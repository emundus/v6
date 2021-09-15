<?php
/**
 * Dropfiles
 *
 * @package    Joomla.Site
 * @subpackage Templates.protostar
 *
 * @copyright Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') || die;

$app = JFactory::getApplication();
$doc = JFactory::getDocument();
$this->language = $doc->language;
$this->direction = $doc->direction;

// Add JavaScript Frameworks
JHtml::_('bootstrap.framework');

// Add Stylesheets
$doc->addStyleSheet($this->baseurl . '/templates/' . $this->template . '/css/template.css');

// Load optional rtl Bootstrap css and Bootstrap bugfixes
$includeMaincss = false;
JHtmlBootstrap::loadCss($includeMaincss, $this->direction);

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>"
      lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>
    <jdoc:include type="head"/>
    <!--[if lt IE 9]>
    <script src="<?php echo JUri::root(true); ?>/media/jui/js/html5.js"></script>
    <![endif]-->
</head>
<body class="contentpane component">
<jdoc:include type="message"/>
<jdoc:include type="component"/>
</body>
</html>
