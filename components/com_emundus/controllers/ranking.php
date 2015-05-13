<?php
/**
 * @package    eMundus
 * @subpackage Components
 *             components/com_emundus/emundus.php
 * @link       http://www.decisionpublique.fr
 * @license    GNU/GPL
 * @author     Benjamin Rivalland
*/
 
// No direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport('joomla.application.component.controller');
require_once (JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');
require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
 
/**
 * eMundus Component Controller
 *
 * @package    Joomla.Tutorials
 * @subpackage Components
 */
class EmundusControllerRanking extends JControllerLegacy {

	function display($cachable = false, $urlparams = false) {
		// Set a default view if none exists
		if ( ! JRequest::getCmd( 'view' ) ) {
			$default = 'ranking';
			JRequest::setVar('view', $default );
		}
		$limitstart = JRequest::getCmd( 'limitstart' );
		$filter_order = JRequest::getCmd( 'filter_order' );
		$filter_order_Dir = JRequest::getCmd( 'filter_order_Dir' );
		$user = JFactory::getUser();
		if ($user->usertype == "Registered") {
			$checklist = $this->getView( 'checklist', 'html' );
			$checklist->setModel( $this->getModel( 'checklist'), true );
			$checklist->display();
		} else {
			parent::display();
		}
    }
	
	function clear() {
		EmundusHelperFilters::clear();
		
		//$itemid = JRequest::getVar('Itemid', null, 'POST', 'none',0);
		$limitstart = JRequest::getVar('limitstart', null, 'POST', 'none',0);
		$filter_order = JRequest::getVar('filter_order', null, 'POST', null, 0);
		$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'POST', null, 0);
		$Itemid=JSite::getMenu()->getActive()->id;
		$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$Itemid);
	}
	
	////// EXPORT SELECTED XLS ///////////////////
	function export_selected_xls(){
	     $cids = JRequest::getVar('ud', null, 'POST', 'array', 0);
		 $page= JRequest::getVar('limitstart',0,'get');
		 if(!empty($cids)){
		 	$this->export_to_xls($cids);
		}else {
			$this->setRedirect("index.php?option=com_emundus&view=ranking_auto&limitstart=".$page,JText::_("NO_ITEM_SELECTED"),'error');
		}
	}
	
   ////// EXPORT ALL XLS ///////////////////	
	function export_to_xls($reqids=array(),$el=array()) {
		//$allowed = array("Super Users", "Administrator", "Editor");
		$user = JFactory::getUser();
		$menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($user->id,$access)) {
			die("You are not allowed to access to this page.");
		}
		require_once('libraries/emundus/xls_ranking_auto.php');
		export_applicants($reqids,$el);
	}
	
	function export_zip() {
		//$allowed = array("Super Users", "Administrator", "Editor");
		$user = JFactory::getUser();
		$menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($user->id,$access)) {
			die("You are not allowed to access to this page.");
		}
		require_once('libraries/emundus/zip.php');
		$db	= JFactory::getDBO();
		$cid = JRequest::getVar('ud', null, 'POST', 'array', 0);
		$limitstart = JRequest::getVar('limitstart', null, 'POST', 'none',0);
		$filter_order = JRequest::getVar('filter_order', null, 'POST', null, 0);
		$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'POST', null, 0);
		JArrayHelper::toInteger( $cid, 0 );

		if (count( $cid ) == 0) {
			JError::raiseWarning( 500, JText::_( 'ERROR_NO_ITEMS_SELECTED' ) );
			$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.JRequest::getCmd( 'Itemid' ));
			exit;
		}
		zip_file($cid);
		exit;
	}
	
	
	////// EMAIL APPLICANT WITH CUSTOM MESSAGE///////////////////
	function custom_email() {
		$current_user = JFactory::getUser();
		if(!EmundusHelperAccess::isAdministrator($user->id) && !EmundusHelperAccess::isCoordinator($user->id)) {
			$this->setRedirect('index.php', JText::_('Only Coordinator can access this function.'), 'error');
			return;
		}
		$mainframe = JFactory::getApplication();
		
		$db = JFactory::getDBO();
		$subject = JRequest::getVar('mail_subject', null, 'POST', 'none',0);
		$ids = JRequest::getVar('ud', null, 'POST', 'array', 0);
		$message = JRequest::getVar('mail_body', null, 'POST', 'none',0);
		$limitstart = JRequest::getVar('limitstart', null, 'POST', 'none',0);
		$filter_order = JRequest::getVar('filter_order', null, 'POST', null, 0);
		$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'POST', null, 0);
		
		
		// List of evaluators
		if (count($ids) == 0) {
			JError::raiseWarning( 500, JText::_('ERROR') );
			$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.JRequest::getCmd( 'Itemid' ));
			return;
		}

		// setup mail
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
			$from = $admin->email;
			$from_id = $admin->id;
			$fromname = $admin->name;
		}
		
		// Evaluations criterias
		$query = 'SELECT id, label, sub_labels
		FROM #__fabrik_elements
		WHERE group_id=41 AND (plugin like "fabrikradiobutton" OR plugin like "fabrikdropdown")';
		$db->setQuery( $query );
		$db->query();
		$eval_criteria=$db->loadObjectList();
		
		$eval = '<ul>';
		foreach($eval_criteria as $e) {
			$eval .= '<li>'.$e->label.' ('.$e->sub_labels.')</li>';
		}
		$eval .= '</ul>';
		
		// Selection outcome
		$query = 'SELECT sub_values, sub_labels FROM #__fabrik_elements WHERE name like "final_grade" LIMIT 1';
		$db->setQuery( $query );
		$result = $db->loadRowList();
		$sub_values = explode('|', $result[0][0]);
		foreach($sub_values as $sv)
			$p_grade[]="/".$sv."/";
		$grade = explode('|', $result[0][1]);
		
		// template replacements
		$patterns = array ('/\[ID\]/', '/\[NAME\]/', '/\[EMAIL\]/', '/\[FINAL_GRADE\]/', '/\[SITE_URL\]/', '/\[EVAL_CRITERIAS\]/', '/\[EVAL_PERIOD\]/', '/\n/');

		JArrayHelper::toInteger( $ids, null );
		if(!empty($ids)) {
				foreach ($ids as $id) {	
				$user = JFactory::getUser($id);
				
				// Get Final Grade
				$query = 'SELECT final_grade 
				FROM #__emundus_final_grade 
				WHERE student_id='.$id;
				$db->setQuery( $query );
				$db->query();
				$final_grade_id=$db->loadResult();
				$final_grade = preg_replace($p_grade, $grade, $final_grade_id);
				
				$query = 'SELECT esp.evaluation_start, esp.evaluation_end 
						FROM #__emundus_setup_profiles AS esp 
						WHERE esp.id=6'; // Evaluator profile
				$db->setQuery( $query );
				$db->query();
				$period=$db->loadRow();
				$period_str = strftime(JText::_('DATE_FORMAT_LC2'), strtotime($period[0])).' '.JText::_('TO').' '.strftime(JText::_('DATE_FORMAT_LC2'), strtotime($period[1]));
					
				$replacements = array ($user->id, $user->name, $user->email, $final_grade, JURI::base(), $eval, $period_str, '<br />');
				// template replacements
				$body = preg_replace($patterns, $replacements, $message);
		
				// mail function
				JUtility::sendMail($from, $fromname, $user->email, $subject, $body, 1);
	
				$sql = "INSERT INTO `#__messages` (`user_id_from`, `user_id_to`, `subject`, `message`, `date_time`) 
					VALUES ('".$from_id."', '".$user->id."', '".$subject."', '".$body."', NOW())";
				$db->setQuery( $sql );
				$db->query();
				
				unset($replacements);
			}
		}
			
		$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.JRequest::getCmd( 'Itemid' ), JText::_('ACTION_DONE'), 'message');
	}
	
	////// AFFECT ASSESSOR ///////////////////
	function setAssessor($reqids = null) {
		//$allowed = array("Super Users", "Administrator", "Editor");
		$user = JFactory::getUser();
		$menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($user->id,$access)) {
			die("You are not allowed to access to this page.");
		}
		$db = JFactory::getDBO();
		$ids = JRequest::getVar('ud', null, 'POST', 'array', 0);
		$ag_id = JRequest::getVar('assessor_group', null, 'POST', 'none',0);
		$au_id = JRequest::getVar('assessor_user', null, 'POST', 'none',0);
		$limitstart = JRequest::getVar('limitstart', null, 'POST', 'none',0);
		$filter_order = JRequest::getVar('filter_order', null, 'POST', null, 0);
		$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'POST', null, 0);
		
		if(empty($ids) && !empty($reqids)) {
			$ids = $reqids;
		}
		JArrayHelper::toInteger( $ids, null );
		if(!empty($ids)) {
			foreach ($ids as $id) {				
				if(!empty($ag_id) && isset($ag_id)) {
					$db->setQuery('SELECT * FROM #__emundus_groups_eval WHERE applicant_id='.$id.' AND group_id='.$ag_id);
					$cpt = $db->loadResultArray();
					
					//** Delete members of group to add **/
					$query = 'DELETE FROM #__emundus_groups_eval WHERE applicant_id='.$id.' AND user_id IN (select user_id from #__emundus_groups where group_id='.$ag_id.')';
					$db->setQuery($query);
					$db->Query() or die($db->getErrorMsg());
					
					if (count($cpt)==0)
						$db->setQuery('INSERT INTO #__emundus_groups_eval (applicant_id, group_id, user_id) VALUES ('.$id.','.$ag_id.',null)');
					
				}
				elseif(!empty($au_id) && isset($au_id)) {
					$db->setQuery('SELECT * FROM #__emundus_groups_eval WHERE applicant_id='.$id.' AND user_id='.$au_id);
					$cpt = $db->loadResultArray();
					
					$db->setQuery('SELECT * FROM #__emundus_groups_eval WHERE applicant_id='.$id.' AND group_id IN (select group_id from #__emundus_groups where user_id='.$au_id.')');
					$cpt_grp = $db->loadResultArray();
					
					if (count($cpt)==0 && count($cpt_grp)==0)
						$db->setQuery('INSERT INTO #__emundus_groups_eval (applicant_id, group_id, user_id) VALUES ('.$id.',null,'.$au_id.')');
				}
				else {
					$db->setQuery('DELETE FROM #__emundus_groups_eval WHERE applicant_id='.$id);
				}
				$db->Query() or die($db->getErrorMsg());
			}
		}
		if (count($ids)>1)
			$this->setRedirect('index.php?option=com_emundus&view=ranking_auto&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir, JText::_('MESSAGE_APPLICANTS_AFFECTED').count($ids), 'message');
		elseif (count($ids)==1)
			$this->setRedirect('index.php?option=com_emundus&view=ranking_auto&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir, JText::_('MESSAGE_APPLICANT_AFFECTED').count($ids), 'message');
		else
			$this->setRedirect('index.php?option=com_emundus&view=ranking_auto&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir);
	}
	
	function delassessor() {
		$user = JFactory::getUser();
		//$allowed = array("Super Users", "Administrator", "Editor");
		if(!EmundusHelperAccess::isAdministrator($user->id) && !EmundusHelperAccess::isCoordinator($user->id)) {
			$this->setRedirect('index.php', JText::_('You are not allowed to access to this page.'), 'error');
			return;
		}
		$uid = JRequest::getVar('uid', null, 'GET', null, 0);
		$aid = JRequest::getVar('aid', null, 'GET', null, 0);
		$pid = JRequest::getVar('pid', null, 'GET', null, 0);
		$limitstart = JRequest::getVar('limitstart', null, 'GET', null, 0);
		$filter_order = JRequest::getVar('filter_order', null, 'GET', null, 0);
		$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'GET', null, 0);
		
		if(!empty($aid) && is_numeric($aid)) {
			$db = JFactory::getDBO();
			$query = 'DELETE FROM #__emundus_groups_eval WHERE applicant_id='.mysql_real_escape_string($aid);
			if(!empty($pid) && is_numeric($pid))
				$query .= ' AND group_id='.mysql_real_escape_string($pid);
			if(!empty($uid) && is_numeric($uid))
				$query .= ' AND user_id='.mysql_real_escape_string($uid);
			$db->setQuery($query);
			$db->Query();
		}
		$this->setRedirect('index.php?option=com_emundus&view=ranking_auto&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir, JText::_('ACTION_DONE'), 'message');
	}
	
	////// UNAFFECT ASSESSOR ///////////////////
	function unsetAssessor($reqids = null) {
		$user = JFactory::getUser();
		if(!EmundusHelperAccess::isAdministrator($user->id) && !EmundusHelperAccess::isCoordinator($user->id)) {
			$this->setRedirect('index.php', JText::_('Only Coordinator can access this function.'), 'error');
			return;
		}
		$db = JFactory::getDBO();
		$ids = JRequest::getVar('ud', null, 'POST', 'array', 0);
		$ag_id = JRequest::getVar('assessor_group', null, 'POST', 'none',0);
		$au_id = JRequest::getVar('assessor_user', null, 'POST', 'none',0);
		$limitstart = JRequest::getVar('limitstart', null, 'POST', 'none',0);
		$filter_order = JRequest::getVar('filter_order', null, 'POST', null, 0);
		$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'POST', null, 0);
		
		if(empty($ids) && !empty($reqids)) {
			$ids = $reqids;
		}
		JArrayHelper::toInteger( $ids, null );
		if(!empty($ids)) {
			foreach ($ids as $id) {				
				if(!empty($ag_id) && isset($ag_id)) {
					$query = 'DELETE FROM #__emundus_groups_eval WHERE applicant_id='.$id.' AND group_id='.$ag_id;
					$db->setQuery($query);
					$db->Query() or die($db->getErrorMsg());
				}
				elseif(!empty($au_id) && isset($au_id)) {
					$query = 'DELETE FROM #__emundus_groups_eval WHERE applicant_id='.$id.' AND user_id='.$au_id;
					$db->setQuery($query);
					$db->Query() or die($db->getErrorMsg());
				}
			}
		}
		if (count($ids)>1)
			$this->setRedirect('index.php?option=com_emundus&view=ranking_auto&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir, JText::_('MESSAGE_APPLICANTS_UNAFFECTED').count($ids), 'message');
		elseif (count($ids)==1)
			$this->setRedirect('index.php?option=com_emundus&view=ranking_auto&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir, JText::_('MESSAGE_APPLICANT_UNAFFECTED').count($ids), 'message');
		else
			$this->setRedirect('index.php?option=com_emundus&view=ranking_auto&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir);
	}
	
	function delete_eval() {
		$user = JFactory::getUser();
		$menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($user->id,$access)) {
			die(JText::_("ACCESS_DENIED"));
		}
		
		$sid = JRequest::getVar('sid', null, 'GET', null, 0); 
		$sids = explode('-',$sid);
		$db	= JFactory::getDBO();
		
		if (EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
			$query = 'UPDATE #__emundus_users SET profile = (SELECT result_for FROM #__emundus_final_grade WHERE student_id='.$sids[0].' AND campaign_id='.$sids[1].' AND user='.$user->id.' ) WHERE user_id='.$sids[0];
			$db->setQuery($query);
			if($db->query()){
				$query = 'DELETE FROM #__emundus_final_grade WHERE student_id='.$sids[0].' AND campaign_id='.$sids[1];
				$db->setQuery($query);
				$db->query();
			}
		}

		$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.JRequest::getCmd( 'Itemid' ));
	}
		
	////// EMAIL ASSESSORS WITH DEFAULT MESSAGE///////////////////
	function defaultEmail($reqids = null) {
		$current_user = JFactory::getUser();
		if(!EmundusHelperAccess::isAdministrator($user->id) && !EmundusHelperAccess::isCoordinator($user->id)) {
			$this->setRedirect('index.php', JText::_('Only Coordinator can access this function.'), 'error');
			return;
		}
		$mainframe = JFactory::getApplication();
		$db = JFactory::getDBO();
		$limitstart = JRequest::getVar('limitstart', null, 'POST', 'none',0);
		$filter_order = JRequest::getVar('filter_order', null, 'POST', null, 0);
		$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'POST', null, 0);
		
		// List of evaluators
		$query = 'SELECT eg.user_id 
					FROM `#__emundus_groups` as eg 
					LEFT JOIN `#__emundus_groups_eval` as ege on ege.group_id=eg.group_id 
					WHERE eg.user_id is not null 
					GROUP BY eg.user_id';
		$db->setQuery( $query );
		$users_1 = $db->loadResultArray();
		
		$query = 'SELECT ege.user_id 
					FROM `#__emundus_groups_eval` as ege 
					WHERE ege.user_id is not null 
					GROUP BY ege.user_id';
		$db->setQuery( $query );
		$users_2 = $db->loadResultArray();
		
		$query = 'SELECT e.email
					FROM #__emundus_users eu
					JOIN #__users e ON e.id = eu.user_id
					WHERE eu.profile IN (2,4,5)';
		$db->setQuery( $query );
		$copy = $db->loadResultArray();
		foreach($copy as $c){
			$cc[] = $c;
		}
		
		$users = array_unique(array_merge($users_1, $users_2));
		
		// Récupération des données du mail
		$query = 'SELECT id, subject, emailfrom, name, message
						FROM #__emundus_setup_emails
						WHERE lbl="assessors_set"';
		$db->setQuery( $query );
		$db->query();
		$obj=$db->loadObjectList();
		
		// setup mail
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
			$from = $admin->email;
			$from_id = $admin->id;
			$fromname = $admin->name;
		}
		
		// Evaluations criterias
		$query = 'SELECT id, label, sub_labels
						FROM #__fabrik_elements
						WHERE group_id=41 AND (plugin like "fabrikradiobutton" OR plugin like "fabrikdropdown")';
		$db->setQuery( $query );
		$db->query();
		$eval_criteria=$db->loadObjectList();
		
		$eval = '<ul>';
		foreach($eval_criteria as $e) {
			$eval .= '<li>'.$e->label.' ('.$e->sub_labels.')</li>';
		}
		$eval .= '</ul>';

		// template replacements
		$patterns = array ('/\[ID\]/', '/\[NAME\]/', '/\[EMAIL\]/', '/\[APPLICANTS_LIST\]/', '/\[SITE_URL\]/', '/\[EVAL_CRITERIAS\]/', '/\[EVAL_PERIOD\]/', '/\n/');
		$error=0;
		foreach ($users as $uid) {
			$user = JFactory::getUser($uid);
			
			$query = 'SELECT applicant_id
						FROM #__emundus_groups_eval
						WHERE user_id='.$user->id.' OR group_id IN (select group_id from #__emundus_groups where user_id='.$user->id.')';
			$db->setQuery( $query );
			$db->query();
			$applicants=$db->loadResultArray();
			$list = '<ul>';
			foreach($applicants as $ap) {
				$app = JFactory::getUser($ap);
				$list .= '<li>'.$app->name.' ['.$app->id.']</li>';
			}
			$list .= '</ul>';
			
			$query = 'SELECT esp.evaluation_start, esp.evaluation_end 
					FROM #__emundus_setup_profiles AS esp 
					LEFT JOIN #__emundus_users AS eu ON eu.profile=esp.id  
					WHERE user_id='.$user->id;
			$db->setQuery( $query );
			$db->query();
			$period=$db->loadRow();
			
			$period_str = strftime(JText::_('DATE_FORMAT_LC2'), strtotime($period[0])).' '.JText::_('TO').' '.strftime(JText::_('DATE_FORMAT_LC2'), strtotime($period[1]));
			
			$replacements = array ($user->id, $user->name, $user->email, $list, JURI::base(), $eval, $period_str, '<br />');
			
			// template replacements
			$body = preg_replace($patterns, $replacements, $obj[0]->message);
			unset($replacements);
			unset($list);
			
			// mail function
			if (JUtility::sendMail($from, $obj[0]->name, $user->email, $obj[0]->subject, $body, 1, $cc)) {
				$sql = "INSERT INTO `#__messages` (`user_id_from`, `user_id_to`, `subject`, `message`, `date_time`) 
					VALUES ('".$from_id."', '".$user->id."', '".$obj[0]->subject."', '".$body."', NOW())";
				$db->setQuery( $sql );
				$db->query();
			} else {
				$error++;
			}
		}
		if ($error>0)	
			$this->setRedirect('index.php?option=com_emundus&view=ranking_auto&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir, JText::_('ACTION_ABORDED'), 'error');
		else 
			$this->setRedirect('index.php?option=com_emundus&view=ranking_auto&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir, JText::_('ACTION_DONE'), 'message');
	}
	
	////// EMAIL ASSESSORS WITH CUSTOM MESSAGE///////////////////
	function customEmail() {
		//$allowed = array("Super Users", "Administrator", "Editor");
		$user = JFactory::getUser();
		$menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($user->id,$access)) {
			die("You are not allowed to access to this page.");
		}
		$mainframe = JFactory::getApplication();
		
		$db = JFactory::getDBO();
		$ag_id = JRequest::getVar('mail_group', null, 'POST', 'none',0);
		$ae_id = JRequest::getVar('mail_user', null, 'POST', 'none',0);
		$subject = JRequest::getVar('mail_subject', null, 'POST', 'none',0);
		$message = JRequest::getVar('mail_body', null, 'POST', 'none',0);
		$limitstart = JRequest::getVar('limitstart', null, 'POST', 'none',0);
		$filter_order = JRequest::getVar('filter_order', null, 'POST', null, 0);
		$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'POST', null, 0);
		
		if ($subject == '') {
			JError::raiseWarning( 500, JText::_( 'ERROR_YOU_MUST_PROVIDE_SUBJECT' ) );
			$this->setRedirect('index.php?option=com_emundus&view=ranking_auto&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir);
			return;
		}
		if ($message == '') {
			JError::raiseWarning( 500, JText::_( 'ERROR_YOU_MUST_PROVIDE_A_MESSAGE' ) );
			$this->setRedirect('index.php?option=com_emundus&view=ranking_auto&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir);
			return;
		}
		
		// List of evaluators
		if (isset($ag_id) && $ag_id > 0) {
			$query = 'SELECT eg.user_id 
						FROM `#__emundus_groups` as eg 
						WHERE eg.group_id='.$ag_id;
			$db->setQuery( $query );
			$users = $db->loadResultArray();
		} elseif (isset($ae_id) && $ae_id > 0)
			$users[] = $ae_id;
		else {
			JError::raiseWarning( 500, JText::_('ERROR') );
			$this->setRedirect('index.php?option=com_emundus&view=ranking_auto&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir);
			return;
		}
		
		/*$query = 'SELECT user_id
					FROM #__emundus_users
					WHERE profile = 5 OR profile = 2 OR profile = 4';
		$db->setQuery( $query );
		$users_2 = $db->loadResultArray();
		
		$users = array_unique(array_merge($users_1, $users_2));*/
		
		// setup mail
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
			$from = $admin->email;
			$from_id = $admin->id;
			$fromname = $admin->name;
		}

		// Evaluations criterias
		$query = 'SELECT id, label, sub_labels
		FROM #__fabrik_elements
		WHERE group_id=41 AND (plugin like "fabrikradiobutton" OR plugin like "fabrikdropdown")';
		$db->setQuery( $query );
		$db->query();
		$eval_criteria=$db->loadObjectList();
		
		$eval = '<ul>';
		foreach($eval_criteria as $e) {
			$eval .= '<li>'.$e->label.' ('.$e->sub_labels.')</li>';
		}
		$eval .= '</ul>';

		// template replacements
		$patterns = array ('/\[ID\]/', '/\[NAME\]/', '/\[EMAIL\]/', '/\[APPLICANTS_LIST\]/', '/\[SITE_URL\]/', '/\[EVAL_CRITERIAS\]/', '/\[EVAL_PERIOD\]/', '/\n/');

		//send to selected people
		foreach ($users as $uid) {
			$user = JFactory::getUser($uid);
			
			$query = 'SELECT applicant_id
					  FROM #__emundus_groups_eval
					  WHERE user_id='.$user->id.' OR group_id IN (select group_id from #__emundus_groups where user_id='.$user->id.')';
			$db->setQuery( $query );
			$db->query();
			$applicants=$db->loadResultArray();
			$list = '<ul>';
			
			foreach($applicants as $ap) {
				$app = JFactory::getUser($ap);
				$list .= '<li>'.$app->name.' ['.$app->id.']</li>';
			}
			$list .= '</ul>';
			
			$query = 'SELECT esp.evaluation_start, esp.evaluation_end 
						FROM #__emundus_setup_profiles AS esp 
						LEFT JOIN #__emundus_users AS eu ON eu.profile=esp.id  
						WHERE user_id='.$user->id;
			$db->setQuery( $query );
			$db->query();
			$period=$db->loadRow();
				
			$period_str = strftime(JText::_('DATE_FORMAT_LC2'), strtotime($period[0])).' '.JText::_('TO').' '.strftime(JText::_('DATE_FORMAT_LC2'), strtotime($period[1]));
				
			$replacements = array ($user->id, $user->name, $user->email, $list, JURI::base(), $eval, $period_str, '<br />');
			// template replacements
			$body = preg_replace($patterns, $replacements, $message);
	
			// mail function
			if(JUtility::sendMail($from, $fromname, $user->email, $subject, $body, 1)){
				$sql = "INSERT INTO `#__messages` (`user_id_from`, `user_id_to`, `subject`, `message`, `date_time`) 
					VALUES ('".$from_id."', '".$user->id."', '".$subject."', '".$body."', NOW())";
				$db->setQuery( $sql );
				$db->query();
			}
			unset($replacements);
		}			
		$this->setRedirect('index.php?option=com_emundus&view=ranking_auto&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir, JText::_('ACTION_DONE'), 'message');
	}
	
	////// EMAIL APPLICANT WITH CUSTOM MESSAGE///////////////////
	function applicantEmail() {
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'emails.php');
		EmundusHelperEmails::sendApplicantEmail();
	}
	
} //END CLASS
?>