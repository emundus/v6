<?php
/**
 * @package         SCLogin
 * @copyright (c)   2009-2019 by SourceCoast - All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @version         Release v8.4.3
 * @build-date      2020/05/29
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

define('DISPLAY_BLOCK', ' class="show"');

class modSCLoginHelper
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

    function __construct($params)
    {
        $this->params = $params;
        $this->user = JFactory::getUser();
        $this->doc = JFactory::getDocument();

        if (class_exists('JFBCFactory'))
        {
            $this->isJFBConnectInstalled = true;
            $this->providers = JFBCFactory::getAllProviders();
        }

        $this->getPasswordAndProfileLinks();
    }

    public function setupTheme()
    {
        // Load our CSS and Javascript files
        if (!$this->isJFBConnectInstalled)
            $this->doc->addStyleSheet(JURI::base(true) . '/media/sourcecoast/css/sc_bootstrap.css');

        $this->doc->addStyleSheet(JURI::base(true) . '/media/sourcecoast/css/common.css');

        $paths = array();
        $paths[] = JPATH_ROOT . '/templates/' . JFactory::getApplication()->getTemplate() . '/html/mod_sclogin/themes/';
        $paths[] = JPATH_ROOT . '/media/sourcecoast/themes/sclogin/';
        $theme = $this->params->get('theme', 'default.css');
        $file = JPath::find($paths, $theme);
        $file = str_replace(JPATH_SITE, '', $file);
        $file = str_replace('\\', "/", $file); //Windows support for file separators
        $this->doc->addStyleSheet(JURI::base(true) . $file);

        // Add placeholder Javascript for old browsers that don't support the placeholder field
        if ($this->user->guest)
        {
            jimport('joomla.environment.browser');
            $browser = JBrowser::getInstance();
            $browserType = $browser->getBrowser();
            $browserVersion = $browser->getMajor();
            if (($browserType == 'msie') && ($browserVersion <= 9))
            {
                // Using addCustomTag to ensure this is the last section added to the head, which ensures that jfbcJQuery has been defined
                $this->doc->addCustomTag('<script src="' . JURI::base(true) . '/media/sourcecoast/js/jquery.placeholder.js" type="text/javascript"> </script>');
                $this->doc->addCustomTag("<script>jfbcJQuery(document).ready(function() { jfbcJQuery('input').placeholder(); });</script>");
            }
        }
    }

    public function setupTwoFactorAuthentication()
    {
        // Two factor authentication check
        $jVersion = new JVersion();
        if (version_compare($jVersion->getShortVersion(), '3.2.0', '>=') && ($this->user->guest))
        {
            /** $db = JFactory::getDbo();
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
            $plugins = JPluginHelper::getPlugin('twofactorauth');

            if (count($plugins))
            {
                //check section
                // site = 1, administrator = 2, both = 3
                foreach($plugins as $plugin)
                {
                    if($plugin->params != '')
                    {
                        $temp = new JRegistry($plugin->params);
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

    public function setupJavascript()
    {
        $needsBootstrap = $this->params->get('displayType') == 'modal' ||
            (!$this->user->guest && ($this->params->get('showUserMenu') && $this->params->get('userMenuStyle') == 0));
        if (!$this->isJFBConnectInstalled)
        {
            if ($this->params->get('loadJQuery'))
                $this->doc->addScript(JURI::base(true) . '/media/sourcecoast/js/jq-bootstrap-1.8.3.js');
            if  ($needsBootstrap || $this->tfaLoaded)
                $this->doc->addScriptDeclaration('if (typeof jfbcJQuery == "undefined") jfbcJQuery = jQuery;');
        }

        if ($this->tfaLoaded)
        {
            $this->doc->addScript(Juri::base(true) . '/media/sourcecoast/js/mod_sclogin.js');
            $this->doc->addScriptDeclaration('sclogin.token = "' . JSession::getFormToken() . '";' .
                //"jfbcJQuery(window).on('load',  function() {
                // Can't use jQuery here because we don't know if jfbcJQuery has been loaded or not.
                "window.onload = function() {
                    sclogin.init();
                };
                sclogin.base = '" . JURI::base() . "';\n"
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
            $jfbcAffiliateID = JFBCFactory::config()->getSetting('affiliate_id');
            $showJFBCPoweredBy = (($showPoweredBy == '2' && JFBCFactory::config()->getSetting('show_powered_by_link')) || ($showPoweredBy == '1'));

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
            return '<div class="powered-by">' . JText::_('MOD_SCLOGIN_POWERED_BY') . ' <a target="_blank" href="' . $link . '" title="' . $title . '">' . $poweredByLabel . '</a></div>';
        }
        return "";
    }

    function getType()
    {
        $user = JFactory::getUser();
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
            $user = JFactory::getUser();
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
            $this->forgotUsernameLink = JRoute::_('index.php?option=com_easysocial&view=account&layout=forgetUsername' . $Itemid);
            $this->forgotPasswordLink = JRoute::_('index.php?option=com_easysocial&view=account&layout=forgetpassword' . $Itemid);
            $this->profileLink = FRoute::profile();
        }
        else if ($registerType == "communitybuilder" && file_exists(JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php'))
        {
            $this->registerLink = JRoute::_("index.php?option=com_comprofiler&task=registers", false);
            $this->forgotLink = JRoute::_("index.php?option=com_comprofiler&task=lostPassword");
            $this->forgotUsernameLink = $this->forgotLink;
            $this->forgotPasswordLink = $this->forgotLink;
            $this->profileLink = JRoute::_("index.php?option=com_comprofiler", false);
        }
        else if ($registerType == "virtuemart" && file_exists(JPATH_ADMINISTRATOR . '/components/com_virtuemart/version.php'))
        {
            require_once(JPATH_ADMINISTRATOR . '/components/com_virtuemart/version.php');
            if (class_exists('vmVersion') && property_exists('vmVersion', 'RELEASE'))
            {
                if (version_compare('1.99', vmVersion::$RELEASE)) // -1 if ver1, 1 if 2.0+
                    $this->registerLink = JRoute::_("index.php?option=com_virtuemart&view=user", false);
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
        else if ($registerType == 'kunena' && JFolder::exists(JPATH_SITE . '/components/com_kunena'))
        {
            $this->profileLink = JRoute::_('index.php?option=com_kunena&view=user', false);
            $this->registerLink = JRoute::_('index.php?option=com_users&view=registration', false);
        }
        else if ($registerType == 'custom')
        {
            $this->profileLink = '';
            $this->registerLink = $this->getLoginRedirect('registrationlink', false);

            //Custom link - determine what component we're in
            $uri = JURI::getInstance($this->registerLink);
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
            $this->profileLink = JRoute::_('index.php?option=com_users&view=profile', false);
            $this->registerLink = JRoute::_('index.php?option=com_users&view=registration', false);
        }
// common for J!, JomSocial, and Virtuemart

        if (!$this->forgotUsernameLink)
            $this->forgotUsernameLink = JRoute::_('index.php?option=com_users&view=remind', false);
        if (!$this->forgotPasswordLink)
            $this->forgotPasswordLink = JRoute::_('index.php?option=com_users&view=reset', false);
    }

    function getLoginRedirect($loginType, $base64_encode = true)
    {
        if (JRequest::getString('return'))
            return JRequest::getString('return');

        $itemId = $this->params->get($loginType);
        $url = $this->getMenuIdUrl($itemId);

        // If no URL determined from the Itemid set, use the current page
        if (!$url)
        {
            $url = JURI::getInstance()->toString();
        }

        // Finally, if we're getting the logout URL, make sure we're not going back to a registered page
        if ($loginType == 'jlogout')
        {
            if ($itemId == "")
                $itemId = JFactory::getApplication()->input->getInt('Itemid', '');
            if ($itemId != "")
            {
                $db = JFactory::getDBO();
                $query = "SELECT * FROM #__menu WHERE id=" . $db->quote($itemId);
                $db->setQuery($query);
                $menuItem = $db->loadObject();
                if ($menuItem && $menuItem->access != "1")
                {
                    $default = JFactory::getApplication()->getMenu()->getDefault();
                    $url = 'index.php?Itemid=' . $default->id;
                }
            }
        }

        return $base64_encode ? base64_encode($url) : $url;
    }

    private function getMenuIdUrl($itemId)
    {
        $url = "";
        $menu = JFactory::getApplication()->getMenu();
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
                        $itemId = $item->params->get('aliasoptions');

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
            $params = new JRegistry();
            $params->set('width', $this->params->get("profileWidth"));
            $params->set('height', $this->params->get("profileHeight"));
            $params->set('secure', JURI::getInstance()->getScheme() == 'https');

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
        else if ($registerType == 'kunena' && JFolder::exists(JPATH_SITE . '/components/com_kunena'))
        {
            $db = JFactory::getDbo();
            $query = "SELECT avatar FROM #__kunena_users WHERE userid = " . $user->id;
            $db->setQuery($query);
            $avatarURL = $db->loadResult();
            if ($avatarURL)
                $avatarURL = JRoute::_('media/kunena/avatars/' . $avatarURL, false);
            $html = $this->getAvatarHtml($avatarURL, $profileLink, "_self");
        }
        else if ($registerType == 'k2' && JFile::exists(JPATH_SITE . '/components/com_k2/helpers/utilities.php'))
        {
            include_once(JPATH_SITE.'/components/com_k2/helpers/utilities.php');
            $html =  $this->getAvatarHtml( K2HelperUtilities::getAvatar($user->id) , '', '');
        }
        return $html;
    }

    function getSocialAvatar($registerType, $profileLink)
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
            $html = '<div id="scprofile-pic">' . $html . '</div>';

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
        $params['text'] = JText::_('MOD_SCLOGIN_CONNECT_USER');

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
        return $this->getForgotButton($this->params->get('showForgotUsername'), $this->forgotLink, $this->forgotUsernameLink, JText::_('MOD_SCLOGIN_FORGOT_LOGIN'), JText::_('MOD_SCLOGIN_FORGOT_USERNAME'), $this->params->get('register_type'));
    }

    function getForgotPasswordButton()
    {
        return $this->getForgotButton($this->params->get('showForgotPassword'), $this->forgotLink, $this->forgotPasswordLink, JText::_('MOD_SCLOGIN_FORGOT_LOGIN'), JText::_('MOD_SCLOGIN_FORGOT_PASSWORD'), $this->params->get('register_type'));
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

            if ($registerType == "communitybuilder" && file_exists(JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php'))
            {
                $forgotButton = '<a href="' . $forgotSharedLink . '" class="forgot btn width-auto hasTooltip" tabindex="-1" data-placement="right" title="' . $forgotSharedText . '"><i class="icon-question-sign' . $buttonImageColor . '"></i></a>';
            }
            else
            {
                $forgotButton = '<a href="' . $forgotButtonLink . '" class="forgot btn width-auto hasTooltip" tabindex="-1" data-placement="right" title="' . $forgotButtonText . '"><i class="icon-question-sign' . $buttonImageColor . '"></i></a>';
            }
        }
        return $forgotButton;
    }
    function getShowPasswordButton($passwordId)
    {
        $showButton = '';

        if($this->params->get('showShowPassword'))
        {
            $showButton =
                <<<EOT
<script>
function showpsw() {
  var x = document.getElementById("{$passwordId}");
  if (x.type === "password") {
    x.type = "text";
  } else {
    x.type = "password";
  }
}</script>
EOT;
            $showButton .= '<a onclick="showpsw()"><span class="icon-eye field-icon"></span></a>';
        }
        return $showButton;
    }


    function getForgotLinks()
    {
        $links = '';
        $linksToShow = array();

        if($this->params->get('showForgotUsername') == 'link')
            $linksToShow[] = '<li><a href="' . $this->forgotUsernameLink . '">' . JText::_('MOD_SCLOGIN_FORGOT_USERNAME') . '</a></li>';
        if($this->params->get('showForgotPassword') == 'link')
            $linksToShow[] = '<li><a href="' . $this->forgotPasswordLink . '">' . JText::_('MOD_SCLOGIN_FORGOT_PASSWORD') . '</a></li>';

        if(count($linksToShow) > 0)
            $links = '<ul>'.implode('',$linksToShow).'</ul>';

        return $links;
    }

    function getUserMenu($userMenu, $menuStyle, $menuTitle='1')
    {
        $app = JFactory::getApplication();
        $menu = $app->getMenu();
        $menu_items = $menu->getItems('menutype', $userMenu);

        if (!empty($menu_items))
        {
            $parentTitle = '';
            if($menuTitle == '2') // Get User's name
            {
                $user = JFactory::getUser();
                $parentTitle = $user->get('name');
            }
            elseif($menuTitle == '1') // Get Menu Name
            {
                $db = JFactory::getDbo();
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
                    if($menuItem->params->get('menu_show', '1'))
                        $menuNav .= $this->getUserMenuItem($menuItem);
                }
                $menuNav .= '</ul>';
                $menuNav .= '</li></ul>';
                $menuNav .= '</div>';
            }
            else //Show in Bootstrap dropdown list
            {
                if ($this->isJFBConnectInstalled)
                    $ddName = JFBCFactory::config()->getSetting('jquery_load') ? 'sc-dropdown' : 'dropdown';
                else
                    $ddName = $this->params->get('loadJQuery') ? 'sc-dropdown' : 'dropdown';

                $menuNav = '<div class="scuser-menu dropdown-view">';
                $menuNav .= '<div class="btn-group">';
                $menuNav .= '<a class="btn dropdown-toggle" data-toggle="' . $ddName . '" href="#">' . $parentTitle . '<span class="caret"></span></a>';
                $menuNav .= '<ul class="dropdown-menu">';
                foreach ($menu_items as $menuItem)
                {
                    if($menuItem->params->get('menu_show', '1'))
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
                $url = JRoute::_('index.php?option=com_users&task=user.logout&return=' . $this->getLoginRedirect('jlogout') . '&' . JSession::getFormToken() . '=1');
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
        $image = $item->params->get('menu_image');
        if($image)
            $image = '<img src="'. JURI::root().$image .'" height="30" width="30" />&nbsp;&nbsp;';

        return '<li><a href="' . $url . '"' . $target . '>' . $image . $item->title . '</a></li>';
    }

    public function getRememberMeValue()
    {
        $showRememberMeParam = $this->params->get('showRememberMe');
        if($showRememberMeParam == '0' || $showRememberMeParam == '2')
            return "";
        else
            return 'checked="checked" value="yes"';
    }

    public function showRememberMe()
    {
        $showRememberMeParam = $this->params->get('showRememberMe');
        if($showRememberMeParam == '2' || $showRememberMeParam == '3')
            return false;
        else
            return true;
    }

    /* DEPRECATED 6.1 */
    function getForgotUser($registerType, $showForgotUsername, $forgotLink, $forgotUsernameLink, $buttonImageColor)
    {
        return $this->getForgotUserButton();
    }

    function getForgotPassword($registerType, $showForgotPassword, $forgotLink, $forgotPasswordLink, $buttonImageColor)
    {
        return $this->getForgotPasswordButton();
    }
}