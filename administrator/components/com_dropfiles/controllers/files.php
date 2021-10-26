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
 * @copyright Copyright (C) 2013 Damien BarrÃ¨re (http://www.crac-design.com). All rights reserved.
 * @license   GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
 */

// no direct access
defined('_JEXEC') || die;

jimport('joomla.application.component.controllerform');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

/**
 * Class DropfilesControllerFiles
 */
class DropfilesControllerFiles extends JControllerForm
{

    /**
     * Allow extension
     *
     * @var array
     */
    private $allowed_ext = array(
        '7z',
        'ace',
        'bz2',
        'dmg',
        'gz',
        'rar',
        'tgz',
        'zip',
        'csv',
        'doc',
        'docx',
        'html',
        'key',
        'keynote',
        'odp',
        'ods',
        'odt',
        'pages',
        'pdf',
        'pps',
        'ppt',
        'pptx',
        'rtf',
        'tex',
        'txt',
        'xls',
        'xlsx',
        'xml',
        'bmp',
        'exif',
        'gif',
        'ico',
        'jpeg',
        'jpg',
        'png',
        'psd',
        'tif',
        'tiff',
        'aac',
        'aif',
        'aiff',
        'alac',
        'amr',
        'au',
        'cdda',
        'flac',
        'm3u',
        'm4a',
        'm4p',
        'mid',
        'mp3',
        'mp4',
        'mpa',
        'ogg',
        'pac',
        'ra',
        'wav',
        'wma',
        '3gp',
        'asf',
        'avi',
        'flv',
        'm4v',
        'mkv',
        'mov',
        'mpeg',
        'mpg',
        'rm',
        'swf',
        'vob',
        'wmv'
    );

    /**
     * DropfilesControllerFiles constructor.
     *
     * @param array $config Config array
     *
     * @return void
     * @throws \Exception Throw when application can not start
     * @since  version
     */
    public function __construct($config = array())
    {
        $params            = JComponentHelper::getParams('com_dropfiles');
        $allowedext_list   = '7z,ace,bz2,dmg,gz,rar,tgz,zip,csv,doc,docx,html,key,keynote,odp,ods,odt,pages,pdf,pps,ppt,'
                             . 'pptx,rtf,tex,txt,xls,xlsx,xml,bmp,exif,gif,ico,jpeg,jpg,png,psd,tif,tiff,aac,aif,aiff,alac,amr,au,cdda,'
                             . 'flac,m3u,m4a,m4p, mid, mp3, mp4, mpa, ogg, pac, ra, wav, wma, 3gp,asf,avi,flv,m4v,mkv,mov,mpeg,mpg,'
                             . 'rm,swf,vob,wmv';
        $this->allowed_ext = explode(',', $params->get('allowedext', $allowedext_list));
        foreach ($this->allowed_ext as $key => $value) {
            $this->allowed_ext[$key] = strtolower(trim($this->allowed_ext[$key]));
            if ($this->allowed_ext[$key] === '') {
                unset($this->allowed_ext[$key]);
            }
        }
        parent::__construct($config);
    }

    /**
     * Upload files
     *
     * @return void
     * @throws \Exception Throw when application can not start
     * @since  version
     */
    public function upload()
    {
        // Access check.
        if (!$this->allowAdd()) {
            $this->exitStatus(JText::_('JLIB_APPLICATION_ERROR_CREATE_RECORD_NOT_PERMITTED'));
        }

        $input       = JFactory::getApplication()->input;
        $id_category = $input->getInt('id_category', 0);
        if ($id_category <= 0) {
            $this->exitStatus(JText::_('COM_DROPFILES_CTRL_FILES_WRONG_CATEGORY'));
        }

        $app = JFactory::getApplication();
        $this->canEdit($id_category);

        $modelCat = $this->getModel('category');
        $category = $modelCat->getCategory($id_category);

        //if ($category->type == 'googledrive') {
        // } else {
        //todo: crÃ©er un rÃ©pertoire spÃ©cial pour les categories
        $file_dir = DropfilesBase::getFilesPath($id_category);
        if (!file_exists($file_dir)) {
            JFolder::create($file_dir);
            $data = '<html><body bgcolor="#FFFFFF"></body></html>';
            JFile::write($file_dir . 'index.html', $data);
            $data = 'deny from all';
            JFile::write($file_dir . '.htaccess', $data);
        }
        //}
        // Delete chunks of cancelled files
        $deleteChunks = $input->getString('deleteChunks', '');
        if ($deleteChunks !== '') {
            $this->rrmdir($file_dir . $deleteChunks);
            $this->exitStatus(true, array('deletedChunks' => $deleteChunks));
        }
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $resumableIdentifier  = $input->getString('resumableIdentifier', '');
            $resumableFilename    = $input->getString('resumableFilename', '');
            $resumableChunkNumber = $input->getInt('resumableChunkNumber', '');
            if ($resumableFilename === '') {
                $this->exitStatus('Wrong request!');
            }

            $temp_dir   = $file_dir . $resumableIdentifier;
            $chunk_file = $temp_dir . '/' . $resumableFilename . '.part' . $resumableChunkNumber;

            if (file_exists($chunk_file)) {
                header('HTTP/1.0 200');
            } else {
                // File's chunk not yet uploaded. Upload it!
                header('HTTP/1.0 204');
            }
        }

        // loop through files and move the chunks to a temporarily created directory
        if (!empty($_FILES)) {
            foreach ($_FILES as $file) {
                // check the error status
                if ((int) $file['error'] !== 0) {
                    continue;
                }

                // init the destination file (format <filename.ext>.part<#chunk>
                // the file is stored in a temporary directory
                $resumableIdentifier  = html_entity_decode($input->getString('resumableIdentifier'), ENT_COMPAT, 'UTF-8');
                $resumableFilename    = html_entity_decode($input->getString('resumableFilename'), ENT_COMPAT, 'UTF-8');
                $resumableChunkNumber = $input->getInt('resumableChunkNumber', '');
                $resumableTotalSize   = $input->getInt('resumableTotalSize', '');
                $resumableTotalChunks = $input->getInt('resumableTotalChunks', '');
                $resumableType        = html_entity_decode($input->getString('resumableType', ''), ENT_COMPAT, 'UTF-8');
                $temp_dir             = $file_dir . $resumableIdentifier;
                $dest_file            = $temp_dir . '/' . $resumableFilename . '.part' . $resumableChunkNumber;

                // create the temporary directory
                if (!is_dir($temp_dir)) {
                    JFolder::create($temp_dir, 0777);
                }
                $user  = JFactory::getUser();
                $date  = JFactory::getDate();
                $model = $this->getModel();

                $newname = uniqid() . '.' . strtolower(JFile::getExt($resumableFilename));
                // move the temporary file
                if (!JFile::upload($file['tmp_name'], $dest_file, false, true)) {
                    $this->exitStatus(JText::_('COM_DROPFILES_CTRL_FILES_CANT_MOVE_FILE') . ' ' . $file['name']);
                } else {
                    // check if all the parts present, and create the final destination file
                    $joinFiles = $this->createFileFromChunks(
                        $temp_dir,
                        $file_dir,
                        $resumableFilename,
                        $newname,
                        $resumableTotalSize,
                        $resumableTotalChunks
                    );
                    if ($joinFiles === false) {
                        $this->exitStatus('Error saving file ' . $file['name']);
                    } elseif ($joinFiles === true) {
                        $filePath = $file_dir . $newname;
                        if ($category->type === 'googledrive') {
                            $google = new DropfilesGoogle();

                            $fileContent  = file_get_contents($filePath);
                            $insertedFile = $google->uploadFile(
                                $resumableFilename,
                                $fileContent,
                                $resumableType,
                                $category->cloud_id
                            );
                            if (!$insertedFile) {
                                $this->exitStatus($google->getLastError());
                            }
                            $modelGoogle       = $this->getModel('googlefiles');
                            $file_data         = $google->getFileObj($insertedFile);
                            $file_data->catid  = $category->cloud_id;
                            $file_data->author = $user->get('id');
                            unset($file_data->id);
                            $modelGoogle->addFile($file_data);
                            JFile::delete($filePath);
                        } elseif ($category->type === 'dropbox') {
                            $dropbox = new DropfilesDropbox();

                            $result = $dropbox->uploadFile(
                                $resumableFilename,
                                $filePath,
                                $resumableTotalSize,
                                $category->path
                            );

                            if ($result) {
                                $modelDropbox               = $this->getModel('dropboxfiles');
                                $file_data                  = array();
                                $file_data['id']            = 0;
                                $file_data['title']         = JFile::stripExt($result['name']);
                                $file_data['file_id']       = $result['id'];
                                $file_data['ext']           = strtolower(JFile::getExt($result['name']));
                                $file_data['size']          = $result['size'];
                                $file_data['catid']         = $category->cloud_id;
                                $file_data['path']          = $result['path_lower'];
                                $file_data['created_time']  = $date->setTimestamp(
                                    strtotime($result['client_modified'])
                                )->toSql();
                                $file_data['modified_time'] = $date->setTimestamp(
                                    strtotime($result['server_modified'])
                                )->toSql();
                                $file_data['author']        = $user->get('id');
                                $modelDropbox->save($file_data);
                            }
                            JFile::delete($filePath);
                        } elseif ($category->type === 'onedrive') {
                            $onedrive = new DropfilesOneDrive();

                            $ext          = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                            $fileTitle    = pathinfo($file['name'], PATHINFO_FILENAME);
                            $file_name    = $fileTitle . '.' . $ext;
                            $pic          = array();
                            $pic['error'] = 0;
                            $pic['name']  = $file_name;
                            $pic['type']  = '';
                            $pic['size']  = $resumableTotalSize;
                            $result       = $onedrive->uploadFile($file_name, $pic, $filePath, $category->cloud_id);
                            if ($result && (int) $result['file']['error'] === 0) {
                                $modelOnedrive              = $this->getModel('onedrivefiles');
                                $created_time               = $date->setTimestamp(
                                    strtotime($result['file']['createdDateTime'])
                                )->toSql();
                                $modified_time              = $date->setTimestamp(
                                    strtotime($result['file']['lastModifiedDateTime'])
                                )->toSql();
                                $file_data                  = array();
                                $file_data['id']            = 0;
                                $file_data['title']         = JFile::stripExt($result['file']['name']);
                                $file_data['file_id']       = $result['file']['id'];
                                $file_data['ext']           = $ext;
                                $file_data['size']          = $result['file']['size'];
                                $file_data['catid']         = $category->cloud_id;
                                $file_data['path']          = '';
                                $file_data['created_time']  = $created_time;
                                $file_data['modified_time'] = $modified_time;
                                $file_data['author']        = $user->get('id');
                                $modelOnedrive->save($file_data);
                            } elseif ((int) $result['file']['error'] !== 0) {
                                $this->exitStatus($result['file']['error']);
                            } else {
                                $this->exitStatus(JText::_('COM_DROPFILES_CTRL_FILES_CANT_SAVE_TO_DB'));
                            }
                            JFile::delete($filePath);
                        } elseif ($category->type === 'onedrivebusiness') {
                            $onedrive     = new DropfilesOneDriveBusiness();
                            $ext          = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                            $fileTitle    = pathinfo($file['name'], PATHINFO_FILENAME);
                            $file_name    = $fileTitle . '.' . $ext;
                            $pic = array();
                            $pic['error'] = 0;
                            $pic['name']  = $file_name;
                            $pic['type']  = '';
                            $pic['size']  = $resumableTotalSize;
                            $result = $onedrive->uploadFile(
                                $file_name,
                                $pic,
                                $filePath,
                                $category->cloud_id
                            );
                            // Save db
                            if ($result && (int) $result['file']['error'] === 0) {
                                $modelOnedriveBusiness      = $this->getModel('onedrivebusinessfiles');
                                $created_time               = $date->setTimestamp(
                                    strtotime($result['file']['createdDateTime'])
                                )->toSql();
                                $modified_time              = $date->setTimestamp(
                                    strtotime($result['file']['lastModifiedDateTime'])
                                )->toSql();
                                $file_data                  = array();
                                $file_data['id']            = 0;
                                $file_data['title']         = JFile::stripExt($result['file']['name']);
                                $file_data['file_id']       = $result['file']['id'];
                                $file_data['ext']           = $ext;
                                $file_data['size']          = $result['file']['size'];
                                $file_data['catid']         = $category->cloud_id;
                                $file_data['path']          = '';
                                $file_data['created_time']  = $created_time;
                                $file_data['modified_time'] = $modified_time;
                                $file_data['author']        = $user->get('id');
                                $modelOnedriveBusiness->save($file_data);
                            } elseif ((int) $result['file']['error'] !== 0) {
                                $this->exitStatus($result['file']['error']);
                            } else {
                                $this->exitStatus(JText::_('COM_DROPFILES_CTRL_FILES_CANT_SAVE_TO_DB'));
                            }
                            JFile::delete($filePath);
                        } else {
                            //Insert new image into databse when success
                            $id_file = $model->addFile(array(
                                'title'       => JFile::stripExt($resumableFilename),
                                'id_category' => $id_category,
                                'file'        => $newname,
                                'ext'         => strtolower(JFile::getExt($resumableFilename)),
                                'size'        => filesize($file_dir . $newname),
                                'author'      => $user->get('id')
                            ));

                            if (!$id_file) {
                                JFile::delete($file_dir . $newname);
                                $this->exitStatus(JText::_('COM_DROPFILES_CTRL_FILES_CANT_SAVE_TO_DB'));
                            }
                        }
                        // Update files counter
                        $categoriesModel = $this->getModel('Categories', 'DropfilesModel');
                        $categoriesModel->updateFilesCount();
                        // Send email after done
                        $params = JComponentHelper::getParams('com_dropfiles');

                        $email_title = JText::_('COM_DROPFILES_EMAIL_ADD_EVENT_TITLE');

                        $email_body     = $params->get(
                            'add_event_editor',
                            DropfilesHelper::getHTMLEmail('file-added.html')
                        );
                        $search_replace = 'components/com_dropfiles/assets/images/icon-download.png';
                        $str_replace    = JUri::root() . 'components/com_dropfiles/assets/images/icon-download.png';
                        $email_body     = str_replace($search_replace, $str_replace, $email_body);
                        $email_body     = str_replace('{category}', $category->title, $email_body);
                        $email_body     = str_replace('{website_url}', JUri::root(), $email_body);
                        $email_body     = str_replace('{file_name}', $file['name'], $email_body);
                        $currentUser    = JFactory::getUser();
                        $email_body     = str_replace('{username}', $currentUser->name, $email_body);
                        $email_body     = str_replace('{uploader_username}', $currentUser->name, $email_body);

                        if ($params->get('add_event_subject', '') !== '') {
                            $email_title = $params->get('add_event_subject', '');
                        }

                        if ((int) $params->get('file_owner', 0) === 1 && (int) $params->get('add_event', 1) === 1) {
                            $email_body = str_replace('{receiver}', $currentUser->name, $email_body);
                            DropfilesHelper::sendMail($user->email, $email_title, $email_body);
                        }

                        if ((int) $params->get('category_owner', 0) === 1 && (int) $params->get('add_event', 1) === 1) {
                            $user       = JFactory::getUser($category->created_user_id);
                            $email_body = str_replace('{receiver}', $user->name, $email_body);
                            DropfilesHelper::sendMail($user->email, $email_title, $email_body);
                        }

                        if ($params->get('add_event_additional_email', '') !== '' && (int) $params->get('add_event', 1) === 1) {
                            $emails = explode(',', $params->get('add_event_additional_email', ''));
                            if (!empty($emails)) {
                                foreach ($emails as $email) {
                                    DropfilesHelper::sendMail($email, $email_title, $email_body);
                                }
                            }
                        }

                        if ((int) $params->get('notify_super_admin', 0) === 1 && (int) $params->get('add_event', 1) === 1) {
                            $users = DropfilesHelper::getSuperAdmins();

                            if (count($users)) {
                                foreach ($users as $item) {
                                    $user       = JFactory::getUser($item->user_id);
                                    $email_body = str_replace('{receiver}', $user->name, $email_body);
                                    DropfilesHelper::sendMail($user->email, $email_title, $email_body);
                                }
                            }
                        }
                        if ($app->isClient('administrator')) {
                            if (isset($id_file)) {
                                $this->exitStatus(true, array('id' => $id_file));
                            } else {
                                $this->exitStatus(true, array());
                            }
                        } else {
                            if (isset($id_file)) {
                                $this->exitStatus(
                                    JText::_('COM_DROPFILES_CTRL_FILES_UPLOAD_FILE_SUCCESS'),
                                    array('id' => $id_file)
                                );
                            } else {
                                $this->exitStatus(JText::_('COM_DROPFILES_CTRL_FILES_UPLOAD_FILE_SUCCESS'), array());
                            }
                        }
                    }
                }
            }
        }
        $this->exitStatus(JText::_('COM_DROPFILES_CTRL_FILES_UPLOAD_FILE_ERROR')); //todo : translate
    }

    /**
     * Index a file
     *
     * @return void
     * @throws \Exception Throw when application can not start
     * @since  version
     */
    public function ftsIndex()
    {
        $id_file  = JFactory::getApplication()->input->post->getString('id', null);
        $ftsModel = $this->getModel('fts');
        $ftsModel->dfPostReindex($id_file);
        $this->exitStatus(true, array());
    }

    /**
     * Add version
     *
     * @return void
     * @throws \Exception Throw when application can not start
     * @since  version
     */
    public function version()
    {
        $id_file     = JFactory::getApplication()->input->getString('id_file', null);
        $id_category = JFactory::getApplication()->input->getInt('id_category', 0);
        $ext         = JFactory::getApplication()->input->getString('ext', '');
        $date        = JFactory::getDate();
        if ($id_file === null) {
            $this->exitStatus(JText::_('COM_DROPFILES_CTRL_FILES_WRONG_FILE'));
        }

        if (strtolower($_SERVER['REQUEST_METHOD']) !== 'post') {
            $this->exitStatus(JText::_('COM_DROPFILES_CTRL_FILES_WRONG_HTTP_RESPONSE'));
        }

        $params = JComponentHelper::getParams('com_dropfiles');
        if (array_key_exists('pic', $_FILES) && (int) $_FILES['pic']['error'] === 0) {
            $pic = $_FILES['pic'];
            if (!in_array(strtolower(JFile::getExt($pic['name'])), $this->allowed_ext)) {
                $allowed_array = array('allowed ' => $this->allowed_ext);
                $this->exitStatus(JText::_('COM_DROPFILES_CTRL_FILES_WRONG_FILE_EXTENSION'), $allowed_array);
            }

            if ($ext !== strtolower(JFile::getExt($pic['name']))) {
                $this->exitStatus(JText::_('COM_DROPFILES_ADD_VERSION_WRONG_EXT'), array());
            }
            $modelCat   = $this->getModel('category');
            $category   = $modelCat->getCategory($id_category);
            $modelFiles = $this->getModel();

            if ($category->type === 'googledrive') {
                $google = new DropfilesGoogle();
                $files  = $google->listVersions($id_file);
            } elseif ($category->type === 'dropbox') {
                $dropbox = new DropfilesDropbox();
                $files   = $dropbox->displayDropboxVersionInfo($id_file);
            } elseif ($category->type === 'onedrive') {
                $onedrive = new DropfilesOneDrive();
                $files = $onedrive->listVersions($id_file); // todo: check list onedrive versions is truely returned
            } else {
                $files = $modelFiles->getVersions($id_file);
            }

            if (count($files) >= $params->get('versioning_number', 10)) {
                $modelFiles->deleteOldestVersion($id_file, $category->id); // This work for local files version. NOT for cloud yet
            }

            $this->canEdit($category->id);

            if ($category->type === 'googledrive') {
                $google = new DropfilesGoogle();

                $fileContent = file_get_contents($pic['tmp_name']);

                $result = $google->saveFileInfos(array(
                    'id'          => $id_file,
                    'newRevision' => true,
                    //'title'       => $pic['name'],
                    'data'        => $fileContent,
                    'ext'         => strtolower(JFile::getExt($pic['name']))
                ), $category->cloud_id);
            } elseif ($category->type === 'dropbox') {
                $dropbox = new DropfilesDropbox();
                $version = $dropbox->saveDropboxVersion(array(
                    'newRevision'   => true,
                    'old_file'      => $id_file,
                    'new_file_name' => $pic['name'],
                    'new_file_size' => $pic['size'],
                    'new_tmp_name'  => $pic['tmp_name']
                ));
            } elseif ($category->type === 'onedrive') {
                $onedrive = new DropfilesOneDrive();
                $service_client = $onedrive->getClientServer();
                $service = $service_client['service'];
                $onedriveFile = $service->items->get(DropfilesCloudHelper::replaceIdOneDrive($id_file, false));
                $fileName = $onedriveFile->getName();
                $result = $onedrive->uploadFile($fileName, $pic, $pic['tmp_name'], $category->cloud_id, true);
                // Update file size and modified time on database
                if ($result && (int) $result['file']['error'] === 0) {
                    $modelOnedrive = $this->getModel('onedrivefiles');
                    $dbFile = $modelOnedrive->getFile($id_file);
                    if ($dbFile) {
                        $modified_time              = $date->setTimestamp(
                            strtotime($result['file']['lastModifiedDateTime'])
                        )->toSql();
                        $file_data                  = array();
                        $file_data['id']            = $dbFile->id; // Current file id
                        $file_data['size']          = $result['file']['size'];
                        $file_data['modified_time'] = $modified_time;
                        $modelOnedrive->save($file_data);
                    }
                } elseif ((int) $result['file']['error'] !== 0) {
                    $this->exitStatus($result['file']['error']);
                } else {
                    $this->exitStatus(JText::_('COM_DROPFILES_CTRL_FILES_CANT_SAVE_TO_DB'));
                }
            } else {
                $model = $this->getModel();
                $file  = $model->getFile($id_file);

                if ($file->catid !== $category->id) {
                    $this->exitStatus(JText::_('COM_DROPFILES_CTRL_FILES_CANT_MOVE_FILE'));
                }

                $version_dir = DropfilesBase::getVersionPath($id_category);
                if (!file_exists($version_dir)) {
                    JFolder::create($version_dir);
                    $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                    JFile::write($version_dir . 'index.html', $data);
                    $data = 'deny from all';
                    JFile::write($version_dir . '.htaccess', $data);
                }

                $newname       = uniqid() . '.' . strtolower(JFile::getExt($pic['name']));
                $src_filespath = DropfilesBase::getFilesPath($file->catid) . $file->file;
                if (JFile::move($src_filespath, $version_dir . $file->file) !== true) {
                    $this->exitStatus(JText::_('COM_DROPFILES_CTRL_FILES_CANT_MOVE_FILE'));
                }
                $new_file_path = DropfilesBase::getFilesPath($file->catid) . $newname;
                if (!JFile::upload($pic['tmp_name'], $new_file_path, false, true)) {
                    $this->exitStatus(JText::_('COM_DROPFILES_CTRL_FILES_CANT_MOVE_FILE'));
                }

                $modelF = $this->getModel('file');
                $table  = $modelF->getTable();

                $updateData = array(
                    'id'   => $file->id,
                    'file' => $newname,
                    'ext'  => JFile::getExt($pic['name']),
                    'size' => filesize(DropfilesBase::getFilesPath($file->catid) . $newname)
                );
                $version    = JFactory::getApplication()->input->getString('version', null);
                if ($version !== null) {
                    $updateData['version'] = $version;
                }
                $date                        = JFactory::getDate();
                $updateData['modified_time'] = $date->toSql();
                $table->save($updateData);

                $data_arr = array(
                    'id_file' => $file->id,
                    'file'    => $file->file,
                    'ext'     => $file->ext,
                    'size'    => $file->size
                );
                $model->addVersion($data_arr);
            }

            $this->exitStatus(JText::_('COM_DROPFILES_ADD_VERSION_SUCCESSFULLY'));
        }
    }

    /**
     * Import file
     *
     * @return void
     * @throws \Exception Throw when application can not start
     * @since  version
     */
    public function import()
    {
        $user = JFactory::getUser();
        if (!$user->authorise('core.admin')) {
            $this->exitStatus(JText::_('COM_DROPFILES_CTRL_FILES_WRONG_PERMISSION'));
        }
        $id_category = JFactory::getApplication()->input->getInt('id_category', 0);
        if ($id_category <= 0) {
            $this->exitStatus(JText::_('COM_DROPFILES_CTRL_FILES_WRONG_CATEGORY'));
        }
        if (!JSession::checkToken('get')) {
            $this->exitStatus(JText::_('WRONG_TOKEN'));
        }

        $modelCat = $this->getModel('category');
        $category = $modelCat->getCategory($id_category);
        $this->canEdit($category->id);

        $params = JComponentHelper::getParams('com_dropfiles');
        $do     = $params->get('import');
        if (!$do) {
            $this->exitStatus('', array('noerror'));
        }
        //todo: crÃ©er un rÃ©pertoire spÃ©cial pour les categories
        $file_dir = DropfilesBase::getFilesPath($id_category);
        if (!file_exists($file_dir)) {
            JFolder::create($file_dir);
            $data = '<html><body bgcolor="#FFFFFF"></body></html>';
            JFile::write($file_dir . 'index.html', $data);
            $data = 'deny from all';
            JFile::write($file_dir . '.htaccess', $data);
        }
        $files = JFactory::getApplication()->input->get('files', null, 'array');
        if (!empty($files)) {
            $count = 0;
            foreach ($files as $file) {
                $file = JPATH_ROOT . DIRECTORY_SEPARATOR . $file;
                if (strpos($file, '..') !== false) {
                    $this->exitStatus(JText::_('COM_DROPFILES_CTRL_FILES_WRONG_FOLDER'));
                }
                if ((JPATH_ROOT !== '') && strpos($file, JPath::clean(JPATH_ROOT)) !== 0) {
                    $this->exitStatus(JText::_('COM_DROPFILES_CTRL_FILES_WRONG_FOLDER'));
                }
                if (!in_array(strtolower(JFile::getExt($file)), $this->allowed_ext)) {
                    $allowed_array = array('allowed ' => $this->allowed_ext);
                    $this->exitStatus(JText::_('COM_DROPFILES_CTRL_FILES_WRONG_FILE_EXTENSION'), $allowed_array);
                }
                if (!file_exists($file)) {
                    $this->exitStatus(JText::_('COM_DROPFILES_CTRL_FILES_CANT_MOVE_FILE'));
                }

                if ($category->type === 'googledrive') {
                    $google        = new DropfilesGoogle();
                    $file_contents = file_get_contents($file);
                    if (!$google->uploadFile(basename($file), $file_contents, '', $category->cloud_id)) {
                        $this->exitStatus($google->getLastError());
                    }
                } else {
                    $newname = uniqid() . '.' . strtolower(JFile::getExt($file));
                    if (!JFile::copy($file, $file_dir . $newname)) {
                        $this->exitStatus(JText::_('COM_DROPFILES_CTRL_FILES_CANT_MOVE_FILE'));
                    }

                    //Insert new image into databse
                    $model   = $this->getModel();
                    $user    = JFactory::getUser();
                    $id_file = $model->addFile(array(
                        'title'       => JFile::stripExt(basename($file)),
                        'id_category' => $id_category,
                        'file'        => $newname,
                        'ext'         => strtolower(JFile::getExt($file)),
                        'size'        => filesize($file_dir . $newname),
                        'author'      => $user->get('id')
                    ));
                    if (!$id_file) {
                        JFile::delete($file_dir . $newname);
                        $this->exitStatus(JText::_('COM_DROPFILES_CTRL_FILES_CANT_SAVE_TO_DB'));
                    }
                }
                $count++;
            }
            // Update files count
            $categoriesModel = $this->getModel('Categories', 'DropfilesModel');
            $categoriesModel->updateFilesCount();
            $this->exitStatus(true, array('nb' => $count));
        }
        $this->exitStatus(JText::_('COM_DROPFILES_CTRL_FILES_UPLOAD_FILE_ERROR')); //todo : translate
    }

    /**
     * Move file in category
     *
     * @return void
     * @throws \Exception Throw when application can not start
     * @since  version
     */
    public function movefile()
    {
        $input              = JFactory::getApplication()->input;
        $id_category        = $input->getInt('id_category', 0);
        $active_category_id = $input->getInt('active_category', 0);
        $id_file            = $input->getString('id_file', null);

        if (!$this->allowEdit()) {
            $this->exitStatus(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
        }

        $model           = $this->getModel();
        $modelFile       = $this->getModel('file');
        $modelCategory   = $this->getModel('category');
        $active_category = $modelCategory->getCategory($active_category_id);
        $target_category = $modelCategory->getCategory($id_category);
        if ($active_category->type === 'default' && $target_category->type === 'default') {
            $catpath_dest    = DropfilesBase::getFilesPath($id_category);
            $file            = $modelFile->getItem($id_file);
            $catpath_current = DropfilesBase::getFilesPath($file->catid);
            if ($model->moveCatFile($id_file, $id_category)) {
                // move file

                $file_current = $catpath_current . $file->file;
                $file_dest    = $catpath_dest . $file->file;

                if (!file_exists($catpath_dest)) {
                    JFolder::create($catpath_dest);
                    $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                    JFile::write($catpath_dest . 'index.html', $data);
                    $data = 'deny from all';
                    JFile::write($catpath_dest . '.htaccess', $data);
                }

                if (is_file($file_current)) {
                    JFile::move($file_current, $file_dest);
                }
            }
        } elseif ($active_category->type === 'googledrive' && $target_category->type === 'default') {
            $google       = new DropfilesGoogle();
            $file         = $google->download($id_file, $active_category->cloud_id, false);
            $catpath_dest = DropfilesBase::getFilesPath($id_category);

            if (!file_exists($catpath_dest)) {
                JFolder::create($catpath_dest);
                $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                JFile::write($catpath_dest . 'index.html', $data);
                $data = 'deny from all';
                JFile::write($catpath_dest . '.htaccess', $data);
            }

            $newname = uniqid() . '.' . $file->ext;
            file_put_contents($catpath_dest . $newname, $file->datas);
            $user     = JFactory::getUser();
            $model    = $this->getModel();
            $new_file = $model->addFile(array(
                'title'       => $file->title,
                'id_category' => $id_category,
                'file'        => $newname,
                'ext'         => $file->ext,
                'size'        => $file->size,
                'author'      => $user->get('id')
            ));

            if ($new_file) {
                $google->delete($id_file, $active_category->cloud_id);
                // Index new uploaded file
                $ftsModel = $this->getModel('fts');
                $ftsModel->reIndexFile($new_file);
            }
        } elseif ($active_category->type === 'default' && $target_category->type === 'googledrive') {
            $google          = new DropfilesGoogle();
            $catpath_dest    = DropfilesBase::getFilesPath($id_category);
            $file            = $modelFile->getItem($id_file);
            $modelFiles      = $this->getModel('files');
            $catpath_current = DropfilesBase::getFilesPath($file->catid);
            $file_current    = $catpath_current . $file->file;
            $file_contents   = file_get_contents($file_current);
            if (!$google->uploadFile($file->title . '.' . $file->ext, $file_contents, '', $target_category->cloud_id)) {
                $this->exitStatus($google->getLastError());
            }
            if ($modelFiles->removePicture($id_file)) {
                JFile::delete($file_current);
                // Index new uploaded file
                $ftsModel = $this->getModel('fts');
                $ftsModel->reIndexFile($id_file, true);
            }
        } elseif ($active_category->type === 'googledrive' && $target_category->type === 'googledrive') {
            $google = new DropfilesGoogle();
            $file   = $google->moveFile($id_file, $target_category->cloud_id);
            if ($file) {
                $user        = JFactory::getUser();
                $modelGoogle = $this->getModel('googlefiles');
                $modelGoogle->deleteFile($id_file);
                $file_data         = $google->getFileObj($file);
                $file_data->catid  = $target_category->cloud_id;
                $file_data->author = $user->get('id');
                unset($file_data->id);
                $modelGoogle->addFile($file_data);
            }
        } elseif ($active_category->type === 'dropbox' && $target_category->type === 'default') {
            $dropbox = new DropfilesDropbox();
            list($tem, $file) = $dropbox->downloadDropbox($id_file);

            $catpath_dest = DropfilesBase::getFilesPath($id_category);

            if (!file_exists($catpath_dest)) {
                JFolder::create($catpath_dest);
                $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                JFile::write($catpath_dest . 'index.html', $data);
                $data = 'deny from all';
                JFile::write($catpath_dest . '.htaccess', $data);
            }

            $newname = uniqid() . '.' . JFile::getExt($file['name']);

            ob_start();
            header('Content-Disposition: attachment; filename="' . $file['name'] . '"');
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');

            header('Content-Length: ' . (int) $file['size']);

            echo readfile($tem);
            unlink($tem);
            $data = ob_get_clean();
            file_put_contents($catpath_dest . $newname, $data);
            $user     = JFactory::getUser();
            $model    = $this->getModel();
            $new_file = $model->addFile(array(
                'title'       => JFile::stripExt($file['name']),
                'id_category' => $id_category,
                'file'        => $newname,
                'ext'         => strtolower(JFile::getExt($file['name'])),
                'size'        => $file['size'],
                'author'      => $user->get('id')
            ));

            if ($new_file) {
                $modelDropbox = $this->getModel('dropboxfiles');
                if ($dropbox->deleteFileDropbox($id_file)) {
                    $modelDropbox->deleteFile($id_file);
                }
                // Index new uploaded file
                $ftsModel = $this->getModel('fts');
                $ftsModel->reIndexFile($new_file);
            }
        } elseif ($active_category->type === 'default' && $target_category->type === 'dropbox') {
            $file            = $modelFile->getItem($id_file);
            $catpath_current = DropfilesBase::getFilesPath($file->catid);
            $file_current    = $catpath_current . $file->file;
            $dropbox         = new DropfilesDropbox();
            $f_name          = $file->title . '.' . $file->ext;
            $result          = $dropbox->uploadFile($f_name, $file_current, filesize($file_current), $target_category->path);

            if ($result) {
                $modelDropbox               = $this->getModel('dropboxfiles');
                $file_data                  = array();
                $file_data['id']            = 0;
                $file_data['title']         = JFile::stripExt($result['name']);
                $file_data['file_id']       = $result['id'];
                $file_data['ext']           = strtolower(JFile::getExt($result['name']));
                $file_data['size']          = $result['size'];
                $file_data['catid']         = $target_category->cloud_id;
                $file_data['path']          = $result['path_lower'];
                $file_data['created_time']  = date('Y-m-d H:i:s', strtotime($result['client_modified']));
                $file_data['modified_time'] = date('Y-m-d H:i:s', strtotime($result['server_modified']));
                $modelDropbox->save($file_data);
                $modelFiles = $this->getModel('files');
                if ($modelFiles->removePicture($id_file)) {
                    JFile::delete($file_current);
                    // Index new uploaded file
                    $ftsModel = $this->getModel('fts');
                    $ftsModel->reIndexFile($id_file, true);
                }
            }
        } elseif ($active_category->type === 'dropbox' && $target_category->type === 'dropbox') {
            $dropbox = new DropfilesDropbox();
            $file    = $dropbox->getDropboxFileInfos($id_file);
            $result  = $dropbox->moveFile($file['path_lower'], $target_category->path . '/' . strtolower($file['name']));
            if ($result) {
                $modelDropbox = $this->getModel('dropboxfiles');
                $modelDropbox->deleteFile($id_file);

                if ($result) {
                    $file_data                  = array();
                    $file_data['id']            = 0;
                    $file_data['title']         = JFile::stripExt($result['name']);
                    $file_data['file_id']       = $result['id'];
                    $file_data['ext']           = strtolower(JFile::getExt($result['name']));
                    $file_data['size']          = $result['size'];
                    $file_data['catid']         = $target_category->cloud_id;
                    $file_data['path']          = $result['path_lower'];
                    $file_data['created_time']  = date('Y-m-d H:i:s', strtotime($result['client_modified']));
                    $file_data['modified_time'] = date('Y-m-d H:i:s', strtotime($result['server_modified']));
                    $modelDropbox->save($file_data);
                }
            }
        } elseif ($active_category->type === 'googledrive' && $target_category->type === 'dropbox') {
            $google = new DropfilesGoogle();
            $file   = $google->download($id_file, $active_category->cloud_id, false);

            $catpath_dest = DropfilesBase::getFilesPath($id_category);

            if (!file_exists($catpath_dest)) {
                JFolder::create($catpath_dest);
                $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                JFile::write($catpath_dest . 'index.html', $data);
                $data = 'deny from all';
                JFile::write($catpath_dest . '.htaccess', $data);
            }

            $newname = uniqid() . '.' . $file->ext;
            file_put_contents($catpath_dest . $newname, $file->datas);

            $dropbox = new DropfilesDropbox();

            $file_current = $catpath_dest . $newname;
            $f_name       = $file->title . '.' . $file->ext;
            $result       = $dropbox->uploadFile($f_name, $file_current, filesize($file_current), $target_category->path);

            if ($result) {
                $modelDropbox               = $this->getModel('dropboxfiles');
                $file_data                  = array();
                $file_data['id']            = 0;
                $file_data['title']         = JFile::stripExt($result['name']);
                $file_data['file_id']       = $result['id'];
                $file_data['ext']           = strtolower(JFile::getExt($result['name']));
                $file_data['size']          = $result['size'];
                $file_data['catid']         = $target_category->cloud_id;
                $file_data['path']          = $result['path_lower'];
                $file_data['created_time']  = date('Y-m-d H:i:s', strtotime($result['client_modified']));
                $file_data['modified_time'] = date('Y-m-d H:i:s', strtotime($result['server_modified']));
                $modelDropbox->save($file_data);

                JFile::delete($file_current);

                if ($google->delete($id_file, $active_category->cloud_id)) {
                    $modelGoogle = $this->getModel('googlefiles');
                    $modelGoogle->deleteFile($id_file);
                }
            }
        } elseif ($active_category->type === 'dropbox' && $target_category->type === 'googledrive') {
            $dropbox = new DropfilesDropbox();
            list($tem, $file) = $dropbox->downloadDropbox($id_file);

            $catpath_dest = DropfilesBase::getFilesPath($id_category);

            if (!file_exists($catpath_dest)) {
                JFolder::create($catpath_dest);
                $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                JFile::write($catpath_dest . 'index.html', $data);
                $data = 'deny from all';
                JFile::write($catpath_dest . '.htaccess', $data);
            }

            $newname = uniqid() . '.' . JFile::getExt($file['name']);

            ob_start();
            header('Content-Disposition: attachment; filename="' . $file['name'] . '"');
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');

            header('Content-Length: ' . (int) $file['size']);

            echo readfile($tem);
            unlink($tem);
            $data         = ob_get_clean();
            $file_current = $catpath_dest . $newname;
            file_put_contents($file_current, $data);
            $google     = new DropfilesGoogle();
            $f_contents = file_get_contents($file_current);
            $insertedFile = $google->uploadFile($file['name'], $f_contents, '', $target_category->cloud_id);

            if ($insertedFile) {
                $modelGoogle       = $this->getModel('googlefiles');
                $user              = JFactory::getUser();
                $file_data         = $google->getFileObj($insertedFile);
                $file_data->catid  = $target_category->cloud_id;
                $file_data->author = $user->get('id');
                unset($file_data->id);
                $modelGoogle->addFile($file_data);
                JFile::delete($file_current);

                $modelDropbox = $this->getModel('dropboxfiles');
                if ($dropbox->deleteFileDropbox($id_file)) {
                    $modelDropbox->deleteFile($id_file);
                }
            }
        } elseif ($active_category->type === 'onedrive' && $target_category->type === 'default') {
            $onedrive = new DropfilesOneDrive();
            $file     = $onedrive->downloadFile($id_file);

            $catpath_dest = DropfilesBase::getFilesPath($id_category);

            if (!file_exists($catpath_dest)) {
                JFolder::create($catpath_dest);
                $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                JFile::write($catpath_dest . 'index.html', $data);
                $data = 'deny from all';
                JFile::write($catpath_dest . '.htaccess', $data);
            }

            $newname = uniqid() . '.' . $file->ext;
            file_put_contents($catpath_dest . $newname, $file->datas);
            $user     = JFactory::getUser();
            $model    = $this->getModel();
            $new_file = $model->addFile(array(
                'title'       => $file->title,
                'id_category' => $id_category,
                'file'        => $newname,
                'ext'         => $file->ext,
                'size'        => $file->size,
                'author'      => $user->get('id')
            ));

            if ($new_file) {
                $modelOnedrive = $this->getModel('onedrivefiles');
                if ($onedrive->delete($id_file)) {
                    $modelOnedrive->deleteFile($id_file);
                }
                // Index new uploaded file
                $ftsModel = $this->getModel('fts');
                $ftsModel->reIndexFile($new_file);
            }
        } elseif ($active_category->type === 'default' && $target_category->type === 'onedrive') {
            $file            = $modelFile->getItem($id_file);
            $catpath_current = DropfilesBase::getFilesPath($file->catid);
            $file_current    = $catpath_current . $file->file;
            $onedrive        = new DropfilesOneDrive();

            $pic          = array();
            $pic['error'] = 0;
            $pic['name']  = $file->title . '.' . $file->ext;
            $pic['type']  = '';
            $pic['size']  = $file->size;
            $f_name       = $file->title . '.' . $file->ext;
            $result       = $onedrive->uploadFile($f_name, $pic, $file_current, $target_category->cloud_id);
            $user         = JFactory::getUser();
            if ($result) {
                $modelOnedrive              = $this->getModel('onedrivefiles');
                $file_data                  = array();
                $file_data['id']            = 0;
                $file_data['title']         = JFile::stripExt($result['file']['name']);
                $file_data['file_id']       = $result['file']['id'];
                $file_data['ext']           = $file->ext;
                $file_data['size']          = $result['file']['size'];
                $file_data['catid']         = $target_category->cloud_id;
                $file_data['path']          = '';
                $file_data['created_time']  = date('Y-m-d H:i:s', strtotime($result['file']['createdDateTime']));
                $file_data['modified_time'] = date('Y-m-d H:i:s', strtotime($result['file']['lastModifiedDateTime']));
                $file_data['author']        = $user->get('id');

                $modelOnedrive->save($file_data);
                $modelFiles = $this->getModel('files');
                if ($modelFiles->removePicture($id_file)) {
                    JFile::delete($file_current);
                }
            }
        } elseif ($active_category->type === 'onedrive' && $target_category->type === 'onedrive') {
            $onedrive = new DropfilesOneDrive();
            $result   = $onedrive->moveFile($id_file, $target_category->cloud_id);

            if ($result) {
                $modelOnedrive = $this->getModel('onedrivefiles');
                $modelOnedrive->deleteFile($id_file);
                $user = JFactory::getUser();
                if ($result) {
                    $file_data                  = array();
                    $file_data['id']            = 0;
                    $file_data['title']         = JFile::stripExt($result->getName());
                    $file_data['file_id']       = DropfilesCloudHelper::replaceIdOneDrive($result->getId());
                    $file_data['ext']           = strtolower(JFile::getExt($result->getName()));
                    $file_data['size']          = $result->getSize();
                    $file_data['catid']         = $target_category->cloud_id;
                    $file_data['path']          = '';
                    $file_data['created_time']  = date('Y-m-d H:i:s', strtotime($result->getCreatedDateTime()));
                    $file_data['modified_time'] = date('Y-m-d H:i:s', strtotime($result->getLastModifiedDateTime()));
                    $file_data['author']        = $user->get('id');
                    $modelOnedrive->save($file_data);
                }
            }
        } elseif ($active_category->type === 'googledrive' && $target_category->type === 'onedrive') {
            $google = new DropfilesGoogle();
            $file   = $google->download($id_file, $active_category->cloud_id, false);

            $catpath_dest = DropfilesBase::getFilesPath($id_category);

            if (!file_exists($catpath_dest)) {
                JFolder::create($catpath_dest);
                $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                JFile::write($catpath_dest . 'index.html', $data);
                $data = 'deny from all';
                JFile::write($catpath_dest . '.htaccess', $data);
            }

            $newname = uniqid() . '.' . $file->ext;
            file_put_contents($catpath_dest . $newname, $file->datas);

            $onedrive = new DropfilesOneDrive();

            $file_current = $catpath_dest . $newname;

            $pic          = array();
            $pic['error'] = 0;
            $pic['name']  = $file->title . '.' . $file->ext;
            $pic['type']  = '';
            $pic['size']  = $file->size;

            $f_name = $file->title . '.' . $file->ext;
            $result = $onedrive->uploadFile($f_name, $pic, $file_current, $target_category->cloud_id);
            $user   = JFactory::getUser();

            if ($result) {
                $modelOnedrive              = $this->getModel('onedrivefiles');
                $file_data                  = array();
                $file_data['id']            = 0;
                $file_data['title']         = JFile::stripExt($result['file']['name']);
                $file_data['file_id']       = $result['file']['id'];
                $file_data['ext']           = $file->ext;
                $file_data['size']          = $result['file']['size'];
                $file_data['catid']         = $target_category->cloud_id;
                $file_data['path']          = '';
                $file_data['created_time']  = date('Y-m-d H:i:s', strtotime($result['file']['createdDateTime']));
                $file_data['modified_time'] = date('Y-m-d H:i:s', strtotime($result['file']['lastModifiedDateTime']));
                $file_data['author']        = $user->get('id');
                $modelOnedrive->save($file_data);

                JFile::delete($file_current);

                if ($google->delete($id_file, $active_category->cloud_id)) {
                    $modelGoogle = $this->getModel('googlefiles');
                    $modelGoogle->deleteFile($id_file);
                }
            }
        } elseif ($active_category->type === 'onedrive' && $target_category->type === 'googledrive') {
            $onedrive = new DropfilesOneDrive();
            $file     = $onedrive->downloadFile($id_file);

            $catpath_dest = DropfilesBase::getFilesPath($id_category);

            if (!file_exists($catpath_dest)) {
                JFolder::create($catpath_dest);
                $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                JFile::write($catpath_dest . 'index.html', $data);
                $data = 'deny from all';
                JFile::write($catpath_dest . '.htaccess', $data);
            }

            $newname      = uniqid() . '.' . $file->ext;
            $file_current = $catpath_dest . $newname;
            file_put_contents($file_current, $file->datas);
            $google     = new DropfilesGoogle();
            $f_contents = file_get_contents($file_current);
            $f_name     = $file->title . '.' . $file->ext;
            $insertedFile = $google->uploadFile($f_name, $f_contents, '', $target_category->cloud_id);

            if ($insertedFile) {
                $modelGoogle       = $this->getModel('googlefiles');
                $user              = JFactory::getUser();
                $file_data         = $google->getFileObj($insertedFile);
                $file_data->catid  = $target_category->cloud_id;
                $file_data->author = $user->get('id');
                unset($file_data->id);
                $modelGoogle->addFile($file_data);
                JFile::delete($file_current);

                $modelOnedrive = $this->getModel('onedrivefiles');
                if ($onedrive->delete($id_file)) {
                    $modelOnedrive->deleteFile($id_file);
                }
            }
        } elseif ($active_category->type === 'onedrive' && $target_category->type === 'dropbox') {
            $onedrive = new DropfilesOneDrive();
            $file     = $onedrive->downloadFile($id_file);

            $catpath_dest = DropfilesBase::getFilesPath($id_category);

            if (!file_exists($catpath_dest)) {
                JFolder::create($catpath_dest);
                $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                JFile::write($catpath_dest . 'index.html', $data);
                $data = 'deny from all';
                JFile::write($catpath_dest . '.htaccess', $data);
            }

            $newname      = uniqid() . '.' . $file->ext;
            $file_current = $catpath_dest . $newname;
            file_put_contents($file_current, $file->datas);

            $dropbox = new DropfilesDropbox();

            $file_current = $catpath_dest . $newname;
            $f_name       = $file->title . '.' . $file->ext;
            $result       = $dropbox->uploadFile($f_name, $file_current, filesize($file_current), $target_category->path);

            if ($result) {
                $modelDropbox               = $this->getModel('dropboxfiles');
                $file_data                  = array();
                $file_data['id']            = 0;
                $file_data['title']         = JFile::stripExt($result['name']);
                $file_data['file_id']       = $result['id'];
                $file_data['ext']           = strtolower(JFile::getExt($result['name']));
                $file_data['size']          = $result['size'];
                $file_data['catid']         = $target_category->cloud_id;
                $file_data['path']          = $result['path_lower'];
                $file_data['created_time']  = date('Y-m-d H:i:s', strtotime($result['client_modified']));
                $file_data['modified_time'] = date('Y-m-d H:i:s', strtotime($result['server_modified']));
                $modelDropbox->save($file_data);

                JFile::delete($file_current);

                if ($onedrive->delete($id_file, $active_category->cloud_id)) {
                    $modelOnedrive = $this->getModel('onedrivefiles');
                    $modelOnedrive->deleteFile($id_file);
                }
            }
        } elseif ($active_category->type === 'dropbox' && $target_category->type === 'onedrive') {
            $dropbox = new DropfilesDropbox();
            list($tem, $file) = $dropbox->downloadDropbox($id_file);

            $catpath_dest = DropfilesBase::getFilesPath($id_category);

            if (!file_exists($catpath_dest)) {
                JFolder::create($catpath_dest);
                $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                JFile::write($catpath_dest . 'index.html', $data);
                $data = 'deny from all';
                JFile::write($catpath_dest . '.htaccess', $data);
            }

            $newname = uniqid() . '.' . JFile::getExt($file['name']);

            ob_start();
            header('Content-Disposition: attachment; filename="' . $file['name'] . '"');
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');

            header('Content-Length: ' . (int) $file['size']);

            echo readfile($tem);
            unlink($tem);
            $data         = ob_get_clean();
            $file_current = $catpath_dest . $newname;
            file_put_contents($file_current, $data);
            $onedrive = new DropfilesOneDrive();

            $file_current = $catpath_dest . $newname;

            $pic          = array();
            $pic['error'] = 0;
            $pic['name']  = $file['name'];
            $pic['type']  = '';
            $pic['size']  = $file['size'];
            $f_name       = $file['name'];
            $result       = $onedrive->uploadFile($f_name, $pic, $file_current, $target_category->cloud_id);
            $user         = JFactory::getUser();

            if ($result) {
                $modelOnedrive              = $this->getModel('onedrivefiles');
                $file_data                  = array();
                $file_data['id']            = 0;
                $file_data['title']         = JFile::stripExt($result['file']['name']);
                $file_data['file_id']       = $result['file']['id'];
                $file_data['ext']           = JFile::getExt($file['name']);
                $file_data['size']          = $result['file']['size'];
                $file_data['catid']         = $target_category->cloud_id;
                $file_data['path']          = '';
                $file_data['created_time']  = date('Y-m-d H:i:s', strtotime($result['file']['createdDateTime']));
                $file_data['modified_time'] = date('Y-m-d H:i:s', strtotime($result['file']['lastModifiedDateTime']));
                $file_data['author']        = $user->get('id');
                $modelOnedrive->save($file_data);

                JFile::delete($file_current);

                $modelDropbox = $this->getModel('dropboxfiles');
                if ($dropbox->deleteFileDropbox($id_file)) {
                    $modelDropbox->deleteFile($id_file);
                }
            }
        } elseif ($active_category->type === 'onedrivebusiness' && $target_category->type === 'default') {
            $onedriveBusiness   = new DropfilesOneDriveBusiness();
            $file               = $onedriveBusiness->downloadFile($id_file);
            $catpath_dest       = DropfilesBase::getFilesPath($id_category);

            if (!file_exists($catpath_dest)) {
                JFolder::create($catpath_dest);
                $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                JFile::write($catpath_dest . 'index.html', $data);
                $data = 'deny from all';
                JFile::write($catpath_dest . '.htaccess', $data);
            }

            $newname = uniqid() . '.' . $file->ext;
            file_put_contents($catpath_dest . $newname, $file->datas);

            $user                   = JFactory::getUser();
            $model                  = $this->getModel();
            $modelOnedriveBusiness  = $this->getModel('onedrivebusinessfiles');
            $fileOnedriveBusiness   = $modelOnedriveBusiness->getFile($id_file);
            $newFile = $model->addFile(array(
                'title'             => (isset($file->title)) ? $file->title : $fileOnedriveBusiness->title,
                'id_category'       => $id_category,
                'file'              => $newname,
                'ext'               => (isset($fileOnedriveBusiness->ext)) ? $fileOnedriveBusiness->ext : '',
                'description'       => (isset($fileOnedriveBusiness->description)) ? $fileOnedriveBusiness->description : '',
                'size'              => $file->size,
                'file_tags'         => (isset($fileOnedriveBusiness->file_tags)) ? $fileOnedriveBusiness->file_tags : '',
                'author'            => $user->get('id')
            ));

            if ($newFile) {
                if ($onedriveBusiness->delete($id_file)) {
                    $modelOnedriveBusiness->deleteFile($id_file);
                }
                // Index new uploaded file
                $ftsModel = $this->getModel('fts');
                $ftsModel->reIndexFile($newFile);
            }
        } elseif ($active_category->type === 'default' && $target_category->type === 'onedrivebusiness') {
            $file             = $modelFile->getItem($id_file);
            $catpath_current  = DropfilesBase::getFilesPath($file->catid);
            $file_current     = $catpath_current . $file->file;
            $onedriveBusiness = new DropfilesOneDriveBusiness();

            $pic          = array();
            $pic['error'] = 0;
            $pic['name']  = $file->title . '.' . $file->ext;
            $pic['type']  = '';
            $pic['size']  = $file->size;

            $f_name = $file->title . '.' . $file->ext;
            $user   = JFactory::getUser();
            $result = $onedriveBusiness->uploadFile($f_name, $pic, $file_current, $target_category->cloud_id);
            if ($result) {
                $modelOnedriveBusiness = $this->getModel('onedrivebusinessfiles');
                $result['file']['description'] = (isset($file->description)) ? $file->description : '';
                $result['file']['file_tags'] = (isset($file->file_tags)) ? $file->file_tags : '';
                $file_data = array();
                $file_data['id'] = 0;
                $file_data['title'] = JFile::stripExt($result['file']['name']);
                $file_data['file_id'] = $result['file']['id'];
                $file_data['ext'] = $file->ext;
                $file_data['description'] = $result['file']['description'];
                $file_data['size'] = $result['file']['size'];
                $file_data['catid'] = $target_category->cloud_id;
                $file_data['path'] = '';
                $file_data['created_time'] = date('Y-m-d H:i:s', strtotime($result['file']['createdDateTime']));
                $file_data['modified_time'] = date('Y-m-d H:i:s', strtotime($result['file']['lastModifiedDateTime']));
                $file_data['author'] = $user->get('id');
                $file_data['file_tags'] = $result['file']['file_tags'];
                $modelOnedriveBusiness->save($file_data);
                // Delete file
                $modelFiles = $this->getModel('files');
                if ($modelFiles->removePicture($id_file)) {
                    JFile::delete($file_current);
                }
            }
        } elseif ($active_category->type === 'onedrivebusiness' && $target_category->type === 'onedrivebusiness') {
            $onedriveBusiness = new DropfilesOneDriveBusiness();
            $cloudId          = DropfilesCloudHelper::getOneDriveBusinessIdByTermId($id_category);
            $result           = $onedriveBusiness->moveFileWithInfo($id_file, $cloudId);

            if ($result) {
                $modelOnedriveBusiness = $this->getModel('onedrivebusinessfiles');
                $fileOnedriveBusiness  = $modelOnedriveBusiness->getFile($id_file);
                if ($result) {
                    $modelOnedriveBusiness->deleteFile($id_file);
                    $user                       = JFactory::getUser();
                    $file['description']        = (isset($fileOnedriveBusiness->description)) ? $fileOnedriveBusiness->description : '';
                    $file['file_tags']          = (isset($fileOnedriveBusiness->file_tags)) ? $fileOnedriveBusiness->file_tags : '';
                    $file_data                  = array();
                    $file_data['id']            = 0;
                    $file_data['title']         = (isset($result['title'])) ? $result['title'] : $fileOnedriveBusiness->title;
                    $file_data['file_id']       = (isset($result['id'])) ? $result['id'] : $fileOnedriveBusiness->file_id;
                    $file_data['ext']           = (isset($fileOnedriveBusiness->ext)) ? $fileOnedriveBusiness->ext : '';
                    $file_data['description']   = $file['description'];
                    $file_data['size']          = (isset($result['size'])) ? $result['size'] : $fileOnedriveBusiness->size;
                    $file_data['catid']         = $target_category->cloud_id;
                    $file_data['path']          = '';
                    $file_data['created_time']  = date('Y-m-d H:i:s', strtotime($result['created_time']->format('Y-m-d H:i:s')));
                    $file_data['modified_time'] = date('Y-m-d H:i:s', strtotime($result['modified_time']->format('Y-m-d H:i:s')));
                    $file_data['file_tags']     = $file['file_tags'];
                    $file_data['author']        = $user->get('id');
                    $modelOnedriveBusiness->save($file_data);
                }
            }
        } elseif ($active_category->type === 'googledrive' && $target_category->type === 'onedrivebusiness') {
            $google         = new DropfilesGoogle();
            $file           = $google->download($id_file, $active_category->cloud_id, false, 0, true);
            $catpath_dest   = DropfilesBase::getFilesPath($id_category);

            if (!file_exists($catpath_dest)) {
                JFolder::create($catpath_dest);
                $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                JFile::write($catpath_dest . 'index.html', $data);
                $data = 'deny from all';
                JFile::write($catpath_dest . '.htaccess', $data);
            }

            $newname = uniqid() . '.' . $file->ext;
            file_put_contents($catpath_dest . $newname, $file->datas);

            $file_current       = $catpath_dest . $newname;
            $onedriveBusiness   = new DropfilesOneDriveBusiness();

            $pic            = array();
            $pic['error']   = 0;
            $pic['name']    = $file->title . '.' . $file->ext;
            $pic['type']    = '';
            $pic['size']    = $file->size;
            $f_name         = $file->title . '.' . $file->ext;
            $user           = JFactory::getUser();
            $result         = $onedriveBusiness->uploadFile($f_name, $pic, $file_current, $target_category->cloud_id);

            if ($result) {
                $modelOnedriveBusiness          = $this->getModel('onedrivebusinessfiles');
                $modelGoogle                    = $this->getModel('googlefiles');
                $filegoogle                     = $modelGoogle->getFile($id_file);
                $result['file']['description']  = (isset($filegoogle->description)) ? $filegoogle->description : '';
                $result['file']['file_tags']    = (isset($filegoogle->file_tags)) ? $filegoogle->file_tags : '';
                $file_data                      = array();
                $file_data['id']                = 0;
                $file_data['title']             = JFile::stripExt($result['file']['name']);
                $file_data['file_id']           = $result['file']['id'];
                $file_data['ext']               = $file->ext;
                $file_data['description']       = $result['file']['description'];
                $file_data['size']              = $result['file']['size'];
                $file_data['catid']             = $target_category->cloud_id;
                $file_data['path']              = '';
                $file_data['created_time']      = date('Y-m-d H:i:s', strtotime($result['file']['createdDateTime']));
                $file_data['modified_time']     = date('Y-m-d H:i:s', strtotime($result['file']['lastModifiedDateTime']));
                $file_data['author']            = $user->get('id');
                $file_data['file_tags']         = $result['file']['file_tags'];
                $modelOnedriveBusiness->save($file_data);

                JFile::delete($file_current);

                if ($google->delete($id_file, $active_category->cloud_id)) {
                    $modelGoogle->deleteFile($id_file);
                }
            }
        } elseif ($active_category->type === 'onedrivebusiness' && $target_category->type === 'googledrive') {
            $onedriveBusiness   = new DropfilesOneDriveBusiness();
            $file               = $onedriveBusiness->downloadFile($id_file);
            $catpath_dest       = DropfilesBase::getFilesPath($id_category);

            if (!file_exists($catpath_dest)) {
                JFolder::create($catpath_dest);
                $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                JFile::write($catpath_dest . 'index.html', $data);
                $data = 'deny from all';
                JFile::write($catpath_dest . '.htaccess', $data);
            }

            $newname        = uniqid() . '.' . $file->ext;
            file_put_contents($catpath_dest . $newname, $file->datas);
            $file_current   = $catpath_dest . $newname;
            $google         = new DropfilesGoogle();
            $f_name         = $file->title . '.' . $file->ext;
            $fg_contents    = file_get_contents($file_current);
            $insertedFile   = $google->uploadFile($f_name, $fg_contents, '', $target_category->cloud_id);

            if ($insertedFile) {
                $modelGoogle            = $this->getModel('googlefiles');
                $modelOnedriveBusiness  = $this->getModel('onedrivebusinessfiles');
                $fileOnedriveBusiness   = $modelOnedriveBusiness->getFile($id_file);
                $user                   = JFactory::getUser();
                $file_data              = $google->getFileObj($insertedFile);
                $file_data->ext         = (isset($fileOnedriveBusiness->ext)) ? $fileOnedriveBusiness->ext : '';
                $file_data->description = (isset($fileOnedriveBusiness->description)) ? $fileOnedriveBusiness->description : '';
                $file_data->catid       = $target_category->cloud_id;
                $file_data->file_tags   = (isset($fileOnedriveBusiness->file_tags)) ? $fileOnedriveBusiness->file_tags : '';
                $file_data->author      = $user->get('id');
                unset($file_data->id);
                $modelGoogle->addFile($file_data);
                JFile::delete($file_current);

                if ($onedriveBusiness->delete($id_file)) {
                    $modelOnedriveBusiness->deleteFile($id_file);
                }
            }
        } elseif ($active_category->type === 'dropbox' && $target_category->type === 'onedrivebusiness') {
            $dropbox            = new DropfilesDropbox();
            list($tem, $file)   = $dropbox->downloadDropbox($id_file);
            $catpath_dest       = DropfilesBase::getFilesPath($id_category);

            if (!file_exists($catpath_dest)) {
                JFolder::create($catpath_dest);
                $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                JFile::write($catpath_dest . 'index.html', $data);
                $data = 'deny from all';
                JFile::write($catpath_dest . '.htaccess', $data);
            }

            $newname = uniqid() . '.' . JFile::getExt($file['name']);

            ob_start();
            header('Content-Disposition: attachment; filename="' . $file['name'] . '"');
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');

            header('Content-Length: ' . (int)$file['size']);

            echo readfile($tem);
            unlink($tem);
            $data               = ob_get_clean();
            $file_current       = $catpath_dest . $newname;
            file_put_contents($file_current, $data);
            $onedriveBusiness   = new DropfilesOneDriveBusiness();
            $pic                = array();
            $pic['error']       = 0;
            $pic['name']        = $file['name'];
            $pic['type']        = '';
            $pic['size']        = $file['size'];
            $f_name             = $file['name'];
            $result             = $onedriveBusiness->uploadFile($f_name, $pic, $file_current, $target_category->cloud_id);

            if ($result) {
                $modelOnedriveBusiness          = $this->getModel('onedrivebusinessfiles');
                $modelDropbox                   = $this->getModel('dropboxfiles');
                $filedropbox                    = $modelDropbox->getFile($id_file);
                $user                           = JFactory::getUser();
                $result['file']['description']  = (isset($filedropbox->description)) ? $filedropbox->description : '';
                $result['file']['file_tags']    = (isset($filedropbox->file_tags)) ? $filedropbox->file_tags : '';
                $file_data                      = array();
                $file_data['id']                = 0;
                $file_data['title']             = JFile::stripExt($result['file']['name']);
                $file_data['file_id']           = $result['file']['id'];
                $file_data['ext']               = JFile::getExt($file['name']);
                $file_data['description']       = $result['file']['description'];
                $file_data['size']              = $result['file']['size'];
                $file_data['catid']             = $target_category->cloud_id;
                $file_data['path']              = '';
                $file_data['created_time']      = date('Y-m-d H:i:s', strtotime($result['file']['createdDateTime']));
                $file_data['modified_time']     = date('Y-m-d H:i:s', strtotime($result['file']['lastModifiedDateTime']));
                $file_data['file_tags']         = $result['file']['file_tags'];
                $file_data['author']            = $user->get('id');
                $modelOnedriveBusiness->save($file_data);

                JFile::delete($file_current);

                if ($dropbox->deleteFileDropbox($id_file)) {
                    $modelDropbox->deleteFile($id_file);
                }
            }
        } elseif ($active_category->type === 'onedrivebusiness' && $target_category->type === 'dropbox') {
            $onedriveBusiness   = new DropfilesOneDriveBusiness();
            $file               = $onedriveBusiness->downloadFile($id_file);
            $catpath_dest       = DropfilesBase::getFilesPath($id_category);

            if (!file_exists($catpath_dest)) {
                JFolder::create($catpath_dest);
                $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                JFile::write($catpath_dest . 'index.html', $data);
                $data = 'deny from all';
                JFile::write($catpath_dest . '.htaccess', $data);
            }

            $newname        = uniqid() . '.' . $file->ext;
            file_put_contents($catpath_dest . $newname, $file->datas);
            $file_current   = $catpath_dest . $newname;
            $dropbox        = new DropfilesDropbox();
            $f_name         = $file->title . '.' . $file->ext;
            $result         = $dropbox->uploadFile($f_name, $file_current, filesize($file_current), $target_category->path);

            if ($result) {
                $modelDropbox               = $this->getModel('dropboxfiles');
                $modelOnedriveBusiness      = $this->getModel('onedrivebusinessfiles');
                $fileOnedriveBusiness       = $modelOnedriveBusiness->getFile($id_file);
                $result['description']      = (isset($fileOnedriveBusiness->description)) ? $fileOnedriveBusiness->description : '';
                $result['file_tags']        = (isset($fileOnedriveBusiness->file_tags)) ? $fileOnedriveBusiness->file_tags : '';
                $file_data                  = array();
                $file_data['id']            = 0;
                $file_data['title']         = JFile::stripExt($result['name']);
                $file_data['file_id']       = $result['id'];
                $file_data['ext']           = (isset($fileOnedriveBusiness->ext)) ? $fileOnedriveBusiness->ext : '';
                $file_data['description']   = $result['description'];
                $file_data['size']          = $result['size'];
                $file_data['catid']         = $target_category->cloud_id;
                $file_data['path']          = $result['path_lower'];
                $file_data['created_time']  = date('Y-m-d H:i:s', strtotime($result['client_modified']));
                $file_data['modified_time'] = date('Y-m-d H:i:s', strtotime($result['server_modified']));
                $file_data['file_tags']     = $result['file_tags'];
                $modelDropbox->save($file_data);

                JFile::delete($file_current);

                if ($onedriveBusiness->delete($id_file)) {
                    $modelOnedriveBusiness->deleteFile($id_file);
                }
            }
        } elseif ($active_category->type === 'onedrive' && $target_category->type === 'onedrivebusiness') {
            $onedrive = new DropfilesOneDrive();
            $file     = $onedrive->downloadFile($id_file);

            $catpath_dest = DropfilesBase::getFilesPath($id_category);

            if (!file_exists($catpath_dest)) {
                JFolder::create($catpath_dest);
                $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                JFile::write($catpath_dest . 'index.html', $data);
                $data = 'deny from all';
                JFile::write($catpath_dest . '.htaccess', $data);
            }

            $newname      = uniqid() . '.' . $file->ext;
            $file_current = $catpath_dest . $newname;
            file_put_contents($file_current, $file->datas);

            $onedriveBusiness = new DropfilesOneDriveBusiness();

            $pic              = array();
            $pic['error']     = 0;
            $pic['name']      = $file->title . '.' . $file->ext;
            $pic['type']      = '';
            $pic['size']      = $file->size;
            $f_name           = $file->title . '.' . $file->ext;

            $result = $onedriveBusiness->uploadFile($f_name, $pic, $file_current, $target_category->cloud_id);
            if ($result) {
                $modelOnedriveBusiness              = $this->getModel('onedrivebusinessfiles');
                $modelOnedrive                      = $this->getModel('onedrivefiles');
                $fileOneDrive                       = $modelOnedrive->getFile($id_file);
                $user                               = JFactory::getUser();
                $result['file']['description']      = (isset($fileOneDrive->description)) ? $fileOneDrive->description : '';
                $result['file']['file_tags']        = (isset($fileOneDrive->file_tags)) ? $fileOneDrive->file_tags : '';
                $file_data                          = array();
                $file_data['id']                    = 0;
                $file_data['title']                 = JFile::stripExt($result['file']['name']);
                $file_data['file_id']               = $result['file']['id'];
                $file_data['ext']                   = (isset($fileOneDrive->ext)) ? $fileOneDrive->ext : '';
                $file_data['description']           = $result['file']['description'];
                $file_data['size']                  = $result['file']['size'];
                $file_data['catid']                 = $target_category->cloud_id;
                $file_data['path']                  = '';
                $file_data['created_time']          = date('Y-m-d H:i:s', strtotime($result['file']['createdDateTime']));
                $file_data['modified_time']         = date('Y-m-d H:i:s', strtotime($result['file']['lastModifiedDateTime']));
                $file_data['file_tags']             = $result['file']['file_tags'];
                $file_data['author']                = $user->get('id');
                $modelOnedriveBusiness->save($file_data);

                JFile::delete($file_current);

                if ($onedrive->delete($id_file, $active_category->cloud_id)) {
                    $modelOnedrive->deleteFile($id_file);
                }
            }
        } elseif ($active_category->type === 'onedrivebusiness' && $target_category->type === 'onedrive') {
            $onedriveBusiness   = new DropfilesOneDriveBusiness();
            $file               = $onedriveBusiness->downloadFile($id_file);
            $catpath_dest       = DropfilesBase::getFilesPath($id_category);

            if (!file_exists($catpath_dest)) {
                JFolder::create($catpath_dest);
                $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                JFile::write($catpath_dest . 'index.html', $data);
                $data = 'deny from all';
                JFile::write($catpath_dest . '.htaccess', $data);
            }

            $newname       = uniqid() . '.' . $file->ext;
            file_put_contents($catpath_dest . $newname, $file->datas);

            $onedrive     = new DropfilesOneDrive();
            $file_current  = $catpath_dest . $newname;

            $pic          = array();
            $pic['error'] = 0;
            $pic['name']  = $file->title . '.' . $file->ext;
            $pic['type']  = '';
            $pic['size']  = $file->size;
            $f_name       = $file->title . '.' . $file->ext;

            $result = $onedrive->uploadFile($f_name, $pic, $file_current, $target_category->cloud_id);
            $user   = JFactory::getUser();
            if ($result) {
                $modelOnedrive                      = $this->getModel('onedrivefiles');
                $modelOnedriveBusiness              = $this->getModel('onedrivebusinessfiles');
                $fileonedrivebusiness               = $modelOnedriveBusiness->getFile($id_file);
                $result['file']['description']      = (isset($fileonedrivebusiness->description)) ? $fileonedrivebusiness->description : '';
                $result['file']['file_tags']        = (isset($fileonedrivebusiness->file_tags)) ? $fileonedrivebusiness->file_tags : '';
                $file_data                          = array();
                $file_data['id']                    = 0;
                $file_data['title']                 = JFile::stripExt($result['file']['name']);
                $file_data['file_id']               = $result['file']['id'];
                $file_data['ext']                   = (isset($file->ext)) ? $file->ext : '';
                $file_data['description']           = $result['file']['description'];
                $file_data['size']                  = $result['file']['size'];
                $file_data['catid']                 = $target_category->cloud_id;
                $file_data['path']                  = '';
                $file_data['created_time']          = date('Y-m-d H:i:s', strtotime($result['file']['createdDateTime']));
                $file_data['modified_time']         = date('Y-m-d H:i:s', strtotime($result['file']['lastModifiedDateTime']));
                $file_data['file_tags']             = $result['file']['file_tags'];
                $file_data['author']                = $user->get('id');
                $modelOnedrive->save($file_data);


                JFile::delete($file_current);

                if ($onedriveBusiness->delete($id_file)) {
                    $modelOnedriveBusiness->deleteFile($id_file);
                }
            }
        }

        // Update files count
        $categoriesModel = $this->getModel('Categories', 'DropfilesModel');
        $categoriesModel->updateFilesCount();
        $this->exitStatus(true, array('id_file' => $id_file));
        JFactory::getApplication()->close();
    }

    /**
     * Copy file in category
     *
     * @return void
     * @throws \Exception Throw when application can not start
     * @since  version
     */
    public function copyfile()
    {
        $input              = JFactory::getApplication()->input;
        $id_category        = $input->getInt('id_category', 0);
        $active_category_id = $input->getInt('active_category', 0);
        $id_file            = $input->getString('id_file', null);

        if (!$this->allowEdit()) {
            $this->exitStatus(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
        }

        $model           = $this->getModel();
        $modelFile       = $this->getModel('file');
        $catpath_dest    = DropfilesBase::getFilesPath($id_category);
        $file            = $modelFile->getItem($id_file);
        $catpath_current = DropfilesBase::getFilesPath($file->catid);

        $modelCategory   = $this->getModel('category');
        $active_category = $modelCategory->getCategory($active_category_id);
        $target_category = $modelCategory->getCategory($id_category);

        if ($active_category->type === 'default' && $target_category->type === 'default') {
            if ($file->catid !== $id_category) {
                $data                = array();
                $newname             = uniqid() . '.' . strtolower(JFile::getExt($file->file));
                $user                = JFactory::getUser();
                $data['file']        = $newname;
                $data['id_category'] = $id_category;
                $data['title']       = $file->title;
                $data['ext']         = $file->ext;
                $data['description'] = $file->description;
                $data['file_tags']   = $file->file_tags;
                $data['size']        = $file->size;
                $data['author']      = $user->get('id');
                $new_file = $model->addFile($data);
                if ($new_file) {
                    // move file
                    $file_current = $catpath_current . $file->file;
                    $file_dest    = $catpath_dest . $newname;

                    if (!file_exists($catpath_dest)) {
                        JFolder::create($catpath_dest);
                        $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                        JFile::write($catpath_dest . 'index.html', $data);
                        $data = 'deny from all';
                        JFile::write($catpath_dest . '.htaccess', $data);
                    }

                    if (is_file($file_current)) {
                        JFile::copy($file_current, $file_dest);
                    }
                    // Index new uploaded file
                    $ftsModel = $this->getModel('fts');
                    $ftsModel->reIndexFile($id_file);
                }
            }
        } elseif ($active_category->type === 'googledrive' && $target_category->type === 'default') {
            $google       = new DropfilesGoogle();
            $file         = $google->download($id_file, $active_category->cloud_id, false, 0, true);
            $catpath_dest = DropfilesBase::getFilesPath($id_category);

            if (!file_exists($catpath_dest)) {
                JFolder::create($catpath_dest);
                $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                JFile::write($catpath_dest . 'index.html', $data);
                $data = 'deny from all';
                JFile::write($catpath_dest . '.htaccess', $data);
            }

            $newname = uniqid() . '.' . $file->ext;
            file_put_contents($catpath_dest . $newname, $file->datas);

            $model              = $this->getModel();
            $modelGoogle        = $this->getModel('googlefiles');
            $filegoogle         = $modelGoogle->getFile($id_file);
            $file->description  = (isset($filegoogle->description)) ? $filegoogle->description : '';
            $file->file_tags    = (isset($filegoogle->file_tags)) ? $filegoogle->file_tags : '';
            $user               = JFactory::getUser();

            $new_file = $model->addFile(array(
                'title'       => $file->title,
                'id_category' => $id_category,
                'file'        => $newname,
                'ext'         => $file->ext,
                'description' => $file->description,
                'size'        => $file->size,
                'file_tags'   => $file->file_tags,
                'author'      => $user->get('id')
            ));
            if ($new_file) {
                // Index new uploaded file
                $ftsModel = $this->getModel('fts');
                $ftsModel->reIndexFile($new_file);
            }
        } elseif ($active_category->type === 'default' && $target_category->type === 'googledrive') {
            $google          = new DropfilesGoogle();
            $file            = $modelFile->getItem($id_file);
            $catpath_current = DropfilesBase::getFilesPath($file->catid);
            $file_current    = $catpath_current . $file->file;
            $fg_contents     = file_get_contents($file_current);
            if (!$google->uploadFile($file->title . '.' . $file->ext, $fg_contents, '', $target_category->cloud_id)) {
                $this->exitStatus($google->getLastError());
            }
            $insertedFile = $google->uploadFile($file->title . '.' . $file->ext, $fg_contents, '', $target_category->cloud_id);
            if ($insertedFile) {
                $modelGoogle                = $this->getModel('googlefiles');
                $user                       = JFactory::getUser();
                $file_data                  = $google->getFileObj($insertedFile);
                $file_data->description     = (isset($file->description)) ? $file->description : '';
                $file_data->catid           = $target_category->cloud_id;
                $file_data->file_tags       = (isset($file->file_tags)) ? $file->file_tags : '';
                $file_data->author          = $user->get('id');
                unset($file_data->id);
                $modelGoogle->addFile($file_data);
                JFile::delete($file_current);
            }
        } elseif ($active_category->type === 'googledrive' && $target_category->type === 'googledrive') {
            $google = new DropfilesGoogle();
            $file = $google->copyFile($id_file, $target_category->cloud_id);
            // Add new file to database
            if ($file) {
                $user                   = JFactory::getUser();
                $modelGoogle            = $this->getModel('googlefiles');
                $filegoogle             = $modelGoogle->getFile($id_file);
                $file_data              = $google->getFileObj($file);
                $file_data->catid       = $target_category->cloud_id;
                $file_data->ext         = (isset($filegoogle->ext)) ? $filegoogle->ext : '';
                $file_data->description = (isset($filegoogle->description)) ? $filegoogle->description : '';
                $file_data->file_tags   = (isset($filegoogle->file_tags)) ? $filegoogle->file_tags : '';
                $file_data->author      = $user->get('id');
                unset($file_data->id);
                $modelGoogle->addFile($file_data);
            }
        } elseif ($active_category->type === 'dropbox' && $target_category->type === 'default') {
            $dropbox = new DropfilesDropbox();
            $modelDropbox = $this->getModel('dropboxfiles');
            $filedropbox = $modelDropbox->getFile($id_file);
            list($tem, $file) = $dropbox->downloadDropbox($id_file);

            $catpath_dest = DropfilesBase::getFilesPath($id_category);

            if (!file_exists($catpath_dest)) {
                JFolder::create($catpath_dest);
                $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                JFile::write($catpath_dest . 'index.html', $data);
                $data = 'deny from all';
                JFile::write($catpath_dest . '.htaccess', $data);
            }

            $newname = uniqid() . '.' . JFile::getExt($file['name']);

            ob_start();
            header('Content-Disposition: attachment; filename="' . $file['name'] . '"');
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');

            header('Content-Length: ' . (int) $file['size']);

            echo readfile($tem);
            unlink($tem);
            $data = ob_get_clean();
            file_put_contents($catpath_dest . $newname, $data);
            $user     = JFactory::getUser();
            $model    = $this->getModel();
            $file['description'] = (isset($filedropbox->description)) ? $filedropbox->description : '';
            $file['file_tags']   = (isset($filedropbox->file_tags)) ? $filedropbox->file_tags : '';
            $new_file = $model->addFile(array(
                'title'       => JFile::stripExt($file['name']),
                'id_category' => $id_category,
                'file'        => $newname,
                'ext'         => strtolower(JFile::getExt($file['name'])),
                'description' => $file['description'],
                'size'        => $file['size'],
                'author'      => $user->get('id'),
                'file_tags'   => $file['file_tags']
            ));
        } elseif ($active_category->type === 'default' && $target_category->type === 'dropbox') {
            $file            = $modelFile->getItem($id_file);
            $catpath_current = DropfilesBase::getFilesPath($file->catid);
            $file_current    = $catpath_current . $file->file;
            $dropbox         = new DropfilesDropbox();

            $f_name = $file->title . '.' . $file->ext;
            $result = $dropbox->uploadFile($f_name, $file_current, filesize($file_current), $target_category->path);

            if ($result) {
                $modelDropbox               = $this->getModel('dropboxfiles');
                $file_data                  = array();
                $result['description']      = (isset($file->description)) ? $file->description : '';
                $result['file_tags']        = (isset($file->file_tags)) ? $file->file_tags : '';
                $file_data['id']            = 0;
                $file_data['title']         = JFile::stripExt($result['name']);
                $file_data['file_id']       = $result['id'];
                $file_data['ext']           = strtolower(JFile::getExt($result['name']));
                $file_data['description']   = $result['description'];
                $file_data['size']          = $result['size'];
                $file_data['catid']         = $target_category->cloud_id;
                $file_data['file_tags']     = $result['file_tags'];
                $file_data['path']          = $result['path_lower'];
                $file_data['created_time']  = date('Y-m-d H:i:s', strtotime($result['client_modified']));
                $file_data['modified_time'] = date('Y-m-d H:i:s', strtotime($result['server_modified']));
                $modelDropbox->save($file_data);
            }
        } elseif ($active_category->type === 'dropbox' && $target_category->type === 'dropbox') {
            $dropbox = new DropfilesDropbox();
            $file    = $dropbox->getDropboxFileInfos($id_file);

            $path_target_file = $target_category->path . '/' . strtolower($file['name']);
            $result           = $dropbox->copyFileDropbox($file['path_lower'], $path_target_file);
            if ($result) {
                $modelDropbox               = $this->getModel('dropboxfiles');
                $filedropbox                = $modelDropbox->getFile($id_file);
                $result['description']      = (isset($filedropbox->description)) ? $filedropbox->description : '';
                $result['file_tags']        = (isset($filedropbox->file_tags)) ? $filedropbox->file_tags : '';
                $file_data                  = array();
                $file_data['id']            = 0;
                $file_data['title']         = JFile::stripExt($result['name']);
                $file_data['file_id']       = $result['id'];
                $file_data['ext']           = strtolower(JFile::getExt($result['name']));
                $file_data['description']   = $result['description'];
                $file_data['size']          = $result['size'];
                $file_data['catid']         = $target_category->cloud_id;
                $file_data['file_tags']     = $result['file_tags'];
                $file_data['path']          = $result['path_lower'];
                $file_data['created_time']  = date('Y-m-d H:i:s', strtotime($result['client_modified']));
                $file_data['modified_time'] = date('Y-m-d H:i:s', strtotime($result['server_modified']));
                $modelDropbox->save($file_data);
            }
        } elseif ($active_category->type === 'googledrive' && $target_category->type === 'dropbox') {
            $google = new DropfilesGoogle();
            $file   = $google->download($id_file, $active_category->cloud_id, false, 0, true);

            $catpath_dest = DropfilesBase::getFilesPath($id_category);

            if (!file_exists($catpath_dest)) {
                JFolder::create($catpath_dest);
                $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                JFile::write($catpath_dest . 'index.html', $data);
                $data = 'deny from all';
                JFile::write($catpath_dest . '.htaccess', $data);
            }

            $newname = uniqid() . '.' . $file->ext;
            file_put_contents($catpath_dest . $newname, $file);

            $dropbox = new DropfilesDropbox();

            $file_current = $catpath_dest . $newname;
            $f_name       = $file->title . '.' . $file->ext;
            $result       = $dropbox->uploadFile($f_name, $file_current, filesize($file_current), $target_category->path);
            if ($result) {
                $modelDropbox               = $this->getModel('dropboxfiles');
                $modelGoogle                = $this->getModel('googlefiles');
                $filegoogle                 = $modelGoogle->getFile($id_file);
                $result['size']             = (isset($filegoogle->size)) ? $filegoogle->size : 0;
                $result['description']      = (isset($filegoogle->description)) ? $filegoogle->description : '';
                $result['file_tags']        = (isset($filegoogle->file_tags)) ? $filegoogle->file_tags : '';
                $file_data                  = array();
                $file_data['id']            = 0;
                $file_data['title']         = JFile::stripExt($result['name']);
                $file_data['file_id']       = $result['id'];
                $file_data['ext']           = strtolower(JFile::getExt($result['name']));
                $file_data['description']   = $result['description'];
                $file_data['size']          = $result['size'];
                $file_data['catid']         = $target_category->cloud_id;
                $file_data['file_tags']     = $result['file_tags'];
                $file_data['path']          = $result['path_lower'];
                $file_data['created_time']  = date('Y-m-d H:i:s', strtotime($result['client_modified']));
                $file_data['modified_time'] = date('Y-m-d H:i:s', strtotime($result['server_modified']));
                $modelDropbox->save($file_data);

                JFile::delete($file_current);
            }
        } elseif ($active_category->type === 'dropbox' && $target_category->type === 'googledrive') {
            $dropbox = new DropfilesDropbox();
            list($tem, $file) = $dropbox->downloadDropbox($id_file);

            $catpath_dest = DropfilesBase::getFilesPath($id_category);

            if (!file_exists($catpath_dest)) {
                JFolder::create($catpath_dest);
                $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                JFile::write($catpath_dest . 'index.html', $data);
                $data = 'deny from all';
                JFile::write($catpath_dest . '.htaccess', $data);
            }

            $newname = uniqid() . '.' . JFile::getExt($file['name']);

            ob_start();
            header('Content-Disposition: attachment; filename="' . $file['name'] . '"');
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');

            header('Content-Length: ' . (int) $file['size']);

            echo readfile($tem);
            unlink($tem);
            $data         = ob_get_clean();
            $file_current = $catpath_dest . $newname;
            file_put_contents($file_current, $data);
            $google      = new DropfilesGoogle();
            $fg_contents = file_get_contents($file_current);
            $insertedFile = $google->uploadFile($file['name'], $fg_contents, '', $target_category->cloud_id);

            if ($insertedFile) {
                $modelGoogle                = $this->getModel('googlefiles');
                $modelDropbox               = $this->getModel('dropboxfiles');
                $filedropbox                = $modelDropbox->getFile($id_file);
                $user                       = JFactory::getUser();
                $file_data                  = $google->getFileObj($insertedFile);
                $file_data->description     = (isset($filedropbox->description)) ? $filedropbox->description : '';
                $file_data->catid           = $target_category->cloud_id;
                $file_data->author          = $user->get('id');
                $file_data->file_tags       = (isset($filedropbox->file_tags)) ? $filedropbox->file_tags : '';
                unset($file_data->id);
                $modelGoogle->addFile($file_data);
                JFile::delete($file_current);
            }
        } elseif ($active_category->type === 'onedrive' && $target_category->type === 'default') {
            $onedrive = new DropfilesOneDrive();
            $file     = $onedrive->downloadFile($id_file);

            $catpath_dest = DropfilesBase::getFilesPath($id_category);

            if (!file_exists($catpath_dest)) {
                JFolder::create($catpath_dest);
                $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                JFile::write($catpath_dest . 'index.html', $data);
                $data = 'deny from all';
                JFile::write($catpath_dest . '.htaccess', $data);
            }

            $newname = uniqid() . '.' . $file->ext;
            file_put_contents($catpath_dest . $newname, $file->datas);

            $user     = JFactory::getUser();
            $model    = $this->getModel();
            $modelOnedrive = $this->getModel('onedrivefiles');
            $fileonedrive  = $modelOnedrive->getFile($id_file);
            $new_file = $model->addFile(array(
                'title'             => $file->title,
                'id_category'       => $id_category,
                'file'              => $newname,
                'ext'               => (isset($fileonedrive->ext)) ? $fileonedrive->ext : '',
                'description'       => (isset($fileonedrive->description)) ? $fileonedrive->description : '',
                'size'              => $file->size,
                'file_tags'         => (isset($fileonedrive->file_tags)) ? $fileonedrive->file_tags : '',
                'author'            => $user->get('id')
            ));
            if ($new_file) {
                // Index new uploaded file
                $ftsModel = $this->getModel('fts');
                $ftsModel->reIndexFile($new_file);
            }
        } elseif ($active_category->type === 'default' && $target_category->type === 'onedrive') {
            $file            = $modelFile->getItem($id_file);
            $catpath_current = DropfilesBase::getFilesPath($file->catid);
            $file_current    = $catpath_current . $file->file;
            $onedrive        = new DropfilesOneDrive();

            $pic          = array();
            $pic['error'] = 0;
            $pic['name']  = $file->title . '.' . $file->ext;
            $pic['type']  = '';
            $pic['size']  = $file->size;

            $f_name = $file->title . '.' . $file->ext;
            $result = $onedrive->uploadFile($f_name, $pic, $file_current, $target_category->cloud_id);
            $user   = JFactory::getUser();
            if ($result) {
                $modelOnedrive                          = $this->getModel('onedrivefiles');
                $result['file']['description']      = (isset($file->description)) ? $file->description : '';
                $result['file']['file_tags']        = (isset($file->file_tags)) ? $file->file_tags : '';
                $file_data                          = array();
                $file_data['id']                    = 0;
                $file_data['title']                 = JFile::stripExt($result['file']['name']);
                $file_data['file_id']               = $result['file']['id'];
                $file_data['ext']                   = $file->ext;
                $file_data['description']           = $result['file']['description'];
                $file_data['size']                  = $result['file']['size'];
                $file_data['catid']                 = $target_category->cloud_id;
                $file_data['path']                  = '';
                $file_data['created_time']          = date('Y-m-d H:i:s', strtotime($result['file']['createdDateTime']));
                $file_data['modified_time']         = date('Y-m-d H:i:s', strtotime($result['file']['lastModifiedDateTime']));
                $file_data['author']                = $user->get('id');
                $file_data['file_tags']             = $result['file']['file_tags'];
                $modelOnedrive->save($file_data);
            }
        } elseif ($active_category->type === 'onedrive' && $target_category->type === 'onedrive') {
            $onedrive = new DropfilesOneDrive();
            $result   = $onedrive->copyFile($id_file, $target_category->cloud_id);
            //todo
            $response = array('onedrive_catkey' => $target_category->cloud_id, 'location' => $result);
            sleep(1);
            $bodyRequest = $onedrive->getResponseBodyRequest($result);
            if ($bodyRequest->status === 'completed') {
                $resourceId = DropfilesCloudHelper::replaceIdOneDrive($bodyRequest->resourceId);
                $file       = $onedrive->getOneDriveFileInfos($resourceId, $target_category->cloud_id);
                $user       = JFactory::getUser();
                if ($file) {
                    $modelOnedrive              = $this->getModel('onedrivefiles');
                    $fileonedrive               = $modelOnedrive->getFile($id_file);
                    $file['description']        = (isset($fileonedrive->description)) ? $fileonedrive->description : '';
                    $file['file_tags']          = (isset($fileonedrive->file_tags)) ? $fileonedrive->file_tags : '';
                    $file_data                  = array();
                    $file_data['id']            = 0;
                    $file_data['title']         = $file['title'];
                    $file_data['file_id']       = $file['id'];
                    $file_data['ext']           = (isset($fileonedrive->ext)) ? $fileonedrive->ext : '';
                    $file_data['description']   = $file['description'];
                    $file_data['size']          = $file['size'];
                    $file_data['catid']         = $target_category->cloud_id;
                    $file_data['path']          = '';
                    $file_data['created_time']  = $file['created_time'];
                    $file_data['modified_time'] = $file['modified_time'];
                    $file_data['file_tags']     = $file['file_tags'];
                    $file_data['author']        = $user->get('id');
                    $modelOnedrive->save($file_data);
                }
            } else {
                $this->exitStatus($response);
                JFactory::getApplication()->close();
            }
        } elseif ($active_category->type === 'googledrive' && $target_category->type === 'onedrive') {
            $google = new DropfilesGoogle();
            $file   = $google->download($id_file, $active_category->cloud_id, false, 0, true);

            $catpath_dest = DropfilesBase::getFilesPath($id_category);

            if (!file_exists($catpath_dest)) {
                JFolder::create($catpath_dest);
                $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                JFile::write($catpath_dest . 'index.html', $data);
                $data = 'deny from all';
                JFile::write($catpath_dest . '.htaccess', $data);
            }

            $newname = uniqid() . '.' . $file->ext;
            file_put_contents($catpath_dest . $newname, $file->datas);

            $file_current = $catpath_dest . $newname;

            $onedrive = new DropfilesOneDrive();

            $pic          = array();
            $pic['error'] = 0;
            $pic['name']  = $file->title;
            $pic['type']  = '';
            $pic['size']  = $file->size;
            $f_name       = $file->title;
            $result       = $onedrive->uploadFile($f_name, $pic, $file_current, $target_category->cloud_id);
            $user         = JFactory::getUser();

            if ($result) {
                $modelOnedrive                  = $this->getModel('onedrivefiles');
                $modelGoogle                    = $this->getModel('googlefiles');
                $filegoogle                     = $modelGoogle->getFile($id_file);
                $result['file']['description']  = (isset($filegoogle->description)) ? $filegoogle->description : '';
                $result['file']['file_tags']    = (isset($filegoogle->file_tags)) ? $filegoogle->file_tags : '';
                $file_data                      = array();
                $file_data['id']                = 0;
                $file_data['title']             = JFile::stripExt($result['file']['name']);
                $file_data['file_id']           = $result['file']['id'];
                $file_data['ext']               = $file->ext;
                $file_data['description']       = $result['file']['description'];
                $file_data['size']              = $result['file']['size'];
                $file_data['catid']             = $target_category->cloud_id;
                $file_data['path']              = '';
                $file_data['created_time']      = date('Y-m-d H:i:s', strtotime($result['file']['createdDateTime']));
                $file_data['modified_time']     = date('Y-m-d H:i:s', strtotime($result['file']['lastModifiedDateTime']));
                $file_data['file_tags']         = $result['file']['file_tags'];
                $file_data['author']            = $user->get('id');
                $modelOnedrive->save($file_data);

                JFile::delete($file_current);
            }
        } elseif ($active_category->type === 'onedrive' && $target_category->type === 'googledrive') {
            $onedrive = new DropfilesOneDrive();
            $file     = $onedrive->downloadFile($id_file);

            $catpath_dest = DropfilesBase::getFilesPath($id_category);

            if (!file_exists($catpath_dest)) {
                JFolder::create($catpath_dest);
                $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                JFile::write($catpath_dest . 'index.html', $data);
                $data = 'deny from all';
                JFile::write($catpath_dest . '.htaccess', $data);
            }

            $newname      = uniqid() . '.' . $file->ext;
            $file_current = $catpath_dest . $newname;
            file_put_contents($file_current, $file->datas);
            $google = new DropfilesGoogle();

            $f_name       = $file->title . '.' . $file->ext;
            $fg_contents  = file_get_contents($file_current);
            $insertedFile = $google->uploadFile($f_name, $fg_contents, '', $target_category->cloud_id);

            if ($insertedFile) {
                $modelGoogle                = $this->getModel('googlefiles');
                $modelOnedrive              = $this->getModel('onedrivefiles');
                $fileonedrive               = $modelOnedrive->getFile($id_file);
                $user                       = JFactory::getUser();
                $file_data                  = $google->getFileObj($insertedFile);
                $file_data->ext             = (isset($fileonedrive->ext)) ? $fileonedrive->ext : '';
                $file_data->description     = (isset($fileonedrive->description)) ? $fileonedrive->description : '';
                $file_data->catid           = $target_category->cloud_id;
                $file_data->file_tags       = (isset($fileonedrive->file_tags)) ? $fileonedrive->file_tags : '';
                $file_data->author          = $user->get('id');
                unset($file_data->id);
                $modelGoogle->addFile($file_data);
                JFile::delete($file_current);
            }
        } elseif ($active_category->type === 'dropbox' && $target_category->type === 'onedrive') {
            $dropbox = new DropfilesDropbox();
            list($tem, $file) = $dropbox->downloadDropbox($id_file);
            $catpath_dest = DropfilesBase::getFilesPath($id_category);

            if (!file_exists($catpath_dest)) {
                JFolder::create($catpath_dest);
                $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                JFile::write($catpath_dest . 'index.html', $data);
                $data = 'deny from all';
                JFile::write($catpath_dest . '.htaccess', $data);
            }

            $newname = uniqid() . '.' . JFile::getExt($file['name']);

            ob_start();
            header('Content-Disposition: attachment; filename="' . $file['name'] . '"');
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');

            header('Content-Length: ' . (int) $file['size']);

            echo readfile($tem);
            unlink($tem);
            $data         = ob_get_clean();
            $file_current = $catpath_dest . $newname;
            file_put_contents($file_current, $data);
            $onedrive     = new DropfilesOneDrive();
            $file_current = $catpath_dest . $newname;

            $pic          = array();
            $pic['error'] = 0;
            $pic['name']  = $file['name'];
            $pic['type']  = '';
            $pic['size']  = $file['size'];
            $f_name       = $file['name'];

            $result = $onedrive->uploadFile($f_name, $pic, $file_current, $target_category->cloud_id);
            $user   = JFactory::getUser();
            if ($result) {
                $modelOnedrive                      = $this->getModel('onedrivefiles');
                $modelDropbox                       = $this->getModel('dropboxfiles');
                $filedropbox                        = $modelDropbox->getFile($id_file);
                $result['file']['description']      = (isset($filedropbox->description)) ? $filedropbox->description : '';
                $result['file']['file_tags']        = (isset($filedropbox->file_tags)) ? $filedropbox->file_tags : '';
                $file_data                          = array();
                $file_data['id']                    = 0;
                $file_data['title']                 = JFile::stripExt($result['file']['name']);
                $file_data['file_id']               = $result['file']['id'];
                $file_data['ext']                   = JFile::getExt($file['name']);
                $file_data['description']           = $result['file']['description'];
                $file_data['size']                  = $result['file']['size'];
                $file_data['catid']                 = $target_category->cloud_id;
                $file_data['path']                  = '';
                $file_data['created_time']          = date('Y-m-d H:i:s', strtotime($result['file']['createdDateTime']));
                $file_data['modified_time']         = date('Y-m-d H:i:s', strtotime($result['file']['lastModifiedDateTime']));
                $file_data['file_tags']             = $result['file']['file_tags'];
                $file_data['author']                = $user->get('id');
                $modelOnedrive->save($file_data);


                JFile::delete($file_current);
            }
        } elseif ($active_category->type === 'onedrive' && $target_category->type === 'dropbox') {
            $onedrive = new DropfilesOneDrive();
            $file     = $onedrive->downloadFile($id_file);

            $catpath_dest = DropfilesBase::getFilesPath($id_category);

            if (!file_exists($catpath_dest)) {
                JFolder::create($catpath_dest);
                $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                JFile::write($catpath_dest . 'index.html', $data);
                $data = 'deny from all';
                JFile::write($catpath_dest . '.htaccess', $data);
            }

            $newname      = uniqid() . '.' . $file->ext;
            $file_current = $catpath_dest . $newname;
            file_put_contents($file_current, $file->datas);

            $dropbox = new DropfilesDropbox();

            $file_current = $catpath_dest . $newname;
            $f_name       = $file->title . '.' . $file->ext;
            $result       = $dropbox->uploadFile($f_name, $file_current, filesize($file_current), $target_category->path);

            if ($result) {
                $modelDropbox               = $this->getModel('dropboxfiles');
                $modelOnedrive              = $this->getModel('onedrivefiles');
                $fileonedrive               = $modelOnedrive->getFile($id_file);
                $result['description']      = (isset($fileonedrive->description)) ? $fileonedrive->description : '';
                $result['file_tags']        = (isset($fileonedrive->file_tags)) ? $fileonedrive->file_tags : '';
                $file_data                  = array();
                $file_data['id']            = 0;
                $file_data['title']         = JFile::stripExt($result['name']);
                $file_data['file_id']       = $result['id'];
                $file_data['ext']           = (isset($fileonedrive->ext)) ? $fileonedrive->ext : '';
                $file_data['description']   = $result['description'];
                $file_data['size']          = $result['size'];
                $file_data['catid']         = $target_category->cloud_id;
                $file_data['path']          = $result['path_lower'];
                $file_data['created_time']  = date('Y-m-d H:i:s', strtotime($result['client_modified']));
                $file_data['modified_time'] = date('Y-m-d H:i:s', strtotime($result['server_modified']));
                $file_data['file_tags']     = $result['file_tags'];
                $modelDropbox->save($file_data);

                JFile::delete($file_current);
            }
        } elseif ($active_category->type === 'onedrivebusiness' && $target_category->type === 'default') {
            $onedriveBusiness   = new DropfilesOneDriveBusiness();
            $file               = $onedriveBusiness->downloadFile($id_file);
            $catpath_dest       = DropfilesBase::getFilesPath($id_category);

            if (!file_exists($catpath_dest)) {
                JFolder::create($catpath_dest);
                $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                JFile::write($catpath_dest . 'index.html', $data);
                $data = 'deny from all';
                JFile::write($catpath_dest . '.htaccess', $data);
            }

            $newname = uniqid() . '.' . $file->ext;
            file_put_contents($catpath_dest . $newname, $file->datas);

            $user                   = JFactory::getUser();
            $model                  = $this->getModel();
            $modelOnedriveBusiness  = $this->getModel('onedrivebusinessfiles');
            $fileOnedriveBusiness   = $modelOnedriveBusiness->getFile($id_file);
            $newFile = $model->addFile(array(
                'title'             => (isset($file->title)) ? $file->title : $fileOnedriveBusiness->title,
                'id_category'       => $id_category,
                'file'              => $newname,
                'ext'               => (isset($fileOnedriveBusiness->ext)) ? $fileOnedriveBusiness->ext : '',
                'description'       => (isset($fileOnedriveBusiness->description)) ? $fileOnedriveBusiness->description : '',
                'size'              => $file->size,
                'file_tags'         => (isset($fileOnedriveBusiness->file_tags)) ? $fileOnedriveBusiness->file_tags : '',
                'author'            => $user->get('id')
            ));

            if ($newFile) {
                // Index new uploaded file
                $ftsModel = $this->getModel('fts');
                $ftsModel->reIndexFile($newFile);
            }
        } elseif ($active_category->type === 'default' && $target_category->type === 'onedrivebusiness') {
            $file               = $modelFile->getItem($id_file);
            $catpath_current    = DropfilesBase::getFilesPath($file->catid);
            $file_current       = $catpath_current . $file->file;
            $onedriveBusiness   = new DropfilesOneDriveBusiness();

            $pic          = array();
            $pic['error'] = 0;
            $pic['name']  = $file->title . '.' . $file->ext;
            $pic['type']  = '';
            $pic['size']  = $file->size;

            $f_name       = $file->title . '.' . $file->ext;
            $user         = JFactory::getUser();
            $result       = $onedriveBusiness->uploadFile($f_name, $pic, $file_current, $target_category->cloud_id);
            if ($result) {
                $modelOnedriveBusiness              = $this->getModel('onedrivebusinessfiles');
                $result['file']['description']      = (isset($file->description)) ? $file->description : '';
                $result['file']['file_tags']        = (isset($file->file_tags)) ? $file->file_tags : '';
                $file_data                          = array();
                $file_data['id']                    = 0;
                $file_data['title']                 = JFile::stripExt($result['file']['name']);
                $file_data['file_id']               = $result['file']['id'];
                $file_data['ext']                   = $file->ext;
                $file_data['description']           = $result['file']['description'];
                $file_data['size']                  = $result['file']['size'];
                $file_data['catid']                 = $target_category->cloud_id;
                $file_data['path']                  = '';
                $file_data['created_time']          = date('Y-m-d H:i:s', strtotime($result['file']['createdDateTime']));
                $file_data['modified_time']         = date('Y-m-d H:i:s', strtotime($result['file']['lastModifiedDateTime']));
                $file_data['author']                = $user->get('id');
                $file_data['file_tags']             = $result['file']['file_tags'];
                $modelOnedriveBusiness->save($file_data);
            }
        } elseif ($active_category->type === 'onedrivebusiness' && $target_category->type === 'onedrivebusiness') {
            $onedriveBusiness   = new DropfilesOneDriveBusiness();
            $cloudId            = DropfilesCloudHelper::getOneDriveBusinessIdByTermId($id_category);
            $result             = $onedriveBusiness->copyFile($id_file, $cloudId);
            if (!empty($result) && isset($result['id'])) {
                $resourceId = DropfilesCloudHelper::replaceIdOneDrive($result['id']);
                $file       = $onedriveBusiness->getOneDriveBusinessFileInfos($resourceId, $target_category->cloud_id);
                $user       = JFactory::getUser();
                if ($file) {
                    $modelOnedriveBusiness      = $this->getModel('onedrivebusinessfiles');
                    $fileOnedriveBusiness       = $modelOnedriveBusiness->getFile($id_file);
                    $file['description']        = (isset($fileOnedriveBusiness->description)) ? $fileOnedriveBusiness->description : '';
                    $file['file_tags']          = (isset($fileOnedriveBusiness->file_tags)) ? $fileOnedriveBusiness->file_tags : '';
                    $file_data                  = array();
                    $file_data['id']            = 0;
                    $file_data['title']         = $file['title'];
                    $file_data['file_id']       = $file['id'];
                    $file_data['ext']           = (isset($fileOnedriveBusiness->ext)) ? $fileOnedriveBusiness->ext : '';
                    $file_data['description']   = $file['description'];
                    $file_data['size']          = $file['size'];
                    $file_data['catid']         = $target_category->cloud_id;
                    $file_data['path']          = '';
                    $file_data['created_time']  = $file['created_time'];
                    $file_data['modified_time'] = $file['modified_time'];
                    $file_data['file_tags']     = $file['file_tags'];
                    $file_data['author']        = $user->get('id');
                    $modelOnedriveBusiness->save($file_data);
                }
            }
        } elseif ($active_category->type === 'googledrive' && $target_category->type === 'onedrivebusiness') {
            $google       = new DropfilesGoogle();
            $file         = $google->download($id_file, $active_category->cloud_id, false, 0, true);
            $catpath_dest = DropfilesBase::getFilesPath($id_category);

            if (!file_exists($catpath_dest)) {
                JFolder::create($catpath_dest);
                $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                JFile::write($catpath_dest . 'index.html', $data);
                $data = 'deny from all';
                JFile::write($catpath_dest . '.htaccess', $data);
            }

            $newname = uniqid() . '.' . $file->ext;
            file_put_contents($catpath_dest . $newname, $file->datas);

            $file_current       = $catpath_dest . $newname;
            $onedriveBusiness   = new DropfilesOneDriveBusiness();

            $pic          = array();
            $pic['error'] = 0;
            $pic['name']  = $file->title . '.' . $file->ext;
            $pic['type']  = '';
            $pic['size']  = $file->size;
            $f_name       = $file->title . '.' . $file->ext;
            $user         = JFactory::getUser();
            $result       = $onedriveBusiness->uploadFile($f_name, $pic, $file_current, $target_category->cloud_id);

            if ($result) {
                $modelOnedriveBusiness              = $this->getModel('onedrivebusinessfiles');
                $modelGoogle                        = $this->getModel('googlefiles');
                $filegoogle                         = $modelGoogle->getFile($id_file);
                $result['file']['description']      = (isset($filegoogle->description)) ? $filegoogle->description : '';
                $result['file']['file_tags']        = (isset($filegoogle->file_tags)) ? $filegoogle->file_tags : '';
                $file_data                          = array();
                $file_data['id']                    = 0;
                $file_data['title']                 = JFile::stripExt($result['file']['name']);
                $file_data['file_id']               = $result['file']['id'];
                $file_data['ext']                   = $file->ext;
                $file_data['description']           = $result['file']['description'];
                $file_data['size']                  = $result['file']['size'];
                $file_data['catid']                 = $target_category->cloud_id;
                $file_data['path']                  = '';
                $file_data['created_time']          = date('Y-m-d H:i:s', strtotime($result['file']['createdDateTime']));
                $file_data['modified_time']         = date('Y-m-d H:i:s', strtotime($result['file']['lastModifiedDateTime']));
                $file_data['author']                = $user->get('id');
                $file_data['file_tags']             = $result['file']['file_tags'];
                $modelOnedriveBusiness->save($file_data);
            }
        } elseif ($active_category->type === 'onedrivebusiness' && $target_category->type === 'googledrive') {
            $onedriveBusiness   = new DropfilesOneDriveBusiness();
            $file               = $onedriveBusiness->downloadFile($id_file);
            $catpath_dest       = DropfilesBase::getFilesPath($id_category);

            if (!file_exists($catpath_dest)) {
                JFolder::create($catpath_dest);
                $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                JFile::write($catpath_dest . 'index.html', $data);
                $data = 'deny from all';
                JFile::write($catpath_dest . '.htaccess', $data);
            }

            $newname = uniqid() . '.' . $file->ext;
            file_put_contents($catpath_dest . $newname, $file->datas);
            $file_current  = $catpath_dest . $newname;
            $google        = new DropfilesGoogle();
            $f_name        = $file->title . '.' . $file->ext;
            $fg_contents   = file_get_contents($file_current);
            $insertedFile  = $google->uploadFile($f_name, $fg_contents, '', $target_category->cloud_id);

            if ($insertedFile) {
                $modelGoogle                = $this->getModel('googlefiles');
                $modelOnedriveBusiness      = $this->getModel('onedrivebusinessfiles');
                $fileOnedriveBusiness       = $modelOnedriveBusiness->getFile($id_file);
                $user                       = JFactory::getUser();
                $file_data                  = $google->getFileObj($insertedFile);
                $file_data->ext             = (isset($fileOnedriveBusiness->ext)) ? $fileOnedriveBusiness->ext : '';
                $file_data->description     = (isset($fileOnedriveBusiness->description)) ? $fileOnedriveBusiness->description : '';
                $file_data->catid           = $target_category->cloud_id;
                $file_data->file_tags       = (isset($fileOnedriveBusiness->file_tags)) ? $fileOnedriveBusiness->file_tags : '';
                $file_data->author          = $user->get('id');
                unset($file_data->id);
                $modelGoogle->addFile($file_data);
                JFile::delete($file_current);
            }
        } elseif ($active_category->type === 'dropbox' && $target_category->type === 'onedrivebusiness') {
            $dropbox          = new DropfilesDropbox();
            list($tem, $file) = $dropbox->downloadDropbox($id_file);
            $catpath_dest     = DropfilesBase::getFilesPath($id_category);

            if (!file_exists($catpath_dest)) {
                JFolder::create($catpath_dest);
                $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                JFile::write($catpath_dest . 'index.html', $data);
                $data = 'deny from all';
                JFile::write($catpath_dest . '.htaccess', $data);
            }

            $newname = uniqid() . '.' . JFile::getExt($file['name']);

            ob_start();
            header('Content-Disposition: attachment; filename="' . $file['name'] . '"');
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');

            header('Content-Length: ' . (int) $file['size']);

            echo readfile($tem);
            unlink($tem);
            $data             = ob_get_clean();
            $file_current     = $catpath_dest . $newname;
            file_put_contents($file_current, $data);
            $onedriveBusiness = new DropfilesOneDriveBusiness();

            $pic              = array();
            $pic['error']     = 0;
            $pic['name']      = $file['name'];
            $pic['type']      = '';
            $pic['size']      = $file['size'];
            $f_name           = $file['name'];

            $result = $onedriveBusiness->uploadFile($f_name, $pic, $file_current, $target_category->cloud_id);
            if ($result) {
                $modelOnedriveBusiness              = $this->getModel('onedrivebusinessfiles');
                $modelDropbox                       = $this->getModel('dropboxfiles');
                $filedropbox                        = $modelDropbox->getFile($id_file);
                $user                               = JFactory::getUser();
                $result['file']['description']      = (isset($filedropbox->description)) ? $filedropbox->description : '';
                $result['file']['file_tags']        = (isset($filedropbox->file_tags)) ? $filedropbox->file_tags : '';
                $file_data                          = array();
                $file_data['id']                    = 0;
                $file_data['title']                 = JFile::stripExt($result['file']['name']);
                $file_data['file_id']               = $result['file']['id'];
                $file_data['ext']                   = JFile::getExt($file['name']);
                $file_data['description']           = $result['file']['description'];
                $file_data['size']                  = $result['file']['size'];
                $file_data['catid']                 = $target_category->cloud_id;
                $file_data['path']                  = '';
                $file_data['created_time']          = date('Y-m-d H:i:s', strtotime($result['file']['createdDateTime']));
                $file_data['modified_time']         = date('Y-m-d H:i:s', strtotime($result['file']['lastModifiedDateTime']));
                $file_data['file_tags']             = $result['file']['file_tags'];
                $file_data['author']                = $user->get('id');
                $modelOnedriveBusiness->save($file_data);

                JFile::delete($file_current);
            }
        } elseif ($active_category->type === 'onedrivebusiness' && $target_category->type === 'dropbox') {
            $onedriveBusiness   = new DropfilesOneDriveBusiness();
            $file               = $onedriveBusiness->downloadFile($id_file);
            $catpath_dest       = DropfilesBase::getFilesPath($id_category);

            if (!file_exists($catpath_dest)) {
                JFolder::create($catpath_dest);
                $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                JFile::write($catpath_dest . 'index.html', $data);
                $data = 'deny from all';
                JFile::write($catpath_dest . '.htaccess', $data);
            }

            $newname       = uniqid() . '.' . $file->ext;
            file_put_contents($catpath_dest . $newname, $file->datas);
            $file_current  = $catpath_dest . $newname;
            $dropbox       = new DropfilesDropbox();
            $f_name        = $file->title . '.' . $file->ext;
            $result        = $dropbox->uploadFile($f_name, $file_current, filesize($file_current), $target_category->path);

            if ($result) {
                $modelDropbox               = $this->getModel('dropboxfiles');
                $modelOnedriveBusiness      = $this->getModel('onedrivebusinessfiles');
                $fileOnedriveBusiness       = $modelOnedriveBusiness->getFile($id_file);
                $result['description']      = (isset($fileOnedriveBusiness->description)) ? $fileOnedriveBusiness->description : '';
                $result['file_tags']        = (isset($fileOnedriveBusiness->file_tags)) ? $fileOnedriveBusiness->file_tags : '';
                $file_data                  = array();
                $file_data['id']            = 0;
                $file_data['title']         = JFile::stripExt($result['name']);
                $file_data['file_id']       = $result['id'];
                $file_data['ext']           = (isset($fileOnedriveBusiness->ext)) ? $fileOnedriveBusiness->ext : '';
                $file_data['description']   = $result['description'];
                $file_data['size']          = $result['size'];
                $file_data['catid']         = $target_category->cloud_id;
                $file_data['path']          = $result['path_lower'];
                $file_data['created_time']  = date('Y-m-d H:i:s', strtotime($result['client_modified']));
                $file_data['modified_time'] = date('Y-m-d H:i:s', strtotime($result['server_modified']));
                $file_data['file_tags']     = $result['file_tags'];
                $modelDropbox->save($file_data);

                JFile::delete($file_current);
            }
        } elseif ($active_category->type === 'onedrive' && $target_category->type === 'onedrivebusiness') {
            $onedrive = new DropfilesOneDrive();
            $file     = $onedrive->downloadFile($id_file);

            $catpath_dest = DropfilesBase::getFilesPath($id_category);

            if (!file_exists($catpath_dest)) {
                JFolder::create($catpath_dest);
                $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                JFile::write($catpath_dest . 'index.html', $data);
                $data = 'deny from all';
                JFile::write($catpath_dest . '.htaccess', $data);
            }

            $newname      = uniqid() . '.' . $file->ext;
            $file_current = $catpath_dest . $newname;
            file_put_contents($file_current, $file->datas);

            $onedriveBusiness = new DropfilesOneDriveBusiness();

            $pic              = array();
            $pic['error']     = 0;
            $pic['name']      = $file->title . '.' . $file->ext;
            $pic['type']      = '';
            $pic['size']      = $file->size;
            $f_name           = $file->title . '.' . $file->ext;

            $result = $onedriveBusiness->uploadFile($f_name, $pic, $file_current, $target_category->cloud_id);
            if ($result) {
                $modelOnedriveBusiness              = $this->getModel('onedrivebusinessfiles');
                $modelOnedrive                      = $this->getModel('onedrivefiles');
                $fileOneDrive                       = $modelOnedrive->getFile($id_file);
                $user                               = JFactory::getUser();
                $result['file']['description']      = (isset($fileOneDrive->description)) ? $fileOneDrive->description : '';
                $result['file']['file_tags']        = (isset($fileOneDrive->file_tags)) ? $fileOneDrive->file_tags : '';
                $file_data                          = array();
                $file_data['id']                    = 0;
                $file_data['title']                 = JFile::stripExt($result['file']['name']);
                $file_data['file_id']               = $result['file']['id'];
                $file_data['ext']                   = (isset($fileOneDrive->ext)) ? $fileOneDrive->ext : '';
                $file_data['description']           = $result['file']['description'];
                $file_data['size']                  = $result['file']['size'];
                $file_data['catid']                 = $target_category->cloud_id;
                $file_data['path']                  = '';
                $file_data['created_time']          = date('Y-m-d H:i:s', strtotime($result['file']['createdDateTime']));
                $file_data['modified_time']         = date('Y-m-d H:i:s', strtotime($result['file']['lastModifiedDateTime']));
                $file_data['file_tags']             = $result['file']['file_tags'];
                $file_data['author']                = $user->get('id');
                $modelOnedriveBusiness->save($file_data);

                JFile::delete($file_current);
            }
        } elseif ($active_category->type === 'onedrivebusiness' && $target_category->type === 'onedrive') {
            $onedriveBusiness   = new DropfilesOneDriveBusiness();
            $file               = $onedriveBusiness->downloadFile($id_file);
            $catpath_dest       = DropfilesBase::getFilesPath($id_category);

            if (!file_exists($catpath_dest)) {
                JFolder::create($catpath_dest);
                $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                JFile::write($catpath_dest . 'index.html', $data);
                $data = 'deny from all';
                JFile::write($catpath_dest . '.htaccess', $data);
            }

            $newname       = uniqid() . '.' . $file->ext;
            file_put_contents($catpath_dest . $newname, $file->datas);

            $onedrive     = new DropfilesOneDrive();
            $file_current  = $catpath_dest . $newname;

            $pic          = array();
            $pic['error'] = 0;
            $pic['name']  = $file->title . '.' . $file->ext;
            $pic['type']  = '';
            $pic['size']  = $file->size;
            $f_name       = $file->title . '.' . $file->ext;

            $result = $onedrive->uploadFile($f_name, $pic, $file_current, $target_category->cloud_id);
            $user   = JFactory::getUser();
            if ($result) {
                $modelOnedrive                      = $this->getModel('onedrivefiles');
                $modelOnedriveBusiness              = $this->getModel('onedrivebusinessfiles');
                $fileonedrivebusiness               = $modelOnedriveBusiness->getFile($id_file);
                $result['file']['description']      = (isset($fileonedrivebusiness->description)) ? $fileonedrivebusiness->description : '';
                $result['file']['file_tags']        = (isset($fileonedrivebusiness->file_tags)) ? $fileonedrivebusiness->file_tags : '';
                $file_data                          = array();
                $file_data['id']                    = 0;
                $file_data['title']                 = JFile::stripExt($result['file']['name']);
                $file_data['file_id']               = $result['file']['id'];
                $file_data['ext']                   = (isset($file->ext)) ? $file->ext : '';
                $file_data['description']           = $result['file']['description'];
                $file_data['size']                  = $result['file']['size'];
                $file_data['catid']                 = $target_category->cloud_id;
                $file_data['path']                  = '';
                $file_data['created_time']          = date('Y-m-d H:i:s', strtotime($result['file']['createdDateTime']));
                $file_data['modified_time']         = date('Y-m-d H:i:s', strtotime($result['file']['lastModifiedDateTime']));
                $file_data['file_tags']             = $result['file']['file_tags'];
                $file_data['author']                = $user->get('id');
                $modelOnedrive->save($file_data);


                JFile::delete($file_current);
            }
        }
        // Update files count
        $categoriesModel = $this->getModel('Categories', 'DropfilesModel');
        $categoriesModel->updateFilesCount();
        $this->exitStatus(true);
        JFactory::getApplication()->close();
    }

    /**
     * Method to check if you can delete file in a category
     *
     * @param integer $recordId Record id
     *
     * @return boolean
     * @since  version
     */
    protected function allowEditOwn($recordId = 0)
    {
        // Zero record (id:0), return component edit permission by calling parent controller method
        if (!$recordId) {
            return parent::allowEdit();
        }

        $user = JFactory::getUser();
        // Check edit on the record asset (explicit or inherited)
        if ($user->authorise('core.edit', 'com_dropfiles.category.' . $recordId)) {
            return true;
        }

        // Check edit own on the record asset (explicit or inherited)
        if ($user->authorise('core.edit.own', 'com_dropfiles.category.' . $recordId)) {
            // Existing record already has an owner, get it
            $modelC = $this->getModel('category');
            $record = $modelC->getCategory($recordId);

            if (empty($record)) {
                return false;
            }

            // Grant if current user is owner of the record
            return (int) $user->id === (int) $record->created_user_id;
        }

        return false;
    }


    /**
     * Delete a file
     *
     * @return void
     * @throws \Exception Throw when application can not start
     * @since  version
     */
    public function delete()
    {
        $return  = false;
        $id_file = JFactory::getApplication()->input->getString('id_file', 0);
        $id_cat  = JFactory::getApplication()->input->getInt('id_cat', 0);
        $id_cate_ref  = JFactory::getApplication()->input->getInt('id_cate_ref', 0);

        if (!$this->allowEditOwn($id_cat)) {
            $this->exitStatus(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
        }

        $modelC         = $this->getModel('category');
        $category       = $modelC->getDropfileCategory($id_cat);
        $category2      = $modelC->getCategory($id_cat);
        $author_user_id = 0;

        if ($category) {
            switch ($category->type) {
                case 'googledrive':
                    $google         = new DropfilesGoogle();
                    $modelGoogle    = $this->getModel('googlefiles');
                    $file           = $modelGoogle->getFile($id_file);
                    $author_user_id = $file->author;

                    if ($google->delete($id_file, $category->cloud_id)) {
                        $return = true;
                        $modelGoogle->deleteFile($id_file);
                    }
                    break;
                case 'dropbox':
                    $dropbox        = new DropfilesDropbox();
                    $modelDropbox   = $this->getModel('dropboxfiles');
                    $file           = $modelDropbox->getFile($id_file);
                    $author_user_id = $file->author;

                    if ($dropbox->deleteFileDropbox($id_file)) {
                        $return = true;
                        $modelDropbox->deleteFile($id_file);
                    }
                    break;
                case 'onedrive':
                    $onedrive       = new DropfilesOneDrive();
                    $modelOnedrive  = $this->getModel('onedrivefiles');
                    $file           = $modelOnedrive->getFile($id_file);
                    $author_user_id = $file->author;

                    if ($onedrive->delete($id_file)) {
                        $return = true;
                        $modelOnedrive->deleteFile($id_file);
                    }
                    break;
                case 'onedrivebusiness':
                    $oneDriveBusiness       = new DropfilesOneDriveBusiness();
                    $modelOneDriveBusiness  = $this->getModel('onedrivebusinessfiles');
                    $file                   = $modelOneDriveBusiness->getFile($id_file);
                    $author_user_id         = $file->author;
                    if ($oneDriveBusiness->delete($id_file)) {
                        $return = true;
                        $modelOneDriveBusiness->deleteFile($id_file);
                    }

                    break;

                default:
                    $model          = $this->getModel();
                    $file           = $model->getFile($id_file);
                    $author_user_id = $file->author;
                    if ($file !== false) {
                        $this->canEdit($file->catid);
                    }
                    if ($id_cat === $id_cate_ref) {
                        $file_dir = DropfilesBase::getFilesPath($file->catid);
                        if (file_exists($file_dir . $file->file)) {
                            JFile::delete($file_dir . $file->file);
                        }
                        if ($model->removePicture($file->id)) {
                            // Index new uploaded file
                            $ftsModel = $this->getModel('fts');
                            $ftsModel->removeIndexRecordForPost($file->id);
                            $return = true;
                        } else {
                            $return = false;
                        }
                        break;
                    } else {
                        $modelC->deleteRefToFiles($id_cat, $id_file, $id_cate_ref);
                        $file_mtc = (isset($file->file_multi_category)) ? $file->file_multi_category : '';
                        if ($file_mtc !== '') {
                            $file_mtc = explode(',', $file_mtc);
                            if (is_array($file_mtc) && !empty($file_mtc)) {
                                $delcate = array_search($id_cat, $file_mtc);
                                if ($delcate !== false) {
                                    unset($file_mtc[$delcate]);
                                }
                            }
                            $file_mtc = implode(',', $file_mtc);
                            $model->setMultiCategoryFile($id_file, $file_mtc);
                            $return = true;
                        }
                    }
            }
            // Update files count
            $categoriesModel = $this->getModel('Categories', 'DropfilesModel');
            $categoriesModel->updateFilesCount();
            $params = JComponentHelper::getParams('com_dropfiles');

            $email_title = JText::_('COM_DROPFILES_EMAIL_DELETE_EVENT_TITLE');

            if ($params->get('delete_event_subject', '') !== '') {
                $email_title = $params->get('delete_event_subject', '');
            }

            $email_body     = $params->get('delete_event_editor', DropfilesHelper::getHTMLEmail('file-deleted.html'));
            $search_replace = 'components/com_dropfiles/assets/images/icon-download.png';
            $str_replace    = JUri::root() . 'components/com_dropfiles/assets/images/icon-download.png';
            $email_body     = str_replace($search_replace, $str_replace, $email_body);
            $email_body     = str_replace('{category}', $category2->title, $email_body);
            $email_body     = str_replace('{website_url}', JUri::root(), $email_body);
            $email_body     = str_replace('{file_name}', $file->title, $email_body);
            $uploader       = JFactory::getUser($file->author);
            $email_body     = str_replace('{uploader_username}', $uploader->name, $email_body);

            $currentUser    = JFactory::getUser();
            $email_body     = str_replace('{username}', $currentUser->name, $email_body);

            if ((int) $params->get('file_owner', 0) === 1 && (int) $params->get('delete_event', 1) === 1) {
                $user       = JFactory::getUser($author_user_id);
                $email_body = str_replace('{receiver}', $user->name, $email_body);
                DropfilesHelper::sendMail($user->email, $email_title, $email_body);
            }

            if ((int) $params->get('category_owner', 0) === 1 && (int) $params->get('delete_event', 1) === 1) {
                $user       = JFactory::getUser($category2->created_user_id);
                $email_body = str_replace('{receiver}', $user->name, $email_body);
                DropfilesHelper::sendMail($user->email, $email_title, $email_body);
            }

            if ($params->get('delete_event_additional_email', '') !== '' && (int) $params->get('delete_event', 1) === 1) {
                $emails = explode(',', $params->get('delete_event_additional_email', ''));
                if (!empty($emails)) {
                    foreach ($emails as $email) {
                        DropfilesHelper::sendMail($email, $email_title, $email_body);
                    }
                }
            }

            if ((int) $params->get('notify_super_admin', 0) === 1 && (int) $params->get('delete_event', 1) === 1) {
                $users = DropfilesHelper::getSuperAdmins();

                if (count($users)) {
                    foreach ($users as $item) {
                        $user       = JFactory::getUser($item->user_id);
                        $email_body = str_replace('{receiver}', $user->name, $email_body);
                        DropfilesHelper::sendMail($user->email, $email_title, $email_body);
                    }
                }
            }
        }
        echo json_encode($return);

        JFactory::getApplication()->close();
    }


    /**
     * Reorder category
     *
     * @return void
     * @throws \Exception Throw when application can not start
     * @since  version
     */
    public function reorder()
    {
        $files    = JFactory::getApplication()->input->getString('order', null);
        $idcat    = JFactory::getApplication()->input->getInt('idcat', false);
        $modelCat = $this->getModel('category');
        $category = $modelCat->getCategory($idcat);
        $this->canEdit($category->id);

        $files = json_decode($files);

        if ($category->type === 'googledrive') {
            //$google = new DropfilesGoogle();
            //$return = $google->reorder($files,$category->cloud_id);
            $modelGoogle = $this->getModel('googlefiles');
            $filesok     = true;
            foreach ($files as $key => $file) {
                $f = $modelGoogle->getFile($file);
                if ($f->catid !== $category->cloud_id) {
                    $filesok = false;
                    break;
                }
            }
            if ($filesok) {
                if ($modelGoogle->reorder($files)) {
                    $return = true;
                } else {
                    $return = false;
                }
            } else {
                $return = false;
            }
        } elseif ($category->type === 'dropbox') {
            $modelDropbox = $this->getModel('dropboxfiles');
            $filesok      = true;
            foreach ($files as $key => $file) {
                $f = $modelDropbox->getFile($file);
                if ($f->catid !== $category->cloud_id) {
                    $filesok = false;
                    break;
                }
            }
            if ($filesok) {
                if ($modelDropbox->reorder($files)) {
                    $return = true;
                } else {
                    $return = false;
                }
            } else {
                $return = false;
            }
        } else {
            $model   = $this->getModel();
            $filesok = true;
            foreach ($files as $key => $file) {
                $f = $model->getFile($file);
                if ($f->catid !== $category->id) {
                    $filesok = false;
                    break;
                }
            }

            if ($filesok) {
                if ($model->reorder($files)) {
                    $return = true;
                } else {
                    $return = false;
                }
            } else {
                $return = false;
            }
        }
        echo json_encode($return);
        JFactory::getApplication()->close();
    }


    /**
     * Return a json response
     *
     * @param boolean $status Response status
     * @param array   $datas  Array of datas to return with the json string
     *
     * @return void
     * @throws \Exception Throw when application can not start
     * @since  version
     */
    private function exitStatus($status, $datas = array())
    {
        $response = array('response' => $status, 'datas' => $datas);
        echo json_encode($response);
        JFactory::getApplication()->close();
    }


    /**
     * Check if the current user has permission on the current gallery
     *
     * @param integer $id_category Category id
     *
     * @return void
     * @throws \Exception Throw when application can not start
     * @since  version
     */
    private function canEdit($id_category)
    {
        $model = $this->getModel('category');
        $canDo = DropfilesHelper::getActions();
        if (!$canDo->get('core.edit')) {
            if ($canDo->get('core.edit.own')) {
                $category = $model->getItem($id_category);
                if ($category->created_user_id !== JFactory::getUser()->id) {
                    $this->exitStatus('not permitted');
                }
            } else {
                $this->exitStatus('not permitted');
            }
        }
    }

    /**
     * Add a remote file by url
     *
     * @return void
     * @throws \Exception Throw when application can not start
     * @since  version
     */
    public function addremoteurl()
    {
        // Access check.
        if (!$this->allowAdd()) {
            $this->exitStatus(JText::_('JLIB_APPLICATION_ERROR_CREATE_RECORD_NOT_PERMITTED'));
        }

        $input       = JFactory::getApplication()->input;
        $id_category = $input->getInt('id_category');
        if ($id_category <= 0) {
            $this->exitStatus(JText::_('Wrong Category'));
        }

        $remote_title = $input->getString('remote_title');

        $remote_url  = $input->getString('remote_url');
        $remote_type = $input->getString('remote_type');
        if ($remote_title === '') {
            $this->exitStatus(JText::_('Enter title'));
        } elseif ($remote_type === '') {
            $this->exitStatus(JText::_('Enter type'));
        } elseif ($remote_url === '') {
            $this->exitStatus(JText::_('Enter url'));
        } else {
            if (!preg_match('(http://|https://)', $remote_url)) {
                $this->exitStatus(JText::_($remote_url . ' is not a valid URL'));
            }
        }

        //Insert new image into databse
        $model   = $this->getModel();
        $user    = JFactory::getUser();
        $id_file = $model->addFile(array(
            'title'       => $remote_title,
            'id_category' => (int) $id_category,
            'file'        => $remote_url,
            'ext'         => $remote_type,
            'size'        => $this->remoteFileSize($remote_url),
            'author'      => $user->get('id')
        ), true);

        if (!$id_file) {
            $this->exitStatus(JText::_('Cant save to database'));
        }

        $this->exitStatus(true, array('id_file' => $id_file, 'name' => $remote_url));
    }

    /**
     * Get file size from a remote file
     *
     * @param string $url Remove file URL
     *
     * @return mixed|string
     *
     * @since version
     */
    protected function remoteFileSize($url)
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_NOBODY         => 1,
        ));

        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_exec($ch);

        $clen = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        curl_close($ch);

        if (!$clen || ($clen === - 1)) {
            return 'n/a';
        }

        return $clen;
    }

    /**
     * Set columns cookie
     *
     * @return void
     * @throws \Exception Throw when application can not start
     * @since  version
     */
    public function showcolumn()
    {
        $input        = JFactory::getApplication()->input;
        $check        = $input->get('check');
        $lists        = $input->getString('column_show', null) !== null ? $input->getString('column_show') : array();
        $string_lists = implode(',', $lists);
        setcookie('dropfiles_show_columns', $string_lists, time() + (86400 * 30), '/');
        $this->exitStatus(true);
    }

    /**
     * Delete a directory RECURSIVELY
     *
     * @param string $dir Directory path
     *
     * @return void
     * @since  version
     */
    public function rrmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object !== '.' && $object !== '..') {
                    if (filetype($dir . '/' . $object) === 'dir') {
                        $this->rrmdir($dir . '/' . $object);
                    } else {
                        unlink($dir . '/' . $object);
                    }
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }

    /**
     * Check if all the parts exist, and
     * gather all the parts of the file together
     *
     * @param string  $temp_dir        The temporary directory holding all the parts of the file
     * @param string  $destination_dir The directory to save joined file
     * @param string  $fileName        The original file name
     * @param string  $newName         The new unique file name
     * @param string  $totalSize       Original file size (in bytes)
     * @param integer $total_files     Total files
     *
     * @return boolean|null
     * @since  version
     */
    public function createFileFromChunks($temp_dir, $destination_dir, $fileName, $newName, $totalSize, $total_files)
    {
        // count all the parts of this file
        $total_files_on_server_size = 0;
        $temp_total                 = 0;
        foreach (scandir($temp_dir) as $file) {
            $temp_total                 = $total_files_on_server_size;
            $tempfilesize               = filesize($temp_dir . '/' . $file);
            $total_files_on_server_size = $temp_total + $tempfilesize;
        }
        // check that all the parts are present
        // If the Size of all the chunks on the server is equal to the size of the file uploaded.
        if ($total_files_on_server_size >= $totalSize) {
            // create the final destination file
            $file = fopen($destination_dir . '/' . $newName, 'w');
            if ($file !== false) {
                for ($i = 1; $i <= $total_files; $i ++) {
                    fwrite($file, file_get_contents($temp_dir . '/' . $fileName . '.part' . $i));
                }
                fclose($file);
            } else {
                return false;
            }

            // rename the temporary directory (to avoid access from other
            // concurrent chunks uploads) and than delete it
            if (rename($temp_dir, $temp_dir . '_UNUSED')) {
                $this->rrmdir($temp_dir . '_UNUSED');
            } else {
                $this->rrmdir($temp_dir);
            }

            return true;
        }

        return null;
    }
}
