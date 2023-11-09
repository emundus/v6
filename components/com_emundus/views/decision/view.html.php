<?php
/**
 * @package    eMundus
 * @subpackage Components
 *             components/com_emundus/emundus.php
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 * @author     Benjamin Rivalland
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

use Joomla\CMS\Factory;

/**
 * HTML View class for the Emundus Component
 *
 * @package    Emundus
 */
class EmundusViewDecision extends JViewLegacy
{
	private $app;
	private $user;
	protected $itemId;

	public function __construct($config = array())
	{
		$this->app = Factory::getApplication();
		if (version_compare(JVERSION, '4.0', '>')) {
			$this->user = $this->app->getIdentity();
		}
		else {
			$this->user = Factory::getUser();
		}

		parent::__construct($config);
	}

	public function display($tpl = null)
	{
		if (!EmundusHelperAccess::asPartnerAccessLevel($this->user->id)) {
			die(JText::_('COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS'));
		}

		$this->itemId = $this->app->input->getInt('Itemid', null);

		/* Get the values from the state object that were inserted in the model's construct function */
		if (version_compare(JVERSION, '4.0', '>')) {
			$session = $this->app->getSession();
		}
		else {
			$session = Factory::getSession();
		}

		$lists['order_dir'] = $session->get('filter_order_Dir');
		$lists['order']     = $session->get('filter_order');

		parent::display($tpl);
	}
}

?>

