<?php
/**
 * Created by PhpStorm.
 * User: bhubinet
 * Date: 08/02/23
 */
defined( '_JEXEC' ) or die( JText::_('RESTRICTED_ACCESS') );
require_once (JPATH_COMPONENT_SITE.DS.'helpers'.DS.'access.php');
require_once (JPATH_ADMINISTRATOR . '/components/com_emundus/helpers/update.php');

class EmundusControllerWebhook extends JControllerLegacy
{
	function display($cachable = false, $urlparams = false) {
		// Set a default view if none exists
		if (!JRequest::getCmd( 'view' )) {
			$default = 'webhook';
			JRequest::setVar('view', $default );
		}

		parent::display();
	}

    function generate() {
        $results = ['status' => true];

		if(EmundusHelperAccess::asAdministratorAccessLevel(JFactory::getUser()->id)) {
			$results['token'] = JUserHelper::genRandomPassword(32);
			$hash_token       = JApplicationHelper::getHash($results['token']);

			EmundusHelperUpdate::updateConfigurationFile('webhook_token', $hash_token);
		}


	    echo json_encode((object)$results);
	    exit;
    }
}
