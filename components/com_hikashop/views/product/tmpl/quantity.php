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
if($this->config->get('add_to_cart_legacy', true)) {
	$this->setLayout('quantity_legacy');
	echo $this->loadTemplate();
	return;
}

$catalogue_mode = $this->config->get('catalogue', false) || !$this->params->get('add_to_cart', 1);
$user = JFactory::getUser();
$enable_wishlist = (hikashop_level(1) && $this->config->get('enable_wishlist', 1) && $this->params->get('add_to_wishlist', 0) && (!$this->config->get('hide_wishlist_guest', 1) || empty($user->guest)));
if($catalogue_mode && !$enable_wishlist)
	return;

$stock = $this->row->product_quantity;
if($stock == -1 && !empty($this->element->main) && isset($this->element->main->product_quantity))
	$stock = $this->element->main->product_quantity;
$in_stock = $stock != 0;
$is_free = empty($this->row->prices);
$display_free_cart = (int)$this->config->get('display_add_to_cart_for_free_products', 0);
$display_free_wishlist = (int)$this->config->get('display_add_to_wishlist_for_free_products', 1);
$display_quantity_field = ((int)$this->config->get('show_quantity_field', -1) != 0);
$this->global_on_listing = $this->config->get('show_quantity_field') == 2;

$waitlist = (int)$this->config->get('product_waitlist', 0);
$waitlist_btn = !$in_stock && (hikashop_level(1) && ($waitlist == 2 || ($waitlist == 1 && (!empty($this->row->main->product_waitlist) || !empty($this->row->product_waitlist)))));

$now = time();
$start_date = (@$this->row->product_sale_start || empty($this->element->main)) ? @$this->row->product_sale_start : $this->element->main->product_sale_start;
$end_date = (@$this->row->product_sale_end || empty($this->element->main)) ? @$this->row->product_sale_end : $this->element->main->product_sale_end;
$product_available = ($end_date <= 0 || $end_date >= $now) && ($start_date <= 0 || $start_date < $now);
$add_to_cart = !$catalogue_mode && $in_stock && (!$is_free || $display_free_cart) && $product_available;
$add_to_wishlist = $enable_wishlist && (!$is_free || $display_free_wishlist);

$css_button = $this->config->get('css_button', 'hikabtn');
$css_button_cart = $this->config->get('css_button_cart', 'hikacart');
$css_button_wishlist = $this->config->get('css_button_wishlist', 'hikawishlist');

global $Itemid;
$url_itemid = '';
if(!empty($Itemid)) {
	$url_itemid = '&Itemid='.$Itemid;
}

if($this->global_on_listing && !empty($this->row->formName)) {
	if($add_to_cart) {
		$attributes = ' class="' . $css_button . ' ' . $css_button_cart. '" onclick="if(window.hikashop.addToCart) { return window.hikashop.addToCart(this); }" data-addTo-div="'.$this->row->formName.'" data-addToCart="list" data-addTo-class="add_in_progress"';
		$content = JText::_('ADD_TO_CART');
		echo $this->loadHkLayout('button', array( 'attributes' => $attributes, 'content' => $content, 'fallback_url' => hikashop_completeLink('product&task=updatecart&add=1')));
	}
	if($add_to_wishlist) {
		$attributes = ' class="' . $css_button . ' ' . $css_button_wishlist. '" onclick="if(window.hikashop.addToWishlist) { document.getElementById(\'hikashop_cart_type_0\').value = \'wishlist\'; return window.hikashop.addToWishlist(this); }" data-addTo-div="'.$this->row->formName.'" data-addToWishlist="list" data-addTo-class="add_in_progress"';
		$content = JText::_('ADD_TO_WISHLIST');
		echo $this->loadHkLayout('button', array( 'attributes' => $attributes, 'content' => $content, 'fallback_url' => hikashop_completeLink('product&task=updatecart&add=1')));
	}
	return;
}

$this->global_on_listing = false;
$classical_url = 'product&task=updatecart&add=1&product_id='.$this->row->product_id.$url_itemid;
if(!empty($this->return_url))
	$classical_url .= '&return_url=' . urlencode(base64_encode(urldecode($this->redirect_url)));

?>
<!-- SALE END MESSAGE -->
<?php
if($end_date > 0 && $end_date < $now) {
?>
	<span class="hikashop_product_sale_end"><?php
		echo JText::_('ITEM_NOT_SOLD_ANYMORE');
	?></span>
<?php
}
?>
<!-- EO SALE END MESSAGE -->
<!-- SALE START MESSAGE -->
<?php
if($start_date > 0 && $start_date > $now) {
?>
	<span class="hikashop_product_sale_start"><?php
		echo JText::sprintf('ITEM_SOLD_ON_DATE', hikashop_getDate($start_date, $this->params->get('date_format', '%d %B %Y')));
	?></span>
<?php
}
?>
<!-- EO SALE START MESSAGE -->
<!-- STOCK MESSAGE -->
<?php
$stock_class = ($stock != 0) ? "" : " hikashop_product_no_stock";
?>
<span class="hikashop_product_stock_count<?php echo $stock_class; ?>">
<?php
	if(!empty($this->row->product_stock_message))
		echo JText::sprintf($this->row->product_stock_message, $stock);
	elseif($stock > 0)
		echo (($stock == 1 && JText::_('X_ITEM_IN_STOCK') != 'X_ITEM_IN_STOCK') ? JText::sprintf('X_ITEM_IN_STOCK', $stock) : JText::sprintf('X_ITEMS_IN_STOCK', $stock));
	elseif(!$in_stock)
		echo JText::_('NO_STOCK');
?>
</span>
<!-- EO STOCK MESSAGE -->
<!-- WAITLIST BUTTON -->
<?php

if($waitlist_btn) {
	$tmpl = hikaInput::get()->getString('tmpl', '');
?>
	<a class="<?php echo $css_button; ?> hika_waitlist_btn" rel="nofollow" href="<?php echo hikashop_completeLink('product&task=waitlist&cid='.$this->row->product_id.$url_itemid, $tmpl=='component'); ?>"><span><?php
		echo JText::_('ADD_ME_WAITLIST');
	?></span></a>
<?php
}
?>
<!-- EO WAITLIST BUTTON -->
<!-- QUANTITY INPUT -->
<?php

if(($add_to_cart || $add_to_wishlist) && $display_quantity_field) {
	$this->setLayout('show_quantity');
	echo $this->loadTemplate();
}
?>
<!-- EO QUANTITY INPUT -->
<!-- ADD TO CART BUTTON -->
<?php
if($add_to_cart) {
	$attributes = ' class="' . $css_button . ' ' . $css_button_cart. '" onclick="if(window.hikashop.addToCart) { return window.hikashop.addToCart(this); }" data-addToCart="'.$this->row->product_id.'" data-addTo-div="hikashop_product_form" data-addTo-class="add_in_progress"';
	if(!empty($this->last_quantity_field_id))
		$attributes .= ' id="'.$this->last_quantity_field_id.'_add_to_cart_button"';
	if(!empty($this->row->product_addtocart_message))
		$content = JText::_($this->row->product_addtocart_message);
	else if(!empty($this->row->main->product_addtocart_message))
		$content = JText::_($this->row->main->product_addtocart_message);
	else
		$content = JText::_('ADD_TO_CART');
	echo $this->loadHkLayout('button', array( 'attributes' => $attributes, 'content' => $content, 'fallback_url' => hikashop_completeLink($classical_url)));
}
?>
<!-- EO ADD TO CART BUTTON -->
<!-- WISHLIST BUTTON -->
<?php
if($add_to_wishlist) {
	$attributes = ' class="' . $css_button . ' ' . $css_button_wishlist. '" onclick="if(window.hikashop.addToWishlist) { return window.hikashop.addToWishlist(this); }" data-addToWishlist="'.$this->row->product_id.'" data-addTo-div="hikashop_product_form" data-addTo-class="add_in_progress"';
	if(!empty($this->last_quantity_field_id))
		$attributes .= ' id="'.$this->last_quantity_field_id.'_add_to_wishlist_button"';
	if(!empty($extra_div_name)){
		$attributes .= ' data-addTo-extra="' . $extra_div_name. '"';
	}
	if(!empty($this->row->product_addtowishlist_message))
		$content = JText::_($this->row->product_addtowishlist_message);
	else
		$content = JText::_('ADD_TO_WISHLIST');
	echo $this->loadHkLayout('button', array( 'attributes' => $attributes, 'content' => $content, 'fallback_url' => hikashop_completeLink($classical_url.'&cart_type=wishlist')));
}
?>
<!-- EO WISHLIST BUTTON -->
