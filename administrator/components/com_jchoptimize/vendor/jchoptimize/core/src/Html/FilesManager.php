<?php

namespace JchOptimize\Core\Html;

use CodeAlfa\Minify\Html;
use JchOptimize\Core\Browser;
use JchOptimize\Core\DynamicJs;
use JchOptimize\Core\FileRetriever;
use JchOptimize\Core\Helper;
use JchOptimize\Core\Url;
use JchOptimize\Platform\Cache;
use JchOptimize\Platform\Excludes;
use JchOptimize\Platform\Settings;
use JchOptimize\Platform\Uri;
use JchOptimize\Platform\Utility;

/**
 * Handles the exclusion and replacement of files in the HTML based on set parameters
 */
class FilesManager
{
	/** @var FilesManager $oInstance Holds instance of class */
	protected static $oInstance = null;
	/** @var bool $bLoadAsync Indicates if we can load the last javascript files asynchronously */
	public $bLoadJsAsync = true;
	public $bGFontPreloaded;
	/** @var array $aCss Multidimensional array of css files to combine */
	public $aCss = [];
	/** @var array $aJs Multidimensional array of js files to combine */
	public $aJs = [];
	/** @var int $iIndex_js Current index of js files to be combined */
	public $iIndex_js = 0;
	/** @var int $iIndex_css Current index of css files to be combined */
	public $iIndex_css = 0;
	/** @var array $aExcludedJs Javascript matches that will be excluded.
	 *        Will be moved to the bottom of section if not selected in "don't move"
	 */
	public $aExcludedJs = [
		'ieo' => [],
		'peo' => []
	];
	/** @var int $jsExcludedIndex Recorded incremented index of js files when the last file was excluded */
	public $jsExcludedIndex = 0;
	/** @var array $aDefers Javascript files having the defer attribute */
	public $aDefers = [];
	/** @var array $sCriticalJs Array of javascript files/scripts excluded from the Remove Unused Js feature */
	public $aCriticalJs = [];
	public $sFileHash;
	/** @var array $aMatch Current match being worked on */
	public $aMatch;
	/** @var array $aExcludes Multidimensional array of excludes set in the parameters. */
	public $aExcludes = [];
	/** @var \JchOptimize\Platform\Settings $oParams */
	public $oParams;
	/** @var string $sType Type of file being processed (css|js) */
	protected $sType = '';
	/** @var array $aMatches Array of matched elements holding links to CSS/Js files on the page */
	protected $aMatches = [];
	/** @var int $iIndex Current index of matches */
	protected $iIndex = -1;
	/** @var array $aReplacements Array of replacements of matched links */
	protected $aReplacements = [];
	/** @var string $sReplacement String to replace the matched link */
	protected $sReplacement = '';
	/** @var string $sCssExcludeType Type of exclude being processed (peo|ieo) */
	protected $sCssExcludeType = '';
	/** @var string $sJsExcludeType Type of exclude being processed (peo|ieo) */
	protected $sJsExcludeType = '';
	/** @var array          Array of files containing Google Font files */
	protected $aContainsGF = array();
	/** @var array  Array to hold files to check for duplicates */
	protected $aUrls = [];
	protected $oFileRetriever = null;
	protected $sLnEnd;
	protected $sTab;

	/**
	 * Private constructor, need to implement a singleton of this class
	 */
	private function __construct( Settings $oParams )
	{
		$this->oParams = $oParams;

		$this->sLnEnd = Utility::lnEnd();
		$this->sTab   = Utility::tab();

		if ( ! defined( 'JCH_TEST_MODE' ) )
		{
			$oUri            = Uri::getInstance();
			$this->sFileHash = serialize( $this->oParams->getOptions() ) . JCH_VERSION . $oUri->toString( array(
					'scheme',
					'host'
				) );
		}

		//Get array of filenames from cache that imports Google font files
		$aContainsGF = Cache::getCache( 'jch_hidden_containsgf' );
		//If cache is not empty save to class property
		if ( $aContainsGF !== false )
		{
			$this->aContainsGF = $aContainsGF;
		}

	}

	/**
	 * @return FilesManager Returns singleton instance of class
	 */
	public static function getInstance( $oParams )
	{
		if ( is_null( self::$oInstance ) )
		{
			self::$oInstance = new FilesManager( $oParams );
		}

		return self::$oInstance;
	}

	public function setExcludes( $aExcludes )
	{
		$this->aExcludes = $aExcludes;
	}

	public function processFiles( $sType, $aMatch )
	{
		$this->aMatch = $aMatch;
		$this->sType  = $sType;
		$this->iIndex++;
		$this->aMatches[ $this->iIndex ] = $aMatch[0];
		//Initialize replacement
		$this->sReplacement = '';

		try
		{
			if ( trim( $aMatch['url'] ) != '' )
			{
				$this->checkUrls( $aMatch['url'] );
				$this->{'process' . ucfirst( $sType ) . 'Url'}( $aMatch['url'] );
			}
			elseif ( trim( $aMatch['content'] ) != '' )
			{
				$this->{'process' . ucfirst( $sType ) . 'Content'}( $aMatch['content'] );
			}
		}
		catch ( ExcludeException $e )
		{
		}

		return $this->sReplacement;
	}

	private function checkUrls( $sUrl )
	{
		//Exclude invalid urls
		if ( ! Url::isHttpScheme( $sUrl ) && ! Url::isDataUri( $sUrl ) )
		{
			$this->{'exclude' . ucfirst( $this->sType ) . 'IEO'}();
		}
	}

	public function setFileRetriever( FileRetriever $oFileRetriever )
	{
		$this->oFileRetriever = $oFileRetriever;
	}

	/**
	 *  Required for unit testing
	 */
	public function destroy()
	{
		self::$oInstance = null;
	}

	private function processCssUrl( $sUrl )
	{
		//Get media value if attribute set
		$sMedia = $this->getMediaAttribute();

		//process google font files
		if ( JCH_PRO && $this->oParams->get( 'pro_optimize_gfont_enable', '0' )
			&& strpos( $sUrl, 'fonts.googleapis.com' ) !== false )
		{
			$this->sReplacement    = \JchOptimize\Core\GoogleFonts::optimizeFile( $sUrl, $sMedia );
			$this->bGFontPreloaded = true;

			$this->excludeCssIEO();
		}

		//process excludes for css urls
		if ( $this->excludeGenericUrls( $sUrl )
			|| Helper::findExcludes( @$this->aExcludes['excludes_peo']['css'], $sUrl ) )
		{
			$this->excludeCssPEO();
		}

		if ( $this->isDuplicated( $sUrl ) )
		{
			$this->excludeCssIEO();
		}

		$this->prepareCssPEO();
		$this->processSmartCombine( $sUrl );

		$this->aCss[ $this->iIndex_css ][] = [
			'match' => $this->aMatch[0],
			'url'   => $sUrl,
			'media' => $sMedia,
			'id'    => $this->getFileID( $this->aMatch )
		];
	}

	private function getMediaAttribute()
	{
		$sMedia = '';

		if ( ( preg_match( '#media=(?(?=["\'])(?:["\']([^"\']+))|(\w+))#i', $this->aMatch[0], $aMediaTypes ) > 0 ) )
		{
			$sMedia .= $aMediaTypes[1] ? $aMediaTypes[1] : $aMediaTypes[2];
		}

		return $sMedia;
	}

	private function excludeCssIEO()
	{
		$this->sCssExcludeType = 'ieo';

		throw new ExcludeException();
	}

	private function excludeGenericUrls( $sUrl )
	{
		//Exclude unsupported urls
		if ( Url::isDataUri( $sUrl ) || ! $this->isHttpAdapterAvailable( $sUrl )
			|| ( Url::isSSL( $sUrl ) && ! extension_loaded( 'openssl' ) ) )
		{
			return true;
		}

		//Exclude files from external extensions if parameter not set (PEO)
		if ( ! $this->oParams->get( 'includeAllExtensions', '0' ) )
		{
			if ( ! Url::isInternal( $sUrl ) || preg_match( '#' . Excludes::extensions() . '#i', $sUrl ) )
			{
				return true;
			}
		}

		return false;
	}

	public function isHttpAdapterAvailable( $sUrl )
	{
		if ( $this->oParams->get( 'phpAndExternal', '0' ) )
		{
			if ( preg_match( '#^(?:http|//)#i', $sUrl )
				&& ! Url::isInternal( $sUrl )
				|| $this->isPHPFile( $sUrl ) )
			{
				//May be injected during unit testing
				if ( is_null( $this->oFileRetriever ) )
				{
					$this->oFileRetriever = FileRetriever::getInstance();
				}

				return $this->oFileRetriever->isHttpAdapterAvailable();
			}
			else
			{
				return true;
			}
		}
		else
		{
			return ! ( preg_match( '#^(?:http|//)#i', $sUrl ) && ! Url::isInternal( $sUrl )
				|| $this->isPHPFile( $sUrl ) );
		}
	}

	public function isPHPFile( $sUrl )
	{
		return preg_match( '#\.php|^(?![^?\#]*\.(?:css|js|png|jpe?g|gif|bmp)(?:[?\#]|$)).++#i', $sUrl );
	}

	private function excludeCssPEO()
	{
		//if previous file was excluded increment css index
		if ( ! empty( $this->aCss[ $this->iIndex_css ] ) && ! $this->oParams->get( 'optimizeCssDelivery_enable', '0' ) )
		{
			$this->iIndex_css++;
		}

		//Just return the match at same location
		$this->sReplacement    = $this->aMatch[0];
		$this->sCssExcludeType = 'peo';

		throw new ExcludeException();
	}

	/**
	 * Checks if a file appears more than once on the page so that it's not duplicated in the combined files
	 *
	 * @param   string  $sUrl  Url of file
	 *
	 * @return bool        True if already included
	 * @since
	 */
	public function isDuplicated( $sUrl )
	{
		$sUrl   = Uri::getInstance( $sUrl )->toString( array( 'host', 'path', 'query' ) );
		$return = in_array( $sUrl, $this->aUrls );

		if ( ! $return )
		{
			$this->aUrls[] = $sUrl;
		}

		return $return;
	}

	private function prepareCssPEO()
	{
		//return marker for combined file
		if ( empty ( $this->aCss[ $this->iIndex_css ] ) && ! $this->oParams->get( 'optimizeCssDelivery_enable', '0' ) )
		{
			$this->sReplacement = '<JCH_CSS' . $this->iIndex_css . '>';
		}
	}

	private function processSmartCombine( $sUrl )
	{
		if ( $this->oParams->get( 'pro_smart_combine', '0' ) )
		{
			$sType = $this->sType;

			$aSmartCombineValues = $this->oParams->get( 'pro_smart_combine_values', '' );
			$aSmartCombineValues = $aSmartCombineValues != '' ? json_decode( $aSmartCombineValues ) : [];

			//Index of files currently being smart combined
			static $iSmartCombineIndex_js = false;
			static $iSmartCombineIndex_css = false;

			$sBaseUrl = preg_replace( '#[?\#].*+#i', '', $sUrl );

			foreach ( Excludes::smartCombine() as $iIndex => $sRegex )
			{
				if ( preg_match( '#' . $sRegex . '#i', $sUrl ) && in_array( $sBaseUrl, $aSmartCombineValues ) )
				{//We're in a batch
					//Is this the first file in this batch?
					if ( ! empty ( $this->{'a' . ucfirst( $sType )}[ $this->{'iIndex_' . $sType} ] ) && ${'iSmartCombineIndex_' . $sType} !== $iIndex )
					{
						$this->{'iIndex_' . $sType}++;

						if ( $sType == 'css' && $this->sReplacement == '' && ! $this->oParams->get( 'optimizeCssDelivery_enable', '0' ) )
						{
							$this->sReplacement = '<JCH_CSS' . $this->iIndex_css . '>';
						}
					}

					if ( $sType == 'js' )
					{
						$this->bLoadJsAsync = false;
					}

					//Save index
					${'iSmartCombineIndex_' . $sType} = $iIndex;

					break;
				}
				else
				{
					//Have we just finished a batch?
					if ( ${'iSmartCombineIndex_' . $sType} === $iIndex )
					{
						${'iSmartCombineIndex_' . $sType} = false;

						if ( ! empty( $this->{'a' . ucfirst( $sType )}[ $this->{'iIndex_' . $sType} ] ) )
						{
							$this->{'iIndex_' . $sType}++;

							if ( $sType == 'css' && $this->sReplacement == '' && ! $this->oParams->get( 'optimizeCssDelivery_enable', '0' ) )
							{
								$this->sReplacement = '<JCH_CSS' . $this->iIndex_css . '>';
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Generates a cache id for each matched file/script. If the files is associated with Google fonts,
	 * a browser hash is also computed.
	 *
	 *
	 * @param   array  $aMatches  Array of files/scripts matched to be optimized and combined
	 *
	 * @return string                md5 hash for the cache id
	 */
	public function getFileID( $aMatches )
	{
		$id = '';

		if ( defined( 'JCH_TEST_MODE' ) )
		{
			return $id;
		}

		//If name of file present in match set id to filename
		if ( ! empty( $aMatches['url'] ) )
		{
			$id .= $aMatches['url'];

			//If file is a, or imports Google fonts, add browser hash to id
			if ( strpos( $aMatches['url'], 'fonts.googleapis.com' ) !== false
				|| in_array( $aMatches['url'], $this->aContainsGF ) )
			{
				$browser = Browser::getInstance();
				$id      .= $browser->getFontHash();
			}
		}
		else
		{
			//No file name present so just use contents of declaration as id
			$id .= $aMatches['content'];
		}

		return md5( $this->sFileHash . $id );
	}

	/**
	 * @throws ExcludeException
	 */
	private function processCssContent( $sContent )
	{
		$sMedia = $this->getMediaAttribute();

		if ( Helper::findExcludes( @$this->aExcludes['excludes_peo']['css_script'], $sContent )
			|| ! $this->oParams->get( 'inlineStyle', '0' )
			|| $this->oParams->get( 'excludeAllStyles', '0' ) )
		{
			$this->excludeCssPEO();
		}

		$this->prepareCssPEO();

		$this->aCss[ $this->iIndex_css ][] = [
			'match'   => $this->aMatch[0],
			'content' => Html::cleanScript( $sContent, 'css' ),
			'media'   => $sMedia,
			'id'      => $this->getFileID( $this->aMatch )
		];
	}

	/**
	 * @throws ExcludeException
	 */
	private function processJsUrl( $sUrl )
	{
		//Remove js files selected as critical
		if ( JCH_PRO )
		{
			DynamicJs::handleCriticalUrls( $this, $sUrl );
		}


		//Process IEO Excludes for js urls
		if ( Helper::findExcludes( ( @$this->aExcludes['excludes_ieo']['js'] ), $sUrl ) )
		{
			//Push excluded files
			$deferred = $this->isFileDeferred( $this->aMatch[0] );
			Helper::addHttp2Push( $sUrl, 'js', $deferred );

			//Return match if selected as 'don't move'
			if ( Helper::findExcludes( @$this->aExcludes['dontmove']['js'], $sUrl ) )
			{
				$this->sReplacement = $this->aMatch[0];
			}
			//Else add to array of excluded js files
			else
			{
				$this->aExcludedJs['ieo'][] = $this->aMatch[0];
			}

			$this->excludeJsIEO();
		}

		//Remove deferred javascript files and modules and add them to the $aDefers array
		$bIsModule = (bool)preg_match( '#type\s*=\s*[\'"]?module#i', $this->aMatch[0] );
		if ( $this->isFileDeferred( $this->aMatch[0], true )
			|| $bIsModule )
		{
			//Push excluded file
			Helper::addHttp2Push( $sUrl, 'js', true );

			//Add match to defer array
			$this->aDefers[] = [
				'url'      => $sUrl,
				'match'    => $this->aMatch[0],
				'module'   => $bIsModule,
				'nomodule' => (bool)( preg_match( "#\bnomodule\b#i", $this->aMatch[0] ) )
			];
			//We now have to defer the last js file
			$this->bLoadJsAsync = false;

			$this->excludeJsIEO();
		}

		//Exclude js files PEO
		if ( $this->excludeGenericUrls( $sUrl )
			|| Helper::findExcludes( @$this->aExcludes['excludes_peo']['js'], $sUrl ) )
		{
			//push excluded file
			Helper::addHttp2Push( $sUrl, 'js' );

			//prepare js match for excluding PEO
			$this->prepareJsPEO();

			//Return match if selected as "don't move"
			if ( Helper::findExcludes( @$this->aExcludes['dontmove']['js'], $sUrl ) )
			{
				//Need to make sure execution order is maintained
				$this->prepareJsDontMoveReplacement();
			}
			//else add to array of excluded js files
			else
			{
				$this->aExcludedJs['peo'][] = $this->aMatch[0];
			}

			$this->excludeJsPEO();
		}

		if ( $this->isDuplicated( $sUrl ) )
		{
			$this->excludeJsIEO();
		}

		$this->processSmartCombine( $sUrl );

		$this->aJs[ $this->iIndex_js ][] = [
			'match' => $this->aMatch[0],
			'url'   => $sUrl,
			'id'    => $this->getFileID( $this->aMatch )
		];

	}

	public function isFileDeferred( $sScriptTag, $bIgnoreAsync = false )
	{
		$a = Parser::HTML_ATTRIBUTE_CP();

		//Shall we ignore files that also include the async attribute
		if ( $bIgnoreAsync )
		{
			$exclude = "(?!(?>\s*+$a)*?\s*+async\b)";
			$attr    = 'defer';
		}
		else
		{
			$exclude = '';
			$attr    = '(?:defer|async)';
		}

		return preg_match( "#<\w++\b{$exclude}(?>\s*+{$a})*?\s*+{$attr}\b#i", $sScriptTag );
	}

	public function excludeJsIEO()
	{
		$this->sJsExcludeType = 'ieo';

		throw new ExcludeException();
	}

	private function prepareJsPEO()
	{
		//If files were previously added for combine in the current index
		// then place marker for combined file(s) above match marked for exclude
		if ( ! empty( $this->aJs[ $this->iIndex_js ] ) )
		{
			$jsReturn = '';

			for ( $i = $this->jsExcludedIndex; $i <= $this->iIndex_js; $i++ )
			{
				$jsReturn .= '<JCH_JS' . $i . '>' . $this->sLnEnd . $this->sTab;
			}

			$this->aMatch[0] = $jsReturn . $this->aMatch[0];

			//increment index of combined files and record it
			$this->jsExcludedIndex = ++$this->iIndex_js;
		}
	}

	private function prepareJsDontMoveReplacement()
	{
		//We'll need to put all the PEO excluded files above this one
		$this->aMatch[0]    = implode( $this->sLnEnd, $this->aExcludedJs['peo'] ) . $this->sLnEnd . $this->aMatch[0];
		$this->sReplacement = $this->aMatch[0];
		//reinitialize array of PEO excludes
		$this->aExcludedJs['peo'] = [];
	}

	/**
	 * @throws ExcludeException
	 */
	private function excludeJsPEO()
	{
		//Can no longer load last combined file asynchronously
		$this->bLoadJsAsync   = false;
		$this->sJsExcludeType = 'peo';

		throw new ExcludeException();
	}

	/**
	 * @throws ExcludeException
	 */
	private function processJsContent( $sContent )
	{
		//Remove js files selected as critical
		if ( JCH_PRO )
		{
			DynamicJs::handleCriticalScripts( $this, $sContent );
		}

		//process IEO excludes for js scripts
		if ( Helper::findExcludes( @$this->aExcludes['excludes_ieo']['js_script'], $sContent ) )
		{
			//Return match if selected as "don't move"
			if ( Helper::findExcludes( @$this->aExcludes['dontmove']['scripts'], $sContent ) )
			{
				$this->sReplacement = $this->aMatch[0];
			}
			//else add to array of excluded js
			else
			{
				$this->aExcludedJs['ieo'][] = $this->aMatch[0];
			}

			$this->excludeJsIEO();
		}

		//process PEO excludes for js scripts
		if ( Helper::findExcludes( @$this->aExcludes['excludes_peo']['js_script'], $sContent )
			|| ! $this->oParams->get( 'inlineScripts', '0' )
			|| $this->oParams->get( 'excludeAllScripts', '0' ) )
		{
			//prepare js match for excluding PEO
			$this->prepareJsPEO();

			//Return match is selected as dont move
			if ( Helper::findExcludes( @$this->aExcludedJs['dontmove']['scripts'], $sContent ) )
			{
				//Need to make sure execution order is maintained
				$this->prepareJsDontMoveReplacement();
			}
			//else add to array of excluded js
			else
			{
				$this->aExcludedJs['peo'][] = $this->aMatch[0];
			}

			$this->excludeJsPEO();
		}

		$this->aJs[ $this->iIndex_js ][] = [
			'match'   => $this->aMatch[0],
			'content' => Html::cleanScript( $sContent, 'js' ),
			'id'      => $this->getFileID( $this->aMatch )
		];
	}
}