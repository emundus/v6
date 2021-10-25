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


//-- No direct access
defined('_JEXEC') || die('=;)');


jimport('joomla.plugin.plugin');
jimport('joomla.application.categories');

/**
 * Content Plugin.
 */
class PlgContentdropfiles extends JPlugin
{
    /**
     * Google running progress
     *
     * @var integer
     */
    public static $Once = 1;

    /**
     * Dropbox running progress
     *
     * @var integer
     */
    public static $DropboxOnce = 1;

    /**
     * Onedrive running progress
     *
     * @var integer
     */
    public static $OnedriveOnce = 1;

    /**
     * Onedrive Business running progress
     *
     * @var integer
     */
    public static $OnedriveBusinessOnce = 1;

    /**
     * Before display content method
     * Method is called by the view and the results are imploded and displayed in a placeholder
     *
     * @param string $context The context for the content passed to the plugin.
     * @param object $article The content object.  Note $article->text is also available
     *
     * @return string
     * @since  version
     *
     * @internal param object $params The content params
     * @internal param int $limitstart The 'page' number
     */
    public function onContentPrepare($context, &$article)
    {
        JLoader::register('DropfilesFilesHelper', JPATH_ADMINISTRATOR . '/components/com_dropfiles/helpers/files.php');
//        $cont = explode('.', $context);
//        if($cont[0]=='com_content'){
        $this->context = $context;
        // Replace category
/*        $replace_category = '@<img[^>]*?data\-dropfilescategory="([0-9]+)".*?data\-columns="([0-9]+)".*?>|<img[^>]*?data\-dropfilescategory="([0-9]+)".*?>@';*/
        $replace_category = '@<img[^>]*?data\-dropfilescategory="([0-9]+)".*?>@';
        $article->text = preg_replace_callback($replace_category, array($this, 'replace'), $article->text);
        // Replace single file
        $replace_single = '@<img[^>]*?data\-dropfilesfile="(.*?)".*?>@';
        $article->text = preg_replace_callback($replace_single, array($this, 'replaceSingle'), $article->text);
//        }

        /*
         * Sync page code use cUrl
         */
        $componentParams = JComponentHelper::getParams('com_dropfiles');
        if (!is_null($componentParams->get('google_credentials')) && $componentParams->get('google_credentials') !== '') {
            $curSyncInterval = $this->curSyncInterval();
            $sync_time       = (int) $componentParams->get('sync_time');
            $sync_method     = $componentParams->get('sync_method');
            if ($curSyncInterval >= $sync_time && $sync_method === 'sync_page_curl') {
                $cUrl = curl_init();
                $googlesync_url = JUri::root() . 'index.php?option=com_dropfiles&task=googledrive.googlesync';
                curl_setopt($cUrl, CURLOPT_URL, $googlesync_url);
                curl_setopt($cUrl, CURLOPT_RETURNTRANSFER, 1);
                curl_exec($cUrl);
                curl_close($cUrl);

                $cUrl = curl_init();
                $googlesync_index_url = JUri::root() . 'index.php?option=com_dropfiles&task=frontgoogle.index';
                curl_setopt($cUrl, CURLOPT_URL, $googlesync_index_url);
                curl_setopt($cUrl, CURLOPT_RETURNTRANSFER, 1);
                curl_exec($cUrl);
                curl_close($cUrl);
            }
            if ($curSyncInterval >= $sync_time && $sync_method === 'sync_page_curl_ajax' && self::$Once === 1) {
                $doc                = JFactory::getDocument();
                $path_dropfilesbase = JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesBase.php';
                JLoader::register('DropfilesBase', $path_dropfilesbase);
                JHtml::_('jquery.framework');
                $script = "jQuery(document).ready(function(){
                            jQuery.ajax({
                                url:'" . JUri::root() . "index.php?option=com_dropfiles&task=googledrive.googlesync'
                            }).done(function( data ) {
                                jQuery.ajax({
                                    url:'" . JUri::root() . "index.php?option=com_dropfiles&task=frontgoogle.index'
                                });
                            });
                        });";
                $doc->addScriptDeclaration($script);

                self::$Once = 0;
            }
        }

        if (!is_null($componentParams->get('dropbox_token')) && $componentParams->get('dropbox_token') !== '') {
            $curSyncInterval = $this->curSyncIntervalDropbox();
            $sync_time       = (int) $componentParams->get('dropbox_sync_time');
            $sync_method     = $componentParams->get('dropbox_sync_method');
            if ($curSyncInterval >= $sync_time && $sync_method === 'dropbox_sync_page_curl') {
                $cUrl = curl_init();
                curl_setopt($cUrl, CURLOPT_URL, JUri::root() . 'index.php?option=com_dropfiles&task=dropbox.sync');
                curl_setopt($cUrl, CURLOPT_RETURNTRANSFER, 1);
                curl_exec($cUrl);
                curl_close($cUrl);

                $cUrl = curl_init();
                $dropbox_index_url = JUri::root() . 'index.php?option=com_dropfiles&task=frontdropbox.index';
                curl_setopt($cUrl, CURLOPT_URL, $dropbox_index_url);
                curl_setopt($cUrl, CURLOPT_RETURNTRANSFER, 1);
                curl_exec($cUrl);
                curl_close($cUrl);
            }
            if ($curSyncInterval >= $sync_time
                && $sync_method === 'dropbox_sync_page_curl_ajax'
                && self::$DropboxOnce === 1) {
                $doc = JFactory::getDocument();
                $path_dropfilesbase = JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesBase.php';
                JLoader::register('DropfilesBase', $path_dropfilesbase);
                JHtml::_('jquery.framework');
                $script = "jQuery(document).ready(function(){
                        jQuery.ajax({
                            url:'" . JUri::root() . "index.php?option=com_dropfiles&task=dropbox.sync'
                        }).done(function( data ) {
                             jQuery.ajax({
                                url:'" . JUri::root() . "index.php?option=com_dropfiles&task=frontdropbox.index'
                            });
                        });
                    });";
                $doc->addScriptDeclaration($script);

                self::$DropboxOnce = 0;
            }
        }

        if (!is_null($componentParams->get('onedriveCredentials')) && $componentParams->get('onedriveCredentials') !== '') {
            $curSyncInterval = $this->curSyncIntervalOnedrive();
            $sync_time       = (int) $componentParams->get('onedriveSyncTime');
            $sync_method     = $componentParams->get('onedriveSyncMethod');
            if ($curSyncInterval >= $sync_time && $sync_method === 'sync_page_curl') {
                $cUrl             = curl_init();
                $onedrivesync_url = JUri::root() . 'index.php?option=com_dropfiles&task=onedrive.onedrivesync';
                curl_setopt($cUrl, CURLOPT_URL, $onedrivesync_url);
                curl_setopt($cUrl, CURLOPT_RETURNTRANSFER, 1);
                curl_exec($cUrl);
                curl_close($cUrl);

                $cUrl               = curl_init();
                $onedrive_index_url = JUri::root() . 'index.php?option=com_dropfiles&task=frontonedrive.index';
                curl_setopt($cUrl, CURLOPT_URL, $onedrive_index_url);
                curl_setopt($cUrl, CURLOPT_RETURNTRANSFER, 1);
                curl_exec($cUrl);
                curl_close($cUrl);
            }
            if ($curSyncInterval >= $sync_time && $sync_method === 'sync_page_curl_ajax' && self::$OnedriveOnce === 1) {
                $doc                = JFactory::getDocument();
                $path_dropfilesbase = JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesBase.php';
                JLoader::register('DropfilesBase', $path_dropfilesbase);
                JHtml::_('jquery.framework');
                $script = "jQuery(document).ready(function(){
                        jQuery.ajax({
                            url:'" . JUri::root() . "index.php?option=com_dropfiles&task=onedrive.onedrivesync'
                        }).done(function( data ) {
                             jQuery.ajax({
                                url:'" . JUri::root() . "index.php?option=com_dropfiles&task=frontonedrive.index'
                            });
                        });
                    });";
                $doc->addScriptDeclaration($script);
                self::$OnedriveOnce = 0;
            }
        }

        if (!is_null($componentParams['onedriveBusinessConnected']) && (int) $componentParams['onedriveBusinessConnected'] === 1) {
            $curSyncInterval = $this->curSyncIntervalOnedriveBusiness();
            $sync_time       = (int) $componentParams->get('onedriveBusinessSyncTime');
            $sync_method     = $componentParams->get('onedriveBusinessSyncMethod');
            if ($curSyncInterval >= $sync_time && $sync_method === 'sync_page_curl') {
                $cUrl                       = curl_init();
                $onedrivebusinesssync_url   = JUri::root() . 'index.php?option=com_dropfiles&task=onedrivebusiness.oneDriveBusinessSync';
                curl_setopt($cUrl, CURLOPT_URL, $onedrivebusinesssync_url);
                curl_setopt($cUrl, CURLOPT_RETURNTRANSFER, 1);
                curl_exec($cUrl);
                curl_close($cUrl);

                $cUrl                        = curl_init();
                $onedrive_business_index_url = JUri::root() . 'index.php?option=com_dropfiles&task=frontonedrivebusiness.index';
                curl_setopt($cUrl, CURLOPT_URL, $onedrive_business_index_url);
                curl_setopt($cUrl, CURLOPT_RETURNTRANSFER, 1);
                curl_exec($cUrl);
                curl_close($cUrl);
            }
            if ($curSyncInterval >= $sync_time && $sync_method === 'sync_page_curl_ajax' && self::$OnedriveBusinessOnce === 1) {
                $doc                = JFactory::getDocument();
                $path_dropfilesbase = JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesBase.php';
                JLoader::register('DropfilesBase', $path_dropfilesbase);
                JHtml::_('jquery.framework');
                $script = "jQuery(document).ready(function(){
                        jQuery.ajax({
                            url:'" . JUri::root() . "index.php?option=com_dropfiles&task=onedrivebusiness.oneDriveBusinessSync'
                        }).done(function( data ) {
                             jQuery.ajax({
                                url:'" . JUri::root() . "index.php?option=com_dropfiles&task=frontonedrivebusiness.index'
                            });
                        });
                    });";
                $doc->addScriptDeclaration($script);
                self::$OnedriveOnce = 0;
            }
        }

        return true;
    }

    /**
     * On Dropfiles content prepare
     *
     * @param string $context The context for the content passed to the plugin.
     * @param object $article The content object.  Note $article->text is also available
     *
     * @return void
     * @since  version
     */
    public function onDropfilesContentPrepare($context, &$article)
    {
        $foo = null;
        $this->onContentPrepare($context, $article);
    }


    /**
     * Dropfiles display a file category
     *
     * @param array $match Match
     *
     * @return string
     * @throws \Exception Throw when application can not start
     * @since  version
     */
    private function replace($match)
    {
        jimport('joomla.application.component.model');
        JLoader::register('DropfilesBase', JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesBase.php');
        JLoader::register('DropfilesHelper', JPATH_ADMINISTRATOR . '/components/com_dropfiles/helpers/dropfiles.php');
        $path_dropfilesGoogle = JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesGoogle.php';
        JLoader::register('DropfilesGoogle', $path_dropfilesGoogle);
        JModelLegacy::addIncludePath(JPATH_ROOT . '/components/com_dropfiles/models/', 'dropfilesModel');

        DropfilesBase::loadLanguage();
        $modelFiles             = JModelLegacy::getInstance('Frontfiles', 'dropfilesModel');
        $modelConfig            = JModelLegacy::getInstance('Frontconfig', 'dropfilesModel');
        $modelCategories        = JModelLegacy::getInstance('Frontcategories', 'dropfilesModel');
        $modelCategory          = JModelLegacy::getInstance('Frontcategory', 'dropfilesModel');
        $modelGoogle            = JModelLegacy::getInstance('Frontgoogle', 'dropfilesModel');
        $modelDropbox           = JModelLegacy::getInstance('Frontdropbox', 'dropfilesModel');
        $modelOnedrive          = JModelLegacy::getInstance('Frontonedrive', 'dropfilesModel');
        $modelOnedriveBusiness  = JModelLegacy::getInstance('Frontonedrivebusiness', 'dropfilesModel');

        $categoryId = (int) $match[1];

        $modelFiles->getState('onsenfout'); //To autopopulate state
        $modelFiles->setState('filter.category_id', $categoryId);
        $modelCategories->getState('onsenfout'); //To autopopulate state
        $modelCategories->setState('category.id', $categoryId);


        $category = $modelCategory->getCategory($categoryId);

        if (!$category) {
            return '';
        }
        $user             = JFactory::getUser();
        $categories       = $modelCategories->getItems();
        $params           = $modelConfig->getParams($category->id);
//        $params = isset($category) ? $category : $modelConfig->getParams($category->id);
        $dropfiles_params = JComponentHelper::getParams('com_dropfiles');
        if ($dropfiles_params->get('categoryrestriction', 'accesslevel') === 'accesslevel') {
            if (!in_array($category->access, $user->getAuthorisedViewLevels())) {
                return '';
            }
            $modelFiles->setState('filter.access', true);
        } else {
            //check permission by user group
            $modelFiles->setState('filter.access', false);
            $usergroup = isset($params->params->usergroup) ? $params->params->usergroup : array();

            $result = array_intersect($user->getAuthorisedGroups(), $usergroup);
            if (!count($result)) {
                return '';
            }
        }

        if ($dropfiles_params->get('restrictfile', 0)) {
            $user = JFactory::getUser();
            $user_id = (int) $user->id;
            // Check single user canview this category
            $canViewCategory = isset($params->params->canview) ? (int) $params->params->canview : 0;

            if ($user_id) {
                if (!($canViewCategory === $user_id || $canViewCategory === 0)) {
                    return '';
                }
            } else {
                if ($canViewCategory !== 0) {
                    return '';
                }
            }
        }
        if ($category->type === 'googledrive') {
            $google = new DropfilesGoogle();
            if (isset($params->params->ordering)) {
                $ordering = $params->params->ordering;
            } else {
                $ordering = 'ordering';
            }
            if (isset($params->params->orderingdir)) {
                $direction = $params->params->orderingdir;
            } else {
                $direction = 'asc';
            }
            //$files = $google->listFiles($category->cloud_id,$ordering,$direction);
            $files = $modelGoogle->getItems($category->cloud_id, $ordering, $direction);
            if ($files === false) {
                JFactory::getApplication()->enqueueMessage($google->getLastError(), 'error');
                return '';
            }
        } elseif ($category->type === 'dropbox') {
            if (isset($params->params->ordering)) {
                $ordering = $params->params->ordering;
            } else {
                $ordering = 'ordering';
            }
            if (isset($params->params->orderingdir)) {
                $direction = $params->params->orderingdir;
            } else {
                $direction = 'asc';
            }
            $files = $modelDropbox->getItems($category->cloud_id, $ordering, $direction);
        } elseif ($category->type === 'onedrive') {
            if (isset($params->params->ordering)) {
                $ordering = $params->params->ordering;
            } else {
                $ordering = 'ordering';
            }
            if (isset($params->params->orderingdir)) {
                $direction = $params->params->orderingdir;
            } else {
                $direction = 'asc';
            }
            $files = $modelOnedrive->getItems($category->cloud_id, $ordering, $direction);
        } elseif ($category->type === 'onedrivebusiness') {
            if (isset($params->params->ordering)) {
                $ordering = $params->params->ordering;
            } else {
                $ordering = 'ordering';
            }
            if (isset($params->params->orderingdir)) {
                $direction = $params->params->orderingdir;
            } else {
                $direction = 'asc';
            }

            $files = $modelOnedriveBusiness->getItems($category->cloud_id, $ordering, $direction);
        } else {
            $subparams   = (array) $params->params;
            $lstAllFile  = null;
            $ordering    = (isset($params->params->ordering)) ? $params->params->ordering : '';
            $orderingdir = (isset($params->params->orderingdir)) ? $params->params->orderingdir : '';
            if (isset($params->params->ordering)) {
                $modelFiles->setState('list.ordering', $params->params->ordering);
            }
            if (isset($params->params->orderingdir)) {
                $modelFiles->setState('list.direction', $params->params->orderingdir);
            }
            if (!empty($subparams) && isset($subparams['refToFile'])) {
                if (isset($subparams['refToFile'])) {
                    $listCatRef = $subparams['refToFile'];
                    $lstAllFile = $this->getAllFileRef($modelFiles, $listCatRef, $ordering, $orderingdir);
                }
            }
            $files = $modelFiles->getItems();
            if (!empty($lstAllFile)) {
                $files = array_merge($lstAllFile, $files);
                if (isset($params->params->ordering) && isset($params->params->orderingdir)) {
                    $ordering = $params->params->ordering;
                    $direction = $params->params->orderingdir;
                    $files = DropfilesHelper::orderingMultiCategoryFiles($files, $ordering, $direction);
                }
            }
        }

        $files = DropfilesFilesHelper::addInfosToFile($files, $category);

        if ($this->context === 'com_finder.indexer') {
            $theme = 'indexer';
        } else {
            if (!empty($params)) {
                $theme = $params->theme;
            } else {
                $theme = 'default';
            }
        }
        $componentParams = JComponentHelper::getParams('com_dropfiles');

        if ((int) $componentParams->get('loadthemecategory', 1) === 0) {
            $params->params = $this->loadParams($theme, $params->params, $componentParams);
        }

        // Check theme exists or fallback to default theme
        $availableThemes = DropfilesBase::getDropfilesThemes();
        $themeExists = false;
        foreach ($availableThemes as $t) {
            if (strtolower($t['id']) === strtolower($theme)) {
                $themeExists = true;
                break;
            }
            $themeExists = false;
        }
        $currentTheme = $themeExists ? $theme : 'default';

        if ($currentTheme === 'default') {
            $columns = isset($params->params->columns)? (int)$params->params->columns : 2;

            // Check default columns value
            if ($columns === 0) {
                $columns = 2;
            }
        }
        $params_arr = array(
            array(
                'files'      => $files,
                'category'   => $category,
                'categories' => $categories,
                'params'     => is_object($params) ? $params->params : '',
                'theme'      => $currentTheme,
                'columns'    => isset($columns) ? $columns : 2,
            )
        );
        $app = JFactory::getApplication();
        $result = $app->triggerEvent('onShowFrontCategory', $params_arr);

        if (!empty($result[0])) {
            if (DropfilesBase::isJoomla40()) {
                JHtml::_('behavior.core');
            } else {
                JHtml::_('behavior.framework', true);
            }
            $doc = JFactory::getDocument();
            $doc->addStyleSheet(JURI::base('true') . '/components/com_dropfiles/assets/css/front_ver5.4.css');

            if ((int) $componentParams->get('usegoogleviewer', 1) === 1) {
                $path_dropfilesbase = JPATH_ADMINISTRATOR . '/components/com_droppics/classes/dropfilesBase.php';
                JLoader::register('DropfilesBase', $path_dropfilesbase);
                JHtml::_('jquery.framework');
                $doc->addStyleSheet(JURI::base('true') . '/components/com_dropfiles/assets/css/video-js.css');
                $doc->addScript(JURI::base('true') . '/components/com_dropfiles/assets/js/video.js');
                $doc->addScript(JURI::base('true') . '/components/com_dropfiles/assets/js/colorbox.init.js');
            }
            $doc->addScriptDeclaration('dropfilesBaseUrl="' . JURI::base() . '";');
            $doc->addScriptDeclaration('dropfilesRootUrl="' . JURI::root(true) . '/";');

            return $result[0];
        }

        return '';
    }

    /**
     * Load theme params
     *
     * @param string     $theme         Theme name
     * @param null|array $cat_params    Category params
     * @param null|array $global_params Global params
     *
     * @return stdClass
     * @since  version
     */
    public function loadParams($theme = 'default', $cat_params = null, $global_params = null)
    {
        $ob = new stdClass();
        if ($theme === '') {
            $theme = 'default';
        }
        foreach ((array) $cat_params as $key => $val) {
            if ($theme === 'default') {
                $ob->$key = $global_params->get($theme . '_' . $key, $val);
            } else {
                $ob->$key = $global_params->get($key, $val);
            }
        }

        return $ob;
    }

    /**
     * Replace a single image
     *
     * @param array $match Match array
     *
     * @return string
     * @since  version
     */
    private function replaceSingle($match)
    {
        jimport('joomla.application.component.model');
        jimport('joomla.template.template');
        JLoader::register('DropfilesBase', JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesBase.php');
        $path_dropfilesgoogle = JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesGoogle.php';
        JLoader::register('DropfilesGoogle', $path_dropfilesgoogle);
        JModelLegacy::addIncludePath(JPATH_ROOT . '/components/com_dropfiles/models/', 'DropfilesModelFrontfile');
        JModelLegacy::addIncludePath(JPATH_ROOT . '/components/com_dropfiles/models/', 'DropfilesModelFrontconfig');
        DropfilesBase::loadLanguage();
        $modelFile              = JModelLegacy::getInstance('Frontfile', 'dropfilesModel');
        $modelConfig            = JModelLegacy::getInstance('Frontconfig', 'dropfilesModel');
        $modelCategory          = JModelLegacy::getInstance('Frontcategory', 'dropfilesModel');
        $modelDropbox           = JModelLegacy::getInstance('Frontdropbox', 'dropfilesModel');
        $modelOnedrive          = JModelLegacy::getInstance('Frontonedrive', 'dropfilesModel');
        $modelOnedriveBusiness  = JModelLegacy::getInstance('Frontonedrivebusiness', 'dropfilesModel');
        preg_match('@.*data\-dropfilesfilecategory="([0-9]+)".*@', $match[0], $matchCat);
        if (!empty($matchCat)) {
            $category = $modelCategory->getCategory((int)$matchCat[1]);
            if (!$category) {
                return '';
            }
        } else {
            $file = $modelFile->getFile((int)$match[1]);
            if ($file === null) {
                return '';
            }
            $category = $modelCategory->getCategory($file->catid);
            if (!$category) {
                return '';
            }
        }
        // Check access
        $user             = JFactory::getUser();
        $params           = $modelConfig->getParams($category->id);
        $dropfiles_params = JComponentHelper::getParams('com_dropfiles');
        if ($dropfiles_params->get('categoryrestriction', 'accesslevel') === 'accesslevel') {
            if (!in_array($category->access, $user->getAuthorisedViewLevels())) {
                return '';
            }
        } else {
            //check permission by user group
            $usergroup = isset($params->params->usergroup) ? $params->params->usergroup : array();

            $result = array_intersect($user->getAuthorisedGroups(), $usergroup);
            if (!count($result)) {
                return '';
            }
        }

        if ($category->type === 'googledrive') {
            $modelGoogle = JModelLegacy::getInstance('Frontgoogle', 'dropfilesModel');
            $file        = $modelGoogle->getFile($match[1]);
            $file->id    = $file->file_id;
            $file->file  = $file->title . '.' . $file->ext;
        } elseif ($category->type === 'dropbox') {
            $file = $modelDropbox->getFile($match[1]);
        } elseif ($category->type === 'onedrive') {
            $file = $modelOnedrive->getFile($match[1]);
        } elseif ($category->type === 'onedrivebusiness') {
            $file = $modelOnedriveBusiness->getFile($match[1]);
        } else {
            $file = $modelFile->getFile((int)$match[1]);
        }
        $file             = DropfilesFilesHelper::addInfosToFile(json_decode(json_encode($file), false), $category);

        if (!DropfilesFilesHelper::isUserCanViewFile($file)) {
            return '';
        }

        if ($this->context === 'com_finder.indexer') {
            $theme = 'indexer';
        } else {
            $theme = 'default';
        }

        $params_arr = array(
            'file'     => $file,
            'category' => $category,
            'params'   => $params->params,
            'theme'    => $theme
        );
        $result     = DropfilesBase::onShowFrontFile($params_arr);

        if (!empty($result)) {
            $componentParams = JComponentHelper::getParams('com_dropfiles');
            $doc             = JFactory::getDocument();
            $doc->addStyleSheet(JURI::base('true') . '/components/com_dropfiles/assets/css/front_ver5.4.css');
            if ((int) $componentParams->get('usegoogleviewer', 1) === 1) {
                $path_dropfilesbase = JPATH_ADMINISTRATOR . '/components/com_droppics/classes/dropfilesBase.php';
                JLoader::register('DropfilesBase', $path_dropfilesbase);
                JHtml::_('jquery.framework');
                $doc->addStyleSheet(JURI::base('true') . '/components/com_dropfiles/assets/css/video-js.css');
                $doc->addScript(JURI::base('true') . '/components/com_dropfiles/assets/js/video.js');
                $doc->addScript(JURI::base('true') . '/components/com_dropfiles/assets/js/colorbox.init.js');
            }
            $bg_color       = $componentParams->get('singlebg', '#444444');
            $bg_hover_color = $componentParams->get('singlebghovercolor', '#444444');
            $hover_color    = $componentParams->get('singlehover', '#888888');
            $font_color     = $componentParams->get('singlefontcolor', '#ffffff');
            $singleStyle = '';

            if ($bg_color !== '') {
                $singleStyle .= '.dropfiles-single-file .dropfiles-file-link {
                    background-color: ' . $bg_color . '  !important;
                    font-family: "robotomedium", Georgia, serif;
                    font-size: 16px;
                    font-size: 1rem;
                }';
            }

            if ($bg_hover_color !== '') {
                $singleStyle .= '.dropfiles-single-file .dropfiles-file-link:hover {
                    background-color: ' . $bg_hover_color . '  !important;
                }';
            }

            if ($font_color !== '') {
                $singleStyle .= ' .dropfiles-single-file .dropfiles-file-link a,.dropfiles-single-file
                    .dropfiles-file-link a .droptitle {
                     color: ' . $font_color . '  !important;
                     text-decoration: none !important;
                }';
                $singleStyle .= ' .dropfiles-single-file .dropfiles-file-link a:hover {
                     background:  none !important;
                }';
            }

            if ($hover_color !== '') {
                $singleStyle .= ' .dropfiles-single-file .dropfiles-file-link a:hover,.dropfiles-single-file
                 .dropfiles-file-link a .droptitle:hover{
                    color: ' . $hover_color . '  !important;
                }';
            }

            $doc->addStyleDeclaration($singleStyle);
            $doc->addScriptDeclaration('dropfilesBaseUrl="' . JURI::base() . '";');

            return $result;
        }
        return '';
    }

    /**
     * Cur Sync Interval
     *
     * @return float|integer
     * @since  version
     */
    private function curSyncInterval()
    {
        //get last_log param
        $params = JComponentHelper::getParams('com_dropfiles');
        if ($params->get('last_log') !== null) {
            $last_log = $params->get('last_log');
            $time_old = (int)strtotime($last_log);
        } else {
            $time_old = 0;
        }
        $time_new = (int)strtotime(date('Y-m-d H:i:s'));
        $timeInterval = $time_new - $time_old;
        $curtime = $timeInterval / 60;
        return $curtime;
    }

    /**
     * Cur Sync interval Dropbox
     *
     * @return float|integer
     * @since  version
     */
    private function curSyncIntervalDropbox()
    {
        //get last_log param
        $params = JComponentHelper::getParams('com_dropfiles');
        if ($params->get('dropbox_last_log') !== null) {
            $last_log = $params->get('dropbox_last_log');
            $time_old = (int) strtotime($last_log);
        } else {
            $time_old = 0;
        }
        $time_new     = (int) strtotime(date('Y-m-d H:i:s'));
        $timeInterval = $time_new - $time_old;
        $curtime      = $timeInterval / 60;

        return $curtime;
    }

    /**
     * Cur Sync interval OneDrive
     *
     * @return float|integer
     * @since  version
     */
    private function curSyncIntervalOnedrive()
    {
        //get last_log param
        $params = JComponentHelper::getParams('com_dropfiles');
        if ($params->get('onedrive_last_log') !== null) {
            $last_log = $params->get('onedrive_last_log');
            $time_old = (int)strtotime($last_log);
        } else {
            $time_old = 0;
        }
        $time_new     = (int) strtotime(date('Y-m-d H:i:s'));
        $timeInterval = $time_new - $time_old;
        $curtime      = $timeInterval / 60;

        return $curtime;
    }

    /**
     * Cur Sync interval OneDrive Business
     *
     * @return float|integer
     * @since  version
     */
    private function curSyncIntervalOnedriveBusiness()
    {
        //get last_log param
        $params = JComponentHelper::getParams('com_dropfiles');
        if ($params->get('onedrive_business_last_log') !== null) {
            $last_log = $params->get('onedrive_business_last_log');
            $time_old = (int)strtotime($last_log);
        } else {
            $time_old = 0;
        }
        $time_new     = (int) strtotime(date('Y-m-d H:i:s'));
        $timeInterval = $time_new - $time_old;
        $curtime      = $timeInterval / 60;

        return $curtime;
    }

    /**
     * Get all file referent
     *
     * @param object $model       Files model
     * @param array  $listCatRef  List category
     * @param string $ordering    Ordering
     * @param string $orderingdir Ordering direction
     *
     * @return array
     */
    private function getAllFileRef($model, $listCatRef, $ordering, $orderingdir)
    {
        $lstAllFile = array();
        foreach ($listCatRef as $key => $value) {
            if (is_array($value) && !empty($value)) {
                $lstFile    = $model->getFilesRef($key, $value, $ordering, $orderingdir);
                $lstAllFile = array_merge($lstFile, $lstAllFile);
            }
        }
        return $lstAllFile;
    }
}
