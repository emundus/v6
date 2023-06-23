<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><h1><?php echo JText::_('PRODUCT'); ?>
<?php
	if(!empty($this->orderProduct->product_id))
		echo ' : ' . (int)@$this->orderProduct->product_id . ' - ' . @$this->originalProduct->product_name;
?>
</h1>
<form action="<?php echo hikashop_completeLink('order&task=save&subtask=products&tmpl=component'); ?>" name="hikashop_order_product_form" id="hikashop_order_product_form" method="post" enctype="multipart/form-data">
	<dl class="hika_options">
		<dt class="hikashop_order_product_name"><label><?php echo JText::_('HIKA_NAME'); ?></label></dt>
		<dd class="hikashop_order_product_name">
			<input type="text" name="data[order][product][order_product_name]" value="<?php echo $this->escape(@$this->orderProduct->order_product_name); ?>" />
		</dd>

		<dt class="hikashop_order_product_code"><label><?php echo JText::_('PRODUCT_CODE'); ?></label></dt>
		<dd class="hikashop_order_product_code">
			<input type="text" name="data[order][product][order_product_code]" value="<?php echo $this->escape(@$this->orderProduct->order_product_code); ?>" />
		</dd>

		<dt class="hikashop_order_product_quantity"><label><?php echo JText::_('PRODUCT_QUANTITY'); ?></label></dt>
		<dd class="hikashop_order_product_quantity">
			<input type="text" name="data[order][product][order_product_quantity]" value="<?php echo @$this->orderProduct->order_product_quantity; ?>"
<?php
	if(!empty($this->allPrices)) {
		$data = array();
		foreach($this->allPrices as $price) {
			$data[] = array((int)$price->price_min_quantity, round($price->price_value,5));
		}
		if(count($data)){
			echo ' data-prices="'.json_encode($data).'" onchange="window.orderMgr.recalculatePrice(this);"';
		}
	}
?>
			/>
		</dd>
		<dt class="hikashop_order_product_price"><label><?php echo JText::_('UNIT_PRICE'); ?></label></dt>
		<dd class="hikashop_order_product_price">
			<input type="text" id="hikashop_order_product_price_input" onchange="window.orderMgr.updateTaxes(this);" name="data[order][product][order_product_price]" value="<?php echo @$this->orderProduct->order_product_price; ?>" />
		</dd>

		<dt class="hikashop_order_product_vat"><label><?php echo JText::_('VAT'); ?></label></dt>
		<dd class="hikashop_order_product_vat">
			<input type="hidden" id="hikashop_order_product_tax_input" name="data[order][product][order_product_tax]" value="<?php echo @$this->orderProduct->order_product_tax; ?>" />
			<?php
			$tax = null;
			if(!empty($this->orderProduct->order_product_tax_info)) {
				$tax = reset($this->orderProduct->order_product_tax_info)->tax_namekey;
			}
			echo $this->ratesType->display( "data[order][product][tax_namekey]" , $tax, true,'onchange="window.orderMgr.updateTaxes(this);"', true ); ?>
		</dd>
		<dt class="hikashop_order_product_price"><label><?php echo JText::_('PRICE_WITH_TAX'); ?></label></dt>
		<dd class="hikashop_order_product_price">
			<input type="text" id="hikashop_order_product_price_with_tax_input" onchange="window.orderMgr.updateTaxes(this);" name="order_product_price_with_tax" value="<?php echo (@$this->orderProduct->order_product_price + @$this->orderProduct->order_product_tax); ?>" />
		</dd>

		<dt class="hikashop_order_product_weight"><label><?php echo JText::_('PRODUCT_WEIGHT'); ?></label></dt>
		<dd class="hikashop_order_product_weight">
			<input type="text" id="hikashop_order_product_weight_input" style="width:120px;" name="data[order][product][order_product_weight]" value="<?php echo @$this->orderProduct->order_product_weight; ?>" />
			<?php echo $this->weightType->display( "data[order][product][order_product_weight_unit]" , @$this->orderProduct->order_product_weight_unit, '', 'style="width:80px; margin-bottom:9px"'); ?>
		</dd>
		<dt class="hikashop_order_product_width"><label><?php echo JText::_('PRODUCT_WIDTH'); ?></label></dt>
		<dd class="hikashop_order_product_width">
			<input type="text" id="hikashop_order_product_width_input" name="data[order][product][order_product_width]" value="<?php echo @$this->orderProduct->order_product_width; ?>" />
		</dd>
		<dt class="hikashop_order_product_length"><label><?php echo JText::_('PRODUCT_LENGTH'); ?></label></dt>
		<dd class="hikashop_order_product_length">
			<input type="text" id="hikashop_order_product_length_input" name="data[order][product][order_product_length]" value="<?php echo @$this->orderProduct->order_product_length; ?>" />
		</dd>
		<dt class="hikashop_order_product_height"><label><?php echo JText::_('PRODUCT_HEIGHT'); ?></label></dt>
		<dd class="hikashop_order_product_height">
			<input type="text" id="hikashop_order_product_height_input" name="data[order][product][order_product_height]" value="<?php echo @$this->orderProduct->order_product_height; ?>" />
		</dd>
		<dt class="hikashop_order_product_dimension_unit"><label><?php echo JText::_('DIMENSIONS_UNIT'); ?></label></dt>
		<dd class="hikashop_order_product_dimension_unit">
			<?php echo $this->volumeType->display( "data[order][product][order_product_dimension_unit]" , @$this->orderProduct->order_product_dimension_unit); ?>
		</dd>


<?php
	if(!empty($this->extra_data['products'])) {
		foreach($this->extra_data['products'] as $key => $content) {
?>		<dt class="hikashop_order_product_<?php echo $key; ?>"><label><?php echo JText::_($content['title']); ?></label></dt>
		<dd class="hikashop_order_product_<?php echo $key; ?>"><?php echo $content['data']; ?></dd>
<?php
		}
	}

	if(!empty($this->fields['item'])) {
		$editCustomFields = true;
		$after = array();
		foreach($this->fields['item'] as $fieldName => $oneExtraField) {
?>
		<dt class="hikashop_order_product_customfield hikashop_order_product_customfield_<?php echo $fieldName; ?>"><?php echo $this->fieldsClass->getFieldName($oneExtraField);?></dt>
		<dd class="hikashop_order_product_customfield hikashop_order_product_customfield_<?php echo $fieldName; ?>"><span><?php
			if($editCustomFields) {
				$html = $this->fieldsClass->display($oneExtraField, @$this->orderProduct->$fieldName, 'data[order][product]['.$fieldName.']',false,'',true);
				if($oneExtraField->field_type=='hidden') {
					$after[] = $html;
					continue;
				}
				echo $html;
			} else {
				echo $this->fieldsClass->show($oneExtraField, @$this->orderProduct->$fieldName);
			}
		?></span></dd>
<?php
		}
		if(count($after)) {
			echo implode("\r\n", $after);
		}
	}
?>
		<dt class="hikashop_orderproduct_history"><label><?php echo JText::_('HISTORY'); ?></label></dt>
		<dd class="hikashop_orderproduct_history">
			<span><input onchange="window.orderMgr.orderproduct_history_changed(this);" type="checkbox" id="hikashop_history_orderproduct_store" name="data[history][store_data]" value="1"/><label for="hikashop_history_orderproduct_store" style="display:inline-block"><?php echo JText::_('SET_HISTORY_MESSAGE');?></label></span><br/>
			<textarea id="hikashop_history_orderproduct_msg" name="data[history][msg]" style="display:none;"></textarea>
		</dd>
<script type="text/javascript">
if(!window.orderMgr)
	window.orderMgr = {};
	dataorderproducttax_namekey
window.orderMgr.recalculatePrice = function(el) {
	var qty = parseInt(el.value);
	if (isNaN(qty))
		return;
	var priceInput = document.getElementById('hikashop_order_product_price_input');
	if(!priceInput)
		return;
	var prices = el.getAttribute('data-prices');
	if(!prices)
		return;
	prices = JSON.parse(prices);
	if(!prices)
		return;
	var priceToUse = 0;
	var minQty = 0;
	for (var i = 0; i < prices.length; i++) {
		var price = prices[i];
		if(price[0] <= qty && (price[0] > minQty || minQty == 0)) {
			minQty = price[0];
			priceToUse = price[1];
		}
	}
	if(priceToUse) {
		priceInput.value = priceToUse;
		priceInput.dispatchEvent(new Event('change'));
	}
}
window.orderMgr.updateTaxes = function(el) {
	var priceInput = document.getElementById('hikashop_order_product_price_input');
	var priceWithTaxInput = document.getElementById('hikashop_order_product_price_with_tax_input');
	var taxRateSelect = document.getElementById('dataorderproducttax_namekey');
	var taxInput = document.getElementById('hikashop_order_product_tax_input');

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
window.orderMgr.orderproduct_history_changed = function(el) {
	var fields = ['hikashop_history_orderproduct_msg'], displayValue = '';
	if(!el.checked) displayValue = 'none';
	window.hikashop.setArrayDisplay(fields, displayValue);
}
<?php if(!empty($this->extra_data['js'])) { echo $this->extra_data['js']; } ?>
</script>
	</dl>
<div style="clear:both;"></div>
	<a class="btn btn-success" href="#save" onclick="return window.hikashop.submitform('save','hikashop_order_product_form');"><i class="fa fa-save"></i> <?php echo JText::_('HIKA_SAVE'); ?></a>
	<input type="hidden" name="data[order][history][history_type]" value="modification" />
	<input type="hidden" name="data[order][product][order_product_id]" value="<?php echo @$this->orderProduct->order_product_id;?>" />
	<input type="hidden" name="data[order][product][product_id]" value="<?php echo @$this->orderProduct->product_id;?>" />
	<input type="hidden" name="data[order][product][order_id]" value="<?php echo @$this->orderProduct->order_id;?>" />
<?php
	if(!empty($this->afterParams->parent_id)) {
?>
	<input type="hidden" name="data[order][product][order_product_option_parent_id]" value="<?php echo $this->afterParams->parent_id;?>" />
<?php
	}
?>
	<input type="hidden" name="data[products]" value="1" />
	<input type="hidden" name="cid[]" value="<?php echo @$this->orderProduct->order_id; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="save" />
	<input type="hidden" name="subtask" value="products" />
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" name="ctrl" value="order" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
