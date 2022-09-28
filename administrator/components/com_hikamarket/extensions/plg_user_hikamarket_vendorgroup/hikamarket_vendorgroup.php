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
class plgUserHikamarket_vendorgroup extends JPlugin {

	protected $oldUser = null;

	public function __construct(& $subject, $config) {
		parent::__construct($subject, $config);
	}

	public function onUserBeforeSave($user, $isnew, $new) {
		return $this->onBeforeStoreUser($user, $isnew);
	}

	public function onUserAfterSave($user, $isnew, $success, $msg) {
		return $this->onAfterStoreUser($user, $isnew, $success, $msg);
	}
	public function onUserAfterDelete($user, $success, $msg) {
		return $this->onAfterDeleteUser($user, $success, $msg);
	}

	public function onBeforeStoreUser($user, $isnew) {
		$this->oldUser = $user;
		return true;
	}

	public function onAfterStoreUser($user, $isnew, $success, $msg) {
		if($success === false || !is_array($user))
			return false;

		if(!defined('DS'))
			define('DS', DIRECTORY_SEPARATOR);
		if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikamarket'.DS.'helpers'.DS.'helper.php'))
			return true;

		$vendorClass = hikamarket::get('class.vendor');
		$vendorClass->onAfterStoreUser($user, $isnew, $success, $msg, $this->oldUser);
		return true;
	}

	public function onAfterDeleteUser($user, $success, $msg) {
		return true;
	}

	public function onUserLogin($user, $options) {
		return $this->onLoginUser($user, $options);
	}

	public function onLoginUser($user, $options) {
		$app = JFactory::getApplication();
		$isAdmin = version_compare(JVERSION,'4.0','<') ? $app->isAdmin() : $app->isClient('administrator');
		if($isAdmin)
			return true;

		if(empty($user['id'])){
			if(!empty($user['username'])) {
				jimport('joomla.user.helper');
				$instance = new JUser();
				if($id = (int)JUserHelper::getUserId($user['username']))
					$instance->load($id);
				if($instance->get('block') == 0)
					$user_id = (int)$instance->id;
			}
		} else {
			$user_id = (int)$user['id'];
		}
		if(empty($user_id))
			return true;

		if(!defined('DS'))
			define('DS', DIRECTORY_SEPARATOR);
		if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikamarket'.DS.'helpers'.DS.'helper.php'))
			return true;

		jimport('joomla.access.access');
		$groups = JAccess::getGroupsByUser($user_id,false);

		$vendorClass = hikamarket::get('class.vendor');
		$vendorClass->onLoginUser($user_id, $user, $groups);
		return true;
	}
}
