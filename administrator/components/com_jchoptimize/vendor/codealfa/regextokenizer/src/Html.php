<?php

/**
 * @package   codealfa/regextokenizer
 * @author    Samuel Marshall <sdmarshall73@gmail.com>
 * @copyright Copyright (c) 2020 Samuel Marshall
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace CodeAlfa\RegexTokenizer;

trait Html
{
        use Base;

        //language=RegExp
        public static function HTML_COMMENT()
        {
                return '<!--(?>-?[^-]*+)*?--!?>';
                //return '(?:(?:<!--|(?<=[\s/^])-->)[^\r\n]*+)';
        }

        //language=RegExp
        public static function HTML_ATTRIBUTE_CP( $sAttrName = '', $bCaptureValue = false, $bCaptureDelimiter = false, $sMatchValue = '' )
        {
                $sTag = $sAttrName != '' ? $sAttrName : '[^\s/"\'=<>]++';
                $sDel = $bCaptureDelimiter ? '([\'"]?)' : '[\'"]?';

                //If we don't need to match a value then the value of attribute is optional
                if ( $sMatchValue == '' )
                {
                        $sAttribute = $sTag . '(?:\s*+=\s*+(?>' . $sDel . ')<<' . self::HTML_ATTRIBUTE_VALUE() . '>>[\'"]?)?';
                }
                else
                {
                        $sAttribute = $sTag . '\s*+=\s*+(?>' . $sDel . ')' . $sMatchValue . '<<' . self::HTML_ATTRIBUTE_VALUE() . '>>[\'"]?';
                }

                return self::prepare( $sAttribute, $bCaptureValue );
        }

        //language=RegExp
        public static function HTML_ATTRIBUTE_VALUE()
        {
                return '(?:' . self::STRING_VALUE() . '|' . self::HTML_ATTRIBUTE_VALUE_UNQUOTED() . ')';
        }

        //language=RegExp
        public static function HTML_ATTRIBUTE_VALUE_UNQUOTED()
        {
                return '(?<==)[^\s*+>]++';
        }

        //language=RegExp
        public static function HTML_ELEMENTS( array $aElement )
        {
                $aResult = array();

                foreach ( $aElement as $sElement )
                {
                        $aResult[] = self::HTML_ELEMENT( $sElement );
                }

                return '(?:' . implode( '|', $aResult ) . ')';
        }

        //language=RegExp
        public static function HTML_ELEMENT( $sElement = '', $bSelfClosing = false )
        {
                $sName = $sElement != '' ? $sElement : '[a-z0-9]++';
                $sTag  = '<' . $sName . '\b[^>]*+>';

                if ( ! $bSelfClosing )
                {
                        $sTag .= '(?><?[^<]*+)*?</' . $sName . '\s*+>';
                }

                return $sTag;
        }

        //language=RegExp
        public static function HTML_ELEMENT_SELF_CLOSING( $sElement = '' )
        {
                return self::HTML_ELEMENT( $sElement, true );
        }
}