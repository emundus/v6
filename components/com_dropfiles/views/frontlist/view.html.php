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
        $path_dropfilesgoogle = JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesGoogle.php';
        JLoader::register('DropfilesGoogle', $path_dropfilesgoogle);

        $app = JFactory::getApplication();
        $catids = $app->input->get('catid', null, 'array');
        if (empty($catids)) {
            return JError::raiseError(404, JText::_('COM_DROPFILES_ERROR_CATEGORY_NOT_FOUND'));
        }

        // Get active menu
        $currentMenuItem = $app->getMenu()->getActive();
        // Get params for active menu
        $this->menuItemParams = $currentMenuItem->params;

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
            } else {
                if (isset($params->params->ordering)) {
                    $modelFiles->setState('list.ordering', $params->params->ordering);
                }
                if (isset($params->params->orderingdir)) {
                    $modelFiles->setState('list.direction', $params->params->orderingdir);
                }
                //to make storeid different to avoid duplicate results
                $modelFiles->setState('list.limit', 1000 * $cat);
                $files = $modelFiles->getItems();
            }
            $files = DropfilesFilesHelper::addInfosToFile($files, $category);


            if (!empty($params)) {
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
            $dispatcher = JDispatcher::getInstance();
            $result = $dispatcher->trigger('onShowFrontCategory', array(array('files' => $files,
                'category' => $category,
                'categories' => $categories,
                'params' => is_object($params) ? $params->params : '',
                'theme' => $theme,
                'columns'    => isset($columns) ? $columns : 2,
            )
            ));

            if (!empty($result[0])) {
                JHtml::_('behavior.framework');

                $doc = JFactory::getDocument();
                $doc->addStyleSheet(JURI::base('true') . '/components/com_dropfiles/assets/css/front.css');
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
}
