<?php

namespace JchOptimize;

/**
 * Class Minify_CSS
 * @package Minify
 */

/**
 * Compress CSS
 *
 * This is a heavy regex-based removal of whitespace, unnecessary
 * comments and tokens, and some CSS value minimization, where practical.
 * Many steps have been taken to avoid breaking comment-based hacks,
 * including the ie5/mac filter (and its inversion), but expect tricky
 * hacks involving comment tokens in 'content' value strings to break
 * minimization badly. A test suite is available.
 *
 * @package Minify
 * @author Stephen Clay <steve@mrclay.org>
 * @author http://code.google.com/u/1stvamp/ (Issue 64 patch)
 */
class CSS_Optimize extends Optimize
{

        public $css;
        
        /**
         * Minify a CSS string
         *
         * @param string $css
         *
         * @param array $options (currently ignored)
         *
         * @return string
         */
        public static function optimize($css, $options = array())
        {
                $obj = new CSS_Optimize($css, $options);

                try
                {
                        return $obj->_optimize();
                }
                catch(\Exception $e)
                {
                        return $obj->css;
                }
        }

        /**
         * Constructor
         *
         * @param array $options (currently ignored)
         *
         * @return null
         */
        private function __construct($css, $options)
        {
                $this->css = $css;
                
                foreach ($options as $key => $value)
                {
                        $this->{'_' . $key} = $value;
                }
        }

        /**
         * Minify a CSS string
         *
         * @param string $css
         *
         * @return string
         */
        private function _optimize()
        {
                $s1 = self::DOUBLE_QUOTE_STRING;
                $s2 = self::SINGLE_QUOTE_STRING;

                $es = $s1 . '|' . $s2;
                $s  = '(?<!\\\\)(?:' . $es . ')|[\'"]';
                $u  = self::URI;
                $e  = '(?<!\\\\)(?:' . $es . '|' . $u . ')|[\'"(]';

		$b = self::BLOCK_COMMENT;
		$c = self::LINE_COMMENT;

                // Remove all comments
                $rx   = "#(?>/?[^/\"'(]*+(?:{$e})?)*?\K(?>{$b}|{$c}|$)#s";
                $this->css = $this->_replace($rx, '', $this->css, '1');

                // remove ws around , ; : { } in CSS Declarations and media queries
                $rx   = "#(?>(?:[{};]|^)[^{}@;]*+{|(?:(?<![,;:{}])\s++(?![,;:{}]))?[^\s{};\"'(]*+(?:$e|[{};])?)+?\K"
                        . "(?:\s++(?=[,;:{}])|(?<=[,;:{}])\s++|\K$)#s";
                $this->css = $this->_replace($rx, '', $this->css, '2');

                //remove ws around , + > ~ { } in selectors
                $rx   = "#(?>(?:(?<![,+>~{}])\s++(?![,+>~{}]))?[^\s{\"'(]*+(?:{[^{}]++}|{|$e)?)*?\K"
                        . "(?:\s++(?=[,+>~{}])|(?<=[,+>~{};])\s++|$\K)#s";
                $this->css = $this->_replace($rx, '', $this->css, '3');


                //remove last ; in block
                $rx   = "#(?>(?:;(?!}))?[^;\"'(]*+(?:$e)?)*?\K(?:;(?=})|$\K)#s";
                $this->css = $this->_replace($rx, '', $this->css, '4');

                // remove ws inside urls
                $rx   = "#(?>\(?[^\"'(]*+(?:$s)?)*?(?:(?<=\burl)\(\K\s++|\G"
                        . "(?(?=[\"'])['\"][^'\"]++['\"]|[^\s]++)\K\s++(?=\))|$\K)#s";
                $this->css = $this->_replace($rx, '', $this->css, '5');

                // minimize hex colors
                $rx   = "/(?>\#?[^\#\"'(]*+(?:$e)?)*?(?:(?<!=)\#\K"
                        . "([a-f\d])\g{1}([a-f\d])\g{2}([a-f\d])\g{3}(?=[\s;}])|$\K)/is";
                $this->css = $this->_replace($rx, '$1$2$3', $this->css, '6');

                // reduce remaining ws to single space
                $rx   = "#(?>[^\s'\"(]*+(?:$e|\s(?!\s))?)*?\K(?:\s\s++|$)#s";
                $this->css = $this->_replace($rx, ' ', $this->css, '7');

                
                return trim($this->css);
        }
}
