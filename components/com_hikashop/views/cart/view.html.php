<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.2.2
 * @author	hikashop.com
 * @copyright	(C) 2010-2019 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class CartViewCart extends HikaShopView {
	var $type = 'main';
	var $ctrl= 'cart';
	var $nameListing = 'CARTS';
	var $nameForm = 'CARTS';
	var $icon = 'cart';
	var $module = false;
	var $triggerView = true;

	public function display($tpl = null, $params = array()) {
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		$this->params =& $params;
		if(method_exists($this, $function))
			$this->$function();
		parent::display($tpl);
	}

	 public function share() {
	 	$cart_id = hikashop_getCID('cart_id');
		$this->config = hikashop_config();
		$this->cartClass = hikashop_get('class.cart');
		$this->cart = $this->cartClass->getFullCart($cart_id);
		$this->emails = hikaInput::get()->getVar('emails','');
		$this->copy = hikaInput::get()->getInt('copy');
		$this->cart_share_url = $this->cartClass->getShareUrl($this->cart);

	 }

	public function show() {
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();

		$user_id = hikashop_loadUser(false);

		$config = hikashop_config();
		$this->assignRef('config', $config);

		global $Itemid;
		$this->Itemid = $Itemid;
		$menus	= $app->getMenu();
		$menu = $menus->getActive();
		if(empty($menu)){
			if(!empty($Itemid)){
				$menus->setActive($Itemid);
				$menu = $menus->getItem($Itemid);
			}
		}

		$this->loadRef(array(
			'imageHelper' => 'helper.image',
			'popupHelper' => 'helper.popup',
			'currencyClass' => 'class.currency',
			'cartClass' => 'class.cart',
			'productClass' => 'class.product',
			'dropdownHelper' => 'helper.dropdown',
		));

		$this->currencyHelper =& $this->currencyClass;

		$cart = null;
		$cart_id = hikashop_getCID('cart_id');

		$type = 'cart';
		if(empty($cart_id)){
			if (is_object( $menu) && is_object( $menu->params ))
				$type = $menu->params->get('cart_type');
			if(empty($type))
				$type = hikaInput::get()->getString('cart_type','cart');
			if(!in_array($type, array('cart','wishlist')))
				$type = 'cart';
			$cart_id = $this->cartClass->getCurrentCartId($type) ;

			if(!empty($cart_id))
				$cart = $this->cartClass->getFullCart($cart_id);
		} else
			$cart = $this->cartClass->getFullCart($cart_id);

		$this->assignRef('cart', $cart);

		$pathway = $app->getPathway();
		$show_page_heading = true;
		$params = null;
		if(!empty($menu) && method_exists($menu, 'getParams')) {
			$params = $menu->getParams();
			$show_page_heading = $params->get('show_page_heading');
		}
		if(is_null($show_page_heading)) {
			$com_menus = JComponentHelper::getParams('com_menus');
			if(!empty($com_menus))
				$show_page_heading = $com_menus->get('show_page_heading');
		}
		$title = ($type == 'wishlist') ? 'HIKASHOP_WISHLIST': 'HIKASHOP_CART';
		if(!empty($menu) && method_exists($menu, 'getParams') && $menu->link == 'index.php?option=com_hikashop&view=cart&layout=listing') {
			if($show_page_heading)
				$this->title = $params->get('page_heading');
			$title = $params->get('page_title');
			if(empty($title))
				$title = $menu->title;
			hikashop_setPageTitle($title);
		} else {
			if($show_page_heading)
				$this->title = JText::_($title);
			hikashop_setPageTitle($title);
			$pathway->addItem(JText::_('CARTS'), hikashop_completeLink('cart&task=listing&cart_type='.$type.'&Itemid='.$Itemid));
		}
		$pathway->addItem(JText::_($title), hikashop_completeLink('cart&task=show&cid='.$cart_id.'&Itemid='.$Itemid));

		if(empty($cart)) {
			$this->checkbox_column = false;
			$this->productFields = null;
			if($type == 'wishlist')
				$app->enqueueMessage(JText::_('WISHLIST_EMPTY'));
			return false;
		}


		$manage = ($cart->cart_type == 'cart' || $cart->user_id == $user_id);
		$this->assignRef('manage', $manage);

		$juser = JFactory::getUser();
		$this->assignRef('guest', $juser->guest);

		$print_cart = (hikaInput::get()->getBool('print_cart', false) === true) && $config->get('print_cart');
		if($print_cart)
			$manage = false;
		$this->assignRef('print_cart', $print_cart);



		$menuClass = hikashop_get('class.menus');
		$url_checkout = $menuClass->getCheckoutURL();
		$this->assignRef('checkout_url', $url_checkout);

		foreach($cart->products as &$product) {
			$this->productClass->addAlias($product);
		}

		$user_carts = array();
		if((int)$config->get('enable_multicart') && !empty($user_id)) {
			$query = 'SELECT cart_id, cart_name, cart_modified, cart_current '.
					' FROM '.hikashop_table('cart').' AS cart WHERE cart.user_id = '.(int)$user_id.' AND cart.cart_type = '.$db->Quote('cart').' AND cart.cart_id != '.(int)$cart->cart_id;
			$db->setQuery($query);
			$user_carts = $db->loadObjectList();
		}
		$this->assignRef('user_carts', $user_carts);


		$checkbox_column = ((int)$config->get('enable_multicart') || (int)$config->get('enable_wishlist')) && empty($print_cart);
		$this->assignRef('checkbox_column', $checkbox_column);

		$params = new hikaParameter();
		$default_params = $config->get('default_params');
		foreach($default_params as $k => $v) {
			$params->set($k, $v);
		}
		$params->set('show_delete', $config->get('checkout_cart_delete', 1));
		$this->assignRef('params', $params);

		$toolbar = array();
		if($config->get('print_cart')) {
			$toolbar['print'] = array(
				'icon' => 'print',
				'name' => JText::_('HIKA_PRINT'),
				'url' => hikashop_completeLink('cart&task=show&print_cart=1&cart_id='.$cart->cart_id.'&Itemid='.$Itemid, true),
				'popup' => array(
					'id' => 'hikashop_print_cart',
					'width' => 760,
					'height' => 480
				),
				'fa' => array('html' => '<i class="fas fa-print"></i>')
			);
		}
		if($cart->cart_type != 'wishlist') {
			$toolbar['cart'] = array(
				'icon' => 'cart',
				'name' => JText::_('CHECKOUT'),
				'url' => $url_checkout,
				'fa' => array('html' => '<i class="fas fa-shopping-cart"></i>')
			);
		} else {
			$toolbar['cart'] = array(
				'icon' => 'cart',
				'name' => JText::_('ADD_TO_CART'),
				'javascript' => 'return window.cartMgr.moveProductsToCart(0)',
				'fa' => array('html' => '<i class="fas fa-cart-plus"></i>')
			);
		}
		if($this->config->get('enable_multicart') && !$juser->guest) {
			$dropData = array();
			foreach($user_carts as $user_cart) {
				$cart_name = !empty($user_cart->cart_name) ? $user_cart->cart_name : '';
				if(empty($cart_name))
					$cart_name = !empty($user_cart->cart_current) ? JText::_('CURRENT_CART') : hikashop_getDate($user_cart->cart_modified);
				$dropData[] = array(
					'name' =>'<i class="fa fa-arrow-right"></i> <span class="btnName">'.$cart_name.'</span>',
					'link' => '#move-to-cart',
					'click' => 'return window.cartMgr.moveProductsToCart('.(int)$user_cart->cart_id.');',
				);
			}

			$dropData['new_cart'] = array(
				'name' => '<i class="fas fa-plus"></i> ' . JText::_('NEW_CART'),
				'link' => '#new-cart',
				'click' => 'return window.cartMgr.moveProductsToCart(-1);',

			);
			$toolbar['move_to'] = array(
				'dropdown' => array(
					'label' => !empty($manage) ? JText::_('HIKA_MOVE_TO') : JText::_('HIKA_ADD_TO'),
					'data' => $dropData,
					'options' => array('type' => 'link', 'right' => true, 'up' => false, 'hkicon' => 'icon-32-go-cart')
				),
				'fa' => array('html' => '<i class="fas fa-cart-arrow-down"></i>')
			);
		}
		if(!empty($manage)) {
			$toolbar['save'] = array(
				'icon' => 'save',
				'name' => JText::_('HIKA_SAVE'),
				'javascript' => "return window.hikashop.submitform('apply','hikashop_show_cart_form');",
				'fa' => array('html' => '<i class="far fa-save"></i>')
			);
		}
		if(!$juser->guest) {
			$multi_cart = (int)$config->get('enable_multicart', 1);
			if($cart->cart_type == 'wishlist')
				$multi_cart = (int)$config->get('enable_multiwishlist', 1);

			$link = hikashop_completeLink('user&task=cpanel');
			if($multi_cart) {
				$link = hikashop_completeLink('cart&task=listing&cart_type=' . $cart->cart_type.'&Itemid='.$Itemid);
			}
			$toolbar['back'] = array(
				'icon' => 'back',
				'name' => JText::_('HIKA_BACK'),
				'url' => $link,
				'fa' => array('html' => '<i class="fas fa-arrow-circle-left"></i>')
			);
		}
		$this->toolbar = $toolbar;

	}

	public function listing() {
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();

		$user_id = hikashop_loadUser(false);

		$config = hikashop_config();
		$this->assignRef('config', $config);
		global $Itemid;
		$this->Itemid = $Itemid;

		$this->loadRef(array(
			'cartClass' => 'class.cart',
			'currencyClass' => 'class.currency'
		));

		$cart_type = hikaInput::get()->getCmd('cart_type', '');
		if(!in_array($cart_type, array('cart','wishlist')))
			$cart_type = 'cart';
		$this->assignRef('cart_type', $cart_type);

		$title = ($cart_type == 'wishlist') ? 'WISHLISTS': 'CARTS';
		hikashop_setPageTitle( JText::_($title) );

		$pageInfo = $this->getPageInfo('cart.cart_id');

		$filters = array(
			'cart.cart_type = ' . $db->Quote($cart_type),
			'cart.user_id = ' . (int)$user_id
		);
		$orderingAccept = array(
			'cart.cart_id'
		);
		$order = ' ORDER BY cart.cart_id ASC';
		$searchMap = array();
		$this->processFilters($filters, $order, $searchMap, $orderingAccept);

		$query = ' FROM ' . hikashop_table('cart') . ' AS cart ' . $filters . $order;
		$this->getPageInfoTotal($query, '*');
		$db->setQuery('SELECT cart.cart_id' . $query, $pageInfo->limit->start, $pageInfo->limit->value);
		$rows = $db->loadObjectList('cart_id');

		foreach($rows as &$row) {
			$row = $this->cartClass->getFullCart($row->cart_id);
		}
		unset($row);

		$this->toolbar = array();
		$new_button = false;
		if($cart_type == 'wishlist') {
			if($config->get('enable_multiwishlist', 1))
				$new_button = true;
			$this->title = JText::_('WISHLISTS');
		} else {
			if($config->get('enable_multicart', 1))
				$new_button = true;
			$this->title = JText::_('CARTS');
		}
		if($new_button) {
			$this->toolbar['new'] = array(
				'icon' => 'new',
				'name' => JText::_('HIKA_NEW'),
				'url' => hikashop_completeLink('cart&task=add&cart_type='.$cart_type.'&Itemid='.$Itemid),
				'fa' => array('html' => '<i class="fas fa-plus"></i>')
			);
		}


		$menus	= $app->getMenu();
		$menu	= $menus->getActive();
		$show_page_heading = true;
		$params = null;
		if(!empty($menu) && method_exists($menu, 'getParams')) {
			$params = $menu->getParams();
			$show_page_heading = $params->get('show_page_heading');
		}
		if(is_null($show_page_heading)) {
			$com_menus = JComponentHelper::getParams('com_menus');
			if(!empty($com_menus))
				$show_page_heading = $com_menus->get('show_page_heading');
		}
		if(!empty($menu) && method_exists($menu, 'getParams') && $menu->link == 'index.php?option=com_hikashop&view=cart&layout=listing') {
			if($show_page_heading)
				$this->title = $params->get('page_heading');
			$title = $params->get('page_title');
			if(empty($title))
				$title = $menu->title;
			hikashop_setPageTitle($title);
		} else {
			$title = ($cart_type == 'wishlist') ? 'WISHLISTS': 'CARTS';
			if($show_page_heading)
				$this->title = JText::_($title);
			hikashop_setPageTitle($title);
			$pathway = $app->getPathway();
			$pathway->addItem(JText::_($title), hikashop_completeLink('cart&cart_type='.$cart_type.'&Itemid='.$Itemid));

			$this->toolbar['back'] = array(
				'icon' => 'back',
				'name' => JText::_('HIKA_BACK'),
				'url' => hikashop_completeLink('user&task=cpanel&Itemid='.$Itemid),
				'fa' => array('html' => '<i class="fas fa-arrow-circle-left"></i>')
			);
		}


		$this->assignRef('carts', $rows);

		$this->getPagination();
		$this->getOrdering('cart.cart_id', true);
	}

	function showcart() {
	}

	function showcarts(){
	}

	function printcart() {
		$this->show();
	}

	function _getCheckoutURL() {
		global $Itemid;
		$url_itemid='';
		if(!empty($Itemid)){
			$url_itemid='&Itemid='.$Itemid;
		}
		return hikashop_completeLink('checkout'.$url_itemid,false,true);
	}

	function init($cart = false) {
		$config =& hikashop_config();
		$url = $config->get('redirect_url_after_add_cart','stay_if_cart');
		switch($url){
			case 'checkout':
				$url = $this->_getCheckoutURL();
				break;
			case 'stay_if_cart':
				$url='';
				if(!$cart){
					$url = $this->_getCheckoutURL();
					break;
				}
			case 'ask_user':
			case 'stay':
				$url='';
			case '':
			default:
				if(empty($url)){
					$url = hikashop_currentURL('return_url',false);
				}
				break;
		}
		return urlencode($url);
	}

	function addCharacteristics(&$element,&$mainCharacteristics,&$characteristics){
		$element->characteristics = @$mainCharacteristics[$element->product_id][0];
		if(!empty($element->characteristics) && is_array($element->characteristics)){
			foreach($element->characteristics as $k => $characteristic){
				if(!empty($mainCharacteristics[$element->product_id][$k])){
					$element->characteristics[$k]->default=end($mainCharacteristics[$element->product_id][$k]);
				}else{
					$app = JFactory::getApplication();
					$app->enqueueMessage('The default value of one of the characteristics of that product isn\'t available as a variant. Please check the characteristics and variants of that product');
				}
			}
		}

		if(empty($element->variants))
			return;

		foreach($characteristics as $characteristic){
			foreach($element->variants as $k => $variant){
				if($variant->product_id==$characteristic->variant_product_id){
					$element->variants[$k]->characteristics[$characteristic->characteristic_parent_id]=$characteristic;
					$element->characteristics[$characteristic->characteristic_parent_id]->values[$characteristic->characteristic_id]=$characteristic;
					if($this->selected_variant_id && $variant->product_id==$this->selected_variant_id){
						$element->characteristics[$characteristic->characteristic_parent_id]->default=$characteristic;
					}
				}
			}
		}
		if(isset($_REQUEST['hikashop_product_characteristic'])){
			if(is_array($_REQUEST['hikashop_product_characteristic'])){
				hikashop_toInteger($_REQUEST['hikashop_product_characteristic']);
				$chars = $_REQUEST['hikashop_product_characteristic'];
			}else{
				$chars = hikaInput::get()->getCmd('hikashop_product_characteristic','');
				$chars = explode('_',$chars);
			}
			if(!empty($chars)){
				foreach($element->variants as $k => $variant){
					$chars = array();
					foreach($variant->characteristics as $val){
						$i = 0;
						$ordering = @$element->characteristics[$val->characteristic_parent_id]->ordering;
						while(isset($chars[$ordering])&& $i < 30){
							$i++;
							$ordering++;
						}
						$chars[$ordering] = $val;
					}
					ksort($chars);
					$element->variants[$k]->characteristics=$chars;
					$variant->characteristics=$chars;
					$choosed = true;
					foreach($variant->characteristics as $characteristic){
						$ok = false;
						foreach($chars as $k => $char){
							if(!empty($char)){
								if($characteristic->characteristic_id==$char){
									$ok = true;
									break;
								}
							}
						}
						if(!$ok){
							$choosed=false;
						}else{
							$element->characteristics[$characteristic->characteristic_parent_id]->default=$characteristic;
						}
					}
					if($choosed){
						break;
					}
				}
			}
		}
		foreach($element->variants as $k => $variant){
			$temp=array();
			foreach($element->characteristics as $k2 => $characteristic2){
				if(!empty($variant->characteristics)){
					foreach($variant->characteristics as $k3 => $characteristic3){
						if($k2==$k3){
							$temp[$k3]=$characteristic3;
							break;
						}
					}
				}
			}
			$element->variants[$k]->characteristics=$temp;
		}
	}
}
