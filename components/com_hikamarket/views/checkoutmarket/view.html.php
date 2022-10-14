<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class checkoutmarketViewcheckoutmarket extends hikamarketView {

	protected $ctrl = 'checkout';
	protected $icon = 'checkout';

	public function display($tpl = null, $params = array()) {
		$this->params =& $params;
		$fct = $this->getLayout();
		if(method_exists($this, $fct)) {
			if($this->$fct() === false)
				return;
		}
		parent::display($tpl);
	}

	public function terms() {
		$app = JFactory::getApplication();
		$shop_terms = $app->getUserState(HIKASHOP_COMPONENT.'.checkout_terms');
		$market_terms = $app->getUserState(HIKAMARKET_COMPONENT.'.checkout_terms');

		$config = hikamarket::config();
		$this->assignRef('config', $config);
		$shopConfig = hikamarket::config(false);
		$this->assignRef('shopConfig', $shopConfig);

		$popupHelper = hikamarket::get('shop.helper.popup');
		$this->assignRef('popupHelper', $popupHelper);

		if(!class_exists('hikashopCheckoutHelper')) {
			$cart = $this->params->view->initCart();
		} else {
			$checkoutHelper = hikashopCheckoutHelper::get();
			$cart = $checkoutHelper->getCart();

			$this->step = $this->params->view->step;
			$this->module_position = $this->params->pos;
		}

		$vendors = array();
		foreach($cart->products as $product) {
			$vendor_id = (int)$product->product_vendor_id;
			$vendors[$vendor_id] = $vendor_id;
		}
		if(!isset($vendors[1]))
			$vendors[1] = 1;

		$db = JFactory::getDBO();
		$query = 'SELECT vendor_id, vendor_name, vendor_terms FROM '.hikamarket::table('vendor').' WHERE vendor_published = 1 AND vendor_id IN ('.implode(',', $vendors).')';
		$db->setQuery($query);
		$terms_content = $db->loadObjectList('vendor_id');
		$this->assignRef('terms_content', $terms_content);

		if(isset($vendors[0])) unset($vendors[0]);
		if(isset($vendors[1])) unset($vendors[1]);
		$this->assignRef('vendors', $vendors);

		$terms = array(
			'shop' => $shop_terms,
			'market' => $market_terms
		);
		$this->assignRef('terms', $terms);
	}
}
