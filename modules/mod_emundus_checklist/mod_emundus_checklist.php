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

$document = JFactory::getDocument();
$document->addStyleSheet('modules/mod_emundus_checklist/style/emundus_checklist.css');

$user = JFactory::getSession()->get('emundusUser');

if (isset($user->fnum) && !empty($user->fnum)) {
    $show_forms = $params->get('show_forms', 0);
    $show_mandatory_documents = $params->get('show_mandatory_documents', 0);
    $show_optional_documents = $params->get('show_optional_documents', 0);
    $show_duplicate_documents = $params->get('show_duplicate_documents', 0);
    $forms_title = $params->get('forms_title', JText::_('FORMS'));
    $mandatory_documents_title = $params->get('mandatory_documents_title', JText::_('MANDATORY_DOCUMENTS'));
    $optional_documents_title = $params->get('optional_documents_title', JText::_('OPTIONAL_DOCUMENTS'));


    $eMConfig = JComponentHelper::getParams('com_emundus');
    $applicant_files_path = $eMConfig->get('applicant_files_path', 'images/emundus/files/');

	require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'menu.php');
	$db = JFactory::getDBO();

	$app = JFactory::getApplication();
	$jinput = $app->input;
	$option = $jinput->get('option');
	$view = $jinput->get('view');

	$menuid = $app->getMenu()->getActive()->id;

	$query='SELECT id, link FROM #__menu WHERE alias like "checklist%" AND menutype like "%'.$user->menutype.'"';
	$db->setQuery( $query );
	$itemid = $db->loadAssoc();


	$forms = @EmundusHelperMenu::buildMenuQuery($user->profile);

	$and = ($show_duplicate_documents != -1)?' AND esap.duplicate='.$show_duplicate_documents:'';
	$query = 'SELECT esa.value, esap.id, esa.id as _id, esap.mandatory, esap.duplicate
		FROM #__emundus_setup_attachment_profiles esap
		JOIN #__emundus_setup_attachments esa ON esa.id = esap.attachment_id
		WHERE esap.displayed = 1 '.$and.' AND esap.profile_id ='.$user->profile.'
		ORDER BY esa.ordering';
	$db->setQuery( $query );
	$documents = $db->loadObjectList();

	$mandatory_documents = array();
	$optional_documents = array();

	if (count($documents) > 0) {
		foreach ($documents as $document) {
			if ($document->mandatory == 1)
				$mandatory_documents[] = $document;
			else
				$optional_documents[] = $document;
		}
	}

    $query = $db->getQuery(true);

    $query->select('eu.*,esa.value as attachment_name')
        ->from($db->quoteName('#__emundus_uploads','eu'))
        ->leftJoin($db->quoteName('#__emundus_setup_attachment_profiles','esap').' ON '.$db->quoteName('eu.attachment_id').' = '.$db->quoteName('esap.attachment_id'))
        ->leftJoin($db->quoteName('#__emundus_setup_attachments','esa').' ON '.$db->quoteName('esap.attachment_id').' = '.$db->quoteName('esa.id'))
        ->where($db->quoteName('esap.mandatory') . ' = 1')
        ->andWhere($db->quoteName('esap.displayed') . ' = 1')
        ->andWhere($db->quoteName('esap.profile_id') . ' = ' . $db->quote($user->profile))
        ->andWhere($db->quoteName('eu.fnum') . ' like ' . $db->quote($user->fnum))
        ->andWhere($db->quoteName('eu.user_id') . ' = ' . $db->quote($user->id))
        ->group('esa.id');
    $db->setQuery($query);
    $uploads = $db->loadObjectList();

    foreach ($uploads as $upload){
        $file = $applicant_files_path . $user->id . '/' . $upload->filename;
        $bytes = filesize($file);

        if($bytes) {
            $decimals = 0;

            $factor = floor((strlen($bytes) - 1) / 3);
            if ($factor > 0) $sz = 'KMGT';
            $upload->filesize = sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor - 1] . 'o';
        } else {
            $upload->filesize = 0;
        }
    }

	require(JModuleHelper::getLayoutPath('mod_emundus_checklist'));
}
