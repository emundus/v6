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
 * Class DropfilesViewFiles
 */
class DropfilesViewFiles extends JViewLegacy
{
    /**
     * State
     *
     * @var string
     */
    protected $state;

    /**
     *  Method display files
     *
     * @param null|string $tpl Template
     *
     * @return string
     */
    public function display($tpl = null)
    {
        $app = JFactory::getApplication();
        $modelCategory = JModelLegacy::getInstance('Category', 'dropfilesModel');
        $config = JComponentHelper::getParams('com_dropfiles');
        if ($app->input->get('layout') === 'form') {
            $this->form = $modelCategory->getForm();
        } elseif ($app->input->get('layout') === 'versions') {
            $category_id = JFactory::getApplication()->input->getInt('id_category', 0);
            $file_id = JFactory::getApplication()->input->getString('id_file', 0);
            $model = $this->getModel();

            $category = $modelCategory->getCategory($category_id);

            if (!$category) {
                return '';
            }

            if ($category->type === 'googledrive') {
                $google = new DropfilesGoogle();
                $files = $google->listVersions($file_id);
            } elseif ($category->type === 'dropbox') {
                $dropbox = new DropfilesDropbox();
                $files = $dropbox->displayDropboxVersionInfo($file_id);
            } elseif ($category->type === 'onedrive') {
                $onedrive = new DropfilesOneDrive();
                $files = $onedrive->listVersions($file_id);
            } else {
                $files = $model->getVersions($file_id);
            }
            $content = '';
            if (!empty($files)) {
                $content .= '<table>';
                foreach ($files as $file) {
                    $content .= '<tr>';
                    if ($category->type === 'dropbox') {
                        $version = '1';
                        $data_id = $file->id;
                        $rev = $file->meta_id;
                    } elseif ($category->type === 'googledrive') {
                        $version = $file->id_version;
                        $data_id = $file->id;
                        $rev     = $file->id_version;
                    } elseif ($category->type === 'onedrive') {
                        $version = $file->id_version;
                        $data_id = $file->id;
                        $rev     = $file->id_version;
                    } else {
                        $version = $file->id;
                        $data_id = $file->id;
                        $rev = $file->id;
                    }
                    $content .= '<td><a title="' . date('H:i:s', strtotime($file->created_time));
                    $content .= '" href="index.php?option=com_dropfiles&task=file.download&version=' . $version;
                    $content .= '&vid=' . $rev . '&id=' . $data_id . '&catid=' . $category_id . '" target="_blank">';

                    $content .= date('Y M d', strtotime($file->created_time)) . ' ';
                    $content .= '</a></td>';
                    $content .= '<td>' . DropfilesFilesHelper::bytesToSize($file->size) . '</td>';

                    $content .= '<td><a data-id="' . $data_id . '" data-vid="' . $rev . '" href="#" class="restore">';
                    $content .= '<i class="icon-restore"></i></a></td>';
                    $content .= '<td><a data-id="' . $data_id . '" data-vid="' . $rev . '" href="#" class="trash">';
                    $content .= '<i class="icon-trash"></i></a></td>';

                    $content .= '</tr>';
                }
                $content .= '</table>';
            }
            echo $content;
        } elseif ($app->input->get('layout') === 'search') {
            $path_dropfilesgoogle = JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesGoogle.php';
            JLoader::register('DropfilesGoogle', $path_dropfilesgoogle);
            $mdFrontsearch = JModelLegacy::getInstance('frontsearch', 'dropfilesModel');
            $filters = array();
            $app = JFactory::getApplication();
            $q = $app->input->getString('s');
            $catid = $app->input->getString('cid');
            $cattype = $app->input->getString('cattype');

            $modelConfig = JModelLegacy::getInstance('Config', 'dropfilesModel');
            $params = $modelConfig->getParams($catid);
            if (!$params) {
                $params = new stdClass();
                $params->ordering = 'ordering';
                $params->orderingdir = 'asc';
            }
            $ordering = JFactory::getApplication()->input->getCmd('orderCol', $params->ordering);
            if ($ordering !== null) {
                if (!in_array($ordering, array(
                    'ordering',
                    'type',
                    'ext',
                    'title',
                    'description',
                    'created_time',
                    'modified_time',
                    'size',
                    'version',
                    'hits'
                ))) {
                    $ordering = 'ordering';
                } else {
                    $direction = JFactory::getApplication()->input->getCmd('orderDir', $params->orderingdir);
                    if ($direction === 'asc') {
                        $direction = 'desc';
                    } else {
                        $direction = 'asc';
                    }
                }
            } else {
                $ordering = 'ordering';
            }

            $filters['q']           = !empty($q) ? $q : '';
            $filters['catid']       = !empty($catid) ? $catid : '';
            $filters['cattype']     = !empty($cattype) ? $cattype : '';
            $filters['ftags']       = '';
            $filters['cfrom']       = '';
            $filters['cto']         = '';
            $filters['ufrom']       = '';
            $filters['uto']         = '';
            $filters['adminsearch'] = '';
            $files                  = array();
            if (!empty($filters)) {
                $files = $mdFrontsearch->searchfile($filters);
            }
            if (!$files) {
                return '';
            }
            $theme = $modelCategory->getCategoryTheme(JFactory::getApplication()->input->getInt('id_category', 0));

            $canDo = DropfilesHelper::getActions();
            if ($canDo->get('core.edit') || $canDo->get('core.edit.own')) {
                $canOrder = true;
            } else {
                $canOrder = false;
            }

            $content          = '<table class="restable">';
            $content          .= '<thead><tr>';
            $array_field_file = array(
                'type'          => array(JText::_('COM_DROPFILES_FIELD_FILE_TYPE_LABEL_ADMIN'), ''),
                'title'         => array(JText::_('COM_DROPFILES_FIELD_FILE_TITLE_LABEL'), 'essential'),
                'size'          => array(JText::_('COM_DROPFILES_FIELD_FILE_FILESIZE_LABEL'), ''),
                'created_time'  => array(JText::_('COM_DROPFILES_FIELD_FILE_DATEADDED_LABEL'), ''),
                'modified_time' => array(JText::_('COM_DROPFILES_FIELD_FILE_DATEMODIFIED_LABEL'), ''),
                'version'       => array(JText::_('COM_DROPFILES_FIELD_FILE_VERSION_LABEL'), ''),
                'hits'          => array(JText::_('COM_DROPFILES_FIELD_HITS_LABEL'), '')
            );
            foreach ($array_field_file as $row => $title) {
                $content .= '<th class="' . $title[1] . '">';
                $direction = JFactory::getApplication()->input->getCmd('orderDir', $params->orderingdir);
                if ($canOrder) {
                    $content .= '<a href="#" class="' . ($ordering === $row ? 'currentOrderingCol' : '')
                        . '" data-ordering="' . $row . '" data-direction="' . $direction . '">';
                }
                $content .= $title[0];

                if ($row === $ordering) {
                    $icon = 'zmdi-caret-' . ($direction === 'asc' ? 'up' : 'down');
                    $content .= '<i class="zmdi ' . $icon . '"></i>';
                }

                if ($canOrder) {
                    $content .= '</a>';
                }
                $content .= '</th>';
            }
            $content .= '</tr></thead>';
            $content .= '<tbody>';


            foreach ($files as $file) {
                $httpcheck   = isset($file->file) ? $file->file : '';
                $remote_file = preg_match('(http://|https://)', $httpcheck) ? 'is-remote-url' : '';
                $unpublish   = '';
                $category    = null;
                $catId       = '';
                if (isset($file->catid)) {
                    $category = $modelCategory->getCategory($file->catid);
                    $catId    = isset($file->catid) ? $file->catid : $category->id;
                }
                $file_ext = isset($file->ext) ? $file->ext : '';
                if (isset($file->state)) {
                    $unpublish = (int)$file->state === 0 ? 'dropfiles-unpublished' : '';
                }
                $content .= '<tr class="file ' . $remote_file . ' ' . $unpublish
                    . '" data-id-file="' . $file->id . '" data-id-category="' . $catId . '">';

                $content .= '<td class="type">';
                $content .= '<div class="ext '. $file_ext .'"><span class="txt">'. $file_ext .'</span></div>';
                $content .= '</td>';
                $content .= '<td class="title">';
                $content .= $file->title;
                $content .= '</td>';
                $content .= '<td class="size">';
                if ($file->size > 0) {
                    $content .= DropfilesFilesHelper::bytesToSize($file->size);
                } else {
                    $content .= 'unknown';
                }
                $content .= '</td>';
                $content .= '<td class="created">';
                $content .= $file->created_time;
                $content .= '</td>';
                $content .= '<td class="version">';
                $content .= $file->version;
                $content .= '</td>';
                $content .= '<td class="hits">';
                $content .= $file->hits . ' ' . JText::_('COM_DROPFILES_LAYOUT_DROPFILES_HITS');
                $content .= '</td>';
                $content .= '</tr>';
            }
            $content .= '</tbody>';
            $content .= '</table>';
            $content .= '<input type="hidden" name="theme" value="' . strtolower($theme) . '">';
            echo $content;
        } else {
            $direction = 'asc';
            $category_id = JFactory::getApplication()->input->getInt('id_category', 0);
            $model = $this->getModel();

            $modelConfig = JModelLegacy::getInstance('Config', 'dropfilesModel');
            $category = $modelCategory->getCategory($category_id);
            if (!$category) {
                return;
            }
            $params = $modelConfig->getParams($category->id);
            if (!$params) {
                $params = new stdClass();
                $params->ordering = 'ordering';
                $params->orderingdir = 'asc';
            }
            if ($category->type === 'googledrive') {
                $google = new DropfilesGoogle();

                $ordering = JFactory::getApplication()->input->getCmd('orderCol', $params->ordering);
                if ($ordering !== null) {
                    if (!in_array($ordering, array(
                        'ordering',
                        'type',
                        'ext',
                        'title',
                        'description',
                        'created_time',
                        'modified_time',
                        'size',
                        'version',
                        'hits'
                    ))) {
                        $ordering = 'ordering';
                    } else {
                        $direction = JFactory::getApplication()->input->getCmd('orderDir', $params->orderingdir);
                        if ($direction !== 'desc') {
                            $direction = 'asc';
                        }
                    }
                } else {
                    $ordering = 'ordering';
                }

                $modelGoogle = JModelLegacy::getInstance('Frontgoogle', 'dropfilesModel');
                $files = $modelGoogle->getItems($category->cloud_id, $ordering, $direction);
                //$files = $google->listFiles($category->cloud_id,$ordering,$direction);
                if ($files === false) {
                    echo '<div class="alert alert-danger">' . $google->getLastError() . '</div>';
                    return '';
                }
            } elseif ($category->type === 'dropbox') {
                $direction = 'asc';
                $ordering = JFactory::getApplication()->input->getCmd('orderCol', $params->ordering);
                if ($ordering !== null) {
                    if (!in_array($ordering, array(
                        'ordering',
                        'type',
                        'ext',
                        'title',
                        'description',
                        'created_time',
                        'modified_time',
                        'size',
                        'version',
                        'hits'
                    ))) {
                        $ordering = 'ordering';
                    } else {
                        $direction = JFactory::getApplication()->input->getCmd('orderDir', $params->orderingdir);
                        if ($direction !== 'desc') {
                            $direction = 'asc';
                        }
                    }
                } else {
                    $ordering = 'ordering';
                }
                $modelDropbox = JModelLegacy::getInstance('frontdropbox', 'dropfilesModel');
                $files = $modelDropbox->getItems($category->cloud_id, $ordering, $direction);
            } elseif ($category->type === 'onedrive') {
                $ordering = JFactory::getApplication()->input->getCmd('orderCol', $params->ordering);
                if ($ordering !== null) {
                    if (!in_array($ordering, array(
                        'ordering',
                        'type',
                        'ext',
                        'title',
                        'description',
                        'created_time',
                        'modified_time',
                        'size',
                        'version',
                        'hits'
                    ))) {
                        $ordering = 'ordering';
                    } else {
                        $direction = JFactory::getApplication()->input->getCmd('orderDir', $params->orderingdir);
                        if ($direction !== 'desc') {
                            $direction = 'asc';
                        }
                    }
                } else {
                    $ordering = 'ordering';
                }
                $modelOnedrive = JModelLegacy::getInstance('frontonedrive', 'dropfilesModel');
                $files = $modelOnedrive->getItems($category->cloud_id, $ordering, $direction);

                if ($files === false) {
                    echo '<div class="alert alert-danger"></div>';
                    return '';
                }
            } elseif ($category->type === 'onedrivebusiness') {
                $ordering = JFactory::getApplication()->input->getCmd('orderCol', $params->ordering);
                if ($ordering !== null) {
                    if (!in_array($ordering, array(
                        'ordering',
                        'type',
                        'ext',
                        'title',
                        'description',
                        'created_time',
                        'modified_time',
                        'size',
                        'version',
                        'hits'
                    ))) {
                        $ordering = 'ordering';
                    } else {
                        $direction = JFactory::getApplication()->input->getCmd('orderDir', $params->orderingdir);
                        if ($direction !== 'desc') {
                            $direction = 'asc';
                        }
                    }
                } else {
                    $ordering = 'ordering';
                }
                $modelOnedriveBusiness = JModelLegacy::getInstance('frontonedrivebusiness', 'dropfilesModel');
                $files = $modelOnedriveBusiness->getItems($category->cloud_id, $ordering, $direction);

                if ($files === false) {
                    echo '<div class="alert alert-danger"></div>';
                    return '';
                }
            } else {
                $files = $model->getItems();
                $ordering = $model->getState('list.ordering', false);
                $direction = $model->getState('list.direction', false);
            }
            $theme = $modelCategory->getCategoryTheme(JFactory::getApplication()->input->getInt('id_category', 0));

            $canDo = DropfilesHelper::getActions();
            if ($canDo->get('core.edit') || $canDo->get('core.edit.own')) {
                $canOrder = true;
            } else {
                $canOrder = false;
            }

            $refFileParams = (array) $params;
            $listCateRef = (isset($refFileParams['refToFile'])) ? $refFileParams['refToFile'] : array();
            $lstAllFile        = null;
            if (!empty($refFileParams) && isset($refFileParams['refToFile'])) {
                if (isset($refFileParams['refToFile'])) {
                    $listCatRef = $refFileParams['refToFile'];
                    $lstAllFile = $this->getAllFileRef($model, $listCatRef, $ordering, $direction);
                }
            }
            if (!empty($lstAllFile)) {
                $files = array_merge($lstAllFile, $files);
                $ordering = JFactory::getApplication()->input->getCmd('orderCol', $params->ordering);
                $direction = JFactory::getApplication()->input->getCmd('orderDir', $params->orderingdir);
                $files = DropfilesHelper::orderingMultiCategoryFiles($files, $ordering, $direction);
            }

            if (!empty($files)) {
                $content = '<table class="restable">';
                $content .= '<thead><tr>';
                $array_field_file = array(
                    'ext'  => array(JText::_('COM_DROPFILES_FIELD_FILE_TYPE_LABEL_ADMIN'), ''),
                    'title' => array(JText::_('COM_DROPFILES_FIELD_FILE_TITLE_LABEL'), 'essential'),
                    'size' => array(JText::_('COM_DROPFILES_FIELD_FILE_FILESIZE_LABEL'), ''),
                    'created_time' => array(JText::_('COM_DROPFILES_FIELD_FILE_DATEADDED_LABEL'), ''),
                    'modified_time' => array(JText::_('COM_DROPFILES_FIELD_FILE_DATEMODIFIED_LABEL'), ''),
                    'version' => array(JText::_('COM_DROPFILES_FIELD_FILE_VERSION_LABEL'), ''),
                    'hits' => array(JText::_('COM_DROPFILES_FIELD_HITS_LABEL'), '')
                );
                foreach ($array_field_file as $row => $title) {
                    $content .= '<th class="' . $row . '">';
                    if ($canOrder) {
                        $content .= '<a href="#" class="' . ($ordering === $row ? 'currentOrderingCol' : '')
                            . '" data-ordering="' . $row . '" data-direction="' . $direction . '">';
                    }
                    $content .= $title[0];

                    if ($row === $ordering) {
                        $icon = 'zmdi-caret-' . ($direction === 'asc' ? 'up' : 'down');
                        $content .= '<i class="zmdi ' . $icon . '"></i>';
                    }

                    if ($canOrder) {
                        $content .= '</a>';
                    }
                    $content .= '</th>';
                }
                $content .= '</tr></thead>';
            } else {
                $content = '<table class="restable">';
                $content .= '<thead><tr>';
                $content .= '</tr></thead>';
            }
            $content .= '<tbody>';


            foreach ($files as $file) {
                $httpcheck = isset($file->file) ? $file->file : '';
                $remote_file = preg_match('(http://|https://)', $httpcheck) ? 'is-remote-url' : '';
                $unpublish = '';
                if (isset($file->state)) {
                    $unpublish = (int) $file->state === 0 ? 'dropfiles-unpublished' : '';
                }
                $link_download = 'index.php?option=com_dropfiles&task=frontfile.download&id=' . $file->id;
                $link_download .= '&catid=' . (isset($file->catid) ? $file->catid : $category->id);
                $catId = isset($file->catid) ? $file->catid : $category->id;
                $link_download_frontend = DropfilesFilesHelper::genUrl($file->id, $catId, $category->title, '', $file->title . '.' . $file->ext);
                $file_ext = isset($file->ext) ? $file->ext : '';

                $content .= '<tr class="file ' . $remote_file . ' ' . $unpublish
                    . '" data-id-file="';
                $content .= $file->id . '" data-id-category="' . $catId . '" data-linkdownload="' . $link_download . '" ';
                $content .= 'data-friendlylinkdownload="' . JRoute::_($link_download_frontend) . '" ';
                $content .= '>';
                $content .= '<td class="type">';
                $content .= '<div class="ext '. $file_ext .'"><span class="txt">'. $file_ext .'</span></div>';
                $content .= '</td>';
                $content .= '<td class="title">';
                $content .= $file->title;
                $content .= '</td>';
                $content .= '<td class="size">';
                if ($file->size > 0) {
                    $content .= DropfilesFilesHelper::bytesToSize($file->size);
                } else {
                    $content .= 'unknown';
                }
                $content .= '</td>';
                $content .= '<td class="created">';
                $date = new JDate($file->created_time);
                $content .= $date->format('Y-m-d');
                $content .= '</td>';
                $content .= '<td class="modified">';
                $date = new JDate($file->modified_time);
                $content .= $date->format($config->get('date_format', 'Y-m-d'));
                $content .= '</td>';
                $content .= '<td class="version">';
                $content .= $file->version;
                $content .= '</td>';
                $content .= '<td class="hits">';
                $content .= $file->hits . ' ' . JText::_('COM_DROPFILES_LAYOUT_DROPFILES_HITS');
                $content .= '</td>';
                $content .= '</tr>';
            }
            $content .= '</tbody>';
            $content .= '</table>';
            $content .= '<input type="hidden" name="theme" value="' . strtolower($theme) . '">';
            echo $content;
        }
//            parent::display($tpl);
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
