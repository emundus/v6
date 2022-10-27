<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="hikamarket_order">
	<h2 style="display:inline-block"><?php
		echo JText::sprintf('HIKAM_ORDER', $this->order->order_number);
	?></h2>
	<span id="hikamarket_order_status" style="margin-left:7px;font-size:1.2em;line-height:1.3em;" class="order-label order-label-<?php echo preg_replace('#[^a-z_0-9]#i', '_', str_replace(' ','_',$this->order->order_status)); ?>"><?php echo hikamarket::orderStatus($this->order->order_status); ?></span>

<!-- GENERAL -->
	<dl class="hikam_options large">
<?php if(empty($this->order->order_invoice_created) || $this->order->order_invoice_created != $this->order->order_created) { ?>
		<dt class="hikamarket_order_created"><label><?php echo JText::_('DATE_ORDER_CREATED');?></label></dt>
		<dd class="hikamarket_order_created"><?php echo hikamarket::getDate($this->order->order_created, '%Y-%m-%d %H:%M'); ?></dd>
<?php } ?>

		<dt class="hikamarket_order_invoicenumber"><label><?php echo JText::_('INVOICE_NUMBER'); ?></label></dt>
		<dd class="hikamarket_order_invoicenumber"><span><?php echo @$this->order->order_invoice_number; ?></span></dd>

<?php if(!empty($this->order->order_invoice_created)) { ?>
		<dt class="hikamarket_order_created"><label><?php echo JText::_('DATE_ORDER_PAID');?></label></dt>
		<dd class="hikamarket_order_created"><?php echo hikamarket::getDate($this->order->order_invoice_created, '%Y-%m-%d %H:%M');?></dd>
<?php } ?>
	</dl>

<!-- ADDITIONAL -->
	<h3><?php echo JText::_('ORDER_DETAILS')?></h3>
	<dl class="hikam_options large">
		<dt class="hikamarket_order_additional_subtotal"><label><?php echo JText::_('SUBTOTAL'); ?></label></dt>
		<dd class="hikamarket_order_additional_subtotal"><span><?php
			if($this->shopConfig->get('price_with_tax'))
				echo $this->currencyHelper->format($this->order->order_subtotal, $this->order->order_currency_id);
			else
				echo $this->currencyHelper->format($this->order->order_subtotal_no_vat, $this->order->order_currency_id);
		?></span></dd>

<?php if(!empty($this->order->order_discount_code) || bccomp($this->order->order_discount_price, 0, 5) !== 0) { ?>
		<dt class="hikamarket_order_additional_coupon"><label><?php echo JText::_('HIKASHOP_COUPON'); ?></label></dt>
		<dd class="hikamarket_order_additional_coupon"><span><?php
			if($this->shopConfig->get('price_with_tax'))
				echo $this->currencyHelper->format($this->order->order_discount_price * -1.0, $this->order->order_currency_id);
			else
				echo $this->currencyHelper->format(($this->order->order_discount_price - @$this->order->order_discount_tax) * -1.0, $this->order->order_currency_id);
		?> <span class="label label-default"><?php echo $this->order->order_discount_code; ?></span></span></dd>
<?php } ?>

	<!-- Shipping -->
<?php if(!empty($this->order->order_shipping_id) || bccomp($this->order->order_shipping_price, 0, 5) !== 0) { ?>
		<dt class="hikamarket_order_additional_shipping"><label><?php echo JText::_('SHIPPING'); ?></label></dt>
		<dd class="hikamarket_order_additional_shipping"><span><?php
			if($this->shopConfig->get('price_with_tax'))
				echo $this->currencyHelper->format($this->order->order_shipping_price, $this->order->order_currency_id);
			else
				echo $this->currencyHelper->format($this->order->order_shipping_price - @$this->order->order_shipping_tax, $this->order->order_currency_id);
		?> <span class="hk-label hk-label-blue"><?php
			if(empty($this->order->shipping_name))
				echo '<em>'.JText::_('HIKA_NONE').'</em>';
			else if(is_string($this->order->shipping_name))
				echo $this->order->shipping_name;
			else
				echo implode('</span> <span class="hk-label hk-label-blue">', $this->order->shipping_name);
		?></span></dd>
<?php
	}
?>

	<!-- Payment -->
<?php if(!empty($this->order->order_payment_id) || bccomp($this->order->order_payment_price, 0, 5) !== 0) { ?>
		<dt class="hikamarket_order_additional_payment"><label><?php echo JText::_('HIKASHOP_PAYMENT'); ?></label></dt>
		<dd class="hikamarket_order_additional_payment"><span><?php
			echo $this->currencyHelper->format($this->order->order_payment_price, $this->order->order_currency_id);
		?> <span class="hk-label hk-label-blue"><?php echo @$this->order->payment_name; ?></span></dd>
<?php
	}
?>
	<!-- Additional -->
<?php
	if(!empty($this->order->additional)) {
		foreach($this->order->additional as $additional) {
?>
		<dt class="hikamarket_order_additional_additional"><label><?php echo JText::_($additional->order_product_name); ?></label></dt>
		<dd class="hikamarket_order_additional_additional"><span><?php
			if(!empty($additional->order_product_price))
				$additional->order_product_price = (float)$additional->order_product_price;

			if(!empty($additional->order_product_price) || empty($additional->order_product_options))
				echo $this->currencyHelper->format($additional->order_product_price, $this->order->order_currency_id);
			else
				echo $additional->order_product_options;
		?></span></dd>
<?php
		}
	}
?>
	<!-- Taxes -->
<?php
	if($this->shopConfig->get('detailed_tax_display') && !empty($this->order->order_tax_info)) {
		foreach($this->order->order_tax_info as $tax){
?>
			<dt class="hikamarket_order_additional_tax"><label><?php echo $tax->tax_namekey; ?></label></dt>
			<dd class="hikamarket_order_additional_tax"><span><?php
				echo $this->currencyHelper->format($tax->tax_amount,$this->order->order_currency_id);
			?></span></dd>
<?php
		}
	}
?>
	<!-- Total -->
		<dt class="hikamarket_order_additional_total"><label><?php
			if((int)$this->order->order_vendor_id <= 1)
				echo JText::_('HIKASHOP_TOTAL');
			else
				echo JText::_('HIKAM_USER_TOTAL');
		?></label></dt>
		<dd class="hikamarket_order_additional_total"><span><?php echo $this->currencyHelper->format($this->order->order_full_price, $this->order->order_currency_id); ?></span></dd>

<?php
	if((int)$this->order->order_vendor_id > 1) {
		$fixed_fees = 0.0;
		if(!empty($this->order->order_vendor_params->fees->fixed)) {
			foreach($this->order->order_vendor_params->fees->fixed as $fixed_fee) {
				$fixed_fees += $fixed_fee;
			}
		}
		if(bccomp($fixed_fees, 0, 5) !== 0) {
?>
	<!-- Vendor fixed fees -->
		<dt class="hikamarket_order_vendor_fixed_fees"><label><?php echo JText::_('HIKAM_VENDOR_FIXED_FEES'); ?></label></dt>
		<dd class="hikamarket_order_vendor_fixed_fees"><span><?php
			echo $this->currencyHelper->format($fixed_fees, $this->order->order_currency_id);
		?></span></dd>
<?php
		}

		if(!empty($this->order->order_vendor_params->fees->shipping)) {
?>
	<!-- Vendor shipping fees -->
		<dt class="hikamarket_order_vendor_shipping_fees"><label><?php echo JText::_('HIKAM_VENDOR_SHIPPING_FEES'); ?></label></dt>
		<dd class="hikamarket_order_vendor_shipping_fees"><span><?php
			echo $this->currencyHelper->format($this->order->order_vendor_params->fees->shipping, $this->order->order_currency_id);
		?></span></dd>
<?php
		}

		if($this->order->order_vendor_paid > 0) {
			$total = $this->order->order_vendor_price;
			$paid = $this->order->order_vendor_price;
			if(!empty($this->order->refunds)) {
				foreach($this->order->refunds as $refund) {
					$total += (float)hikamarket::toFloat($refund->order_vendor_price);
					if($refund->order_vendor_paid > 0)
						$paid += (float)hikamarket::toFloat($refund->order_vendor_price);
				}
			}
?>
	<!-- Vendor total -->
		<dt class="hikamarket_order_vendor_paid"><label><?php echo JText::_('VENDOR_TOTAL'); ?></label></dt>
		<dd class="hikamarket_order_vendor_paid"><span><?php
			echo $this->currencyHelper->format($total, $this->order->order_currency_id);

			if($total == $paid)
				echo ' ' . hikamarket::tooltip(JText::_('HIKAM_ORDER_IS_PAID'), '', '', '<img src="'.HIKAMARKET_IMAGES.'icon-16/save2.png" style="vertical-align:top;" alt="('.JText::_('PAID').')" />', '', 0);
		?></span></dd>
<?php
		} else {
?>
	<!-- Vendor total -->
		<dt class="hikamarket_order_vendor_total"><label><?php echo JText::_('VENDOR_TOTAL'); ?></label></dt>
		<dd class="hikamarket_order_vendor_total"><span><?php
			echo $this->currencyHelper->format($this->order->order_vendor_price, $this->order->order_currency_id);
		?></span></dd>
<?php
		}
?>
<?php } ?>
	</dl>
<?php
	if(!empty($this->fields['order']) && hikamarket::acl('order/show/customfields')) {
?>
	<dl class="hikam_options large">
<?php
		foreach($this->fields['order'] as $fieldName => $oneExtraField) {
?>
		<dt class="hikamarket_order_additional_customfield hikamarket_order_additional_customfield_<?php echo $fieldName; ?>"><?php echo $this->fieldsClass->getFieldName($oneExtraField);?></dt>
		<dd class="hikamarket_order_additional_customfield hikamarket_order_additional_customfield_<?php echo $fieldName; ?>"><span><?php
			echo $this->fieldsClass->show($oneExtraField, @$this->order->$fieldName);
		?></span></dd>
<?php
		}
?>
	</dl>
<?php
	}
?>

<!-- CUSTOMER -->
<?php if(hikamarket::acl('order/show/customer')){ ?>
	<h3><?php echo JText::_('CUSTOMER')?></h3>
	<dl class="hikam_options large">
		<dt class="hikamarket_order_customer_name"><label><?php echo JText::_('HIKA_NAME');?></label></dt>
		<dd class="hikamarket_order_customer_name"><span id="hikamarket_order_customer_name"><?php echo @$this->order->customer->name; ?></span></dd>

		<dt class="hikamarket_order_customer_email"><label><?php echo JText::_('HIKA_EMAIL');?></label></dt>
		<dd class="hikamarket_order_customer_email"><span id="hikamarket_order_customer_email"><?php echo @$this->order->customer->user_email; ?></span></dd>
	</dl>
<?php } ?>

<!-- ADDRESSES -->
<?php
	if(hikamarket::acl('order/show/billingaddress') || hikamarket::acl('order/show/shippingaddress')) {
		$acl_billing = hikamarket::acl('order/show/billingaddress');
		$acl_shipping = hikamarket::acl('order/show/shippingaddress');
		$row_class = '';
		$cell_class = '';
		if($acl_billing && $acl_shipping) {
			$row_class = 'hk-row-fluid';
			$cell_class = 'hkc-md-6';
		}
?>
	<div class="<?php echo $row_class; ?> hikamarket_order_addresses">
<?php if($acl_billing) { ?>
		<div class="<?php echo $cell_class; ?> hikamarket_order_billing_address">
			<h3><?php echo JText::_('HIKASHOP_BILLING_ADDRESS')?></h3>
			<?php
				echo $this->addressClass->maxiFormat($this->addresses[(int)$this->order->order_billing_address_id], $this->address_fields, true);
			?>
		</div>
<?php } ?>
<?php if($acl_shipping) { ?>
		<div class="<?php echo $cell_class; ?> hikamarket_order_shipping_address">
			<h3><?php echo JText::_('HIKASHOP_SHIPPING_ADDRESS')?></h3>
			<?php
				if(empty($this->order->override_shipping_address))
					echo $this->addressClass->maxiFormat($this->addresses[(int)$this->order->order_shipping_address_id], $this->address_fields, true);
				else
					echo $this->order->override_shipping_address;
			?>
		</div>
<?php } ?>
	</div>
<?php } ?>

<!-- PRODUCTS -->
	<h3><?php echo JText::_('PRODUCT_LIST')?></h3>
	<table class="hikam_listing <?php echo (HIKASHOP_RESPONSIVE)?'table table-striped table-hover table-bordered':'hikam_table'; ?>" id="hikamarket_order_product_listing" style="width:100%">
		<thead>
			<tr>
				<th class="hikamarket_order_item_name_title title"><?php echo JText::_('PRODUCT'); ?></th>
				<th class="hikamarket_order_item_price_title title"><?php echo JText::_('UNIT_PRICE'); ?></th>
				<th class="hikamarket_order_item_quantity_title title"><?php echo JText::_('PRODUCT_QUANTITY'); ?></th>
				<th class="hikamarket_order_item_total_price_title title"><?php echo JText::_('PRICE'); ?></th>
			</tr>
		</thead>
		<tbody>
<?php

foreach($this->order->products as $k => $product) {
	$td_class = '';
	if(!empty($product->order_product_option_parent_id))
		$td_class = ' hikamarket_order_item_option';

?>
		<tr>
			<td class="hikamarket_order_item_name_value<?php echo $td_class; ?>"><?php
	if(!empty($product->product_id)) {
?>
				<a onclick="return window.orderMgr.showProduct(this);" data-popup-href="<?php echo hikamarket::completeLink('shop.product&task=show&cid='.$product->product_id, true); ?>" href="<?php echo hikamarket::completeLink('shop.product&task=show&cid='.$product->product_id); ?>"><?php
		if(!empty($product->images)) {
			$img = reset($product->images);
			$thumb = $this->imageHelper->getThumbnail(@$img->file_path, array(50,50), array('default' => 1, 'forcesize' => 1));
			if(!empty($thumb->path))
				echo '<img src="'. $this->imageHelper->uploadFolder_url . str_replace('\\', '/', $thumb->path).'" alt="" class="hikam_imglist" />';
		}
		echo $product->order_product_name;
				?></a>
<?php
	} else {
		echo $product->order_product_name;
	}
			?></td>
			<td class="hikamarket_order_item_price_value"><?php
				echo $this->currencyHelper->format($product->order_product_price, $this->order->order_currency_id);
				if(bccomp($product->order_product_tax, 0, 5))
					echo '<br/>'.JText::sprintf('PLUS_X_OF_VAT', $this->currencyHelper->format($product->order_product_tax, $this->order->order_currency_id));
			?></td>
			<td class="hikamarket_order_item_quantity_value"><?php
				echo (int)$product->order_product_quantity;
			?></td>
			<td class="hikamarket_order_item_total_price_value"><?php
				echo $this->currencyHelper->format($product->order_product_total_price, $this->order->order_currency_id);
			?></td>
		</tr>
<?php
}
?>
		</tbody>
	</table>
<?php
echo $this->popup->display(
	'',
	'HIKAM_SHOW_ORDER_PRODUCT',
	hikamarket::completeLink('shop.product&task=show&cid=0', true),
	'hikamarket_showproduct_popup',
	750, 460, 'style="display:none;"', '', 'link'
);
?>
<!-- OTHER COMPONENTS -->
<?php
	JPluginHelper::importPlugin('hikashop');
	JPluginHelper::importPlugin('hikamarket');
	JFactory::getApplication()->triggerEvent('onAfterOrderProductsListingDisplay', array(&$this->order, 'order_frontvendor_vendor_show'));
?>

<!-- HISTORY -->
<?php if(hikamarket::acl('order/show/history') && !empty($this->order->history)) { ?>
	<h3><?php echo JText::_('HISTORY')?></h3>
	<div class="hikamarket_history_container">
	<table id="hikamarket_order_history_listing" class="hikam_listing hikam_table table table-striped table-hover">
		<thead>
			<tr>
				<th class="title"><?php  echo JText::_('HIKA_TYPE'); ?></th>
				<th class="title"><?php echo JText::_('ORDER_STATUS'); ?></th>
				<th class="title"><?php echo JText::_('REASON'); ?></th>
				<th class="title"><?php echo JText::_('DATE'); ?></th>
			</tr>
		</thead>
		<tbody>
<?php
	$userClass = hikamarket::get('shop.class.user');
	foreach($this->order->history as $k => $history) {
?>
			<tr>
				<td><?php
					$val = preg_replace('#[^a-z0-9]#i','_',strtoupper($history->history_type));
					$trans = JText::_($val);
					if($val != $trans)
						$history->history_type = $trans;
					echo $history->history_type;
				?></td>
				<td><?php
					echo hikamarket::orderStatus($history->history_new_status);
				?></td>
				<td><?php
					echo $history->history_reason;
				?></td>
				<td><?php
					echo hikamarket::getDate($history->history_created,'%Y-%m-%d %H:%M');
				?></td>
			</tr>
<?php
	}
?>
		</tbody>
	</table>
	</div>
<?php } ?>
</div>
<script type="text/javascript">
<!--
if(!window.orderMgr)
	window.orderMgr = {};
window.orderMgr.showProduct = function(el) {
	window.hikashop.submitFct = function(data) { window.hikashop.closeBox(); };
	var href = el.getAttribute('data-popup-href');
	if(!href)
		href = el.getAttribute('href');
	window.hikashop.openBox('hikamarket_showproduct_popup', href);
	return false;
};
window.orderMgr.editOrderStatus = function(el) {
	window.hikamarket.submitFct = function(data) {
		var orderstatus = document.getElementById('hikamarket_order_status');
		if(orderstatus) {
			orderstatus.innerHTML = data.name;
			orderstatus.className = 'order-label order-label-' + data.order_status.replace(/[^a-z_0-9]/i, '_');
		}
		window.hikamarket.closeBox();
	};
	var href = el.getAttribute('href');
	if(href == '' || href == null || href == '#')
		href = null;
	window.hikamarket.openBox('hikamarket_order_status_popup', href);
	return false;
};
// -->
</script>
