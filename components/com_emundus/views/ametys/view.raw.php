<?php
/**
 * Created by eMundus.
 * User: brivalland
 * Date: 03/05/16
 * Time: 11:39
 * @package        Joomla
 * @subpackage     eMundus
 * @link           http://www.emundus.fr
 * @copyright      Copyright (C) 2006 eMundus. All rights reserved.
 * @license        GNU/GPL
 * @author         Benjamin Rivalland
 */

// no direct access

defined('_JEXEC') or die('Restricted access');
//error_reporting(E_ALL);
jimport('joomla.application.component.view');

/**
 * HTML View class for the Emundus Component
 *
 * @package    Emundus
 */
class EmundusViewAmetys extends JViewLegacy
{
	//protected $itemId;
	protected $actions;

	public function __construct($config = array())
	{
		require_once(JPATH_COMPONENT . DS . 'helpers' . DS . 'access.php');
		require_once(JPATH_COMPONENT . DS . 'models' . DS . 'profile.php');

		parent::__construct($config);
	}

	public function display($tpl = null)
	{
		$current_user = JFactory::getUser();
		if (!EmundusHelperAccess::asCoordinatorAccessLevel($current_user->id))
			die(JText::_('COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS'));

		$app    = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_emundus');

		$this->itemId = $current_menu->id;
		$layout       = $app->input->getString('layout', null);

		$model = $this->getModel('Ametys');

		switch ($layout) {
			// get Form Campaign
			case 'formcampaign':
				// get list of applicant profiles
				$mProfle           = new EmundusModelProfile;
				$applicantProfiles = $mProfle->getApplicantsProfiles();
				$this->assignRef('applicantProfiles', $applicantProfiles);
				break;

			// get list of application files
			default :
				// default actions

				break;
		}
		parent::display($tpl);
	}
}
