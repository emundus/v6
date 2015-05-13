<?php

function repeat_emails($params, &$formModel) {
	jimport('joomla.mail.helper');

	$article_id = '70';
	$email_element_name = 'fab_sponsors___sponsor_email';
	$sponsorship_prefix = 'fab_sponsorship___';
	$sponsorship_pk = $sponsorship_prefix . 'id';
	$sponsors_join_id = 58;
	$email_from_addr = "hugh.messenger@gmail.com";
	$email_from_name = "Hugh Messenger";
	$email_subject = "Hi {fab_sponsors___sponsor_name}";

	$user						= JFactory::getUser();
	$config					= JFactory::getConfig();
	$db 						= JFactory::getDbo();
	$w = new FabrikWorker();

	$content = repeat_emails_get_article($article_id);

	$sponsorship_data = array();
	foreach($formModel->_formDataWithTableName as $key => $value) {
		if (strstr($key, $sponsorship_prefix)) {
			$sponsorship_data[$key] = $value;
		}
	}
	$sponsorship_data[$sponsorship_pk] = $formModel->_formData[$sponsorship_pk];
	$sponsorship_data[$sponsorship_pk_raw] = $formModel->_formData[$sponsorship_pk];

	foreach ($formModel->_formData['join'][$sponsors_join_id][$email_element_name] as $key => $email) {
		$sponsor_data = array();
		foreach($formModel->_formData['join'][$sponsors_join_id] as $sponsor_key => $sponsor_val) {
			$sponsor_data[$sponsor_key] = $formModel->_formData['join'][$sponsors_join_id][$sponsor_key][$key];
		}

		$email_data = array_merge($sponsorship_data, $sponsor_data);
		$this_content = $w->parseMessageForPlaceHolder($content, $email_data);
		$this_subject = $w->parseMessageForPlaceHolder($email_subject, $email_data);
		if (JMailHelper::isEmailAddress($email)) {
			$res = JUtility::sendMail($email_from_addr, $email_from_name, $email, $this_subject, $this_content, true);
		}
	}

}

function repeat_emails_get_article($contentTemplate)
{
	require_once(COM_FABRIK_BASE.'components'.DS.'com_content'.DS.'helpers'.DS.'query.php');
	JModel::addIncludePath(COM_FABRIK_BASE.'components'.DS.'com_content'.DS.'models');
	$articleModel = JModel::getInstance('Article', 'ContentModel');

	// $$$ rob when sending from admin we need to alter $mainframe to be the
	//front end application otherwise com_content errors out trying to create
	//the article
	global $mainframe;
	$origMainframe = $mainframe;
	jimport('joomla.application.application');
	$mainframe = JApplication::getInstance('site', array(), 'J');
	$res = $articleModel->getItem($contentTemplate);
	$mainframe = $origMainframe;
	return $res->introtext . " " . $res->fulltext;
}

repeat_emails($params, &$formModel);

?>