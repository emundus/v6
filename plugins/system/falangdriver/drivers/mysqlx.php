<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

require_once( JPATH_SITE.'/components/com_falang/helpers/defines.php' );
require_once( JPATH_SITE.'/components/com_falang/helpers/falang.class.php' );
require_once( JPATH_SITE."/administrator/components/com_falang/classes/FalangManager.class.php");

/**
 * MySQLi database driver
 *
 * @package     Joomla.Platform
 * @subpackage  Database
 * @see         http://php.net/manual/en/book.mysql.php
 * @since       11.1
 */

class JOverrideDatabase extends JDatabaseDriverMysql
{

    function __construct($options){
        $db =  JFactory::getDBO();

        // support for recovery of existing connections (Martin N. Brampton)
        if (isset($this->_options)) $this->_options = $options;

        $select		= array_key_exists('select', $options)	? $options['select']	: true;
        $database	= array_key_exists('database',$options)	? $options['database']	: '';

        // perform a number of fatality checks, then return gracefully
        if (!function_exists( 'mysql_connect' )) {
            $this->_errorNum = 1;
            $this->_errorMsg = 'The MySQL adapter "mysql" is not available.';
            return;
        }

		// connect to the server
		$this->connection = $db->get("connection");

        // finalize initialization
        parent::__construct($options);

        // select the database
        if ( $select ) {
            $this->select($database);
        }

    }

    function _getFieldCount(){
        if (!is_resource($this->cursor)){
            // This is a serious problem since we do not have a valid db connection
            // or there is an error in the query
            $error = JError::raiseError( 500, JTEXT::_('No valid database connection:') .$this->getErrorMsg());
            return $error;
        }

        $fields = mysql_num_fields($this->cursor);
        return $fields;
    }

    function _getFieldMetaData($i){
        $meta = mysql_fetch_field($this->cursor, $i);
        return $meta;
    }

	public function setRefTables(){

		$pfunc = $this->_profile();

		if($this->cursor===true || $this->cursor===false) {
			$pfunc = $this->_profile($pfunc);
			return;
		}

		// only needed for selects at present - possibly add for inserts/updates later
        if (is_a($this->sql,'JDatabaseQueryMySQL')) {
           $tempsql = $this->sql->__toString();
        } else {
   		   $tempsql = $this->sql;
        }
        //use tempprefixsql for mysql only driver
        $tempprefixsql = $this->replacePrefix((string) $tempsql);


        if (strpos(strtoupper(trim($tempsql)),"SELECT")!==0) {
			$pfunc = $this->_profile($pfunc);
			return;
		}

		$config = JFactory::getConfig();

		// get column metadata
		$fields = $this->_getFieldCount();

		if ($fields<=0) {
			$pfunc = $this->_profile($pfunc);
			return;
		}

		$this->_refTables=array();
		$this->_refTables["fieldTablePairs"]=array();
		$this->_refTables["tableAliases"]=array();
		$this->_refTables["reverseTableAliases"]=array();
		$this->_refTables["fieldAliases"]=array();
		$this->_refTables["fieldTableAliasData"]=array();
		$this->_refTables["fieldCount"]=$fields;
		// Do not store sql in _reftables it will disable the cache a lot of the time

		$tableAliases = array();
		for ($i = 0; $i < $fields; ++$i) {
			$meta = $this->_getFieldMetaData($i);
			if (!$meta) {
				echo JText::_(PLG_SYSTEM_FALANGDRIVER_META_NO_INFO);
			}
			else {
				$tempTable =  $meta->table;
				// if I have already found the table alias no need to do it again!
				if (array_key_exists($tempTable,$tableAliases)){
					$value = $tableAliases[$tempTable];
				}
				// mysqli only
                else if (isset($meta->orgtable)){
                    $value = $meta->orgtable;
                    if (isset($this->_table_prefix) && strlen($this->_table_prefix)>0 && strpos($meta->orgtable,$this->_table_prefix)===0) $value = substr($meta->orgtable, strlen( $this->_table_prefix));
                    $tableAliases[$tempTable] = $value;
				}
				else {
                    if (!isset($tempTable) || strlen($tempTable)==0) {
                        continue;
                    }
                    //echo "<br>Information for column $i of ".($fields-1)." ".$meta->name." : $tempTable=";
                    $tempArray=array();
                    //sbou TODO optimize this section
                    $prefix = $this->_table_prefix;

                    preg_match_all("/`?$prefix(\w+)`?\s+(?:AS\s)?+`?".$tempTable."`?[,\s]/i",$tempprefixsql, $tempArray, PREG_PATTERN_ORDER);
                    //preg_match_all("/`?$prefix(\w+)`?\s+AS\s+`?".$tempTable."`?[,\s]/i",$this->_sql, $tempArray, PREG_PATTERN_ORDER);
                    if (count($tempArray)>1 && count($tempArray[1])>0) $value = $tempArray[1][0];
                    else $value = null;
                    if (isset($this->_table_prefix) && strlen($this->_table_prefix)>0 && strpos($tempTable,$this->_table_prefix)===0) $tempTable = substr($tempTable, strlen( $this->_table_prefix));
                    $value = $value?$value:$tempTable;
                    $tableAliases[$tempTable]=$value;
				}

                if ((!($value=="session" || strpos($value,"jf_")===0)) && $this->translatedContentAvailable($value)){
                    /// ARGH !!! I must also look for aliases for fieldname !!
                    if (isset($meta->orgname)){
                        $nameValue = $meta->orgname;
                    }
                    else {
                        $tempName = $meta->name;
                        $tempArray=array();
                        // This is a bad match when we have "SELECT id" at the start of the query
                        preg_match_all("/`?(\w+)`?\s+(?:AS\s)?+`?".$tempName."`?[,\s]/i",$tempprefixsql, $tempArray, PREG_PATTERN_ORDER);
                        //preg_match_all("/`?(\w+)`?\1s+AS\s+`?".$tempName."`?[,\s]/i",$this->_sql, $tempArray, PREG_PATTERN_ORDER);
                        if (count($tempArray)>1 && count($tempArray[1])>0) {
                            //echo "$meta->name is an alias for ".$tempArray[1][0]."<br>";
                            // must ignore "SELECT id"
                            if (strtolower($tempArray[1][0])=="select"){
                                $nameValue = $meta->name;
                            }
                            else {
                                $nameValue = $tempArray[1][0];
                            }
                        }
                        else $nameValue = $meta->name;
                    }

                    if (!array_key_exists($value,$this->_refTables["tableAliases"])) $this->_refTables["tableAliases"][$value]=$meta->table;
                    if (!array_key_exists($meta->table,$this->_refTables["reverseTableAliases"])) $this->_refTables["reverseTableAliases"][$meta->table]=$value;

                    // I can't use the field name as the key since it may not be unique!
                    if (!in_array($value,$this->_refTables["fieldTablePairs"])) $this->_refTables["fieldTablePairs"][]=$value;
                    if (!array_key_exists($nameValue,$this->_refTables["fieldAliases"])) $this->_refTables["fieldAliases"][$meta->name]=$nameValue;

                    // Put all the mapping data together so that everything is in sync and I can check fields vs aliases vs tables in one place
                    $this->_refTables["fieldTableAliasData"][$i]=array("fieldNameAlias"=>$meta->name, "fieldName"=>$nameValue,"tableNameAlias"=>$meta->table,"tableName"=>$value);

				}

			}
		}
		$pfunc = $this->_profile($pfunc);
	}

    /**
     * Return the actual SQL Error number
     *
     * @return  integer  The SQL Error number
     *
     * @since   4.0.0
     */
    protected function getErrorNumber()
    {
        return (int) mysql_errno($this->connection);
    }
}
