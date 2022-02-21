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

use JchOptimize\Platform\Settings;

defined( '_JCH_EXEC' ) or die( 'Restricted access' );

class Cdn
{
	protected static $oCdnInstance = null;
	public $oParams;
	public $scheme = '';
	protected $aDomains = array();
	protected $aFilePaths = array();
	protected $aCdnFileTypes = array();


	private function __construct( $oParams )
	{
		$this->oParams = $oParams;

		switch ( $oParams->get( 'cdn_scheme', '0' ) )
		{
			case '1':
				$this->scheme = 'http:';
				break;
			case '2':
				$this->scheme = 'https:';
				break;
			case '0':
			default:
				$this->scheme = '';
				break;
		}

		$aDefaultFiles = self::getStaticFiles();
		$aDomain       = array();

		if ( trim( $oParams->get( 'cookielessdomain', '' ) ) != '' )
		{
			$domain1       = $oParams->get( 'cookielessdomain' );
			$sStaticFiles1 = implode( '|', array_merge( $oParams->get( 'staticfiles', $aDefaultFiles ), $oParams->get( 'pro_customcdnextensions', array() ) ) );

			$aDomain[ $this->scheme . $this->prepareDomain( $domain1 ) ] = $sStaticFiles1;
		}

		if ( JCH_PRO )
		{
			\JchOptimize\Core\CdnDomains::addCdnDomains( $this, $aDomain );
		}

		$this->aDomains = $aDomain;

		if ( ! empty( $this->aDomains ) )
		{
			foreach ( $this->aDomains as $cdn_file_types )
			{
				$this->aCdnFileTypes = array_merge( $this->aCdnFileTypes, explode( '|', $cdn_file_types ) );
			}

			$this->aCdnFileTypes = array_unique( $this->aCdnFileTypes );
		}
	}

	/**
	 * Returns array of default static files to load from CDN
	 *
	 *
	 * @return array $aStaticFiles Array of file type extensions
	 */
	public static function getStaticFiles()
	{
		return array( 'css', 'js', 'jpe?g', 'gif', 'png', 'ico', 'bmp', 'pdf', 'webp', 'svg' );
	}

	/**
	 *
	 * @param   string  $domain
	 *
	 * @return string
	 */
	public function prepareDomain( $domain )
	{

		return '//' . preg_replace( '#^(?:https?:)?//|/$#i', '', trim( $domain ) );
	}

	/**
	 * Returns an array of file types that will be loaded by CDN
	 *
	 * @return array $aCdnFileTypes Array of file type extensions
	 */
	public function getCdnFileTypes()
	{
		return $this->aCdnFileTypes;
	}

	/**
	 * @param   Settings  $oParams
	 * @param   string    $path
	 * @param   string    $orig_path
	 * @param   bool      $domains_only
	 * @param   bool      $reset
	 *
	 * @return array|bool|mixed
	 */
	public function loadCdnResource( $path, $orig_path )
	{
		//if no domain is configured abort
		if ( empty( $this->aDomains ) )
		{
			return $orig_path;
		}

		//If we haven't matched a cdn domain to this file yet then find one.
		if ( ! isset( $this->aFilePaths[ $path ] ) )
		{
			$this->aFilePaths[ $path ] = $this->selectDomain( $this->aDomains, $path );
		}

		if ( $this->aFilePaths[ $path ] === false )
		{
			return $orig_path;
		}

		return $this->aFilePaths[ $path ];
	}

	/**
	 *
	 * @staticvar int $iIndex
	 *
	 * @param   array   $aDomain
	 * @param   string  $sPath
	 *
	 * @return bool|string
	 */
	private function selectDomain( &$aDomain, $sPath )
	{
		//If no domain is matched to a configured file type then we'll just return the file
		$sCdnUrl = false;

		for ( $i = 0; count( $aDomain ) > $i; $i++ )
		{
			$sStaticFiles = current( $aDomain );
			$sDomain      = key( $aDomain );
			next( $aDomain );

			if ( current( $aDomain ) === false )
			{
				reset( $aDomain );
			}

			if ( preg_match( '#\.(?>' . $sStaticFiles . ')#i', $sPath ) )
			{
				//Prepend the cdn domain to the file path if a match is found.
				$sCdnUrl = $sDomain . $sPath;

				break;
			}
		}

		return $sCdnUrl;
	}

	public function getCdnDomains()
	{
		return $this->aDomains;
	}

	public function reset( $oParams )
	{
		self::$oCdnInstance = null;

		Cdn::getInstance( $oParams );

		return false;
	}

	public static function getInstance( $oParams )
	{
		if ( is_null( self::$oCdnInstance ) )
		{
			self::$oCdnInstance = new Cdn( $oParams );
		}

		return self::$oCdnInstance;
	}
}