<?php

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
