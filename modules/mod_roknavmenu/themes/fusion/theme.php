<?php
/**
 * @version   $Id: theme.php 4585 2012-10-27 01:44:54Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2017 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokNavMenuFusionTheme extends AbstractRokMenuTheme {

    protected $defaults = array(
        'roknavmenu_fusion_load_css' => 1,
        'roknavmenu_fusion_enable_js' => 1,
        'roknavmenu_fusion_opacity' => 1,
        'roknavmenu_fusion_effect' => 'slidefade',
        'roknavmenu_fusion_hidedelay' => 500,
        'roknavmenu_fusion_menu_animation' => 'Sine.easeOut',
        'roknavmenu_fusion_menu_duration' => 700,
        'roknavmenu_fusion_pill' => 0,
        'roknavmenu_fusion_pill_animation' => 'Sine.easeOut',
        'roknavmenu_fusion_pill_duration' => 700,
        'roknavmenu_fusion_centeredOffset' => 0,
        'roknavmenu_fusion_tweakInitial_x' => -3,
        'roknavmenu_fusion_tweakInitial_y' => 0,
        'roknavmenu_fusion_tweakSubsequent_x' => 0,
        'roknavmenu_fusion_tweakSubsequent_y' => 1,
        'roknavmenu_fusion_tweak-width' => 0,
        'roknavmenu_fusion_tweak-height' => 0,
        'roknavmenu_fusion_enable_current_id' => 0
    );

    public function getFormatter($args){
        require_once(dirname(__FILE__) . '/formatter.php');
        return new RokNavMenuFusionFormatter($args);
    }

    public function getLayout($args){
        require_once(dirname(__FILE__) . '/layout.php');
        return new RokMavMenuFusionLayout($args);
    }
}
