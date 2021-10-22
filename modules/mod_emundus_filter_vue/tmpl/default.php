<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
require_once (JPATH_ROOT.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'access.php');
$document = JFactory::getDocument();
$document->addScript('media/mod_emundus_filter_vue/chunk-vendors_filter.js');
$document->addStyleSheet('media/mod_emundus_filter_vue/app_filter.css');

?>
<div id="em-filter-builder-vue"></div>

<script src="media/mod_emundus_filter_vue/app_filter.js"></script>
