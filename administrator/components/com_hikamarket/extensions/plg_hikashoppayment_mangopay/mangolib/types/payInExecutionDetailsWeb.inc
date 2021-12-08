<?php
namespace MangoPay;

/**
 * Class represents Web type for execution option in PayIn entity
 */
class PayInExecutionDetailsWeb extends Dto implements PayInExecutionDetails {

    /**
     * URL format expected
     * @var string
     */
    public $RedirectURL;
    
    /**
     * URL format expected
     * @var string
     */
    public $ReturnURL;
    
    /**
     * URL format expected.
     * @var string
     */
    public $TemplateURL;
    
    /**
     * The URL where you host the iFramed template.
     * For CB, Visa, MasterCard you need to specify PAYLINE: before your URL 
     * with the iFramed template 
     * ex: PAYLINE: https://www.maysite.com/payline_template/
     * Used for:
     *  - direct debit web type pay-in.
     * 
     * @var PayInTemplateURLOptions 
     */
    public $TemplateURLOptions;
    
    /**
     * @var string
     */
    public $Culture;
    
    /**
     * Mode3DSType { DEFAULT, FORCE }
     * @var string
     */
    public $SecureMode;
    
    /**
     * Get array with read-only properties
     * @return array
     */
    public function GetReadOnlyProperties() {
        $properties = parent::GetReadOnlyProperties();
        array_push( $properties, 'RedirectURL' );
        
        return $properties;
    }
    
    /**
     * Get array with mapping which property is object and what type of object 
     * @return array
     */
    public function GetSubObjects() {
        return array(
            'TemplateURLOptions' => '\MangoPay\PayInTemplateURLOptions'
        );
    }
}