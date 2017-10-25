<?php
/**
 * @package	HikaShop for Joomla!
 * @version	3.2.1
 * @author	hikashop.com
 * @copyright	(C) 2010-2017 HIKARI SOFTWARE. All rights reserved.
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
			if(empty($element->user_created_ip))
				$element->user_created_ip = hikashop_getIP();

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

			if($app->isAdmin()) {
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
		$dispatcher = JDispatcher::getInstance();
		$do = true;
		if($new) {
			$dispatcher->trigger( 'onBeforeUserCreate', array( & $element, & $do) );
		} else {
			$dispatcher->trigger( 'onBeforeUserUpdate', array( & $element, & $do) );
		}

		if(!$do)
			return false;

		$element->user_id = parent::save($element);

		if(empty($element->user_id))
			return $element->user_id;

		if($new) {
			$dispatcher->trigger( 'onAfterUserCreate', array( & $element ) );
		} else {
			$dispatcher->trigger( 'onAfterUserUpdate', array( & $element ) );
		}

		if($element->user_id == hikashop_loadUser()) {
			hikashop_loadUser(null,true);
			$this->get(false);
		}

		if($new) {
			return $element->user_id;
		}

		if(!$skipJoomla && !empty($element->user_email)){
			if(empty($element->user_cms_id)){
				$userData = $this->get($element->user_id);
				$element->user_cms_id = $userData->user_cms_id;
			}
			$user = JFactory::getUser($element->user_cms_id);
			if(!empty($user) && $element->user_email!=$user->email){
				$user->email = $element->user_email;
				$user->save();
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
		$this->database->query();
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

		$newDefaultId = hikaInput::get()->getInt('address_default', 0);
		if($newDefaultId) {
			$addressClass = hikashop_get('class.address');
			$oldData = $addressClass->get($newDefaultId);
			if(!empty($oldData)) {
				$user_id = hikashop_getCID('user_id');
				if($user_id == $oldData->address_user_id) {
					$oldData->address_default = 1;
					$addressClass->save($oldData);
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
			JArrayHelper::toInteger($elements);
		}

		JPluginHelper::importPlugin( 'hikashop' );
		$dispatcher = JDispatcher::getInstance();
		$do = true;
		$dispatcher->trigger('onBeforeUserDelete', array( & $elements, & $do));

		if(!$do) {
			return false;
		}

		$app = JFactory::getApplication();
		$addressClass = hikashop_get('class.address');

		foreach($elements as $el) {
			$query = 'SELECT count(*) FROM '.hikashop_table('order').' WHERE order_user_id='.$el;
			$this->database->setQuery($query);
			$hasOrders = $this->database->loadResult();

			if(empty($hasOrders)) {
				$result = parent::delete($el);
				if($result){
					$addresses = $addressClass->loadUserAddresses($el);
					foreach($addresses as $id => $data) {
						$addressClass->delete($id);
					}
				}
				continue;
			}

			if($app->isAdmin()) {
				$data = $this->get($el);
				$app->enqueueMessage('The user with the email address "'.$data->user_email.'" could not be deleted in HikaShop because he has orders attached to him. If you want to delete this user in HikaShop as well, you first need to delete his orders.');
				$result = false;
			}
			if($fromCMS) {
				$query = 'UPDATE '.hikashop_table('user').' SET user_cms_id=0 WHERE user_id IN ('.implode(',',$elements).')';
				$this->database->setQuery($query);
				$result = $this->database->query();
			}
		}

		if($result) {
			$dispatcher->trigger( 'onAfterUserDelete', array( & $elements ) );
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
		if(bccomp($user->user_params->user_partner_click_fee,0,5)){
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
		if(bccomp($user->user_params->user_partner_lead_fee,0,5)){
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
		if(bccomp($user->user_params->user_partner_percent_fee,0,5) || bccomp($user->user_params->user_partner_flat_fee,0,5)) {
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

		if(!HIKASHOP_J16) {
			if(empty($my->id))
				return array(29);
			return array($my->gid);
		}

		jimport('joomla.access.access');
		$config =& hikashop_config();
		$userGroups = JAccess::getGroupsByUser($my->id, (bool)$config->get('inherit_parent_group_access')); //$my->authorisedLevels();
		return $userGroups;
	}

	public function register($input_data, $mode, $options = array()) {
		$config = hikashop_config();

		$user = clone(JFactory::getUser());
		$authorize = JFactory::getACL();

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
		$userData = $fieldClass->getInput('user', $old, 'msg', $input_data['user']);
		$addressData = null;
		if($input_data['address'] !== null)
			$addressData = $fieldClass->getInput('address', $old, 'msg', $input_data['address']);

		$status = true;
		$messages = array();

		if($registerData === false || $addressData === false || $userData === false) {
			$messages = $fieldClass->messages;
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
			if(empty($registerData->email) || (method_exists('JMailHelper', 'isEmailAddress') && !JMailHelper::isEmailAddress($registerData->email))){
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

					$language = JFactory::getLanguage();
					$language->load('com_users', JPATH_SITE, $language->getTag(), true);

					if(!empty($minimumLength) && strlen((string)$registerData->password) < $minimumLength) {
						$status = false;
						$messages[] = array(JText::plural('COM_USERS_MSG_PASSWORD_TOO_SHORT_N', $minimumLength), 'warning');
					}

					$checks = array(
						'COM_USERS_MSG_NOT_ENOUGH_INTEGERS_N' => array($minimumIntegers, '/[0-9]/'),
						'COM_USERS_MSG_NOT_ENOUGH_SYMBOLS_N' => array($minimumSymbols, '[\W]'),
						'COM_USERS_MSG_NOT_ENOUGH_UPPERCASE_LETTERS_N' => array($minimumUppercase, '/[A-Z]/'),
					);
					foreach($checks as $k => $v) {
						if(empty($v[0]))
							continue;
						$n = preg_match_all($v[1], $registerData->password, $m);
						if($n >= $v[0])
							continue;
						$status = false;
						$messages[] = array(JText::plural($k, $v[0]), 'warning');
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
			'addressData' => &$addressData
		);


		if(!empty($addressData->address_vat)) {
			$vatHelper = hikashop_get('helper.vat');
			if(!$vatHelper->isValid($addressData)) {
				$ret['status'] = false;
				$ret['messages']['VAT_NUMBER_NOT_VALID'] = array(JText::_('VAT_NUMBER_NOT_VALID'), 'warning');
				return $ret;
			}
		}

		if($config->get('affiliate_registration', 0) && !empty($input_data['affiliate'])) {
			$userData->user_partner_activated = 1;
			$registerData->user_partner_activated = 1;
		}

		JPluginHelper::importPlugin('hikashop');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onBeforeHikaUserRegistration', array(&$ret, $input_data, $mode));

		if($ret['status'] == false) {
			if(empty($ret['messages'])) {
				$ret['messages']['general'] = array(JText::_('REGISTRATION_NOT_ALLOWED'), 'error');
			}
			return $ret;
		}

		if($mode != 2) {

			$newUsertype = $params->get( 'new_usertype' );
			if(!$newUsertype)
				$newUsertype = (!HIKASHOP_J16) ? 'Registered' : 2;

			$userGroupRegistration = $config->get('user_group_registration', '');
			if(HIKASHOP_J16 && !empty($userGroupRegistration)){
				if(!is_numeric($userGroupRegistration)){
					$fieldId = substr($userGroupRegistration,1);
					$field = $fieldClass->get($fieldId);
					if(in_array($field->field_table, array('user','address'))){
						$variable = $field->field_table.'Data';
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

			if(HIKASHOP_J16 && empty($data['groups']))
				$data['groups'] = array(
					$newUsertype => $newUsertype
				);

			if(HIKASHOP_J25) {
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
			}

			if( !$user->bind($data, 'usertype') ) {
				$ret['status'] = false;
				$ret['messages'][] = array(JText::_( $user->getError() ), 'error');
				return $ret;
			}

			$user->set('id', 0);
			if(!HIKASHOP_J16) {
				$user->set('usertype', $newUsertype);
				$user->set('gid', $authorize->get_group_id('', $newUsertype, 'ARO'));
			}

			$jdate = JFactory::getDate();
			if(HIKASHOP_J30)
				$user->set('registerDate', $jdate->toSql());
			else
				$user->set('registerDate', $jdate->toMySQL());

			$useractivation = $params->get('useractivation');
			if($useractivation > 0) {
				jimport('joomla.user.helper');
				if(HIKASHOP_J30)
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
			$newUser = $this->get($user->id, 'cms');

			if(!empty($newUser))
				$userData->user_id = $newUser->user_id;
			else if(!empty($user->id))
				$userData->user_cms_id = $user->id;
			else
				$userData->user_email = $registerData->email;

			$ret['user_id'] = $this->save($userData);

		} else if($mode == 2) {
			$userData->user_email = $registerData->email;

			$query = 'SELECT * FROM '.hikashop_table('user').
					' WHERE user_email = '.$this->database->Quote($userData->user_email);
			$this->database->setQuery($query);
			$userInDB = $this->database->loadObject();

			if(@$userInDB->user_cms_id) {
				$ret['status'] = false;
				$ret['messages'][] = array(JText::_('EMAIL_ADDRESS_ALREADY_USED'), '');
				return $ret;
			}

			$ret['user_id'] = (isset($userInDB->user_id) ? (int)$userInDB->user_id : 0);

			if(!empty($ret['user_id'])) {
				$userInDB->user_created_ip = hikashop_getIP();
				$this->save($userInDB);
			} else {
				$ret['user_id'] = $this->save($userData);
			}

			$query = 'UPDATE '.hikashop_table('address').' AS hk_addr '.
					' SET hk_addr.address_published = 0 '.
					' WHERE hk_addr.address_user_id='.(int)$ret['user_id'].' AND hk_addr.address_published = 1';
			$this->database->setQuery($query);
			$this->database->query();

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

			$registerData->user_id = $ret['user_id'];
			if(!empty($addressData)) {
				$addressData->address_user_id = $ret['user_id'];
				$addressClass = hikashop_get('class.address');
				$ret['address_id'] = $addressClass->save($addressData);
			}
		}

		$send_email = ($mode != 2);
		$dispatcher->trigger('onAfterHikaUserRegistration', array(&$ret, $input_data, $mode, &$send_email));

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

	public function registerGuest($user_id, $registerData){
		$authorize = JFactory::getACL();
		$user = clone(JFactory::getUser());

		jimport('joomla.application.component.helper');
		$params = JComponentHelper::getParams('com_users');

		$config = hikashop_config();

		$hikaUser = $this->get($user_id);

		$status = true;
		$messages = array();

		if(empty($hikaUser)){
			$status = false;
			$messages['invalid_user'] = array(JText::_('INVALID_USER'), 'error');
		}

		if(empty($registerData->name)){
			$status = false;
			$messages['register_name'] = array(JText::sprintf('PLEASE_FILL_THE_FIELD', JText::_('HIKA_NAME')), 'error');
		}

		if(empty($registerData->username)) {
			$status = false;
			$messages['register_username'] = array(JText::sprintf('PLEASE_FILL_THE_FIELD', JText::_('HIKA_USERNAME')), 'error');
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

			$language = JFactory::getLanguage();
			$language->load('com_users', JPATH_SITE, $language->getTag(), true);

			if(!empty($minimumLength) && strlen((string)$registerData->password) < $minimumLength) {
				$status = false;
				$messages[] = array(JText::plural('COM_USERS_MSG_PASSWORD_TOO_SHORT_N', $minimumLength), 'warning');
			}

			$checks = array(
				'COM_USERS_MSG_NOT_ENOUGH_INTEGERS_N' => array($minimumIntegers, '/[0-9]/'),
				'COM_USERS_MSG_NOT_ENOUGH_SYMBOLS_N' => array($minimumSymbols, '[\W]'),
				'COM_USERS_MSG_NOT_ENOUGH_UPPERCASE_LETTERS_N' => array($minimumUppercase, '/[A-Z]/'),
			);
			foreach($checks as $k => $v) {
				if(empty($v[0]))
					continue;
				$n = preg_match_all($v[1], $registerData->password, $m);
				if($n >= $v[0])
					continue;
				$status = false;
				$messages[] = array(JText::plural($k, $v[0]), 'warning');
			}
		}

		$data = array();
		$data['name'] = @$registerData->name;
		$data['username'] = @$registerData->username;
		$data['password'] = @$registerData->password;
		$data['password2'] = @$registerData->password2;
		$data['email'] = $registerData->email = $hikaUser->user_email;

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
			$newUsertype = (!HIKASHOP_J16) ? 'Registered' : 2;

		$userGroupRegistration = $config->get('user_group_registration', '');
		if(HIKASHOP_J16 && !empty($userGroupRegistration)){
			if((int)$userGroupRegistration > 0)
				$newUsertype = (int)$userGroupRegistration;
		}

		if(HIKASHOP_J16 && empty($data['groups']))
			$data['groups'] = array(
				$newUsertype => $newUsertype
			);

		if(HIKASHOP_J25) {
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
		}

		if( !$user->bind($data, 'usertype') ) {
			$ret['status'] = false;
			$ret['messages'][] = array(JText::_( $user->getError() ), 'error');
			return $ret;
		}

		$user->set('id', 0);
		if(!HIKASHOP_J16) {
			$user->set('usertype', $newUsertype);
			$user->set('gid', $authorize->get_group_id('', $newUsertype, 'ARO'));
		}

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
			if(empty($mail->dst_email))
				$mail->dst_email = explode(',', $config->get('from_email'));
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
		$error = $app->login($credentials, $options);

		$user = JFactory::getUser();

		if(JError::isError($error) || $user->guest)
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

		$ret = $this->register($data, $simplified, array('page' => $page));

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
				JError::raiseError($ret['raise_error'], @$ret['raise_error_msg']);
			if(isset($ret['raise_warning']) && $ret['raise_warning'] !== null)
				JError::raiseWarning($ret['raise_warning'], @$ret['raise_warning_msg']);

			return false;
		}

		if(isset($ret['userActivation']) && $ret['userActivation'] > 0 && $redirect) {
			if(isset($ret['messages']['HIKA_REG_COMPLETE_ACTIVATE']) && $page == 'checkout') {
				$app->enqueueMessage(JText::_('WHEN_CLICKING_ACTIVATION'));
			}

			$lang = JFactory::getLanguage();
			$locale = strtolower(substr($lang->get('tag'), 0, 2));
			$app->redirect(hikashop_completeLink('checkout&task=activate_page&lang='.$locale,false,true));
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
		$this->database->query();

		return true;
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
				$searchVal = '\'%' . $this->db->getEscaped(JString::strtolower($search), true) . '%\'';
			else
				$searchVal = '\'%' . $this->db->escape(JString::strtolower($search), true) . '%\'';
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
		} else if(!empty($value) && is_array($value)) {

			JArrayHelper::toInteger($value);
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
