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
$labelcolumnclass = 'hkc-xs-4';
$inputcolumnclass = 'hkc-xs-8';
?>
<form action="<?php echo hikashop_completeLink('cart&task=product_save'); ?>" method="post" name="hikashop_cart_product_form" enctype="multipart/form-data">
	<fieldset class="hkform-horizontal">
<!-- TITLE -->
		<h3><?php echo JText::_('EDIT_PRODUCT_INFORMATION_IN_THE_CART'); ?></h3>
<!-- EO TITLE -->
<!-- NAME -->
		<div class="hkform-group control-group hikashop_item_product_name_line" id="hikashop_item_product_name">
			<div class="<?php echo $labelcolumnclass;?> hkcontrol-label">
<?php
$thumbnail_x = $this->config->get('thumbnail_x', 100);
$thumbnail_y = $this->config->get('thumbnail_y', 100);
$default_params = $this->config->get('default_params');
$price_name = 'price_value';
if($this->config->get('price_with_tax'))
	$price_name = 'price_value_with_tax';
$image = null;
if(!empty($this->product->images)) {
	$image = reset($this->product->images);
	$this->imageHelper->checkSize($thumbnail_x, $thumbnail_y, $image);
}
$img = $this->imageHelper->getThumbnail(
	@$image->file_path,
	array(
		'width' => $thumbnail_x,
		'height' => $thumbnail_y
	),
	array(
		'default' => true,
		'forcesize' => $this->config->get('image_force_size', true),
		'scale' => $this->config->get('image_scale_mode', 'inside')
	)
);
if($img->success) {
	$attributes = '';
	if($img->external)
		$attributes = ' width="'.$img->req_width.'" height="'.$img->req_height.'"';
	echo '<img class="hikashop_product_edit_cart_image" title="'.$this->escape((string)@$image->file_description).'" alt="'.$this->escape((string)@$image->file_name).'" src="'.$img->url.'"'.$attributes.'/>';
}

$text = '';
if(!empty($this->product->prices) && !empty($default_params['show_price'])) {
	$ok = null;
	$positive = 1;
	$unit_price = false;
	foreach($this->product->prices as $k => $price){
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
	$text = ' ( '.$this->currencyClass->format($price, $ok->price_currency_id).' )';

}

?>
			</div>
			<div class="<?php echo $inputcolumnclass;?> hikashop_item_product_name_text" style="height:<?php echo $thumbnail_y; ?>px;">
				<div>
					<p class="hikashop_item_product_name_p"><?php echo $this->product->product_name.$text; ?></p>
					<p class="hikashop_item_product_qty"><?php echo JText::sprintf('WITH_A_QUANTITY_OF_X', $this->product->cart_product_quantity); ?></p>
				</div>
			</div>
		</div>
<!-- EO NAME -->
<!-- CHARACTERISTICS -->
<?php

if(!empty($this->parentProduct->characteristics)) {
	$characteristic_display = $this->config->get('characteristic_display', 'table');
	if($characteristic_display == 'list')
		$characteristic_display = 'dropdown';

	$app = JFactory::getApplication();
	$characteristics_dynamic_display = $this->config->get('characteristics_dynamic_display', 1);
	$this->characteristics = &$this->parentProduct->main->characteristics;
	foreach($this->characteristics as $k => $characteristic) {
		if(empty($this->characteristics[$k]->characteristic_display_method))
			$this->characteristics[$k]->characteristic_display_method = $characteristic_display;
	}

	$html = '';
	$js = '';
	$ids = array_keys($this->characteristics);
	$list = '[\''.implode('\',\'',$ids).'\']';
?>
<input id="new_product_id" name="new_product_id" type="hidden" value="<?php echo $this->product->product_id; ?>"/>
<?php

	switch($characteristic_display){
		case 'table':
			if(count($this->characteristics) == 2) {
				$html = '';
				$firstCharacteristic = reset($this->characteristics);
				$secondCharacteristic = end($this->characteristics);

				$html.= '<table class="hikashop_product_characteristic_chooser"><tr><td></td>';
				if(!empty($secondCharacteristic->values)){
					foreach($secondCharacteristic->values as $value){
						$html.='<td>'.hikashop_translate($value->characteristic_value).'</td>';
					}
				}
				$html.='</tr>';
				$options =' onclick="return hikashopUpdateVariantData(this.value);"';
				$size=0;
				if(!empty($firstCharacteristic->values)){
					foreach($firstCharacteristic->values as $value){
						$html .= '<tr><td style="text-align:right">'.hikashop_translate($value->characteristic_value).'</td>';
						if(strlen($value->characteristic_value)>$size)
							$size=strlen($value->characteristic_value);
						if(!empty($secondCharacteristic->values)) {
							foreach($secondCharacteristic->values as $value2) {
								$class = '';
								$classspan = '';
								$extra = '';
								foreach($this->parentProduct->variants as $k => $variant) {
									$char1 = false;
									$char2 = false;
									foreach($variant->characteristics as $variantCharacteristic) {
										if($variantCharacteristic->characteristic_id == $value->characteristic_id) {
											$char1 = true;
										} elseif($variantCharacteristic->characteristic_id == $value2->characteristic_id) {
											$char2 = true;
										}
										if($char1 && $char2) {
											if(!$variant->product_published || $variant->product_quantity == 0) {
												$class = ' hikashop_product_variant_out_of_stock';
												$classspan=' hikashop_product_variant_out_of_stock_span';
											}else {
												$extra = ' data-stock="'.(int)$variant->product_quantity.'"';
											}
											break 2;
										}
									}
								}

								$name = '_'.$value->characteristic_id.'_'.$value2->characteristic_id;
								$radio = "\n\t<span class=\"hikashop_product_characteristic_span".$classspan."\"><input type=\"radio\" class=\"hikashop_product_characteristic".$class."\" name=\"hikashop_product_characteristic\"".$extra." id=\"hikashop_product_characteristic".$name."\" value=\"".$name."\" ".$options;
								if($this->characteristics[$value->characteristic_parent_id]->default->characteristic_id == $value->characteristic_id && !empty($this->characteristics[$value2->characteristic_parent_id]->default->characteristic_id) && $this->characteristics[$value2->characteristic_parent_id]->default->characteristic_id == $value2->characteristic_id) {
									$radio .= ' checked';
								}
								$radio .= " /></span>";
								$html .= '<td>'.$radio.'</td>';
							}
						}
						$html .= '</tr>';
					}
				}
				$html .= '</table>';
				if($this->config->get('characteristic_display_text')) {
					$space = '';
					for($i = 0; $i <= $size; $i++) {
						$space .= '&nbsp;&nbsp;';
					}
					$html = '<table class="hikashop_product_characteristic_chooser"><tr><td></td><td class="hikashop_charactersitic_name">'.$space.@$secondCharacteristic->characteristic_value.'</td></tr><tr><td class="hikashop_charactersitic_name">'.$firstCharacteristic->characteristic_value.'</td><td>'.$html.'</td></table>';
				}
				break;
			}

		default:
		case 'radio':
		case 'dropdown':
			$main_html = '<div class="hkform-group control-group hikashop_product_characteristics_main_div" id="hikashop_product_characteristics">';
			$count = count($this->characteristics);
			$i = 0;
			foreach($this->characteristics as $characteristic) {
				$i++;
				$main_html.='<div class="hikashop_characteristic_line_'.$characteristic->characteristic_id.'" data-characrow="'.$i.'" style="width:100%;"><div class="hkform-group control-group">';
				$values = array();
				$switch_done = false;
				if(!empty($characteristic->values)) {
					foreach($characteristic->values as $k => $value){
						if(!$this->config->get('show_out_of_stock',1)){
							$hasQuantity = false;
							foreach($this->parentProduct->variants as $variant){
								foreach($variant->characteristics as $variantCharacteristic){
									if($variantCharacteristic->characteristic_id==$value->characteristic_id){
										if($variant->product_quantity != 0){
											$hasQuantity = true;
										}elseif( $this->parentProduct->product_id==$variant->product_id && !$switch_done){
											if($characteristic->characteristic_display_method == 'dropdown'){
												$id = 'hikashop_product_characteristic_'.$characteristic->characteristic_id;
												$js = "hikashopUpdateVariant(document.getElementById('".$id."'));";
											}else{
												$id = 'hikashop_product_characteristic['.$characteristic->characteristic_id.']';
												$js = "var el = document.querySelector('[name=\"".$id."\"]'); if(el) el.checked = true; hikashopUpdateVariant(el);";
											}

											$js = "
											window.hikashop.ready( function() {".$js."});";
											$switch_done = true;
										}
									}
								}
							}
							if(!$hasQuantity)
								continue;
						}
						$values[$k] = $value->characteristic_value;
					}
				}

				if(empty($values) || !is_array($values)){
					echo JText::_('NO_VALUES_FOUND');
					echo '</div>';
					return;
				}

				if(is_array($this->characteristics)){
					$characteristic_id = @$characteristic->characteristic_id;
					@$characteristic->characteristic_id = 'hikashop_product_characteristic['.$characteristic_id.']';
					$id = 'hikashop_product_characteristic_'.$characteristic_id;
				}else{
					$id = $characteristic_id = $map;
				}
				$selected = (int)@$characteristic->default->characteristic_id;

				$this->values = array();
				if($characteristics_dynamic_display && $count > 1) {
					if($characteristic->characteristic_display_method!='radio')
						$this->values[] = JHTML::_('select.option', '', JText::_('PLEASE_SELECT') );
				}
				foreach($values as $key => $val){
					if(strlen($val)!=0 && empty($val)){
						$val = $val.'&nbsp;';
					}

					if(strpos($val, '<img ') !== false)
						$val = str_replace('<img ', '<img onclick="return hikashopVariantSelected(\'hikashop_product_characteristic_'.$characteristic_id.$key.'\');" ', $val);
					$clean = hikashop_translate(strip_tags($val));
					$optionValue = ($characteristic->characteristic_display_method != 'radio' && !empty($clean) ? $clean : $val);
					$this->values[] = JHTML::_('select.option', $key, $optionValue );
				}

				$type = 'onclick';
				if($characteristic->characteristic_display_method!='radio'){
					$characteristic->characteristic_display_method='generic';
					$type = 'onchange';
				}
				$options = ' '.$type.'="return hikashopVariantSelected(this);"';
				$options .=' data-characteristic="'.$i.'"';
				if($count == $i)
					$options .=' data-last="1"';


				$main_html .= '<div class="'.$labelcolumnclass.' hkcontrol-label">';
				if($this->config->get('characteristic_display_text') && isset($characteristic->characteristic_value)) {
					$main_html .= hikashop_translate($characteristic->characteristic_value);
				}
				$html = JHTML::_('select.'.$characteristic->characteristic_display_method.'list', $this->values, @$characteristic->characteristic_id, 'class="custom-select" size="1"' . $options, 'value', 'text', $selected, $id );
				$main_html .= '</div><div class="'. $inputcolumnclass.'">'.$html.'</div></div></div>';
			}
			$main_html .= '</div>';
			$html = $main_html;

			if($characteristics_dynamic_display) {
				$productClass = hikashop_get('class.product');
				$matches = $productClass->getAllValuesMatches($this->characteristics, $this->parentProduct->variants);
				$js.="\r\n".'window.hikashop.availableValues = [';
				if($matches) {
					foreach($matches as $value_id => $children) {
						$js.="\r\n\t"."[".implode(',',$children)."],";
					}
				}
				$js.="\r\n".'];';
			}

			break;
	}

	echo $html;
	$keys = array();
	foreach($this->parentProduct->variants as $variant){
		$variant_name = array ();
		if(!empty($variant->characteristics)) {
			foreach($variant->characteristics as $k => $ch) {
				$variant_name[] = $ch->characteristic_id;
			}
		}
		$key = '_'.implode('_', $variant_name);
		$keys[$key] = $variant->product_id;
	}
?>
	<script>
window.hikashop.variantKeys = ['<?php echo implode('\',\'', array_keys($keys)); ?>'];
window.hikashop.variantIds = ['<?php echo implode('\',\'', $keys); ?>'];
<?php echo $js; ?>

function initVariants() {
	var allRows = document.querySelectorAll('div[data-characrow]'), first = true,
	qtyArea = document.getElementById('buttons_right'), altArea = document.getElementById('buttons_right_alt');
	for (index = 0; index < allRows.length; ++index) {
		if(first) {
			first = false;
		} else {
			allRows[index].style.display = 'none';
		}
	}
	if(qtyArea) {
		qtyArea.style.display = 'none';
	}
	if(altArea) {
		altArea.style.display = '';
	}
}
function hikashopVariantSelected(obj) {
<?php if($characteristics_dynamic_display && $count > 1) { ?>
	if(typeof(obj) == "string")
		obj = document.getElementById(obj);
	if(!obj)
		return true;
	var pos = obj.getAttribute('data-characteristic'), last = obj.getAttribute('data-last'), otherRow = null,
	qtyArea = document.getElementById('buttons_right'), altArea = document.getElementById('buttons_right_alt'),
	allInputs = document.querySelectorAll('[data-characteristic]');

	if(!last) {
		var selectedElements = [];
		for (index = 0; index < allInputs.length; ++index) {
			var input = allInputs[index];
			if(input.tagName.toLowerCase() == 'select') {
				if(input.selectedIndex && input.value)
					selectedElements[selectedElements.length] = parseInt(input.options[input.selectedIndex].value);
			} else {
				if(input.checked)
					selectedElements[selectedElements.length] = parseInt(input.value);
			}
			if(selectedElements.length == pos)
				break;
		}
		var validVariants = [];
		for (index = 0; index < window.hikashop.availableValues.length; ++index) {
			var valid = true;
			for (index2 = 0; index2 < selectedElements.length; ++index2) {
				if(selectedElements[index2] != window.hikashop.availableValues[index][index2]) {
					valid = false;
					break;
				}
			}
			if(valid){
				validVariants[validVariants.length] = window.hikashop.availableValues[index];
			}
		}

		if(validVariants.length < 1 && obj.value != '') {
			console.log('characteristic value with id ' + obj.value + ' missing in window.hikashop.availableValues');
		} else {
			var next = parseInt(pos) + 1;
			var nextEl = document.querySelector('[data-characteristic="'+next+'"]');
			var nextRow = document.querySelector('[data-characrow="'+next+'"]');
			if(nextEl.tagName.toLowerCase() == 'select') {
				nextEl.selectedIndex = 0;
			} else {
				var inputs = nextRow.querySelectorAll('input');
				for (index = 0; index < inputs.length; ++index) {
					inputs[index].checked = false;
				}
			}
			if(obj.value == '') {
				nextRow.style.display = 'none';
			} else {
				nextRow.style.display = '';
			}
			next++;
			while(otherRow = document.querySelector('[data-characrow="'+next+'"]')) {
				next++;
				otherRow.style.display = 'none';
			}
			if(nextEl.tagName.toLowerCase() == 'select') {
				for (index = 0; index < nextEl.options.length; ++index) {
					var found = false;
					for (i = 0; i < validVariants.length; ++i) {
						var currentVariant = validVariants[i];
						if(parseInt(nextEl.options[index].value) == currentVariant[pos]) {
							found = true;
							nextEl.options[index].hidden = false;
							nextEl.options[index].disabled = false;
						}
					}
					if(!found && index != 0) {
						nextEl.options[index].hidden = true;
						nextEl.options[index].disabled = true;
					}
				}
				if(window.jQuery && typeof(jQuery().chosen) == "function") {
					jQuery( "#hikashop_product_characteristics select" ).chosen('destroy').chosen({disable_search_threshold:10, search_contains: true});
				}
			} else {
				var inputs = nextRow.querySelectorAll('input');
				for (index = 0; index < inputs.length; ++index) {
					var found = false;
					for (i = 0; i < validVariants.length; ++i) {
						var currentVariant = validVariants[i];
						if(parseInt(inputs[index].value) == currentVariant[pos]) {
							found = true;
							inputs[index].parentNode.style.display = '';
						}
					}
					if(!found) {
						inputs[index].parentNode.style.display = 'none';
					}
				}
			}

			if(qtyArea) {
				qtyArea.style.display = 'none';
			}
			if(altArea) {
				altArea.style.display = '';
			}
			return;
		}
	}
	if(qtyArea) {
		qtyArea.style.display = '';
	}
	if(altArea) {
		altArea.style.display = 'none';
	}
<?php } ?>
	hikashopUpdateVariant(obj);
}
function hikashopUpdateVariant(obj) {
	var options = <?php echo $list; ?>;
	var len = options.length, selection = '', found = false, el = null;
	if(typeof(obj) == "string")
		obj = document.getElementById(obj);
	if(!obj)
		return true;
	try { obj.blur(); } catch(e){}
	for (var i = 0; i < len; i++) {
		el = document.getElementById('hikashop_product_characteristic_'+options[i]);
		if(el) {
			selection += '_' + el.value;
			continue;
		}
		var form = document['hikashop_cart_product_form'];
		if(!form) {
			continue;
		}
		var checkFields = form.elements['hikashop_product_characteristic['+options[i]+']'];
		if(checkFields && !checkFields.length && checkFields.value) {
			selection += '_' + checkFields.value;
			continue;
		}
		var len2 = (checkFields ? checkFields.length : 0);
		for (var j = 0; j < len2; j++) {
			if(checkFields && checkFields[j] && checkFields[j].checked) {
				selection += '_' + checkFields[j].value;
				found = true;
			}
		}
		if(!found) {
			return true;
		}
	}

	hikashopUpdateVariantData(selection);
	if(window.Oby && window.Oby.fireAjax) window.Oby.fireAjax("hkAfterUpdateVariant", {obj:obj,selection:selection});
	return true;
}

function hikashopUpdateVariantData(selection) {
	if(!selection)
		return true;
	var position = window.hikashop.variantKeys.indexOf(selection);
	var button = document.getElementById('buttons_right');
	var input = document.getElementById('new_product_id');
	if(position == -1) {
		button.style.display = 'none';
		return true;
	}

	button.style.display = '';
	input.value = window.hikashop.variantIds[position];
	return true;
}
	</script>
</div>
<?php
}
?>
<!-- EO CHARACTERISTICS -->
<!-- OPTIONS -->
<?php

if(!empty($this->options)) {
	$this->show_option_quantity = false;
	$i = 0;
	$js_product_data = array();


	foreach($this->options as $optionElement) {
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
			if(!empty($optionElement->prices) && !empty($default_params['show_price'])) {
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
				$text .= ' ( '.($positive?'+ ':'').$this->currencyClass->format($price, $ok->price_currency_id).' )';

				$js_product_data[(int)$optionElement->product_id] = (float)str_replace(',','.',$price);
			}
			$option_values[] = JHTML::_('select.option', $optionElement->product_id, $text);
			if(!empty($this->optionsInCart[$optionElement->product_id]))
				$value = $optionElement->product_id;
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

				if(!empty($variant->prices) && !empty($default_params['show_price'])) {
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
					$text .= ' ( '.($positive?'+ ':'').$this->currencyClass->format($price, $ok->price_currency_id).' )';

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

				if(!empty($this->optionsInCart[$variant->product_id])) {
					$defaultValue = null;
					$value = $variant->product_id;
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

		<div class="hkform-group control-group hikashop_option_<?php echo $id;?>_line" id="hikashop_option_<?php echo $id; ?>">
			<div class="<?php echo $labelcolumnclass;?> hkcontrol-label">
				<?php echo $optionInfo->product_name . $options; ?>
			</div>
			<div class="<?php echo $inputcolumnclass;?>"><?php
				echo $html;
			?>
<?php
		if(!empty($this->show_option_quantity) && $select != 'radio') {
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
		}
?>
			</div>
		</div>
	<?php
		unset($optionInfo);
		$i++;
	}

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

}
?>
<!-- EO OPTIONS -->
<!-- CUSTOM ITEM FIELDS -->
<?php
if(!empty($this->itemFields)) {
	$formData = hikaInput::get()->get('data', array(), 'array');
	$after = array();
	foreach ($this->itemFields as $fieldName => $oneExtraField) {
		$itemData = @$this->product->$fieldName;
		if(isset($formData['item'][$fieldName]))
			$itemData = $formData['item'][$fieldName];
		$onWhat='onchange';
		if($oneExtraField->field_type=='radio')
			$onWhat='onclick';
		$oneExtraField->product_id = $this->product->product_id;
		$html = $this->fieldsClass->display(
			$oneExtraField,
			$itemData,
			'data[item]['.$oneExtraField->field_namekey.']',
			false,
			' '.$onWhat.'="window.hikashop.toggleField(this.value,\''.$fieldName.'\',\'item\',0);"',
			false,
			null,
			null,
			false
		);
		if($oneExtraField->field_type=='hidden') {
			$after[] = $html;
			continue;
		}
?>
		<div class="hkform-group control-group hikashop_item_<?php echo $oneExtraField->field_namekey;?>_line" id="hikashop_item_<?php echo $oneExtraField->field_namekey; ?>">
<?php
		$classname = $labelcolumnclass.' hkcontrol-label';
		echo $this->fieldsClass->getFieldName($oneExtraField, true, $classname);
?>
			<div class="<?php echo $inputcolumnclass;?>">
<?php
		echo $html;
?>
			</div>
		</div>
<?php
	}
	if(count($after)) {
		echo implode("\r\n", $after);
	}
}
?>
<!-- EO CUSTOM ITEM FIELDS -->
	</div>
	<div class="hikashop_checkout_buttons">
		<div class="buttons_left">
<!-- CANCEL BUTTON -->
				<a href="#" onclick="window.parent.hikashop.closeBox();return false;" class="<?php echo $this->config->get('css_button','hikabtn'); ?> hikabtn_cart_product_edit_cancel" id="hikabtn_cart_product_edit_cancel">
					<i class="fa fa-times"></i> <?php echo JText::_('HIKA_CANCEL'); ;?>
				</a>
<!-- EO CANCEL BUTTON -->
		</div>
		<div class="buttons_right" id="buttons_right">
<!-- SAVE BUTTON -->
			<button id="hikabtn_cart_product_edit_save" type="submit" class="<?php echo $this->config->get('css_button','hikabtn'); ?> hikabtn-success hikabtn_cart_product_edit_save">
				<i class="fa fa-save"></i> <?php echo JText::_('HIKA_OK'); ;?>
			</button>
<!-- EO SAVE BUTTON -->
		</div>
	</div>
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="ctrl" value="cart"/>
	<input type="hidden" name="task" value="product_save"/>
	<input type="hidden" name="cart_product_id" value="<?php echo (int)$this->product->cart_product_id; ?>"/>
	<input type="hidden" name="cart_id" value="<?php echo (int)$this->cart->cart_id; ?>"/>
	<input type="hidden" name="tmpl" value="<?php echo hikaInput::get()->getCmd('tmpl'); ?>"/>
	<?php echo JHTML::_('form.token'); ?>
</form>
