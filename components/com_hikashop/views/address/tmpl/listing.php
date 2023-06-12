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
if(empty($this->user_id))
	return;

if(empty($this->ajax)) {
?>
<div id="hikashop_address_listing">
<?php
echo $this->toolbarHelper->process($this->toolbar, $this->title);
?>
<div class="hikashop_address_listing_div">
<form action="<?php echo hikashop_completeLink('address'.$this->url_itemid); ?>" name="hikashop_user_address" method="post">

<div id="hikashop_user_addresses_default">
	<div class="hikashop_checkout_loading_elem"></div>
	<div class="hikashop_checkout_loading_spinner"></div>
	<dl class="hika_options large hikashop_default_billing_address">
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
						$current = $address->address_id;
				}
			}
			if(empty($values))
				$values = array(JHTML::_('select.option', '', JText::_('HIKA_NO_ADDRESS')));
			echo JHTML::_('select.genericlist', $values, 'data[user][default_billing]', 'class="'.HK_FORM_SELECT_CLASS.' hikashop_default_address_dropdown" onchange="window.addressMgr.setDefault(this, \'billing\');"', 'value', 'text', $current, 'hikashop_default_billing_address_selector');
		?></dd>
	</dl>
	<dl class="hika_options large hikashop_default_shipping_address">
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
						$current = $address->address_id;
				}
			}
			if(empty($values))
				$values = array(JHTML::_('select.option', '', JText::_('HIKA_NO_ADDRESS')));
			echo JHTML::_('select.genericlist', $values, 'data[user][default_shipping]', 'class="'.HK_FORM_SELECT_CLASS.' hikashop_default_address_dropdown" onchange="window.addressMgr.setDefault(this, \'shipping\');"', 'value', 'text', $current, 'hikashop_default_shipping_address_selector');
		?></dd>
	</dl>
</div>

<div id="hikashop_user_addresses_show">
<?php
}
?>
	<div class="hikashop_checkout_loading_elem"></div>
	<div class="hikashop_checkout_loading_spinner"></div>
<?php
if(!empty($this->two_columns)) {
?>
<div class="hk-row-fluid">
	<div class="hkc-md-6 hikashop_billing_addresses">
		<h3><?php echo JText::_('HIKASHOP_BILLING_ADDRESSES'); ?></h3>
<?php
}

foreach($this->addresses as $address) {
	if($this->two_columns && $address->address_type != 'billing')
		continue;
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

if(!empty($this->two_columns)) {
?>
		<div class="" style="margin-top:6px;">
			<a class="hikabtn hikabtn-success" href="#newAddress" onclick="return window.addressMgr.new('billing');"><i class="fa fa-plus"></i> <?php echo JText::_('HIKASHOP_NEW_BILLING_ADDRESS'); ?></a>
		</div>
	</div>
	<div class="hkc-md-6 hikashop_shipping_addresses">
		<h3><?php echo JText::_('HIKASHOP_SHIPPING_ADDRESSES'); ?></h3>
<?php
	foreach($this->addresses as $address) {
		if($address->address_type != 'shipping')
			continue;
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
			<a class="hikabtn hikabtn-success" href="#newAddress" onclick="return window.addressMgr.new('shipping');"><i class="fa fa-plus"></i> <?php echo JText::_('HIKASHOP_NEW_SHIPPING_ADDRESS'); ?></a>
		</div>
	</div>
</div>
<?php
} else {
?>
	<div class="" style="margin-top:6px;">
		<a class="hikabtn hikabtn-success" href="#newAddress" onclick="return window.addressMgr.new('billing');"><i class="fa fa-plus"></i> <?php echo JText::_('HIKASHOP_NEW_BILLING_ADDRESS'); ?></a>
		<a class="hikabtn hikabtn-success" href="#newAddress" onclick="return window.addressMgr.new('shipping');"><i class="fa fa-plus"></i> <?php echo JText::_('HIKASHOP_NEW_SHIPPING_ADDRESS'); ?></a>
	</div>
<?php
}

$new_cid = hikaRegistry::get('new_cid');
if(!empty($new_cid) && $this->ajax) {
	$data = array(
		'cid' => $new_cid,
		'type' => $this->addresses[$new_cid]->address_type,
		'miniFormat' => $this->addressClass->miniFormat($this->addresses[$new_cid]),
	);
	$previous_id = hikaRegistry::get('previous_cid');
	if($previous_id !== null)
		$data['previous_cid'] = $previous_id;
?>
<script type="text/javascript">
if(window.addressMgr.modifyAddr)
	window.addressMgr.modifyAddr(<?php echo json_encode($data); ?>);
</script>
<?php
}

$delete_cid = hikaRegistry::get('address_deleted_id');
if(!empty($delete_cid) && $delete_cid > 0 && $this->ajax) {
?>
<script type="text/javascript">
if(window.addressMgr.deleteAddr)
	window.addressMgr.deleteAddr(<?php echo (int)$delete_cid; ?>);
</script>
<?php
}

if(empty($this->ajax)) {
?>
</div>

	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="ctrl" value="address" />
	<input type="hidden" name="task" value="setdefault" />
	<?php echo JHTML::_('form.token'); ?>
</form>
<script type="text/javascript">
if(!window.addressMgr) window.addressMgr = {};
window.addressMgr.loading = function(load, el) {
	var d = document, w = window, o = w.Oby;
	if(!el)
		el = 'hikashop_user_addresses_show';
	el = d.getElementById(el);
	if(!el)
		return false;
	if(load || load === undefined)
		o.addClass(el, "hikashop_checkout_loading");
	else
		o.removeClass(el, "hikashop_checkout_loading");
};
window.addressMgr.get = function(elem, target) {
	var t = this;
	t.loading();
	window.hikashop.xRequest(elem.getAttribute('href'), {update: target}, function(){
		t.loading(false);
	});
	document.getElementById('hikashop_address_listing').scrollIntoView();
	return false;
};
window.addressMgr.setDefault = function(elem, type) {
	var t = this;
	t.loading(true, 'hikashop_user_addresses_default');
	window.hikashop.xRequest('<?php echo hikashop_completeLink('address&task=setdefault'.$this->url_itemid, 'ajax'); ?>', {mode: 'POST', data: 'address_default=' + elem.options[elem.selectedIndex].value + '&address_type=' + type + '&<?php echo hikashop_getFormToken(); ?>=1'}, function(){
		t.loading(false, 'hikashop_user_addresses_default');
	});
	return false;
};
window.addressMgr.form = function(elem, target) {
	var t = this;
	t.loading();
	var data = window.Oby.getFormData(target);
	window.hikashop.xRequest(elem.getAttribute('href'), {update: target, mode: 'POST', data: data}, function(){
		t.loading(false);
	});
	return false;
};
window.addressMgr.new = function(type) {
	var t = this, w = window, o = w.Oby;
	t.loading();
	var data = o.encodeFormData({'address_type': type});
	window.hikashop.xRequest('<?php echo hikashop_completeLink('address&task=edit&cid=0'.$this->url_itemid, 'ajax'); ?>', {update: 'hikashop_user_addresses_show', mode: 'POST', data: data}, function(){
		t.loading(false);
	});
	document.getElementById('hikashop_address_listing').scrollIntoView();
	return false;
};
window.addressMgr.delete = function(el, cid) {
	if(!confirm('<?php echo JText::_('HIKASHOP_CONFIRM_DELETE_ADDRESS', true); ?>'))
		return false;
	var t = this, w = window, o = w.Oby, d = document;
	t.loading();
	var data = o.encodeFormData({'<?php echo hikashop_getFormToken(); ?>': 1});
	window.hikashop.xRequest(el.href, {update: 'hikashop_user_addresses_show', mode: 'POST', data: data}, function(xhr) {
		t.loading(false);
	});
	return false;
};
window.addressMgr.deleteAddr = function(cid) {
	var t = this, d = document;
	['billing','shipping'].forEach(function(atype){
		var el_sel = d.getElementById('hikashop_default_' + atype + '_address_selector');
		if(!el_sel) return;
		for(var k in el_sel.options) {
			if(!el_sel.options.hasOwnProperty(k))
				continue;
			if(el_sel.options[k].value != cid)
				continue;
			el_sel.remove(k);
		}
	});
};
window.addressMgr.modifyAddr = function(params) {
	var t = this, d = document, f = false;
	['billing','shipping'].forEach(function(atype){
		var el_sel = d.getElementById('hikashop_default_' + atype + '_address_selector');
		if(!el_sel) return;

		for(var k in el_sel.options) {
			if(!el_sel.options.hasOwnProperty(k))
				continue;
			if(params.previous_cid && el_sel.options[k].value == params.previous_cid && params.previous_cid != 0 && params.previous_cid != params.cid)
				el_sel.options[k].value = params.cid;
			if(el_sel.options[k].value == params.cid) {
				el_sel.options[k].text = params.miniFormat;
				f = true;
			}
		}
	});
	if(f) return;
	if(params.type && (params.type == 'billing' || params.type == 'shipping')) {
		window.addressMgr.addEntry(params.type, params.cid, params.miniFormat);
	} else if(!params.type || params.type == '' || params.type == 'both') {
		window.addressMgr.addEntry('billing', params.cid, params.miniFormat);
		window.addressMgr.addEntry('shipping', params.cid, params.miniFormat);
	}
};
window.addressMgr.addEntry = function(type, cid, text) {
	var d = document,
		el_sel = d.getElementById('hikashop_default_' + type + '_address_selector');
	if(!el_sel) return;
	var o = d.createElement('option');
	o.text = text;
	o.value = cid;
	el_sel.add(o);
};
</script>
</div>
</div>
<div class="clear_both"></div>
<?php
}
