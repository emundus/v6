<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="hkc-xl-4 hkc-md-6 hikashop_menu_subblock hikashop_menu_edit_product">
	<div class="hikashop_menu_subblock_content">
		<div class="hikashop_menu_subblock_title hikashop_menu_edit_display_settings_div_title"><?php echo JText::_('HIKA_PRODUCT_DATA_DISPLAY'); ?></div>
		<dl class="hika_options">
			<dt class="hikashop_option_name">
				<label><?php
				echo JText::_('HIKA_OUT_OF_STOCK');
				?></label>
			</dt>
			<dd class="hikashop_option_value"><?php
				if(!isset($this->element['show_out_of_stock'])) $this->element['show_out_of_stock'] = '-1';
				echo JHTML::_('hikaselect.inheritRadiolist', $this->name.'[show_out_of_stock]', $this->element['show_out_of_stock']);
			?></dd>
		</dl>
<?php if($this->menu == 'product'){ ?>
		<dl class="hika_options">
			<dt class="hikashop_option_name">
				<label for="data_menu__<?php echo $this->type; ?>_recently_viewed"><?php echo JText::_( 'RECENTLY_VIEWED' ); ?></label>
			</dt>
			<dd class="hikashop_option_value"><?php
				if(!isset($this->element['recently_viewed'])) $this->element['recently_viewed'] = '-1';
				echo JHTML::_('hikaselect.inheritRadiolist', $this->name.'[recently_viewed]', $this->element['recently_viewed']);
			?></dd>
		</dl>
<?php } ?>
		<dl class="hika_options">
			<dt class="hikashop_option_name">
				<label><?php echo hikashop_hktooltip(JText::_('HIKA_LINK_TO_DETAIL_PAGE_TOOLTIP'), '', JText::_('HIKA_LINK_TO_DETAIL_PAGE'), ''); ?></label>
			</dt>
			<dd class="hikashop_option_value"><?php
				if(!isset($this->element['link_to_product_page'])) $this->element['link_to_product_page'] = '-1';
				echo JHTML::_('hikaselect.inheritRadiolist', $this->name.'[link_to_product_page]', $this->element['link_to_product_page']);
			?></dd>
		</dl>
		<dl class="hika_options">
			<dt class="hikashop_option_name">
				<label for="data_menu__product_popup_mode">
					<?php echo JText::_('HIKA_POPUP_MODE');?>
				</label>
			</dt>
			<dd class="hikashop_option_value">
				<?php
				if(!isset($this->element['product_popup_mode'])) $this->element['product_popup_mode'] = 3;
				echo $this->showpopupoptionType->display($this->name.'[product_popup_mode]', $this->element['product_popup_mode'], true, true, true);
				?>
			</dd>
		</dl>
		<dl class="hika_options">
			<dt class="hikashop_option_name">
				<label><?php echo JText::_('DISPLAY_PRICE'); ?></label>
			</dt>
			<dd class="hikashop_option_value"><?php
				if(!isset($this->element['show_price'])) $this->element['show_price'] = '-1';
				echo JHTML::_('hikaselect.inheritRadiolist', $this->name.'[show_price]', $this->element['show_price']);
			?></dd>
		</dl>
		<dl class="hika_options" id="price_display_type_line">
			<dt class="hikashop_option_name">
				<label><?php echo JText::_('HIKA_PRICE_TYPE'); ?></label>
			</dt>
			<dd class="hikashop_option_value"><?php
				if(!isset($this->element['price_display_type'])) $this->element['price_display_type'] = 'inherit';
				echo $this->priceDisplayType->display( $this->name.'[price_display_type]', $this->element['price_display_type']);
			?></dd>
		</dl>
		<dl class="hika_options" id="show_taxed_price_line">
			<dt class="hikashop_option_name">
				<label><?php echo JText::_('SHOW_TAXED_PRICES'); ?></label>
			</dt>
			<dd class="hikashop_option_value"><?php
				if(!isset($this->element['price_with_tax'])) $this->element['price_with_tax'] = 3;
				echo $this->pricetaxType->display($this->name.'[price_with_tax]' , $this->element['price_with_tax'],true);
			?></dd>
		</dl>
		<dl class="hika_options" id="show_original_price_line">
			<dt class="hikashop_option_name">
				<label><?php echo JText::_('HIKA_ORIGINAL_CURRENCY'); ?></label>
			</dt>
			<dd class="hikashop_option_value"><?php
				if(!isset($this->element['show_original_price'])) $this->element['show_original_price'] = '-1';
				echo JHTML::_('hikaselect.inheritRadiolist', $this->name.'[show_original_price]', $this->element['show_original_price']);
			?></dd>
		</dl>
		<dl class="hika_options" id="show_discount_line">
			<dt class="hikashop_option_name">
				<label><?php echo JText::_('HIKA_DISCOUNT_DISPLAY'); ?></label>
			</dt>
			<dd class="hikashop_option_value"><?php
				if(!isset($this->element['show_discount'])) $this->element['show_discount'] = 3;
				echo $this->discountDisplayType->display( $this->name.'[show_discount]', $this->element['show_discount']);
			?></dd>
		</dl>
	</div>
</div>
<div class="hkc-xl-4 hkc-md-6 hikashop_menu_subblock hikashop_menu_edit_product">
	<div class="hikashop_menu_subblock_content">
		<div class="hikashop_menu_subblock_title hikashop_menu_edit_display_settings_div_title"><?php echo JText::_('HIKA_PRODUCT_FEATURES_DISPLAY'); ?></div>
		<dl class="hika_options">
			<dt class="hikashop_option_name">
				<label for="data_menu__<?php echo $this->type; ?>_add_to_cart"><?php echo JText::_( 'ADD_TO_CART' ); ?></label>
			</dt>
			<dd class="hikashop_option_value"><?php
				if(!isset($this->element['add_to_cart'])) $this->element['add_to_cart'] = '-1';
				echo JHTML::_('hikaselect.inheritRadiolist', $this->name.'[add_to_cart]', $this->element['add_to_cart']);
			?></dd>
		</dl>
		<dl class="hika_options">
			<dt class="hikashop_option_name">
				<label><?php echo JText::_('ADD_TO_WISHLIST'); ?></label>
			</dt>
			<dd class="hikashop_option_value"><?php
				if(hikashop_level(1)) {
					if(!isset($this->element['add_to_wishlist'])) $this->element['add_to_wishlist'] = '-1';
					echo JHTML::_('hikaselect.inheritRadiolist', $this->name.'[add_to_wishlist]', $this->element['add_to_wishlist']);
				} else {
					$this->element['add_to_wishlist'] = 0;
					echo hikashop_getUpgradeLink('essential');
				}
			?></dd>
		</dl>
		<dl class="hika_options">
			<dt class="hikashop_option_name">
				<label for="data_menu__<?php echo $this->type; ?>_show_quantity_field"><?php echo JText::_( 'HIKA_QUANTITY_FIELD' ); ?></label>
			</dt>
			<dd class="hikashop_option_value"><?php
				if(!isset($this->element['show_quantity_field'])) $this->element['show_quantity_field'] = '-1';
				echo JHTML::_('hikaselect.inheritRadiolist', $this->name.'[show_quantity_field]', $this->element['show_quantity_field'] );
			?></dd>
		</dl>
		<dl class="hika_options">
			<dt class="hikashop_option_name">
				<label><?php echo JText::_('DISPLAY_WAITLIST_BUTTON'); ?></label>
			</dt>
			<dd class="hikashop_option_value"><?php
				echo JHTML::_('hikaselect.booleanlist', $this->name.'[product_waitlist]', '', @$this->element['product_waitlist']);
			?></dd>
		</dl>
		<dl class="hika_options">
			<dt class="hikashop_option_name">
				<label><?php echo JText::_('CONTACT_US_BUTTON'); ?></label>
			</dt>
			<dd class="hikashop_option_value"><?php
				if(hikashop_level(1)) {
					if(!isset($this->element['product_contact_button'])) $this->element['product_contact_button'] = '-1';
					echo JHTML::_('hikaselect.inheritRadiolist', $this->name.'[product_contact_button]', @$this->element['product_contact_button']);
				} else {
					echo hikashop_getUpgradeLink('essential');
				}
			?></dd>
		</dl>
		<dl class="hika_options">
			<dt class="hikashop_option_name">
				<label><?php echo JText::_('PRODUCT_DETAILS_BUTTON'); ?></label>
			</dt>
			<dd class="hikashop_option_value"><?php
				if(!isset($this->element['details_button'])) $this->element['details_button'] = '-1';
				echo JHTML::_('hikaselect.inheritRadiolist', $this->name.'[details_button]', @$this->element['details_button']);
			?></dd>
		</dl>
		<dl class="hika_options">
			<dt class="hikashop_option_name">
				<label><?php echo JText::_('VOTE'); ?></label> 
			</dt>
			<dd class="hikashop_option_value"><?php
				if((!isset($this->element['show_vote'])) && (isset($this->element['show_vote_product'])))
					$this->element['show_vote'] = $this->element['show_vote_product'];
				elseif(!isset($this->element['show_vote']))
					$this->element['show_vote'] = '-1';
				echo JHTML::_('hikaselect.inheritRadiolist', $this->name.'[show_vote]', $this->element['show_vote']);
			?></dd>
		</dl>
<?php if(hikashop_level(2)) { ?>
		<dl class="hika_options">
			<dt class="hikashop_option_name">
				<label><?php echo JText::_('CUSTOM_ITEM_FIELDS'); ?></label>
			</dt>
			<dd class="hikashop_option_value"><?php
				if(!isset($this->element['display_custom_item_fields'])) $this->element['display_custom_item_fields'] = '-1';
				echo JHTML::_('hikaselect.inheritRadiolist', $this->name.'[display_custom_item_fields]', $this->element['display_custom_item_fields']);
			?></dd>
		</dl>
		<dl class="hika_options">
			<dt class="hikashop_option_name">
				<label><?php echo JText::_('FILTERS'); ?></label>
			</dt>
			<dd class="hikashop_option_value"><?php
				if(!isset($this->element['display_filters'])) $this->element['display_filters'] = '-1';
				echo JHTML::_('hikaselect.inheritRadiolist', $this->name.'[display_filters]', $this->element['display_filters']);
			?></dd>
		</dl>
<?php } ?>
		<dl class="hika_options">
			<dt class="hikashop_option_name">
				<label><?php echo JText::_('HIKA_BADGE'); ?></label>
			</dt>
			<dd class="hikashop_option_value"><?php
				if(!isset($this->element['display_badges'])) $this->element['display_badges'] = '-1';
				echo JHTML::_('hikaselect.inheritRadiolist', $this->name.'[display_badges]', $this->element['display_badges']);
			?></dd>
		</dl>
<?php
if(!empty($this->extra_blocks['products'])) {
	foreach($this->extra_blocks['products'] as $r) {
		if(is_string($r))
			echo $r;
		if(is_array($r)) {
			if(!isset($r['name']) && isset($r[0]))
				$r['name'] = $r[0];
			if(!isset($r['value']) && isset($r[1]))
				$r['value'] = $r[1];
?>
		<dl class="hika_options">
			<dt class="hikashop_option_name"><label><?php echo JText::_(@$r['name']); ?></label></dt>
			<dd class="hikashop_option_value"><?php echo @$r['value']; ?></dd>
		</dl>
<?php
		}
	}
}
?>
	</div>
</div>
