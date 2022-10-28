<?php

namespace Omnipay\RedSys;

class ResponseCode
{
    private $code;
    private $title;
    private $description;

    public function __construct($code, $title, $description)
    {
        $this->code = $code;
        $this->title = $title;
        $this->description = $description;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param $code
     * @return null|static
     */
    public static function find($code)
    {
        $codes = static::all();
        $lookupCode = $code;

        $integerCode = (int)$code;
        if ($integerCode >= 2 && $integerCode <= 99) {
            $lookupCode = '0002-0099';
        } elseif ($integerCode >= 181 && $integerCode <= 182) {
            $lookupCode = '0181-0182';
        } elseif ($integerCode >= 208 && $integerCode <= 209) {
            $lookupCode = '0208-0209';
        } elseif ($integerCode >= 501 && $integerCode <= 503) {
            $lookupCode = '0501-0503';
        }

        if (isset($codes[$lookupCode])) {
            return new static($code, $codes[$lookupCode][0], $codes[$lookupCode][1]);
        }

        return null;
    }

    public static function all()
    {
        return [
            '0000' => [
                'TRANSACCION APROBADA',
                'Transaction authorised by card issuing bank',
            ],
            '0001' => [
                'TRANSACCION APROBADA PREVIA IDENTIFICACION DE TITULAR',
                'Exclusive code for transactions Verified by Visa or MasterCard SecureCode. The transaction has been authorised and the issuing bank informs us that it has correctly authenticated the identity of the cardholder.',
            ],
            '0002-0099' => [
                'TRANSACCION APROBADA',
                'Transaction authorised by issuing bank',
            ],
            '0101' => [
                'EXPIRED CARD',
                'Transaction rejected because card expiry date entered during payment is prior to that currently valid.',
            ],
            '0102' => [
                'CARD TEMPORARILY BLOCKED OR UNDER SUSPICION OF FRAUD',
                'Card temporarily blocked by issuing bank or under suspicion of fraud',
            ],
            '0104' => [
                'OPERATION NOT ALLOWED',
                'Operation not allowed for this type of card.',
            ],
            '0106' => [
                'NO. ATTEMPTS EXCEEDED',
                'Number of attempts with erroneous PIN exceeded.',
            ],
            '0107' => [
                'CONTACT ISSUER',
                'Issuing bank does not allow automatic authorisation. It is necessary to call your authorisation centre to obtain manual approval.',
            ],
            '0109' => [
                'IDENTIFICATION OF MERCHANT OR TERMINAL INVALID',
                'Rejected because merchant is not correctly registered in international card systems.',
            ],
            '0110' => [
                'AMOUNT INVALID',
                'Transaction amount unusual for this type of merchant requesting payment authorisation.',
            ],
            '0114' => [
                'CARD DOES NOT SUPPORT TYPE OF OPERATION REQUESTED',
                'Operation not allowed for this type of card.',
            ],
            '0116' => [
                'INSUFFICIENT BALANCE',
                'The cardholder has insufficient credit to meet payment.',
            ],
            '0118' => [
                'CARD NOT REGISTERED',
                'Card inexistent or not registered by issuing bank.',
            ],
            '0125' => [
                'CARD NOT EFFECTIVE',
                'Card inexistent or not registered by issuing bank.',
            ],
            '0129' => [
                'CVV2/CVC2 ERROR',
                'The CVV2/CVC2 code (three digits on back of card) entered by consumer is erroneous.',
            ],
            '0167' => [
                'CONTACT ISSUER SUSPECTED FRAUD',
                'Due to suspicion that transaction is fraudulent the issuing bank does not allow automatic authorisation. It is necessary to call your authorisation centre to obtain manual approval.',
            ],
            '0180' => [
                'NON-SERVICE CARD',
                'Operation not allowed for this type of card.',
            ],
            '0181-0182' => [
                'CARD WITH DEBIT OR CREDIT RESTRICTIONS',
                'Card temporarily blocked by issuing bank.',
            ],
            '0184' => [
                'AUTHENTICATION ERROR',
                'Exclusive code for transactions Verified by Visa or MasterCard SecureCode. Transaction rejected because issuing bank cannot authenticate the cardholder.',
            ],
            '0190' => [
                'REJECTION WITHOUT SPECIFYING MOTIVE',
                'Transaction rejected by issuing bank but without reporting the reason.',
            ],
            '0191' => [
                'ERRONEOUS EXPIRY DATE',
                'Transaction rejected because card expiry date entered during payment does not match that currently valid.',
            ],

            '0201' => [
                'EXPIRED CARD',
                'Transaction rejected because card expiry date entered during payment is prior to that currently valid. In addition, the issuing bank considers that the card is subject to possible fraud.',
            ],
            '0202' => [
                'CARD TEMPORARILY BLOCKED OR UNDER SUSPICION OF FRAUD',
                'Card temporarily blocked by issuing bank or under suspicion of fraud In addition, the issuing bank considers that the card is subject to possible fraud.',
            ],
            '0204' => [
                'OPERATION NOT ALLOWED',
                'Operation not allowed for this type of card. In addition, the issuing bank considers that the card is subject to possible fraud.',
            ],
            '0207' => [
                'CONTACT ISSUER',
                'Issuing bank does not allow automatic authorisation. It is necessary to call your authorisation centre to obtain manual approval. In addition, the issuing bank considers that the card is subject to possible fraud.',
            ],
            '0208-0209' => [
                'CARD LOST OR STOLEN',
                'Card blocked by issuing bank as holder has reported it is stolen or lost. In addition, the issuing bank considers that the card is subject to possible fraud.',
            ],
            '0280' => [
                'CVV2/CVC2 ERROR',
                'Exclusive code for transactions in which 3-figit CVV2 code is requested (Visa card) or CVC2 (MasterCard) on back of card. The CVV2/CVC2 code entered by purchaser is erroneous. In addition, the issuing bank considers that the card is subject to possible fraud.',
            ],
            '0290' => [
                'REJECTION WITHOUT SPECIFYING MOTIVE',
                'Transaction rejected by issuing bank but without reporting the reason. In addition, the issuing bank considers that the card is subject to possible fraud.',
            ],

            '0400' => [
                'CANCELLATION ACCEPTED',
                'Cancellation or partial chargeback transaction accepted by issuing bank.',
            ],
            '0480' => [
                'ORIGINAL OPERATION NOT FOUND OR TIME-OUT EXCEEDED',
                'The cancellation or partial chargeback not accepted because original operation not located or because issuing bank has not responded within predefined time-out limit.',
            ],
            '0481' => [
                'CANCELLATION ACCEPTED',
                'Cancellation or partial chargeback transaction accepted by issuing bank. However, issuing bank response received late, outside predefined time-out limit.',
            ],

            '0500' => [
                'RECONCILIATION ACCEPTED',
                'Reconciliation transaction accepted by issuing bank.',
            ],
            '0501-0503' => [
                'ORIGINAL OPERATION NOT FOUND OR TIME-OUT EXCEEDED',
                'The reconciliation was not accepted because original operation not located or because issuing bank has not responded within predefined time-out limit.',
            ],
            '9928' => [
                'CANCELLATION OF PRE-AUTHORISATION PERFORMED BY SYSTEM',
                'System has cancelled deferred pre-authorisation as over 72 hours have passed.',
            ],
            '9929' => [
                'CANCELLATION OF PRE-AUTHORISATION PERFORMED BY MERCHANT',
                'The cancellation of the pre-authorisation was accepted.',
            ],
        ];
    }
}
