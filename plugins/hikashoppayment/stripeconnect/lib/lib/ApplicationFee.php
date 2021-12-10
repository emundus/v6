<?php

namespace Stripe;

class ApplicationFee extends ApiResource
{

    const OBJECT_NAME = "application_fee";

    use ApiOperations\All;
    use ApiOperations\NestedResource;
    use ApiOperations\Retrieve;

    const PATH_REFUNDS = '/refunds';

    public function refund($params = null, $opts = null)
    {
        $this->refunds->create($params, $opts);
        $this->refresh();
        return $this;
    }

    public static function createRefund($id, $params = null, $opts = null)
    {
        return self::_createNestedResource($id, static::PATH_REFUNDS, $params, $opts);
    }

    public static function retrieveRefund($id, $refundId, $params = null, $opts = null)
    {
        return self::_retrieveNestedResource($id, static::PATH_REFUNDS, $refundId, $params, $opts);
    }

    public static function updateRefund($id, $refundId, $params = null, $opts = null)
    {
        return self::_updateNestedResource($id, static::PATH_REFUNDS, $refundId, $params, $opts);
    }

    public static function allRefunds($id, $params = null, $opts = null)
    {
        return self::_allNestedResources($id, static::PATH_REFUNDS, $params, $opts);
    }
}
