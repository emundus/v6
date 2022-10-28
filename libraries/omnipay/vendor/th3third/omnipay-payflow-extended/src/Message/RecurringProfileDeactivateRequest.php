<?php 

    namespace Omnipay\PayflowExtended\Message;

    use Omnipay\Payflow\Message\AuthorizeRequest;

    /**
     * Payflow Recurring Billing Add Profile Request
     */
    class RecurringProfileDeactivateRequest extends AuthorizeRequest
    {
        protected $trxtype = 'R'; 
        protected $action = 'C'; // A-Add, M-Modify, R-Reactivate, C-Cancel, I-Inquiry, P-Retry failed pmt

        public function getProfileID()
        {
            return $this->getParameter('profileID');
        }

        public function setProfileID($value)
        {
            return $this->setParameter('profileID', $value);
        }

        protected function getBaseData()
        {
            $data = array();
            $data['TRXTYPE'] = $this->trxtype;
            $data['USER'] = $this->getUsername();
            $data['PWD'] = $this->getPassword();
            $data['VENDOR'] = $this->getVendor();
            $data['PARTNER'] = $this->getPartner();
            $data['ACTION'] = $this->action;
            $data['TENDER'] = 'C';

            return $data;
        }

        public function getData()
        {
            $data = $this->getBaseData();
            $data['ORIGPROFILEID'] = $this->getProfileID();
            
            return $data;
        }

        public function sendData($data)
        {
            $httpResponse = $this->httpClient->post($this->getEndpoint(), null, $this->encodeData($data))->send();

            return $this->response = new RecurringProfileDeactivateResponse($this, $httpResponse->getBody());
        }


    }