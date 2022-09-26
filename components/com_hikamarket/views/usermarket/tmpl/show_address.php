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
if(hikamarket::acl('user/edit/address') && ($this->vendor->vendor_id <= 1)) {

	if(empty($this->ajax)) {
?>
<div id="hikamarket_user_addresses_default">
	<div class="hikamarket_ajax_loading_elem"></div>
	<div class="hikamarket_ajax_loading_spinner"></div>
	<dl class="hikam_options large">
		<dt><label for="hikamarket_default_billing_address_selector"><?php echo JText::_('HIKASHOP_SELECT_DEFAULT_BILLING_ADDRESS'); ?></label></dt>
		<dd><?php
			$current = 0;
			$values = array();
			if(!empty($this->addresses)) {
				foreach($this->addresses as $k => $address) {
					if(!in_array($address->address_type, array('billing', 'both', '')))
						continue;

					$addr = $this->addressClass->miniFormat($address, $this->fields['address']);
					$values[] = JHTML::_('select.option', $k, $addr);
					if(!empty($address->address_default))
						$current = $address->address_id;
				}
			}
			if(empty($values))
				$values = array(JHTML::_('select.option', '', JText::_('HIKAM_NO_ADDRESS')));
			echo JHTML::_('select.genericlist', $values, 'data[user][default_billing]', 'class="hikamarket_default_address_dropdown"', 'value', 'text', $current, 'hikamarket_default_billing_address_selector');
		?></dd>
	</dl>
	<dl class="hikam_options large">
		<dt><label for="hikamarket_default_shipping_address_selector"><?php echo JText::_('HIKASHOP_SELECT_DEFAULT_SHIPPING_ADDRESS'); ?></label></dt>
		<dd><?php
			$current = 0;
			$values = array();
			if(!empty($this->addresses)) {
				foreach($this->addresses as $k => $address) {
					if(!in_array($address->address_type, array('shipping', 'both', '')))
						continue;

					$addr = $this->addressClass->miniFormat($address, $this->fields['address']);
					$values[] = JHTML::_('select.option', $k, $addr);
					if(!empty($address->address_default))
						$current = $address->address_id;
				}
			}
			if(empty($values))
				$values = array(JHTML::_('select.option', '', JText::_('HIKAM_NO_ADDRESS')));
			echo JHTML::_('select.genericlist', $values, 'data[user][default_shipping]', 'class="hikamarket_default_address_dropdown"', 'value', 'text', $current, 'hikamarket_default_shipping_address_selector');
		?></dd>
	</dl>
</div>

<div id="hikamarket_user_addresses_show">
<?php
	}
?>
	<div class="hikamarket_ajax_loading_elem"></div>
	<div class="hikamarket_ajax_loading_spinner"></div>
<?php
	if(!empty($this->two_columns)) {
?>
<div class="hk-row-fluid">
	<div class="hkc-md-6">
		<h3><?php echo JText::_('HIKASHOP_BILLING_ADDRESSES'); ?></h3>
<?php
	}

	foreach($this->addresses as $address) {
		if(!empty($this->two_columns) && $address->address_type != 'billing')
			continue;
?>
	<div class="hikamarket_user_address address_selection" id="hikamarket_user_address_<?php echo $address->address_id; ?>">
<?php
		$this->address_id = (int)$address->address_id;
		$this->address = $address;
		$this->setLayout('address');
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
	<div class="hkc-md-6">
		<h3><?php echo JText::_('HIKASHOP_SHIPPING_ADDRESSES'); ?></h3>
<?php
		foreach($this->addresses as $address) {
			if($address->address_type != 'shipping')
				continue;
?>
	<div class="hikamarket_user_address address_selection" id="hikamarket_user_address_<?php echo $address->address_id; ?>">
<?php
			$this->address_id = (int)$address->address_id;
			$this->address = $address;
			$this->setLayout('address');
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

<script type="text/javascript">
if(!window.addressMgr) window.addressMgr = {};
window.addressMgr.loading = function(load, el) {
	var d = document, w = window, o = w.Oby;
	if(!el)
		el = 'hikamarket_user_addresses_show';
	el = d.getElementById(el);
	if(!el)
		return false;
	if(load || load === undefined)
		o.addClass(el, "hikamarket_ajax_loading");
	else
		o.removeClass(el, "hikamarket_ajax_loading");
};
window.addressMgr.get = function(elem, target) {
	var t = this;
	t.loading();
	window.Oby.xRequest(elem.getAttribute('href'), {update: target}, function(){
		t.loading(false);
	});
	return false;
};
window.addressMgr.form = function(elem, target) {
	var t = this;
	t.loading();
	var data = window.Oby.getFormData(target);
	window.Oby.xRequest(elem.getAttribute('href'), {update: target, mode: 'POST', data: data}, function(){
		t.loading(false);
	});
	return false;
};
window.addressMgr.new = function(type) {
	var t = this, w = window, o = w.Oby;
	t.loading();
	var data = o.encodeFormData({'address_type': type});
	o.xRequest('<?php echo hikamarket::completeLink('user&task=address&subtask=edit&cid=0&user_id='.$this->user_id, 'ajax', true); ?>', {update: 'hikamarket_user_addresses_show', mode: 'POST', data: data}, function(){
		t.loading(false);
	});
	return false;
};
window.addressMgr.delete = function(el, cid) {
	if(!confirm('<?php echo JText::_('HIKASHOP_CONFIRM_DELETE_ADDRESS', true); ?>'))
		return false;
	var t = this, w = window, o = w.Oby, d = document;
	t.loading();
	var data = o.encodeFormData({'<?php echo hikamarket::getFormToken(); ?>': 1});
	o.xRequest(el.href, {update: 'hikamarket_user_addresses_show', mode: 'POST', data: data}, function(xhr) {
		t.loading(false);
	});
	return false;
};
window.addressMgr.deleteAddr = function(cid) {
	var t = this, d = document;
	['billing','shipping'].forEach(function(atype){
		var el_sel = d.getElementById('hikamarket_default_' + atype + '_address_selector');
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
		var el_sel = d.getElementById('hikamarket_default_' + atype + '_address_selector');
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
		el_sel = d.getElementById('hikamarket_default_' + type + '_address_selector');
	if(!el_sel) return;
	var o = d.createElement('option');
	o.text = text;
	o.value = cid;
	el_sel.add(o);
};
</script>
<div class="clear_both"></div>
<?php
	}
} else {
?>
	<div class="hk-row-fluid">
<?php
	foreach(array('billing','shipping') as $type) {
?>
	<div class="hkc-md-6" id="hikamarket_user_<?php echo $type; ?>_addresses_show">
		<span class="hikamarket_address_title"><?php echo JText::_('HIKASHOP_'.strtoupper($type).'_ADDRESS'); ?></span>
<?php
		$show_only_current = (int)$this->config->get('show_only_current_address', 1);

		foreach($this->addresses as $address) {
			if($show_only_current && empty($address->address_default))
				continue;

			$address_css = '';
			if(!empty($address->address_default))
				$address_css = ' address_default';
?>
	<div class="hikamarket_user_address address_selection<?php echo $address_css; ?>" id="hikamarket_user_address_<?php echo $address->address_id; ?>">
<?php
		$this->address = $address;
		$this->setLayout('address');
		echo $this->loadTemplate();
?>
	</div>
<?php
		}
?>
	</div>
<?php
	}
?>
	</div>
<?php
}
