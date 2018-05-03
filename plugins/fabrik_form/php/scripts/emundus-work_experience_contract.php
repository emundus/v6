<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1: emundus_work_experience_contract.php 89 2008-10-13 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2008 eMundus SAS. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Ajout d'un contrat dans la liste des documents du candidat
 */

$can_be_viewed = 1;
$can_be_deleted = 0;
$user = & JFactory::getUser();
$attachment_id = 32;

$db =& JFactory::getDBO();
$query = 'SELECT contract FROM #__emundus_cv WHERE user='.$user->id;
$db->setQuery($query);
$upload=$db->loadResult();
$contract_lst = explode('//..*..//', $upload);
die($query);

$query = 'SELECT attachment.lbl 
			FROM #__emundus_setup_attachments AS attachment
			WHERE attachment.id ='.$attachment_id;
$db->setQuery( $query );
$attachement_params=$db->loadObject();

$student = strtolower(preg_replace(array('([\40])','([^a-zA-Z0-9-])','(-{2,})'),array('_','','_'),preg_replace('/&([A-Za-z]{1,2})(grave|acute|circ|cedil|uml|lig);/','$1',htmlentities($user->name,ENT_NOQUOTES,'UTF-8'))));

foreach($contract_lst as $cl) {
	$filename = $student.'_'.$attachement_params->lbl.rand().'.'.end(explode('.', $cl));
	$query="insert into #__emundus_uploads (user_id,attachment_id,filename,description,can_be_deleted,can_be_viewed) values(".$user->id.",".$attachment_id.",'".$filename."','',".$can_be_deleted.",".$can_be_viewed.")";
	$db->setQuery($query) or die($query);
}

?>