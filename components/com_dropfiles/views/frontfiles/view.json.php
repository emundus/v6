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
 * Class DropfilesViewFrontfiles
 */
class DropfilesViewFrontfiles extends JViewLegacy
{
    /**
     * Display the view
     *
     * @param null|string $tpl Template
     *
     * @return boolean
     */
    public function display($tpl = null)
    {
        JLoader::register('DropfilesFilesHelper', JPATH_ADMINISTRATOR . '/components/com_dropfiles/helpers/files.php');
        $model = $this->getModel();
        $modelCat = $this->getModel('frontcategory');
        $category = $modelCat->getCategory();
        $modelConfig = JModelLegacy::getInstance('Frontconfig', 'dropfilesModel');

        if (!$category) {
            return false;
        }

        $params = $modelConfig->getParams($category->id);
        //check category restriction
        $user = JFactory::getUser();
        $config = JComponentHelper::getParams('com_dropfiles');
        if ($config->get('categoryrestriction', 'accesslevel') === 'accesslevel') {
            $groups = $user->getAuthorisedViewLevels();
            if (!in_array($category->access, $groups)) {
                return false;
            }
        } else {
            $usergroup = isset($params->params->usergroup) ? $params->params->usergroup : array();
            $result = array_intersect($user->getAuthorisedGroups(), $usergroup);
            if (!count($result)) {
                return false;
            }
        }

        if ($category->type === 'googledrive') {
            $user = JFactory::getUser();
            $access = $user->getAuthorisedViewLevels();
            if (!in_array($category->access, $access)) {
                return false;
            }
            $path_dropfilesgoogle = JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesGoogle.php';
            JLoader::register('DropfilesGoogle', $path_dropfilesgoogle);
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
            $model->getState('onsenfout'); //To autopopulate state
            $model->setState('filter.access', false);
            $subparams    = (array) $params->params;
            $lstAllFile   = null;
            $ordering     = (isset($params->params->ordering)) ? $params->params->ordering : '';
            $orderingdir  = (isset($params->params->orderingdir)) ? $params->params->orderingdir : '';
            if (isset($params->params->ordering)) {
                $model->setState('list.ordering', $params->params->ordering);
            }
            if (isset($params->params->orderingdir)) {
                $model->setState('list.direction', $params->params->orderingdir);
            }
            if (!empty($subparams) && isset($subparams['refToFile'])) {
                if (isset($subparams['refToFile'])) {
                    $listCatRef = $subparams['refToFile'];
                    $lstAllFile = $this->getAllFileRef($model, $listCatRef, $ordering, $orderingdir);
                }
            }
            $files = $model->getItems();
            if (!empty($lstAllFile)) {
                $files = array_merge($lstAllFile, $files);
                if (isset($params->params->ordering) && isset($params->params->orderingdir)) {
                    $ordering = $params->params->ordering;
                    $direction = $params->params->orderingdir;
                    $files = DropfilesHelper::orderingMultiCategoryFiles($files, $ordering, $direction);
                }
            }
        }

        $content = new stdClass();
        $content->files = DropfilesFilesHelper::addInfosToFile($files, $category);
        $content->category = $category;

        echo json_encode($content);
        JFactory::getApplication()->close();
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
    public function getAllFileRef($model, $listCatRef, $ordering, $orderingdir)
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
