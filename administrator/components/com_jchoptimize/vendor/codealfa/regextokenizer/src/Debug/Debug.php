<?php

/**
 * @package   codealfa/regextokenizer
 * @author    Samuel Marshall <sdmarshall73@gmail.com>
 * @copyright Copyright (c) 2020 Samuel Marshall
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace CodeAlfa\RegexTokenizer\Debug;

/**
 * Trait Debug
 * @package CodeAlfa\RegexTokenizer\Debug
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