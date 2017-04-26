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
$hikashop_config =& hikashop_config();
if($hikashop_config->get('checkout_legacy', 0)) {
	require_once dirname(__FILE__) . '/view_legacy.html.php';
} else {
	class CheckoutViewCheckoutLegacy extends hikashopView {}
}

class CheckoutViewCheckout extends CheckoutViewCheckoutLegacy {
	public $ctrl = 'checkout';
	public $nameListing = 'CHECKOUT';
	public $nameForm = 'CHECKOUT';
	public $icon = 'checkout';
	public $extraFields = array();
	public $requiredFields = array();
	public $validMessages = array();
	public $triggerView = array('hikashop','hikashoppayment','hikashopshipping');

	public $config = null;
	public $fieldClass = null;

	protected $legacy = false;

	public function __construct() {
		$this->config =& hikashop_config();
		$this->legacy = ((int)$this->config->get('checkout_legacy', 0) != 0);

		if(!class_exists('hikashopCheckoutHelper'))
			hikashop_get('helper.checkout');

		parent::__construct();
	}

	public function display($tpl = null, $params = array()) {
		if($this->legacy)
			return parent::display($tpl, $params);

		$this->view_params =& $params;
		$this->params = new HikaParameter('');
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		if(method_exists($this, $function) && $this->$function() === false)
			return false;
		parent::display($tpl);
	}

	public function termsandconditions() {
		$terms_article = $this->config->get('checkout_terms', 0);
		$article = '';
		$this->assignRef('article', $article);

		if (empty($terms_article))
			return;

		$db = JFactory::getDBO();
		$sql = 'SELECT c.fulltext, c.introtext FROM #__content AS c WHERE id = ' . intval($terms_article);
		$db->setQuery($sql);
		$data = $db->loadObject();

		if (is_object($data))
			$article = $data->introtext . $data->fulltext;
	}

	public function show() {
		$checkoutHelper = hikashopCheckoutHelper::get();
		$this->checkoutHelper = $checkoutHelper;

		$imageHelper = hikashop_get('helper.image');
		$this->imageHelper = $imageHelper;

		$this->continueShopping = $this->config->get('continue_shopping');
		$this->display_checkout_bar = $this->config->get('display_checkout_bar');
		$cartHelper = hikashop_get('helper.cart');
		$this->assignRef('cart', $cartHelper);

		$cart_id = $checkoutHelper->getCartId();
		$this->assignRef('cart_id', $cart_id);
		$cartIdParam = ($cart_id > 0) ? '&cart_id=' . $cart_id : '';
		$this->assignRef('cartIdParam', $cartIdParam);

		$this->initItemId();

		$this->workflow_step = hikashop_getCID();
		if($this->workflow_step > 0)
			$this->workflow_step--;
		if($this->workflow_step < 0)
			$this->workflow_step = 0;
		$this->step = ($this->workflow_step + 1);

		$tmpl = JRequest::getCmd('tmpl', '');
		if($tmpl == 'ajax')
			$this->ajax = true;

		$this->workflow = $checkoutHelper->checkout_workflow;

		JPluginHelper::importPlugin('hikashop');
		JPluginHelper::importPlugin('hikashoppayment');
		JPluginHelper::importPlugin('hikashopshipping');
		$dispatcher = JDispatcher::getInstance();

		$this->checkout_data = array();

		foreach($this->workflow['steps'][$this->workflow_step]['content'] as $k => &$content) {
			$task = $content['task'];
			$this->block_position = $k;

			$ctrl = hikashop_get('helper.checkout-' . $task);
			if(!empty($ctrl)) {
				$this->checkout_data[$k] = $ctrl->display($this, $content['params']);
			} else {
				$dispatcher->trigger('onInitCheckoutStep', array($task, &$this));
			}
		}
		unset($content);

		hikashop_setPageTitle('CHECKOUT');
	}

	public function showblock() {
		$checkoutHelper = hikashopCheckoutHelper::get();
		$this->checkoutHelper = $checkoutHelper;

		$this->workflow_step = hikashop_getCID();
		if($this->workflow_step > 0)
			$this->workflow_step--;
		if($this->workflow_step < 0)
			$this->workflow_step = 0;
		$this->step = ($this->workflow_step + 1);

		$block_pos = JRequest::getInt('blockpos', 0);
		$block_task = JRequest::getString('blocktask', null);

		$this->block_position = $block_pos;

		$this->initItemid();

		$tmpl = JRequest::getCmd('tmpl', '');
		if($tmpl == 'ajax')
			$this->ajax = true;

		$this->workflow = $checkoutHelper->checkout_workflow;

		$this->checkout_data = array();

		if(empty($this->workflow['steps'][$this->workflow_step]['content']))
			return false;
		if(empty($this->workflow['steps'][$this->workflow_step]['content'][$block_pos]))
			return false;
		if($this->workflow['steps'][$this->workflow_step]['content'][$block_pos]['task'] != $block_task)
			return false;

		$content = $this->workflow['steps'][$this->workflow_step]['content'][$block_pos];
		if(empty($content['params']))
			$content['params'] = array();
		JPluginHelper::importPlugin('hikashop');
		JPluginHelper::importPlugin('hikashoppayment');
		JPluginHelper::importPlugin('hikashopshipping');
		$dispatcher = JDispatcher::getInstance();

		$ctrl = hikashop_get('helper.checkout-' . $block_task);
		if(!empty($ctrl)) {
			$this->checkout_data[$block_pos] = $ctrl->display($this, $content['params']);
		} else {
			$dispatcher->trigger('onInitCheckoutStep', array($block_task, &$this));
		}

		$dispatcher->trigger('onHikashopBeforeDisplayView', array(&$this));

		echo $this->displayBlock($block_task, $block_pos, $content['params']);

		$dispatcher->trigger('onHikashopAfterDisplayView', array(&$this));

		$events = $checkoutHelper->getEvents();
		if(!empty($events)) {
			echo "\r\n".'<script type="text/javascript">'."\r\n";
			foreach($events as $k => $v) {
				echo 'window.Oby.fireAjax("'.$k.'", '.json_encode($v).');' . "\r\n";
			}
			echo "\r\n".'</script>';
		}
		$this->displayView = false;
		return true;
	}

	public function displayBlock($layout, $pos, $options) {
		$ctrl = hikashop_get('helper.checkout-' . $layout);
		if(!empty($ctrl)) {
			$previous_options = null;
			if(!empty($this->options))
				$previous_options = $this->options;

			$this->options = $options;
			$this->module_position = (int)$pos;

			$this->setLayout('show_block_' . $layout);
			$ret = $this->loadTemplate();

			$this->options = $previous_options;

			return $ret;
		}

		$ret = '';
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onCheckoutStepDisplay', array($layout, &$ret, &$this, $pos, $options));
		return $ret;
	}

	public function getDisplayProductPrice(&$product, $unit = false) {
		$previous_price_with_tax = $this->params->get('price_with_tax', false);
		$this->row =& $product;
		$this->unit = $unit;
		$this->params->set('price_with_tax', $this->options['price_with_tax']);

		$this->setLayout('listing_price');
		$ret = $this->loadTemplate();

		unset($this->row);
		unset($this->unit);
		$this->params->set('price_with_tax', $previous_price_with_tax);

		return $ret;
	}

	public function addOptionPriceToProduct(&$productPrice, &$optionPrice){
		foreach(get_object_vars($productPrice) as $key => $value) {
			if($key == 'unit_price')
				$this->addOptionPriceToProduct($productPrice->$key, $optionPrice->$key);
			if(strpos($key, 'price_value') === false)
				continue;
			$productPrice->$key += (float)hikashop_toFloat(@$optionPrice->$key);
		}
	}

	public function loadFields() {
		if(!hikashop_level(2) || !empty($this->extraFields['item']))
			return;
		if(empty($this->fieldClass))
			$this->fieldClass = hikashop_get('class.field');
		$null = null;
		$this->extraFields['item'] = $this->fieldClass->getFields('frontcomp', $null, 'item');
	}

	public function state() {
		$namekey = JRequest::getCmd('namekey','');
		if(!headers_sent()) {
			header('Content-Type:text/html; charset=utf-8');
		}

		if(empty($namekey)) {
			echo '<span class="state_no_country">'.JText::_('PLEASE_SELECT_COUNTRY_FIRST').'</span>';
			exit;
		}

		$field_namekey = JRequest::getString('field_namekey', '');
		if(empty($field_namekey))
			$field_namekey = 'address_state';

		$field_id = JRequest::getString('field_id', '');
		if(empty($field_id))
			$field_id = 'address_state';

		$field_type = JRequest::getString('field_type', '');
		if(empty($field_type))
			$field_type = 'address';

		$db = JFactory::getDBO();
		$query = 'SELECT * FROM '.hikashop_table('field').' WHERE field_namekey = '.$db->Quote($field_namekey);
		$db->setQuery($query, 0, 1);
		$field = $db->loadObject();

		$countryType = hikashop_get('type.country');
		echo $countryType->displayStateDropDown($namekey, $field_id, $field_namekey, $field_type, '', $field->field_options);
		exit;
	}

	public function end() {
		$html = JRequest::getVar('hikashop_plugins_html', '', 'default', 'string', JREQUEST_ALLOWRAW);
		$this->assignRef('html', $html);

		$noform = JRequest::getVar('noform', 1, 'default', 'int');
		$this->assignRef('noform', $noform);

		$order_id = JRequest::getInt('order_id');
		if(empty($order_id)) {
			$app = JFactory::getApplication();
			$order_id = $app->getUserState('com_hikashop.order_id');
		}
		$order = null;
		if(!empty($order_id)){
			$orderClass = hikashop_get('class.order');
			$order = $orderClass->loadFullOrder($order_id,false,false);
		}

		$this->assignRef('order',$order);
	}

	public function after_end() {
		$order_id = JRequest::getInt('order_id');
		if(empty($order_id)) {
			$app = JFactory::getApplication();
			$order_id = $app->getUserState('com_hikashop.order_id');
		}

		$order = null;
		if(!empty($order_id)) {
			$orderClass = hikashop_get('class.order');
			$order = $orderClass->loadFullOrder($order_id, false, false);
		}

		JPluginHelper::importPlugin('hikashoppayment');
		JPluginHelper::importPlugin('hikashopshipping');
		$this->assignRef('order', $order);
	}

	 public function shop_closed() {
		$checkoutHelper = hikashopCheckoutHelper::get();
		$messages = $checkoutHelper->displayMessages('shop_closed', false);
		$this->assignRef('messages',$messages);
	 }

	protected function initItemid() {
		global $Itemid;
		$checkout_itemid = (int)$Itemid;
		$itemid_for_checkout = (int)$this->config->get('checkout_itemid', 0);
		if(!empty($itemid_for_checkout) && $checkout_itemid != $itemid_for_checkout)
			$checkout_itemid = $itemid_for_checkout;
		$url_itemid = (!empty($checkout_itemid)) ? '&Itemid='.$checkout_itemid : '';

		$this->assignRef('itemid', $checkout_itemid);
		$this->assignRef('url_itemid', $url_itemid);
	}

	public function notice() {
		global $Itemid;
		$url_itemid = '';
		if(!empty($Itemid))
			$url_itemid = '&Itemid=' . $Itemid;
		jimport('joomla.html.parameter');
		$cartHelper = hikashop_get('helper.cart');
		$this->assignRef('url_itemid', $url_itemid);
		$this->assignRef('cartClass', $cartHelper);
		$config = hikashop_config();
		$this->assignRef('config', $config);
	}

	public function &initCart() {
		if($this->legacy)
			return parent::initCart();
		$checkoutHelper = hikashopCheckoutHelper::get();
		$cart = $checkoutHelper->getCart();
		return $cart;
	}
}
