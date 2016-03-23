<?php
/**
 * @version   $Id: mod_standard.php 2381 2012-08-15 04:14:26Z btowles $
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
class GantryLayoutMod_Standard extends GantryLayout {
    var $render_params = array(
        'contents'      =>  null,
        'gridCount'     =>  null,
        'prefixCount'   =>  0,
        'extraClass'      =>  ''
    );
    function render($params = array()){
        /** @var $gantry Gantry */
		global $gantry;

        $rparams = $this-> _getParams($params);

        $prefixClass = '';

        if ($rparams->prefixCount !=0) {
            $prefixClass = " rt-prefix-".$rparams->prefixCount;
        }
        ob_start();
        // XHTML LAYOUT
?>
<div class="rt-grid-<?php echo $rparams->gridCount.$prefixClass.$rparams->extraClass; ?>">
    <?php echo $rparams->contents;  ?>
</div>
<?php

        return ob_get_clean();
    }
}