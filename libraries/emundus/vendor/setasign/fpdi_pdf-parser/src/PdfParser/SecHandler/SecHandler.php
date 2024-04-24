<?php

/**
 * This file is part of FPDI PDF-Parser
 *
 * @package   setasign\FpdiPdfParser
 * @copyright Copyright (c) 2023 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @license   FPDI PDF-Parser Commercial Developer License Agreement (see LICENSE.txt file within this package)
 */

namespace setasign\FpdiPdfParser\PdfParser\SecHandler;

use setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException;
use setasign\Fpdi\PdfParser\PdfParserException;
use setasign\Fpdi\PdfParser\Type\PdfBoolean;
use setasign\Fpdi\PdfParser\Type\PdfDictionary;
use setasign\Fpdi\PdfParser\Type\PdfName;
use setasign\Fpdi\PdfParser\Type\PdfNumeric;
use setasign\Fpdi\PdfParser\Type\PdfType;
use setasign\Fpdi\PdfParser\Type\PdfTypeException;
use setasign\FpdiPdfParser\PdfParser\PdfParser;

/**
 * An abstract SecHandler class
 */
abstract class SecHandler
{
    /**
     * Encryption constant
     *
     * @var int
     */
    const ARCFOUR = 4;

    /**
     * Encryption constant
     *
     * @var int
     */
    const AES = 32;

    /**
     * Encryption constant
     *
     * @var int
     */
    const AES_128 = 96; // 64 | 32

    /**
     * Encryption constant
     *
     * @var int
     */
    const AES_256 = 160; // 128 | 32

    /**
     * User auth mode
     *
     * @var string
     */
    const USER = 'user';

    /**
     * Owner auth mode
     *
     * @var string
     */
    const OWNER = 'owner';

    /**
     * @var PdfParser
     */
    protected $parser;

    /**
     * The default key length in bytes
     *
     * @var int
     */
    protected $keyLength = 5;

    /**
     * The encryption key
     *
     * @var string
     */
    protected $encryptionKey;

    /**
     * The encryption dictionary
     *
     * @var PdfDictionary
     */
    protected $encryptionDictionary;

    /**
     * Defines if this security handler is authenticated
     *
     * @var boolean
     */
    protected $auth = false;

    /**
     * The auth mode
     *
     * Says who is authenticated: user or owner
     *
     * @var string|null
     */
    protected $authMode;

    /**
     * The algorithm and key length to be used for decrypting strings
     *
     * @var array
     */
    protected $stringAlgorithm = [self::ARCFOUR, 5];

    /**
     * The algorithm and key length to be used for decrypting streams
     *
     * @var array
     */
    protected $streamAlgorithm = [self::ARCFOUR, 5];

    /**
     * @param PdfParser $parser
     * @param PdfDictionary $encryptionDictionary
     * @return Standard
     * @throws PdfParserException
     * @throws PdfTypeException
     * @throws SecHandlerException
     * @throws CrossReferenceException
     */
    public static function factory(PdfParser $parser, PdfDictionary $encryptionDictionary)
    {
        $filter = PdfDictionary::get($encryptionDictionary, 'Filter');
        if (!$filter instanceof PdfName) {
            throw new SecHandlerException('Missing /Filter key in encryption dictionary.');
        }

        if ($filter->value === 'Standard') {
            return new Standard($parser, $encryptionDictionary);
        }

        throw new SecHandlerException(
            sprintf('Encryption filter (%s) not supported yet.', $filter->value),
            PdfParserException::NOT_IMPLEMENTED
        );
    }

    /**
     * @param string $cipher
     * @return bool
     * @throws SecHandlerException
     */
    public static function checkCipherSupport($cipher)
    {
        static $checks = [];

        $cipher = strtolower($cipher);

        if (isset($checks[$cipher])) {
            return $checks[$cipher];
        }

        if (!function_exists('openssl_get_cipher_methods')) {
            throw new SecHandlerException(
                'To decrypt strings and streams the FPDI PDF-Parser add-on requires OpenSSL to be installed.'
            );
        }

        $checks[$cipher] = \in_array($cipher, openssl_get_cipher_methods(), true);

        return $checks[$cipher];
    }

    /**
     * @param string $key
     * @param string $data
     * @return string
     * @throws SecHandlerException
     */
    public static function arcfour($key, $data)
    {
        if (self::checkCipherSupport('rc4-40')) {
            return (string)\openssl_decrypt($data, 'rc4-40', $key, OPENSSL_RAW_DATA, '');
        }

        // the native implementation is required to offer a fallback implementation for Arcfour which
        // was marked as legacy and is inactive by default in OpenSSL 3
        static $_lastRc4Key = null, $_lastRc4KeyValue = null;

        if ($_lastRc4Key !== $key) {
            $k = \str_repeat($key, (int)(256 / \strlen($key) + 1));
            $rc4 = \range(0, 255);
            $j = 0;
            for ($i = 0; $i < 256; $i++) {
                $rc4[$i] = $rc4[$j = ($j + ($t = $rc4[$i]) + \ord($k[$i])) % 256];
                $rc4[$j] = $t;
            }
            $_lastRc4Key = $key;
            $_lastRc4KeyValue = $rc4;
        } else {
            $rc4 = $_lastRc4KeyValue;
        }

        $len = \strlen($data);
        $newData = '';
        $a = 0;
        $b = 0;
        for ($i = 0; $i < $len; $i++) {
            $b = ($b + ($t = $rc4[$a = ($a + 1) % 256])) % 256;
            $rc4[$a] = $rc4[$b];
            $rc4[$b] = $t;
            $newData .= \chr(\ord($data[$i]) ^ $rc4[($rc4[$a] + $rc4[$b]) % 256]);
        }

        return $newData;
    }

    /**
     * @param string $key
     * @param string $data
     * @param string $bits
     * @return string
     */
    public static function aesDecrypt($key, $data, $bits = '128')
    {
        if (\strlen($data) < 16) {
            throw new \InvalidArgumentException('Cannot decrypt string with a length lower than 16 bytes.');
        }

        $iv = \substr($data, 0, 16);
        $data = \substr($data, 16);

        if ($data === '' || $data === false) {
            return '';
        }

        $data = \openssl_decrypt($data, 'AES-' . $bits . '-CBC', $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);
        if ($data === false) {
            return '';
        }

        return \substr($data, 0, -\ord($data[\strlen($data) - 1]));
    }

    /**
     * @param PdfParser $parser
     * @param PdfDictionary $encryptionDictionary
     * @throws PdfTypeException
     * @throws SecHandlerException
     * @throws CrossReferenceException
     * @throws PdfParserException
     */
    protected function __construct(PdfParser $parser, PdfDictionary $encryptionDictionary)
    {
        $this->parser = $parser;
        $this->encryptionDictionary = $encryptionDictionary;

        // define the standard key length
        $keyLength = PdfNumeric::ensure(
            PdfType::resolve(PdfDictionary::get($encryptionDictionary, 'Length', PdfNumeric::create(40)), $parser)
        )->value;

        $this->keyLength = $keyLength / 8;

        // Crypt Filters / V == 4
        $cryptFilters = PdfType::resolve(PdfDictionary::get($encryptionDictionary, 'CF'), $parser);
        if ($cryptFilters instanceof PdfDictionary) {
            $streamFilterName = PdfName::ensure(
                PdfType::resolve(PdfDictionary::get($encryptionDictionary, 'StmF'), $parser)
            )->value;

            $streamFilter = PdfDictionary::ensure(
                PdfType::resolve(PdfDictionary::get($cryptFilters, $streamFilterName), $parser)
            );

            $cryptFilterMethod = PdfName::ensure(
                PdfType::resolve(PdfDictionary::get($streamFilter, 'CFM'), $parser)
            )->value;
            $keyLength = PdfNumeric::ensure(
                PdfType::resolve(
                    PdfDictionary::get($streamFilter, 'Length', PdfNumeric::create($this->keyLength)),
                    $parser
                )
            )->value;

            switch ($cryptFilterMethod) {
                case 'V2':
                    $this->streamAlgorithm = [self::ARCFOUR, $keyLength];
                    break;
                case 'AESV2':
                    $this->streamAlgorithm = [self::AES_128, $keyLength];
                    break;
                case 'AESV3':
                    $this->streamAlgorithm = [self::AES_256, $keyLength];
                    break;
                default:
                    throw new SecHandlerException(
                        'Unsupported Crypt Filter Method: ' . $cryptFilterMethod,
                        SecHandlerException::UNSUPPORTED_CRYPT_FILTER_METHOD
                    );
            }

            $stringFilterName = PdfName::ensure(
                PdfType::resolve(PdfDictionary::get($encryptionDictionary, 'StrF'), $parser)
            )->value;
            $stringFilter = PdfDictionary::ensure(
                PdfType::resolve(PdfDictionary::get($cryptFilters, $stringFilterName), $parser)
            );

            $cryptFilterMethod = PdfType::resolve(PdfDictionary::get($stringFilter, 'CFM'), $parser)->value;
            $keyLength = PdfNumeric::ensure(
                PdfType::resolve(
                    PdfDictionary::get($stringFilter, 'Length', PdfNumeric::create($this->keyLength)),
                    $parser
                )
            )->value;

            switch ($cryptFilterMethod) {
                case 'V2':
                    $this->stringAlgorithm = [self::ARCFOUR, $keyLength];
                    break;
                case 'AESV2':
                    $this->stringAlgorithm = [self::AES_128, $keyLength];
                    break;
                case 'AESV3':
                    $this->stringAlgorithm = [self::AES_256, $keyLength];
                    break;
                default:
                    throw new SecHandlerException(
                        'Unsupported Crypt Filter Method: ' . $cryptFilterMethod,
                        SecHandlerException::UNSUPPORTED_CRYPT_FILTER_METHOD
                    );
            }

            // Standard
        } else {
            $this->streamAlgorithm =
            $this->stringAlgorithm = [self::ARCFOUR, $this->keyLength];
        }

        if (
            ($this->streamAlgorithm[0] === self::AES_128 || $this->stringAlgorithm[0] === self::AES_128) &&
            self::checkCipherSupport('AES-128-CBC') === false
        ) {
            throw new SecHandlerException(
                'To decrypt strings and streams the FPDI PDF-Parser add-on requires the AES-128-CBC chipher in OpenSSL.'
            );
        }

        if (
            ($this->streamAlgorithm[0] === self::AES_256 || $this->stringAlgorithm[0] === self::AES_256) &&
            self::checkCipherSupport('AES-256-CBC') === false
        ) {
            throw new SecHandlerException(
                'To decrypt strings and streams the FPDI PDF-Parser add-on requires the AES-256-CBC chipher in OpenSSL.'
            );
        }
    }

    /**
     * @return PdfDictionary
     */
    public function getEncryptionDictionary()
    {
        return $this->encryptionDictionary;
    }

    /**
     * Checks whether metadata is encrypted or not.
     *
     * @return boolean
     * @throws PdfTypeException
     */
    public function isMetadataEncrypted()
    {
        $dict = $this->getEncryptionDictionary();
        return PdfDictionary::get($dict, 'EncryptMetadata', PdfBoolean::create(true))->value;
    }

    /**
     * @param string $data
     * @param int $objectNumber
     * @param int $generationNumber
     * @return string
     * @throws SecHandlerException
     */
    public function decryptString($data, $objectNumber, $generationNumber)
    {
        if ($this->isAuth()) {
            if ($data === '') {
                return $data;
            }

            return $this->decrypt($data, $this->stringAlgorithm, $objectNumber, $generationNumber);
        }

        throw new SecHandlerException(
            'Security handler not authorized to decrypt strings or streams. Authenticate first!',
            SecHandlerException::NOT_AUTHENTICATED
        );
    }

    /**
     * @param string $data
     * @param int $objectNumber
     * @param int $generationNumber
     * @return string
     * @throws SecHandlerException
     */
    public function decryptStream($data, $objectNumber, $generationNumber)
    {
        if ($this->isAuth()) {
            if ($data === '') {
                return $data;
            }

            return $this->decrypt($data, $this->streamAlgorithm, $objectNumber, $generationNumber);
        }

        throw new SecHandlerException(
            'Security handler not authorized to decrypt strings or streams. Authenticate first!',
            SecHandlerException::NOT_AUTHENTICATED
        );
    }

    /**
     * Get the authentication mode (user or owner)
     *
     * @return string|null
     */
    public function getAuthMode()
    {
        return $this->authMode;
    }

    /**
     * Checks for authentication and tries to authenticate without arguments through the auth() method.
     *
     * @return bool
     */
    public function isAuth()
    {
        if ($this->auth === false) {
            $this->auth();
        }

        return $this->auth;
    }

    /**
     * @return boolean
     */
    abstract public function auth();

    /**
     * @param string $data
     * @param array $algorithm
     * @param int $objectNumber
     * @param int $generationNumber
     * @return string
     * @throws SecHandlerException
     */
    protected function decrypt($data, $algorithm, $objectNumber, $generationNumber)
    {
        // Algorithm 1: Encryption of data using the RC4 or AES algorithms

        // Use the 32-byte file encryption key for the AES-256 symmetric key algorithm, along
        // with the string or stream data to be encrypted.
        // Use the AES algorithm in Cipher Block Chaining (CBC) mode, which requires an
        // initialization vector. The block size parameter is set to 16 bytes, and the
        // initialization vector is a 16-byte random number that is stored as the first 16
        // bytes of the encrypted stream or string.
        // The output is the encrypted data to be stored in the PDF file.
        if ($algorithm[0] === self::AES_256) {
            return self::aesDecrypt($this->encryptionKey, $data, '256');
        }

        // a) Obtain the object number and generation number from the object
        //    identifier of the string or stream to be encrypted (see 7.3.10,
        //    "Indirect Objects"). If the string is a direct object, use the
        //    identifier of the indirect object containing it.
        // b) For all strings and streams without crypt filter specifier; treating
        //    the object number and generation number as binary integers, extend
        //    the original n-byte encryption key to n + 5 bytes by appending the
        //    low-order 3 bytes of the object number and the low-order 2 bytes of
        //    the generation number in that order, low-order byte first.
        //    (n is 5 unless the value of V in the encryption dictionary is greater
        //    than 1, in which case n is the value of Length divided by 8.)
        $key = $this->encryptionKey . \pack('VXVXX', $objectNumber, $generationNumber);

        // If using the AES algorithm, extend the encryption key an additional 4 bytes
        // by adding the value “sAlT”, which corresponds to the hexadecimal values 0x73,
        // 0x41, 0x6C, 0x54. (This addition is done for backward compatibility and is not
        // intended to provide additional security.)
        if ($algorithm[0] === self::AES_128) {
            $key .= "\x73\x41\x6c\x54";
        }

        // c) Initialize the MD5 hash function and pass the result of step (b) as input
        //    to this function.
        $s = \md5($key, true);

        // d) Use the first (n + 5) bytes, up to a maximum of 16, of the output from the
        //    MD5 hash as the key for the RC4 or AES symmetric key algorithms, along with
        //    the string or stream data to be encrypted.
        $s = \substr(\substr($s, 0, $algorithm[1] + 5), 0, 16);

        if (self::ARCFOUR & $algorithm[0]) {
            return self::arcfour($s, $data);
        }
        // If using the AES algorithm, the Cipher Block Chaining (CBC) mode, which requires
        // an initialization vector, is used. The block size parameter is set to 16 bytes,
        // and the initialization vector is a 16-byte random number that is stored as the
        // first 16 bytes of the encrypted stream or string.
        if ($algorithm[0] === self::AES_128) {
            return self::aesDecrypt($s, $data, '128');
        }

        throw new SecHandlerException('Unknown algorithm (' . $algorithm[0] . ').');
    }

    /**
     * @param string $data
     * @param string $inputPassword
     * @param string $userKey
     * @return string
     */
    protected function computeHashR6($data, $inputPassword, $userKey = '')
    {
        // Take the SHA-256 hash of the original input to the algorithm and name the resulting 32 bytes, K.
        $hash = 'sha256';
        $k = \hash($hash, $data, true);

        $i = 0;
        $e = '';

        // Perform the following steps (a)-(d) 64 times:
        while (true) {
            // [...]do the following, starting with round number 64:
            if ($i > 63) {
                /* e) Look at the very last byte of "E". If the value of that byte (taken as an unsigned integer) is
                 * greater than the (round number) - 32, repeat steps (a-d) again.
                 */
                $lastByteValue = ord($e[strlen($e) - 1]);
                if ($lastByteValue <= ($i - 32)) {
                    break;
                }
                /* f) Repeat from steps (a-e) until the value of the last byte is ≤ (round number) - 32.
                 */
            }

            /* a) Make a new string, "K1", consisting of 64 repetitions of the sequence: input password, "K", the
             * 48-byte user key. The 48 byte user key is only used when checking the owner password or creating the
             * owner key. If checking the user password or creating the user key, "K1" is the concatenation of the
             * input password and "K".
             */
            $k1 = \str_repeat($inputPassword . $k . $userKey, 64);
            /*
             * b) Encrypt "K1" with the AES-128 (CBC, no padding) algorithm, using the first 16 bytes of "K" as the key
             * and the second 16 bytes of "K" as the initialization vector. The result of this encryption is "E".
             */
            /** @noinspection EncryptionInitializationVectorRandomnessInspection */
            $e = \openssl_encrypt(
                $k1,
                'AES-128-CBC',
                substr($k, 0, 16),
                OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING,
                substr($k, 16, 16)
            );

            /* c) Taking the first 16 bytes of "E" as an unsigned big-endian integer, compute the remainder, modulo 3.
             * If the result is 0, the next hash used is SHA-256, if the result is 1, the next hash used is SHA-384, if
             * the result is 2, the next hash used is SHA-512.
             */
            for ($j = 0, $sum = 0; $j < 16; $j++) {
                $sum += ord($e[$j]);
            }

            switch ($sum % 3) {
                case 0:
                    $hash = 'sha256';
                    break;
                case 1:
                    $hash = 'sha384';
                    break;
                case 2:
                    $hash = 'sha512';
                    break;
            }

            /* d) Using the hash algorithm determined in step c, take the hash of "E". The result is a new value of
             * "K", which will be 32, 48, or 64 bytes in length.
             */
            $k = hash($hash, $e, true);
            $i++;
        }

        return substr($k, 0, 32);
    }
}
