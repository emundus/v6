<?php
/**
 * @package    eMundus
 * @subpackage Components
 *             components/com_emundus/emailalert.php
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 * @author     Mathilde Guillossou
*/

class EmundusModelEmailalert extends JModelList{

	function __construct(){
		parent::__construct();

		$this->_db = JFactory::getDBO();
		$this->_eMConfig = JComponentHelper::getParams('com_emundus');
	}

	function getDate($date_id){
		$query = 'SELECT ft.db_table_name as table_name, fe.name, fe.plugin, ft.created_by_alias
				FROM #__fabrik_lists ft
				LEFT JOIN #__fabrik_formgroup ff ON ff.form_id = ft.form_id
				LEFT JOIN #__fabrik_elements fe ON fe.group_id = ff.group_id
				WHERE fe.id = '.$date_id;
		$this->_db->setQuery( $query );
		$date = $this->_db->loadObject();

		//date id is a real date and if is a part of application form
		if(!empty($date) && $date->plugin == 'fabrikdate' && $date->created_by_alias == 'form')
			$query = 'SELECT '.$date->name.' FROM '.$date->table_name;
		else
			exit();
		return $query;
	}

	function getCurrentCampaign(){
		$query = 'SELECT DISTINCT year as schoolyear
				FROM #__emundus_setup_campaigns
				WHERE published=1
				ORDER BY schoolyear';
		$this->_db->setQuery( $query );
		return $this->_db->loadResultArray();
	}

	function getMailtosend(){
		//reminder end of candidature email
		$p_remind = $this->_eMConfig->get('particular_remind', '14,7,1');
		$remind_mail = $this->_eMConfig->get('reminder_mail_id');

		//specific reminder email
		if(isset($reminder_date) && !empty($reminder_date)) {
			$reminder_date = $this->_eMConfig->get('reminder_element_date_id');
			$end_candidature = $this->getDate($reminder_date);
			$remind_days = $this->_eMConfig->get('remind_days');
			$reminder_attachment = $this->_eMConfig->get('reminder_attanchment_id');
			$remind_report_mail = $this->_eMConfig->get('report_remind_mail_id');
			$reminder_profile = $this->_eMConfig->get('reminder_profile_id');
		}

		//liste d'envoi -- Rappel periodique ou rappel plus frequent avant fin de candidature
		// @TODO Prendre en compte la table #__emundus_campaign_candidature !!
		$query = 'SELECT u.id, u.name, u.email, ee.date_time, ee.email_id, ee.periode, esp.candidature_end, ed.validated, ese.subject, ed.time_date
					FROM #__users u
					LEFT JOIN #__emundus_declaration as ed ON ed.user=u.id
					LEFT JOIN #__emundus_setup_campaigns as esc ON esc.profile_id=eu.profile AND published=1
					JOIN #__emundus_emailalert ee ON ee.user_id = u.id
					LEFT JOIN #__emundus_setup_emails ese ON ese.id = ee.email_id
					JOIN #__emundus_users eu ON u.id = eu.user_id
					JOIN #__emundus_setup_profiles esp ON esp.id = eu.profile
					WHERE  ed.validated IS NULL AND u.usertype ="Registered" AND eu.schoolyear IN ("'.implode('","',$this->getCurrentCampaign()).'")
					AND (
						 (ed.time_date IS NULL AND (DATEDIFF( esp.candidature_end , NOW() > 0) AND (DATEDIFF(NOW() , ee.date_time ) >= ee.periode) AND (ed.validated is null OR ed.validated!=1) AND ee.email_id = '.$remind_mail.')
						 OR ((DATEDIFF( esp.candidature_end , NOW() IN ('.$p_remind.')) AND (ed.validated is null OR ed.validated!=1) AND ee.email_id = '.$remind_mail.')';
		//if necessary report remind
		if(!empty($remind_days)){
			$query .= ' OR u.id IN (
				SELECT u.id
				FROM jos_users u
				INNER JOIN jos_emundus_declaration as ed ON ed.user = u.id
				INNER JOIN jos_emundus_emailalert ee ON ee.user_id = u.id
				INNER JOIN jos_emundus_final_grade efg ON efg.student_id = u.id
				WHERE efg.final_grade = 4
				AND DATEDIFF(NOW(),('.$end_candidature.' WHERE user = u.id)) IN ('.$remind_days.')
				AND u.id NOT IN(
								SELECT user_id
								FROM #__emundus_uploads
								WHERE attachment_id = '.$reminder_attachment.')
				AND ee.email_id = '.$remind_report_mail.')';
			$query .= ')';
			$query .= ' AND eu.profile IN ('.$reminder_profile.')';
		} else {
			$query .= ')';
		}
		$this->_db->setQuery($query);
		//die(str_replace('#_','jos',$query));
		return $this->_db->loadObjectList();
	}

	function getSetupemail($mail_id){
		$query = '	SELECT subject, emailfrom, message, name, id
					FROM #__emundus_setup_emails
					WHERE id = '.$mail_id;
		$this->_db->setQuery( $query );
		return $this->_db->loadRow();
	}

	function getInsert() {

		$conf = JFactory::getConfig();
		$cfromname = $conf->getValue('config.fromname');
		$date = date('Y-m-d G:i:s');

		require_once(JPATH_COMPONENT.DS.'models'.DS.'check.php');
		$model = new EmundusModelCheck;

		$insert = $this->getMailtosend();

		foreach ($insert as $i) {

			$mail = $this->getSetupemail($i->email_id);
			$subject = $mail[0];
			$mail_id = $mail[4];
			$body = $mail[2];
			$student = JFactory::getUser($i->id);
			$profile = $model->getProfileByID($student->id);
			$patterns = array ('/\[NAME]/','/\[SIGNATURE]/','/\[USERNAME]/','/\[SITE_URL]/','/\[DEADLINE]/');
			$replacements = array ($student->name,$cfromname,$student->username,JURI::base(), strftime("%A %d %B %Y %H:%M", strtotime($profile[0]->candidature_end) ).' (GMT)');
			$body = preg_replace($patterns,$replacements,$body);

			$query = 'INSERT INTO #__messages (user_id_from,user_id_to,date_time,state,subject,message)
						VALUES (62,'.$student->id.',"'.$date.'",1,"'.$subject.'",'.$this->_db->quote($body).')';
			$this->_db->setQuery($query);

			if ($this->_db->query() == 1) {
				$query = 'UPDATE #__emundus_emailalert SET date_time = NOW() WHERE user_id ='.$student->id.' AND email_id ='.$mail_id;
				$this->_db->setQuery($query);
				$this->_db->Query();
			}
		}
	}

	function getSend(){
		$remind_mail = $this->_eMConfig->get('reminder_mail_id');
		$remind_report_mail = $this->_eMConfig->get('report_remind_mail_id');

		$query = '	SELECT m.subject, m.message, u.email, m.user_id_to, ese.id as email_id
					FROM #__messages m
					JOIN #__users u ON u.id = m.user_id_to
					JOIN #__emundus_setup_emails ese ON ese.subject = m.subject
					WHERE m.subject IN (
									 SELECT subject
									 FROM #__emundus_setup_emails
									 WHERE (id = '.$remind_report_mail.' OR id = '.$remind_mail.'))
					AND m.state =1';
		$this->_db->setQuery( $query );
		return $this->_db->loadObjectList();
	}

	function getKey(){
		$key = JRequest::getVar('keyid', null, 'GET');
		$p_key = $this->_eMConfig->get('keyid', '100');

		if($key == $p_key) return true;
		return false;
	}
}
?>