<?php
namespace MangoPay;

/**
 * Class represents direct debit type for mean of payment in PayIn entity
 */
class PayInPaymentDetailsDirectDebit extends Dto implements PayInPaymentDetails {

    /**
     * Direct debit type {SOFORT, ELV, GIROPAY}
     * @var string
     */
    public $DirectDebitType;
}
