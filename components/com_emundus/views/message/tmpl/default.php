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

jimport('joomla.utilities.date');
JHTML::_('behavior.tooltip');
JHTML::_('behavior.modal');


// Get Fnums which are send as a GET in JSON.
$jinput = JFactory::getApplication()->input;
$fnums = $jinput->get('fnums', null, 'RAW');
$fnumsArray = (array) json_decode($fnums);

if (count($fnumsArray) > 0) {
	foreach ($fnumsArray as $key => $value) {
		$fnums_tab[] = $value->fnum;
	}
	$fnums = json_encode($fnums_tab);
}

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

<form id="adminForm" name="adminForm" onSubmit="return OnSubmitForm();" method="POST" >
	<div class="emundusraw">
		<div class="em_email_block" id="em_email_block">

			<div class="form-group">
				<!-- List of users / their emails, gotten from the fnums selected. -->
				<div class="well well-sm">
				<?php foreach ($this->users as $user) : ?>

					<?php if (!empty($user['email']) && !in_array($user['email'], $email_list)) : ?>
						<?php $email_list[] = $user['email']; ?>
						<span class="label label-primary">
							<?php echo $user['name'].'<em>&lt;'.$user['email'].'&gt;</em>'; ?>
						</span>
						<input type="hidden" name="ud[]" value="<?php echo $user['id'].'|'.$user['campaign_id'] ?>"/>
					<?php endif; ?>

				<?php endforeach; ?>
				</div>

				<label>
					<input type="checkbox" id="sendUserACopy"> <?php echo JText::_('SEND_COPY_TO_CURRENT_USER'); ?>
				</label>
			</div>

			<div class="form-inline row">

				<!-- Dropdown to select the email categories used. -->
				<div class="form-group col-md-6">
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
				<div class="form-group col-md-6">
					<label for="select_template" ><?php echo JText::_('SELECT_TEMPLATE'); ?></label>
					<select name="select_template" class="form-control" onChange="getTemplate(this);">
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

			<div class="form-group">
				<hr>
			</div>

			<input name="mail_from_id" type="hidden" class="inputbox" id="mail_from_id" value="<?php echo $current_user->id; ?>" /><br>
			<input name="fnums" type="hidden" class="inputbox" id="fnums" value="<?php echo $fnums; ?>" />


			<div class="form-inline row">
				<!-- Email sender -->
				<div class="form-group col-md-6">
					<label for="mail_from_name" ><?php echo JText::_('EMAIL_FROM'); ?></label>
					<input placeholder="<?php echo JText::_('EMAIL_FROM'); ?>" name="mail_from_name" type="text" class="inputbox input-xlarge form-control" id="mail_from_name" value="<?php echo $current_user->name; ?>" />
				</div>
				<!-- Email sender address -->
				<div class="form-group col-md-6">
					<label for="mail_from" ><?php echo JText::_('EMAIL'); ?></label>
					<input placeholder="<?php echo JText::_('EMAIL'); ?>" name="mail_from" type="text" class="inputbox input-xlarge form-control" id="mail_from" value="<?php echo $current_user->email; ?>" />
				</div>
			</div>

			<!-- Email subject -->
			<div class="form-group">
				<label for="mail_subject" ><?php echo JText::_('SUBJECT'); ?></label>
				<input placeholder="<?php echo JText::_('SUBJECT'); ?>" name="mail_subject" type="text" class="inputbox form-control" id="mail_subject" value="" size="100" style="width: inherit !important;" />
			</div>

			<!-- Email WYSIWYG -->
			<div class="form-group">
				<?php echo $mail_body; ?>
			</div>

			<br>
			<hr>

			<div class="form-inline row">
				<div class="form-group col-md-6">
					<label for="select_attachement_type" ><?php echo JText::_('SELECT_ATTACHEMENT_TYPE'); ?></label>
					<select name="select_attachement_type" id="select_attachement_type" class="form-control" onChange="toggleAttachementType(this);">
						<option value=""> <?php echo JText::_('PLEASE_SELECT'); ?> </option>
						<option value="upload"> <?php echo JText::_('UPLOAD'); ?> </option>
						<option value="candidate_file"> <?php echo JText::_('CANDIDATE_FILE'); ?> </option>
						<option value="setup_letters"> <?php echo JText::_('SETUP_LETTERS'); ?> </option>
					</select>
				</div>

				<div class="form-group col-md-6">
					<!-- Upload a file from computer -->
					<div class="hidden" id="upload_file">
						<label for="file_to_upload" ><?php echo JText::_('UPLOAD'); ?></label>
						<input type="file" name="file_to_upload" id="file_to_upload">
						<style>
							#progress-wrp {
								border: 1px solid #0099CC;
								padding: 1px;
								position: relative;
								height: 30px;
								border-radius: 3px;
								margin: 10px;
								text-align: left;
								background: #fff;
								box-shadow: inset 1px 3px 6px rgba(0, 0, 0, 0.12);
							}
							#progress-wrp .progress-bar{
								height: 100%;
								border-radius: 3px;
								background-color: #f39ac7;
								width: 0;
								box-shadow: inset 1px 1px 10px rgba(0, 0, 0, 0.11);
							}
							#progress-wrp .status{
								top:3px;
								left:50%;
								position:absolute;
								display:inline-block;
								color: #000000;
							}
						</style>
						<div id="progress-wrp">
							<div class="progress-bar"></div>
							<div class="status">0%</div>
						</div>
					</div>

					<!-- Get a file from setup_attachements -->
					<div class="hidden" id="candidate_file">
						<label for="candidate_file" ><?php echo JText::_('UPLOAD'); ?></label>
						<select name="candidate_file" class="form-control">
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
						<select name="setup_letters" class="form-control">
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
				</div>
			</div>
			<button class="btn btn-primary hidden" id="uploadButton" onClick="addFile();"><?php echo JText::_('ADD_FILE'); ?></button>
			<div class="form-group">
				<ul class="list-group" id="attachement-list">
					<!-- Files to be attached will be added here. -->
				</ul>
			</div>
		</div>
		<input class="btn btn-large btn-success" type="submit" name="applicant_email" value="<?php echo JText::_('SEND_CUSTOM_EMAIL'); ?>" >
		<script>
			$(document).on("click", "input[type='submit']", function() {
				if ($("#mail_subject").val() == "") {
					$("#mail_subject").css("border", "2px solid red");
					return false;
				} else
					document.pressed = this.name;
				});
		</script>

		<script> <?php echo EmundusHelperJavascript::getTemplate(); ?></script>
		<?php // TODO: Add EmundusHelperJavascript::setCategory() ?>

	</div>
	<input type="hidden" name="task" value=""/>
</form>

<script type="text/javascript">
	function OnSubmitForm() {
		if (typeof document.pressed !== "undefined") {

			document.adminForm.task.value = "";
			var button_name = document.pressed.split("|");

			switch (button_name[0]) {

				case 'expert':
				break;

				case 'applicant_email':
					document.adminForm.task.value = "applicantemail";
					document.adminForm.action = "index.php?option=com_emundus&view=files&controller=files&Itemid=<?php echo $itemid; ?>&task=applicantemail";
				break;

				case 'group_email':
					document.adminForm.task.value = "groupmail";
					document.adminForm.action ="index.php?option=com_emundus&view=files&controller=files&Itemid=<?php echo $itemid; ?>&task=groupmail";
				break;

				default:
					return false;

			}
			return true;
		}
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

			break;

			case 'setup_letters' :

				// We need to note the reference to the setup_letters file.

			break;

			default :

				// Nothing selected, this case should not happen.

			break;

		}

		//TODO: Append a <li class="list-group-item"><span class="badge">14</span>Cras justo odio</li>
		// Containing an ID and file info that allows the AJAX to know what to do.
		// the span badge element can contain a fontawesome icon that changes based on the type of item.

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
			url: "script",
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
				$('#attachement-list').append('<li class="list-group-item">hello<span class="badge">14</span></li>');
			},
			error: function (error) {
				// handle error
				$("#file_to_upload").append('<span class=alert> <?php echo JText::_('UPLOAD_FAILED'); ?> </span>')
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