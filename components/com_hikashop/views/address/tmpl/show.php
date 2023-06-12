<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
$tmpl = hikaInput::get()->getCmd('tmpl', '');
if(isset($this->params->address_id) || $tmpl == 'component') {
	echo $this->loadTemplate('legacy');
	return;
}

$labelcolumnclass = 'hkc-sm-4';
$inputcolumnclass = 'hkc-sm-8';

$show_url = 'address&task=listing'.$this->url_itemid;
$save_url = 'address&task=save&cid='.(int)@$this->address->address_id.$this->url_itemid;

$dest = 'hikashop_user_addresses_show';

if(!isset($this->edit) || $this->edit !== true ) {
	$update_url = 'address&task=edit&cid='.(int)@$this->address->address_id.'&address_type='.$this->address->address_type.$this->url_itemid;
	$delete_url = 'address&task=delete&cid='.(int)@$this->address->address_id.$this->url_itemid;
?>
		<div class="hika_edit">
			<a href="<?php echo hikashop_completeLink($update_url, 'ajax');?>" onclick="return window.addressMgr.get(this,'<?php echo $dest; ?>');">
				<i class="fas fa-pen"></i> <span><?php echo JText::_('HIKA_EDIT'); ?></span>
			</a>
			<a href="<?php echo hikashop_completeLink($delete_url, 'ajax');?>" onclick="return window.addressMgr.delete(this,<?php echo (int)@$this->address->address_id; ?>);">
				<i class="fas fa-trash"></i> <span><?php echo JText::_('HIKA_DELETE'); ?></span>
			</a>
		</div>
<?php
} else {
	if(!empty($this->ajax)) {
?>
	<div class="hikashop_checkout_loading_elem"></div>
	<div class="hikashop_checkout_loading_spinner"></div>
<?php }
}

if(isset($this->edit) && $this->edit === true) {
	if(empty($this->address->address_id)) {
		$title = $this->type == 'billing' ? 'HIKASHOP_NEW_BILLING_ADDRESS': 'HIKASHOP_NEW_SHIPPING_ADDRESS';
	} else {
		$title = in_array($this->address->address_type, array('billing', 'shipping')) ? 'HIKASHOP_EDIT_'.strtoupper($this->address->address_type).'_ADDRESS' : 'HIKASHOP_EDIT_ADDRESS';
	}
?>
<div class="hikashop_address_edition">
	<h3><?php echo JText::_($title); ?></h3>
<?php
	$error_messages = hikaRegistry::get('address.error');
	if(!empty($error_messages)) {
		foreach($error_messages as $msg) {
			if(!is_array($msg))
				$msg = array($msg);
			if(!isset($msg[1]))
				$msg[1] = 'error';
			hikashop_display($msg[0], $msg[1]);
		}
	}
	?>
	<fieldset class="hkform-horizontal">
<?php
	if(!empty($this->extraData->address_top)) { echo implode("\r\n", $this->extraData->address_top); }
	$after = array();
	foreach($this->fields as $fieldname => $field) {
		$onWhat = 'onchange';
		if($field->field_type == 'radio')
			$onWhat = 'onclick';

		$html = $this->fieldsClass->display(
			$field,
			@$this->address->$fieldname,
			'data[address]['.$fieldname.']',
			false,
			' class="'.HK_FORM_CONTROL_CLASS.'" ' . $onWhat . '="window.hikashop.toggleField(this.value,\''.$fieldname.'\',\'address\',0);"',
			false,
			$this->fields,
			$this->address,
			false
		);
		if($field->field_type == 'hidden') {
			$after[] = $html;
			continue;
		}
?>
		<div class="hkform-group control-group hikashop_address_<?php echo $fieldname;?>" id="hikashop_address_<?php echo $fieldname; ?>">
<?php
		$classname = $labelcolumnclass.' hkcontrol-label';
		echo $this->fieldsClass->getFieldName($field, true, $classname);
?>
			<div class="<?php echo $inputcolumnclass;?>">
				<?php echo $html; ?>
			</div>
		</div>
<?php
	}
	if(count($after)) {
		echo implode("\r\n", $after);
	}
	if(!empty($this->extraData) && !empty($this->extraData->address_bottom)) { echo implode("\r\n", $this->extraData->address_bottom); }
	?>
	</fieldset>
<?php
	if(empty($this->address->address_id)) {
?>
	<input type="hidden" name="data[address][address_type]" value="<?php echo @$this->address->address_type; ?>"/>
<?php
	}
?>
	<input type="hidden" name="data[address][address_id]" value="<?php echo @$this->address->address_id; ?>"/>
	<input type="hidden" name="data[address][address_user_id]" value="<?php echo @$this->address->address_user_id; ?>"/>
	<?php echo JHTML::_('form.token'); ?>

	<div style="float:right">
		<a href="<?php echo hikashop_completeLink($save_url, 'ajax');?>" onclick="return window.addressMgr.form(this,'<?php echo $dest; ?>');" class="<?php echo $this->config->get('css_button','hikabtn'); ?> hikabtn-success hikashop_checkout_address_ok_button"><i class="fa fa-save"></i> <?php echo JText::_('HIKA_OK'); ;?></a>
	</div>
	<a href="<?php echo hikashop_completeLink($show_url, 'ajax');?>" onclick="return window.addressMgr.get(this,'<?php echo $dest; ?>');" class="<?php echo $this->config->get('css_button','hikabtn'); ?> hikabtn-danger hikashop_checkout_address_cancel_button"><i class="fa fa-times"></i> <?php echo JText::_('HIKA_CANCEL'); ;?></a>
</div>
<?php
} else {
	if($this->config->get('address_show_details', 0)) {
		foreach($this->fields as $fieldname => $field) {
?>
	<dl class="hika_options">
		<dt class="hikashop_user_address_<?php echo $fieldname;?>"><label><?php echo $this->fieldsClass->trans($field->field_realname);?></label></dt>
		<dd class="hikashop_user_address_<?php echo $fieldname;?>"><span><?php echo $this->fieldsClass->show($field, @$this->address->$fieldname);?></span></dd>
	</dl>
<?php
		}
	} else {
		echo $this->addressClass->maxiFormat($this->address, $this->fields, true);
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

if(!empty($this->init_js)) {
?>
<script type="text/javascript">
<?php echo $this->init_js; ?>
</script>
<?php
}
