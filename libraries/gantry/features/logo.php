<?php
/**
 * @version   $Id: logo.php 2468 2012-08-17 06:16:57Z btowles $
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
class GantryFeaturelogo extends GantryFeature
{
	var $_feature_name = 'logo';
	var $_autosize = false;


	function render($position)
	{
		/** @var $gantry Gantry */
		global $gantry;


		// default location for custom icon is {template}/images/logo/logo.png, with 'perstyle' it's
		// located in {template}/images/logo/styleX/logo.png
		if ($gantry->get("logo-autosize")) {

			jimport('joomla.filesystem.file');

			$path    = $gantry->templatePath . '/' . 'images' . '/' . 'logo';
			$logocss = $gantry->get('logo-css', 'body #rt-logo');

			// get proper path based on perstyle hidden param
			$path = (intval($gantry->get("logo-perstyle", 0)) === 1) ? $path . '/' . $gantry->get("cssstyle") . '/' : $path . '/';
			// append logo file
			$path .= 'logo.png';

			// if the logo exists, get it's dimentions and add them inline
			if (JFile::exists($path)) {
				$logosize = getimagesize($path);
				if (isset($logosize[0]) && isset($logosize[1])) {
					$gantry->addInlineStyle($logocss . ' {width:' . $logosize[0] . 'px;height:' . $logosize[1] . 'px;}');
				}
			}
		}

		ob_start();
		?>
	<div class="rt-block">
		<a href="<?php echo $gantry->baseUrl; ?>" id="rt-logo"></a>
	</div>
	<?php
		return ob_get_clean();
	}
}