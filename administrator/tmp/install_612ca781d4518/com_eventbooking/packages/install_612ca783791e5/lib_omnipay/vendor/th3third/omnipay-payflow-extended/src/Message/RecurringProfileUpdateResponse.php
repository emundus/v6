<?php 

    namespace Omnipay\PayflowExtended\Message;

    use Omnipay\Payflow\Message\Response;

    class RecurringProfileUpdateResponse extends Response 
    {    	
        protected $card;

        public function isSuccessful()
        {
            return isset($this->data['RESULT']) && '0' === $this->data['RESULT'];
        }
    }
