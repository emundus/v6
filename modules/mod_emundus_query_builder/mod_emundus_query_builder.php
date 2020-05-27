<?php
defined('_JEXEC') or die('Access Deny');
require_once(dirname(__FILE__).DS.'helper.php');
require_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'stats.php');

JHtml::script('media/com_emundus/js/jquery.cookie.js');
JHtml::script('media/jui/js/bootstrap.min.js');

$document   = JFactory::getDocument();
$document->addStyleSheet("modules/mod_emundus_query_builder/style/mod_emundus_query_builder.css" );

$helper = new modEmundusQueryBuilderHelper;

$tabModule = $helper->getModuleStat();

$showModule = "";
foreach($tabModule as $mod) {
	$typeMod = $helper->getTypeStatModule($mod['id']);
	$showModule .= "<div class='input'>
	<input type='checkbox' id='".$mod['title']."' value='".$mod['id']."' onchange='changePublished(".$mod['id'].")' ".(($mod['published'] == 1)?"checked":"").">
	<label>".$mod['title']."</label>
	<input type='button' class='btn' value='Editer' onclick='modifyModule(".$mod['id'].", \"".$mod['title']."\", \"".$typeMod."\")'/>
	<input type='button' class='btn' value='Corbeille' onclick='deleteModule(".$mod['id'].")'/>
	</div>";
}

$selectIndicateur = $helper->getElements();

if(isset($_POST['idChangePublishedModule'])) {
	$helper->changePublishedModule($_POST['idChangePublishedModule']);
}

if(isset($_POST['idDeleteModule'])) {
	$helper->deleteModule($_POST['idDeleteModule']);
}

if(isset($_POST['idModifyModule'])) {
	$helper->changeModule($_POST['titleModule'], $_POST['typeModule'], $_POST['idModifyModule']);
}

if(isset($_POST['createModule'])) {
	var_dump(($helper->createModule($_POST['titleModule'], $_POST['typeModule'], $_POST['indicateurModule'], $_POST['axeXModule'], $_POST['axeYModule']))).die();
}

require(JModuleHelper::getLayoutPath('mod_emundus_query_builder','default.php'));