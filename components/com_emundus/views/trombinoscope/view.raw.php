<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 28/03/2017
 * Time: 01:14
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.view');

class EmundusViewTrombinoscope extends JViewLegacy
{
	protected $actions;

	public function __construct($config = array())
	{
		parent::__construct($config);
	}

	public function display($tpl = null)
	{
		$current_user = JFactory::getUser();
		if (!EmundusHelperAccess::asPartnerAccessLevel($current_user->id))
			die(JText::_('COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS'));

		parent::display($tpl);
	}
}

?>
