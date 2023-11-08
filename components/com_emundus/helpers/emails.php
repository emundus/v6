<?php
/**
 * @version        $Id: email.php
 * @package        Joomla
 * @subpackage     Emundus
 * @copyright      Copyright (C) 2019 eMundus. All rights reserved.
 * @license        GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.helper');

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

/**
 * Content Component Query Helper
 *
 * @static
 * @package        Joomla
 * @subpackage     Helper
 * @since          1.5
 */
class EmundusHelperEmails
{

	function createEmailBlock($params, $users = null)
	{
		$app          = Factory::getApplication();
		$current_user = $app->getIdentity();

		$jinput = Factory::getApplication()->input;
		$itemid = $jinput->get('Itemid', null, 'INT');
		$fnums  = $jinput->get('fnums', null, 'RAW');

		$fnumsArray = (array) json_decode($fnums);
		if (count($fnumsArray) > 0) {
			$fnums_tab = array();

			foreach ($fnumsArray as $value) {
				if ($value->fnum !== 'em-check-all') {
					$fnums_tab[] = $value->fnum;
				}
			}
			$fnums = json_encode($fnums_tab);
		}

		$email = '<div class="em_email_block" id="em_email_block">
					<input placeholder="' . JText::_('NAME_FROM') . '" name="mail_from_name" type="text" class="inputbox input-xlarge" id="mail_from_name" style="margin-bottom: 16px" value="' . $current_user->name . '" />
					<input placeholder="' . JText::_('COM_EMUNDUS_MAILS_EMAIL_FROM') . '" name="mail_from" type="text" class="inputbox input-xlarge" style="margin-bottom: 16px" id="mail_from" value="' . $current_user->email . '" />
					<input name="mail_from_id" type="hidden" class="inputbox" id="mail_from_id" value="' . $current_user->id . '" /><br>';

		if (in_array('default', $params)) {
			$email .= '<fieldset class="em_email_block-fieldset">
				<legend>
					<span class="editlinktip hasTip" title="' . JText::_('COM_EMUNDUS_GROUPS_EMAIL_ASSESSORS_DEFAULT') . '::' . JText::_('COM_EMUNDUS_GROUPS_EMAIL_ASSESSORS_DEFAULT_TIP') . '">
						<img src="' . JURI::base() . 'media/com_emundus/images/icones/mail_replayall_22x22.png" alt="' . JText::_('COM_EMUNDUS_GROUPS_EMAIL_ASSESSORS_DEFAULT') . '"/>' . JText::_('COM_EMUNDUS_GROUPS_EMAIL_ASSESSORS_DEFAULT') . '
					</span>
				</legend>
				<div><input type="submit" class="btn btn-large btn-success" name="default_email" value="' . JText::_('COM_EMUNDUS_EMAILS_SEND_DEFAULT_EMAIL') . '" ></div>
			</fieldset>';
		}

		if (in_array('groups', $params)) {
			$default_template = EmundusHelperEmails::getEmail('assessors_set');
			$editor           = JFactory::getEditor('tinymce');
			$params           = array('mode' => 'simple');
			$mail_body        = $editor->display('mail_body', $default_template->message, '100%', '400', '20', '20', false, 'mail_body', null, null, $params);
			$email            .= '<input name="fnums" type="hidden" class="inputbox" id="fnums" value=\'' . $fnums . '\' />';

			$current_group = $jinput->get('groups', null, 'INT');
			$all_groups    = EmundusHelperFilters::getGroups();

			$email .= '<select name="mail_group[]" id="mail_group" multiple="multiple" size="6">
						<option value=""> ' . JText::_('COM_EMUNDUS_GROUPS_PLEASE_SELECT_GROUP') . ' </option>';
			foreach ($all_groups as $groups) {
				$email .= '<option value="' . $groups->id . '"';
				if ($current_group == $groups->id) $email .= ' selected';
				$email .= '>' . $groups->label . '</option>';
			}
			$email .= '</select>';
			$email .= '<script>$(document).ready(function() {$("#mail_group").chosen({width: "100%"}); })</script>';

			$AllEmail_template = EmundusHelperEmails::getAllEmail(2);
			$email             .= '<select name="select_template" onChange="getTemplate(this);">
				<option value="%">-- ' . JText::_('COM_EMUNDUS_EMAILS_SELECT_TEMPLATE') . ' --</option>';
			foreach ($AllEmail_template as $email_template) {
				$email .= '<option value="' . $email_template->id . '">' . $email_template->subject . '</option>';
			}
			$email .= '</select>';
			$email .= ' <input placeholder="' . JText::_('COM_EMUNDUS_EMAILS_SUBJECT') . '" name="mail_subject" type="text" class="inputbox" id="mail_subject" value="' . $default_template->subject . '" size="100" style="width: inherit !important;" />';
			$email .= $mail_body . '<input class="btn btn-large btn-success" type="submit" name="group_email" value="' . JText::_('COM_EMUNDUS_EMAILS_SEND_CUSTOM_EMAIL') . '" >';
		}

		if (in_array('applicants', $params)) {
			$editor    = JFactory::getEditor('tinymce');
			$params    = array('mode' => 'simple');
			$mail_body = $editor->display('mail_body', '[NAME], ', '100%', '400', '20', '20', false, 'mail_body', null, null, $params);
			$email     .= '<input name="fnums" type="hidden" class="inputbox" id="fnums" value=\'' . $fnums . '\' />';

			if (is_null($users)) {
				$email             .= '<label for="select_template">' . JText::_('COM_EMUNDUS_MAILS_TEMPLATE') . '</label>';
				$AllEmail_template = EmundusHelperEmails::getAllEmail(2);
				$email             .= '<select name="select_template" onChange="getTemplate(this);">
					<option value="%">' . JText::_('COM_EMUNDUS_EMAILS_SELECT_TEMPLATE') . '</option>';
				foreach ($AllEmail_template as $email_template) {
					$email .= '<option value="' . $email_template->id . '">' . $email_template->subject . '</option>';
				}
				$email .= '</select>';
				$email .= '<input placeholder="' . JText::_('COM_EMUNDUS_EMAILS_SUBJECT') . '" name="mail_subject" type="text" class="inputbox" id="mail_subject" value="" size="100" style="width: inherit !important;" />';
				$email .= $mail_body . '<input class="btn btn-large btn-success" type="submit" name="applicant_email" value="' . JText::_('COM_EMUNDUS_EMAILS_SEND_CUSTOM_EMAIL') . '" >';

			}
			else {
				$email      .= '<div class="well well-sm">';
				$email_list = array();
				foreach ($users as $user) {
					if (!empty($user['email']) && !in_array($user['email'], $email_list)) {
						$email_list[] = $user['email'];
						$email        .= '<span class="label label-primary">';
						$email        .= $user['name'] . ' <em>&lt;' . $user['email'] . '&gt;</em>, ';
						$email        .= '</span> ';
						$email        .= '<input type="hidden" name="ud[]" value="' . $user['id'] . '|' . $user['campaign_id'] . '"/> ';
					}
				}
				$email .= '</div>';

				$AllEmail_template = EmundusHelperEmails::getAllEmail(2);
				$email             .= '<select name="select_template" onChange="getTemplate(this);">
					<option value="%">-- ' . JText::_('COM_EMUNDUS_EMAILS_SELECT_TEMPLATE') . ' --</option>';
				foreach ($AllEmail_template as $email_template) {
					$email .= '<option value="' . $email_template->id . '">' . $email_template->subject . '</option>';
				}
				$email .= '</select>';
				$email .= ' <input placeholder="' . JText::_('COM_EMUNDUS_EMAILS_SUBJECT') . '" name="mail_subject" type="text" class="inputbox" id="mail_subject" value="" size="100" style="width: inherit !important;" />';
				$email .= $mail_body . '<div><input class="btn btn-large btn-success" type="submit" name="applicant_email" value="' . JText::_('COM_EMUNDUS_EMAILS_SEND_CUSTOM_EMAIL') . '" ></div>';
			}
		}

		if (in_array('evaluators', $params)) {

			$default_template = EmundusHelperEmails::getEmail('assessors_set');
			$editor           = Factory::getEditor('tinymce');
			$params           = array('mode' => 'simple');
			$mail_body        = $editor->display('mail_body', $default_template->message, '100%', '400', '20', '20', false, 'mail_body', null, null, $params);
			$email            .= '<input name="fnums" type="hidden" class="inputbox" id="fnums" value=\'' . $fnums . '\' />';

			if (is_null($users)) {

				$email             = '<input type="text" name="ud[]" value=""/> ';
				$AllEmail_template = EmundusHelperEmails::getAllEmail(2);
				$email             .= '<select name="select_template" onChange="getTemplate(this);">
					<option value="%">-- ' . JText::_('COM_EMUNDUS_EMAILS_SELECT_TEMPLATE') . ' --</option>';
				foreach ($AllEmail_template as $email_template) {
					$email .= '<option value="' . $email_template->id . '">' . $email_template->subject . '</option>';
				}
				$email .= '</select>';

				$email .= '<input placeholder="' . JText::_('COM_EMUNDUS_EMAILS_SUBJECT') . '" name="mail_subject" type="text" class="inputbox" id="mail_subject" value="' . $default_template->subject . '" size="100" style="width: inherit !important;" />';
				$email .= '<input placeholder="' . JText::_('EMAIL_TO') . '" type="text" name="ud[]" value=""/> ';
				$email .= $mail_body . '<input type="submit" class="btn btn-large btn-success" name="applicant_email" value="' . JText::_('COM_EMUNDUS_EMAILS_SEND_CUSTOM_EMAIL') . '" >';
			}
			else {
				foreach ($users as $user) {
					$email_list = array();
					if (!empty($user['email']) && !in_array($user['email'], $email_list)) {
						$email_list[] = $user['email'];
						$email        .= '<span class="label label-primary">';
						$email        .= strtoupper($user['last_name']) . ' ' . $user['first_name'] . ' <em>' . $user['email'] . '</em>';
						$email        .= '</span> ';
						$email        .= '<input type="hidden" name="ud[]" value="' . $user['id'] . '|' . $user['campaign_id'] . '"/> ';
					}
				}

				$AllEmail_template = EmundusHelperEmails::getAllEmail(2);
				$email             .= '<br><select name="select_template" onChange="getTemplate(this);">
				<option value="%">-- ' . JText::_('COM_EMUNDUS_EMAILS_SELECT_TEMPLATE') . ' --</option>';
				foreach ($AllEmail_template as $email_template) {
					$email .= '<option value="' . $email_template->id . '">' . $email_template->subject . '</option>';
				}
				$email .= '</select>';
				$email .= '<input placeholder="' . JText::_('COM_EMUNDUS_EMAILS_SUBJECT') . '" name="mail_subject" type="text" class="inputbox" id="mail_subject" value="' . $default_template->subject . '" size="100" style="width: inherit !important;" />';

				$email .= $mail_body . '<input type="submit" class="btn btn-large btn-success" name="applicant_email" value="' . JText::_('COM_EMUNDUS_EMAILS_SEND_CUSTOM_EMAIL') . '" >';
			}
		}

		if (in_array('evaluation_result', $params)) {
			$editor    = Factory::getEditor('tinymce');
			$params    = array('mode' => 'simple');
			$mail_body = $editor->display('mail_body', '[NAME], ', '100%', '400', '20', '20', false, 'mail_body', null, null, $params);
			$email     .= '<input name="fnums" type="hidden" class="inputbox" id="fnums" value=\'' . $fnums . '\' />';

			$student_id  = $jinput->getInt('jos_emundus_evaluations___student_id');
			$campaign_id = $jinput->getInt('jos_emundus_evaluations___campaign_id');

			$applicant = Factory::getUser($student_id);

			$email .= '<fieldset>
				<legend>
					<span class="editlinktip hasTip" title="' . JText::_('COM_EMUNDUS_EMAILS_EMAIL_APPLICATION_RESULT') . '::' . JText::_('COM_EMUNDUS_EMAILS_EMAIL_APPLICATION_RESULT_TIP') . '">
						<img src="' . JURI::base() . 'media/com_emundus/images/icones/mail_replay_22x22.png" alt="' . JText::_('COM_EMUNDUS_EMAILS_EMAIL_TO') . '"/> ' . JText::_('COM_EMUNDUS_EMAILS_EMAIL_TO') . ' ' . $applicant->name . ' &bull; <i>' . $applicant->email . '</i>
					</span>
				</legend>
				<div>';
			$email .= '
					<input name="mail_subject" type="text" class="inputbox" id="mail_subject" value="" size="100" style="width: inherit !important;" />
					<input name="mail_to" type="hidden" class="inputbox input-xlarge" id="mail_to" value="' . $applicant->id . '" />
					<input name="campaign_id" type="hidden" class="inputbox" id="campaign_id" value="' . $campaign_id . '" size="100" />
				</div>' . $mail_body . '
				</p>
					<input name="mail_attachments" type="hidden" class="inputbox" id="mail_attachments" value="" />
					<input name="mail_type" type="hidden" class="inputbox" id="mail_type" value="evaluation_result" />
				<p><div><input class="btn btn-large btn-success" type="submit" name="evaluation_result_email" value="' . JText::_('COM_EMUNDUS_EMAILS_SEND_CUSTOM_EMAIL') . '" ></div></p>
			</fieldset>';

			$email .= '<script>
			function getXMLHttpRequest() {
				var xhr = null;

				if (window.XMLHttpRequest || window.ActiveXObject) {
					if (window.ActiveXObject) {
						try {
							xhr = new ActiveXObject("Msxml2.XMLHTTP");
						} catch(e) {
							xhr = new ActiveXObject("Microsoft.XMLHTTP");
						}
					} else {
						xhr = new XMLHttpRequest();
					}
				} else {
					alert("Votre navigateur ne supporte pas l\'objet XMLHTTPRequest...");
					return null;
				}

				return xhr;
			}
			function deleteAttachment(id) {
				var xhr = getXMLHttpRequest();
				xhr.onreadystatechange = function() {
					if (xhr.readyState == 4 && (xhr.status == 200 || xhr.status == 0)) {
						if (xhr.responseText != "SQL Error") {
							$("em_dl_"+id).innerHTML = "";
							document.getElementById("em_dl_"+id).style.visibility="hidden";
						} else {
							alert(xhr.responseText);
						}
					}
				};
				xhr.open("GET", "/index.php?option=com_emundus&controller=application&format=raw&task=delete_attachment&Itemid=' . $itemid . '&id="+id, true);
				xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
				xhr.send("&id="+id);
			} </script>';
		}

		if (in_array('expert', $params)) {

			$mail_body = '<textarea name="mail_body" id="mail_body">[NAME], </textarea>';

			$email .= '<div>';

			$AllEmail_template = EmundusHelperEmails::getAllEmail(2);
			$email             .= '<select name="select_template" onChange="getTemplate(this);" style="margin-bottom: 16px">
						<option value="%">-- ' . JText::_('COM_EMUNDUS_EMAILS_SELECT_TEMPLATE') . ' --</option>';
			foreach ($AllEmail_template as $email_template) {
				$email .= '<option value="' . $email_template->id . '">' . $email_template->subject . '</option>';
			}
			$email .= '</select>
						<input placeholder="' . JText::_('COM_EMUNDUS_EMAILS_SUBJECT') . '" name="mail_subject" type="text" class="inputbox" id="mail_subject" value="" size="100" style="width: inherit !important;margin-bottom: 16px" />
						<select name="mail_to[]" type="text" class="inputbox" id="mail_to" size="100" style="width: 100% !important;margin-bottom: 16px" multiple="multiple">
							<option value="">' . JText::_('COM_EMUNDUS_EMAILS_EMAIL_TO') . '</option>';
			foreach ($users as $expert) {
				$email .= '<option value="' . $expert['email'] . '">' . $expert['first_name'] . ' ' . $expert['last_name'] . ((!empty($expert['group'])) ? ' (' . JText::_($expert['group']) . ')' : '') . '</option>';
			}
			$email .= '</select>
						<input name="fnums" type="hidden" class="inputbox" id="fnums" value=\'' . $fnums . '\' />
						<input name="delete_attachment" type="hidden" class="inputbox" id="delete_attachment" value=0 />' . $mail_body . '
						<input name="mail_attachments" type="hidden" class="inputbox" id="mail_attachments" value="" />
						<input name="mail_type" type="hidden" class="inputbox" id="mail_type" value="expert" />
						<p>
							<div>
								<input class="btn btn-large btn-success" style="margin-top: 16px" type="submit" name="expert" value="' . JText::_('COM_EMUNDUS_EMAILS_SEND_CUSTOM_EMAIL') . '" >
							</div>
						</p>
						
					<script data-cfasync="false" type="text/javascript" src="media/editors/tinymce/tinymce.min.js"></script>
					<script data-cfasync="false" type="text/javascript" src="media/editors/tinymce/js/tinymce.min.js"></script>
					<script data-cfasync="false" type="text/javascript">tinyMCE.init({
								selector: "#mail_body",
                                document_base_url: "' . JURI::Base() . '",
                                relative_urls: false,
                                remove_script_host: false,
                                convert_urls: false,
                                height : "480"
                              });</script>
					<script>
					
						var REGEX_EMAIL = "([a-z0-9!#$%&\\\'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\\\'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?)";
						$("#mail_to").selectize({
					        plugins: ["remove_button"],
					        delimiter: ",",
					        persist: false,
					        createOnBlur: true,
					        render: {
					            item: function(data, escape) {
					                return "<div>" + escape(data.value.trim()) + "</div>";
								},
								option: function(item, escape) {
									const label = item.text.trim();
									return \'<div>\' +
										((label !== \'\')?\'<strong>\'+escape(label)+\' </strong>\'+escape(item.value):escape(item.value)) +
									\'</div>\';
								}
					        },
					        onDelete: function() {
					            return true;
					        },
					        create: function(input) {
					            let val = input;
					            val = val.substring(val.indexOf(":") + 1).trim();
						        if ((new RegExp(\'^\' + REGEX_EMAIL + \'$\', \'i\')).test(val)) {
						            return {
							            value: val
							        }
						        }
						        alert(\'Invalid email address.\');
						        return false;
						    }
                        });
					
						function getXMLHttpRequest() {
							var xhr = null;
			
							if (window.XMLHttpRequest || window.ActiveXObject) {
								if (window.ActiveXObject) {
									try {
										xhr = new ActiveXObject("Msxml2.XMLHTTP");
									} catch(e) {
										xhr = new ActiveXObject("Microsoft.XMLHTTP");
									}
								} else {
									xhr = new XMLHttpRequest();
								}
							} else {
								alert("Votre navigateur ne supporte pas l\'objet XMLHTTPRequest...");
								return null;
							}
			
							return xhr;
						}
						function deleteAttachment(id){
							var xhr = getXMLHttpRequest();
							xhr.onreadystatechange = function() {
								if (xhr.readyState == 4 && (xhr.status == 200 || xhr.status == 0)) {
									if (xhr.responseText !== "SQL Error") {
										$("em_dl_"+id).innerHTML = "";
										document.getElementById("em_dl_"+id).style.visibility = "hidden";
									} else {
										alert(xhr.responseText);
									}
								}
							};
							xhr.open("GET", "/index.php?option=com_emundus&controller=application&format=raw&task=delete_attachment&Itemid=' . $itemid . '&id="+id, true);
							xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
							xhr.send("&id="+id);
						}
					</script>';
		}

		if (in_array('this_applicant', $params)) {
			$editor    = Factory::getEditor('tinymce');
			$params    = array('mode' => 'simple');
			$mail_body = $editor->display('mail_body', '[NAME], ', '100%', '400', '20', '20', false, 'mail_body', null, null, $params);
			$email     .= '<input name="fnums" type="hidden" class="inputbox" id="fnums" value=\'' . $fnums . '\' />';

			$email_to = $app->input->get('sid', null, 'GET', 'none', 0);

			$student = Factory::getUser($email_to);

			$AllEmail_template = EmundusHelperEmails::getAllEmail(2);
			$email             .= '<select name="select_template" onChange="getTemplate(this);">
				<option value="%">-- ' . JText::_('COM_EMUNDUS_EMAILS_SELECT_TEMPLATE') . ' --</option>';
			foreach ($AllEmail_template as $email_template) {
				$email .= '<option value="' . $email_template->id . '">' . $email_template->subject . '</option>';
			}
			$email .= '</select>';

			$email .= '
					<input name="mail_subject" placeholder="' . JText::_('COM_EMUNDUS_EMAILS_SUBJECT') . '" type="text" class="inputbox" id="mail_subject" value="" size="100" style="width: inherit !important;" />
					<input name="mail_to" type="text" class="inputbox input-xlarge" id="mail_to" value="' . $student->username . '" size="100" disabled/>
					<input type="hidden" name="ud[]" value="' . $email_to . '" >';

			$email .= $mail_body . '<div><input class="btn btn-large btn-success" type="submit" name="applicant_email" value="' . JText::_('COM_EMUNDUS_EMAILS_SEND_CUSTOM_EMAIL') . '" ></div>';
		}
		$email .= '</div>
					<script>$(document).on("click", "input[type=\'submit\']", function() { if($("#mail_subject").val() == ""){$("#mail_subject").css("border", "2px solid red"); return false;} else document.pressed=this.name; }); </script>
					<script>' . EmundusHelperJavascript::getTemplate() . '</script>';

		return $email;
	}

	public static function getEmail($lbl)
	{
		$db = Factory::getDBO();

		$query = $db->getQuery(true);

		$query->select('*')
			->from($db->quoteName('#__emundus_setup_emails'))
			->where($db->quoteName('lbl') . ' = ' . $db->quote($lbl));
		$db->setQuery($query);

		return $db->loadObject();
	}

	function getAllEmail($type = 2)
	{
		$db    = JFactory::getDBO();
		$query = 'SELECT * FROM #__emundus_setup_emails WHERE type IN (' . $db->Quote($type) . ') AND published=1';
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	public static function getTemplate()
	{
		$db     = JFactory::getDBO();
		$select = JFactory::getApplication()->input->get('select', null, 'POST', 'none', 0);
		$query  = 'SELECT * FROM #__emundus_setup_emails WHERE id=' . $select;
		$db->setQuery($query);
		$email = $db->loadObject();
		echo json_encode((object) (array('status' => true, 'tmpl' => $email)));

		die();
	}

	function sendGroupEmail()
	{
		$current_user = JFactory::getUser();

		$app            = JFactory::getApplication();
		$email_from_sys = $app->getCfg('mailfrom');


		if (//!EmundusHelperAccess::asAccessAction(9, 'c')  && 	//email applicant
			//!EmundusHelperAccess::asAccessAction(15, 'c') &&	//email evaluator
		!EmundusHelperAccess::asAccessAction(16, 'c')    //email group
			//!EmundusHelperAccess::asAccessAction(17, 'c') &&	//email address
			//!EmundusHelperAccess::asAccessAction(18, 'c')		//email expert
		) {
			die(JText::_("ACCESS_DENIED"));
		}

		// Model for GetCampaignWithID()

		// include model email for Tag
		include_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'emails.php');
		$m_emails = new EmundusModelEmails;

		$mainframe = JFactory::getApplication();
		$db        = JFactory::getDBO();
		$jinput    = JFactory::getApplication()->input;

		$limitstart       = JFactory::getApplication()->input->get('limitstart', null, 'POST', 'none', 0);
		$filter_order     = JFactory::getApplication()->input->get('filter_order', null, 'POST', null, 0);
		$filter_order_Dir = JFactory::getApplication()->input->get('filter_order_Dir', null, 'POST', null, 0);
		$itemid           = JFactory::getApplication()->input->get('Itemid', null, 'GET', null, 0);

		$ag_id    = $jinput->get('mail_group', array(), "ARRAY");
		$users_id = array();
		if (!empty($ag_id) && count($ag_id) > 0) {
			$query = 'SELECT user_id FROM #__emundus_groups WHERE group_id IN (' . implode(", ", $ag_id) . ') GROUP BY user_id';
			$db->setQuery($query);
			try {
				$users_id = $db->loadColumn();
			}
			catch (Exception $e) {
				echo 'Error database: ' . $e;
				die();
			}
		}

		// Content of email
		$captcha = 1;//JFactory::getApplication()->input->getInt( JR_CAPTCHA, null, 'post' );

		$from     = JFactory::getApplication()->input->get('mail_from', null, 'post');
		$from_id  = JFactory::getApplication()->input->get('mail_from_id', null, 'post');
		$fromname = JFactory::getApplication()->input->get('mail_from_name', null, 'post');
		$subject  = JFactory::getApplication()->input->get('mail_subject', null, 'post');
		$message  = JFactory::getApplication()->input->get('mail_body', '', 'POST', 'STRING', JREQUEST_ALLOWHTML);

		if ($subject == '') {
			JError::raiseWarning(500, JText::_('COM_EMUNDUS_ERROR_EMAILS_YOU_MUST_PROVIDE_SUBJECT'));
			$mainframe->redirect('index.php?option=com_emundus&view=email&tmpl=component&desc=2&Itemid=' . $itemid);

			return;
		}
		if ($message == '') {
			JError::raiseWarning(500, JText::_('COM_EMUNDUS_ERROR_EMAILS_YOU_MUST_PROVIDE_A_MESSAGE'));
			$mainframe->redirect('index.php?option=com_emundus&view=email&tmpl=component&desc=2&Itemid=' . $itemid);

			return;
		}

		// setup mail
		if (!isset($from) || empty($from)) {
			if (isset($current_user->email)) {
				$from     = $current_user->email;
				$from_id  = $current_user->id;
				$fromname = $current_user->name;
			}
			elseif ($mainframe->getCfg('mailfrom') != '' && $mainframe->getCfg('fromname') != '') {
				$from     = $mainframe->getCfg('mailfrom');
				$fromname = $mainframe->getCfg('fromname');
				$from_id  = 62;
			}
			else {
				$query = 'SELECT id, name, email' .
					' FROM #__users' .
					// administrator
					' WHERE gid = 25 LIMIT 1';
				$db->setQuery($query);
				$admin    = $db->loadObject();
				$from     = $admin->name;
				$from_id  = $admin->id;
				$fromname = $admin->email;
			}
		}

		$query = 'SELECT u.id, u.name, u.email' .
			' FROM #__users AS u' .
			' WHERE u.id IN (' . implode(',', $users_id) . ')';
		$db->setQuery($query);
		try {
			$users = $db->loadObjectList();
		}
		catch (Exception $e) {
			echo '<div class="alert alert-warning">Aucun mail envoyé, groupe vide</div>';
			die();
		}


		$nUsers = count($users);
		$info   = '';
		for ($i = 0; $i < $nUsers; $i++) {
			$user = $users[$i];

			$can_send_mail = $this->assertCanSendMailToUser($user->id);
			if (!$can_send_mail) {
				continue;
			}

			if (isset($campaigns_id[$i]) && !empty($campaigns_id[$i])) {
				include_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'campaign.php');
				$m_campaign = new EmundusModelCampaign;
				$campaign   = $m_campaign->getCampaignByID($campaigns_id[$i]);
				$programme  = $m_campaign->getProgrammeByCampaignID($campaigns_id[$i]);
			}

			// template replacements (patterns)
			$post = [
				'COURSE_LABEL'   => @$programme['label'],
				'CAMPAIGN_LABEL' => @$campaign['label'],
				'SITE_URL'       => JURI::base(),
				'USER_EMAIL'     => $user->email
			];
			$tags = $m_emails->setTags($user->id, $post, null, '', $message);

			$body = preg_replace($tags['patterns'], $tags['replacements'], $message);

			if (!empty($user->email)) {
				// mail function
				$mailer = JFactory::getMailer();

				// If the email sender has the same domain as the system sender address.
				if (!empty($from) && substr(strrchr($from, "@"), 1) === substr(strrchr($email_from_sys, "@"), 1))
					$mail_from_address = $from;
				else
					$mail_from_address = $email_from_sys;

				// Set sender
				$sender = [
					$mail_from_address,
					$fromname
				];

				$mailer->setSender($sender);
				$mailer->addReplyTo($from, $fromname);
				$mailer->addRecipient($user->email);
				$mailer->setSubject($subject);
				$mailer->isHTML(true);
				$mailer->Encoding = 'base64';
				$mailer->setBody($body);

				$send = $mailer->Send();
				if ($send !== true) {
					JLog::add($send->__toString(), JLog::ERROR, 'com_emundus.email');
					echo 'Error sending email: ' . $send->__toString();
					die();
				}
				else {
					$sql = "INSERT INTO `#__messages` (`user_id_from`, `user_id_to`, `subject`, `message`, `date_time`)
						VALUES ('" . $from_id . "', '" . $user->id . "', " . $db->quote($subject) . ", " . $db->quote($body) . ", NOW())";
					$db->setQuery($sql);
					try {
						$db->execute();
					}
					catch (Exception $e) {
						echo 'Error database: ' . $e;
						die();
					}
					$info .= "<hr>" . ($i + 1) . " : " . $user->email . " " . JText::_('COM_EMUNDUS_APPLICATION_SENT');
					if ($i % 10 == 0) {
						@set_time_limit(10800);
						usleep(1000);
					}
				}
			}
		}
		$mainframe->redirect('index.php?option=com_emundus&view=email&tmpl=component&layout=sent&desc=2', JText::_('COM_EMUNDUS_EMAILS_REPORTS_MAILS_SENT') . $info, 'message');

	}

	public static function sendApplicantEmail()
	{

		$current_user = JFactory::getUser();
		$config       = JFactory::getConfig();

		if (!EmundusHelperAccess::asAccessAction(9, 'c'))    //email applicant
		{
			die(JText::_("ACCESS_DENIED"));
		}

		// include model email for Tag
		include_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'emails.php');
		$m_emails = new EmundusModelEmails();

		$mainframe = JFactory::getApplication();

		$db = JFactory::getDBO();

		$email_from_sys = $mainframe->getCfg('mailfrom');

		$cids = JFactory::getApplication()->input->get('ud', array(), 'post', 'array');
		foreach ($cids as $cid) {
			$params         = explode('|', $cid);
			$users_id[]     = intval($params[0]);
			$campaigns_id[] = intval($params[1]);
		}

		$captcha = 1;

		$from     = JFactory::getApplication()->input->get('mail_from', null, 'post');
		$from_id  = JFactory::getApplication()->input->get('mail_from_id', null, 'post');
		$fromname = JFactory::getApplication()->input->get('mail_from_name', null, 'post');
		$subject  = JFactory::getApplication()->input->get('mail_subject', null, 'post');
		$message  = JFactory::getApplication()->input->get('mail_body', '', 'POST', 'STRING', JREQUEST_ALLOWHTML);

		$fnums = $mainframe->input->get('fnums', null, 'RAW');
		$fnums = (array) json_decode(stripslashes($fnums), false, 512, JSON_BIGINT_AS_STRING);

		if ($captcha !== 1) {
			JError::raiseWarning(500, JText::_('COM_EMUNDUS_ERROR_EMAILS_NOT_A_VALID_POST'));
			$mainframe->redirect('index.php?option=com_emundus&view=' . JFactory::getApplication()->input->get('view') . '&tmpl=' . JFactory::getApplication()->input->get('tmpl') . '&limitstart=' . $limitstart . '&filter_order=' . $filter_order . '&filter_order_Dir=' . $filter_order_Dir . '&Itemid=' . JFactory::getApplication()->input->get('Itemid'));

			return;
		}
		if (count($users_id) == 0) {
			JError::raiseWarning(500, JText::_('COM_EMUNDUS_ERROR_NO_ITEMS_SELECTED'));
			$mainframe->redirect('index.php?option=com_emundus&view=' . JFactory::getApplication()->input->get('view') . '&tmpl=' . JFactory::getApplication()->input->get('tmpl') . '&limitstart=' . $limitstart . '&filter_order=' . $filter_order . '&filter_order_Dir=' . $filter_order_Dir . '&Itemid=' . JFactory::getApplication()->input->get('Itemid'));

			return;
		}
		if ($subject == '') {
			JError::raiseWarning(500, JText::_('COM_EMUNDUS_ERROR_EMAILS_YOU_MUST_PROVIDE_SUBJECT'));
			$mainframe->redirect('index.php?option=com_emundus&view=' . JFactory::getApplication()->input->get('view') . '&tmpl=' . JFactory::getApplication()->input->get('tmpl') . '&limitstart=' . $limitstart . '&filter_order=' . $filter_order . '&filter_order_Dir=' . $filter_order_Dir . '&Itemid=' . JFactory::getApplication()->input->get('Itemid'));

			return;
		}
		if ($message == '') {
			JError::raiseWarning(500, JText::_('COM_EMUNDUS_ERROR_EMAILS_YOU_MUST_PROVIDE_A_MESSAGE'));
			$mainframe->redirect('index.php?option=com_emundus&view=' . JFactory::getApplication()->input->get('view') . '&tmpl=' . JFactory::getApplication()->input->get('tmpl') . '&limitstart=' . $limitstart . '&filter_order=' . $filter_order . '&filter_order_Dir=' . $filter_order_Dir . '&Itemid=' . JFactory::getApplication()->input->get('Itemid'));

			return;
		}


		$query = 'SELECT u.id, u.name, u.email' .
			' FROM #__users AS u' .
			' WHERE u.id IN (' . implode(',', $users_id) . ')';
		$db->setQuery($query);
		try {
			$users = $db->loadObjectList();
		}
		catch (Exception $e) {
			echo 'Error database: ' . $e;
			die();
		}

		// setup mail
		if (!isset($from) || empty($from)) {
			if (isset($current_user->email)) {
				$from     = $current_user->email;
				$from_id  = $current_user->id;
				$fromname = $current_user->name;
			}
			elseif ($mainframe->getCfg('mailfrom') != '' && $mainframe->getCfg('fromname') != '') {
				$from     = $mainframe->getCfg('mailfrom');
				$fromname = $mainframe->getCfg('fromname');
				$from_id  = 62;
			}
			else {
				$query = 'SELECT id, name, email' .
					' FROM #__users' .
					// administrator
					' WHERE gid = 25 LIMIT 1';
				$db->setQuery($query);
				$admin    = $db->loadObject();
				$from     = $admin->name;
				$from_id  = $admin->id;
				$fromname = $admin->email;
			}
		}

		$nUsers = count($users);
		$info   = '';
		for ($i = 0; $i < $nUsers; $i++) {

			$user = $users[$i];

			$can_send_mail = EmundusHelperEmails::assertCanSendMailToUser($user->id);
			if (!$can_send_mail) {
				continue;
			}

			if (isset($campaigns_id[$i]) && !empty($campaigns_id[$i])) {
				include_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'campaign.php');
				$m_campaign = new EmundusModelCampaign;
				$campaign   = $m_campaign->getCampaignByID($campaigns_id[$i]);
				$programme  = $m_campaign->getProgrammeByCampaignID($campaigns_id[$i]);
			}

			// template replacements (patterns)
			$post = [
				'COURSE_LABEL'   => @$programme['label'],
				'CAMPAIGN_LABEL' => @$campaign['label'],
				'SITE_URL'       => JURI::base(),
				'USER_EMAIL'     => $user->email
			];

			$tags = $m_emails->setTags($user->id, $post, null, '', $from . $fromname . $subject . $message);

			$from     = preg_replace($tags['patterns'], $tags['replacements'], $from);
			$from_id  = $user->id;
			$fromname = preg_replace($tags['patterns'], $tags['replacements'], $fromname);
			$to       = $user->email;
			$subject  = preg_replace($tags['patterns'], $tags['replacements'], $subject);
			$body     = preg_replace($tags['patterns'], $tags['replacements'], $message);
			$body     = $m_emails->setTagsFabrik($body, array($fnums[$i]));

			if (!empty($user->email)) {
				// mail function
				$mailer = JFactory::getMailer();

				// If the email sender has the same domain as the system sender address.
				if (!empty($from) && substr(strrchr($from, "@"), 1) === substr(strrchr($email_from_sys, "@"), 1))
					$mail_from_address = $from;
				else
					$mail_from_address = $email_from_sys;

				// Set sender
				$sender = [
					$mail_from_address,
					$fromname
				];

				$mailer->setSender($sender);
				$mailer->addReplyTo($from, $fromname);
				$mailer->addRecipient($user->email);
				$mailer->setSubject($subject);
				$mailer->isHTML(true);
				$mailer->Encoding = 'base64';
				$mailer->setBody($body);

				$send = $mailer->Send();
				if ($send !== true) {
					JLog::add($send->__toString(), JLog::ERROR, 'com_emundus.email');
					echo 'Error sending email: ' . $send->__toString();
					die();
				}
				else {

					$sql = "INSERT INTO `#__messages` (`user_id_from`, `user_id_to`, `subject`, `message`, `date_time`)
							VALUES ('" . $from_id . "', '" . $user->id . "', " . $db->quote($subject) . ", " . $db->quote($body) . ", NOW())";
					$db->setQuery($sql);
					try {
						$db->execute();
					}
					catch (Exception $e) {
						echo 'Error database: ' . $e;
						die();
					}
					$info .= "<hr>" . ($i + 1) . " : " . $user->email . " " . JText::_('COM_EMUNDUS_APPLICATION_SENT');
					if ($i % 10 == 0) {
						@set_time_limit(10800);
						usleep(1000);
					}
				}
			}
		}
		$mainframe->redirect('index.php?option=com_emundus&view=email&tmpl=component&layout=sent', JText::_('COM_EMUNDUS_EMAILS_REPORTS_MAILS_SENT') . $info, 'message');
	}

	/**
	 * Assert that emails can be sent to user, by checking user params and email validity
	 *
	 * @param $user_id
	 * @param $fnum
	 *
	 * @return bool
	 */
	function assertCanSendMailToUser($user_id = null, $fnum = null): bool
	{
		$can_send_mail = true;

		if (!empty($user_id) || !empty($fnum)) {
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			if (!empty($user_id)) {
				$query->select('email, params')
					->from('#__users')
					->where('id = ' . $user_id);
				$db->setQuery($query);
			}
			else {
				$query->select('ju.email, ju.params')
					->from('#__users AS ju')
					->leftJoin('#__emundus_campaign_candidature AS jecc ON jecc.applicant_id = ju.id')
					->where('jecc.fnum LIKE ' . $db->quote($fnum));
				$db->setQuery($query);
			}

			try {
				$user = $db->loadObject();
			}
			catch (Exception $e) {
				$user = null;
				JLog::add('Failed to retrieve user params for user_id ' . $user_id . ' fnum ' . $fnum . ' ' . $e->getMessage(), JLog::ERROR, 'com_emundus.email');
			}

			if (!empty($user)) {
				if (!$this->correctEmail($user->email)) {
					$can_send_mail = false;
				}
				else {
					$params = json_decode($user->params, true);
					if (isset($params['send_mail']) && !$params['send_mail']) {
						$can_send_mail = false;
						JLog::add("[User $user_id fnum $fnum] does not receive emails due to user parameter", JLog::INFO, 'com_emundus.email');
					}
				}
			}
			else {
				$can_send_mail = false;
			}
		}
		else {
			$can_send_mail = false;
		}

		return $can_send_mail;
	}

	/**
	 * Check given email is not empty, has a valid format, and email dns exists
	 *
	 * @param $email
	 *
	 * @return bool
	 */
	function correctEmail($email): bool
	{
		$is_correct = true;

		if (empty($email)) {
			$is_correct = false;
		}
		else {
			if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$is_correct = false;
				JLog::add('Invalid email ' . $email, JLog::INFO, 'com_emundus.email');
			}
			else {
				$domain = substr($email, strpos($email, '@') + 1);
				if (!checkdnsrr($domain)) {
					JLog::add('Invalid email domain ' . $email, JLog::INFO, 'com_emundus.email');
					$is_correct = false;
				}
			}
		}

		return $is_correct;
	}

	function getLogo(): string
	{
		$logo     = '';
		$app      = JFactory::getApplication();
		$template = $app->getTemplate(true);
		$config   = JFactory::getConfig();

		$params = $template->params;


		if (!empty($params->get('logo')->custom->image)) {
			$logo = json_decode(str_replace("'", "\"", $params->get('logo')->custom->image), true);
			$logo = !empty($logo['path']) ? JURI::base() . $logo['path'] : "";
		}
		else {
			$logo_module = JModuleHelper::getModuleById('90');
			preg_match('#src="(.*?)"#i', $logo_module->content, $tab);
			$pattern = "/^(?:ftp|https?|feed)?:?\/\/(?:(?:(?:[\w\.\-\+!$&'\(\)*\+,;=]|%[0-9a-f]{2})+:)*
        (?:[\w\.\-\+%!$&'\(\)*\+,;=]|%[0-9a-f]{2})+@)?(?:
        (?:[a-z0-9\-\.]|%[0-9a-f]{2})+|(?:\[(?:[0-9a-f]{0,4}:)*(?:[0-9a-f]{0,4})\]))(?::[0-9]+)?(?:[\/|\?]
        (?:[\w#!:\.\?\+\|=&@$'~*,;\/\(\)\[\]\-]|%[0-9a-f]{2})*)?$/xi";

			if (preg_match($pattern, $tab[1])) {
				$tab[1] = parse_url($tab[1], PHP_URL_PATH);
			}

			$logo = JURI::base() . $tab[1];
		}

		return $logo;
	}

	public static function getCustomHeader(): string
	{
		$result = '';

		$eMConfig         = ComponentHelper::getParams('com_emundus');
		$custom_email_tag = $eMConfig->get('email_custom_tag', null);

		if (!empty($custom_email_tag)) {
			$custom_email_tag = explode(',', $custom_email_tag);

			$result = $custom_email_tag[0] . ':' . $custom_email_tag[1];
		}

		return $result;
	}
}

?>
