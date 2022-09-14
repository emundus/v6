<?php
/**
 * @version     1.0.0
 * @package     com_emundus
 * @copyright   Copyright (C) 2022. Tous droits réservés.
 * @license     GNU General Public License version 2 ou version ultérieure ; Voir LICENSE.txt
 * @author      bhubinet <brice.hubinet@emundus.fr> - http://www.emundus.fr
 */
// No direct access
defined('_JEXEC') or die;

/**
 * Emundus helper.
 */
class EmundusHelperUpdate
{

    /**
     * Get all emundus plugins
     *
     * @return array|mixed
     *
     * @since version 1.33.0
     */
    public static function getEmundusPlugins() {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->select('*')
                ->from('#__extensions')
                ->where("folder LIKE '%emundus%' OR element LIKE " . $db->q('%emundus%') . " AND type='plugin'");
            $db->setQuery($query);
            return $db->loadObjectList();
        } catch (Exception $e){
            echo $e->getMessage();
            return [];
        }
    }

    /**
     * Disable an emundus plugin
     *
     * @param $name
     *
     * @return false|mixed
     *
     * @since version 1.33.0
     */
    public static function disableEmundusPlugins($name) {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->update('#__extensions')
                ->set('enabled = 0')
                ->where("element LIKE " . $db->q('%'. $name .'%'));
            $db->setQuery($query);
            return $db->execute();
        } catch (Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

    /**
     * Update a parameter of a Joomla module
     *
     * @param $name
     * @param $param
     * @param $value
     *
     *
     * @since version 1.33.0
     */
    public static function updateModulesParams($name, $param, $value) {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->select('id,params')
                ->from("#__modules")
                ->where('module LIKE ' . $db->q('%'.$name.'%'));
            $db->setQuery($query);
            $rows =  $db->loadObjectList();

            foreach ($rows as $row) {
                $params = json_decode($row->params,true);
                $params[$param] = $value;

                $query->clear()
                    ->update("#__modules")
                    ->set("params = " . $db->quote(json_encode($params)))
                    ->where("id = " . $row->id);
                $db->setQuery($query);
                $db->execute();
            }
        }
        catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Update a parameter of a row in database. Parameteres updated need to be in a json format.
     *
     * @param $table
     * @param $where
     * @param $name
     * @param $param
     * @param $valuesToSet
     * @param $updateParams
     *
     *
     * @since version 1.33.0
     */
    public static function genericUpdateParams($table, $where, $name, $param, $valuesToSet, $updateParams = null) {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        if (empty($updateParams[0])) {
            $updateParams[0] = "params";
        }
        if (empty($updateParams[1])) {
            $updateParams[1] = "id";
        }
        try {
            $query->select('*')
                ->from($table)
                ->where($where. ' LIKE ' . $db->q('%'.$name.'%'));
            $db->setQuery($query);
            $rows =  $db->loadObjectList();

            foreach ($rows as $row) {
                $params = json_decode($row->params,true);
                foreach ($param as $k => $par) {
                    $params[$par] = $valuesToSet[$k];
                }

                $query->clear()
                    ->update($table)
                    ->set($updateParams[0] . ' = ' . $db->quote(json_encode($params)))
                    ->where($updateParams[1] . ' = ' . $row->id);
                $db->setQuery($query);
                $db->execute();
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Update a parameter of a fabrik cron plugin
     *
     * @param $name
     * @param $param
     * @param $value
     *
     *
     * @since version 1.33.0
     */
    public static function updateFabrikCronParams($name, $param, $values) {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {

            $query->select('id,params')
                ->from($db->quoteName('#__fabrik_cron'))
                ->where('plugin LIKE ' . $db->q('%' . $name . '%'));
            $db->setQuery($query);
            $rows = $db->loadObjectList();

            foreach ($rows as $row) {
                $params = json_decode($row->params, true);
                foreach ($param as $k => $par) {
                    $params[$par] = $values[$k];
                }

                $query->clear()
                    ->update($db->quoteName('#__fabrik_cron'))
                    ->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($params)))
                    ->where($db->quoteName('id') . ' = ' . $db->quote($row->id));
                $db->setQuery($query);
                $db->execute();
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Update a parameter of SecurityCheckPro
     *
     * @param $name
     * @param $param
     * @param $value
     *
     *
     * @since version 1.33.0
     */
    public static function updateSCPParams($name, $param, $values) {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->select('storage_key,storage_value')
                ->from("#__securitycheckpro_storage")
                ->where('storage_key LIKE ' . $db->q('%' . $name . '%'));
            $db->setQuery($query);
            $rows = $db->loadObjectList();

            foreach ($rows as $row) {
                $params = json_decode($row->storage_value, true);
                foreach ($param as $k => $par) {
                    $params[$par] = $values[$k];
                }

                $query->clear()
                    ->update("#__securitycheckpro_storage")
                    ->set("storage_value = " .$db->quote(json_encode($params)))
                    ->where("storage_key = " . $row->storage_key);
                $db->setQuery($query);
                $db->execute();
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Update a variable in the Joomla configuration file
     *
     * @param $param
     * @param $value
     *
     *
     * @since version 1.33.0
     */
    public static function updateConfigurationFile($param, $value) {
        $formatter = new JRegistryFormatPHP();
        $config = new JConfig();

        $config->$param = $value;
        $params = array('class' => 'JConfig', 'closingtag' => false);
        $str = $formatter->objectToString($config, $params);
        $config_file = JPATH_CONFIGURATION . '/configuration.php';

        if (file_exists($config_file) and is_writable($config_file)){
            file_put_contents($config_file,$str);
        } else {
            echo ("Update Configuration file failed");
        }
    }

    /**
     * Update a variable in a yaml file like Gantry configuration files
     *
     * @param $key1
     * @param $value
     * @param $file
     * @param $key2
     *
     *
     * @since version 1.33.0
     */
    public static function updateYamlVariable($key1,$value,$file,$key2 = null) {
        $yaml = \Symfony\Component\Yaml\Yaml::parse(file_get_contents($file));

        if(!empty($key2) && isset($yaml[$key1])){
            if(isset($yaml[$key1][$key2])) {
                $yaml[$key1][$key2] = $value;
            }
        } elseif (isset($yaml[$key1])){
            $yaml[$key1] = $value;
        } else {
            echo ("Key " . $key1 . ' not found in file ' . $file);
        }

        $new_yaml = \Symfony\Component\Yaml\Yaml::dump($yaml);

        file_put_contents($file, $new_yaml);
    }

}
