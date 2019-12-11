<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.2.2
 * @author	hikashop.com
 * @copyright	(C) 2010-2019 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php

$enable_cart = !$this->config->get('catalogue', false) && $this->params->get('add_to_cart');
$user = JFactory::getUser();
$enable_wishlist = (hikashop_level(1) && $this->config->get('enable_wishlist', 1) && $this->params->get('add_to_wishlist', 0) && (!$this->config->get('hide_wishlist_guest', 1) || empty($user->guest)));
if(!$enable_cart && !$enable_wishlist)
	return;

$classical_url = 'product&task=updatecart&add=1&product_id='.$this->row->product_id;
if(!empty($this->return_url))
	$classical_url .= '&return_url=' . urlencode(base64_encode(urldecode($this->redirect_url)));

$in_stock = (((int)$this->row->product_quantity == -1 && (empty($this->element->main) || $this->element->main->product_quantity == -1 || $this->element->main->product_quantity > 0)) || (int)$this->row->product_quantity > 0);
$has_fields = !empty($this->row->itemFields);
$has_required_fields = !empty($this->row->has_required_item_field);
$is_free = empty($this->row->prices);
$display_free_cart = (int)$this->config->get('display_add_to_cart_for_free_products', 0);
$display_free_wishlist = (int)$this->config->get('display_add_to_wishlist_for_free_products', 1);
$display_fields = (int)$this->params->get('display_custom_item_fields', 0);
$display_quantity_field = (int)$this->params->get('show_quantity_field', 0);
$global_on_listing = $this->config->get('show_quantity_field') == 2;
if($global_on_listing){
	$this->row->quantityFieldName = 'data['.$this->row->product_id.']';
	$display_quantity_field = true;
}

$display_waitlist = (int)$this->params->get('product_waitlist', 0);
$waitlist = (int)$this->config->get('product_waitlist', 0) && $display_waitlist;
$waitlist_btn = !$in_stock && (hikashop_level(1) && ($waitlist == 2 || ($waitlist == 1 && (!empty($this->element->main->product_waitlist) || !empty($this->row->product_waitlist)))));


$has_options = !empty($this->row->has_options) || (!$display_fields && $has_required_fields);

$css_button = $this->config->get('css_button', 'hikabtn');
$css_button_cart = $this->config->get('css_button_cart', 'hikacart');
$css_button_wishlist = $this->config->get('css_button_wishlist', 'hikawishlist');

$now = time();
$start_date = (int)(@$this->row->product_sale_start || empty($this->element->main)) ? @$this->row->product_sale_start : $this->element->main->product_sale_start;
$end_date = (int)(@$this->row->product_sale_end || empty($this->element->main)) ? @$this->row->product_sale_end : $this->element->main->product_sale_end;

$product_available = ($end_date <= 0 || $end_date >= $now) && ($start_date <= 0 || $start_date < $now);


$add_to_cart = $enable_cart && $in_stock && (!$is_free || $display_free_cart) && $product_available;
$add_to_wishlist = $enable_wishlist && (!$is_free || $display_free_wishlist);
$extra_div_name = $this->params->get('extra_div_name', '');

if($end_date > 0 && $end_date < $now) {
?>
<span class="hikashop_product_sale_end"><?php
	echo JText::_('ITEM_NOT_SOLD_ANYMORE');
?></span>
<?php
}

if($start_date > 0 && $start_date > $now) {
?>
<span class="hikashop_product_sale_start"><?php
	echo JText::sprintf('ITEM_SOLD_ON_DATE', hikashop_getDate($start_date, $this->params->get('date_format', '%d %B %Y')));
?></span>
<?php
}
?>
<span class="hikashop_product_stock_count">
<?php
	if(!empty($this->row->product_stock_message))
		echo JText::_($this->row->product_stock_message);
	elseif($this->row->product_quantity > 0)
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

if(($add_to_cart || $add_to_wishlist) && (($has_fields && $display_fields) || $display_quantity_field) && !$has_options && !$global_on_listing) {
?>
	<form action="<?php echo hikashop_completeLink($classical_url); ?>" method="post" name="hikashop_product_form_<?php echo $this->row->product_id.'_'.$this->params->get('main_div_name'); ?>" enctype="multipart/form-data">
<?php
}

if(($add_to_cart || $add_to_wishlist) && (($has_fields && $display_fields) || $display_quantity_field) && !$has_options) {
	if($has_fields && $display_fields && !$global_on_listing) {
		?><dl>
<?php
		foreach($this->row->itemFields as $fieldName => $oneExtraField) {
			$itemData = hikaInput::get()->getString('item_data_'.$fieldName, @$this->row->$fieldName);

			$onWhat = 'onchange';
			if($oneExtraField->field_type == 'radio')
				$onWhat = 'onclick';

			$oneExtraField->product_id = $this->row->product_id;
			$this->fieldsClass->prefix = 'product_'.$this->row->product_id.'_';
?>
			<dt><?php echo $this->fieldsClass->getFieldName($oneExtraField); ?></dt>
			<dd><?php
				echo $this->fieldsClass->display(
					$oneExtraField,
					$itemData,
					'data[item]['.$oneExtraField->field_namekey.']',
					false,
					' '.$onWhat.'="if(window.hikashop.toggleField) { window.hikashop.toggleField(this.value, \''.$fieldName.'\',\'item\',0); }"'
				);
			?></dd>
<?php
		}
		?></dl>
<?php
	}

	if($display_quantity_field && (!$global_on_listing || !$has_required_fields )) {
		$this->setLayout('show_quantity');
		echo $this->loadTemplate();
	}
}

if($add_to_cart && !$has_options && !$global_on_listing) {
?>
	<a class="<?php echo $css_button . ' ' . $css_button_cart; ?>" rel="nofollow" href="<?php echo hikashop_completeLink($classical_url); ?>" onclick="if(window.hikashop.addToCart) { return window.hikashop.addToCart(this); }" data-addToCart="<?php echo $this->row->product_id; ?>" data-addTo-div="<?php echo $this->params->get('main_div_name'); ?>"<?php if(!empty($extra_div_name)){ echo ' data-addTo-extra="' . $extra_div_name. '"'; } ?> data-addTo-class="add_in_progress"><span><?php
		if(!empty($this->row->product_addtocart_message))
			echo JText::_($this->row->product_addtocart_message);
		else
			echo JText::_('ADD_TO_CART');
	?></span></a>
<?php
}

if($add_to_wishlist && !$has_options && !$global_on_listing) {
?>
	<a class="<?php echo $css_button . ' ' . $css_button_wishlist; ?>" rel="nofollow" href="<?php echo hikashop_completeLink($classical_url.'&cart_type=wishlist'); ?>" onclick="if(window.hikashop.addToWishlist) { return window.hikashop.addToWishlist(this); }" data-addToWishlist="<?php echo $this->row->product_id; ?>" data-addTo-div="<?php echo $this->params->get('main_div_name'); ?>"<?php if(!empty($extra_div_name)){ echo ' data-addTo-extra="' . $extra_div_name. '"'; } ?> data-addTo-class="add_in_progress"><span><?php
		if(!empty($this->row->product_addtowishlist_message))
			echo JText::_($this->row->product_addtowishlist_message);
		else
			echo JText::_('ADD_TO_WISHLIST');
	?></span></a>
<?php
}

if(($add_to_cart || $add_to_wishlist) && $has_options) {
	$product_link = hikashop_contentLink('product&task=show&cid='.$this->row->product_id.'&name='.$this->row->alias . $this->itemid . $this->category_pathway, $this->row);
?>
	<a class="<?php echo $css_button . ' ' . $css_button_cart; ?>" href="<?php echo $product_link; ?>"><span><?php
		if(!empty($this->row->product_chooseoptions_message))
			echo JText::_($this->row->product_chooseoptions_message);
		else
			echo JText::_('CHOOSE_OPTIONS');
	?></span></a>
<?php
}

if(($add_to_cart || $add_to_wishlist) && (($has_fields && $display_fields) || $display_quantity_field) && !$has_options && !$global_on_listing) {
?>
	</form>
<?php
}
