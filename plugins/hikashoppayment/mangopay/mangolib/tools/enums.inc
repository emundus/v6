<?php
namespace MangoPay;

/**
 * Request type enum
 */
class RequestType {
    const GET = 'GET';
    const POST = 'POST';
    const PUT = 'PUT';
    const DELETE = 'DELETE';
}

/**
 * Person type for users
 */
class PersonType {
    const Natural = 'NATURAL';
    const Legal = 'LEGAL';
}

/**
 * Event types
 */
class EventType {
    const KycCreated = "KYC_CREATED";
    const KycSucceeded = "KYC_SUCCEEDED";
    const KycFailed = "KYC_FAILED";
    const KycValidationAsked  = "KYC_VALIDATION_ASKED ";
    const PayinNormalCreated = "PAYIN_NORMAL_CREATED";
    const PayinNormalSucceeded = "PAYIN_NORMAL_SUCCEEDED";
    const PayinNormalFailed = "PAYIN_NORMAL_FAILED";
    const PayoutNormalCreated = "PAYOUT_NORMAL_CREATED";
    const PayoutNormalSucceeded = "PAYOUT_NORMAL_SUCCEEDED";
    const PayoutNormalFailed = "PAYOUT_NORMAL_FAILED";
    const TransferNormalCreated = "TRANSFER_NORMAL_CREATED";
    const TransferNormalSucceeded = "TRANSFER_NORMAL_SUCCEEDED";
    const TransferNormalFailed = "TRANSFER_NORMAL_FAILED";
    const PayinRefundCreated = "PAYIN_REFUND_CREATED";
    const PayinRefundSucceeded = "PAYIN_REFUND_SUCCEEDED";
    const PayinRefundFailed = "PAYIN_REFUND_FAILED";
    const PayoutRefundCreated = "PAYOUT_REFUND_CREATED";
    const PayoutRefundSucceeded = "PAYOUT_REFUND_SUCCEEDED";
    const PayoutRefundFailed = "PAYOUT_REFUND_FAILED";
    const TransferRefundCreated = "TRANSFER_REFUND_CREATED";
    const TransferRefundSucceeded = "TRANSFER_REFUND_SUCCEEDED";
    const TransferRefundFailed = "TRANSFER_REFUND_FAILED";
}

/**
 * KYC document types
 */
class KycDocumentType {
    const IdentityProof = 'IDENTITY_PROOF';
    const RegistrationProof = 'REGISTRATION_PROOF';
    const ArticlesOfAssociation = 'ARTICLES_OF_ASSOCIATION';
    const ShareholderDeclaration = 'SHAREHOLDER_DECLARATION';
    const AddressProof = 'ADDRESS_PROOF';
}

/**
 * KYC document statuses
 */
class KycDocumentStatus{
    const Created = 'CREATED';
    const ValidationAsked = 'VALIDATION_ASKED';
    const Validated = 'VALIDATED';
    const Refused = 'REFUSED';
}

class CardValidity {
    const Unknown = 'UNKNOWN';
    const Valid = 'VALID';
    const Invalid = 'INVALID';
}

class SortDirection {
    const DESC = 'desc';
    const ASC = 'asc';
}