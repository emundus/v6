<?php
/**
 * Created by PhpStorm.
 * User: bhubinet
 * Date: 04/09/22
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');


include_once(JPATH_SITE.'/components/com_emundus/models/users.php');
include_once(JPATH_SITE.'/components/com_emundus/models/formbuilder.php');
include_once(JPATH_SITE.'/components/com_emundus/models/settings.php');
include_once(JPATH_SITE.'/components/com_emundus/helpers/files.php');

class EmundusAdminModelSamples extends JModelList {

    public function createSampleUser($profile = 9)
    {
        $user_id = 0;
        $m_users = new EmundusModelUsers();

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);


        do {
            $username = (string)rand(100000, 1000000);

            $query->clear()
                ->select('id')
                ->from($db->quoteName('#__users'))
                ->where($db->quoteName('username') . ' = ' . $username);
            $db->setQuery($query);
            $existing = $db->loadResult();
        } while(!is_null($existing));

        $query->clear()
	        ->insert('#__users')
            ->columns('name, username, email, password')
            ->values($db->quote('Test USER') . ', ' . $db->quote($username) . ',' . $db->quote($username . '@emundus.fr') . ',' .  $db->quote(md5('test1234')));

        try {
            $db->setQuery($query);
            $db->execute();
            $user_id = $db->insertid();
        } catch (Exception $e) {
            JLog::add("Failed to insert jos_users" . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
        }

        if (!empty($user_id)) {
            $other_param['firstname'] = 'Test';
            $other_param['lastname'] = 'USER';
            $other_param['profile'] = $profile;
            $other_param['em_oprofiles'] = '';
            $other_param['univ_id'] = 0;
            $other_param['em_groups'] = '';
            $other_param['em_campaigns'] = '1';
            $other_param['news'] = '';

            $m_users->addEmundusUser($user_id, $other_param);
        }

        return $user_id;
    }

    public function createSampleFile($uids = null){
        $nb_files_created = 0;
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        if(!is_array($uids) && !empty($uids)) {
            $uids = [$uids];
        }

        $query->select('id')
            ->from($db->quoteName('#__emundus_setup_campaigns'));
        $db->setQuery($query);
        $cids = $db->loadColumn();

        if (!empty($cids)) {
            $query->clear()
                ->select('step')
                ->from($db->quoteName('#__emundus_setup_status'));
            $db->setQuery($query);
            $status = $db->loadColumn();

            if (empty($uids)) {
                $query->clear()
                    ->select('user_id')
                    ->from($db->quoteName('#__emundus_users'))
                    ->where($db->quoteName('profile') . ' = 9')
                    ->setLimit('1');
                $db->setQuery($query);
                $uids = [$db->loadResult()];
            }

            foreach ($uids as $uid) {
                $cid_key = array_rand($cids);
	            $fnum = '';
	            do {
		            $fnum = @EmundusHelperFiles::createFnum($cids[$cid_key], $uid);

		            $query->clear()
			            ->select('id')
			            ->from($db->quoteName('#__emundus_campaign_candidature'))
			            ->where($db->quoteName('fnum') . ' LIKE ' . $db->quote($fnum));
		            $db->setQuery($query);
		            $file_already_exist = $db->loadResult();
	            } while (!empty($file_already_exist));


                if (!empty($fnum)) {
                    try {
                        $query->clear()
                            ->insert($db->quoteName('#__emundus_campaign_candidature'));
                        $query->set($db->quoteName('applicant_id') . ' = ' . $db->quote($uid))
                            ->set($db->quoteName('user_id') . ' = ' . $db->quote($uid))
                            ->set($db->quoteName('campaign_id') . ' = ' . $db->quote($cids[$cid_key]))
                            ->set($db->quoteName('fnum') . ' = ' . $db->quote($fnum))
                            ->set($db->quoteName('status') . ' = ' . $db->quote(array_rand($status)));
                        $db->setQuery($query);
                        $created = $db->execute();

                        if ($created) {
                            $nb_files_created++;
                        }
                    } catch (Exception $e) {
                        JLog::add('component/com_emundus/models/formbuilder | Error at creating a testing file in the campaign ' . $cids[$cid_key] . ' of the user ' . $uid . ' : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus.error');
                        JFactory::getApplication()->enqueueMessage('Error at creating a testing file in the campaign ' . $cids[$cid_key] . ' of the user ' . $uid . ' : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), 'warning');
                    }
                }
            }
        }

        return $nb_files_created;
    }

    public function createSampleTag(){
        $m_settings = new EmundusModelSettings;

        return $m_settings->createTag()->id;
    }

    public function createSampleStatus(){
        $m_settings = new EmundusModelSettings;

        return $m_settings->createStatus()->step;
    }

    public function createSampleCampaign($label,$profile=9){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('code')
            ->from($db->quoteName('#__emundus_setup_programmes'));
        $db->setQuery($query);
        $programmes = $db->loadColumn();

        $campaigns = [];
        foreach ($programmes as $programme) {
            $start_date = new DateTime();
            $start_date->modify('-1 day');

            $end_date = new DateTime();
            $end_date->modify('+1 year');

            $inserting_datas = [
                'user' => 62,
                'label' => $label,
                'description' => 'Lorem ipsum',
                'short_description' => 'Lorem ipsum',
                'start_date' => $start_date->format('Y-m-d H:i:s'),
                'end_date' => $end_date->format('Y-m-d H:i:s'),
                'profile_id' => $profile,
                'training' => $programme,
                'year' => '2022-2023',
                'published' => 1,
            ];

            $query->clear()
                ->insert($db->quoteName('#__emundus_setup_campaigns'))
                ->columns($db->quoteName(array_keys($inserting_datas)))
                ->values(implode(',', $db->quote(array_values($inserting_datas))));
            $db->setQuery($query);
            $db->execute();

            $campaigns[] = $db->insertid();
        }

        return $campaigns;
    }

    public function createSampleProgram($label,$code){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('code')
            ->from($db->quoteName('#__emundus_setup_programmes'))
            ->where($db->quoteName('code') . ' = ' . $db->quote($code));
        $db->setQuery($query);
        $existing = $db->loadResult();

        if(empty($existing)) {
            $inserting_datas = [
                'code' => $code,
                'label' => $label,
                'ordering' => 1,
                'published' => 1,
                'apply_online' => 1,
            ];

            $query->clear()
                ->insert($db->quoteName('#__emundus_setup_programmes'))
                ->columns($db->quoteName(array_keys($inserting_datas)))
                ->values(implode(',', $db->quote(array_values($inserting_datas))));
            $db->setQuery($query);
            $db->execute();

            $inserting_datas = [
                'parent_id' => 1,
                'course' => $code,
            ];
            $query->clear()
                ->insert($db->quoteName('#__emundus_setup_groups_repeat_course'))
                ->columns($db->quoteName(array_keys($inserting_datas)))
                ->values(implode(',', $db->quote(array_values($inserting_datas))));
            $db->setQuery($query);
            $db->execute();

            $end_date = new DateTime();
            $end_date->modify('+1 year');
            $inserting_datas = [
                'label' => '2022-2023',
                'schoolyear' => '2022-2023',
                'code' => $code,
                'published' => 1,
                'date_start' => date('Y-m-d H:i:s'),
                'date_end' => $end_date->format('Y-m-d H:i:s'),
                'profile_id' => 9,
            ];
            $query->clear()
                ->insert($db->quoteName('#__emundus_setup_teaching_unity'))
                ->columns($db->quoteName(array_keys($inserting_datas)))
                ->values(implode(',', $db->quote(array_values($inserting_datas))));
            $db->setQuery($query);
            $db->execute();
        }

        return $code;
    }
}
