<?php
/**
 * @package	HikaShop for Joomla!
 * @version	3.0.1
 * @author	hikashop.com
 * @copyright	(C) 2010-2017 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
$minicart_style = (!empty($this->element['small_cart']) && (int)$this->element['small_cart'] == 1) ? 'display: none;' : '';
$dropdown_style = (empty($this->element['small_cart']) || (int)$this->element['small_cart'] != 2) ? 'display: none;' : '';
?>
<div class="hkc-xl-6 hkc-md-6 hikashop_module_subblock hikashop_module_edit_general_part1">
	<div class="hikashop_module_subblock_content">
		<div class="hikashop_menu_subblock_title hikashop_module_edit_display_settings_div_title"><?php echo JText::_('HIKA_DATA_DISPLAY'); ?></div>
		<dl class="hika_options">
			<dt class="hikashop_option_name"><?php
				echo hikashop_hktooltip(JText::sprintf('HIKA_MINI_CART_DESC',$this->type), '', JText::sprintf('MINI_CART', $this->type), '', 0);
			?></dt>
			<dd class="hikashop_option_value"><?php
				$values = array(
					1 => JHTML::_('select.option', 1, JText::_('JYES')),
					0 => JHTML::_('select.option', 0, JText::_('JNO')),
					2 => JHTML::_('select.option', 2, JText::_('HIKA_CART_DROPDOWN')),
				);
				echo JHTML::_('hikaselect.radiolist', $values, $this->name.'[small_cart]', 'data-control="mini_cart"', 'value', 'text', (int)@$this->element['small_cart']);
			?></dd>
		</dl>
		<dl class="hika_options" style="<?php echo $minicart_style; ?>" data-part="mini_cart">
			<dt class="hikashop_option_name"><?php
				echo JText::_('HIKA_CART_IMAGE');
			?></dt>
			<dd class="hikashop_option_value"><?php
				echo JHTML::_('hikaselect.booleanlist', $this->name.'[image_in_cart]', '', @$this->element['image_in_cart']);
			?></dd>
		</dl>
		<dl class="hika_options">
			<dt class="hikashop_option_name"><?php
				echo JText::_('HIKA_PROCEED_BUTTON');
			?></dt>
			<dd class="hikashop_option_value"><?php
				echo JHTML::_('hikaselect.booleanlist', $this->name.'[show_cart_proceed]', '', @$this->element['show_cart_proceed']);
			?></dd>
		</dl>
		<dl class="hika_options" style="<?php echo $minicart_style; ?>" data-part="mini_cart">
			<dt class="hikashop_option_name"><?php
				echo JText::_('HIKA_PRODUCT_NAME');
			?></dt>
			<dd class="hikashop_option_value"><?php
				if(!isset($this->element['show_cart_product_name']))
					$this->element['show_cart_product_name'] = 1;
				echo JHTML::_('hikaselect.booleanlist', $this->name.'[show_cart_product_name]', '', $this->element['show_cart_product_name']);
			?></dd>
		</dl>
		<dl class="hika_options">
			<dt class="hikashop_option_name"><?php
				echo JText::_('HIKA_PRODUCT_QUANTITIES');
			?></dt>
			<dd class="hikashop_option_value"><?php
				echo JHTML::_('hikaselect.booleanlist', $this->name.'[show_cart_quantity]', '', @$this->element['show_cart_quantity']);
			?></dd>
		</dl>
		<dl class="hika_options">
			<dt class="hikashop_option_name"><?php
				echo hikashop_hktooltip(JText::sprintf('HIKA_DELETE_BUTTON_DESC', $this->type), '', JText::_('HIKA_DELETE_BUTTON'), '', 0);
			?></dt>
			<dd class="hikashop_option_value"><?php
				echo JHTML::_('hikaselect.booleanlist', $this->name.'[show_cart_delete]', '', @$this->element['show_cart_delete']);
			?></dd>
		</dl>
		<dl class="hika_options">
			<dt class="hikashop_option_name"><?php
				echo hikashop_hktooltip(JText::sprintf('HIKA_CART_COUPON_DESC', $this->type), '', JText::_('HIKASHOP_CHECKOUT_COUPON'), '', 0);
			?></dt>
			<dd class="hikashop_option_value"><?php
				echo JHTML::_('hikaselect.booleanlist', $this->name.'[show_coupon]', '', @$this->element['show_coupon']);
			?></dd>
		</dl>
		<dl class="hika_options">
			<dt class="hikashop_option_name"><?php
				echo hikashop_hktooltip(JText::_('HIKA_CART_SHIPPING_DESC'), '', JText::_('HIKASHOP_CHECKOUT_SHIPPING'), '', 0);
			?></dt>
			<dd class="hikashop_option_value"><?php
				echo JHTML::_('hikaselect.booleanlist', $this->name.'[show_shipping]', '', @$this->element['show_shipping']);
			?></dd>
		</dl>
		<dl class="hika_options">
			<dt class="hikashop_option_name"><?php
				echo JText::_('HIDE_CART');
			?></dt>
			<dd class="hikashop_option_value"><?php
				echo JHTML::_('hikaselect.radiolist', $this->arr1, $this->name.'[hide_cart]', 'data-control="hideCart"', 'value', 'text', @$this->element['hide_cart']);
			?></dd>
		</dl>
		<dl class="hika_options" data-part="msg" style="<?php echo ((@$this->element['hide_cart'] != "1") ? 'display: none;' : ''); ?>">
			<dt class="hikashop_option_name"><?php
				echo hikashop_hktooltip(JText::sprintf('HIKA_EMPTY_MESSAGE_DESC', $this->type), '', JText::_('HIKA_EMPTY_MESSAGE'), '', 0);
			?></dt>
			<dd class="hikashop_option_value">
				<input name="<?php echo $this->name; ?>[msg]" id="custommsg" type="text" value="<?php echo $this->escape(@$this->element['msg']); ?>"/>
			</dd>
		</dl>
<?php
	if(preg_match('/wishlist/', $this->name)) {
?>
		<dl class="hika_options">
			<dt class="hikashop_option_name"><?php
				echo hikashop_hktooltip(JText::sprintf('CART_MODULE_ITEMID_DESC', $this->type), '', JText::_('HIKA_ITEM_ID'), '', 0);
			?></dt>
			<dd class="hikashop_option_value">
				<input name="<?php echo $this->name; ?>[cart_itemid]" type="text" value="<?php echo $this->escape(@$this->element['cart_itemid']); ?>" />
			</dd>
		</dl>
<?php
	}
?>
	</div>
</div>
<div class="hkc-xl-6 hkc-md-6 hikashop_module_subblock hikashop_module_edit_general_part1" style="<?php echo $dropdown_style; ?>" data-part="dropdown_cart">
	<div class="hikashop_module_subblock_content">
		<div class="hikashop_menu_subblock_title hikashop_module_edit_display_settings_div_title"><?php echo JText::_('HIKA_CART_DROPDOWN'); ?></div>
		<dl class="hika_options">
			<dt class="hikashop_option_name"><?php
				echo hikashop_hktooltip(JText::sprintf('CART_MODULE_DROPDOWN_LEFT_DESC', $this->type), '', JText::_('CART_MODULE_DROPDOWN_LEFT'), '', 0);
			?></dt>
			<dd class="hikashop_option_value">
				<input name="<?php echo $this->name; ?>[dropdown_left]" type="text" value="<?php echo (int)@$this->element['dropdown_left']; ?>" />
			</dd>
		<dl class="hika_options">
		</dl>
			<dt class="hikashop_option_name"><?php
				echo hikashop_hktooltip(JText::sprintf('CART_MODULE_DROPDOWN_RIGHT_DESC', $this->type), '', JText::_('CART_MODULE_DROPDOWN_RIGHT'), '', 0);
			?></dt>
			<dd class="hikashop_option_value">
				<input name="<?php echo $this->name; ?>[dropdown_right]" type="text" value="<?php echo (int)@$this->element['dropdown_right']; ?>" />
			</dd>
		</dl>
	</div>
</div>
<?php
$js = '
window.hikashop.ready(function(){
	hkjQuery("[data-control=\'mini_cart\']").change(function(){
		if(hkjQuery(this).val() == "1")
			hkjQuery("[data-part=\'mini_cart\']").hide();
		else
			hkjQuery("[data-part=\'mini_cart\']").show();

		if(hkjQuery(this).val() == "2")
			hkjQuery("[data-part=\'dropdown_cart\']").show();
		else
			hkjQuery("[data-part=\'dropdown_cart\']").hide();
	});

	hkjQuery("[data-control=\'hideCart\']").change(function(){
		if(hkjQuery(this).val() == "1")
			hkjQuery("[data-part=\'msg\']").show();
		else
			hkjQuery("[data-part=\'msg\']").hide();
	});
});
';
$doc = JFactory::getDocument();
$doc->addScriptDeclaration($js);
