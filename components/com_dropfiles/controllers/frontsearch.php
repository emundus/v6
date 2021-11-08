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

jimport('joomla.application.component.controller');
jimport('joomla.filesystem.file');

/**
 * Class DropfilesControllerFrontsearch
 */
class DropfilesControllerFrontsearch extends JControllerLegacy
{
    /**
     * Get moldel frontsearch
     *
     * @param string $name   Model name
     * @param string $prefix Model prefix
     * @param array  $config Model config
     *
     * @return mixed
     * @since  version
     */
    public function getModel(
        $name = 'frontsearch',
        $prefix = 'dropfilesModel',
        $config = array('ignore_request' => true)
    ) {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }

    /**
     * Display
     *
     * @param boolean $cachable  Catchable
     * @param array   $urlparams URL Param
     *
     * @return void
     * @since  version
     */
//    public function display($cachable = false, $urlparams = array())
//    {
//        parent::display($cachable, $urlparams);
//    }

    /**
     * Search files
     *
     * @return void
     * @since  version
     */
    public function search()
    {
        // Slashes cause errors, <> get stripped anyway later on. # causes problems.
        $badchars = array('#', '>', '<', '\\');
        $searchword = trim(str_replace($badchars, '', $this->input->getString('q', null, 'post')));

        // If searchword enclosed in double quotes, strip quotes and do exact match
        if (substr($searchword, 0, 1) === '"' && substr($searchword, -1) === '"') {
            $post['q'] = substr($searchword, 1, -1);
            $this->input->set('searchphrase', 'exact');
        } else {
            $post['q'] = $searchword;
        }

        $catid = $this->input->getUInt('catid', null, 'post');
        if (!empty($catid)) {
            $post['catid'] = $catid;
        }

        $ftags = $this->input->getString('ftags', null, 'post');
        if (!empty($ftags)) {
            $post['ftags'] = $ftags;
        }

        $cfrom = $this->input->getString('cfrom', null, 'post');
        if (!empty($cfrom)) {
            $date = new JDate($cfrom);
            $post['cfrom'] = $date->format('Y-m-d');
        }
        $cto = $this->input->getString('cto', null, 'post');
        if (!empty($cto)) {
            $date = new JDate($cto);
            $post['cto'] = $date->format('Y-m-d');
        }
        $ufrom = $this->input->getString('ufrom', null, 'post');
        if (!empty($ufrom)) {
            $date = new JDate($ufrom);
            $post['ufrom'] = $date->format('Y-m-d');
        }
        $uto = $this->input->getString('uto', null, 'post');
        if (!empty($uto)) {
            $date = new JDate($uto);
            $post['uto'] = $date->format('Y-m-d');
        }

        //$post['searchphrase'] = $this->input->getWord('searchphrase', 'all', 'post');
        $post['limit'] = $this->input->getUInt('limit', null, 'post');
        if ($post['limit'] === null) {
            unset($post['limit']);
        }
        $post['limitstart'] = $this->input->getUInt('limitstart', null, 'post');
        if ($post['limitstart'] === null) {
            unset($post['limitstart']);
        }

        // The Itemid from the request, we will use this if it's a search page or if there is no search page available
        $post['Itemid'] = $this->input->getInt('Itemid');
        // Set Itemid id for links from menu
        $app = JFactory::getApplication();
        $menu = $app->getMenu();
        $item = $menu->getItem($post['Itemid']);
        // The request Item is not a search page so we need to find one
        if ($item->component !== 'com_dropfiles' || $item->query['view'] !== 'frontsearch') {
            // Get item based on component, not link. link is not reliable.
            $item = $menu->getItems('component', 'com_dropfiles', true);

            // If we found a search page, use that.
            if (!empty($item) && $item->query['view'] === 'frontsearch') {
                $post['Itemid'] = $item->id;
            }
        }

        unset($post['task']);
        unset($post['submit']);

        $uri = JUri::getInstance();
        $uri->setQuery($post);
        $uri->setVar('option', 'com_dropfiles');
        $uri->setVar('view', 'frontsearch');

        $this->setRedirect(JRoute::_('index.php' . $uri->toString(array('query', 'fragment')), false));
    }

    /**
     * Filter
     *
     * @return void
     * @since  version
     */
    public function filter()
    {
        echo 'filter';
        die();
    }

    /**
     * View category dropfiles
     *
     * @return void
     * @since  version
     */
    public function viewcat()
    {
        jimport('joomla.plugin.plugin');
        JLoader::register('PlgContentdropfiles', JPATH_PLUGINS . '/content/dropfiles/dropfiles.php');
        $catid = JFactory::getApplication()->input->getInt('catid');
        $match = array('', $catid);
        echo $this->viewfilebycatid($match);
    }

    /**
     * View file by category id
     *
     * @param array $match Match categories
     *
     * @return string
     * @since  version
     */
    public function viewfilebycatid($match)
    {
        jimport('joomla.application.component.model');
        JLoader::register('DropfilesBase', JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesBase.php');
        $path_dropfilesgoogle = JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesGoogle.php';
        JLoader::register('DropfilesGoogle', $path_dropfilesgoogle);
        $path_dropfilesdropbox = JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesDropbox.php';
        JLoader::register('DropfilesDropbox', $path_dropfilesdropbox);
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

        $categories = $modelCategories->getItems();
        $params = $modelConfig->getParams($category->id);
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
            //$files = $google->listFiles($category->cloud_id,$ordering,$direction);
            $modelGoogle = JModelLegacy::getInstance('Frontgoogle', 'dropfilesModel');
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
            $modelDropbox = JModelLegacy::getInstance('Frontdropbox', 'dropfilesModel');
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
            $modelOnedrive = JModelLegacy::getInstance('Frontonedrive', 'dropfilesModel');
            $files = $modelOnedrive->getItems($category->cloud_id, $ordering, $direction);
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

        JPluginHelper::importPlugin('dropfilesthemes');
        $app = JFactory::getApplication();
        $result = $app->triggerEvent('onShowFrontCategory', array(array('files' => $files,
            'category' => $category,
            'categories' => $categories,
            'params' => is_object($params) ? $params->params : '',
            'theme' => $theme
        )
        ));

        if (!empty($result[0])) {
            if (DropfilesBase::isJoomla40()) {
                JHtml::_('behavior.core');
            } else {
                JHtml::_('behavior.framework', true);
            }
            $componentParams = JComponentHelper::getParams('com_dropfiles');
            $doc = JFactory::getDocument();
            if ((int) $componentParams->get('usegoogleviewer', 1) === 1) {
                $path_dropfilesbase = JPATH_ADMINISTRATOR . '/components/com_droppics/classes/dropfilesBase.php';
                JLoader::register('DropfilesBase', $path_dropfilesbase);

                JHtml::_('jquery.framework');

                $doc->addScript(JURI::base('true') . '/components/com_dropfiles/assets/js/jquery.colorbox-min.js');
                $doc->addScript(JURI::base('true') . '/components/com_dropfiles/assets/js/colorbox.init.js');
                $doc->addStyleSheet(JURI::base('true') . '/components/com_dropfiles/assets/css/colorbox.css');
            }
            $doc->addScriptDeclaration('dropfilesBaseUrl="' . JURI::base() . '";');
            $doc->addScriptDeclaration('dropfilesRootUrl="' . JURI::root(true) . '/";');

            return $result[0];
        }

        return '';
    }
}
