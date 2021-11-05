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

use CodeAlfa\RegexTokenizer\Debug\Debug;
use JchOptimize\Core\Admin\Icons;
use JchOptimize\Core\Css\Processor as CssProcessor;
use JchOptimize\Core\Html\Processor as HtmlProcessor;
use JchOptimize\Core\Css\Sprite\SpriteGenerator;
use JchOptimize\Platform\Cache;
use JchOptimize\Platform\Profiler;
use JchOptimize\Platform\Settings;
use JchOptimize\Platform\Utility;
use CodeAlfa\Minify\Js;
use CodeAlfa\Minify\Css;

/**
 *
 *
 */
class Combiner
{
	use Debug;

	public static $bLogErrors = false;
	public $params = null;
	public $sLnEnd = '';
	public $sTab = '';
	public $bBackend = false;

	/**
	 * Constructor
	 *
	 * @param   Settings       $params
	 * @param   bool           $bBackend
	 */
	public function __construct( Settings $params, $bBackend = false )
	{
		$this->params         = $params;
		$this->bBackend       = $bBackend;

		$this->sLnEnd = Utility::lnEnd();
		$this->sTab   = Utility::tab();
	}

	/**
	 *
	 * @return bool|mixed
	 */
	public function getLogParam()
	{
		if ( self::$bLogErrors == '' )
		{
			self::$bLogErrors = $this->params->get( 'log', 0 );
		}

		return self::$bLogErrors;
	}

	/**
	 * Get aggregated and possibly minified content from js and css files
	 *
	 * @param   array   $aUrlArray  Indexed multidimensional array of urls of css or js files for aggregation
	 * @param   string  $sType      css or js
	 *
	 * @return array   Aggregated (and possibly minified) contents of files
	 * @throws Exception
	 */
	public function getContents( $aUrlArray, $sType )
	{
		JCH_DEBUG ? Profiler::start( 'GetContents - ' . $sType, true ) : null;

		$aResult   = $this->combineFiles( $aUrlArray, $sType );
		$sContents = $this->prepareContents( $aResult['content'] );

		if ( $sType == 'css' )
		{

			if ( $this->params->get( 'csg_enable', 0 ) )
			{
				try
				{
					$oSpriteGenerator = new SpriteGenerator( $this->params );
					$aSpriteCss       = $oSpriteGenerator->getSprite( $sContents );

					if ( ! empty( $aSpriteCss ) && ! empty( $aSpriteCss['needles'] ) && ! empty( $aSpriteCss['replacements'] ) )
					{
						$sContents = str_replace( $aSpriteCss['needles'], $aSpriteCss['replacements'], $sContents );
					}
				}
				catch ( Exception $ex )
				{
					Logger::log( $ex->getMessage(), $this->params );
				}
			}

			$sContents = $aResult['import'] . $sContents;

			if ( function_exists( 'mb_convert_encoding' ) )
			{
				$sContents = '@charset "utf-8";' . $sContents;
			}

		}

		//Save contents in array to store in cache
		$aContents = array(
			'filemtime' => Utility::unixCurrentDate(),
			'etag'      => md5( $sContents ),
			'contents'  => $sContents,
			'gfonts'    => $aResult['gfonts'],
			'images'    => array_unique( $aResult['images'] )
		);

		JCH_DEBUG ? Profiler::stop( 'GetContents - ' . $sType ) : null;

		return $aContents;
	}

	/**
	 * Aggregate contents of CSS and JS files
	 *
	 * @param   array   $aUrlArray  Array of links of files to combine
	 * @param   string  $sType      css|js
	 *
	 * @return array               Aggregated contents
	 * @throws Exception
	 */
	public function combineFiles( $aUrlArray, $sType )
	{
		$aData = array(
			'content' => '',
			'import'  => '',
			'gfonts'  => array(),
			'images'  => array()
		);

		//Iterate through each file/script to optimize and combine
		foreach ( $aUrlArray as $aUrl )
		{
			//Truncate url to less than 40 characters
			$sUrl = Helper::prepareFileUrl( $aUrl, $sType );

			JCH_DEBUG ? Profiler::start( 'CombineFile - ' . $sUrl ) : null;

			//If a cache id is present then cache this individual file to avoid
			//optimizing it again if it's present on another page
			if ( isset( $aUrl['id'] ) && $aUrl['id'] != '' )
			{
				$function = array( $this, 'cacheContent' );
				$args     = array( $aUrl, $sType, true );

				//Optimize and cache file/script returning the optimized content
				$aResult = Cache::getCallbackCache( $aUrl['id'], $function, $args );

				//Append to combined contents
				$aData['content'] .= $this->addCommentedUrl( $sType, $aUrl ) . $aResult['content'] .
				                     $this->sLnEnd . 'DELIMITER';
			}
			else
			{
				//If we're not caching just get the optimized content
				$aResult          = $this->cacheContent( $aUrl, $sType, false );
				$aData['content'] .= $this->addCommentedUrl( $sType, $aUrl ) . $aResult['content'] . '|"LINE_END"|';
			}

			if ( $sType == 'css' )
			{
				$aData['import'] .= $aResult['import'];
				$aData['gfonts'] = array_merge( $aData['gfonts'], $aResult['gfonts'] );
				$aData['images'] = array_merge( $aData['images'], $aResult['images'] );
			}

			JCH_DEBUG ? Profiler::stop( 'CombineFile - ' . $sUrl, true ) : null;
		}

		return $aData;
	}

	/**
	 *
	 * @param   string  $sType
	 * @param   string  $sUrl
	 *
	 * @return string
	 */
	protected function addCommentedUrl( $sType, $sUrl )
	{
		$sComment = '';

		if ( $this->params->get( 'debug', '1' ) )
		{
			if ( is_array( $sUrl ) )
			{
				$sUrl = isset( $sUrl['url'] ) ? $sUrl['url'] : ( ( $sType == 'js' ? 'script' : 'style' ) . ' declaration' );
			}

			$sComment = '|"COMMENT_START ' . $sUrl . ' COMMENT_END"|';
		}

		return $sComment;
	}

	/**
	 * Optimize and cache contents of individual file/script returning optimized content
	 *
	 * @param   array    $aUrl
	 * @param   string   $sType
	 * @param   boolean  $bPrepare
	 *
	 * @return string|string[]|null
	 * @throws Exception
	 */
	public function cacheContent( $aUrl, $sType, $bPrepare )
	{
		//Initialize content string
		$sContent = '';
		$aData    = array();

		//If it's a file fetch the contents of the file
		if ( isset( $aUrl['url'] ) )
		{
			//Convert local urls to file path
			$sPath          = Helper::getFilePath( $aUrl['url'] );
			$oFileRetriever = FileRetriever::getInstance();
			$sContent       .= $oFileRetriever->getFileContents( $sPath );
		}
		else
		{
			//If its a declaration just use it
			$sContent .= $aUrl['content'];
		}

		if ( $sType == 'css' )
		{
			$oCssProcessor = new CssProcessor( $this->params );
			$oCssProcessor->setUrlArray( $aUrl );
			$oCssProcessor->setCss( $sContent );
			$oCssProcessor->formatCss();
			$oCssProcessor->processUrls(false, false, $this->bBackend);
			$oCssProcessor->processMediaQueries();
			$oCssProcessor->processAtRules();

			$sContent = $oCssProcessor->getCss();

			$aData['import'] = $oCssProcessor->getImports();
			$aData['gfonts'] = $oCssProcessor->getGFonts();
			$aData['images'] = $oCssProcessor->getImages();
		}

		if ( $sType == 'js' && trim( $sContent ) != '' )
		{
			if ( $this->params->get( 'try_catch', '1' ) )
			{
				$sContent = $this->addErrorHandler( $sContent, $aUrl );
			}
			else
			{
				$sContent = $this->addSemiColon( $sContent );
			}
		}

		if ( $bPrepare )
		{
			$sContent = $this->minifyContent( $sContent, $sType, $aUrl );
			$sContent = $this->prepareContents( $sContent );
		}

		$aData['content'] = $sContent;

		return $aData;
	}

	/**
	 * Add semi-colon to end of js files if non exists;
	 *
	 * @param   string  $sContent
	 * @param   array   $aUrl
	 *
	 * @return string
	 */
	public function addErrorHandler( $sContent, $aUrl )
	{
		$sContent = 'try {' . $this->sLnEnd . $sContent . $this->sLnEnd . '} catch (e) {' . $this->sLnEnd;
		$sContent .= 'console.error(\'Error in ';
		$sContent .= isset( $aUrl['url'] ) ? 'file:' . $aUrl['url'] : 'script declaration';
		$sContent .= '; Error:\' + e.message);' . $this->sLnEnd . '};';

		return $sContent;
	}

	/**
	 * Add semi-colon to end of js files if non exists;
	 *
	 * @param   string  $sContent
	 *
	 * @return string
	 */
	public function addSemiColon( $sContent )
	{
		$sContent = rtrim( $sContent );

		if ( substr( $sContent, - 1 ) != ';' && ! preg_match( '#\|"COMMENT_START File[^"]+not found COMMENT_END"\|#', $sContent ) )
		{
			$sContent = $sContent . ';';
		}

		return $sContent;
	}

	/**
	 * Minify contents of fil
	 *
	 * @param   string  $sContent
	 * @param   string  $sType
	 * @param   array   $aUrl
	 *
	 * @return string $sMinifiedContent Minified content or original content if failed
	 */
	protected function minifyContent( $sContent, $sType, $aUrl )
	{
		if ( $this->params->get( $sType . '_minify', 0 ) )
		{
			$sUrl = Helper::prepareFileUrl( $aUrl, $sType );

			$sMinifiedContent = trim( $sType == 'css' ? Css::optimize( $sContent ) : Js::optimize( $sContent ) );

			/* @TODO inject Exception class into minifier libraries */
			if ( preg_last_error() !== 0 )
			{
				Logger::log( sprintf( 'Error occurred trying to minify: %s', $sUrl ), $this->params );
				$sMinifiedContent = $sContent;
			}

			$this->_debug( $sUrl, '', 'minifyContent' );

			return $sMinifiedContent;
		}

		return $sContent;
	}

	/**
	 * Remove placeholders from aggregated file for caching
	 *
	 * @param   string  $sContents  Aggregated file contents
	 * @param   bool    $test
	 *
	 * @return string
	 */
	public function prepareContents( $sContents, $test = false )
	{
		$sContents = str_replace(
			array(
				'|"COMMENT_START',
				'|"COMMENT_IMPORT_START',
				'COMMENT_END"|',
				'DELIMITER',
				'|"LINE_END"|'
			),
			array(
				$this->sLnEnd . '/***! ',
				$this->sLnEnd . $this->sLnEnd . '/***! @import url',
				' !***/' . $this->sLnEnd . $this->sLnEnd,
				( $test ) ? 'DELIMITER' : '',
				$this->sLnEnd
			), trim( $sContents ) );

		return $sContents;
	}

	/**
	 * Save filenames of Google fonts or files that import them
	 *
	 *
	 *
	 */
	public function saveHiddenGf( $sUrl )
	{
		//Get array of Google font files from cache
		$containsgf = Cache::get( 'jch_hidden_containsgf' );
	}
}
