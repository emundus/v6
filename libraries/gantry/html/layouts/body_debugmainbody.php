<?php
/**
 * @version   $Id: body_debugmainbody.php 2381 2012-08-15 04:14:26Z btowles $
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
class GantryLayoutBody_DebugMainBody extends GantryLayout {
    var $render_params = array(
        'counter'       =>  null,
        'schema'        =>  null,
        'pushPull'      =>  null,
        'classKey'      =>  null,
        'contents'       =>  null,
        'sidebars'      =>  ''
    );
    function render($params = array()){
        /** @var $gantry Gantry */
		global $gantry;

        $fparams = $this-> _getParams($params);

        ob_start();
// XHTML LAYOUT
?>      <div id="rt-main" class="<?php echo $fparams->classKey; ?>">
            <span class="status">(<?php echo $fparams->counter; ?>) <?php echo $fparams->classKey; ?></span>
            <div class="rt-grid-<?php echo $fparams->schema['mb']; ?> <?php echo $fparams->pushPull[0]; ?>">
                <div class="rt-block">
                    <div id="rt-mainbody">
                        <?php echo $fparams->contents; ?>
                    </div>
                </div>
            </div>
            <?php echo $fparams->sidebars; ?>
            <div class="clear"></div>
        </div>
<?php
        return ob_get_clean();
    }
}