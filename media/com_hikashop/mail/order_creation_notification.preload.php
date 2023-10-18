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

$app = JFactory::getApplication();
$config = hikashop_config();
$orderClass = hikashop_get('class.order');
$imageHelper = hikashop_get('helper.image');
$productClass = hikashop_get('class.product');
$fieldsClass = hikashop_get('class.field');

global $Itemid;
$url_itemid = '';
if(!empty($Itemid)) {
	$url_itemid = '&Itemid=' . $Itemid;
}

$order_url = $data->order_url;
$mail_status = $data->mail_status;
$customer = $data->customer;

if(!isset($data->order))
	$data->order = new stdClass();
$fs = array('order_number','order_discount_tax','order_shipping_tax','order_payment_tax','order_full_price','order_shipping_price','order_payment_price','order_discount_price','order_currency_id','order_status');
foreach($fs as $f) {
	if(!isset($data->order->$f)){
		if(isset($data->$f))
			$data->order->$f = $data->$f;
		else
			$data->order->$f = '';
	}
}

$url = $data->order->order_number;
$data->order->order_url = $order_url;

$url = '<a href="'.$order_url.'">'. $url.'</a>';

$data->cart = $orderClass->loadFullOrder($data->order_id,true,false);
$data->cart->coupon = new stdClass();

if(hikashop_level(2)) {
	$itemFields = $fieldsClass->getFields('display:mail_order_creation=1', $data->cart->products, 'item');
}

$price = new stdClass();
$tax = $data->cart->order_subtotal - $data->cart->order_subtotal_no_vat - $data->cart->order_discount_tax + $data->cart->order_shipping_tax + $data->cart->order_payment_tax;
$price->price_value = max(0, $data->cart->order_full_price - $tax);
$price->price_value_with_tax = $data->cart->order_full_price;
$data->cart->full_total = new stdClass;
$data->cart->full_total->prices = array($price);
$data->cart->coupon->discount_value =& $data->cart->order_discount_price;

if(hikashop_isClient('administrator')) {
	$view = 'order';
} else {
	$view = 'address';
}
$colspan = 4;

$customer_name = @$customer->name;
if(empty($customer_name))
	$customer_name = @$data->cart->billing_address->address_firstname.' '.@$data->cart->billing_address->address_lastname;

$vars = array(
	'LIVE_SITE' => HIKASHOP_LIVE,
	'URL' => $order_url,
	'ORDER_PRODUCT_CODE' => (bool)$config->get('show_code', false),
	'order' => $data->cart,
	'user' => $customer,
	'customer' => $customer,
	'billing_address' => @$data->cart->billing_address,
	'shipping_address' => @$data->cart->shipping_address,

	'TPL_HEADER' => (bool)@$customer->user_cms_id,
	'TPL_HEADER_URL' => $order_url,
);
$texts = array(
	'BILLING_ADDRESS' => JText::_('HIKASHOP_BILLING_ADDRESS'),
	'SHIPPING_ADDRESS' => JText::_('HIKASHOP_SHIPPING_ADDRESS'),
	'SUMMARY_OF_YOUR_ORDER' => JText::_('SUMMARY_OF_YOUR_ORDER'),
	'MAIL_HEADER' => JText::_('HIKASHOP_MAIL_HEADER'),
	'TPL_HEADER_TEXT' => JText::_('HIKASHOP_MAIL_HEADER'),
	'USER_ACCOUNT' => (bool)@$customer->user_cms_id,
	'PRODUCT_NAME' => JText::_('CART_PRODUCT_NAME'),
	'PRODUCT_CODE' => JText::_('CART_PRODUCT_CODE'),
	'PRODUCT_PRICE' => JText::_('CART_PRODUCT_UNIT_PRICE'),
	'PRODUCT_QUANTITY' => JText::_('CART_PRODUCT_QUANTITY'),
	'PRODUCT_TOTAL' => JText::_('HIKASHOP_TOTAL'),
	'ADDITIONAL_INFORMATION' => JText::_('ADDITIONAL_INFORMATION'),

	'ORDER_TITLE' => JText::_('YOUR_ORDER'),
	'HI_CUSTOMER' => JText::sprintf('HI_CUSTOMER', $customer_name),
	'ORDER_CREATED' => JText::sprintf('ORDER_CREATION_SUCCESS_ON_WEBSITE_AT_DATE', $url, HIKASHOP_LIVE, hikashop_getDate(time(),'%d %B %Y'), hikashop_getDate(time(),'%H:%M')),
	'ORDER_BEGIN_MESSAGE' => JText::sprintf('THANK_YOU_FOR_YOUR_ORDER_BEGIN', HIKASHOP_LIVE),
	'ORDER_END_MESSAGE' => JText::sprintf('THANK_YOU_FOR_YOUR_ORDER', HIKASHOP_LIVE) . '<br/>' . JText::sprintf('BEST_REGARDS_CUSTOMER',$mail->from_name),
);
$templates = array();

$products_ids = array();
foreach($data->cart->products as $item) { $products_ids[] = $item->product_id; }
$productClass->getProducts($products_ids);

$cartProducts = array();
$cartFooters = array();
if(!empty($data->cart->products)){

	$null = null;
	$fields = null;
	$texts['CUSTOMFIELD_NAME'] = '';
	$texts['FOOTER_COLSPAN'] = 3;
	if(hikashop_level(1)){
		$fields = $fieldsClass->getFields('display:mail_order_creation=1',$null,'product');
		if(!empty($fields)){
			$product_customfields = array();
			$usefulFields = array();
			foreach($fields as $field){
				$namekey = $field->field_namekey;
				foreach($productClass->all_products as $product){
					if(!empty($product->$namekey)){
						$usefulFields[] = $field;
						break;
					}
				}
			}
			$fields = $usefulFields;
		}
		if(!empty($fields)){
			foreach($fields as $field){
				$texts['FOOTER_COLSPAN']++;
				$texts['CUSTOMFIELD_NAME'].='<td class="hika_template_color" style="border-bottom:1px solid #ddd;padding-bottom:3px;text-align:right;font-size:12px;font-weight:bold;">'.$fieldsClass->getFieldName($field).'</td>';
			}
		}
	}

	$group = $config->get('group_options',0);
	$subtotal = 0;
	foreach($data->cart->products as $item) {
		if($group && $item->order_product_option_parent_id)
			continue;

		$product = @$productClass->all_products[$item->product_id];

		$cartProduct = array(
			'PRODUCT_CODE' => $item->order_product_code,
			'PRODUCT_QUANTITY' => $item->order_product_quantity,
			'PRODUCT_IMG' => '',
			'item' => $item,
			'product' => $product,
		);

		if(!empty($item->images[0]->file_path) && $config->get('thumbnail', 1) != 0) {
			$img = $imageHelper->getThumbnail($item->images[0]->file_path, array(50, 50), array('forcesize' => true, 'scale' => 'outside'));
			if($img->success) {
				if(substr($img->url, 0, 3) == '../')
					$image = str_replace('../', HIKASHOP_LIVE, $img->url);
				elseif(!$img->external)
					$image = substr(HIKASHOP_LIVE, 0, strpos(HIKASHOP_LIVE, '/', 9)) . $img->url;
				else
					$image = $img->url;
				$attributes = '';
				if($img->external)
					$attributes = ' width="'.$img->req_width.'" height="'.$img->req_height.'"';
				$cartProduct['PRODUCT_IMG'] = '<img src="'.$image.'" alt="" style="float:left;margin-top:3px;margin-bottom:3px;margin-right:6px;"'.$attributes.'/>';
			}
		}

		$t = '<p>' . $item->order_product_name;
		if($group){
			$display_item_price=false;
			foreach($data->cart->products as $j => $optionElement){
				if($optionElement->order_product_option_parent_id != $item->order_product_id) continue;
				if($optionElement->order_product_price>0){
					$display_item_price = true;
				}

			}
			if($display_item_price){
				if($config->get('price_with_tax')){
					$t .= ' '.$currencyHelper->format($item->order_product_price+$item->order_product_tax,$data->cart->order_currency_id);
				}else{
					$t .= ' '.$currencyHelper->format($item->order_product_price,$data->cart->order_currency_id);
				}
			}
		}
		$t .= '</p>';

		if(!empty($itemFields)){
			foreach($itemFields as $field){
				$namekey = $field->field_namekey;
				if(!isset($item->$namekey) || (empty($item->$namekey) && !strlen($item->$namekey))) continue;
				$t .= '<p>'.$fieldsClass->getFieldName($field).': '.$fieldsClass->show($field,$item->$namekey,'user_email').'</p>';
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

		if($group){
			foreach($data->cart->products as $j => $optionElement){
				if($optionElement->order_product_option_parent_id != $item->order_product_id) continue;

				$item->order_product_price +=$optionElement->order_product_price;
				$item->order_product_tax +=$optionElement->order_product_tax;
				$item->order_product_total_price+=$optionElement->order_product_total_price;
				$item->order_product_total_price_no_vat+=$optionElement->order_product_total_price_no_vat;

				$t .= '<p class="hikashop_order_option_name">' . $optionElement->order_product_name;
				if($optionElement->order_product_price>0){
					if($config->get('price_with_tax')){
						$t .= ' ( + '.$currencyHelper->format($optionElement->order_product_price+$optionElement->order_product_tax,$data->cart->order_currency_id).' )';
					}else{
						$t .= ' ( + '.$currencyHelper->format($optionElement->order_product_price,$data->cart->order_currency_id).' )';
					}
				}
				$t .= '</p>';
			}
		}
		$cartProduct['PRODUCT_NAME'] = $t;

		$t = '';
		$statusDownload = explode(',',$config->get('order_status_for_download','confirmed,shipped'));
		if(!empty($item->files) && in_array($data->cart->order_status, $statusDownload)){
			$class = 'class="cart_button hika_template_color"';
			$t .= '<p>';
			foreach($item->files as $file){
				$fileName = empty($file->file_name) ? $file->file_path : $file->file_name;
				$file_pos = empty($file->file_pos) ? '' : ('&file_pos=' . $file->file_pos);
				if(empty($customer->user_cms_id))
					$file_pos .= '&order_token=' . $data->cart->order_token;
				$t .= '<a '.$class.'href="'.hikashop_frontendLink('index.php?option=com_hikashop&ctrl=order&task=download&file_id='.$file->file_id.'&order_id='.$data->order_id.$file_pos.$url_itemid).'">'.$fileName.'</a><br/>';
			}
			$t .= '</p>';
		}
		$cartProduct['PRODUCT_DOWNLOAD'] = $t;

		$cartProduct['PRODUCT_PRICE_BEFORE_DISCOUNT'] = '';
		if($config->get('price_with_tax')){
			$unit_price = $currencyHelper->format($item->order_product_price+$item->order_product_tax,$data->cart->order_currency_id);
			if(!empty($item->order_product_price_before_discount) && $item->order_product_price_before_discount > 0.0)
				$cartProduct['PRODUCT_PRICE_BEFORE_DISCOUNT'] =  $currencyHelper->format($item->order_product_price_before_discount+$item->order_product_tax_before_discount,$data->cart->order_currency_id);
			$total_price = $currencyHelper->format($item->order_product_total_price,$data->cart->order_currency_id);
			$subtotal += $item->order_product_total_price;
		}else{
			$unit_price = $currencyHelper->format($item->order_product_price,$data->cart->order_currency_id);
			if(!empty($item->order_product_price_before_discount) && $item->order_product_price_before_discount > 0.0)
				$cartProduct['PRODUCT_PRICE_BEFORE_DISCOUNT'] =  $currencyHelper->format($item->order_product_price_before_discount,$data->cart->order_currency_id);
			$total_price = $currencyHelper->format($item->order_product_total_price_no_vat,$data->cart->order_currency_id);
			$subtotal += $item->order_product_total_price_no_vat;
		}
		$cartProduct['PRODUCT_PRICE'] = $unit_price;
		$cartProduct['PRODUCT_TOTAL'] = $total_price;

		$cartProducts[] = $cartProduct;
	}
	$templates['PRODUCT_LINE'] = $cartProducts;

	if(bccomp(sprintf('%F',$data->cart->order_discount_price),0,5) != 0 || bccomp(sprintf('%F',$data->cart->order_shipping_price),0,5) != 0 || bccomp(sprintf('%F',$data->cart->order_payment_price),0,5) != 0 || ($data->cart->full_total->prices[0]->price_value!=$data->cart->full_total->prices[0]->price_value_with_tax) || !empty($data->cart->additional)){
		$cartFooters[] = array(
			'CLASS' => 'subtotal',
			'NAME' => JText::_('SUBTOTAL'),
			'VALUE' => $currencyHelper->format($subtotal,$data->cart->order_currency_id)
		);
	}
	if(bccomp(sprintf('%F',$data->cart->order_discount_price),0,5) != 0) {
		if($config->get('price_with_tax')) {
			$t = $currencyHelper->format($data->cart->order_discount_price * -1, $data->cart->order_currency_id);
		}else{
			$t = $currencyHelper->format(($data->cart->order_discount_price - @$data->cart->order_discount_tax) * -1, $data->cart->order_currency_id);
		}
		$cartFooters[] = array(
			'CLASS' => 'coupon',
			'NAME' => JText::_('HIKASHOP_COUPON'),
			'VALUE' => $t
		);
	}
	if(bccomp(sprintf('%F',$data->cart->order_shipping_price),0,5) != 0){
		if($config->get('price_with_tax')) {
			$t = $currencyHelper->format($data->cart->order_shipping_price,$data->cart->order_currency_id);
		}else{
			$t = $currencyHelper->format($data->cart->order_shipping_price-@$data->cart->order_shipping_tax,$data->cart->order_currency_id);
		}
		$cartFooters[] = array(
			'CLASS' => 'shipping',
			'NAME' => JText::_('HIKASHOP_SHIPPING'),
			'VALUE' => $t
		);
	}
	if(bccomp(sprintf('%F',$data->cart->order_payment_price),0,5) != 0){
		if($config->get('price_with_tax')) {
			$t = $currencyHelper->format($data->cart->order_payment_price, $data->cart->order_currency_id);
		} else {
			$t = $currencyHelper->format($data->cart->order_payment_price - @$data->cart->order_payment_tax, $data->cart->order_currency_id);
		}
		$cartFooters[] = array(
			'CLASS' => 'payment',
			'NAME' => JText::_('HIKASHOP_PAYMENT'),
			'VALUE' => $t
		);
	}
	if(!empty($data->cart->additional)) {
		$exclude_additionnal = explode(',', $config->get('order_additional_hide', ''));
		foreach($data->cart->additional as $additional) {
			if(in_array($additional->order_product_name, $exclude_additionnal))
				continue;
			if(!empty($additional->order_product_price))
				$additional->order_product_price = (float)hikashop_toFloat($additional->order_product_price);
			if((!empty($additional->order_product_price) && ($additional->order_product_price > 0) ) || empty($additional->order_product_options)) {
				if($config->get('price_with_tax')){
					$t = $currencyHelper->format($additional->order_product_price + @$additional->order_product_tax, $data->cart->order_currency_id);
				}else{
					$t = $currencyHelper->format($additional->order_product_price, $data->cart->order_currency_id);
				}
			} else {
				$t = $additional->order_product_options;
			}
			$cartFooters[] = array(
				'CLASS' => 'additional'.preg_replace('#[^a-z0-9_]#i','', strip_tags($additional->order_product_name)),
				'NAME' => JText::_($additional->order_product_name),
				'VALUE' => $t
			);
		}
	}

	if($data->cart->full_total->prices[0]->price_value!=$data->cart->full_total->prices[0]->price_value_with_tax) {
		if($config->get('detailed_tax_display') && !empty($data->cart->order_tax_info)) {
			foreach($data->cart->order_tax_info as $k => $tax) {
				$cartFooters[] = array(
					'CLASS' => 'tax_'.$k,
					'NAME' => hikashop_translate($tax->tax_namekey),
					'VALUE' => $currencyHelper->format($tax->tax_amount,$data->cart->order_currency_id)
				);
			}
		} else {
			$cartFooters[] = array(
				'CLASS' => 'total_without_tax',
				'NAME' => JText::_('ORDER_TOTAL_WITHOUT_VAT'),
				'VALUE' => $currencyHelper->format($data->cart->full_total->prices[0]->price_value,$data->cart->order_currency_id)
			);
		}
		$cartFooters[] = array(
			'CLASS' => 'total_with_tax',
			'NAME' => JText::_('ORDER_TOTAL_WITH_VAT'),
			'VALUE' => $currencyHelper->format($data->cart->full_total->prices[0]->price_value_with_tax,$data->cart->order_currency_id)
		);
	} else {
		$cartFooters[] = array(
			'CLASS' => 'total_with_tax',
			'NAME' => JText::_('HIKASHOP_TOTAL'),
			'VALUE' => $currencyHelper->format($data->cart->full_total->prices[0]->price_value_with_tax,$data->cart->order_currency_id)
		);
	}

	$templates['ORDER_FOOTER'] = $cartFooters;
}

if(!empty($data->cart->order_payment_method)) {
	if(!is_numeric($data->cart->order_payment_id) || empty($data->cart->order_payment_id)){
		$vars['PAYMENT'] = $data->cart->order_payment_method.' '.$data->cart->order_payment_id;
	}else{
		$paymentClass = hikashop_get('class.payment');
		$payment = $paymentClass->get($data->cart->order_payment_id);
		if(!empty($payment))
			$vars['PAYMENT'] = $payment->payment_name;
		else
			$vars['PAYMENT'] = $data->cart->order_payment_method;
		unset($paymentClass);
	}
}

if(!empty($data->cart->order_shipping_id)) {
	$shippingClass = hikashop_get('class.shipping');
	if(!empty($data->cart->order_shipping_method)) {
		if(!is_numeric($data->cart->order_shipping_id)){
			$shipping_name = $shippingClass->getShippingName($data->cart->order_shipping_method, $data->cart->order_shipping_id);
			$vars['SHIPPING'] = $shipping_name;
			$vars['SHIPPING_TXT'] = $vars['SHIPPING'];
		}else{
			$shipping = $shippingClass->get($data->cart->order_shipping_id);
			$vars['SHIPPING'] = $shipping->shipping_name;
			$vars['SHIPPING_TXT'] = $vars['SHIPPING'];
		}
	} else {
		$shippings_data = array();
		$shipping_ids = explode(';', $data->cart->order_shipping_id);
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
						if(!is_numeric($order_product->order_product_shipping_id)){
							$shipping_name = $shippingClass->getShippingName($order_product->order_product_shipping_method, $shipping_id);
							$shipping_data = $shipping_name;
						}else{
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
				if($config->get('price_with_tax')){
					$shipping_data .= ' (' . $currencyHelper->format($price_params->price_with_tax, $data->cart->order_currency_id) . ')';
				}else{
					$shipping_data .= ' (' . $currencyHelper->format($price_params->price_with_tax - @$price_params->tax, $data->cart->order_currency_id) . ')';
				}
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
		$fields = $fieldsClass->getFields('display:mail_order_creation=1',$data->cart,'order','');
		foreach($fields as $fieldName => $oneExtraField) {
			if(isset($data->$fieldName) && !isset($data->cart->$fieldName))
				$data->cart->$fieldName = $data->$fieldName;
			if($oneExtraField->field_type != 'customtext' && empty($data->cart->$fieldName))
				continue;
			echo $sep . $fieldsClass->trans($oneExtraField->field_realname).' : '.$fieldsClass->show($oneExtraField, @$data->cart->$fieldName, 'user_email');
			$sep = '<br />';
		}
	}

	JPluginHelper::importPlugin('hikashop');
	$app = JFactory::getApplication();
	$app->triggerEvent('onAfterOrderProductsListingDisplay', array(&$data->cart, 'email_notification_html'));

$content = ob_get_clean();
$vars['ORDER_SUMMARY'] = trim($content);

$vars['BILLING_ADDRESS'] = '';
$vars['SHIPPING_ADDRESS'] = '';

$addressClass = hikashop_get('class.address');
if(!empty($data->cart->billing_address) && !empty($data->cart->fields)){
	$vars['BILLING_ADDRESS'] = $addressClass->displayAddress($data->cart->fields,$data->cart->billing_address,$view);
}
if(!empty($data->cart->override_shipping_address)) {
	$vars['SHIPPING_ADDRESS'] =  $data->cart->override_shipping_address;
} elseif(!empty($data->cart->order_shipping_id) && !empty($data->cart->shipping_address) && !empty($data->cart->fields)) {
	$vars['SHIPPING_ADDRESS'] = $addressClass->displayAddress($data->cart->fields,$data->cart->shipping_address,$view);
} else {
	$vars['SHIPPING_ADDRESS'] = $vars['BILLING_ADDRESS'];
}

$unpaid_statuses = explode(',', $config->get('order_unpaid_statuses', 'created'));
if(in_array($data->order_status, $unpaid_statuses) && !empty($data->order_payment_method) && $data->order_payment_method != 'collectondelivery') {
	ob_start();

	if($data->cart->full_total->prices[0]->price_value_with_tax>0)
		echo '<p>' . JText::_('ORDER_VALID_AFTER_PAYMENT') . '</p>';

	if(in_array($data->order_payment_method, array('banktransfer', 'check', 'purchaseorder'))) {
		$amount = $currencyHelper->format($data->cart->full_total->prices[0]->price_value_with_tax, $data->cart->order_currency_id);
		$paymentClass = hikashop_get('class.payment');
		$payment = $paymentClass->get($data->order_payment_id);
		$information = $payment->payment_params->information;
		if(preg_match('#^[a-z0-9_]*$#i',$information)){
			$information = JText::_($information);
		}
		if($data->order_payment_method=='banktransfer'){
			echo '<p>' . JText::sprintf('PLEASE_TRANSFERT_MONEY',$amount).'<br/>'.$information.'<br/>'.JText::sprintf('INCLUDE_ORDER_NUMBER_TO_TRANSFER',$data->order_number) . '</p>';
		}elseif($data->order_payment_method=='check'){
			echo '<p>' . JText::sprintf('PLEASE_SEND_CHECK',$amount).'<br/>'.$information.'<br/>'.JText::sprintf('INCLUDE_ORDER_NUMBER_TO_CHECK',$data->order_number) . '</p>';
		}elseif($data->order_payment_method=='purchaseorder'){
			echo '<p>' . JText::sprintf('PLEASE_SEND_PURCHASEORDER',$amount).'<br/>'.$information.'<br/>'.JText::sprintf('INCLUDE_ORDER_NUMBER_TO_PURCHASEORDER',$data->order_number) . '</p>';
		}
	} else {
		if($data->cart->full_total->prices[0]->price_value_with_tax>0 && hikashop_level(1) && $config->get('allow_payment_button',1)) {
			$pay_url = 'index.php?option=com_hikashop&ctrl=order&task=pay&order_id='.$data->order_id.$url_itemid;
			if(empty($customer->user_cms_id) && !empty($data->order_token)) {
				$pay_url .= '&order_token='.urlencode($data->order_token);
			}
			$pay_url = hikashop_frontendLink($pay_url);
			if($config->get('force_ssl',0) && strpos('https://',$pay_url) === false) {
				$pay_url = str_replace('http://','https://',$pay_url);
			}
			echo '<p><a class="cart_button hika_template_color" href="'. $pay_url .'">'.JText::_('PAY_NOW') . '</a></p>';
		}
	}

	$content = ob_get_clean();
	$vars['ORDER_SUMMARY'] .= $content;
}

if($config->get('register_after_guest', 1) && empty($customer->user_cms_id) && !empty($data->order_token)){
	jimport('joomla.application.component.helper');
	$params = JComponentHelper::getParams('com_users');
	if((int)$params->get('allowUserRegistration') != 0) {
		$register_url = 'index.php?option=com_hikashop&ctrl=user&task=guest_form&order_id='.$data->order_id.$url_itemid.'&order_token='.urlencode($data->order_token);
		$register_url = hikashop_frontendLink($register_url);
		$vars['ORDER_SUMMARY'] .= '<p><a href="'. $register_url .'">'.JText::_('CLICK_HERE_TO_EASILY_CREATE_YOUR_USER_ACCOUNT') . '</a></p>';
	}
}
