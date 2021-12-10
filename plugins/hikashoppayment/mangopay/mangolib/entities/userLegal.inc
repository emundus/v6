<?php
namespace MangoPay;

/**
 * UserLegal entity
 */
class UserLegal extends User {
    
    /**
     * Name of user
     * @var String
     */
    public $Name;
    
    /**
     * Type for legal user. Possible: ‘BUSINESS’, ’ORGANIZATION’
     * @var String 
     */
    public $LegalPersonType;
    
    /**
     * 
     * @var String 
     */
    public $HeadquartersAddress;
    
    /**
     * 
     * @var String 
     */
    public $LegalRepresentativeFirstName;
    
    /**
     * 
     * @var String 
     */
    public $LegalRepresentativeLastName;
    
    /**
     * 
     * @var String 
     */
    public $LegalRepresentativeAddress;
    
    /**
     * 
     * @var String 
     */
    public $LegalRepresentativeEmail;
    
    /**
     * 
     * @var Unix timestamp 
     */
    public $LegalRepresentativeBirthday;
    
    /**
     * 
     * @var String 
     */
    public $LegalRepresentativeNationality;
    
    /**
     * 
     * @var String 
     */
    public $LegalRepresentativeCountryOfResidence;
    
    /**
     * 
     * @var String 
     */
    public $Statute;
    
    /**
     * 
     * @var String 
     */
    public $ProofOfRegistration;
    
    /**
     * 
     * @var String 
     */
    public $ShareholderDeclaration;
    
    /**
     * Construct
     */
    function __construct($id = null) {
        parent::__construct($id);
        $this->SetPersonType(PersonType::Legal);
    }
    
    /**
     * Get array with read-only properties
     * @return array
     */
    public function GetReadOnlyProperties() {
        $properties = parent::GetReadOnlyProperties();
        array_push( $properties, 'Statute' );
        array_push( $properties, 'ProofOfRegistration' );
        array_push( $properties, 'ShareholderDeclaration' );
        
        return $properties;
    }
}