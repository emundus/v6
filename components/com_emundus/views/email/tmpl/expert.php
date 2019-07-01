<?php
defined('_JEXEC') or die('Restricted access');

require_once (JPATH_COMPONENT.DS.'helpers'.DS.'emails.php');
require_once (JPATH_COMPONENT.DS.'helpers'.DS.'list.php');
require_once (JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');
require_once (JPATH_COMPONENT.DS.'models'.DS.'files.php');

$m_files = new EmundusModelFiles();

JHTML::_('behavior.modal');
JHTML::_('behavior.tooltip');

$jinput = JFactory::getApplication()->input;

$document = JFactory::getDocument();
//$document->addStyleSheet(JURI::base()."media/com_emundus/css/emundus_trombinoscope.css" );
$document->addStyleSheet(JURI::base()."media/com_emundus/lib/bootstrap-232/css/bootstrap.min.css" );
unset($document->_styleSheets[$this->baseurl .'/media/com_emundus/lib/bootstrap-emundus/css/bootstrap.min.css']);
// AJAX upload
$document->addScript('media/com_emundus/js/webtoolkit.aim.js');

$current_user = JFactory::getUser();
if (!EmundusHelperAccess::asAccessAction(18, 'c', $current_user->id, $this->fnums->fnum) ) {
	echo "<script>window.setTimeout('closeme();', 1500); function closeme() { parent.SqueezeBox.close(); }</script>";
	die('<h1>'.JText::_("RESTRICTED_ACCESS").'</h1>');
} else {
	$student_id = $this->fnums->sid;
	$itemid = JRequest::getVar('Itemid', null, 'GET', 'INT',0);

	include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'evaluation.php');
	include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'emails.php');

	$evaluations = new EmundusModelEvaluation;
	$emails = new EmundusModelEmails;

	if (empty($this->fnums->cid) || !isset($this->fnums->cid)) {
		$fnumInfos = $m_files->getFnumInfos($this->fnums);
		$this->fnums->cid = $fnumInfos['id'];
		$this->fnums->fnum = $this->fnums;
		$this->fnums->status = -1;
	} 

	$campaign = @EmundusHelperfilters::getCampaignByID($this->fnums->cid);

	$user = JFactory::getUser($student_id);
	$email = $emails->getEmail($this->default_email_tmpl);

	$experts_email = array();
	foreach ($this->experts_list as $key => $value) {
		$experts_email[] = $value['email'];
	}
	?>
	
	<div id="em-email-messages"></div>

	<div class="em-modal-sending-emails" id="em-modal-sending-emails">
	    <div id="em-sending-email-caption"><?php echo JText::_('SENDING_EMAILS') ;?></div>
	    <img class="em-sending-email-img" id="em-sending-email-img" src="/images/emundus/sending-email.gif">
	</div>

	<div id="em-email">
	  <form id="adminForm" name="adminForm">
	    <?php echo $this->email; ?>
	  </form>
	</div>

	<?php  
	$attachments = $evaluations->getEvaluationDocuments($this->fnums->fnum, $this->fnums->cid, 0); 

	if ( count($attachments) == 0 ) {
		require(JPATH_LIBRARIES.DS.'emundus'.DS.'pdf.php'); 
		$files = letter_pdf($this->fnums->sid, $this->fnums->status, $campaign['training'], $this->fnums->cid, 0, "F", $this->fnums->fnum);
	} else {
		if (!empty($attachments)) {
			$files = array();
			foreach ($attachments as $attachment) {
				$file_info['id'] = $attachment->id;
				$file_info['path'] = EMUNDUS_PATH_ABS.$student_id.DS.$attachment->filename;
				$file_info['attachment_id'] = $attachment->attachment_id;
				$file_info['name'] = $attachment->value;
				$file_info['url'] = EMUNDUS_PATH_REL.$student_id.'/'.$attachment->filename;

				$files[] = $file_info;
			}
		}
	}

	// Upload ajax
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
				submit_attachment.value = "<?php echo JText::_('UPLOAD'); ?>";
    			var objJSON = JSON.parse(response);
				var html = '<div id="em_dl_'+objJSON.id+'" class="em_dl"><a class="dO" target="_blank" href="'+objJSON.url+'"><div class="vI">'+objJSON.name+'</div> <div class="vJ"> ('+objJSON.filesize+' <?php echo JText::_("BYTES") ?>)</div></a><div class="em_email_icon" id="attachment_'+objJSON.id+'">';
				html += '<img src="<?php echo JURI::base(); ?>media/com_emundus/images/icones/x_8px.png" alt="<?php echo JText::_("DELETE_ATTACHMENT"); ?>" title="<?php echo JText::_("DELETE_ATTACHMENT"); ?>" onClick="if (confirm(\'<?php echo htmlentities(JText::_("DELETE_ATTACHMENT_CONFIRM")); ?>\')) {deleteAttachment('+objJSON.id+');}"/></div>';

				document.getElementById("em_attachment").innerHTML += html;

				$('mail_attachments').value += "," + "<?php echo str_replace('\\', '\\\\', EMUNDUS_PATH_ABS.$student_id.DS); ?>" + objJSON.filename;


				//document.getElementById("nr").innerHTML = parseInt(document.getElementById("nr").innerHTML) + 1;
				//document.getElementById("r").innerHTML = response;
			}
		</script>
 <?php 
 	$attachment_types = @EmundusHelperfilters::setEvaluationList(0); 
 	if (!empty($attachment_types)) {
 ?>
	<form action="<?php echo JURI::base(); ?>index.php?option=com_emundus&controller=application&format=raw&task=upload_attachment" method="post" enctype="multipart/form-data" onsubmit="return AIM.submit(this, {'onStart' : startCallback, 'onComplete' : completeCallback})">
		<div>
			<?php echo $attachment_types; ?>
			<input name="campaign_id" type="hidden" value="<?php echo $this->fnums->cid; ?>" />
			<input name="uid" type="hidden" value="<?php echo $student_id; ?>" />
			<input name="aid" type="hidden" value="26" />
			<input name="can_be_viewed" type="hidden" value="1" />
			<input name="can_be_deleted" type="hidden" value="0" />
			<input name="MAX_FILE_SIZE" type="hidden" value="10000000" />
			<input name="filename" type="file" />
			<input id="submit_attachment" type="submit" value="<?php echo JText::_('UPLOAD'); ?>" />
		</div>
	</form>
	<?php
	}
	echo '<hr />';
	//	<div># of submited forms: <span id="nr">0</span></div>
	//	<div>last submit response: <span id="r"></span></div>';
/////////////////////////////////////////

	echo '<ul class="em_attachments_list">';
	$files_path = "";

	if(!empty($files) && isset($files)) {
		echo '<fieldset><legend>'.JText::_('ATTACHMENTS').'</legend>';
		echo '<label><input type="checkbox" name="delete_attachment_box" id="delete_attachment_box" value="1"> '.JText::_('DELETE_ATTACHMENT_ONCE_MESSAGE_SENT').'</label>';
		echo "<hr>";

		foreach ($files as $file) {
			$files_path .= str_replace('\\', '\\\\', $file['path']).',';
			//echo '<li><a href="'.$file['url'].'" target="_blank"><img src="'.$this->baseurl.'/media/com_emundus/images/icones/pdf.png" alt="'.JText::_('ATTACHMENTS').'" title="'.JText::_('ATTACHMENTS').'" width="22" height="22" align="absbottom" /> '.$file['name'].'</a></li>';
			echo '<div id="em_attachment">
				<div id="em_dl_'.$file['id'].'" class="em_dl">
					<a class="dO" target="_blank" href="'.$file['url'].'">
						<div class="vI"><img src="'.$this->baseurl.'/media/com_emundus/images/icones/pdf.png" alt="'.$file['name'].'" title="'.$file['name'].'" width="22" height="22" align="absbottom" /> '.$file['name'].'</div>
						<div class="vJ"></div>
					</a>
					<div class="em_email_icon" id="attachment_'.$file['id'].'">
						<img src="'.JURI::base().'media/com_emundus/images/icones/x_8px.png" alt="'.JText::_("DELETE_ATTACHMENT").'" title="'.JText::_("DELETE_ATTACHMENT").'" onClick="if (confirm('.htmlentities('"'.JText::_("DELETE_ATTACHMENT_CONFIRM").'"').')) {deleteAttachment('.$file['id'].'); document.getElementById(\'mail_attachments\').value=\'\';}"/>
					</div>
				</div>
			</div>';
		}
		$files_path = rtrim($files_path, ",");
		echo '</ul>';
	} else {
		echo '<div id="em_attachment">';
		echo '<input type="hidden" name="delete_attachment_box" id="delete_attachment_box" value="0">';
		echo '<a href="index.php?option=com_fabrik&view=list&listid=108">'.JText::_('NO_FILE_FROM_TEMPLATE').'<a>';
		echo '</div>';
	}
	echo '</fieldset>';

	?>

	<script>
	
	var mail_body = document.getElementById("mail_body");
	var mail_subject = document.getElementById("mail_subject");
	var mail_attachments = document.getElementById("mail_attachments");
	var mail_to = document.getElementById("mail_to");
	var mail_from = document.getElementById("mail_from");
	var mail_from_name = document.getElementById("mail_from_name");
	mail_body.value = '<?php echo str_replace("'", "\'", preg_replace('~[[:cntrl:]]~', '', $email->message)); ?>';
	mail_subject.value = "<?php echo $email->subject; ?>";
	mail_attachments.value = "<?php echo $files_path; ?>";
	mail_to.value = "<?php echo implode(',', $experts_email); ?>";
	mail_from.value = "<?php echo $email->emailfrom; ?>";
	mail_from_name.value = "<?php echo $email->name; ?>";

	function OnSubmitForm() {
		if(mail_to.value == ""){
			$("#mail_to").css("border", "2px solid red"); 
			$("html, body").animate({ scrollTop: 0 }, "slow");
		} else {

			var btn = document.getElementsByName(document.pressed);
			btn[0].disabled = true;
			btn[0].value = "<?php echo JText::_('SENDING_EMAIL'); ?>";

			var delete_attachment = 0;
			if (document.getElementById('delete_attachment_box').checked) {
				document.getElementById("delete_attachment").value = 1;
				delete_attachment = 1;
			}

			$('#em-email-messages').empty();
            $('#em-modal-sending-emails').css('display', 'block');

            var data = {
                mail_attachments: $('#mail_attachments').val(),
                mail_to			: $('#mail_to').val(),
                mail_from_name 	: $('#mail_from_name').val(),
                mail_from 		: $('#mail_from').val(),
                mail_subject 	: $('#mail_subject').val(),
                mail_body		: $('#mail_body').val(),
                delete_attachment : $('#delete_attachment').val(),
                fnum			: "<?php echo $this->fnums->fnum ?>",
                sid 			: "<?php echo $student_id ?>"
            };

            $.ajax({
                type: "POST",
                url: "<?php echo JURI::base(); ?>index.php?option=com_emundus&controller=email&task=sendmail_expert&fnum=<?php echo $this->fnums->fnum ?>&sid=<?php echo $student_id ?>&Itemid=<?php echo $itemid ?>&delete_attachment="+delete_attachment,
                data: data,
                success: function(result) {

                    $('#em-modal-sending-emails').css('display', 'none');
                    $('#em-email').empty();

                    result = JSON.parse(result);

                    if (result.status) {

                        if (result.sent.length > 0) {
                            // Block containing the email adresses of the sent emails.
                            var sent_to = '<p>' + Joomla.JText._('SEND_TO') + '</p><ul class="list-group" id="em-mails-sent">';
                            result.sent.forEach(function (element) {
                                sent_to += '<li class="list-group-item alert-success">'+element+'</li>';
                                console.log(element);
                            })

                            Swal.fire({
                                type: 'success',
                                title: Joomla.JText._('EMAILS_SENT') + result.sent.length,
                                html:  sent_to + '</ul>'
                            });


                        } else {
                            Swal.fire({
                                type: 'error',
                                title: Joomla.JText._('NO_EMAILS_SENT')
                            })
                            /*$("#em-email-messages").append('<span class="alert alert-danger" id="em-mails-sent">'+Joomla.JText._('NO_EMAILS_SENT')+'</span>');*/
                        }

                        if (result.failed.length > 0) {
                            // Block containing the email adresses of the failed emails.
                            $("#em-email-messages").append('<div class="alert alert-danger">'+Joomla.JText._('EMAILS_FAILED')+'<span class="badge">'+result.failed.length+'</span>'+
                                                        '<ul class="list-group" id="em-mails-failed"></ul>');

                            result.failed.forEach(function (element) {
                                $('#em-mails-sent').append('<li class="list-group-item alert-danger">'+element+'</li>');
                            });

                            $('#em-email-messages').append('</div>');
                        }

                    } else {
                        $("#em-email-messages").append('<span class="alert alert-danger">'+Joomla.JText._('SEND_FAILED')+'</span>')
                    }
                    $('#em-email').append(result.message);
                },
                error : function () {
                    $("#em-email-messages").append('<span class="alert alert-danger">'+Joomla.JText._('SEND_FAILED')+'</span>')
                }
            });
		}
	    event.preventDefault();
	};

	var form = document.getElementById("adminForm");
	form.addEventListener("submit", OnSubmitForm, true);

	</script>

<?php
}
?>
