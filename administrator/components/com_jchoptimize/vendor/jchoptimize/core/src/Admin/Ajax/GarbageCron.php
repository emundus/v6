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

namespace JchOptimize\Core\Admin\Ajax;

defined( '_JCH_EXEC' ) or die( 'Restricted access' );

use JchOptimize\Platform\Cache;

class GarbageCron extends Ajax
{

	/**
	 *
	 */
	public function run()
	{
		Cache::gc();
	}

}