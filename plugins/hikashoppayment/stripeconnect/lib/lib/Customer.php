<?php

namespace Stripe;

class Customer extends ApiResource
{

    const OBJECT_NAME = "customer";

    use ApiOperations\All;
    use ApiOperations\Create;
    use ApiOperations\Delete;
    use ApiOperations\NestedResource;
    use ApiOperations\Retrieve;
    use ApiOperations\Update;

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

    const PATH_SOURCES = '/sources';

    public function addInvoiceItem($params = null)
    {
        $params = $params ?: [];
        $params['customer'] = $this->id;
        $ii = InvoiceItem::create($params, $this->_opts);
        return $ii;
    }

    public function invoices($params = null)
    {
        $params = $params ?: [];
        $params['customer'] = $this->id;
        $invoices = Invoice::all($params, $this->_opts);
        return $invoices;
    }

    public function invoiceItems($params = null)
    {
        $params = $params ?: [];
        $params['customer'] = $this->id;
        $iis = InvoiceItem::all($params, $this->_opts);
        return $iis;
    }

    public function charges($params = null)
    {
        $params = $params ?: [];
        $params['customer'] = $this->id;
        $charges = Charge::all($params, $this->_opts);
        return $charges;
    }

    public function updateSubscription($params = null)
    {
        $url = $this->instanceUrl() . '/subscription';
        list($response, $opts) = $this->_request('post', $url, $params);
        $this->refreshFrom(['subscription' => $response], $opts, true);
        return $this->subscription;
    }

    public function cancelSubscription($params = null)
    {
        $url = $this->instanceUrl() . '/subscription';
        list($response, $opts) = $this->_request('delete', $url, $params);
        $this->refreshFrom(['subscription' => $response], $opts, true);
        return $this->subscription;
    }

    public function deleteDiscount()
    {
        $url = $this->instanceUrl() . '/discount';
        list($response, $opts) = $this->_request('delete', $url);
        $this->refreshFrom(['discount' => null], $opts, true);
    }

    public static function createSource($id, $params = null, $opts = null)
    {
        return self::_createNestedResource($id, static::PATH_SOURCES, $params, $opts);
    }

    public static function retrieveSource($id, $sourceId, $params = null, $opts = null)
    {
        return self::_retrieveNestedResource($id, static::PATH_SOURCES, $sourceId, $params, $opts);
    }

    public static function updateSource($id, $sourceId, $params = null, $opts = null)
    {
        return self::_updateNestedResource($id, static::PATH_SOURCES, $sourceId, $params, $opts);
    }

    public static function deleteSource($id, $sourceId, $params = null, $opts = null)
    {
        return self::_deleteNestedResource($id, static::PATH_SOURCES, $sourceId, $params, $opts);
    }

    public static function allSources($id, $params = null, $opts = null)
    {
        return self::_allNestedResources($id, static::PATH_SOURCES, $params, $opts);
    }
}
