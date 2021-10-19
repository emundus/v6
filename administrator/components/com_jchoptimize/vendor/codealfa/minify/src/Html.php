<?php

/**
 * @package   codealfa/minify
 * @author    Samuel Marshall <sdmarshall73@gmail.com>
 * @copyright Copyright (c) 2020 Samuel Marshall
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace CodeAlfa\Minify;


class Html extends Base
{
        use \CodeAlfa\RegexTokenizer\Html;

        public $_html = '';
        protected $_isXhtml = false;
        protected $_isHtml5 = false;
        protected $_cssMinifier = null;
        protected $_jsMinifier = null;
        protected $_jsonMinifier = null;
        protected $_minifyLevel = 0;

        /**
         * "Minify" an HTML page
         *
         * @param   string  $html
         *
         * @param   array   $options
         *
         * 'cssMinifier' : (optional) callback function to process content of STYLE
         * elements.
         *
         * 'jsMinifier' : (optional) callback function to process content of SCRIPT
         * elements. Note: the type attribute is ignored.
         *
         * 'xhtml' : (optional boolean) should content be treated as XHTML1.0? If
         * unset, minify will sniff for an XHTML doctype.
         *
         * @return string
         */
        public static function optimize( $html, $options = array() )
        {
                $options[ 'html' ] = $html;

                $min = new Html( $options );

                try
                {
                        return $min->_optimize();
                }
                catch ( \Exception $e )
                {
                        return $min->_html;
                }
        }

        /**
         * Minify the markup given in the constructor
         *
         * @return string
         * @throws \Exception
         */
        private function _optimize()
        {
                $x  = self::HTML_COMMENT();
                $s1 = self::DOUBLE_QUOTE_STRING();
                $s2 = self::SINGLE_QUOTE_STRING();
                $a  = self::HTML_ATTRIBUTE_CP();

                //Regex for escape elements
                $pr = self::HTML_ELEMENT( 'pre' );
                $sc = self::HTML_ELEMENT( 'script' );
                $st = self::HTML_ELEMENT( 'style' );
                $tx = self::HTML_ELEMENT( 'textarea' );

                if ( $this->_minifyLevel > 0 )
                {
                        //Remove comments (not containing IE conditional comments)
                        $rx          = "#(?><?[^<]*+(?>$pr|$sc|$st|$tx|<!--\[(?><?[^<]*+)*?"
                                . "<!\s*\[(?>-?[^-]*+)*?--!?>|<!DOCTYPE[^>]++>)?)*?\K(?:$x|$)#i";
                        $this->_html = $this->_replace( $rx, '', $this->_html, 'html1' );
                }

                //Reduce runs of whitespace outside all elements to one
                $rx          = "#(?>[^<]*+(?:$pr|$sc|$st|$tx|$x|<(?>[^>=]*+(?:=\s*+(?:$s1|$s2|['\"])?|(?=>)))*?>)?)*?\K"
                        . '(?:[\t\f ]++(?=[\r\n]\s*+<)|(?>\r?\n|\r)\K\s++(?=<)|[\t\f]++(?=[ ]\s*+<)|[\t\f]\K\s*+(?=<)|[ ]\K\s*+(?=<)|$)#i';
                $this->_html = $this->_replace( $rx, '', $this->_html, 'html2' );

                //Minify scripts
                //invalid scripts
                $nsc = "<script\b(?=(?>\s*+$a)*?\s*+type\s*+=\s*+(?![\"']?(?:text|application)/(?:javascript|[^'\"\s>]*?json)))[^<>]*+>(?><?[^<]*+)*?</\s*+script\s*+>";
                //invalid styles
                $nst         = "<style\b(?=(?>\s*+$a)*?\s*+type\s*+=\s*+(?![\"']?(?:text|(?:css|stylesheet))))[^<>]*+>(?><?[^<]*+)*?</\s*+style\s*>";
                $rx          = "#(?><?[^<]*+(?:$x|$nsc|$nst)?)*?\K"
                        . "(?:(<script\b(?!(?>\s*+$a)*?\s*+type\s*+=\s*+(?![\"']?(?:text|application)/(?:javascript|[^'\"\s>]*?json)))[^<>]*+>)((?><?[^<]*+)*?)(</\s*+script\s*+>)|"
                        . "(<style\b(?!(?>\s*+$a)*?\s*+type\s*+=\s*+(?![\"']?text/(?:css|stylesheet)))[^<>]*+>)((?><?[^<]*+)*?)(</\s*+style\s*+>)|$)#i";
                $this->_html = $this->_replace( $rx, '', $this->_html, 'html3', array( $this, '_minifyCB' ) );

                if ( $this->_minifyLevel < 1 )
                {
                        return trim( $this->_html );
                }

                //Replace line feed with space (legacy)
                $rx          = "#(?>[^<]*+(?:$pr|$sc|$st|$tx|$x|<(?>[^>=]*+(?:=\s*+(?:$s1|$s2|['\"])?|(?=>)))*?>)?)*?\K"
                        . '(?:[\r\n\t\f]++(?=<)|$)#i';
                $this->_html = $this->_replace( $rx, ' ', $this->_html, 'html4' );

                // remove ws around block elements preserving space around inline elements
                //block/undisplayed elements
                $b = 'address|article|aside|audio|body|blockquote|canvas|dd|div|dl'
                        . '|fieldset|figcaption|figure|footer|form|h[1-6]|head|header|hgroup|html|noscript|ol|output|p'
                        . '|pre|section|style|table|title|tfoot|ul|video';

                //self closing block/undisplayed elements
                $b2 = 'base|meta|link|hr';

                //inline elements
                $i = 'b|big|i|small|tt'
                        . '|abbr|acronym|cite|code|dfn|em|kbd|strong|samp|var'
                        . '|a|bdo|br|map|object|q|script|span|sub|sup'
                        . '|button|label|select|textarea';

                //self closing inline elements
                $i2 = 'img|input';

                $rx          = "#(?>\s*+(?:$pr|$sc|$st|$tx|$x|<(?:(?>$i)\b[^>]*+>|(?:/(?>$i)\b>|(?>$i2)\b[^>]*+>)\s*+)|<[^>]*+>)|[^<]++)*?\K"
                        . "(?:\s++(?=<(?>$b|$b2)\b)|(?:</(?>$b)\b>|<(?>$b2)\b[^>]*+>)\K\s++(?!<(?>$i|$i2)\b)|$)#i";
                $this->_html = $this->_replace( $rx, '', $this->_html, 'html5' );

                //Replace runs of whitespace inside elements with single space escaping pre, textarea, scripts and style elements
                //elements to escape
                $e = 'pre|script|style|textarea';

                $rx          = "#(?>[^<]*+(?:$pr|$sc|$st|$tx|$x|<[^>]++>[^<]*+))*?(?:(?:<(?!$e|!)[^>]*+>)?(?>\s?[^\s<]*+)*?\K\s{2,}|\K$)#i";
                $this->_html = $this->_replace( $rx, ' ', $this->_html, 'html6' );

                //Remove additional ws around attributes
                $rx          = "#(?>\s?(?>[^<>]*+(?:<!(?!DOCTYPE)(?>>?[^>]*+)*?>[^<>]*+)?<|(?=[^<>]++>)[^\s>'\"]++(?>$s1|$s2)?|[^<]*+))*?\K"
                        . "(?>\s\s++|$)#i";
                $this->_html = $this->_replace( $rx, ' ', $this->_html, 'html7' );

                if ( $this->_minifyLevel < 2 )
                {
                        return trim( $this->_html );
                }

                //remove redundant attributes
                $rx          = "#(?:(?=[^<>]++>)|(?><?[^<]*+(?>$x|$nsc|$nst|<(?!(?:script|style|link)|/html>))?)*?"
                        . "<(?:(?:script|style|link)|/html>))(?>[ ]?[^ >]*+)*?\K"
                        . '(?: (?:type|language)=["\']?(?:(?:text|application)/(?:javascript|css)|javascript)["\']?|[^<]*+\K$)#i';
                $this->_html = $this->_replace( $rx, '', $this->_html, 'html8' );

                $j = '<input type="hidden" name="[0-9a-f]{32}" value="1" />';

                //Remove quotes from selected attributes
                if ( $this->_isHtml5 )
                {
                        $ns1 = '"[^"\'`=<>\s]*+(?:[\'`=<>\s]|(?<=\\\\)")(?>(?:(?<=\\\\)")?[^"]*+)*?(?<!\\\\)"';
                        $ns2 = "'[^'\"`=<>\s]*+(?:[\"`=<>\s]|(?<=\\\\)')(?>(?:(?<=\\\\)')?[^']*+)*?(?<!\\\\)'";

                        $rx          = "#(?:(?=[^>]*+>)|<[a-z0-9]++ )"
                                . "(?>[=]?[^=><]*+(?:=(?:$ns1|$ns2)|>(?>[^<]*+(?:$j|$x|$nsc|$nst|<(?![a-z0-9]++ ))?)*?(?:<[a-z0-9]++ |$))?)*?"
                                . "(?:=\K([\"'])([^\"'`=<>\s]++)\g{1}[ ]?|\K$)#i";
                        $this->_html = $this->_replace( $rx, '$2 ', $this->_html, 'html9' );
                }

                //Remove last whitespace in open tag
                $rx          = "#(?>[^<]*+(?:$j|$x|$nsc|$nst|<(?![a-z0-9]++))?)*?(?:<[a-z0-9]++(?>\s*+[^\s>]++)*?\K"
                        . "(?:\s*+(?=>)|(?<=[\"'])\s++(?=/>))|$\K)#i";
                $this->_html = $this->_replace( $rx, '', $this->_html, 'html10' );

                return trim( $this->_html );
        }

        /**
         * @param $content
         * @param $type
         *
         * @return  string
         */
        public static function cleanScript( $content, $type )
        {
                $s1 = self::DOUBLE_QUOTE_STRING();
                $s2 = self::SINGLE_QUOTE_STRING();
                $b  = self::BLOCK_COMMENT();
                $l  = self::LINE_COMMENT();
                $c  = self::HTML_COMMENT();

                if ( $type == 'css' )
                {
                        return preg_replace(
                                "#(?>[<\]\-]?[^'\"<\]\-/]*+(?>$s1|$s2|$b|$l|/)?)*?\K(?:$c|$)#i", '', $content
                        );
                }
                else
                {
                        return Js::optimize( $content, array( 'prepareOnly' => true ) );
                }
        }

        /**
         *
         * @param   array  $m
         *
         * @return string
         */
        protected function _minifyCB( $m )
        {
                if ( $m[ 0 ] == '' )
                {
                        return $m[ 0 ];
                }

                if ( strpos( $m[ 0 ], 'var google_conversion' ) !== false )
                {
                        return $m[ 0 ];
                }

                $openTag  = isset( $m[ 1 ] ) && $m[ 1 ] != '' ? $m[ 1 ] : ( isset( $m[ 4 ] ) && $m[ 4 ] != '' ? $m[ 4 ] : '' );
                $content  = isset( $m[ 2 ] ) && $m[ 2 ] != '' ? $m[ 2 ] : ( isset( $m[ 5 ] ) && $m[ 5 ] != '' ? $m[ 5 ] : '' );
                $closeTag = isset( $m[ 3 ] ) && $m[ 3 ] != '' ? $m[ 3 ] : ( isset( $m[ 6 ] ) && $m[ 6 ] != '' ? $m[ 6 ] : '' );

                if ( trim( $content ) == '' )
                {
                        return $m[ 0 ];
                }

                $type = stripos( $openTag, 'script' ) == 1 ? ( stripos( $openTag, 'json' ) !== false ? 'json' : 'js' ) : 'css';

                if ( $this->{'_' . $type . 'Minifier'} )
                {
                        // minify
                        $content = $this->_callMinifier( $this->{'_' . $type . 'Minifier'}, $content );

                        return $this->_needsCdata( $content, $type ) ? "{$openTag}/*<![CDATA[*/{$content}/*]]>*/{$closeTag}" : "{$openTag}{$content}{$closeTag}";
                }
                else
                {
                        return $m[ 0 ];
                }
        }

        /**
         *
         * @param   array   $aFunc
         * @param   string  $content
         *
         * @return string
         */
        protected function _callMinifier( $aFunc, $content )
        {
                $class  = $aFunc[ 0 ];
                $method = $aFunc[ 1 ];

                return $class::$method( $content );
        }

        /**
         *
         * @param   string  $str
         *
         * @return string
         */
        protected function _needsCdata( $str, $type )
        {
                return ( $this->_isXhtml && $type == 'js' && preg_match( '#(?:[<&]|\-\-|\]\]>)#', $str ) );
        }

}
