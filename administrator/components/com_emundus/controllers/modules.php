<?php
/**
 * Created by PhpStorm.
 * User: yoan
 * Date: 28/01/15
 * Time: 16:28
 */
defined( '_JEXEC' ) or die( JText::_('RESTRICTED_ACCESS') );
require_once (JPATH_COMPONENT_SITE.DS.'helpers'.DS.'access.php');
require_once (JPATH_ADMINISTRATOR . '/components/com_emundus/helpers/update.php');

class EmundusControllerModules extends JControllerLegacy
{
	function display($cachable = false, $urlparams = false) {
		// Set a default view if none exists
		if ( ! JRequest::getCmd( 'view' ) ) {
			$default = 'modules';
			JRequest::setVar('view', $default );
		}
		parent::display();
	}

    function install() {
        $result = ['status' => false,'message' => ''];

        $jinput = JFactory::getApplication()->input;
        $module = $jinput->getString('module');

        switch($module){
            case 'qcm':
                $result['status'] = $this->installQcm();
                break;
            default:
                $result['message'] = 'Module not found';
        }

        echo json_encode((object)$result);
        exit;

    }

    function installQcm() {
        require_once (JPATH_ADMINISTRATOR . '/components/com_emundus/models/modules.php');
        $mModules = new EmundusModelModules();
        $mModules->installQCM();

        $params = [
            'menutype' => 'coordinatormenu',
            'title' => 'QCM',
            'alias' => 'qcm',
            'path' => 'qcm',
            'params' => [
                'menu_image' => 'images/emundus/menus/qcm.png'
            ]
        ];
        $parent = EmundusHelperUpdate::addJoomlaMenu($params);

        //TODO : Create menu with Fabrik list just created or found
        $params = [
            'menutype' => 'coordinatormenu',
            'title' => 'Questions',
            'alias' => 'questions',
            'path' => 'questions',
            'type' => 'component',
        ];
        EmundusHelperUpdate::addJoomlaMenu($params,$parent['id']);


        return true;
    }
}
