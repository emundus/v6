<?php
/**
 * Part of the Ossolution Payment Package
 *
 * @copyright  Copyright (C) 2016 Ossolution Team. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Ossolution\Payment;

use Omnipay\Omnipay;
use Omnipay\Common\CreditCard;
use JFactory;

/**
 * Payment class which use Omnipay payment class for processing payment
 *
 * @since 1.0
 */
abstract class OmnipayPayment extends AbstractPayment implements PaymentInterface
{
	/**
	 * Name of the Omnipay package use for this payment class
	 *
	 * @var string
	 */
	protected $omnipayPackage;

	/**
	 * Omnipay gateway
	 *
	 * \Omnipay\Common\AbstractGateway
	 */
	protected $gateway;

	/**
	 * Url of the page user will be redirected to after successful payment
	 *
	 * @var string
	 */
	protected $paymentSuccessUrl;

	/**
	 * URL of the page users will be redirected to when payment failure
	 *
	 * @var string
	 */
	protected $paymentFailureUrl;

	/**
	 * Map between gateway default parameters and payment plugin parameters
	 *
	 * @var array
	 */
	protected $paramsMap = array();

	/**
	 * Instantiate the payment object
	 *
	 * @param JRegistry $params
	 * @param array     $config
	 */
	public function __construct($params, $config = array())
	{
		if (isset($config['omnipay_package']))
		{
			$this->omnipayPackage = $config['omnipay_package'];
		}

		if (isset($config['params_map']))
		{
			$this->paramsMap = $config['params_map'];
		}

		parent::__construct($params, $config);
	}

	/**
	 * Set name of the Omnipay package use for this payment method class
	 *
	 * @param string $value
	 */
	public function setOmnipayPackage($value)
	{
		$this->omnipayPackage = $value;
	}

	/**
	 * Method to get Omnipay payment gateway object
	 *
	 * @return \Omnipay\Common\AbstractGateway
	 */
	protected function getGateway()
	{
		if (is_null($this->gateway))
		{
			$gateway = Omnipay::create($this->omnipayPackage);

			$this->initialise($gateway);

			$this->gateway = $gateway;
		}

		return $this->gateway;
	}

	/**
	 * This method is used to set parameters for the gateway object from payment plugin parameters
	 *
	 * Usually, it needs to be override by the actual payment method class
	 *
	 * @param \Omnipay\Common\AbstractGateway $gateway
	 */
	protected function initialise($gateway)
	{
		if (!empty($this->paramsMap))
		{
			$parameters = $gateway->getDefaultParameters();

			foreach ($parameters as $name => $value)
			{
				if (isset($this->paramsMap[$name]))
				{
					if ($name == 'testMode' || $name == 'developerMode')
					{
						$testMode = !(bool) $this->params->get($this->paramsMap[$name], $value);
						$gateway->setParameter($name, $testMode);
					}
					else
					{
						$gateway->setParameter($name, $this->params->get($this->paramsMap[$name], $value));
					}
				}
			}
		}
	}

	/**
	 * {@inheritdoc }
	 *
	 * @param $row
	 * @param $data
	 *
	 * @return void
	 */
	public function processPayment($row, $data)
	{
		$app      = JFactory::getApplication();
		$gateway  = $this->getGateway();
		$cardData = $this->getOmnipayCard($data);

		// Register payment success and payment failure URL
		$this->setPaymentSuccessUrl($row->id, $data);
		$this->setPaymentFailureUrl($row->id, $data);

		/* @var $request \Omnipay\Common\Message\AbstractRequest */
		try
		{
			$request = $gateway->purchase(array('card' => $cardData));

			// Add additional data to the request if needed
			$this->beforeRequestSend($request, $row, $data);

			/* @var $response \Omnipay\Common\Message\ResponseInterface */

			$response = $request->send();
		}
		catch (\Exception $e)
		{
			$session = JFactory::getSession();
			$session->set('omnipay_payment_error_reason', $e->getMessage());
			$app->redirect($this->paymentFailureUrl);
		}

		if ($response->isSuccessful())
		{
			// Payment success
			$this->onPaymentSuccess($row, $response->getTransactionReference());
			$app->redirect($this->paymentSuccessUrl);
		}
		elseif ($response->isRedirect())
		{
			/* @var $response \Omnipay\Common\Message\RedirectResponseInterface */
			if ($response->getRedirectMethod() == 'GET')
			{
				$app->redirect($response->getRedirectUrl());
			}
			else
			{
				$redirectUrl = $response->getRedirectUrl();
				$data        = $response->getRedirectData();
				$this->renderRedirectForm($redirectUrl, $data);
			}
		}
		else
		{
			//Payment failure, display error message to users
			$session = JFactory::getSession();
			$session->set('omnipay_payment_error_reason', $response->getMessage());
			$app->redirect($this->paymentFailureUrl);
		}
	}

	/**
	 * This method need to be implemented by the payment plugin class. It needs to set url which users will be
	 * redirected to after a successful payment. The url is stored in paymentSuccessUrl property
	 *
	 * @param int   $id
	 * @param array $data
	 *
	 * @return void
	 */
	abstract protected function setPaymentSuccessUrl($id, $data = array());

	/**
	 * This method need to be implemented by the payment plugin class. It needs to set url which users will be
	 * redirected to when the payment is not success for some reasons. The url is stored in paymentFailureUrl property
	 *
	 * @param int   $id
	 * @param array $data
	 *
	 * @return void
	 */
	abstract protected function setPaymentFailureUrl($id, $data = array());

	/**
	 * This method need to be implemented by the payment plugin class. It is called when a payment success. Usually,
	 * this method will update status of the order to success, trigger onPaymentSuccess event and send notification emails
	 * to administrator(s) and customer
	 *
	 * @param JTable $row
	 * @param string $transactionId
	 *
	 * @return void
	 */
	abstract protected function onPaymentSuccess($row, $transactionId);

	/**
	 * This method need to be implemented by the payment gateway class. It needs to init the JTable order record,
	 * update it with transaction data and then call onPaymentSuccess method to complete the order.
	 *
	 * @param int    $id
	 * @param string $transactionId
	 *
	 * @return mixed
	 */
	abstract protected function onVerifyPaymentSuccess($id, $transactionId);

	/**
	 * This method is usually called by payment method class to add additional data
	 * to the request message before that message is actually sent to the payment gateway
	 *
	 * The actual method class should take this chance to set the payment return, cancel and notify URLs for the request
	 * message object...
	 *
	 * @param \Omnipay\Common\Message\AbstractRequest $request
	 * @param JTable                                  $row
	 * @param array                                   $data
	 */
	protected function beforeRequestSend($request, $row, $data)
	{
		$request->setTransactionId($row->id);
		$request->setTransactionReference($row->id);
	}

	/***
	 * This method is called by off-site payment method when the payment gateway send notification back to the site
	 * to inform about the payment
	 *
	 * Usually, this method will validate the payment and in case it is valid, it needs to update status of a record,
	 * sending notification emails...
	 *
	 * In rarely case, it also need to redirect users to registration complete page
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function verifyPayment()
	{
		$app     = JFactory::getApplication();
		$gateway = $this->getGateway();

		try
		{
			$success      = true;
			$errorMessage = '';

			/* @var $response \Omnipay\Common\Message\ResponseInterface */
			$response = $gateway->completePurchase()->send();

			if ($response->isSuccessful())
			{
				// Payment success
				$id = $response->getTransactionId();

				// In case the payment package doesn't return any Transaction ID, we will just get ID of record from URL
				if (empty($id))
				{
					$id = JFactory::getApplication()->input->get->getInt('id', 0);
				}
				$transactionId = $response->getTransactionReference();

				$this->onVerifyPaymentSuccess($id, $transactionId);
			}
			else
			{
				$success      = false;
				$errorMessage = $response->getMessage();
			}
		}
		catch (\Exception $e)
		{
			$success = false;

			$errorMessage = $e->getMessage();
		}

		if ($this->redirectOnPaymentVerify())
		{
			if ($success)
			{
				$this->setPaymentSuccessUrl($id);
				$app->redirect($this->paymentSuccessUrl);
			}
			else
			{
				$session = JFactory::getSession();
				$session->set('omnipay_payment_error_reason', $errorMessage);

				$this->setPaymentFailureUrl($id);
				$app->redirect($this->paymentFailureUrl);
			}
		}
		else
		{
			// Assume that this is is server to server notification, so no-redirect will be performed,
			// just log the data for debugging purpose if log is enabled
			try
			{
				if (isset($response))
				{
					$logData = $response->getData();
				}
				else
				{
					$logData = $_REQUEST;
				}
			}
			catch (\Exception $e)
			{
				$logData = $_REQUEST;
			}

			$this->logGatewayData($logData, $errorMessage);
		}
	}

	/**
	 * Determine whether we need to redirect after payment verification. Actual payment class
	 * can override this to have expected behavior
	 *
	 * @return bool
	 */
	protected function redirectOnPaymentVerify()
	{
		if (JFactory::getApplication()->input->getInt('notify', 0))
		{
			return false;
		}

		return true;
	}

	/**
	 * Get Omnipay Creditcard object use for processing payment
	 *
	 * @param $data
	 *
	 * @return CreditCard
	 */
	protected function getOmnipayCard($data)
	{
		$cardData      = array();
		$fieldMappings = array(
			'first_name'       => 'firstName',
			'last_name'        => 'lastName',
			'organization'     => 'company',
			'address'          => 'address1',
			'address2'         => 'address2',
			'city'             => 'city',
			'zip'              => 'postcode',
			'state'            => 'state',
			'country'          => 'country',
			'phone'            => 'phone',
			'fax'              => 'fax',
			'email'            => 'email',
			'x_card_num'       => 'number',
			'exp_month'        => 'expiryMonth',
			'exp_year'         => 'expiryYear',
			'x_card_code'      => 'cvv',
			'card_holder_name' => 'name'
		);

		foreach ($fieldMappings as $field => $omnipayField)
		{
			if (!empty($data[$field]))
			{
				$cardData[$omnipayField] = $data[$field];
			}
		}

		return new CreditCard($cardData);
	}
}