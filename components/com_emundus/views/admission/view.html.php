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
class EmundusViewAdmission extends JViewLegacy
{
	protected $itemId;

	public function __construct($config = array())
	{
		parent::__construct($config);
	}

	public function display($tpl = null)
	{
		$app = Factory::getApplication();
		if (version_compare(JVERSION, '4.0', '>')) {
			$current_user = $app->getIdentity();
			$document     = $app->getDocument();
			$wa           = $document->getWebAssetManager();
			$wa->registerAndUseScript(JURI::base() . "media/com_emundus/js/em_admission.js", 'em_admission');
			$session = $app->getSession();
		}
		else {
			$current_user = Factory::getUser();
			$document     = Factory::getDocument();
			$document->addScript(JURI::base() . "media/com_emundus/js/em_admission.js");
			$session = Factory::getSession();
		}

		if (!EmundusHelperAccess::asPartnerAccessLevel($current_user->id)) {
			die(JText::_('COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS'));
		}

		$this->itemId = $app->input->getInt('Itemid', null);

		/* Get the values from the state object that were inserted in the model's construct function */
		$lists['order_dir'] = $session->get('filter_order_Dir');
		$lists['order']     = $session->get('filter_order');

		parent::display($tpl);
	}
}

?>

