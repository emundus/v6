<?php
/**
* @version   $Id: component.php 26100 2015-01-27 14:16:12Z james $
* @author RocketTheme http://www.rockettheme.com
* @copyright Copyright (C) 2007 - 2015 RocketTheme, LLC
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */
// no direct access
defined( '_JEXEC' ) or die( 'Restricted index access' );

// load and inititialize gantry class
require_once(dirname(__FILE__) . '/lib/gantry/gantry.php');
$gantry->init();

?>
<?php if (JRequest::getString('type')=='raw'):?>
	<jdoc:include type="component" />
<?php else: ?>
	<!doctype html>
	<html xml:lang="<?php echo $gantry->language; ?>" lang="<?php echo $gantry->language;?>" >
		<head>
			<?php
				$gantry->displayHead();
				if ($gantry->get('layout-mode', 'responsive') == 'responsive') $gantry->addStyle('grid-responsive.css', 5);
				$gantry->addLess('bootstrap.less', 'bootstrap.css', 6);   

			?>
		</head>
		<body class="component-body">
			<div class="component-content">
		    	<jdoc:include type="message" />
				<jdoc:include type="component" />
			</div>
		</body>
	</html>
<?php endif; ?>
<?php
$gantry->finalize();
?>
