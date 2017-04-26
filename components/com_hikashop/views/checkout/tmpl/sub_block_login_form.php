<?php
/**
 * @package	HikaShop for Joomla!
 * @version	3.0.1
 * @author	hikashop.com
 * @copyright	(C) 2010-2017 HIKARI SOFTWARE. All rights reserved.
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

if(!HIKASHOP_J16) {
	$reset_url = 'index.php?option=com_user&view=reset';
	$remind_url = 'index.php?option=com_user&view=remind';
} else {
	$reset_url = 'index.php?option=com_users&view=reset';
	$remind_url = 'index.php?option=com_users&view=remind';
}

if(!HIKASHOP_RESPONSIVE) {
?>
	<p id="com-form-login-username">
		<label for="username"><?php echo JText::_('HIKA_USERNAME') ?></label><br />
		<input type="text" id="username" name="login[username]" class="inputbox" alt="username" size="18" />
	</p>
	<p id="com-form-login-password">
		<label for="passwd"><?php echo JText::_('HIKA_PASSWORD') ?></label><br />
		<input type="password" id="passwd" name="login[passwd]" class="inputbox" size="18" alt="password" />
	</p>
<?php
	if(JPluginHelper::isEnabled('system', 'remember')) {
?>
	<p id="com-form-login-remember">
		<label for="remember"><?php echo JText::_('HIKA_REMEMBER_ME') ?></label>
		<input type="checkbox" id="remember" name="login[remember]" value="yes" alt="Remember Me" />
	</p>
<?php
	}
?>
	<button type="submit" onclick="window.checkout.submitLogin(<?php echo $this->step; ?>,<?php echo $this->module_position; ?>, 'login'); return false;" class="<?php echo $this->config->get('css_button','hikabtn'); ?> hikabtn_checkout_login_form"><?php
		echo JText::_('HIKA_LOGIN');
	?></button>

	<ul>
		<li>
			<a href="<?php echo JRoute::_( $reset_url ); ?>"><?php
				echo JText::_('HIKA_FORGOT_YOUR_PASSWORD');
			?></a>
		</li>
		<li>
			<a href="<?php echo JRoute::_( $remind_url ); ?>"><?php
				echo JText::_('HIKA_FORGOT_YOUR_USERNAME');
			?></a>
		</li>
	</ul>
<?php

} else {

?>
<div class="userdata form-inline">
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
</div>
<?php
	}
