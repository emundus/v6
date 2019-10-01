<?php

namespace JchOptimize;

/**
 * JCH Optimize - Aggregate and minify external resources for optmized downloads
 * 
 * @author Samuel Marshall <sdmarshall73@gmail.com>
 * @copyright Copyright (c) 2010 Samuel Marshall
 * @license GNU/GPLv3, See LICENSE file
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
class JSON_Optimize extends Optimize
{
        public $json;
        

        public static function optimize($json, $options = array())
        {
                $obj = new JSON_Optimize($json, $options);
                
                try
                {
                        return $obj->_optimize();
                }
                catch(\Exception $e)
                {
                        return $obj->js;
                }
        }

        private function __construct($json, $options)
        {
                $this->json = $json;
                
                foreach ($options as $key => $value)
                {
                        $this->{'_' . $key} = $value;
                }
        }

        private function _optimize()
        {
                //regex for double quoted strings
                $s1 = self::DOUBLE_QUOTE_STRING;

                //regex for single quoted string
                $s2 = self::SINGLE_QUOTE_STRING;

                //regex for block comments
                $b = self::BLOCK_COMMENT;

                //regex for line comments
                $c = self::LINE_COMMENT;

		//regex for HTML comments
		$h = self::HTML_COMMENT;

		//remove all comments
		$rx = "#(?>[^/\"'<]*+(?:$s1|$s2)?)*?\K(?>{$b}|{$c}|{$h}|$)#si";
		$this->json = $this->_replace($rx, '', $this->json, '1');

		//remove whitespaces around :,{}
                $rx   = "#(?>[^\"'\s]*+(?:{$s1}|{$s2})?)*?\K(?>\s++(?=[:,{}\[\]])|(?<=[:,{}\[\]])\s++|$)#s";
                $this->json = $this->_replace($rx, '', $this->json, '2');

                return $this->json;
        }

}
