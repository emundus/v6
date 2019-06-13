<?php
/**
 * JCH Optimize - Aggregate and minify external resources for optmized downloads
 * 
 * @author Samuel Marshall <sdmarshall73@gmail.com>
 * @copyright Copyright (c) 2010 Samuel Marshall
 * @license GNU/GPLv3, See LICENSE file
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */
use JchOptimize\Optimize;

defined('_JCH_EXEC') or die('Restricted access');

/**
 * Class to parse HTML and find css and js links to replace, populating an array with matches
 * and removing found links from HTML
 * 
 */
class JchOptimizeParser extends JchOptimizeBase
{

        /** @var string   Html of page */
        public $sHtml = '';

        /** @var array    Array of css or js urls taken from head */
        protected $aLinks = array();
        protected $aUrls  = array();
        protected $oFileRetriever;
	protected $sRegexMarker = 'JCHREGEXMARKER';
	protected $containsgf = array();
        public $params    = null;
        public $sLnEnd    = '';
        public $sTab      = '';
        public $sFileHash = '';
	public $bAmpPage  = false;
	public $iIndex_js    = 0;
	public $iIndex_css   = 0;
	public $bExclude_js  = false;
	public $bExclude_css = false;

	public $sAttributeRegex = '[^\s/"\'=<>]*+(?:\s*=(?:\s*+"[^"]*+"|\s*+\'[^\']*+\'|[^\s>]*+[\s>]))?';
	public $sAttributeValueRegex = '(?:(?<=")[^"]*+|(?<=\')[^\']*+|(?<==)[^\s*+>]*+)'; 

        /**
         * Constructor
         * 
         * @param JRegistry object $params      Plugin parameters
         * @param string  $sHtml                Page HMTL
         */
        public function __construct($oParams, $sHtml, $oFileRetriever)
        {
                $this->params = $oParams;
                $this->sHtml  = $sHtml;

                $this->oFileRetriever = $oFileRetriever;

                $this->sLnEnd = JchPlatformUtility::lnEnd();
                $this->sTab   = JchPlatformUtility::tab();

                if (!defined('JCH_TEST_MODE'))
                {
                        $oUri            = JchPlatformUri::getInstance();
                        $this->sFileHash = serialize($this->params->getOptions()) . JCH_VERSION . $oUri->toString(array('scheme', 'host'));
                }

		//Get array of filenames from cache that imports Google font files
		$containsgf = JchPlatformCache::getCache('jch_hidden_containsgf');
		//If cache is not empty save to class property
		if ($containsgf !== false)
		{
			$this->containsgf = $containsgf;
		}

		$this->bAmpPage = (bool) preg_match('#<html [^>]*?(?:&\#26A1;|amp)(?: |>)#', $sHtml);

                $this->parseHtml();
        }

        /**
         * 
         * @return type
         */
        public function getOriginalHtml()
        {
                return $this->sHtml;
        }

        /**
         * 
         * @return type
         */
        public function cleanHtml()
        {
                $hash = preg_replace(array(
                        $this->getHeadRegex(true),
                        '#' . $this->ifRegex() . '#',
                        '#' . implode('', $this->getJsRegex()) . '#ix',
                        '#' . implode('', $this->getCssRegex()) . '#six'
                        ), '', $this->sHtml);


                return $hash;
        }

        /**
         * 
         */
        public function getHtmlHash()
        {
                $sHtmlHash = '';

                preg_replace_callback('#<(?!/)[^>]++>#i',
                                      function($aM) use (&$sHtmlHash)
                {
                        $sHtmlHash .= $aM[0];

                        return;
                }, $this->cleanHtml(), 200);


                return $sHtmlHash;
        }

        /**
         * Removes applicable js and css links from search area
         * 
         */
        public function parseHtml()
        {
                JCH_DEBUG ? JchPlatformProfiler::start('SetUpExcludes') : null;

                $oParams = $this->params;

                $aCBArgs = array();

                if (!JchOptimizeHelper::isMsieLT10() && $oParams->get('combine_files_enable', '1') && !$this->bAmpPage)
                {
                        loadJchOptimizeClass('JchPlatformExcludes');
			
			//These parameters will be excluded while preserving execution order
                        $aExJsComp  = $this->getExComp($oParams->get('excludeJsComponents_peo', ''));
                        $aExCssComp = $this->getExComp($oParams->get('excludeCssComponents', ''));

                        $aExcludeJs     = JchOptimizeHelper::getArray($oParams->get('excludeJs_peo', ''));
                        $aExcludeCss    = JchOptimizeHelper::getArray($oParams->get('excludeCss', ''));
                        $aExcludeScript = JchOptimizeHelper::getArray($oParams->get('pro_excludeScripts_peo'));
                        $aExcludeStyle  = JchOptimizeHelper::getArray($oParams->get('pro_excludeStyles'));

                        $aExcludeScript = array_map(function($sScript)
                        {
                                return stripslashes($sScript);
                        }, $aExcludeScript);

                        $aCBArgs['excludes']['js']         = array_merge($aExcludeJs, $aExJsComp,
                                                                         array('.com/maps/api/js', '.com/jsapi', '.com/uds', 'typekit.net','cdn.ampproject.org', 'googleadservices.com/pagead/conversion'),



                                                                         JchPlatformExcludes::head('js'));
                        $aCBArgs['excludes']['css']        = array_merge($aExcludeCss, $aExCssComp, JchPlatformExcludes::head('css'));
                        $aCBArgs['excludes']['js_script']  = $aExcludeScript;
                        $aCBArgs['excludes']['css_script'] = $aExcludeStyle;

                        $aCBArgs['removals']['js']  = JchOptimizeHelper::getArray($oParams->get('removeJs', ''));
                        $aCBArgs['removals']['css'] = JchOptimizeHelper::getArray($oParams->get('removeCss', ''));

			//These parameters will be excluded without preserving execution order
                        $aExJsComp_ieo  = $this->getExComp($oParams->get('excludeJsComponents', ''));
                        $aExcludeJs_ieo     = JchOptimizeHelper::getArray($oParams->get('excludeJs', ''));
                        $aExcludeScript_ieo = JchOptimizeHelper::getArray($oParams->get('pro_excludeScripts'));

			$aCBArgs['excludes_ieo']['js'] = array_merge($aExcludeJs_ieo, $aExJsComp_ieo);
			$aCBArgs['excludes_ieo']['js_script'] = $aExcludeScript_ieo;

                        JCH_DEBUG ? JchPlatformProfiler::stop('SetUpExcludes', TRUE) : null;

                        $this->initSearch($aCBArgs);
                }

                $this->getImagesWithoutAttributes();
        }

        /**
         * 
         * @param type $sType
         */
        protected function initSearch($aCBArgs)
        {

                JCH_DEBUG ? JchPlatformProfiler::start('InitSearch') : null;

                $aJsRegex = $this->getJsRegex();
                $j        = implode('', $aJsRegex);

                $aCssRegex = $this->getCssRegex();
                $c         = implode('', $aCssRegex);

                $i  = $this->ifRegex();
                $ns = '<noscript\b[^>]*+>(?><?[^<]*+)*?</noscript\s*+>';
		$sc = '<script\b(?=[^<>]*?(?:type\s*=\s*(?![\'"]?(?:text|application)/javascript)))[^>]*+>(?><?[^<]*+)*?</script\s*+>';

                $sRegex = "#(?>(?:<(?!(?:!--|(?:no)?script\b)))?[^<]*+(?:$i|$ns|$sc|<!)?)*?\K(?:$j|$c|\K$)#six";


                JCH_DEBUG ? JchPlatformProfiler::stop('InitSearch', TRUE) : null;

		$this->searchArea($sRegex, 'head', $aCBArgs);
                
        }

        /**
         * 
         * @param type $sRegex
         * @param type $sType
         * @param type $sSection
         * @param type $aCBArgs
         * @throws Exception
         */
        protected function searchArea($sRegex, $sSection, $aCBArgs)
        {
                JCH_DEBUG ? JchPlatformProfiler::start('SearchArea - ' . $sSection) : null;

                $obj = $this;

                $sProcessedHtml = preg_replace_callback($sRegex,
                                                        function($aMatches) use ($obj, $aCBArgs)
                {
                        return $obj->replaceScripts($aMatches, $aCBArgs);
                }, $this->{'get' . ucfirst($sSection) . 'Html'}());

                if (is_null($sProcessedHtml))
                {
                        throw new Exception(sprintf('Error while parsing for links in %1$s', $sSection));
                }

                $this->{'set' . ucfirst($sSection) . 'Html'}($sProcessedHtml);

                JCH_DEBUG ? JchPlatformProfiler::stop('SearchArea - ' . $sSection, TRUE) : null;
        }

        /**
         * 
         */
        protected function getImagesWithoutAttributes()
        {
                if ($this->params->get('img_attributes_enable', '0'))
                {
                        JCH_DEBUG ? JchPlatformProfiler::start('GetImagesWithoutAttributes') : null;

                        $rx = '#(?><?[^<]*+)*?\K(?:<img\s++(?!(?=(?>[^\s>]*+\s++)*?width\s*+=\s*+["\'][^\'">a-z]++[\'"])'
                                . '(?=(?>[^\s>]*+\s++)*?height\s*+=\s*+["\'][^\'">a-z]++[\'"]))'
                                . '(?=(?>[^\s>]*+\s++)*?src\s*+=(?:\s*+"([^">]*+)"|\s*+\'([^\'>]*+)\'|([^\s>]++)))[^>]*+>|$)#i';

			//find all images without width and height attributes and populate the $m array
                        preg_match_all($rx, $this->getBodyHtml(), $m, PREG_PATTERN_ORDER);

                        $this->aLinks['img'] = array_map(function($a)
                        {
                                return array_slice($a, 0, -1);
                        }, $m);

                        JCH_DEBUG ? JchPlatformProfiler::stop('GetImagesWithoutAttributes', true) : null;
                }
        }

        /**
         * Callback function used to remove urls of css and js files in head tags
         *
         * @param array $aMatches       Array of all matches
         * @return string               Returns the url if excluded, empty string otherwise
         */
        public function replaceScripts($aMatches, $aCBArgs)
        {
                $sUrl = $aMatches['url'] = trim(!empty($aMatches[1]) ? $aMatches[1] : (!empty($aMatches[3]) ? $aMatches[3] : ''));
		$sDeclaration = $aMatches['content'] = !empty($aMatches[2]) ? $aMatches[2] : (!empty($aMatches[4]) ? $aMatches[4] : '');

                if (preg_match('#^<!--#', $aMatches[0])
                        || (JchOptimizeUrl::isInvalid($sUrl) && trim($sDeclaration) == ''))
                {
                        return $aMatches[0];
                }

                $sType = preg_match('#^<script#i', $aMatches[0]) ? 'js' : 'css';

                if ($sType == 'js' && !$this->params->get('javascript', '1'))
                {
                        return $aMatches[0];
                }

                if ($sType == 'css' && !$this->params->get('css', '1'))
                {
                        return $aMatches[0];
                }


                $aExcludes = array();

                if (isset($aCBArgs['excludes']))
                {
                        $aExcludes = $aCBArgs['excludes'];
                }

		if (isset($aCBArgs['excludes_ieo']))
		{
			$aExcludes_ieo = $aCBArgs['excludes_ieo'];
		}

                $aRemovals = array();

               // if (isset($aCBArgs['removals']))
               // {
               //         $aRemovals = $aCBArgs['removals'];
               // }

                $sMedia = '';

                if (($sType == 'css') && (preg_match('#media=(?(?=["\'])(?:["\']([^"\']+))|(\w+))#i', $aMatches[0], $aMediaTypes) > 0))
                {
                        $sMedia .= $aMediaTypes[1] ? $aMediaTypes[1] : $aMediaTypes[2];
                }

                switch (true)
                {
			//These cases are being excluded without preserving execution order
                        case ($sUrl != '' && !JchOptimizeUrl::isHttpScheme($sUrl)):
			case (!empty($sUrl) && !empty($aExcludes_ieo['js']) && JchOptimizeHelper::findExcludes($aExcludes_ieo['js'], $sUrl)):
                        case ($sDeclaration != '' && JchOptimizeHelper::findExcludes($aExcludes_ieo['js_script'], $sDeclaration, 'js')):

				return $aMatches[0];

			//These cases are being excluded while preserving execution order
                        case (($sUrl != '') && !$this->isHttpAdapterAvailable($sUrl)):
                        case ($sUrl != '' && JchOptimizeUrl::isSSL($sUrl) && !extension_loaded('openssl')):
                        case (($sUrl != '') && !empty($aExcludes[$sType]) && JchOptimizeHelper::findExcludes($aExcludes[$sType], $sUrl)):
                        case ($sDeclaration != '' && $this->excludeDeclaration($sType)):
                        case ($sDeclaration != '' && JchOptimizeHelper::findExcludes($aExcludes[$sType . '_script'], $sDeclaration, $sType)):
                        case (($sUrl != '') && $this->excludeExternalExtensions($sUrl)):

				//We want to put the combined js files as low as possible, if files were removed before,
				//we place them just above the excluded files
				if($sType == 'js' && !$this->bExclude_js && !empty($this->aLinks['js']))
				{
					$aMatches[0] = '<JCH_JS' . $this->iIndex_js . '>' . $this->sLnEnd . 
						$this->sTab . $aMatches[0];
				}

                                $this->{'bExclude_' . $sType} = true;

                                return $aMatches[0];

			case (($sUrl != '') && $this->isDuplicated($sUrl)):

                                return '';
				
                        default:
                                $return = '';

				//mark location of first css file
				if($sType == 'css' && empty($this->aLinks['css']) 
					&& !$this->params->get('pro_optimizeCssDelivery_enable', '0'))
				{
					$return = '<JCH_CSS' . $this->iIndex_css . '>';
				}

				//The last file was excluded while preserving execution order
                                if ($this->{'bExclude_' . $sType})
                                {
					//reset Exclude flag
                                        $this->{'bExclude_' . $sType} = false;

					//mark location of next removed css file
					if ($sType == 'css' && !empty($this->aLinks['css'])
						&& !$this->params->get('pro_optimizeCssDelivery_enable', '0'))
					{
						$return = '<JCH_CSS' . ++$this->iIndex_css . '>';
					}

					if ($sType == 'js' && !empty($this->aLinks['js']))
					{
						$this->iIndex_js++;
					}
                                }

                                $array = array();

                                $array['match'] = $aMatches[0];

                                if ($sUrl == '' && trim($sDeclaration) != '')
                                {
                                        $content = JchOptimize\HTML_Optimize::cleanScript($sDeclaration, $sType);

                                        $array['content'] = $content;
                                }
                                else
                                {
                                        $array['url'] = $sUrl;
                                }

                                if ($this->sFileHash != '')
                                {
                                        $array['id'] = $this->getFileID($aMatches);
                                }

                                if ($sType == 'css')
                                {
                                        $array['media'] = $sMedia;
                                }

                                $this->aLinks[$sType][$this->{'iIndex_' . $sType}][] = $array;

                                return $return;
                }
        }

        /**
	 * Generates a cache id for each matched file/script. If the files is associated with Google fonts, 
	 * a browser hash is also computed.
	 * 
	 *
         * @param array $aMatches	Array of files/scripts matched to be optimized and combined
         * @return string		md5 hash for the cache id
         */
        protected function getFileID($aMatches)
        {
                $id = '';

		//If name of file present in match set id to filename
                if (!empty($aMatches['url']))
                {
			$id .= $aMatches['url'];

			//If file is a, or imports Google fonts, add browser hash to id 
                        if (strpos($aMatches['url'], 'fonts.googleapis.com') !== FALSE
                                || in_array($aMatches['url'], $this->containsgf))
                        {
                                $browser = JchOptimizeBrowser::getInstance();
                                $id .= $browser->getFontHash();
                        }
                }
		else
		{
			//No file name present so just use contents of declaration as id
			$id .= $aMatches['content'];
		}

                return md5($this->sFileHash . $id);
        }

        /**
	 * Checks if a file appears more than once on the page so it's not duplciated in the combined files
	 *
	 *
         * @param string $sUrl	Url of file
	 * @return bool  	True if already included
         */
        public function isDuplicated($sUrl)
        {
                $sUrl   = JchOptimizeUrl::AbsToProtocolRelative($sUrl);
                $return = in_array($sUrl, $this->aUrls);

                if (!$return)
                {
                        $this->aUrls[] = $sUrl;
                }

                return $return;
        }

        /**
         * Checks if plugin should exclude third party plugins/modules/extensions
	 * 
	 *
	 * @param string $sPath	Filesystem path of file
	 * @return bool		False will not exclude third party extension
         */
        protected function excludeExternalExtensions($sPath)
        {
                if (!$this->params->get('includeAllExtensions', '0'))
                {
                        return !JchOptimizeUrl::isInternal($sPath) || preg_match('#' . JchPlatformExcludes::extensions() . '#i', $sPath);
                }

                return false;
        }

        /**
         * Generates regex for excluding components set in plugin params
         * 
         * @param string $param
         * @return string
         */
        protected function getExComp($sExComParam)
        {
                $aComponents = JchOptimizeHelper::getArray($sExComParam);
                $aExComp     = array();

                if (!empty($aComponents))
                {
                        $aExComp = array_map(function($sValue)
                        {
                                return $sValue . '/';
                        }, $aComponents);
                }

                return $aExComp;
        }

        /**
         * Fetches class property containing array of matches of urls to be removed from HTML
         * 
         * @return array
         */
        public function getReplacedFiles()
        {
                return $this->aLinks;
        }

        /**
         * Determines if document is of html5 doctype
         * 
         * @return boolean	True if doctype is html5
         */
        public function isHtml5()
        {
                return (bool) preg_match('#^<!DOCTYPE html>#i', trim($this->sHtml));
        }

        /**
         * Retruns regex for content enclosed in conditional IE HTML comments 
	 *
         * @return string	Conditional comments regex
         */
        public static function ifRegex()
        {
                return '<!--(?>-?[^-]*+)*?-->';
        }

        /**
         * 
         * @param type $aAttrs
         * @param type $aExts
         * @param type $bFileOptional
         */
        protected static function urlRegex($aAttrs, $aExts)
        {
                $sAttrs = implode('|', $aAttrs);
                $sExts  = implode('|', $aExts);

                $sUrlRegex = <<<URLREGEX
                (?>  [^\s>]*+\s  )+?  (?>$sAttrs)\s*+=\s*+["']?
                ( (?<!["']) [^\s>]*+  | (?<!') [^"]*+ | [^']*+ )
                                                                        
URLREGEX;

                return $sUrlRegex;
        }

        /**
         * 
         * @param type $sCriteria
         * @return string
         */
        protected static function criteriaRegex($sCriteria)
        {
                $sCriteriaRegex = '(?= (?> [^\s>]*+[\s] ' . $sCriteria . ' )*+  [^\s>]*+> )';

                return $sCriteriaRegex;
        }

        /**
         * 
         */
        public function getJsRegex()
        {
                $aRegex = array();

		$a = $this->sAttributeRegex;
		$u = $this->sAttributeValueRegex;

		$aRegex[0] = "(?:<script\b(?!(?>\s*+$a)*?\s*+type\s*+=\s*+(?![\"']?(?:text|application)/javascript[\"' ]))";
		$aRegex[1] = "(?>\s*+(?!src)$a)*\s*+(?:src\s*+=\s*+[\"']?($u))?[^<>]*+>((?><?[^<]*+)*?)</\s*+script\s*+>)";

                return $aRegex;
        }

        /**
         * 
         * @return string
         */
        public function getCssRegex()
        {
                $aRegex = array();

		$a = $this->sAttributeRegex;
		$u = $this->sAttributeValueRegex;

		$aRegex[0] = "(?:<link\b(?!(?>\s*+$a)*?\s*+(?:itemprop|disabled|type\s*+=\s*+(?![\"']?text/css[\"' ])|rel\s*+=\s*+(?![\"']?stylesheet[\"' ])))";
		$aRegex[1] = "(?>\s*+$a)*?\s*+href\s*+=\s*+[\"']?($u)[^<>]*+>)";
                $aRegex[3] = "|(?:<style\b(?:(?!(?:\stype\s*+=\s*+(?![\"']?text/css[\"' ]))|(?:scoped|amp))[^>])*>((?><?[^<]+)*?)</\s*+style\s*+>)";

                return $aRegex;
        }

        /**
         * Get the search area to be used..head section or body
         * 
         * @param type $sHead   
         * @return type
         */
        public function getBodyHtml()
        {
		if (preg_match($this->getBodyRegex(), $this->sHtml, $aBodyMatches) === false || empty($aBodyMatches))
		{
			throw new Exception('Error occurred while trying to match for search area.'
			. ' Check your template for open and closing body tags');
		}

		return $aBodyMatches[0] . $this->sRegexMarker;
        }

	public function setBodyHtml($sHtml)
	{
		$sHtml = $this->cleanRegexMarker($sHtml);
		$this->sHtml = preg_replace($this->getBodyRegex(), JchOptimizeHelper::cleanReplacement($sHtml), $this->sHtml, 1);	
	}

	public function getFullHtml()
	{
		return $this->sHtml . $this->sRegexMarker;
	}

	public function setFullHtml($sHtml)
	{
		$this->sHtml = $this->cleanRegexMarker($sHtml);
	}

        
        
}
