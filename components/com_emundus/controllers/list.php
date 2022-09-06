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
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $listId = $jinput->getInt('listId');

            $listParticularConditionalColumn= json_decode($jinput->getString('listParticularConditionalColumn'));

            $listParticularConditionalColumnValues=  json_decode($jinput->getString('listParticularConditionalColumnValues'));

            $listData = $this->m_list->getList($listId,$listParticularConditionalColumn,$listParticularConditionalColumnValues);

            if (!empty($listData)) {
                $tab = array('status' => 1, 'msg' => JText::_('COM_EMUNDUS_LIST_RETRIEVED'), 'data' => $listData);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('COM_EMUNDUS_ERROR_CANNOT_RETRIEVE_LIST'), 'data' => $listData);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getListActions() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
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

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $rowId = $jinput->getString('row_id');
            $value = $jinput->getString('value');
            $columnName = $jinput->getString('column_name');
            $dbTablename = $jinput->getString('db_table_name');
            $listData = $this->m_list->actionSetColumnValueAs($rowId,$value,$dbTablename,$columnName);

            if (!empty($listData)) {
                $tab = array('status' => 1, 'msg' => JText::_('COM_EMUNDUS_LIST_RETRIEVED'), 'data' => $listData);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('COM_EMUNDUS_ERROR_CANNOT_RETRIEVE_LIST'), 'data' => $listData);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }
}

