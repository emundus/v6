<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><h1><?php echo JText::_('ORDER_ADD_INFO'); ?></h1>
<form action="<?php echo hikashop_completeLink('order&task=save&subtask=additional&tmpl=component'); ?>" name="hikashop_order_additional_form" id="hikashop_order_additional_form" method="post" enctype="multipart/form-data">
<?php 
	if(isset($this->edit) && $this->edit === true) {
?>
<?php
		if(!empty($this->order->additional)) {
?>
	<input type="hidden" name="data[order][additional]" value="1"/>
<?php
		}
?>
	<table class="hikashop_order_additional_table adminlist table table-striped">
		<thead>
			<tr>
				<th class="title">
				</th>
				<th class="title">
					<?php echo JText::_('INFORMATION'); ?>
				</th>
				<th class="title">
					<?php echo JText::_('PRICE'); ?>
				</th>
				<th class="title">
					<?php echo JText::_('TAXES'); ?>
				</th>
				<th class="title">
					<?php echo JText::_('PRICE_WITH_TAX'); ?>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td class="additional_row_title">
					<?php echo JText::_('SUBTOTAL'); ?>
				</td>
				<td>
				</td>
				<td>
				<?php echo $this->currencyHelper->format($this->order->order_subtotal_no_vat,$this->order->order_currency_id); ?>
				</td>
				<td>
				</td>
				<td>
					<?php echo $this->currencyHelper->format($this->order->order_subtotal,$this->order->order_currency_id); ?>
				</td>
			</tr>
			<tr>
				<td class="additional_row_title">
					<?php echo JText::_('HIKASHOP_COUPON'); ?>
				</td>
				<td>
					<input type="text" name="data[order][order_discount_code]" value="<?php echo $this->escape(@$this->order->order_discount_code); ?>" />
				</td>
				<td>
					<input type="text" id="hikashop_order_discount_price_input" name="hikashop_order_discount_price_without_tax_input" onchange="window.orderMgr.updateAdditionalTaxes(this, 'discount');" value="<?php echo (@$this->order->order_discount_price-@$this->order->order_discount_tax); ?>" />
				</td>
				<td>
					<input type="hidden" id="hikashop_order_discount_tax_input" name="data[order][order_discount_tax]" value="<?php echo @$this->order->order_discount_tax; ?>" />
					<?php echo str_replace('id="dataorderorder_discount_tax_namekey"', 'id="hikashop_order_discount_tax_namekey"', $this->ratesType->display( "data[order][order_discount_tax_namekey]" , @$this->order->order_discount_tax_namekey, true,'onchange="window.orderMgr.updateAdditionalTaxes(this, \'discount\');"', true )); ?>
				</td>
				<td>
					<input type="text" id="hikashop_order_discount_price_with_tax_input" name="data[order][order_discount_price]" onchange="window.orderMgr.updateAdditionalTaxes(this, 'discount');" value="<?php echo @$this->order->order_discount_price; ?>" />
				</td>
			</tr>
			<tr>
				<td class="additional_row_title">
					<?php echo JText::_('SHIPPING'); ?>
				</td>
				<td>
				<?php if(strpos($this->order->order_shipping_id, ';') === false) { ?>
					<?php echo $this->shippingPlugins->display('data[order][shipping]',$this->order->order_shipping_method,$this->order->order_shipping_id); ?><br/>
				<?php } ?>
				</td>
				<td>
					<input type="text" id="hikashop_order_shipping_price_input" name="hikashop_order_shipping_price_without_tax_input" value="<?php echo ($this->order->order_shipping_price-@$this->order->order_shipping_tax); ?>" onchange="window.orderMgr.updateAdditionalTaxes(this, 'shipping');" />
				</td>
				<td>
					<input type="hidden" id="hikashop_order_shipping_tax_input" name="data[order][order_shipping_tax]" value="<?php echo @$this->order->order_shipping_tax; ?>" />
					<?php echo str_replace('id="dataorderorder_shipping_tax_namekey"', 'id="hikashop_order_shipping_tax_namekey"', $this->ratesType->display( "data[order][order_shipping_tax_namekey]" , @$this->order->order_shipping_tax_namekey, true,'onchange="window.orderMgr.updateAdditionalTaxes(this, \'shipping\');"', true )); ?>
				</td>
				<td>
					<input type="text" id="hikashop_order_shipping_price_with_tax_input" name="data[order][order_shipping_price]" onchange="window.orderMgr.updateAdditionalTaxes(this, 'shipping');" value="<?php echo @$this->order->order_shipping_price; ?>" />
				</td>
			</tr>
			<tr>
				<td class="additional_row_title">
					<?php echo JText::_('HIKASHOP_PAYMENT'); ?>
				</td>
				<td>
					<?php echo $this->paymentPlugins->display('data[order][payment]',$this->order->order_payment_method,$this->order->order_payment_id); ?>
				</td>
				<td>
					<input type="text" id="hikashop_order_payment_price_input" name="hikashop_order_payment_price_without_tax_input" value="<?php echo ($this->order->order_payment_price-@$this->order->order_payment_tax); ?>" onchange="window.orderMgr.updateAdditionalTaxes(this, 'payment');" />
				</td>
				<td>
					<input type="hidden" id="hikashop_order_payment_tax_input" name="data[order][order_payment_tax]" value="<?php echo @$this->order->order_payment_tax; ?>" />
					<?php echo str_replace('id="dataorderorder_payment_tax_namekey"', 'id="hikashop_order_payment_tax_namekey"', $this->ratesType->display( "data[order][order_payment_tax_namekey]" , @$this->order->order_payment_tax_namekey, true,'onchange="window.orderMgr.updateAdditionalTaxes(this, \'payment\');"', true )); ?>
				</td>
				<td>
					<input type="text" id="hikashop_order_payment_price_with_tax_input" name="data[order][order_payment_price]" onchange="window.orderMgr.updateAdditionalTaxes(this, 'payment');" value="<?php echo @$this->order->order_payment_price; ?>" />
				</td>
			</tr>
<?php
	if(!empty($this->order->additional)) {
		foreach($this->order->additional as $additional) {
			if(!empty($additional->order_product_price)) {
				$additional->order_product_price = (float)$additional->order_product_price;
			}
			if(!empty($additional->order_product_price) || empty($additional->order_product_options)) {
				$name = 'order_product_price';
				$value = $additional->order_product_price;
			} else {
				$name = 'order_product_options';
				$value = $additional->order_product_options;
			}
?>
			<tr>
				<td class="additional_row_title">
					<?php echo JText::_($additional->order_product_name); ?>
					<input type="hidden" name="data[order][product][<?php echo $additional->order_product_name; ?>][order_product_id]" value="<?php echo $additional->order_product_id; ?>"/>
					<input type="hidden" name="data[order][product][<?php echo $additional->order_product_name; ?>][order_product_code]" value="order additional"/>
					<input type="hidden" name="data[order][product][<?php echo $additional->order_product_name; ?>][order_product_quantity]" value="0"/>
				</td>
				<td>
				</td>
				<td>
				</td>
				<td>
				</td>
				<td>
					<input type="text" name="data[order][product][<?php echo $additional->order_product_name; ?>][<?php echo $name; ?>]" value="<?php echo $value; ?>"/>
				</td>
			</tr>
<?php
		}
	}
?>
			<tr>
				<td class="additional_row_title">
					<?php echo JText::_('HIKASHOP_TOTAL'); ?>
				</td>
				<td>
				</td>
				<td>
				</td>
				<td>
				</td>
				<td>
					<?php echo $this->currencyHelper->format($this->order->order_full_price,$this->order->order_currency_id); ?>
				</td>
			</tr>
		</tbody>
	</table>
	<?php
		if(strpos($this->order->order_shipping_id, ';') !== false) {
?>
			<table class="hikam_table table table-striped">
				<thead>
					<tr>
						<th><?php echo JText::_('WAREHOUSE'); ?></th>
						<th><?php echo JText::_('HIKASHOP_SHIPPING_METHOD'); ?></th>
						<th><?php echo JText::_('SHIPPING_PRICE'); ?></th>
						<th><?php echo JText::_('SHIPPING_TAX'); ?></th>
					</tr>
				</thead>
				<tbody>
<?php
			$warehouses = array(
				JHTML::_('select.option', 0, JText::_('HIKA_NONE'))
			);
			$shipping_ids = explode(';', $this->order->order_shipping_id);
			foreach($shipping_ids as $shipping_key) {
				$shipping_warehouse = 0;
				if(strpos($shipping_key, '@') !== false)
					list($shipping_id, $shipping_warehouse) = explode('@', $shipping_key, 2);
				else
					$shipping_id = (int)$shipping_key;
				$warehouses[] = JHTML::_('select.option', $shipping_warehouse, $shipping_warehouse);
				$shipping_method = '';
				foreach($this->order->shippings as $s) {
					if((int)$s->shipping_id == $shipping_id) {
						$shipping_method = $s->shipping_type;
						break;
					}
				}
				$k = $shipping_id.'_'.$shipping_warehouse;
				$prices = @$this->order->order_shipping_params->prices[$shipping_key];
?>
					<tr>
						<td><?php echo $shipping_warehouse; ?></td>
						<td><?php echo $this->shippingPlugins->display('data[order][shipping]['.$shipping_warehouse.']',$shipping_method,$shipping_id, true, ' style="max-width:160px;"'); ?></td>
						<td><input type="text" name="data[order][order_shipping_prices][<?php echo $shipping_warehouse; ?>]" value="<?php echo @$prices->price_with_tax; ?>" /></td>
						<td><input type="text" name="data[order][order_shipping_taxs][<?php echo $shipping_warehouse; ?>]" value="<?php echo @$prices->tax; ?>" /></td>
					</tr>
<?php
			}
?>				</tbody>
			</table>
			<table class="hika_table table table-striped">
				<thead>
					<tr>
						<th><?php echo JText::_('PRODUCT'); ?></th>
						<th><?php echo JText::_('WAREHOUSE'); ?></th>
					</tr>
				</thead>
				<tbody>
<?php
			foreach($this->order->products as $k => $product) {
				$map = 'data[order][warehouses]['.$product->order_product_id.']';
				$value = 0;
				if(strpos($product->order_product_shipping_id, '@') !== false)
					$value = substr($product->order_product_shipping_id, strpos($product->order_product_shipping_id, '@')+1);
?>
					<tr>
						<td><?php echo $product->order_product_name; ?></td>
						<td><?php echo JHTML::_('select.genericlist', $warehouses, $map, 'class="custom-select"', 'value', 'text', $value); ?></td>
					</tr>
<?php
			}
?>
				</tbody>
			</table>
<?php
	} ?>
	<dl class="hika_options">
<?php } else { ?>
	<dl class="hika_options">
		<dt class="hikashop_order_additional_subtotal"><label><?php echo JText::_('SUBTOTAL'); ?></label></dt>
		<dd class="hikashop_order_additional_subtotal"><span><?php echo $this->currencyHelper->format($this->order->order_subtotal,$this->order->order_currency_id); ?></span></dd>
		<dt class="hikashop_order_additional_coupon"><label><?php echo JText::_('HIKASHOP_COUPON'); ?></label></dt>
		<dd class="hikashop_order_additional_coupon"><span><?php echo $this->currencyHelper->format($this->order->order_discount_price*-1.0,$this->order->order_currency_id); ?> <?php echo $this->order->order_discount_code; ?></span></dd>
		<dt class="hikashop_order_additional_shipping"><label><?php echo JText::_('SHIPPING'); ?></label></dt>
		<dd class="hikashop_order_additional_shipping"><span><?php echo $this->currencyHelper->format($this->order->order_shipping_price, $this->order->order_currency_id); ?> - <?php
			if(empty($this->order->order_shipping_method))
				echo '<em>'.JText::_('HIKA_NONE').'</em>';
			else
				echo $this->order->order_shipping_method;
			?></span></dd>
		<dt class="hikashop_order_additional_payment_fee"><label><?php echo JText::_('HIKASHOP_PAYMENT'); ?></label></dt>
		<dd class="hikashop_order_additional_payment_fee"><span><?php echo $this->currencyHelper->format($this->order->order_payment_price, $this->order->order_currency_id); ?> - <?php
			if(empty($this->order->order_payment_method))
				echo '<em>'.JText::_('HIKA_NONE').'</em>';
			else
				echo $this->order->order_payment_method;
			?></span></dd>
		<dt class="hikashop_order_additional_total"><label><?php echo JText::_('HIKASHOP_TOTAL'); ?></label></dt>
		<dd class="hikashop_order_additional_total"><span><?php echo $this->currencyHelper->format($this->order->order_full_price,$this->order->order_currency_id); ?></span></dd>
<?php }
?>
<?php
	if(!empty($this->extra_data['additional'])) {
		foreach($this->extra_data['additional'] as $key => $content) {
?>		<dt class="hikashop_order_additional_<?php echo $key; ?>"><label><?php echo JText::_($content['title']); ?></label></dt>
		<dd class="hikashop_order_additional_<?php echo $key; ?>"><span><?php echo $content['data']; ?></span></dd>
<?php
		}
	}

	if(!empty($this->fields['order'])) {
		$editCustomFields = false;
		if(isset($this->edit) && $this->edit === true) {
			$editCustomFields = true;
		}
		$after = array();
		foreach($this->fields['order'] as $fieldName => $oneExtraField) {
?>
		<dt class="hikashop_order_additional_customfield hikashop_order_additional_customfield_<?php echo $fieldName; ?>"><?php echo $this->fieldsClass->getFieldName($oneExtraField);?></dt>
		<dd class="hikashop_order_additional_customfield hikashop_order_additional_customfield_<?php echo $fieldName; ?>"><span><?php
			if($editCustomFields) {
				$html = $this->fieldsClass->display($oneExtraField, @$this->order->$fieldName, 'data[orderfields]['.$fieldName.']');
				if($oneExtraField->field_type=='hidden') {
					$after[] = $thml;
					continue;
				}
				echo $html;
			} else {
				echo $this->fieldsClass->show($oneExtraField, @$this->order->$fieldName);
			}
		?></span></dd>
<?php
		}
		if(count($after)) {
			echo implode("\r\n", $after);
		}
	}

?>
		<dt class="hikashop_orderadditional_history"><label><?php echo JText::_('HISTORY'); ?></label></dt>
		<dd class="hikashop_orderadditional_history">
			<span><input onchange="window.orderMgr.orderadditional_history_changed(this);" type="checkbox" id="hikashop_history_orderadditional_store" name="data[history][store_data]" value="1"/><label for="hikashop_history_orderadditional_store" style="display:inline-block"><?php echo JText::_('SET_HISTORY_MESSAGE');?></label></span><br/>
			<textarea id="hikashop_history_orderadditional_msg" name="data[history][msg]" style="display:none;"></textarea>
		</dd>
		<dd class="hikashop_orderadditional_usermsg">
			<span><input onchange="window.orderMgr.orderadditional_usermsg_changed(this);" type="checkbox" id="hikashop_history_orderadditional_usermsg_send" name="data[history][usermsg_send]" value="1"/><label for="hikashop_history_orderadditional_usermsg_send" style="display:inline-block"><?php echo JText::_('SEND_USER_MESSAGE');?></label></span><br/>
			<textarea id="hikashop_history_orderadditional_usermsg" name="data[history][usermsg]" style="display:none;"></textarea>
		</dd>

		<a href="#save" class="btn btn-success" onclick="document.getElementById('hikashop_order_notify').value = 1;return window.hikashop.submitform('save','hikashop_order_additional_form');"><i class="fa fa-save"></i> <?php echo JText::_('HIKA_SAVE_AND_NOTIFY'); ?></a>
		<a href="#save" class="btn btn-success" onclick="return window.hikashop.submitform('save','hikashop_order_additional_form');"><i class="fa fa-save"></i> <?php echo JText::_('HIKA_SAVE'); ?></a>

<script type="text/javascript">
if(!window.orderMgr)
	window.orderMgr = {};
window.orderMgr.orderadditional_history_changed = function(el) {
	var fields = ['hikashop_history_orderadditional_msg'], displayValue = '';
	if(!el.checked) displayValue = 'none';
	window.hikashop.setArrayDisplay(fields, displayValue);
}
window.orderMgr.orderadditional_usermsg_changed = function(el) {
	var fields = ['hikashop_history_orderadditional_usermsg'], displayValue = '';
	if(!el.checked) displayValue = 'none';
	window.hikashop.setArrayDisplay(fields, displayValue);
}
window.orderMgr.updateAdditionalTaxes = function(el, type) {
	var priceInput = document.getElementById('hikashop_order_'+type+'_price_input');
	var priceWithTaxInput = document.getElementById('hikashop_order_'+type+'_price_with_tax_input');
	var taxRateSelect = document.getElementById('hikashop_order_'+type+'_tax_namekey');
	var taxInput = document.getElementById('hikashop_order_'+type+'_tax_input');

	var conversion = 0;
	var price = priceInput.value;
	var target = priceInput;
	if(el.id == priceWithTaxInput.id) {
		conversion = 1;
		price = priceWithTaxInput.value;
	} else {
		target = priceWithTaxInput;
	}
	if(taxRateSelect.value == '-1') {
		target.value = price;
		taxInput.value = 0;
	} else {
		window.Oby.xRequest(
			'index.php?option=com_hikashop&tmpl=component&ctrl=product&task=getprice&price='+price+'&rate_namekey='+taxRateSelect.value+'&conversion='+conversion,
			{ mode: 'GET'},
			function(result) {
				if(result.responseText) {
					target.value = result.responseText;
					if(conversion)
						taxInput.value = price-result.responseText;
					else
						taxInput.value = result.responseText-price;
				}
			}
		);
	}

}
<?php if(!empty($this->extra_data['js'])) { echo $this->extra_data['js']; } ?>
</script>
	</dl>
	<input type="hidden" name="data[notify]" id="hikashop_order_notify" value="0" />
	<input type="hidden" name="data[additional]" value="1" />
	<input type="hidden" name="data[customfields]" value="1" />
	<input type="hidden" name="cid[]" value="<?php echo @$this->order->order_id; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="save" />
	<input type="hidden" name="subtask" value="additional" />
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" name="ctrl" value="order" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
