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
class vendorMarketController extends hikamarketController {
	protected $rights = array(
		'display' => array(
			'cpanel', 'show', 'registration', 'form', 'terms', 'activate', 'reports'
			,'listing', 'selection', 'useselection', 'getvalues', 'vendorpaynotify'
		),
		'add' => array('adduser','register'),
		'edit' => array('save', 'switchvendor'),
		'modify' => array(),
		'delete' => array()
	);

	public function __construct($config = array(), $skip = false) {
		parent::__construct($config, $skip);
		if(!$skip)
			$this->registerDefaultTask('cpanel');
	}

	public function show() {
		$shopConfig = hikamarket::config(false);
		if($shopConfig->get('store_offline')) {
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('SHOP_IN_MAINTENANCE'));
			return false;
		}

		$cid = hikamarket::getCID('vendor_id');
		if(empty($cid)) {
			$app = JFactory::getApplication();
			$menus = $app->getMenu();
			$menu = $menus->getActive();
			if(empty($menu) && !empty($Itemid)) {
				$menus->setActive($Itemid);
				$menu = $menus->getItem($Itemid);
			}
			if(is_object($menu)) {
				jimport('joomla.html.parameter');
				if(HIKASHOP_J30)
					$menuParams = $menu->getParams();
				else
					$menuParams = @$menu->params;
				$market_params = new HikaParameter($menuParams);
				$cid = $market_params->get('vendor_id');
				$cid = is_array($cid) ? (int)$cid[0] : (int)$cid;
				hikaInput::get()->set('vendor_id', $cid);
				hikaInput::get()->set('cid', $cid);
			}
		}
		if(empty($cid)) {
			$vendor_id = hikamarket::loadVendor(false);
			if(!empty($vendor_id)) {
				hikaInput::get()->set('vendor_id', $vendor_id);
				hikaInput::get()->set('cid', $vendor_id);
			}
		}

		hikaInput::get()->set('layout', 'show');
		return $this->display();
	}

	public function cpanel() {
		$config = hikamarket::config();
		if( !$config->get('frontend_edition', 0) ) {
			header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden', true, 403);
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('Access Forbidden'), 'error');
			return false;
		}
		if( !hikamarket::loginVendor() )
			return false;
		hikaInput::get()->set('layout', 'cpanel');
		return parent::display();
	}

	public function terms() {
		$shopConfig = hikamarket::config(false);
		if($shopConfig->get('store_offline')) {
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('SHOP_IN_MAINTENANCE'));
			return false;
		}

		hikaInput::get()->set('layout', 'terms');
		return parent::display();
	}

	public function save() {
		$this->store();
		return $this->form();
	}

	public function store() {
		$config = hikamarket::config();
		if( !$config->get('frontend_edition', 0) ) {
			header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden', true, 403);
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('Access Forbidden'), 'error');
			return false;
		}

		$app = JFactory::getApplication();
		JSession::checkToken() || die('Invalid Token');

		$vendorClass = hikamarket::get('class.vendor');
		$status = $vendorClass->frontSaveForm();
		if($status) {
			$app->enqueueMessage(JText::_('HIKAM_SUCC_SAVED'), 'message');
			hikaInput::get()->set('cid', $status);
			hikaInput::get()->set('fail', null);
		} else {
			$app->enqueueMessage(JText::_('ERROR_SAVING'), 'error');
			if(!empty($vendorClass->errors)) {
				foreach($vendorClass->errors as $err) {
					$app->enqueueMessage($err, 'error');
				}
			}
		}
		return $status;
	}

	public function form() {
		$app = JFactory::getApplication();
		$config = hikamarket::config();

		if(!$config->get('frontend_edition', 0))
			return false;

		$registration = false;
		$user = hikamarket::loadUser(true);
		$vendor = hikamarket::loadVendor(false);
		$registration = $config->get('allow_registration', 1);

		if(empty($vendor) && !$registration) {
			$app->redirect('index.php');
			return false;
		}

		if(empty($user)) {
			jimport('joomla.application.component.helper');
			$usersConfig = JComponentHelper::getParams('com_users');
			if($usersConfig->get('allowUserRegistration') == '0') {
				$app->enqueueMessage(JText::_('PLEASE_LOGIN_FIRST'));
				global $Itemid;
				$url = '';
				if(!empty($Itemid)) { $url = '&Itemid=' . $Itemid; }
				$url = 'index.php?option=com_users&view=login' . $url;
				$app->redirect(JRoute::_($url . '&return='.urlencode(base64_encode(hikamarket::currentUrl())), false));
			}
		}

		if(!empty($vendor) && !hikamarket::acl('vendor/edit'))
			return hikamarket::deny('vendor', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_VENDOR_EDIT')));

		hikaInput::get()->set('layout', 'form');
		return parent::display();
	}

	public function registration() {
		return $this->form();
	}

	public function register() {
		$app = JFactory::getApplication();
		$config = hikamarket::config();
		$allow_registration = $config->get('allow_registration', 1);

		if(!$allow_registration || !$config->get('frontend_edition', 0)) {
			$app->redirect('index.php');
			return false;
		}

		$vendor = hikamarket::loadVendor(true);
		if($vendor != null) {
			$app->enqueueMessage(JText::_('HIKAM_ERR_REGISTER_ALREADY_VENDOR'));
			$app->redirect(hikamarket::completeLink('vendor', false, true));
			return false;
		}

		JSession::checkToken() || die('Invalid Token');

		$vendorClass = hikamarket::get('class.vendor');
		$user = hikamarket::loadUser(true);
		$create_user = empty($user);

		$status = $vendorClass->register($user);
		if($status) {
			$app->enqueueMessage(JText::sprintf('THANK_YOU_FOR_REGISTERING',HIKASHOP_LIVE));

			if($create_user) {
				$usersConfig = JComponentHelper::getParams('com_users');
				if((int)$usersConfig->get('useractivation') > 0)
					$app->enqueueMessage(JText::_('HIKA_REG_COMPLETE_ACTIVATE'));
			}

			hikaInput::get()->set('layout', 'after_register');
			return parent::display();
		}
		hikaInput::get()->set('layout', 'registration');
		return $this->form();
		return parent::display();
	}

	public function activate() {
		$app = JFactory::getApplication();
		$db	= JFactory::getDBO();
		$juser = JFactory::getUser();

		$usersConfig = JComponentHelper::getParams('com_users');
		$userActivation	= $usersConfig->get('useractivation');
		$allowUserRegistration = $usersConfig->get('allowUserRegistration');

		if($juser->get('id')) {
			$app->redirect(hikamarket::completeLink('vendor',false,true));
			return false;
		}
		unset($juser);

		if($allowUserRegistration == '0' || $userActivation == '0') {
			header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden', true, 403);
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('Access Forbidden'), 'error');
			return false;
		}

		$lang = JFactory::getLanguage();
		$lang->load('com_user', JPATH_SITE);
		jimport('joomla.user.helper');

		$activation = hikamarket::getEscaped(hikaInput::get()->getAlnum('activation', ''));

		if(empty($activation)) {
			$app->enqueueMessage(JText::_('HIKA_REG_ACTIVATE_NOT_FOUND'));
			$app->redirect('index.php');
			return false;
		}

		if(HIKASHOP_J30) {
			JModelLegacy::addIncludePath(HIKASHOP_ROOT.DS.'components'.DS.'com_users'.DS.'models');
		} else {
			JModel::addIncludePath(HIKASHOP_ROOT.DS.'components'.DS.'com_users'.DS.'models');
		}

		$model = $this->getModel('Registration', 'UsersModel', array(), true);
		$language = JFactory::getLanguage();
		$language->load('com_users', JPATH_SITE, $language->getTag(), true);
		$result = false;
		if($model)
			$result = $model->activate($activation);

		if(!$result) {
			$app->enqueueMessage(JText::_('HIKA_REG_ACTIVATE_NOT_FOUND'));
			$app->redirect('index.php');
			return false;
		}

		$app->enqueueMessage(JText::_('HIKA_REG_ACTIVATE_COMPLETE'));
		$id = hikaInput::get()->getInt('id', 0);
		$userClass = hikamarket::get('shop.class.user');
		$user = $userClass->get($id);
		if($id && file_exists(JPATH_ROOT.DS.'components'.DS.'com_comprofiler'.DS.'comprofiler.php') && (int)$userActivation < 2) {
			$userClass->addAndConfirmUserInCB($user);
		}

		$infos = hikaInput::get()->getString('infos', '');
		global $Itemid;
		$url = '';
		if(!empty($Itemid))
			$url = '&Itemid='.$Itemid;

		if(!empty($infos) && function_exists('json_decode')) {
			$infos = json_decode(base64_decode($infos), true);

			JPluginHelper::importPlugin('user');
			if($userActivation < 2 && is_array($infos) && !empty($infos['passwd']) && !empty($infos['username'])) {
				$options = array(
					'remember' => false,
					'return' => false
				);
				$credentials = array(
					'username' => $infos['username'],
					'password' => $infos['passwd']
				);
				$error = $app->login($credentials, $options);
				$juser = JFactory::getUser();
				if(JError::isError($error) || $juser->guest) {
					$app->redirect('index.php');
					return false;
				}

				$user_id = $userClass->getID($juser->get('id'));
				if(!empty($user_id)) {
					$app->setUserState(HIKASHOP_COMPONENT.'.user_id', $user_id);
				}
			} elseif($userActivation >= 2) {
				$app->enqueueMessage(JText::_('HIKA_ADMIN_CONFIRM_ACTIVATION'));
			}
		}

		$app->redirect(hikamarket::completeLink('vendor',false,true));
		return false;
	}

	public function adduser() {
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$config = hikamarket::config();
		$vendor = hikamarket::loadVendor(true);

		if(!$config->get('frontend_edition', 0))
			return false;

		while(ob_get_level())
			@ob_end_clean();

		if($vendor == null) {
			echo JText::_('PLEASE_LOGIN_FIRST');
			exit;
		}

		if(!hikamarket::acl('vendor/edit')) {
			echo JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_VENDOR_EDIT'));
			exit;
		}

		$email = hikaInput::get()->getString('email', '');
		if(empty($email)) {
			echo JText::_('HIKAM_INVALID_USER');
			exit;
		}

		$query = 'SELECT hu.*, ju.* FROM '.hikamarket::table('shop.user').' AS hu '.
			' INNER JOIN '.hikamarket::table('joomla.users').' AS ju ON hu.user_cms_id = ju.id '.
			' WHERE hu.user_vendor_id = 0 AND ju.block = 0 AND hu.user_email = ' . $db->Quote($email);
		$db->setQuery($query);
		$user = $db->loadObject();

		if(!empty($user)) {
			$ret = new stdClass();
			$ret->user_id = (int)$user->user_id;
			$ret->user_email = $user->user_email;
			$ret->user_vendor_id = $user->user_vendor_id;
			$ret->user_vendor_access = $user->user_vendor_access;
			$ret->name = $user->name;
			$ret->username = $user->username;

			echo json_encode($ret);

		} else {
			echo JText::_('HIKAM_INVALID_USER');
		}

		exit;
	}

	public function reports() {
		while(ob_get_level())
			@ob_end_clean();

		$vendor_id = hikamarket::loadVendor(false);
		$config = hikamarket::config();
		if(empty($vendor_id) || !$config->get('frontend_edition',0) || !hikamarket::acl('vendor/statistics')) {
			echo '{}';
			exit;
		}

		$statName = hikaInput::get()->getCmd('chart', '');
		$statValue = hikaInput::get()->getString('value', '');
		if(empty($statName) || empty($statValue)) {
			echo '[]';
			exit;
		}

		$statisticsClass = hikamarket::get('class.statistics');
		$ret = $statisticsClass->getAjaxData($vendor_id, $statName, $statValue);

		if($ret === false) {
			echo '[]';
			exit;
		}
		echo $ret;
		exit;
	}

	public function switchvendor() {
		JSession::checkToken() || die('Invalid Token');

		if(!hikamarket::loginVendor())
			return false;

		$config = hikamarket::config();
		if(!$config->get('frontend_edition',0))
			return false;

		$vendor_id = hikamarket::getCID('vendor_id');
		if($config->get('user_multiple_vendor', 0) && !empty($vendor_id)) {
			$vendorClass = hikamarket::get('class.vendor');
			$vendorClass->switchVendor($vendor_id);
		}

		global $Itemid;
		$url = !empty($Itemid) ? '&Itemid=' . $Itemid : '';

		$app = JFactory::getApplication();
		$app->redirect(hikamarket::completeLink('vendor'.$url, false, true));
	}

	public function selection() {
		if(!hikamarket::loginVendor())
			return false;

		$config = hikamarket::config();
		if(!$config->get('frontend_edition',0))
			return false;

		$vendor_id = hikamarket::loadVendor(false);
		if($vendor_id > 1)
			return false;

		hikaInput::get()->set('layout', 'selection');
		return parent::display();
	}

	public function useselection() {
		if(!hikamarket::loginVendor())
			return false;

		$config = hikamarket::config();
		if(!$config->get('frontend_edition',0))
			return false;
		$vendor_id = hikamarket::loadVendor(false);
		if($vendor_id > 1)
			return false;

		hikaInput::get()->set('layout', 'useselection');
		return parent::display();
	}

	public function vendorpaynotify() {
		while(ob_get_level())
			@ob_end_clean();

		$mode = hikaInput::get()->getString('mode', null);
		if(empty($mode))
			$mode = @$_GET['mode'];
		if(empty($mode))
			exit;

		$order_id = (int)@$_GET['order_id'];
		$orderClass = hikamarket::get('class.order');
		$order = $orderClass->getRaw($order_id);

		if($order->order_type != 'vendorpayment')
			exit;

		switch($mode) {
			case 'paypal':
				$raw_data = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : file_get_contents('php://input');
				$ipndata = $this->processIPNdata($raw_data);
				$ipnConfirm = $this->sendIPNconfirm('https://www.paypal.com/webscr', $raw_data . '&cmd=_notify-validate');
				$verified = preg_match('#VERIFIED#i', $ipnConfirm);
				$completed = preg_match('#Completed#i', $ipndata['payment_status']);
				if($verified && $completed) {
					$shopConfig = hikamarket::config(false);
					$confirmed_status = $shopConfig->get('order_confirmed_status', 'confirmed');

					$update_order = new stdClass();
					$update_order->order_id = (int)$order_id;
					$update_order->order_status = $confirmed_status;
					$update_order->history = new stdClass();
					$update_order->history->history_reason = JText::_('AUTOMATIC_PAYMENT_NOTIFICATION');
					$update_order->history->history_notified = true;
				}
				break;
		}
		exit;
	}

	private function processIPNdata($data = '') {
		if(empty($data))
			return array();
		$ret = array();
		$elements = explode('&', $data);
		foreach($elements as $element) {
			list($k, $v) = explode('=', $element, 2);
			$k = urldecode($k);
			$v = urldecode($v);
			preg_match('/(\w+)(?:\[(\d+)\])?(?:\.(\w+))?/', $k, $parts);
			switch(count($parts)) {
				case 4:
					if(!isset($ret[ $parts[1] ])) $ret[ $parts[1] ] = array();
					if(!isset($ret[ $parts[1] ][ $parts[2] ])) $ret[ $parts[1] ][ $parts[2] ] = array();
					$ret[ $parts[1] ][ $parts[2] ][ $parts[3] ] = $v;
					break;
				case 3:
					if(!isset($ret[$parts[1]])) $ret[ $parts[1] ] = array();
					$ret[ $parts[1] ][ $parts[2] ] = $v;
					break;
				default:
					$ret[$k] = $v;
					break;
			}
		}
		return $ret;
	}

	private function sendIPNconfirm($notif_url, $data = '') {
		$url = parse_url($notif_url);
		if(!isset($url['query'])) $url['query'] = '';

		if(!isset($url['port'])) {
			if(!empty($url['scheme']) && in_array($url['scheme'], array('https', 'ssl'))) $url['port'] = 443;
			else $url['port'] = 80;
		}

		if(!empty($url['scheme']) && in_array($url['scheme'], array('https', 'ssl'))) $url['host_socket'] = 'ssl://' . $url['host'];
		else $url['host_socket'] = $url['host'];

		$fp = fsockopen($url['host_socket'], $url['port'], $errno, $errstr, 30);
		if(!$fp) return false;
		$uri = $url['path'] . ($url['query'] != '' ? '?' . $url['query'] : '');
		$header = 'POST '.$uri.' HTTP/1.1'."\r\n".
			'User-Agent: PHP/'.phpversion()."\r\n".
			'Referer: '.hikashop_currentURL()."\r\n".
			'Server: '.$_SERVER['SERVER_SOFTWARE']."\r\n".
			'Host: '.$url['host']."\r\n".
			'Content-Type: application/x-www-form-urlencoded'."\r\n".
			'Content-Length: '.strlen($data)."\r\n".
			'Accept: */'.'*'."\r\n".
			'Connection: close'."\r\n\r\n";
		fwrite($fp, $header . $data);
		$response = '';
		while(!feof($fp)) {
			$response .= fgets($fp, 1024);
		}
		fclose ($fp);
		return substr($response, strpos($response, "\r\n\r\n") + strlen("\r\n\r\n"));
	}

	public function getUploadSetting($upload_key, $caller = '') {
		if(!hikamarket::loginVendor())
			return false;
		$config = hikamarket::config();
		if(!$config->get('frontend_edition',0))
			return false;

		$shopConfig = hikamarket::config(false);
		$vendor_id = hikamarket::loadVendor(false);
		if(empty($upload_key))
			return false;
		if(!empty($vendor_id) && !hikamarket::acl('vendor/edit/image'))
			return false;
		$upload_value = null;
		$upload_keys = array(
			'vendor_image' => array(
				'type' => 'image',
				'field' => 'data[vendor][vendor_image]'
			)
		);

		if(empty($upload_keys[$upload_key]))
			return false;
		$upload_value = $upload_keys[$upload_key];

		$options = array();
		$options['upload_dir'] = $shopConfig->get('uploadfolder');
		if($vendor_id > 1)
			$options['sub_folder'] = 'vendor'.(int)$vendor_id.DS;

		if(empty($vendor_id))
			$options['sub_folder'] = 'vendor_register'.DS;

		return array(
			'limit' => 1,
			'type' => $upload_value['type'],
			'options' => $options,
			'extra' => array(
				'vendor_id' => $vendor_id,
				'field_name' => $upload_value['field']
			)
		);
	}

	public function manageUpload($upload_key, &$ret, $uploadConfig, $caller = '') {
		$config = hikamarket::config();
		if(!$config->get('frontend_edition',0) || empty($ret) || empty($ret->name))
			return;

		$vendor_id = hikamarket::loadVendor(false);
		if(empty($vendor_id) || !hikamarket::acl('vendor/edit/image'))
			return;

		$vendorClass = hikamarket::get('class.vendor');
		$vendor = new stdClass();
		$vendor->vendor_id = $vendor_id;
		$vendor->vendor_image = @$uploadConfig['options']['sub_folder'].$ret->name;
		$vendorClass->save($vendor);
	}

	public function getValues() {
		if(!hikamarket::loginVendor())
			return false;
		$config = hikamarket::config();
		if(!$config->get('frontend_edition', 0))
			return false;

		while(ob_get_level())
			@ob_end_clean();

		$vendor_id = hikamarket::loadVendor(false);
		if($vendor_id > 1) {
			echo '{}';
			exit;
		}

		$displayFormat = hikaInput::get()->getString('displayFormat', '');
		$search = hikaInput::get()->getString('search', null);

		$nameboxType = hikamarket::get('type.namebox');
		$options = array(
			'displayFormat' => $displayFormat
		);
		$ret = $nameboxType->getValues($search, 'vendor', $options);
		if(!empty($ret)) {
			echo json_encode($ret);
			exit;
		}
		echo '[]';
		exit;
	}
}
