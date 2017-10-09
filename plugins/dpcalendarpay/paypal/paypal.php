<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2017 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use Omnipay\Omnipay;

JLoader::import('components.com_dpcalendar.helpers.dpcalendar', JPATH_ADMINISTRATOR);

class PlgDPCalendarPayPaypal extends \DPCalendar\Plugin\PaymentPlugin
{

	protected function getPaymentData($gateway, $transactionData, $booking)
	{
		if ($gateway instanceof \Omnipay\PayPal\RestGateway) {
			$updates = array();
			switch ($transactionData['state']) {
				case 'created':
					$updates['state'] = 4;
					break;
				case 'approved':
					$updates['state'] = 1;
					break;
				case 'failed':
					$updates['state'] = 0;
					break;
			}

			$updates['payer_id']       = $transactionData['payer']['payer_info']['payer_id'];
			$updates['payer_email']    = $transactionData['payer']['payer_info']['email'];
			$updates['transaction_id'] = $transactionData['id'];
			if (!empty($transactionData['transactions'])) {
				$tr = $transactionData['transactions'][0];
				if (!empty($tr['related_resources'])) {
					$s                       = $tr['related_resources'][0]['sale'];
					$updates['txn_type']     = $s['payment_mode'];
					$updates['txn_currency'] = $s['amount']['currency'];
					$updates['gross_amount'] = $s['amount']['total'];
					$updates['payment_fee']  = $s['transaction_fee']['value'];
					$updates['net_amount']   = $updates['gross_amount'] - $updates['payment_fee'];
				}

			}
			$updates['raw_data'] = json_encode($transactionData);

			return $updates;
		}


		$response          = $gateway->fetchCheckout(array('token' => $transactionData['TOKEN']))->send();
		$transactonDetails = $response->getData();

		// Check the payment_status
		switch ($transactionData['PAYMENTINFO_0_PAYMENTSTATUS']) {
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
				$newStatus = 0;
				break;
		}

		$updates = array(
			'state'          => $newStatus,
			'payer_id'       => $transactonDetails['PAYERID'],
			'payer_email'    => $transactonDetails['EMAIL'],
			'transaction_id' => $transactonDetails['PAYMENTREQUEST_0_TRANSACTIONID'],
			'txn_type'       => $transactionData['PAYMENTINFO_0_PAYMENTTYPE'],
			'txn_currency'   => $transactonDetails['CURRENCYCODE'],
			'payment_fee'    => $transactionData['PAYMENTINFO_0_FEEAMT'],
			'gross_amount'   => $transactionData['PAYMENTINFO_0_AMT'],
			'net_amount'     => $transactionData['PAYMENTINFO_0_AMT'] - $transactionData['PAYMENTINFO_0_FEEAMT'],
			'raw_data'       => json_encode(
				array(
					'transactionDetails' => $transactonDetails,
					'paymentDetails'     => $transactionData
				))
		);

		return $updates;
	}

	/**
	 *
	 * @return \Omnipay\Common\AbstractGateway
	 */
	protected function getPaymentGateway()
	{
		$params = $this->params;

		$gateway = null;

		if ($params->get('driver', 'express') == 'rest') {
			$gateway = Omnipay::create('PayPal_Rest');

			// Credentials
			$gateway->setClientId(trim($params->get('rest_client_id')));
			$gateway->setSecret(trim($params->get('rest_secret')));
		} else {
			$gateway = Omnipay::create('PayPal_Express');

			// Credentials
			$gateway->setUsername(trim($params->get('api_username')));
			$gateway->setPassword(trim($params->get('api_password')));
			$gateway->setSignature(trim($params->get('api_signature')));

			// Customizing
			$gateway->setBrandName($params->get('cbt'));
			$gateway->setHeaderImageUrl($params->get('cpp_header_image'));
			$gateway->setBorderColor($params->get('cpp_headerborder_color'));
		}

		$gateway->setTestMode($params->get('sandbox') == '1');

		return $gateway;
	}

	protected function getPurchaseParameters($gateway, $booking)
	{
		$purchaseParameters = parent::getPurchaseParameters($gateway, $booking);

		$purchaseParameters['redirectUrl'] = $purchaseParameters['returnUrl'];
		$purchaseParameters['description'] = DPCalendarHelper::renderPrice($purchaseParameters['amount'], $purchaseParameters['currency']);

		$input = JFactory::getApplication()->input;
		if ($input->get('PayerID')) {
			$purchaseParameters['payerId'] = $input->get('PayerID');
		}
		if ($input->get('paymentId')) {
			$purchaseParameters['transactionReference'] = $input->get('paymentId');
		}

		return $purchaseParameters;
	}
}
