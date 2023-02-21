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

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\Registry\Registry;
use Joomla\CMS\Version;
use Joomla\CMS\Environment\Browser;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Helper\ModuleHelper;

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

define('DISPLAY_BLOCK', ' class="show"');

class ModScloginHelper
{
    var $isJFBConnectInstalled = false;

    var $providers = array();
    var $params;

    var $forgotLink;
    var $forgotUsernameLink;
    var $forgotPasswordLink;
    var $registerLink;
    var $profileLink;
    var $customRegisterComponent;

    var $user;
    var $tfaLoaded = false;
    var $doc;

    var $colClass;
    var $pullClass;
    var $rowClass;
    var $bsVersion;
    var $bsClass;
    var $forgotClass;
    var $fwClass;

    function __construct($params)
    {
        $this->params = $params;
        $this->user = Factory::getUser();
        $this->doc = Factory::getDocument();

        if (class_exists('JFBCFactory'))
        {
            $this->isJFBConnectInstalled = true;
            $this->providers = JFBCFactory::getAllProviders();
        }

        $this->getPasswordAndProfileLinks();

        if($this->isJFBConnectInstalled && JFBCFactory::config()->get('jquery_load'))
            $jfbcBsVersion = JFBCFactory::config()->get('bootstrap_css');
        else
            $jfbcBsVersion = "0";

        $this->forgotClass = $params->get('forgotbutton_class');

        if(JVERSION >= 4.0 || $jfbcBsVersion == '2')
        {
            $this->bsVersion = "";
            $this->bsClass = "ns-bs5";
            $this->pullClass = 'float-';
            $this->colClass = 'col-md-';
            $this->rowClass = "row";
            $this->fwClass = "icon-fw";
        }
        else
        {
            $this->bsVersion = "bs2_";
            $this->bsClass = "ns-bs2";
            $this->pullClass = 'pull-';
            $this->colClass = 'span';
            $this->rowClass = "row-fluid";
            if(empty($this->forgotClass))
                $this->forgotClass = "btn";
            //$this->forgotClass = "btn btn-secondary";
            $this->fwClass = "field-icon";
        }
    }

    public function setupTheme()
    {
        // Load our CSS and Javascript files
        if (!$this->isJFBConnectInstalled) {
            if (JVERSION >= 4.0)
                $this->doc->addStyleSheet(Uri::base(true) . '/media/sourcecoast/css/sc_bootstrap5.css');
            else
                $this->doc->addStyleSheet(Uri::base(true) . '/media/sourcecoast/css/sc_bootstrap.css');
        }
        if ($this->params->get('loadFontAwesome') && $this->params->get('loadFontAwesome'))
            $this->doc->addStyleSheet(Uri::base(true) . '/media/sourcecoast/css/fontawesome/css/font-awesome.min.css');

        $this->doc->addStyleSheet(Uri::base(true) . '/media/sourcecoast/css/common.css');

        $paths = array();
        $paths[] = JPATH_ROOT . '/templates/' . Factory::getApplication()->getTemplate() . '/html/mod_sclogin/themes/';
        $paths[] = JPATH_ROOT . '/media/sourcecoast/themes/sclogin/';
        $theme = $this->params->get('theme', 'default.css');
        $file = Path::find($paths, $theme);
        $file = str_replace(JPATH_SITE, '', $file);
        $file = str_replace('\\', "/", $file); //Windows support for file separators
        $this->doc->addStyleSheet(Uri::base(true) . $file);

        // Add placeholder Javascript for old browsers that don't support the placeholder field
        if ($this->user->guest)
        {
            jimport('joomla.environment.browser');
            $browser = Browser::getInstance();
            $browserType = $browser->getBrowser();
            $browserVersion = $browser->getMajor();
            if (($browserType == 'msie') && ($browserVersion <= 9))
            {
                // Using addCustomTag to ensure this is the last section added to the head, which ensures that jfbcJQuery has been defined
                $this->doc->addCustomTag('<script src="' . Uri::base(true) . '/media/sourcecoast/js/jquery.placeholder.js" type="text/javascript"> </script>');
                $this->doc->addCustomTag("<script>jfbcJQuery(document).ready(function() { jfbcJQuery('input').placeholder(); });</script>");
            }
        }
    }

    public function setupTwoFactorAuthentication()
    {
        // Two factor authentication check
        $jVersion = new Version();
        if (version_compare($jVersion->getShortVersion(), '3.2.0', '>=') && ($this->user->guest))
        {
            /** $db = Factory::getDbo();
            // Check if TFA is enabled. If not, just return false
            $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from('#__extensions')
            ->where('enabled=' . $db->q(1))
            ->where('folder=' . $db->q('twofactorauth'));
            $db->setQuery($query);
            $tfaCount = $db->loadResult();
             **/

            $this->tfaLoaded = false;
            $plugins = PluginHelper::getPlugin('twofactorauth');

            if (count($plugins))
            {
                //check section
                // site = 1, administrator = 2, both = 3
                foreach($plugins as $plugin)
                {
                    if($plugin->params != '')
                    {
                        $temp = new Registry($plugin->params);
                        $tempO = $temp->toObject();

                        //set TFA to true if section params is either 1, 3 or empty
                        if(in_array($tempO->section, array(1, 3)) || !isset($tempO->section))
                        {
                            $this->tfaLoaded = true;
                        }
                    }
                    else
                        $this->tfaLoaded = true;
                }
            }

        }
    }

    /***
     * Called by com_ajax to check if otp is required and, if so, display the otp template
     * @return HTML
     * @throws Exception
     */
    public static function otpcheckAjax()
    {
        // Since this is called statically, we need to load the module parameters to be used in the otp.php template file
        $modId = Factory::getApplication()->input->get('mod_id');
        $mod = ModuleHelper::getModuleById($modId);
        $params = new Registry();
        $params->loadString($mod->params);
        $helper = new ModScloginHelper($params);

        $response = new stdClass();
        $response->needsOtp = 'false';
        $response->form = "";
        // Session check
        if (Session::checkToken('POST'))
        {
            $db = Factory::getDbo();
            // Check if TFA is enabled. If not, just return false
            $query = $db->getQuery(true)
                ->select('COUNT(*)')
                ->from('#__extensions')
                ->where('enabled=' . $db->q(1))
                ->where('folder=' . $db->q('twofactorauth'));
            $db->setQuery($query);
            $tfaCount = $db->loadResult();

            if ($tfaCount > 0)
            {
                $input = Factory::getApplication()->input;
                $username = $input->post->get('u', '');

                $query = $db->getQuery(true)
                    ->select('id, password, otpKey')
                    ->from('#__users')
                    ->where('username=' . $db->q($username));

                $db->setQuery($query);
                $result = $db->loadObject();

                if ($result && $result->otpKey != '')
                {
                    //jimport('sourcecoast.utilities');
                    //SCStringUtilities::loadLanguage('mod_sclogin');
                    Factory::getLanguage()->load('mod_sclogin');

                    $response->needsOtp = 'true';
                    ob_start();
                    require(ModuleHelper::getLayoutPath('mod_sclogin', 'otp'));
                    $response->form = ob_get_clean();
                }
            }
        }
        return $response;
    }

    public function setupJavascript()
    {
        if(!$this->isJFBConnectInstalled)
        {
            if ($this->params->get('loadJQuery'))
                $this->doc->addScript(Uri::base(true) . '/media/sourcecoast/js/jquery-3.5.1.js');
            else
            {
                HTMLHelper::_('jquery.framework');
                $this->doc->addScriptDeclaration('if (typeof jfbcJQuery == "undefined") jfbcJQuery = jQuery;');
            }
        }

        if ($this->tfaLoaded)
        {
            $this->doc->addScript(Uri::base(true) . '/media/sourcecoast/js/mod_sclogin.js');
            $this->doc->addScriptDeclaration('sclogin.token = "' . Session::getFormToken() . '";' .
                //"jfbcJQuery(window).on('load',  function() {
                // Can't use jQuery here because we don't know if jfbcJQuery has been loaded or not.
                "window.onload = function() {
                    sclogin.init();
                };
                sclogin.base = '" . Uri::base() . "';\n"
            );
        }
    }

    function getPoweredByLink()
    {
        $showPoweredBy = $this->params->get('showPoweredByLink');
        if ($showPoweredBy == 0)
            return;

        if ($this->isJFBConnectInstalled)
        {
            $jfbcAffiliateID = JFBCFactory::config()->get('affiliate_id');
            $showJFBCPoweredBy = (($showPoweredBy == '2' && JFBCFactory::config()->get('show_powered_by_link')) || ($showPoweredBy == '1'));

            if ($showJFBCPoweredBy)
            {
                jimport('sourcecoast.utilities');
                $title = 'Facebook for Joomla';
                $poweredByLabel = 'JFBConnect';
                $link = SCLibraryUtilities::getAffiliateLink($jfbcAffiliateID);
            }
        }

        if (isset($link))
        {
            return '<div class="powered-by">' . Text::_('MOD_SCLOGIN_POWERED_BY') . ' <a target="_blank" href="' . $link . '" title="' . $title . '">' . $poweredByLabel . '</a></div>';
        }
        return "";
    }

    function getBootstrapVersion()
    {
        return $this->bsVersion;
    }

    function getType()
    {
        $user = Factory::getUser();
        return (!$user->get('guest')) ? 'logout' : 'login';
    }

    function getPasswordAndProfileLinks()
    {
        $registerType = $this->params->get('register_type');
        $this->forgotLink = '';
        if ($registerType == "jomsocial" && file_exists(JPATH_BASE . '/components/com_community/libraries/core.php'))
        {
            $jspath = JPATH_BASE . '/components/com_community';
            include_once($jspath . '/libraries/core.php');
            $this->registerLink = CRoute::_('index.php?option=com_community&view=register');
            $user = Factory::getUser();
            $this->profileLink = CRoute::_('index.php?option=com_community&view=profile&userid='. $user->id);
        }
        else if ($registerType == 'easysocial' && file_exists(JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/foundry.php'))
        {
            include_once(JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/foundry.php');
            $this->registerLink = FRoute::registration();
            if(method_exists('FRoute', 'getDefaultItemId'))
                $Itemid = '&Itemid=' . FRoute::getDefaultItemId( 'account' );
            else
                $Itemid = '';
            $this->forgotUsernameLink = Route::_('index.php?option=com_easysocial&view=account&layout=forgetUsername' . $Itemid);
            $this->forgotPasswordLink = Route::_('index.php?option=com_easysocial&view=account&layout=forgetpassword' . $Itemid);
            $this->profileLink = FRoute::profile();
        }
        else if ($registerType == "communitybuilder" && file_exists(JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php'))
        {
            $this->registerLink = Route::_("index.php?option=com_comprofiler&task=registers", false);
            $this->forgotLink = Route::_("index.php?option=com_comprofiler&task=lostPassword");
            $this->forgotUsernameLink = $this->forgotLink;
            $this->forgotPasswordLink = $this->forgotLink;
            $this->profileLink = Route::_("index.php?option=com_comprofiler", false);
        }
        else if ($registerType == "virtuemart" && file_exists(JPATH_ADMINISTRATOR . '/components/com_virtuemart/version.php'))
        {
            require_once(JPATH_ADMINISTRATOR . '/components/com_virtuemart/version.php');
            if (class_exists('vmVersion') && property_exists('vmVersion', 'RELEASE'))
            {
                if (version_compare('1.99', vmVersion::$RELEASE)) // -1 if ver1, 1 if 2.0+
                    $this->registerLink = Route::_("index.php?option=com_virtuemart&view=user", false);
                else
                {
                    if (file_exists(JPATH_SITE . '/components/com_virtuemart/virtuemart_parser.php'))
                    {
                        require_once(JPATH_SITE . '/components/com_virtuemart/virtuemart_parser.php');
                        global $sess;
                        $this->registerLink = $sess->url(SECUREURL . 'index.php?option=com_virtuemart&amp;page=shop.registration');
                    }
                }
            }
            $this->profileLink = '';
        }
        else if ($registerType == 'kunena' && Folder::exists(JPATH_SITE . '/components/com_kunena'))
        {
            $this->profileLink = Route::_('index.php?option=com_kunena&view=user', false);
            $this->registerLink = Route::_('index.php?option=com_users&view=registration', false);
        }
        else if ($registerType == 'custom')
        {
            $this->profileLink = '';
            $this->registerLink = $this->getLoginRedirect('registrationlink', false);

            //Custom link - determine what component we're in
            $uri = Uri::getInstance($this->registerLink);
            $uriParts = $uri->getQuery(true);
            if(isset($uriParts['option']))
            {
                switch($uriParts['option'])
                {
                    case 'com_community':
                        $this->customRegisterComponent = 'jomsocial';
                        break;
                    case 'com_comprofiler':
                        $this->customRegisterComponent = 'communitybuilder';
                        break;
                    default:
                        $this->customRegisterComponent = str_replace('com_', '', $uriParts['option']);
                        break;
                }
            }
        }
        else
        {
            $this->profileLink = Route::_('index.php?option=com_users&view=profile', false);
            $this->registerLink = Route::_('index.php?option=com_users&view=registration', false);
        }
// common for J!, JomSocial, and Virtuemart

        if (!$this->forgotUsernameLink)
            $this->forgotUsernameLink = Route::_('index.php?option=com_users&view=remind', false);
        if (!$this->forgotPasswordLink)
            $this->forgotPasswordLink = Route::_('index.php?option=com_users&view=reset', false);
    }

    function getLoginRedirect($loginType, $base64_encode = true)
    {
        $input = Factory::getApplication()->input;
        if ($input->getString('return'))
            return $input->getString('return');

        $itemId = $this->params->get($loginType);
        $url = $this->getMenuIdUrl($itemId);

        // If no URL determined from the Itemid set, use the current page
        if (!$url)
        {
            $url = Uri::getInstance()->toString();
        }

        // Finally, if we're getting the logout URL, make sure we're not going back to a registered page
        if ($loginType == 'jlogout')
        {
            if ($itemId == "")
                $itemId = Factory::getApplication()->input->getInt('Itemid', '');
            if ($itemId != "")
            {
                $db = Factory::getDBO();
                $query = "SELECT * FROM #__menu WHERE id=" . $db->quote($itemId);
                $db->setQuery($query);
                $menuItem = $db->loadObject();
                if ($menuItem && $menuItem->access != "1")
                {
                    $default = Factory::getApplication()->getMenu()->getDefault();
                    $url = 'index.php?Itemid=' . $default->id;
                }
            }
        }

        return $base64_encode ? base64_encode($url) : $url;
    }

    private function getMenuIdUrl($itemId)
    {
        $url = "";
        $menu = Factory::getApplication()->getMenu();
        if ($itemId)
        {
            $item = $menu->getItem($itemId);

            if ($item)
            {
                if ($item->type == 'url')
                    $url = $item->link;
                else
                {
                    if ($item->type == 'alias')
                        $itemId = $item->getParams()->get('aliasoptions');

                    if ($item->link)
                    {
                        $url = 'index.php?Itemid=' . $itemId;
                    }
                }
            }
        }
        return $url;
    }

    function getAvatarHtml($avatarURL, $profileURL, $profileURLTarget)
    {
        $html = '';
        if ($avatarURL)
        {
            $picHeightParam = $this->params->get("profileHeight");
            $picWidthParam = $this->params->get("profileWidth");
            $picHeight = $picHeightParam != "" ? 'height="' . $picHeightParam . 'px"' : "";
            $picWidth = $picWidthParam != "" ? 'width="' . $picWidthParam . 'px"' : "";

            $html = '<img src="' . $avatarURL . '" ' . $picWidth . " " . $picHeight . ' />';

            $isLinked = ($this->params->get("linkProfile") == 1);
            if ($isLinked && $profileURL != '')
                $html = '<a target="' . $profileURLTarget . '" href="' . $profileURL . '">' . $html . '</a>';
        }
        return $html;
    }

    function getProviderAvatar($provider, $user)
    {
        $html = "";
        $providerId = JFBCFactory::usermap()->getProviderUserId($user->get('id'), $provider->systemName);

        if ($providerId)
        {
            $params = new Registry();
            $params->set('width', $this->params->get("profileWidth"));
            $params->set('height', $this->params->get("profileHeight"));
            $params->set('secure', Uri::getInstance()->getScheme() == 'https');

            $avatarURL = $provider->profile->getAvatarUrl($providerId, false, $params);
            $profileURL = $provider->profile->getProfileUrl($providerId);
            $html = $this->getAvatarHtml($avatarURL, $profileURL, "_blank");
        }
        return $html;
    }

    function getJoomlaAvatar($registerType, $profileLink, $user)
    {
        $html = '';

        if($registerType == 'custom' && !empty($this->customRegisterComponent))
            $registerType = $this->customRegisterComponent;

        if ($registerType == 'jomsocial' && file_exists(JPATH_BASE . '/components/com_community/libraries/core.php'))
        {
            $jsUser = CFactory::getUser($user->id);
            $avatarURL = $jsUser->getThumbAvatar();
            $html = $this->getAvatarHtml($avatarURL, $profileLink, "_self");
        }
        else if ($registerType == 'easysocial' && file_exists(JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/foundry.php'))
        {
            $avatarURL = Foundry::user($user->id)->getAvatar();
            $html = $this->getAvatarHtml($avatarURL, $profileLink, "_self");
        }
        else if ($registerType == "communitybuilder" && file_exists(JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php'))
        {
            include_once( JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php' );
            $cbUser = CBuser::getInstance( $user->id );
            $avatar = $cbUser->getField( 'avatar', null, 'csv', 'none', 'list' );
            $html = $this->getAvatarHtml($avatar, $profileLink, "_self");
        }
        else if ($registerType == 'kunena' && Folder::exists(JPATH_SITE . '/components/com_kunena'))
        {
            $db = Factory::getDbo();
            $query = "SELECT avatar FROM #__kunena_users WHERE userid = " . $user->id;
            $db->setQuery($query);
            $avatarURL = $db->loadResult();
            if ($avatarURL)
                $avatarURL = Route::_('media/kunena/avatars/' . $avatarURL, false);
            $html = $this->getAvatarHtml($avatarURL, $profileLink, "_self");
        }
        else if ($registerType == 'k2' && File::exists(JPATH_SITE . '/components/com_k2/helpers/utilities.php'))
        {
            include_once(JPATH_SITE.'/components/com_k2/helpers/utilities.php');
            $html =  $this->getAvatarHtml( K2HelperUtilities::getAvatar($user->id) , '', '');
        }
        return $html;
    }

    function getSocialAvatar($registerType, $profileLink, $moduleId)
    {
        $html = "";
        if ($this->params->get('enableProfilePic') == 'social' && $this->isJFBConnectInstalled)
        {
            $userId = $this->user->get('id');
            $html = JFBCFactory::cache()->get('sclogin.avatar.' . $userId);
            if ($html === false)
            {
                foreach ($this->providers as $provider)
                {
                    $html = $this->getProviderAvatar($provider, $this->user);
                    if ($html != "")
                    {
                        JFBCFactory::cache()->store($html, 'sclogin.avatar.' . $userId);
                        break;
                    }
                }
            }
        }
        if($html == '') //Joomla avatar is selected or no social network avatar is found, so fall back to Joomla avatar
        {
            $html = $this->getJoomlaAvatar($registerType, $profileLink, $this->user);
        }

        if ($html != "")
            $html = '<div class="scprofile-pic" id="scprofile-pic-'.$moduleId.'">' . $html . '</div>';

        return $html;
    }

    function getLoginButtons($orientation, $alignment)
    {
        $login = '';
        if( $this->params->get('showSociaLoginButton', 1))
        {
            $params['providers'] = $this->getLoginButtonOrdering();
            $params['loginbuttonstype'] = $this->params->get('loginbuttonstype', 'default');
            $params['loginbuttons'] = $this->params->get('loginbuttons');

            $params['addStyles'] = 'false';
            $params['alignment'] = $alignment;
            $params['orientation'] = $orientation;
            $login = JFBCFactory::getLoginButtons($params);
        }
        return $login;
    }

    function getReconnectButtons($orientation, $alignment)
    {
        if (!$this->isJFBConnectInstalled)
            return '';

        $params['providers'] = $this->getLoginButtonOrdering();
        $params['loginbuttonstype'] = $this->params->get('loginbuttonstype', 'default');
        $params['loginbuttons'] = $this->params->get('loginbuttons');

        $params['addStyles'] = 'false';
        $params['alignment'] = $alignment;
        $params['orientation'] = $orientation;
        $params['text'] = Text::_('MOD_SCLOGIN_CONNECT_USER');

        return JFBCFactory::getReconnectButtons($params);
    }

    private function getLoginButtonOrdering()
    {
        $providers = $this->params->get('loginbuttonsorder', '');
        if ($providers != '')
            return $providers;
        else
        {
            $providers = array();
            foreach ($this->providers as $p)
                $providers[] = $p->systemName;
            return $providers;
        }
    }

    function getForgotUserButton()
    {
        return $this->getForgotButton($this->params->get('showForgotUsername'), $this->forgotLink, $this->forgotUsernameLink, Text::_('MOD_SCLOGIN_FORGOT_LOGIN'), Text::_('MOD_SCLOGIN_FORGOT_USERNAME'), $this->params->get('register_type'));
    }

    function getForgotPasswordButton()
    {
        return $this->getForgotButton($this->params->get('showForgotPassword'), $this->forgotLink, $this->forgotPasswordLink, Text::_('MOD_SCLOGIN_FORGOT_LOGIN'), Text::_('MOD_SCLOGIN_FORGOT_PASSWORD'), $this->params->get('register_type'));
    }

    private function getForgotButton($showForgotButtonType, $forgotSharedLink, $forgotButtonLink, $forgotSharedText, $forgotButtonText, $registerType)
    {
        $forgotButton = '';

        if($showForgotButtonType == 'button_black' || $showForgotButtonType == 'button_white')
        {
            if($showForgotButtonType == 'button_white')
                $buttonImageColor = ' icon-white';
            else
                $buttonImageColor = '';

            $question = $this->getIconClass('question');

            if ($registerType == "communitybuilder" && file_exists(JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php'))
            {
                $forgotButton = '<a href="' . $forgotSharedLink . '" class="forgot '.$this->forgotClass.' hasTooltip" tabindex="-1" data-placement="right" title="' . $forgotSharedText . '" aria-label="'.$forgotSharedText.'"><i class="'. $question . $buttonImageColor . '"></i></a>';
            }
            else
            {
                //$forgotButton = '<a href="' . $forgotButtonLink . '" class="forgot btn width-auto hasTooltip" tabindex="-1" data-placement="right" title="' . $forgotButtonText . '" aria-label="'.$forgotSharedText.'"><i class="'. $question . $buttonImageColor . '"></i></a>';
                $forgotButton = '<a href="' . $forgotButtonLink . '" class="forgot '.$this->forgotClass.'  hasTooltip" tabindex="-1" data-placement="right" title="' . $forgotButtonText . '" aria-label="'.$forgotSharedText.'"><i class="'. $question . $buttonImageColor . '"></i></a>';
            }
        }
        return $forgotButton;
    }

    function getIconClass($icon)
    {
        $useFontAwesome = $this->params->get('useFontAwesome');

        switch ($icon)
        {
            case 'user':
                $iconClass = $useFontAwesome ? $this->params->get('showUser_fa_class') : 'icon-user';
                break;
            case 'eye':
                $iconClass = $useFontAwesome ? $this->params->get('showPassword_fa_class') : 'icon-eye';
                break;
            case 'eye-slash':
                $iconClass = $useFontAwesome ? $this->params->get('hidePassword_fa_class') : 'icon-eye-close';
                break;
            case 'question':
                $iconClass = $useFontAwesome ? $this->params->get('showForgotButton_fa_class') : 'icon-question-sign';
                break;
            default:
                $iconClass = '';
                break;
        }
        return $this->fwClass . " " . $iconClass;
    }

    function getShowPasswordButton($passwordId)
    {
        $showButton = '';

        if ($this->params->get('showShowPassword'))
        {
            $eye = $this->getIconClass('eye');
            if (JVERSION < 4.0)
            {
                $eyeClose = $this->getIconClass('eye-slash');

                $fnName = 'showpsw' . str_replace('-', '', $passwordId);
                $showButton =
                    <<<EOT
<script>
function {$fnName}() {
  var x = document.getElementById("{$passwordId}");
  var eye = document.getElementById("sc-eye-{$passwordId}");
  if (x.type === "password") {
    x.type = "text";
    eye.className = "{$eyeClose}";
  } else {
    x.type = "password";
    eye.className = "{$eye}";
  }
}</script>
EOT;
                $showButton .= '<a class="showpasswd ' . $this->forgotClass . '" onclick="' . $fnName . '()"><span class="' . $eye . '" id="sc-eye-' . $passwordId . '"></span></a>';
            }
            else
            {
                $showButton = '<label for="' . $passwordId . '" class="visually-hidden">' . Text::_('MOD_SCLOGIN_PASSWORD') . '</label>'
                    . '<a type="button" class="showpasswd ' . $this->forgotClass . ' input-password-toggle">'
                    . '<span class="' . $eye . '" aria-hidden="true"></span>'
                    . '<span class="visually-hidden">' . Text::_('JSHOWPASSWORD') . '</span>'
                    . '</a>';
            }
        }
        return $showButton;
    }

    function getForgotLinks()
    {
        $links = '';
        $linksToShow = array();

        if($this->params->get('showForgotUsername') == 'link')
            $linksToShow[] = '<li><a href="' . $this->forgotUsernameLink . '">' . Text::_('MOD_SCLOGIN_FORGOT_USERNAME') . '</a></li>';
        if($this->params->get('showForgotPassword') == 'link')
            $linksToShow[] = '<li><a href="' . $this->forgotPasswordLink . '">' . Text::_('MOD_SCLOGIN_FORGOT_PASSWORD') . '</a></li>';

        if(count($linksToShow) > 0)
            $links = '<ul>'.implode('',$linksToShow).'</ul>';

        return $links;
    }

    function getUserMenu($userMenu, $menuStyle, $menuTitle='1')
    {
        $app = Factory::getApplication();
        $menu = $app->getMenu();
        $menu_items = $menu->getItems('menutype', $userMenu);

        if (!empty($menu_items))
        {
            $parentTitle = '';
            if($menuTitle == '2') // Get User's name
            {
                $user = Factory::getUser();
                $parentTitle = $user->get('name');
            }
            elseif($menuTitle == '1') // Get Menu Name
            {
                $db = Factory::getDbo();
                $query = 'SELECT title FROM #__menu_types WHERE menutype=' . $db->quote($userMenu);
                $db->setQuery($query);
                $parentTitle = $db->loadResult();
            }

            if ($menuStyle) //Show in List view
            {
                $menuNav = '<div class="scuser-menu list-view">';
                //$menuNav .= '<ul class="menu nav"><li class="dropdown"><span>'.$parentTitle.'</span>';
                $menuNav .= '<ul class="menu nav"><li><span>' . $parentTitle . '</span>';
                //$menuNav .= '<ul class="dropdown-menu">';
                $menuNav .= '<ul class="flat-list">';
                foreach ($menu_items as $menuItem)
                {
                    if($menuItem->getParams()->get('menu_show', '1'))
                        $menuNav .= $this->getUserMenuItem($menuItem);
                }
                $menuNav .= '</ul>';
                $menuNav .= '</li></ul>';
                $menuNav .= '</div>';
            }
            else //Show in Bootstrap dropdown list
            {
                if (JVERSION >= 4.0)
                    HTMLHelper::_('bootstrap.dropdown', 'dropdown');

                $menuNav = '<div class="scuser-menu dropdown-view">';
                $menuNav .= '<div class="btn-group">';
                $menuNav .= '<a class="btn dropdown-toggle" data-bs-toggle="dropdown" data-toggle="dropdown" href="#" role="button">' . $parentTitle . '</a>';
                $menuNav .= '<ul class="dropdown-menu">';
                foreach ($menu_items as $menuItem)
                {
                    if($menuItem->getParams()->get('menu_show', '1'))
                        $menuNav .= $this->getUserMenuItem($menuItem);
                }
                $menuNav .= '</ul>';
                $menuNav .= '</div>';
                $menuNav .= '</div>';
            }

        }
        else
            $menuNav = '';
        return $menuNav;
    }

    private function getUserMenuItem($item)
    {
        $url = $this->getMenuIdUrl($item->id);

        if ($item->type == 'url')
        {
            if ($item->link == 'sclogout')
                $url = Route::_('index.php?option=com_users&task=user.logout&return=' . $this->getLoginRedirect('jlogout') . '&' . Session::getFormToken() . '=1');
            if ($item->link == 'scconnect')
            {
                $params['image'] = 'icon.png';
                $html = '<li class="connect">' . $item->title;
                $html .= JFBCFactory::getReconnectButtons($params);
                $html .= '</li>';
                return $html;
            }
        }
        $target = $item->browserNav == 1 ? ' target="_blank" ' : '';
        $image = $item->getParams()->get('menu_image');
        if($image)
            $image = '<img src="'. Uri::root().$image .'" height="30" width="30" />&nbsp;&nbsp;';

        return '<li><a href="' . $url . '"' . $target . '>' . $image . $item->title . '</a></li>';
    }

    public function getRememberMeValue()
    {
        $showRememberMeParam = $this->params->get('showRememberMe');
        if($showRememberMeParam == '0' || $showRememberMeParam == '2')
            return "";
        else
            return 'checked value="yes"';
    }

    public function showRememberMe()
    {
        $showRememberMeParam = $this->params->get('showRememberMe');
        if($showRememberMeParam == '2' || $showRememberMeParam == '3')
            return false;
        else
            return true;
    }

    public function getGreetingName()
    {
        $name = '';
        $paramGreetingName = $this->params->get('greetingName');
        if ($paramGreetingName != 2)
        {
            $user = Factory::getUser();
            if ($paramGreetingName == 0)
                $name = $user->get('username');
            else if($paramGreetingName == 1)
                $name = $user->get('name');
            else if($paramGreetingName == 3)
                $name = strtok($user->name, ' ');
        }

        return $name;
    }

}