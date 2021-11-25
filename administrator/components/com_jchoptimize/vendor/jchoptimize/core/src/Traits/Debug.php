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

namespace JchOptimize\Core\Traits;

defined( '_JCH_EXEC' ) or die( 'Restricted access' );

/**
 * Trait Debug
 * @package JchOptimize\Core\Traits
 * @deprecated
 */
trait Debug
{


	public $_debug = false; /**DO NOT ENABLE on production sites!! **/
	public $_regexNum = - 1;
	public $_limit = 10.0;
	public $_printCode = true;

	public function _debug( $regex, $code, $regexNum = 0 )
	{
		if ( ! $this->_debug )
		{
			return false;
		}

		/** @var float $pstamp */
		static $pstamp = 0;

		if ( $pstamp === 0 )
		{
			$pstamp = microtime( true );

			return true;
		}

		$nstamp = microtime( true );
		$time   = ( $nstamp - $pstamp ) * 1000;

		if ( $time > $this->_limit )
		{
			print 'num=' . $regexNum . "\n";
			print 'time=' . $time . "\n\n";

			if ( $this->_printCode )
			{
				print $regex . "\n";
				print $code . "\n\n";
			}
		}


		$pstamp = $nstamp;
	}
}