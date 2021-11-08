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

class Json extends Base
{
        use \CodeAlfa\RegexTokenizer\Js;

        public $_json;
        

        public static function optimize($json, $options = array())
        {
        	$options['json'] = $json;

                $obj = new Json($options);
                
                try
                {
                        return $obj->_optimize();
                }
                catch(\Exception $e)
                {
                        return $obj->_json;
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

                //regex for block comments
                $b = self::BLOCK_COMMENT();

                //regex for line comments
                $c = self::LINE_COMMENT();

		//regex for HTML comments
		$h = self::JS_HTML_COMMENT();

		//remove all comments
		$rx = "#(?>[^/\"'<]*+(?:$s1|$s2)?)*?\K(?>{$b}|{$c}|{$h}|$)#si";
		$this->_json = $this->_replace($rx, '', $this->_json, '1');

		//remove whitespaces around :,{}
                $rx   = "#(?>[^\"'\s]*+(?:{$s1}|{$s2})?)*?\K(?>\s++(?=[:,{}\[\]])|(?<=[:,{}\[\]])\s++|$)#s";
                $this->_json = $this->_replace($rx, '', $this->_json, '2');

                return $this->_json;
        }

}
