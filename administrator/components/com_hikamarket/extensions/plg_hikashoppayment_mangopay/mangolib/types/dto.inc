<?php

namespace MangoPay;

/**
 * Abstract class for all DTOs (entities and their composites)
 */
abstract class Dto {
    
    /**
     * Get array with mapping which property is object and what type of object.
     * To be overridden in child class if has any sub objects.
     * @return array
     */
    public function GetSubObjects() { return array(); }

    /**
     * Get array with mapping which property depends on other property  
     * To be overridden in child class if has any dependent objects.
     * @return array
     */
    public function GetDependsObjects() { return array(); }
    
    /**
     * Get array with read only properties - not used in response
     * To be overridden in child class if has any read-only properties.
     * @return array
     */
    public function GetReadOnlyProperties() { return array(); }
}
