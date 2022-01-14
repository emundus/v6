<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><legend><?php echo JText::_('HIKASHOP_'.strtoupper($this->type).'_ADDRESS'); ?></legend>
<?php

$name = $this->type.'_address';
$fields_type = $this->type.'_fields';
if(!isset($this->order->$fields_type))
	$fields_type = 'fields';
$show_url = 'order&task=show&subtask='.$name.'&cid='.$this->order->order_id;
$save_url = 'order&task=save&subtask='.$name.'&cid='.$this->order->order_id;
$update_url = 'order&task=edit&subtask='.$name.'&cid='.$this->order->order_id;

if(hikamarket::acl('order/edit/'.$name) && ($this->vendor->vendor_id == 0 || $this->vendor->vendor_id == 1)) {
	if(!isset($this->edit) || $this->edit !== true ) {
?>		<div class="hikam_edit"><a href="<?php echo hikamarket::completeLink($update_url, true);?>" onclick="return window.hikamarket.get(this,'hikamarket_order_field_<?php echo $name; ?>');"><i class="fas fa-pencil-alt"></i><span><?php echo JText::_('HIKA_EDIT'); ?></span></a></div>
<?php
	} else {
?>		<div class="hikam_edit">
			<a href="<?php echo hikamarket::completeLink($save_url, true);?>" onclick="return window.hikamarket.form(this,'hikamarket_order_field_<?php echo $name; ?>');"><i class="far fa-times-circle"></i><span><?php echo JText::_('HIKA_SAVE'); ?></span></a>
			<a href="<?php echo hikamarket::completeLink($show_url, true);?>" onclick="return window.hikamarket.get(this,'hikamarket_order_field_<?php echo $name; ?>');"><i class="fas fa-check"></i><span><?php echo JText::_('HIKA_CANCEL'); ?></span></a>
		</div>
<?php
	}
}

$address =& $this->order->$name;
$display = 'field_backend';
if(isset($this->edit) && $this->edit === true ) {
	if(hikamarket::acl('order/edit/'.$name)) {
		foreach($this->order->$fields_type as $field) {
			if(!$field->$display)
				continue;

			$fieldname = $field->field_namekey;
?>
	<dl id="hikashop_<?php echo $this->type;?>order_address_<?php echo $fieldname;?>" class="hikam_options">
		<dt class="hikamarket_<?php echo $this->type;?>order_address_<?php echo $fieldname;?>"><label><?php echo $this->fieldsClass->trans($field->field_realname);?></label></dt>
		<dd class="hikamarket_<?php echo $this->type;?>order_address_<?php echo $fieldname;?>"><?php
			$onWhat = 'onchange';
			if($field->field_type == 'radio')
				$onWhat = 'onclick';

			$field->table_name = 'order';
			echo $this->fieldsClass->display(
					$field,
					@$address->$fieldname,
					'data['.$name.']['.$fieldname.']',
					false,
					' ' . $onWhat . '="hikashopToggleFields(this.value,\''.$fieldname.'\',\''.$name.'\',0);"',
					false,
					$this->order->$fields_type,
					$address
			);
		?></dd>
	</dl>
<?php
		}
	}

	if(hikamarket::acl('order/edit/history')) {
?>
	<dl class="hikam_options">
		<dt class="hikamarket_<?php echo $this->type;?>_history"><label><?php echo JText::_('HISTORY'); ?></label></dt>
		<dd class="hikamarket_<?php echo $this->type;?>_history">
			<span><input onchange="window.orderMgr.<?php echo $this->type;?>_history_changed(this);" type="checkbox" id="hikamarket_history_<?php echo $this->type;?>_store" name="data[history][store_data]" value="1"/><label for="hikamarket_history_<?php echo $this->type;?>_store" style="display:inline-block"><?php echo JText::_('SET_HISTORY_MESSAGE');?></label></span><br/>
			<textarea id="hikamarket_history_<?php echo $this->type;?>_msg" name="data[history][msg]" style="display:none;"></textarea>
		</dd>
	</dl>
<script type="text/javascript">
window.orderMgr.<?php echo $this->type;?>_history_changed = function(el) {
	var fields = ['hikamarket_history_<?php echo $this->type;?>_msg'], displayValue = '';
	if(!el.checked) displayValue = 'none';
	window.hikamarket.setArrayDisplay(fields, displayValue);
}
</script>
<?php
	}

	echo JHTML::_( 'form.token' );
} else {
	foreach($this->order->$fields_type as $field){
		if($field->$display){
			$fieldname = $field->field_namekey;
?>
	<dl class="hikam_options">
		<dt class="hikamarket_<?php echo $this->type;?>order_address_<?php echo $fieldname;?>"><label><?php echo $this->fieldsClass->trans($field->field_realname);?></label></dt>
		<dd class="hikamarket_<?php echo $this->type;?>order_address_<?php echo $fieldname;?>"><span><?php echo $this->fieldsClass->show($field, @$address->$fieldname);?></span></dd>
	</dl>
<?php
		}
	}
}
?>
<script type="text/javascript">
window.orderMgr.update<?php echo ucfirst($this->type);?> = function() {
	window.Oby.xRequest('<?php echo hikamarket::completeLink('order&task=show&subtask='.$this->type.'_address&cid='.$this->order->order_id,true,false,true); ?>',{update:'hikashop_order_field_<?php echo $this->type; ?>_address'});
}
</script>
