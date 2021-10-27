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

namespace JchOptimize\Core\Interfaces;

defined('_JCH_EXEC') or die('Restricted access');

interface Settings
{
	public function __construct($params);

        public function get($param, $default=NULL);
        
        public function set($param, $value);
        
        public function getOptions();

	/**
	 * Delete a value from the settings object
	 *
	 * @param    mixed    $param    The parameter value to be deleted
	 *
	 * @return   null
	 */
	public function remove($param);
}
