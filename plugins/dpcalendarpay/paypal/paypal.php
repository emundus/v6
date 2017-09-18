<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use Omnipay\Omnipay;

JLoader::import('components.com_dpcalendar.libraries.dpcalendar.paymentplugin', JPATH_ADMINISTRATOR);
if (! class_exists('DPCalendarPaymentPlugin'))
{
	return;
}

class PlgDPCalendarPayPaypal extends DPCalendarPaymentPlugin
{

	protected function getPaymentData ($gateway, $transactionData, $booking)
	{
		$response = $gateway->fetchCheckout(array(
				'token' => $transactionData['TOKEN']
		))->send();
		$transactonDetails = $response->getData();

		// Check the payment_status
		switch ($paymentDetails['PAYMENTINFO_0_PAYMENTSTATUS'])
		{
			case 'Canceled_Reversal':
			case 'Completed':
				$newStatus = 1;
				break;

			case 'Created':
			case 'Pending':
			case 'Processed':
				$newStatus = 4;
				break;

			case 'Denied':
			case 'Expired':
			case 'Failed':
			case 'Refunded':
			case 'Reversed':
			case 'Voided':
			default:
				/* Partial refunds can only by issued by the merchant. In that case
				 * we don't want the subscription to be cancelled. We have to let the
				 * merchant adjust its parameters if needed. */
				if ($isPartialRefund)
				{
					$newStatus = 0;
				}
				else
				{
					$newStatus = 0;
				}
				break;
		}

		$updates = array(
				'state' => $newStatus,
				'payer_id' => $transactonDetails['PAYERID'],
				'payer_email' => $transactonDetails['EMAIL'],
				'transaction_id' => $transactonDetails['PAYMENTREQUEST_0_TRANSACTIONID'],
				'txn_type' => $paymentDetails['PAYMENTINFO_0_PAYMENTTYPE'],
				'txn_currency' => $transactonDetails['CURRENCYCODE'],
				'payment_fee' => $paymentDetails['PAYMENTINFO_0_FEEAMT'],
				'gross_amount' => $paymentDetails['PAYMENTINFO_0_AMT'],
				'net_amount' => $paymentDetails['PAYMENTINFO_0_AMT'] - $paymentDetails['PAYMENTINFO_0_FEEAMT'],
				'raw_data' => json_encode(
						array(
								'transactionDetails' => $transactonDetails,
								'paymentDetails' => $paymentDetails
						))
		);
		return $updates;
	}

	/**
	 *
	 * @return \Omnipay\PayPal\ExpressGateway
	 */
	protected function getPaymentGateway ()
	{
		$params = $this->params;

		$gateway = Omnipay::create('PayPal_Express');

		// Credentials
		$gateway->setUsername($params->get('api_username'));
		$gateway->setPassword($params->get('api_password'));
		$gateway->setSignature($params->get('api_signature'));

		// Customizing
		$gateway->setBrandName($params->get('cbt'));
		$gateway->setHeaderImageUrl($params->get('cpp_header_image'));
		$gateway->setBorderColor($params->get('cpp_headerborder_color'));

		$gateway->setTestMode($params->get('sandbox') == '1');

		return $gateway;
	}
}
