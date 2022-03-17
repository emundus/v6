<?php
/**
 * @package	eMundus
 * @version	0.0.1
 * @author	eMundus.fr
 * @copyright (C) 2022 eMundus SOFTWARE. All rights reserved.
 * @license	GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('_JEXEC') or die('Restricted access');

class PlgEmundusSyncFile extends JPlugin {

    function __construct(&$subject, $config) {
        parent::__construct($subject, $config);
        jimport('joomla.log.log');
        JLog::addLogger(array('text_file' => 'com_emundus.sync_file.php'), JLog::ALL, array('com_emundus_sync_file'));
    }

    function onAfterUploadFile($args): void {
        if (!isset($args['fnums']) || !isset($args['files'])) {
            JLog::add('[SYNC_FILE_PLUGIN] Missing fnums or files in args', JLog::ERROR, 'com_emundus');
            return;
        }


    }

    function onDeleteFile($args): void {
        if (!isset($args['fnum']) || !isset($args['file'])) {
            JLog::add('[SYNC_FILE_PLUGIN] Missing fnums or files in args', JLog::ERROR, 'com_emundus');
            return;
        }
    }
}
