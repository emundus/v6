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

class EmundusViewMessages extends JViewLegacy {

	var $user_id = null;
	var $user_name = null;
	var $message_contacts = null;

	public function __construct($config = array()) {

		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
		require_once (JPATH_COMPONENT.DS.'models'.DS.'messages.php');
		require_once (JPATH_COMPONENT.DS.'models'.DS.'files.php');
		require_once (JPATH_COMPONENT.DS.'models'.DS.'application.php');

		parent::__construct($config);

	}

    public function display($tpl = null) {

		$current_user = JFactory::getUser();

    	if (!EmundusHelperAccess::asApplicantAccessLevel($current_user->id)) {
		    die(JText::_('RESTRICTED_ACCESS'));
	    }

        $document = JFactory::getDocument();
        $document->addStyleSheet('/media/com_emundus/lib/Semantic-UI-CSS-master/semantic.min.css');

        $m_messages = new EmundusModelMessages();

        // load all of the contacts
        $this->message_contacts = $m_messages->getContacts();
        $this->user_id = $current_user->id;
        $this->user_name = $current_user->name;

		parent::display($tpl);

    }
}