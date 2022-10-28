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

/**
 * DropfilesFilesHelper class
 */
class DropfilesFilesHelper
{

    /**
     * Convert bytes too file size format
     *
     * @param integer $bytes     Bytes
     * @param integer $precision Precision
     *
     * @return string
     * @since  version
     */
    public static function bytesToSize($bytes, $precision = 2)
    {
        $sz = array('COM_DROPFILES_FIELD_FILE_BYTE',
            'COM_DROPFILES_FIELD_FILE_KILOBYTE',
            'COM_DROPFILES_FIELD_FILE_MEGABYTE',
            'COM_DROPFILES_FIELD_FILE_GIGABYTE',
            'COM_DROPFILES_FIELD_FILE_TERRABYTE',
            'COM_DROPFILES_FIELD_FILE_PETABYTE');
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf('%.' . $precision . 'f', $bytes / pow(1000, $factor)) . ' ' . JText::_($sz[$factor]);
    }

    /**
     * Include JS Helper
     *
     * @return void
     * @since  version
     */
    public static function includeJSHelper()
    {
        $doc = JFactory::getDocument();
        $doc->addScript(JURI::root() . 'components/com_dropfiles/assets/js/helper.js');
        if (DropfilesBase::isJoomla40()) {
            JHtml::_('behavior.core');
        } else {
            JHtml::_('behavior.framework', true);
        }
        JText::script('COM_DROPFILES_FIELD_FILE_BYTE');
        JText::script('COM_DROPFILES_FIELD_FILE_KILOBYTE');
        JText::script('COM_DROPFILES_FIELD_FILE_MEGABYTE');
        JText::script('COM_DROPFILES_FIELD_FILE_GIGABYTE');
        JText::script('COM_DROPFILES_FIELD_FILE_TERRABYTE');
        JText::script('COM_DROPFILES_FIELD_FILE_PETABYTE');
        JText::script('COM_DROPFILES_DOWNLOAD_SELECTED');
        JText::script('COM_DROPFILES_DOWNLOAD_ALL');
        JText::script('COM_DROPFILES_LIGHT_BOX_LOADING_STATUS');
    }

    /**
     * Generate download url
     *
     * @param integer $id           File id
     * @param integer $id_category  Category id
     * @param string  $categoryname Category name
     * @param boolean $token        Token string
     * @param string  $filename     File name
     * @param boolean $forDownload  For download
     *
     * @return string
     * @since  version
     */
    public static function genUrl(
        $id,
        $id_category,
        $categoryname = '',
        $token = false,
        $filename = null,
        $forDownload = true
    ) {
        $config = JFactory::getConfig();
        $params = JComponentHelper::getParams('com_dropfiles');
        $dropfilesUri = $params->get('uri', 'files');
        $url = JURI::root();
        if ($config->get('sef') && $dropfilesUri) {
            if (!$config->get('sef_rewrite')) {
                $url .= 'index.php/';
            }

            $url .= $dropfilesUri;

            $url .= '/' . $id_category;
            if ($categoryname) {
                $url .= '/' . preg_replace(array('/\'/', '#/#'), '', self::makeSafeFilename($categoryname, false));
            }

            $url .= '/' . $id;
            if ($filename !== null) {
                $url .= '/' . preg_replace('/\'/', '', self::makeSafeFilename($filename));
            }

            if ($token) {
                $url .= '?token=' . $token;
                if (!$forDownload) {
                    $url .= '&preview=1';
                }
            } elseif (!$forDownload) {
                $url .= '?preview=1';
            }
        } else {
            $url = JURI::root() . 'index.php?option=com_dropfiles&task=frontfile.download&&id=';
            $url .= $id . '&catid=' . $id_category;
            if ($token) {
                $url .= '&token=' . $token;
            }
            if (!$forDownload) {
                $url .= '&preview=1';
            }
            $url = JRoute::_($url);
        }
        return $url;
    }

    /**
     * Generate Viewer Url
     *
     * @param integer $id           File id
     * @param integer $id_category  Category id
     * @param string  $categoryname Category name
     * @param boolean $token        Token string
     * @param string  $filename     File name
     *
     * @return string
     * @since  version
     */
    public static function genViewerUrl($id, $id_category, $categoryname = '', $token = false, $filename = null)
    {
        $url = self::genUrl($id, $id_category, $categoryname, $token, $filename, false);
        return 'https://docs.google.com/viewer?url=' . urlencode($url) . '&embedded=true';
    }

    /**
     * Generate Media Viewer Url
     *
     * @param string  $id          File id
     * @param integer $id_category Category id
     * @param string  $ext         File extension
     *
     * @return string
     * @since  version
     */
    public static function genMediaViewerUrl($id, $id_category, $ext = '')
    {
        $imagesType = array('jpg', 'png', 'gif', 'jpeg', 'jpe', 'bmp', 'ico', 'tiff', 'tif', 'svg', 'svgz');
        $videoType  = array(
            'mp4',
            'mpeg',
            'mpe',
            'mpg',
            'mov',
            'qt',
            'rv',
            'avi',
            'movie',
            'flv',
            'webm',
            'ogv'
        );//,'3gp'
        $audioType  = array(
            'mid',
            'midi',
            'mp2',
            'mp3',
            'mpga',
            'ram',
            'rm',
            'rpm',
            'ra',
            'wav'
        );  // ,'aif','aifc','aiff'
        if (in_array($ext, $imagesType)) {
            $type = 'image';
        } elseif (in_array($ext, $videoType)) {
            $type = 'video';
        } elseif (in_array($ext, $audioType)) {
            $type = 'audio';
        } else {
            $type = '';
        }
        $url_frontviewer = JUri::root() . 'index.php?option=com_dropfiles&tmpl=component&view=frontviewer&id=';

        return $url_frontviewer . $id . '&catid=' . $id_category . '&type=' . $type . '&ext=' . $ext;
    }

    /**
     * Check media file
     *
     * @param string $ext File extension
     *
     * @return boolean
     * @since  version
     */
    public static function isMediaFile($ext)
    {
        $media_arr = array('mid', 'midi', 'mp2', 'mp3', 'mpga', 'ram', 'rm', 'rpm', 'ra', 'wav', //,'aif','aifc','aiff'
            'mp4', 'mpeg', 'mpe', 'mpg', 'mov', 'qt', 'rv', 'avi', 'movie', 'flv', 'webm', 'ogv', //'3gp',
            'jpg', 'png', 'gif', 'jpeg', 'jpe', 'bmp', 'ico', 'tiff', 'tif', 'svg', 'svgz');
        if (in_array($ext, $media_arr)) {
            return true;
        }
        return false;
    }

    /**
     * Add more file info
     *
     * @param array  $items    Files
     * @param object $category Category
     *
     * @return array
     * @since  version
     */
    public static function addInfosToFile($items, $category)
    {
        JLoader::register('DropfilesModelTokens', JPATH_ROOT . '/components/com_dropfiles/models/tokens.php');
        $params = JComponentHelper::getParams('com_dropfiles');
        $model = DropfilesModelTokens::getInstance('dropfilesModelTokens');
        $model->removeTokens();
        $session = JFactory::getSession();
        $sessionToken = $session->get('dropfilesToken', null);
        $viewfileanddowload = DropfilesBase::getAuthViewFileAndDownload();
        if ($sessionToken === null) {
            $token = $model->createToken();
            $session->set('dropfilesToken', $token);
        } else {
            $tokenId = $model->tokenExists($sessionToken);
            if ($tokenId) {
                $model->updateToken($tokenId);
                $token = $sessionToken;
            } else {
                $token = $model->createToken();
                $session->set('dropfilesToken', $token);
            }
        }
        if (!empty($items)) {
            $user = JFactory::getUser();
            $userId = (string) $user->id;
            if (is_array($items)) {
                foreach ($items as $key => &$item) {
                    if (!self::isUserCanViewFile($item)) {
                        unset($items[$key]);
                        continue;
                    }

                    if (isset($item->file) && strpos($item->file, 'http') !== false) {
                       // $item->link = $item->file;
                        $item->remoteurl = true;
                        $item->link = self::genUrl(
                            $item->id,
                            $category->id,
                            $category->title,
                            '',
                            $item->title . '.' . $item->ext
                        );
                    } else {
                        $item->remoteurl = false;
                        $item->link = self::genUrl(
                            $item->id,
                            $category->id,
                            $category->title,
                            '',
                            $item->title . '.' . $item->ext
                        );
                        $allowedgoogleext = 'pdf,ppt,pptx,doc,docx,xls,xlsx,dxf,ps,eps,xps,psd,tif,tiff,bmp,svg,pages,';
                        $allowedgoogleext .= 'ai,dxf,ttf,txt,mp3,mp4,png,gif,ico,jpeg,jpg';
                        if ($params->get('usegoogleviewer', 1) > 0 &&
                            in_array(
                                $item->ext,
                                explode(',', $params->get('allowedgoogleext', $allowedgoogleext))
                            )) {
                            $item->viewerlink = self::isMediaFile($item->ext) ?
                                self::genMediaViewerUrl($item->id, $category->id, $item->ext)
                                : self::genViewerUrl(
                                    $item->id,
                                    $category->id,
                                    $category->title,
                                    $token,
                                    $item->title . '.' . $item->ext
                                );
                        }
                    }
                    if (!$viewfileanddowload) {
                        $item->link = '#';
                    }

                    $item->link_download_popup = $item->link;

                    $item->created_time = JHtml::_('date', $item->created_time, $params->get('date_format', 'Y-m-d'));
                    $item->modified_time = JHtml::_('date', $item->modified_time, $params->get('date_format', 'Y-m-d'));
                    if (!isset($item->catid)) {
                        $item->catid = $category->id;
                    }
                    $item->versionNumber = $item->version;
                    if ($item->custom_icon) {
                        $pos = strpos($item->custom_icon, '#');
                        if ($pos !== false) {
                            $item->custom_icon =  substr($item->custom_icon, 0, $pos);
                        }
                        $image = new JImage(JPath::clean(JPATH_SITE . '/' . $item->custom_icon));
                        $result = $image->createThumbs(array('50x70'));
                        if (JPATH_SITE === '/') {
                            $item->custom_icon_thumb = JUri::root() . $result[0]->getPath();
                        } else {
                            $pat_replace = str_replace(JPATH_SITE, JUri::root(), $result[0]->getPath());
                            $item->custom_icon_thumb = str_replace('/\\', '/', $pat_replace);
                        }
                    }

                    if ((int) $params->get('open_pdf_in', 0) === 1 && $item->ext === 'pdf') {
                        $item->openpdflink = self::genUrl(
                            $item->id,
                            $category->id,
                            $category->title,
                            '',
                            $item->title . '.' . $item->ext,
                            false
                        );
                    }
                }
            } else {
                if (!self::isUserCanViewFile($items)) {
                    return array();
                }
                if (isset($items->file) && strpos($items->file, 'http') !== false) {
                    $items->link = $items->file;
                    $items->remoteurl = true;
                } else {
                    $items->remoteurl = false;
                    $items->link = self::genUrl(
                        $items->id,
                        $category->id,
                        $category->title,
                        '',
                        $items->title . '.' . $items->ext
                    );
                    $allowedgoogleext = 'pdf,ppt,pptx,doc,docx,xls,xlsx,dxf,ps,eps,xps,psd,tif,tiff,bmp,svg,pages,';
                    $allowedgoogleext .= 'ai,dxf,ttf,txt,mp3,mp4,png,gif,ico,jpeg,jpg';
                    if ($params->get('usegoogleviewer', 1) > 0 &&
                        in_array($items->ext, explode(
                            ',',
                            $params->get('allowedgoogleext', $allowedgoogleext)
                        ))) {
                        $items->viewerlink = self::isMediaFile($items->ext) ?
                            self::genMediaViewerUrl($items->id, $category->id, $items->ext)
                            : self::genViewerUrl(
                                $items->id,
                                $category->id,
                                $category->title,
                                $token,
                                $items->title . '.' . $items->ext
                            );
                    }
                }
                if (!$viewfileanddowload) {
                    $items->link = '#';
                }

                $items->link_download_popup = $items->link;

                $items->created_time = JHtml::_('date', $items->created_time, $params->get('date_format', 'Y-m-d'));
                $items->modified_time = JHtml::_('date', $items->modified_time, $params->get('date_format', 'Y-m-d'));
                if (!isset($items->catid)) {
                    $items->catid = $category->id;
                }
                $items->versionNumber = $items->version;

                if ($items->custom_icon) {
                    $pos = strpos($items->custom_icon, '#');
                    if ($pos !== false) {
                        $items->custom_icon =  substr($items->custom_icon, 0, $pos);
                    }
                    $image = new JImage(JPath::clean(JPATH_SITE . '/' . $items->custom_icon));
                    $result = $image->createThumbs(array('50x70'));
                    if (JPATH_SITE === '/') {
                        $items->custom_icon_thumb = JUri::root() . $result[0]->getPath();
                    } else {
                        $path_replace = str_replace(JPATH_SITE, JUri::root(), $result[0]->getPath());
                        $items->custom_icon_thumb = str_replace('/\\', '/', $path_replace);
                    }
                }
                if ((int) $params->get('open_pdf_in', 0) === 1 && $items->ext === 'pdf') {
                    $items->openpdflink = self::genUrl(
                        $items->id,
                        $category->id,
                        $category->title,
                        '',
                        $items->title . '.' . $items->ext,
                        false
                    );
                }
            }
        }

        return $items;
    }

    /**
     * Get mime type of a file extension
     *
     * @param string $ext File extension
     *
     * @return mixed|string
     * @since  version
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

        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        } else {
            return 'application/octet-stream';
        }
    }

    /**
     * Sanitize a file name
     *
     * @param string  $filename File name
     * @param boolean $withext  With extension
     *
     * @return boolean|mixed|string false if failed string otherwise
     * @since  version
     */
    public static function makeSafeFilename($filename, $withext = true)
    {

        $replace = array(
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'Ae', 'Å' => 'A', 'Æ' => 'A', 'Ă' => 'A',
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'ae', 'å' => 'a', 'ă' => 'a', 'æ' => 'ae',
            'þ' => 'b', 'Þ' => 'B',
            'Ç' => 'C', 'ç' => 'c',
            'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
            'Ğ' => 'G', 'ğ' => 'g',
            'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'İ' => 'I', 'ı' => 'i', 'ì' => 'i', 'í' => 'i',
            'î' => 'i', 'ï' => 'i',
            'Ñ' => 'N',
            'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'Oe', 'Ø' => 'O', 'ö' => 'oe', 'ø' => 'o',
            'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o',
            'Š' => 'S', 'š' => 's', 'Ş' => 'S', 'ș' => 's', 'Ș' => 'S', 'ş' => 's', 'ß' => 'ss',
            'ț' => 't', 'Ț' => 'T',
            'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'Ue',
            'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'ue',
            'Ý' => 'Y',
            'ý' => 'y', 'ÿ' => 'y',
            'Ž' => 'Z', 'ž' => 'z'
        );
//        $chars = array_keys($replace);
        $name = strtr($filename, $replace);

        if ($withext) {
            //get last extension
            $exploded = explode('.', $name);
            $ext = $exploded[count($exploded) - 1];

            $name = substr($name, 0, strlen($name) - strlen($ext) - 1);
        } else {
            $ext = '';
        }
        $name = str_replace(array(' ', '&', '\'', ':', '/', '\\', '?'), '-', $name);
        // Keep latin character only
        $name = preg_replace('/[^A-Za-z0-9\-]/', '', $name);
        $name = preg_replace('/&([^#])(?![a-z]{1,8};)/i', '&#038;$1', $name);
        if (empty($name)) {
            if ($withext) {
                //get last extension
                $exploded = explode('.', $filename);
                $ext = $exploded[count($exploded) - 1];

                $filename = substr($filename, 0, strlen($filename) - strlen($ext) - 1);
            }
            $name = rawurlencode($filename);
        }

        if ($ext === '') {
            return $name;
        }
        return $name . '.' . $ext;
    }

    /**
     * Check current user can view file
     *
     * @param object $file File object
     *
     * @return boolean
     * @since  5.2.0
     */
    public static function isUserCanViewFile($file)
    {
        $dropfiles_params = JComponentHelper::getParams('com_dropfiles');
        if ($dropfiles_params->get('restrictfile', 0)) {
            $usersCanView = (isset($file->canview) && $file->canview !== '0' && $file->canview !== '') ? $file->canview : '';
            if ($usersCanView !== '') {
                $user         = JFactory::getUser();
                $user_id      = (string) $user->id;
                $usersCanView = explode(',', $usersCanView);
                if ($user_id) {
                    if (count($usersCanView) > 0 && !in_array($user_id, $usersCanView)) {
                        return false;
                    }
                } else {
                    if (is_array($usersCanView) && count($usersCanView) > 0) {
                        return false;
                    }
                }
            }
        }

        return true;
    }
}
