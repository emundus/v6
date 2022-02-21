<?php
namespace MangoPay;

/**
 * Class represents OTHER bank account type for in BankAccount entity
 */
class BankAccountDetailsOTHER extends Dto implements BankAccountDetails {
    
    /**
     * Type  
     * @var string
     */
    public $Type;
    
    /**
     * The Country associate to the BankAccount, 
     * ISO 3166-1 alpha-2 format is expected
     * @var string 
     */
    public $Country;
    
    /**
     * Valid BIC format
     * @var string
     */
    public $BIC;
    
    /**
     * Account number 
     * @var string 
     */
    public $AccountNumber;
}