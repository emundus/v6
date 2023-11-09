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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * HTML View class for the Emundus Component
 *
 * @package Emundus
 */
class EmundusViewMessage extends JViewLegacy
{
	private $app;

	protected $users;
	protected $fnums;
	protected $body;
	protected $data;

	public function __construct($config = array())
	{

		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'access.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'messages.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'files.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'application.php');

		parent::__construct($config);

		$this->app = Factory::getApplication();

	}

	public function display($tpl = null)
	{

		$current_user = $this->app->getIdentity();

		if (!EmundusHelperAccess::asPartnerAccessLevel($current_user->id)) {
			die (Text::_('COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS'));
		}

		// List of fnum is sent via GET in JSON format.
		$jinput = $this->app->input;
		$layout = $jinput->getString('layout', null);

		$document = $this->app->getDocument();
		$wa       = $document->getWebAssetManager();
		$wa->registerAndUseStyle('emundus_css', 'media/com_emundus/css/emundus.css');

		switch ($layout) {
			// Sending an email directly to a user.
			case 'user_message':
				$m_users = new EmundusModelUsers();

				$this->users = $jinput->getString('users', null);
				if ($this->users === 'all') {
					$us          = $m_users->getUsers(0, 0);
					$this->users = array();
					foreach ($us as $u) {
						$this->users[] = $u->id;
					}

				}
				else {
					$this->users = (array) json_decode(stripslashes($this->users));

					foreach ($this->users as $key => $value) {
						if (!is_numeric($value)) {
							unset($this->users[$key]);
						}
					}
				}

				$this->users = $m_users->getUsersByIds($this->users);
				break;


			// Default = sending an email to an FNUM.
			default:
				$fnums      = $jinput->getString('fnums', null);
				$this->data = $jinput->getArray()['data'];
				$this->body = $jinput->getRaw('body', '');
				if (empty($this->body)) {
					$this->body = Text::_('COM_EMUNDUS_EMAILS_DEAR') . ' [NAME], ';
				}
				$fnums = ($fnums == 'all') ? 'all' : (array) json_decode(stripslashes($fnums), false, 512, JSON_BIGINT_AS_STRING);

				$m_files       = new EmundusModelFiles();
				$m_application = new EmundusModelApplication();

				// If we are selecting all fnums: we get them using the files model
				if ($fnums == "all") {
					$fnums           = $m_files->getAllFnums();
					$formatted_fnums = [];
					foreach ($fnums as $fnum) {
						$tmp               = new stdClass();
						$tmp->fnum         = $fnum;
						$tmp->cid          = substr($fnum, 14, 7);
						$tmp->sid          = substr($fnum, 21, 7);
						$formatted_fnums[] = $tmp;
					}
					$fnums = $formatted_fnums;
				}

				$fnum_array = [];

				$tables = array('jos_users.name', 'jos_users.username', 'jos_users.email', 'jos_users.id');
				foreach ($fnums as $fnum) {
					if (EmundusHelperAccess::asAccessAction(9, 'c', $current_user->id, $fnum->fnum) && !empty($fnum->sid)) {
						$user                = $m_application->getApplicantInfos($fnum->sid, $tables);
						$user['campaign_id'] = $fnum->cid;
						$fnum_array[]        = $fnum->fnum;
						$this->users[]       = $user;
					}
				}

				$this->fnums = $fnum_array;
				break;

		}

		parent::display($tpl);
	}
}
