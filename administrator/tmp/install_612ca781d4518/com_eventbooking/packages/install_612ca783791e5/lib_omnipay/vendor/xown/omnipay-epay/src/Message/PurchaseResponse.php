<?php

namespace Omnipay\Epay\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RedirectResponseInterface;

/**
 * Epay Purchase Response
 */
class PurchaseResponse extends AbstractResponse implements RedirectResponseInterface
{
    protected $endpoint = 'https://ssl.ditonlinebetalingssystem.dk/integration/ewindow/Default.aspx';

    public function isSuccessful()
    {
        return false;
    }

    public function isRedirect()
    {
        return true;
    }

    public function getRedirectUrl()
    {
        return $this->endpoint.'?'.http_build_query($this->data);
    }

    public function getRedirectMethod()
    {
        return 'GET';
    }

    public function getRedirectData()
    {
        return null;
    }
}
