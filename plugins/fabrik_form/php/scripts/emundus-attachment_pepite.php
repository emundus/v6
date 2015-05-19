<?php

$user_id = $formModel->getElementData('jos_emundus_pepite_projet1___user', false, '');
$fnum = $formModel->getElementData('jos_emundus_pepite_projet1___fnum', false, '');
$cid = substr($fnum, 14, 7);
$cid = ltrim($cid, '0');

// shéma de la solution id=49
$q6_2 = $formModel->getElementData('jos_emundus_pepite_projet1___q6_2', false, '');
$q7_2 = $formModel->getElementData('jos_emundus_pepite_projet1___q7_2', false, '');
// doc illustrant la solution id=48
$q9_2 = $formModel->getElementData('jos_emundus_pepite_projet1___q9_2', false, '');
$q10_2 = $formModel->getElementData('jos_emundus_pepite_projet1___q10_2', false, '');

$a49 = empty($q6_2)?$q7_2:$q6_2;
$a48 = empty($q9_2)?$q10_2:$q9_2;

$f1 = explode(DS, $a49);
$f2 = explode(DS, $a48);


$db 	= JFactory::getDBO();
if (!empty($a49)) {
	$query 	= "INSERT INTO `#__emundus_uploads` (`user_id`, `fnum`, `attachment_id`, `filename`, `timedate`, `campaign_id`) 
				VALUES (".$user_id[0].", '".$fnum."', 49, '".$f1[5]."', NOW(), ".$cid.")";
	$db->setQuery( $query );
	$db->execute();
}

if (!empty($a48)) {
	$query 	= "INSERT INTO `#__emundus_uploads` (`user_id`, `fnum`, `attachment_id`, `filename`, `timedate`, `campaign_id`) 
				VALUES (".$user_id[0].", '".$fnum."', 48, '".$f2[5]."', NOW(), ".$cid.")";
	$db->setQuery( $query );
	$db->execute();
}

?>