<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class plgHikamarketVendorUsergroup extends JPlugin {
	public function __construct(&$subject, $config) {
		parent::__construct($subject, $config);
	}

	public function onAfterVendorCreate(&$vendor) {
		$vendorGroup = (int)$this->params->get('vendor_group', 0);
		if(empty($vendorGroup))
			return;

		$move_user = (int)$this->params->get('move_user', 0);
		$default_group = (int)$this->params->get('default_group', 0);

		if(!$move_user)
			$default_group = 0;

		$userClass = hikamarket::get('shop.class.user');
		$user = $userClass->get($vendor->vendor_admin_id);
		$user_updated = $this->updateGroup($user->user_cms_id, $vendorGroup, $default_group);

		$logout_user = (int)$this->params->get('logout_user', 0);
		if(!empty($logout_user) && $user_updated) {
			$jconf = JFactory::getConfig();
			if($jconf->get('session_handler', 'none') == 'database') {
				$db = JFactory::getDBO();
				$db->setQuery('DELETE FROM ' . hikamarket::table('session', false).' WHERE client_id = 0 AND userid = ' . (int)$user->user_cms_id);
				$db->execute();
			}

			$app = JFactory::getApplication();
			if(!hikamarket::isAdmin())
				$app->logout($user->user_cms_id);
		}
	}

	private function updateGroup($user_id, $new_group_id, $remove_group_id = 0) {
		$user = clone(JFactory::getUser($user_id));
		$user_update = false;

		jimport('joomla.access.access');
		$userGroups = $user->groups;
		if(empty($userGroups))
			$userGroups = JAccess::getGroupsByUser($user_id, true);
		if(!in_array($new_group_id, $userGroups)) {
			$userGroups[] = $new_group_id;
			$user_update = true;
		}
		if(!empty($remove_group_id)) {
			$key = array_search($remove_group_id, $userGroups);
			if(is_int($key)) {
				$user_update = true;
				unset($userGroups[$key]);
			}
		}
		$user->set('groups', $userGroups);

		if($user_update)
			$user->save();
		return $user_update;
	}
}
