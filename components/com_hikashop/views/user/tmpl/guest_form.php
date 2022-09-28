<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.6.2
 * @author	hikashop.com
 * @copyright	(C) 2010-2022 HIKARI SOFTWARE. All rights reserved.
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
					<input type="text" name="data[register][name]" id="register_name" value="<?php echo $this->escape(@$this->user->name);?>" class="hkform-control" size="30" maxlength="50"/>
				</div>
			</div>
<!-- EO NAME -->
<!-- USERNAME -->
			<div class="hkform-group control-group hikashop_registration_username_line" id="hikashop_registration_username_line">
				<label id="usernamemsg" for="register_username" class="<?php echo $labelcolumnclass;?> hkcontrol-label" title=""><?php echo JText::_('HIKA_USERNAME'); ?>*</label>
				<div class="<?php echo $inputcolumnclass;?>">
					<input type="text" name="data[register][username]" id="register_username" value="<?php echo $this->escape(@$this->user->username);?>" class="hkform-control validate-username" maxlength="25" size="30" />
				</div>
			</div>
<!-- EO USERNAME -->
<!-- PASSWORD -->
			<div class="hkform-group control-group hikashop_registration_password_line" id="hikashop_registration_password_line">
				<label id="pwmsg" for="register_password" class="<?php echo $labelcolumnclass;?> hkcontrol-label" title=""><?php echo JText::_('HIKA_PASSWORD'); ?>*</label>
				<div class="<?php echo $inputcolumnclass;?>">
					<input autocomplete="off" type="password" name="data[register][password]" id="register_password" value="" class="hkform-control validate-password" size="30" >
				</div>
			</div>
<!-- EO PASSWORD -->
<!-- VERIFY PASSWORD -->
			<div class="hkform-group control-group hikashop_registration_password2_line" id="hikashop_registration_password2_line">
				<label id="pw2msg" for="register_password2" class="<?php echo $labelcolumnclass;?> hkcontrol-label" title=""><?php echo JText::_('HIKA_VERIFY_PASSWORD'); ?>*</label>
				<div class="<?php echo $inputcolumnclass;?>">
					<input autocomplete="off" type="password" name="data[register][password2]" id="register_password2" value="" class="hkform-control validate-password" size="30" >
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
