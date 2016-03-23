<?php
/**
 * @version   $Id: body_iphonemainbody.php 6306 2013-01-05 05:39:57Z btowles $
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
class GantryLayoutBody_iPhoneMainBody extends GantryLayout {
    var $render_params = array(
        'schema'        =>  null,
        'classKey'      =>  null
    );
    function render($params = array()){
        /** @var $gantry Gantry */
		global $gantry;

        $fparams = $this-> _getParams($params);

        // logic to determine if the component should be displayed
        $display_component = !($gantry->get("component-enabled",true)==false && JFactory::getApplication()->input->getString('view') == 'featured');
        ob_start();
// XHTML LAYOUT
?>          <div id="rt-main" class="<?php echo $fparams->classKey; ?>">
                <div class="rt-container">
                    <div class="rt-grid-12">
                        <div class="rt-block">
                            <?php if ($display_component) : ?>
                            <div id="rt-mainbody">
								<div class="component-content">
                                	<jdoc:include type="component" />
								</div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
<?php
        return ob_get_clean();
    }
}