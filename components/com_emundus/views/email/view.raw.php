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

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

/**
 * HTML View class for the Emundus Component
 *
 * @package    Emundus
 */
class EmundusViewEmail extends JViewLegacy
{
	private $app;
	private $_user;

	protected $mailBlock;
	protected $default_email_tmpl;

	function __construct($config = array())
	{
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'javascript.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'filters.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'files.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'list.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'access.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'emails.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'export.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'menu.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'files.php');

		$this->app = Factory::getApplication();
		if (version_compare(JVERSION, '4.0', '>')) {
			$this->_user = $this->app->getIdentity();
		}
		else {
			$this->_user = Factory::getUser();
		}

		parent::__construct($config);
	}

	function display($tpl = null)
	{

		$h_emails = new EmundusHelperEmails();

		$jinput = $this->app->input;
		$fnums  = $jinput->post->getString('fnums', null);
		$fnums  = (array) json_decode(stripslashes($fnums), false, 512, JSON_BIGINT_AS_STRING);

		$dest = $jinput->getInt('desc', 0);

		$eMConfig                 = ComponentHelper::getParams('com_emundus');
		$this->default_email_tmpl = $eMConfig->get('default_email_tmpl', 'expert');

		if ($dest === 3) {

			if (version_compare(JVERSION, '4.0', '>')) {
				$document = $this->app->getDocument();
				$wa       = $document->getWebAssetManager();
				$wa->registerAndUseStyle('com_emundus', 'media/com_emundus/css/emundus.css');
				$wa->registerAndUseStyle('com_emundus.chosen', 'media/jui/css/chosen.min.css');
				$wa->registerAndUseScript('com_emundus.chosen', 'media/jui/js/chosen.jquery.min.js');
			}
			else {
				$document = Factory::getDocument();
				$document->addStyleSheet("media/com_emundus/css/emundus.css");
				$document->addStyleSheet("media/jui/css/chosen.min.css");
				$document->addScript("media/jui/js/chosen.jquery.min.js");
			}

			if (!is_array($fnums) || $fnums == "all") {
				$m_files     = new EmundusModelFiles;
				$fnums       = $m_files->getAllFnums();
				$fnums_infos = $m_files->getFnumsInfos($fnums, 'object');
				$fnums       = $fnums_infos;
			}

			$fnum_array = array();

			require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'evaluation.php');
			require_once(JPATH_BASE . '/components/com_emundus/models/application.php');

			$m_application = new EmundusModelApplication();
			$m_evaluation  = new EmundusModelEvaluation;


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

			$this->experts_list = $m_evaluation->getExperts();
			$this->email        = $h_emails->createEmailBlock(['expert'], $this->experts_list);
			$this->fnums        = $fnums;
			$this->fnum_array   = $fnum_array;
		}
		else {
			$this->mailBlock = $h_emails->createEmailBlock(['applicant_list']);
		}

		parent::display($tpl);
	}
}