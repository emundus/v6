<?php

/**
 * This file is part of FPDI PDF-Parser
 *
 * @package   setasign\FpdiPdfParser
 * @copyright Copyright (c) 2023 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @license   FPDI PDF-Parser Commercial Developer License Agreement (see LICENSE file within this package)
 */

namespace setasign\FpdiPdfParser\PdfParser\Filter;

use setasign\Fpdi\PdfParser\Filter\FilterException;

/**
 * Exception for predictor filter class
 */
class PredictorException extends FilterException
{
    /**
     * @var int
     */
    const UNRECOGNIZED_PNG_PREDICTOR = 0x010001;

    /**
     * @var int
     */
    const UNRECOGNIZED_PREDICTOR = 0x010002;
}
