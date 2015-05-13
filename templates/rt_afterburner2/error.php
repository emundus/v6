<?php
/**
* @version   $Id: error.php 26100 2015-01-27 14:16:12Z james $
* @author    RocketTheme http://www.rockettheme.com
* @copyright Copyright (C) 2007 - 2015 RocketTheme, LLC
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*
* Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
*
*/
defined( '_JEXEC' ) or die( 'Restricted access' );
if (!isset($this->error)) {
	$this->error = JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
	$this->debug = false;
}

// load and inititialize gantry class
global $gantry;
require_once(dirname(__FILE__) . '/lib/gantry/gantry.php');
$gantry->init();

$doc = JFactory::getDocument();
$doc->setTitle($this->error->getCode() . ' - '.$this->title);

if ($gantry->get('layout-mode', 'responsive') == 'responsive') $gantry->addStyle('grid-responsive.css', 5);
$gantry->addLess('bootstrap.less', 'bootstrap.css', 6);   

if ($gantry->browser->name == 'ie') {
	if ($gantry->browser->shortversion == 8) {
		$gantry->addScript('html5shim.js');
	}
}
ob_start();
?>
<body <?php echo $gantry->displayBodyTag(); ?>>
    <header id="rt-top-surround">
		<div id="rt-top" <?php echo $gantry->displayClassesByTag('rt-top'); ?>>
			<div class="rt-container">
				<?php echo $gantry->displayModules('top','standard','standard'); ?>
				<div class="clear"></div>
			</div>
		</div>
		<div id="rt-header">
			<div class="rt-container">
				<?php echo $gantry->displayModules('header','standard','standard'); ?>
				<div class="clear"></div>
			</div>
		</div>
	</header>	
	<div class="rt-container">
		<div class="component-content">
			<div class="rt-grid-12">
				<div class="rt-block">
					<div class="rt-error-rocket"></div>
					<div class="rt-error-content">
						<h1 class="error-title title">Error: <span><?php echo $this->error->getCode(); ?></span> - <?php echo $this->error->getMessage(); ?></h1>
						<div class="error-content">
						<p><strong>You may not be able to visit this page because of:</strong></p>
						<ol>
							<li>an out-of-date bookmark/favourite</li>
							<li>a search engine that has an out-of-date listing for this site</li>
							<li>a mistyped address</li>
							<li>you have no access to this page</li>
							<li>The requested resource was not found.</li>
							<li>An error has occurred while processing your request.</li>
						</ol>
						<p><a href="<?php echo $gantry->baseUrl; ?>" class="readon"><span>&larr; Home</span></a></p>
					</div>
				</div>
			</div>
		</div>
	</div>
</body>
</html>
<?php

$body = ob_get_clean();
$gantry->finalize();

require_once(JPATH_LIBRARIES.'/joomla/document/html/renderer/head.php');
$header_renderer = new JDocumentRendererHead($doc);
$header_contents = $header_renderer->render(null);
ob_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<?php echo $header_contents; ?>
</head>
<?php
$header = ob_get_clean();
echo $header.$body;;
