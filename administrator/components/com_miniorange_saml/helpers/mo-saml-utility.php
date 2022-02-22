<?php
defined('_JEXEC') or die;
/** miniOrange enables user to log in using saml credentials.
    Copyright (C) 2015  miniOrange

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>
* @package 		miniOrange SAML
* @license		http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/
/**
This class contains all the utility functions

**/
class Mo_saml_Local_Util{

    public static function is_customer_registered()
    {
        $result      = (new Mo_saml_Local_Util)->_load_db_values('#__miniorange_saml_customer_details');
        $email       = isset($result['email']) ? $result['email'] : '';
        $customerKey = isset($result['customer_key']) ? $result['customer_key'] : '';
        if( ! $email || ! $customerKey || ! is_numeric( trim( $customerKey ) ) ) {
            return 0;
        }
        return 1;
    }


    public static function GetPluginVersion()
    {
        $db = JFactory::getDbo();
        $dbQuery = $db->getQuery(true)
        ->select('manifest_cache')
        ->from($db->quoteName('#__extensions'))
        ->where($db->quoteName('element') . " = " . $db->quote('com_miniorange_saml'));
        $db->setQuery($dbQuery);
        $manifest = json_decode($db->loadResult());
        return($manifest->version);
    }
 
    
    public static function check_empty_or_null( $value ) {
        return !isset($value) || empty($value) ? true : false;
    }
    
    public static function is_curl_installed() {
         return (in_array  ('curl', get_loaded_extensions())) ?  1 : 0;
    }

    public static function getHostname(){
        return "https://login.xecurify.com";
    }

    public function _load_db_values($table){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from($db->quoteName($table));
        $query->where($db->quoteName('id')." = 1");
        $db->setQuery($query);
        $default_config = $db->loadAssoc();
        return $default_config;
    }

    public static function generic_update_query($database_name, $updatefieldsarray){

        $db = JFactory::getDbo();

        $query = $db->getQuery(true);
        foreach ($updatefieldsarray as $key => $value)
        {
            $database_fileds[] = $db->quoteName($key) . ' = ' . $db->quote($value);
        }

        $query->update($db->quoteName($database_name))->set($database_fileds)->where($db->quoteName('id')." = 1");
        $db->setQuery($query);
        $db->execute();
    }

    public static function loadDBValues($table, $load_by, $col_name = '*', $id_name = 'id', $id_value = 1){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select($col_name);

        $query->from($db->quoteName($table));
        if(is_numeric($id_value)){
            $query->where($db->quoteName($id_name)." = $id_value");

        }else{
            $query->where($db->quoteName($id_name) . " = " . $db->quote($id_value));
        }
        $db->setQuery($query);

        if($load_by == 'loadAssoc'){
            $default_config = $db->loadAssoc();
        }
        elseif ($load_by == 'loadResult'){
            $default_config = $db->loadResult();
        }
        elseif($load_by == 'loadColumn'){
            $default_config = $db->loadColumn();
        }
        return $default_config;
    }
}
