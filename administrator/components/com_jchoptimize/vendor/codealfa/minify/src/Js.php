<?php

/**
 * This is a regular expressions based implementation of the JSMin algorithim in PHP as described 
 * on Douglas Crockford's page at http://www.crockford.com/javascript/jsmin.html and also 
 * guided by the PHP port written by  Ryan Grove <ryan@wonko.com>
 * 
 * This was written to provide a PHP tool to minify javascript but with an emphasis on speed, 
 * in particular for tools that want to minify javascript on the fly such as http://www.jch-optimize.net. 
 * Based on independent comparison tests, this library consistently returns the same results as JSMin.php 
 * but on an average of 200 times faster.
 * 
 * Permission is hereby granted to use this version of the library under the
 * same terms as jsmin.c, which has the following license:
 * 
 *  -- 
 * Copyright (c) 2002 Douglas Crockford  (www.crockford.com)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do
 * so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * The Software shall be used for Good, not Evil.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 * --
 *
 * @package codealfa/minify
 * @author Samuel Marshall <sdmarshall73@gmail.com>
 * @copyright (c) 2002, Douglas Crockford <douglas@crockford.com> (jsmin.c)
 * @copyright Copyright (c) 2020 Samuel Marshall
 * @license   GNU/GPLv3, See LICENSE file
 */

namespace CodeAlfa\Minify;

class Js extends Base
{
        use \CodeAlfa\RegexTokenizer\Js;

        public $_js;
        protected $_prepareOnly;
        

        public static function optimize($js, $options = array())
        {
        	$options['js'] = $js;

                $oMinifyJs = new Js($options);
                
                try
                {
                        return $oMinifyJs->_optimize();
                }
                catch(\Exception $e)
                {
                        return $oMinifyJs->_js;
                }
        }

	/**
	 *
	 * @return string
	 *
	 * @throws \Exception
	 */
        private function _optimize()
        {
                //regex for double quoted strings
                $s1 = self::DOUBLE_QUOTE_STRING();

                //regex for single quoted string
                $s2 = self::SINGLE_QUOTE_STRING();

                //regex for backtick quoted string
	        $s3 = self::BACK_TICK_STRING();

                //regex for block comments
                $b = self::BLOCK_COMMENT();

                //regex for line comments
                $c = self::LINE_COMMENT();

		//regex for HTML comments in scripts
		$h = self::JS_HTML_COMMENT();

                //We have to do some manipulating with regexp literals; Their pattern is a little 'irregular' but 
                //they need to be escaped
                //
                //characters that can precede a regexp literal
                $x1 = '[(.<>%,=:[!&|?+\-~*{;\r\n^]';
                //keywords that can precede a regex literal
                $x2 = '\breturn|\bthrow|\btypeof|\bcase|\bdelete|\bdo|\belse|\bin|\binstanceof|\bnew|\bvoid';
                //actual regexp literal
                $x3 = '/(?![/*])(?>(?(?=\\\\)\\\\.|\[(?>(?:\\\\.)?[^\]\r\n]*+)+?\])?[^\\\\/\r\n\[]*+)+?/';
                //ambiguous characters
                $x4 = '[)}]';
                //methods and properties
                $x5 = 'compile|exec|test|toString|constructor|global|ignoreCase|lastIndex|multiline|source';

                //regex for complete regexp literal
                $x = "(?>(?=/)(?<={$x1}|$x2)(?<!\+\+|--){$x3}"
                        . "|(?=/)(?<={$x4}){$x3}(?=\.(?>{$x5})))";

                //control characters excluding \r, \
                $ws = '\x00-\x09\x0B\x0C\x0E-\x1F\x7F';

                //Remove spaces before regexp literals
                $rx = "#(?>[$ws ]*+(?(?=[^'\"/`]*+(?<=[$ws ])/)[^'\"/`$ws ]*+(?(?=['\"/`])(?>$s1|$s2|$s3|$b|$c|$x|/)?)"
                        . "|[^'\"/`]*+(?>$s1|$s2|$s3|$b|$c|$x|/)?))*?\K"
                        . "(?>(?=[$ws ]++/)(?:(?<=$x1|$x2)(?>[$ws ]++($x3))|(?<=$x4)(?>[$ws ]++($x3))(?=\.(?>$x5)))|$)#siS";
                $this->_js = $this->_replace($rx, '$1$2', $this->_js, 'js1');

		//remove HTML comments
	        //language=RegExp
		$r1 = "(?>[<\]\-]?[^'\"`<\]\-/]*+(?>$s1|$s2|$s3|$b|$c|$x|/)?)";
		$rx = "#{$r1}*?\K(?>{$h}|$)#si";
		$this->_js = $this->_replace($rx, '', $this->_js, 'js1B');

                if (isset($this->_prepareOnly) && $this->_prepareOnly == TRUE)
                {
                        return $this->_js;
                }

                //replace line comments with line feed
                $rx = "#(?>[^'\"/`]*+(?>{$s1}|{$s2}|{$s3}|{$x}|{$b}|/(?![*/]))?)*?\K(?>{$c}|$)#si";
                $this->_js = $this->_replace($rx, "\n", $this->_js, 'js2');

                //replace block comments with single space
                $rx = "#(?>[^'\"/`]*+(?>{$s1}|{$s2}|{$s3}|{$x}|/(?![*/]))?)*?\K(?>{$b}|$)#si";
                $this->_js = $this->_replace($rx, ' ', $this->_js, 'js3');

                //convert carriage returns to line feeds
                $rx = "#(?>[^'\"`/\\r]*+(?>$s1|$s2|$s3|$x|/)?)*?\K(?>\\r\\n?|$)#si";
                $this->_js = $this->_replace($rx, "\n", $this->_js, 'js4');

                //convert all other control characters to space
                $rx = "#(?>[^'\"`/$ws]*+(?>$s1|$s2|$s3|$x|/)?)*?\K(?>[$ws]++|$)#si";
                $this->_js = $this->_replace($rx, ' ', $this->_js, 'js5');

                //replace runs of whitespace with single space or linefeed
                $rx = "#(?>[^'\"`/\\n ]*+(?>{$s1}|{$s2}|{$s3}|{$x}|[ \\n](?![ \\n])|/)?)*?\K(?:[ ]++(?=\\n)|\\n\K\s++|[ ]\K[ ]++|$)#si";
                $this->_js = $this->_replace($rx, '', $this->_js, 'js6');

                //if regex literal ends line (without modifiers) insert semicolon
                $rx = "#(?>[/]?[^'\"`/]*+(?>$s1|$s2|$s3|$x(?!\\n))?)*?(?:$x\K\\n(?![!\#%&`*./,:;<=>?@\^|~}\])\"'])|\K$)#si";
                $this->_js = $this->_replace($rx, ';', $this->_js, 'js7');

                //clean up
//                $rx = '#.+\K;$#s';
//                $this->_js = $this->_replace($rx, '', $this->_js, '8');
                $this->_js = substr($this->_js, 0, -1);
                
                //regex for removing spaces
                //remove space except when a space is preceded and followed by a non-ASCII character or by an ASCII letter or digit, 
                //or by one of these characters \ $ _  ...ie., all ASCII characters except those listed.
                $c  = '["\'!\#%&`()*./,:;<=>?@\[\]\^{}|~+\-]';
                $sp = "(?<=$c) | (?=$c)";

                //Non-ASCII characters
                $na = '[^\x00-\x7F]';

                //spaces to keep
                $k1 = "(?<=[\$_a-z0-9\\\\]|$na) (?=[\$_a-z0-9\\\\]|$na)|(?<=\+) (?=\+)|(?<=-) (?=-)";

                //regex for removing linefeeds
                //remove linefeeds except if it precedes a non-ASCII character or an ASCII letter or digit or one of these 
                //characters: ! \ $ _ [ ( { + - and if it follows a non-ASCII character or an ASCII letter or digit or one of these 
                //characters: \ $ _ ] ) } + - " ' ` ...ie., all ASCII characters except those listed respectively
		//(or one of these characters: ) " ' ` followed by a string)
                $ln = '(?<=[!\#%&*./,:;<=>?@\^|~{\[(])\n|\n(?=[\#%&*./,:;<=>?@\^|~}\])])|(?<![\)"\'`])\\n(?=[\'"`])';

                //line feeds to keep
                $k2 = "(?<=[\$_a-z0-9\\\\\])}+\-\"'`]|$na)\\n(?=[!\$_a-z0-9\\\\\[({+\-]|$na)|(?<=[\)\"'`])\\n(?=[\"'`])";

                //remove unnecessary linefeeds and spaces
                $rx = "#(?>[^'\"`/\\n ]*+(?>$s1|$s2|$s3|$x|/|$k1|$k2)?)*?\K(?>$sp|$ln|$)#si";
                $this->_js = $this->_replace($rx, '', $this->_js, 'js9');

                $this->_js = trim($this->_js);

                return $this->_js;
        }

}
