<?php

namespace Omnipay\PlugNPay\Message;

/**
 * Class AbstractTransactionAdminRequest
 *
 * @package Omnipay\PlugNPay\Message
 */
abstract class AbstractTransactionAdminRequest extends AbstractRequest
{

    /**
     * Get the data needed for all transaction admin requests.
     *
     * @return array
     */
    public function getData()
    {
        $this->validate('transactionReference', 'amount');

        $baseTransactionAdminData = [
            'orderID'=>$this->getTransactionReference(),
            'card-amount'=>$this->getAmount()
        ];

        return array_merge($this->getBaseData(), $baseTransactionAdminData);
    }
}
