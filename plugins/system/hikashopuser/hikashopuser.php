<?php
/**
 * @package	HikaShop for Joomla!
 * @version	3.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2018 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
jimport('joomla.plugin.plugin');
class plgSystemHikashopuser extends JPlugin {

	public function __construct(&$subject, $config) {
		parent::__construct($subject, $config);

		if(!isset($this->params)) {
			$plugin = JPluginHelper::getPlugin('system', 'hikashopuser');
			if(version_compare(JVERSION,'2.5','<')) {
				jimport('joomla.html.parameter');
				$this->params = new JParameter($plugin->params);
			} else {
				$this->params = new JRegistry($plugin->params);
			}
		}

		$app = JFactory::getApplication();
		$this->currency = $app->getUserState('com_hikashop.currency_id');
		$this->entries = $app->getUserState('com_hikashop.entries_fields');

		$jsession = JFactory::getSession();
		$this->session = $jsession->getId();

		$this->cart = $app->getUserState('com_hikashop.cart_id');
		$this->wishlist = $app->getUserState('com_hikashop.wishlist_id');
		$this->checkout_fields = $app->getUserState( 'com_hikashop.checkout_fields');
		$this->checkout_fields_ok = $app->getUserState( 'com_hikashop.checkout_fields_ok', 0);

		if($app->isAdmin() && @$_GET['option'] == 'com_plugins' && @$_GET['view'] == 'plugin' && (@$_GET['layout'] == 'edit' || @$_GET['task'] == 'edit')) {
			$lang = JFactory::getLanguage();
			$lang->load('com_hikashop', JPATH_SITE, null, true);
		}
	}

	public function onBeforeCompileHead(){

		if(version_compare(JVERSION,'3.7','<'))
			return;

		$doc = JFactory::getDocument();
		$head = $doc->getHeadData();

		if(empty($head['scripts']))
			return;

		$js_files = array('jquery.js', 'jquery.min.js', 'jquery-noconflict.js', 'jquery.ui.core.js', 'jquery.ui.core.min.js');
		$newScripts = array();
		foreach($head['scripts'] as $file => $data) {
			foreach($js_files as $js_file) {
				if(strpos($file,'media/jui/js/'.$js_file)=== false)
					continue;
				$newScripts[$file] = $data;
			}
		}
		foreach($head['scripts'] as $file => $data){
			if(!isset($newScripts[$file]))
				$newScripts[$file] = $data;
		}
		$head['scripts'] = $newScripts;

		$doc->setHeadData($head);
	}

	public function onContentPrepare($context, &$article, &$params, $limitstart = 0) {
		if($context == 'com_content.article')
			$this->onPrepareContent($article, $params, $limitstart);
	}

	public function onPrepareContent(&$article, &$params, $limitstart) {
		if(JRequest::getString('tmpl') != 'component')
			return true;

		$db = JFactory::getDBO();
		$query = 'SELECT config_value FROM #__hikashop_config WHERE config_namekey = ' . $db->Quote('checkout_terms');
		$db->setQuery($query);
		$terms_article = (int)$db->loadResult();
		if($article->id != $terms_article)
			return true;

		$params->set('show_page_heading',false);
	}

	public function onAfterCartSave(&$cart) {
		if(!HIKASHOP_J30) return;

		$plugin = JPluginHelper::getPlugin('system', 'cache');
		$params = new JRegistry(@$plugin->params);

		$options = array(
			'defaultgroup'	=> 'page',
			'browsercache'	=> $params->get('browsercache', false),
			'caching'		=> false,
		);

		$cache = JCache::getInstance('page', $options);
		$cache->clean();
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
	public function onUserLogin($user, $options) {
		return $this->onLoginUser($user, $options);
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
		if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php'))
			return true;

		$userClass = hikashop_get('class.user');
		$hikaUser = new stdClass();
		$hikaUser->user_email = trim(strip_tags($user['email']));
		$hikaUser->user_cms_id = (int)$user['id'];
		if(!empty($hikaUser->user_cms_id)) {
			$hikaUser->user_id = $userClass->getID($hikaUser->user_cms_id, 'cms');
		}
		if(empty($hikaUser->user_id) && !empty($hikaUser->user_email)) {
			$hikaUser->user_id = $userClass->getID($hikaUser->user_email, 'email');
		}

		$formData = hikaInput::get()->get('data', array(), 'array');

		$in_checkout = !empty($_REQUEST['option']) && $_REQUEST['option'] == 'com_hikashop' && !empty($_REQUEST['ctrl']) && $_REQUEST['option'] == 'checkout';

		if(!empty($formData) && !empty($formData['user']) && !$in_checkout) {
			$display = $this->params->get('fields_on_user_profile');
			if(is_null($display))
				$display = 1;
			if(empty($display) || $display=='0')
				return;
			$oldUser = null;
			$fieldsClass = hikashop_get('class.field');
			$element = $fieldsClass->getInput('user', $oldUser);
			if(!empty($element)) {
				foreach($element as $key => $value) {
					$hikaUser->$key = $value;
				}
			}
		}

		$userClass->save($hikaUser, true);
		return true;
	}

	public function onAfterDeleteUser($user, $success, $msg) {
		if($success === false || !is_array($user))
			return false;

		if(!defined('DS'))
			define('DS', DIRECTORY_SEPARATOR);
		if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php'))
			return true;

		$userClass = hikashop_get('class.user');
		$user_id = $userClass->getID($user['email'],'email');
		if(!empty($user_id)) {
			$userClass->delete($user_id,true);
		}
		return true;
	}

	public function restoreSession(&$user_id, $options) {
		$app = JFactory::getApplication();
		if($app->isAdmin())
			return;

		$currency = $app->getUserState('com_hikashop.currency_id');
		if(empty($currency) && !empty($this->currency))
			$app->setUserState('com_hikashop.currency_id', $this->currency);

		$entries = $app->getUserState('com_hikashop.entries_fields');
		if(empty($entries) && !empty($this->entries))
			$app->setUserState('com_hikashop.entries_fields', $this->entries);

		$checkout_fields_ok = $app->getUserState('com_hikashop.checkout_fields_ok');
		if(empty($checkout_fields_ok) && !empty($this->checkout_fields_ok))
			$app->setUserState('com_hikashop.checkout_fields_ok', $this->checkout_fields_ok);

		$checkout_fields = $app->getUserState('com_hikashop.checkout_fields');
		if(empty($checkout_fields) && !empty($this->checkout_fields))
			$app->setUserState('com_hikashop.checkout_fields', $this->checkout_fields);
		if(!empty($this->checkout_fields)) {
			foreach($this->checkout_fields as $k => $v) {
				if(isset($_REQUEST['data']['order'][$k]))
					continue;
				$_POST['data']['order'][$k] = $_REQUEST['data']['order'][$k] = $v;
			}
		}
	}

	public function onLoginUser($user, $options) {
		$app = JFactory::getApplication();

		if($app->isAdmin())
			return true;

		$user_id = 0;
		if(empty($user['id'])) {
			if(!empty($user['username'])) {
				jimport('joomla.user.helper');
				$instance = new JUser();
				if($id = intval(JUserHelper::getUserId($user['username'])))  {
					$instance->load($id);
				}
				if($instance->get('block') == 0) {
					$user_id = $instance->id;
				}
			}
		} else {
			$user_id = $user['id'];
		}

		$this->restoreSession($user_id, $options);

		if(empty($user_id))
			return true;

		if(!defined('DS'))
			define('DS', DIRECTORY_SEPARATOR);
		if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php'))
			return true;

		$userClass = hikashop_get('class.user');
		$hika_user_id = $userClass->getID($user_id,'cms');
		if(empty($hika_user_id))
			return true;

		if($options !== null)
			$this->moveCarts($hika_user_id);
		else{
			$db = JFactory::getDBO();
			$query = 'UPDATE #__hikashop_cart SET session_id = \'\' WHERE user_id = '.(int)$hika_user_id.' AND cart_type = \'cart\';';
			$db->setQuery($query);
			$db->query();
		}

		$addressClass = hikashop_get('class.address');
		$addresses = $addressClass->getByUser($hika_user_id);
		if(empty($addresses) || !count($addresses))
			return true;

		$address = reset($addresses);
		$field = 'address_country';
		if(!empty($address->address_state)) {
			$field = 'address_state';
		}
		$app->setUserState(HIKASHOP_COMPONENT.'.shipping_address', $address->address_id );
		$app->setUserState(HIKASHOP_COMPONENT.'.billing_address', $address->address_id );

		$zoneClass = hikashop_get('class.zone');
		$zone = $zoneClass->get($address->$field);
		if(!empty($zone)){
			$zone_id = $zone->zone_id;
			$app->setUserState(HIKASHOP_COMPONENT.'.zone_id', $zone->zone_id );
		}
	}

	protected function moveCarts($hika_user_id) {
		if(empty($hika_user_id))
			return true;

		$db = JFactory::getDBO();

		$query = 'SELECT COUNT(*) AS `carts` FROM #__hikashop_cart WHERE session_id = '.$db->Quote($this->session).' AND cart_type = \'cart\';';
		$db->setQuery($query);
		$carts = (int)$db->loadResult();
		if($carts == 0)
			return;

		$query = 'UPDATE #__hikashop_cart SET cart_current = 0 WHERE user_id = '.(int)$hika_user_id.' AND cart_type = \'cart\';';
		$db->setQuery($query);
		$db->query();

		$config = hikashop_config();
		if(!$config->get('enable_multicart', 1)) {
			$query = 'SELECT cart_id FROM #__hikashop_cart WHERE user_id = '.(int)$hika_user_id.' AND cart_type = \'cart\';';
			$db->setQuery($query);
			if(!HIKASHOP_J25) {
				$cart_ids = $db->loadResultArray();
			} else {
				$cart_ids = $db->loadColumn();
			}
			if(count($cart_ids)) {
				$cartClass = hikashop_get('class.cart');
				$cartClass->delete($cart_ids, $hika_user_id);
			}
		}

		$query = 'UPDATE #__hikashop_cart SET user_id = '.(int)$hika_user_id.
			' WHERE session_id = '.$db->Quote($this->session).' AND cart_type = \'cart\';';
		$db->setQuery($query);
		$db->query();

		if(!class_exists('hikashopCartClass'))
			return;
		$cartClass = hikashop_get('class.cart');
		$cartClass->get('reset_cache');

		if(!class_exists('hikashopCheckoutHelper'))
			return;
		$checkoutHelper = hikashopCheckoutHelper::get();
		$checkoutHelper->getCart(true);
	}

	public function onUserLogout($user) {
		return $this->onLogoutUser($user);
	}

	public function onLogoutUser($user) {
		$options = null;
		return $this->onLoginUser($user, $options);
	}

	public function onAfterRoute() {
		$app = JFactory::getApplication();
		if($app->isAdmin())
			return;

		$option = JRequest::getCmd('option', '');
		$view = JRequest::getCmd('view', '');
		$task = JRequest::getCmd('task', '');
		$layout = JRequest::getCmd('layout', '');

		if(($option != 'com_user' || $view != 'user' || $task != 'edit') && ($option != 'com_users' || $view != 'profile' || $layout != 'edit'))
			return;

		$display = $this->params->get('fields_on_user_profile');
		if(is_null($display))
			$display = 1;

		if(empty($display) || $display=='0')
			return;

		if(!defined('DS'))
			define('DS', DIRECTORY_SEPARATOR);
		if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php'))
			return true;

		$user = hikashop_loadUser(true);
		$fieldsClass = hikashop_get('class.field');
		$extraFields = array(
			'user' => $fieldsClass->getFields('frontcomp',$user,'user')
		);
		if(empty($extraFields['user']))
			return;

		$null = array();
		$fieldsClass->addJS($null,$null,$null);
		$fieldsClass->jsToggle($extraFields['user'],$user,0);
		$requiredFields = array();
		$validMessages = array();
		$values = array('user' => $user);
		$fieldsClass->checkFieldsForJS($extraFields, $requiredFields, $validMessages, $values);
		$fieldsClass->addJS($requiredFields, $validMessages, array('user'));

		foreach($extraFields['user'] as $fieldName => $oneExtraField) {
			$fieldsClass->display($oneExtraField, @$user->$fieldName, 'data[user]['.$fieldName.']', false, '',false, $extraFields['user'], $user);
		}
	}

	public function onAfterRender() {
		$app = JFactory::getApplication();
		if($app->isAdmin())
			return;

		$option = JRequest::getCmd('option', '');
		$view = JRequest::getCmd('view', '');
		$task = JRequest::getCmd('task', '');
		$layout = JRequest::getCmd('layout', '');

		if(($option != 'com_user' || $view != 'user' || $task != 'edit') && ($option != 'com_users' || $view != 'profile' || $layout != 'edit'))
			return;

		$display = $this->params->get('fields_on_user_profile');
		if(is_null($display))
			$display = 1;

		if(empty($display) || $display=='0')
			return;

		$body = JResponse::getBody();
		$alternate_body = false;
		if(empty($body)){
			$app = JFactory::getApplication();
			$body = $app->getBody();
			$alternate_body = true;
		}
		if(strpos($body, 'class="form-validate') === false)
			return;

		if(!defined('DS'))
			define('DS', DIRECTORY_SEPARATOR);
		if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php'))
			return true;

		$user = hikashop_loadUser(true);
		$fieldsClass = hikashop_get('class.field');
		$extraFields = array(
			'user' => $fieldsClass->getFields('frontcomp',$user,'user')
		);
		if(empty($extraFields['user']))
			return;

		$null = array();
		$fieldsClass->addJS($null,$null,$null);
		$fieldsClass->jsToggle($extraFields['user'],$user,0);
		$requiredFields = array();
		$validMessages = array();
		$values = array('user' => $user);
		$fieldsClass->checkFieldsForJS($extraFields, $requiredFields, $validMessages, $values);
		$fieldsClass->addJS($requiredFields, $validMessages, array('user'));

		$data = '';
		if(version_compare(JVERSION,'1.6.0','<')) {
			$data .= '<style type="text/css">'."\r\n".
					'fieldset.hikashop_user_edit { border: 1px solid rgb(204, 204, 204); margin: 10px 0 15px; padding: 0px 10px 0px 10px; }'."\r\n".
					'.hikashop_user_edit legend { font-size: 1em; font-weight: bold; }'."\r\n".
					'.hikashop_user_edit dt { padding: 5px 5px 5px 0px; width: 13em; clear:left; float:left; }'."\r\n".
					'.hikashop_user_edit dd { margin-left: 14em; }'."\r\n".
					'</style>';
		}

		if(HIKASHOP_J30)
			$data .= '<fieldset class="hikashop_user_edit"><legend>'.JText::_('HIKASHOP_USER_DETAILS').'</legend><dl>';
		else
			$data .= '<fieldset class="hikashop_user_edit"><legend>'.JText::_('HIKASHOP_USER_DETAILS').'</legend>';

		foreach($extraFields['user'] as $fieldName => $oneExtraField) {
			if(HIKASHOP_J30)
				$data .= '<div class="control-group hikashop_registration_' . $fieldName. '_line" id="hikashop_user_' . $fieldName. '"><div class="control-label"><label>'.$fieldsClass->getFieldName($oneExtraField).'</label></div><div class="controls">';
			else
				$data .= '<dt><label>'.$fieldsClass->getFieldName($oneExtraField).'</label></dt><dd class="hikashop_registration_' . $fieldName. '_line" id="hikashop_user_' . $fieldName. '">';

			$onWhat='onchange';
			if($oneExtraField->field_type=='radio')
				$onWhat='onclick';
			$data .= $fieldsClass->display($oneExtraField,@$user->$fieldName,'data[user]['.$fieldName.']',false,' '.$onWhat.'="hikashopToggleFields(this.value,\''.$fieldName.'\',\'user\',0);"',false,$extraFields['user'],$user);

			if(HIKASHOP_J30)
				$data .= '</div></div>';
			else
				$data .= '</dd>';
		}
		if(HIKASHOP_J30)
			$data .= '</dl></fieldset>';
		else
			$data .= '</fieldset>';

		$body = preg_replace('#(<form[^>]*class="form-validate.*"[^>]*>.*</(fieldset|table)>)#Uis','$1'.$data, $body,1);
		if($alternate_body)
			$app->setBody($body);
		else
			JResponse::setBody($body);
	}
}
