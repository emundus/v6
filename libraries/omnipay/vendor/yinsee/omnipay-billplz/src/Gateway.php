<?php
namespace Omnipay\Billplz;

use Omnipay\Common\AbstractGateway;

class Gateway extends AbstractGateway
{

  /**
   * get the name of your processor. This will be the name used w
   * @return string
   */
    public function getName()
    {
        return 'Billplz';
    }

  /**
   * declare the parameters that will be used to authenticate with the site
   * You will need to create a function for each of these. e.g getUsername for username
   * @return array
   */
    public function getDefaultParameters()
    {
        return array(
            'apikey' => '',
            'collectionId' => '',
            'testMode' => false,
        );
    }

    /**
     *
     * @param array $parameters
     * @return \Omnipay\Billplz\Message\AuthorizeRequest
     */
    // public function authorize(array $parameters = array())
    // {
    //     return $this->createRequest('\Omnipay\Billplz\Message\AuthorizeRequest', $parameters);
    // }

    /**
     *
     * @param array $parameters
     * @return \Omnipay\Billplz\Message\CaptureRequest
     */
    // public function capture(array $parameters = array())
    // {
    //     return $this->createRequest('\Omnipay\Billplz\Message\CaptureRequest', $parameters);
    // }

    /**
     *
     * @param array $parameters
     * @return \Omnipay\Billplz\Message\PurchaseRequest
     */
    public function purchase(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Billplz\Message\PurchaseRequest', $parameters);
    }

    /**
     *
     * @param array $parameters
     * @return \Omnipay\Billplz\Message\CompletePurchaseRequest
     */
    public function completePurchase(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Billplz\Message\CompletePurchaseRequest', $parameters);
    }

    /**
     * @param array $parameters
     * @return \Omnipay\Billplz\Message\CompleteAuthorizeRequest
     */
    // public function completeAuthorize(array $parameters = array())
    // {
    //     return $this->createRequest('\Omnipay\Billplz\Message\CompleteAuthorizeRequest', $parameters);
    // }
    public function getCollectionId()
    {
        return $this->getParameter('collectionId');
    }

    public function setCollectionId($value)
    {
        return $this->setParameter('collectionId', $value);
    }

    public function getAPIKey()
    {
        return $this->getParameter('apikey');
    }

    public function setAPIKey($value)
    {
        return $this->setParameter('apikey', $value);
    }
}
