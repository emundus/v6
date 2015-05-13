<?php
/**
 * @package         SCLogin
 * @copyright (c)   2009-2014 by SourceCoast - All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @version         Release v4.3.0
 * @build-date      2015/03/19
 */

defined('_JEXEC') or die('Restricted access');

if ($registerType == "communitybuilder" && file_exists(JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php'))
    $passwordName = 'passwd';
else
    $passwordName = 'password';

if ($params->get('showLoginForm'))
{
    ?>

    <div class="sclogin-joomla-login horizontal <?php echo $joomlaSpan; ?>">
        <form action="<?php echo JRoute::_('index.php', true, $params->get('usesecure')); ?>" method="post" id="sclogin-form<?php echo $module->id; ?>">
            <fieldset class="userdata span12">
                <div class="control-group pull-left" id="form-sclogin-username">
                    <div class="controls">
                        <div class="input-append">
                            <input name="username" tabindex="0" id="sclogin-username" alt="username" type="text" class="input-small"
                                   placeholder="<?php echo JText::_('MOD_SCLOGIN_USERNAME'); ?>">
                            <?php echo $helper->getForgotUserButton(); ?>
                        </div>
                    </div>
                </div>
                <div class="control-group pull-left" id="form-sclogin-password">
                    <div class="controls">
                        <div class="input-append">
                            <input name="<?php echo $passwordName; ?>" tabindex="0" id="sclogin-passwd" alt="password" type="password" class="input-small"
                                   placeholder="<?php echo JText::_('MOD_SCLOGIN_PASSWORD') ?>">
                            <?php echo $helper->getForgotPasswordButton(); ?>
                        </div>
                    </div>
                </div>
                <div class="control-group pull-left" id="form-sclogin-submitcreate">
                    <button type="submit" name="Submit" class="btn btn-primary"><?php echo JText::_('MOD_SCLOGIN_LOGIN') ?></button>
                    <?php if ($showRegisterLinkInLogin) : ?>
                        <a class="btn" href="<?php echo $helper->registerLink; ?>"><?php echo JText::_('MOD_SCLOGIN_REGISTER_FOR_THIS_SITE'); ?></a>
                    <?php endif; ?>
                </div>
                <?php if (JPluginHelper::isEnabled('system', 'remember')) :
                    if($helper->showRememberMe())
                    {?>
                        <div class="control-group" id="form-sclogin-remember">
                            <label for="sclogin-remember">
                                <input id="sclogin-remember" type="checkbox" name="remember" class="inputbox" <?php echo $helper->getRememberMeValue();?> alt="Remember Me" />
                                <?php echo JText::_('JGLOBAL_REMEMBER_ME');?>
                            </label>
                        </div>
                    <?php }
                    else
                    { ?>
                        <input id="sclogin-remember" type="hidden" name="remember" class="inputbox" <?php echo $helper->getRememberMeValue();?> alt="Remember Me" />
                    <?php }
                endif; ?>

                <?php
                if ($registerType == "communitybuilder" && file_exists(JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php')) // Use Community Builder's login
                {
                    include_once(JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php');
                    global $_CB_framework;
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
                echo JHTML::_('form.token');

                echo $helper->getForgotLinks();
                ?>
            </fieldset>
        </form>
    </div>
    <?php
    if ($orientation == 'bottom')
        echo '<div class="clearfix"></div>';
}