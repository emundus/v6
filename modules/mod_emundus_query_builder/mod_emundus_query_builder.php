<?php
defined('_JEXEC') or die('Access Deny');
require_once(dirname(__FILE__) . DS . 'helper.php');
require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'stats.php');

JHtml::script('media/com_emundus/js/jquery.cookie.js');
JHtml::script('media/jui/js/bootstrap.min.js');

$document = JFactory::getDocument();
$document->addStyleSheet("modules/mod_emundus_query_builder/style/mod_emundus_query_builder.css");
$eMConfig             = JComponentHelper::getParams('com_emundus');
$gotenberg_activation = $eMConfig->get('gotenberg_activation', 1);

$helper = new modEmundusQueryBuilderHelper;

$tabModule       = $helper->getModuleStat();
$tabExportModule = $helper->getExportModuleStat();

// Create the table of stats modules which will allow you to change the order, modify, delete and display or not the stats modules
$showModule = "<div class='showModule' id='sortable'>";
$i          = 0;
foreach ($tabModule as $mod) {
	$typeMod = $helper->getTypeStatModule($mod['id']);
	$view    = json_decode($mod['params'], true)['view'];

	$showModule .= "<div class='input order_" . $mod['ordering'] . "' id='id_" . $mod['id'] . "'><table class='editModule'><tr><td class='order'>";
	$showModule .= "<div class='move'>&#x283F;</div>";
	$showModule .= "</td><td class='radioModule'><input type='checkbox' id='" . JText::_($mod['title']) . "' value='" . $mod['id'] . "' onchange='changePublished(" . $mod['id'] . ")' " . (($mod['published'] == 1) ? "checked" : "") . ">
	<a href='#chart-container-" . $view . "'><label>" . JText::_($mod['title']) . "</label></a></td><td class='edit'>";
	if (substr_count($view, "stats") != 1) {
		$showModule .= "<input type='button' class='delete' value='&#128465; " . JText::_('MOD_EMUNDUS_QUERY_RECYCLE_BIN') . "' onclick='deleteModule(" . $mod['id'] . ")'/></td>";
	}
	$showModule .= "</td><td class='edit'><input type='button' class='modif' value='&#128395; " . JText::_('MOD_EMUNDUS_QUERY_BUILDER_EDIT') . "' onclick='modifyModule(" . $mod['id'] . ", \"" . JText::_($mod['title']) . "\", \"" . $typeMod . "\")'/></td>";
	$showModule .= "</tr></table></div>";
	$i++;
}
$showModule .= "</div>";

// Create the table of statistics modules which will allow you to export the selected statistics modules
$exportModule = "<div class='showModule'>";
$i            = 0;
foreach ($tabExportModule as $mod) {
	$typeMod = $helper->getTypeStatModule($mod['id']);
	$view    = json_decode($mod['params'], true)['view'];

	$exportModule .= "<table class='exportModule'><tr><td class='radioModule'><label><input type='checkbox' class='radioButton' id='" . JText::_($mod['title']) . "' value='" . $mod['id'] . "' onchange='exportNum(\"" . $view . "\")'>" . JText::_($mod['title']) . "</label></td></td></tr></table>";
	$i++;
}
$exportModule .= "</div>";

$selectIndicateur = $helper->getElements();

require(JModuleHelper::getLayoutPath('mod_emundus_query_builder', 'default.php'));
