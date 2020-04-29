<?php

/**
 * JCH Optimize - Joomla! plugin to aggregate and minify external resources for
 * optimized downloads
 * @author Samuel Marshall <sdmarshall73@gmail.com>
 * @copyright Copyright (c) 2010 Samuel Marshall
 * @license GNU/GPLv3, See LICENSE file
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

use JchOptimize\Platform\Plugin;
use JchOptimize\Platform\Paths;
use JchOptimize\Core\SpriteGenerator;

defined('_JEXEC') or die;

include_once dirname(__FILE__) . '/jchoptimize/loader.php';

class JFormFieldSpriteformat extends JFormFieldList
{

        public $type = 'spriteformat';

        protected function getOptions()
        {
                $plugin       = Plugin::getPlugin();
                $pluginParams = new JRegistry();
                $pluginParams->loadString($plugin->params);
                
                $pluginParams->set('sprite-path', Paths::spritePath());

                $CssSpriteGenClass = 'JchOptimize\LIBS\CssSpriteGen';
                
                $oSprite = new SpriteGenerator(\JchOptimize\Platform\Settings::getInstance($pluginParams));

                /** @var \JchOptimize\LIBS\CssSpriteGen $CssSpriteGen */
                $CssSpriteGen = new $CssSpriteGenClass($oSprite->getImageLibrary(), $pluginParams);
                
                $aSpriteFormats = $CssSpriteGen->GetSpriteFormats();
                
                $this->default = $aSpriteFormats[0];
                
                $options = array();
                
                foreach($aSpriteFormats as $sSpriteFormat)
                {
                        $option = JHtml::_('select.option', $sSpriteFormat, $sSpriteFormat, 'value', 'text');
                        $options[] = $option;
                }
                
                reset($options);

		return $options;
        }

}
