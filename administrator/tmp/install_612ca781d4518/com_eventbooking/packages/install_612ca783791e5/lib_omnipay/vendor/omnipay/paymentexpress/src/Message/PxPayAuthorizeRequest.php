<?php

namespace Omnipay\PaymentExpress\Message;

use SimpleXMLElement;
use Omnipay\Common\Message\AbstractRequest;

/**
 * PaymentExpress PxPay Authorize Request
 */
class PxPayAuthorizeRequest extends AbstractRequest
{
	protected $endpoint = 'https://sec.paymentexpress.com/pxaccess/pxpay.aspx';
	protected $action = 'Auth';

	public function getUsername()
	{
		return $this->getParameter('username');
	}

	public function setUsername($value)
	{
		return $this->setParameter('username', $value);
	}

	public function getPassword()
	{
		return $this->getParameter('password');
	}

	public function setPassword($value)
	{
		return $this->setParameter('password', $value);
	}

	/**
	 * Get the PxPay TxnData1
	 *
	 * Optional free text field that can be used to store information against a
	 * transaction. Returned in the response and can be retrieved from DPS
	 * reports.
	 *
	 * @return mixed
	 */
	public function getTransactionData1()
	{
		return $this->getParameter('transactionData1');
	}

	/**
	 * Set the PxPay TxnData1
	 *
	 * @param string $value Max 255 bytes
	 *
	 * @return mixed
	 */
	public function setTransactionData1($value)
	{
		return $this->setParameter('transactionData1', $value);
	}

	/**
	 * Get the PxPay TxnData2
	 *
	 * Optional free text field that can be used to store information against a
	 * transaction. Returned in the response and can be retrieved from DPS
	 * reports.
	 *
	 * @return mixed
	 */
	public function getTransactionData2()
	{
		return $this->getParameter('transactionData2');
	}

	/**
	 * Set the PxPay TxnData2
	 *
	 * @param string $value Max 255 bytes
	 *
	 * @return mixed
	 */
	public function setTransactionData2($value)
	{
		return $this->setParameter('transactionData2', $value);
	}

	/**
	 * Get the PxPay TxnData3
	 *
	 * Optional free text field that can be used to store information against a
	 * transaction. Returned in the response and can be retrieved from DPS
	 * reports.
	 *
	 * @return mixed
	 */
	public function getTransactionData3()
	{
		return $this->getParameter('transactionData3');
	}

	/**
	 * Set the TxnData3 field on the request
	 *
	 * @param string $value Max 255 bytes
	 *
	 * @return mixed
	 */
	public function setTransactionData3($value)
	{
		return $this->setParameter('transactionData3', $value);
	}

	public function getData()
	{
		$this->validate('amount', 'returnUrl');

		$data                    = new SimpleXMLElement('<GenerateRequest/>');
		$data->PxPayUserId       = $this->getUsername();
		$data->PxPayKey          = $this->getPassword();
		$data->TxnType           = $this->action;
		$data->AmountInput       = $this->getAmount();
		$data->CurrencyInput     = $this->getCurrency();
		$data->MerchantReference = $this->getDescription();
		$data->UrlSuccess        = $this->getReturnUrl();
		$data->UrlFail           = $this->getReturnUrl();
		$data->EmailAddress      = $this->getCard()->getEmail();

		if ($this->getTransactionData1())
		{
			$data->TxnData1 = $this->getTransactionData1();
		}

		if ($this->getTransactionData2())
		{
			$data->TxnData2 = $this->getTransactionData2();
		}

		if ($this->getTransactionData3())
		{
			$data->TxnData3 = $this->getTransactionData3();
		}

		return $data;
	}

	public function sendData($data)
	{
		$httpResponse = $this->httpClient->post($this->endpoint, null, $data->asXML())->send();

		return $this->createResponse($httpResponse->xml());
	}

	protected function createResponse($data)
	{
		return $this->response = new PxPayAuthorizeResponse($this, $data);
	}
}
