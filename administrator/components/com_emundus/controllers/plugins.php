<?php


// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');

/**
 * Jobs list controller class.
 */
class EmundusControllerPlugins extends JControllerAdmin
{
	public function get_well_known_configuration() {
		$response = ['status' => false, 'message' => JText::_('ACCESS_DENIED')];

		$user = JFactory::getUser();

		if ($user->authorise('core.admin')) {
			$jinput = JFactory::getApplication()->input;
			$url = $jinput->getString('url', '');

			if (!empty($url)) {
				$json = file_get_contents($url);
				$response = ['status' => true, 'message' => JText::_('SUCCESS'), 'data' => json_decode($json)];
			}
		}

		echo json_encode($response);
		exit;
	}
}