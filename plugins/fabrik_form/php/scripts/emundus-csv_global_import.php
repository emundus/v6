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

//Force conversion for file comming from Excel
function convert( $str ) {
    return iconv( "Windows-1252", "UTF-8", $str );
}


$app = JFactory::getApplication();

$csv = $formModel->data['jos_emundus_setup_csv_import___csv_file_raw'];
$campaign = $formModel->data['jos_emundus_setup_csv_import___campaign_raw'][0];
$create_new_fnum = $formModel->data['jos_emundus_setup_csv_import___create_new_fnum'];

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
if (($data = fgetcsv($handle, 0, ';')) !== false) {

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

                    // We add the table to the repeat tables so we can later insert.
                    if (!in_array($table, $repeat_tables)) {
                        $repeat_tables[$column_number]->parent = $parent_table;
                        $repeat_tables[$column_number]->table = $table;
                    }

                    // If the parent table is not in the list of known parents, we need to check if it contains an fnum column.
                    if (!in_array($parent_table, array_keys($repeat_tables))) {

                        // Check for the presence of the fnum in the parent table.
                        $db->setQuery('SHOW COLUMNS FROM '.$db->quoteName($parent_table).' LIKE '.$db->quote('fnum'));
                        try {
                            if (empty($db->loadResult())) {
                                $bad_columns[] = $column_number;
                                $database_elements[$column_number]->table = $table;
                                $database_elements[$column_number]->column = $element;
                                $repeat = true;
                                continue;
                            }
                        } catch (Exception $e) {
                            $bad_columns[] = $column_number;
                            continue;
                        }

                    }

                    $repeat = true;

                } else {

                    // If not, we check for the presence of the fnum.
                    $db->setQuery('SHOW COLUMNS FROM '.$db->quoteName($table).' LIKE '.$db->quote('fnum'));
                    try {
                        if (empty($db->loadResult())) {
                            $bad_columns[] = $column_number;
                            $database_elements[$column_number]->table = $table;
                            $database_elements[$column_number]->column = $element;
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
while (($data = fgetcsv($handle, 0, ';')) !== false) {

    //try to convert char
    //$data = array_map("convert", $data);

    foreach ($data as $column_number => $column) {

        // Clean up data from any invisible chars in xls.
        $column = trim(preg_replace('/[^\PC\s]/u', '', $column));

        if ($column_number === $profile_column) {
            $profile_row[$row] = $column;
        }

        if ($column_number === $campaign_column) {
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
        } elseif ($column_number === $status_column) {
            $status_row[$row] = $column;
            continue;
        } elseif ($column_number === $cas_column) {
            $cas_row[$row] = $column;
        } elseif ($column_number === $group_column) {
            $group_row[$row] = $column;
        }

        // If in bad columns we import in an other table
        if (in_array($column_number, $bad_columns)) {
            //echo '<pre>'; var_dump($repeat_tables); echo '</pre>';
            //echo '<pre>'; var_dump($column_number); echo '</pre>';
            if (in_array($column_number, array_keys($repeat_tables))) {
                // The repeat values are inserted into a separate array as they will be inserted into the DB AFTER their parent table.
                $parsed_repeat[$row][$repeat_tables[$column_number]->parent][$repeat_tables[$column_number]->table][$database_elements[$column_number]->column] = $column;
            } else {
                // Build the complex data structure.
                //echo '<pre>'; var_dump($database_elements); echo '</pre>';
                $parsed_data[$row][$database_elements[$column_number]->table][$database_elements[$column_number]->column] = $column;
            }
            //continue;
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
    $executed_parent_tables = [];
    $table = array_keys($insert_row)[0];
    $datas = $insert_row[$table];

    $query
        ->clear()
        ->insert($table);
    foreach ($datas as $key => $data){
        $query->set($db->quoteName($key) . ' = ' . $db->quote($data));
    }
    $db->setQuery($query);
    try {
        $db->execute();
    } catch(Exception $e) {
        JLog::add('ERROR inserting data in query : '.preg_replace("/[\r\n]/"," ",$query->__toString()).' error text -> '.$e->getMessage(), JLog::ERROR, 'com_emundus.csvimport');
    }

    // Insert into child repeat tables.
    if (isset($parsed_repeat) && !in_array($table, $executed_parent_tables) && in_array($table, array_keys($parsed_repeat[$row_id]))) {
        $parent_id = $db->insertid();
        foreach ($parsed_repeat[$row_id] as $parent_table => $repeat_table) {
            foreach ($repeat_table as $repeat_table_name => $repeat_columns) {
                $query->clear()
                    ->insert($db->quoteName($repeat_table_name));
                $inserting_rows = [];

                $repeat_columns = array_merge(...array_map(function ($r_column, $k_key) {
                    return [$k_key => explode('|',trim($r_column))];
                }, $repeat_columns, array_keys($repeat_columns)));
                $query->columns('parent_id'.','.implode(',',array_keys($repeat_columns)));

                $number_values = sizeof($repeat_columns[array_keys($repeat_columns)[0]]);
                for ($i = 0;$i < $number_values;$i++) {
                    $insert_row = [];

                    foreach (array_keys($repeat_columns) as $r_key) {
                        $insert_row[] = is_numeric($repeat_columns[$r_key][$i]) ? $repeat_columns[$r_key][$i] : $db->quote($repeat_columns[$r_key][$i]);
                    }
                    $query->values($parent_id . ', ' . implode(',', $insert_row));
                }

                $db->setQuery($query);
                try {
                    $db->execute();
                    $executed_parent_tables[] = $table;
                    $totals['write']++;
                    JLog::add(' --- INSERTED REPEAT ROW :'.$db->insertid().' AT TABLE : '.$repeat_table_name, JLog::INFO, 'com_emundus.csvimport');
                } catch (Exception $e) {
                    JLog::add('ERROR inserting data in query : '.preg_replace("/[\r\n]/"," ",$query->__toString()).' error text -> '.$e->getMessage(), JLog::ERROR, 'com_emundus.csvimport');
                }
            }
        }
    }
}

return true;
