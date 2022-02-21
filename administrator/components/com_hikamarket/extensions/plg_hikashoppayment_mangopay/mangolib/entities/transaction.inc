<?php
namespace MangoPay;

/**
 * Transaction entity.
 * Base class for: PayIn, PayOut, Transfer.
 */
class Transaction extends EntityBase {
    
    /**
     * Author Id
     * @var int
     */
    public $AuthorId;
    
    /**
     * Credited user Id
     * @var int 
     */
    public $CreditedUserId;
    
    /**
     * Debited funds
     * @var \MangoPay\Money
     */
    public $DebitedFunds;
    
    /**
     * Credited funds
     * @var \MangoPay\Money
     */
    public $CreditedFunds;
    
    /**
     * Fees
     * @var \MangoPay\Money
     */
    public $Fees;
    
    /**
     * TransactionStatus {CREATED, SUCCEEDED, FAILED}
     * @var string 
     */
    public $Status;
    
    /**
     * Result code
     * @var string
     */
    public $ResultCode;
    
    /**
     * The PreAuthorization result Message explaining the result code
     * @var string 
     */
    public $ResultMessage;
    
    /**
     * Execution date;
     * @var date
     */
    public $ExecutionDate;
    
    /**
     * TransactionType {PAYIN, PAYOUT, TRANSFER}
     * @var string
     */
    public $Type;
    
    /**
     * TransactionNature { REGULAR, REFUND, REPUDIATION }
     * @var string
     */
    public $Nature;
    
    /**
     * Get array with mapping which property is object and what type of object 
     * @return array
     */
    public function GetSubObjects() {
        return array(
            'DebitedFunds' => '\MangoPay\Money' ,
            'CreditedFunds' => '\MangoPay\Money' ,
            'Fees' => '\MangoPay\Money'
        );
    }
    
    /**
     * Get array with read-only properties
     * @return array
     */
    public function GetReadOnlyProperties() {
        $properties = parent::GetReadOnlyProperties();
        array_push( $properties, 'Status' );
        array_push( $properties, 'ResultCode' );
        array_push( $properties, 'ExecutionDate' );
        
        return $properties;
    }
}
