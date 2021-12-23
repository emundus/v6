<?php
/**
 * @package     Joomla
 * @subpackage  eMundus
 * @copyright   Copyright (C) 2015 emundus.fr. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.model' );

class EmundusModelTranslations extends JModelList
{
    var $_db = null;

    /**
     * Constructor
     *
     * @since 1.5
     */
    function __construct()
    {
        parent::__construct();
        $this->_db = JFactory::getDBO();

        jimport('joomla.log.log');
        JLog::addLogger(['text_file' => 'com_emundus.translations.php'], JLog::ERROR);
    }

    /**
     * Get our translations definitions
     *
     * @return array
     *
     * @since version 1.28.0
     */
    public function getTranslationsObject(){
        $dir = JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'contentelements';

        $objects = array();

        if (file_exists($dir) && is_dir($dir) ) {
            $scan_arr = scandir($dir);
            $files_arr = array_diff($scan_arr, array('.','..') );
            foreach ($files_arr as $file) {
                $string_datas = '';
                $fp = fopen($dir . '/' . $file, "r");
                while ( $ligneXML = fgets($fp, 1024)) {
                    // Affichage "brut" de la ligne convertie en HTML
                    $string_datas .= $ligneXML;
                }
                $data = simplexml_load_string($string_datas);
                $object = new stdClass;
                $object->name = $data->name->__toString();
                $object->description = $data->description->__toString();
                $object->table = $data->reference->table;
                $objects[] = $object;
            }
        }

        return $objects;
    }

    /**
     * Get translations with many filters
     *
     * @param $type
     * @param $lang_code
     * @param $search
     * @param $location
     * @param $reference_table
     * @param $reference_id
     * @param $tag
     *
     * @return array|mixed
     *
     * @since version 1.28.0
     */
    public function getTranslations($type = 'override',$lang_code = '*',$search = '',$location = '',$reference_table = '',$reference_id = 0,$tag = ''){
        $query = $this->_db->getQuery(true);

        $query->select('*')
            ->from($this->_db->quoteName('#__emundus_setup_languages'))
            ->where($this->_db->quoteName('type') . ' LIKE ' . $this->_db->quote('%' . $type . '%'));


        if($lang_code !== '*' && !empty($lang_code)){
            $query->where($this->_db->quoteName('lang_code') . ' = ' . $this->_db->quote($lang_code));
        }
        if(!empty($search)){
            $query->where($this->_db->quoteName('override') . ' LIKE ' . $this->_db->quote('%' . $search . '%'));
        }
        if(!empty($location)){
            $query->where($this->_db->quoteName('location') . ' = ' . $this->_db->quote($location));
        }
        if(!empty($reference_table)){
            $query->where($this->_db->quoteName('reference_table') . ' LIKE ' . $this->_db->quote($reference_table));
        }
        if(!empty($reference_id)){
            $query->where($this->_db->quoteName('reference_id') . ' LIKE ' . $this->_db->quote($reference_id));
        }
        if(!empty($tag)){
            $query->where($this->_db->quoteName('tag') . ' LIKE ' . $this->_db->quote($tag));
        }

        $this->_db->setQuery($query);
        return $this->_db->loadObjectList();
    }

    /**
     * Create a new translation in base and insert it in override file
     *
     * @param $tag
     * @param $override
     * @param $lang_code
     * @param $location
     * @param $type
     * @param $reference_table
     * @param $reference_id
     *
     * @return bool|void
     *
     * @since version
     */
    public function insertTranslation($tag,$override,$lang_code,$location = '',$type='override',$reference_table = '',$reference_id = 0){
        $query = $this->_db->getQuery(true);
        $user = JFactory::getUser();

        try{
            if(empty($location)){
                $location = $lang_code . '.override.ini';
            }

            $columns = ['tag','lang_code','override','original_text','original_md5','override_md5','location','type','reference_id','reference_table','published','created_by','created_date','modified_by','modified_date'];
            $values = [
                $this->_db->quote($tag),
                $this->_db->quote($lang_code),
                $this->_db->quote($override),
                $this->_db->quote($override),
                $this->_db->quote(md5($override)),
                $this->_db->quote(md5($override)),
                $this->_db->quote($location),
                $this->_db->quote($type),
                $reference_id,
                $this->_db->quote($reference_table),
                1,
                $user->id,
                time(),
                $user->id,
                time()
            ];

            $query->insert($this->_db->quoteName('#__emundus_setup_languages'))
                ->columns($this->_db->quoteName($columns))
                ->values(implode(',',$values));
            $this->_db->setQuery($query);

            if($this->_db->execute()){
                $override_file = JPATH_BASE . '/language/overrides/' . $location;
                if (file_exists($override_file)) {
                    $parsed_file = JLanguageHelper::parseIniFile($override_file);
                    $parsed_file[$tag] = $override;
                    return JLanguageHelper::saveToIniFile($override_file, $parsed_file);
                }
            } else {
                return false;
            }
        } catch(Exception $e){
            JLog::add('Problem when try to insert translation into file ' . $location . ' with error : ' . $e->getMessage(),JLog::ERROR, 'com_emundus.translations');
            return false;
        }
    }

    /**
     * Update a translation
     * If the translation is not override (ex. com_emundus) we insert it in override file
     *
     * @param $tag
     * @param $override
     * @param $lang_code
     * @param $type
     *
     * @return bool|void
     *
     * @since version
     */
    public function updateTranslation($tag,$override,$lang_code,$type = 'override'){
        $query = $this->_db->getQuery(true);
        $user = JFactory::getUser();

        $location = $lang_code . '.override.ini';

        try {
            if($type === 'override') {
                $query->update('#__emundus_setup_languages')
                    ->set($this->_db->quoteName('override') . ' = ' . $this->_db->quote($override))
                    ->set($this->_db->quoteName('override_md5') . ' = ' . $this->_db->quote(md5($override)))
                    ->set($this->_db->quoteName('modified_by') . ' = ' . $this->_db->quote($user->id))
                    ->set($this->_db->quoteName('modified_date') . ' = ' . time())
                    ->where($this->_db->quoteName('tag') . ' = ' . $this->_db->quote($tag))
                    ->andWhere($this->_db->quoteName('lang_code') . ' = ' . $this->_db->quote($lang_code))
                    ->andWhere($this->_db->quoteName('type') . ' = ' . $this->_db->quote($type));
                $this->_db->setQuery($query);

                if($this->_db->execute()){
                    $override_file = JPATH_BASE . '/language/overrides/' . $location;
                    if (file_exists($override_file)) {
                        $parsed_file = JLanguageHelper::parseIniFile($override_file);
                        $parsed_file[$tag] = $override;
                        return JLanguageHelper::saveToIniFile($override_file, $parsed_file);
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            } else {
                $existing_translation = $this->getTranslations('override',$lang_code,'','','','',$tag);
                if(empty($existing_translation)) {
                    return $this->insertTranslation($tag, $override, $lang_code);
                } else {
                    return $this->updateTranslation($tag,$override,$lang_code);
                }
            }
        } catch(Exception $e){
            JLog::add('Problem when try to update translation ' . $tag . ' into file ' . $location . ' with error : ' . $e->getMessage(),JLog::ERROR, 'com_emundus.translations');
            return false;
        }
    }

    /**
     * Delete a translation in base and then remove it from overrides files
     *
     * @param $tag
     * @param $lang_code
     * @param $reference_table
     * @param $reference_id
     *
     * @return false|void
     *
     * @since version
     */
    public function deleteTranslation($tag = '',$lang_code = '*',$reference_table = '',$reference_id = 0){
        $query = $this->_db->getQuery(true);

        try {
            $query->delete($this->_db->quoteName('#__emundus_setup_languages'))
                ->where($this->_db->quoteName('type') . ' = ' . $this->_db->quote('override'));

            if(!empty($tag)){
                $query->where($this->_db->quoteName('tag') . ' = ' . $this->_db->quote($tag));
            }
            if($lang_code !== '*' && !empty($lang_code)){
                $query->where($this->_db->quoteName('lang_code') . ' = ' . $this->_db->quote($lang_code));
            }
            if(!empty($reference_table)){
                $query->where($this->_db->quoteName('reference_table') . ' LIKE ' . $this->_db->quote($reference_table));
            }
            if(!empty($reference_id)){
                $query->where($this->_db->quoteName('reference_id') . ' LIKE ' . $this->_db->quote($reference_id));
            }
            $this->_db->setQuery($query);
            return $this->_db->execute();
        } catch (Exception $e) {
            JLog::add('Problem when try to delete translation ' . $tag . ' with error : ' . $e->getMessage(),JLog::ERROR, 'com_emundus.translations');
            return false;
        }
    }
}
