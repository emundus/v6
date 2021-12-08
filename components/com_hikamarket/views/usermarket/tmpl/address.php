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
if(!empty($this->ajax) && !empty($this->edit)) {
?>
	<div class="hikamarket_ajax_loading_elem"></div>
	<div class="hikamarket_ajax_loading_spinner"></div>
<?php
}
$show_url = 'user&task=address&subtask=listing&user_id='.$this->user_id;
$save_url = 'user&task=address&subtask=save&cid='.$this->address->address_id.'&user_id='.$this->user_id;
$update_url = 'user&task=address&subtask=edit&cid='.$this->address->address_id.'&user_id='.$this->user_id;
$delete_url = 'user&task=address&subtask=delete&cid='.$this->address->address_id.'&user_id='.$this->user_id;

if(hikamarket::acl('user/edit/address') && ($this->vendor->vendor_id == 0 || $this->vendor->vendor_id == 1)) {
	if(!isset($this->edit) || $this->edit !== true ) {
?>		<div class="hikam_edit">
			<a class="hikabtn" href="<?php echo hikamarket::completeLink($update_url, 'ajax');?>" onclick="return window.addressMgr.get(this,'hikamarket_user_addresses_show');"><i class="fas fa-pencil-alt"></i> <span><?php echo JText::_('HIKA_EDIT'); ?></span></a>
			<a class="hikabtn" href="<?php echo hikamarket::completeLink($delete_url, 'ajax');?>" onclick="return window.addressMgr.delete(this,<?php echo $this->address->address_id; ?>);"><i class="far fa-trash-alt"></i> <span><?php echo JText::_('HIKA_DELETE'); ?></span></a>
		</div>
<?php
	} else {
		if(empty($this->address->address_id)) {
			$title = $this->type == 'billing' ? 'HIKASHOP_NEW_BILLING_ADDRESS': 'HIKASHOP_NEW_SHIPPING_ADDRESS';
		} else {
			$title = in_array($this->address->address_type, array('billing', 'shipping')) ? 'HIKASHOP_EDIT_'.strtoupper($this->address->address_type).'_ADDRESS' : 'HIKASHOP_EDIT_ADDRESS';
		}
?>
<div class="hikashop_address_edition">
	<h3><?php echo JText::_($title); ?></h3>
<?php
	}
}

if(isset($this->edit) && $this->edit === true) {
	$error_messages = hikaRegistry::get('address.error');
	if(!empty($error_messages)) {
		foreach($error_messages as $msg) {
			hikashop_display($msg[0], $msg[1]);
		}
	}

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
?>
	<div style="float:right">
		<a class="hikabtn hikabtn-success" href="<?php echo hikamarket::completeLink($save_url, 'ajax');?>" onclick="return window.addressMgr.form(this,'hikamarket_user_addresses_show');"><i class="fas fa-check"></i> <span><?php echo JText::_('HIKA_SAVE'); ?></span></a>
	</div>
	<a class="hikabtn hikabtn-danger" href="<?php echo hikamarket::completeLink($show_url, 'ajax');?>" onclick="return window.addressMgr.get(this,'hikamarket_user_addresses_show');"><i class="fas fa-times-circle"></i> <span><?php echo JText::_('HIKA_CANCEL'); ?></span></a>

	<input type="hidden" name="data[user_address][address_id]" value="<?php echo @$this->address->address_id; ?>"/>
	<input type="hidden" name="data[user_address][address_user_id]" value="<?php echo @$this->address->address_user_id; ?>"/>
	<?php echo JHTML::_( 'form.token' ); ?>
</div>
<?php
} else {
	if($this->config->get('address_show_details', 0)) {
		foreach($this->fields['address'] as $fieldname => $field) {
?>
	<dl class="hikam_options">
		<dt class="hikamarket_user_address_<?php echo $fieldname;?>"><label><?php echo $this->fieldsClass->trans($field->field_realname);?></label></dt>
		<dd class="hikamarket_user_address_<?php echo $fieldname;?>"><span><?php echo $this->fieldsClass->show($field, @$this->address->$fieldname);?></span></dd>
	</dl>
<?php
		}
	} else {
		echo $this->addressClass->maxiFormat($this->address, $this->fields['address'], true);
	}

	if(!empty($this->display_badge)) {
?>
		<div class="" style="float:right"><?php
			if(in_array($this->address->address_type, array('billing', '', 'both')))
				echo '<span class="hk-label hk-label-blue">'.JText::_('HIKASHOP_BILLING_ADDRESS').'</span>';
			if(in_array($this->address->address_type, array('shipping', '', 'both')))
				echo '<span class="hk-label hk-label-orange">'.JText::_('HIKASHOP_SHIPPING_ADDRESS').'</span>';
		?></div>
<?php
	}
}

if(!empty($this->ajax)) {
	$miniFormat = $this->addressClass->miniFormat($this->address, $this->fields['address']);
?>
<script type="text/javascript">
window.Oby.fireAjax('hikamarket_address_changed',{'edit':<?php echo $this->edit?'1':'0'; ?>,'cid':<?php echo $this->address->address_id; ?>,'miniFormat':'<?php echo str_replace('\'','\\\'', $miniFormat); ?>'<?php
	$previous_id = hikaInput::get()->getVar('previous_cid', null);
	if((!empty($previous_id) || $previous_id === 0) && is_int($previous_id))
		echo ',\'previous_cid\':' . $previous_id;
?>});
</script>
<?php
}

if(!empty($this->init_js)) {
?>
<script type="text/javascript">
<?php echo $this->init_js; ?>
</script>
<?php
}
