<?php

// get all applicants with status = "confirmer son admission"
$confirm_admission_status = 28;

$db = JFactory::getDBO();
$query = $db->getQuery(true);

$query->select('applicant_id, fnum, confirm_admission_date')
->from('#__emundus_campaign_candidature')
->where('status = '.$confirm_admission_status);

$db->setQuery($query);

$applicants = array();
try {
	$applicants = $db->loadObjectList();
} catch (Exception $e) {
	echo 'Error: '.$e->getMessage();
	exit;
}

if (!empty($applicants)) {
	require_once( JPATH_SITE .DS. 'components' .DS. 'com_emundus' .DS. 'models' .DS. 'files.php' );
  require_once( JPATH_SITE .DS. 'components' .DS. 'com_emundus' .DS. 'controllers' .DS. 'messages.php' );
  $files_model = new EmundusModelFiles();
  $messages_model = new EmundusControllerMessages();

	foreach ($applicants as $applicant) {
		// check diff between current date and confirm_admission_date
		$date_diff = date_diff(date_create(date('Y-m-d')), date_create($applicant->confirm_admission_date));
		$days_diff = $date_diff->format('%a');

		// if diff > 5 days, set status = "désistement"
		if ($days_diff > 5) {	
  		
			$files_model->updateState([$applicant->fnum], 27);
		} else {
			// if diff == 4 days, send email to applicant
			if ($days_diff == 4) {
				$send = $messages_model->sendEmail($applicant->fnum, '107'); 
			}
		}
	}

	return count($applicants);
}