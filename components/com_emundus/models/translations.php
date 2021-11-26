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

    public function insertTranslation($tag,$override,$lang_code,$location = '',$type='fabrik',$reference_table = '',$reference_id = 0){
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
                $this->_db->quote($reference_table),
                $reference_id,
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
                return $this->insertTranslation($tag,$override,$lang_code);
            }
        } catch(Exception $e){
            JLog::add('Problem when try to update translation ' . $tag . ' into file ' . $location . ' with error : ' . $e->getMessage(),JLog::ERROR, 'com_emundus.translations');
            return false;
        }
    }
}
