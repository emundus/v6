<?php
/**
 * Modules controller class
 *
 * @package     Joomla.Administrator
 * @subpackage  eMundus
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;

jimport('joomla.application.component.controllerform');

require_once (JPATH_SITE.'/components/com_emundus/helpers/access.php');
require_once (JPATH_ADMINISTRATOR . '/components/com_emundus/helpers/update.php');

class EmundusAdminControllerModules extends JControllerLegacy
{
	function display($cachable = false, $urlparams = false) {
        $input = Factory::getApplication()->input;

		// Set a default view if none exists
		if (!$input->getCmd( 'view')) {
			$default = 'modules';
            $input->set('view', $default);
		}

		parent::display();
	}

    function install() {
        $result = ['status' => false,'message' => ''];

        $jinput = Factory::getApplication()->input;
        $module = $jinput->getString('module');

        switch($module){
            case 'qcm':
                $result['status'] = $this->installQcm();
                break;
            case 'anonym_user_sessions':
                $result = $this->installAnonymUserForms();
                break;
            case 'homepage':
                $result['status'] = $this->installHomepage();
                break;
            case 'checklist':
                $result['status'] = $this->installChecklist();
                break;
            default:
                $result['message'] = 'Module not found';
        }

        echo json_encode((object)$result);
        exit;

    }

    function installQcm() {
        require_once (JPATH_ADMINISTRATOR . '/components/com_emundus/models/modules.php');
        $mModules = new EmundusAdminModelModules();
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

    function installHomepage() {
        require_once (JPATH_ADMINISTRATOR . '/components/com_emundus/models/modules.php');
        $mModules = new EmundusAdminModelModules();
        return $mModules->installHomepage();
    }

    function installChecklist() {
        require_once(JPATH_ADMINISTRATOR . '/components/com_emundus/models/modules.php');
        $mModules = new EmundusAdminModelModules();
        return $mModules->installChecklist();
    }

    function installAnonymUserForms()
    {
        require_once (JPATH_ADMINISTRATOR . '/components/com_emundus/models/modules.php');
        $mModules = new EmundusAdminModelModules();
        $installed = $mModules->installAnonymUserForms();

        if ($installed['status']) {
            require_once (JPATH_SITE.'/components/com_emundus/helpers/fabrik.php');
            $params = EmundusHelperFabrik::prepareFabrikMenuParams();

            if (!empty($installed['send_anonym_form_id'])) {
                $datas = [
                    'menutype' => 'topmenu',
                    'title' => 'Déposer un dossier',
                    'link' => 'index.php?option=com_fabrik&view=form&formid=' . $installed['send_anonym_form_id'],
                    'path' => 'deposer-un-dossier',
                    'type' => 'component',
                    'component_id' => 10041,
                    'params' => $params
                ];
                $result = EmundusHelperUpdate::addJoomlaMenu($datas, 1, 0);

                if (!$result['status']) {
                    $installed['status'] = false;
                    $installed['message'] = 'Forms have been created but Menu has not';
                }
            }

            if (!empty($installed['connect_from_token_form_id'])) {
                $datas = [
                    'menutype' => 'topmenu',
                    'title' => 'Se connecter depuis ma clé d\'authentification',
                    'link' => 'index.php?option=com_fabrik&view=form&formid=' . $installed['connect_from_token_form_id'],
                    'path' => 'connexion-avec-token',
                    'type' => 'component',
                    'component_id' => 10041,
                    'params' => $params
                ];
                $result = EmundusHelperUpdate::addJoomlaMenu($datas, 1, 0);

                if (!$result['status']) {
                    $installed['status'] = false;
                    $installed['message'] = 'Forms have been created but Menu has not';
                }
            }
        }

        return $installed;
    }
}
