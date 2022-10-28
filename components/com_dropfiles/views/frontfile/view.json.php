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
 * Class DropfilesViewFrontfile
 */
class DropfilesViewFrontfile extends JViewLegacy
{
    /**
     * Display the view
     *
     * @param null|string $tpl Template
     *
     * @return string
     */
    public function display($tpl = null)
    {
        $model = $this->getModel('frontfile');
        $id = JFactory::getApplication()->input->getString('id', 0);
        $catid = JFactory::getApplication()->input->getInt('catid', 0);
        $modelCat = $this->getModel('frontcategory');
        $category = $modelCat->getCategory($catid);

        if ($category->type === 'googledrive') {
            $path_dropfilesgoogle = JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesGoogle.php';
            JLoader::register('DropfilesGoogle', $path_dropfilesgoogle);
            $google = new DropfilesGoogle();
            $file = $google->getFileInfos($id, $category->cloud_id);
        } elseif ($category->type === 'dropbox') {
            $modelDropbox = JModelLegacy::getInstance('Frontdropbox', 'dropfilesModel');
            $file = $modelDropbox->getFile($id);
        } elseif ($category->type === 'onedrive') {
            $modelOnedrive = JModelLegacy::getInstance('Frontonedrive', 'dropfilesModel');
            $file = $modelOnedrive->getFile($id);
        } elseif ($category->type === 'onedrivebusiness') {
            $modelOnedriveBusiness = JModelLegacy::getInstance('Frontonedrivebusiness', 'dropfilesModel');
            $file = $modelOnedriveBusiness->getFile($id);
        } else {
            $file = $model->getFile($id);
            if (!$file) {
                return json_encode(new stdClass());
            }
        }

        $user = JFactory::getUser();
        $config = JComponentHelper::getParams('com_dropfiles');
        if ($config->get('categoryrestriction', 'accesslevel') === 'accesslevel') {
            $groups = $user->getAuthorisedViewLevels();
            if (!in_array($category->access, $groups)) {
                return json_encode(new stdClass());
            }
        } else {
            $usergroup = isset($category->params->usergroup) ? $category->params->usergroup : array();

            $result = array_intersect($user->getAuthorisedGroups(), $usergroup);
            if (!count($result)) {
                return json_encode(new stdClass());
            }
        }

        $content = new stdClass();
        $content->file = DropfilesFilesHelper::addInfosToFile(json_decode(json_encode($file), false), $category);
        echo json_encode($content);
        JFactory::getApplication()->close();
    }
}
