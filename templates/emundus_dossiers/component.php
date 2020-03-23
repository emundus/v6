<?php
/**
 * @package     Joomla.Site
 * @subpackage  Template
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$app             = JFactory::getApplication();
$doc             = JFactory::getDocument();
$this->language  = $doc->language;
$this->direction = $doc->direction;

// Add JavaScript Frameworks
JHtml::_('bootstrap.framework');

// Add Stylesheets
////$doc->addStyleSheet('templates/' . $this->template . '/css/bootstrap.css');
if ($this->direction == 'rtl'){
$doc->addStyleSheet('templates/'.$this->template.'/css/template_rtl.css');
}else{
$doc->addStyleSheet('templates/'.$this->template.'/css/template.css');
}

// Load optional rtl Bootstrap css and Bootstrap bugfixes
JHtmlBootstrap::loadCss($includeMaincss = false, $this->direction);

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
<jdoc:include type="head" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<!--[if lt IE 9]>
	<script src="<?php echo $this->baseurl; ?>/media/jui/js/html5.js"></script>
<![endif]-->
</head>
<body class="contentpane">
<div id="ttr_content" style="width:100%">
<div id="ttr_content_margin">
<div style="height:0px;width:0px;overflow:hidden;-webkit-margin-top-collapse: separate;"></div>
<div id="system-message-container"></div>
	<jdoc:include type="message" />
	<jdoc:include type="component" />
</div>
</div>
</body>
</html>
