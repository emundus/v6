<?php 

    namespace Omnipay\PayflowExtended\Message;

    use Omnipay\Payflow\Message\AuthorizeRequest;

    /**
     * Payflow Recurring Billing Add Profile Request
     */
    class RecurringProfileUpdateRequest extends AuthorizeRequest
    {
        protected $trxtype = 'R'; 
        protected $action = 'M'; // A-Add, M-Modify, R-Reactivate, C-Cancel, I-Update, P-Retry failed pmt

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

            if (!empty($this->getAmount()))
            {
                $data['AMT'] = $this->getAmount();
            }

            if (!empty($this->getTransactionReference()))
            {
                $data['ORIGID'] = $this->getTransactionReference();
            }
            
            return $data;
        }

        public function sendData($data)
        {
            $httpResponse = $this->httpClient->post($this->getEndpoint(), null, $this->encodeData($data))->send();

            return $this->response = new RecurringProfileUpdateResponse($this, $httpResponse->getBody());
        }


    }