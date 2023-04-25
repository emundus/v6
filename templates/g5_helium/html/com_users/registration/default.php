<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @since		1.6
 */

defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.noframes');

$app        = JFactory::getApplication();
$template   = $app->getTemplate();
$db         = JFactory::getDBO();

require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'campaign.php');

include_once(JPATH_BASE.'/components/com_emundus/models/emails.php');
include_once(JPATH_BASE.'/components/com_emundus/models/users.php');


$m_campaign = new EmundusModelCampaign;

$lang->load('tpl_'.$template, JPATH_THEMES.DS.$template);
//$this->form->reset( true );
$this->form->loadFile(dirname(__FILE__) . DS . "registration.xml");
$jform = $app->getUserState('com_users.registration.data');

/**check if warning and send a mail to @ email */
$messages = $app->getMessageQueue();
$errors = false;
foreach ($messages as $message) {
  	if ($message['message'] == JText::_("COM_USERS_REGISTER_EMAIL1_MESSAGE")) {
		try {
			$chars 	= "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?";
			//$passwd_md5 = md5($passwd);


			$m_users = new EmundusModelUsers;
			$user = $m_users->getUserByEmail($jform["email2"]);
			$uid = (int) $user[0]->id;

			$emails = new EmundusModelEmails;
			$mailer = JFactory::getMailer();
			$email = $emails->getEmail("account_already_exists");
			$post = array();
			$tags = $emails->setTags($uid, $post, null, '', $email->emailfrom.$email->name.$email->subject.$email->message);

			//var_dump($jform);


			$from = preg_replace($tags['patterns'], $tags['replacements'], $email->emailfrom);
			$fromname = preg_replace($tags['patterns'], $tags['replacements'], $email->name);
			$to = $jform["email2"];
			$subject = preg_replace($tags['patterns'], $tags['replacements'], $email->subject);
			$body = preg_replace($tags['patterns'], $tags['replacements'], $email->message);
			$body = $emails->setTagsFabrik($body);



			$email_from_sys = $app->getCfg('mailfrom');


			// If the email sender has the same domain as the system sender address.
			if (!empty($email->emailfrom) && substr(strrchr($email->emailfrom, "@"), 1) === substr(strrchr($email_from_sys, "@"), 1))
				$mail_from_address = $email->emailfrom;
			else
				$mail_from_address = $email_from_sys;

			// Set sender
			$sender = [
				$mail_from_address,
				$fromname
			];

			$mailer->setSender($sender);
			$mailer->addReplyTo($from, $fromname);
			$mailer->addRecipient($to);
			$mailer->setSubject($subject);
			$mailer->isHTML(true);
			$mailer->Encoding = 'base64';
			$mailer->setBody($body);
			//var_dump($body);
			$send = $mailer->Send();
			if ($send !== true) {
				$res = false;
				$msg = JText::_('COM_EMUNDUS_ERROR_CANNOT_SEND_EMAIL').' : '.$send->__toString();
				JLog::add($send->__toString(), JLog::ERROR, 'com_emundus.email');
			} else {
				$message = array(
					'user_id_to' => $uid,
					'subject' => $subject,
					'message' => $body
				);
				$emails->logEmail($message);

				$res = true;
				//save the profile/program
				$profile = $m_users->getProfileIDByCampaignID((int)$jform["emundus_profile"]['campaign']);

				$query="INSERT INTO `#__emundus_users_profiles` VALUES ('','".date('Y-m-d H:i:s')."',".$uid.",".$profile.",'','')";
				$db->setQuery( $query );
				$db->Query();

				$query = 'SELECT `acl_aro_groups` FROM `#__emundus_setup_profiles` WHERE id='.(int)$profile;
				$db->setQuery($query);
				$group = $db->loadColumn();

                $group_add = JUserHelper::addUserToGroup($uid,$group[0]);

				$campaign_id = (int)$jform["emundus_profile"]['campaign'];
				if (isset($campaign_id) && !empty($campaign_id)) {
                    $query = 'INSERT INTO #__emundus_campaign_candidature (`applicant_id`, `campaign_id`, `fnum`) VALUES ('.$uid.','.$campaign_id.', CONCAT(DATE_FORMAT(NOW(),\'%Y%m%d%H%i%s\'),LPAD(`campaign_id`, 7, \'0\'),LPAD(`applicant_id`, 7, \'0\')))';
                    $db->setQuery($query);
                    try {
                        $db->Query();
                    } catch (Exception $e) {
                       	error_log($e->getMessage(), 0);
            			return false;
                    }
                }
				$msg = JText::_('COM_EMUNDUS_EMAIL_SENT');

			}
			$app->enqueueMessage(JText::_('COM_USERS_REGISTER_CHECK_YOUR_MAIL'), 'notice');
			//$app->redirect('index.php');

			//echo json_encode((object)array('status' => $res, 'msg' => $msg));

			/***********************end mail sending ************************* */
		}
		catch(Exception $e){
			error_log($e->getMessage(), 0);
            return false;
		}
   }
}
//email sending

/*if (!EmundusHelperAccess::isAdministrator($current_user->id) && !EmundusHelperAccess::isCoordinator($current_user->id)) {
	echo json_encode((object)array('status' => false));
	exit;
}*/



$course = JFactory::getApplication()->input->get('course', null, 'GET', null, 0);
$cid 	= JFactory::getApplication()->input->get('cid', null, 'GET', null, 0);

if (!empty($course) && empty($cid)) {
	$campaigns = $m_campaign->getCampaignsByCourse($course);
	$campaign_id = $campaigns['id'];
} elseif (!empty($cid)) {
	$campaigns = $m_campaign->getCampaignByID($cid);
	$campaign_id = $campaigns['id'];
} else {
	$campaigns = $m_campaign->getAllowedCampaign();
}

if ((count(@$campaign_id) == 0 && (!empty($course) && !empty($cid))) || count($campaigns) == 0) {
	$app->enqueueMessage(JText::_('EMUNDUS_NO_CAMPAIGN'), 'error');
	JLog::add('No available campaign', JLog::ERROR, 'com_emundus');
} else {
?>
<style> #jform_name {border:solid 0px #FFF;} </style>
<div class="box">
	<div class="box_content"><?php echo JText::_("EMUNDUS_REGISTRATION_INSTRUCTIONS"); ?></div>
</div>

<div class="registration<?php echo $this->pageclass_sfx?>">
<?php if ($this->params->get('show_page_heading')) : ?>
	<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
<?php endif; ?>

	<form id="member-registration" action="<?php echo JRoute::_('index.php?option=com_users&task=registration.register&course='.$course); ?>" method="post" class="form-validate" autocomplete="off">
<?php foreach ($this->form->getFieldsets() as $fieldset): // Iterate through the form fieldsets and display each one.?>
	<?php $fields = $this->form->getFieldset($fieldset->name); ?>
	<?php if (count($fields)):?>
		<?php if (isset($fieldset->label)):// If the fieldset has a label set, display it as the legend.
		?>
        <table class="em-register-table">
			<legend><?php echo JText::_($fieldset->label);?></legend>
		<?php endif;?>


        <tr class="em-checkBox-tr">
            <?php echo $this->form->getLabel('spacer'); ?>
        </tr>

        <tr class="em-checkBox-tr">
            <td class="em-checkBox-label">
                <?php echo $this->form->getLabel('firstname', 'emundus_profile'); ?>
            </td>
            <td class="em-input">
                <?php echo $this->form->getInput('firstname', 'emundus_profile'); ?>
            </td>
        </tr>

        <tr class="em-checkBox-tr">
            <td class="em-checkBox-label">
                <?php echo $this->form->getLabel('lastname', 'emundus_profile'); ?>
            </td>
            <td class="em-input">
                <?php echo $this->form->getInput('lastname', 'emundus_profile'); ?>
            </td>
        </tr>


		<?php foreach($fields as $field):?>
                <?php if ($field->name == 'jform[emundus_profile][lastname]' || $field->name == 'jform[emundus_profile][firstname]' || $field->name == 'jform[spacer]') :?>
                    <?php continue; ?>
                <?php else: ?>
				<?php if ($field->hidden):?>
                    <?php echo $field->input;?>
				<?php else:?>
					<?php if ($field->type == 'Checkbox'):?>

						<tr class="em-checkBox-tr">
							<td class="em-checkBox-input"><?php echo $field->input; ?></td>
							<?php if ($field->name == 'jform[emundus_profile][cgu]'):?>
								<td class="em-checkBox-label"><?php echo '<i>'.$field->label.'</i>'; ?></td>
							<?php else:?>
								<td class="em-checkBox-label"><?php echo '<i>'.$field->label.'</i>'; ?></td>
							<?php endif;?>

						</tr>
					<?php else:?>

                            <tr class="em-control-group">
                                <td class="em-label">
                                    <?php echo $field->label; ?>
                                    <?php if (!empty($field->required) && $field->type !== 'Spacer') : ?>
                                        <span class="optional"><?php echo JText::_('COM_USERS_OPTIONAL'); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="em-input">
                                    <?php echo $field->input; ?>
                                </td>
                            </tr>

					<?php endif;?>
				<?php endif;?>
            <?php endif;?>
		<?php endforeach;?>
		</table>
	<?php endif;?>
<?php endforeach;?>
		<div>
			<button type="submit" class="validate"><?php echo JText::_('JREGISTER');?></button>
			<?php echo JText::_('COM_USERS_OR');?>
			<a href="/" title="<?php echo JText::_('JCANCEL');?>"><?php echo JText::_('JCANCEL');?></a>
			<input type="hidden" name="option" value="com_users" />
			<input type="hidden" name="course" value="<?php echo $course; ?>" />
			<input type="hidden" name="cid" value="<?php echo $cid; ?>" />
			<input type="hidden" name="task" value="registration.register" />
			<?php echo JHtml::_('form.token');?>
		</div>
	</form>
</div>

<div class="box">
	<h2><?php echo JText::_("CONTACT_US_FOR_TECHNICAL_ISSUES"); ?></h2>
	<div class="box_content"></div>
</div>

<?php
}
$HTTP_USER_AGENT = $_SERVER['HTTP_USER_AGENT'];
//Detection du browser
if (preg_match('/Safari/', $HTTP_USER_AGENT) && !preg_match('/Konqueror/', $HTTP_USER_AGENT))
	$browser='Safari';
elseif (preg_match('/msie/', $HTTP_USER_AGENT) && !preg_match('/opera/', $HTTP_USER_AGENT))
	$browser='IE';
elseif (preg_match('/opera/', $HTTP_USER_AGENT))
	$browser='Opera';
elseif (preg_match('/Mozilla/', $HTTP_USER_AGENT))
	$browser='FireFox';
else
	$browser=$HTTP_USER_AGENT;

//var_dump($jform);
?>


<script>
    function mycgu() {
        window.location="index.php?option=com_content&view=article&id=2";
    }

    var courseInURL = "<?php echo (isset($course)) ? 'true' : 'null'; ?>";
    var cidInUrl 	= "<?php echo (isset($cid)) ? 'true' : 'null' ?>";

    if (courseInURL == 'true' && cidInUrl == 'true') {
        var campaign = document.getElementById('jform_emundus_profile_campaign');
        if (campaign.options.length === 2) {
            var cText = campaign.options[1].text;
            campaign.selectedIndex = 1;
            campaign.style.display = 'none';

            var newItem = document.createElement("p");       // Create a <li> node
            var textnode = document.createTextNode(cText);  // Create a text node
            newItem.appendChild(textnode);

            campaign.parentNode.insertBefore(newItem, campaign);
        }
    }


    function validateEmail(email) {
        var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(email);
    }

    function check_field() {
		
	campaign_id = "<?php echo (isset($campaign_id)) ? $campaign_id : ''; ?>";
        campaign = document.getElementById("jform_emundus_profile_campaign");
        if (campaign_id != "") {
            for (var i=0 ; i<campaign.options.length ; ++i) {
                if (campaign.options[i].value == campaign_id)
                    campaign.options[i].selected = true;
			}
			
        } else { 
			
			campaign.options[0].selected = true; }
        	var form_values = new Array();
		
		<?php
        if (!empty($jform)) {
            foreach ($jform as $key => $value) {

                if (is_array($value)) {
                    foreach($value as $k => $v) {
						echo 'form_values["jform_'.$key.'_'.$k.'"] = "'.$v.'"; ';
					}
                } else {
                    echo 'form_values["jform_'.$key.'"] = "'.$value.'"; ';
				}
				
            }
        }
        ?>

        firstname = document.getElementById("jform_emundus_profile_firstname");
        lastname = document.getElementById("jform_emundus_profile_lastname");

		<?php $i = 0; foreach ($fields as $field) { ?>
			
		field = document.getElementsByName("<?php echo $field->name; ?>");
		
        if (field[0] != undefined) {
			
			if (form_values[field[0].id] != undefined)
                field[0].value = form_values[field[0].id];
			
				if (field[0].value == "" && "<?php echo $browser; ?>" != "IE")
                field[0].setStyles({backgroundColor: '#F7F2B2'});
		
				field[0].onblur = function() {
		
					if ("<?php echo $browser; ?>" != "IE")
		    			this.setStyles({backgroundColor: '#fff'});
		
					document.getElementById("jform_name").value = firstname.value + ' ' + lastname.value;
					document.getElementById("jform_email1").value = document.getElementById("jform_username").value;
			
				}

            	if ("<?php echo $browser; ?>" != "IE") {
                	field[0].onchange = function(){this.setStyles({backgroundColor: '#fff'});}
                	field[0].onkeyup = function(){this.setStyles({backgroundColor: '#fff'});}
            	}
        	}
        <?php } ?>
        username = document.getElementById("jform_username");
        passwd1 = document.getElementById("jform_password1");
        passwd2 = document.getElementById("jform_password2");

        //username.onkeyup = function() { this.value = this.value.replace(/[^a-z0-9]/gi, '').toLowerCase(); };

        passwd1.onchange = function() { 
			if (passwd1.value.length < 4) 
				jQuery('em_msg_jform[password1]').innerHTML = "<?php echo JText::_('COM_USERS_DESIRED_PASSWORD');?>"; 
			else 
				jQuery('em_msg_jform[password1]').innerHTML = ""; 
		};

        passwd2.onchange = function() { 
			if (passwd1.value != this.value) 
				jQuery('em_msg_jform[password2]').innerHTML = "<?php echo JText::_('COM_USERS_FIELD_RESET_PASSWORD1_MESSAGE');?>";
			else 
				jQuery('em_msg_jform[password2]').innerHTML = ""; 
		};

        email1 = document.getElementById("jform_email1");
        email1.onchange = function() {

            if (!validateEmail(this.value))
                jQuery('em_msg_jform[email1]').innerHTML = "<?php echo JText::_('COM_USERS_INVALID_EMAIL');?>";
            else
				jQuery('em_msg_jform[email1]').innerHTML = "";
				
            username.value = this.value;
        };

        email2 = document.getElementById("jform_email2");
        email2.onchange = function() { 
			if (jform_email1.value != this.value) 
				jQuery('em_msg_jform[email2]').innerHTML = "<?php echo JText::_('COM_USERS_PROFILE_EMAIL2_MESSAGE');?>"; 
			else 
				jQuery('em_msg_jform[email2]').innerHTML = ""; 
		};

		campaign = document.getElementById("jform_emundus_profile_campaign");
        campaign.onclick = function() { 
			if (campaign.value == "") 
				jQuery('em_msg_jform[emundus_profile][campaign]').innerHTML = "<?php echo JText::_('COM_USERS_PROFILE_CAMPAIGN_MESSAGE');?>"; 
			else 
				jQuery('em_msg_jform[emundus_profile][campaign]').innerHTML = ""; 
		};


    }
    check_field();


</script>
