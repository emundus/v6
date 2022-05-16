<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?>	<legend><?php echo JText::_('ORDER_ADD_INFO'); ?></legend>
<?php if(hikamarket::acl('order/edit/additional') && ($this->vendor->vendor_id == 0 || $this->vendor->vendor_id == 1)) { ?>
		<div class="hikam_edit"><?php
			echo $this->popup->display(
				'<i class="fas fa-pencil-alt"></i><span>'. JText::_('HIKA_EDIT') .'</span>',
				'HIKAM_SET_ORDER_ADDITIONALS',
				hikamarket::completeLink('order&task=edit&subtask=additional&cid='.$this->order->order_id, true),
				'hikamarket_editadditional_popup',
				750, 460, 'onclick="return window.orderMgr.setAdditionals(this);"', '', 'link'
			);
		?></div>
<script type="text/javascript">
<!--
window.orderMgr.setAdditionals = function(el) {
	window.hikamarket.submitFct = function(data) {
		var w = window, o = w.Oby;
		w.hikashop.closeBox();
		w.orderMgr.updateAdditionals();
	};
	window.hikashop.openBox(el);
	return false;
}
window.orderMgr.updateAdditionals = function() {
	window.Oby.xRequest('<?php echo hikamarket::completeLink('order&task=show&subtask=additional&cid='.$this->order->order_id, true, false, true); ?>', {update: 'hikashop_order_field_additional'});
}
//-->
</script>
<?php } ?>
	<dl class="hikam_options">
		<dt class="hikamarket_order_additional_subtotal"><label><?php echo JText::_('SUBTOTAL'); ?></label></dt>
		<dd class="hikamarket_order_additional_subtotal"><span><?php
			if($this->shopConfig->get('price_with_tax'))
				echo $this->currencyHelper->format($this->order->order_subtotal, $this->order->order_currency_id);
			else
				echo $this->currencyHelper->format($this->order->order_subtotal_no_vat, $this->order->order_currency_id);
		?></span></dd>

		<dt class="hikamarket_order_additional_coupon"><label><?php echo JText::_('HIKASHOP_COUPON'); ?></label></dt>
		<dd class="hikamarket_order_additional_coupon"><span><?php
			if($this->shopConfig->get('price_with_tax'))
				echo $this->currencyHelper->format($this->order->order_discount_price * -1.0, $this->order->order_currency_id);
			else
				echo $this->currencyHelper->format(($this->order->order_discount_price - @$this->order->order_discount_tax) * -1.0, $this->order->order_currency_id);
		?> <?php echo $this->order->order_discount_code; ?></span></dd>

		<dt class="hikamarket_order_additional_shipping"><label><?php echo JText::_('SHIPPING'); ?></label></dt>
		<dd class="hikamarket_order_additional_shipping"><span><?php
			if($this->shopConfig->get('price_with_tax'))
				echo $this->currencyHelper->format($this->order->order_shipping_price, $this->order->order_currency_id);
			else
				echo $this->currencyHelper->format($this->order->order_shipping_price - @$this->order->order_shipping_tax, $this->order->order_currency_id);

			if(empty($this->order->order_shipping_method) && empty($this->order->shippings)) {
				echo '- <em>'.JText::_('NONE').'</em>';
			} else if(!empty($this->order->order_shipping_method)) {
				if(!is_numeric($this->order->order_shipping_id)){
					$shipping_name = $this->getShippingName($this->order->order_shipping_method, $this->order->order_shipping_id);
					echo ' - ' . $shipping_name;
				} else {
					$shipping = $this->shippingClass->get($this->order->order_shipping_id);
					echo ' - ' . $shipping->shipping_name;
				}
			} else {
				$shippings_data = array();
				$shipping_ids = explode(';', $this->order->order_shipping_id);
				foreach($shipping_ids as $key) {
					$shipping_data = '';
					list($k, $w) = explode('@', $key);
					$shipping_id = $k;
					if(isset($this->order->shippings[$shipping_id])) {
						$shipping = $this->order->shippings[$shipping_id];
						$shipping_data = $shipping->shipping_name;
					} else {
						foreach($this->order->products as $order_product) {
							if($order_product->order_product_shipping_id == $key) {
								if(!is_numeric($order_product->order_product_shipping_id)) {
									$shipping_name = $this->getShippingName($order_product->order_product_shipping_method, $shipping_id);
									$shipping_data = $shipping_name;
								} else {
									$shipping_method_data = $this->shippingClass->get($shipping_id);
									$shipping_data = $shipping_method_data->shipping_name;
								}
								break;
							}
						}
						if(empty($shipping_data))
							$shipping_data = '[ ' . $key . ' ]';
					}
					if(isset($this->order->order_shipping_params->prices[$key])) {
						$price_params = $this->order->order_shipping_params->prices[$key];
						if($this->shopConfig->get('price_with_tax'))
							$shipping_data .= ' (' . $this->currencyHelper->format($price_params->price_with_tax, $this->order->order_currency_id) . ')';
						else
							$shipping_data .= ' (' . $this->currencyHelper->format($price_params->price_with_tax - @$price_params->tax, $this->order->order_currency_id) . ')';
					}
					$shippings_data[] = $shipping_data;
				}
				if(!empty($shippings_data))
					echo '<ul><li>'.implode('</li><li>', $shippings_data).'</li></ul>';
			}
			?></span></dd>

		<dt class="hikamarket_order_additional_payment_fee"><label><?php echo JText::_('HIKASHOP_PAYMENT'); ?></label></dt>
		<dd class="hikamarket_order_additional_payment_fee"><span><?php echo $this->currencyHelper->format($this->order->order_payment_price, $this->order->order_currency_id); ?> - <?php
			if(empty($this->order->order_payment_method) || $this->order->order_payment_method == 'market-')
				echo '<em>'.JText::_('NONE').'</em>';
			else {
				if(!is_numeric($this->order->order_payment_id)){
					echo $this->order->order_payment_method.' '.$this->order->order_payment_id;
				} else if((int)$this->order->order_payment_id > 0) {
					$payment = $this->paymentClass->get($this->order->order_payment_id);
					echo @$payment->payment_name;
				}
			}
			?></span></dd>
<?php
	if(!empty($this->order->additional)) {
		foreach($this->order->additional as $additional) {
?>
		<dt class="hikamarket_order_additional_additional"><label><?php echo JText::_($additional->order_product_name); ?></label></dt>
		<dd class="hikamarket_order_additional_additional"><span><?php
			if(!empty($additional->order_product_price)) {
				$additional->order_product_price = (float)$additional->order_product_price;
			}
			if(!empty($additional->order_product_price) || empty($additional->order_product_options)) {
				echo $this->currencyHelper->format($additional->order_product_price, $this->order->order_currency_id);
			} else {
				echo $additional->order_product_options;
			}
		?></span></dd>
<?php
		}
	}

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
		<dt class="hikamarket_order_additional_total"><label><?php
			if((int)$this->order->order_vendor_id <= 1)
				echo JText::_('HIKASHOP_TOTAL');
			else
				echo JText::_('HIKAM_USER_TOTAL');
		?></label></dt>
		<dd class="hikamarket_order_additional_total"><span><?php echo $this->currencyHelper->format($this->order->order_full_price,$this->order->order_currency_id); ?></span></dd>

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
	<!-- Vendor shipping fees -->
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
	}

	if(!empty($this->fields['order'])) {
		foreach($this->fields['order'] as $fieldName => $oneExtraField) {
?>
		<dt class="hikamarket_order_additional_customfield hikamarket_order_additional_customfield_<?php echo $fieldName; ?>"><?php echo $this->fieldsClass->getFieldName($oneExtraField);?></dt>
		<dd class="hikamarket_order_additional_customfield hikamarket_order_additional_customfield_<?php echo $fieldName; ?>"><span><?php
			echo $this->fieldsClass->show($oneExtraField, @$this->order->$fieldName);
		?></span></dd>
<?php
		}
	}
?>
	</dl>
