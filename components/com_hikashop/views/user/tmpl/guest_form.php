<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><form action="<?php echo hikashop_completeLink('user&task=guest_register'.$this->url_itemid); ?>" method="post" name="hikashop_guest_registration_form" enctype="multipart/form-data">
	<div class="hikashop_user_guest_registration_page">
		<h2><?php echo JText::_('HIKA_REGISTRATION');?></h2>
		<fieldset class="hkform-horizontal">
<?php
$labelcolumnclass = 'hkc-sm-4';
$inputcolumnclass = 'hkc-sm-8';
?>
<!-- NAME -->
			<div class="hkform-group control-group hikashop_registration_name_line" id="hikashop_registration_name_line">
				<label id="namemsg" for="register_name" class="<?php echo $labelcolumnclass;?> hkcontrol-label" title=""><?php echo JText::_('HIKA_USER_NAME'); ?>*</label>
				<div class="<?php echo $inputcolumnclass;?>">
					<input type="text" name="data[register][name]" id="register_name" value="<?php echo $this->escape((string)@$this->user->name);?>" class="<?php echo HK_FORM_CONTROL_CLASS; ?>" size="30" maxlength="50"/>
				</div>
			</div>
<!-- EO NAME -->
<!-- USERNAME -->
			<div class="hkform-group control-group hikashop_registration_username_line" id="hikashop_registration_username_line">
				<label id="usernamemsg" for="register_username" class="<?php echo $labelcolumnclass;?> hkcontrol-label" title=""><?php echo JText::_('HIKA_USERNAME'); ?>*</label>
				<div class="<?php echo $inputcolumnclass;?>">
					<input type="text" name="data[register][username]" id="register_username" value="<?php echo $this->escape((string)@$this->user->username);?>" class="<?php echo HK_FORM_CONTROL_CLASS; ?> validate-username" maxlength="25" size="30" />
				</div>
			</div>
<!-- EO USERNAME -->
<!-- PASSWORD -->
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
<!-- EO PASSWORD -->
<!-- VERIFY PASSWORD -->
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
<!-- EO VERIFY PASSWORD -->
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
			<input type="hidden" name="order_token" value="<?php echo hikaInput::get()->getVar('order_token');?>" />
			<input type="hidden" name="order_id" value="<?php echo hikashop_getCID('order_id');?>" />
			<div class="hkform-group control-group">
				<div class="controls">
<!-- REGISTER BUTTON -->
					<button type="submit" class="<?php echo $this->config->get('css_button','hikabtn'); ?> hikabtn_guest_register_button" id="hikashop_guest_register_form_button"><?php
						echo JText::_('HIKA_REGISTER');
					?></button>
<!-- EO REGISTER BUTTON -->
				</div>
			</div>
		</fieldset>
	</div>
</form>
