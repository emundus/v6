<?php

/**
 * JCH Optimize - Aggregate and minify external resources for optmized downloads
 *
 * @author    Samuel Marshall <sdmarshall73@gmail.com>
 * @copyright Copyright (c) 2010 Samuel Marshall
 * @license   GNU/GPLv3, See LICENSE file
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

namespace JchOptimize\Core;

defined('_JCH_EXEC') or die('Restricted access');

use JchOptimize\Platform\Settings;
use JchOptimize\Platform\Uri;
use JchOptimize\Platform\Cache;
use JchOptimize\Platform\Profiler;
use JchOptimize\Platform\Paths;
use JchOptimize\Platform\Utility;
use JchOptimize\Platform\Excludes;
use JchOptimize\Minify\Html;
use JchOptimize\Minify\Js;
use JchOptimize\Minify\Css;


class Admin
{

	protected $bBackend;
	protected $params;
	protected $links = array();

	/**
	 *
	 * @param   Settings  $params
	 * @param   boolean   $bBackend
	 */
	public function __construct(Settings $params, $bBackend = false)
	{
		$this->params   = $params;
		$this->bBackend = $bBackend;
	}

	/**
	 * Returns a multi-dimensional array of items to populate the multi-select exclude lists in the
	 * admin settings section
	 *
	 * @param   string  $sHtml  HTML before it's optimized by JCH Optimize
	 * @param   string  $sCss   Combined css contents
	 *
	 * @return array
	 * @throws Exception
	 */
	public function getAdminLinks($sHtml, $sCss = '')
	{
		if (empty($this->links))
		{
			$hash        = $this->params->get('cookielessdomain_enable', 0);
			$sId         = md5('getAdminLinks' . JCH_VERSION . serialize($hash));
			$aFunction   = array($this, 'generateAdminLinks');
			$aArgs       = array($sHtml, $sCss);
			$this->links = Cache::getCallbackCache($sId, $aFunction, $aArgs);
		}

		return $this->links;
	}

	/**
	 *
	 * @param   string  $sHtml
	 * @param   string  $sCss
	 *
	 * @return array
	 */
	public function generateAdminLinks($sHtml, $sCss)
	{
		JCH_DEBUG ? Profiler::start('GenerateAdminLinks') : null;

		$params = clone $this->params;
		$params->set('combine_files_enable', '1');
		$params->set('javascript', '1');
		$params->set('css', '1');
		$params->set('gzip', '0');
		$params->set('css_minify', '0');
		$params->set('js_minify', '0');
		$params->set('html_minify', '0');
		$params->set('defer_js', '0');
		$params->set('debug', '0');
		$params->set('bottom_js', '2');
		$params->set('includeAllExtensions', '1');
		// $params->set('excludeCss', array());
		// $params->set('excludeJs', array());
		// $params->set('excludeCssComponents', array());
		// $params->set('excludeJsComponents', array());
		$params->set('csg_exclude_images', array());
		$params->set('csg_include_images', array());

		$params->set('searchBody', '1');
		$params->set('phpAndExternal', '1');
		$params->set('inlineScripts', '1');
		$params->set('replaceImports', '0');
		$params->set('loadAsynchronous', '0');
		$params->set('cookielessdomain_enable', '0');
		$params->set('lazyload', '0');
		$params->set('optimizeCssDelivery_enable', '0');
		//$params->set('pro_excludeLazyLoad', array());
		//$params->set('pro_excludeLazyLoadFolders', array());
		//$params->set('pro_excludeLazyLoadClass', array());

		try
		{
			$oParser = new Parser($params, $sHtml, FileRetriever::getInstance());
			$aLinks  = $oParser->getReplacedFiles();

			if ($sCss == '' && !empty($aLinks['css'][0]))
			{
				$oCombiner  = new Combiner($params, $oParser);
				$oCssParser = new CssParser($params, $this->bBackend);

				$oCombiner->combineFiles($aLinks['css'][0], 'css', $oCssParser);
				$sCss = $oCombiner->css;
			}

			$oSpriteGenerator = new SpriteGenerator($params);
			$aLinks['images'] = $oSpriteGenerator->processCssUrls($sCss, true);

			$sRegex = $oParser->getLazyLoadRegex(true);

			preg_match_all($sRegex, $oParser->getBodyHtml(), $aMatches);

			
			$aLinks['lazyload'] = array_merge($aMatches[8], $aMatches[18]);
		}
		catch (Exception $e)
		{
			$aLinks = array();
		}

		JCH_DEBUG ? Profiler::stop('GenerateAdminLinks', true) : null;

		return $aLinks;
	}

	/**
	 *
	 * @param   string  $sType
	 * @param   string  $sExcludeParams
	 * @param   string  $sGroup
	 * @param   bool    $bIncludeExcludes
	 *
	 * @return array
	 */
	public function prepareFieldOptions($sType, $sExcludeParams, $sGroup = '', $bIncludeExcludes = true)
	{
		if ($sType == 'lazyload')
		{
			$aFieldOptions = $this->getLazyLoad($sGroup);
			$sGroup        = 'file';
		}
		elseif ($sType == 'images')
		{
			$sGroup        = 'file';
			$aM            = explode('_', $sExcludeParams);
			$aFieldOptions = $this->getImages($aM[1]);
		}
		else
		{
			$aFieldOptions = $this->getOptions($sType, $sGroup . 's');
		}

		$aOptions  = array();
		$oParams   = $this->params;
		$aExcludes = Helper::getArray($oParams->get($sExcludeParams, array()));

		foreach ($aExcludes as $sExclude)
		{
			$aOptions[$sExclude] = $this->{'prepare' . ucfirst($sGroup) . 'Values'}($sExclude);
		}

		//Should we include saved exclude parameters?
		if ($bIncludeExcludes)
		{
			return array_merge($aFieldOptions, $aOptions);
		}
		else
		{
			return array_diff($aFieldOptions, $aOptions);
		}
	}

	/**
	 *
	 * @param   string  $sType
	 * @param   string  $sExclude
	 *
	 * @return array
	 */
	protected function getOptions($sType, $sExclude = 'files')
	{
		$aLinks = $this->links;

		$aOptions = array();

		if (!empty($aLinks[$sType][0]))
		{
			foreach ($aLinks[$sType][0] as $aLink)
			{
				if (isset($aLink['url']) && $aLink['url'] != '')
				{
					if ($sExclude == 'files')
					{
						$sFile            = $this->prepareFileValues($aLink['url'], 'key');
						$aOptions[$sFile] = $this->prepareFileValues($sFile, 'value');
					}
					elseif ($sExclude == 'extensions')
					{
						$sExtension = $this->prepareExtensionValues($aLink['url'], false);

						if ($sExtension === false)
						{
							continue;
						}

						$aOptions[$sExtension] = $sExtension;
					}
				}
				elseif (isset($aLink['content']) && $aLink['content'] != '')
				{
					if ($sExclude == 'scripts')
					{
						$sScript = Html::cleanScript($aLink['content'], 'js');
						$sScript = trim(Js::optimize($sScript));
					}
					elseif ($sExclude == 'styles')
					{
						$sScript = Html::cleanScript($aLink['content'], 'css');
						$sScript = trim(Css::optimize($sScript));
					}

					if (isset($sScript))
					{
						if (strlen($sScript) > 60)
						{
							$sScript = substr($sScript, 0, 60);
						}

						$sScript = htmlspecialchars($sScript);

						$aOptions[addslashes($sScript)] = $this->prepareScriptValues($sScript);
					}
				}
			}
		}

		return $aOptions;
	}

	/**
	 *
	 * @param   string  $group
	 *
	 * @return array
	 */
	public function getLazyLoad($group)
	{
		$aLinks = $this->links;

		$aFieldOptions = array();

		if ($group == 'file' || $group == 'folder')
		{
			if (!empty($aLinks['lazyload']))
			{
				foreach ($aLinks['lazyload'] as $sImage)
				{
					if ($group == 'folder')
					{
						$regex = '#(?<!/)/[^/\n]++$|(?<=^)[^/.\n]++$#';
						$i     = 0;

						$sImage = $this->prepareFileValues($sImage, 'key');
						$folder = preg_replace($regex, '', $sImage);

						while (preg_match($regex, $folder))
						{
							$aFieldOptions[$folder] = $this->prepareFileValues($folder, 'value');

							$folder = preg_replace($regex, '', $folder);

							$i++;

							if ($i == 12)
							{
								break;
							}
						}
					}
					else
					{
						$sImage = $this->prepareFileValues($sImage, 'key');

						$aFieldOptions[$sImage] = $this->prepareFileValues($sImage, 'value');
					}
				}
			}
		}
		elseif ($group == 'class')
		{
			if (!empty($aLinks['lazyloadclass']))
			{
				foreach ($aLinks['lazyloadclass'] as $sClasses)
				{
					$aClass = preg_split('# #', $sClasses, -1, PREG_SPLIT_NO_EMPTY);

					foreach ($aClass as $sClass)
					{
						$aFieldOptions[$sClass] = $sClass;
					}
				}
			}
		}

		return array_filter($aFieldOptions);
	}

	/**
	 *
	 * @param   string  $sAction
	 *
	 * @return array
	 */
	protected function getImages($sAction = 'exclude')
	{
		$aLinks = $this->links;

		$aOptions = array();

		if (!empty($aLinks['images'][$sAction]))
		{
			foreach ($aLinks['images'][$sAction] as $sImage)
			{
//                                $aImage = explode('/', $sImage);
//                                $sImage = array_pop($aImage);

				$aOptions[$sImage] = $this->prepareFileValues($sImage);
			}
		}

		return array_unique($aOptions);
	}

	/**
	 *
	 * @param   string  $sScript
	 *
	 * @return string
	 */
	public static function prepareScriptValues($sScript)
	{
		if (strlen($sScript) > 52)
		{
			$sScript = substr($sScript, 0, 52);
			$sEps    = '...';
			$sScript = $sScript . $sEps;
		}

		if (strlen($sScript) > 26)
		{
			$sScript = str_replace($sScript[26], $sScript[26] . "\n", $sScript);
		}

		return $sScript;
	}

	/**
	 *
	 * @param   string  $sStyle
	 *
	 * @return string
	 */
	public static function prepareStyleValues($sStyle)
	{
		return self::prepareScriptValues($sStyle);
	}

	/**
	 *
	 * @param   string  $sFile
	 * @param   string  $sType
	 * @param   int     $iLen
	 *
	 * @return string
	 */
	public static function prepareFileValues($sFile, $sType = '', $iLen = 27)
	{
		if ($sType != 'value')
		{
			$oFile = Uri::getInstance($sFile);

			if (Url::isInternal($sFile))
			{
				$sFile = $oFile->getPath();
			}
			else
			{
				$sFile = $oFile->toString(array('scheme', 'user', 'pass', 'host', 'port', 'path'));
			}

			if ($sType == 'key')
			{
				return $sFile;
			}
		}

		$sEps = '';

		if (strlen($sFile) > $iLen)
		{
			$sFile = substr($sFile, -$iLen);
			$sFile = preg_replace('#^[^/]*+/#', '/', $sFile);
			$sEps  = '...';
		}

		return $sEps . $sFile;
	}

	/**
	 *
	 * @staticvar string $sUriBase
	 * @staticvar string $sUriPath
	 *
	 * @param   string  $sUrl
	 * @param   bool    $bReturn
	 *
	 * @return boolean
	 */
	public static function prepareExtensionValues($sUrl, $bReturn = true)
	{
		if ($bReturn)
		{
			return $sUrl;
		}

		static $sHost = '';

		$oUri  = Uri::getInstance();
		$sHost = $sHost == '' ? $oUri->toString(array('host')) : $sHost;

		$result     = preg_match('#^(?:https?:)?//([^/]+)#', $sUrl, $m1);
		$sExtension = isset($m1[1]) ? $m1[1] : '';

		if ($result === 0 || $sExtension == $sHost)
		{
			$result2 = preg_match('#' . Excludes::extensions() . '([^/]+)#', $sUrl, $m);

			if ($result2 === 0)
			{
				return false;
			}
			else
			{
				$sExtension = $m[1];
			}
		}

		return $sExtension;
	}

	/**
	 *
	 * @param   string  $sImage
	 *
	 * @return string
	 */
	public static function prepareImagesValues($sImage)
	{
		return $sImage;
	}

	public static function prepareFolderValues($sFolder)
	{
		return self::prepareFileValues($sFolder);
	}

	public static function prepareClassValues($sClass)
	{
		return self::prepareFileValues($sClass);
	}

	/**
	 *
	 * @param   array  $aButtons
	 *
	 * @return string
	 */
	public static function generateIcons($aButtons)
	{
		$sField = '<div class="container-icons clearfix">';

		foreach ($aButtons as $sButton)
		{
			$tooltip = isset($sButton['tooltip']) ? 'class="hasTooltip" title="' . $sButton['tooltip'] . '"' : '';
			$sField  .= <<<JFIELD
<div class="icon {$sButton['class']}">
        <a class="btn" href="{$sButton['link']}"  {$sButton['script']}  >
                <div style="text-align: center;">
                        <i class="fa {$sButton['icon']} fa-3x" style="margin: 7px 0; color: {$sButton['color']}"></i>
                </div>
                <label {$tooltip}>
                        {$sButton['text']}
                </label><br>
                <i id="toggle" class="fa"></i>
        </a>
</div>
JFIELD;
		}

		$sField .= '</div>';

		return $sField;
	}

	/**
	 *
	 * @return array
	 */
	public static function getSettingsIcons()
	{
		$aButtons = array();

		$aButtons[0]['link']   = '';
		$aButtons[0]['icon']   = 'fa-wrench';
		$aButtons[0]['text']   = 'Minimum';
		$aButtons[0]['color']  = '#FFA319';
		$aButtons[0]['script'] = 'onclick="applyAutoSettings(1, 0); return false;"';
		$aButtons[0]['class']  = 'enabled settings-1';

		$aButtons[1]['link']   = '';
		$aButtons[1]['icon']   = 'fa-cog';
		$aButtons[1]['text']   = 'Intermediate';
		$aButtons[1]['color']  = '#FF32C7';
		$aButtons[1]['script'] = 'onclick="applyAutoSettings(2, 0); return false;"';
		$aButtons[1]['class']  = 'enabled settings-2';

		$aButtons[2]['link']   = '';
		$aButtons[2]['icon']   = 'fa-cogs';
		$aButtons[2]['text']   = 'Average';
		$aButtons[2]['color']  = '#CE3813';
		$aButtons[2]['script'] = 'onclick="applyAutoSettings(3, 0); return false;"';
		$aButtons[2]['class']  = 'enabled settings-3';

		$aButtons[3]['link']   = '';
		$aButtons[3]['icon']   = 'fa-forward';
		$aButtons[3]['text']   = 'Deluxe';
		$aButtons[3]['color']  = '#E8CE0B';
		$aButtons[3]['script'] = 'onclick="applyAutoSettings(4, 2); return false;"';
		$aButtons[3]['class']  = 'enabled settings-4';

		$aButtons[4]['link']   = '';
		$aButtons[4]['icon']   = 'fa-fast-forward';
		$aButtons[4]['text']   = 'Premium';
		$aButtons[4]['color']  = '#9995FF';
		$aButtons[4]['script'] = 'onclick="applyAutoSettings(5, 1); return false;"';
		$aButtons[4]['class']  = 'enabled settings-5';

		$aButtons[5]['link']   = '';
		$aButtons[5]['icon']   = 'fa-dashboard';
		$aButtons[5]['text']   = 'Optimum';
		$aButtons[5]['color']  = '#60AF2C';
		$aButtons[5]['script'] = 'onclick="applyAutoSettings(6, 1); return false;"';
		$aButtons[5]['class']  = 'enabled settings-6';

		return $aButtons;
	}

	/**
	 *
	 * @return array
	 */
	public static function getUtilityIcons()
	{
		$aButtons = array();

		$aButtons[1]['link']    = Paths::adminController('browsercaching');
		$aButtons[1]['icon']    = 'fa-globe';
		$aButtons[1]['color']   = '#51A351';
		$aButtons[1]['text']    = Utility::translate('Optimize .htaccess');
		$aButtons[1]['script']  = '';
		$aButtons[1]['class']   = 'enabled';
		$aButtons[1]['tooltip'] = Utility::translate('Use this button to add codes to your htaccess file to enable leverage browser caching and gzip compression.');

		$aButtons[3]['link']    = Paths::adminController('filepermissions');
		$aButtons[3]['icon']    = 'fa-file-text';
		$aButtons[3]['color']   = '#166BEC';
		$aButtons[3]['text']    = Utility::translate('Fix file permissions');
		$aButtons[3]['script']  = '';
		$aButtons[3]['class']   = 'enabled';
		$aButtons[3]['tooltip'] = Utility::translate('If your site has lost CSS formatting after enabling the plugin, the problem could be that the plugin files were installed with incorrect file permissions so the browser cannot access the cached combined file. Click here to correct the plugin\'s file permissions.');

		$aButtons[5]['link']    = Paths::adminController('cleancache');
		$aButtons[5]['icon']    = 'fa-times-circle';
		$aButtons[5]['color']   = '#C0110A';
		$aButtons[5]['text']    = Utility::translate('Clean Cache');
		$aButtons[5]['script']  = '';
		$aButtons[5]['class']   = 'enabled';
		$aButtons[5]['tooltip'] = Utility::translate('Click this button to clean the plugin\'s cache and page cache. If you have edited any CSS or javascript files you need to clean the cache so the changes can be visible.');

		return $aButtons;
	}

	/**
	 *
	 * @return string
	 */
	public static function leverageBrowserCaching()
	{
		$htaccess = Paths::rootPath() . '/.htaccess';

		if (file_exists($htaccess))
		{
			$contents = file_get_contents($htaccess);

			if (!preg_match('@\n?## BEGIN EXPIRES CACHING - JCH OPTIMIZE ##.*?## END EXPIRES CACHING - JCH OPTIMIZE ##@s', $contents))
			{
				$sExpires = <<<APACHECONFIG
## BEGIN EXPIRES CACHING - JCH OPTIMIZE ##
<IfModule mod_expires.c>
	ExpiresActive on

	# Perhaps better to whitelist expires rules? Perhaps.
	ExpiresDefault "access plus 1 year"

	# cache.appcache needs re-requests in FF 3.6 (thanks Remy ~Introducing HTML5)
	ExpiresByType text/cache-manifest "access plus 0 seconds"

	# Your document html
	ExpiresByType text/html "access plus 0 seconds"

	# Data
	ExpiresByType text/xml "access plus 0 seconds"
	ExpiresByType application/xml "access plus 0 seconds"
	ExpiresByType application/json "access plus 0 seconds"

	# Feed
	ExpiresByType application/rss+xml "access plus 1 hour"
	ExpiresByType application/atom+xml "access plus 1 hour"

	# Favicon (cannot be renamed)
	ExpiresByType image/x-icon "access plus 1 week"

	# Media: images, video, audio
	ExpiresByType image/gif "access plus 1 year"
	ExpiresByType image/png "access plus 1 year"
	ExpiresByType image/jpg "access plus 1 year"
	ExpiresByType image/jpeg "access plus 1 year"
	ExpiresByType image/webp "access plus 1 year"
	ExpiresByType audio/ogg "access plus 1 year"
	ExpiresByType video/ogg "access plus 1 year"
	ExpiresByType video/mp4 "access plus 1 year"
	ExpiresByType video/webm "access plus 1 year"

	# HTC files (css3pie)
	ExpiresByType text/x-component "access plus 1 year"

	# Webfonts
	ExpiresByType application/font-ttf "access plus 1 year"
	ExpiresByType font/opentype "access plus 1 year"
	ExpiresByType application/font-woff "access plus 1 year"
	ExpiresByType application/font-woff2 "access plus 1 year"
	ExpiresByType image/svg+xml "access plus 1 year"
	ExpiresByType application/vnd.ms-fontobject "access plus 1 year"

	# CSS and JavaScript
	ExpiresByType text/css "access plus 1 year"
	ExpiresByType type/javascript "access plus 1 year"
	ExpiresByType application/javascript "access plus 1 year"

	<IfModule mod_headers.c>
		Header append Cache-Control "public"
		<FilesMatch ".(js|css|xml|gz|html)$">
			Header append Vary: Accept-Encoding
		</FilesMatch>
	</IfModule>

</IfModule>

<IfModule mod_deflate.c>
	AddOutputFilterByType DEFLATE text/html
	AddOutputFilterByType DEFLATE text/css
	AddOutputFilterByType DEFLATE text/javascript
	AddOutputFilterByType DEFLATE text/xml
	AddOutputFilterByType DEFLATE text/plain
	AddOutputFilterByType DEFLATE image/x-icon
	AddOutputFilterByType DEFLATE image/svg+xml
	AddOutputFilterByType DEFLATE application/rss+xml
	AddOutputFilterByType DEFLATE application/javascript
	AddOutputFilterByType DEFLATE application/x-javascript
	AddOutputFilterByType DEFLATE application/xml
	AddOutputFilterByType DEFLATE application/xhtml+xml
	AddOutputFilterByType DEFLATE application/font
	AddOutputFilterByType DEFLATE application/font-truetype
	AddOutputFilterByType DEFLATE application/font-ttf
	AddOutputFilterByType DEFLATE application/font-otf
	AddOutputFilterByType DEFLATE application/font-opentype
	AddOutputFilterByType DEFLATE application/font-woff
	AddOutputFilterByType DEFLATE application/font-woff2
	AddOutputFilterByType DEFLATE application/vnd.ms-fontobject
	AddOutputFilterByType DEFLATE font/ttf
	AddOutputFilterByType DEFLATE font/otf
	AddOutputFilterByType DEFLATE font/opentype
	AddOutputFilterByType DEFLATE font/woff
	AddOutputFilterByType DEFLATE font/woff2
	# For Olders Browsers Which Can't Handle Compression
	BrowserMatch ^Mozilla/4 gzip-only-text/html
	BrowserMatch ^Mozilla/4\.0[678] no-gzip
	BrowserMatch \bMSIE !no-gzip !gzip-only-text/html
</IfModule>
## END EXPIRES CACHING - JCH OPTIMIZE ##
APACHECONFIG;

				return file_put_contents($htaccess, $sExpires, FILE_APPEND);
			}
			else
			{
				return 'CODEALREADYINFILE';
			}
		}
		else
		{
			return 'FILEDOESNTEXIST';
		}
	}

	public static function cleanHtaccess()
	{
		$htaccess = Paths::rootPath() . '/.htaccess';

		if (file_exists($htaccess))
		{
			$contents = file_get_contents($htaccess);
			$regex    = '@\n?## BEGIN EXPIRES CACHING - JCH OPTIMIZE ##.*?## END EXPIRES CACHING - JCH OPTIMIZE ##@s';

			$clean_contents = preg_replace($regex, '', $contents, -1, $count);

			if ($count > 0)
			{
				file_put_contents($htaccess, $clean_contents);
			}
		}
	}
}
