<?php
/**
 * @package	HikaShop for Joomla!
 * @version	3.0.1
 * @author	hikashop.com
 * @copyright	(C) 2010-2017 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
$form_key = 'price';
if(!empty($this->editing_variant))
	$form_key = 'variantprice';
?>
<table id="hikashop_product_<?php echo $form_key; ?>_table" class="adminlist table table-striped" style="width:100%">
	<thead>
		<tr>
			<th class="title"><?php
				echo JText::_('PRICE');
			?></th>
			<th class="title"><?php
				echo JText::_('RESTRICTIONS');
			?></th>
			<th style="width:60px;text-align:center">
				<a href="#" onclick="return window.productMgr.editPrice('<?php echo $form_key ?>', 0);"><img src="<?php echo HIKASHOP_IMAGES; ?>plus.png" alt="<?php echo JText::_('ADD'); ?>"></a>
			</th>
		</tr>
	</thead>
	<tfoot>
		<tr id="hikashop_<?php echo $form_key; ?>_edit_zone" style="display:none;">
		</tr>
	</tfoot>
	<tbody>
<?php
	$k = 0;
	foreach($this->product->prices as $i => $price) {
		if(empty($price->price_value))
			continue;
?>
		<tr class="row<?php echo $k ?>" id="price_<?php echo $price->price_id; ?>">
			<td style="white-space: nowrap;"><?php
				if(!$this->config->get('floating_tax_prices',0)){
					echo $this->currencyClass->format($price->price_value,$price->price_currency_id). ' / ';
				}
				echo $this->currencyClass->format($price->price_value_with_tax,$price->price_currency_id);
			?></td>
			<td>
<?php
		$restrictions = array();
		$qty = max((int)$price->price_min_quantity, 1);
		if($qty > 1)
			$restrictions[] = '<strong>'.JText::_('MINIMUM_QUANTITY').'</strong>: '.$qty;
		if(!empty($price->price_users)) {
			$users = explode(',',$price->price_users);
			$text = array();
			foreach($users as $user) {
				if($user) {
					$data = $this->userClass->get($user);
					if($data)
						$text[] = $data->name;
				}
			}
			$restrictions[] = '<strong>'.JText::_('USERS').'</strong>: '.implode(', ',$text);
		}
		if($price->price_access != 'all' && hikashop_level(2)) {
			$groups = $this->joomlaAcl->getList();
			$access = explode(',',$price->price_access);
			$text = array();
			foreach($access as $a){
				if(empty($a))
					continue;
				foreach($groups as $group){
					if($group->id == $a){
						$text[] = $group->text;
						break;
					}
				}
			}
			$restrictions[] = '<strong>'.JText::_('ACCESS_LEVEL').'</strong>: '.implode(', ', $text);
		}
		if(!empty($price->price_site_id))
			$restrictions[] = '<strong>'.JText::_('SITE_ID').'</strong>: '.$price->price_site_id;
		echo implode('<br/>',$restrictions);
?>
			<input type="hidden" name="<?php echo $form_key.'['.$i.'][price_access]'; ?>" value="<?php echo $price->price_access; ?>"/>
			<input type="hidden" name="<?php echo $form_key.'['.$i.'][price_users]'; ?>" value="<?php echo $price->price_users; ?>"/>
			<input type="hidden" name="<?php echo $form_key.'['.$i.'][price_min_quantity]'; ?>" value="<?php echo $qty; ?>"/>
			<input type="hidden" name="<?php echo $form_key.'['.$i.'][price_site_id]'; ?>" value="<?php echo $price->price_site_id; ?>"/>
			<input type="hidden" name="<?php echo $form_key.'['.$i.'][price_currency_id]'; ?>" value="<?php echo $price->price_currency_id; ?>"/>
			<input type="hidden" name="<?php echo $form_key.'['.$i.'][price_id]'; ?>" value="<?php echo $price->price_id;?>" />
			<input type="hidden" name="<?php echo $form_key.'['.$i.'][price_value]'; ?>" value="<?php if($this->config->get('floating_tax_prices',0)){ echo @$price->price_value_with_tax; }else{ echo @$price->price_value; } ?>" />
			</td>
			<td style="text-align:center">
				<a href="#edit" onclick="window.productMgr.editPrice('<?php echo $form_key ?>', <?php echo $price->price_id;?>); return false;"><img src="<?php echo HIKASHOP_IMAGES; ?>edit.png" alt="<?php echo JText::_('HIKA_EDIT'); ?>"></a>
				<a href="#delete" onclick="window.hikashop.deleteRow(this); return false;"><img src="<?php echo HIKASHOP_IMAGES; ?>delete.png" alt="<?php echo JText::_('HIKA_DELETE'); ?>"></a>
			</td>
		</tr>
<?php
		$k = 1 - $k;
	}
?>
		<tr id="hikashop_<?php echo $form_key; ?>_row_template" id="price_{ID}" class="row<?php echo $k ?>" style="display:none;">
			<td style="white-space: nowrap;">
				{PRICE}
			</td>
			<td>
				{RESTRICTIONS}
				<input type="hidden" name="{PRICE_ACCESS_INPUT_NAME}" value="{PRICE_ACCESS_VALUE}"/>
				<input type="hidden" name="{PRICE_USERS_INPUT_NAME}" value="{PRICE_USERS_VALUE}"/>
				<input type="hidden" name="{PRICE_QTY_INPUT_NAME}" value="{PRICE_QTY_VALUE}"/>
				<input type="hidden" name="{PRICE_SITE_INPUT_NAME}" value="{PRICE_SITE_VALUE}"/>
				<input type="hidden" name="{PRICE_CURRENCY_INPUT_NAME}" value="{PRICE_CURRENCY_VALUE}"/>
				<input type="hidden" name="{PRICE_ID_INPUT_NAME}" value="{PRICE_ID}"/>
				<input type="hidden" name="{PRICE_VALUE_INPUT_NAME}" value="{PRICE_VALUE}"/>
			</td>
			<td style="text-align:center">
				{EDIT_BUTTON}
				<a href="#delete" onclick="window.hikashop.deleteRow(this); return false;"><img src="<?php echo HIKASHOP_IMAGES; ?>delete.png" alt="<?php echo JText::_('HIKA_DELETE'); ?>"></a>
			</td>
		</tr>
	</tbody>
</table>
<script type="text/javascript">
if(!window.productMgr.priceEdition)
	window.productMgr.priceEdition = {};
<?php if(empty($this->product->product_id)) { ?>
window.hikashop.ready(function(){
	window.productMgr.editPrice('<?php echo $form_key ?>', 0);
});
<?php } ?>
window.productMgr.editPrice = function(formkey, pid) {
	var w = window, d = document, o = w.Oby, td = null,
		u = '<?php echo hikashop_completeLink('product&task=form_price_edit&price_id={ID}&formkey={FORMKEY}', true, false, true); ?>';

	if(window.productMgr.priceEdition[formkey+'_edit'])
		return false;
	if(window.productMgr.priceEdition[formkey])
		this.restorePriceRow(window.productMgr.priceEdition[formkey]);
	this.cancelNewPrice(formkey);
	if(pid > 0)
		this.disablePriceRow(pid);
	window.productMgr.priceEdition[formkey] = pid;

	el = d.getElementById('hikashop_' + formkey + '_edit_zone');
	if(!el)
		return false;

	window.productMgr.priceEdition[formkey+'_edit'] = true;
	el.style.display = '';

	o.xRequest(u.replace('{ID}', pid).replace('{FORMKEY}', formkey), {mode:"GET"}, function(x,p) {
		if(x.responseText == '') return;
		td = el.insertCell(0);
		td.colSpan = 3;
		if(typeof(hkjQuery) != "undefined") {
			hkjQuery(td).html(x.responseText);
			if(hkjQuery().chosen)
				hkjQuery('.hika_options select').chosen();
		} else {
			td.innerHTML = x.responseText;
		}
		window.productMgr.priceEdition[formkey+'_edit'] = false;
	});

	return false;
};
window.productMgr.disablePriceRow = function(id) {
	var d = document;
	el = d.getElementById('price_' + id);
	if(el)
		el.style.display = 'none';
	return;
};
window.productMgr.restorePriceRow = function(id) {
	var d = document;
	el = d.getElementById('price_' + id);
	if(el)
		el.style.display = '';
	return;
};
window.productMgr.cancelNewPrice = function(formkey) {
	var d = document;
	var el = d.getElementById('hikashop_' + formkey + '_edit_zone');
	if(!el)
		return false;
	el.style.display = 'none';
	el.innerHTML = '';
	return false;
};
window.productMgr.addPrice = function(formkey) {
	var w = window, d = document, o = w.Oby, id = null, qty = null, site = '', i = null,
		el = null, value = null, curr = null, row_id = false, users = null, userid = [], username = '', access = null, edit = '', price = '', restrictions = [];

	el = d.getElementById('hikashop_' + formkey + '_site_edit');
	if(el) site = el.value;
	el = d.getElementById('hikashop_' + formkey + '_qty_edit');
	if(el) qty = parseInt(el.value);
	el = d.getElementById('hikashop_' + formkey + '_id_edit');
	if(el) id = parseInt(el.value);

	if(id){
		el = d.getElementById('price_' + id);
		if(el){
			var tbody = el.parentNode;
			tbody.removeChild(el);
			var table = tbody.parentNode;
			w.hikashop.cleanTableRows(table);
		}
		row_id = 'price_' + id;
		edit = '<a href="#edit" onclick="window.productMgr.editPrice(\'' + formkey + '\',' + id + '); return false;"><img src="<?php echo HIKASHOP_IMAGES; ?>edit.png" alt="<?php echo JText::_('HIKA_EDIT', true); ?>"></a>';
	}

	el = d.getElementById('hikashop_' + formkey + '_currency_edit');
	if(el){
		currid = parseInt(el.options[el.selectedIndex].value);
		curr = el.options[el.selectedIndex].text;
	}
	el = d.getElementById('hikashop_' + formkey + '_acl_edit');
	if(el) access = el.value;

	el = d.getElementById('hikashop_' + formkey + '_edit');
	if(el) {
		value = parseFloat(el.value);
		if(isNaN(value))
			value = 0;
		price = value + ' ' + curr;

		el = d.getElementById('hikashop_' + formkey + '_with_tax_edit');
		if(el) {
			value_with_tax = parseFloat(el.value);
			if(isNaN(value_with_tax))
				value_with_tax = 0;
			price += ' / ' + value_with_tax + ' ' + curr;
		}
	}

	users = d.getElementsByName('hikashop_' + formkey + '_user_edit[]');
	var names = [];
	if(users && users.length) {
		userid.push('');
		for(var i = 0; i < users.length; i++) {
			userid.push(users[i].value);
			var usersList = w.oNameboxes['hikashop_' + formkey + '_user_edit'].data;
			usersList = Object.keys(usersList).map(function (key) { return usersList[key]; });
			for(var j = 0; j < usersList.length; j++) {
				if(usersList[j].user_id == users[i].value){
					names.push(usersList[j].name);
					break;
				}
			}
		}
		userid.push('');
	}

	if(isNaN(qty))
		qty = 1;
	if(qty > 1)
		restrictions.push('<strong><?php echo JText::_('MINIMUM_QUANTITY', true); ?></strong>: ' + qty);

	if(names.length)
		restrictions.push('<strong><?php echo JText::_('USERS', true); ?></strong>: ' + names.join(', '));
	if(access && access != 'all'){
		var groups = access.split(",");
		var length = groups.length;
		var text = [];
		for (var i = 0; i < length; i++) {
			if(groups[i] == '')
				continue;
			node = w['hikashop_' + formkey + '_acl_edit'].find(groups[i]);
			if(node)
				text.push(node.name);
		}
		restrictions.push('<strong><?php echo JText::_('ACCESS_LEVEL', true); ?></strong>: ' + text.join(', '));
	}
	if(site != '')
		restrictions.push('<strong><?php echo JText::_('SITE_ID', true); ?></strong>: ' + site);

	i = d.getElementById('hikashop_product_' + formkey + '_table').tBodies[0].rows.length;

	var htmlblocks = {
		PRICE: price, RESTRICTIONS: restrictions.join('<br/>'),
		PRICE_USERS_INPUT_NAME: formkey + '[' + i + '][price_users]', PRICE_USERS_VALUE: userid.join(','),
		PRICE_ACCESS_INPUT_NAME: formkey + '[' + i + '][price_access]', PRICE_ACCESS_VALUE: access,
		PRICE_QTY_INPUT_NAME: formkey + '[' + i + '][price_min_quantity]', PRICE_QTY_VALUE: qty,
		PRICE_SITE_INPUT_NAME: formkey + '[' + i + '][price_site_id]', PRICE_SITE_VALUE: site,
		PRICE_CURRENCY_INPUT_NAME: formkey + '[' + i + '][price_currency_id]', PRICE_CURRENCY_VALUE: currid,
		PRICE_ID_INPUT_NAME: formkey + '[' + i + '][price_id]', PRICE_ID: id,
		PRICE_VALUE_INPUT_NAME: formkey + '[' + i + '][price_value]', PRICE_VALUE: value,
		EDIT_BUTTON: edit
	};

	w.hikashop.dupRow('hikashop_' + formkey + '_row_template', htmlblocks, row_id);
	w.productMgr.cancelNewPrice(formkey);
	return false;
};

window.productMgr.updatePrice = function(taxed, key) {
	var d = document, o = window.Oby, conversion = '', elName = 'hikashop_' + key, destName = elName;
	if(taxed) {
		elName += '_with_tax_edit'; destName += '_edit'; conversion = 1;
	} else {
		elName += '_edit'; destName += '_with_tax_edit'; conversion = 0;
	}

	var price = d.getElementById(elName).value,
		dest = d.getElementById(destName),
		taxElem = d.getElementById('dataproductproduct_tax_id'),
		tax_id = -1;

	if(!dest)
		return;

	if(taxElem)
		tax_id = taxElem.value;
<?php if(!empty($this->product->product_tax_id)) { ?>
	else
		tax_id = <?php echo $this->product->product_tax_id; ?>;
<?php } ?>
	var url = '<?php echo str_replace('\'', '\\\'', hikashop_completeLink('product&task=getprice&price={PRICE}&tax_id={TAXID}&conversion={CONVERSION}', true, false, true)); ?>';
	url = url.replace('{PRICE}', price).replace('{TAXID}', tax_id).replace('{CONVERSION}', conversion);
	o.xRequest(url, null, function(xhr, params) {
		dest.value = xhr.responseText;
	});
};
</script>
