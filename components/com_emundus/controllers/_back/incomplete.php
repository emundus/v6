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
 * @package    Joomla.eMundus
 * @subpackage Components
 */
class EmundusControllerIncomplete extends JController {
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
			$default = 'incomplete';
			JRequest::setVar('view', $default );
		}
		$user = JFactory::getUser();
		$menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($user->id,$access)) {
			parent::display();
		}
    }
	
	////// EMAIL APPLICANT WITH CUSTOM MESSAGE///////////////////
	function applicantEmail() {
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'emails.php');
		EmundusHelperEmails::sendApplicantEmail();
	}

	function clear() {
		EmundusHelperFilters::clear();
	}
	/*
	function clear() {
		// Starting a session.
		$session = JFactory::getSession();
		$session->clear( 'profile' );
		$session->clear( 'quick_search' );
		$session->clear( 's_elements' );
		$session->clear( 's_elements_values' );
		//$session->clear( 'groups' );
		//$session->clear( 'finalgrade' );
		//$session->clear( 'evaluator' );
		
		$limitstart = JRequest::getVar('limitstart', null, 'POST', 'none',0);
		$filter_order = JRequest::getVar('filter_order', null, 'POST', null, 0);
		$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'POST', null, 0);
		$Itemid=JSite::getMenu()->getActive()->id;
		
		$this->setRedirect('index.php?option=com_emundus&view=incomplete&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$Itemid);
	}*/
	
	function getCampaign()
	{
		$db = JFactory::getDBO();
		$query = 'SELECT year as schoolyear FROM #__emundus_setup_campaigns WHERE published=1';
		$db->setQuery( $query );
		$syear = $db->loadRow();
		
		return $syear[0];
	}

/*	function unvalidate() {
		//$allowed = array("Super Users", "Administrator", "Editor");
		$user = JFactory::getUser();
		$menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($user->id,$access)) {
			die(JText::_("ACCES_DENIED"));
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
		$Itemid=JSite::getMenu()->getActive()->id;
		$this->setRedirect('index.php?option=com_emundus&view=incomplete&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$Itemid, JText::_('Application form unvalidated'), 'message');
	}
	
	function validate() {
		//$allowed = array("Super Users", "Administrator", "Editor");
		$user = JFactory::getUser();
		$menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($user->id,$access)) {
			die(JText::_("ACCES_DENIED"));
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
		$Itemid=JSite::getMenu()->getActive()->id;
		$this->setRedirect('index.php?option=com_emundus&view=incomplete&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$Itemid, JText::_('Application form validated'), 'message');
	}
	
	function administrative_check($reqids = null) {
		//$allowed = array("Super Users", "Administrator", "Editor");
		$user = JFactory::getUser();
		$menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($user->id,$access)) {
			die(JText::_("ACCES_DENIED"));
		}
		$db = JFactory::getDBO();
		$ids = JRequest::getVar('ud', null, 'POST', 'array', 0);
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
		$Itemid=JSite::getMenu()->getActive()->id;
		if (count($ids)>1)
			$this->setRedirect('index.php?option=com_emundus&view=incomplete&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid'.$Itemid, JText::_('ACTION_DONE').' : '.count($ids), 'message');
		else
			$this->setRedirect('index.php?option=com_emundus&view=incomplete&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$Itemid, JText::_('ACTION_DONE').' : '.count($ids), 'message');
	}
	
	*/
	function push_true(){
		$user = JFactory::getUser();
		//$allowed = array("Super Users", "Administrator", "Editor");
		$menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($user->id, $access) || !EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
			die(JText::_("ACCES_DENIED"));
		}
		$db = JFactory::getDBO();
		$uids = JRequest::getVar('ud', null, 'POST', 'array', 0);
		foreach ($uids as $uid){
			$params=explode('|',$uid);
			$users_id[] = $params[0];
		}
		$comment = JRequest::getVar('comments', null, 'POST');
		$itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);
		$limitstart = JRequest::getVar('limitstart', null, 'POST', 'none',0);
		
		$model = $this->getModel('profile');

		foreach ($users_id as $id) {
			if(!empty($comment)) {
				$query = 'INSERT INTO `#__emundus_comments` (applicant_id, user_id, reason, date, comment_body) 
						VALUES('.$id.','.$user->id.',"Consider application form as complete","'.date("Y.m.d H:i:s").'",'.$db->quote($comment).')';
				$db->setQuery( $query );
				$db->query();
			}
			if (!$model->isApplicationDeclared($id)) {		
				$query = 'INSERT INTO #__emundus_declaration (time_date, user) VALUES("'.date("Y.m.d H:i:s").'", '.$id.')';
				$db->setQuery( $query );
				$db->query() or die($db->getErrorMsg());
			}
			
			$campaign_id = $model->getCurrentIncompleteCampaignByApplicant($id);
			$query = 'UPDATE #__emundus_campaign_candidature SET submitted = 1, date_submitted = NOW() WHERE applicant_id = '.mysql_real_escape_string($id).' AND campaign_id='.$campaign_id; 
		//die(str_replace("#_", "jos", $query));
			$db->setQuery($query);
			$db->Query() or die($db->getErrorMsg());
		}
		$Itemid=JSite::getMenu()->getActive()->id;
		$this->setRedirect('index.php?option=com_emundus&view=incomplete&limitstart='.$limitstart.'&Itemid='.$Itemid, JText::_('ACTION_DONE').' : '.count($users_id), 'message');
	}
	
	/**
	 * export selected to xls
	 */
/*	function export_incompletes_xls() {
		//$allowed = array("Super Users", "Administrator", "Editor");
		$user = JFactory::getUser();
		$menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($user->id,$access)) {
			die(JText::_("ACCES_DENIED"));
		}
		//require_once('libraries/emundus/excel.php');
		
		require_once('libraries/emundus/test.php');
		
		$cid = JRequest::getVar('ud', null, 'POST', 'array', 0);
		$limitstart = JRequest::getVar('limitstart', null, 'POST', 'none',0);
		$filter_order = JRequest::getVar('filter_order', null, 'POST', null, 0);
		$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'POST', null, 0);
		JArrayHelper::toInteger( $cid, 0 );
		if (count( $cid ) == 0) 
			JError::raiseWarning( 500, JText::_( 'ERROR_NO_ITEMS_SELECTED' ) );		
		else 
			export_incompletes_xls($cid);
		exit;
	}
*/
	
	////// Export incomplete application form ///////////////////
	function export_incomplete_to_xls() {
		$user = JFactory::getUser();
		//$allowed = array("Super Users", "Administrator", "Editor");
		$menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($user->id,$access)) {
			die(JText::_("ACCES_DENIED"));
		}
		require_once('libraries/emundus/excel.php');
		
		$db	= JFactory::getDBO();
		$query = 'SELECT u.id FROM #__users AS u
			 	  LEFT JOIN #__emundus_users AS eu on eu.user_id=u.id
				  LEFT JOIN #__emundus_setup_profiles AS esp on eu.profile=esp.id
				  WHERE esp.published=1 
				  AND u.block = 0 
				  AND eu.schoolyear like "%'.$this->getCampaign().'%"
				  AND u.id NOT IN (
								   SELECT user 
								   FROM #__emundus_declaration) ';
		$no_filter = array("Super Users", "Administrator");
		if (!in_array($user->usertype, $no_filter)) {
			$model = $this->getModel('check');
			$query .= ' AND eu.user_id IN (select user_id from #__emundus_users_profiles where profile_id in ('.implode(',',$model->getProfileAcces($user->id)).')) ';
		}
		$query .= ' ORDER BY eu.lastname';//  LIMIT 724,1';
		$db->setQuery( $query );
		$cid = $db->loadResultArray();
		
		export_incompletes_xls($cid);
		$Itemid=JSite::getMenu()->getActive()->id;
		if (count($cid)>1)
			$this->setRedirect('index.php?option=com_emundus&view=incomplete&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$Itemid, JText::_('ACTION_DONE').count($ids), 'message');
		else
			$this->setRedirect('index.php?option=com_emundus&view=incomplete&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$Itemid);
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
		$Itemid=JSite::getMenu()->getActive()->id;
		if (count( $cid ) == 0) {
			JError::raiseWarning( 500, JText::_( 'ERROR_NO_ITEMS_SELECTED' ) );
			$this->setRedirect('index.php?option=com_emundus&view=incomplete&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$Itemid);
			exit;
		}
		zip_file($cid);
		exit;
	}
}
?>