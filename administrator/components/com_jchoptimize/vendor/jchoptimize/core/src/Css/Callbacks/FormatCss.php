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

namespace JchOptimize\Core\Css\Callbacks;

use JchOptimize\Core\Css\Parser;

defined( '_JCH_EXEC' ) or die( 'Restricted access' );


class FormatCss extends CallbackBase
{
	public $sValidCssRules;

	function processMatches( $aMatches, $sContext )
	{
		if ( isset ( $aMatches[7] ) && !preg_match( '#' . $this->sValidCssRules . '#i', $aMatches[7] ) )
		{
			return '';
		}

		return $aMatches[0];
	}
}
