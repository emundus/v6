<?php
/**
 * @package     Joomla
 * @subpackage  eMundus
 * @link        http://www.emundus.fr
 * @copyright   Copyright (C) 2016 eMundus. All rights reserved.
 * @license     GNU/GPL
 * @author      James Dean
 */

// No direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');

/**
 * Files Controller
 *
 * @package    Joomla
 * @subpackage eMundus
 * @since      5.0.0
 */
class EmundusonboardControllerfiles extends JControllerLegacy {

    var $model = null;
    public function __construct($config = array()) {
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
        parent::__construct($config);
        $this->model = $this->getModel('files');
    }

    public function getfilescount() {
        $user = JFactory::getUser();
        $m_camp = $this->model;

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $campaigns = $m_camp->getFilesCount();

            if ($campaigns > 0) {
                $tab = array('status' => 1, 'msg' => JText::_('CAMPAIGNS_RETRIEVED'), 'data' => $campaigns);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_CAMPAIGNS'), 'data' => $campaigns);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getallfiles() {
        $user = JFactory::getUser();
        $m_camp = $this->model;

        $prog = $_GET['prog'];
        $camp = $_GET['camp'];
        $session = $_GET['session'];
        $status = $_GET['status'];

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $files = $m_camp->getAssociatedFiles($prog, $camp, $session, $status);

            if (count($files) > 0) {
                $tab = array('status' => 1, 'msg' => JText::_('FILES_RETRIEVED'), 'data' => $files);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_FILES'), 'data' => $files);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }
    
    public function getdistincts() {
        $user = JFactory::getUser();
        $jinput = JFactory::getApplication()->input;
        $ids = $jinput->getRaw('ids');
        $m_camp = $this->model;

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $files = $m_camp->getDistincts($ids);

            if (count($files) > 0) {
                $tab = array('status' => 1, 'msg' => JText::_('DISTINCTS_RETRIEVED'), 'data' => $files);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_DISTINCTS'), 'data' => $files);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }
}

