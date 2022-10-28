<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class hikamarketDiscountClass extends hikamarketClass {

	protected $tables = array('shop.discount');
	protected $pkeys = array('discount_id');
	protected $toggle = array('discount_published' => 'discount_id');
	protected $deleteToggle = array('shop.discount' => 'discount_id');

	public function frontSaveForm($task = '', $acl = true) {
		$app = JFactory::getApplication();
		$config = hikamarket::config();
		$shopConfig = hikamarket::config(false);
		$discount_id = hikamarket::getCID('discount_id');
		$discountClass = hikamarket::get('shop.class.discount');
		$vendorClass = hikamarket::get('class.vendor');
		$vendor_id = hikamarket::loadVendor(false, false);
		$safeHtmlFilter = JFilterInput::getInstance(array(), array(), 1, 1);

		$discount = new stdClass();
		$new = false;
		if(empty($discount_id))
			$new = true;
		else
			$discount->discount_id = $discount_id;

		$formData = hikaInput::get()->get('data', array(), 'array');
		unset($formData['discount']['discount_id']);

		$nameboxes = array('discount_product_id','discount_category_id','discount_zone_id','discount_user_id');
		foreach($formData['discount'] as $column => $value) {
			hikamarket::secureField($column);
			if(in_array($column, $nameboxes)) {
				if($column == 'discount_zone_id') {
					$discount->$column = array();
					foreach($value as $i => $v) {
						$discount->{$column}[] = $safeHtmlFilter->clean(strip_tags($v), 'string');
					}
				} else {
					hikamarket::toInteger($value);
				}
				$discount->$column = $value;
			} else {
				$discount->$column = $safeHtmlFilter->clean(strip_tags($value), 'string');
			}
		}

		if(!hikamarket::acl('discount/edit/code')) { unset($discount->discount_code); }
		if(!hikamarket::acl('discount/edit/type')) { unset($discount->discount_type); }

		if(!hikamarket::acl('discount/edit/flatamount')) { unset($discount->discount_flat_amount); }
		if(!hikamarket::acl('discount/edit/flatamount')) { unset($discount->discount_currency_id); }

		if(!hikamarket::acl('discount/edit/percentamount')) { unset($discount->discount_percent_amount); }
		if(!hikamarket::acl('discount/edit/taxcategory')) { unset($discount->discount_tax_id); }
		if(!hikamarket::acl('discount/edit/usedtimes')) { unset($discount->discount_used_times); }
		if(!hikamarket::acl('discount/edit/published')) { unset($discount->discount_published); }
		if(!hikamarket::acl('discount/edit/dates')) {
			unset($discount->discount_start);
			unset($discount->discount_end);
		}
		if(!hikamarket::acl('discount/edit/minorder')) { unset($discount->discount_minimum_order); }
		if(!hikamarket::acl('discount/edit/minproducts')) { unset($discount->discount_minimum_products); }
		if(!hikamarket::acl('discount/edit/quota')) { unset($discount->discount_quota); }
		if(!hikamarket::acl('discount/edit/peruser')) { unset($discount->discount_quota_per_user); }
		if(!hikamarket::acl('discount/edit/product')) { unset($discount->discount_product_id); }
		if(!hikamarket::acl('discount/edit/category')) { unset($discount->discount_category_id); }
		if(!hikamarket::acl('discount/edit/zone')) { unset($discount->discount_zone_id); }
		if(!hikamarket::acl('discount/edit/user') || !hikashop_level(2)) { unset($discount->discount_user_id); }

		if($new && !empty($vendor_id) && $vendor_id > 1)
			$discount->discount_target_vendor = $vendor_id;
		else if($vendor_id > 1 || !hikamarket::acl('discount/edit/targetvendor'))
			unset($discount->discount_target_vendor);
		else
			$discount->discount_target_vendor = (int)@$discount->discount_target_vendor;

		if(!empty($discount->discount_start))
			$discount->discount_start = hikamarket::getTime($discount->discount_start);
		if(!empty($discount->discount_end))
			$discount->discount_end = hikamarket::getTime($discount->discount_end);

		if(!empty($discount->discount_id) && !empty($discount->discount_code)){
			$query = 'SELECT discount_id FROM '.hikamarket::table('shop.discount').' WHERE discount_code  = '. $this->db->Quote($discount->discount_code).' AND discount_id != ' . (int)$discount->discount_id;
			$this->db->setQuery($query);
			$res = $this->db->loadResult();
			if(!empty($res) && $res != $discount->discount_id){
				$app = JFactory::getApplication();
				$app->enqueueMessage(JText::_('DISCOUNT_CODE_ALREADY_USED'), 'error');
				hikaInput::get()->set('fail', $discount);
				return false;
			}
		}

		$status = $this->save($discount);
		if($status) {
			$app->enqueueMessage(JText::_('HIKASHOP_SUCC_SAVED'));
		} else {
			hikaInput::get()->set('fail', $discount);
			$app->enqueueMessage(JText::_('DISCOUNT_CODE_ALREADY_USED'));
		}
		return $status;
	}

	public function save(&$discount) {
		JPluginHelper::importPlugin('hikamarket');
		$discountClass = hikamarket::get('shop.class.discount');
		$status = $discountClass->save($discount);
		return $status;
	}

	public function toggleId($task, $value = null) {
		if($value !== null) {
			$app = JFactory::getApplication();
			if(!hikamarket::isAdmin() && ((int)$value == 0 || empty($this->toggle[$task]) || !hikamarket::acl('discount/edit/'.str_replace('discount_', '', $task)) || !hikamarket::isVendorDiscount((int)$value) ))
				return false;
		}
		if(!empty($this->toggle[$task]))
			return $this->toggle[$task];
		return false;
	}

	public function toggleDelete($value1 = '', $value2 = '') {
		$app = JFactory::getApplication();
		if(!hikamarket::isAdmin() && ((int)$value1 == 0 || !hikamarket::acl('discount/delete') || !hikamarket::isVendorDiscount((int)$value1)))
			return false;
		if(!empty($this->deleteToggle))
			return $this->deleteToggle;
		return false;
	}

	public function discountBlocksDisplay(&$discount, &$html) {
		$vendor_id = (int)@$discount->discount_target_vendor;
		if($vendor_id > 1) {
			$vendorClass = hikamarket::get('class.vendor');
			$vendor = $vendorClass->get($vendor_id);
			$vendor_name = $vendor_id . ' - ' . $vendor->vendor_name;
		} else {
			$vendor_id = -2;
			$vendor_name = JText::_('HIKAM_SELECT_VENDOR');
		}
		$values = array(
			JHTML::_('select.option', '0', JText::_('HIKASHOP_NO')),
			JHTML::_('select.option', '1', JText::_('HIKASHOP_YES')),
			JHTML::_('select.option', '-1', JText::_('HIKAM_DISCOUNT_NOT_APPLIED_TO_VENDOR')),
			JHTML::_('select.option', $vendor_id, $vendor_name)
		);

		$popup = hikamarket::get('shop.helper.popup');

		$ret = '
	<dt><label>'. JText::_('DISCOUNT_TARGET_VENDOR') .'</label></dt>
	<dd>
		'.JHTML::_('hikaselect.radiolist', $values, 'data[discount][discount_target_vendor]' , 'onclick="hikamarket_discount_setVendor(this, true);" onchange="hikamarket_discount_setVendor(this, false);"', 'value', 'text', @$discount->discount_target_vendor).
		$popup->display(
			'', // $popupLinkData,
			'EDIT',
			hikamarket::completeLink('vendor&task=selection&single=true', true),
			'market_discount_set_vendor',
			760, 480, 'onclick="return hikamarket_discount_changeVendor(this);"', '', 'link'
		).
'<script type="text/javascript">
var hikamarket_discount_current_vendor = '.$vendor_id.';
function hikamarket_discount_setVendor(el, c) {
	var v = 0;
	if(el.value) v = parseInt(el.value);
	if((v == -2 && c) || (c && v > 1 && v == hikamarket_discount_current_vendor && el.checked)) {
		var p = document.getElementById("market_discount_set_vendor");
		hikamarket_discount_changeVendor(p);
	}
	if(c)
		hikamarket_discount_current_vendor = v;
}
function hikamarket_discount_changeVendor(el) {
	window.hikamarket.submitFct = function(data) {
		var d = document, w = window, o = w.Oby,
			el = d.getElementById("data[discount][discount_target_vendor]-1"),
			lbl = d.getElementById("data[discount][discount_target_vendor]-1-lbl");
		if(!el) el = d.getElementById("data_discount_discount_target_vendor-1");
		if(el) el.value = data.id;
		if(el && !el.checked) el.checked = "checked";
		if(!lbl) { lbl = el; while(lbl && lbl.nodeName.toLowerCase() != "label") { lbl = lbl.nextSibling; } }
		if(lbl) lbl.innerHTML = data.id + " - " + data.vendor_name;
	};
	window.hikamarket.openBox(el);
}
</script>
	</dd>';
		$html[] = $ret;
	}

	public function beforeCouponCheck(&$coupon, &$total, &$zones, &$products, &$display_error, &$error_message, &$do) {
		if(empty($coupon->discount_target_vendor) || (int)$coupon->discount_target_vendor == 1 || !$do)
			return;

		$vendor_products = array();
		foreach($products as &$product) {
			if((int)$product->product_vendor_id == (int)$coupon->discount_target_vendor || ((int)$product->product_vendor_id <= 1 && (int)$coupon->discount_target_vendor == -1)) {
				$vendor_products[] = &$product;
			}
		}

		if(empty($vendor_products)) {
			$error_message = JText::_('DISCOUNT_NO_VENDOR');
			$do = false;
			return;
		}

		$coupon_flat = (float)hikamarket::toFloat($coupon->discount_flat_amount);
		$coupon_percent = (float)hikamarket::toFloat($coupon->discount_percent_amount);

		if($coupon_flat > 0) {
			$shopConfig = hikamarket::config(false);
			$discount_before_tax = $shopConfig->get('discount_before_tax');

			$vendor_total = 0.0;
			foreach($vendor_products as $vendor_product) {
				if(isset($vendor_product->prices[0]->price_value_with_tax) && $discount_before_tax)
					$vendor_total += $vendor_product->prices[0]->price_value_with_tax;
				else
					$vendor_total += (float)@$vendor_product->prices[0]->price_value;
			}

			$currencyClass = hikamarket::get('shop.class.currency');
			if($coupon->discount_currency_id != $total->prices[0]->price_currency_id)
				$coupon_flat = $currencyClass->convertUniquePrice($coupon_flat, $coupon->discount_currency_id, $total->prices[0]->price_currency_id);

			if($vendor_total < $coupon_flat) {
				$error_message = JText::sprintf('ORDER_NOT_EXPENSIVE_ENOUGH_FOR_COUPON', $currencyClass->format($vendor_total, $total->prices[0]->price_currency_id));
				$do = false;
				return;
			}
		}

		$coupon->discount_coupon_product_only = 1;
		$coupon->all_products = false;

		if(!isset($coupon->old_products))
			$coupon->old_products = $products;
		$products = $vendor_products;
	}

	public function afterCouponCheck(&$coupon, &$total, &$zones, &$products, &$display_error, &$error_message, &$do) {
		if(empty($coupon->discount_target_vendor) || (int)$coupon->discount_target_vendor == 1)
			return;

		if(empty($coupon->products))
			$coupon->products = $products;

		if(!empty($coupon->old_products)) {
			$products = $coupon->old_products;
			unset($coupon->old_products);
		}
	}

	public function onSelectDiscount(&$product, &$discountsSelected, &$discounts, $zone_id, &$parent) {
		$vendor_id = (int)$product->product_vendor_id;
		if($vendor_id == 0 && !empty($parent))
			$vendor_id = (int)$parent->product_vendor_id;

		foreach($discountsSelected as &$discountSelected) {
			$this->recursiveSelectDiscount($vendor_id, $discountSelected);
		}
		unset($discountSelected);
	}

	protected function recursiveSelectDiscount($vendor_id, &$discounts) {
		foreach($discounts as $k => $v) {
			if(is_array($v)) {
				$this->recursiveSelectDiscount($vendor_id, $discounts[$k]);
				if(empty($discounts[$k]))
					unset($discounts[$k]);
			} else {
				$discount_target = (int)$v->discount_target_vendor;
				if($discount_target > 1 && $vendor_id != $discount_target)
					unset($discounts[$k]);
				if($discount_target == 0 && $vendor_id > 1)
					unset($discounts[$k]);
			}
		}
	}
}
