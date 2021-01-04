<?php

require_once (JPATH_ROOT.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'access.php');
include_once(JPATH_BASE.'/components/com_emundus/models/campaign.php');
include_once(JPATH_BASE.'/components/com_emundus/models/profile.php');
include_once(JPATH_BASE.'/components/com_emundus/models/users.php');

$user = JFactory::getSession()->get('emundusUser');

$m_profiles = new EmundusModelProfile;
$applicant_profiles = $m_profiles->getApplicantsProfilesArray();
if(!in_array($user->profile, $applicant_profiles)){
    echo '<div id="em-dashboard-vue"></div>';
}
?>

<script src="media/mod_emundus_dashboard_vue/app.js"></script>
