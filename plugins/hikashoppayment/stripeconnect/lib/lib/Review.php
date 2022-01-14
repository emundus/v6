<?php

namespace Stripe;

class Review extends ApiResource
{
    const OBJECT_NAME = "review";

    use ApiOperations\All;
    use ApiOperations\Retrieve;

    const REASON_APPROVED          = 'approved';
    const REASON_DISPUTED          = 'disputed';
    const REASON_MANUAL            = 'manual';
    const REASON_REFUNDED          = 'refunded';
    const REASON_REFUNDED_AS_FRAUD = 'refunded_as_fraud';
    const REASON_RULE              = 'rule';

    public function approve($params = null, $options = null)
    {
        $url = $this->instanceUrl() . '/approve';
        list($response, $opts) = $this->_request('post', $url, $params, $options);
        $this->refreshFrom($response, $opts);
        return $this;
    }
}
