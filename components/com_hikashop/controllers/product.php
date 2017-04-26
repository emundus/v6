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
class productController extends hikashopController {
	public $modify = array();
	public $delete = array();
	public $modify_views = array();

	public function __construct($config = array(), $skip = false) {
		parent::__construct($config, $skip);
		$this->display = array_merge($this->display, array(
			'updatecart', 'cart', 'cleancart', 'contact', 'compare', 'waitlist', 'send_email', 'add_waitlist', 'price', 'download'
		));
	}

	public function authorize($task) {
		return $this->isIn($task, array('display'));
	}

	public function contact() { JRequest::setVar('layout', 'contact'); return $this->display(); }
	public function compare() { JRequest::setVar('layout', 'compare'); return $this->display(); }
	public function waitlist() { JRequest::setVar('layout', 'waitlist'); return $this->display(); }
	public function price() { JRequest::setVar('layout', 'option_price'); return $this->display(); }

	public function listing() {
		JRequest::setVar('layout', 'listing');

		$tmpl = JRequest::getCmd('tmpl', '');
		if($tmpl == 'ajax') {
			$this->display();
			exit;
		}
		return $this->display();
	}

	public function send_email() {
		JRequest::checkToken('request') || jexit('Invalid Token');

		$element = new stdClass();
		$formData = JRequest::getVar('data', array(), '', 'array');
		if(empty($formData['contact'])) {
			$formData['contact'] = @$formData['register'];
			foreach($formData['contact'] as $column => $value) {
				hikashop_secureField($column);
				$element->$column = strip_tags($value);
			}
		} else {
			$fieldsClass = hikashop_get('class.field');
			$element = $fieldsClass->getInput('contact', $element);
		}

		$app = JFactory::getApplication();
		if(empty($element->email)) {
			$app->enqueueMessage(JText::_('VALID_EMAIL'));
			return $this->contact();
		}

		$config =& hikashop_config();

		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('hikashop');
		$send = (int)$config->get('product_contact', 0);
		$dispatcher->trigger('onBeforeSendContactRequest', array(&$element, &$send));

		jimport('joomla.mail.helper');
		if($element->email && method_exists('JMailHelper', 'isEmailAddress') && !JMailHelper::isEmailAddress($element->email)){
			$app->enqueueMessage(JText::_('EMAIL_INVALID'), 'error');
			$send = false;
		}

		if(empty($element->name)) {
			$app->enqueueMessage(JText::_('SPECIFY_A_NAME'), 'error');
			$send = false;
		}

		if(empty($element->altbody)) {
			$app->enqueueMessage(JText::_('PLEASE_FILL_ADDITIONAL_INFO'), 'error');
			$send = false;
		}

		if(!$send) {
			JRequest::setVar('formData', $element);
			$this->contact();
			return;
		}

		$subject = JText::_('CONTACT_REQUEST');
		if(!empty($element->product_id)) {
			$productClass = hikashop_get('class.product');
			$product = $productClass->get((int)$element->product_id);

			if(!empty($product) && $product->product_type == 'variant') {
				$db = JFactory::getDBO();
				$query = 'SELECT * FROM '.hikashop_table('variant').' AS v '.
					' LEFT JOIN '.hikashop_table('characteristic') .' AS c ON v.variant_characteristic_id = c.characteristic_id '.
					' WHERE v.variant_product_id = '.(int)$element->product_id.' ORDER BY v.ordering';
				$db->setQuery($query);
				$product->characteristics = $db->loadObjectList();
				$parentProduct = $productClass->get((int)$product->product_parent_id);
				$productClass->checkVariant($product, $parentProduct);
			}

			if(!empty($product) && !empty($product->product_name)){
				$subject = JText::sprintf('CONTACT_REQUEST_FOR_PRODUCT',strip_tags($product->product_name));
			}
		}

		$mailClass = hikashop_get('class.mail');
		$infos = new stdClass();
		$infos->element =& $element;
		$infos->product =& $product;
		$mail = $mailClass->get('contact_request', $infos);
		$mail->subject = $subject;
		$mail->from_email = $config->get('from_email');
		$mail->from_name = $config->get('from_name');
		$mail->reply_email = $element->email;
		if(empty($mail->dst_email))
			$mail->dst_email = array($config->get('from_email'));
		$status = $mailClass->sendMail($mail);

		if($status) {
			$app->enqueueMessage(JText::_('CONTACT_REQUEST_SENT'));
			if(JRequest::getString('tmpl', '') == 'component') {
				$doc = JFactory::getDocument();
				$doc->addScriptDeclaration('setTimeout(function(){ window.parent.hikashop.closeBox(); }, 4000);');
				return true;
			}
			if(!empty($product->product_id)) {
				$url_itemid = '';
				if(!empty($Itemid)) {
					$url_itemid = '&Itemid='.(int)$Itemid;
				}

				if(!isset($productClass))
					$productClass = hikashop_get('class.product');
				$productClass->addAlias($product);
				$app->enqueueMessage(JText::sprintf('CLICK_HERE_TO_GO_BACK_TO_PRODUCT',hikashop_contentLink('product&task=show&cid='.$product->product_id.'&name='.$product->alias.$url_itemid, $product)));
			}
		}

		$url = JRequest::getVar('redirect_url');
		if(!empty($url)) {
			$app->redirect($url);
		} else {
			$this->contact();
		}
	}

	function add_waitlist() {
		JRequest::checkToken('request') || jexit( 'Invalid Token' );
		$element = new stdClass();
		$formData = JRequest::getVar('data', array(), '', 'array');
		foreach($formData['register'] as $column => $value){
			hikashop_secureField($column);
			$element->$column = strip_tags($value);
		}
		$user = JFactory::getUser();
		$app = JFactory::getApplication();
		if(empty($element->email) && $user->guest) {
			$app->enqueueMessage(JText::_('VALID_EMAIL'));
			return $this->waitlist();
		}

		jimport('joomla.mail.helper');
		if($element->email && !JMailHelper::isEmailAddress($element->email)) {
			$app->enqueueMessage(JText::_('EMAIL_INVALID'), 'error');
			return $this->waitlist();
		}

		$config =& hikashop_config();
		if(!$config->get('product_waitlist', 0)) {
			return $this->waitlist();
		}
		$waitlist_subscribe_limit = $config->get('product_waitlist_sub_limit',10);

		$product_id = 0;
		$itemId = JRequest::getVar('Itemid');
		$url_itemid = '';
		if(!empty($itemId))
			$url_itemid = '&Itemid='.$itemId;
		$alias = '';
		if(!empty($element->product_id)){
			$productClass = hikashop_get('class.product');
			$product = $productClass->get((int)$element->product_id);
			if(!empty($product)){
				if($product->product_type=='variant'){
					$db = JFactory::getDBO();
					$db->setQuery('SELECT * FROM '.hikashop_table('variant').' AS a LEFT JOIN '.hikashop_table('characteristic') .' AS b ON a.variant_characteristic_id=b.characteristic_id WHERE a.variant_product_id='.(int)$element->product_id.' ORDER BY a.ordering');
					$product->characteristics = $db->loadObjectList();
					$parentProduct = $productClass->get((int)$product->product_parent_id);
					$productClass->checkVariant($product,$parentProduct);
				}
				$product_id = (int)$product->product_id;
				$productClass->addAlias($product);
				$alias = $product->alias;
			}
		}
		if( $product_id == 0 ) {
			return $this->waitlist();
		}

		$email = (!empty($element->email)) ? $element->email : '';
		$name = (!empty($element->name)) ? $element->name : '';

		$db = JFactory::getDBO();

		$sql = 'SELECT waitlist_id FROM '.hikashop_table('waitlist').' WHERE email='.$db->quote($email).' AND product_id='.(int)$product_id;
		$db->setQuery($sql);
		$subscription = $db->loadResult();
		if(empty($subscription)) {
			$sql = 'SELECT count(*) FROM '.hikashop_table('waitlist').' WHERE product_id='.(int)$product_id;
			$db->setQuery($sql);
			$subscriptions = $db->loadResult();

			if( $subscriptions < $waitlist_subscribe_limit || $waitlist_subscribe_limit <= 0 ) {
				$sql = 'INSERT IGNORE INTO '.hikashop_table('waitlist').' (`product_id`,`date`,`email`,`name`,`product_item_id`) VALUES ('.(int)$product_id.', '.time().', '.$db->quote($email).', '.$db->quote($name).', '.(int)$itemId.');';
				$db->setQuery($sql);
				$db->query();

				$app->enqueueMessage(JText::_('WAITLIST_SUBSCRIBE'));

				$subject = JText::_('WAITLIST_REQUEST');
				if(!empty($product->product_name)) {
					$subject = JText::sprintf('WAITLIST_REQUEST_FOR_PRODUCT', strip_tags($product->product_name));
				}
				$mailClass = hikashop_get('class.mail');
				$infos = new stdClass();
				$infos->user =& $element;
				$infos->product =& $product;
				$mail = $mailClass->get('waitlist_admin_notification', $infos);
				$mail->subject = $subject;
				$mail->from_email = $config->get('from_email');
				$mail->from_name = $config->get('from_name');
				$mail->reply_email = $element->email;
				if(empty($mail->dst_email))
					$mail->dst_email = array($config->get('from_email'));
				$status = $mailClass->sendMail($mail);
			} else {
				$app->enqueueMessage(JText::_('WAITLIST_FULL'));
			}
		} else {
			$app->enqueueMessage(JText::_('ALREADY_REGISTER_WAITLIST'));
		}
		$app->enqueueMessage(JText::sprintf('CLICK_HERE_TO_GO_BACK_TO_PRODUCT',hikashop_contentLink('product&task=show&cid='.$product->product_id.'&name='.$alias.$url_itemid,$product)));
		$url = JRequest::getVar('redirect_url');
		if(!empty($url)){
			$app->redirect($url);
		}else{
			$this->waitlist();
		}
	}

	public function cleancart() {
		hikashop_nocache();

		$cartClass = hikashop_get('class.cart');
		$cart_id = $cartClass->getCurrentCartId();
		if(!empty($cart_id))
			$cartClass->delete($cart_id);

		$url = JRequest::getVar('return_url', '');
		if(empty($url)) {
			$url = JRequest::getVar('url', '');
			$url = urldecode($url);
		} else {
			$url = base64_decode(urldecode($url));
		}

		if(HIKASHOP_J30){
			$plugin = JPluginHelper::getPlugin('system', 'cache');
			$params = new JRegistry(@$plugin->params);

			$options = array(
				'defaultgroup' => 'page',
				'browsercache' => $params->get('browsercache', false),
				'caching'      => false,
			);

			$cache = JCache::getInstance('page', $options);
			$cache->clean();
		}

		if(empty($url)) {
			echo '<html><head><script type="text/javascript">history.go(-1);</script></head><body></body></html>';
			exit;
		}

		if(strpos($url, 'tmpl=component') !== false || strpos($url, 'tmpl-component') !== false) {
			if(!empty($_SERVER['HTTP_REFERER'])) {
				$app = JFactory::getApplication();
				$app->redirect($_SERVER['HTTP_REFERER']);
			} else {
				echo '<html><head><script type="text/javascript">history.back();</script></head><body></body></html>';
				exit;
			}
		}
		if(hikashop_disallowUrlRedirect($url))
			return false;
		$this->setRedirect($url);
	}

	public function updatecart() {
		hikashop_nocache();

		$char = JRequest::getString('characteristic', '');
		if(!empty($char))
			return $this->show();

		$app = JFactory::getApplication();
		$cartClass = hikashop_get('class.cart');

		$product_id = JRequest::getInt('product_id', 0);
		$module_id = JRequest::getInt('module_id', 0);
		$tmpl = JRequest::getCmd('tmpl', '');

		$cart_type = JRequest::getString('hikashop_cart_type_'.$product_id.'_'.$module_id, null);
		if(empty($cart_type))
			$cart_type = JRequest::getString('hikashop_cart_type_'.$module_id, null);
		if(empty($cart_type))
			$cart_type = JRequest::getString('cart_type', 'cart');

		$cart_id = JRequest::getInt('cart_id', 0, 'GET');
		if(!empty($cart_id)) {
			$cart = $cartClass->get($cart_id);
			$cart_id = 0;
			if(!empty($cart)) // && $cart->cart_type == $cart_type)
				$cart_id = $cart->cart_id;
		}
		if(empty($cart_id))
			$cart_id = $cartClass->getCurrentCartId($cart_type);

		if($cart_id === false && $cart_type == 'wishlist' && hikashop_loadUser() == false) {
			if($tmpl == 'ajax') {
				$ret = array(
					'ret' => 0,
					'message' => JText::_('LOGIN_REQUIRED_FOR_WISHLISTS')
				);
				echo json_encode($ret);
				exit;
			}
			$app->enqueueMessage(JText::_('LOGIN_REQUIRED_FOR_WISHLISTS'));
			return $this->legacyWishlistRedirection();
		}

		if($cart_id === false) {
			if($tmpl == 'ajax') {
				echo '{ret:0}';
				exit;
			}
			return false;
		}

		if($cart_id === 0 && $cart_type != 'cart') {
			$cart = new stdClass();
			$cart->cart_type = $cart_type;
			$status = $cartClass->save($cart);

			if(empty($status) && $tmpl == 'ajax') {
				echo '{ret:0}';
				exit;
			}
			if(empty($status))
				return false;

			$cart_id = (int)$status;
		}

		$addTo = JRequest::getString('add_to', '');
		$cart_type_id = $cart_type.'_id';
		if(!empty($addTo)) {
			$from_id = $cart_id;
			if($addTo == 'cart')
				JRequest::setVar('from_id', $cart_id);
			$cart_id = $app->getUserState(HIKASHOP_COMPONENT.'.'.$addTo.'_id', 0);
			$cart_type_id = $addTo.'_id';
			JRequest::setVar('cart_type', $addTo);
		} else {
			JRequest::setVar('cart_type', $cart_type);
		}
		JRequest::setVar($cart_type_id, $cart_id);

		$add = JRequest::getCmd('add', '');
		$add = empty($add) ? 0 : 1;

		if(empty($product_id))
			$product_id = JRequest::getCmd('cid', 0);

		$cart_product_id = JRequest::getInt('cart_product_id', 0);
		$quantity = JRequest::getInt('quantity', 1);
		$status = null;
		$used_data = null;

		if (!empty($product_id)) {
			$type = JRequest::getWord('type', 'product');
			if($type == 'product')
				$product_id = (int)$product_id;
			$used_data = array('product' => $product_id, 'quantity' => $quantity);
			$status = $cartClass->update($product_id, $quantity, $add, $type, true, false, $cart_id);

		} elseif (!empty($cart_product_id)) {
			$used_data = array('cart_product' => $cart_product_id, 'quantity' => $quantity);
			$status = $cartClass->update($cart_product_id, $quantity, $add, 'item', true, false, $cart_id);

		} else {
			$formData = JRequest::getVar('item', array(), '', 'array');
			$type = 'item';
			if(empty($formData)) {
				$formData = JRequest::getVar('data', array(), '', 'array');
				$type = 'product';
			}
			$used_data = array('form' => $formData, 'type' => $type);
			$status = $cartClass->update($formData, 0, $add, $type, true, false, $cart_id);
		}

		$cart = $cartClass->getFullCart($cart_id);
		if($tmpl == 'ajax') {
			$ret = $this->getAjaxCartData($used_data, $cart, $status);
			echo json_encode($ret);
			exit;
		}

		if(!empty($cart->messages)) {
			foreach($cart->messages as $msg) {
				$app->enqueueMessage($msg['msg'], $msg['type']);
			}
		}

		if($status === false && $tmpl != 'component') {
			if(!empty($_SERVER['HTTP_REFERER'])) {
				if(strpos($_SERVER['HTTP_REFERER'], HIKASHOP_LIVE) === false && preg_match('#^https?://.*#', $_SERVER['HTTP_REFERER']))
					return false;
				$app->redirect( str_replace('&popup=1','',$_SERVER['HTTP_REFERER']));
			} else {
				echo '<html><head><script type="text/javascript">history.back();</script></head><body></body></html>';

				$session = JFactory::getSession();
				$session->set('application.queue', $app->getMessageQueue());
				exit;
			}
		}

		$checkout = JRequest::getString('checkout', '');
		if(!empty($checkout)) {
			$this->redirectToCheckout();
			return true;
		}

		if($cart_type == 'wishlist')
			return $this->legacyWishlistRedirection();

		return $this->legacyCartRedirection();
	}

	protected function getAjaxCartData($data, $cart, $status) {
		$ret = array(
			'ret' => (int)$status
		);
		if(!empty($cart->messages))
			$ret['messages'] = $cart->messages;

		if(empty($cart->cart_products))
			$ret['empty'] = true;

		if(!isset($data['product']))
			return $ret;

		$imageHelper = hikashop_get('helper.image');
		if(!empty($cart->products)) {
			foreach($cart->products as $product) {
				if($product->product_id != $data['product'])
					continue;

				$ret['product_name'] = $product->product_name;
				$ret['quantity'] = (int)$product->cart_product_quantity;

				$image_path = (isset($product->images[0]->file_path) ? $product->images[0]->file_path : '');
				$img = $imageHelper->getThumbnail($image_path, array(50,50), array('default' => true), true);
				if($img->success)
					$ret['image'] = $img->url;
				break;
			}
		}
		if(!isset($ret['product_name'])) {
			$productClass = hikashop_get('class.product');
			$product = $productClass->getProduct($data['product']);
			$ret['product_name'] = $product->product_name;
			$ret['quantity'] = 0;

			$image_path = ((isset($product->parent) && isset($product->parent->images[0]->file_path)) ? $product->parent->images[0]->file_path : '');
			$image_path = (isset($product->images[0]->file_path) ? $product->images[0]->file_path : $image_path);
			$img = $imageHelper->getThumbnail($image_path, array(50,50), array('default' => true), true);
			if($img->success)
				$ret['image'] = $img->url;
		}

		if(!empty($cart->messages)) {
			foreach($cart->messages as $msg) {
				if(empty($msg['product_id']) || (int)$msg['product_id'] != $data['product'])
					continue;
				$ret['message'] = $msg['msg'];
				break;
			}
		}

		return $ret;
	}

	protected function redirectToCheckout() {
		global $Itemid;
		$url = 'checkout';
		if(!empty($Itemid)){
			$url .= '&Itemid=' . $Itemid;
		}
		$url = hikashop_completeLink($url, false, true);
		$this->setRedirect($url);
	}

	protected function legacyCartRedirection() {
		$app = JFactory::getApplication();
		$config = hikashop_config();

		$app->setUserState(HIKASHOP_COMPONENT.'.popup_cart_type','cart');

		$url = JRequest::getVar('return_url','');
		if(empty($url)) {
			$url = urldecode(JRequest::getVar('url', ''));
		} else {
			$url = base64_decode(urldecode($url));
		}

		$url = str_replace(array('&popup=1','?popup=1'), '', $url);

		if(hikashop_disallowUrlRedirect($url))
			$url = '';

		if(empty($url)){
			global $Itemid;
			$url = 'checkout';
			if(!empty($Itemid))
				$url .= '&Itemid=' . $Itemid;
			$url = hikashop_completeLink($url, false, true);
		}

		$tmpl = JRequest::getCmd('tmpl', '');
		if($tmpl == 'component' && $config->get('redirect_url_after_add_cart', 'stay_if_cart') != 'checkout') {

			$js ='';
			jimport('joomla.application.module.helper');
			global $Itemid;
			if(isset($Itemid) && empty($Itemid)) {
				$Itemid = null;
				JRequest::setVar('Itemid', null);
			}

			$module = JModuleHelper::getModule('hikashop_cart', false);
			$params = new HikaParameter( @$module->params );
			if(!empty($module))
				$module_options = $config->get('params_'.$module->id);

			if(empty($module_options))
				$module_options = $config->get('default_params');

			foreach($module_options as $key => $optionElement) {
				$params->set($key, $optionElement);
			}

			if(!empty($module)) {
				foreach(get_object_vars($module) as $k => $v) {
					if(!is_object($v))
						$params->set($k,$v);
				}
				$params->set('from', 'module');
			}
			$params->set('return_url', $url);
			header('Content-Type: text/css; charset=utf-8');
			echo hikashop_getLayout('product', 'cart', $params, $js);
			exit;
		}

		$params = new HikaParameter(@$module->params);

		if(JRequest::getInt('popup', 0) || (JRequest::getInt('quantity', 0) > 0 && $config->get('redirect_url_after_add_cart', 'stay_if_cart') == 'ask_user')) {
			$url .= (strpos($url, '?') ? '&' : '?') . 'popup=1';
			$app->setUserState(HIKASHOP_COMPONENT.'.popup', 1);
		}

		if(JRequest::getInt('hikashop_ajax', 0) == 0) {
			$this->setRedirect($url);
			return false;
		}

		ob_clean();
		if($params->get('from','module') != 'module' || $config->get('redirect_url_after_add_cart', 'stay_if_cart') == 'checkout') {
			echo 'URL|' . $url;
			exit;
		}

		$this->setRedirect($url);
		return false;
	}

	protected function legacyWishlistRedirection() {
		$app = JFactory::getApplication();
		$config = hikashop_config();

		$app->setUserState( HIKASHOP_COMPONENT.'.popup_cart_type', 'wishlist');

		if(hikashop_loadUser() == null) {
			$url = JRequest::getVar('return_url', '');
			if(!empty($url))
				$url = base64_decode(urldecode($url));

			$url = str_replace(array('&popup=1', '?popup=1'), '', $url);

			if($config->get('redirect_url_after_add_cart', 'stay_if_cart') != 'ask_user')
				$app->enqueueMessage(JText::_('LOGIN_REQUIRED_FOR_WISHLISTS'));

			$tmpl = JRequest::getCmd('tmpl', '');
			if($tmpl == 'component') {
				echo 'notLogged';
				exit;
			}

			if(!empty($_SERVER['HTTP_REFERER'])) {
				if(strpos($_SERVER['HTTP_REFERER'], HIKASHOP_LIVE) === false && preg_match('#^https?://.*#', $_SERVER['HTTP_REFERER']))
					return false;

				if($config->get('redirect_url_after_add_cart','stay_if_cart') == 'ask_user')
					$app->enqueueMessage(JText::_('LOGIN_REQUIRED_FOR_WISHLISTS'));
				$app->redirect( str_replace('&popup=1', '', $_SERVER['HTTP_REFERER']));
			}
		} else {
			$url = '';
			$stay = 0;
			$redirectConfig = $config->get('redirect_url_after_add_cart', 'stay_if_cart');
			switch($redirectConfig) {
				case 'ask_user':
					$url = JRequest::getVar('return_url','');
					if(!empty($url))
						$url = base64_decode(urldecode($url));
					$url = str_replace(array('&popup=1','?popup=1'), '', $url);

					if(JRequest::getInt('popup', 0) && empty($_COOKIE['popup']) || JRequest::getInt('quantity', 0)) {
						$url .= (strpos($url, '?') ? '&' : '?') . 'popup=1';
						$app->setUserState( HIKASHOP_COMPONENT.'.popup', 1);
					}
					JRequest::setVar('cart_type', 'wishlist');
					break;
				case 'stay':
					$stay = 1;
					break;
				case 'checkout':
					break;
				case 'stay_if_cart':
				default:
					$module = JModuleHelper::getModule('hikashop_wishlist', false);
					if($module != null)
						$stay = 1;
					break;
			}

			if($redirectConfig != 'checkout') {
				$module = JModuleHelper::getModule('hikashop_wishlist', false);
				$params = new HikaParameter(@$module->params);
				if(!empty($module))
					$module_options = $config->get('params_'.$module->id);
				if(empty($module_options))
					$module_options = $config->get('default_params');

				foreach($module_options as $key => $optionElement) {
					$params->set($key,$optionElement);
				}

				if(!empty($module)) {
					foreach(get_object_vars($module) as $k => $v) {
						if(!is_object($v))
							$params->set($k,$v);
					}
					$params->set('from','module');
				}
				$params->set('return_url', $url);
				$params->set('cart_type', 'wishlist');
				$js = '';
				echo hikashop_getLayout('product', 'cart', $params, $js);
			}
		}

		if(empty($url)) {
			global $Itemid;
			if(isset($from_id))
				$cart_id = $from_id;
			if(JRequest::getInt('new_wishlist_id', 0) != 0 && JRequest::getInt('delete', 0) == 0)
				$cart_id = JRequest::getInt('new_wishlist_id', 0);

			$cartClass = hikashop_get('class.cart');
			$cart = $cartClass->get($cart_id, false, 'wishlist');
			if(!empty($cart) && (int)$cart_id != 0) {
				$url = 'cart&task=showcart&cart_type=wishlist&cart_id='.$cart_id.'&Itemid='.$Itemid;
			} else {
				$url = 'cart&task=showcarts&cart_type=wishlist&Itemid='.$Itemid;
			}
			$url = hikashop_completeLink($url, false, true);
		}

		$stay = JRequest::getInt('stay', 0);
		if($stay == 0) {
			if(hikashop_disallowUrlRedirect($url))
				return false;

			if(JRequest::getVar('from_form', true)) {
				JRequest::setVar('cart_type', 'wishlist');
				$this->setRedirect($url);
				return false;
			}

			ob_clean();
			echo 'URL|'.$url;
			exit;
		}

		echo '<html><head><script type="text/javascript">history.back();</script></head><body></body></html>';
		exit;
	}

	public function cart() {
		$module_id = JRequest::getInt('module_id', 0);
		if(empty($module_id))
			return false;

		$tmpl = JRequest::getVar('tmpl', '');
		if(!in_array($tmpl, array('component', 'ajax')))
			JRequest::setVar('tmpl', 'component');

		JRequest::setVar('layout', 'cart');
		ob_clean();
		$this->display();
		exit;
	}

	public function download() {
		$file_id = JRequest::getInt('file_id', 0);
		if(empty($file_id))
			return false;
		$fileClass = hikashop_get('class.file');
		$fileClass->download($file_id);
		return true;
	}
}
