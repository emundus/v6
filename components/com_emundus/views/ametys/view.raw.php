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

jimport('joomla.application.component.view');

use Joomla\CMS\Factory;

/**
 * HTML View class for the Emundus Component
 *
 * @package    Emundus
 */
class EmundusViewAmetys extends JViewLegacy
{
	protected $actions;

	private $app;
	private $user;

	public $applicantProfiles;

	public function __construct($config = array())
	{
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'access.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'profile.php');

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
		if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->user->id)) {
			die(JText::_('COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS'));
		}

		$menu         = $this->app->getMenu();
		$current_menu = $menu->getActive();
		$this->itemId = $current_menu->id;

		$layout = $this->app->input->getString('layout', null);

		if ($layout == 'formcampaign') {
			$mProfle                 = new EmundusModelProfile;
			$this->applicantProfiles = $mProfle->getApplicantsProfiles();
		}

		parent::display($tpl);
	}
}
