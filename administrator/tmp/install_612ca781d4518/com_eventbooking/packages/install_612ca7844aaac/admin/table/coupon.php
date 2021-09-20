<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die;

/**
 * Class EventbookingTableCoupon
 *
 * @property $id
 * @property $event_id
 * @property $code
 * @property $coupon_type
 * @property $discount
 * @property $times
 * @property $used
 * @property $valid_from
 * @property $valid_to
 * @property $user_id
 * @property $apply_to
 * @property $enable_for
 * @property $published
 */
class EventbookingTableCoupon extends JTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  $db  Database connector object
	 */
	public function __construct(& $db)
	{
		parent::__construct('#__eb_coupons', 'id', $db);
	}

	/**
	 * Sanitize data before storing into database
	 *
	 * @return bool|void
	 */
	public function check()
	{
		$this->times   = (int) $this->times;
		$this->used    = (int) $this->used;
		$this->user_id = (int) $this->user_id;

		if (!(int) $this->valid_from)
		{
			$this->valid_from = $this->getDbo()->getNullDate();
		}

		if (!(int) $this->valid_to)
		{
			$this->valid_to = $this->getDbo()->getNullDate();
		}

		return parent::check();
	}
}
