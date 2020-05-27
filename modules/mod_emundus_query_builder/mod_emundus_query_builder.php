<?php
defined('_JEXEC') or die('Access Deny');
require_once(dirname(__FILE__).DS.'helper.php');
require_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'stats.php');
// ini_set("xdebug.var_display_max_children", -1);
// ini_set("xdebug.var_display_max_data", -1);
// ini_set("xdebug.var_display_max_depth", -1);

JHtml::script('media/com_emundus/js/jquery.cookie.js');
JHtml::script('media/jui/js/bootstrap.min.js');

$document   = JFactory::getDocument();
$document->addStyleSheet("modules/mod_emundus_query_builder/style/mod_emundus_query_builder.css" );

$helper = new modEmundusQueryBuilderHelper;

$tabModule = $helper->getModuleStat();

$showModule = "<div class='showModule'>";
$i = 0;
foreach($tabModule as $mod) {
	$typeMod = $helper->getTypeStatModule($mod['id']);
	$view = json_decode($mod['params'], true)['view'];
	
	$showModule .= "<div class='input' id='order_".$mod['id']."'>";
	if($i != 0) $showModule .= "<input type='button'  class='btn' value='&#9652;' onclick='changeOrder(".$mod['id'].", \"-\")'/>";
	if($i != count($tabModule)-1) $showModule .= "<input type='button'  class='btn' value='&#9662;' onclick='changeOrder(".$mod['id'].", \"+\")'/>";
	$showModule .= "<input type='checkbox' id='".$mod['title']."' value='".$mod['id']."' onchange='changePublished(".$mod['id'].")' ".(($mod['published'] == 1)?"checked":"").">
	<a href='#chart-container-".$view."'><label>".$mod['title']."</label></a>
	<input type='button' class='btn' value='Editer' onclick='modifyModule(".$mod['id'].", \"".$mod['title']."\", \"".$typeMod."\")'/>
	<input type='button' class='btn' value='Corbeille' onclick='deleteModule(".$mod['id'].")'/>
	</div>";
	$i++;
}
$showModule .= "</div>";

$selectIndicateur = $helper->getElements();

require(JModuleHelper::getLayoutPath('mod_emundus_query_builder','default.php'));