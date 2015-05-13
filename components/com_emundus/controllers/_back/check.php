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
class EmundusControllerCheck extends JController {
	var $_user = null;
	var $_db = null;
	
	function __construct($config = array()){
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'javascript.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'list.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'emails.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'export.php');
		
		$this->_user = JFactory::getUser();
		$this->_db = JFactory::getDBO();
		
		parent::__construct($config);
	}
	function display() {
		// Set a default view if none exists
		if ( ! JRequest::getCmd( 'view' ) ) {
			$default = 'check';
			JRequest::setVar('view', $default );
		}
		parent::display();
    }
    
////// EMAIL APPLICANT WITH CUSTOM MESSAGE///////////////////
	function applicantEmail() {
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'emails.php');
		EmundusHelperEmails::sendApplicantEmail();
	}

	function clear() {
		EmundusHelperFilters::clear();
	}
	
	function getCampaign()
	{
		$db = JFactory::getDBO();
		$query = 'SELECT year as schoolyear FROM #__emundus_setup_campaigns WHERE published=1';
		$db->setQuery( $query );
		$syear = $db->loadRow();
		
		return $syear[0];
	}

	function unvalidate() {
		if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
			die(JText::_('ACCESS_DENIED'));
		}
		$uid = JRequest::getVar('uid', null, 'GET', null, 0);
		$limitstart = JRequest::getVar('limitstart', null, 'GET', null, 0);
		$filter_order = JRequest::getVar('filter_order', null, 'POST', null, 0);
		$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'POST', null, 0);
		$elements_items = JRequest::getVar('elements', null, 'POST', 'array', 0);
		$elements_values = JRequest::getVar('elements_values', null, 'POST', 'array', 0);
	 	// Starting a session.
		$session = JFactory::getSession();
		$session->set('s_elements', $elements_items);
		$session->set('s_elements_values', $elements_values);
		
		//die(print_r($session->get('s_search')));
		if(!empty($uid) && is_numeric($uid)) {
			$db = JFactory::getDBO();
			$db->setQuery('UPDATE #__emundus_declaration SET validated = 0 WHERE user = '.mysql_real_escape_string($uid));
			$db->Query();
		}
		$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.JRequest::getCmd( 'Itemid' ), JText::_('Application form unvalidated'), 'message');
	}
	
	function validate() {
		if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
			die(JText::_('ACCESS_DENIED'));
		}
		$uid = JRequest::getVar('uid', null, 'GET', null, 0);
		$limitstart = JRequest::getVar('limitstart', null, 'POST', null, 0);
		$filter_order = JRequest::getVar('filter_order', null, 'POST', null, 0);
		$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'POST', null, 0);
		$elements_items = JRequest::getVar('elements', null, 'POST', 'array', 0);
		$elements_values = JRequest::getVar('elements_values', null, 'POST', 'array', 0);
	 	// Starting a session.
		$session = JFactory::getSession();
		$session->set('s_elements', $elements_items);
		$session->set('s_elements_values', $elements_values);
		
		if(!empty($uid) && is_numeric($uid)) {
			$db = JFactory::getDBO();
			$db->setQuery('UPDATE #__emundus_declaration SET validated = 1 WHERE user = '.mysql_real_escape_string($uid));
			$db->Query();
		}
		$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.JRequest::getCmd( 'Itemid' ), JText::_('Application form validated'), 'message');
	}
	
	function administrative_check($reqids = null) { 
		if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
			die(JText::_('ACCESS_DENIED'));
		}
		$db = JFactory::getDBO();
		$uids = JRequest::getVar('ud', array(), 'POST', 'array');
		foreach ($uids as $uid){
			$params=explode('|',$uid);
			$users_id[] = intval($params[0]);
		}
		
		$validation_list = JRequest::getVar('validation_list', null, 'POST', 'none',0);
		$limitstart = JRequest::getVar('limitstart', null, 'POST', 'none',0);
		$filter_order = JRequest::getVar('filter_order', null, 'POST', null, 0);
		$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'POST', null, 0);
		$elements_items = JRequest::getVar('elements', null, 'POST', 'array', 0);
		$elements_values = JRequest::getVar('elements_values', null, 'POST', 'array', 0);
	 	// Starting a session.
		$session = JFactory::getSession();
		$session->set('s_elements', $elements_items);
		$session->set('s_elements_values', $elements_values);
		
		if(empty($users_id) && !empty($reqids)) {
			$users_id = $reqids;
		}
		
		if(!empty($users_id)) {
			foreach ($users_id as $id) {
				$db->setQuery('UPDATE #__emundus_declaration SET validated = '.$validation_list.' WHERE user = '.mysql_real_escape_string($id));
				$db->Query() or die($db->getErrorMsg());
			}
		}
		if (count($users_id)>1)
			$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.JRequest::getCmd( 'Itemid' ), JText::_('ACTION_DONE').' : '.count($users_id), 'message');
		else
			$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.JRequest::getCmd( 'Itemid' ), JText::_('ACTION_DONE').' : '.count($users_id), 'message');
	}

	
	function push_false() {
		if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
			die(JText::_("ACCES_DENIED"));
		}
		$db = JFactory::getDBO();
		$uids = JRequest::getVar('ud', null, 'POST', 'array', 0);
		foreach ($uids as $uid){
			$params=explode('|',$uid);
			$users_id[] = $params[0];
		}
		$comment = JRequest::getVar('comments', null, 'POST');
		$limitstart = JRequest::getVar('limitstart', null, 'POST', 'none',0);
		$itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);
		
		$model = $this->getModel('profile');
		
		foreach ($users_id as $id) {
			if(!empty($comment)) {
				$query = 'INSERT INTO `#__emundus_comments` (applicant_id, user_id, reason, date, comment_body) 
						VALUES('.$id.','.$this->_user->id.',"Consider application form as incomplete","'.date("Y.m.d H:i:s").'","'.$comment.'")';
				$db->setQuery( $query );
				$db->query();
			}
			//$query = 'DELETE FROM #__emundus_declaration WHERE user='.$id;
			$query ='UPDATE #__emundus_declaration SET time_date = "0000-00-00 00:00:00" WHERE user = '.$id;
			$db->setQuery( $query );
			$db->query();
			$campaign_id = $model->getCurrentCompleteCampaignByApplicant($id);
			$db->setQuery('UPDATE #__emundus_campaign_candidature SET submitted = 0, date_submitted = NULL WHERE applicant_id = '.mysql_real_escape_string($id).' AND campaign_id='.$campaign_id);
			$db->Query() or die($db->getErrorMsg());
			
			unlink(JPATH_BASE.DS.'images'.DS.'emundus'.DS.'files'.DS.$id.DS."application.pdf");
		}
		$this->setRedirect('index.php?option=com_emundus&view=check&limitstart='.$limitstart.'&Itemid='.$itemid, JText::_('ACTION_DONE').' : '.count($users_id), 'message');
	}
	
	
	/**
	 * export selected to xls
	 */
	function export_complete() {
		if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
			die(JText::_('ACCESS_DENIED'));
		}
		require_once('libraries/emundus/excel.php');
		$cid = JRequest::getVar('ud', null, 'POST', 'array', 0);
		$limitstart = JRequest::getVar('limitstart', null, 'POST', 'none',0);
		$filter_order = JRequest::getVar('filter_order', null, 'POST', null, 0);
		$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'POST', null, 0);
		JArrayHelper::toInteger( $cid, 0 );
		if (count( $cid ) == 0) {
			JError::raiseWarning( 500, JText::_( 'ERROR_NO_ITEMS_SELECTED' ) );
			$this->setRedirect('index.php?option=com_emundus&view=check&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.JRequest::getCmd( 'Itemid' ));
			exit;
		}
		export_complete($cid);
		$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.JRequest::getCmd( 'Itemid' ), JText::_('ACTION_DONE'), 'message');
	}
	
	////// Export complete application form ///////////////////
	function export_complete_to_xls ($reqids = null) {
		if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
			die(JText::_('ACCESS_DENIED'));
		}
		$mainframe = JFactory::getApplication();
		require_once('libraries/emundus/xls.php');
		$db	= JFactory::getDBO();
		
		$query = 'SELECT ed.user 
			 	  FROM #__emundus_declaration AS ed
				  LEFT JOIN #__emundus_users AS eu ON eu.user_id=ed.user  
				  WHERE schoolyear like "%'.$this->getCampaign().'%"'; //Applicants
				  
		$no_filter = array("Super Users", "Administrator");
		if (!in_array($user->usertype, $no_filter)) {
			$model = $this->getModel('check');
			$query .= ' AND ed.user IN (select user_id from #__emundus_users_profiles where profile_id in ('.implode(',',$model->getProfileAcces($this->_user->id)).')) ';
		}
		$db->setQuery( $query );
		$cid = $db->loadResultArray();

		export_complete($cid);
		
		if (count($cid)>1)
			$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.JRequest::getCmd( 'Itemid' ), JText::_('ACTION_DONE').count($ids), 'message');
		else
			$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.JRequest::getCmd( 'Itemid' ));
	}
	

	function export_zip() {
		if (!EmundusHelperAccess::asEvaluatorAccessLevel($this->_user->id)) {
			die(JText::_('ACCESS_DENIED'));
		}

		$cid = JRequest::getVar('ud', null, 'POST', 'array', 0);
		JArrayHelper::toInteger( $cid, 0 );

		EmundusHelperExport::export_zip($cid);

		exit;
	}
	
}
?>