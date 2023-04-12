<?php
/**
 * @package    Joomla
 * @subpackage emundus
 *             components/com_emundus/emundus.php
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 * @author     Hugo Moracchini
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * HTML View class for the Emundus Component
 *
 * @package Emundus
 */

class EmundusViewMessage extends JViewLegacy {


	public function __construct($config = array()) {

		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
		require_once (JPATH_COMPONENT.DS.'models'.DS.'messages.php');
		require_once (JPATH_COMPONENT.DS.'models'.DS.'files.php');
		require_once (JPATH_COMPONENT.DS.'models'.DS.'application.php');

		parent::__construct($config);

	}

    public function display($tpl = null) {

		$current_user = JFactory::getUser();

    	if (!EmundusHelperAccess::asPartnerAccessLevel($current_user->id)) {
		    die (JText::_('COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS'));
	    }

		// List of fnum is sent via GET in JSON format.
	    $jinput = JFactory::getApplication()->input;
	    $layout = $jinput->getString('layout', null);

	    $document = JFactory::getDocument();
	    $document->addStyleSheet('media/com_emundus/css/emundus.css');

	    switch ($layout) {

	    	// Sending an email directly to a user.
		    case 'user_message':
			    $m_users = new EmundusModelUsers();

		    	$users = $jinput->getString('users', null);
			    if ($users === 'all') {

				    $us = $m_users->getUsers(0,0);
				    $users = array();
				    foreach ($us as $u) {
					    $users[] = $u->id;
				    }

			    } else {
			    	$users = (array) json_decode(stripslashes($users));
			    }

			    $users = $m_users->getUsersByIds($users);
			    $this->assignRef('users', $users);
	        break;


	        // Default = sending an email to an FNUM.
		    default:
			    $fnums = $jinput->getString('fnums', null);
                $body = $jinput->getRaw('body', null);
                if(empty($body)){
                    $body = JText::_('COM_EMUNDUS_EMAILS_DEAR') . ' [NAME], ';
                }
			    $fnums = ($fnums =='all') ? 'all' : (array) json_decode(stripslashes($fnums), false, 512, JSON_BIGINT_AS_STRING);

			    $m_files = new EmundusModelFiles();
			    $m_application = new EmundusModelApplication();

			    // If we are selecting all fnums: we get them using the files model
			    if ($fnums == "all") {
				    $fnums = $m_files->getAllFnums();
				    $formatted_fnums = [];
				    foreach ($fnums as $fnum) {
					    $tmp = new stdClass();
					    $tmp->fnum = $fnum;
					    $tmp->cid = substr($fnum, 14, 7);
					    $tmp->sid = substr($fnum, 21, 7);
					    $formatted_fnums[] = $tmp;
				    }
				    $fnums = $formatted_fnums;
			    }

			    $fnum_array = [];

			    $tables = array('jos_users.name', 'jos_users.username', 'jos_users.email', 'jos_users.id');
			    foreach ($fnums as $fnum) {
				    if (EmundusHelperAccess::asAccessAction(9, 'c', $current_user->id, $fnum->fnum) && !empty($fnum->sid)) {
					    $user = $m_application->getApplicantInfos($fnum->sid, $tables);
					    $user['campaign_id'] = $fnum->cid;
					    $fnum_array[] = $fnum->fnum;
					    $users[] = $user;
				    }
			    }

			    $this->assignRef('users', $users);
			    $this->assignRef('fnums', $fnum_array);
                $this->assignRef('body', $body);
	        break;

	    }

		parent::display($tpl);
    }
}
