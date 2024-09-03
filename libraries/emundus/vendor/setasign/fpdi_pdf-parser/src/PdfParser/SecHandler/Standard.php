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
use setasign\Fpdi\PdfParser\Type\PdfArray;
use setasign\Fpdi\PdfParser\Type\PdfDictionary;
use setasign\Fpdi\PdfParser\Type\PdfHexString;
use setasign\Fpdi\PdfParser\Type\PdfNumeric;
use setasign\Fpdi\PdfParser\Type\PdfString;
use setasign\Fpdi\PdfParser\Type\PdfTypeException;
use setasign\FpdiPdfParser\PdfParser\PdfParser;

/**
 * Implementation for decryptiong of Standard PDF security.
 */
class Standard extends SecHandler
{
    /**
     * @var string
     */
    protected static $padding = "\x28\xBF\x4E\x5E\x4E\x75\x8A\x41\x64\x00\x4E\x56\xFF\xFA\x01\x08\x2E\x2E\x00\xB6" .
                                "\xD0\x68\x3E\x80\x2F\x0C\xA9\xFE\x64\x53\x69\x7A";

    /**
     * @var string
     */
    protected $fileIdentifier;

    /**
     * This method ensures the correct encoding of a password.
     *
     * Internally the password is converted into the required encoding for the desired revision and it is pocessed
     * with the SASLprep profile if requried.
     *
     * @param int $revision
     * @param string $password The password in UTF-8 encoding.
     * @return string
     * @throws SecHandlerException
     */
    public static function ensurePasswordEncoding($revision, $password)
    {
        if ($revision <= 4) {
            static $pdfDoc2Utf8;
            if ($pdfDoc2Utf8 === null) {
                $pdfDoc2Utf8 = ['A' => 65, 'Æ' => 198, 'Á' => 193, 'Â' => 194, 'Ä' => 196, 'À' => 192, 'Å' => 197,
                    'Ã' => 195, 'B' => 66, 'C' => 67, 'Ç' => 199, 'D' => 68,'E' => 69, 'É' => 201, 'Ê' => 202,
                    'Ë' => 203, 'È' => 200, 'Ð' => 208, '€' => 160, 'F' => 70, 'G' => 71, 'H' => 72, 'I' => 73,
                    'Í' => 205, 'Î' => 206, 'Ï' => 207, 'Ì' => 204, 'J' => 74, 'K' => 75, 'L' => 76, 'Ł' => 149,
                    'M' => 77, 'N' => 78, 'Ñ' => 209, 'O' => 79, 'Œ' => 150, 'Ó' => 211, 'Ô' => 212, 'Ö' => 214,
                    'Ò' => 210, 'Ø' => 216, 'Õ' => 213, 'P' => 80, 'Q' => 81, 'R' => 82, 'S' => 83, 'Š' => 151,
                    'T' => 84, 'Þ' => 222, 'U' => 85, 'Ú' => 218, 'Û' => 219, 'Ü' => 220, 'Ù' => 217, 'V' => 86,
                    'W' => 87, 'X' => 88, 'Y' => 89, 'Ý' => 221, 'Ÿ' => 152, 'Z' => 90, 'Ž' => 153, 'a' => 97,
                    'á' => 225, 'â' => 226, '´' => 180, 'ä' => 228, 'æ' => 230, 'à' => 224, '&' => 38, 'å' => 229,
                    '^' => 94, '~' => 126, '*' => 42, '@' => 64, 'ã' => 227, 'b' => 98, '\\' => 92, '|' => 124,
                    '{' => 123, '}' => 125, '[' => 91, ']' => 93, '˘' => 24, '¦' => 166, '•' => 128, 'c' => 99,
                    'ˇ' => 25, 'ç' => 231, '¸' => 184, '¢' => 162, 'ˆ' => 26, ':' => 58, ',' => 44, '©' => 169,
                    '¤' => 164, 'd' => 100, '†' => 129, '‡' => 130, '°' => 176, '¨' => 168, '÷' => 247, '$' => 36,
                    '˙' => 27, 'ı' => 154, 'e' => 101, 'é' => 233, 'ê' => 234, 'ë' => 235, 'è' => 232, '8' => 56,
                    '…' => 131, '—' => 132, '–' => 133, '=' => 61, 'ð' => 240, '!' => 33, '¡' => 161, 'f' => 102,
                    'ﬁ' => 147, '5' => 53, 'ﬂ' => 148, 'ƒ' => 134, '4' => 52, '⁄' => 135, 'g' => 103, 'ß' => 223,
                    '`' => 96, '>' => 62, '«' => 171, '»' => 187, '‹' => 136, '›' => 137, 'h' => 104, '˝' => 28,
                    '-' => 45, 'i' => 105,'í' => 237, 'î' => 238, 'ï' => 239, 'ì' => 236, 'j' => 106, 'k' => 107,
                    'l' => 108, '<' => 60, '¬' => 172, 'ł' => 155, 'm' => 109, '¯' => 175, '−' => 138, 'µ' => 181,
                    '×' => 215, 'n' => 110, '9' => 57, 'ñ' => 241, '#' => 35, 'o' => 111, 'ó' => 243, 'ô' => 244,
                    'ö' => 246, 'œ' => 156, '˛' => 29, 'ò' => 242, '1' => 49, '½' => 189, '¼' => 188, '¹' => 185,
                    'ª' => 170, 'º' => 186, 'ø' => 248, 'õ' => 245, 'p' => 112, '¶' => 182, '(' => 40, ')' => 41,
                    '%' => 37, '.' => 46, '·' => 183, '‰' => 139, '+' => 43, '±' => 177, 'q' => 113, '?' => 63,
                    '¿' => 191, '"' => 34, '„' => 140, '“' => 141, '”' => 142, '‘' => 143, '’' => 144, '‚' => 145,
                    '\'' => 39, 'r' => 114, '®' => 174, '˚' => 30, 's' => 115, 'š' => 157, '§' => 167, ';' => 59,
                    '7' => 55, '6' => 54, '/' => 47, ' ' => 32, '£' => 163, 't' => 116, 'þ' => 254, '3' => 51,
                    '¾' => 190, '³' => 179, '˜' => 31, '™' => 146, '2' => 50, '²' => 178, 'u' => 117, 'ú' => 250,
                    'û' => 251, 'ü' => 252, 'ù' => 249, '_' => 95, 'v' => 118, 'w' => 119, 'x' => 120, 'y' => 121,
                    'ý' => 253, 'ÿ' => 255, '¥' => 165, 'z' => 122, 'ž' => 158, '0' => 48
                ];
            }

            $chars = [];
            if (function_exists('mb_str_split')) {
                $chars = mb_str_split($password, 1, 'UTF-8');
            } elseif (function_exists('mb_strlen')) {
                $length = mb_strlen($password, 'UTF-8');
                for ($i = 0; $i < $length; $i++) {
                    $chars[] = mb_substr($password, $i, 1, 'UTF-8');
                }
            } elseif (function_exists('iconv')) {
                $length = iconv_strlen($password, 'UTF-8');
                for ($i = 0; $i < $length; $i++) {
                    $chars[] = iconv_substr($password, $i, 1, 'UTF-8');
                }
            }

            $pdfDocPassword = '';
            foreach ($chars as $char) {
                $newChar = isset($pdfDoc2Utf8[$char]) ? $pdfDoc2Utf8[$char] : false;
                if ($newChar === false) {
                    throw new \InvalidArgumentException('Password uses invalid characters.');
                }
                $pdfDocPassword .= chr($newChar);
            }

            return $pdfDocPassword;
        }

        if ($revision === 5 || $revision === 6) {
            return SaslPrep::process($password);
        }

        throw new SecHandlerException(
            sprintf('Revision %s not implemented yet.', $revision),
            PdfParserException::NOT_IMPLEMENTED
        );
    }

    /**
     * @param PdfParser $parser
     * @param PdfDictionary $encryptionDictionary
     * @throws SecHandlerException
     * @throws CrossReferenceException
     * @throws PdfParserException
     * @throws PdfTypeException
     */
    public function __construct(PdfParser $parser, PdfDictionary $encryptionDictionary)
    {
        /** @noinspection NullPointerExceptionInspection */
        $this->fileIdentifier = self::getStringValue(PdfArray::ensure(
            PdfDictionary::get($parser->getCrossReference()->getTrailer(), 'ID'),
            2
        )->value[0]);

        parent::__construct($parser, $encryptionDictionary);
    }

    /**
     * @return mixed
     * @throws PdfTypeException
     */
    public function getRevision()
    {
        return PdfNumeric::ensure(PdfDictionary::get($this->encryptionDictionary, 'R'))->value;
    }

    /**
     * @param string $password The password in UTF-8 encoding
     * @return bool
     * @throws PdfTypeException
     * @throws SecHandlerException
     */
    public function auth($password = null)
    {
        $password = (string)$password;
        if ($this->authByOwnerPassword($password) !== false) {
            return true;
        }

        if ($this->authByUserPassword($password) !== false) {
            return true;
        }

        return $this->auth = false;
    }

    /**
     * @param string $password The owner-password in UTF-8 encoding
     * @return bool
     * @throws PdfTypeException
     * @throws SecHandlerException
     */
    public function authByOwnerPassword($password)
    {
        $password = self::ensurePasswordEncoding($this->getRevision(), $password);
        if (($encryptionKey = $this->createEncryptionKeyByOwnerPassword($password)) !== false) {
            $this->auth = true;
            $this->encryptionKey = $encryptionKey;
            $this->authMode = SecHandler::OWNER;
            return true;
        }

        return false;
    }

    /**
     * @param string $password The user-password in UTF-8 encoding
     * @return bool
     * @throws PdfTypeException
     * @throws SecHandlerException
     */
    public function authByUserPassword($password)
    {
        $password = self::ensurePasswordEncoding($this->getRevision(), $password);
        if (($encryptionKey = $this->createEncryptionKeyByUserPassword($password)) !== false) {
            $this->auth = true;
            $this->encryptionKey = $encryptionKey;
            $this->authMode = SecHandler::USER;
            return true;
        }

        return false;
    }

    /**
     * @param mixed $value
     * @return string
     * @internal
     */
    public static function getStringValue($value)
    {
        if ($value instanceof PdfString) {
            return PdfString::unescape($value->value);
        }

        if ($value instanceof PdfHexString) {
            $v = $value->value;
            if ((strlen($v) % 2) === 1) {
                $v .= '0';
            }
            return (string)\hex2bin($v);
        }

        throw new \InvalidArgumentException('Value is not a pdf string.');
    }

    /**
     * @param string $userPassword
     * @return false|string
     * @throws PdfTypeException
     */
    protected function createEncryptionKeyByUserPassword($userPassword = '')
    {
        $revision = $this->getRevision();
        if ($revision < 5) {
            // Algorithm 6: Authenticating the user password
            $encryptionKey = $this->computeEncryptionKey($userPassword);

            $uValue = $this->computeUValue($encryptionKey);
            $originalUValue = self::getStringValue(PdfDictionary::get($this->encryptionDictionary, 'U'));

            if (
                $uValue === $originalUValue ||
                ($revision >= 3 && strpos($originalUValue, substr($uValue, 0, 16)) === 0)
            ) {
                return $encryptionKey;
            }
        } elseif ($revision === 5 || $revision === 6) {
            // 1. The password string is generated from Unicode input by processing the input
            //    string with the SASLprep (IETF RFC 4013) profile of stringprep (IETF RFC 3454),
            //    and then converting to a UTF-8 representation.

            // 2. Truncate the UTF-8 representation to 127 bytes if it is longer than 127 bytes.
            if (strlen($userPassword) > 127) {
                $userPassword = substr($userPassword, 0, 127);
            }

            // The first 32 bytes are a hash value (explained below). The next 8 bytes are
            // called the Validation Salt. The final 8 bytes are called the Key Salt.
            $uValue = self::getStringValue(PdfDictionary::get($this->encryptionDictionary, 'U'));

            // 4. Test the password against the user key by computing the SHA-256 hash of the
            //    UTF-8 password concatenated with the 8 bytes of user Validation Salt. If the
            //    32 byte result matches the first 32 bytes of the U string, this is the user
            //    password.
            $validationSalt = substr($uValue, 32, 8);

            if ($revision === 6) {
                $hash = $this->computeHashR6($userPassword . $validationSalt, $userPassword);
            } else {
                $hash = hash('sha256', $userPassword . $validationSalt, true);
            }

            if (strpos($uValue, $hash) === 0) {
                // Compute an intermediate user key by computing the SHA-256 hash of the UTF-8 password
                // concatenated with the 8 bytes of user Key Salt. The 32-byte result is the key used
                // to decrypt the 32-byte UE string using AES-256 in CBC mode with no padding and an
                // initialization vector of zero. The 32-byte result is the file encryption key.
                $keySalt = substr($uValue, 40, 8);
                if ($revision === 6) {
                    $tmpKey = $this->computeHashR6($userPassword . $keySalt, $userPassword);
                } else {
                    $tmpKey = hash('sha256', $userPassword . $keySalt, true);
                }

                $ueValue = self::getStringValue(PdfDictionary::get($this->encryptionDictionary, 'UE'));

                return $this->decryptEncryptionKeyR5R6($ueValue, $tmpKey);
            }
        }

        return false;
    }

    /**
     * @param string $value
     * @param string $key
     * @return false|string
     * @throws PdfTypeException
     */
    protected function decryptEncryptionKeyR5R6($value, $key)
    {
        $ivSize = openssl_cipher_iv_length('AES-256-CBC');
        $encryptionKey = openssl_decrypt(
            $value,
            'AES-256-CBC',
            $key,
            OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING,
            str_repeat("\0", $ivSize)
        );

        // 5. Decrypt the 16-byte Perms string using AES-256 in ECB mode with an
        //    initialization vector of zero and the file encryption key as the key. Verify
        //    that bytes 9-11 of the result are the characters ‘a’, ‘d’, ‘b’. Bytes 0-3 of the
        //    decrypted Perms entry, treated as a little-endian integer, are the user
        //    permissions. They should match the value in the P key.
        $perms = self::getStringValue(PdfDictionary::get($this->encryptionDictionary, 'Perms'));

        $ivSize = openssl_cipher_iv_length('AES-256-ECB');
        $tmpPerms = openssl_decrypt(
            $perms,
            'AES-256-ECB',
            $encryptionKey,
            OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING,
            str_repeat("\0", $ivSize)
        );

        if (
            $tmpPerms[9]  === 'a' &&
            $tmpPerms[10] === 'd' &&
            $tmpPerms[11] === 'b'
        ) {
            return $encryptionKey;
        }

        return false;
    }

    /**
     * @param string $ownerPassword
     * @return false|string
     * @throws PdfTypeException
     * @throws SecHandlerException
     */
    protected function createEncryptionKeyByOwnerPassword($ownerPassword = '')
    {
        $revision = $this->getRevision();
        if ($revision < 5) {
            // Algorithm 7: Authenticating the owner password
            // a) Compute an encryption key from the supplied password string, as described
            //    in steps (a) to (d) of "Algorithm 3: Computing the encryption dictionary’s
            //    O (owner password) value".
            $s = substr($ownerPassword . self::$padding, 0, 32);
            $s = md5($s, true);
            if ($revision >= 3) {
                for ($i = 0; $i < 50; $i++) {
                    $s = md5($s, true);
                }
            }

            $encryptionKey = substr($s, 0, $this->keyLength);

            // b) (Security handlers of revision 2 only) Decrypt the value of the encryption
            //     dictionary’s O entry, using an RC4 encryption function with the encryption
            //     key computed in step (a).
            $originalOValue = self::getStringValue(PdfDictionary::get($this->encryptionDictionary, 'O'));

            if ($revision === 2) {
                $userPassword = SecHandler::arcfour($encryptionKey, $originalOValue);

                // (Security handlers of revision 3 or greater) Do the following 20 times: Decrypt
                //  the value of the encryption dictionary’s O entry (first iteration) or the output
                // from the previous iteration (all subsequent iterations), using an RC4 encryption
                // function with a different encryption key at each iteration. The key shall be
                // generated by taking the original key (obtained in step (a)) and performing an XOR
                // (exclusive or)
            } elseif ($revision >= 3) {
                $userPassword = $originalOValue;

                $length = strlen($encryptionKey);
                for ($i = 19; $i >= 0; $i--) {
                    $tmp = [];
                    for ($j = 0; $j < $length; $j++) {
                        $tmp[$j] = ord($encryptionKey[$j]) ^ $i;
                        $tmp[$j] = chr($tmp[$j]);
                    }
                    $userPassword = SecHandler::arcfour(implode('', $tmp), $userPassword);
                }
            } else {
                throw new SecHandlerException('Unsupported revision 1.', SecHandlerException::NOT_IMPLEMENTED);
            }

            // c) The result of step (b) purports to be the user password. Authenticate this
            //    user password using "Algorithm 6: Authenticating the user password". If it
            //    is correct, the password supplied is the correct owner password.
            return $this->createEncryptionKeyByUserPassword($userPassword);
        }

        if ($revision === 5 || $revision === 6) {
            // 1. The password string is generated from Unicode input by processing the input
            //    string with the SASLprep (IETF RFC 4013) profile of stringprep (IETF RFC 3454),
            //    and then converting to a UTF-8 representation.

            // 2. Truncate the UTF-8 representation to 127 bytes if it is longer than 127 bytes.
            if (strlen($ownerPassword) > 127) {
                $ownerPassword = substr($ownerPassword, 0, 127);
            }

            // The first 32 bytes are a hash value (explained below). The next 8 bytes are
            // called the Validation Salt. The final 8 bytes are called the Key Salt.
            $oValue = self::getStringValue(PdfDictionary::get($this->encryptionDictionary, 'O'));
            $uValue = self::getStringValue(PdfDictionary::get($this->encryptionDictionary, 'U'));

            // 3. Test the password against the owner key by computing the SHA-256 hash of the
            //    UTF-8 password concatenated with the 8 bytes of owner Validation Salt,
            //    concatenated with the 48-byte U string. If the 32-byte result matches the
            //    first 32 bytes of the O string, this is the owner password.
            $validationSalt = substr($oValue, 32, 8);
            if ($revision === 6) {
                $hash = $this->computeHashR6(
                    $ownerPassword . $validationSalt . substr($uValue, 0, 48),
                    $ownerPassword,
                    substr($uValue, 0, 48)
                );
            } else {
                $hash = hash('sha256', $ownerPassword . $validationSalt . substr($uValue, 0, 48), true);
            }

            if (strpos($oValue, $hash) === 0) {
                //    Compute an intermediate owner key by computing the SHA-256 hash of the UTF-8
                //    password concatenated with the 8 bytes of owner Key Salt, concatenated with
                //    the 48-byte U string. The 32-byte result is the key used to decrypt the
                //    32-byte OE string using AES-256 in CBC mode with no padding and
                //    an initialization vector of zero. The 32-byte result is the file encryption key.
                $keySalt = substr($oValue, 40, 8);
                if ($revision === 6) {
                    $tmpKey = $this->computeHashR6(
                        $ownerPassword . $keySalt . substr($uValue, 0, 48),
                        $ownerPassword,
                        substr($uValue, 0, 48)
                    );
                } else {
                    $tmpKey = hash('sha256', $ownerPassword . $keySalt . substr($uValue, 0, 48), true);
                }

                $oeValue = self::getStringValue(PdfDictionary::get($this->encryptionDictionary, 'OE'));

                return $this->decryptEncryptionKeyR5R6($oeValue, $tmpKey);
            }

            return false;
        }

        return false;
    }

    /**
     * @param int $value
     * @return int
     * @internal
     */
    public static function ensure32BitInteger($value)
    {
        $value = (int)$value;
        if (PHP_INT_SIZE === 4 || ($value <= 2147483647)) {
            return $value;
        }

        return ($value | (4294967295 << 32));
    }

    protected function computeEncryptionKey($password = '')
    {
        $revision = $this->getRevision();
        if ($revision >= 5) {
            throw new \BadMethodCallException('This method is only useable if revision is < 4.');
        }

        // Algorithm 2: Computing an encryption key
        // a) Pad or truncate the password string to exactly 32 bytes.
        // b) Initialize the MD5 hash function and pass the result of step (a) as input to this function.
        $s = substr($password . self::$padding, 0, 32);

        // c) Pass the value of the encryption dictionary’s O entry to the MD5 hash function.
        //    ("Algorithm 3: Computing the encryption dictionary’s O (owner password) value" shows how the O value
        //    is computed.)
        $s .= self::getStringValue(PdfDictionary::get($this->encryptionDictionary, 'O'));

        // d) Convert the integer value of the P entry to a 32-bit unsigned binary number and pass these
        //    bytes to the MD5 hash function, low-order byte first.
        $pValue = self::ensure32BitInteger(
            PdfDictionary::get($this->encryptionDictionary, 'P', PdfNumeric::create(0))->value
        );
        $s .= pack('V', $pValue);

        // e) Pass the first element of the file’s file identifier array (the value of the ID
        //    entry in the document’s trailer dictionary; see Table 15) to the MD5 hash function.
        $s .= $this->fileIdentifier;

        // f) (Security handlers of revision 4 or greater) If document metadata is not
        //    being encrypted, pass 4 bytes with the value 0xFFFFFFFF to the MD5 hash function.
        if ($revision === 4 && $this->isMetadataEncrypted() === false) {
            $s .= "\xFF\xFF\xFF\xFF";
        }

        // g) Finish the hash.
        $s = md5($s, true);

        // h) (Security handlers of revision 3 or greater) Do the following 50 times:
        //    Take the output from the previous MD5 hash and pass the first n bytes of
        //    the output as input into a new MD5 hash, where n is the number of bytes
        //    of the encryption key as defined by the value of the encryption dictionary’s
        //    Length entry.
        if ($revision >= 3) {
            for ($i = 0; $i < 50; $i++) {
                $s = md5(substr($s, 0, $this->keyLength), true);
            }
        }

        // i) Set the encryption key to the first n bytes of the output from the final
        //    MD5 hash, where n shall always be 5 for security handlers of revision 2 but,
        //    for security handlers of revision 3 or greater, shall depend on the value of
        //    the encryption dictionary’s Length entry.

        return substr($s, 0, $this->keyLength); // key length is calculated automatically if it is missing (5)
    }

    protected function computeUValue($encryptionKey)
    {
        $revision = $this->getRevision();
        if ($revision >= 5) {
            throw new \BadMethodCallException('This method is only useable if revision is < 4.');
        }

        // Algorithm 4: Computing the encryption dictionary’s U (user password)
        // value (Security handlers of revision 2)
        if ($revision === 2) {
            return SecHandler::arcfour($encryptionKey, self::$padding);
        }

        // Algorithm 5: Computing the encryption dictionary’s U (user password)
        // value (Security handlers of revision 3 or greater)
        if (
            $revision === 3 || $revision === 4
        ) {
            // a) Create an encryption key based on the user password string, as described
            //    in "Algorithm 2: Computing an encryption key".
            //    passed through $encryptionKey-parameter

            // b) Initialize the MD5 hash function and pass the 32-byte padding string shown
            //    in step (a) of "Algorithm 2: Computing an encryption key" as input to
            //    this function.
            $s = self::$padding;

            // c) Pass the first element of the file’s file identifier array (the value of
            //    the ID entry in the document’s trailer dictionary; see Table 15) to the
            //    hash function and finish the hash.
            $s .= $this->fileIdentifier;
            $s = md5($s, true);

            // d) Encrypt the 16-byte result of the hash, using an RC4 encryption function
            //    with the encryption key from step (a).
            $s = SecHandler::arcfour($encryptionKey, $s);

            // e) Do the following 19 times: Take the output from the previous invocation
            //    of the RC4 function and pass it as input to a new invocation of the function;
            //    use an encryption key generated by taking each byte of the original encryption
            //    key obtained in step (a) and performing an XOR (exclusive or) operation
            //    between that byte and the single-byte value of the iteration counter (from 1 to 19).
            $length = strlen($encryptionKey);
            for ($i = 1; $i <= 19; $i++) {
                $tmp = [];
                for ($j = 0; $j < $length; $j++) {
                    $tmp[$j] = ord($encryptionKey[$j]) ^ $i;
                    $tmp[$j] = chr($tmp[$j]);
                }
                $s = SecHandler::arcfour(implode('', $tmp), $s);
            }

            // f) Append 16 bytes of arbitrary padding to the output from the final invocation
            //    of the RC4 function and store the 32-byte result as the value of the U entry
            //    in the encryption dictionary.
            return $s . str_repeat("\0", 16);
        }
    }
}
