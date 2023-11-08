<?php

/**
 * @version     1.0.0
 * @package     com_emundus
 * @copyright   Copyright (C) 2016 emundus.fr. Tous droits réservés.
 * @license     GNU General Public License version 2 ou version ultérieure ; Voir LICENSE.txt
 * @author      emundus <dev@emundus.fr> - http://www.emundus.fr
 */
// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

use Joomla\CMS\Factory;

/**
 * Ametys controller class.
 */
class EmundusControllerAmetys extends EmundusController
{
	protected $app;

	private $_user;

	public function __construct($config = array())
	{
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'files.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'filters.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'list.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'access.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'emails.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'export.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'menu.php');

		$this->app   = Factory::getApplication();
		$this->_user = $this->app->getIdentity();

		parent::__construct($config);
	}

	/**
	 * Method to display tools.
	 *
	 * @return  void
	 * @since   1.6
	 */
	function display($cachable = false, $urlparams = false)
	{

		if (!$this->input->get('view')) {
			$default = 'default';
			$this->input->set('view', $default);
		}

		parent::display();
	}

	public function getprogrammes()
	{
		$m_ametys = $this->getModel('ametys');

		if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
			$result = 0;
			$tab    = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {
			$programmes = $m_ametys->getProgrammes();

			if (count($programmes) > 0)
				$tab = array('status' => 1, 'msg' => JText::_('PROGRAMMES_RETRIEVED'), 'data' => $programmes);
			else
				$tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_PROGRAMMES'), 'data' => $programmes);
		}

		echo json_encode((object) $tab);
		exit;
	}

	public function addcampaigns()
	{
		$data                      = array();
		$data['start_date']        = $this->input->get('start_date', null, 'POST');
		$data['end_date']          = $this->input->get('end_date', null, 'POST');
		$data['profile_id']        = $this->input->get('profile_id', null, 'POST');
		$data['year']              = $this->input->get('year', null, 'POST');
		$data['short_description'] = $this->input->get('short_description', null, 'POST');

		$m_campaign  = $this->getModel('Campaign');
		$m_programme = $this->getModel('Programme');

		if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
			$result = 0;
			$tab    = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {
			$codeList           = array();
			$codeList['IN']     = array();
			$codeList['NOT_IN'] = array('0312421N', '0312760G');

			$programmes = $m_programme->getProgrammes(1, $codeList);

			if (count($programmes) > 0)
				$result = $m_campaign->addCampaignsForProgrammes($data, $programmes);
			else $result = false;

			if ($result === false)
				$tab = array('status' => 0, 'msg' => JText::_('COM_EMUNDUS_AMETYS_ERROR_CANNOT_ADD_CAMPAIGNS'), 'data' => $result);
			else $tab = array('status' => 1, 'msg' => JText::_('COM_EMUNDUS_CAMPAIGNS_ADDED'), 'data' => $result);
		}

		echo json_encode((object) $tab);
		exit;
	}

}
