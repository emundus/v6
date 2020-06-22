<?php
/**
 * @version		$Id: email.php 
 * @package		Joomla
 * @subpackage	Emundus
 * @copyright	Copyright (C) 2019 eMundus. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.helper');
/**
 * Content Component Query Helper
 *
 * @static
 * @package		Joomla
 * @subpackage	Helper
 * @since 1.5
 */
class EmundusHelperEmails {

	function createEmailBlock($params, $users = null) {
		$jinput = JFactory::getApplication()->input;
		$itemid = $jinput->get('Itemid', null, 'INT');
		$fnums = $jinput->get('fnums', null, 'RAW');

		$fnumsArray = (array) json_decode($fnums);
		if (count($fnumsArray) > 0) {
			foreach ($fnumsArray as $value) {
				if ($value->fnum !== 'em-check-all') {
					$fnums_tab[] = $value->fnum;
				}
			}
			$fnums = json_encode($fnums_tab);
		}

		$current_user = JFactory::getUser();
		$email = '<div class="em_email_block" id="em_email_block">
					<input placeholder="'.JText::_('NAME_FROM').'" name="mail_from_name" type="text" class="inputbox input-xlarge" id="mail_from_name" value="'.$current_user->name.'" />
					<input placeholder="'.JText::_('EMAIL_FROM').'" name="mail_from" type="text" class="inputbox input-xlarge" id="mail_from" value="'.$current_user->email.'" />
					<input name="mail_from_id" type="hidden" class="inputbox" id="mail_from_id" value="'.$current_user->id.'" /><br>';

		if (in_array('default',$params)) {
			$email .= '<fieldset class="em_email_block-fieldset">
				<legend>
					<span class="editlinktip hasTip" title="'.JText::_('EMAIL_ASSESSORS_DEFAULT').'::'.JText::_('EMAIL_ASSESSORS_DEFAULT_TIP').'">
						<img src="'.JURI::base().'media/com_emundus/images/icones/mail_replayall_22x22.png" alt="'.JText::_('EMAIL_ASSESSORS_DEFAULT').'"/>'.JText::_('EMAIL_ASSESSORS_DEFAULT').'
					</span>
				</legend>
				<div><input type="submit" class="btn btn-large btn-success" name="default_email" value="'.JText::_( 'SEND_DEFAULT_EMAIL' ).'" ></div>
			</fieldset>';
		}

		if (in_array('groups', $params)) {
			$default_template = EmundusHelperEmails::getEmail('assessors_set');
			$editor = JFactory::getEditor('tinymce');
			$params = array('mode' => 'simple');
			$mail_body = $editor->display( 'mail_body', $default_template->message, '100%', '400', '20', '20', false, 'mail_body', null, null, $params );
			$email .= '<input name="fnums" type="hidden" class="inputbox" id="fnums" value=\''.$fnums.'\' />';

			$current_group = $jinput->get('groups', null, 'INT');
			$all_groups = EmundusHelperFilters::getGroups();

			$email .= '<select name="mail_group[]" id="mail_group" multiple="multiple" size="6">
						<option value=""> '.JText::_('PLEASE_SELECT_GROUP').' </option>' ;
							foreach($all_groups as $groups) {
								$email .= '<option value="'.$groups->id.'"';
								if($current_group==$groups->id) $email .= ' selected';
								$email .= '>'.$groups->label.'</option>';
							}
			$email .= '</select>';
			$email .= '<script>$(document).ready(function() {$("#mail_group").chosen({width: "100%"}); })</script>';

			$AllEmail_template = EmundusHelperEmails::getAllEmail(2);
			$email.='<select name="select_template" onChange="getTemplate(this);">
				<option value="%">-- '.JText::_( 'SELECT_TEMPLATE' ).' --</option>';
			foreach ($AllEmail_template as $email_template){
				$email.='<option value="'.$email_template->id.'">'.$email_template->subject.'</option>';
			}
			$email.='</select>';
			$email.= ' <input placeholder="'.JText::_( 'SUBJECT' ).'" name="mail_subject" type="text" class="inputbox" id="mail_subject" value="'.$default_template->subject.'" size="100" style="width: inherit !important;" />';
			$email .= $mail_body.'<input class="btn btn-large btn-success" type="submit" name="group_email" value="'.JText::_( 'SEND_CUSTOM_EMAIL' ).'" >';
		}

		if (in_array('applicants', $params)) {
			$editor = JFactory::getEditor('tinymce');
			$params = array('mode' => 'simple');
			$mail_body = $editor->display( 'mail_body', '[NAME], ', '100%', '400', '20', '20', false, 'mail_body', null, null, $params );
			$email .= '<input name="fnums" type="hidden" class="inputbox" id="fnums" value=\''.$fnums.'\' />';

			if (is_null($users)) {
				$email.= '<label for="select_template">'.JText::_( 'TEMPLATE' ).'</label>';
				$AllEmail_template = EmundusHelperEmails::getAllEmail(2);
				$email.='<select name="select_template" onChange="getTemplate(this);">
					<option value="%">'.JText::_( 'SELECT_TEMPLATE' ).'</option>';
				foreach ($AllEmail_template as $email_template){
					$email.='<option value="'.$email_template->id.'">'.$email_template->subject.'</option>';
				}
				$email.='</select>';
				$email.= '<input placeholder="'.JText::_( 'SUBJECT' ).'" name="mail_subject" type="text" class="inputbox" id="mail_subject" value="" size="100" style="width: inherit !important;" />';
				$email .=$mail_body.'<input class="btn btn-large btn-success" type="submit" name="applicant_email" value="'.JText::_( 'SEND_CUSTOM_EMAIL' ).'" >';

			} else {
				$email .= '<div class="well well-sm">';
				$email_list = array();
				foreach ($users as $user) {
					if (!empty($user['email']) && !in_array($user['email'], $email_list)) {
						$email_list[] = $user['email'];
						$email .= '<span class="label label-primary">';
						$email .= $user['name'].' <em>&lt;'.$user['email'].'&gt;</em>, ';
						$email .= '</span> ';
						$email .= '<input type="hidden" name="ud[]" value="'.$user['id'].'|'.$user['campaign_id'].'"/> ';
					}
				}
				$email .= '</div>';

				$AllEmail_template = EmundusHelperEmails::getAllEmail(2);
				$email.='<select name="select_template" onChange="getTemplate(this);">
					<option value="%">-- '.JText::_( 'SELECT_TEMPLATE' ).' --</option>';
				foreach ($AllEmail_template as $email_template){
					$email.='<option value="'.$email_template->id.'">'.$email_template->subject.'</option>';
				}
				$email .='</select>';
				$email .= ' <input placeholder="'.JText::_( 'SUBJECT' ).'" name="mail_subject" type="text" class="inputbox" id="mail_subject" value="" size="100" style="width: inherit !important;" />';
				$email .= $mail_body.'<div><input class="btn btn-large btn-success" type="submit" name="applicant_email" value="'.JText::_( 'SEND_CUSTOM_EMAIL' ).'" ></div>';
			}
		}

		if (in_array('evaluators', $params)) {

			$default_template = EmundusHelperEmails::getEmail('assessors_set');
			$editor = JFactory::getEditor('tinymce');
			$params = array('mode' => 'simple');
			$mail_body = $editor->display( 'mail_body', $default_template->message, '100%', '400', '20', '20', false, 'mail_body', null, null, $params );
			$email .= '<input name="fnums" type="hidden" class="inputbox" id="fnums" value=\''.$fnums.'\' />';

			if (is_null($users)) {

				$email = '<input type="text" name="ud[]" value=""/> ';
				$AllEmail_template = EmundusHelperEmails::getAllEmail(2);
				$email .= '<select name="select_template" onChange="getTemplate(this);">
					<option value="%">-- '.JText::_( 'SELECT_TEMPLATE' ).' --</option>';
				foreach ($AllEmail_template as $email_template){
					$email .= '<option value="'.$email_template->id.'">'.$email_template->subject.'</option>';
				}
				$email .= '</select>';

				$email .= '<input placeholder="'.JText::_( 'SUBJECT' ).'" name="mail_subject" type="text" class="inputbox" id="mail_subject" value="'.$default_template->subject.'" size="100" style="width: inherit !important;" />';
				$email .= '<input placeholder="'.JText::_( 'EMAIL_TO' ).'" type="text" name="ud[]" value=""/> ';
				$email .= $mail_body.'<input type="submit" class="btn btn-large btn-success" name="applicant_email" value="'.JText::_( 'SEND_CUSTOM_EMAIL' ).'" >';
			} else {
				foreach ($users as $user) {
					$email_list = array();
					if (!empty($user['email']) && !in_array($user['email'], $email_list)) {
						$email_list[] = $user['email'];
						$email .= '<span class="label label-primary">';
						$email .= strtoupper($user['last_name']).' '.$user['first_name'].' <em>'.$user['email'].'</em>';
						$email .= '</span> ';
						$email .= '<input type="hidden" name="ud[]" value="'.$user['id'].'|'.$user['campaign_id'].'"/> ';
					}
				}

				$AllEmail_template = EmundusHelperEmails::getAllEmail(2);
				$email .= '<br><select name="select_template" onChange="getTemplate(this);">
				<option value="%">-- '.JText::_( 'SELECT_TEMPLATE' ).' --</option>';
				foreach ($AllEmail_template as $email_template){
					$email.='<option value="'.$email_template->id.'">'.$email_template->subject.'</option>';
				}
				$email.='</select>';
				$email .= '<input placeholder="'.JText::_( 'SUBJECT' ).'" name="mail_subject" type="text" class="inputbox" id="mail_subject" value="'.$default_template->subject.'" size="100" style="width: inherit !important;" />';

				$email .= $mail_body.'<input type="submit" class="btn btn-large btn-success" name="applicant_email" value="'.JText::_( 'SEND_CUSTOM_EMAIL' ).'" >';
			}
		}

		if (in_array('evaluation_result', $params)) {
			$editor = JFactory::getEditor('tinymce');
			$params = array('mode' => 'simple');
			$mail_body = $editor->display( 'mail_body', '[NAME], ', '100%', '400', '20', '20', false, 'mail_body', null, null, $params );
			$email .= '<input name="fnums" type="hidden" class="inputbox" id="fnums" value=\''.$fnums.'\' />';

			$student_id = $jinput->getInt('jos_emundus_evaluations___student_id');
			$campaign_id = $jinput->getInt('jos_emundus_evaluations___campaign_id');
			$applicant = JFactory::getUser($student_id);

			$email .= '<fieldset>
				<legend>
					<span class="editlinktip hasTip" title="'.JText::_('EMAIL_APPLICATION_RESULT').'::'.JText::_('EMAIL_APPLICATION_RESULT_TIP').'">
						<img src="'.JURI::base().'media/com_emundus/images/icones/mail_replay_22x22.png" alt="'.JText::_('EMAIL_TO').'"/> '.JText::_('EMAIL_TO').' '.$applicant->name.' &bull; <i>'.$applicant->email.'</i>
					</span>
				</legend>
				<div>';
				$email .= '
					<input name="mail_subject" type="text" class="inputbox" id="mail_subject" value="" size="100" style="width: inherit !important;" />
					<input name="mail_to" type="hidden" class="inputbox input-xlarge" id="mail_to" value="'.$applicant->id.'" />
					<input name="campaign_id" type="hidden" class="inputbox" id="campaign_id" value="'.$campaign_id.'" size="100" />
				</div>'.$mail_body.'
				</p>
					<input name="mail_attachments" type="hidden" class="inputbox" id="mail_attachments" value="" />
					<input name="mail_type" type="hidden" class="inputbox" id="mail_type" value="evaluation_result" />
				<p><div><input class="btn btn-large btn-success" type="submit" name="evaluation_result_email" value="'.JText::_( 'SEND_CUSTOM_EMAIL' ).'" ></div></p>
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
				xhr.open("GET", "/index.php?option=com_emundus&controller=application&format=raw&task=delete_attachment&Itemid='.$itemid.'&id="+id, true);
				xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
				xhr.send("&id="+id);
			} </script>';
		}

		if (in_array('expert', $params)) {

			$editor = JFactory::getEditor('tinymce');
			$params = array('mode' => 'simple');
			$mail_body = $editor->display( 'mail_body', '[NAME], ', '100%', '400', '20', '20', false, 'mail_body', null, null, $params);

			$experts = "";

			$email .= '<div>';

			$AllEmail_template = EmundusHelperEmails::getAllEmail(2);
			$email .= '<select name="select_template" onChange="getTemplate(this);">
						<option value="%">-- '.JText::_( 'SELECT_TEMPLATE' ).' --</option>';
			foreach ($AllEmail_template as $email_template) {
				$email .= '<option value="'.$email_template->id.'">'.$email_template->subject.'</option>';
			}
			$email .= '</select>
						<input placeholder="'.JText::_( 'SUBJECT' ).'" name="mail_subject" type="text" class="inputbox" id="mail_subject" value="" size="100" style="width: inherit !important;" />
						<input placeholder="'.JText::_( 'EMAIL_TO' ).'"  name="mail_to" type="text" class="inputbox" id="mail_to" value="'.$experts.'" size="100" style="width: 100% !important;" />
						<input name="fnums" type="hidden" class="inputbox" id="fnums" value=\''.$fnums.'\' />
						<input name="delete_attachment" type="hidden" class="inputbox" id="delete_attachment" value=0 />'.$mail_body.'
						<input name="mail_attachments" type="hidden" class="inputbox" id="mail_attachments" value="" />
						<input name="mail_type" type="hidden" class="inputbox" id="mail_type" value="expert" />
						<p>
							<div>
								<input class="btn btn-large btn-success" type="submit" name="expert" value="'.JText::_( 'SEND_CUSTOM_EMAIL' ).'" >
							</div>
						</p>
						
					<script data-cfasync="false" type="text/javascript" src="media/editors/tinymce/tinymce.min.js"></script>
					<script data-cfasync="false" type="text/javascript" src="media/editors/tinymce/js/tinymce.min.js"></script>
					<script data-cfasync="false" type="text/javascript">tinyMCE.init({menubar:false,statusbar: false})</script>
					<script>
					
						// Editor loads disabled by default, we apply must toggle it active on page load.
					    $(document).ready(function() {
					        tinyMCE.execCommand(\'mceToggleEditor\', true, \'mail_body\');
					    });
					
						var REGEX_EMAIL = "([a-z0-9!#$%&\\\'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\\\'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?)";
						$("#mail_to").selectize({
					        plugins: ["remove_button"],
					        delimiter: ",",
					        persist: false,
					        createOnBlur: true,
					        render: {
					            item: function(data, escape) {
					                return "<div>" + escape(data.value.trim()) + "</div>";
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
							xhr.open("GET", "/index.php?option=com_emundus&controller=application&format=raw&task=delete_attachment&Itemid='.$itemid.'&id="+id, true);
							xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
							xhr.send("&id="+id);
						}
					</script>';
		}

		if (in_array('this_applicant', $params)) {
			$editor = JFactory::getEditor('tinymce');
			$params = array('mode' => 'simple');
			$mail_body = $editor->display( 'mail_body', '[NAME], ', '100%', '400', '20', '20', false, 'mail_body', null, null, $params );
			$email .= '<input name="fnums" type="hidden" class="inputbox" id="fnums" value=\''.$fnums.'\' />';

			$email_to = JRequest::getVar('sid', null, 'GET', 'none',0);
			$student = JFactory::getUser($email_to);

			$AllEmail_template = EmundusHelperEmails::getAllEmail(2);
			$email.='<select name="select_template" onChange="getTemplate(this);">
				<option value="%">-- '.JText::_( 'SELECT_TEMPLATE' ).' --</option>';
			foreach ($AllEmail_template as $email_template){
				$email.='<option value="'.$email_template->id.'">'.$email_template->subject.'</option>';
			}
			$email.='</select>';

			$email.= '
					<input name="mail_subject" placeholder="'.JText::_( 'SUBJECT' ).'" type="text" class="inputbox" id="mail_subject" value="" size="100" style="width: inherit !important;" />
					<input name="mail_to" type="text" class="inputbox input-xlarge" id="mail_to" value="'.$student->username.'" size="100" disabled/>
					<input type="hidden" name="ud[]" value="'.$email_to.'" >';

			$email .= $mail_body.'<div><input class="btn btn-large btn-success" type="submit" name="applicant_email" value="'.JText::_( 'SEND_CUSTOM_EMAIL' ).'" ></div>';
		}
		$email .= '</div>
					<script>$(document).on("click", "input[type=\'submit\']", function() { if($("#mail_subject").val() == ""){$("#mail_subject").css("border", "2px solid red"); return false;} else document.pressed=this.name; }); </script>
					<script>'.EmundusHelperJavascript::getTemplate().'</script>';

		return $email;
	}

	function getEmail($lbl)
	{
		$db = JFactory::getDBO();
		$query = 'SELECT * FROM #__emundus_setup_emails WHERE lbl like '.$db->Quote($lbl);
		$db->setQuery( $query );
		return $db->loadObject();
	}

	function getAllEmail($type=2)
	{
		$db = JFactory::getDBO();
		$query = 'SELECT * FROM #__emundus_setup_emails WHERE type IN ('.$db->Quote($type).') AND published=1';
		$db->setQuery($query);
		return $db->loadObjectList();
	}

	function getTemplate(){
		$db = JFactory::getDBO();
		$select = JRequest::getVar('select', null, 'POST', 'none', 0);
		$query = 'SELECT * FROM #__emundus_setup_emails WHERE id='.$select;
		$db->setQuery($query);
		$email = $db->loadObject();
		echo json_encode((object)(array('status' => true, 'tmpl' => $email)));

		die();
	}

	function sendGroupEmail(){
		$current_user = JFactory::getUser();

		$app    = JFactory::getApplication();
	    $email_from_sys = $app->getCfg('mailfrom');


		if (//!EmundusHelperAccess::asAccessAction(9, 'c')  && 	//email applicant
			//!EmundusHelperAccess::asAccessAction(15, 'c') &&	//email evaluator
			!EmundusHelperAccess::asAccessAction(16, 'c')  	//email group
			//!EmundusHelperAccess::asAccessAction(17, 'c') &&	//email address
			//!EmundusHelperAccess::asAccessAction(18, 'c')		//email expert
		) {
			die(JText::_("ACCESS_DENIED"));
		}

        // Model for GetCampaignWithID()

		// include model email for Tag
		include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'emails.php');
		$m_emails = new EmundusModelEmails;

		$mainframe 	= JFactory::getApplication();
		$db 		= JFactory::getDBO();
		$jinput 	= JFactory::getApplication()->input;

		$limitstart 		= JRequest::getVar('limitstart', null, 'POST', 'none',0);
		$filter_order 		= JRequest::getVar('filter_order', null, 'POST', null, 0);
		$filter_order_Dir 	= JRequest::getVar('filter_order_Dir', null, 'POST', null, 0);
		$itemid 			= JRequest::getVar('Itemid', null, 'GET', null, 0);

		$ag_id = $jinput->get('mail_group', array(), "ARRAY");
		$users_id = array();
		if (!empty($ag_id) && count($ag_id) > 0) {
			$query='SELECT user_id FROM #__emundus_groups WHERE group_id IN ('.implode(", ",$ag_id).') GROUP BY user_id';
			$db->setQuery( $query );
            try {
                $users_id=$db->loadColumn();
            } catch (Exception $e) {
                echo 'Error database: ' . $e ; die();
            }
		}

		// Content of email
		$captcha	= 1;//JRequest::getInt( JR_CAPTCHA, null, 'post' );

		$from 		= JRequest::getVar( 'mail_from', null, 'post' );
		$from_id	= JRequest::getVar( 'mail_from_id', null, 'post' );
		$fromname	= JRequest::getVar( 'mail_from_name', null, 'post' );
		$subject	= JRequest::getVar( 'mail_subject', null, 'post' );
		$message	= JRequest::getVar( 'mail_body','','POST','STRING',JREQUEST_ALLOWHTML);

		if ($subject == '') {
			JError::raiseWarning( 500, JText::_( 'ERROR_YOU_MUST_PROVIDE_SUBJECT' ) );
			$mainframe->redirect('index.php?option=com_emundus&view=email&tmpl=component&desc=2&Itemid='.$itemid);
			return;
		}
		if ($message == '') {
			JError::raiseWarning( 500, JText::_( 'ERROR_YOU_MUST_PROVIDE_A_MESSAGE' ) );
			$mainframe->redirect('index.php?option=com_emundus&view=email&tmpl=component&desc=2&Itemid='.$itemid);
			return;
		}

		// setup mail
		if (!isset($from) || empty($from)) {
			if (isset($current_user->email)) {
				$from = $current_user->email;
				$from_id = $current_user->id;
				$fromname=$current_user->name;
			} elseif ($mainframe->getCfg( 'mailfrom' ) != '' && $mainframe->getCfg( 'fromname' ) != '') {
				$from = $mainframe->getCfg( 'mailfrom' );
				$fromname = $mainframe->getCfg( 'fromname' );
				$from_id = 62;
			} else {
				$query = 'SELECT id, name, email' .
					' FROM #__users' .
					// administrator
					' WHERE gid = 25 LIMIT 1';
				$db->setQuery( $query );
				$admin = $db->loadObject();
				$from = $admin->name;
				$from_id = $admin->id;
				$fromname = $admin->email;
			}
		}

		$query = 'SELECT u.id, u.name, u.email' .
					' FROM #__users AS u' .
					' WHERE u.id IN ('.implode( ',', $users_id ).')';
		$db->setQuery( $query );
        try {
            $users = $db->loadObjectList();
        } catch (Exception $e) {
            echo '<div class="alert alert-warning">Aucun mail envoyé, groupe vide</div>';die();
        }


		$nUsers = count( $users );
        $info = '';
		for ($i = 0; $i < $nUsers; $i++) {
			$user = $users[$i];

			if (isset($campaigns_id[$i]) && !empty($campaigns_id[$i])) {
				include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'campaign.php');
				$m_campaign = new EmundusModelCampaign;
				$campaign 	= $m_campaign->getCampaignByID($campaigns_id[$i]);
				$programme 	= $m_campaign->getProgrammeByCampaignID($campaigns_id[$i]);
			}

			// template replacements (patterns)
			$post = [
				'COURSE_LABEL' => @$programme['label'],
				'CAMPAIGN_LABEL' => @$campaign['label'],
				'SITE_URL' => JURI::base(),
				'USER_EMAIL' => $user->email
			];
			$tags = $m_emails->setTags($user->id, $post);

			$body = preg_replace($tags['patterns'], $tags['replacements'], $message);

			if(!empty($user->email)){
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
                if ( $send !== true ) {
		            JLog::add($send->__toString(), JLog::ERROR, 'com_emundus.email');
                    echo 'Error sending email: ' . $send->__toString(); die();
                } else {
                    $sql = "INSERT INTO `#__messages` (`user_id_from`, `user_id_to`, `subject`, `message`, `date_time`)
						VALUES ('".$from_id."', '".$user->id."', ".$db->quote($subject).", ".$db->quote($body).", NOW())";
                    $db->setQuery( $sql );
                    try {
                        $db->execute();
                    } catch (Exception $e) {
                        echo 'Error database: ' . $e ; die();
                    }
                    $info .= "<hr>".($i+1)." : ".$user->email." ".JText::_('SENT');
                    if ($i%10 == 0) {
                        @set_time_limit(10800);
                        usleep(1000);
                    }
                }
			}
		}
        $mainframe->redirect('index.php?option=com_emundus&view=email&tmpl=component&layout=sent&desc=2', JText::_('REPORTS_MAILS_SENT').$info, 'message');

	}

	function sendApplicantEmail() {

		$current_user = JFactory::getUser();
		$config = JFactory::getConfig();

		if (!EmundusHelperAccess::asAccessAction(9, 'c'))	//email applicant
		{
			die(JText::_("ACCESS_DENIED"));
		}

		// include model email for Tag
		include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'emails.php');
		$m_emails = new EmundusModelEmails();

		$mainframe = JFactory::getApplication();

		$db	= JFactory::getDBO();

		$email_from_sys = $mainframe->getCfg('mailfrom');

		$cids = JRequest::getVar( 'ud', array(), 'post', 'array' );
		foreach ($cids as $cid){
			$params=explode('|',$cid);
			$users_id[] = intval($params[0]);
			$campaigns_id[] = intval($params[1]);
		}

		$captcha	= 1;

		$from 		= JRequest::getVar( 'mail_from', null, 'post' );
		$from_id	= JRequest::getVar( 'mail_from_id', null, 'post' );
		$fromname	= JRequest::getVar( 'mail_from_name', null, 'post' );
		$subject	= JRequest::getVar( 'mail_subject', null, 'post' );
		$message	= JRequest::getVar( 'mail_body','','POST','STRING',JREQUEST_ALLOWHTML);

		$fnums = $mainframe->input->get('fnums', null, 'RAW');
        $fnums = (array) json_decode(stripslashes($fnums), false, 512, JSON_BIGINT_AS_STRING);

		if ($captcha !== 1) {
			JError::raiseWarning( 500, JText::_( 'ERROR_NOT_A_VALID_POST' ) );
			$mainframe->redirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&tmpl='.JRequest::getCmd( 'tmpl' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.JRequest::getCmd( 'Itemid' ));
			return;
		}
		if (count( $users_id ) == 0) {
			JError::raiseWarning( 500, JText::_( 'ERROR_NO_ITEMS_SELECTED' ) );
			$mainframe->redirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&tmpl='.JRequest::getCmd( 'tmpl' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.JRequest::getCmd( 'Itemid' ));
			return;
		}
		if ($subject == '') {
			JError::raiseWarning( 500, JText::_( 'ERROR_YOU_MUST_PROVIDE_SUBJECT' ) );
			$mainframe->redirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&tmpl='.JRequest::getCmd( 'tmpl' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.JRequest::getCmd( 'Itemid' ));
			return;
		}
		if ($message == '') {
			JError::raiseWarning( 500, JText::_( 'ERROR_YOU_MUST_PROVIDE_A_MESSAGE' ) );
			$mainframe->redirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&tmpl='.JRequest::getCmd( 'tmpl' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.JRequest::getCmd( 'Itemid' ));
			return;
		}


		$query = 'SELECT u.id, u.name, u.email' .
					' FROM #__users AS u' .
					' WHERE u.id IN ('.implode( ',', $users_id ).')';
		$db->setQuery( $query );
        try {
            $users = $db->loadObjectList();
        } catch (Exception $e) {
            echo 'Error database: ' . $e;
            die();
        }

		// setup mail
		if (!isset($from) || empty($from)) {
			if (isset($current_user->email)) {
				$from = $current_user->email;
				$from_id = $current_user->id;
				$fromname=$current_user->name;
			} elseif ($mainframe->getCfg( 'mailfrom' ) != '' && $mainframe->getCfg( 'fromname' ) != '') {
				$from = $mainframe->getCfg( 'mailfrom' );
				$fromname = $mainframe->getCfg( 'fromname' );
				$from_id = 62;
			} else {
				$query = 'SELECT id, name, email' .
					' FROM #__users' .
					// administrator
					' WHERE gid = 25 LIMIT 1';
				$db->setQuery( $query );
				$admin = $db->loadObject();
				$from = $admin->name;
				$from_id = $admin->id;
				$fromname = $admin->email;
			}
		}

		$nUsers = count( $users );
        $info='';
		for ($i = 0; $i < $nUsers; $i++) {

            $user = $users[$i];

			if (isset($campaigns_id[$i]) && !empty($campaigns_id[$i])) {
				include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'campaign.php');
				$m_campaign = new EmundusModelCampaign;
                $campaign 	= $m_campaign->getCampaignByID($campaigns_id[$i]);
                $programme 	= $m_campaign->getProgrammeByCampaignID($campaigns_id[$i]);
            }

            // template replacements (patterns)
            $post = [
				'COURSE_LABEL' => @$programme['label'],
                'CAMPAIGN_LABEL' => @$campaign['label'],
                'SITE_URL' => JURI::base(),
                'USER_EMAIL' => $user->email
			];

            $tags = $m_emails->setTags($user->id, $post);

            $from 		= preg_replace($tags['patterns'], $tags['replacements'], $from);
            $from_id 	= $user->id;
            $fromname 	= preg_replace($tags['patterns'], $tags['replacements'], $fromname);
            $to 		= $user->email;
            $subject 	= preg_replace($tags['patterns'], $tags['replacements'], $subject);
            $body 		= preg_replace($tags['patterns'], $tags['replacements'], $message);
            $body 		= $m_emails->setTagsFabrik($body, array($fnums[$i]));

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
                } else {

					$sql = "INSERT INTO `#__messages` (`user_id_from`, `user_id_to`, `subject`, `message`, `date_time`)
							VALUES ('" . $from_id . "', '" . $user->id . "', " . $db->quote($subject) . ", " . $db->quote($body) . ", NOW())";
					$db->setQuery($sql);
                    try {
                        $db->execute();
                    } catch (Exception $e) {
                        echo 'Error database: ' . $e;
                        die();
                    }
                    $info .= "<hr>" . ($i + 1) . " : " . $user->email . " " . JText::_('SENT');
                    if ($i % 10 == 0) {
                        @set_time_limit(10800);
                        usleep(1000);
                    }
                }
            }
        }
		$mainframe->redirect('index.php?option=com_emundus&view=email&tmpl=component&layout=sent', JText::_('REPORTS_MAILS_SENT').$info, 'message');
	}
}
?>