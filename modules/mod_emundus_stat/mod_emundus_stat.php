<?php
defined('_JEXEC') or die('Access Deny');
require_once(dirname(__FILE__).DS.'helper.php');
require_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'stats.php');

JHtml::script('media/com_emundus/js/jquery.cookie.js');
JHtml::script('media/jui/js/bootstrap.min.js');

$document   = JFactory::getDocument();
$document->addStyleSheet("modules/mod_emundus_stat/style/mod_emundus_stat.css" );

$helper = new modEmundusStatHelper;

/** 
  * Setting parameters
  * According to the type of the graph
  */
$listId		= $params->get('list_id');
$view		= $params->get('view');
$titleGraph	= $module->title;
$typeGraph	= $params->get('type_graph');
$xAxeNameDB	= $params->get('x_name_db');
$xAxeName	= $params->get('x_name');
$yAxeName	= $params->get('y_name_0');

if(substr_count($typeGraph, "combi") != 0  || (substr_count($typeGraph, "column") != 0 && substr_count($typeGraph, "line") != 0)) {
	$nbValue	= intval($params->get('nb_value'));
	for($cpt = 0 ; $cpt < $nbValue ; $cpt++) {
		$yAxeNameDB[$cpt]	= $params->get('y_name_db_'.$cpt);
		$serieName[$cpt]	= $params->get('serie_name_'.$cpt);
		$typeTrace[$cpt]	= $params->get('type_trace_'.$cpt);
	}
} elseif(substr_count($typeGraph, "ms") != 0 || substr_count($typeGraph, "stacked") != 0 || substr_count($typeGraph, "marimekko") != 0 || substr_count($typeGraph, "zoom") != 0 || substr_count($typeGraph, "over") != 0 || substr_count($typeGraph, "scrollcombi") != 0) {
	$nbValue	= intval($params->get('nb_value'));
	for($cpt = 0 ; $cpt < $nbValue ; $cpt++) {
		$yAxeNameDB[$cpt]	= $params->get('y_name_db_'.$cpt);
		$serieName[$cpt]	= $params->get('serie_name_'.$cpt);
	}
} else {
	$yAxeNameDB[0]	= $params->get('y_name_db_0');
}

if(substr_count($typeGraph, "dy") != 0) {
	$yAxeName1	= $params->get('y_name_1');
	for($cpt = 0 ; $cpt < $nbValue ; $cpt++) {
		$yChoice[$cpt]	= $params->get('y_choice_'.$cpt);
	}
}
if(substr_count($typeGraph, "msstacked") != 0) {
	$nbColumn	= intval($params->get('nb_column'));
	for($cpt = 0 ; $cpt < count($serieName) ; $cpt++) {
		$columnChoice[$cpt]	= intval($params->get('column_choice_'.$cpt))-1;
	}
}
/************************/

/** 
  * Create a table HTML of values
  * According to the type of the graph
  */
$html = "<table>";
if($typeGraph === "timeseries")
	$item = $helper->getViewOrder($view, $yAxeNameDB, $xAxeNameDB, $xAxeNameDB, $params);
else
	$item = $helper->getView($view, $yAxeNameDB, $xAxeNameDB, $params);
if($item != null)
	for($cpt = 0 ; $cpt < count($item); $cpt++)
		if(substr_count($typeGraph, "ms") != 0 || substr_count($typeGraph, "stacked") != 0 || substr_count($typeGraph, "marimekko") != 0 || substr_count($typeGraph, "zoom") != 0 || substr_count($typeGraph, "over") != 0 || substr_count($typeGraph, "scrollcombi") != 0) {
			$html .= "<tr>";
			for($cpt0 = 0 ; $cpt0 < $nbValue ; $cpt0++)
				$html .= "<td>".$item[$cpt]['number'.$cpt0]."</td>";
			$html .= "<td>".$item[$cpt][''.$xAxeNameDB]."</td></tr>";
		} else {
			$html .= "<tr><td>".$item[$cpt]['number0']."</td><td>".$item[$cpt][''.$xAxeNameDB]."</td></tr>";
		}
$html .= "</table>";
/************************/

/** 
  * Create an array PHP of values
  * According to the type of the graph
  */
$DOM = new DOMDocument();
$DOM->loadXML($html);

$Detail = $DOM->getElementsByTagName('td');

if(substr_count($typeGraph, "msstacked") != 0) {
	for($cpt0 = 0 ; $cpt0 < $nbColumn ; $cpt0++)
		$cptC[$cpt0] = 0;
	$cptL = 0;
	for($cpt = 0 ; $cpt < count($serieName) ; $cpt++) {
		if(substr_count($typeGraph, "column") != 0 && substr_count($typeGraph, "line") != 0 && $typeTrace[$cpt] === "line") {
			$aDataTableDetailHTML["lineset"][0]["seriesname"] = $serieName[$cpt];
			$posSerie[$cpt] = -1;
			$posSerieLine[$cpt] = $cptL;
			$cptL++;
		} else {
			$aDataTableDetailHTML["dataset"][$columnChoice[$cpt]]["dataset"][$cptC[$columnChoice[$cpt]]]["seriesname"] = $serieName[$cpt];
			$posSerie[$cpt] = $cptC[$columnChoice[$cpt]];
			$cptC[$columnChoice[$cpt]]++;
		}
	}
} elseif(substr_count($typeGraph, "ms") != 0 || substr_count($typeGraph, "stacked") != 0 || substr_count($typeGraph, "marimekko") != 0 || substr_count($typeGraph, "zoom") != 0 || substr_count($typeGraph, "over") != 0 || substr_count($typeGraph, "scrollcombi") != 0) {
	for($cpt = 0 ; $cpt < count($serieName) ; $cpt++) {
		$aDataTableDetailHTML["dataset"][$cpt]["seriesname"] = $serieName[$cpt];
		if(substr_count($typeGraph, "combi") != 0 || (substr_count($typeGraph, "column") != 0 && substr_count($typeGraph, "line") != 0)) {
			$aDataTableDetailHTML["dataset"][$cpt]["renderAs"] = $typeTrace[$cpt];
		}
		if(substr_count($typeGraph, "dy") != 0) {
			$aDataTableDetailHTML["dataset"][$cpt]["parentYAxis"] = $yChoice[$cpt];
		}
	}
}

// Data
if($typeGraph === "timeseries")
	$i = 0;
else
	$i = "value";
$j = 0;
$k = 0;
$dateBefore = null;
for($cpt = 0; $cpt < count($Detail); $cpt++)
{
	// For a timeseries, we set to 0 before and after for the date ranges
	if($typeGraph === "timeseries")
	{
		if($i === 0 && $dateBefore != null && date('Y-m-d', strtotime('+1 day', strtotime($dateBefore))) != date('Y-m-d', strtotime(trim($Detail[$cpt+1]->textContent)))) {
			$aDataTableDetailHTML[$j][0] = 0;
			$aDataTableDetailHTML[$j][1] = date('Y-m-d', strtotime('+1 day', strtotime($dateBefore)))." 12:00:00";
			$j++;
			$aDataTableDetailHTML[$j][0] = 0;
			$aDataTableDetailHTML[$j][1] = date('Y-m-d', strtotime('-1 day', strtotime(trim($Detail[$cpt+1]->textContent))))." 12:00:00";
			$j++;
		}
		$dateBefore = trim($Detail[$cpt]->textContent);
		
	}
	$sNodeDetail = $Detail[$cpt];
	
	
	// We write the data in the array PHP according to the type of graph
	if($typeGraph === "timeseries") {
		if($i === 1)
			$aDataTableDetailHTML[$j][$i] = date('Y-m-d', strtotime(trim($sNodeDetail->textContent)))." 12:00:00";
		else
			$aDataTableDetailHTML[$j][$i] = intval(trim($sNodeDetail->textContent));
		$j = $i === 1 ? $j + 1 : $j;
		$i = $i === 1 ? 0 : 1;
	} elseif(substr_count($typeGraph, "msstacked") != 0) {
		if($i === "label")
			$aDataTableDetailHTML["categories"][0]["category"][$j][$i] = JText::_(trim($sNodeDetail->textContent));
		else {
			if($posSerie[$k] != -1)
				$aDataTableDetailHTML["dataset"][$columnChoice[$k]]["dataset"][$posSerie[$k]]["data"][][$i] = intval(trim($sNodeDetail->textContent));
			else
				$aDataTableDetailHTML["lineset"][$posSerieLine[$k]]["data"][][$i] = intval(trim($sNodeDetail->textContent));
		}
		$j = $k === $nbValue ? $j + 1 : $j;
		$i = $k != $nbValue-1 ? "value" : "label";
		$k = $k === $nbValue ? 0 : $k + 1;
	} elseif(substr_count($typeGraph, "scroll") != 0 && substr_count($typeGraph, "scrollcombi") === 0 && substr_count($typeGraph, "scrollstacked") === 0) {
		$aDataTableDetailHTML[(($i === "label")?"categories":"dataset")][0][(($i === "label")?"category":"data")][$j][$i] = JText::_(trim($sNodeDetail->textContent));
		$j = $i === "label" ? $j + 1 : $j;
		$i = $i === "label" ? "value" : "label";
	} elseif(substr_count($typeGraph, "ms") != 0 || substr_count($typeGraph, "stacked") != 0 || substr_count($typeGraph, "marimekko") != 0 || substr_count($typeGraph, "zoom") != 0 || substr_count($typeGraph, "over") != 0 || substr_count($typeGraph, "scrollcombi") != 0) {
		if($i === "label")
			$aDataTableDetailHTML["categories"][0]["category"][$j][$i] = JText::_(trim($sNodeDetail->textContent));
		else
			$aDataTableDetailHTML["dataset"][$k]["data"][$j][$i] = intval(trim($sNodeDetail->textContent));
		$j = $k === $nbValue ? $j + 1 : $j;
		$i = $k != $nbValue-1 ? "value" : "label";
		$k = $k === $nbValue ? 0 : $k + 1;
	} else {
		if($i === "label")
			$aDataTableDetailHTML[$j][$i] = JText::_(trim($sNodeDetail->textContent));
		else
			$aDataTableDetailHTML[$j][$i] = intval(trim($sNodeDetail->textContent));
		$j = $i === "label" ? $j + 1 : $j;
		$i = $i === "label" ? "value" : "label";
	}
	
}
/************************/

/** 
  * Create a JSON of values
  * According to the type of the graph
  */
$jsonGraph = json_encode($aDataTableDetailHTML);
/************************/

$urlFiltre = $helper->getUrlFiltre($view, $params);

require(JModuleHelper::getLayoutPath('mod_emundus_stat','default.php'));