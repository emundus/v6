<?php
/**
 * @package    Joomla
 * @subpackage emundus
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 * @author     Benjamin Rivalland
 */

// no direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');
//error_reporting(E_ALL);
/**
 * HTML View class for the Emundus Component
 *
 * @package    Emundus
 */

class EmundusViewEmail extends JViewLegacy
{
	var $_user = null;
	var $_db = null;

	function __construct($config = array())
	{
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'javascript.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'files.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'list.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'emails.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'export.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'menu.php');

		$this->_user = JFactory::getUser();
		$this->_db = JFactory::getDBO();

		parent::__construct($config);
	}

	function display($tpl = null)
	{

		$jinput = JFactory::getApplication()->input;
		$fnums = $jinput->getString('fnums', null);

		$fnums = json_decode($fnums, true);
		require_once(JPATH_BASE . '/components/com_emundus/models/application.php');
		$appModel = new EmundusModelApplication();
		foreach ($fnums as $fnum)
		{
			$users[] = $appModel->getApplicantInfos($fnum['sid'], ['jos_emundus_personal_detail.last_name', 'jos_emundus_personal_detail.first_name', 'jos_users.username', 'jos_users.email']);
		}
		$mailBlock = EmundusHelperEmails::createEmailBlock(['applicant_list']);

		$eMConfig = JComponentHelper::getParams('com_emundus');
		$default_email_tmpl = $eMConfig->get('default_email_tmpl', 'expert');
	
		$this->assignRef('email', $mailBlock);
		$this->assignRef('default_email_tmpl', $default_email_tmpl);
		
		parent::display($tpl);
	}
}
?>