<?php

/**
 * This file is part of FPDI PDF-Parser
 *
 * @package   setasign\FpdiPdfParser
 * @copyright Copyright (c) 2023 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @license   FPDI PDF-Parser Commercial Developer License Agreement (see LICENSE.txt file within this package)
 */

namespace setasign\FpdiPdfParser\PdfParser\CrossReference;

use setasign\Fpdi\PdfParser\CrossReference\CrossReference as FpdiCrossReference;
use setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException;
use setasign\Fpdi\PdfParser\CrossReference\ReaderInterface;
use setasign\Fpdi\PdfParser\Filter\FilterException;
use setasign\Fpdi\PdfParser\PdfParserException;
use setasign\Fpdi\PdfParser\StreamReader;
use setasign\Fpdi\PdfParser\Type\PdfDictionary;
use setasign\Fpdi\PdfParser\Type\PdfIndirectObject;
use setasign\Fpdi\PdfParser\Type\PdfNumeric;
use setasign\Fpdi\PdfParser\Type\PdfStream;
use setasign\Fpdi\PdfParser\Type\PdfToken;
use setasign\Fpdi\PdfParser\Type\PdfType;
use setasign\Fpdi\PdfParser\Type\PdfTypeException;
use setasign\FpdiPdfParser\PdfParser\PdfParser;

/**
 * Class CrossReference
 *
 * This class also supports reading of compressed cross-references and object streams.
 */
class CrossReference extends FpdiCrossReference
{
    /**
     * @var PdfParser
     */
    protected $parser;

    /**
     * Data of object streams.
     *
     * @var array
     * @phpstan-var array<int,array{streamOffsets: array, objectStreamParser: PdfParser}>
     */
    protected $objectStreams = [];

    /**
     * @var array
     * @phpstan-var array<CompressedReader|false>
     */
    protected $compressedXrefs = [];

    /**
     * CrossReference constructor.
     *
     * @param PdfParser $parser
     * @param int $fileHeaderOffset
     * @throws CrossReferenceException
     * @throws PdfTypeException
     */
    public function __construct(PdfParser $parser, $fileHeaderOffset = 0)
    {
        try {
            parent::__construct($parser, $fileHeaderOffset);
        } catch (CrossReferenceException $e) {
            $eCode = $e->getCode();
            if (
                $eCode !== CrossReferenceException::NO_XREF_FOUND &&
                $eCode !== CrossReferenceException::NO_STARTXREF_FOUND
            ) {
                throw $e;
            }

            $this->readers[] = new CorruptedReader($this->parser, $fileHeaderOffset);
        }

        // let's check for a CorruptedReader instance because
        $corrupted = \array_filter($this->readers, static function ($reader) {
            return $reader instanceof CorruptedReader;
        });

        // if there's one we should only rely on this one
        if (\count($corrupted)) {
            $this->readers = \array_values($corrupted);
        }
    }

    /**
     * Get the offset by an object number.
     *
     * @param int $objectNumber
     * @return integer|array|false
     * @throws CrossReferenceException
     * @throws PdfParserException
     * @throws PdfTypeException
     */
    public function getOffsetFor($objectNumber)
    {
        foreach ($this->getReaders() as $key => $reader) {
            $offset = $reader->getOffsetFor($objectNumber);
            if ($offset !== false) {
                return $offset;
            }

            // handle hybrid files
            if (!isset($this->compressedXrefs[$key])) {
                $trailer = $reader->getTrailer();
                if (!($reader instanceof CompressedReader) && isset($trailer->value['XRefStm'])) {
                    $this->parser->getStreamReader()->reset(
                        PdfNumeric::ensure($trailer->value['XRefStm'])->value +
                        $this->fileHeaderOffset
                    );
                    $this->parser->getTokenizer()->clearStack();

                    $this->compressedXrefs[$key] = new CompressedReader(
                        $this->parser,
                        PdfStream::ensure(PdfType::resolve($this->parser->readValue(), $this->parser))
                    );
                } else {
                    $this->compressedXrefs[$key] = false;
                }
            }

            if ($this->compressedXrefs[$key] instanceof CompressedReader) {
                /** @var CompressedReader $compressedXref */
                $compressedXref = $this->compressedXrefs[$key];
                $offset = $compressedXref->getOffsetFor($objectNumber);
                if ($offset !== false) {
                    return $offset;
                }
            }
        }

        return false;
    }

    /**
     * Get a cross-reference reader instance.
     *
     * @param PdfToken|PdfIndirectObject $initValue
     * @return ReaderInterface|bool
     * @throws CrossReferenceException
     * @throws PdfParserException
     * @throws PdfTypeException
     */
    protected function initReaderInstance($initValue)
    {
        try {
            return parent::initReaderInstance($initValue);
        } catch (CrossReferenceException $e) {
            if ($e->getCode() === CrossReferenceException::ENCRYPTED) {
                throw $e;
            }

            if ($e->getCode() !== CrossReferenceException::COMPRESSED_XREF) {
                return new CorruptedReader($this->parser, $this->fileHeaderOffset);
            }

            $stream = PdfStream::ensure(PdfType::resolve($initValue, $this->parser));
            return new CompressedReader($this->parser, $stream);
        }
    }

    /**
     * Get an indirect object by its object number.
     *
     * @param int $objectNumber
     * @return PdfIndirectObject
     * @throws CrossReferenceException
     * @throws FilterException
     * @throws PdfParserException
     */
    public function getIndirectObject($objectNumber)
    {
        $offset = $this->getOffsetFor($objectNumber);
        if ($offset === false) {
            throw new CrossReferenceException(
                \sprintf('Object (id:%s) not found.', $objectNumber),
                CrossReferenceException::OBJECT_NOT_FOUND
            );
        }

        $parser = $this->parser;
        $parser->getTokenizer()->clearStack();

        // handle standard cross-references
        if (\is_int($offset)) {
            $parser->getStreamReader()->reset($offset + $this->fileHeaderOffset);

            $object = $parser->readValue();
            if (!($object instanceof PdfIndirectObject)) {
                throw new CrossReferenceException(
                    \sprintf('Object (id:%s) not found at location (%s).', $objectNumber, $offset),
                    CrossReferenceException::OBJECT_NOT_FOUND
                );
            }

        // handle compressed object streams
        } else {
            list($targetObjectNumber) = $offset;
            if (isset($this->objectStreams[$targetObjectNumber])) {
                $streamOffsets = $this->objectStreams[$targetObjectNumber]['streamOffsets'];
                $objectStreamParser = $this->objectStreams[$targetObjectNumber]['objectStreamParser'];
            } else {
                $objectStream = $this->getIndirectObject($targetObjectNumber);
                /** @var PdfStream $stream */
                $stream = PdfType::resolve($objectStream, $this->parser);
                $objectStreamParser = new PdfParser(StreamReader::createByString($stream->getUnfilteredStream()));
                $dict = $stream->value;
                $firstPos = $dict->value['First']->value;
                $count = $dict->value['N']->value;

                $streamOffsets = [];
                for ($i = 0; $i < $count; $i++) {
                    $_objectNumber = PdfNumeric::ensure($objectStreamParser->readValue())->value;
                    $streamOffsets[$_objectNumber] = (
                        PdfNumeric::ensure($objectStreamParser->readValue())->value + $firstPos
                    );
                }

                $this->objectStreams[$targetObjectNumber] = [
                    'streamOffsets' => $streamOffsets,
                    'objectStreamParser' => $objectStreamParser
                ];
            }

            $objectStreamParser->getStreamReader()->reset($streamOffsets[$objectNumber]);
            $objectStreamParser->getTokenizer()->clearStack();
            $value = $objectStreamParser->readValue();
            if ($value === false) {
                throw new CrossReferenceException(
                    \sprintf('Object (id:%s) not found in object stream (id:%s).', $objectNumber, $offset[0]),
                    CrossReferenceException::OBJECT_NOT_FOUND
                );
            }

            $object = PdfIndirectObject::create($objectNumber, 0, $value);
        }

        if ($object->objectNumber !== $objectNumber) {
            throw new CrossReferenceException(
                \sprintf('Wrong object found, got %s while %s was expected.', $object->objectNumber, $objectNumber),
                CrossReferenceException::OBJECT_NOT_FOUND
            );
        }

        return $object;
    }

    /**
     * Bypass check for encryption and handle this afterwards
     */
    protected function checkForEncryption(PdfDictionary $dictionary)
    {
        // encryption is handled in the PdfParser instance afterward.
    }
}
