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
 * Class EventbookingTableRegistrant
 *
 * @property $id
 * @property $event_id
 * @property $user_id
 * @property $group_id
 * @property $first_name
 * @property $last_name
 * @property $organization
 * @property $address
 * @property $address2
 * @property $city
 * @property $state
 * @property $country
 * @property $zip
 * @property $phone
 * @property $fax
 * @property $email
 * @property $number_registrants
 * @property $total_amount
 * @property $discount_amount
 * @property $tax_amount
 * @property $payment_processing_fee
 * @property $late_fee
 * @property $amount
 * @property $coupon_id
 * @property $register_date
 * @property $payment_date
 * @property $payment_method
 * @property $transaction_id
 * @property $published
 * @property $is_group_billing
 * @property $invoice_number
 * @property $registration_code
 * @property $ticket_code
 * @property $ticket_qrcode
 * @property $ticket_number
 * @property $params
 * @property $cart_id
 * @property $deposit_amount
 * @property $payment_status
 * @property $user_ip
 * @property $language
 * @property $checked_in
 * @property $checked_in_count
 * @property $checked_in_at
 * @property $checked_out_at
 * @property $process_deposit_payment
 * @property $deposit_payment_processing_fee
 * @property $deposit_payment_transaction_id
 */
class EventbookingTableRegistrant extends JTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  $db  Database connector object
	 */
	public function __construct(& $db)
	{
		parent::__construct('#__eb_registrants', 'id', $db);
	}
}
