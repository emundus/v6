<?php
/**
* @version		$Id: mod_emundus_checklist.php
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
	//require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'checklist.php');
	//require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'application.php');
	//require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
	
	$db = JFactory::getDBO();
	$document = JFactory::getDocument();
	$document->addStyleSheet( JURI::base()."media/com_emundus/lib/Semantic-UI-CSS-master/semantic.min.css" );
	// overide css
	if (!empty($params->get('header_class', ''))) {
		$document->addStyleSheet( JURI::base()."media/com_emundus/lib/Semantic-UI-CSS-master/components/site.".$params->get('header_class', '').".css" );
	}
	$app = JFactory::getApplication();
	$jinput = $app->input;
	$option = $jinput->get('option');
	$view = $jinput->get('view');

	$menuid = $app->getMenu()->getActive()->id;

	$query='SELECT id, link FROM #__menu WHERE alias like "checklist%" AND menutype like "%'.$user->menutype.'"';
	$db->setQuery( $query );
	$itemid = $db->loadAssoc();


	//$params = JComponentHelper::getParams('com_emundus');
	$show_forms = $params->get('show_forms', 0);
	$show_mandatory_documents = $params->get('show_mandatory_documents', 0);
	$show_optional_documents = $params->get('show_optional_documents', 0);
	$show_duplicate_documents = $params->get('show_duplicate_documents', 0);

	$forms_title = $params->get('forms_title', JText::_('FORMS'));
	$mandatory_documents_title = $params->get('mandatory_documents_title', JText::_('MANDATORY_DOCUMENTS'));
	$optional_documents_title = $params->get('optional_documents_title', JText::_('OPTIONAL_DOCUMENTS'));


	$forms = @EmundusHelperMenu::buildMenuQuery($user->profile);

	$and = ($show_duplicate_documents != -1)?' AND esap.duplicate='.$show_duplicate_documents:'';
	$query = 'SELECT esa.value, esap.id, esa.id as _id, esap.mandatory, esap.duplicate
		FROM #__emundus_setup_attachment_profiles esap
		JOIN #__emundus_setup_attachments esa ON esa.id = esap.attachment_id
		WHERE esap.displayed = 1 '.$and.' AND esap.profile_id ='.$user->profile.' 
		ORDER BY esa.ordering';

	$db->setQuery( $query );
	$documents = $db->loadObjectList();

	if (count($documents) > 0) {
		$mandatory_documents = array();
		$optional_documents = array();
		foreach ($documents as $document) {
			if ($document->mandatory == 1) 
				$mandatory_documents[] = $document;
			else
				$optional_documents[] = $document;
		}
	}

	//$attachments = $application->getAttachmentsProgress($user->id, $user->profile, $user->fnum);
	//$forms = $application->getFormsProgress($user->id, $user->profile, $user->fnum);
	//$current_application = $application->getApplication($user->fnum);
	//$sent = $checklist->getSent();
	//$confirm_form_url = $checklist->getConfirmUrl();
	

	require(JModuleHelper::getLayoutPath('mod_emundus_checklist'));
}