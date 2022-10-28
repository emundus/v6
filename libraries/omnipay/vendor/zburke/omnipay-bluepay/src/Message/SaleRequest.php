<?php

namespace Omnipay\BluePay\Message;

/**
 * BluePay Sale Request
 */
class SaleRequest extends AbstractRequest
{
    protected $action = 'SALE';

    public function getData()
    {
        $data = $this->getBaseData();

        if ($card = $this->getCard()) {
            $card->validate();
            $data['PAYMENT_ACCOUNT'] = $this->getCard()->getNumber();
            $data['CARD_EXPIRE'] = $this->getCard()->getExpiryDate('my');
            $data['CARD_CVV2'] = $this->getCard()->getCvv();
        }

        return array_merge($data, $this->getBillingData());
    }
}
