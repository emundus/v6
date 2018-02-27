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

class PlgDPCalendarPayStripe extends \DPCalendar\Plugin\PaymentPlugin
{
	protected function getPurchaseParameters($gateway, $booking)
	{
		$params          = parent::getPurchaseParameters($gateway, $booking);
		$params['token'] = JFactory::getApplication()->input->get('token', 1);

		return $params;
	}

	public function getPaymentData($gateway, $transactionData, $booking)
	{
		$transaction = $gateway->fetchBalanceTransaction();
		$transaction->setBalanceTransactionReference($transactionData['balance_transaction']);
		$response = $transaction->send();

		// Error during balance fetch
		if (!$response->isSuccessful()) {
			return $response->getMessage();
		}

		$balanceData = $response->getData();

		// Status: 0 (Not done), 1 Completed, 4 (Pending)
		$status = 1;

		// Balance data
		$fee         = null;
		$grossAmount = null;
		$netAmount   = null;
		if (is_numeric($balanceData['fee'])) {
			$fee = number_format($balanceData['fee'] / 100, 2);
		}
		if (is_numeric($balanceData['amount'])) {
			$grossAmount = number_format($balanceData['amount'] / 100, 2);
		}
		if (is_numeric($balanceData['net'])) {
			$netAmount = number_format($balanceData['net'] / 100, 2);
		}

		$updates = array(
			'processor_key'  => $transactionData['balance_transaction'],
			'state'          => $status,
			'payer_id'       => $transactionData['card']['id'],
			'payer_email'    => $transactionData['card']['name'],
			'transaction_id' => $transactionData['id'],
			'txn_type'       => $balanceData['type'],
			'txn_currency'   => $balanceData['currency'],
			'payment_fee'    => $fee,
			'gross_amount'   => $grossAmount,
			'net_amount'     => $netAmount,
			'raw_data'       => json_encode(array('transactionData' => $transactionData, 'balanceData' => $balanceData))
		);

		return $updates;
	}

	/**
	 *
	 * @return \Omnipay\Stripe\Gateway
	 */
	protected function getPaymentGateway()
	{
		$gateway = Omnipay::create('Stripe');
		$gateway->setApiKey($this->params->get('data-skey'));

		return $gateway;
	}
}
