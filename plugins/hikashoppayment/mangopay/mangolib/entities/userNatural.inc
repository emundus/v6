<?php
namespace MangoPay;

/**
 * UserNatural entity
 */
class UserNatural extends User {
    
    /**
     * First name for user
     * @var String
     */
    public $FirstName;
    
     /**
     * Last name for user
     * @var String 
     */
    public $LastName;
    
     /**
     * Address for user
     * @var String 
     */
    public $Address;
    
     /**
     * Date of birth
     * @var Unix timestamp 
     */
    public $Birthday;
    
     /**
     * User's country
     * @var String 
     */
    public $Nationality;
    
     /**
     * Country of residence
     * @var String 
     */
    public $CountryOfResidence;
    
    /**
     * User's occupation
     * @var String 
     */
    public $Occupation;
    
    /**
     * 
     * @var Int 
     */
    public $IncomeRange;
    
    /**
     * 
     * @var String 
     */
    public $ProofOfIdentity;
    
    /**
     * 
     * @var String 
     */
    public $ProofOfAddress;
    
    /**
     * Construct
     */
    function __construct($id = null) {
        parent::__construct($id);
        $this->SetPersonType(PersonType::Natural);
    }
    
    /**
     * Get array with read-only properties
     * @return array
     */
    public function GetReadOnlyProperties() {
        $properties = parent::GetReadOnlyProperties();
        array_push( $properties, 'ProofOfIdentity' );
        array_push( $properties, 'ProofOfAddress' );
        
        return $properties;
    }
}