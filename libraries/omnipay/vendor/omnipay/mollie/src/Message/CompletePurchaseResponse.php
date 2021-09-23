<?php

namespace Omnipay\Mollie\Message;

class CompletePurchaseResponse extends FetchTransactionResponse
{
	/**
	 * {@inheritdoc}
	 */
	public function isSuccessful()
	{
		return parent::isSuccessful() && $this->isPaid();
	}

	public function getTransactionId()
	{
		if (isset($this->data['metadata']['order_id']))
		{
			return $this->data['metadata']['order_id'];
		}

		return parent::getTransactionId();
	}
}
