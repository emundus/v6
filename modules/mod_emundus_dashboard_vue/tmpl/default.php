<?php

defined('_JEXEC') or die('Restricted Access');
require_once (JPATH_ROOT.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'access.php');
include_once(JPATH_BASE.'/components/com_emundus/models/campaign.php');
include_once(JPATH_BASE.'/components/com_emundus/models/profile.php');
include_once(JPATH_BASE.'/components/com_emundus/models/users.php');

JText::script('COM_EMUNDUS_DASHBOARD_CAMPAIGN_PUBLISHED');
JText::script('COM_EMUNDUS_DASHBOARD_CAMPAIGN_FROM');
JText::script('COM_EMUNDUS_DASHBOARD_CAMPAIGN_TO');
JText::script('COM_EMUNDUS_DASHBOARD_NO_CAMPAIGN');
JText::script('COM_EMUNDUS_DASHBOARD_FILES');
JText::script('COM_EMUNDUS_DASHBOARD_FILE_NUMBER');
JText::script('COM_EMUNDUS_DASHBOARD_FILES_BY_STATUS');
JText::script('COM_EMUNDUS_DASHBOARD_STATUS');
JText::script('COM_EMUNDUS_DASHBOARD_FILES_BY_STATUS_NUMBER');
JText::script('COM_EMUNDUS_DASHBOARD_USERS_BY_DAY');
JText::script('COM_EMUNDUS_DASHBOARD_USERS_NUMBER');
JText::script('COM_EMUNDUS_DASHBOARD_USERS_REGISTER');
JText::script('COM_EMUNDUS_DASHBOARD_USERS_DAYS');
JText::script('COM_EMUNDUS_DASHBOARD_USERS_TOTAL');
JText::script('COM_EMUNDUS_DASHBOARD_USERS');
JText::script('COM_EMUNDUS_DASHBOARD_FAQ_QUESTION');
JText::script('COM_EMUNDUS_DASHBOARD_FAQ_REDIRECT');
JText::script('COM_EMUNDUS_DASHBOARD_SELECT_FILTER');
JText::script('COM_EMUNDUS_DASHBOARD_FILES_BY_STATUS');
/* SCIENCES PO */
JText::script('COM_EMUNDUS_DASHBOARD_KEY_FIGURES_TITLE');
JText::script('COM_EMUNDUS_DASHBOARD_INCOMPLETE_FILES');
JText::script('COM_EMUNDUS_DASHBOARD_REGISTERED_FILES');
JText::script('COM_EMUNDUS_DASHBOARD_FILES_BY_STATUS_AND_DATE');
JText::script('COM_EMUNDUS_DASHBOARD_FILES_BY_STATUS_AND_SESSION');
JText::script('COM_EMUNDUS_DASHBOARD_FILES_BY_COURSES');
JText::script('COM_EMUNDUS_DASHBOARD_ALL_PROGRAMMES');
JText::script('COM_EMUNDUS_DASHBOARD_FILTER_BY_PROGRAMMES');
JText::script('COM_EMUNDUS_DASHBOARD_FILES_BY_NATIONALITIES');
JText::script('COM_EMUNDUS_DASHBOARD_UNIVERSITY');
JText::script('COM_EMUNDUS_DASHBOARD_PRECOLLEGE');
JText::script('COM_EMUNDUS_DASHBOARD_1ST_SESSION');
JText::script('COM_EMUNDUS_DASHBOARD_2ND_SESSION');
JText::script('COM_EMUNDUS_DASHBOARD_JUNE_SESSION');
JText::script('COM_EMUNDUS_DASHBOARD_JULY_SESSION');

$user = JFactory::getSession()->get('emundusUser');

$m_profiles = new EmundusModelProfile;
$applicant_profiles = $m_profiles->getApplicantsProfilesArray();
if(!in_array($user->profile, $applicant_profiles)){
    if($programme_filter) {
        echo '<div id="em-dashboard-vue" programmeFilter="1"></div>';
    } else {
        echo '<div id="em-dashboard-vue" programmeFilter="0"></div>';
    }
}
?>

<script src="media/mod_emundus_dashboard_vue/app.js"></script>
