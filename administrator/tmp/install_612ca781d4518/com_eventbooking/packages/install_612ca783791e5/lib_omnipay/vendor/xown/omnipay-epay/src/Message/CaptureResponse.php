<?php

namespace Omnipay\Epay\Message;

use Omnipay\Common\Message\AbstractResponse;

/**
 * Epay Capture Response
 */
class CaptureResponse extends AbstractResponse
{
    public function isSuccessful()
    {
        $data = $this->getData();
        return isset($data['captureResult']) && $data['captureResult'];
    }
}
