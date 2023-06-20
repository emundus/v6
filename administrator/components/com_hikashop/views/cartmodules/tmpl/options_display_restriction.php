<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="hkc-xl-6 hkc-md-6 hikashop_module_subblock hikashop_module_edit_general_part1">
<div class="hikashop_module_subblock_content">
	<div class="hikashop_module_subblock_title hikashop_module_edit_display_settings_div_title"><?php echo JText::_('DISPLAY'); ?></div>
	 <dl class="hika_options">
		<dt class="hikashop_option_name">
			<label><?php echo JText::_('PRODUCT_PAGE'); ?></label>
		</dt>
		<dd class="hikashop_option_value"><?php
			if(!isset($this->element['display_on_product_page']))
				$this->element['display_on_product_page'] = 1;
			echo JHTML::_('hikaselect.booleanlist', $this->name.'[display_on_product_page]', '', $this->element['display_on_product_page']);
		?></dd>
	</dl>
	<dl class="hika_options">
		<dt class="hikashop_option_name">
			<label><?php echo JText::_('PRODUCT_LISTING_PAGE'); ?></label>
		</dt>
		<dd class="hikashop_option_value"><?php
			if(!isset($this->element['display_on_product_listing_page']))
				$this->element['display_on_product_listing_page'] = 1;
			echo JHTML::_('hikaselect.booleanlist', $this->name.'[display_on_product_listing_page]', '', $this->element['display_on_product_listing_page']);
		?></dd>
	</dl>
	<dl class="hika_options">
		<dt class="hikashop_option_name">
			<label><?php echo JText::_('PRODUCT_COMPARE_PAGE'); ?></label>
		</dt>
		<dd class="hikashop_option_value"><?php
			if(!isset($this->element['display_on_product_compare_page']))
				$this->element['display_on_product_compare_page'] = 1;
			echo JHTML::_('hikaselect.booleanlist', $this->name.'[display_on_product_compare_page]', '', $this->element['display_on_product_compare_page']);
		?></dd>
	</dl>
	<dl class="hika_options">
		<dt class="hikashop_option_name">
			<label><?php echo JText::_('CATEGORY_LISTING_PAGE'); ?></label>
		</dt>
		<dd class="hikashop_option_value"><?php
			if(!isset($this->element['display_on_category_listing_page']))
				$this->element['display_on_category_listing_page'] = 1;
			echo JHTML::_('hikaselect.booleanlist', $this->name.'[display_on_category_listing_page]', '', $this->element['display_on_category_listing_page']);
		?></dd>
	</dl>
	<dl class="hika_options">
		<dt class="hikashop_option_name">
			<label><?php echo JText::_('CHECKOUT_PAGE'); ?></label>
		</dt>
		<dd class="hikashop_option_value"><?php
			if(!isset($this->element['display_on_checkout_page']))
				$this->element['display_on_checkout_page'] = 1;
			echo JHTML::_('hikaselect.booleanlist', $this->name.'[display_on_checkout_page]', '', $this->element['display_on_checkout_page']);
		?></dd>
	</dl>
	<dl class="hika_options">
		<dt class="hikashop_option_name">
			<label><?php echo JText::_('COM_HIKASHOP_CONTACT_VIEW_DEFAULT_TITLE'); ?></labdel>
		</dt>
		<dd class="hikashop_option_value"><?php
			if(!isset($this->element['display_on_contact_page']))
				$this->element['display_on_contact_page'] = 1;
			echo JHTML::_('hikaselect.booleanlist', $this->name.'[display_on_contact_page]', '', $this->element['display_on_contact_page']);
		?></dd>
	</dl>
	<dl class="hika_options">
		<dt class="hikashop_option_name">
			<label><?php echo JText::_('COM_HIKASHOP_WAITLIST_VIEW_DEFAULT_TITLE'); ?></label>
		</dt>
		<dd class="hikashop_option_value"><?php
			if(!isset($this->element['display_on_waitlist_page']))
				$this->element['display_on_waitlist_page'] = 1;
			echo JHTML::_('hikaselect.booleanlist', $this->name.'[display_on_waitlist_page]', '', $this->element['display_on_waitlist_page']);
		?></dd>
	</dl>
</div>
</div>
