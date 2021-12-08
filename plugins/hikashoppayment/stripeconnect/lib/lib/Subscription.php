<?php

namespace Stripe;

class Subscription extends ApiResource
{

    const OBJECT_NAME = "subscription";

    use ApiOperations\All;
    use ApiOperations\Create;
    use ApiOperations\Delete {
        delete as protected _delete;
    }
    use ApiOperations\Retrieve;
    use ApiOperations\Update;

    const STATUS_ACTIVE             = 'active';
    const STATUS_CANCELED           = 'canceled';
    const STATUS_PAST_DUE           = 'past_due';
    const STATUS_TRIALING           = 'trialing';
    const STATUS_UNPAID             = 'unpaid';
    const STATUS_INCOMPLETE         = 'incomplete';
    const STATUS_INCOMPLETE_EXPIRED = 'incomplete_expired';

    public static function getSavedNestedResources()
    {
        static $savedNestedResources = null;
        if ($savedNestedResources === null) {
            $savedNestedResources = new Util\Set([
                'source',
            ]);
        }
        return $savedNestedResources;
    }

    public function cancel($params = null, $opts = null)
    {
        return $this->_delete($params, $opts);
    }

    public function deleteDiscount()
    {
        $url = $this->instanceUrl() . '/discount';
        list($response, $opts) = $this->_request('delete', $url);
        $this->refreshFrom(['discount' => null], $opts, true);
    }
}
