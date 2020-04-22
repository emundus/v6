<?php
defined( '_JEXEC' ) or die();
/**
 * @version 3.8: Hesam-isApplicationSent.php 89 2017-10-19 James Dean
 * @package Fabrik
 * @copyright Copyright (C) 2016 eMundus. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Verification de l'autorisation de mettre a jour le formulaire
 */
$mainframe = JFactory::getApplication();

if (!$mainframe->isAdmin()) {
	require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'access.php');

	jimport('joomla.log.log');
	JLog::addLogger(
	    array(
	        // Sets file name
	        'text_file' => 'com_emundus.duplicate.php'
	    ),
	    JLog::ALL,
	    array('com_emundus')
	);

	$user = JFactory::getSession()->get('emundusUser');
	if (empty($user))
		$user = JFactory::getUser();

	$jinput = $mainframe->input;

	$eMConfig = JComponentHelper::getParams('com_emundus');
	$copy_application_form = $eMConfig->get('copy_application_form', 0);
	$id_applicants 			 = $eMConfig->get('id_applicants', '0');
	$applicants 			 = explode(',',$id_applicants);

	$view = $jinput->get('view');
	$fnum = $jinput->get->get('rowid', null);
	$itemid = $jinput->get('Itemid');
	$reload = $jinput->get('r', 0);
	$reload++;

    $is_app_complete     = (@$user->status == 2)? true : false;

	// once access condition is not correct, redirect page
	$reload_url = true;
	// FNUM sent by URL is like user fnum (means an applicant trying to open a file)

	if (isset($user->fnum) && !empty($user->fnum) && $view == 'form') {
        if (!in_array($user->id, $applicants) && $is_app_complete) {
            if ($reload_url) {
                $mainframe->redirect("index.php?option=com_fabrik&view=details&formid=".$jinput->get('formid')."&Itemid=".$itemid."&usekey=fnum&rowid=".$user->fnum."&r=".$reload);
            }
        }
	}
}