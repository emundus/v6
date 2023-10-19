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
$height = $this->newSizes->height;
$width = $this->newSizes->width;
$this->type = '';
$mainDivName = $this->params->get('main_div_name', '');
$duration = round(((int)$this->params->get('product_effect_duration', 400)) / 1000, 3);

$paneHeightCss = ($this->params->get('pane_height') != '') ? ('height: ' . (int)$this->params->get('pane_height') . 'px') : '';

$transitions = array(
 	'bounce' => 'ease-out',
	'linear' => 'linear',
	'elastic' => 'cubic-bezier(1,0,0,1)',
	'sin' => 'cubic-bezier(.45,.05,.55,.95)',
	'quad' => 'cubic-bezier(.46,.03,.52,.96)',
	'expo' => 'cubic-bezier(.19,1,.22,1)',
	'back' => 'cubic-bezier(.18,.89,.32,1.28)'
);

$transition_mode = $this->params->get('product_transition_effect', 'bounce');
if(!isset($transitions[$transition_mode]))
	$transition_mode = 'bounce';
$productTransition = $transitions[$transition_mode];

$link = hikashop_contentLink('product&task=show&cid=' . (int)$this->row->product_id . '&name=' . $this->row->alias . $this->itemid . $this->category_pathway, $this->row);
$this->haveLink = (int)$this->params->get('link_to_product_page', 1);

$htmlLink = "";
$cursor = "";
if($this->haveLink && !$this->params->get('add_to_cart') && !$this->params->get('add_to_wishlist')) {
	$htmlLink = ' onclick="window.location.href=\''.$link.'\'';
	$cursor = "cursor:pointer;";
}
$hk_main_classes = array('hikashop_horizontal_slider');
if(!empty($this->row->categories)) {
	foreach($this->row->categories as $category) {
		$hk_main_classes[] = 'hikashop_product_of_category_'.$category->category_id;
	}
}

if(!empty($this->row->extraData->top)) { echo implode("\r\n",$this->row->extraData->top); }

?>
<div class="<?php echo implode(' ', $hk_main_classes); ?>" id="window_<?php echo $mainDivName; ?>_<?php echo $this->row->product_id;  ?>"<?php echo $htmlLink; ?>>
 	<div class="hikashop_horizontal_slider_subdiv">
		<table cellspacing="0" cellpadding="0">
			<tr>
			<td valign="top">
				<!-- PRODUCT IMG -->
				<div class="hikashop_product_image">
					<div class="hikashop_product_image_subdiv">
<?php if($this->haveLink) { ?>
						<a href="<?php echo $link;?>" title="<?php echo $this->escape($this->row->product_name); ?>">
<?php } ?>
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
		echo $html;
?>		<meta itemprop="image" content="<?php echo $img->url; ?>"/>
<?php
	}

	if($this->params->get('display_badges', 1)) {
		$this->classbadge->placeBadges($this->image, $this->row->badges, array('vertical' => -10, 'horizontal' => 0, 'thumbnail' => $img));
	}
?>
<?php if($this->haveLink) { ?>
						</a>
<?php } ?>
					</div>
				</div>
				<!-- EO PRODUCT IMG -->
				<div class="hikashop_img_pane_panel">
					<!-- PRODUCT NAME -->
					<span class="hikashop_product_name">
<?php if($this->haveLink) { ?>
						<a href="<?php echo $link;?>">
<?php } ?>
							<?php echo $this->row->product_name; ?>
<?php if($this->haveLink) { ?>
						</a>
<?php } ?>
					</span>
					<meta itemprop="name" content="<?php echo $this->escape(strip_tags($this->row->product_name)); ?>">
					<!-- EO PRODUCT NAME -->
					<!-- PRODUCT CODE -->
					<span class='hikashop_product_code_list'>
<?php if($this->config->get('show_code')) { ?>
<?php if($this->haveLink) { ?>
						<a href="<?php echo $link;?>">
<?php } ?>
							<?php echo $this->row->product_code; ?>
<?php if($this->haveLink) { ?>
						</a>
<?php } ?>
<?php } ?>
					</span>
					<!-- EO PRODUCT CODE -->

					<!-- PRODUCT PRICE -->
<?php
if($this->params->get('show_price', -1) == -1) {
	$this->params->set('show_price', $this->config->get('show_price'));
}
if($this->params->get('show_price')) {
	$this->setLayout('listing_price');
	echo $this->loadTemplate();
}
?>
					<!-- EO PRODUCT PRICE -->
				</div>
			</td>
			<td valign="top" height="<?php echo $height; ?>" width="<?php echo $width; ?>" >
				<!-- PRODUCT NAME -->
				<span class="hikashop_product_name">
<?php if($this->haveLink) { ?>
					<a href="<?php echo $link;?>">
<?php } ?>
						<?php echo $this->row->product_name; ?>
<?php if($this->haveLink) { ?>
					</a>
<?php } ?>
				</span>
				<!-- EO PRODUCT NAME -->
<?php if(!empty($this->row->extraData->afterProductName)) { echo implode("\r\n",$this->row->extraData->afterProductName); } ?>

				<!-- PRODUCT DESCRIPTION -->
<?php if($this->params->get('show_description_listing', 0)) { ?>
				<div class="hikashop_product_description"><?php
					echo preg_replace('#<hr *id="system-readmore" */>.*#is','',$this->row->product_description);
				?></div>
<?php } ?>
				<!-- EO PRODUCT DESCRIPTION -->

				<!-- PRODUCT CUSTOM FIELDS -->
<?php
if(!empty($this->productFields)) {
	foreach ($this->productFields as $fieldName => $oneExtraField) {
		if(empty($this->row->$fieldName) && (!isset($this->row->$fieldName) || $this->row->$fieldName !== '0'))
			continue;

		if(!empty($oneExtraField->field_products)) {
			$field_products = is_string($oneExtraField->field_products) ? explode(',', trim($oneExtraField->field_products, ',')) : $oneExtraField->field_products;
			if(!in_array($this->row->product_id, $field_products))
				continue;
		}
?>
				<dl class="hikashop_product_custom_<?php echo $oneExtraField->field_namekey;?>_line">
					<dt class="hikashop_product_custom_name"><?php
						echo $this->fieldsClass->getFieldName($oneExtraField);
					?></dt>
					<dd class="hikashop_product_custom_value"><?php
						echo $this->fieldsClass->show($oneExtraField,$this->row->$fieldName);
					?></dd>
				</dl>
<?php
	}
}
?>
				<!-- EO PRODUCT CUSTOM FIELDS -->

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
		$this->css_button = $this->config->get('css_button', 'hikabtn');
		$this->type = 'detail';
		$this->setLayout('show_popup');
		echo $this->loadTemplate();
	}
?>

	<!-- EO PRODUCT DETAILS BUTTON AREA -->
			</td>
			</tr>
		</table>
	</div>
	<meta itemprop="url" content="<?php echo $link; ?>">
</div>
<?php

if(!empty($this->row->extraData->bottom)) { echo implode("\r\n",$this->row->extraData->bottom); }

if($this->rows[0]->product_id == $this->row->product_id) {

	$css_transition  = 'margin-left '.number_format($duration, 1, '.', '').'s '.$productTransition;

	$css = '
#'.$mainDivName.' .hikashop_horizontal_slider_subdiv table td,
#'.$mainDivName.' .hikashop_horizontal_slider { height:'.(int)$height.'px; width:'.(int)$width.'px; }

#'.$mainDivName.' .hikashop_horizontal_slider_subdiv table { height:'.(int)$height.'px; }
#'.$mainDivName.' .hikashop_horizontal_slider_subdiv { width:'.(int)($width * 2).'px; }

#'.$mainDivName.' .hikashop_img_pane_panel { width:'.(int)$width.'px; }
#'.$mainDivName.' .hikashop_horizontal_slider_subdiv {-webkit-transition:'.$css_transition.'; -moz-transition:'.$css_transition.'; -o-transition:'.$css_transition.'; transition:'.$css_transition.'; }
#'.$mainDivName.' .hikashop_horizontal_slider_subdiv:hover { margin-left: -'.((int)$width + 1).'px; }

';
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

	hikashop_loadJslib('jquery');
?>
<script>
window.hikashop.ready(function(){
	hkjQuery('#<?php echo $mainDivName; ?> .hikashop_horizontal_slider_subdiv').on('touchstart', function () {
        hkjQuery(this).trigger('hover');
    }).on('touchend', function () {
        hkjQuery(this).trigger('hover');
    });
});
</script>
<?php
}
