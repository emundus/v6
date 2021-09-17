<?php

namespace Omnipay\Epay\Message;

use Omnipay\Common\Message\ResponseInterface;

/**
 * Epay Refund Request
 */
class RefundRequest extends CaptureRequest
{
    protected $endpoint = 'https://ssl.ditonlinebetalingssystem.dk/remote/payment.asmx';



    public function getData()
    {
        $this->validate('merchantnumber', 'amount', 'transactionid');

        $data = array();
        foreach($this->getSupportedKeys() as $key) {
            $value = $this->parameters->get($key);
            if (!empty($value)) {
                $data[$key] = $value;
            }
        }

        /** Hack from SOAP description */
        $data['pbsresponse'] = -1;
        $data['epayresponse'] = -1;

        return $data;
    }

    /**
     * @param mixed $data
     * @return CaptureResponse
     */
    public function sendData($data)
    {
        $client = new \SoapClient($this->endpoint.'?WSDL');
        $result = $client->credit($data);


        return $this->response = new RefundResponse($this, array(
            'creditResult' => $result->creditResult,
            'pbsResponse' => $result->pbsresponse,
            'epayresponse' => $result->epayresponse,
        ));
    }

    /**
     * Send the request
     *
     * @return ResponseInterface
     */
    public function send()
    {
        return $this->sendData($this->getData());
    }
}
