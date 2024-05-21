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
class CartController extends hikashopController {
	public $display = array(
		'show', 'listing', 'cancel', '',
		'share','printcart','add',
		'showcart','showcarts'
	);
	public $modify_views = array('product_edit');
	public $add = array();
	public $modify = array(
		'apply','save',
		'setcurrent',
		'addtocart',
		'sendshare',
		'product_save'
	);
	public $delete = array('remove');
	public $type = 'cart';

	public function __construct($config = array(), $skip = false) {
		parent::__construct($config, $skip);

		$config =& hikashop_config();
		if($config->get('checkout_legacy', 0)) {
			$this->display[] = 'convert';
		} else {
			$this->modify[] = 'convert';
		}

		if(!$skip) {
			$this->registerDefaultTask('show');
		}
	}

	protected function isLogged() {
		$user_id = hikashop_loadUser(false);
		if(!empty($user_id))
			return true;

		$app = JFactory::getApplication();
		$app->enqueueMessage(JText::_('PLEASE_LOGIN_FIRST'));

		global $Itemid;
		$suffix = (!empty($Itemid) ? '&Itemid=' . $Itemid : '');

		$url = 'index.php?option=com_users&view=login';
		$app->redirect(JRoute::_($url . $suffix . '&return='.urlencode(base64_encode(hikashop_currentUrl('', false))), false));
		return false;
	}

	public function add() {
		$cart_type = hikaInput::get()->getCmd('cart_type', '');
		if(!in_array($cart_type, array('cart','wishlist')))
			$cart_type = 'cart';

		$config = hikashop_config();
		if( ($cart_type == 'wishlist' && !$config->get('enable_multiwishlist', 1))
			|| ($cart_type == 'cart' && !$config->get('enable_multicart', 1)) ) {
			return $this->listing();
		}

		$cartClass = hikashop_get('class.cart');
		$cart = new stdClass();
		$cart->cart_type = $cart_type;
		$result = $cartClass->save($cart);

		$app = JFactory::getApplication();
		if($result) {
			$app->enqueueMessage(JText::_('HIKASHOP_SUCC_SAVED'), 'success');
			global $Itemid;
			$suffix = (!empty($Itemid) ? '&Itemid=' . $Itemid : '');
			if($cart_type == 'wishlist')
				$app->redirect(hikashop_completeLink('cart&task=listing&cart_type='.$cart_type.$suffix));
			else
				$app->redirect(hikashop_completeLink('cart&task=show&cart_id='.$result.$suffix));
		} else {
			$app->enqueueMessage(JText::_( 'ERROR_SAVING' ), 'error');
		}
		return $this->listing();
	}
	public function show() {
		hikashop_nocache();

		$cartClass = hikashop_get('class.cart');
		$cart_id = hikashop_getCID('cart_id');
		$app = JFactory::getApplication();

		if(empty($cart_id)) {
			$cart_id = 0;

			$cart_type = hikaInput::get()->getCmd('cart_type', '');

			$menu_item_displaying_the_current_wishlist = false;

			if(empty($cart_type)) {
				$menus = $app->getMenu();
				$menu = $menus->getActive();
				global $Itemid;
				if(empty($menu) && !empty($Itemid)) {
					$menus->setActive($Itemid);
					$menu = $menus->getItem($Itemid);
				}
				if(is_object($menu)) {
					if(HIKASHOP_J30)
						$menuParams = $menu->getParams();
					elseif(is_object($menu->params))
						$menuParams = @$menu->params;
					if($menuParams) {
						$cart_type = $menuParams->get('cart_type');
						$menu_item_displaying_the_current_wishlist = true;
					}
				}
			}

			if(!empty($cart_type) && $cart_type == 'wishlist') {
				$cart_id = $cartClass->getCurrentCartId('wishlist');

				if(empty($cart_id) && $menu_item_displaying_the_current_wishlist) {

					$user = JFactory::getUser();
					if(!$user->guest) {
						$app->enqueueMessage(JText::_('WISHLIST_EMPTY'));
						return parent::show();
					}
					$app->enqueueMessage(JText::_('PLEASE_LOGIN_FIRST'));

					global $Itemid;
					$url = '';
					if(!empty($Itemid))
						$url = '&Itemid='.$Itemid;

					$url = 'index.php?option=com_users&view=login'.$url;
					$app->redirect(JRoute::_($url.'&return='.urlencode(base64_encode(hikashop_currentUrl('', false))), false));
				}

				if(empty($cart_id)) {
					$this->setRedirect(hikashop_completeLink('user'), JText::_('WISHLIST_EMPTY'));
					return true;
				}
				hikaInput::get()->set('cart_id', $cart_id);
			}
		}

		$cart = $cartClass->get($cart_id);

		$config = hikashop_config();

		if(empty($cart) || empty($cart->cart_products)) {
			$cart_id = hikashop_getCID('cart_id');
			if(!empty($cart_id)) {
				$cart = $cartClass->get($cart_id, null, array('skip_user_check' => true));
				$user_id = hikashop_loadUser(false);
				if(!empty($cart) && empty($user_id)) {
					$app->enqueueMessage(JText::_('PLEASE_LOGIN_FIRST'));

					global $Itemid;
					$suffix = (!empty($Itemid) ? '&Itemid=' . $Itemid : '');

					$url = 'index.php?option=com_users&view=login';
					$app->redirect(JRoute::_($url . $suffix . '&return='.urlencode(base64_encode(hikashop_currentUrl('', false))), false));
					return true;
				}
			}
			if($config->get('cart_show_page_redirect_on_empty', 1) && (empty($cart_id) || empty($cart->products))) {
				hikashop_get('helper.checkout');
				$checkoutHelper = hikashopCheckoutHelper::get();
				$override = false;
				if($app->getUserState('com_hikashop.cart_empty_redirect') > time()-1) {
					$app->setUserState('com_hikashop.cart_empty_redirect', 0);
					$override = true;
				} else {
					$app->setUserState('com_hikashop.cart_empty_redirect', time());
				}
				$this->setRedirect($checkoutHelper->getRedirectUrl($override), JText::_('CART_EMPTY'));
				return true;
			}
		}

		$user_id = hikashop_loadUser(false);
		if(!empty($cart)) {
			if($cart->cart_type == 'wishlist' && $cart->user_id != $user_id && $cart->cart_share == 'email') {
				$token = hikaInput::get()->getString('token');
				if(!empty($cart->cart_params->token) && $token != $cart->cart_params->token) {
					$app->enqueueMessage(JText::_('CART_SHARE_INVALID_TOKEN'), 'error');
					return false;
				}
			}
		}

		$app->setUserState('com_hikashop.cart_empty_redirect', 0);

		return parent::show();
	}

	public function product_edit() {
		hikashop_nocache();
		$cartClass = hikashop_get('class.cart');
		$cart_id = hikashop_getCID('cart_id');
		$cart = $cartClass->get($cart_id);

		if(empty($cart) && !empty($cart_id)) {
			$cart = $cartClass->get($cart_id, null, array('skip_user_check' => true));
			$user_id = hikashop_loadUser(false);
			if(!empty($cart) && empty($user_id)) {
				$app = JFactory::getApplication();
				$app->enqueueMessage(JText::_('PLEASE_LOGIN_FIRST'));

				global $Itemid;
				$suffix = (!empty($Itemid) ? '&Itemid=' . $Itemid : '');

				$url = 'index.php?option=com_users&view=login&tmpl=component';
				$app->redirect(JRoute::_($url . $suffix . '&return='.urlencode(base64_encode(hikashop_currentUrl('', false))), false));
			}
		} elseif(empty($cart)) {
			echo JText::_('CART_EMPTY');
			return true;
		}

		$cart_product_id = hikaInput::get()->getInt('cart_product_id', 0);

		if(empty($cart_product_id)) {
			echo 'no cart_product_id provided';
			return true;
		}

		$found = false;
		foreach($cart->cart_products as $p){
			if($p->cart_product_id == $cart_product_id) {
				$found = true;
				break;
			}
		}
		if(!$found) {
			echo 'cart_product_id '.$cart_product_id.' not for the cart '.$cart_id;
			return true;
		}
		hikaInput::get()->set('layout', 'product_edit');
		return parent::display();
	}

	public function product_save() {
		hikashop_nocache();
		$cartClass = hikashop_get('class.cart');
		$cart_id = hikashop_getCID('cart_id');
		$cart = $cartClass->get($cart_id);

		if(empty($cart) && !empty($cart_id)) {
			$cart = $cartClass->get($cart_id, null, array('skip_user_check' => true));
			$user_id = hikashop_loadUser(false);
			if(!empty($cart) && empty($user_id)) {
				echo JText::_('PLEASE_LOGIN_FIRST');
				return true;
			}
		}
		if(empty($cart)) {
			echo JText::_('CART_EMPTY');
			return true;
		}

		$cart_product_id = hikaInput::get()->getInt('cart_product_id', 0);

		if(empty($cart_product_id)) {
			echo 'no cart_product_id provided';
			return true;
		}

		$found = false;
		$product_id = 0;
		$qty = 0;
		foreach($cart->cart_products as $p){
			if($p->cart_product_id == $cart_product_id) {
				$found = $p;
				$product_id = $p->product_id;
				$qty = $p->cart_product_quantity;
				break;
			}
		}
		if(!$found) {
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('THE_PRODUCT_BIENG_MODIFIED_IS_NOT_IN_THE_CURRENT_CART_ANYMORE'),'error');
			return true;
		}

		$optionsData = null;
		if(hikashop_level(1)) {
			$options = hikaInput::get()->get('hikashop_product_option', array(), 'array');
			$options_qty = hikaInput::get()->get('hikashop_product_option_qty', array(), 'array');
			if(!empty($options) && is_array($options)) {
				$optionsData = array();
				foreach($options as $k => $option) {
					if(empty($option) || (int)$option == 0)
						continue;
					if(isset($options_qty[$k]) && empty($options_qty[$k]))
						continue;
					if(!isset($options_qty[$k]) || (int)$options_qty[$k] < 0) $options_qty[$k] = 0;
					$quantity = !empty($options_qty[$k]) ? (int)$options_qty[$k] : $qty;
					$coef = !empty($options_qty[$k]) ? 0 : 1;
					$optionsData[] = array(
						'id' => (int)$option,
						'qty' => $quantity,
						'coef' => $coef
					);
				}
				if(empty($optionsData))
					$optionsData = null;
			}
		}

		$options = array();

		$fieldData = null;
		if(hikashop_level(2)) {
			$product_ids = array($product_id);
			$fields = $cartClass->loadFieldsForProducts($product_ids, 'display:cart_edit=1');
			if(!empty($fields)) {
				$formData = hikaInput::get()->get('data', array(), 'array');
				$cart_products[0]['fields'] = array();
				foreach($fields as $k => $field){
					if($field->field_type == 'customtext')
						continue;
					$fieldData[$field->field_namekey] = @$formData['item'][$field->field_namekey];
				}

				$data = new stdClass();
				$fieldClass = hikashop_get('class.field');
				$buffer = hikashop_copy($fieldData);
				$ok = $fieldClass->checkFieldsData($fields, $buffer, $data, 'item', $found);
				$options['fields_area'] = 'display:cart_edit=1';
				if(!$ok) {
					if(!empty($fieldClass->error_fields)) {
						foreach($fieldClass->error_fields as $error_field){
							if(!empty($error_field->field_options['errormessage']))
								$message = $fieldClass->trans($error_field->field_options['errormessage']);
							else
								$message = JText::sprintf('FIELD_VALID', $fieldClass->trans($error_field->field_realname));
							$cartClass->addMessage($cart, array(
								'msg' => $message,
								'product_id' => $product_id,
								'type' => 'error'
							));
						}
					}
					return $this->product_edit();
				}
			}
		}

		$new_product_id = hikaInput::get()->getInt('new_product_id', 0);
		if(!empty($new_product_id)) {
			$productClass = hikashop_get('class.product');
			$old_product = $productClass->get($product_id);
			$new_product = $productClass->get($new_product_id);
			if($old_product->product_parent_id != $new_product->product_parent_id) {
				echo 'new variant '.$new_product_id.' is not from the same parent product as the one being changed '.$product_id;
				return true;
			}
			$product_id = $new_product_id;
		}

		$cart_products = array(array('id'=>$cart_product_id,'qty'=>0));
		$result = $cartClass->updateProduct($cart_id, $cart_products);

		$cart_products[0]['id'] = $product_id;
		$cart_products[0]['qty'] = $qty;
		if(!empty($fieldData))
			$cart_products[0]['fields'] = $fieldData;
		if(!empty($optionsData))
			$cart_products[0]['options'] = $optionsData;

		$result = $cartClass->addProduct($cart_id, $cart_products, $options);

		if(!$result) {
			return $this->product_edit();
		}

		hikaInput::get()->set('layout', 'product_save');
		return parent::display();
	}

	public function edit() {
		return $this->show();
	}

	public function showcart() {
		return $this->show();
	}

	public function listing() {
		if($this->isLogged() == false)
			return false;

		global $Itemid;
		$app = JFactory::getApplication();
		$config = hikashop_config();

		$cart_type = hikaInput::get()->getCmd('cart_type', '');
		if(!in_array($cart_type, array('cart','wishlist'))) {
			$cart_type = 'cart';

			$menus = $app->getMenu();
			$menu = $menus->getActive();
			if(empty($menu)) {
				if(!empty($Itemid)) {
					$menus->setActive($Itemid);
					$menu = $menus->getItem($Itemid);
				}
			}

			if(is_object($menu) && in_array($menu->link, array('index.php?option=com_hikashop&view=cart&layout=showcarts', 'index.php?option=com_hikashop&view=cart&layout=listing'))) {

				if(HIKASHOP_J30)
					$menu_params = $menu->getParams();
				elseif(is_object($menu->params)) {
					jimport('joomla.html.parameter');
					$menu_params = new HikaParameter( $menu->params );
				}
				if($menu_params) {
					$cart_type = $menu_params->get('cart_type');
				}
			}
			if(!in_array($cart_type, array('cart','wishlist')))
				$cart_type = 'cart';
			hikaInput::get()->set('cart_type', $cart_type);
		}

		if(!hikashop_level(1) && $cart_type == 'wishlist') {
			$app->redirect( hikashop_completeLink('user&task=cpanel&Itemid='.$Itemid, false, true) );
		}

		$multi_cart = (int)$config->get('enable_multicart', 1);
		if($cart_type == 'wishlist')
			$multi_cart = (int)$config->get('enable_multiwishlist', 1);
		if(!$multi_cart) {
			$cartClass = hikashop_get('class.cart');
			$cart_id = $cartClass->getCurrentCartId($cart_type);
			if(!empty($cart_id))
				$app->redirect( hikashop_completeLink('cart&task=show&cart_id='.$cart_id.'&Itemid='.$Itemid, false, true) );
			$app->redirect( hikashop_completeLink('user&task=cpanel&Itemid='.$Itemid, false, true) );
		}

		return parent::listing();
	}

	public function showcarts() {
		return $this->listing();
	}

	public function share() {
		hikashop_nocache();
		hikaInput::get()->set('tmpl','component');
		$cart_id = hikashop_getCID('cart_id');
		if(empty($cart_id)) {
			hikashop_display('No wishlist ID provided');
			return false;
		}
		$cartClass = hikashop_get('class.cart');
		$cart = $cartClass->getFullCart($cart_id);
		if(empty($cart)) {
			hikashop_display('We couldn`\'t find any wishlist to share for the id '.$cart_id);
			return false;
		}
		if($cart->cart_type != 'wishlist') {
			hikashop_display('The Id provided is not a wishlist');
			return false;
		}
		$user_id = hikashop_loadUser(false);
		if($cart->user_id != $user_id) {
			hikashop_display('You are not the owner of the wishlist');
			return false;
		}
		hikaInput::get()->set('layout', 'share');
		return parent::display();
	}

	public function sendshare(){
		$emails = hikaInput::get()->getVar('emails','');
		if(empty($emails)) {
			hikashop_display(JText::_('PLEASE_ENTER_EMAIL_ADDRESSES'), 'error');
			return $this->share();
		}
		hikashop_nocache();
		$emails = preg_split("/[\s,]+/", $emails);
		jimport('joomla.mail.helper');
		$mailer = JFactory::getMailer();
		$ok = true;
		$bcc = array();
		foreach($emails as $k => $email){
			$email = trim($email);
			if(empty($email))
				continue;

			if((method_exists('JMailHelper', 'isEmailAddress') && !JMailHelper::isEmailAddress($email))|| !$mailer->validateAddress($email)){
				hikashop_display(JText::sprintf('THE_EMAIL_ADDRESS_X_IS_INVALID', $email), 'error');
				$ok = false;
			}else{
				$bcc[] = $email;
			}
		}



		if(!$ok)
			return $this->share();

		$cart_id = hikashop_getCID('cart_id');
		if(empty($cart_id)) {
			hikashop_display('No wishlist ID provided');
			return false;
		}

		$cartClass = hikashop_get('class.cart');
		$cart = $cartClass->getFullCart($cart_id);
		$user_id = hikashop_loadUser(false);
		if($cart->user_id != $user_id) {
			hikashop_display('You are not the owner of the wishlist');
			return false;
		}

		if($cart->cart_share == 'nobody'){
			$cart->cart_share = 'email';
			$cartClass->save($cart);
		}

		$mail = $cartClass->loadNotification($cart_id, 'wishlist_share');

		if(!$mail)
			hikashop_display('We couldn`\'t find any wishlist to share for the id '.$cart_id);

		$copy = hikaInput::get()->getInt('copy');
		if($copy){
			$bcc[] = $mail->data->user->user_email;
		}
		$mail->bcc_email = $bcc;
		$mailClass = hikashop_get('class.mail');
		$status = $mailClass->sendMail($mail);
		if(!$status) {
			hikashop_display(JText::_('AN_ERROR_OCCURED_DURING_THE_SENDING_OF_THE_EMAIL'), 'error');
			return $this->share();
		}
		hikashop_display(JText::_('THE_EMAIL_HAS_BEEN_SENT_SUCCESSFULLY'));
	}

	public function printcart() {
		hikashop_nocache();

		$cartClass = hikashop_get('class.cart');
		$cart_id = hikashop_getCID('cart_id');

		$cart = $cartClass->get($cart_id);
		if(empty($cart))
			return false;

		hikaInput::get()->set('tmpl','component');
		hikaInput::get()->set('print_cart', true);

		$js = '
window.hikashop.ready(function(){
	window.focus();
	if(document.all) {
		document.execCommand("print", false, null);
	} else {
		window.print();
	}
	setTimeout(function(){ window.top.hikashop.closeBox();}, 2000);
});
';
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration($js);

		return $this->show();
	}

	public function addtocart() {
		$app = JFactory::getApplication();
		$config = hikashop_config();
		$user_id = hikashop_loadUser(false);
		$cartClass = hikashop_get('class.cart');

		$group = $config->get('group_options', 0);

		$cart_id = hikashop_getCID('cart_id');
		$addto_type = hikaInput::get()->getCmd('addto_type', 'cart');
		$addto_id = hikaInput::get()->getInt('addto_id', 0);
		$request_addto_id = $addto_id;

		$formProducts = hikaInput::get()->get('products', array(), 'array');
		hikashop_toInteger($formProducts);

		$cart = $cartClass->get($cart_id);

		if(empty($cart)) {
			$app->enqueueMessage(JText::_('ERROR'), 'error');
			$app->redirect( hikashop_completeLink('cart&task=listing', false, true) );
			return false;
		}

		$add = $cart->user_id != $user_id || ($request_addto_id === 0 && $addto_type === 'cart');

		$juser = JFactory::getUser();
		if($addto_type == 'wishlist' && (!$config->get('enable_wishlist') || $juser->guest)) {
			if(!$config->get('enable_wishlist'))
				$app->enqueueMessage(JText::_('ERROR'), 'error');
			else
				$app->enqueueMessage(JText::_('LOGIN_REQUIRED_FOR_WISHLISTS'), 'error');

			$app->redirect( hikashop_completeLink('cart&task=show&cid='.$cart_id, false, true) );
			return false;
		}

		$products = array();
		foreach($formProducts as $p) {
			if(!isset($cart->cart_products[$p]))
				continue;

			if($group && !empty($cart->cart_products[$p]->cart_product_option_parent_id))
				continue;

			$products[$p] = $cart->cart_products[$p];
		}

		if(empty($products)) {
			$app->enqueueMessage(JText::_('PLEASE_SELECT_A_PRODUCT_FIRST'), 'error');
			$app->redirect( hikashop_completeLink('cart&task=show&cid='.$cart_id, false, true) );
			return false;
		}

		if(empty($addto_id)) {
			$addto_id = $cartClass->getCurrentCartId($addto_type);
			if($addto_id === false)
				return false;
		}

		if($addto_id <= 0) {
			if(!in_array($addto_type, array('cart','wishlist')))
				return false;
			$newCart = new stdClass();
			$newCart->cart_type = $addto_type;
			$cartClass->save($newCart);
			$addto_id = $newCart->cart_id;
		} else {
			$destCart = $cartClass->get($addto_id);
			if(empty($destCart) || $destCart->cart_type != $addto_type)
				return false;
			if($destCart->cart_type == 'wishlist' && $destCart->user_id != $user_id)
				return false;
		}

		$ret = false;
		if($add) {
			$formData = hikaInput::get()->get('data', array(), 'array');
			foreach($products as $key => &$product) {
				$qty_change = 0;
				if(isset($formData['products'][$key]))
					$qty_change = $formData['products'][$key]['quantity'] - $product->cart_product_quantity;

				foreach($cart->cart_products as $product_in_cart_key => $product_in_cart){
					if($product_in_cart->cart_product_option_parent_id == $key){
						$products[$product_in_cart_key] = $product_in_cart;
						$products[$product_in_cart_key]->cart_product_quantity = $product_in_cart->cart_product_quantity + ($product_in_cart->cart_product_quantity / $product->cart_product_quantity) * $qty_change;
					}
				}
				if($qty_change)
					$product->cart_product_quantity = (int)$formData['products'][$key]['quantity'];
			}

			$product_data = $cartClass->cartProductsToArray($products, array('wishlist' => $cart_id));
			$ret = $cartClass->addProduct($addto_id, $product_data);
		} else {
			$cart_product_ids = array_keys($products);
			$ret = $cartClass->moveTo($cart_id, $cart_product_ids, $addto_id, $addto_type);
		}

		if(empty($ret)) {
			$app->enqueueMessage(JText::_('ERROR'), 'error');
			$app->redirect( hikashop_completeLink('cart&task=listing', false, true) );
			return false;
		}

		$translation = $addto_type == 'wishlist' ? 'PRODUCT_SUCCESSFULLY_ADDED_TO_WISHLIST' : 'PRODUCT_SUCCESSFULLY_ADDED_TO_CART';
		$app->enqueueMessage(JText::_($translation));

		$app->redirect( hikashop_completeLink('cart&task=show&cid='.$ret, false, true) );
		return false;
	}

	public function convert() {
		$config = hikashop_config();
		if(!$config->get('enable_wishlist'))
			return false;

		if($this->isLogged() == false)
			return false;

		$app = JFactory::getApplication();
		$cart_id = hikashop_getCID('cart_id');
		$cartClass = hikashop_get('class.cart');

		$ret = $cartClass->convert($cart_id, false);

		if(!$ret) {
			$app->enqueueMessage(JText::_('ERROR'), 'error');
			$app->redirect( hikashop_completeLink('cart&task=listing', false, true) );
			return false;
		}

		$app->enqueueMessage(JText::_('SUCCESS'));
		$app->redirect( hikashop_completeLink('cart&task=show&cid='.(int)$cart_id, false, true) );
		return true;
	}

	public function setcurrent() {
		if($this->isLogged() == false)
			return false;

		$app = JFactory::getApplication();
		$cart_id = hikashop_getCID('cart_id');
		$cartClass = hikashop_get('class.cart');
		$user_id = hikashop_loadUser(false);

		$cart = $cartClass->get($cart_id);
		$cart_type = @$cart->cart_type;

		if(empty($cart) || $cart->user_id != $user_id || !in_array($cart_type, array('cart','wishlist'))) {
			$app->redirect( hikashop_completeLink('cart&task=listing', false, true) );
			return false;
		}

		$ret = $cartClass->setCurrent($cart_id);

		$app = JFactory::getApplication();
		if(!$ret) {
			$app->enqueueMessage(JText::_('ERROR'), 'error');
		}

		$url_params = ($cart->cart_type == 'cart') ? '' : '&cart_type='.$cart->cart_type;
		$app->redirect( hikashop_completeLink('cart&task=listing'.$url_params, false, true) );
		return false;
	}

	public function remove() {
		$app = JFactory::getApplication();
		$cartClass = hikashop_get('class.cart');
		global $Itemid;
		$url_itemid = '';
		if(!empty($Itemid)) {
			$url_itemid = '&Itemid='.$Itemid;
		}

		$cart_type = '';
		$cids = hikaInput::get()->get('cid', array(), 'array');
		if(empty($cids)) {
			$app->redirect( hikashop_completeLink('cart&task=listing'.$url_itemid, false, true) );
			return false;
		}

		$cid = is_array($cids) ? (int)reset($cids) : (int)$cid;
		$cart = null;
		if(!empty($cid) && $cid > 0) {
			$cart = $cartClass->get( $cid );
			if(!empty($cart) && in_array($cart->cart_type, array('cart','wishlist')))
				$cart_type = '&cart_type='.$cart->cart_type;
			if(empty($cart)) {
				$cart = $cartClass->get($cid, null, array('skip_user_check' => true));
				$user_id = hikashop_loadUser(false);
				if(!empty($cart) && empty($user_id)) {
					$app->enqueueMessage(JText::_('PLEASE_LOGIN_FIRST'));

					global $Itemid;
					$suffix = (!empty($Itemid) ? '&Itemid=' . $Itemid : '');

					$url = 'index.php?option=com_users&view=login';
					$app->redirect(JRoute::_($url . $suffix . '&return='.urlencode(base64_encode(hikashop_currentUrl('', false))), false));
				}
				$app->redirect( hikashop_completeLink('cart&task=listing'.$cart_type.$url_itemid, false, true) );
				return false;
			}
		} else {
			return false;
		}

		$num = $cartClass->delete($cid);
		if($num) {
			$app->enqueueMessage(JText::_('CART_EMPTY'), 'message');

			$user = JFactory::getUser();
			$config = hikashop_config();
			if(empty($user->guest) && $config->get('enable_multicart')) {
				$app->redirect( hikashop_completeLink('cart&task=listing'.$cart_type, false, true) );
			} else {
				hikashop_get('helper.checkout');
				$checkoutHelper = hikashopCheckoutHelper::get();
				$override = false;
				if($app->getUserState('com_hikashop.cart_empty_redirect') > time()-1) {
					$app->setUserState('com_hikashop.cart_empty_redirect', 0);
					$override = true;
				} else {
					$app->setUserState('com_hikashop.cart_empty_redirect', time());
				}
				$app->redirect($checkoutHelper->getRedirectUrl($override));
			}
		}
		return false;
	}
}
