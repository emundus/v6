<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php

if($this->display_method == 1) {
	$this->simplified_registration = explode(',',$this->simplified_registration);
	$this->simplified_registration = array_shift($this->simplified_registration);
}

?>
<div class="hkform-horizontal hikashop_contentpane">
<?php
	if(!$this->simplified_registration) {
?>
<!-- NAME -->
<div class="hkform-group control-group hikashop_registration_name_line" id="hikashop_registration_name_line">
	<label id="namemsg" for="register_name" class="hkc-sm-4 hkcontrol-label">
		<?php echo JText::_( 'HIKA_USER_NAME' ).'*'; ?>
	</label>
	<div class="hkc-sm-8">
		<input type="text" name="data[register][name]" id="register_name" value="<?php echo $this->escape($this->mainUser->get( 'name' ));?>" class="<?php echo HK_FORM_CONTROL_CLASS; ?> validate-username required" maxlength="50" />
	</div>
</div>
<!-- EO NAME -->
<!-- USERNAME -->
<div class="hkform-group control-group hikashop_registration_username_line" id="hikashop_registration_username_line">
	<label id="usernamemsg" for="register_username" class="hkc-sm-4 hkcontrol-label">
		<?php echo JText::_( 'HIKA_USERNAME' ).'*'; ?>
	</label>
	<div class="hkc-sm-8">
		<input type="text" id="register_username" name="data[register][username]" value="<?php echo $this->escape($this->mainUser->get( 'username' ));?>" class="<?php echo HK_FORM_CONTROL_CLASS; ?> required validate-username" maxlength="25" />
	</div>
</div>
<!-- EO USERNAME -->
<?php
	}
?>
<!-- EMAIL -->
<div class="hkform-group control-group hikashop_registration_email_line">
	<label id="emailmsg" for="register_email" class="hkc-sm-4 hkcontrol-label">
		<?php echo JText::_( 'HIKA_EMAIL' ).'*'; ?>
	</label>
	<div class="hkc-sm-8">
		<input <?php if($this->config->get('show_email_confirmation_field',0)){echo ' autocomplete="off"';} ?> type="text" id="register_email" name="data[register][email]" value="<?php echo $this->escape($this->mainUser->get( 'email' ));?>" class="<?php echo HK_FORM_CONTROL_CLASS; ?> required validate-email" maxlength="100" />
	</div>
</div>
<!-- EO EMAIL -->
<!-- CONFIRM EMAIL -->
<?php
	if($this->config->get('show_email_confirmation_field',0)) {
?>
<div class="hkform-group control-group hikashop_registration_email_confirm_line">
	<label id="email_confirm_msg" for="register_email_confirm" class="hkc-sm-4 hkcontrol-label">
		<?php echo JText::_( 'HIKA_EMAIL_CONFIRM' ).'*'; ?>
	</label>
	<div class="hkc-sm-8">
		<input autocomplete="off" type="text" id="register_email_confirm" name="data[register][email_confirm]" value="<?php echo $this->escape($this->mainUser->get( 'email' ));?>" class="<?php echo HK_FORM_CONTROL_CLASS; ?> required validate-email" maxlength="100" onchange="if(this.value!=document.getElementById('register_email').value){alert('<?php echo JText::_('THE_CONFIRMATION_EMAIL_DIFFERS_FROM_THE_EMAIL_YOUR_ENTERED',true); ?>'); this.value = '';}" />
	</div>
</div>
<?php
	}
?>
<!-- EO CONFIRM EMAIL -->
<!-- TOP EXTRA DATA -->
<?php
	if(!empty($this->extraData) && !empty($this->extraData->top)) { echo implode("\r\n", $this->extraData->top); }
?>
<!-- EO TOP EXTRA DATA -->
<!-- PASSWORD -->
<?php
	if(!$this->simplified_registration || $this->simplified_registration == 3) {
?>
<div class="hkform-group control-group hikashop_registration_password_line" id="hikashop_registration_password_line">
	<label id="pwmsg" for="password"  class="hkc-sm-4 hkcontrol-label">
		<?php echo JText::_( 'HIKA_PASSWORD' ).'*'; ?>
	</label>
	<div class="hkc-sm-8">
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
		<input autocomplete="off" class="<?php echo HK_FORM_CONTROL_CLASS; ?> required  validate-password" type="password" id="register_password" name="data[register][password]" value="" />
<?php 
	}
?>
	</div>
</div>
<div class="hkform-group control-group hikashop_registration_password2_line" id="hikashop_registration_password2_line">
	<label id="pw2msg" for="register_password2"  class="hkc-sm-4 hkcontrol-label">
		<?php echo JText::_( 'HIKA_VERIFY_PASSWORD' ).'*'; ?>
	</label>
	<div class="hkc-sm-8">
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
		<input autocomplete="off" class="<?php echo HK_FORM_CONTROL_CLASS; ?> required  validate-passverify" type="password" id="register_password2" name="data[register][password2]" value="" />
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
	if(!empty($this->extraData) && !empty($this->extraData->middle)) { echo implode("\r\n", $this->extraData->middle); }
?>
<!-- EO MIDDLE EXTRA DATA -->
<!-- CUSTOM USER FIELDS -->
<?php
	$this->setLayout('custom_fields');
	$this->type = 'user';
	echo $this->loadTemplate();
?>
<!-- EO CUSTOM USER FIELDS -->
<!-- AFFILIATE -->
<?php
	if($this->config->get('affiliate_registration',0)){
		$plugin = JPluginHelper::getPlugin('system', 'hikashopaffiliate');
		if(!empty($plugin)){
?>
<div class="hkform-group control-group hikashop_registration_affiliate_line" id="hikashop_registration_affiliate_line">
	<div class="hkc-sm-4 hkcontrol-label"></div>
	<div class="hkc-sm-8">
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
<!-- CUSTOM ADDRESS FIELDS -->
<?php
	if($this->config->get('address_on_registration',1)) {
?>
<div class="hikashop_registration_address_info_line">
	<div colspan="2" height="40">
		<h3 class="hikashop_registration_address_info_title"><?php echo JText::_( 'ADDRESS_INFORMATION' ); ?></h3>
	</div>
</div>
<?php
		if(!empty($this->extraData) && !empty($this->extraData->address_top)) { echo implode("\r\n", $this->extraData->address_top); }
		$this->type = 'address';
		echo $this->loadTemplate();
		if(!empty($this->extraData) && !empty($this->extraData->address_bottom)) { echo implode("\r\n", $this->extraData->address_bottom); }
	}
?>
<!-- EO CUSTOM ADDRESS FIELDS -->
<!-- PRIVACY CONSENT -->
<?php
	if(!empty($this->options['privacy'])) {
?>
<fieldset>
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
		<div class="hkc-sm-4 hkcontrol-label">
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
		<div class="hkc-sm-8">
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
<!-- BOTTOM EXTRA DATA -->
<?php
	if(!empty($this->extraData) && !empty($this->extraData->bottom)) { echo implode("\r\n", $this->extraData->bottom); }
?>
<!-- EO BOTTOM EXTRA DATA -->
<!-- REQUIRED FIELDS TEXT -->
<div class="hkform-group control-group hikashop_registration_required_info_line">
	<label class='hkc-sm-4 hkcontrol-label'></label>
	<span>
		<?php echo JText::_( 'HIKA_REGISTER_REQUIRED' ); ?>
	</span>
</div>
<!-- EO REQUIRED FIELDS TEXT -->
<!-- REGISTER BUTTON -->
<?php
if(empty($this->form_name)) {
	$this->form_name = 'hikashop_checkout_form';
}
$registerButtonName = JText::_('HIKA_REGISTER');
if($this->simplified_registration == 2) {
	$registerButtonName = JText::_('HIKA_NEXT');
}
?>
<label class='hkc-sm-4 hkcontrol-label'></label>
<button type="submit" onclick="var field=document.getElementById(
		'hikashop_product_quantity_field_1'
	);
	hikashopSubmitForm('<?php echo $this->form_name; ?>', 'register');
	return false;"
	class="<?php echo $this->config->get('css_button','hikabtn'); ?> hikabtn_checkout_login_register"
	id="hikashop_register_form_button"><?php
		echo $registerButtonName;
?></button>
<!-- EO REGISTER BUTTON -->
<input type="hidden" name="data[register][id]" value="<?php echo (int)$this->mainUser->get( 'id' );?>" />
<input type="hidden" name="data[register][gid]" value="<?php echo (int)$this->mainUser->get( 'gid' );?>" />
</div>
