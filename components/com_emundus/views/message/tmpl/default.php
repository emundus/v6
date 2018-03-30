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
$itemid 	= JRequest::getVar('Itemid', null, 'GET', 'none',0);
$view 		= JRequest::getVar('view', null, 'GET', 'none',0);
$task 		= JRequest::getVar('task', null, 'GET', 'none',0);
$tmpl 		= JRequest::getVar('tmpl', null, 'GET', 'none',0);

// Load the WYSIWYG editor used to edit the mail body.
$editor = JFactory::getEditor('tinymce');
$mail_body = $editor->display('mail_body', '[NAME], ', '100%', '400', '20', '20', false, 'mail_body', null, null, array('mode' => 'simple'));

$m_messages = new EmundusModelMessages();

// load all of the available messages, categories (to sort messages),attachements, letters.
$message_categories = $m_messages->getAllCategories();
$message_templates 	= $m_messages->getAllMessages();
$setup_attachements = $m_messages->getAttachements();
$setup_letters 		= $m_messages->getLetters();

$email_list = array();

?>

<!-- WYSIWYG Editor -->
<link rel="stylesheet" href="/components/com_jce/editor/libraries/css/editor.min.css" type="text/css">
<script data-cfasync="false" type="text/javascript" src="/media/editors/tinymce/tinymce.min.js"></script>
<script data-cfasync="false" type="text/javascript" src="/media/editors/tinymce/js/tinymce.min.js"></script>
<script data-cfasync="false" type="text/javascript">tinyMCE.init({menubar:false,statusbar: false})</script>

<form id="emailForm"  name="emailForm" style="padding:0px 15px;">
	<div class="em_email_block" id="em_email_block">

		<div class="form-inline row">

			<!-- Dropdown to select the email categories used. -->
			<div class="form-group col-md-6 col-sm-6">
				<label for="select_category" ><?php echo JText::_('SELECT_CATEGORY'); ?></label>
				<select name="select_category" class="form-control" onChange="setCategory(this);">
					<?php if (!$message_categories) :?>
						<option value="%"> <?php echo JText::_('NO_CATEGORIES_FOUND'); ?> </option>
					<?php else: ?>
						<option value="%"> <?php echo JText::_('SELECT_CATEGORY'); ?> </option>
						<?php foreach ($message_categories as $message_category) :?>
							<?php if (!empty($message_category)) :?>
								<option value="<?php echo $message_category; ?>"> <?php echo $message_category; ?></option>
							<?php endif; ?>
						<?php endforeach; ?>
					<?php endif; ?>
				</select>
			</div>

			<!-- Dropdown to select the email template used. -->
			<div class="form-group col-md-6 col-sm-6">
				<label for="select_template" ><?php echo JText::_('SELECT_TEMPLATE'); ?></label>
				<select name="select_template" id="message_template" class="form-control" onChange="getTemplate(this);">
					<?php if (!$message_templates) :?>
						<option value="%"> <?php echo JText::_('NO_TEMPLATES_FOUND'); ?> </option>
					<?php else: ?>
						<option value="%"> <?php echo JText::_('SELECT_TEMPLATE'); ?> </option>
						<?php foreach ($message_templates as $message_template) :?>
							<option value="<?php echo $message_template->id; ?>"> <?php echo $message_template->subject; ?></option>
						<?php endforeach; ?>
					<?php endif; ?>
				</select>
			</div>
		</div>

		<input name="mail_from_id" type="hidden" class="inputbox" id="mail_from_id" value="<?php echo $current_user->id; ?>" /><br>
		<input name="fnums" type="hidden" class="inputbox" id="fnums" value="<?php echo implode(',',$this->fnums); ?>" />

		<!-- Add current user to Bcc -->
		<div class="checkbox">
			<label>
				<input type="checkbox" id="sendUserACopy"> <?php echo JText::_('SEND_COPY_TO_CURRENT_USER'); ?>
			</label>
		</div>

		<div class="form-group">
			<!-- List of users / their emails, gotten from the fnums selected. -->
			<div class="well well-sm">
			<span class='label label-grey'><?php echo JText::_('TO'); ?>:</span>
				<?php foreach ($this->users as $user) : ?>

					<?php if (!empty($user['email']) && !in_array($user['email'], $email_list)) : ?>
						<?php $email_list[] = $user['email']; ?>
						<span class="label label-grey em-email-label">
							<?php echo $user['name'].' <em>&lt;'.$user['email'].'&gt;</em>'; ?>
						</span>
						<input type="hidden" name="ud[]" id="ud" value="<?php echo $user['id'] ?>"/>
					<?php endif; ?>

				<?php endforeach; ?>
			</div>
		</div>
		<div class="form-group">
			<div class="inputbox input-xlarge form-control form-inline">
				<span class='label label-grey' for="mail_from" ><?php echo JText::_('FROM'); ?>:</span>
				<div class="form-group" style="display:inline-block !important;" id="mail_from_name" contenteditable="true"><?php echo $current_user->name; ?> </div>
				<strong> <div class="form-group" style="display:inline-block !important;" id="mail_from" contenteditable="true"><?php echo $current_user->email; ?></div> </strong>
			</div>
		</div>
		<div class="form-group">
			<div class="inputbox input-xlarge form-control form-inline">
				<span class='label label-grey' for="mail_from" ><?php echo JText::_('SUBJECT'); ?>:</span>
				<div class="form-group" style="display:inline-block !important;" id="mail_subject" contenteditable="true"></div>
			</div>

			<!-- Email WYSIWYG -->
			<?php echo $mail_body; ?>
		</div>

		<div class="form-group">
			<br>
			<hr>
		</div>

		<div class="form-inline row">
			<div class="form-group col-md-5">
				<label for="select_attachement_type" ><?php echo JText::_('SELECT_ATTACHEMENT_TYPE'); ?></label>
				<select name="select_attachement_type" id="select_attachement_type" class="form-control" onChange="toggleAttachementType(this);">
					<option value=""> <?php echo JText::_('PLEASE_SELECT'); ?> </option>
					<option value="upload"> <?php echo JText::_('UPLOAD'); ?> </option>
					<option value="candidate_file"> <?php echo JText::_('CANDIDATE_FILE'); ?> </option>
					<option value="setup_letters"> <?php echo JText::_('SETUP_LETTERS'); ?> </option>
				</select>
			</div>

			<div class="form-group col-md-7">
				<div class="input-group">
						<!-- Upload a file from computer -->
						<div class="hidden" id="upload_file">
							<label for="file_to_upload" ><?php echo JText::_('UPLOAD'); ?></label>
							<input type="file" name="file_to_upload" id="file_to_upload">
							<div id="progress-wrp">
								<div class="progress-bar"></div>
								<div class="status">0%</div>
							</div>
						</div>

						<!-- Get a file from setup_attachements -->
						<div class="hidden" id="candidate_file">
							<label for="candidate_file" ><?php echo JText::_('UPLOAD'); ?></label>
							<select id="select_candidate_file" name="candidate_file" class="form-control">
							<?php if (!$setup_attachements) :?>
								<option value="%"> <?php echo JText::_('NO_FILES_FOUND'); ?> </option>
							<?php else: ?>
								<option value="%"> <?php echo JText::_('PLEASE_SELECT'); ?> </option>
								<?php foreach ($setup_attachements as $attachement): ?>
									<option value="<?php echo $attachement->id; ?>"> <?php echo $attachement->value; ?></option>
								<?php endforeach; ?>
							<?php endif; ?>
							</select>
						</div>

						<!-- Get a file from setup_letters -->
						<div class="hidden" id="setup_letters">
							<label for="setup_letters" ><?php echo JText::_('UPLOAD'); ?></label>
							<select id="select_setup_letters" name="setup_letters" class="form-control">
							<?php if (!$setup_letters) :?>
								<option value="%"> <?php echo JText::_('NO_FILES_FOUND'); ?> </option>
							<?php else: ?>
								<option value="%"> <?php echo JText::_('PLEASE_SELECT'); ?> </option>
								<?php foreach ($setup_letters as $letter): ?>
									<option value="<?php echo $letter->id; ?>"> <?php echo $letter->title; ?></option>
								<?php endforeach; ?>
							<?php endif; ?>
							</select>
						</div>
						<span class="input-group-btn">
							<a class="btn btn-primary hidden" type="button" id="uploadButton" style="top:13px;" onClick="addFile();"><?php echo JText::_('ADD_ATTACHMENT'); ?></a>
						</span>
					</div>
				</div>
			</div>
		</div>
		<br>
		<hr>
		<div class="form-group">
			<ul class="list-group" id="attachement-list">
				<!-- Files to be attached will be added here. -->
			</ul>
		</div>
	</div>

	<input type="hidden" name="task" value=""/>
</form>

<script type="text/javascript">

	// Editor loads disabled by default, we apply must toggle it active on page load.
	$(document).ready(function() {
		tinyMCE.execCommand('mceToggleEditor', true, 'mail_body');
	});

	// Loads the template and updates the WYSIWYG editor
	function getTemplate(select) {

		$.ajax({
			type: "POST",
			url : "index.php?option=com_emundus&controller=email&view=email&task=getTemplate",
			data : {
				select : select.value
			},
			success: function (email) {

				email = JSON.parse(email);

				var email_block = document.getElementById("em_email_block");
				$("#mail_subject").text(email.tmpl.subject);
				$("#mail_from").text(email.tmpl.emailfrom);
				$("#mail_from_name").text(email.tmpl.name);
				$("#mail_body").val(email.tmpl.message);
				tinyMCE.execCommand("mceSetContent", false, email.tmpl.message);
				tinyMCE.execCommand("mceRepaint");
			},
			error: function () {
				// handle error
				$("#message_template").append('<span class="alert"> <?php echo JText::_('ERROR'); ?> </span>')
			}
		});

	}

	// Used for toggling the options dipslayed in the message templates dropdown.
	function setCategory(element) {

		if (element.value == "%")
			category = 'all';
		else
			category = element.value;

		$.ajax({
			type: "GET",
			url: "index.php?option=com_emundus&controller=messages&task=setcategory&category="+category,
			success: function (data) {

				data = JSON.parse(data);

				if (data.status) {

					var $el = $("#message_template");
					$('#message_template option:gt(0)').remove();

					$.each(data.templates, function(key,value) {
					$el.append($("<option></option>")
						.attr("value", value.id).text(value.subject));
					});
				} else {
					$("#message_template").append('<span class="alert"> <?php echo JText::_('ERROR'); ?> </span>')
				}
			},
			error: function (error) {
				// handle error
				$("#message_template").append('<span class="alert"> <?php echo JText::_('ERROR'); ?> </span>')
			},

		});

	}

	function SubmitForm() {

		// update the textarea with the WYSIWYG content.
		tinymce.triggerSave();

		// Get all form elements.
		var data = {
			recipients 		: $('#fnums').val(),
			template		: $('#message_template :selected').val(),
			Bcc 			: $('#sendUserACopy').prop('checked'),
			mail_from_name 	: $('#mail_from_name').text(),
			mail_from 		: $('#mail_from').text(),
			mail_subject 	: $('#mail_subject').text(),
			message			: $('#mail_body').val()
		}


		// Attachements object used for sorting the different attachement types.
		var attachements = {
			upload : [],
			candidate_file : [],
			setup_letters : []
		}

		// Looping through the list and sorting attachements based on their type.
		var listItems = $("#attachement-list li");
		listItems.each(function(idx, li) {
			var attachement = $(li);

			if (attachement.hasClass('upload')) {

				attachements.upload.push(attachement.find('.value').text());

			} else if (attachement.hasClass('candidate_file')) {

				attachements.candidate_file.push(attachement.find('.value').text());

			} else if (attachement.hasClass('setup_letters')) {

				attachements.setup_letters.push(attachement.find('.value').text());

			}
		});

		data.attachements = attachements;

		$.ajax({
			type: "POST",
			url: "index.php?option=com_emundus&controller=messages&task=applicantemail",
			data: data,
			success: function (result) {
				$("#em_email_block").append('<span class="alert alert-success"> <?php echo JText::_('EMAIL_SENT'); ?> </span>')
			},
			error : function (error) {
				$("#em_email_block").append('<span class="alert alert-danger"> <?php echo JText::_('SEND_FAILED'); ?> </span>')
			}
		});
	}


	// Used for reseting a File upload input.
	function resetFileInput(e) {
		e.wrap('<form>').closest('form').get(0).reset();
		e.unwrap();
	}


	// Change the attachement type being uploaded.
	function toggleAttachementType(toggle) {

		switch (toggle.value) {

			case 'upload' :
				$('#upload_file').removeClass('hidden')
				$('#candidate_file').addClass('hidden')
				$('#setup_letters').addClass('hidden')
				$('#uploadButton').removeClass('hidden')
			break;

			case 'candidate_file' :
				resetFileInput($('#upload_file'))
				$('#upload_file').addClass('hidden')
				$('#candidate_file').removeClass('hidden')
				$('#setup_letters').addClass('hidden')
				$('#uploadButton').removeClass('hidden')
			break;

			case 'setup_letters' :
				resetFileInput($('#upload_file'))
				$('#upload_file').addClass('hidden')
				$('#candidate_file').addClass('hidden')
				$('#setup_letters').removeClass('hidden')
				$('#uploadButton').removeClass('hidden')
			break;

			default :
				resetFileInput($('#upload_file'))
				$('#upload_file').addClass('hidden')
				$('#candidate_file').addClass('hidden')
				$('#setup_letters').addClass('hidden')
				$('#uploadButton').addClass('hidden')
			break;

		}

	}


	// Add file to the list being attached.
	function addFile() {

		switch ($('#select_attachement_type :selected').val()) {

			case 'upload' :

				// We need to get the file uploaded by the user.
				var file = $("#file_to_upload")[0].files[0];
				var upload = new Upload(file);
				// Verification of style size and type can be done here.
				upload.doUpload();

			break;


			case 'candidate_file' :

				// we just need to note the reference to the setup_attachement file.
				var file = $('#select_candidate_file :selected');

				var alreadyPicked = $('#attachement-list li.candidate_file').find('.value:contains("'+file.val()+'")');

				if (alreadyPicked.text() != '') {

					// Flash the line a certain color to show it's already picked.
					alreadyPicked.parent().attr("style", "background-color: #C5EFF7");
					setTimeout(function(){
						alreadyPicked.parent().attr("style", "");
					}, 500);

				} else {

					// Disable the file from the dropdown.
					file.attr('disabled', 'disabled');
					// Add the file to the list.
					$('#attachement-list').append('<li class="list-group-item candidate_file"><div class="value hidden">'+file.val()+'</div>'+file.text()+'<span class="badge btn-danger" onClick="removeAttachement(this);"><span class="glyphicon glyphicon-remove"></span></span><span class="badge"><span class="glyphicon glyphicon-paperclip"></span></span></li>');

				}


			break;

			case 'setup_letters' :

				// We need to note the reference to the setup_letters file.
				var file = $('#select_setup_letters :selected');

				var alreadyPicked = $('#attachement-list li.setup_letters').find('.value:contains("'+file.val()+'")');

				if (alreadyPicked.text() != '') {

					// Flash the line a certain color to show it's already picked.
					alreadyPicked.parent().attr("style", "background-color: #C5EFF7");
					setTimeout(function(){
						alreadyPicked.parent().attr("style", "");
					}, 500);

				} else {

					// Disable the file from the dropdown.
					file.attr('disabled', 'disabled');
					// Add the file to the list.
					$('#attachement-list').append('<li class="list-group-item setup_letters"><div class="value hidden">'+file.val()+'</div>'+file.text()+'<span class="badge btn-danger" onClick="removeAttachement(this);"><span class="glyphicon glyphicon-remove"></span></span><span class="badge"><span class="glyphicon glyphicon-envelope"></span></span></li>');

				}

			break;

			default :

				// Nothing selected, this case should not happen.
				$("#attachement-list").append('<span class="alert alert-danger"> <?php echo JText::_('ERROR'); ?> </span>')

			break;

		}

	}

	function removeAttachement(element) {
		$(element).parent().remove();
	}


	// Helper function for uploading a file via AJAX.
	var Upload = function (file) {
		this.file = file;
	};

	Upload.prototype.getType = function() {
		return this.file.type;
	};
	Upload.prototype.getSize = function() {
		return this.file.size;
	};
	Upload.prototype.getName = function() {
		return this.file.name;
	};
	Upload.prototype.doUpload = function () {
		var that = this;
		var formData = new FormData();

		// add assoc key values, this will be posts values
		formData.append("file", this.file, this.getName());
		formData.append("upload_file", true);

		$.ajax({
			type: "POST",
			url: "index.php?option=com_emundus&controller=messages&task=uploadfiletosend",
			xhr: function () {
				var myXhr = $.ajaxSettings.xhr();
				if (myXhr.upload) {
					myXhr.upload.addEventListener('progress', that.progressHandling, false);
				}
				return myXhr;
			},
			success: function (data) {
				// your callback here
				$("#progress-wrp").fadeOut();

				data = JSON.parse(data);

				if (data.status) {
					$('#attachement-list').append('<li class="list-group-item upload"><div class="value hidden">'+data.file_path+'</div>'+data.file_name+'<span class="badge btn-danger" onClick="removeAttachement(this);"><span class="glyphicon glyphicon-remove"></span></span><span class="badge"><span class="glyphicon glyphicon-saved"></span></span></li>');
				} else {
					$("#file_to_upload").append('<span class="alert"> <?php echo JText::_('UPLOAD_FAILED'); ?> </span>')
				}
			},
			error: function (error) {
				// handle error
				$("#file_to_upload").append('<span class="alert"> <?php echo JText::_('UPLOAD_FAILED'); ?> </span>')
			},
			async: true,
			data: formData,
			cache: false,
			contentType: false,
			processData: false,
			timeout: 60000
		});
	};

	Upload.prototype.progressHandling = function (event) {
		var percent = 0;
		var position = event.loaded || event.position;
		var total = event.total;
		var progress_bar_id = "#progress-wrp";
		if (event.lengthComputable) {
			percent = Math.ceil(position / total * 100);
		}
		// update progressbars classes so it fits your code
		$(progress_bar_id + " .progress-bar").css("width", +percent + "%");
		$(progress_bar_id + " .status").text(percent + "%");
	};
</script>