<?php

    namespace Omnipay\PayflowExtended;

    use Omnipay\Payflow\ProGateway;

    /**
    * Payflow Pro Extended class
    *
    * @author Marshall Miller
    * @link https://www.x.com/sites/default/files/payflowgateway_guide.pdf
    */
    class Gateway extends ProGateway
    {
        public function getName()
        {
            return 'PayflowExtended';
        }

        public function addRecurringProfile(Array $parameters = array()) 
        {
            return $this->createRequest('\Omnipay\PayflowExtended\Message\RecurringProfileAddRequest', $parameters);
        }

        public function recurringProfileInquiry(Array $parameters = array())
        {
            return $this->createRequest('\Omnipay\PayflowExtended\Message\RecurringProfileInquiryRequest', $parameters);
        }

        public function deactivateRecurringProfile(Array $parameters = array())
        {
            return $this->createRequest('\Omnipay\PayflowExtended\Message\RecurringProfileDeactivateRequest', $parameters);
        }

        public function updateRecurringProfile(Array $parameters = array())
        {
            return $this->createRequest('\Omnipay\PayflowExtended\Message\RecurringProfileUpdateRequest', $parameters);
        }
    }