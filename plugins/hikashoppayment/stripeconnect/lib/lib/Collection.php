<?php

namespace Stripe;

class Collection extends StripeObject implements \IteratorAggregate
{

    const OBJECT_NAME = "list";

    use ApiOperations\Request;

    protected $_requestParams = [];

    public static function baseUrl()
    {
        return Stripe::$apiBase;
    }

    public function setRequestParams($params)
    {
        $this->_requestParams = $params;
    }

    public function all($params = null, $opts = null)
    {
        list($url, $params) = $this->extractPathAndUpdateParams($params);

        list($response, $opts) = $this->_request('get', $url, $params, $opts);
        $this->_requestParams = $params;
        return Util\Util::convertToStripeObject($response, $opts);
    }

    public function create($params = null, $opts = null)
    {
        list($url, $params) = $this->extractPathAndUpdateParams($params);

        list($response, $opts) = $this->_request('post', $url, $params, $opts);
        $this->_requestParams = $params;
        return Util\Util::convertToStripeObject($response, $opts);
    }

    public function retrieve($id, $params = null, $opts = null)
    {
        list($url, $params) = $this->extractPathAndUpdateParams($params);

        $id = Util\Util::utf8($id);
        $extn = urlencode($id);
        list($response, $opts) = $this->_request(
            'get',
            "$url/$extn",
            $params,
            $opts
        );
        $this->_requestParams = $params;
        return Util\Util::convertToStripeObject($response, $opts);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->data);
    }

    public function autoPagingIterator()
    {
        return new Util\AutoPagingIterator($this, $this->_requestParams);
    }

    private function extractPathAndUpdateParams($params)
    {
        $url = parse_url($this->url);
        if (!isset($url['path'])) {
            throw new Error\Api("Could not parse list url into parts: $url");
        }

        if (isset($url['query'])) {
            $query = [];
            parse_str($url['query'], $query);
            $params = array_merge($params ?: [], $query);
        }

        return [$url['path'], $params];
    }
}
