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

//jimport('joomla.application.component.modeladmin');
//jimport('joomla.access.access');
$path_admin_categories = JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components';
$path_admin_categories .= DIRECTORY_SEPARATOR . 'com_categories' . DIRECTORY_SEPARATOR . 'models';
$path_admin_categories .= DIRECTORY_SEPARATOR . 'categories.php';

require_once($path_admin_categories);

/**
 * Class DropfilesModelCategories
 */
class DropfilesModelCategories extends CategoriesModelCategories
{
    /**
     * Can do
     *
     * @var mixed
     */
    protected $canDo;

    /**
     * DropfilesModelCategories constructor.
     *
     * @param array $config Config
     *
     * @return void
     * @throws Exception Throw when application can not start
     * @since  version
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
        $app = JFactory::getApplication();
        $app->setUserState('com_categories.categories.filter.extension', 'com_dropfiles');
        $app->setUserState('list.limit', 100000000);
    }


    /**
     * Method Extend to auto-populate the model state.
     *
     * @param null|string $ordering  Ordering
     * @param null|string $direction Direction
     *
     * @return void
     * @since  version
     */
    public function populateState($ordering = null, $direction = null)
    {
        parent::populateState($ordering, $direction);
        $this->setState('list.start', 0);
        $this->setState('filter.extension', 'com_dropfiles');
        $this->state->set('list.limit', 100000000);
    }

    /**
     * Method get list categories
     *
     * @return mixed
     * @since  version
     */
    public function getListQuery()
    {
        $db = $this->getDbo();
        $this->setState('filter.access', null); //don't want to use Joomla access

        $query = $db->getQuery(true);


        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.id, a.title, a.alias, a.note, a.published, a.access' .
                ', a.checked_out, a.checked_out_time, a.created_user_id' .
                ', a.path, a.parent_id, a.level, a.lft, a.rgt' .
                ', a.language'
            )
        );
        $query->from('#__categories AS a');

        // Join over the language
        $query->select('l.title AS language_title')
            ->join('LEFT', $db->quoteName('#__languages') . ' AS l ON l.lang_code = a.language');

        // Join over the users for the checked out user.
        $query->select('uc.name AS editor')
            ->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

        //Join over the dropfiles categories
        $query->select('d.type AS type')
            ->join('LEFT', '#__dropfiles AS d ON d.id=a.id');

        // Filter by extension
        $extension = $this->getState('filter.extension');
        if ($extension) {
            $query->where('a.extension = ' . $db->quote($extension));
        }

        // Filter on the level.
        $level = $this->getState('filter.level');
        if ($level) {
            $query->where('a.level <= ' . (int)$level);
        }

        // Filter by published state
        $published = $this->getState('filter.published');
        if (is_numeric($published)) {
            $query->where('a.published = ' . (int)$published);
        } elseif ($published === '') {
            $query->where('(a.published IN (0, 1))');
        }
        // Filter by category type
        $dropfiles_params = JComponentHelper::getParams('com_dropfiles');
        $catType = array($db->quote('default'));

        if ($dropfiles_params->get('google_credentials', '') !== '') {
            $catType[] = $db->quote('googledrive');
        }
        if ($dropfiles_params->get('onedriveCredentials', '') !== '') {
            $catType[] = $db->quote('onedrive');
        }
        if ($dropfiles_params->get('dropbox_token', '') !== '') {
            $catType[] = $db->quote('dropbox');
        }
        $getType = implode(',', $catType);
        $query->where('d.type IN (' . $getType . ')');

        $catid = $this->getState('category.id', null);
        if ($catid !== null) {
            $subQuery = 'SELECT rgt,lft FROM #__categories WHERE id=' . (int)$catid . " AND extension='com_dropfiles'";
            $db->setQuery($subQuery);
            if (!$db->query()) {
                return false;
            }
            $parent = $db->loadObject();
            $recursive = $this->getState('category.recursive', null);
            if ($recursive) {
                $query->where('a.rgt<= ' . (int)$parent->rgt);
                $query->where('a.lft> ' . (int)$parent->lft);
            } else {
                $query->where('a.parent_id = ' . (int)$catid);
            }
        }

        $query->select('COUNT(f.id) as files');
        $query->join('LEFT', '#__dropfiles_files AS f ON a.id = f.catid');
//        $query->where('a.id IS NOT null');
        $query->group('a.id');

        if ($this->getState('category.frontcategories', false) === false) {
            $canDo = DropfilesHelper::getActions();
            if (($canDo->get('core.edit.own') && !$canDo->get('core.edit')) ||
                (!$canDo->get('core.edit.own') && !$canDo->get('core.edit'))) {
                $query->where('created_user_id=' . (int)JFactory::getUser()->id);
            }
        }
        $query->order('a.lft ASC');

        return $query;
    }


    /**
     * Get all categories for module latest files setting
     *
     * @param boolean $replaceCloudId Replace id to cloud id or keep it
     *
     * @return mixed
     * @since  version
     */
    public function getAllCategories($replaceCloudId = true)
    {
        $dbo = $this->getDbo();
        $dropfiles_params = JComponentHelper::getParams('com_dropfiles');
        $catType = array($dbo->quote('default'));

        if ($dropfiles_params->get('google_credentials', '') !== '') {
            $catType[] = $dbo->quote('googledrive');
        }
        if ($dropfiles_params->get('onedriveCredentials', '') !== '') {
            $catType[] = $dbo->quote('onedrive');
        }
        if ($dropfiles_params->get('dropbox_token', '') !== '') {
            $catType[] = $dbo->quote('dropbox');
        }

        $query = 'SELECT d.type, d.cloud_id, c.* FROM #__categories as c,#__dropfiles as d';
        $query .= " WHERE c.id=d.id AND c.extension='com_dropfiles'";
        $getType = implode(',', $catType);
        $query .= ' AND ' . $dbo->quoteName('d.type') . ' IN (' . $getType . ')';
        $query .= ' ORDER BY c.lft ASC';
        $dbo->setQuery($query);
        $listCat = $dbo->loadObjectList();

        if ($replaceCloudId) {
            foreach ($listCat as $key => $value) {
                if ($value->cloud_id !== '') {
                    $value->id = $value->cloud_id;
                }
            }
            $listCat = array_values($listCat);
        }

        return $listCat;
    }

    /**
     * Get all categories
     *
     * @return mixed
     * @since  version
     */
    public function getAllCat()
    {
        // Get a db connection.
        $db = JFactory::getDbo();

        $query = $db->getQuery(true);
        $query
            ->select('*')
            ->from('#__categories AS c, #__dropfiles AS d')
            ->where($db->quoteName('c.id') . ' = ' . $db->quoteName('d.id') .
                ' AND d.type=' . $db->quote('googledrive'));

        $db->setQuery($query);
        $results = $db->loadObjectList();

        return $results;
    }


    /**
     * Get all dropbox categories
     *
     * @return mixed
     * @since  version
     */
    public function getAllDropboxCat()
    {
        // Get a db connection.
        $db = JFactory::getDbo();

        $query = $db->getQuery(true);
        $query
            ->select('*')
            ->from('#__categories AS c, #__dropfiles AS d')
            ->where($db->quoteName('c.id') . ' = ' . $db->quoteName('d.id') . ' AND d.type=' . $db->quote('dropbox'));

        $db->setQuery($query);
        $results = $db->loadObjectList();

        return $results;
    }

    /**
     * Get all OneDrive categories
     *
     * @return mixed
     * @since  version
     */
    public function getAllOneDriveCat()
    {
        // Get a db connection.
        $db = JFactory::getDbo();

        $query = $db->getQuery(true);
        $query
            ->select('*')
            ->from('#__categories AS c, #__dropfiles AS d')
            ->where($db->quoteName('c.id') . ' = ' . $db->quoteName('d.id') . ' AND d.type=' . $db->quote('onedrive'));

        $db->setQuery($query);
        $results = $db->loadObjectList();

        return $results;
    }

    /**
     * Get id last on categories
     *
     * @return mixed
     * @since  version
     */
    public function getIdLastOnCategories()
    {
        // Get a db connection.
        $db = $this->getDbo();

        $query = $db->getQuery(true);
        $query
            ->select('id')
            ->from('#__categories AS c')
            ->where('extension="com_dropfiles"')
            ->order('created_time DESC, id DESC');

        $db->setQuery($query);
        $results = $db->loadResult();
        return $results;
    }

    /**
     * Get all google drive categories
     *
     * @return mixed
     * @since  version
     */
    public function arrayCloudIdDropfiles()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select('cloud_id')
            ->from($db->quoteName('#__dropfiles'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('googledrive'));
        $db->setQuery($query);
        $result = $db->loadColumn();

        return $result;
    }

    /**
     * List OneDrive on dropfiles
     *
     * @return mixed
     * @since  version
     */
    public function arrayOneDriveIdDropfiles()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select('cloud_id')
            ->from($db->quoteName('#__dropfiles'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('onedrive'));
        $db->setQuery($query);
        $result = $db->loadColumn();

        return $result;
    }

    /**
     * Get one cat by cloudId
     *
     * @param string $cloud_id Cloud id
     *
     * @return mixed
     * @since  version
     */
    public function getOneCatByCloudId($cloud_id)
    {
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select('*')
            ->from('#__categories AS c, #__dropfiles AS d')
            ->where($db->quoteName('c.id') . ' = ' . $db->quoteName('d.id')
                . ' AND BINARY d.cloud_id=' . $db->quote($cloud_id));

        $db->setQuery($query);
        $results = $db->loadObject();
        return $results;
    }

    /**
     * Create Dropbox on Dropfiles
     *
     * @param integer $id       Id
     * @param string  $type     Type
     * @param string  $cloud_id Cloud id
     * @param string  $path     Path
     *
     * @return void
     * @since  version
     */
    public function createDropboxOnDropfiles($id, $type, $cloud_id, $path)
    {
        // Get a db connection.
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        // Insert columns.
        $columns = array('id', 'type', 'cloud_id', 'path');
        // Insert values.
        $values = array($db->quote($id), $db->quote($type), $db->quote($cloud_id), $db->quote($path));
        $query->insert($db->quoteName('#__dropfiles'))
            ->columns($db->quoteName($columns))
            ->values(implode(',', $values));
        $db->setQuery($query);
        $db->execute();
    }

    /**
     * One cat by local id
     *
     * @param integer $local_id Local category id
     *
     * @return mixed
     * @since  version
     */
    public function getOneCatByLocalId($local_id)
    {
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select('*')
            ->from('#__categories AS c, #__dropfiles AS d')
            ->where($db->quoteName('c.id') . ' = ' . $db->quoteName('d.id') . ' AND c.id=' . $db->quote($local_id));

        $db->setQuery($query);
        $results = $db->loadObject();

        return $results;
    }

    /**
     * Update path Dropbox by Id item
     *
     * @param integer $id   Dropfile item id
     * @param string  $path Dropbox path
     *
     * @return mixed
     * @since  version
     */
    public function updatePathDropboxById($id, $path)
    {
        // Get a db connection.
        $db = $this->getDbo();
        $query = 'UPDATE #__dropfiles SET path=' . $db->quote($path) . ' WHERE id=' . $db->quote($id);
        $db->setQuery($query);

        return $db->execute();
    }

    /**
     * Create category On Dropfiles
     *
     * @param integer $id       Category id
     * @param string  $type     Category type
     * @param integer $cloud_id Cloud id
     *
     * @return void
     * @since  version
     */
    public function createOnDropfiles($id, $type, $cloud_id)
    {
        // Get a db connection.
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        // Insert columns.
        $columns = array('id', 'type', 'cloud_id');
        // Insert values.
        $values = array($id, $db->quote($type), $db->quote($cloud_id));
        $query->insert($db->quoteName('#__dropfiles'))
            ->columns($db->quoteName($columns))
            ->values(implode(',', $values));
        $db->setQuery($query);
        $db->execute();
    }

    /**
     * Method to create On Categories
     *
     * @param string  $title     Category title
     * @param integer $parent_id Category parent
     * @param integer $level     Category level
     *
     * @return boolean|mixed
     * @since  version
     */
    public function createOnCategories($title, $parent_id, $level)
    {
        $basePath = JPATH_ADMINISTRATOR . '/components/com_categories';
        require_once $basePath . '/models/category.php';
        $config = array('table_path' => $basePath . '/tables');
        $catmodel = new CategoriesModelCategory($config);

        $catData = array(
            'id' => 0, 'parent_id' => $parent_id, 'level' => $level, 'extension' => 'com_dropfiles',
            'title' => $title, 'alias' => JString::increment($title) . '-' . uniqid(),
            'published' => 1, 'language' => '*', 'associations' => array()
        );
        $result = $catmodel->save($catData);
        if ($result) {
            $newCatId = $this->getIdLastOnCategories();
            return $newCatId;
        }
        return $result;
    }

    /**
     *  Method to delete on Dropfiles
     *
     * @param integer $id Category id
     *
     * @return mixed
     * @since  version
     */
    public function deleteOnDropfiles($id)
    {
        // Get a db connection.
        $db = $this->getDbo();
        $query = 'DELETE From #__dropfiles WHERE BINARY `cloud_id` = ' . $db->quote($id);
        $db->setQuery($query);

        return $db->execute();
    }

    /**
     * Method to delete on Categories
     *
     * @param integer $id Category id
     *
     * @return mixed
     * @since  version
     */
    public function deleteOnCategories($id)
    {
        // Get a db connection.
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $conditions = array(
            $db->quoteName('id') . ' = ' . $db->quote($id)
        );
        $query->delete($db->quoteName('#__categories'));
        $query->where($conditions);
        $db->setQuery($query);
        return $db->execute();
    }

    /**
     * Delete categories recursive
     *
     * @param null   $cid  Category id
     * @param string $type Category type
     *
     * @return boolean
     * @since  4.1.5
     */
    public function deleteCategoriesRecursive($cid = null, $type = '')
    {
        if (is_null($cid) || $type === '') {
            return false;
        }

        if ($cid) {
            $dbo = $this->getDbo();
            $folderToDelete = array();
            $cloudFolderToDelete = array();
            // Get the model.
            $children = $this->getListCategories($cid);
            // Remove parents
            $localParent = $this->getOneCatByLocalId($cid);

            $folderToDelete[] = $cid;
            $cloudFolderToDelete[] = $dbo->quote($localParent->cloud_id);

            if (!empty($children)) {
                foreach ($children as $child) {
                    // Remove parents
                    $childCat = $this->getOneCatByLocalId($child->id);
                    $folderToDelete[] = $cid;
                    $cloudFolderToDelete[] = $dbo->quote($childCat->cloud_id);
                }
            }

            $query = 'DELETE FROM #__categories WHERE `id` IN (' . implode(',', $folderToDelete) . ')';
            $dbo->setQuery($query);
            $dbo->execute();

            $query = 'DELETE FROM #__dropfiles WHERE BINARY `cloud_id` IN (' . implode(',', $cloudFolderToDelete) . ')';
            $dbo->setQuery($query);
            $dbo->execute();

            $query = 'DELETE FROM #__dropfiles_google_files WHERE catid IN (' . implode(',', $cloudFolderToDelete) . ')';
            $dbo->setQuery($query);
            $dbo->execute();
        }
    }

    /**
     * Get list categories
     *
     * @param integer $catId Category id
     *
     * @return \Joomla\CMS\Categories\CategoryNode[]
     * @since  4.1.5
     */
    public function getListCategories($catId)
    {
        $categories = JCategories::getInstance('dropfiles');
        $cat = $categories->get($catId);
        $children = $cat->getChildren(true);

        return $children;
    }

    /**
     * Method to update categories title
     *
     * @param integer $id    Category id
     * @param string  $title Category title
     *
     * @return mixed
     * @since  version
     */
    public function updateTitleById($id, $title)
    {
        // Get a db connection.
        $db = $this->getDbo();
        $query = 'UPDATE #__categories SET title=' . $db->quote($title) . ' WHERE id=' . $db->quote($id);
        $db->setQuery($query);
        return $db->execute();
    }

    /**
     * Method to get all tag files
     *
     * @return array
     * @since  version
     */
    public function getAllTagsFiles()
    {
        $dbo = $this->getDbo();
        $query = "SELECT catid, GROUP_CONCAT(file_tags SEPARATOR ',') as cat_tags FROM #__dropfiles_files ";
        $query .= " WHERE file_tags !='' GROUP BY catid";
        $dbo->setQuery($query);
        $list_tags = $dbo->loadAssocList('catid');

        $catTags = array();
        foreach ($list_tags as $key => $value) {
            $allTags1 = explode(',', $value['cat_tags']);
            $allTags2 = array_values(array_unique($allTags1));
            $catTags[$key] = $allTags2;
        }
        $params = JComponentHelper::getParams('com_dropfiles');
        //tags of GoogleDrive files
        if ($params->get('google_credentials', '')) {
            $query = "SELECT catid, GROUP_CONCAT(file_tags SEPARATOR ',') as cat_tags FROM #__dropfiles_google_files";
            $query .= " WHERE file_tags !='' GROUP BY catid";
            $dbo->setQuery($query);
            $list_tags = $dbo->loadAssocList('catid');

            $gCatTags = array();
            foreach ($list_tags as $key => $value) {
                $allTags1 = explode(',', $value['cat_tags']);
                $allTags2 = array_values(array_unique($allTags1));
                $gCatTags[$key] = $allTags2;
            }

            if (!empty($gCatTags)) {
                $catTags = array_merge($catTags, $gCatTags);
            }
        }
        //tags of Dropbox files
        if ($params->get('dropbox_token') !== '') {
            $query = "SELECT catid, GROUP_CONCAT(file_tags SEPARATOR ',') as cat_tags FROM #__dropfiles_dropbox_files";
            $query .= " WHERE file_tags !='' GROUP BY catid";
            $dbo->setQuery($query);
            $list_tags = $dbo->loadAssocList('catid');

            $dCatTags = array();
            foreach ($list_tags as $key => $value) {
                $allTags1 = explode(',', $value['cat_tags']);
                $allTags2 = array_values(array_unique($allTags1));
                $dCatTags[$key] = $allTags2;
            }
            if (!empty($dCatTags)) {
                $catTags = array_merge($catTags, $dCatTags);
            }
        }
        return $catTags;
    }


    /**
     * Extract categories for the user having own category permission
     *
     * @param array $listCate List categories
     *
     * @return array|mixed
     * @since  version
     */
    public function extractOwnCategories($listCate)
    {
        $canDo = DropfilesHelper::getActions();
        $is_edit_all = false;
        $user_cate_parent_id = array();
        $user_cate_id = array();
        if ($canDo->get('core.edit')) {
            // Allows edit all categories
            $is_edit_all = true;
        } else {
            foreach ($listCate as $key => $val) {
                if (isset($val->parent_id) && $val->parent_id !== 1) {
                    $user_cate_parent_id[] = $val->parent_id;
                }
                $user_cate_id[] = $val->id;
            }
        }

        $items = $this->getAllCategories();

        if (!empty($items) && !$is_edit_all) {
            $parent = $user_cate_parent_id;
            $visible_categories = $user_cate_parent_id;
            while (!empty($parent)) {
                $visible_categories = array_unique(array_merge($parent, $visible_categories), SORT_REGULAR);
                $parent = $this->getParentIds($items, $parent);
            }
            foreach ($items as $key_cat => $cat) {
                if (!in_array($cat->id, $visible_categories)) {
                    unset($items[$key_cat]);
                } elseif (in_array($cat->id, $user_cate_id)) {
                    unset($items[$key_cat]);
                } else {
                    $items[$key_cat]->disable = true;
                }
            }
            //reset index array
            $items = array_values($items);
            $items = array_merge($items, $listCate);
        } else {
            $items = $listCate;
        }

        return $items;
    }

    /**
     * Get all parent id
     *
     * @param array $items           Items
     * @param array $user_categories User category
     *
     * @return array
     * @since  version
     */
    public function getParentIds($items, $user_categories)
    {
        $parent = array();
        foreach ($items as $key_cat => $cat) {
            if (in_array($cat->id, $user_categories)) {
                if ($cat->parent_id && !in_array($cat->parent_id, $parent)) {
                    $parent[] = $cat->parent_id;
                }
            }
        }

        return $parent;
    }

    /**
     * Method to get all parents category
     *
     * @param integer $catid      Category id
     * @param integer $displaycat Display category
     *
     * @return array
     * @since  version
     */
    public function getParentsCat($catid, $displaycat)
    {
        $results = array();
        $results[] = $catid;
        $this->getParentCat($catid, $results, $displaycat);
        return $results;
    }

    /**
     * Method to get parents category
     *
     * @param integer $catid      Category id
     * @param array   $results    Return result
     * @param integer $displaycat Display category
     *
     * @return void
     * @since  version
     */
    public function getParentCat($catid, &$results, $displaycat)
    {
        if ((int) $catid !== 0) {
            $cat = $this->getOneCatByLocalId($catid);

            if (!is_null($cat) && $cat->parent_id > 1 && $cat->parent_id !== $displaycat) {
                $results[] = (int) $cat->parent_id;
                $this->getParentCat($cat->parent_id, $results, $displaycat);
            }
        }
    }
}
