<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2016 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class plgHikashopShippingmanual_prices extends JPlugin {

	function __construct(&$subject, $config) {
		parent::__construct($subject, $config);
	}

	function _getShippings($product, $vendor = null) {
		if(empty($product->product_id))
			$product_id = 0;
		else
			$product_id = (int)$product->product_id;

		$extra_filters = '';
		if($vendor !== null && $vendor > 1)
			$extra_filters = ' AND a.shipping_vendor_id IN (-1, 0, ' . (int)$vendor . ') ';

		$db = JFactory::getDBO();
		$query = 'SELECT b.*, a.*, c.currency_symbol FROM ' . hikashop_table('shipping') . ' AS a '.
		' LEFT JOIN '.hikashop_table('shipping_price').' AS b ON a.shipping_id = b.shipping_id AND b.shipping_price_ref_id = '.$product_id.
		' INNER JOIN '. hikashop_table('currency').' AS c ON c.currency_id = a.shipping_currency_id '.
		' WHERE (a.shipping_params LIKE \'%' . hikashop_getEscaped('s:20:"shipping_per_product";s:1:"1"') . '%\' OR a.shipping_params LIKE \'%' . hikashop_getEscaped('s:20:"shipping_per_product";i:1') . '%\') '.
		' AND (b.shipping_price_ref_id IS NULL OR (b.shipping_price_ref_id = ' . $product_id . ' AND b.shipping_price_ref_type = \'product\')) '.
		$extra_filters.
		'ORDER BY a.shipping_id, b.shipping_price_min_quantity';

		$db->setQuery($query);
		$shippings = $db->loadObjectList();
		$temp_shipping = new stdClass();
		foreach($shippings as $key => $shipping) {
			$temp_shipping = hikashop_unserialize($shipping->shipping_params);

			if(!empty($temp_shipping->shipping_vendor_filter) && !empty($temp_shipping->shipping_warehouse_filter))
				$temp_shipping->shipping_warehouse_filter = substr($temp_shipping->shipping_warehouse_filter, 0, 1);

			if(!empty($product->product_warehouse_id) && $product->product_warehouse_id != 0) {
				if(!empty($temp_shipping->shipping_warehouse_filter) && ($temp_shipping->shipping_warehouse_filter != $product->product_warehouse_id) && ($temp_shipping->shipping_warehouse_filter != 0))
					unset($shippings[$key]);
			} else {
				if(!empty($temp_shipping->shipping_warehouse_filter) && ($temp_shipping->shipping_warehouse_filter != 0))
					unset($shippings[$key]);
			}

			if(!empty($product->product_vendor_id) && $product->product_vendor_id != 0) {
				if(!empty($temp_shipping->shipping_vendor_filter) && ($temp_shipping->shipping_vendor_filter != $product->product_vendor_id) && ($temp_shipping->shipping_vendor_filter != 0))
					unset($shippings[$key]);
			} else {
				if(!empty($temp_shipping->shipping_vendor_filter) && ($temp_shipping->shipping_vendor_filter != 0))
					unset($shippings[$key]);
			}
		}
		return $shippings;
	}

	function onProductBlocksDisplay(&$product, &$html) {
		$shippings = $this->_getShippings($product);
		if(empty($shippings))
			return;

		$currencyHelper = hikashop_get('class.currency');

		ob_start();
		include dirname(__FILE__).DS.'shippingprices_views'.DS.'backend_product.php';
		$data = ob_get_clean();
		$html[] = $data;
	}

	function onMarketProductBlocksDisplay(&$product, &$html) {
		if(!defined('HIKAMARKET_COMPONENT'))
			return;

		$marketConfig = hikamarket::config();
		if(!$marketConfig->get('frontend_edition',0)) return;
		if(!hikamarket::acl('product_edit_plugin_shippingprices')) return;

		$vendor_id = hikamarket::loadVendor(false);
		$shippings = $this->_getShippings($product, $vendor_id);
		if(empty($shippings))
			return;

		$currencyHelper = hikashop_get('class.currency');

		if(empty($product->product_id))
			$product_id = 0;
		else
			$product_id = (int)$product->product_id;

		ob_start();
		include dirname(__FILE__).DS.'shippingprices_views'.DS.'market_product.php';
		$data = ob_get_clean();
		$html[] = $data;
		return;
	}

	function onMarketAclPluginListing(&$categories) {
		$categories['product'][] = 'shippingprices';
	}

	function onAfterProductCreate(&$product) {
		return $this->onAfterProductUpdate($product, true);
	}

	function onAfterProductUpdate(&$product, $create = false) {
		$app = JFactory::getApplication();
		$vendor = null;
		if(!$app->isAdmin()) {
			if(!defined('HIKAMARKET_COMPONENT'))
				return;

			$marketConfig = hikamarket::config();
			if(!$marketConfig->get('frontend_edition',0)) return;
			if(!hikamarket::acl('product_edit_plugin_shippingprices')) return;

			$vendor = hikamarket::loadVendor(false);
		}

		$formData = JRequest::getVar('shipping_prices', array(), '', 'array');
		if(empty($formData))
			return;

		if(!$app->isAdmin()) {
			if(isset($formData[$product->product_id]))
				$formData = $formData[$product->product_id];
			else if(isset($formData[0]) && $create)
				$formData = $formData[0];
			else
				$formData = array();
		}

		if(empty($product->product_id))
			return;

		$extra_filters = '';
		if($vendor !== null && $vendor > 1)
			$extra_filters = ' AND a.shipping_vendor_id IN (-1, 0, ' . (int)$vendor . ') ';

		$db = JFactory::getDBO();
		$query = 'SELECT b.*, a.*, c.currency_symbol FROM ' . hikashop_table('shipping') . ' AS a INNER JOIN '.
			hikashop_table('shipping_price').' AS b ON a.shipping_id = b.shipping_id INNER JOIN '.
			hikashop_table('currency').' AS c ON c.currency_id = a.shipping_currency_id '.
			'WHERE a.shipping_params LIKE '.
			$db->Quote('%s:20:"shipping_per_product";s:1:"1"%') . ' AND b.shipping_price_ref_id = ' . $product->product_id . ' AND b.shipping_price_ref_type = \'product\' '.
			$extra_filters.
			'ORDER BY a.shipping_id, b.shipping_price_min_quantity';
		$db->setQuery($query);
		$shippings = $db->loadObjectList('shipping_price_id');

		$toRemove = array_keys($shippings);
		if(!empty($toRemove)) {
			$toRemove = array_combine($toRemove, $toRemove);
		}
		$toInsert = array();


		$checks = array();
		foreach($formData as &$data) {
			if(is_string($data)) {
				$data = null;
			} else {
				if(empty($checks[$data['shipping_id']])) {
					$checks[$data['shipping_id']] = array();
				}
				if(!isset($checks[$data['shipping_id']][$data['qty']])) {
					$checks[$data['shipping_id']][$data['qty']] = true;
				} else {
					$data = null;
				}
			}
			unset($data);
		}
		unset($checks);

		foreach($formData as $data) {
			if($data == null)
				continue;
			$shipping = null;
			if(!empty($data['id']) && isset($shippings[$data['id']]) ) {
				if(empty($data['value']) && empty($data['fee']))
					continue;

				$shipping = $shippings[$data['id']];
				unset($toRemove[$data['id']]);

				if(empty($data['qty']) || (int)$data['qty'] < 1)
					$data['qty'] = 1;

				if( (int)$shipping->shipping_price_min_quantity != (int)$data['qty'] || (float)$shipping->shipping_price_value != (float)$data['value'] || (float)$shipping->shipping_fee_value != (float)$data['fee']) {
					$query = 'UPDATE ' . hikashop_table('shipping_price') .
						' SET shipping_price_min_quantity = ' . (int)$data['qty'] . ', shipping_price_value = ' . (float)$data['value'] . ', shipping_fee_value = ' . (float)$data['fee'] .
						' WHERE shipping_price_id = ' . $data['id'] . ' AND shipping_price_ref_id = ' . $product->product_id . ' AND shipping_price_ref_type = \'product\'';
					$db->setQuery($query);
					$db->query();
				}
			} else {
				if((!empty($data['value']) || !empty($data['fee'])) && !empty($data['shipping_id']) ) {
					if(empty($data['qty']) || (int)$data['qty'] < 1)
						$data['qty'] = 1;
					$toInsert[] = (int)$data['shipping_id'].','.$product->product_id.',\'product\','.(int)$data['qty'].','.(float)$data['value'].','.(float)$data['fee'];
				}
			}
		}

		if(!empty($toRemove)) {
			$db->setQuery('DELETE FROM ' . hikashop_table('shipping_price') . ' WHERE shipping_price_ref_id = ' . $product->product_id . ' AND shipping_price_ref_type = \'product\' AND shipping_price_id IN ('.implode(',',$toRemove).')');
			$db->query();
		}
		if(!empty($toInsert)) {
			$db->setQuery('INSERT IGNORE INTO ' . hikashop_table('shipping_price') . ' (`shipping_id`,`shipping_price_ref_id`,`shipping_price_ref_type`,`shipping_price_min_quantity`,`shipping_price_value`,`shipping_fee_value`) VALUES ('.implode('),(',$toInsert).')');
			$db->query();
		}
	}
	function onHikaShopBeforeDisplayView (&$view) {
		if (!isset($view->element->product_id) )
			return;

		$ctrl = @$view->ctrl;
		$task = $view->getLayout();

		if($ctrl != "product" || $task != 'show')
			return;

		if(empty($plugin->params['displayOnFrontend'] ) )
			return;

		$shippings = $this->_getShippings($view->element);
		$shipPrices = @$view->element->prices;

		$pluginsClass = hikashop_get('class.plugins');
		$plugin = $pluginsClass->getByName('hikashop','shippingmanual_prices');

		if(!isset($plugin->params['position'] ) )
			$plugin->params['position'] = 'rightMiddle';
		if(!isset($plugin->params['DisplayMinQtity'] ) )
			$plugin->params['DisplayMinQtity'] = 1;

		$position = $plugin->params['position'];
		$display = $plugin->params['DisplayMinQtity'];


		ob_start();

		if (!empty ($shippings) && (is_array($shippings) ) ) {

			$shipData = array();
			foreach ($shippings as $v) {

				if ($v->shipping_published == 0)
					continue;

				$arrayKey = $v->shipping_name . ' ' . $v->shipping_price_id;
				$shipData[$arrayKey] = array();

				$shipParams = unserialize($v->shipping_params);

				if ( ($v->shipping_price_min_quantity > 1 ) && (isset($v->shipping_price_min_quantity) ) ) {
					$shipData[$arrayKey]['minQtity'] = $v->shipping_price_min_quantity;

					$total = ($v->shipping_price_min_quantity * $v->shipping_price_value) + $v->shipping_fee_value + $v->shipping_price;
				} else {
					$shipData[$arrayKey]['minQtity'] = 0;

					$total = $v->shipping_price_value + $v->shipping_fee_value + $v->shipping_price;
				}

				$shipData[$arrayKey]['price'] = $view->currencyHelper->format($total,$v->shipping_currency_id);

				if ($shipParams->shipping_percentage != 0) {

					$prdctPrice = 0;
					if(!empty($shipPrices)){
						foreach ($shipPrices as $v1) {
							$prdctPrice = $v1->price_value_with_tax;

							if ($v1->price_min_quantity == 0) {
								break;
							}
						}
					}

					$MainPrice = ($prdctPrice * $shipParams->shipping_percentage) / 100;

					$shipData[$arrayKey]['percent'] = (int)$shipParams->shipping_percentage;

					if ( ($v->shipping_price_min_quantity > 1 ) && (isset($v->shipping_price_min_quantity) ) ) {
						$shipData[$arrayKey]['minQtity'] = $v->shipping_price_min_quantity;

						$total = ($v->shipping_price_min_quantity * $v->shipping_price_value) + $v->shipping_fee_value + ($v->shipping_price_min_quantity * $MainPrice);
					} else {
						$shipData[$arrayKey]['minQtity'] = 0;

						$total = $v->shipping_price_value + $v->shipping_fee_value + $MainPrice;
					}

					$shipData[$arrayKey]['price'] = $view->currencyHelper->format($view->currencyHelper->round($total),$v->shipping_currency_id);
				}

				$shipData[$arrayKey]['name'] = $v->shipping_name;
			}

			include dirname(__FILE__).DS.'shippingprices_views'.DS.'frontend_product.php';
		}

		$data = ob_get_clean();

		if(!isset($view->element->extraData))
			$view->element->extraData = new stdClass();

		if(!isset($view->element->extraData->$position))
			$view->element->extraData->$position = array();
		array_push($view->element->extraData->$position, $data);
	}
}
