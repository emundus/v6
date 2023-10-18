<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?>				<?php
					$displayCursors='style="display:none"';
					$displayFormat='style="display:none"';
					$displayPrice='style="display:none"';
					$displayValues='style="display:none"';
					$fieldsetDisplay='style="display:none"';
					$fieldsetFields='style="display:none"';
					$weightUnitDisplay='style="display:none"';
					$checkboxDisplay='style="display:none"';
					$sortDisplay='style="display:none"';
					$sizeUnitDisplay='style="display:none"';
					$textSizeDisplay='style="display:none"';
					$dipslayApplyCursor='style="display:none"';
					$dipslayApply='style="display:none"';
					$dipslayApplyText='style="display:none"';
					$searchProcessing='style="display:none"';
					$logic = 'style="display:none"';
					if(@$this->element->filter_type=='text'){
						$dipslayApplyText='';
						$textSizeDisplay='';
						$searchProcessing='';
					}else if(@$this->element->filter_type=='cursor'){
						$dipslayApplyCursor='';
						if(empty($this->element->filter_options['input']) || $this->element->filter_options['input']  == '0')
							$displayFormat='';
					}else{
						$dipslayApply='';
					}
					if($this->element->filter_type!="text"){
						if(@$this->element->filter_data=='cursor'){
							$displayCursors='';
						}
						if(@$this->element->filter_data=='price'){
							$displayPrice='';
							$displayValues='';
						}
						if( @$this->element->filter_data == 'custom_field' ){
							$fieldsetFields='';
							if (@$this->element->filter_type == "checkbox" || @$this->element->filter_type == "multipledropdown") {
								$logic = '';
							}
						}
						if(@$this->element->filter_type=="checkbox" || @$this->element->filter_type=="radio"){
							$checkboxDisplay="";
						}
						if(@$this->element->filter_data=='sort'){
							$sortDisplay='';
						}
						if(@$this->element->filter_options['product_information']=='weight' || @$this->element->filter_options['sort_by']=='weight'){
							$weightUnitDisplay='';
						}else{
							$sizeUnitDisplay='';
						}
					}
					if(@$this->element->filter_data=="category"){
						$fieldsetDisplay="";
					}
				?>
				<table class="paramlist admintable table">
					<tr id="applyOntext" <?php echo $dipslayApplyText; ?>>
						<td class="key">
								<?php echo JText::_( 'APPLY_ON' ); ?>
						</td>
						<td >
							<?php echo $this->orderType->display('data[filter][filter_data_text][]',@$this->element->filter_data, 'product_filter', 'class="custom-select" size="8" multiple="multiple"', false); ?>
						</td>
					</tr>
					<tr id="applyOn" <?php echo $dipslayApply; ?>>
						<td class="key">
								<?php echo JText::_( 'APPLY_ON' ); ?>
						</td>
						<td>
							<?php echo $this->data_filterType->display('data[filter][filter_data]',@$this->element->filter_data); ?>
						</td>
					</tr>
					<tr id="applyOnCursor" <?php echo $dipslayApplyCursor; ?>>
						<td class="key">
								<?php echo JText::_( 'APPLY_ON' ); ?>
						</td>
						<td>
							<?php echo $this->product_informationType->display("data[filter][filter_data_cursor]", @$this->element->filter_data, $this->fields , 'class="custom-select" onchange="setVisibleUnit(this.value);"', 'datafilterfilter_data_cursor'); ?>
						</td>
					</tr>
					<tr id="filterSize">
						<td class="key">
								<?php echo JText::_( 'FIELD_SIZE' ); ?>
						</td>
						<td>
							<input size="10" type="text" name="data[filter][filter_size]" id="name" class="inputbox" size="40" value="<?php echo $this->escape(@$this->element->filter_options['filter_size']); ?>" />
						</td>
					</tr>
					<tr id="titlePosition" class="required">
						<td class="key">
								<?php echo JText::_( 'TITLE_POSITION' ); ?>
						</td>
						<td>
							<?php echo $this->positionType->display('data[filter][title_position]',@$this->element->filter_options['title_position'], true, true);?>
						</td>
					</tr>
					<tr id="titlePositionCursor" class="required">
						<td class="key">
								<?php echo JText::_( 'TITLE_POSITION' ); ?>
						</td>
						<td>
							<?php echo $this->div_positionType->display('data[filter][title_position_cursor]',@$this->element->filter_options['title_position']);?>
						</td>
					</tr>
					<tr>
						<td class="key">
								<?php echo JText::_( 'NUMBER_OF_COLUMNS' ); ?>
						</td>
							<td>
							<select name="data[filter][column_width]" class="custom-select">
								<?php
								$config =& hikashop_config();
								$maxColumn=$config->get('filter_column_number');
								if(!isset($maxColumn) || empty($maxColumn) || !is_numeric($maxColumn)){
									$maxColumn=2;
								}
								for($i=1;$i<=$maxColumn;$i++){  ?>
									<option <?php if(@$this->element->filter_options['column_width'] == $i) echo "selected=\"selected\""; ?> value='<?php echo $i; ?>' ><?php echo $i; ?></option>
								<?php } ?>
							</select>
						</td>
					</tr>
					<tr id="button_align" <?php echo $checkboxDisplay; ?>>
						<td class="key">
								<?php echo JText::_( 'INLINE_BUTTON' ); ?>
						</td>
						<td>
							<?php echo JHTML::_('hikaselect.booleanlist', "data[filter][button_align]" , '',@$this->element->filter_options['button_align']); ?>
						</td>
					</tr>
					<tr id="textBoxSize" <?php echo $textSizeDisplay; ?>>
						<td class="key">
								<?php echo JText::_( 'MAXIMIZE_TEXT_SIZE' ); ?>
						</td>
						<td>
							<?php echo JHTML::_('hikaselect.booleanlist', "data[filter][textBoxSize]" , '',@$this->element->filter_options['textBoxSize']); ?>
						</td>
					</tr>
					<tr id="searchProcessing" <?php echo $searchProcessing; ?>>
						<td class="key">
							<?php echo JText::_( 'SEARCH_PROCESSING' ); ?>
						</td>
						<td>
							<?php
								$arr = array(
									JHTML::_('select.option', 'every', JText::_('Every words') ),
									JHTML::_('select.option', 'complete', JText::_('Complete expression') ),
									JHTML::_('select.option', 'exact', JText::_('Exact') ),
									JHTML::_('select.option', 'any', JText::_('Any word') ),
									JHTML::_('select.option', 'operators', JText::_('Search operators') ),
								);
								echo JHTML::_('hikaselect.genericlist', $arr, "data[filter][searchProcessing]", 'class="custom-select" size="1"', 'value', 'text', @$this->element->filter_options['searchProcessing']);
							 ?>
						</td>
					</tr>
					<tr id="logic" <?php echo $logic; ?>>
						<td class="key">
							<?php echo JText::_( 'LOGIC' ); ?>
						</td>
						<td><?php
							$arr = array(
								JHTML::_('select.option', 'OR', JText::_('OR') ),
								JHTML::_('select.option', 'AND', JText::_('AND') ),
							);
							echo JHTML::_('hikaselect.genericlist', $arr, "data[filter][logic]", 'class="custom-select" size="1"', 'value', 'text', @$this->element->filter_options['logic']);
						?></td>
					</tr>
					<tr id="max_char">
						<td class="key">
							<?php echo JText::_( 'MAXLENGTH' ); ?>
						</td>
						<td>
							<input type="text" name="data[filter][max_char]" id="name" class="inputbox" size="10" value="<?php echo $this->escape(@$this->element->filter_options['max_char']); ?>" />
						</td>
					</tr>
					<tr id="attributes">
						<td class="key">
							<?php echo JText::_( 'FIELD_ATTRIBUTE' ); ?>
						</td>
						<td>
							<input type="text" name="data[filter][attribute]" id="name" class="inputbox" size="10" value="<?php echo $this->escape(@$this->element->filter_options['attribute']); ?>" />
						</td>
					</tr>
					<tr id="currencies" <?php echo $displayPrice; ?>>
						<td class="key">
								<?php echo JText::_( 'CURRENCIES' ); ?>
						</td>
						<td>
							<?php 	$currency=hikashop_get('type.currency');
								 	$currencyList=$currency->display("data[filter][filter_currencies][]", @$this->element->filter_options['currencies'], 'multiple="multiple" size="4"');
									echo $currencyList;
							?>
						</td>
					<tr/>
					<tr id="characteristic">
						<td class="key">
								<?php echo JText::_( 'CHARACTERISTICS' ); ?>
						</td>
						<td>
							<?php
								echo $this->characteristiclistType->display("data[filter][filter_charac]", @$this->element->filter_options['filter_charac']);
							?>
						</td>
					<tr/>
					<tr id="sort_by" <?php echo $sortDisplay; ?>>
						<td class="key">
								<?php echo JText::_( 'SORT' ); ?>
						</td>
						<td>
							<?php
								echo $this->product_informationType->display("data[filter][sort_by][]", @$this->element->filter_options['sort_by'], $this->fields, '', null, true);
							?>
						</td>
					<tr/>
					<tr id="product_information">
						<td class="key">
								<?php echo JText::_( 'INFORMATION' ); ?>
						</td>
						<td>
							<?php
								echo $this->product_informationType->display("data[filter][product_information]", @$this->element->filter_options['product_information'],'', 'class="custom-select" onchange="setVisibleUnit(this.value);"', 'product_information_value');
							?>
						</td>
					<tr/>
					<tr id="dimension_unit" <?php echo $sizeUnitDisplay; ?>>
						<td class="key">
								<?php echo JText::_( 'UNITS' ); ?>
						</td>
						<td>
							<?php
								echo $this->volume->display('data[filter][dimension_unit]',@$this->element->filter_options['information_unit']);
							?>
						</td>
					<tr/>
					<tr id="weight_unit" <?php echo $weightUnitDisplay; ?>>
						<td class="key">
								<?php echo JText::_( 'UNITS' ); ?>
						</td>
						<td>
							<?php
								echo $this->weight->display('data[filter][weight_unit]',@$this->element->filter_options['information_unit'], '', '', false);
							?>
						</td>
					<tr/>
					<tr id="custom_field" <?php echo $fieldsetFields; ?> >
						<td class="key">
								<?php echo JText::_( 'FIELDS' ); ?>
						</td>
						<td>
							<select name="data[filter][custom_field]" class="custom-select">
							<?php
								if(!empty($this->fields)){
									foreach($this->fields as $key => $field){
									?>
									<option <?php if(@$this->element->filter_options['custom_field'] == $field->field_namekey) echo "selected=\"selected\""; ?> value='<?php echo $field->field_namekey; ?>' ><?php echo $key; ?></option>
								<?php }
								} ?>
							</select>
						</td>
					<tr/>
					<tr id="filterValues" class="multivalues" <?php echo $displayValues; ?>>
						<td class="key" valign="top">
								<?php echo JText::_( 'FIELD_VALUES' ); ?>
						</td>
						<td>
							<table class="table table-striped table-hover">
							<tbody  id="tablevalues">
								<?php
								if(!empty($this->element->filter_value) AND is_array($this->element->filter_value)){
								foreach($this->element->filter_value as $key => $value){
								?>
								<tr>
									<td><input type="text" name="filter_values[value][]" value="<?php echo $value; ?>" /></td>
								</tr>
								<?php } ?>
								<tr>
									<td><input type="text" name="filter_values[value][]" value="" /></td>
								</tr>
								 <?php }else{ ?>
								<tr>
									<td><input type="text" name="filter_values[value][]" value="" /></td>
								</tr>
								<tr>
									<td><input type="text" name="filter_values[value][]" value="" /></td>
								</tr>
								<tr>
									<td><input type="text" name="filter_values[value][]" value="" /></td>
								</tr>
								<?php } ?>
							</tbody>
							</table>
							<a onclick="addLine();return false;" href='#' title="<?php echo $this->escape(JText::_('FIELD_ADDVALUE')); ?>"><?php echo JText::_('FIELD_ADDVALUE'); ?></a>
							<br/><br/><?php echo JText::_( 'DEFINED_LIMITS' ).' '. JHTML::_('hikaselect.booleanlist', "data[filter][defined_limits]" , '',@$this->element->filter_options['defined_limits']); ?>
						</td>
					</tr>
					<tr id="cursorNumber" <?php echo $displayCursors; ?>>
						<td class="key">
								<?php echo JText::_( 'INPUT_FIELDS' ); ?>
						</td>
						<td>
							<?php echo JHTML::_('hikaselect.booleanlist', "data[filter][input]" , '',@$this->element->filter_options['input']); ?>
						</td>
					</tr>
					<tr id="cursorFormat" <?php echo $displayFormat; ?>>
						<td class="key">
								<?php echo JText::_( 'CURSOR_FORMAT' ); ?>
						</td>
						<td>
						<textarea rows="3" cols="50" name="data[filter][label_format]" id="format" class="inputbox" ><?php echo $this->escape(@$this->element->filter_options['label_format']); ?></textarea>
						</td>
					</tr>
					<tr id="cursorMax" <?php echo $displayCursors; ?>>
						<td class="key">
								<?php echo JText::_( 'CURSOR_MAX' ); ?>
						</td>
						<td>
							<input size="10" type="text" name="data[filter][cursor_max]" id="max" class="inputbox" size="40" value="<?php echo $this->escape(@$this->element->filter_options['cursor_max']); ?>" />
						</td>
					</tr>
					<tr id="cursorMin" <?php echo $displayCursors; ?>>
						<td class="key">
								<?php echo JText::_( 'CURSOR_MIN' ); ?>
						</td>
						<td>
							<input size="10" type="text" name="data[filter][cursor_min]" id="min" class="inputbox" size="40" value="<?php echo $this->escape(@$this->element->filter_options['cursor_min']); ?>" />
						</td>
					</tr>
					<tr id="cursorStep" <?php echo $displayCursors; ?>>
						<td class="key">
								<?php echo JText::_( 'CURSOR_STEP' ); ?>
						</td>
						<td>
							<input size="10" type="text" name="data[filter][cursor_step]" id="step" class="inputbox" size="40" value="<?php echo $this->escape(@$this->element->filter_options['cursor_step']); ?>" />
						</td>
					</tr>
					<tr id="cursorEffet" <?php echo $displayCursors; ?>>
						<td class="key">
								<?php echo JText::_( 'CURSOR_EFFECT' ); ?>
						</td>
						<td>
							<?php echo JHTML::_('hikaselect.booleanlist', "data[filter][cursor_effect]" , '',@$this->element->filter_options['cursor_effect']); ?>
						</td>
					</tr>
					<tr id="cursorWidth" <?php echo $displayCursors; ?>>
						<td class="key">
								<?php echo JText::_( 'CURSOR_WIDTH' ); ?>
						</td>
						<td>
							<input size="8" type="text" name="data[filter][cursor_width]" id="width" class="inputbox" size="40" value="<?php echo $this->escape(@$this->element->filter_options['cursor_width']); ?>" /> px
						</td>
					</tr>
					<tr id="filter_categories" <?php echo $fieldsetDisplay; ?>>
						<td class="key">
								<?php echo JText::_( 'CATEGORY' ); ?>
						</td>
						<td>
							<span id="changeParentCategory" >
								<?php echo (int)@$this->element->filter_options['parent_category_id'].' '.@$this->element->filter_options['parent_category_name']; ?>

							</span>
								<input type="hidden" id="filterselectparentlisting" name="data[filter][parent_category_id]" value="<?php echo @$this->element->filter_options['parent_category_id']; ?>" />
							<?php
							echo $this->popup->display(
									'<img src="'. HIKASHOP_IMAGES.'edit.png" alt="'.JText::_('CATEGORY').'"/>',
									'CATEGORY',
									hikashop_completeLink("category&task=selectparentlisting&control=filter&id=changeParentCategory",true ),
									'change_category_link',
									860, 480, '', '', 'link'
								);
							?>
							<a href="#" onclick="document.getElementById('changeParentCategory').innerHTML='0 <?php echo $this->escape(JText::_('CATEGORY_NOT_FOUND'));?>'; document.getElementById('filterselectparentlisting').value='0';return false;" >
								<img src="<?php echo HIKASHOP_IMAGES; ?>delete.png" alt="delete"/>
							</a>
						</td>
					</tr>
				</table>
				<br/>




