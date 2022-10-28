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
     * Display the view
     *
     * @param null|string $tpl Template
     *
     * @return string
     */
    public function display($tpl = null)
    {
        $app = JFactory::getApplication();
        $config = JComponentHelper::getParams('com_dropfiles');
        $modelC = $this->getModel('category');
        $direction = 'desc';

        if ($app->input->get('layout') === 'form') {
            $this->form = $modelC->getForm();
        } elseif ($app->input->get('layout') === 'versions') {
            $category_id = JFactory::getApplication()->input->getInt('id_category', 0);
            $file_id = JFactory::getApplication()->input->getString('id_file', 0);
            $model = $this->getModel();
            $modelCategory = JModelLegacy::getInstance('Category', 'dropfilesModel');
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
                    // todo: Restore and delete version on Onedrive not supported Microsoft API maybe possible with new Graph can
                    if ($category->type !== 'onedrive') {
                        $content .= '<td><a data-id="' . $data_id . '" data-vid="' . $rev . '" href="#" class="restore">';
                        $content .= '<i class="icon-restore"></i></a></td>';
                        $content .= '<td><a data-id="' . $data_id . '" data-vid="' . $rev . '" href="#" class="trash">';
                        $content .= '<i class="icon-trash"></i></a></td>';
                    }
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
            $modelCategory = JModelLegacy::getInstance('Category', 'dropfilesModel');
            if (!$params) {
                $params = new stdClass();
                $params->ordering = 'ordering';
                $params->orderingdir = 'asc';
            }
            $ordering = JFactory::getApplication()->input->getCmd('orderCol', $params->ordering);
            if ($ordering !== null) {
                $ordering_array = array('ordering', 'type', 'ext', 'title', 'description',
                    'created_time', 'modified_time', 'size', 'version', 'hits');
                if (!in_array($ordering, $ordering_array)) {
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
            $filters['q'] = !empty($q) ? $q : '';
            $filters['catid'] = !empty($catid) ? $catid : '';
            $filters['cattype'] = !empty($cattype) ? $cattype : '';
            $filters['ftags'] = '';
            $filters['cfrom'] = '';
            $filters['cto'] = '';
            $filters['ufrom'] = '';
            $filters['uto'] = '';
            $filters['adminsearch'] = '';
            $files = array();
            if (!empty($filters)) {
                $files = $mdFrontsearch->searchFile($filters);
            }
            if (!$files) {
                return '';
            }
            $theme = $modelC->getCategoryTheme(JFactory::getApplication()->input->getInt('id_category', 0));

            $canDo = DropfilesHelper::getActions();
            if ($canDo->get('core.edit') || $canDo->get('core.edit.own')) {
                $canOrder = true;
            } else {
                $canOrder = false;
            }

            $content = '<table class="restable">';
            $content .= '<thead><tr>';
            $row_array = array(
                'type' => array(JText::_('COM_DROPFILES_FIELD_FILE_TYPE_LABEL_ADMIN'), ''),
                'title' => array(JText::_('COM_DROPFILES_FIELD_FILE_TITLE_LABEL'), 'essential'),
                'size' => array(JText::_('COM_DROPFILES_FIELD_FILE_FILESIZE_LABEL'), ''),
                'created_time' => array(JText::_('COM_DROPFILES_FIELD_FILE_DATEADDED_LABEL'), ''),
                'modified_time' => array(JText::_('COM_DROPFILES_FIELD_FILE_DATEMODIFIED_LABEL'), ''),
                'version' => array(JText::_('COM_DROPFILES_FIELD_FILE_VERSION_LABEL'), ''),
                'hits' => array(JText::_('COM_DROPFILES_FIELD_HITS_LABEL'), '')
            );
            foreach ($row_array as $row => $title) {
                $content .= '<th class="' . $title[1] . '">';
                if ($canOrder) {
                    $content .= '<a href="#" class="' . ($ordering === $row ? 'currentOrderingCol' : '');
                    $content .= '" data-ordering="' . $row . '" data-direction="' . $direction . '">';
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
                $httpcheck = isset($file->file) ? $file->file : '';
                $remote_file = preg_match('(http://|https://)', $httpcheck) ? 'is-remote-url' : '';
                $unpublish = $file->state === 0 ? 'dropfiles-unpublished' : '';
                $category = null;
                $catId = '';
                $link_download_frontend = '';
                if (isset($file->catid)) {
                    $category = $modelCategory->getCategory($file->catid);
                    $catId = isset($file->catid) ? $file->catid : $category->id;
                    $link_download_frontend = DropfilesFilesHelper::genUrl($file->id, $catId, $category->title, '', $file->title . '.' . $file->ext);
                }

                $file_ext = isset($file ->ext) ? $file->ext : '';
                $content .= '<tr class="file ' . $remote_file . ' ' . $unpublish;
                $content .= '" data-id-file="' . $file->id . '" data-id-category="' . $catId . '" data-friendlylinkdownload="'. JRoute::_($link_download_frontend) .'">';

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
                $content .= '<td class="modified">';
                $content .= $file->modified_time;
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
            $category_id = JFactory::getApplication()->input->getInt('id_category', 0);
            $model = $this->getModel();
            $modelCategory = JModelLegacy::getInstance('Category', 'dropfilesModel');
            $modelConfig = JModelLegacy::getInstance('Config', 'dropfilesModel');
            $category = $modelCategory->getCategory($category_id);
            if (!$category) {
                return '';
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
                    $ordering_array = array('ordering', 'type', 'ext', 'title', 'description',
                        'created_time', 'modified_time', 'size', 'version', 'hits');
                    if (!in_array($ordering, $ordering_array)) {
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
                $modelGoogle = JModelLegacy::getInstance('Googlefiles', 'dropfilesModel');
                $files = $modelGoogle->getItems($category->cloud_id, $ordering, $direction);

                if ($files === false) {
                    echo '<div class="alert alert-danger">' . $google->getLastError() . '</div>';
                    return '';
                }
            } elseif ($category->type === 'dropbox') {
                $ordering = JFactory::getApplication()->input->getCmd('orderCol', $params->ordering);
                if ($ordering !== null) {
                    $ordering_array = array('ordering', 'type', 'ext', 'title', 'description',
                        'created_time', 'modified_time', 'size', 'version', 'hits');
                    if (!in_array($ordering, $ordering_array)) {
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
                $modelDropbox = JModelLegacy::getInstance('dropboxfiles', 'dropfilesModel');
                $files = $modelDropbox->getItems($category->cloud_id, $ordering, $direction);

                if ($files === false) {
                    echo '<div class="alert alert-danger"></div>';
                    return '';
                }
            } elseif ($category->type === 'onedrive') {
                $ordering = JFactory::getApplication()->input->getCmd('orderCol', $params->ordering);
                if ($ordering !== null) {
                    $ordering_array = array('ordering', 'type', 'ext', 'title', 'description',
                        'created_time', 'modified_time', 'size', 'version', 'hits');
                    if (!in_array($ordering, $ordering_array)) {
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
                $modelOnedrive = JModelLegacy::getInstance('onedrivefiles', 'dropfilesModel');
                $files = $modelOnedrive->getItems($category->cloud_id, $ordering, $direction);

                if ($files === false) {
                    echo '<div class="alert alert-danger"></div>';
                    return '';
                }
            } elseif ($category->type === 'onedrivebusiness') {
                $ordering = JFactory::getApplication()->input->getCmd('orderCol', $params->ordering);
                if ($ordering !== null) {
                    $ordering_array = array('ordering', 'type', 'ext', 'title', 'description',
                        'created_time', 'modified_time', 'size', 'version', 'hits');
                    if (!in_array($ordering, $ordering_array)) {
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
                $modelOnedriveBusiness = JModelLegacy::getInstance('onedrivebusinessfiles', 'dropfilesModel');
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
            $theme = $modelC->getCategoryTheme(JFactory::getApplication()->input->getInt('id_category', 0));

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
                $row_array = array(
                    'ext' => array(JText::_('COM_DROPFILES_FIELD_FILE_TYPE_LABEL_ADMIN'), ''),
                    'title' => array(JText::_('COM_DROPFILES_FIELD_FILE_TITLE_LABEL'), 'essential'),
                    'size' => array(JText::_('COM_DROPFILES_FIELD_FILE_FILESIZE_LABEL'), ''),
                    'created_time' => array(JText::_('COM_DROPFILES_FIELD_FILE_DATEADDED_LABEL'), ''),
                    'modified_time' => array(JText::_('COM_DROPFILES_FIELD_FILE_DATEMODIFIED_LABEL'), ''),
                    'version' => array(JText::_('COM_DROPFILES_FIELD_FILE_VERSION_LABEL'), ''),
                    'hits' => array(JText::_('COM_DROPFILES_FIELD_HITS_LABEL'), '')
                );
                foreach ($row_array as $row => $title) {
                    $content .= '<th class="' . $row . ' ' . $title[1] . '">';
                    if ($canOrder) {
                        $content .= '<a href="#" class="' . ($ordering === $row ? 'currentOrderingCol' : '');
                        $content .= '' . $title[1] . '" data-ordering="' . $row . '" data-direction="' . $direction . '">';
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
                $unpublish = (int) $file->state === 0 ? 'dropfiles-unpublished' : '';
                $link_download = 'index.php?option=com_dropfiles&task=file.download&id=' . $file->id;
                $link_download .= '&catid=' . (isset($file->catid) ? $file->catid : $category->id);

                $catId = isset($file->catid) ? $file->catid : $category->id;
                $link_download_frontend = DropfilesFilesHelper::genUrl($file->id, $catId, $category->title, '', $file->title . '.' . $file->ext);
                $file_ext = isset($file ->ext) ? $file->ext : '';
                $content .= '<tr class="file ' . $remote_file . ' ' . $unpublish . '" data-id-file="';
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
                $content .= $date->format($config->get('date_format', 'Y-m-d'));
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
