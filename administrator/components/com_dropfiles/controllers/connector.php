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
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

/**
 * Class DropfilesControllerConnector
 */
class DropfilesControllerConnector extends JControllerLegacy
{

    /**
     * List all files and child directories in a directory
     *
     * @return string
     *
     * @since 1.0
     */
    public function listDir()
    {
        $user = JFactory::getUser();
        if (!$user->authorise('core.admin')) {
            return json_encode(array());
        }
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

        $path = JPATH_ROOT . DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR;
        $dir = JFactory::getApplication()->input->getString('dir'); //JFolder::makeSafe(JRequest::getString('dir'));

        //Prevent  directory traversal
        if (strpos($dir, '..') !== false) {
            jexit();
        }

        $return = array();
        $dirs   = array();
        $fi     = array();

        if (file_exists($path . $dir)) {
            $files = scandir($path . $dir);

            natcasesort($files);
            if (count($files) > 2) { // The 2 counts for . and ..
                // All dirs
                foreach ($files as $file) {
                    if (file_exists($path . $dir . DIRECTORY_SEPARATOR . $file) &&
                        $file !== '.' && $file !== '..' && is_dir($path . $dir . DIRECTORY_SEPARATOR . $file)) {
                        $dirs[] = array('type' => 'dir', 'dir' => $dir, 'file' => $file);
                    } elseif (file_exists($path . $dir . DIRECTORY_SEPARATOR . $file) &&
                        $file !== '.' && $file !== '..' &&
                        !is_dir($path . $dir . DIRECTORY_SEPARATOR . $file) &&
                        in_array(JFile::getExt($file), $allowed_ext)) {
                        $fi[] = array('type' => 'file',
                            'dir' => $dir,
                            'file' => $file,
                            'ext' => strtolower(JFile::getExt($file))
                        );
                    }
                }
                $return = array_merge($dirs, $fi);
            }
        }
        echo json_encode($return);
        jexit();
    }
}
