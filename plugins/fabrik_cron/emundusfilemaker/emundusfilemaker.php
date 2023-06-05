<?php

//namespace emundusfilemaker;

require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'classes' . DS . 'api' . DS . 'FileMaker.php');

require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'users.php');

require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'files.php');
require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'users.php');


require_once COM_FABRIK_FRONTEND . '/models/plugin-cron.php';

defined('_JEXEC') or die('Restricted access');

use \classes\api\FileMaker;


class PlgFabrik_Cronemundusfilemaker extends PlgFabrik_Cron
{
    public static $offset = 1;


    /**
     * Check if the user can use the plugin
     *
     * @param string $location To trigger plugin on
     * @param string $event To trigger plugin on
     *
     * @return  bool can use or not
     */
    public function canUse($location = null, $event = null)
    {
        return true;
    }

    public function getRecords($limit, $offset, $adminStep)
    {
        $file_maker_api = new FileMaker();
        try {
            $records = $file_maker_api->findRecord($limit, $offset, $adminStep);
            return $records;
        } catch (\Exception $e) {
            JLog::add('[FABRIK CRON FILEMAKER  GET RECORDS] ' . $e->getMessage(), JLog::ERROR, 'com_emundus.filemaker_fabrik_cron');
            return $e->getMessage();
        }


    }

    public function createFiles($filesData)
    {

        foreach ($filesData as $file) {


            $fieldData = $file->fieldData;


            if (!empty($fieldData->InterlocuteurIF) && !empty($fieldData->InterlocuteurIF_Email)) {

                $user_id = $this->createUserIfNotExist($fieldData->InterlocuteurIF_Email, $fieldData->InterlocuteurIF);

                $this->createSingleFile($file, $user_id);

            }

        }

    }


    public function createUserIfNotExist($email, $name)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);


        $query->select('*')
            ->from($db->quoteName('jos_users'))
            ->where($db->quoteName('email') . '=' . $db->quote($email));
        $db->setQuery($query);

        $user = $db->loadObject();
        $query->clear();

        if (!empty($user)) {
            return $user->id;
        } else {

            $profile = 1000;

            $m_users = new EmundusModelUsers;
            $h_users = new EmundusHelperUsers;
            $firstname_and_lastname = explode(" ", $name);
            $user_id = 0;

            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $password = $h_users->generateStrongPassword();

            $query->insert('#__users')
                ->columns('name, username, email, password')
                ->values($db->quote($name) . ', ' . $db->quote($email) .  ', ' . $db->quote($email) . ',' .  $db->quote($password));

            try {
                $db->setQuery($query);
                $db->execute();
                $user_id = $db->insertid();
            } catch (Exception $e) {
                JLog::add("Failed to insert jos_users" . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
            }

            if (!empty($user_id)) {
                $other_param['firstname'] 		= $firstname_and_lastname[0];
                $other_param['lastname'] 		= $firstname_and_lastname[1];
                $other_param['profile'] 		= $profile;
                $other_param['em_oprofiles'] 	= '';
                $other_param['univ_id'] 		= 0;
                $other_param['em_groups'] 		= '';
                $other_param['em_campaigns'] 	= [];
                $other_param['news'] 			= '';
                $m_users->addEmundusUser($user_id, $other_param);
            }

            return $user_id;

        }


    }

    public function createSingleFile($singleFieldData, $user_id)
    {
        $fieldData = $singleFieldData->fieldData;
        $campaign_id = 2;
        $config = JFactory::getConfig();
        $timezone = new DateTimeZone( $config->get('offset'));
        $now = JFactory::getDate()->setTimezone($timezone);
        $h_files = new EmundusHelperFiles();




        if(empty($this->checkIfFileNotAlreadyExist($fieldData->uuid))){



            $fnum = $h_files->createFnum($campaign_id, $user_id);

            while(!empty($this->checkIfFnumNotAlreadyExist($fnum))){
                $fnum = $h_files->createFnum($campaign_id, $user_id);
            }


            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->clear()
                ->insert($db->quoteName('#__emundus_campaign_candidature'))
                ->columns($db->quoteName(['date_time', 'applicant_id', 'user_id', 'campaign_id', 'fnum', 'uuid', 'uuidConnect']))
                ->values($db->quote($now) . ', ' . $user_id . ', ' . $user_id . ', ' . $campaign_id . ', ' . $db->quote($fnum). ', ' . $db->quote($fieldData->uuid). ', ' . $db->quote($fieldData->uuidConnect));
            $db->setQuery($query);


            try {
                $inserted = $db->execute();
            } catch (Exception $e) {
                $fnum = '';
                $inserted = false;

                JLog::add("[FILEMAKER CRON] Failed to create file $fnum - $user_id" . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
            }

            if (!$inserted) {
                $fnum = '';
            }
        }

    }

    public function checkIfFileNotAlreadyExist($uuid)
    {

        $file = '';
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);


        $query->select('*')
            ->from($db->quoteName('#__emundus_campaign_candidature'))
            ->where($db->quoteName('uuid') . '=' . $db->quote($uuid));
        $db->setQuery($query);

        try {
            $file = $db->loadObject();

        } catch (Exception $e) {

            JLog::add("[FILEMAKER CRON] Failed to check if file already exist for ". $uuid. " ". $e->getMessage(), JLog::ERROR, 'com_emundus.error');
        }

        return $file;

    }


    public function checkIfFnumNotAlreadyExist($fnum)
    {

        $file = '';
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);


        $query->select('*')
            ->from($db->quoteName('#__emundus_campaign_candidature'))
            ->where($db->quoteName('fnum') . '=' . $db->quote($fnum));
        $db->setQuery($query);

        try {
            $file = $db->loadObject();

        } catch (Exception $e) {

            JLog::add("[FILEMAKER CRON] Failed to check if file already exist for fnum". $fnum. " ". $e->getMessage(), JLog::ERROR, 'com_emundus.error');
        }

        return $file;

    }


    /**
     * Do the plugin action
     *
     * @param array  &$data data
     *
     * @return  mixed  number of records updated
     * @throws Exception
     */
    public function process(&$data, &$listModel)
    {
        $records_retrieved = [];


        $find_records_response = $this->getRecords(50, $this->offset, "PRE");


        if (!empty($find_records_response)) {
            $this->createFiles($find_records_response->data);
        }

    }


}
