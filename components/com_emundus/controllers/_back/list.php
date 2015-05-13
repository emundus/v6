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
 
/**
 * eMundus Component Controller
 *
 * @package    Joomla.Tutorials
 * @subpackage Components
 */
class EmundusControllerList extends JController {
	var $_user = null;
	var $_db = null;
	
	function __construct($config = array()){
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'javascript.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'list.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'emails.php');
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'export.php');
		
		$this->_user = JFactory::getUser();
		$this->_db = JFactory::getDBO();
		
		parent::__construct($config);
	}
	
	function display() {
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

	function push_true() {
		$user = JFactory::getUser();
		//$allowed = array("Super Users", "Administrator", "Editor");
		$menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($user->id,$access)) {
			die("You are not allowed to access to this page.");
		}
		$db = JFactory::getDBO();
		$ids = JRequest::getVar('ud', null, 'POST', 'array', 0);
		$comment = JRequest::getVar('comments', null, 'POST');
		$itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);
		$limitstart = JRequest::getVar('limitstart', null, 'POST', 'none',0);
		
		foreach ($ids as $id) {
			if(!empty($comment)) {
				$query = 'INSERT INTO `#__emundus_comments` (applicant_id, user_id, reason,date, comment_body) 
						VALUES('.$id.','.$user->id.',"Consider application form as complete","'.date("Y.m.d H:i:s").'","'.$comment.'")';
				$db->setQuery( $query );
				$db->query();
			}
			$query = 'INSERT INTO #__emundus_declaration (time_date, user) VALUES("'.date("Y.m.d H:i:s").'", '.$id.')';
			$db->setQuery( $query );
			$db->query();
		}
		$this->setRedirect('index.php?option=com_emundus&view=list&limitstart='.$limitstart.'&Itemid='.$itemid, JText::_('ACTION_DONE').' : '.count($ids), 'message');
	}

	function push_false() {
		$user = JFactory::getUser();
		//$allowed = array("Super Users", "Administrator", "Editor");
		$menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($user->id,$access)) {
			die("You are not allowed to access to this page.");
		}
		$db = JFactory::getDBO();
		$ids = JRequest::getVar('ud', null, 'POST', 'array', 0);
		$comment = JRequest::getVar('comments', null, 'POST');
		$limitstart = JRequest::getVar('limitstart', null, 'POST', 'none',0);
		$itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);
		
		foreach ($ids as $id) {
			if(!empty($comment)) {
				$query = 'INSERT INTO `#__emundus_comments` (applicant_id, user_id, reason, date, comment_body) 
						VALUES('.$id.','.$user->id.',"Consider application form as incomplete","'.date("Y.m.d H:i:s").'","'.$comment.'")';
				$db->setQuery( $query );
				$db->query();
			}
			$query = 'DELETE FROM #__emundus_declaration WHERE user='.$id;
			$db->setQuery( $query );
			$db->query();
			
			unlink(JPATH_BASE.DS.'images'.DS.'emundus'.DS.'files'.DS.$id.DS."application.pdf");
		}
		$this->setRedirect('index.php?option=com_emundus&view=list&limitstart='.$limitstart.'&Itemid='.$itemid, JText::_('ACTION_DONE').' : '.count($ids), 'message');
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
		$itemid = JRequest::getVar('Itemid', null, 'GET', null, 0);
		
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
			$this->setRedirect('index.php?option=com_emundus&view=list&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$itemid, JText::_('MESSAGE_APPLICANTS_AFFECTED').count($ids), 'message');
		elseif (count($ids)==1)
			$this->setRedirect('index.php?option=com_emundus&view=list&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$itemid, JText::_('MESSAGE_APPLICANT_AFFECTED').count($ids), 'message');
		else
			$this->setRedirect('index.php?option=com_emundus&view=list&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$itemid);
	}
	/**/
	function delassessor() {
		$user = JFactory::getUser();
		//$allowed = array("Super Users", "Administrator", "Editor");
		$menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($user->id,$access)) {
			die("You are not allowed to access to this page.");
		}
		$uid = JRequest::getVar('uid', null, 'GET', null, 0);
		$aid = JRequest::getVar('aid', null, 'GET', null, 0);
		$pid = JRequest::getVar('pid', null, 'GET', null, 0);
		$limitstart = JRequest::getVar('limitstart', null, 'GET', null, 0);
		$filter_order = JRequest::getVar('filter_order', null, 'GET', null, 0);
		$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'GET', null, 0);
		$itemid = JRequest::getVar('Itemid', null, 'GET', null, 0);
		
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
		$this->setRedirect('index.php?option=com_emundus&view=list&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$itemid, JText::_('ACTION_DONE'), 'message');
	}
	
	////// UNAFFECT ASSESSOR ///////////////////
	function unsetAssessor($reqids = null) {
		$user = JFactory::getUser();
		//$allowed = array("Super Users", "Administrator", "Editor");
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
		$itemid = JRequest::getVar('Itemid', null, 'GET', null, 0);
		
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
			$this->setRedirect('index.php?option=com_emundus&view=list&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$itemid, JText::_('MESSAGE_APPLICANTS_UNAFFECTED').count($ids), 'message');
		elseif (count($ids)==1)
			$this->setRedirect('index.php?option=com_emundus&view=list&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$itemid, JText::_('MESSAGE_APPLICANT_UNAFFECTED').count($ids), 'message');
		else
			$this->setRedirect('index.php?option=com_emundus&view=list&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$itemid);
	}

	function administrative_check($reqids = null) {
		//$allowed = array("Super Users", "Administrator", "Editor");
		$user = JFactory::getUser();
		$menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($user->id,$access)) {
			die("You are not allowed to access to this page.");
		}
		$db = JFactory::getDBO();
		$ids = JRequest::getVar('ud', null, 'POST', 'array', 0);
		$validation_list = JRequest::getVar('validation_list', null, 'POST', 'none',0);
		$limitstart = JRequest::getVar('limitstart', null, 'POST', 'none',0);
		$filter_order = JRequest::getVar('filter_order', null, 'POST', null, 0);
		$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'POST', null, 0);

		if(empty($ids) && !empty($reqids)) {
			$ids = $reqids;
		}
		JArrayHelper::toInteger( $ids, null );
		if(!empty($ids)) {
			foreach ($ids as $id) {
				$db->setQuery('UPDATE #__emundus_declaration SET validated = '.$validation_list.' WHERE user = '.mysql_real_escape_string($id));
				$db->Query() or die($db->getErrorMsg());
			}
		}
		if (count($ids)>1)
			$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.JRequest::getCmd( 'Itemid' ), JText::_('ACTION_DONE').' : '.count($ids), 'message');
		else
			$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.JRequest::getCmd( 'Itemid' ), JText::_('ACTION_DONE').' : '.count($ids), 'message');
	}

	function export_zip() {
		$menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		$user = JFactory::getUser();
		if (!EmundusHelperAccess::isAllowedAccessLevel($user->id, $access)) {
			die(JText::_('ACCESS_DENIED'));
		}

		$cid = JRequest::getVar('ud', null, 'POST', 'array', 0);
		JArrayHelper::toInteger( $cid, 0 );

		EmundusHelperExport::export_zip($cid);

		exit;
	}
	
		
	////// EMAIL ASSESSORS WITH DEFAULT MESSAGE///////////////////
	function defaultEmail($reqids = null) {
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'emails.php');
		EmundusHelperEmails::sendDefaultEmail();
	}

	////// EMAIL ASSESSORS WITH CUSTOM MESSAGE///////////////////
	function customEmail() {
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'emails.php');
		EmundusHelperEmails::sendCustomEmail();
	}
	
	////// EMAIL APPLICANT WITH CUSTOM MESSAGE///////////////////
	function applicantEmail() {
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'emails.php');
		EmundusHelperEmails::sendApplicantEmail();
	}

	function clear() {
		EmundusHelperFilters::clear();
	}
	
} //END CLASS
?>