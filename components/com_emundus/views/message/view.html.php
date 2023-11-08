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
class EmundusViewMessage extends JViewLegacy
{


	public function __construct($config = array())
	{

		require_once(JPATH_COMPONENT . DS . 'helpers' . DS . 'access.php');
		require_once(JPATH_COMPONENT . DS . 'models' . DS . 'messages.php');
		require_once(JPATH_COMPONENT . DS . 'models' . DS . 'files.php');
		require_once(JPATH_COMPONENT . DS . 'models' . DS . 'application.php');

		parent::__construct($config);

	}

	public function display($tpl = null)
	{

		$current_user = JFactory::getUser();

		if (!EmundusHelperAccess::asPartnerAccessLevel($current_user->id)) {
			die (JText::_('COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS'));
		}

		// List of fnum is sent via GET in JSON format.
		$jinput = JFactory::getApplication()->input;

		$fnums_post  = $jinput->getString('fnums', null);
		$fnums_array = ($fnums_post == 'all') ? 'all' : (array) json_decode(stripslashes($fnums_post), false, 512, JSON_BIGINT_AS_STRING);

		$document = JFactory::getDocument();
		$document->addStyleSheet("media/com_emundus/css/emundus.css");

		$m_files       = new EmundusModelFiles();
		$m_application = new EmundusModelApplication();


		// If we are selecting all fnums: we get them using the files model
		if ($fnums_array == 'all') {
			$fnums       = $m_files->getAllFnums();
			$fnums_infos = $m_files->getFnumsInfos($fnums, 'object');
			$fnums       = $fnums_infos;
		}
		else {
			$fnums = array();
			foreach ($fnums_array as $key => $value) {
				$fnums[] = $value->fnum;
			}
		}

		$fnum_array = [];

		$tables = array('jos_users.name', 'jos_users.username', 'jos_users.email', 'jos_users.id');

		foreach ($fnums as $fnum) {
			if (EmundusHelperAccess::asAccessAction(9, 'c', $current_user->id, $fnum->fnum) && !empty($fnum->sid)) {
				$user                = $m_application->getApplicantInfos($fnum->sid, $tables);
				$user['campaign_id'] = $fnum->cid;
				$fnum_array[]        = $fnum->fnum;
				$users[]             = $user;
			}
		}

		$this->assignRef('users', $users);
		$this->assignRef('fnums', $fnum_array);

		parent::display($tpl);

	}
}

?>
