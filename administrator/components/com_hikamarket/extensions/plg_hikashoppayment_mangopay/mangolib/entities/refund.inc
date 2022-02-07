<?php
namespace MangoPay;

/**
 * Refund entity
 */
class Refund extends Transaction {
    
    /**
     * Initial transaction Id
     * @var string
     */
    public $InitialTransactionId;
    
    /**
     * Debited wallet Id
     * @var string
     */
    public $DebitedWalletId;
    
    /**
     * Credited wallet Id
     * @var string
     */
    public $CreditedWalletId;
}