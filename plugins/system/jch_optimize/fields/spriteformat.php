<?php

defined('_JEXEC') or die;

include_once dirname(dirname(__FILE__)) . '/jchoptimize/loader.php';

class JFormFieldSpriteformat extends JFormFieldList
{

        public $type = 'spriteformat';

        protected function getOptions()
        {
                $plugin       = JchPlatformPlugin::getPlugin();
                $pluginParams = new JRegistry();
                $pluginParams->loadString($plugin->params);
                
                $pluginParams->set('sprite-path', JchPlatformPaths::spriteDir());

                $CssSpriteGenClass = 'JchOptimize\CssSpriteGen';
                
                $oSprite = new JchOptimizeSpriteGenerator($pluginParams);
                
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
