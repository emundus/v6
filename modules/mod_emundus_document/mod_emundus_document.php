<?php
defined('_JEXEC') or die('Access Deny');
require_once(dirname(__FILE__).DS.'helper.php');
require_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'stats.php');

JHtml::script('media/com_emundus/js/jquery.cookie.js');
JHtml::script('media/jui/js/bootstrap.min.js');

$helper   = new modEmundusDocumentHelper();

$classDiv = $params->get('header_class');

$docs ="";
$docObligatoire = $helper->getDocObligatoire();
$docOptionnel = $helper->getDocOptionnel();
$docCharge = $helper->getDocCharges();


if($params->get('obligatoire') || $params->get('option')) {
	if(!$params->get('dissocier')	&& (($params->get('obligatoire')	&& trim(strip_tags($docObligatoire)) != "") || ($params->get('option')	&& trim(strip_tags($docOptionnel)) != "")))
		$docs .= "<".$params->get('header_tag')." class=\"g-title\" style=\"margin-top: 20px\" title=\"".JText::_($params->get('desc_joindre'))."\">".JText::_($params->get('titre_joindre'))."</".$params->get('header_tag').">";

	if($params->get('dissocier')	&& $params->get('obligatoire')		&& trim(strip_tags($docObligatoire)) != "")
		$docs .= "<".$params->get('header_tag')." class=\"g-title\" style=\"margin-top: 20px\" title=\"".JText::_($params->get('desc_obligatoire'))."\">".JText::_($params->get('titre_obligatoire'))."</".$params->get('header_tag').">";
	if($params->get('obligatoire')		&& trim(strip_tags($docObligatoire)) != "")
		$docs .= $docObligatoire;

	if($params->get('dissocier')	&& $params->get('option')		&& trim(strip_tags($docOptionnel)) != "")
		$docs .= "<".$params->get('header_tag')." class=\"g-title\" style=\"margin-top: 20px\" title=\"".JText::_($params->get('desc_optionnel'))."\">".JText::_($params->get('titre_optionnel'))."</".$params->get('header_tag').">";
	if($params->get('option')		&& trim(strip_tags($docOptionnel)) != "")
		$docs .= $docOptionnel;
}
if($params->get('charge') && trim(strip_tags($docCharge)) != "") {
	$docs .= "<".$params->get('header_tag')." class=\"g-title\" style=\"margin-top: 20px\" title=\"".JText::_($params->get('desc_charge'))."\">".JText::_($params->get('titre_charge'))."</".$params->get('header_tag').">";
	$docs .= $docCharge;
}

require(JModuleHelper::getLayoutPath('mod_emundus_document','default.php'));
