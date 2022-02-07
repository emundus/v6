<?php

namespace MangoPay;

/**
 * Abstract class with common properties
 */
abstract class EntityBase extends Dto {
    
    /**
     * @var Int Unique identifier
     * (At this moment type is Integer - in the feature will be GUID)
     */
    public $Id;
    
    /**
     * @var String Custom data
     */
    public $Tag;
    
    /**
     * @var Time Date of creation
     */
    public $CreationDate;
    
    /**
     * Construct
     * @param type $id Entity identifier
     */
    function __construct($id = null) {
        $this->Id = $id;
    }
    
    /**
     * Get array with read-only properties
     * @return array
     */
    public function GetReadOnlyProperties() {
        return array( 'Id', 'CreationDate' );
    }
}
