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

use JchOptimize\Platform\Plugin;

// No direct access
defined( '_JCH_EXEC' ) or die( 'Restricted access' );

class Http2
{
	protected static $oHttp2Instance = null;
	public $bEnabled = false;
	public $oParams;
	protected $aPreloads = array();

	private function __construct( $oParams )
	{
		$this->oParams = $oParams;

		if ( $oParams->get( 'http2_push_enable', '0' ) )
		{
			$this->bEnabled = true;
		}
	}

	public static function getInstance()
	{
		if ( is_null( self::$oHttp2Instance ) )
		{
			$oHttp2 = new Http2( Plugin::getPluginParams() );
			$oHttp2->addIncludesToPreload();

			self::$oHttp2Instance = $oHttp2;
		}

		return self::$oHttp2Instance;
	}

	public function addIncludesToPreload()
	{
		if ( JCH_PRO )
		{
			Http2Excludes::addHttp2Includes( $this );
		}
	}

	public function addHttp2Preload( $sUrl, $sType, $bDeferred = false )
	{
		//Avoid invalid urls
		if ( $sUrl == '' || Url::isDataUri( trim( $sUrl ) ) )
		{
			return false;
		}

		if ( JCH_PRO )
		{
			if ( Http2Excludes::findHttp2Excludes( $this, $sUrl, $bDeferred ) )
			{
				return false;
			}
		}

		//Skip external files
		if ( ! Url::isInternal( $sUrl, $this->oParams ) )
		{
			return false;
		}

		if ( $this->oParams->get( 'cookielessdomain_enable', '0' ) )
		{
			static $sCdnFileTypesRegex = '';

			if ( empty( $sCdnFileTypesRegex ) )
			{
				$sCdnFileTypesRegex = implode( '|', Cdn::getInstance( $this->oParams )->getCdnFileTypes() );
			}

			//If this file type will be loaded by CDN don't push if option not set
			if ( $sCdnFileTypesRegex != '' && preg_match( '#\.(?>' . $sCdnFileTypesRegex . ')#i', $sUrl )
				&& ! $this->oParams->get( 'pro_http2_push_cdn', '0' )
			)
			{
				return false;
			}
		}

		if ( $sType == 'image' )
		{
			static $no_image = 0;

			if ( $no_image++ > 5 )
			{
				return false;
			}
		}

		if ( $sType == 'js' )
		{
			static $no_js = 0;

			if ( $no_js++ > 5 )
			{
				return false;
			}

			$sType = 'script';
		}

		if ( $sType == 'css' )
		{
			static $no_css = 0;

			if ( $no_css++ > 5 )
			{
				return false;
			}

			$sType = 'style';
		}

		if ( ! in_array( $sType, $this->oParams->get( 'pro_http2_file_types', array(
			'style',
			'script',
			'font',
			'image'
		) ) ) )
		{
			return false;
		}

		if ( $sType == 'font' )
		{
			//Only push fonts of type woff/woff2
			if ( preg_match( "#\.\K(?:woff2?|ttf)(?=$|[\#?])#", $sUrl, $m ) == '1' )
			{
				static $no_font = 0;

				if ( $no_font++ > 10 )
				{
					return false;
				}

				$this->addToPreload( $sUrl, $sType, $m[0] );
			}
			else
			{
				return false;
			}
		}
		else
		{
			//Populate preload variable
			$this->addToPreload( $sUrl, $sType );

		}
	}

	/**
	 * @param   string  $sUrl
	 * @param   string  $type
	 * @param   string  $ext
	 */
	private function addToPreload( $sUrl, $type, $ext = '' )
	{
		$RR_url  = html_entity_decode( $sUrl );
		$preload = "<{$RR_url}>; rel=preload; as={$type}";

		if ( $type == 'font' )
		{
			$preload .= '; crossorigin';

			switch ( $ext )
			{
				case 'ttf':
					$preload .= '; type="font/ttf"';

					//If we already have the woff or woff2 version, abort
					if ( in_array( preg_replace( '#(?<=font/|\.)ttf#', 'woff', $preload ), $this->aPreloads ) ||
						in_array( preg_replace( '#(?<=font/|\.)ttf#', 'woff2', $preload ), $this->aPreloads ) )
					{
						return false;
					}

					break;
				case 'woff':
					$preload .= '; type="font/woff"';

					//If we already have the woff2 version of this file, abort
					if ( in_array( preg_replace( '#(?<=font/|\.)woff#', 'woff2', $preload ), $this->aPreloads ) )
					{
						return false;
					}
					//if we already have the ttf version of this file, let's remove
					//it and preload the woff version instead
					$ttf = preg_replace( '#(?<=font/|\.)woff#', 'ttf', $preload );
					$key = array_search( $ttf, $this->aPreloads );

					if ( $key !== false )
					{
						unset( $this->aPreloads[ $key ] );
					}

					break;
				case 'woff2':
					$preload .= '; type="font/woff2"';

					//If we already have the woff version of this file,
					// let's remove it and preload the woff2 version instead
					$woff     = preg_replace( '#(?<=font/|\.)woff2#', 'woff', $preload );
					$woff_key = array_search( $woff, $this->aPreloads );

					if ( $woff_key !== false )
					{
						unset( $this->aPreloads[ $woff_key ] );
					}

					//If we already have the ttf version of this file,
					//let's remove it also
					$ttf     = preg_replace( '#(?<=font/|\.)woff2#', 'ttf', $preload );
					$ttf_key = array_search( $ttf, $this->aPreloads );

					if ( $ttf_key !== false )
					{
						unset( $this->aPreloads[ $ttf_key ] );
					}

					break;
				default:
					break;
			}

		}


		if ( ! in_array( $preload, $this->aPreloads ) )
		{
			$this->aPreloads[] = $preload;
		}

		return true;
	}

	public function getPreloads()
	{
		return $this->aPreloads;
	}

}