<?php
/**
 * @version   $Id: doc_tag.php 2381 2012-08-15 04:14:26Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */
defined('GANTRY_VERSION') or die();

gantry_import('core.gantrylayout');

/**
 *
 * @package    gantry
 * @subpackage html.layouts
 */
class GantryLayoutDoc_Tag extends GantryLayout {
    var $render_params = array(
        'classes'       =>  null
    );
    function render($params = array()){
        /** @var $gantry Gantry */
		global $gantry;

        $fparams = $this-> _getParams($params);

    ob_start();
	//XHTML LAYOUT
?><?php if(strlen($fparams->classes) > 0):?>class="<?php echo $fparams->classes; ?>"<?php endif;?><?php
	return ob_get_clean();
    }
}