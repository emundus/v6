<?php
/**
 * Created by PhpStorm.
 * User: yoan
 * Date: 28/01/15
 * Time: 16:28
 */
defined( '_JEXEC' ) or die( JText::_('RESTRICTED_ACCESS') );
require_once (JPATH_COMPONENT_SITE.DS.'helpers'.DS.'access.php');

class EmundusControllerScan extends JControllerLegacy
{
	function display($cachable = false, $urlparams = false) {
		// Set a default view if none exists
		if ( ! JRequest::getCmd( 'view' ) ) {
			$default = 'migration';
			JRequest::setVar('view', $default );
		}
		parent::display();
	}

	public function scan() {
		$result = ['status' => false, 'message' => JText::_('ACCESS_RESTRICTED'), 'data' => []];

		$user = JFactory::getUser();
		if (EmundusHelperAccess::asAdministratorAccessLevel($user->id)) {
			require_once JPATH_COMPONENT.'/helpers/update.php';
			$h_update = new EmundusHelperUpdate();

			$scan = $h_update->scanPHP8Compability();
			$result['status'] = true;
			$result['message'] = JText::_('COM_EMUNDUS_SCAN_SUCCESS');
			$result['data'] = $scan;
		}

		echo json_encode($result);
		exit;
	}
}
