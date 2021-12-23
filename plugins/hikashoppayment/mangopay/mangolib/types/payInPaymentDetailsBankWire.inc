<?php
namespace MangoPay;

/**
 * Class represents BankWire type for mean of payment in PayIn entity
 */
class PayInPaymentDetailsBankWire extends Dto implements PayInPaymentDetails {
    
    /**
     * Declared debited funds
     * @var \MangoPay\Money
     */
    public $DeclaredDebitedFunds;

    /**
     * Declared fees
     * @var \MangoPay\Money
     */
    public $DeclaredFees;

    /**
     * Bank account details
     * @var \MangoPay\BankAccount
     */
    public $BankAccount;
    
    /**
     * Wire reference
     * @var string 
     */
    public $WireReference;
    
    /**
     * Get array with mapping which property is object and what type of object 
     * @return array
     */
    public function GetSubObjects() {
        return array(
            'DeclaredDebitedFunds' => '\MangoPay\Money' ,
            'DeclaredFees' => '\MangoPay\Money' ,
            'BankAccount' => '\MangoPay\BankAccount'
        );
    }
}
