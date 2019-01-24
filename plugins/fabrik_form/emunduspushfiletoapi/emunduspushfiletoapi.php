<?php
/**
 * @version 2: emunduspushfiletoapi 2019-01-22 Hugo Moracchini
 * @package Fabrik
 * @copyright Copyright (C) 2019 emundus.fr. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Sends a JSON request to an external service.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-form.php';

/**
 * Create a Joomla user from the forms data
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.juseremundus
 * @since       3.0
 */

class PlgFabrik_FormEmunduspushfiletoapi extends plgFabrik_Form {
	/**
	 * Status field
	 *
	 * @var  string
	 */
	protected $URLfield = '';

	/**
	 * Get an element name
	 *
	 * @param   string  $pname  Params property name to look up
	 * @param   bool    $short  Short (true) or full (false) element name, default false/full
	 *
	 * @return	string	element full name
	 */
	public function getFieldName($pname, $short = false) {
		$params = $this->getParams();

		if ($params->get($pname) == '') {
			return '';
		}

		$elementModel = FabrikWorker::getPluginManager()->getElementPlugin($params->get($pname));

		return $short ? $elementModel->getElement()->name : $elementModel->getFullName();
	}

	/**
	 * Get the fields value regardless of whether its in joined data or no
	 *
	 * @param   string  $pname    Params property name to get the value for
	 * @param   array   $data     Posted form data
	 * @param   mixed   $default  Default value
	 *
	 * @return  mixed  value
	 */
	public function getParam($pname, $default = '') {
		$params = $this->getParams();

		if ($params->get($pname) == '') {
			return $default;
		}

		return $params->get($pname);
	}

	/**
	 * Main script.
	 *
	 * @return  bool
	 * @throws Exception
	 */
	public function onAfterSave() {

		jimport('joomla.utilities.utility');
		jimport('joomla.log.log');
		JLog::addLogger(['text_file' => 'com_emundus.emunduspushfiletoapi.php'], JLog::ALL, ['com_emundus']);

		$jinput = JFactory::getApplication()->input;
		$fnum = $jinput->get->get('rowid');

		$api_url = $this->getParam('api_url');
		$api_route = $this->getParam('api_route');
		$api_token = $this->getParam('api_token');

		if (empty($api_url) || empty($api_route) || empty($api_token)) {
			return false;
		}

		$http = new JHttp();

		$data = [];
		if ($this->getParam('use_api', false) == 'true') {

			$em_api_url = $this->getParam('em_api_url', 'localhost:3000');
			$em_api_route = $this->getParam('em_api_route', '/forms/fnum/').$fnum;
			$em_api_token = $this->getParam('em_api_token');

			if (empty($em_api_token)) {
				return false;
			}

			$data = $http->get($em_api_url.$em_api_route, ['accept' => 'application/json', 'x-access-token' => $em_api_token]);
			if ($data->code == 200) {

				$data = json_decode($data->body);

				if (!empty($data)) {

					if ($data->status != 200) {
						JLog::add('ERROR FROM eMundus API: CODE ('.$data->status.') - '.$data->message, JLog::ERROR, 'com_emundus');
						return false;
					}

					if ($data->results == 1) {
						$data = $data->rows[0];
					} else {
						$data = $data->rows;
					}
				}
			}
		} else {

			$db = JFactory::getDbo();
			
			require_once (JPATH_ROOT.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
			require_once (JPATH_ROOT.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'profile.php');

			$m_files = new EmundusModelFiles();
			$m_profile = new EmundusModelProfile();

			$fnumInfos = $m_profile->getFnumDetails($fnum);
			$admissionForm = $m_files->getAdmissionFormidByFnum($fnum);

			// Get all forms for the file.
			$pid = (isset($fnumInfos['profile_id_form']) && !empty($fnumInfos['profile_id_form']))?$fnumInfos['profile_id_form']:$fnumInfos['profile_id'];

			$user   = JFactory::getUser();
			$levels = JAccess::getAuthorisedViewLevels($user->id);

			$query = 'SELECT fbtables.id AS table_id, fbtables.form_id, fbforms.label, fbtables.db_table_name
				FROM #__menu AS menu
				INNER JOIN #__emundus_setup_profiles AS profile ON profile.menutype = menu.menutype AND profile.id = '.$pid.'
				INNER JOIN #__fabrik_forms AS fbforms ON fbforms.id = SUBSTRING_INDEX(SUBSTRING(menu.link, LOCATE("formid=",menu.link)+7, 3), "&", 1)
				LEFT JOIN #__fabrik_lists AS fbtables ON fbtables.form_id = fbforms.id
				WHERE (menu.published = 0 OR menu.published = 1) AND menu.parent_id !=1 AND menu.access IN ('.implode(',', $levels).')';
			if (!empty($formids) && $formids[0] != "") {
				$query .= ' AND fbtables.form_id IN('.implode(',',$formids).')';
			}
			$query .= ' ORDER BY menu.lft';

			try {
				$db->setQuery($query);
				$tableuser = $db->loadObjectList();
			} catch(Exception $e) {
				throw new $e->getMessage();
			}
			
			$query = $db->getQuery(true);
			$query->select([$db->quoteName('fbtables.id', 'table_id'), $db->quoteName('fbtables.form_id'), $db->quoteName('fbforms.label'), $db->quoteName('fbtables.db_table_name')])
				->from($db->quoteName('#__fabrik_lists', 'fbtables'))
				->leftJoin($db->quoteName('#__fabrik_forms', 'fbforms').' ON '.$db->quoteName('fbforms.id').' = '.$db->quoteName('fbtables.form_id'))
				->where($db->quoteName('fbtables.id').' = '.$admissionForm);

			try {
				$db->setQuery($query);
				$tableuser[] = $db->loadObject();
			} catch(Exception $e) {
				throw new $e->getMessage();
			}
			
			
			foreach ($tableuser as $key => $itemt) {
				
				$query = 'SELECT ff.id, ff.group_id, fg.id, fg.label, INSTR(fg.params,"\"repeat_group_button\":\"1\"") as repeated, INSTR(fg.params,"\"repeat_group_button\":1") as repeated_1
                            FROM #__fabrik_formgroup ff, #__fabrik_groups fg
                            WHERE ff.group_id = fg.id AND
                                  ff.form_id = "'.$itemt->form_id.'"
                            ORDER BY ff.ordering';
				$db->setQuery($query);
				$groups = $db->loadObjectList();

				foreach ($groups as $group) {
					
					$query = 'SELECT fe.id, fe.name, fe.label, fe.plugin, fe.params
                                FROM #__fabrik_elements fe
                                WHERE fe.published=1 AND
                                      fe.hidden=0 AND
                                      fe.group_id = "'.$group->group_id.'"
                                ORDER BY fe.ordering';
					$db->setQuery($query);
					$elements = $db->loadObjectList();

					if (count($elements) > 0) {

						if ($group->group_id == 14) {

							foreach ($elements as &$element) {
								if (!empty($element->label) && $element->label != ' ') {

									if ($element->plugin == 'date' && $element->content > 0) {
										$date_params = json_decode($element->params);
										$elt = date($date_params->date_form_format, strtotime($element->content));
									}

									elseif ($element->plugin == 'birthday' && $element->content > 0) {
										$format = 'Y-n-j';
										$d = DateTime::createFromFormat($format, $element->content);
										if ($d && $d->format($format) == $element->content) {
											$elt = JHtml::_('date', $element->content, JText::_('DATE_FORMAT_LC'));
										} else {
											$elt = $element->content;
										}
									}

									elseif ($element->plugin == 'checkbox') {
										$elt = implode(", ", json_decode (@$element->content));
									}

									else {
										$elt = $element->content;
									}

									$data[$itemt->db_table_name.'___'.$element->name] = $elt;
								}
							}
							
						} elseif ($group->repeated > 0 || $group->repeated_1 > 0){

							$t_elt = array();
							foreach ($elements as &$element) {
								$t_elt[] = $element->name;
							}
							unset($element);
							$query = 'SELECT table_join FROM #__fabrik_joins WHERE list_id='.$itemt->table_id.' AND group_id='.$group->group_id.' AND table_join_key like "parent_id"';
							$this->_db->setQuery($query);
							$table = $db->loadResult();

							if ($group->group_id == 174) {
								$query = 'SELECT `'.implode("`,`", $t_elt).'`, id FROM '.$table.'
                                        WHERE parent_id=(SELECT id FROM '.$itemt->db_table_name.' WHERE user='.$fnumInfos['applicant_id'].' AND fnum like '.$db->Quote($fnum).') OR applicant_id='.$fnumInfos['applicant_id'];
							} else {
								$query = 'SELECT `'.implode("`,`", $t_elt).'`, id FROM '.$table.'
                                    WHERE parent_id=(SELECT id FROM '.$itemt->db_table_name.' WHERE fnum like '.$this->_db->Quote($fnum).')';
							}

							$db->setQuery($query);

							try {
								$repeated_elements = $db->loadObjectList();
							} catch (Exception $e) {
								JLog::Add('ERROR getting repeated elements in model/application at query: '.$query, JLog::ERROR, 'com_emundus');
							}

							unset($t_elt);
							
							if (count($repeated_elements) > 0) {
								foreach ($repeated_elements as $r_element) {
									$j = 0;
									foreach ($r_element as $key => $r_elt) {

										if (!empty($elements[$j])) {
											$params = json_decode($elements[$j]->params);
										}

										if ($key != 'id' && $key != 'parent_id' && isset($elements[$j])) {

											if ($elements[$j]->plugin == 'date') {
												$elt = date($params->date_form_format, strtotime($r_elt));
											}

											elseif ($elements[$j]->plugin == 'birthday' && $r_elt>0) {
												$format = 'Y-n-j';
												$d = DateTime::createFromFormat($format, $r_elt);
												if ($d && $d->format($format) == $r_elt) {
													$elt = JHtml::_('date', $r_elt, JText::_('DATE_FORMAT_LC'));
												} else {
													$elt = $r_elt;
												}
											}

											elseif ($elements[$j]->plugin == 'checkbox') {
												$elt = implode(", ", json_decode (@$r_elt));
											}

											else {
												$elt = $r_elt;
											}
											$data[$itemt->db_table_name.'___'.$elements[$j]->name] = $elt;
										}
										$j++;
									}
								}
							}
						} else {
							foreach ($elements as &$element) {
								if (!empty($element->label) && $element->label != ' ') {
									$query = 'SELECT `id`, `'.$element->name .'` FROM `'.$itemt->db_table_name.'` WHERE user='.$fnumInfos['applicant_id'].' AND fnum like '.$this->_db->Quote($fnum);
									$db->setQuery($query);
									$res = $db->loadRow();

									$element->content = @$res[1];
									$element->content_id = @$res[0];

									if ($element->plugin == 'date' && $element->content > 0) {
										$date_params = json_decode($element->params);
										$elt = date($date_params->date_form_format, strtotime($element->content));
									}
									elseif ($element->plugin == 'birthday' && $element->content > 0) {
										$format = 'Y-n-j';
										$d = DateTime::createFromFormat($format, $element->content);
										if ($d && $d->format($format) == $element->content) {
											$elt = JHtml::_('date', $element->content, JText::_('DATE_FORMAT_LC'));
										} else {
											$elt = $element->content;
										}
									}
									elseif ($element->plugin == 'checkbox') {
										$elt = implode(", ", json_decode (@$element->content));
									}
									else {
										$elt = $element->content;
									}
									unset($params);
									$data[$itemt->db_table_name.'___'.$element->name] = $elt;
								}
							}
						}
					}
				}
			}
		}

		if (!empty($data)) {

			// TODO: Finish post.
			$response = $http->post($api_url.$api_route, json_encode($data));

		}

		return true;
	}

	/**
	 * Raise an error - depends on whether you are in admin or not as to what to do
	 *
	 * @param   array   &$err   Form models error array
	 * @param   string   $field Name
	 * @param   string   $msg   Message
	 *
	 * @return  void
	 * @throws Exception
	 */

	protected function raiseError(&$err, $field, $msg) {
		$app = JFactory::getApplication();

		if ($app->isAdmin()) {
			$app->enqueueMessage($msg, 'notice');
		} else {
			$err[$field][0][] = $msg;
		}
	}
}
