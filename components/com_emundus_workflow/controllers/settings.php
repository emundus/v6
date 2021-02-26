<?php
    defined('_JEXEC') or die('Restricted access');
    jimport('joomla.application.component.controller');

    class EmundusworkflowControllersettings extends JControllerLegacy {
        public function __construct($config = array()) {
            require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
            parent::__construct($config);
        }

        public function redirectjroute() {
            $user = JFactory::getUser();

            if (!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
                $result = 0;
                $response = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
            } else {
                $jinput = JFactory::getApplication()->input;
                $url = $jinput->getString('link');

                $response = array('status' => true, 'msg' => 'SUCCESS', 'data' => JRoute::_($url, false));
            }
            echo json_encode((object)$response);
            exit;
        }

    }