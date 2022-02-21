<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/joomla-platform
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2020 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

use JchOptimize\Platform\Plugin;
use JchOptimize\Platform\Paths;
use JchOptimize\Core\Css\Sprite\SpriteGenerator;

defined('_JEXEC') or die;

if (!defined('_JCH_EXEC'))
{
	define('_JCH_EXEC', 1);
}

include_once dirname(dirname(__FILE__) ). '/vendor/autoload.php';

class JFormFieldSpriteformat extends JFormFieldList
{

        public $type = 'spriteformat';

        protected function getOptions()
        {
                $plugin       = Plugin::getPlugin();
                $pluginParams = new JRegistry();
                $pluginParams->loadString($plugin->params);
                
                $pluginParams->set('sprite-path', Paths::spritePath());

                $CssSpriteGenClass = 'JchOptimize\Core\Css\Sprite\CssSpriteGen';
                
                $oSprite = new SpriteGenerator(\JchOptimize\Platform\Settings::getInstance($pluginParams));

                /** @var \JchOptimize\Core\Css\Sprite\CssSpriteGen $CssSpriteGen */
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
