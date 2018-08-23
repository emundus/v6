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


	public function __construct($config = array()) {
		

		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
		require_once (JPATH_COMPONENT.DS.'models'.DS.'messages.php');
		require_once (JPATH_COMPONENT.DS.'models'.DS.'files.php');
		require_once (JPATH_COMPONENT.DS.'models'.DS.'application.php');
		parent::__construct($config);

	}

    public function display($tpl = null) {

		$current_user = JFactory::getUser();

    	if (!EmundusHelperAccess::asApplicantAccessLevel($current_user->id))
			die (JText::_('RESTRICTED_ACCESS'));

        $m_messages = new EmundusModelMessages();

        $jinput = JFactory::getApplication()->input;
        $id = $jinput->post->get('id', null);
        $tmpl = $jinput->get->get('layout', 'default');

        if($tmpl == 'chat') {
            $this->getMessages = $m_messages->loadMessages($id);
            $this->user_id = $current_user->id;
        }


        elseif ($tmpl == 'default') {
            $this->message_contacts = $m_messages->getContacts();
            $this->user_id = $current_user->id;
            $this->user_name = $current_user->name;
            parent::display($tpl);
        }


		parent::display($tpl);

    }
}
?>