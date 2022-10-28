<?php

namespace Omnipay\BluePay\Message;

/**
 * BluePay Refund Request
 */
class RefundRequest extends AbstractRequest
{
    protected $action = 'REFUND';

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
