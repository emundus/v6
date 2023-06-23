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
$mainDivName = $this->params->get('main_div_name', '');

$link = hikashop_contentLink('product&task=show&cid=' . (int)$this->row->product_id . '&name=' . $this->row->alias . $this->itemid . $this->category_pathway, $this->row);
$this->haveLink = (int)$this->params->get('link_to_product_page', 1);

if(!empty($this->row->extraData->top)) { echo implode("\r\n",$this->row->extraData->top); }

?>
<div class="hikashop_listing_fade2" id="div_<?php echo $mainDivName.'_'.$this->row->product_id;  ?>">
	<!-- PRODUCT IMG -->
	<div class="hikashop_product_image">
		<div class="hikashop_product_image_subdiv">
<?php
	$img = $this->image->getThumbnail(
		@$this->row->file_path,
		array('width' => $this->image->main_thumbnail_x, 'height' => $this->image->main_thumbnail_y),
		array('default' => true,'forcesize'=>$this->config->get('image_force_size',true),'scale'=>$this->config->get('image_scale_mode','inside'))
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
	<div class="hikashop_fade_data" style="position: relative;">
	<!-- PRODUCT PRICE -->
<?php
	if($this->params->get('show_price','-1')=='-1'){
		$config =& hikashop_config();
		$this->params->set('show_price',$config->get('show_price'));
	}
	if($this->params->get('show_price')){
		$this->setLayout('listing_price');
		echo $this->loadTemplate();
	}
?>
	<!-- EO PRODUCT PRICE -->

	<!-- PRODUCT NAME -->
	<span class="hikashop_product_name" style="margin:0px;">
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
<?php
if ($this->config->get('show_code')) {
	$this->link_content = $this->row->product_code;
	$this->setLayout('show_popup');
	echo $this->loadTemplate();
}
?>	</span>
	<!-- EO PRODUCT CODE -->

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
	<dl class="hikashop_product_custom_<?php echo $oneExtraField->field_namekey;?>_line" style="margin:0px;">
		<dt class="hikashop_product_custom_name" style="display:inline-block;">
			<?php echo $this->fieldsClass->getFieldName($oneExtraField);?>
		</dt>
		<dd class="hikashop_product_custom_value" style="display:inline-block;">
			<?php echo $this->fieldsClass->show($oneExtraField,$this->row->$fieldName); ?>
		</dd>
	</dl>
<?php
	}
}
?>
	<!-- EO PRODUCT CUSTOM FIELDS -->

<?php if(!empty($this->row->extraData->afterProductName)) { echo implode("\r\n",$this->row->extraData->afterProductName); } ?>

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
	$css_button = $this->config->get('css_button', 'hikabtn');
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
</div>
	<!-- PRODUCT DETAILS BUTTON AREA -->

	<meta itemprop="url" content="<?php echo $link; ?>">
</div>
<?php
	$details_button = (int)$this->params->get('details_button', 0);
	if($details_button) {
		$this->css_button = $this->config->get('css_button', 'hikabtn');
	}
	$height_calculate = 20;
	if($details_button) { 
		$height_calculate = 60;
?>
<div class="hikashop_readmore_container" style="position:relative;">
<?php
	$this->link_content = JText::_('PRODUCT_DETAILS');
	$this->type = 'detail';
	$this->css_button = $this->config->get('css_button', 'hikabtn');
	$this->setLayout('show_popup');
	echo $this->loadTemplate();
?>	
</div>
<?php } ?>
<?php
static $jsDone = false;
if(!$jsDone) {
	$jsDone = true;
?>

<script type="text/javascript">
function fadeHeight() {
	var mainDivs = document.querySelectorAll('.hikashop_listing_fade2');
	var maxHeight = 0;
	for(var i = 0; i< mainDivs.length; i++) {
		var Img = mainDivs[i].querySelector('.hikashop_product_image');
		var Data = mainDivs[i].querySelector('.hikashop_fade_data');
		Img.style.height = "auto";
		Data.style.height = "auto";
	}
	for(var i = 0; i< mainDivs.length; i++) {
		var Img = mainDivs[i].querySelector('.hikashop_product_image');
		var Data = mainDivs[i].querySelector('.hikashop_fade_data');
		var readmore = mainDivs[i].parentNode.querySelector('.hikashop_readmore_container');
		var ImgHeight = Img.offsetHeight;
		var DataHeight = Data.offsetHeight;

		if(ImgHeight > DataHeight)
			var currHeight = ImgHeight;
		else
			var currHeight = DataHeight;

		if (maxHeight == 0)
			maxHeight = parseInt(currHeight);
		else
			if (currHeight > maxHeight)
				maxHeight = parseInt(currHeight);
	}
	var readCss = 20;	
<?php 
	if($details_button) { 
?>		readCss = 60;
<?php
	} 
?>
	for(var i = 0; i< mainDivs.length; i++) {
		mainDivs[i].style.height = maxHeight + "px";
		mainDivs[i].parentNode.style.height = (readCss + maxHeight) + "px";
		mainDivs[i].parentNode.style.minHeight = (readCss + maxHeight) + "px";
		var Img = mainDivs[i].querySelector('.hikashop_product_image');
		var Data = mainDivs[i].querySelector('.hikashop_fade_data');

		Img.style.height = maxHeight + "px";
		Data.style.height = maxHeight + "px";
		Data.style.top = "-" + maxHeight + "px";
	}
}
window.hikashop.ready(function(){
	fadeHeight();
	window.Oby.registerAjax(['hkAfterProductListingSwitch'], function(params) {
		setTimeout(function(){ fadeHeight(); }, 500);
	});
});
</script>
<?php
}
?>
<?php
	$details_button = (int)$this->params->get('details_button', 0);
	if($details_button)
		$css_button = $this->config->get('css_button', 'hikabtn');
	$height_calculate = 20;
	if($details_button)
		$height_calculate = 60;
?>
	<!-- EO PRODUCT DETAILS BUTTON AREA -->
<?php
if(!empty($this->row->extraData->bottom)) { echo implode("\r\n",$this->row->extraData->bottom); }

if(isset($this->rows[0]) && $this->rows[0]->product_id == $this->row->product_id) {
	$backgroundColor = $this->params->get('background_color', $this->config->get('background_color'));
	$container_height_grid = (int)$this->image->main_thumbnail_y + $height_calculate;
	$container_height_list = (int)$this->image->main_thumbnail_y + 25;

	$css = '';
	if((int)$this->image->main_thumbnail_y>0){
		$css .= ''.
'#'.$mainDivName.' .display_list .hikashop_listing_fade2,'.		
'#'.$mainDivName.' .display_list .hikashop_listing_fade2 .hikashop_product_image { '.
	'height:'.(int)$this->image->main_thumbnail_y.'px !important;'.
'}'.
'#'.$mainDivName.' .display_list .hikashop_listing_fade2 .hikashop_fade_data {'.
	'height:'.(int)$this->image->main_thumbnail_y.'px !important;'. 
    'margin-left:'.(int)$this->image->main_thumbnail_x.'px;'.
	'position: unset !important;'.
'}'.
'#'.$mainDivName.' .hikashop_fade_data,'.
'#'.$mainDivName.' .display_grid .hikashop_fade_data {'. 
	'position: relative;'.
	'background-color: '.$backgroundColor.';'. 
'}'.
'#'.$mainDivName.' .display_list .hikashop_subcontainer  {'.
	'height:'.$container_height_list.'px !important;'.
	'padding: 5px;'. 
'}'.
'.display_grid .hikashop_listing_fade2 {'.
	'margin-bottom: 10px;'.
'}'.
'.hikashop_listing_fade2 .hikashop_product_custom_name label {'.
	'margin:0px;'.
'}'.
'.hikashop_listing_fade2 .hikashop_fade_data form, '.
'.hikashop_subcontainer .hikashop_readmore_container {'.
	'background-color: '.$backgroundColor.';'. 
'}'. 
'.display_list .hikashop_readmore_container {'.
    'height: '.(int)$this->image->main_thumbnail_y.'px;'.
    'width: '.(int)$this->image->main_thumbnail_x.'px;'.
    'position: relative;'.
    'top: -'.(int)$this->image->main_thumbnail_y.'px;'. 
'}';
	}
	if((int)$this->image->main_thumbnail_x>0){
		$css .= '
#'.$mainDivName.' .hikashop_product_image_subdiv { width:'.(int)$this->image->main_thumbnail_x.'px; }';
	}
	$doc = JFactory::getDocument();
	$doc->addStyleDeclaration($css);
}
?>
