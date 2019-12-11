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


        $author_user_id = 0;
        $file = array();
        $preview = JFactory::getApplication()->input->getInt('preview', 0);
        JLoader::register('DropfilesFilesHelper', JPATH_ADMINISTRATOR . '/components/com_dropfiles/helpers/files.php');
        switch ($category->type) {
            case 'googledrive':
                $path_dropfilesgoogle = JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesGoogle.php';
                JLoader::register('DropfilesGoogle', $path_dropfilesgoogle);
                $google = new DropfilesGoogle();
                $file = $google->download($id, $category->cloud_id, false, $preview);

                if (!is_object($file)) {
                    $this->setRedirect('index.php');
                    $this->redirect();
                }

                //$google->incrHits($id);
                $modelGoogle = $this->getModel('Frontgoogle');
                $modelGoogle->incrHits($id);
                $model->addCountChart($id);

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
                    header('Content-Disposition: inline; filename="'
                        . htmlspecialchars($file->title . '.' . $file->ext) . '"');
                } else {
                    header('Content-Disposition: attachment; filename="'
                        . htmlspecialchars($file->title . '.' . $file->ext) . '"');
                }

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

                break;
            case 'dropbox':
                $path_dropfilesdropbox = JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesDropbox.php';
                JLoader::register('DropfilesDropbox', $path_dropfilesdropbox);

                $dropCate = new DropfilesDropbox();
                $rev = JFactory::getApplication()->input->getString('vid', '');
                if ($rev !== '') {
                    $dropCate->downloadVersion($id, $rev);
                } else {
                    list($file, $fMeta) = $dropCate->downloadDropbox($id);
                    $ext = strtolower(pathinfo($fMeta['path_lower'], PATHINFO_EXTENSION));

                    $modelDropbox = $this->getModel('Frontdropbox');
                    $modelDropbox->incrHits($id);
                    $model->addCountChart($id);

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
                        . htmlspecialchars($file->title . '.' . $file->ext) . '"');
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
            default:
                $file = $model->getFile($id);
                if (!$file) {
                    $this->setRedirect('index.php');
                    $this->redirect();
                }
                $model->hit($id);
                $model->addCountChart($id);

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

                                $size   = filesize($sysfile); // File size
                                $length = $size;              // Content length
                                $start  = 0;                  // Start byte
                                $end    = $size - 1;          // End byte

                                header('Accept-Ranges: bytes');
                                if (isset($_SERVER['HTTP_RANGE'])) {
                                    $c_end = $end;
                                    list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
                                    if (strpos($range, ',') !== false) {
                                        header('HTTP/1.1 416 Requested Range Not Satisfiable');
                                        //header("Content-Range: bytes $start-$end/$size");
                                        header(sprintf('Content-Range: bytes %d-%d/%d', $start, $end, $size));
                                        exit;
                                    }
                                    if ($range === '-') {
                                        $c_start = $size - substr($range, 1);
                                    } else {
                                        $range   = explode('-', $range);
                                        $c_start = $range[0];
                                        $c_end   = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $size;
                                    }
                                    $c_end = ($c_end > $end) ? $end : $c_end;
                                    if ($c_start > $c_end || $c_start > $size - 1 || $c_end >= $size) {
                                        header('HTTP/1.1 416 Requested Range Not Satisfiable');
                                        //header("Content-Range: bytes $start-$end/$size");
                                        header(sprintf('Content-Range: bytes %d-%d/%d', $start, $end, $size));
                                        exit;
                                    }
                                    $start = $c_start;
                                    $end = $c_end;
                                    $length = $end - $start + 1;
                                    fseek($fp, $start);
                                    if ($length < $size) {
                                        header('HTTP/1.1 206 Partial Content');
                                    }
                                }

                                //header("Content-Range: bytes $start-$end/$size");
                                header(sprintf('Content-Range: bytes %d-%d/%d', $start, $end, $size));
                                header('Content-Length: ' . $length);


                                $buffer = 1024 * 8;
                                while (!feof($fp) && ($p = ftell($fp)) <= $end) {
                                    if ($p + $buffer > $end) {
                                        $buffer = $end - $p + 1;
                                    }
                                    set_time_limit(0);
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

        if ((int) $params->get('file_owner', 0) === 1 && (int) $params->get('download_event', 0) === 1) {
            $user = JFactory::getUser($author_user_id);
            $email_body = str_replace('{receiver}', $user->name, $email_body);
            DropfilesHelper::sendMail($user->email, $email_title, $email_body);
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
}
