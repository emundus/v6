<?php
/**
 * @package         SCLogin
 * @copyright (c)   2009-2021 by SourceCoast - All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @version         Release v9.0.215
 * @build-date      2022/09/06
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
    ?>

    <div class="sclogin-joomla-login vertical <?php echo $joomlaSpan; ?>">
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
            <fieldset class="input-block-level userdata">
                <div class="control-group form-sclogin-username" id="form-sclogin-username-<?php echo $module->id; ?>">
                    <div class="controls input-block-level">
                        <div class="input-append input-block-level">
                            <input id="sclogin-username-<?php echo $module->id; ?>" name="username" tabindex="0" <?php echo $params->get('autoFocusUsername') ? 'autofocus' : '';?> class="sclogin-username input-block-level" title="username" type="text"
                                   placeholder="<?php echo Text::_('MOD_SCLOGIN_USERNAME'); ?>" aria-label="<?php echo Text::_('MOD_SCLOGIN_USERNAME'); ?>" required aria-required="true">
                            <?php echo $helper->getForgotUserButton(); ?>
                        </div>
                    </div>
                </div>
                <div class="control-group form-sclogin-password" id="form-sclogin-password-<?php echo $module->id; ?>">
                    <div class="controls input-block-level">
                        <div class="input-append input-block-level">
                            <input id="sclogin-passwd<?php echo $module->id; ?>" name="<?php echo $passwordName; ?>" tabindex="0" class="sclogin-passwd input-block-level" title="password" type="password"
                                   placeholder="<?php echo Text::_('MOD_SCLOGIN_PASSWORD') ?>" aria-label="<?php echo Text::_('MOD_SCLOGIN_PASSWORD') ?>" required aria-required="true">
                            <?php echo $helper->getShowPasswordButton('sclogin-passwd'. $module->id ); ?>
                            <?php echo $helper->getForgotPasswordButton(); ?>
                        </div>
                    </div>
                </div>
                <div class="control-group form-sclogin-submitcreate" id="form-sclogin-submitcreate-<?php echo $module->id; ?>">
                    <button type="submit" name="Submit" class="<?php echo $loginButtonClass;?><?php if (!$showRegisterLinkInLogin)
                    {
                        echo ' span12';
                    } ?>"><?php echo Text::_('MOD_SCLOGIN_LOGIN') ?></button>
                    <?php if ($showRegisterLinkInLogin) : ?>
                        <a class="<?php echo $registerButtonClass;?>" href="<?php echo $helper->registerLink; ?>"><?php echo Text::_('MOD_SCLOGIN_REGISTER_FOR_THIS_SITE'); ?></a>
                    <?php endif; ?>
                </div>
                <?php if (PluginHelper::isEnabled('system', 'remember')) :
                    if($helper->showRememberMe())
                    {?>
                        <div class="control-group form-sclogin-remember" id="form-sclogin-remember-<?php echo $module->id; ?>">
                            <label for="sclogin-remember-<?php echo $module->id; ?>">
                                <input id="sclogin-remember-<?php echo $module->id; ?>" type="checkbox" name="remember" class="inputbox sclogin-remember" <?php echo $helper->getRememberMeValue();?> title="Remember Me" />
                                <?php echo Text::_('JGLOBAL_REMEMBER_ME');?>
                            </label>
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
            </fieldset>
        </form>
    </div>
    <?php
}