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
class EmundusViewMessages extends JViewLegacy
{

	var $user_id = null;
	var $user_name = null;
	var $messages = null;
	var $message_contacts = null;
	var $other_user = null;
	var $offers = null;
	var $chatroom_id = null;

	public function __construct($config = array())
	{
		require_once(JPATH_COMPONENT . DS . 'helpers' . DS . 'access.php');
		require_once(JPATH_COMPONENT . DS . 'models' . DS . 'messages.php');

		parent::__construct($config);
	}

	public function display($tpl = null)
	{

		$current_user = JFactory::getUser();

		if (!EmundusHelperAccess::asApplicantAccessLevel($current_user->id)) {
			die(JText::_('COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS'));
		}

		$m_messages = new EmundusModelMessages();

		$jinput = JFactory::getApplication()->input;
		$layout = $jinput->get->get('layout', 'default');

		if ($layout === 'chat') {

			$this->other_user = $jinput->get->getInt('chatid', null);
			$this->messages   = $m_messages->loadMessages($this->other_user);
			$this->user_id    = $current_user->id;

			require_once(JPATH_COMPONENT . DS . 'models' . DS . 'cifre.php');
			$m_cifre      = new EmundusModelCifre();
			$this->offers = $m_cifre->getOffersBetweenUsers($this->user_id, $this->other_user);

		}
		elseif ($layout === 'hesamchatroom') {

			$chatroom = $jinput->get->getInt('chatroom', null);
			if (empty($chatroom)) {
				die('error');
			}

			if (!in_array($current_user->id, $m_messages->getChatroomUsersId($chatroom))) {
				die(JText::_('COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS'));
			}

			$chatroom = $m_messages->getChatroom($chatroom);

			require_once(JPATH_COMPONENT . DS . 'models' . DS . 'cifre.php');
			$m_cifre      = new EmundusModelCifre();
			$this->offers = $m_cifre->getOffer($chatroom->fnum);

			$this->messages    = $m_messages->getChatroomMessages($chatroom->id);
			$this->user_id     = $current_user->id;
			$this->user_name   = $current_user->name;
			$this->chatroom_id = $chatroom->id;

		}
		else {
			$this->message_contacts = $m_messages->getContacts();
			$this->user_id          = $current_user->id;
			$this->user_name        = $current_user->name;
		}

		parent::display($tpl);

	}
}
