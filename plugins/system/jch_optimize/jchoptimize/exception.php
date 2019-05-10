<?php

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
defined('_JCH_EXEC') or die('Restricted access');

interface JchIException
{

        /* Protected methods inherited from Exception class */

        public function getMessage();                 // Exception message 

        public function getCode();                    // User-defined Exception code

        public function getFile();                    // Source filename

        public function getLine();                    // Source line

        public function getTrace();                   // An array of the backtrace()

        public function getTraceAsString();           // Formated string of trace

        /* Overrideable methods inherited from Exception class */

        public function __toString();                 // formated string for display

        public function __construct($message = null, $code = 0);
}

abstract class JchCustomException extends Exception implements JchIException
{

        protected $message = 'Unknown exception';     // Exception message
        private $string;                            // Unknown
        protected $code    = 0;                       // User-defined exception code
        protected $file;                              // Source filename of exception
        protected $line;                              // Source line of exception
        private $trace;                             // Unknown

        public function __construct($message = null, $code = 0)
        {
                if (!$message)
                {
                        throw new $this('Unknown ' . get_class($this));
                }
                parent::__construct($message, $code);
        }

        public function __toString()
        {
                return get_class($this) . " '{$this->message}' in {$this->file}({$this->line})\n"
                        . "{$this->getTraceAsString()}";
        }

}

class JchOptimizeException extends JchCustomException
{
        
}
