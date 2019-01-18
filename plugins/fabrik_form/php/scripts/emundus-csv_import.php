<?php
defined( '_JEXEC' ) or die();
/**
 * @package eMundus
 * @copyright Copyright (C) 2019 eMundus SAS. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Attach logger.
jimport('joomla.log.log');
JLog::addLogger(array('text_file' => 'com_emundus.csvimport.php'), JLog::ALL, array('com_emundus'));

$app = JFactory::getApplication();

$csv = $formModel->data['jos_emundus_setup_csv_import___csv_file_raw'];

// Check if the file is a file on the server and in the right format.
if (!is_file(JPATH_ROOT.$csv)) {
	JLog::add('ERROR: Tried to upload something that was not a file.', JLog::ERROR, 'com_emundus');
	$app->enqueueMessage('ERROR: Tried to upload something that was not a file.', 'error');
	return false;
}

if (pathinfo($csv, PATHINFO_EXTENSION) !== 'csv') {
	JLog::add('ERROR: Tried to upload something that was not a csv file.', JLog::ERROR, 'com_emundus');
	$app->enqueueMessage('ERROR: Tried to upload something that was not a csv file.', 'error');
	return false;
}

// auto_detect_line_endings allows PHP to detect MACOS line endings or else things get ugly...
ini_set('auto_detect_line_endings', TRUE);


$handle = fopen(JPATH_ROOT.$csv, 'r');
if (!$handle) {
	JLog::add('ERROR: Could not open import file.', JLog::ERROR, 'com_emundus');
	$app->enqueueMessage('ERROR: Could not open import file.', 'error');
	return false;
}

// Prepare data structure for parsing.
$database_elements = [];
$bad_columns = [];

$db = JFactory::getDbo();
$query = $db->getQuery(true);
$checked_tables = [];

$profile = $formModel->data['jos_emundus_setup_csv_import___profile_raw'][0];

$row = 0;
if (($data = fgetcsv($handle, 0, ';')) !== false) {

	foreach ($data as $column_number => $column) {

		// If the file name is not in the following format : table___element; mark column as bad.
		$column = explode("___", preg_replace('/[^\PC\s]/u', '', $column));
		if (count($column) !== 2) {

			// Special columns such as the campaign ID can be inserted.
			if ($column[0] = 'campaign') {
				$campaign_column = $column_number;
			}

			if ($column[0] = 'status') {
				$status_column = $column_number;
			}

			$bad_columns[] = $column_number;
			continue;
		}
		
		$table = $column[0];
		$element = $column[1];
		
		// Test the existence of the table and the fnum column.
		if ($table != 'jos_emundus_users') {

			if (!in_array($table, $checked_tables)) {

				$db->setQuery('SHOW COLUMNS FROM '.$db->quoteName($table).' LIKE '.$db->quote('fnum'));
				try {
					if (empty($db->loadResult())) {
						$bad_columns[] = $column_number;
						continue;
					}
				} catch (Exception $e) {
					$bad_columns[] = $column_number;
					continue;
				}

				$checked_tables[] = $table;
			}

			$db->setQuery('SHOW COLUMNS FROM '.$db->quoteName($table).' LIKE '.$db->quote($element));
			try {
				if (empty($db->loadResult())) {
					$bad_columns[] = $column_number;
					continue;
				}
			} catch (Exception $e) {
				$bad_columns[] = $column_number;
				continue;
			}

		}

		$database_elements[$column_number]->table = $table;
		$database_elements[$column_number]->column = $element;
	}

} else {
	JLog::add('ERROR: Empty file was uploaded.', JLog::ERROR, 'com_emundus');
	$app->enqueueMessage('ERROR: Empty file was uploaded.', 'error');
	return false;
}

$parsed_data = [];

while (($data = fgetcsv($handle, 0, ';')) !== false) {
	
	foreach ($data as $column_number => $column) {

		if ($column_number == $campaign_column) {
			$campaign_row[$row] = preg_replace('/[^\PC\s]/u', '', $column);

			// If we have no profile, we must get the associated one using the campaign.
			if (empty($profile)) {

				$query->clear()
					->select($db->quoteName('profile_id'))
					->from($db->quoteName('#__emundus_setup_campaigns'))
					->where($db->quoteName('id').' = '.preg_replace('/[^\PC\s]/u', '', $column));
				$db->setQuery($query);

				try {
					$profile_row[$row] = $db->loadResult();
				} catch (Exception $e) {
					JLog::add('ERROR: Could not get profile using campaign in row.', JLog::ERROR, 'com_emundus');
					continue;
				}

			}

			continue;
		} elseif ($column_number == $status_column) {
			$status_row[$row] = preg_replace('/[^\PC\s]/u', '', $column);
			continue;
		}

		if (in_array($column_number, $bad_columns)) {
			continue;
		}
		
		// Build the complex data structure.
		$parsed_data[$row][$database_elements[$column_number]->table][$database_elements[$column_number]->column] = preg_replace('/[^\PC\s]/u', '', $column);
		
	}
	
	$row++;
}
fclose($handle);

// If we never incremented row then there are not files being imported.
if ($row === 0) {
	JLog::add('ERROR: No data sent in file.', JLog::ERROR, 'com_emundus');
	$app->enqueueMessage('ERROR: No data sent in file.', 'error');
	return false;
}

// If have no parsed data, something went wrong.
if (empty($parsed_data)) {
	JLog::add('ERROR: Something went wrong.', JLog::ERROR, 'com_emundus');
	$app->enqueueMessage('ERROR: Something went wrong.', 'error');
	return false;
}

$campaign = $formModel->data['jos_emundus_setup_csv_import___campaign_raw'][0];
$status = 0;
$email_from_sys = $app->getCfg('mailfrom');

// if we have the LDAP param active, we can look for one here.
$ldap_plugin = JPluginHelper::getPlugin('authentication','ldap');

// Defining the search filters as a param allows us make it modular.
$emundus_params = JComponentHelper::getParams('ldapFiltersImport');
$ldap_filters   = $emundus_params->get('ldapFiltersImport');
$ldap_elements  = explode(',', $emundus_params->get('ldapElements'));

$ldap_params = new JRegistry($ldap_plugin->params);
$ldap = new JLDAP($ldap_params);

// Handle parsed data insertion
foreach ($parsed_data as $row_id => $insert_row) {

	$fnum = $insert_row['jos_emundus_campaign_candidature']['fnum'];

	// We can pass the campaign ID in the XLS if we need.
	if (!empty($campaign_row[$row_id]) && is_numeric($campaign_row[$row_id])) {
		$campaign = $campaign_row[$row_id];
	}

	if (!empty($status_row[$row_id]) && is_numeric($status_row[$row_id])) {
		$status = $status_row[$row_id];
	}

	if (!empty($profile_row[$row_id]) && is_numeric($status_row[$row_id])) {
		$profile = $profile_row[$row_id];
	} elseif (empty($profile)) {

		$query->clear()
			->select($db->quoteName('profile_id'))
			->from($db->quoteName('#__emundus_setup_campaigns'))
			->where($db->quoteName('id').' = '.$campaign);
		$db->setQuery($query);

		try {
			$profile_row[$row] = $db->loadResult();
		} catch (Exception $e) {
			JLog::add('ERROR: Could not get profile using campaign in row.', JLog::ERROR, 'com_emundus');
			continue;
		}
	}

	// We need a user
	if (!empty($insert_row['jos_emundus_users']['user_id'])) {

		$user = (int)$insert_row['jos_emundus_users']['user_id'];
		if (JFactory::getUser($user)->guest) {
			unset($user);
		}

	} elseif (!empty($insert_row['jos_emundus_users']['username']) || !empty($insert_row['jos_emundus_users']['email'])) {

		// If we have an email present then we need to check if a user already exists.
		$query->clear()
			->select($db->quoteName('id'))
			->from($db->quoteName('#__users'))
			->where($db->quoteName('username').' LIKE '.$db->quote($insert_row['jos_emundus_users']['username']).' OR '.$db->quoteName('email').' LIKE '.$db->quote($insert_row['jos_emundus_users']['email']));
		$db->setQuery($query);

		try {
			$user = $db->loadResult();
		} catch (Exception $e) {
			continue;
		}

	} else {

		// Clear any potential user ID from previous iteration.
		unset($user);
		if (!empty($fnum)) {
			$user = (int)substr($fnum, -7);
		}

	}

	if (empty($user)) {

		if (!JFactory::getUser()->authorise('core.admin') && !EmundusHelperAccess::asAccessAction(12, 'c')) {
			JLog::add('ERROR: You do not have the rights to create a user.', JLog::ERROR, 'com_emundus');
			$app->enqueueMessage('ERROR: '.JFactory::getUser()->name.' does not have the rights to create a user.', 'error');
			return false;
		}

		$username = $insert_row['jos_emundus_users']['username'];
		if (empty($username)) {
			$username = $insert_row['jos_emundus_users']['email'];
		}
		$email = $insert_row['jos_emundus_users']['email'];
		$firstname = $insert_row['jos_emundus_personal_detail']['first_name'];
		$lastname = $insert_row['jos_emundus_personal_detail']['last_name'];
		$ldap_user = false;

		// No user could be found either by id, username, email, or fnum: so we need to make a new one.
		if ($ldap_plugin && !empty($ldap_filters) && $ldap->connect() && $ldap->bind()) {

			// Filters come in a list separated by commas, but are fed into the LDAP object as an array.
			// The area to put the search term is defined as [SEARCH] in the param.
			if (!empty($username)) {
				$user = $ldap->search(explode(',', str_replace('[SEARCH]', $username, $ldap_filters)))[0];
			}

			// If the search found nothing by username, retry with email.
			if (empty($user) && !empty($email)) {
				$user = $ldap->search(explode(',', str_replace('[SEARCH]', $email, $ldap_filters)))[0];
			}

			// If the LDAP actually found something: make the user.
			if (!empty($user)) {

				$username = $user[$ldap_elements[0]];
				$email = strtok($user[$ldap_elements[1]], ',');
				$firstname = $user[$ldap_elements[2]];
				$lastname = $user[$ldap_elements[3]];
				$ldap_user = true;

			}

			// Check if any data is missing.
			if (empty($username) || empty($email) || empty($firstname) || empty($lastname)) {
				JLog::add('ERROR: Missing some user details, cannot create user.', JLog::ERROR, 'com_emundus');
				continue;
			}
		}

		$user = clone(JFactory::getUser(0));
		if (preg_match('/^[0-9a-zA-Z\_\@\-\.]+$/', $username) !== 1) {
			JLog::add('ERROR: Username format not OK.', JLog::ERROR, 'com_emundus');
			continue;
		}
		if (preg_match('/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-z\-0-9]+\.)+[a-z]{2,}))$/', $email) !== 1) {
			JLog::add('ERROR: Email format not OK.', JLog::ERROR, 'com_emundus');
			continue;
		}

		$user->name = strtolower($firstname).' '.strtoupper($lastname);
		$user->username = $username;
		$user->email = $email;

		// If our user comes from the LDAP system, he has no password.
		// If he doesn't, he needs one generated.
		if (!$ldap_user) {
			$password = JUserHelper::genRandomPassword();
			$user->password = md5($password);
		}

		$user->registerDate = date('Y-m-d H:i:s');
		$user->lastvisitDate = date('Y-m-d H:i:s');
		$user->block = 0;
		$other_param['firstname'] = $firstname;
		$other_param['lastname'] = $lastname;
		$other_param['profile'] = $profile;
		$other_param['em_campaigns'] = $campaign;

		require_once (JPATH_ROOT.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'users.php');
		require_once (JPATH_ROOT.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'emails.php');

		$m_users = new EmundusModelUsers();
		$acl_aro_groups = $m_users->getDefaultGroup($profile);
		$user->groups = $acl_aro_groups;
		$usertype = $m_users->found_usertype($acl_aro_groups[0]);
		$user->usertype = $usertype;
		$uid = $m_users->adduser($user, $other_param);

		if (is_array($uid)) {
			JLog::add('ERROR: Inserting the user ('.$user->email.') failed.', JLog::ERROR, 'com_emundus');
			continue;
		}

		if (!defined('EMUNDUS_PATH_ABS')) {
			define('EMUNDUS_PATH_ABS', JPATH_ROOT.DS.$emundus_params->get('applicant_files_path', 'images/emundus/files/'));
		}

		if (!mkdir(EMUNDUS_PATH_ABS.$uid, 0755) || !copy(EMUNDUS_PATH_ABS.'index.html', EMUNDUS_PATH_ABS.$uid.DS.'index.html')) {
			JLog::add('ERROR: Creating the user file on the server ('.EMUNDUS_PATH_ABS.$uid.') failed.', JLog::ERROR, 'com_emundus');
			continue;
		}

		// Send email indicating account creation.
		$m_emails = new EmundusModelEmails();

		// If we are creating an ldap account, we need to send a different email.
		if ($ldap_user) {

			$email = $m_emails->getEmail('new_ldap_account');
			$tags = $m_emails->setTags($user->id, array(), null, null);

			$mailer = JFactory::getMailer();
			$from = preg_replace($tags['patterns'], $tags['replacements'], $email->emailfrom);
			$fromname = preg_replace($tags['patterns'], $tags['replacements'], $email->name);
			$subject = preg_replace($tags['patterns'], $tags['replacements'], $email->subject);
			$body = preg_replace($tags['patterns'], $tags['replacements'], $email->message);
			$body = $m_emails->setTagsFabrik($body);

			// If the email sender has the same domain as the system sender address.
			if (!empty($email->emailfrom) && substr(strrchr($email->emailfrom, "@"), 1) === substr(strrchr($email_from_sys, "@"), 1)) {
				$mail_from_address = $email->emailfrom;
			} else {
				$mail_from_address = $email_from_sys;
			}

			$sender = [
				$mail_from_address,
				$fromname
			];

			$mailer->setSender($sender);
			$mailer->addReplyTo($email->emailfrom, $email->name);
			$mailer->addRecipient($user->email);
			$mailer->setSubject($email->subject);
			$mailer->isHTML(true);
			$mailer->Encoding = 'base64';
			$mailer->setBody($body);

			try {
				$send = $mailer->Send();

				if ($send === false) {
					JLog::add('No email configuration!', JLog::ERROR, 'com_emundus');
				} else {
					if (JComponentHelper::getParams('com_emundus')->get('logUserEmail', '0') == '1') {
						$message = array(
							'user_id_to' => $uid,
							'subject' => $email->subject,
							'message' => $body
						);
						$m_emails->logEmail($message);
					}
				}

			} catch (Exception $e) {
				JLog::add('ERROR: Could not send email to user : '.$user->id, JLog::ERROR, 'com_emundus');
			}
		}

		$user = $user->id;
	}

	// If the user has no fnum, get the one made by the user creation code.
	if (empty($fnum)) {

		$query->clear()
			->select($db->quoteName('fnum'))
			->from($db->quoteName('#__emundus_campaign_candidature'))
			->where($db->quoteName('applicant_id').' = '.$user.' AND '.$db->quoteName('campaign_id').' = '.$campaign);
		$db->setQuery($query);

		try {
			$fnum = $db->loadResult();
		} catch (Exception $e) {
			JLog::add('ERROR: Could not get fnum for user : '.$user, JLog::ERROR, 'com_emundus');
		}
	}

	// If no fnum is found, generate it.
	if (empty($fnum)) {

		$fnum = date('YmdHis').str_pad($campaign, 7, '0', STR_PAD_LEFT).str_pad($user, 7, '0', STR_PAD_LEFT);
		$query->clear()
			->insert($db->quoteName('#__emundus_campaign_candidature'))
			->columns($db->quoteName(['applicant_id', 'user_id', 'campaign_id', 'fnum', 'status']))
			->values($user.', '.JFactory::getUser()->id.', '.$campaign.', '.$db->quote($fnum).', '.$status);
		$db->setQuery($query);

		try {
			$db->execute();
			JLog::add(' --- INSERTED CC :'.$fnum.' FOR USER : '.$user, JLog::INFO, 'com_emundus');
		} catch (Exception $e) {
			JLog::add('ERROR: Could not build fnum for user at query : '.$query->__toString(), JLog::ERROR, 'com_emundus');
			continue;
		}
	}


	foreach ($insert_row as $table_name => $element) {

		if (empty($element)) {
			JLog::add('ERROR: Empty element : '.$table_name.'___'.array_keys($element)[0], JLog::ERROR, 'com_emundus');
			continue;
		}


		$columns = ['fnum','user'];
		$values = [$db->quote($fnum), $user];
		$fields = [];

		foreach ($element as $element_name => $element_value) {

			if (!is_integer($element_value)) {
				$element_value = $db->quote($element_value);
			}

			if ($table_name == 'jos_emundus_users') {

				$fields[] = $db->quoteName($element_name).' = '.$element_value;

			} else {

				$columns[] = $element_name;
				$values[]  = $element_value;
			}

		}
		
		if ($table_name == 'jos_emundus_users') {
			$query->clear()
				->update($db->quoteName($table_name))
				->set($fields)
				->where($db->quoteName('user_id').' = '.$user);
		} else {
			$query->clear()
				->insert($db->quoteName($table_name))
				->columns($db->quoteName($columns))
				->values(implode(',', $values));
			$db->setQuery($query);
		}

		try {
			$db->execute();
			JLog::add(' --- INSERTED ROW :'.$db->insertid().' AT TABLE : '.$table_name, JLog::INFO, 'com_emundus');
		} catch (Exception $e) {
			JLog::add('ERROR inserting data in query : '.$query->__toString(), JLog::ERROR, 'com_emundus');
		}

	}


	// TODO: Support repeat groups.

}


return true;
