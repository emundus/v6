<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2020 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="hkc-xl-4 hkc-md-6 hikashop_module_subblock hikashop_module_edit_category" data-display-type="category">
	<div class="hikashop_module_subblock_content">
		<div class="hikashop_module_subblock_title hikashop_module_edit_display_settings_div_title"><?php echo JText::_('HIKA_CATEGORY_SETTINGS'); ?></div>
		<dl class="hika_options">
			<dt class="hikashop_option_name">
				<label for="data_module__<?php echo $this->type; ?>_child_display_type"><?php echo JText::_( 'HIKA_SUB_CATEGORIES' ); ?></label>
			</dt>
			<dd class="hikashop_option_value">
				<?php
				if(!isset($this->element['child_display_type'])) $this->element['child_display_type'] = 'inherit';
				echo $this->listType->display($this->name.'[child_display_type]',$this->element['child_display_type']);
				?>
			</dd>
		</dl>
		<dl class="hika_options">
			<dt class="hikashop_option_name">
				<label for="data_module__<?php echo $this->type; ?>_child_limit"><?php echo JText::_( 'HIKA_SUB_CATEGORIES_NUMBER' ); ?></label>
			</dt>
			<dd class="hikashop_option_value">
				<input name="<?php echo $this->name; ?>[child_limit]" type="text" value="<?php echo @$this->element['child_limit'];?>" />
			</dd>
		</dl>
		<dl class="hika_options">
			<dt class="hikashop_option_name">
				<?php echo hikashop_hktooltip(JText::_('LINK_ON_MAIN_CATEGORIES'), '', JText::_('HIKA_LINK_MAIN_CATEGORIES'), '', 0);?>
			</dt>
			<dd class="hikashop_option_value">
				<?php
				if(!isset($this->element['links_on_main_categories'])) $this->element['links_on_main_categories'] = '-1';
				echo JHTML::_('hikaselect.inheritRadiolist', $this->name.'[links_on_main_categories]', @$this->element['links_on_main_categories']);
				?>
			</dd>
		</dl>
		<dl class="hika_options">
			<dt class="hikashop_option_name">
				<label for="data_module__<?php echo $this->type; ?>_number_of_products">
					<?php echo hikashop_hktooltip(JText::_('SHOW_NUMBER_OF_PRODUCTS'), '', JText::_('HIKA_PRODUCTS_NUMBER'), '', 0);?>
				</label>
			</dt>
			<dd class="hikashop_option_value">
				<?php
				if(!isset($this->element['number_of_products'])) $this->element['number_of_products'] = '-1';
				echo JHTML::_('hikaselect.inheritRadiolist', $this->name.'[number_of_products]', $this->element['number_of_products']);
				?>
			</dd>
		</dl>
		<dl class="hika_options">
			<dt class="hikashop_option_name">
				<label for="data_module__<?php echo $this->type; ?>_only_if_products">
					<?php echo hikashop_hktooltip(JText::_('ONLY_DISPLAY_CATEGORIES_WITH_PRODUCTS'), '', JText::_('HIKA_ONLY_WITH_PRODUCTS'), '', 0);?>
				</label>
			</dt>
			<dd class="hikashop_option_value">
				<?php
				if(!isset($this->element['only_if_products'])) $this->element['only_if_products'] = '-1';
				echo JHTML::_('hikaselect.inheritRadiolist', $this->name.'[only_if_products]', $this->element['only_if_products']);
				?>
			</dd>
		</dl>
	</div>
</div>
