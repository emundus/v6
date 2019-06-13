<?php

use JchOptimize\CssSpriteGen;

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

class JchOptimizeSpriteGenerator
{

        public $params = null;

        /**
         * Constructor
         * 
         * @param type $params
         */
        public function __construct($params)
        {
                $this->params = $params;
        }
        
        /**
         * 
         * @return string|boolean
         */
        public function getImageLibrary()
        {
                if(!extension_loaded('exif'))
                {
                      JchOptimizeLogger::log('EXIF extension not loaded', $this->params);
                      
                      return FALSE;
                }
                
                if (extension_loaded('imagick'))
                {
                        $sImageLibrary = 'imagick';
                }
                else
                {
                        if (!extension_loaded('gd'))
                        {
                                JchOptimizeLogger::log('No image manipulation library installed', $this->params);
                                
                                return FALSE;
                        }

                        $sImageLibrary = 'gd';
                }
                
                return $sImageLibrary;
        }

        /**
         * Grabs background images with no-repeat attribute from css and merge them in one file called a sprite.
         * Css is updated with sprite url and correct background positions for affected images.
         * Sprite saved in {Joomla! base}/images/jch-optimize/
         *
         * @param string $sCss       Aggregated css file before sprite generation
         * @return string           Css updated with sprite information on success. Original css on failure
         */
        public function getSprite($sCss)
        {
                $sImageLibrary = $this->getImageLibrary();

                $aMatches = $this->processCssUrls($sCss);
                        
                if (empty($aMatches) || $sImageLibrary === FALSE)
                {
                        return $sCss;
                }
                
                $this->params->set('sprite-path', JchPlatformPaths::spriteDir());

                $aSearch = $this->generateSprite($aMatches, new CssSpriteGen($sImageLibrary, $this->params));

                return $aSearch;
        }

        /**
         * Generates sprite image and return background positions for image replaced with sprite
         * 
         * @param array $aMatches       Array of css declarations and image url to be included in sprite
         * @param object $oSpriteGen    Object of sprite generator
         * @return array
         */
        public function generateSprite($aMatches, $oSpriteGen)
        {
                JCH_DEBUG ? JchPlatformProfiler::start('GenerateSprite') : null;
                
                $aDeclaration = $aMatches[0];
                $aImages      = $aMatches[1];

                $oSpriteGen->CreateSprite($aImages);
                $aSpriteCss = $oSpriteGen->GetCssBackground();

                $aPatterns    = array();
                $aPatterns[0] = '#background-position:[^;}]+;?#i'; //Background position declaration regex
                $aPatterns[1] = '#(background:[^;}]*)\b' //Background position regex
                        . '((?:top|bottom|left|right|center|-?[0-9]+(?:%|[c-x]{2})?)'
                        . '\s(?:top|bottom|left|right|center|-?[0-9]+(?:%|[c-x]{2})?))([^;}]*[;}])#i';
                $aPatterns[2] = '#(background-image:)[^;}]+;?#i'; // Background image declaration regex
		$aPatterns[3] = '#(background:[^;}]*)\b'
			. 'url\((?=[^\)]+\.(?:png|gif|jpe?g))[^\)]+\)'
			. '([^;}]*[;}])#i'; //Background image regex

                $sSpriteName = $oSpriteGen->GetSpriteFilename();

                $aSearch = array();
		$spritepath = JchPlatformPaths::spriteDir(true) . $sSpriteName;
		$spritepath = JchOptimizeHelper::cookieLessDomain($this->params, $spritepath, $spritepath);

                for ($i = 0; $i < count($aSpriteCss); $i++)
                {
                        if (isset($aSpriteCss[$i]))
                        {
                                $aSearch['needles'][] = $aDeclaration[$i];
                                
                                $aReplacements    = array();
                                $aReplacements[0] = '';
                                $aReplacements[1] = '$1$3';
                                $aReplacements[2] = '$1 url(' . $spritepath . '); background-position: ' . $aSpriteCss[$i] . ';';
                                $aReplacements[3] = '$1url(' . $spritepath . ') ' . $aSpriteCss[$i] . '$2';

                                $sReplacement = preg_replace($aPatterns, $aReplacements, $aDeclaration[$i]);

                                if (is_null($sReplacement))
                                {
                                        throw new Exception('Error finding replacements for sprite background positions');
                                }

                                $aSearch['replacements'][] = $sReplacement;
                        }
                }

                JCH_DEBUG ? JchPlatformProfiler::stop('GenerateSprite', TRUE) : null;
                
                return $aSearch;
        }

        /**
         * Uses regex to find css declarations containing background images to include in sprite
         * 
         * @param string $sCss  Aggregated css file
         * @return array        Array of css declarations and image urls to replace with sprite
         */
        public function processCssUrls($sCss, $bBackend = FALSE)
        {
                JCH_DEBUG ? JchPlatformProfiler::start('ProcessCssUrls') : null;
                
                $params         = $this->params;
                $aRegexStart    = array();
                $aRegexStart[0] = '
                        #(?:{
                                (?=\s*+(?>[^}\s:]++[\s:]++)*?url\(
                                        (?=[^)]+\.(?:png|gif|jpe?g))
                                ([^)]+)\))';
                $aRegexStart[1] = '
                        (?=\s*+(?>[^}\s:]++[\s:]++)*?no-repeat)';
                $aRegexStart[2] = '
                        (?(?=\s*(?>[^};]++[;\s]++)*?background(?:-position)?\s*+:\s*+(?:[^;}\s]++[^}\S]++)*?
                                (?<p>(?:[tblrc](?:op|ottom|eft|ight|enter)|-?[0-9]+(?:%|[c-x]{2})?))(?:\s+(?&p))?)
                                        (?=\s*(?>[^};]++[;\s]++)*?background(?:-position)?\s*+:\s*+(?>[^;}\s]++[\s]++)*?
                                                (?:left|top|0(?:%|[c-x]{2})?)\s+(?:left|top|0(?:%|[c-x]{2})?)
                                        )
                        )';
                $sRegexMiddle   = '   
                        [^{}]++} )';
                $sRegexEnd      = '#isx';

                $aIncludeImages  = JchOptimizeHelper::getArray($params->get('csg_include_images'));
                $aExcludeImages  = JchOptimizeHelper::getArray($params->get('csg_exclude_images'));
                $sIncImagesRegex = '';

                if (!empty($aIncludeImages[0]))
                {
                        foreach ($aIncludeImages as &$sImage)
                        {
                                $sImage = preg_quote($sImage, '#');
                        }

                        $sIncImagesRegex .= '
                                |(?:{
                                        (?=\s*+(?>[^}\s:]++[\s:]++)*?url';
                        $sIncImagesRegex .= ' (?=[^)]* [/(](?:' . implode('|', $aIncludeImages) . ')\))';
                        $sIncImagesRegex .= '\(([^)]+)\)
                                        )
                                        [^{}]++ })';
                }
                $sExImagesRegex = '';
                if (!empty($aExcludeImages[0]))
                {
                        $sExImagesRegex .= '(?=\s*+(?>[^}\s:]++[\s:]++)*?url\(
                                                        [^)]++  (?<!';

                        foreach ($aExcludeImages as &$sImage)
                        {
                                $sImage = preg_quote($sImage, '#');
                        }

                        $sExImagesRegex .= implode('|', $aExcludeImages) . ')\)
                                                        )';
                }

                $sRegexStart = implode('', $aRegexStart);
                $sRegex      = $sRegexStart . $sExImagesRegex . $sRegexMiddle . $sIncImagesRegex . $sRegexEnd;

                if (preg_match_all($sRegex, $sCss, $aMatches) === FALSE)
                {
                        throw new Exception('Error occurred matching for images to sprite');
                }

                if (isset($aMatches[3]))
                {
                        $total = count($aMatches[1]);
                        
                        for($i = 0; $i < $total;  $i++)
                        {
                                $aMatches[1][$i] = trim($aMatches[1][$i])? $aMatches[1][$i] : $aMatches[3][$i];
                        }
                }

                if ($bBackend)
                {
                        $aImages        = array();
                        $aImagesMatches = array();

                        $aImgRegex    = array();
                        $aImgRegex[0] = $aRegexStart[0];
                        $aImgRegex[2] = $aRegexStart[1];
                        $aImgRegex[4] = $sRegexMiddle;
                        $aImgRegex[5] = $sRegexEnd;

                        $sImgRegex = implode('', $aImgRegex);

                        if (preg_match_all($sImgRegex, $sCss, $aImagesMatches) === FALSE)
                        {
                                throw new Exception('Error occurred matching for images to include');
                        }

                        $aImagesMatches[0] = array_diff($aImagesMatches[0], $aMatches[0]);
                        $aImagesMatches[1] = array_diff($aImagesMatches[1], $aMatches[1]);

                        $oImageLibrary = $this->getImageLibrary();
                        
                        if($oImageLibrary === FALSE)
                        {
                                return array();
                        }
                        
                        $oCssSpriteGen = new CssSpriteGen($oImageLibrary, $this->params, $bBackend);
                        
                        $aImages['include'] = $oCssSpriteGen->CreateSprite($aImagesMatches[1]);
                        $aImages['exclude'] = $oCssSpriteGen->CreateSprite($aMatches[1]);
                        
                        

                        return $aImages;
                }

                JCH_DEBUG ? JchPlatformProfiler::stop('ProcessCssUrls', TRUE) : null;
                
                return $aMatches;
        }

}
