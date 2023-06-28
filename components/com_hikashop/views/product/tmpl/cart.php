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
$tmpl = hikaInput::get()->getWord('tmpl', '');
$module_id = (int)$this->params->get('id', 0);

if(!in_array($tmpl, array('component', 'ajax', 'raw'))) {
	$events = ($this->cart_type == 'cart') ? '["cart.updated","checkout.cart.updated"]' : '"wishlist.updated"';
?>
<script type="text/javascript">
window.Oby.registerAjax(<?php echo $events; ?>, function(params) {
	var o = window.Oby, el = document.getElementById('hikashop_cart_<?php echo $module_id; ?>');
	if(!el) return;
	if(params && params.resp && params.resp.module == <?php echo (int)$module_id; ?>) return;
	if(params && params.type && params.type != '<?php echo $this->cart_type; ?>') return;
	o.addClass(el, "hikashop_checkout_loading");
	window.hikashop.xRequest("<?php echo hikashop_completeLink('product&task=cart&module_id='.$module_id . '&module_type='.$this->cart_type.$this->url_itemid, true, false, true); ?>", {update:false, mode:'POST', data:'return_url=<?php echo urlencode(base64_encode(hikashop_currentURL('return_url'))); ?>'}, function(xhr){
		o.removeClass(el, "hikashop_checkout_loading");
		var cartDropdown = document.querySelector('#hikashop_cart_<?php echo $module_id; ?> .hikashop_cart_dropdown_content');
		if(cartDropdown) {
			var dropdownType = 'click';
			var dropdownLink = document.querySelector('#hikashop_cart_<?php echo $module_id; ?> .hikashop_small_cart_checkout_link')
			if(dropdownLink) {
				var hover = dropdownLink.getAttribute('onmousehover');
				if(hover) {
					dropdownType = 'hover';
				}
			}
			window.hikashop.updateElem(el, xhr.responseText, true);
			if(cartDropdown.toggleOpen) {
				cartDropdown = document.querySelector('#hikashop_cart_<?php echo $module_id; ?> .hikashop_cart_dropdown_content');
				window.hikashop.toggleOverlayBlock(cartDropdown, dropdownType);
			}
		} else {
			window.hikashop.updateElem(el, xhr.responseText, true);
		}
	});
});
</script>
<?php
} elseif(!headers_sent()){
	header('X-Robots-Tag: noindex');
}

$group = (int)$this->config->get('group_options', 0);
$small_cart = (int)$this->params->get('small_cart', 0);
$link_to_product = (int)$this->params->get('link_to_product_page', 1);
$spinner_css="";
if (!empty($small_cart)) $spinner_css="small_spinner small_cart";

if(empty($this->rows)) {
	$hidecart = (int)$this->params->get('hide_cart', 0);
	$desc = $this->params->get('msg');
	if(!empty($desc))
		$desc = trim($desc);
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

	if(!in_array($tmpl, array('component', 'ajax', 'raw'))) {
?>
<div id="hikashop_cart_<?php echo $module_id; ?>" class="hikashop_cart">
<?php
	}
?>
	<div class="hikashop_checkout_loading_elem"></div>
	<div class="hikashop_checkout_loading_spinner <?php echo $spinner_css ?>"></div>
<?php
	if(!empty($desc))
		echo $desc;

	if(!in_array($tmpl, array('component', 'ajax', 'raw'))) {
?>
</div>
<div class="clear_both"></div>
<?php
	}

	return;
}


$css_button = $this->config->get('css_button', 'hikabtn');
$css_button_checkout = $this->config->get('css_button_checkout', 'hikashop_cart_proceed_to_checkout');

if($this->params->get('print_cart', 0)) {
	$print_button = $this->popup->display(
		'<i class="fas fa-print"></i>',
		'HIKA_PRINT', hikashop_completeLink('cart&task=printcart&cid='.$this->element->cart_id, true),
		'hikashop_print_popup', 760, 480, 'title="'.JText::_('HIKA_PRINT').'"', '', 'link'
	);
}
$icon_html = "";
$path = $this->params->get('icon');	
if(!empty($path)) {
	$icon_html = '';
	if($path != '') {
		$icon_path = trim($path);
		$icon_html = '<img class="hikashop_cart_module_product_icon_title" src="'.$icon_path.'" alt="'.JText::_('HIKA_CARTNOTIFICATION_REDIRECT').'">';
	}
}

$this->setLayout('listing_price');
$this->params->set('show_quantity_field', 0);

if(!in_array($tmpl, array('component', 'ajax', 'raw'))) {
?>
<div id="hikashop_cart_<?php echo $module_id; ?>" class="hikashop_cart">
<?php
}
?>
	<div class="hikashop_checkout_loading_elem"></div>
	<div class="hikashop_checkout_loading_spinner <?php echo $spinner_css ?>"></div>
<?php

echo $this->notice_html;
if(!empty($this->element->messages)) {
	foreach($this->element->messages as $msg) {
		if(empty($msg['type']))
			$msg['type'] = 'success';
		hikashop_display($msg['msg'], $msg['type']);
	}
}
$text = '';
if(!empty($small_cart)) {
	$price_name  = '';
	if(!$this->params->get('show_shipping', 0) && isset($this->total->prices[0]->price_value_without_shipping)){
		$price_name = '_without_shipping';
	}
	if(!$this->params->get('show_coupon', 0) && isset($this->total->prices[0]->price_value_without_discount)){
		$price_name = '_without_discount';
	}
	$price = '';
	if($this->params->get('price_with_tax')){
		$var_name = 'price_value'.$price_name.'_with_tax';
		$price .= $this->currencyClass->format(@$this->total->prices[0]->$var_name, $this->total->prices[0]->price_currency_id);
	}
	if($this->params->get('price_with_tax')==2){
		$price .= JText::_('PRICE_BEFORE_TAX');
	}
	if($this->params->get('price_with_tax')==2||!$this->params->get('price_with_tax')){
		$var_name = 'price_value'.$price_name;
		$price .= $this->currencyClass->format(@$this->total->prices[0]->price_value, $this->total->prices[0]->price_currency_id);
	}
	if($this->params->get('price_with_tax')==2){
		$price .= JText::_('PRICE_AFTER_TAX');
	}
	if((int)$this->params->get('show_cart_quantity', 1)) {
		$qty = 0;
		foreach($this->element->cart_products as $i => $row) {
			if(empty($row->cart_product_quantity) && $this->element->cart_type == 'cart')
				continue;
			if($group && $row->cart_product_option_parent_id)
				continue;

			$qty += $row->cart_product_quantity;
		}

		if($this->params->get('show_price')){
			if($qty == 1 && JText::_('X_ITEM_FOR_X') != 'X_ITEM_FOR_X') {
				$text = JText::sprintf('X_ITEM_FOR_X', $qty, $price);
			} else {
				$text = JText::sprintf('X_ITEMS_FOR_X', $qty, $price);
			}
		}else{
			if($qty == 1)
				$text = JText::sprintf('X_ITEM', $qty);
			else
				$text = JText::sprintf('X_ITEMS', $qty);
		}
	} else {
		if($this->params->get('show_price'))
			$text = JText::sprintf('TOTAL_IN_CART_X', $price);
		else
			$text = JText::_('MINI_CART_PROCEED_TO_CHECKOUT');
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
	}elseif($small_cart == 3) {
		$extra_data .= ' ontouchend="window.hikashop.toggleOverlayBlock(\'hikashop_cart_dropdown_'.$module_id.'\', \'hover\'); return false;" onmouseover="window.hikashop.toggleOverlayBlock(\'hikashop_cart_dropdown_'.$module_id.'\', \'hover\'); return false;"';
	}
?>
<!-- MINI CART MAIN LINK -->
	<a class="hikashop_small_cart_checkout_link" href="<?php echo $link; ?>"<?php echo $extra_data; ?>>
		<span class="hikashop_small_cart_total_title"><?php echo $icon_html . $text; ?></span>
	</a>
<!-- EO MINI CART MAIN LINK -->
<!-- MINI CART PRINT CART BUTTON -->
<?php
	if($this->element->cart_type == 'cart' && $small_cart == 1 && $this->params->get('print_cart', 0)) {
?>		<span class="hikashop_checkout_cart_print_link">
<?php		echo $print_button;
?>		</span>
<?php
	}
?>
<!-- EO MINI CART PRINT CART BUTTON -->
<!-- MINI CART CLEAN CART BUTTON -->
<?php
	if($this->element->cart_type == 'cart' && $small_cart == 1 && $this->params->get('show_cart_delete', 1)) {
		$delete = hikashop_completeLink('product&task=cleancart');
?>
	<a class="hikashop_small_cart_clean_link" title="<?php echo JText::_('EMPTY_THE_CART'); ?>" href="<?php echo $delete; ?>" onclick="window.location='<?php echo $delete. (strpos($delete, '?') ? '&amp;' : '?') .'return_url='; ?>'+window.btoa(window.location); return false;">
		<i class="fa fa-times-circle"></i>
	</a>
<?php
	}
?>
<!-- EO MINI CART CLEAN CART BUTTON -->
<!-- MINI CART PROCEED TO CHECKOUT BUTTON -->
<?php
	if($this->element->cart_type == 'cart' && $small_cart == 1 && $this->params->get('show_cart_proceed', 1)) {
?>
	<a class="<?php echo $css_button . ' ' . $css_button_checkout; ?>" href="<?php echo $this->url_checkout; ?>" onclick="if(this.disable) return false; this.disable = true;"><span><?php
		echo JText::_('PROCEED_TO_CHECKOUT');
	?></span></a>
<?php
	}
?>
<!-- EO MINI CART PROCEED TO CHECKOUT BUTTON -->
<?php
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
	'payment' => (int)$this->params->get('show_payment', 0),
	'taxes' => (int)$this->params->get('show_taxes', 0),
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
<!-- IMAGE HEADER -->
	<tr>
<?php if(!empty($columns['image'])) { ?>
				<th class="hikashop_cart_module_product_image_title hikashop_cart_title"><?php
					echo JText::_('CART_PRODUCT_IMAGE');
				?></th>
<?php } ?>
<!-- EO IMAGE HEADER -->
<!-- NAME HEADER -->
<?php if(!empty($columns['name'])) { ?>
				<th class="hikashop_cart_module_product_name_title hikashop_cart_title"><?php
					echo JText::_('CART_PRODUCT_NAME');
				?></th>
<?php } ?>
<!-- EO NAME HEADER -->
<!-- QUANTITY HEADER -->
<?php if(!empty($columns['quantity'])) { ?>
				<th class="hikashop_cart_module_product_quantity_title hikashop_cart_title"><?php
					echo JText::_('CART_PRODUCT_QUANTITY');
				?></th>
<?php } ?>
<!-- EO QUANTITY HEADER -->
<!-- PRICE HEADER -->
<?php if(!empty($columns['price'])) { ?>
				<th class="hikashop_cart_module_product_price_title hikashop_cart_title"><?php
					echo JText::_('CART_PRODUCT_PRICE');
				?></th>
<?php } ?>
<!-- EO PRICE HEADER -->
<!-- EMPTY COLUMN HEADER -->
<?php if($nb_columns == 0) { ?>
				<th></th>
<?php }
?>
<!-- EO EMPTY COLUMN HEADER -->
<!-- PRINT BUTTON -->
<?php
if($this->params->get('print_cart', 0)) {
?>				<th class="hikashop_cart_module_product_print_title hikashop_cart_title">
					<span class="hikashop_checkout_cart_print_link" style="width: 16px; display: inline-block;">
<?php					echo $print_button;
?>					</span>
				</th>
<?php } ?>
<!-- EO PRINT BUTTON -->
			</tr>
		</thead>
<?php
if(!empty($shows['price']) && $this->element->cart_type == 'cart') {
	$colspan = $nb_columns - (empty($columns['delete']) ? 1 : 2);
?>
		<tfoot>
<!-- COUPON -->
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
<!-- EO COUPON -->
<!-- PAYMENT FEE -->
<?php
if(!empty($shows['payment']) && !empty($this->element->payment) && $this->element->payment->payment_price !== null) { ?>
			<tr>
<?php if($colspan > 0) { ?>
				<td class="hikashop_cart_module_payment_title" colspan="<?php echo $colspan; ?>"><?php
					echo JText::_('HIKASHOP_PAYMENT');
				?></td>
<?php } ?>
				<td class="hikashop_cart_module_payment_value"><?php
					echo $this->currencyClass->format($this->payment_price, $this->total->prices[0]->price_currency_id);
				?></td>
<?php if(!empty($columns['delete'])) { ?>
				<td></td>
<?php } ?>
			</tr>
<?php } ?>
<!-- EO PAYMENT FEE -->
<!-- SHIPPING FEE -->
<?php if(!empty($shows['shipping']) && !empty($this->element->shipping) && $this->shipping_price !== null) { ?>
			<tr>
<?php if($colspan > 0) { ?>
				<td class="hikashop_cart_module_shipping_title" colspan="<?php echo $colspan; ?>"><?php
					echo JText::_('HIKASHOP_SHIPPING');
				?></td>
<?php } ?>
				<td class="hikashop_cart_module_shipping_value"><?php
					echo $this->currencyClass->format($this->shipping_price, $this->total->prices[0]->price_currency_id);
				?></td>
<?php if(!empty($columns['delete'])) { ?>
				<td></td>
<?php } ?>
			</tr>
<?php } ?>
<!-- EO SHIPPING FEE -->
<!-- TAXES -->
<?php
if(!empty($shows['taxes']) && isset($this->total->prices[0])) {
	if ($this->config->get('detailed_tax_display') && !empty($this->total->prices[0]->taxes)) {
		foreach($this->displayingPrices->taxes as $taxname => $taxdata){
?>
			<tr>
<?php
			if($colspan > 0) { ?>
				<td class="hikashop_cart_module_tax_title" colspan="<?php echo $colspan; ?>"><?php
					echo hikashop_translate($taxname);
				?></td>
<?php 		} ?>
				<td class="hikashop_cart_module_tax_value"><?php
					echo $this->currencyClass->format($taxdata->tax_amount, $this->displayingPrices->price_currency_id);
				?></td>
<?php 		if(!empty($columns['delete'])) { ?>
				<td></td>
<?php 		} ?>
			</tr>
<?php 	}
	}else{
?>
			<tr>
<?php
			if($colspan > 0) { ?>
				<td class="hikashop_cart_module_tax_title" colspan="<?php echo $colspan; ?>"><?php
					echo JText::_('TAXES');
				?></td>
<?php 		} ?>
				<td class="hikashop_cart_module_tax_value"><?php
					$taxes = round($this->displayingPrices->total->price_value_with_tax - $this->displayingPrices->total->price_value, $this->currencyClass->getRounding($this->displayingPrices->price_currency_id));
					echo $this->currencyClass->format($taxes, $this->displayingPrices->price_currency_id);
				?></td>
<?php 		if(!empty($columns['delete'])) { ?>
				<td></td>
<?php 		} ?>
			</tr>
<?php
	}
}
?>
<!-- EO TAXES -->
<!-- TOTAL -->
			<tr>
<?php if($colspan > 0) { ?>
				<td class="hikashop_cart_module_product_total_title" colspan="<?php echo $colspan; ?>"><?php
					echo JText::_('HIKASHOP_TOTAL');
				?></td>
<?php } ?>
				<td class="hikashop_cart_module_product_total_value"><?php
					if($this->params->get('price_with_tax', 3) == 3) {
						$this->params->set('price_with_tax', (int)$this->config->get('price_with_tax'));
					}
					$total_price = '';
					if($this->params->get('price_with_tax')){
						$total_price .= $this->currencyClass->format($this->displayingPrices->total->price_value_with_tax, $this->displayingPrices->price_currency_id);
					}
					if($this->params->get('price_with_tax')==2){
						$total_price .= JText::_('PRICE_BEFORE_TAX');
					}
					if($this->params->get('price_with_tax')==2||!$this->params->get('price_with_tax')){
						$total_price .= $this->currencyClass->format($this->displayingPrices->total->price_value, $this->displayingPrices->price_currency_id);
					}
					if($this->params->get('price_with_tax')==2){
						$total_price .= JText::_('PRICE_AFTER_TAX');
					}
					?>
					<span class="hikashop_product_price_full">
						<span class="hikashop_product_price hikashop_product_price_0">
							<?php echo $total_price; ?>
						</span>
					</span>
				</td>
<?php if(!empty($columns['delete'])) { ?>
				<td></td>
<?php } ?>
			</tr>
<!-- EO TOTAL -->
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
<!-- IMAGE -->
<?php
	if(!empty($columns['image'])) {
?>
				<td class="hikashop_cart_module_product_image hikashop_cart_value" style="vertical-align:middle !important;text-align:center;"><?php
		$img = $this->imageHelper->getThumbnail(@$product->images[0]->file_path, array('width' => $width, 'height' => $height), $image_options);
		if($img->success) {
			$attributes = '';
			if($img->external)
				$attributes = ' width="'.$img->req_width.'" height="'.$img->req_height.'"';
			?><img class="hikashop_product_cart_image" title="<?php echo $this->escape((string)@$product->images[0]->file_description); ?>" alt="<?php echo $this->escape((string)@$product->images[0]->file_name); ?>" src="<?php echo $img->url; ?>" <?php echo $attributes; ?>/><?php
		}
				?></td>
<?php
	}
?>
<!-- EO IMAGE -->
<!-- NAME -->
<?php
	if(!empty($columns['name'])) {
?>
				<td class="hikashop_cart_module_product_name_value hikashop_cart_value">
<?php
		if($link_to_product == 1) {
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
		if($link_to_product == 1) {
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
<!-- EO NAME -->
<!-- QUANTITY INPUT -->
<?php
	if(!empty($columns['quantity'])) {
?>
				<td class="hikashop_cart_module_product_quantity_value hikashop_cart_value"><?php
		$this->row =& $product;
		$this->quantityLayout = $this->cartHelper->getProductQuantityLayout($this->row);
		echo $this->loadHkLayout('quantity', array(
			'id_prefix' => 'hikashop_cart_'.$module_id.'_quantity_field',
			'quantity_fieldname' => 'item['.$product->cart_product_id.'][cart_product_quantity]',
			'onchange_script' => ' window.hikashop.checkQuantity(this); if(this.value == '.(int)$product->cart_product_quantity.'){ return; } if(this.form.onsubmit && !this.form.onsubmit()) return; this.form.submit();',
			'onincrement_script' => ' window.hikashop.updateQuantity(this,\'{id}\'); var input = document.getElementById(\'{id}\'); if(input.value == '.(int)$product->cart_product_quantity.'){ return false; } if(input.form.onsubmit && !input.form.onsubmit()) return false; input.form.submit(); return false;',
			'refresh_task' => 'updatecart',
		));
				?></td>
<?php
	}
?>
<!-- EO QUANTITY INPUT -->
<!-- PRICE -->
<?php
	if(!empty($columns['price'])) {
		if($group) {
			foreach($this->element->products as $j => $optionElement) {
				if($optionElement->cart_product_option_parent_id != $product->cart_product_id)
					continue;
				if(empty($optionElement->prices[0]))
					continue;
				if(!isset($product->prices[0])) {
					$product->prices[0] = new stdClass();
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

		$price_with_tax_option = $this->params->get('price_with_tax');
		if(!empty($shows['taxes']) && $this->params->get('price_with_tax') == 1)
			$this->params->set('price_with_tax',0);

		if($this->params->get('show_discount', 3) == 3 && isset($this->default_params['show_discount'])) {
			$this->params->set('show_discount', (int)$this->default_params['show_discount']);
		}

		echo $this->loadTemplate();

		if(!empty($shows['taxes']) && $price_with_tax_option == 1)
			$this->params->set('price_with_tax',$price_with_tax_option);
				?></td>
<?php } ?>
<!-- EO PRICE -->
<!-- DELETE BUTTON -->
<?php
	if(!empty($columns['delete'])) {
		$delete_url = hikashop_completeLink('product&task=updatecart&cart_id='.(int)$this->element->cart_id.'&cart_product_id='.(int)$product->cart_product_id.'&quantity=0');
		$delete_url .= ((strpos($delete_url, '?') === false) ? '?' : '&') . 'return_url='.urlencode(base64_encode(urldecode($this->params->get('url'))));
?>
				<td class="hikashop_cart_module_product_delete_value hikashop_cart_value">
					<a href="<?php echo $delete_url; ?>" data-cart-id="<?php echo (int)$this->element->cart_id; ?>" data-cart-type="<?php echo $this->escape($this->element->cart_type); ?>" data-cart-product-id="<?php echo (int)$product->cart_product_id; ?>" onclick="if(window.hikashop) { return window.hikashop.deleteFromCart(this, null, 'hikashop_cart_<?php echo $module_id; ?>'); }" title="<?php echo JText::_('HIKA_DELETE'); ?>">
						<i class="fa fa-times-circle"></i>
					</a>
				</td>
<?php
	}
?>
<!-- EO DELETE BUTTON -->
<!-- EMPTY COLUMN -->
<?php
	if($nb_columns == 0) {
?>
				<td></td>
<?php
	}
?>
<!-- EO EMPTY COLUMN -->
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
<!-- PROCEED TO CHECKOUT BUTTON -->
<?php
if($this->element->cart_type == 'cart' && $this->params->get('show_cart_proceed', 1)) {
?>
	<a class="<?php echo $css_button . ' ' . $css_button_checkout; ?>" href="<?php echo $this->url_checkout; ?>" onclick="if(this.disable) return false; this.disable = true;"><span><?php
		echo JText::_('PROCEED_TO_CHECKOUT');
	?></span></a>
<?php
}
?>
<!-- EO PROCEED TO CHECKOUT BUTTON -->
<!-- BOTTOM EXTRA DATA -->
<?php
if(!empty($this->extraData->bottom)) { echo implode("\r\n", $this->extraData->bottom); }
?>
<!-- EO BOTTOM EXTRA DATA -->
<?php
if(in_array($small_cart, array(2, 3))) {
?>
	</div>
	</div>
<?php
}

if(!in_array($tmpl, array('component', 'ajax', 'raw'))) {
?>
</div>
<div class="clear_both"></div>
<?php
}
