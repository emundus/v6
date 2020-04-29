<?php

/**
 * JCH Optimize - Aggregate and minify external resources for optimized downloads
 *
 * @author    Samuel Marshall <sdmarshall73@gmail.com>
 * @copyright Copyright (c) 2010 Samuel Marshall
 * @license   GNU/GPLv3, See LICENSE file
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace JchOptimize\Minify;

defined('_JEXEC') or die('Restricted access');

class Css extends Base
{

        public $css;
        
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
                $obj = new Css($css, $options);

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
	 * @param          $css
	 * @param   array  $options  (currently ignored)
	 *
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
	 * @return string
	 * @throws \Exception
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
                $rx   = "#(?>\#?[^\#\"'(]*+(?:$e)?)*?(?:(?<!=)\#\K"
                        . "([a-f\d])\g{1}([a-f\d])\g{2}([a-f\d])\g{3}(?=[\s;}])|$\K)#is";
                $this->css = $this->_replace($rx, '$1$2$3', $this->css, '6');

                // reduce remaining ws to single space
                $rx   = "#(?>[^\s'\"(]*+(?:$e|\s(?!\s))?)*?\K(?:\s\s++|$)#s";
                $this->css = $this->_replace($rx, ' ', $this->css, '7');

                
                return trim($this->css);
        }
}
