<?php

/**
 * This file is part of FPDI PDF-Parser
 *
 * @package   setasign\FpdiPdfParser
 * @copyright Copyright (c) 2023 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @license   FPDI PDF-Parser Commercial Developer License Agreement (see LICENSE.txt file within this package)
 */

namespace setasign\FpdiPdfParser\PdfParser\SecHandler;

/**
 * A simple implementation of RFC 4013 (SASLprep).
 */
class SaslPrep
{
    /**
     * Processed the given string with the SASLprep Profile.
     *
     * @param string $string
     * @return string
     */
    public static function process($string)
    {
        // Implementation of the SASLprep Profile: https://tools.ietf.org/html/rfc4013

        // 2.1. Mapping

        // Map...
        // C.1.2 Non-ASCII space characters (https://tools.ietf.org/html/rfc3454#appendix-C.1.2)
        // ...to SPACE
        $string = \preg_replace(
            "~[\x{00A0}\x{1680}\x{2001}-\x{200B}\x{202F}\x{205F}\x{3000}]~u",
            "\x20",
            $string
        );

        // B.1 Commonly mapped to nothing (https://tools.ietf.org/html/rfc3454#appendix-B.1)
        $string = \preg_replace(
            "~[\x{00AD}|\x{034F}\x{1806}\x{180B}-\x{180D}\x{200B}-\x{200D}\x{2060}\x{FE00}-\x{FE0F}\x{FEFF}]~u",
            '',
            $string
        );

        // 2.2. Normalization
        if (class_exists('Normalizer', false)) {
            $string = \Normalizer::normalize($string, \Normalizer::FORM_KC);
        }

        // 2.3.  Prohibited Output: https://tools.ietf.org/html/rfc4013#section-2.3
        if (
            \preg_match(
                "~[" .
                // ASCII control characters, C.2.1: https://tools.ietf.org/html/rfc3454#appendix-C.2.1
                "\x{0000}-\x{001F}|\x{007F}" .
                // Non-ASCII control characters, C.2.2: https://tools.ietf.org/html/rfc3454#appendix-C.2.2
                "\x{0080}-\x{009F}\x{06DD}\x{070F}\x{180E}\x{200C}\x{200D}\x{2028}\x{2029}\x{2060}-\x{2063}" .
                "\x{206A}-\x{206F}\x{FEFF}\x{FFF9}-\x{FFFC}\x{1D173}-\x{1D17A}" .
                // Private Use characters, C.3: https://tools.ietf.org/html/rfc3454#appendix-C.3
                "\p{Co}" .
                // Non-character code points, C.4: https://tools.ietf.org/html/rfc3454#appendix-C.4
                "\x{FDD0}-\x{FDEF}\x{FFFE}-\x{FFFF}\x{1FFFE}-\x{1FFFF}\x{2FFFE}-\x{2FFFF}\x{3FFFE}-\x{3FFFF}" .
                "\x{4FFFE}-\x{4FFFF}\x{5FFFE}-\x{5FFFF}\x{6FFFE}-\x{6FFFF}\x{7FFFE}-\x{7FFFF}\x{8FFFE}-\x{8FFFF}" .
                "\x{9FFFE}-\x{9FFFF}\x{AFFFE}-\x{AFFFF}\x{BFFFE}-\x{BFFFF}\x{CFFFE}-\x{CFFFF}\x{DFFFE}-\x{DFFFF}" .
                "\x{EFFFE}-\x{EFFFF}\x{FFFFE}-\x{FFFFF}\x{10FFFE}-\x{10FFFF}" .
                // Surrogate code points: C.5: https://tools.ietf.org/html/rfc3454#appendix-C.5
                "\p{Cs}" .
                // Inappropriate for plain text characters, C.6: https://tools.ietf.org/html/rfc3454#appendix-C.6
                "\x{FFF9}-\x{FFFD}" .
                // Inappropriate for canonical representation characters,
                // C.7: https://tools.ietf.org/html/rfc3454#appendix-C.7
                "\x{2FF0}-\x{2FFB}" .
                // Change display properties or deprecated characters,
                // C.8: https://tools.ietf.org/html/rfc3454#appendix-C.8
                "\x{0340}\x{0341}\x{200E}\x{200F}\x{202A}-\x{202E}\x{206A}-\x{206F}" .
                // Tagging characters, C.9: https://tools.ietf.org/html/rfc3454#appendix-C.9
                "\x{E0001}\x{E0020}-\x{E007F}" .
                "]~u",
                $string
            )
        ) {
            throw new \InvalidArgumentException('Password uses prohibited characters.');
        }

        // 2.4. Bidirectional Characters
        // 1) The characters in section 5.8 MUST be prohibited: https://tools.ietf.org/html/rfc3454#section-5.8
        if (\preg_match("~[\x{0340}\x{0341}\x{200E}\x{200F}\x{202A}-\x{202E}\x{206A}-\x{206F}]~u", $string)) {
            throw new \InvalidArgumentException('Password uses prohibited characters.');
        }

        // 2) If a string contains any RandALCat character, the string MUST NOT contain any LCat character.
        // https://tools.ietf.org/html/rfc3454#appendix-D.1
        $rAndAlCat = "\x{05BE}\x{05C0}\x{05C3}\x{05D0}-\x{05EA}\x{05F0}-\x{05F4}\x{061B}\x{061F}\x{0621}-\x{063A}"
            . "\x{0640}-\x{064A}\x{066D}-\x{066F}\x{0671}-\x{06D5}\x{06DD}\x{06E5}-\x{06E6}\x{06FA}-\x{06FE}"
            . "\x{0700}-\x{070D}\x{0710}\x{0712}-\x{072C}\x{0780}-\x{07A5}\x{07B1}\x{200F}\x{FB1D}\x{FB1F}-\x{FB28}"
            . "\x{FB2A}-\x{FB36}\x{FB38}-\x{FB3C}\x{FB3E}\x{FB40}-\x{FB41}\x{FB43}-\x{FB44}\x{FB46}-\x{FBB1}"
            . "\x{FBD3}-\x{FD3D}\x{FD50}-\x{FD8F}\x{FD92}-\x{FDC7}\x{FDF0}-\x{FDFC}\x{FE70}-\x{FE74}"
            . "\x{FE76}-\x{FEFC}";

        if (\preg_match('~[' . $rAndAlCat . ']~u', $string)) {
            $lCat = "\x{0041}-\x{005A}\x{0061}-\x{007A}\x{00AA}\x{00B5}\x{00BA}\x{00C0}-\x{00D6}\x{00D8}-\x{00F6}"
                . "\x{00F8}-\x{0220}\x{0222}-\x{0233}\x{0250}-\x{02AD}\x{02B0}-\x{02B8}\x{02BB}-\x{02C1}"
                . "\x{02D0}-\x{02D1}\x{02E0}-\x{02E4}\x{02EE}\x{037A}\x{0386}\x{0388}-\x{038A}\x{038C}"
                . "\x{038E}-\x{03A1}\x{03A3}-\x{03CE}\x{03D0}-\x{03F5}\x{0400}-\x{0482}\x{048A}-\x{04CE}"
                . "\x{04D0}-\x{04F5}\x{04F8}-\x{04F9}\x{0500}-\x{050F}\x{0531}-\x{0556}\x{0559}-\x{055F}"
                . "\x{0561}-\x{0587}\x{0589}\x{0903}\x{0905}-\x{0939}\x{093D}-\x{0940}\x{0949}-\x{094C}\x{0950}"
                . "\x{0958}-\x{0961}\x{0964}-\x{0970}\x{0982}-\x{0983}\x{0985}-\x{098C}\x{098F}-\x{0990}"
                . "\x{0993}-\x{09A8}\x{09AA}-\x{09B0}\x{09B2}\x{09B6}-\x{09B9}\x{09BE}-\x{09C0}\x{09C7}-\x{09C8}"
                . "\x{09CB}-\x{09CC}\x{09D7}\x{09DC}-\x{09DD}\x{09DF}-\x{09E1}\x{09E6}-\x{09F1}\x{09F4}-\x{09FA}"
                . "\x{0A05}-\x{0A0A}\x{0A0F}-\x{0A10}\x{0A13}-\x{0A28}\x{0A2A}-\x{0A30}\x{0A32}-\x{0A33}"
                . "\x{0A35}-\x{0A36}\x{0A38}-\x{0A39}\x{0A3E}-\x{0A40}\x{0A59}-\x{0A5C}\x{0A5E}\x{0A66}-\x{0A6F}"
                . "\x{0A72}-\x{0A74}\x{0A83}\x{0A85}-\x{0A8B}\x{0A8D}\x{0A8F}-\x{0A91}\x{0A93}-\x{0AA8}"
                . "\x{0AAA}-\x{0AB0}\x{0AB2}-\x{0AB3}\x{0AB5}-\x{0AB9}\x{0ABD}-\x{0AC0}\x{0AC9}\x{0ACB}-\x{0ACC}"
                . "\x{0AD0}\x{0AE0}\x{0AE6}-\x{0AEF}\x{0B02}-\x{0B03}\x{0B05}-\x{0B0C}\x{0B0F}-\x{0B10}"
                . "\x{0B13}-\x{0B28}\x{0B2A}-\x{0B30}\x{0B32}-\x{0B33}\x{0B36}-\x{0B39}\x{0B3D}-\x{0B3E}\x{0B40}"
                . "\x{0B47}-\x{0B48}\x{0B4B}-\x{0B4C}\x{0B57}\x{0B5C}-\x{0B5D}\x{0B5F}-\x{0B61}\x{0B66}-\x{0B70}"
                . "\x{0B83}\x{0B85}-\x{0B8A}\x{0B8E}-\x{0B90}\x{0B92}-\x{0B95}\x{0B99}-\x{0B9A}\x{0B9C}"
                . "\x{0B9E}-\x{0B9F}\x{0BA3}-\x{0BA4}\x{0BA8}-\x{0BAA}\x{0BAE}-\x{0BB5}\x{0BB7}-\x{0BB9}"
                . "\x{0BBE}-\x{0BBF}\x{0BC1}-\x{0BC2}\x{0BC6}-\x{0BC8}\x{0BCA}-\x{0BCC}\x{0BD7}\x{0BE7}-\x{0BF2}"
                . "\x{0C01}-\x{0C03}\x{0C05}-\x{0C0C}\x{0C0E}-\x{0C10}\x{0C12}-\x{0C28}\x{0C2A}-\x{0C33}"
                . "\x{0C35}-\x{0C39}\x{0C41}-\x{0C44}\x{0C60}-\x{0C61}\x{0C66}-\x{0C6F}\x{0C82}-\x{0C83}"
                . "\x{0C85}-\x{0C8C}\x{0C8E}-\x{0C90}\x{0C92}-\x{0CA8}\x{0CAA}-\x{0CB3}\x{0CB5}-\x{0CB9}\x{0CBE}"
                . "\x{0CC0}-\x{0CC4}\x{0CC7}-\x{0CC8}\x{0CCA}-\x{0CCB}\x{0CD5}-\x{0CD6}\x{0CDE}\x{0CE0}-\x{0CE1}"
                . "\x{0CE6}-\x{0CEF}\x{0D02}-\x{0D03}\x{0D05}-\x{0D0C}\x{0D0E}-\x{0D10}\x{0D12}-\x{0D28}"
                . "\x{0D2A}-\x{0D39}\x{0D3E}-\x{0D40}\x{0D46}-\x{0D48}\x{0D4A}-\x{0D4C}\x{0D57}\x{0D60}-\x{0D61}"
                . "\x{0D66}-\x{0D6F}\x{0D82}-\x{0D83}\x{0D85}-\x{0D96}\x{0D9A}-\x{0DB1}\x{0DB3}-\x{0DBB}\x{0DBD}"
                . "\x{0DC0}-\x{0DC6}\x{0DCF}-\x{0DD1}\x{0DD8}-\x{0DDF}\x{0DF2}-\x{0DF4}\x{0E01}-\x{0E30}"
                . "\x{0E32}-\x{0E33}\x{0E40}-\x{0E46}\x{0E4F}-\x{0E5B}\x{0E81}-\x{0E82}\x{0E84}\x{0E87}-\x{0E88}"
                . "\x{0E8A}\x{0E8D}\x{0E94}-\x{0E97}\x{0E99}-\x{0E9F}\x{0EA1}-\x{0EA3}\x{0EA5}\x{0EA7}"
                . "\x{0EAA}-\x{0EAB}\x{0EAD}-\x{0EB0}\x{0EB2}-\x{0EB3}\x{0EBD}\x{0EC0}-\x{0EC4}\x{0EC6}"
                . "\x{0ED0}-\x{0ED9}\x{0EDC}-\x{0EDD}\x{0F00}-\x{0F17}\x{0F1A}-\x{0F34}\x{0F36}\x{0F38}"
                . "\x{0F3E}-\x{0F47}\x{0F49}-\x{0F6A}\x{0F7F}\x{0F85}\x{0F88}-\x{0F8B}\x{0FBE}-\x{0FC5}"
                . "\x{0FC7}-\x{0FCC}\x{0FCF}\x{1000}-\x{1021}\x{1023}-\x{1027}\x{1029}-\x{102A}\x{102C}\x{1031}"
                . "\x{1038}\x{1040}-\x{1057}\x{10A0}-\x{10C5}\x{10D0}-\x{10F8}\x{10FB}\x{1100}-\x{1159}"
                . "\x{115F}-\x{11A2}\x{11A8}-\x{11F9}\x{1200}-\x{1206}\x{1208}-\x{1246}\x{1248}\x{124A}-\x{124D}"
                . "\x{1250}-\x{1256}\x{1258}\x{125A}-\x{125D}\x{1260}-\x{1286}\x{1288}\x{128A}-\x{128D}"
                . "\x{1290}-\x{12AE}\x{12B0}\x{12B2}-\x{12B5}\x{12B8}-\x{12BE}\x{12C0}\x{12C2}-\x{12C5}"
                . "\x{12C8}-\x{12CE}\x{12D0}-\x{12D6}\x{12D8}-\x{12EE}\x{12F0}-\x{130E}\x{1310}\x{1312}-\x{1315}"
                . "\x{1318}-\x{131E}\x{1320}-\x{1346}\x{1348}-\x{135A}\x{1361}-\x{137C}\x{13A0}-\x{13F4}"
                . "\x{1401}-\x{1676}\x{1681}-\x{169A}\x{16A0}-\x{16F0}\x{1700}-\x{170C}\x{170E}-\x{1711}"
                . "\x{1720}-\x{1731}\x{1735}-\x{1736}\x{1740}-\x{1751}\x{1760}-\x{176C}\x{176E}-\x{1770}"
                . "\x{1780}-\x{17B6}\x{17BE}-\x{17C5}\x{17C7}-\x{17C8}\x{17D4}-\x{17DA}\x{17DC}\x{17E0}-\x{17E9}"
                . "\x{1810}-\x{1819}\x{1820}-\x{1877}\x{1880}-\x{18A8}\x{1E00}-\x{1E9B}\x{1EA0}-\x{1EF9}"
                . "\x{1F00}-\x{1F15}\x{1F18}-\x{1F1D}\x{1F20}-\x{1F45}\x{1F48}-\x{1F4D}\x{1F50}-\x{1F57}\x{1F59}"
                . "\x{1F5B}\x{1F5D}\x{1F5F}-\x{1F7D}\x{1F80}-\x{1FB4}\x{1FB6}-\x{1FBC}\x{1FBE}\x{1FC2}-\x{1FC4}"
                . "\x{1FC6}-\x{1FCC}\x{1FD0}-\x{1FD3}\x{1FD6}-\x{1FDB}\x{1FE0}-\x{1FEC}\x{1FF2}-\x{1FF4}"
                . "\x{1FF6}-\x{1FFC}\x{200E}\x{2071}\x{207F}\x{2102}\x{2107}\x{210A}-\x{2113}\x{2115}"
                . "\x{2119}-\x{211D}\x{2124}\x{2126}\x{2128}\x{212A}-\x{212D}\x{212F}-\x{2131}\x{2133}-\x{2139}"
                . "\x{213D}-\x{213F}\x{2145}-\x{2149}\x{2160}-\x{2183}\x{2336}-\x{237A}\x{2395}\x{249C}-\x{24E9}"
                . "\x{3005}-\x{3007}\x{3021}-\x{3029}\x{3031}-\x{3035}\x{3038}-\x{303C}\x{3041}-\x{3096}"
                . "\x{309D}-\x{309F}\x{30A1}-\x{30FA}\x{30FC}-\x{30FF}\x{3105}-\x{312C}\x{3131}-\x{318E}"
                . "\x{3190}-\x{31B7}\x{31F0}-\x{321C}\x{3220}-\x{3243}\x{3260}-\x{327B}\x{327F}-\x{32B0}"
                . "\x{32C0}-\x{32CB}\x{32D0}-\x{32FE}\x{3300}-\x{3376}\x{337B}-\x{33DD}\x{33E0}-\x{33FE}"
                . "\x{3400}-\x{4DB5}\x{4E00}-\x{9FA5}\x{A000}-\x{A48C}\x{AC00}-\x{D7A3}"
                // surrogates which will not be handled correct: disallowed Unicode code point (>= 0xd800 && <= 0xdfff)
                // . "\x{D800}-\x{FA2D}"
                . "\x{E000}-\x{FA2D}"
                . "\x{FA30}-\x{FA6A}\x{FB00}-\x{FB06}\x{FB13}-\x{FB17}\x{FF21}-\x{FF3A}\x{FF41}-\x{FF5A}"
                . "\x{FF66}-\x{FFBE}\x{FFC2}-\x{FFC7}\x{FFCA}-\x{FFCF}\x{FFD2}-\x{FFD7}\x{FFDA}-\x{FFDC}"
                . "\x{10300}-\x{1031E}\x{10320}-\x{10323}\x{10330}-\x{1034A}\x{10400}-\x{10425}\x{10428}-\x{1044D}"
                . "\x{1D000}-\x{1D0F5}\x{1D100}-\x{1D126}\x{1D12A}-\x{1D166}\x{1D16A}-\x{1D172}"
                . "\x{1D183}-\x{1D184}\x{1D18C}-\x{1D1A9}\x{1D1AE}-\x{1D1DD}\x{1D400}-\x{1D454}"
                . "\x{1D456}-\x{1D49C}\x{1D49E}-\x{1D49F}\x{1D4A2}\x{1D4A5}-\x{1D4A6}\x{1D4A9}-\x{1D4AC}"
                . "\x{1D4AE}-\x{1D4B9}\x{1D4BB}\x{1D4BD}-\x{1D4C0}\x{1D4C2}-\x{1D4C3}\x{1D4C5}-\x{1D505}"
                . "\x{1D507}-\x{1D50A}\x{1D50D}-\x{1D514}\x{1D516}-\x{1D51C}\x{1D51E}-\x{1D539}\x{1D53B}-\x{1D53E}"
                . "\x{1D540}-\x{1D544}\x{1D546}\x{1D54A}-\x{1D550}\x{1D552}-\x{1D6A3}\x{1D6A8}-\x{1D7C9}"
                . "\x{20000}-\x{2A6D6}\x{2F800}-\x{2FA1D}\x{F0000}-\x{FFFFD}\x{100000}-\x{10FFFD}";

            if (\preg_match('~[' . $lCat . ']~u', $string)) {
                throw new \InvalidArgumentException(
                    'Checking of bidirectional strings failed. The string MUST NOT contain any LCat character.'
                );
            }

            // 3) If a string contains any RandALCat character, a RandALCat character MUST be the first character of
            // the string, and a RandALCat character MUST be the last character of the string.
            if (!\preg_match('~^[' . $rAndAlCat . '].*[' . $rAndAlCat . ']$~u', $string)) {
                throw new \InvalidArgumentException(
                    'Checking of bidirectional strings failed. A RandALCat character MUST ' .
                    'be the first and last character.'
                );
            }
        }

        return $string;
    }
}
