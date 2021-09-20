<?php 

    namespace Omnipay\PayflowExtended\Message;

    use Omnipay\Payflow\Message\AuthorizeRequest;

    /**
     * Payflow Recurring Billing Add Profile Request
     */
    class RecurringProfileAddRequest extends AuthorizeRequest
    {
        protected $trxtype = 'R'; 
        protected $action = 'A'; // A-Add, M-Modify, R-Reactivate, C-Cancel, I-Inquiry, P-Retry failed pmt

        public function getProfileName()
        {
            return $this->getParameter('profileName');
        }

        public function setProfileName($value)
        {
            return $this->setParameter('profileName', $value);
        }
       
        public function getStartDate()
        {
            return $this->getParameter('startDate');
        }

        // MMDDYYYY
        public function setStartDate($value) 
        {
            return $this->setParameter('startDate', $value);
        }

        public function getTerm()
        {
            return $this->getParameter('term');
        }

        public function setTerm($value)
        {
            return $this->setParameter('term', $value);
        }

        public function getPayPeriod()
        {
            return $this->getParameter('payPeriod');
        }
        // SMMO = 2x month, MONT = Monthly, BIWK = every 2 weeks, WEEK = weekly
        public function setPayPeriod($value)
        {
            return $this->setParameter('payPeriod', $value);
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
            $this->validate('amount', 'card');
            $this->getCard()->validate();

            $data = $this->getBaseData();
            $data['PROFILENAME'] = $this->getProfileName();

            $data['ACCT'] = $this->getCard()->getNumber();
            $data['EXPDATE'] = $this->getCard()->getExpiryDate('my');
            $data['CVV2'] = $this->getCard()->getCvv();
            $data['OPTIONALTRX'] = 'A'; // this is an authorization only, S = actual sale (require OPTIONALTRXAMT)
       
            $data['AMT'] = $this->getAmount();
            $data['START'] = $this->getStartDate(); 
            $data['TERM'] = $this->getTerm(); // # payments
            $data['PAYPERIOD'] = $this->getPayPeriod(); 
            $data['COMMENT1'] = $this->getComment1();
            //$data['COMMENT2'] = $this->getComment2();
           
            $data['EMAIL'] = $this->getCard()->getEmail();
            $data['FIRSTNAME'] = $this->getCard()->getFirstName();
            $data['LASTNAME'] = $this->getCard()->getLastName();
            $data['STREET'] = $this->getCard()->getAddress1();
            $data['CITY'] = $this->getCard()->getCity();
            $data['STATE'] = $this->getCard()->getState();
            $data['ZIP'] = $this->getCard()->getPostcode();
            $data['COUNTRY'] = $this->getCard()->getCountry();
            
            return $data;
        }

        public function sendData($data)
        {
            $httpResponse = $this->httpClient->post($this->getEndpoint(), null, $this->encodeData($data))->send();

            return $this->response = new RecurringProfileAddResponse($this, $httpResponse->getBody());
        }


    }