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

// Get repeat db table
$query->select('fl.db_table_name,fj.table_join')
    ->from($db->quoteName('#__fabrik_lists', 'fl'))
    ->leftJoin($db->quoteName('#__fabrik_joins', 'fj') . ' ON ' . $db->quoteName('fj.join_from_table') . ' = ' . $db->quoteName('fl.db_table_name'))
    ->where($db->quoteName('fl.form_id') . ' = ' . $db->quote($formid))
    ->andWhere($db->quoteName('table_join_key') . ' = ' . $db->quote('parent_id'));
$db->setQuery($query);
$dbTables = $db->loadObject();
//

$fnumsDetails = $m_profile->getFnumDetails($fnum);

// Check the limit of files by program and by user
$query->clear()
    ->select('cc.*,sc.training')
    ->from($db->quoteName('#__emundus_campaign_candidature', 'cc'))
    ->leftJoin($db->quoteName('#__emundus_setup_campaigns', 'sc') . ' ON ' . $db->quoteName('sc.id') . ' = ' . $db->quoteName('cc.campaign_id'))
    ->where($db->quoteName('sc.id') . ' IN (' . implode(',',$campaigns) . ')')
    ->andWhere($db->quoteName('sc.year') . ' = ' . $db->quote($fnumsDetails['year']))
    ->andWhere($db->quoteName('cc.applicant_id') . ' = ' . $db->quote($user));
$db->setQuery($query);
$applications = $db->loadObjectList();

// Copy wishes to all applications of the same program
foreach ($applications as $application) {
    $query->clear()
        ->select('id')
        ->from($dbTables->db_table_name)
        ->where($db->quoteName('fnum') . ' = ' . $db->quote($application->fnum));
    $db->setQuery($query);
    $parent_id = $db->loadResult();

    if(empty($parent_id)){
        $query->clear()
            ->insert($dbTables->db_table_name)
            ->set($db->quoteName('time_date') . ' = ' . $db->quote(date('Y-m-d H:i:s')))
            ->set($db->quoteName('user') . ' = ' . $db->quote($user))
            ->set($db->quoteName('fnum') . ' = ' . $db->quote($application->fnum))
            ->set($db->quoteName('code_prog') . ' = ' . $db->quote($application->training));
        $db->setQuery($query);
        $db->execute();
        $parent_id = $db->insertid();
    }

    if(!empty($parent_id)) {
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
    }
}
//
//
