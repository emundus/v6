<?php

/**
 * @package     Joomla
 * @subpackage  eMundus
 * @link        http://www.emundus.fr
 * @copyright   Copyright (C) 2016 eMundus. All rights reserved.
 * @license     GNU/GPL
 * @author      Merveille Gbetegan
 */

// No direct access

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

/**
 * List Controller
 *
 * @package    Joomla
 * @subpackage eMundus
 */
class EmundusControllerList extends JControllerLegacy {

    var $m_list = null;
    public function __construct($config = array()) {
        parent::__construct($config);

        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
        $this->m_list = $this->getModel('list');
    }

    public function getList() {
        $tab = array('status' => 0, 'msg' => JText::_("ACCESS_DENIED"));
        $user = JFactory::getUser();

        if (EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $jinput = JFactory::getApplication()->input;
            $listId = $jinput->getInt('listId');
            $listParticularConditionalColumn = json_decode($jinput->getString('listParticularConditionalColumn'));
            $listParticularConditionalColumnValues = json_decode($jinput->getString('listParticularConditionalColumnValues'));

            if (!empty($listId)) {
                $listData = $this->m_list->getList($listId, $listParticularConditionalColumn, $listParticularConditionalColumnValues);
                if (!empty($listData)) {
                    $tab = array('status' => 1, 'msg' => JText::_('COM_EMUNDUS_LIST_RETRIEVED'), 'data' => $listData);
                } else {
                    $tab = array('status' => 0, 'msg' => JText::_('COM_EMUNDUS_ERROR_CANNOT_RETRIEVE_LIST'), 'data' => $listData);
                }
            } else {
                $tab['msg'] = JText::_('MISSING_PARAMS');
            }
        }

        echo json_encode((object)$tab);
        exit;
    }

    public function getListActions() {
        $user = JFactory::getUser();
        $tab = array('status' => 0, 'msg' => JText::_("ACCESS_DENIED"));

        if (EmundusHelperAccess::asCoordinatorAccessLevel($user->id) || EmundusHelperAccess::asPartnerAccessLevel($user->id)) {
            $jinput = JFactory::getApplication()->input;
            $listId = $jinput->getInt('listId');
            $lisActionColumnId=  $jinput->getInt('listActionColumnId');
            $listData = $this->m_list->getListActions($listId,$lisActionColumnId,);

            if (!empty($listData)) {
                $tab = array('status' => 1, 'msg' => JText::_('COM_EMUNDUS_LIST_RETRIEVED'), 'data' => $listData);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('COM_EMUNDUS_ERROR_CANNOT_RETRIEVE_LIST'), 'data' => $listData);
            }
        }

        echo json_encode((object)$tab);
        exit;
    }

    public function actionSetColumnValueAs() {
        $user = JFactory::getUser();
        $tab = array('status' => 0, 'msg' => JText::_("ACCESS_DENIED"));

        if (EmundusHelperAccess::asCoordinatorAccessLevel($user->id) || EmundusHelperAccess::asPartnerAccessLevel($user->id)) {
            $jinput = JFactory::getApplication()->input;
            $rowId = $jinput->getString('row_id');
            $value = $jinput->getString('value');
            $columnName = $jinput->getString('column_name');
            $dbTablename = $jinput->getString('db_table_name');
            $updated = $this->m_list->actionSetColumnValueAs($rowId,$value,$dbTablename,$columnName);

            if ($updated) {
                $tab = array('status' => 1, 'msg' => JText::_('COM_EMUNDUS_LIST_RETRIEVED'), 'data' => $updated);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('COM_EMUNDUS_ERROR_CANNOT_RETRIEVE_LIST'), 'data' => $updated);
            }
        }

        echo json_encode((object)$tab);
        exit;
    }

    public function updateActionState()
    {
        $user = JFactory::getUser();
        $tab = array('status' => 0, 'msg' => JText::_("ACCESS_DENIED"));

        if (EmundusHelperAccess::asCoordinatorAccessLevel($user->id) || EmundusHelperAccess::asPartnerAccessLevel($user->id)) {
            $jinput = JFactory::getApplication()->input;
            $newValue = $jinput->getString('newValue');
            $rows = json_decode($jinput->getString('rows'), true);

            if (!empty($newValue) && !empty($rows)) {
                $updated = $this->m_list->updateActionState($newValue, $rows);

                $tab['status'] = $updated;
            }
        }

        echo json_encode((object)$tab);
        exit;
    }
}

