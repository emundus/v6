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
 * Class EventbookingTableEvent
 *
 * @property $id
 * @property $parent_id
 * @property $main_category_id
 * @property $location_id
 * @property $title
 * @property $alias
 * @property $event_type
 * @property $event_date
 * @property $event_end_date
 * @property $cut_off_date
 * @property $registration_start_date
 * @property $short_description
 * @property $description
 * @property $thumb
 * @property $image
 * @property $attachment
 * @property $access
 * @property $registration_access
 * @property $individual_price
 * @property $tax_rate
 * @property $event_capacity
 * @property $registration_type
 * @property $registration_handle_url
 * @property $discount_type
 * @property $discount
 * @property $early_bird_discount_type
 * @property $early_bird_discount_date
 * @property $early_bird_discount_amount
 * @property $enable_cancel_registration
 * @property $cancel_before_date
 * @property $discount_groups
 * @property $discount_amounts
 * @property $activate_tickets_pdf
 * @property $ticket_start_number
 * @property $ticket_prefix
 * @property $ticket_layout
 * @property $recurring_type
 * @property $number_days
 * @property $number_weeks
 * @property $number_months
 * @property $weekdays
 * @property $recurring_frequency
 * @property $params
 * @property $created_by
 * @property $ordering
 * @property $published
 */
class EventbookingTableEvent extends JTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  $db  Database connector object
	 */
	public function __construct(& $db)
	{
		parent::__construct('#__eb_events', 'id', $db);
	}
}
