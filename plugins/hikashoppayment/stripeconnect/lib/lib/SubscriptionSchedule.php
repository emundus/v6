<?php

namespace Stripe;

class SubscriptionSchedule extends ApiResource
{

    const OBJECT_NAME = "subscription_schedule";

    use ApiOperations\All;
    use ApiOperations\Create;
    use ApiOperations\Retrieve;
    use ApiOperations\Update;
    use ApiOperations\NestedResource;

    const PATH_REVISIONS = '/revisions';

    public function cancel($params = null, $opts = null)
    {
        $url = $this->instanceUrl() . '/cancel';
        list($response, $opts) = $this->_request('post', $url, $params, $opts);
        $this->refreshFrom($response, $opts);
        return $this;
    }

    public function release($params = null, $opts = null)
    {
        $url = $this->instanceUrl() . '/release';
        list($response, $opts) = $this->_request('post', $url, $params, $opts);
        $this->refreshFrom($response, $opts);
        return $this;
    }

    public function revisions($params = null, $options = null)
    {
        $url = $this->instanceUrl() . '/revisions';
        list($response, $opts) = $this->_request('get', $url, $params, $options);
        $obj = Util\Util::convertToStripeObject($response, $opts);
        $obj->setLastResponse($response);
        return $obj;
    }

    public static function retrieveRevision($id, $personId, $params = null, $opts = null)
    {
        return self::_retrieveNestedResource($id, static::PATH_REVISIONS, $personId, $params, $opts);
    }

    public static function allRevisions($id, $params = null, $opts = null)
    {
        return self::_allNestedResources($id, static::PATH_REVISIONS, $params, $opts);
    }
}
