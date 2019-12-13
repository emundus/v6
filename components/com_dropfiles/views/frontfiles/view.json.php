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
        } else {
            $model->getState('onsenfout'); //To autopopulate state
            $model->setState('filter.access', false);
            if (isset($params->params->ordering)) {
                $model->setState('list.ordering', $params->params->ordering);
            }
            if (isset($params->params->orderingdir)) {
                $model->setState('list.direction', $params->params->orderingdir);
            }
            $files = $model->getItems();
        }

        $content = new stdClass();
        $content->files = DropfilesFilesHelper::addInfosToFile($files, $category);
        $content->category = $category;

        echo json_encode($content);
        JFactory::getApplication()->close();
    }
}
