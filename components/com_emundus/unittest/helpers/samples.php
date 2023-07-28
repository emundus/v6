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
    public function createSampleUser($profile = 9, $username = 'user.test@emundus.fr', $j_groups = [2])
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
			if(!empty($j_groups)) {
				foreach ($j_groups as $j_group) {
					$query->clear()
						->insert($db->quoteName('#__user_usergroup_map'))
						->columns('user_id, group_id')
						->values($user_id . ',' . $j_group);
					try {
						$db->setQuery($query);
						$db->execute();
					} catch (Exception $e) {
						JLog::add("Failed to insert jos_user_usergroup_map" . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
					}
				}
			}

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

	public function createSampleAttachment() {
		$sample_id = 0;

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$rand_id = rand();

		$query->insert('#__emundus_setup_attachments')
			->columns(['lbl', 'value', 'description', 'allowed_types'])
			->values($db->quote('_test_unitaire_' . $rand_id) . ',' . $db->quote('Test unitaire ' . $rand_id) . ',' . $db->quote('Document pour les tests unitaire') . ',' .$db->quote('pdf'));

		$db->setQuery($query);
		$inserted = $db->execute();
		if ($inserted) {
			$sample_id = $db->insertid();
		}

		return $sample_id;
	}

	public function createSampleLetter($attachment_id, $template_type = 2, $programs = [], $status = [], $campaigns = []) {
		$letter_id = 0;

		if (!empty($attachment_id)) {
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->insert('#__emundus_setup_letters')
				->columns(['attachment_id', 'template_type', 'header', 'body', 'footer', 'title'])
				->values($attachment_id . ',' . $template_type . ',' . $db->quote('<p>letter_header</p>') . ',' . $db->quote('<p>letter_body</p>') . ',' . $db->quote('<p>letter_footer</p>') . ',' . $db->quote('Lettre Test unitaire'));
			$db->setQuery($query);

			$inserted = $db->execute();

			if ($inserted) {
				$letter_id = $db->insertid();

				if (!empty($programs)) {
					$values = [];
					foreach ($programs as $program) {
						$values[] = $letter_id . ',' . $db->quote($program);
					}

					$query->clear()
						->insert('#__emundus_setup_letters_repeat_training')
						->columns(['parent_id', 'training'])
						->values($values);

					$db->setQuery($query);
					$db->execute();
				}

				if (!empty($status)) {
					$values = [];
					foreach ($status as $statu) {
						$values[] = $letter_id . ',' . $db->quote($statu);
					}

					$query->clear()
						->insert('#__emundus_setup_letters_repeat_status')
						->columns(['parent_id', 'status'])
						->values($values);

					$db->setQuery($query);
					$db->execute();
				}

				if (!empty($campaigns)) {
					$values = [];
					foreach ($campaigns as $campaign) {
						$values[] = $letter_id . ',' . $db->quote($campaign);
					}

					$query->clear()
						->insert('#__emundus_setup_letters_repeat_campaign')
						->columns(['parent_id', 'campaign'])
						->values($values);

					$db->setQuery($query);
					$db->execute();
				}
			}
		}

		return $letter_id;
	}

	public function createSampleUpload($fnum, $campaign_id, $user_id = 95, $attachment_id = 1) {
		$inserted = false;

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
				$inserted = false;
				error_log('attachment insertion failed');
			}
		}

		return $inserted;
	}

	public function  duplicateSampleProfile($profile_id)
	{

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Get profile
		$query->clear()
			->select('*')
			->from($db->quoteName('#__emundus_setup_profiles'))
			->where($db->quoteName('id') . ' = ' . $db->quote($profile_id));
		$db->setQuery($query);
		$oldprofile = $db->loadObject();

		if (!empty($oldprofile)) {
			// Create a new profile
			$query->clear()
				->insert('#__emundus_setup_profiles')
				->set($db->quoteName('label') . ' = ' . $db->quote($oldprofile->label . ' - Copy'))
				->set($db->quoteName('published') . ' = 1')
				->set($db->quoteName('menutype') . ' = ' . $db->quote($oldprofile->menutype))
				->set($db->quoteName('acl_aro_groups') . ' = ' . $db->quote($oldprofile->acl_aro_groups))
				->set($db->quoteName('status') . ' = ' . $db->quote($oldprofile->status));
			$db->setQuery($query);
			$db->execute();
			$newprofile = $db->insertid();
		}

		return $newprofile;
	}

	public function addJGroup($j_group, $user_id) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		try
		{
			$query->insert($db->quoteName('#__user_usergroup_map'))
				->columns('user_id, group_id')
				->values($user_id . ',' . $j_group);
			$db->setQuery($query);
			$db->execute();
		}
		catch (Exception $e)
		{
			JLog::add("Failed to insert jos_user_usergroup_map" . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
		}
	}
}
