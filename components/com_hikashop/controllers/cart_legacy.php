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
class CartController extends hikashopController {
	var $modify_views = array();
	var $add = array();
	var $modify = array();
	var $delete = array();

	public function __construct($config = array(), $skip = false) {
		parent::__construct($config, $skip);

		if(!$skip) {
			$this->registerDefaultTask('show');
		}

		$this->display[]='convert';
		$this->display[]='newcart';
		$this->display[]='showcarts';
		$this->display[]='showcart';
		$this->display[]='setcurrent';
		$this->display[]='delete';
		$this->display[]='savecart';
		$this->display[]='addtocart';
	}
	function convert() {
		$app = JFactory::getApplication();
		$cart_type = JRequest::getString('cart_type','cart');
		$cart_id = JRequest::getInt('cart_id', 0);

		$cartClass = hikashop_get('class.cart');
		$ret = $cartClass->convert($cart_id);

		if(empty($ret)) {
			$app->enqueueMessage(JText::_('ERROR'), 'error');

			return $this->showcarts();
		}

		$app->enqueueMessage(JText::_('SUCCESS'));
		JRequest::setVar('cart_id', $ret);

		$cart = $cartClass->get($ret);
		JRequest::setVar('cart_type', $cart->cart_type);

		return $this->showcart();
	}

	function newcart(){
		$cartClass = hikashop_get('class.cart');
		$cart_type = JRequest::getString('cart_type','cart');

		$cart_id = $cartClass->getCurrentCartId($cart_type);

		$status = $cartClass->setCurrent($cart_id, true);

		if($status){
			$session = JFactory::getSession();
			$currUser = hikashop_loadUser();
			$newCart = new stdClass();
			if($currUser == null)
				$newCart->user_id = 0;
			else
				$newCart->user_id = $currUser;
			$newCart->session_id = $session->getId();
			$newCart->cart_modified = time();
			$newCart->cart_type = $cart_type;
			$newCart->cart_current = 1;
			$newCart->cart_share = 'nobody';
			$status = $cartClass->save($newCart);
			if($status){
				$app = JFactory::getApplication();
				if($cart_type == 'cart')
					$app->enqueueMessage(JText::sprintf( 'HIKASHOP_CART_CREATED'), 'notice');
				else
					$app->enqueueMessage(JText::sprintf( 'HIKASHOP_WISHLIST_CREATED'), 'notice');
			}else{
				$app->enqueueMessage(JText::sprintf( 'ERROR'), 'warning');
			}
		}
		return $this->showcarts();
	}

	function showcarts(){
		JRequest::setVar('layout', 'showcarts');
		return parent::display();
	}

	function showcart(){
		JRequest::setVar('layout', 'showcart');
		return parent::display();
	}

	function addtocart(){
		global $Itemid;
		$app = JFactory::getApplication();
		$cartClass = hikashop_get('class.cart');

		$fromCart = new stdClass();
		$fromCart->cart_id = JRequest::getInt('cart_id',0);
		$fromCart = $cartClass->get($fromCart->cart_id);

		$action = JRequest::getString('action','');
		if($action == 'compare'){
			$formData = JRequest::getVar('data', array(), '', 'array');
			if(isset($formData['products'])){
				$cidList = array();
				foreach($formData['products'] as $product_id => $product){
					if(!empty($product['checked'])) {
						$cidList[(int)$product_id] = "cid[]=".$product_id;
					}
				}

				$jconfig = JFactory::getConfig();
				$sef = (HIKASHOP_J30 ? $jconfig->get('sef') : $jconfig->getValue('config.sef'));

				$url = hikashop_completeLink('product&task=compare&Itemid='.$Itemid,false,true).($sef?'?':'&').implode('&',$cidList);
			}else{
				$url = 'cart&task=showcart&cart_type='.$fromCart->cart_type.'&cart_id='.$fromCart->cart_id.'&Itemid='.$Itemid;
				$url = hikashop_completeLink($url,false,true);
			}
			return $this->setRedirect($url);
		}

		$url = $action;
		if($action == ''){
			$url = 'cart&task=showcart&cart_type='.$fromCart->cart_type.'&cart_id='.$fromCart->cart_id.'&Itemid='.$Itemid;
			$url = hikashop_completeLink($url,false,true);
		}

		$toCart = new stdClass();
		$toCart->cart_type = 'cart';
		if($fromCart->cart_type == 'cart')
			$toCart->cart_type = 'wishlist';

		if($toCart->cart_type == 'wishlist' && hikashop_loadUser() == null){
			$app->enqueueMessage(JText::_('LOGIN_REQUIRED_FOR_WISHLISTS'));
			return $this->setRedirect($url);
		}

		$toCart->cart_id = $cartClass->getCurrentCartId($toCart->cart_type);

		if(!$toCart->cart_id){
			unset($toCart->cart_id);
			$toCart->cart_id = $cartClass->save($toCart);
		}

		$formData = JRequest::getVar('data', array(), '', 'array');
		$i = 0;
		if(isset($formData['products'])){
			$cart_product_id = 0;
			$fromProducts = $fromCart->cart_products;
			foreach($formData['products'] as $product_id => $product){
				if(empty($product['checked']))
					continue;
				$i++;
				if(!isset($product['quantity'])) $product['quantity'] = 1;
				$options = array();
				foreach($fromProducts as $fromProduct){
					if($fromProduct->product_id == $product_id){
						$cart_product_id = $fromProduct->cart_product_id;
					}
				}
				foreach($fromProducts as $fromProduct){
					if($fromProduct->cart_product_option_parent_id == $cart_product_id){
						$options[] = $fromProduct->product_id;
					}
				}
				JRequest::setVar('hikashop_product_option',$options);
				$cartClass->update((int)$product_id, (int)$product['quantity'],1,'product',false,false,$toCart->cart_id);
			}
		}
		if($i == 0){
			$app->enqueueMessage(JText::_('PLEASE_SELECT_A_PRODUCT_FIRST'));
		}
		return $this->setRedirect($url);
	}

	function savecart(){
		$app = JFactory::getApplication();
		$cartClass = hikashop_get('class.cart');
		$session = JFactory::getSession();

		$cart_id = JRequest::getInt('cart_id','0');
		$cart_name = JRequest::getString('cart_name','');
		$cart_share = JRequest::getString('cart_share','nobody');
		if($cart_share == 'email'){
			$cart_share = JRequest::getString('hikashop_wishlist_token','nobody');
		}

		$cart = $cartClass->get($cart_id);
		$currUser = hikashop_loadUser(true);

		if($cart != null && ((isset($currUser->user_id) && $currUser->user_id == $cart->user_id) || $session->getId() == $cart->session_id)){

			$formData = JRequest::getVar( 'data', array(), '', 'array' );
			if(!empty($formData) && isset($cart->cart_products)){
				foreach($cart->cart_products as $k => $product){
					if(isset($formData['products'][$product->product_id])){
						$cart->cart_products[$k]->cart_product_quantity = (int)$formData['products'][$product->product_id]['quantity'];
					}
				}
			}

			$cart->cart_name = $cart_name;
			$cart->cart_share = $cart_share;

			$cartClass->save($cart);
		}
		$this->showcart();
	}

	function setcurrent(){
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$cart_id = JRequest::getVar('cart_id','0');
		$cart_type = JRequest::getString('cart_type','cart');
		$cartClass = hikashop_get('class.cart');
		$cart = $cartClass->get($cart_id);
		$currUser = hikashop_loadUser(true);
		if($cart != null && $currUser->user_id == $cart->user_id)
			$result = $cartClass->setCurrent($cart_id);
		JRequest::setVar('layout', 'showcarts');
		return parent::display();
	}

	function delete(){ //delete a cart with the id given
		$cart_id = JRequest::getInt('cart_id','0');
		$cart_type = JRequest::getString('cart_type','cart');
		$cartClass = hikashop_get('class.cart');
		$cart = $cartClass->get($cart_id);

		$currUser = hikashop_loadUser(true);
		if($cart != null && $currUser->user_id == $cart->user_id)
			$cartClass->delete($cart_id, 'old');

		$this->showcarts();
	}
}
