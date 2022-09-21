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

    /**
     * @param $tag
     * @param $value
     * @param $type
     * @param $reference_id
     * @param $reference_table
     * @param $reference_field
     * @param $lang
     *
     * @return bool|mixed
     *
     * @since version 1.33.0
     */
    public static function insertTranslationsTag($tag,$value,$type = 'override', $reference_id = null, $reference_table = null, $reference_field = null, $lang = 'fr-FR'){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->select('id')
                ->from($db->quoteName('#__emundus_setup_languages'))
                ->where($db->quoteName('tag') . ' LIKE ' . $db->quote($tag))
                ->andWhere($db->quoteName('lang_code') . ' LIKE ' . $db->quote($lang));
            $db->setQuery($query);
            $tag_existing = $db->loadResult();

            if(empty($tag_existing)) {
                $query->insert($db->quoteName('#__emundus_setup_languages'))
                    ->set($db->quoteName('tag') . ' = ' . $db->quote($tag))
                    ->set($db->quoteName('lang_code') . ' = ' . $db->quote($lang))
                    ->set($db->quoteName('override') . ' = ' . $db->quote($value))
                    ->set($db->quoteName('original_text') . ' = ' . $db->quote($value))
                    ->set($db->quoteName('original_md5') . ' = ' . $db->quote(md5($value)))
                    ->set($db->quoteName('override_md5') . ' = ' . $db->quote(md5($value)))
                    ->set($db->quoteName('location') . ' = ' . $db->quote($lang . 'override.ini'))
                    ->set($db->quoteName('type') . ' = ' . $db->quote($type))
                    ->set($db->quoteName('reference_id') . ' = ' . $db->quote($reference_id))
                    ->set($db->quoteName('reference_table') . ' = ' . $db->quote($reference_table))
                    ->set($db->quoteName('reference_field') . ' = ' . $db->quote($reference_field))
                    ->set($db->quoteName('created_date') . ' = ' . $db->quote(date('Y-m-d H:i:s')))
                    ->set($db->quoteName('created_by') . ' = 62')
                    ->set($db->quoteName('modified_date') . ' = ' . $db->quote(date('Y-m-d H:i:s')))
                    ->set($db->quoteName('modified_by') . ' = 62')
                    ->set($db->quoteName('published') . ' = 1');
                $db->setQuery($query);
                return $db->execute();
            } else {
                return true;
            }
        } catch (Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

    public static function languageFileToBase() {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query
            ->select('DISTINCT(element), CONCAT(type, "s") AS type')
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('element') . ' LIKE ' . $db->quote('%emundus%'));
        $db->setQuery($query);

        try {
            $extensions = $db->loadObjectList();
        } catch (Exception $e) {
            return ['status' => false, 'message' => "Error getting extensions"];
        }

        $query->clear()
            ->select($db->quoteName('lang_code'))
            ->from($db->quoteName('#__languages'))
            ->where($db->quoteName('published') . ' = 1');
        $db->setQuery($query);

        try {
            $platform_languages = $db->loadColumn();
        } catch (Exception $e) {
            return ['status' => false, 'message' => "Cannot getting platform languages"];
        }

        // Components, modules, extensions files
        $files = [];
        foreach ($platform_languages as $language) {
            foreach ($extensions as $extension) {
                $file = JPATH_BASE . '/' . $extension->type . '/' . $extension->element . '/language/' . $language . '/' . $language.'.'.$extension->element. '.ini';
                if (file_exists($file)) {
                    $files[] = $file;
                }
            }
            // Overrides
            $override_file = JPATH_BASE . '/language/overrides/' . $language.'.override.ini';
            if (file_exists($override_file)) {
                $files[] = $override_file;
            }
            //
        }
        //

        $db_columns = [
            $db->quoteName('tag'),
            $db->quoteName('lang_code'),
            $db->quoteName('override'),
            $db->quoteName('original_text'),
            $db->quoteName('original_md5'),
            $db->quoteName('override_md5'),
            $db->quoteName('location'),
            $db->quoteName('type'),
            $db->quoteName('created_by'),
            $db->quoteName('reference_id'),
            $db->quoteName('reference_table'),
            $db->quoteName('reference_field'),
        ];

        $length = 0;
        $index= 0;
        foreach ($files as $file) {
            $parsed_file = JLanguageHelper::parseIniFile($file);
            $length += sizeof($parsed_file);
        }
        foreach ($files as $file) {
            $parsed_file = JLanguageHelper::parseIniFile($file);

            $file = explode('/', $file);
            $file_name = end($file);
            $language = strtok($file_name, '.');

            foreach ($parsed_file as $key => $val) {
                $index++;
                echo "\r\033[33m$index/$length translations backed \033[0m";
                $query->clear()
                    ->select('count(id)')
                    ->from($db->quoteName('jos_emundus_setup_languages'))
                    ->where($db->quoteName('tag') . ' = ' . $db->quote($key))
                    ->andWhere($db->quoteName('lang_code') . ' = ' . $db->quote($language))
                    ->andWhere($db->quoteName('location') . ' = ' . $db->quote($file_name));
                $db->setQuery($query);

                if($db->loadResult() == 0) {
                    if(strpos($file_name,'override') !== false) {
                        // Search if value is use in fabrik
                        $reference_table = null;
                        $reference_id = null;
                        $reference_field = null;

                        $query->clear()
                            ->select('id')
                            ->from($db->quoteName('#__fabrik_forms'))
                            ->where($db->quoteName('label') . ' LIKE ' . $db->quote($key));
                        $db->setQuery($query);
                        $find = $db->loadResult();

                        if(!empty($find)){
                            $reference_table = 'fabrik_forms';
                            $reference_id = $find;
                            $reference_field = 'label';
                        } else {
                            $query->clear()
                                ->select('id,intro')
                                ->from($db->quoteName('#__fabrik_forms'));
                            $db->setQuery($query);
                            $forms_intro = $db->loadObjectList();

                            foreach ($forms_intro as $intro) {
                                if (strip_tags($intro->intro) == $key) {
                                    $find = $intro->id;
                                    break;
                                }
                            }

                            if (!empty($find)) {
                                $reference_table = 'fabrik_forms';
                                $reference_id = $find;
                                $reference_field = 'intro';
                            } else {
                                $query->clear()
                                    ->select('id')
                                    ->from($db->quoteName('#__fabrik_groups'))
                                    ->where($db->quoteName('label') . ' LIKE ' . $db->quote($key));
                                $db->setQuery($query);
                                $find = $db->loadResult();

                                if(!empty($find)) {
                                    $reference_table = 'fabrik_groups';
                                    $reference_id = $find;
                                    $reference_field = 'label';
                                } else {
                                    $query->clear()
                                        ->select('id,params')
                                        ->from($db->quoteName('#__fabrik_groups'));
                                    $db->setQuery($query);
                                    $groups_params = $db->loadObjectList();

                                    if (!empty($groups_params)) {
                                        foreach ($groups_params as $group_params) {
                                            $params = json_decode($group_params->params);
                                            if (!empty($params->intro)) {
                                                if (strip_tags($params->intro) == $key) {
                                                    $find = $group_params->id;
                                                    break;
                                                }
                                            } else {
                                                $find = null;
                                            }
                                        }
                                    }

                                    if (!empty($find)) {
                                        $reference_table = 'fabrik_groups';
                                        $reference_id = $find;
                                        $reference_field = 'intro';
                                    } else {
                                        $query->clear()
                                            ->select('id')
                                            ->from($db->quoteName('#__fabrik_elements'))
                                            ->where($db->quoteName('label') . ' LIKE ' . $db->quote($key));
                                        $db->setQuery($query);
                                        $find = $db->loadResult();

                                        if(!empty($find)) {
                                            $reference_table = 'fabrik_elements';
                                            $reference_id = $find;
                                            $reference_field = 'label';
                                        } else {
                                            $query->clear()
                                                ->select('id,params')
                                                ->from($db->quoteName('#__fabrik_elements'))
                                                ->where($db->quoteName('plugin') . ' = ' . $db->quote('dropdown'));
                                            $db->setQuery($query);
                                            $elements_params = $db->loadObjectList();

                                            if(!empty($elements_params)) {
                                                foreach ($elements_params as $element_params) {
                                                    $params = json_decode($element_params->params);
                                                    if (!empty($params->sub_options)) {
                                                        $sub_options = $params->sub_options;
                                                        if (in_array($key, array_values($sub_options->sub_labels))) {
                                                            $find = $element_params->id;
                                                            break;
                                                        }
                                                    } else {
                                                        $find = null;
                                                    }
                                                }
                                            }

                                            if (!empty($find)) {
                                                $reference_table = 'fabrik_elements';
                                                $reference_id = $find;
                                                $reference_field = 'sub_labels';
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        //
                        $row = [$db->quote($key), $db->quote($language), $db->quote($val), $db->quote($val), $db->quote(md5($val)), $db->quote(md5($val)), $db->quote($file_name),$db->quote('override'), 62, $db->quote($reference_id), $db->quote($reference_table), $db->quote($reference_field)];
                    } else {
                        $row = [$db->quote($key), $db->quote($language), $db->quote($val), $db->quote($val), $db->quote(md5($val)), $db->quote(md5($val)), $db->quote($file_name),$db->quote(null), 62, $db->quote(null), $db->quote(null), $db->quote(null)];
                    }

                    $query
                        ->clear()
                        ->insert($db->quoteName('jos_emundus_setup_languages'))
                        ->columns($db_columns)
                        ->values(implode(',', $row));
                    $db->setQuery($query);

                    try {
                        $db->execute();
                    } catch (Exception $exception) {
                        $error[] = $key . ' : ' . $exception->getMessage();
                    }
                }
            }
        }
        if(!empty($error)) {
            return $error;
        }

        return ['status' => true, 'message' => "Language files successfully backed on database"];
    }

    public static function languageBaseToFile(){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select($db->quoteName('lang_code'))
            ->from($db->quoteName('#__languages'))
            ->where($db->quoteName('published') . ' = 1');
        $db->setQuery($query);

        try {
            $platform_languages = $db->loadColumn();
        } catch (Exception $e) {
            return ['status' => false, 'message' => "Cannot getting platform languages"];
        }

        try {
            $files = [];
            foreach ($platform_languages as $language) {
                $override_file = JPATH_BASE . '/language/overrides/' . $language . '.override.ini';
                if (file_exists($override_file)) {
                    $files[] = $override_file;
                }
            }


            foreach ($files as $file) {
                $file_explode = explode('/', $file);
                $file_name = end($file_explode);

                $query->clear()
                    ->select('id,tag,override,location,original_md5,override_md5')
                    ->from($db->quoteName('#__emundus_setup_languages'))
                    ->where($db->quoteName('location') . ' LIKE ' . $db->quote($file_name));
                $db->setQuery($query);
                $modified_overrides = $db->loadObjectList();

                $parsed_file = JLanguageHelper::parseIniFile($file);
                if(empty($parsed_file)) {
                    foreach ($modified_overrides as $modified_override) {
                        $parsed_file[$modified_override->tag] = $modified_override->override;
                    }
                    JLanguageHelper::saveToIniFile($file, $parsed_file);
                } else {
                    foreach ($modified_overrides as $modified_override) {
                        if(empty($parsed_file[$modified_override->tag])) {
                            $parsed_file[$modified_override->tag] = $modified_override->override;
                        }
                    }
                    JLanguageHelper::saveToIniFile($file, $parsed_file);
                }
            }
        } catch(Exception $e){
            return ['status' => false, 'message' => "Error when import translation into file : " . $e->getMessage()];
        }

        return ['status' => true, 'message' => "Language translations successfully inserted into files"];
    }

}
