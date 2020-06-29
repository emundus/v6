<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2020 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
if(JPluginHelper::isEnabled('authentication', 'openid')) {
	$lang = JFactory::getLanguage();
	$lang->load('plg_authentication_openid', JPATH_ADMINISTRATOR);
	$langScript = 'var JLanguage = {};'."\r\n".
		' JLanguage.WHAT_IS_OPENID = \''.JText::_('WHAT_IS_OPENID').'\';'."\r\n".
		' JLanguage.LOGIN_WITH_OPENID = \''.JText::_('LOGIN_WITH_OPENID').'\';'."\r\n".
		' JLanguage.NORMAL_LOGIN = \''.JText::_('NORMAL_LOGIN').'\';'."\r\n".
		' var comlogin = 1;';
	$doc = JFactory::getDocument();
	$doc->addScriptDeclaration($langScript);
	JHTML::_('script', 'openid.js');
}

$reset_url = 'index.php?option=com_users&view=reset';
$remind_url = 'index.php?option=com_users&view=remind';

if(!HIKASHOP_RESPONSIVE) {
	$labelcolumnclass = 'hkc-sm-4';
	$inputcolumnclass = 'hkc-sm-8';
?>
<fieldset class="hkform-horizontal">
<?php
if(!empty($this->extraData[$this->module_position]) && !empty($this->extraData[$this->module_position]->loginTop)) { echo implode("\r\n", $this->extraData[$this->module_position]->loginTop); }
?>
	<div class="hkform-group control-group hikashop_login_username_line">
		<label for="username" class="<?php echo $labelcolumnclass;?> hkcontrol-label"><?php echo JText::_('HIKA_USERNAME') ?></label>
		<div class="<?php echo $inputcolumnclass;?>">
			<input type="text" id="username" name="login[username]" class="inputbox" alt="<?php echo JText::_('HIKA_USERNAME') ?>" size="18" />
		</div>
	</div>
	<div class="hkform-group control-group hikashop_login_password_line">
		<label for="passwd" class="<?php echo $labelcolumnclass;?> hkcontrol-label"><?php echo JText::_('HIKA_PASSWORD') ?></label>
		<div class="<?php echo $inputcolumnclass;?>">
			<input type="password" id="passwd" name="login[passwd]" class="inputbox" size="18" alt="<?php echo JText::_('HIKA_PASSWORD') ?>" />
		</div>
	</div>
<?php
	if(JPluginHelper::isEnabled('system', 'remember')) {
?>
	<div class="hkform-group control-group hikashop_login_remember_line">
		<div class="<?php echo $labelcolumnclass;?> hkcontrol-label"></div>
		<div class=" <?php echo $inputcolumnclass;?>">
			<div class="hkcheckbox">
				<label for="remember">
					<input type="checkbox" id="remember" name="login[remember]" value="yes" class="hkform-control" alt="<?php echo JText::_('HIKA_REMEMBER_ME') ?>" />
					<?php echo JText::_('HIKA_REMEMBER_ME') ?>
				</label>
			</div>
		</div>
	</div>
<?php
	}
?>
	<div class="hkform-group control-group hikashop_login_button_line">
		<div class="<?php echo $labelcolumnclass;?> hkcontrol-label"></div>
		<div class=" <?php echo $inputcolumnclass;?>">
			<button type="submit" onclick="window.checkout.submitLogin(<?php echo $this->step; ?>,<?php echo $this->module_position; ?>, 'login'); return false;" class="<?php echo $this->config->get('css_button','hikabtn'); ?> hikabtn_checkout_login_form">
				<?php echo JText::_('HIKA_LOGIN'); ?>
			</button>
		</div>
	</div>
	<div class="hkform-group control-group hikashop_login_forgot_password_line">
		<div class="<?php echo $labelcolumnclass;?> hkcontrol-label"></div>
		<div class=" <?php echo $inputcolumnclass;?>">
			<a href="<?php echo JRoute::_( $reset_url ); ?>">
				<?php echo JText::_('HIKA_FORGOT_YOUR_PASSWORD'); ?>
			</a>
		</div>
	</div>
	<div class="hkform-group control-group hikashop_login_forgot_username_line">
		<div class="<?php echo $labelcolumnclass;?> hkcontrol-label"></div>
		<div class=" <?php echo $inputcolumnclass;?>">
			<a href="<?php echo JRoute::_( $remind_url ); ?>">
				<?php echo JText::_('HIKA_FORGOT_YOUR_USERNAME'); ?>
			</a>
		</div>
	</div>
<?php
if(!empty($this->extraData[$this->module_position]) && !empty($this->extraData[$this->module_position]->loginBottom)) { echo implode("\r\n", $this->extraData[$this->module_position]->loginBottom); }
?>
</fieldset>
<?php

} else {

?>
<div class="userdata form-inline">
<?php
if(!empty($this->extraData[$this->module_position]) && !empty($this->extraData[$this->module_position]->loginTop)) { echo implode("\r\n", $this->extraData[$this->module_position]->loginTop); }
?>
	<div id="form-login-username" class="control-group">
		<div class="controls">
			<div class="input-prepend input-append">
				<span class="add-on">
					<i class="icon-user tip" title="<?php echo JText::_('HIKA_USERNAME'); ?>"></i>
					<label for="modlgn-username" class="element-invisible"><?php echo JText::_('HIKA_USERNAME'); ?></label>
				</span>
				<input id="modlgn-username" type="text" name="login[username]" class="input-small" tabindex="1" size="18" placeholder="<?php echo JText::_('HIKA_USERNAME'); ?>" />
				<a href="<?php echo JRoute::_( $remind_url );?>" class="btn hasTooltip" title="<?php echo JText::_('HIKA_FORGOT_YOUR_USERNAME'); ?>"><i class="icon-question-sign"></i></a>
			</div>
		</div>
	</div>
	<div id="form-login-password" class="control-group">
		<div class="controls">
			<div class="input-prepend input-append">
				<span class="add-on">
					<i class="icon-lock tip" title="<?php echo JText::_('HIKA_PASSWORD') ?>"></i>
					<label for="modlgn-passwd" class="element-invisible"><?php echo JText::_('HIKA_PASSWORD') ?></label>
				</span>
				<input id="modlgn-passwd" type="password" name="login[passwd]" class="input-small" tabindex="2" size="18" placeholder="<?php echo JText::_('HIKA_PASSWORD') ?>" />
				<a href="<?php echo JRoute::_( $reset_url );?>" class="btn hasTooltip" title="<?php echo JText::_('HIKA_FORGOT_YOUR_PASSWORD'); ?>"><i class="icon-question-sign"></i></a>
			</div>
		</div>
	</div>
<?php
	if(JPluginHelper::isEnabled('system', 'remember')) {
?>
	<div id="form-login-remember" class="control-group checkbox">
		<label for="modlgn-remember" class="control-label"><?php echo JText::_('HIKA_REMEMBER_ME') ?></label>
		<input id="modlgn-remember" type="checkbox" name="login[remember]" value="yes"/>
	</div>
<?php
	}
?>
	<div id="form-login-submit" class="control-group">
		<div class="controls">
			<button type="submit" onclick="window.checkout.submitLogin(<?php echo $this->step; ?>,<?php echo $this->module_position; ?>, 'login'); return false;" class="<?php echo $this->config->get('css_button','hikabtn'); ?> hikabtn_checkout_login_form"><?php
				echo JText::_('HIKA_LOGIN');
			?></button>
		</div>
	</div>
	<div class="control-group hikashop_login_forgot_password_line">
		<div class="controls">
			<a href="<?php echo JRoute::_( $reset_url ); ?>">
				<?php echo JText::_('HIKA_FORGOT_YOUR_PASSWORD'); ?>
			</a>
		</div>
	</div>
	<div class="control-group hikashop_login_forgot_username_line">
		<div class="controls">
			<a href="<?php echo JRoute::_( $remind_url ); ?>">
				<?php echo JText::_('HIKA_FORGOT_YOUR_USERNAME'); ?>
			</a>
		</div>
	</div>
<?php
if(!empty($this->extraData[$this->module_position]) && !empty($this->extraData[$this->module_position]->loginBottom)) { echo implode("\r\n", $this->extraData[$this->module_position]->loginBottom); }
?>
</div>
<?php
	}
