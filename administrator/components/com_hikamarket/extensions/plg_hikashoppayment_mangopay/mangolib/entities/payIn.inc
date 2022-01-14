<?php
namespace MangoPay;

/**
 * Pay-in entity
 */
class PayIn extends Transaction {

    /**
     * Credited wallet Id
     * @var int
     */
    public $CreditedWalletId;
    
    /**
     * PaymentType {CARD, BANK_WIRE, AUTOMATIC_DEBIT, DIRECT_DEBIT }
     * @var string  
     */
    public $PaymentType;
    
    /**
     * One of PayInPaymentDetails implementations, depending on $PaymentType
     * @var object 
     */
    public $PaymentDetails;
    
    /**
     * ExecutionType { WEB, TOKEN, DIRECT, PREAUTHORIZED, RECURRING_ORDER_EXECUTION }
     * @var string  
     */
    public $ExecutionType;
    
    /**
     * One of PayInExecutionDetails implementations, depending on $ExecutionType
     * @var object 
     */
    public $ExecutionDetails;

    /**
     * Get array with mapping which property depends on other property  
     * @return array
     */
    public function GetDependsObjects() {
        return array(
            'PaymentType' => array(
                '_property_name' => 'PaymentDetails',
                'CARD' => '\MangoPay\PayInPaymentDetailsCard',
                'PREAUTHORIZED' => '\MangoPay\PayInPaymentDetailsPreAuthorized',
                'BANK_WIRE' => '\MangoPay\PayInPaymentDetailsBankWire',
                'DIRECT_DEBIT' => '\MangoPay\PayInPaymentDetailsDirectDebit',
                // ...and more in future...
            ),
            'ExecutionType' => array(
                '_property_name' => 'ExecutionDetails',
                'WEB' => '\MangoPay\PayInExecutionDetailsWeb',
                'DIRECT' => '\MangoPay\PayInExecutionDetailsDirect',
                // ...and more in future...
            )
        );
    }

    /**
     * Get array with read-only properties
     * @return array
     */
    public function GetReadOnlyProperties() {
        $properties = parent::GetReadOnlyProperties();
        array_push( $properties, 'PaymentType' );
        array_push( $properties, 'ExecutionType' );
        
        return $properties;
    }
}
