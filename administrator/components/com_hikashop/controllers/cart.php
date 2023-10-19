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
	public $type = 'cart';
	public $pkey = array('cart_id');
	public $table = array('cart');
	public $orderingMap = 'cart_modified';

	public function __construct($config = array(), $skip = false) {
		parent::__construct($config, $skip);

		$this->display[] = 'showblock';
		$this->display[] = 'findList';
		$this->modify[] = 'addproduct';
		$this->modify[] = 'createorder';
	}

	protected function getACLName($task) {
		$cartClass = hikashop_get('class.cart');
		switch($task){
			case 'edit':
			case 'apply':
			case 'save':
				$app = JFactory::getApplication();
				$cid = hikashop_getCID('cart_id');
				$cartClass = hikashop_get('class.cart');
				$cart = $cartClass->get( $cid );
				return @$cart->cart_type;
		}
		return hikaInput::get()->getVar('cart_type', 'cart');
	}

	public function edit() {
		$app = JFactory::getApplication();
		$cid = hikashop_getCID('cart_id');
		if(empty($cid)) {
			$app->enqueueMessage(JText::_('INVALID_CART'), 'error');
			$app->redirect( hikashop_completeLink('cart&task=listing', false, true) );
		}

		$cartClass = hikashop_get('class.cart');
		$cart = $cartClass->get( $cid );
		if(empty($cart)) {
			$app->enqueueMessage(JText::_('INVALID_CART'), 'error');
			$app->redirect( hikashop_completeLink('cart&task=listing', false, true) );
		}

		return parent::edit();
	}

	public function showblock() {
		hikashop_nocache();
		$cart_id = hikashop_getCID('cart_id');
		if(empty($cart_id))
			return false;

		$tmpl = hikaInput::get()->getCmd('tmpl', '');
		hikaInput::get()->set('layout', 'showblock');
		if(in_array($tmpl, array('component', 'ajax', 'raw'))) {
			ob_end_clean();
			parent::display();
			exit;
		}
		return $this->display();
	}
	public function createorder() {
		hikashop_nocache();

		JSession::checkToken('request') || jexit('Invalid Token');

		$cart_id = hikashop_getCID('cart_id');
		if(empty($cart_id))
			return false;

		$orderClass = hikashop_get('class.order');
		$orderClass->sendEmailAfterOrderCreation = false;
		$order = $orderClass->createFromCart($cart_id);

		$app = JFactory::getApplication();
		if(empty($order)){
			$app->enqueueMessage(JText::_('ORDER_COULD_NOT_BE_CREATED_FROM_CART'), 'error');
		}else{
			$app->enqueueMessage(JText::sprintf('THE_ORDER_X_WAS_SUCCESSFULLY_CREATED', '<a href="'.hikashop_completeLink('order&task=edit&cid[]='.$order->order_id).'">'.$order->order_number.'</a>'), 'success');
		}

		return $this->edit();
	}

	public function addproduct() {
		hikashop_nocache();

		JSession::checkToken('request') || jexit('Invalid Token');

		$cart_id = hikashop_getCID('cart_id');
		$product_id = hikaInput::get()->getInt('product_id', 0);
		if(empty($cart_id) || empty($product_id))
			return false;

		$cartClass = hikashop_get('class.cart');
		$cart = $cartClass->get( $cart_id );
		if(empty($cart))
			return false;

		$db = JFactory::getDBO();
		$values = array(
			'cart_id' => $cart_id,
			'product_id' => $product_id,
			'cart_product_quantity' => 1,
			'cart_product_parent_id' => 0,
			'cart_product_modified' => time(),
			'cart_product_option_parent_id' => 0
		);
		$query = 'INSERT INTO '.hikashop_table('cart_product').' ('.implode(',', array_keys($values)).') VALUES ('.implode(',', $values).')';
		$db->setQuery($query);
		$ret = (int)$db->execute();
		if(!$ret)
			return false;

		$cart_product_id = (int)$db->insertid();

		$cartClass->get('reset_cache', $cart_id);

		hikaInput::get()->set('layout', 'showblock');
		hikaInput::get()->set('block', 'product');
		hikaInput::get()->set('pid', $cart_product_id);

		$tmpl = hikaInput::get()->getCmd('tmpl', '');
		if(in_array($tmpl, array('component', 'ajax', 'raw'))) {
			ob_end_clean();
			parent::display();
			exit;
		}
		return $this->display();
	}


	public function findList() {
		$search = hikaInput::get()->getVar('search', '');
		$start = hikaInput::get()->getInt('start', 0);
		$displayFormat = hikaInput::get()->getVar('displayFormat', '');

		$options = array();

		if(!empty($displayFormat))
			$options['displayFormat'] = $displayFormat;
		if($start > 0)
			$options['page'] = $start;

		$nameboxType = hikashop_get('type.namebox');
		$elements = $nameboxType->getValues($search, 'cart', $options);
		echo json_encode($elements);
		exit;
	}
}
