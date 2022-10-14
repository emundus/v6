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
if(empty($this->order))
	return;

if(empty($this->ajax)) {

	if(hikamarket::acl('order/edit')) {
		$dropData = array();

		if($this->editable_order && hikamarket::acl('order/edit/coupon')) {
			$dropData['coupon'] = array(
				'name' => '<i class="fas fa-percentage"></i> ' . JText::_('HIKAM_EDIT_COUPON'),
				'link' => '#coupon',
				'click' => 'return window.orderMgr.showEditDetails(this, \'coupon\', true);'
			);
		}
		if($this->editable_order && hikamarket::acl('order/edit/shipping')) {
			$dropData['shipping'] = array(
				'name' => '<i class="fas fa-shipping-fast"></i> ' . JText::_('HIKAM_EDIT_SHIPPING'),
				'link' => '#shipping',
				'click' => 'return window.orderMgr.showEditDetails(this, \'shipping\', true);'
			);
		}
		if($this->editable_order && hikamarket::acl('order/edit/payment')) {
			$dropData['payment'] = array(
				'name' => '<i class="far fa-credit-card"></i> ' . JText::_('HIKAM_EDIT_PAYMENT'),
				'link' => '#payment',
				'click' => 'return window.orderMgr.showEditDetails(this, \'payment\', true);'
			);
		}
		if(!empty($this->fields['order']) && $this->editable_order && hikamarket::acl('order/show/customfields') && hikamarket::acl('order/edit/customfields')) {
			$dropData[] = array(
				'name' => '<i class="fas fa-clipboard-list"></i> ' . JText::_('HIKAM_EDIT_CUSTOM_FIELDS'),
				'link' => '#fields',
				'click' => 'return window.orderMgr.refreshBlock(\'fields\', true);'
			);
		}

		if(!empty($dropData)) {
			echo $this->dropdownHelper->display(JText::_('HIKAM_EDIT'), $dropData, array('type' => '', 'mini' => true, 'class' => 'hikabtn-primary', 'right' => false, 'up' => false));
?>
<script type="text/javascript">
window.orderMgr.showEditDetails = function(el, type, show) {
	if(type != 'coupon' && type != 'payment' && type != 'shipping')
		return false;

	var d = document,
		block = d.getElementById('hikamarket_order_edit_' + type),
		marker = false,
		showing = ((show === undefined && block.style.display == 'none') || show == true);
	if(!block)
		return false;
	block.style.display = showing ? '' : 'none';

	if(!marker && showing)
		this.refreshBlock(type, true);
	return false;
};
window.orderMgr.submitDetails = function(el, type) {
	var d = document, w = window, o = w.Oby,
		block = document.getElementById('hikamarket_order_edit_' + type);
	if(block)
		o.addClass(el, "hikamarket_ajax_loading");
	this.submitBlock(type, {data:false, update:false}, function(x,p){
		if(block) {
			o.removeClass(el, "hikamarket_ajax_loading");
			if(x.responseText.length > 1)
				return window.Oby.updateElem(block, x.responseText);
		}
		window.Oby.fireAjax('orderMgr.details', null);
	});
	return this.showEditDetails(el, type, false);
};
</script>
<?php
		}

		if($this->editable_order && hikamarket::acl('order/edit/coupon')) {
?>
<div class="hikamarket_order_edit_block" id="hikamarket_order_edit_coupon" style="display:none;">
	<div class="hikamarket_ajax_loading_elem"></div>
	<div class="hikamarket_ajax_loading_spinner"></div>
</div>
<?php
		}

		if($this->editable_order && hikamarket::acl('order/edit/shipping')) {
?>
<div class="hikamarket_order_edit_block" id="hikamarket_order_edit_shipping" style="display:none;">
	<div class="hikamarket_ajax_loading_elem"></div>
	<div class="hikamarket_ajax_loading_spinner"></div>
</div>
<?php
		}

		if($this->editable_order && hikamarket::acl('order/edit/payment')) {
?>
<div class="hikamarket_order_edit_block" id="hikamarket_order_edit_payment" style="display:none;">
	<div class="hikamarket_ajax_loading_elem"></div>
	<div class="hikamarket_ajax_loading_spinner"></div>
</div>
<?php
		}
	}
?>
<div id="hikamarket_order_block_details">
<?php } ?>
	<div class="hikamarket_ajax_loading_elem"></div>
	<div class="hikamarket_ajax_loading_spinner"></div>

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
			else if(!empty($this->order->shipping_data))
				echo implode('</span> <span class="hk-label hk-label-blue">', $this->order->shipping_data);
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
		foreach($this->order->order_tax_info as $tax) {
?>
			<dt class="hikamarket_order_additional_tax"><label><?php
				echo $tax->tax_namekey;
			?></label></dt>
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

			if($total == $paid) {
				echo ' ' . hikamarket::tooltip(JText::_('HIKAM_ORDER_IS_PAID'), '', '', '<img src="'.HIKAMARKET_IMAGES.'icon-16/save2.png" style="vertical-align:top;" alt="('.JText::_('PAID').')" />', '', 0);
			} elseif($total > $paid) {
				echo ' ' . JText::sprintf('HIKAM_ORDER_PARTIAL_PAID', $this->currencyHelper->format($total - $paid, $this->order->order_currency_id));
			} else {
				echo ' ' . JText::sprintf('HIKAM_ORDER_PARTIAL_REFUND', $this->currencyHelper->format($paid - $total, $this->order->order_currency_id));
			}
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

if(!empty($this->ajax))
	return;
?>
</div>
<script type="text/javascript">
window.Oby.registerAjax('orderMgr.details',function(params){ window.orderMgr.refreshBlock('details'); });
</script>
