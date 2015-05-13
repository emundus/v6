<?php
/*
 *  Administrator Tools
 *  Copyright (C) 2010-2013  Nicholas K. Dionysopoulos / AkeebaBackup.com
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('_JEXEC') or die();

JLoader::import('joomla.application.plugin');

if (!function_exists('fnmatch')) {
	function fnmatch($pattern, $string) {
		return @preg_match(
                '/^' . strtr(addcslashes($pattern, '/\\.+^$(){}=!<>|'),
		array('*' => '.*', '?' => '.?')) . '$/i', $string
		);
	}
}

class plgSystemAdmintoolsCore extends JPlugin
{
	/* When set to true, the cache files will be regenerated every time */
	const DEBUG = false;

	/** @var string Combine feature's cache directory */
	private $combineCache = null;

	/** @var object JavaScript merge parameters */
	private $JSparams = null;

	/** @var array An array holding the JS files to replace in the output*/
	private $jsFiles = array();

	/** @var object CSS merge parameters */
	private $CSSparams = array();

	/** @var array An array holding the CSS files to replace in the output*/
	private $cssFiles = array();

	/** @var AdminToolsModelStorage The component parameters store */
	private $cparams = null;

	/** @var string The absolute base URL of the site */
	private $baseURL = null;

	static public $myself = null;

	public static function &fetchMyself()
	{
		return self::$myself;
	}

	public function __construct(& $subject, $config = array())
	{
		JLoader::import('joomla.html.parameter');
		JLoader::import('joomla.plugin.helper');
		JLoader::import('joomla.application.component.helper');
		$plugin = JPluginHelper::getPlugin('system', 'admintools');
		$defaultConfig = (array)($plugin);

		$config = array_merge($defaultConfig, $config);

		// Use the parent constructor to create the plugin object
		parent::__construct($subject, $config);

		// Load the components parameters
		JLoader::import('joomla.application.component.model');
		require_once JPATH_ROOT.'/administrator/components/com_admintools/models/storage.php';
		if(interface_exists('JModel')) {
			$this->cparams = JModelLegacy::getInstance('Storage','AdmintoolsModel');
		} else {
			$this->cparams = JModel::getInstance('Storage','AdmintoolsModel');
		}

		// Get the combine feature's cache directory
		$combinecache = $this->cparams->getValue('combinecache', null);
		if(empty($combinecache)) {
			$combinecache = JPATH_CACHE;
		} else {
			JLoader::import('joomla.filesystem.folder');
			if(!JFolder::exists($combinecache)) {
				$combinecache = JPATH_ROOT.'/'.ltrim($combinecache,'/'.DIRECTORY_SEPARATOR);
			}
			if(!JFolder::exists($combinecache)) {
				$combinecache = JPATH_CACHE;
			}
		}
		$this->combineCache = $combinecache;

		// Do we have to deliver a file?
		$hash = JRequest::getCmd('fetchcombinedfile',null);
		if(!empty($hash))
		{
			$this->deliverFile($hash);
		}

		self::$myself = $this;
	}

	public function onAfterInitialise()
	{
		$app = JFactory::getApplication();

		if(in_array($app->getName(),array('administrator','admin'))) {
			// Back-end stuff
		} else {
			// Front-end stuff
		}
	}

	public function onBeforeRender()
	{
		$app = JFactory::getApplication();
		$app->registerEvent('onAfterRender', 'AdminToolsLateBoundAfterRender');
	}

	public function onAfterRenderLatebound()
	{
		$app = JFactory::getApplication();
		if(in_array($app->getName(),array('administrator','admin'))) return;

		// Link Migration - rewrite links pointing to the old domain name of the site
		if($this->cparams->getValue('linkmigration',0) == 1) {
			$this->linkMigration();
		}

		// HTTPSizer - convert all links to HTTPS
		if($this->cparams->getValue('httpsizer',0) == 1) {
			$this->httpsizer();
		}

		// CSS and JS combination
		$this->onCssJsCombine();
	}

	public function onCssJsCombine()
	{
		$app = JFactory::getApplication();
		if(in_array($app->getName(),array('administrator','admin'))) return;

		// Get the site's base URI
		$base = JURI::base();
		if( $app->getName() == 'administrator' ) {
			$base = rtrim($base,'/');
			$base_pieces = @explode('/',$base);
			array_pop($base_pieces);
			$base = @implode('/', $base_pieces).'/';
		}
		$this->baseURL = $base;

		// Javascript Combine - Combines JavaScript files on the page to one, big file.
		if(
			($this->cparams->getValue('jscombine',0) == 1)
			&& (JFactory::getDocument()->getType() == 'html')
		) {
			// Initialise parameters
			$this->JSparams = new JObject();

			// Fetch the files to skip when combining
			$skip = $this->cparams->getValue('jsskip', '');
			if(empty($skip)) {
				$skip = array();
			} else {
				$skip = str_replace("\r", "", $skip);
				$skip = explode("\n", $skip);
			}
			$this->JSparams->set('skip', $skip);

			// Set the delivery method for combined files
			$delivery = $this->cparams->getValue('jsdelivery', 'plugin');
			if(!in_array($delivery, array('plugin','direct'))) $delivery = 'plugin';
			$this->JSparams->set('delivery', $delivery);

			// Get the cache folder to use
			$this->JSparams->set('cache', $this->combineCache);

			// Finally, get the signature...
			$signature = md5(serialize($this->JSparams));
			$this->JSparams->set('signature', $signature);
			// ...and combine Javascript
			$this->jscombine();
		}

		// CSS Combine - Combines CSS files on the page to one, big file.
		if(
			($this->cparams->getValue('csscombine',0) == 1)
			&& (JFactory::getDocument()->getType() == 'html')
		) {
			// Initialise parameters
			$this->CSSparams = new JObject();

			if(!class_exists('aCssToken')) {
				require_once 'cssmin.php';
			}

			// Fetch the files to skip when combining
			$skip = $this->cparams->getValue('cssskip', '');
			if(empty($skip)) {
				$skip = array();
			} else {
				$skip = str_replace("\r", "", $skip);
				$skip = explode("\n", $skip);
			}
			$this->CSSparams->set('skip', $skip);

			// Set the delivery method for combined files
			$delivery = $this->cparams->getValue('cssdelivery', 'plugin');
			if(!in_array($delivery, array('plugin','direct'))) $delivery = 'plugin';
			$this->CSSparams->set('delivery', $delivery);

			// Get the cache folder to use
			$this->CSSparams->set('cache', $this->combineCache);

			// Finally, get the signature...
			$signature = md5(serialize($this->CSSparams));
			$this->CSSparams->set('signature', $signature);
			// ...and combine Javascript
			$this->csscombine();
		}
	}

	/**
	 * Provides link migration services. All absolute links pointing to any of the old domain names
	 * are being rewritten to point to the current domain name. This runs a full page replacement
	 * using Regular Expressions, so even menus with absolute URLs will be migrated!
	 */
	private function linkMigration()
	{
		$buffer = JResponse::getBody();

		$pattern = '/(href|src)=\"([^"]*)\"/i';
		$number_of_matches = preg_match_all($pattern, $buffer, $matches, PREG_OFFSET_CAPTURE);

		if($number_of_matches > 0) {
			$substitutions = $matches[2];
			$last_position = 0;
			$temp = '';

			// Loop all URLs
			foreach($substitutions as &$entry)
			{
				// Copy unchanged part, if it exists
				if($entry[1] > 0)
					$temp .= substr($buffer, $last_position, $entry[1]-$last_position);
				// Add the new URL
				$temp .= $this->replaceDomain($entry[0]);
				// Calculate next starting offset
				$last_position = $entry[1] + strlen($entry[0]);
			}
			// Do we have any remaining part of the string we have to copy?
			if($last_position < strlen($buffer))
				$temp .= substr($buffer, $last_position);
			// Replace content with the processed one
			unset($buffer);
			JResponse::setBody($temp);
			unset($temp);
		}
	}

	/**
	 * Replaces a URL's domain name (if it is in the substitution list) with the
	 * current site's domain name
	 * @param $url string The URL to process
	 * @return string The processed URL
	 */
	private function replaceDomain($url)
	{
		static $old_domains;
		static $mydomain;

		if(empty($old_domains))
		{
			$temp = explode("\n", $this->cparams->getValue('migratelist',''));
			if(!empty($temp))
			{
				foreach($temp as $entry)
				{
					if(substr($entry,-1) == '/') $entry = substr($entry,0,-1);
					if(substr($entry,0,7) == 'http://') $entry = substr($entry,7);
					if(substr($entry,0,8) == 'https://') $entry = substr($entry,8);
					$old_domains[] = $entry;
				}
			}
		}
		if(empty($mydomain))
		{
			$mydomain = JURI::base(false);
			if(substr($mydomain,-1) == '/') $mydomain = substr($mydomain,0,-1);
			if(substr($mydomain,0,7) == 'http://') $mydomain = substr($mydomain,7);
			if(substr($mydomain,0,8) == 'https://') $mydomain = substr($mydomain,8);
		}

		if(!empty($old_domains))
			foreach($old_domains as $domain)
			{
				if (substr($url, 0, strlen($domain)) == $domain)
				{
					return $mydomain.substr($url, strlen($domain));
				} elseif (substr($url, 0, strlen($domain)+7) == 'http://'.$domain)
				{
					return 'http://'.$mydomain.substr($url, strlen($domain)+7);
				} elseif (substr($url, 0, strlen($domain)+8) == 'https://'.$domain)
				{
					return 'https://'.$mydomain.substr($url, strlen($domain)+8);
				}
			}

		return $url;
	}


	/**
	 * Converts all HTTP URLs to HTTPS URLs when the site is accessed over SSL
	 */
	private function httpsizer()
	{
		// Make sure we're accessed over SSL (HTTPS)
		$uri = JURI::getInstance();
		$protocol = $uri->toString(array('scheme'));
		if($protocol != 'https://') return;

		$buffer = JResponse::getBody();
		$buffer = str_replace('http://','https://',$buffer);
		JResponse::setBody($buffer);
	}

	private function jscombine()
	{
		// Get the HTML content from the JResponse class
		$body = JResponse::getBody();

		// Load Joomla! classes
		JLoader::import('joomla.filesystem.file');

		// Parse JavaScript separators
		$this->jsFiles = array();
		$scriptRegex="/<script [^>]+(\/>|><\/script>)/i";
	 	$jsRegex="/([^\"\'=]+\.(js)(\?[^\"\']*){0,1})[\"\']/i";
	 	preg_match_all($scriptRegex, $body, $matches);
	 	$scripts=@implode('',$matches[0]);
	 	preg_match_all($jsRegex,$scripts,$matches);
	 	foreach( $matches[1] as $url )
		{
			$file = $url; // Clone the string, as it gets modified
			$replace = true;
			if($this->isInternal($file))
			{
				// Separate any URL query
				$qmPos = strpos($file,'?');
				if( $qmPos !== false )
				{
					// Strip the query
					$query = substr($file, $qmPos+1);
					$file = substr($file, 0, $qmPos);
				}
				else
				{
					$query = '';
				}

				// Do not add dynamicaly generated files (.php)
				if( substr( strtolower($file), -4 ) == '.php' ) {
					$replace = false;
				};

				// Do not add any file in the skip list
				$skips = $this->JSparams->get('skip','');
				if(!empty($skips)) foreach($skips as $skip) {
					$skip = trim($skip,'/');
					if(fnmatch($skip, $file)) {
						$replace = false;
						break;
					};
				}

				// Make sure the file exists
				if( !JFile::exists(JPATH_ROOT.DIRECTORY_SEPARATOR.$file) ) {
					$replace = false;
				}

				// Make sure the file is readable
				if( !@is_readable(JPATH_ROOT.DIRECTORY_SEPARATOR.$file) ) {
					$replace = false;
				}

				// Try to get the date and file size
				$date = @filectime($file);
				$size = @filesize($file);

				if((int)$size == 0) $size = PHP_INT_MAX;

				// Calculate a unique file hash
				$hash = md5($file.$query.$date.$size);

				$this->jsFiles[] = (object)array(
					'url'		=> $url,
					'file'		=> $file,
					'hash'		=> $hash,
					'replace'	=> $replace,
					'size'		=> $size,
					'internal'	=> true,
				);
			} else {
				$this->jsFiles[] = (object)array(
					'url'		=> $url,
					'file'		=> null,
					'hash'		=> null,
					'replace'	=> $replace,
					'size'		=> 0,
					'internal'	=> false,
				);
			}
		}

		// Create unique hash of all JS files
		if(count($this->jsFiles))
		{
			$hashable = '';
			foreach($this->jsFiles as $js)
			{
				if(!$js->internal) continue;
				if(!$js->replace) continue;
				$hashable .= $js->hash;
			}
			$jsHash = md5($hashable.$this->JSparams->get('signature',''));
		}

		// Does the cache file exist?
		if(!JFile::exists($this->combineCache.DIRECTORY_SEPARATOR.'js-'.$jsHash.'.js') || self::DEBUG)
		{
			// Nope. Let's create the file.
			$hashable = '';
			$jsContent = "";
			foreach($this->jsFiles as $file)
			{
				if(!$file->internal) continue;
				if(!$file->replace) continue;

				$myContent = JFile::read(JPATH_ROOT.DIRECTORY_SEPARATOR.$file->file, false, $file->size);
				if($myContent === false) {
					$file->replace = false;
					continue;
				}
				$basename = basename($file->file);
				$jsContent .= "\n\n/* COMBINED FILE {$file->url} */\n\n";
				$jsContent .= $myContent."\n";
			}
			// Write the cache file
			JFile::write($this->combineCache.DIRECTORY_SEPARATOR.'js-'.$jsHash.'.js', $jsContent);
		}

		// Replace the JS files
		$body=preg_replace_callback($scriptRegex,array($this,'replaceJS'),$body);
		if($this->JSparams->get('delivery','') == 'plugin') {
			$newURL = $this->baseURL.'index.php?fetchcombinedfile=js-'.$jsHash;
		} else {
			$myCache = str_replace(DIRECTORY_SEPARATOR, '/', @realpath($this->combineCache));
			$myRoot = str_replace(DIRECTORY_SEPARATOR, '/', @realpath(JPATH_ROOT));
			$rootLen = strlen($myRoot);
			if(substr($myCache, 0, $rootLen) == $myRoot) {
				$myCache = trim(substr($myCache, $rootLen),'/');
			}
			$newURL = rtrim($this->baseURL,'/').'/'.$myCache.'/js-'.$jsHash.'.js';
		}

		$newHeadCode = '</title>';
		foreach($this->jsFiles as $js) {
			if(!$js->internal || !$js->replace) {
				$newHeadCode .= "\n<script type=\"text/javascript\" src=\"{$js->url}\"></script>";
			}
		}
		$newHeadCode .="\n".'<script type="text/javascript" src="'.$newURL.'"></script>'."\n";

		//only match once
		$body = preg_replace('/<\/title>/i',$newHeadCode , $body,1);
		JResponse::setBody($body);
	}

	private function csscombine()
	{
		// Get the HTML content from the JResponse class
		$body = JResponse::getBody();

		// Load Joomla! classes
		JLoader::import('joomla.filesystem.file');

		// Parse JavaScript separators
		$this->cssFiles = array();
		$conditionRegex="/<\!--\[if.*?\[endif\]-->/is";
	 	$linksRegex="|<link[^>]+[/]?>((.*)</[^>]+>)?|U";
		$cssRegex="/([^\"\'=]+\.(css)(\?[^\"\']*){0,1})[\"\']/i";

		// Banned files from being minified
		$cssBanList = array();
		// Parse conditional CSS files
		preg_match_all($conditionRegex,$body,$conditonMatches);
		if(!empty($conditonMatches)){
	 		preg_match_all($linksRegex,@implode('',$conditonMatches[0]),$conditionCss);
	 		if(!empty($conditionCss[0])){
	 			preg_match_all($cssRegex,@implode('',$conditionCss[0]),$conditionCssFiles);
	 			if(!empty($conditionCssFiles[1])){
	 				foreach($conditionCssFiles[1] as $conditionalCss){
	 					$url = trim($conditionalCss);
						$isInternal = $this->isInternal($url);
						if($isInternal)
						{
							$cssBanList[]=$url;
						}
	 				}
	 			}
	 		}
	 	}

		// Only parse the CSS files for "all" and "screen" media (or those which
		// do not define a media selector, implying "all")
		preg_match_all($linksRegex, $body, $matches);
		$links=$matches[0];
		$allCSSLinks = array();
		$mediaRegex="|media[\s]*=\"([a-z,\s]*)\"|U";
		foreach($links as $linkCode) {
			// Make sure it's a CSS file
			preg_match_all($cssRegex,$linkCode,$matches);
			if(empty($matches[0])) continue;

			// Get URL
			$url = array_pop($matches[1]);

			// Get media
			preg_match_all($mediaRegex,$linkCode,$matches);

			if(!empty($matches[1])) {
				$allMediaString = array_pop($matches[1]);
				$media = explode(',', $allMediaString);
				$skip = true;
				foreach($media as $medium) {
					$medium = trim($medium);
					if(in_array($medium, array( 'screen','all' ))) $skip = false;
				}
			} else {
				$allMediaString = 'all';
				$skip = false;
			}

			if($skip) continue;

			// Add to array
			$allCSSLinks[$url] = $allMediaString;
		}

		foreach( $allCSSLinks as $url => $allMediaString )
		{
			$file = $url; // Clone the string, as it gets modified
			$replace = true;
			if($this->isInternal($file))
			{
				// Separate any URL query
				$qmPos = strpos($file,'?');
				if( $qmPos !== false )
				{
					// Strip the query
					$query = substr($file, $qmPos+1);
					$file = substr($file, 0, $qmPos);
				}
				else
				{
					$query = '';
				}

				// Do not add dynamicaly generated files (.php)
				if( substr( strtolower($file), -4 ) == '.php' ) {
					$replace = false;
				};

				// Do not add any file in the skip list
				$skips = $this->CSSparams->get('skip','');
				if(!empty($skips)) foreach($skips as $skip) {
					$skip = trim($skip,'/');
					if(fnmatch($skip, $file)) {
						$replace = false;
						break;
					};
				}

				// Make sure the file exists
				if( !JFile::exists(JPATH_ROOT.DIRECTORY_SEPARATOR.$file) ) {
					$replace = false;
				}

				// Make sure the file is readable
				if( !@is_readable(JPATH_ROOT.DIRECTORY_SEPARATOR.$file) ) {
					$replace = false;
				}

				// Try to get the date and file size
				$date = @filectime($file);
				$size = @filesize($file);

				if((int)$size == 0) $size = PHP_INT_MAX;

				// Calculate a unique file hash
				$hash = md5($file.$query.$date.$size);

				// Some files must not be replaced
				if( in_array($file, $cssBanList) ) {
					$replace = false;
					$skip = true;
				} else {
					$skip = false;
				}

				$this->cssFiles[] = (object)array(
					'url'		=> $url,
					'file'		=> $file,
					'hash'		=> $hash,
					'replace'	=> $replace,
					'size'		=> $size,
					'skip'		=> $skip,
					'internal'	=> true,
					'media'		=> $allMediaString,
				);
			} else {
				if( in_array($url, $cssBanList) ) {
					$replace = false;
					$skip = true;
				} else {
					$skip = false;
				}

				$this->cssFiles[] = (object)array(
					'url'		=> $url,
					'file'		=> null,
					'hash'		=> null,
					'replace'	=> $replace,
					'size'		=> 0,
					'skip'		=> $skip,
					'internal'	=> false,
					'media'		=> $allMediaString,
				);
			}
		}

		// Create unique hash of all JS files
		if(count($this->cssFiles))
		{
			$hashable = '';
			foreach($this->cssFiles as $css)
			{
				if($css->skip) continue;
				if(!$css->internal) continue;
				if(!$css->replace) continue;
				$hashable .= $css->hash;
			}
			$cssHash = md5($hashable.$this->CSSparams->get('signature',''));
		}

		// Does the cache file exist?
		if(!JFile::exists($this->combineCache.DIRECTORY_SEPARATOR.'css-'.$cssHash.'.css') || self::DEBUG)
		{
			// Nope. Let's create the file.
			$hashable = '';
			$cssContent = "";
			foreach($this->cssFiles as $file)
			{
				if($file->skip) continue;
				if(!$file->internal) continue;
				if(!$file->replace) continue;

				$myContent = JFile::read(JPATH_ROOT.DIRECTORY_SEPARATOR.$file->file, false, $file->size);
				if($myContent === false) {
					$file->replace = false;
					continue;
				}

				$basePath = $this->baseURL . trim(dirname($file->file),'/\\') ;
				if(DIRECTORY_SEPARATOR == '\\') $basePath = str_replace (DIRECTORY_SEPARATOR, '/', $basePath);
				$this->cssBaseURL = $basePath;
				$myContent = preg_replace_callback("/url\s*\((.*)\)/siU", array($this,"decode_url"), $myContent);

				$cssContent .= "\n\n/* COMBINED FILE: {$file->url} */\n\n";

				$cmFilters = array (
					"ImportImports"                 => array("BasePath" => dirname($file->file)),
					"RemoveComments"                => true,
					"RemoveEmptyRulesets"           => true,
					"RemoveEmptyAtBlocks"           => true,
					"ConvertLevel3AtKeyframes"      => array("RemoveSource" => false),
					"ConvertLevel3Properties"       => true,
					"Variables"                     => true,
					"RemoveLastDelarationSemiColon" => true
				);
				$cmPlugins = array (
					"Variables"                     => true,
					"ConvertFontWeight"             => true,
					"ConvertHslColors"              => true,
					"ConvertRgbColors"              => true,
					"ConvertNamedColors"            => true,
					"CompressColorValues"           => true,
					"CompressUnitValues"            => true,
					"CompressExpressionValues"      => true
				);
				$cssContent .= CssMin::minify($myContent, $cmFilters, $cmPlugins)."\n";

				//$cssContent .= $myContent . "\n\n";

				$basename = basename($file->file);
			}

			// Write the cache file
			JFile::write($this->combineCache.DIRECTORY_SEPARATOR.'css-'.$cssHash.'.css', $cssContent);
		}

		// Replace the CSS files
		$body=preg_replace_callback($linksRegex,array($this,'replaceCSS'),$body);
		if($this->CSSparams->get('delivery','') == 'plugin') {
			$newURL = $this->baseURL.'index.php?fetchcombinedfile=css-'.$cssHash;
		} else {
			$myCache = str_replace(DIRECTORY_SEPARATOR, '/', @realpath($this->combineCache));
			$myRoot = str_replace(DIRECTORY_SEPARATOR, '/', @realpath(JPATH_ROOT));
			$rootLen = strlen($myRoot);
			if(substr($myCache, 0, $rootLen) == $myRoot) {
				$myCache = trim(substr($myCache, $rootLen),'/');
			}
			$newURL = rtrim($this->baseURL,'/').'/'.$myCache.'/css-'.$cssHash.'.css';
		}

		$newHeadCode = '</title>';
		foreach($this->cssFiles as $css) {
			if( (!$css->internal || !$css->replace) && !$css->skip) {
				//$newHeadCode .= "\n<link rel=\"stylesheet\" type=\"text/css\" href=\"{$css->url}\" media=\"{$css->media}\" />";
			}
		}
		$newHeadCode .="\n".'<link rel="stylesheet" type="text/css" href="'.$newURL.'" />'."\n";

		//only match once
		$body = preg_replace('/<\/title>/i',$newHeadCode , $body,1);
		JResponse::setBody($body);
	}

	/**
	 * Delivers a cached JS/CSS combined file
	 */
	private function deliverFile($hash)
	{
		// Load Joomla! libraries
		JLoader::import('joomla.filesystem.file');
		JLoader::import('joomla.utilities.date');

		// Kill caching
		@ob_end_clean();

		// Check that it's a js- or css- file, or throw a Forbidden message
		$pass = true;
		if(substr($hash,0,3) == 'js-') {
			$ext = '.js';
		} elseif(substr($hash,0,4) == 'css-') {
			$ext = '.css';
		} else {
			$ext = '.php';
			$pass = false;
		}

		// Is the file there?
		if($pass) $pass = $pass && JFile::exists($this->combineCache.DIRECTORY_SEPARATOR.$hash.$ext);

		// Can we read the file?
		if($pass)
		{
			$content = JFile::read($this->combineCache.DIRECTORY_SEPARATOR.$hash.$ext);
			if($content === false) {
				// Can't read the file, no go.
				$pass = false;
			}
		}

		// If there is something wrong, throw a Forbidden header
		if(!$pass) {
			if(!headers_sent()) header('HTTP/1.0 403 Forbidden');
			jexit(403);
		}

		// Guess the appropriate content type
		$contentType = (substr($hash,0,3) == 'js-') ? 'text/javascript' : 'text/css';
		$suffix = (substr($hash,0,3) == 'js-') ? 'js' : 'css';

		// Calculate the expiration date
		JLoader::import('joomla.utilities.date');
		$date = new JDate();
		$filedate = @filemtime($this->combineCache.DIRECTORY_SEPARATOR.$hash.$ext);
		$jfiledate = new JDate($filedate);
		$modified = $jfiledate->toRFC822();
		$filedate += 31536000; // Add one year
		$gmt = new DateTimeZone('GMT');
		if (version_compare(JVERSION, '3.1.0', 'ge'))
		{
			$date = new JDate($filedate, $gmt);
		}
		else
		{
			$date = new JDate($filedate, 0);
		}
		$expires = $date->toRFC822();

		// Calculate data length
		$length = strlen($content);

		// Check if the browser tries to validate against an ETag
		if(function_exists('getallheaders'))
		{
			$headers = @getallheaders();
			foreach($headers as $key => $value)
			{
				if(strtolower($key) == 'if-none-match') {
					if(strstr($value, $hash)) {
						if(!headers_sent()) {
							@header('HTTP/1.1 304 Not Modified');
						}
					}
				}
			}
		}

		// Send our headers
		if(!headers_sent())
		{
			@header("ETag: \"{$hash}\"");
			@header("Expires: $expires");
			@header("Last-Modified: $modified");
			@header("Content-type: $contentType");
			@header("Content-Disposition: inline; filename=\"$hash.$ext\";");
		}

		// Do we have to compress?
		$app = JFactory::getApplication();
		$compress = $app->getCfg('gzip',0);

		if($compress) {
			// Get the client supported encoding
			$encoding = false;
			if (isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
				if (false !== strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
					$encoding = 'gzip';
				}
				if (false !== strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'x-gzip')) {
					$encoding = 'x-gzip';
				}
			}
		}

		if($compress && !ini_get('zlib.output_compression') && ini_get('output_handler')!='ob_gzhandler' && !headers_sent() && extension_loaded('zlib') && (connection_status() === 0) && $encoding)
		{
			$level = 4; //ideal level
			$gzdata = @gzencode($content, $level);
			@header("Content-Encoding: $encoding");
			@header("Content-Length: ".strlen($gzdata));
			echo $gzdata;
		}
		else
		{
			@header("Content-Length: $length");
			echo $content;
		}
		jexit();
	}

	/**
	 * Figures out if a URL is internal (comes from this site) or if it is an
	 * external URL, while figuring out the file it refers to as well.
	 * @param string $url Input: the URL; Output: the file for this URL
	 * @return bool
	 */
	private function isInternal(&$url)
	{
		if( (strtolower(substr($url,0,7)) == 'http://') ||
			(strtolower(substr($url,0,8)) == 'https://') )
		{
			// Strip the protocol from the URL
			if((strtolower(substr($url,0,7)) == 'http://')) {
				$url = substr($url,7);
			} else {
				$url = substr($url, 8);
			}
			// Strip the protocol from our own site's URL
			if((strtolower(substr($this->baseURL,0,7)) == 'http://')) {
				$base = substr($this->baseURL,7);
			} else {
				$base = substr($this->baseURL, 8);
			}
			// Does the domain match?
			if(strtolower(substr($url,0,strlen($base))) == strtolower($base) )
			{
				// Yes, trim the url
				$url = ltrim(substr($url,strlen($base)),'/\\');
				return true;
			}
			else
			{
				// Nope, it's an external URL
				return false;
			}

		}
		else
		{
			// No protocol, ergo we are a relative internal URL

			$app = JFactory::getApplication();
			if( (substr($url,0,1) != '/') && ($app->getName() == 'admin') )
			{
				// Relative URL to the administrator directory
				$url = 'administrator/'.$url;
			}

			$url = ltrim($url,'/\\');
			return true;
		}
	}

	/**
	 * Callback method to remove the JavaScript <script> tags
	 * @param array $matches
	 * @return string
	 */
	public function replaceJS($matches)
	{
		$jsRegex="/src=[\"\']([^\"\']+)[\"\']/i";
		preg_match_all($jsRegex, $matches[0], $m);
		if(isset($m[1])&&count($m[1])){
			// Get the URL of the script
			$url=$m[1][0];
			// Sanitize it
			$filename = $url;
			$junk = $this->isInternal($filename);
			$qmPos = strpos($filename,'?');
			if( $qmPos !== false ) {
				// Strip the query
				$query = substr($filename, $qmPos+1);
				$file = substr($filename, 0, $qmPos);
			} else {
				$query = '';
			}

			// Check if it marked as "do not replace"
			if(count($this->jsFiles))
			{
				$found = false;
				foreach($this->jsFiles as $file)
				{
					if(($file->url == $url)) $found = true;
					if(($file->file == $filename)) $found = true;
					if(($file->file == 'administrator'.DIRECTORY_SEPARATOR.$filename)) $found = true;

					if($found) {
						$file->REPLACED = 'REPLACED';
						return ' ';
					}
				}
				if(!$found) return $matches[0];
			}
			else
			{
				return $matches[0];
			}
			// If we are still here, the script must be removed, so we'll just
			// replace it with an empty string!
			return ' ';
		}
		else
		{
			return $matches[0];
		}
	}

	/**
	 * Callback method to remove the CSS <link> tags
	 * @param array $matches
	 * @return string
	 */
	public function replaceCSS($matches)
	{
		$cssRegex="/([^\"\'=]+\.(css)(\?[^\"\']*){0,1})[\"\']/i";
		preg_match_all($cssRegex, $matches[0], $m);
		if(isset($m[1])&&count($m[1])){
			// Get the URL of the script
			$url=$m[1][0];
			// Sanitize it
			$filename = $url;
			$junk = $this->isInternal($filename);
			$qmPos = strpos($filename,'?');
			if( $qmPos !== false ) {
				// Strip the query
				$query = substr($filename, $qmPos+1);
				$file = substr($filename, 0, $qmPos);
			} else {
				$query = '';
			}

			// Check if it marked as "do not replace"
			if(count($this->cssFiles))
			{
				$found = false;
				foreach($this->cssFiles as $file)
				{
					if(($file->url == $url)) $found = true;
					if(($file->file == $filename)) $found = true;
					if(($file->file == 'administrator'.DIRECTORY_SEPARATOR.$filename)) $found = true;
					/**
					if($found) {
						$file->REPLACED = 'REPLACED';
						return ' ';
					}
					/**/
					/**/
					if( $found && (!$file->replace || $file->skip) )
					{
						$found = false;
						return $matches[0];
					}
					if($found) {
						$file->REPLACED = 'REPLACED';
						return ' ';
					}
					/**/

				}
				if(!$found) return $matches[0];
			}
			else
			{
				return $matches[0];
			}
			// If we are still here, the script must be removed, so we'll just
			// replace it with an empty string!
			return ' ';
		}
		else
		{
			return $matches[0];
		}
	}

	public function decode_url($match)
	{
		$baseurl = $this->cssBaseURL;
		if(!empty($baseurl)) {
			// Slash the protocol
			if( substr($baseurl,0,7) == 'http://' ) $baseurl = substr($baseurl,7);
			if( substr($baseurl,0,8) == 'https://' ) $baseurl = substr($baseurl,8);
			$parts = explode('/',$baseurl);
			if(count($parts) >= 2) array_shift($parts);
			$baseurl = '/'.@implode('/', $parts);
		}

		$myURL = trim($match[1],'"\'');
		if(
			!(substr($myURL,0,7) == 'http://') &&
			!(substr($myURL,0,8) == 'https://') &&
			!(substr($myURL,0,1) == '/')
		) {
			$myURL = rtrim($this->baseURL.$baseurl,'/') .'/'. $myURL;
		}

		return "url(\"$myURL\")";
	}
}

function AdminToolsLateBoundAfterRender()
{
	$subject = array();
	$plugin = plgSystemAdmintoolsCore::fetchMyself();
	$plugin->onAfterRenderLatebound();
}