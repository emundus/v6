<?php

/**
 * This file is part of FPDI PDF-Parser
 *
 * @package   setasign\FpdiPdfParser
 * @copyright Copyright (c) 2023 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @license   FPDI PDF-Parser Commercial Developer License Agreement (see LICENSE.txt file within this package)
 */

namespace setasign\FpdiPdfParser\PdfParser\SecHandler;

use setasign\Fpdi\PdfParser\PdfParserException;

/**
 * Exception for security handler implementations
 */
class SecHandlerException extends PdfParserException
{
    const UNSUPPORTED_CRYPT_FILTER_METHOD = 0x010101;

    const NOT_AUTHENTICATED = 0x010102;

    const NO_PERMISSIONS = 0x010103;
}
