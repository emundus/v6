<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
if(empty($this->ajax)) {
	if($this->editable_order && hikamarket::acl('order/edit/products')) {
		$dropData = array(
			array(
				'name' => '<i class="fas fa-plus"></i> ' . JText::_('HIKAM_ADD_NEW_PRODUCT'),
				'link' => '#new-product',
				'click' => 'return window.orderMgr.addProduct(this);'
			)
		);
		echo $this->dropdownHelper->display(JText::_('HIKAM_EDIT'), $dropData, array('type' => '', 'mini' => true, 'class' => 'hikabtn-primary', 'right' => false, 'up' => false));

		hikamarket::loadJslib('otree');
	}
?>
<div id="hikamarket_order_block_products">
<?php
}

$showVendor = (hikamarket::level(1) && $this->order->order_type == 'sale' && $this->vendor->vendor_id <= 1);
?>
	<div class="hikamarket_ajax_loading_elem"></div>
	<div class="hikamarket_ajax_loading_spinner"></div>

	<table class="hikam_listing <?php echo (HIKASHOP_RESPONSIVE)?'table table-striped table-hover table-bordered':'hikam_table'; ?>" id="hikamarket_order_product_listing" style="width:100%">
		<thead>
			<tr>
				<th class="hikamarket_order_item_name_title title"><?php echo JText::_('PRODUCT'); ?></th>
				<th class="hikamarket_order_item_price_title title"><?php echo JText::_('UNIT_PRICE'); ?></th>
<?php if($showVendor){ ?>
				<th class="hikamarket_order_item_vendor_title title"><?php echo JText::_('HIKA_VENDOR'); ?></th>
<?php } ?>
				<th class="hikamarket_order_item_quantity_title title"><?php echo hikamarket::tooltip(JText::_('PRODUCT_QUANTITY'), '', '', JText::_('CART_PRODUCT_QUANTITY'), '', 0); ?></th>
				<th class="hikamarket_order_item_total_price_title title"><?php echo JText::_('PRICE'); ?></th>
			</tr>
		</thead>
		<tbody id="hikamarket_order_product_listing_content">
<?php

foreach($this->order->products as $k => $product) {

?>
		<tr id="hikamarket_order_product_<?php echo (int)$product->order_product_id; ?>"><?php
			$this->product = $product;
			echo $this->loadTemplate('block_product');
		?></tr>
<?php
}
?>
		</tbody>
	</table>
<?php

if(!empty($this->ajax))
	return;
?>
</div>
<?php

echo $this->popup->display(
	'',
	'HIKAM_SHOW_ORDER_PRODUCT',
	hikamarket::completeLink('shop.product&task=show&cid=0', true),
	'hikamarket_showproduct_popup',
	750, 460, 'style="display:none;"', '', 'link'
);
?>
<script type="text/javascript">
window.Oby.registerAjax('orderMgr.products',function(params){ window.orderMgr.refreshBlock('products'); });

window.orderMgr.showProduct = function(el) { return this.showEl(el, 'hikamarket_showproduct_popup'); };
<?php if(hikamarket::acl('order/edit/products')) { ?>
window.orderMgr.updateProductLine = function(e, data) {
	var tr = document.createElement('tr'), cell = null;
	tr.innerHTML = data;
	e.innerHTML = '';
	for(var i = tr.cells.length - 1; i >= 0; i--) {
		cell = tr.cells[0];
		tr.removeChild(cell);
		e.appendChild(cell);
		cell = null;
	}
	window.Oby.updateElem(tr, data);
	tr = null;
};
window.orderMgr.refreshProduct = function(el, id, edit) {
	var d = document, o = window.Oby,
		c = null, e = d.getElementById('hikamarket_order_product_' + id);
	if(!e) return false;

	if(id == 0) {
		e.parentNode.removeChild(e);
		return false;
	}

	c = d.getElementById('hikamarket_order_block_products');
	if(c) o.addClass(c, "hikamarket_ajax_loading");

	var url = '<?php echo hikamarket::completeLink('order&task=showblock&cid='.(int)$this->order->order_id.'&tmpl=ajax', false, false, true); ?>',
		params = { mode: "POST", data: o.encodeFormData({'block': edit ? 'edit_product' : 'product', 'pid': id}) };
	o.xRequest(url, params, function(x,p) {
		window.orderMgr.updateProductLine(e, x.responseText);
		if(c) o.removeClass(c, "hikamarket_ajax_loading");
	});
	return false;
};
window.orderMgr.submitProduct = function(el, id) {
	var d = document, o = window.Oby,
		c = null, e = d.getElementById('hikamarket_order_product_' + id);
	if(!e) return false;

	c = d.getElementById('hikamarket_order_block_products');
	if(c) o.addClass(c, "hikamarket_ajax_loading");
	var url = '<?php echo hikamarket::completeLink('order&task=submitblock&block=product&cid='.(int)$this->order->order_id.'&pid=HIKAPID&'.hikamarket::getFormToken().'=1&tmpl=ajax', false, false, true); ?>'.replace("HIKAPID", id),
		formData = o.getFormData(e);
	o.xRequest(url, {mode:"POST", data: formData}, function(x,p) {
		if(x.responseText != "0") {
			window.orderMgr.updateProductLine(e, x.responseText);

			if(id == 0) {
				var cell = e.firstChild, pid = 0;
				if(cell) pid = parseInt(cell.getAttribute('data-order-product-id'));
				if(pid !== null && pid !== NaN)
					e.id = "hikamarket_order_product_" + pid;
			}
		}
		if(c) o.removeClass(c, "hikamarket_ajax_loading");
	});
	return false;
};
window.orderMgr.addProduct = function(el) {
	var d = document, o = window.Oby,
		c = null, e = d.getElementById('hikamarket_order_product_0');
	if(e) return false;

	e = d.getElementById('hikamarket_order_product_listing_content');
	if(!e) return false;

	c = d.getElementById('hikamarket_order_block_products');
	if(c) o.addClass(c, "hikamarket_ajax_loading");

	var url = '<?php echo hikamarket::completeLink('order&task=showblock&block=edit_product&cid='.(int)$this->order->order_id.'&pid=0&tmpl=ajax', false, false, true); ?>';
	o.xRequest(url, null, function(x,p) {
		var tr = document.createElement('tr');
		tr.id = 'hikamarket_order_product_0';
		e.appendChild(tr);

		e = d.getElementById('hikamarket_order_product_0');
		window.orderMgr.updateProductLine(e, x.responseText);
		if(c) o.removeClass(c, "hikamarket_ajax_loading");
	});
	return false;

};
window.orderMgr.deleteProduct = function(el, id, hash) {
	var d = document, o = window.Oby,
		c = null, e = d.getElementById('hikamarket_order_product_' + id);
	if(!e) return false;

	if(!confirm('<?php echo JText::_('HIKAM_CONFIRM_DELETE_ORDER_PRODUCT', true); ?>'))
		return false;

	c = d.getElementById('hikamarket_order_block_products');
	if(c) o.addClass(c, "hikamarket_ajax_loading");

	var url = '<?php echo hikamarket::completeLink('order&task=submitblock&block=delete_product&cid='.(int)$this->order->order_id.'&'.hikamarket::getFormToken().'=1&tmpl=ajax', false, false, true); ?>',
		params = { mode: "POST", data: o.encodeFormData({'pid': id, 'product_hash': hash}) };
	o.xRequest(url, params, function(x,p) {
		if(x.responseText == '1') {
			e.parentNode.removeChild(e);
			o.fireAjax('orderMgr.details');
			o.fireAjax('orderMgr.history');
		} else {
			console.log(['product delete error', x.responseText]);
		}
		if(c) o.removeClass(c, "hikamarket_ajax_loading");
	});

	return false;
};
window.orderMgr.loadProductData = function(id, product) {
	var d = document, o = window.Oby,
		c = null, e = d.getElementById('hikamarket_order_product_' + id);
	if(!e) return false;

	if(product === undefined || product === null) {
		var n = window.oNameboxes['order_products_' + id + '_id'];
		if(!n)
			return false;
		var data = n.get();
		if(data && data.value)
			product = data.value
	}
	if(!product || product <= 0)
		return false;

	var url = '<?php echo hikamarket::completeLink('order&task=product_data&cid='.(int)$this->order->order_id.'&'.hikamarket::getFormToken().'=1&tmpl=ajax', false, false, true); ?>',
		field_name = 'hikamarket_order_<?php echo $this->order->order_id; ?>_orderproduct_' + id + '_',
		params = { mode: "POST", data: o.encodeFormData({'order_product': id, 'product': product}) },
		field = d.getElementById(field_name + 'qty'), qty = 0,
		setField = function(k, v) {
			var el = d.getElementById(field_name + k);
			if(!el) return;
			el.value = v;
			if(k == 'value') o.fireEvent(el, 'change');
		};
	if(field && field.value)
		qty = parseInt(field.value);
	if(qty !== NaN && qty > 0)
		params.data = o.encodeFormData({'order_product': id, 'product': product, 'qty': qty});

	o.xRequest(url, params, function(x,p) {
		var ret = o.evalJSON( x.responseText );
		if(!ret) return;
		if(ret.name) setField('name', ret.name);
		if(ret.code) setField('code', ret.code);
		if(ret.tax) setField('tax_namekey', ret.tax);
		if(ret.price) setField('value', ret.price);
		if(ret.vendor) {
			var n = window.oNameboxes[field_name + 'vendor'];
			if(n) n.set(ret.vendor.name, ret.vendor.id);
		}
		if(ret.vendorprice) setField('vendorprice', ret.vendorprice);
	});

	return false;
};
<?php } ?>
</script>
