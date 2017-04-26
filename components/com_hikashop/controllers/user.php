<?php
/**
 * @package	HikaShop for Joomla!
 * @version	3.0.1
 * @author	hikashop.com
 * @copyright	(C) 2010-2017 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class userController extends hikashopController {
	var $delete = array();
	var $modify = array();
	var $modify_views = array();
	var $add = array();

	public function __construct($config = array(), $skip = false) {
		parent::__construct($config,$skip);
		if(!$skip){
			$this->registerDefaultTask('cpanel');
		}

		$this->display = array_merge($this->display, array(
			'cpanel',
			'form',
			'register',
			'downloads',
			'activate'
		));
	}

	public function register() {
		if(empty($_REQUEST['data']))
			return $this->form();

		$userClass = hikashop_get('class.user');
		$status = $userClass->registerLegacy($this, 'user');

		if(!empty($status)) {
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::sprintf('THANK_YOU_FOR_REGISTERING', HIKASHOP_LIVE));
			JRequest::setVar('layout', 'after_register');
			return parent::display();
		}
		$this->form();
	}

	public function cpanel() {
		if(!$this->_checkLogin())
			return true;
		JRequest::setVar('layout', 'cpanel');
		return parent::display();
	}

	function form() {
		$user = JFactory::getUser();
		if($user->guest) {
			JRequest::setVar('layout', 'form');
			return $this->display();
		}

		$app = JFactory::getApplication();
		$app->redirect(hikashop_completeLink('user&task=cpanel', false, true));
		return false;
	}

	public function downloads() {
		if(!$this->_checkLogin())
			return true;
		JRequest::setVar('layout', 'downloads');
		return parent::display();
	}

	protected function _checkLogin() {
		$user = JFactory::getUser();
		if(!$user->guest)
			return true;

		$app = JFactory::getApplication();
		$app->enqueueMessage(JText::_('PLEASE_LOGIN_FIRST'));

		global $Itemid;
		$url = '';
		if(!empty($Itemid))
			$url = '&Itemid='.$Itemid;

		if(!HIKASHOP_J16)
			$url = 'index.php?option=com_user&view=login'.$url;
		else
			$url = 'index.php?option=com_users&view=login'.$url;

		$app->redirect(JRoute::_($url.'&return='.urlencode(base64_encode(hikashop_currentUrl('', false))), false));
		return false;
	}

	public function activate() {
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$user = JFactory::getUser();
		$usersConfig = JComponentHelper::getParams('com_users');
		$userActivation = (int)$usersConfig->get('useractivation');
		$allowUserRegistration = (int)$usersConfig->get('allowUserRegistration');

		if($user->get('id')) {
			$app->redirect(hikashop_completeLink('checkout',false,true));
		}

		if($allowUserRegistration == 0 || $userActivation == 0) {
			JError::raiseError(403, JText::_('Access Forbidden'));
			return false;
		}

		$lang = JFactory::getLanguage();
		$lang->load('com_user',JPATH_SITE);
		jimport('joomla.user.helper');

		$activation = hikashop_getEscaped(JRequest::getVar('activation', '', '', 'alnum'));

		if(empty($activation)) {
			$app->enqueueMessage(JText::_('HIKA_REG_ACTIVATE_NOT_FOUND'));
			return false;
		}

		if(version_compare(JVERSION,'1.6', '<')) {
			$result = JUserHelper::activateUser($activation);
		} else {
			if(HIKASHOP_J30) {
				JModelLegacy::addIncludePath(HIKASHOP_ROOT . DS . 'components' . DS . 'com_users' . DS . 'models');
			} else {
				JModel::addIncludePath(HIKASHOP_ROOT . DS . 'components' . DS . 'com_users' . DS . 'models');
			}

			$model = $this->getModel('Registration', 'UsersModel',array(),true);
			$language = JFactory::getLanguage();
			$language->load('com_users', JPATH_SITE, $language->getTag(), true);
			if($model)
				$result = $model->activate($activation);
		}

		if(!$result) {
			$app->enqueueMessage(JText::_('HIKA_REG_ACTIVATE_NOT_FOUND'));
			return false;
		}

		$app->enqueueMessage(JText::_('HIKA_REG_ACTIVATE_COMPLETE'));

		$id = JRequest::getInt('id', 0);
		$userClass = hikashop_get('class.user');
		$user = $userClass->get($id);

		if($id && file_exists(JPATH_ROOT.DS.'components'.DS.'com_comprofiler'.DS.'comprofiler.php') && $userActivation < 2) {
			$userClass->addAndConfirmUserInCB($user);
		}

		$infos = JRequest::getVar('infos', '');

		global $Itemid;
		$url_itemid = '';
		if(!empty($Itemid))
			$url_itemid = '&Itemid='.$Itemid;

		if(!empty($infos) && function_exists('json_decode')) {
			$infos = json_decode(base64_decode($infos), true);
			if(empty($infos['pass']) && !empty($infos['passwd']))
				$infos['pass'] = $infos['passwd'];
			JPluginHelper::importPlugin('user');
			if($userActivation < 2 && !empty($infos['pass']) && !empty($infos['username']) && $userClass->login($infos['username'], $infos['pass'])) {
				$page = JRequest::getString('page', 'checkout');
				if($page == 'checkout') {
					$app->redirect(hikashop_completeLink('checkout'.$url_itemid, false, true));
				} else {
					JRequest::setVar('layout', 'activate');
					return parent::display();
				}
			} elseif($userActivation >= 2) {
				$app->enqueueMessage(JText::_('HIKA_ADMIN_CONFIRM_ACTIVATION'));
			}
		}

		if(!HIKASHOP_J16)
			$url = 'index.php?option=com_user&view=login'.$url_itemid;
		else
			$url = 'index.php?option=com_users&view=login'.$url_itemid;
		$app->redirect(JRoute::_($url, false));
	}
}
