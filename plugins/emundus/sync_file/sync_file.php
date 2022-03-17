<?php
/**
 * @package	eMundus
 * @version	0.0.1
 * @author	eMundus.fr
 * @copyright (C) 2022 eMundus SOFTWARE. All rights reserved.
 * @license	GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('_JEXEC') or die('Restricted access');

require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'api'.DS.'FileSynchronizer.php');

class plgEmundusSync_file extends JPlugin {

    function __construct(&$subject, $config) {
        parent::__construct($subject, $config);
        jimport('joomla.log.log');
        JLog::addLogger(array('text_file' => 'com_emundus.sync_file.php'), JLog::ALL, array('com_emundus_sync_file'));
    }

    function onAfterUploadFile($args) {;
        if(!isset($args['upload_id'])) {
            JLog::add('Missing parameters', JLog::ERROR, 'com_emundus_sync_file');
            return false;
        }

        $fileSynchronizer = new FileSynchronizer('ged');
        $fileSynchronizer->addFile($args['upload_id']);
    }

    function onDeleteFile($args) {
        // app enqueue the file to be deleted
        $app = JFactory::getApplication();
        $app->enqueueMessage('[SYNC_FILE_PLUGIN] File to be deleted: '.$args['fnum']);

        if (!isset($args['fnum'])) {
            JLog::add('[SYNC_FILE_PLUGIN] Missing fnum in args', JLog::ERROR, 'com_emundus');
            return false;
        }
    }
}
