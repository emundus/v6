<?php

namespace Omnipay\RedSys\Message;

use Omnipay\Common\Message\AbstractRequest;
use Omnipay\RedSys\Encoder;
use Omnipay\RedSys\Signer;

abstract class RequestBase extends AbstractRequest
{
    protected $liveEndpoint = 'https://sis.redsys.es/sis/realizarPago';

    protected $testEndpoint = 'https://sis-t.redsys.es:25443/sis/realizarPago';

    /**
     * @var \Omnipay\RedSys\Encoder
     */
    private $encoder;

    /**
     * @var \Omnipay\RedSys\Signer
     */
    private $signer;

    public function getEncoder()
    {
        if (!isset($this->encoder)) {
            $this->encoder = new Encoder();
        }
        return $this->encoder;
    }

    public function setEncoder(Encoder $encoder)
    {
        $this->encoder = $encoder;
    }

    public function getSigner()
    {
        if (!isset($this->signer)) {
            $this->validate('secretKey');
            $this->signer = new Signer($this->getParameter('secretKey'));
        }
        return $this->signer;
    }

    public function setSigner(Signer $signer)
    {
        $this->signer = $signer;
    }

    public function getEndpoint()
    {
        return $this->getTestMode() ? $this->testEndpoint : $this->liveEndpoint;
    }
}
