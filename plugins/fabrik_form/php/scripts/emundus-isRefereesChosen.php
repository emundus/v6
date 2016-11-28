<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1: emundus-isRefereesChosen.php 89 2008-10-13 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2008 eMundus SAS. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Vérification du choix ou non de professeurs référents ; si oui alors redirection en mode visualisation
 */
 if ($_REQUEST['view'] == 'form') {
	global $mainframe;
	$user = & JFactory::getUser();
	// Affichage vue details
	if($user->usertype == "Registered" && $user->referees_choosen > 0){
		$mainframe->redirect( "index.php?option=com_fabrik&view=details&fabrik=".$_REQUEST['fabrik']."&random=0&rowid=-1&usekey=user");
	}
}
 ?>