<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
if(empty($this->element->characteristics) || empty($this->element->main->characteristics))
	return;

?>
<div id="hikashop_product_characteristics" class="hikashop_product_characteristics">
<?php
if($this->params->get('characteristic_display') != 'list') {

	$app = JFactory::getApplication();
	$config = hikashop_config();
	$characteristics_dynamic_display = $config->get('characteristics_dynamic_display', 1);

	$this->characteristics=&$this->element->main->characteristics;
	foreach($this->characteristics as $k => $characteristic) {
		if(empty($this->characteristics[$k]->characteristic_display_method)) {
			$this->characteristics[$k]->characteristic_display_method = $this->params->get('characteristic_display');
		}
	}

	$chromePath = JPATH_THEMES . DS . $app->getTemplate() . DS . 'html' . DS . 'hikashop_characteristics.php';
	if(file_exists($chromePath)) {
		require_once ($chromePath);
		if(function_exists('hikashop_characteristics_html')) {
			echo hikashop_characteristics_html($this->element, $params, $this);
			echo '</div>';
			return;
		}
	}

	$html = '';
	$js = '';
	$ids = array_keys($this->characteristics);
	$list = '[\''.implode('\',\'',$ids).'\']';

	switch($this->params->get('characteristic_display')){
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
				$this->options=' onclick="return hikashopUpdateVariantData(this.value);"';
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
								foreach($this->element->variants as $k => $variant) {
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
								$radio = "\n\t<span class=\"hikashop_product_characteristic_span".$classspan."\"><input type=\"radio\" class=\"hikashop_product_characteristic".$class."\" name=\"hikashop_product_characteristic\"".$extra." id=\"hikashop_product_characteristic".$name."\" value=\"".$name."\" ".$this->options;
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
				if($this->params->get('characteristic_display_text')) {
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
			$main_html = '<table class="hikashop_product_characteristics_table">';
			$config = hikashop_config();
			$count = count($this->characteristics);
			$i = 0;
			foreach($this->characteristics as $characteristic) {
				$i++;
				$main_html.='<tr class="hikashop_characteristic_line_'.$characteristic->characteristic_id.'" data-characrow="'.$i.'">';
				$values = array();
				$switch_done = false;
				if(!empty($characteristic->values)) {
					foreach($characteristic->values as $k => $value){
						if(!$config->get('show_out_of_stock',1)){
							$hasQuantity = false;
							foreach($this->element->variants as $variant){
								foreach($variant->characteristics as $variantCharacteristic){
									if($variantCharacteristic->characteristic_id==$value->characteristic_id){
										if($variant->product_quantity != 0){
											$hasQuantity = true;
										}elseif( $this->element->product_id==$variant->product_id && !$switch_done){
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
				if(($characteristics_dynamic_display && $count > 1) || $characteristics_dynamic_display > 1) {
					if($characteristic->characteristic_display_method!='radio')
						$this->values[] = JHTML::_('select.option', '', JText::_('PLEASE_SELECT') );
					$selected = '';
				}
				foreach($values as $key => $val){
					if(strlen($val)!=0 && empty($val)){
						$val = $val.'&nbsp;';
					}

					if(strpos($val, '<img ') !== false)
						$val = str_replace('<img ', '<img onclick="return hikashopVariantSelected(\'hikashop_product_characteristic_'.$characteristic_id.$key.'\');" ', $val);
					$clean = hikashop_translate(strip_tags($val));
					$optionValue = ($characteristic->characteristic_display_method != 'radio' && !empty($clean) ? $clean : hikashop_translate($val));

					$obj = new stdClass;
					$obj->value  = $key;
					$obj->text = $optionValue;
					$obj->id = 'hikashop_product_characteristic_'.$characteristic_id.'_'.$key;
					$this->values[] = $obj;
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

				$html = JHTML::_('select.'.$characteristic->characteristic_display_method.'list', $this->values, @$characteristic->characteristic_id, 'class="'.HK_FORM_SELECT_CLASS.'" size="1"' . $options, 'value', 'text', $selected, $id );
				if($this->params->get('characteristic_display_text') && isset($characteristic->characteristic_value)) {

					$html = hikashop_translate($characteristic->characteristic_value).'</td><td>'.$html;
				}
				$main_html .= '<td>'.$html.'</td></tr>';
			}
			$main_html .= '</table>';
			$html = $main_html;

			if($characteristics_dynamic_display) {
				$matches = $this->getAllValuesMatches($this->characteristics, $this->element->variants, $this->element->main);
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
?>
	<script>
<?php echo $js; ?>

<?php if(($characteristics_dynamic_display && $count > 1) || $characteristics_dynamic_display > 1) { ?>
window.hikashop.ready( function() { initVariants(); });
<?php } ?>
function initVariants() {
	var allRows = document.querySelectorAll('tr[data-characrow]'), first = true,
	qtyArea = document.getElementById('hikashop_product_quantity_main'), altArea = document.getElementById('hikashop_product_quantity_alt');
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

<?php if(@$this->displayVariants['prices']) { ?>
	var priceDivs = document.querySelectorAll('#hikashop_product_price_main > .hikashop_product_price_full');
	priceDivs.forEach(function (sub) { sub.style.display = 'none'; });
<?php } ?>

<?php if( $characteristics_dynamic_display <= 1) { ?>
	var firstEl = document.querySelector('[data-characteristic="1"]');
	var firstRow = document.querySelector('[data-characrow="1"]');
	var autoSelect = false;
	if(firstEl.tagName.toLowerCase() == 'select') {
		if(firstEl.options.length == 2) {
			autoSelect = true;
			firstEl.selectedIndex = firstEl.options.length - 1;
			if(window.jQuery && typeof(jQuery().chosen) == "function") {
				jQuery( "#hikashop_product_characteristics select" ).chosen('destroy').chosen({disable_search_threshold:10, search_contains: true});
			}
		}
	} else {
		var inputs = firstRow.querySelectorAll('input');
		if(inputs.length == 1) {
			autoSelect = true;
			inputs[inputs.length-1].checked = true;
		}
	}

	if(autoSelect) {
		if(firstEl.tagName.toLowerCase() == 'select') {
			hikashopVariantSelected(firstEl);
		} else {
			hikashopVariantSelected(inputs[inputs.length-1]);
		}
	}
<?php } ?>
}

function getValidVariants(pos) {
	var allInputs = document.querySelectorAll('[data-characteristic]'), selectedElements = [], validVariants = [];

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

	if(!selectedElements.length)
		return window.hikashop.availableValues;

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
	return validVariants;
}

function hikashopVariantSelected(obj) {
<?php if(($characteristics_dynamic_display && $count > 1) || $characteristics_dynamic_display > 1) { ?>
	if(typeof(obj) == "string")
		obj = document.getElementById(obj);
	if(!obj)
		return true;
	var pos = obj.getAttribute('data-characteristic'), last = obj.getAttribute('data-last'), otherRow = null,
	qtyArea = document.getElementById('hikashop_product_quantity_main'), altArea = document.getElementById('hikashop_product_quantity_alt');
	if(!last) {
		validVariants = getValidVariants(pos);

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
			var autoSelect = false;
			if(nextEl.tagName.toLowerCase() == 'select') {
				var count = 0;
				var lastIndexFound = 0;
				for (index = 0; index < nextEl.options.length; ++index) {
					var found = false;
					for (i = 0; i < validVariants.length; ++i) {
						var currentVariant = validVariants[i];
						if(parseInt(nextEl.options[index].value) == currentVariant[pos]) {
							found = true;
							lastIndexFound = index;
							nextEl.options[index].hidden = false;
							nextEl.options[index].disabled = false;
						}
					}
					if(!found && index != 0) {
						nextEl.options[index].hidden = true;
						nextEl.options[index].disabled = true;
					} else {
						count++;
					}
				}

				if(count==2) {
					autoSelect = true;
					nextEl.selectedIndex = lastIndexFound;
				}
				if(window.jQuery && typeof(jQuery().chosen) == "function") {
					jQuery( "#hikashop_product_characteristics select" ).chosen('destroy').chosen({disable_search_threshold:10, search_contains: true});
				}
			} else {
				var inputs = nextRow.querySelectorAll('input');
				var count = 0;
				var lastIndexFound = 0;
				for (index = 0; index < inputs.length; ++index) {
					var found = false;
					for (i = 0; i < validVariants.length; ++i) {
						var currentVariant = validVariants[i];
						if(parseInt(inputs[index].value) == currentVariant[pos]) {
							found = true;
							lastIndexFound = index;
							inputs[index].parentNode.style.setProperty('display', '', 'important');
						}
					}

					if(!found) {
						inputs[index].parentNode.style.setProperty('display', 'none', 'important');
					} else {
						count++;
					}
				}
				if(count==2) {
					autoSelect = true;
					inputs[lastIndexFound].checked = true;
				}
			}

			if(qtyArea) {
				qtyArea.style.display = 'none';
			}
			if(altArea) {
				altArea.style.display = '';
			}
			if(autoSelect) {
				if(nextEl.tagName.toLowerCase() == 'select') {
					hikashopVariantSelected(nextEl);
				} else {
					hikashopVariantSelected(inputs[lastIndexFound]);
				}
			}
			return;
		}
	}
	if(obj.value != '') {
		if(qtyArea) {
			qtyArea.style.display = '';
		}
		if(altArea) {
			altArea.style.display = 'none';
		}
	} else {
		if(qtyArea) {
			qtyArea.style.display = 'none';
		}
		if(altArea) {
			altArea.style.display = '';
		}
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
		var form = document['hikashop_product_form'];
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
	var names = ['id','name','code','image','price','quantity','description','weight','url','width','length','height','contact','custom_info','files'];
	var len = names.length;
	for(var i = 0; i < len; i++){
		var el = document.getElementById('hikashop_product_'+names[i]+'_main');
		var el2 = document.getElementById('hikashop_product_'+names[i]+selection);
		if(el && el2)
			el.innerHTML = el2.innerHTML.replace(/_VARIANT_NAME/g, selection).replace(/data-content/g, 'content').replace(/data-itemprop/g, 'itemprop');
	}
	if(window.hikaProductOptions) hikaProductOptions.refreshPrice();
	else if(typeof this.window['hikashopRefreshOptionPrice'] == 'function') hikashopRefreshOptionPrice();
	if(window.Oby && window.Oby.fireAjax) window.Oby.fireAjax("hkContentChanged", {selection:selection});
	return true;
}
	</script>
</div>
<?php
	return;
}

if(!empty($this->element->main->characteristics)) {
	$columns=0;
	if((int)$this->config->get('show_quantity_field') >= 2) {
?>
	<form action="<?php echo hikashop_completeLink('product&task=updatecart'); ?>" method="post" name="hikashop_product_form_variants" enctype="multipart/form-data">
<?php
	}
?>
	<table class="hikashop_variants_table hikashop_products_table adminlist table table-striped table-hover" cellpadding="1">
		<thead class="hikashop_variants_table_thead">
			<tr class="hikashop_variants_table_thead_tr">
<?php if($this->config->get('thumbnail') && @$this->displayVariants['images']) { $columns++; ?>
				<th class="hikashop_product_image title hikashop_variants_table_th"><?php
					echo JText::_( 'HIKA_IMAGE' );
				?></th>
<?php }
	if(@$this->displayVariants['variant_name']) { $columns++; ?>
				<th class="hikashop_product_name title hikashop_variants_table_th"><?php
					echo JText::_( 'PRODUCT' );
				?></th>
<?php }
	if($this->config->get('show_code')) { $columns++; ?>
				<th class="hikashop_product_code title hikashop_variants_table_th"><?php
					echo JText::_( 'PRODUCT_CODE' );
				?></th>
<?php }
	foreach($this->element->main->characteristics as $characteristic) { $columns++; ?>
				<th class="hikashop_product_characteristic hikashop_product_characteristic_<?php echo $characteristic->characteristic_id; ?> title hikashop_variants_table_th"><?php
					echo hikashop_translate($characteristic->characteristic_value);
				?></th>
<?php }

	if(@$this->displayVariants['product_description']) { $columns++; ?>
				<th class="hikashop_product_description title hikashop_variants_table_th"><?php
					echo JText::_( 'HIKA_DESCRIPTION' );
				?></th>
<?php }
	if($this->params->get('show_price','-1') == '-1') {
		$this->params->set('show_price', $this->config->get('show_price'));
	}
	if($this->params->get('show_price') && @$this->displayVariants['prices']) { $columns++; ?>
				<th class="hikashop_product_price title hikashop_variants_table_th"><?php
					echo JText::_('PRICE');
				?></th>
<?php }
	if(!$this->params->get('catalogue')){ $columns++; ?>
				<th class="hikashop_product_add_to_cart title hikashop_variants_table_th">
				</th>
<?php } ?>
			</tr>
		</thead>
		<tbody class="hikashop_variants_table_tbody">
<?php
	$productClass = hikashop_get('class.product');
	$productClass->generateVariantData($this->element);

	foreach($this->element->variants as $variant) {
		if(isset($variant->map))
			continue;
		if(!$this->config->get('show_out_of_stock', 1) && $variant->product_quantity == 0)
			continue;
		if(!$variant->product_published)
			continue;

		$this->row =& $variant;
?>
			<tr id="hikashop_variant_row_<?php echo $variant->product_id; ?>" class="hikashop_variant_row hikashop_variants_table_tbody_tr">
<?php 	if($this->config->get('thumbnail') && @$this->displayVariants['images']){ ?>
				<td class="hikashop_product_image_row hikashop_variants_table_td" data-label="<?php echo JText::_( 'HIKA_IMAGE' ); ?>">
<?php
			if (!empty ($variant->images)) {
				$image = reset($variant->images);
				$width = $this->config->get('thumbnail_x');
				$height = $this->config->get('thumbnail_y');
				$this->image->checkSize($width,$height,$image);
				foreach($variant->images as $image) {
?>
					<div class="hikashop_variants_table_image_thumb"><?php
					if($this->image->override) {
						echo $this->image->display(@$image->file_path, true, @$image->file_name, 'style="margin-top:10px;margin-bottom:10px;display:inline-block;vertical-align:middle"','', $width, $height);
					} else {
						if(empty($this->popup))
							$this->popup = hikashop_get('helper.popup');
						$image_options = array('default' => true,'forcesize'=>$this->config->get('image_force_size',true),'scale'=>$this->config->get('image_scale_mode','inside'));
						$img = $this->image->getThumbnail(@$image->file_path, array('width' => $width, 'height' => $height), $image_options);
						if(@$img->success) {
							$attributes = 'style="margin-top:10px;margin-bottom:10px;display:inline-block;vertical-align:middle"';
							if($img->external && $img->req_width && $img->req_height)
								$attributes .= ' width="'.$img->req_width.'" height="'.$img->req_height.'"';
							$html = '<img '.$attributes.' title="'.$this->escape((string)@$image->file_description).'" alt="'.$this->escape((string)@$image->file_name).'" src="'.$img->url.'"/>';
							if($this->config->get('add_webp_images', 1) && function_exists('imagewebp') && !empty($img->webpurl)) {
								$html = '
								<picture>
									<source srcset="'.$img->webpurl.'" type="image/webp">
									<source srcset="'.$img->url.'" type="image/'.$img->ext.'">
									'.$html.'
								</picture>
								';
							}

							echo $this->popup->image($html, $img->origin_url, null, 'title="'.$this->escape((string)@$image->file_description).'"');
						}
					}
					?></div>
<?php			}
			}
?>
				</td>
<?php	}
		if(@$this->displayVariants['variant_name']){ ?>
				<td class="hikashop_product_name_row hikashop_variants_table_td" data-label="<?php echo JText::_( 'PRODUCT' ); ?>">
					<?php echo $variant->variant_name; ?>
				</td>
<?php	}
		if ($this->config->get('show_code')) {
?>
				<td class="hikashop_product_code_row hikashop_variants_table_td" data-label="<?php echo JText::_( 'PRODUCT_CODE' ); ?>"><?php
					echo $variant->product_code;
				?></td>
<?php	}

		foreach($this->element->main->characteristics as $characteristic) {
?>
				<td class="hikashop_product_characteristic_row hikashop_product_characteristic_<?php echo $characteristic->characteristic_id; ?>_row hikashop_variants_table_td" data-label="<?php echo $characteristic->characteristic_value; ?>">
<?php
			if(!empty($characteristic->values)) {
				foreach($characteristic->values as $k => $value) {
					foreach($variant->characteristics as $variantCharacteristic) {
						if($variantCharacteristic->characteristic_id == $value->characteristic_id) {
							echo hikashop_translate($variantCharacteristic->characteristic_value);
							break 2;
						}
					}
				}
			}
?>
				</td>
<?php 	}

		if(@$this->displayVariants['product_description']) {
?>
				<td class="hikashop_product_description_row hikashop_variants_table_td" data-label="<?php echo JText::_( 'HIKA_DESCRIPTION' ); ?>"><?php
					echo JHTML::_('content.prepare', preg_replace('#<hr *id="system-readmore" */>#i', '', $variant->product_description));
				?></td>
<?php 	}

		if($this->params->get('show_price') && @$this->displayVariants['prices']){
?>
				<td class="hikashop_product_price_row hikashop_variants_table_td" data-label="<?php echo JText::_( 'PRICE' ); ?>">
<?php
			if ( ($this->row->product_msrp) == ($this->element->main->product_msrp) )
				$this->params->set('from_module',1);

			$this->setLayout('listing_price');
			echo $this->loadTemplate();
			$this->params->set('from_module',0);
?>
				</td>
<?php	}
		if(!$this->params->get('catalogue')) {
?>
				<td class="hikashop_product_add_to_cart_row hikashop_variants_table_td">
<?php
			if ($this->config->get('show_quantity_field') < 2) {
				$this->params->set('main_div_name','variants');
				$this->params->set('extra_div_name','hikashop_product_form');
				$this->params->set('product_waitlist', $this->config->get('product_waitlist', 0));
	 			$this->setLayout('add_to_cart_ajax');
				echo $this->loadTemplate();
				$this->params->set('extra_div_name','');
			} else {
				$start_date = (@$this->row->product_sale_start || empty($this->element->main)) ? @$this->row->product_sale_start : $this->element->main->product_sale_start;
				$end_date = (@$this->row->product_sale_end || empty($this->element->main)) ? @$this->row->product_sale_end : $this->element->main->product_sale_end;
				$now = time();
				if($end_date > 0 && $end_date < $now) {
				?>
					<span class="hikashop_product_sale_end"><?php
					echo JText::_('ITEM_NOT_SOLD_ANYMORE');
					?></span>
				<?php
				}

				else if($start_date > 0 && $start_date > $now) {
				?>
					<span class="hikashop_product_sale_start"><?php
					echo JText::sprintf('ITEM_SOLD_ON_DATE', hikashop_getDate($start_date, $this->params->get('date_format', '%d %B %Y')));
					?></span>
				<?php
				}
				else {
?>
					<span class="hikashop_product_stock_count">
<?php
					if($this->row->product_quantity > 0)
						echo (($this->row->product_quantity == 1 && JText::_('X_ITEM_IN_STOCK') != 'X_ITEM_IN_STOCK') ? JText::sprintf('X_ITEM_IN_STOCK', $this->row->product_quantity) : JText::sprintf('X_ITEMS_IN_STOCK', $this->row->product_quantity));
					elseif($this->row->product_quantity == 0)
						echo JText::_('NO_STOCK');
?>
					</span>
<?php
					if($this->row->product_quantity == -1 || $this->row->product_quantity > 0) {
						$quantityLayout = $this->quantityLayout;
						if(!empty($this->row->product_quantity_layout) && $this->row->product_quantity_layout != 'inherit')
							$quantityLayout = $this->row->product_quantity_layout;
						if(empty($quantityLayout) || !in_array($quantityLayout, array( 'show_select', 'show_select_price'))) {
?>
					<input id="hikashop_listing_quantity_<?php echo $this->row->product_id;?>" type="text" style="width:40px;" name="data[<?php echo $this->row->product_id;?>]" class="hikashop_listing_quantity_field" value="0" />
<?php 				} else {
							$min_quantity = ($this->row->product_min_per_order || empty($this->element->main)) ? $this->row->product_min_per_order : $this->element->main->product_min_per_order;
							$max_quantity = ($this->row->product_max_per_order || empty($this->element->main)) ? $this->row->product_max_per_order : $this->element->main->product_max_per_order;
							$min_quantity = max((int)$min_quantity, 1);
							$max_quantity = max((int)$max_quantity, 0);
							if($max_quantity == 0)
								$max_quantity = $min_quantity * $this->config->get('quantity_select_max_default_value', 15);
							$values = array();
							if($quantityLayout == 'show_select' || empty($this->row->prices)) {
								$values = range($min_quantity, $max_quantity, $min_quantity);
							} else {
								foreach($this->row->prices as $price) {
									$price_min_qty = max((int)$price->price_min_quantity, $min_quantity);
									$values[$price_min_qty] = $price_min_qty;
								}
							}
?>
					<select id="hikashop_listing_quantity_select_<?php echo $this->row->product_id;?>" class="tochosen" onchange="var qty_field = document.getElementById('hikashop_listing_quantity_<?php echo $this->row->product_id;?>'); qty_field.value = this.value;">
<?php
							echo '<option value="0" selected="selected">0</option>';
							foreach($values as $j) {
								echo '<option value="'.$j.'">'.$j.'</option>';
							}
?>
					</select>
					<input id="hikashop_listing_quantity_<?php echo $this->row->product_id;?>" type="hidden" name="data[<?php echo $this->row->product_id;?>]" value="0" />
<?php
						}
					}
				}
			}
?>
				</td>
<?php
		}
?>
			</tr>
<?php
	}
?>
		</tbody>
	</table>
<?php
	if($this->config->get('show_quantity_field') >= 2) {
		$this->ajax = 'if(hikashopCheckChangeForm(\'item\',\'hikashop_product_form_variants\')){ return hikashopModifyQuantity(\'\',field,1,\'hikashop_product_form_variants\'); } return false;';
		$this->row = new stdClass();
		$this->row->prices = array($this->row);
		$this->row->product_quantity = -1;
		$this->row->product_min_per_order = 0;
		$this->row->product_max_per_order = -1;
		$this->row->product_sale_start = 0;
		$this->row->product_sale_end = 0;
		$this->row->formName = 'hikashop_product_form_variants';
		$this->row->prices = array('filler');
		$this->params->set('show_quantity_field', 2);
		$this->setLayout('quantity');
		echo $this->loadTemplate();

		if(!empty($this->ajax) && $this->config->get('redirect_url_after_add_cart','stay_if_cart') == 'ask_user') {
?>
		<input type="hidden" name="popup" value="1"/>
<?php
 		}
?>
		<input type="hidden" name="hikashop_cart_type_0" id="hikashop_cart_type_0" value="cart"/>
		<input type="hidden" name="add" value="1"/>
		<input type="hidden" name="ctrl" value="product"/>
		<input type="hidden" name="task" value="updatecart"/>
		<input type="hidden" name="return_url" value="<?php echo urlencode(base64_encode(urldecode($this->redirect_url)));?>"/>
	</form>
<?php
	}
}
?></div>
