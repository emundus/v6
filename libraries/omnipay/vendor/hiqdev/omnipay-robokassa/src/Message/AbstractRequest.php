<?php
/**
 * RoboKassa driver for Omnipay PHP payment library.
 *
 * @link      https://github.com/hiqdev/omnipay-robokassa
 * @package   omnipay-robokassa
 * @license   MIT
 * @copyright Copyright (c) 2015-2017, HiQDev (http://hiqdev.com/)
 */

namespace Omnipay\RoboKassa\Message;

use Omnipay\Common\Exception\InvalidRequestException;

/**
 * RoboKassa Abstract Request.
 */
abstract class AbstractRequest extends \Omnipay\Common\Message\AbstractRequest
{
    protected $zeroAmountAllowed = false;

    /**
     * Get the InvId
     *
     * @return integer invid. Defaults to 0 (zero)
     */
    public function getInvId()
    {
        return $this->getParameter('InvId') ?? 0;
    }

    /**
     * Set the InvId value.
     *
     * @param integer $invid invid
     *
     * @return self
     */
    public function setInvId($value)
    {
        if (!is_numeric($value)) {
            throw new InvalidRequestException('Property InvId must be numeric');
        }

        return $this->setParameter('InvId', $value);
    }

    /**
     * Get the purse.
     *
     * @return string purse
     */
    public function getPurse()
    {
        return $this->getParameter('purse');
    }

    /**
     * Set the purse.
     *
     * @param string $purse purse
     *
     * @return self
     */
    public function setPurse($value)
    {
        return $this->setParameter('purse', $value);
    }

    /**
     * @return string
     */
    public function getClient()
    {
        return $this->getParameter('client');
    }

    /**
     * @param $value
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setClient($value)
    {
        return $this->setParameter('client', $value);
    }

    /**
     * @return string
     */
    public function getReceipt()
    {
        return rawurlencode($this->getParameter('receipt'));
    }

    /**
     * @param $value
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setReceipt($value)
    {
        return $this->setParameter('receipt', $value);
    }

    public function getCurrency()
    {
        $currency = $this->getParameter('currency');
        if ($currency === 'RUB') {
            return '';
        }
        
        return $currency;
    }

    /**
     * Get the payment currency label.
     *
     * @return string
     */
    public function getCurrencyLabel()
    {
        return $this->getParameter('currencyLabel');
    }

    /**
     * @param string $value
     * @return AbstractRequest
     */
    public function setCurrencyLabel($value)
    {
        return $this->setParameter('currencyLabel', $value);
    }

    /**
     * Get the secret key.
     *
     * @return string secret key
     */
    public function getSecretKey()
    {
        return $this->getParameter('secretKey');
    }

    /**
     * Set the secret key.
     *
     * @param string $key secret key
     *
     * @return self
     */
    public function setSecretKey($value)
    {
        return $this->setParameter('secretKey', $value);
    }

    /**
     * Get the secret key for notification signing.
     *
     * @return string secret key
     */
    public function getSecretKey2()
    {
        return $this->getParameter('secretKey2');
    }

    /**
     * Set the secret key for notification signing.
     *
     * @param string $value secret key
     *
     * @return self
     */
    public function setSecretKey2($value)
    {
        return $this->setParameter('secretKey2', $value);
    }

}
