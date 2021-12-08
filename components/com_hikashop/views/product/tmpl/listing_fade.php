<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.4.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2020 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
$height = $this->newSizes->height;
$width = $this->newSizes->width;
$mainDivName = $this->params->get('main_div_name', '');

$hk_duration = (int)((int)$this->params->get('product_effect_duration', 500) / 100);
if($hk_duration <= 0) $hk_duration = 10;
$hk_duration = 'hk_' . (($hk_duration < 10) ? ('0s' . $hk_duration) : ((int)($hk_duration / 10) . 's'));
$backgroundColor = $this->params->get('background_color', $this->config->get('background_color'));

$link = hikashop_contentLink('product&task=show&cid=' . (int)$this->row->product_id . '&name=' . $this->row->alias . $this->itemid . $this->category_pathway, $this->row);
$haveLink = (int)$this->params->get('link_to_product_page', 1);

$htmlLink = $haveLink ? ' onclick = "window.location.href = \''.$link.'\'"' : '';

$paneHeight = ($this->params->get('pane_height','') != '') ? ('height:' . (int)$this->params->get('pane_height') . 'px;') : '';

if(!empty($this->row->extraData->top)) { echo implode("\r\n", $this->row->extraData->top); }

$hk_main_classes = array(
	'hikashop_fade_effect',
	'hk_text_' . $this->align
);
if($haveLink) $hk_main_classes[] = 'hk_link_cursor';

$hk_picture_classes = array(
	'hk_picture',
	$hk_duration
);
?>
<div class="<?php echo implode(' ', $hk_main_classes); ?>" id="div_<?php echo $mainDivName.'_'.$this->row->product_id; ?>"<?php echo $htmlLink; ?>">
	<div class="<?php echo implode(' ', $hk_picture_classes); ?>">
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
		$html = '<img class="hikashop_product_listing_image" title="'.$this->escape(@$this->row->file_description).'" alt="'.$this->escape(@$this->row->file_name).'" src="'.$img->url.'"/>';
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
?>		<meta itemprop="image" content=<?php echo $img->url; ?>/>
<?php
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

		<div class="hikashop_img_fade_panel">
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
			<meta itemprop="name" content="<?php echo $this->escape(strip_tags($this->row->product_name)); ?>">
			<!-- EO PRODUCT NAME -->

			<!-- PRODUCT CODE -->
			<span class='hikashop_product_code_list'>
<?php if($this->config->get('show_code')) { ?>
<?php if($haveLink) { ?>
				<a href="<?php echo $link;?>">
<?php } ?>
					<?php echo $this->row->product_code; ?>
<?php if($haveLink) { ?>
				</a>
<?php } ?>
<?php } ?>
			</span>
			<!-- EO PRODUCT CODE -->

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
		</div>
	</div>

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
if($this->params->get('show_vote')) {
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
if(hikaInput::get()->getVar('hikashop_front_end_main', 0) && hikaInput::get()->getVar('task') == 'listing' && $this->params->get('show_compare')) { ?>
	<br/><?php
	if($this->params->get('show_compare') == 1) {
		$js = 'setToCompareList('.(int)$this->row->product_id.',\''.$this->escape(str_replace("'","\'",$this->row->product_name)).'\',this); return false;';

		echo $this->cart->displayButton(JText::_('ADD_TO_COMPARE_LIST'), 'compare', $this->params, $link, $js, '', 0, 1, 'hikashop_compare_button');
	 } else {
?>
	<input type="checkbox" class="hikashop_compare_checkbox" id="hikashop_listing_chk_<?php echo $this->row->product_id;?>" onchange="setToCompareList(<?php echo $this->row->product_id;?>,'<?php echo $this->escape(str_replace("'","\'",$this->row->product_name)); ?>',this);"><label for="hikashop_listing_chk_<?php echo $this->row->product_id;?>"><?php echo JText::_('ADD_TO_COMPARE_LIST'); ?></label>
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
?>
	<a href="<?php echo hikashop_completeLink('product&task=contact&cid=' . (int)$this->row->product_id . $this->itemid); ?>" class="<?php echo $css_button; ?>"><?php
		echo JText::_('CONTACT_US_FOR_INFO');
	?></a>
<?php
	}
?>

	<!-- EO CONTACT US AREA -->

	<!-- PRODUCT DETAILS BUTTON AREA -->
<?php
	$details_button = (int)$this->params->get('details_button', 0);
	if($details_button) {
		$css_button = $this->config->get('css_button', 'hikabtn');
?>
	<a href="<?php echo $link; ?>" class="<?php echo $css_button; ?>"><?php
		echo JText::_('PRODUCT_DETAILS');
	?></a>
<?php
	}
?>

	<!-- EO PRODUCT DETAILS BUTTON AREA -->

	<meta itemprop="url" content="<?php echo $link; ?>">
</div>
<?php
if(!empty($this->row->extraData->bottom)) { echo implode("\r\n", $this->row->extraData->bottom); }

if($this->rows[0]->product_id == $this->row->product_id) {
	$css = '
#'.$mainDivName.' .hikashop_fade_effect,
#'.$mainDivName.' .hikashop_fade_effect_picture { height:'.(int)$height.'px; width:'.(int)$width.'px; }
#'.$mainDivName.' .hikashop_fade_effect_picture { background-color:'.$backgroundColor.'; }
#'.$mainDivName.' .hikashop_img_pane_panel { width:'.(int)$width.'px; '.$paneHeight.'; }
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
}
