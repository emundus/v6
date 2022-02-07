<?php

namespace Stripe\ApiOperations;

trait NestedResource
{
    protected static function _nestedResourceOperation($method, $url, $params = null, $options = null)
    {
        self::_validateParams($params);

        list($response, $opts) = static::_staticRequest($method, $url, $params, $options);
        $obj = \Stripe\Util\Util::convertToStripeObject($response->json, $opts);
        $obj->setLastResponse($response);
        return $obj;
    }

    protected static function _nestedResourceUrl($id, $nestedPath, $nestedId = null)
    {
        $url = static::resourceUrl($id) . $nestedPath;
        if ($nestedId !== null) {
            $url .= "/$nestedId";
        }
        return $url;
    }

    protected static function _createNestedResource($id, $nestedPath, $params = null, $options = null)
    {
        $url = static::_nestedResourceUrl($id, $nestedPath);
        return self::_nestedResourceOperation('post', $url, $params, $options);
    }

    protected static function _retrieveNestedResource($id, $nestedPath, $nestedId, $params = null, $options = null)
    {
        $url = static::_nestedResourceUrl($id, $nestedPath, $nestedId);
        return self::_nestedResourceOperation('get', $url, $params, $options);
    }

    protected static function _updateNestedResource($id, $nestedPath, $nestedId, $params = null, $options = null)
    {
        $url = static::_nestedResourceUrl($id, $nestedPath, $nestedId);
        return self::_nestedResourceOperation('post', $url, $params, $options);
    }

    protected static function _deleteNestedResource($id, $nestedPath, $nestedId, $params = null, $options = null)
    {
        $url = static::_nestedResourceUrl($id, $nestedPath, $nestedId);
        return self::_nestedResourceOperation('delete', $url, $params, $options);
    }

    protected static function _allNestedResources($id, $nestedPath, $params = null, $options = null)
    {
        $url = static::_nestedResourceUrl($id, $nestedPath);
        return self::_nestedResourceOperation('get', $url, $params, $options);
    }
}
