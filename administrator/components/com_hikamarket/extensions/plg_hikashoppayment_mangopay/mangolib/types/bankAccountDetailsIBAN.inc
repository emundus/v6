<?php
namespace MangoPay;

/**
 * Class represents IBAN bank account type for in BankAccount entity
 */
class BankAccountDetailsIBAN extends Dto implements BankAccountDetails {
    
    /**
     * IBAN number
     * @var string
     */
    public $IBAN;
    
    /**
     * BIC
     * @var string 
     */
    public $BIC;
}