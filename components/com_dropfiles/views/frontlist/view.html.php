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

defined('_JEXEC') || die;

/**
 * Class DropfilesViewFrontlist
 */
class DropfilesViewFrontlist extends JViewLegacy
{
    /**
     * Display the view
     *
     * @param null|string $tpl Template
     *
     * @return JException
     */
    public function display($tpl = null)
    {
        JLoader::register('DropfilesFilesHelper', JPATH_ADMINISTRATOR . '/components/com_dropfiles/helpers/files.php');
        JLoader::register('DropfilesHelper', JPATH_ADMINISTRATOR . '/components/com_dropfiles/helpers/dropfiles.php');
        $path_dropfilesgoogle = JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesGoogle.php';
        JLoader::register('DropfilesGoogle', $path_dropfilesgoogle);

        $app = JFactory::getApplication();
        $catids = $app->input->get('catid', null, 'array');
        if (empty($catids)) {
            return JError::raiseError(404, JText::_('COM_DROPFILES_ERROR_CATEGORY_NOT_FOUND'));
        }

        // Get active menu
        $this->menuItemParams = null;
        $currentMenuItem = $app->getMenu()->getActive();
        if ($currentMenuItem) {
            // Get params for active menu
            $this->menuItemParams = $currentMenuItem->getParams();
        }

        $modelFiles = JModelLegacy::getInstance('Frontfiles', 'dropfilesModel');
        $modelConfig = JModelLegacy::getInstance('Frontconfig', 'dropfilesModel');
        $modelCategories = JModelLegacy::getInstance('Frontcategories', 'dropfilesModel');
        $modelCategory = JModelLegacy::getInstance('Frontcategory', 'dropfilesModel');

        $this->filesHtml = '';
        $user = JFactory::getUser();
        $dropfiles_params = JComponentHelper::getParams('com_dropfiles');
        // If select all is selected
        if ($catids[0] === '' && !isset($catids[1])) {
            $mCats = JModelLegacy::getInstance('Categories', 'DropfilesModel');
            $cats = $mCats->getAllCategories();
            $tmpC = array();
            foreach ($cats as $s) {
                if ((int) $s->level === 1) {
                    $tmpC[] = $s->id;
                }
            }
            $catids = $tmpC;
        }
        foreach ($catids as $cat) {
            if (empty($cat)) {
                continue;
            } elseif (!is_numeric($cat)) { //cloud id
                $cat = $modelCategory->getCategoryIDbyCloudId($cat);
            }

            $modelFiles->getState('onsenfout'); //To autopopulate state
            $modelFiles->setState('filter.category_id', $cat);
            $modelCategories->getState('onsenfout'); //To autopopulate state
            $modelCategories->setState('category.id', $cat);
            $category = $modelCategory->getCategory((int)$cat);
            if (!$category) {
                continue;
            }

            $categories = $modelCategories->getItems();
            $params = $modelConfig->getParams($category->id);
            if ($dropfiles_params->get('categoryrestriction', 'accesslevel') === 'accesslevel') {
                $modelFiles->setState('filter.access', true);
                if (!in_array((int)$category->access, $user->getAuthorisedViewLevels())) {
                    continue;
                }
            } else {
                // Check permission by user group
                $modelFiles->setState('filter.access', false);
                $usergroup = isset($params->params->usergroup) ? $params->params->usergroup : array();

                $result = array_intersect($user->getAuthorisedGroups(), $usergroup);
                if (!count($result)) {
                    continue;
                }
            }

            if ($dropfiles_params->get('restrictfile', 0)) {
                $user_id = (int) $user->id;

                $canViewCategory = isset($params->params->canview) ? (int) $params->params->canview : 0;
                if ($user_id) {
                    if (!($canViewCategory === $user_id || $canViewCategory === 0)) {
                        continue;
                    }
                } else {
                    if ($canViewCategory !== 0) {
                        continue;
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

                $modelOnedriveBusiness = JModelLegacy::getInstance('Frontonedrivebusiness', 'dropfilesModel');
                $files = $modelOnedriveBusiness->getItems($category->cloud_id, $ordering, $direction);
            } else {
                if (isset($params->params->ordering)) {
                    $modelFiles->setState('list.ordering', $params->params->ordering);
                }
                if (isset($params->params->orderingdir)) {
                    $modelFiles->setState('list.direction', $params->params->orderingdir);
                }
                //to make storeid different to avoid duplicate results
                $modelFiles->setState('list.limit', 1000 * $cat);

                $subparams   = (array) $params->params;
                $lstAllFile  = null;
                $ordering    = (isset($params->params->ordering)) ? $params->params->ordering : '';
                $orderingdir = (isset($params->params->orderingdir)) ? $params->params->orderingdir : '';
                if (!empty($subparams) && isset($subparams['refToFile'])) {
                    if (isset($subparams['refToFile'])) {
                        $listCatRef = $subparams['refToFile'];
                        $lstAllFile = $this->getAllFileRef($modelFiles, $listCatRef, $ordering, $orderingdir);
                    }
                }

                $files = $modelFiles->getItems();
                if (!empty($lstAllFile) && $lstAllFile !== null) {
                    $files = array_merge($lstAllFile, $files);
                    if (isset($params->params->ordering) && isset($params->params->orderingdir)) {
                        $ordering   = $params->params->ordering;
                        $direction  = $params->params->orderingdir;
                        $files      = DropfilesHelper::orderingMultiCategoryFiles($files, $ordering, $direction);
                    }
                }
            }
            $files = DropfilesFilesHelper::addInfosToFile($files, $category);

            if (!empty($params) && !empty($params->theme)) {
                $theme = $params->theme;
            } else {
                $theme = 'default';
            }

            $componentParams = JComponentHelper::getParams('com_dropfiles');

            if ((int) $componentParams->get('loadthemecategory', 1) === 0) {
                $params->params = $this->loadParams($theme, $params->params, $componentParams);
            }

            if ($theme === 'default') {
                $columns = (int) $params->params->columns;

                // Check default columns value
                if ($columns === 0) {
                    $columns = 2;
                }
            }
            JPluginHelper::importPlugin('dropfilesthemes');
            $app = JFactory::getApplication();
            $result = $app->triggerEvent('onShowFrontCategory', array(array('files' => $files,
                'category' => $category,
                'categories' => $categories,
                'params' => is_object($params) ? $params->params : '',
                'theme' => $theme,
                'columns'    => isset($columns) ? $columns : 2,
            )
            ));

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
                $this->filesHtml .= $result[0];
            }
        }

        if ($this->menuItemParams && $this->menuItemParams->get('menu-meta_keywords')) {
            $this->document->setMetadata('keywords', $this->menuItemParams->get('menu-meta_keywords'));
        }
        if ($this->menuItemParams && $this->menuItemParams->get('menu-meta_description')) {
            $this->document->setDescription($this->menuItemParams->get('menu-meta_description'));
        }

        parent::display($tpl);
    }

    /**
     * Load Params theme
     *
     * @param string $theme         Theme name
     * @param null   $cat_params    Category params
     * @param null   $global_params Global params
     *
     * @return stdClass
     */
    public function loadParams($theme = 'default', $cat_params = null, $global_params = null)
    {
        $ob = new stdClass();
        if ($theme === '') {
            $theme = 'default';
        }
        foreach ((array)$cat_params as $key => $val) {
            $ob->$key = $global_params->get($theme . '_' . $key, $val);
        }

        return $ob;
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
