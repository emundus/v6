<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class hikashopUserClass extends hikashopClass {
	var $tables = array('user');
	var $pkeys = array('user_id');

	public function get($id, $type = 'hikashop', $geoloc = false) {
		static $data = array();

		if($id === false) {
			$data = array();
			return true;
		}

		if(!empty($data[$type.'_'.$id]))
			return $data[$type.'_'.$id];

		$field = 'user_id';
		switch($type) {
			case 'hikashop':
				$field = 'user_id';
				$id = (int)$id;
				break;
			case 'email':
				$field = 'user_email';
				$id = $this->database->Quote(trim($id));
				break;
			case 'cms':
			default:
				$field = 'user_cms_id';
				$id = (int)$id;
				break;
		}

		$geo = '';
		$select = 'a.*,b.*';
		if($geoloc && hikashop_level(2)) {
			$geo = ' LEFT JOIN '.hikashop_table('geolocation').' AS c ON a.user_id=c.geolocation_ref_id AND c.geolocation_type=\'user\'';
			$select .= ',c.*';
		}

		$query = 'SELECT '.$select.' FROM '.hikashop_table('user').' AS a LEFT JOIN '.hikashop_table('users', false).' AS b ON a.user_cms_id = b.id ' . $geo . ' WHERE a.' . $field . ' = ' . $id;
		$this->database->setQuery($query);
		$user = $this->database->loadObject();

		if(!empty($user->user_params)) {
			$user->user_params = hikashop_unserialize($user->user_params);
		} elseif(!empty($user)) {
			$user->user_params = new stdClass();
		}
		$data[$type.'_'.$id] = $user;

		return $data[$type.'_'.$id];
	}

	public function getID($cms_id, $type = 'cms') {
		$user = $this->get($cms_id, $type);
		$id = (int)@$user->user_id;

		if(!empty($id) || $type != 'cms')
			return $id;

		$this->database->setQuery('SELECT * FROM '.hikashop_table('users',false).' WHERE id='.(int)$cms_id);
		$userData = $this->database->loadObject();

		if(!empty($userData)) {
			$user = new stdClass();
			$user->user_cms_id = $cms_id;
			$user->user_email = $userData->email;
			$id = $this->save($user);
		}
		return $id;
	}

	public function save(&$element, $skipJoomla = false) {
		$new = empty($element->user_id);
		if($new) {
			if(empty($element->user_created_ip)) {
				$config = hikashop_config();
				if($config->get('user_ip', 1))
					$element->user_created_ip = hikashop_getIP();
			}
			if(empty($element->user_created))
				$element->user_created = time();

			if(empty($element->user_email) && !empty($element->user_cms_id)) {
				$user = JFactory::getUser($element->user_cms_id);
				$element->user_email = $user->email;
			} elseif(!empty($element->user_email)&&empty($element->user_cms_id)) {
			}
		}

		if(isset($element->user_currency_id)) {
			$app = JFactory::getApplication();
			$config =& hikashop_config();

			$user = $this->get($element->user_id);
			if(empty($user->user_currency_id))
				$user->user_currency_id = $config->get('partner_currency');

			$previousPartnerCurrency = $user->user_currency_id;

			if(hikashop_isClient('administrator')) {
				if($element->user_currency_id == $config->get('partner_currency')) {
					$element->user_currency_id = 0;
				}
			} elseif($config->get('allow_currency_selection')) {
				$currencyClass = hikashop_get('class.currency');
				$currency = $currencyClass->get($element->user_currency_id);
				if(empty($currency->currency_published))
					unset($element->user_currency_id);
			} else {
				unset($element->user_currency_id);
			}
			if(!empty($element->user_currency_id))
				$element->user_currency_id = (int)$element->user_currency_id;
		}

		if(!empty($element->user_params))
			$element->user_params = serialize($element->user_params);

		JPluginHelper::importPlugin( 'hikashop' );
		$app = JFactory::getApplication();
		$do = true;
		if($new) {
			$app->triggerEvent( 'onBeforeUserCreate', array( & $element, & $do) );
		} else {
			$app->triggerEvent( 'onBeforeUserUpdate', array( & $element, & $do) );
		}

		if(!$do)
			return false;

		try{
			$element->user_id = parent::save($element);
		}catch(Exception $e) {
			$msg = $e->getMessage();
			if($e->getCode() == 1062) {
				$msg = JText::_('USER_WITH_SAME_EMAIL_ADDRESS_ALREADY_EXISTS');
			}
			$app->enqueueMessage($msg, 'error');
			return false;
		}

		if(empty($element->user_id))
			return $element->user_id;

		if($new) {
			$app->triggerEvent( 'onAfterUserCreate', array( & $element ) );
		} else {
			$app->triggerEvent( 'onAfterUserUpdate', array( & $element ) );
		}

		if($element->user_id == hikashop_loadUser()) {
			hikashop_loadUser(null,true);
			$this->get(false);
		}

		if($new) {
			$plugin = JPluginHelper::getPlugin('system', 'hikashopgeolocation');
			if(!empty($plugin) && hikashop_level(2)) {
				jimport('joomla.html.parameter');
				$params = new HikaParameter( $plugin->params );
				if(!empty($params) && $params->get('user',1)) {
					$geo = new stdClass();
					$geo->geolocation_ref_id = $element->user_id;
					$geo->geolocation_type = 'user';
					$geo->geolocation_ip = $element->user_created_ip;
					$geolocationClass = hikashop_get('class.geolocation');
					$geolocationClass->params =& $params;
					$geolocationClass->save($geo);
				}
			}
			return $element->user_id;
		}

		if(!$skipJoomla && !empty($element->user_email)){
			if(empty($element->user_cms_id)){
				$userData = $this->get($element->user_id);
				$element->user_cms_id = $userData->user_cms_id;
			}
			if(!empty($element->user_cms_id)) {
				$user = JFactory::getUser($element->user_cms_id);
				if(!empty($user) && $element->user_email!=$user->email){
					$user->email = $element->user_email;
					$user->save();
				}
			}
		}
		if(isset($element->user_currency_id)) {
			$config =& hikashop_config();

			if(empty($element->user_currency_id))
				$element->user_currency_id = $config->get('partner_currency');

			if($element->user_currency_id != $previousPartnerCurrency) {
				$currencyClass = hikashop_get('class.currency');

				$main_currency = (int)$config->get('main_currency', 1);
				$null = null;
				$ids = array(
					$previousPartnerCurrency => $previousPartnerCurrency,
					$element->user_currency_id => $element->user_currency_id,
					$main_currency => $main_currency
				);

				$currencies = $currencyClass->getCurrencies($ids, $null);

				$srcCurrency = $currencies[$previousPartnerCurrency];
				$dstCurrency = $currencies[$element->user_currency_id];
				$mainCurrency = $currencies[$main_currency];

				$this->_updatePartnerPrice($srcCurrency, $dstCurrency, $mainCurrency, $element, 'click');
				$this->_updatePartnerPrice($srcCurrency, $dstCurrency, $mainCurrency, $element, 'order');
				$this->_updatePartnerPrice($srcCurrency, $dstCurrency, $mainCurrency, $element, 'user');
			}
		}

		return $element->user_id;
	}

	function _updatePartnerPrice(&$srcCurrency,&$dstCurrency,&$mainCurrency,&$element,$type='click'){
		$amount='';
		if($srcCurrency->currency_id!=$mainCurrency->currency_id){
			$amount='('.$type.'_partner_price/ ((1+ '.floatval($srcCurrency->currency_percent_fee).'/100)*'.floatval($srcCurrency->currency_rate).' )) ';
			if($dstCurrency->currency_id!=$mainCurrency->currency_id){
				$amount = '('.$amount.'*'.floatval($dstCurrency->currency_rate).')*(1+'.floatval($dstCurrency->currency_percent_fee).'/100)';
			}
		}elseif($dstCurrency->currency_id!=$mainCurrency->currency_id){
			$amount = '('.$type.'_partner_price *'.floatval($dstCurrency->currency_rate).')*(1+'.floatval($dstCurrency->currency_percent_fee).'/100)';
		}
		if(!empty($amount)){
			$amount = ','.$type.'_partner_price='.$amount;
		}
		$orCurrencyConfig = ($srcCurrency->currency_id == $mainCurrency->currency_id) ? ' OR '.$type.'_partner_currency_id=0' : '';

		$query = 'UPDATE '.hikashop_table($type).' SET '.$type.'_partner_currency_id='.$element->user_currency_id.$amount.' WHERE '.$type.'_partner_id='.$element->user_id.' AND '.$type.'_partner_paid=0 AND ('.$type.'_partner_currency_id='.$srcCurrency->currency_id.$orCurrencyConfig.')';
		$this->database->setQuery($query);
		$this->database->execute();
	}

	public function saveForm() {
		$oldUser = null;
		$user_id = hikashop_getCID('user_id');
		if($user_id){
			$oldUser = $this->get($user_id);
		}
		$fieldsClass = hikashop_get('class.field');
		$element = $fieldsClass->getInput('user',$oldUser);
		if(empty($element)){
			return false;
		}
		$element->user_id = $user_id;

		$status = $this->save($element);
		if(!$status) {
			return $status;
		}

		$newDefaultId = hikaInput::get()->getInt('billing_address_default', 0);
		if($newDefaultId) {
			$addressClass = hikashop_get('class.address');
			$oldData = $addressClass->get($newDefaultId);
			if(!empty($oldData)) {
				$user_id = hikashop_getCID('user_id');
				if($user_id == $oldData->address_user_id) {
					$oldData->address_default = 1;
					$addressClass->save($oldData, 0 , 'billing');
				}
			}
		}

		$newDefaultId = hikaInput::get()->getInt('shipping_address_default', 0);
		if($newDefaultId) {
			$addressClass = hikashop_get('class.address');
			$oldData = $addressClass->get($newDefaultId);
			if(!empty($oldData)) {
				$user_id = hikashop_getCID('user_id');
				if($user_id == $oldData->address_user_id) {
					$oldData->address_default = 1;
					$addressClass->save($oldData, 0 , 'shipping');
				}
			}
		}

		hikashop_loadUser(null,true);
		$this->get(false);

		return $status;
	}

	public function delete(&$elements, $fromCMS = false) {
		$result = true;
		if(empty($elements))
			return $result;

		if(!is_array($elements)){
			$elements = array((int)$elements);
		}else{
			hikashop_toInteger($elements);
		}

		JPluginHelper::importPlugin( 'hikashop' );
		$app = JFactory::getApplication();
		$do = true;
		$app->triggerEvent('onBeforeUserDelete', array( & $elements, & $do));

		if(!$do) {
			return false;
		}

		$app = JFactory::getApplication();
		$addressClass = hikashop_get('class.address');

		foreach($elements as $el) {
			$query = 'SELECT count(*) FROM '.hikashop_table('order').' WHERE order_user_id=' . $el . ' AND order_type=\'sale\'';
			$this->database->setQuery($query);
			$hasOrders = $this->database->loadResult();

			$addresses = $addressClass->loadUserAddresses($el);
			foreach($addresses as $id => $data) {
				$addressClass->delete($id);
			}

			if(empty($hasOrders)) {
				$result = parent::delete($el);
				continue;
			}

			if(hikashop_isClient('administrator')) {
				$data = $this->get($el);
				$app->enqueueMessage('The user with the email address "'.$data->user_email.'" could not be deleted in HikaShop because he has orders attached to him. If you want to delete this user in HikaShop as well, you first need to delete his orders.');
				$result = false;
			}
			if($fromCMS) {
				$query = 'UPDATE '.hikashop_table('user').' SET user_cms_id=0 WHERE user_id IN ('.implode(',',$elements).')';
				$this->database->setQuery($query);
				$result = $this->database->execute();
			}
		}

		if($result) {
			$app->triggerEvent( 'onAfterUserDelete', array( & $elements ) );
		}
		return $result;
	}

	function loadPartnerData(&$user) {
		$config =& hikashop_config();

		if(empty($user->user_params->user_custom_fee)){
			if(!isset($user->user_params) || !is_object($user->user_params)){
				if(is_null($user)) $user = new stdClass();
				$user->user_params = new stdClass();
			}
			$user->user_params->user_partner_click_fee = $config->get('partner_click_fee',0);
			$user->user_params->user_partner_lead_fee = $config->get('partner_lead_fee',0);
			$user->user_params->user_partner_percent_fee = $config->get('partner_percent_fee',0);
			$user->user_params->user_partner_flat_fee = $config->get('partner_flat_fee',0);
		}

		$user->accumulated = array();
		if(empty($user->user_partner_activated))
			return;

		$minDelay = $config->get('affiliate_payment_delay',0);
		$maxTime = intval(time() - $minDelay);

		$db = JFactory::getDBO();

		$user->accumulated['currentclicks']=$user->accumulated['clicks']=$user->accumulated['paidclicks']=0;
		if(bccomp(sprintf('%F',$user->user_params->user_partner_click_fee),0,5)){
			$query='SELECT SUM(click_partner_price) AS clicks_total,click_partner_paid FROM '.hikashop_table('click').' WHERE click_partner_id='.$user->user_id.' GROUP BY click_partner_paid';
			$db->setQuery($query);
			$results = $db->loadObjectList('click_partner_paid');
			$user->accumulated['currentclicks']=$user->accumulated['clicks']=@$results[0]->clicks_total*1;
			$user->accumulated['paidclicks'] = @$results[1]->clicks_total*1;

			if(!empty($minDelay)){
				$query='SELECT SUM(click_partner_price) AS clicks_total FROM '.hikashop_table('click').' WHERE click_partner_id='.$user->user_id.' AND click_created < '.$maxTime.' AND click_partner_paid=0 GROUP BY click_partner_id';
				$db->setQuery($query);
				$user->accumulated['currentclicks']=$db->loadResult()*1;
			}

		}
		$user->accumulated['currentleads']=$user->accumulated['leads']=$user->accumulated['paidleads']=0;
		if(bccomp(sprintf('%F',$user->user_params->user_partner_lead_fee),0,5)){
			$query='SELECT SUM(user_partner_price) AS leads_total,user_partner_paid FROM '.hikashop_table('user').' WHERE user_partner_id='.$user->user_id.' GROUP BY user_partner_paid';
			$db->setQuery($query);
			$results = $db->loadObjectList('user_partner_paid');
			$user->accumulated['currentleads']=$user->accumulated['leads']=@$results[0]->leads_total*1;
			$user->accumulated['paidleads'] = @$results[1]->leads_total*1;
			if(!empty($minDelay)){
				$query='SELECT SUM(user_partner_price) AS leads_total FROM '.hikashop_table('user').' WHERE user_partner_id='.$user->user_id.' AND user_created < '.$maxTime.' AND user_partner_paid=0 GROUP BY user_partner_id';
				$db->setQuery($query);
				$user->accumulated['currentleads']=$db->loadResult()*1;
			}
		}

		$user->accumulated['currentsales'] = $user->accumulated['sales'] = $user->accumulated['paidsales'] = 0;
		if(bccomp(sprintf('%F',$user->user_params->user_partner_percent_fee),0,5) || bccomp(sprintf('%F',$user->user_params->user_partner_flat_fee),0,5)) {
			$partner_valid_status_list=explode(',',$config->get('partner_valid_status','confirmed,shipped'));
			foreach($partner_valid_status_list as $k => $partner_valid_status) {
				$partner_valid_status_list[$k]= $this->database->Quote($partner_valid_status);
			}
			$query = 'SELECT SUM(order_partner_price) AS sales_total, order_partner_paid FROM '.hikashop_table('order').' WHERE order_partner_id='.$user->user_id.' AND order_type=\'sale\' AND order_status IN ('.implode(',',$partner_valid_status_list).') GROUP BY order_partner_paid';
			$db->setQuery($query);
			$results = $db->loadObjectList('order_partner_paid');
			$user->accumulated['currentsales']=$user->accumulated['sales']=@$results[0]->sales_total*1;
			$user->accumulated['paidsales'] = @$results[1]->sales_total*1;
			if(!empty($minDelay)) {
				$query='SELECT SUM(order_partner_price) AS sales_total FROM '.hikashop_table('order').' WHERE order_partner_id='.$user->user_id.' AND order_created < '.$maxTime.' AND order_type=\'sale\' AND order_partner_paid=0 AND order_status IN ('.implode(',',$partner_valid_status_list).') GROUP BY order_partner_id';
				$db->setQuery($query);
				$user->accumulated['currentsales']=$db->loadResult()*1;
			}

		}
		$user->accumulated['total'] = round($user->accumulated['sales'] + $user->accumulated['leads'] + $user->accumulated['clicks'],2);
		$user->accumulated['currenttotal'] = round($user->accumulated['currentsales'] + $user->accumulated['currentleads'] + $user->accumulated['currentclicks'],2);
		$user->accumulated['paidtotal'] = round($user->accumulated['paidsales'] + $user->accumulated['paidleads'] + $user->accumulated['paidclicks'],2);
	}

	public function getGroups($user = null) {
		if(empty($user) || (int)$user == 0) {
			$my = JFactory::getUser();
		} elseif(is_numeric($user)) {
			$hkUser = $this->get( (int)$user );
			$my = JFactory::getUser( (int)$hkUser->user_cms_id );
		} elseif(is_object($user) && isset($user->user_cms_id)) {
			$my = JFactory::getUser( (int)$user->user_cms_id );
		}

		jimport('joomla.access.access');
		$config =& hikashop_config();
		$userGroups = JAccess::getGroupsByUser($my->id, (bool)$config->get('inherit_parent_group_access')); //$my->authorisedLevels();
		return $userGroups;
	}

	public function register($input_data, $mode, $options = array()) {
		$config = hikashop_config();

		$user = clone(JFactory::getUser());

		jimport('joomla.application.component.helper');
		$params = JComponentHelper::getParams('com_users');

		$mode = (int)$mode;

		if($mode != 2 && (int)$params->get('allowUserRegistration') == 0) {
			return array(
				'status' => false,
				'raise_error' => 403,
				'raise_error_msg' => JText::_('Access Forbidden')
			);
		}

		$fieldClass = hikashop_get('class.field');
		$old = null;
		$registerData = $fieldClass->getInput('register', $old, 'msg', $input_data['register']);
		$userData = $fieldClass->getFilteredInput('user', $old, 'msg', $input_data['user']);
		$addressData = null;
		if(isset($input_data['address']) && $input_data['address'] !== null)
			$addressData = $fieldClass->getFilteredInput(array('billing_address','billing_address'), $old, 'msg', $input_data['address']);
		$shippingAddressData = null;
		if(isset($input_data['shipping_address']) && $input_data['shipping_address'] !== null)
			$shippingAddressData = $fieldClass->getFilteredInput(array('shipping_address','shipping_address', 'shipping_'), $old, 'msg', $input_data['shipping_address']);

		$status = true;
		$messages = array();

		if($registerData === false || $addressData === false || $userData === false  || $shippingAddressData === false) {
			if(!empty($fieldClass->messages) && is_array($fieldClass->messages) && count($fieldClass->messages)) {
				foreach($fieldClass->messages as $k => $msg) {
					if(is_array($msg))
						$msg = $msg[0];
					$messages[$k] = array($msg, 'error');
				}
			}
			$fieldClass->messages = array();
			$status = false;
		}

		if($registerData !== false) {
			if(empty($registerData->name)) {
				if(!empty($addressData))
					$registerData->name = @$addressData->address_firstname.(!empty($addressData->address_middle_name)?' '.$addressData->address_middle_name:'').(!empty($addressData->address_lastname)?' '.$addressData->address_lastname:'');

				if(empty($registerData->name) && !empty($registerData->email)) {
					$parts = explode('@', $registerData->email);
					$registerData->name = array_shift($parts);
				}
			}

			if($mode == 0 && empty($registerData->name)){
				$status = false;
				$messages['register_name'] = array(JText::sprintf('PLEASE_FILL_THE_FIELD', JText::_('HIKA_NAME')), 'error');
			}

			if(in_array($mode, array(1, 3))) {
				$registerData->username = $registerData->email;
			} elseif($mode == 0 && empty($registerData->username)) {
				$status = false;
				$messages['register_username'] = array(JText::sprintf('PLEASE_FILL_THE_FIELD', JText::_('HIKA_USERNAME')), 'error');
			}

			if($mode == 1) {
				jimport('joomla.user.helper');
				$registerData->password = JUserHelper::genRandomPassword();
				$registerData->password2 = $registerData->password;
			}

			jimport('joomla.mail.helper');
			$mailer = JFactory::getMailer();
			if(empty($registerData->email) || (method_exists('JMailHelper', 'isEmailAddress') && !JMailHelper::isEmailAddress($registerData->email)) || !$mailer->validateAddress($registerData->email)){
				$status = false;
				$messages['register_email'] = array(JText::_('EMAIL_INVALID'), 'error');
			}

			if(in_array($mode, array(0, 3))) {
				if(empty($registerData->password)) {
					$status = false;
					$messages['register_password'] = array(JText::_('JGLOBAL_AUTH_EMPTY_PASS_NOT_ALLOWED'), 'error');
				} elseif($registerData->password != $registerData->password2) {
					$status = false;
					$messages['register_password'] = array(JText::_('JLIB_USER_ERROR_PASSWORD_NOT_MATCH'), 'error');
					$messages['register_password2'] = '';
				} else {
					$minimumLength = (int)$params->get('minimum_length');
					$minimumIntegers = (int)$params->get('minimum_integers');
					$minimumSymbols = (int)$params->get('minimum_symbols');
					$minimumUppercase = (int)$params->get('minimum_uppercase');
					$minimumLowercase = (int)$params->get('minimum_lowercase');

					if(!empty($minimumLength) && strlen((string)$registerData->password) < $minimumLength) {
						$status = false;
						$messages[] = array($this->error('too_short', $minimumLength), 'error');
					}
					if (strlen((string)$registerData->password) > 4096) {
						$status = false;
						$messages[] = array($this->error('too_long'), 'error');
					}
					$valueTrim = trim((string)$registerData->password);

					if (strlen((string)$registerData->password) != strlen($valueTrim)) {
						$status = false;
						$messages[] = array($this->error('space'), 'error');
					}

					$checks = array(
						'int' => array($minimumIntegers, '/[0-9]/'),
						'symbol' => array($minimumSymbols, '[\W]'),
						'uppercase' => array($minimumUppercase, '/[A-Z]/'),
						'lowercase' => array($minimumLowercase, '/[a-z]/'),
					);
					foreach($checks as $k => $v) {
						if(empty($v[0]))
							continue;
						$n = preg_match_all($v[1], $registerData->password, $m);
						if($n >= $v[0])
							continue;
						$status = false;
						$messages[] = array($this->error($k, $v[0]), 'error');
					}
				}
			}
		}

		$data = array(
			'name' => @$registerData->name,
			'username' => @$registerData->username,
			'email' => @$registerData->email,
			'password' => @$registerData->password,
			'password2' => @$registerData->password2
		);

		$_SESSION['hikashop_main_user_data'] = $data;

		if(!$status){
			return array( 'status' => false, 'messages' => $messages);
		}

		$ret = array(
			'status' => true,
			'messages' => array(),
			'registerData' => &$registerData,
			'userData' => &$userData,
			'addressData' => &$addressData,
			'shippingAddressData' => &$shippingAddressData
		);

		if(!empty($addressData->address_vat)) {
			$vatHelper = hikashop_get('helper.vat');
			if(!$vatHelper->isValid($addressData)) {
				$ret['status'] = false;
				$ret['messages']['VAT_NUMBER_NOT_VALID'] = array(JText::_('VAT_NUMBER_NOT_VALID'), 'warning');
				return $ret;
			}
		}
		if(!empty($shippingAddressData->address_vat)) {
			$vatHelper = hikashop_get('helper.vat');
			if(!$vatHelper->isValid($shippingAddressData)) {
				$ret['status'] = false;
				$ret['messages']['VAT_NUMBER_NOT_VALID'] = array(JText::_('VAT_NUMBER_NOT_VALID'), 'warning');
				return $ret;
			}
		}

		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$app->triggerEvent('onBeforeHikaUserRegistration', array(&$ret, $input_data, $mode));

		$data = array(
			'name' => @$registerData->name,
			'username' => @$registerData->username,
			'email' => @$registerData->email,
			'password' => @$registerData->password,
			'password2' => @$registerData->password2
		);
		$_SESSION['hikashop_main_user_data'] = $data;

		if($ret['status'] == false) {
			if(empty($ret['messages'])) {
				$ret['messages']['general'] = array(JText::_('REGISTRATION_NOT_ALLOWED'), 'error');
			}
			return $ret;
		}

		if($mode != 2) {

			$newUsertype = $params->get( 'new_usertype' );
			if(!$newUsertype)
				$newUsertype = 2; // "Registered" value for Joomla 2.5 and up

			$userGroupRegistration = $config->get('user_group_registration', '');
			if(!empty($userGroupRegistration)){
				if(!is_numeric($userGroupRegistration)){
					$fieldId = substr($userGroupRegistration,1);
					$field = $fieldClass->get($fieldId);
					if(in_array($field->field_table, array('user','address'))){
						$variable = $field->field_table.'Data';
						foreach($field->field_value as $key => $val) {
							$groups = explode(',', $key);
							foreach($groups as $group){
								$field->field_value[$group] = $group;
							}
						}
						if(isset($$variable->{$field->field_namekey})){
							$groups = explode(',', $$variable->{$field->field_namekey});
							$validGroups = array();
							foreach($groups as $group){
								if(!isset($field->field_value[$group]))
									continue;
								if(!is_numeric($group)){
									$ret['status'] = false;
									$ret['messages']['INVALID_VALUE_CUSTOM_FIELD'] = array(JText::sprintf('INVALID_VALUE_CUSTOM_FIELD', $group, $field->field_namekey), 'warning');
									return $ret;
								}
								$validGroups[(int)$group] = (int)$group;
							}
							if(count($validGroups))
								$data['groups'] = $validGroups;
						}
					}
				}

				if((int)$userGroupRegistration > 0)
					$newUsertype = (int)$userGroupRegistration;
			}
			if(empty($data['groups']))
				$data['groups'] = array(
					$newUsertype => $newUsertype
				);

			$jconfig = JFactory::getConfig();
			if(HIKASHOP_J30)
				$locale = $jconfig->get('language');
			else
				$locale = $jconfig->getValue('config.language');

			$data['params'] = array(
				'site_language' => $locale,
				'language' => $locale
			);

			$language = JFactory::getLanguage();
			$language->load('lib_joomla', JPATH_SITE);


			$privacy = $this->getPrivacyConsentSettings();
			if($privacy && !@$registerData->privacy) {
				$ret['status'] = false;
				$ret['messages']['PLG_SYSTEM_PRIVACYCONSENT_FIELD_ERROR'] = array(JText::_('PLG_SYSTEM_PRIVACYCONSENT_FIELD_ERROR'), 'error');
				return $ret;
			}

			if( !$user->bind($data, 'usertype') ) {
				$ret['status'] = false;
				$ret['messages'][] = array(JText::_( $user->getError() ), 'error');
				return $ret;
			}

			$user->set('id', 0);

			$jdate = JFactory::getDate();
			if(HIKASHOP_J30)
				$user->set('registerDate', $jdate->toSql());
			else
				$user->set('registerDate', $jdate->toMySQL());

			$useractivation = $params->get('useractivation');
			if($useractivation > 0) {
				jimport('joomla.user.helper');
				if(HIKASHOP_J40)
					$user->set('activation', JApplicationHelper::getHash( JUserHelper::genRandomPassword()) );
				elseif(HIKASHOP_J30)
					$user->set('activation', JApplication::getHash( JUserHelper::genRandomPassword()) );
				else
					$user->set('activation', JUtility::getHash( JUserHelper::genRandomPassword()) );

				$user->set('block', 1);
			}

			if( !$user->save() ) {
				$ret['status'] = false;
				$ret['messages'][] = array(JText::_( $user->getError() ), '');
				return $ret;
			}

			$ret['juser'] =& $user;
			$ret['userActivation'] = $useractivation;

			$this->get(false);
			$newUser = $this->get($user->email, 'email');

			if(!empty($newUser)) {
				$userData->user_id = $newUser->user_id;
				$userData->user_cms_id = $user->id;
			} else if(!empty($user->id))
				$userData->user_cms_id = $user->id;
			else
				$userData->user_email = $registerData->email;

			if($privacy)
				$this->addUserConsent($user);

			$ret['user_id'] = $this->save($userData);

		} else if($mode == 2) {
			$userData->user_email = $registerData->email;

			$privacy = $this->getPrivacyConsentSettings('contact');
			if($privacy && !empty($registerData->privacy_guest_check) && !$registerData->privacy_guest) {
				$ret['status'] = false;
				$ret['messages']['PLEASE_AGREE_TO_PRIVACY_POLICY'] = array(JText::_('PLEASE_AGREE_TO_PRIVACY_POLICY'), 'error');
				return $ret;
			}

			$query = 'SELECT * FROM '.hikashop_table('user').
					' WHERE user_email = '.$this->database->Quote($userData->user_email);
			$this->database->setQuery($query);
			$userInDB = $this->database->loadObject();

			if(@$userInDB->user_cms_id) {
				$ret['status'] = false;
				$ret['messages'][] = array(JText::_('EMAIL_ADDRESS_ALREADY_USED'), 'warning');
				$reset_url = JRoute::_('index.php?option=com_users&view=reset');
				$ret['messages'][] = array('<a href="'.$reset_url.'">'.JText::_('PLEASE_CLICK_HERE_TO_RESET_PASSWORD').'</a>', 'warning');
				return $ret;
			}

			$ret['user_id'] = (isset($userInDB->user_id) ? (int)$userInDB->user_id : 0);

			$app = JFactory::getApplication();
			$old_messages = $app->getMessageQueue();

			if(!empty($ret['user_id'])) {
				if($config->get('user_ip'))
					$userInDB->user_created_ip = hikashop_getIP();
				$ret['user_id'] = $this->save($userInDB);
			} else {
				$ret['user_id'] = $this->save($userData);
			}

			if(empty($ret['user_id'])) {
				$ret['status'] = false;
				$new_messages = $app->getMessageQueue();

				if(count($old_messages) < count($new_messages)) {
					$new_messages = array_slice($new_messages, count($old_messages));

					foreach($new_messages as $msg) {
						$ret['messages'][] = array(
							$msg['message'],
							$msg['type']
						);
					}
				}
				return $ret;
			}

			if(empty($_SESSION['hikashop_previously_guest_as']) || $_SESSION['hikashop_previously_guest_as'] != $ret['user_id']) {
				$query = 'UPDATE '.hikashop_table('address').' AS hk_addr '.
						' SET hk_addr.address_published = 0 '.
						' WHERE hk_addr.address_user_id='.(int)$ret['user_id'].' AND hk_addr.address_published = 1';

				$this->database->setQuery($query);
				$this->database->execute();
				unset($_SESSION['hikashop_previously_guest_as']);
			}

			$cartClass = hikashop_get('class.cart');
			$cart_id = $cartClass->getCurrentCartId();
			if($cart_id !== false && $cart_id > 0) {
				$cart = $cartClass->getFullCart($cart_id);
				$cart->user_id = $ret['user_id'];
				$cartClass->save($cart);
			}
		}

		$this->user_id = $ret['user_id'];

		if(!empty($addressData)) {
			if(isset($addressData->address_id))
				unset($addressData->address_id);

			if(!empty($options['address_type']))
				$addressData->address_type = $options['address_type'];

			$registerData->user_id = $ret['user_id'];
			if(!empty($addressData)) {
				$addressData->address_user_id = $ret['user_id'];
				$addressClass = hikashop_get('class.address');
				$ret['address_id'] = $addressClass->save($addressData);
			}
		}
		if(!empty($shippingAddressData)) {
			if(isset($shippingAddressData->address_id))
				unset($shippingAddressData->address_id);

			$shippingAddressData->address_type = 'shipping';

			if(!empty($shippingAddressData)) {
				$shippingAddressData->address_user_id = $ret['user_id'];
				$addressClass = hikashop_get('class.address');
				$ret['shipping_address_id'] = $addressClass->save($shippingAddressData);
			}
		}

		$send_email = ($mode != 2);
		$app->triggerEvent('onAfterHikaUserRegistration', array(&$ret, $input_data, $mode, &$send_email));

		if($mode == 2)
			return $ret;

		if($useractivation == 0 && file_exists(JPATH_ROOT.DS.'components'.DS.'com_comprofiler'.DS.'comprofiler.php')) {
			$newUser = $this->get($ret['user_id']);
			$this->addAndConfirmUserInCB($newUser, $addressData);
		}

		if($send_email && !empty($registerData->email)) {
			$mailClass = hikashop_get('class.mail');
			$registerData->user_data =& $userData;
			$registerData->address_data =& $addressData;
			$registerData->shipping_address_data =& $shippingAddressData;
			$registerData->active = $useractivation;

			$original_password = null;
			if(isset($registerData->password)) {
				$original_password = $registerData->password;
				$registerData->password = preg_replace('/[\x00-\x1F\x7F]/', '', $registerData->password);
			}

			global $Itemid;
			$url_itemid = '';
			if(!empty($Itemid))
				$url_itemid = '&Itemid=' . $Itemid;

			$lang = JFactory::getLanguage();
			$locale = strtolower(substr($lang->get('tag'),0,2));

			if(isset($input_data['page']) && !isset($options['page']))
				$options['page'] = $input_data['page'];

			$vars = '';
			if(!isset($options['autolog']) || $options['autolog'] == true)
				$vars = urlencode(base64_encode(json_encode(array('pass' => $registerData->password, 'username' => $registerData->username))));
			$registerData->activation_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=user&task=activate&activation='.$user->get('activation').'&infos='.$vars.'&id='.$ret['user_id'].$url_itemid.'&lang='.$locale;
			if(!empty($options['page']) && is_string($options['page']))
				$registerData->activation_url .= '&page='.urlencode($options['page']);
			$registerData->partner_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=affiliate&task=show'.$url_itemid;

			$mail = $mailClass->get('user_account', $registerData);

			$mail->subject = JText::sprintf($mail->subject, @$registerData->name, HIKASHOP_LIVE);
			$mail->dst_email =& $registerData->email;
			$mail->dst_name = '';
			if(!empty($registerData->name))
				$mail->dst_name =& $registerData->name;

			$mailClass->sendMail($mail);
			$mailSent = $mailClass->mail_success;

			if($params->get('mail_to_admin', 0)) {
				$mail = $mailClass->get('user_account_admin_notification', $registerData);
				$mail->subject = JText::sprintf($mail->subject, @$registerData->name, HIKASHOP_LIVE);
				if(empty($mail->dst_email))
					$mail->dst_email = explode(',', $config->get('from_email'));
				$mailClass->sendMail($mail);
			}

			unset($registerData->user_data);
			unset($registerData->address_data);
			unset($registerData->shipping_address_data);
			unset($registerData->active);
			unset($registerData->activation_url);
			unset($registerData->partner_url);
			if($original_password !== null)
				$registerData->password = $original_password;

			if($useractivation > 0) {
				if($mailSent) {
					$ret['messages']['HIKA_REG_COMPLETE_ACTIVATE'] = JText::_('HIKA_REG_COMPLETE_ACTIVATE');
				} else {
					$ret['messages']['HIKA_MAIL_ISSUE_ACTIVATION'] = array(
						JText::_('HIKA_MAIL_ISSUE_ACTIVATION'),
						'warning'
					);
				}
			}
		}

		return $ret;
	}

	private function error($key, $var=null) {
		$k = 'j3';
		if(HIKASHOP_J40) {
			$k = 'j4';
		} else {
			static $done = false;
			if(!$done) {
				$done = true;
				$language = JFactory::getLanguage();
				$language->load('com_users', JPATH_SITE, $language->getTag(), true);
			}
		}

		$msg = array(
			'j4' => array(
				'too_short' => 'JFIELD_PASSWORD_TOO_SHORT_N',
				'too_long' => 'JFIELD_PASSWORD_TOO_LONG',
				'space' => 'JFIELD_PASSWORD_SPACES_IN_PASSWORD',
				'int' => 'JFIELD_PASSWORD_NOT_ENOUGH_INTEGERS_N',
				'symbol' => 'JFIELD_PASSWORD_NOT_ENOUGH_SYMBOLS_N',
				'lowercase' => 'JFIELD_PASSWORD_NOT_ENOUGH_LOWERCASE_LETTERS_N',
				'uppercase' => 'JFIELD_PASSWORD_NOT_ENOUGH_UPPERCASE_LETTERS_N',
			),

			'j3' => array(
				'too_short' => 'COM_USERS_MSG_PASSWORD_TOO_SHORT_N',
				'too_long' => 'COM_USERS_MSG_PASSWORD_TOO_LONG',
				'space' => 'COM_USERS_MSG_SPACES_IN_PASSWORD',
				'int' => 'COM_USERS_MSG_NOT_ENOUGH_INTEGERS_N',
				'symbol' => 'COM_USERS_MSG_NOT_ENOUGH_SYMBOLS_N',
				'lowercase' => 'COM_USERS_MSG_NOT_ENOUGH_LOWERCASE_LETTERS_N',
				'uppercase' => 'COM_USERS_MSG_NOT_ENOUGH_UPPERCASE_LETTERS_N',
			),
		);
		if(is_null($var)) {
			return JText::_($msg[$k][$key]);
		}
		return JText::plural($msg[$k][$key], $var);
	}

	public function registerGuest($user_id, $registerData) {
		$user = clone(JFactory::getUser());

		jimport('joomla.application.component.helper');
		$params = JComponentHelper::getParams('com_users');

		$config = hikashop_config();

		$hikaUser = $this->get($user_id);

		$status = true;
		$messages = array();

		if(empty($hikaUser)) {
			$status = false;
			$messages['invalid_user'] = array(JText::_('INVALID_USER'), 'error');
		}

		$registerData->email = $hikaUser->user_email;

		if(empty($registerData->name)) {
			$status = false;
			$messages['register_name'] = array(JText::sprintf('PLEASE_FILL_THE_FIELD', JText::_('HIKA_NAME')), 'error');
		}

		if(empty($registerData->username)) {
			$status = false;
			$messages['register_username'] = array(JText::sprintf('PLEASE_FILL_THE_FIELD', JText::_('HIKA_USERNAME')), 'error');
		}

		jimport('joomla.mail.helper');
		$mailer = JFactory::getMailer();
		if(empty($registerData->email) || (method_exists('JMailHelper', 'isEmailAddress') && !JMailHelper::isEmailAddress($registerData->email)) || !$mailer->validateAddress($registerData->email)){
			$status = false;
			$messages['register_email'] = array(JText::_('EMAIL_INVALID'), 'error');
		}

		if(empty($registerData->password)) {
			$status = false;
			$messages['register_password'] = array(JText::_('JGLOBAL_AUTH_EMPTY_PASS_NOT_ALLOWED'), 'error');
		} elseif($registerData->password != $registerData->password2) {
			$status = false;
			$messages['register_password'] = array(JText::_('JLIB_USER_ERROR_PASSWORD_NOT_MATCH'), 'error');
			$messages['register_password2'] = '';
		} else {
			$minimumLength = (int)$params->get('minimum_length');
			$minimumIntegers = (int)$params->get('minimum_integers');
			$minimumSymbols = (int)$params->get('minimum_symbols');
			$minimumUppercase = (int)$params->get('minimum_uppercase');
			$minimumLowercase = (int)$params->get('minimum_lowercase');

			if(!empty($minimumLength) && strlen((string)$registerData->password) < $minimumLength) {
				$status = false;
				$messages[] = array($this->error('too_short', $minimumLength), 'warning');
			}
			if (strlen((string)$registerData->password) > 4096) {
				$status = false;
				$messages[] = array($this->error('too_long'), 'error');
			}
			$valueTrim = trim((string)$registerData->password);

			if (strlen((string)$registerData->password) != strlen($valueTrim)) {
				$status = false;
				$messages[] = array($this->error('space'), 'error');
			}

			$checks = array(
				'int' => array($minimumIntegers, '/[0-9]/'),
				'symbol' => array($minimumSymbols, '[\W]'),
				'uppercase' => array($minimumUppercase, '/[A-Z]/'),
				'lowercase' => array($minimumLowercase, '/[a-z]/'),
			);
			foreach($checks as $k => $v) {
				if(empty($v[0]))
					continue;
				$n = preg_match_all($v[1], $registerData->password, $m);
				if($n >= $v[0])
					continue;
				$status = false;
				$messages[] = array($this->error($k, $v[0]), 'warning');
			}
		}

		$data = array();
		$data['name'] = @$registerData->name;
		$data['username'] = @$registerData->username;
		$data['password'] = @$registerData->password;
		$data['password2'] = @$registerData->password2;
		$data['email'] = $registerData->email;

		$_SESSION['hikashop_guest_data'] = $data;

		if(!$status){
			return array( 'status' => false, 'messages' => $messages);
		}

		$addressClass = hikashop_get('class.address');
		$addresses = $addressClass->getByUser($user_id);

		$ret = array(
			'registerData' => $registerData,
			'addressData' => reset($addresses),
			'userData' => $hikaUser
		);

		$newUsertype = $params->get( 'new_usertype' );
		if(!$newUsertype)
			$newUsertype = 2; // "Registered" value for Joomla 2.5 and up

		$userGroupRegistration = $config->get('user_group_registration', '');
		if(!empty($userGroupRegistration)){
			if((int)$userGroupRegistration > 0)
				$newUsertype = (int)$userGroupRegistration;
		}

		if(empty($data['groups']))
			$data['groups'] = array(
				$newUsertype => $newUsertype
			);

		$jconfig = JFactory::getConfig();
		if(HIKASHOP_J30)
			$locale = $jconfig->get('language');
		else
			$locale = $jconfig->getValue('config.language');

		$data['params'] = array(
			'site_language' => $locale,
			'language' => $locale
		);

		$language = JFactory::getLanguage();
		$language->load('lib_joomla', JPATH_SITE);

		$privacy = $this->getPrivacyConsentSettings();
		if($privacy && !@$registerData->privacy) {
			$ret['status'] = false;
			$ret['messages']['PLG_SYSTEM_PRIVACYCONSENT_FIELD_ERROR'] = array(JText::_('PLG_SYSTEM_PRIVACYCONSENT_FIELD_ERROR'), 'error');
			return $ret;
		}

		if( !$user->bind($data, 'usertype') ) {
			$ret['status'] = false;
			$ret['messages'][] = array(JText::_( $user->getError() ), 'error');
			return $ret;
		}

		$user->set('id', 0);

		$jdate = JFactory::getDate();
		if(HIKASHOP_J30)
			$user->set('registerDate', $jdate->toSql());
		else
			$user->set('registerDate', $jdate->toMySQL());

		if( !$user->save() ) {
			$ret['status'] = false;
			$ret['messages'][] = array(JText::_( $user->getError() ), '');
			return $ret;
		}

		$hikaUser->user_cms_id = $user->id;

		if(file_exists(JPATH_ROOT.DS.'components'.DS.'com_comprofiler'.DS.'comprofiler.php')) {
			$this->addAndConfirmUserInCB($hikaUser, $addressData);
		}

		$mailClass = hikashop_get('class.mail');
		$registerData->user_data =& $hikaUser;
		$registerData->address_data =& $addressData;
		$registerData->active = false;

		if(isset($registerData->password)) {
			$registerData->password = preg_replace('/[\x00-\x1F\x7F]/', '', $registerData->password);
		}

		$mail = $mailClass->get('user_account', $registerData);

		$mail->subject = JText::sprintf($mail->subject, @$registerData->name, HIKASHOP_LIVE);
		$mail->dst_email =& $registerData->email;
		$mail->dst_name = '';
		if(!empty($registerData->name))
			$mail->dst_name =& $registerData->name;

		$mailClass->sendMail($mail);
		$mailClass->mail_success;

		if($params->get('mail_to_admin', 0)) {
			$mail = $mailClass->get('user_account_admin_notification', $registerData);
			$mail->subject = JText::sprintf($mail->subject, @$registerData->name, HIKASHOP_LIVE);

			if(empty($mail->dst_email)) {
				$dst = $config->get('user_account_admin_email');
				if(empty($dst))
					$mail->dst_email = array($config->get('from_email'));
				else
					$mail->dst_email = explode(',', $dst);
			}
			$mailClass->sendMail($mail);
		}

		$ret['status'] = true;
		return $ret;
	}

	public function login($user = '', $pass = '') {
		$options = array(
			'remember' => hikaInput::get()->getBool('remember', false),
			'return' => false
		);
		$credentials = array(
			'username' => $user,
			'password' => $pass
		);
		if(empty($user))
			$credentials['username'] = hikaInput::get()->request->getUsername('username', '');

		if(empty($pass))
			$credentials['password'] = hikaInput::get()->request->getRaw('passwd', '');


		$app = JFactory::getApplication();
		try {
			$error = $app->login($credentials, $options);
		} catch (Exception $e) {
			return false;
		}

		if(!HIKASHOP_J40 && JError::isError($error))
			return false;

		$user = JFactory::getUser();
		if($user->guest)
			return false;

		$user_id = $this->getID($user->get('id'));
		if($user_id) {
			$app->setUserState( HIKASHOP_COMPONENT.'.user_id', $user_id);
		}
		return true;
	}

	public function registerLegacy(&$checkout, $page = 'checkout', $redirect = true) {
		$app = JFactory::getApplication();
		$config =& hikashop_config();

		$data = array();

		$simplified = $config->get('simplified_registration', 0);
		$display = $config->get('display_method', 0);
		if(!hikashop_level(1)) $display = 0;

		if($display == 1) {
			$simplified = explode(',', $simplified);
			if($page == 'checkout') {
				$formData = hikaInput::get()->get('data', array(), 'array');
				if(in_array(@$formData['register']['registration_method'], $simplified)) {
					$simplified = $formData['register']['registration_method'];
				} else {
					$simplified = array_shift($simplified);
				}
			} elseif($page == 'user') {
				$simplified = array_shift($simplified);
			}
		}

		$data = array(
			'register' => null,
			'user' => null,
			'address' => null,
			'page' => $page
		);

		if($config->get('affiliate_registration', 0) && hikaInput::get()->getInt('hikashop_affiliate_checkbox', 0))
			$data['affiliate'] = 1;

		$formData = hikaInput::get()->get('data', array(), 'array');
		if(isset($formData['register']))
			$data['register'] = $formData['register'];
		if(isset($formData['user']))
			$data['user'] = $formData['user'];
		if($config->get('address_on_registration', 1) && isset($formData['address']))
			$data['address'] = $formData['address'];

		$ret = $this->register($data, $simplified, array('page' => $page, 'address_type' => 'both'));

		if($ret === false || !isset($ret['status']))
			return false;

		if(isset($ret['registerData']))
			$this->registerData = $ret['registerData'];

		if(!empty($ret['messages'])) {
			foreach($ret['messages'] as $msg) {
				if(is_string($msg))
					$app->enqueueMessage($msg);
				else if(is_array($msg) && count($msg) == 2)
					$app->enqueueMessage($msg[0], $msg[1]);
			}
		}

		if($ret['status'] === false) {
			if(isset($ret['raise_error']) && $ret['raise_error'] !== null)
				$app->enqueueMessage(@$ret['raise_error_msg'], 'error');
			if(isset($ret['raise_warning']) && $ret['raise_warning'] !== null)
				$app->enqueueMessage(@$ret['raise_warning_msg'], 'warning');

			return false;
		}

		if(isset($ret['userActivation']) && $ret['userActivation'] > 0 && $redirect) {
			if(isset($ret['messages']['HIKA_REG_COMPLETE_ACTIVATE']) && $page == 'checkout') {
				$app->enqueueMessage(JText::_('WHEN_CLICKING_ACTIVATION'));
			}

			$lang = JFactory::getLanguage();
			$locale = strtolower(substr($lang->get('tag'), 0, 2));

			global $Itemid;
			$url_itemid = '';
			if(!empty($Itemid))
				$url_itemid = '&Itemid=' . $Itemid;
			$app->redirect(hikashop_completeLink('checkout&task=activate_page&lang='.$locale.$url_itemid,false,true));
		}

		if($simplified != 2 && $redirect && isset($ret['userActivation']) && $ret['userActivation'] == 0) {
			$this->login($ret['registerData']->username, $ret['registerData']->password);
		}
		return true;
	}

	function addAndConfirmUserInCB($newUser, $addressData = null) {

		$query = 'SELECT id FROM #__comprofiler WHERE id='.(int)$newUser->user_cms_id;
		$this->database->setQuery($query);
		$CBID = $this->database->loadResult();
		if($CBID){
			return true;
		}

		if(is_null($addressData)) {
			$addressClass = hikashop_get('class.address');
			$addresses = $addressClass->getByUser($newUser->user_id);
			$addressData = reset($addresses);
		}

		$fields = array(
			'cbactivation' => $this->database->Quote(''),
			'id' => (int)$newUser->user_cms_id,
			'user_id' => (int)$newUser->user_cms_id,
			'approved' => 1,
			'confirmed' => 1
		);

		if(!empty($addressData->address_firstname))
			$fields['firstname'] = $this->database->Quote($addressData->address_firstname);

		if(!empty($addressData->address_middle_name))
			$fields['middlename'] = $this->database->Quote($addressData->address_middle_name);

		if(!empty($addressData->address_lastname))
			$fields['lastname'] = $this->database->Quote($addressData->address_lastname);

		$query = 'INSERT INTO #__comprofiler (' . implode(',', array_keys($fields)) . ') VALUES (' . implode(',', $fields) . ')';
		$this->database->setQuery($query);
		$this->database->execute();

		return true;
	}

	public function getPrivacyConsentSettings($type = 'registration') {
		$group = 'system';
		$name = 'privacyconsent';
		$note_name = 'privacy_note';
		$note_trans_key = 'PLG_SYSTEM_PRIVACYCONSENT_NOTE_FIELD_DEFAULT';
		if($type == 'contact') {
			$group = 'content';
			$name = 'confirmconsent';
			$note_name = 'consentbox_text';
			$note_trans_key = 'PLG_CONTENT_CONFIRMCONSENT_FIELD_NOTE_DEFAULT';
		}

		$pluginsClass = hikashop_get('class.plugins');
		$plugin = $pluginsClass->getByName($group, $name);

		if(empty($plugin) || !$plugin->enabled)
			return false;

		$language = JFactory::getLanguage();
		$language->load('plg_'.$group.'_'.$name, JPATH_ADMINISTRATOR, $language->getTag(), true);

		$type = 'article';
		if(!empty($plugin->params['privacy_type'])) {
			$type = $plugin->params['privacy_type'];
		}

		$privacyArticleId = @$plugin->params['privacy_article'];
		$privacyNote = @$plugin->params[$note_name];
		if(empty($privacyNote))
			$privacyNote = JText::_($note_trans_key);

		$articleClass = hikashop_get('class.article');
		$privacyArticleId = $articleClass->getLanguageArticleId($privacyArticleId);

		$privacyMenuItem = @$plugin->params['privacy_menu_item'];

		if(!empty($privacyMenuItem)) {
			$languageSuffix = '';
			if(HIKASHOP_J40 && Joomla\CMS\Language\Associations::isEnabled()) {
				$privacyAssociated = Joomla\CMS\Language\Associations::getAssociations('com_menus', '#__menu', 'com_menus.item', $privacyMenuItem, 'id', '', '');
				$lang = JFactory::getLanguage();
				$currentLang = $lang->getTag();

				if (isset($privacyAssociated[$currentLang])) {
					$privacyMenuItem = $privacyAssociated[$currentLang]->id;
				}
				if (Joomla\CMS\Language\Multilanguage::isEnabled()) {
					$db = JFactory::getDBO();
					$query = 'SELECT id, language FROM #__menu WHERE id='.(int)$privacyMenuItem;
					$db->setQuery($query);
					$menuItem = $db->loadObject();
					$languageSuffix = '&lang=' . $menuItem->language;
				}
			}
			$privacyMenuItem = JRoute::_('index.php?Itemid=' . (int) $privacyMenuItem . '&tmpl=component' . $languageSuffix);
		}

		return array(
			'id' => $privacyArticleId,
			'text' => $privacyNote,
			'url' => $privacyMenuItem,
			'type' => $type,
		);
	}

	public function addUserConsent(&$user){
		$ip = hikashop_getIP();

		$userAgent = filter_var($_SERVER['HTTP_USER_AGENT'], FILTER_SANITIZE_STRING);

		$userNote = (object) array(
			'user_id' => $user->id,
			'subject' => 'PLG_SYSTEM_PRIVACYCONSENT_SUBJECT',
			'body'    => JText::sprintf('PLG_SYSTEM_PRIVACYCONSENT_BODY', $ip, $userAgent),
			'created' => JFactory::getDate()->toSql(),
		);

		try
		{
			$this->db->insertObject('#__privacy_consents', $userNote);
		}
		catch (Exception $e)
		{
		}

		$message = array(
			'action'      => 'consent',
			'id'          => $user->id,
			'title'       => $user->name,
			'itemlink'    => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
			'userid'      => $user->id,
			'username'    => $user->username,
			'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
		);

		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_actionlogs/models', 'ActionlogsModel');


		$model = JModelLegacy::getInstance('Actionlog', 'ActionlogsModel');
		$model->addLog(array($message), 'PLG_SYSTEM_PRIVACYCONSENT_CONSENT', 'plg_system_privacyconsent', $user->id);
	}

	public function &getNameboxData($typeConfig, &$fullLoad, $mode, $value, $search, $options) {
		$ret = array(
			0 => array(),
			1 => array()
		);

		$sqlJoins = array();
		$sqlFilters = array('juser.block = 0');
		if(!empty($options['filters'])) {
			foreach($options['filters'] as $filter) {
			}
		}

		if(!empty($search)) {
			$searchMap = array('user.user_id', 'juser.name', 'user.user_email');
			if(!HIKASHOP_J30)
				$searchVal = '\'%' . $this->db->getEscaped(HikaStringHelper::strtolower($search), true) . '%\'';
			else
				$searchVal = '\'%' . $this->db->escape(HikaStringHelper::strtolower($search), true) . '%\'';
			$sqlFilters['search'] = '('.implode(' LIKE '.$searchVal.' OR ', $searchMap).' LIKE '.$searchVal.')';
		}

		$sqlSort = 'user.user_id';
		if(!empty($options['sort']) && $options['sort'] == 'name')
			$sqlSort = 'user.user_name';

		$start = 0;
		$max = 30;

		if(isset($options['start']) && (int)$options['start'] > 0)
			$start = (int)$options['start'];

		$query = 'SELECT user.user_id, (CASE WHEN juser.name IS NULL THEN user.user_email ELSE juser.name END) AS name, user.user_email '.
			' FROM ' . hikashop_table('user') . ' AS user '.
			' LEFT JOIN ' . hikashop_table('users', false) . ' AS juser ON user.user_cms_id = juser.id ' . implode(' ', $sqlJoins) .
			' WHERE ('.implode(') AND (', $sqlFilters).') '.
			' ORDER BY '.$sqlSort;
		$this->db->setQuery($query, $start, $max+1);
		$users = $this->db->loadObjectList('user_id');
		if(count($users) > $max) {
			$fullLoad = false;
			array_pop($users);
		}

		if(!empty($value) && !is_array($value) && (int)$value > 0) {
			$value = (int)$value;
			if(isset($users[$value])) {
				$ret[1] = $users[$value];
			} else {
				$query = 'SELECT user.user_id, (CASE WHEN juser.name IS NULL THEN user.user_email ELSE juser.name END) AS name, user.user_email '.
					' FROM ' . hikashop_table('user') . ' AS user '.
					' LEFT JOIN ' . hikashop_table('users', false) . ' AS juser ON user.user_cms_id = juser.id'.
					' WHERE user.user_id = ' . $value;
				$this->db->setQuery($query);
				$ret[1] = $this->db->loadObject();
			}
		} else if(!empty($value) && is_array($value) && (count($value) > 1 || !empty($value[0]))) {
			hikashop_toInteger($value);
			$query = 'SELECT user.user_id, (CASE WHEN juser.name IS NULL THEN user.user_email ELSE juser.name END) AS name, user.user_email '.
				' FROM ' . hikashop_table('user') . ' AS user '.
				' LEFT JOIN ' . hikashop_table('users', false) . ' AS juser ON user.user_cms_id = juser.id'.
				' WHERE user.user_id IN (' . implode(',', $value) . ')';
			$this->db->setQuery($query);

			$ret[1] = $this->db->loadObjectList('user_id');
		}

		if(!empty($users))
			$ret[0] = $users;
		return $ret;
	}
}
