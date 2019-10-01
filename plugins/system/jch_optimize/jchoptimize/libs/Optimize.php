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
class Optimize extends \JchOptimizeRegextokenizer
{


        protected $_debug    = false;
        protected $_regexNum = -1;
        protected $_limit =0;

        /**
         * 
         * @param type $rx
         * @param type $code
         * @param type $regexNum
         * @return boolean
         */
        protected function _debug($rx, $code, $regexNum = 0)
        {
                if (!$this->_debug) return false;

                static $pstamp = 0;
                
                if($pstamp === 0)
                {
                        $pstamp = microtime(true);
                        return;
                }
                
                $nstamp = microtime(true);
                $time = $nstamp - $pstamp;
                
                if($time > $this->_limit)
                {
                        print 'num=' . $regexNum . "\n";
                        print 'time=' . $time . "\n\n";
                }

                if ($regexNum == $this->_regexNum)
                {
                        print $rx . "\n";
                        print $code . "\n\n";
                }

                $pstamp = $nstamp;
        }

        /**
         * 
         * @staticvar type $tm
         * @param type $rx
         * @param type $code
         * @param type $replacement
         * @param type $regex_num
         * @return type
         */
        protected function _replace($rx, $replacement, $code, $regex_num, $callback=null)
        {
                static $tm = false;

                if($tm === false)
                {
                       $this->_debug('', ''); 
                       $tm = true;
                }
                
                if(empty($callback))
                {
                        $op_code = preg_replace($rx, $replacement, $code);
                }
                else
                {
                        $op_code = preg_replace_callback($rx, $callback, $code);
                }
                
                $this->_debug($rx, $code, $regex_num);
		$error = @array_flip(get_defined_constants(true)['pcre'])[preg_last_error()];
		if (preg_last_error() != PREG_NO_ERROR)
		{
			throw new \Exception($error);
		}

                return $op_code;
        }

}
