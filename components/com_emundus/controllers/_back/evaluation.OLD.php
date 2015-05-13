<?php
/**
 * @package    Joomla
 * @subpackage Emundus
 *             components/com_emundus/emundus.php
 * @link       http://www.decisionpublique.fr
 * @license    GNU/GPL
 * @author     Benjamin Rivalland
*/
 
// No direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.application.component.controller');
JHTML::addIncludePath(JPATH_COMPONENT.DS.'helpers');

/**
 * eMundus Component Controller
 *
 * @package    Joomla.eMundus
 * @subpackage Components
 */
class EmundusControllerEvaluation extends JController {
	var $_user = null;
	var $_db = null;
	
	function __construct($config = array()){
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'javascript.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'list.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'emails.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'export.php');
		
		$this->_user = JFactory::getUser();
		$this->_db = JFactory::getDBO();
		
		parent::__construct($config);
	}
	
	function display() {
		// Set a default view if none exists
		if ( ! JRequest::getCmd( 'view' ) ) {
			$default = 'evaluation';
			JRequest::setVar('view', $default );
		}
		$limitstart = JRequest::getCmd( 'limitstart' );
		$filter_order = JRequest::getCmd( 'filter_order' );
		$filter_order_Dir = JRequest::getCmd( 'filter_order_Dir' );

		if (EmundusHelperAccess::asEvaluatorAccessLevel($this->_user->id))
			parent::display();
		else 
			echo JText::_('ACCESS_DENIED');
    }
	
	function clear() {
		EmundusHelperFilters::clear();
		
		//$itemid = JRequest::getVar('Itemid', null, 'POST', 'none',0);
		$itemid=JSite::getMenu()->getActive()->id;
		$limitstart = JRequest::getVar('limitstart', null, 'POST', 'none',0);
		$filter_order = JRequest::getVar('filter_order', null, 'POST', null, 0);
		$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'POST', null, 0);
		
		$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$itemid);
	}
	
	function export_zip() {
		if (!EmundusHelperAccess::asEvaluatorAccessLevel($this->_user->id)) {
			die(JText::_('ACCESS_DENIED'));
		}

		$cid = JRequest::getVar('ud', null, 'POST', 'array', 0);

		EmundusHelperExport::export_zip($cid);

		exit;
	}
	
	////// AFFECT ASSESSOR ///////////////////
	function setAssessor($reqids = null) { 
		//$allowed = array("Super Users", "Administrator", "Editor");
		$user = JFactory::getUser();
		$menu=JSite::getMenu()->getActive();
		//$itemid=JSite::getMenu()->getActive()->id;
		$access=!empty($menu)?$menu->access : 0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($user->id,$access)) {
			die("ACCESS_DENIED");
		}
		$db = JFactory::getDBO();
		$ids = JRequest::getVar('ud', null, 'POST', 'array', 0);
		$ag_id = JRequest::getVar('assessor_group', null, 'POST', 'none',0);
		$au_id = JRequest::getVar('assessor_user', null, 'POST', 'none',0);
		$limitstart = JRequest::getVar('limitstart', null, 'POST', 'none',0);
		$filter_order = JRequest::getVar('filter_order', null, 'POST', null, 0);
		$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'POST', null, 0);
		$itemid = JRequest::getVar('itemid', null, 'POST', null, 0);
		if(empty($ids) && !empty($reqids)) {
			$ids = $reqids;
		}

		if((empty($ag_id) || !isset($ag_id)) && (empty($au_id) || !isset($au_id))) { die(JText::_("PLEASE_SELECT_LESS_APPLICANT_AT_ONCE")); }

		if(!empty($ids)) {
			foreach ($ids as $id) {
				$params=explode('|',$id);
				$applicant_id = $params[0];
				$campaign_id = $params[1];
				$applicant_id=intval($applicant_id);
				$campaign_id=intval($campaign_id);
				if(!empty($ag_id) && isset($ag_id)) {
					$db->setQuery('SELECT * FROM #__emundus_groups_eval WHERE applicant_id='.$applicant_id.' AND campaign_id='.$campaign_id.' AND group_id='.$ag_id);
					$cpt = $db->loadResultArray();
					
					//** Delete members of group to add **/
					$query = 'DELETE FROM #__emundus_groups_eval WHERE applicant_id='.$applicant_id.' AND campaign_id='.$campaign_id.' AND user_id IN (select user_id from #__emundus_groups where group_id='.$ag_id.')';
					$db->setQuery($query);
					$db->Query() or die($db->getErrorMsg());
					
					if (count($cpt)==0)
						$db->setQuery('INSERT INTO #__emundus_groups_eval (applicant_id, group_id, user_id,campaign_id) VALUES ('.$applicant_id.','.$ag_id.',null,'.$campaign_id.')');
					
				}
				elseif(!empty($au_id) && isset($au_id)) {
					$db->setQuery('SELECT * FROM #__emundus_groups_eval WHERE applicant_id='.$applicant_id.' AND campaign_id='.$campaign_id.' AND user_id='.$au_id);
					$cpt = $db->loadResultArray();
					
					$db->setQuery('SELECT * FROM #__emundus_groups_eval WHERE applicant_id='.$applicant_id.' AND campaign_id='.$campaign_id.' AND group_id IN (select group_id from #__emundus_groups where user_id='.$au_id.')');
					$cpt_grp = $db->loadResultArray();
					
					if (count($cpt)==0 && count($cpt_grp)==0)
						$db->setQuery('INSERT INTO #__emundus_groups_eval (applicant_id, group_id, user_id,campaign_id) VALUES ('.$applicant_id.',null,'.$au_id.','.$campaign_id.')');
				}
				else {
					echo JText::_('APPLICANT')." : ".$id." ".JText::_('NOT_AFFECTED')."<br>"; $k++;
					//$db->setQuery('DELETE FROM #__emundus_groups_eval WHERE applicant_id='.$applicant_id.' AND campaign_id='.$campaign_id);
				}
				$db->Query() or die($db->getErrorMsg());
			}
		}
		if (count($ids)>1)
			$this->setRedirect('index.php?option=com_emundus&view=evaluation&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$itemid, JText::_('MESSAGE_APPLICANTS_AFFECTED').count($ids), 'message');
		elseif (count($ids)==1)
			$this->setRedirect('index.php?option=com_emundus&view=evaluation&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$itemid, JText::_('MESSAGE_APPLICANT_AFFECTED').count($ids), 'message');
		else
			$this->setRedirect('index.php?option=com_emundus&view=evaluation&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$itemid);
	}
	/**/
	function delassessor() {
		$user = JFactory::getUser();
		//$allowed = array("Super Users", "Administrator", "Editor");
		$menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($user->id,$access)) {
			die("ACCESS_DENIED");
		}
		$uid = JRequest::getVar('uid', null, 'GET', null, 0);
		$cid = JRequest::getVar('cid', null, 'GET', null, 0);
		$aid = JRequest::getVar('aid', null, 'GET', null, 0);
		$pid = JRequest::getVar('pid', null, 'GET', null, 0);
		$limitstart = JRequest::getVar('limitstart', null, 'GET', null, 0);
		$filter_order = JRequest::getVar('filter_order', null, 'GET', null, 0);
		$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'GET', null, 0);
		$itemid = JRequest::getVar('Itemid', null, 'GET', null, 0);

		if(!empty($aid) && is_numeric($aid) && !empty($cid) && is_numeric($cid)) {
			$db = JFactory::getDBO();
			$query = 'DELETE FROM #__emundus_groups_eval WHERE campaign_id ='.mysql_real_escape_string($cid).' AND applicant_id='.mysql_real_escape_string($aid);
			if(!empty($pid) && is_numeric($pid))
				$query .= ' AND group_id='.mysql_real_escape_string($pid);
			if(!empty($uid) && is_numeric($uid))
				$query .= ' AND user_id='.mysql_real_escape_string($uid);
			$db->setQuery($query);
			$db->Query();
		}
		$this->setRedirect('index.php?option=com_emundus&view=evaluation&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$itemid, JText::_('ACTION_DONE'), 'message');
	}
	
	////// UNAFFECT ASSESSOR ///////////////////
	function unsetAssessor($reqids = null) {
		$user = JFactory::getUser();
		//$allowed = array("Super Users", "Administrator", "Editor");
		$menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($user->id,$access)) {
			die("ACCESS_DENIED");
		}
		$db = JFactory::getDBO();
		$ids = JRequest::getVar('ud', null, 'POST', 'array', 0);
		$ag_id = JRequest::getVar('assessor_group', null, 'POST', 'none',0);
		$au_id = JRequest::getVar('assessor_user', null, 'POST', 'none',0);
		$limitstart = JRequest::getVar('limitstart', null, 'POST', 'none',0);
		$filter_order = JRequest::getVar('filter_order', null, 'POST', null, 0);
		$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'POST', null, 0);$itemid = JRequest::getVar('itemid', null, 'POST', null, 0);
		
		if(empty($ids) && !empty($reqids)) {
			$ids = $reqids;
		}
		
		if(!empty($ids)) {
			foreach ($ids as $id) {
				$params=explode('|',$id);
				$applicant_id = $params[0];
				$campaign_id = $params[1];
				$applicant_id=intval($applicant_id);
				$campaign_id=intval($campaign_id);
				if(!empty($ag_id) && isset($ag_id)) {
					$query = 'DELETE FROM #__emundus_groups_eval WHERE applicant_id='.$applicant_id.' AND campaign_id='.$campaign_id.' AND group_id='.$ag_id;
					$db->setQuery($query);
					$db->Query() or die($db->getErrorMsg());
				}
				elseif(!empty($au_id) && isset($au_id)) {
					$query = 'DELETE FROM #__emundus_groups_eval WHERE applicant_id='.$applicant_id.' AND campaign_id='.$campaign_id.' AND user_id='.$au_id;
					$db->setQuery($query);
					$db->Query() or die($db->getErrorMsg());
				}
			}
		}
		if (count($ids)>1)
			$this->setRedirect('index.php?option=com_emundus&view=evaluation&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$itemid, JText::_('MESSAGE_APPLICANTS_UNAFFECTED').count($ids), 'message');
		elseif (count($ids)==1)
			$this->setRedirect('index.php?option=com_emundus&view=evaluation&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$itemid, JText::_('MESSAGE_APPLICANT_UNAFFECTED').count($ids), 'message');
		else
			$this->setRedirect('index.php?option=com_emundus&view=evaluation&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$itemid);
	}
	
	function delete_eval(){
		$user = JFactory::getUser();
		$menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($user->id, $access)) {
			die("ACCESS_DENIED");
		}
		$limitstart = JRequest::getVar('limitstart', null, 'GET', 'none',0);
		$filter_order = JRequest::getVar('filter_order', null, 'GET', null, 0);
		$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'GET', null, 0);
		$view = JRequest::getVar('view', null, 'GET', null, 0);
		$itemid = JRequest::getVar('Itemid', null, 'GET', 'INT', 0);
		$sid = JRequest::getVar('sid', null, 'GET', null, 0);
		$sids = explode('-',$sid); // student_id-ID

		$db = JFactory::getDBO();
		
		$model = $this->getModel($view);
		
		if( EmundusHelperAccess::asEvaluatorAccessLevel($user->id) ) {
			if( $model->is_evaluator_of_this($sids[1]) > 0 || EmundusHelperAccess::isCoordinator($user->id) ){
				$query = 'DELETE FROM #__emundus_evaluations WHERE student_id='.$sids[0].' AND id='.$sids[1];
				$db->setQuery($query);
				$db->query();
			}else{
				$this->setRedirect('index.php?option=com_emundus&view='.$view.'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$itemid.'#cb'.$sids[0], JText::_('MESSAGE_IS_NOT_YOUR_EVALUATION'), 'message');
			}
			$this->setRedirect('index.php?option=com_emundus&view='.$view.'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$itemid.'#cb'.$sids[0]);
		}
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
	
} //END CLASS
?>