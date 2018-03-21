<?php
/**
 * @package    Joomla
 * @subpackage emundus
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

	function __construct($config = array()) {

		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'messages.php');
		require_once (JPATH_COMPONENT.DS.'models'.DS.'application.php');

		parent::__construct($config);
	}

	function display($tpl = null) {

		// Fnums are sent in a JSON via GET
		$jinput = JFactory::getApplication()->input;
		$fnums = $jinput->getString('fnums', null);
		$fnums = json_decode($fnums, true);

		$m_application = new EmundusModelApplication();
		$h_messages = new EmundusHelperMessages();

		foreach ($fnums as $fnum) {
			$users[] = $m_application->getApplicantInfos($fnum['sid'], ['jos_emundus_personal_detail.last_name', 'jos_emundus_personal_detail.first_name', 'jos_users.username', 'jos_users.email']);
		}

		$messageBlock = $h_messages->createMessageBlock(['applicant_list']);

		$eMConfig = JComponentHelper::getParams('com_emundus');
		$default_email_tmpl = $eMConfig->get('default_email_tmpl', 'expert');

		$this->assignRef('email', $messageBlock);
		$this->assignRef('default_email_tmpl', $default_email_tmpl);

		parent::display($tpl);
	}
}
?>