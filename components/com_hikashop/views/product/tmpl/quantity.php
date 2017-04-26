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
if($this->config->get('add_to_cart_legacy', true)) {
	$this->setLayout('quantity_legacy');
	echo $this->loadTemplate();
	return;
}

$catalogue_mode = $this->config->get('catalogue', false);
$enable_wishlist = (hikashop_level(1) && $this->config->get('enable_wishlist', 1) && $this->params->get('add_to_wishlist', 0) && (!$this->config->get('hide_wishlist_guest', 1) || hikashop_loadUser() != null));
if($catalogue_mode && !$enable_wishlist)
	return;

$in_stock = ((int)$this->row->product_quantity == -1 || (int)$this->row->product_quantity > 0);
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

if($this->global_on_listing && !empty($this->row->formName)) {
	if($add_to_cart) {
?>
	<a class="<?php echo $css_button . ' ' . $css_button_cart; ?>" href="#" onclick="if(window.hikashop.addToCart) { return window.hikashop.addToCart(this); }" data-addTo-div="<?php echo $this->row->formName; ?>" data-addTo-class="add_in_progress"><span><?php
		echo JText::_('ADD_TO_CART');
	?></span></a>
<?php
	}
	if($add_to_wishlist) {
?>
	<a class="<?php echo $css_button . ' ' . $css_button_wishlist; ?>" href="#" onclick="if(window.hikashop.addToWishlist) { document.getElementById('hikashop_cart_type_0').value = 'wishlist'; return window.hikashop.addToWishlist(this); }" data-addTo-div="<?php echo $this->row->formName; ?>" data-addTo-class="add_in_progress"><span><?php
		echo JText::_('ADD_TO_WISHLIST');
	?></span></a>
<?php
	}
	return;
}

$this->global_on_listing = false;
$classical_url = 'product&task=updatecart&add=1&product_id='.$this->row->product_id;
if(!empty($this->return_url))
	$classical_url .= '&return_url=' . urlencode(base64_encode(urldecode($this->redirect_url)));


if($end_date > 0 && $end_date < $now) {
?>
	<span class="hikashop_product_sale_end"><?php
		echo JText::_('ITEM_NOT_SOLD_ANYMORE');
	?></span>
<?php
	return;
}

if($start_date > 0 && $start_date > $now) {
?>
	<span class="hikashop_product_sale_start"><?php
		echo JText::sprintf('ITEM_SOLD_ON_DATE', hikashop_getDate($start_date, $this->params->get('date_format', '%d %B %Y')));
	?></span>
<?php
	return;
}

?>
<span class="hikashop_product_stock_count">
<?php
	if($this->row->product_quantity > 0)
		echo (($this->row->product_quantity == 1 && JText::_('X_ITEM_IN_STOCK') != 'X_ITEM_IN_STOCK') ? JText::sprintf('X_ITEM_IN_STOCK', $this->row->product_quantity) : JText::sprintf('X_ITEMS_IN_STOCK', $this->row->product_quantity));
	elseif(!$in_stock)
		echo JText::_('NO_STOCK');
?>
</span>
<?php

if($waitlist_btn) {
?>
	<a class="<?php echo $css_button; ?>" rel="nofollow" href="<?php echo hikashop_completeLink('product&task=waitlist&cid='.$this->row->product_id); ?>"><span><?php
		echo JText::_('ADD_ME_WAITLIST');
	?></span></a>
<?php
}

if(($add_to_cart || $add_to_wishlist) && $display_quantity_field) {
	$this->setLayout('show_quantity');
	echo $this->loadTemplate();
}

if($add_to_cart) {
?>
	<a class="<?php echo $css_button . ' ' . $css_button_cart; ?>" rel="nofollow" href="<?php echo hikashop_completeLink($classical_url); ?>" onclick="if(window.hikashop.addToCart) { return window.hikashop.addToCart(this); }" data-addToCart="<?php echo $this->row->product_id; ?>" data-addTo-div="hikashop_product_form" data-addTo-class="add_in_progress"><span><?php
		echo JText::_('ADD_TO_CART');
	?></span></a>
<?php
}

if($add_to_wishlist) {
?>
	<a class="<?php echo $css_button . ' ' . $css_button_wishlist; ?>" rel="nofollow" href="<?php echo hikashop_completeLink($classical_url); ?>" onclick="if(window.hikashop.addToWishlist) { return window.hikashop.addToWishlist(this); }" data-addToWishlist="<?php echo $this->row->product_id; ?>" data-addTo-div="hikashop_product_form" data-addTo-class="add_in_progress"><span><?php
		echo JText::_('ADD_TO_WISHLIST');
	?></span></a>
<?php
}
