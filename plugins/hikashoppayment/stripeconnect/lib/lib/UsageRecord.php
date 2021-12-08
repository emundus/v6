<?php

namespace Stripe;

class UsageRecord extends ApiResource
{

    const OBJECT_NAME = "usage_record";

    public static function create($params = null, $options = null)
    {
        self::_validateParams($params);
        if (!array_key_exists('subscription_item', $params)) {
            throw new Error\InvalidRequest("Missing subscription_item param in request", null);
        }
        $subscription_item = $params['subscription_item'];
        $url = "/v1/subscription_items/$subscription_item/usage_records";
        $request_params = $params;
        unset($request_params['subscription_item']);

        list($response, $opts) = static::_staticRequest('post', $url, $request_params, $options);
        $obj = \Stripe\Util\Util::convertToStripeObject($response->json, $opts);
        $obj->setLastResponse($response);
        return $obj;
    }
}
