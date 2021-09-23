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

//-- No direct access
defined('_JEXEC') || die('=;)');


jimport('joomla.application.component.controller');
jimport('joomla.filesystem.file');

/**
 * Class DropfilesControllerFrontfile
 */
class DropfilesControllerFrontfile extends JControllerLegacy
{
    /**
     * Method get model
     *
     * @param string $name   Model name
     * @param string $prefix Model prefix
     * @param array  $config Model config
     *
     * @return mixed
     * @since  version
     */
    public function getModel($name = 'frontfile', $prefix = 'dropfilesModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }


    /**
     * Download load file
     *
     * @return void
     * @since  version
     */
    public function download()
    {
        $app = JFactory::getApplication();
        $model = $this->getModel('frontfile');

        $id = JFactory::getApplication()->input->getString('id', 0);
        $catid = JFactory::getApplication()->input->getInt('catid', 0);

        $catmod = $this->getModel('frontcategory');

        $category = $catmod->getCategory($catid);
        $user = JFactory::getUser();
        $groups = $user->getAuthorisedViewLevels();

        $config = JComponentHelper::getParams('com_dropfiles');
        if ($config->get('categoryrestriction', 'accesslevel') === 'accesslevel') {
            if (!in_array($category->access, $groups)) {
                $token = $app->input->getString('token');
                $modelTokens = $this->getModel('tokens');
                $modelTokens->removeTokens();
                $tokenId = $modelTokens->tokenExists($token);
                if ($tokenId) {
                    $modelTokens->updateToken($tokenId);
                } else {
                    $this->setRedirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));
                    $this->redirect();
                }
            }
        } else {
            $modelConfig = $this->getModel('frontconfig');
            $params = $modelConfig->getParams($catid);
            $usergroup = isset($params->params->usergroup) ? $params->params->usergroup : array();
            $user = JFactory::getUser();
            $result = array_intersect($user->getAuthorisedGroups(), $usergroup);

            if (!count($result)) {
                $token = $app->input->getString('token');
                $modelTokens = $this->getModel('tokens');
                $modelTokens->removeTokens();
                $tokenId = $modelTokens->tokenExists($token);
                if ($tokenId) {
                    $modelTokens->updateToken($tokenId);
                } else {
                    $this->setRedirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));
                    $this->redirect();
                }
            }
        }

        if ($config->get('restrictfile', 0)) {
            $user_id = (int) $user->id;
            $modelConfig = $this->getModel('frontconfig');
            $params = $modelConfig->getParams($catid);
            $canViewCategory = isset($params->params->canview) ? (int) $params->params->canview : 0;
            if ($user_id) {
                if (!($canViewCategory === $user_id || $canViewCategory === 0)) {
                    $this->setRedirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));
                    $this->redirect();
                }
            } else {
                if ($canViewCategory !== 0) {
                    $this->setRedirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));
                    $this->redirect();
                }
            }
        }

        $author_user_id = 0;
        $file = array();
        $preview = JFactory::getApplication()->input->getInt('preview', 0);
        JLoader::register('DropfilesFilesHelper', JPATH_ADMINISTRATOR . '/components/com_dropfiles/helpers/files.php');
        switch ($category->type) {
            case 'googledrive':
                $path_dropfilesgoogle = JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesGoogle.php';
                JLoader::register('DropfilesGoogle', $path_dropfilesgoogle);
                $google = new DropfilesGoogle();
                $file = $google->download($id);
                $userId = (isset($user->id)) ? $user->id : 0;

                if (!is_object($file)) {
                    $this->setRedirect('index.php');
                    $this->redirect();
                }

                //$google->incrHits($id);
                $modelGoogle = $this->getModel('Frontgoogle');
                $modelGoogle->incrHits($id);
                $model->addCountChart($id, $userId);

                $file2 = $modelGoogle->getFile($id);
                if ($file2) {
                    $author_user_id = $file2->author;
                }
                if ($preview) {
                    $contentType = DropfilesFilesHelper::mimeType($file->ext);
                } else {
                    $contentType = 'application/octet-stream';
                }

                if ((int) $config->get('open_pdf_in', 0) === 1 && $file->ext === 'pdf' && (int) $preview === 1) {
                    $disposition = 'inline';
                } else {
                    $disposition = 'attachment';
                }
                // Serve download for google document
                if (strpos($file->mimeType, 'vnd.google-apps') !== false) { // Is google file
                    // GuzzleHttp\Psr7\Response
                    $fileData = $google->downloadGoogleDocument($file->id, $file->exportMineType);

                    if ($fileData instanceof \GuzzleHttp\Psr7\Response) {
                        $contentLength = $fileData->getHeaderLine('Content-Length');
                        $contentType = $fileData->getHeaderLine('Content-Type');

                        if ($fileData->getStatusCode() === 200) {
                            header('Content-Disposition: ' . $disposition . '; filename="' . htmlspecialchars($file->title . '.' . $file->ext, ENT_QUOTES, 'UTF-8') . '"');
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

                break;
            case 'dropbox':
                $path_dropfilesdropbox = JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesDropbox.php';
                JLoader::register('DropfilesDropbox', $path_dropfilesdropbox);

                $dropCate = new DropfilesDropbox();
                $rev = JFactory::getApplication()->input->getString('vid', '');
                $userId = (isset($user->id)) ? $user->id : 0;
                if ($rev !== '') {
                    $dropCate->downloadVersion($id, $rev);
                } else {
                    list($file, $fMeta) = $dropCate->downloadDropbox($id);
                    $ext = strtolower(pathinfo($fMeta['path_lower'], PATHINFO_EXTENSION));

                    $modelDropbox = $this->getModel('Frontdropbox');
                    $modelDropbox->incrHits($id);
                    $model->addCountChart($id, $userId);

                    $file2 = $modelDropbox->getFile($id);
                    if ($file2) {
                        $author_user_id = $file2->author;
                    }

                    ob_end_clean();
                    ob_start();
                    if ($preview) {
                        $contentType = DropfilesFilesHelper::mimeType($ext);
                    } else {
                        $contentType = 'application/octet-stream';
                    }
                    if ((int) $config->get('open_pdf_in', 0) === 1 && $ext === 'pdf' && (int) $preview === 1) {
                        header('Content-Disposition: inline; filename="' . $fMeta['name'] . '"');
                    } else {
                        header('Content-Disposition: attachment; filename="' . $fMeta['name'] . '"');
                    }

                    header('Content-Description: File Transfer');
                    header('Content-Type: ' . $contentType);
                    header('Content-Transfer-Encoding: binary');
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                    header('Pragma: public');

                    header('Content-Length: ' . (int)$fMeta['size']);
                    ob_clean();
                    flush();
                    readfile($file);
                    unlink($file);
                }

                break;
            case 'onedrive':
                $path_drf_onedrive = JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesOneDrive.php';
                JLoader::register('DropfilesOneDrive', $path_drf_onedrive);
                $dropOneDrive = new DropfilesOneDrive();
                $rev = JFactory::getApplication()->input->getString('vid', '');
                if ($rev !== '') {
                    $dropOneDrive->downloadVersion($id, $rev);
                } else {
                    $file = $dropOneDrive->downloadFile($id);

                    header('Content-Disposition: attachment; filename="'
                        . htmlspecialchars($file->title . '.' . $file->ext, ENT_QUOTES, 'UTF-8') . '"');
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
                $path_drf_onedrive_business = JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesOneDriveBusiness.php';
                JLoader::register('DropfilesOneDriveBusiness', $path_drf_onedrive_business);
                $dropOneDriveBusiness   = new DropfilesOneDriveBusiness();
                $rev                    = JFactory::getApplication()->input->getString('vid', '');
                $file                   = $dropOneDriveBusiness->downloadFile($id);
                $filename               = htmlspecialchars($file->title . '.' . $file->ext, ENT_QUOTES, 'UTF-8');
                if ($preview) {
                    $contentType = DropfilesFilesHelper::mimeType($file->ext);
                } else {
                    $contentType = 'application/octet-stream';
                }
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Content-Description: File Transfer');
                header('Content-Type: ' . $contentType);
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
                $file = $model->getFile($id);
                $userId = (isset($user->id)) ? $user->id : 0;
                if (!$file) {
                    $this->setRedirect('index.php');
                    $this->redirect();
                }
                $model->hit($id);
                $model->addCountChart($id, $userId);

                $modelFiles = $this->getModel('frontfile');
                $file2 = $modelFiles->getFile($id);
                if ($file2) {
                    $author_user_id = $file2->author;
                }

                if ($file->id) {
                    if (strpos($file->file, 'http') !== false) {
                        header('Location: ' . $file->file);
                    } else {
                        if ($preview) {
                            $contentType = DropfilesFilesHelper::mimeType($file->ext);
                        } else {
                            $contentType = 'application/octet-stream';
                        }
                        $path_drf_base = JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesBase.php';
                        JLoader::register('DropfilesBase', $path_drf_base);
                        $sysfile = DropfilesBase::getFilesPath($file->catid) . '/' . $file->file;
                        if (file_exists($sysfile)) {
                            $fileTitle = $file->title;
                            // Fix wrong file name in iOS
                            preg_match('/iPhone|iPad|iPod|Android|webOS/', $_SERVER['HTTP_USER_AGENT'], $matches);
                            $os = current($matches);
                            if (in_array($os, array('iPhone','iPad'))) {
                                $fileTitle = iconv('UTF-8', 'ISO-8859-1//IGNORE', $file->title);
                                if (empty($fileTitle)) {
                                    $fileTitle = $file->title;
                                }
                                $contentType = DropfilesFilesHelper::mimeType($file->ext);
                            }

                            $fileTitle .= '.' . $file->ext;
                            if ((int) $config->get('open_pdf_in', 0) === 1 && $file->ext === 'pdf' && (int) $preview === 1) {
                                header('Content-Disposition: inline; filename="'
                                    . $fileTitle . '"');
                            } else {
                                header('Content-Disposition: attachment; filename="'
                                    . $fileTitle . '"');
                            }

                            header('Content-Description: File Transfer');
                            header('Content-Type: ' . $contentType);
                            header('Content-Transfer-Encoding: binary');
                            header('Expires: 0');
                            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                            header('Pragma: public');
                            header('Content-Length: ' . filesize($sysfile));
                            ob_clean();

                            //support http header range to make video work on iOS
                            $videoTypes = array('mp3', 'm4a', 'mp4', 'webm', 'ogg', 'ogv', 'flv');
                            if (in_array($file->ext, $videoTypes) && $preview) {
                                // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged -- Not print any error when download file
                                $fp = @fopen($sysfile, 'rb');
                                $fileSize   = filesize($sysfile); // File size

                                if (isset($_SERVER['HTTP_RANGE'])) {
                                    list($sizeUnit, $rangeOrig) = explode('=', $_SERVER['HTTP_RANGE'], 2);
                                    if ($sizeUnit === 'bytes') {
                                        // multiple ranges could be specified at the same time,
                                        // but for simplicity only serve the first range
                                        // http://tools.ietf.org/id/draft-ietf-http-range-retrieval-00.txt
                                        list($range, $extraRanges) = explode(',', $rangeOrig, 2);
                                    } else {
                                        $range = '';
                                        header('HTTP/1.1 416 Requested Range Not Satisfiable');

                                        jexit();
                                    }
                                } else {
                                    $range = '';
                                }
                                // figure out download piece from range (if set)
                                list($seekStart, $seekEnd) = explode('-', $range, 2);
                                // set start and end based on range (if set), else set defaults
                                // also check for invalid ranges.
                                $seekEnd   = (empty($seekEnd)) ? ($fileSize - 1) : min(abs(intval($seekEnd)), ($fileSize - 1));
                                $seekStart = (empty($seekStart) || $seekEnd < abs(intval($seekStart))) ?
                                    0 : max(abs(intval($seekStart)), 0);
                                // Only send partial content header if downloading a piece of the file (IE workaround)
                                if ($seekStart > 0 || $seekEnd < ($fileSize - 1)) {
                                    header('HTTP/1.1 206 Partial Content');
                                    header('Content-Range: bytes ' . $seekStart . '-' . $seekEnd . '/' . $fileSize);
                                    if (stripos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') === false) {
                                        header('Content-Length: ' . ($seekEnd - $seekStart + 1));
                                    }
                                } else {
                                    if (stripos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') === false) {
                                        header('Content-Length: ' . $fileSize);
                                    }
                                }

                                header('Accept-Ranges: bytes');
                                // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged -- Not print any error when download file
                                @set_time_limit(0);
                                $buffer = 1024 * 8;
                                fseek($fp, $seekStart);
                                while (!feof($fp) && ($p = ftell($fp)) <= $seekEnd) {
                                    if ($p + $buffer > $seekEnd) {
                                        $buffer = $seekEnd - $p + 1;
                                    }
                                    echo fread($fp, $buffer);
                                    flush();
                                }

                                fclose($fp);
                                exit();
                            }


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
                        }
                    }
                }
                break;
        }


        $params = JComponentHelper::getParams('com_dropfiles');

        $email_title = JText::_('COM_DROPFILES_EMAIL_DOWNLOAD_EVENT_TITLE');

        if ($params->get('download_event_subject', '') !== '') {
            $email_title = $params->get('download_event_subject', '');
        }

        $email_body         = $params->get('download_event_editor', DropfilesHelper::getHTMLEmail('file-downloaded.html'));
        $path_icon_download = 'components/com_dropfiles/assets/images/icon-download.png';
        $path_icon_replace  = JUri::root() . 'components/com_dropfiles/assets/images/icon-download.png';
        $email_body         = str_replace($path_icon_download, $path_icon_replace, $email_body);
        $email_body         = str_replace('{category}', $category->title, $email_body);
        $email_body         = str_replace('{website_url}', JUri::root(), $email_body);
        $email_body         = str_replace('{file_name}', $file->title, $email_body);
        $currentUser        = JFactory::getUser();
        $email_body         = str_replace('{username}', $currentUser->name, $email_body);
        $uploader       = JFactory::getUser($author_user_id);
        $email_body     = str_replace('{uploader_username}', $uploader->name, $email_body);
        if ((int) $params->get('file_owner', 0) === 1 && (int) $params->get('download_event', 0) === 1) {
            $email_body = str_replace('{receiver}', $uploader->name, $email_body);
            DropfilesHelper::sendMail($uploader->email, $email_title, $email_body);
        }

        if ((int)$params->get('category_owner', 0) === 1 && (int) $params->get('download_event', 0) === 1) {
            $user = JFactory::getUser($category->created_user_id);
            $email_body = str_replace('{receiver}', $user->name, $email_body);
            DropfilesHelper::sendMail($user->email, $email_title, $email_body);
        }

        if ($params->get('download_event_additional_email', '') !== '' && (int) $params->get('download_event', 1) === 1) {
            $emails = explode(',', $params->get('download_event_additional_email', ''));
            if (!empty($emails)) {
                foreach ($emails as $email) {
                    DropfilesHelper::sendMail($email, $email_title, $email_body);
                }
            }
        }

        if ((int) $params->get('notify_super_admin', 0) === 1 && (int) $params->get('download_event', 0) === 1) {
            $users = DropfilesHelper::getSuperAdmins();

            if (count($users)) {
                foreach ($users as $item) {
                    $user = JFactory::getUser($item->user_id);
                    $email_body = str_replace('{receiver}', $user->name, $email_body);
                    DropfilesHelper::sendMail($user->email, $email_title, $email_body);
                }
            }
        }

        jexit();
    }


    /**
     * Get subs category
     *
     * @return void
     * @since  version
     */
    public function getSubs()
    {
        $modelCats = JModelLegacy::getInstance('Frontcategories', 'dropfilesModel');
        $catid = JFactory::getApplication()->input->getInt('id', 0);

        $modelCats->setState('category.id', $catid);
        //$modelCats->setState('filter.parentId',$catid);

        $cats = $modelCats->getItems(true);

        if (count($cats)) {
            foreach ($cats as $cat) {
                $cat->count_child = $modelCats->getSubCategoriesCount($cat->id);
            }
        }
        echo json_encode($cats);
        die();
    }

    /**
     * Method to download files in categories
     *
     * @param integer $category_id   Category id
     * @param string  $category_name Category name
     *
     * @return void
     *
     * @throws Exception Throw when error of download files
     */
    public function downloadCategory($category_id = null, $category_name = null)
    {
        $app       = JFactory::getApplication();
        $modelCate = $this->getModel('frontcategory', 'DropfilesModel');
        if ($category_id === null && $category_name === null) {
            $category_id = $app->input->getInt('cate_id', 0);
        }
        $category = $modelCate->getCategory($category_id);
        $category_name  = $category->title;
        $upload_dir = JPATH_ROOT . '/media/com_dropfiles/';
        $listFiles  = $this->getAllFiles($category_id);
        if (empty($listFiles) && !$listFiles) {
            $app->enqueueMessage(JText::_('COM_DROPFILES_ERROR_EMPTY_CATEGORY'), 'error');
        } else {
            // Calculate zip file name
            $zipName      = $upload_dir . $category_id . '-';
            $allFilesName = '';
            foreach ($listFiles as $file) {
                $allFilesName .= $file->title;
                $allFilesName .= $file->size;
                if ($file->size < 0) {
                    continue;
                } else {
                    if (!file_exists($upload_dir . $file->catid . '/' . $file->file)) {
                        continue;
                    }
                    $allFilesName .= filemtime($upload_dir . $file->catid . '/' . $file->file);
                }
            }

            $zipName .= md5($allFilesName) . '.zip';

            if (!file_exists($zipName)) {
                // Remove all old files with same category id
                $files = glob($upload_dir . $category_id . '-*.zip');
                if (!empty($files) && count($files) > 0) {
                    foreach ($files as $file) {
                        if (is_file($file)) {
                            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                            if ($ext === 'zip') {
                                unlink($file);
                            }
                        }
                    }
                }

                // Start zip new file
                $zipFiles = new ZipArchive();
                $zipFiles->open($zipName, ZipArchive::CREATE);
                if (!empty($listFiles) && count($listFiles) > 0) {
                    foreach ($listFiles as $key => $filevl) {
                        $sysfile = $upload_dir . $filevl->catid . '/' . $filevl->file;
                        $file_name =  trim($filevl->title);
                        $count = 0;
                        for ($i = 0; $i < $zipFiles->numFiles; $i++) {
                            if ($zipFiles->getNameIndex($i) === $file_name . '.' . $filevl->ext) {
                                $count++;
                            }
                        }
                        if ($count > 0) {
                            $file_name = $file_name . '(' . $count . ')';
                        }
                        $zipFiles->addFile($sysfile, $file_name . '.' . $filevl->ext);
                    }
                }
                $zipFiles->close();
            }
            $this->SendDownload($zipName, $category_name . '.zip', 'zip');
            exit();
        }
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
    private function getAllFileRef($model, $listCatRef, $ordering, $orderingdir)
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

    /**
     * Get all files in category
     *
     * @param integer $catid Category id
     *
     * @return array|string
     *
     * @throws Exception Throw when error of get all files
     */
    private function getAllFiles($catid)
    {
        $app = JFactory::getApplication();
        $modelFiles  = $this->getModel('frontfiles', 'dropfilesModel');
        $modelCate   = $this->getModel('frontcategory', 'dropfilesModel');
        $category    = $modelCate->getCategory($catid);
        $params      = $category->params;
        $files       = $modelFiles->getListOfCate($catid);
        $subparams   = (array) $params;
        $lstAllFile  = null;
        $ordering    = (isset($params->ordering)) ? $params->ordering : '';
        $orderingdir = (isset($params->orderingdir)) ? $params->orderingdir : '';

        if (!empty($subparams) && isset($subparams['refToFile'])) {
            if (isset($subparams['refToFile'])) {
                $listCatRef = $subparams['refToFile'];
                $lstAllFile = $this->getAllFileRef($modelFiles, $listCatRef, $ordering, $orderingdir);
            }
        }

        if (!empty($lstAllFile)) {
            $files = array_merge($lstAllFile, $files);
        }

        return $files;
    }

    /**
     * Send Download File to the browser
     *
     * @param string $filePath Absolute path to the file
     * @param string $fileName File name return to Browser
     * @param string $fileExt  File extension for check it mime
     *
     *
     * Copyright 2012 Armand Niculescu - media-division.com
     * Redistribution and use in source and binary forms, with or without modification,
     * are permitted provided that the following conditions are met:
     * 1. Redistributions of source code must retain the above copyright notice,
     * this list of conditions and the following disclaimer.
     * 2. Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the
     * following disclaimer in the documentation and/or other materials provided with the distribution.
     * THIS SOFTWARE IS PROVIDED BY THE FREEBSD PROJECT "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING,
     * BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
     * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE FREEBSD PROJECT OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
     * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT
     * OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
     * ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
     * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
     *
     * @return boolean|void
     */
    public static function sendDownload($filePath, $fileName, $fileExt)
    {
        // phpcs:disable Generic.PHP.NoSilencedErrors.Discouraged,WordPress.Security.EscapeOutput.OutputNotEscaped,WordPress.Security.NonceVerification.Recommended -- not print any error to file content, output is file content, $_REQUEST['stream'] is checking condition
        @ini_set('error_reporting', E_ALL & ~E_NOTICE);
        @ini_set('zlib.output_compression', 'Off');

        while (ob_get_level()) {
            ob_end_clean();
        }

        // make sure the file exists on server
        if (is_file($filePath)) {
            $fileSize    = filesize($filePath);
            $fileHandler = @fopen($filePath, 'rb');
            if ($fileHandler) {
                // set the headers, prevent caching
                header('Pragma: public');
                header('Expires: -1');
                header('Cache-Control: public, must-revalidate, post-check=0, pre-check=0');
                // set appropriate headers for attachment or streamed file
                header('Content-Disposition: attachment; filename="' . $fileName . '"; filename*=UTF-8\'\'' . rawurlencode($fileName));
                header('Content-Type: ' . self::mimeType($fileExt));

                // check if http_range is sent by browser (or download manager)
                // todo: Apply multiple ranges
                if (isset($_SERVER['HTTP_RANGE'])) {
                    list($sizeUnit, $rangeOrig) = explode('=', $_SERVER['HTTP_RANGE'], 2);
                    if ($sizeUnit === 'bytes') {
                        // multiple ranges could be specified at the same time,
                        // but for simplicity only serve the first range
                        // http://tools.ietf.org/id/draft-ietf-http-range-retrieval-00.txt
                        list($range, $extraRanges) = explode(',', $rangeOrig, 2);
                    } else {
                        $range = '';
                        header('HTTP/1.1 416 Requested Range Not Satisfiable');

                        return false;
                    }
                } else {
                    $range = '';
                }
                // figure out download piece from range (if set)
                list($seekStart, $seekEnd) = explode('-', $range, 2);
                // set start and end based on range (if set), else set defaults
                // also check for invalid ranges.
                $seekEnd   = (empty($seekEnd)) ? ($fileSize - 1) : min(abs(intval($seekEnd)), ($fileSize - 1));
                $seekStart = (empty($seekStart) || $seekEnd < abs(intval($seekStart))) ?
                    0 : max(abs(intval($seekStart)), 0);
                // Only send partial content header if downloading a piece of the file (IE workaround)
                if ($seekStart > 0 || $seekEnd < ($fileSize - 1)) {
                    header('HTTP/1.1 206 Partial Content');
                    header('Content-Range: bytes ' . $seekStart . '-' . $seekEnd . '/' . $fileSize);
                    if (stripos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') === false) {
                        header('Content-Length: ' . ($seekEnd - $seekStart + 1));
                    }
                } else {
                    if (stripos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') === false) {
                        header('Content-Length: ' . $fileSize);
                    }
                }
                header('Accept-Ranges: bytes');
                @set_time_limit(0);
                fseek($fileHandler, $seekStart);
                while (!feof($fileHandler)) {
                    print(@fread($fileHandler, 1024 * 8));
                    @ob_flush();
                    flush();
                    if (connection_status() !== 0) {
                        @fclose($fileHandler);

                        return true;
                    }
                }
                // File save was a success
                @fclose($fileHandler);

                return true;
            } else {
                // File couldn't be opened
                header('HTTP/1.0 500 Internal Server Error');

                return false;
            }
        } else {
            // File does not exist
            header('HTTP/1.0 404 Not Found');

            return false;
        }
        // phpcs:enable
    }

    /**
     * Get mime type
     *
     * @param string $ext Extension
     *
     * @return string
     */
    public static function mimeType($ext)
    {
        $mime_types = array(
            //flash
            'swf'   => 'application/x-shockwave-flash',
            'flv'   => 'video/x-flv',
            // images
            'png'   => 'image/png',
            'jpe'   => 'image/jpeg',
            'jpeg'  => 'image/jpeg',
            'jpg'   => 'image/jpeg',
            'gif'   => 'image/gif',
            'bmp'   => 'image/bmp',
            'ico'   => 'image/vnd.microsoft.icon',
            'tiff'  => 'image/tiff',
            'tif'   => 'image/tiff',
            'svg'   => 'image/svg+xml',
            'svgz'  => 'image/svg+xml',

            // audio
            'mid'   => 'audio/midi',
            'midi'  => 'audio/midi',
            'mp2'   => 'audio/mpeg',
            'mp3'   => 'audio/mpeg',
            'mpga'  => 'audio/mpeg',
            'aif'   => 'audio/x-aiff',
            'aifc'  => 'audio/x-aiff',
            'aiff'  => 'audio/x-aiff',
            'ram'   => 'audio/x-pn-realaudio',
            'rm'    => 'audio/x-pn-realaudio',
            'rpm'   => 'audio/x-pn-realaudio-plugin',
            'ra'    => 'audio/x-realaudio',
            'wav'   => 'audio/x-wav',
            'wma'   => 'audio/wma',
            'm4a'   => 'audio/m4a',

            //Video
            'mp4'   => 'video/mp4',
            'mpeg'  => 'video/mpeg',
            'mpe'   => 'video/mpeg',
            'mpg'   => 'video/mpeg',
            'mov'   => 'video/quicktime',
            'qt'    => 'video/quicktime',
            'rv'    => 'video/vnd.rn-realvideo',
            'avi'   => 'video/x-msvideo',
            'movie' => 'video/x-sgi-movie',
            '3gp'   => 'video/3gpp',
            'webm'  => 'video/webm',
            'ogv'   => 'video/ogg',
            //doc
            'pdf'   => 'application/pdf'
        );

        if (array_key_exists(strtolower($ext), $mime_types)) {
            return $mime_types[strtolower($ext)];
        } else {
            return 'application/octet-stream';
        }
    }

    /**
     * Zip file
     *
     * @param null|string  $filesId     Files id
     * @param null|integer $category_id Category id
     *
     * @return void
     *
     * @throws Exception Throw when error of zip file
     */
    public function zipSeletedFiles($filesId = null, $category_id = null)
    {
        $app = JFactory::getApplication();
        $modelCate = JModelLegacy::getInstance('frontcategory', 'dropfilesModel');
        if (is_null($category_id)) {
            $category_id   = $app->input->getInt('dropfiles_category_id', 0);
        }
        if (is_null($filesId)) {
            $filesId   = $app->input->getString('filesId');
        }
        if (empty($filesId) || trim($filesId) === '' || empty($category_id) || trim($category_id) === '') {
            echo json_encode(array('status' => 'error', 'message' => 'Missing files id or category id wrong!'));
        }

        $category = $modelCate->getCategory($category_id);
        // Check category for sure it not come from cloud
        if ($category->type === 'default') {
            // Get files info
            $files = explode(',', $filesId);

            // Clean file id
            $files = array_map(
                function ($f) {
                    return intval(trim($f));
                },
                $files
            );

            $fileModel = $this->getModel('frontfile');

            $filesObj    = array();
            $dropfilesUploadDir = JPATH_ROOT . '/media/com_dropfiles/';
            $zipName     = $dropfilesUploadDir . $category_id . '.selected-';
            $allFilesName = '';

            foreach ($files as $fileId) {
                $file = $fileModel->getFile($fileId);
                /**
                 * Filter of file selected to download
                 *
                 * @param array
                 */

                if (!$file) {
                    continue;
                }

                // Add file
                $filesObj[] = $file;

                // Calculate zip file name to made a hash
                $allFilesName .= $file->title;
                $allFilesName .= $file->size;
                if ($file->size < 0) {
                    continue;
                } else {
                    if (!file_exists($dropfilesUploadDir . $file->catid . '/' . $file->file)) {
                        continue;
                    }
                    $allFilesName .= filemtime($dropfilesUploadDir . $file->catid . '/' . $file->file);
                }
            }
            // Create a hash with all files name
            $hash = md5($allFilesName);
            $zipName .= $hash . '.zip';

            if (file_exists($zipName)) {
                echo json_encode(array('status' => 'error', 'message' => 'file zip exists','hash' => $hash));
                die();
            }
            // Zip it

            if (!empty($filesObj) && count($filesObj) > 0) {
                $zipFiles = new ZipArchive();
                $zipFiles->open($zipName, ZipArchive::CREATE);
                foreach ($filesObj as $file) {
                    $sysfile   = $dropfilesUploadDir . $file->catid . '/' . $file->file;
                    $file_name =  trim($file->title);
                    $count = 0;
                    for ($i = 0; $i < $zipFiles->numFiles; $i++) {
                        if ($zipFiles->getNameIndex($i) === $file_name . '.' . $file->ext) {
                            $count++;
                        }
                    }
                    if ($count > 0) {
                        $file_name = $file_name . '(' . $count . ')';
                    }
                    $zipFiles->addFile($sysfile, $file_name . '.' . $file->ext);
                }
                $zipFiles->close();
            } else {
                echo json_encode(array('status' => 'error', 'message' => 'There is no file to download!'));
                die();
            }

            // Return hashed information
            echo json_encode(array('status' => 'success', 'message' => 'file zip created', 'hash' => $hash));
            die();
        } else {
            echo json_encode(array('status' => 'error', 'message' => 'Sorry, something went wrong! Please contact administrator for more information.'));
            die();
        }
    }

    /**
     * Download ziped file
     *
     * @param null|string  $hash          File hash
     * @param null|integer $category_id   Category id
     * @param null|string  $category_name Category name
     *
     * @return void
     *
     * @throws Exception Throw when error of download ziped file
     */
    public function downloadZipedFile($hash = null, $category_id = null, $category_name = null)
    {
        $app = JFactory::getApplication();
        if (is_null($category_id)) {
            $category_id   = $app->input->getInt('dropfiles_category_id', 0);
        }

        if (is_null($category_name)) {
            $category_name   = $app->input->getString('dropfiles_category_name');
        }

        if (empty($category_name) || $category_name === '') {
            $category_name = time() . '-category-' . $category_id;
        }

        if (is_null($hash)) {
            $hash   = $app->input->getString('hash');
        }
        if (empty($hash) || trim($hash) === '' || empty($category_id)) {
            die(JText::_('COM_DROPFILES_DOWNLOAD_SELECTED_MISSING_HASH_OR_CATEGORY'));
        }

        // Check hash
        $dropfilesUploadDir = JPATH_ROOT . '/media/com_dropfiles/';
        $zipName     = $dropfilesUploadDir . $category_id . '.selected-' . $hash . '.zip';

        if (!file_exists($zipName)) {
            die(JText::_('COM_DROPFILES_DOWNLOAD_SELECTED_FILE_NOT_EXISTS'));
        }
        // Send ziped file if it exists
        $this->sendDownload($zipName, $category_name . '.zip', 'zip');
        // Remove file after download
        unlink($zipName);
        exit();
    }
}
