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
class CartViewCart extends hikashopView {
	public $ctrl = 'cart';
	public $nameListing = 'HIKASHOP_CHECKOUT_CART';
	public $nameForm = 'HIKASHOP_CHECKOUT_CART';
	public $icon = 'shopping-cart';

	public function display($tpl = null) {
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		if(method_exists($this, $function) && $this->$function($tpl) === false)
			return false;
		parent::display($tpl);
	}

	protected function handleToolbarTitle() {
		$cart_type = hikaInput::get()->getString('cart_type', 'cart');
		if($cart_type == 'wishlist') {
			$this->nameListing = 'WISHLIST';
			$this->nameForm = 'WISHLIST';
			$this->icon = 'heart';
		}
	}

	public function listing() {
		$this->handleToolbarTitle();

		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$config =& hikashop_config();
		$this->assignRef('config', $config);

		$cart_type = hikaInput::get()->getString('cart_type', 'cart');
		if(!in_array($cart_type, array('cart', 'wishlist')))
			$cart_type = 'cart';
		$this->assignRef('cart_type', $cart_type);

		$this->loadRef(array(
			'cartHelper' => 'helper.cart',
			'cartClass' => 'class.cart',
			'userClass' => 'class.user',
			'currencyClass' => 'class.currency'
		));

		$popup = (hikaInput::get()->getString('tmpl', null) === 'component');
		$this->assignRef('popup', $popup);

		$main_currency = (int)$config->get('main_currency', 1);

		$pageInfo = $this->getPageInfo('cart.cart_id', 'desc');

		$filters = array(
			'cart.cart_type = ' . $db->Quote($cart_type)
		);
		$order = '';
		$searchMap = array(
			'cart.cart_id',
			'cart.user_id',
			'cart.cart_ip',
			'cart.cart_name',
			'cart.cart_coupon',
			'cart.cart_type',
			'hk_user.user_email',
			'joomla_user.username',
			'joomla_user.name'
		);
		$orderingAccept = array(
			'cart.'
		);

		$this->processFilters($filters, $order, $searchMap, $orderingAccept);
		$query = ' FROM ' . hikashop_table('cart') . ' AS cart LEFT JOIN ' . hikashop_table('user') . ' AS hk_user ON cart.user_id = hk_user.user_id LEFT JOIN ' . hikashop_table('users',false) . ' AS joomla_user ON hk_user.user_cms_id = joomla_user.id ' . $filters . $order;
		$this->getPageInfoTotal($query, '*');
		$db->setQuery('SELECT cart.*' . $query, $pageInfo->limit->start, $pageInfo->limit->value);
		$rows = $db->loadObjectList();

		if(!empty($pageInfo->search)) {
			$rows = hikashop_search($pageInfo->search, $rows, 'cart_id');
		}

		foreach($rows as $k => &$row) {
			if(empty($row->cart_id)) {
				unset($rows[$k]);
				continue;
			}

			$row->full_cart = $this->cartClass->getFullCart($row->cart_id);

			$row->price = isset($row->full_cart->total->prices[0]->price_value) ? $row->full_cart->total->prices[0]->price_value : 0;
			$row->currency = isset($row->full_cart->total->prices[0]->price_currency_id) ? $row->full_cart->total->prices[0]->price_currency_id : $main_currency;
			$row->quantity = isset($row->full_cart->quantity->total) ? $row->full_cart->quantity->total : 0;

			if(!empty($row->user_id))
				$row->user = $this->userClass->get($row->user_id);
		}
		unset($row);

		$this->assignRef('carts', $rows);

		$this->getPagination();
		$this->getOrdering('cart.cart_id', true);

		$manageUser = hikashop_isAllowed($config->get('acl_user_manage', 'all'));
		$this->assignRef('manageUser', $manageUser);
		$manage = hikashop_isAllowed($config->get('acl_' . $cart_type . '_manage','all'));
		$this->assignRef('manage', $manage);

		hikashop_setTitle(JText::_($this->nameListing), $this->icon, $this->ctrl);

		$this->toolbar = array(
			array('name' => 'addNew', 'display' => $manage),
			array('name' => 'editList', 'display' => $manage),
			array('name' => 'deleteList', 'display' => hikashop_isAllowed($config->get('acl_' . $cart_type . '_delete', 'all'))),
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-listing'),
			'dashboard'
		);
	}

	public function form($tpl = null) {
		$this->handleToolbarTitle();

		$this->loadRef(array(
			'popup' => 'helper.popup',
			'toggle' => 'helper.toggle',
			'fieldClass' => 'class.field',
			'productClass' => 'class.product',
			'currencyClass' => 'class.currency',
			'nameboxType' => 'type.namebox',
			'dropdownHelper' => 'helper.dropdown',
			'cartShareType' => 'type.cart_share',
		));

		$cart_id = hikashop_getCID('cart_id');

		if(!empty($cart_id)) {
			$cartClass = hikashop_get('class.cart');
			$cart = $cartClass->getFullCart($cart_id);
			if(!empty($cart->messages)) {
				$tmpl = hikaInput::get()->getCmd('tmpl', '');
				if(in_array($tmpl, array('component', 'ajax', 'raw'))) {
					ob_end_clean();
					echo json_encode($cart->messages);
					exit;
				} else {
					$app = JFactory::getApplication();
					foreach($cart->messages as $message) {
						$app->enqueueMessage($message['msg'], $message['type']);
					}
				}
			}

			$cart->cart_currency_id = (int)hikashop_getCurrency();
			if(isset($cart->full_total) && isset($cart->full_total->prices[0])) {
				$cart->cart_currency_id = $cart->full_total->prices[0]->price_currency_id;
			}

			$task = 'edit';
		} else {
			$cart = new stdClass();
			$cart->cart_id = 0;
			$cart->cart_name = '';
			$cart->cart_type = hikaInput::get()->getVar('cart_type', 'cart');
			$cart->cart_modified = time();
			$cart->cart_coupon = '';
			$cart->user_id = 0;
			$cart->cart_products = array();
			$cart->products = array();
			$cart->cart_currency_id = (int)hikashop_getCurrency();
			$cart->cart_share = 'nobody';

			$task = 'add';
		}
		$this->assignRef('cart', $cart);

		$user = null;
		if(!is_null($cart) && isset($cart->user_id) && !empty($cart->user_id)) {
			$userClass = hikashop_get('class.user');
			$user = $userClass->get((int)$cart->user_id);
		}
		$this->assignRef('user', $user);

		$fields = array();
		if(hikashop_level(2)) {
			$fields = array(
				'product' => array(),
				'item' => $this->fieldClass->getFields('backend', $cart->cart_products, 'item'),
			);
			if(!empty($cart->products))
				$fields['product'] = $this->fieldClass->getFields('display:back_cart_details=1', $cart->cart_products, 'product');

			if(!empty($fields['product']) && !empty($cart->products)) {
				foreach($fields['product'] as $k => $field) {
					$fieldname = $field->field_namekey;
					$used = false;
					foreach($cart->products as $product) {
						if(empty($product->$fieldname))
							continue;
						$used = true;
						break;
					}
					if(!$used)
						unset($fields['product'][$k]);
				}
			}
		}
		$this->assignRef('fields', $fields);

		hikashop_setTitle(JText::_($this->nameForm), $this->icon, $this->ctrl.'&task='.$task.'&cid='.$cart_id);

		$this->toolbar = array(
			array('name' => 'link', 'icon'=>'new','alt'=>JText::_('CREATE_ORDER'),'url'=>hikashop_completeLink('cart&task=createorder&cid='.$cart_id.'&'.hikashop_getFormToken().'=1')),
			array('name' => 'group', 'buttons' => array( 'apply', 'save')),
			'cancel',
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-listing')
		);
	}

	protected function checkFieldForProduct(&$field, &$product) {
		if(empty($field->field_categories) && empty($field->field_products))
			return true;

		if(!empty($field->field_products) && strpos($field->field_products, ','.$product->product_id.',') !== false)
			return true;
		if(!empty($field->field_products) && !empty($product->product_parent_id) && strpos($field->field_products, ','.$product->product_parent_id.',') !== false)
			return true;

		if(empty($field->field_categories))
			return false;

		foreach($this->product->categories as $category) {
			if(strpos($field->field_categories, ','.$category->category_id.',') !== false)
				return true;
		}

		return false;
	}

	public function showblock($tpl = null) {
		$block = hikaInput::get()->getString('block', null);
		$blocks = array(
			'product', 'edit_product',
		);
		if(!in_array($block, $blocks))
			return false;

		$this->form($tpl);

		$this->ajax = true;
		if(in_array($block, array('product'))) {
			$this->product = null;
			$this->cart_product = null;
			$this->pid = hikaInput::get()->getInt('pid', 0);

			foreach($this->cart->products as $k => $v) {
				if((int)$v->cart_product_id != $this->pid)
					continue;

				$this->product = $v;
				$this->cart_product = $this->cart->cart_products[$k];
				break;
			}
			if($this->pid > 0 && empty($this->product)) {
				return false;
			}
		}

		$this->setLayout('form_block_' . $block);
		echo $this->loadTemplate();

		return false;
	}
}
