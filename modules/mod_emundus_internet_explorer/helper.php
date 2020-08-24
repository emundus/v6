<?php
defined('_JEXEC') or die('Access Denied');

class modEmundusInternetExplorerHelper {
	public function closeMessageAjax() {
		$session = JFactory::getSession();
		$session->set('showInternetExplorer', false);
		return json_encode((object)['status' => true, 'msg' => 'It\'s ok']);
	}
}