<?php
namespace MangoPay;

/**
 * Class represents CA bank account type for in BankAccount entity
 */
class BankAccountDetailsCA extends Dto implements BankAccountDetails {
    
    /**
     * Bank name
     * @var string
     */
    public $BankName;
    
    /**
     * Institution number
     * @var string 
     */
    public $InstitutionNumber;
    
    /**
     * Branch code
     * @var string 
     */
    public $BranchCode;
    
    /**
     * Account number
     * @var string 
     */
    public $AccountNumber;
}