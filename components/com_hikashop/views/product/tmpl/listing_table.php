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
if(empty($this->rows))
	return;
$this->type = '';
$height = (int)$this->params->get('image_height');
$width = (int)$this->params->get('image_width');
if(empty($height))
	$height = (int)$this->config->get('thumbnail_y');
if(empty($width))
	$width = (int)$this->config->get('thumbnail_x');

$this->haveLink = (int)$this->params->get('link_to_product_page', 1);

$app = JFactory::getApplication();
$pagination = $this->config->get('pagination','bottom');
if(in_array($pagination, array('top', 'both')) && $this->params->get('show_limit') && $this->pageInfo->elements->total) {
	$this->pagination->form = '_top';
?>
<form action="<?php echo str_replace(array('&tmpl=raw', '&tmpl=component'), '', hikashop_currentURL()); ?>" method="post" name="adminForm_<?php echo $this->params->get('main_div_name').$this->category_selected; ?>_top">
	<div class="hikashop_products_pagination hikashop_products_pagination_top">
		<?php echo str_replace(array('&tmpl=raw', '&tmpl=component'),'', $this->pagination->getListFooter($this->params->get('limit'))); ?>
		<span class="hikashop_results_counter"><?php echo $this->pagination->getResultsCounter(); ?></span>
	</div>
	<input type="hidden" name="filter_order_<?php echo $this->params->get('main_div_name').$this->category_selected;?>" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
	<input type="hidden" name="filter_order_Dir_<?php echo $this->params->get('main_div_name').$this->category_selected;?>" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
<?php
}
?>
<div class="hikashop_products" itemscope="" itemtype="https://schema.org/itemListElement">
<?php
$columns = 1;
if((int)$this->config->get('show_quantity_field') >= 2) {
?>
	<form action="<?php echo hikashop_completeLink('product&task=updatecart'); ?>" method="post" name="hikashop_product_form_<?php echo $this->params->get('main_div_name'); ?>" enctype="multipart/form-data">
<?php } ?>
		<table class="hikashop_products_table adminlist table table-striped table-hover" cellpadding="1"
			 itemscope="" itemtype="https://schema.org/itemListElement">
			<thead>
				<tr>
<!-- IMAGE HEADER -->
<?php if($this->config->get('thumbnail')){ $columns++; ?>
					<th class="hikashop_product_image title hk_center"><?php
						echo JText::_('HIKA_IMAGE');
					?></th>
<?php } ?>
<!-- EO IMAGE HEADER -->
<!-- NAME HEADER -->
					<th class="hikashop_product_name title hk_center"><?php
						echo JText::_('PRODUCT');
					?></th>
<!-- EO NAME HEADER -->
<!-- CODE HEADER -->
<?php if ($this->config->get('show_code')) { $columns++; ?>
					<th class="hikashop_product_code title hk_center"><?php
						echo JText::_('PRODUCT_CODE');
					?></th>
<?php } ?>
<!-- EO CODE HEADER -->
<!-- CUSTOM PRODUCT FIELDS HEADER -->
<?php
	if(hikashop_level(1) && !empty($this->productFields)) {
		$usefulFields = array();
		foreach ($this->productFields as $field) {
			$fieldname = $field->field_namekey;
			foreach($this->rows as $product) {
				if(!empty($product->$fieldname)) {
					$usefulFields[] = $field;
					break;
				}
			}
		}
		$productFields = $usefulFields;

		if(!empty($productFields)) {
			foreach($productFields as $field) {
				$columns++;
?>
					<th class="hikashop_product_field title hk_center"><?php
						echo $this->fieldsClass->getFieldName($field);
					?></th>
<?php
			}
		}
	}
?>
<!-- EO CUSTOM PRODUCT FIELDS HEADER -->
<!-- CHARACTERISTIC AVAILABLE VALUES HEADER -->
<?php
if(!empty($this->row->characteristics)) {
	foreach($this->row->characteristics as $characteristic) {
		if(!empty($characteristic->availableValues)) {
?>
	<th class="hikashop_product_characteristic_on_listing hikashop_product_characteristic_on_listing_<?php echo $characteristic->characteristic_id; ?>">
		<?php echo $characteristic->characteristic_value; ?>
	</th>
<?php		
		}
	}
}
?>
<!-- EO CHARACTERISTIC AVAILABLE VALUES HEADER -->

<!-- VOTE HEADER -->
<?php if($this->params->get('show_vote')){ $columns++; ?>
					<th class="hikashop_product_vote title hk_center">
						<?php echo JText::_('VOTE'); ?>
					</th>
<?php } ?>
<!-- EO VOTE HEADER -->
<!-- PRICE HEADER -->
<?php
	if((int)$this->params->get('show_price', -1) == -1) {
		$this->params->set('show_price', $this->config->get('show_price'));
	}
	if($this->params->get('show_price')) {
		$columns++;
?>
					<th class="hikashop_product_price title hk_center"><?php
						echo JText::_('PRICE');
					?></th>
<?php } ?>
<!-- EO PRICE HEADER -->
<!-- BUTTONS HEADER -->
<?php if($this->params->get('add_to_cart') || $this->params->get('add_to_wishlist') || (hikashop_level(1) && $this->params->get('product_contact_button', 0)) || (int)$this->params->get('details_button', 0)) { $columns++; ?>
					<th class="hikashop_product_add_to_cart title hk_center">
					</th>
<?php } ?>
<!-- EO BUTTONS HEADER -->
<!-- COMPARISON HEADER -->
<?php if(hikaInput::get()->getVar('hikashop_front_end_main', 0) && hikaInput::get()->getVar('task') == 'listing' && $this->params->get('show_compare')) { $columns++; ?>
					<th class="hikashop_product_compare title hk_center">
					</th>
<?php } ?>
<!-- EO COMPARISON HEADER -->
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="<?php echo $columns; ?>">
					</td>
				</tr>
			</tfoot>
			<tbody>
<?php
foreach($this->rows as $row) {
	$this->row =& $row;

	$divWidth = $width;
	$divHeight = $height;

	$this->image->checkSize($divWidth, $divHeight, $row);
	$link = hikashop_contentLink('product&task=show&cid=' . $this->row->product_id . '&name=' . $this->row->alias . $this->itemid . $this->category_pathway, $this->row);

	$this->quantityLayout = $this->getProductQuantityLayout($row);
?>
				<tr itemprop="itemList" itemscope="" itemtype="http://schema.org/ItemList">
<!-- IMAGE -->
<?php if($this->config->get('thumbnail')) { ?>
					<td class="hikashop_product_image_row">
						<div style="height:<?php echo $divHeight;?>px;text-align:center;clear:both;" class="hikashop_product_image">
							<div style="position:relative;text-align:center;clear:both;width:<?php echo $divWidth;?>px;margin: auto;" class="hikashop_product_image_subdiv">
<?php if($this->haveLink) { ?>
								<a href="<?php echo $link;?>" title="<?php echo $this->escape($this->row->product_name); ?>">
<?php } ?>
<?php
	$image_options = array('default' => true,'forcesize'=>$this->config->get('image_force_size',true),'scale'=>$this->config->get('image_scale_mode','inside'));
	$img = $this->image->getThumbnail(@$this->row->file_path, array('width' => $this->image->main_thumbnail_x, 'height' => $this->image->main_thumbnail_y), $image_options);
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
?>		<meta itemprop="image" content=<?php echo HIKASHOP_LIVE.$img->url; ?>/>
<?php
	}
	$main_thumb_x = $this->image->main_thumbnail_x;
	$main_thumb_y = $this->image->main_thumbnail_y;
	if($this->params->get('display_badges',1)){
		$this->classbadge->placeBadges($this->image, $this->row->badges, array('vertical' => -10, 'horizontal' => 0, 'thumbnail' => $img));
	}
	$this->image->main_thumbnail_x = $main_thumb_x;
	$this->image->main_thumbnail_y = $main_thumb_y;
?>
<?php if($this->haveLink) { ?>
								</a>
<?php } ?>
								<meta itemprop="url" content="<?php echo $link;?>">
							</div>
						</div>
					</td>
<?php } ?>
<!-- EO IMAGE -->
<!-- NAME -->
					<td class="hikashop_product_name_row">
						<span class="hikashop_product_name">
<?php if($this->haveLink) { ?>
							<a href="<?php echo $link;?>">
<?php } ?>
								<?php echo $this->row->product_name; ?>
<?php if($this->haveLink) { ?>
								</a>
<?php } ?>						<meta itemprop="name" content="<?php echo $this->escape(strip_tags($this->row->product_name)); ?>">
						</span>
<?php if(!empty($this->row->extraData->afterProductName)) { echo implode("\r\n",$this->row->extraData->afterProductName); } ?>
					</td>
<!-- EO NAME -->
<!-- CODE -->
<?php if($this->config->get('show_code')) { ?>
					<td class="hikashop_product_code_row">
<?php if($this->haveLink) { ?>
						<a href="<?php echo $link;?>">
<?php } ?>
							<?php echo $this->row->product_code; ?>
<?php if($this->haveLink) { ?>
						</a>
<?php } ?>
						</td>
<?php } ?>
<!-- EO CODE -->
<!-- CUSTOM PRODUCT FIELDS -->
<?php
	if(hikashop_level(1) && !empty($productFields)) {
		foreach($productFields as $field) {
			$namekey = $field->field_namekey;
?>
						<td><?php
			if(!empty($field->field_products)) {
							$field_products = is_string($field->field_products) ? explode(',', trim($field->field_products, ',')) : $field->field_products;
							if(!in_array($this->row->product_id, $field_products))
								continue;
			}
			if(!empty($this->row->$namekey))
				echo '<p class="hikashop_product_field'.$namekey.'">'.$this->fieldsClass->show($field,$this->row->$namekey).'</p>';
						?></td>
<?php
		}
	}
?>
<!-- EO CUSTOM PRODUCT FIELDS -->
<!-- CHARACTERISTIC AVAILABLE VALUES -->
			<?php
if(!empty($this->row->characteristics)) {
	foreach($this->row->characteristics as $characteristic) {
		if(!empty($characteristic->availableValues)) {
?>
	<td class="hikashop_product_characteristic_on_listing hikashop_product_characteristic_on_listing_<?php echo $characteristic->characteristic_id; ?>">
<?php
			foreach($characteristic->availableValues as $value) {
?>
			<span class="hikashop_product_characteristic_value_on_listing hikashop_product_characteristic_value_on_listing_<?php echo $value->characteristic_id; ?>">
				<?php echo $value->characteristic_value; ?>
			</span>
<?php
			}
?>
	</td>
<?php		
		}
	}
}
?>
<!-- EO CHARACTERISTIC AVAILABLE VALUES -->
<!-- VOTE -->
<?php if($this->params->get('show_vote')) { ?>
						<td class="hikashop_product_vote_row"><?php
							$this->setLayout('listing_vote');
							echo $this->loadTemplate();
						?></td>
<?php } ?>
<!-- EO VOTE -->
<!-- PRICE -->
<?php if($this->params->get('show_price')) { ?>
						<td class="hikashop_product_price_row"><?php
							$this->setLayout('listing_price');
							echo $this->loadTemplate();
						?></td>
<?php } ?>
<!-- EO PRICE -->
<!-- BUTTONS -->
<?php if($this->params->get('add_to_cart') || $this->params->get('add_to_wishlist') || (hikashop_level(1) && $this->params->get('product_contact_button', 0)) || (int)$this->params->get('details_button', 0)) { ?>
						<td class="hikashop_product_add_to_cart_row">
							<?php if($this->params->get('add_to_cart') || $this->params->get('add_to_wishlist')) {
								$this->setLayout('add_to_cart_listing');
								echo $this->loadTemplate();
							}
						?>
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
</td>
<?php } ?>
<!-- EO BUTTONS -->
<!-- COMPARISON -->
<?php
	if(hikaInput::get()->getVar('hikashop_front_end_main', 0) && hikaInput::get()->getVar('task') == 'listing' && $this->params->get('show_compare')) {
		$css_button = $this->config->get('css_button', 'hikabtn');
		$css_button_compare = $this->config->get('css_button_compare', 'hikabtn-compare');
?>
					<td class="hikashop_product_compare_row">
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
?>
					</td>
<?php
	}
?>
<!-- EO COMPARISON -->
				</tr>
<?php } ?>
			</tbody>
		</table>
<?php
if((int)$this->config->get('show_quantity_field') >= 2) {
	$this->ajax = 'if(hikashopCheckChangeForm(\'item\',\'hikashop_product_form_'.$this->params->get('main_div_name').'\')){ return hikashopModifyQuantity(\'\',field,1,\'hikashop_product_form_'.$this->params->get('main_div_name').'\'); } return false;';

	$this->row = new stdClass();
	$this->row->product_quantity = -1;
	$this->row->product_min_per_order = 0;
	$this->row->product_max_per_order = -1;
	$this->row->product_sale_start = 0;
	$this->row->product_sale_end = 0;
	$this->row->prices = array('filler');
	$this->setLayout('quantity');
	echo $this->loadTemplate();

	if(!empty($this->ajax) && $this->config->get('redirect_url_after_add_cart', 'stay_if_cart') == 'ask_user') {
?>
		<input type="hidden" name="popup" value="1"/>
<?php
	}
?>
		<input type="hidden" name="hikashop_cart_type_0" id="hikashop_cart_type_0" value="cart"/>
		<input type="hidden" name="add" value="1"/>
		<input type="hidden" name="ctrl" value="product"/>
		<input type="hidden" name="task" value="updatecart"/>
		<input type="hidden" name="return_url" value="<?php echo urlencode(base64_encode(urldecode($this->redirect_url))); ?>"/>
	</form>
<?php
}

if(in_array($pagination,array('bottom','both')) && $this->params->get('show_limit') && $this->pageInfo->elements->total) {
	$this->pagination->form = '_bottom';
?>
	<form action="<?php echo str_replace(array('&tmpl=raw', '&tmpl=component'), '', hikashop_currentURL()); ?>" method="post" name="adminForm_<?php echo $this->params->get('main_div_name').$this->category_selected; ?>_bottom">
		<div class="hikashop_products_pagination hikashop_products_pagination_bottom">
		<?php echo str_replace(array('&tmpl=raw', '&tmpl=component'),'', $this->pagination->getListFooter($this->params->get('limit'))); ?>
		<span class="hikashop_results_counter"><?php echo $this->pagination->getResultsCounter(); ?></span>
		</div>
		<input type="hidden" name="filter_order_<?php echo $this->params->get('main_div_name').$this->category_selected; ?>" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
		<input type="hidden" name="filter_order_Dir_<?php echo $this->params->get('main_div_name').$this->category_selected; ?>" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
		<?php echo JHTML::_( 'form.token' ); ?>
	</form>
<?php } ?>
</div>
