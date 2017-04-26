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
$height = $this->image->main_thumbnail_y;
$width = $this->image->main_thumbnail_x;
$mainDivName = $this->params->get('main_div_name', '');

$link = hikashop_contentLink('product&task=show&cid=' . (int)$this->row->product_id . '&name=' . $this->row->alias . $this->itemid . $this->category_pathway, $this->row);
$haveLink = (int)$this->params->get('link_to_product_page', 1);

$paneHeight = ($this->params->get('pane_height','') != '') ? ('height:' . (int)$this->params->get('pane_height') . 'px;') : '';
$htmlLink = $haveLink ? ' onclick = "window.location.href = \''.$link.'\'"' : '';

$hk_main_classes = array(
	'hk_img_pane_window',
	'hk_text_' . $this->align
);
if($haveLink) $hk_main_classes[] = 'hk_link_cursor';

if(!empty($this->row->extraData->top)) { echo implode("\r\n",$this->row->extraData->top); }

?>
<div class="<?php echo implode(' ', $hk_main_classes); ?>" id="div_<?php echo $mainDivName.'_'.$this->row->product_id; ?>"<?php echo $htmlLink; ?>>
 	<div class="hk_img_pane_product">
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
		$this->classbadge->placeBadges($this->image, $this->row->badges, -10, 0);
	}
?>
<?php if($haveLink) { ?>
				</a>
<?php } ?>
			</div>
		</div>
		<!-- EO PRODUCT IMG -->
		<div class="hikashop_img_pane_panel">
			<!-- PRODUCT NAME -->
			<span class="hikashop_product_name">
<?php if($haveLink) { ?>
				<a href="<?php echo $link;?>">
<?php } ?>
					<?php echo $this->row->product_name; ?>
<?php if($haveLink) { ?>
				</a>
<?php } ?>
			</span>
			<!-- EO PRODUCT NAME -->
			<!-- PRODUCT CODE -->
			<span class='hikashop_product_code_list'>
<?php if($this->config->get('show_code')) { ?>
<?php if($haveLink) { ?>
				<a href="<?php echo $link; ?>">
<?php } ?>
					<?php echo $this->row->product_code; ?>
<?php if($haveLink) { ?>
				</a>
<?php } ?>
<?php } ?>
			</span>
			<!-- EO PRODUCT CODE -->
<?php if(!empty($this->row->extraData->afterProductName)) { echo implode("\r\n",$this->row->extraData->afterProductName); } ?>
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
	$this->setLayout('add_to_cart_listing');
	echo $this->loadTemplate();
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
	</div>
</div>
<?php

if(!empty($this->row->extraData->bottom)) { echo implode("\r\n",$this->row->extraData->bottom); }

if($this->rows[0]->product_id == $this->row->product_id) {

	$css = '
#'.$mainDivName.' .hk_img_pane_window,
#'.$mainDivName.' .hk_img_pane_product { height:'.(int)$height.'>px; width:'.(int)$width.'px; }
#'.$mainDivName.' .hikashop_img_pane_panel,
#'.$mainDivName.' .hikashop_product_image_subdiv { width:'.(int)$width.'px; }
#'.$mainDivName.' .hikashop_img_pane_panel { '.$paneHeight.' }
';
	if((int)$height>0){
		$css .= '
#'.$mainDivName.' .hikashop_product_image { height:'.(int)$height.'px; }';
	}
	$doc = JFactory::getDocument();
	$doc->addStyleDeclaration($css);
}
