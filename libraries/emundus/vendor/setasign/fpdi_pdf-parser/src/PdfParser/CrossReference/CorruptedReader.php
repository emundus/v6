<?php

/**
 * This file is part of FPDI PDF-Parser
 *
 * @package   setasign\FpdiPdfParser
 * @copyright Copyright (c) 2021 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @license   FPDI PDF-Parser Commercial Developer License Agreement (see LICENSE.txt file within this package)
 */

namespace setasign\FpdiPdfParser\PdfParser\CrossReference;

use setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException;
use setasign\Fpdi\PdfParser\CrossReference\ReaderInterface;
use setasign\Fpdi\PdfParser\Type\PdfDictionary;
use setasign\FpdiPdfParser\PdfParser\PdfParser;
use setasign\Fpdi\PdfParser\Type\PdfTypeException;

/**
 * Class CorruptedReader
 *
 * This class tries to get object numbers and their positions from the whole PDF content.
 * It doesn't uses a cross-reference at all.
 */
class CorruptedReader implements ReaderInterface
{
    /**
     * @var PdfParser
     */
    protected $parser;

    /**
     * @var array
     */
    protected $offsets = [];

    /**
     * @var PdfDictionary|null
     */
    protected $trailer;

    /**
     * CorruptedReader constructor.
     *
     * @param PdfParser $parser
     * @throws CrossReferenceException
     * @throws PdfTypeException
     */
    public function __construct(PdfParser $parser)
    {
        $this->parser = $parser;
        $this->read();
    }

    /**
     * Extract all information from the pdf stream.
     *
     * @throws CrossReferenceException
     * @throws PdfTypeException
     */
    protected function read()
    {
        $start = 0;
        $bufferLen = 10000;
        $stream = $this->parser->getStreamReader();
        $stream->reset($start, $bufferLen);

        $delimiters = \preg_quote("\x00\x09\x0A\x0C\x0D\x20()<>[]", '/');
        $regex = '/(\d+)[' . $delimiters . ']+(\d+)[' . $delimiters . ']+obj/U';

        while (($buffer = $stream->getBuffer()) !== '') {
            \preg_match_all($regex, $buffer, $match, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

            $lastFound = 0;
            $lengthOfLastFound = ($bufferLen / 2);
            foreach ($match as $foundObj) {
                $lastFound = $foundObj[0][1];
                $lengthOfLastFound = \strlen($foundObj[0][0]);
                $objectNumber = $foundObj[1][0];
                $this->offsets[$objectNumber] = $start + $lastFound;
            }

            $pos = \strpos($buffer, 'trailer');
            if ($pos !== false) {
                // 7 = length of "trailer"
                $stream->reset($start + $pos + 7);
                if (!isset($this->trailer)) {
                    $this->trailer = PdfDictionary::create();
                }

                $this->parser->getTokenizer()->clearStack();
                foreach ($this->parser->readValue()->value as $key => $value) {
                    if ($key === 'Prev') {
                        continue;
                    }

                    $this->trailer->value[$key] = $value;
                }
                $start = $stream->getPosition() + $stream->getOffset();
            } else {
                $start += $lastFound + $lengthOfLastFound;
            }

            $stream->reset($start, $bufferLen);
        }

        if (!isset($this->trailer)) {
            throw new CrossReferenceException(
                'No trailer found.',
                CrossReferenceException::NO_TRAILER_FOUND
            );
        }
    }

    /**
     * Get an offset by an object number.
     *
     * @param int $objectNumber
     * @return int|bool False if the offset was not found.
     */
    public function getOffsetFor($objectNumber)
    {
        if (isset($this->offsets[$objectNumber])) {
            return $this->offsets[$objectNumber];
        }

        return false;
    }

    /**
     * Get the trailer related to this cross reference.
     *
     * @return PdfDictionary
     */
    public function getTrailer()
    {
        return $this->trailer;
    }
}
