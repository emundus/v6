<?php 

    namespace Omnipay\PayflowExtended\Message;

    use Omnipay\Payflow\Message\Response;

    class RecurringProfileAddResponse extends Response 
    {    	
    	// this is the optional sale or authorization transaction
        public function isSuccessful()
        {
            return isset($this->data['TRXRESULT']) && '0' === $this->data['TRXRESULT'];
        }

        // this is the optional sale or authorization transaction
        public function getTransactionReference()
        {
            return isset($this->data['TRXPNREF']) ? $this->data['TRXPNREF'] : null;
        }

    	// this is the optional sale or authorization transaction
        public function getMessage()
        {
            return isset($this->data['RESPMSG']) ? $this->data['RESPMSG'] : null;   
        }

        // this is for the profile action
        public function isProfileActionSuccessful()
        {
            return isset($this->data['RESULT']) && '0' === $this->data['RESULT'];
        }

        // this is for the profile action
    	public function getProfileReference() 
        {
            return isset($this->data['RPREF']) ? $this->data['RPREF'] : null;		
    	}

        // this is for the profile action
    	public function getProfileID() 
        {
            return isset($this->data['PROFILEID']) ? $this->data['PROFILEID'] : null;		
    	}
    }
