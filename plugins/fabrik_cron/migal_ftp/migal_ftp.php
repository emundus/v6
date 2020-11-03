<?php
/**
 * A cron task to email records to a give set of users
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.cron.email
 * @copyright   Copyright (C) 2005-2016  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-cron.php';

/**
 * A cron task to update eMundus DB with data from GesCOF JSON files.
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.cron.migalFTP
 * @since       3.0
 */
class PlgFabrik_Cronmigal_ftp extends PlgFabrik_Cron {
	/**
	 * Check if the user can use the plugin
	 *
	 * @param   string $location To trigger plugin on
	 * @param   string $event    To trigger plugin on
	 *
	 * @return  bool can use or not
	 */
	public function canUse($location = null, $event = null) {
		return true;
	}

	/**
	 * Do the plugin action
	 *
	 * @param   array &  $data       data
	 * @param   object  &$listModel  List model
	 *
	 * @return bool|int
	 */
	public function process(&$data, &$listModel) {

		// LOGGER
		jimport('joomla.log.log');
		JLog::addLogger(['text_file' => 'com_emundus.migal.info.php'], JLog::INFO);
		JLog::addLogger(['text_file' => 'com_emundus.migal.error.php'], JLog::ERROR);

		$db = JFactory::getDbo();

		$rows_updated = 0;

		$params = $this->getParams();
		$session_url = $params->get('session_url', null);

		if (!empty($session_url)) {
			// Hash the distant file in order to compare its contents with those of the past.
			$session_json = file_get_contents($session_url);
			$session_md5 = md5($session_json);

			// Get the list of files which are session JSON files.
			$session_files = glob(JPATH_ROOT.DS.'plugins'.DS.'fabrik_cron'.DS.'migalFTP'.DS.'files'.DS.'sessions-*.json');

			// Compare MD5 of the file for sessions.
			if (empty($session_files) || $session_md5 != substr(explode('-', max($session_files))[2], 0, -5)) {

				// Write the distant file to the local disk and name it as so : sessions-YYYYMMDD-MD5.json
				$session_filename = 'sessions-'.date('Ymd').'-'.$session_md5.'.json';
				file_put_contents(JPATH_ROOT.DS.'plugins'.DS.'fabrik_cron'.DS.'migalFTP'.DS.'files'.DS.$session_filename, $session_json);

				JLog::add('Created NEW session JSON file with filename: '.$session_filename, JLog::INFO, 'com_emundus');

				// Delete the oldest file (if there are 5).
				if (sizeof($session_files) >= 5) {
					unlink(min($session_files));
				}

				$query = $db->getQuery(true);

				// Get all results from DB and compare them with JSON results.
				$query
					->select('*')
					->from($db->quoteName('#__emundus_setup_teaching_unity'));

				$db->setQuery($query);

				try {
					$db_array = $db->loadAssocList();
				} catch (Exception $e) {
					JLog::add('Error getting teaching units in query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
					return false;
				}

				$json_array = json_decode($session_json, true);

				// If the format is invalid then the value will be null.
				// This helps catch potential issues with the Migal system.
				if (empty($json_array)) {
					JLog::add('Error parsing session JSON.', JLog::ERROR, 'com_emundus');
					return false;
				}


				// To separate the data into 3 parts we need to organize the objects.
				$to_create = array();
				$to_update = array();
				$to_delete = array();
				foreach ($json_array as $json_item) {

					// If the item is in the JSON but not in the DB: mark as CREATE.
					$create = true;

					$i = 0;
					foreach ($db_array as $db_item) {

						// We need to see if a value already exists in the DB for the data.
						// If so then it is an UPDATE and also not a CREATE.
						if ($db_item['session_code'] == $json_item['numsession']) {
							$to_update[$db_item['session_code']] = $json_item;
							$create = false;
							unset($to_delete[$i]);
						}
						$i++;

					}

					if ($create) {
						$to_create[] = $json_item;
					}

				}

				JLog::add('Differential analysis complete: CREATE('.sizeof($to_create).') UPDATE('.sizeof($to_update).') DELETE('.sizeof($to_delete).')', JLog::INFO, 'com_emundus');

				// Now that we have all the data sorted into the correct slots it's time to build the MEATY queries.
				// UNPUBLISH
				if (!empty($to_delete)) {

					// Deleting consists of simply setting published to 0.
					$in = array();
					foreach ($to_delete as $item) {
						$in[] = $db->quote($item['session_code']);
					}

					// Unpublish teaching unit.
					$query = $db->getQuery(true);
					$query
						->update($db->quoteName('#__emundus_setup_teaching_unity'))
						->set($db->quoteName('published').' = 0')
						->where($db->quoteName('session_code').' IN ('.implode(',', $in).')');
					$db->setQuery($query);
					try {
						$db->execute();
					} catch (Exception $e) {
						JLog::add('Error unpublishing teaching units in query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
					}
					JLog::add('DELETED teaching units in query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::INFO, 'com_emundus');
					$rows_updated += $db->getAffectedRows();

					// Unpublish registration period.
					$query = $db->getQuery(true);
					$query
						->update($db->quoteName('#__emundus_setup_campaigns'))
						->set($db->quoteName('published').' = 0')
						->where($db->quoteName('session_code').' IN ('.implode(',', $in).')');
					$db->setQuery($query);
					try {
						$db->execute();
					} catch (Exception $e) {
						JLog::add('Error unpublishing campaigns in query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
					}
					$rows_updated += $db->getAffectedRows();
					JLog::add('DELETED campaigns in query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::INFO, 'com_emundus');

				}


				// UPDATE or DO NOTHING
				if (!empty($to_update)) {

					$query = $db->getQuery(true);
					$query
						->select([
							't.*',
							$db->quoteName('c.description', 'desc'),
                            $db->quoteName('p.label', 'product_name'), $db->quoteName('p.url'), $db->quoteName('p.programmes', 'categ'), $db->quoteName('p.prerequisite'), $db->quoteName('p.audience'), $db->quoteName('p.tagline'), $db->quoteName('p.objectives'), $db->quoteName('p.content'), $db->quoteName('p.numcpf'), $db->quoteName('p.manager_lastname'), $db->quoteName('p.manager_firstname'), $db->quoteName('p.pedagogie', 'pedagogie'), $db->quoteName('p.certificate', 'certificate'), $db->quoteName('p.partner', 'partner'), $db->quoteName('p.target', 'target'), $db->quoteName('p.evaluation', 'evaluation'), $db->quoteName('p.temoignagesclients', 'temoignagesclients'), $db->quoteName('p.accrochecom', 'accrochecom')
						])
						->from($db->quoteName('#__emundus_setup_teaching_unity','t'))
						->leftJoin($db->quoteName('#__emundus_setup_programmes','p').' ON t.code = p.code')
						->leftJoin($db->quoteName('#__emundus_setup_campaigns','c').' ON c.session_code = t.session_code')
						->where($db->quoteName('t.session_code').' IN ('.implode(',', $db->quote(array_keys($to_update))).')');
					$db->setQuery($query);
					try {
						$db_array = $db->loadAssocList();
					} catch (Exception $e) {
						JLog::add('Error getting data for update comparisons at query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
					}

					// Get the list of categories.
					$query = $db->getQuery(true);
					$query
						->select([$db->quoteName('id'), $db->quoteName('title')])
						->from($db->quoteName('#__emundus_setup_thematiques'));
					$db->setQuery($query);
					try {
						$categories = $db->loadAssocList('id','title');
					} catch (Exception $e) {
						JLog::add('Error getting programme codes in query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
						$categories = null;
					}

					foreach ($db_array as $db_item) {

						$update_item = $to_update[$db_item['session_code']];
						$fields = array();

						// In case the programme has been unpublished (has gone through the DELETE procedure): republish it.
						if ($db_item['published'] == '0' && $update_item['publicationsession'] == false) {
							$fields[] = $db->quoteName('c.published').' = 1';
							$fields[] = $db->quoteName('t.published').' = 1';
						}

						// Items which are set to NOT be displayed need to be unpublished.
						if ($db_item['published'] == '1' && $update_item['publicationsession'] == true) {
							$fields[] = $db->quoteName('t.published').' = 0';
							$fields[] = $db->quoteName('c.published').' = 0';
						}

						// Compare each field and update those which have differences.
						// Product name = programme label
						if ($db_item['product_name'] != $update_item['intituleproduit']) {
							$fields[] = $db->quoteName('p.label').' = '.$db->quote($update_item['intituleproduit']);
						}

						// Session label = Teaching unit & campaign label
						if ($db_item['label'] != $update_item['libellestage']) {
							$fields[] = $db->quoteName('t.label').' = '.$db->quote($update_item['libellestage']);
							$fields[] = $db->quoteName('c.label').' = '.$db->quote($update_item['libellestage']);
						}

						// Product url = programme url
						if ($db_item['url'] != $update_item['libellestageurl']) {
							$fields[] = $db->quoteName('p.url').' = '.$db->quote($update_item['libellestageurl']);
						}

						// Product family = programme programmes
						// Updating the category involves checking if the category exists in the other table.
						$category = array_search(str_replace(['é','è','ê'],'e', html_entity_decode(mb_strtolower(str_replace([' ','/','\''],'-', $update_item['produit6'])))), $categories);
						$multiple = explode(',-', str_replace(['é','è','ê'],'e', html_entity_decode(mb_strtolower(str_replace([' ','/','\''],'-', $update_item['produit6'])))));
						if ($db_item['categ'] != $category ) {
							$category_concat  = array();
							foreach ($multiple as $t) {
								if (!in_array($t,$categories)){
									if ($category == 0) {
										// If no category exists: INSERT
										$query = $db->getQuery(true);
										$query
											->insert($db->quoteName('#__emundus_setup_thematiques'))
											->columns([$db->quoteName('title'), $db->quoteName('color'), $db->quoteName('published'), $db->quoteName('order'), $db->quoteName('label')])
											->values($db->quote(str_replace(['é','è','ê'],'e', html_entity_decode(mb_strtolower(str_replace([' ','/','\''],'-', $t))))).', '.$db->quote('default').', 0, '.(max(array_keys($categories))+1).', '.$db->quote($update_item['produit6']));
										$db->setQuery($query);
										try {
											$db->execute();
											$category = $db->insertid();
											$categories[$category] = str_replace(['é','è','ê'],'e', html_entity_decode(mb_strtolower(str_replace([' ','/','\''],'-', $t))));
										} catch (Exception $e) {
											JLog::add('Error inserting category in query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
										}
									}
								} else {
									$query = $db->getQuery(true);
									$query
										->select($db->quoteName('id'))
										->from($db->quoteName('#__emundus_setup_thematiques'))
										->where($db->quoteName('title') . ' LIKE ' . $db->quote($t));
									$db->setQuery($query);
									try {
										$category = $db->loadResult();
									} catch (Exception $e) {
										JLog::add('Error getting programme codes in query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
									}
								}
								array_push($category_concat, $category);
							}
							$fields[] = $db->quoteName('p.programmes').' = '.$db->quote(implode(', ', $category_concat));
						}

						// Product code = programme code
						if ($db_item['code'] != $update_item['codeproduit']) {
							$fields[] = $db->quoteName('p.code').' = '.$db->quote($update_item['codeproduit']);
							$fields[] = $db->quoteName('c.training').' = '.$db->quote($update_item['codeproduit']);
							$fields[] = $db->quoteName('t.code').' = '.$db->quote($update_item['codeproduit']);
						}

						// Product observations = programme notes, campaign description, teaching_unit notes
						if ($db_item['notes'] != $update_item['observations']) {
							$fields[] = $db->quoteName('p.notes').' = '.$db->quote($update_item['observations']);
							$fields[] = $db->quoteName('t.notes').' = '.$db->quote($update_item['observations']);
						}

						if ($db_item['desc'] != $update_item['resumeproduit']) {
							$fields[] = $db->quoteName('c.description').' = '.$db->quote($update_item['resumeproduit']);
							$fields[] = $db->quoteName('c.short_description').' = '.$db->quote($update_item['resumeproduit']);
						}

						// Session price = teaching unit price
						if ($db_item['price'] != $update_item['coutsession']) {
							$fields[] = $db->quoteName('t.price').' = '.$db->quote($update_item['coutsession']);
						}

						// Session start date = teaching unit start date and campaign end date (reminder: cmapaigns run until the session starts)
						if ($db_item['date_start'].'.000000' != $update_item['datedebutsession']['date']) {
							$fields[] = $db->quoteName('c.end_date').' = '.$db->quote($update_item['datedebutsession']['date']);
							$fields[] = $db->quoteName('t.date_start').' = '.$db->quote($update_item['datedebutsession']['date']);
						}

						// Session end date = teaching unit end date
						if ($db_item['date_end'].'.000000' != $update_item['datefinsession']['date']) {
							$fields[] = $db->quoteName('t.date_end').' = '.$db->quote($update_item['datefinsession']['date']);
						}

						// Session days = teaching unit days
						if ($db_item['days'] != $update_item['nbjours']) {
							$fields[] = $db->quoteName('t.days').' = '.$db->quote($update_item['nbjours']);
						}

						// Session hours = teaching unit hours
						if ($db_item['hours'] != $update_item['nbheures']) {
							$fields[] = $db->quoteName('t.hours').' = '.$db->quote($update_item['nbheures']);
						}

						// Session hours per day = teaching unit hours per day
						if ($db_item['hours_per_day'] != $update_item['nbheuresjoursession']) {
							$fields[] = $db->quoteName('t.hours_per_day').' = '.$db->quote($update_item['nbheuresjoursession']);
						}

						// Time spent in the company.
						if ($db_item['time_in_company'] != $update_item['session1']) {
							$fields[] = $db->quoteName('t.time_in_company').' = '.$db->quote($update_item['session1']);
						}

						// Session minimum occupants = teaching unit minimum occupants
						if ($db_item['min_occupants'] != $update_item['effectif_mini']) {
							$fields[] = $db->quoteName('t.min_occupants').' = '.$db->quote($update_item['effectif_mini']);
						}

						// Session maximum occupants = teaching unit maximum occupants
						if ($db_item['max_occupants'] != $update_item['effectif_maxi']) {
							$fields[] = $db->quoteName('t.max_occupants').' = '.$db->quote($update_item['effectif_maxi']);
						}

						// Session occupants = teaching unit occupants
						if ($db_item['occupants'] != $update_item['placedispo']['nbInscrit']) {
							$fields[] = $db->quoteName('t.occupants').' = '.$db->quote($update_item['placedispo']['nbInscrit']);
						}

						// Session seo title = teaching unit seo title
						if ($db_item['seo_title'] != $update_item['titleseo']) {
							$fields[] = $db->quoteName('t.seo_title').' = '.$db->quote($update_item['titleseo']);
						}

						// Session address name = teaching unit address name
						if ($db_item['location_title'] != $update_item['libellelieu']) {
							$fields[] = $db->quoteName('t.location_title').' = '.$db->quote($update_item['libellelieu']);
						}

						// Session address1 address2 = teaching unit address
						if ($db_item['location_address'] != $update_item['adresse1lieu'].' '.$update_item['adresse2lieu']) {
							$fields[] = $db->quoteName('t.location_address').' = '.$db->quote($update_item['adresse1lieu'].' '.$update_item['adresse2lieu']);
						}

						// Session address zip = teaching unit address zip
						if ($db_item['location_zip'] != $update_item['cplieu']) {
							$fields[] = $db->quoteName('t.location_zip').' = '.$db->quote($update_item['cplieu']);
						}

						// Session address city = teaching unit address city
						if ($db_item['location_city'] != $update_item['villelieu']) {
							$fields[] = $db->quoteName('t.location_city').' = '.$db->quote($update_item['villelieu']);
						}

						// Session address city = teaching unit address city
						if ($db_item['location_region'] != $update_item['region']) {
							$fields[] = $db->quoteName('t.location_region').' = '.$db->quote($update_item['region']);
						}

						// Session prerequisites = teaching unit prerequisites
						if ($db_item['prerequisite'] != $update_item['prerequis']) {
							$fields[] = $db->quoteName('p.prerequisite').' = '.$db->quote($update_item['prerequis']);
						}

						// Session public type = teaching unit audience
						if ($db_item['audience'] != $update_item['typepublic']) {
							$fields[] = $db->quoteName('p.audience').' = '.$db->quote($update_item['typepublic']);
						}

						// Session commercial tagline = teaching unit tagline
						if ($db_item['tagline'] != $update_item['accrochecom']) {
							$fields[] = $db->quoteName('p.tagline').' = '.$db->quote($update_item['accrochecom']);
						}

						// Session objectives = teaching unit objectives
						if ($db_item['objectives'] != $update_item['objectifs']) {
							$fields[] = $db->quoteName('p.objectives').' = '.$db->quote($update_item['objectifs']);
						}

						// Session content = teaching unit content
						if ($db_item['content'] != $update_item['contenu']) {
							$fields[] = $db->quoteName('p.content').' = '.$db->quote($update_item['contenu']);
						}

						// CPF number
						if ($db_item['numcpf'] != $update_item['numcpf']) {
							$fields[] = $db->quoteName('p.numcpf').' = '.$db->quote($update_item['numcpf']);
						}

						// Manager name
						if ($db_item['manager_lastname'] != $update_item['ur_nom']) {
							$fields[] = $db->quoteName('p.manager_lastname').' = '.$db->quote($update_item['ur_nom']);
						}
						if ($db_item['manager_firstname'] != $update_item['ur_prenom']) {
							$fields[] = $db->quoteName('p.manager_firstname').' = '.$db->quote($update_item['ur_prenom']);
						}

						// Tax rate
						if ($db_item['tax_rate'] != $update_item['tauxtvaproduit']) {
							$fields[] = $db->quoteName('t.tax_rate').' = '.$db->quote($update_item['tauxtvaproduit']);
						}

						// Pedagogie
						if ($db_item['pedagogie'] != $update_item['pedagogie']) {
							$fields[] = $db->quoteName('p.pedagogie').' = '.$db->quote($update_item['pedagogie']);
						}

						// Intervenant
						if ($db_item['intervenant'] != $update_item['typeintervenant']) {
							$fields[] = $db->quoteName('t.intervenant').' = '.$db->quote($update_item['typeintervenant']);
						}

						// Partner
						if ($db_item['partner'] != $update_item['produit9'] || $db_item['partner'] != $update_item['produit8']) {
							if (!empty($update_item['produit9']) && $db_item['partner'] != $update_item['produit9']) {
								$fields[] = $db->quoteName('p.partner') . ' = ' . $db->quote($update_item['produit9']);
							} elseif (!empty($update_item['produit8']) && $db_item['partner'] != $update_item['produit8']) {
								$fields[] = $db->quoteName('p.partner').' = '.$db->quote($update_item['produit8']);
							}
						}

						// Target
						if ($db_item['target'] != $update_item['produit7']) {
							$fields[] = $db->quoteName('p.target').' = '.$db->quote($update_item['produit7']);
						}

						// Evaluation
						if ($db_item['evaluation'] != $update_item['evaluation']) {
							$fields[] = $db->quoteName('p.evaluation').' = '.$db->quote($update_item['evaluation']);
						}

                        // temoignagesclients
                        if ($db_item['temoignagesclients'] != $update_item['temoignagesclients']) {
                            $fields[] = $db->quoteName('p.temoignagesclients').' = '.$db->quote($update_item['temoignagesclients']);
                        }

                        // temoignagesclients
                        if ($db_item['accrochecom'] != $update_item['accrochecom']) {
                            $fields[] = $db->quoteName('p.accrochecom').' = '.$db->quote($update_item['accrochecom']);
                        }

                        // If any of the fields are different, we must run the UPDATE query.
						if (!empty($fields)) {

							$db = JFactory::getDbo();

							// JDatabase PDO does not support multiple table updates, we must resort to standard SQL.
							$query = 'UPDATE '.$db->quoteName('#__emundus_setup_campaigns', 'c').', '.$db->quoteName('#__emundus_setup_programmes', 'p').', '.$db->quoteName('#__emundus_setup_teaching_unity', 't').
									' SET '.implode(',', $fields).
									' WHERE p.code LIKE '.$db->quote($db_item['code']).' AND c.session_code LIKE '.$db->quote($db_item['session_code']).' AND t.session_code LIKE '.$db->quote($db_item['session_code']);
							$db->setQuery($query);

							try {
								$db->execute();
							} catch (Exception $e) {
								JLog::add('Error updating data in query: '.$query, JLog::ERROR, 'com_emundus');
							}
							$rows_updated += $db->getAffectedRows();
							JLog::add('UPDATED data for DB item ['.$db_item['session_code'].'] in query: '.$query, JLog::INFO, 'com_emundus');

						}
					}
				}


				// INSERT
				if (!empty($to_create)) {

					// Get the highest ID for the campaigns table, this will be used to establish the foreign key between campaigns and teaching units.
					$query = $db->getQuery(true);
					$query
						->select('MAX(id)')
						->from($db->quoteName('#__emundus_setup_campaigns'));
					$db->setQuery($query);
					try {
						$campaign_id = $db->loadResult();
					} catch (Exception $e) {
						JLog::add('Error getting max ID in query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
					}

					if (empty($campaign_id)) {
						$campaign_id = 0;
					}

					// Get the list of programme codes that already exist in order to avoid creating duplicates.
					$query = $db->getQuery(true);
					$query
						->select([$db->quoteName('p.code'), $db->quoteName('tu.session_code')])
						->from($db->quoteName('#__emundus_setup_programmes','p'))
						->leftJoin($db->quoteName('#__emundus_setup_teaching_unity', 'tu').' ON '.$db->quoteName('tu.code').' LIKE '.$db->quoteName('p.code'));
					$db->setQuery($query);
					try {
						$programme_codes = $db->loadAssocList('code');
					} catch (Exception $e) {
						JLog::add('Error getting programme codes in query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
					}

					// Get the list of programmes allowed to be imported form the table.
					$query = $db->getQuery(true);
					$query
						->select($db->quoteName('code'))
						->from($db->quoteName('#__emundus_setup_programmes_to_import'));
					$db->setQuery($query);
					try {
						$programmes_to_import = $db->loadColumn();
					} catch (Exception $e) {
						JLog::add('Error getting programme codes in query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
						$programmes_to_import = null;
					}

					// Get the list of categories.
					$query = $db->getQuery(true);
					$query
						->select([$db->quoteName('id'),$db->quoteName('title')])
						->from($db->quoteName('#__emundus_setup_thematiques'));
					$db->setQuery($query);
					try {
						$categories = $db->loadAssocList('id','title');
					} catch (Exception $e) {
						JLog::add('Error getting programme codes in query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
						$categories = null;
					}

					// DB table struct for different tables.
					$programme_columns  = ['code', 'label', 'notes', 'published', 'programmes', 'apply_online', 'url', 'prerequisite', 'audience', 'tagline', 'objectives', 'content', 'numcpf', 'manager_lastname', 'manager_firstname', 'pedagogie', 'certificate', 'partner', 'target', 'evaluation'];
					$campaign_columns   = ['session_code', 'label', 'description', 'short_description', 'start_date', 'end_date', 'profile_id', 'training', 'published'];
					$teaching_columns   = ['code', 'session_code', 'label', 'notes', 'published', 'price', 'date_start', 'date_end', 'registration_periode', 'days', 'hours', 'hours_per_day', 'time_in_company', 'min_occupants', 'max_occupants', 'occupants', 'seo_title', 'location_title', 'location_address', 'location_zip', 'location_city', 'location_region', 'tax_rate', 'intervenant'];

					// Build all value lists for the different inserts at once, this avoids having to loop multiple times.
					$programme_values = array();
					$campaign_values = array();
					$teaching_values = array();
					$add_to_all_rights = array();
					foreach ($to_create as $item) {

						// If the product is not found in the list of programmes to import: skip.
						// If the list of products to import is empty we are assuming importation of everything.
						if (!empty($programmes_to_import) && !in_array($item['codeproduit'], $programmes_to_import)) {
							JLog::add('Skipped product '.$item['codeproduit'].' due to not present in jos_emundus_setup_programmes_to_import', JLog::INFO, 'com_emundus');
							continue;
						}

						// Array search returns FALSE (0) if it does not find the key.
						// Else it will return the ID of the category with the name in the JSON.
						$category = array_search(str_replace(['é','è','ê'],'e', html_entity_decode(mb_strtolower(str_replace([' ','/','\''],'-', $item['produit6'])))), $categories);
						$multiple = explode(',-', str_replace(['é','è','ê'],'e', html_entity_decode(mb_strtolower(str_replace([' ','/','\''],'-', $item['produit6'])))));
						$category_concat  = array();
						foreach ($multiple as $t) {
							if (!in_array($t,$categories)){
								if ($category == 0) {
									// If no category exists: INSERT
									$query = $db->getQuery(true);
									$query
										->insert($db->quoteName('#__emundus_setup_thematiques'))
										->columns([$db->quoteName('title'), $db->quoteName('color'), $db->quoteName('published'), $db->quoteName('order'), $db->quoteName('label')])
										->values($db->quote(str_replace(['é','è','ê'],'e', html_entity_decode(mb_strtolower(str_replace([' ','/','\''],'-', $t))))).', '.$db->quote('default').', 0, '.(max(array_keys($categories))+1).', '.$db->quote($item['produit6']));
									$db->setQuery($query);
									try {
										$db->execute();
										$category = $db->insertid();
										$categories[$category] = str_replace(['é','è','ê'],'e', html_entity_decode(mb_strtolower(str_replace([' ','/','\''],'-', $t))));
									} catch (Exception $e) {
										JLog::add('Error inserting category in query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
									}
								}
							} else {
								$query = $db->getQuery(true);
								$query
									->select($db->quoteName('id'))
									->from($db->quoteName('#__emundus_setup_thematiques'))
									->where($db->quoteName('title') . ' LIKE ' . $db->quote($t));
								$db->setQuery($query);
								try {
									$category = $db->loadResult();
								} catch (Exception $e) {
									JLog::add('Error getting programme codes in query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
								}
							}
							array_push($category_concat, $category);
						}

						// Only add programme if it does not already exist in DB and has not already been added.
						$partner = '';
						if (!empty($item['produit8'])) {
							$partner = $item['produit8'];
						} elseif (!empty($item['produit9'])) {
							$partner = $item['produit9'];
						}

						if (!in_array($item['codeproduit'], array_unique(array_keys($programme_codes))) && !array_key_exists($item['codeproduit'], $programme_values)) {
							$programme_values[$item['codeproduit']] = implode(',', [
								$db->quote($item['codeproduit']),
								$db->quote($item['intituleproduit']),
								$db->quote($item['observations']),
								'1',
								$db->quote(implode(', ', $category_concat)),
								'1',
								$db->quote($item['libellestageurl']),
								$db->quote($item['prerequis']),
								$db->quote($item['typepublic']),
								$db->quote($item['accrochecom']),
								$db->quote($item['objectifs']),
								$db->quote($item['contenu']),
								$db->quote($item['numcpf']),
								$db->quote($item['ur_nom']),
								$db->quote($item['ur_prenom']),
								$db->quote($item['pedagogie']),
								$db->quote(''),
								$db->quote($partner),
								$db->quote($item['produit7']),
								$db->quote($item['evaluation'])
							]);

							$add_to_all_rights[] = '1,'.$db->quote($item['codeproduit']);
						}

						if (!in_array($item['numsession'], $campaign_values) && !in_array($item['numsession'], $programme_codes)) {
							$campaign_values[] = implode(',', [
								$db->quote($item['numsession']),
								$db->quote($item['libellestage']),
								$db->quote($item['resumeproduit']),
								$db->quote($item['resumeproduit']),
								'NOW()',
								$db->quote($item['datedebutsession']['date']),
								'1001',
								$db->quote($item['codeproduit']),
								'1'
							]);

							if ($item['publicationsession']) {
								$published = '0';
							} else {
								$published = '1';
							}
							$teaching_values[] = implode(',', [
								$db->quote($item['codeproduit']),
								$db->quote($item['numsession']),
								$db->quote($item['libellestage']),
								$db->quote($item['observations']),
								$published,
								$db->quote($item['coutsession']),
								$db->quote($item['datedebutsession']['date']),
								$db->quote($item['datefinsession']['date']),
								++$campaign_id,
								$db->quote($item['nbjours']),
								$db->quote($item['nbheures']),
								$db->quote($item['nbheuresjoursession']),
								$db->quote($item['session1']),
								$db->quote($item['effectif_mini']),
								$db->quote($item['effectif_maxi']),
								$db->quote($item['placedispo']['nbInscrit']),
								$db->quote($item['titleseo']),
								$db->quote($item['libellelieu']),
								$db->quote($item['adresse1lieu'].' '.$item['adresse2lieu']),
								$db->quote($item['cplieu']),
								$db->quote($item['villelieu']),
								$db->quote($item['region']),
								$db->quote($item['tauxtvaproduit']),
								$db->quote($item['typeintervenant'])
							]);
						}
					}

					if (!empty($programme_values)) {
						// Create a new programme for the session / product.
						$query = $db->getQuery(true);
						$query
							->insert($db->quoteName('#__emundus_setup_programmes'))
							->columns($programme_columns)
							->values($programme_values);
						$db->setQuery($query);
						try {
							$db->execute();
						} catch (Exception $e) {
							JLog::add('Error inserting programme data in query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
						}

						$query->clear()
							->insert($db->quoteName('#__emundus_setup_groups_repeat_course'))
							->columns(['parent_id', 'course'])
							->values($add_to_all_rights);
						$db->setQuery($query);
						try {
							$db->execute();
						} catch (Exception $e) {
							JLog::add('Error inserting programme code in all rights group: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
						}

						$rows_updated += $db->getAffectedRows();
						JLog::add('INSERT programme data with query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::INFO, 'com_emundus');
					}

					// Create a new registration period for the session.
					// This period will run from now until the session starts.
					$query = $db->getQuery(true);
					$query
						->insert($db->quoteName('#__emundus_setup_campaigns'))
						->columns($campaign_columns)
						->values($campaign_values);
					$db->setQuery($query);
					try {
						$db->execute();
					} catch (Exception $e) {
						JLog::add('Error inserting session data in query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
					}
					$rows_updated += $db->getAffectedRows();
					JLog::add('INSERT session data with query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::INFO, 'com_emundus');

					// Here we add the meatiest part of the data to the teaching_unity table.
					// This will contain things like occupants, location, price, etc...
					$query = $db->getQuery(true);
					$query
						->insert($db->quoteName('#__emundus_setup_teaching_unity'))
						->columns($teaching_columns)
						->values($teaching_values);
					$db->setQuery($query);
					try {
						$db->execute();
					} catch (Exception $e) {
						JLog::add('Error inserting teaching unit data in query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
					}
					$rows_updated += $db->getAffectedRows();
					JLog::add('INSERT teaching unit data with query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::INFO, 'com_emundus');

				}
			}
		}
		return $rows_updated;
	}
}
