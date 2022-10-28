<?php

namespace Omnipay\Epay\Message;

use Omnipay\Common\Message\AbstractResponse;

/**
 * Epay Delete Response
 */
class DeleteResponse extends AbstractResponse
{
    public function isSuccessful()
    {
        $data = $this->getData();
        return isset($data['deleteResult']) && $data['deleteResult'];
    }
}
