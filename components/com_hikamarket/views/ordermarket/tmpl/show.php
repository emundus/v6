<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><script type="text/javascript">
<!--
if(!window.orderMgr)
	window.orderMgr = {};
window.orderMgr.customer_id = <?php echo (int)$this->order->order_user_id; ?>;
window.orderMgr.showEl = function(el, sel) {
	window.hikamarket.submitFct = function(data) { window.hikamarket.closeBox(); };
	var href = el.getAttribute('data-popup-href');
	if(!href) href = el.getAttribute('href');
	window.hikamarket.openBox(sel, href);
	return false;
};
window.orderMgr.toggleDisplay = function(el) {
	var d = document, block = el.getAttribute('data-toggle-display'), e = d.getElementById(block);
	if(!e) return false;
	e.style.display = (e.style.display == 'none') ? '' : 'none';
	el.blur();
	return false;
};
window.orderMgr.refreshBlock = function(type, edit, prefix) {
	var d = document, w = window, o = w.Oby, el = null;
	if(prefix !== undefined) el = d.getElementById("hikamarket_order_" + prefix + "_" + type);
	if(edit && !el) el = d.getElementById("hikamarket_order_edit_" + type);
	if(!el) el = d.getElementById("hikamarket_order_block_" + type);
	if(!el) return false;
	o.addClass(el, "hikamarket_ajax_loading");
	var url = '<?php echo hikamarket::completeLink('order&task=showblock&block=HIKATYPE&cid='.(int)$this->order->order_id.'&tmpl=ajax', false, false, true); ?>'.replace("HIKATYPE", (edit ? 'edit_' : '') + type);
	o.xRequest(url, {update: el}, function(x,p) {
		o.removeClass(el, "hikamarket_ajax_loading");
	});
	return false;
};
window.orderMgr.submitBlock = function(type, opt, cb, prefix) {
	var url = null, formData = '', d = document, w = window, o = w.Oby, el = null;
	if(prefix !== undefined) el = d.getElementById("hikamarket_order_" + prefix + "_" + type);
	if(!el) el = d.getElementById("hikamarket_order_edit_" + type);
	if(!el) el = d.getElementById("hikamarket_order_block_" + type);
	if(!el) return false;

	if(!opt || opt.data === undefined || !opt.data) {
		formData = o.getFormData(el);
	} else if(opt.data) {
		formData = o.encodeFormData(opt.data);
	}
	o.addClass(el, "hikamarket_ajax_loading");
	url = '<?php echo hikamarket::completeLink('order&task=submitblock&block=HIKATYPE&cid='.(int)$this->order->order_id.'&'.hikamarket::getFormToken().'=1&tmpl=ajax', false, false, true); ?>'.replace("HIKATYPE", type);
	var params = {mode:"POST", data:formData};
	if(opt && opt.update) {
		params.update = (opt.update === true) ? el : opt.update;
	}
	o.xRequest(url, params, function(x,p) {
		o.removeClass(el, "hikamarket_ajax_loading");
		if(cb) cb(x,p);
	});
	return false;
};
window.orderMgr.updateTaxValueFields = function(key) {
	var d = document, rate = 0.0, v = 0.0,
		el = d.getElementById(key+'_value'),
		s = d.getElementById(key+'tax_namekey');

	if(!s) s = d.getElementById(key+'_tax_namekey');
	if(!s) return;
	rate = s.options[ s.selectedIndex ];
	if(!rate) return;
	try {
		rate = parseFloat(rate.getAttribute('data-rate'));
	} catch(e) { return; }

	try {
		v = parseFloat( Oby.trim(el.value) );
		if(isNaN(v)) v = 0.0;
	} catch(e) { v = 0.0; }

	var n = v - (v / (1 + rate));
	s = d.getElementById(key+'_tax');
	if(s) s.value = parseFloat(n.toFixed(5));

	s = d.getElementById(key+'_value_tax');
	if(s) s.innerHTML = parseFloat(n.toFixed(5));

	n = (v / (1 + rate));
	s = d.getElementById(key+'_value_price');
	if(s) s.innerHTML = parseFloat(n.toFixed(5));
};
window.orderMgr.editOrderStatus = function(el) {
	window.hikamarket.submitFct = function(data) {
		var orderstatus = document.getElementById('hikamarket_order_status');
		if(orderstatus) {
			orderstatus.innerHTML = data.name;
			orderstatus.className = 'order-label order-label-' + data.order_status.replace(/[^a-z_0-9]/i, '_');
		}
		window.hikamarket.closeBox();
	};
	var href = el.getAttribute('href');
	if(href == '' || href == null || href == '#')
		href = null;
	window.hikamarket.openBox('hikamarket_order_status_popup', href);
	return false;
};
//-->
</script>
<div class="hikamarket_order">
	<h2 style="display:inline-block"><?php
		echo JText::sprintf('HIKAM_ORDER', $this->order->order_number);
	?></h2>
	<span id="hikamarket_order_status" style="margin-left:7px;font-size:1.2em;line-height:1.3em;" class="order-label order-label-<?php echo preg_replace('#[^a-z_0-9]#i', '_', str_replace(' ','_',$this->order->order_status)); ?>"><?php echo hikamarket::orderStatus($this->order->order_status); ?></span>

<!-- GENERAL -->
<?php
echo $this->loadTemplate('block_general');
?>

<!-- ADDITIONAL -->
	<h3 style="display:inline-block"><?php echo JText::_('ORDER_DETAILS')?></h3>
<?php

echo $this->loadTemplate('block_details');

if(!empty($this->fields['order']) && hikamarket::acl('order/show/customfields')) {
	echo $this->loadTemplate('block_fields');
}

?>

<!-- CUSTOMER -->
<?php if(hikamarket::acl('order/show/customer')) { ?>
	<h3 style="display:inline-block"><?php echo JText::_('CUSTOMER')?></h3>
<?php
	if($this->editable_order && hikamarket::acl('order/edit/customer')) {
		echo $this->loadTemplate('block_edit_customer');
	}

	echo $this->loadTemplate('block_customer');
}
?>

<!-- ADDRESSES -->
<?php
if(hikamarket::acl('order/show/billingaddress') || hikamarket::acl('order/show/shippingaddress')) {
	$acl_billing = hikamarket::acl('order/show/billingaddress');
	$acl_shipping = hikamarket::acl('order/show/shippingaddress');
	$cell_class = ($acl_billing && $acl_shipping) ? 'hkc-md-6' : 'hkc-md-12';
?>
	<div class="hk-row-fluid hikamarket_order_addresses">
<?php
	if($acl_billing) {
?>
		<div class="<?php echo $cell_class; ?> hikamarket_order_billing_address">
			<h3 style="display:inline-block;"><?php echo JText::_('HIKASHOP_BILLING_ADDRESS')?></h3>
<?php
		$this->block_edit_address = 'billing';
		echo $this->loadTemplate('block_edit_address');

		$this->block_show_address = 'billing';
		echo $this->loadTemplate('block_address');
?>
		</div>
<?php
	}
?>

<?php
	if($acl_shipping) {
		$display_shipping_addr = empty($this->order->order_shipping_id) ? 'display:none;' : '';
?>
		<div class="<?php echo $cell_class; ?> hikamarket_order_shipping_address" style="<?php echo $display_shipping_addr; ?>">
			<h3 style="display:inline-block;"><?php echo JText::_('HIKASHOP_SHIPPING_ADDRESS')?></h3>
<?php
		if(empty($this->order->override_shipping_address)) {
			$this->block_edit_address = 'shipping';
			echo $this->loadTemplate('block_edit_address');
		}

		$this->block_show_address = 'shipping';
		echo $this->loadTemplate('block_address');
?>
		</div>
<?php
	}
?>
	</div>
<?php } ?>

<!-- PRODUCTS -->
	<h3 style="display:inline-block"><?php echo JText::_('PRODUCT_LIST')?></h3>
<?php
echo $this->loadTemplate('block_products');
?>

<?php
if(hikamarket::level(1) && $this->order->order_type == 'sale' && hikamarket::acl('order/show/vendors')) {
	echo $this->loadTemplate('block_vendors');
}
?>

<!-- OTHER COMPONENTS -->
<?php
JFactory::getApplication()->triggerEvent('onAfterOrderProductsListingDisplay', array(&$this->order, 'order_frontmarket_show'));
?>

<!-- HISTORY -->
<?php
if(hikamarket::acl('order/show/history')) {
	echo $this->loadTemplate('block_history');
}
?>
</div>
