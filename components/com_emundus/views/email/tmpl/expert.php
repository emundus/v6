<?php
defined('_JEXEC') or die('Restricted access');

require_once(JPATH_COMPONENT . DS . 'helpers' . DS . 'emails.php');
require_once(JPATH_COMPONENT . DS . 'helpers' . DS . 'list.php');
require_once(JPATH_COMPONENT . DS . 'helpers' . DS . 'filters.php');
require_once(JPATH_COMPONENT . DS . 'models' . DS . 'files.php');

$m_files = new EmundusModelFiles();

JHTML::_('behavior.modal');
JHTML::_('behavior.tooltip');

$jinput        = JFactory::getApplication()->input;
$doc_to_attach = $jinput->get->get('attach', null);

$document = JFactory::getDocument();
$document->addStyleSheet(JURI::base() . "media/com_emundus/lib/bootstrap-232/css/bootstrap.min.css");
unset($document->_styleSheets[$this->baseurl . '/media/com_emundus/lib/bootstrap-emundus/css/bootstrap.min.css']);
JHTML::stylesheet('media/com_emundus/css/emundus_files.css');

// AJAX upload
$document->addScript('media/com_emundus/js/webtoolkit.aim.js');

$current_user = JFactory::getUser();

$itemid = $jinput->get->getInt('Itemid');

include_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'evaluation.php');
include_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'emails.php');

$m_evaluations = new EmundusModelEvaluation;
$m_emails      = new EmundusModelEmails;


$fnums = $m_files->getFnumsInfos($this->fnum_array);
$email = $m_emails->getEmail($this->default_email_tmpl);

?>

<div id="em-email-messages"></div>

<div class="em-modal-sending-emails" id="em-modal-sending-emails">
    <div id="em-sending-email-caption"><?= JText::_('COM_EMUNDUS_EMAILS_SENDING_EMAILS'); ?></div>
    <img class="em-sending-email-img" id="em-sending-email-img" src="media/com_emundus/images/sending-email.gif">
</div>

<div id="em-email">
    <form id="adminForm" name="adminForm">
		<?= $this->email; ?>
    </form>
</div>

<?php
$files = [];
foreach ($fnums as $fnum => $fnumInfo) {

	$attachments = $m_evaluations->getEvaluationDocuments($fnum, $fnumInfo['campaign_id'], $doc_to_attach);

	if (empty($attachments)) {

		require_once(JPATH_LIBRARIES . DS . 'emundus' . DS . 'pdf.php');
		$files[$fnum] = letter_pdf($fnumInfo['applicant_id'], $fnumInfo['step'], $fnumInfo['training'], $fnumInfo['campaign_id'], 0, "F", $fnum);

	}
	else {

		if (!empty($attachments)) {
			foreach ($attachments as $attachment) {
				$file_info['id']            = $attachment->id;
				$file_info['path']          = EMUNDUS_PATH_ABS . $fnumInfo['applicant_id'] . DS . $attachment->filename;
				$file_info['attachment_id'] = $attachment->attachment_id;
				$file_info['name']          = $attachment->value;
				$file_info['url']           = EMUNDUS_PATH_REL . $fnumInfo['applicant_id'] . '/' . $attachment->filename;

				$files[$fnum][] = $file_info;
			}
		}

	}
}

// Upload ajax
?>
<script type="text/javascript">
    function startCallback() {
        submit_attachment = document.getElementById('submit_attachment');
        submit_attachment.value = "";
        submit_attachment.disabled = true;
        submit_attachment.style = "background: url('media/com_emundus/images/icones/loading.gif');width:16px;height:11px;";
        return true;
    }

    function completeCallback(response) {
        submit_attachment = document.getElementById('submit_attachment');
        submit_attachment.disabled = false;
        submit_attachment.style = "background: url('')";
        submit_attachment.value = "<?= JText::_('COM_EMUNDUS_UPLOAD'); ?>";
        var objJSON = JSON.parse(response);
        var html = '<div id="em_dl_' + objJSON.id + '" class="em_dl"><a class="dO" target="_blank" href="' + objJSON.url + '"><div class="vI">' + objJSON.name + '</div> <div class="vJ"> (' + objJSON.filesize + ' <?= JText::_("BYTES") ?>)</div></a><div class="em_email_icon" id="attachment_' + objJSON.id + '">';
        html += '<img src="<?= JURI::base(); ?>media/com_emundus/images/icones/x_8px.png" alt="<?= JText::_("COM_EMUNDUS_ATTACHMENTS_DELETE_ATTACHMENT"); ?>" title="<?= JText::_("COM_EMUNDUS_ATTACHMENTS_DELETE_ATTACHMENT"); ?>" onClick="if (confirm(\'<?= htmlentities(JText::_("COM_EMUNDUS_ATTACHMENTS_DELETE_ATTACHMENT_CONFIRM")); ?>\')) {deleteAttachment(' + objJSON.id + ');}"/></div>';

        document.getElementById("em_attachment").innerHTML += html;
        $('#mail_attachments').value += "," + "<?= str_replace('\\', '\\\\', EMUNDUS_PATH_ABS); ?>" + objJSON.aid + "<?= str_replace('\\', '\\\\', DS); ?>" + objJSON.filename;
    }
</script>
<?php
$attachment_types = @EmundusHelperfilters::setEvaluationList(0);
if (!empty($attachment_types)) :?>

	<?php foreach ($fnums as $fnum => $fnumInfo) : ?>
        <form action="<?= JURI::base(); ?>index.php?option=com_emundus&controller=application&format=raw&task=upload_attachment"
              method="post" enctype="multipart/form-data"
              onsubmit="return AIM.submit(this, {'onStart' : startCallback, 'onComplete' : completeCallback})">
            <div>
				<?= $attachment_types; ?>
                <input name="campaign_id" type="hidden" value="<?= $fnumInfo['campaign_id']; ?>"/>
                <input name="uid" type="hidden" value="<?= $fnumInfo['applicant_id']; ?>"/>
                <input name="aid" type="hidden" value="26"/>
                <input name="can_be_viewed" type="hidden" value="1"/>
                <input name="can_be_deleted" type="hidden" value="0"/>
                <input name="MAX_FILE_SIZE" type="hidden" value="10000000"/>
                <input name="filename" type="file"/>
                <input id="submit_attachment" type="submit"
                       value="<?= JText::_('COM_EMUNDUS_UPLOAD') . ' (' . $fnumInfo['name'] . ')'; ?>"/>
            </div>
        </form>
	<?php endforeach; ?>

<?php endif; ?>

<hr/>
<ul class="em_attachments_list">

	<?php
	$files_path = "";
	if (!empty($files)) {
		echo '<fieldset><legend>' . JText::_('COM_EMUNDUS_ATTACHMENTS_ATTACHMENTS') . '</legend>';
		echo '<label><input type="checkbox" name="delete_attachment_box" id="delete_attachment_box" value="1"> ' . JText::_('COM_EMUNDUS_EXPERT_DELETE_ATTACHMENT_ONCE_MESSAGE_SENT') . '</label>';
		echo "<hr>";

		foreach ($files as $fnum => $file_for_fnum) {
			foreach ($file_for_fnum as $file) {
				$files_path .= str_replace('\\', '\\\\', $file['path']) . ',';

				echo '<div id="em_attachment">
                <div id="em_dl_' . $file['id'] . '" class="em_dl">
                    <div class="vI"><img src="' . $this->baseurl . '/media/com_emundus/images/icones/pdf.png" alt="' . $file['name'] . '" title="' . $file['name'] . '" width="22" height="22" align="absbottom" /> ' . $file['name'] . '</div>
                    <div class="em_email_icon" id="attachment_' . $file['id'] . '">
                        <img src="' . JURI::base() . 'media/com_emundus/images/icones/x_8px.png" alt="' . JText::_("COM_EMUNDUS_ATTACHMENTS_DELETE_ATTACHMENT") . '" title="' . JText::_("COM_EMUNDUS_ATTACHMENTS_DELETE_ATTACHMENT") . '" onClick="if (confirm(' . htmlentities('"' . JText::_("COM_EMUNDUS_ATTACHMENTS_DELETE_ATTACHMENT_CONFIRM") . '"') . ')) {deleteAttachment(' . $file['id'] . '); document.getElementById(\'mail_attachments\').value=\'\';}"/>
                    </div>
                </div>
            </div>';
			}
		}
		$files_path = rtrim($files_path, ",");
		echo '</fieldset></ul>';

	}
	else {
		echo '<div id="em_attachment">
        <input type="hidden" name="delete_attachment_box" id="delete_attachment_box" value="0">
        <a href="index.php?option=com_fabrik&view=list&listid=108">' . JText::_('COM_EMUNDUS_LETTERS_NO_FILE_FROM_TEMPLATE') . '<a>
        </div></ul>';
	}
	?>

    <script>
        var mail_body = document.getElementById("mail_body");
        var mail_subject = document.getElementById("mail_subject");
        var mail_attachments = document.getElementById("mail_attachments");
        var mail_to = document.getElementById("mail_to");
        var mail_from = document.getElementById("mail_from");
        var mail_from_name = document.getElementById("mail_from_name");
        mail_body.value = '<?= str_replace("'", "\'", preg_replace('~[[:cntrl:]]~', '', $email->message)); ?>';
        mail_subject.value = "<?= $email->subject; ?>";
        mail_attachments.value = "<?= $files_path; ?>";
        mail_to.value = "<?= implode(',', $experts_email); ?>";
        mail_from.value = "<?= $email->emailfrom; ?>";
        mail_from_name.value = "<?= $email->name; ?>";

        document.getElementById("adminForm").addEventListener("submit", event => {

            event.preventDefault();

            if (mail_to.value == "") {
                $("#mail_to").css("border", "2px solid red");
                $("html, body").animate({scrollTop: 0}, "slow");
            } else {

                var btn = document.getElementsByName(document.pressed);
                btn[0].disabled = true;
                btn[0].value = "<?= JText::_('COM_EMUNDUS_EMAILS_SENDING_EMAIL'); ?>";

                var delete_attachment = 0;
                if (document.getElementById('delete_attachment_box').checked) {
                    document.getElementById("delete_attachment").value = 1;
                    delete_attachment = 1;
                }

                $('#em-email-messages').empty();
                $('#em-modal-sending-emails').css('display', 'block');

                // update the textarea with the WYSIWYG content.
                tinymce.triggerSave();

                var data = {
                    mail_attachments: $('#mail_attachments').val(),
                    mail_to: $('#mail_to').val(),
                    mail_from_name: $('#mail_from_name').val(),
                    mail_from: $('#mail_from').val(),
                    mail_subject: $('#mail_subject').val(),
                    mail_body: $('#mail_body').val(),
                    delete_attachment: $('#delete_attachment').val(),
                    fnums: <?= json_encode(array_keys($fnums)); ?>
                };

                $.ajax({
                    type: "POST",
                    url: "<?= JURI::base(); ?>index.php?option=com_emundus&controller=email&task=sendmail_expert&Itemid=<?= $itemid; ?>&delete_attachment=" + delete_attachment,
                    data: data,
                    success: function (result) {

                        $('#em-modal-sending-emails').css('display', 'none');
                        $('#em-email').empty();

                        result = JSON.parse(result);
                        if (result.status) {

                            if (result.sent.length > 0) {

                                // Block containing the email adresses of the sent emails.
                                var sent_to = '<p>' + Joomla.JText._('COM_EMUNDUS_MAILS_SEND_TO') + '</p><ul class="list-group" id="em-mails-sent">';
                                result.sent.forEach(element => {
                                    sent_to += '<li class="list-group-item alert-success">' + element + '</li>';
                                });

                                Swal.fire({
                                    type: 'success',
                                    title: Joomla.JText._('COM_EMUNDUS_EMAILS_EMAILS_SENT') + result.sent.length,
                                    html: sent_to + '</ul>',
                                    customClass: {
                                        title: 'em-swal-title',
                                        confirmButton: 'em-swal-confirm-button',
                                        actions: "em-swal-single-action",
                                    },
                                });

                            } else {
                                Swal.fire({
                                    type: 'error',
                                    title: Joomla.JText._('COM_EMUNDUS_EMAILS_NO_EMAILS_SENT')
                                })
                            }

                            if (result.failed.length > 0) {
                                // Block containing the email adresses of the failed emails.
                                $("#em-email-messages").append('<div class="alert alert-danger">' + Joomla.JText._('COM_EMUNDUS_EMAILS_FAILED') + '<span class="badge">' + result.failed.length + '</span>' +
                                    '<ul class="list-group" id="em-mails-failed"></ul>');

                                result.failed.forEach(element => {
                                    $('#em-mails-sent').append('<li class="list-group-item alert-danger">' + element + '</li>');
                                });

                                $('#em-email-messages').append('</div>');
                            }

                        } else {
                            $("#em-email-messages").append('<span class="alert alert-danger">' + Joomla.JText._('COM_EMUNDUS_EMAILS_SEND_FAILED') + '</span>')
                        }
                        $('#em-email').append(result.message);
                    },
                    error: function () {
                        $("#em-email-messages").append('<span class="alert alert-danger">' + Joomla.JText._('COM_EMUNDUS_EMAILS_SEND_FAILED') + '</span>')
                    }
                });
            }
        });

    </script>
