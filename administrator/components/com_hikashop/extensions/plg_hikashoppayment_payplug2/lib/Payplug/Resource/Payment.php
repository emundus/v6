<?php
namespace Payplug\Resource;
use Payplug;

class Payment extends APIResource implements IVerifiableAPIResource
{
    public static function fromAttributes(array $attributes)
    {
        $object = new Payment();
        $object->initialize($attributes);
        return $object;
    }

    protected function initialize(array $attributes)
    {
        parent::initialize($attributes);

        if (isset($attributes['card'])) {
            $this->card = PaymentCard::fromAttributes($attributes['card']);
        }

        if (isset($attributes['customer'])) {
            $this->customer = PaymentCustomer::fromAttributes($attributes['customer']);
        }
        if (isset($attributes['billing'])) {
            $this->billing = PaymentBilling::fromAttributes($attributes['billing']);
        }
        if (isset($attributes['shipping'])) {
            $this->shipping = PaymentShipping::fromAttributes($attributes['shipping']);
        }
        if (isset($attributes['hosted_payment'])) {
            $this->hosted_payment = PaymentHostedPayment::fromAttributes($attributes['hosted_payment']);
        }
        if (isset($attributes['failure'])) {
            $this->failure = PaymentPaymentFailure::fromAttributes($attributes['failure']);
        }
        if (isset($attributes['notification'])) {
            $this->notification = PaymentNotification::fromAttributes($attributes['notification']);
        }
        if (isset($attributes['authorization'])) {
            $this->authorization = PaymentAuthorization::fromAttributes($attributes['authorization']);
        }
    }

    public function refund(array $data = null, Payplug\Payplug $payplug = null)
    {
        if (!array_key_exists('id', $this->getAttributes())) {
            throw new Payplug\Exception\InvalidPaymentException("This payment object has no id. It can't be refunded.");
        }

        return Refund::create($this->id, $data, $payplug);
    }

    public function listRefunds(Payplug\Payplug $payplug = null)
    {
        if (!array_key_exists('id', $this->getAttributes())) {
            throw new Payplug\Exception\InvalidPaymentException("This payment object has no id. You can't list refunds on it.");
        }

        return Refund::listRefunds($this->id, $payplug);
    }

    public function abort(Payplug\Payplug $payplug = null)
    {
        if ($payplug === null) {
            $payplug = Payplug\Payplug::getDefaultConfiguration();
        }

        $httpClient = new Payplug\Core\HttpClient($payplug);
        $response = $httpClient->patch(
            Payplug\Core\APIRoutes::getRoute(Payplug\Core\APIRoutes::PAYMENT_RESOURCE, $this->id),
            array('aborted' => true)
        );

        return Payment::fromAttributes($response['httpResponse']);
    }

    public function capture(Payplug\Payplug $payplug = null)
    {
        if ($payplug === null) {
            $payplug = Payplug\Payplug::getDefaultConfiguration();
        }

        $httpClient = new Payplug\Core\HttpClient($payplug);
        $response = $httpClient->patch(
            Payplug\Core\APIRoutes::getRoute(Payplug\Core\APIRoutes::PAYMENT_RESOURCE, $this->id),
            array('captured' => true)
        );

        return Payment::fromAttributes($response['httpResponse']);
    }

    public static function retrieve($paymentId, Payplug\Payplug $payplug = null)
    {
        if ($payplug === null) {
            $payplug = Payplug\Payplug::getDefaultConfiguration();
        }

        $httpClient = new Payplug\Core\HttpClient($payplug);
        $response = $httpClient->get(
            Payplug\Core\APIRoutes::getRoute(Payplug\Core\APIRoutes::PAYMENT_RESOURCE, $paymentId)
        );

        return Payment::fromAttributes($response['httpResponse']);
    }

    public static function listPayments($perPage = null, $page = null, Payplug\Payplug $payplug = null)
    {
        if ($payplug === null) {
            $payplug = Payplug\Payplug::getDefaultConfiguration();
        }

        $httpClient = new Payplug\Core\HttpClient($payplug);
        $pagination = array('per_page' => $perPage, 'page' => $page);
        $response = $httpClient->get(
            Payplug\Core\APIRoutes::getRoute(Payplug\Core\APIRoutes::PAYMENT_RESOURCE, null, array(), $pagination)
        );

        if (!array_key_exists('data', $response['httpResponse'])
            || !is_array($response['httpResponse']['data'])) {
            throw new Payplug\Exception\UnexpectedAPIResponseException(
                "Expected 'data' key in API response.",
                $response['httpResponse']
            );
        }

        $payments = array();
        foreach ($response['httpResponse']['data'] as &$payment) {
            $payments[] = Payment::fromAttributes($payment);
        }

        return $payments;
    }

    public static function create(array $data, Payplug\Payplug $payplug = null)
    {
        if ($payplug === null) {
            $payplug = Payplug\Payplug::getDefaultConfiguration();
        }

        $httpClient = new Payplug\Core\HttpClient($payplug);
        $response = $httpClient->post(
            Payplug\Core\APIRoutes::getRoute(Payplug\Core\APIRoutes::PAYMENT_RESOURCE),
            $data
        );

        return Payment::fromAttributes($response['httpResponse']);
    }

    public function update(array $data, Payplug\Payplug $payplug = null)
    {
        if ($payplug === null) {
            $payplug = Payplug\Payplug::getDefaultConfiguration();
        }

        $httpClient = new Payplug\Core\HttpClient($payplug);
        $response = $httpClient->patch(
            Payplug\Core\APIRoutes::getRoute(Payplug\Core\APIRoutes::PAYMENT_RESOURCE, $this->id),
            $data
        );

        return Payment::fromAttributes($response['httpResponse']);
    }

    function getConsistentResource(Payplug\Payplug $payplug = null)
    {
        if (!array_key_exists('id', $this->_attributes)) {
            throw new Payplug\Exception\UndefinedAttributeException('The id of the payment is not set.');
        }

        return Payment::retrieve($this->_attributes['id'], $payplug);
    }
}
