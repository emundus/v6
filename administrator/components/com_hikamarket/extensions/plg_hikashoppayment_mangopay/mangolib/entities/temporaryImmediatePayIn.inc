<?php
namespace MangoPay;

/**
 * WARNING!!
 * It's temporary entity and it will be removed in the future.
 * Please, contact with support before using these features or if you have any questions.
 * 
 * Temporary immediate pay-in entity.
 */
class TemporaryImmediatePayIn extends Transaction {

    /**
     * Payment card Id
     * @var string
     */
    public $PaymentCardId;

    /**
     * Credited wallet Id
     * @var string
     */
    public $CreditedWalletId;
}