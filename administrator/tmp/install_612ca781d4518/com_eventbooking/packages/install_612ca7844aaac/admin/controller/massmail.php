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

class EventbookingControllerMassmail extends EventbookingController
{
	/**
	 * Send Massmail to registrants of an event
	 */
	public function send()
	{
		/* @var EventbookingModelMassmail $model */
		$model = $this->getModel();

		try
		{
			$model->send($this->input);
			$this->setRedirect('index.php?option=com_eventbooking&view=massmail', Text::_('EB_EMAIL_SENT'));
		}
		catch (Exception $e)
		{
			$this->setRedirect('index.php?option=com_eventbooking&view=massmail', $e->getMessage(), 'error');
		}
	}

	/**
	 * Cancel sending massmail, redirect back to dashboard
	 */
	public function cancel()
	{
		$this->setRedirect('index.php?option=com_eventbooking&view=dashboard');
	}
}
