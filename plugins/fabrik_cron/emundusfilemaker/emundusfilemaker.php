<?php

namespace emundusfilemaker;

require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'classes'.DS.'api'.DS.'FileMaker.php');
require_once COM_FABRIK_FRONTEND . '/models/plugin-cron.php';

defined('_JEXEC') or die('Restricted access');

use \classes\api\FileMaker;

class PlgFabrik_Cronemundusfilemaker extends PlgFabrik_Cron
{
    public static $offset = 1;

    /**
     * Check if the user can use the plugin
     *
     * @param   string  $location  To trigger plugin on
     * @param   string  $event     To trigger plugin on
     *
     * @return  bool can use or not
     */
    public function canUse($location = null, $event = null) {
        return true;
    }

    public function getRecords($limit,$offset,$adminStep){
        $file_maker_api = new FileMaker();
        try {
            $records = $file_maker_api->findRecord($limit,$offset,$adminStep);
            return $records;
        }
        catch (\Exception $e) {
                JLog::add('[FABRIK CRON FILEMAKER  GET RECORDS] ' .$e->getMessage(), JLog::ERROR, 'com_emundus.filemaker_fabrik_cron');
                return $e->getMessage();
            }


    }


    /**
     * Do the plugin action
     *
     * @param array  &$data data
     *
     * @return  mixed  number of records updated
     * @throws Exception
     */
    public function process(&$data, &$listModel) {
        $records_retrieved = [];


        $find_records_response = $this->getRecords(50,$this->offset,"PRE");



    }
}
