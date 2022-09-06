<?php
/**
 * @version 2: emundusredirect 2018-04-25 Hugo Moracchini
 * @package Fabrik
 * @copyright Copyright (C) 2018 emundus.fr. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Redirection et chainage des formulaires suivant le profile de l'utilisateur
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

class PlgFabrik_FormEmundusRedirect extends plgFabrik_Form
{
	/**
	 * Status field
	 *
	 * @var  string
	 */
	protected $statusfield = '';

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

    public function onBeforeStore() {
        $excludeElements = array('id','time_date','user','fnum');
        $timeElements = array('birthday','birthday_remove_slashes','date','jdate','time','timer','timestamp','years');
        $checkElements = array('radiobutton', 'dropdown', 'yesno');
        $multipleElements = array('checkbox');

        /* get process data */
        $formModel = $this->getModel();
        $formData = $formModel->formData;

        $keys = array_keys($formData);

        /* grab all tables name (repeat and non-repeat table) */
        //$table = array_values(array_unique(array_map(function($data) {return explode('___', $data)[0];},$keys)));

        $parentTable = "";

        /* */
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        /* get fnum */
        $user = JFactory::getSession()->get('emundusUser');
        //$fnum = $user->fnum;

        /* get form id from $jinput */
        $jinput = JFactory::getApplication()->input;
        $formid = $jinput->get('formid');

        $elements = array();

        /* old data */
        $oldData = array();

        foreach($formData as $key=>$value) {
            if(strpos($key, '___fnum')) {
                $fnum = $value;
                $parentTable = explode("___",$key)[0];
            }

            $elementName = explode('___', $key)[1];
            $elementName === null or strpos($elementName,'_raw') or strpos($elementName,'-') or in_array($elementName,$excludeElements) ? null : $elements[] = $key;
        }

        foreach($elements as $element) {
            if(!strpos($element,'repeat')) {
                $query
                    ->clear()
                    ->select($db->quoteName(explode('___',$element)[0] . '.' . explode('___',$element)[1]))
                    ->from($db->quoteName($parentTable));
                $query->where($db->quoteName($parentTable . '.fnum') . ' = ' . $db->quote($fnum));
            } else {
                /* repeat group */
                $query
                    ->clear()
                    ->select(explode('___',$element)[0] . '.' . $db->quoteName(explode('___',$element)[1]))
                    ->from($db->quoteName(explode('___',$element)[0]))
                    ->leftJoin($db->quoteName($parentTable) . ' ON ' . $db->quoteName($parentTable . '.id') . ' = ' . $db->quoteName(explode('___',$element)[0] . '.parent_id'));
                $query->where($db->quoteName($parentTable . '.fnum') . ' = ' . $db->quote($fnum));
            }
            $db->setQuery($query);
            $res = $db->loadColumn();       /* here */
            $oldData[explode('___',$element)[0] . '___' . explode('___',$element)[1]] = $res;
        }

        /* find all same keys of 2 arrays (oldData, processData) */
        $intersectKey = array_keys(array_intersect_key($oldData,$formData));

        $results = array();

        /* init a new session */
        $session = JFactory::getSession();

        foreach($intersectKey as $iKey) {
            $diffs = $this->dataFormCompare($oldData,$formData,$iKey);

            if(!empty($diffs)) {
                /* get element data (getObject) */

                $query->clear()
                    ->select("distinct jfe.id as element_id, jfe.name as element_name, jfe.label as element_label, jfe.params as element_params, jfg.id as group_id, jfg.name as group_name, jfg.label as group_label, jff1.id as form_id, jff1.label as form_label, jfe.plugin, instr(jfg.params, '\"repeat_group_button\":\"1\"') as group_repeat")
                    ->from($db->quoteName('#__fabrik_elements', 'jfe'))
                    ->leftJoin($db->quoteName('#__fabrik_groups', 'jfg') . ' ON ' . $db->quoteName('jfe.group_id') . ' = ' . $db->quoteName('jfg.id'))
                    ->leftJoin($db->quoteName('#__fabrik_formgroup', 'jff') . ' ON ' . $db->quoteName('jff.group_id') . ' = ' . $db->quoteName('jfg.id'))
                    ->leftJoin($db->quoteName('#__fabrik_forms', 'jff1') . ' ON ' . $db->quoteName('jff.form_id') . ' = ' . $db->quoteName('jff1.id'))
                    ->where($db->quoteName('jff1.id') . ' = ' . $formid)
                    ->andWhere($db->quoteName('jfe.hidden') . ' != 1')
                    ->andWhere($db->quoteName('jfe.published') . ' = 1')
                    ->andWhere($db->quoteName('jfe.name') . ' LIKE ' . $db->quote(explode('___', $iKey)[1]));

                $db->setQuery($query);
                $element = (array)$db->loadObject();

                if(empty($element)) { continue; }

                if($element['group_repeat'] == 0) {

                    // flat old data and new data
                    $diffs['old_data'] = reset($diffs['old_data']);
                    $diffs['new_data'] = reset($diffs['new_data']);

                    if ($element['plugin'] !== 'databasejoin' and $element['plugin'] != 'cascadingdropdown') {
                        /* special case : time, date plugins --> using the method strtotime to compare */
                        if (in_array($element['plugin'], $timeElements)) {
                            /* recheck the diff */
                            if (strtotime($diffs['old_data']) === strtotime($diffs['new_data'])) {
                                continue;
                            }
                        }

                        /* check or select plugins */
                        if (in_array($element['plugin'], $checkElements) or in_array($element['plugin'],$multipleElements)) {
                            /* get subValues and subLabels */
                            $optSubValues = json_decode($element['element_params'])->sub_options->sub_values;
                            $optSubLabels = json_decode($element['element_params'])->sub_options->sub_labels;
                            /* *********** */

                            $oldsValues = $newsValues = array();
                            $oldsLabels = $newsLabels = array();

                            if (in_array($element['plugin'], $checkElements)) {
                                /* find the index of subValues from $diffs['old_data'] and $diffs['new_data'] */
                                $oldArrayIndex = array_search($diffs['old_data'], $optSubValues);
                                $newArrayIndex = array_search($diffs['new_data'], $optSubValues);

                                /* get oldValues and newValues */
                                $oldsValues = [$diffs['old_data']];
                                $newsValues = [$diffs['new_data']];

                                /* using ternary operator */
                                $oldsLabels = $oldArrayIndex !== false ? JText::_($optSubLabels[$oldArrayIndex]) : null;
                                $newsLabels = $newArrayIndex !== false ? JText::_($optSubLabels[$newArrayIndex]) : null;
                            }
                            else if (in_array($element['plugin'], $multipleElements)) {
                                /* replace the substring "[" and "]" by empty string */
                                $olds = str_replace('[', '', $diffs['old_data']);
                                $olds = str_replace(']', '', $olds);
                                $olds = str_replace(',', ';', $olds);
                                $olds = str_replace('"', '', $olds);

                                /* convert $diffs['old_data'] to (string) by explode (";") */
                                $olds = explode(';', $olds);

                                /* null condition */
                                $olds = empty(trim($olds)) !== false ? $olds : array("");

                                $olds = is_array($olds) === false ? array($olds) : $olds;
                                $news = is_array($diffs['new_data']) === false ? array($diffs['new_data']) : $diffs['new_data'];

                                foreach ($olds as $_old) {
                                    $_oIndex = array_search($_old, $optSubValues);
                                    $_oLabels = $_oIndex !== false ? JText::_($optSubLabels[$_oIndex]) : null;
                                    $oldsLabels[] = $_oLabels;
                                    $oldsValues[] = $_old;
                                }

                                foreach ($news as $_new) {
                                    $_nIndex = array_search($_new, $optSubValues);
                                    $_nLabels = $_nIndex !== false ? JText::_($optSubLabels[$_nIndex]) : null;
                                    $newsLabels[] = $_nLabels;
                                    $newsValues[] = $_new;
                                }
                            }

                            if ($oldsValues === $newsValues) {
                                $oldsLabels = $newsLabels = "";
                            } else {
                                $oldsLabels = count($oldsLabels) > 1 ? (empty(trim(implode("", $oldsLabels))) === true ? "" : implode('<#>', $oldsLabels)) : (is_array($oldsLabels) === true ? $oldsLabels[0] : $oldsLabels);
                                $newsLabels = count($newsLabels) > 1 ? (empty(trim(implode("", $newsLabels))) === true ? "" : implode('<#>', $newsLabels)) : (is_array($newsLabels) === true ? $newsLabels[0] : $newsLabels);
                            }
                            $diffs['old_data'] = $oldsLabels;
                            $diffs['new_data'] = $newsLabels;
                        }
                    }
                    else {
                        //TODO : HANDLE THE CONCAT LABEL OF DATABASE JOIN with {shortlang}, {thistable}
                        /* get label of this element by $diffs['old_data'] and $diffs['new_data'] */
                        $query->clear()
                            ->select('*')
                            ->from($db->quoteName('#__fabrik_joins','jfj'))
                            ->where($db->quoteName('jfj.element_id') . ' = ' . $db->quote($element['element_id']));
                        $db->setQuery($query);
                        $joinResults = $db->loadObject();

                        $joinlabel = json_decode($joinResults->params,true)['join-label'];
                        $joinKey = $joinResults->table_join_key;
                        $joinFrom = $joinResults->table_join;

                        $query->clear()->select($db->quoteName($joinlabel))->from($db->quoteName($joinFrom))->where($db->quoteName($joinKey) . ' = ' . $db->quote($diffs['old_data']));
                        $db->setQuery($query);
                        $diffs['old_data'] = $db->loadResult();

                        $query->clear()->select($db->quoteName($joinlabel))->from($db->quoteName($joinFrom))->where($db->quoteName($joinKey) . ' = ' . $db->quote($diffs['new_data']));

                        $db->setQuery($query);
                        $diffs['new_data'] = $db->loadResult();
                    }
                    $results[$iKey] = array_merge($element,$diffs);
                }
                else {
                    /* group repeat is always an array nD with n >= 1 */
                    if($element['plugin'] != 'databasejoin' and $element['plugin'] != 'cascadingdropdown') {

                        //TODO : TIME, DATE PLUGIN WITH REPEAT GROUP

                        // check or select plugins //
                        if (in_array($element['plugin'], $checkElements) or in_array($element['plugin'],$multipleElements)) {
                            /* get subValues and subLabels */
                            $optSubValues = json_decode($element['element_params'])->sub_options->sub_values;
                            $optSubLabels = json_decode($element['element_params'])->sub_options->sub_labels;
                            /* *********** */

                            $oldsValues = $newsValues = array();
                            $oldsLabels = $newsLabels = array();

                            if (in_array($element['plugin'], $checkElements)) {
                                $olds = is_array($diffs['old_data']) === false ? array($diffs['old_data']) : $diffs['old_data'];
                                $news = is_array($diffs['new_data']) === false ? array($diffs['new_data']) : $diffs['new_data'];

                                foreach ($olds as $_old) {
                                    $_oIndex = array_search($_old, $optSubValues);
                                    $_oLabels = $_oIndex !== false ? JText::_($optSubLabels[$_oIndex]) : null;
                                    $oldsLabels[] = $_oLabels;
                                    $oldsValues[] = $_old;
                                }

                                foreach ($news as $_new) {
                                    $_nIndex = array_search($_new, $optSubValues);
                                    $_nLabels = $_nIndex !== false ? JText::_($optSubLabels[$_nIndex]) : null;
                                    $newsLabels[] = $_nLabels;
                                    $newsValues[] = $_new;
                                }

                            } else if (in_array($element['plugin'], $multipleElements)) {
                                $diffs['old_data'] = is_array($diffs['old_data']) === true ? $diffs['old_data'] : [$diffs['old_data']];

                                $olds = array_map(
                                    function($x) {
                                        $x =  str_replace('[','',$x);
                                        $x =  str_replace(']','',$x);
                                        $x =  str_replace(',',';',$x);
                                        return str_replace('"','',$x);
                                    }, array_values($diffs['old_data']));

                                ////
                                $olds = array_map(function($x) { return empty(trim(explode(";", $x))) !== false ? explode(";", $x) : array(""); },array_values($olds));
                                $olds = call_user_func_array('array_merge',$olds);

                                $olds = is_array($olds) === false ? array($olds) : $olds;
                                $news = is_array($diffs['new_data']) === false ? array($diffs['new_data']) : $diffs['new_data'];

                                foreach ($olds as $_old) {
                                    $_oIndex = array_search($_old, $optSubValues);
                                    $_oLabels = $_oIndex !== false ? JText::_($optSubLabels[$_oIndex]) : null;
                                    $oldsLabels[] = $_oLabels;
                                    $oldsValues[] = $_old;
                                }

                                foreach ($news as $_new) {
                                    $_nIndex = array_search($_new, $optSubValues);
                                    $_nLabels = $_nIndex !== false ? JText::_($optSubLabels[$_nIndex]) : null;
                                    $newsLabels[] = $_nLabels;
                                    $newsValues[] = $_new;
                                }
                            }

                            if (array_values($oldsValues) === array_values($newsValues)) {
                                $oldsLabels = $newsLabels = "";
                            } else {
                                $oldsLabels = count($oldsLabels) > 1 ? (empty(trim(implode("", $oldsLabels))) === true ? "" : implode('<#>', $oldsLabels)) : (is_array($oldsLabels) === true ? $oldsLabels[0] : $oldsLabels);
                                $newsLabels = count($newsLabels) > 1 ? (empty(trim(implode("", $newsLabels))) === true ? "" : implode('<#>', $newsLabels)) : (is_array($newsLabels) === true ? $newsLabels[0] : $newsLabels);
                            }
                            $diffs['old_data'] = $oldsLabels;
                            $diffs['new_data'] = $newsLabels;


                        } else {
                            $diffs['old_data'] = count($diffs['old_data']) > 1 ? (empty(trim(implode("", $diffs['old_data']))) === true ? "" : implode('<#>', $diffs['old_data'])) : (is_array($diffs['old_data']) === true ? $diffs['old_data'][0] : $diffs['old_data']);
                            $diffs['new_data'] = count($diffs['new_data']) > 1 ? (empty(trim(implode("", $diffs['new_data']))) === true ? "" : implode('<#>', $diffs['new_data'])) : (is_array($diffs['new_data']) === true ? $diffs['new_data'][0] : $diffs['new_data']);
                        }
                    } else {
                        $query->clear()
                            ->select('*')
                            ->from($db->quoteName('#__fabrik_joins','jfj'))
                            ->where($db->quoteName('jfj.element_id') . ' = ' . $db->quote($element['element_id']));
                        $db->setQuery($query);
                        $joinResults = $db->loadObject();

                        /* get the join results */
                        $joinlabel = json_decode($joinResults->params,true)['join-label'];
                        $joinKey = $joinResults->table_join_key;
                        $joinFrom = $joinResults->table_join;

                        $oldsLabels = $newsLabels = array();

                        foreach ($diffs['old_data'] as $_old) {
                            $query->clear()->select($db->quoteName($joinlabel))->from($db->quoteName($joinFrom))->where($db->quoteName($joinKey) . ' = ' . $db->quote($_old));
                            $db->setQuery($query);
                            $oldsLabels[] = $db->loadResult();
                        }

                        if (empty(trim(implode("", $diffs['new_data'])))) {
                            $diffs['new_data'] = "";
                        } else {
                            foreach ($diffs['new_data'] as $_new) {
                                $query->clear()->select($db->quoteName($joinlabel))->from($db->quoteName($joinFrom))->where($db->quoteName($joinKey) . ' = ' . $db->quote($_new));
                                $db->setQuery($query);
                                $newsLabels[] = $db->loadResult();
                            }
                        }

                        $diffs['old_data'] = count($oldsLabels) > 1 ? (empty(trim(implode("", $oldsLabels))) === true ? "" : implode('<#>', $oldsLabels)) : (is_array($oldsLabels) === true ? $oldsLabels[0] : $oldsLabels);
                        $diffs['new_data'] = count($newsLabels) > 1 ? (empty(trim(implode("", $newsLabels))) === true ? "" : implode('<#>', $newsLabels)) : (is_array($newsLabels) === true ? $newsLabels[0] : $newsLabels);
                    }
                }

                $results[$iKey] = array_merge($element,$diffs);
            }

        }
        $logger = array();

        if(!empty($results)) {
            foreach ($results as $result) {
                $logsStd = new stdClass();

                if(($result['old_data'] === null or empty(trim($result['old_data']))) and ($result['new_data'] === null or empty(trim($result['new_data'])))) {
                    continue;
                } else {
                    $logsStd->element = '<u>' . JText::_($result['element_label']) . '</u>';
                    $logsStd->old = $result['old_data'];
                    $logsStd->new = $result['new_data'];
                    $logger[] = $logsStd;
                }
            }
        }

        # parse to JSON (json encode)
        $logParams = array('updated' => $logger);

        /* REGISTER LOGS TO DATABASE, DO NOT NEED USING THE SESSION IN THIS CASE */
        require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
        $mFile = new EmundusModelFiles();
        $applicant_id = ($mFile->getFnumInfos($fnum))['applicant_id'];

        if(!empty($logParams['updated']) and $logParams['updated'] !== null) {
            EmundusModelLogs::log($user->id, $applicant_id, $fnum, 1, 'u', 'COM_EMUNDUS_ACCESS_FILE_UPDATE', json_encode($logParams,JSON_UNESCAPED_UNICODE));
        }

        $session->set( 'updateFormData___' .$formid, $results);
    }

    public function dataFormCompare($old,$new,$key) : array {
        $diffElements = array();

        if(!is_array($new[$key])) { $new[$key] = array($new[$key]); }

        /* handle new data */
        if(is_array(current($new[$key])) === false) {
            $new[$key] = array_values($new[$key]);
        } else {
            if(count($new[$key]) >= 1) {    // the sub array
                $new[$key] = call_user_func_array('array_merge', array_values($new[$key]));
            }
        }

        if(trim(implode("", array_values($old[$key]))) === trim(implode("", array_values($new[$key])))) {
            return array();
        } else {
            if (array_values($old[$key]) !== array_values($new[$key])) {
                if (empty(trim(implode("", $old[$key]))) and empty(trim(implode("", $new[$key])))) {
                    return array();
                } else {
                    $diffElements['key_data'] = $key;
                    $diffElements['old_data'] = $old[$key];
                    $diffElements['new_data'] = $new[$key];
                }
            } else {
                return array();
            }
        }

        return $diffElements;       /* empty or not-empty array */
    }

	/**
	 * Before the record is stored, this plugin will see if it should process
	 * and if so store the form data in the session.
	 *
	 * @return void should the form model continue to save
	 * @throws Exception
	 */
	public function onAfterProcess() {

		/********************************************
		 *
		 * Duplicate data on each applicant file for current campaigns
		 */
		jimport('joomla.log.log');
		JLog::addLogger(['text_file' => 'com_emundus.redirect.php'], JLog::ALL, ['com_emundus']);

		$user = JFactory::getSession()->get('emundusUser');
		$db = JFactory::getDBO();

		include_once(JPATH_SITE.'/components/com_emundus/models/profile.php');
		$m_profile = new EmundusModelProfile();
		$applicant_profiles = $m_profile->getApplicantsProfilesArray();

		$copy_form = (Int)$this->getParam('copy_form', '0');

		// duplication is defined
		if ($copy_form === 1 && isset($user->fnum)) {

			JLog::addLogger(['text_file' => 'com_emundus.duplicate.php'], JLog::ALL, ['duplicate']);

			// Get some form definition
			$data = $this->getProcessData();
			$table = explode('___', key($data));
			$table_name = $table[0];
			$fnums = $user->fnums;
			unset($fnums[$user->fnum]);

			$fabrik_repeat_group = array();

			if (!empty($data['fabrik_repeat_group'])) {
				foreach ($data['fabrik_repeat_group'] as $key => $value) {
					$fabrik_repeat_group[] = $key;
				}
			}

			// only repeated groups
			$fabrik_group_rowids_key = array();

			if (!empty($data['fabrik_group_rowids'])) {

				foreach ($data['fabrik_group_rowids'] as $key => $value) {
					$repeat_table_name = $table_name.'_'.$key.'_repeat';
					$query = 'SELECT id FROM '.$repeat_table_name.' WHERE parent_id='.$data['rowid'];
					$db->setQuery( $query );
					$fabrik_group_rowids_key[$key] = $db->loadColumn();
				}
			}

			// Only if other application files found
			if (!empty($fnums)) {

				$query = 'SELECT * FROM '.$table_name.' WHERE id='.$data['rowid'];

				$db->setQuery($query);
				$parent_data = $db->loadAssoc();
				unset($parent_data['fnum']);
				unset($parent_data['id']);

				// new record
				if (isset($data['usekey_newrecord']) && $data['usekey_newrecord']==1) {

					// Parent table
					$parent_id = array();
					foreach ($fnums as $key => $fnum) {

						$query = 'INSERT INTO `'.$table_name.'` (`'.implode('`,`', array_keys($parent_data)).'`, `fnum`) VALUES ';
						$query .= '('.implode(',', $db->Quote($parent_data)).', '.$db->Quote($key).')';

						$db->setQuery($query);

						try {

							$db->execute();
							$parent_id[] = $db->insertid();

						} catch (Exception $e) {
							$error = JUri::getInstance().' :: USER ID : '.$user->id.' -> '.$e->getMessage();
							JLog::add($error, JLog::ERROR, 'com_emundus');
						}
					}

					// Repeated table
					foreach ($fabrik_group_rowids_key as $key => $rowids) {

						if (count($rowids) > 0) {

							$repeat_table_name = $table_name.'_'.$key.'_repeat';

							$query = 'SELECT * FROM `'.$repeat_table_name.'` WHERE id IN ('.implode(',', $rowids).')';

							try {

								$db->setQuery($query);
								$repeat_data = $db->loadAssocList();

							} catch (Exception $e) {
								$error = JUri::getInstance().' :: USER ID : '.$user->id.' -> '.$e->getMessage();
								JLog::add($error, JLog::ERROR, 'com_emundus');
							}

							if (!empty($repeat_data)) {

								foreach ($parent_id as $parent) {

									$parent_data = array();
									foreach ($repeat_data as $key => $d) {
										unset($d['parent_id']);
										unset($d['id']);
										$columns = '`'.implode('`,`', array_keys($d)).'`';
										$parent_data[] = '('.implode(',', $db->Quote($d)).', '.$parent.')';
									}
									$query = 'INSERT INTO `'.$repeat_table_name.'` ('.$columns.', `parent_id`) VALUES ';
									$query .= implode(',', $parent_data);

									$db->setQuery($query);

									try {

										$db->execute();

									} catch (Exception $e) {
										$error = JUri::getInstance().' :: USER ID : '.$user->id.' -> '.$e->getMessage();
										JLog::add($error, JLog::ERROR, 'com_emundus');
									}
								}
							}
						}
					}

				} else {

					// Parent table
					$updated_fnum = array();

					foreach ($fnums as $fnum => $f) {

						$query = 'UPDATE `'.$table_name.'` SET ';
						$parent_update = array();
						foreach ($parent_data as $key => $value) {
							$parent_update[] = '`'.$key.'`='.$db->Quote($value);
						}
						$query .= implode(',', $parent_update);
						$query .= ' WHERE fnum like '.$db->Quote($fnum);

						$db->setQuery($query);

						try {

							$db->execute();
							$updated_fnum[] = $fnum;

						} catch (Exception $e) {
							$error = JUri::getInstance().' :: USER ID : '.$user->id.' -> '.$e->getMessage();
							JLog::add($error, JLog::ERROR, 'com_emundus');
						}
					}

					if (!empty($updated_fnum)) {
						$query = 'SELECT id FROM `'.$table_name.'` WHERE fnum IN ('.implode(',', $db->Quote($updated_fnum)).')';
						$db->setQuery($query);
						$parent_id = $db->loadColumn();
					}

					// Repeated table
					foreach ($fabrik_group_rowids_key as $key => $rowids) {

						if (!empty($rowids)) {

							$repeat_table_name = $table_name.'_'.$key.'_repeat';

							$query = 'SELECT * FROM `'.$repeat_table_name.'` WHERE id IN ('.implode(',', $rowids).')';

							try {

								$db->setQuery($query);
								$repeat_data = $db->loadAssocList('id');

							} catch (Exception $e) {
								$error = JUri::getInstance().' :: USER ID : '.$user->id.' -> '.$e->getMessage();
								JLog::add($error, JLog::ERROR, 'com_emundus');
							}

							if (!empty($parent_id)) {

								$query = 'DELETE FROM `'.$repeat_table_name.'` WHERE parent_id IN ('.implode(',', $parent_id).')';

								$db->setQuery($query);

								try {
									$db->execute();
								} catch (Exception $e) {
									$error = JUri::getInstance().' :: USER ID : '.$user->id.' -> '.$e->getMessage();
									JLog::add($error, JLog::ERROR, 'com_emundus');
								}

								if (!empty($repeat_data)) {

									foreach ($parent_id as $parent) {

										$parent_data = array();
										foreach ($repeat_data as $key => $d) {
											unset($d['parent_id']);
											unset($d['id']);
											$columns = '`'.implode('`,`', array_keys($d)).'`';
											$parent_data[] = '('.implode(',', $db->Quote($d)).', '.$parent.')';
										}

										$query = 'INSERT INTO `'.$repeat_table_name.'` ('.$columns.', `parent_id`) VALUES ';
										$query .= implode(',', $parent_data);
										$db->setQuery( $query );

										try {
											$db->execute();
										} catch (Exception $e) {
											$error = JUri::getInstance().' :: USER ID : '.$user->id.' -> '.$e->getMessage();
											JLog::add($error, JLog::ERROR, 'com_emundus');
										}
									}
								}
							}
						}
					}
				}
			}
		}

		// If the status setting is set & not at -1 then we need to change the user's status.
		$toStatus = (Int)$this->getParam('emundusredirect_field_status', '-1');
		if (isset($toStatus) && $toStatus != -1 && isset($user->fnum)) {

			$query = $db->getQuery(true);

			// Conditions for which status should be updated.
			// We only want to update the user's status to another value if it's 0 (NOT SENT).
			$conditions = [
				$db->quoteName('fnum').' LIKE '.$user->fnum,
				$db->quoteName('status').' = 0'
			];

			$query->update($db->quoteName('#__emundus_campaign_candidature'))
					->set([$db->quoteName('status').' = '.$toStatus])
					->where($conditions);

			try {

				$db->setQuery($query);
				$db->execute();

			} catch (Exception $e) {
				JLog::add('Error updating file status : '.$e->getMessage(), JLog::ERROR, 'com_emundus');
			}
		}

        require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'application.php');
        $m_application = new EmundusModelApplication();


		/*
		* REDIRECTION ONCE DUPLICATION IS DONE
		*/
		require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'access.php');

		$user = JFactory::getSession()->get('emundusUser');
		$jinput = JFactory::getApplication()->input;
		$formid = $jinput->get('formid');

		if (in_array($user->profile, $applicant_profiles) && EmundusHelperAccess::asApplicantAccessLevel($user->id)) {
			$levels = JAccess::getAuthorisedViewLevels($user->id);

            if(isset($user->fnum)) {
                $m_application->getFormsProgress($user->fnum);
                $m_application->getAttachmentsProgress($user->fnum);
            }

			try {

				$query = 'SELECT CONCAT(link,"&Itemid=",id)
						FROM #__menu
						WHERE published=1 AND menutype = "'.$user->menutype.'" AND access IN ('.implode(',', $levels).')
						AND parent_id != 1
						AND lft = 2+(
								SELECT menu.lft
								FROM `#__menu` AS menu
								WHERE menu.published=1 AND menu.parent_id>1 AND menu.menutype="'.$user->menutype.'"
								AND SUBSTRING_INDEX(SUBSTRING(menu.link, LOCATE("formid=",menu.link)+7, 3), "&", 1)='.$formid.')';

				$db->setQuery($query);
				$link = $db->loadResult();

			} catch (Exception $e) {
				$error = JUri::getInstance().' :: USER ID : '.$user->id.' -> '.$e->getMessage();
				JLog::add($error, JLog::ERROR, 'com_emundus');
			}

			if (empty($link)) {

				try {

					$query = 'SELECT CONCAT(link,"&Itemid=",id)
						FROM #__menu
						WHERE published=1 AND menutype = "'.$user->menutype.'"  AND access IN ('.implode(',', $levels).')
						AND parent_id != 1
						AND lft = 4+(
								SELECT menu.lft
								FROM `#__menu` AS menu
								WHERE menu.published=1 AND menu.parent_id>1 AND menu.menutype="'.$user->menutype.'"
								AND SUBSTRING_INDEX(SUBSTRING(menu.link, LOCATE("formid=",menu.link)+7, 3), "&", 1)='.$formid.')';

					$db->setQuery($query);
					$link = $db->loadResult();

				} catch (Exception $e) {
					$error = JUri::getInstance().' :: USER ID : '.$user->id.' -> '.$e->getMessage();
					JLog::add($error, JLog::ERROR, 'com_emundus');
				}

				if (empty($link)) {
					try {

						$query = 'SELECT CONCAT(link,"&Itemid=",id)
								FROM #__menu
								WHERE published=1 AND menutype = "'.$user->menutype.'" AND type!="separator" AND published=1 AND alias LIKE "checklist%"';

						$db->setQuery($query);
						$link = $db->loadResult();

					} catch (Exception $e) {
						$error = JUri::getInstance().' :: USER ID : '.$user->id.' -> '.$e->getMessage();
						JLog::add($error, JLog::ERROR, 'com_emundus');
					}

					if (empty($link)) {
						try {
							$query = 'SELECT CONCAT(link,"&Itemid=",id) 
							FROM #__menu 
							WHERE published=1 AND menutype = "'.$user->menutype.'" AND type LIKE "component" AND published=1 AND level = 1 ORDER BY id ASC';
							$db->setQuery( $query );
							$link = $db->loadResult();
						} catch (Exception $e) {
							$error = JUri::getInstance().' :: USER ID : '.$user->id.' -> '.$e->getMessage();
							JLog::add($error, JLog::ERROR, 'com_emundus');
						}
					}
				}
			}

            // track the LOGS (1 | u | COM_EMUNDUS_ACCESS_FILE_UPDATE)
            # get the logged user id	$user->id
            # get the fnum				$user->fnum
            # get the applicant id		$user->id
            require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'logs.php');
            $user = JFactory::getSession()->get('emundusUser');			# logged user #

            require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
            $mFile = new EmundusModelFiles();
            $applicant_id = ($mFile->getFnumInfos($user->fnum))['applicant_id'];

            EmundusModelLogs::log($user->id, $applicant_id, $user->fnum, 1, 'u', 'COM_EMUNDUS_ACCESS_FILE_UPDATE', 'COM_EMUNDUS_ACCESS_FILE_UPDATED_BY_APPLICANT');

		} else {

			try {

				$query = 'SELECT db_table_name FROM `#__fabrik_lists` WHERE `form_id` ='.$formid;
				$db->setQuery($query);
				$db_table_name = $db->loadResult();

			} catch (Exception $e) {
				$error = JUri::getInstance().' :: USER ID : '.$user->id.' -> '.$e->getMessage();
				JLog::add($error, JLog::ERROR, 'com_emundus');
			}

			$fnum = $jinput->get($db_table_name.'___fnum');
			$s1 = JRequest::getVar($db_table_name.'___user', null, 'POST');
			$s2 = JRequest::getVar('sid', '', 'GET');
			$student_id = !empty($s2)?$s2:$s1;

			$sid = is_array($student_id)?$student_id[0]:$student_id;

			try {

				$query = 'UPDATE `'.$db_table_name.'` SET `user`='.$sid.' WHERE fnum like '.$db->Quote($fnum);
				$db->setQuery($query);
				$db->execute();

			} catch (Exception $e) {
				$error = JUri::getInstance().' :: USER ID : '.$user->id.' -> '.$e->getMessage();
				JLog::add($error, JLog::ERROR, 'com_emundus');
			}

			$link = JRoute::_('index.php?option=com_fabrik&view=form&formid='.$formid.'&usekey=fnum&rowid='.$fnum.'&tmpl=component');

            # get logged user_id 	$user->id
            # get candidat user_id	$sid
            # get the fnum			$fnum

            // TRACK THE LOGS (1 | u | COM_EMUNDUS_ACCESS_FILE_UPDATE)
            require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'logs.php');
            $user = JFactory::getSession()->get('emundusUser');		# logged user #

            require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
            $mFile = new EmundusModelFiles();
            $applicant_id = ($mFile->getFnumInfos($fnum))['applicant_id'];

            EmundusModelLogs::log($user->id, $applicant_id, $fnum, 1, 'u', 'COM_EMUNDUS_ACCESS_FILE_UPDATE', 'COM_EMUNDUS_ACCESS_FILE_UPDATED_BY_COORDINATOR');

            echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@8"></script>';
            echo '<script src="https://code.jquery.com/jquery-3.3.1.slim.js" integrity="sha256-fNXJFIlca05BIO2Y5zh1xrShK3ME+/lYZ0j+ChxX2DA=" crossorigin="anonymous"></script>';
            die("<script>
              $(document).ready(function () {
                Swal.fire({
                  position: 'top',
                  type: 'success',
                  title: '".JText::_('SAVED')."',
                  showConfirmButton: false,
                  timer: 2000,
                  onClose: () => {
                    window.close();
                  }
                })
              });
            </script>");
		}


		if ($this->getParam('notify_complete_file', 0) == 1) {

			require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'application.php');
			require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'checklist.php');
			$m_application = new EmundusModelApplication;
			$m_checklist = new EmundusModelChecklist();

			$attachments = $m_application->getAttachmentsProgress($user->fnum);
			$forms = $m_application->getFormsProgress($user->fnum);
			$send_file_url = $m_checklist->getConfirmUrl().'&usekey=fnum&rowid='.$user->fnum;

			if ($attachments >= 100 && $forms >= 100) {

				echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@8"></script>';
				echo '<script src="https://code.jquery.com/jquery-3.3.1.slim.js" integrity="sha256-fNXJFIlca05BIO2Y5zh1xrShK3ME+/lYZ0j+ChxX2DA=" crossorigin="anonymous"></script>';
				die("<script>
				    	$(document).ready(() => {
				      		Swal.fire({
					        	position: 'top',
					        	type: 'info',
					        	title: '".JText::_('PLG_FABRIK_FORM_EMUNDUSREDIRECT_FILE_COMPLETE')."',
					        	confirmButtonText: '".JText::_('PLG_FABRIK_FORM_EMUNDUSREDIRECT_SEND_FILE')."',
					        	showCancelButton: true,
					        	cancelButtonText: '".JText::_('PLG_FABRIK_FORM_EMUNDUSREDIRECT_CONTINUE')."',
					        	onClose: () => {
					            	window.location.href = '".$link."';
					        	}
				            })
			                .then(confirm => {
                                if (confirm.value) {
                                    window.location.href = '".$send_file_url."';
                                } else {
                                    window.location.href = '".$link."';
                                }
                            })
				      });
				  </script>");

			}

		}

        header('Location: '.$link);
        exit();
	}

	/**
	 * Raise an error - depends on whether you are in admin or not as to what to do
	 *
	 * @param   array   &$err    Form models error array
	 * @param   string   $field  Name
	 * @param   string   $msg    Message
	 *
	 * @return  void
	 * @throws Exception
	 */

	protected function raiseError(&$err, $field, $msg)
	{
		$app = JFactory::getApplication();

		if ($app->isAdmin())
		{
			$app->enqueueMessage($msg, 'notice');
		}
		else
		{
			$err[$field][0][] = $msg;
		}
	}
}
