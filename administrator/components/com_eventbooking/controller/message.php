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

class EventbookingControllerMessage extends EventbookingController
{
	public function save()
	{
		$data = $this->input->getData(RAD_INPUT_ALLOWRAW);
		unset($data['option'], $data['view'], $data['task']);

		$model = $this->getModel('Message', ['ignore_request' => true]);
		$model->store($data);

		$task = $this->getTask();
		if ($task == 'save')
		{
			$this->setRedirect('index.php?option=com_eventbooking&view=dashboard', Text::_('EB_MESSAGES_SAVED'));
		}
		else
		{
			$this->setRedirect('index.php?option=com_eventbooking&view=message', Text::_('EB_MESSAGES_SAVED'));
		}
	}

	public function cancel()
	{
		$this->setRedirect('index.php?option=com_eventbooking&view=dashboard');
	}
}
