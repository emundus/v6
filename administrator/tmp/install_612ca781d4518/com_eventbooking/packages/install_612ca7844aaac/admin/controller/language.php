<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

class EventbookingControllerLanguage extends EventbookingController
{
	public function save()
	{
		$data  = $this->input->getData();
		$model = $this->getModel();
		$model->save($data);

		$task = $this->getTask();
		if ($task == 'apply')
		{
			$lang = $data['filter_language'];
			$item = $data['filter_item'];
			$this->setRedirect('index.php?option=com_eventbooking&view=language&filter_language=' . $lang . '&filter_item=' . $item);
		}
		else
		{
			$this->setRedirect('index.php?option=com_eventbooking&view=language&view=dashboard');
		}
	}

	/**
	 * Cancel registration, redirect to dashboard page
	 */
	public function cancel()
	{
		$this->setRedirect('index.php?option=com_eventbooking&view=dashboard');
	}
}
