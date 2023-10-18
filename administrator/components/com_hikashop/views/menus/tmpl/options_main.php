<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="hkc-xl-4 hkc-md-6 hikashop_menu_subblock hikashop_menu_edit_general_part1">
	<div class="hikashop_menu_subblock_content">
		<div class="hikashop_menu_subblock_title hikashop_menu_edit_display_settings_div_title"><?php echo JText::_('HIKA_DATA_DISPLAY'); ?></div>
		<?php if($this->menu == 'product' || ($this->menu == 'category' && $this->type == 'category')){ ?>
		<dl class="hika_options">
			<dt class="hikashop_option_name">
				<label for="data_menu__<?php echo $this->type; ?>_show_image"><?php echo JText::_( 'SHOW_IMAGE' ); ?></label>
			</dt>
			<dd class="hikashop_option_value">
				<?php echo JHTML::_('hikaselect.booleanlist', $this->name.'[show_image]', 'class="custom-select"', @$this->element['show_image'] ); ?>
			</dd>
		</dl>
		<dl class="hika_options">
			<dt class="hikashop_option_name">
				<label for="data_menu__<?php echo $this->type; ?>_show_description"><?php echo JText::_( 'SHOW_DESCRIPTION' ); ?></label>
			</dt>
			<dd class="hikashop_option_value">
				<?php echo JHTML::_('hikaselect.booleanlist', $this->name.'[show_description]', 'class="custom-select"', @$this->element['show_description'] ); ?>
			</dd>
		</dl>
		<dl class="hika_options">
			<dt class="hikashop_option_name">
				<label for="data_menu__<?php echo $this->type; ?>_category"><?php echo JText::_( 'HIKA_MAIN_CATEGORY' ); ?></label>
			</dt>
			<dd class="hikashop_option_value">
				<?php
				if(!isset($this->element['category']) || $this->element['category'] == '')
					$this->element['category'] = $this->mainProductCategory;

				echo $this->nameboxType->display(
					$this->name.'[category]',
					$this->element['category'],
					hikashopNameboxType::NAMEBOX_SINGLE,
					'category',
					array(
						'delete' => true,
						'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
					)
				);
				?>
			</dd>
		</dl>
		<?php } ?>
		<dl class="hika_options">
			<dt class="hikashop_option_name">
				<label for="data_menu__<?php echo $this->type; ?>_product_order"><?php echo JText::_( 'ORDERING_FIELD' ); ?></label>
			</dt>
			<dd class="hikashop_option_value">
				<?php
				if(!isset($this->element[$this->type.'_order'])) $this->element[$this->type.'_order'] = 'inherit';
				echo $this->orderType->display($this->name.'['.$this->type.'_order]',$this->element[$this->type.'_order'],$this->type);
				?>
			</dd>
		</dl>
		<dl class="hika_options">
			<dt class="hikashop_option_name">
				<label for="data_menu__<?php echo $this->type; ?>_order_dir"><?php echo JText::_( 'ORDERING_DIRECTION' ); ?></label>
			</dt>
			<dd class="hikashop_option_value">
				<?php
				if(!isset($this->element['order_dir'])) $this->element['order_dir'] = 'inherit';
				echo $this->orderdirType->display($this->name.'[order_dir]',$this->element['order_dir']);
				?>
			</dd>
		</dl>
		<dl class="hika_options">
			<dt class="hikashop_option_name">
				<label for="data_menu__<?php echo $this->type; ?>_random"><?php echo JText::_( 'RANDOM_ITEMS' ); ?></label>
			</dt>
			<dd class="hikashop_option_value">
				<?php
				if(!isset($this->element['random'])) $this->element['random'] = '-1';
				echo JHTML::_('hikaselect.inheritRadiolist', $this->name.'[random]', $this->element['random']);
				?>
			</dd>
		</dl>
		<dl class="hika_options">
			<dt class="hikashop_option_name">
				<label for="data_menu__<?php echo $this->type; ?>_filter_type"><?php echo JText::_( 'SUB_ELEMENTS_FILTER' ); ?></label>
			</dt>
			<dd class="hikashop_option_value">
				<?php
				if(!isset($this->element['filter_type'])) $this->element['filter_type'] = '0';
				echo $this->childdisplayType->display($this->name.'[filter_type]',$this->element['filter_type'], true, true, true);
				?>
			</dd>
		</dl>
		<dl class="hika_options">
			<dt class="hikashop_option_name">
				<label for="data_menu__<?php echo $this->type; ?>_use_module_name"><?php echo JText::_( 'MENU_NAME_AS_TITLE' ); ?></label>
			</dt>
			<dd class="hikashop_option_value">
				<?php
				if(!isset($this->element['use_module_name'])) $this->element['use_module_name'] = '0';
				echo JHTML::_('hikaselect.booleanlist', $this->name.'[use_module_name]' , '',$this->element['use_module_name']);
				?>
			</dd>
		</dl>
		<?php if($this->menu == 'product' || ($this->menu == 'category' && $this->type == 'product')){ ?>
		<dl class="hika_options">
			<dt class="hikashop_option_name">
				<label for="data_menu__<?php echo $this->type; ?>_discounted_only"><?php echo JText::_( 'DISCOUNTED_ONLY' ); ?></label>
			</dt>
			<dd class="hikashop_option_value">
				<?php
				if(!isset($this->element['discounted_only'])) $this->element['discounted_only'] = '0';
				echo JHTML::_('hikaselect.booleanlist', $this->name.'[discounted_only]' , '',$this->element['discounted_only']);
				?>
			</dd>
		</dl>
		<dl class="hika_options">
			<dt class="hikashop_option_name">
				<label for="data_menu__<?php echo $this->type; ?>_related_products_from_cart"><?php echo JText::_( 'RELATED_PRODUCTS_FROM_CART' ); ?></label>
			</dt>
			<dd class="hikashop_option_value">
				<?php
				if(!isset($this->element['related_products_from_cart'])) $this->element['related_products_from_cart'] = '0';
				echo JHTML::_('hikaselect.booleanlist', $this->name.'[related_products_from_cart]' , '',$this->element['related_products_from_cart']);
				?>
			</dd>
		</dl>
		<?php } ?>
	</div>
</div>
