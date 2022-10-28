<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/**
 * Layout variables
 * -----------------
 * @var   string $controlGroupClass
 * @var   string $controlLabelClass
 * @var   string $controlsClass
 */

$bootstrapHelper     = EventbookingHelperBootstrap::getInstance();
$btnPrimary          = $bootstrapHelper->getClassMapping('btn btn-primary');
$formHorizontalClass = $bootstrapHelper->getClassMapping('form form-horizontal');
?>
<h3 class="eb-heading"><?php echo Text::_('EB_EXISTING_USER_LOGIN'); ?></h3>
<form method="post" action="<?php echo Route::_('index.php?option=com_users&task=user.login'); ?>" name="eb-login-form" id="eb-login-form" autocomplete="off" class="<?php echo $formHorizontalClass; ?>">
	<div class="<?php echo $controlGroupClass;  ?>">
		<div class="<?php echo $controlLabelClass; ?>">
			<?php echo  Text::_('EB_USERNAME') ?><span class="required">*</span>
		</div>
		<div class="<?php echo $controlsClass; ?>">
			<input type="text" name="username" id="username" class="input-large validate[required]<?php echo $bootstrapHelper->getFrameworkClass('uk-input',1); ?>" value=""/>
		</div>
	</div>
	<div class="<?php echo $controlGroupClass;  ?>">
		<div class="<?php echo $controlLabelClass; ?>">
			<?php echo  Text::_('EB_PASSWORD') ?><span class="required">*</span>
		</div>
		<div class="<?php echo $controlsClass; ?>">
			<input type="password" id="password" name="password" class="input-large validate[required]<?php echo $bootstrapHelper->getFrameworkClass('uk-input',1); ?>" value="" />
		</div>
	</div>
	<div class="<?php echo $controlGroupClass;  ?>">
		<div class="<?php echo $controlsClass; ?>">
			<input type="submit" value="<?php echo Text::_('EB_LOGIN'); ?>" class="button <?php echo $btnPrimary; ?>" />
		</div>
	</div>
	<?php

	// Show forgot username and password if configured
	if ($this->config->show_forgot_username_password)
	{
		Factory::getLanguage()->load('com_users');
		$navClass = $bootstrapHelper->getClassMapping('nav');
		$navTabsClass = $bootstrapHelper->getClassMapping('nav-tabs');
		$navStackedClass = $bootstrapHelper->getClassMapping('nav-stacked');
	?>
        <ul id="eb-forgot-username-password" class="<?php echo $navClass . ' ' . $navTabsClass; ?>">
            <li>
                <a href="<?php echo Route::_('index.php?option=com_users&view=reset'); ?>">
                    <?php echo Text::_('COM_USERS_LOGIN_RESET'); ?></a>
            </li>
            <li>
                <a href="<?php echo Route::_('index.php?option=com_users&view=remind'); ?>">
                    <?php echo Text::_('COM_USERS_LOGIN_REMIND'); ?></a>
            </li>
        </ul>
    <?php
	}

	if (PluginHelper::isEnabled('system', 'remember'))
	{
	?>
		<input type="hidden" name="remember" value="1" />
	<?php
	}
	?>
	<input type="hidden" name="return" id="return_url" value="<?php echo base64_encode(Uri::getInstance()->toString()); ?>" />
	<input type="hidden" name="login_from_mp_subscription_form" value="1" />
	<?php echo HTMLHelper::_( 'form.token' ); ?>
</form>
<?php
    if ($this->config->user_registration)
    {
    ?>
        <h3 class="eb-heading"><?php echo Text::_('EB_NEW_USER_REGISTER'); ?></h3>
    <?php
    }
?>
