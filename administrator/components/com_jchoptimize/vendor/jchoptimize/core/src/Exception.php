<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/core
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2020 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace JchOptimize\Core;

defined('_JCH_EXEC') or die('Restricted access');

class Exception extends \Exception
{

        public function __construct($message = null, $code = 0)
        {
                if (!$message)
                {
			$this->message = 'Unknown Exception';
                }

		$error_message = get_class($this) . " '{$message}' in {$this->getFile()}({$this->getLine()})\n"
		       . "{$this->getTraceAsString()}";	

                parent::__construct($error_message, $code);
        }
}

