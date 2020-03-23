<?php
defined('_JEXEC') or die;
JHtml::_('behavior.keepalive');
?>
<?php if ($type == 'logout') : ?>
<form action="<?php echo JRoute::_('index.php', true, $params->get('usesecure')); ?>" method=" post" id="login-form">
<?php if ($params->get('greeting')) : ?>
<div class="login-greeting">
<?php if($params->get('name') == 0) : {
echo JText::sprintf('MOD_LOGIN_HINAME', $user->get('name'));
} else : {
echo JText::sprintf('MOD_LOGIN_HINAME', $user->get('username'));
} endif; ?>
</div>
<?php endif; ?>
<div>
<input class="btn btn-default" type="submit" name="Submit" value="<?php echo JText::_('JLOGOUT'); ?>"/>
<div style="clear:both;">
</div>
</div>
<input type="hidden" name="option" value="com_users"/>
<input type="hidden" name="task" value="user.logout"/>
<input type="hidden" name="return" value="<?php echo $return; ?>"/>
	<?php echo JHtml::_('form.token'); ?>
</form>
<?php else : ?>
<form action="<?php echo JRoute::_('index.php', true, $params->get('usesecure')); ?>" method="post" id="login-form">
<?php if ($params->get('pretext')): ?>
<div class="pretext">
<p><?php echo $params->get('pretext'); ?></p>
</div>
<?php endif; ?>
	<fieldset class="userdata">
<p id="form-login-username">
<label for="modlgn-username"><?php echo JText::_('MOD_LOGIN_VALUE_USERNAME') ?></label>
<input id="modlgn-username" type="text" name="username" class="boxcolor" size="18" style="display:block;"/>
</p>
<p id="form-login-password">
<label for="modlgn-passwd"><?php echo JText::_('JGLOBAL_PASSWORD') ?></label>
<input id="modlgn-passwd" type="password" name="password" class="boxcolor" size="18" style="display:block;"/>
</p>
<?php if (count($twofactormethods) > 1) : ?>
<p id="form-login-secretkey">
<?php if (!$params->get('usetext', 0)) : ?>
<label for="modlgn-secretkey" class="element-invisible"><?php echo JText::_('JGLOBAL_SECRETKEY'); ?></label>
<input id="modlgn-secretkey"autocomplete="off" type="text"name="secretkey" class="boxcolor"tabindex="0"size="18"  />
<?php else : ?>
<label for="modlgn-secretkey"><?php echo JText::_('JGLOBAL_SECRETKEY'); ?></label>
<input id="modlgn-secretkey"autocomplete="off"type="text"name="secretkey"class="boxcolor"tabindex="0"size="18"/>
<?php endif; ?>
</p>
<?php endif; ?>
<?php if (JPluginHelper::isEnabled('system', 'remember')) : ?>
	<p id="form-login-remember">
<input id="modlgn-remember" style="margin-top:5px;" type="checkbox" name="remember" value="yes"/>
<label for="modlgn-remember"><?php echo JText::_('MOD_LOGIN_REMEMBER_ME') ?></label>
</p>
<?php endif; ?>
<div>
<input class="btn btn-default" type="submit" name="Submit" value="<?php echo JText::_('JLOGIN') ?>"/>
<div style="clear:both;">
</div>
</div>
<input type="hidden" name="option" value="com_users"/>
	<input type="hidden" name="task" value="user.login"/>
<input type="hidden" name="return" value="<?php echo $return; ?>"/>
<?php echo JHtml::_('form.token'); ?>
</fieldset>
<ul>
<li style="background:none;padding-left:0px;list-style:none;">
<a href="<?php echo JRoute::_('index.php?option=com_users&view=reset'); ?>">
<?php echo JText::_('MOD_LOGIN_FORGOT_YOUR_PASSWORD'); ?></a>
</li>
<li style="background:none;padding-left:0px;list-style:none;">
<a href="<?php echo JRoute::_('index.php?option=com_users&view=remind'); ?>">
<?php echo JText::_('MOD_LOGIN_FORGOT_YOUR_USERNAME'); ?></a>
</li>
<?php
$usersConfig = JComponentHelper::getParams('com_users');
if ($usersConfig->get('allowUserRegistration')) : ?>
<li style="background:none;padding-left:0px;list-style:none;">
<a href="<?php echo JRoute::_('index.php?option=com_users&view=registration'); ?>">
<?php echo JText::_('MOD_LOGIN_REGISTER'); ?></a>
</li>
<?php endif; ?>
</ul>
<?php if ($params->get('posttext')): ?>
<div class="posttext">
<p><?php echo $params->get('posttext'); ?></p>
</div>
<?php endif; ?>
</form>
<?php endif; ?>
