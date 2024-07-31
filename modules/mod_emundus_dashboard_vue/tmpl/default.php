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

JText::script('COM_EMUNDUS_DASHBOARD_OK');

JText::script('COM_EMUNDUS_DASHBOARD_AREA');
JText::script('COM_EMUNDUS_DASHBOARD_EMPTY_LABEL');
JText::script('COM_EMUNDUS_DASHBOARD_HELLO');
JText::script('COM_EMUNDUS_DASHBOARD_WELCOME');

JText::script('COM_EMUNDUS_DASHBOARD_CLOSE_MESSENGER');
JText::script('COM_EMUNDUS_DASHBOARD_CLOSE_MESSENGER_DESC');
JText::script('COM_EMUNDUS_DASHBOARD_CLOSE_MESSENGER_CONFIRM');
JText::script('COM_EMUNDUS_DASHBOARD_CLOSE_MESSENGER_CANCEL');

$user = JFactory::getSession()->get('emundusUser');

$m_profiles = new EmundusModelProfile;
$applicant_profiles = $m_profiles->getApplicantsProfilesArray();

if(!in_array($user->profile, $applicant_profiles)) {
    ?>
    <div id="em-dashboard-vue"
         programmeFilter="<?= $programme_filter ?>"
         displayDescription="<?= $display_description ?>"
         displayShapes="<?= $display_shapes ?>"
         displayTchoozy="<?= $display_tchoozy ?>"
         name="<?= $name ?>"
         language="<?= $language ?>"
         displayName="<?= $display_name ?>"
         profile_name="<?= $profile_details->label ?>"
         profile_description="<?= $profile_details->description ?>"
    ></div>

    <script src="media/mod_emundus_dashboard_vue/app.js"></script>
	<?php
}
?>
<script type="text/javascript">
    document.addEventListener("DOMContentLoaded", function() {
        setTimeout(function() {
            var close = document.querySelectorAll("a.closeMessenger");
            if(close.length > 0){
                close.forEach(function(element) {
                    element.addEventListener("click", function() {
                        var fnum = this.getAttribute("data-fnum");
                        const swalWithEmundusButtons = Swal.mixin({
                            customClass: {
                                confirmButton: "em-swal-confirm-button",
                                cancelButton: "em-swal-cancel-button",
                                title: 'em-swal-title',
                                header: 'em-flex-column',
                                text: 'em-text-color'
                            },
                            buttonsStyling: false
                        });
                        swalWithEmundusButtons.fire({
                            title: Joomla.JText._('COM_EMUNDUS_DASHBOARD_CLOSE_MESSENGER'),
                            html: '<p class="em-text-align-center">'+Joomla.JText._('COM_EMUNDUS_DASHBOARD_CLOSE_MESSENGER_DESC')+'</p>',
                            icon: "info",
                            type: "warning",
                            reverseButtons: true,
                            showCancelButton: true,
                            confirmButtonText: Joomla.JText._('COM_EMUNDUS_DASHBOARD_CLOSE_MESSENGER_CONFIRM'),
                            cancelButtonText: Joomla.JText._('COM_EMUNDUS_DASHBOARD_CLOSE_MESSENGER_CANCEL')
                        }).then((result) => {
                            if (result.value) {
                                // Envoie les données via une requête POST
                                var formData = new FormData();
                                formData.append("fnum", fnum);
                                fetch("index.php?option=com_emundus&controller=messenger&task=closeMessenger", {
                                    method: "POST",
                                    body: formData
                                })
                                    .then(response => response.text())
                                    .then(data => {
                                        window.location.href = "/";
                                    })
                                    .catch(error => console.error("Erreur:", error));
                            }
                        });
                    });
                });
            }
        },3000);
    });
</script>
