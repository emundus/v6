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
 * @since     1.6
 */

defined('_JEXEC') || die;

jimport('joomla.application.component.modeladmin');

/**
 * Class DropfilesModelCategory
 */
class DropfilesModelCategory extends JModelAdmin
{

    /**
     * Method to get the row form.
     *
     * @param array   $data     Data for the form.
     * @param boolean $loadData True if the form is to load its own data (default case), false if not.
     *
     * @return JForm|boolean  A JForm object on success, false on failure
     *
     * @since 1.6
     */
    public function getForm($data = array(), $loadData = true)
    {

        $jinput = JFactory::getApplication()->input;
        $extension = 'com_dropfiles';
        $this->setState('category.extension', $extension);
        // Get the form.
        $form = $this->loadForm('com_dropfiles.category' . $extension, 'category', array('control' => 'jform', 'load_data' => $loadData));

        if (empty($form)) {
            return false;
        }

        // Modify the form based on Edit State access controls.
        if (empty($data['extension'])) {
            $data['extension'] = $extension;
        }

        $categoryId = $jinput->get('id');
        $parts = explode('.', $extension);
        $assetKey = $categoryId ? $extension . '.category.' . $categoryId : $parts[0];

        if (!JFactory::getUser()->authorise('core.edit.state', $assetKey)) {
            // Disable fields for display.
            $form->setFieldAttribute('ordering', 'disabled', 'true');
            $form->setFieldAttribute('published', 'disabled', 'true');

            // Disable fields while saving.
            // The controller has already verified this is a record you can edit.
            $form->setFieldAttribute('ordering', 'filter', 'unset');
            $form->setFieldAttribute('published', 'filter', 'unset');
        }

        return $form;
    }

    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param string $type   Table type
     * @param string $prefix Table prefix
     * @param array  $config Table config
     *
     * @return mixed
     * @since  version
     */
    public function getTable($type = 'Category', $prefix = 'DropfilesTable', $config = array())
    {
        $table = JTable::getInstance($type, $prefix, $config);
        // set child of system root category
        $table->setLocation(1, 'last-child');
        return $table;
    }


    /**
     * Get category file
     *
     * @param null|integer $idcat Category id
     *
     * @return null
     * @throws Exception Throw when application can not start
     * @since  version
     */
    public function getCategory($idcat = null)
    {
        if ($idcat === null) {
            $app = JFactory::getApplication();
            $idcat = $app->input->getInt('id', 0);
        }
        $user = JFactory::getUser();
        $db = $this->getDbo();
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


    /**
     * Get category file
     *
     * @param integer $idcat Dropfile id
     *
     * @return mixed
     * @since  version
     */
    public function getDropfileCategory($idcat)
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select('a.*');
        $query->from('#__dropfiles as a');
        $query->where('a.id=' . (int)$idcat);
        $db->setQuery($query);
        if ($db->execute()) {
            return $db->loadObject();
        }

        return null;
    }

    /**
     * Get the current theme from a category id
     *
     * @param integer $id Dropfile id
     *
     * @return boolean
     * @since  version
     */
    public function getCategoryTheme($id)
    {
        $dbo = $this->getDbo();
        $query = 'SELECT theme FROM #__dropfiles WHERE id=' . $dbo->quote($id);
        $dbo->setQuery($query);
        if ($dbo->execute()) {
            $theme = $dbo->loadResult();
            if (empty($theme)) {
                $theme = 'default';
            }

            return $theme;
        }

        return false;
    }

    /**
     * Get the params from a category id
     *
     * @param integer $id Dropfile id
     *
     * @return boolean
     * @since  version
     */
    public function getCategoryParams($id)
    {
        $dbo = $this->getDbo();
        $query = 'SELECT params FROM #__dropfiles WHERE id=' . $dbo->quote($id);
        $dbo->setQuery($query);
        if ($dbo->execute()) {
            return json_decode($dbo->loadResult());
        }

        return false;
    }

    /**
     * Set the title of a category
     *
     * @param integer $id_category Category id
     * @param string  $title       Category title
     *
     * @return integer
     * @since  version
     */
    public function setTitle($id_category, $title)
    {
        JModelLegacy::addIncludePath(JPATH_ROOT . '/administrator/components/com_dropfiles/models/', 'DropfilesModel');
        $modelOneDriveBusinessCategory = JModelLegacy::getInstance('OnedriveBusinessCategory', 'dropfilesModel');
        $dbo = $this->getDbo();
        if ($title === '') {
            return false;
        }
        $filter = JFilterInput::getInstance();
        $title = $filter->clean($title);

        $table = $this->getTable();
        if (!$table->load($id_category)) {
            return false;
        }
        if (!$table->bind(array('title' => $title))) {
            return false;
        }
        if (!$table->store()) {
            return false;
        }
        $query = 'SELECT * FROM #__dropfiles WHERE id=' . (int)$id_category;
        $dbo->setQuery($query);
        $dbo->execute();
        $cat = $dbo->loadObject();

        if ($cat->type === 'googledrive') {
            $google = new DropfilesGoogle();
            $google->changeFilename($cat->cloud_id, $title);
        } elseif ($cat->type === 'dropbox') {
            $dropbox = new DropfilesDropbox();

            $f = pathinfo($cat->path);

            if (strlen($f['dirname']) === 1) {
                $f['dirname'] = '/';
                $ntitle = $f['dirname'] . $title;
            } else {
                $ntitle = $f['dirname'] . '/' . $title;
            }
            $this->setDropboxpath($id_category, $ntitle);
            $dropbox->changeDropboxFilename($cat->path, $ntitle);
        } elseif ($cat->type === 'onedrive') {
            $onedrive = new DropfilesOneDrive();
            $onedrive->changeFilename($cat->cloud_id, $title);
        } elseif ($cat->type === 'onedrivebusiness') {
            $modelOneDriveBusinessCategory->changeCategoryName($cat->cloud_id, $title);
        }

        return true;
    }


    /**
     * Set the theme of a category
     *
     * @param integer $id_category Category id
     * @param string  $path        Path
     *
     * @return boolean
     * @since  version
     */
    public function setDropboxpath($id_category, $path)
    {
        $dbo = $this->getDbo();
        if ($path === '') {
            return false;
        }
        $query = 'UPDATE #__dropfiles SET path=' . $dbo->quote($path) . ' WHERE id=' . $dbo->quote($id_category);
        $dbo->setQuery($query);
        if ($dbo->execute()) {
            return true;
        }

        return false;
    }

    /**
     * Set the theme of a category
     *
     * @param integer $id_category Category id
     * @param string  $theme       Theme name
     *
     * @return integer
     * @since  version
     */
    public function setTheme($id_category, $theme)
    {
        $dbo = $this->getDbo();
        if ($theme === '') {
            return false;
        }
        $query = 'UPDATE #__dropfiles SET theme=' . $dbo->quote($theme) . ' WHERE id=' . $dbo->quote($id_category);
        $dbo->setQuery($query);
        if ($dbo->execute()) {
            return true;
        }

        return false;
    }

    /**
     * Method to get the record form.
     *
     * @param boolean $loadData Load data
     *
     * @return boolean
     * @throws Exception Throw when application can not start
     * @since  version
     */
    public function getFormParams($loadData = true)
    {
        $app = JFactory::getApplication();
        $id_category = $app->input->getInt('id', 0);
        if (!$id_category) {
            return false;
        }

        // Get the form.
        $arr_control = array('control' => 'jform', 'load_data' => $loadData);
        $form = $this->loadForm('com_dropfiles.categoryparams', 'categoryparams', $arr_control);
        $form->removeGroup('associations');

        //Get the theme
        $dbo = $this->getDbo();
        $query = 'SELECT theme,params FROM #__dropfiles WHERE id=' . (int)$id_category;
        $dbo->setQuery($query);
        if (!$dbo->execute()) {
            return false;
        }
        $category = $dbo->loadObject();

        // If type is already known we can load the plugin form
        if (isset($category->theme)) {
            JPluginHelper::importPlugin('dropfilesthemes');
            $app = JFactory::getApplication();
            $app->triggerEvent('onConfigForm', array($category->theme, &$form));
        }
        if (isset($loadData) && $loadData) {
            // Get the data for the form.
            $data = $this->loadFormData();
            if (isset($data->params->setTheme)) {
                unset($data->params->setTheme);
                $dropfiles_params = JComponentHelper::getParams('com_dropfiles');
                $defaultThemes = array('', 'default', 'ggd', 'tree', 'table');
                if (in_array($category->theme, $defaultThemes)) {
                    $str_params = '{';
                    foreach ($dropfiles_params as $k => $v) {
                        if (is_object($category) && $category->theme === '' || $category->theme === 'default') {
                            if (preg_match('/^default_/', $k)) {
                                $str_params .= '"' . ltrim($k, 'default_') . '":"' . $v . '",';
                            }
                        } else {
                            if (preg_match('/^' . $category->theme . '_/', $k)) {
                                $str_params .= '"' . $k . '":"' . $v . '",';
                            }
                        }
                    }
                    $str_params = rtrim($str_params, ',');
                    $str_params .= '}';
                    $data->params = json_decode($str_params);
                } else {
                    // Get params from plugins
                    $plugin = JPluginHelper::getPlugin('dropfilesthemes', strtolower($category->theme));
                    $themeParams = new JRegistry($plugin->params);
                    $data->params = $themeParams->toObject();
                }
                $data->params->access = -1; // Default value
                $data->access = -1;
            } else {
                // Inject access
                if (isset($data->params->access)) {
                    if (intval($data->params->access) === -1) {
                        $data->access = -1;
                    }
                }
            }
            $form->bind($data);
        }

        if (empty($form)) {
            return false;
        }
        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return mixed    The data for the form.
     * @since  1.6
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = $this->getItem();

        //replace params with dropfiles parmas
        $modelConfig = $this->getInstance('config', 'dropfilesModel');
        $data->params = $modelConfig->getParams($data->id);

        return $data;
    }

    /**
     * Method to test whether a record can be deleted.
     *
     * @param object $record A record object.
     *
     * @return boolean  True if allowed to delete the record. Defaults to the permission set in the component.
     *
     * @since 1.6
     */
    protected function canDelete($record)
    {
        $authorise = null;
        if (!empty($record->id)) {
            $user = JFactory::getUser();
            $authorise = $user->authorise('core.delete', $record->extension . '.category.' . (int)$record->id);
        }
        return $authorise;
    }

    /**
     * Method to delete items
     *
     * @param array $pks Pks
     *
     * @return boolean
     * @since  version
     */
    public function delete(&$pks)
    {
        if (parent::delete($pks)) {
            foreach ($pks as $i => $pk) {
                $pks[$i] = (int)$pk;
            }
            $dbo = $this->getDbo();

            // Delete index
            $query = $dbo->getQuery(true);
            $query->select($dbo->quoteName(array('id', 'catid')))
                ->from($dbo->quoteName('#__dropfiles_files'))
                ->where($dbo->quoteName('catid') . ' IN (' . implode(',', $pks) . ')');
            $dbo->setQuery($query);
            $filesId = $dbo->loadObjectList();

            foreach ($filesId as $fileId) {
                $this->removeIndexRecordForPost($fileId->id);
            }
            //Delete files under category

            $query = 'DELETE FROM #__dropfiles_files WHERE catid IN (' . implode(',', $pks) . ')';
            $dbo->setQuery($query);
            if (!$dbo->execute()) {
                return false;
            }
            $query = 'DELETE FROM #__dropfiles WHERE id IN (' . implode(',', $pks) . ')';
            $dbo->setQuery($query);
            if (!$dbo->execute()) {
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * Remove index record for file
     *
     * @param integer $fileId File id
     *
     * @return boolean
     * @since  version
     */
    public function removeIndexRecordForPost($fileId)
    {
        $db = JFactory::getDbo();
        $tables = $db->getTableList();
        if (!in_array($db->getPrefix() . 'dropfiles_fts_index', $tables)) {
            return false;
        }
        $prefix = '#__dropfiles_fts_';
        $q = 'select `id` from `' . $prefix . 'index` where (`tid` = "';
        $q .= addslashes($fileId) . '") and (`tsrc` = "df_files")';
        $db->setQuery($q);
        $indexResults = $db->loadObjectList();

        if (isset($indexResults[0])) {
            $q = 'select `id` from `' . $prefix;
            $q .= 'docs` where `index_id` in (' . implode(',', $this->getColumn($indexResults, 'id')) . ')';
            $db->setQuery($q);
            $docResults = $db->loadObjectList();

            if (isset($res_docs[0])) {
                $q = 'delete from `' . $prefix . 'vectors` where `did` in (';
                $q .= implode(',', $this->getColumn($docResults, 'id')) . ')';
                $db->setQuery($q);
                $db->execute();

                $q = 'delete from `' . $prefix . 'docs` where `index_id` in (';
                $q .= implode(',', $this->getColumn($indexResults, 'id')) . ')';
                $db->setQuery($q);
                $db->execute();
            }

            $q = 'delete from `' . $prefix;
            $q .= 'index` where (`tid` = "' . addslashes($fileId) . '") and (`tsrc` = "df_files")';
            $db->setQuery($q);
            $db->execute();
        }

        return true;
    }

    /**
     * Get columns
     *
     * @param array  $a   Document results
     * @param string $col Column
     *
     * @return array
     * @since  version
     */
    public function getColumn($a, $col)
    {
        $r = array();
        foreach ($a as $d) {
            if (isset($d->{$col})) {
                $r[] = $d->{$col};
            }
        }

        return $r;
    }

    /**
     * Method to delete category dropbox
     *
     * @param string $cloud_id Cloud id
     *
     * @return boolean
     * @since  version
     */
    public function deleteCatDropboxFiles($cloud_id)
    {
        //Delete files under category
        $dbo = $this->getDbo();
        $query = 'DELETE FROM #__dropfiles_dropbox_files WHERE catid=' . $dbo->quote($cloud_id) . '';
        $dbo->setQuery($query);
        if (!$dbo->execute()) {
            return false;
        }

        return true;
    }

    /**
     * Method delete category in OneDrive
     *
     * @param string $cloud_id Cloud id
     *
     * @return boolean
     * @since  version
     */
    public function deleteCatOneDriveFiles($cloud_id)
    {
        //Delete files under category
        $dbo = $this->getDbo();
        $query = 'DELETE FROM #__dropfiles_onedrive_files WHERE catid=' . $dbo->quote($cloud_id) . '';
        $dbo->setQuery($query);
        if (!$dbo->execute()) {
            return false;
        }

        return true;
    }

    /**
     * Method delete category in OneDrive Business
     *
     * @param string $cloud_id Cloud id
     *
     * @return boolean
     * @since  version
     */
    public function deleteCatOneDriveBusinessFiles($cloud_id)
    {
        //Delete files under category
        $dbo = $this->getDbo();
        $query = 'DELETE FROM #__dropfiles_onedrive_business_files WHERE catid=' . $dbo->quote($cloud_id) . '';
        $dbo->setQuery($query);
        if (!$dbo->execute()) {
            return false;
        }

        return true;
    }


    /**
     * There is no ckeckin in ajax
     *
     * @param array $pks Pks
     *
     * @return boolean
     * @since  version
     */
    public function checkin($pks = array())
    {
        return true;
    }

    /**
     * There is no checkout in ajax
     *
     * @param null $pk Pk
     *
     * @return boolean
     * @since  version
     */
    public function checkout($pk = null)
    {
        return true;
    }

    /**
     * Method create a Joomla category
     *
     * @param array $data Category data
     *
     * @return boolean
     * @throws Exception Throw when application can not start
     * @since  version
     */
    public function createJoomlaCategory($data)
    {
        $id = (!empty($data['id'])) ? $data['id'] : (int)$this->getState($this->getName() . '.id');
        if (parent::save($data) && !$id) {
            $savedId = (int)$this->getState($this->getName() . '.id');
            if ($data['parent_id']) {
                $table = $this->getTable();
                $table->moveByReference($data['parent_id'], 'first-child', $savedId);
            }
            return $savedId;
        }
        return false;
    }

    /**
     * Method save category
     *
     * @param array $data Category data
     *
     * @return boolean
     * @throws Exception Throw when application can not start
     * @since  version
     */
    public function save($data)
    {
        $id = (!empty($data['id'])) ? $data['id'] : (int)$this->getState($this->getName() . '.id');
        // var_dump($data) ; die();
        if (parent::save($data) && !$id) {
            //this is a new category
            $type = JFactory::getApplication()->input->get('type');
            $path = '';
            $params = JComponentHelper::getParams('com_dropfiles');
            switch ($type) {
                case 'googledrive':
                    $google = new DropfilesGoogle();
                    $cloud_id = $google->createFolder($data['title'], $params->get('google_base_folder'));
                    $cloud_id = $cloud_id->getId();
                    break;
                case 'dropbox':
                    $dropbox = new DropfilesDropbox();
                    $dropboxfolder = $dropbox->createDropFolder($data['title'] . '-' . time());
                    $cloud_id = $dropboxfolder['id'];
                    $path = $dropboxfolder['path_lower'];
                    break;
                case 'onedrive':
                    $onedrive = new DropfilesOneDrive();
                    $onedrivefolder = $onedrive->createFolder(
                        $data['title'] . '-' . time(),
                        $params->get('onedriveBaseFolderId')
                    );
                    $decoded = json_decode($onedrivefolder['responsebody'], true);
                    $newentry = new OneDrive_Service_Drive_Item($decoded);
                    $cloud_id = DropfilesCloudHelper::replaceIdOneDrive($newentry->getId());
                    break;
                case 'onedrivebusiness':
                    if (!class_exists('DropfilesCloudHelper')) {
                        JLoader::register('DropfilesCloudHelper', JPATH_ADMINISTRATOR . '/components/com_dropfiles/helpers/dropfilescloud.php');
                    }
                    $oneDriveBusiness = new DropfilesOneDriveBusiness();
                    $oneDriveConfigs = DropfilesCloudHelper::getAllOneDriveBusinessConfigs();
                    $oneBaseFolderId = $oneDriveConfigs['onedriveBusinessBaseFolder']->id;
                    /* @var \Krizalys\Onedrive\Proxy\DriveItemProxy $newFolder */
                    $newFolder = $oneDriveBusiness->createFolder($data['title'], $oneBaseFolderId);
                    $cloud_id  = null;
                    if (!empty($newFolder)) {
                        $oneDriveId = $newFolder->id;
                        $oneDriveName = $newFolder->name;
                        $cloud_id = $oneDriveId;
                    }
                    break;
                default:
                    $type = 'default';
                    $cloud_id = null;
                    break;
            }
            $id = (int)$this->getState($this->getName() . '.id');
            $dbo = $this->getDbo();
            $params = array();
            $params['setTheme'] = true;
            $params['ordering'] = 'ordering';
            $params['orderingdir'] = 'asc';

            $query = 'INSERT INTO #__dropfiles (id,type,cloud_id,path, params, theme) VALUES (' . (int)$id . ',';
            if ($cloud_id) {
                $query .= $dbo->quote($type) . ',' . $dbo->quote($cloud_id) . ',' . $dbo->quote($path) . ','. $dbo->quote(json_encode($params)). ',"")';
            } else {
                $query .= $dbo->quote($type) . ', NULL ,' . $dbo->quote($path) . ','. $dbo->quote(json_encode($params)). ',"")';
            }

            $dbo->setQuery($query);
            if (!$dbo->execute()) {
                return false;
            }

            return true;
        }

        return true;
    }

    /**
     * Save category by ID
     *
     * @param integer|object $id        If integer, data will be fetched from the database, or from the cache if
     *                                                 available. If stdClass object (as in the results of a database query), will apply
     *                                                 filters and return an object corresponding to the data.,
     *                                                 will return data.
     * @param integer        $fileId    File id
     * @param string         $catFileId Category File id
     *
     * @return boolean
     * @throws Exception Throw when error
     */
    public function saveRefToFiles($id, $fileId, $catFileId)
    {
        $dbo = $this->getDbo();
        $result = $this->getDropfileCategory($id);
        $params = json_decode($result->params, true);
        $listFileref = null;
        if (empty($params) || !isset($params['refToFile'])) {
            $params['refToFile'] = array();
        }
        $params = $this->checkListCatRef($params, $id);
        if (isset($params['refToFile'][$catFileId]) && !empty($params['refToFile'][$catFileId])) {
            $listFileref = $params['refToFile'][$catFileId];
        } else {
            $listFileref = array();
        }

        if (!in_array($fileId, $listFileref)) {
            array_push($listFileref, $fileId);
        }
        $params['refToFile'][$catFileId] = $listFileref;
        $params = json_encode($params);
        $query = 'UPDATE #__dropfiles SET params=' . $dbo->quote($params) . ' WHERE id=' . $dbo->quote($id);
        $dbo->setQuery($query);
        if ($dbo->execute()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check list category referent
     *
     * @param array          $params         Category params
     * @param integer|object $current_cat_id Current category id
     *
     * @return array
     * @throws Exception Throw when error
     */
    public function checkListCatRef($params, $current_cat_id)
    {
        $listFileref = $params['refToFile'];
        $params['refToFile'] = array();
        if (!empty($listFileref) && $listFileref) {
            foreach ($listFileref as $key => $lst) {
                $category = $this->getCategory($key);
                if ($category && !empty($category)) {
                    $lstFile = $this->checkListFiles($key, $lst, $current_cat_id);
                    $params['refToFile'][$key] = $lstFile;
                }
            }
        }

        return $params;
    }

    /**
     * Check list file referent category
     *
     * @param string         $catid          Category id
     * @param array          $listFileRef    List category ref
     * @param integer|object $current_cat_id Current category id
     *
     * @return array
     */
    public function checkListFiles($catid, $listFileRef, $current_cat_id)
    {
        $lstFile = array();
        JModelLegacy::addIncludePath(JPATH_ROOT . '/administrator/components/com_dropfiles/models/', 'DropfilesModel');
        $modelFiles = JModelLegacy::getInstance('Files', 'dropfilesModel');
        if (!empty($listFileRef) && $listFileRef) {
            foreach ($listFileRef as $key => $val) {
                $file = (array)$modelFiles->getFile($val);
                if ($file && !empty($file)) {
                    $file_multi_category = explode(',', $file['file_multi_category']);
                    unset($file_multi_category[count($file_multi_category) - 1]);
                    if ((int)$catid === (int)$file['catid']) {
                        if (in_array($current_cat_id, $file_multi_category)) {
                            $lstFile[] = $val;
                        }
                    }
                }
            }
        }
        return $lstFile;
    }

    /**
     * Delete config reference to files
     *
     * @param string|integer $id        Category id
     * @param integer        $fileId    File id
     * @param string|integer $catFileId Category file id
     *
     * @return boolean
     */
    public function deleteRefToFiles($id, $fileId, $catFileId)
    {
        $dbo = $this->getDbo();
        $result = $this->getCategoryParams($id);
        $params = (array)$result;
        $listFileref = null;
        $delFile = '';
        if (!empty($params) && isset($params['refToFile'])) {
            $listFileref = $params['refToFile'];
            foreach ($listFileref as $key => $value) {
                if ((int)$key === $catFileId) {
                    $lstfile = $value;
                    $delFile = array_search($fileId, $lstfile);
                    if ($delFile !== false) {
                        unset($lstfile[$delFile]);
                    }
                    $listFileref->$key = $lstfile;
                }
            }
            $params['refToFile'] = (object)$listFileref;
            $params = json_encode($params);
            $query = 'UPDATE #__dropfiles SET params=' . $dbo->quote($params) . ' WHERE id=' . $dbo->quote($id);
            $dbo->setQuery($query);
            if ($dbo->execute()) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * Check cloud category
     *
     * @param string|integer $idCategory Category id
     *
     * @return boolean
     */
    public function checkCloudCategory($idCategory)
    {
        $db     = $this->getDbo();
        $query  = $db->getQuery(true);
        $query->select('a.*');
        $query->from('#__dropfiles as a');
        $query->where('a.id=' . (int)$idCategory);
        $db->setQuery($query);
        $cloudType = array('googledrive', 'dropbox', 'onedrive', 'onedrivebusiness');
        if ($db->execute()) {
            $cate = $db->loadObject();
            if (isset($cate->type) && in_array($cate->type, $cloudType)) {
                return array('status' => true, 'type' => $cate->type);
            } else {
                return array('status' => false, 'type' => $cate->type);
            }
        } else {
            return array('status' => false, 'type' => '');
        }
    }
}
