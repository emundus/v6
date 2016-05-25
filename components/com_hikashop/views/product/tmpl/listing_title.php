<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2016 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
$mainDivName = $this->params->get('main_div_name','');
$link = hikashop_contentLink('product&task=show&cid='.$this->row->product_id.'&name='.$this->row->alias.$this->itemid.$this->category_pathway,$this->row);

if(!empty($this->row->extraData->top)) { echo implode("\r\n",$this->row->extraData->top); }
?>
<div class="hikashop_listing_title" id="div_<?php echo $mainDivName.'_'.$this->row->product_id;  ?>">
	<!-- PRODUCT NAME -->
	<span class="hikashop_product_name">
		<?php if($this->params->get('link_to_product_page',1)){ ?>
			<a href="<?php echo $link;?>">
		<?php }
			echo $this->row->product_name;
		if($this->params->get('link_to_product_page',1)){ ?>
		</a>
		<?php } ?>
	</span>
	<!-- EO PRODUCT NAME -->
	<!-- PRODUCT CODE -->
		<span class='hikashop_product_code_list'>
			<?php if ($this->config->get('show_code')) { ?>
				<?php if($this->params->get('link_to_product_page',1)){ ?>
					<a href="<?php echo $link;?>">
				<?php }
				echo $this->row->product_code;
				if($this->params->get('link_to_product_page',1)){ ?>
					</a>
				<?php } ?>
			<?php } ?>
		</span>
	<!-- EO PRODUCT CODE -->
	<?php if(!empty($this->row->extraData->afterProductName)) { echo implode("\r\n",$this->row->extraData->afterProductName); } ?>
	<!-- PRODUCT PRICE -->
	<?php

	if($this->params->get('show_price')){
		$this->setLayout('listing_price');
		echo $this->loadTemplate();
	} ?>
	<!-- EO PRODUCT PRICE -->

	<!-- PRODUCT CUSTOM FIELDS -->
	<?php
		if(!empty($this->productFields)) {
			foreach ($this->productFields as $fieldName => $oneExtraField) {
				if(!empty($this->row->$fieldName) || (isset($this->row->$fieldName) && $this->row->$fieldName === '0')) {
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
		}
	?>
	<!-- EO PRODUCT CUSTOM FIELDS -->

	<!-- PRODUCT VOTE -->
	<?php
	if($this->params->get('show_vote_product')){
		$this->setLayout('listing_vote');
		echo $this->loadTemplate();
	}
	?>
	<!-- EO PRODUCT VOTE -->

	<!-- ADD TO CART BUTTON AREA -->
	<?php

	if($this->params->get('add_to_cart') || $this->params->get('add_to_wishlist')){
		$this->setLayout('add_to_cart_listing');
		echo $this->loadTemplate();
	} ?>
	<!-- EO ADD TO CART BUTTON AREA -->
	<?php
	if(JRequest::getVar('hikashop_front_end_main',0) && JRequest::getVar('task')=='listing' && $this->params->get('show_compare')) { ?>
		<br/><?php
		if( $this->params->get('show_compare') == 1 ) {
			$js = 'setToCompareList('.$this->row->product_id.',\''.$this->escape($this->row->product_name).'\',this); return false;';
			echo $this->cart->displayButton(JText::_('ADD_TO_COMPARE_LIST'),'compare',$this->params,$link,$js,'',0,1,'hikashop_compare_button');
		} else { ?>
		<input type="checkbox" class="hikashop_compare_checkbox" id="hikashop_listing_chk_<?php echo $this->row->product_id;?>" onchange="setToCompareList(<?php echo $this->row->product_id;?>,'<?php echo $this->escape($this->row->product_name); ?>',this);"><label for="hikashop_listing_chk_<?php echo $this->row->product_id;?>"><?php echo JText::_('ADD_TO_COMPARE_LIST'); ?></label>
	<?php }
	} ?>
</div>
<?php if(!empty($this->row->extraData->bottom)) { echo implode("\r\n",$this->row->extraData->bottom); } ?>
