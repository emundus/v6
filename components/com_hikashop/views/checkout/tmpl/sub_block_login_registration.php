<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><fieldset class="hkform-horizontal">
<?php
$labelcolumnclass = 'hkc-sm-4';
$inputcolumnclass = 'hkc-sm-8';
if(!empty($this->options['override_registration']) && !empty($this->options['registration_registration'])) {
	echo hikashop_translate($this->options['text']);
	$this->options['registration_registration'] = false;
}
if(!empty($this->options['registration_registration'])) {
?>
<!-- NAME -->
	<div class="hkform-group control-group hikashop_registration_name_line" id="hikashop_registration_name_line">
		<label id="namemsg" for="register_name" class="<?php echo $labelcolumnclass;?> hkcontrol-label" title=""><?php echo JText::_('HIKA_USER_NAME'); ?>*</label>
		<div class="<?php echo $inputcolumnclass;?>">
			<input type="text" name="data[register][name]" id="register_name" value="<?php echo $this->escape($this->mainUser->get( 'name' ));?>" class="<?php echo HK_FORM_CONTROL_CLASS; ?>" size="30" maxlength="50"/>
		</div>
	</div>
<!-- EO NAME -->
<!-- USERNAME -->
	<div class="hkform-group control-group hikashop_registration_username_line" id="hikashop_registration_username_line">
		<label id="usernamemsg" for="register_username" class="<?php echo $labelcolumnclass;?> hkcontrol-label" title=""><?php echo JText::_('HIKA_USERNAME'); ?>*</label>
		<div class="<?php echo $inputcolumnclass;?>">
			<input type="text" name="data[register][username]" id="register_username" value="<?php echo $this->escape($this->mainUser->get( 'username' ));?>" class="<?php echo HK_FORM_CONTROL_CLASS; ?> validate-username" maxlength="25" size="30" />
		</div>
	</div>
<!-- EO USERNAME -->
<?php
}
?>
<!-- EMAIL -->
	<div class="hkform-group control-group hikashop_registration_email_line">
		<label id="emailmsg" for="register_email" class="<?php echo $labelcolumnclass;?> hkcontrol-label" title=""><?php echo JText::_('HIKA_EMAIL'); ?>*</label>
		<div class="<?php echo $inputcolumnclass;?>">
			<input <?php if($this->config->get('show_email_confirmation_field',0)){echo ' autocomplete="off"';} ?> type="text" name="data[register][email]" id="register_email" value="<?php echo $this->escape($this->mainUser->get( 'email' ));?>" class="<?php echo HK_FORM_CONTROL_CLASS; ?> validate-email" maxlength="100" size="30" />
		</div>
	</div>
<!-- EO EMAIL -->
<!-- EMAIL CONFIRMATION -->
<?php
if(!empty($this->options['registration_email_confirmation'])) {
?>
	<div class="hkform-group control-group hikashop_registration_email_confirm_line">
		<label id="email_confirm_msg" for="register_email_confirm" class="<?php echo $labelcolumnclass;?> hkcontrol-label" title=""><?php echo JText::_('HIKA_EMAIL_CONFIRM'); ?>*</label>
		<div class="<?php echo $inputcolumnclass;?>">
			<input autocomplete="off" type="text" name="data[register][email_confirm]" id="register_email_confirm" value="<?php echo $this->escape($this->mainUser->get('email'));?>" class="<?php echo HK_FORM_CONTROL_CLASS; ?> validate-email" maxlength="100" size="30" onchange="if(this.value!=document.getElementById('register_email').value){alert('<?php echo JText::_('THE_CONFIRMATION_EMAIL_DIFFERS_FROM_THE_EMAIL_YOUR_ENTERED',true); ?>'); this.value = '';}" />
		</div>
	</div>
<?php
}
?>
<!-- EO EMAIL CONFIRMATION -->
<!-- TOP EXTRA DATA -->
<?php
if(!empty($this->extraData[$this->module_position]) && !empty($this->extraData[$this->module_position]->top)) { echo implode("\r\n", $this->extraData[$this->module_position]->top); }
?>
<!-- EO TOP EXTRA DATA -->
<!-- PASSWORD -->
<?php
if(!empty($this->options['registration_registration']) || !empty($this->options['registration_password'])) {
?>
	<div class="hkform-group control-group hikashop_registration_password_line" id="hikashop_registration_password_line">
		<label id="pwmsg" for="register_password" class="<?php echo $labelcolumnclass;?> hkcontrol-label" title=""><?php echo JText::_('HIKA_PASSWORD'); ?>*</label>
		<div class="<?php echo $inputcolumnclass;?>">
<?php
	if(HIKASHOP_J40) {
		$com_usersParams = \Joomla\CMS\Component\ComponentHelper::getParams('com_users');
		$minLength    = (int) $com_usersParams->get('minimum_length', 12);
		$minIntegers  = (int) $com_usersParams->get('minimum_integers', 0);
		$minSymbols   = (int) $com_usersParams->get('minimum_symbols', 0);
		$minUppercase = (int) $com_usersParams->get('minimum_uppercase', 0);
		$minLowercase = (int) $com_usersParams->get('minimum_lowercase', 0);
		$rules = $minLowercase > 0 || $minUppercase > 0 || $minSymbols > 0 || $minIntegers > 0 || $minLength > 0;
		$layout = new JLayoutFile('joomla.form.field.password');
		echo $layout->render(array(
			'meter' => true,
			'class' => 'validate-password',
			'forcePassword' => true,
			'lock' => false,
			'rules' => $rules,
			'hint' => '',
			'readonly' => false,
			'disabled' => false,
			'required' => true,
			'autofocus' => false,
			'dataAttribute' => 'autocomplete="new-password"',
			'name' => 'data[register][password]',
			'id' => 'register_password',
			'minLength' => $minLength,
			'minIntegers' => $minIntegers,
			'minSymbols' => $minSymbols,
			'minUppercase' => $minUppercase,
			'minLowercase' => $minLowercase,
			'value' => '',
		));
	} else {
?>
			<input autocomplete="off" type="password" name="data[register][password]" id="register_password" value="" class="<?php echo HK_FORM_CONTROL_CLASS; ?> validate-password" size="30" >
<?php 
	}
?>
		</div>
	</div>
	<div class="hkform-group control-group hikashop_registration_password2_line" id="hikashop_registration_password2_line">
		<label id="pw2msg" for="register_password2" class="<?php echo $labelcolumnclass;?> hkcontrol-label" title=""><?php echo JText::_('HIKA_VERIFY_PASSWORD'); ?>*</label>
		<div class="<?php echo $inputcolumnclass;?>">
<?php
	if(HIKASHOP_J40) {
		$layout = new JLayoutFile('joomla.form.field.password');
		echo $layout->render(array(
			'meter' => false,
			'class' => 'validate-password',
			'forcePassword' => true,
			'lock' => false,
			'rules' => false,
			'hint' => '',
			'readonly' => false,
			'disabled' => false,
			'required' => true,
			'autofocus' => false,
			'dataAttribute' => 'autocomplete="new-password"',
			'name' => 'data[register][password2]',
			'id' => 'register_password2',
			'value' => '',
		));
	} else {
?>
			<input autocomplete="off" type="password" name="data[register][password2]" id="register_password2" value="" class="<?php echo HK_FORM_CONTROL_CLASS; ?> validate-password" size="30" >
<?php 
	}
?>
		</div>
	</div>
<?php
}
?>
<!-- EO PASSWORD -->
<!-- MIDDLE EXTRA DATA -->
<?php
if(!empty($this->extraData[$this->module_position]) && !empty($this->extraData[$this->module_position]->middle)) { echo implode("\r\n", $this->extraData[$this->module_position]->middle); }
?>
<!-- EO MIDDLE EXTRA DATA -->
<!-- CUSTOM USER FIELDS -->
<?php
$type = 'user';
if(!empty($this->extraFields[$type])) {
	$after = array();
	foreach($this->extraFields[$type] as $fieldName => $field) {
		$onWhat = ($field->field_type == 'radio') ? 'onclick' : 'onchange';
		$html = $this->fieldsClass->display(
			$field,
			@$this->$type->$fieldName,
			'data['.$type.']['.$fieldName.']',
			false,
			' class="'.HK_FORM_CONTROL_CLASS.'" '.$onWhat.'="window.hikashop.toggleField(this.value,\''.$fieldName.'\',\''.$type . '_' . $this->step . '_' . $this->module_position.'\',0,\'hikashop_\');"',
			false,
			$this->extraFields[$type],
			@$this->$type,
			false
		);
		if($field->field_type == 'hidden') {
			$after[] = $html;
			continue;
		}
?>
	<div class="hkform-group control-group hikashop_registration_<?php echo $fieldName;?>_line" id="hikashop_<?php echo $type . '_' . $this->step . '_' . $this->module_position . '_' . $field->field_namekey; ?>">
		<?php
			$classname = $labelcolumnclass.' hkcontrol-label';
			echo $this->fieldsClass->getFieldName($field, true, $classname);
		?>
		<div class="<?php echo $inputcolumnclass;?>">
<?php
			echo $html;
?>
		</div>
	</div>
<?php
	}
	if(count($after)) {
		echo implode("\r\n", $after);
	}
}
?>
<!-- EO CUSTOM USER FIELDS -->
<!-- AFFILIATE -->
<?php
if(!empty($this->options['affiliate_registration'])) {
	$plugin = JPluginHelper::getPlugin('system', 'hikashopaffiliate');
	if(!empty($plugin)) {
?>
	<div class="hkform-group control-group hikashop_registration_affiliate_line">
		<div class="<?php echo $labelcolumnclass;?> hkcontrol-label"></div>
		<div class=" <?php echo $inputcolumnclass;?>">
			<div class="checkbox">
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
				<label>
					<input class="hikashop_affiliate_checkbox" id="hikashop_affiliate_checkbox" type="checkbox" name="hikashop_affiliate_checkbox" value="1" <?php echo $this->affiliate_checked; ?> />
					<?php echo JText::_('BECOME_A_PARTNER');?>
				</label>
<?php
		}
?>
			</div>
		</div>
	</div>
<?php
	}
}
?>
<!-- EO AFFILIATE -->
<?php
if(!empty($this->options['address_on_registration']) && !empty($this->extraFields['address'])) {
	$type = 'address';
?>
<!-- BILLING ADDRESS TITLE -->
	<div class="">
		<legend><?php echo JText::_( 'ADDRESS_INFORMATION' ); ?></legend>
	</div>
<!-- EO BILLING ADDRESS TITLE -->
<!-- BILLING ADDRESS TOP EXTRA DATA -->
<?php
if(!empty($this->extraData[$this->module_position]) && !empty($this->extraData[$this->module_position]->address_top)) { echo implode("\r\n", $this->extraData[$this->module_position]->address_top); }
?>
<!-- EO BILLING ADDRESS TOP EXTRA DATA -->
<!-- CUSTOM BILLING ADDRESS FIELDS -->
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
				'class="'.HK_FORM_CONTROL_CLASS.'" '.$onWhat.'="window.hikashop.toggleField(this.value,\''.$fieldName.'\',\''.$type . '_' . $this->step . '_' . $this->module_position.'\',0,\'hikashop_\');"',
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
?>
<!-- EO CUSTOM BILLING ADDRESS FIELDS -->
<!-- BILLING ADDRESS BOTTOM EXTRA DATA -->
<?php
	if(!empty($this->extraData[$this->module_position]) && !empty($this->extraData[$this->module_position]->address_bottom)) { echo implode("\r\n", $this->extraData[$this->module_position]->address_bottom); }
?>
<!-- EO BILLING ADDRESS BOTTOM EXTRA DATA -->
<?php
	if(!empty($this->options['same_address'])) {
		$checked = '';
		$attribute = '';
		if(!empty($this->options['same_address_pre_checked'])) {
			$checked = ' checked';
			$attribute = ' style="display:none;"';
		}
		$type = 'shipping_address';
?>
<!-- SAME ADDRESS CHECKBOX -->
	<div class="hkform-group control-group hikashop_registration_same_address_line" id="hikashop_address_<?php echo $this->step . '_' . $this->module_position . '_same_address'; ?>">
		<div class="<?php echo $labelcolumnclass;?> hkcontrol-label"></div>
		<div class="<?php echo $inputcolumnclass;?>">
			<input class="hikashop_checkout_same_address_checkbox" id="hikashop_address_<?php echo $this->step . '_' . $this->module_position . '_same_address_input'; ?>" data-displayzone="hikashop_registration_shipping_address_<?php echo $this->step . '_' . $this->module_position; ?>" onchange="window.checkout.sameAddressToggle(this);" type="checkbox" name="data[same_address]"<?php echo $checked; ?> value="1"/>
			<label for="hikashop_address_<?php echo $this->step . '_' . $this->module_position . '_same_address_input'; ?>"><?php echo JText::_('SAME_FOR_SHIPPING'); ?></label>
		</div>
	</div>
<!-- EO SAME ADDRESS CHECKBOX -->
<!-- SHIPPING ADDRESS TITLE -->
	<div class="hikashop_registration_shipping_address_title" id="hikashop_registration_shipping_address_<?php echo $this->step . '_' . $this->module_position; ?>_title" <?php echo $attribute; ?>>
		<legend><?php echo JText::_( 'HIKASHOP_SHIPPING_ADDRESS' ); ?></legend>
	</div>
<!-- EO SHIPPING ADDRESS TITLE -->
	<div class="hikashop_registration_shipping_address" id="hikashop_registration_shipping_address_<?php echo $this->step . '_' . $this->module_position; ?>" <?php echo $attribute; ?>>
<!-- SHIPPING ADDRESS TOP EXTRA DATA -->
<?php
		if(!empty($this->extraFields[$type]) && !empty($this->extraData[$this->module_position]) && !empty($this->extraData[$this->module_position]->address_shipping_top)) { echo implode("\r\n", $this->extraData[$this->module_position]->address_shipping_top); }
?>
<!-- EO SHIPPING ADDRESS TOP EXTRA DATA -->
<!-- CUSTOM SHIPPING ADDRESS FIELDS -->
<?php
		foreach($this->extraFields[$type] as $fieldName => $oneExtraField) {
?>
		<div class="hkform-group control-group hikashop_registration_<?php echo $fieldName;?>_line" id="hikashop_address_shipping_<?php echo $this->step . '_' . $this->module_position . '_' . $oneExtraField->field_namekey; ?>">
<?php
		$classname = $labelcolumnclass.' hkcontrol-label';
		echo $this->fieldsClass->getFieldName($oneExtraField, true, $classname);
?>
			<div class="<?php echo $inputcolumnclass;?>">
<?php
		$onWhat = ($oneExtraField->field_type == 'radio') ? 'onclick' : 'onchange';
		$this->fieldsClass->prefix = 'shipping_';
		echo $this->fieldsClass->display(
				$oneExtraField,
				@$this->$type->$fieldName,
				'data['.$type.']['.$fieldName.']',
				false,
				'class="'.HK_FORM_CONTROL_CLASS.'" '.$onWhat.'="window.hikashop.toggleField(this.value,\''.$fieldName.'\',\'address_shipping_' . $this->step . '_' . $this->module_position.'\',0,\'hikashop_\');"',
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
?>
<!-- EO CUSTOM SHIPPING ADDRESS FIELDS -->
<!-- SHIPPING ADDRESS BOTTOM EXTRA DATA -->
<?php
	if(!empty($this->extraFields[$type]) && !empty($this->extraData[$this->module_position]) && !empty($this->extraData[$this->module_position]->address_shipping_bottom)) { echo implode("\r\n", $this->extraData[$this->module_position]->address_shipping_bottom); }
?>
<!-- EO SHIPPING ADDRESS BOTTOM EXTRA DATA -->
	</div>
<?php

	}
}
?>
<!-- PRIVACY CONSENT -->
<?php
if(!empty($this->options['privacy'])) {
?>
<fieldset id="hikashop_registration_privacy_area">
	<legend>
<?php
	echo JText::_('PLG_SYSTEM_PRIVACYCONSENT_LABEL');
?>
	</legend>
<?php
	if(!empty($this->options['privacy_text']))
		hikashop_display($this->options['privacy_text'], 'info');
?>
	<div class="hkform-group control-group hikashop_registration_privacy_line">
		<div class="<?php echo $labelcolumnclass;?> hkcontrol-label">
<?php
	$text = JText::_('PLG_SYSTEM_PRIVACYCONSENT_FIELD_LABEL').'<span class="hikashop_field_required_label">*</span>';
	if(!empty($this->options['privacy_id']) || !empty($this->options['privacy_url'])) {
		$popupHelper = hikashop_get('helper.popup');
		$text = $popupHelper->display(
			$text,
			'PLG_SYSTEM_PRIVACYCONSENT_FIELD_LABEL',
			JRoute::_('index.php?option=com_hikashop&ctrl=checkout&task=privacyconsent&tmpl=component'),
			'shop_privacyconsent',
			800, 500, '', '', 'link'
		);
	}
	echo $text;
?>
		</div>
		<div class=" <?php echo $inputcolumnclass;?>">
<?php
	echo JHTML::_('hikaselect.booleanlist', "data[register][privacy]" , '', 0, JText::_('PLG_SYSTEM_PRIVACYCONSENT_OPTION_AGREE'), JText::_('JNO')	);
?>
		</div>
	</div>
</fieldset>
<?php
}
?>
<!-- EO PRIVACY CONSENT -->
<!-- PRIVACY CONSENT GUEST -->
<?php
if(!empty($this->options['privacy_guest'])) {
?>
	<div class="hkform-group control-group" id="hikashop_registration_privacy_guest_area">
		<div class="<?php echo $labelcolumnclass;?> hkcontrol-label">
<?php
	$text = JText::_( 'PLG_CONTENT_CONFIRMCONSENT_CONSENTBOX_LABEL' ) . '<span class="hikashop_field_required_label">*</span>';
	if(!empty($this->options['privacy_guest_id']) || !empty($this->options['privacy_guest_url'])) {
		$popupHelper = hikashop_get('helper.popup');
		$text = $popupHelper->display(
			$text,
			'PLG_CONTENT_CONFIRMCONSENT_CONSENTBOX_LABEL',
			JRoute::_('index.php?option=com_hikashop&ctrl=checkout&task=privacyconsent&type=contact&tmpl=component'),
			'shop_privacyconsent',
			800, 500, '', '', 'link'
		);
	}
	echo $text;
?>
		</div>
		<div class=" <?php echo $inputcolumnclass;?>">
			<label class="checkbox">
				<input type="checkbox" id="hikashop_privacy_consent_guest" name="data[register][privacy_guest]" value="1"/> <?php echo $this->options['privacy_guest_text']; ?>
			</label>
			<input type="hidden" name="data[register][privacy_guest_check]" value="1"/>
		</div>
	</div>
</fieldset>
<?php
}
?>
<!-- EO PRIVACY CONSENT GUEST -->
<!-- BOTTOM EXTRA DATA -->
<?php
if(!empty($this->extraData[$this->module_position]) && !empty($this->extraData[$this->module_position]->bottom)) { echo implode("\r\n", $this->extraData[$this->module_position]->bottom); }

?>
<!-- EO BOTTOM EXTRA DATA -->
<!-- REQUIRED FIELDS TEXT -->
	<div class="hkform-group control-group hikashop_registration_required_info_line">
		<div class="<?php echo $labelcolumnclass;?> hkcontrol-label"></div>
		<div class="<?php echo $inputcolumnclass;?>"><?php echo JText::_('HIKA_REGISTER_REQUIRED'); ?></div>
	</div>
<!-- EO REQUIRED FIELDS TEXT -->
<!-- NEXT BUTTON -->
<?php
	if(!empty($this->options['show_submit'])) {
?>
	<div class="hkform-group control-group">
		<div class="<?php echo $labelcolumnclass;?> hkcontrol-label"></div>
		<div class="<?php echo $inputcolumnclass;?>">
			<button type="submit" onclick="window.checkout.submitLogin(<?php echo $this->step; ?>,<?php echo $this->module_position; ?>, 'register'); return false;" class="<?php echo $this->config->get('css_button','hikabtn'); ?> hikabtn_checkout_login_register" id="hikashop_register_form_button"><?php
				echo !empty($this->options['registration_simplified']) || !empty($this->options['registration_guest']) ? JText::_('HIKA_NEXT') : JText::_('HIKA_REGISTER');
			?></button>
		</div>
	</div>
<?php
	}
?>
<!-- EO NEXT BUTTON -->
	<input type="hidden" name="data[register][id]" value="<?php echo (int)$this->mainUser->get('id');?>" />
	<input type="hidden" name="data[register][gid]" value="<?php echo (int)$this->mainUser->get('gid');?>" />
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
	var container = document.getElementById('hikashop_checkout_form');
	if(container)
		document.formvalidator.attachToForm(container);
});
window.checkout.sameAddressToggle = function(el) {
	var d = document, zoneName = el.getAttribute('data-displayzone'), zone = d.getElementById(zoneName), title = d.getElementById(zoneName+'_title');
	if(!zone)
		return;
	if(el.checked)
		zone.style.display = 'none';
	else
		zone.style.display = '';
	if(!title)
		return;
	if(el.checked)
		title.style.display = 'none';
	else
		title.style.display = '';
};
</script>
