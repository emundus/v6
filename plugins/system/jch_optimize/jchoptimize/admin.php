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
defined('_JCH_EXEC') or die('Restricted access');

class JchOptimizeAdmin
{

        protected $bBackend;
        protected $params;
        protected $links = array();

        /**
         * 
         * @param type $params
         * @param type $bBackend
         */
        public function __construct(JchPlatformSettings $params, $bBackend = false)
        {
                $this->params   = $params;
                $this->bBackend = $bBackend;
        }

        /**
	 * Retruns a multi-dimensional array of items to populate the multi-select exclude lists in the 
	 * admin settings section
	 *
         * @param object  $sHtml 	HTML before it's optimized by JCH Optimize
         * @param string  $sCss	Combined css contents
         * @return array
         */
        public function getAdminLinks($sHtml, $sCss = '')
        {
                if (empty($this->links))
                {
                        $hash      = $this->params->get('pro_cookielessdomain_enable', 0);
                        $sId       = md5('getAdminLinks' . JCH_VERSION . serialize($hash));
                        $aFunction = array($this, 'generateAdminLinks');
                        $aArgs     = array($sHtml, $sCss);
                        $this->links = JchPlatformCache::getCallbackCache($sId, $aFunction, $aArgs);
                }

                return $this->links;
        }

        /**
         * 
         * @param type $sHtml Can be instance of JchOptimizeParser or JchOptimizeHtml
         * @param type $sCss
         * @return type
         */
        public function generateAdminLinks($sHtml, $sCss)
        {
                JCH_DEBUG ? JchPlatformProfiler::start('GenerateAdminLinks') : null;

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

                

                try
                {
			$oParser = new JchOptimizeParser($params, $sHtml, JchOptimizeFileRetriever::getInstance());
			$aLinks = $oParser->getReplacedFiles();

                        if ($sCss == '' && !empty($aLinks['css'][0]))
                        {
                                $oCombiner  = new JchOptimizeCombiner($params, $oParser);
                                $oCssParser = new JchOptimizeCssParser($params, $this->bBackend);

                                $oCombiner->combineFiles($aLinks['css'][0], 'css', $oCssParser);
                                $sCss = $oCombiner->css;
                        }

                        $oSpriteGenerator = new JchOptimizeSpriteGenerator($params);
                        $aLinks['images'] = $oSpriteGenerator->processCssUrls($sCss, TRUE);

                        
                }
                catch (Exception $e)
                {
                        $aLinks = array();
                }

                JCH_DEBUG ? JchPlatformProfiler::stop('GenerateAdminLinks', TRUE) : null;

                return $aLinks;
        }

        /**
         * 
         * @param type $sExcludeParams
         * @param type $sField
         * @return type
         */
        public function prepareFieldOptions($sType, $sExcludeParams, $sGroup = '', $bIncludeExcludes=true)
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
		$aExcludes = JchOptimizeHelper::getArray($oParams->get($sExcludeParams, array()));

		foreach ($aExcludes as $sExclude)
		{
			$aOptions[$sExclude] = $this->{'prepare' . ucfirst($sGroup) . 'Values'}($sExclude);
		}

		//Should we include saved exclude parameters?
		if($bIncludeExcludes)
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
         * @param type $sType
         * @param type $sExclude
         * @return type
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
                                                $sExtension = $this->prepareExtensionValues($aLink['url'], FALSE);

                                                if ($sExtension === FALSE)
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
                                                $sScript = JchOptimize\HTML_Optimize::cleanScript($aLink['content'], 'js');
                                                $sScript = trim(JchOptimize\JS_Optimize::optimize($sScript));
                                        }
                                        elseif ($sExclude == 'styles')
                                        {
                                                $sScript = JchOptimize\HTML_Optimize::cleanScript($aLink['content'], 'css');
                                                $sScript = trim(JchOptimize\CSS_Optimize::optimize($sScript));
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
         * @return type
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
         * @param type $sAction
         * @return type
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
         * @param type $sContent
         */
        public static function prepareScriptValues($sScript)
        {
                $sEps = '';

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
         * @param type $sStyle
         * @return type
         */
        public static function prepareStyleValues($sStyle)
        {
                return self::prepareScriptValues($sStyle);
        }

        /**
         * 
         * @param type $sUrl
         * @return type
         */
        public static function prepareFileValues($sFile, $sType = '', $iLen = 27)
        {
                if ($sType != 'value')
                {
                        $oFile = JchPlatformUri::getInstance($sFile);

                        if (JchOptimizeUrl::isInternal($sFile))
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
         * @param type $sUrl
         * @return boolean
         */
        public static function prepareExtensionValues($sUrl, $bReturn = TRUE)
        {
                if ($bReturn)
                {
                        return $sUrl;
                }

                static $sHost = '';

                $oUri  = JchPlatformUri::getInstance();
                $sHost = $sHost == '' ? $oUri->toString(array('host')) : $sHost;

                $result     = preg_match('#^(?:https?:)?//([^/]+)#', $sUrl, $m1);
                $sExtension = isset($m1[1]) ? $m1[1] : '';

                if ($result === 0 || $sExtension == $sHost)
                {
                        $result2 = preg_match('#' . JchPlatformExcludes::extensions() . '([^/]+)#', $sUrl, $m);

                        if ($result2 === 0)
                        {
                                return FALSE;
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
         * @param type $sImage
         * @return type
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
         * @param type $aButtons
         * @return string
         */
        public static function generateIcons($aButtons)
        {
                $sField = '<div class="container-icons clearfix">';

                foreach ($aButtons as $sButton)
                {
                        $tooltip = isset($sButton['tooltip']) ? 'class="hasTooltip" title="' . $sButton['tooltip'] . '"' : '';
                        $sField .= <<<JFIELD
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
         * @return string
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
                
                $aButtons[3]['color']  = '#CCC';
                  $aButtons[3]['script'] = '';
                  $aButtons[3]['class']  = 'disabled';

                $aButtons[4]['link']   = '';
                $aButtons[4]['icon']   = 'fa-fast-forward';
                $aButtons[4]['text']   = 'Premium';
                
                $aButtons[4]['color']  = '#CCC';
                  $aButtons[4]['script'] = '';
                  $aButtons[4]['class']  = 'disabled';

                $aButtons[5]['link']   = '';
                $aButtons[5]['icon']   = 'fa-dashboard';
                $aButtons[5]['text']   = 'Optimum';
                
                $aButtons[5]['color']  = '#CCC';
                  $aButtons[5]['script'] = '';
                  $aButtons[5]['class']  = 'disabled';

                return $aButtons;
        }

        /**
         * 
         * @return type
         */
        public static function getUtilityIcons()
        {
                $aButtons = array();

                $aButtons[1]['link']    = JchPlatformPaths::adminController('browsercaching');
                $aButtons[1]['icon']    = 'fa-globe';
                $aButtons[1]['color']   = '#51A351';
                $aButtons[1]['text']    = JchPlatformUtility::translate('Optimize .htaccess');
                $aButtons[1]['script']  = '';
                $aButtons[1]['class']   = 'enabled';
                $aButtons[1]['tooltip'] = JchPlatformUtility::translate('Use this button to add codes to your htaccess file to enable leverage browser caching and gzip compression.');

                $aButtons[3]['link']    = JchPlatformPaths::adminController('filepermissions');
                $aButtons[3]['icon']    = 'fa-file-text';
                $aButtons[3]['color']   = '#166BEC';
                $aButtons[3]['text']    = JchPlatformUtility::translate('Fix file permissions');
                $aButtons[3]['script']  = '';
                $aButtons[3]['class']   = 'enabled';
                $aButtons[3]['tooltip'] = JchPlatformUtility::translate('If your site has lost CSS formatting after enabling the plugin, the problem could be that the plugin files were installed with incorrect file permissions so the browser cannot access the cached combined file. Click here to correct the plugin\'s file permissions.');

                $aButtons[5]['link']    = JchPlatformPaths::adminController('cleancache');
                $aButtons[5]['icon']    = 'fa-times-circle';
                $aButtons[5]['color']   = '#C0110A';
                $aButtons[5]['text']    = JchPlatformUtility::translate('Clean Cache');
                $aButtons[5]['script']  = '';
                $aButtons[5]['class']   = 'enabled';
                $aButtons[5]['tooltip'] = JchPlatformUtility::translate('Click this button to clean the plugin\'s cache and page cache. If you have edited any CSS or javascript files you need to clean the cache so the changes can be visible.');

                return $aButtons;
        }

        /**
         * 
         * @return string
         */
        public static function leverageBrowserCaching()
        {
                $htaccess = JchPlatformPaths::rootPath() . '.htaccess';

                if (file_exists($htaccess))
                {
                        $contents = file_get_contents($htaccess);

                        if (!preg_match('@\n?## BEGIN EXPIRES CACHING - JCH OPTIMIZE ##.*?## END EXPIRES CACHING - JCH OPTIMIZE ##@s', $contents))
                        {
				$sExpires = PHP_EOL;
				$sExpires .= '## BEGIN EXPIRES CACHING - JCH OPTIMIZE ##' . PHP_EOL;
				$sExpires .= '<IfModule mod_expires.c>' . PHP_EOL;
				$sExpires .= '  ExpiresActive on' . PHP_EOL;
				$sExpires .= '' . PHP_EOL;
				$sExpires .= '# Perhaps better to whitelist expires rules? Perhaps.' . PHP_EOL;
				$sExpires .= '  ExpiresDefault "access plus 1 year"' . PHP_EOL;
				$sExpires .= '' . PHP_EOL;
				$sExpires .= '# cache.appcache needs re-requests in FF 3.6 (thanks Remy ~Introducing HTML5)' . PHP_EOL;
				$sExpires .= '  ExpiresByType text/cache-manifest "access plus 0 seconds"' . PHP_EOL;
				$sExpires .= '' . PHP_EOL;
				$sExpires .= '# Your document html' . PHP_EOL;
				$sExpires .= '  ExpiresByType text/html "access plus 0 seconds"' . PHP_EOL;
				$sExpires .= '' . PHP_EOL;
				$sExpires .= '# Data' . PHP_EOL;
				$sExpires .= '  ExpiresByType text/xml "access plus 0 seconds"' . PHP_EOL;
				$sExpires .= '  ExpiresByType application/xml "access plus 0 seconds"' . PHP_EOL;
				$sExpires .= '  ExpiresByType application/json "access plus 0 seconds"' . PHP_EOL;
				$sExpires .= '' . PHP_EOL;
				$sExpires .= '# Feed' . PHP_EOL;
				$sExpires .= '  ExpiresByType application/rss+xml "access plus 1 hour"' . PHP_EOL;
				$sExpires .= '  ExpiresByType application/atom+xml "access plus 1 hour"' . PHP_EOL;
				$sExpires .= '' . PHP_EOL;
				$sExpires .= '# Favicon (cannot be renamed)' . PHP_EOL;
				$sExpires .= '  ExpiresByType image/x-icon "access plus 1 week"' . PHP_EOL;
				$sExpires .= '' . PHP_EOL;
				$sExpires .= '# Media: images, video, audio' . PHP_EOL;
				$sExpires .= '  ExpiresByType image/gif "access plus 1 year"' . PHP_EOL;
				$sExpires .= '  ExpiresByType image/png "access plus 1 year"' . PHP_EOL;
				$sExpires .= '  ExpiresByType image/jpg "access plus 1 year"' . PHP_EOL;
				$sExpires .= '  ExpiresByType image/jpeg "access plus 1 year"' . PHP_EOL;
				$sExpires .= '  ExpiresByType video/ogg "access plus 1 year"' . PHP_EOL;
				$sExpires .= '  ExpiresByType audio/ogg "access plus 1 year"' . PHP_EOL;
				$sExpires .= '  ExpiresByType video/mp4 "access plus 1 year"' . PHP_EOL;
				$sExpires .= '  ExpiresByType video/webm "access plus 1 year"' . PHP_EOL;
				$sExpires .= '  ExpiresByType video/webp "access plus 1 year"' . PHP_EOL;
				$sExpires .= '' . PHP_EOL;
				$sExpires .= '# HTC files (css3pie)' . PHP_EOL;
				$sExpires .= '  ExpiresByType text/x-component "access plus 1 year"' . PHP_EOL;
				$sExpires .= '' . PHP_EOL;
				$sExpires .= '# Webfonts' . PHP_EOL;
				$sExpires .= '  ExpiresByType application/font-ttf "access plus 1 year"' . PHP_EOL;
				$sExpires .= '  ExpiresByType font/opentype "access plus 1 year"' . PHP_EOL;
				$sExpires .= '  ExpiresByType application/font-woff "access plus 1 year"' . PHP_EOL;
				$sExpires .= '  ExpiresByType application/font-woff2 "access plus 1 year"' . PHP_EOL;
				$sExpires .= '  ExpiresByType image/svg+xml "access plus 1 year"' . PHP_EOL;
				$sExpires .= '  ExpiresByType application/vnd.ms-fontobject "access plus 1 year"' . PHP_EOL;
				$sExpires .= '' . PHP_EOL;
				$sExpires .= '# CSS and JavaScript' . PHP_EOL;
				$sExpires .= '  ExpiresByType text/css "access plus 1 year"' . PHP_EOL;
				$sExpires .= '  ExpiresByType application/javascript "access plus 1 year"' . PHP_EOL;
				$sExpires .= '' . PHP_EOL;
				$sExpires .= '  <IfModule mod_headers.c>' . PHP_EOL;
				$sExpires .= '    Header append Cache-Control "public"' . PHP_EOL;
				$sExpires .= '    <FilesMatch ".(js|css|xml|gz|html)$">' . PHP_EOL;
				$sExpires .= '       Header append Vary: Accept-Encoding' . PHP_EOL;
				$sExpires .= '    </FilesMatch>' . PHP_EOL;
				$sExpires .= '  </IfModule>' . PHP_EOL;
				$sExpires .= '' . PHP_EOL;
				$sExpires .= '</IfModule>' . PHP_EOL;
				$sExpires .= '' . PHP_EOL;
				$sExpires .= '<IfModule mod_deflate.c>' . PHP_EOL;
				$sExpires .= 'AddOutputFilterByType DEFLATE text/html' . PHP_EOL;
				$sExpires .= 'AddOutputFilterByType DEFLATE text/css' . PHP_EOL;
				$sExpires .= 'AddOutputFilterByType DEFLATE text/javascript' . PHP_EOL;
				$sExpires .= 'AddOutputFilterByType DEFLATE text/xml' . PHP_EOL;
				$sExpires .= 'AddOutputFilterByType DEFLATE text/plain' . PHP_EOL;
				$sExpires .= 'AddOutputFilterByType DEFLATE image/x-icon' . PHP_EOL;
				$sExpires .= 'AddOutputFilterByType DEFLATE image/svg+xml' . PHP_EOL;
				$sExpires .= 'AddOutputFilterByType DEFLATE application/rss+xml' . PHP_EOL;
				$sExpires .= 'AddOutputFilterByType DEFLATE application/javascript' . PHP_EOL;
				$sExpires .= 'AddOutputFilterByType DEFLATE application/x-javascript' . PHP_EOL;
				$sExpires .= 'AddOutputFilterByType DEFLATE application/xml' . PHP_EOL;
				$sExpires .= 'AddOutputFilterByType DEFLATE application/xhtml+xml' . PHP_EOL;
				$sExpires .= 'AddOutputFilterByType DEFLATE application/font' . PHP_EOL;
				$sExpires .= 'AddOutputFilterByType DEFLATE application/font-truetype' . PHP_EOL;
				$sExpires .= 'AddOutputFilterByType DEFLATE application/font-ttf' . PHP_EOL;
				$sExpires .= 'AddOutputFilterByType DEFLATE application/font-otf' . PHP_EOL;
				$sExpires .= 'AddOutputFilterByType DEFLATE application/font-opentype' . PHP_EOL;
				$sExpires .= 'AddOutputFilterByType DEFLATE application/font-woff' . PHP_EOL;
				$sExpires .= 'AddOutputFilterByType DEFLATE application/font-woff2' . PHP_EOL;
				$sExpires .= 'AddOutputFilterByType DEFLATE application/vnd.ms-fontobject' . PHP_EOL;
				$sExpires .= 'AddOutputFilterByType DEFLATE font/ttf' . PHP_EOL;
				$sExpires .= 'AddOutputFilterByType DEFLATE font/otf' . PHP_EOL;
				$sExpires .= 'AddOutputFilterByType DEFLATE font/opentype' . PHP_EOL;
				$sExpires .= 'AddOutputFilterByType DEFLATE font/woff' . PHP_EOL;
				$sExpires .= 'AddOutputFilterByType DEFLATE font/woff2' . PHP_EOL;
				$sExpires .= '# For Olders Browsers Which Can\'t Handle Compression' . PHP_EOL;
				$sExpires .= 'BrowserMatch ^Mozilla/4 gzip-only-text/html' . PHP_EOL;
				$sExpires .= 'BrowserMatch ^Mozilla/4\.0[678] no-gzip' . PHP_EOL;
				$sExpires .= 'BrowserMatch \bMSIE !no-gzip !gzip-only-text/html' . PHP_EOL;
				$sExpires .= '</IfModule>' . PHP_EOL;
				$sExpires .= '## END EXPIRES CACHING - JCH OPTIMIZE ##' . PHP_EOL;

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
                $htaccess = JchPlatformPaths::rootPath() . '.htaccess';

                if (file_exists($htaccess))
                {
                        $contents = file_get_contents($htaccess);
                        $regex    = '@\n?## BEGIN EXPIRES CACHING - JCH OPTIMIZE ##.*?## END EXPIRES CACHING - JCH OPTIMIZE ##@s';

                        $clean_contents = preg_replace($regex, '', $contents, -1, $count);

			if($count > 0)
			{
				file_put_contents($htaccess, $clean_contents);
			}
                }
	}
}
