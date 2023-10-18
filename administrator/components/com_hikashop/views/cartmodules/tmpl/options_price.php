<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
$style = empty($this->element['show_price']) ? 'display:none;' : '';
?>
<div class="hkc-xl-6 hkc-md-6 hikashop_module_subblock hikashop_module_edit_product">
<div class="hikashop_module_subblock_content">
	<div class="hikashop_menu_subblock_title hikashop_module_edit_display_settings_div_title"><?php echo JText::_('HIKA_PRICE_DISPLAY'); ?></div>
	<dl class="hika_options">
		<dt class="hikashop_option_name"><?php
			echo JText::_('DISPLAY_PRICE');
		?></dt>
		<dd class="hikashop_option_value"><?php
			if(!isset($this->element['show_price'])) $this->element['show_price'] = '-1';
			foreach($this->arr as $v){
				if($v->value == $this->default_params['show_price'])
					$v->default = true;
			}
			echo JHTML::_('hikaselect.radiolist',  $this->arr, $this->name.'[show_price]', 'data-control="price"', 'value', 'text', @$this->element['show_price']);
		?></dd>
	</dl>
	<dl class="hika_options" id="show_taxed_price_line" style="<?php echo $style; ?>" data-part="price">
		<dt class="hikashop_option_name"><?php
			echo JText::_('SHOW_TAXED_PRICES');
		?></dt>
		<dd class="hikashop_option_value"><?php
			if(!isset($this->element['price_with_tax']))
				$this->element['price_with_tax'] = 3;
			echo $this->pricetaxType->display($this->name.'[price_with_tax]', $this->element['price_with_tax'], true);
		?></dd>
	</dl>
	<dl class="hika_options" id="show_discounted_price_line" style="<?php echo $style; ?>" data-part="price">
		<dt class="hikashop_option_name"><?php
			echo JText::_('SHOW_DISCOUNTED_PRICE');
		?></dt>
		<dd class="hikashop_option_value"><?php
			if(!isset($this->element['show_discount']))
				$this->element['show_discount'] = 3;
			echo $this->discountDisplayType->display($this->name.'[show_discount]', $this->element['show_discount'], true);
		?></dd>
	</dl>
	<input type="hidden" name="<?php echo $this->name.'[show_original_price]'; ?>" value="<?php echo $this->element['show_original_price']; ?>"/>
</div>
</div>
<?php
$js = '
window.hikashop.ready(function(){
	hkjQuery("[data-control=\'price\']").change(function() { hkPriceToggle(hkjQuery(this).val()); });
	hkPriceToggle();
});
hkPriceToggle = function(val) {
	if(typeof val === \'undefined\')
		val = hkjQuery("[data-control=\'price\']").val();
	if( val == "1" || (val == "-1" && "'.(int)@$this->default_params['show_price'].'" == "1"))
		hkjQuery("[data-part=\'price\']").show();
	else
		hkjQuery("[data-part=\'price\']").hide();
}
';
$doc = JFactory::getDocument();
$doc->addScriptDeclaration($js);
