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
class EmundusControllerRailwayyard extends JController {

	function display() {
		// Set a default view if none exists
		if ( ! JRequest::getCmd( 'view' ) ) {
			$default = 'railwayyard';
			JRequest::setVar('view', $default );
		}
		
		$user = JFactory::getUser();
		if ($user->usertype == "Registered") {
			$checklist = $this->getView( 'checklist', 'html' );
			$checklist->setModel( $this->getModel( 'checklist'), true );
			$checklist->display();
		} else {
			parent::display();
		}
    }

	function getCampaign()
	{
		$db = JFactory::getDBO();
		$query = 'SELECT year as schoolyear FROM #__emundus_setup_campaigns WHERE published=1';
		$db->setQuery( $query );
		$syear = $db->loadRow();
		
		return $syear[0];
	}
	
	function set_profile(){
		$user = JFactory::getUser();
		if(!EmundusHelperAccess::isAdministrator($user->id) && !EmundusHelperAccess::isCoordinator($user->id)) {
			$this->setRedirect('index.php', JText::_('Only Coordinator can access this function.'), 'error');
			return;
		}
		$db = JFactory::getDBO();
		
		$sid = JRequest::getVar('sid', null, 'GET', 'int',0);
		$pid = JRequest::getVar('pid', null, 'GET', 'int',0);
		$to_set = JRequest::getVar('set', null, 'GET', 'int',0);
		$limitstart = JRequest::getVar('limitstart', null, 'POST', 'none',0);
		$filter_order = JRequest::getVar('filter_order', null, 'POST', null, 0);
		$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'POST', null, 0);
		
		JArrayHelper::toInteger( $ids, null );
		if($to_set==1)
			$query = 'INSERT INTO #__emundus_users_profiles (user_id, profile_id) VALUES ('.$sid.', '.$pid.')';
		else
			$query = 'DELETE FROM #__emundus_users_profiles WHERE user_id='.$sid.' AND profile_id='.$pid;

		$db->setQuery($query);
		$db->Query() or die($db->getErrorMsg());
		echo JText::_('SAVED');		
	}
	

	////// Export complete application form with evaluation ///////////////////
	function export_to_xls($reqids = null) {
		$user = JFactory::getUser();
		if(!EmundusHelperAccess::isAdministrator($user->id) && !EmundusHelperAccess::isCoordinator($user->id)) {
			$this->setRedirect('index.php', JText::_('Only Coordinator can access this function.'), 'error');
			return;
		}
		$mainframe = JFactory::getApplication();
		require_once('libraries/emundus/excel.php');

		$db	= JFactory::getDBO();
		$query = 'SELECT distinct(ee.student_id) 
			 	  FROM #__emundus_evaluations AS ee
				  INNER JOIN #__emundus_users AS eu ON eu.user_id=ee.student_id 
				  WHERE eu.schoolyear like "%'.$this->getCampaign().'%"';
		$db->setQuery( $query );
		$cid = $db->loadResultArray();

		export_xls($cid);

		if (count($ids)>1)
			$this->setRedirect('index.php?option=com_emundus&view=eval&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir, JText::_('ACTION_DONE').count($ids), 'message');
		else
			$this->setRedirect('index.php?option=com_emundus&view=eval&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir);
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
		$Itemid=JSite::getMenu()->getActive()->id;
		if (count($ids)>1)
			$this->setRedirect('index.php?option=com_emundus&view=groups&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$Itemid, JText::_('MESSAGE_APPLICANTS_UNAFFECTED').count($ids), 'message');
		elseif (count($ids)==1)
			$this->setRedirect('index.php?option=com_emundus&view=groups&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&itemid='.$Itemid, JText::_('MESSAGE_APPLICANT_UNAFFECTED').count($ids), 'message');
		else
			$this->setRedirect('index.php?option=com_emundus&view=groups&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$Itemid);
	}
	
} //END CLASS
?>