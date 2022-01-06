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
        $objects = array();

        include_once(JPATH_BASE . DS . 'administrator' . DS . 'components' . DS . 'com_falang' . DS . "models".DS."ContentElement.php");

        jimport('joomla.filesystem.folder');
        $dir = JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'contentelements/';
        $filesindir = JFolder::files($dir ,".xml");
        if(count($filesindir) > 0)
        {
            foreach($filesindir as $file)
            {
                $object = new stdClass;
                unset($xmlDoc);
                $xmlDoc = new DOMDocument();
                if ($xmlDoc->load($dir . $file)) {
                    $xpath = new DOMXPath($xmlDoc);
                    $tableElement = $xpath->query('//reference/table')->item(0);

                    $contentElement = new ContentElement( $xmlDoc );
                    $object->name = JText::_($xmlDoc->getElementsByTagName('name')->item(0)->textContent);
                    $object->description = JText::_($xmlDoc->getElementsByTagName('description')->item(0)->textContent);
                    $object->table = new stdClass;
                    $object->table->name = trim($tableElement->getAttribute( 'name' ));
                    $object->table->reference = trim($tableElement->getAttribute( 'reference' ));
                    $object->table->label = trim($tableElement->getAttribute( 'label' ));
                    $object->table->filters = trim($tableElement->getAttribute( 'filters' ));
                    $object->table->type = trim($tableElement->getAttribute( 'type' ));
                    $object->fields = $contentElement->getTable();

                    $objects[] = $object;
                }
            }
        }

        return $objects;
    }

    /**
     * Get references datas
     *
     * @param $table
     * @param $reference_id
     * @param $label
     * @param $filters
     * @return array|false|mixed
     *
     * @since version 1.28.0
     */
    public function getDatas($table,$reference_id,$label,$filters){
        $query = $this->_db->getQuery(true);

        try {
            $query->select($this->_db->quoteName($reference_id) . 'as id,' . $this->_db->quoteName($label) . 'as label')
                ->from($this->_db->quoteName('#__' . $table));
            if(!empty($filters)) {
                $filters = explode(',',$filters);
                foreach ($filters as $filter) {
                    $query->where($this->_db->quoteName($filter) . ' = 1');
                }
            }
            $this->_db->setQuery($query);
            return $this->_db->loadObjectList();
        } catch (Exception $e) {
            JLog::add('Problem when try to get datas from table ' . $table . ' with error : ' . $e->getMessage(),JLog::ERROR, 'com_emundus.translations');
            return false;
        }
    }

    public function getChildrens($table,$reference_id,$label){
        $query = $this->_db->getQuery(true);

        if($table == 'fabrik_forms') {
            $forms = array();
            require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'menu.php');
            $h_menu = new EmundusHelperMenu;

            $tableuser = $h_menu->buildMenuQuery($reference_id);
            foreach ($tableuser as $menu){
                $forms[] = $menu->form_id;
            }
        }

        try {
            $query->select('id,' . $this->_db->quoteName($label) . ' as label')
                ->from($this->_db->quoteName('#__' . $table));

            if(isset($forms)){
                $query->where($this->_db->quoteName('id') . ' IN (' . implode(',',$forms) . ')');
            }
            $this->_db->setQuery($query);
            $values = $this->_db->loadObjectList();

            foreach ($values as $key => $value){
                $values[$key]->label = JText::_($value->label);
            }

            return $values;
        } catch (Exception $e) {
            JLog::add('Problem when try to get childrens from table ' . $table . ' with error : ' . $e->getMessage(),JLog::ERROR, 'com_emundus.translations');
            return false;
        }
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

    public function getDefaultLanguage(){
        $query = $this->_db->getQuery(true);

        require_once (JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_languages'.DS.'models'.DS.'installed.php');
        $m_installed = new LanguagesModelInstalled;
        $languages_installed = $m_installed->getData();

        foreach ($languages_installed as $language){
            if($language->published == 1){
                $default = $language->language;
                break;
            }
        }

        try {
            $query->select('lang_code,title_native')
                ->from($this->_db->quoteName('#__languages'))
                ->where($this->_db->quoteName('lang_code') . ' = ' . $this->_db->quote($default));
            $this->_db->setQuery($query);
            return $this->_db->loadObject();
        } catch (Exception $e) {
            JLog::add('Problem when try to fet default language with error : ' . $e->getMessage(),JLog::ERROR, 'com_emundus.translations');
            return false;
        }
    }

    public function getAllLanguages(){
        $query = $this->_db->getQuery(true);

        try {
            $query->select('lang_code,title_native,published')
                ->from($this->_db->quoteName('#__languages'));
            $this->_db->setQuery($query);
            return $this->_db->loadObjectList();
        } catch (Exception $e) {
            JLog::add('Problem when try to fet default language with error : ' . $e->getMessage(),JLog::ERROR, 'com_emundus.translations');
            return false;
        }
    }

    public function updateLanguage($lang_code,$published,$default){
        $query = $this->_db->getQuery(true);

        try {
            if(!empty($default)) {
                $old_lang = $this->getDefaultLanguage();
                require_once (JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_languages'.DS.'models'.DS.'installed.php');
                $m_installed = new LanguagesModelInstalled;

                $m_installed->publish($lang_code);

                $query->update($this->_db->quoteName('#__languages'))
                    ->set($this->_db->quoteName('published') . ' = 0')
                    ->where($this->_db->quoteName('lang_code') . ' = ' . $this->_db->quote($old_lang->lang_code));
                $this->_db->setQuery($query);
                return $this->_db->execute();
            } else {
                $query->update($this->_db->quoteName('#__languages'))
                    ->set($this->_db->quoteName('published') . ' = ' . $this->_db->quote($published))
                    ->where($this->_db->quoteName('lang_code') . ' = ' . $this->_db->quote($lang_code));
                $this->_db->setQuery($query);
                return $this->_db->execute();
            }
        } catch (Exception $e) {
            JLog::add('Problem when try to update language ' . $lang_code .' with error : ' . $e->getMessage(),JLog::ERROR, 'com_emundus.translations');
            return false;
        }
    }

    public function getTranslationsFalang($default_lang,$lang_to,$reference_id,$fields,$reference_table,$reference_field = ''){
        $labels = new stdClass();
        $fields = explode(',',$fields);

        $query = $this->_db->getQuery(true);

        try {
            $query->clear()
                ->select('lang_id')
                ->from($this->_db->quoteName('#__languages'))
                ->where($this->_db->quoteName('lang_code') . ' = ' . $this->_db->quote($default_lang));
            $this->_db->setQuery($query);
            $default_lang_id = $this->_db->loadResult();

            $query->clear()
                ->select('lang_id')
                ->from($this->_db->quoteName('#__languages'))
                ->where($this->_db->quoteName('lang_code') . ' = ' . $this->_db->quote($lang_to));
            $this->_db->setQuery($query);
            $lang_to_id = $this->_db->loadResult();

            foreach ($fields as $field){
                $labels->{$field} = new stdClass;

                $query->clear()
                    ->select('value')
                    ->from($this->_db->quoteName('#__falang_content'))
                    ->where($this->_db->quoteName('reference_id') . ' = ' . $this->_db->quote($reference_id))
                    ->where($this->_db->quoteName('reference_table') . ' = ' . $this->_db->quote($reference_table))
                    ->where($this->_db->quoteName('language_id') . ' = ' . $this->_db->quote($default_lang_id))
                    ->where($this->_db->quoteName('reference_field') . ' = ' . $this->_db->quote($field));
                $this->_db->setQuery($query);
                $labels->{$field}->default_lang = $this->_db->loadResult();

                if(empty($labels->{$field}->default_lang)){
                    $query->clear()
                        ->select($field)
                        ->from($this->_db->quoteName('#__' . $reference_table))
                        ->where($this->_db->quoteName('id') . ' = ' . $reference_id);
                    $this->_db->setQuery($query);
                    $labels->{$field}->default_lang = $this->_db->loadResult();
                }

                $query->clear()
                    ->select('value')
                    ->from($this->_db->quoteName('#__falang_content'))
                    ->where($this->_db->quoteName('reference_id') . ' = ' . $this->_db->quote($reference_id))
                    ->where($this->_db->quoteName('reference_table') . ' = ' . $this->_db->quote($reference_table))
                    ->where($this->_db->quoteName('language_id') . ' = ' . $this->_db->quote($lang_to_id))
                    ->where($this->_db->quoteName('reference_field') . ' = ' . $this->_db->quote($field));
                $this->_db->setQuery($query);
                $labels->{$field}->lang_to = $this->_db->loadResult();
            }

            return $labels;
        } catch(Exception $e) {
            JLog::add('component/com_emundus/models/translations | Error at getting the translations ' . $reference_id . ' references to table ' . $reference_table . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus.translations');
            return false;
        }
    }

    public function updateFalangTranslation($value,$lang_to,$reference_table,$reference_id,$field){
        $query = $this->_db->getQuery(true);

        $user = JFactory::getUser()->id;

        try {
            $query->select('lang_id')
                ->from($this->_db->quoteName('#__languages'))
                ->where($this->_db->quoteName('lang_code') . ' = ' . $this->_db->quote($lang_to));
            $this->_db->setQuery($query);
            $lang_to_id = $this->_db->loadResult();

            $query->clear()
                ->select('id')
                ->from($this->_db->quoteName('#__falang_content'))
                ->where($this->_db->quoteName('language_id') . ' = ' . $this->_db->quote($lang_to_id))
                ->where($this->_db->quoteName('reference_id') . ' = ' . $this->_db->quote($reference_id))
                ->where($this->_db->quoteName('reference_table') . ' = ' . $this->_db->quote($reference_table))
                ->where($this->_db->quoteName('reference_field') . ' = ' . $this->_db->quote($field));
            $this->_db->setQuery($query);
            $falang_translation = $this->_db->loadResult();

            if(!empty($falang_translation)) {
                $query->update($this->_db->quoteName('#__falang_content'))
                    ->set($this->_db->quoteName('value') . ' = ' . $this->_db->quote($value))
                    ->set($this->_db->quoteName('modified_by') . ' = ' . $this->_db->quote($user))
                    ->where($this->_db->quoteName('id') . ' = ' . $this->_db->quote($falang_translation));
                $this->_db->setQuery($query);
                return $this->_db->execute();
            } else {
                $query->insert($this->_db->quoteName('#__falang_content'))
                    ->set($this->_db->quoteName('language_id') . ' = ' . $this->_db->quote($lang_to_id))
                    ->set($this->_db->quoteName('reference_id') . ' = ' . $this->_db->quote($reference_id))
                    ->set($this->_db->quoteName('reference_table') . ' = ' . $this->_db->quote($reference_table))
                    ->set($this->_db->quoteName('reference_field') . ' = ' . $this->_db->quote($field))
                    ->set($this->_db->quoteName('value') . ' = ' . $this->_db->quote($value))
                    ->set($this->_db->quoteName('original_text') . ' = ' . $this->_db->quote($value))
                    ->set($this->_db->quoteName('modified') . ' = ' . $this->_db->quote(date('Y-m-d H:i:s')))
                    ->set($this->_db->quoteName('modified_by') . ' = ' . $this->_db->quote($user))
                    ->set($this->_db->quoteName('published') . ' = ' . $this->_db->quote(1));
                $this->_db->setQuery($query);
                return $this->_db->execute();
            }

        } catch (Exception $e) {
            JLog::add('component/com_emundus/models/translations | Error at updating the translation ' . $reference_id . ' references to table ' . $reference_table . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus.translations');
            return false;
        }
    }
}
