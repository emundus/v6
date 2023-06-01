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
$this->type = '';
$app = JFactory::getApplication();
if((!empty($this->rows) || !$this->module || hikaInput::get()->getVar('hikashop_front_end_main',0)) && $this->pageInfo->elements->total) {
	$pagination = $this->config->get('pagination','bottom');
	if(in_array($pagination,array('top','both')) && $this->params->get('show_limit') && $this->pageInfo->elements->total) {
		$this->pagination->form = '_top';
?>
	<form action="<?php echo str_replace(array('&tmpl=raw', '&tmpl=component'),'', hikashop_currentURL()); ?>" method="post" name="adminForm_<?php echo $this->params->get('main_div_name').$this->category_selected;?>_top">
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
	if(!empty($this->rows)){
		if ($this->config->get('show_quantity_field') >= 2) {
?>
		<form action="<?php echo hikashop_completeLink('product&task=updatecart'); ?>" method="post" name="hikashop_product_form_<?php echo $this->params->get('main_div_name'); ?>" enctype="multipart/form-data">
<?php
		}
		$columns = $this->params->get('columns');
		if(empty($columns)|| $columns == 0)
			$columns = 1;
		$width = (int)(100 / $columns) - 2;
		$current_column = 1;

		if(empty($width))
			$width='style="float:left;"';
		else
			$width='style="float:left;width:'.$width.'%;"';

?>
			<ul class="hikashop_product_list <?php echo $this->params->get('ul_class_name'); ?>" data-consistencyheight="true" itemscope="" itemtype="https://schema.org/itemListElement">
<?php
		foreach($this->rows as $row) {
			$this->row =& $row;
			$link = hikashop_contentLink('product&task=show&cid='.$row->product_id.'&name='.$row->alias.$this->itemid.$this->category_pathway,$row);
			$this->quantityLayout = $this->getProductQuantityLayout($row);
?>
				<li class="hikashop_product_list_item" <?php echo $width; ?> itemprop="itemList" itemscope="" itemtype="http://schema.org/ItemList">
<!-- NAME -->
<?php
			if($this->params->get('link_to_product_page', 0)) { ?>
					<a href="<?php echo $link; ?>" class="hikashop_product_name_in_list">
<?php
			}
			echo $row->product_name;
?>					<meta itemprop="url" content="<?php echo $link; ?>">
					<meta itemprop="name" content="<?php echo $this->escape(strip_tags($row->product_name)); ?>">
					<span class='hikashop_product_code_list'><?php
						if ($this->config->get('show_code')) {
							echo $this->row->product_code;
						}
					?></span>
<?php
			if($this->params->get('show_price')) {
				$this->setLayout('listing_price');
				echo '&nbsp;'.$this->loadTemplate();
			}

			if($this->params->get('link_to_product_page',1)){ ?>
					</a>
<?php
			}
?>
<!-- EO NAME -->
<!-- CUSTOM PRODUCT FIELDS -->
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
<!-- EO CUSTOM PRODUCT FIELDS -->
<!-- VOTE -->
<?php
			if($this->params->get('show_vote')){
				$this->setLayout('listing_vote');
				echo $this->loadTemplate();
			}
?>
<!-- EO VOTE -->
<!-- ADD TO CART AREA -->
<?php
			if($this->params->get('add_to_cart') || $this->params->get('add_to_wishlist')) {
				$this->setLayout('add_to_cart_listing');
				echo $this->loadTemplate();
			}
?>
<!-- EO ADD TO CART AREA -->
<!-- COMPARISON -->
<?php
			if(hikaInput::get()->getVar('hikashop_front_end_main', 0) && hikaInput::get()->getVar('task') == 'listing' && $this->params->get('show_compare')) {
				$css_button = $this->config->get('css_button', 'hikabtn');
				$css_button_compare = $this->config->get('css_button_compare', 'hikabtn-compare');
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
<!-- EO COMPARISON -->
<!-- CONTACT US BUTTON -->
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

<!-- EO CONTACT US BUTTON -->

<!-- PRODUCT DETAILS BUTTON -->
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

<!-- EO PRODUCT DETAILS BUTTON -->
				</li>
<?php
			if($current_column >= $columns) {
				$current_column = 0;
			}
			$current_column++;
		}
?>
			</ul>
<?php
		if($this->config->get('show_quantity_field') >= 2) {
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
?>
<?php
			if(!empty($this->ajax) && $this->config->get('redirect_url_after_add_cart','stay_if_cart')=='ask_user') {
?>
			<input type="hidden" name="popup" value="1"/>
<?php } ?>
			<input type="hidden" name="hikashop_cart_type_0" id="hikashop_cart_type_0" value="cart"/>
			<input type="hidden" name="add" value="1"/>
			<input type="hidden" name="ctrl" value="product"/>
			<input type="hidden" name="task" value="updatecart"/>
			<input type="hidden" name="return_url" value="<?php echo urlencode(base64_encode(urldecode($this->redirect_url)));?>"/>
		</form>
<?php
		}
	}
		?>
	</div>
<?php
	if(in_array($pagination,array('bottom','both')) && $this->params->get('show_limit') && $this->pageInfo->elements->total) {
		$this->pagination->form = '_bottom';
?>
	<form action="<?php echo str_replace(array('&tmpl=raw', '&tmpl=component'),'', hikashop_currentURL()); ?>" method="post" name="adminForm_<?php echo $this->params->get('main_div_name').$this->category_selected;?>_bottom">
		<div class="hikashop_products_pagination hikashop_products_pagination_bottom">
		<?php echo str_replace(array('&tmpl=raw', '&tmpl=component'),'', $this->pagination->getListFooter($this->params->get('limit'))); ?>
		<span class="hikashop_results_counter"><?php echo $this->pagination->getResultsCounter(); ?></span>
		</div>
		<input type="hidden" name="filter_order_<?php echo $this->params->get('main_div_name').$this->category_selected;?>" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
		<input type="hidden" name="filter_order_Dir_<?php echo $this->params->get('main_div_name').$this->category_selected;?>" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
		<?php echo JHTML::_( 'form.token' ); ?>
	</form>
<?php }
}
