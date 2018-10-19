<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.0.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2018 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
if(empty($this->element->characteristics))
	return;

?><div id="hikashop_product_characteristics" class="hikashop_product_characteristics"><?php

if($this->params->get('characteristic_display') != 'list') {
	echo $this->characteristic->displayFE($this->element, $this->params) . '</div>';
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
				<th class="hikashop_product_code_title hikashop_variants_table_th"><?php
					echo JText::_( 'PRODUCT_CODE' );
				?></th>
<?php }
	foreach($this->element->main->characteristics as $characteristic) { $columns++; ?>
				<th class="hikashop_product_characteristic hikashop_product_characteristic_<?php echo $characteristic->characteristic_id; ?> title hikashop_variants_table_th"><?php
					echo $characteristic->characteristic_value;
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
						echo $this->image->display($image->file_path, true, $image->file_name, 'style="margin-top:10px;margin-bottom:10px;display:inline-block;vertical-align:middle"', '', $width, $height);
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
							echo $variantCharacteristic->characteristic_value;
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
								$max_quantity = $min_quantity * 15;
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
