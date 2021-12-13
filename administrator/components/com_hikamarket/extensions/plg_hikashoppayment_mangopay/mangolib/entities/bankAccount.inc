<?php
namespace MangoPay;

/**
 * Bank Account entity
 */
class BankAccount extends EntityBase {
    
    /**
     * User identifier
     * @var LeetchiId
     */
    public $UserId;
    
    /**
     * Type of bank account
     * @var string 
     */
    public $Type;
    
    /**
     * Owner name
     * @var string 
     */
    public $OwnerName;
    
    /**
     * Owner address
     * @var string 
     */
    public $OwnerAddress;
    
     /**
     * One of BankAccountDetails implementations, depending on $Type
     * @var object 
     */
    public $Details;
    
    /**
     * Get array with mapping which property depends on other property  
     * @return array
     */
    public function GetDependsObjects() {
        return array(
            'Type' => array(
                '_property_name' => 'Details',
                'IBAN' => '\MangoPay\BankAccountDetailsIBAN',
                'GB' => '\MangoPay\BankAccountDetailsGB',
                'US' => '\MangoPay\BankAccountDetailsUS',
                'CA' => '\MangoPay\BankAccountDetailsCA',
                'OTHER' => '\MangoPay\BankAccountDetailsOTHER',
            )
        );
    }
    
    /**
     * Get array with read-only properties
     * @return array
     */
    public function GetReadOnlyProperties() {
        $properties = parent::GetReadOnlyProperties();
        array_push( $properties, 'UserId' );
        array_push( $properties, 'Type' );
        return $properties;
    }
}
