<?php
namespace MangoPay;

/**
 * Class represents BankWire type for mean of payment in PayOut entity
 */
class PayOutPaymentDetailsBankWire extends Dto implements PayOutPaymentDetails {
    
    /**
     * Bank account Id
     * @var string
     */
    public $BankAccountId;
    
    /**
     * A custom reference you wish to appear on the user’s bank statement 
     * @var string
     */
    public $BankWireRef;
}
