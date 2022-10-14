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
$type = @$this->block_edit_address;
if(!in_array($type, array('shipping', 'billing')))
	return;

if(!empty($this->ajax) && ( empty($this->edit_address_mode) || !in_array($this->edit_address_mode, array('select', 'edit'))) )
	return;

$address_edit = $this->editable_order && hikamarket::acl('order/edit/'.$type.'address');

if(empty($this->ajax)) {
	$data = array(
		array(
			'name' => '<i class="fas fa-map-marked-alt"></i> ' . JText::_('HIKAM_SHOW_DEFAULT'),
			'link' => '#select_'.$type.'_address',
			'click' => 'return window.orderMgr.refreshAddressBlock(\''.$type.'\', 0);'
		),
		array(
			'name' => '<i class="fas fa-address-card"></i> ' . JText::_('HIKAM_SHOW_DETAILS'),
			'link' => '#select_'.$type.'_address',
			'click' => 'return window.orderMgr.refreshAddressBlock(\''.$type.'\', 1);'
		)
	);

	if($address_edit) {
		$data = array_merge($data, array(
			'-',
			array(
				'name' => '<i class="fas fa-pencil-alt"></i> ' . JText::_('HIKAM_EDIT_ADDRESS'),
				'link' => '#edit_'.$type.'_address',
				'click' => 'return window.orderMgr.refreshAddressEditBlock(\''.$type.'\', \'edit\');'
			),
		));
		if(hikamarket::acl('user/show/address')) {
			$data[] = array(
				'name' => '<i class="fas fa-location-arrow"></i> ' . JText::_('HIKAM_SELECT_ADDRESS'),
				'link' => '#select_'.$type.'_address',
				'click' => 'return window.orderMgr.refreshAddressEditBlock(\''.$type.'\', \'select\');'
			);
		}
	}

	echo $this->dropdownHelper->display(
		JText::_('HIKAM_MORE'),
		$data,
		array('type' => '', 'mini' => true, 'class' => 'hikabtn-primary', 'right' => true, 'up' => false)
	);

?>
<script type="text/javascript">
window.orderMgr.refreshAddressBlock = function(type, mode) {
	if(type != 'billing' && type != 'shipping')
		return false;
	var d = document, w = window, o = w.Oby,
		el = d.getElementById("hikamarket_order_block_" + type + "address");
	if(!el) return false;
	mode = parseInt(mode);
	if(mode == NaN) mode = 0;
	o.addClass(el, "hikamarket_ajax_loading");
	var url = '<?php echo hikamarket::completeLink('order&task=showblock&block=HIKATYPE&address_mode=HIKAADDRMODE&cid='.(int)$this->order->order_id.'&tmpl=ajax', false, false, true); ?>'.replace("HIKATYPE", type + 'address').replace("HIKAADDRMODE", mode);
	o.xRequest(url, {update: el}, function(x,p) {
		o.removeClass(el, "hikamarket_ajax_loading");
	});
	return false;
};
</script>
<?php

	if(!$address_edit)
		return;
?>
	<div class="hikamarket_order_edit_block" id="hikamarket_order_edit_<?php echo $type; ?>address" style="display:none;">
		<div class="hikamarket_ajax_loading_elem"></div>
		<div class="hikamarket_ajax_loading_spinner"></div>
<?php
	hikamarket::loadJslib('otree');
?>
	</div>
<script type="text/javascript">
window.orderMgr.refreshAddressEditBlock = function(type, mode) {
	if(type != 'billing' && type != 'shipping')
		return false;
	if(mode != 'select' && mode != 'edit')
		return false;
	var d = document, w = window, o = w.Oby, el = null,
		url = '<?php echo hikamarket::completeLink('order&task=showblock&block=HIKATYPE&address_mode=HIKAADDRMODE&cid='.(int)$this->order->order_id.'&tmpl=ajax', false, false, true); ?>'.replace("HIKATYPE", 'edit_' + type + 'address').replace("HIKAADDRMODE", mode)
		box = window.oNameboxes['hikamarket_order_edit_' + type + '_address_namebox'];

	if(mode == 'select')
		el = d.getElementById('hikamarket_order_edit_' + type + 'address');
	if(mode == 'edit')
		el = d.getElementById('hikamarket_order_block_' + type + 'address');

	if(!el)
		return false;

	if(box)
		box.clear();
	if(mode == 'select')
		el.style.display = '';
	o.addClass(el, "hikamarket_ajax_loading");
	o.xRequest(url, {update: el}, function(x,p) {
		o.removeClass(el, "hikamarket_ajax_loading");
	});
	return false;
};
window.orderMgr.selectAddress = function(type) {
	if(type != 'billing' && type != 'shipping')
		return false;
	var d = document, addr = d.getElementById('hikamarket_order_block_' + type + 'address');
	window.orderMgr.submitBlock(type + 'address', {update: addr}, function(x,p){
		window.orderMgr.hideSelectAddress(type);
	});
	return false;
};
window.orderMgr.submitAddress = function(type) {
	if(type != 'billing' && type != 'shipping')
		return false;
	window.orderMgr.submitBlock(type + 'address', {update:true}, null, 'block');
	return false;
};
window.orderMgr.hideSelectAddress = function(type) {
	if(type != 'billing' && type != 'shipping')
		return false;
	var d = document,
		block = d.getElementById('hikamarket_order_edit_' + type + 'address'),
		box = window.oNameboxes['hikamarket_order_edit_' + type + '_address_namebox'];
	if(box) box.clear();
	if(block) block.style.display = 'none';
	return false;
};
</script>
<?php
	return;
}


if($address_edit && $this->edit_address_mode == 'select') {
?>
	<div class="hikamarket_ajax_loading_elem"></div>
	<div class="hikamarket_ajax_loading_spinner"></div>
	<div>
<?php
	echo $this->nameboxType->display(
		'order['.$type.'address][address_id]',
		'',
		hikamarketNameboxType::NAMEBOX_SINGLE,
		'address',
		array(
			'url_params' => array(
				'USER_ID' => (int)@$this->order->order_user_id,
				'ADDR_TYPE' => $type,
			),
			'delete' => true,
			'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
			'id' => 'hikamarket_order_edit_'.$type.'_address_namebox'
		)
	);
?>
		<input type="hidden" name="order[<?php echo $type; ?>address][addrselect]" value="1"/>
		<div>
			<label for="hikamarket_order_select_<?php echo $type; ?>_address_addrlink"><input type="checkbox" value="1" id="hikamarket_order_select_<?php echo $type; ?>_address_addrlink" name="order[<?php echo $type; ?>address][addrlink]" /><span><?php
				$key = ($type == 'billing') ? 'SET_SHIPPING_ADDRESS_TOO' : 'SET_BILLING_ADDRESS_TOO';
				echo JText::_($key);
			?></span></label>
		</div>
		<div style="clear:both;margin-top:4px;"></div>
		<div style="float:right">
			<button onclick="return window.orderMgr.selectAddress('<?php echo $type; ?>');" class="hikabtn hikabtn-success"><i class="fas fa-check"></i> <?php echo JText::_('HIKAM_SELECT_ADDRESS'); ;?></button>
		</div>
		<button onclick="return window.orderMgr.hideSelectAddress('<?php echo $type; ?>');" class="hikabtn hikabtn-danger"><i class="far fa-times-circle"></i> <?php echo JText::_('HIKA_CANCEL'); ;?></button>
		<div style="clear:both"></div>
	</div>
	<script type="text/javascript">
	window.Oby.registerAjax('orderMgr.customer',function(params){
		if(!params || !params.id) return;
		var u = '<?php echo hikamarket::completeLink('user&task=getAddressList&address_type='.$type.'&user_id=HIKA_USER_ID', true, false, true); ?>';
		window.oNameboxes['hikamarket_order_edit_<?php echo $type; ?>_address_namebox'].changeUrl(u.replace('HIKA_USER_ID', params.id));
	});
	</script>
<?php
	return;
}

if($address_edit && $this->edit_address_mode == 'edit') {
?>
	<div class="hikamarket_ajax_loading_elem"></div>
	<div class="hikamarket_ajax_loading_spinner"></div>
	<div class="hikamarket_order_edit_block">

		<dl>
<?php
	$address = (($type == 'billing') ? $this->order->billing_address : $this->order->shipping_address);

	$fields = (isset($this->order->{$type.'_fields'}) ? $this->order->{$type.'_fields'} : $this->order->fields);
	foreach($fields as $field) {
		if(empty($field->field_frontcomp) || strpos($field->field_display, ';vendor_order_edit=1;') === false)
			continue;
		$fieldname = $field->field_namekey;
?>
			<dt class="hikamarket_<?php echo $type; ?>order_address_<?php echo $fieldname;?>"><label><?php
				echo $this->fieldsClass->trans($field->field_realname);
				if(!empty($field->field_required))
					echo ' *';
			?></label></dt>
			<dd class="hikamarket_<?php echo $type; ?>order_address_<?php echo $fieldname;?>"><?php
				echo $this->fieldsClass->display(
						$field,
						@$address->$fieldname,
						'order['.$type.'address]['.$fieldname.']',
						false,
						'',
						false,
						$fields,
						$address,
						false
					);
			?></dd>
<?php
	}
?>
		</dl>
		<input type="hidden" name="order[<?php echo $type; ?>address][address_id]" value="<?php echo (int)$this->order->{'order_'.$type.'_address_id'}; ?>"/>
<?php if($this->order->order_billing_address_id == $this->order->order_shipping_address_id) { ?>
		<div>
			<label for="hikamarket_order_select_<?php echo $type; ?>_address_addrlink"><input type="checkbox" value="1" id="hikamarket_order_select_<?php echo $type; ?>_address_addrlink" name="order[<?php echo $type; ?>address][addrlink]" /><span><?php
				$key = ($type == 'billing') ? 'SET_SHIPPING_ADDRESS_TOO' : 'SET_BILLING_ADDRESS_TOO';
				echo JText::_($key);
			?></span></label>
		</div>
<?php } ?>
		<div style="clear:both;margin-top:4px;"></div>
		<div style="float:right">
			<button onclick="return window.orderMgr.submitAddress('<?php echo $type; ?>');" class="hikabtn hikabtn-success"><i class="fas fa-check"></i> <?php echo JText::_('HIKA_OK'); ;?></button>
		</div>
		<button onclick="return window.orderMgr.refreshAddressBlock('<?php echo $type; ?>', 0);" class="hikabtn hikabtn-danger"><i class="far fa-times-circle"></i> <?php echo JText::_('HIKA_CANCEL'); ;?></button>
		<div style="clear:both"></div>
	</div>
<?php
}
