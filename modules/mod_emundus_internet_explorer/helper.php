<?php
defined('_JEXEC') or die('Access Deny');
require_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'files.php');
require_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'list.php');
require_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'users.php');
require JPATH_LIBRARIES . '/emundus/vendor/autoload.php';

class modEmundusInternetExplorerHelper {
	public function closeMessageAjax() {
		$session = JFactory::getSession();
		$session->set('showInternetExplorer', false);
		return json_encode((object)['status' => true, 'msg' => 'It\'s ok']);
	}
}