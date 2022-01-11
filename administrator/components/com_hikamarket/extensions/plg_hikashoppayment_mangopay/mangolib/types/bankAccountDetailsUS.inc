<?php
namespace MangoPay;

/**
 * Class represents US bank account type for in BankAccount entity
 */
class BankAccountDetailsUS extends Dto implements BankAccountDetails {
    
    /**
     * Account number
     * @var string
     */
    public $AccountNumber;
    
    /**
     * ABA
     * @var string 
     */
    public $ABA;
}