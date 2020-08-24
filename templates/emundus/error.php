<?php 
defined('_JEXEC') or die ('Restricted access');
 $app	= JFactory::getApplication();
jimport( 'joomla.application.module.helper' );
$params = JFactory::getApplication()->getTemplate(true)->params;
$doc = JFactory::getDocument();
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title><?php echo $this->error->getCode(); ?> - <?php echo htmlspecialchars($this->error->getMessage()); ?></title>
<?php $template_path = JURI::base() . 'templates/' . $app->getTemplate(); ?>
<script type="text/javascript" src="<?php echo $template_path?>/jquery.js">
</script>
<script type="text/javascript">$.noConflict();</script>
<script type="text/javascript" src="<?php echo $template_path?>/customjs.js">
</script>
<?php if ($this->error->getCode()>=400 && $this->error->getCode() < 500) { 	?>
<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/system/css/system.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/system/css/general.css" type="text/css" />
<!--<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/css/bootstrap.css" type="text/css" />-->
<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/css/template.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/css/new.css" type="text/css" />
<!--[if lte IE 8]>
<link rel="stylesheet"  href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/css/menuie.css" type="text/css"/>
<link rel="stylesheet"  href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/css/vmenuie.css" type="text/css"/>
<![endif]-->
<script type="text/javascript" src="<?php echo $template_path?>/height.js">
</script>
<?php
$doc = JFactory::getDocument();
$doc->addScript(JURI::base() .'/templates/' . $this->template . '/js/jui/jquery-ui-1.9.2.custom.min.js', 'text/javascript');
 unset($this->_scripts[JURI::root(true).'/media/jui/js/bootstrap.min.js']);
 $doc->addScript(JURI::base() .'/templates/' . $this->template . '/js/jui/bootstrap.min.js', 'text/javascript');
?>
<script type="text/javascript" src="<?php echo $template_path?>/totop.js">
</script>
<!--[if IE 7]>
<style type="text/css" media="screen">
.ttr_vmenu_items  li.ttr_vmenu_items_parent {display:inline;}
</style>
<![endif]-->
<style type="text/css" media="screen">
.ttr_menu_items {height:auto !important;}
</style>
<!--[if lt IE 9]>
<script type="text/javascript" src="<?php echo $template_path?>/html5shiv.js">
</script>
<script type="text/javascript" src="<?php echo $template_path?>/respond.min.js">
</script>
<![endif]-->
</head>
<body>
<div class="totopshow">
<a href="#" class="back-to-top"><img alt="Back to Top" src="<?php echo $template_path?>/images/gototop.png"/></a>
</div>
<div style="height:0px;width:0px;overflow:hidden;-webkit-margin-top-collapse: separate;"></div>
<div id="ttr_page" class="container">
<div id="ttr_header">
<div id="ttr_header_inner">
</div>
</div>
<div id="ttr_content_and_sidebar_container">
<div id="ttr_content">
<div id="ttr_content_margin">
<div style="height:0px;width:0px;overflow:hidden;-webkit-margin-top-collapse: separate;"></div>
<?php
if(  count(JModuleHelper::getModules('CAModulePosition00'))||  count(JModuleHelper::getModules('CAModulePosition01'))||  count(JModuleHelper::getModules('CAModulePosition02'))||  count(JModuleHelper::getModules('CAModulePosition03'))):
?>
<div class="contenttopcolumn0">
<?php
$showcolumn= count(JModuleHelper::getModules('CAModulePosition00'));
?>
<?php if($showcolumn): ?>
<div style="width:3%;float:left;">
<div class="topcolumn1">
<jdoc:include type="modules" name="CAModulePosition00" style="<?php if($params->get('CAModulePosition00') == 'block'): echo "block"; else: echo "xhtml"; endif;?>"/>
</div>
</div>
<?php else: ?>
<div style="width:3%;float:left;background-color:transparent;">
&nbsp;
</div>
<?php endif; ?>
<?php
$showcolumn= count(JModuleHelper::getModules('CAModulePosition01'));
?>
<?php if($showcolumn): ?>
<div style="width:3%;float:left;">
<div class="topcolumn2">
<jdoc:include type="modules" name="CAModulePosition01" style="<?php if($params->get('CAModulePosition01') == 'block'): echo "block"; else: echo "xhtml"; endif;?>"/>
</div>
</div>
<?php else: ?>
<div style="width:3%;float:left;background-color:transparent;">
&nbsp;
</div>
<?php endif; ?>
<?php
$showcolumn= count(JModuleHelper::getModules('CAModulePosition02'));
?>
<?php if($showcolumn): ?>
<div style="width:3%;float:left;">
<div class="topcolumn3">
<jdoc:include type="modules" name="CAModulePosition02" style="<?php if($params->get('CAModulePosition02') == 'block'): echo "block"; else: echo "xhtml"; endif;?>"/>
</div>
</div>
<?php else: ?>
<div style="width:3%;float:left;background-color:transparent;">
&nbsp;
</div>
<?php endif; ?>
<?php
$showcolumn= count(JModuleHelper::getModules('CAModulePosition03'));
?>
<?php if($showcolumn): ?>
<div style="width:3%;float:right;">
<div class="topcolumn4">
<jdoc:include type="modules" name="CAModulePosition03" style="<?php if($params->get('CAModulePosition03') == 'block'): echo "block"; else: echo "xhtml"; endif;?>"/>
</div>
</div>
<?php else: ?>
<div style="width:3%;float:right;background-color:transparent;">
&nbsp;
</div>
<?php endif; ?>
<div style="clear:both;">
</div>
</div>
<?php endif; ?>
<h2><?php echo JText::_('JERROR_AN_ERROR_HAS_OCCURRED'); ?><br>
<?php echo JText::_('JERROR_LAYOUT_PAGE_NOT_FOUND'); ?></h2>
<div id="searchbox">
<p><?php echo JText::_('JERROR_LAYOUT_SEARCH'); ?></p>
<?php $module = JModuleHelper::getModule( 'search' );
echo JModuleHelper::renderModule( $module);	?>
<p><a href="<?php echo $this->baseurl; ?>/index.php" title="<?php echo JText::_('JERROR_LAYOUT_GO_TO_THE_HOME_PAGE'); ?>"><?php echo JText::_('JERROR_LAYOUT_HOME_PAGE'); ?></a></p>	
</div>
<p><?php echo $this->error->getCode() ; echo $this->error->getMessage();?><br>
<?php echo JText::_('JERROR_LAYOUT_PLEASE_CONTACT_THE_SYSTEM_ADMINISTRATOR'); ?>.</p>
<?php if ($this->debug) :
echo $this->renderBacktrace();
endif; ?>
<?php
if(  count(JModuleHelper::getModules('CBModulePosition00'))||  count(JModuleHelper::getModules('CBModulePosition01'))||  count(JModuleHelper::getModules('CBModulePosition02'))||  count(JModuleHelper::getModules('CBModulePosition03'))):
?>
<div class="contentbottomcolumn0">
<?php
$showcolumn= count(JModuleHelper::getModules('CBModulePosition00'));
?>
<?php if($showcolumn): ?>
<div style="width:3%;float:left;">
<div class="bottomcolumn1">
<jdoc:include type="modules" name="CBModulePosition00" style="<?php if($params->get('CBModulePosition00') == 'block'): echo "block"; else: echo "xhtml"; endif;?>"/>
</div>
</div>
<?php else: ?>
<div style="width:3%;float:left;background-color:transparent;">
&nbsp;
</div>
<?php endif; ?>
<?php
$showcolumn= count(JModuleHelper::getModules('CBModulePosition01'));
?>
<?php if($showcolumn): ?>
<div style="width:3%;float:left;">
<div class="bottomcolumn2">
<jdoc:include type="modules" name="CBModulePosition01" style="<?php if($params->get('CBModulePosition01') == 'block'): echo "block"; else: echo "xhtml"; endif;?>"/>
</div>
</div>
<?php else: ?>
<div style="width:3%;float:left;background-color:transparent;">
&nbsp;
</div>
<?php endif; ?>
<?php
$showcolumn= count(JModuleHelper::getModules('CBModulePosition02'));
?>
<?php if($showcolumn): ?>
<div style="width:3%;float:left;">
<div class="bottomcolumn3">
<jdoc:include type="modules" name="CBModulePosition02" style="<?php if($params->get('CBModulePosition02') == 'block'): echo "block"; else: echo "xhtml"; endif;?>"/>
</div>
</div>
<?php else: ?>
<div style="width:3%;float:left;background-color:transparent;">
&nbsp;
</div>
<?php endif; ?>
<?php
$showcolumn= count(JModuleHelper::getModules('CBModulePosition03'));
?>
<?php if($showcolumn): ?>
<div style="width:3%;float:right;">
<div class="bottomcolumn4">
<jdoc:include type="modules" name="CBModulePosition03" style="<?php if($params->get('CBModulePosition03') == 'block'): echo "block"; else: echo "xhtml"; endif;?>"/>
</div>
</div>
<?php else: ?>
<div style="width:3%;float:right;background-color:transparent;">
&nbsp;
</div>
<?php endif; ?>
<div style="clear:both;">
</div>
</div>
<?php endif; ?>
<div style="height:0px;width:0px;overflow:hidden;-webkit-margin-top-collapse: separate;"></div>
</div>
</div>
<div style="clear:both;">
</div>
</div>
</div>
<?php $showcolumn = count(JModuleHelper::getModules('debug'));
 if ($showcolumn){ ?>
<jdoc:include type="modules" name="debug" style="block" />
<?php } ?>
</body>
</html>
<?php } else { ?>
<?php 
if (!isset($this->error)) {
$this->error = JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
$this->debug = false; 
}
?>
<link rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/system/css/error.css" type="text/css" />
</head>
<body>
<div id="ttr_body_texture">
<div id="ttr_body_specialeffect">
<div class="error">
<div id="outline">
<div id="errorboxoutline">
<div id="errorboxheader"> <?php echo $this->title; ?></div>
<div id="errorboxbody">
<p><strong><?php echo JText::_('JERROR_LAYOUT_NOT_ABLE_TO_VISIT'); ?></strong></p>
<ol>
<li><?php echo JText::_('JERROR_LAYOUT_AN_OUT_OF_DATE_BOOKMARK_FAVOURITE'); ?></li>
<li><?php echo JText::_('JERROR_LAYOUT_SEARCH_ENGINE_OUT_OF_DATE_LISTING'); ?></li>
<li><?php echo JText::_('JERROR_LAYOUT_MIS_TYPED_ADDRESS'); ?></li>
<li><?php echo JText::_('JERROR_LAYOUT_YOU_HAVE_NO_ACCESS_TO_THIS_PAGE'); ?></li>
<li><?php echo JText::_('JERROR_LAYOUT_REQUESTED_RESOURCE_WAS_NOT_FOUND'); ?></li>
<li><?php echo JText::_('JERROR_LAYOUT_ERROR_HAS_OCCURRED_WHILE_PROCESSING_YOUR_REQUEST'); ?></li>
</ol>
<p><strong><?php echo JText::_('JERROR_LAYOUT_PLEASE_TRY_ONE_OF_THE_FOLLOWING_PAGES'); ?></strong></p>
<ul>
<li><a href="<?php echo $this->baseurl; ?>/index.php" title="<?php echo JText::_('JERROR_LAYOUT_GO_TO_THE_HOME_PAGE'); ?>"><?php echo JText::_('JERROR_LAYOUT_HOME_PAGE'); ?></a></li>
<li><a href="<?php echo $this->baseurl; ?>/index.php?option=com_search" title="<?php echo JText::_('JERROR_LAYOUT_SEARCH_PAGE'); ?>"><?php echo JText::_('JERROR_LAYOUT_SEARCH_PAGE'); ?></a></li>
</ul>
<p><?php echo JText::_('JERROR_LAYOUT_PLEASE_CONTACT_THE_SYSTEM_ADMINISTRATOR'); ?>.</p>
<div id="techinfo">
<p><?php echo $this->error->getMessage(); ?></p>
<p>
<?php if ($this->debug) :
echo $this->renderBacktrace();
endif; ?>
</p>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
<?php $showcolumn = count(JModuleHelper::getModules('debug'));
 if ($showcolumn){ ?>
<jdoc:include type="modules" name="debug" style="block" />
<?php } ?>
</body>
</html>
<?php } ?>
