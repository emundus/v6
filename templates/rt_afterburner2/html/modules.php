<?php
/**
* @version   $Id: modules.php 26100 2015-01-27 14:16:12Z james $
* @author    RocketTheme http://www.rockettheme.com
* @copyright Copyright (C) 2007 - 2015 RocketTheme, LLC
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
* Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * This is a file to add template specific chrome to module rendering.  To use it you would
 * set the style attribute for the given module(s) include in your template to use the style
 * for each given modChrome function.
 *
 * eg.  To render a module mod_test in the sliders style, you would use the following include:
 * <jdoc:include type="module" name="test" style="slider" />
 *
 * This gives template designers ultimate control over how modules are rendered.
 *
 * NOTICE: All chrome wrapping methods should be named: modChrome_{STYLE} and take the same
 * two arguments.
 */

function modChrome_basic($module, &$params, &$attribs)
{
	if (!empty ($module->content)) : ?>
		<?php echo $module->content; ?>
	<?php endif;
}

function modChrome_standard($module, &$params, &$attribs)
{
 	if (!empty ($module->content)) : ?>
           <div class="rt-block <?php if ($params->get('moduleclass_sfx')!='') : ?><?php echo $params->get('moduleclass_sfx'); ?><?php endif; ?>">
           	<div class="module-surround">
	           	<?php if ($module->showtitle != 0) : ?>
			<div class="module-title">
	                		<h2 class="title"><?php echo $module->title; ?></h2>
			</div>
	                	<?php endif; ?>
	                	<div class="module-content">
	                		<?php echo $module->content; ?>
	                	</div>
                	</div>
           </div>
	<?php endif;
}

function modChrome_menu($module, &$params, &$attribs)
{
    	
	if (!empty ($module->content)) : ?>
	<div class="rt-block menu-block">
		<?php echo $module->content; ?>
		<div class="clear"></div>
	</div>
	<?php endif;
}

?>
