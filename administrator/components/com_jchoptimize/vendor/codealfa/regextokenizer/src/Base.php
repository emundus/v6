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

use CodeAlfa\RegexTokenizer\Debug\Debug;

trait Base
{
        use Debug;

        //language=RegExp
        public static function DOUBLE_QUOTE_STRING()
        {
                return '"' . self::DOUBLE_QUOTE_STRING_VALUE() . '(?:"|(?=$))';
        }

        //language=RegExp
        public static function DOUBLE_QUOTE_STRING_VALUE()
        {
                return '(?<=")(?>(?:\\\\.)?[^\\\\"]*+)++';
        }

        //language=RegExp
        public static function SINGLE_QUOTE_STRING()
        {
                return "'" . self::SINGLE_QUOTE_STRING_VALUE() . "(?:'|(?=$))";
        }

        //language=RegExp
        public static function SINGLE_QUOTE_STRING_VALUE()
        {
                return "(?<=')(?>(?:\\\\.)?[^\\\\']*+)++";
        }

        //language=RegExp
        public static function BACK_TICK_STRING()
        {
                return '`' . self::BACK_TICK_STRING_VALUE() . '(?:`|(?=$))';
        }

        //language=RegExp
        public static function BACK_TICK_STRING_VALUE()
        {
                return '(?<=`)(?>(?:\\\\.)?[^\\\\`]*+)++';
        }

        //language=RegExp
        public static function STRING_CP( $bCV = false )
        {
                $sString = '[\'"`]<<' . self::STRING_VALUE() . '>>[\'"`]';

                return self::prepare( $sString, $bCV );
        }

        //language=RegExp
        public static function STRING_VALUE()
        {
                return '(?:' . self::DOUBLE_QUOTE_STRING_VALUE() . '|' . self::SINGLE_QUOTE_STRING_VALUE() . '|' . self::BACK_TICK_STRING_VALUE() . ')';
        }

        //language=RegExp
        private static function prepare( $sRegex, $bCV )
        {
                $aSearchArray = array( '<<<', '>>>', '<<', '>>' );

                if ( $bCV )
                {
                        return str_replace( $aSearchArray, array( '(?|', ')', '(', ')' ), $sRegex );
                }
                else
                {
                        return str_replace( $aSearchArray, array( '(?:', ')', '', '' ), $sRegex );
                }
        }

        //language=RegExp
        public static function COMMENT()
        {
                return '(?:' . self::BLOCK_COMMENT() . '|' . self::LINE_COMMENT() . ')';
        }

        //language=RegExp
        public static function BLOCK_COMMENT()
        {
                return '/\*(?>\*?[^*]*+)*?\*/';
        }

        public static function LINE_COMMENT()
        {
                return '//[^\r\n]*+';
        }

        protected static function throwExceptionOnPregError( $sExceptionClassName = '' )
        {
                if ( $sExceptionClassName === '' )
                {
                        $sExceptionClassName = 'Exception';
                }

                $error = array_flip( array_filter( get_defined_constants( true )[ 'pcre' ], function ( $value )
                {
                        return substr( $value, -6 ) === '_ERROR';
                }, ARRAY_FILTER_USE_KEY ) )[ preg_last_error() ];

                if ( preg_last_error() != PREG_NO_ERROR )
                {
                        throw new $sExceptionClassName( $error );
                }
        }

}