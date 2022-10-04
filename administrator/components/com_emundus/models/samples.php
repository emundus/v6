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

class EmundusModelSamples extends JModelList {

    public function createSampleUser($profile = 9,$username = 'user.test@emundus.fr')
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $m_users = new EmundusModelUsers();

        $user = clone(JFactory::getUser(0));
        $user->name = 'USER Test';
        $user->username = (string)rand(100000, 1000000);

        do {
            $query->clear()
                ->select('id')
                ->from($db->quoteName('#__users'))
                ->where($db->quoteName('username') . ' = ' . $user->username);
            $db->setQuery($query);
            $existing = $db->loadResult();
        } while(!is_null($existing));

        $user->email = $username;
        $user->password = md5('test1234');
        $user->registerDate = date('Y-m-d H:i:s');
        $user->lastvisitDate = date('Y-m-d H:i:s');
        $user->groups = array();
        $user->block = 0;

        $other_param['firstname'] 		= 'Test';
        $other_param['lastname'] 		= 'USER';
        $other_param['profile'] 		= $profile;
        $other_param['em_oprofiles'] 	= '';
        $other_param['univ_id'] 		= 0;
        $other_param['em_groups'] 		= '';
        $other_param['em_campaigns'] 	= '1';
        $other_param['news'] 			= '';

        $acl_aro_groups = $m_users->getDefaultGroup($profile);
        $user->groups = $acl_aro_groups;

        $usertype = $m_users->found_usertype($acl_aro_groups[0]);
        $user->usertype = $usertype;

        $user->save();

        $query->clear()
            ->update($db->quoteName('#__users'))
            ->set($db->quoteName('username') . ' = ' . $db->quote('user'.$user->id.'.test@emundus.fr'))
            ->where($db->quoteName('id') . ' = ' . $user->id);
        $db->setQuery($query);
        $db->execute();

        $m_users->addEmundusUser($user->id, $other_param);

        return $user;
    }

    public function createSampleFile($uids = null){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        if(!is_array($uids)){
            $uids = [$uids];
        }

        $query->select('id')
            ->from($db->quoteName('#__emundus_setup_campaigns'));
        $db->setQuery($query);
        $cids = $db->loadColumn();

        $query->clear()
            ->select('step')
            ->from($db->quoteName('#__emundus_setup_status'));
        $db->setQuery($query);
        $status = $db->loadColumn();

        if(empty($uid)){
            $query->clear()
                ->select('user_id')
                ->from($db->quoteName('#__emundus_users'))
                ->where($db->quoteName('profile') . ' = 9');
            $db->setQuery($query);
            $uids = $db->loadColumn();
        }

        foreach ($uids as $uid) {
            foreach ($cids as $cid) {
                $fnum = @EmundusHelperFiles::createFnum($cid, $uid);

                try {
                    $query->clear()
                        ->insert($db->quoteName('#__emundus_campaign_candidature'));
                    $query->set($db->quoteName('applicant_id') . ' = ' . $db->quote($uid))
                        ->set($db->quoteName('user_id') . ' = ' . $db->quote($uid))
                        ->set($db->quoteName('campaign_id') . ' = ' . $db->quote($cid))
                        ->set($db->quoteName('fnum') . ' = ' . $db->quote($fnum))
                        ->set($db->quoteName('status') . ' = ' . $db->quote(array_rand($status)));
                    $db->setQuery($query);
                    $db->execute();
                } catch (Exception $e) {
                    JLog::add('component/com_emundus/models/formbuilder | Error at creating a testing file in the campaign ' . $cid . ' of the user ' . $uid . ' : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');
                    return false;
                }
            }
        }
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
        $training = $db->loadResult();

        if(empty($training)){
            $inserting_datas = [
                'code' => 'prog',
                'label' => 'Programme de test',
                'ordering' => 1,
                'published' => 1,
                'apply_online' => 1,
            ];

            $query->clear()
                ->insert($db->quoteName('#__emundus_setup_programmes'))
                ->columns($db->quoteName(array_keys($inserting_datas)))
                ->values(implode(',',$db->quote(array_values($inserting_datas))));
            $db->setQuery($query);
            $db->execute();

            $inserting_datas = [
                'parent_id' => 1,
                'course' => 'prog',
            ];
            $query->clear()
                ->insert($db->quoteName('#__emundus_setup_groups_repeat_course'))
                ->columns($db->quoteName(array_keys($inserting_datas)))
                ->values(implode(',',$db->quote(array_values($inserting_datas))));
            $db->setQuery($query);
            $db->execute();

            $end_date = new DateTime();
            $end_date->modify('+1 year');
            $inserting_datas = [
                'label' => '2022-2023',
                'schoolyear' => '2022-2023',
                'code' => 'prog',
                'published' => 1,
                'date_start' => date('Y-m-d H:i:s'),
                'date_end' => $end_date->format('Y-m-d H:i:s'),
                'profile_id' => 9,
            ];
            $query->clear()
                ->insert($db->quoteName('#__emundus_setup_teaching_unity'))
                ->columns($db->quoteName(array_keys($inserting_datas)))
                ->values(implode(',',$db->quote(array_values($inserting_datas))));
            $db->setQuery($query);
            $db->execute();

            $training = 'prog';
        }

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
            'training' => $training,
            'year' => '2022-2023',
            'published' => 1,
        ];

        $query->clear()
            ->insert($db->quoteName('#__emundus_setup_campaigns'))
            ->columns($db->quoteName(array_keys($inserting_datas)))
            ->values(implode(',',$db->quote(array_values($inserting_datas))));
        $db->setQuery($query);
        $db->execute();

        return $db->insertid();
    }
}
