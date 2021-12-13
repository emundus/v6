<?php
namespace MangoPay;

/**
 * Wallet entity
 */
class Wallet extends EntityBase {
    
    /**
     * Array with owners identities
     * @var array
     */
    public $Owners;
    
    /**
     * Wallet description
     * @var string 
     */
    public $Description;
    
    /**
     * Money in wallet
     * @var Money 
     */
    public $Balance;
    
    /**
     * Currency code in ISO
     * @var string
     */
    public $Currency;
    
    /**
     * Get array with mapping which property is object and what type of object 
     * @return array
     */
    public function GetSubObjects() {
        return array( 'Balance' => '\MangoPay\Money' );
    }
    
    /**
     * Get array with read-only properties
     * @return array
     */
    public function GetReadOnlyProperties() {
        $properties = parent::GetReadOnlyProperties();
        array_push( $properties, 'Balance' );
        
        return $properties;
    }
}
