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
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;

class os_offline extends RADPayment
{
	/**
	 * Constructor
	 *
	 * @param   \Joomla\Registry\Registry  $params
	 * @param   array                      $config
	 */
	public function __construct($params, $config = [])
	{
		parent::__construct($params, $config);
	}

	/**
	 * Process payment
	 */
	public function processPayment($row, $data)
	{
		$app    = Factory::getApplication();
		$Itemid = $app->input->getInt('Itemid', 0);

		if ($this->params->get('published') == 1)
		{
			$this->onPaymentSuccess($row, $row->transaction_id);
		}
		else
		{
			$config = EventbookingHelper::getConfig();

			if ($row->is_group_billing)
			{
				EventbookingHelperRegistration::updateGroupRegistrationRecord($row->id);
			}

			EventbookingHelper::callOverridableHelperMethod('Mail', 'sendEmails', [$row, $config]);
		}


		if (PluginHelper::isEnabled('system', 'cache'))
		{
			$url = Route::_('index.php?option=com_eventbooking&view=complete&Itemid=' . $Itemid . '&pt=' . time(), false, false);
		}
		else
		{
			$url = Route::_('index.php?option=com_eventbooking&view=complete&Itemid=' . $Itemid, false, false);
		}

		$app->redirect($url);
	}
}
