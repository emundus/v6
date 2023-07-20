<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
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
			'updatecart', 'cart', 'cleancart', 'contact', 'compare', 'waitlist', 'send_email', 'add_waitlist', 'price', 'download', 'filter', 'cartinfo'
		));
	}

	public function authorize($task) {
		return $this->isIn($task, array('display'));
	}

	public function contact() { hikaInput::get()->set('layout', 'contact'); return $this->display(); }
	public function compare() { hikaInput::get()->set('layout', 'compare'); return $this->display(); }
	public function waitlist() { hikaInput::get()->set('layout', 'waitlist'); return $this->display(); }
	public function price() { hikaInput::get()->set('layout', 'option_price'); return $this->display(); }

	public function listing() {
		hikaInput::get()->set('layout', 'listing');

		$tmpl = hikaInput::get()->getCmd('tmpl', '');
		if(in_array($tmpl, array('ajax', 'raw', 'component'))) {
			if(!headers_sent())
				header('X-Robots-Tag: noindex');
			$result = $this->display();

			$filter = hikaInput::get()->getCmd('filter', '');
			if(!$filter)
				exit;
			return $result;
		}
		return $this->display();
	}
	public function filter() {
		hikaInput::get()->set('layout', 'filter');

		if(!headers_sent())
			header('X-Robots-Tag: noindex');
		return $this->display();
	}
	public function send_email() {
		JSession::checkToken('request') || die('Invalid Token');

		$element = new stdClass();
		$formData = hikaInput::get()->get('data', array(), 'array');
		if(empty($formData['contact'])) {
			$formData['contact'] = @$formData['register'];
		}
		if(!empty($formData['contact'])) {
			foreach($formData['contact'] as $column => $value) {
				hikashop_secureField($column);
				$element->$column = strip_tags((string)$value);
			}
		}
		if(empty($formData['register'])) {
			$fieldsClass = hikashop_get('class.field');
			$element = $fieldsClass->getInput('contact', $element);
		}

		$config =& hikashop_config();

		if(empty($element)) {
			if(!empty($formData['contact'])) {
				$element = new stdClass();
				foreach($formData['contact'] as $column => $value) {
					hikashop_secureField($column);
					$element->$column = strip_tags((string)$value);
				}
			}
			hikaInput::get()->set('formData', $element);
			$this->contact();
			return;
		}

		$app = JFactory::getApplication();
		JPluginHelper::importPlugin('hikashop');
		$send = empty($element->product_id) || (int)$config->get('product_contact', 0);


		if(empty($element->email)) {
			$app->enqueueMessage(JText::_('VALID_EMAIL'), 'error');
			$send = false;
		}

		$app->triggerEvent('onBeforeSendContactRequest', array(&$element, &$send));

		jimport('joomla.mail.helper');
		$mailer = JFactory::getMailer();

		if($config->get('product_contact_email_required', 1) && empty($element->email)) {
			$app->enqueueMessage(JText::_('EMAIL_INVALID'), 'error');
			$send = false;
		}
		if(!empty($element->email) && ((method_exists('JMailHelper', 'isEmailAddress') && !JMailHelper::isEmailAddress($element->email)) || !$mailer->validateAddress($element->email))){
			$app->enqueueMessage(JText::_('EMAIL_INVALID'), 'error');
			$send = false;
		}

		if($config->get('product_contact_name_required', 1) && empty($element->name)) {
			$app->enqueueMessage(JText::_('SPECIFY_A_NAME'), 'error');
			$send = false;
		}

		if($config->get('product_contact_altbody_required', 1) && empty($element->altbody)) {
			$app->enqueueMessage(JText::_('PLEASE_FILL_ADDITIONAL_INFO'), 'error');
			$send = false;
		} elseif(!empty($element->altbody)) {
			$element->altbody = strip_tags((string)$element->altbody);
		}

		if(!empty($element->consentcheck) && empty($element->consent)) {
			$app->enqueueMessage(JText::_('PLEASE_AGREE_TO_PRIVACY_POLICY'), 'error');
			$send = false;
		}

		if(!$send) {
			hikaInput::get()->set('formData', $element);
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
				$subject = JText::sprintf('CONTACT_REQUEST_FOR_PRODUCT',strip_tags((string)$product->product_name));
			}
		}

		$send = $config->get('product_contact_send_email', 1);

		$app->triggerEvent('onBeforeSendContactRequestEmail', array(&$element, &$send));

		if($send) {
			$mailClass = hikashop_get('class.mail');
			$infos = new stdClass();
			$infos->element =& $element;
			$infos->product =& $product;
			$mail = $mailClass->get('contact_request', $infos);
			$mail->subject = $subject;
			$mail->from_email = $config->get('from_email');
			$mail->from_name = $config->get('from_name');
			if(!empty($element->email))
				$mail->reply_email = $element->email;
			if(empty($mail->dst_email)) {
				$dst = $config->get('contact_request_email');
				if(empty($dst))
					$mail->dst_email = array($config->get('from_email'));
				else
					$mail->dst_email = explode(',', $dst);
			}
			if($config->get('contact_form_copy_checkbox', 0) && !empty($element->copycheck) && empty($element->copy) && !empty($element->email)) {
				$mail->cc_email = $element->email;
			}
			if(!empty($element->email)) {
				$user_name = '';
				if(!empty($element->name))
					$user_name = $element->name;
				if(HIKASHOP_J30) {
					$mailClass->mailer->addReplyTo($element->email, $user_name);
				} else {
					$mailClass->mailer->addReplyTo(array($element->email, $user_name));
				}
			}
			$status = $mailClass->sendMail($mail);
		} else {
			$status = true;
		}

		if($status) {
			$app->enqueueMessage(JText::_('CONTACT_REQUEST_SENT'));
			if(hikaInput::get()->getString('tmpl', '') == 'component') {
				$doc = JFactory::getDocument();
				$doc->addScriptDeclaration('setTimeout(function(){ window.parent.hikashop.closeBox(); }, 4000);');
				return true;
			}
			if(!empty($product->product_id)) {
				$url_itemid = '';
				global $Itemid;
				if(!empty($Itemid)) {
					$url_itemid = '&Itemid='.(int)$Itemid;
				}

				if(!isset($productClass))
					$productClass = hikashop_get('class.product');
				$productClass->addAlias($product);

				$redirect = $config->get('product_contact_redirect_to_product_page', 1);
				if($redirect) {
					$app->redirect(hikashop_contentLink('product&task=show&cid='.$product->product_id.'&name='.$product->alias.$url_itemid, $product, false, true));
				} else {
					$app->enqueueMessage(JText::sprintf('CLICK_HERE_TO_GO_BACK_TO_PRODUCT',hikashop_contentLink('product&task=show&cid='.$product->product_id.'&name='.$product->alias.$url_itemid, $product)));
				}
			}
		}

		$url = hikaInput::get()->getVar('redirect_url');
		if(!empty($url)) {
			$app->redirect($url);
		} else {
			$this->contact();
		}
	}

	function add_waitlist() {
		JSession::checkToken('request') || die('Invalid Token');

		$element = new stdClass();
		$formData = hikaInput::get()->get('data', array(), 'array');
		foreach($formData['register'] as $column => $value){
			hikashop_secureField($column);
			$element->$column = strip_tags((string)$value);
		}

		$hkUser = hikashop_loadUser(true);
		$app = JFactory::getApplication();
		if(empty($element->email)) {
			if(empty($hkUser->user_email)) {
				$app->enqueueMessage(JText::_('VALID_EMAIL'));
				return $this->waitlist();
			} else {
				$element->email = $hkUser->user_email;
			}
		}
		if(empty($element->name) && !empty($hkUser->name)) {
			$element->name = $hkUser->name;
		}

		jimport('joomla.mail.helper');
		$mailer = JFactory::getMailer();
		if($element->email && (!JMailHelper::isEmailAddress($element->email) || !$mailer->validateAddress($element->email))) {
			$app->enqueueMessage(JText::_('EMAIL_INVALID'), 'error');
			return $this->waitlist();
		}


		if(!empty($element->consentcheck) && empty($element->consent)) {
			$app->enqueueMessage(JText::_('PLEASE_AGREE_TO_PRIVACY_POLICY'), 'error');
			return $this->waitlist();
		}

		$config =& hikashop_config();
		if(!$config->get('product_waitlist', 0)) {
			return $this->waitlist();
		}
		$waitlist_subscribe_limit = $config->get('product_waitlist_sub_limit',10);

		$product_id = 0;
		$itemId = hikaInput::get()->getVar('Itemid');
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
				$lang = JFactory::getLanguage();
				$tag = $lang->getTag();

				$sql = 'INSERT IGNORE INTO '.hikashop_table('waitlist').' (`product_id`,`date`,`email`,`name`,`product_item_id`,`language`) VALUES ('.(int)$product_id.', '.time().', '.$db->quote($email).', '.$db->quote($name).', '.(int)$itemId.', '.$db->quote($tag).');';
				$db->setQuery($sql);
				$db->execute();

				$app->enqueueMessage(JText::_('WAITLIST_SUBSCRIBE'));

				$subject = JText::_('WAITLIST_REQUEST');
				if(!empty($product->product_name)) {
					$subject = JText::sprintf('WAITLIST_REQUEST_FOR_PRODUCT', strip_tags((string)$product->product_name));
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
		$tmpl = hikaInput::get()->getString('tmpl');
		$app->enqueueMessage(JText::sprintf('CLICK_HERE_TO_GO_BACK_TO_PRODUCT',hikashop_contentLink('product&task=show&cid='.$product->product_id.'&name='.$alias.$url_itemid,$product, $tmpl == 'component')));
		$url = hikaInput::get()->getVar('redirect_url');
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

		$url = hikaInput::get()->getVar('return_url', '');
		if(empty($url)) {
			$url = hikaInput::get()->getVar('url', '');
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

		if(!headers_sent())
			header('X-Robots-Tag: noindex');

		$char = hikaInput::get()->getString('characteristic', '');
		if(!empty($char))
			return $this->show();

		$config = hikashop_config();
		$app = JFactory::getApplication();
		$cartClass = hikashop_get('class.cart');

		$product_id = hikaInput::get()->getInt('product_id', 0);
		$module_id = hikaInput::get()->getInt('module_id', 0);
		$reset_cart = hikaInput::get()->getInt('reset_cart', 0);

		$tmpl = hikaInput::get()->getCmd('tmpl', '');

		if(empty($_COOKIE)) {
			if(in_array($tmpl, array('ajax', 'raw', 'component'))) {
				$ret = array(
					'ret' => 0,
					'message' => JText::_('COOKIES_REQUIRED_FOR_OPERATION')
				);
				hikashop_cleanBuffers();
				echo json_encode($ret);
				exit;
			}

			$privacy_plugin_present = false;
			try {
				$db = JFactory::getDBO();
				$db->setQuery("SELECT extension_id FROM #__extensions WHERE `type` = 'plugin' AND `folder` = 'system' AND `element` = 'eprivacy' AND `enabled` = '1'");
				$privacy_plugin_present = $db->loadResult();
			} catch(Exception $e) {
			}

			if($privacy_plugin_present) {
				echo hikashop_display(JText::_('COOKIES_REQUIRED_FOR_OPERATION'), 'error');
				return;
			}
		}

		$cart_type = hikaInput::get()->getString('hikashop_cart_type_'.$product_id.'_'.$module_id, null);
		if(empty($cart_type))
			$cart_type = hikaInput::get()->getString('hikashop_cart_type_'.$module_id, null);
		if(empty($cart_type))
			$cart_type = hikaInput::get()->getString('cart_type', 'cart');

		$cart_id = hikaInput::get()->get->getInt('cart_id', 0);
		if(!empty($cart_id)) {
			$cart = $cartClass->get($cart_id);
			$cart_id = 0;
			if(!empty($cart)) // && $cart->cart_type == $cart_type)
				$cart_id = $cart->cart_id;
		}
		if(empty($cart_id))
			$cart_id = $cartClass->getCurrentCartId($cart_type);

		if($cart_id && $reset_cart) {
			$cart_id = $cartClass->resetCart($cart_id);
		}

		if($cart_id === false && $cart_type == 'wishlist' && hikashop_loadUser() == false) {
			if(in_array($tmpl, array('ajax', 'raw', 'component'))) {
				$ret = array(
					'ret' => 0,
					'message' => JText::_('LOGIN_REQUIRED_FOR_WISHLISTS'),
					'err_wishlist_guest' => 1
				);
				hikashop_cleanBuffers();
				echo json_encode($ret);
				exit;
			}
			$app->enqueueMessage(JText::_('LOGIN_REQUIRED_FOR_WISHLISTS'));
			return $this->legacyWishlistRedirection();
		}

		if($cart_id === false) {
			if(in_array($tmpl, array('ajax', 'raw', 'component'))) {
				echo '{ret:0}';
				exit;
			}
			return false;
		}


		if($cart_type != 'wishlist' && $config->get('catalogue')) {
			if(in_array($tmpl, array('ajax', 'raw', 'component'))) {
				echo '{ret:0}';
				exit;
			}
			return false;
		}

		if($cart_id === 0 && $cart_type != 'cart') {
			$cart = new stdClass();
			$cart->cart_type = $cart_type;
			$status = $cartClass->save($cart);

			if(empty($status) && in_array($tmpl, array('ajax', 'raw', 'component'))) {
				echo '{ret:0}';
				exit;
			}
			if(empty($status))
				return false;

			$cart_id = (int)$status;
		}

		$addTo = hikaInput::get()->getString('add_to', '');
		$cart_type_id = $cart_type.'_id';
		if(!empty($addTo)) {
			$from_id = $cart_id;
			if($addTo == 'cart')
				hikaInput::get()->set('from_id', $cart_id);
			$cart_id = $app->getUserState(HIKASHOP_COMPONENT.'.'.$addTo.'_id', 0);
			$cart_type_id = $addTo.'_id';
			hikaInput::get()->set('cart_type', $addTo);
		} else {
			hikaInput::get()->set('cart_type', $cart_type);
		}
		hikaInput::get()->set($cart_type_id, $cart_id);

		$add = hikaInput::get()->getCmd('add', '');
		$add = empty($add) ? 0 : 1;

		if(empty($product_id))
			$product_id = hikaInput::get()->getCmd('cid', 0);

		$cart_product_id = hikaInput::get()->getInt('cart_product_id', 0);
		$quantity = hikaInput::get()->getInt('quantity', 1);
		$status = null;
		$used_data = null;


		if (!empty($cart_product_id)) {
			$used_data = array('cart_product' => $cart_product_id, 'quantity' => $quantity);
			$status = $cartClass->update($cart_product_id, $quantity, $add, 'item', true, false, $cart_id);
		} elseif (!empty($product_id)) {
			$type = hikaInput::get()->getWord('type', 'product');
			if($type == 'product')
				$product_id = (int)$product_id;
			$used_data = array('product' => $product_id, 'quantity' => $quantity);
			$status = $cartClass->update($product_id, $quantity, $add, $type, true, false, $cart_id);
		} else {
			$formData = hikaInput::get()->get('item', array(), 'array');
			$type = 'item';
			if(empty($formData)) {
				$formData = hikaInput::get()->get('data', array(), 'array');
				$type = 'product';
			}
			if(count($formData)) {
				$used_data = array('form' => $formData, 'type' => $type);
				$status = $cartClass->update($formData, 0, $add, $type, true, false, $cart_id);
			}
		}

		if($status || is_null($status)){
			$coupon = hikaInput::get()->getString('coupon');
			if(!empty($coupon))
				$cartClass->addCoupon($cart_id, $coupon);
		}

		$cart = $cartClass->getFullCart($cart_id);
		if(in_array($tmpl, array('ajax', 'raw', 'component'))) {
			$ret = $this->getAjaxCartData($used_data, $cart, $status);
			hikashop_cleanBuffers();
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

		$checkout = hikaInput::get()->getString('checkout', '');
		if(!empty($checkout)) {
			$this->redirectToCheckout();
			return true;
		}

		if($cart_type == 'wishlist')
			return $this->legacyWishlistRedirection();

		return $this->legacyCartRedirection();
	}

	public function cartinfo() {
		$ret = array();
		$cartClass = hikashop_get('class.cart');
		$cart = $cartClass->getFullCart();
		$ret = $cartClass->getCartProductsInfo($cart);
		hikashop_cleanBuffers();
		echo json_encode($ret);
		exit;
	}

	protected function getAjaxCartData($data, $cart, $status) {
		$cartClass = hikashop_get('class.cart');
		$ret = $cartClass->getCartProductsInfo($cart);
		$ret['ret'] = (int)$status;

		if(!empty($data['type']) && $data['type'] == 'product' && !empty($data['form']) && count($data['form'])) {
			$added = array();
			foreach($data['form'] as $prod => $qty) {
				if($qty)
					$added[] = array('product' => $prod, 'quantity' => $qty);
			}

			if(count($added) == 1) {
				$data = array_merge($data,$added[0]);
			}
		}



		if(!isset($data['product']))
			return $ret;

		$imageHelper = hikashop_get('helper.image');
		$config = hikashop_config();
		$imageSize = (int)$config->get('addtocart_popup_image_size', 50);
		if(!empty($cart->products)) {
			foreach($cart->products as $product) {
				if($product->product_id != $data['product'])
					continue;
				$ret['product_id'] = (int)$product->product_id;
				$ret['cart_product_id'] = (int)$product->cart_product_id;
				$ret['product_name'] = $product->product_name;
				$ret['quantity'] = (int)$product->cart_product_quantity;

				if($imageSize > 0) {
					$image_path = (isset($product->images[0]->file_path) ? $product->images[0]->file_path : '');
					$img = $imageHelper->getThumbnail($image_path, array($imageSize,$imageSize), array('default' => true), true);
					if($img->success)
						$ret['image'] = $img->url;
				}
				break;
			}
		}
		if(!isset($ret['product_name'])) {
			$productClass = hikashop_get('class.product');
			$product = $productClass->getProduct($data['product']);
			if(empty($product->product_name) && !empty($product->parent->product_name))
				$product->product_name = $product->parent->product_name;
			$ret['product_name'] = hikashop_translate($product->product_name);
			$ret['quantity'] = 0;
			$ret['cart_product_id'] = 0;

			$image_path = ((isset($product->parent) && isset($product->parent->images[0]->file_path)) ? $product->parent->images[0]->file_path : '');
			$image_path = (isset($product->images[0]->file_path) ? $product->images[0]->file_path : $image_path);
			$img = $imageHelper->getThumbnail($image_path, array($imageSize,$imageSize), array('default' => true), true);
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

		$url = hikaInput::get()->getVar('return_url','');
		if(empty($url)) {
			$url = urldecode(hikaInput::get()->getVar('url', ''));
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

		$tmpl = hikaInput::get()->getCmd('tmpl', '');
		if($tmpl == 'component' && $config->get('redirect_url_after_add_cart', 'stay_if_cart') != 'checkout') {

			$js ='';
			jimport('joomla.application.module.helper');
			global $Itemid;
			if(isset($Itemid) && empty($Itemid)) {
				$Itemid = null;
				hikaInput::get()->set('Itemid', null);
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

		if(hikaInput::get()->getInt('popup', 0) || (hikaInput::get()->getInt('quantity', 0) > 0 && $config->get('redirect_url_after_add_cart', 'stay_if_cart') == 'ask_user')) {
			$url .= (strpos($url, '?') ? '&' : '?') . 'popup=1';
			$app->setUserState(HIKASHOP_COMPONENT.'.popup', 1);
		}

		if(hikaInput::get()->getInt('hikashop_ajax', 0) == 0) {
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
			$url = hikaInput::get()->getVar('return_url', '');
			if(!empty($url))
				$url = base64_decode(urldecode($url));

			$url = str_replace(array('&popup=1', '?popup=1'), '', $url);

			if($config->get('redirect_url_after_add_cart', 'stay_if_cart') != 'ask_user')
				$app->enqueueMessage(JText::_('LOGIN_REQUIRED_FOR_WISHLISTS'));

			$tmpl = hikaInput::get()->getCmd('tmpl', '');
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
					$url = hikaInput::get()->getVar('return_url','');
					if(!empty($url))
						$url = base64_decode(urldecode($url));
					$url = str_replace(array('&popup=1','?popup=1'), '', $url);

					if(hikaInput::get()->getInt('popup', 0) && empty($_COOKIE['popup']) || hikaInput::get()->getInt('quantity', 0)) {
						$url .= (strpos($url, '?') ? '&' : '?') . 'popup=1';
						$app->setUserState( HIKASHOP_COMPONENT.'.popup', 1);
					}
					hikaInput::get()->set('cart_type', 'wishlist');
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
			if(hikaInput::get()->getInt('new_wishlist_id', 0) != 0 && hikaInput::get()->getInt('delete', 0) == 0)
				$cart_id = hikaInput::get()->getInt('new_wishlist_id', 0);

			$cartClass = hikashop_get('class.cart');
			$cart = $cartClass->get($cart_id, false, 'wishlist');
			if(!empty($cart) && (int)$cart_id != 0) {
				$url = 'cart&task=showcart&cart_type=wishlist&cart_id='.$cart_id.'&Itemid='.$Itemid;
			} else {
				$url = 'cart&task=showcarts&cart_type=wishlist&Itemid='.$Itemid;
			}
			$url = hikashop_completeLink($url, false, true);
		}

		$stay = hikaInput::get()->getInt('stay', 0);
		if($stay == 0) {
			if(hikashop_disallowUrlRedirect($url))
				return false;

			if(hikaInput::get()->getVar('from_form', true)) {
				hikaInput::get()->set('cart_type', 'wishlist');
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
		$module_id = hikaInput::get()->getInt('module_id', 0);
		if(empty($module_id))
			return false;

		$tmpl = hikaInput::get()->getVar('tmpl', '');
		if(!in_array($tmpl, array('component', 'ajax', 'raw')))
			hikaInput::get()->set('tmpl', 'component');

		hikaInput::get()->set('layout', 'cart');
		ob_clean();
		$this->display();
		exit;
	}

	public function download() {
		$file_id = hikaInput::get()->getInt('file_id', 0);
		if(empty($file_id))
			return false;
		$fileClass = hikashop_get('class.file');
		$fileClass->download($file_id);
		return true;
	}
}
