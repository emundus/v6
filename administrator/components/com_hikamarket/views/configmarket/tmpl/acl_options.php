<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="iframedoc" id="iframedoc"></div>
<form action="<?php echo hikamarket::completeLink('config'); ?>" method="post" name="adminForm" id="adminForm">
	<table id="hikamarket_acl_list" class="adminlist pad0 table table-striped table-hover hikamarket_acl_opt_list">
		<thead>
			<tr>
				<th class="hikamarket_acl_name_title title"><?php echo JText::_('HIKA_NAME'); ?></th>
<?php
	$width = floor(70 / count($this->groups));
	foreach($this->groups as $group) {
?>
				<th class="hikamarket_acl_group_<?php echo $group->id; ?>_title title" style="width:<?php echo $width; ?>%"><?php echo $group->title; ?></th>
<?php
	}
?>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="<?php echo count($this->groups) + 1; ?>">
				</td>
			</tr>
		</tfoot>
		<tbody>
<?php
	$options = array(
		'product_limitation' => array(
			'title' => JText::_('VENDOR_PRODUCT_LIMITATION'),
			'mode' => 'text'
		),
		'product_min_price' => array(
			'title' => JText::_('VENDOR_PRODUCT_MIN_PRICE'),
			'mode' => 'prices'
		),
		'product_max_price' => array(
			'title' => JText::_('VENDOR_PRODUCT_MAX_PRICE'),
			'mode' => 'prices'
		),
		'product_max_prices_per_product' => array(
			'title' => JText::_('VENDOR_PRODUCT_MAX_PRICES_PER_PRODUCT'),
			'mode' => 'text'
		),
		'product_max_categories_per_product' => array(
			'title' => JText::_('VENDOR_PRODUCT_MAX_CATEGORIES_PER_PRODUCT'),
			'mode' => 'text'
		),
		'product_max_options_per_product' => array(
			'title' => JText::_('VENDOR_PRODUCT_MAX_OPTIONS_PER_PRODUCT'),
			'mode' => 'text'
		),
		'product_max_related_per_product' => array(
			'title' => JText::_('VENDOR_PRODUCT_MAX_RELATED_PER_PRODUCT'),
			'mode' => 'text'
		),
		'product_max_images_per_product' => array(
			'title' => JText::_('VENDOR_PRODUCT_MAX_IMAGES_PER_PRODUCT'),
			'mode' => 'text'
		),
	);
	foreach($options as $key => $option) {
?>
			<tr>
				<td><label><?php echo $option['title']; ?></label></td>
<?php
	foreach($this->groups as $group) {
?>
				<td class="hikamarket_acl_opt hikamarket_acl_opt_<?php echo $key; ?> hikamarket_acl_group_<?php echo $group->id; ?>" style="text-align:center" data-acl-type="<?php echo $option['mode']; ?>" data-acl-id="data_<?php echo $group->id . '_' . $key; ?>" onclick="return window.localPage.edit(this);"><?php
		$value = '';
		if(isset($this->aclConfig[$group->id][$key]))
			$value = $this->aclConfig[$group->id][$key];
		if(empty($option['mode']) || $option['mode'] == 'text') {
			echo '<div class="acl_value">'.$value.'</div>'.
				'<input type="hidden" autocomplete="off" id="data_'.$group->id.'_'.$key.'" name="data['.$group->id.']['.$key.']" value="'.$value.'" />';
		} else if($option['mode'] == 'prices') {
			$data = json_decode($value, true);
			$formated_value = array();
			if(!empty($data)) {
				foreach($data as $k => $v) {
					$v = hikamarket::toFloat($v);
					if($v == 0.0)
						continue;
					$formated_value[] = $v . '&nbsp;' . $this->currencies[$k]->currency_code;
				}
			}
			echo '<div class="acl_value">'.implode('<br/>', $formated_value).'</div>'.
				'<input type="hidden" autocomplete="off" id="data_'.$group->id.'_'.$key.'" name="data['.$group->id.']['.$key.']" value="'.$this->escape($value).'" />';
		}
				?></td>
<?php
	}
?>
			</tr>
<?php
	}
?>
		</tbody>
	</table>
<script type="text/javascript">
if(!window.localPage)
	window.localPage = {};

window.localPage.currencies = <?php
	$currencies = array();
	foreach($this->currencies as $currency) {
		$currencies[$currency->currency_id] = $currency->currency_code;
	}
	echo json_encode($currencies);
?>;

window.localPage.edit = function(el) {
	var d = document,
		id = el.getAttribute('data-acl-id'),
		type = el.getAttribute('data-acl-type'),
		div = el.firstChild,
		field = null;
	if(id)
		field = d.getElementById(id);
	if(!field)
		return true;

	if(el.editing === true)
		return true;
	if(el.editing === false) {
		el.editing = null;
		return true;
	}

	switch(type) {
		case 'text':
			window.localPage.editText(div, field, id, el);
			el.editing = true;
			break;
		case 'prices':
			window.localPage.editPrices(div, field, id, el);
			el.editing = true;
			break;
	}
	return false;
};

window.localPage.editText = function(div, field, id, el) {
	div.innerHTML = '<input type="text" value="' + field.value + '" id="field_' + id + '"/>'+
			'<a href="#apply" onclick="return window.localPage.apply(this);" class="acl-apply"><span><?php echo JText::_('APPLY'); ?></span></a>'+
			'<a href="#cancel" onclick="return window.localPage.cancel(this);" class="acl-cancel"><span><?php echo JText::_('CANCEL'); ?></span></a>';
	el.editing = true;
	input = document.getElementById('field_' + id);
	if(input) input.focus();
};

window.localPage.editPrices = function(div, field, id, el) {
	var data = '', currencies = window.localPage.currencies, code = null, value = '',
		prices = window.Oby.evalJSON(field.value);
	for(var i in currencies) {
		if(!currencies.hasOwnProperty(i))
			continue;
		value = prices ? (prices[i] || '') : '';
		data += '<input type="text" value="' + value + '" id="field_' + id + '_p' + i + '"/>&nbsp;'+currencies[i]+'<br/>';
	}
	data += '<a href="#apply" onclick="return window.localPage.apply(this);" class="acl-apply"><span><?php echo JText::_('APPLY'); ?></span></a>'+
			'<a href="#cancel" onclick="return window.localPage.cancel(this);" class="acl-cancel"><span><?php echo JText::_('CANCEL'); ?></span></a>';

	div.innerHTML = data;
	el.editing = true;
};

window.localPage.getPricesText = function(data) {
	var ret = '', currencies = window.localPage.currencies, code = null, value = '',
		prices = typeof(data) == 'string' ? window.Oby.evalJSON(data) : data;
	for(var i in currencies) {
		if(!currencies.hasOwnProperty(i))
			continue;
		if(!prices || !prices[i])
			continue;
		value = parseFloat(prices[i]);
		if(!isNaN(value) && value > 0.0)
			ret += value + '&nbsp;' + currencies[i] + '<br/>';
	}
	return ret;
};

window.localPage.getPricesData = function(id, json) {
	var d = document, ret = {}, input = null, currencies = window.localPage.currencies, value = '';
	for(var i in currencies) {
		if(!currencies.hasOwnProperty(i))
			continue;
		input = d.getElementById('field_' + id + '_p' + i);
		if(!input) continue;
		value = parseFloat(input.value);
		if(!isNaN(value) && value > 0.0)
			ret[i] = value;
	}
	if(json === undefined || !json)
		return ret;
	if(JSON.stringify)
		return JSON.stringify(ret);
	var r = [];
	for(var i in ret) { r.push('"'+i+'":'+ret[i]); }
	return '{' + r.join(',') + '}';
};

window.localPage.apply = function(el) {
	var d = document, w = window, o = w.Oby, p = el;
	while(p && !(p.nodeName.toLowerCase() == 'td' && o.hasClass(p, 'hikamarket_acl_opt'))) { p = p.parentNode; }
	if(!p) return false;

	var id = p.getAttribute('data-acl-id'),
		type = p.getAttribute('data-acl-type'),
		div = p.firstChild,
		field = null;
	if(id)
		field = d.getElementById(id);
	if(!field)
		return false;

	switch(type) {
		case 'text': {
			var input = d.getElementById('field_' + id);
			if(!input) return false;
			field.value = input.value;
			div.innerHTML = field.value;
			p.editing = false;
		}
		break;

		case 'prices': {
			field.value = w.localPage.getPricesData(id, true);
			div.innerHTML = w.localPage.getPricesText(field.value);
			p.editing = false;
		}
		break;
	}
	return false;
}

window.localPage.cancel = function(el) {
	var d = document, w = window, o = w.Oby, p = el;
	while(p && !(p.nodeName.toLowerCase() == 'td' && o.hasClass(p, 'hikamarket_acl_opt'))) { p = p.parentNode; }
	if(!p) return false;

	var id = p.getAttribute('data-acl-id'),
		type = p.getAttribute('data-acl-type'),
		div = p.firstChild,
		field = null;
	if(id)
		field = d.getElementById(id);
	if(!field)
		return false;

	switch(type) {
		case 'text':
			div.innerHTML = field.value;
			p.editing = false;
			break;
		case 'prices':
			div.innerHTML = w.localPage.getPricesText(field.value);
			p.editing = false;
			break;
	}
	return false;
};
</script>
	<input type="hidden" name="acl_type" value="<?php echo $this->acl_type; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKAMARKET_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="<?php echo hikaInput::get()->getCmd('ctrl'); ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
