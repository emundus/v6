<?php
/**
 * @version   $Id: totop.php 5520 2012-11-29 17:37:42Z djamil $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
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
class GantryFeatureToTop extends GantryFeature
{
	var $_feature_name = 'totop';

	function init()
	{
		/** @var $gantry Gantry */
		global $gantry;
		JHTML::_('behavior.framework', true);

		if ($this->get('enabled')) {
			$gantry->addScript('gantry-totop.js');
		}
	}

	function render($position)
	{
		ob_start();
		?>
	<div class="clear"></div>
	<div class="rt-block">
		<a href="#" id="gantry-totop" rel="nofollow"><?php echo $this->get('text'); ?></a>
	</div>
	<?php
		return ob_get_clean();
	}
}
