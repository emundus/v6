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

defined( '_JCH_EXEC' ) or die( 'Restricted access' );


class CombineMediaQueries extends CallbackBase
{

	function processMatches( $aMatches, $sContext )
	{
		if ( $sContext == 'media' )
		{
			return '@media ' . $this->combineMediaQueries( $this->aUrl['media'], trim( substr( $aMatches[2], 6 ) ) ) . '{' . $aMatches[4] . '}';
		}

		if ( $sContext == 'import' )
		{
			$sMediaQuery = $aMatches[7];

			$sAtImport = substr( $aMatches[0], 0, - ( strlen( $sMediaQuery . ';' ) ) );

			return $sAtImport . ' ' . $this->combineMediaQueries( $this->aUrl['media'], $sMediaQuery ) . ';';
		}

		return '@media ' . $this->aUrl['media'] . '{' . $aMatches[0] . '}';
	}

	/**
	 *
	 * @param   string  $sParentMediaQueries
	 * @param   string  $sChildMediaQueries
	 *
	 * @return string
	 */
	protected function combineMediaQueries( $sParentMediaQueries, $sChildMediaQueries )
	{
		$aParentMediaQueries = preg_split( '#\s++or\s++|,#i', $sParentMediaQueries );
		$aChildMediaQueries  = preg_split( '#\s++or\s++|,#i', $sChildMediaQueries );

		//$aMediaTypes = array('all', 'aural', 'braille', 'handheld', 'print', 'projection', 'screen', 'tty', 'tv', 'embossed');

		$aMediaQuery = array();

		foreach ( $aParentMediaQueries as $sParentMediaQuery )
		{
			$aParentMediaQuery = $this->parseMediaQuery( trim( $sParentMediaQuery ) );

			foreach ( $aChildMediaQueries as $sChildMediaQuery )
			{
				$sMediaQuery = '';

				$aChildMediaQuery = $this->parseMediaQuery( trim( $sChildMediaQuery ) );

				if ( $aParentMediaQuery['keyword'] == 'only' || $aChildMediaQuery['keyword'] == 'only' )
				{
					$sMediaQuery .= 'only ';
				}

				if ( $aParentMediaQuery['keyword'] == 'not' && $sChildMediaQuery['keyword'] == '' )
				{
					if ( $aParentMediaQuery['media_type'] == 'all' )
					{
						$sMediaQuery .= '(not ' . $aParentMediaQuery['media_type'] . ')';
					}
					elseif ( $aParentMediaQuery['media_type'] == $aChildMediaQuery['media_type'] )
					{
						$sMediaQuery .= '(not ' . $aParentMediaQuery['media_type'] . ') and ' . $aChildMediaQuery['media_type'];
					}
					else
					{
						$sMediaQuery .= $aChildMediaQuery['media_type'];
					}
				}
				elseif ( $aParentMediaQuery['keyword'] == '' && $aChildMediaQuery['keyword'] == 'not' )
				{
					if ( $aChildMediaQuery['media_type'] == 'all' )
					{
						$sMediaQuery .= '(not ' . $aChildMediaQuery['media_type'] . ')';
					}
					elseif ( $aParentMediaQuery['media_type'] == $aChildMediaQuery['media_type'] )
					{
						$sMediaQuery .= $aParentMediaQuery['media_type'] . ' and (not ' . $aChildMediaQuery['media_type'] . ')';
					}
					else
					{
						$sMediaQuery .= $aChildMediaQuery['media_type'];
					}
				}
				elseif ( $aParentMediaQuery['keyword'] == 'not' && $aChildMediaQuery['keyword'] == 'not' )
				{
					$sMediaQuery .= 'not ' . $aChildMediaQuery['keyword'];
				}
				else
				{
					if ( $aParentMediaQuery['media_type'] == $aChildMediaQuery['media_type']
					     || $aParentMediaQuery['media_type'] == 'all' )
					{
						$sMediaQuery .= $aChildMediaQuery['media_type'];
					}
					elseif ( $aChildMediaQuery['media_type'] == 'all' )
					{
						$sMediaQuery .= $aParentMediaQuery['media_type'];
					}
					else
					{
						//Two different media types are nested and neither is 'all' then
						//the enclosed rule will not be applied on any media type
						//We put 'not all' to maintain a syntactically correct combined media type
						$sMediaQuery .= 'not all';

						//Don't bother including media features in the media query
						$aMediaQuery[] = $sMediaQuery;
						continue;
					}
				}

				if ( isset( $aParentMediaQuery['expression'] ) )
				{
					$sMediaQuery .= ' and ' . $aParentMediaQuery['expression'];
				}

				if ( isset( $aChildMediaQuery['expression'] ) )
				{
					$sMediaQuery .= ' and ' . $aChildMediaQuery['expression'];
				}

				$aMediaQuery[] = $sMediaQuery;
			}
		}

		return implode( ', ', array_unique( $aMediaQuery ) );
	}

	protected function parseMediaQuery( $sMediaQuery )
	{
		$aParts = array();

		$sMediaQuery = preg_replace( array( '#\(\s++#', '#\s++\)#' ), array( '(', ')' ), $sMediaQuery );
		preg_match( '#(?:\(?(not|only)\)?)?\s*+(?:\(?(all|screen|print|speech|aural|tv|tty|projection|handheld|braille|embossed)\)?)?(?:\s++and\s++)?(.++)?#si',
			$sMediaQuery, $aMatches );

		$aParts['keyword'] = isset( $aMatches[1] ) ? strtolower( $aMatches[1] ) : '';

		if ( isset( $aMatches[2] ) && $aMatches[2] != '' )
		{
			$aParts['media_type'] = strtolower( $aMatches[2] );
		}
		else
		{
			$aParts['media_type'] = 'all';
		}

		if ( isset( $aMatches[3] ) && $aMatches[3] != '' )
		{
			$aParts['expression'] = $aMatches[3];
		}

		return $aParts;
	}
}
