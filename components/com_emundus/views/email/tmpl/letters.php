<?php
defined('_JEXEC') or die('Restricted access');

$document = JFactory::getDocument();

JHTML::_('behavior.modal');
JHTML::_('behavior.tooltip');
JHTML::stylesheet( 'media/com_emundus/cssemundus.css/' );
JHTML::stylesheet( 'templates/system/css/general.css' );
JHTML::stylesheet( 'templates/system/csssystem.css/' );
// AJAX upload
JHTML::script('media/com_emundus/js/webtoolkit.aim.js');

require_once (JPATH_COMPONENT.DS.'helpers'.DS.'emails.php');
require_once (JPATH_COMPONENT.DS.'helpers'.DS.'list.php');
require_once (JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');

$current_user = JFactory::getUser();
if (!EmundusHelperAccess::isCoordinator($current_user->id)) {
	echo "<script>window.setTimeout('closeme();', 1500); function closeme() { parent.SqueezeBox.close(); }</script>";
	die('<h1><img src="'.$this->baseurl.'/media/com_emundus/images/icones/admin_val.png" width="80" height="80" align="middle" /> '.JText::_("COM_EMUNDUS_SAVED").'</h1>');
} else {
	$student_id = JRequest::getVar('jos_emundus_evaluations___student_id', null, 'GET', 'INT',0);
	$evaluations_id = JRequest::getVar('jos_emundus_evaluations___id', null, 'GET', 'INT',0);
	$itemid = JRequest::getVar('Itemid', null, 'GET', 'INT',0);

	include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'evaluation.php');
	include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'emails.php');

	$evaluations = new EmundusModelEvaluation;
	$emails = new EmundusModelEmails;

	$evaluation = $evaluations->getEvaluationByID($evaluations_id);
	$eligibility = $evaluations->getEvaluationEligibility();
	$result_id = @$eligibility[$evaluation[0]["result"]]->whenneed;

	$campaign = EmundusHelperfilters::getCampaignByID($evaluation[0]["campaign_id"]);

	$user = JFactory::getUser($student_id);

	$final_grade = $evaluations->getFinalGrade($student_id, $evaluation[0]["campaign_id"]);

	$chemin = EMUNDUS_PATH_REL;

	// Get email
	$email = $emails->getEmail("candidature_decision");

	?>

	<div id="attachment_list">
	  <form id="adminForm" name="adminForm" onSubmit="return OnSubmitForm();" method="POST" enctype="multipart/form-data" />
	    <?php echo EmundusHelperEmails::createEmailBlock(array('evaluation_result')); ?>
	  </form>
	</div>

	<?php
	$attachments = $evaluations->getEvaluationDocuments($student_id, $campaign['id']);

	if ( (!empty($result_id) && empty($final_grade['result_sent'])) || count($attachments) == 0 ) {
		require(JPATH_LIBRARIES.DS.'emundus'.DS.'pdf.php');
		$files = letter_pdf($user->id, @$result_id, $campaign['training'], $campaign['id'], $evaluations_id, "F");
	} else {
		if (!empty($attachments)) {
			$files = array();
			foreach ($attachments as $attachment) {
				$file_info['id'] = $attachment->id;
				$file_info['path'] = EMUNDUS_PATH_ABS.$user_id.DS.$attachment->filename;
				$file_info['attachment_id'] = $attachment->attachment_id;
				$file_info['name'] = $attachment->value;
				$file_info['url'] = EMUNDUS_PATH_REL.$student_id.'/'.$attachment->filename;

				$files[] = $file_info;
			}
		}
	}

	echo '<fieldset><legend>'.JText::_('COM_EMUNDUS_ATTACHMENTS_ATTACHMENTS').'</legend>';
	?>
	<script type="text/javascript">
			function startCallback() {
				submit_attachment = document.getElementById('submit_attachment');
				submit_attachment.value = "";
				submit_attachment.disabled=true;
				submit_attachment.style="background: url('media/com_emundus/images/icones/loading.gif');width:16px;height:11px;";
				return true;
			}

			function completeCallback(response) { //document.getElementById("em_attachment").innerHTML += response;
				submit_attachment = document.getElementById('submit_attachment');
				submit_attachment.disabled=false;
				submit_attachment.style="background: url('')";
				submit_attachment.value = "<?php echo JText::_('COM_EMUNDUS_UPLOAD'); ?>";
    			var objJSON = JSON.parse(response);
				var html = '<div id="em_dl_'+objJSON.id+'" class="em_dl"><a class="dO" target="_blank" href="'+objJSON.url+'"><div class="vI">'+objJSON.name+'</div> <div class="vJ"> ('+objJSON.filesize+' <?php echo JText::_("BYTES") ?>)</div></a><div class="em_email_icon" id="attachment_'+objJSON.id+'">';
				html += '<img src="<?php echo JURI::base(); ?>media/com_emundus/images/icones/x_8px.png" alt="<?php echo JText::_("COM_EMUNDUS_ATTACHMENTS_DELETE_ATTACHMENT"); ?>" title="<?php echo JText::_("COM_EMUNDUS_ATTACHMENTS_DELETE_ATTACHMENT"); ?>" onClick="if (confirm(\'<?php echo htmlentities(JText::_("COM_EMUNDUS_ATTACHMENTS_DELETE_ATTACHMENT_CONFIRM")); ?>\')) {deleteAttachment('+objJSON.id+');}"/></div>';

				document.getElementById("em_attachment").innerHTML += html;

				$('mail_attachments').value += "," + "<?php echo str_replace('\\', '\\\\', EMUNDUS_PATH_ABS.$student_id.DS); ?>" + objJSON.filename;
			}
		</script>

	<form action="<?php echo JURI::base(); ?>index.php?option=com_emundus&controller=application&format=raw&task=upload_attachment" method="post" enctype="multipart/form-data" onsubmit="return AIM.submit(this, {'onStart' : startCallback, 'onComplete' : completeCallback})">
		<div>
			<?php echo EmundusHelperFilters::setEvaluationList($result_id); ?>
			<input name="campaign_id" type="hidden" value="<?php echo $evaluation[0]["campaign_id"]; ?>" />
			<input name="uid" type="hidden" value="<?php echo $student_id; ?>" />
			<input name="aid" type="hidden" value="26" />
			<input name="can_be_viewed" type="hidden" value="1" />
			<input name="can_be_deleted" type="hidden" value="0" />
			<input name="MAX_FILE_SIZE" type="hidden" value="10000000" />
			<input name="filename" type="file" />
			<input id="submit_attachment" type="submit" value="<?php echo JText::_('COM_EMUNDUS_UPLOAD'); ?>" />
		</div>
	</form>
	<?php
	echo '<hr />';

	echo '<ul class="em_attachments_list">';
	$files_path = "";
	if(!empty($files) && isset($files)) {
		foreach ($files as $file) {
			$files_path .= str_replace('\\', '\\\\', $file['path']).',';
			echo '<div id="em_attachment">
				<div id="em_dl_'.$file['id'].'" class="em_dl">
					<a class="dO" target="_blank" href="'.$file['url'].'">
						<div class="vI"><img src="'.$this->baseurl.'/media/com_emundus/images/icones/pdf.png" alt="'.$file['name'].'" title="'.$file['name'].'" width="22" height="22" align="absbottom" /> '.$file['name'].'</div>
						<div class="vJ"></div>
					</a>
					<div class="em_email_icon" id="attachment_'.$file['id'].'">
						<img src="'.JURI::base().'media/com_emundus/images/icones/x_8px.png" alt="'.JText::_("COM_EMUNDUS_ATTACHMENTS_DELETE_ATTACHMENT").'" title="'.JText::_("COM_EMUNDUS_ATTACHMENTS_DELETE_ATTACHMENT").'" onClick="if (confirm('.htmlentities('"'.JText::_("COM_EMUNDUS_ATTACHMENTS_DELETE_ATTACHMENT_CONFIRM").'"').')) {deleteAttachment('.$file['id'].');}"/>
					</div>
				</div>
			</div>';
		}
		$files_path = rtrim($files_path, ",");
		echo '</ul>';
	} else {
		echo '<div id="em_attachment">';
		echo '<a href="index.php?option=com_fabrik&view=list&listid=108">'.JText::_('COM_EMUNDUS_LETTERS_NO_FILE_FROM_TEMPLATE').'<a>';
		echo '</div>';
	}
	echo '</fieldset>';

	?>

	<script>
	function OnSubmitForm() {
		var btn = document.getElementsByName(document.pressed);
		btn[0].disabled = true;
		btn[0].value = "<?php echo JText::_('COM_EMUNDUS_EMAILS_SENDING_EMAIL'); ?>";

		switch(document.pressed) {
			case 'evaluation_result_email':
				document.adminForm.action ="index.php?option=com_emundus&task=sendmail_applicant&Itemid=<?php echo $itemid ?>";
			break;
			default: return false;
		}
		return true;
	}
	var mail_body = document.getElementById("mail_body");
	var mail_subject = document.getElementById("mail_subject");
	var mail_attachments = document.getElementById("mail_attachments");
	mail_body.value = "<?php echo preg_replace('~[.[:cntrl:]]~', '', $email->message); ?>";
	mail_subject.value = "<?php echo $campaign['label']; ?>";
	mail_attachments.value = "<?php echo $files_path; ?>";

	</script>
<?php } ?>
