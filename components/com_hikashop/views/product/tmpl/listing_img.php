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
$mainDivName = $this->params->get('main_div_name', '');

$link = hikashop_contentLink('product&task=show&cid=' . (int)$this->row->product_id . '&name=' . $this->row->alias . $this->itemid . $this->category_pathway, $this->row);
$haveLink = (int)$this->params->get('link_to_product_page', 1);

if(!empty($this->row->extraData->top)) { echo implode("\r\n",$this->row->extraData->top); }

?>
<div class="hikashop_listing_img" id="div_<?php echo $mainDivName . '_' . $this->row->product_id; ?>">
	<!-- PRODUCT IMG -->
	<div class="hikashop_product_image">
		<div class="hikashop_product_image_subdiv">
<?php if($haveLink) { ?>
			<a href="<?php echo $link;?>" title="<?php echo $this->escape($this->row->product_name); ?>">
<?php } ?>
<?php
	$img = $this->image->getThumbnail(
		@$this->row->file_path,
		array('width' => $this->image->main_thumbnail_x, 'height' => $this->image->main_thumbnail_y),
		array('default' => true, 'forcesize' => $this->config->get('image_force_size', true), 'scale' => $this->config->get('image_scale_mode', 'inside'))
	);
	if($img->success) {
		echo '<img class="hikashop_product_listing_image" title="'.$this->escape(@$this->row->file_description).'" alt="'.$this->escape(@$this->row->file_name).'" src="'.$img->url.'"/>';
	}

	if($this->params->get('display_badges', 1)) {
		$this->classbadge->placeBadges($this->image, $this->row->badges, 0, 0);
	}
?>
<?php if($haveLink) { ?>
			</a>
<?php } ?>
		</div>
	</div>
	<!-- EO PRODUCT IMG -->

	<!-- PRODUCT PRICE -->
<?php
if((int)$this->params->get('show_price', -1) == -1) {
	$config =& hikashop_config();
	$this->params->set('show_price', $config->get('show_price'));
}
if($this->params->get('show_price')) {
	$this->setLayout('listing_price');
	echo $this->loadTemplate();
}
?>
	<!-- EO PRODUCT PRICE -->

	<!-- PRODUCT CUSTOM FIELDS -->
<?php
if(!empty($this->productFields)) {
	foreach($this->productFields as $fieldName => $oneExtraField) {
		if(empty($this->row->$fieldName) && (!isset($this->row->$fieldName) || $this->row->$fieldName !== '0'))
			continue;

		if(!empty($oneExtraField->field_products)) {
			$field_products = is_string($oneExtraField->field_products) ? explode(',', trim($oneExtraField->field_products, ',')) : $oneExtraField->field_products;
			if(!in_array($this->row->product_id, $field_products))
				continue;
		}
?>
	<dl class="hikashop_product_custom_<?php echo $oneExtraField->field_namekey;?>_line">
		<dt class="hikashop_product_custom_name">
			<?php echo $this->fieldsClass->getFieldName($oneExtraField);?>
		</dt>
		<dd class="hikashop_product_custom_value">
			<?php echo $this->fieldsClass->show($oneExtraField,$this->row->$fieldName); ?>
		</dd>
	</dl>
<?php
	}
}
?>
	<!-- EO PRODUCT CUSTOM FIELDS -->

	<!-- PRODUCT VOTE -->
<?php
if($this->params->get('show_vote_product')) {
	$this->setLayout('listing_vote');
	echo $this->loadTemplate();
}
?>
	<!-- EO PRODUCT VOTE -->

	<!-- ADD TO CART BUTTON AREA -->
<?php
if($this->params->get('add_to_cart') || $this->params->get('add_to_wishlist')) {
?>
	<form action="<?php echo hikashop_completeLink('product&task=updatecart'); ?>" method="post" name="hikashop_product_form_<?php echo $this->row->product_id.'_'.$this->params->get('main_div_name'); ?>"><?php
	$this->ajax='';
	if(!$this->config->get('ajax_add_to_cart',0)) {
		$this->ajax = 'return hikashopModifyQuantity(\''.$this->row->product_id.'\',field,1,\'hikashop_product_form_'.$this->row->product_id.'_'.$this->params->get('main_div_name').'\',\'cart\');';
	}
	$this->setLayout('quantity');
	echo $this->loadTemplate();

	if(!empty($this->ajax) && $this->config->get('redirect_url_after_add_cart','stay_if_cart') == 'ask_user') {
?>
		<input type="hidden" name="popup" value="1"/>
<?php } ?>
		<input type="hidden" name="product_id" value="<?php echo $this->row->product_id; ?>" />
		<input type="hidden" name="add" value="1"/>
		<input type="hidden" name="ctrl" value="product"/>
		<input type="hidden" name="task" value="updatecart"/>
		<input type="hidden" name="return_url" value="<?php echo urlencode(base64_encode(urldecode($this->redirect_url))); ?>"/>
	</form>
<?php
}
?>
	<!-- EO ADD TO CART BUTTON AREA -->

	<!-- COMPARISON AREA -->
<?php
if(JRequest::getVar('hikashop_front_end_main', 0) && JRequest::getVar('task') == 'listing' && $this->params->get('show_compare')) {
	$css_button = $this->config->get('css_button', 'hikabtn');
	$css_button_compare = $this->config->get('css_button_compare', 'hikabtn-compare');
?>
	<br/>
<?php
	if((int)$this->params->get('show_compare') == 1) {
?>
	<a class="<?php echo $css_button . ' ' . $css_button_compare; ?>" href="<?php echo $link; ?>" onclick="if(window.hikashop.addToCompare) { return window.hikashop.addToCompare(this); }" data-addToCompare="<?php echo $this->row->product_id; ?>" data-product-name="<?php echo $this->escape($this->row->product_name); ?>" data-addTo-class="hika-compare"><span><?php
		echo JText::_('ADD_TO_COMPARE_LIST');
	?></span></a>
<?php
	} else {
?>
	<label><input type="checkbox" class="hikashop_compare_checkbox" onchange="if(window.hikashop.addToCompare) { return window.hikashop.addToCompare(this); }" data-addToCompare="<?php echo $this->row->product_id; ?>" data-product-name="<?php echo $this->escape($this->row->product_name); ?>" data-addTo-class="hika-compare"><?php echo JText::_('ADD_TO_COMPARE_LIST'); ?></label>
<?php
	}
}
?>
	<!-- EO COMPARISON AREA -->
</div>
<?php

if(!empty($this->row->extraData->bottom)) { echo implode("\r\n",$this->row->extraData->bottom); }

if($this->rows[0]->product_id == $this->row->product_id) {
	$css = '';
	if((int)$this->image->main_thumbnail_y>0){
		$css .= '
#'.$mainDivName.' .hikashop_product_image { height:'.(int)$this->image->main_thumbnail_y.'px; }';
	}
	if((int)$this->image->main_thumbnail_x>0){
		$css .= '
#'.$mainDivName.' .hikashop_product_image_subdiv { width:'.(int)$this->image->main_thumbnail_x.'px; }';
	}
	$doc = JFactory::getDocument();
	$doc->addStyleDeclaration($css);
}
