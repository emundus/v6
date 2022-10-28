<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php

$app = JFactory::getApplication();
$config = hikamarket::config();
$shopConfig = hikamarket::config(false);
$orderClass = hikamarket::get('shop.class.order');
$imageHelper = hikamarket::get('shop.helper.image');
$productClass = hikamarket::get('shop.class.product');
$fieldsClass = hikamarket::get('shop.class.field');

global $Itemid;
$url_itemid = '';
if(!empty($Itemid))
	$url_itemid = '&Itemid=' . $Itemid;

$customer = $data->customer;

if(!isset($data->order))
	$data->order = new stdClass();
$fs = array('order_number','order_discount_tax','order_shipping_tax','order_payment_tax','order_full_price','order_shipping_price','order_payment_price','order_discount_price','order_currency_id','order_status');
foreach($fs as $f) {
	if(isset($data->$f) && !isset($data->order->$f))
		$data->order->$f = $data->$f;
	if(isset($data->old->$f) && !isset($data->order->$f))
		$data->order->$f = $data->old->$f;
}

if(hikamarket::isAdmin()) {
	$view = 'shop.order';
	$order_url = HIKASHOP_LIVE.'index.php?option=com_hikamarket&ctrl=order&task=show&cid=' . (int)$data->order_id;
} else {
	$view = 'shop.address';
	$order_url = hikamarket::completeLink('order&task=show&cid=' . (int)$data->order_id);
}

$order_number = isset($data->order->order_number) ? $data->order->order_number : @$data->order->old->order_number;

$url = '<a href="' . $order_url . '">' . $order_number . '</a>';
$data->order->order_url = $order_url;

$data->cart = $orderClass->loadFullOrder($data->order_id, true, false);
$data->cart->coupon = new stdClass();

if(hikashop_level(2)) {
	$null = null;
	$itemFields = $fieldsClass->getFields('display:mail_status_notif=1', $data->cart->products, 'item');
}

$price = new stdClass();
$tax = $data->cart->order_subtotal - $data->cart->order_subtotal_no_vat + $data->order->order_discount_tax + $data->order->order_shipping_tax + $data->order->order_payment_tax;
$price->price_value = $data->order->order_full_price - $tax;
$price->price_value_with_tax = $data->order->order_full_price;
$data->cart->full_total = new stdClass;
$data->cart->full_total->prices = array($price);
$data->cart->coupon->discount_value =& $data->order->order_discount_price;

$colspan = 4;
$vendor_name = $data->vendor->vendor_name;

$customer_name = @$data->customer->name;
if(empty($customer_name))
	$customer_name = @$data->cart->billing_address->address_firstname;

$vendor_access = hikamarket::getAclVendor($data->vendor);
$acl = array(
	'notify' => hikamarket::aclTest('order/notify', $vendor_access),
	'billingaddress' => hikamarket::aclTest('order/show/billingaddress', $vendor_access),
	'shippingaddress' => hikamarket::aclTest('order/show/shippingaddress', $vendor_access),
	'customfields' => hikamarket::aclTest('order/show/customfields', $vendor_access),
);

$vars = array(
	'LIVE_SITE' => HIKASHOP_LIVE,
	'URL' => $order_url,
	'ORDER_PRODUCT_CODE' => (bool)$shopConfig->get('show_code', false),
	'order' => $data->cart,
	'billing_address' => @$data->cart->billing_address,
	'shipping_address' => @$data->cart->shipping_address,
	'vendor' => $data->vendor,
	'customer' => @$data->customer,
	'acl.address' => !$acl['notify'] || $acl['billingaddress'] || $acl['shippingaddress'],
	'acl.billingaddress' => !$acl['notify'] || $acl['billingaddress'],
	'acl.shippingaddress' => !$acl['notify'] || $acl['shippingaddress'],
);

$texts = array(
	'BILLING_ADDRESS' => JText::_('HIKASHOP_BILLING_ADDRESS'),
	'SHIPPING_ADDRESS' => JText::_('HIKASHOP_SHIPPING_ADDRESS'),
	'SUMMARY_OF_YOUR_SALE' => JText::_('SUMMARY_OF_YOUR_SALE'),
	'MAIL_HEADER' => JText::_('HIKASHOP_MAIL_HEADER'),
	'PRODUCT_NAME' => JText::_('CART_PRODUCT_NAME'),
	'PRODUCT_CODE' => JText::_('CART_PRODUCT_CODE'),
	'PRODUCT_PRICE' => JText::_('CART_PRODUCT_UNIT_PRICE'),
	'PRODUCT_QUANTITY' => JText::_('CART_PRODUCT_QUANTITY'),
	'PRODUCT_TOTAL' => JText::_('HIKASHOP_TOTAL'),
	'ADDITIONAL_INFORMATION' => JText::_('ADDITIONAL_INFORMATION'),

	'ORDER_TITLE' => JText::_('YOUR_SALE'),
	'HI_VENDOR' => JText::sprintf('HI_VENDOR', $vendor_name),
	'ORDER_CHANGED' => JText::sprintf('ORDER_STATUS_CHANGED_TO', $url, $data->mail_status),
	'SALE_BEGIN_MESSAGE' => JText::sprintf('MAIL_SALE_BEGIN_MESSAGE', $order_number, $customer_name, HIKASHOP_LIVE),
	'SALE_END_MESSAGE' => JText::sprintf('MAIL_SALE_END_MESSAGE', HIKASHOP_LIVE) . '<br/>' . JText::sprintf('BEST_REGARDS_VENDOR', $vendor_name),
	'CUSTOMFIELD_NAME' => '',
	'FOOTER_COLSPAN' => 3,
);

if(!empty($data->usermsg->usermsg))
	$texts['ORDER_CHANGED'] = $data->usermsg->usermsg;

$templates = array();

$products_ids = array();
foreach($data->cart->products as $item) {
	$products_ids[] = $item->product_id;
}
$productClass->getProducts($products_ids);

$cartProducts = array();
$cartFooters = array();
if(!empty($data->cart->products)) {
	$fields = null;
	if(hikashop_level(1) && $acl['customfields']) {
		$null = null;
		$fields = $fieldsClass->getFields('display:mail_status_notif=1', $null, 'product');
		if(!empty($fields)){
			$product_customfields = array();
			$usefulFields = array();
			foreach($fields as $field) {
				$namekey = $field->field_namekey;
				foreach($productClass->all_products as $product) {
					if(!empty($product->$namekey)) {
						$usefulFields[] = $field;
						break;
					}
				}
			}
			$fields = $usefulFields;
		}
		if(!empty($fields)) {
			foreach($fields as $field) {
				$texts['FOOTER_COLSPAN']++;
				$texts['CUSTOMFIELD_NAME'] .= '<td style="border-bottom:1px solid #ddd;padding-bottom:3px;text-align:left;color:#1c8faf !important;font-size:12px;font-weight:bold;">'.$fieldsClass->getFieldName($field).'</td>';
			}
		}
	}

	$group = $shopConfig->get('group_options', 0);
	$subtotal = 0;
	foreach($data->cart->products as $item) {
		if($group && $item->order_product_option_parent_id)
			continue;

		$product = @$productClass->all_products[$item->product_id];

		$cartProduct = array(
			'PRODUCT_CODE' => $item->order_product_code,
			'PRODUCT_QUANTIY' => $item->order_product_quantity,
			'PRODUCT_IMG' => '',
			'item' => $item,
			'product' => $product,
		);

		if(!empty($item->images[0]->file_path)) {
			$img = $imageHelper->getThumbnail($item->images[0]->file_path, array(50, 50), array('forcesize' => true, 'scale' => 'outside'));
			if($img->success) {
				$image = str_replace('../', HIKASHOP_LIVE, $img->url);
				$cartProduct['PRODUCT_IMG'] = '<img src="'.$image.'" alt="" style="float:left;margin-top:3px;margin-bottom:3px;margin-right:6px;max-height:50px;max-width:50px;"/>';
			}
		}

		$t = '<p>' . $item->order_product_name;
		if($group) {
			$display_item_price = false;
			foreach($data->cart->products as $j => $optionElement) {
				if($optionElement->order_product_option_parent_id != $item->order_product_id) continue;
				if($optionElement->order_product_price > 0)
					$display_item_price = true;
			}
			if($display_item_price) {
				if($shopConfig->get('price_with_tax'))
					$t .= ' '.$currencyHelper->format($item->order_product_price + $item->order_product_tax, $data->order->order_currency_id);
				else
					$t .= ' '.$currencyHelper->format($item->order_product_price, $data->order->order_currency_id);
			}
		}
		$t .= '</p>';

		if(!empty($itemFields)) {
			foreach($itemFields as $field) {
				$namekey = $field->field_namekey;
				if(!isset($item->$namekey) || !strlen($item->$namekey))
					continue;
				$t .= '<p>' . $fieldsClass->getFieldName($field) . ': ' . $fieldsClass->show($field, $item->$namekey, 'user_email') . '</p>';
			}
		}

		$cartProduct['CUSTOMFIELD_VALUE'] = '';
		if(!empty($fields) && hikashop_level(1)){
			foreach($fields as $field){
				$namekey = $field->field_namekey;
				$productData = @$productClass->all_products[$item->product_id];
				$cartProduct['CUSTOMFIELD_VALUE'] .= '<td style="border-bottom:1px solid #ddd;padding-bottom:3px;text-align:right">'.(empty($productData->$namekey)?'':$fieldsClass->show($field,$productData->$namekey)).'</td>';
			}
		}

		if($group) {
			foreach($data->cart->products as $j => $optionElement) {
				if($optionElement->order_product_option_parent_id != $item->order_product_id) continue;

				$item->order_product_price +=$optionElement->order_product_price;
				$item->order_product_tax +=$optionElement->order_product_tax;
				$item->order_product_total_price+=$optionElement->order_product_total_price;
				$item->order_product_total_price_no_vat+=$optionElement->order_product_total_price_no_vat;

				$t .= '<p class="hikashop_order_option_name">' . $optionElement->order_product_name;
				if($optionElement->order_product_price > 0) {
					if($shopConfig->get('price_with_tax'))
						$t .= ' ( + '.$currencyHelper->format($optionElement->order_product_price + $optionElement->order_product_tax,$data->order->order_currency_id).' )';
					else
						$t .= ' ( + '.$currencyHelper->format($optionElement->order_product_price,$data->order->order_currency_id).' )';
				}
				$t .= '</p>';
			}
		}
		$cartProduct['PRODUCT_NAME'] = $t;

		$t = '';
		$statusDownload = explode(',',$shopConfig->get('order_status_for_download','confirmed,shipped'));
		if(!empty($item->files) && in_array($data->order->order_status, $statusDownload)) {
		}
		$cartProduct['PRODUCT_DOWNLOAD'] = $t;

		if($shopConfig->get('price_with_tax')) {
			$unit_price = $currencyHelper->format($item->order_product_price+$item->order_product_tax, $data->order->order_currency_id);
			$total_price = $currencyHelper->format($item->order_product_total_price, $data->order->order_currency_id);
			$subtotal += $item->order_product_total_price;
		} else {
			$unit_price = $currencyHelper->format($item->order_product_price, $data->order->order_currency_id);
			$total_price = $currencyHelper->format($item->order_product_total_price_no_vat, $data->order->order_currency_id);
			$subtotal += $item->order_product_total_price_no_vat;
		}
		$cartProduct['PRODUCT_PRICE'] = $unit_price;
		$cartProduct['PRODUCT_TOTAL'] = $total_price;

		$cartProducts[] = $cartProduct;
	}
	$templates['PRODUCT_LINE'] = $cartProducts;

	if(bccomp($data->order->order_discount_price,0,5) || bccomp($data->order->order_shipping_price,0,5) || bccomp($data->order->order_payment_price,0,5) || ($data->cart->full_total->prices[0]->price_value!=$data->cart->full_total->prices[0]->price_value_with_tax) || !empty($data->cart->additional)) {
		$cartFooters[] = array(
			'NAME' => JText::_('SUBTOTAL'),
			'VALUE' => $currencyHelper->format($subtotal,$data->order->order_currency_id)
		);
	}
	if(bccomp($data->order->order_discount_price,0,5)) {
		if($shopConfig->get('price_with_tax'))
			$t = $currencyHelper->format($data->order->order_discount_price * -1, $data->order->order_currency_id);
		else
			$t = $currencyHelper->format(($data->order->order_discount_price - @$data->order->order_discount_tax) * -1, $data->order->order_currency_id);
		$cartFooters[] = array(
			'NAME' => JText::_('HIKASHOP_COUPON'),
			'VALUE' => $t
		);
	}
	if(bccomp($data->order->order_shipping_price,0,5)) {
		if($shopConfig->get('price_with_tax'))
			$t = $currencyHelper->format($data->order->order_shipping_price, $data->order->order_currency_id);
		else
			$t = $currencyHelper->format($data->order->order_shipping_price - @$data->order->order_shipping_tax, $data->order->order_currency_id);
		$cartFooters[] = array(
			'NAME' => JText::_('HIKASHOP_SHIPPING'),
			'VALUE' => $t
		);
	}
	if(bccomp($data->order->order_payment_price,0,5)) {
		if($shopConfig->get('price_with_tax'))
			$t = $currencyHelper->format($data->cart->order_payment_price, $data->cart->order_currency_id);
		else
			$t = $currencyHelper->format($data->cart->order_payment_price - @$data->cart->order_payment_tax, $data->cart->order_currency_id);
		$cartFooters[] = array(
			'NAME' => JText::_('HIKASHOP_PAYMENT'),
			'VALUE' => $t
		);
	}
	if(!empty($data->cart->additional)) {
		$exclude_additionnal = explode(',', $shopConfig->get('order_additional_hide', ''));
		foreach($data->cart->additional as $additional) {
			if(in_array($additional->order_product_name, $exclude_additionnal))
				continue;

			if(!empty($additional->order_product_price) || empty($additionaltionnal->order_product_options)) {
				if($shopConfig->get('price_with_tax'))
					$t = $currencyHelper->format($additional->order_product_price + @$additional->order_product_tax, $data->order->order_currency_id);
				else
					$t = $currencyHelper->format($additional->order_product_price, $data->order->order_currency_id);
			} else {
				$t = $additional->order_product_options;
			}
			$cartFooters[] = array(
				'NAME' => JText::_($additional->order_product_name),
				'VALUE' => $t
			);
		}
	}

	if($data->cart->full_total->prices[0]->price_value != $data->cart->full_total->prices[0]->price_value_with_tax) {
		if($shopConfig->get('detailed_tax_display') && !empty($data->cart->order_tax_info)) {
			foreach($data->cart->order_tax_info as $tax) {
				$cartFooters[] = array(
					'NAME' => $tax->tax_namekey,
					'VALUE' => $currencyHelper->format($tax->tax_amount, $data->order->order_currency_id)
				);
			}
		} else {
			$cartFooters[] = array(
				'NAME' => JText::_('ORDER_TOTAL_WITHOUT_VAT'),
				'VALUE' => $currencyHelper->format($data->cart->full_total->prices[0]->price_value, $data->order->order_currency_id)
			);
		}
		$cartFooters[] = array(
			'NAME' => JText::_('ORDER_TOTAL_WITH_VAT'),
			'VALUE' => $currencyHelper->format($data->cart->full_total->prices[0]->price_value_with_tax, $data->order->order_currency_id)
		);
	} else {
		$cartFooters[] = array(
			'NAME' => JText::_('HIKAM_CUSTOMER_FINAL_TOTAL'),
			'VALUE' => $currencyHelper->format($data->cart->full_total->prices[0]->price_value_with_tax, $data->order->order_currency_id)
		);
	}

	$vendor_price = (float)hikamarket::toFloat($data->cart->order_vendor_price);
	if($vendor_price < 0)
		$vendor_price += $data->cart->full_total->prices[0]->price_value_with_tax;
	if($vendor_price != 0) {
		$cartFooters[] = array(
			'NAME' => JText::_('HIKAM_VENDOR_FINAL_TOTAL'),
			'VALUE' => $currencyHelper->format($vendor_price, $data->order->order_currency_id)
		);
	}

	$templates['ORDER_FOOTER'] = $cartFooters;
}

if(!empty($data->cart->order_payment_method)) {
	if(!is_numeric($data->cart->order_payment_id)) {
		$vars['PAYMENT'] = $data->cart->order_payment_method.' '.$data->cart->order_payment_id;
	} else {
		$paymentClass = hikamarket::get('shop.class.payment');
		$payment = $paymentClass->get($data->cart->order_payment_id);
		if(!empty($payment))
			$vars['PAYMENT'] = $payment->payment_name;
		unset($paymentClass);
	}
}

if(!empty($data->cart->order_shipping_id)) {
	$shippingClass = hikamarket::get('shop.class.shipping');
	if(!empty($data->cart->order_shipping_method)) {
		if(!is_numeric($data->cart->order_shipping_id)) {
			$shipping_name = $shippingClass->getShippingName($data->cart->order_shipping_method, $data->cart->order_shipping_id);
			$vars['SHIPPING'] = $shipping_name;
			$vars['SHIPPING_TXT'] = $vars['SHIPPING'];
		} else {
			$shipping = $shippingClass->get($data->cart->order_shipping_id);
			$vars['SHIPPING'] = $shipping->shipping_name;
			$vars['SHIPPING_TXT'] = $vars['SHIPPING'];
		}
	} else {
		$shippings_data = array();
		$shipping_ids = explode(';', $data->cart->order_shipping_id);
		$shippingClass = hikamarket::get('shop.class.shipping');
		foreach($shipping_ids as $key) {
			$shipping_data = '';
			list($k, $w) = explode('@', $key);
			$shipping_id = $k;
			if(isset($data->cart->shippings[$shipping_id])) {
				$shipping = $data->cart->shippings[$shipping_id];
				$shipping_data = $shipping->shipping_name;
			} else {
				foreach($data->cart->products as $order_product) {
					if($order_product->order_product_shipping_id == $key) {
						if(!is_numeric($order_product->order_product_shipping_id)) {
							$shipping_name = $shippingClass->getShippingName($order_product->order_product_shipping_method, $shipping_id);
							$shipping_data = $shipping_name;
						} else {
							$shipping_method_data = $shippingClass->get($shipping_id);
							$shipping_data = $shipping_method_data->shipping_name;
						}
						break;
					}
				}
				if(empty($shipping_data))
					$shipping_data = '[ ' . $key . ' ]';
			}
			if(isset($data->cart->order_shipping_params->prices[$key])) {
				$price_params = $data->cart->order_shipping_params->prices[$key];
				if($shopConfig->get('price_with_tax'))
					$shipping_data .= ' (' . $currencyHelper->format($price_params->price_with_tax, $data->cart->order_currency_id) . ')';
				else
					$shipping_data .= ' (' . $currencyHelper->format($price_params->price_with_tax - @$price_params->tax, $data->cart->order_currency_id) . ')';
			}
			$shippings_data[] = $shipping_data;
		}
		if(!empty($shippings_data)) {
			$vars['SHIPPING'] = '<ul><li>'.implode('</li><li>', $shippings_data).'</li></ul>';
			$vars['SHIPPING_TXT'] = ' - ' . implode("\r\n - ", $shippings_data);
		}
	}
	unset($shippingClass);
} else {
	$vars['SHIPPING'] = '';
}

ob_start();

	$sep = '';
	if(hikashop_level(2)) {
		$fields = $fieldsClass->getFields('display:mail_status_notif=1',$data,'order','');
		foreach($fields as $fieldName => $oneExtraField) {
			if(isset($data->$fieldName) && !isset($data->cart->$fieldName))
				$data->cart->$fieldName = $data->$fieldName;
			if(empty($data->cart->$fieldName))
				continue;
			echo $sep . $fieldsClass->trans($oneExtraField->field_realname).' : '.$fieldsClass->show($oneExtraField, $data->cart->$fieldName,'user_email');
			$sep = '<br />';
		}
	}

	JPluginHelper::importPlugin('hikashop');
	JFactory::getApplication()->triggerEvent('onAfterOrderProductsListingDisplay', array(&$data->cart, 'email_notification_html'));

$content = ob_get_clean();
$vars['ORDER_SUMMARY'] = $content;

$vars['BILLING_ADDRESS'] = '';
$vars['SHIPPING_ADDRESS'] = '';

$params = null;
$js = '';
$template = trim(hikamarket::getLayout($view, 'address_template', $params, $js));
if(!empty($data->cart->billing_address) && !empty($data->cart->fields)) {
	$billing = $template;
	foreach($data->cart->fields as $field) {
		$fieldname = $field->field_namekey;
		if(!empty($data->cart->billing_address->$fieldname))
			$billing = str_replace('{' . $fieldname . '}', $fieldsClass->show($field, $data->cart->billing_address->$fieldname, 'user_email'), $billing);
	}
	$vars['BILLING_ADDRESS'] = str_replace(array("\r\n","\r","\n"),'<br/>',preg_replace('#{(?:(?!}).)*}#i','',$billing));
}
if(!empty($data->cart->override_shipping_address)) {
	$vars['SHIPPING_ADDRESS'] =  $data->cart->override_shipping_address;
} elseif(!empty($data->cart->order_shipping_id) && !empty($data->cart->shipping_address)) {
	$shipping = $template;
	foreach($data->cart->fields as $field) {
		$fieldname = $field->field_namekey;
		if(!empty($data->cart->shipping_address->$fieldname))
			$shipping = str_replace('{' . $fieldname . '}', $fieldsClass->show($field, $data->cart->shipping_address->$fieldname, 'user_email'), $shipping);
	}
	$vars['SHIPPING_ADDRESS'] = str_replace(array("\r\n", "\r", "\n"), '<br/>', preg_replace('#{(?:(?!}).)*}#i', '', $shipping));
} else {
	$vars['SHIPPING_ADDRESS'] = $vars['BILLING_ADDRESS'];
}
