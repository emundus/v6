<?php
/**
 * @package	HikaShop for Joomla!
 * @version	3.0.1
 * @author	hikashop.com
 * @copyright	(C) 2010-2017 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><fieldset class="form-horizontal">
<?php
$labelcolumnclass = 'hkc-sm-4';
$inputcolumnclass = 'hkc-sm-8';

if(!empty($this->options['registration_registration'])) {
?>
	<div class="hkform-group control-group hikashop_registration_name_line" id="hikashop_registration_name_line">
		<label id="namemsg" for="register_name" class="<?php echo $labelcolumnclass;?> hkcontrol-label" title=""><?php echo JText::_('HIKA_USER_NAME'); ?>*</label>
		<div class="<?php echo $inputcolumnclass;?>">
			<input type="text" name="data[register][name]" id="register_name" value="<?php echo $this->escape($this->mainUser->get( 'name' ));?>" class="hkform-control" size="30" maxlength="50"/>
		</div>
	</div>
	<div class="hkform-group control-group hikashop_registration_username_line" id="hikashop_registration_username_line">
		<label id="usernamemsg" for="register_username" class="<?php echo $labelcolumnclass;?> hkcontrol-label" title=""><?php echo JText::_('HIKA_USERNAME'); ?>*</label>
		<div class="<?php echo $inputcolumnclass;?>">
			<input type="text" name="data[register][username]" id="register_username" value="<?php echo $this->escape($this->mainUser->get( 'username' ));?>" class="hkform-control validate-username" maxlength="25" size="30" />
		</div>
	</div>
<?php
}
?>
	<div class="hkform-group control-group hikashop_registration_email_line">
		<label id="emailmsg" for="register_email" class="<?php echo $labelcolumnclass;?> hkcontrol-label" title=""><?php echo JText::_('HIKA_EMAIL'); ?>*</label>
		<div class="<?php echo $inputcolumnclass;?>">
			<input<?php if($this->config->get('show_email_confirmation_field',0)){echo ' autocomplete="off"';} ?> type="text" name="data[register][email]" id="register_email" value="<?php echo $this->escape($this->mainUser->get( 'email' ));?>" class="hkform-control validate-email" maxlength="100" size="30" />
		</div>
	</div>
<?php
if(!empty($this->options['registration_email_confirmation'])) {
?>
	<div class="hkform-group control-group hikashop_registration_email_confirm_line">
		<label id="email_confirm_msg" for="register_email_confirm" class="<?php echo $labelcolumnclass;?> hkcontrol-label" title=""><?php echo JText::_('HIKA_EMAIL_CONFIRM'); ?>*</label>
		<div class="<?php echo $inputcolumnclass;?>">
			<input autocomplete="off" type="text" name="data[register][email_confirm]" id="register_email_confirm" value="<?php echo $this->escape($this->mainUser->get('email'));?>" class="hkform-control validate-email" maxlength="100" size="30" onchange="if(this.value!=document.getElementById('register_email').value){alert('<?php echo JText::_('THE_CONFIRMATION_EMAIL_DIFFERS_FROM_THE_EMAIL_YOUR_ENTERED',true); ?>'); this.value = '';}" />
		</div>
	</div>
<?php
}

if(!empty($this->extraData[$this->module_position]) && !empty($this->extraData[$this->module_position]->top)) { echo implode("\r\n", $this->extraData[$this->module_position]->top); }

?>
<?php
if(!empty($this->options['registration_registration']) || !empty($this->options['registration_password'])) {
?>
	<div class="hkform-group control-group hikashop_registration_password_line" id="hikashop_registration_password_line">
		<label id="pwmsg" for="register_password" class="<?php echo $labelcolumnclass;?> hkcontrol-label" title=""><?php echo JText::_('HIKA_PASSWORD'); ?>*</label>
		<div class="<?php echo $inputcolumnclass;?>">
			<input autocomplete="off" type="password" name="data[register][password]" id="register_password" value="" class="hkform-control validate-password" size="30" >
		</div>
	</div>
	<div class="hkform-group control-group hikashop_registration_password2_line" id="hikashop_registration_password2_line">
		<label id="pw2msg" for="register_password2" class="<?php echo $labelcolumnclass;?> hkcontrol-label" title=""><?php echo JText::_('HIKA_VERIFY_PASSWORD'); ?>*</label>
		<div class="<?php echo $inputcolumnclass;?>">
			<input autocomplete="off" type="password" name="data[register][password2]" id="register_password2" value="" class="hkform-control validate-password" size="30" >
		</div>
	</div>
<?php
}

if(!empty($this->extraData[$this->module_position]) && !empty($this->extraData[$this->module_position]->middle)) { echo implode("\r\n", $this->extraData[$this->module_position]->middle); }

$type = 'user';
if(!empty($this->extraFields[$type])) {
	foreach($this->extraFields[$type] as $fieldName => $field) {
?>
	<div class="hkform-group control-group hikashop_registration_<?php echo $fieldName;?>_line" id="hikashop_<?php echo $type . '_' . $this->step . '_' . $this->module_position . '_' . $field->field_namekey; ?>">
		<?php
			$classname = $labelcolumnclass.' hkcontrol-label';
			echo $this->fieldsClass->getFieldName($field, true, $classname);
		?>
		<div class="<?php echo $inputcolumnclass;?>">
<?php
		$onWhat = ($field->field_type == 'radio') ? 'onclick' : 'onchange';
		echo $this->fieldsClass->display(
				$field,
				@$this->$type->$fieldName,
				'data['.$type.']['.$fieldName.']',
				false,
				'class="hkform-control" '.$onWhat.'="hikashopToggleFields(this.value,\''.$fieldName.'\',\''.$type . '_' . $this->step . '_' . $this->module_position.'\',0,\'hikashop_\');"',
				false,
				$this->extraFields[$type],
				@$this->$type,
				false
		);
?>
		</div>
	</div>
<?php
	}
}
if(!empty($this->options['affiliate_registration'])) {
	$plugin = JPluginHelper::getPlugin('system', 'hikashopaffiliate');
	if(!empty($plugin)) {
?>
	<div class="hkform-group control-group hikashop_registration_affiliate_line">
		<div class="controls">
<?php
		$affiliate_terms = $this->config->get('affiliate_terms', 0);
		if(!empty($affiliate_terms)) {
?>
			<input class="hikashop_affiliate_checkbox" id="hikashop_affiliate_checkbox" type="checkbox" name="hikashop_affiliate_checkbox" value="1" <?php echo $this->affiliate_checked; ?> />
			<span class="hikashop_affiliate_terms_span_link" id="hikashop_affiliate_terms_span_link">
				<a class="hikashop_affiliate_terms_link" id="hikashop_affiliate_terms_link" target="_blank" href="<?php echo JRoute::_('index.php?option=com_content&view=article&id='.$affiliate_terms); ?>"><?php echo JText::_('BECOME_A_PARTNER'); ?></a>
			</span>
<?php
		} else {
?>
			<label class="checkbox">
				<input class="hikashop_affiliate_checkbox" id="hikashop_affiliate_checkbox" type="checkbox" name="hikashop_affiliate_checkbox" value="1" <?php echo $this->affiliate_checked; ?> />
				<?php echo JText::_('BECOME_A_PARTNER');?>
			</label>
<?php
		}
?>
		</div>
	</div>
<?php
	}
}

if(!empty($this->options['address_on_registration']) && !empty($this->extraFields['address'])) {
	$type = 'address';
?>
	<div class="">
		<legend><?php echo JText::_( 'ADDRESS_INFORMATION' ); ?></legend>
	</div>
<?php
	foreach($this->extraFields[$type] as $fieldName => $oneExtraField) {
?>
	<div class="hkform-group control-group hikashop_registration_<?php echo $fieldName;?>_line" id="hikashop_<?php echo $type . '_' . $this->step . '_' . $this->module_position . '_' . $oneExtraField->field_namekey; ?>">
<?php
		$classname = $labelcolumnclass.' hkcontrol-label';
		echo $this->fieldsClass->getFieldName($oneExtraField, true, $classname);
?>
		<div class="<?php echo $inputcolumnclass;?>">
<?php
		$onWhat = ($oneExtraField->field_type == 'radio') ? 'onclick' : 'onchange';
		echo $this->fieldsClass->display(
				$oneExtraField,
				@$this->$type->$fieldName,
				'data['.$type.']['.$fieldName.']',
				false,
				'class="hkform-control" '.$onWhat.'="window.hikashop.toggleField(this.value,\''.$fieldName.'\',\''.$type . '_' . $this->step . '_' . $this->module_position.'\',0,\'hikashop_\');"',
				false,
				$this->extraFields[$type],
				@$this->$type,
				false
		);
?>
		</div>
	</div>
<?php
	}
}

if(!empty($this->extraData[$this->module_position]) && !empty($this->extraData[$this->module_position]->bottom)) { echo implode("\r\n", $this->extraData[$this->module_position]->bottom); }

?>
	<div class="hkform-group control-group hikashop_registration_required_info_line">
		<div class="controls"><?php echo JText::_('HIKA_REGISTER_REQUIRED'); ?></div>
	</div>
	<input type="hidden" name="data[register][id]" value="<?php echo (int)$this->mainUser->get('id');?>" />
	<input type="hidden" name="data[register][gid]" value="<?php echo (int)$this->mainUser->get('gid');?>" />
	<div class="hkform-group control-group">
		<div class="controls">
			<button type="submit" onclick="window.checkout.submitLogin(<?php echo $this->step; ?>,<?php echo $this->module_position; ?>, 'register'); return false;" class="<?php echo $this->config->get('css_button','hikabtn'); ?> hikabtn_checkout_login_register" id="hikashop_register_form_button"><?php
				echo !empty($this->options['registration_simplified']) ? JText::_('HIKA_NEXT') : JText::_('HIKA_REGISTER');
			?></button>
		</div>
	</div>
</fieldset>
<?php
	if(!empty($this->options['js'])) {
?>
<script type="text/javascript">
<?php echo $this->options['js']; ?>
</script>
<?php
	}
?>
<script type="text/javascript">
window.hikashop.ready(function() {
	if(!document.formvalidator)
		return;
	var container = document.getElementById('hikashop_checkout_login_<?php echo $this->step; ?>_<?php echo $this->module_position; ?>');
	if(container)
		document.formvalidator.attachToForm(container);
});
</script>
