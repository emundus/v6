<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2016 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class userController extends hikashopController{
	var $delete = array();
	var $modify = array();
	var $modify_views = array();
	var $add = array();
	function __construct($config = array(),$skip=false){
		parent::__construct($config,$skip);
		if(!$skip){
			$this->registerDefaultTask('cpanel');
		}

		$this->display = array_merge($this->display, array(
			'cpanel',
			'form',
			'register',
			'downloads'
		));
	}

	function register() {
		if(empty($_REQUEST['data'])) {
			return $this->form();
		}
		$class = hikashop_get('class.user');
		$status = $class->register($this,'user');

		if(!empty($status)) {
			$config =& hikashop_config();
			$simplified = $config->get('simplified_registration', 0);

			if($simplified != 2){
				$usersConfig = JComponentHelper::getParams('com_users');
				$useractivation = $usersConfig->get('useractivation');
				if($useractivation == 0) {
					$this->_login($class->registerData->username, $class->registerData->password);
				}
			}

			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::sprintf('THANK_YOU_FOR_REGISTERING',HIKASHOP_LIVE));
			JRequest::setVar('layout', 'after_register');
			return parent::display();

		}
		$this->form();
	}

	function _login($user='',$pass='',$checkToken=true){
		$options = array();
		$options['remember'] = JRequest::getBool('remember', false);
		$options['return'] = false;
		$credentials = array();
		if(empty($user)){
			$credentials['username'] = JRequest::getVar('username', '', 'request', 'username');
		}else{
			$credentials['username'] = $user;
		}
		if(empty($pass)){
			$credentials['password'] = JRequest::getString('passwd', '', 'request', JREQUEST_ALLOWRAW);
		}else{
			$credentials['password'] = $pass;
		}

		$mainframe = JFactory::getApplication();
		$error = $mainframe->login($credentials, $options);

		$user = JFactory::getUser();

		if(JError::isError($error) || $user->guest){
			return false;
		}

		$class = hikashop_get('class.user');
		$user_id = $class->getID($user->get('id'));

		if($user_id){
			$app = JFactory::getApplication();
			$app->setUserState( HIKASHOP_COMPONENT.'.user_id',$user_id );
		}
		return true;

	}

	function cpanel() {
		if(!$this->_checkLogin()) return true;
		JRequest::setVar( 'layout', 'cpanel'  );
		return parent::display();
	}

	function form() {
		$user = JFactory::getUser();
		if ($user->guest) {
			JRequest::setVar( 'layout', 'form'  );
			return $this->display();
		}else{
			$app=JFactory::getApplication();
			$app->redirect(hikashop_completeLink('user&task=cpanel',false,true));
			return false;
		}
	}

	function downloads() {
		if(!$this->_checkLogin()) return true;
		JRequest::setVar( 'layout', 'downloads'  );
		return parent::display();
	}

	function _checkLogin() {
		$user = JFactory::getUser();
		if ($user->guest) {
			$app=JFactory::getApplication();
			$app->enqueueMessage(JText::_('PLEASE_LOGIN_FIRST'));
			global $Itemid;
			$url = '';
			if(!empty($Itemid)){
				$url='&Itemid='.$Itemid;
			}
			if(!HIKASHOP_J16){
				$url = 'index.php?option=com_user&view=login'.$url;
			}else{
				$url = 'index.php?option=com_users&view=login'.$url;
			}
			$app->redirect(JRoute::_($url.'&return='.urlencode(base64_encode(hikashop_currentUrl('',false))),false));
			return false;
		}
		return true;
	}

}
