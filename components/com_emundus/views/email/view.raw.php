<?php
/**
 * @package    Joomla
 * @subpackage emundus
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 * @author     Benjamin Rivalland
 */

// no direct access

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

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
		require_once(JPATH_COMPONENT . DS . 'helpers' . DS . 'javascript.php');
		require_once(JPATH_COMPONENT . DS . 'helpers' . DS . 'filters.php');
		require_once(JPATH_COMPONENT . DS . 'helpers' . DS . 'files.php');
		require_once(JPATH_COMPONENT . DS . 'helpers' . DS . 'list.php');
		require_once(JPATH_COMPONENT . DS . 'helpers' . DS . 'access.php');
		require_once(JPATH_COMPONENT . DS . 'helpers' . DS . 'emails.php');
		require_once(JPATH_COMPONENT . DS . 'helpers' . DS . 'export.php');
		require_once(JPATH_COMPONENT . DS . 'helpers' . DS . 'menu.php');
		require_once(JPATH_COMPONENT . DS . 'models' . DS . 'files.php');

		$this->_user = JFactory::getUser();
		$this->_db   = JFactory::getDBO();

		parent::__construct($config);
	}

	function display($tpl = null)
	{

		$jinput = JFactory::getApplication()->input;
		$fnums  = $jinput->post->getString('fnums', null);
		$fnums  = (array) json_decode(stripslashes($fnums), false, 512, JSON_BIGINT_AS_STRING);

		$dest = $jinput->getInt('desc', 0);

		$eMConfig           = JComponentHelper::getParams('com_emundus');
		$default_email_tmpl = $eMConfig->get('default_email_tmpl', 'expert');

		if ($dest === 3) {

			$document = JFactory::getDocument();
			$document->addStyleSheet("media/com_emundus/css/emundus.css");
			$document->addStyleSheet("media/jui/css/chosen.min.css");
			$document->addScript("media/jui/js/chosen.jquery.min.js");

			if (!is_array($fnums) || $fnums == "all") {
				$m_files     = new EmundusModelFiles;
				$fnums       = $m_files->getAllFnums();
				$fnums_infos = $m_files->getFnumsInfos($fnums, 'object');
				$fnums       = $fnums_infos;
			}

			$fnum_array = array();

			require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'evaluation.php');
			require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'emails.php');
			require_once(JPATH_BASE . '/components/com_emundus/models/application.php');

			$m_application = new EmundusModelApplication();
			$m_evaluation  = new EmundusModelEvaluation;
			$h_emails      = new EmundusHelperEmails();

			$reference_table = $eMConfig->get('reference_table', '#__emundus_references');
			$reference_field = $eMConfig->get('reference_field', 'Email_1 as email');

			foreach ($fnums as $key => $fnum) {

				if ($fnum->fnum === 'em-check-all') {
					unset($fnums[$key]);
					continue;
				}

				if (EmundusHelperAccess::asAccessAction(18, 'c', $this->_user->id, $fnum->fnum)) {
					$fnum_array[] = $fnum->fnum;
					$app_file     = $m_application->getApplication($fnum->fnum);
					$fnum->status = $app_file->status;
				}
			}
			$fnums = array_values($fnums);

			$this->experts_list       = $m_evaluation->getExperts();
			$this->email              = $h_emails->createEmailBlock(['expert'], $this->experts_list);
			$this->fnums              = $fnums;
			$this->fnum_array         = $fnum_array;
			$this->default_email_tmpl = $default_email_tmpl;

		}
		else {
			require_once(JPATH_BASE . '/components/com_emundus/models/application.php');
			$m_application = new EmundusModelApplication();
			foreach ($fnums as $fnum) {
				$users[] = $m_application->getApplicantInfos($fnum['sid'], ['jos_emundus_personal_detail.last_name', 'jos_emundus_personal_detail.first_name', 'jos_users.username', 'jos_users.email']);
			}
			$mailBlock = EmundusHelperEmails::createEmailBlock(['applicant_list']);

			$this->assignRef('email', $mailBlock);
			$this->assignRef('default_email_tmpl', $default_email_tmpl);
		}

		parent::display($tpl);
	}
}