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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;

?>
<div class="sclogin sourcecoast <?php echo $helper->bsClass;?>">

<?php
if ($params->get('enableProfilePic'))
    echo $helper->getSocialAvatar($registerType, $helper->profileLink, $module->id);

$name = $helper->getGreetingName();
if(!empty($name))
    echo '<div class="sclogin-greeting">' . Text::sprintf('MOD_SCLOGIN_WELCOME', $name) . '</div>';

if($params->get('showProfileLink'))
{
    echo '<div class="sclogin-profile-link"><a href="'.$helper->profileLink.'">'.Text::_('MOD_SCLOGIN_LOGOUT_SHOW_PROFILE_LINK').'</a></div>';
}

if ($params->get('showLogoutButton'))
{
    if($params->get('showLogoutButton') == 1)
        $logoutClass=$loginButtonClass;
    else
        $logoutClass='logout-link';
    ?>
    <div class="sclogout-button">
        <div class="sclogin-joomla-login">
            <form action="<?php echo Route::_('index.php', true, $params->get('usesecure'));?>" method="post" class="sclogin-form" id="sclogin-form<?php echo $module->id; ?>">
                <div class="logout-button scLogoutButton" id="scLogoutButton-<?php echo $module->id; ?>">
                    <input type="submit" name="Submit" class="<?php echo $logoutClass;?>" value="<?php echo Text::_('JLOGOUT');?>" />

                    <?php $option = Factory::getApplication()->input->get('option');?>
                    <?php if($option == 'com_easysocial'):?>
                    <input type="hidden" name="option" value="com_easysocial" />
                    <input type="hidden" name="controller" value="account" />
                    <input type="hidden" name="task" value="logout" />
                    <?php else:?>
                    <input type="hidden" name="option" value="com_users" />
                    <input type="hidden" name="task" value="user.logout" />
                    <?php endif;?>

                    <input type="hidden" name="return" value="<?php echo $jLogoutUrl;?>" />
                    <?php echo HTMLHelper::_('form.token')?>
                </div>
            </form>
        </div>
    </div>
<?php
}

if ($params->get('showUserMenu'))
{
    echo $helper->getUserMenu($params->get('showUserMenu'), $params->get('userMenuStyle'), $params->get('userMenuTitle'));
}

if ($params->get('showConnectButton'))
{ ?>
    <div class="sclogin-social-connect">
        <?php echo $helper->getReconnectButtons($params->get('socialButtonsOrientation'), $params->get('socialButtonsAlignment'));?>
    </div>
<?php
}

echo $helper->getPoweredByLink();
?>
    <div class="clearfix"></div>
</div>
