<?php

namespace Omnipay\BluePay\Message;

/**
 * BluePay Auth Request
 */
class AuthRequest extends AbstractRequest
{
    protected $action = 'AUTH';

    public function getData()
    {
        $this->getCard()->validate();

        $data = $this->getBaseData();
        $data['PAYMENT_ACCOUNT'] = $this->getCard()->getNumber();
        $data['CARD_EXPIRE'] = $this->getCard()->getExpiryDate('my');
        $data['CARD_CVV2'] = $this->getCard()->getCvv();

        return array_merge($data, $this->getBillingData());
    }
}
