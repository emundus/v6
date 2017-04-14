<?php
/**
* @version		$Id: mod_emundusflow.php
* @package		Joomla
* @copyright	Copyright (C) 2016 emundus.fr. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

$user = JFactory::getUser();

if (isset($user->fnum) && !empty($user->fnum)) {

	require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'menu.php');
	require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'checklist.php');
	require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'application.php');
	require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');

	$document = JFactory::getDocument();
	$document->addStyleSheet( JURI::base()."media/com_emundus/lib/Semantic-UI-CSS-master/semantic.min.css" );
	// overide css
	$header_class = $params->get('header_class', '');
	if (!empty($header_class)) {
		$document->addStyleSheet( JURI::base()."media/com_emundus/lib/Semantic-UI-CSS-master/components/site.".$header_class.".css" );
	}
	$jinput = JFactory::getApplication()->input;
	$option = $jinput->get('option');
	$view = $jinput->get('view');


	$db = JFactory::getDBO();

	// module params
	$show_programme = $params->get('show_programme', 1);
	$show_deadline  = $params->get('show_deadline', 0);

	// eMundus params
	$params_emundus = JComponentHelper::getParams('com_emundus');
	$applicant_can_renew = $params_emundus->get('applicant_can_renew', 0);
	$application_fee  = $params_emundus->get('application_fee', 0);

	$checklist = new EmundusModelChecklist;
	$application = new EmundusModelApplication;
	//$files = new EmundusModelFiles;

	$fnumInfos = EmundusModelFiles::getFnumInfos($user->fnum);
	if ($application_fee == 1) {
		$paid = count($application->getHikashopOrder($fnumInfos))>0?1:0;
		if ($paid == 0 ) {
			$checkout_url = $application->getHikashopCheckoutUrl($user->profile);
		} else{
			$checkout_url = 'index.php';
		}
		
	}

	if (isset($user->fnum) && !empty($user->fnum)) {
		$attachments = $application->getAttachmentsProgress($user->id, $user->profile, $user->fnum);
		$forms = $application->getFormsProgress($user->id, $user->profile, $user->fnum);

		$current_application = $application->getApplication($user->fnum);
		$sent = $checklist->getSent();

		$confirm_form_url 	= $checklist->getConfirmUrl().'&usekey=fnum&rowid='.$user->fnum; 
	}
	

	require(JModuleHelper::getLayoutPath('mod_emundusflow'));
}