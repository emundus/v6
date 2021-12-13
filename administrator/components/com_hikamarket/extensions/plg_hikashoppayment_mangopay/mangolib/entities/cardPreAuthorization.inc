<?php
namespace MangoPay;

/**
 * Pre-authorization entity
 */
class CardPreAuthorization extends EntityBase {
    
    /**
     * The user Id of the author of the pre-authorization
     * @var string
     */
    public $AuthorId;
    
    /**
     * It represents the amount debited on the bank account 
     * of the Author.DebitedFunds = Fees + CreditedFunds 
     * (amount received on wallet)
     *  @var \MangoPay\Money
     */
    public $DebitedFunds;
    
    /**
     * Status of the PreAuthorization: CREATED, SUCCEEDED, FAILED
     * @var string
     */
    public $Status;
    
    /**
     * The status of the payment after the PreAuthorization:
     * WAITING, CANCELED, EXPIRED, VALIDATED
     * @var string 
     */
    public $PaymentStatus;
    
    /**
     * The PreAuthorization result code
     * @var string 
     */
    public $ResultCode;
    
    /**
     * The PreAuthorization result Message explaining the result code
     * @var string 
     */
    public $ResultMessage;
        
    /**
     * How the PreAuthorization has been executed. 
     * Only on value for now: CARD
     * @var string 
     */
    public $ExecutionType;
    
    /**
     * The SecureMode correspond to '3D secure' for CB Visa and MasterCard 
     * or 'Amex Safe Key' for American Express. 
     * This field lets you activate it manually.
     * @var string 
     */
    public $SecureMode;
    
    /**
     * The ID of the registered card (Got through CardRegistration object)
     * @var string 
     */
    public $CardId;
    
    /**
     * Boolean. The value is 'true' if the SecureMode was used
     * @var string 
     */
    public $SecureModeNeeded;
    
    /**
     * This is the URL where to redirect users to proceed 
     * to 3D secure validation
     * @var string 
     */
    public $SecureModeRedirectURL;
    
    /**
     * This is the URL where users are automatically redirected 
     * after 3D secure validation (if activated)
     * @var string 
     */
    public $SecureModeReturnURL;
    
    /**
     * The date when the payment is processed
     * @var Timestamp 
     */
    public $ExpirationDate;
    
     /**
     * The date when the payment was authorized
     * @var Timestamp 
     */
    public $AuthorizationDate;
    
    /**
     * The type of pre-authorization ("CARD" is the only acceptable value at present
     * @var string 
     */
    public $PaymentType;
    
    /**
     * The Id of the associated PayIn
     * @var string 
     */
    public $PayInId;
    
    /**
     * Get array with mapping which property is object and what type of object 
     * @return array
     */
    public function GetSubObjects() {
        return array(
            'DebitedFunds' => '\MangoPay\Money'
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
        array_push( $properties, 'ResultMessage' );
        
        return $properties;
    }
}
