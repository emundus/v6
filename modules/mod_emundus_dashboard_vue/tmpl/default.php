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
JText::script('COM_EMUNDUS_DASHBOARD_USERS_TOTAL');
JText::script('COM_EMUNDUS_DASHBOARD_USERS');

$user = JFactory::getSession()->get('emundusUser');

$m_profiles = new EmundusModelProfile;
$applicant_profiles = $m_profiles->getApplicantsProfilesArray();
if(!in_array($user->profile, $applicant_profiles)){
    echo '<div id="em-dashboard-vue"></div>';
}
?>

<script src="media/mod_emundus_dashboard_vue/app.js"></script>
