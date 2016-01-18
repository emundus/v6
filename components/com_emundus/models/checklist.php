<?php
/**
 * CheckList model : displays applicant checklist (docs and forms).
 * 
 * @package    eMundus
 * @subpackage Components
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 * @author     Decision Publique - Jonas Lerebours
 */
 
// No direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.model' );
 
class EmundusModelChecklist extends JModelList
{
	var $_user = null;
	var $_db = null;
	var $_need = 0;

	function __construct()
	{
		parent::__construct();
		require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'menu.php');
		$this->_db = JFactory::getDBO();
		$student_id = JRequest::getVar('sid', null, 'GET', 'none',0);
		
		if ($student_id > 0 && JFactory::getUser()->usertype != 'Registered') {
			$this->_user = JFactory::getUser($student_id);
			$this->_db->setQuery('SELECT user.profile, user.university_id, user.schoolyear, profile.menutype 
				FROM #__emundus_users AS user 
				LEFT JOIN #__emundus_setup_profiles AS profile ON profile.id = user.profile
				WHERE user.user_id = '.$this->_user->id);
			$res = $this->_db->loadObject();
			$this->_user->profile = $res->profile;
		} else {
			$this->_user = JFactory::getUser();
		}
		//echo $this->_user->usertype;
	}

	function getGreeting(){
		$query = 'SELECT id, title, text FROM #__emundus_setup_checklist WHERE page = "checklist" ';
		$note = 0;//$this->getResult();
		if($note && is_numeric($note) && $note>1) $this->_need = $note;
		$query .= 'AND whenneed = '.$this->_need . ' OR whenneed='.$this->_user->status;
		$this->_db->setQuery( $query );
		return $this->_db->loadObject();
	}

	function getInstructions()
	{
		$query = 'SELECT id, title, text FROM #__emundus_setup_checklist WHERE page = "instructions"';
		//$query.= $this->_need?'1':'0';
		$this->_db->setQuery( $query );
		return $this->_db->loadObject();
	}

	function getFormsList()
	{
		$forms = @EmundusHelperMenu::buildMenuQuery($this->_user->profile);
		foreach ($forms as $form) {
			$query = 'SELECT count(*) FROM '.$form->db_table_name.' WHERE user = '.$this->_user->id.' AND fnum like '.$this->_db->Quote($this->_user->fnum);
			$this->_db->setQuery( $query );
			$form->nb = $this->_db->loadResult();
			if ($form->nb==0) $this->_need = 1;
		}
		return $forms;
	}

	function getAttachmentsList()
	{
		$query = 'SELECT attachments.id, COUNT(uploads.attachment_id) AS nb, attachments.nbmax, attachments.value, attachments.lbl, attachments.description, attachments.allowed_types, profiles.mandatory
					FROM #__emundus_setup_attachments AS attachments
						INNER JOIN #__emundus_setup_attachment_profiles AS profiles ON attachments.id = profiles.attachment_id
						LEFT JOIN #__emundus_uploads AS uploads ON uploads.attachment_id = profiles.attachment_id AND uploads.user_id = '.$this->_user->id.'  AND fnum like '.$this->_db->Quote($this->_user->fnum).'
					WHERE profiles.profile_id = '.$this->_user->profile.' AND profiles.displayed = 1 
					GROUP BY attachments.id
					ORDER BY profiles.mandatory DESC, attachments.ordering ASC';
		$this->_db->setQuery( $query );
	//die(str_replace('#_','jos',$query));	
		$attachments = $this->_db->loadObjectList(); /*or die($this->_db->getErrorMsg());*/
		foreach($attachments as $attachment) {
			if($attachment->nb>0) {
				$query = 'SELECT * FROM #__emundus_uploads WHERE user_id = '.$this->_user->id.' AND attachment_id = '.$attachment->id. ' AND fnum like '.$this->_db->Quote($this->_user->fnum);
				$this->_db->setQuery($query);
				$attachment->liste = $this->_db->loadObjectList(); // or die($this->_db->getErrorMsg());
			} elseif($attachment->mandatory==1) $this->_need = 1;
		}
		return $attachments;
	}

	function getNeed() {
		return $this->_need;
	}

	function getSent() {
		$query = 'SELECT submitted 
					FROM #__emundus_campaign_candidature 
					WHERE applicant_id = '.$this->_user->id.' AND fnum like '.$this->_db->Quote($this->_user->fnum);
		$this->_db->setQuery( $query );
		$res = $this->_db->loadResult(); 
		return $res>0;
	}

	function getResult() {
		$query = 'SELECT final_grade FROM #__emundus_final_grade WHERE student_id = '.$this->_user->id.' AND campaign_id='.$this->_user->campaign_id.'  AND fnum like '.$this->_db->Quote($this->_user->fnum);
		$this->_db->setQuery( $query );
		return $this->_db->loadResult();
	}
	
	function getApplicant(){
		$query = 'SELECT profile FROM #__emundus_users WHERE user_id = '.$this->_user->id;
		$this->_db->setQuery( $query );
		if($this->_db->loadResult() == 8) return false;
		return true;
	}

	function getIsOtherActiveCampaign() {
		$query='SELECT count(id) as cpt 
				FROM #__emundus_setup_campaigns 
				WHERE id NOT IN (
								select campaign_id FROM #__emundus_campaign_candidature WHERE applicant_id='.$this->_user->id.'
								)';
		$this->_db->setQuery($query); 
		$cpt = $this->_db->loadResult();
		return $cpt>0?true:false;
	}

	function getConfirmUrl()
    {
        $db = JFactory::getDBO();
        $query = 'SELECT CONCAT(m.link,"&Itemid=", m.id) as link
        FROM #__emundus_setup_profiles as esp 
        LEFT JOIN  #__menu as m on m.menutype = esp.menutype
        WHERE esp.id='.$this->_user->profile.' AND m.published>=0 AND m.level=1 ORDER BY m.lft DESC'; 
        $db->setQuery($query); 
        return $db->loadResult();
    }
}
?>