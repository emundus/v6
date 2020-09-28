<?php
/**
 * Created by PhpStorm.
 * User: James Dean
 * Date: 2019-01-28
 * Time: 14:37
 */


jimport('joomla.utilities.utility');
jimport('joomla.log.log');
JLog::addLogger(
    array(
        // Sets file name
        'text_file' => 'com_emundus.HESAMallRegionsDepartments.php'
    ),
    // Sets messages of all log levels to be sent to the file
    JLog::ALL,
    // The log category/categories which should be recorded in this file
    // In this case, it's just the one category from our extension, still
    // we need to put it inside an array
    array('com_emundus')
);

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
$app = JFactory::getApplication();

$jinput = $app->input;

$all = $formModel->getElementData('jos_emundus_users___all_regions_depatments_raw');
$formId = $formModel->getElementData('jos_emundus_users___id_raw');
$all = is_array($all) ? $all[0] : $all;

if ($all == 'oui') {

    $db = JFactory::getDBO();

    $query = $db->getquery('true');
    // Get all regions
    $query
        ->select($db->quoteName('id'))
        ->from($db->quoteName('data_regions'));

    $db->setQuery($query);
    $regions = $db->loadColumn();
    try {

        foreach ($regions as $region) {
            $query->clear();

            // insert into region repeat table
            $columns = array('parent_id', 'region');
            $values = array($formId, $region);

            $query
                ->insert($db->quoteName('#__emundus_users_597_repeat'))
                ->columns($db->quoteName($columns))
                ->values(implode(',', $values));


            $db->setQuery($query);
            $db->execute();

            // Get row id
            $regionRowId = $db->insertid();
            $query->clear();

            // Get all departments
            $query
                ->select($db->quoteName('departement_id'))
                ->from($db->quoteName('data_departements'))
                ->where($db->quoteName('region_id') . ' = ' . $region);

            $db->setQuery($query);
            $departments = $db->loadColumn();

            foreach ($departments as $department) {
                $query->clear();

                //insert into department repeat table
                $columns = array('parent_id', 'department');
                $values = array($regionRowId, $department);

                $query
                    ->insert($db->quoteName('#__emundus_users_597_repeat_repeat_department'))
                    ->columns($db->quoteName($columns))
                    ->values(implode(',', $values));

                $db->setQuery($query);
                $db->execute();
            }
        }

    } catch (Exection $e) {
        JLog::add('Error at query: ' . preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
    }

}
