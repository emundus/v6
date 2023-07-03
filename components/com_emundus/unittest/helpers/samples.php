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

	public function __construct()
	{
		define('EVALUATOR_RIGHTS', array ([ 'id' => '1', 'c' => 0, 'r' => 1, 'u' => 0, 'd' => 0, ], 1 => array ( 'id' => '4', 'c' => 1, 'r' => 1, 'u' => 0, 'd' => 0, ), 2 => array ( 'id' => '5', 'c' => 1, 'r' => 1, 'u' => 1, 'd' => 0, ), 3 => array ( 'id' => '29', 'c' => 0, 'r' => 0, 'u' => 0, 'd' => 0, ), 4 => array ( 'id' => '32', 'c' => 0, 'r' => 0, 'u' => 0, 'd' => 0, ), 5 => array ( 'id' => '34', 'c' => 0, 'r' => 0, 'u' => 0, 'd' => 0, ), 6 => array ( 'id' => '28', 'c' => 0, 'r' => 0, 'u' => 0, 'd' => 0, ), 7 => array ( 'id' => '13', 'c' => 0, 'r' => 1, 'u' => 0, 'd' => 0, ), 8 => array ( 'id' => '14', 'c' => 1, 'r' => 1, 'u' => 1, 'd' => 0, ), 9 => array ( 'id' => '10', 'c' => 1, 'r' => 1, 'u' => 1, 'd' => 0, ), 10 => array ( 'id' => '11', 'c' => 0, 'r' => 0, 'u' => 0, 'd' => 0, ), 11 => array ( 'id' => '37', 'c' => 0, 'r' => 0, 'u' => 0, 'd' => 0, ), 12 => array ( 'id' => '36', 'c' => 0, 'r' => 0, 'u' => 0, 'd' => 0, ), 13 => array ( 'id' => '8', 'c' => 1, 'r' => 0, 'u' => 0, 'd' => 0, ), 14 => array ( 'id' => '6', 'c' => 1, 'r' => 0, 'u' => 0, 'd' => 0, ), 15 => array ( 'id' => '7', 'c' => 1, 'r' => 0, 'u' => 0, 'd' => 0, ), 16 => array ( 'id' => '27', 'c' => 0, 'r' => 0, 'u' => 0, 'd' => 0, ), 17 => array ( 'id' => '31', 'c' => 0, 'r' => 0, 'u' => 0, 'd' => 0, ), 18 => array ( 'id' => '35', 'c' => 0, 'r' => 0, 'u' => 0, 'd' => 0, ), 19 => array ( 'id' => '18', 'c' => 0, 'r' => 0, 'u' => 0, 'd' => 0, ), 20 => array ( 'id' => '9', 'c' => 0, 'r' => 0, 'u' => 0, 'd' => 0, ), 21 => array ( 'id' => '16', 'c' => 0, 'r' => 0, 'u' => 0, 'd' => 0, ), 22 => array ( 'id' => '15', 'c' => 0, 'r' => 0, 'u' => 0, 'd' => 0, ), ));
	}

    public function createSampleUser($profile = 9, $username = 'user.test@emundus.fr', $password = 'test1234')
    {
        $user_id = 0;
        $m_users = new EmundusModelUsers;

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->insert('#__users')
            ->columns('name, username, email, password')
            ->values($db->quote('Test USER') . ', ' . $db->quote($username) .  ', ' . $db->quote($username) . ',' .  $db->quote(md5($password)));

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

	public function createSampleProgram($label = 'Programme Test Unitaire')
	{
		$m_programme = new EmundusModelProgramme;
		$program = $m_programme->addProgram(['label' => $label, 'published' => 1]);
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
}
