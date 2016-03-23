<?php
/**
 * @version   $Id: analytics.php 21698 2014-06-25 17:44:42Z djamil $
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
class GantryFeatureAnalytics extends GantryFeature
{

	var $_feature_name = 'analytics';

	function init()
	{
		/** @var $gantry Gantry */
		global $gantry;

		ob_start();
		// start of Google Analytics javascript
		?>
		(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		})(window,document,'script','//www.google-analytics.com/analytics.js','__gaTracker');
		__gaTracker('create', '<?php echo $this->get('code'); ?>', 'auto');
		__gaTracker('send', 'pageview');
	<?php
		// end of Google Analytics javascript
		$gantry->addInlineScript(ob_get_clean());
	}
}
