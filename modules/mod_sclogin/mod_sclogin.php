<?php
/**
 * @package         SCLogin
 * @copyright (c)   2009-2021 by SourceCoast - All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @version         Release v9.0.215
 * @build-date      2022/09/06
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Helper\ModuleHelper;

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

require_once(dirname(__FILE__) . '/helper.php');
require_once(dirname(__FILE__) . '/sc_helper.php');
$helper = new ModScloginHelper($params);

$helper->setupTheme();
$helper->setupTwoFactorAuthentication();
$helper->setupJavascript();

$jLoginUrl = $helper->getLoginRedirect('jlogin');
$jLogoutUrl = $helper->getLoginRedirect('jlogout');
$registerType = $params->get('register_type');
$showRegisterLink = $params->get('showRegisterLink');
$showRegisterLinkInModal = $showRegisterLink == 2 || $showRegisterLink == 3;
$showRegisterLinkInLogin = $showRegisterLink == 1 || $showRegisterLink == 3;

// Setup our parameters
$layout = $params->get('socialButtonsLayout', 'vertical'); //horizontal or vertical
$orientation = $params->get('socialButtonsOrientation'); //bottom, side or top
$alignment = $params->get('socialButtonsAlignment');
$loginButtonType = $params->get('loginButtonType');

$loginButtonClass = $params->get('loginbutton_class');
$registerButtonClass = $params->get('registerbutton_class');

if(empty($loginButtonClass))
{
    if($helper->isJFBConnectInstalled)
        $loginButtonClass = JFBCFactory::config()->get('registration_loginbutton_class');
    else
        $loginButtonClass = "btn-primary";
}
if(empty($registerButtonClass))
{
    if($helper->isJFBConnectInstalled)
        $registerButtonClass = JFBCFactory::config()->get('registration_registerbutton_class');
    else
        $registerButtonClass = "btn-secondary";
}

$loginButtonClass = "btn " . $loginButtonClass;
$logoutButtonClass = $loginButtonClass;
$registerButtonClass = "btn validate " . $registerButtonClass;

if ($layout == 'horizontal')
{
    $joomlaSpan = $helper->pullClass.'left';
    $socialSpan = $helper->pullClass. $alignment;
}
else if ($orientation == 'side' && $helper->isJFBConnectInstalled)
{
    // probably need settings for these spans instead of auto-guessing them, since any images can be used nowadays
    $joomlaSpan = $helper->colClass.'8';
    $socialSpan = $helper->colClass.'4';
}
else //$orientation == 'bottom' || $orientation == 'top'
{
    $joomlaSpan = $helper->colClass.'12';
    $socialSpan = $helper->colClass.'12';
}

$addClearfix = false; //($layout == 'vertical' && $orientation == "side") ||
// ($layout == "horizontal" && $orientation == "side" && $params->get('displayType') == 'modal');

require(ModuleHelper::getLayoutPath('mod_sclogin', $helper->getType()));
?>
