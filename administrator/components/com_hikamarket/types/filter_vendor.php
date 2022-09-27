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
class hikamarketFilter_vendorType {

	protected $values = array();

	public function __construct() {
		$this->app = JFactory::getApplication();
	}

	protected function load($value) {
		$this->values = array();
		$db = JFactory::getDBO();

		$query = 'SELECT COUNT(*) FROM '.hikamarket::table('vendor').' WHERE vendor_published = 1';
		$db->setQuery($query);
		$ret = (int)$db->loadResult();
		if($ret > 15) {
			$this->values = $ret;
			return;
		}

		$query = 'SELECT * FROM '.hikamarket::table('vendor').' WHERE vendor_published = 1 ORDER BY vendor_name, vendor_id';
		$db->setQuery($query);
		$vendors = $db->loadObjectList();
		$this->values = array(
			JHTML::_('select.option', 0, JText::_('ALL_VENDORS')),
			JHTML::_('select.option', 1, JText::_('HIKAM_MY_VENDOR')),
		);
		if(!empty($vendors)) {
			foreach($vendors as $vendor) {
				if($vendor->vendor_id == 0 || $vendor->vendor_id == 1)
					continue;
				$this->values[] = JHTML::_('select.option', $vendor->vendor_id, $vendor->vendor_name . ' [' . $vendor->vendor_id . ']');
			}
		}
	}

	protected function initJs() {
		static $jsInit = null;
		if($jsInit === true)
			return;

		$vendor_format = 'data.vendor_name';
		if($this->app->isAdmin())
			$vendor_format = 'data.id + " - " + data.vendor_name';

		$js = '
if(!window.localPage)
	window.localPage = {};
window.localPage.filterChooseVendor = function(el, name) {
	window.hikamarket.submitFct = function(data) {
		var d = document,
			vendorInput = d.getElementById(name + "_input_id"),
			vendorSpan = d.getElementById(name + "_span_id");
		if(vendorInput) { vendorInput.value = data.id; }
		if(vendorSpan) { vendorSpan.innerHTML = '.$vendor_format.'; }
		if(d.adminForm)
			d.adminForm.submit();
		else {
			var f = d.getElementById("adminForm");
			if(!f) f = d.getElementById("hikamarketForm");
			if(!f && el.form) f = el.form;
			if(f) f.submit();
		}
	};
	window.hikamarket.openBox(el,null,(el.getAttribute("rel") == null));
	return false;
};
window.localPage.filterSetVendor = function(el, name, value) {
	var d = document,
		vendorInput = d.getElementById(name + "_input_id"),
		vendorSpan = d.getElementById(name + "_span_id");
	if(vendorInput) { vendorInput.value = value; }
	if(vendorSpan) {
		if(value == 0)
			vendorSpan.innerHTML = "'.JText::_('NO_VENDOR', true).'";
		else
			vendorSpan.innerHTML = "'.JText::_('ALL_VENDORS', true).'";
	}
	if(d.adminForm)
		d.adminForm.submit();
	else {
		var f = d.getElementById("adminForm");
		if(!f) f = d.getElementById("hikamarketForm");
		if(!f && el.form) f = el.form;
		if(f) f.submit();
	}
};
';
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration($js);

		$jsInit = true;
	}

	public function display($map, $value, $invoicemap = '', $invoicevalue = 0) {
		if(empty($this->values))
			$this->load($value);
		if(is_array($this->values)) {
			$ret = JHTML::_('select.genericlist', $this->values, $map, 'class="inputbox" size="1" data-search-reset="-1" onchange="document.adminForm.submit();"', 'value', 'text', $value);
		} else {
			$uuid = str_replace(array('][','[',']'), '_', $map);
			$nameboxType = hikamarket::get('type.namebox');
			$ret = '<div style="display:inline-block;min-width:240px;vertical-align:top;">'.
				$nameboxType->display(
					$map,
					(int)$value,
					hikamarketNameboxType::NAMEBOX_SINGLE,
					'vendor',
					array(
						'delete' => true,
						'id' => $uuid,
						'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>'
					)
				) .
				'<script>
window.Oby.ready(function(){ window.oNameboxes["'.$uuid.'"].register("set", function(e){ document.getElementById("'.$uuid.'_text").form.submit(); }); });
</script>'.
				'</div>';
		}

		if($value > 1 && !empty($invoicemap)) {
			$choices = array(
				JHTML::_('select.option', 0, JText::_('VENDOR_SALES')),
				JHTML::_('select.option', 1, JText::_('VENDOR_INVOICES')),
			);
			$ret .= JHTML::_('select.genericlist', $choices, $invoicemap, 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $invoicevalue);
		}

		return $ret;
	}
}
