<?php
/**
 * @package	HikaShop for Joomla!
 * @version	3.0.1
 * @author	hikashop.com
 * @copyright	(C) 2010-2017 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php if(empty($this->ajax)) { ?>
<div id="hikashop_user_addresses_show">
<?php } ?>
	<dl class="hika_options large">
		<dt><label><?php echo JText::_('HIKA_SELECT_DEFAULT_ADDRESS'); ?></label></dt>
		<dd><?php

	$current = 0;
	$values = array();
	if(!empty($this->addresses)) {
		foreach($this->addresses as $k => $address) {
			$addr = $this->addressClass->miniFormat($address, $this->fields['address']);
			$values[] = JHTML::_('select.option', $k, $addr);
			if(!empty($address->address_default))
				$current = $address->address_default;
		}
	}
	if(empty($values))
		$values = array(JHTML::_('select.option', '', JText::_('HIKA_NO_ADDRESS')));
	echo JHTML::_('select.genericlist', $values, 'default_address', 'class="hikashop_default_address_dropdown" style="width:100%;"', 'value', 'text', $current, 'hikashop_default_address_selector');

		?></dd>
	</dl>

	<div class="hikashop_user_addresses_list">
<?php
if(empty($this->edit_address)) {
	foreach($this->addresses as $address) {
?>
	<div class="hikashop_user_address address_selection" id="hikashop_user_address_<?php echo $address->address_id; ?>">
		<div class="hika_edit">
<?php
	echo $this->popup->display(
		'<img src="'. HIKASHOP_IMAGES.'edit.png" alt="'.JText::_('HIKA_EDIT').'"/>',
		'HIKA_EDIT',
		hikashop_completeLink('user&task=editaddress&user_id='.$this->user->user_id.'&address_id='.$address->address_id,true),
		'aedit_address_'.$address->address_id.'_link',
		760, 480, '', '', 'link'
	);
?>
			<a href="<?php echo hikashop_completeLink('user&task=deleteaddress&address_id='.$address->address_id.'&'.hikashop_getFormToken().'=1');?>"><img src="<?php echo HIKASHOP_IMAGES; ?>delete.png"/></a>
		</div>
<?php
	echo $this->addressClass->displayAddress($this->fields['address'], $address, 'order');
?>
	</div>
<?php
	}
?>
	</div>
	<div class="hikashop_user_addresses_button">
<?php
	echo $this->popup->display(
		JText::_('ADD'),
		'ADD',
		hikashop_completeLink('user&task=editaddress&user_id='.$this->user->user_id.'',true),
		'add_address_link',
		760, 480, '', '', 'button'
	);
?>
	</div>
<?php
} else {
	foreach($this->fields['address'] as $fieldname => $field) {
?>
	<dl id="hikamarket_user_address_<?php echo $this->address->address_id; ?>_<?php echo $fieldname;?>" class="hikam_options">
		<dt class="hikamarket_user_address_<?php echo $fieldname;?>"><label><?php
			echo $this->fieldsClass->trans($field->field_realname);
			if($field->field_required && !empty($field->vendor_edit))
				echo ' <span class="field_required">*</span>';
		?></label></dt>
		<dd class="hikamarket_user_address_<?php echo $fieldname;?>"><?php
			if(!empty($field->vendor_edit)) {
				$onWhat = 'onchange';
				if($field->field_type == 'radio')
					$onWhat = 'onclick';

				$field->field_required = false;
				echo $this->fieldsClass->display(
						$field,
						@$this->address->$fieldname,
						'data[user_address]['.$fieldname.']',
						false,
						' ' . $onWhat . '="hikashopToggleFields(this.value,\''.$fieldname.'\',\'user_address\',0);"',
						false,
						$this->fields['address'],
						$this->address
				);
			} else {
				echo $this->fieldsClass->show($field, @$this->address->$fieldname);
			}
		?></dd>
	</dl>
<?php
	}
	echo '<input type="hidden" name="data[user_address][address_id]" value="'.@$this->address->address_id.'"/>';
	echo '<input type="hidden" name="data[user_address][address_user_id]" value="'.@$this->address->address_user_id.'"/>';
}
?>

<?php
if(empty($this->ajax)) {
?>
</div>
<script type="text/javascript">
if(!window.addressMgr) window.addressMgr = {};
window.addressMgr.deleteAddress = function(el) {
	return false;
};
window.addressMgr.editAddress = function(el) {
	return false;
};
window.addressMgr.newAddress = function(el) {
	return false;
};
window.addressMgr.submitAddress = function(el) {
	return false;
};
window.addressMgr.cancelEditAddress = function(el) {
	return false;
};
</script>
<?php
}
