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

// load all of the available messages
$AllMessage_category = $m_messages->getAllCategories();
$AllMessage_template = $m_messages->getAllMessages();

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
						<?php if (!$AllMessage_category) :?>
							<option value="%"> <?php echo JText::_('NO_CATEGORIES_FOUND'); ?> </option>
						<?php else: ?>
							<option value="%"> <?php echo JText::_('SELECT_CATEGORY'); ?> </option>
							<?php foreach ($AllMessage_category as $message_category) :?>
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
						<?php if (!$AllMessage_template) :?>
							<option value="%"> <?php echo JText::_('NO_TEMPLATES_FOUND'); ?> </option>
						<?php else: ?>
							<option value="%"> <?php echo JText::_('SELECT_TEMPLATE'); ?> </option>
							<?php foreach ($AllMessage_template as $message_template) :?>
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

			<div class="form-inline row">
				<div class="form-group col-md-6">
					<label for="select_attachement_type" ><?php echo JText::_('SELECT_ATTACHEMENT_TYPE'); ?></label>
					<select name="select_attachement_type" class="form-control" onChange="toggleAttachementType(this);">
						<option value=""> <?php echo JText::_('PLEASE_SELECT'); ?> </option>
						<option value="upload"> <?php echo JText::_('UPLOAD'); ?> </option>
						<option value="candidate_file"> <?php echo JText::_('CANDIDATE_FILE'); ?> </option>
						<option value="setup_letters"> <?php echo JText::_('SETUP_LETTERS'); ?> </option>
					</select>
				</div>


			</div>

			<input class="btn btn-large btn-success" type="submit" name="applicant_email" value="<?php echo JText::_('SEND_CUSTOM_EMAIL'); ?>" >

		</div>
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
</script>