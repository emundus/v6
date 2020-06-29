<?php
namespace Payplug\Resource;
use Payplug;

class Refund extends APIResource implements IVerifiableAPIResource
{
    public static function fromAttributes(array $attributes)
    {
        $object = new Refund();
        $object->initialize($attributes);
        return $object;
    }

    public static function create($payment, array $data = null, Payplug\Payplug $payplug = null)
    {
        if ($payplug === null) {
            $payplug = Payplug\Payplug::getDefaultConfiguration();
        }
        if ($payment instanceof Payment) {
            $payment = $payment->id;
        }

        $httpClient = new Payplug\Core\HttpClient($payplug);
        $response = $httpClient->post(
            Payplug\Core\APIRoutes::getRoute(Payplug\Core\APIRoutes::REFUND_RESOURCE, null, array('PAYMENT_ID' => $payment)),
            $data
        );

        return Refund::fromAttributes($response['httpResponse']);
    }

    public static function retrieve($payment, $refundId, Payplug\Payplug $payplug = null)
    {
        if ($payplug === null) {
            $payplug = Payplug\Payplug::getDefaultConfiguration();
        }
        if ($payment instanceof Payment) {
            $payment = $payment->id;
        }

        $httpClient = new Payplug\Core\HttpClient($payplug);
        $response = $httpClient->get(
            Payplug\Core\APIRoutes::getRoute(
                Payplug\Core\APIRoutes::REFUND_RESOURCE, $refundId, array('PAYMENT_ID' => $payment)
            )
        );

        return Refund::fromAttributes($response['httpResponse']);
    }

    public static function listRefunds($payment, Payplug\Payplug $payplug = null)
    {
        if ($payplug === null) {
            $payplug = Payplug\Payplug::getDefaultConfiguration();
        }
        if ($payment instanceof Payment) {
            $payment = $payment->id;
        }

        $httpClient = new Payplug\Core\HttpClient($payplug);

        $response = $httpClient->get(
            Payplug\Core\APIRoutes::getRoute(Payplug\Core\APIRoutes::REFUND_RESOURCE, null, array('PAYMENT_ID' => $payment))
        );

        if (!array_key_exists('data', $response['httpResponse']) || !is_array($response['httpResponse']['data'])) {
            throw new Payplug\Exception\UnexpectedAPIResponseException(
                "Expected API response to contain 'data' key referencing an array.",
                $response['httpResponse']
            );
        }

        $refunds = array();
        foreach ($response['httpResponse']['data'] as &$refund) {
            $refunds[] = Refund::fromAttributes($refund);
        }

        return $refunds;
    }

    function getConsistentResource(Payplug\Payplug $payplug = null)
    {
        if (!array_key_exists('id', $this->_attributes)) {
            throw new Payplug\Exception\UndefinedAttributeException('The id of the refund is not set.');
        }
        else if (!array_key_exists('payment_id', $this->_attributes)) {
            throw new Payplug\Exception\UndefinedAttributeException('The payment_id of the refund is not set.');
        }

        return Payplug\Resource\Refund::retrieve($this->_attributes['payment_id'], $this->_attributes['id'], $payplug);
    }
}
