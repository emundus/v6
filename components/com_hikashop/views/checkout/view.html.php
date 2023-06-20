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
	public $triggerView = array('hikashop','hikashopshipping','hikashoppayment');

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
		$step = hikaInput::get()->getInt('step', 0)-1;
		$pos = hikaInput::get()->getInt('pos', 0);

		$checkoutHelper = hikashopCheckoutHelper::get();
		$this->workflow = $checkoutHelper->checkout_workflow;
		$block = @$this->workflow['steps'][$step]['content'][$pos];
		if(!empty($block) && $block['task'] == 'terms' && !empty($block['params']['article_id']))
			$terms_article = $block['params']['article_id'];

		if(empty($terms_article))
			$terms_article = $this->config->get('checkout_terms', 0);

		if (empty($terms_article))
			return;

		$db = JFactory::getDBO();
		$sql = 'SELECT * FROM #__content WHERE id = ' . (int)$terms_article;
		$db->setQuery($sql);
		$data = $db->loadObject();

		$lang = JFactory::getLanguage();
		$currentLanguage = $lang->getTag();
		if(!in_array($data->language, array('all', $currentLanguage)) ) {
			$assoc = JLanguageAssociations::isEnabled();
			if ($assoc) {
				$data->associations = array();
				if ($data->id != null) {
					$associations = JLanguageAssociations::getAssociations('com_content', '#__content', 'com_content.item', $data->id);
					foreach ($associations as $tag => $association) {
						if($tag == $currentLanguage) {
							$sql = 'SELECT * FROM #__content WHERE id = ' . (int)$association->id;
							$db->setQuery($sql);
							$data = $db->loadObject();

						}
					}
				}
			}
		}
		$article = '';
		if (is_object($data))
			$article = $data->introtext . $data->fulltext;
		$this->assignRef('article', $article);
	}
	public function privacyconsent() {
		$type = hikaInput::get()->getString('type', 'registration');
		$userClass = hikashop_get('class.user');
		$privacy = $userClass->getPrivacyConsentSettings($type);
		if (empty($privacy))
			return;

		if($privacy['type'] == 'menu_item') {
			if(empty($privacy['url']))
				return;
			$app = JFactory::getApplication();
			$app->redirect($privacy['url']);
		} else {
			if (empty($privacy['id']))
				return;

			$db = JFactory::getDBO();
			$sql = 'SELECT * FROM #__content WHERE id = ' . intval($privacy['id']);
			$db->setQuery($sql);
			$data = $db->loadObject();

			if (is_object($data))
				$data->text = $data->introtext . $data->fulltext;
			$this->assignRef('article', $data);
		}
	}

	public function show() {
		$checkoutHelper = hikashopCheckoutHelper::get();
		$this->checkoutHelper = $checkoutHelper;

		$imageHelper = hikashop_get('helper.image');
		$this->imageHelper = $imageHelper;

		$this->continueShopping = $this->config->get('continue_shopping');
		$this->continueShopping = hikashop_translate($this->continueShopping);
		$this->display_checkout_bar = $this->config->get('display_checkout_bar');
		$cartHelper = hikashop_get('helper.cart');
		$this->assignRef('cart', $cartHelper);

		$cart_id = $checkoutHelper->getCartId();
		$this->assignRef('cart_id', $cart_id);
		$cartIdParam = ($cart_id > 0) ? '&cart_id=' . $cart_id : '';
		$this->assignRef('cartIdParam', $cartIdParam);

		$this->initItemId();

		$this->workflow_step = hikashop_getCID('step');
		if($this->workflow_step > 0)
			$this->workflow_step--;
		if($this->workflow_step < 0)
			$this->workflow_step = 0;
		$this->step = ($this->workflow_step + 1);

		$tmpl = hikaInput::get()->getCmd('tmpl', '');
		if(in_array($tmpl, array('ajax', 'raw', 'component')))
			$this->ajax = true;

		$this->workflow = $checkoutHelper->checkout_workflow;

		JPluginHelper::importPlugin('hikashop');
		JPluginHelper::importPlugin('hikashopshipping');
		JPluginHelper::importPlugin('hikashoppayment');
		$app = JFactory::getApplication();

		$this->checkout_data = array();
		$this->hasSeparator = false;
		$obj =& $this;
		foreach($this->workflow['steps'][$this->workflow_step]['content'] as $k => &$content) {
			$task = $content['task'];
			$this->block_position = $k;
			if($task == 'separator')
				$this->hasSeparator = true;

			$ctrl = hikashop_get('helper.checkout-' . $task);
			if(!empty($ctrl)) {
				$this->checkout_data[$k] = $ctrl->display($this, $content['params']);
			} else {
				$app->triggerEvent('onInitCheckoutStep', array($task, &$obj));
			}
		}
		unset($content);

		hikashop_setPageTitle('CHECKOUT');
	}

	public function showblock() {
		$checkoutHelper = hikashopCheckoutHelper::get();
		$this->checkoutHelper = $checkoutHelper;

		$this->workflow_step = hikashop_getCID('step');
		if($this->workflow_step > 0)
			$this->workflow_step--;
		if($this->workflow_step < 0)
			$this->workflow_step = 0;
		$this->step = ($this->workflow_step + 1);

		$block_pos = hikaInput::get()->getInt('blockpos', 0);
		$block_task = hikaInput::get()->getString('blocktask', null);

		$this->block_position = $block_pos;

		$cart_id = $checkoutHelper->getCartId();
		$this->assignRef('cart_id', $cart_id);
		$cartIdParam = ($cart_id > 0) ? '&cart_id=' . $cart_id : '';
		$this->assignRef('cartIdParam', $cartIdParam);

		$this->initItemid();

		$tmpl = hikaInput::get()->getCmd('tmpl', '');
		if(in_array($tmpl, array('ajax', 'raw', 'component')))
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
		JPluginHelper::importPlugin('hikashopshipping');
		JPluginHelper::importPlugin('hikashoppayment');
		$app = JFactory::getApplication();

		$ctrl = hikashop_get('helper.checkout-' . $block_task);
		$obj =& $this;
		if(!empty($ctrl)) {
			$this->checkout_data[$block_pos] = $ctrl->display($this, $content['params']);
		} else {
			$app->triggerEvent('onInitCheckoutStep', array($block_task, &$obj));
		}
		$app->triggerEvent('onHikashopBeforeDisplayView', array(&$obj));

		echo $this->displayBlock($block_task, $block_pos, $content['params']);

		$app->triggerEvent('onHikashopAfterDisplayView', array(&$obj));

		$events = $checkoutHelper->getEvents();
		if(!empty($events)) {
			echo "\r\n".'<script type="text/javascript">'."\r\n";
			foreach($events as $k => $v) {
				echo 'window.Oby.fireAjax("'.$k.'", '.json_encode($v).');' . "\r\n";
			}
			echo "\r\n".'</script>';
		}
		$config = hikashop_config();
		if($config->get('bootstrap_forcechosen')) {
			echo "\r\n".'<script type="text/javascript">'."\r\n";
			echo '
			if(typeof(hkjQuery) != "undefined" && hkjQuery().chosen)
				hkjQuery(\'.hikashop_checkout_page select\').not(\'.chzen-done\').chosen();
			';
			echo "\r\n".'</script>';
		}
		$this->displayView = false;
		return true;
	}

	function getDescription(&$method) {
		$name = 'shipping_description';
		if(!empty($method->payment_id))
			$name = 'payment_description';
		return preg_replace('@(((?>src|href)=")((?!http|#)[^"]+"))@', '$2' . JURI::base() . '$3', $method->$name);

	}

	public function displayBlock($layout, $pos, $options) {
		$ctrl = hikashop_get('helper.checkout-' . $layout);

		$app = JFactory::getApplication();
		$obj =& $this;
		if(!empty($ctrl)) {
			$previous_options = null;
			if(!empty($this->options))
				$previous_options = $this->options;

			$this->options = $options;
			$this->module_position = (int)$pos;

			$app->triggerEvent('onBeforeCheckoutViewDisplay', array($layout, &$obj));

			$this->setLayout('show_block_' . $layout);
			$ret = $this->loadTemplate();


			$app->triggerEvent('onAfterCheckoutViewDisplay', array($layout, &$obj, &$ret));

			$this->options = $previous_options;

		} else {
			$ret = '';
			$app->triggerEvent('onCheckoutStepDisplay', array($layout, &$ret, &$obj, $pos, $options));
		}
		if(!empty($options['process_content_tags'])) {
			$ret = JHTML::_('content.prepare', $ret);
		}
		return $ret;
	}


	public function getGrid() {
		if(empty($this->options['type']))
			return;

		$StepViews = $this->checkoutHelper->checkout_workflow['steps'][$this->workflow_step]['content'];
		$flow = array();
		foreach($StepViews as $k => $view) {
			if($view['task'] == 'separator')
				$flow[$k] = $view['params']['type'];
		}
		if(!count($flow))
			return;
		$columns = 1;
		$stop = false;
		foreach($flow as $k => $sep) {
			if($this->module_position < $k)
				$stop = true;
			if($sep == 'horizontal') {
				if($stop)
					break;
				$columns = 1;
				continue;
			}
			$columns++;
		}

		$span = 1;
		$row_fluid = 12;
		switch($columns) {
			case 12:
			case 6:
			case 4:
			case 3:
			case 2:
			case 1:
				$row_fluid = 12;
				$span = $row_fluid / $columns;
				break;
			case 10:
			case 8:
			case 7:
				$row_fluid = $columns;
				$span = 1;
				break;
			case 5:
				$row_fluid = 10;
				$span = 2;
				break;
			case 9: // special case
				$row_fluid = 10;
				$span = 1;
				break;
		}

		return array($row_fluid, $span);
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

	public function addOptionPriceToProduct(&$productPrice, &$optionPrice) {
		foreach(get_object_vars($productPrice) as $key => $value) {
			if($key == 'unit_price')
				$this->addOptionPriceToProduct($productPrice->$key, $optionPrice->$key);
			if(strpos($key, 'price_value') === false)
				continue;
			$productPrice->$key += (float)hikashop_toFloat(@$optionPrice->$key);
		}
	}

	public function loadFields() {
		$products = null;
		if(!isset($this->extraFields['product'])){
			if(empty($this->fieldClass))
				$this->fieldClass = hikashop_get('class.field');
			if(!empty($this->checkoutHelper)) {
				$cart = $this->checkoutHelper->getCart();
				$products =& $cart->products;
			}
			$this->extraFields['product'] = $this->fieldClass->getFields('display:checkout=1', $products, 'product');
		}

		if(!hikashop_level(2) || !empty($this->extraFields['item']))
			return;
		if(empty($this->fieldClass))
			$this->fieldClass = hikashop_get('class.field');

		if(empty($products) && !empty($this->checkoutHelper)) {
			$cart = $this->checkoutHelper->getCart();
			$products =& $cart->products;
		}
		$this->extraFields['item'] = $this->fieldClass->getFields('display:checkout=1', $products, 'item');

	}

	public function state() {
		$namekey = hikaInput::get()->getCmd('namekey','');
		if(!headers_sent()) {
			header('Content-Type:text/html; charset=utf-8');
		}

		if(empty($namekey)) {
			echo '<span class="state_no_country">'.JText::_('PLEASE_SELECT_COUNTRY_FIRST').'</span>';
			exit;
		}

		$field_namekey = hikaInput::get()->getString('field_namekey', '');
		if(empty($field_namekey))
			$field_namekey = 'address_state';

		$field_id = hikaInput::get()->getString('field_id', '');
		if(empty($field_id))
			$field_id = 'address_state';

		$field_type = hikaInput::get()->getString('field_type', '');
		if(empty($field_type))
			$field_type = 'address';

		$id = hikaInput::get()->getInt('state_field_id', 0);
		$field_options = '';
		if($id){
			$class = hikashop_get('class.field');
			$field = $class->get($id);
			$field_options = $field->field_options;
		}

		$countryType = hikashop_get('type.country');
		echo $countryType->displayStateDropDown($namekey, $field_id, $field_namekey, $field_type, '', $field_options);
		exit;
	}

	public function end() {
		$html = hikaInput::get()->getRaw('hikashop_plugins_html', '');
		$this->assignRef('html', $html);

		$noform = hikaInput::get()->getInt('noform', 1);
		$this->assignRef('noform', $noform);

		$order_id = hikaInput::get()->getInt('order_id');
		if(empty($order_id)) {
			$app = JFactory::getApplication();
			$order_id = $app->getUserState('com_hikashop.order_id');
		}
		$order = null;
		if(!empty($order_id)) {
			$orderClass = hikashop_get('class.order');
			$order = $orderClass->loadFullOrder($order_id, false, true);
		}

		$this->assignRef('order',$order);
		$this->_orderURL($order);
	}

	public function after_end() {
		$order_id = hikaInput::get()->getInt('order_id');
		if(empty($order_id)) {
			$app = JFactory::getApplication();
			$order_id = $app->getUserState('com_hikashop.order_id');
		}

		$order = null;
		if(!empty($order_id)) {
			$orderClass = hikashop_get('class.order');
			$order = $orderClass->loadFullOrder($order_id, false, true);
		}

		JPluginHelper::importPlugin('hikashopshipping');
		JPluginHelper::importPlugin('hikashoppayment');
		$this->assignRef('order', $order);
		$this->_orderURL($order);

	}

	protected function _orderURL(&$order){
		$user = JFactory::getUser();
		global $Itemid;
		$url_itemid = (!empty($Itemid)) ? '&Itemid='.$Itemid : '';
		if(!$user->guest){
			$url = hikashop_completeLink('order&task=show&cid='.@$order->order_id.$url_itemid);
		}else{
			$url = hikashop_completeLink('order&task=show&cid='.@$order->order_id.'&order_token='.@$order->order_token.$url_itemid);
		}
		$this->assignRef('url', $url);
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
