<?php
/**
 * @package    eMundus
 * @subpackage Components
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 * @author     eMundus SAS - Jonas Lerebours
 */

// no direct access

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');


class EmundusViewProfile extends JViewLegacy
{
	private $_user;

	protected $profile;
	protected $forms;
	protected $attachments;


	function __construct($config = array())
	{
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'access.php');

		$this->_user = JFactory::getUser();

		parent::__construct($config);
	}

	function display($tpl = null)
	{
		if (!EmundusHelperAccess::asAdministratorAccessLevel($this->_user->id) && !EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
			die(JText::_('ACCESS_DENIED'));
		}
		$app           = JFactory::getApplication();
		$p             = $app->input->get('rowid', $default = null, $hash = 'GET', $type = 'none', $mask = 0);
		$model         = $this->getModel();
		$this->profile = $model->getProfile($p);

		if ($this->profile->published != 1) {
			$app->enqueueMessage(JText::_('CANNOT_SETUP_ATTACHMENTS_TO_NON_APPLICANT_USERS'));
			$app->redirect('index.php?option=com_fabrik&view=list&listid=67');
		}

		$this->attachments = $model->getAttachments($p);
		$this->forms       = $model->getForms($p);

		parent::display($tpl);
	}
}

?>