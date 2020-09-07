<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2020 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><table class="hikashop_product_options_table">
<?php
$this->show_option_quantity = false;

$old_show_discount = $this->params->get('show_discount');
$old_per_unit = $this->params->get('per_unit', 1);

$this->params->set('show_discount', 0);
$this->params->set('per_unit', 0);
$this->params->set('from_module', 1);

$i = 0;
$js_product_data = array();

$price_name = 'price_value';
if($this->params->get('price_with_tax'))
	$price_name = 'price_value_with_tax';


foreach($this->element->options as $optionElement) {
	$option_values = array();
	$value = 0;
	$id = 'hikashop_product_option_'.$i;

	if(!empty($optionElement->variants)) {
		$optionInfo =& $optionElement->main;
	} else {
		$optionInfo =& $optionElement;
	}

	$selectionMethod = $this->config->get('product_selection_method', 'generic');
	if($selectionMethod == 'per_product' && !empty($optionInfo->product_option_method)) {
		$selectionMethod = $optionInfo->product_option_method;
	}
	if(!in_array($selectionMethod, array('generic', 'radio', 'check')))
		$selectionMethod = 'generic';
	$map = 'hikashop_product_option[]';
	if($selectionMethod == 'radio')
		$map = 'hikashop_product_option['.$i.']';

	if(empty($optionElement->variants)) {
		if(!$optionElement->product_published || empty($optionElement->product_quantity))
			continue;
		if($selectionMethod != 'check')
			$option_values[] = JHTML::_('select.option', 0, JText::_('HIKASHOP_NO'));
		$text = JText::_('HIKASHOP_YES');
		$this->row =& $optionElement;
		if(!empty($optionElement->prices) && $this->params->get('show_price')) {
			$ok = null;
			$positive = 1;
			$unit_price = false;
			foreach($optionElement->prices as $k => $price){
				if($unit_price)
					continue;
				if($price->price_min_quantity <= 1)
					$unit_price = true;

				if($price->price_value < 0) $positive=false;
				if(!$unit_price && (($positive && $price->price_value > $ok->price_value) || (!$positive && $price->price_value < $ok->price_value)))
					continue;
				$ok = $price;
			}
			$price = $ok->$price_name;
			$text .= ' ( '.($positive?'+ ':'').$this->currencyHelper->format($price, $ok->price_currency_id).' )';

			$js_product_data[(int)$optionElement->product_id] = (float)str_replace(',','.',$price);
		}
		$option_values[] = JHTML::_('select.option', $optionElement->product_id, $text);
	} else {
		if($this->config->get('add_no_to_options', 0) && $selectionMethod != 'check') {
			$option_values[] = JHTML::_('select.option', 0,JText::_('HIKASHOP_NO'));
		}
		if($this->config->get('select_option_default_value', 1) && $selectionMethod != 'check') {
			$defaultValue = array();
			if(!empty($optionElement->characteristics) && is_array($optionElement->characteristics)) {
				foreach($optionElement->characteristics as $char){
						$defaultValue[]=$char->characteristic_id;
				}
			}
		}

		foreach($optionElement->variants as $variant) {
			if(!$variant->product_published || empty($variant->product_quantity)) continue;
			if($variant->product_sale_start > time()) continue;
			if($variant->product_sale_end != '' && $variant->product_sale_end != '0' && $variant->product_sale_end < time()) continue;

			if(!empty($variant->variant_name)) {
				$text = $variant->variant_name;
			} else if(!empty($variant->characteristics_text)) {
				$text = $variant->characteristics_text;
			} else {
				$text = $variant->product_name;
			}
			$this->row =& $variant;

			if(!empty($variant->prices) && $this->params->get('show_price')) {
				$ok = null;
				$positive = 1;
				$unit_price = false;
				foreach($variant->prices as $k => $price) {
					if($unit_price)
						continue;
					if($price->price_min_quantity <= 1)
						$unit_price = true;
					if($price->price_value < 0) $positive=false;
					if(!$unit_price && (($positive && $price->price_value > $ok->price_value) || (!$positive && $price->price_value < $ok->price_value)))
						continue;
					$ok = $price;
				}

				$price = $ok->$price_name;
				$text .= ' ( '.($positive?'+ ':'').$this->currencyHelper->format($price, $ok->price_currency_id).' )';

				$js_product_data[(int)$variant->product_id] = (float)str_replace(',','.',$price);
			}

			if(!empty($defaultValue) && !empty($variant->characteristics) && is_array($variant->characteristics)) {
				$default = true;
				foreach($variant->characteristics as $char) {
					if(!in_array($char->characteristic_id, $defaultValue)) {
						$default = false;
					}
				}
				if($default) {
					$value = $variant->product_id;
				}
			}
			$option_values[] = JHTML::_('select.option', $variant->product_id, $text);
		}
	}

	if(!count($option_values))
		continue;

	$select = ($selectionMethod == 'check') ? 'radio' : $selectionMethod;
	$html = JHTML::_('select.'.$select.'list', $option_values, $map, 'class="custom-select" size="1" data-product-option="'.$i.'" onchange="hikaProductOptions.change();"', 'value', 'text', (int)$value, $id);
	if($selectionMethod == 'check')
		$html = str_replace('type="radio"', 'type="checkbox"', $html);

	$options = '';
	if(!empty($optionInfo->product_description) || !empty($optionInfo->product_url)) {
		$description = '';
		if(!empty($optionInfo->product_description)) {
			$description = $this->escape(html_entity_decode(strip_tags(JHTML::_('content.prepare',$optionInfo->product_description)), ENT_NOQUOTES | ENT_HTML401, 'UTF-8'));
			$options = '<span class="hikashop_option_info" title="' . $description . '" alt="Information"></span>';
		}
		if(!empty($optionInfo->product_url)) {
			if(empty($description)) {
				$description = $optionInfo->product_name;
			}
			if(empty($popup))
				$popup = hikashop_get('helper.popup');
			$options = $popup->display(
					$options,
					$optionInfo->product_name,
					$optionInfo->product_url,
					'hikashop_product option_'.$optionInfo->product_id.'_popup',
					760, 480, '', '', 'link'
				);
		}
	}
?>
	<tr>
		<td>
			<span class="hikashop_option_name"><?php
				echo $optionInfo->product_name . $options;
			?></span>
		</td>
		<td><?php
			echo $html;
		?></td>
<?php
	if(!empty($this->show_option_quantity) && $select != 'radio') {
?>
		<td>
<?php
		if($this->show_option_quantity === true || (int)$this->show_option_quantity <= 1) {
?>
			<input type="text" class="hikashop_product_quantity_field" id="hikashop_product_option_qty_<?php echo $i; ?>" name="hikashop_product_option_qty[<?php echo $i; ?>]" onchange="hikaProductOptions.change();" value="1"/>
<?php
		} else {
			$r = range(1, (int)$this->show_option_quantity, 1);
			$values = array_combine($r, $r);
			ksort($values);
			echo JHTML::_('select.genericlist', $values, 'hikashop_product_option_qty['.$i.']', 'style="width:auto;" class="no-chzn" onchange="hikaProductOptions.change();"', 'value', 'text', 1, 'hikashop_product_option_qty_'.$i);
		}
?>
		</td>
<?php
	}
?>
	</tr>
<?php
	unset($optionInfo);
	$i++;
}

global $Itemid;
$url_itemid = !empty($Itemid) ? ('&Itemid=' . $Itemid) : '';

if($this->show_option_quantity) {
	$quantity_mul = 'var main_mul = 1,
			qty_main_div = d.getElementById("hikashop_product_quantity_main");
		if(qty_main_div) {
			var qty_main = qty_main_div.querySelector("[name=\"quantity\"]");
			if(qty_main)
				main_mul = parseInt(qty_main.value);
			if(isNaN(main_mul) || main_mul <= 0)
				main_mul = 1;
		}
';
} else {
	$quantity_mul = 'var main_mul = 1;';
}

$js = '
var hikaProductOptions = {
	values: '.json_encode($js_product_data).',
	total: '.$i.',
	change: function() {
		var d = document, w = window, o = w.Oby, t = this;
		var el = null, total_opt_price = 0.0, mul = 1;
		for(var i = 0; i < t.total; i++) {
			mul = 1;
			el = d.getElementById("hikashop_product_option_qty_"+i);
			if(el) {
				mul = parseInt(el.value);
				if(isNaN(mul) || mul < 0) {
					el.value = 1;
					mul = 1;
				}
				if(mul == 0)
					continue;
			}
			el = d.getElementById("hikashop_product_option_"+i);
			if(el) {
				if(t.values[el.value])
					total_opt_price += t.values[el.value] * mul;
				continue;
			}
			if(!el && !d.querySelectorAll)
				continue;
			var els = d.querySelectorAll("[data-product-option=\""+i+"\"]");
			if(els.length == 0)
				continue;
			for(var j = els.length - 1; j >= 0; j--) {
				if(!els[j].checked)
					continue;
				if(t.values[els[j].value])
					total_opt_price += t.values[els[j].value] * mul;
			}
		}
		'.$quantity_mul.'
		var arr = d.getElementsByName("hikashop_price_product");
		for(var i = arr.length - 1; i >= 0; i--) {
			var obj = arr.item(i),
				price = d.getElementById("hikashop_price_product_" + obj.value)
				price_with_options = d.getElementById("hikashop_price_product_with_options_" + obj.value);
			if(price && price_with_options)
				price_with_options.value = (parseFloat(price.value) * main_mul) + total_opt_price;
		}

		t.refreshPrice();
		if(o && o.fireAjax)
			o.fireAjax("hkContentChanged");
	},
	refreshPrice: function() {
		var w = window, o = w.Oby, d = document, inputs = null,
			price_div = d.getElementById("hikashop_product_id_main");
		if(price_div)
			inputs = price_div.getElementsByTagName("input");
		if(!inputs[0])
			return;
		var price_with_options = d.getElementById("hikashop_price_product_with_options_" + inputs[0].value);
		if(!price_with_options)
			return;
		var target = d.getElementById("hikashop_product_price_with_options_main");
		if(target)
			o.xRequest("'.hikashop_completeLink('product&task=price'.$url_itemid,true,true).'", {mode:"POST",data:"price="+price_with_options.value,update:target});
	}
};
window.hikaProductOptions = hikaProductOptions;
window.hikashop.ready( function() { hikaProductOptions.change(); });
';

if($this->show_option_quantity) {
	$js .= 'window.Oby.registerAjax("quantity.checked", function(params){ hikaProductOptions.change(); });';
}

$doc = JFactory::getDocument();
$doc->addScriptDeclaration("\n<!--\n".$js."\n//-->\n");

$this->params->set('show_discount', $old_show_discount);
$this->params->set('per_unit', $old_per_unit);
$this->params->set('from_module', '');
?>
</table>
