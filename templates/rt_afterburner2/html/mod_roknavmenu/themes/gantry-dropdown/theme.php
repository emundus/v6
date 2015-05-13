<?php
/**
* @version   $Id: theme.php 26100 2015-01-27 14:16:12Z james $
* @author    RocketTheme http://www.rockettheme.com
* @copyright Copyright (C) 2007 - 2015 RocketTheme, LLC
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*
* Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
*
 */

class GantryDropdownTheme extends AbstractRokMenuTheme {

    protected $defaults = array(
        'enable_js' => 1,
        'opacity' => 1,
        'effect' => 'slidefade',
        'hidedelay' => 500,
        'menu-animation' => 'Quad.easeOut',
        'menu-duration' => 400,
        'centered-offset' => 0,
        'tweak-initial-x' => -3,
        'tweak-initial-y' => 0,
        'tweak-subsequent-x' => 0,
        'tweak-subsequent-y' => 1,
        'tweak-width' => 0,
        'tweak-height' => 0,
        'enable_current_id' => 0,
        'responsive-menu' => 'panel'
    );

    public function getFormatter($args){
        require_once(dirname(__FILE__) . '/formatter.php');
        return new GantryDropdownFormatter($args);
    }

    public function getLayout($args){
        require_once(dirname(__FILE__) . '/layout.php');
        return new GantryDropdownLayout($args);
    }
}
