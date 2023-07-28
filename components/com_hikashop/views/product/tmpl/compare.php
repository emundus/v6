<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="hikashop_compare_page">
<?php
if(empty($this->elements)) {
	$app = JFactory::getApplication();
	$app->enqueueMessage(JText::_('PRODUCT_NOT_FOUND'));
?>
</div>
<?php
	return;
}
?>
<div class="toolbar hikashop_header_buttons" id="toolbar" style="float: right;">
	<table class="hikashop_no_border">
		<tr>
			<td>
				<a href="<?php echo hikashop_completeLink('user&task=cpanel'); ?>" onclick="history.go(-1); return false;">
					<span title="<?php echo JText::_('HIKA_BACK'); ?>">
						<i class="fas fa-caret-left"></i>
					</span>
					<?php echo JText::_('HIKA_BACK'); ?>
				</a>
			</td>
		</tr>
	</table>
</div>
<div style="clear:both"></div>
<table class="hikashop_compare_table">
<?php
	global $Itemid;
	$url_itemid = '';
	if(!empty($Itemid)){
		$url_itemid = '&Itemid='.$Itemid;
	}
?>
<!-- NAME -->
	<tr id="hikashop_compare_tr_name">
		<td class="hikashop_compare_title_first_column"></td>
<?php
	foreach($this->elements as $element) {
		if(!isset($element->alias))
			$element->alias = '';
		$link = hikashop_contentLink('product&task=show&cid='.$element->product_id.'&name='.$element->alias.$url_itemid,$element);
?>
		<td class="hikashop_compare_title_prod_column">
			<h2>
				<a href="<?php echo $link; ?>" title="<?php echo $this->escape($element->product_name); ?>">
					<span id="hikashop_product_<?php echo $element->product_id; ?>_name_main" class="hikashop_product_name_main"><?php echo $element->product_name; ?></span>
					<?php if ($this->config->get('show_code')) { ?><span id="hikashop_product_<?php echo $element->product_id; ?>_code_main" class="hikashop_product_code_main"><?php echo $element->product_code; ?></span><?php } ?>
				</a>
			</h2>
		</td>
<?php
	}
?>
	</tr>
<!-- EO NAME -->
<!-- IMAGE -->
	<tr id="hikashop_compare_tr_image">
		<td class="hikashop_compare_img_first_column"></td>
<?php
	foreach($this->elements as $element) {
?>
		<td class="hikashop_compare_img_prod_column">
			<div id="hikashop_product_<?php echo $element->product_id; ?>_image_main" >
				<div class="hikashop_main_image_div">
<?php
		if(!empty($element->images)) {
			$image = reset($element->images);
			if(!$this->config->get('thumbnail')) {
				echo '<img src="'.$this->image->uploadFolder_url.$image->file_path.'" alt="'.$image->file_name.'" id="hikashop_main_image" style="margin-top:10px;margin-bottom:10px;display:inline-block;vertical-align:middle" />';
			} else {
				$height = $this->config->get('thumbnail_y');
				$width = $this->config->get('thumbnail_x');
				$style='';
				if(count($element->images)>1){
					if(!empty($height)){
						$style=' style="height:'.($height+5).'px;"';
					}
				}
?>
				<div class="hikashop_product_main_image_thumb" id="hikashop_main_image_thumb_div" <?php echo $style;?> >
<?php
				$image_options = array('default' => true,'forcesize'=>$this->config->get('image_force_size',true),'scale'=>$this->config->get('image_scale_mode','inside'));
				$img = $this->image->getThumbnail(@$image->file_path, array('width' => $width, 'height' => $height), $image_options);
				if($img->success) {
					echo '<img class="hikashop_product_compare_image" title="'.$this->escape((string)@$image->file_description).'" alt="'.$this->escape((string)@$image->file_name).'" src="'.$img->url.'"/>';
				}
?>
					</div>
<?php
			}
		}
?>
				</div>
			</div>
		</td>
<?php
	}
?>
	</tr>
<!-- EO IMAGE -->
<!-- PRICE & DIMENSIONS -->
	<tr id="hikashop_compare_tr_price">
		<td class="hikashop_compare_details_first_column"></td>
<?php
	if($this->params->get('show_price','-1') == '-1'){
		$config =& hikashop_config();
		$defaultParams = $config->get('default_params');
		$this->params->set('show_price', $defaultParams['show_price']);
	}

	foreach($this->elements as $k => $element) { ?>
		<td class="hikashop_compare_details_prod_column">
<?php
		if($this->params->get('show_price')) {
?>
			<span id="hikashop_product_<?php echo $element->product_id; ?>_price_main" class="hikashop_product_price_main"><?php
				$this->row =& $element;
				$this->setLayout('listing_price');
				echo $this->loadTemplate();
			?></span>
<?php
		}
?>
<?php
		if(isset($element->product_weight) && bccomp(sprintf('%F',$element->product_weight),0,3)) {
?>
			<br/><span id="hikashop_product_weight_main" class="hikashop_product_weight_main"><?php
				echo JText::_('PRODUCT_WEIGHT').': '.rtrim(rtrim($element->product_weight,'0'),',.').' '.JText::_($element->product_weight_unit);
			?></span>
<?php
		}
		if($this->config->get('dimensions_display',0) && bccomp(sprintf('%F',$element->product_width),0,3)) {
?>
			<br/><span id="hikashop_product_width_main" class="hikashop_product_width_main"><?php
				echo JText::_('PRODUCT_WIDTH').': '.rtrim(rtrim($element->product_width,'0'),',.').' '.JText::_($element->product_dimension_unit);
			?></span>
<?php
		}
		if($this->config->get('dimensions_display',0) && bccomp(sprintf('%F',$element->product_length),0,3)) {
?>
			<br/><span id="hikashop_product_length_main" class="hikashop_product_length_main"><?php
				echo JText::_('PRODUCT_LENGTH').': '.rtrim(rtrim($element->product_length,'0'),',.').' '.JText::_($element->product_dimension_unit);
			?></span>
<?php
		}
		if($this->config->get('dimensions_display',0) && bccomp(sprintf('%F',$element->product_height),0,3)) {
?>
			<br/><span id="hikashop_product_height_main" class="hikashop_product_height_main"><?php
				echo JText::_('PRODUCT_HEIGHT').': '.rtrim(rtrim($element->product_height,'0'),',.').' '.JText::_($element->product_dimension_unit);
			?></span>
<?php
		}
?>
		</td>
<?php
	}
?>
	</tr>
<!-- EO PRICE & DIMENSIONS -->
<!-- ADD TO CART BUTTON -->
	<tr id="hikashop_compare_tr_cart">
		<td class="hikashop_compare_cart_first_column"></td>
<?php
	$form = '';
	if(!$this->config->get('ajax_add_to_cart',0)){
		$form = ',\'hikashop_product_form\'';
	}

	if($this->params->get('add_to_cart')){
		foreach($this->elements as $element) {
			$this->row =& $element;
?>
		<td class="hikashop_compare_cart_prod_column">
			<?php
				$this->params->set('main_div_name','compare');
				$this->setLayout('add_to_cart_listing');
				echo $this->loadTemplate();
			?>
		</td>
<?php
		}
	}
?>
	</tr>
<!-- EO ADD TO CART BUTTON -->
<!-- CUSTOM PRODUCT FIELDS -->
<?php
	foreach( $this->fields[0] as $fieldName => $oneExtraField ) {
		if($oneExtraField->field_type != "customtext") {
			$display = false;
			foreach($this->elements as $element) {
				if(!empty($element->$fieldName) || $element->$fieldName === '0') {
					$display = true;
				}
			}
			if(!$display)
				continue;
?>
	<tr id="hikashop_compare_tr_cf_<?php echo $oneExtraField->field_id;?>">
		<td class="hikashop_compare_custom_first_column">
			<span id="hikashop_product_custom_name_<?php echo $oneExtraField->field_id;?>" class="hikashop_product_custom_name"><?php
				echo $this->fieldsClass->getFieldName($oneExtraField);
			?></span>
		</td>
<?php
			foreach($this->elements as $element) {
?>
		<td class="hikashop_compare_custom_prod_column">
<?php
				if(!empty($element->$fieldName)) {
?>
			<span id="hikashop_product_<?php echo $element->product_id; ?>_custom_value_<?php echo $oneExtraField->field_id;?>" class="hikashop_product_custom_value">
				<?php echo $this->fieldsClass->show($oneExtraField,$element->$fieldName); ?>
			</span>
<?php
				} else {
					$t = JText::_('COMPARE_EMPTY');
					if( $t != 'COMPARE_EMPTY' )
						echo $t;
				}
			}
?>
		</td>
	</tr>
<?php
		} else {
?>
	<tr id="hikashop_compare_tr_cf_<?php echo $oneExtraField->field_id; ?>" class="hikashop_product_compare_custom_separator">
		<td class="hikashop_compare_separator_first_column">
			<span id="hikashop_product_custom_name_<?php echo $oneExtraField->field_id; ?>" class="hikashop_product_custom_name"><?php
				echo $this->fieldsClass->getFieldName($oneExtraField);
			?></span>
		</td>
<?php
			foreach($this->elements as $element) {
?>
		<td class="hikashop_compare_separator_prod_column">
<?php
				if($this->params->get('compare_show_name_separator')) {
?>
			<span id="hikashop_product_<?php echo $element->product_id; ?>_custom_value_<?php echo $oneExtraField->field_id; ?>" class="hikashop_product_custom_value"><?php
				echo $element->product_name;
			?></span>
<?php
				}
?>
		</td>
<?php
			}
?>
	</tr>
<?php
		}
	}
?>
<!-- EO CUSTOM PRODUCT FIELDS -->
</table>
</div>
