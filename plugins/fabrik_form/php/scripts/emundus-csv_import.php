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
JLog::addLogger(array('text_file' => 'com_emundus.csvimport.php'), JLog::ALL, array('com_emundus.csvimport'));


//setlocale(LC_ALL, "en_GB.ISO-8859-1");

//Force conversion for file comming from Excel. WARNING: this methode use a lot of memory
function convert( $str ) {
    return iconv( "Windows-1252", "UTF-8", $str );
}

/**
* @param string $filePath
* @param int $checkLines
* @return string
*/
function getCsvDelimiter($filePath, $checkLines = 3)
{
    $delimiters =[",", ";", "\t"];

    $default =",";

    $fileObject = new \SplFileObject($filePath);
    $results = [];
    $counter = 0;
    while ($fileObject->valid() && $counter <= $checkLines) {
        $line = $fileObject->fgets();
        foreach ($delimiters as $delimiter) {
            $fields = explode($delimiter, $line);
            $totalFields = count($fields);
            if ($totalFields > 1) {
                if (!empty($results[$delimiter])) {
                    $results[$delimiter] += $totalFields;
                } else {
                    $results[$delimiter] = $totalFields;
                }
            }
        }
        $counter++;
    }
    if (!empty($results)) {
        $results = array_keys($results, max($results));

        return $results[0];
    }
    return $default;
 }


$app = JFactory::getApplication();

$csv = $formModel->data['jos_emundus_setup_csv_import___csv_file_raw'];
$campaign = $formModel->data['jos_emundus_setup_csv_import___campaign_raw'][0];
$profile_id = $formModel->data['jos_emundus_setup_csv_import___profile'];
$create_new_fnum = $formModel->data['jos_emundus_setup_csv_import___create_new_fnum'];
$send_email = $formModel->data['jos_emundus_setup_csv_import___send_email_raw'][0];
$resume = '';

// Check if the file is a file on the server and in the right format.
if (!is_file(JPATH_ROOT.$csv)) {
    JLog::add('ERROR: Tried to upload something that was not a file.', JLog::ERROR, 'com_emundus.csvimport');
    $app->enqueueMessage('ERROR: Tried to upload something that was not a file.', 'error');
    return false;
}

if (pathinfo($csv, PATHINFO_EXTENSION) !== 'csv') {
    JLog::add('ERROR: Tried to upload something that was not a csv file.', JLog::ERROR, 'com_emundus.csvimport');
    $app->enqueueMessage('ERROR: Tried to upload something that was not a csv file.', 'error');
    return false;
}

// auto_detect_line_endings allows PHP to detect MACOS line endings or else things get ugly...
ini_set('auto_detect_line_endings', TRUE);

$handle = fopen(JPATH_ROOT.$csv, 'r');
if (!$handle) {
    JLog::add('ERROR: Could not open import file.', JLog::ERROR, 'com_emundus.csvimport');
    $app->enqueueMessage('ERROR: Could not open import file.', 'error');
    return false;
}

JPluginHelper::importPlugin('emundus');
$dispatcher = JEventDispatcher::getInstance();
$dispatcher->trigger('onCallEventHandler', ['onBeforeImportCSV', ['data' => array(
    'csv' => $csv,
    'create_new_fnum' => $create_new_fnum,
    'formData' => $formModel->formData,
)]]);

// Prepare data structure for parsing.
$database_elements = [];
$bad_columns = [];

$db = JFactory::getDbo();
$query = $db->getQuery(true);
$checked_tables = [];
$repeat_tables = [];

$profile = $formModel->data['jos_emundus_setup_csv_import___profile_raw'][0];
$group = $formModel->data['jos_emundus_setup_csv_import___group_raw'][0];

$row = 0;

$delimiter = getCsvDelimiter(JPATH_ROOT.$csv);

if (($data = fgetcsv($handle, 0, $delimiter)) !== false) {

    foreach ($data as $column_number => $column) {

        //try to convert char
        //$data = array_map( "convert", $data );

        // If the file name is not in the following format : table___element; mark column as bad.
        $column = explode("___", trim(preg_replace('/[^\PC\s]/u', '', $column)));
        if (count($column) !== 2) {

            // Special columns such as the campaign ID can be inserted.
            if ($column[0] == 'campaign') {
                $campaign_column = $column_number;
            } else if ($column[0] == 'status') {
                $status_column = $column_number;
            } else if ($column[0] == 'cas_username') {
                // TODO: Adapt this column name to be the real CAS username column name.
                // Be careful that the provided cas_username not contain ___.
                $cas_column = $column_number;
            } else if ($column[0] == 'group') {
                $group_column = $column_number;
            } else if ($column[0] == 'profile') {
                $profile_column = $column_number;
            } else if ($column[0] == 'jos_emundus_comments') {
                $comments_column = $column_number;
            } else if ($column[0] == 'jos_emundus_tag_assoc') {
                $tags_column = $column_number;
            }

            $bad_columns[] = $column_number;
            continue;
        }

        $table = $column[0];
        $element = $column[1];

        // Test the existence of the table and the fnum column.
        if ($table !== 'jos_emundus_users' && $table !== 'jos_emundus_campaign_candidature') {

            if (!in_array($table, $checked_tables)) {

                // Check if we are dealing with a repeat table.
                if (strpos($table, '_repeat') !== false) {

                    // If we are, we check for the presence of the parent_id column.
                    $db->setQuery('SHOW COLUMNS FROM '.$db->quoteName($table).' LIKE '.$db->quote('parent_id'));
                    try {
                        if (empty($db->loadResult())) {
                            $bad_columns[] = $column_number;
                            continue;
                        }
                    } catch (Exception $e) {
                        $bad_columns[] = $column_number;
                        continue;
                    }

                    // Parse parent table name from repeat table name using a RegEx.
                    $parent_table = preg_split('/_\d+_repeat$/', $table);

                    // If the result of the preg_split contains 2 elements (meaning the regex found a match) but the second is empty (meaning we correctly split on the end of the string), the table name matches the correct format.
                    if (sizeof($parent_table) === 2 && empty($parent_table[1])) {
                        $parent_table = $parent_table[0];
                    } else {

                        // In case our table is not a repeat group, we need to check the case of a repeat element (like a databasejoin with a multi-select)
                        $parent_table = preg_split('/_repeat_'.$element.'$/', $table);

                        // If the result of the preg_split contains 2 elements (meaning the regex found a match) but the second is empty (meaning we correctly split on the end of the string), the table name matches the correct format.
                        if (sizeof($parent_table) === 2 && empty($parent_table[1])) {
                            $parent_table = $parent_table[0];
                        } else {
                            $bad_columns[] = $column_number;
                            continue;
                        }
                    }

                    // If the parent table is not in the list of known parents, we need to check if it contains an fnum column.
                    if (!in_array($parent_table, array_keys($repeat_tables))) {

                        // Check for the presence of the fnum in the parent table.
                        $db->setQuery('SHOW COLUMNS FROM '.$db->quoteName($parent_table).' LIKE '.$db->quote('fnum'));
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

                    // We add the table to the repeat tables so we can later insert.
                    if (!in_array($table, $repeat_tables)) {
                        $repeat_tables[$column_number]->parent = $parent_table;
                        $repeat_tables[$column_number]->table = $table;
                    }

                    $repeat = true;

                } else {

                    // If not, we check for the presence of the fnum.
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
    JLog::add('ERROR: Empty file was uploaded.', JLog::ERROR, 'com_emundus.csvimport');
    $app->enqueueMessage('ERROR: Empty file was uploaded.', 'error');
    return false;
}

$parsed_data = [];

if(empty($campaign_column) && empty($campaign)){
    JLog::add('ERROR: Could not get campaign_id from Fabrik form or csv file [campaign] in row.', JLog::ERROR, 'com_emundus.csvimport');
    $app->enqueueMessage('ERROR: Could not get campaign_id from Fabrik form or csv file [campaign] in row.', 'error');
    return false;
}

while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {

    //try to convert char
    //$data = array_map("convert", $data);

    foreach ($data as $column_number => $column) {

        // Clean up data from any invisible chars in xls.
        $column = trim(preg_replace('/[^\PC\s]/u', '', $column));

        if($profile_id > 0) {
            $profile_row[$row] = $profile_id;
        } elseif ($column_number === $profile_column) {
            $profile_row[$row] = $column;
        } else {
            JLog::add('WARNING: Could not get profile from Fabrik form or csv file [profile] in row.', JLog::WARNING, 'com_emundus.csvimport');
        }

        if($campaign > 0) {
            $campaign_row[$row] = $campaign; 
        } elseif ($column_number === $campaign_column) {
            $campaign_row[$row] = $column;

            // If we have no profile, we must get the associated one using the campaign.
            if (empty($profile) && !isset($profile_column)) {

                $query->clear()
                    ->select($db->quoteName('profile_id'))
                    ->from($db->quoteName('#__emundus_setup_campaigns'))
                    ->where($db->quoteName('id').' = '.$column);
                $db->setQuery($query);

                try {
                    $profile_row[$row] = $db->loadResult();
                } catch (Exception $e) {
                    JLog::add('ERROR: Could not get profile using campaign in row.', JLog::ERROR, 'com_emundus.csvimport');
                    continue;
                }

            }

            continue;
        }
        
        if ($column_number === $status_column) {
            $status_row[$row] = $column;
            continue;
        } elseif ($column_number === $cas_column) {
            $cas_row[$row] = $column;
        } elseif ($column_number === $group_column) {
            $group_row[$row] = $column;
        } elseif ($column_number === $comments_column) {
            $comments_row[$row] = $column;
        } elseif ($column_number === $tags_column) {
            $tags_row[$row] = $column;
        }

        if (in_array($column_number, $bad_columns)) {
            continue;
        }

        if (in_array($column_number, array_keys($repeat_tables))) {
            // The repeat values are inserted into a separate array as they will be inserted into the DB AFTER their parent table.
            $parsed_repeat[$row][$repeat_tables[$column_number]->parent][$repeat_tables[$column_number]->table][$database_elements[$column_number]->column] = $column;
        } else {
            // Build the complex data structure.
            $parsed_data[$row][$database_elements[$column_number]->table][$database_elements[$column_number]->column] = $column;
        }

    }

    $row++;
}
fclose($handle);

// If we never incremented row then there are not files being imported.
if ($row === 0) {
    JLog::add('ERROR: No data sent in file.', JLog::ERROR, 'com_emundus.csvimport');
    $app->enqueueMessage('ERROR: No data sent in file.', 'error');
    return false;
}

// If have no parsed data, something went wrong.
if (empty($parsed_data)) {
    JLog::add('ERROR: Something went wrong, please check that your CSV is separated by semi-colons (;).', JLog::ERROR, 'com_emundus.csvimport');
    $app->enqueueMessage('ERROR: Something went wrong, please check that your CSV is separated by semi-colons (;).', 'error');
    return false;
}


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

// Check if the CAS auth plugin is activated.
$cas_plugin = JPluginHelper::getPlugin('system','caslogin');

$totals = [
    'ldap' => 0,
    'cas' => 0,
    'user' => 0,
    'fnum' => 0,
    'write' => 0
];


if (!JFactory::getUser()->authorise('core.admin') && !EmundusHelperAccess::asAccessAction(12, 'c')) {
    $can_create_user = false;
} else {
    $can_create_user = true;
}

// Handle parsed data insertion
foreach ($parsed_data as $row_id => $insert_row) {

    $fnum = $insert_row['jos_emundus_campaign_candidature']['fnum'];

    // Clear any potential user ID from previous iteration.
    unset($user);
    $new_user = false;

    // We can pass the campaign ID in the XLS if we need.
    if (!empty($campaign_row[$row_id]) && is_numeric($campaign_row[$row_id])) {
        $campaign = $campaign_row[$row_id];
    }

    if (!empty($status_row[$row_id]) && is_numeric($status_row[$row_id])) {
        $status = $status_row[$row_id];
    }

    if (!empty($group_row[$row_id]) && is_numeric($group_row[$row_id])) {
        $group = $group_row[$row_id];
    }

    if (!empty($comments_row[$row_id])) {
        $comments = $comments_row[$row_id];
    }

    if (!empty($tags_row[$row_id])) {
        $tags = $tags_row[$row_id];
    }

    if (!empty($profile_row[$row_id]) && is_numeric($profile_row[$row_id])) {
        $profile = $profile_row[$row_id];
    } elseif (empty($profile) && !empty($campaign)) {

        $query->clear()
            ->select($db->quoteName('profile_id'))
            ->from($db->quoteName('#__emundus_setup_campaigns'))
            ->where($db->quoteName('id').' = '.$campaign);
        $db->setQuery($query);

        try {
            $profile_row[$row] = $db->loadResult();
        } catch (Exception $e) {
            JLog::add('ERROR: Could not get profile using campaign in row.', JLog::ERROR, 'com_emundus.csvimport');
            continue;
        }
    }

    // We need a user
    if (!empty($insert_row['jos_emundus_users']['user_id'])) {

        // In the case of a user id already provided in the CSV
        $user = (int)$insert_row['jos_emundus_users']['user_id'];
        if (JFactory::getUser($user)->guest) {
            unset($user);
        }

    } elseif (!empty($insert_row['jos_emundus_users']['username']) || !empty($insert_row['jos_emundus_users']['email'])) {

        $username = (!empty($insert_row['jos_emundus_users']['username'])) ? $insert_row['jos_emundus_users']['username'] : strtolower($insert_row['jos_emundus_users']['email']);

        // If we have an email present then we need to check if a user already exists.
        $query->clear()
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__users'))
            ->where($db->quoteName('username').' LIKE '.$db->quote($username));
        $db->setQuery($query);

        try {
            $user = $db->loadResult();
        } catch (Exception $e) {
            continue;
        }

    } else {

        if (!empty($fnum)) {
            $user = (int)substr($fnum, -7);
        }

    }

    if (empty($user)) {

        if (!$can_create_user) {
            JLog::add('ERROR: You do not have the rights to create a user.', JLog::ERROR, 'com_emundus.csvimport');
            $app->enqueueMessage('ERROR: '.JFactory::getUser()->name.' does not have the rights to create a user.', 'error');

            return false;
        }

        $username = (!empty($insert_row['jos_emundus_users']['username'])) ? $insert_row['jos_emundus_users']['username'] : strtolower($insert_row['jos_emundus_users']['email']);
        $email = $insert_row['jos_emundus_users']['email'];
        $firstname = $insert_row['jos_emundus_users']['firstname'];
        $lastname = $insert_row['jos_emundus_users']['lastname'];
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
                JLog::add('ERROR: Missing some user details, cannot create user.', JLog::ERROR, 'com_emundus.csvimport');
                continue;
            }
        }

        $cas_user = false;
        // If CAS is active, manage the following case: we need a user object already in the DB that can be logged into via CAS.
        if ($cas_plugin && !empty($cas_row[$row_id])) {

            $username = $cas_row[$row_id];
            $cas_user = true;

            // Check if any data is missing.
            if (empty($username) || empty($email) || empty($firstname) || empty($lastname)) {
                JLog::add('ERROR: Missing some user details, cannot create user.', JLog::ERROR, 'com_emundus.csvimport');
                continue;
            }
        }

        JLog::add('--- '.$row_id.' Username: '.$username, JLog::INFO, 'com_emundus.csvimport');

        $user = clone(JFactory::getUser(0));
        if (preg_match('/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-z\-0-9]+\.)+[a-z]{2,}))$/', $username) !== 1) {
            JLog::add('ERROR: Username format not OK: '.$username, JLog::ERROR, 'com_emundus.csvimport');
            continue;
        }
        if (preg_match('/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-z\-0-9]+\.)+[a-z]{2,}))$/', $email) !== 1) {
            JLog::add('ERROR: Email format not OK: '.$email, JLog::ERROR, 'com_emundus.csvimport');
            continue;
        }

        $user->name = ucfirst($firstname).' '.strtoupper($lastname);
        $user->username = $username;
        $user->email = $email;

        // If our user comes from the LDAP system, he has no password.
        // If he doesn't, he needs one generated.
        if (!$ldap_user && !$cas_user) {
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
        $user->usertype = $m_users->found_usertype($acl_aro_groups[0]);
        $uid = $m_users->adduser($user, $other_param);
        $user->id = $uid;

        if (is_array($uid)) {
            JLog::add('ERROR: Inserting the user ('.$user->email.') failed.', JLog::ERROR, 'com_emundus.csvimport');
            continue;
        }

        if (!defined('EMUNDUS_PATH_ABS')) {
            define('EMUNDUS_PATH_ABS', JPATH_ROOT.DS.$emundus_params->get('applicant_files_path', 'images/emundus/files/'));
        }

        if (!mkdir(EMUNDUS_PATH_ABS.$uid, 0755) || !copy(EMUNDUS_PATH_ABS.'index.html', EMUNDUS_PATH_ABS.$uid.DS.'index.html')) {
            JLog::add('ERROR: Creating the user file on the server ('.EMUNDUS_PATH_ABS.$uid.') failed.', JLog::ERROR, 'com_emundus.csvimport');
            continue;
        }

        // If we are adding our user via CAS, we need to register him in the extLogin table.
        if ($cas_user) {

            $query->insert($db->quoteName('#__externallogin_users'))
                ->columns($db->quoteName(['server_id','user_id']))
                ->values('1,'.$uid);
            $db->setQuery($query);

            try {
                $db->execute();
            } catch (Exception $e) {
                JLog::add('ERROR: Could not add user to list of CAS users for extLogin plugin.', JLog::ERROR, 'com_emundus.csvimport');
                continue;
            }
        }

        $new_user = true;

    } else {

        // Check if the user has not been activated.
        $table = JTable::getInstance('user', 'JTable');
        $table->load($user);
        $table->block = 0;
        $table->store();
        $user = JFactory::getUser($user);
    }

    if (!$create_new_fnum) {
        // If the user has no fnum, get the one made by the user creation code.
        if (empty($fnum)) {

            $query->clear()
                ->select($db->quoteName('fnum'))
                ->from($db->quoteName('#__emundus_campaign_candidature'))
                ->where($db->quoteName('applicant_id').' = '.$user->id.' AND '.$db->quoteName('campaign_id').' = '.$campaign);
            $db->setQuery($query);

            try {
                $fnum = $db->loadResult();
            } catch (Exception $e) {
                JLog::add('ERROR: Could not get fnum for user : '.$user->id.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus.csvimport');
            }
        }
    }


    // If no fnum is found, generate it.
    if (empty($fnum) && !empty($campaign)) {
        $fnum_date = date('YmdHis');
        $fnum_date = date('YmdHis',strtotime('+'.$row_id.' seconds',strtotime($fnum_date)));
        $fnum = $fnum_date.str_pad($campaign, 7, '0', STR_PAD_LEFT).str_pad($user->id, 7, '0', STR_PAD_LEFT);       $query->clear()
            ->insert($db->quoteName('#__emundus_campaign_candidature'))
            ->columns($db->quoteName(['applicant_id', 'user_id', 'campaign_id', 'fnum', 'status']))
            ->values($user->id.', '.JFactory::getUser()->id.', '.$campaign.', '.$db->quote($fnum).', '.$status);
        $db->setQuery($query);

        try {
            $db->execute();
            $totals['fnum']++;
            $totals['write']++;
            JLog::add(' --- INSERTED CC :'.$fnum.' FOR USER : '.$user->id, JLog::INFO, 'com_emundus.csvimport');
        } catch (Exception $e) {
            JLog::add('ERROR: Could not build fnum for user at query : '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus.csvimport');
            continue;
        }
    }


    if (!empty($group)) {
        $query->clear()
            ->insert($db->quoteName('#__emundus_groups'))
            ->columns($db->quoteName(['user_id', 'group_id']))
            ->values($user->id.', '.$group);
        $db->setQuery($query);
        try {
            $db->execute();
            $totals['write']++;
            JLog::add(' --- INSERTED GROUP :'.$group.' FOR USER : '.$user->id, JLog::INFO, 'com_emundus.csvimport');
        } catch (Exception $e) {
            JLog::add('ERROR: Could not insert user into group at query : '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus.csvimport');
            // No continue, just a silent error with logging.
        }
    }

    if(!empty($comments)){
        $query->clear()
            ->insert($db->quoteName('#__emundus_comments'))
            ->columns($db->quoteName(['applicant_id', 'user_id','fnum','comment_body']))
            ->values($user->id.', 62, '.$fnum.', '.$db->quote($comments));
        $db->setQuery($query);
        try {
            $db->execute();
            $totals['write']++;
            JLog::add(' --- INSERTED COMMENTS :'.$comments.' FOR USER : '.$user->id, JLog::INFO, 'com_emundus.csvimport');
        } catch (Exception $e) {
            JLog::add('ERROR: Could not insert user into group at query : '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus.csvimport');
            // No continue, just a silent error with logging.
        }
    }

    if(!empty($tags)){
        $tags_ids = explode(',',$tags);
        foreach ($tags_ids as $tag) {
            $query->clear()
                ->insert($db->quoteName('#__emundus_tag_assoc'))
                ->columns($db->quoteName(['fnum', 'id_tag']))
                ->values($fnum . ', ' . $tag);
            $db->setQuery($query);
            try {
                $db->execute();
                $totals['write']++;
                JLog::add(' --- INSERTED TAGS :' . $tags . ' FOR USER : ' . $user->id, JLog::INFO, 'com_emundus.csvimport');
            } catch (Exception $e) {
                JLog::add('ERROR: Could not insert user into group at query : ' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'com_emundus.csvimport');
                // No continue, just a silent error with logging.
            }
        }
    }

    foreach ($insert_row as $table_name => $element) {

        $parent_ids = [];
        $executed_parent_tables = [];

        if ($table_name === 'jos_emundus_campaign_candidature') {
            continue;
        }

        if (empty($element)) {
            JLog::add('ERROR: Empty element : '.$table_name.'___'.array_keys($element)[0], JLog::ERROR, 'com_emundus.csvimport');
            continue;
        }

        $columns = ['fnum','user'];
        $values = [$db->quote($fnum), $user->id];
        $fields = [];

        foreach ($element as $element_name => $element_value) {

            if (!is_integer($element_value)) {
                $element_value = $db->quote($element_value);
            }

            if ($table_name === 'jos_emundus_users') {

                if($element_name != 'user_id'){
                    $fields[] = $db->quoteName($element_name).' = '.$element_value;
                }

            }
            else {
                $columns[] = $element_name;
                $values[]  = $element_value;
            }

        }

        if ($table_name === 'jos_emundus_users') {

                $select = array('email');

            $query->clear()
                ->select($db->quoteName($select))
                ->from($db->quoteName('#__emundus_users'))
                ->where($db->quoteName('user_id').' = '.$user->id);

            $db->setQuery($query);
            $existData = $db->loadObject();

            if($element['email'] == $existData->email){
                $app->enqueueMessage('The user with email : '.$existData->email.' already exist', 'info');
                $resume .= 'The user with email : '.$existData->email.' already exist <br>';
            }

            $query->clear()
                ->update($db->quoteName($table_name))
                ->set($fields)
                ->where($db->quoteName('user_id').' = '.$user->id);
        } else {

            if (empty($fnum)) {
                continue;
            }

            $query->clear()
                ->insert($db->quoteName($table_name))
                ->columns($db->quoteName($columns))
                ->values(implode(',', $values));
            $db->setQuery($query);
        }

        try {
            $db->execute();
            $totals['write']++;

            if ($db->insertid() == 0) {
                JLog::add(' --- UPDATE: '.$table_name, JLog::INFO, 'com_emundus.csvimport');
            } else {
                JLog::add(' --- INSERTED ROW: '.$db->insertid().' AT TABLE : '.$table_name, JLog::INFO, 'com_emundus.csvimport');
            }
        } catch (Exception $e) {
            JLog::add('ERROR inserting data in query : '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus.csvimport');
        }

        // Insert into child repeat tables.
        if (isset($parsed_repeat) && !in_array($table_name, $executed_parent_tables) && in_array($table_name, array_keys($parsed_repeat[$row_id]))) {
            $parent_id = $db->insertid();
            foreach ($parsed_repeat[$row_id] as $parent_table => $repeat_table) {
                foreach ($repeat_table as $repeat_table_name => $repeat_columns) {

                    $repeat_columns = array_merge(...array_map(function ($r_column, $k_key) {
                        return [$k_key => explode('|',trim($r_column))];
                    }, $repeat_columns, array_keys($repeat_columns)));

                    $query->clear()
                        ->insert($db->quoteName($repeat_table_name))
                        ->columns($db->quoteName(array_merge(['parent_id'],array_keys($repeat_columns))));

                    $i = 0;
                    foreach ($repeat_columns as $r_column) {
                        $insert_row = [];
                        if ($i == sizeof($r_column)) {
                            break;
                        }
                        foreach (array_keys($repeat_columns) as $r_key) {
                            $insert_row[] = is_numeric($repeat_columns[$r_key][$i])?$repeat_columns[$r_key][$i]:$db->quote($repeat_columns[$r_key][$i]);
                        }
                        $query->values($parent_id.', '.implode(',', $insert_row));
                        $i++;
                    }

                    $db->setQuery($query);
                    try {
                        $db->execute();
                        $executed_parent_tables[] = $table_name;
                        $totals['write']++;
                        JLog::add(' --- INSERTED REPEAT ROW :'.$db->insertid().' AT TABLE : '.$repeat_table_name, JLog::INFO, 'com_emundus.csvimport');
                    } catch (Exception $e) {
                        JLog::add('ERROR inserting data in query : '.preg_replace("/[\r\n]/"," ",$query->__toString()).' error text -> '.$e->getMessage(), JLog::ERROR, 'com_emundus.csvimport');
                    }
                }
            }
        }
    }

    if ($new_user && !empty($send_email) && $send_email == 1) {
        // Send email indicating account creation.
        $m_emails = new EmundusModelEmails();
        $tags = array('patterns' => [], 'replacements' => []);

        // If we are creating an ldap or cas account, we need to send a different email.
        if ($ldap_user) {
            $totals['ldap']++;
            $email = $m_emails->getEmail('new_ldap_account');
            try {
                $tags = $m_emails->setTags($user->id, null, $fnum, null, $email->emailfrom.$email->name.$email->subject.$email->message);
            } catch(Exception $e) {
                JLog::add('ERROR setting tags in query : error text -> '.$e->getMessage(), JLog::ERROR, 'com_emundus.csvimport');
            }
        } else if ($cas_user) {
            $totals['cas']++;
            $email = $m_emails->getEmail('new_cas_account');
            try {
                $tags = $m_emails->setTags($user->id, null, $fnum, null, $email->emailfrom.$email->name.$email->subject.$email->message);
            } catch(Exception $e) {
                JLog::add('ERROR setting tags in query : error text -> '.$e->getMessage(), JLog::ERROR, 'com_emundus.csvimport');
            }
        } else {
            $totals['user']++;
            $email = $m_emails->getEmail('new_account');
            try {
                $tags = $m_emails->setTags($user->id, null, $fnum, $password, $email->emailfrom.$email->name.$email->subject.$email->message);
            } catch(Exception $e) {
                JLog::add('ERROR setting tags in query : error text -> '.$e->getMessage(), JLog::ERROR, 'com_emundus.csvimport');
            }
        }

        $mailer = JFactory::getMailer();
        $from = preg_replace($tags['patterns'], $tags['replacements'], $email->emailfrom);
        $fromname = preg_replace($tags['patterns'], $tags['replacements'], $email->name);
        $subject = preg_replace($tags['patterns'], $tags['replacements'], $email->subject);
        $body = $email->message;

        if (!empty($email->Template)) {
            $body = preg_replace(["/\[EMAIL_SUBJECT\]/", "/\[EMAIL_BODY\]/"], [$subject, $body], $email->Template);
        }
        $body = preg_replace($tags['patterns'], $tags['replacements'], $body);
        $body = $m_emails->setTagsFabrik($body, [$fnum]);

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
                JLog::add('No email configuration!', JLog::ERROR, 'com_emundus.csvimport');
            } else {

                JLog::add('Email account sent: '.$user->email, JLog::INFO, 'com_emundus.csvimport');

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
            JLog::add('ERROR: Could not send email to user : '.$user->id, JLog::ERROR, 'com_emundus.csvimport');
        }
    }

    $parsed_data[$row_id]['user_id'] = $user->id;
    $parsed_data[$row_id]['fnum'] = $fnum;
}


if (!empty($totals)) {

    $totals['write'] += ((2*$totals['user']) + (2*$totals['ldap']));

    $summary = '';

    if (!empty($totals['user'])) {
        $summary .= 'Added '.$totals['user'].' new users. <br>';
    }

    if (!empty($totals['ldap'])) {
        $summary .= 'Added '.$totals['ldap'].' users found in the LDAP system. <br>';
    }

    if (!empty($totals['cas'])) {
        $summary .= 'Added '.$totals['cas'].' users using the CAS system. <br>';
    }

    if (!empty($totals['fnum'])) {
        $summary .= 'Added '.$totals['fnum'].' new candidacy files. <br>';
    }

    if (!empty($totals['write'])) {
        $summary .= 'Wrote '.$totals['write'].' lines.';
    }
    $resume .= $summary;
    $app->enqueueMessage($summary, 'info');
}

$data = array(
    'csv' => $csv,
    'rows' => $parsed_data,
    'bad_columns' => $bad_columns,
    'checked_tables' => $checked_tables,
    'repeat_tables' => $repeat_tables,
    'database_elements' => $database_elements,
    'formData' => $formModel->formData,
    'resume' => !empty($resume) ? $resume : null
);

JPluginHelper::importPlugin('emundus');
$dispatcher = JEventDispatcher::getInstance();
$dispatcher->trigger('onCallEventHandler', ['onAfterImportCSV', $data]);

return true;
