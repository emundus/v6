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
$form_key = empty($this->editing_variant) ? 'price' : 'variantprice';
?>
<div id="hikamarket_product_edit_<?php echo $form_key; ?>">
	<div class="hikamarket_ajax_loading_elem"></div>
	<div class="hikamarket_ajax_loading_spinner"></div>
<table class="hikamarket_product_prices table table-bordered table-striped" style="width:100%">
	<thead>
		<tr>
			<th class="title"><?php
				echo JText::_('PRICE');
			?></th>
<?php if($this->price_acls['quantity'] || $this->price_acls['acl'] || $this->price_acls['user']) { ?>
			<th class="title"><?php
				echo JText::_('RESTRICTIONS');
			?></th>
<?php } ?>
			<th style="text-align:center">
				<a class="hikabtn hikabtn-success hikabtn-mini" href="#" onclick="return window.productMgr.newPrice('<?php echo $form_key; ?>');"><i class="fas fa-plus"></i></a>
			</th>
		</tr>
	</thead>
	<tbody id="hikamarket_<?php echo $form_key; ?>_list">
<?php
$k = 0;
if(!empty($this->product->prices)) {
	foreach($this->product->prices as $i => $price) {
		if(empty($price->price_min_quantity))
			$price->price_min_quantity = 1;
		if(empty($price->price_id))
			continue;

		$this->price_num = $i;
		$this->price = $price;
?>
		<tr class="row<?php echo $k;?>" id="hikamarket_<?php echo $form_key; ?>_<?php echo $i; ?>" data-hkm-price="<?php echo (int)@$price->price_id; ?>"><?php
			echo $this->loadTemplate('price_entry');
		?></tr>
<?php
		$k = 1 - $k;
	}
}
?>
	</tbody>
</table>
</div>
<script type="text/javascript">
if(!window.productMgr)
	window.productMgr = {};
if(!window.productMgr.priceEdit)
	window.productMgr.priceEdit = {};
window.productMgr.priceEdit['<?php echo $form_key; ?>'] = <?php echo count($this->product->prices); ?>

if(!window.productMgr.updatePriceValue) {
window.productMgr.updatePriceValue = function(id, taxed, key) {
<?php if($this->price_acls['tax']){ ?>
	var d = document, o = window.Oby, conversion = '', elName = 'hikamarket_'+key+'_'+id, destName = elName;
	if(taxed) {
		elName += '_with_tax_edit'; destName += '_edit'; conversion = 1;
	} else {
		elName += '_edit'; destName += '_with_tax_edit'; conversion = 0;
	}

	var price = d.getElementById(elName).value,
		dest = d.getElementById(destName),
		taxElem = d.getElementById('dataproductproduct_tax_id'),
		tax_id = -1;
	if(taxElem)
		tax_id = taxElem.value;
<?php if(!empty($this->product->product_tax_id)) { ?>
	else
		tax_id = <?php echo (int)$this->product->product_tax_id; ?>;
<?php } ?>
	var url = '<?php echo str_replace('\'', '\\\'', hikamarket::completeLink('product&task=getprice&price={PRICE}&product_id='.$this->product->product_id.'&tax_id={TAXID}&conversion={CONVERSION}', true, false, true)); ?>';
	url = url.replace('{PRICE}', price).replace('{TAXID}', tax_id).replace('{CONVERSION}', conversion);
	o.xRequest(url, null, function(xhr, params) {
		dest.value = xhr.responseText;
	});
<?php } ?>
};
}

if(!window.productMgr.newPrice) {
window.productMgr.newPrice = function(key) {
	var d = document, w = window, o = w.Oby,
		idx = (w.productMgr.priceEdit[key]++),
		el = d.getElementById('hikamarket_' + key + '_' + idx);
	if(el)
		return window.productMgr.editPrice(null, key, idx, 0);

	var el = d.getElementById('hikamarket_' + key + '_list');
		tr = d.createElement('tr');
	tr.className = 'row0';
	tr.id = 'hikamarket_'+key+'_'+idx;
	tr.setAttribute('data-hkm-price', 0);

	el.appendChild(tr);
	return window.productMgr.editPrice(null, key, idx, 0);
};
}
if(!window.productMgr.editPrice) {
window.productMgr.editPrice = function(el, key, num, id, state) {
	var d = document, w = window, o = w.Oby,
		container = d.getElementById('hikamarket_product_edit_'+key),
		priceLine = d.getElementById('hikamarket_' + key + '_' + num);

	if(!priceLine) return false;
	if(container) o.addClass(container, 'hikamarket_ajax_loading');

	if(state === undefined || state === true) state = 1;
	if(state === false) state = 0;

	var url = '<?php echo hikamarket::completeLink('product&task=editprice&product_id='.$this->product->product_id, true, false, true); ?>',
		data = o.getFormData(priceLine) + '&' + o.encodeFormData({price_id:id,price_num:num,edition_state:state,formkey:key});
	if(key == 'variantprice' && w.productMgr.variantEdition && w.productMgr.variantEdition.current)
		data += '&variant_product_id=' + encodeURIComponent(w.productMgr.variantEdition.current);
	o.xRequest(url, {mode:'POST',data:data},function(xhr,params){
		var tr = document.createElement('tr'), cell = null;
		tr.innerHTML = xhr.responseText;
		priceLine.innerHTML = '';
		for(var i = tr.cells.length - 1; i >= 0; i--) {
			cell = tr.cells[0];
			tr.removeChild(cell);
			priceLine.appendChild(cell);
			cell = null;
		}
		window.Oby.updateElem(tr, xhr.responseText);
		tr = null;

		if(container) o.removeClass(container, 'hikamarket_ajax_loading');
	});
	return false;
};
}
if(!window.productMgr.updatePrice) {
window.productMgr.updatePrice = function(el, key, num, id) {
};
}
if(!window.productMgr.cpt)
	window.productMgr.cpt = {};
window.productMgr.cpt['<?php echo $form_key; ?>'] = <?php echo count(@$this->product->prices); ?>;
</script>
