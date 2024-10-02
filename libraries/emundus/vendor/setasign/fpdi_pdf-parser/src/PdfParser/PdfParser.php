<?php

/**
 * This file is part of FPDI PDF-Parser
 *
 * @package   setasign\FpdiPdfParser
 * @copyright Copyright (c) 2023 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @license   FPDI PDF-Parser Commercial Developer License Agreement (see LICENSE.txt file within this package)
 */

namespace setasign\FpdiPdfParser\PdfParser;

use setasign\Fpdi\FpdiException;
use setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException;
use setasign\Fpdi\PdfParser\PdfParserException;
use setasign\Fpdi\PdfParser\StreamReader;
use setasign\Fpdi\PdfParser\Type\PdfDictionary;
use setasign\Fpdi\PdfParser\Type\PdfHexString;
use setasign\Fpdi\PdfParser\Type\PdfIndirectObject;
use setasign\Fpdi\PdfParser\Type\PdfName;
use setasign\Fpdi\PdfParser\Type\PdfNumeric;
use setasign\Fpdi\PdfParser\Type\PdfStream;
use setasign\Fpdi\PdfParser\Type\PdfString;
use setasign\Fpdi\PdfParser\Type\PdfType;
use setasign\Fpdi\PdfParser\Type\PdfTypeException;
use setasign\FpdiPdfParser\PdfParser\CrossReference\CrossReference;
use setasign\FpdiPdfParser\PdfParser\SecHandler\SecHandler;
use setasign\FpdiPdfParser\PdfParser\SecHandler\SecHandlerException;

/**
 * A PDF parser class
 *
 * @property null|CrossReference $xref
 */
class PdfParser extends \setasign\Fpdi\PdfParser\PdfParser
{
    /**
     * @var string
     */
    const PARAM_OWNER_PASSWORD = 'ownerPassword';

    /**
     * @var string
     */
    const PARAM_USER_PASSWORD = 'userPassword';

    /**
     * @var string
     */
    const PARAM_PASSWORD = 'password';

    /**
     * @var string
     */
    const PARAM_IGNORE_PERMISSIONS = 'ignorePermissions';

    /**
     * @var array
     */
    protected $parserParams = [];

    /**
     * @var SecHandler
     */
    protected $secHandler;

    /**
     * @var int
     */
    protected $currentIndirectObjectNumber;

    /**
     * @var int
     */
    protected $currentIndirectObjectGenerationNumber;

    /**
     * PdfParser constructor.
     *
     * @param StreamReader $streamReader
     * @param array $parserParams
     * @throws FpdiException
     */
    public function __construct(StreamReader $streamReader, array $parserParams = [])
    {
        if (!method_exists(PdfString::class, 'escape')) {
            throw new FpdiException(
                'The version of FPDI is not compatible to the version of the FPDI PDF-Parser. Please upgrade to the ' .
                'latest version (^2.5.0).'
            );
        }

        parent::__construct($streamReader);
        $this->parserParams = $parserParams;
    }

    /**
     * Get the cross-reference instance.
     *
     * @return CrossReference
     * @throws CrossReferenceException
     * @throws PdfParserException
     * @throws PdfTypeException
     */
    public function getCrossReference()
    {
        if ($this->xref === null) {
            $this->xref = new CrossReference($this, $this->resolveFileHeader());

            $trailer = $this->xref->getTrailer();
            if (isset($trailer->value['Encrypt'])) {
                try {
                    $encryptDict = PdfType::resolve($trailer->value['Encrypt'], $this);
                    if ($encryptDict instanceof PdfDictionary) {
                        $this->secHandler = SecHandler::factory($this, $encryptDict);

                        if (isset($this->parserParams[self::PARAM_USER_PASSWORD])) {
                            $auth = $this->secHandler->authByUserPassword(
                                $this->parserParams[self::PARAM_USER_PASSWORD]
                            );
                        } elseif (isset($this->parserParams[self::PARAM_OWNER_PASSWORD])) {
                            $auth = $this->secHandler->authByOwnerPassword(
                                $this->parserParams[self::PARAM_OWNER_PASSWORD]
                            );
                        } else {
                            $auth = $this->secHandler->auth(
                                isset($this->parserParams[self::PARAM_PASSWORD])
                                    ? $this->parserParams[self::PARAM_PASSWORD]
                                    : ''
                            );
                        }

                        if ($auth === false) {
                            throw new SecHandlerException(
                                'This PDF document is encrypted but you are not authenticated appropriately.',
                                SecHandlerException::NOT_AUTHENTICATED
                            );
                        }

                        if (
                            $this->secHandler->getAuthMode() === SecHandler::USER &&
                            (
                                !isset($this->parserParams[self::PARAM_IGNORE_PERMISSIONS]) ||
                                $this->parserParams[self::PARAM_IGNORE_PERMISSIONS] === false
                            )
                        ) {
                            throw new SecHandlerException(
                                'This PDF document is encrypted and you are authenticated as the "user" which ' .
                                'has not the permission to extract pages from this document.',
                                SecHandlerException::NO_PERMISSIONS
                            );
                        }
                    }
                } catch (SecHandlerException $e) {
                    // to keep backwards-compatibility we create a CrossReferenceException at this point and forward
                    // the real exception via the $prev parameter. This needs to be changed in the next major version.
                    throw new CrossReferenceException(
                        'This PDF document is encrypted and you are not authenticated appropriately.',
                        CrossReferenceException::ENCRYPTED,
                        $e
                    );
                }
            }
        }

        return $this->xref;
    }

    /**
     * @return SecHandler
     * @throws CrossReferenceException
     * @throws PdfParserException
     * @throws PdfTypeException
     */
    public function getSecHandler()
    {
        // ensure creation of security handler
        $this->getCrossReference();
        return $this->secHandler;
    }

    /**
     * @param int $objectNumber
     * @param int $generationNumber
     * @return bool|PdfIndirectObject
     * @throws CrossReferenceException
     * @throws PdfParserException
     * @throws PdfTypeException
     * @throws SecHandlerException
     */
    protected function parsePdfIndirectObject($objectNumber, $generationNumber)
    {
        $this->currentIndirectObjectNumber = $objectNumber;
        $this->currentIndirectObjectGenerationNumber = $generationNumber;

        $object = parent::parsePdfIndirectObject($objectNumber, $generationNumber);

        if ($object && $this->secHandler instanceof SecHandler && $object->value instanceof PdfStream) {
            $stream = $object->value;
            $dict = $stream->value;

            $content = $this->decryptStream($stream, $objectNumber, $generationNumber);
            $dict->value['Length'] = PdfNumeric::create(\strlen($content));
            $object->value = PdfStream::create($dict, $content);
        }

        $this->currentIndirectObjectNumber = null;
        $this->currentIndirectObjectGenerationNumber = null;

        return $object;
    }

    /**
     * @param PdfStream $stream
     * @param int $objectNumber
     * @param int $generationNumber
     * @return string
     * @throws CrossReferenceException
     * @throws PdfParserException
     * @throws PdfTypeException
     * @throws SecHandlerException
     */
    protected function decryptStream(PdfStream $stream, $objectNumber, $generationNumber)
    {
        $content = $stream->getStream();

        $filters = $stream->getFilters();
        if (isset($filters[0]) && $filters[0] instanceof PdfName && $filters[0]->value === 'Crypt') {
            // the check/support for only "Identity" is currently implemented in the PdfStream class
            return $content;
        }

        // Some tools miss to add the Crypt filter but only rely on the information of the security handler
        $isMetadata = (PdfDictionary::get($stream->value, 'Type')->value === 'Metadata');
        if ($isMetadata) {
            if ($this->secHandler->isMetadataEncrypted() === false) {
                return $content;
            }

            // let's check for unencrypted metadata (there are faulty documents on the road)
            if (strpos($content, '<?xpacket') === 0) {
                return $content;
            }
        }

        return $this->secHandler->decryptStream($content, $objectNumber, $generationNumber);
    }

    /**
     * @return PdfString
     * @throws SecHandlerException
     */
    protected function parsePdfString()
    {
        $value = parent::parsePdfString();

        if ($this->secHandler instanceof SecHandler) {
            $value->value = PdfString::escape(
                $this->secHandler->decryptString(
                    PdfString::unescape($value->value),
                    $this->currentIndirectObjectNumber,
                    $this->currentIndirectObjectGenerationNumber
                )
            );
        }

        return $value;
    }

    /**
     * @return false|PdfHexString
     * @throws SecHandlerException
     */
    protected function parsePdfHexString()
    {
        $value = parent::parsePdfHexString();

        if ($value instanceof PdfHexString && $this->secHandler instanceof SecHandler) {
            $v = $value->value;
            if ((strlen($v) % 2) === 1) {
                $v .= '0';
            }

            $value->value = \bin2hex(
                $this->secHandler->decryptString(
                    \hex2bin($v),
                    $this->currentIndirectObjectNumber,
                    $this->currentIndirectObjectGenerationNumber
                )
            );
        }

        return $value;
    }
}
