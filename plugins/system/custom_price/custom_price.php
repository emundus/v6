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
jimport('joomla.plugin.plugin');

class plgSystemCustom_price extends JPlugin {
	protected $currencyClass = null;
	public $params = null;

	public function onBeforeCalculateProductPriceForQuantity(&$product) {
		if(empty($this->currencyClass))
			$this->currencyClass = hikashop_get('class.currency');

		$quantity = @$product->cart_product_quantity;

		if(empty($this->params)) {
			$plugin = JPluginHelper::getPlugin('system', 'custom_price');
			if(version_compare(JVERSION,'2.5','<')){
				jimport('joomla.html.parameter');
				$this->params = new JParameter($plugin->params);
			} else {
				$this->params = new JRegistry($plugin->params);
			}
		}

		$taxes = $this->params->get('taxes',0);
		$column = $this->params->get('field','amount');
		if(empty($product->$column))
			return;

		if(empty($product->prices)) {
			$price= new stdClass();
			$price->price_currency_id = hikashop_getCurrency();
			$price->price_min_quantity = 1;
			$product->prices = array($price);
		}
		if($taxes && $product->product_type == 'variant' && empty($product->product_tax_id)) {
			$productClass = hikashop_get('class.product');
			$main = $productClass->get($product->product_parent_id);
			$product->product_tax_id = $main->product_tax_id;
		}
		foreach($product->prices as $k => $price) {
			switch($taxes) {
				case 2:
					$product->prices[$k]->price_value = $this->currencyClass->getUntaxedPrice(hikashop_toFloat($product->$column),hikashop_getZone(),$product->product_tax_id);
					$product->prices[$k]->taxes = $this->currencyClass->taxRates;
					$product->prices[$k]->price_value_with_tax = hikashop_toFloat($product->$column);
					break;
				case 1:
					$product->prices[$k]->price_value = hikashop_toFloat($product->$column);
					$product->prices[$k]->price_value_with_tax = $this->currencyClass->getTaxedPrice(hikashop_toFloat($product->$column),hikashop_getZone(),$product->product_tax_id);
					$product->prices[$k]->taxes = $this->currencyClass->taxRates;
					break;
				case 0:
				default:
					$product->prices[$k]->price_value = hikashop_toFloat($product->$column);
					$product->prices[$k]->price_value_with_tax = hikashop_toFloat($product->$column);
					break;
			}
		}
	}
}
