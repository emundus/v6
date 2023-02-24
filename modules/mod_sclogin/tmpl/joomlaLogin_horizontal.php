<?php
/**
 * @package         SCLogin
 * @copyright (c)   2009-2019 by SourceCoast - All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @version         Release v9.0.215
 * @build-date      2021/07/09
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\HTML\HTMLHelper;

if ($registerType == "communitybuilder" && file_exists(JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php'))
    $passwordName = 'passwd';
else
    $passwordName = 'password';

if ($params->get('showLoginForm'))
{
    $showPassword = $helper->getShowPasswordButton('sclogin-passwd-'. $module->id );;
    $forgotUser = $helper->getForgotUserButton();
    $forgotPassword = $helper->getForgotPasswordButton();
    ?>

    <div class="sclogin-joomla-login horizontal <?php echo $joomlaSpan; ?>">
        <?php
        $action = Route::_('index.php', true, $params->get('usesecure'));
        $isCB = false;
        if ($registerType == "communitybuilder" && file_exists(JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php')) // Use Community Builder's login
        {
            include_once(JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php');
            require_once(JPATH_ADMINISTRATOR . '/components/com_comprofiler/library/cb/cb.database.php');
            global $_CB_framework;
            $isCB = true;
            $action = $_CB_framework->viewUrl( 'login', true, null, 'html', $params->get('usesecure') );
        }
        ?>
        <form action="<?php echo $action; ?>" method="post" id="sclogin-form<?php echo $module->id; ?>">
            <div class="mod-sclogin__userdata userdata">
                <div class="mod-sclogin__username form-group <?php echo $helper->pullClass;?>left">
                    <div class="input-group">
                        <input id="sclogin-username-<?php echo $module->id; ?>" type="text" name="username" class="form-control sclogin-username"
                               autocomplete="username" placeholder="<?php echo Text::_('MOD_SCLOGIN_USERNAME'); ?>"
                               tabindex="0" <?php echo $params->get('autoFocusUsername') ? 'autofocus' : '';?>
                        >
                        <label for="sclogin-username-<?php echo $module->id; ?>" class="visually-hidden"><?php echo Text::_('MOD_SCLOGIN_USERNAME'); ?></label>
                        <span class="input-group-text" title="<?php echo Text::_('MOD_SCLOGIN_USERNAME'); ?>">
                            <span class="<?php echo $helper->getIconClass('user');?>" aria-hidden="true"></span>
                        </span>
                        <!-- only add spans if they have buttons present -->
                        <?php if(!empty($forgotUser)) { ?>
                            <span class="input-group-text">
                            <?php echo $forgotUser; ?>
                        </span>
                        <?php } ?>

                    </div>
                </div>
                <div class="mod-sclogin__password form-group <?php echo $helper->pullClass;?>left">
                    <div class="input-group">
                        <input id="sclogin-passwd-<?php echo $module->id; ?>" type="password" name="password" autocomplete="current-password" class="form-control sclogin-passwd" placeholder="<?php echo Text::_('MOD_SCLOGIN_PASSWORD'); ?>">
                        <!-- only add spans if they have buttons present -->
                        <?php if(!empty($showPassword)) { ?>
                            <span class="input-group-text">
                            <?php echo $showPassword; ?>
                        </span>
                        <?php } if(!empty($forgotPassword)) { ?>
                            <span class="input-group-text">
                            <?php echo $forgotPassword; ?>
                        </span>
                        <?php } ?>
                    </div>
                </div>
                <div class="form-group <?php echo $helper->pullClass;?>left form-sclogin-submitcreate" id="form-sclogin-submitcreate-<?php echo $module->id; ?>">
                    <button type="submit" name="Submit" class="<?php echo $loginButtonClass;?>"><?php echo Text::_('MOD_SCLOGIN_LOGIN') ?></button>
                    <?php if ($showRegisterLinkInLogin) : ?>
                        <a class="<?php echo $registerButtonClass;?>" href="<?php echo $helper->registerLink; ?>"><?php echo Text::_('MOD_SCLOGIN_REGISTER_FOR_THIS_SITE'); ?></a>
                    <?php endif; ?>
                </div>
                <?php if (PluginHelper::isEnabled('system', 'remember')) :
                    if($helper->showRememberMe())
                    {?>
                        <div class="form-check <?php echo $helper->pullClass;?>left">
                            <div class="form-group form-sclogin-remember" id="form-sclogin-remember-<?php echo $module->id; ?>">
                                <label for="sclogin-remember-<?php echo $module->id; ?>">
                                    <input id="sclogin-remember-<?php echo $module->id; ?>" type="checkbox" name="remember" class="form-check-input sclogin-remember" <?php echo $helper->getRememberMeValue();?> title="Remember Me" />
                                    <?php echo Text::_('JGLOBAL_REMEMBER_ME');?>
                                </label>
                            </div>
                        </div>
                    <?php }
                    else
                    { ?>
                        <input id="sclogin-remember-<?php echo $module->id; ?>" type="hidden" name="remember" class="inputbox sclogin-remember" <?php echo $helper->getRememberMeValue();?> title="Remember Me" />
                    <?php }
                endif; ?>

                <?php
                if($isCB) // Use Community Builder's login
                {
                    echo '<input type="hidden" name="option" value="com_comprofiler" />' . "\n";
                    echo '<input type="hidden" name="task" value="login" />' . "\n";
                    echo '<input type="hidden" name="op2" value="login" />' . "\n";
                    echo '<input type="hidden" name="lang" value="' . $_CB_framework->getCfg('lang') . '" />' . "\n";
                    echo '<input type="hidden" name="force_session" value="1" />' . "\n"; // makes sure to create joomla 1.0.11+12 session/bugfix
                    echo '<input type="hidden" name="return" value="B:' . base64_encode(cbSef(base64_decode($jLoginUrl))) . '"/>';
                    echo cbGetSpoofInputTag('login');
                }
                else
                {
                    echo '<input type="hidden" name="option" value="com_users"/>';
                    echo '<input type="hidden" name="task" value="user.login"/>';
                    echo '<input type="hidden" name="return" value="' . $jLoginUrl . '"/>';
                }
                echo '<input type="hidden" name="mod_id" value="' . $module->id . '"/>';
                echo HTMLHelper::_('form.token');

                echo $helper->getForgotLinks();
                ?>
            </div>
        </form>
    </div>
    <?php
    if ($orientation == 'bottom')
        echo '<div class="clearfix"></div>';
}