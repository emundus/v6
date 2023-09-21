<?php
/**
 * @version 2: EmundusAssigntogroup 2020-02 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2020 emundus.fr. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Assign application to group
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

class PlgFabrik_FormEmundusassigntogroup extends plgFabrik_Form {
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

        if ($params->get($pname) == '')
            return '';

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
    public function onBeforeCalculations(): bool {

        jimport('joomla.log.log');
        JLog::addLogger(['text_file' => 'com_emundus.assignToGroup.php'], JLog::ALL, ['com_emundus']);

        $db = JFactory::getDBO();
        $jinput = JFactory::getApplication()->input;
        $fnum = $jinput->get->get('rowid');

        $fabrik_elt = str_replace(' ', '', $this->getParam('fabrik_elt'));
        $value = str_replace(' ', '', $this->getParam('value'));
        $group_id = str_replace(' ', '', $this->getParam('group_id'));
        $reset = $this->getParam('reset', 0);

        if (empty($fabrik_elt) || empty($value) || empty($group_id)) {
            return false;
        }

        $request = explode('___', $fabrik_elt);
        $repeated_group = strpos($request[0], '_repeat');
        $values = explode(',', $value);
        $groups_id = explode(',', $group_id);

        if (!empty($request[0]) && !empty($request[1])) {

            // get value from application form
            if ($repeated_group) {
                $query = 'SELECT join_from_table FROM #__fabrik_joins WHERE table_join LIKE '.$db->Quote($request[0]);
                try {

                    $db->setQuery($query);
                    $table = $db->loadResult();

                } catch (Exception $e) {
                    JLog::add('Error in script/assign-to-group getting parent table at query: '.$query, JLog::ERROR, 'com_emundus');
                }

                $query = 'SELECT `'.$request[1].'` FROM `'.$request[0].'` AS rt 
							LEFT JOIN `'.$table.'` AS t ON t.id = rt.parent_id 
							WHERE `t`.`fnum` LIKE '.$db->Quote($fnum);

                try {

                    $db->setQuery($query);
                    $columns = $db->loadColumn();

                } catch (Exception $e) {
                    JLog::add('Error in script/assign-to-group getting application values from repeated_group at query: '.$query, JLog::ERROR, 'com_emundus');
                }

            } else {
                $query = 'SELECT `'.$request[1].'` FROM `'.$request[0].'` WHERE `fnum` LIKE '.$db->Quote($fnum);
            }

            try {

                $db->setQuery($query);
                $column = !empty(json_decode(str_replace(' ', '', $db->loadResult()))) ? json_decode(str_replace(' ', '', $db->loadResult())) : str_replace(' ', '', $db->loadResult());

            } catch (Exception $e) {
                JLog::add('Error in script/assign-to-group getting application value at query: '.$query, JLog::ERROR, 'com_emundus');
                $column = '';
            }


            // reset previous associations
            if ($reset == 1) {
                $query = 'DELETE FROM `#__emundus_group_assoc` WHERE fnum LIKE '.$db->Quote($fnum);

                try {

                    $db->setQuery($query);
                    $db->execute();

                } catch (Exception $e) {
                    JLog::add('Error in script/assign-to-group delete groups assignation at query: '.$query, JLog::ERROR, 'com_emundus');
                }
            }
        }

        // for each value compared make the good group_id association
        foreach ($values as $key => $value) {

            if ($repeated_group) {
                $match = in_array($value, $columns);

            } else if (is_array($column)){
                $match = false;
                foreach ($column as $col){
                    if ($col == $value){
                        $match = true;
                        break;
                    }
                }
            }
            else {
                $match = ($column == $value);
            }


            if ($match) {

                $query = 'SELECT COUNT(id) FROM #__emundus_group_assoc
	                WHERE group_id = '.$groups_id[$key].' AND action_id = 1 AND fnum LIKE '.$db->Quote($fnum);

                try {

                    $db->setQuery($query);
                    $cpt = $db->loadResult();

                } catch (Exception $e) {
                    JLog::add('Error in script/assign-to-group getting groups at query: '.$query, JLog::ERROR, 'com_emundus');
                    $cpt = 0;
                }

                if ($cpt == 0) {
                    $query = 'INSERT INTO #__emundus_group_assoc (`group_id`, `action_id`, `fnum`, `c`, `r`, `u`, `d`)
	                    VALUES ('.$groups_id[$key].', 1, '.$db->Quote($fnum).', 0, 1, 0, 0)';

                    try {

                        $db->setQuery($query);
                        $db->execute();

                    } catch (Exception $e) {
                        JLog::add('Error in script/assign-to-group setting rights to groups at query: '.$query, JLog::ERROR, 'com_emundus');
                    }
                }
            }
        }

        $this->syncAllActions($fnum);
        return true;
    }

    public function syncAllActions($fnum) {
        try {
            $dbo = JFactory::getDBO();
            $select_fnum = empty($fnum) ?: ' AND fnum LIKE ' . $dbo->quote($fnum);

            $queryGetMissingGroups = 'SELECT id FROM jos_emundus_setup_groups WHERE id NOT IN (SELECT group_id FROM jos_emundus_acl)';
            $queryActionID = "SELECT id FROM jos_emundus_setup_actions WHERE status >= 1";
            $groupAssocQuery = "select jega.fnum, jega.group_id, jega.action_id from jos_emundus_group_assoc as jega left join jos_emundus_setup_actions as jesa on jesa.id = jega.action_id where jesa.status = 1" .$select_fnum;
            $userAssocQuery = "select jega.fnum, jega.user_id, jega.action_id from jos_emundus_users_assoc as jega left join jos_emundus_setup_actions as jesa on jesa.id = jega.action_id where jesa.status = 1" . $select_fnum;
            $queryAcl = "select action_id, group_id from jos_emundus_acl";

            $dbo->setQuery($queryGetMissingGroups);
            $missingGroups = $dbo->loadColumn();
            if (!empty($missingGroups)) {
                $queryInsertMissingGroups = 'INSERT INTO `jos_emundus_acl` (`group_id`, `action_id`, `c`, `r`, `u`, `d`) VALUES ('.implode(',1,0,1,0,0),(',$missingGroups).',1,0,1,0,0)';
                $dbo->setQuery($queryInsertMissingGroups);
                $dbo->execute();
            }

            $dbo->setQuery($queryActionID);
            $actionsId = $dbo->loadColumn();
            $dbo->setQuery($queryAcl);
            $aclAction = $dbo->loadAssocList();
            $dbo->setQuery($groupAssocQuery);
            $arrayGroupAssoc = $dbo->loadAssocList();
            $dbo->setQuery($userAssocQuery);
            $arrayUserAssoc = $dbo->loadAssocList();
            $acl = array();
            $aclGroupAssoc = array();
            $aclUserAssoc = array();

            foreach ($aclAction as $action) {
                $acl[$action['group_id']][] = $action['action_id'];
            }
            foreach ($arrayGroupAssoc as $aga) {
                $aclGroupAssoc[$aga['fnum']][$aga['group_id']][] = $aga['action_id'];
            }
            foreach ($arrayUserAssoc as $aua) {
                $aclUserAssoc[$aua['fnum']][$aua['user_id']][] = $aua['action_id'];
            }
            foreach ($acl as $gId => $groupAction) {
                $acl[$gId] = array_diff($actionsId, $groupAction);
            }
            $queryActionID = "SELECT id FROM jos_emundus_setup_actions WHERE status = 1";
            $dbo->setQuery($queryActionID);
            $actionsId = $dbo->loadColumn();

            foreach ($aclGroupAssoc as $fnum => $groups) {
                foreach ($groups as $gid => $action) {
                    $aclGroupAssoc[$fnum][$gid] = array_diff($actionsId, $action);
                }
            }
            foreach ($aclUserAssoc as $fnum => $users) {
                foreach ($users as $uid => $action) {
                    $aclUserAssoc[$fnum][$uid] = array_diff($actionsId, $action);
                }
            }

            $canInsert = false;
            $insert = "INSERT INTO jos_emundus_acl (action_id, group_id, c, r, u, d) values ";
            $overload = array();
            foreach ($acl as $gid => $actions) {
                if (!empty($actions)) {
                    if (count($actions) > count($overload)) {
                        $overload = $actions;
                    }
                    $canInsert = true;
                    foreach ($actions as $aid) {
                        $insert .= "({$aid}, {$gid}, 0, 0, 0, 0),";
                    }
                }
            }

            if ($canInsert) {
                $insert = rtrim($insert, ",");
                $dbo->setQuery($insert);

                $dbo->execute();
            }
            $canInsert = false;
            $insert = "INSERT INTO jos_emundus_group_assoc (fnum, action_id, group_id, c, r, u, d) values ";

            foreach ($aclGroupAssoc as $fnum => $groups) {
                foreach ($groups as $gid => $assocActions) {
                    if (!empty($assocActions)) {
                        $canInsert = true;
                        foreach ($assocActions as $aid) {
                            $insert .= "({$fnum}, {$aid}, {$gid}, 0, 0, 0, 0),";
                        }
                    }
                }
            }
            if ($canInsert) {
                $insert = rtrim($insert, ",");
                $dbo->setQuery($insert);

                $dbo->execute();
            }
            $canInsert = false;
            $insert = "INSERT INTO jos_emundus_users_assoc (fnum, action_id, user_id, c, r, u, d) values ";

            foreach ($aclUserAssoc as $fnum => $users) {
                foreach ($users as $uid => $assocActions) {
                    if (!empty($assocActions)) {
                        foreach ($assocActions as $aid) {
                            $user = JFactory::getUser($uid);
                            if ($user->id > 0) {
                                $canInsert = true;
                                $insert .= "({$fnum}, {$aid}, {$uid}, 0, 0, 0, 0),";
                            }
                        }
                    }
                }
            }

            if ($canInsert) {
                $insert = rtrim($insert, ",");
                $dbo->setQuery($insert);

                $dbo->execute();
            }

        } catch (Exception $e) {
            JLog::add('Error in script/assign-to-group setting default rights to groups at query: '.$insert, JLog::ERROR, 'com_emundus');
        }
    }

    /**
     * Raise an error - depends on whether you are in admin or not as to what to do
     *
     * @param   array   &$err   Form models error array
     * @param   string  $field  Name
     * @param   string  $msg    Message
     *
     * @return  void
     */

    protected function raiseError(&$err, $field, $msg)
    {
        $app = JFactory::getApplication();

        if ($app->isClient('administrator'))
        {
            $app->enqueueMessage($msg, 'notice');
        }
        else
        {
            $err[$field][0][] = $msg;
        }
    }
}
