<?php
/**
 * @package     Joomla
 * @subpackage  eMundus
 * @copyright   Copyright (C) 2015 eMundus. All rights reserved.
 * @license     GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.helper');
jimport('joomla.user.helper');
jimport( 'joomla.application.application' );
jimport('joomla.plugin.helper');

include_once(JPATH_SITE.'/components/com_emundus/models/users.php');
include_once(JPATH_SITE.'/components/com_emundus/models/formbuilder.php');
include_once(JPATH_SITE.'/components/com_emundus/models/settings.php');
include_once(JPATH_SITE.'/components/com_emundus/classes/api/FileSynchronizer.php');
include_once(JPATH_SITE.'/components/com_emundus/models/campaign.php');
include_once(JPATH_SITE.'/components/com_emundus/models/programme.php');

/**
 * eMundus Component Query Helper
 *
 * @static
 * @package     Joomla
 * @subpackage  eMundus
 * @since 1.5
 */
class EmundusUnittestHelperSamples
{
    public function createSampleUser($profile = 9, $username = 'user.test@emundus.fr')
    {
        $user_id = 0;
        $m_users = new EmundusModelUsers;

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->insert('#__users')
            ->columns('name, username, email, password')
            ->values($db->quote('Test USER') . ', ' . $db->quote($username) .  ', ' . $db->quote($username) . ',' .  $db->quote(md5('test1234')));

        try {
            $db->setQuery($query);
            $db->execute();
            $user_id = $db->insertid();
        } catch (Exception $e) {
            JLog::add("Failed to insert jos_users" . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
        }

        if (!empty($user_id)) {
            $other_param['firstname'] 		= 'Test';
            $other_param['lastname'] 		= 'USER';
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

    public function createSampleFile($cid,$uid){
        $m_formbuilder = new EmundusModelFormbuilder;

        return $m_formbuilder->createTestingFile($cid,$uid);
    }

    public function createSampleTag(){
        $m_settings = new EmundusModelSettings;

        return $m_settings->createTag()->id;
    }

    public function createSampleStatus(){
        $m_settings = new EmundusModelSettings;

        return $m_settings->createStatus()->step;
    }

    public function createSampleForm($prid = 9, $label = ['fr' => 'Formulaire Tests unitaires', 'en' => 'form for unit tests'], $intro = ['fr' => 'Ce formulaire est un formulaire de test eMundus, utilisé uniquement pour tester le bon fonctionnement de la plateforme.', 'en' => '']) {
        $m_formbuilder = new EmundusModelFormbuilder;
        return $m_formbuilder->createFabrikForm($prid, $label, $intro);
    }

    public function createSampleGroup() {
        $data = [];
        $m_formbuilder = new EmundusModelFormbuilder;

        $form_id = $this->createSampleForm();

        if (!empty($form_id)) {
            $group = $m_formbuilder->createGroup(['fr' => 'Groupe Tests unitaires', 'en' => 'Group Unit tests'] , $form_id);

            if (!empty($group['group_id'])) {
                $group_id = $group['group_id'];

                $db = JFactory::getDbo();
                $query = $db->getQuery(true);

                $query->select('params')
                    ->from('#__fabrik_groups')
                    ->where('id = ' . $group_id);

                $db->setQuery($query);

                $params = $db->loadResult();
                $params = json_decode($params, true);

                $params['is_sample'] = true;

                $query->clear()
                    ->update('#__fabrik_groups')
                    ->set('params = ' . $db->quote(json_encode($params)))
                    ->where('id = ' . $group_id);

                $db->setQuery($query);
                $db->execute();

                $data = array(
                    'form_id' => $form_id,
                    'group_id' => $group_id
                );
            }
        }

        return $data;
    }

    public function deleteSampleGroup($group_id) {
        $deleted = false;
        if (!empty($group_id)) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('params')
                ->from('#__fabrik_groups')
                ->where('id = ' . $group_id);

            $db->setQuery($query);

            $params = $db->loadResult();
            $params = json_decode($params, true);

            if ($params['is_sample']) {
                $query->clear()
                    ->delete('#__fabrik_groups')
                    ->where('id = ' . $group_id);

                $db->setQuery($query);
                $deleted = $db->execute();
            }
        }

        return $deleted;
    }

    public function deleteSampleForm($form_id) {
        $deleted = false;
        if (!empty($form_id)) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->delete('#__fabrik_groups')
                ->where('id = ' . $form_id);

            $db->setQuery($query);
            $deleted = $db->execute();
        }

        return $deleted;
    }

	public function createSampleProgram()
	{
		$m_programme = new EmundusModelProgramme;
		$program = $m_programme->addProgram(['label' => 'Programme Test Unitaire']);
		return $program;
	}

	public function createSampleCampaign($program)
	{
		$campaign_id = 0;

		if (!empty($program)) {
			$m_campaign = new EmundusModelCampaign;

			$start_date = new DateTime();
			$start_date->modify('-1 day');
			$end_date = new DateTime();
			$end_date->modify('+1 year');
			$campaign_id = $m_campaign->createCampaign([
				'label' =>  json_encode(['fr' => 'Campagne test unitaire', 'en' => 'Campagne test unitaire']),
				'description' => 'Lorem ipsum',
				'short_description' => 'Lorem ipsum',
				'start_date' => $start_date->format('Y-m-d H:i:s'),
				'end_date' => $end_date->format('Y-m-d H:i:s'),
				'profile_id' => 9,
				'training' => $program['programme_code'],
				'year' => '2022-2023',
				'published' => 1,
				'is_limited' => 0
			]);
		}

		return $campaign_id;
	}

	public function createSampleUpload($fnum, $campaign_id, $user_id = 95, $attachment_id = 1) {
		$inserted = false;

		error_log('createSampleUpload(' . $fnum . ', ' . $campaign_id . ', ' . $user_id . ', ' . $attachment_id . ')');

		if (!empty($fnum)) {
			$filename = $user_id . '-' . $campaign_id . '-unittest' . rand(0, 100) . '.pdf';
			$localFilename = 'Unit Test file.pdf';

			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->insert('#__emundus_uploads')
				->columns(['fnum', 'user_id', 'campaign_id', 'attachment_id', 'filename', 'local_filename', 'timedate', 'can_be_deleted', 'can_be_viewed'])
				->values($fnum . ',' . $user_id . ',' . $campaign_id . ',' . $attachment_id . ',' . $db->quote($filename) . ',' . $db->quote($localFilename) . ',' . $db->quote(date('Y-m-d H:i:s')) . ',1,1');


			try {
				$db->setQuery($query);
				$inserted = $db->execute();
			} catch (Exception $e) {
				error_log($e->getMessage());
			}
		}

		return $inserted;
	}
}
