<?php
/**
 * @version		$Id: list.php 14401 2013-03-26 14:10:00Z brivalland $
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
 * @subpackage	Content
 * @since 1.5
 */

class EmundusHelperList{

    function aggregation($array1, $array2, $array3 = array(), $array4 = array()){
        if(!empty($array2))
            $merge = array_merge($array1, $array2, $array3, $array4);
        else
            $merge = array_merge($array1, $array3, $array4);
        $newArray = array(); // le nouveau tableau dédoublonné
        $arrayTemp = array(); // contiendra les ids à éviter
        foreach($merge as $m)
        {
            if(!in_array( @$m['name'], $arrayTemp)) {
                $newArray[] = $m;
                $arrayTemp[] = $m['name'];
            }
        }
        return $newArray;
    }

    // Fonction de tri des tableaux
    function multi_array_sort($multi_array=array(), $sort_key, $sort=SORT_ASC){
        if(is_array($multi_array)){
            foreach ($multi_array as $key=>$row_array){
                if(is_array($row_array)){
                    $user_id = $row_array['user_id'];
                    @$key_array[$key] = @$row_array[$sort_key];
                }else{
                    return -1;
                }
            }
        }else{
            return -1;
        }
        if(!empty($key_array))
            array_multisort($key_array, $sort, $multi_array);
        return $multi_array;
    }

    function affectEvaluators(){
        $current_eval = JRequest::getVar('user', null, 'POST', 'none',0);
        $current_group = JRequest::getVar('groups', null, 'POST', 'none',0);
        $affect = '
		 	<fieldset>
				<legend><img src="'.JURI::base().'media/com_emundus/images/icones/kbackgammon_engine_22x22.png" alt="'.JText::_('COM_EMUNDUS_GROUPS_BATCH').'"/> '.JText::_('COM_EMUNDUS_GROUPS_AFFECT_TO_ASSESSORS').'</legend>
				<div id="assessor_group">
					<label for="ass1"><input type="radio" name="assessor" id="ass1" onclick="hidden_affect(this);" value="1">'.JText::_('COM_EMUNDUS_GROUPS_ASSESSOR_GROUP_FILTER').'</label>
					<div id="hidden_assessor_group">
						<select name="assessor_group">
							<option value="">'.JText::_('NONE').'</option>';
        foreach($this->groups as $groups) {
            $affect .= '<option value="'.$groups->id.'"';
            if($current_group == $groups->id) $affect .= ' selected';
            $affect .= '>'.$groups->label.'</option>';
        }
        $affect .= '</select>
					</div>
				</div>

				<div id="assessor_user">
					<label for="ass2"><input type="radio" name="assessor" id="ass2" onclick="hidden_affect(this);" value="2" checked="yes">'.JText::_('COM_EMUNDUS_GROUPS_ASSESSOR_USER_FILTER').'</label>
					<div id="hidden_assessor_user">
						<select name="assessor_user" onchange="hidden_affect(this);">
							<option value="">'.JText::_('NONE').'</option> ';
        foreach($this->evaluators as $eval_users) {
            $affect .= '<option value="'.$eval_users->id.'"';
            if($current_eval==$eval_users->id) $affect .= ' selected';
            $affect .= '>'.$eval_users->name.'</option>';
        }
        $affect .= '</select>
					</div>
				</div>
				<input type="submit" name="affect" class="green" onclick="document.pressed=this.name" value="'.JText::_('COM_EMUNDUS_GROUPS_AFFECT_SELECTED').'" />
				<input type="submit" name="unaffect" class="red" onclick="document.pressed=this.name" value="'.JText::_('COM_EMUNDUS_GROUPS_UNAFFECT_SELECTED').'" />

            </fieldset>';
        return $affect;
    }

    //check if an applicant is evaluated
    function getEvaluation($user_id, $campaign_id, $eval_id)
    {
        $query = 'SELECT id,user FROM #__emundus_evaluations WHERE student_id='.$user_id.' AND user='.$eval_id.' AND campaign_id='.$campaign_id;
        $this->_db->setQuery( $query );
        return $this->_db->loadObject();
    }

    //check if an applicant is evaluated
    function isEvaluatedBy($user_id, $campaign_id, $eval_id){
        $query = 'SELECT id,user FROM #__emundus_evaluations WHERE student_id='.$user_id.' AND user='.$eval_id.' AND campaign_id='.$campaign_id;
        $this->_db->setQuery( $query );
        return count($this->_db->loadObject())>0?true:false;
    }

    //check if the applicant is affected to the evaluator to be evaluated
    function isAffectedToMe($user_id, $campaign_id, $user_eval){
        $query = 'SELECT id FROM #__emundus_groups_eval ege WHERE ege.applicant_id  = '.$user_id.' AND ege.campaign_id='.$campaign_id.'  AND (ege.user_id='.$user_eval.' OR ege.group_id IN (select group_id from #__emundus_groups where user_id='.$user_eval.'))';
        $this->_db->setQuery( $query );
        return count($this->_db->loadObject())>0?true:false;
    }

    //get all the evaluator for an applicant
    function assessorsList($user_id,$campaign_id){
        $query = 'SELECT ege.id, ege.group_id, ege.user_id, ege.campaign_id
			FROM #__emundus_groups_eval ege
			WHERE ege.applicant_id = '.$user_id.'
			AND ege.campaign_id='.$campaign_id;
        $this->_db->setQuery( $query );
        // var_dump($query);
        return $this->_db->loadObjectList('id');
    }

    // get comment from an evaluator
    function getComment($user_id, $eval_id, $campaign_id){
        $query = 'SELECT comment FROM #__emundus_evaluations WHERE student_id = '.$user_id.' AND user = '.$eval_id.' AND campaign_id = '.$campaign_id;
        $this->_db->setQuery( $query );
        return $this->_db->loadResult();
    }

    // get files request by or for applicant
    function getFilesRequest($user_id, $campaign_id){
        $query = 'SELECT efr.time_date, efr.keyid, efr.attachment_id, efr.filename, efr.uploaded, efr.email, esa.value, esa.description
					FROM #__emundus_files_request as efr
					LEFT JOIN #__emundus_setup_attachments as esa ON esa.id=efr.attachment_id
					WHERE student_id = '.$user_id.' AND campaign_id = '.$campaign_id. '
					ORDER BY efr.time_date DESC, esa.ordering, esa.value ASC';
        $this->_db->setQuery( $query );
        return $this->_db->loadObjectList();
    }

    //get evaluators for each applicant
    function getUsersGroups(){
        $db = JFactory::getDBO();
        $query = 'SELECT eg.user_id, eg.group_id FROM #__emundus_groups eg';
        $db->setQuery( $query );
        return $db->loadObjectList();
    }

    // get usual user info
    function getUserInfo($user_id){
        $db = JFactory::getDBO();
        $query = 'SELECT epd.id, eu.firstname, eu.lastname, epd.gender, u.email, eu.profile
		FROM #__emundus_users eu
		LEFT JOIN #__users as u ON u.id=eu.user_id
		LEFT JOIN #__emundus_personal_detail as epd ON epd.user=eu.user_id
		WHERE eu.user_id='.$user_id;
        $db->setQuery( $query );
        return $db->loadObjectList();
    }

    // get photo for applicant
    function getAvatar($user_id){
        $db = JFactory::getDBO();
        $query = 'SELECT filename FROM #__emundus_uploads WHERE attachment_id = 10 AND user_id = '.$user_id;
        $db->setQuery( $query );
        return $db->loadResult();
    }

    // get profile id for an applicant (if selected applicant, get 'result for' profile)
    function getProfile($user_id){
        $db = JFactory::getDBO();
        $query = 'SELECT profile FROM #__emundus_users WHERE user_id = '.$user_id;
        $db->setQuery( $query );
        $profile = $db->loadResult();
        if($profile == 8){
            $query = 'SELECT result_for FROM #__emundus_final_grade WHERE student_id = '.$user_id;
            $db->setQuery( $query );
            return $db->loadResult();
        }
        return $profile;
    }

    // get details for a profile id
    function getProfileDetails($pid){
        $db = JFactory::getDBO();
        $query = 'SELECT * FROM #__emundus_setup_profiles WHERE id = '.$pid;
        $db->setQuery( $query );
        $profile = $db->loadAssocList();
        return $profile;
    }

    // get upload list to create action block for each users
    function getUploadList($user){
        $db = JFactory::getDBO();
        $query = 'SELECT attachments.id, uploads.filename, uploads.description, attachments.lbl, attachments.value
					FROM #__emundus_uploads AS uploads
					LEFT JOIN #__emundus_setup_attachments AS attachments ON uploads.attachment_id=attachments.id
					WHERE uploads.user_id = '.$user.'
					ORDER BY attachments.ordering';
        $db->setQuery( $query );
        return $db->loadObjectList();
    }

    // @description get forms list to create action block for each users
    // @param	int applicant user id
    // @return 	array Menu links of all forms needed to apply
    function getFormsList($user_id, $fnum="0", $formids=null, $profile_id = null){
        $formsList = [];

        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'menu.php');
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'profile.php');
        $m_profile 	= new EmundusModelProfile();
        $h_menu 	= new EmundusHelperMenu();
        $infos 		= $m_profile->getFnumDetails($fnum);
        $profile 	= $m_profile->getProfileByCampaign($infos['campaign_id']);
        $workflow_profiles = $m_profile->getWorkflowProfilesByCampaign($infos['campaign_id']);

        if(!empty($profile_id)) {
            $profile['profile_id'] = $profile_id;
        }

        $formsList = $h_menu->buildMenuQuery($profile['profile_id'], $formids);

        //TODO : Break pdf export, not the good solution for zip
        /*foreach($workflow_profiles as $workflow_profile) {
            if ($workflow_profile != $profile['profile_id']) {
                $workflow_form_list = $h_menu->buildMenuQuery($workflow_profile, $formids);
                $formsList = array_merge($formsList, $workflow_form_list);
            }
        }*/

        return $formsList;
    }

    // @description get forms list to create action block by profile_id
    // @param   int profile id
    // @param   boolean checklevel : if true will check J! Access Level of logged user
    // @return  array Menu links of all forms needed to apply
    function getFormsListByProfileID($profile_id, $checklevel=true){
        require_once(JPATH_COMPONENT.DS.'helpers'.DS.'menu.php');
        return EmundusHelperMenu::buildMenuQuery($profile_id, null, $checklevel);
    }

    // @description Get applicants list
    // @param	int  	1 for submitted application, 0 for incomplete, 2 for all applicants
    // @param	string	Year(s) of the campaigns comma separated, can be something like 2012-2013
    // @return	array	Object of users ID and campaign info
    function getApplicants($submitted, $year){
        $db = JFactory::getDBO();
        $query = 'SELECT ecc.applicant_id, esc.label, ecc.submitted, ecc.date_submitted
					FROM #__emundus_campaign_candidature AS ecc
					LEFT JOIN #__emundus_setup_campaigns AS esc ON ecc.campaign_id=esc.id ';
        if ($submitted == 0)
            $query .= ' WHERE (ecc.submitted = '.$submitted.' OR ecc.submitted IS NULL) ';
        if ($submitted == 1)
            $query .= ' WHERE ecc.submitted = '.$submitted;
        else
            $query .= ' WHERE 1 ';
        $query .= '  AND esc.year IN ("'.$year.'") ';
        //$query .= ' ORDER BY ecc.submitted, esc.label';
        $db->setQuery( $query );

        return $db->loadObjectList();
    }

    // @description Get applicants list
    // @param	int  	1 for submitted application, 0 for incomplete, 2 for all applicants
    // @param	string	Year(s) of the campaigns comma separated, can be something like 2012-2013
    // @return	array	Object of users ID and campaign info
    function getCampaignsByApplicantID($user, $submitted, $year){
        $db = JFactory::getDBO();
        $query = 'SELECT esc.year, ecc.applicant_id, esc.label, ecc.submitted, ecc.date_submitted, ecc.date_time, esc.training
					FROM #__emundus_campaign_candidature AS ecc
					LEFT JOIN #__emundus_setup_campaigns AS esc ON ecc.campaign_id=esc.id ';
        $query .= ' WHERE 1 ';
        if ($submitted == 0)
            $query .= ' AND (ecc.submitted = '.$submitted.' OR ecc.submitted IS NULL) ';
        if ($submitted == 1)
            $query .= ' AND ecc.submitted = '.$submitted;
        $query .= '  AND ecc.applicant_id = '.$user;

        if($year == "%")
            $query .= ' AND esc.year like "%" ';
        else
            $query .= '  AND esc.year IN ("'.$year.'") ';
        //$query .= ' ORDER BY ecc.submitted, esc.label';
        $db->setQuery( $query );
//echo str_replace('#_', 'jos', $query);
        return $db->loadObjectList();
    }

    /*
    ** @description Get the list of campaign applied by applicant.
    ** @param array $users List of users to display in page.
    ** @param array $params $params['submitted'], $params['year'],... have a look in view.html.php
    ** @return array Array of HTML to display in page for "Applicant for" column.
    */
    function createApplicantsCampaignsBlock($users, $params){
        $actions = array();
        foreach($users as $user) {
            if (is_object($user)) {
                $json  = json_encode($user);
                $user = json_decode($json, true);
            }

            $campaigns_list = & EmundusHelperList::getCampaignsByApplicantID($user['user_id'], $params['submitted'], $params['year']);
//	print_r($campaigns_list);
            @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '<div class="em_campaigns" id="em_campaigns_'.$user['user_id'].'">';
            @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '<ul>';
            foreach($campaigns_list as $c) {
                $dt = $params['submitted']==0?$c->date_time:$c->date_submitted;
                @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '<li>';
                @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '<div class="em_campaigns_'.$c->training.'" >';
                @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '<span class="em_campaign_label">'.$c->label.'</span> | '.$c->year.' | '.JHtml::_('date', $dt, JText::_('DATE_FORMAT_LC2'));
                @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '</div>';
                @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '</li>';
            }
            @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '</ul>';
            @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '</div>';
        }
        return $actions;
    }


    /*
    ** @description Get icone on first column.
    ** @param array $users List of users to display in page.
    ** @param array $params Type of action that can be done for user (checkbox / gender /email / details / photo / upload / attachments / forms / evaluation / selection_outcome).
    ** @return array Array of HTML to display in page for action block indexed by user ID.
    */
    function createActionsBlock($users, $params){
        $itemid = JRequest::getVar('Itemid', null, 'GET', 'none', 0);
        $s = JRequest::getVar('s', null, 'POST', 'none', 0);
        $actions = array();
        $ids = array();
        //print_r($users);
        foreach($users as $user) {
            if (is_object($user)) {
                $json  = json_encode($user);
                $user = json_decode($json, true);
            }
            //$val = $user['user_id'].",".@$user['campaign_id'];
            $val = $user['user_id'].",".@$user['user'].",".@$user['campaign_id'];
            if( !in_array($val, $ids) ){
                $ids[] = $val;
                //echo $val."*";
                //print_r($user);
                $user_info = EmundusHelperList::getUserInfo($user['user_id']);
                $avatar = EmundusHelperList::getAvatar($user['user_id']);
                @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '<div class="em_actions" id="em_actions_'.$user['user_id'].'">';
                if(in_array('checkbox',$params)){
                    $checked = ($s==$user['user_id'])?'checked':'';
                    @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '<div class="em_checkbox" id="em_checkbox_'.$user['user_id'].'"><input id="cb'.$user['user_id'].'|'.$user['campaign_id'].'" type="checkbox" name="ud[]" value="'.$user['user_id'].'|'.$user['campaign_id'].'" '.$checked.' /></div>';
                }
                @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '<div class="em_user_id" id="em_user_id_'.$user['user_id'].'">#'.$user['user_id'].'</div>';
                if(in_array('gender',$params)){
                    @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '<div class="em_gender" id="em_gender_'.$user['user_id'].'">';
                    @$actions[$user['user_id']][@$user['user']][@$user['campaign_id']] .= '<span class="editlinktip hasTip" title="'.JText::_('COM_EMUNDUS_MAILS_MAIL_TO').'::'.$user_info[0]->email.'">';
                    if (strtolower($user_info[0]->gender) == 'male')
                        @$actions[$user['user_id']][@$user['user']][@$user['campaign_id']] .= '<a href="mailto:'.$user_info[0]->email.'"><img src="'.$this->baseurl.'/media/com_emundus/images/icones/user_male.png" width="22" height="22" align="bottom" /></a>';
                    elseif (strtolower($user_info[0]->gender) == 'female')
                        @$actions[$user['user_id']][@$user['user']][@$user['campaign_id']] .= '<a href="mailto:'.$user_info[0]->email.'"><img src="'.$this->baseurl.'/media/com_emundus/images/icones/user_female.png" width="22" height="22" align="bottom" /></a>';
                    else
                        @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '<a href="mailto:'.$user_info[0]->email.'"><img src="'.$this->baseurl.'/media/com_emundus/images/icones/mailreminder.png" width="22" height="22" align="bottom" /></a>';
                    @$actions[$user['user_id']][@$user['user']][@$user['campaign_id']] .= '</span>';
                    @$actions[$user['user_id']][@$user['user']][@$user['campaign_id']] .= '</div>';
                }
                if(in_array('email',$params)){
                    @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '<div class="em_email" id="em_email_'.$user['user_id'].'">';
                    @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '<span class="editlinktip hasTip" title="'.JText::_('COM_EMUNDUS_MAILS_MAIL_TO').'::'.$user_info[0]->email.'">';
                    @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '<a href="mailto:'.$user_info[0]->email.'"><img src="'.$this->baseurl.'/media/com_emundus/images/icones/mailreminder.png" width="22" height="22" align="bottom" /></a>';
                    @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '</span>';
                    @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '</div>';
                }
                if(in_array('details',$params)){
                    @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '<div class="em_details" id="em_details_'.$user['user_id'].'">';
                    @$actions[@$user['user_id']][@$user['user']][@$user['campaign_id']] .= '<a class="modal" rel="{handler:\'iframe\',size:{x:window.getWidth()*0.8,y:window.getHeight()*0.9}}" href="index.php?option=com_emundus&view=application&sid='.@$user['user_id'].'&campaign_id='.@$user['campaign_id'].'&rowid='.@$user['evaluation_id'].'&Itemid='.$itemid.'&tmpl=component&iframe=1"><img height="16" width="16" align="bottom" title="'.JText::_('COM_EMUNDUS_DETAILS').'" src="'.$this->baseurl.'/media/com_emundus/images/icones/viewmag_16x16.png"/></a>';
                    @$actions[@$user['user_id']][@$user['user']][@$user['campaign_id']] .= '</div>';
                }
                if(in_array('upload',$params)){
                    @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '<div class="em_upload" id="em_upload_'.$user['user_id'].'">';
                    @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '<span class="editlinktip hasTip" title="'.JText::_('COM_EMUNDUS_APPLICATION_UPLOAD_FILE_FOR_STUDENT').'::'.JText::_('COM_EMUNDUS_APPLICATION_YOU_CAN_ATTACH_A_DOCUMENT_FOR_THE_STUDENT_THRU_THAT_LINK').'"><a rel="{handler:\'iframe\',size:{x:window.getWidth()*0.8,y:window.getHeight()*0.9}}" href="'.$this->baseurl.'/index.php?option=com_fabrik&view=form&formid=67&rowid=&jos_emundus_uploads___user_id[value]='. $user['user_id'].'&student_id='. $user['user_id'].'&tmpl=component&iframe=1" target="_self" class="modal"><img src="'.$this->baseurl.'/media/com_emundus/images/icones/attach_16x16.png" alt="'.JText::_('COM_EMUNDUS_UPLOAD').'" title="'.JText::_('COM_EMUNDUS_UPLOAD').'" width="16" height="16" align="bottom" /></a></span> ';
                    @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '</div>';
                }
                if(in_array('photo',$params)){
                    if(!empty($avatar)){
                        @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '<div class="em_photo" id="em_photo_'.$user['user_id'].'"><span class="editlinktip hasTip" title="'.JText::_('COM_EMUNDUS_APPLICATION_OPEN_PHOTO_IN_NEW_WINDOW').'::">';
                        $folder = $this->baseurl.EMUNDUS_PATH_REL.$user['user_id'];
                        @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '<a href="'.$folder.'/'.$avatar.'" target="_blank" class="modal"><img src="'.$folder.'/tn_'.$avatar.'" width="60" /></a>';
                        @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '</span></div>';
                    }
                }

                if(in_array('attachments',$params)){
                    $uploads = EmundusHelperList::getUploadList($user['user_id']);
                    @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '<div class="em_attachments" id="em_attachments_'.$user['user_id'].'"><div id="container" class="emundusraw">';
                    @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '<ul id="emundus_nav"><li><a href="#"><img src="'.$this->baseurl.'/media/com_emundus/images/icones/pdf.png" alt="'.JText::_('COM_EMUNDUS_ATTACHMENTS_ATTACHMENTS').'" title="'.JText::_('COM_EMUNDUS_ATTACHMENTS_ATTACHMENTS').'" width="22" height="22" align="absbottom" /></a>';
                    @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '<ul>';
                    foreach ( $uploads as $row ) {
                        @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '<li>';
                        if ($row->description != '') $link = $row->value.' (<em>'.$row->description.'</em>)';
                        else $link = $row->value;
                        @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '<a href="'.$this->baseurl.'/'.EMUNDUS_PATH_REL.$user['user_id'].'/'.$row->filename.'" target="_new">'.$link.'</a>';
                        @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '</li>';
                    }
                    @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '</ul></li>';
                    @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '</ul>';
                    @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '</div></div>';
                }
                if(in_array('forms',$params)){
                    $forms = EmundusHelperList::getFormsList($user['user_id']);
                    @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '<div class="em_forms" id="em_forms_'.$user['user_id'].'"><div id="container" class="emundusraw">';
                    @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '<ul id="emundus_nav"><li><a href="#"><img src="'.$this->baseurl.'/media/com_emundus/images/icones/folder_documents.png" alt="'.JText::_('COM_EMUNDUS_ATTACHMENTS_FORMS').'" title="'.JText::_('COM_EMUNDUS_ATTACHMENTS_FORMS').'" width="22" height="22" align="absbottom" /></a><ul>';
                    if (count($forms) > 0) {
                        foreach ( $forms as $row ) {
                            @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '<li>';
                            @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '<a href="'.$this->baseurl.'/index.php?option=com_fabrik&view=form&formid='.$row->form_id.'&random=0&rowid='.$user['user_id'].'&usekey=user&Itemid='.$itemid.'" target="_blank" >'.$row->label.'</a>';
                            @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '</li>';
                        }
                    }
                    @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '</ul></li>';
                    @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '</ul>';
                    @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '</div></div>';
                }
                if(in_array('evaluation',$params)){
                    @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '<div class="em_evaluation" id="em_evaluation_'.$user['user_id'].'_'.$user['campaign_id'].'">';
                    if(!empty($this->evaluation[$user['user_id']][$user['user']]))
                        @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= $this->evaluation[$user['user_id']][$user['user']][$user['campaign_id']];
                    @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '</div>';
                }
                if(in_array('letter',$params)){
                    if (!empty($user['final_grade'])) {
                        $letter = '<a rel="{handler:\'iframe\',size:{x:window.getWidth()*0.8,y:window.getHeight()*0.9},onClose:function(){delayAct('.$user['user_id'].', '.$user['campaign_id'].');}}" href="'.$this->baseurl.'/index.php?option=com_emundus&view=email&layout=letters&jos_emundus_evaluations___student_id='.$user['user_id'].'&jos_emundus_evaluations___campaign_id='.$user['campaign_id'].'&student_id='. $user['user_id'].'&jos_emundus_evaluations___id='.@$user['evaluation_id'].'&tmpl=component&iframe=1&Itemid='.$itemid.'" target="_self" name="" class="modal"><img title="'.JText::_( 'COM_EMUNDUS_EMAILS_SEND_RESULT_BY_EMAIL' ).'" src="'.$this->baseurl.'/media/com_emundus/images/icones/mail_post_to.png" /></a>';
                        @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '<div class="em_letter" id="em_letter_'.$user['user_id'].'_'.$user['campaign_id'].'">';
                        @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= $letter;
                        @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '</div>';
                    } else {
                        @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '<div class="em_letter" id="em_letter_'.$user['user_id'].'_'.$user['campaign_id'].'">';
                        @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= ''; //JText::_("COM_EMUNDUS_EVALUATION_UPDATE_EVALUATION");
                        @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '</div>';
                    }
                }
                if(in_array('expert',$params)){
                    $expert = '<a rel="{handler:\'iframe\',size:{x:window.getWidth()*0.8,y:window.getHeight()*0.9},onClose:function(){delayAct('.$user['user_id'].', '.$user['campaign_id'].');}}" href="'.$this->baseurl.'/index.php?option=com_emundus&view=email&layout=expert&campaign_id='.$user['campaign_id'].'&student_id='. $user['user_id'].'&tmpl=component&iframe=1&Itemid='.$itemid.'" target="_self" name="" class="modal"><img title="'.JText::_( 'COM_EMUNDUS_APPLICATION_SEND_APPLICATION_BY_EMAIL' ).'" src="'.$this->baseurl.'/media/com_emundus/images/icones/add_user_22x22.png" /></a>';
                    @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '<div class="em_expert" id="em_expert'.$user['user_id'].'_'.$user['campaign_id'].'">';
                    @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= $expert;
                    @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '</div>';
                }
                if(in_array('selection_outcome',$params)){
                    @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '<div class="em_selection_outcome" id="em_selection_outcome_'.$user['user_id'].'_'.$user['campaign_id'].'">';
                    @$actions[@$user['user_id']][@$user['user']][@$user['campaign_id']] .= $this->selection[$user['user_id']][@$user['campaign_id']];
                    @$actions[@$user['user_id']][@$user['user']][@$user['campaign_id']] .= '</div>';
                }
                @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '</div>';
            }
        }
        return $actions;
    }

    /*
    ** @description Create cellule for administrative validation.
    ** @param array $users List of user to display in page.
    ** @param array $params Type of validation needed.
    ** @return array Arary of HTML to display in page for action block indexed by user ID.
    */
    function createValidateBlock($users, $params){
        $itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);
        $validate = array();
//echo '<hr>';
        $validate_details = EmundusHelperList::getElementsDetailsByID('"'.implode('","', $params).'"');
//print_r($validate_details);
        foreach($users as $user) {
            foreach($validate_details as $vd) {
                if(!EmundusHelperAccess::isAdministrator($user['user_id']) && !EmundusHelperAccess::isCoordinator($user['user_id'])) {
                    if ($user[$vd->element_name]>0){
                        $img = 'tick.png';
                        $btn = 'unvalidate|'.$user['user_id'];
                        $alt = JText::_('COM_EMUNDUS_FORMS_VALIDATED').'::'.JText::_('COM_EMUNDUS_FORMS_VALIDATED_NOTE');
                    } else {
                        $img = 'publish_x.png';
                        $btn = 'validate|'.$user['user_id'];
                        $alt = JText::_('COM_EMUNDUS_FORMS_UNVALIDATED').'::'.JText::_('COM_EMUNDUS_FORMS_UNVALIDATED_NOTE');
                    }
                    $id = $vd->tab_name.'.'.$vd->element_name.'.'.$user['user_id'];
                    @$validate[$user['user_id']] .= '<div class="em_validation" id="'.$id.'"><span class="hasTip" title="'.$alt.'">';
                    @$validate[$user['user_id']] .= '<input type="image" src="'.JURI::base().'media/com_emundus/images/icones/'.$img.'" onclick="validation('.$user['user_id'].',\''.$user[$vd->element_name].'\', \''.$id.'\');" >';
                    //@$validate[$user['user_id']] .= '<img src="'.JURI::base().'/media/com_emundus/images/icones/'.$img.'" onclick="validation('.$user['user_id'].',\''.$user[$vd->element_name].'\', \''.$id.'\');" >';
                    @$validate[$user['user_id']] .= '</span></div> '.$vd->element_label.'<br>';
                } else {
                    @$validate[$user['user_id']] .= '<img src="'.JURI::base().'media/com_emundus/images/icones/'.$btn.'" /> '.$vd->element_label.'<br>';
                }
            }
        }
        return $validate;
    }

    // @description	Create icones for selection outcome
    // @param 	array Array of user id
    // @return 	array Array of HTML to display in page
    function createSelectionBlock($users){
        $itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);
        $selection = array();
        //final grade
        $final_grade = @EmundusHelperFilters::getFinal_grade();
        $grade = explode('|', $final_grade['final_grade']['sub_labels']);
        $sub_values = explode('|', $final_grade['final_grade']['sub_values']);
        foreach($sub_values as $sv) $p_grade[]="/".$sv."/";

        $ids = array();
        foreach($users as $user) {
            if( !in_array($user['user_id'].",".@$user['campaign_id'], $ids) ){
                $ids[] = $user['user_id'].",".@$user['campaign_id'];

                @$selection[$user['user_id']][$user['campaign_id']] .= '<div class="emundusraw">';
                if (isset($user['final_grade'])) {
                    $fg_txt = preg_replace($p_grade, $grade, $user['final_grade']);
                    @$selection[$user['user_id']][$user['campaign_id']] .= '<a rel="{handler:\'iframe\',size:{x:window.getWidth()*0.8,y:window.getHeight()*0.8},onClose:function(){delayAct('.$user['user_id'].', '.$user['campaign_id'].');}}" href="'.$this->baseurl.'/index.php?option=com_fabrik&view=form&formid=39&random=0&rowid='.$user['row_id'].'&usekey=id&student_id='. $user['user_id'].'&jos_emundus_final_grade___campaign_id[value]='. $user['campaign_id'].'&tmpl=component&iframe=1&Itemid='.$itemid.'" target="_self" class="modal">';
                    if ($user['final_grade']!= -1 && $user['final_grade'] != '') {
                        if ($user['final_grade'] == 2)
                            $final_grade = '<img src="'.$this->baseurl.'/images/M_images/edit.png" alt="'.JText::_($fg_txt).'" title="'.JText::_($fg_txt).'" width="16" height="16" align="absbottom" /> ';
                        elseif ($user['final_grade'] == 3 || $user['final_grade'] == 1)
                            $final_grade = '<img src="'.$this->baseurl.'/images/M_images/edit.png" alt="'.JText::_($fg_txt).'" title="'.JText::_($fg_txt).'" width="16" height="16" align="absbottom" /> ';
                        elseif ($user['final_grade'] == 4)
                            $final_grade = '<img src="'.$this->baseurl.'/images/M_images/edit.png" alt="'.JText::_($fg_txt).'" title="'.JText::_($fg_txt).'" width="16" height="16" align="absbottom" /> ';
                        @$selection[$user['user_id']][$user['campaign_id']] .= $final_grade;
                    }
                    @$selection[$user['user_id']][$user['campaign_id']] .= '</a>';
                    @$selection[$user['user_id']][$user['campaign_id']] .= ' <input type="image" src="'.$this->baseurl.'/media/com_emundus/images/icones/cancel_selection.png" name="delete_eval" width="16" height="16" onclick="document.pressed=\'delete_eval|'.$user['user_id'].'-'.$user['campaign_id'].'\'" alt="'.JText::_('DELETE_SELECTION_OUTCOME').'" title="'.JText::_('DELETE_SELECTION_OUTCOME').'"  align="absbottom" />';
                } else
                    @$selection[$user['user_id']][$user['campaign_id']] .= '<a rel="{handler:\'iframe\',size:{x:window.getWidth()*0.8,y:window.getHeight()*0.8},onClose:function(){delayAct('.$user['user_id'].', '.$user['campaign_id'].');}}" href="'.$this->baseurl.'/index.php?option=com_fabrik&c=form&view=form&formid=39&tableid=41&rowid='.$user['row_id'].'&jos_emundus_final_grade___student_id[value]='.$user['user_id'].'&jos_emundus_final_grade___campaign_id[value]='.$user['campaign_id'].'&jos_emundus_final_grade___result_for[value]='.$user['profile'].'&student_id='. $user['user_id'].'&tmpl=component&iframe=1&Itemid='.$itemid.'" target="_self" class="modal"><img src="'.$this->baseurl.'/media/com_emundus/images/icones/add.png" alt="'.JText::_("DEFINE_SELECTION_OUTCOME").'" title="'.JText::_("DEFINE_SELECTION_OUTCOME").'" width="16" height="16" align="absbottom" /></a>';
                @$selection[$user['user_id']][$user['campaign_id']] .= '</div>';
            }
        }
        return $selection;
    }

    // Create icone for evaluation
    function createEvaluationBlock($users, $params){
        $itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);
        $eval = array();
        $current_user = JFactory::getUser();
        $ids = array();
        //die(print_r($users));
        //echo '<hr>';
        foreach($users as $user) {
            $val = $user['user_id'].",".@$user['user'].",".@$user['campaign_id'];

            if( !in_array($val, $ids) ) {
                $ids[] = $val;
                $evaluation = (!empty($user['user']) && !empty($user['campaign_id']))?EmundusHelperList::getEvaluation($user['user_id'], $user['campaign_id'], $user['user']):array();
                $isEvalByMe = EmundusHelperList::isEvaluatedBy($user['user_id'], $user['campaign_id'], $current_user->id);
                $myAffect = EmundusHelperList::isAffectedToMe($user['user_id'],$user['campaign_id'], $current_user->id);
                $pid = EmundusHelperList::getProfile($user['user_id']);
                $profile = EmundusHelperList::getProfileDetails($pid);
                $form_eval = !empty($profile[0]['evaluation'])?$profile[0]['evaluation']:29;

                $add = '<a rel="{handler:\'iframe\',size:{x:window.getWidth()*0.8,y:window.getHeight()*0.9},onClose:function(){delayAct('.$user['user_id'].', '.$user['campaign_id'].');}}" href="'.$this->baseurl.'/index.php?option=com_fabrik&c=form&view=form&formid='.$form_eval.'&tableid=31&rowid=&jos_emundus_evaluations___student_id[value]='.$user['user_id'].'&jos_emundus_evaluations___campaign_id[value]='.$user['campaign_id'].'&student_id='. $user['user_id'].'&tmpl=component&iframe=1&Itemid='.$itemid.'" target="_self" class="modal"><img title="'.JText::_( 'COM_EMUNDUS_FILES_ADD_EVALUATION' ).'" src="'.$this->baseurl.'/media/com_emundus/images/icones/add.png" /></a>';

                $edit = '<a rel="{handler:\'iframe\',size:{x:window.getWidth()*0.8,y:window.getHeight()*0.9},onClose:function(){delayAct('.$user['user_id'].', '.$user['campaign_id'].');}}" href="'.$this->baseurl.'/index.php?option=com_fabrik&view=form&formid='.$form_eval.'&random=0&rowid='.@$user['evaluation_id'].'&usekey=id&student_id='. $user['user_id'].'&tmpl=component&iframe=1&jos_emundus_evaluations___campaign_id='.@$user['campaign_id'].'&Itemid='.$itemid.'" target="_self" name="" class="modal"><img title="'.JText::_( 'COM_EMUNDUS_EVALUATION_UPDATE_EVALUATION' ).'" src="'.$this->baseurl.'/images/M_images/edit.png" /></a>';

                $view = '<a rel="{handler:\'iframe\',size:{x:window.getWidth()*0.8,y:window.getHeight()*0.9}}" href="'.$this->baseurl.'/index.php?option=com_fabrik&view=details&fabrik='.$form_eval.'&random=0&rowid='.@$user['evaluation_id'].'&usekey=id&student_id='. $user['user_id'].'&tmpl=component&iframe=1&Itemid='.$itemid.'" target="_self" name="" class="modal"><img title="'.JText::_( 'COM_EMUNDUS_EVALUATION_VIEW_EVALUATION' ).'" src="'.$this->baseurl.'/media/com_emundus/images/icones/zoom_application.png" /></a>';

                $delete = '<input type="image" src="'.$this->baseurl.'/media/com_emundus/images/icones/b_drop.png" name="delete" onclick="document.pressed=\'delete_eval|'.$user['user_id'].'-'.$user['evaluation_id'].'\'" alt="'.JText::_('COM_EMUNDUS_EVALUATION_DELETE_EVALUATION').'" title="'.JText::_('COM_EMUNDUS_EVALUATION_DELETE_EVALUATION').'" />';

                if( (!EmundusHelperAccess::isAdministrator($current_user->id) && !EmundusHelperAccess::isCoordinator($current_user->id)) && $this->evaluators_can_see > 1 ) {
                    $canview = true;
                    $canedit = true;
                } elseif ($this->evaluators_can_see > 0) {
                    $canview = true;
                    $canedit = false;
                } else {
                    $canview = false;
                    $canedit = false;
                }
                if(EmundusHelperAccess::isAdministrator($current_user->id) || EmundusHelperAccess::isCoordinator($current_user->id)) {
                    $canedit = true;
                    $candelete = true;
                }else{
                    $candelete = false;
                }

                if(count($evaluation) > 0) {
                    if($isEvalByMe) {
                        if(in_array('view',$params) && $canview)
                            @$eval[$user['user_id']][$user['user']][$user['campaign_id']] .= $view;
                        if(in_array('edit',$params) && $canedit)
                            @$eval[$user['user_id']][$user['user']][$user['campaign_id']] .= $edit;
                        if(in_array('delete',$params))
                            @$eval[$user['user_id']][$user['user']][$user['campaign_id']] .= $delete;
                    } else {
                        if($this->multi_eval == 1) {
                            if(in_array('add',$params))
                                @$eval[$user['user_id']][$user['user']][$user['campaign_id']] .= $add;
                            if(in_array('view',$params) && $canview)
                                @$eval[$user['user_id']][$user['user']][$user['campaign_id']] .= $view;
                            if(in_array('edit',$params) && $canedit)
                                @$eval[$user['user_id']][$user['user']][$user['campaign_id']] .= $edit;
                            if(in_array('delete',$params) && $candelete)
                                @$eval[$user['user_id']][$user['user']][$user['campaign_id']] .= $delete;
                        } else {
                            if(in_array('view',$params) && $canview)
                                @$eval[$user['user_id']][$user['user']][$user['campaign_id']] .= $view;
                            if(in_array('edit',$params) && $canedit)
                                @$eval[$user['user_id']][$user['user']][$user['campaign_id']] .= $edit;
                            if(in_array('delete',$params) && $candelete)
                                @$eval[$user['user_id']][$user['user']][$user['campaign_id']] .= $delete;
                        }
                    }
                } else {
                    if(in_array('add',$params))
                        @$eval[$user['user_id']][$user['user']][$user['campaign_id']] .= $add;
                }
            }
        }
        return $eval;
    }


    function createEvaluatorBlock($users, $params){
        $limitstart = JRequest::getVar('limitstart', null, 'GET', 'none',0);
        $filter_order = JRequest::getVar('filter_order', null, 'GET', 'none',0);
        $filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'GET', 'none',0);
        $itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);
        $evaluator = array();
        $ids = array();
        $current_user = JFactory::getUser();
        $group=array();
        $eval=array();

        foreach ($users as $user) {
            if (!in_array($user['user_id'],$ids)) {
                $ids[] = $user['user_id'];
            }
            $assessors = EmundusHelperList::assessorsList($user['user_id'],$user['campaign_id']); // applicant + campaign

            foreach ($assessors as $ass) {
                if (!empty($ass->group_id) && isset($ass->group_id)) { // if group
                    if (!isset($group[$user['user_id']][$user['campaign_id']][$ass->group_id])){
                        $group[$user['user_id']][$user['campaign_id']][$ass->group_id]=true;
                        $uList = '<ul>';
                        foreach ($this->users_groups as $ug) {
                            if ($ug->group_id == $ass->group_id) {
                                $usr = JUser::getInstance($ug->user_id);
                                $uList .= '<li>'.$usr->name.'</li>';
                            }
                        }
                        $uList .= '</ul>';
                        if (in_array('delete',$params)) {
                            $img = '<span class="editlinktip hasTip" title="'.JText::_('COM_EMUNDUS_GROUPS_DELETE_ASSESSOR').' : '.$this->groups[$ass->group_id]->label.'::'.JText::_('COM_EMUNDUS_GROUPS_DELETE_ASSESSOR_TXT').'"><a href="index.php?option=com_emundus&controller=evaluation&task=delassessor&aid='.$user['user_id'].'&pid='.$ass->group_id.'&uid='.$ass->user_id.'&cid='.$ass->campaign_id.'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$itemid.'"><img src="'.JURI::base().'media/com_emundus/images/icones/clear_left_16x16.png" alt="'.JText::_('DEL_ASSESSOR').'" align="absbottom" /></a></span> ';
                            @$evaluator[$user['user_id']][$user['campaign_id']] .= '<span class="editlinktip hasTip" title="'.JText::_('COM_EMUNDUS_GROUPS_GROUP_MEMBERS').'::'.$uList.'">'.$this->groups[$ass->group_id]->label.'</span> '.$img.'<br />';
                        } else {
                            @$evaluator[$user['user_id']][$user['campaign_id']] .= '<span class="editlinktip hasTip" title="'.JText::_('COM_EMUNDUS_GROUPS_GROUP_MEMBERS').'::'.$uList.'">'.$this->groups[$ass->group_id]->label.'</span></br>';
                        }
                        unset($uList);
                    }
                } elseif (!empty($ass->user_id) && isset($ass->user_id)) { // if evaluator
                    if (!isset($eval[$user['user_id']][$user['campaign_id']][$ass->user_id])) {
                        $eval[$user['user_id']][$user['campaign_id']][$ass->user_id] = true;
                        $usr = JUser::getInstance($ass->user_id);
                        if (in_array('delete',$params)) {
                            $img = '<span class="editlinktip hasTip" title="'.JText::_('COM_EMUNDUS_GROUPS_DELETE_ASSESSOR').' : '.$this->evaluators[$ass->user_id]->name.'::'.JText::_('COM_EMUNDUS_GROUPS_DELETE_ASSESSOR_TXT').'"><a href="index.php?option=com_emundus&controller=evaluation&task=delassessor&aid='.$user['user_id'].'&pid='.$ass->group_id.'&uid='.$ass->user_id.'&cid='.$ass->campaign_id.'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$itemid.'"><img src="'.JURI::base().'media/com_emundus/images/icones/clear_left_16x16.png" alt="'.JText::_('DEL_ASSESSOR').'" align="absbottom" /></a></span> ';
                            @$evaluator[$user['user_id']][$user['campaign_id']] .= $this->evaluators[$ass->user_id]->name.' '.$img.'<br />';
                        } else {
                            @$evaluator[$user['user_id']][$user['campaign_id']] .= $this->evaluators[$ass->user_id]->name.'</br>';
                        }
                    }
                }
            }
            if (count($assessors) == 0)
                @$evaluator[$user['user_id']][$user['campaign_id']] .= '<span class="hasTip" title="'.JText::_('COM_EMUNDUS_GROUPS_ASSESSOR_FILTER_ALERT').'"><font color="red">'.JText::_('COM_EMUNDUS_GROUPS_NO_ASSESSOR').'</font></span>';
        }
        return $evaluator;
    }

    /*
    ** @description Create the block to display files request
    */
    function createFilesRequestBlock($users) {
        //$itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);
        $actions = array();
        //$ids = array();
        foreach ($users as $user) {
            $uploads = (!empty($user['user']) && !empty($user['campaign_id']))?EmundusHelperList::getFilesRequest($user['user_id'], $user['campaign_id']):'';

            if (!empty($uploads)) {

                @$actions[$user['user_id']][$user['user']][@$user['campaign_id']] .= "<div class='em_filesrequest'>".$user->value." : ".$user->email." ".$user->time_date."</div>";
                @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '<div class="em_attachments" id="em_attachments_'.$user['user_id'].'"><div id="container" class="emundusraw">';
                @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '<ul id="emundus_nav"><li><a href="#"><img src="'.$this->baseurl.'/media/com_emundus/images/icones/pdf.png" alt="'.JText::_('COM_EMUNDUS_EXPERTS_REQUEST').'" title="'.JText::_('COM_EMUNDUS_ATTACHMENTS_ATTACHMENTS').'" width="22" height="22" align="absbottom" /></a>';
                @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '<ul>';

                foreach ( $uploads as $row ) {
                    @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '<li>';
                    /*if ($row->description != '') $link = $row->value.' (<em>'.$row->description.'</em>)';
                    else $link = $row->value;*/
                    @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= $row->value." : ".$row->email." ".$row->time_date;
                    @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '</li>';
                }
                @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '</ul></li>';
                @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '</ul>';
                @$actions[$user['user_id']][$user['user']][$user['campaign_id']] .= '</div></div>';


            } else
                @$actions[$user['user_id']][$user['user']][@$user['campaign_id']] .= '';

        }
        return $actions;
    }

    /**/
    function createCommentBlock($users) {
        $itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);
        $comment = array();
        $ids = array();
        //$val = $user['user_id'].",".@$user['user'].",".@$user['campaign_id'];
        foreach($users as $user) {
            //if( !in_array($val, $ids) ){
            //$ids[] = $val;

            $com = (!empty($user['user']) && !empty($user['campaign_id']))?EmundusHelperList::getComment($user['user_id'], $user['user'], $user['campaign_id']):'';

            if(!empty($com))
                @$comment[$user['user_id']][$user['user']][@$user['campaign_id']] .= '<span class="editlinktip hasTip" title="'.JText::_(' '.$com.' ').'"><a class="modal" rel="{handler:\'iframe\',size:{x:window.getWidth()*0.6,y:window.getHeight()*0.6}}" href="index.php?option=com_emundus&view=evaluation&layout=detail&tmpl=component&iframe=1&sid='.$user['user_id'].'&uid='.$user["user"].'&cid='.$user["campaign_id"].'&Itemid='.$itemid.'&iframe=1"><img height="25" width="25" align="bottom" src="'.$this->baseurl.'/media/com_emundus/images/icones/comments.png"/></a></span>';
            else
                @$comment[$user['user_id']][$user['user']][@$user['campaign_id']] .= '';

            //}
        }
        return $comment;
    }

    //Fn for array_unique column-wise for multi-dimensioanl array without losing keys | Start
    function array_uniquecolumn($arr, $key)
    {
        $rows   = sizeof($arr);
        $columns = sizeof($arr[0]);
        $columnkeys = array_keys($arr[0]);
        for($i=0; $i<$columns; $i++){
            if ($columnkeys[$i] == $key) {
                for($j=0;$j<$rows;$j++){
                    for($k = $j+1; $k<$rows; $k++){
                        if($arr[$j][$columnkeys[$i]] == $arr[$k][$columnkeys[$i]])
                            $arr[$k][$columnkeys[$i]] = "";
                    }
                }
            }
        }
        return ($arr);
    }

    //get the Confirm by applicant column
    function getEngaged($users){
        $itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);
        $view = JRequest::getVar('view', null, 'GET', 'none',0);
        $engaged = array();
        foreach($users as $user){
            @$engaged[$user['user_id']][$user['campaign_id']] .= '';
            if ($user['final_grade'] == 4) {
                if ($user['engaged']==1){
                    $img = 'tick.png';
                    $btn = 'set_engaged|'.$user['user_id'];
                    $alt = JText::_('COM_EMUNDUS_FORM_ENGAGED').'::'.JText::_('COM_EMUNDUS_FORM_ENGAGED_NOTE');
                    $label = JText::_('JYES');
                } else {
                    $img = 'publish_x.png';
                    $btn = 'set_engaged|'.$user['user_id'];
                    $alt = JText::_('COM_EMUNDUS_FORM_UNENGAGED').'::'.JText::_('COM_EMUNDUS_FORM_UNENGAGED_NOTE');
                    $label = JText::_('JNO');
                }
                $id =  "jos_emundus_final_grade.engaged.".$user['user_id'].".".$user['campaign_id'];
                $engaged[$user['user_id']][$user['campaign_id']] = '<div class="em_validation" id="'.$id.'"><span class="hasTip" title="'.$alt.'"><input type="image" src="'.JURI::base().'media/com_emundus/images/icones/'.$img.'" onclick="validation('.$user['user_id'].',\''.$user['engaged'].'\', \''.$id.'\');" > '.$label.'</span></div> ';
            } else {
                @$engaged[$user['user_id']][$user['campaign_id']] .= '';
            }
        }
        return $engaged;
    }

    //get profiles
    function getProfiles(){
        $db = JFactory::getDBO();
        $query = 'SELECT esp.id, esp.label, esp.acl_aro_groups, caag.lft
		FROM #__emundus_setup_profiles esp
		INNER JOIN #__usergroups caag on esp.acl_aro_groups=caag.id
		ORDER BY caag.lft, esp.label';
        $db->setQuery( $query );
        return $db->loadObjectList('id');
    }

    function createProfileBlock($users, $key){
        $profile = array();
        $ids = array();
        $profiles_label = EmundusHelperList::getProfiles();
        foreach($users as $user){
            $str = $user['user_id'].','.$user['campaign_id'];
            if(!in_array($str, $ids)){
                $ids[] = $str;
                @$profile[$user['user_id']][$user['campaign_id']] .= '<div class="emundusprofile'.$user[$key].'">'.$profiles_label[$user[$key]]->label.'</div>';
            }
        }
        return $profile;
    }

    function getApplicationComments($user_id){
        $db = JFactory::getDBO();
        $query = 'SELECT ec.applicant_id, ec.reason, ec.date as com_date, ec.comment_body as comment, u.name as evaluator_name
				FROM #__emundus_comments ec
				LEFT JOIN #__users u ON ec.user_id = u.id
				WHERE ec.applicant_id = '.$user_id;
        $db->setQuery( $query );
        return $db->loadObjectList();
    }

    function createApplicationCommentBlock($users,$params){
        $comments = array();
        foreach($users as $user){
            $com = EmundusHelperList::getApplicationComments($user['user_id']);
            @$comments[$user['user_id']] .= '<div id="comments_'.$user['user_id'].'">';
            if(!empty($com)){
                $i = 1;
                foreach($com as $c){
                    $comments[$user['user_id']] .= '<div id="comment'.$i.'_'.$user['user_id'].'">';
                    if(in_array('evaluator',$params)) $comments[$user['user_id']] .= '<span><b>'.JText::_('COM_EMUNDUS_EVALUATION_EVALUATOR').':</b> '.$c->evaluator_name.' </span>';
                    if(in_array('date',$params)) $comments[$user['user_id']] .= '<span><b>'.JText::_('COM_EMUNDUS_DATE').':</b> '.$c->com_date.' </span>';
                    if(in_array('reason',$params)) $comments[$user['user_id']] .= '<span><b>'.JText::_('COM_EMUNDUS_COMMENTS_REASON').':</b> '.$c->reason.' </span>';
                    if(in_array('comment',$params)) $comments[$user['user_id']] .= '<span><b>'.JText::_('COM_EMUNDUS_COMMENT').':</b> '.$c->comment.' </span>';
                    $comments[$user['user_id']] .= '</div>';
                    $i++;
                }
            }
            $comments[$user['user_id']] .= '</div>';
        }
        return $comments;
    }

    function createShowCommentBlock(){
        $filter_comment = JRequest::getVar('comments', null, 'POST', 'none', 0);
        // Starting a session.
        $session = JFactory::getSession();
        if(empty($filter_comment) && $session->has( 'comments' )) $filter_comment = $session->get( 'comments' );
        $comments = '<div id="show_comment"><label>'.JText::_('COM_EMUNDUS_COMMENTS_SHOW_COMMENT').'</label>';
        $comments .= '<input name="comments" type="checkbox" onClick="javascript:submit()" value="1" ';
        if($filter_comment==1) $comments .= 'checked=checked';
        $comments .= '/></div>';
        return $comments;
    }

    function createBatchBlock(){
        $batch = '<select id="validation_list" name="validation_list">';
        $batch .= '<option value="1">'.JText::_('COM_EMUNDUS_FORMS_VALIDATE').'</option>';
        $batch .= '<option value="0">'.JText::_('COM_EMUNDUS_FORMS_UNVALIDATE').'</option>';
        $batch .= '</select>';
        $batch .= ' <input type="submit" class="blue" name="set_status" value="'.JText::_('COM_EMUNDUS_FORMS_SUBMIT_FOR_SELECTED').'" onclick="document.pressed=this.name" />';
        return $batch;
    }

    function createApplicationStatutblock($params){
        $statut = '<div id="em_comments"><img class="selectallarrow" width="38" height="22" alt="'.JText::_('FOR_SELECTION').'" src="'.JURI::base().'media/com_emundus/images/icones/arrow_ltr.png"><textarea name="comments" id="comments" rows="1" cols="50%" onFocus="if(this.value == this.defaultValue) this.value=\'\'">'.JText::_('COM_EMUNDUS_COMMENTS').'</textarea>';
        $statut .= '<div id="em_comments_action">';
        if(in_array('complete',$params)) $statut .= '<input type="submit" class="green" name="push_true" value="'.JText::_('COM_EMUNDUS_APPLICATION_PUSH_TRUE').'" onclick="document.pressed=this.name" />';
        if(in_array('incomplete',$params)) $statut .= '<input type="submit" class="red" name="push_false" value="'.JText::_('COM_EMUNDUS_APPLICATION_PUSH_FALSE').'" onclick="document.pressed=this.name" />';
        $statut .= '</div></div>';
        return $statut;
    }

    /*
    ** @description	Get Fabrik elements detail from List Fabrik name
    ** @param	string	$elements	list of Fabrik element comma separated.
    ** @return	array	Array of Fabrik element params.
    */
    function getElementsDetailsByID($elements) {
        $db = JFactory::getDBO();
        $query = 'SELECT concat_ws("___",tab.db_table_name,element.name) as fabrik_element, element.id, element.name AS element_name, element.label AS element_label, element.plugin AS element_plugin, element.ordering, element.hidden, element.published, element.id AS element_id, tab.db_table_name AS tab_name, element.plugin AS element_plugin,
				groupe.id AS group_id, groupe.label AS group_label, element.params AS params, element.params, tab.id AS table_id, tab.db_table_name AS table_name, tab.label AS table_label, tab.created_by_alias, tab.group_by AS tab_group_by
				FROM #__fabrik_elements element
				INNER JOIN #__fabrik_groups AS groupe ON element.group_id = groupe.id
				INNER JOIN #__fabrik_formgroup AS formgroup ON groupe.id = formgroup.group_id
				INNER JOIN #__fabrik_lists AS tab ON tab.form_id = formgroup.form_id
				WHERE element.id IN ('.$elements.')';
//echo str_replace("#_", "jos", $query);
        $db->setQuery($query);

        return @EmundusHelperFilters::insertValuesInQueryResult($db->loadObjectList(), array("sub_values", "sub_labels", "element_value"));
    }

    /*
    ** @description	Get Fabrik elements detail from List Fabrik name
    ** @param	string	$elements	list of Fabrik element comma separated.
    ** @return	array	Array of Fabrik element params.
    */
    function getElementsDetails($elements) {
        $db = JFactory::getDBO();
        $query = 'SELECT element.name AS element_name, element.label AS element_label, element.id AS element_id, tab.db_table_name AS tab_name, element.plugin AS element_plugin, element.ordering, element.hidden, element.published,
				element.params AS params, element.params, tab.group_by AS tab_group_by
				FROM #__fabrik_elements element
				INNER JOIN #__fabrik_groups AS groupe ON element.group_id = groupe.id
				INNER JOIN #__fabrik_formgroup AS formgroup ON groupe.id = formgroup.group_id
				INNER JOIN #__fabrik_lists AS tab ON tab.form_id = formgroup.form_id';
        $query .= ' WHERE concat_ws(".", tab.db_table_name, element.name) IN ('.$elements.')';
        $db->setQuery($query);

        return @EmundusHelperFilters::insertValuesInQueryResult($db->loadObjectList(), array("sub_values", "sub_labels", "element_value"));
    }

    /*
    ** @description	Get Fabrik elements detail from elements Fabrik name
    ** @param	string	$elements	list of Fabrik element comma separated.
    ** @return	array	Array of Fabrik element params.
    */
    function getElementsDetailsByName($elements) {
        $db = JFactory::getDBO();
        $query = 'SELECT element.name AS element_name, element.label AS element_label, element.id AS element_id, tab.db_table_name AS tab_name, element.plugin AS element_plugin, element.ordering, element.hidden, element.published,
				element.params AS params, element.params, tab.group_by AS tab_group_by
				FROM #__fabrik_elements element
				INNER JOIN #__fabrik_groups AS groupe ON element.group_id = groupe.id
				INNER JOIN #__fabrik_formgroup AS formgroup ON groupe.id = formgroup.group_id
				INNER JOIN #__fabrik_lists AS tab ON tab.form_id = formgroup.form_id
				WHERE element.name IN ('.$elements.') ORDER BY element.ordering';
        $db->setQuery($query);
//echo str_replace("#_", "jos", $query);
        return @EmundusHelperFilters::insertValuesInQueryResult($db->loadObjectList(), array("sub_values", "sub_labels"));
    }

    /*
    ** @description	Get Fabrik elements detail from elements Fabrik name
    ** @param	string	$elements	list of Fabrik element comma separated.
    ** @return	array	Array of Fabrik element params.
    */
    function getElementsDetailsByFullName($fullname) {
        $tab = explode("__", $fullname);
        $db = JFactory::getDBO();
        $query = 'SELECT element.name AS element_name, element.label AS element_label, element.id AS element_id, tab.db_table_name AS tab_name, element.plugin AS element_plugin, element.ordering, element.hidden, element.published,
				element.params AS params, element.params, tab.group_by AS tab_group_by
				FROM #__fabrik_elements element
				INNER JOIN #__fabrik_groups AS groupe ON element.group_id = groupe.id
				INNER JOIN #__fabrik_formgroup AS formgroup ON groupe.id = formgroup.group_id
				INNER JOIN #__fabrik_lists AS tab ON tab.form_id = formgroup.form_id
				WHERE tab.db_table_name like "'.$tab[0].'" AND element.name like "'.$tab[1].'"';
        $db->setQuery($query);
//echo str_replace("#_", "jos", $query);
        return @EmundusHelperFilters::insertValuesInQueryResult($db->loadObjectList(), array("sub_values", "sub_labels"));
    }

    /*
    ** @description	Get the value of the search box
    ** @param	array	$details	tableau des détails d'un élément.
    ** @param	string	$default	valeur par défaut de la case.
    ** @param	string	$name	nom de l'élément.
    */
    function getBoxValue($details, $default, $name) {

        if ($details['plugin'] == "field" || $details['plugin'] == "textarea" || $details['plugin'] == "calc") return $default;
        /*elseif ($details['plugin'] == "fabrikuser")*/
        elseif ($details['plugin'] == "databasejoin") {
            if (!empty($details['option_list']))
                foreach($details['option_list'] as $value){
                    if ($value->elt_key == $default) return $value->elt_val;
                }
        }
        else {
            $sub_values = explode('|', $details['sub_values']);
            $sub_labels = explode('|', $details['sub_labels']);
            $j = 0;
            foreach($sub_values as $value){
                if($value == $default) return $sub_labels[$j];
                $j++;
            }
        }
    }

    /*
    ** @description		génère une liste html à partir d'un tableau de données
    ** @param array		tableau à une dimension
    */
    function createHtmlList($tab) {
        $str = '<ul class="em_list">';
        foreach ($tab as $t) {
            $str .= '<li id="em_list_elements">'.$t.'</li>';
        }
        $str .= '</ul>';
        return $str;
    }
}
?>
