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


use JchOptimize\Core\Css\Parser;
use JchOptimize\Core\Helper;
use JchOptimize\Core\Url;
use JchOptimize\Platform\Uri;


class CorrectUrls extends CallbackBase
{
	public $bHttp2 = false;

	public $bFontOnly = false;

	public $aImages = array();

	public $bBackend = false;

	public function processMatches( $aMatches, $sContext )
	{
		$sRegex = '(?>u?[^u]*+)*?\K(?:' . Parser::CSS_URL_CP( true ) . '|$)';

		if ( $sContext == 'import' )
		{
			$sRegex = Parser::CSS_AT_IMPORT_CP( true );
		}

		return preg_replace_callback( '#' . $sRegex . '#i', function ( $aInnerMatches ) use ( $sContext ) {
			return $this->processInnerMatches( $aInnerMatches, $sContext );
		}, $aMatches[0] );
	}

	protected function processInnerMatches( $aMatches, $sContext )
	{
		if ( empty( $aMatches[0] ) )
		{
			return $aMatches[0];
		}

		$sOriginalImageUrl = trim( $aMatches[1] );

		$sCssFileUrl = empty( $this->aUrl['url'] ) ? '' : $this->aUrl['url'];

		if ( Url::isHttpScheme( $sOriginalImageUrl ) )
		{
			if ( $this->bHttp2 && Url::isInternal( $sOriginalImageUrl, $this->oParams ) )
			{
				$sFileType = $sContext == 'font-face' ? 'font' : 'image';

				if ( $this->bFontOnly && $sFileType != 'font' )
				{
					return false;
				}

				Helper::addHttp2Push( $sOriginalImageUrl, $sFileType );

				return true;
			}

			if ( ( $sCssFileUrl == '' || Url::isInternal( $sCssFileUrl ) ) && Url::isInternal( $sOriginalImageUrl ) )
			{
				$sImageUrl    = Url::toRootRelative( $sOriginalImageUrl, $sCssFileUrl );
				$oImageUri    = clone Uri::getInstance( $sImageUrl );
				$sImageUrlCdn = Helper::cookieLessDomain( $this->oParams, $oImageUri->toString( array( 'path' ) ), $sImageUrl );

				if ( $this->oParams->get( 'cookielessdomain_enable', '0' ) && $sContext == 'font-face' )
				{
					$oUri = clone Uri::getInstance();

					//If image(font) not loaded over CDN
					if ( $sImageUrlCdn == $sImageUrl )
					{
						$sImageUrl = '//' . $oUri->toString( array( 'host', 'port' ) ) .
						             $oImageUri->toString( array(
							             'path',
							             'query',
							             'fragment'
						             ) );
					}
					else
					{
						$sImageUrl = $sImageUrlCdn;
					}
				}
				else
				{
					//If CSS file will be loaded by CDN but image won't, then return absolute url
					if ( $this->oParams->get( 'cookielessdomain_enable', '0' ) && in_array( 'css', Helper::getCdnFileTypes( $this->oParams ) ) && $sImageUrlCdn == $sImageUrl )
					{
						$sImageUrl = Url::toAbsolute( $sImageUrl );
					}
					else
					{
						$sImageUrl = $sImageUrlCdn;
					}
				}

			}
			else
			{
				if ( ! Url::isAbsolute( $sOriginalImageUrl ) )
				{
					$sImageUrl = Url::toAbsolute( $sOriginalImageUrl, $sCssFileUrl );
				}
				else
				{
					return $aMatches[0];
				}
			}

			if ( $this->bBackend && $sContext != 'font-face' )
			{
				$this->aImages[] = $sImageUrl;
			}

			if ( JCH_PRO && $this->oParams->get( 'pro_next_gen_images' ) )
			{
				$sImageUrl = \JchOptimize\Core\Webp::getWebpImages( $sImageUrl );
			}

			// If URL without quotes and contains any parentheses, whitespace characters,
			// single quotes (') and double quotes (") that are part of the URL, quote URL
			if ( strpos( $aMatches[0], 'url(' . $sOriginalImageUrl . ')' ) !== false && preg_match( '#[()\s\'"]#', $sImageUrl ) )
			{
				$sImageUrl = '"' . $sImageUrl . '"';
			}

			return str_replace( $sOriginalImageUrl, $sImageUrl, $aMatches[0] );

		}
		else
		{
			return $aMatches[0];
		}
	}
}
