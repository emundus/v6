<?php

/**
 * This file is part of FPDI PDF-Parser
 *
 * @package   setasign\FpdiPdfParser
 * @copyright Copyright (c) 2021 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @license   FPDI PDF-Parser Commercial Developer License Agreement (see LICENSE.txt file within this package)
 */

namespace setasign\FpdiPdfParser\PdfParser\CrossReference;

use setasign\Fpdi\PdfParser\CrossReference\ReaderInterface;
use setasign\Fpdi\PdfParser\Filter\FilterException;
use setasign\Fpdi\PdfParser\PdfParserException;
use setasign\Fpdi\PdfParser\StreamReader;
use setasign\Fpdi\PdfParser\Type\PdfArray;
use setasign\Fpdi\PdfParser\Type\PdfDictionary;
use setasign\Fpdi\PdfParser\Type\PdfIndirectObjectReference;
use setasign\Fpdi\PdfParser\Type\PdfNumeric;
use setasign\Fpdi\PdfParser\Type\PdfStream;
use setasign\FpdiPdfParser\PdfParser\PdfParser;

/**
 * Class CompressedReader
 *
 * This class reads a compressed cross-references stream.
 */
class CompressedReader implements ReaderInterface
{
    /**
     * @var PdfParser
     */
    protected $parser;

    /**
     * @var PdfDictionary
     */
    protected $trailer;

    /**
     * @var PdfStream|null
     */
    protected $stream;

    /**
     * @var StreamReader
     */
    protected $streamReader;

    /**
     * @var array
     */
    protected $subSections = [];

    /**
     * The fields sizes (values from W entry).
     *
     * @var array
     */
    protected $fieldSizes = [];

    /**
     * The size of all fields (sum of W entry).
     *
     * @var int
     */
    protected $fieldsSize;

    /**
     * @var array
     */
    protected $offsets = [];

    /**
     * CompressedReader constructor.
     *
     * @param PdfParser $parser
     * @param PdfStream $stream
     */
    public function __construct(PdfParser $parser, PdfStream $stream)
    {
        $this->parser = $parser;

        // There are faulty documents which use an indirect reference for its length parameter
        if (
            isset($stream->value->value['Length']) &&
            $stream->value->value['Length'] instanceof PdfIndirectObjectReference
        ) {
            unset($stream->value->value['Length']);
        }

        $this->stream = $stream;
        $this->read();
    }

    /**
     * Read the main cross-reference data.
     */
    protected function read()
    {
        $dict = $this->stream->value;

        if (isset($dict->value['Index'])) {
            $index = PdfArray::ensure($dict->value['Index']);
            for ($i = 0, $n = \count($index->value); $i < $n; $i += 2) {
                $start = PdfNumeric::ensure($index->value[$i])->value;
                $count = PdfNumeric::ensure($index->value[$i + 1])->value;
                $this->subSections[$start] = $count;
            }
        } else {
            $this->subSections[0] = $dict->value['Size']->value;
        }

        /**
         * @var array $fieldSizes
         */
        $fieldSizes = PdfDictionary::get($dict, 'W', new PdfArray())->value;
        foreach ($fieldSizes as $fieldSize) {
            $this->fieldSizes[] = PdfNumeric::ensure($fieldSize)->value;
        }

        $this->fieldsSize = \array_sum($this->fieldSizes);

        $this->trailer = PdfDictionary::create();
        foreach (['Size', 'Root', 'Encrypt', 'Info', 'ID', 'Prev'] as $key) {
            if (!isset($dict->value[$key])) {
                continue;
            }

            $this->trailer->value[$key] = $dict->value[$key];
        }
    }

    /**
     * Get an offset by an object number.
     *
     * @param int $objectNumber
     * @return int|array|bool False if the offset was not found.
     * @throws FilterException
     * @throws PdfParserException
     */
    public function getOffsetFor($objectNumber)
    {
        $streamOffset = 0;
        foreach ($this->subSections as $start => $count) {
            if ($objectNumber < $start || $objectNumber >= ($start + $count)) {
                $streamOffset += ($this->fieldsSize * $count);
                continue;
            }

            $streamReader = $this->getStreamReader();

            $streamOffset = (int) ($streamOffset + ($objectNumber - $start) * $this->fieldsSize);
            $streamReader->reset($streamOffset);

            $fields = [1, 0, 0];
            for ($i = 0; $i < 3; $i++) {
                if ($this->fieldSizes[$i] > 0) {
                    if ($this->fieldSizes[$i] === 1) {
                        $fields[$i] = \ord($streamReader->readByte());
                    } else {
                        $fields[$i] = 0;
                        for ($k = 0; $k < $this->fieldSizes[$i]; $k++) {
                            $fields[$i] = ($fields[$i] << 8) + (\ord($streamReader->readByte()) & 0xff);
                        }
                    }
                }
            }

            switch ($fields[0]) {
                case 1:
                    return $fields[1];

                case 2:
                    return [$fields[1], $fields[2]];
            }
        }

        return false;
    }

    /**
     * Get the stream reader for this stream.
     *
     * @return StreamReader
     * @throws FilterException
     * @throws PdfParserException
     */
    protected function getStreamReader()
    {
        if ($this->streamReader === null) {
            $this->streamReader = StreamReader::createByString($this->stream->getUnfilteredStream());
            $this->stream = null;
        }

        return $this->streamReader;
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
