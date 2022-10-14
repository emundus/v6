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

    public static function installExtension($name,$element,$manifest_cache,$type,$enabled = 1){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->select('extension_id')
                ->from($db->quoteName('#__extensions'))
                ->where($db->quoteName('element') . ' LIKE ' . $db->quote($element));
            $db->setQuery($query);
            $is_existing = $db->loadResult();

            if(empty($is_existing)){
                $query->clear()
                    ->insert($db->quoteName('#__extensions'))
                    ->set($db->quoteName('name') . ' = ' . $db->quote($name))
                    ->set($db->quoteName('type') . ' = ' . $db->quote($type))
                    ->set($db->quoteName('element') . ' = ' . $db->quote($element))
                    ->set($db->quoteName('folder') . ' = ' . $db->quote(''))
                    ->set($db->quoteName('client_id') . ' = ' . $db->quote(0))
                    ->set($db->quoteName('enabled') . ' = ' . $db->quote($enabled))
                    ->set($db->quoteName('manifest_cache') . ' = ' . $db->quote($manifest_cache))
                    ->set($db->quoteName('params') . ' = ' . $db->quote(''))
                    ->set($db->quoteName('custom_data') . ' = ' . $db->quote(''))
                    ->set($db->quoteName('system_data') . ' = ' . $db->quote(''));
                $db->setQuery($query);
                return $db->execute();
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }

        return true;
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
                ->from($db->quoteName('#__securitycheckpro_storage'))
                ->where($db->quoteName('storage_key') . ' LIKE ' . $db->quote('%' . $name . '%'));
            $db->setQuery($query);
            $rows = $db->loadObjectList();

            foreach ($rows as $row) {
                $params = json_decode($row->storage_value, true);
                foreach ($param as $k => $par) {
                    $params[$par] = $values[$k];
                }

                $query->clear()
                    ->update($db->quoteName('#__securitycheckpro_storage'))
                    ->set($db->quoteName('storage_value') . ' = ' .$db->quote(json_encode($params)))
                    ->where($db->quoteName('storage_key') . ' = ' . $db->quote($row->storage_key));
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

    public static function addYamlVariable($key,$value,$file,$parent = null,$breakline = false,$check_existing = false) {
        $yaml = \Symfony\Component\Yaml\Yaml::parse(file_get_contents($file));

        if (!empty($parent) && isset($yaml[$parent])){
            $already_exist = false;

            if($check_existing) {
                foreach ($yaml[$parent] as $object) {
                    if (isset($object[$key]) && $object[$key] == $value) {
                        $already_exist = true;
                        break;
                    }
                }
            }

            if(!$already_exist) {
                if ($breakline) {
                    $yaml[$parent][][$key] = $value;
                } else {
                    $yaml[$parent][sizeof($yaml[$parent]) - 1][$key] = $value;
                }
            }
        } else {
            echo ("Key " . $parent . ' not found in file ' . $file);
        }

        $new_yaml = \Symfony\Component\Yaml\Yaml::dump($yaml,3,2);

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
        $updated = ['status' => true, 'message' => "Language translations successfully inserted into files"];

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select($db->quoteName('lang_code'))
            ->from($db->quoteName('#__languages'))
            ->where($db->quoteName('published') . ' = 1');
        $db->setQuery($query);

        try {
            $platform_languages = $db->loadColumn();
        } catch (Exception $e) {
            $updated = ['status' => false, 'message' => "Cannot getting platform languages"];
        }

        if (!empty($platform_languages)) {
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
                    if (empty($parsed_file)) {
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
                $updated = ['status' => false, 'message' => "Error when import translation into file : " . $e->getMessage()];
            }
        } else {
            $updated = ['status' => false, 'message' => "Empty platform languages"];
        }

        return $updated;
    }

    /**
     *
     * @return array|mixed|void
     *
     * @since version 1.33.0
     */
    public static function convertEventHandlers() {
        $updated = ['status' => true, 'message' => ''];
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->select('extension_id,params')
                ->from($db->quoteName('#__extensions'))
                ->where($db->quoteName('element') . ' LIKE ' . $db->quote('custom_event_handler'));
            $db->setQuery($query);
            $result = $db->loadObject();

            if(!empty($result->extension_id)) {
                $params = json_decode($result->params);
                $old_events = json_decode($params->event_handlers);

                $events = array_values($old_events->event);

                if(!empty($events)) {
                    $codes = array_values($old_events->code);

                    $new_events = new stdClass;
                    $new_events->event_handlers = new stdClass;

                    foreach ($events as $key => $event) {
                        $new_events->event_handlers->{'event_handlers' . $key} = new stdClass;
                        $new_events->event_handlers->{'event_handlers' . $key}->event = $event;

                        $backed_file = fopen('libraries/emundus/custom/'.strtolower($event) . '_' . $key . '.php', 'w');
                        if ($backed_file) {
                            fwrite($backed_file, '<?php ' . $codes[$key]);
                            fclose($backed_file);
                        } else {
                            JLog::add('Failed to backup events', JLog::WARNING, 'com_emundus.cli');
                        }

                        $new_events->event_handlers->{'event_handlers' . $key}->code = $codes[$key];
                    }

                    $query->clear()
                        ->update($db->quoteName('#__extensions'))
                        ->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($new_events)))
                        ->where($db->quoteName('extension_id') . ' = ' . $db->quote($result->extension_id));
                    $db->setQuery($query);
                    $db->execute();
                }
            }
        } catch (Exception $e) {
            $updated = ['status' => false, 'message' => "Error when convert event handlers : " . $e->getMessage()];
        }

        $state_msg =  $updated['status'] ? "\033[32mSUCCESS\033[0m" : "\033[31mFAILED\033[0m";
        echo "\n-> Finish update event handlers [$state_msg]";

        return $updated;
    }

    /**
     * @return bool
     *
     * @since version 1.33.0
     */
    public static function updateCampaignWorkflowTable(): array
    {
        $update_campaign_workflow = ['status' => false, 'message' => ''];

        jimport('joomla.log.log');
        JLog::addLogger(['text_file' => 'com_emundus.cli.php'], JLog::ALL, array('com_emundus.cli'));

        $error = false;
        $old_workflows = [];

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query->select('*')
            ->from('#__emundus_campaign_workflow');
        $db->setQuery($query);

        try {
            $old_workflows = $db->loadObjectList();
        } catch (Exception $e) {
            JLog::add('Error trying to get emundus campaign workflow rows', JLog::ERROR, 'com_emundus.cli');
            $error = true;
        }

        if (!$error) {
            $query->clear()
                ->select('jff.group_id, jfl.id')
                ->from('#__fabrik_formgroup AS jff')
                ->leftJoin('#__fabrik_lists AS jfl ON jfl.form_id = jff.form_id')
                ->where('jfl.db_table_name = ' . $db->quote('jos_emundus_campaign_workflow'));

            try {
                $data = $db->loadObject();
                $group_id = $data->group_id;
                $list_id = $data->id;
            } catch (Exception $e) {
                JLog::add('Could not retrieve jos_emundus_campaign_workflow fabrik group and list ids', JLog::ERROR, 'com_emundus.cli');
            }

            if (!empty($group_id) && !empty($list_id)) {
                $query->clear()
                    ->select('id')
                    ->from('#__fabrik_elements')
                    ->where('group_id = ' . $group_id)
                    ->andWhere('name = ' . $db->quote('status'));

                $db->setQuery($query);

                try {
                    $status_element_id = $db->loadResult();
                } catch (Exception $e) {
                    JLog::add('Could not retrieve fabrik element id from name status and group_id ' . $group_id . ' : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.cli');
                }

                if (!empty($status_element_id)) {
                    $params = '{"database_join_display_type":"multilist","join_conn_id":"1","join_db_name":"jos_emundus_setup_status","join_key_column":"step","join_val_column":"value","join_val_column_concat":"","database_join_where_sql":"","database_join_where_access":"1","database_join_where_access_invert":"0","database_join_where_when":"3","databasejoin_where_ajax":"0","databasejoin_where_ajax_default_eval":"","database_join_filter_where_sql":"","database_join_show_please_select":"1","database_join_noselectionvalue":"","database_join_noselectionlabel":"","placeholder":"","databasejoin_popupform":"275","fabrikdatabasejoin_frontend_add":"0","join_popupwidth":"","databasejoin_readonly_link":"0","fabrikdatabasejoin_frontend_select":"0","advanced_behavior":"1","dbjoin_options_per_row":"4","dbjoin_multiselect_max":"0","dbjoin_multilist_size":"6","dbjoin_autocomplete_size":"20","dbjoin_autocomplete_rows":"10","bootstrap_class":"input-large","dabase_join_label_eval":"","join_desc_column":"","dbjoin_autocomplete_how":"contains","clean_concat":"0","show_in_rss_feed":"0","show_label_in_rss_feed":"0","use_as_rss_enclosure":"0","rollover":"","tipseval":"0","tiplocation":"top-left","labelindetails":"0","labelinlist":"0","comment":"","edit_access":"1","edit_access_user":"","view_access":"1","view_access_user":"","list_view_access":"1","encrypt":"0","store_in_db":"1","default_on_copy":"0","can_order":"0","alt_list_heading":"","custom_link":"","custom_link_target":"","custom_link_indetails":"1","use_as_row_class":"0","include_in_list_query":"1","always_render":"0","icon_folder":"0","icon_hovertext":"1","icon_file":"","icon_subdir":"","filter_length":"20","filter_access":"1","full_words_only":"0","filter_required":"0","filter_build_method":"0","filter_groupby":"text","inc_in_adv_search":"1","filter_class":"input-medium","filter_responsive_class":"","tablecss_header_class":"","tablecss_header":"","tablecss_cell_class":"","tablecss_cell":"","sum_on":"0","sum_label":"Sum","sum_access":"8","sum_split":"","avg_on":"0","avg_label":"Average","avg_access":"8","avg_round":"0","avg_split":"","median_on":"0","median_label":"Median","median_access":"8","median_split":"","count_on":"0","count_label":"Count","count_condition":"","count_access":"8","count_split":"","custom_calc_on":"0","custom_calc_label":"Custom","custom_calc_query":"","custom_calc_access":"1","custom_calc_split":"","custom_calc_php":"","notempty-message":[""],"notempty-validation_condition":[""],"tip_text":[""],"icon":[""],"validations":{"plugin":["notempty"],"plugin_published":["1"],"validate_in":["both"],"validation_on":["both"],"validate_hidden":["0"],"must_validate":["0"],"show_icon":["1"]}}';
                    $query->clear()
                        ->update('#__fabrik_elements')
                        ->set('name = ' . $db->quote('entry_status'))
                        ->set('params = ' . $db->quote($params))
                        ->where('id = ' . $status_element_id);

                    $db->setQuery($query);

                    try {
                        $updated = $db->execute();
                    } catch (Exception $e) {
                        $updated = false;
                        JLog::add('Error trying to update element ' . $status_element_id . ' params from group ' . $group_id, JLog::ERROR, 'com_emundus.cli');
                        $update_campaign_workflow['message'] = 'Error trying to update element ' . $status_element_id . ' params from group ' . $group_id;
                    }

                    if ($updated) {
                        $sql = 'CREATE TABLE IF NOT EXISTS `jos_emundus_campaign_workflow_repeat_entry_status` (
                            `id` int NOT NULL AUTO_INCREMENT,
                            `parent_id` int DEFAULT NULL,
                            `entry_status` int DEFAULT NULL,
                            `params` text,
                            PRIMARY KEY (`id`),
                            KEY `fb_parent_fk_parent_id_INDEX` (`parent_id`),
                            KEY `fb_repeat_el_entry_status_INDEX` (`entry_status`))';

                        $db->setQuery($sql);

                        try {
                            $created = $db->execute();
                        } catch (Execption $e) {
                            JLog::add('Error trying to create jos_emundus_campaign_workflow_repeat_entry_status ' . $e->getMessage(), JLog::ERROR, 'com_emundus.cli');
                            $update_campaign_workflow['message'] = 'Error trying to create jos_emundus_campaign_workflow_repeat_entry_status ' . $e->getMessage();
                        }

                        $fields = array(
                            $db->quoteName('list_id') . ' = ' . $list_id,
                            $db->quoteName('join_from_table') . ' = ' . $db->quote('jos_emundus_campaign_workflow'),
                            $db->quoteName('table_join') . ' = ' . $db->quote('jos_emundus_campaign_workflow_repeat_entry_status'),
                            $db->quoteName('table_key') . ' = ' . $db->quote('entry_status'),
                            $db->quoteName('table_join_key') . ' = ' . $db->quote('parent_id'),
                            $db->quoteName('join_type') . ' = ' . $db->quote('left'),
                            $db->quoteName('group_id') . ' = 0',
                            $db->quoteName('params') . ' = ' . $db->quote('{"type":"repeatElement","pk":"`jos_emundus_campaign_workflow_repeat_entry_status`.`id`"}')
                        );

                        $query->clear()
                            ->update('#__fabrik_joins')
                            ->set($fields)
                            ->where('element_id = ' . $status_element_id);

                        $db->setQuery($query);
                        try {
                            $joined = $db->execute();
                        } catch (Execption $e) {
                            JLog::add('Cannot update fabrik element join with new table jos_emundus_campaign_workflow_repeat_entry_status ' . $e->getMessage(), JLog::ERROR, 'com_emundus.cli');
                            $update_campaign_workflow['message'] = 'Cannot update fabrik element join with new table jos_emundus_campaign_workflow_repeat_entry_status ' . $e->getMessage();
                        }
                    }
                }

                $query->clear()
                    ->select('id')
                    ->from('#__fabrik_elements')
                    ->where('group_id = ' . $group_id)
                    ->andWhere('name = ' . $db->quote('campaign'));

                $db->setQuery($query);
                $campaign_element_id = $db->loadResult();

                if (!empty($campaign_element_id)) {
                    $params = '{"database_join_display_type":"multilist","join_conn_id":"1","join_db_name":"jos_emundus_setup_campaigns","join_key_column":"id","join_val_column":"label","join_val_column_concat":"","database_join_where_sql":"","database_join_where_access":"1","database_join_where_access_invert":"0","database_join_where_when":"3","databasejoin_where_ajax":"0","databasejoin_where_ajax_default_eval":"","database_join_filter_where_sql":"","database_join_show_please_select":"1","database_join_noselectionvalue":"","database_join_noselectionlabel":"","placeholder":"","databasejoin_popupform":"103","fabrikdatabasejoin_frontend_add":"0","join_popupwidth":"","databasejoin_readonly_link":"0","fabrikdatabasejoin_frontend_select":"0","advanced_behavior":"1","dbjoin_options_per_row":"4","dbjoin_multiselect_max":"0","dbjoin_multilist_size":"6","dbjoin_autocomplete_size":"20","dbjoin_autocomplete_rows":"10","bootstrap_class":"input-large","dabase_join_label_eval":"","join_desc_column":"","dbjoin_autocomplete_how":"contains","clean_concat":"0","show_in_rss_feed":"0","show_label_in_rss_feed":"0","use_as_rss_enclosure":"0","rollover":"","tipseval":"0","tiplocation":"top-left","labelindetails":"0","labelinlist":"0","comment":"","edit_access":"1","edit_access_user":"","view_access":"1","view_access_user":"","list_view_access":"1","encrypt":"0","store_in_db":"1","default_on_copy":"0","can_order":"0","alt_list_heading":"","custom_link":"","custom_link_target":"","custom_link_indetails":"1","use_as_row_class":"0","include_in_list_query":"1","always_render":"0","icon_folder":"0","icon_hovertext":"1","icon_file":"","icon_subdir":"","filter_length":"20","filter_access":"1","full_words_only":"0","filter_required":"0","filter_build_method":"0","filter_groupby":"text","inc_in_adv_search":"1","filter_class":"input-medium","filter_responsive_class":"","tablecss_header_class":"","tablecss_header":"","tablecss_cell_class":"","tablecss_cell":"","sum_on":"0","sum_label":"Sum","sum_access":"8","sum_split":"","avg_on":"0","avg_label":"Average","avg_access":"8","avg_round":"0","avg_split":"","median_on":"0","median_label":"Median","median_access":"8","median_split":"","count_on":"0","count_label":"Count","count_condition":"","count_access":"8","count_split":"","custom_calc_on":"0","custom_calc_label":"Custom","custom_calc_query":"","custom_calc_access":"1","custom_calc_split":"","custom_calc_php":"","notempty-message":[""],"notempty-validation_condition":[""],"tip_text":[""],"icon":[""],"validations":{"plugin":["notempty"],"plugin_published":["1"],"validate_in":["both"],"validation_on":["both"],"validate_hidden":["0"],"must_validate":["0"],"show_icon":["1"]}}';
                    $query->clear()
                        ->update('#__fabrik_elements')
                        ->set('params = ' . $db->quote($params))
                        ->where('id = ' . $campaign_element_id);

                    $db->setQuery($query);

                    try {
                        $updated = $db->execute();
                    } catch (Exception $e) {
                        $updated = false;
                        JLog::add('Error trying to update campaign element params from group ' . $group_id . ' : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.cli');
                        $update_campaign_workflow['message'] = 'Error trying to update campaign element params from group ' . $group_id . ' : ' . $e->getMessage();
                    }

                    if ($updated) {
                        $sql = 'CREATE TABLE IF NOT EXISTS `jos_emundus_campaign_workflow_repeat_campaign` (
                            `id` int NOT NULL AUTO_INCREMENT,
                            `parent_id` int DEFAULT NULL,
                            `campaign` int DEFAULT NULL,
                            `params` text,
                            PRIMARY KEY (`id`),
                            KEY `fb_parent_fk_parent_id_INDEX` (`parent_id`),
                            KEY `fb_repeat_el_entry_status_INDEX` (`campaign`))';

                        $db->setQuery($sql);

                        try {
                            $created = $db->execute();
                        } catch (Execption $e) {
                            JLog::add('Error trying to create jos_emundus_campaign_workflow_repeat_campaign ' . $e->getMessage(), JLog::ERROR, 'com_emundus.cli');
                            $update_campaign_workflow['message'] = 'Error trying to create jos_emundus_campaign_workflow_repeat_campaign ' . $e->getMessage();
                        }

                        $fields = array(
                            $db->quoteName('list_id') . ' = ' . $list_id,
                            $db->quoteName('join_from_table') . ' = ' . $db->quote('jos_emundus_campaign_workflow'),
                            $db->quoteName('table_join') . ' = ' . $db->quote('jos_emundus_campaign_workflow_repeat_campaign'),
                            $db->quoteName('table_key') . ' = ' . $db->quote('campaign'),
                            $db->quoteName('table_join_key') . ' = ' . $db->quote('parent_id'),
                            $db->quoteName('join_type') . ' = ' . $db->quote('left'),
                            $db->quoteName('group_id') . ' = 0',
                            $db->quoteName('params') . ' = ' . $db->quote('{"type":"repeatElement","pk":"`jos_emundus_campaign_workflow_repeat_campaign`.`id`"}')
                        );

                        $query->clear()
                            ->update('#__fabrik_joins')
                            ->set($fields)
                            ->where('element_id = ' . $campaign_element_id);

                        $db->setQuery($query);
                        try {
                            $joined = $db->execute();
                        } catch (Execption $e) {
                            JLog::add('Cannot update fabrik element join with new table jos_emundus_campaign_workflow_repeat_campaign ' . $e->getMessage(), JLog::ERROR, 'com_emundus.cli');
                            $update_campaign_workflow['message'] = 'Cannot update fabrik element join with new table jos_emundus_campaign_workflow_repeat_campaign ' . $e->getMessage();
                        }
                    } else {
                        JLog::add('campaign element from fabrik group ' . $group_id . ' has not been updated', JLog::WARNING, 'com_emundus.cli');
                        $update_campaign_workflow['message'] = 'campaign element from fabrik group ' . $group_id . ' has not been updated';
                    }
                }

                $params = '{"database_join_display_type":"dropdown","join_conn_id":"1","join_db_name":"jos_emundus_setup_status","join_key_column":"step","join_val_column":"value","join_val_column_concat":"","database_join_where_sql":"","database_join_where_access":"1","database_join_where_access_invert":"0","database_join_where_when":"3","databasejoin_where_ajax":"0","databasejoin_where_ajax_default_eval":"","database_join_filter_where_sql":"","database_join_show_please_select":"1","database_join_noselectionvalue":"","database_join_noselectionlabel":"","placeholder":"","databasejoin_popupform":"275","fabrikdatabasejoin_frontend_add":"0","join_popupwidth":"","databasejoin_readonly_link":"0","fabrikdatabasejoin_frontend_select":"0","advanced_behavior":"1","dbjoin_options_per_row":"4","dbjoin_multiselect_max":"0","dbjoin_multilist_size":"6","dbjoin_autocomplete_size":"20","dbjoin_autocomplete_rows":"10","bootstrap_class":"input-large","dabase_join_label_eval":"","join_desc_column":"","dbjoin_autocomplete_how":"contains","clean_concat":"0","show_in_rss_feed":"0","show_label_in_rss_feed":"0","use_as_rss_enclosure":"0","rollover":"","tipseval":"0","tiplocation":"top-left","labelindetails":"0","labelinlist":"0","comment":"","edit_access":"1","edit_access_user":"","view_access":"1","view_access_user":"","list_view_access":"1","encrypt":"0","store_in_db":"1","default_on_copy":"0","can_order":"0","alt_list_heading":"","custom_link":"","custom_link_target":"","custom_link_indetails":"1","use_as_row_class":"0","include_in_list_query":"1","always_render":"0","icon_folder":"0","icon_hovertext":"1","icon_file":"","icon_subdir":"","filter_length":"20","filter_access":"1","full_words_only":"0","filter_required":"0","filter_build_method":"0","filter_groupby":"text","inc_in_adv_search":"1","filter_class":"input-medium","filter_responsive_class":"","tablecss_header_class":"","tablecss_header":"","tablecss_cell_class":"","tablecss_cell":"","sum_on":"0","sum_label":"Sum","sum_access":"8","sum_split":"","avg_on":"0","avg_label":"Average","avg_access":"8","avg_round":"0","avg_split":"","median_on":"0","median_label":"Median","median_access":"8","median_split":"","count_on":"0","count_label":"Count","count_condition":"","count_access":"8","count_split":"","custom_calc_on":"0","custom_calc_label":"Custom","custom_calc_query":"","custom_calc_access":"1","custom_calc_split":"","custom_calc_php":"","notempty-message":[""],"notempty-validation_condition":[""],"tip_text":[""],"icon":[""],"validations":{"plugin":["notempty"],"plugin_published":["1"],"validate_in":["both"],"validation_on":["both"],"validate_hidden":["0"],"must_validate":["0"],"show_icon":["1"]}}';
                $sql = 'INSERT INTO jos_fabrik_elements (name, group_id, plugin, label, checked_out, checked_out_time, created, created_by, created_by_alias, modified, modified_by, width, height, `default`, hidden, eval, ordering, show_in_list_summary, filter_type, filter_exact_match, published, link_to_detail, primary_key, auto_increment, access, use_in_page_title, parent_id, params) VALUES ("output_status",' . $group_id . ', "databasejoin", "Statut de sortie", DEFAULT, NOW(), NOW(), 62, "admin", NOW(), 62, 50, 6, " ", 0, 0, 9, 1, null, 0, 1, 0, 0, 0, 1, 0, 0, \'' . $params . '\')';
                $db->setQuery($sql);

                try {
                    $output_status_inserted = $db->execute();
                } catch (Exception $e) {
                    $output_status_inserted = false;
                    JLog::add('Error trying to insert output_status element in jos_fabrik_elements ' . $e->getMessage(), JLog::ERROR, 'com_emundus.cli');
                    $update_campaign_workflow['message'] = 'Error trying to insert output_status element in jos_fabrik_elements ' . $e->getMessage();
                }

                if ($output_status_inserted) {
                    $every_alter_succeed = true;
                    // Add output status
                    $db->setQuery("SHOW COLUMNS FROM `jos_emundus_campaign_workflow` LIKE 'output_status'");
                    try {
                        $output_status = $db->loadObject();

                        if (empty($output_status->Field)) {
                            $db->setQuery("ALTER TABLE jos_emundus_campaign_workflow ADD output_status int null;");
                            $altered = $db->execute();

                            if (!$altered) {
                                $update_campaign_workflow['message'] = 'output_status column has not been added.';
                                $every_alter_succeed = false;
                            }
                        }
                    } catch (Exception $e) {
                        JLog::add('Error on output_status creation ' . $e->getMessage(),  JLog::ERROR, 'com_emundus.cli');
                        $update_campaign_workflow['message'] = 'Error on output_status creation ' . $e->getMessage();
                        $every_alter_succeed = false;
                    }

                    // change status column to entry_status
                    $db->setQuery("SHOW COLUMNS FROM `jos_emundus_campaign_workflow` LIKE 'status'");
                    try {
                        $status = $db->loadObject();

                        if (!empty($status->Field)) {
                            $db->setQuery("ALTER TABLE jos_emundus_campaign_workflow CHANGE status entry_status int null");
                            $altered = $db->execute();

                            if (!$altered) {
                                $update_campaign_workflow['message'] = 'Cannot change jos_emundus_campaign_workflow status column to entry_status';
                                $every_alter_succeed = false;
                            }
                        }
                    } catch (Exception $e) {
                        JLog::add('Error on status column change ' . $e->getMessage(),  JLog::ERROR, 'com_emundus.cli');
                        $update_campaign_workflow['message'] = 'Error on status column ' . $e->getMessage();
                        $every_alter_succeed = false;
                    }

                    if ($every_alter_succeed) {
                        if (!empty(!empty($old_workflows))) {

                            // before deleting, backup workflows
                            JLog::add('JSON Save values : ' . json_encode($old_workflows), JLog::INFO, 'com_emundus.cli');
                            $backup_file = fopen('libraries/emundus/custom/campaign_workflow_rows.json', 'w');
                            if ($backup_file) {
                                fwrite($backup_file, json_encode($old_workflows));
                                fclose($backup_file);

                                $query->clear()
                                    ->update('#__emundus_campaign_workflow')
                                    ->set('campaign = NULL')
                                    ->set('entry_status = NULL');

                                $db->setQuery($query);

                                try {
                                    $update = $db->execute();
                                } catch (Exception $e) {
                                    $update = false;
                                    JLog::add('Failed to set null values on campaign and entry_status columns ' . $e->getMessage(), JLog::ERROR, 'com_emundus');
                                    $update_campaign_workflow['message'] = 'Failed to set null values on campaign and entry_status columns ' . $e->getMessage();
                                }

                                $old_workflows_reinserted = true;
                                foreach ($old_workflows as $workflow) {
                                    if (!empty($workflow->id)) {
                                        $query->clear()
                                            ->insert('#__emundus_campaign_workflow_repeat_campaign')
                                            ->columns($db->quoteName(['parent_id', 'campaign']))
                                            ->values($workflow->id . ', ' . $workflow->campaign);
                                        $db->setQuery($query);

                                        try {
                                            $repeat_campaign_inserted = $db->execute();
                                        } catch (Exception $e) {
                                            $repeat_campaign_inserted = false;
                                            JLog::add('Failed to join new row in jos_emundus_campaign_workflow_repeat_campaign. ' . $e->getMessage(), JLog::ERROR, 'com_emundus.cli');
                                            $update_campaign_workflow['message'] = 'Failed to join new row in jos_emundus_campaign_workflow_repeat_campaign. ' . $e->getMessage();
                                        }

                                        $query->clear()
                                            ->insert('#__emundus_campaign_workflow_repeat_entry_status')
                                            ->columns($db->quoteName(['parent_id', 'entry_status']))
                                            ->values($workflow->id . ', ' . $workflow->status);

                                        $db->setQuery($query);

                                        try {
                                            $repeat_status_inserted = $db->execute();
                                        } catch (Exception $e) {
                                            $repeat_status_inserted = false;
                                            JLog::add('Failed to join new row in jos_emundus_campaign_workflow_repeat_entry_status. ' . $e->getMessage(), JLog::ERROR, 'com_emundus.cli');
                                            $update_campaign_workflow['message'] = 'Failed to join new row in jos_emundus_campaign_workflow_repeat_entry_status. ' . $e->getMessage();
                                        }

                                        if (!$repeat_status_inserted || !$repeat_campaign_inserted) {
                                            $old_workflows_reinserted = false;
                                        }
                                    }
                                }

                                $update_campaign_workflow['status'] = $old_workflows_reinserted;
                            } else {
                                JLog::add('Unable to save old workflows to backup file, stop deletion', JLog::ERROR, 'com_emundus.cli');
                                $update_campaign_workflow['message'] = 'Unable to save old workflows to backup file, stop update';
                            }
                        } else {
                            $update_campaign_workflow['status'] = true;
                        }
                    }
                } else {
                    JLog::add('output_status element in jos_fabrik_elements has not been inserted', JLog::WARNING, 'com_emundus.cli');
                    $update_campaign_workflow['message'] = 'output_status element in jos_fabrik_elements has not been inserted';
                }
            } else {
                JLog::add('Did not find group_id nor list_id', JLog::WARNING, 'com_emundus.cli');
                $update_campaign_workflow['message'] = 'Did not find group_id nor list_id';
            }
        }

        $state_msg =  $update_campaign_workflow['status'] ? "\033[32mSUCCESS\033[0m" : "\033[31mFAILED\033[0m";
        echo "\n-> Finish update jos_emundus_campaign_workflow_table [$state_msg]";

        return $update_campaign_workflow;
    }

    /**
     * @param $params
     * @param $parent_id
     * @param $published
     *
     * Params available : menutype,title,alias,path,type,link,component_id
     *
     * @return array
     *
     * @since version 1.33.0
     */
    public static function addJoomlaMenu($params, $parent_id = 1, $published = 1) {
        $result = ['status' => false, 'message' => '', 'id' => 0];
        $menu_table = JTableNested::getInstance('Menu');

        if(empty($params['menutype'])){
            $result['message'] = 'INSERTING JOOMLA MENU : Please pass a menutype.';
            return $result;
        }
        if(empty($params['title'])){
            $result['message'] = 'INSERTING JOOMLA MENU : Please indicate a title.';
            return $result;
        }

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $alias = $params['alias'] ?: $params['menutype'] . '-' . str_replace(' ','-',strtolower($params['title']));

        $query->clear()
            ->select('id')
            ->from($db->quoteName('#__menu'))
            ->where($db->quoteName('alias') . ' = ' . $db->quote($alias))
            ->andWhere($db->quoteName('menutype') . ' = ' . $db->quote($params['menutype']));
        $db->setQuery($query);
        $is_existing = $db->loadResult();

        if(empty($is_existing)) {
            if (empty($params['type']) || $params['type'] == 'url') {
                $default_params = [
                    'menu-anchor_title' => '',
                    'menu-anchor_css' => '',
                    'menu-anchor_rel' => '',
                    'menu_image_css' => '',
                    'menu_text' => 1,
                    'menu_show' => 1
                ];
                $params['params'] = array_merge($default_params, $params['params']);
            }

            $menu_data = array(
                'menutype' => $params['menutype'],
                'title' => $params['title'],
                'alias' => $alias,
                'path' => $params['path'] ?: $alias,
                'type' => $params['type'] ?: 'url',
                'link' => $params['link'] ?: '#',
                'component_id' => $params['component_id'] ?: 0,
                'language' => '*',
                'published' => $published,
                'params' => json_encode($params['params'])
            );

            $menu_table->setLocation($parent_id, 'last-child');

            if (!$menu_table->save($menu_data)) {
                $result['message'] = 'INSERTING JOOMLA MENU : Error at saving menu.';
                return $result;
            }
            $result['id'] = $menu_table->id;
        } else {
            $result['id'] = $is_existing;
        }

        $result['status'] = true;
        return $result;
    }

    public static function addFabrikForm($datas,$params = [], $published = 1) {
        $result = ['status' => false, 'message' => '', 'id' => 0];

        if(empty($datas['label'])){
            $result['message'] = 'INSERTING FABRIK FORM : Please indicate a label.';
            return $result;
        }

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->select('id')
                ->from($db->quoteName('#__fabrik_forms'))
                ->where($db->quoteName('label') . ' LIKE ' . $db->quote($datas['label']));
            $db->setQuery($query);
            $is_existing = $db->loadResult();

            if(!$is_existing) {
                require_once(JPATH_SITE . '/components/com_emundus/helpers/fabrik.php');

                $default_params = EmundusHelperFabrik::prepareFormParams(false);
                $params = array_merge($default_params, $params);

                $publish_up = new DateTime();
                $publish_up->modify('-1 day');

                $inserting_datas = [
                    'label' => $datas['label'],
                    'record_in_database' => $datas['record_in_database'] ?: 1,
                    'error' => $datas['error'] ?: 'FORM_ERROR',
                    'intro' => $datas['intro'] ?: '',
                    'created' => date('Y-m-d H:i:s'),
                    'created_by' => 62,
                    'created_by_alias' => 'admin',
                    'modified' => date('Y-m-d H:i:s'),
                    'modified_by' => 0,
                    'checked_out' => 0,
                    'checked_out_time' => date('Y-m-d H:i:s'),
                    'published' => $published,
                    'publish_up' => $publish_up->format('Y-m-d H:i:s'),
                    'publish_down' => '2099-01-01 00:00:00',
                    'reset_button_label' => $datas['reset_button_label'] ?: 'RESET',
                    'submit_button_label' => $datas['submit_button_label'] ?: 'SAVE_CONTINUE',
                    'form_template' => $datas['form_template'] ?: 'bootstrap',
                    'view_only_template' => $datas['view_only_template'] ?: 'bootstrap',
                    'params' => json_encode($params),
                ];

                $query->clear()
                    ->insert($db->quoteName('#__fabrik_forms'))
                    ->columns($db->quoteName(array_keys($inserting_datas)))
                    ->values(implode(',',$db->quote(array_values($inserting_datas))));
                $db->setQuery($query);
                $db->execute();

                $result['id'] = $db->insertid();
            } else {
                $result['id'] = $is_existing;
            }
        } catch (Exception $e) {
            $result['message'] = 'INSERTING FABRIK FORM : Error : ' . $e->getMessage();
            return $result;
        }

        $result['status'] = true;
        return $result;
    }

    public static function addFabrikList($datas,$params = [], $published = 1) {
        $result = ['status' => false, 'message' => '', 'id' => 0];

        if(empty($datas['label'])){
            $result['message'] = 'INSERTING FABRIK LIST : Please indicate a label.';
            return $result;
        }
        if(empty($datas['form_id'])){
            $result['message'] = 'INSERTING FABRIK LIST : Please pass a form_id.';
            return $result;
        }
        if(empty($datas['db_table_name'])){
            $result['message'] = 'INSERTING FABRIK LIST : Please indicate a table name.';
            return $result;
        }

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('id')
            ->from($db->quoteName('#__fabrik_lists'))
            ->where($db->quoteName('label') . ' LIKE ' . $db->quote($datas['label']))
            ->andWhere($db->quoteName('db_table_name') . ' LIKE ' . $db->quote($datas['db_table_name']));
        $db->setQuery($query);
        $is_existing = $db->loadResult();

        if(!$is_existing) {
            require_once(JPATH_SITE . '/components/com_emundus/helpers/fabrik.php');

            $default_params = EmundusHelperFabrik::prepareListParams();
            $params = array_merge($default_params, $params);

            try {
                $publish_up = new DateTime();
                $publish_up->modify('-1 day');

                $inserting_datas = [
                    'label' => $datas['label'],
                    'introduction' => $datas['introduction'] ?: '',
                    'form_id' => $datas['form_id'],
                    'db_table_name' => $datas['db_table_name'],
                    'db_primary_key' => $datas['db_primary_key'] ?: $datas['db_table_name'] . '.id',
                    'auto_inc' => $datas['auto_inc'] ?: 1,
                    'connection_id' => $datas['connection_id'] ?: 1,
                    'created' => date('Y-m-d H:i:s'),
                    'created_by' => 62,
                    'created_by_alias' => 'admin',
                    'modified' => date('Y-m-d H:i:s'),
                    'modified_by' => 0,
                    'checked_out' => 0,
                    'checked_out_time' => date('Y-m-d H:i:s'),
                    'published' => $published,
                    'publish_up' => $publish_up->format('Y-m-d H:i:s'),
                    'publish_down' => '2099-01-01 00:00:00',
                    'access' => $datas['access'] ?: 1,
                    'hits' => 0,
                    'rows_per_page' => $datas['rows_per_page'] ?: 10,
                    'template' => $datas['template'] ?: 'bootstrap',
                    'order_by' => $datas['order_by'] ?: '[]',
                    'order_dir' => $datas['order_dir'] ?: '[]',
                    'filter_action' => $datas['filter_action'] ?: 'onchange',
                    'group_by' => $datas['group_by'] ?: '',
                    'params' => json_encode($params)
                ];

                $query->clear()
                    ->insert($db->quoteName('#__fabrik_lists'))
                    ->columns($db->quoteName(array_keys($inserting_datas)))
                    ->values(implode(',',$db->quote(array_values($inserting_datas))));
                $db->setQuery($query);
                $db->execute();

                $result['id'] = $db->insertid();
            } catch (Exception $e) {
                $result['message'] = 'INSERTING FABRIK LIST : Error : ' . $e->getMessage();
                return $result;
            }
        } else {
            $result['id'] = $is_existing;
        }

        $result['status'] = true;
        return $result;
    }

    public static function addFabrikGroup($datas,$params = [], $published = 1) {
        $result = ['status' => false, 'message' => '', 'id' => 0];

        if(empty($datas['name'])){
            $result['message'] = 'INSERTING FABRIK GROUP : Please indicate a name.';
            return $result;
        }

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('id')
            ->from($db->quoteName('#__fabrik_groups'))
            ->where($db->quoteName('name') . ' LIKE ' . $db->quote($datas['name']));
        $db->setQuery($query);
        $is_existing = $db->loadResult();

        if(!$is_existing) {
            require_once(JPATH_SITE . '/components/com_emundus/helpers/fabrik.php');

            $default_params = EmundusHelperFabrik::prepareGroupParams();
            $params = array_merge($default_params, $params);

            try {
                $inserting_datas = [
                    'name' => $datas['name'],
                    'css' => $datas['css'] ?: '',
                    'label' => $datas['label'] ?: $datas['name'],
                    'created' => date('Y-m-d H:i:s'),
                    'created_by' => 62,
                    'created_by_alias' => 'admin',
                    'modified' => date('Y-m-d H:i:s'),
                    'modified_by' => 0,
                    'checked_out' => 0,
                    'checked_out_time' => date('Y-m-d H:i:s'),
                    'published' => $published,
                    'is_join' => $datas['is_join'] ?: 0,
                    'params' => json_encode($params)
                ];

                $query->clear()
                    ->insert($db->quoteName('#__fabrik_groups'))
                    ->columns($db->quoteName(array_keys($inserting_datas)))
                    ->values(implode(',',$db->quote(array_values($inserting_datas))));
                $db->setQuery($query);
                $db->execute();

                $result['id'] = $db->insertid();
            } catch (Exception $e) {
                $result['message'] = 'INSERTING FABRIK GROUP : Error : ' . $e->getMessage();
                return $result;
            }
        } else {
            $result['id'] = $is_existing;
        }

        $result['status'] = true;
        return $result;
    }

    public static function joinFormGroup($form_id,$groups_id) {
        $result = ['status' => false, 'message' => ''];

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            foreach ($groups_id as $group){
                $query->clear()
                    ->select('id')
                    ->from($db->quoteName('#__fabrik_formgroup'))
                    ->where($db->quoteName('form_id') . ' = ' . $form_id)
                    ->andWhere($db->quoteName('group_id') . ' = ' . $group);
                $db->setQuery($query);
                $is_existing = $db->loadResult();

                if(!$is_existing){
                    $query->clear()
                        ->insert($db->quoteName('#__fabrik_formgroup'))
                        ->set($db->quoteName('form_id') . ' = ' . $db->quote($form_id))
                        ->set($db->quoteName('group_id') . ' = ' . $db->quote($group));
                    $db->setQuery($query);
                    $db->execute();
                }
            }
        } catch (Exception $e) {
            $result['message'] = 'JOIN FABRIK FORM WITH GROUPS : Error : ' . $e->getMessage();
            return $result;
        }

        $result['status'] = true;
        return $result;
    }

    public static function addFabrikJoin($datas,$params) {
        $result = ['status' => false, 'message' => ''];

        if(empty($datas['table_join'])){
            $result['message'] = 'INSERTING FABRIK JOIN : Please indicate a table_join.';
            return $result;
        }

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $inserting_datas = [
                'list_id' => $datas['list_id'] ?: 0,
                'element_id' => $datas['element_id'] ?: 0,
                'join_from_table' => $datas['join_from_table'] ?: '',
                'table_join' => $datas['table_join'],
                'table_key' => $datas['table_key'],
                'table_join_key' => $datas['table_join_key'] ?: 'id',
                'join_type' => $datas['join_type'] ?: 'left',
                'group_id' => $datas['group_id'] ?: 0,
                'params' => json_encode($params)
            ];

            $query->clear()
                ->insert($db->quoteName('#__fabrik_joins'))
                ->columns($db->quoteName(array_keys($inserting_datas)))
                ->values(implode(',',$db->quote(array_values($inserting_datas))));
            $db->setQuery($query);
            $db->execute();
        } catch (Exception $e) {
            $result['message'] = 'INSERTING FABRIK JOIN : Error : ' . $e->getMessage();
            return $result;
        }

        $result['status'] = true;
        return $result;
    }

    public static function addColumn($table,$name,$type = 'VARCHAR',$length = 255,$null = 1){
        $result = ['status' => false, 'message' => ''];

        if(empty($table)){
            $result['message'] = 'ADDING COLUMN : Please refer a database table.';
            return $result;
        }
        if(empty($name)){
            $result['message'] = 'ADDING COLUMN : Please refer a column name.';
            return $result;
        }

        $db = JFactory::getDbo();

        $column_existing = $db->setQuery('SHOW COLUMNS FROM ' . $table . ' WHERE ' . $db->quoteName('Field') . ' = ' . $db->quote($name))->loadResult();

        if(empty($column_existing)){
            $null_query = 'NULL';
            if($null == 0){
                $null_query = 'NOT NULL';
            }
            try {
                $query = 'ALTER TABLE ' . $table . ' ADD COLUMN ' . $db->quoteName($name) . ' ' . $type . '(' . $length . ') ' . $null_query;
                $db->setQuery($query);
                $result['status'] = $db->execute();
            } catch (Exception $e) {
                $result['message'] = 'ADDING COLUMN : Error : ' . $e->getMessage();
                return $result;
            }
        }

        return $result;
    }
}
