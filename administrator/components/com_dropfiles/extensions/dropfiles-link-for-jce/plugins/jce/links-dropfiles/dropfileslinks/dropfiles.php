<?php
/**
 * Dropfiles Links for JCE
 *
 * @version   1.0.0
 * @package   Dropfiles Links for JCE
 * @author    JoomUnited http://www.joomunited.com
 * @copyright Copyright © 2015 JoomUnited. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

defined('_WF_EXT') || die('Restrict Access!');

/**
 * Class DropfileslinksDropfiles
 */
class DropfileslinksDropfiles extends JObject
{
    /**
     * Component name
     *
     * @var string
     */
    public $option = 'com_dropfiles';

    /**
     * Task name
     *
     * @var string
     */
    protected $task = 'getFiles';

    /**
     * DropfileslinksDropfiles constructor.
     *
     * @since version
     */
    public function __construct()
    {
    }

    /**
     * Get Instance
     *
     * @return DropfileslinksDropfiles
     * @since  version
     */
    public function getInstance()
    {
        static $instance;

        if (!is_object($instance)) {
            $instance = new DropfileslinksDropfiles();
        }
        return $instance;
    }

    /**
     * Get option
     *
     * @return string
     * @since  version
     */
    public function getOption()
    {
        return $this->option;
    }

    /**
     * Get task
     *
     * @return string
     * @since  version
     */
    public function getTask()
    {
        return $this->task;
    }

    /**
     * Get list
     *
     * @return string
     * @since  version
     */
    public function getList()
    {
        $advlink = WFEditorPlugin::getInstance();
        $list = '';
        if ($advlink->checkAccess('dropfileslinks.dropfiles', '1')) {
            $list = '<li id="index.php?option=com_dropfiles&task=getFiles" class="folder nolink">';
            $list .= '<div class="uk-tree-row"><a href="Cannot insert category link!">';
            $list .= '<span class="uk-tree-icon folder content nolink"></span>';
            $list .= '<span class="uk-tree-text">' . JText::_('Dropfiles') . '</span>';
            $list .= '</a></div>';
            $list .= '</li>';
        }
        return $list;
    }

    /**
     * Get Items
     *
     * @param null $id_cat Category id
     *
     * @return array|mixed
     * @throws \Exception Throw when application can not start
     * @since  version
     */
    public function getItems($id_cat = null)
    {
        JLoader::register('DropfilesFilesHelper', JPATH_ADMINISTRATOR . '/components/com_dropfiles/helpers/files.php');
        if (!class_exists('DropfilesFilesHelper')) {
            // Throw error to users
            return array(
                array(
                    'class' => 'file error',
                    'name' => 'Error while loading files. Dropfiles not exist!',
                    'id' => 'Error!'
                )
            );
        }

        $dbo = JFactory::getDbo();
        $query = 'SELECT id AS pid, title AS name FROM #__categories WHERE extension = "com_dropfiles" AND published = 1 AND parent_id = ' . $id_cat . ' ORDER BY lft';
        $dbo->setQuery($query);
        $items = $dbo->loadAssocList();

        foreach ($items as $key => $item) {
            $items[$key]['class'] = 'folder content nolink';
            $items[$key]['id'] = 'index.php?option=com_dropfiles&task=getFiles&id=' . $item['pid'];
        }

        // Return immediately if in root category coz no files here
        if ($id_cat === 1) {
            return $items;
        }
        // Get all child files
        $category = $this->getCategory($id_cat);

        if (!$category) {
            return array();
        }
        // Get all child categories
        $table = '#__dropfiles_files';
        $selectCatId = $id_cat;
        $isCloud = false;
        if ($category->type === 'googledrive') {
            $table = '#__dropfiles_google_files';
            $selectCatId = '"' . $category->cloud_id . '"';
            $isCloud = true;
        } elseif ($category->type === 'dropbox') {
            $table = '#__dropfiles_dropbox_files';
            $selectCatId = '"' . $category->cloud_id . '"';
            $isCloud = true;
        } elseif ($category->type === 'onedrive') {
            $table = '#__dropfiles_onedrive_files';
            $selectCatId = '"' . $category->cloud_id . '"';
            $isCloud = true;
        }

        if (!$isCloud) {
            $query = 'SELECT i.id AS aid, i.title, i.ext, i.catid , c.title AS cat_title FROM ' . $table . ' AS i INNER JOIN #__categories AS c ON i.catid = c.id WHERE i.catid =' . $selectCatId . ' AND i.state = 1 ORDER BY i.ordering';
        } else {
            $query ='SELECT i.id AS aid, i.file_id, i.title, i.ext, i.catid, c.id as cat_id, c.title AS cat_title FROM ' . $table . ' AS i INNER JOIN #__dropfiles AS d ON d.cloud_id = i.catid INNER JOIN #__categories AS c ON d.id = c.id WHERE i.catid =' . $selectCatId . ' AND i.state = 1 ORDER BY i.ordering';
        }

        $dbo->setQuery($query);
        $files = $dbo->loadAssocList();

        foreach ($files as $key => $file) {
            $files[$key]['name'] = $file['title'] . '.' . $file['ext'];
            $files[$key]['class'] = 'file';
            if (!$isCloud) {
                $file_url = DropfilesFilesHelper::genUrl($file['aid'], $file['catid'], $file['cat_title'], '', $file['text']);
            } else {
                $file_url = DropfilesFilesHelper::genUrl($file['file_id'], $file['cat_id'], $file['cat_title'], '', $file['text']);
            }
            $files[$key]['id'] = $file_url;
        }

        // Append child files after child categories
        $items = array_merge($items, $files);

        return $items;
    }

    /**
     * Get Links
     *
     * @param array $args Arguments
     *
     * @return array|mixed
     * @throws \Exception Throw when application can not start
     * @since  version
     */
    public function getLinks($args)
    {
        $id = isset($args->id) ? $args->id : 1;
        $items = $this->getItems($id);

        return $items;
    }

    /**
     * Get category
     *
     * @param null $idcat Category id
     *
     * @return mixed|null
     * @throws \Exception Throw when application can not start
     * @since  version
     */
    public function getCategory($idcat = null)
    {
        if ($idcat === null) {
            $app = JFactory::getApplication();
            $idcat = $app->input->getInt('id', 0);
        }
        $user = JFactory::getUser();
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->from('#__categories as a');

        $query->select('a.level, a.id, a.title, a.parent_id, a.created_user_id');

        $query->where('a.published=1');
        $query->where('a.extension=' . $db->quote('com_dropfiles'));
        $query->where('a.id=' . (int)$idcat);

        $query->select('b.title as parent_title');
        $query->select('b.id as parent_id');
        $query->join(
            'LEFT OUTER',
            '#__categories AS b ON b.id=a.parent_id AND b.extension=' . $db->quote('com_dropfiles')
        );

        $query->select('c.type, c.cloud_id, c.path');
        $query->join('LEFT OUTER', '#__dropfiles AS c ON c.id=a.id');

        // Implement View Level Access
        if (!$user->authorise('core.admin')) {
            $groups = implode(',', $user->getAuthorisedViewLevels());
            $query->where('a.access IN (' . $groups . ')');
        }

        $db->setQuery($query);
        if ($db->execute()) {
            return $db->loadObject();
        }

        return null;
    }
}
