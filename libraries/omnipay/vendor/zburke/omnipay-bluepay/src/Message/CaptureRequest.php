<?php

namespace Omnipay\BluePay\Message;

/**
 * BluePay Capture Request
 */
class CaptureRequest extends AbstractRequest
{
    protected $action = 'CAPTURE';

    public function getData()
    {
        $data = $this->getBaseData();

        return array_merge($data, $this->getBillingData());
    }
}
