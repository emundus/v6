<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="hikamarket_invoice_main_div">
	<div id="print" style="float:right">
		<a href="#" onclick="document.getElementById('print').style.visibility='hidden';window.focus();window.print();return false;">
			<img src="<?php echo HIKASHOP_IMAGES; ?>print.png"/>
		</a>
	</div>
	<br/>
	<table width="100%">
		<tr>
			<td>
				<h1 style="text-align:center"><?php
				if($this->invoice_type=='full') {
					echo JText::_('INVOICE');
				} else {
					echo JText::_('SHIPPING_INVOICE');
				}
				?></h1>
			</td>
		</tr>
		<tr>
			<td>
				<div style="float:right;width:100px;padding-top:20px">
					<?php
						$date = (!empty($this->element->order_invoice_created)) ? $this->element->order_invoice_created : @$this->element->order_created;
						echo JText::_('DATE') . ': ' . hikamarket::getDate($date, '%d %B %Y ');
					?>
					<br/>
					<?php echo JText::_('INVOICE') . ': ' . @$this->element->order_invoice_number; ?>
				</div>
				<p><?php
if($this->vendor->vendor_id == 0 || $this->vendor->vendor_id == 1) {
	echo $this->store_address;
} else {
	$params = null; $js = null;
	$html = hikamarket::getLayout('shop.address', 'address_template', $params, $js);
	foreach($this->extraFields['vendor'] as $field) {
		$fieldname = $field->field_namekey;
		$html = str_replace('{' . str_replace('vendor_', '', $fieldname) . '}', $this->fieldsClass->show($field, $this->vendor->$fieldname), $html);
	}
	echo str_replace("\n","<br/>\n",trim(str_replace("\n\n","\n",preg_replace('#{(?:(?!}).)*}#i','',$html)),"\n"));
}
				?></p>
			</td>
		</tr>
		<tr>
			<td>
				<table width="100%">
					<tr>
<?php if($this->invoice_type=='full' && !empty($this->element->billing_address)){ ?>
						<td>
							<fieldset class="adminform" id="htmlfieldset_billing">
							<legend style="background-color: #FFFFFF;"><?php echo JText::_('HIKASHOP_BILLING_ADDRESS'); ?></legend>
<?php
	$params = null; $js = null;
	$html = hikamarket::getLayout('shop.address', 'address_template', $params, $js);
	foreach($this->element->fields as $field) {
		$fieldname = $field->field_namekey;
		$html = str_replace('{'.$fieldname.'}', $this->fieldsClass->show($field, $this->element->billing_address->$fieldname), $html);
	}
	echo str_replace("\n","<br/>\n",trim(str_replace("\n\n","\n",preg_replace('#{(?:(?!}).)*}#i','',$html)),"\n"));
?>
							</fieldset>
						</td>
<?php } ?>
						<td>
<?php if(!empty($this->element->order_shipping_id) && !empty($this->element->shipping_address)) { ?>
							<fieldset class="adminform" id="htmlfieldset_shipping">
								<legend style="background-color: #FFFFFF;"><?php echo JText::_('HIKASHOP_SHIPPING_ADDRESS'); ?></legend>
<?php
	if(empty($this->element->override_shipping_address)) {
		$params = null; $js = null;
		$html = hikamarket::getLayout('shop.address', 'address_template', $params, $js);
		foreach($this->element->fields as $field){
			$fieldname = $field->field_namekey;
			$html = str_replace('{'.$fieldname.'}', $this->fieldsClass->show($field, $this->element->shipping_address->$fieldname), $html);
		}
		echo str_replace("\n", "<br/>\n", trim(str_replace("\n\n", "\n", preg_replace('#{(?:(?!}).)*}#i', '', $html)),"\n"));
	} else {
		echo $this->element->override_shipping_address;
	}
?>
							</fieldset>
<?php } ?>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				<fieldset class="adminform" id="htmlfieldset_products">
					<legend style="background-color: #FFFFFF;"><?php echo JText::_('PRODUCT_LIST'); ?></legend>
					<table class="adminlist table table-striped" cellpadding="1" width="100%">
						<thead>
							<tr>
								<th class="title" width="60%">
									<?php echo JText::_('PRODUCT'); ?>
								</th>
<?php if ($this->shopConfig->get('show_code')) { ?>
								<th class="title" ><?php echo JText::_('CART_PRODUCT_CODE'); ?></th>
<?php } ?>
<?php if($this->invoice_type == 'full') { ?>
								<th class="title">
									<?php echo JText::_('UNIT_PRICE'); ?>
								</th>
<?php } ?>
								<th class="title titletoggle">
									<?php echo JText::_('PRODUCT_QUANTITY'); ?>
								</th>
<?php if($this->invoice_type == 'full') { ?>
								<th class="title titletoggle">
									<?php echo JText::_('PRICE'); ?>
								</th>
<?php } ?>
							</tr>
						</thead>
						<tbody>
<?php
	$k = 0;
	$group = $this->shopConfig->get('group_options', 0);

	foreach($this->order->products as $product) {
		if($group && $product->order_product_option_parent_id)
			continue;
?>
							<tr class="row<?php echo $k;?>">
								<td>
									<?php echo $product->order_product_name.' '.$product->order_product_code; ?>
									<p class="hikashop_order_product_custom_item_fields">
<?php
		if($group) {
			$display_item_price = false;
			foreach($this->order->products as $j => $optionElement) {
				if($optionElement->order_product_option_parent_id != $product->order_product_id)
					continue;
				if($optionElement->order_product_price > 0) {
					$display_item_price = true;
				}

			}
			if($display_item_price) {
				if($this->shopConfig->get('price_with_tax')) {
					echo ' '.$this->currencyHelper->format($product->order_product_price+$product->order_product_tax,$this->order->order_currency_id);
				} else {
					echo ' '.$this->currencyHelper->format($product->order_product_price,$this->order->order_currency_id);
				}
			}
		}

		if(hikashop_level(2) && !empty($this->fields['item'])) {
			foreach($this->fields['item'] as $field) {
				$namekey = $field->field_namekey;
				if(empty($product->$namekey))
					continue;

				echo '<p class="hikashop_order_item_'.$namekey.'">'.$this->fieldsClass->getFieldName($field).': '.$this->fieldsClass->show($field,$product->$namekey).'</p>';
			}
		}
		if($group) {
			foreach($this->order->products as $j => $optionElement) {
				if($optionElement->order_product_option_parent_id != $product->order_product_id) continue;

				$product->order_product_price +=$optionElement->order_product_price;
				$product->order_product_tax +=$optionElement->order_product_tax;
				$product->order_product_total_price+=$optionElement->order_product_total_price;
				$product->order_product_total_price_no_vat+=$optionElement->order_product_total_price_no_vat;

?>
										<p class="hikashop_order_option_name">
<?php
				echo $optionElement->order_product_name;
				if($optionElement->order_product_price > 0) {
					if($this->shopConfig->get('price_with_tax')) {
						echo ' ( + '.$this->currencyHelper->format($optionElement->order_product_price+$optionElement->order_product_tax,$this->order->order_currency_id).' )';
					} else {
						echo ' ( + '.$this->currencyHelper->format($optionElement->order_product_price,$this->order->order_currency_id).' )';
					}
				}
?>
										</p>
<?php
			}
		}
?>
									</p>
								</td>
<?php if ($this->shopConfig->get('show_code')) { ?>
										<td><p class="hikashop_product_code_invoice"><?php echo $product->order_product_code; ?></p></td>
<?php } ?>

<?php if($this->invoice_type == 'full') {?>
								<td>
<?php
			if($this->shopConfig->get('price_with_tax')) {
				echo $this->currencyHelper->format($product->order_product_price+$product->order_product_tax,$this->order->order_currency_id);
			} else {
				echo $this->currencyHelper->format($product->order_product_price,$this->order->order_currency_id);
			}
?>
								</td>
<?php } ?>
								<td align="center"><?php echo $product->order_product_quantity;?></td>
<?php if($this->invoice_type == 'full') { ?>
								<td>
<?php
			if($this->shopConfig->get('price_with_tax')) {
				echo $this->currencyHelper->format($product->order_product_total_price,$this->order->order_currency_id);
			} else {
				echo $this->currencyHelper->format($product->order_product_total_price_no_vat,$this->order->order_currency_id);
			}
?>
								</td>
<?php } ?>
							</tr>
<?php
		$k = 1 - $k;
	}
?>
<?php if($this->invoice_type == 'full') { ?>
							<tr>
								<td style="border-top:2px solid #B8B8B8;" colspan="2"></td>
								<td style="border-top:2px solid #B8B8B8;" class="key">
									<label><?php echo JText::_('SUBTOTAL'); ?></label>
								</td>
								<td style="border-top:2px solid #B8B8B8;">
<?php
	if($this->shopConfig->get('price_with_tax')) {
		echo $this->currencyHelper->format($this->order->order_subtotal,$this->order->order_currency_id);
	} else {
		echo $this->currencyHelper->format($this->order->order_subtotal_no_vat,$this->order->order_currency_id);
	}
?>
								</td>
							</tr>
<?php
	$taxes = $this->order->order_subtotal - $this->order->order_subtotal_no_vat + $this->order->order_shipping_tax - $this->order->order_discount_tax - $this->order->order_payment_tax;
	if($taxes != 0) {
		if($this->shopConfig->get('detailed_tax_display') && !empty($this->order->order_tax_info)) {
			foreach($this->order->order_tax_info as $tax) {
?>
							<tr>
								<td colspan="2">
								</td>
								<td class="hikashop_order_tax_title key">
									<label><?php
										echo $tax->tax_namekey;
									?></label>
								</td>
								<td class="hikashop_order_tax_value"><?php
									echo $this->currencyHelper->format($tax->tax_amount, $this->order->order_currency_id);
								?></td>
							</tr>
<?php
			}
		} else {
?>
							<tr>
								<td colspan="2">
								</td>
								<td class="hikashop_order_tax_title key">
									<label><?php
										echo JText::_('VAT');
									?></label>
								</td>
								<td class="hikashop_order_tax_value"><?php
									echo $this->currencyHelper->format($taxes, $this->order->order_currency_id);
								?></td>
							</tr>

<?php
		}
	}
	if($this->order->order_discount_price != 0) {
?>
							<tr>
								<td colspan="2">
								</td>
								<td class="key">
									<label><?php
										echo JText::_('HIKASHOP_COUPON');
									?></label>
								</td>
								<td>
<?php
		if($this->shopConfig->get('price_with_tax')) {
			echo $this->currencyHelper->format($this->order->order_discount_price * -1.0, $this->order->order_currency_id);
		} else {
			echo $this->currencyHelper->format(($this->order->order_discount_price - @$this->order->order_discount_tax) * -1.0, $this->order->order_currency_id);
		}
?>
								</td>
							</tr>
<?php }
	if(!empty($this->order->additional)) {
		$exclude_additionnal = explode(',', $this->shopConfig->get('order_additional_hide', ''));
		foreach($this->order->additional as $additional) {
			if(in_array($additional->order_product_name, $exclude_additionnal))
				continue;
?>
							<tr>
								<td colspan="2">
								</td>
								<td class="hikashop_order_additionall_title key">
									<label><?php
										echo JText::_($additional->order_product_name);
									?></label>
								</td>
								<td><?php
									if(!empty($additional->order_product_price)) {
										$additional->order_product_price = (float)$additional->order_product_price;
									}
									if(!empty($additional->order_product_price) || empty($additional->order_product_options)) {
										echo $this->currencyHelper->format($additional->order_product_price, $this->order->order_currency_id);
									} else {
										echo $additional->order_product_options;
									}
								?></td>
							</tr>
<?php
		}
	}
	if($this->order->order_shipping_price != 0) {
?>
							<tr>
								<td colspan="2">
								</td>
								<td class="key">
									<label><?php echo JText::_( 'SHIPPING' ); ?></label>
								</td>
								<td>
<?php
		if($this->shopConfig->get('price_with_tax')) {
			echo $this->currencyHelper->format($this->order->order_shipping_price, $this->order->order_currency_id);
		} else {
			echo $this->currencyHelper->format($this->order->order_shipping_price - @$this->order->order_shipping_tax, $this->order->order_currency_id);
		}
?>
								</td>
							</tr>
<?php
	}
	if($this->order->order_payment_price != 0 || ($this->shopConfig->get('price_with_tax') && $this->order->order_payment_tax != 0)) {
?>
							<tr>
								<td colspan="2">
								</td>
								<td class="key">
									<label><?php echo JText::_( 'HIKASHOP_PAYMENT' ); ?></label>
								</td>
								<td><?php
		if($this->shopConfig->get('price_with_tax')) {
			echo $this->currencyHelper->format($this->order->order_payment_price, $this->order->order_currency_id);
		} else {
			echo $this->currencyHelper->format($this->order->order_payment_price - @$this->order->order_payment_tax, $this->order->order_currency_id);
		}
								?></td>
							</tr>
<?php } ?>
							<tr>
								<td colspan="2">
								</td>
								<td class="key">
									<label><?php echo JText::_( 'HIKASHOP_TOTAL' ); ?></label>
								</td>
								<td><?php echo $this->currencyHelper->format($this->order->order_full_price, $this->order->order_currency_id); ?></td>
							</tr>
<?php } ?>
						</tbody>
					</table>
				</fieldset>
			</td>
		</tr>
<?php
	JPluginHelper::importPlugin('hikashop');
	JPluginHelper::importPlugin('hikamarket');
	JFactory::getApplication()->triggerEvent('onAfterOrderProductsListingDisplay', array(&$this->order, 'order_frontvendor_invoice'));

	if($this->invoice_type == 'full') {
		if(!empty($this->fields['order']) && hikashop_level(2)) {
?>
		<tr>
			<td>
				<fieldset class="hikashop_order_custom_fields_fieldset">
					<legend><?php echo JText::_('ADDITIONAL_INFORMATION'); ?></legend>
					<table class="hikashop_order_custom_fields_table adminlist" cellpadding="1" width="100%">
<?php foreach($this->fields['order'] as $fieldName => $oneExtraField) { ?>
						<tr class="hikashop_order_custom_field_<?php echo $fieldName;?>_line">
							<td class="key"><?php echo $this->fieldsClass->getFieldName($oneExtraField);?></td>
							<td><?php echo $this->fieldsClass->show($oneExtraField, @$this->order->$fieldName); ?></td>
						</tr>
<?php } ?>
					</table>
				</fieldset>
			</td>
		</tr>
<?php } ?>
		<tr>
			<td>
<?php
	if(!empty($this->shipping)) {
		echo JText::_('HIKASHOP_SHIPPING_METHOD').' : ';
		if(is_string($this->order->order_shipping_method))
			echo $this->shipping->getName($this->order->order_shipping_method, $this->order->order_shipping_id);
		else
			echo implode(', ', $this->order->order_shipping_method);
		echo '<br/>';
	}
	if(!empty($this->payment)) {
		echo JText::_('HIKASHOP_PAYMENT_METHOD').' : '.$this->payment->getName($this->order->order_payment_method, $this->order->order_payment_id);
	}
?>
			</td>
		</tr>
<?php } ?>
		<tr>
			<td>
			</td>
		</tr>
	</table>
</div>
<div style="page-break-after:always"></div>
