<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><dl class="hika_options large">
	<dt><label for="hikashop_default_billing_address_selector"><?php echo JText::_('HIKASHOP_SELECT_DEFAULT_BILLING_ADDRESS'); ?></label></dt>
	<dd><?php
		$current = 0;
		$values = array();
		if(!empty($this->addresses)) {
			if(empty($this->addressClass))
				$this->addressClass = hikashop_get('class.address');

			foreach($this->addresses as $k => $address) {
				if(!in_array($address->address_type, array('billing', 'both', '')))
					continue;

				$addr = $this->addressClass->miniFormat($address);
				$values[] = JHTML::_('select.option', $k, $addr);
				if(!empty($address->address_default))
					$current = $address->address_default;
			}
		}
		if(empty($values))
			$values = array(JHTML::_('select.option', '', JText::_('HIKA_NO_ADDRESS')));
		echo JHTML::_('select.genericlist', $values, 'data[user][default_billing]', 'class="hikashop_default_address_dropdown"', 'value', 'text', $current, 'hikashop_default_billing_address_selector');
	?></dd>
</dl>
<dl class="hika_options large">
	<dt><label for="hikashop_default_shipping_address_selector"><?php echo JText::_('HIKASHOP_SELECT_DEFAULT_SHIPPING_ADDRESS'); ?></label></dt>
	<dd><?php
		$current = 0;
		$values = array();
		if(!empty($this->addresses)) {
			if(empty($this->addressClass))
				$this->addressClass = hikashop_get('class.address');

			foreach($this->addresses as $k => $address) {
				if(!in_array($address->address_type, array('shipping', 'both', '')))
					continue;

				$addr = $this->addressClass->miniFormat($address);
				$values[] = JHTML::_('select.option', $k, $addr);
				if(!empty($address->address_default))
					$current = $address->address_default;
			}
		}
		if(empty($values))
			$values = array(JHTML::_('select.option', '', JText::_('HIKA_NO_ADDRESS')));
		echo JHTML::_('select.genericlist', $values, 'data[user][default_shipping]', 'class="hikashop_default_address_dropdown"', 'value', 'text', $current, 'hikashop_default_shipping_address_selector');
	?></dd>
</dl>

	<div id="hikashop_user_addresses_show">
<?php
foreach($this->addresses as $address) {
?>
	<div class="hikashop_user_address address_selection" id="hikashop_user_address_<?php echo $address->address_id; ?>">
<?php
	$this->address_id = (int)$address->address_id;
	$this->address = $address;
	$this->setLayout('show');
	echo $this->loadTemplate();
?>
	</div>
<?php
}
?>
		<div class="" style="margin-top:6px;">
			<a class="btn btn-success" href="#newAddress" onclick="return window.addressMgr.new();"><?php echo JText::_('HIKA_NEW'); ?></a>
		</div>
	</div>
	<div id="hikashop_user_addresses_edition">
	</div>
<script type="text/javascript">
if(!window.addressMgr) window.addressMgr = {};
window.Oby.registerAjax('hikashop_address_changed', function(params) {
	if(!params) return;

	var d = document,
		el_show = d.getElementById('hikashop_user_addresses_show'),
		el_edit = d.getElementById('hikashop_user_addresses_edition');

	if(params.edit) {
		el_show.style.display = 'none';
		el_edit.style.display = '';
		return;
	}
	if(el_edit.children.length == 0)
		return;

	var target_id = params.previous_cid || params.cid,
		target = d.getElementById('hikashop_user_address_' + target_id),
		el_sel = d.getElementById('hikashop_default_address_selector');
		content = el_edit.innerHTML;

	el_edit.style.display = 'none';
	el_edit.innerHTML = '';

	for(var k in el_sel.options) {
		if(params.previous_cid && el_sel.options[k].value == params.previous_cid && params.previous_cid != 0 && params.previous_cid != params.cid)
			el_sel.options[k].value = params.cid;
		if(el_sel.options[k].value == params.cid)
			el_sel.options[k].text = params.miniFormat;
	}
	if(params.previous_cid !== undefined && params.previous_cid === 0) {
		var o = d.createElement('option');
		o.text = params.miniFormat;
		o.value = params.cid;
		el_sel.add(o, el_sel.options[el_sel.selectedIndex]);
		el_sel.selectedIndex--;
		o.fireEvent(el_sel,'change');
	}
	if(jQuery) jQuery(el_sel).trigger("liszt:updated");

	if(target) {
		target.innerHTML = content;
	} else if(params.cid > 0) {
		window.hikashop.dup('hikashop_user_address_template', {'VALUE':params.cid, 'CONTENT':content}, 'hikashop_user_address_'+params.cid);
	}

	el_show.style.display = '';
});
window.addressMgr.new = function() {
	var d = document, w = window, o = w.Oby,
		el_show = d.getElementById('hikashop_user_addresses_show'),
		el_edit = d.getElementById('hikashop_user_addresses_edition');
	if(!el_edit || !el_show)
		return false;
	el_edit.innerHTML = '';
	var url = '<?php echo hikashop_completeLink('address&task=edit&cid=0&user_id='.$this->user_id, 'ajax', true); ?>';
	w.hikashop.xRequest(url, {update:el_edit}, function(xhr) {
		el_show.style.display = 'none';
		el_edit.style.display = '';
	});
	return false;
};
window.addressMgr.delete = function(el, cid) {
	if(!confirm('<?php echo JText::_('HIKASHOP_CONFIRM_DELETE_ADDRESS', true); ?>'))
		return false;
	var w = window, o = w.Oby, d = document;
	o.xRequest(el.href, {mode: 'POST', data: '<?php echo hikashop_getFormToken(); ?>=1'}, function(xhr) { if(xhr.status == 200) {
		var target = d.getElementById('hikashop_user_address_' + cid);
		if(xhr.responseText == '1') {
			if(target)
				target.parentNode.removeChild(target);
			var el_sel = d.getElementById('hikashop_default_address_selector');
			for(var k in el_sel.options) {
				if(el_sel.options[k].value == cid) {
					el_sel.remove(k);
					break;
				}
			}
			o.fireEvent(el_sel,'change');
			if(jQuery) jQuery(el_sel).trigger("liszt:updated");
			o.fireAjax('hikashop_address_deleted',{'cid':cid,'uid':target,'el':el});
		} else if(xhr.responseText != '0') {
			if(target) window.hikashop.updateElem(target, xhr.responseText);
		}
	}});
	return false;
};
</script>
