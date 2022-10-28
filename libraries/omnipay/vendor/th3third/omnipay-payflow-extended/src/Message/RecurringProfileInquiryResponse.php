<?php 

    namespace Omnipay\PayflowExtended\Message;

    use Omnipay\Payflow\Message\Response;

    class RecurringProfileInquiryResponse extends Response 
    {    	
        protected $card;

        public function isSuccessful()
        {
            return isset($this->data['RESULT']) && '0' === $this->data['RESULT'];
        }

    	public function getCard() 
        {
            if (!$this->card)
            {
                $this->card = new CreditCard;
                $this->card->setNumber($this->data['ACCT']);
            }

            return $this->card;	
    	}

        public function getStart()
        {
            return isset($this->data['START']) ? $this->data['START'] : null;
        }
    }
