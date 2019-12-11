<?php
/**
 * Dropfiles
 *
 * @package    Joomla.Site
 * @subpackage Templates.dropfiles
 *
 * @copyright Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') || die;

$app = JFactory::getApplication();
$doc = JFactory::getDocument();
$user = JFactory::getUser();
$this->language = $doc->language;
$this->direction = $doc->direction;

// Getting params from template
$params = $app->getTemplate(true)->params;

// Detecting Active Variables
$option = $app->input->getCmd('option', '');
$view = $app->input->getCmd('view', '');
$layout = $app->input->getCmd('layout', '');
$task = $app->input->getCmd('task', '');
$itemid = $app->input->getCmd('Itemid', '');
$sitename = $app->get('sitename');

// Add JavaScript Frameworks
JHtml::_('bootstrap.framework');

// Add Stylesheets
$doc->addStyleSheet($this->baseurl . '/templates/' . $this->template . '/css/template.css');

// Load optional RTL Bootstrap CSS
JHtml::_('bootstrap.loadCss', false, $this->direction);


?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>"
      lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <base href=""/>
    <jdoc:include type="head"/>
    <?php // Use of Google Font ?>
    <?php if ($this->params->get('googleFont')) : ?>
        <link href='//fonts.googleapis.com/css?family=<?php echo $this->params->get('googleFontName'); ?>'
              rel='stylesheet' type='text/css'/>
        <style type="text/css">
            h1, h2, h3, h4, h5, h6, .site-title {
                font-family: '<?php echo str_replace('+', ' ', $this->params->get('googleFontName')); ?>', sans-serif;
            }
        </style>
    <?php endif; ?>
    <?php // Template color ?>
    <?php if ($this->params->get('templateColor')) : ?>
        <style type="text/css">
            body.site {
                border-top: 3px solid <?php echo $this->params->get('templateColor'); ?>;
                background-color: <?php echo $this->params->get('templateBackgroundColor'); ?>
            }

            a {
                color: <?php echo $this->params->get('templateColor'); ?>;
            }

        </style>
    <?php endif; ?>
    <!--[if lt IE 9]>
    <script src="<?php echo JUri::root(true); ?>/media/jui/js/html5.js"></script>
    <![endif]-->
</head>

<body class="site <?php echo $option
    . ' view-' . $view
    . ($layout ? ' layout-' . $layout : ' no-layout')
    . ($task ? ' task-' . $task : ' no-task')
    . ($itemid ? ' itemid-' . $itemid : '')
    . ($params->get('fluidContainer') ? ' fluid' : '');
echo($this->direction === 'rtl' ? ' rtl' : '');
?>">

<!-- Body -->
<div class="body">
    <div class="container-fluid">
        <!-- Header -->
        <header class="header" role="banner">
        </header>

        <div class="row-fluid">
            <main id="content" role="main" class="span12">
                <jdoc:include type="message"/>
                <jdoc:include type="component"/>
            </main>
        </div>
    </div>
</div>
<!-- Footer -->
<footer class="footer" role="contentinfo">

</footer>
<jdoc:include type="modules" name="debug" style="none"/>
</body>
</html>
