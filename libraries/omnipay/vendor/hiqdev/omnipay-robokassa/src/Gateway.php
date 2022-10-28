<?php
/**
 * RoboKassa driver for Omnipay PHP payment library.
 *
 * @link      https://github.com/hiqdev/omnipay-robokassa
 * @package   omnipay-robokassa
 * @license   MIT
 * @copyright Copyright (c) 2015-2017, HiQDev (http://hiqdev.com/)
 */

namespace Omnipay\RoboKassa;

use Omnipay\Common\AbstractGateway;

/**
 * Gateway for ePayService.
 */
class Gateway extends AbstractGateway
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'RoboKassa';
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultParameters()
    {
        return [
            'purse' => '',
            'secretKey'     => '',
            'secretKey2'    => '',
            'testMode'      => false,
        ];
    }

    /**
     * Get the unified purse.
     *
     * @return string merchant purse
     */
    public function getPurse()
    {
        return $this->getParameter('purse');
    }

    /**
     * Set the unified purse.
     *
     * @param string $purse merchant purse
     *
     * @return self
     */
    public function setPurse($value)
    {
        return $this->setParameter('purse', $value);
    }

    /**
     * Get the unified secret key.
     *
     * @return string secret key
     */
    public function getSecretKey()
    {
        return $this->getParameter('secretKey');
    }

    /**
     * Set the unified secret key.
     *
     * @param string $value secret key
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

    /**
     * @param array $parameters
     *
     * @return \Omnipay\ePayService\Message\PurchaseRequest
     */
    public function purchase(array $parameters = [])
    {
        return $this->createRequest('\Omnipay\RoboKassa\Message\PurchaseRequest', $parameters);
    }

    /**
     * @param array $parameters
     *
     * @return \Omnipay\ePayService\Message\CompletePurchaseRequest
     */
    public function completePurchase(array $parameters = [])
    {
        return $this->createRequest('\Omnipay\RoboKassa\Message\CompletePurchaseRequest', $parameters);
    }
}
