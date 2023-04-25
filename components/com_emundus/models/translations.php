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
     * @codeCoverageIgnore
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
     * Check if translation tool is ready to use
     *
     * @return false|mixed|null
     *
     * @since version 1.28.0
     */
    public function checkSetup(){
        try {
            $query = $this->_db->getQuery(true);

            $query->select('count(id)')
                ->from($this->_db->quoteName('#__emundus_setup_languages'));
            $this->_db->setQuery($query);
            return $this->_db->loadResult();
        }
        // @codeCoverageIgnoreStart
        catch (Exception $e) {
            JLog::add('Problem when try to get setup translation tool with error : ' . $e->getMessage(),JLog::ERROR, 'com_emundus.translations');
            return false;
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Configure setup at first launch of the translation tool
     *
     * @return false|mixed|void
     *
     * @since version 1.28.0
     */
    public function configureSetup(){
        $query = $this->_db->getQuery(true);

        try {
            $query
                ->select('DISTINCT(element), CONCAT(type, "s") AS type')
                ->from($this->_db->quoteName('#__extensions'))
                ->where($this->_db->quoteName('element') . ' LIKE ' . $this->_db->quote('%emundus%'));

            $this->_db->setQuery($query);

            $extensions = $this->_db->loadObjectList();
        } catch (Exception $e) {
            JLog::add('Error getting extensions with error : ' . $e->getMessage(),JLog::ERROR, 'com_emundus.translations');
            return false;
        }

        // Components, modules, extensions files
        $files = [];
        foreach ($this->getPlatformLanguages() as $language) {
            /*foreach ($extensions as $extension) {
                $file = JPATH_SITE . '/' . $extension->type . '/' . $extension->element . '/language/' . $language . '/' . $language.'.'.$extension->element. '.ini';
                if (file_exists($file)) {
                    $files[] = $file;
                }
            }*/
            // Overrides
            $override_file = JPATH_SITE . '/language/overrides/' . $language.'.override.ini';
            if (file_exists($override_file)) {
                $files[] = $override_file;
            }
            //
        }
        //

        $db_columns = [
            $this->_db->quoteName('tag'),
            $this->_db->quoteName('lang_code'),
            $this->_db->quoteName('override'),
            $this->_db->quoteName('original_text'),
            $this->_db->quoteName('original_md5'),
            $this->_db->quoteName('override_md5'),
            $this->_db->quoteName('location'),
            $this->_db->quoteName('type'),
            $this->_db->quoteName('created_by'),
            $this->_db->quoteName('reference_id'),
            $this->_db->quoteName('reference_table'),
            $this->_db->quoteName('reference_field'),
        ];

        foreach ($files as $file) {
            $parsed_file = JLanguageHelper::parseIniFile($file);

            $file = explode('/', $file);
            $file_name = end($file);
            $language = strtok($file_name, '.');

            $key_added = [];

            foreach ($parsed_file as $key => $val) {
                if(!in_array(strtoupper($key),$key_added)) {
                    $query->clear()
                        ->select('count(id)')
                        ->from($this->_db->quoteName('jos_emundus_setup_languages'))
                        ->where($this->_db->quoteName('tag') . ' = ' . $this->_db->quote($key))
                        ->andWhere($this->_db->quoteName('lang_code') . ' = ' . $this->_db->quote($language))
                        ->andWhere($this->_db->quoteName('location') . ' = ' . $this->_db->quote($file_name));
                    $this->_db->setQuery($query);

                    if ($this->_db->loadResult() == 0) {
                        if (strpos($file_name, 'override') !== false) {
                            // Search if value is use in fabrik
                            $reference_table = null;
                            $reference_id = null;
                            $reference_field = null;

                            $query->clear()
                                ->select('id')
                                ->from($this->_db->quoteName('#__fabrik_forms'))
                                ->where($this->_db->quoteName('label') . ' LIKE ' . $this->_db->quote($key));
                            $this->_db->setQuery($query);
                            $find = $this->_db->loadResult();

                            if (!empty($find)) {
                                $reference_table = 'fabrik_forms';
                                $reference_id = $find;
                                $reference_field = 'label';
                            } else {
                                $query->clear()
                                    ->select('id,intro')
                                    ->from($this->_db->quoteName('#__fabrik_forms'));
                                $this->_db->setQuery($query);
                                $forms_intro = $this->_db->loadObjectList();

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
                                        ->from($this->_db->quoteName('#__fabrik_groups'))
                                        ->where($this->_db->quoteName('label') . ' LIKE ' . $this->_db->quote($key));
                                    $this->_db->setQuery($query);
                                    $find = $this->_db->loadResult();

                                    if (!empty($find)) {
                                        $reference_table = 'fabrik_groups';
                                        $reference_id = $find;
                                        $reference_field = 'label';
                                    } else {
                                        $query->clear()
                                            ->select('id,params')
                                            ->from($this->_db->quoteName('#__fabrik_groups'));
                                        $this->_db->setQuery($query);
                                        $groups_params = $this->_db->loadObjectList();

                                        foreach ($groups_params as $group_params) {
                                            $params = json_decode($group_params->params);
                                            if (strip_tags($params->intro) == $key) {
                                                $find = $group_params->id;
                                                break;
                                            }
                                        }

                                        if (!empty($find)) {
                                            $reference_table = 'fabrik_groups';
                                            $reference_id = $find;
                                            $reference_field = 'intro';
                                        } else {
                                            $query->clear()
                                                ->select('id')
                                                ->from($this->_db->quoteName('#__fabrik_elements'))
                                                ->where($this->_db->quoteName('label') . ' LIKE ' . $this->_db->quote($key));
                                            $this->_db->setQuery($query);
                                            $find = $this->_db->loadResult();

                                            if (!empty($find)) {
                                                $reference_table = 'fabrik_elements';
                                                $reference_id = $find;
                                                $reference_field = 'label';
                                            } else {
                                                $query->clear()
                                                    ->select('id,params')
                                                    ->from($this->_db->quoteName('#__fabrik_elements'))
                                                    ->where($this->_db->quoteName('plugin') . ' = ' . $this->_db->quote('dropdown'));
                                                $this->_db->setQuery($query);
                                                $elements_params = $this->_db->loadObjectList();

                                                foreach ($elements_params as $element_params) {
                                                    $params = json_decode($element_params->params);
                                                    $sub_options = $params->sub_options;
                                                    if (in_array($key, array_values($sub_options->sub_labels))) {
                                                        $find = $element_params->id;
                                                        break;
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
                            $row = [$this->_db->quote($key), $this->_db->quote($language), $this->_db->quote($val), $this->_db->quote($val), $this->_db->quote(md5($val)), $this->_db->quote(md5($val)), $this->_db->quote($file_name), $this->_db->quote('override'), 62, $this->_db->quote($reference_id), $this->_db->quote($reference_table), $this->_db->quote($reference_field)];
                        } else {
                            $row = [$this->_db->quote($key), $this->_db->quote($language), $this->_db->quote($val), $this->_db->quote($val), $this->_db->quote(md5($val)), $this->_db->quote(md5($val)), $this->_db->quote($file_name), $this->_db->quote(null), 62, $this->_db->quote(null), $this->_db->quote(null), $this->_db->quote(null)];
                        }
                        try {
                            $query
                                ->clear()
                                ->insert($this->_db->quoteName('jos_emundus_setup_languages'))
                                ->columns($db_columns)
                                ->values(implode(',', $row));

                            $this->_db->setQuery($query);
                            $this->_db->execute();
                        } catch (Exception $e) {
                            JLog::add('Problem when insert translations at first launch with error : ' . $e->getMessage(),JLog::ERROR, 'com_emundus.translations');
                            return false;
                        }
                        $key_added[] = strtoupper($key);
                    }
                }
            }
        }
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

        include_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_falang' . DS . "models".DS."ContentElement.php");

        jimport('joomla.filesystem.folder');
        $dir = JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'contentelements/';
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

                    $object->name = JText::_($xmlDoc->getElementsByTagName('name')->item(0)->textContent);
                    $object->description = JText::_($xmlDoc->getElementsByTagName('description')->item(0)->textContent);
                    $object->table = new stdClass;
                    $object->table->name = trim($tableElement->getAttribute( 'name' ));
                    $object->table->reference = trim($tableElement->getAttribute( 'reference' ));
                    $object->table->label = trim($tableElement->getAttribute( 'label' ));
                    $object->table->filters = trim($tableElement->getAttribute( 'filters' ));
                    $object->table->load_all = trim($tableElement->getAttribute( 'load_all' ));
                    $object->table->type = trim($tableElement->getAttribute( 'type' ));
                    $object->table->load_first_data = trim($tableElement->getAttribute( 'load_first_data' ));
                    $object->table->load_first_child = trim($tableElement->getAttribute( 'load_first_child' ));

                    $tableFields = $tableElement->getElementsByTagName( 'field' );
                    $tableSections = $tableElement->getElementsByTagName( 'section' );

                    $fields = array();
                    $indexedSections = array();

                    foreach ($tableFields as $tableField){
                        if(trim($tableField->getAttribute( 'type' )) == 'children'){
                            $field = new stdClass;
                            $field->Type = trim($tableField->getAttribute( 'type' ));
                            $field->Name = trim($tableField->getAttribute( 'name' ));
                            $field->Label = trim($tableField->textContent);
                            $field->Table = trim($tableField->getAttribute( 'table' ));
                            $field->Options = trim($tableField->getAttribute( 'options' ));

                            $fields[] = $field;
                        }
                    }

                    foreach ($tableSections as $tableSection){
                        $section = new stdClass;
                        $section->Label = trim( $tableSection->getAttribute( 'label' ));
                        $section->Name = trim( $tableSection->getAttribute( 'name' ));
                        $section->Table = trim( $tableSection->getAttribute( 'table' ));
                        $section->TableJoin = trim( $tableSection->getAttribute( 'join_table' ));
                        $section->TableJoinColumn = trim( $tableSection->getAttribute( 'join_column' ));
                        $section->ReferenceColumn = trim( $tableSection->getAttribute( 'reference_column' ));
                        $section->indexedFields = array();

                        foreach ($tableFields as $tableField){
                            if(trim($tableField->getAttribute( 'section' )) == $section->Name){
                                $field = new stdClass;
                                $field->Type = trim($tableField->getAttribute( 'type' ));
                                $field->Name = trim($tableField->getAttribute( 'name' ));
                                $field->Label = trim($tableField->textContent);
                                $field->Table = trim($tableField->getAttribute( 'table' ));
                                $field->Options = trim($tableField->getAttribute( 'options' ));

                                $fields[] = $field;
                                $section->indexedFields[$field->Name] = $field;
                            }
                        }
                        $indexedSections[] = $section;

                    }
                    $object->fields = new stdClass;
                    $object->fields->Fields = $fields;
                    $object->fields->Sections = $indexedSections;

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

    /**
     * Get childrens to filter our translations
     *
     * @param $table
     * @param $reference_id
     * @param $label
     *
     * @return array|false|mixed
     *
     * @since version 1.28.0
     */
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
    public function getTranslations($type = 'override',$lang_code = '*',$search = '',$location = '',$reference_table = '',$reference_id = 0,$reference_fields = '',$tag = ''){

        try {
            $query = $this->_db->getQuery(true);

            $query->select('*')
                ->from($this->_db->quoteName('#__emundus_setup_languages'))
                ->where($this->_db->quoteName('type') . ' LIKE ' . $this->_db->quote('%' . $type . '%'));


            if ($lang_code !== '*' && !empty($lang_code)) {
                $query->where($this->_db->quoteName('lang_code') . ' = ' . $this->_db->quote($lang_code));
            }
            if (!empty($search)) {
                $query->where($this->_db->quoteName('override') . ' LIKE ' . $this->_db->quote('%' . $search . '%'));
            }
            if (!empty($location)) {
                $query->where($this->_db->quoteName('location') . ' = ' . $this->_db->quote($location));
            }
            if (!empty($reference_table)) {
                $query->where($this->_db->quoteName('reference_table') . ' LIKE ' . $this->_db->quote($reference_table));
            }
            if (!empty($reference_fields)) {
                if(is_array($reference_fields)){
                    $query->where($this->_db->quoteName('reference_field') . ' IN (' . implode(',',$this->_db->quote($reference_fields)) . ')');
                } else {
                    $query->where($this->_db->quoteName('reference_field') . ' LIKE ' . $this->_db->quote($reference_fields));
                }
            }
            if (!empty($reference_id)) {
                if(is_array($reference_id)){
                    $query->where($this->_db->quoteName('reference_id') . ' IN (' . implode(',',$this->_db->quote($reference_id)) . ')');
                } else {
                    $query->where($this->_db->quoteName('reference_id') . ' LIKE ' . $this->_db->quote($reference_id));
                }
            }
            if (!empty($tag)) {
                $query->where($this->_db->quoteName('tag') . ' LIKE ' . $this->_db->quote($tag));
            }
            $this->_db->setQuery($query);
            return $this->_db->loadObjectList();
        } catch(Exception $e){
            JLog::add('Problem when try to get translations with error : ' . $e->getMessage(),JLog::ERROR, 'com_emundus.translations');
            return [];
        }
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
    public function insertTranslation($tag,$override,$lang_code,$location = '',$type='override',$reference_table = '',$reference_id = 0,$reference_field = ''){
        $isCorrect = $this->checkTagIsCorrect($tag, $override, 'insert', $lang_code);
        if (!$isCorrect) {
            return false;
        }

        $query = $this->_db->getQuery(true);
        $user = JFactory::getUser();

        try{
            if(empty($location)){
                $location = $lang_code . '.override.ini';
            }

            $columns = ['tag','lang_code','override','original_text','original_md5','override_md5','location','type','reference_id','reference_table','reference_field','published','created_by','created_date','modified_by','modified_date'];
            $values = [
                $this->_db->quote($tag),
                $this->_db->quote($lang_code),
                $this->_db->quote($override),
                $this->_db->quote($override),
                $this->_db->quote(md5($override)),
                $this->_db->quote(md5($override)),
                $this->_db->quote($location),
                $this->_db->quote($type),
                $this->_db->quote($reference_id),
                $this->_db->quote($reference_table),
                $this->_db->quote($reference_field),
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
                $override_file = JPATH_SITE . '/language/overrides/' . $location;
                if (file_exists($override_file)) {
                    $parsed_file = JLanguageHelper::parseIniFile($override_file);
                    $parsed_file[$tag] = $override;
                    return JLanguageHelper::saveToIniFile($override_file, $parsed_file);
                }
            }
        }
        // @codeCoverageIgnoreStart
        catch(Exception $e){
            JLog::add('Problem when try to insert translation into file ' . $location . ' with error : ' . $e->getMessage(),JLog::ERROR, 'com_emundus.translations');
            return false;
        }
        // @codeCoverageIgnoreEnd
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
    public function updateTranslation($tag, $override, $lang_code, $type = 'override', $reference_table = '', $reference_id = 0) {
        $saved = false;

        $isCorrect = $this->checkTagIsCorrect($tag, $override, 'update', $lang_code);
        if (!$isCorrect) {
            return false;
        }
        $isTag = $this->checkTagExists($tag, $reference_table, $reference_id);
        if (!$isTag) {
            $tag = $this->generateNewTag($tag, $reference_table, $reference_id);
        }

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
                    ->set($this->_db->quoteName('location') . ' = ' . $this->_db->quote($location));
                if(!empty($reference_table)){
                    $query->set($this->_db->quoteName('reference_table') . ' = ' . $this->_db->quote($reference_table));
                }
                if(!empty($reference_id)){
                    $query->set($this->_db->quoteName('reference_id') . ' = ' . $this->_db->quote($reference_id));
                }
                $query->where($this->_db->quoteName('tag') . ' = ' . $this->_db->quote($tag))
                    ->andWhere($this->_db->quoteName('lang_code') . ' = ' . $this->_db->quote($lang_code))
                    ->andWhere($this->_db->quoteName('type') . ' = ' . $this->_db->quote($type));
                $this->_db->setQuery($query);

                if($this->_db->execute()) {
                    $override_file = JPATH_BASE . '/language/overrides/' . $location;
                    if (file_exists($override_file)) {
                        $parsed_file = JLanguageHelper::parseIniFile($override_file);
                        $parsed_file[$tag] = $override;
                        $saved = JLanguageHelper::saveToIniFile($override_file, $parsed_file);

                        if ($saved) {
                            $saved = $tag;
                        }
                    } else {
                        $saved = false;
                    }
                }
            } else {
                $existing_translation = $this->getTranslations('override',$lang_code,'','','','',$tag);
                if(empty($existing_translation)) {
                    $saved = $this->insertTranslation($tag, $override, $lang_code);
                } else {
                    $saved = $this->updateTranslation($tag, $override, $lang_code);
                }
            }
        }
        // @codeCoverageIgnoreStart
        catch(Exception $e){
            JLog::add('Problem when try to update translation ' . $tag . ' into file ' . $location . ' with error : ' . $e->getMessage(),JLog::ERROR, 'com_emundus.translations');
            return false;
        }
        // @codeCoverageIgnoreEnd

        return $saved;
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
            $this->_db->execute();

            if($lang_code == '*') {
                $languages = JLanguageHelper::getLanguages();

                foreach ($languages as $language) {
                    $location = $language->lang_code . '.override.ini';
                    $override_file = JPATH_SITE . '/language/overrides/' . $location;
                    if (file_exists($override_file)) {
                        $parsed_file = JLanguageHelper::parseIniFile($override_file);
                        unset($parsed_file[$tag]);
                        JLanguageHelper::saveToIniFile($override_file, $parsed_file);
                    }
                }
            } else {
                $location = $lang_code . '.override.ini';
                $override_file = JPATH_SITE . '/language/overrides/' . $location;
                if (file_exists($override_file)) {
                    $parsed_file = JLanguageHelper::parseIniFile($override_file);
                    unset($parsed_file[$tag]);
                    JLanguageHelper::saveToIniFile($override_file, $parsed_file);
                }
            }
            return true;
        }
        // @codeCoverageIgnoreStart
        catch (Exception $e) {
            JLog::add('Problem when try to delete translation ' . $tag . ' with error : ' . $e->getMessage(),JLog::ERROR, 'com_emundus.translations');
            return false;
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Get default language of the platform
     *
     * @return false|mixed|null
     *
     * @since version 1.28.0
     */
    public function getDefaultLanguage(){
        $query = $this->_db->getQuery(true);

        $default = JComponentHelper::getParams('com_languages')->get('site');

        try {
            $query->select('lang_code,title_native')
                ->from($this->_db->quoteName('#__languages'))
                ->where($this->_db->quoteName('lang_code') . ' = ' . $this->_db->quote($default));
            $this->_db->setQuery($query);
            return $this->_db->loadObject();
        }
        // @codeCoverageIgnoreStart
        catch (Exception $e) {
            JLog::add('Problem when try to fet default language with error : ' . $e->getMessage(),JLog::ERROR, 'com_emundus.translations');
            return false;
        }
        // @codeCoverageIgnoreEnd
    }

    public function getPlatformLanguages() : array {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query
            ->select($db->quoteName('lang_code'))
            ->from($db->quoteName('#__languages'))
            ->where($db->quoteName('published') . ' = 1 ');
        $db->setQuery($query);

        try {
            return $db->loadColumn();
        }
        // @codeCoverageIgnoreStart
        catch (Exception $e) {
            return [];
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Get all languages available on our platform
     *
     * @return array|false|mixed
     *
     * @since version 1.28.0
     */
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

    /**
     * Update default language or/and secondary languages
     *
     * @param $lang_code
     * @param $published
     * @param $default boolean to specify if we are changing default language or secondary languages
     *
     * @return false|mixed
     *
     * @since version 1.28.0
     */
    public function updateLanguage($lang_code,$published,$default){
        $query = $this->_db->getQuery(true);

        try {
            if(!empty($default)) {
                $old_lang = $this->getDefaultLanguage();
                JComponentHelper::getParams('com_languages')->set('site', $lang_code);

                $query->update($this->_db->quoteName('#__languages'))
                    ->set($this->_db->quoteName('published') . ' = ' . $this->_db->quote($published))
                    ->where($this->_db->quoteName('lang_code') . ' = ' . $this->_db->quote($lang_code));
                $this->_db->setQuery($query);
                $this->_db->execute();

                $query->clear()
                    ->update($this->_db->quoteName('#__languages'))
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

    public function updateFalangModule($published){
        try {
            $query = $this->_db->getQuery(true);

            $query->update('#__modules')
                ->set($this->_db->quoteName('published') . ' = ' . $this->_db->quote($published))
                ->where($this->_db->quoteName('module') . ' = ' . $this->_db->quote('mod_falang'));
            $this->_db->setQuery($query);
            return $this->_db->execute();
        } catch (Exception $e) {
            JLog::add('Problem when try to unpublish falang module with error : ' . $e->getMessage(),JLog::ERROR, 'com_emundus.translations');
            return false;
        }
    }

    /**
     * Get translations with Falang system (campaigns, emails, programs, status)
     *
     * @param $default_lang
     * @param $lang_to
     * @param $reference_id
     * @param $fields
     * @param $reference_table
     * @param $reference_field
     *
     * @return false|stdClass
     *
     * @since version 1.28.0
     */
    public function getTranslationsFalang($default_lang,$lang_to,$reference_id,$fields,$reference_table,$reference_field = ''){
        $translations = new stdClass();
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

            $translations->{$reference_id} = new stdClass;

            foreach ($fields as $field){
                $labels = new stdClass();
                $labels = new stdClass;
                $labels->reference_field = $field;
                $labels->reference_table = $reference_table;
                $labels->reference_id = $reference_id;

                $query->clear()
                    ->select('value')
                    ->from($this->_db->quoteName('#__falang_content'))
                    ->where($this->_db->quoteName('reference_id') . ' = ' . $this->_db->quote($reference_id))
                    ->where($this->_db->quoteName('reference_table') . ' = ' . $this->_db->quote($reference_table))
                    ->where($this->_db->quoteName('language_id') . ' = ' . $this->_db->quote($default_lang_id))
                    ->where($this->_db->quoteName('reference_field') . ' = ' . $this->_db->quote($field));
                $this->_db->setQuery($query);
                $labels->default_lang = $this->_db->loadResult();

                if(empty($labels->default_lang)){
                    $query->clear()
                        ->select($field)
                        ->from($this->_db->quoteName('#__' . $reference_table))
                        ->where($this->_db->quoteName('id') . ' = ' . $reference_id);
                    $this->_db->setQuery($query);
                    $labels->default_lang = $this->_db->loadResult();
                }

                $query->clear()
                    ->select('value')
                    ->from($this->_db->quoteName('#__falang_content'))
                    ->where($this->_db->quoteName('reference_id') . ' = ' . $this->_db->quote($reference_id))
                    ->where($this->_db->quoteName('reference_table') . ' = ' . $this->_db->quote($reference_table))
                    ->where($this->_db->quoteName('language_id') . ' = ' . $this->_db->quote($lang_to_id))
                    ->where($this->_db->quoteName('reference_field') . ' = ' . $this->_db->quote($field));
                $this->_db->setQuery($query);
                $labels->lang_to = $this->_db->loadResult();
                $translations->{$reference_id}->{$field} = $labels;
            }

            return $translations;
        } catch(Exception $e) {
            JLog::add('component/com_emundus/models/translations | Error at getting the translations ' . $reference_id . ' references to table ' . $reference_table . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus.translations');
            return false;
        }
    }

    /**
     * Update a translation with Falang system
     *
     * @param $value
     * @param $lang_to
     * @param $reference_table
     * @param $reference_id
     * @param $field
     *
     * @return false|mixed
     *
     * @since version
     */
    public function updateFalangTranslation($value, $lang_to, $reference_table, $reference_id, $field){
        $updated = false;
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

            if (!empty($falang_translation)) {
                $query->update($this->_db->quoteName('#__falang_content'))
                    ->set($this->_db->quoteName('value') . ' = ' . $this->_db->quote($value))
                    ->set($this->_db->quoteName('modified_by') . ' = ' . $this->_db->quote($user))
                    ->where($this->_db->quoteName('id') . ' = ' . $this->_db->quote($falang_translation));
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
            }

            $this->_db->setQuery($query);
            $updated = $this->_db->execute();
        } catch (Exception $e) {
            JLog::add('component/com_emundus/models/translations | Error at updating the translation ' . $reference_id . ' references to table ' . $reference_table . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus.translations');
        }

        return $updated;
    }

    /**
     * Get reference id by filters
     *
     * @param $reference_table
     * @param $reference_column
     * @param $join_table
     * @param $join_column
     * @param $reference_id
     *
     * @return array|false|mixed
     *
     * @since version 1.28.0
     */
    public function getJoinReferenceId($reference_table,$reference_column,$join_table,$join_column,$reference_id){
        $query = $this->_db->getQuery(true);


        try {
            $query->select('rt.id')
                ->from($this->_db->quoteName('#__' . $reference_table,'rt'))
                ->leftJoin($this->_db->quoteName('#__' . $join_table,'jt').' ON '.$this->_db->quoteName('rt.id').' = '.$this->_db->quoteName('jt.' . $reference_column));
            if(is_array($reference_id)){
                $query->where($this->_db->quoteName('jt.' . $join_column) . ' IN (' . implode(',',$this->_db->quote($reference_id)) . ')');
            } else {
                $query->where($this->_db->quoteName('jt.' . $join_column) . ' = ' . $this->_db->quote($reference_id));
            }

            $this->_db->setQuery($query);
            return $this->_db->loadColumn();
        } catch (Exception $e) {
            JLog::add('component/com_emundus/models/translations | Error at getting the reference id by join with id ' . $reference_id . ' references to table ' . $join_table . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus.translations');
            return false;
        }
    }

    public function getOrphelins($default_lang,$lang_code,$type = 'override'){
        $query = $this->_db->getQuery(true);
        $sub_query = $this->_db->getQuery(true);

        try {
            $sub_query->select('tag')
                ->from($this->_db->quoteName('#__emundus_setup_languages'))
                ->where($this->_db->quoteName('lang_code') . ' = ' . $this->_db->quote($lang_code))
                ->andWhere($this->_db->quoteName('type') . ' = ' . $this->_db->quote($type));

            $query->select('*')
                ->from($this->_db->quoteName('#__emundus_setup_languages'))
                ->where($this->_db->quoteName('lang_code') . ' = ' . $this->_db->quote($default_lang))
                ->andWhere($this->_db->quoteName('type') . ' = ' . $this->_db->quote($type))
                ->andWhere($this->_db->quoteName('tag') . ' NOT IN (' . $sub_query->__toString() . ')');
            $this->_db->setQuery($query);
            return $this->_db->loadObjectList();
        } catch (Exception $e) {
            JLog::add('component/com_emundus/models/translations | Error at getting orphelins : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus.translations');
            return false;
        }
    }

    public function sendPurposeNewLanguage($language,$comment){
        try {
            include_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'controllers'.DS.'messages.php');
            $c_messages = new EmundusControllerMessages();

            $template   = JFactory::getApplication()->getTemplate(true);
            $params     = $template->params;
            $config = JFactory::getConfig();
            // Get LOGO
            if (!empty($params->get('logo')->custom->image)) {
                $logo = json_decode(str_replace("'", "\"", $params->get('logo')->custom->image), true);
                $logo = !empty($logo['path']) ? JURI::base().$logo['path'] : "";

            } else {
                $logo_module = JModuleHelper::getModuleById('90');
                preg_match('#src="(.*?)"#i', $logo_module->content, $tab);
                $pattern = "/^(?:ftp|https?|feed)?:?\/\/(?:(?:(?:[\w\.\-\+!$&'\(\)*\+,;=]|%[0-9a-f]{2})+:)*
        (?:[\w\.\-\+%!$&'\(\)*\+,;=]|%[0-9a-f]{2})+@)?(?:
        (?:[a-z0-9\-\.]|%[0-9a-f]{2})+|(?:\[(?:[0-9a-f]{0,4}:)*(?:[0-9a-f]{0,4})\]))(?::[0-9]+)?(?:[\/|\?]
        (?:[\w#!:\.\?\+\|=&@$'~*,;\/\(\)\[\]\-]|%[0-9a-f]{2})*)?$/xi";

                if ((bool) preg_match($pattern, $tab[1])) {
                    $tab[1] = parse_url($tab[1], PHP_URL_PATH);
                }

                $logo = JURI::base().$tab[1];
            }

            $post = [
                'SITE_NAME'      => $config->get('sitename'),
                'SITE_URL'      => JURI::base(),
                'LANGUAGE_FIELD' => $language,
                'LOGO' => $logo
            ];

            return $c_messages->sendEmailNoFnum('support@emundus.fr','installation_new_language',$post);
        } catch (Exception $e) {
            JLog::add('component/com_emundus/models/translations | Error at sending email to purpose a new language : ' . preg_replace("/[\r\n]/"," ",$e->getMessage()), JLog::ERROR, 'com_emundus.translations');
            return false;
        }
    }

    public function checkTagIsCorrect($tag, $override, $action, $lang) {
        $isCorrect = false;

        if (!empty($tag)) {
            if (!preg_match('/[$^*()=+\\\[<?;]/', $tag, $matches)) {
                $isCorrect = true;
            } else {
                JLog::add("Problem when try to $action translation into file, tag [$tag] for override [$override] contains forbidden characters ",JLog::ERROR, 'com_emundus.translations');
            }
        } else {
            JLog::add("Problem when try to $action translation into file, missing tag for this override $override, $lang",JLog::ERROR, 'com_emundus.translations');
        }

        return $isCorrect;
    }

    public function checkTagExists($tag, $reference_table, $reference_id)
    {
        $tagExistsInBdd = false;
        $tagExistsInOverrides = false;
        $translations = $this->getTranslations('override', '*', '', '', $reference_table, $reference_id, $tag);

        if (!empty($translations)) {
            $tagExistsInBdd = true;
        } else {
            $tagExistsInOverrides = $this->checkTagExistsInOverrideFiles($tag);
        }

        return ($tagExistsInBdd || $tagExistsInOverrides);
    }

    public function checkTagExistsInOverrideFiles($tag, $languages = null) {
        $existsInOverrideFiles = false;
        $languages = empty($languages) ? $this->getPlatformLanguages() : $languages;

        $files = [];
        foreach ($languages as $language) {
            $override_file = JPATH_SITE . '/language/overrides/' . $language.'.override.ini';
            if (file_exists($override_file)) {
                $files[] = $override_file;
            }
        }

        foreach ($files as $file) {
            $parsed_file = JLanguageHelper::parseIniFile($file);

            if (!empty($parsed_file)) {
                if (in_array($tag, array_keys($parsed_file))) {
                    $existsInOverrideFiles = true;
                    break;
                }
            }
        }

        return $existsInOverrideFiles;
    }

    public function generateNewTag($tag, $reference_table = "", $reference_id = 0)
    {
        if (!empty($reference_table) && !empty($reference_id)) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            switch ($reference_table) {
                case 'fabrik_elements':
                    $element_id = $reference_id;
                    $group_id = 0;

                    $query->select('group_id')
                        ->from('#__fabrik_elements')
                        ->where('id = ' . $element_id);

                    $db->setQuery($query);

                    try {
                        $group_id = $db->loadResult();
                    } catch(Exception $e) {
                        JLog::add("Error trying to find group_id from element_id $element_id " . preg_replace("/[\r\n]/"," ",$e->getMessage()), JLog::ERROR, 'com_emundus.translations');
                    }

                    $tag = "ELEMENT_" . $group_id . "_" . $element_id;
                    break;
                case 'fabrik_forms':
                    $tag = "FORM_" . $reference_id;
                    break;
                case 'fabrik_groups':
                    $group_id = $reference_id;
                    $form_id = 0;

                    $query->select('form_id')
                        ->from('#__fabrik_formgroup')
                        ->where('group_id = ' . $reference_id);

                    $db->setQuery($query);

                    try {
                        $form_id = $db->loadResult();
                    } catch(Exception $e) {
                        JLog::add("Error trying to find form_id from group_id $group_id " . preg_replace("/[\r\n]/"," ",$e->getMessage()), JLog::ERROR, 'com_emundus.translations');
                    }

                    $tag = "GROUP_" . $form_id . "_" . $group_id;
                    break;
                default:
                    JLog::add(" Impossible to generate a new tag. $tag has no TAG in setup_languages nor in override files, but reference_id is empty.", JLog::INFO, 'com_emundus.translations');
                    break;
            }

            $index = 0;
            $tmp_tag = $tag;
            while ($this->checkTagExistsInOverrideFiles($tmp_tag)) {
                $tmp_tag =  $tag . '_' . $index;
            }
            $tag = $tmp_tag;
        }

        return $tag;
    }

    public function updateElementLabel($tag, $reference_table, $reference_id): bool
    {
        $updated = false;

        if (!empty($tag) && !empty($reference_table) && !empty($reference_id)) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            switch($reference_table) {
                case 'fabrik_elements':
                    $query->update('#__fabrik_elements')
                        ->set('label = ' . $db->quote($tag))
                        ->where('id = ' . $reference_id);
                    break;
                case 'fabrik_forms':
                    $query->update('#__fabrik_forms')
                        ->set('label = ' . $db->quote($tag))
                        ->where('id = ' . $reference_id);
                    break;
                case 'fabrik_groups':
                    $query->update('#__fabrik_groups')
                        ->set('label = ' . $db->quote($tag))
                        ->where('id = ' . $reference_id);
                    break;
            }

            $db->setQuery($query);
            try {
                $updated = $db->execute();
            } catch (Exception $e) {
                JLog::add("Error trying to update label for $reference_table, $reference_id, $tag " . $e->getMessage(), JLog::ERROR, 'com_emundus.translations');
            }
        }

        return $updated;
    }
}
