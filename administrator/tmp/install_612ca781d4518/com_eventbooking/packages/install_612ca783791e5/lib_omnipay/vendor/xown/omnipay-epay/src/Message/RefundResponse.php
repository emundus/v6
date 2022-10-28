<?php

namespace Omnipay\Epay\Message;

use Omnipay\Common\Message\AbstractResponse;

/**
 * Epay Refund Response
 */
class RefundResponse extends AbstractResponse
{
    public function isSuccessful()
    {
        $data = $this->getData();
        return isset($data['creditResult']) && $data['creditResult'];
    }
}
