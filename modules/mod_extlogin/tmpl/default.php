<?php // no direct access
defined('_JEXEC') or die('Restricted access'); 
JHTML::stylesheet( 'emundus.css', JURI::Base().'modules/mod_extlogin/style/' );
$usersConfig = JComponentHelper::getParams( 'com_users' );

if($type == 'logout') : ?>
<form action="index.php" method="post" name="login" id="form-login-nav">
<span class="logout">
	<?php if (isset($user->avatar)) { ?>
		<span id="log_photo"><img src="<?php echo EMUNDUS_PATH_REL.$user->id.'/tn_'.$user->avatar; ?>" width="30" align="middle" /></span>
	<?php } ?>
  
	<span class="logout-button">
		<button value="<?php echo JText::_( 'BUTTON_LOGOUT'); ?>" name="Submit" type="submit" title="<?php echo JText::_('BUTTON_LOGOUT'); ?>"><?php echo JText::_( 'BUTTON_LOGOUT'); ?></button>
	</span>
	<span class="log_updateprofile">
        <button value="<?php echo JText::_( 'PROFILE'); ?>" name="profile" type="button" onclick="self.location.href='index.php?option=com_users&view=profile&layout=edit'" title="<?php echo JText::_('UPDATE_PROFILE'); ?>"><?php echo JText::_( 'PROFILE'); ?></button>
    </span>
	<?php if ($params->get('greeting')) { ?><span id="log_username"><?php echo $user->get('firstname'); ?> </span><?php } ?>
		<input type="hidden" name="option" value="com_users" />
		<input type="hidden" name="task" value="user.logout" />
		<input type="hidden" name="return" value="<?php echo $return; ?>" />
		<?php echo JHtml::_('form.token'); ?>
</span>
	
</form>
<?php else : ?>
	<?php if(JPluginHelper::isEnabled('authentication', 'openid')) :
		$lang->load( 'plg_authentication_openid', JPATH_ADMINISTRATOR );
			$langScript = 	'var JLanguage = {};'.
							' JLanguage.WHAT_IS_OPENID = \''.JText::_( 'WHAT_IS_OPENID' ).'\';'.
							' JLanguage.LOGIN_WITH_OPENID = \''.JText::_( 'LOGIN_WITH_OPENID' ).'\';'.
							' JLanguage.NORMAL_LOGIN = \''.JText::_( 'NORMAL_LOGIN' ).'\';'.
							' var modlogin = 1;';
			$document = &JFactory::getDocument();
			$document->addScriptDeclaration( $langScript );
			JHTML::_('script', 'openid.js');
	endif; ?>



		
<form action="<?php echo JRoute::_('index.php', true, $params->get('usesecure')); ?>" method="post" name="login" id="form-login-nav" >
	<?php echo $params->get('pretext'); ?>

	<span class="username">
		<input id="em_modlgn-username" type="text" name="username" class="inputbox"  size="18" value="<?php echo JText::_( 'USERNAME_CONNEXION' ); ?>" onblur="if(this.value=='') this.value='<?php echo JText::_( 'USERNAME_CONNEXION' ); ?>';" onfocus="if(this.value=='<?php echo JText::_( 'USERNAME_CONNEXION' ); ?>') this.value='';"/>
	</span>

	<span class="password">
		<input id="em_modlgn-passwd" type="password" name="password" class="inputbox" size="18" value="<?php echo JText::_( 'Password' ); ?>" onblur="if(this.value=='') this.value='<?php echo JText::_( 'Password' ); ?>';" onfocus="if(this.value=='<?php echo JText::_( 'Password' ); ?>') this.value='';" />
	</span>
	
	<?php if(JPluginHelper::isEnabled('system', 'remember')) : ?>
		<!-- <input type="checkbox" name="remember" class="inputbox" value="yes" alt="Remember Me" /> -->
	<?php endif; 
		if ($usersConfig->get('allowUserRegistration')) { ?>
            <span class="register-button">
                <button value="register" name="register" type="button" title="<?php echo JText::_('REGISTER'); ?>" onclick="location.href='index.php?option=com_users&view=registration'"><?php echo JText::_( 'REGISTER' ); ?></button>
            </span><?php
        } ?>
		<span class="login-button">
			<button value="<?php echo JText::_( 'LOGIN' ); ?>" name="Submit" type="submit" title="<?php echo JText::_('LOGIN'); ?>"><?php echo JText::_( 'LOGIN'); ?></button>
		</span><?php 
		
		if ($params->get('lost_password') ) { ?>
			<span class="lostpassword">
				<a href="<?php echo JRoute::_( 'index.php?option=com_users&view=reset' ); ?>" title="<?php echo JText::_('FORGOT_PASSWORD'); ?>"></a>
			</span><?php 
		} 
		if ( $params->get('lost_username')) { ?>
            <span class="lostusername">
                <a href="<?php echo JRoute::_( 'index.php?option=com_users&view=remind' ); ?>" title="<?php echo JText::_('FORGOT_USERNAME'); ?>"></a>
            </span><?php 
		} 
		$usersConfig = JComponentHelper::getParams( 'com_users' );
		if ($usersConfig->get('allowUserRegistration') && $usersConfig->get('registration')) { ?>
            <span class="registration">
                <a href="<?php echo JRoute::_( 'index.php?option=com_users&view=registration' ); ?>" title="<?php echo JText::_('REGISTER'); ?>"></a>
            </span><?php 
		} ?>
	<?php echo $params->get('posttext'); ?>

	<input type="hidden" name="option" value="com_users" />
	<input type="hidden" name="task" value="user.login" />
	<input type="hidden" name="return" value="<?php echo $return; ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>
<?php 
	
	
 endif; ?>
