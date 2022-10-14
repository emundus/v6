<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><script type="text/javascript">
window.characteristicMgr = {};
window.characteristicMgr.cpt = {};
</script>
<form action="<?php echo hikamarket::completeLink('characteristic');?>" method="post" name="adminForm" id="adminForm">
	<dl class="hikam_options large">
		<dt class="hikamarket_characteristic_name"><label><?php echo JText::_('HIKA_NAME'); ?></label></dt>
		<dd class="hikamarket_characteristic_name">
<?php if(hikamarket::acl('characteristic/edit/value') && $this->editable_characteristic) { ?>
			<input type="text" size="45" name="data[characteristic][characteristic_value]" value="<?php echo $this->escape(@$this->characteristic->characteristic_value); ?>" />
<?php } else { ?>
			<span><?php echo $this->escape(@$this->characteristic->characteristic_value); ?></span>
<?php } ?>
		</dd>
<?php if(hikamarket::acl('characteristic/edit/alias') && $this->editable_characteristic) { ?>
		<dt class="hikamarket_characteristic_alias"><label><?php echo JText::_('HIKA_ALIAS'); ?></label></dt>
		<dd class="hikamarket_characteristic_alias"><input type="text" size="45" name="data[characteristic][characteristic_alias]" value="<?php echo $this->escape(@$this->characteristic->characteristic_alias); ?>" /></dd>
<?php } ?>
<?php if($this->vendor->vendor_id <= 1 && hikamarket::acl('characteristic/edit/vendor')) { ?>
		<dt class="hikamarket_characteristic_vendor"><label><?php echo JText::_('HIKA_VENDOR'); ?></label></dt>
		<dd class="hikamarket_characteristic_vendor"><?php
			echo $this->nameboxType->display(
				'data[characteristic][characteristic_vendor_id]',
				@$this->characteristic->characteristic_vendor_id,
				hikamarketNameboxType::NAMEBOX_SINGLE,
				'vendor',
				array(
					'delete' => true,
					'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>'
				)
			);
		?></dd>
<?php } ?>
<?php if(hikamarket::acl('characteristic/edit/display') && in_array($this->shopConfig->get('characteristic_display'), array('dropdown','radio'))) { ?>
		<dt class="hikamarket_characteristic_display"><label><?php echo JText::_('CHARACTERISTIC_DISPLAY_MODE'); ?></label></dt>
		<dd class="hikamarket_characteristic_display"><?php
			echo $this->characteristicdisplayType->display('data[characteristic][characteristic_display_method]', @$this->characteristic->characteristic_display_method, 'characteristic');
		?></dd>
<?php } ?>
<?php if(!empty($this->characteristic->characteristic_id)) { ?>
		<dt class="hikamarket_characteristic_counter"><label><?php echo JText::_('HIKAM_NB_OF_USED'); ?></label></dt>
		<dd class="hikamarket_characteristic_counter"><?php
			if(empty($this->used_counter))
				echo '<span class="order-label order-label-created">';
			else
				echo '<span class="order-label order-label-confirmed">';
			echo (int)$this->used_counter;
			echo '</span>';
		?></dd>
<?php } ?>
	</dl>
	<h2><?php echo JText::_('CHARACTERISTIC_VALUES'); ?></h2>
<?php
if(!empty($this->characteristic->characteristic_id)) {
	if(!HIKASHOP_RESPONSIVE) {
?>	<table class="hikam_filter">
		<tr>
			<td width="100%">
				<?php echo JText::_('FILTER'); ?>:
				<input type="text" name="search" id="hikamarket_characteristic_listing_search" value="<?php echo $this->escape($this->pageInfo->search);?>" class=""/>
				<button class="hikabtn" onclick="this.form.submit();"><i class="fas fa-search"></i></button>
				<button class="hikabtn" onclick="document.getElementById('hikamarket_characteristic_listing_search').value='';this.form.submit();"><i class="fas fa-times"></i></button>
			</td>
			<td nowrap="nowrap">
<?php } else {?>
	<div class="row-fluid">
		<div class="span8">
			<div class="input-prepend input-append">
				<span class="add-on"><i class="icon-filter"></i></span>
				<input type="text" name="search" id="hikamarket_characteristic_listing_search" value="<?php echo $this->escape($this->pageInfo->search);?>" class=""/>
				<button class="hikabtn" onclick="this.form.submit();"><i class="icon-search"></i></button>
				<button class="hikabtn" onclick="document.getElementById('hikamarket_characteristic_listing_search').value='';this.form.submit();"><i class="icon-remove"></i></button>
			</div>
		</div>
		<div class="span4">
			<div class="expand-filters" style="width:auto;float:right">
<?php }

	if(!empty($this->vendorType) && $this->show_vendor && ($this->vendor->vendor_id == 0 || $this->vendor->vendor_id == 1))
		echo $this->vendorType->display('filter_vendors', @$this->pageInfo->filter->vendors);

	if(!HIKASHOP_RESPONSIVE) { ?>
			</td>
		</tr>
	</table>
<?php } else {?>
			</div>
			<div style="clear:both"></div>
		</div>
	</div>
<?php
	}
}

	if(hikamarket::acl('characteristic/values/edit')) {
?>
	<div>
		<button onclick="return window.characteristicMgr.addValue(this);" class="hikabtn hikabtn-success"><i class="fas fa-plus"></i> <?php echo JText::_('HIKAM_ADD_VALUE'); ;?></button>
	</div>
<?php
		if(!empty($this->characteristic->characteristic_id)) { //(int)$this->characteristic->characteristic_id > 0) {
?>
	<div id="market_characteristic_add_value" style="display:none;">
		<dl>
			<dt><?php echo JText::_('HIKA_NAME'); ?></dt>
			<dd><input type="text" size="30" style="min-width:60%" name="" id="market_characteristic_add_value_input" value=""/></dd>
<?php if($this->show_vendor && $this->vendor->vendor_id <= 1) { ?>
		<dt><?php echo JText::_('HIKA_VENDOR'); ?></dt>
		<dd><?php
		echo $this->nameboxType->display(
			'',
			0,
			hikamarketNameboxType::NAMEBOX_SINGLE,
			'vendor',
			array(
				'delete' => true,
				'id' => 'market_characteristic_add_value_vendor',
				'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>'
			)
		);
	?></dd>
<?php } ?>
		</dl>
		<div style="float:right">
			<button onclick="return window.characteristicMgr.createValue();" class="hikabtn hikabtn-success"><i class="fas fa-check"></i> <?php echo JText::_('HIKA_SAVE'); ;?></button>
		</div>
		<button onclick="return window.characteristicMgr.cancelAddValue();" class="hikabtn hikabtn-danger"><i class="far fa-times-circle"></i> <?php echo JText::_('HIKA_CANCEL'); ;?></button>
		<div style="clear:both"></div>
	</div>
<?php
		}
	}

	echo $this->loadTemplate('listing');

	if(hikamarket::acl('characteristic/values/edit') || hikamarket::acl('characteristic/values/add')) {
?>
	<div id="market_characteristic_value_edit_tpl" style="display:none;">
		<div class="hk-input-group">
			<input type="text" size="30" class="hk-form-control" style="min-width:60%" name="{NAME}" id="{INPUT_ID}" value="{VALUE}"/>
			<div class="hk-input-group-append">
				<a href="#save" class="hikabtn" onclick="return window.characteristicMgr.saveValue(this,{ID});"><i class="fas fa-check"></i> <span class="hikam_btn_text"><?php echo JText::_('HIKA_SAVE'); ?></span></a>
				<a href="#cancel" class="hikabtn" onclick="return window.characteristicMgr.cancelValue(this,{ID});"><i class="fas fa-times-circle"></i> <span class="hikam_btn_text"><?php echo JText::_('HIKA_CANCEL'); ?></span></a>
			</div>
		</div>
	</div>
<?php if($this->show_vendor && $this->vendor->vendor_id <= 1) { ?>
	<div id="market_characteristic_vendor_edit_tpl" style="display:none;"><?php
		echo $this->nameboxType->display(
			'{VENDOR_INPUT}',
			0,
			hikamarketNameboxType::NAMEBOX_SINGLE,
			'vendor',
			array(
				'delete' => true,
				'default_text' => JText::_('HIKA_NONE')
			)
		);
	?></div>
<?php } ?>
<script type="text/javascript">
window.characteristicMgr.edit = function(el, id) {
	var d = document, tpl = d.getElementById('market_characteristic_value_edit_tpl'),
		e = d.getElementById('market_characteristic_value_' + id);
	if(!tpl || !e) return false;
	var ev = e.childNodes[0], ee = e.childNodes[1], value = '', html = '';
	ev.style.display = 'none';
	for(var n in ev.childNodes[0].childNodes) {
		if(ev.childNodes[0].childNodes[n] && typeof(ev.childNodes[0].childNodes[n]) == 'object') {
			n = ev.childNodes[0].childNodes[n];
			if(n.tagName.toLowerCase() == 'span' && n.className == 'value')
				value = n.innerHTML;
		}
	}
	var htmlblocks = {
		'{ID}': id,
		'{INPUT_ID}': 'market_characteristic_value_input_'+id,
		'{NAME}': 'data[characteristic_value]['+id+'][value]',
		'{VALUE}': value
	};
	html = tpl.innerHTML;
	for(var k in htmlblocks) {
		if(htmlblocks.hasOwnProperty(k))
			html = html.replace(new RegExp(k,"g"), htmlblocks[k]);
	}
	ee.innerHTML = html;
<?php if($this->show_vendor && $this->vendor->vendor_id <= 1) { ?>
	tpl = d.getElementById('market_characteristic_vendor_edit_tpl');
	e = d.getElementById('market_characteristic_vendor_' + id);
	if(!tpl || !e) return false;
	ev = e.childNodes[0]; ee = e.childNodes[1];
	var name = ''; value = 0;
	if(e.getAttribute('data-vendor_id')) {
		value = parseInt(e.getAttribute('data-vendor_id'));
		if(value !== NaN && value > 0) {
			name = ev.innerHTML;
		} else
			value = 0;
	}
	ev.style.display = 'none';
	htmlblocks = {
		'id="{VENDOR_INPUT}': 'id="data_vendor_value_'+id,
		'\'{VENDOR_INPUT}': '\'data_vendor_value_'+id,
		'{VENDOR_INPUT}': 'data[characteristic_value]['+id+'][vendor]'
	};
	html = tpl.innerHTML;
	for(var k in htmlblocks) {
		if(htmlblocks.hasOwnProperty(k))
			html = html.replace(new RegExp(k,"g"), htmlblocks[k]);
	}
	ee.innerHTML = html;
	new window.oNamebox(
		'data_vendor_value_' + id,
		window.oNameboxes['{VENDOR_INPUT}'].data,
		window.oNameboxes['{VENDOR_INPUT}']._conf
	);
	if(value > 0)
		window.oNameboxes['data_vendor_value_' + id].set(name, value);
<?php } ?>
	return false;
};
window.characteristicMgr.cancelValue = function(el, id) {
	var d = document, e = d.getElementById('market_characteristic_value_' + id);
	if(!e) return false;
	var ev = e.childNodes[0], ee = e.childNodes[1];
	ee.innerHTML = '';
	ev.style.display = '';
<?php if($this->show_vendor) { ?>
	e = d.getElementById('market_characteristic_vendor_' + id);
	if(!e) return false;
	if(window.oNameboxes && window.oNameboxes['data_vendor_value_' + id])
		window.oNameboxes['data_vendor_value_' + id].destroy();
	ev = e.childNodes[0]; ee = e.childNodes[1];
	if(ee) ee.innerHTML = '';
	if(ev) ev.style.display = '';
<?php } ?>
	return false;
};
<?php if(!empty($this->characteristic->characteristic_id)) { ?>
window.characteristicMgr.saveValue = function(el, id) {
	var d = document, w = window, o = w.Oby, data = null,
		e = d.getElementById('market_characteristic_value_' + id),
		url = '<?php echo hikamarket::completeLink('characteristic&task=apply&characteristic_parent_id='.$this->characteristic->characteristic_id.'&characteristic_id={ID}&tmpl=json', false, false, true); ?>';
	if(!e) return false;
	data = o.getFormData(e) + '&<?php echo hikamarket::getFormToken(); ?>=1';
	o.xRequest(url.replace('{ID}', id), {mode:'POST', data:data}, function(x){
		var data = o.evalJSON(x.responseText);
		if(data && !data.err && data.id && data.id > 0) {
			e = d.getElementById('market_characteristic_value_' + id);
			var ev = e.childNodes[0], n = null,
				input = d.getElementById('market_characteristic_value_input_' + id);
			for(var n in ev.childNodes[0].childNodes) {
				if(ev.childNodes[0].childNodes[n] && typeof(ev.childNodes[0].childNodes[n]) == 'object') {
					n = ev.childNodes[0].childNodes[n];
					if(n.tagName.toLowerCase() == 'span' && n.className == 'value')
						n.innerHTML = input.value; // .replace(/</g,'&lt;');
				}
			}
<?php if($this->show_vendor && $this->vendor->vendor_id <= 1) { ?>
			e = d.getElementById('market_characteristic_vendor_' + id);
			if(!e) return;
			ev = e.childNodes[0];
			input = d.getElementById('data_vendor_value_'+id+'_valuehidden');
			if(input && input.value != '0' && input.value != '') {
				var vendor_id = parseInt(input.value);
				if(vendor_id !== NaN) {
					input = d.getElementById('data_vendor_value_'+id+'_valuetext');
					e.setAttribute('data-vendor_id', vendor_id);
					ev.innerHTML = input.innerHTML; //.replace(/</g,'&lt;');
				}
			}
<?php } ?>
		}
		w.characteristicMgr.cancelValue(el,id);
	}, function(x){
	});
	return false;
};
window.characteristicMgr.addValue = function(el) {
	var e = document.getElementById('market_characteristic_add_value');
	if(e) e.style.display = '';
	return false;
};
window.characteristicMgr.createValue = function(el) {
	var d = document, w = window, o = w.Oby,
		url = '<?php echo hikamarket::completeLink('characteristic&task=addCharacteristic&characteristic_parent_id=' . $this->characteristic->characteristic_id . '&characteristic_type=value&tmpl=json', false, false, true); ?>',
		data = '<?php echo hikamarket::getFormToken(); ?>=1',
		el = d.getElementById('market_characteristic_add_value_input');
	if(!el.value || el.value.length == 0)
		return false;
	data += '&value=' + encodeURI(el.value);
<?php if($this->show_vendor && $this->vendor->vendor_id <= 1) { ?>
	el = d.getElementById('market_characteristic_add_value_vendor_valuehidden');
	if(el && el.value && el.value.length > 0)
		data += '&characteristic_vendor_id=' + encodeURI(el.value);
<?php } ?>
	o.xRequest(url, {mode:'POST',data:data}, function(xhr,params) {
		var data = o.evalJSON(xhr.responseText);
		if(data === false || data === null) return;
		if(data.err) return;
		var blocks = {
			'ID': data.value,
			'VALUE': data.name,
			'VENDOR_ID': 0,
			'VENDOR': ''
		};
		if(data.vendor_id) blocks['VENDOR_ID'] = data.vendor_id;
		if(data.vendor) blocks['VENDOR'] = data.vendor;
		w.hikashop.dup('market_characteristic_tpl', blocks, 'market_characteristic_' + data.value);
		var tpl = d.getElementById('market_characteristic_tpl');
		if(tpl) tpl.className = (tpl.className == 'row0') ? 'row1' : 'row0';
		w.characteristicMgr.cancelAddValue(el);
	});
	return false;
};
window.characteristicMgr.cancelAddValue = function(el) {
	var e = document.getElementById('market_characteristic_add_value');
	if(e) e.style.display = 'none';
	return false;
};
<?php } else { ?>
window.characteristicMgr.addValue = function(el) {
	var d = document, w = window, tpl = null, uuid = Date.now(),
		blocks = {UUID: uuid, VENDOR:''};
<?php if($this->show_vendor && $this->vendor->vendor_id <= 1) { ?>
	tpl = d.getElementById('market_characteristic_vendor_edit_tpl');
	if(tpl) {
		var html = tpl.innerHTML;
		html = html.replace(new RegExp('id="{VENDOR_INPUT}', 'g'), 'id="data_vendor_value_'+uuid);
		html = html.replace(new RegExp('\'{VENDOR_INPUT}', 'g'), '\'data_vendor_value_'+uuid);
		html = html.replace(new RegExp('{VENDOR_INPUT}', 'g'), 'data[values][vendor][]');
		blocks['VENDOR'] = html;
	}
<?php } ?>
	w.hikashop.dup('market_characteristic_tpl', blocks, 'market_characteristic_'+uuid);
<?php if($this->show_vendor && $this->vendor->vendor_id <= 1) { ?>
	new window.oNamebox(
		'data_vendor_value_' + uuid,
		window.oNameboxes['{VENDOR_INPUT}'].data,
		window.oNameboxes['{VENDOR_INPUT}']._conf
	);
<?php } ?>
	tpl = d.getElementById('market_characteristic_tpl');
	if(tpl) tpl.className = (tpl.className == 'row0') ? 'row1' : 'row0';
	return false;
};
<?php } ?>
</script>
<?php
	} // End ACL characteristic_values_edit || characteristic_values_add
?>
	<input type="hidden" name="cid" value="<?php echo @$this->characteristic->characteristic_id; ?>"/>
	<input type="hidden" name="option" value="<?php echo HIKAMARKET_COMPONENT; ?>"/>
	<input type="hidden" name="task" value="show"/>
	<input type="hidden" name="ctrl" value="characteristic"/>
	<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
	<?php echo JHTML::_('form.token'); ?>
</form>
