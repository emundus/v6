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
class PlgK2dropfiles extends JPlugin
{

    /**
     * Example before display content method
     *
     * Method is called by the view and the results are imploded and displayed in a placeholder
     *
     * @param object $item K2 item
     *
     * @return string
     *
     * @internal param string $context The context for the content passed to the plugin.
     * @internal param object $article The content object.  Note $article->text is also available
     * @internal param object $params The content params
     * @internal param int $limitstart The 'page' number
     *
     * @since version
     */
    public function onK2PrepareContent(&$item)
    {
//        $app = JFactory::getApplication();
//        if($app->isAdmin()){
//            return true;
//        }
        JLoader::register('DropfilesFilesHelper', JPATH_ADMINISTRATOR . '/components/com_dropfiles/helpers/files.php');
//        $cont = explode('.', $context);
//        if($cont[0]=='com_content'){
        //Replace category
        $replace_category = '@<img.*?data\-dropfilescategory="([0-9]+)".*?>@';
        $item->text = preg_replace_callback($replace_category, array($this, 'replace'), $item->text);
        //Replace single file
        $replace_single = '@<img.*?data\-dropfilesfile="([[:alnum:]_]+)".*?>@';
        $item->text = preg_replace_callback($replace_single, array($this, 'replaceSingle'), $item->text);
//        }
        return true;
    }

    /**
     * Replace
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
        $path_dropfilesgoogle = JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesGoogle.php';
        JLoader::register('DropfilesGoogle', $path_dropfilesgoogle);
        JModelLegacy::addIncludePath(JPATH_ROOT . '/components/com_dropfiles/models/', 'DropfilesModelFrontfiles');
        JModelLegacy::addIncludePath(JPATH_ROOT . '/components/com_dropfiles/models/', 'DropfilesModelFrontconfig');
        JModelLegacy::addIncludePath(JPATH_ROOT . '/components/com_dropfiles/models/', 'DropfilesModelCategories');
        DropfilesBase::loadLanguage();

        $modelFiles = JModelLegacy::getInstance('Frontfiles', 'dropfilesModel');
        $modelConfig = JModelLegacy::getInstance('Frontconfig', 'dropfilesModel');
        $modelCategories = JModelLegacy::getInstance('Frontcategories', 'dropfilesModel');
        $modelCategory = JModelLegacy::getInstance('Frontcategory', 'dropfilesModel');

        $modelFiles->getState('onsenfout'); //To autopopulate state
        $modelFiles->setState('filter.category_id', (int)$match[1]);
        $modelCategories->getState('onsenfout'); //To autopopulate state
        $modelCategories->setState('category.id', (int)$match[1]);


        $category = $modelCategory->getCategory((int)$match[1]);
        if (!$category) {
            return '';
        }

        $categories       = $modelCategories->getItems();
        $params           = $modelConfig->getParams($category->id);
        $dropfiles_params = JComponentHelper::getParams('com_dropfiles');


        if ($dropfiles_params->get('restrictfile', 0)) {
            $user = JFactory::getUser();
            $user_id = (int) $user->id;

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
            $files = $google->listFiles($category->cloud_id, $ordering, $direction);
            if ($files === false) {
                JFactory::getApplication()->enqueueMessage($google->getLastError(), 'error');
                return '';
            }
        } else {
            if (isset($params->params->ordering)) {
                $modelFiles->setState('list.ordering', $params->params->ordering);
            }
            if (isset($params->params->orderingdir)) {
                $modelFiles->setState('list.direction', $params->params->orderingdir);
            }
            $files = $modelFiles->getItems();
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
        $params_arr = array(
            array(
                'files' => $files,
                'category' => $category,
                'categories' => $categories,
                'params' => $params->params,
                'theme' => $themeExists ? $theme : 'default'
            )
        );
        $result = $app->triggerEvent('onShowFrontCategory', $params_arr);

        if (!empty($result[0])) {
            $componentParams = JComponentHelper::getParams('com_dropfiles');
            if ((int) $componentParams->get('usegoogleviewer', 1) === 1) {
                $doc = JFactory::getDocument();
                $path_dropfilesbase = JPATH_ADMINISTRATOR . '/components/com_droppics/classes/dropfilesBase.php';
                JLoader::register('DropfilesBase', $path_dropfilesbase);
                JHtml::_('jquery.framework');

                $doc->addScript(JURI::base('true') . '/components/com_dropfiles/assets/js/jquery.colorbox-min.js');
                $doc->addScript(JURI::base('true') . '/components/com_dropfiles/assets/js/colorbox.init.js');
                $doc->addStyleSheet(JURI::base('true') . '/components/com_dropfiles/assets/css/colorbox.css');
            }
            return $result[0];
        }
        return '';
    }

    /**
     * Replace a single image
     *
     * @param array $match Match
     *
     * @return string
     * @since  version
     */
    private function replaceSingle($match)
    {
        jimport('joomla.application.component.model');
        JLoader::register('DropfilesBase', JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesBase.php');
        $path_dropfilesgoogle = JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesGoogle.php';
        JLoader::register('DropfilesGoogle', $path_dropfilesgoogle);
        JModelLegacy::addIncludePath(JPATH_ROOT . '/components/com_dropfiles/models/', 'DropfilesModelFrontfile');
        JModelLegacy::addIncludePath(JPATH_ROOT . '/components/com_dropfiles/models/', 'DropfilesModelFrontconfig');
        DropfilesBase::loadLanguage();

        $modelFile = JModelLegacy::getInstance('Frontfile', 'dropfilesModel');
        $modelConfig = JModelLegacy::getInstance('Frontconfig', 'dropfilesModel');
        $modelCategory = JModelLegacy::getInstance('Frontcategory', 'dropfilesModel');

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

        if ($category->type === 'googledrive') {
            $google = new DropfilesGoogle();
            $file = $google->getFileInfos($match[1], $category->cloud_id);
        } else {
            $file = $modelFile->getFile((int)$match[1]);
        }
        $file = DropfilesFilesHelper::addInfosToFile(json_decode(json_encode($file), false), $category);

        //Access check already done in category model
        $catmod = JCategories::getInstance('Dropfiles');
        $jcategory = $catmod->get($category->id);
        if (!$jcategory) {
            return '';
        }

        $params = $modelConfig->getParams($jcategory->id);

        if ($this->context === 'com_finder.indexer') {
            $theme = 'indexer';
        } else {
            if (!empty($params)) {
                $theme = $params->theme;
            } else {
                $theme = 'default';
            }
        }

        JPluginHelper::importPlugin('dropfilesthemes');
        $params_arr = array(array('file' => $file,
            'category' => $category,
            'params' => $params->params,
            'theme' => $theme));
        $app = JFactory::getApplication();
        $result = $app->triggerEvent('onShowFrontFile', $params_arr);

        if (!empty($result[0])) {
            $componentParams = JComponentHelper::getParams('com_dropfiles');
            $doc = JFactory::getDocument();
            $doc->addStyleSheet(JURI::base('true') . '/components/com_dropfiles/assets/css/front_ver5.4.css');
            if ((int) $componentParams->get('usegoogleviewer', 1) === 1) {
                $path_dropfilesbase = JPATH_ADMINISTRATOR . '/components/com_droppics/classes/dropfilesBase.php';
                JLoader::register('DropfilesBase', $path_dropfilesbase);
                JHtml::_('jquery.framework');
                $doc->addStyleSheet(JURI::base('true') . '/components/com_dropfiles/assets/css/video-js.css');
                $doc->addScript(JURI::base('true') . '/components/com_dropfiles/assets/js/video.js');
                $doc->addScriptDeclaration('dropfilesBaseUrl="' . JURI::base() . '";');
                //$doc->addScript(JURI::base('true').'/components/com_dropfiles/assets/js/jquery.colorbox-min.js');
                $doc->addScript(JURI::base('true') . '/components/com_dropfiles/assets/js/colorbox.init.js');
                //$doc->addStyleSheet(JURI::base('true').'/components/com_dropfiles/assets/css/colorbox.css');
            }
            return $result[0];
        }
        return '';
    }
}
