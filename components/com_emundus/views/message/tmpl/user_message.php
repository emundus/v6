<?php
/**
 * @package    Joomla
 * @subpackage emundus
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 * @author     Hugo Moracchini
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

$current_user = JFactory::getUser();

// Load the WYSIWYG editor used to edit the mail body.
$editor = JFactory::getEditor('tinymce');
$mail_body = $editor->display('mail_body', '[NAME], ', '100%', '400', '20', '20', false, 'mail_body', null, null, array('mode' => 'simple'));

$m_messages = new EmundusModelMessages();

// load all of the available messages, categories (to sort messages),attachments, letters.
$message_categories = $m_messages->getAllCategories();
$message_templates 	= $m_messages->getAllMessages();

$email_list = array();
?>

<!-- WYSIWYG Editor -->
<link rel="stylesheet" href="components/com_jce/editor/libraries/css/editor.min.css" type="text/css">
<script data-cfasync="false" type="text/javascript" src="media/editors/tinymce/tinymce.min.js"></script>
<script data-cfasync="false" type="text/javascript" src="media/editors/tinymce/js/tinymce.min.js"></script>
<script data-cfasync="false" type="text/javascript">tinyMCE.init({menubar:false,statusbar: false})</script>

<div id="em-email-messages"></div>

<div class="em-modal-sending-emails" id="em-modal-sending-emails">
    <div id="em-sending-email-caption" class="em-sending-email-caption"><?= JText::_('COM_EMUNDUS_EMAILS_SENDING_EMAILS'); ?></div>
    <img class="em-sending-email-img" id="em-sending-email-img" src="media/com_emundus/images/sending-email.gif" alt="Sending email loop">
</div>

<form id="emailForm" class="em-form-message" name="emailForm" style="padding:0px 15px;">
    <div class="em_email_block" id="em_email_block">
        <div class="form-inline row">

            <!-- Dropdown to select the email categories used. -->
            <div class="form-group col-md-6 col-sm-6 em-form-selectCategory">
                <label for="select_category" ><?= JText::_('COM_EMUNDUS_EMAILS_SELECT_CATEGORY'); ?></label>
                <select name="select_category" id="select_category" class="form-control" onChange="setCategory(this);">
                    <?php if (!$message_categories) :?>
                        <option value="%"> <?= JText::_('COM_EMUNDUS_EMAILS_NO_CATEGORIES_FOUND'); ?> </option>
                    <?php else: ?>
                        <option value="%"> <?= JText::_('COM_EMUNDUS_EMAILS_SELECT_CATEGORY'); ?> </option>
                        <?php foreach ($message_categories as $message_category) :?>
                            <?php if (!empty($message_category)) :?>
                                <option value="<?= $message_category; ?>"> <?= $message_category; ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Dropdown to select the email template used. -->
            <div class="form-group col-md-6 col-sm-6 em-form-selectTypeEmail">
                <label for="select_template" ><?= JText::_('COM_EMUNDUS_EMAILS_SELECT_TEMPLATE'); ?></label>
                <select name="select_template" id="message_template" class="form-control" onChange="getTemplate(this);">
                    <?php if (!$message_templates) :?>
                        <option value="%"> <?= JText::_('COM_EMUNDUS_EMAILS_NO_TEMPLATES_FOUND'); ?> </option>
                    <?php else: ?>
                        <option value="%"> <?= JText::_('COM_EMUNDUS_EMAILS_SELECT_TEMPLATE'); ?> </option>
                        <?php foreach ($message_templates as $message_template) :?>
                            <option value="<?= $message_template->id; ?>"> <?= $message_template->subject; ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        </div>

        <!-- Add current user to Bcc -->
        <div class="checkbox em-form-checkbox-copyEmail">
            <label>
                <input type="checkbox" id="sendUserACopy"> <?= JText::_('COM_EMUNDUS_EMAILS_SEND_COPY_TO_CURRENT_USER'); ?>
            </label>
        </div>

        <div class="form-group em-form-recipients">
            <!-- List of users / their emails, gotten from the fnums selected. -->
            <div class="well well-sm" id="em-recipitents">
                <span class='label label-grey'><?= JText::_('COM_EMUNDUS_TO'); ?>:</span>
                <?php $uids = []; ?>
                <?php foreach ($this->users as $user) :?>

                    <?php if (!empty($user->email)) : ?>
                        <?php
                            $email_list[] = $user->email;
                            $uids[] = $user->id;
                            ?>
                        <span class="label label-grey em-email-label">
							<?= $user->name.' <em>&lt;'.$user->email.'&gt;</em>'; ?>
						</span>
                        <input type="hidden" name="ud[]" id="ud" value="<?= $user->id; ?>"/>
					<?php endif; ?>

				<?php endforeach; ?>
			</div>
		</div>

        <input name="uids" type="hidden" class="inputbox" id="uids" value="<?= implode(',', $uids); ?>" />
        <input name="mail_from_id" type="hidden" class="inputbox" id="mail_from_id" value="<?= $current_user->id; ?>" /><br>

        <div class="form-group em-form-sender">
			<div class="inputbox input-xlarge form-control form-inline">
				<span class='label label-grey' for="mail_from" ><?= JText::_('FROM'); ?>:</span>
				<div class="form-group" style="display:inline-block !important;" id="mail_from_name" contenteditable="true"><?= $current_user->name; ?> </div>
				<div class="form-group" style="display:inline-block !important;" id="mail_from" contenteditable="true"><strong> <?= $current_user->email; ?></strong></div>
			</div>
		</div>
		<div class="form-group em-form-subject">
			<div class="inputbox input-xlarge form-control form-inline">
				<span class='label label-grey' for="mail_from" ><?= JText::_('COM_EMUNDUS_EMAILS_SUBJECT'); ?>:</span>
				<div class="form-group" style="display:inline-block !important;" id="mail_subject" contenteditable="true"><?= JFactory::getConfig()->get('sitename'); ?></div>
			</div>

			<!-- Email WYSIWYG -->
			<?php echo $mail_body; ?>
		</div>

		<div class="form-inline row em-form-attachments">

			<div class="form-group col-sm-12 col-md-12">
				<!-- Upload a file from computer -->
                <div class="upload-file em-form-attachments-uploadFile" id="upload_file">

                    <div class="file-browse">
                        <span id="em-filename"><?= JText::_('COM_EMUNDUS_ATTACHMENTS_FILE_NAME'); ?></span>

                        <label for="em-file_to_upload" type="button"><?= JText::_('COM_EMUNDUS_ATTACHMENTS_SELECT_FILE_TO_UPLOAD') ?>
                            <input type="file" id="em-file_to_upload" onChange="addFile();">
                        </label>
                    </div>
                    <div id="em-progress-wrp" class="loading-bar">
                        <div class="progress-bar"></div>
                        <div class="status">0%</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="form-group attachment em-form-attachments-location">
        <ul class="list-group" id="em-attachment-list">
            <!-- Files to be attached will be added here. -->
        </ul>
    </div>

    <input type="hidden" name="task" value=""/>
</form>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@8"></script>
<script type="text/javascript">

    // Editor loads disabled by default, we apply must toggle it active on page load.
    $(document).ready(() => {
        tinyMCE.execCommand('mceToggleEditor', true, 'mail_body');
    });

    // Change file upload string to selected file and reset the progress bar.
    $('#em-file_to_upload').change(function () {
        $('#em-filename').html(this.value.match(/([^\/\\]+)$/)[1]);
        $("#em-progress-wrp .progress-bar").css("width", + 0 + "%");
        $("#em-progress-wrp .status").text(0 + "%");
    });

    // Loads the template and updates the WYSIWYG editor
    function getTemplate(select) {

        $.ajax({
            type: "POST",
            url : "index.php?option=com_emundus&controller=messages&task=gettemplate",
            data : {
                select : select.value
            },
            success: email => {

                email = JSON.parse(email);

                var email_block = document.getElementById("em_email_block");
                if (email.tmpl.subject) {
                    $("#mail_subject").text(email.tmpl.subject);
                }

                if (email.tmpl.emailfrom) {
                    $("#mail_from").text(email.tmpl.emailfrom);
                }

                if (email.tmpl.name) {
                    $("#mail_from_name").text(email.tmpl.name);
                }

                $("#mail_body").val(email.tmpl.message);
                tinyMCE.execCommand("mceSetContent", false, email.tmpl.message);
                tinyMCE.execCommand("mceRepaint");

                // Get the attached uploaded file if there is one.
                if (typeof(email.tmpl.attachment) != 'undefined' && email.tmpl.attachment != null) {
                    $('#em-attachment-list').append('<li class="list-group-item upload"><div class="value hidden">'+email.tmpl.attachment+'</div>'+ email.tmpl.attachment.split('\\').pop().split('/').pop() +'<span class="badge btn-danger" onClick="removeAttachment(this);"><span class="glyphicon glyphicon-remove"></span></span><span class="badge"><span class="glyphicon glyphicon-saved"></span></span></li>');
                }
            },
            error: () => {
                // handle error
                $("#message_template").append('<span class="alert"> <?= JText::_('ERROR'); ?> </span>')
            }
        });

    }

    // Used for toggling the options dipslayed in the message templates dropdown.
    function setCategory(element) {

        if (element.value === "%") {
            category = 'all';
        } else {
            category = element.value;
        }

        $.ajax({
            type: "GET",
            url: "index.php?option=com_emundus&controller=messages&task=setcategory&category="+category,
            success: data => {

                data = JSON.parse(data);
                if (data.status) {

                    var $el = $("#message_template");
                    $('#message_template option:gt(0)').remove();

                    $.each(data.templates, (key, value) => {
                        $el.append($("<option></option>").attr("value", value.id).text(value.subject));
                    });
                } else {
                    $("#message_template").append('<span class="alert"> <?= JText::_('ERROR'); ?> </span>')
                }
            },
            error: error => {
                // handle error
                $("#message_template").append('<span class="alert"> <?= JText::_('ERROR'); ?> </span>')
            },
        });
    }

    // Used for reseting a File upload input.
    function resetFileInput(e) {
        e.wrap('<form>').closest('form').get(0).reset();
        e.unwrap();
    }

    // Add file to the list being attached.
    function addFile() {
        // We need to get the file uploaded by the user.
        let file = $("#em-file_to_upload")[0].files[0];
        let upload = new Upload(file);
        // Verification of style size and type can be done here.
        upload.doUpload();
    }

    function removeAttachment(element) {
        $(element).parent().remove();
    }


    // Helper function for uploading a file via AJAX.
    var Upload = function(file) {
        this.file = file;
    };

    Upload.prototype.getType = function() { return this.file.type; };
    Upload.prototype.getSize = function() { return this.file.size; };
    Upload.prototype.getName = function() { return this.file.name; };

    Upload.prototype.doUpload = function() {
        var that = this;
        var formData = new FormData();

        // add assoc key values, this will be posts values
        formData.append("file", this.file, this.getName().replace(/\s/g, '-').normalize("NFD").replace(/[\u0300-\u036f]/g, ""));
        formData.append("upload_file", true);

        $.ajax({
            type: "POST",
            url: "index.php?option=com_emundus&controller=messages&task=uploadfiletosend",
            xhr: () => {
                var myXhr = $.ajaxSettings.xhr();
                if (myXhr.upload) {
                    myXhr.upload.addEventListener('progress', that.progressHandling, false);
                }
                return myXhr;
            },
            success: data => {
                data = JSON.parse(data);

                if (data.status) {
                    $('#em-attachment-list').append('<li class="list-group-item upload"><div class="value hidden">'+data.file_path+'</div>'+data.file_name+'<span class="badge btn-danger" onClick="removeAttachment(this);"><span class="glyphicon glyphicon-remove"></span></span><span class="badge"><span class="glyphicon glyphicon-saved"></span></span></li>');
                } else {
                    $("#em-file_to_upload").append('<span class="alert"> <?= JText::_('UPLOAD_FAILED'); ?> </span>')
                }
            },
            error: error => {
                // handle error
                $("#em-file_to_upload").append('<span class="alert"> <?= JText::_('UPLOAD_FAILED'); ?> </span>')
            },
            async: true,
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            timeout: 60000
        });
    };

    Upload.prototype.progressHandling = event => {
        var percent = 0;
        var position = event.loaded || event.position;
        var total = event.total;
        var progress_bar_id = "";
        if (event.lengthComputable) {
            percent = Math.ceil(position / total * 100);
        }
        // update progressbars classes so it fits your code
        $("#em-progress-wrp .progress-bar").css("width", +percent + "%");
        $("#em-progress-wrp .status").text(percent + "%");
    };
</script>
