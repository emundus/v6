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
jimport('joomla.plugin.plugin');
class plgSystemHikashopuser extends JPlugin {
	public $hikashopRegistrationInProgress = false;
	public $oldUser = null;
	public $currency = null;
	public $entries = null;
	public $session = null;
	public $cart = null;
	public $wishlist = null;
	public $checkout_fields = null;
	public $checkout_fields_ok = null;
	public function __construct(&$subject, $config) {
		parent::__construct($subject, $config);

		if (version_compare(JVERSION,'4.0','>=') && !(Joomla\CMS\Factory::getApplication() instanceof Joomla\CMS\Application\WebApplication)) return;

		if(!isset($this->params)) {
			$plugin = JPluginHelper::getPlugin('system', 'hikashopuser');
			$this->params = new JRegistry($plugin->params);
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

	}

	public function onAfterProductCreate(&$product) {
		$app = JFactory::getApplication();
		JPluginHelper::importPlugin('finder');

		$isNew = true;
		$context = 'com_hikashop.product';
		$app->triggerEvent('onFinderAfterSave', array($context, $product, $isNew));
	}
	public function onBeforeProductCreate(&$product, &$do) {
		$app = JFactory::getApplication();
		JPluginHelper::importPlugin('finder');

		$isNew = true;
		$context = 'com_hikashop.product';
		$app->triggerEvent('onFinderBeforeSave', array($context, $product, $isNew));
	}
	public function onAfterProductUpdate(&$product) {
		$app = JFactory::getApplication();
		JPluginHelper::importPlugin('finder');

		$isNew = false;
		$context = 'com_hikashop.product';
		$app->triggerEvent('onFinderAfterSave', array($context, $product, $isNew));
	}
	public function onBeforeProductUpdate(&$product) {
		$app = JFactory::getApplication();
		JPluginHelper::importPlugin('finder');

		$isNew = false;
		$context = 'com_hikashop.product';
		$app->triggerEvent('onFinderBeforeSave', array($context, $product, $isNew));
	}
	public function onAfterProductDelete($elements) {
		$app = JFactory::getApplication();
		JPluginHelper::importPlugin('finder');
		$context = 'com_hikashop.product';

		foreach($elements as $element) {
			$app->triggerEvent('onFinderAfterDelete', array($context, $element));
		}
	}
	public function onAfterCategoryUpdate(&$category) {
		$app = JFactory::getApplication();
		JPluginHelper::importPlugin('finder');
		if(!empty($category->old) && isset($category->category_published) && $category->category_published != $category->old->category_published)
			$app->triggerEvent('onFinderCategoryChangeState', array('com_hikashop', array($category->category_id), $category->category_published));
	}

	public function onContentPrepareForm($form, $data) {
		$app = JFactory::getApplication();

		if(version_compare(JVERSION,'4.0','>=') && $app->isClient('site'))
			return true;
		if(version_compare(JVERSION,'4.0','<') && !$app->isAdmin())
			return true;

		if(@$_GET['option'] == 'com_plugins' && @$_GET['view'] == 'plugin' && (@$_GET['layout'] == 'edit' || @$_GET['task'] == 'edit')) {
			$lang = JFactory::getLanguage();
			$lang->load('com_hikashop', JPATH_SITE, null, true);
		}

		if(@$_GET['option'] == 'com_modules' && @$_GET['view'] == 'module' && (@$_GET['layout'] == 'edit' || @$_GET['task'] == 'edit')) {
			$lang = JFactory::getLanguage();
			$lang->load('com_hikashop', JPATH_SITE, null, true);
		}
	}

	public function onBeforeCompileHead(){

		if(version_compare(JVERSION,'3.7','<'))
			return;

		$app = JFactory::getApplication();
		if($app->isClient('administrator')) {
			if(empty($_REQUEST['option']) || $_REQUEST['option'] != 'com_hikashop')
				return;
		}

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
		$app = JFactory::getApplication();

		if(version_compare(JVERSION,'3.0','>='))
			$tmpl = $app->input->getCmd('tmpl', '');
		else
			$tmpl = JRequest::getCmd('tmpl', '');
		if($tmpl != 'component')
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

	public function onAfterUserProfileSaved(&$user, $env) {
		if(empty($user->id) || empty($user->email))
			return;
		if(!defined('DS'))
			define('DS', DIRECTORY_SEPARATOR);
		if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php'))
			return true;
		$userClass = hikashop_get('class.user');
		$hikaUser = new stdClass();
		$hikaUser->user_email = $user->email;
		$hikaUser->user_cms_id = $user->id;
		$userClass->save($hikaUser, true);
	}

	public function onBeforeHikaUserRegistration(&$ret, $input_data, $mode) {
		$this->hikashopRegistrationInProgress = true;
	}

	public function onAfterStoreUser($user, $isnew, $success, $msg) {
		if($success === false || !is_array($user))
			return false;

		if($isnew && $this->hikashopRegistrationInProgress)
			return true;

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
			$element = $fieldsClass->getFilteredInput('user', $oldUser);
			if(!empty($element)) {
				foreach($element as $key => $value) {
					$hikaUser->$key = $value;
				}
			}
		}

		$userClass->save($hikaUser, true);

		$session = JFactory::getSession();
		$session_id = $session->getId();
		if($isnew && strlen(trim($session_id)) > 0 && (int)$user['id'] > 0)
		{
			$db = JFactory::getDBO();

			$query = 'SELECT `user_id`';
			$query .= ' FROM  `#__hikashop_user` ';
			$query .= ' WHERE '.$db->quoteName('user_cms_id').' = '.(int)$user['id'].';';
			$db->setQuery($query);
			$user_hikashop_id = (int)$db->loadResult();

			if(!empty($user_hikashop_id)) {

				$query = 'UPDATE '.$db->quoteName('#__hikashop_cart');
				$query .= ' SET '.$db->quoteName('user_id').' = ' . (int)$user_hikashop_id . '';
				$query .= ' WHERE '.$db->quoteName('user_id').' = 0 ';
				$query .= ' AND '.$db->quoteName('session_id').' = '.$db->quote($session_id).';';
				$db->setQuery($query);
				$db->execute();
			}
		}
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

		if(version_compare(JVERSION,'4.0','>=') && $app->isClient('administrator'))
			return true;
		if(version_compare(JVERSION,'4.0','<') && $app->isAdmin())
			return true;

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

		if(version_compare(JVERSION,'4.0','>=') && $app->isClient('administrator'))
			return true;
		if(version_compare(JVERSION,'4.0','<') && $app->isAdmin())
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

		$app->setUserState(HIKASHOP_COMPONENT.'.user_id', $hika_user_id );

		if($options !== null) {
			$this->moveCarts($hika_user_id);
		} else {
			$db = JFactory::getDBO();
			$query = 'UPDATE #__hikashop_cart SET session_id = \'\' WHERE user_id = '.(int)$hika_user_id.' AND cart_type = \'cart\';';
			$db->setQuery($query);
			$db->execute();
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
		$db->execute();

		$config = hikashop_config();
		if(!$config->get('enable_multicart', 1)) {
			$query = 'SELECT cart_id FROM #__hikashop_cart WHERE user_id = '.(int)$hika_user_id.'  AND session_id != '.$db->Quote($this->session).' AND cart_type = \'cart\';';
			$db->setQuery($query);
			$cart_ids = $db->loadColumn();
			if(count($cart_ids)) {
				$cartClass = hikashop_get('class.cart');
				$cartClass->delete($cart_ids, $hika_user_id);
			}
		}

		$query = 'UPDATE #__hikashop_cart SET user_id = '.(int)$hika_user_id.
			' WHERE session_id = '.$db->Quote($this->session).' AND cart_type = \'cart\';';
		$db->setQuery($query);
		$db->execute();

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

		if(!defined('HIKASHOP_JOOMLA_LOADED'))
			define('HIKASHOP_JOOMLA_LOADED', true);


		if(version_compare(JVERSION,'3.0','>=')) {
			$option = $app->input->getCmd('option', '');
			$view = $app->input->getCmd('view', '');
			$task = $app->input->getCmd('task', '');
			$layout = $app->input->getCmd('layout', '');
		} else {
			$option = JRequest::getCmd('option', '');
			$view = JRequest::getCmd('view', '');
			$task = JRequest::getCmd('task', '');
			$layout = JRequest::getCmd('layout', '');
		}

		if($option == 'com_ajax') {
			if(version_compare(JVERSION,'3.0','>='))
				$group = $app->input->getCmd('group', '');
			else
				$group = JRequest::getCmd('group', '');
			if(in_array($group, array('hikashop', 'hikashopshipping', 'hikashoppayment'))) {
				if(!defined('DS'))
					define('DS', DIRECTORY_SEPARATOR);
				if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php'))
					die('You cannot call plugins of the groups hikashop, hikashoppayment or hikashopshipping without HikaShop on the website.');
			}
		}

		if($option == 'com_finder') {
			$lang = JFactory::getLanguage();
			$lang->load('com_hikashop', JPATH_SITE, null, true);
		}

		if(version_compare(JVERSION,'4.0','>=') && $app->isClient('administrator'))
			return true;
		if(version_compare(JVERSION,'4.0','<') && $app->isAdmin())
			return true;

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

	private function _fixJoomlaMetaTags() {
		$view = hikaInput::get()->getCmd('view');
		$layout = '';
		if(!empty($view) && !hikaInput::get()->getCmd('ctrl')) {
			hikaInput::get()->set('ctrl', $view);
			$layout = hikaInput::get()->getCmd('layout');
			if(!empty($layout)){
				hikaInput::get()->set('task', $layout);
			}
		}
		if(in_array((string)$view, array('product', 'category', '')) && in_array((string)$layout, array('show', 'listing', ''))) {
			$app = JFactory::getApplication();
			$body = $app->getBody();
			if(strpos($body, 'hreflang')) {
				$server = JURI::base();
				$body = str_replace('<link href="'.rtrim($server,'/').$server, '<link href="'.$server, $body);
				$app->setBody($body);
			}
		}
	}

	public function onAfterRender() {
		$app = JFactory::getApplication();

		if(version_compare(JVERSION,'3.0','>=')) {
			$option = $app->input->getCmd('option', '');
			$view = $app->input->getCmd('view', '');
			$task = $app->input->getCmd('task', '');
			$layout = $app->input->getCmd('layout', '');
		} else {
			$option = JRequest::getCmd('option', '');
			$view = JRequest::getCmd('view', '');
			$task = JRequest::getCmd('task', '');
			$layout = JRequest::getCmd('layout', '');
		}

		if(version_compare(JVERSION,'4.0','>=') && $app->isClient('administrator'))
			return true;
		if(version_compare(JVERSION,'4.0','<') && $app->isAdmin())
			return true;

		if($option == 'com_hikashop')
			$this->_fixJoomlaMetaTags();

		if(
			($option != 'com_user' || $view != 'user' || $task != 'edit') && 
			($option != 'com_users' || $view != 'profile' || !in_array($layout, array('edit', 'profile.edit')))
		)
			return;

		$display = $this->params->get('fields_on_user_profile');
		if(is_null($display))
			$display = 1;

		if(empty($display) || $display=='0')
			return;

		$body = '';
		if(class_exists('JResponse'))
			$body = JResponse::getBody();
		$alternate_body = false;
		if(empty($body)){
			$app = JFactory::getApplication();
			$body = $app->getBody();
			$alternate_body = true;
		}
		if(preg_match('#<form[^>]*class=".*form-validate#Uis', $body) === false)
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
			$html = $fieldsClass->display($oneExtraField,@$user->$fieldName,'data[user]['.$fieldName.']',false,' '.$onWhat.'="window.hikashop.toggleField(this.value,\''.$fieldName.'\',\'user\',0);"',false,$extraFields['user'],$user);
			if(HIKASHOP_J40) {
				$html = str_replace('class="inputbox', 'class="form-control', $html);
			}
			$data .= $html;
			if(HIKASHOP_J30)
				$data .= '</div></div>';
			else
				$data .= '</dd>';
		}
		if(HIKASHOP_J30)
			$data .= '</dl></fieldset>';
		else
			$data .= '</fieldset>';

		$body = preg_replace('#(<form[^>]*class=".*form-validate.*"[^>]*>.*</(fieldset|table)>)#Uis','$1'.$data, $body,1);
		if($alternate_body)
			$app->setBody($body);
		else
			JResponse::setBody($body);
	}

	 public function onPreprocessMenuItems($name, &$items, $params = null, $enabled = true) {
		if($name != 'com_menus.administrator.module' )
			return;

	 	$remove = array();
	 	foreach($items as $k => $item) {
	 		switch($item->link) {
				case 'index.php?option=com_hikashop&ctrl=update':
	 				if(!$this->_isAllowed('acl_update_about_view'))
						$remove[] = $k;
					break;
				case 'index.php?option=com_hikashop&ctrl=documentation':
	 				if(!$this->_isAllowed('acl_documentation_view'))
						$remove[] = $k;
					break;
				case 'index.php?option=com_hikashop&ctrl=discount':
	 				if(!$this->_isAllowed('acl_discount_view'))
						$remove[] = $k;
					break;
				case 'index.php?option=com_hikashop&ctrl=config':
	 				if(!$this->_isAllowed('acl_config_view'))
						$remove[] = $k;
					break;
				case 'index.php?option=com_hikashop&ctrl=order&order_type=sale&filter_partner=0':
	 				if(!$this->_isAllowed('acl_order_view'))
						$remove[] = $k;
					break;
				case 'index.php?option=com_hikashop&ctrl=user&filter_partner=0':
	 				if(!$this->_isAllowed('acl_user_view'))
						$remove[] = $k;
					break;
				case 'index.php?option=com_hikashop&ctrl=category&filter_id=product':
	 				if(!$this->_isAllowed('acl_category_view'))
						$remove[] = $k;
					break;
				case 'index.php?option=com_hikashop&ctrl=product':
	 				if(!$this->_isAllowed('acl_product_view'))
						$remove[] = $k;
					break;
				default:
					break;
			}
		}
		if(!count($remove))
			return;

		foreach($remove as $r) {
			unset($items[$r]);
		}
	}

	public function onPrivacyCollectAdminCapabilities() {
		$lang = JFactory::getLanguage();
		$lang->load('com_hikashop', JPATH_SITE, null, true);
		$capabilities = array(
			'HikaShop' => array(
				JText::_('HIKASHOP_PRIVACY_CAPABILITY_IP_ADDRESS'),
				JText::_('HIKASHOP_PRIVACY_CAPABILITY_ADDRESS'),
			),
		);
		return $capabilities;
	}
	private function _config($value, $default = 'all') {
		static $config = null;
		if(!isset($config)) {
			$query = 'SELECT * FROM #__hikashop_config WHERE config_namekey IN(
				\'acl_update_about_view\',
				\'acl_documentation_view\',
				\'acl_discount_view\',
				\'acl_config_view\',
				\'acl_order_view\',
				\'acl_user_view\',
				\'acl_category_view\',
				\'acl_product_view\',
				\'inherit_parent_group_access\'
			)';
			$database = JFactory::getDBO();
			$database->setQuery($query);
			$config = $database->loadObjectList('config_namekey');
		}
		return (isset($config[$value]) ? $config[$value]->config_value : $default);
	}

	private function _isAllowed($acl){
		$allowedGroups = $this->_config($acl);

		if($allowedGroups == 'all') return true;
		if($allowedGroups == 'none') return false;
		$id = null;

		if(!is_array($allowedGroups)) $allowedGroups = explode(',',$allowedGroups);

		jimport('joomla.access.access');
		$my = JFactory::getUser($id);
		$userGroups = JAccess::getGroupsByUser($my->id, (bool)$this->_config('inherit_parent_group_access', false));

		$inter = array_intersect($userGroups,$allowedGroups);
		if(empty($inter)) return false;
		return true;
	}
}
