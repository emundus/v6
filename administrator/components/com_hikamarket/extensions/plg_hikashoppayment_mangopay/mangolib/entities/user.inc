<?php

namespace MangoPay;

/**
 * User entity
 */
abstract class User extends EntityBase {
    
    /**
     * Type of user
     * @var String
     */
    public $PersonType;
    
    /**
     * Email address
     * @var String 
     */
    public $Email;
    
    /**
     * Construct
     * @param string $personType String with type of person
     */
    protected function SetPersonType($personType) {
        $this->PersonType = $personType;
    }
    
    /**
     * Get array with read-only properties
     * @return array
     */
    public function GetReadOnlyProperties() {
        $properties = parent::GetReadOnlyProperties();
        array_push( $properties, 'PersonType' );
        
        return $properties;
    }
}