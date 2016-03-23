<?php
/**
 * @version   $Id: orderedbody_mainbody.php 6306 2013-01-05 05:39:57Z btowles $
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
class GantryLayoutOrderedBody_MainBody extends GantryLayout {
    var $render_params = array(
        'schema'        =>  null,
        'pushPull'      =>  null,
        'classKey'      =>  null,
        'sidebars'      =>  '',
        'contentTop'    =>  null,
        'contentBottom' =>  null,
        'extraClass' => ''
    );
    function render($params = array()){
        /** @var $gantry Gantry */
		global $gantry;

        $fparams = $this-> _getParams($params);

        // logic to determine if the component should be displayed
        $display_component = !($gantry->get("component-enabled",true)==false && JFactory::getApplication()->input->getString('view') == 'featured');
        ob_start();

		$mbClasses = trim("rt-grid-" . trim($fparams->schema['mb'] . " " . $fparams->pushPull[0] . " " . $fparams->extraClass));
		$mbClasses = preg_replace('/\s\s+/', ' ', $mbClasses);

// XHTML LAYOUT
?>          <div id="rt-main" class="<?php echo $fparams->classKey; ?>">
                <div class="rt-container">
                    <?php foreach($fparams->schema as $position => $value): ?>
                        <?php if ($position != 'mb'): ?>
                            <?php echo $fparams->sidebars[$position]; ?>
                        <?php else: ?>
                            <div class="<?php echo $mbClasses; ?>">
                                <?php if (isset($fparams->contentTop)) : ?>
                                <div id="rt-content-top">
                                    <?php echo $fparams->contentTop; ?>
                                </div>
                                <?php endif; ?>
                                <?php if ($display_component) : ?>
                                <div class="rt-block">
                                    <div id="rt-mainbody">
										<div class="component-content">
                                        	<jdoc:include type="component" />
										</div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <?php if (isset($fparams->contentBottom)) : ?>
                                <div id="rt-content-bottom">
                                    <?php echo $fparams->contentBottom; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <div class="clear"></div>
                </div>
            </div>
<?php
        return ob_get_clean();
    }
}