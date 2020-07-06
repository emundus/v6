<?php
/**
 * @package    Joomla
 * @subpackage Emundus
 * @link       http://www.emundus.fr
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
class EmundusControllerEmail extends JControllerLegacy {
	var $_user = null;
	var $_db = null;
	
	function __construct($config = array()){
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'export.php');
		
		$this->_user = JFactory::getSession()->get('emundusUser');
		$this->_db = JFactory::getDBO();
		
		parent::__construct($config);
	}
	
	function display($cachable = false, $urlparams = false) {
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
		
		$itemid=JFactory::getApplication()->getMenu()->getActive()->id;
		$limitstart = JRequest::getVar('limitstart', null, 'POST', 'none',0);
		$filter_order = JRequest::getVar('filter_order', null, 'POST', null, 0);
		$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'POST', null, 0);
		
		$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$itemid);
	}
	
	
	////// EMAIL ASSESSORS WITH DEFAULT MESSAGE///////////////////
	function defaultEmail($reqids = null) {
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'emails.php');
		@EmundusHelperEmails::sendDefaultEmail();
	}

	////// EMAIL ASSESSORS WITH CUSTOM MESSAGE///////////////////
	function customEmail() {
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'emails.php');
		@EmundusHelperEmails::sendCustomEmail();
	}
	
	////// EMAIL APPLICANT WITH CUSTOM MESSAGE///////////////////
	function applicantEmail() {
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'emails.php');
		@EmundusHelperEmails::sendApplicantEmail();
	}
	
	function getTemplate(){
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'emails.php');
		@EmundusHelperEmails::getTemplate();
	}

	function sendmail_expert() {

		$user = JFactory::getUser();

		if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id) && !EmundusHelperAccess::asAccessAction(18, 'c', $user->id)) {
			echo json_encode(['status' => false, 'sent' => null, 'failed' => true, 'message' => JText::_( 'ACCESS_DENIED')]);
	        die(JText::_( 'ACCESS_DENIED'));
        }

		$jinput = JFactory::getApplication()->input;
        $fnums = $jinput->post->getString('fnums');

		require_once (JPATH_COMPONENT.DS.'models'.DS.'emails.php');
        $m_emails = new EmundusModelEmails();
        $email = $m_emails->sendExpertMail($fnums);

        echo json_encode(['status' => true, 'sent' => $email['sent'], 'failed' => $email['failed'], 'message' => $email['message']]);
        exit;
    }
	
}