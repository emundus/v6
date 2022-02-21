<?php
/**
 * Dropfiles
 *
 * We developed this code with our hearts and passion.
 * We hope you found it useful, easy to understand and to customize.
 * Otherwise, please feel free to contact us at contact@joomunited.com *
 *
 * @package   Dropfiles
 * @copyright Copyright (C) 2013 JoomUnited (http://www.joomunited.com). All rights reserved.
 * @copyright Copyright (C) 2013 Damien Barrère (http://www.crac-design.com). All rights reserved.
 * @license   GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
 */

// no direct access
defined('_JEXEC') || die;

/**
 * Class DropfilesBase
 */
class DropfilesBase
{

    /**
     * Register helper class, style sheet, script
     *
     * @return void
     */
    public static function initComponent()
    {
        //Load language from non default position
        self::loadLanguage();
        // Register helper class
        JLoader::register('DropfilesHelper', JPATH_ADMINISTRATOR . '/components/com_dropfiles/helpers/dropfiles.php');
        JLoader::register('DropfilesFilesHelper', JPATH_ADMINISTRATOR . '/components/com_dropfiles/helpers/files.php');
        JLoader::register('DropfilesCloudHelper', JPATH_ADMINISTRATOR . '/components/com_dropfiles/helpers/dropfilescloud.php');
        JLoader::register('DropfilesOneDrive', JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesOneDrive.php');
        JLoader::register('DropfilesOneDriveBusiness', JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesOneDriveBusiness.php');
        // Register helper class
        JLoader::register('DropfilesComponentHelper', JPATH_ADMINISTRATOR . '/components/com_dropfiles/helpers/component.php');
        JLoader::register('DropfilesGoogle', JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesGoogle.php');

        //Load scripts and stylesheets
        $document = JFactory::getDocument();
        $uri_dropfiles_assets = JURI::root() . 'components/com_dropfiles/assets';
        JHtml::_('jquery.framework');
        if (self::isJoomla30()) {
            if (JFactory::getApplication()->isClient('site')) {
                $document->addScript($uri_dropfiles_assets . '/js/modal.min.js');
                $document->addStyleSheet($uri_dropfiles_assets . '/css/modal.min.css');
            }
            $document->addScript($uri_dropfiles_assets . '/js/jquery-ui-1.9.2.custom.min.js');
            $document->addStyleSheet($uri_dropfiles_assets . '/css/ui-lightness/jquery-ui-1.9.2.custom.min.css');
            $document->addStyleSheet($uri_dropfiles_assets . '/css/bootstrap.min.css');
        } else { // Joomla 4
            $document->addScript($uri_dropfiles_assets . '/js/jquery-ui-1.9.2.custom.min.js');
            $document->addStyleSheet($uri_dropfiles_assets . '/css/ui-lightness/jquery-ui-1.9.2.custom.min.css');
        }
        $document->addStyleSheet($uri_dropfiles_assets . '/css/icons.min.css');
        //For touch devices
        $document->addScript($uri_dropfiles_assets . '/js/jquery.ui.touch-punch.min.js');

        $app = JFactory::getApplication();
        $document->addScriptDeclaration('dropfilesRootUrl="' . JURI::root() . '";');
        if ($app->isClient('site')) {
            $document->addStyleSheet($uri_dropfiles_assets . '/css/frontstyle.css');
            if ($app->input->get('view') === 'manage') {
                $document->addStyleSheet(JURI::root() . 'media/jui/css/icomoon.css');
            }
        } else {
            $stylebody = 'body {background: #ffffff;}';
            $document->addStyleDeclaration($stylebody);
        }
        if (class_exists('DropfilesOneDriveBusiness')) {
            $onedriveBusinessObj = new DropfilesOneDriveBusiness();
            if ($onedriveBusinessObj->hasOneDriveButton()) {
                if (!$onedriveBusinessObj->checkConnectOnedrive()) {
                    $connectUrl = $onedriveBusinessObj->getAuthorisationUrl();
                    if (!isset($connectUrl) || !$connectUrl) {
                        $connectUrl = '';
                    }
                    $document->addScriptDeclaration('dropfilesOnedriveBusinessUrl="' . $connectUrl . '";');
                }
            }
        }

        $document->addStyleSheet($uri_dropfiles_assets . '/css/jquery.gritter.css');
        $document->addStyleSheet($uri_dropfiles_assets . '/css/upload.min.css');
        $document->addStyleSheet($uri_dropfiles_assets . '/ui/css/style.css?v=5.8');
        $document->addStyleSheet($uri_dropfiles_assets . '/ui/css/statistics.css');
        $document->addStyleSheet($uri_dropfiles_assets . '/css/jaofiletree.css');
        $document->addStyleSheet($uri_dropfiles_assets . '/css/jquery.restable.css');
        $document->addStyleSheet($uri_dropfiles_assets . '/css/jquery.tagit.css');
        $document->addStyleSheet($uri_dropfiles_assets . '/css/jquery.restable.css');
        $document->addStyleSheet($uri_dropfiles_assets . '/css/material-design-iconic-font.min.css');

        $document->addScript($uri_dropfiles_assets . '/js/jquery.gritter.min.js');
        $document->addScript($uri_dropfiles_assets . '/js/dropfiles.js');
        $document->addScript($uri_dropfiles_assets . '/ui/js/core.js');
        $document->addScript($uri_dropfiles_assets . '/js/jquery.filedrop.min.js');
        $document->addScript($uri_dropfiles_assets . '/js/jquery.textselect.min.js');
        $document->addScript($uri_dropfiles_assets . '/js/jquery.nestable.js');
        $document->addScript($uri_dropfiles_assets . '/js/bootbox.min.js');
        $document->addScript($uri_dropfiles_assets . '/js/jaofiletree.js');
        $document->addScript($uri_dropfiles_assets . '/js/jquery.restable.js');
        $document->addScript($uri_dropfiles_assets . '/js/jquery.tagit.js');
        $document->addScript($uri_dropfiles_assets . '/js/resumable.js');

        self::setDefine();
    }

    /**
     * Register helper class, style sheet front
     *
     * @return void
     */
    public static function initFrontComponent()
    {
        //Load language from non default position
        self::loadLanguage();
        $uri_dropfiles_assets = JURI::root() . 'components/com_dropfiles/assets';
        JHtml::_('jquery.framework');
        if (self::isJoomla40()) {
            JHtml::_('behavior.core');
        } else {
            JHtml::_('behavior.framework', true);
        }
        // Register helper class
        JLoader::register('DropfilesHelper', JPATH_ADMINISTRATOR . '/components/com_dropfiles/helpers/dropfiles.php');
        JLoader::register('DropfilesCloudHelper', JPATH_ADMINISTRATOR . '/components/com_dropfiles/helpers/dropfilesCloud.php');
        // Register helper class
        JLoader::register('DropfilesComponentHelper', JPATH_ADMINISTRATOR . '/components/com_dropfiles/helpers/component.php');
        $document = JFactory::getDocument();
        $document->addStyleSheet($uri_dropfiles_assets . '/css/front_ver5.4.css');
        $document->addStyleSheet($uri_dropfiles_assets . '/css/video-js.css');
        $document->addScript($uri_dropfiles_assets . '/js/video.js');
        $document->addScriptDeclaration('dropfilesBaseUrl="' . JURI::base() . '";');
        self::setDefine();
    }

    /**
     * Set define
     *
     * @return void
     */
    public static function setDefine()
    {
//        $path = "file_path";
//        $paramsmedia = JComponentHelper::getParams('com_media');
//        if(!defined('COM_MEDIA_BASE')){
//            define('COM_MEDIA_BASE',  JPATH_ROOT.'/'.$paramsmedia->get($path, 'images'));
//        }
//        if(!defined('COM_MEDIA_BASEURL')){
//            define('COM_MEDIA_BASEURL', JURI::root().$paramsmedia->get($path, 'images'));
//        }
    }

    /**
     * Search a param into the component config
     *
     * @param string      $path    Path
     * @param null|string $default Default path
     *
     * @return mixed
     */
    public static function getParam($path, $default = null)
    {
        $params = JComponentHelper::getParams('com_dropfiles');
        return $params->get($path, $default);
    }

    /**
     * Method to retrieve the path to the component full width image directory
     *
     * @param null|integer $id_category Category id
     *
     * @return string
     */
    public static function getFullPicturePath($id_category)
    {
        if ($id_category > 0) {
            return COM_MEDIA_BASE . '/com_dropfiles/' . (int)$id_category . '/full/';
        } else {
            return '';
        }
    }

    /**
     * Method to retrieve the path to the component image directory
     *
     * @param null|integer $id_category Category id
     *
     * @return string
     */
    public static function getFilesPath($id_category = null)
    {
        if ($id_category === null) {
            return JPATH_ROOT . '/media/com_dropfiles/';
        }
        return JPATH_ROOT . '/media/com_dropfiles/' . $id_category . '/';
    }

    /**
     * Method to retrieve the path to the component image directory
     *
     * @param string $id_category Type
     *
     * @return string
     */
    public static function getVersionPath($id_category)
    {
        $path = self::getFilesPath($id_category);
        return $path . 'versions' . DIRECTORY_SEPARATOR;
    }

    /**
     * Method to return the current joomla version
     *
     * @param string $format Format
     *
     * @return string version
     */
    public static function getJoomlaVersion($format = 'short')
    {
        $method = 'get' . ucfirst($format) . 'Version';

        // Get the joomla version
        $instance = new JVersion();
        $version = call_user_func(array($instance, $method));

        return $version;
    }

    /**
     * Method to check if current joomla version is 3.X
     *
     * @return boolean
     */
    public static function isJoomla40()
    {
        if (version_compare(self::getJoomlaVersion(), '4.0') >= 0) {
            return true;
        }
        return false;
    }

    /**
     * Method to check if current joomla version is 3.X
     *
     * @return boolean
     */
    public static function isJoomla30()
    {
        if (version_compare(self::getJoomlaVersion(), '3.0', '>=') &&
            version_compare('4', self::getJoomlaVersion(), '>')) {
            return true;
        }
        return false;
    }

    /**
     * Check if a component is installed and activated
     *
     * @param string $extension Extension
     * @param string $type      Type
     *
     * @return boolean
     */
    public static function isExtensionActivated($extension, $type = '')
    {
        $db = JFactory::getDbo();
        $query = 'SELECT extension_id FROM #__extensions WHERE element=' . $db->quote($extension);

        if ($type !== '') {
            $query .= ' AND type=' . $db->quote($type);
        }
        $query .= ' AND enabled=1';
        $db->setQuery($query);
        if ($db->execute()) {
            if ($db->getNumRows() > 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Method to set config parameters
     *
     * @param array $datas Data array
     *
     * @return boolean
     */
    public static function setParams($datas)
    {
        return DropfilesComponentHelper::setParams($datas);
    }

    /**
     * Load global file language
     *
     * @return void
     */
    public static function loadLanguage()
    {
        $lang = JFactory::getLanguage();
        $lang->load('com_dropfiles', JPATH_ADMINISTRATOR . '/components/com_dropfiles', null, true);
        $lang->load('com_dropfiles.override', JPATH_ADMINISTRATOR . '/components/com_dropfiles', null, true);
        $lang->load('com_dropfiles.sys', JPATH_ADMINISTRATOR . '/components/com_dropfiles', null, true);
        // load language define for JS
        JText::script('COM_DROPFILES_DOWNLOAD_ALL');
        JText::script('COM_DROPFILES_DOWNLOAD_SELECTED');
    }

    /**
     * Get value in array with value index
     *
     * @param object|array $var     Variables
     * @param string       $value   Value
     * @param string       $default Default value
     *
     * @return mixed|string
     */
    public static function loadValue($var, $value, $default = '')
    {
        if (is_object($var) && isset($var->$value)) {
            return $var->$value;
        } elseif (is_array($var) && isset($var[$value])) {
            return $var[$value];
        }
        return $default;
    }

    /**
     * Check if htaccess with limit directive installed
     *
     * @return boolean|null True if installed false if not installed null if cant check
     */
    public function isHtaccesOk()
    {
        $url = JURI::root() . 'media/com_dropfiles/index.html';

        if (function_exists('get_headers')) {
            $headers = get_headers($url, 1);
            if ($headers[0] === 'HTTP/1.1 403 Forbidden') {
                return true;
            } else {
                return false;
            }
        }

        return null;
    }

    /**
     * Check on Joomunited website the latest version number of the component
     *
     * @param null|string $extension Extension
     *
     * @return boolean False Or version number (string)
     */
    public static function getLastExtensionVersion($extension = null)
    {
        if ($extension === null) {
            $extension = JFactory::getApplication()->input->getString('option', '');
        }
        if ((int) ini_get('allow_url_fopen') === 1) {
            $content = file_get_contents('http://www.joomunited.com/UPDATE-INFO/updates.json');
        } else {
            return false;
        }
        $json = json_decode($content);
        return $json->extensions->$extension->version;
    }

    /**
     * Get Extension Version
     *
     * @param null|string $extension Extension
     * @param string      $type      Type
     *
     * @return boolean
     */
    public static function getExtensionVersion($extension = null, $type = '')
    {
        if ($extension === null) {
            $extension = JFactory::getApplication()->input->getString('option', '');
        }
        $db = JFactory::getDbo();
        $query = 'SELECT manifest_cache FROM #__extensions WHERE element=' . $db->quote($extension);

        if ($type !== '') {
            $query .= ' AND type=' . $db->quote($type);
        }
        $db->setQuery($query);
        if ($db->execute()) {
            $manifest = $db->loadResult();
            $json = json_decode($manifest);
            if (property_exists($json, 'version')) {
                return $json->version;
            }
        }
        return false;
    }

    /**
     * Get cookie show hide columns
     *
     * @return array
     */
    public static function getCookieDropfiles()
    {
        if (isset($_COOKIE['dropfiles_show_columns']) && is_string($_COOKIE['dropfiles_show_columns'])) {
            $listColumns = explode(',', $_COOKIE['dropfiles_show_columns']);
        } else {
            $listColumns = array();
        }
        return $listColumns;
    }

    /**
     * Get Auth view file and download file
     *
     * @return mixed
     */
    public static function getAuthViewFileAndDownload()
    {
        return JFactory::getUser()->authorise('com_dropfiles.viewfile_download', 'com_dropfiles');
    }

    /**
     * Show single file
     *
     * @param array $options Options data
     *
     * @return string
     */
    public static function onShowFrontFile($options)
    {
        if (isset($options['themes']) && $options['theme'] !== '') {
            return '';
        }

        $doc = JFactory::getDocument();
        JHtml::_('jquery.framework');
        $doc->addStyleSheet(JURI::base('true') . '/plugins/dropfilesthemes/default/style_ver5.4.css');
        $doc->addScript(JURI::base('true') . '/components/com_dropfiles/assets/js/colorbox.init.js');
        $options['componentParams'] = JComponentHelper::getParams('com_dropfiles');
        $content = '';
        if (!empty($options['file'])) {
            if ($options['category']) {
                $options['category']->alias = JFilterOutput::stringURLSafe($options['category']->title);
            }

            $style = '.file {margin : ' . self::loadValue($options['params'], 'margintop', 10) . 'px ';
            $style .= self::loadValue($options['params'], 'marginright', 10) . 'px ';
            $style .= self::loadValue($options['params'], 'marginbottom', 10) . 'px ';
            $style .= self::loadValue($options['params'], 'marginleft', 10) . 'px;}';
            $doc->addStyleDeclaration($style);

            $layout = new JLayoutFile('dropfiles.singlefile.tpl', null, array('debug' => false, 'client' => 0, 'component' => 'com_dropfiles'));
            $content = $layout->render($options);
        }
        return $content;
    }

    /**
     * Get list of available themes
     *
     * @return mixed
     */
    public static function getDropfilesThemes()
    {
        JPluginHelper::importPlugin('dropfilesthemes');
        $app = JFactory::getApplication();
        $themes = $app->triggerEvent('onThemeName', array());
        return $themes;
    }
}
