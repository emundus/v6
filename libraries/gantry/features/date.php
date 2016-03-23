<?php
/**
 * @version   $Id: date.php 4246 2012-10-11 22:06:13Z kevin $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */

defined('JPATH_BASE') or die();

gantry_import('core.gantryfeature');
gantry_import('core.utilities.gantrydate');
/**
 * @package     gantry
 * @subpackage  features
 */
class GantryFeatureDate extends GantryFeature
{
	var $_feature_name = 'date';

	function render($position)
	{
		/** @var $gantry Gantry */
		global $gantry;
		ob_start();
		$now    = new GantryDate();
		$format = $this->get('formats');
		?>
    <div class="date-block">
        <span class="date"><?php echo $now->toFormat($format); ?></span>
    </div>
	<?php
		return ob_get_clean();
	}

}
