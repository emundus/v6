<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;

class plgEventBookingWaitinglist extends CMSPlugin
{
	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 */
	protected $app;

	/**
	 * Display event's waiting list
	 *
	 * @param $row
	 *
	 * @return array|string
	 */
	public function onEventDisplay($row)
	{
		// Require library + register autoloader
		require_once JPATH_ADMINISTRATOR . '/components/com_eventbooking/libraries/rad/bootstrap.php';

		if (!EventbookingHelperAcl::canViewRegistrantList($row->id))
		{
			return;
		}

		EventbookingHelper::loadLanguage();
		$request = ['option' => 'com_eventbooking', 'view' => 'registrantlist', 'id' => $row->id, 'registrant_type' => 3, 'hmvc_call' => 1, 'Itemid' => $this->app->input->getInt('Itemid'), 'limit' => 1000];
		$input   = new RADInput($request);
		$config  = require JPATH_ADMINISTRATOR . '/components/com_eventbooking/config.php';
		ob_start();

		//Initialize the controller, execute the task
		RADController::getInstance('com_eventbooking', $input, $config)
			->execute();

		$form = ob_get_clean();

		return ['title'    => Text::_('EB_WAITING_LIST'),
		        'form'     => $form,
		        'position' => $this->params->get('output_position', 'before_register_buttons'),
		];
	}
}