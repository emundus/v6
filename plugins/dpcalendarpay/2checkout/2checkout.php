<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2018 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use Omnipay\Omnipay;

if (!JLoader::import('components.com_dpcalendar.helpers.dpcalendar', JPATH_ADMINISTRATOR)) {
	return;
}

class PlgDPCalendarPay2Checkout extends \DPCalendar\Plugin\PaymentPlugin
{

	protected function getPurchaseParameters($gateway, $booking)
	{
		$params                  = parent::getPurchaseParameters($gateway, $booking);
		$params['accountNumber'] = $gateway->getAccountNumber();
		$params['secretWord']    = $gateway->getSecretWord();
		$params['transactionId'] = 1;

		return $params;
	}

	protected function getPaymentData($gateway, $transactionData, $booking)
	{
		$newStatus = 1;
		switch ($transactionData['message_type']) {
			case 'ORDER_CREATED':
			case 'FRAUD_STATUS_CHANGED':
			case 'INVOICE_STATUS_CHANGED':
				switch ($transactionData['invoice_status']) {
					case 'approved':
						// "Approved" means "we're about to request the money"
						// or something like that, dunno
						$newStatus = 1;
						break;

					case 'pending':
						// "Pending" means "accepted by bank, the money is not
						// in your account yet"
						$newStatus = 1;
						break;

					case 'deposited':
						// "Deposited" means "the money is yours".
						$newStatus = 1;
						break;

					case 'declined':
					default:
						// "Declined" means "you ain't gonna have your money,
						// bro"
						$newStatus = 4;
						break;
				}
				break;

			case 'REFUND_ISSUED':
				$newStatus = 0;
				break;

			case 'RECURRING_INSTALLMENT_SUCCESS':
				$newStatus = 1;
				break;
		}

		$updates = array(
			'processor_key'  => $transactionData['invoice_id'],
			'state'          => $status,
			'payer_email'    => $transactionData['customer_email'],
			'transaction_id' => $transactionData['message_id'],
			'txn_type'       => $balanceData['payment_type'],
			'txn_currency'   => $balanceData['cust_currency'],
			'gross_amount'   => $transactionData['invoice_cust_amount'],
			'raw_data'       => json_encode(array(
				'transactionData' => $transactionData
			))
		);

		return $updates;
	}

	/**
	 *
	 * @return \Omnipay\TwoCheckout\Gateway
	 */
	protected function getPaymentGateway()
	{
		$gateway = Omnipay::create('TwoCheckout');
		$gateway->setAccountNumber($this->params->get('account_number'));
		$gateway->setSecretWord($this->params->get('secret_word'));

		$gateway->setTestMode($this->params->get('sandbox') == '1');

		return $gateway;
	}
}
