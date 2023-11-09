<?php
/**
 * Messages model used for the new message dialog.
 *
 * @package    Joomla
 * @subpackage eMundus
 *             components/com_emundus/emundus.php
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');
jimport('joomla.database.table');

use Joomla\CMS\Factory;

class EmundusModelForm extends JModelList
{

	private $app;
	private $db;

	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->app = Factory::getApplication();
		$this->db = Factory::getDbo();

		JLog::addLogger(['text_file' => 'com_emundus.form.php'], JLog::ALL, array('com_emundus.form'));
	}

	/**
	 * @param   String  $filter
	 * @param   String  $sort
	 * @param   String  $recherche
	 * @param   Int     $lim
	 * @param   Int     $page
	 *
	 * @return array|stdClass
	 */
	function getAllForms(string $filter = '', string $sort = '', string $recherche = '', int $lim = 0, int $page = 0): array
	{
		$data = ['datas' => [], 'count' => 0];
		require_once(JPATH_ROOT . '/components/com_emundus/models/users.php');

		
		$query    = $this->db->getQuery(true);

		// Build filter / limit / pagination part of the query
		if (empty($lim)) {
			$limit = 25;
		}
		else {
			$limit = $lim;
		}

		if (empty($page)) {
			$offset = 0;
		}
		else {
			$offset = ($page - 1) * $limit;
		}

		if (empty($sort)) {
			$sort = 'DESC';
		}

		if ($filter == 'Unpublish') {
			$filterDate = $this->db->quoteName('sp.status') . ' = 0';
		}
		else {
			$filterDate = $this->db->quoteName('sp.status') . ' = 1';
		}

		$filterId      = $this->db->quoteName('sp.published') . ' = 1';
		$fullRecherche = empty($recherche) ? 1 : $this->db->quoteName('sp.label') . ' LIKE ' . $this->db->quote('%' . $recherche . '%');

		$m_user           = new EmundusModelUsers();
		$allowed_programs = $m_user->getUserGroupsProgramme(JFactory::getUser()->id);

		// GET ALL PROFILES THAT ARE NOT LINKED TO A CAMPAIGN
		$other_profile_query          = $this->db->getQuery(true);
		$other_profile_full_recherche = empty($recherche) ? 1 : $this->db->quoteName('esp.label') . ' LIKE ' . $this->db->quote('%' . $recherche . '%');

		$other_profile_query->select(['esp.*', 'esp.label AS form_label'])
			->from($this->db->quoteName('#__emundus_setup_profiles', 'esp'))
			->leftJoin($this->db->quoteName('#__emundus_setup_campaigns', 'esc') . ' ON ' . $this->db->quoteName('esc.profile_id') . ' = ' . $this->db->quoteName('esp.id'))
			->where($this->db->quoteName('esc.profile_id') . ' IS NULL')
			->andWhere($this->db->quoteName('esp.published') . ' = 1')
			->andWhere($other_profile_full_recherche)
			->andWhere($this->db->quoteName('esp.menutype') . ' IS NOT NULL');

		if ($filter == 'Unpublish') {
			$other_profile_query->andWhere($this->db->quoteName('esp.status') . ' = 0');
		}
		else {
			$other_profile_query->andWhere($this->db->quoteName('esp.status') . ' = 1');
		}


		// Now we need to put the query together and get the profiles
		$query->select(['sp.*', 'sp.label AS form_label'])
			->from($this->db->quoteName('#__emundus_setup_profiles', 'sp'))
			->leftJoin($this->db->quoteName('#__emundus_setup_campaigns', 'esc') . ' ON ' . $this->db->quoteName('esc.profile_id') . ' = ' . $this->db->quoteName('sp.id'))
			->where($filterDate)
			->andWhere($fullRecherche)
			->andWhere($filterId)
			->andWhere($this->db->quoteName('esc.training') . ' IN (' . implode(',', $this->db->quote($allowed_programs)) . ')')
			->group($this->db->quoteName('id'))
			->order('id ' . $sort)
			->union($other_profile_query);

		try {
			$this->db->setQuery($query);
			$data['count'] = sizeof($this->db->loadObjectList());
			$this->db->setQuery($query, $offset, $limit);
			$data['datas'] = $this->db->loadObjectList();

			if (!empty($data['datas'])) {
				$path_to_file   = basename(__FILE__) . '/../language/overrides/';
				$path_to_files  = array();
				$Content_Folder = array();
				$languages      = JLanguageHelper::getLanguages();
				if (!empty($languages)) {
					foreach ($languages as $language) {
						$path_to_files[$language->sef]  = $path_to_file . $language->lang_code . '.override.ini';
						$Content_Folder[$language->sef] = file_get_contents($path_to_files[$language->sef]);
					}

					require_once(JPATH_ROOT . '/components/com_emundus/models/formbuilder.php');
					$formbuilder = new EmundusModelFormbuilder;
					foreach ($data['datas'] as $key => $form) {
						$label = [];
						foreach ($languages as $language) {
							$label[$language->sef] = $formbuilder->getTranslation($form->label, $language->lang_code) ?: $form->label;
						}
						$data['datas'][$key]->label = $label;
					}
				}
			}
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/form | Cannot getting the list of forms : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');
		}

		return $data;
	}

	/**
	 * TODO: Add filters / recherche etc./.. At the moment, it's not working
	 *
	 * @param $filter
	 * @param $sort
	 * @param $recherche
	 * @param $lim
	 * @param $page
	 *
	 * @return array
	 */
	function getAllGrilleEval($filter, $sort, $recherche, $lim, $page): array
	{
		$data     = ['datas' => [], 'count' => 0];
		
		$query    = $this->db->getQuery(true);

		try {
			// We need to get the list of fabrik forms that are linked to the jos_emundus_evaluations table
			$query->clear();
			$query
				->select([$this->db->quoteName('ff.id'), $this->db->quoteName('ff.label'), '"grilleEval" AS type'])
				->from($this->db->quoteName('#__fabrik_forms', 'ff'))
				->leftJoin($this->db->quoteName('#__fabrik_lists', 'fl') . ' ON ' . $this->db->quoteName('fl.form_id') . ' = ' . $this->db->quoteName('ff.id'))
				->where($this->db->quoteName('fl.db_table_name') . ' = ' . $this->db->quote('jos_emundus_evaluations'));
			$this->db->setQuery($query);

			$evaluation_forms = $this->db->loadObjectList();

			if (!empty($evaluation_forms)) {
				require_once(JPATH_ROOT . '/components/com_emundus/models/formbuilder.php');
				$m_form_builder = new EmundusModelFormbuilder();

				$path_to_file   = basename(__FILE__) . '/../language/overrides/';
				$path_to_files  = array();
				$Content_Folder = array();
				$languages      = JLanguageHelper::getLanguages();
				foreach ($languages as $language) {
					$path_to_files[$language->sef]  = $path_to_file . $language->lang_code . '.override.ini';
					$Content_Folder[$language->sef] = file_get_contents($path_to_files[$language->sef]);
				}

				foreach ($evaluation_forms as $evaluation_form) {
					$label = [];
					foreach ($languages as $language) {
						$label[$language->sef] = $m_form_builder->getTranslation($evaluation_form->label, $language->lang_code) ?: $evaluation_form->label;
					}
					$evaluation_form->label = $label;
				}
			}

			$data['datas'] = $evaluation_forms;
			$data['count'] = sizeof($evaluation_forms);
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/form | Cannot getting the list of forms : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');
		}

		return $data;
	}

	function getAllFormsPublished()
	{
		
		$query = $this->db->getQuery(true);

		$filterId = $this->db->quoteName('sp.published') . ' = 1';

		$query->select([
			'sp.*',
			'sp.label AS form_label'
		])
			->from($this->db->quoteName('#__emundus_setup_profiles', 'sp'))
			->where($this->db->quoteName('sp.status') . ' = 1')
			->andWhere($filterId);

		try {
			$this->db->setQuery($query);

			return $this->db->loadObjectList();
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/form | Cannot getting the published forms : ' . preg_replace("/[\r\n]/", " ", $query . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

			return new stdClass();
		}
	}

	public function deleteForm($data)
	{
		
		$query = $this->db->getQuery(true);

		require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'formbuilder.php');
		require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'falang.php');

		$formbuilder = new EmundusModelFormbuilder;
		$falang      = new EmundusModelFalang;

		$eMConfig = JComponentHelper::getParams('com_emundus');
		$modules  = $eMConfig->get('form_builder_page_creation_modules', [93, 102, 103, 104, 168, 170]);

		if (count($data) > 0) {
			$sp_conditions = array(
				$this->db->quoteName('sp.id') . ' IN (' . implode(", ", array_values($data)) . ')'
			);

			$query->select([
				'sp.id AS spid',
				'mt.id AS mtid',
				'me.id AS meid'
			])
				->from($this->db->quoteName('#__emundus_setup_profiles', 'sp'))
				->leftJoin($this->db->quoteName('#__menu_types', 'mt') . ' ON ' . $this->db->quoteName('mt.menutype') . ' = ' . $this->db->quoteName('sp.menutype'))
				->leftJoin($this->db->quoteName('#__menu', 'me') . ' ON ' . $this->db->quoteName('me.menutype') . ' = ' . $this->db->quoteName('mt.menutype'))
				->where($sp_conditions);

			try {
				$this->db->setQuery($query);
				$results   = $this->db->loadObjectList();
				$spids_arr = array();
				$mtids_arr = array();
				$meids_arr = array();
				$flids_arr = array();
				foreach (array_values($results) as $result) {
					if (!in_array($result->spid, $spids_arr)) {
						$spids_arr[] = $result->spid;
					}
					if (!in_array($result->mtid, $mtids_arr)) {
						$mtids_arr[] = $result->mtid;
					}
					if (!in_array($result->meid, $meids_arr)) {
						$meids_arr[] = $result->meid;
					}
				}

				$query->clear()
					->select('form_id')
					->from($this->db->quoteName('#__emundus_setup_formlist'))
					->where($this->db->quoteName('profile_id') . ' IN (' . implode(", ", array_values($data)) . ')');
				$this->db->setQuery($query);
				$forms = $this->db->loadObjectList();

				foreach (array_values($forms) as $form) {
					if (!in_array($form->form_id, $flids_arr)) {
						$flids_arr[] = $form->form_id;
					}
				}

				$fl_conditions = array($this->db->quoteName('fl.id') . ' IN (' . implode(", ", array_values($flids_arr)) . ')');

				$query->clear();
				$query->select([
					'ff.intro AS ffintro',
					'ff.id AS ffid',
					'fl.db_table_name AS dbtable',
					'ffg.id AS ffgid',
					'fg.id AS fgid',
					'fe.id AS feid'
				])
					->from($this->db->quoteName('#__fabrik_lists', 'fl'))
					->leftJoin($this->db->quoteName('#__fabrik_forms', 'ff') . ' ON ' . $this->db->quoteName('fl.form_id') . ' = ' . $this->db->quoteName('ff.id'))
					->leftJoin($this->db->quoteName('#__fabrik_formgroup', 'ffg') . ' ON ' . $this->db->quoteName('ffg.form_id') . ' = ' . $this->db->quoteName('ff.id'))
					->leftJoin($this->db->quoteName('#__fabrik_groups', 'fg') . ' ON ' . $this->db->quoteName('fg.id') . ' = ' . $this->db->quoteName('ffg.group_id'))
					->leftJoin($this->db->quoteName('#__fabrik_elements', 'fe') . ' ON ' . $this->db->quoteName('fe.group_id') . ' = ' . $this->db->quoteName('fg.id'))
					->where($fl_conditions);

				$this->db->setQuery($query);
				$results      = $this->db->loadObjectList();
				$ffids_arr    = array();
				$dbtables_arr = array();
				$ffgids_arr   = array();
				$fgids_arr    = array();
				$feids_arr    = array();
				$ffintros_arr = array();
				foreach (array_values($results) as $result) {
					if (!in_array($result->ffid, $ffids_arr)) {
						$ffids_arr[] = $result->ffid;
					}
					if (!in_array($result->dbtable, $dbtables_arr)) {
						$dbtables_arr[] = $result->dbtable;
					}
					if (!in_array($result->ffgid, $ffgids_arr)) {
						$ffgids_arr[] = $result->ffgid;
					}
					if (!in_array($result->fgid, $fgids_arr)) {
						$fgids_arr[] = $result->fgid;
					}
					if (!in_array($result->feid, $feids_arr) && $result->feid != null) {
						$feids_arr[] = $result->feid;
					}
					if (!in_array($result->ffintro, $ffintros_arr)) {
						$ffintros_arr[] = $result->ffintro;
					}
				}

				try {
					// DISSOCIATE CAMPAIGN WITH THIS PROFILE ID
					$conditions = array($this->db->quoteName('profile_id') . ' IN (' . implode(", ", array_values($spids_arr)) . ')');

					$query->clear()
						->update($this->db->quoteName('#__emundus_setup_campaigns'))
						->set($this->db->quoteName('profile_id') . ' = NULL')
						->where($conditions);

					$this->db->setQuery($query);
					$this->db->execute();
					//

					// DELETE SETUP PROFILE
					$conditions = array($this->db->quoteName('id') . ' IN (' . implode(", ", array_values($spids_arr)) . ')');

					$query->clear()
						->delete($this->db->quoteName('#__emundus_setup_profiles'))
						->where($conditions);

					$this->db->setQuery($query);
					$this->db->execute();

					// DELETE MENU TYPE
					$conditions = array($this->db->quoteName('id') . ' IN (' . implode(", ", array_values($mtids_arr)) . ')');

					$query->clear()
						->delete($this->db->quoteName('#__menu_types'))
						->where($conditions);

					$this->db->setQuery($query);
					$this->db->execute();

					// DELETE MENUS
					$conditions = array($this->db->quoteName('id') . ' IN (' . implode(", ", array_values($meids_arr)) . ')');

					$query->clear()
						->select('*')
						->from($this->db->quoteName('#__menu'))
						->where($conditions);
					$this->db->setQuery($query);
					$menus = $this->db->loadObjectList();

					foreach ($menus as $menu) {
						$falang->deleteFalang($menu->id, 'menu', 'title');

						foreach ($modules as $module) {
							$query
								->clear()
								->delete($this->db->quoteName('#__modules_menu'))
								->where($this->db->quoteName('moduleid') . ' = ' . $this->db->quote($module))
								->andWhere($this->db->quoteName('menuid') . ' = ' . $this->db->quote($menu->id));
							$this->db->setQuery($query);
							$this->db->execute();
						}
					}

					$query->clear()
						->delete($this->db->quoteName('#__menu'))
						->where($conditions);

					$this->db->setQuery($query);
					$this->db->execute();

					// DELETE FABRIK FORMS
					$conditions = array($this->db->quoteName('id') . ' IN (' . implode(", ", array_values($ffids_arr)) . ')');

					$query->clear()
						->select(['label AS label', 'intro AS intro'])
						->from($this->db->quoteName('#__fabrik_forms'))
						->where($conditions);
					$this->db->setQuery($query);
					$forms_texts = $this->db->loadObjectList();

					foreach ($forms_texts as $form_text) {
						$formbuilder->deleteTranslation($form_text->intro);
						$formbuilder->deleteTranslation($form_text->label);
					}

					$query->clear()
						->delete($this->db->quoteName('#__fabrik_forms'))
						->where($conditions);

					$this->db->setQuery($query);
					$this->db->execute();

					// DELETE FABRIK LISTS
					foreach ($dbtables_arr as $dbtablearr) {
						$query = "DROP TABLE " . $dbtablearr;
						$this->db->setQuery($query);
						$this->db->execute();
					}

					$query = $this->db->getQuery(true);

					$conditions = array($this->db->quoteName('id') . ' IN (' . implode(", ", array_values($flids_arr)) . ')');

					$query->delete($this->db->quoteName('#__fabrik_lists'))
						->where($conditions);

					$this->db->setQuery($query);
					$this->db->execute();

					// DELETE FORMLIST
					$conditions = array($this->db->quoteName('profile_id') . ' IN (' . implode(", ", array_values($data)) . ')');

					$query->clear()
						->delete($this->db->quoteName('#__emundus_setup_formlist'))
						->where($conditions);

					$this->db->setQuery($query);
					$this->db->execute();

					// DELETE FABRIK FORM GROUP
					$conditions = array($this->db->quoteName('id') . ' IN (' . implode(", ", array_values($ffgids_arr)) . ')');

					$query->clear()
						->delete($this->db->quoteName('#__fabrik_formgroup'))
						->where($conditions);

					$this->db->setQuery($query);
					$this->db->execute();

					// DELETE FABRIK GROUP
					$conditions = array($this->db->quoteName('id') . ' IN (' . implode(", ", array_values($fgids_arr)) . ')');

					$query->clear()
						->select(['label AS label'])
						->from($this->db->quoteName('#__fabrik_groups'))
						->where($conditions);
					$this->db->setQuery($query);
					$groups_texts = $this->db->loadObjectList();

					foreach ($groups_texts as $group_text) {
						$formbuilder->deleteTranslation($group_text->label);
					}

					$query->clear()
						->delete($this->db->quoteName('#__fabrik_groups'))
						->where($conditions);

					$this->db->setQuery($query);
					$this->db->execute();

					// DELETE FABRIK ELEMENTS
					$conditions = array($this->db->quoteName('id') . ' IN (' . implode(", ", array_values($feids_arr)) . ')');

					$query->clear()
						->select(['label AS label'])
						->from($this->db->quoteName('#__fabrik_elements'))
						->where($conditions);
					$this->db->setQuery($query);
					$elts_texts = $this->db->loadObjectList();

					foreach ($elts_texts as $elt_text) {
						$formbuilder->deleteTranslation($elt_text->label);
					}

					$query->clear()
						->delete($this->db->quoteName('#__fabrik_elements'))
						->where($conditions);

					$this->db->setQuery($query);

					return $this->db->execute();

				}
				catch (Exception $e) {
					JLog::add('component/com_emundus/models/form | Error when try to delete forms : ' . preg_replace("/[\r\n]/", " ", $query . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

					return false;
				}
			}
			catch (Exception $e) {
				JLog::add('component/com_emundus/models/form | Error when try to delete forms : ' . preg_replace("/[\r\n]/", " ", $query . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

				return false;
			}
		}
		else {
			return false;
		}
	}


	public function unpublishForm($data)
	{
		$response = [
			'status' => false,
			'msg'    => ''
		];

		
		$query = $this->db->getQuery(true);

		if (!empty($data)) {
			foreach ($data as $key => $val) {
				$data[$key] = htmlspecialchars($data[$key]);

				$campaigns = $this->getCampaignsByProfile($val);

				if (!empty($campaigns)) {
					$response['msg'] = '<div class="em-flex-column"><p><strong>' . JText::_('COM_EMUNDUS_FORM_UNPUBLISH_BLOCKED_BY_CAMPAIGN_LINK') . '</strong></p>';

					$response['msg'] .= '<ul>';
					foreach ($campaigns as $campaign) {
						$response['msg'] .= '<li><a href="/index.php?option=com_emundus&view=campaigns&layout=addnextcampaign&cid=' . $campaign['id'] . '" target="_blank">' . $campaign['label'] . '</a></li>';
					}
					$response['msg'] .= '</ul>';
					$response['msg'] .= '</div>';

					return $response;
				}
			}

			// we have to verify that form is not linked to any campaign

			try {
				$fields        = array($this->db->quoteName('status') . ' = 0');
				$se_conditions = array($this->db->quoteName('id') . ' IN (' . implode(", ", array_values($data)) . ')');

				$query->update($this->db->quoteName('#__emundus_setup_profiles'))
					->set($fields)
					->where($se_conditions);

				$this->db->setQuery($query);
				$response['status'] = $this->db->execute();
			}
			catch (Exception $e) {
				JLog::add('component/com_emundus/models/form | Error when unpublish forms : ' . preg_replace("/[\r\n]/", " ", $query . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');
				$response['status'] = false;
			}
		}

		return $response;
	}


	public function publishForm($data)
	{
		
		$query = $this->db->getQuery(true);

		if (!empty($data)) {
			foreach ($data as $key => $val) {
				$data[$key] = htmlspecialchars($data[$key]);
			}

			try {
				$fields        = array($this->db->quoteName('status') . ' = 1');
				$se_conditions = array($this->db->quoteName('id') . ' IN (' . implode(", ", array_values($data)) . ')');

				$query->update($this->db->quoteName('#__emundus_setup_profiles'))
					->set($fields)
					->where($se_conditions);

				$this->db->setQuery($query);

				return $this->db->execute();
			}
			catch (Exception $e) {
				JLog::add('component/com_emundus/models/form | Error when publish forms : ' . preg_replace("/[\r\n]/", " ", $query . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

				return false;
			}
		}
		else {
			return false;
		}
	}


	public function duplicateForm($data)
	{
		$duplicated = false;
		if (!is_array($data)) {
			$data = array($data);
		}

		if (!empty($data)) {
			
			$query = $this->db->getQuery(true);

			// Prepare languages
			$path_to_file   = basename(__FILE__) . '/../language/overrides/';
			$path_to_files  = array();
			$Content_Folder = array();

			$languages = JLanguageHelper::getLanguages();
			foreach ($languages as $language) {
				$path_to_files[$language->sef] = $path_to_file . $language->lang_code . '.override.ini';

				if (file_exists($path_to_files[$language->sef])) {
					$Content_Folder[$language->sef] = file_get_contents($path_to_files[$language->sef]);
				}
				else {
					$Content_Folder[$language->sef] = '';
				}
			}

			require_once(JPATH_SITE . '/components/com_emundus/models/formbuilder.php');
			$formbuilder = new EmundusModelFormbuilder();

			try {
				foreach ($data as $pid) {
					// Get profile
					$query->clear()
						->select('*')
						->from($this->db->quoteName('#__emundus_setup_profiles'))
						->where($this->db->quoteName('id') . ' = ' . $this->db->quote($pid));
					$this->db->setQuery($query);
					$oldprofile = $this->db->loadObject();

					if (!empty($oldprofile)) {
						// Create a new profile
						$query->clear()
							->insert('#__emundus_setup_profiles')
							->set($this->db->quoteName('label') . ' = ' . $this->db->quote($oldprofile->label . ' - Copy'))
							->set($this->db->quoteName('published') . ' = 1')
							->set($this->db->quoteName('menutype') . ' = ' . $this->db->quote($oldprofile->menutype))
							->set($this->db->quoteName('acl_aro_groups') . ' = ' . $this->db->quote($oldprofile->acl_aro_groups))
							->set($this->db->quoteName('status') . ' = ' . $this->db->quote($oldprofile->status));
						$this->db->setQuery($query);
						$this->db->execute();
						$newprofile = $this->db->insertid();

						if (!empty($newprofile)) {
							$newmenutype = 'menu-profile' . $newprofile;
							$newmenutype = $this->createMenuType($newmenutype, $oldprofile->label . ' - Copy');
							if (empty($newmenutype)) {
								JLog::add('Failed to create new menu from profile ' . $newprofile, JLog::WARNING, 'com_emundus.error');

								return false;
							}

							$query->clear()
								->update('#__emundus_setup_profiles')
								->set($this->db->quoteName('menutype') . ' = ' . $this->db->quote($newmenutype))
								->where($this->db->quoteName('id') . ' = ' . $this->db->quote($newprofile));
							$this->db->setQuery($query);
							$this->db->execute();
							//

							// Duplicate heading menu
							$query->clear()
								->select('*')
								->from('#__menu')
								->where($this->db->quoteName('menutype') . ' = ' . $this->db->quote($oldprofile->menutype))
								->andWhere($this->db->quoteName('type') . ' = ' . $this->db->quote('heading'))
								->andWhere('published = 1');

							$this->db->setQuery($query);
							$heading_to_duplicate = $this->db->loadObject();

							if (empty($heading_to_duplicate) || empty($heading_to_duplicate->id)) {
								JLog::add('Could not find heading menu when copying profile ' . $pid, JLog::INFO, 'com_emundus.form');

								$default_heading_menu                    = new stdClass();
								$default_heading_menu->id                = 1;
								$default_heading_menu->menutype          = '';
								$default_heading_menu->title             = "PROFILE $pid - Copy";
								$default_heading_menu->alias             = '';
								$default_heading_menu->note              = '';
								$default_heading_menu->path              = '';
								$default_heading_menu->link              = '';
								$default_heading_menu->type              = 'heading';
								$default_heading_menu->published         = 1;
								$default_heading_menu->parent_id         = 1;
								$default_heading_menu->level             = 1;
								$default_heading_menu->component_id      = 0;
								$default_heading_menu->checked_out       = 0;
								$default_heading_menu->params            = '{"menu-anchor_title":"","menu-anchor_css":"","menu-anchor_rel":"","menu_image":"","menu_image_css":"","menu_text":1,"menu_show":1}';
								$default_heading_menu->home              = 0;
								$default_heading_menu->language          = '*';
								$default_heading_menu->client_id         = 0;
								$default_heading_menu->template_style_id = 22;
								$default_heading_menu->access            = 1;
								$default_heading_menu->browserNav        = 0;
								$heading_to_duplicate                    = $default_heading_menu;
							}

							if (!empty($heading_to_duplicate->id)) {
								$query->clear();
								$query->insert($this->db->quoteName('#__menu'));
								foreach ($heading_to_duplicate as $key => $val) {
									if ($key != 'id' && $key != 'menutype' && $key != 'alias' && $key != 'path') {
										$query->set($key . ' = ' . $this->db->quote($val));
									}
									elseif ($key == 'menutype') {
										$query->set($key . ' = ' . $this->db->quote($newmenutype));
									}
									elseif ($key == 'path') {
										$query->set($key . ' = ' . $this->db->quote($newmenutype));
									}
									elseif ($key == 'alias') {
										$query->set($key . ' = ' . $this->db->quote(str_replace($formbuilder->getSpecialCharacters(), '-', strtolower($oldprofile->label . '-Copy')) . '-' . $newprofile));
									}
								}
								$this->db->setQuery($query);

								$inserted_heading = $this->db->execute();

								if ($inserted_heading) {
									// Get fabrik_lists
									$query->clear()
										->select('link')
										->from('#__menu')
										->where($this->db->quoteName('menutype') . ' = ' . $this->db->quote($oldprofile->menutype))
										->andWhere($this->db->quoteName('type') . ' = ' . $this->db->quote('component'))
										->andWhere('published = 1');
									$this->db->setQuery($query);
									$links = $this->db->loadObjectList();

									foreach ($links as $link) {
										if (strpos($link->link, 'formid') !== false) {
											$formsid_arr[] = explode('=', $link->link)[3];
										}
									}

									foreach ($formsid_arr as $formid) {
										$query->clear()
											->select('label, intro')
											->from($this->db->quoteName('#__fabrik_forms'))
											->where($this->db->quoteName('id') . ' = ' . $this->db->quote($formid));
										$this->db->setQuery($query);
										$form = $this->db->loadObject();

										$label = array();
										$intro = array();

										foreach ($languages as $language) {
											# Fabrik has a functionnality that adds <p> tags around the intro text, we need to remove them
											$stripped_intro = strip_tags($form->intro);
											if ($form->intro == '<p>' . $stripped_intro . '</p>') {
												$form->intro = $stripped_intro;
											}

											$label[$language->sef] = $formbuilder->getTranslation($form->label, $language->lang_code);
											$intro[$language->sef] = $formbuilder->getTranslation($form->intro, $language->lang_code);

											if ($label[$language->sef] == '') {
												$label[$language->sef] = $form->label;
											}
											if (!isset($intro[$language->sef])) {
												$intro[$language->sef] = '';
											}
										}

										$formbuilder->createMenuFromTemplate($label, $intro, $formid, $newprofile, true);
									}

									// Copy attachments
									$copied = $this->copyAttachmentsToNewProfile($pid, $newprofile);

									// Create checklist menu
									$this->addChecklistMenu($newprofile);

									$duplicated = $newprofile;
								}
								else {
									JLog::add('Failed to duplicate form, heading has not been created properly', JLog::WARNING, 'com_emundus.error');
								}
							}
							else {
								JLog::add('Failed to duplicate form, no heading menu found', JLog::WARNING, 'com_emundus.error');
							}
							//
						}
						else {
							JLog::add('Failed to duplicate form, empty new profile ', JLog::WARNING, 'com_emundus.error');
						}
					}
				}
			}
			catch (Exception $e) {
				JLog::add('component/com_emundus/models/form | Error when duplicate forms : ' . preg_replace("/[\r\n]/", " ", $query . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');
			}
		}

		return $duplicated;
	}

	public function copyAttachmentsToNewProfile($oldprofile, $newprofile)
	{
		$copied = false;
		$db = $this->db;

		if (!empty($oldprofile) && !empty($newprofile)) {

			$query = $this->db->getQuery(true);

			$new_profile_exists = false;
			$query->select('id')
				->from($this->db->quoteName('#__emundus_setup_profiles'))
				->where($this->db->quoteName('id') . ' = ' . $newprofile);

			try {
				$this->db->setQuery($query);
				$new_profile_exists = $this->db->loadResult();
			}
			catch (Exception $e) {
				JLog::add('component/com_emundus/models/form | Error when get profile : ' . preg_replace("/[\r\n]/", " ", $query . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');
			}

			if (!empty($new_profile_exists)) {
				$query->clear();
				$query->select('*')
					->from($this->db->quoteName('#__emundus_setup_attachment_profiles'))
					->where($this->db->quoteName('profile_id') . ' = ' . $oldprofile);

				try {
					$this->db->setQuery($query);
					$attachments = $this->db->loadAssocList();
				}
				catch (Exception $e) {
					JLog::add('component/com_emundus/models/form | Error when get attachments to copy : ' . preg_replace("/[\r\n]/", " ", $query . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

					return false;
				}

				if (!empty($attachments)) {
					$columns = array_keys($attachments[0]);
					$id_key  = array_search('id', $columns);
					unset($columns[$id_key]);

					$values = array();
					foreach ($attachments as $attachment) {
						$attachment['profile_id'] = $newprofile;
						unset($attachment['id']);

						foreach ($attachment as $key => $value) {
							if (empty($value) && $value != 0) {
								$attachment[$key] = null;
							}
						}

						// do not use db->quote() every time, only if the value is not an integer and not null
						$values[] = implode(',', array_map(function ($value) use ($db) {
							return is_null($value) ? 'NULL' : $db->quote($value);
						}, $attachment));
					}

					$query->clear()
						->insert($this->db->quoteName('#__emundus_setup_attachment_profiles'))
						->columns($this->db->quoteName($columns))
						->values($values);

					try {
						$this->db->setQuery($query);
						$copied = $this->db->execute();
					}
					catch (Exception $e) {
						JLog::add('component/com_emundus/models/form | Error when copy attachments to new profile : ' . preg_replace("/[\r\n]/", " ", $query . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');
					}
				}
			}
		}

		return $copied;
	}

	public function getFormById($id)
	{
		if (empty($id)) {
			return false;
		}


		$query = $this->db->getQuery(true);

		$query->select(['sp.*', 'sp.label AS form_label'])
			->from($this->db->quoteName('#__emundus_setup_profiles', 'sp'))
			->where($this->db->quoteName('sp.id') . ' = ' . $id);

		$this->db->setQuery($query);

		try {
			$this->db->setQuery($query);

			return $this->db->loadObject();
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/form | Error when get form by id ' . $id . ' : ' . preg_replace("/[\r\n]/", " ", $query . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

			return false;
		}
	}

	public function getFormByFabrikId($id)
	{
		$form = [];

		if (!empty($id)) {

			$query = $this->db->getQuery(true);

			$query->select('id, label')
				->from($this->db->quoteName('#__fabrik_forms'))
				->where($this->db->quoteName('id') . ' = ' . $id);


			try {
				$this->db->setQuery($query);
				$form = $this->db->loadObject();

				if (!empty($form->label)) {
					$form->label = JText::_($form->label);
				}
			}
			catch (Exception $e) {
				JLog::add('component/com_emundus/models/form | Error when get form by fabrik id ' . $id . ' : ' . preg_replace("/[\r\n]/", " ", $query . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');
			}
		}

		return $form;
	}

	public function createApplicantProfile($first_page = true)
	{
		require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'formbuilder.php');
		require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'settings.php');
		require_once(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'update.php');

		$formbuilder = new EmundusModelFormbuilder();
		$settings    = new EmundusModelSettings();

		$query = $this->db->getQuery(true);

		// Create profile
		$query->clear()
			->select('id')
			->from($this->db->quoteName('#__emundus_setup_profiles'))
			->order('id DESC');
		$this->db->setQuery($query);
		$lastprofile = $this->db->loadObjectList()[0];

		$columns = array(
			'label',
			'description',
			'published',
			'schoolyear',
			'candidature_start',
			'candidature_end',
			'menutype',
			'reference_letter',
			'acl_aro_groups',
			'is_evaluator',
			'evaluation_start',
			'evaluation_end',
			'evaluation',
			'status',
			'class');

		$values = array(
			'Nouveau formulaire',
			'',
			1,
			null,
			null,
			null,
			'menu-profile',
			null,
			2,
			0,
			null,
			null,
			null,
			1,
			null
		);

		if ($lastprofile->id == '999' || $lastprofile->id == '1000') {
			array_unshift($columns, 'id');
			array_unshift($values, 1001);
		}
		$query->clear()
			->insert($this->db->quoteName('#__emundus_setup_profiles'))
			->columns($this->db->quoteName($columns))
			->values(implode(',', $this->db->Quote($values)));

		try {
			$this->db->setQuery($query);
			$this->db->execute();
			$newprofile = $this->db->insertid();
			if (empty($newprofile)) {
				return false;
			}

			// Create menutype
			$menutype = $this->createMenuType('menu-profile' . $newprofile, 'Nouveau formulaire');
			if (empty($menutype)) {
				return false;
			}

			$query->clear()
				->update($this->db->quoteName('#__emundus_setup_profiles'))
				->set($this->db->quoteName('menutype') . ' = ' . $this->db->quote($menutype))
				->where($this->db->quoteName('id') . ' = ' . $this->db->quote($newprofile));
			$this->db->setQuery($query);
			$this->db->execute();


			// Create heading menu
			$datas        = [
				'menutype'     => 'menu-profile' . $newprofile,
				'title'        => 'Nouveau formulaire',
				'link'         => '#',
				'type'         => 'heading',
				'component_id' => 0,
				'params'       => []
			];
			$heading_menu = EmundusHelperUpdate::addJoomlaMenu($datas);
			if ($heading_menu['status'] !== true) {
				return false;
			}
			$header_menu_id = $heading_menu['id'];

			$alias = 'menu-profile' . $newprofile . '-heading-' . $header_menu_id;
			$query->clear()
				->update($this->db->quoteName('#__menu'))
				->set($this->db->quoteName('alias') . ' = ' . $this->db->quote($alias))
				->set($this->db->quoteName('path') . ' = ' . $this->db->quote($alias))
				->where($this->db->quoteName('id') . ' = ' . $this->db->quote($header_menu_id));
			$this->db->setQuery($query);
			$this->db->execute();

			// Create first page
			if ($first_page) {
				$label = [
					'fr' => 'Ma première page',
					'en' => 'My first page'
				];
				$intro = [
					'fr' => 'Décrivez votre page de formulaire avec une introduction',
					'en' => 'Describe your form page with an introduction'
				];
				$formbuilder->createApplicantMenu($label, $intro, $newprofile, 'false');
			}

			// Create submittion page
			$label               = [
				'fr' => "Confirmation d'envoi de dossier",
				'en' => 'Data & disclaimer confirmation'
			];
			$intro               = [
				'fr' => '',
				'en' => ''
			];
			$submittion_page_res = $formbuilder->createSubmittionPage($label, $intro, $newprofile);
			if ($submittion_page_res['status'] !== true) {
				return false;
			}

			// Create checklist menu
			$this->addChecklistMenu($newprofile);
			//

			return $newprofile;
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/form | Error when create a setup_profile : ' . preg_replace("/[\r\n]/", " ", $query . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus.error');

			return false;
		}
	}

	public function createFormEval()
	{
		require_once(JPATH_ROOT . '/components/com_emundus/models/formbuilder.php');
		$m_formbuilder = new EmundusModelFormbuilder();
		$form_id       = $m_formbuilder->createFabrikForm('EVALUATION', ['fr' => 'Nouvelle Évaluation', 'en' => 'New Evaluation'], ['fr' => 'Introduction de l\'évaluation', 'en' => 'Evaluation introduction'], 'eval');

		if (!empty($form_id)) {
			$group = $m_formbuilder->createGroup(array('fr' => 'Hidden group', 'en' => 'Hidden group'), $form_id, -1);
			if (!empty($group)) {
				// Create hidden group
				$m_formbuilder->createElement('id', $group['group_id'], 'internalid', 'id', '', 1, 0);
				$m_formbuilder->createElement('time_date', $group['group_id'], 'date', 'time date', '', 1, 0);
				$m_formbuilder->createElement('fnum', $group['group_id'], 'field', 'fnum', '{jos_emundus_evaluations___fnum}', 1, 0, 1, 1, 0, 44);
				$m_formbuilder->createElement('user', $group['group_id'], 'databasejoin', 'user', '{$my->id}', 1, 0);
				$m_formbuilder->createElement('student_id', $group['group_id'], 'field', 'student_id', '{jos_emundus_evaluations___student_id}', 1, 0);
			}


			$query = $this->db->getQuery(true);

			$query->clear()
				->select('*')
				->from($this->db->quoteName('#__fabrik_lists'))
				->where($this->db->quoteName('db_table_name') . ' LIKE ' . $this->db->quote('jos_emundus_evaluations'));

			$this->db->setQuery($query);
			$list = $this->db->loadAssoc();

			if (!empty($list)) {
				$list_id = $m_formbuilder->copyList($list, $form_id);

				if (empty($list_id)) {
					JLog::add('component/com_emundus/models/form | Error when create a list for evaluation form, could not copy list based on jos_emundus_evaluations', JLog::WARNING, 'com_emundus.error');
				}
			}
			else {
				JLog::add('component/com_emundus/models/form | Error when create a list for evaluation form, could not find list with jos_emundus_evaluations', JLog::WARNING, 'com_emundus.error');
			}
		}
		else {
			JLog::add('component/com_emundus/models/form | Error when create a form for evaluation form', JLog::WARNING, 'com_emundus.error');
		}

		return $form_id;
	}

	public function createMenuType($menutype, $title)
	{
		$menutype_table = JTableNested::getInstance('MenuType');

		try {
			JFactory::$database = null;


			$query = $this->db->getQuery(true);


			$query->clear()
				->select('menutype')
				->from($this->db->quoteName('#__menu_types'))
				->where($this->db->quoteName('menutype') . ' LIKE ' . $this->db->quote($menutype));
			$this->db->setQuery($query);
			$is_existing = $this->db->loadResult();

			if (empty($is_existing)) {
				$data = array(
					'menutype'    => $menutype,
					'title'       => $title,
					'description' => '',
					'client_id'   => 0,
				);

				if (!$menutype_table->save($data)) {
					return '';
				}

				return $menutype;
			}
			else {
				return $is_existing;
			}
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/form | Cannot create the menutype ' . $menutype . ' : -> ' . $e->getMessage(), JLog::ERROR, 'com_emundus');

			return '';
		}
	}


	public function createMenu($menu, $menutype)
	{
		
		$query = $this->db->getQuery(true);

		// Insert columns.
		$columns = array(
			'menutype',
			'title',
			'alias',
			'note',
			'path',
			'link',
			'type',
			'published',
			'parent_id',
			'level',
			'component_id',
			'checked_out',
			'checked_out_time',
			'browserNav',
			'access',
			'img',
			'template_style_id',
			'params',
			'lft',
			'rgt',
			'home',
			'language',
			'client_id',
		);

		// Insert values.
		$values = array(
			$menutype,
			$menu['title'],
			$menu['alias'],
			$menu['note'],
			$menu['path'],
			$menu['link'],
			$menu['type'],
			$menu['published'],
			$menu['parent_id'],
			$menu['level'],
			$menu['component_id'],
			$menu['checked_out'],
			$menu['checked_out_time'],
			$menu['browserNav'],
			$menu['access'],
			$menu['img'],
			$menu['template_style_id'],
			$menu['params'],
			$menu['lft'],
			$menu['rgt'],
			$menu['home'],
			$menu['language'],
			$menu['client_id'],
		);

		$query->insert($this->db->quoteName('#__menu'))
			->columns($this->db->quoteName($columns))
			->values(implode(',', $this->db->Quote($values)));

		try {
			$this->db->setQuery($query);

			return $this->db->execute();
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/form | Cannot create the menu : ' . preg_replace("/[\r\n]/", " ", $query . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

			return false;
		}
	}


	public function updateForm($id, $data)
	{
		$query_pid = $this->db->getQuery(true);

		if (!empty($data)) {
			$fields = [];

			foreach ($data as $key => $val) {
				$insert   = $this->db->quoteName(htmlspecialchars($key)) . ' = ' . $this->db->quote(htmlspecialchars($val));
				$fields[] = $insert;
			}

			$query_pid->update($this->db->quoteName('#__emundus_setup_profiles'))
				->set($fields)
				->where($this->db->quoteName('id') . ' = ' . $this->db->quote($id));

			try {
				$this->db->setQuery($query_pid);

				return $this->db->execute();
			}
			catch (Exception $e) {
				JLog::add('component/com_emundus/models/form | Cannot update the form ' . $id . ' : ' . preg_replace("/[\r\n]/", " ", $query_pid . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

				return false;
			}
		}
		else {
			return false;
		}
	}

	public function updateFormLabel($prid, $label)
	{
		$results = [];

		if (!empty($prid)) {
			require_once(JPATH_SITE . '/components/com_emundus/models/formbuilder.php');
			$formbuilder = new EmundusModelFormbuilder;


			$query = $this->db->getQuery(true);

			$query->update($this->db->quoteName('#__menu_types'))
				->set($this->db->quoteName('title') . ' = ' . $this->db->quote($label))
				->where($this->db->quoteName('menutype') . ' = ' . $this->db->quote('menu-profile' . $prid));

			try {
				$this->db->setQuery($query);
				$results[] = $this->db->execute();

				$query->clear()
					->select($this->db->quoteName('id'))
					->from($this->db->quoteName('#__menu'))
					->where($this->db->quoteName('menutype') . ' = ' . $this->db->quote('menu-profile' . $prid))
					->andWhere($this->db->quoteName('type') . ' = ' . $this->db->quote('heading'));
				$this->db->setQuery($query);
				$heading_id = $this->db->loadResult();

				$alias = 'menu-profile' . $prid . '-heading-' . $heading_id;
				$query->clear()
					->update($this->db->quoteName('#__menu'))
					->set($this->db->quoteName('title') . ' = ' . $this->db->quote($label))
					->set($this->db->quoteName('alias') . ' = ' . $this->db->quote($alias))
					->set($this->db->quoteName('path') . ' = ' . $this->db->quote($alias))
					->where($this->db->quoteName('menutype') . ' = ' . $this->db->quote('menu-profile' . $prid))
					->andWhere($this->db->quoteName('type') . ' = ' . $this->db->quote('heading'));
				$this->db->setQuery($query);
				$results[] = $this->db->execute();

				$query->clear()
					->update($this->db->quoteName('#__emundus_setup_profiles'))
					->set($this->db->quoteName('label') . ' = ' . $this->db->quote($label))
					->where($this->db->quoteName('id') . ' = ' . $this->db->quote($prid));
				$this->db->setQuery($query);
				$results[] = $this->db->execute();
			}
			catch (Exception $e) {
				JLog::add('component/com_emundus/models/form | Cannot update the form ' . $prid . ' : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');
			}
		}

		return $results;
	}


	public function getAllDocuments($prid, $cid)
	{

		$query = $this->db->getQuery(true);

		require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'falang.php');

		$falang = new EmundusModelFalang;

		try {
			$query->select('*')
				->from($this->db->quoteName('#__emundus_setup_attachment_profiles'))
				->where($this->db->quoteName('profile_id') . ' = ' . $this->db->quote($prid))
				->andWhere($this->db->quoteName('campaign_id') . ' IS NULL ');
			$this->db->setQuery($query);
			$old_docs = $this->db->loadObjectList();

			if (!empty($old_docs)) {
				$query->clear()
					->select('id')
					->from($this->db->quoteName('#__emundus_setup_campaigns'))
					->where($this->db->quoteName('profile_id') . ' = ' . $this->db->quote($prid));
				$this->db->setQuery($query);
				$campaignstoaffect = $this->db->loadObjectList();

				foreach ($campaignstoaffect as $campaign) {
					foreach ($old_docs as $old_doc) {
						$query->clear()
							->insert($this->db->quoteName('#__emundus_setup_attachment_profiles'));
						foreach ($old_doc as $key => $value) {
							if ($key != 'id' && $key != 'campaign_id') {
								$query->set($key . ' = ' . $this->db->quote($value));
							}
							elseif ($key == 'campaign_id') {
								$query->set($this->db->quoteName('campaign_id') . ' = ' . $this->db->quote($campaign->id));
							}
						}
						$this->db->setQuery($query);
						$this->db->execute();
					}
				}

				$query->clear()
					->delete($this->db->quoteName('#__emundus_setup_attachment_profiles'))
					->where($this->db->quoteName('profile_id') . ' = ' . $this->db->quote($prid))
					->andWhere($this->db->quoteName('campaign_id') . ' IS NULL');
				$this->db->setQuery($query);
				$this->db->execute();
			}

			$query->clear()
				->select([
					'sap.attachment_id AS id',
					'sap.ordering',
					'sap.mandatory AS need',
					'sa.value',
					'sa.description',
					'sa.allowed_types',
					'sa.nbmax',
					'sa.lbl'
				])
				->from($this->db->quoteName('#__emundus_setup_attachment_profiles', 'sap'))
				->leftJoin($this->db->quoteName('#__emundus_setup_attachments', 'sa') . ' ON ' . $this->db->quoteName('sa.id') . ' = ' . $this->db->quoteName('sap.attachment_id'))
				->order($this->db->quoteName('sap.ordering'))
				->where($this->db->quoteName('sap.published') . ' = 1')
				->andWhere($this->db->quoteName('sap.campaign_id') . ' = ' . $cid);

			$this->db->setQuery($query);
			$documents = $this->db->loadObjectList();

			foreach ($documents as $document) {
				if (strpos($document->lbl, '_em') === 0) {
					$document->can_be_deleted = true;
				}
				else {
					$document->can_be_deleted = false;
				}

				$f_values           = $falang->getFalang($document->id, 'emundus_setup_attachments', 'value');
				$document->value_en = $f_values->en->value;
				$document->value_fr = $f_values->fr->value;

				$f_descriptions           = $falang->getFalang($document->id, 'emundus_setup_attachments', 'description');
				$document->description_en = $f_descriptions->en->value;
				$document->description_fr = $f_descriptions->fr->value;
			}

			return $documents;
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/form | Error at getting documents of the campaign ' . $cid . ' : ' . preg_replace("/[\r\n]/", " ", $query . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

			return false;
		}
	}


	public function getUnDocuments()
	{

		$query     = $this->db->getQuery(true);
		$languages = JLanguageHelper::getLanguages();

		require_once(JPATH_SITE . '/components/com_emundus/models/falang.php');

		$falang = new EmundusModelFalang;

		$query->select(array(' DISTINCT a.*', 'b.mandatory'))
			->from($this->db->quoteName('#__emundus_setup_attachments', 'a'))
			->leftJoin($this->db->quoteName('#__emundus_setup_attachment_profiles', 'b') . ' ON ' . $this->db->quoteName('b.attachment_id') . ' = ' . $this->db->quoteName('a.id'))
			->where($this->db->quoteName('a.published') . ' = 1')
			->group($this->db->quoteName('a.id'))
			->order($this->db->quoteName('a.value'));

		try {
			$this->db->setQuery($query);
			$undocuments = $this->db->loadObjectList();


			foreach ($undocuments as $undocument) {
				if (strpos($undocument->lbl, '_em') === 0) {
					$undocument->can_be_deleted = true;
				}
				else {
					$undocument->can_be_deleted = false;
				}

				$f_values                = $falang->getFalang($undocument->id, 'emundus_setup_attachments', 'value', $undocument->value);
				$f_descriptions          = $falang->getFalang($undocument->id, 'emundus_setup_attachments', 'description', $undocument->description);
				$undocument->name        = $f_values;
				$undocument->description = $f_descriptions;
			}

			return $undocuments;
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/form | Error getting documents not associated : ' . preg_replace("/[\r\n]/", " ", $query . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

			return false;
		}
	}

	public function getAttachments()
	{
		$attachments = [];

		$query = $this->db->getQuery(true);

		$query->select('*')
			->from($this->db->quoteName('#__emundus_setup_attachments'))
			->where($this->db->quoteName('published') . ' = 1')
			->order('value');

		try {
			$this->db->setQuery($query);
			$attachments = $this->db->loadObjectList();

			if (!empty($attachments)) {
				require_once(JPATH_SITE . '/components/com_emundus/models/falang.php');
				$falang = new EmundusModelFalang;

				foreach ($attachments as $attachment) {
					$attachment->can_be_deleted = strpos($attachment->lbl, '_em') === 0;
					$attachment->name           = $falang->getFalang($attachment->id, 'emundus_setup_attachments', 'value', $attachment->value);
					$attachment->description    = $falang->getFalang($attachment->id, 'emundus_setup_attachments', 'description', $attachment->description);
				}
			}
		}
		catch (Exception $e) {
			JLog::add('Failed to get attachments ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
		}

		return $attachments;
	}

	/**
	 * @param $documentIds
	 *
	 * @return array
	 */
	public function getDocumentsUsage($documentIds): array
	{
		$forms = [];

		if (!empty($documentIds)) {

			$query = $this->db->getQuery(true);

			$query->select('jesap.attachment_id, jesap.profile_id, jesp.label')
				->from('jos_emundus_setup_attachment_profiles AS jesap')
				->leftJoin('jos_emundus_setup_profiles AS jesp ON jesap.profile_id = jesp.id')
				->where('jesap.attachment_id  IN (' . implode(',', $documentIds) . ')');

			$this->db->setQuery($query);

			try {
				$profile_infos = $this->db->loadObjectList();
			}
			catch (Exception $e) {
				$msg = 'Error trying to get profile info from attachment_id ' . $e->getMessage();
				JLog::add($msg, JLog::ERROR, 'com_emundus');
			}

			if (!empty($profile_infos)) {
				foreach ($profile_infos as $profile_info) {
					if (!isset($forms[$profile_info->attachment_id])) {
						$forms[$profile_info->attachment_id] = [
							'profiles' => [],
							'usage'    => 0
						];
					}

					$forms[$profile_info->attachment_id]['profiles'][] = [
						'id'    => $profile_info->profile_id,
						'label' => $profile_info->label
					];
					$forms[$profile_info->attachment_id]['usage']++;
				}
			}
		}

		return $forms;
	}

	public function deleteRemainingDocuments($prid, $allDocumentsIds)
	{
		

		$values = [];

		foreach ($allDocumentsIds as $document) {
			array_push($values, '(' . $document . ',' . $prid . ',0,0)');
		}

		$query =
			'INSERT INTO jos_emundus_setup_attachment_profiles 
        (attachment_id, profile_id, displayed, published)
        VALUES 
        ' .
			implode(',', $values) .
			'
        ON DUPLICATE KEY UPDATE 
        displayed = VALUES(displayed),
        published = VALUES(published),
        profile_id = VALUES(profile_id)
        ;';

		try {
			$this->db->setQuery($query);

			return $this->db->execute();
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/form | Error deleting documents : ' . preg_replace("/[\r\n]/", " ", $query . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

			return false;
		}
	}


	public function removeDocument($did, $prid, $cid)
	{
		
		$query = $this->db->getQuery(true);

		$query->delete($this->db->quoteName('#__emundus_setup_attachment_profiles'))
			->where($this->db->quoteName('attachment_id') . ' = ' . $this->db->quote($did))
			->andWhere($this->db->quoteName('campaign_id') . ' = ' . $this->db->quote($cid))
			->andWhere($this->db->quoteName('profile_id') . ' = ' . $this->db->quote($prid));
		try {
			$this->db->setQuery($query);
			$this->db->execute();

			$documents_campaign = EmundusModelform::getAllDocuments($prid, $cid);

			if (empty($documents_campaign)) {
				$this->removeChecklistMenu($prid);
			}

			return true;
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/form | Error remove document ' . $did . ' associated to the campaign ' . $cid . ' : ' . preg_replace("/[\r\n]/", " ", $query . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

			return false;
		}
	}

	public function updateMandatory($did, $prid, $cid)
	{
		
		$query = $this->db->getQuery(true);

		try {
			$query->select('id,mandatory')
				->from($this->db->quoteName('#__emundus_setup_attachment_profiles'))
				->where($this->db->quoteName('attachment_id') . ' = ' . $this->db->quote($did))
				->andWhere($this->db->quoteName('profile_id') . ' = ' . $this->db->quote($prid))
				->andWhere($this->db->quoteName('campaign_id') . ' = ' . $this->db->quote($cid));
			$this->db->setQuery($query);
			$attachment = $this->db->loadObject();
			$mandatory  = intval($attachment->mandatory);

			if ($mandatory == 0) {
				$mandatory = 1;
			}
			else {
				$mandatory = 0;
			}

			$query->clear()
				->update($this->db->quoteName('#__emundus_setup_attachment_profiles'))
				->set($this->db->quoteName('mandatory') . ' = ' . $this->db->quote($mandatory))
				->where($this->db->quoteName('id') . ' = ' . $this->db->quote($attachment->id));

			$this->db->setQuery($query);

			return $this->db->execute();
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/form | Error remove document ' . $did . ' associated to the campaign ' . $cid . ' : ' . preg_replace("/[\r\n]/", " ", $query . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

			return false;
		}
	}

	public function addDocument($did, $profile, $campaign)
	{
		
		$query = $this->db->getQuery(true);

		try {
			// Create checklist menu if documents are asked
			$query->clear()
				->select('*')
				->from($this->db->quoteName('#__menu'))
				->where($this->db->quoteName('alias') . ' = ' . $this->db->quote('checklist-' . $profile));
			$this->db->setQuery($query);
			$checklist = $this->db->loadObject();

			if ($checklist == null) {
				$this->addChecklistMenu($profile);
			}
			//

			$query->clear()
				->insert($this->db->quoteName('#__emundus_setup_attachment_profiles'))
				->set($this->db->quoteName('profile_id') . ' = ' . $this->db->quote($profile))
				->set($this->db->quoteName('campaign_id') . ' = ' . $this->db->quote($campaign))
				->set($this->db->quoteName('attachment_id') . ' = ' . $this->db->quote($did))
				->set($this->db->quoteName('displayed') . ' = ' . $this->db->quote(1))
				->set($this->db->quoteName('mandatory') . ' = ' . $this->db->quote(0))
				->set($this->db->quoteName('ordering') . ' = ' . $this->db->quote(0));
			$this->db->setQuery($query);
			$this->db->execute();

			$documents_campaign = EmundusModelform::getAllDocuments($profile, $campaign);

			if (empty($documents_campaign)) {
				$this->removeChecklistMenu($profile);
			}

			return true;
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/form | Error remove document ' . $did . ' associated to the campaign ' . $campaign . ' : ' . preg_replace("/[\r\n]/", " ", $query . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

			return false;
		}
	}

	public function deleteDocument($did)
	{
		
		$query = $this->db->getQuery(true);

		require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'falang.php');

		$falang = new EmundusModelFalang;

		try {
			$falang->deleteFalang($did, 'emundus_setup_attachments', 'value');
			$falang->deleteFalang($did, 'emundus_setup_attachments', 'description');

			$query->clear()
				->delete($this->db->quoteName('#__emundus_setup_attachment_profiles'))
				->where($this->db->quoteName('attachment_id') . ' = ' . $this->db->quote($did));

			$this->db->setQuery($query);
			$this->db->execute();

			$query->clear()
				->delete($this->db->quoteName('#__emundus_setup_attachments'))
				->where($this->db->quoteName('id') . ' = ' . $this->db->quote($did));

			$this->db->setQuery($query);

			return $this->db->execute();
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/form | Error when delete the document ' . $did . ' : ' . preg_replace("/[\r\n]/", " ", $query . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

			return false;
		}
	}

	public function addChecklistMenu($prid)
	{
		
		$query = $this->db->getQuery(true);

		$eMConfig = JComponentHelper::getParams('com_emundus');
		$modules  = $eMConfig->get('form_builder_page_creation_modules', [93, 102, 103, 104, 168, 170]);

		try {
			// Create the menu
			$submittion_page = $this->getSubmittionPage($prid);

			$params = array(
				'custom_title'         => "",
				'show_info_panel'      => "0",
				'show_info_legend'     => "1",
				'show_browse_button'   => "0",
				'show_shortdesc_input' => "0",
				'required_desc'        => "0",
				'show_nb_column'       => "1",
				'is_admission'         => "0",
				'notify_complete_file' => 0,
				'menu-anchor_title'    => "Documents",
				'menu-anchor_css'      => "huge circular inverted blue upload outline icon",
				'menu_image'           => "0",
				'menu_image_css'       => "0",
				'menu_text'            => 1,
				'menu_show'            => 1,
				'page_title'           => "Documents",
				'show_page_heading'    => "",
				'page_heading'         => "",
				'pageclass_sfx'        => "applicant-form",
				'meta_description'     => "",
				'meta_keywords'        => "",
				'robots'               => "",
				'secure'               => 0,
			);

			$datas          = [
				'menutype'     => 'menu-profile' . $prid,
				'title'        => 'Documents',
				'alias'        => 'checklist-' . $prid,
				'path'         => 'checklist-' . $prid,
				'link'         => 'index.php?option=com_emundus&view=checklist',
				'type'         => 'component',
				'component_id' => 11369,
				'params'       => $params
			];
			$checklist_menu = EmundusHelperUpdate::addJoomlaMenu($datas, $submittion_page->id, 1, 'before', $modules);
			if ($checklist_menu['status'] !== true) {
				return false;
			}

			$newmenuid = $checklist_menu['id'];

			// Affect documents module to each menus of profile
			$query->clear()
				->select('*')
				->from($this->db->quoteName('#__menu'))
				->where($this->db->quoteName('menutype') . ' = ' . $this->db->quote('menu-profile' . $prid));
			$this->db->setQuery($query);
			$menus = $this->db->loadObjectList();

			foreach ($menus as $menu) {
				$query->clear()
					->select('moduleid')
					->from($this->db->quoteName('#__modules_menu'))
					->where($this->db->quoteName('moduleid') . ' = 103')
					->andWhere($this->db->quoteName('menuid') . ' = ' . $this->db->quote($menu->id));
				$this->db->setQuery($query);
				$is_existing = $this->db->loadResult();

				if (!$is_existing) {
					$query->clear()
						->insert($this->db->quoteName('#__modules_menu'))
						->set($this->db->quoteName('moduleid') . ' = 103')
						->set($this->db->quoteName('menuid') . ' = ' . $this->db->quote($menu->id));
					$this->db->setQuery($query);
					$this->db->execute();
				}

				$query->clear()
					->select('moduleid')
					->from($this->db->quoteName('#__modules_menu'))
					->where($this->db->quoteName('moduleid') . ' = 104')
					->andWhere($this->db->quoteName('menuid') . ' = ' . $this->db->quote($menu->id));
				$this->db->setQuery($query);
				$is_existing = $this->db->loadResult();

				if (!$is_existing) {
					$query->clear()
						->insert($this->db->quoteName('#__modules_menu'))
						->set($this->db->quoteName('moduleid') . ' = 104')
						->set($this->db->quoteName('menuid') . ' = ' . $this->db->quote($menu->id));
					$this->db->setQuery($query);
					$this->db->execute();
				}
			}

			//

			return $newmenuid;
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/form | Error to add the checklist module to form (' . $prid . ') menus : ' . preg_replace("/[\r\n]/", " ", $query . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

			return false;
		}
	}

	public function removeChecklistMenu($prid)
	{
		
		$query = $this->db->getQuery(true);

		$eMConfig = JComponentHelper::getParams('com_emundus');
		$modules  = $eMConfig->get('form_builder_page_creation_modules', [93, 102, 103, 104, 168, 170]);

		$query->clear()
			->select('*')
			->from($this->db->quoteName('#__menu'))
			->where($this->db->quoteName('alias') . ' = ' . $this->db->quote('checklist-' . $prid));
		try {
			$this->db->setQuery($query);
			$checklist = $this->db->loadObject();

			foreach ($modules as $module) {
				$query->clear()
					->delete($this->db->quoteName('#__modules_menu'))
					->where($this->db->quoteName('moduleid') . ' = ' . $this->db->quote($module))
					->andWhere($this->db->quoteName('menuid') . ' = ' . $this->db->quote($checklist->id));
				$this->db->setQuery($query);
				$this->db->execute();
			}

			$query->clear()
				->select('*')
				->from($this->db->quoteName('#__menu'))
				->where($this->db->quoteName('menutype') . ' = ' . $this->db->quote('menu-profile' . $prid));
			$this->db->setQuery($query);
			$menus = $this->db->loadObjectList();

			foreach ($menus as $menu) {
				$query->clear()
					->delete($this->db->quoteName('#__modules_menu'))
					->where($this->db->quoteName('moduleid') . ' IN (103,104)')
					->andWhere($this->db->quoteName('menuid') . ' = ' . $this->db->quote($menu->id));
				$this->db->setQuery($query);
				$this->db->execute();
			}

			$query->clear()
				->delete($this->db->quoteName('#__menu'))
				->where($this->db->quoteName('id') . ' = ' . $this->db->quote($checklist->id));
			$this->db->setQuery($query);

			return $this->db->execute();
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/form | Error to remove the checklist module to form (' . $prid . ') menus : ' . preg_replace("/[\r\n]/", " ", $query . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

			return false;
		}
	}


	public function getFormsByProfileId($profile_id)
	{
		if (empty($profile_id)) {
			return false;
		}

		require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'formbuilder.php');
		$formbuilder = new EmundusModelFormbuilder;

		$query = $this->db->getQuery(true);

		$query->select(['menu.link', 'menu.rgt'])
			->from($this->db->quoteName('#__menu', 'menu'))
			->leftJoin($this->db->quoteName('#__menu_types', 'mt') . ' ON ' . $this->db->quoteName('mt.menutype') . ' = ' . $this->db->quoteName('menu.menutype'))
			->leftJoin($this->db->quoteName('#__emundus_setup_profiles', 'sp') . ' ON ' . $this->db->quoteName('sp.menutype') . ' = ' . $this->db->quoteName('mt.menutype'))
			->where($this->db->quoteName('sp.id') . ' = ' . $profile_id)
			->where($this->db->quoteName('menu.parent_id') . ' != 1')
			->where($this->db->quoteName('menu.published') . ' = 1')
			->where($this->db->quoteName('menu.link') . ' LIKE ' . $this->db->quote('%option=com_fabrik%'))
			->group('menu.rgt')
			->order('menu.rgt ASC');


		try {
			$this->db->setQuery($query);
			$forms = $this->db->loadObjectList();

			foreach ($forms as $form) {
				$link     = explode('=', $form->link);
				$form->id = $link[sizeof($link) - 1];

				$query->clear()
					->select('label')
					->from($this->db->quoteName('#__fabrik_forms'))
					->where($this->db->quoteName('id') . ' = ' . $this->db->quote($form->id));
				$this->db->setQuery($query);
				$form->label = $formbuilder->getJTEXT($this->db->loadResult());
				print_r($forms->label);
			}

			return $forms;
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/form | Error at getting form pages by profile_id ' . $profile_id . ' : ' . preg_replace("/[\r\n]/", " ", $query . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

			return false;
		}
	}

	public function getCampaignsByProfile($profile_id)
	{
		$campaigns = [];

		if (!empty($profile_id)) {

			$query = $this->db->getQuery(true);

			$query->select(['sc.id', 'sc.label'])
				->from($this->db->quoteName('#__emundus_setup_campaigns', 'sc'))
				->leftJoin($this->db->quoteName('#__emundus_setup_profiles', 'sp') . ' ON ' . $this->db->quoteName('sp.id') . ' = ' . $this->db->quoteName('sc.profile_id'))
				->where($this->db->quoteName('sp.id') . ' = ' . $profile_id);

			try {
				$campaigns = $this->db->setQuery($query)->loadAssocList();
			}
			catch (Exception $e) {
				JLog::add('Failed to get campaigns from form_id ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
			}
		}

		return $campaigns;
	}

	public function getGroupsByForm($form_id)
	{
		require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'formbuilder.php');

		$formbuilder = new EmundusModelFormbuilder;


		$query = $this->db->getQuery(true);

		$query->select(['g.id', 'g.label', 'g.params', 'g.published'])
			->from($this->db->quoteName('#__fabrik_formgroup', 'fg'))
			->leftJoin($this->db->quoteName('#__fabrik_groups', 'g') . ' ON ' . $this->db->quoteName('g.id') . ' = ' . $this->db->quoteName('fg.group_id'))
			->where($this->db->quoteName('fg.form_id') . ' = ' . $form_id)
			->order('fg.ordering ASC');


		try {
			$this->db->setQuery($query);
			$groups = $this->db->loadObjectList();

			foreach ($groups as $key => $group) {
				$params = json_decode($group->params, true);
				if ($params['repeat_group_show_first'] == -1) {
					array_splice($groups, $key, 1);
				}
				$group->label = $formbuilder->getJTEXT($group->label);
			}

			return $groups;
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/form | Error at getting groups by form_id ' . $form_id . ' : ' . preg_replace("/[\r\n]/", " ", $query . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

			return false;
		}
	}

	public function getSubmittionPage($prid)
	{
		if (empty($prid)) {
			return false;
		}


		$query = $this->db->getQuery(true);

		$query->select(['menu.link', 'menu.rgt', 'menu.id'])
			->from($this->db->quoteName('#__menu', 'menu'))
			->leftJoin($this->db->quoteName('#__menu_types', 'mt') . ' ON ' . $this->db->quoteName('mt.menutype') . ' = ' . $this->db->quoteName('menu.menutype'))
			->leftJoin($this->db->quoteName('#__emundus_setup_profiles', 'sp') . ' ON ' . $this->db->quoteName('sp.menutype') . ' = ' . $this->db->quoteName('mt.menutype'))
			->where($this->db->quoteName('sp.id') . ' = ' . $prid)
			->andWhere($this->db->quoteName('menu.parent_id') . ' = 1')
			->andWhere($this->db->quoteName('menu.type') . ' = ' . $this->db->quote('component'))
			->andWhere($this->db->quoteName('menu.published') . ' = 1');

		try {
			$this->db->setQuery($query);
			$menus    = $this->db->loadObjectList();
			$sub_page = new stdClass();

			foreach ($menus as $menu) {
				$formid = explode('=', $menu->link)[3];
				if ($formid != null) {
					$query->clear()
						->select('count(id)')
						->from($this->db->quoteName('#__fabrik_lists'))
						->where($this->db->quoteName('db_table_name') . ' LIKE ' . $this->db->quote('jos_emundus_declaration'))
						->andWhere($this->db->quoteName('form_id') . ' = ' . $this->db->quote($formid));
					$this->db->setQuery($query);
					$submittion = $this->db->loadResult();
					if ($submittion > 0) {
						$sub_page->link = $menu->link;
						$sub_page->rgt  = $menu->rgt;
						$sub_page->id   = $menu->id;

						break;
					}
				}
			}

			return $sub_page;
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/form | Error at getting the submittion page of the form ' . $prid . ' : ' . preg_replace("/[\r\n]/", " ", $query . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

			return false;
		}

	}


	public function getProfileLabelByProfileId($profile_id)
	{
		if (empty($profile_id)) {
			return false;
		}


		$query = $this->db->getQuery(true);

		$query->select('stpr.label')
			->from($this->db->quoteName('#__emundus_setup_profiles', 'stpr'))
			->where($this->db->quoteName('stpr.id') . ' = ' . $profile_id);
		try {
			$this->db->setQuery($query);

			return $this->db->loadObject();
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/form | Error at getting name of the form ' . $profile_id . ' : ' . preg_replace("/[\r\n]/", " ", $query . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

			return false;
		}
	}

	public function getFilesByProfileId($profile_id)
	{

		$query = $this->db->getQuery(true);

		$user = JFactory::getUser();

		$files = 0;

		$query->select('id')
			->from($this->db->quoteName('#__emundus_setup_campaigns'))
			->where($this->db->quoteName('profile_id') . ' = ' . $profile_id);
		try {
			$this->db->setQuery($query);
			$campaigns = $this->db->loadObjectList();

			foreach ($campaigns as $campaign) {
				$query->clear()
					->select('COUNT(*)')
					->from($this->db->quoteName('#__emundus_campaign_candidature'))
					->where($this->db->quoteName('campaign_id') . ' = ' . $campaign->id)
					->andWhere($this->db->quoteName('published') . ' != -1')
					->andWhere($this->db->quoteName('user_id') . ' != ' . $this->db->quote($user->id));

				$this->db->setQuery($query);
				$files += $this->db->loadResult();
			}

			return $files;
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/form | Error at getting files by form ' . $profile_id . ' : ' . preg_replace("/[\r\n]/", " ", $query . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

			return false;
		}
	}

	public function getAssociatedCampaign($profile_id)
	{

		$query = $this->db->getQuery(true);

		$query->select(['id as id', 'label as label'])
			->from($this->db->quoteName('#__emundus_setup_campaigns'))
			->where($this->db->quoteName('profile_id') . ' = ' . $this->db->quote($profile_id));

		try {
			$this->db->setQuery($query);

			return $this->db->loadObjectList();
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/form | Error at getting campaigns link to the form ' . $profile_id . ' : ' . preg_replace("/[\r\n]/", " ", $query . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

			return false;
		}
	}

	public function getAssociatedProgram($form_id)
	{

		$query = $this->db->getQuery(true);

		$query->select(['group_id as id'])
			->from($this->db->quoteName('#__fabrik_formgroup'))
			->where($this->db->quoteName('form_id') . ' = ' . $this->db->quote($form_id));

		try {
			$this->db->setQuery($query);
			$group_id = $this->db->loadRow();
			//var_dump($group_id);


			$query->clear()
				->select('*')
				->from($this->db->quoteName('#__emundus_setup_programmes'))
				->where($this->db->quoteName('fabrik_group_id') . ' = ' . $this->db->quote($group_id[0]));

			$this->db->setQuery($query);
			$programme = $this->db->loadObject();

			//var_dump($programme);
			return $programme;

		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/form | Error at getting eval form program link to the form ' . $form_id . ' : ' . preg_replace("/[\r\n]/", " ", $query . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

			return false;
		}
	}

	function affectCampaignsToForm($prid, $campaigns)
	{
		foreach ($campaigns as $campaign) {
			
			$query = $this->db->getQuery(true);

			$query->select('year')
				->from($this->db->quoteName('#__emundus_setup_campaigns'))
				->where($this->db->quoteName('id') . ' = ' . $this->db->quote($campaign));
			$this->db->setQuery($query);
			$schoolyear = $this->db->loadResult();

			$query->clear()
				->update($this->db->quoteName('#__emundus_setup_campaigns'))
				->set($this->db->quoteName('profile_id') . ' = ' . $this->db->quote($prid))
				->where($this->db->quoteName('id') . ' = ' . $this->db->quote($campaign));

			try {
				$this->db->setQuery($query);
				$this->db->execute();

				$query->clear()
					->update($this->db->quoteName('#__emundus_setup_teaching_unity'))
					->set($this->db->quoteName('profile_id') . ' = ' . $this->db->quote($prid))
					->where($this->db->quoteName('schoolyear') . ' = ' . $this->db->quote($schoolyear));

				$this->db->setQuery($query);
				$this->db->execute();

			}
			catch (Exception $e) {
				JLog::add('component/com_emundus/models/form | Error when affect campaigns to the form ' . $prid . ' : ' . preg_replace("/[\r\n]/", " ", $query . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

				return false;
			}
		}

		return true;
	}

	function getDocumentsByProfile($prid)
	{
		$attachments_by_profile = [];

		if (!empty($prid)) {
			
			$query = $this->db->getQuery(true);

			$query->select('sa.id as docid,sa.value as label,sap.*,sa.allowed_types')
				->from($this->db->quoteName('#__emundus_setup_attachment_profiles', 'sap'))
				->leftJoin($this->db->quoteName('#__emundus_setup_attachments', 'sa') . ' ON ' . $this->db->quoteName('sa.id') . ' = ' . $this->db->quoteName('sap.attachment_id'))
				->where($this->db->quoteName('sap.profile_id') . ' = ' . $this->db->quote($prid))
				->order('sap.mandatory DESC, sap.ordering, sa.value ASC');

			try {
				$this->db->setQuery($query);
				$attachments_by_profile = $this->db->loadObjectList();
			}
			catch (Exception $e) {
				JLog::add('component/com_emundus/models/form | Error cannot get documents by profile_id : ' . $prid . ' : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');
			}
		}

		return $attachments_by_profile;
	}

	function reorderDocuments($documents)
	{
		
		$query = $this->db->getQuery(true);

		$results = array();

		try {
			foreach ($documents as $document) {

				$query->update($this->db->quoteName('#__emundus_setup_attachment_profiles'))
					->set($this->db->quoteName('ordering') . ' = ' . (int) $document['ordering'])
					->where($this->db->quoteName('id') . ' = ' . (int) $document['id']);
				$this->db->setQuery($query);

				$results[] = $this->db->execute();
				$query->clear();
			}

			return $results;

		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/form | Error cannot reorder documents : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

			return false;
		}
	}

	function removeDocumentFromProfile($did)
	{
		
		$query = $this->db->getQuery(true);

		try {
			$query->delete($this->db->quoteName('#__emundus_setup_attachment_profiles'))
				->where($this->db->quoteName('id') . ' = ' . (int) $did);
			$this->db->setQuery($query);

			return $this->db->execute();
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/form | Error cannot remove document : ' . $did . ' with query : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

			return false;
		}
	}

	function deleteModelDocument($did)
	{
		
		$query = $this->db->getQuery(true);

		try {
			$query->select('count(id)')
				->from($this->db->quoteName('#__emundus_setup_attachment_profiles'))
				->where($this->db->quoteName('attachment_id') . ' = ' . $this->db->quote($did));
			$this->db->setQuery($query);
			$attachment_used = $this->db->loadResult();

			if ($attachment_used == 0) {
				$query->clear()
					->delete($this->db->quoteName('#__emundus_setup_attachments'))
					->where($this->db->quoteName('id') . ' = ' . (int) $did);
				$this->db->setQuery($query);

				return $this->db->execute();
			}
			else {
				return false;
			}
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/form | Error cannot delete document template : ' . $did . ' with query : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

			return false;
		}
	}

	function getDatabaseJoinOptions($table, $column, $value, $concat_value = null, $where = null)
	{

		$query = $this->db->getQuery(true);

		$current_shortlang = explode('-', JFactory::getLanguage()->getTag())[0];

		try {
			$value_select = $value . ' as value';
			if (!empty($concat_value)) {
				$concat_value = str_replace('{thistable}', $table, $concat_value);
				$concat_value = str_replace('{shortlang}', $current_shortlang, $concat_value);

				$value_select = 'CONCAT(' . $concat_value . ') as value';
			}
			$query->select(array($this->db->quoteName($column, 'primary_key'), $value_select))
				->from($this->db->quoteName($table));
			if (!empty($where)) {
				$query->where(str_replace('WHERE', '', $where));
			}
			$this->db->setQuery($query);

			return $this->db->loadObjectList();
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/form | Error at getDatabaseJoinOptions : ' . preg_replace("/[\r\n]/", " ", $e->getMessage()), JLog::ERROR, 'com_emundus');

			return false;
		}
	}

	public function checkIfDocCanBeRemovedFromCampaign($document_id, $profile_id): array
	{
		$data = [
			'can_be_deleted' => false,
			'reason'         => 'No response from sql'
		];


		$query = $this->db->getQuery(true);

		$query->select('COUNT(jeu.id)')
			->from('#__emundus_uploads AS jeu')
			->leftJoin('#__emundus_setup_campaigns AS jesc ON jesc.id = jeu.campaign_id')
			->where('jesc.profile_id = ' . $profile_id)
			->andWhere('jeu.attachment_id = ' . $document_id);

		$this->db->setQuery($query);

		try {
			$nb_uploads = $this->db->loadResult();
		}
		catch (Exception $e) {
			JLog::add('Error trying to know if i can remove document from profile ' . $e->getMessage(), JLog::ERROR, 'com_emundus');
		}

		if ($nb_uploads < 1) {
			$data['can_be_deleted'] = true;
			$data['reason']         = 'No document found for this attachment_id and campaign_id';
		}
		else {
			$data['reason'] = $nb_uploads;
			$data['sql']    = $query->__toString();
		}

		return $data;
	}
}
