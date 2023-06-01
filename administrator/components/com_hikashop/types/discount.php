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
class HikashopDiscountType
{
	public function load($form)
	{
		$this->values = array();
		if (!$form) {
			$this->values[] = JHTML::_('select.option', 'all', JText::_('HIKA_ALL'));
		}
		$this->values[] = JHTML::_('select.option', 'discount', JText::_('DISCOUNTS'));
		$this->values[] = JHTML::_('select.option', 'coupon', JText::_('COUPONS'));
	}

	public function display($map, $value, $form = false)
	{
		$this->load($form);
		$attribute='';
		if (!$form) {
			$attribute = ' onchange="document.adminForm.submit( );"';
		} else {
			if (empty($value)) {
				$value = 'discount';
			}
			$js = '
function hikashopToggleDiscount(value) {
	var elements = document.querySelectorAll("[data-discount-display]");
	for(var i = elements.length - 1; i >= 0; i--) {
		elements[i].style.display = (elements[i].getAttribute("data-discount-display") == value) ? "" : "none";
	}
}
window.hikashop.ready( function(){ hikashopToggleDiscount(\''.$value.'\'); });
';
			$doc = JFactory::getDocument();
			$doc->addScriptDeclaration($js);
			$attribute = ' onchange="hikashopToggleDiscount(this.value);"';
		}

		return JHTML::_('select.genericlist',   $this->values, $map, 'class="custom-select" size="1"'.$attribute, 'value', 'text', $value);
	}

	public function displaySelector($map, $value, $delete = false, $type = 'coupon')
	{
		static $jsInit = null;

		$app = JFactory::getApplication();

		if ($jsInit !== true) {
			$display_format = 'data.discount_code';
			if (hikashop_isClient('administrator')) {
				$display_format = 'data.id + " - " + data.discount_code';
			}

			$js = '
if(!window.localPage)
	window.localPage = {};
window.localPage.fieldSetDiscount = function(el, name) {
	window.hikashop.submitFct = function(data) {
		var d = document,
			elemInput = d.getElementById(name + "_input_id"),
			elemSpan = d.getElementById(name + "_span_id");
		if(elemInput) { elemInput.value = data.id; }
		if(elemSpan) { elemSpan.innerHTML = '.$display_format.'; }
	};
	window.hikashop.openBox(el,null);
	return false;
};
window.localPage.fieldRemDiscount = function(el, name) {
	var d = document,
		elemInput = d.getElementById(name + "_input_id"),
		elemSpan = d.getElementById(name + "_span_id");
	if(elemInput) { elemInput.value = ""; }
	if(elemSpan) { elemSpan.innerHTML = " - "; }
};
';
			$doc = JFactory::getDocument();
			$doc->addScriptDeclaration($js);

			$jsInit = true;
		}

		$discountClass = hikashop_get('class.discount');
		$popupHelper = hikashop_get('helper.popup');

		$name = str_replace(array('][', '[', ']'), '_', $map);
		$discount_id = (int)$value;
		$discount = $discountClass->get($discount_id);
		$discount_code = '';
		if (!empty($discount)) {
			$discount_code = @$discount->discount_code;
		} else {
			if (!empty($discount_id)) {
				$discount_code = '<em>'.JText::_('INVALID_DISCOUNT_CODE').'</em>';
			}
			$discount_id = '';
		}

		$discount_display_name = $discount_code;
		if (hikashop_isClient('administrator')) {
			$discount_display_name = $discount_id.' - '.$discount_code;
		}

		if (empty($type) || !in_array($type, array('all', 'coupon', 'discount'))) {
			$type = 'all';
		}

		$ret = '<span id="'.$name.'_span_id">'.$discount_display_name.'</span>' .
			'<input type="hidden" id="'.$name.'_input_id" name="'.$map.'" value="'.$discount_id.'"/> '.
			$popupHelper->display(
				'<img src="'.HIKASHOP_IMAGES.'edit.png" style="vertical-align:middle;"/>',
				'DISCOUNT_SELECTION',
				hikashop_completeLink('discount&task=selection&filter_type='.$type.'&single=true', true),
				'hikashop_set_discount_'.$name,
				760, 480, 'onclick="return window.localPage.fieldSetDiscount(this,\''.$name.'\');"', '', 'link'
			);

		if ($delete) {
			$ret .= ' <a title="'.JText::_('HIKA_DELETE').'" href="#'.JText::_('HIKA_DELETE').'" onclick="return window.localPage.fieldRemDiscount(this, \''.$name.'\');"><img src="'.HIKASHOP_IMAGES.'cancel.png" style="vertical-align:middle;"/></a>';
		}

		return $ret;
	}
}
