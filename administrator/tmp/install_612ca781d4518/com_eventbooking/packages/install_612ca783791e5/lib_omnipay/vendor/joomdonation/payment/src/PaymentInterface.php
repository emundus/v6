<?php
/**
 * Part of the Ossolution Payment Package
 *
 * @copyright  Copyright (C) 2016 Ossolution Team. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Ossolution\Payment;

/**
 * Payment Interface
 *
 * @since 1.0
 */
interface PaymentInterface
{
	/**
	 * Constructor.
	 *
	 * @param JRegistry $params
	 * @param array     $config
	 */
	public function __construct($params, $config = array());

	/**
	 * Get the payment data from user input (usually in a post request), pass that data to payment
	 * gateway for processing payment.
	 *
	 * @param JTable $row
	 * @param array  $data
	 *
	 * @return void
	 */
	public function processPayment($row, $data);
}
