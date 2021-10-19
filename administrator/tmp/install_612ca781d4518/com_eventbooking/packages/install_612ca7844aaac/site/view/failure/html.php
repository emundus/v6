<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

class EventbookingViewFailureHtml extends RADViewHtml
{
	/**
	 * Prepare data for the view before it's being rendered
	 *
	 */
	protected function prepareView()
	{
		parent::prepareView();

		$this->setLayout('default');
		$reason = Factory::getSession()->get('omnipay_payment_error_reason');

		if (!$reason)
		{
			$reason = $this->input->getString('failReason', '');
		}

		$this->reason = $reason;
	}
}
