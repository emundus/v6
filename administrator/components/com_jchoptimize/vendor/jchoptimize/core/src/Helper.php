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

defined( '_JCH_EXEC' ) or die( 'Restricted access' );

use CodeAlfa\Minify\Js;
use CodeAlfa\Minify\Css;
use CodeAlfa\Minify\Html;
use JchOptimize\Core\Admin\Helper as AdminHelper;
use JchOptimize\Core\Admin\MultiSelectItems;
use JchOptimize\Platform\Cache;
use JchOptimize\Platform\FileSystem;
use JchOptimize\Platform\Settings;
use JchOptimize\Platform\Uri;
use JchOptimize\Platform\Plugin;
use JchOptimize\Platform\Profiler;
use JchOptimize\Platform\Paths;
use JchOptimize\Platform\Utility;

/**
 * Some helper functions
 *
 */
class Helper
{
	/**
	 * Checks if file (can be external) exists
	 *
	 * @param   string  $sPath
	 *
	 * @return boolean
	 */
	public static function fileExists( $sPath )
	{
		if ( ( strpos( $sPath, 'http' ) === 0 ) )
		{
			$sFileHeaders = @get_headers( $sPath );

			return ( $sFileHeaders !== false && strpos( $sFileHeaders[0], '404' ) === false );
		}
		else
		{
			return file_exists( $sPath );
		}
	}

	/**
	 *
	 * @return boolean
	 */
	public static function isMsieLT10()
	{
		//$browser = Browser::getInstance( 'Mozilla/5.0 (Macintosh; Intel Mac OS X10_15_7) AppleWebkit/605.1.15 (KHTML, like Gecko) Version/14.1 Safari/605.1.15' );
		$browser = Browser::getInstance();

		return ( $browser->getBrowser() == 'Internet Explorer' && version_compare( $browser->getVersion(), 10, '<' ) );
	}

	/**
	 *
	 * @param   string  $string
	 *
	 * @return string
	 */
	public static function cleanReplacement( $string )
	{
		return strtr( $string, array( '\\' => '\\\\', '$' => '\$' ) );
	}

	/**
	 * Get local path of file from the url in the HTML if internal
	 * If external or php file, the url is returned
	 *
	 * @param   string  $sUrl  Url of file
	 *
	 * @return string       File path
	 */
	public static function getFilePath( $sUrl )
	{
		$sUriPath = Uri::base( true );

		$oUri = clone Uri::getInstance();
		$oUrl = clone Uri::getInstance( html_entity_decode( $sUrl ) );

		//Use absolute file path if file is internal and a static file
		if ( Url::isInternal( $sUrl ) && ! Url::requiresHttpProtocol( $sUrl ) )
		{
			return Paths::absolutePath( preg_replace( '#^' . preg_quote( $sUriPath, '#' ) . '#', '', $oUrl->getPath() ) );
		}
		else
		{
			$scheme = $oUrl->getScheme();

			if ( empty( $scheme ) )
			{
				$oUrl->setScheme( $oUri->getScheme() );
			}

			$host = $oUrl->getHost();

			if ( empty( $host ) )
			{
				$oUrl->setHost( $oUri->getHost() );
			}

			$path = $oUrl->getPath();

			if ( ! empty( $path ) )
			{
				if ( substr( $path, 0, 1 ) != '/' )
				{
					$oUrl->setPath( $sUriPath . '/' . $path );
				}
			}

			$sUrl = $oUrl->toString();

			$query = $oUrl->getQuery();

			if ( ! empty( $query ) )
			{
				parse_str( $query, $args );

				$sUrl = str_replace( $query, http_build_query( $args, '', '&' ), $sUrl );
			}

			return $sUrl;
		}
	}

	/**
	 *
	 * @return string
	 */
	public static function getBaseFolder()
	{
		return Uri::base( true ) . '/';
	}

	/**
	 *
	 * @param   string  $search
	 * @param   string  $replace
	 * @param   string  $subject
	 *
	 * @return string|string[]
	 */
	public static function strReplace( $search, $replace, $subject )
	{
		return str_replace( self::cleanPath( $search ), $replace, self::cleanPath( $subject ) );
	}

	/**
	 *
	 * @param   string  $str
	 *
	 * @return string|string[]
	 */
	public static function cleanPath( $str )
	{
		return str_replace( array( '\\\\', '\\' ), '/', $str );
	}

	/**
	 * If parameter is set will minify HTML before sending to browser;
	 * Inline CSS and JS will also be minified if respective parameters are set
	 *
	 * @param   string    $sHtml
	 * @param   Settings  $oParams
	 *
	 * @return string                       Optimized HTML
	 */
	public static function minifyHtml( $sHtml, $oParams )
	{
		JCH_DEBUG ? Profiler::start( 'MinifyHtml' ) : null;


		if ( $oParams->get( 'combine_files_enable', '1' ) && $oParams->get( 'html_minify', 0 ) )
		{
			$aOptions = array();

			if ( $oParams->get( 'css_minify', 0 ) )
			{
				$aOptions['cssMinifier'] = array( 'CodeAlfa\Minify\Css', 'optimize' );
			}

			if ( $oParams->get( 'js_minify', 0 ) )
			{
				$aOptions['jsMinifier'] = array( 'CodeAlfa\Minify\Js', 'optimize' );
			}

			$aOptions['jsonMinifier'] = array( 'CodeAlfa\Minify\Json', 'optimize' );
			$aOptions['minifyLevel']  = $oParams->get( 'html_minify_level', 0 );
			$aOptions['isXhtml']      = self::isXhtml( $sHtml );
			$aOptions['isHtml5']      = self::isHtml5( $sHtml );

			$sHtmlMin = Html::optimize( $sHtml, $aOptions );

			if ( $sHtmlMin == '' )
			{
				Logger::log( 'Error while minifying HTML', $oParams );

				$sHtmlMin = $sHtml;
			}

			$sHtml = $sHtmlMin;

			JCH_DEBUG ? Profiler::stop( 'MinifyHtml', true ) : null;
		}

		return $sHtml;
	}

	/**
	 * Determine if document is of XHTML doctype
	 *
	 * @param   string  $sHtml
	 *
	 * @return boolean
	 */
	public static function isXhtml( $sHtml )
	{
		return (bool)preg_match( '#^\s*+(?:<!DOCTYPE(?=[^>]+XHTML)|<\?xml.*?\?>)#i', trim( $sHtml ) );
	}

	/**
	 * Determines if document is of html5 doctype
	 *
	 * @param   string  $sHtml
	 *
	 * @return boolean        True if doctype is html5
	 */
	public static function isHtml5( $sHtml )
	{
		return (bool)preg_match( '#^<!DOCTYPE html>#i', trim( $sHtml ) );
	}

	/**
	 * Splits a string into an array using any regular delimiter or whitespace
	 *
	 * @param   string  $sString  Delimited string of components
	 *
	 * @return array            An array of the components
	 */
	public static function getArray( $sString )
	{
		if ( is_array( $sString ) )
		{
			$aArray = $sString;
		}
		else
		{
			$aArray = explode( ',', trim( $sString ) );
		}

		$aArray = array_map( function ( $sValue ) {
			return trim( $sValue );
		}, $aArray );

		return array_filter( $aArray );
	}

	/**
	 *
	 * @param   string    $url
	 * @param   Settings  $params
	 * @param   array     $posts
	 *
	 * @deprecated
	 *            //Being used in CssSpriteGen
	 */
	public static function postAsync( $url, $params, array $posts )
	{
		$post_params = array();

		foreach ( $posts as $key => &$val )
		{
			if ( is_array( $val ) )
			{
				$val = implode( ',', $val );
			}

			$post_params[] = $key . '=' . urlencode( $val );
		}

		$post_string = implode( '&', $post_params );

		$parts = Helper::parseUrl( $url );

		if ( isset( $parts['scheme'] ) && ( $parts['scheme'] == 'https' ) )
		{
			$protocol     = 'ssl://';
			$default_port = 443;
		}
		else
		{
			$protocol     = '';
			$default_port = 80;
		}

		$fp = @fsockopen( $protocol . $parts['host'], isset( $parts['port'] ) ? $parts['port'] : $default_port, $errno, $errstr, 1 );

		if ( ! $fp )
		{
			Logger::log( $errno . ': ' . $errstr, $params );
			Logger::debug( $errno . ': ' . $errstr, 'JCH_post-error' );
		}
		else
		{
			$out = "POST " . $parts['path'] . '?' . $parts['query'] . " HTTP/1.1\r\n";
			$out .= "Host: " . $parts['host'] . "\r\n";
			$out .= "Content-Type: application/x-www-form-urlencoded\r\n";
			$out .= "Content-Length: " . strlen( $post_string ) . "\r\n";
			$out .= "Connection: Close\r\n\r\n";

			if ( isset( $post_string ) )
			{
				$out .= $post_string;
			}

			fwrite( $fp, $out );
			fclose( $fp );
			Logger::debug( $out, 'JCH_post' );
		}
	}

	/**
	 *
	 * @param   string  $sUrl
	 *
	 * @return array
	 */
	public static function parseUrl( $sUrl )
	{
		preg_match( '#^(?:([a-z][a-z0-9+.-]*+):)?(?://(?:([^:@/]*+)(?::([^@/]*+))?@)?([^:/]++)(?::([^/]*+))?)?([^?\#\n]*+)?(?:\?([^\#\n]*+))?(?:\#(.*+))?$#i', $sUrl, $m );

		$parts = array();

		$parts['scheme']   = ! empty( $m[1] ) ? $m[1] : null;
		$parts['user']     = ! empty( $m[2] ) ? $m[2] : null;
		$parts['pass']     = ! empty( $m[3] ) ? $m[3] : null;
		$parts['host']     = ! empty( $m[4] ) ? $m[4] : null;
		$parts['port']     = ! empty( $m[5] ) ? $m[5] : null;
		$parts['path']     = ! empty( $m[6] ) ? $m[6] : '';
		$parts['query']    = ! empty( $m[7] ) ? $m[7] : null;
		$parts['fragment'] = ! empty( $m[8] ) ? $m[8] : null;

		return $parts;
	}

	/**
	 *
	 * @param   string  $sHtml
	 *
	 * @return false|int
	 */
	public static function validateHtml( $sHtml )
	{
		return preg_match( '#^(?>(?><?[^<]*+)*?<html(?><?[^<]*+)*?<head(?><?[^<]*+)*?</head\s*+>)(?><?[^<]*+)*?'
			. '<body.*</body\s*+>(?><?[^<]*+)*?</html\s*+>#is', $sHtml );
	}


	/**
	 *
	 * @param   Settings  $params
	 */
	public static function clearHiddenValues( Settings $params )
	{
		$params->set( 'hidden_containsgf', '' );
		Plugin::saveSettings( $params );
	}

	/**
	 * Truncate url at the '/' less than 40 characters prepending '...' to the string
	 *
	 * @param   array   $aUrl
	 * @param   string  $sType
	 *
	 * @return string
	 */
	public static function prepareFileUrl( $aUrl, $sType )
	{
		$sUrl = isset( $aUrl['url'] ) ?
			MultiSelectItems::prepareFileValues( $aUrl['url'], '', 40 ) :
			( $sType == 'css' ? 'Style' : 'Script' ) . ' Declaration';

		return $sUrl;
	}

	/**
	 * @param         $sUrl
	 * @param         $sType
	 * @param   bool  $bDeferred
	 *
	 * @return bool|mixed
	 */
	public static function addHttp2Push( $sUrl, $sType, $bDeferred = false )
	{
		$oHttp2Instance = Http2::getInstance();

		if ( $oHttp2Instance->bEnabled )
		{
			$oHttp2Instance->addHttp2Preload( $sUrl, $sType, $bDeferred );
		}
	}

	/**
	 *
	 * @param   array   $aArray
	 * @param   string  $sString
	 * @param   string  $sType
	 *
	 * @return boolean
	 */
	public static function findExcludes( $aArray, $sString, $sType = '' )
	{
		if ( empty( $aArray ) )
		{
			return false;
		}

		foreach ( $aArray as $sValue )
		{
			if ( $sType == 'js' )
			{
				$sString = Js::optimize( $sString );
			}
			elseif ( $sType == 'css' )
			{
				$sString = Css::optimize( $sString );
			}

			if ( $sValue && strpos( htmlspecialchars_decode( $sString ), $sValue ) !== false )
			{
				return true;
			}
		}

		return false;
	}

	public static function cookieLessDomain( $oParams, $path, $orig_path, $domains_only = false, $reset = false )
	{
		//If feature disabled just return the path if present
		if ( ! $oParams->get( 'cookielessdomain_enable', '0' ) && ! $domains_only )
		{
			return $domains_only ? array() : $orig_path;
		}

		$oCdnInstance = Cdn::getInstance( $oParams );

		if ( $domains_only )
		{
			return $oCdnInstance->getCdnDomains();
		}

		//Sprite Generator needs this to remove CDN domains from images to create sprite
		if ( $reset )
		{
			$oCdnInstance->reset( $oParams );

			return true;
		}

		return $oCdnInstance->loadCdnResource( $path, $orig_path );
	}

	public static function getCdnFileTypes( $oParams )
	{
		return Cdn::getInstance( $oParams )->getCdnFileTypes();
	}

	public static function updateNewSettings()
	{
		$params = Plugin::getPluginParams();

		//Some settings have changed
		//Update new settings from the old ones
		$aSettingsMap = array(
			'pro_http2_push_enable' => 'http2_push_enable'
		);

		foreach ( $aSettingsMap as $old => $new )
		{
			if ( ! is_null( $params->get( $old ) ) )
			{
				if ( is_array( $new ) )
				{
					foreach ( $new as $value )
					{
						$params->set( $value, $params->get( $old ) );
					}
				}
				else
				{
					$params->set( $new, $params->get( $old ) );
				}

				$params->remove( $old );
			}
		}

		Plugin::saveSettings( $params );
	}

	public static function extractUrlsFromSrcset( $sSrcset )
	{
		$aStrings = explode( ',', $sSrcset );
		$aUrls    = array_map( function ( $v ) {
			$aUrlString = explode( ' ', trim( $v ) );

			return array_shift( $aUrlString );
		}, $aStrings );

		return $aUrls;
	}
}
