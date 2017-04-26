<?php
/**
 * @package	HikaShop for Joomla!
 * @version	3.0.1
 * @author	hikashop.com
 * @copyright	(C) 2010-2017 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
$tmpl = JRequest::getWord('tmpl', '');
$module_id = (int)$this->params->get('id', 0);

if(!in_array($tmpl, array('component', 'ajax'))) {
	$events = ($this->cart_type == 'cart') ? '["cart.updated","checkout.cart.updated"]' : '"wishlist.updated"';
?>
<script type="text/javascript">
window.Oby.registerAjax(<?php echo $events; ?>, function(params) {
	var o = window.Oby, el = document.getElementById('hikashop_cart_<?php echo $module_id; ?>');
	if(!el) return;
	if(params && params.resp && (params.resp.ret === 0 || params.resp.module == <?php echo (int)$module_id; ?>)) return;
	if(params && params.type && params.type != '<?php echo $this->cart_type; ?>') return;
	if(params && params.cart_empty) return;
	o.addClass(el, "hikashop_checkout_loading");
	o.xRequest("<?php echo hikashop_completeLink('product&task=cart&module_id='.$module_id . '&module_type='.$this->cart_type.'&return_url='.urlencode(base64_encode(hikashop_currentURL('return_url'))), true, false, true); ?>", {update: el}, function(xhr){
		o.removeClass(el, "hikashop_checkout_loading");
	});
});
</script>
<?php
}

if(empty($this->rows)) {
	$hidecart = (int)$this->params->get('hide_cart', 0);
	$desc = trim($this->params->get('msg'));
	if((empty($desc) && $desc != '0') || $hidecart == 0)
		$desc = ($this->cart_type == 'cart') ? JText::_('CART_EMPTY') : JText::_('WISHLIST_EMPTY');
	if($hidecart == 2)
		$desc = '';

	if(empty($desc) && $desc != '0' && $tmpl == 'component') {
		if(!headers_sent())
			header('Content-Type: text/css; charset=utf-8');
		exit;
	}

	if(!empty($desc))
		echo $this->notice_html;

	if(!in_array($tmpl, array('component', 'ajax'))) {
?>
<div id="hikashop_cart_<?php echo $module_id; ?>" class="hikashop_cart">
<?php
	}
?>
	<div class="hikashop_checkout_loading_elem"></div>
	<div class="hikashop_checkout_loading_spinner"></div>
<?php
	if(!empty($desc))
		echo $desc;

	if(!in_array($tmpl, array('component', 'ajax'))) {
?>
</div>
<div class="clear_both"></div>
<?php
	}

	return;
}


$css_button = $this->config->get('css_button', 'hikabtn');
$css_button_checkout = $this->config->get('css_button_checkout', '');

$group = (int)$this->config->get('group_options', 0);
$small_cart = (int)$this->params->get('small_cart', 0);

$this->setLayout('listing_price');
$this->params->set('show_quantity_field', 0);

if(!in_array($tmpl, array('component', 'ajax'))) {
?>
<div id="hikashop_cart_<?php echo $module_id; ?>" class="hikashop_cart">
<?php
}
?>
	<div class="hikashop_checkout_loading_elem"></div>
	<div class="hikashop_checkout_loading_spinner"></div>
<?php
	if($this->element->cart_type == 'cart' && $this->config->get('print_cart', 0) && empty($small_cart)) {
?>
	<div class="hikashop_checkout_cart_print_link">
<?php
		echo $this->popup->display(
			'<img src="'.HIKASHOP_IMAGES.'print.png" alt="'.JText::_('HIKA_PRINT').'" />',
			'HIKA_PRINT', hikashop_completeLink('cart&task=printcart&cid='.$this->element->cart_id, true),
			'hikashop_print_popup', 760, 480, '', '', 'link'
		);
?>
	</div>
<?php
	}
?>
<?php

echo $this->notice_html;
if(!empty($this->element->messages)) {
	foreach($this->element->messages as $msg) {
		if(empty($msg['type']))
			$msg['type'] = 'success';
		hikashop_display($msg['msg'], $msg['type']);
	}
}

if(!empty($small_cart)) {
	$this->setLayout('listing_price');
	$this->row = $this->total;
	if((int)$this->params->get('show_cart_quantity', 1)) {
		$qty = 0;
		foreach($this->element->cart_products as $i => $row) {
			if(empty($row->cart_product_quantity) && $this->element->cart_type == 'cart')
				continue;
			if($group && $row->cart_product_option_parent_id)
				continue;

			$qty += $row->cart_product_quantity;
		}

		if($qty == 1 && JText::_('X_ITEM_FOR_X') != 'X_ITEM_FOR_X') {
			$text = JText::sprintf('X_ITEM_FOR_X', $qty, $this->loadTemplate());
		} else {
			$text = JText::sprintf('X_ITEMS_FOR_X', $qty, $this->loadTemplate());
		}
	} else {
		$text = JText::sprintf('TOTAL_IN_CART_X', $this->loadTemplate());
	}
	unset($this->row);

	$extra_data = '';
	if($this->element->cart_type == 'cart') {
		$link = $this->url_checkout;
	} else {
		$link = hikashop_completeLink('cart&task=showcart&cart_id='.$this->element->cart_id.'&cart_type='.$this->element->cart_type . $this->cart_itemid);
	}
	if($small_cart == 2) {
		$extra_data .= ' onclick="if(window.hikashop.toggleOverlayBlock(\'hikashop_cart_dropdown_'.$module_id.'\')) return false;"';
	}
?>
	<a class="hikashop_small_cart_checkout_link" href="<?php echo $link; ?>"<?php echo $extra_data; ?>>
		<span class="hikashop_small_cart_total_title"><?php echo $text; ?></span>
	</a>
<?php

	if($this->element->cart_type == 'cart' && $small_cart == 1 && $this->params->get('show_cart_delete', 1)) {
		$delete = hikashop_completeLink('product&task=cleancart');
		$delete .= (strpos($delete, '?') ? '&amp;' : '?');
?>
	<a class="hikashop_small_cart_clean_link" href="#" onclick="window.location='<?php echo $delete.'return_url='; ?>'+window.btoa(window.location); return false;">
		<img src="<?php echo HIKASHOP_IMAGES . 'delete2.png';?>" style="max-width:inherit;" border="0" alt="<?php echo JText::_('EMPTY_THE_CART'); ?>" />
	</a>
<?php
	}

	if($this->element->cart_type == 'cart' && $small_cart == 1 && $this->params->get('show_cart_proceed', 1)) {
?>
	<a class="<?php echo $css_button . ' ' . $css_button_checkout; ?>" href="<?php echo $this->url_checkout; ?>" onclick="if(this.disable) return false; this.disable = true;"><span><?php
		echo JText::_('PROCEED_TO_CHECKOUT');
	?></span></a>
<?php
	}

	if($small_cart == 1) {
?>
</div>
<div class="clear_both"></div>
<?php
		return;
	}

	$alignment = '';
	$v = (int)$this->params->get('dropdown_left', 0);
	if($v != 0) $alignment .= 'left:'.(-$v).'px;';
	$v = (int)$this->params->get('dropdown_right', 0);
	if($v != 0) $alignment .= 'right:'.(-$v).'px;';
?>
	<div class="hikashop_cart_dropdown_container">
	<div class="hikashop_cart_dropdown_content" id="hikashop_cart_dropdown_<?php echo $module_id; ?>" style="display:none;<?php echo $alignment; ?>">
<?php
}

$shows = array(
	'price' => (int)$this->params->get('show_price', 1),
	'coupon' => (int)$this->params->get('show_coupon', 0),
	'shipping' => (int)$this->params->get('show_shipping', 0),
);
$columns = array(
	'image' => (int)$this->params->get('image_in_cart', 0),
	'name' => (int)$this->params->get('show_cart_product_name', 1),
	'quantity' => (int)$this->params->get('show_cart_quantity', 1),
	'price' => (int)$shows['price'],
	'delete' => (int)$this->params->get('show_cart_delete', 1)
);
$nb_columns = 0;
foreach($columns as $c) {
	if(!empty($c))
		$nb_columns++;
}

?>
	<form action="<?php echo hikashop_completeLink('product&task=updatecart'.$this->url_itemid, false, true); ?>" method="post" name="hikashop_<?php echo $this->element->cart_type; ?>_form" onsubmit="if(window.hikashop) return window.hikashop.submitCartModule(this, 'hikashop_cart_<?php echo $module_id; ?>', '<?php echo $this->element->cart_type; ?>');">
		<table class="hikashop_cart" width="100%">
		<thead>
			<tr>
<?php if(!empty($columns['image'])) { ?>
				<th class="hikashop_cart_module_product_image_title hikashop_cart_title"><?php
					echo JText::_('CART_PRODUCT_IMAGE');
				?></th>
<?php } ?>
<?php if(!empty($columns['name'])) { ?>
				<th class="hikashop_cart_module_product_name_title hikashop_cart_title"><?php
					echo JText::_('CART_PRODUCT_NAME');
				?></th>
<?php } ?>
<?php if(!empty($columns['quantity'])) { ?>
				<th class="hikashop_cart_module_product_quantity_title hikashop_cart_title"><?php
					echo JText::_('CART_PRODUCT_QUANTITY');
				?></th>
<?php } ?>
<?php if(!empty($columns['price'])) { ?>
				<th class="hikashop_cart_module_product_price_title hikashop_cart_title"><?php
					echo JText::_('CART_PRODUCT_PRICE');
				?></th>
<?php } ?>
<?php if(!empty($columns['delete'])) { ?>
				<th class="hikashop_cart_title"></th>
<?php } ?>
<?php if($nb_columns == 0) { ?>
				<th></th>
<?php } ?>
			</tr>
		</thead>
<?php
if(!empty($shows['price']) && $this->element->cart_type == 'cart') {
	$colspan = $nb_columns - (empty($columns['delete']) ? 1 : 2);
?>
		<tfoot>
<?php if(!empty($shows['coupon']) && !empty($this->element->coupon)) { ?>
			<tr>
<?php if($colspan > 0) { ?>
				<td class="hikashop_cart_module_coupon_title" colspan="<?php echo $colspan; ?>"><?php
					echo JText::_('HIKASHOP_COUPON');
				?></td>
<?php } ?>
				<td class="hikashop_cart_module_coupon_value"><?php
					if(!$this->params->get('price_with_tax'))
						echo $this->currencyClass->format(@$this->element->coupon->discount_value_without_tax * -1, @$this->element->coupon->discount_currency_id);
					else
						echo $this->currencyClass->format(@$this->element->coupon->discount_value * -1, @$this->element->coupon->discount_currency_id);
				?></td>
<?php if(!empty($columns['delete'])) { ?>
				<td></td>
<?php } ?>
			</tr>
<?php } ?>
<?php if(!empty($shows['shipping']) && !empty($this->element->shipping) && $this->shipping_price !== null) { ?>
			<tr>
<?php if($colspan > 0) { ?>
				<td class="hikashop_cart_module_shipping_title" colspan="<?php echo $colspan; ?>"><?php
					echo JText::_('HIKASHOP_SHIPPING');
				?></td>
<?php } ?>
				<td class="hikashop_cart_module_coupon_value"><?php
					echo $this->currencyClass->format($this->shipping_price, $this->total->prices[0]->price_currency_id);
				?></td>
<?php if(!empty($columns['delete'])) { ?>
				<td></td>
<?php } ?>
			</tr>
<?php } ?>
			<tr>
<?php if($colspan > 0) { ?>
				<td class="hikashop_cart_module_product_total_title" colspan="<?php echo $colspan; ?>"><?php
					echo JText::_('HIKASHOP_TOTAL');
				?></td>
<?php } ?>
				<td class="hikashop_cart_module_product_total_value"><?php
					$this->row = $this->total;
					echo $this->loadTemplate();
				?></td>
<?php if(!empty($columns['delete'])) { ?>
				<td></td>
<?php } ?>
			</tr>
		</tfoot>
<?php } ?>
		<tbody>
<?php
$group = $this->config->get('group_options', 0);
$width = (int)$this->config->get('cart_thumbnail_x', 50);
$height = (int)$this->config->get('cart_thumbnail_y', 50);
$image_options = array(
	'default' => true,
	'forcesize' => $this->config->get('image_force_size', true),
	'scale' => $this->config->get('image_scale_mode','inside')
);

$k = 0;
foreach($this->element->products as $k => $product) {
	if($group && !empty($product->cart_product_option_parent_id))
		continue;
	if(empty($product->cart_product_quantity) || substr($k,0,1) === 'p')
		continue;

	$cart_product = $this->element->cart_products[$k];
?>
			<tr class="row<?php echo $k; ?>">
<?php
	if(!empty($columns['image'])) {
?>
				<td class="hikashop_cart_module_product_image hikashop_cart_value" style="vertical-align:middle !important;text-align:center;"><?php
		$img = $this->imageHelper->getThumbnail(@$product->images[0]->file_path, array('width' => $width, 'height' => $height), $image_options);
		if($img->success) {
			?><img class="hikashop_product_cart_image" title="<?php echo $this->escape(@$product->images[0]->file_description); ?>" alt="<?php echo $this->escape(@$product->images[0]->file_name); ?>" src="<?php echo $img->url; ?>"/><?php
		}
				?></td>
<?php
	}
?>
<?php
	if(!empty($columns['name'])) {
?>
				<td class="hikashop_cart_module_product_name_value hikashop_cart_value">
<?php
		if(!empty($this->default_params['link_to_product_page'])) {
			?><a href="<?php echo hikashop_contentLink('product&task=show&cid='.$product->product_id.'&name='.$product->alias.$this->url_itemid, $product);?>"><?php
		}
?>
<?php
		echo $product->product_name;
?>
<?php
		if ($this->config->get('show_code')) {
			?><span class="hikashop_product_code_cart"><?php echo $product->product_code; ?></span><?php
		}
?>
<?php
		if(!empty($this->default_params['link_to_product_page'])) {
			?></a><?php
		}
?>
<?php
		$html = '';
		if(hikashop_level(2) && !empty($this->itemFields)) {
			foreach($this->itemFields as $field) {
				$namekey = $field->field_namekey;
				if(empty($cart_product->$namekey) || !strlen($cart_product->$namekey))
					continue;
				$html .= '<p class="hikashop_cart_item_'.$namekey.'">' .
					$this->fieldsClass->getFieldName($field) . ': ' .
					$this->fieldsClass->show($field, $cart_product->$namekey) .
					'</p>';
			}
		}
		if($group) {
			foreach($this->element->products as $j => $optionElement) {
				if($optionElement->cart_product_option_parent_id != $product->cart_product_id)
					continue;
				if(!empty($optionElement->variant_name)) {
					$text = $optionElement->variant_name;
				} elseif(empty($optionElement->characteristics_text)){
					$text = $optionElement->product_name;
				} else {
					$text = $optionElement->characteristics_text;
				}
				$html .= '<p class="hikashop_cart_option_name">'. $text . '</p>';
			}
		}
		if(!empty($html)) {
?>
					<div class="hikashop_cart_product_custom_item_fields"><?php
						echo $html;
					?></div>
<?php
		}
?>
				</td>
<?php
	}
?>
<?php
	if(!empty($columns['quantity'])) {
?>
				<td class="hikashop_cart_module_product_quantity_value hikashop_cart_value"><?php
		$this->row =& $product;
		$this->quantityLayout = $this->cartHelper->getProductQuantityLayout($this->row);
		if(!in_array($this->quantityLayout, array('show_simple','show_select','show_select_price','show_none')))
			$this->quantityLayout = 'show_simple';
		echo $this->loadHkLayout('quantity', array(
			'quantity_fieldname' => 'item['.$product->cart_product_id.'][cart_product_quantity]',
			'onchange_script' => 'window.hikashop.checkQuantity(this); if(this.value == '.(int)$product->cart_product_quantity.'){ return; } if(this.form.onsubmit && !this.form.onsubmit()) return; this.form.submit();',
		));
				?></td>
<?php
	}
?>
<?php
	if(!empty($columns['price'])) {
		if($group) {
			foreach($this->element->products as $j => $optionElement) {
				if($optionElement->cart_product_option_parent_id != $product->cart_product_id)
					continue;
				if(empty($optionElement->prices[0]))
					continue;
				if(!isset($product->prices[0])) {
					$product->prices[0]->price_value = 0;
					$product->prices[0]->price_value_with_tax = 0;
					$product->prices[0]->price_currency_id = hikashop_getCurrency();
				}
				foreach(get_object_vars($product->prices[0]) as $key => $value) {
					if(strpos($key, 'price_value') === false)
						continue;
					if(is_object($value)) {
						foreach(get_object_vars($value) as $key2 => $var2) {
							$product->prices[0]->$key->$key2 += @$optionElement->prices[0]->$key->$key2;
						}
					} else {
						$product->prices[0]->$key += @$optionElement->prices[0]->$key;
					}
				}
			}
		}
?>
				<td class="hikashop_cart_module_product_price_value hikashop_cart_value"><?php
		$this->row =& $product;
		$this->unit = false;
		$this->cart_product_price = true;
		echo $this->loadTemplate();
				?></td>
<?php } ?>
<?php
	if(!empty($columns['delete'])) {
		$delete_url = hikashop_completeLink('product&task=updatecart&cart_id='.(int)$this->element->cart_id.'&cart_product_id='.(int)$product->cart_product_id.'&quantity=0&return_url='.urlencode(base64_encode(urldecode($this->params->get('url')))));
?>
				<td class="hikashop_cart_module_product_delete_value hikashop_cart_value">
					<a href="<?php echo $delete_url; ?>" data-cart-id="<?php echo (int)$this->element->cart_id; ?>" data-cart-type="<?php echo $this->escape($this->element->cart_type); ?>" data-cart-product-id="<?php echo (int)$product->cart_product_id; ?>" onclick="if(window.hikashop) { return window.hikashop.deleteFromCart(this, null, 'hikashop_cart_<?php echo $module_id; ?>'); }" title="<?php echo JText::_('HIKA_DELETE'); ?>"><img src="<?php echo HIKASHOP_IMAGES . 'delete2.png';?>" style="max-width:inherit;" border="0" alt="<?php echo JText::_('HIKA_DELETE'); ?>" /></a>
				</td>
<?php
	}
?>
<?php
	if($nb_columns == 0) {
?>
				<td></td>
<?php
	}
?>
			</tr>
<?php
	$k = 1 - $k;
}
?>
		</tbody>
		</table>
		<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>"/>
		<input type="hidden" name="ctrl" value="product"/>
		<input type="hidden" name="task" value="updatecart"/>
		<input type="hidden" name="cart_type" value="<?php echo $this->cart_type; ?>"/>
		<input type="hidden" name="url" value="<?php echo $this->escape($this->params->get('url')); ?>"/>
<?php
if($this->params->get('show_cart_quantity', 1)) {
?>
		<noscript>
			<input type="submit" class="<?php echo $css_button; ?>" name="refresh" value="<?php echo JText::_('REFRESH_CART');?>"/>
		</noscript>
<?php
}
?>
	</form>
<?php
if($this->element->cart_type == 'cart' && $this->params->get('show_cart_proceed', 1)) {
?>
	<a class="<?php echo $css_button . ' ' . $css_button_checkout; ?>" href="<?php echo $this->url_checkout; ?>" onclick="if(this.disable) return false; this.disable = true;"><span><?php
		echo JText::_('PROCEED_TO_CHECKOUT');
	?></span></a>
<?php
}

if(!empty($this->extraData->bottom)) { echo implode("\r\n", $this->extraData->bottom); }

if($small_cart == 2) {
?>
	</div>
	</div>
<?php
}

if(!in_array($tmpl, array('component', 'ajax'))) {
?>
</div>
<div class="clear_both"></div>
<?php
}
