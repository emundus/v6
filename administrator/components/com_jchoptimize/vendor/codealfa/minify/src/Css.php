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

class Css extends Base
{
        use \CodeAlfa\RegexTokenizer\Css;

        public $_css;
        
        /**
         * Minify a CSS string
         *
         * @param string $css
         * @param array $options (currently ignored)
         *
         * @return string
         */
        public static function optimize($css, $options = array())
        {
        	$options['css'] = $css;

                $obj = new Css($options);

                try
                {
                        return $obj->_optimize();
                }
                catch(\Exception $e)
                {
                        return $obj->_css;
                }
        }

	/**
	 * Minify a CSS string
	 *
	 * @return string
	 * @throws \Exception
	 */
        private function _optimize()
        {
                $s1 = self::DOUBLE_QUOTE_STRING();
                $s2 = self::SINGLE_QUOTE_STRING();

                $es = $s1 . '|' . $s2;
                $s  = '(?<!\\\\)(?:' . $es . ')|[\'"]';
                $u  = self::CSS_URL_CP();
                $e  = '(?<!\\\\)(?:' . $es . '|' . $u . ')|[\'"(]';

		$b = self::BLOCK_COMMENT();
		//$c = self::LINE_COMMENT();

                // Remove all comments
                $rx   = "#(?>/?[^/\"'(]*+(?:{$e})?)*?\K(?>{$b}|$)#s";
                $this->_css = $this->_replace($rx, '', $this->_css, 'css1');

                // remove ws around , ; : { } in CSS Declarations and media queries
                $rx   = "#(?>(?:[{};]|^)[^{}@;]*+{|(?:(?<![,;:{}])\s++(?![,;:{}]))?[^\s{};\"'(]*+(?:$e|[{};])?)+?\K"
                        . "(?:\s++(?=[,;:{}])|(?<=[,;:{}])\s++|\K$)#s";
                $this->_css = $this->_replace($rx, '', $this->_css, 'css2');

                //remove ws around , + > ~ { } in selectors
                $rx   = "#(?>(?:(?<![,+>~{}])\s++(?![,+>~{}]))?[^\s{\"'(]*+(?:{[^{}]++}|{|$e)?)*?\K"
                        . "(?:\s++(?=[,+>~{}])|(?<=[,+>~{};])\s++|$\K)#s";
                $this->_css = $this->_replace($rx, '', $this->_css, 'css3');


                //remove last ; in block
                $rx   = "#(?>(?:;(?!}))?[^;\"'(]*+(?:$e)?)*?\K(?:;(?=})|$\K)#s";
                $this->_css = $this->_replace($rx, '', $this->_css, 'css4');

                // remove ws inside urls
                $rx   = "#(?>\(?[^\"'(]*+(?:$s)?)*?(?:(?<=\burl)\(\K\s++|\G"
                        . "(?(?=[\"'])['\"][^'\"]++['\"]|[^\s]++)\K\s++(?=\))|$\K)#s";
                $this->_css = $this->_replace($rx, '', $this->_css, 'css5');

                // minimize hex colors
                $rx   = "#(?>\#?[^\#\"'(]*+(?:$e)?)*?(?:(?<!=)\#\K"
                        . "([a-f\d])\g{1}([a-f\d])\g{2}([a-f\d])\g{3}(?=[\s;}])|$\K)#is";
                $this->_css = $this->_replace($rx, '$1$2$3', $this->_css, 'css6');

                // reduce remaining ws to single space
                $rx   = "#(?>[^\s'\"(]*+(?:$e|\s(?!\s))?)*?\K(?:\s\s++|$)#s";
                $this->_css = $this->_replace($rx, ' ', $this->_css, 'css7');

                
                return trim($this->_css);
        }
}
