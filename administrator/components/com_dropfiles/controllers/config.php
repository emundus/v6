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

// no direct access
defined('_JEXEC') || die;

jimport('joomla.application.component.controllerform');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

use Joomla\Utilities\ArrayHelper;

/**
 * Class DropfilesControllerConfig
 */
class DropfilesControllerConfig extends JControllerForm
{

    /**
     * Save param config
     *
     * @param null $key    Key
     * @param null $urlVar Url var
     *
     * @return boolean
     */
    public function save($key = null, $urlVar = null)
    {
        // Check for request forgeries.
        JSession::checkToken() || jexit(JText::_('JINVALID_TOKEN'));

        // Initialise variables.
        $app = JFactory::getApplication();
        $lang = JFactory::getLanguage();
        $model = $this->getModel();
        $data = $app->input->get('jform', array(), 'post', 'array');
        $context = $this->option . '.edit.' . $this->context;

        // Access check.
        $canDo = DropfilesHelper::getActions();
        if (!$canDo->get('core.edit')) {
            if ($canDo->get('core.edit.own')) {
                $category = $model->getItem(JFactory::getApplication()->input->getInt('id', 0));
                if ($category->created_user_id !== JFactory::getUser()->id) {
                    $this->exitStatus('not permitted');
                }
            } else {
                $this->exitStatus('not permitted');
            }
        }

        // Validate the posted data.
        // Sometimes the form needs some posted data, such as for plugins and modules.
        $form = $model->getForm($data, false);

        if (!$form) {
            $app->enqueueMessage($model->getError(), 'error');

            return false;
        }

        // Test whether the data is valid.
        $validData = $model->validate($form, $data);

        // Check for validation errors.
        if ($validData === false) {
            // Get the validation messages.
            $errors = $model->getErrors();

            // Push up to three validation messages out to the user.
            $errorCount = count($errors);
            for ($i = 0, $n = $errorCount; $i < $n && $i < 3; $i++) {
                if ($errors[$i] instanceof Exception) {
                    $app->enqueueMessage($errors[$i]->getMessage(), 'warning');
                } else {
                    $app->enqueueMessage($errors[$i], 'warning');
                }
            }

            // Save the data in the session.
            $app->setUserState($context . '.data', $data);

            // Redirect back to the edit screen.
            $url_redirect = 'index.php?option=' . $this->option . '&view=' . $this->view_item;
            $url_redirect .= $this->getRedirectToItemAppend(null, $urlVar);
            $this->setRedirect(JRoute::_($url_redirect, false));
            return false;
        }

        // Attempt to save the data.
        if (!$model->save($validData)) {
            // Save the data in the session.
            $app->setUserState($context . '.data', $validData);

            // Redirect back to the edit screen.
            $this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
            $this->setMessage($this->getError(), 'error');
            $url_redirect = 'index.php?option=' . $this->option . '&view=' . $this->view_item . '&tmpl=component';
            $url_redirect .= $this->getRedirectToItemAppend(null, $urlVar);
            $this->setRedirect(JRoute::_($url_redirect, false));
            return false;
        }
        $recordId = $app->input->getInt($urlVar);
        $text_prefix = $this->text_prefix . ($recordId === 0 && $app->isClient('site') ? '_SUBMIT' : '') . '_SAVE_SUCCESS';
        $this->setMessage(
            JText::_(
                ($lang->hasKey($text_prefix)
                    ? $this->text_prefix
                    : 'JLIB_APPLICATION') . ($recordId === 0 && $app->isClient('site') ? '_SUBMIT' : '') . '_SAVE_SUCCESS'
            )
        );

        $app->setUserState($context . '.data', null);

        // Redirect to the list screen.
        $url_redirect = 'index.php?option=' . $this->option . '&view=' . $this->view_item . '&tmpl=component';
        $url_redirect .= $this->getRedirectToListAppend();
        $this->setRedirect(JRoute::_($url_redirect, false));

        // Invoke the postSave method to allow for the child class to access the model.
        $this->postSaveHook($model, $validData);
        return true;
    }

    /**
     * Get redirect to item append
     *
     * @param null   $recordId Record id
     * @param string $urlVar   Url var
     *
     * @return string
     */
    public function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
    {
        $app = JFactory::getApplication();
        $append = parent::getRedirectToItemAppend($recordId, $urlVar);

        $format = $app->input->get('format', 'raw');

        // Setup redirect info.
        if ($format) {
            $append .= '&format=' . $format;
        }
        return $append;
    }

    /**
     * Set theme
     *
     * @return void
     * @throws \Exception Throw when application can not start
     * @since  1.0
     */
    public function setTheme()
    {
        $app = JFactory::getApplication();
        $theme = $app->input->get('theme');
        $id = $app->input->getInt('id');


        $canDo = DropfilesHelper::getActions();
        if (!$canDo->get('core.edit')) {
            if ($canDo->get('core.edit.own')) {
                $modelC = $this->getModel('category');
                $category = $modelC->getItem($id);
                if ($category->created_user_id !== JFactory::getUser()->id) {
                    $this->exitStatus('not permitted');
                }
            } else {
                $this->exitStatus('not permitted');
            }
        }

        $themesObj = DropfilesBase::getDropfilesThemes();
        $themes = array();
        foreach ($themesObj as $value) {
            $themes[] = $value['id'];
        }

        if (!in_array($theme, $themes)) {
            $theme = 'default';
        }

        $model   = $this->getModel();
        $params  = $model->getParams($id);
        $params  = (isset($params)) ? (array)$params : array();
        $keep = array('group', 'access', 'refToFile', 'ordering', 'orderingdir');
        foreach ($params as $k => $v) {
            if (!in_array($k, $keep)) {
                unset($params[$k]);
            }
        }
        if (!empty($params)) {
            $params['setTheme'] = true;
        }
        $refToFile = (!empty($params)) ? json_encode($params) : '';
        if ($model->setTheme($theme, $id, $refToFile)) {
            $result = true;
        } else {
            $result = false;
        }
        echo json_encode($result);
        JFactory::getApplication()->close();
    }

    /**
     * Return a json response
     *
     * @param boolean $status Status
     * @param array   $datas  Array of datas to return with the json string
     *
     * @return void
     * @throws \Exception Throw when application can not start
     * @since  1.0
     */
    private function exitStatus($status, $datas = array())
    {
        $response = array('response' => $status, 'datas' => $datas);
        echo json_encode($response);
        JFactory::getApplication()->close();
    }

    /**
     * Get Dropbox token
     *
     * @return void
     * @throws \Exception Throw when application can not start
     * @since  1.0
     */
    public function getDropboxToken()
    {
        $code = JFactory::getApplication()->input->get('authencode');

        $canDo = DropfilesHelper::getActions();

        if (!$canDo->get('core.edit')) {
            $this->exitStatus('not permitted');
        }

        $Dropbox = new DropfilesDropbox();


        $list = $Dropbox->convertAuthorizationCode($code);


        if ($list['accessToken']) {
            $Dropbox->saveCodeToken($code, $list['accessToken']);
        }
        echo json_encode(array());
        die;
    }

    /**
     * Logout Dropbox
     *
     * @return void
     * @since  1.0
     */
    public function logoutDropbox()
    {
        $canDo = DropfilesHelper::getActions();

        if (!$canDo->get('core.edit')) {
            $url_redirect = 'index.php?option=com_dropfiles&task=configuration.display';
            $this->setRedirect(JRoute::_($url_redirect, false), JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
            $this->redirect();
        }

        $Dropbox = new DropfilesDropbox();
        $Dropbox->logout();

        $this->setRedirect(JRoute::_('index.php?option=com_dropfiles&task=configuration.display', false));
        $this->redirect();
    }

    /**
     * Import jdownloads files from selected category
     *
     * @return void
     * @throws \Exception Throw when application can not start
     * @since  1.0
     */
    public function downimport()
    {
        $app = JFactory::getApplication();
        $path = JPATH_SITE . '/components/com_jdownloads/helpers/categories.php';
        if (is_file($path)) {
            include_once $path;
        }

        include_once JPATH_ADMINISTRATOR . '/components/com_dropfiles/controllers/category.php';

        $params = JComponentHelper::getParams('com_dropfiles');
        $allowedext_list = '7z,ace,bz2,dmg,gz,rar,tgz,zip,csv,doc,docx,html,key,keynote,odp,ods,odt,pages,pdf,pps,ppt,'
            . 'pptx,rtf,tex,txt,xls,xlsx,xml,bmp,exif,gif,ico,jpeg,jpg,png,psd,tif,tiff,aac,aif,aiff,alac,amr,au,cdda,'
            . 'flac,m3u,m4a,m4p, mid, mp3, mp4, mpa, ogg, pac, ra, wav, wma, 3gp,asf,avi,flv,m4v,mkv,mov,mpeg,mpg,'
            . 'rm,swf,vob,wmv';
        $allowed_ext = explode(',', $params->get('allowedext', $allowedext_list));
        foreach ($allowed_ext as $key => $value) {
            $allowed_ext[$key] = strtolower(trim($allowed_ext[$key]));
            if ($allowed_ext[$key] === '') {
                unset($allowed_ext[$key]);
            }
        }

        $id = JFactory::getApplication()->input->getInt('doccat');

        JTable::addIncludePath(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_jdownloads' . DS . 'tables');
        $path_admin_models = JPATH_ADMINISTRATOR . ' / components / com_jdownloads / models / ';
        JModelLegacy::addIncludePath($path_admin_models, 'jdownloadsModel');

        $categories = JDCategories::getInstance('jdownloads', '');
        $cat = $categories->get($id);

        if (is_object($cat)) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('c .*');
            $query->from('`#__jdownloads_categories` AS c');

            $rgt = $cat->rgt;
            $lft = $cat->lft;
            $query->where('c.lft >= ' . (int)$lft);
            $query->where('c.rgt <= ' . (int)$rgt);
            $query->order('c.lft ASC');

            $db->setQuery($query);
            $rows = $db->loadObjectList();

            $parent_id = 1;
            $mapping = array(); //jdownload cat => dropfiles cat
            if (count($rows)) {
                $catcontroller = new DropfilesControllerCategory();
                $app = JFactory::getApplication();
                $catpath = '';
                foreach ($rows as $category) {
                    $jdownload_parent = $category->parent_id;
                    if (isset($mapping[$jdownload_parent])) {
                        $parent_id = (int)$mapping[$jdownload_parent];
                    }
                    $db = JFactory::getDbo();
                    $query = $db->getQuery(true);

                    // Select the required fields from the table.
                    $query->select('a.file_id, a.file_title, a.file_alias, a.description, a.file_pic, '
                       . 'a.price,a.release, a.cat_id, a.size, a.date_added, a.publish_from, a.modified_date,'
                       . 'a.publish_to, a.use_timeframe,a.url_download, a.other_file_id, a.extern_file,'
                       . 'a.downloads,a.extern_site, a.notes,a.access, a.language, a.checked_out,'
                       . 'a.checked_out_time, a.ordering, a.featured,a.published, a.asset_id');
                    $query->from('`#__jdownloads_files` AS a');

                    // Join over the users for the checked out user.
                    $query->select('uc . name AS editor');
                    $query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

                    // Join over the files for other selected file
                    $query->select('f.url_download AS other_file_name, f.file_title AS other_download_title');
                    $query->join('LEFT', $db->quoteName('#__jdownloads_files') .
                        ' AS f ON f.file_id = a.other_file_id');

                    // Join over the language
                    $query->select('l.title AS language_title');
                    $query->join('LEFT', $db->quoteName('#__languages') . ' AS l ON l.lang_code = a.language');

                    // Join over the asset groups.
                    $query->select('ag.title AS access_level');
                    $query->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

                    // Join over the categories.
                    $query->select('c.title AS category_title, c.parent_id AS category_parent_id');
                    $query->join('LEFT', '#__jdownloads_categories AS c ON c.id = a.cat_id');

                    $query->where('a.published = 1');


                    $query->where('a.cat_id = ' . $category->id);
                    $db->setQuery($query);

                    $downloads = $db->loadObjectList();

                    $downloaddir = JPATH_ROOT . '/jdownloads';

                    $datas = array();
                    $datas['jform']['extension'] = 'com_dropfiles';
                    $datas['jform']['title'] = $category->title;
                    $datas['jform']['alias'] = $category->alias . '-' . date('dmY-h-m-s', time());
                    $datas['jform']['parent_id'] = $parent_id;
                    $datas['jform']['published'] = 1;
                    $datas['jform']['language'] = '*';
                    $datas['jform']['metadata']['tags'] = '';

                    //Set state value to retreive the correct table
                    $model = $this->getModel('category');

                    $model->setState('category.extension', 'category');

                    foreach ($datas as $data => $val) {
                        $app->input->set($data, $val, 'POST');
                    }

                    if ($catcontroller->save()) {
                        $newId = $catcontroller->savedId;
                        $mapping[$category->id] = $newId;
                        $parent_id = $newId;

                        $file_dir = DropfilesBase::getFilesPath($newId);
                        if (!file_exists($file_dir)) {
                            JFolder::create($file_dir);
                            $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                            JFile::write($file_dir . 'index.html', $data);
                            $data = 'deny from all';
                            JFile::write($file_dir . '.htaccess', $data);
                        }

                        $modelFiles = $this->getModel('files');
                        $user = JFactory::getUser();

                        $catpath = ($category->cat_dir_parent)? $category->cat_dir_parent. '/' . $category->cat_dir : $category->cat_dir ;
                        foreach ($downloads as $download) {
                            $publish = ($download->publish_from === '0000-00-00 00:00:00') ? $download->date_added : $download->publish_from;
                            $created_time = $download->date_added;
                            $modified_time = ($download->modified_date === '0000-00-00 00:00:00') ? $download->date_added : $download->modified_date;
                            if ($download->url_download !== '') {
                                if (!in_array(strtolower(JFile::getExt($download->url_download)), $allowed_ext)) {
                                    continue;
                                }

                                $newname = uniqid() . '.' . strtolower(JFile::getExt($download->url_download));

                                $downloadfile = $downloaddir . '/' . $catpath . '/' . $download->url_download;

                                if (file_exists($downloadfile)) {
                                    JFile::copy($downloadfile, $file_dir . $newname);
                                }

                                //Insert new image into database
                                $id_file = $modelFiles->addFile(array(
                                    'title' => $download->file_title,
                                    'description' => $download->description,
                                    'id_category' => $newId,
                                    'file' => $newname,
                                    'ext' => strtolower(JFile::getExt($download->url_download)),
                                    'size' => filesize($file_dir . $newname),
                                    'author' => $user->get('id'),
                                    'created_time' => $created_time,
                                    'modified_time' => $modified_time,
                                    'publish' => $publish
                                ));
                                if (!$id_file) {
                                    JFile::delete($file_dir . $newname);
                                }
                            } else {
                                $urlfile = pathinfo($download->extern_file);
                                $modelFiles->addFile(array(
                                    'title' => $download->file_title,
                                    'description' => $download->description,
                                    'id_category' => $newId,
                                    'file' => $download->extern_file,
                                    'ext' => $urlfile['extension'],
                                    'size' => $this->remoteFileSize($download->extern_file),
                                    'author' => $user->get('id'),
                                    'created_time' => $created_time,
                                    'modified_time' => $modified_time,
                                    'publish' => $publish
                                ));
                            }
                        }
                    }
                }
            }
        }

        $this->exitStatus('Done');
    }

    /**
     *  Import Files from eDocman
     *
     * @return void
     * @throws \Exception Throw when application can not start
     * @since  1.0
     */
    public function eDocImport()
    {
        $app = JFactory::getApplication();
        include_once JPATH_ADMINISTRATOR . '/components/com_dropfiles/controllers/category.php';

        $params = JComponentHelper::getParams('com_dropfiles');
        $allowedext_list = '7z,ace,bz2,dmg,gz,rar,tgz,zip,csv,doc,docx,html,key,keynote,odp,ods,odt,pages,pdf,pps,ppt,'
            . 'pptx,rtf,tex,txt,xls,xlsx,xml,bmp,exif,gif,ico,jpeg,jpg,png,psd,tif,tiff,aac,aif,aiff,alac,amr,au,cdda,'
            . 'flac,m3u,m4a,m4p, mid, mp3, mp4, mpa, ogg, pac, ra, wav, wma, 3gp,asf,avi,flv,m4v,mkv,mov,mpeg,mpg,'
            . 'rm,swf,vob,wmv';
        $allowed_ext = explode(',', $params->get('allowedext', $allowedext_list));
        foreach ($allowed_ext as $key => $value) {
            $allowed_ext[$key] = strtolower(trim($allowed_ext[$key]));
            if ($allowed_ext[$key] === '') {
                unset($allowed_ext[$key]);
            }
        }

        $id = JFactory::getApplication()->input->getInt('doccat');

        require_once JPATH_ADMINISTRATOR . '/components/com_edocman/libraries/rad/loader.php';
        JLoader::register('EDocmanModelList', JPATH_ROOT . '/components/com_edocman/model/list.php');

        $cats = EDocmanHelper::getChildrenCategories($id);
        if (count($cats)) {
            $parent_id = 1;
            $mapping = array(); //edoc cat => dropfiles cat
            $modelList = OSModel::getInstance('List', 'EDocmanModel', array('ignore_session' => true,
                'ignore_request' => true));
            $catcontroller = new DropfilesControllerCategory();
            $modelFiles = $this->getModel('files');
            $user = JFactory::getUser();
            $edocmandir = JPATH_ROOT . '/edocman/';

            foreach ($cats as $cat) {
                $cat = (int)$cat;
                $category = EDocmanHelper::getCategory($cat);
                $edoc_parent = $category->parent_id;
                if (isset($mapping[$edoc_parent])) {
                    $parent_id = (int)$mapping[$edoc_parent];
                }

                $datas = array();
                $datas['jform']['extension'] = 'com_dropfiles';
                $datas['jform']['title'] = $category->title;
                $datas['jform']['alias'] = $category->alias . '-' . date('dmY-h-m-s', time());
                $datas['jform']['parent_id'] = $parent_id;
                $datas['jform']['published'] = 1;
                $datas['jform']['language'] = '*';
                $datas['jform']['metadata']['tags'] = '';

                //Set state value to retreive the correct table
                $model = $this->getModel('category');
                $model->setState('category.extension', 'category');
                foreach ($datas as $data => $val) {
                    $app->input->set($data, $val, 'POST');
                }

                if ($catcontroller->save()) {
                    $newId = $catcontroller->savedId;
                    $mapping[$cat] = $newId;
                    $parent_id = $newId;

                    $file_dir = DropfilesBase::getFilesPath($newId);
                    if (!file_exists($file_dir)) {
                        JFolder::create($file_dir);
                        $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                        JFile::write($file_dir . 'index.html', $data);
                        $data = 'deny from all';
                        JFile::write($file_dir . '.htaccess', $data);
                    }

                    $docs = $modelList
                        ->limitstart(0)
                        ->limit(0)
                        ->id($cat)
                        ->getData();

                    foreach ($docs as $document) {
                        if ($document->document_url !== '') {
                            $urlfile = pathinfo($document->document_url);
                            $des = $document->description;
                            if ($document->short_description !== '') {
                                $des = $document->short_description;
                            }
                            $modelFiles->addFile(array(
                                'title' => $document->title,
                                'description' => $des,
                                'id_category' => $newId,
                                'file' => $document->document_url,
                                'ext' => $urlfile['extension'],
                                'size' => $this->remoteFileSize($document->document_url),
                                'author' => $user->get('id')
                            ));
                        } else {
                            if (!in_array(strtolower(JFile::getExt($document->filename)), $allowed_ext)) {
                                continue;
                            }

                            $newname = uniqid() . '.' . strtolower(JFile::getExt($document->filename));

                            $docmanfile = $edocmandir . $document->filename;
                            if (file_exists($docmanfile)) {
                                JFile::copy($docmanfile, $file_dir . $newname);
                            }
                            //Insert new image into databse
                            $des = $document->description;
                            if ($document->short_description !== '') {
                                $des = $document->short_description;
                            }
                            $id_file = $modelFiles->addFile(array(
                                'title' => $document->title,
                                'description' => $des,
                                'id_category' => $newId,
                                'file' => $newname,
                                'ext' => strtolower(JFile::getExt($document->filename)),
                                'size' => filesize($file_dir . $newname),
                                'author' => $user->get('id')
                            ));
                            if (!$id_file) {
                                JFile::delete($file_dir . $newname);
                            }
                        }
                    } //end of foreach
                }
            }
        }
        $this->exitStatus('Done');
    }

    /**
     * Import docman files from selected category
     *
     * @return void
     * @throws \Exception Throw when application can not start
     * @since  1.0
     */
    public function docimport()
    {
        $app = JFactory::getApplication();
        include_once JPATH_ADMINISTRATOR . '/components/com_dropfiles/controllers/category.php';

        $params = JComponentHelper::getParams('com_dropfiles');
        $allowedext_list = '7z,ace,bz2,dmg,gz,rar,tgz,zip,csv,doc,docx,html,key,keynote,odp,ods,odt,pages,pdf,pps,ppt,'
            . 'pptx,rtf,tex,txt,xls,xlsx,xml,bmp,exif,gif,ico,jpeg,jpg,png,psd,tif,tiff,aac,aif,aiff,alac,amr,au,cdda,'
            . 'flac,m3u,m4a,m4p, mid, mp3, mp4, mpa, ogg, pac, ra, wav, wma, 3gp,asf,avi,flv,m4v,mkv,mov,mpeg,mpg,'
            . 'rm,swf,vob,wmv';
        $allowed_ext = explode(',', $params->get('allowedext', $allowedext_list));
        foreach ($allowed_ext as $key => $value) {
            $allowed_ext[$key] = strtolower(trim($allowed_ext[$key]));
            if ($allowed_ext[$key] === '') {
                unset($allowed_ext[$key]);
            }
        }

        $id = JFactory::getApplication()->input->getInt('doccat', 0);
        $dbo = JFactory::getDbo();
        $query = 'SELECT GROUP_CONCAT(r.descendant_id ORDER BY r.level ASC SEPARATOR \'/\') as path ,r.level'
            . ' FROM #__docman_category_relations as r INNER JOIN #__docman_categories as c  ON '
            . ' c.docman_category_id=r.ancestor_id  WHERE c.docman_category_id='
            . (int)$id . ' GROUP BY r.ancestor_id';

        $dbo->setQuery($query);

        $list = $dbo->loadObject();

        $cats = explode('/', $list->path);
        $cats = ArrayHelper::toInteger($cats);
        $query = 'SELECT r.*  FROM #__docman_category_relations as r ';
        $query .= ' WHERE r.level = 1 AND r.descendant_id IN (' . implode(',', $cats) . ') Order By r.ancestor_id';
        $dbo->setQuery($query);
        $cat_relations = $dbo->loadObjectList('descendant_id');

        if (count($cats)) {
            $parent_id = 1;
            $mapping = array(); //doc cat => dropfiles cat
            $app = JFactory::getApplication();
            $config = KObjectManager::getInstance()->getObject('com://admin/docman.model.entity.config');
            $docmandir = JPATH_ROOT . '/' . $config->document_path . '/' ;

            foreach ($cats as $catid) {
                $model = KObjectManager::getInstance()->getObject('com://admin/docman.model.documents')
                    ->enabled(1)
                    ->status('published')
                    ->category($catid)
                    ->limit(0)
                    ->sort('title')
                    ->direction('ASC');
                if (isset($cat_relations[$catid])) {
                    $doc_parent = $cat_relations[$catid]->ancestor_id;
                    if (isset($mapping[$doc_parent])) {
                        $parent_id = (int)$mapping[$doc_parent];
                    }
                }

                $documents = $model->fetch();
                $uri_docman_cate = 'com://admin/docman.model.categories';
                $cat = KObjectManager::getInstance()->getObject($uri_docman_cate)->id($catid)->fetch();
                $catcontroller = new DropfilesControllerCategory();

                $datas = array();
                $datas['jform']['extension'] = 'com_dropfiles';
                $datas['jform']['title'] = $cat->title;
                $datas['jform']['alias'] = $cat->slug . '-' . date('dmY-h-m-s', time());
                $datas['jform']['parent_id'] = $parent_id;
                $datas['jform']['published'] = 1;
                $datas['jform']['language'] = '*';
                $datas['jform']['metadata']['tags'] = '';

                //Set state value to retreive the correct table
                $model = $this->getModel('category');

                $model->setState('category.extension', 'category');

                foreach ($datas as $data => $val) {
                    $app->input->set($data, $val, 'POST');
                }

                if ($catcontroller->save()) {
                    $newId = $catcontroller->savedId;
                    $mapping[$catid] = $newId;
                    $parent_id = $newId;

                    $file_dir = DropfilesBase::getFilesPath($newId);
                    if (!file_exists($file_dir)) {
                        JFolder::create($file_dir);
                        $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                        JFile::write($file_dir . 'index.html', $data);
                        $data = 'deny from all';
                        JFile::write($file_dir . '.htaccess', $data);
                    }

                    $modelFiles = $this->getModel('files');
                    $user = JFactory::getUser();

                    foreach ($documents as $document) {
                        if ($document->storage_type === 'remote') {
                            $urlfile = pathinfo($document->storage_path);
                            $modelFiles->addFile(array(
                                'title' => $document->title,
                                'description' => $document->description,
                                'id_category' => $newId,
                                'file' => $document->storage_path,
                                'ext' => $urlfile['extension'],
                                'size' => $this->remoteFileSize($document->storage_path),
                                'author' => $user->get('id')
                            ));
                        } elseif ($document->storage_type === 'file') {
                            if (!in_array(strtolower(JFile::getExt($document->storage_path)), $allowed_ext)) {
                                continue;
                            }

                            $newname = uniqid() . '.' . strtolower(JFile::getExt($document->storage_path));

                            $docmanfile = $docmandir . $document->storage_path;

                            if (file_exists($docmanfile)) {
                                JFile::copy($docmanfile, $file_dir . $newname);
                            }

                            //Insert new image into databse

                            $id_file = $modelFiles->addFile(array(
                                'title' => $document->title,
                                'description' => $document->description,
                                'id_category' => $newId,
                                'file' => $newname,
                                'ext' => strtolower(JFile::getExt($document->storage_path)),
                                'size' => filesize($file_dir . $newname),
                                'author' => $user->get('id')
                            ));
                            if (!$id_file) {
                                JFile::delete($file_dir . $newname);
                            }
                        }
                    }
                }
            }
        }
        $this->exitStatus('Done');
    }


    /**
     * Import Phoca download files from selected category
     *
     * @return void
     * @throws \Exception Throw when application can not start
     * @since  1.0
     */
    public function phocaDownloadImport()
    {
        $app = JFactory::getApplication();
        include_once JPATH_ADMINISTRATOR . '/components/com_dropfiles/controllers/category.php';

        $params = JComponentHelper::getParams('com_dropfiles');
        $allowedext_list = '7z,ace,bz2,dmg,gz,rar,tgz,zip,csv,doc,docx,html,key,keynote,odp,ods,odt,pages,pdf,pps,ppt,'
            . 'pptx,rtf,tex,txt,xls,xlsx,xml,bmp,exif,gif,ico,jpeg,jpg,png,psd,tif,tiff,aac,aif,aiff,alac,amr,au,cdda,'
            . 'flac,m3u,m4a,m4p, mid, mp3, mp4, mpa, ogg, pac, ra, wav, wma, 3gp,asf,avi,flv,m4v,mkv,mov,mpeg,mpg,'
            . 'rm,swf,vob,wmv';
        $allowed_ext = explode(',', $params->get('allowedext', $allowedext_list));
        foreach ($allowed_ext as $key => $value) {
            $allowed_ext[$key] = strtolower(trim($allowed_ext[$key]));
            if ($allowed_ext[$key] === '') {
                unset($allowed_ext[$key]);
            }
        }

        $id = $app->input->getInt('phocadownloadcat', 0);
        $listchid = array();
        $this->getAllChild((array)$id, $listchid);
        $listchid = array_merge((array)$id, $listchid);

        $dbo = JFactory::getDbo();
        $query = 'SELECT a.id,a.parent_id,a.title,a.alias FROM #__phocadownload_categories AS a ';
        $query .= ' WHERE a.id IN (' . implode(',', $listchid) . ') ORDER BY a.parent_id';
        $dbo->setQuery($query);
        $list = $dbo->loadObjectList();

        if (count($list)) {
            $catcontroller = new DropfilesControllerCategory();
            $catpath = '';

            $parent_id = 1;
            $mapping = array(); //doc cat => dropfiles cat
            $db = JFactory::getDbo();
            $app = JFactory::getApplication();
            foreach ($list as $cats) {
                $downloads = $this->getAllFilesPhocaByCat($db, $cats);
                $phoca_parent = $cats->parent_id;
                if (isset($mapping[$phoca_parent])) {
                    $parent_id = $mapping[$phoca_parent];
                } else {
                    $parent_id = 1;
                }

                $datas = array();
                $datas['jform']['extension'] = 'com_dropfiles';
                $datas['jform']['title'] = $cats->title;
                $datas['jform']['alias'] = $cats->alias . '-' . date('dmY-h-m-s', time());
                $datas['jform']['parent_id'] = $parent_id;
                $datas['jform']['published'] = 1;
                $datas['jform']['language'] = '*';
                $datas['jform']['metadata']['tags'] = '';

                //Set state value to retreive the correct table
                $model = $this->getModel('category');

                $model->setState('category.extension', 'category');

                foreach ($datas as $data => $val) {
                    $app->input->set($data, $val, 'POST');
                }

                if ($catcontroller->save()) {
                    $newId = $catcontroller->savedId;
                    $mapping[$cats->id] = $newId;


                    $file_dir = DropfilesBase::getFilesPath($newId);
                    if (!file_exists($file_dir)) {
                        JFolder::create($file_dir);
                        $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                        JFile::write($file_dir . 'index.html', $data);
                        $data = 'deny from all';
                        JFile::write($file_dir . '.htaccess', $data);
                    }

                    $modelFiles = $this->getModel('files');
                    $user = JFactory::getUser();

                    $catpath .= '/' . $cats->title;
                    foreach ($downloads as $download) {
                        $downloaddir = JPATH_ROOT . '/phocadownload';
                        if (dirname($download->filename) !== '.') {
                            $downloaddir = $downloaddir . '/' . dirname($download->filename);
                        }
                        if ($download->filename !== '') {
                            if (!in_array(strtolower(JFile::getExt(basename($download->filename))), $allowed_ext)) {
                                continue;
                            }

                            $newname = uniqid() . '.' . strtolower(JFile::getExt(basename($download->filename)));

                            $downloadfile = $downloaddir . '/' . basename($download->filename);

                            if (file_exists($downloadfile)) {
                                JFile::copy($downloadfile, $file_dir . $newname);
                            }

                            //Insert new image into databse

                            $id_file = $modelFiles->addFile(array(
                                'title' => $download->title,
                                'description' => $download->description,
                                'id_category' => $newId,
                                'file' => $newname,
                                'ext' => strtolower(JFile::getExt(basename($download->filename))),
                                'size' => filesize($file_dir . $newname),
                                'author' => $user->get('id')
                            ));
                            if (!$id_file) {
                                JFile::delete($file_dir . $newname);
                            }
                        } else {
                            $urlfile = pathinfo($download->filename);
                            $modelFiles->addFile(array(
                                'title' => $download->title,
                                'description' => $download->description,
                                'id_category' => $newId,
                                'file' => basename($download->filename),
                                'ext' => $urlfile['extension'],
                                'size' => $this->remoteFileSize($download->filename),
                                'author' => $user->get('id')
                            ));
                        }
                    }
                }
            }
        }
        $this->exitStatus('Done');
    }

    /**
     * Get file size of a remote file
     *
     * @param string $url Url
     *
     * @return mixed|string
     * @since  1.0
     */
    protected function remoteFileSize($url)
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_NOBODY => 1,
        ));

        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_exec($ch);

        $clen = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        curl_close($ch);

        if (!$clen || ((int) $clen === -1)) {
            return 'n/a';
        }

        return $clen;
    }

    /**
     * Get all id child
     *
     * @param array $id  List id
     * @param array $arr Array
     *
     * @return array
     * @since  1.0
     */
    private function getAllChild($id, &$arr)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('id,parent_id');
        $query->from('#__phocadownload_categories as c');
        $query->where('c.parent_id IN (' . implode(',', $id) . ')');
        $db->setQuery($query);
        $results = $db->loadColumn();
        if ($results) {
            $arr = array_merge($arr, $results);
            $this->getAllChild($results, $arr);
        } else {
            return $arr;
        }
    }


    /**
     * Get all files of category
     *
     * @param object $db  Database instance
     * @param object $cat Category
     *
     * @return mixed
     * @since  1.0
     */
    private function getAllFilesPhocaByCat($db, $cat)
    {
        $query = $db->getQuery(true);
        // Select the required fields from the table.
        $query->select('a.*');
        $query->from('`#__phocadownload` AS a');

        // Join over the language
        $query->select('l.title AS language_title');
        $query->join('LEFT', '`#__languages` AS l ON l.lang_code = a.language');

        // Join over the users for the checked out user.


        $query->select('uc.name AS editor');
        $query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

        $query->select('uua.id AS uploaduserid, uua.username AS uploadusername, uua.name AS uploadname');
        $query->join('LEFT', '#__users AS uua ON uua.id=a.userid');

        // Join over the asset groups.
        $query->select('ag.title AS access_level');
        $query->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

        // Join over the categories.
        $query->select('c.title AS category_title, c.id AS category_id');
        $query->join('LEFT', '#__phocadownload_categories AS c ON c.id = a.catid');

        $query->select('ua.id AS userid, ua.username AS username, ua.name AS usernameno');
        $query->join('LEFT', '#__users AS ua ON ua.id = a.owner_id');

        $query->where('a.catid = ' . (int)$cat->id);
        $db->setQuery($query);

        return $db->loadObjectList();
    }

    /**
     * Clone theme
     *
     * @return void
     * @throws \Exception Throw when application can not start
     * @since  1.0
     */
    public function clonetheme()
    {
        $fromTheme = JFactory::getApplication()->input->getString('fromtheme');
        $newTheme = JFactory::getApplication()->input->getString('newtheme');

        $newTheme = str_replace(' ', '_', $newTheme);
        $newTheme = preg_replace('/[^a-zA-Z0-9_]+/', '', $newTheme);
        $newTheme = strtolower($newTheme);

        $model = $this->getModel('config');

        $result = $model->cloneTheme($fromTheme, $newTheme);

        $this->exitStatus($result['success'], $result['message']);
    }

    /**
     * Google Stop watch changes
     *
     * @return void
     * @since  5.2
     */
    public function googleStopWatchChanges()
    {
        // Check for request forgeries.
        JSession::checkToken() || jexit(JText::_('JINVALID_TOKEN'));

        try {
            $app = JFactory::getApplication();
        } catch (Exception $ex) {
            $this->exitStatus(false, JText::_('JERROR_AN_ERROR_HAS_OCCURRED'));
        }

        $params = JComponentHelper::getParams('com_dropfiles');
        $google_watch_changes = (int) $params->get('google_watch_changes', 1);

        if (!$google_watch_changes) {
            // Watch changes
            if (DropfilesCloudHelper::watchChanges()) {
                DropfilesComponentHelper::setParams(array('google_watch_changes' => 1));
            } else {
                DropfilesComponentHelper::setParams(array('google_watch_changes' => 0));
            }
        } else {
            // Cancel watch changes
            DropfilesCloudHelper::cancelWatchChanges();
            DropfilesComponentHelper::setParams(array('google_watch_changes' => 0));
        }
        $this->exitStatus(true);
        $app->close();
    }
}
