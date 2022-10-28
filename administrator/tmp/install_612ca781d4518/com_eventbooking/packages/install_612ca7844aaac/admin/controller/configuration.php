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

class EventbookingControllerConfiguration extends EventbookingController
{
	/**
	 * Save configuration data
	 */
	public function save()
	{
		$data = $this->input->getData(RAD_INPUT_ALLOWRAW);

		// Make sure no space character is saved to Download ID
		$data['download_id'] = trim($data['download_id']);

		/* @var EventbookingModelConfiguration $model */
		$model = $this->getModel('Configuration', ['ignore_request' => true]);
		$model->store($data);

		$task = $this->getTask();

		if ($task == 'save')
		{
			$this->setRedirect('index.php?option=com_eventbooking&view=dashboard', Text::_('EB_CONFIGURATION_DATA_SAVED'));
		}
		else
		{
			$this->setRedirect('index.php?option=com_eventbooking&view=configuration', Text::_('EB_CONFIGURATION_DATA_SAVED'));
		}
	}

	/**
	 * Cancel configuration action, redirect back to dashboard
	 */
	public function cancel()
	{
		$this->setRedirect('index.php?option=com_eventbooking&view=dashboard');
	}
}
