<?php
/**
* @version   $Id: dropdownmenu.php 9290 2013-04-11 22:12:18Z kevin $
* @author    RocketTheme http://www.rockettheme.com
* @copyright Copyright (C) 2007 - 2015 RocketTheme, LLC
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*
* Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
*
*/

defined('JPATH_BASE') or die();
gantry_import('core.gantryfeature');


/**
 * @package     gantry
 * @subpackage  features
 */
class GantryFeatureDropdownMenu extends GantryFeature {
    var $_feature_name = 'dropdownmenu';
    var $_legacy_name = 'fusionmenu';
    var $_feature_prefix = 'menu';
    var $_menu_picker = 'menu-type';

    function isEnabled() {
		global $gantry;
        $menu_enabled = $gantry->get('menu-enabled');
        $selected_menu = $gantry->get($this->_menu_picker);

        $cookie = 0;

        if (1 == (int)$menu_enabled && ($selected_menu == $this->_feature_name || $selected_menu == $this->_legacy_name) && $cookie == 0) return true;
        return false;
    }

    function isOrderable() {
        return false;
    }

    function render($position) {
		global $gantry;

        $renderer = $gantry->document->loadRenderer('module');
        $options = array('style' => "menu");
        $module = JModuleHelper::getModule('mod_roknavmenu','_z_empty');

        $params = $gantry->getParams($this->_feature_prefix . "-" . $this->_feature_name, true);
        $reg = new JRegistry();
        foreach ($params as $param_name => $param_value)
        {
            $reg->set($param_name, $param_value['value']);
        }
		$reg->set('style', 'menu');
        $module->params = $reg->toString();
        $rendered_menu = $renderer->render($module, $options);

        return $rendered_menu;
    }
}
