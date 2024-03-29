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
            case 'anonym_user_sessions':
                $result = $this->installAnonymUserForms();
                break;
            case 'homepage':
                $result['status'] = $this->installHomepage();
                break;
            case 'checklist':
                $result['status'] = $this->installChecklist();
                break;
            case 'ranking':
                require_once (JPATH_ADMINISTRATOR . '/components/com_emundus/models/ranking.php');
                $mRanking = new EmundusAdministrationModelRanking();
                $result['status'] = $mRanking->install(true);
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

    function installHomepage() {
        require_once (JPATH_ADMINISTRATOR . '/components/com_emundus/models/modules.php');
        $mModules = new EmundusModelModules();
        return $mModules->installHomepage();
    }

    function installChecklist() {
        require_once(JPATH_ADMINISTRATOR . '/components/com_emundus/models/modules.php');
        $mModules = new EmundusModelModules();
        return $mModules->installChecklist();
    }

    function installAnonymUserForms()
    {
        require_once (JPATH_ADMINISTRATOR . '/components/com_emundus/models/modules.php');
        $mModules = new EmundusModelModules();
        $installed = $mModules->installAnonymUserForms();

        if ($installed['status']) {
            require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'fabrik.php');
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
