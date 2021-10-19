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

namespace JchOptimize\Core\Html\Callbacks;

defined( '_JCH_EXEC' ) or die( 'Restricted access' );

use JchOptimize\Core\Helper;
use JchOptimize\Core\Html\Processor;
use JchOptimize\Core\Url;
use JchOptimize\Core\Css\Parser as CssParser;

class Cdn extends CallbackBase
{
	protected $sContext = 'default';

	protected $sDir = '';

	protected $sSearchRegex = '';

	protected $sLocalhost = '';


	function processMatches( $aMatches )
	{
		if ( empty ( $aMatches[0] ) )
		{
			return $aMatches[0];
		}

		switch ( $this->sContext )
		{
			case ( 'url' ):

				$sRegex = 'url\([\'"]?(' . $this->sSearchRegex . CssParser::CSS_URL_VALUE() . ')([\'"]?\))';

				preg_match_all( '#' . $sRegex . '#i', $aMatches[0], $aUrlMatches, PREG_SET_ORDER );

				//Prevent modifying the same url multiple times
				$aUrlMatches = array_unique( $aUrlMatches, SORT_REGULAR );

				foreach ( $aUrlMatches as $aMatch )
				{
					if ( ! empty( $aMatch[0] ) )
					{
						$sCdnUrl = $this->processCdn( $aMatch );

						$aMatches[0] = str_replace( $aMatch[1], $sCdnUrl, $aMatches[0] );
					}
				}

				return $aMatches[0];

			case( 'srcset' ):

				$sRegex = '(?:^|,)\s*+(' . $this->sSearchRegex . '([^,]++))';

				preg_match_all( '#' . $sRegex . '#i', $aMatches[4], $aMs, PREG_SET_ORDER );

				foreach ( $aMs as $aMatch )
				{
					if ( ! empty( $aMatch[0] ) )
					{
						$sCdnUrl = $this->processCdn( $aMatch );

						$aMatches[0] = str_replace( $aMatch[2], $sCdnUrl, $aMatches[0] );
					}
				}

				return $aMatches[0];

				break;
			default:

				$sCdnUrl = $this->processCdn( $aMatches );

				return str_replace( $aMatches[6], $sCdnUrl, $aMatches[0] );
		}
	}

	protected function processCdn( $aMatches )
	{
		$sPath = $this->fixRelPath( $aMatches );

		return Helper::cookieLessDomain( $this->oParams, $sPath, $aMatches[0] );
	}

	protected function fixRelPath( $aMatches )
	{
		$m_array = array_filter( $aMatches );
		array_pop( $m_array );
		$sPath = array_pop( $m_array );

		$sRegex = '^(?>https?:)?//' . $this->sLocalhost;
		$sPath  = preg_replace( '#' . $sRegex . '#i', '', trim( $sPath ) );

		if ( substr( $sPath, 0, 1 ) != '/' )
		{
			$sPath = '/' . $this->sDir . '/' . $sPath;
		}

		return $sPath;
	}

	public function setDir( $sDir )
	{
		$this->sDir = $sDir;
	}

	public function setLocalhost( $sLocalhost )
	{
		$this->sLocalhost = $sLocalhost;
	}

	public function setContext( $sContext )
	{
		$this->sContext = $sContext;
	}

	public function setSearchRegex( $sSearchRegex )
	{
		$this->sSearchRegex = $sSearchRegex;
	}
}
