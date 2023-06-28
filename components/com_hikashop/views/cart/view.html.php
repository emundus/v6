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
	public function product_save() {
		$cartClass = hikashop_get('class.cart');
		$cart_id = hikashop_getCID('cart_id');
		$this->config = hikashop_config();
		$this->cart = $cartClass->getFullCart($cart_id);
	}
	public function product_edit() {
		$cartClass = hikashop_get('class.cart');
		$cart_id = hikashop_getCID('cart_id');
		$this->config = hikashop_config();
		$this->imageHelper = hikashop_get('helper.image');
		$this->currencyClass = hikashop_get('class.currency');
		$productClass = hikashop_get('class.product');
		$this->cart = $cartClass->getFullCart($cart_id);
		$this->mainProduct = null;
		$this->parentProduct = null;
		$this->options = array();
		$this->optionsInCart = array();
		$cart_product_id = hikaInput::get()->getInt('cart_product_id', 0);
		foreach($this->cart->products as $p){
			if($p->cart_product_id == $cart_product_id) {
				$this->mainProduct = $this->product = $p;
			}
			if($p->cart_product_option_parent_id == $cart_product_id) {
				$this->optionsInCart[$p->product_id] = $p;
			}
		}
		if(!empty($this->product->cart_product_parent_id)) {
			$this->mainProduct = $this->parentProduct = $this->cart->products[$this->product->cart_product_parent_id];
		}

		if(hikashop_level(2)) {
			$fieldsClass = hikashop_get('class.field');
			$this->itemFields = $fieldsClass->getFields('display:cart_edit=1', $this->mainProduct, 'item', 'checkout&task=state');

			$null = array();
			$fieldsClass->addJS($null, $null, $null);
			$fieldsClass->jsToggle($this->itemFields, $this->mainProduct, 0);
			$extraFields = array('item'=> &$this->itemFields);
			$requiredFields = array();
			$validMessages = array();
			$values = array('item'=> $this->mainProduct);
			$fieldsClass->checkFieldsForJS($extraFields, $requiredFields, $validMessages, $values);
			$fieldsClass->addJS($requiredFields, $validMessages, array('item'));
			$this->fieldsClass = $fieldsClass;
		}

		if($this->config->get('group_options', 0)) {
			$prices = $this->product->prices;
			$this->options = $productClass->loadProductOptions($this->mainProduct, array('user_id' => hikashop_loadUser(false)));
			$this->product->prices = $prices;
		}
		if(!empty($this->parentProduct))
			$productClass->loadProductVariants($this->parentProduct, array('user_id' => hikashop_loadUser(false), 'selected_variant_id' => $this->product->product_id));
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
			if(is_object( $menu)) {
				if(HIKASHOP_J30)
					$menuParams = $menu->getParams();
				else
					$menuParams = @$menu->params;
				$type = $menuParams->get('cart_type');
			}
			if(empty($type))
				$type = hikaInput::get()->getString('cart_type','cart');
			if(!in_array($type, array('cart','wishlist')))
				$type = 'cart';
			$cart_id = $this->cartClass->getCurrentCartId($type) ;

			if(!empty($cart_id))
				$cart = $this->cartClass->getFullCart($cart_id);
		} else
			$cart = $this->cartClass->getFullCart($cart_id);
		if(!empty($cart) && !empty($cart->cart_type))
			$type = $cart->cart_type;

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
		if(!empty($menu) && method_exists($menu, 'getParams') && in_array($menu->link, array('index.php?option=com_hikashop&view=cart&layout=show', 'index.php?option=com_hikashop&view=cart&layout=listing'))) {
			if($show_page_heading)
				$this->title = $params->get('page_heading');
			$title = $params->get('page_title');
			if(empty($title))
				$title = $menu->title;
			hikashop_setPageTitle($title);

			$robots = $params->get('robots');
			if (!$robots) {
				$jconfig = JFactory::getConfig();
				$robots = $jconfig->get('robots', '');
			}
			if($robots) {
				$doc = JFactory::getDocument();
				$doc->setMetadata('robots', $robots);
			}

		} else {
			if($show_page_heading)
				$this->title = JText::_($title);
			hikashop_setPageTitle($title);
			$pathway->addItem(JText::_('CARTS'), hikashop_completeLink('cart&task=listing&cart_type='.$type.'&Itemid='.$Itemid));
		}
		$pathway->addItem(JText::_($title), hikashop_completeLink('cart&task=show&cid='.$cart_id.'&Itemid='.$Itemid));

		if(empty($cart) || empty($cart->products)) {
			$this->checkbox_column = false;
			$this->productFields = null;
			if($type == 'wishlist')
				$app->enqueueMessage(JText::_('WISHLIST_EMPTY'));
			else
				$app->enqueueMessage(JText::_('CART_EMPTY'));
			return false;
		}

		if($cart->cart_type == 'wishlist') {
			if($cart->user_id != $user_id) {
				$user = !empty($cart->user->username) ? $cart->user->username : $cart->user->user_email;
				hikashop_setPageTitle( JText::sprintf('HIKASHOP_USER_WISHLIST', $user) );
			}

			$this->loadRef(array(
				'cartShareType' => 'type.cart_share',
			));
		}

		$manage = ($cart->cart_type == 'cart' || $cart->user_id == $user_id);
		$this->assignRef('manage', $manage);

		$juser = JFactory::getUser();
		$this->assignRef('guest', $juser->guest);

		$print_cart = (hikaInput::get()->getBool('print_cart', false) === true) && $config->get('print_cart');
		if($print_cart)
			$manage = false;
		$this->assignRef('print_cart', $print_cart);

		if(hikashop_level(2)) {
			$fieldsClass = hikashop_get('class.field');
			$this->assignRef('fieldsClass', $fieldsClass);

			$null = null;
			$itemFields = $fieldsClass->getFields('frontcomp', $null, 'item', 'checkout&task=state');
			$this->assignRef('itemFields', $itemFields);

			$null = null;
			$productFields = $fieldsClass->getFields('display:front_cart_details=1', $null, 'product');
			$this->assignRef('productFields', $productFields);

			$usefulFields = array();
			foreach($productFields as $field){
				$fieldname = $field->field_namekey;
				foreach($cart->products as $product) {
					if(!empty($product->$fieldname)) {
						$usefulFields[] = $field;
						break;
					}
				}
			}
			$productFields = $usefulFields;
		}

		if($cart->cart_type == 'wishlist') {
			$confirmed_status = $config->get('invoice_order_statuses', 'confirmed,shipped');
			if(empty($confirmed_status))
				$confirmed_status = 'confirmed,shipped';
			$confirmed_status = explode(',', trim($confirmed_status, ','));
			foreach($confirmed_status as &$status) {
				$status = $db->Quote($status);
			}
			unset($status);

			$filters = array(
				'hk_order_product.order_product_wishlist_id = -' . (int)$cart_id
			);

			if(!empty($cart->cart_products)) {
				$p = array_keys($cart->cart_products);
				hikashop_toInteger($p);
				if(in_array(0, $p))
					$p = array_diff($p, array(0));
				$filters[] = 'hk_order_product.order_product_wishlist_product_id IN ('.implode(',', $p).')';
			}

			$query = 'SELECT hk_order.order_id, hk_order.order_user_id, hk_user.user_email, hk_order.order_status, hk_order_product.* '.
				' FROM '.hikashop_table('order').' AS hk_order '.
				' LEFT JOIN '.hikashop_table('order_product').' AS hk_order_product ON hk_order.order_id = hk_order_product.order_id '.
				' LEFT JOIN '.hikashop_table('user').' AS hk_user ON hk_user.user_id = hk_order.order_user_id '.
				' WHERE hk_order.order_status IN ('.implode(',', $confirmed_status).') AND hk_order.order_type = '.$db->Quote('sale').' AND ('.implode(' OR ', $filters).')';
			$db->setQuery($query);
			$related_orders = $db->loadObjectList();

			if(!empty($related_orders)) {

				foreach($related_orders as &$related_order) {

					if(!empty($related_order->order_product_wishlist_product_id) && isset($cart->products[(int)$related_order->order_product_wishlist_product_id])) {
						$product =& $cart->products[(int)$related_order->order_product_wishlist_product_id];

						if(empty($product->bought))
							$product->bought = 0;
						$product->bought += (int)$related_order->order_product_quantity;

						if($manage) {
							if(empty($product->buyers))
								$product->related_orders = array();
							$product->related_orders[] = $related_order;
						}

						unset($product);

						$related_order->done = true;

						continue;
					}

					if(empty($related_order->order_product_wishlist_product_id)) {
						foreach($cart->products as &$product) {
							if((int)$related_order->product_id != (int)$product->product_id)
								continue;

							if(empty($product->bought))
								$product->bought = 0;
							$product->bought += (int)$related_order->order_product_quantity;

							if($manage) {
								if(empty($product->buyers))
									$product->related_orders = array();
								$product->related_orders[] = $related_order;
							}

							$related_order->done = true;
						}
						unset($product);
					}

					if(!empty($related_order->done))
						continue;

				}
				unset($related_order);
			}

			$cart_share_url = $this->cartClass->getShareUrl($cart);
			$this->assignRef('cart_share_url', $cart_share_url);
		}

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

		$user_wishlists = array();
		if((int)$config->get('enable_wishlist')) {
			$query = 'SELECT cart_id, cart_name, cart_modified, cart_current '.
					' FROM '.hikashop_table('cart').' AS cart WHERE cart.user_id = '.(int)$user_id.' AND cart.cart_type = '.$db->Quote('wishlist').' AND cart.cart_id != '.(int)$cart->cart_id;
			$db->setQuery($query);
			$user_wishlists = $db->loadObjectList();
		}
		$this->assignRef('user_wishlists', $user_wishlists);

		$multi_wishlist = (int)$config->get('enable_multiwishlist', 1);
		$this->assignRef('multi_wishlist', $multi_wishlist);

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

		if(!empty($manage)) {
			$txt = 'EMPTY_THE_CART';
			if($cart->cart_type == 'wishlist') {
				$txt = 'EMPTY_THE_WISHLIST';
			}
			$toolbar['empty'] = array(
				'icon' => 'delete',
				'name' => JText::_($txt),
				'url' => hikashop_completeLink('cart&task=remove&cid='.$cart->cart_id.'&'.hikashop_getFormToken().'=1&Itemid='.$Itemid),
				'javascript' => 'if(window.localPage && window.localPage.confirmDelete) return window.localPage.confirmDelete()',
				'fa' => array(
					'html' => '<i class="far fa-trash-alt"></i>',
				),
			);
		}

		if($cart->cart_type == 'wishlist' && !empty($this->manage)) {
			$toolbar['share'] = array(
				'icon' => 'email',
				'name' => JText::_('SHARE'),
				'url' => hikashop_completeLink('cart&task=share=&cart_id='.$cart->cart_id.'&Itemid='.$Itemid, true),
				'popup' => array(
					'id' => 'hikashop_share_cart',
					'width' => 360,
					'height' => 360
					),
				'fa' => array(
					'html' => '<i class="fas fa-at"></i>',
				),
			);
		}
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

		$catalogue_mode = $config->get('catalogue', false);
		if(!$catalogue_mode) {
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
		}
		if($this->config->get('enable_multicart') && !$juser->guest && !$catalogue_mode) {
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
		if($config->get('enable_wishlist') && !$juser->guest) {
			$dropData = array();
			foreach($user_wishlists as $user_wishlist) {
				$dropData[] = array(
					'name' => '<i class="fa fa-arrow-right"></i> '. (!empty($user_wishlist->cart_name) ? $user_wishlist->cart_name : hikashop_getDate($user_wishlist->cart_modified)),
					'link' => '#move-to-wishlist',
					'click' => 'return window.cartMgr.moveProductsToWishlist('.(int)$user_wishlist->cart_id.');'
				);
			}

			if($this->multi_wishlist || ($cart->cart_type != 'wishlist' && empty($user_wishlists))) {
				$dropData[] = array(
					'name' => '<i class="fa fa-plus"></i> ' . JText::_('NEW_WISHLIST'),
					'link' => '#new-wishlist',
					'click' => 'return window.cartMgr.moveProductsToWishlist(-1);'
				);
			}

			if(empty($toolbar['move_to'])) {
				if(count($dropData)) {
					$toolbar['move_to'] = array(
						'dropdown' => array(
							'label' => !empty($manage) ? JText::_('HIKA_MOVE_TO') : JText::_('HIKA_ADD_TO'),
							'data' => $dropData,
							'options' => array('type' => 'link', 'right' => true, 'up' => false, 'hkicon' => 'icon-32-wishlist', 'main_class' => 'hikabtn')
						),
						'fa' => array(
							'size' => 1,
							'html' => array('<i class="fas fa-list-ul fa-stack-2x" style="top:15%"></i>','<i class="fas fa-star fa-stack-1x" style="left:-36%;top:-20%;"></i>'),
						),
					);
				}
			} else {
				$cart_header = array(array(
					'header' => true,
					'name' => '<i class="fas fa-shopping-cart"></i> '.JText::_('HIKASHOP_CART')
				));
				$wishlist_header = array('-', array(
					'header' => true,
					'name' => '<i class="fas fa-star"></i> '.JText::_('WISHLIST')
				));
				$toolbar['move_to']['dropdown']['data'] = array_merge($cart_header, $toolbar['move_to']['dropdown']['data'], $wishlist_header, $dropData);
			}
		}
		if(!empty($manage) && !empty($cart) && !empty($cart->products)) {
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

			$link = hikashop_completeLink('user&task=cpanel&Itemid='.$Itemid);
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
			$robots = $params->get('robots');
			if (!$robots) {
				$jconfig = JFactory::getConfig();
				$robots = $jconfig->get('robots', '');
			}
			if($robots) {
				$doc = JFactory::getDocument();
				$doc->setMetadata('robots', $robots);
			}

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
