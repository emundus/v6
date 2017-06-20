<?php
/**
 * Application Model for eMundus Component
 *
 * @package    Joomla
 * @subpackage eMundus
 *             components/com_emundus/emundus.php
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 * @author     Benjamin Rivalland
 */

// No direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.model' );

class EmundusModelApplication extends JModelList
{
    var $_user = null;
    var $_db = null;

    /**
     * Constructor
     *
     * @since 1.5
     */
    public function __construct()
    {
        parent::__construct();
        global $option;

        $this->_mainframe = JFactory::getApplication();

        $this->_db = JFactory::getDBO();
        $this->_user = JFactory::getUser();
    }

    public function getApplicantInfos($aid, $param)
    {
        $query = 'SELECT '.implode(",", $param).'
                FROM #__users
                LEFT JOIN #__emundus_users ON #__emundus_users.user_id=#__users.id
                LEFT JOIN #__emundus_personal_detail ON #__emundus_personal_detail.user=#__users.id
                LEFT JOIN #__emundus_setup_profiles ON #__emundus_setup_profiles.id=#__emundus_users.profile
                LEFT JOIN #__emundus_uploads ON (#__emundus_uploads.user_id=#__users.id AND #__emundus_uploads.attachment_id=10)
                WHERE #__users.id='.$aid;
        $this->_db->setQuery( $query );
        $infos =  $this->_db->loadAssoc();

        return $infos;
    }

    public function getApplicantDetails($aid, $ids){
        $details = @EmundusHelperList::getElementsDetailsByID($ids);
        $select=array();
        foreach ($details as $detail) {
            $select[] = $detail->tab_name.'.'.$detail->element_name.' AS "'.$detail->element_id.'"';
        }

        $query = 'SELECT '.implode(",", $select).'
                FROM #__users as u
                LEFT JOIN #__emundus_users ON #__emundus_users.user_id=u.id
                LEFT JOIN #__emundus_personal_detail ON #__emundus_personal_detail.user=u.id
                LEFT JOIN #__emundus_setup_profiles ON #__emundus_setup_profiles.id=#__emundus_users.profile
                LEFT JOIN #__emundus_uploads ON (#__emundus_uploads.user_id=u.id AND #__emundus_uploads.attachment_id=10)
                WHERE u.id='.$aid;
        $this->_db->setQuery( $query );
        $values =  $this->_db->loadAssoc();

        foreach ($details as $detail) {
            $detail->element_value = $values[$detail->element_id];
        }
//var_dump($details);
        return $details;
    }

    public function getUserCampaigns($id, $cid = null)
    {
        if($cid === null)
        {
            $query = 'SELECT esc.*, ecc.date_submitted, ecc.submitted, ecc.id as campaign_candidature_id, efg.result_sent, efg.date_result_sent, efg.final_grade, ecc.fnum, ess.class, ess.step, ess.value as step_value
            FROM #__emundus_users eu
            LEFT JOIN #__emundus_campaign_candidature ecc ON ecc.applicant_id=eu.user_id
            LEFT JOIN #__emundus_setup_campaigns esc ON ecc.campaign_id=esc.id
            LEFT JOIN #__emundus_final_grade efg ON efg.campaign_id=esc.id AND efg.student_id=eu.user_id
            LEFT JOIN #__emundus_setup_status as ess ON ess.step = ecc.status
            WHERE eu.user_id="'.$id.'" and ecc.published = 1';
            $this->_db->setQuery( $query );

            return $this->_db->loadObjectList();
        }
        else
        {
            $query = 'SELECT esc.*, ecc.date_submitted, ecc.submitted, ecc.id as campaign_candidature_id, efg.result_sent, efg.date_result_sent, efg.final_grade, ecc.fnum, ess.class, ess.step, ess.value as step_value
            FROM #__emundus_users eu
            LEFT JOIN #__emundus_campaign_candidature ecc ON ecc.applicant_id=eu.user_id
            LEFT JOIN #__emundus_setup_campaigns esc ON ecc.campaign_id=esc.id
            LEFT JOIN #__emundus_final_grade efg ON efg.campaign_id=esc.id AND efg.student_id=eu.user_id
            LEFT JOIN #__emundus_setup_status as ess ON ess.step = ecc.status
            WHERE eu.user_id="'.$id.'" and ecc.published = 1 and esc.id = '.$cid;
            $this->_db->setQuery( $query );
            return $this->_db->loadObject();
        }
    }

    public function getCampaignByFnum($fnum)
    {
        $query = 'SELECT esc.*, ecc.date_submitted, ecc.submitted, ecc.id as campaign_candidature_id, efg.result_sent, efg.date_result_sent, efg.final_grade, ecc.fnum, ess.class, ess.step, ess.value as step_value
            FROM #__emundus_users eu
            LEFT JOIN #__emundus_campaign_candidature ecc ON ecc.applicant_id=eu.user_id
            LEFT JOIN #__emundus_setup_campaigns esc ON ecc.campaign_id=esc.id
            LEFT JOIN #__emundus_final_grade efg ON efg.campaign_id=esc.id AND efg.student_id=eu.user_id
            LEFT JOIN #__emundus_setup_status as ess ON ess.step = ecc.status
            WHERE ecc.fnum like '.$fnum;

        $this->_db->setQuery( $query );

        return $this->_db->loadObjectList();
    }

    public function getUserAttachments($id){

        $query = 'SELECT eu.id AS aid, esa.*, eu.filename, eu.description, eu.timedate, esc.label as campaign_label, esc.year, esc.training
            FROM #__emundus_uploads AS eu
            LEFT JOIN #__emundus_setup_attachments AS esa ON  eu.attachment_id=esa.id
            LEFT JOIN #__emundus_setup_campaigns AS esc ON esc.id=eu.campaign_id
            WHERE eu.user_id = '.$id;'
            ORDER BY esa.ordering';
        $this->_db->setQuery( $query );
        return $this->_db->loadObjectList();
    }

    function getUserAttachmentsByFnum($fnum){

        if (EmundusHelperAccess::isExpert($this->_user->id)) {
            $eMConfig = JComponentHelper::getParams('com_emundus');
            $expert_document_id = $eMConfig->get('expert_document_id', '36');

            $query = 'SELECT eu.id AS aid, esa.*, eu.attachment_id, eu.filename, eu.description, eu.timedate, eu.can_be_deleted, eu.can_be_viewed, esc.label as campaign_label, esc.year, esc.training
            FROM #__emundus_uploads AS eu
            LEFT JOIN #__emundus_setup_attachments AS esa ON  eu.attachment_id=esa.id
            LEFT JOIN #__emundus_setup_campaigns AS esc ON esc.id=eu.campaign_id
            WHERE eu.fnum like '.$this->_db->Quote($fnum).' AND (eu.attachment_id != '.$expert_document_id.')
            ORDER BY esa.ordering, eu.timedate ASC';
        } else {
            $query = 'SELECT eu.id AS aid, esa.*, eu.attachment_id, eu.filename, eu.description, eu.timedate, eu.can_be_deleted, eu.can_be_viewed, esc.label as campaign_label, esc.year, esc.training
            FROM #__emundus_uploads AS eu
            LEFT JOIN #__emundus_setup_attachments AS esa ON  eu.attachment_id=esa.id
            LEFT JOIN #__emundus_setup_campaigns AS esc ON esc.id=eu.campaign_id
            WHERE eu.fnum like '.$this->_db->Quote($fnum).' 
            ORDER BY esa.ordering, eu.timedate ASC';
        }
        $this->_db->setQuery( $query );
        //die( str_replace ('#_', 'jos', $query));
        return $this->_db->loadObjectList();
    }

    public function getUsersComments($id){

        $query = 'SELECT ec.id, ec.comment_body as comment, ec.reason, ec.date, u.name
                FROM #__emundus_comments ec 
                LEFT JOIN #__users u ON u.id = ec.user_id 
                WHERE ec.applicant_id ="'.$id.'" 
                ORDER BY ec.date DESC ';
        $this->_db->setQuery( $query );
        // echo str_replace ('#_', 'jos', $query);
        return $this->_db->loadObjectList();
    }

    public function getComment($id){

        $query = 'SELECT * FROM #__emundus_comments ec WHERE ec.id ='.$id;
        $this->_db->setQuery( $query );
        return $this->_db->loadAssoc();
    }

    public function getTag($id){

        $query = 'SELECT * FROM #__emundus_tag_assoc WHERE id ='.$id;
        $this->_db->setQuery( $query );
        return $this->_db->loadAssoc();
    }

    public function getFileComments($fnum){

        $query = 'SELECT ec.id, ec.comment_body as comment, ec.reason, ec.fnum, ec.user_id, ec.date, u.name
                FROM #__emundus_comments ec 
                LEFT JOIN #__users u ON u.id = ec.user_id 
                WHERE ec.fnum like '.$this->_db->Quote($fnum).' 
                ORDER BY ec.date ASC ';
        $this->_db->setQuery( $query );
        // echo str_replace ('#_', 'jos', $query);
        return $this->_db->loadObjectList();
    }

    public function deleteComment($id){
        $query = 'SELECT user_id FROM #__emundus_comments WHERE id="'.$id.'"';
        $this->_db->setQuery( $query );
        $result=$this->_db->loadResult();
        if($result==$this->_user->id){
            $query = 'DELETE FROM #__emundus_comments WHERE id = '.$id;
            $this->_db->setQuery($query);
//die(str_replace ('#_', 'jos', $query));
            return $this->_db->Query();
        }else{
            return -1;
        }
    }

    public function deleteTag($id_tag, $fnum){
        $query = 'DELETE FROM #__emundus_tag_assoc WHERE id_tag = '.$id_tag.' AND fnum like '.$this->_db->Quote($fnum);
        $this->_db->setQuery($query);

        return $this->_db->Query();
    }

    public function addComment($row)
    {
        $query = 'INSERT INTO `#__emundus_comments` (applicant_id, user_id, reason, date, comment_body, fnum)
                VALUES('.$row['applicant_id'].','.$row['user_id'].','.$this->_db->Quote($row['reason']).',"'.date("Y.m.d H:i:s").'",'.$this->_db->Quote($row['comment_body']).','.$this->_db->Quote(@$row['fnum']).')';
        $this->_db->setQuery( $query );
        try
        {
            $this->_db->query();
            return $this->_db->insertid();
        }
        catch(Exception $e)
        {
            throw $e;
        }
    }

    public function deleteData($id, $table){
        $query = 'DELETE FROM `'.$table.'` WHERE id='.$id;
        $this->_db->setQuery($query);

        return $this->_db->Query();
    }

    public function deleteAttachment($id){
        $query = 'SELECT * FROM #__emundus_uploads WHERE id='.$id;
        $this->_db->setQuery( $query );
        $file = $this->_db->loadAssoc();

        $f = EMUNDUS_PATH_ABS.$file['user_id'].DS.$file['filename'];
        @unlink($f);
        /*if(!@unlink($f) && file_exists($f)) {
            // JError::raiseError(500, JText::_('FILE_NOT_FOUND').$file);
            //$this->setRedirect($url, JText::_('FILE_NOT_FOUND'), 'error');
            return -1;
        }*/

        $query = 'DELETE FROM #__emundus_uploads WHERE id='.$id;
        $this->_db->setQuery( $query );

        return $this->_db->Query();
    }

    public function uploadAttachment($data) {
        try
        {
           /* $i = 0;
            foreach ($data['value'] as $key=>$value) {
                $data['value'][$i] =  str_replace('"','', $value);
                $i++;
            }*/
            $query = "INSERT INTO #__emundus_uploads (".implode(',', $data["key"]).") VALUES ('".implode("','", $data["value"])."')";
            $this->_db->setQuery( $query );
            $this->_db->execute();
            return $this->_db->insertid();
        }
        catch (RuntimeException $e)
        {
            JFactory::getApplication()->enqueueMessage($e->getMessage());

            return false;
        }
    }

    public function getAttachmentByID($id) {
        $query = "SELECT * FROM #__emundus_setup_attachments WHERE id=".$id;
        $this->_db->setQuery($query);

        return $this->_db->loadAssoc();
    }

    public function getAttachmentByLbl($label) {
        $query = "SELECT * FROM #__emundus_setup_attachments WHERE lbl LIKE".$this->_db->Quote($label);
        $this->_db->setQuery($query);

        return $this->_db->loadAssoc();
    }

    public function getUploadByID($id) {
        $query = "SELECT * FROM #__emundus_uploads WHERE id=".$id;
        $this->_db->setQuery($query);

        return $this->_db->loadAssoc();
    }

    public function getFormsProgress($aid, $pid = 9, $fnum = "0")
    {
        if(!is_array($fnum))
        {
            //$user = JFactory::getUser($aid);
            $forms = @EmundusHelperMenu::buildMenuQuery($pid);
            $nb = 0;
            $formLst = array();
            foreach ($forms as $form) {
                $query = 'SELECT count(*) FROM '.$form->db_table_name.' WHERE user = '.$aid.' AND fnum like '.$this->_db->Quote($fnum);
                $this->_db->setQuery( $query );
                $cpt = $this->_db->loadResult();
                if ($cpt==1)
                {
                    $nb++;
                } else {
                    $formLst[] = $form->label;
                }
            }
            return  @floor(100*$nb/count($forms));
        }
        else
        {
            $result = array();
            foreach($fnum as $f)
            {
                $query = 'SELECT esc.*
                    FROM #__emundus_campaign_candidature AS esc
                    WHERE esc.fnum like '.$this->_db->Quote($f);
                $this->_db->setQuery( $query );
                $fInfo = $this->_db->loadAssoc();
                $query = 'SELECT esp.*, esc.*
                    FROM  #__emundus_setup_profiles AS esp
                    LEFT JOIN #__emundus_setup_campaigns AS esc ON esc.profile_id = esp.id
                    WHERE esc.id='.$fInfo['campaign_id'];
                $this->_db->setQuery( $query );
                $pid = $this->_db->loadAssoc();
                $forms = @EmundusHelperMenu::buildMenuQuery($pid['profile_id']);
                $nb = 0;
                $formLst = array();
                foreach ($forms as $form)
                {
                    $query = 'SELECT count(*) FROM '.$form->db_table_name.' WHERE fnum like '.$this->_db->Quote($f);
                    $this->_db->setQuery( $query );
                    $cpt = $this->_db->loadResult();
                    if ($cpt==1)
                    {
                        $nb++;
                    }
                    else
                    {
                        $formLst[] = $form->label;
                    }
                }
                $result[$f] = @floor(100*$nb/count($forms));
            }
            return $result;
        }
    }

    public function getAttachmentsProgress($aid, $pid=9, $fnum = "0")
    {
        if(!is_array($fnum))
        {
            $query = 'SELECT IF(COUNT(profiles.attachment_id)=0, 100, 100*COUNT(uploads.attachment_id>0)/COUNT(profiles.attachment_id))
                FROM #__emundus_setup_attachment_profiles AS profiles
                LEFT JOIN #__emundus_uploads AS uploads ON uploads.attachment_id = profiles.attachment_id AND uploads.user_id = '.$aid.' AND uploads.fnum like '.$this->_db->Quote($fnum).'
                WHERE profiles.profile_id = '.$pid.' AND profiles.displayed = 1 AND profiles.mandatory = 1' ;
            $this->_db->setQuery($query);
            return floor($this->_db->loadResult());
        }
        else
        {
            $result = array();
            foreach($fnum as $f)
            {
                $query = 'SELECT esc.*
                    FROM #__emundus_campaign_candidature AS esc
                    WHERE esc.fnum like '.$this->_db->Quote($f);
                $this->_db->setQuery( $query );
                $fInfo = $this->_db->loadAssoc();
                $query = 'SELECT esp.*, esc.*
                    FROM  #__emundus_setup_profiles AS esp
                    LEFT JOIN #__emundus_setup_campaigns AS esc ON esc.profile_id = esp.id
                    WHERE esc.id='.$fInfo['campaign_id'];
                $this->_db->setQuery( $query );
                $pid = $this->_db->loadAssoc();

                $query = 'SELECT IF(COUNT(profiles.attachment_id)=0, 100, 100*COUNT(uploads.attachment_id>0)/COUNT(profiles.attachment_id))
                FROM #__emundus_setup_attachment_profiles AS profiles
                LEFT JOIN #__emundus_uploads AS uploads ON uploads.attachment_id = profiles.attachment_id AND uploads.fnum like '.$this->_db->Quote($f).'
                WHERE profiles.profile_id = '.$pid['profile_id'].' AND profiles.displayed = 1 AND profiles.mandatory = 1' ;
                $this->_db->setQuery($query);
                $result[$f] = floor($this->_db->loadResult());
            }
            return $result;
        }
    }

    public function getLogged ($aid) {
        $user = JFactory::getUser();
        $query = 'SELECT s.time, s.client_id, u.id, u.name, u.username
                    FROM #__session AS s
                    LEFT JOIN #__users AS u on s.userid = u.id 
                    WHERE u.id = "'.$aid.'"';
        $this->_db->setQuery($query);
        $results = $this->_db->loadObjectList();

        // Check for database errors
        if ($error = $this->_db->getErrorMsg()) {
            JError::raiseError(500, $error);
            return false;
        };

        foreach($results as $k => $result)
        {
            $results[$k]->logoutLink = '';

            if($user->authorise('core.manage', 'com_users'))
            {
                $results[$k]->editLink = JRoute::_('index.php?option=com_emundus&view=users&edit=1&rowid='.$result->id.'&tmpl=component');
                $results[$k]->logoutLink = JRoute::_('index.php?option=com_login&task=logout&uid='.$result->id .'&'. JSession::getFormToken() .'=1');
            }
            $results[$k]->name = $results[$k]->username;
        }

        return $results;
    }

    // Get form to display in application page layout view
    public function getForms($aid, $fnum = 0, $pid = 9) {
        //$tableuser = @EmundusHelperList::getFormsList($aid, $fnum);
        $tableuser = @EmundusHelperMenu::buildMenuQuery($pid);

        $forms = '';

        try {
        if(isset($tableuser)) {
            foreach($tableuser as $key => $itemt) {
                $forms .= '<br><hr><h3>';
                $title = explode('-', $itemt->label);
                $forms .= $title[1];

                if (EmundusHelperAccess::asAccessAction(1, 'u', $this->_user->id, $fnum) &&
                    $itemt->db_table_name != "#__emundus_training")
                {
                    $query = 'SELECT count(id) FROM `'.$itemt->db_table_name.'` WHERE user='.$aid.' AND fnum like '.$this->_db->Quote($fnum);
                    $this->_db->setQuery( $query );
                    $cpt = $this->_db->loadResult();

                    if($cpt>0)
                        $forms .= ' <button type="button" id="'.$itemt->form_id.'" class="btn btn btn-info btn-sm em-actions-form" url="index.php?option=com_fabrik&view=form&formid='.$itemt->form_id.'&usekey=fnum&rowid='.$fnum.'&tmpl=component" alt="'.JText::_('EDIT').'"><span class="glyphicon glyphicon-edit"></span><i> '.JText::_('EDIT').'</i></button>';
                    else
                        $forms .= ' <button type="button" id="'.$itemt->form_id.'" class="btn btn-default btn-sm em-actions-form" url="index.php?option=com_fabrik&view=form&formid='.$itemt->form_id.'&'.$itemt->db_table_name.'___fnum='.$fnum.'&'.$itemt->db_table_name.'___user_raw='.$aid.'&'.$itemt->db_table_name.'___user='.$aid.'&sid='.$aid.'&tmpl=component" alt="'.JText::_('EDIT').'"><span class="glyphicon glyphicon-edit"></span><i> '.JText::_('ADD').'</i></button>';


                }

                $forms .= '</h3>';
                // liste des groupes pour le formulaire d'une table
                $query = 'SELECT ff.id, ff.group_id, fg.id, fg.label, INSTR(fg.params,"\"repeat_group_button\":\"1\"") as repeated, INSTR(fg.params,"\"repeat_group_button\":1") as repeated_1
                            FROM #__fabrik_formgroup ff, #__fabrik_groups fg
                            WHERE ff.group_id = fg.id AND
                                  ff.form_id = "'.$itemt->form_id.'" 
                            ORDER BY ff.ordering';
                $this->_db->setQuery( $query );
                $groupes = $this->_db->loadObjectList();

                /*-- Liste des groupes -- */
                foreach($groupes as $keyg => $itemg) {
                    // liste des items par groupe
                    $query = 'SELECT fe.id, fe.name, fe.label, fe.plugin, fe.params
                                FROM #__fabrik_elements fe
                                WHERE fe.published=1 AND 
                                      fe.hidden=0 AND 
                                      fe.group_id = "'.$itemg->group_id.'" 
                                ORDER BY fe.ordering';

                    $this->_db->setQuery( $query );
                    $elements = $this->_db->loadObjectList();
                    if(count($elements)>0) {
                        $forms .= '<fieldset><legend class="legend">';
                        $forms .= $itemg->label;
                        $forms .= '</legend>';
/*
                        if ($itemg->repeated == 0 && $itemg->repeated_1 == 0) {
                            foreach($elements as &$iteme) {                    
                                try { 
                                    $query = 'SELECT `id`, `'.$iteme->name .'` FROM `'.$itemt->db_table_name.'` WHERE user='.$aid.' AND fnum like '.$this->_db->Quote($fnum);
                                    $this->_db->setQuery( $query );
                                    $res = $this->_db->loadRow();
                                    
                                    $iteme->content = @$res[1];
                                    $iteme->content_id = @$res[0];

                                    if ($iteme->plugin == 'databasejoin') {
                                        $params = json_decode($iteme->params);
                                        if($params->database_join_display_type == 'checkbox'){
                                            $query = 'SELECT `id`, GROUP_CONCAT('.$iteme->name.', ", ") as '.$iteme->name .'
                                                        FROM `'.$itemt->db_table_name.'_repeat_'.$iteme->name.'` 
                                                        WHERE parent_id='.$iteme->content_id.' GROUP BY parent_id';
                                            try {
                                                $this->_db->setQuery( $query );
                                                $res = $this->_db->loadRow();
                                                $iteme->content = @$res[1];
                                                $iteme->content_id = @$res[0];
                                            } catch (Exception $e) {
                                                echo $e->getMessage();
                                            }
                                        } else{
                                            $select = !empty($params->join_val_column_concat)?"CONCAT(".$params->join_val_column_concat.")":$params->join_val_column;
                                            $from = $params->join_db_name;
                                            $where = $params->join_key_column.'='.$this->_db->Quote($iteme->content);
                                            $query = "SELECT id, ".$select." FROM ".$from." WHERE ".$where;
                                            $query = preg_replace('#{thistable}#', $from, $query);
                                            $query = preg_replace('#{my->id}#', $aid, $query);
                                            $this->_db->setQuery( $query );
                                            $res = $this->_db->loadRow();
                                            $iteme->content = @$res[1];
                                            $iteme->content_id = @$res[0];
                                        }
                                     } 
                                } catch (Exception $e) {
                                    echo $e->getMessage();
                                }
                            }
                        }

                        unset($iteme);
*/
                        if ($itemg->group_id == 14) {

                            foreach($elements as &$element) {
                                if(!empty($element->label) && $element->label!=' ') {
                                    if ($element->plugin=='date' && $element->content>0) {
                                        $date_params = json_decode($element->params);
                                        $elt = date($date_params->date_form_format, strtotime($element->content));
                                    }
                                    elseif ($element->plugin=='birthday' && $element->content>0) {
                                        $elt = JHtml::_('date', $element->content, JText::_('DATE_FORMAT_LC'));
                                    }
                                    elseif($element->plugin=='databasejoin') {
                                        $params = json_decode($element->params);
                                        $select = !empty($params->join_val_column_concat)?"CONCAT(".$params->join_val_column_concat.")":$params->join_val_column;
                                        $from = $params->join_db_name;
                                        $where = $params->join_key_column.'='.$this->_db->Quote($element->content);
                                        $query = "SELECT ".$select." FROM ".$from." WHERE ".$where;
                                        $query = preg_replace('#{thistable}#', $from, $query);
                                        $query = preg_replace('#{my->id}#', $aid, $query);
                                        $this->_db->setQuery( $query );
                                        $elt = $this->_db->loadResult();
                                    }
                                    elseif ($element->plugin == 'checkbox') {
                                        $elt = implode(", ", json_decode (@$element->content));
                                    }
                                    else
                                        $elt = $element->content;
                                    $forms .= '<b>'.$element->label.': </b>'.$elt.'<br/>';
                                }
                            }

                            // TABLEAU DE PLUSIEURS LIGNES
                        } elseif ($itemg->repeated > 0 || $itemg->repeated_1 > 0){
                            $forms .= '<table class="table table-bordered table-striped">
                              <thead>
                              <tr> ';

                            //-- Entrée du tableau -- */
                            //$nb_lignes = 0;
                            $t_elt = array();
                            foreach($elements as &$element) {
                                $t_elt[] = $element->name;
                                $forms .= '<th scope="col">'.$element->label.'</th>';
                            }
                            unset($element);
                            //$table = $itemt->db_table_name.'_'.$itemg->group_id.'_repeat';
                            $query = 'SELECT table_join FROM #__fabrik_joins WHERE group_id='.$itemg->group_id.' AND table_join_key like "parent_id"';
                            $this->_db->setQuery($query);
                            $table = $this->_db->loadResult();

                            if($itemg->group_id == 174)
                                $query = 'SELECT `'.implode("`,`", $t_elt).'`, id FROM '.$table.'
                                        WHERE parent_id=(SELECT id FROM '.$itemt->db_table_name.' WHERE user='.$aid.' AND fnum like '.$this->_db->Quote($fnum).') OR applicant_id='.$aid;
                            else
                                $query = 'SELECT `'.implode("`,`", $t_elt).'`, id FROM '.$table.'
                                    WHERE parent_id=(SELECT id FROM '.$itemt->db_table_name.' WHERE user='.$aid.' AND fnum like '.$this->_db->Quote($fnum).')';
                            //$forms .= $query;
                            $this->_db->setQuery($query);
                            $repeated_elements = $this->_db->loadObjectList();
                            unset($t_elt);
//print_r($repeated_elements);
                            $forms .= '</tr></thead>';
                            // -- Ligne du tableau --
                            if (count($repeated_elements)>0) {
                                $forms .= '<tbody>';
                                foreach ($repeated_elements as $r_element) {
                                    $delete_link = false;
                                    $forms .= '<tr>';
                                    $j = 0;
                                    foreach ($r_element as $key => $r_elt) {
                                        if ($key != 'id' && $key != 'parent_id' && isset($elements[$j])) {
                                            if ($elements[$j]->plugin=='date') {
                                                $date_params = json_decode($elements[$j]->params);
                                                $elt = date($date_params->date_form_format, strtotime($r_elt));
                                            }
                                            elseif ($elements[$j]->plugin=='birthday' && $r_elt>0) {
                                                $elt = JHtml::_('date', $r_elt, JText::_('DATE_FORMAT_LC'));
                                            }
                                            elseif($elements[$j]->plugin=='databasejoin') {
                                                $params = json_decode($elements[$j]->params);
                                                $select = !empty($params->join_val_column_concat)?"CONCAT(".$params->join_val_column_concat.")":$params->join_val_column;
                                                $from = $params->join_db_name;
                                                $where = $params->join_key_column.'='.$this->_db->Quote($r_elt);
                                                $query = "SELECT ".$select." FROM ".$from." WHERE ".$where;
                                                $query = preg_replace('#{thistable}#', $from, $query);
                                                $query = preg_replace('#{my->id}#', $aid, $query);
                                                $this->_db->setQuery( $query );
                                                $elt = $this->_db->loadResult();
                                            }
                                            elseif ($elements[$j]->plugin == 'checkbox') {
                                                $elt = implode(", ", json_decode (@$r_elt));
                                            }
                                            else
                                                $elt = $r_elt;
                                            //print_r($this->_mainframe->data);
                                            /*if(EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id) && !$delete_link) {
                                                //$delete_link = '<div class="comment_icon" id="training_'.$r_element->id.'"><img src="'.JURI::Base().'/media/com_emundus/images/icones/button_cancel.png" onClick="if (confirm('.htmlentities('"'.JText::_("DELETE_CONFIRM").'"').')) {deleteData('.$r_element->id.', \''.$table.'\');}"/></div>';
                                                $delete_link = '<a class=​"ui" name="delete_course" data-title="'.JText::_('DELETE_CONFIRM').'" onClick="$(\'#confirm_type\').val(this.name); $(\'#course_id\').val('.$r_element->id.'); $(\'#course_table\').val(\''.$table.'\'); $(\'.basic.modal.confirm.course\').modal(\'show\');"><i class="trash icon"></i>​</a>​';
                                            }
                                            $forms .= '<td><div id="em_training_'.$r_element->id.'" class="course '.$r_element->id.'">'.$delete_link.' '.$elt.'</div></td>';
                                            $delete_link = true;*/
                                            $forms .= '<td><div id="em_training_'.$r_element->id.'" class="course '.$r_element->id.'"> '.$elt.'</div></td>';
                                        }
                                        $j++;
                                    }
                                    $forms .= '</tr>';
                                }
                                $forms .= '</tbody>';
                            }
                            $forms .= '</table>';

                            // AFFICHAGE EN LIGNE
                        } else {
                            foreach($elements as &$element) {
                                if(!empty($element->label) && $element->label!=' ') {
                                    $query = 'SELECT `id`, `'.$element->name .'` FROM `'.$itemt->db_table_name.'` WHERE user='.$aid.' AND fnum like '.$this->_db->Quote($fnum);
                                    $this->_db->setQuery( $query );
                                    $res = $this->_db->loadRow();
                                    
                                    $element->content = @$res[1];
                                    $element->content_id = @$res[0];

                                    if ($element->plugin=='date' && $element->content>0) {
                                        $date_params = json_decode($element->params);
                                        $elt = date($date_params->date_form_format, strtotime($element->content));
                                    }
                                    elseif ($element->plugin=='birthday' && $element->content>0) {
                                        $elt = JHtml::_('date', $element->content, JText::_('DATE_FORMAT_LC'));
                                    }
                                    elseif($element->plugin=='databasejoin') {
                                        $params = json_decode($element->params);

                                        $select = !empty($params->join_val_column_concat)?"CONCAT(".$params->join_val_column_concat.")":$params->join_val_column;

                                        if($params->database_join_display_type == 'checkbox'){
                                            $elt = implode(", ", json_decode (@$element->content));
                                        }
                                        else {
                                            $from = $params->join_db_name;
                                            $where = $params->join_key_column.'='.$this->_db->Quote($element->content);
                                            $query = "SELECT ".$select." FROM ".$from." WHERE ".$where;
                                            $query = preg_replace('#{thistable}#', $from, $query);
                                            $query = preg_replace('#{my->id}#', $aid, $query);
                                            $this->_db->setQuery( $query );
                                            $elt = $this->_db->loadResult();
                                        }
                                    }
                                    elseif($element->plugin=='cascadingdropdown') {
                                        $params = json_decode($element->params);
                                        $cascadingdropdown_id = $params->cascadingdropdown_id;
                                        $r1 = explode('___', $cascadingdropdown_id);
                                        $cascadingdropdown_label = $params->cascadingdropdown_label;
                                        $r2 = explode('___', $cascadingdropdown_label);
                                        $select = !empty($params->cascadingdropdown_label_concat)?"CONCAT(".$params->cascadingdropdown_label_concat.")":$r2[1];
                                        $from = $r2[0];
                                        $where = $r1[1].'='.$this->_db->Quote($element->content);
                                        $query = "SELECT ".$select." FROM ".$from." WHERE ".$where;
                                        $query = preg_replace('#{thistable}#', $from, $query);
                                        $query = preg_replace('#{my->id}#', $aid, $query);
                                        $this->_db->setQuery( $query );
                                        $elt = $this->_db->loadResult();
                                    }
                                    elseif ($element->plugin == 'checkbox') {
                                        $elt = implode(", ", json_decode (@$element->content));
                                    }
                                    else
                                        $elt = $element->content;
                                    $forms .= '<b>'.$element->label.': </b>'.$elt.'<br/>';
                                }
                            }
                        }
                        $forms .= '</fieldset>';
                    }
                }
            }
        }
        } 
        catch(Exception $e)
        {
            error_log($e->getMessage(), 0);
            return $e->getMessage();
        }
        return $forms;
    }



    // @description  generate HTML to send to PDF librairie
    // @param   int applicant user id
    // @param   int fnum application file number
    // @return  string HTML to send to PDF librairie
    function getFormsPDF($aid, $fnum=0) {
        $tableuser = @EmundusHelperList::getFormsList($aid, $fnum);

        $forms = "<style>
                    table{
                        border-spacing: 1px;
                        background-color: #f2f2f2;
                        width: 100%;
                    } 
                    th {
                        border-spacing: 1px; 
                        color: #666666;
                        padding:5px;
                    }
                    td {
                        border-spacing: 2px;
                        background-color: #FFFFFF;
                        padding:5px;
                    }
                    </style>";
        if(isset($tableuser)) {
            foreach($tableuser as $key => $itemt) {
                $forms .= '<br><br>';
                $forms .= '<hr><h3>';
                $title = explode('-', $itemt->label);
                $forms .= $title[1];

                /*if (EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id) && $itemt->db_table_name != "#__emundus_training"){
                    $forms .= ' <a href="index.php?option=com_fabrik&view=form&formid='.$itemt->form_id.'&usekey=user&rowid='.$aid.'" alt="'.JText::_('EDIT').'" target="_blank"><i class="icon edit">'.JText::_('EDIT').'</i></a>';
                }*/

                $forms .= '</h3>';
                // liste des groupes pour le formulaire d'une table
                $query = 'SELECT ff.id, ff.group_id, fg.id, fg.label, INSTR(fg.params,"\"repeat_group_button\":\"1\"") as repeated, INSTR(fg.params,"\"repeat_group_button\":1") as repeated_1
                            FROM #__fabrik_formgroup ff, #__fabrik_groups fg
                            WHERE ff.group_id = fg.id AND
                                  ff.form_id = "'.$itemt->form_id.'" 
                            ORDER BY ff.ordering';
                $this->_db->setQuery( $query );
                $groupes = $this->_db->loadObjectList();

                /*-- Liste des groupes -- */
                foreach($groupes as $keyg => $itemg) {

                    // liste des items par groupe
                    $query = 'SELECT fe.id, fe.name, fe.label, fe.plugin, fe.params
                                FROM #__fabrik_elements fe
                                WHERE fe.published=1 AND 
                                      fe.hidden=0 AND 
                                      fe.group_id = "'.$itemg->group_id.'" 
                                ORDER BY fe.ordering';

                    $this->_db->setQuery( $query );
                    $elements = $this->_db->loadObjectList();
                    if(count($elements)>0) {
                        $forms .= '<hr><h4>';
                        $forms .= $itemg->label;
                        $forms .= '</h4>';
                        if ($itemg->repeated == 0 && $itemg->repeated_1 == 0) {
                            foreach($elements as &$iteme) {
                                $query = 'SELECT `id`, `'.$iteme->name .'` FROM `'.$itemt->db_table_name.'` WHERE user='.$aid.' AND fnum like '.$this->_db->Quote($fnum);
                                $this->_db->setQuery($query);
                                $res = $this->_db->loadRow();
                                if(count($res)>1) {
                                    $iteme->content = $res[1];
                                    $iteme->content_id = $res[0];
                                } else {
                                    $iteme->content = '';
                                    $iteme->content_id = -1;
                                }
                                if ($iteme->plugin == 'databasejoin') {
                                    $params = json_decode($iteme->params);
                                    if ($params->database_join_display_type == 'checkbox') {
                                        $query =
                                            'SELECT `id`, GROUP_CONCAT(' . $iteme->name . ', ", ") as ' . $iteme->name . '
                                                    FROM `' . $itemt->db_table_name . '_repeat_' . $iteme->name . '`
                                                    WHERE parent_id=' . $iteme->content_id . ' GROUP BY parent_id';
                                        $this->_db->setQuery($query);
                                        $res = $this->_db->loadRow();
                                        
                                        if(count($res)>1) {
                                            $iteme->content = $res[1];
                                            $iteme->content_id = $res[0];
                                        } else {
                                            $iteme->content = '';
                                            $iteme->content_id = -1;
                                        }
                                    }
                                }
                                elseif($iteme->plugin == 'checkbox') {
                                    $iteme->content = implode(", ", json_decode (@$res[1]));
                                    $iteme->content_id = $res[0];
                                }
                            }
                        }
                        unset($iteme);

                        if ($itemg->group_id == 14) {

                            foreach($elements as $element) {
                                if(!empty($element->label) && $element->label!=' ' && !empty($element->content)) {
                                    if ($element->plugin=='date' && $element->content>0) {
                                        $date_params = json_decode($element->params);
                                        $elt = date($date_params->date_form_format, strtotime($element->content));
                                    } else $elt = $element->content;
                                    
                                    $forms .= '<b>'.$element->label.': </b>'.$elt.'<br/>';
                                }
                            }

                            // TABLEAU DE PLUSIEURS LIGNES
                        } elseif (($itemg->repeated > 0 || $itemg->repeated_1 > 0) && count($elements)<7){
                            $forms .= '<p><table class="adminlist">
                              <thead>
                              <tr> ';

                            //-- Entrée du tableau -- */
                            //$nb_lignes = 0;
                            $t_elt = array();
                            foreach($elements as &$element) {
                                $t_elt[] = $element->name;
                                $forms .= '<th scope="col">'.$element->label.'</th>';
                            }
                            unset($element);
                            //$table = $itemt->db_table_name.'_'.$itemg->group_id.'_repeat';
                            $query = 'SELECT table_join FROM #__fabrik_joins WHERE group_id='.$itemg->group_id.' AND table_join_key like "parent_id"';
                            $this->_db->setQuery($query);
                            $table = $this->_db->loadResult();

                            if($itemg->group_id == 174)
                                $query = 'SELECT `'.implode("`,`", $t_elt).'`, id FROM '.$table.'
                                        WHERE parent_id=(SELECT id FROM '.$itemt->db_table_name.' WHERE user='.$aid.' AND fnum like '.$this->_db->Quote($fnum).') OR applicant_id='.$aid;
                            else
                                $query = 'SELECT `'.implode("`,`", $t_elt).'`, id FROM '.$table.'
                                    WHERE parent_id=(SELECT id FROM '.$itemt->db_table_name.' WHERE user='.$aid.' AND fnum like '.$this->_db->Quote($fnum).')';
                            //$forms .= $query;
                            $this->_db->setQuery($query);
                            $repeated_elements = $this->_db->loadObjectList();
                            unset($t_elt);
//print_r($repeated_elements);
                            $forms .= '</tr></thead><tbody>';

                            // -- Ligne du tableau --
                            if (count($repeated_elements) > 0) {
                                foreach ($repeated_elements as $r_element) {
                                    $forms .= '<tr>';
                                    $j = 0;
                                    foreach ($r_element as $key => $r_elt) {
                                        if ($key != 'id' && $key != 'parent_id' && isset($elements[$j])) {
                                            if ($elements[$j]->plugin=='date') {
                                                $date_params = json_decode($elements[$j]->params);
                                                $elt = date($date_params->date_form_format, strtotime($r_elt));
                                            }
                                            elseif ($elements[$j]->plugin=='birthday' && $r_elt>0) {
                                                $elt = JHtml::_('date', $r_elt, JText::_('DATE_FORMAT_LC'));
                                            }
                                            elseif($elements[$j]->plugin=='databasejoin') {
                                                $params = json_decode($elements[$j]->params);
                                                $select = !empty($params->join_val_column_concat)?"CONCAT(".$params->join_val_column_concat.")":$params->join_val_column;
                                                $from = $params->join_db_name;
                                                $where = $params->join_key_column.'='.$this->_db->Quote($r_elt);
                                                $query = "SELECT ".$select." FROM ".$from." WHERE ".$where;
                                                $query = preg_replace('#{thistable}#', $from, $query);
                                                $query = preg_replace('#{my->id}#', $aid, $query);
                                                $this->_db->setQuery( $query );
                                                $elt = $this->_db->loadResult();
                                            }
                                            elseif($elements[$j]->plugin == 'checkbox') {
                                                $elt = implode(", ", json_decode (@$r_elt));
                                            }
                                            else
                                                $elt = $r_elt;
                                            // trick to prevent from blank value in PDF when string is to long without spaces (usually emails)
                                            $elt = str_replace('@', '<br>@', $elt);
                                            $forms .= '<td><div id="em_training_'.$r_element->id.'" class="course '.$r_element->id.'">'.$elt.'</div></td>';
                                        }
                                        $j++;
                                    }
                                    $forms .= '</tr>';
                                }
                            }
                            $forms .= '</tbody></table></p>';

                            // AFFICHAGE EN LIGNE
                        }elseif ($itemg->repeated > 0 || $itemg->repeated_1 > 0) {

                            //-- Entrée du tableau -- */
                            $t_elt = array();
                            foreach($elements as &$element) {
                                $t_elt[] = $element->name;
                                //$forms .= '<th scope="col">'.$element->label.'</th>';
                            }
                            unset($element);

                            $query = 'SELECT table_join FROM #__fabrik_joins WHERE group_id='.$itemg->group_id.' AND table_join_key like "parent_id"';
                            $this->_db->setQuery($query);
                            $table = $this->_db->loadResult();

                            if($itemg->group_id == 174)
                                $query = 'SELECT `'.implode("`,`", $t_elt).'`, id FROM '.$table.'
                                        WHERE parent_id=(SELECT id FROM '.$itemt->db_table_name.' WHERE user='.$aid.' AND fnum like '.$this->_db->Quote($fnum).') OR applicant_id='.$aid;
                            else
                                $query = 'SELECT `'.implode("`,`", $t_elt).'`, id FROM '.$table.'
                                    WHERE parent_id=(SELECT id FROM '.$itemt->db_table_name.' WHERE user='.$aid.' AND fnum like '.$this->_db->Quote($fnum).')';

                            $this->_db->setQuery($query);
                            $repeated_elements = $this->_db->loadObjectList();
                            unset($t_elt);


                            // -- Ligne du tableau --
                            if (count($repeated_elements) > 0) {
                                $i = 1;
                                foreach ($repeated_elements as $r_element) {
                                    $j = 0;
                                    $forms .= '<br>---- '.$i.' ----';
                                    foreach ($r_element as $key => $r_elt) {
                                        if (!empty($r_elt)) {                        
                                            if ($key != 'id' && $key != 'parent_id' && isset($elements[$j])) {
                                                if ($elements[$j]->plugin=='date') {
                                                    $date_params = json_decode($elements[$j]->params);
                                                    $elt = date($date_params->date_form_format, strtotime($r_elt));
                                                }
                                                elseif ($elements[$j]->plugin=='birthday' && $r_elt>0) {
                                                    $elt = JHtml::_('date', $r_elt, JText::_('DATE_FORMAT_LC'));
                                                }
                                                elseif($elements[$j]->plugin=='databasejoin') {
                                                    $params = json_decode($elements[$j]->params);
                                                    $select = !empty($params->join_val_column_concat)?"CONCAT(".$params->join_val_column_concat.")":$params->join_val_column;
                                                    $from = $params->join_db_name;
                                                    $where = $params->join_key_column.'='.$this->_db->Quote($r_elt);
                                                    $query = "SELECT ".$select." FROM ".$from." WHERE ".$where;
                                                    $query = preg_replace('#{thistable}#', $from, $query);
                                                    $query = preg_replace('#{my->id}#', $aid, $query);
                                                    $this->_db->setQuery( $query );
                                                    $elt = $this->_db->loadResult();
                                                }
                                                elseif($element->plugin=='cascadingdropdown') {
                                                    $params = json_decode($elements[$j]->params);
                                                    $cascadingdropdown_id = $params->cascadingdropdown_id;
                                                    $r1 = explode('___', $cascadingdropdown_id);
                                                    $cascadingdropdown_label = $params->cascadingdropdown_label;
                                                    $r2 = explode('___', $cascadingdropdown_label);
                                                    $select = !empty($params->cascadingdropdown_label_concat)?"CONCAT(".$params->cascadingdropdown_label_concat.")":$r2[1];
                                                    $from = $r2[0];
                                                    $where = $r1[1].'='.$this->_db->Quote($element->content);
                                                    $query = "SELECT ".$select." FROM ".$from." WHERE ".$where;
                                                    $query = preg_replace('#{thistable}#', $from, $query);
                                                    $query = preg_replace('#{my->id}#', $aid, $query);
                                                    $this->_db->setQuery( $query );
                                                    $elt = $this->_db->loadResult();
                                                }
                                                elseif($elements[$j]->plugin=='textarea') 
                                                    $elt = '<br>'.$r_elt;
                                                elseif($elements[$j]->plugin == 'checkbox') {
                                                    $elt = implode(", ", json_decode (@$r_elt));
                                                }
                                                else
                                                    $elt = $r_elt;
                                                if (!empty($elt)) {
                                                    $forms .= '<br><span style="color: #000071;"><b>'.$elements[$j]->label.'</b></span>: '.$elt;
                                                }
                                            }
                                        }
                                        $j++;
                                    }
                                    $i++;
                                }
                            }

                            // AFFICHAGE EN LIGNE
                        } else {
                            foreach($elements as $element) {
                                if (!empty($element->content)) {
                                    if(!empty($element->label) && $element->label!=' ') {
                                        if ($element->plugin=='date' && $element->content>0) {
                                            $date_params = json_decode($element->params);
                                            $elt = date($date_params->date_form_format, strtotime($element->content));
                                        }
                                        elseif ($element->plugin=='birthday' && $element->content>0) {
                                            $elt = JHtml::_('date', $element->content, JText::_('DATE_FORMAT_LC'));
                                        }
                                        elseif($element->plugin=='databasejoin') {
                                            $params = json_decode($element->params);

                                            $select = !empty($params->join_val_column_concat)?"CONCAT(".$params->join_val_column_concat.")":$params->join_val_column;

                                            if($params->database_join_display_type == 'checkbox'){
                                                $elt = implode(", ", json_decode (@$element->content));

                                            }
                                            else {
                                                $from = $params->join_db_name;
                                                $where = $params->join_key_column.'='.$this->_db->Quote($element->content);
                                                $query = "SELECT ".$select." FROM ".$from." WHERE ".$where;
                                                $query = preg_replace('#{thistable}#', $from, $query);
                                                $query = preg_replace('#{my->id}#', $aid, $query);
                                                $this->_db->setQuery( $query );
                                                $elt = $this->_db->loadResult();
                                            }
                                        }
                                        elseif($element->plugin=='cascadingdropdown') {
                                            $params = json_decode($element->params);
                                            $cascadingdropdown_id = $params->cascadingdropdown_id;
                                            $r1 = explode('___', $cascadingdropdown_id);
                                            $cascadingdropdown_label = $params->cascadingdropdown_label;
                                            $r2 = explode('___', $cascadingdropdown_label);
                                            $select = !empty($params->cascadingdropdown_label_concat)?"CONCAT(".$params->cascadingdropdown_label_concat.")":$r2[1];
                                            $from = $r2[0];
                                            $where = $r1[1].'='.$this->_db->Quote($element->content);
                                            $query = "SELECT ".$select." FROM ".$from." WHERE ".$where;
                                            $query = preg_replace('#{thistable}#', $from, $query);
                                            $query = preg_replace('#{my->id}#', $aid, $query);
                                            $this->_db->setQuery( $query );
                                            $elt = $this->_db->loadResult();
                                        }
                                        elseif($element->plugin=='textarea')
                                            $elt = '<br>'.$element->content;
                                        elseif($elements[$j]->plugin == 'checkbox') {
                                            $elt = implode(", ", json_decode (@$element->content));
                                        }
                                        else
                                            $elt = $element->content;
                                        $forms .= '<br><span style="color: #000071;"><b>'.$element->label.'</b></span>: '.$elt;
                                    }
                                }
                            }
                        }
                        //$forms .= '</fieldset>';
                    }
                }
            }
        }
        return $forms;
    }

    public function getFormsPDFElts($aid, $elts, $options) {

        $tableuser = @EmundusHelperList::getFormsListByProfileID($options['profile_id']);

        $forms = "<style>
table{
    border-spacing: 1px;
    background-color: #f2f2f2;
    width: 100%;
} 
th {
    border-spacing: 1px; color: #666666;
}
td {
    border-spacing: 1px;
    background-color: #FFFFFF;
}
</style>";
        if(isset($tableuser)) {
            foreach($tableuser as $key => $itemt) {
                //$forms .= '<br><br>';
                $forms .= ($options['show_list_label']==1)?'<h2>'.$itemt->label.'</h2>':'';
                // liste des groupes pour le formulaire d'une table
                $query = 'SELECT ff.id, ff.group_id, fg.id, fg.label, INSTR(fg.params,"\"repeat_group_button\":\"1\"") as repeated, INSTR(fg.params,"\"repeat_group_button\":1") as repeated_1
                            FROM #__fabrik_formgroup ff, #__fabrik_groups fg
                            WHERE ff.group_id = fg.id AND
                                  ff.form_id = "'.$itemt->form_id.'" 
                            ORDER BY ff.ordering';
                $this->_db->setQuery( $query );
                $groupes = $this->_db->loadObjectList();

                /*-- Liste des groupes -- */
                foreach($groupes as $keyg => $itemg) {
                    // liste des items par groupe
                    $query = 'SELECT fe.id, fe.name, fe.label, fe.plugin, fe.params
                                FROM #__fabrik_elements fe
                                WHERE fe.published=1 AND 
                                      fe.hidden=0 AND 
                                      fe.group_id = "'.$itemg->group_id.'" AND 
                                      fe.id IN ('.implode(',', $elts).')
                                ORDER BY fe.ordering';
                    $this->_db->setQuery( $query );
                    $elements = $this->_db->loadObjectList();
                    if(count($elements)>0) {
                        $forms .= ($options['show_group_label']==1)?'<h3>'.$itemg->label.'</h3>':'';
                        foreach($elements as &$iteme) {
                            $where = 'user='.$aid;
                            $where .= $options['rowid']>0?' AND id='.$options['rowid']:'';
                            $query = 'SELECT `'.$iteme->name .'` FROM `'.$itemt->db_table_name.'` WHERE '.$where;
                            $this->_db->setQuery( $query );
                            $iteme->content = $this->_db->loadResult();
                        }
                        unset($iteme);

                        if ($itemg->group_id == 14) {

                            foreach($elements as $element) {
                                if(!empty($element->label) && $element->label!=' ') {
                                    if ($element->plugin=='date' && $element->content>0) {
                                        $date_params = json_decode($element->params);
                                        $elt = date($date_params->date_form_format, strtotime($element->content));
                                    } else $elt = $element->content;
                                    $forms .= '<p><b>'.$element->label.': </b>'.$elt.'</p>';
                                }
                            }

                            // TABLEAU DE PLUSIEURS LIGNES
                        } elseif ($itemg->repeated > 0 || $itemg->repeated_1 > 0){
                            $forms .= '<p><table class="adminlist">
                              <thead>
                              <tr> ';

                            //-- Entrée du tableau -- */
                            //$nb_lignes = 0;
                            $t_elt = array();
                            foreach($elements as &$element) {
                                $t_elt[] = $element->name;
                                $forms .= '<th scope="col">'.$element->label.'</th>';
                            }
                            unset($element);
                            //$table = $itemt->db_table_name.'_'.$itemg->group_id.'_repeat';
                            $query = 'SELECT table_join FROM #__fabrik_joins WHERE group_id='.$itemg->group_id;
                            $this->_db->setQuery($query);
                            $table = $this->_db->loadResult();

                            if($itemg->group_id == 174)
                                $query = 'SELECT '.implode(",", $t_elt).', id FROM '.$table.'
                                        WHERE parent_id=(SELECT id FROM '.$itemt->db_table_name.' WHERE user='.$aid.') OR applicant_id='.$aid;
                            else
                                $query = 'SELECT '.implode(",", $t_elt).', id FROM '.$table.'
                                    WHERE parent_id=(SELECT id FROM '.$itemt->db_table_name.' WHERE user='.$aid.')';
                            //$forms .= $query;
                            $this->_db->setQuery($query);
                            $repeated_elements = $this->_db->loadObjectList();
                            unset($t_elt);
//print_r($repeated_elements);
                            $forms .= '</tr></thead><tbody>';
                            // -- Ligne du tableau --
                            foreach ($repeated_elements as $r_element) {
                                $forms .= '<tr>';
                                $j = 0;
                                foreach ($r_element as $key => $r_elt) {
                                    if ($key != 'id' && $key != 'parent_id' && isset($elements[$j])) {
                                        if ($elements[$j]->plugin=='date') {
                                            $date_params = json_decode($elements[$j]->params);
                                            $elt = date($date_params->date_form_format, strtotime($r_elt));
                                        }
                                        elseif ($elements[$j]->plugin=='birthday' && $r_elt>0) {
                                            //$date_params = json_decode($element->params);
                                            $elt = strftime('%d/%m/%Y', strtotime($r_elt));
                                        }
                                        elseif($elements[$j]->plugin=='databasejoin') {
                                            $params = json_decode($elements[$j]->params);
                                            $select = !empty($params->join_val_column_concat)?"CONCAT(".$params->join_val_column_concat.")":$params->join_val_column;
                                            $from = $params->join_db_name;
                                            $where = $params->join_key_column.'='.$this->_db->Quote($r_elt);
                                            $query = "SELECT ".$select." FROM ".$from." WHERE ".$where;
                                            $query = preg_replace('#{thistable}#', $from, $query);
                                            $query = preg_replace('#{my->id}#', $aid, $query);
                                            $this->_db->setQuery( $query );
                                            $elt = $this->_db->loadResult();
                                        }
                                        elseif($elements[$j]->plugin == 'checkbox') {
                                            $elt = implode(", ", json_decode (@$r_elt));
                                        }
                                        else
                                            $elt = $r_elt;

                                        $forms .= '<td><div id="em_training_'.$r_element->id.'" class="course '.$r_element->id.'">'.$elt.'</div></td>';
                                    }
                                    $j++;
                                }
                                $forms .= '</tr>';
                            }
                            $forms .= '</tbody></table></p>';

                            // AFFICHAGE EN LIGNE
                        } else {
                            foreach($elements as &$element) {
                                if(!empty($element->label) && $element->label!=' ') {
                                    if ($element->plugin=='date' && $element->content>0) {
                                        $date_params = json_decode($element->params);
                                       // $elt = strftime($date_params->date_form_format, strtotime($element->content));
                                        $elt = date($date_params->date_form_format, strtotime($element->content));

                                    }
                                    elseif ($element->plugin=='birthday' && $element->content>0) {
                                        //$date_params = json_decode($element->params);
                                        $elt = strftime('%d/%m/%Y', strtotime($element->content));
                                    }
                                    elseif($element->plugin=='databasejoin') {
                                        $params = json_decode($element->params);
                                        $select = !empty($params->join_val_column_concat)?"CONCAT(".$params->join_val_column_concat.")":$params->join_val_column;
                                        $from = $params->join_db_name;
                                        $where = $params->join_key_column.'='.$this->_db->Quote($element->content);
                                        $query = "SELECT ".$select." FROM ".$from." WHERE ".$where;
                                        $query = preg_replace('#{thistable}#', $from, $query);
                                        $query = preg_replace('#{my->id}#', $aid, $query);
                                        $this->_db->setQuery( $query );
                                        $elt = $this->_db->loadResult();
                                    }
                                    elseif($element->plugin=='cascadingdropdown') {
                                        $params = json_decode($element->params);
                                        $cascadingdropdown_id = $params->cascadingdropdown_id;
                                        $r1 = explode('___', $cascadingdropdown_id);
                                        $cascadingdropdown_label = $params->cascadingdropdown_label;
                                        $r2 = explode('___', $cascadingdropdown_label);
                                        $select = !empty($params->cascadingdropdown_label_concat)?"CONCAT(".$params->cascadingdropdown_label_concat.")":$r2[1];
                                        $from = $r2[0];
                                        $where = $r1[1].'='.$this->_db->Quote($element->content);
                                        $query = "SELECT ".$select." FROM ".$from." WHERE ".$where;
                                        $query = preg_replace('#{thistable}#', $from, $query);
                                        $query = preg_replace('#{my->id}#', $aid, $query);
                                        $this->_db->setQuery( $query );
                                        $elt = $this->_db->loadResult();
                                    }
                                    elseif($element->plugin == 'checkbox') {
                                        $elt = implode(", ", json_decode (@$element->content));
                                    }
                                    else
                                        $elt = $element->content;
                                    $forms .= '<p><b>'.$element->label.': </b>'.$elt.'</p>';
                                }
                            }
                        }
                        //$forms .= '</fieldset>';
                    }
                }
            }
        }
        return $forms;
    }

    public function getEmail($user_id){
        $query = 'SELECT *
        FROM #__messages as email
        LEFT JOIN #__users as user ON user.id=email.user_id_from 
        LEFT JOIN #__emundus_users as eu ON eu.user_id=user.id
        WHERE email.user_id_to ='.$user_id.' ORDER BY `date_time` DESC';
        $this->_db->setQuery($query);
        $results['to'] = $this->_db->loadObjectList('message_id');

        $query = 'SELECT *
        FROM #__messages as email
        LEFT JOIN #__users as user ON user.id=email.user_id_to 
        LEFT JOIN #__emundus_users as eu ON eu.user_id=user.id 
        WHERE email.user_id_from ='.$user_id.' ORDER BY `date_time` DESC';
        $this->_db->setQuery($query);
        $results['from'] = $this->_db->loadObjectList('message_id');

        return $results;
    }

    public function getActionMenu()
    {
        $juser = JFactory::getUser();

        try
        {
            $db = $this->getDbo();
            $grUser = $juser->getAuthorisedViewLevels();

            $query = 'SELECT m.id, m.title, m.link, m.lft, m.rgt, m.note
                        FROM #__menu as m 
                        WHERE m.published=1 AND m.menutype = "application" and m.access in ('.implode(',', $grUser).')
                        ORDER BY m.lft';

            $db->setQuery($query);
            return $db->loadAssocList();

        }
        catch(Exception $e)
        {
            return false;
        }
    }

    public function getProgramSynthesis($cid)
    {
        try
        {
            $db = $this->getDbo();
            $query = 'select p.synthesis, p.id, p.label from #__emundus_setup_programmes as p left join #__emundus_setup_campaigns as c on c.training = p.code where c.id='.$cid;
            $db->setQuery($query);
            return $db->loadObject();
        }
        catch(Exception $e)
        {
            return null;
        }
    }

    public function getAttachments($ids)
    {
        try
        {
            $query = "SELECT id, fnum, user_id, filename FROM #__emundus_uploads WHERE id in (".implode(',', $ids).")";
            $this->_db->setQuery($query);
            return $this->_db->loadObjectList();
        }
        catch(Exception $e)
        {
            error_log($e->getMessage(), 0);
            return false;
        }
    }

    public function getAttachmentsByFnum($fnum, $attachment_id=null)
    {
        try
        {
            $query = "SELECT * FROM #__emundus_uploads WHERE fnum like ".$this->_db->quote($fnum);
            if (isset($attachment_id)) {
                $query .= " AND attachment_id=".$attachment_id;
            }
            $this->_db->setQuery($query);
            return $this->_db->loadObjectList();
        }
        catch(Exception $e)
        {
            error_log($e->getMessage(), 0);
            return false;
        }
    }

    public function getAccessFnum($fnum)
    {
        $query = "SELECT jecc.fnum, jesg.label as gname, jea.*, jesa.label as aname FROM #__emundus_campaign_candidature as jecc
                    LEFT JOIN #__emundus_setup_campaigns as jesc on jesc.id = jecc.campaign_id
                    LEFT JOIN #__emundus_setup_programmes as jesp on jesp.code = jesc.training
                    LEFT JOIN #__emundus_setup_groups_repeat_course as jesgrc on jesgrc.course = jesp.code
                    LEFT JOIN #__emundus_setup_groups as jesg on jesg.id = jesgrc.parent_id
                    LEFT JOIN #__emundus_acl as jea on jea.group_id = jesg.id
                    LEFT JOIN #__emundus_setup_actions as jesa on jesa.id = jea.action_id
                    WHERE jecc.fnum like '".$fnum."' and jesa.status = 1 order by jecc.fnum, jea.group_id, jea.action_id";

        try
        {
            $db = $this->getDbo();
            $db->setQuery($query);
            $res = $db->loadAssocList();
            $access = array();
            foreach($res as $r)
            {
                $access['groups'][$r['group_id']]['gname'] = $r['gname'];
                $access['groups'][$r['group_id']]['isAssoc'] = false;
                $access['groups'][$r['group_id']]['isACL'] = true;
                $access['groups'][$r['group_id']]['actions'][$r['action_id']]['aname'] = $r['aname'];
                $access['groups'][$r['group_id']]['actions'][$r['action_id']]['c'] = $r['c'];
                $access['groups'][$r['group_id']]['actions'][$r['action_id']]['r'] = $r['r'];
                $access['groups'][$r['group_id']]['actions'][$r['action_id']]['u'] = $r['u'];
                $access['groups'][$r['group_id']]['actions'][$r['action_id']]['d'] = $r['d'];
            }
            $query = "SELECT jega.* , jesg.label as gname, jesa.label as aname FROM #__emundus_group_assoc as jega
                      LEFT JOIN #__emundus_setup_groups as jesg on jesg.id = jega.group_id
                      LEFT JOIN #__emundus_setup_actions as jesa ON jesa.id = jega.action_id
                      WHERE  jega.fnum like ".$db->quote($fnum)." and jesa.status = 1 ORDER BY jega.fnum, jega.group_id, jega.action_id";
            $db->setQuery($query);
            $res = $db->loadAssocList();
            foreach($res as $r)
            {
                if(isset($access['groups'][$r['group_id']]['actions'][$r['action_id']]))
                {
                    $access['groups'][$r['group_id']]['isAssoc'] = true;
                    $access['groups'][$r['group_id']]['actions'][$r['action_id']]['c'] += $r['c'];
                    $access['groups'][$r['group_id']]['actions'][$r['action_id']]['r'] += $r['r'];
                    $access['groups'][$r['group_id']]['actions'][$r['action_id']]['u'] += $r['u'];
                    $access['groups'][$r['group_id']]['actions'][$r['action_id']]['d'] += $r['d'];
                }
                else
                {
                    $access['groups'][$r['group_id']]['gname'] = $r['gname'];
                    $access['groups'][$r['group_id']]['isAssoc'] = true;
                    $access['groups'][$r['group_id']]['isACL'] = false;
                    $access['groups'][$r['group_id']]['actions'][$r['action_id']]['aname'] = $r['aname'];
                    $access['groups'][$r['group_id']]['actions'][$r['action_id']]['c'] = $r['c'];
                    $access['groups'][$r['group_id']]['actions'][$r['action_id']]['r'] = $r['r'];
                    $access['groups'][$r['group_id']]['actions'][$r['action_id']]['u'] = $r['u'];
                    $access['groups'][$r['group_id']]['actions'][$r['action_id']]['d'] = $r['d'];
                }
            }

            $query = "SELECT jeua.*, ju.name as uname, jesa.label as aname
                        FROM #__emundus_users_assoc as jeua
                        LEFT JOIN #__users as ju on ju.id = jeua.user_id
                        LEFT JOIN   #__emundus_setup_actions as jesa on jesa.id = jeua.action_id
                        where  jeua.fnum like '".$fnum."' and jesa.status = 1
                        ORDER BY jeua.fnum, jeua.user_id, jeua.action_id";
            $db->setQuery($query);
            $res = $db->loadAssocList();
            foreach($res as $r)
            {
                if(isset($access['groups'][$r['user_id']]['actions'][$r['action_id']]))
                {
                    $access['users'][$r['user_id']]['actions'][$r['action_id']]['c'] += $r['c'];
                    $access['users'][$r['user_id']]['actions'][$r['action_id']]['r'] += $r['r'];
                    $access['users'][$r['user_id']]['actions'][$r['action_id']]['u'] += $r['u'];
                    $access['users'][$r['user_id']]['actions'][$r['action_id']]['d'] += $r['d'];
                }
                else
                {
                    $access['users'][$r['user_id']]['uname'] = $r['uname'];
                    $access['users'][$r['user_id']]['actions'][$r['action_id']]['aname'] = $r['aname'];
                    $access['users'][$r['user_id']]['actions'][$r['action_id']]['c'] = $r['c'];
                    $access['users'][$r['user_id']]['actions'][$r['action_id']]['r'] = $r['r'];
                    $access['users'][$r['user_id']]['actions'][$r['action_id']]['u'] = $r['u'];
                    $access['users'][$r['user_id']]['actions'][$r['action_id']]['d'] = $r['d'];
                }

            }
            return $access;
        }
        catch(Exception $e)
        {
            error_log($e->getMessage(), 0);
            return false;
        }
    }

    public function getActions()
    {
        $dbo = $this->getDbo();
        try
        {
            $query = 'select * from #__emundus_setup_actions ';
            $dbo->setQuery($query);
            return $dbo->loadAssocList('id');
        }
        catch(Exception $e)
        {
            throw $e;
        }
    }

    public function checkGroupAssoc($fnum, $gid, $aid = null)
    {
        $dbo = $this->getDbo();
        try
        {
            if(!is_null($aid))
            {
                $query = "select * from #__emundus_group_assoc where `action_id` = $aid and  `group_id` = $gid and `fnum` like ".$dbo->quote($fnum);
            }
            else
            {
                $query = "select * from #__emundus_group_assoc where `group_id` = $gid and `fnum` like ".$dbo->quote($fnum);
            }
            $dbo->setQuery($query);
            return $dbo->loadObject();
        }
        catch(Exception $e)
        {
            throw $e;
        }
    }

    public function updateGroupAccess($fnum, $gid, $actionId, $crud, $value)
    {
        $dbo = $this->getDbo();
        try
        {
            if($this->checkGroupAssoc($fnum, $gid) !== null)
            {
                if($this->checkGroupAssoc($fnum, $gid, $actionId) !== null)
                {
                    $query = "update #__emundus_group_assoc set ".$dbo->quoteName($crud)." = ".$value.
                        " where `group_id` = $gid and `action_id` = $actionId and `fnum` like ".$dbo->quote($fnum);
                    $dbo->setQuery($query);
                    return $dbo->execute();
                }
                else
                {
                    return $this->_addGroupAssoc($fnum, $crud, $actionId, $gid, $value);
                }
            }
            else
            {
                return $this->_addGroupAssoc($fnum, $crud, $actionId, $gid, $value);
            }
        }
        catch(Exception $e)
        {
            throw $e;
        }
    }

    private function _addGroupAssoc($fnum, $crud, $aid, $gid, $value)
    {
        $dbo = $this->getDbo();
        $actionQuery = "select c, r, u, d from #__emundus_acl where action_id = $aid  and  group_id = $gid";
        $dbo->setQuery($actionQuery);
        $actions = $dbo->loadAssoc();
        $actions[$crud] = $value;
        $query = "INSERT INTO `#__emundus_group_assoc`(`group_id`, `action_id`, `fnum`, `c`, `r`, `u`, `d`) VALUES ($gid, $aid, ".$dbo->quote($fnum).",{$actions['c']}, {$actions['r']}, {$actions['u']}, {$actions['d']})";
        $dbo->setQuery($query);
        return $dbo->execute();
    }

    public function checkUserAssoc($fnum, $uid, $aid = null)
    {
        $dbo = $this->getDbo();
        try
        {
            if(!is_null($aid))
            {
                $query = "select * from #__emundus_users_assoc where `action_id` = $aid and  `user_id` = $uid and `fnum` like ".$dbo->quote($fnum);
            }
            else
            {
                $query = "select * from #__emundus_users_assoc where `user_id` = $uid and `fnum` like ".$dbo->quote($fnum);
            }
            $dbo->setQuery($query);
            return $dbo->loadObject();
        }
        catch(Exception $e)
        {
            throw $e;
        }
    }

    private function _addUserAssoc($fnum, $crud, $aid, $uid, $value)
    {
        $dbo = $this->getDbo();
        $actionQuery = "select jea.c, jea.r, jea.u, jea.d from #__emundus_acl as jea left join #__emundus_groups as jeg on jeg.group_id = jea.group_id
        where jea.action_id = {$aid}  and jeg.user_id  = {$uid}";
        $dbo->setQuery($actionQuery);
        $actions = $dbo->loadAssoc();
        $actionQuery = "select jega.c, jega.r, jega.u, jega.d from #__emundus_group_assoc as jega left join #__emundus_groups as jeg on jeg.group_id = jega.group_id
        where jega.action_id = {$aid} and jeg.user_id  = {$uid} and jega.fnum like {$dbo->quote($fnum)}";
        $dbo->setQuery($actionQuery);
        $actionAssoc = $dbo->loadAssoc();
        if(!empty($actionAssoc))
        {
            $actions['c'] += $actionAssoc['c'];
            $actions['r'] += $actionAssoc['r'];
            $actions['u'] += $actionAssoc['u'];
            $actions['d'] += $actionAssoc['d'];
        }
        $actions[$crud] = $value;
        $query = "INSERT INTO `#__emundus_group_assoc`(`user_id`, `action_id`, `fnum`, `c`, `r`, `u`, `d`) VALUES ($uid, $aid, ".$dbo->quote($fnum).",{$actions['c']}, {$actions['r']}, {$actions['u']}, {$actions['d']})";
        $dbo->setQuery($query);
        return $dbo->execute();
    }

    public function updateUserAccess($fnum, $uid, $actionId, $crud, $value)
    {
        $dbo = $this->getDbo();
        try
        {
            if($this->checkUserAssoc($fnum, $uid) !== null)
            {
                if($this->checkUserAssoc($fnum, $uid, $actionId) !== null)
                {
                    $query = "update #__emundus_users_assoc set ".$dbo->quoteName($crud)." = ".$value.
                        " where `user_id` = $uid and `action_id` = $actionId and `fnum` like ".$dbo->quote($fnum);
                    $dbo->setQuery($query);
                    return $dbo->execute();
                }
                else
                {
                    return $this->_addUserAssoc($fnum, $crud, $actionId, $uid, $value);
                }
            }
            else
            {
                return $this->_addUserAssoc($fnum, $crud, $actionId, $uid, $value);
            }
        }
        catch(Exception $e)
        {
            throw $e;
        }
    }

    public function deleteGroupAccess($fnum, $gid)
    {
        $dbo = $this->getDbo();
        try
        {
            $query = "delete from #__emundus_group_assoc  where `group_id` = $gid and `fnum` like ".$dbo->quote($fnum);
            $dbo->setQuery($query);
            return $dbo->execute();
        }
        catch(Exception $e)
        {
            throw $e;
        }
    }

    public function deleteUserAccess($fnum, $uid)
    {
        $dbo = $this->getDbo();
        try
        {
            $query = "delete from #__emundus_users_assoc where `user_id` = $uid and `fnum` like ".$dbo->quote($fnum);
            $dbo->setQuery($query);
            return $dbo->execute();
        }
        catch(Exception $e)
        {
            throw $e;
        }
    }

    public function getApplications($uid)
    {
        $db = $this->getDbo();
        try
        {
            $query = 'SELECT ecc.*, esc.*, ess.step, ess.value, ess.class
                        FROM #__emundus_campaign_candidature AS ecc
                        LEFT JOIN #__emundus_setup_campaigns AS esc ON esc.id=ecc.campaign_id
                        LEFT JOIN #__emundus_setup_status AS ess ON ess.step=ecc.status
                        WHERE ecc.applicant_id ='.$uid.' 
                        ORDER BY esc.end_date DESC';
    //echo str_replace('#_', 'jos', $query);
            $db->setQuery($query);
            $result = $db->loadObjectList('fnum');
            return (array) $result;
        }
        catch(Exception $e)
        {
            throw $e;
        }
    }

    public function getApplication($fnum)
    {
        $dbo = $this->getDbo();
        try
        {
            $query = 'SELECT ecc.*, esc.*, ess.step, ess.value, ess.class
                        FROM #__emundus_campaign_candidature AS ecc
                        LEFT JOIN #__emundus_setup_campaigns AS esc ON esc.id=ecc.campaign_id
                        LEFT JOIN #__emundus_setup_status AS ess ON ess.step=ecc.status
                        WHERE ecc.fnum like '.$dbo->Quote($fnum).' 
                        ORDER BY esc.end_date DESC';
    //echo str_replace('#_', 'jos', $query);
            $dbo->setQuery($query);
            $result = $dbo->loadObject();
            return $result;
        }
        catch(Exception $e)
        {
            throw $e;
        }
    }

    /**
     * Return the order for current fnum. If an order with confirmed status is found for fnum campaign period, then return the order
     * @param $fnumInfos
     * @return bool|mixed
     */
    public function getHikashopOrder($fnumInfos)
    {
        $dbo = $this->getDbo();
        try
        {
            $query = 'SELECT ho.*, hu.user_cms_id
                        FROM #__hikashop_order ho
                        LEFT JOIN #__hikashop_user hu on hu.user_id=ho.order_user_id
                        WHERE hu.user_cms_id='.$fnumInfos['applicant_id'].' 
                        AND ho.order_status like "confirmed"  
                        AND ho.order_created >= '.strtotime($fnumInfos['start_date']).' 
                        AND ho.order_created <= '.strtotime($fnumInfos['end_date']);
//echo str_replace('#_', 'jos', $query);
            $dbo->setQuery($query);
            $result = $dbo->loadObject();
            return $result;
        }
        catch (Exception $e)
        {
            echo $e->getMessage();
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    /**
     * Return the checkout URL order for current fnum. 
     * @param $pid      the applicant's profile_id
     * @return bool|mixed
     */
    public function getHikashopCheckoutUrl($pid)
    {
        $dbo = $this->getDbo();
        try
        {
            $query = 'SELECT CONCAT(link, "&Itemid=", id) as url
                        FROM #__menu
                        WHERE alias like "checkout'.$pid.'"';
//echo str_replace('#_', 'jos', $query);
            $dbo->setQuery($query);
            $url = $dbo->loadResult();
            return $url;
        }
        catch (Exception $e)
        {
            echo $e->getMessage();
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    /**
     * Duplicate an application file (form data)
     * @param $fnum_from      the fnum of the source
     * @param $fnum_to      the fnum of the duplicated application
     * @param $pid          the profile_id to get list of forms
     * @return bool
     */
    public function copyApplication($fnum_from, $fnum_to, $pid = null)
    {
        $db = JFactory::getDbo();
        
        try
        {
            if (empty($pid)) {
                $profiles = new EmundusModelProfile();

                $fnumInfos = $profiles->getFnumDetails($fnum_from);
                $pid = (isset($fnumInfos['profile_id_form']) && !empty($fnumInfos['profile_id_form']))?$fnumInfos['profile_id_form']:$fnumInfos['profile_id'];
            }

            $forms = @EmundusHelperMenu::buildMenuQuery($pid);
//echo "<hr><pre>";

            foreach ($forms as $key => $form) {
                $query = 'SELECT * FROM '.$form->db_table_name.' WHERE fnum like '.$db->Quote($fnum_from);
                $db->setQuery( $query );
                $stored = $db->loadAssoc();
//var_dump($query); var_dump($stored); echo "<hr>STORED";
                if (count($stored) > 0) {
                    // update form data
                    $parent_id = $stored['id'];
                    unset($stored['id']);
                    $stored['fnum'] = $fnum_to;
                    
                    $query = 'INSERT INTO '.$form->db_table_name.' (`'.implode('`,`', array_keys($stored)).'`) VALUES('.implode(',', $db->Quote($stored)).')';
                    $db->setQuery( $query );
                    $db->execute();
                    $id = $db->insertid();

//var_dump($query); echo "<hr>";
 
                    // liste des groupes pour le formulaire d'une table
                    $query = 'SELECT ff.id, ff.group_id, fe.name, fg.id, fg.label, (IF( ISNULL(fj.table_join), fl.db_table_name, fj.table_join)) as `table`, fg.params as `gparams`
                                FROM #__fabrik_formgroup ff
                                LEFT JOIN #__fabrik_lists fl ON fl.form_id=ff.form_id 
                                LEFT JOIN #__fabrik_groups fg ON fg.id=ff.group_id 
                                LEFT JOIN #__fabrik_elements fe ON fe.group_id=fg.id
                                LEFT JOIN #__fabrik_joins AS fj ON (fj.group_id = fe.group_id AND fj.list_id != 0 AND fj.element_id = 0)
                                WHERE ff.form_id = "'.$form->form_id.'" 
                                ORDER BY ff.ordering';
//echo str_replace("#_", "jos", $query);

                    $db->setQuery( $query );
                    $groups = $db->loadObjectList();
//var_dump($groups); echo "<hr>";

                    // get data and update current form
                    $data   = array();
                    if (count($groups) > 0) {
                        foreach ($groups as $key => $group) {
                            $group_params = json_decode($group->gparams); 
                            if (@$group_params->repeat_group_button == 1) {
                                $data[$group->group_id]['repeat_group'] = $group_params->repeat_group_button;
                                $data[$group->group_id]['group_id'] = $group->group_id;
                                $data[$group->group_id]['element_name'][] = $group->name;
                                $data[$group->group_id]['table'] = $group->table;
                                //$data[$group->group_id]['table'] = $form->db_table_name.'_'.$group->group_id.'_repeat';
                            }       
                        }
                        if (count($data) > 0) {
                            foreach ($data as $key => $d) {
                                
                                $query = 'SELECT '.implode(',', $d['element_name']).' FROM '.$d['table'].' WHERE parent_id='.$parent_id;
                                $db->setQuery( $query );
                                $stored = $db->loadAssocList();
// var_dump($query); echo "<hr>";
                                   
                               if (count($stored) > 0) {
                                    $arrayValue = [];

                                    foreach($stored as $rowvalues){
                                        unset($rowvalues['id']);
                                        $rowvalues['parent_id'] = $id;
                                        $arrayValue[] = '('.implode(',', $db->quote($rowvalues)).')';
                                        $keyValue[] = $rowvalues;
                                       
                                    } 
                                     unset($stored[0]['id']);
                                         
                                   // update form data
                                   $query = 'INSERT INTO '.$d['table'].' (`'.implode('`,`', array_keys($stored[0])).'`)'.' VALUES '.implode(',', $arrayValue);
//var_dump($query); echo "<hr>";
                                    $db->setQuery( $query );
                                    $db->execute();
                                }
                            }
                        }
                    }

                }
            }

        }
        catch (Exception $e)
        {
            echo $e->getMessage();
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
            return false;
        }

        return true;
    }

     /**
     * Duplicate all documents (files)
     * @param $fnum_from      the fnum of the source
     * @param $fnum_to      the fnum of the duplicated application
     * @param $pid          the profile_id to get list of forms
     * @return bool
     */
    public function copyDocuments($fnum_from, $fnum_to, $pid = null, $duplicated = null)
    {
        $db = JFactory::getDbo();
        
        try
        {
            if (empty($pid)) {
                $profiles = new EmundusModelProfile();

                $fnumInfos = $profiles->getFnumDetails($fnum_from);
                $pid = (isset($fnumInfos['profile_id_form']) && !empty($fnumInfos['profile_id_form']))?$fnumInfos['profile_id_form']:$fnumInfos['profile_id'];
            }

                // 1. get list of uploaded documents for previous file defined as duplicated
            $query = 'SELECT eu.* 
                        FROM #__emundus_uploads as eu 
                        LEFT JOIN #__emundus_setup_attachment_profiles as esap on esap.attachment_id=eu.attachment_id AND esap.profile_id='.$pid.'
                        WHERE eu.fnum like '.$db->Quote($fnum_from);

            if (empty($pid)) 
                $query .= ' AND esap.duplicate=1';

            $db->setQuery( $query );
            $stored = $db->loadAssocList();
            
            if (count($stored) > 0) {
                // 2. copy DB définition and duplicate files in applicant directory
                foreach ($stored as $key => $row) {
                    $src = $row['filename'];
                    $ext = explode('.', $src);
                    $ext = $ext[count($ext)-1];;
                    $cpt = 0-(int)(strlen($ext)+1);
                    $dest = substr($row['filename'], 0, $cpt).'-'.$row['id'].'.'.$ext;
                    $row['filename'] = $dest;
                    unset($row['id']);
                    unset($row['fnum']);
                    try
                    {
                        $query = 'INSERT INTO #__emundus_uploads (`fnum`, `'.implode('`,`', array_keys($row)).'`) VALUES('.$db->Quote($fnum_to).', '.implode(',', $db->Quote($row)).')';
                        $db->setQuery( $query );
                        $db->execute();
                        $id = $db->insertid();
                        $path = EMUNDUS_PATH_ABS.$row['user_id'].DS;

                        if (!copy($path.$src, $path.$dest)) {
                            $query = 'UPDATE #__emundus_uploads SET filename='.$src.' WHERE id='.$id;
                            $db->setQuery( $query );
                            $db->execute();
                        }
                    }
                    catch(Exception $e)
                    {
                        $error = JUri::getInstance().' :: USER ID : '.$row['user_id'].' -> '.$e->getMessage();
                        JLog::add($error, JLog::ERROR, 'com_emundus');
                    }
                }
            }

        }
        catch (Exception $e)
        {
            echo $e->getMessage();
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
            return false;
        }

        return true;
    }

    /**
     * Duplicate all documents (files)
     * @param $fnum             String     the fnum of application file
     * @param $applicant        Object     the applicant user ID
     * @return bool
     */
    public function sendApplication($fnum, $applicant)
    {
        include_once(JPATH_BASE.'/components/com_emundus/models/emails.php');
        $db = JFactory::getDBO();
        try {
            // Vérification que le dossier à été entièrement complété par le candidat
            $query = 'SELECT id 
                        FROM #__emundus_declaration
                        WHERE fnum  like '.$db->Quote($fnum);
            $db->setQuery( $query );
            $db->execute();
            $id = $db->loadResult(); 

            $today = date('Y-m-d h:i:s');
        
            if ($id > 0) {
                $query = 'UPDATE #__emundus_declaration SET time_date='.$db->quote($today). ', user='.$applicant->id.' WHERE id='.$id;
            } else {
                $query = 'INSERT INTO #__emundus_declaration (time_date, user, fnum, type_mail) 
                                VALUE ('.$db->quote($today). ', '.$applicant->id.', '.$db->Quote($fnum).', "paid_validation")';
            }

            $db->setQuery( $query );
            $db->execute();

            // Insert data in #__emundus_campaign_candidature
            $query = 'UPDATE #__emundus_campaign_candidature SET submitted=1, date_submitted=NOW(), status=1 WHERE applicant_id='.$applicant->id.' AND campaign_id='.$applicant->campaign_id. ' AND fnum like '.$db->Quote($applicant->fnum);
            $db->setQuery($query);
            $db->execute();
            
            // Send emails defined in trigger
            $emails = new EmundusModelEmails;
            $step = 1;
            $code = array($applicant->code);
            $to_applicant = '0,1';
            $trigger_emails = $emails->sendEmailTrigger($step, $code, $to_applicant, $applicant);

        } catch (Exception $e) {
            // catch any database errors.
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
        }
        
        return true;
    }
}
