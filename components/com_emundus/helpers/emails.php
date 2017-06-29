<?php
/**
 * @version		$Id: email.php 14401 2010-01-26 14:10:00Z guillossou
 * @package		Joomla
 * @subpackage	Emundus
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
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
class EmundusHelperEmails
{
	function createEmailBlock($params, $users = null)
	{
		$jinput = JFactory::getApplication()->input;
		$itemid = $jinput->get('Itemid', null, 'INT'); 
		$fnums = $jinput->get('fnums', null, 'RAW');

		$fnumsArray = (array) json_decode($fnums);
		if (count($fnumsArray) > 0) {
			foreach ($fnumsArray as $key => $value) {
				$fnums_tab[] = $value->fnum;
			}
			$fnums = json_encode($fnums_tab);
		}

		$current_user = JFactory::getUser();
		$email = '<div class="em_email_block" id="em_email_block">';
		$email.= '<input placeholder="'.JText::_( 'EMAIL_FROM' ).'" name="mail_from_name" type="text" class="inputbox input-xlarge" id="mail_from_name" value="'.$current_user->name.'" /> ';
		$email.= '<input placeholder="'.JText::_( 'EMAIL' ).'" name="mail_from" type="text" class="inputbox input-xlarge" id="mail_from" value="'.$current_user->email.'" /> ';
		$email.= '<input name="mail_from_id" type="hidden" class="inputbox" id="mail_from_id" value="'.$current_user->id.'" /><br>';
		
		if(in_array('default',$params)){
			$email .= '<fieldset>
				<legend> 
					<span class="editlinktip hasTip" title="'.JText::_('EMAIL_ASSESSORS_DEFAULT').'::'.JText::_('EMAIL_ASSESSORS_DEFAULT_TIP').'">
						<img src="'.JURI::Base().'media/com_emundus/images/icones/mail_replayall_22x22.png" alt="'.JText::_('EMAIL_ASSESSORS_DEFAULT').'"/>'.JText::_('EMAIL_ASSESSORS_DEFAULT').'
					</span>
				</legend>
				<div><input type="submit" class="btn btn-large btn-success" name="default_email" value="'.JText::_( 'SEND_DEFAULT_EMAIL' ).'" ></div>
			</fieldset>';
		}
		if(in_array('groups',$params)){
			$default_template = EmundusHelperEmails::getEmail('assessors_set');
			$editor = JFactory::getEditor('tinymce');
			$params = array('mode' => 'simple');
			$mail_body = $editor->display( 'mail_body', $default_template->message, '100%', '400', '20', '20', false, 'mail_body', null, null, $params );
			$email .= '<input name="fnums" type="hidden" class="inputbox" id="fnums" value=\''.$fnums.'\' />';

			//$current_eval = JRequest::getVar('user', null, 'POST', 'none',0);
			$current_group = $jinput->get('groups', null, 'INT'); //JRequest::getVar('groups', null, 'POST', 'none',0);
			$all_groups = EmundusHelperFilters::getGroups();
			//$evaluators = EmundusHelperFilters::getEvaluators();

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
		if(in_array('applicants', $params)){
			$editor = JFactory::getEditor('tinymce');
			$params = array('mode' => 'simple');
			$mail_body = $editor->display( 'mail_body', '[NAME], ', '100%', '400', '20', '20', false, 'mail_body', null, null, $params );
			$email .= '<input name="fnums" type="hidden" class="inputbox" id="fnums" value=\''.$fnums.'\' />';

			if(is_null($users))
			{
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

			}
			else
			{
				$email .= '<div class="well well-sm">';
				$email_list = array();
				foreach ($users as $user) 
				{ 
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
				$email.='</select>';
				$email.= ' <input placeholder="'.JText::_( 'SUBJECT' ).'" name="mail_subject" type="text" class="inputbox" id="mail_subject" value="" size="100" style="width: inherit !important;" />';
				$email .= $mail_body.'<div><input class="btn btn-large btn-success" type="submit" name="applicant_email" value="'.JText::_( 'SEND_CUSTOM_EMAIL' ).'" ></div>';
			}
		}
		if(in_array('evaluators', $params)){ 
			$default_template = EmundusHelperEmails::getEmail('assessors_set');
			$editor = JFactory::getEditor('tinymce');
			$params = array('mode' => 'simple');
			$mail_body = $editor->display( 'mail_body', $default_template->message, '100%', '400', '20', '20', false, 'mail_body', null, null, $params );
			$email .= '<input name="fnums" type="hidden" class="inputbox" id="fnums" value=\''.$fnums.'\' />';

			if(is_null($users))
			{

				$email = '<input type="text" name="ud[]" value=""/> ';
				$AllEmail_template = EmundusHelperEmails::getAllEmail(2);
				$email.='<select name="select_template" onChange="getTemplate(this);">
					<option value="%">-- '.JText::_( 'SELECT_TEMPLATE' ).' --</option>';
				foreach ($AllEmail_template as $email_template){
					$email.='<option value="'.$email_template->id.'">'.$email_template->subject.'</option>';
				}
				$email.='</select>';

				$email.= '<input placeholder="'.JText::_( 'SUBJECT' ).'" name="mail_subject" type="text" class="inputbox" id="mail_subject" value="'.$default_template->subject.'" size="100" style="width: inherit !important;" />';
				$email .= '<input placeholder="'.JText::_( 'EMAIL_TO' ).'" type="text" name="ud[]" value=""/> ';
				

				$email .= $mail_body.'<input type="submit" class="btn btn-large btn-success" name="applicant_email" value="'.JText::_( 'SEND_CUSTOM_EMAIL' ).'" >';
			}
			else
			{
				foreach ($users as $user) 
				{ 
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
		if(in_array('evaluation_result', $params)){
			$editor = JFactory::getEditor('tinymce');
			$params = array('mode' => 'simple');
			$mail_body = $editor->display( 'mail_body', '[NAME], ', '100%', '400', '20', '20', false, 'mail_body', null, null, $params );
			$email .= '<input name="fnums" type="hidden" class="inputbox" id="fnums" value=\''.$fnums.'\' />';

			$student_id = $jinput->get('jos_emundus_evaluations___student_id', null, 'INT'); //JRequest::getVar('jos_emundus_evaluations___student_id', null, 'GET', 'INT',0);
			$campaign_id = $jinput->get('jos_emundus_evaluations___campaign_id', null, 'INT'); //JRequest::getVar('jos_emundus_evaluations___campaign_id', null, 'GET', 'INT',0);
			$applicant = JFactory::getUser($student_id);
			
			$email .= '<fieldset>
				<legend> 
					<span class="editlinktip hasTip" title="'.JText::_('EMAIL_APPLICATION_RESULT').'::'.JText::_('EMAIL_APPLICATION_RESULT_TIP').'">
						<img src="'.JURI::Base().'media/com_emundus/images/icones/mail_replay_22x22.png" alt="'.JText::_('EMAIL_TO').'"/> '.JText::_( 'EMAIL_TO' ).' '.$applicant->name.' &bull; <i>'.$applicant->email.'</i> 
					</span>
				</legend>
				<div>';
				$email .= '
					<input name="mail_subject" type="text" class="inputbox" id="mail_subject" value="" size="100" style="width: inherit !important;" />
				<p>
					<input name="mail_to" type="hidden" class="inputbox input-xlarge" id="mail_to" value="'.$applicant->id.'" />
					<input name="campaign_id" type="hidden" class="inputbox" id="campaign_id" value="'.$campaign_id.'" size="100" />
				</div>';
				//$email .= '<p><label for="mail_body"> '.JText::_( 'MESSAGE' ).' </label><br/>';
				$email .= $mail_body;
				$email .= '
				</p>
					<input name="mail_attachments" type="hidden" class="inputbox" id="mail_attachments" value="" />
					<input name="mail_type" type="hidden" class="inputbox" id="mail_type" value="evaluation_result" />
				<p><div><input class="btn btn-large btn-success" type="submit" name="evaluation_result_email" value="'.JText::_( 'SEND_CUSTOM_EMAIL' ).'" ></div>
				</p>
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
			function deleteAttachment(id){
				var xhr = getXMLHttpRequest();
				xhr.onreadystatechange = function()
				{
					if (xhr.readyState == 4 && (xhr.status == 200 || xhr.status == 0))
					{
						if(xhr.responseText != "SQL Error"){ 
							$("em_dl_"+id).innerHTML = "";
							document.getElementById("em_dl_"+id).style.visibility="hidden";
						}else{
							alert(xhr.responseText);
						}
					}
				};
				xhr.open("GET", "index.php?option=com_emundus&controller=application&format=raw&task=delete_attachment&Itemid='.$itemid.'&id="+id, true);
				xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
				xhr.send("&id="+id);
			}</script>';
		}
		if(in_array('expert', $params)){
			$editor = JFactory::getEditor('tinymce');
			$params = array('mode' => 'simple');
			$mail_body = $editor->display( 'mail_body', '[NAME], ', '100%', '400', '20', '20', false, 'mail_body', null, null, $params );

			$student_id = $jinput->get('student_id', null, 'INT'); //JRequest::getVar('student_id', null, 'GET', 'INT',0);
			$campaign_id = $jinput->get('campaign_id', null, 'INT'); //JRequest::getVar('campaign_id', null, 'GET', 'INT',0);
			$applicant = JFactory::getUser($student_id);

			$experts = "";
			
			$email .= '<div>';
				$AllEmail_template = EmundusHelperEmails::getAllEmail(2);
				$email.='<select name="select_template" onChange="getTemplate(this);">
					<option value="%">-- '.JText::_( 'SELECT_TEMPLATE' ).' --</option>';
				foreach ($AllEmail_template as $email_template){
					$email.='<option value="'.$email_template->id.'">'.$email_template->subject.'</option>';
				}
				$email .='</select>';
				$email .= '<input placeholder="'.JText::_( 'SUBJECT' ).'" name="mail_subject" type="text" class="inputbox" id="mail_subject" value="" size="100" style="width: inherit !important;" />';
				$email .= '<input placeholder="'.JText::_( 'EMAIL_TO' ).'"  name="mail_to" type="text" class="inputbox" id="mail_to" value="'.$experts.'" size="100" style="width: 100% !important;" />';
				$email .= '<input name="fnums" type="hidden" class="inputbox" id="fnums" value=\''.$fnums.'\' />';
				$email .= '<input name="delete_attachment" type="hidden" class="inputbox" id="delete_attachment" value=0 />';
				/*$email .= '<input name="mail_from_name" type="hidden" class="inputbox input-xlarge" id="mail_from_name" value="" size="100" style="width: 100% !important;" />';
				$email .= '<input name="mail_from" type="hidden" class="inputbox input-xlarge" id="mail_from" value="" size="100" style="width: 100% !important;" />';
				$email .= '<input name="campaign_id" type="hidden" class="inputbox" id="campaign_id" value="'.$campaign_id.'" />
					<input name="student_id" type="hidden" class="inputbox" id="student_id" value="'.$student_id.'" />
				</div>';
				$email .= '<p><label for="mail_body"> '.JText::_( 'MESSAGE' ).' </label><br/>';*/
				$email .= $mail_body;
				$email .= '
				</p>
					<input name="mail_attachments" type="hidden" class="inputbox" id="mail_attachments" value="" />
					<input name="mail_type" type="hidden" class="inputbox" id="mail_type" value="expert" />
				<p><div><input class="btn btn-large btn-success" type="submit" name="expert" value="'.JText::_( 'SEND_CUSTOM_EMAIL' ).'" ></div>
				</p>';
			
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
			function deleteAttachment(id){
				var xhr = getXMLHttpRequest();
				xhr.onreadystatechange = function()
				{
					if (xhr.readyState == 4 && (xhr.status == 200 || xhr.status == 0))
					{
						if(xhr.responseText != "SQL Error"){ 
							$("em_dl_"+id).innerHTML = "";
							document.getElementById("em_dl_"+id).style.visibility="hidden";
						}else{
							alert(xhr.responseText);
						}
					}
				};
				xhr.open("GET", "index.php?option=com_emundus&controller=application&format=raw&task=delete_attachment&Itemid='.$itemid.'&id="+id, true);
				xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
				xhr.send("&id="+id);
			}</script>';
		}
		if(in_array('this_applicant', $params))
		{
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
		$email .= '</div>';
		$email .= '
<script>$(document).on("click", "input[type=\'submit\']", function() { if($("#mail_subject").val() == ""){$("#mail_subject").css("border", "2px solid red"); return false;} else document.pressed=this.name; }); </script>';
		$email .='<script>'.EmundusHelperJavascript::getTemplate().'</script>';

		return $email;
	}
	
	function getEmail($lbl)
	{
		$query = 'SELECT * FROM #__emundus_setup_emails WHERE lbl="'.mysql_real_escape_string($lbl).'"';
		$this->_db->setQuery( $query );
		return $this->_db->loadObject();
	}
	
	function getAllEmail($type=2)
	{
		$query = 'SELECT * FROM #__emundus_setup_emails WHERE type IN ('.$this->_db->Quote($type).') AND published=1';
		$this->_db->setQuery( $query );
		return $this->_db->loadObjectList();
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
		$config = JFactory::getConfig();
		
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
        $model=$this->getModel('campaign');

		// include model email for Tag
		include_once(JPATH_BASE.'/components/com_emundus/models/emails.php');
		$emails = new EmundusModelEmails;

		$mainframe = JFactory::getApplication();
		$db = JFactory::getDBO();
		$jinput = JFactory::getApplication()->input;
	
		$limitstart = JRequest::getVar('limitstart', null, 'POST', 'none',0);
		$filter_order = JRequest::getVar('filter_order', null, 'POST', null, 0);
		$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'POST', null, 0);
		$itemid = JRequest::getVar('Itemid', null, 'GET', null, 0);	
		
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
			$this->setRedirect('index.php?option=com_emundus&view=email&tmpl=component&desc=2&Itemid='.$itemid);
			return;
		}
		if ($message == '') {
			JError::raiseWarning( 500, JText::_( 'ERROR_YOU_MUST_PROVIDE_A_MESSAGE' ) );
			$this->setRedirect('index.php?option=com_emundus&view=email&tmpl=component&desc=2&Itemid='.$itemid);
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
				$campaign = $model->getCampaignByID($campaigns_id[$i]);
				$programme = $model->getProgrammeByCampaignID($campaigns_id[$i]);
			}

			// template replacements (patterns)
			$post = array(	'COURSE_LABEL' => @$programme['label'],
							'CAMPAIGN_LABEL' => @$campaign['label'],
							'SITE_URL' => JURI::base(),
							'USER_EMAIL' => $user->email
						 );
			$tags = $emails->setTags($user->id, $post);

			$body = preg_replace($tags['patterns'], $tags['replacements'], $message);

			if(!empty($user->email)){
				// mail function
				$mailer = JFactory::getMailer();

                $sender = array(
		            $email_from_sys,
		            $fromname
		        );

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
					$now = new DateTime(date("Y-m-d H:i:s"), new DateTimeZone($config->get('offset')));
                    $sql = "INSERT INTO `#__messages` (`user_id_from`, `user_id_to`, `subject`, `message`, `date_time`)
						VALUES ('".$from_id."', '".$user->id."', ".$db->quote($subject).", ".$db->quote($body).", ".$db->quote($now).")";
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
        $this->setRedirect('index.php?option=com_emundus&view=email&tmpl=component&layout=sent&desc=2', JText::_('REPORTS_MAILS_SENT').$info, 'message');

	}
	
	function sendApplicantEmail() {
		
		$current_user = JFactory::getUser();
		$config = JFactory::getConfig();

		if (!EmundusHelperAccess::asAccessAction(9, 'c'))	//email applicant
		{
			die(JText::_("ACCESS_DENIED"));
		}

		// include model email for Tag
		include_once(JPATH_BASE.'/components/com_emundus/models/emails.php');
		$emails = new EmundusModelEmails();
		
		$mainframe = JFactory::getApplication();

		$db	= JFactory::getDBO();

		// Model for GetCampaignWithID()
		$model=$this->getModel('campaign');

		$email_from_sys = $mainframe->getCfg('mailfrom');

		$cids = JRequest::getVar( 'ud', array(), 'post', 'array' );
		foreach ($cids as $cid){
			$params=explode('|',$cid);
			$users_id[] = intval($params[0]);
			$campaigns_id[] = intval($params[1]); 
		}

		$captcha	= 1;//JRequest::getInt( JR_CAPTCHA, null, 'post' );

		$from 		= JRequest::getVar( 'mail_from', null, 'post' );
		$from_id	= JRequest::getVar( 'mail_from_id', null, 'post' );
		$fromname	= JRequest::getVar( 'mail_from_name', null, 'post' );
		$subject	= JRequest::getVar( 'mail_subject', null, 'post' );
		$message	= JRequest::getVar( 'mail_body','','POST','STRING',JREQUEST_ALLOWHTML); 
		
		$fnums = $mainframe->input->get('fnums', null, 'RAW');
        $fnums = (array) json_decode(stripslashes($fnums));

		if ($captcha !== 1) {
			JError::raiseWarning( 500, JText::_( 'ERROR_NOT_A_VALID_POST' ) );
			$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&tmpl='.JRequest::getCmd( 'tmpl' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.JRequest::getCmd( 'Itemid' ));
			return;
		}
		if (count( $users_id ) == 0) {
			JError::raiseWarning( 500, JText::_( 'ERROR_NO_ITEMS_SELECTED' ) );
			$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&tmpl='.JRequest::getCmd( 'tmpl' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.JRequest::getCmd( 'Itemid' ));
			return;
		}
		if ($subject == '') {
			JError::raiseWarning( 500, JText::_( 'ERROR_YOU_MUST_PROVIDE_SUBJECT' ) );
			$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&tmpl='.JRequest::getCmd( 'tmpl' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.JRequest::getCmd( 'Itemid' ));
			return;
		}
		if ($message == '') {
			JError::raiseWarning( 500, JText::_( 'ERROR_YOU_MUST_PROVIDE_A_MESSAGE' ) );
			$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&tmpl='.JRequest::getCmd( 'tmpl' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.JRequest::getCmd( 'Itemid' ));
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
                $campaign = $model->getCampaignByID($campaigns_id[$i]);
                $programme = $model->getProgrammeByCampaignID($campaigns_id[$i]);
            }

            // template replacements (patterns)
            $post = array('COURSE_LABEL' => @$programme['label'],
                'CAMPAIGN_LABEL' => @$campaign['label'],
                'SITE_URL' => JURI::base(),
                'USER_EMAIL' => $user->email
            );
            $tags = $emails->setTags($user->id, $post);

            $from = preg_replace($tags['patterns'], $tags['replacements'], $from);
            $from_id = $user->id;
            $fromname = preg_replace($tags['patterns'], $tags['replacements'], $fromname);
            $to = $user->email;
            $subject = preg_replace($tags['patterns'], $tags['replacements'], $subject);
            $body = preg_replace($tags['patterns'], $tags['replacements'], $message);
            $body = $emails->setTagsFabrik($body, array($fnums[$i]));

            if (!empty($user->email)) {
                // mail function
				$mailer = JFactory::getMailer();

                $sender = array(
		            $email_from_sys,
		            $fromname
		        );

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
					$now = new DateTime(date("Y-m-d H:i:s"), new DateTimeZone($config->get('offset')));
                    $sql = "INSERT INTO `#__messages` (`user_id_from`, `user_id_to`, `subject`, `message`, `date_time`)
						VALUES ('" . $from_id . "', '" . $user->id . "', " . $db->quote($subject) . ", " . $db->quote($body) . ", ".$db->quote($now).")";
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
		$this->setRedirect('index.php?option=com_emundus&view=email&tmpl=component&layout=sent', JText::_('REPORTS_MAILS_SENT').$info, 'message');
	}
}
?>