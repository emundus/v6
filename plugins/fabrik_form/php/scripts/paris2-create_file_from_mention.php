<?php

/**
 * WARNING : All queries need teaching unity (year) !
 *
 * This plugin allow applicants to create many files by choosing mentions
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

$db = JFactory::getDbo();
$query = $db->getQuery(true);

require_once (JPATH_SITE.DS.'components/com_emundus/helpers'.DS.'files.php');
require_once (JPATH_SITE.DS.'components/com_emundus/models'.DS.'application.php');
require_once (JPATH_SITE.DS.'components/com_emundus/models'.DS.'profile.php');
require_once (JPATH_SITE.DS.'components/com_emundus/models'.DS.'files.php');
$h_files = new EmundusHelperFiles;
$m_application = new EmundusModelApplication;
$m_profile = new EmundusModelProfile;
$m_files = new EmundusModelFiles;

$mainframe = JFactory::getApplication();
$config = JFactory::getConfig();
$jinput = $mainframe->input;
$redirect = null;

// Get inputs
$fnum = $jinput->get('jos_emundus_scholarship_domain___fnum');
$prog = $jinput->get('jos_emundus_scholarship_domain___code_prog');
$user = $jinput->get('jos_emundus_scholarship_domain___user')[0];
$voeux = $jinput->getRaw('jos_emundus_scholarship_domain_888_repeat___voeu');
$formid = $jinput->get('formid');
$campaigns = array();
$fnums_to_delete = array();

$offset = $mainframe->get('offset', 'UTC');

try {
    $timezone = new DateTimeZone( $config->get('offset') );
    $now = JFactory::getDate()->setTimezone($timezone);
} catch (Exception $e) {
    echo $e->getMessage() . '<br />';
}

foreach ($voeux as $voeu){
    $campaigns[] = $voeu[0];
}

try {
// Get repeat db table
    /*$query->select('fl.db_table_name,fj.table_join')
        ->from($db->quoteName('#__fabrik_lists', 'fl'))
        ->leftJoin($db->quoteName('#__fabrik_joins', 'fj') . ' ON ' . $db->quoteName('fj.join_from_table') . ' = ' . $db->quoteName('fl.db_table_name'))
        ->where($db->quoteName('fl.form_id') . ' = ' . $db->quote($formid))
        ->andWhere($db->quoteName('table_join_key') . ' = ' . $db->quote('parent_id'));
    $db->setQuery($query);
    $dbTables = $db->loadObject();*/
//
//

    $fnumsDetails = $m_profile->getFnumDetails($fnum);

    // Check the limit of files by program and by user
    $query->clear()
        ->select('cc.*')
        ->from($db->quoteName('#__emundus_campaign_candidature', 'cc'))
        ->leftJoin($db->quoteName('#__emundus_setup_campaigns', 'sc') . ' ON ' . $db->quoteName('sc.id') . ' = ' . $db->quoteName('cc.campaign_id'))
        ->where($db->quoteName('sc.training') . ' = ' . $db->quote($prog))
        ->andWhere($db->quoteName('sc.year') . ' = ' . $db->quote($fnumsDetails['year']))
        ->andWhere($db->quoteName('cc.published') . ' = 1')
        ->andWhere($db->quoteName('cc.applicant_id') . ' = ' . $db->quote($fnumsDetails['applicant_id']));
    $db->setQuery($query);
    $applications = $db->loadObjectList();

    $query->clear()
        ->select('cc.*')
        ->from($db->quoteName('#__emundus_campaign_candidature', 'cc'))
        ->leftJoin($db->quoteName('#__emundus_setup_campaigns', 'sc') . ' ON ' . $db->quoteName('sc.id') . ' = ' . $db->quoteName('cc.campaign_id'))
        ->where($db->quoteName('sc.training') . ' = ' . $db->quote($prog))
        ->andWhere($db->quoteName('sc.year') . ' = ' . $db->quote($fnumsDetails['year']))
        ->andWhere($db->quoteName('cc.status') . ' = 0')
        ->andWhere($db->quoteName('cc.published') . ' = 1')
        ->andWhere($db->quoteName('cc.applicant_id') . ' = ' . $db->quote($fnumsDetails['applicant_id']));
    $db->setQuery($query);
    $current_applications = $db->loadObjectList();
    //

    $number_app_same_wishes = 0;

    // Check if existing applications are again in wishes
    foreach ($current_applications as $key => $application) {
      $check_voeu = in_array($application->campaign_id, $campaigns);
      if (!$check_voeu) {
        $mainframe->enqueueMessage('Veuillez inclure vos dossiers en cours dans les voeux. Revenez sur la page d\'accueil pour supprimer des dossiers si besoin.');
        $mainframe->redirect($_SERVER['REQUEST_URI']);
      } else {
          $number_app_same_wishes++;
      }
    }

    foreach ($applications as $key => $application) {
        $check_voeu = in_array($application->campaign_id, $campaigns);
        if ($check_voeu) {
            $number_app_same_wishes++;
        }
    }
    //

    $query->clear()
        ->select('lp.id,lp.max_applicant_files as max')
        ->from($db->quoteName('#__emundus_limit_by_program','lp'))
        ->leftJoin($db->quoteName('#__emundus_limit_by_program_repeat_programs','lpr').' ON '.$db->quoteName('lpr.parent_id').' = '.$db->quoteName('lp.id'))
        ->where($db->quoteName('lpr.programs') . ' = ' . $db->quote($prog));
    $db->setQuery($query);
    $max_applicant_files = $db->loadObject();

    // If limit not reached, foreach mentions we check if an application exist, else we create a new
    if ((sizeof($applications) < (int)$max_applicant_files->max) && (sizeof($campaigns) <= (int)$max_applicant_files->max) || empty($max_applicant_files)) {
        foreach ($campaigns as $campaign) {
            // Get campaign in array
            $app_key = array_search($campaign, array_column($applications, 'campaign_id'));
            if ($app_key === false) {
                // Create our new application
                $new_fnum = $h_files->createFnum($campaign, $fnumsDetails['applicant_id']);
                $query->clear()
                    ->insert($db->quoteName('#__emundus_campaign_candidature'));
                $query->set($db->quoteName('campaign_id') . ' = ' . $db->quote($campaign))
                    ->set($db->quoteName('applicant_id') . ' = ' . $db->quote($fnumsDetails['applicant_id']))
                    ->set($db->quoteName('user_id') . ' = ' . $db->quote($fnumsDetails['applicant_id']))
                    ->set($db->quoteName('fnum') . ' = ' . $db->quote($new_fnum));
                $db->setQuery($query);
                $db->execute();
                $new_file = $db->insertid();

                $query->clear()
                    ->select('cc.*')
                    ->from($db->quoteName('#__emundus_campaign_candidature', 'cc'))
                    ->where($db->quoteName('cc.id') . ' = ' . $db->quote($new_file));
                $db->setQuery($query);
                $applications[] = $db->loadObject();
                //

                // Copy current application to new application
                $result = $m_application->copyApplication($fnum, $new_fnum);
                //
            }
        }
    } elseif ($number_app_same_wishes >= (int)$max_applicant_files->max && sizeof($campaigns) <= (int)$max_applicant_files->max){
        //exit;
    } else {
    	$mainframe->enqueueMessage('Vous avez atteint la limite maximum de dossier pour ce domaine. En M1, vous pouvez déposer jusqu\'à 4 dossiers par domaine. En M2, vous pouvez déposer jusqu\'à 2 dossiers par domaine.');
        $mainframe->redirect($_SERVER['REQUEST_URI']);
    }
    //

    // Copy wishes to all applications of the same program
    /*foreach ($applications as $application) {
        $query->clear()
            ->select('id')
            ->from($dbTables->db_table_name)
            ->where($db->quoteName('fnum') . ' = ' . $db->quote($application->fnum));
        $db->setQuery($query);
        $parent_id = $db->loadResult();

        if(empty($parent_id) && $application->fnum != $fnum){
            $query->clear()
                ->insert($dbTables->db_table_name)
                ->set($db->quoteName('time_date') . ' = ' . $db->quote(date('Y-m-d H:i:s')))
                ->set($db->quoteName('user') . ' = ' . $db->quote($user))
                ->set($db->quoteName('fnum') . ' = ' . $db->quote($application->fnum));
            $db->setQuery($query);
            $parent_id = $db->execute();
        }

        $query->clear()
            ->delete($dbTables->table_join)
            ->where($db->quoteName('parent_id') . ' = ' . $db->quote($parent_id));
        $db->setQuery($query);
        $db->execute();

        foreach ($campaigns as $campaign) {
            $query->clear()
                ->insert($dbTables->table_join)
                ->set($db->quoteName('parent_id') . ' = ' . $db->quote($parent_id))
                ->set($db->quoteName('voeu') . ' = ' . $db->quote($campaign));
            $db->setQuery($query);
            $db->execute();
        }
    }*/
    //

    // Delete file not present in wishes
    /*foreach ($fnums_to_delete as $fnum_to_delete) {
        $query->clear()
            ->update('#__emundus_campaign_candidature')
            ->set($db->quoteName('published') . ' = -1')
            ->where($db->quoteName('fnum') . ' = ' . $db->quote($fnum_to_delete));
        $db->setQuery($query);
        $db->execute();
    }*/
    //

    // Redirect to index.php if the current fnum has been deleted
    if(!empty($redirect)) {
        $mainframe->enqueueMessage('Vos voeux ont été modifiés');
        $mainframe->redirect($redirect);
    }
    //
} catch(Exception $e){
    JLog::add($e->getMessage() . 'with query : ' . $query->__toString(), JLog::ERROR, 'com_emundus');
}

