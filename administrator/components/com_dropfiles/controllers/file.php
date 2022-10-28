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
jimport('joomla.filesystem.file');

/**
 * Class DropfilesControllerFile
 */
class DropfilesControllerFile extends JControllerForm
{
    /**
     * Save property file info
     *
     * @param string|null $key    Key
     * @param string|null $urlVar Key Value
     *
     * @return void
     * @throws \Exception Throw when application can not start
     * @since  version
     */
    public function save($key = null, $urlVar = null)
    {
        // Check for request forgeries.
        JSession::checkToken() || jexit(JText::_('JINVALID_TOKEN'));

        $app = JFactory::getApplication();
        $model = $this->getModel();
        $modelFiles = $this->getModel('files');
        $modelCat = $this->getModel('category');
        $idCateint = JFactory::getApplication()->input->getInt('catid', 0);
        $table = $model->getTable();
        $data = $app->input->post->get('jform', array(), 'array');
        //file_multi_category
        $file_multi_category = (isset($data['file_multi_category'])) ? $data['file_multi_category'] : array();
        $idmfcat[] = JFactory::getApplication()->input->getString('catid', 0);
        $file_multi_category = array_merge($file_multi_category, $idmfcat);
        $data['file_multi_category'] = implode(',', $file_multi_category);
        $file_multi_category_old = '';
        $idmc = 0;
        if (isset($data['id'])) {
            $idmc = $data['id'];
            $filemc = $modelFiles->getFile($data['id']);
            $file_multi_category_old = (isset($filemc->file_multi_category)) ? explode(',', $filemc->file_multi_category) : array();
            unset($file_multi_category_old[count($file_multi_category_old) - 1]);
            unset($file_multi_category[count($file_multi_category) - 1]);
            $this->saveCatRefToFiles($modelCat, $file_multi_category_old, $file_multi_category, $idmc, $idCateint);
        }

        // Access check.
        if (!$this->allowSave($data, $key)) {
            $this->exitStatus(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
        }

        $remotefile = '';
        if (isset($data['id'])) {
            $file = $modelFiles->getFile($data['id']);
            if ($file && preg_match('(http://|https://)', $file->file)) {
                if (!preg_match('(http://|https://)', $data['remoteurl'])) {
                    $this->exitStatus($data['remoteurl'] . ' is not valid url');
                } else {
                    $remotefile = $data['remoteurl'];
                }
            }
        }

        // Save tags
        if ($data['file_tags'] !== '') {
            $file_tags = explode(',', $data['file_tags']);
            if (DropfilesBase::isJoomla30()) {
                JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tags/tables');
                $tagTable = JTable::getInstance('Tag', 'TagsTable');
                foreach ($file_tags as $tag) {
                    $tagTable->reset();

                    if (!$tagTable->load(array('title' => $tag))) {
                        $tagTable->id = 0;
                        $tagTable->title = $tag;
                        $tagTable->published = 1;
                        $tagTable->language = '*';
                        $tagTable->access = 1;

                        $tagTable->setLocation($tagTable->getRootId(), 'last-child');

                        if ($tagTable->check()) {
                            $tagTable->path = $tagTable->alias;
                            $tagTable->store();
                        }
                    }
                }
            } else { // Joomla 4
                $db    = JFactory::getDbo();
                $jTagModel = new Joomla\Component\Tags\Administrator\Model\TagModel();
                $jTagTable = new Joomla\Component\Tags\Administrator\Table\TagTable($db);
                foreach ($file_tags as $tag) {
                    if (!$jTagTable->load(array('title' => $tag))) {
                        $tagData = array();
                        $tagData['title'] = $tag;
                        $tagData['alias'] = '' ;
                        $tagData['id'] = 0;
                        $tagData['parent_id'] = 1;
                        $tagData['published'] = 1;
                        $tagData['access'] = 1;
                        $tagData['language'] = '*';
                        $tagData['description'] = '';
                        $jTagModel->save($tagData);
                    }
                }
            }
        }

        $checkin = property_exists($table, 'checked_out');
        $context = $this->option . '.edit.' . $this->context;

        // Determine the name of the primary key for the data.
        if (empty($key)) {
            $key = $table->getKeyName();
        }

        // To avoid data collisions the urlVar may be different from the primary key.
        if (empty($urlVar)) {
            $urlVar = $key;
        }

        $recordId = $app->input->getString($urlVar);

        // Populate the row id from the session.
        $data[$key] = $recordId;


        // Validate the posted data.
        // Sometimes the form needs some posted data, such as for plugins and modules.
        $form = $model->getForm($data, false);

        if (!$form) {
            $this->exitStatus($model->getError());
        }

        // Test whether the data is valid.
        $validData = $model->validate($form, $data);

        // Check for validation errors.
        if ($validData === false) {
            // Get the validation messages.
            $errors = $model->getErrors();

            // Push up to three validation messages out to the user.
            for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
                if ($errors[$i] instanceof Exception) {
                    $this->exitStatus($errors[$i]->getMessage());
                } else {
                    $this->exitStatus($errors[$i]);
                }
            }
        }

        if (!isset($validData['tags'])) {
            $validData['tags'] = null;
        }
        if ($remotefile !== '') {
            $validData['file'] = $remotefile;
        }
        // Attempt to save the data.
        if (!$model->save($validData)) {
            // Save the data in the session.
            $app->setUserState($context . '.data', $validData);

            // Redirect back to the edit screen.
            $this->exitStatus(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
        }

        // Save succeeded, so check-in the record.
        if ($checkin && $model->checkin($validData[$key]) === false) {
            // Save the data in the session.
            $app->setUserState($context . '.data', $validData);

            // Check-in failed, so go back to the record and display a notice.
            $this->exitStatus(JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()));
        }

        // Clear the record id and data from the session.
        $this->releaseEditId($context, $recordId);
        $app->setUserState($context . '.data', null);


        // Invoke the postSave method to allow for the child class to access the model.
        $this->postSaveHook($model, $validData);


        $modelC = $this->getModel('category');

        $idcat = JFactory::getApplication()->input->getInt('catid', 0);
        $category = $modelC->getCategory($idcat);

        $params = JComponentHelper::getParams('com_dropfiles');
        $author_user_id = 0;
        if ($category->type === 'googledrive') {
            $modelGoogle = $this->getModel('googlefiles');
            $file = $modelGoogle->getFile($data['id']);
            if ($file) {
                $author_user_id = $file->author;
                $google = new DropfilesGoogle();
                $google->changeFilename($file->file_id, $file->title);
            }
        } elseif ($category->type === 'dropbox') {
            $modelDropbox = $this->getModel('dropboxfiles');
            $file = $modelDropbox->getFile($data['id']);
            if ($file) {
                $author_user_id = $file->author;
            }
        } elseif ($category->type === 'onedrive') {
            $modelOnedrive = $this->getModel('onedrivefiles');
            $file = $modelOnedrive->getFile($data['id']);
            if ($file) {
                $author_user_id = $file->author;
            }
        } elseif ($category->type === 'onedrivebusiness') {
            $modelOnedriveBusiness = $this->getModel('onedrivebusinessfiles');
            $file = $modelOnedriveBusiness->getFile($data['id']);
            if ($file) {
                $author_user_id = $file->author;
            }
        } else {
            $modelFiles = $this->getModel('files');
            $file = $modelFiles->getFile($data['id']);
            if ($file) {
                $author_user_id = $file->author;
            }
        }


        $email_title = JText::_('COM_DROPFILES_EMAIL_EDIT_EVENT_TITLE');

        if ($params->get('edit_event_subject', '') !== '') {
            $email_title = $params->get('edit_event_subject', '');
        }


        $email_body  = $params->get('edit_event_editor', DropfilesHelper::getHTMLEmail('file-edited.html'));
        $email_body  = str_replace(
            'components/com_dropfiles/assets/images/icon-download.png',
            JUri::root() . 'components/com_dropfiles/assets/images/icon-download.png',
            $email_body
        );
        $email_body  = str_replace('{category}', $category->title, $email_body);
        $email_body  = str_replace('{website_url}', JUri::root(), $email_body);
        $email_body  = str_replace('{file_name}', $validData['title'], $email_body);
        $currentUser = JFactory::getUser();
        $email_body  = str_replace('{username}', $currentUser->name, $email_body);
        $uploader       = JFactory::getUser($author_user_id);
        $email_body     = str_replace('{uploader_username}', $uploader->name, $email_body);

        if ((int) $params->get('file_owner', 0) === 1 && (int) $params->get('edit_event', 1) === 1) {
            $email_body = str_replace('{receiver}', $uploader->name, $email_body);
            DropfilesHelper::sendMail($uploader->email, $email_title, $email_body);
        }

        if ((int) $params->get('category_owner', 0) === 1 && (int) $params->get('edit_event', 1) === 1) {
            $user = JFactory::getUser($category->created_user_id);
            $email_body = str_replace('{receiver}', $user->name, $email_body);
            DropfilesHelper::sendMail($user->email, $email_title, $email_body);
        }

        if ($params->get('edit_event_additional_email', '') !== '' && (int) $params->get('edit_event', 1) === 1) {
            $emails = explode(',', $params->get('edit_event_additional_email', ''));
            if (!empty($emails)) {
                foreach ($emails as $email) {
                    DropfilesHelper::sendMail($email, $email_title, $email_body);
                }
            }
        }

        if ((int) $params->get('notify_super_admin', 0) === 1 && (int) $params->get('edit_event', 1) === 1) {
            $users = DropfilesHelper::getSuperAdmins();

            if (count($users)) {
                foreach ($users as $item) {
                    $user = JFactory::getUser($item->user_id);
                    $email_body = str_replace('{receiver}', $user->name, $email_body);
                    DropfilesHelper::sendMail($user->email, $email_title, $email_body);
                }
            }
        }

        $this->exitStatus(true);
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
     * Get redirect to item append
     *
     * @param null   $recordId Item Id
     * @param string $urlVar   Url val
     *
     * @return string
     * @throws \Exception Throw when application can not start
     * @since  version
     */
    public function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
    {
        $app      = JFactory::getApplication();
        $append   = parent::getRedirectToItemAppend($recordId, $urlVar);

        $format   = $app->input->get('format', 'raw');
        $idcat    = $app->input->getInt('catid', 0);
        $id       = $app->input->getString('id');

        $modelC   = $this->getModel('category');
        $category = $modelC->getCategory($idcat);

        if ($category->type === 'googledrive') {
            $append .= '&type=googledrive&id=' . $id;
        } elseif ($category->type === 'dropbox') {
            $append .= '&type=dropbox&id=' . $id;
        } elseif ($category->type === 'onedrive') {
            $append .= '&type=onedrive&id=' . $id;
        } elseif ($category->type === 'onedrivebusiness') {
            $append .= '&type=onedrivebusiness&id=' . $id;
        } else {
            $append .= '&type=default&id=' . $id;
        }

        // Setup redirect info.
        if ($format) {
            $append .= '&format=' . $format;
        }

        return $append;
    }

    /**
     * Check permission to edit file
     *
     * @param array  $data Data
     * @param string $key  Key
     *
     * @return boolean
     * @throws \Exception Throw when application can not start
     * @since  version
     */
    protected function allowEdit($data = array(), $key = 'id')
    {
        $id    = JFactory::getApplication()->input->getString('id', 0);
        $idcat = JFactory::getApplication()->input->getInt('catid', 0);
        $canDo = DropfilesHelper::getActions();

        if (!$canDo->get('core.edit')) {
            if ($canDo->get('core.edit.own')) {
                $modelC   = $this->getModel('category');
                $category = $modelC->getItem($idcat);
                if ($category->created_user_id !== JFactory::getUser()->id) {
                    return false;
                }
                $category = $modelC->getCategory($idcat);
                if ($category->type !== 'googledrive') {
                    $modelF = $this->getModel('files');
                    $file   = $modelF->getFile($id);
                    if ((int) $file->catid !== $idcat) {
                        return false;
                    }
                }
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * Download a file
     *
     * @return void
     * @throws \Exception Throw when application can not start
     * @since  version
     */
    public function download()
    {
        $model   = $this->getModel();

        $id      = JFactory::getApplication()->input->getString('id', 0);
        $catid   = JFactory::getApplication()->input->getInt('catid', 0);
        $version = JFactory::getApplication()->input->getString('version', false);

        if (!$this->allowEdit()) {
            $this->setRedirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));
            $this->redirect();
        }

        $modelC   = $this->getModel('category');
        $category = $modelC->getCategory($catid);

        switch ($category->type) {
            case 'googledrive':
                $google = new DropfilesGoogle();
                $file   = $google->download($id, $category->cloud_id, $version, 0, false);

                if (!is_object($file)) {
                    $this->setRedirect('index.php');
                    $this->redirect();
                }
                $contentType = 'application/octet-stream';
                $disposition = 'attachment';
                // Serve download for google document
                if (strpos($file->mimeType, 'vnd.google-apps') !== false) { // Is google file
                    // GuzzleHttp\Psr7\Response
                    $fileData = $google->downloadGoogleDocument($file->id, $file->exportMineType);

                    if ($fileData instanceof \GuzzleHttp\Psr7\Response) {
                        $contentLength = $fileData->getHeaderLine('Content-Length');
                        $contentType = $fileData->getHeaderLine('Content-Type');

                        if ($fileData->getStatusCode() === 200) {
                            header('Content-Disposition: ' . $disposition . '; filename="' . htmlspecialchars($file->title . '.' . $file->exthtmlspecialchars, ENT_QUOTES, 'UTF-8') . '"');
                            header('Content-Description: File Transfer');
                            header('Content-Type: ' . $contentType);
                            header('Content-Transfer-Encoding: binary');
                            header('Expires: 0');
                            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                            header('Pragma: public');
                            if ($contentLength !== 0) {
                                header('Content-Length: ' . $contentLength);
                            }
                            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- file content output
                            echo $fileData->getBody();
                            jexit();
                        }
                    }
                } else {
                    $google->downloadLargeFile($file, $contentType, $disposition);
                }
                jexit();
                break;
            case 'dropbox':
                $dropCate = new DropfilesDropbox();
                $rev      = JFactory::getApplication()->input->getString('vid', '');
                if ($rev !== '') {
                    $dropCate->downloadVersion($id, $rev);
                } else {
                    list($file, $fMeta) = $dropCate->downloadDropbox($id);

                    ob_end_clean();
                    ob_start();
                    header('Content-Disposition: attachment; filename="' . $fMeta['name'] . '"');
                    header('Content-Description: File Transfer');
                    header('Content-Type: application/octet-stream');
                    header('Content-Transfer-Encoding: binary');
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                    header('Pragma: public');

                    header('Content-Length: ' . (int)$fMeta['size']);
                    ob_clean();
                    flush();
                    readfile($file);
                    unlink($file);
                    jexit();
                }

                break;
            case 'onedrive':
                $dropOneDrive = new DropfilesOneDrive();
                $rev          = JFactory::getApplication()->input->getString('vid', '');
                if ($rev !== '') {
                    $dropOneDrive->downloadVersion($id, $rev);
                } else {
                    $file     = $dropOneDrive->downloadFile($id);
                    $filename = htmlspecialchars($file->title . '.' . $file->ext, ENT_QUOTES, 'UTF-8');
                    header('Content-Disposition: attachment; filename="' . $filename . '"');
                    header('Content-Description: File Transfer');
                    header('Content-Type: application/octet-stream');
                    header('Content-Transfer-Encoding: binary');
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                    header('Pragma: public');
                    if ($file->size !== 0) {
                        header('Content-Length: ' . $file->size);
                    }
                    ob_clean();
                    flush();
                    echo $file->datas;
                    jexit();
                }

                break;
            case 'onedrivebusiness':
                $dropOneDriveBusiness   = new DropfilesOneDriveBusiness();
                $rev                    = JFactory::getApplication()->input->getString('vid', '');
                $file                   = $dropOneDriveBusiness->downloadFile($id);
                $filename               = htmlspecialchars($file->title . '.' . $file->ext, ENT_QUOTES, 'UTF-8');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Transfer-Encoding: binary');
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Pragma: public');
                if ($file->size !== 0) {
                    header('Content-Length: ' . $file->size);
                }
                ob_clean();
                flush();
                echo $file->datas;
                jexit();

                break;
            default:
                if (!$version) {
                    $file = $model->getItem($id);
                } else {
                    $file = $model->getVersion($id);
                }
                if (!$file) {
                    $this->setRedirect('index.php');
                    $this->redirect();
                }

                if ($file->id) {
                    if (strpos($file->file, 'http') !== false) {
                        header('Location: ' . $file->file);
                    } else {
                        if (!(bool)$version) {
                            $sysfile = DropfilesBase::getFilesPath($file->catid) . '/' . $file->file;
                        } else {
                            $sysfile = DropfilesBase::getVersionPath($file->catid) . '/' . $file->file;
                        }

                        if (file_exists($sysfile)) {
                            $filename = htmlspecialchars($file->title . '.' . $file->ext, ENT_QUOTES, 'UTF-8');
                            header('Content-Disposition: attachment; filename="' . $filename . '"');
                            header('Content-Description: File Transfer');
                            header('Content-Type: application/octet-stream');
                            header('Content-Transfer-Encoding: binary');
                            header('Expires: 0');
                            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                            header('Pragma: public');
                            header('Content-Length: ' . filesize($sysfile));
                            ob_clean();

                            $params = JComponentHelper::getParams('com_dropfiles');
                            if ((int) $params->get('readfiletype', 0) === 0) {
                                flush();
                                readfile($sysfile);
                            } else {
                                ob_end_flush();
                                $handle = fopen($sysfile, 'rb');
                                while (!feof($handle)) {
                                    echo fread($handle, 1000);
                                }
                            }
                            jexit();
                        }
                    }
                }
                break;
        }
    }

    /**
     * Delete version file
     *
     * @return void
     * @throws \Exception Throw when application can not start
     * @since  version
     */
    public function deleteVersion()
    {
        $model = $this->getModel();

        $versionId      = JFactory::getApplication()->input->getString('vid', 0);
        $id_file = JFactory::getApplication()->input->getString('id_file', 0);
        $catid   = JFactory::getApplication()->input->getInt('catid', 0);

        if (!$this->allowEdit()) {
            $this->setRedirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));
            $this->redirect();
        }

        $modelC   = $this->getModel('category');
        $category = $modelC->getCategory($catid);

        if (empty($category)) {
            $this->exitStatus('Error deleting');
        }

        switch ($category->type) {
            case 'googledrive':
                $google = new DropfilesGoogle();
                if (!$google->deleteRevision($id_file, $versionId, $category->cloud_id)) {
                    $this->exitStatus('Error deleting');
                }
                break;
            case 'dropbox':
                break;
            default:
                $file = $model->getVersion($versionId);
                if ($file->catid !== $category->id) {
                    $this->exitStatus('Error deleting');
                }
                if (!$model->deleteVersion($versionId, $id_file)) {
                    $this->exitStatus('Error deleting');
                }
                JFile::delete(DropfilesBase::getVersionPath($file->catid) . $file->file);
                break;
        }
        $this->exitStatus(true);
    }

    /**
     * Restore Version
     *
     * @return void
     * @throws \Exception Throw when application can not start
     * @since  1.0
     */
    public function restoreVersion()
    {
        $app     = JFactory::getApplication();
        $id_file = $app->input->getString('id_file', 0);
        $catid   = $app->input->getInt('catid', 0);
        $rev     = $app->input->getString('vid', '');
        $id      = $app->input->getString('id', 0);
        $date    = JFactory::getDate();

        if (!$this->allowEdit()) {
            $this->setRedirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));
            $this->redirect();
        }

        $modelC   = $this->getModel('category');
        $category = $modelC->getCategory($catid);

        if ($category->type === 'dropbox') {
            $dropbox = new DropfilesDropbox();
            $dropbox->restoreVersion($id_file, $rev);
        } elseif ($category->type === 'googledrive') {
            $google      = new DropfilesGoogle();
            $version     = $google->updateRevision($id_file, $rev); // todo: Not working
            $googleFiles = $this->getModel('googlefiles');
            $googleFiles->restoreVersion($id_file, $version->getSize());
        } elseif ($category->type === 'onedrive') {
            $onedrive = new DropfilesOneDrive();
            // Restore version on Onedrive
            $version  = $onedrive->restoreVersion($id_file, $rev);
            // Update local database file info
            $modelOnedrive = $this->getModel('onedrivefiles');
            $dbFile = $modelOnedrive->getFile($id_file);
            if ($dbFile) {
                $modified_time              = $date->setTimestamp(
                    strtotime($version->getLastModifiedDateTime())
                )->toSql();
                $file_data                  = array();
                $file_data['id']            = $dbFile->id; // Current file id
                $file_data['size']          = $version->getSize();
                $file_data['modified_time'] = $modified_time;
                $modelOnedrive->save($file_data);
            }
        } else {
            $modelFiles = $this->getModel('files');
            $version    = $modelFiles->getInfoVersion($id);
            $file       = $modelFiles->getFile($id_file);
            $update     = $modelFiles->updateFile(
                array(
                    'id' => $id_file,
                    'file' => $file->file,
                    'ext' => $version->ext,
                    'size' => $version->size
                )
            );

            if ($update) {
                JFile::delete(DropfilesBase::getFilesPath($file->catid) . '/' . $file->file);
                JFile::copy(
                    DropfilesBase::getVersionPath($file->catid) . '/' . $version->file,
                    DropfilesBase::getFilesPath($file->catid) . '/' . $file->file
                );
            }
        }
        $this->exitStatus(true);
    }

    /**
     * Check copy OneDrive response
     *
     * @return void
     * @throws \Exception Throw when application can not start
     * @since  1.0
     */
    public function oneDriveCopyRespone()
    {
        $url         = JFactory::getApplication()->input->getString('url', 0);
        $cat_id      = JFactory::getApplication()->input->getString('cat_id', 0);
        $onedrive    = new DropfilesOneDrive();
        $bodyRequest = $onedrive->getResponseBodyRequest($url);

        if ($bodyRequest->status === 'completed') {
            $resourceId = DropfilesCloudHelper::replaceIdOneDrive($bodyRequest->resourceId);
            $file       = $onedrive->getOneDriveFileInfos($resourceId, $cat_id);
            $user       = JFactory::getUser();

            if ($file) {
                $modelOnedrive              = $this->getModel('onedrivefiles');
                $file_data                  = array();
                $file_data['id']            = 0;
                $file_data['title']         = $file['title'];
                $file_data['file_id']       = $file['id'];
                $file_data['ext']           = $file['ext'];
                $file_data['size']          = $file['size'];
                $file_data['catid']         = $cat_id;
                $file_data['path']          = '';
                $file_data['created_time']  = $file['created_time'];
                $file_data['modified_time'] = $file['modified_time'];
                $file_data['author']        = $user->get('id');

                $modelOnedrive->save($file_data);
            }
        }
        $this->exitStatus(true);
    }

    /**
     * Save multiple category to file meta
     *
     * @param mixed   $modelCat                Category model
     * @param array   $file_multi_category_old Old category list
     * @param array   $file_multi_category     Category list
     * @param string  $id_file                 File id
     * @param integer $idCategory              Category id
     *
     * @return void
     */
    public function saveCatRefToFiles($modelCat, $file_multi_category_old, $file_multi_category, $id_file, $idCategory)
    {
        $lst_catRef_del = array();
        if ((!empty($file_multi_category_old) && $file_multi_category) && $file_multi_category_old) {
            $lst_catRef_del = array_diff($file_multi_category_old, $file_multi_category);
        }
        if (!empty($file_multi_category) && $file_multi_category) {
            foreach ($file_multi_category as $value) {
                if (trim($value) !== '') {
                    $modelCat->saveRefToFiles($value, $id_file, $idCategory);
                }
            }
            if (!empty($lst_catRef_del) && $lst_catRef_del) {
                foreach ($lst_catRef_del as $value) {
                    if (trim($value) !== '') {
                        $modelCat->deleteRefToFiles($value, $id_file, $idCategory);
                    }
                }
            }
        } elseif (!empty($file_multi_category_old)) {
            foreach ($file_multi_category_old as $value) {
                if (trim($value) !== '') {
                    $modelCat->deleteRefToFiles($value, $id_file, $idCategory);
                }
            }
        }
    }
}
