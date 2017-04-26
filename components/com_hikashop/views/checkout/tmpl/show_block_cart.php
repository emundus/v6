<?php
/**
 * @package	HikaShop for Joomla!
 * @version	3.0.1
 * @author	hikashop.com
 * @copyright	(C) 2010-2017 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php if(empty($this->ajax)) { ?>
<div id="hikashop_checkout_cart_<?php echo $this->step; ?>_<?php echo $this->module_position; ?>" class="hikashop_checkout_cart">
<?php } ?>
	<div class="hikashop_checkout_loading_elem"></div>
	<div class="hikashop_checkout_loading_spinner"></div>
<?php
	$this->checkoutHelper->displayMessages('cart');
?>
<table class="table table-striped table-hover" width="100%">
	<thead>
		<tr>
<?php
	$row_count = 4;
	if(!empty($this->options['show_cart_image'])) {
		$row_count++;
?>
			<th id="hikashop_cart_product_image_title" class="hikashop_cart_product_image_title hikashop_cart_title"><?php
				echo JText::_('CART_PRODUCT_IMAGE');
			?></th>
<?php } ?>
			<th id="hikashop_cart_product_name_title" class="hikashop_cart_product_name_title hikashop_cart_title"><?php
				echo JText::_('CART_PRODUCT_NAME');
			?></th>
			<th id="hikashop_cart_product_price_title" class="hikashop_cart_product_price_title hikashop_cart_title"><?php
				echo JText::_('CART_PRODUCT_UNIT_PRICE');
			?></th>
			<th id="hikashop_cart_product_quantity_title" class="hikashop_cart_product_quantity_title hikashop_cart_title"><?php
				echo JText::_('PRODUCT_QUANTITY');
			?></th>
			<th id="hikashop_cart_product_total_title" class="hikashop_cart_product_total_title hikashop_cart_title"><?php
				echo JText::_('CART_PRODUCT_TOTAL_PRICE');
			?></th>
		</tr>
	</thead>
	<tbody>
<?php
	$k = 0;
	$group = $this->config->get('group_options', 0);
	$thumbnail_x = $this->config->get('thumbnail_x', 100);
	$thumbnail_y = $this->config->get('thumbnail_y', 100);

	$cart = $this->checkoutHelper->getCart();

	if(empty($this->productClass))
		$this->productClass = hikashop_get('class.product');

	if(!empty($this->options['show_cart_image']) && empty($this->imageHelper))
		$this->imageHelper = hikashop_get('helper.image');

	if(empty($this->currencyClass)) {
		$this->currencyClass = hikashop_get('class.currency');
		$this->currencyHelper =& $this->currencyClass;
	}

	global $Itemid;
	$checkout_itemid = $this->config->get('checkout_itemid');
	if(!empty($checkout_itemid))
		$Itemid = $checkout_itemid;

	$url_itemid='';
	if(!empty($Itemid))
		$url_itemid = '&Itemid=' . $Itemid;

	foreach($cart->products as $i => $product) {
		if(empty($product->cart_product_quantity))
			continue;
		if($group && !empty($product->cart_product_option_parent_id))
			continue;

		$this->productClass->addAlias($product);
?>
		<tr class="row<?php echo $k; ?>">
<?php
		if(!empty($this->options['show_cart_image'])) {
?>
			<td data-title="<?php echo JText::_('CART_PRODUCT_IMAGE'); ?>" class="hikashop_cart_product_image_value">
<?php
			$image = null;
			if(!empty($product->images)) {
				$image = reset($product->images);
				$this->imageHelper->checkSize($thumbnail_x, $thumbnail_y, $image);
			}

			if($image && !$this->config->get('thumbnail')) {
				echo '<img src="'.$this->imageHelper->uploadFolder_url . $image->file_path.'" alt="' . $image->file_name . '" style="margin-top:10px;margin-bottom:10px;display:inline-block;vertical-align:middle" />';
			} else {
?>
				<div class="hikashop_cart_product_image_thumb" ><?php
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
			if($img->success)
				echo '<img class="hikashop_product_checkout_cart_image" title="'.$this->escape(@$image->file_description).'" alt="'.$this->escape(@$image->file_name).'" src="'.$img->url.'"/>';
				?></div>
<?php
		}
?>
			</td>
<?php } ?>
			<td data-title="<?php echo JText::_('CART_PRODUCT_NAME'); ?>" class="hikashop_cart_product_name_value">
				<p class="hikashop_cart_product_name"><?php

		if(!empty($this->options['link_to_product_page'])) {
			?><a class="hikashop_no_print" href="<?php echo hikashop_contentLink('product&task=show&cid=' . $product->product_id . '&name=' . $product->alias . $url_itemid, $product);?>" ><?php
		}

		echo $product->product_name;

		if(!empty($this->options['show_product_code'])) {
			?><span class="hikashop_product_code_checkout"><?php
				echo $product->product_code;
			?></span><?php
		}

		if(!empty($this->options['link_to_product_page'])) {
			?></a><?php
		}

		if($group){
			$display_item_price = false;
			foreach($cart->products as $j => $optionElement) {
				if(empty($optionElement->cart_product_option_parent_id) || (int)$optionElement->cart_product_option_parent_id != (int)$product->cart_product_id)
					continue;
				if(!empty($optionElement->prices[0])) {
					$display_item_price = true;
					break;
				}
			}

			if($display_item_price)
				echo ' <span class="hikashop_product_base_price">' . strip_tags($this->getDisplayProductPrice($product, true)) . '</span>';
		}

				?></p>
<?php
		$input = '';
		$html = '';

		if(hikashop_level(2) && !empty($this->extraFields['item'])) {
			$item = $cart->cart_products[$i];
			foreach($this->extraFields['item'] as $field) {
				$namekey = $field->field_namekey;
				if(empty($item->$namekey) || !strlen($item->$namekey))
					continue;
				$html .= '<p class="hikashop_cart_item_'.$namekey.'">'.$this->fieldClass->getFieldName($field).': '.$this->fieldClass->show($field, $item->$namekey).'</p>';
			}
		}

		if($group) {
			if(!isset($product->prices[0])) {
				$product->prices[0] = new stdClass();
				$product->prices[0]->price_value = 0;
				$product->prices[0]->price_value_with_tax = 0.0;
				$product->prices[0]->price_currency_id = hikashop_getCurrency();
			}

			foreach($cart->products as $j => $optionElement) {
				if(empty($optionElement->cart_product_option_parent_id) || $optionElement->cart_product_option_parent_id != $product->cart_product_id)
					continue;
				if(!empty($optionElement->prices[0]))
					$this->addOptionPriceToProduct($product->prices[0],$optionElement->prices[0]);

				$html .= '<p class="hikashop_cart_option_name">' . $optionElement->product_name;
				if(@$optionElement->prices[0]->price_value_with_tax > 0)
					$html .= ' ( + ' . strip_tags($this->getDisplayProductPrice($optionElement, true)) . ' )';
				$html .= '</p>';
			}
		}

		if(!empty($html))
			echo '<div class="hikashop_cart_product_custom_item_fields">'.$html.'</div>';
?>
			</td>
			<td data-title="<?php echo JText::_('CART_PRODUCT_UNIT_PRICE'); ?>" class="hikashop_cart_product_price_value"><?php
				echo $this->getDisplayProductPrice($product, true);

				if(HIKASHOP_RESPONSIVE) {
					?><span class="visible-phone"><?php echo JText::_('PER_UNIT'); ?></span><?php
				}
			?></td>
			<td data-title="<?php echo JText::_('PRODUCT_QUANTITY'); ?>" class="hikashop_cart_product_quantity_value"><?php

		if(empty($this->options['status'])) {
			if($product->product_parent_id != 0 && isset($product->main_product_quantity_layout))
				$product->product_quantity_layout = $product->main_product_quantity_layout;

			if($product->product_quantity_layout == 'show_select' || (empty($product->product_quantity_layout) && $this->config->get('product_quantity_display', '') == 'show_select')) {
				$min_quantity = $product->product_min_per_order;
				$max_quantity = $product->product_max_per_order;
				if($min_quantity == 0)
					$min_quantity = 1;
				if($max_quantity == 0)
					$max_quantity = (int)$min_quantity * 15;

				$values = array(
					0 => JHTML::_('select.option', 0, 0)
				);
				for($j = $min_quantity; $j <= $max_quantity; $j += $min_quantity) {
					$values[$j] = JHTML::_('select.option', $j, $j);
				}
				$onchange = 'onchange="var qty_field = document.getElementById(\'hikashop_checkout_quantity_'.$product->cart_product_id.'\'); if (qty_field && qty_field.value != \''.$product->cart_product_quantity.'\'){'.$input.'return window.checkout.submitCart('.$this->step.','.$this->module_position.'); } return false;"';
				$ret = JHTML::_('select.genericlist', $values, 'checkout[cart][item]['.$product->cart_product_id.']', 'id="hikashop_checkout_quantity_'.$product->cart_product_id.'" '.$onchange, 'value', 'text', $product->cart_product_quantity);
				echo str_replace('id="checkoutcartitem'.$product->cart_product_id.'"', '', $ret);
			} else {
?>
				<input id="hikashop_checkout_quantity_<?php echo $product->cart_product_id;?>" type="text" name="checkout[cart][item][<?php echo $product->cart_product_id;?>]" class="hikashop_product_quantity_field" value="<?php echo $product->cart_product_quantity; ?>"/>
				<div class="hikashop_cart_product_quantity_refresh">
					<a class="hikashop_no_print" href="#" onclick="var qty_field = document.getElementById('hikashop_checkout_quantity_<?php echo $product->cart_product_id;?>'); if (qty_field && qty_field.value != '<?php echo $product->cart_product_quantity; ?>'){<?php echo $input; ?> return window.checkout.submitCart(<?php echo $this->step; ?>,<?php echo $this->module_position; ?>); } return false;" title="<?php echo JText::_('HIKA_REFRESH'); ?>">
						<img src="<?php echo HIKASHOP_IMAGES . 'refresh.png';?>" border="0" alt="<?php echo JText::_('HIKA_REFRESH'); ?>" />
					</a>
				</div>
<?php
			}

			if(!empty($this->options['show_delete'])) {
				$url = hikashop_currentURL();
?>
				<div class="hikashop_cart_product_quantity_delete">
					<a class="hikashop_no_print" href="<?php echo hikashop_completeLink('product&task=updatecart&product_id='.$product->product_id.'&quantity=0&return_url='.urlencode(base64_encode(urldecode($url)))); ?>" onclick="var qty_field = document.getElementById('hikashop_checkout_quantity_<?php echo $product->cart_product_id;?>'); if(qty_field){qty_field.value=0; return window.checkout.submitCart(<?php echo $this->step; ?>,<?php echo $this->module_position; ?>); } return false;" title="<?php echo JText::_('HIKA_DELETE'); ?>">
						<img src="<?php echo HIKASHOP_IMAGES . 'delete2.png';?>" border="0" alt="<?php echo JText::_('HIKA_DELETE'); ?>" />
					</a>
				</div>
<?php
			}
		}else{
			echo $product->cart_product_quantity;
		}
?>
			</td>
			<td data-title="<?php echo JText::_('CART_PRODUCT_TOTAL_PRICE'); ?>" class="hikashop_cart_product_total_value"><?php
				echo $this->getDisplayProductPrice($product, false);
			?></td>
		</tr>
<?php
		$k = 1-$k;
	}
?>

<?php
	$taxes = round($cart->full_total->prices[0]->price_value_with_tax - $cart->full_total->prices[0]->price_value, $this->currencyClass->getRounding($cart->full_total->prices[0]->price_currency_id));
	if(!empty($cart->coupon) || !empty($cart->shipping) || !empty($cart->additional) || $taxes > 0) {
?>
		<tr class="margin"><td colspan="<?php echo $row_count; ?>" class="hikashop_cart_empty_footer"></td></tr>
		<tr>
			<td colspan="<?php echo $row_count - 2; ?>" class="hikashop_cart_empty_footer"></td>
			<td id="hikashop_checkout_cart_total2_title" class="hikashop_cart_subtotal_title hikashop_cart_title"><?php
				echo JText::_('SUBTOTAL');
			?></td>
			<td class="hikashop_cart_subtotal_value" data-title="<?php echo JText::_('SUBTOTAL'); ?>">
				<span class="hikashop_checkout_cart_subtotal"><?php
					if(!empty($this->options['price_with_tax']))
						echo $this->currencyClass->format(@$cart->total->prices[0]->price_value_with_tax,@$cart->total->prices[0]->price_currency_id);
					else
						echo $this->currencyClass->format(@$cart->total->prices[0]->price_value,@$cart->total->prices[0]->price_currency_id);
				?></span>
			</td>
		</tr>
<?php
		}
		if(!empty($cart->coupon)) {
?>
		<tr>
			<td colspan="<?php echo $row_count - 2; ?>" class="hikashop_cart_empty_footer"></td>
			<td id="hikashop_checkout_cart_coupon_title" class="hikashop_cart_coupon_title hikashop_cart_title"><?php
				echo JText::_('HIKASHOP_COUPON');
			?></td>
			<td class="hikashop_cart_coupon_value" data-title="<?php echo JText::_('HIKASHOP_COUPON'); ?>">
				<span class="hikashop_checkout_cart_coupon"><?php
					if($taxes == 0 || empty($this->options['price_with_tax']))
						echo $this->currencyClass->format(@$cart->coupon->discount_value_without_tax * -1, @$cart->coupon->discount_currency_id);
					else
						echo $this->currencyClass->format(@$cart->coupon->discount_value * -1, @$cart->coupon->discount_currency_id);
				?></span>
			</td>
		</tr>
<?php
		}

		if(!empty($cart->shipping)) {
?>
		<tr>
			<td colspan="<?php echo $row_count - 2; ?>" class="hikashop_cart_empty_footer"></td>
			<td id="hikashop_checkout_cart_shipping_title" class="hikashop_cart_shipping_title hikashop_cart_title"><?php
				echo JText::_('HIKASHOP_SHIPPING');
			?></td>
			<td class="hikashop_cart_shipping_value" data-title="<?php echo JText::_('HIKASHOP_SHIPPING'); ?>">
				<span class="hikashop_checkout_cart_shipping">
<?php
			if(isset($this->value)) {
				echo $this->value;
			} else {
				$shipping_price = null;
				foreach($cart->shipping as $shipping) {
					if(!isset($shipping->shipping_price) && isset($shipping->shipping_price_with_tax) ) {
						$shipping->shipping_price = $shipping->shipping_price_with_tax;
					}
					if(isset($shipping->shipping_price)) {
						if($shipping_price === null)
							$shipping_price = 0.0;
						if($taxes == 0 || empty($this->options['price_with_tax']) || !isset($shipping->shipping_price_with_tax))
							$shipping_price += $shipping->shipping_price;
						else
							$shipping_price += $shipping->shipping_price_with_tax;
					}
				}
				if($shipping_price !== null)
					echo $this->currencyClass->format($shipping_price, $cart->full_total->prices[0]->price_currency_id);
			}
?>
				</span>
			</td>
		</tr>
<?php
		}

		if(!empty($cart->additional)) {
			$exclude_additionnal = explode(',', $this->config->get('order_additional_hide', ''));
			foreach($cart->additional as $k => $additional) {
				if(in_array($additional->name, $exclude_additionnal))
					continue;
?>
		<tr id="hikashop_checkout_cart_additional_<?php echo str_replace(' ','_',$k); ?>_line" >
			<td colspan="<?php echo $row_count - 2; ?>" class="hikashop_cart_empty_footer"></td>
			<td id="hikashop_checkout_cart_additional_<?php echo str_replace(' ','_',$k); ?>_title" class="hikashop_cart_additional_title hikashop_cart_title"><?php
				echo JText::_($additional->name);
			?></td>
			<td class="hikashop_cart_additional_value" data-title="<?php echo JText::_($additional->name); ?>">
				<span class="hikashop_checkout_cart_additional">
<?php
				if(!empty($additional->price_value) || empty($additional->value)) {
					if($taxes == 0 || empty($this->options['price_with_tax']))
						echo $this->currencyClass->format(@$additional->price_value,$additional->price_currency_id);
					else
						echo $this->currencyClass->format(@$additional->price_value_with_tax,$additional->price_currency_id);
				} else
					echo $additional->value;
?>
				</span>
			</td>
		</tr>
<?php
			}
		}

		if($taxes > 0){
			if($this->config->get('detailed_tax_display') && isset($cart->full_total->prices[0]->taxes)) {
				foreach($cart->full_total->prices[0]->taxes as $tax) {
?>
		<tr>
			<td colspan="<?php echo $row_count - 2; ?>" class="hikashop_cart_empty_footer"></td>
			<td id="hikashop_checkout_cart_tax_title" class="hikashop_cart_tax_title hikashop_cart_title"><?php
				echo $tax->tax_namekey;
			?></td>
			<td class="hikashop_cart_tax_value" data-title="<?php echo $tax->tax_namekey; ?>">
				<span class="hikashop_checkout_cart_taxes"><?php
					echo $this->currencyClass->format($tax->tax_amount, $cart->full_total->prices[0]->price_currency_id);
				?></span>
			</td>
		</tr>
<?php
				}
			} else {
?>
		<tr>
			<td colspan="<?php echo $row_count - 2; ?>" class="hikashop_cart_empty_footer"></td>
			<td id="hikashop_checkout_cart_tax_title" class="hikashop_cart_tax_title hikashop_cart_title"><?php
				echo JText::_('TAXES');
			?></td>
			<td class="hikashop_cart_tax_value" data-title="<?php echo Jtext::_('TAXES'); ?>">
				<span class="hikashop_checkout_cart_taxes"><?php
					echo $this->currencyClass->format($taxes, $cart->full_total->prices[0]->price_currency_id);
				?></span>
			</td>
		</tr>
<?php
			}
		}

		if(!empty($cart->payment) && $cart->payment->payment_price != 0) {
?>
		<tr>
			<td colspan="<?php echo $row_count - 2; ?>" class="hikashop_cart_empty_footer"></td>
			<td id="hikashop_checkout_cart_payment_title" class="hikashop_cart_payment_title hikashop_cart_title"><?php
				echo JText::_('HIKASHOP_PAYMENT');
			?></td>
			<td class="hikashop_cart_payment_value" data-title="<?php echo Jtext::_('HIKASHOP_PAYMENT'); ?>">
				<span class="hikashop_checkout_cart_payment"><?php
					if(!isset($cart->payment->payment_price) && isset($cart->payment->payment_price_with_tax) ) {
						if(isset($this->value)) {
							echo $this->value;
						} else {
							$cart->payment->payment_price = 0.0;
							$cart->payment->payment_price_with_tax = 0.0;
						}
					}
					if(isset($cart->payment->payment_price)) {
						if($taxes == 0 || empty($this->options['price_with_tax']) || !isset($cart->payment->payment_price_with_tax) )
							echo $this->currencyClass->format(@$cart->payment->payment_price, $cart->full_total->prices[0]->price_currency_id);
						else
							echo $this->currencyClass->format(@$cart->payment->payment_price_with_tax, $cart->full_total->prices[0]->price_currency_id);
					}
				?></span>
			</td>
		</tr>
<?php
		}
?>
		<tr>
			<td colspan="<?php echo $row_count - 2; ?>" class="hikashop_cart_empty_footer"></td>
			<td id="hikashop_checkout_cart_final_total_title" class="hikashop_cart_total_title hikashop_cart_title"><?php
				echo JText::_('HIKASHOP_TOTAL');
			?></td>
			<td class="hikashop_cart_total_value" data-title="<?php echo Jtext::_('HIKASHOP_TOTAL'); ?>">
				<span class="hikashop_checkout_cart_final_total"><?php
					echo $this->currencyClass->format($cart->full_total->prices[0]->price_value_with_tax, $cart->full_total->prices[0]->price_currency_id);
				?></span>
			</td>
		</tr>
	</tbody>
</table>
<?php

	if(!empty($this->extraData[$this->module_position]) && !empty($this->extraData[$this->module_position]->bottom)) { echo implode("\r\n", $this->extraData[$this->module_position]->bottom); }

	if(false) {
?>
	<noscript>
		<input id="hikashop_checkout_cart_quantity_button" class="btn button" type="submit" name="refresh" value="<?php echo JText::_('REFRESH_CART');?>"/>
	</noscript>
<?php
	}

	if(empty($this->ajax)) {
?>
</div>
<script type="text/javascript">
if(!window.checkout) window.checkout = {};
window.Oby.registerAjax(['checkout.cart.updated','cart.updated'], function(params){
	if(window.checkout.isSource(params, <?php echo (int)$this->step; ?>, <?php echo (int)$this->module_position; ?>))
		return;
	window.checkout.refreshCart(<?php echo (int)$this->step; ?>, <?php echo (int)$this->module_position; ?>);
});
window.checkout.refreshCart = function(step, id) { return window.checkout.refreshBlock('cart', step, id); };
window.checkout.submitCart = function(step, id) { return window.checkout.submitBlock('cart', step, id); };
</script>
<?php
	}
