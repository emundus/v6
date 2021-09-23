<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

JLoader::register('EventbookingModelCommonRegistrants', JPATH_ADMINISTRATOR . '/components/com_eventbooking/model/common/registrants.php');

use Joomla\CMS\Factory;

class EventbookingModelHistory extends EventbookingModelCommonRegistrants
{
	/**
	 * ID of a user to get registration history
	 *
	 * @var int
	 */
	protected $userId = null;

	/**
	 * Instantiate the model.
	 *
	 * @param   array  $config  configuration data for the model
	 */
	public function __construct($config = [])
	{
		$config['remember_states'] = false;

		parent::__construct($config);

		$params = EventbookingHelper::getViewParams(Factory::getApplication()->getMenu()->getActive(), ['history']);

		if ((int) $params->get('display_num'))
		{
			$this->setState('limit', (int) $params->get('display_num'));
		}

		$ebConfig = EventbookingHelper::getConfig();

		$this->state->setDefault('filter_order', $ebConfig->get('registration_history_order', 'tbl.id'))
			->setDefault('filter_order_Dir', $ebConfig->get('registration_history_order_dir', 'DESC'));
	}

	/**
	 * Builds SELECT columns list for the query
	 */
	protected function buildQueryColumns(JDatabaseQuery $query)
	{
		$currentDate = $this->getDbo()->quote(EventbookingHelper::getServerTimeFromGMTTime());

		$query->select('ev.enable_cancel_registration, ev.cancel_before_date, ev.activate_certificate_feature, ev.payment_methods, ev.currency_symbol')
			->select("TIMESTAMPDIFF(MINUTE, ev.event_end_date, $currentDate) AS event_end_date_minutes");

		return parent::buildQueryColumns($query);
	}

	/**
	 * Builds a WHERE clause for the query
	 *
	 * @param   JDatabaseQuery  $query
	 *
	 * @return $this
	 */
	protected function buildQueryWhere(JDatabaseQuery $query)
	{
		$user   = Factory::getUser($this->userId);
		$config = EventbookingHelper::getConfig();

		$query->where('(tbl.published >= 1 OR tbl.payment_method LIKE "os_offline%")')
			->where('(tbl.user_id =' . $user->get('id') . ' OR tbl.email = ' . $this->getDbo()->quote($user->get('email')) . ')');

		if (!$config->get('include_group_billing_in_registrants', 1))
		{
			$query->where(' tbl.is_group_billing = 0 ');
		}

		if (!$config->include_group_members_in_registrants)
		{
			$query->where(' tbl.group_id = 0 ');
		}

		return parent::buildQueryWhere($query);
	}

	/**
	 * Set ID of user to get registration history
	 *
	 * @param   int  $userId
	 */
	public function setUserId($userId)
	{
		if ($userId > 0)
		{
			$this->userId = $userId;
		}
	}
}