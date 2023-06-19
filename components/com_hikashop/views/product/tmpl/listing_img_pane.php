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
$height = $this->image->main_thumbnail_y;
$width = $this->image->main_thumbnail_x;
$mainDivName = $this->params->get('main_div_name', '');

$link = hikashop_contentLink('product&task=show&cid=' . (int)$this->row->product_id . '&name=' . $this->row->alias . $this->itemid . $this->category_pathway, $this->row);
$this->haveLink = (int)$this->params->get('link_to_product_page', 1);

$paneHeight = ($this->params->get('pane_height','') != '') ? ('height:' . (int)$this->params->get('pane_height') . 'px;') : '';
$htmlLink = $this->haveLink ? ' onclick = "window.location.href = \''.$link.'\'"' : '';

$hk_main_classes = array(
	'hk_img_pane_window',
	'hk_text_' . $this->align
);
if($this->haveLink) $hk_main_classes[] = 'hk_link_cursor';

if(!empty($this->row->extraData->top)) { echo implode("\r\n",$this->row->extraData->top); }

?>
<div class="<?php echo implode(' ', $hk_main_classes); ?>" id="div_<?php echo $mainDivName.'_'.$this->row->product_id; ?>">
 	<div class="hk_img_pane_product">
		<!-- PRODUCT IMG -->
		<div class="hikashop_product_image"<?php echo $htmlLink; ?>>
			<div class="hikashop_product_image_subdiv">
<?php
	$img = $this->image->getThumbnail(
		@$this->row->file_path,
		array('width' => $this->image->main_thumbnail_x, 'height' => $this->image->main_thumbnail_y),
		array('default' => true, 'forcesize' => $this->config->get('image_force_size', true), 'scale' => $this->config->get('image_scale_mode', 'inside'))
	);
	if($img->success) {
		$html = '<img class="hikashop_product_listing_image" title="'.$this->escape((string)@$this->row->file_description).'" alt="'.$this->escape((string)@$this->row->file_name).'" src="'.$img->url.'"/>';
		if($this->config->get('add_webp_images', 1) && function_exists('imagewebp') && !empty($img->webpurl)) {
			$html = '
			<picture>
				<source srcset="'.$img->webpurl.'" type="image/webp">
				<source srcset="'.$img->url.'" type="image/'.$img->ext.'">
				'.$html.'
			</picture>
			';
		}
		$this->link_content = $html;
		$this->setLayout('show_popup');
		echo $this->loadTemplate();
?>		<meta itemprop="image" content="<?php echo $img->url; ?>"/>
<?php
	}

	if($this->params->get('display_badges', 1)) {
		$this->classbadge->placeBadges($this->image, $this->row->badges, array('vertical' => -10, 'horizontal' => 0, 'thumbnail' => $img));
	}
?>
			</div>
		</div>
		<!-- EO PRODUCT IMG -->
		<div class="hikashop_img_pane_panel">
			<!-- PRODUCT NAME -->
			<span class="hikashop_product_name">
<?php
		$this->link_content = $this->row->product_name;
		$this->setLayout('show_popup');
		echo $this->loadTemplate();
?>
			</span>
			<meta itemprop="name" content="<?php echo $this->escape(strip_tags($this->row->product_name)); ?>">
			<!-- EO PRODUCT NAME -->
			<!-- PRODUCT CODE -->
			<span class='hikashop_product_code_list'>
<?php if ($this->config->get('show_code')) {
		$this->link_content = $this->row->product_code;
		$this->setLayout('show_popup');
		echo $this->loadTemplate();
} ?>
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
if($this->params->get('show_vote')) {
	$this->setLayout('listing_vote');
	echo $this->loadTemplate();
}
?>
			<!-- EO PRODUCT VOTE -->

			<!-- CHARACTERISTIC AVAILABLE VALUES -->
<?php
if(!empty($this->row->characteristics)) {
	foreach($this->row->characteristics as $characteristic) {
		if(!empty($characteristic->availableValues)) {
?>
	<div class="hikashop_product_characteristic_on_listing hikashop_product_characteristic_on_listing_<?php echo $characteristic->characteristic_id; ?>">
		<div class="hikashop_product_characteristic_name_on_listing"><?php echo $characteristic->characteristic_value; ?></div>
		<div class="hikashop_product_characteristic_values_on_listing">
<?php
			foreach($characteristic->availableValues as $value) {
?>
			<span class="hikashop_product_characteristic_value_on_listing hikashop_product_characteristic_value_on_listing_<?php echo $value->characteristic_id; ?>">
				<?php echo $value->characteristic_value; ?>
			</span>
<?php
			}
?>
		</div>
	</div>
<?php		
		}
	}
}
?>
			<!-- EO CHARACTERISTIC AVAILABLE VALUES -->

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
if(hikaInput::get()->getVar('hikashop_front_end_main', 0) && hikaInput::get()->getVar('task') == 'listing' && $this->params->get('show_compare')) {
	$css_button = $this->config->get('css_button', 'hikabtn');
	$css_button_compare = $this->config->get('css_button_compare', 'hikabtn-compare');
?>
	<br/>
<?php
	if((int)$this->params->get('show_compare') == 1) {
		$onclick = ' onclick="if(window.hikashop.addToCompare) { return window.hikashop.addToCompare(this); }" '.
			'data-addToCompare="'.$this->row->product_id.'" '. 
			'data-product-name="'.$this->escape($this->row->product_name).'" '.
			'data-addTo-class="hika-compare"';
		$attributes = 'class="'.$css_button . ' ' . $css_button_compare.'" '.$onclick;
		$fallback_url = $link;
		$content = JText::_('ADD_TO_COMPARE_LIST');

		echo $this->loadHkLayout('button', array( 'attributes' => $attributes, 'content' => $content, 'fallback_url' => $fallback_url));
	} else {
?>
	<label><input type="checkbox" class="hikashop_compare_checkbox" onchange="if(window.hikashop.addToCompare) { return window.hikashop.addToCompare(this); }" data-addToCompare="<?php echo $this->row->product_id; ?>" data-product-name="<?php echo $this->escape($this->row->product_name); ?>" data-addTo-class="hika-compare"><?php echo JText::_('ADD_TO_COMPARE_LIST'); ?></label>
<?php
	}
}
?>
			<!-- EO COMPARISON AREA -->

			<!-- CONTACT US AREA -->
<?php
	$contact = (int)$this->config->get('product_contact', 0);
	if(hikashop_level(1) && $this->params->get('product_contact_button', 0) && ($contact == 2 || ($contact == 1 && !empty($this->row->product_contact)))) {
		$css_button = $this->config->get('css_button', 'hikabtn');
		$attributes = 'class="'.$css_button.'"';
		$fallback_url = hikashop_completeLink('product&task=contact&cid=' . (int)$this->row->product_id . $this->itemid);
		$content = JText::_('CONTACT_US_FOR_INFO');

		echo $this->loadHkLayout('button', array( 'attributes' => $attributes, 'content' => $content, 'fallback_url' => $fallback_url));
	}
?>

			<!-- EO CONTACT US AREA -->

			<!-- PRODUCT DETAILS BUTTON AREA -->
<?php
	$details_button = (int)$this->params->get('details_button', 0);
	if($details_button) {
		$this->link_content = JText::_('PRODUCT_DETAILS');
		$this->type = 'detail';
		$this->css_button = $this->config->get('css_button', 'hikabtn');
		$this->setLayout('show_popup');
		echo $this->loadTemplate();
	}
?>

			<!-- EO PRODUCT DETAILS BUTTON AREA -->
		</div>
	</div>
	<meta itemprop="url" content="<?php echo $link; ?>">
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
