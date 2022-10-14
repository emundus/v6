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
class plgHikamarketVendorpaytaxes extends JPlugin {
	public function __construct(&$subject, $config) {
		parent::__construct($subject, $config);
	}

	private function init() {
		if(isset($this->params))
			return;
		$plugin = JPluginHelper::getPlugin('hikamarket', 'vendorpaytaxes');
		$this->params = new JRegistry(@$plugin->params);
	}

	public function onBeforeVendorPay(&$order, &$vendor, &$orders, &$pay_orders, &$do) {
		$this->init();

		$config = hikamarket::config();
		$shopOrderClass = hikamarket::get('shop.class.order');
		$currencyClass = hikamarket::get('shop.class.currency');
		$addressClass = hikamarket::get('shop.class.address');
		$zoneClass = hikamarket::get('shop.class.zone');

		$tax_id = (int)$this->params->get('tax_id', 0);
		if(empty($tax_id))
			return;

		$config =& hikashop_config();
		$config->set('floating_tax_prices', 0);

		$address = $addressClass->get($order->order_billing_address_id);
		$field = 'address_country';
		if(!empty($address->address_state))
			$field = 'address_state';
		$zones[$address->$field] = $zoneClass->get($address->$field);
		$zone_id = $zones[$address->$field]->zone_id;

		$round = $currencyClass->getRounding($order->order_currency_id, true);

		$feeMode = ($config->get('market_mode', 'fee') == 'fee');

		foreach($order->cart->products as &$p) {
			if(!empty($p->order_product_tax))
				continue;
			$p->order_product_tax = $currencyClass->getTaxedPrice($p->order_product_price, $zone_id, $tax_id, $round) - $p->order_product_price;
			$p->order_product_tax_info = $currencyClass->taxRates;
		}
		unset($product);

		$shopOrderClass->recalculateFullPrice($order, $order->cart->products);
	}
}
