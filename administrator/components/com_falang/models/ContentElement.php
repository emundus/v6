<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

include_once(dirname(__FILE__).DS."ContentElementTable.php");

/**
 * Content element class based on the xml file
 *
 * @package joomfish
 * @subpackage administrator
 * @copyright 2003 - 2011, Think Network GmbH, Munich
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Revision: 1580 $
 * @author Alex Kempkens <joomfish@thinknetwork.com>
 */
class ContentElement {
	var $_xmlFile;
	var $checked_out=false;
	var $Name='';
	var $Author='';
	var $Version='';
	var $Description='';
	var $PrimaryKey = "id";

	var $referenceInformation;

	/**	field (if any) that keyword	filters apply to*/
	var $_keywordFilter=null;
	var $_categoryFilter=null;
	var $_authorFilter=null;


	/** Standard constructor, which loads already standard information
	 * for easy and direct access
	*/
	public function __construct($xmlDoc){
		$this->_xmlFile = $xmlDoc;

		if( isset($this->_xmlFile) ) {
			$valueElement = $this->_xmlFile->getElementsByTagName('name')->item(0);
			$this->Name = trim($valueElement->textContent);

			$valueElement = $this->_xmlFile->getElementsByTagName('author')->item(0);
			$this->Author = trim($valueElement->textContent);

			$valueElement = $this->_xmlFile->getElementsByTagName('version')->item(0);
			$this->Version = trim($valueElement->textContent);

			$valueElement = $this->_xmlFile->getElementsByTagName('description')->item(0);
			$this->Description = trim($valueElement->textContent);

		}
	}

	/** Type of reference
	*/
	function getReferenceType() {
		if( !isset($this->referenceInformation["type"]) && isset($this->_xmlFile) ) {
			$tableElement = $this->_xmlFile->getElementsByTagName('reference')->item(0);
			$tableName = trim($tableElement->getAttribute( 'type' ));
			$this->referenceInformation["type"] = $tableName;
		}

		return $this->referenceInformation["type"];
	}

    /** Type of reference
     * v 1.4.5
     */
    function getFrontEdit() {
        if( !isset($this->referenceInformation["frontedit"]) && isset($this->_xmlFile) ) {
            $tableElement = $this->_xmlFile->getElementsByTagName('reference')->item(0);
            $frontedit = trim($tableElement->getAttribute( 'frontedit' ));
            $this->referenceInformation["frontedit"] = $frontedit;
        }
        return $this->referenceInformation["frontedit"];
    }

	function getQuickjumps(){
		$allQuickjump = array();
		if(isset($this->_xmlFile) ) {
			$node = $this->_xmlFile->getElementsByTagName('quickjumps');
			if (isset($node) && !empty($node->length)){
				$quickjumps = $this->_xmlFile->getElementsByTagName('quickjump');
				foreach ($quickjumps as $quickjump){
					$allQuickjump[]=trim($quickjump->textContent);
				}

			}
		}
		return $allQuickjump;

	}

	/**
	 * Public function to return array of filters included in contentelement file
	 */
	function getAllFilters(){
		$allFilters = array();
		if(isset($this->_xmlFile) ) {
			$fElement = $this->_xmlFile->getElementsByTagName('translationfilters')->item(0);
			if (!isset($fElement) || !$fElement->hasChildNodes()){
				return $allFilters;
			}
			foreach ($fElement->childNodes as $child){
				$type = $child->nodeName;
				$filter = "_$type"."Filter";
				$this->$filter=$child->textContent;
				$allFilters[$type]=trim($this->$filter);
			}
		}
		return $allFilters;

	}

	/**
	 * function that returns filter string and handles getting filter info from xmlfile if needed
	 *
	 */
	function getFilter($type){
		$filter = "_$type"."Filter";
		if( !isset($this->$filter) && isset($this->_xmlFile) ) {
			$xpath = new DOMXPath($this->_xmlFile);
			$fElement = $xpath->query('//translationfilters/'.$type);
			if (!isset($fElement)){
				$this->$filter=false;
				return $this->$filter;
			}
			$this->$filter=trim($fElement->textContent);
		}
		return $this->$filter;

	}

	/**
	 * returns translation filter keyword field (if any)
	 */
	function getKeywordFilter() {
		return $this->_getFilter("keyword");
	}

	/**
	 *  returns category filter fieldname (if any)
	 */
	function getCategoryFilter() {
		return $this->_getFilter("category");
	}

	/**
	 *  returns author filter fieldname (if any)
	 */
	function getAuthorFilter() {
		return $this->_getFilter("author");
	}

	/** Name of the refering table
	*/
	function getTableName() {
		if( !isset($this->referenceInformation["tablename"]) && isset($this->_xmlFile) ) {
			$xpath = new DOMXPath($this->_xmlFile);
			$tableElement = $xpath->query('//reference/table')->item(0);

			$tableName = trim($tableElement->getAttribute( 'name' ));
			$this->referenceInformation["tablename"] = strtolower($tableName);
		}

		return $this->referenceInformation["tablename"];
	}

	/**
	 * Name of reference id (in other words the primary key)
	 */
	function getReferenceId() {
		if( isset($this->referenceInformation["tablename"]) && isset($this->_xmlFile) ) {
			$xpath = new DOMXPath($this->_xmlFile);
			$tableElement = $xpath->query('//reference/table')->item(0);
			$tableFields = $tableElement->getElementsByTagName('field');

			foreach ($tableFields as $field) {
				if (trim($field->getAttribute('type'))=="referenceid") {
					$refid = trim($field->getAttribute('name'));
					if ($refid!=null) return $refid;
					else return "id";
				}
			}
		}
		return "id";
	}


	/** Array of the field elements in the table
	*/
	function &getTable() {
		if( !isset($this->referenceInformation["table"]) && isset($this->_xmlFile) ) {
			$xpath = new DOMXPath($this->_xmlFile);
			$tableElement = $xpath->query('//reference/table')->item(0);

			$this->referenceInformation["table"] = new ContentElementTable( $tableElement );
		}

		return $this->referenceInformation["table"];
	}

	/** Generating the sql statement to retrieve the information
	 * from the database
	 */
	function createContentSQL( $idLanguage=-1, $contentid=null, $limitStart=-1, $maxRows=-1 , $filters=array()) {
		$db = JFactory::getDBO();
		$sqlFields=null;
		$where=array();
		$order=null;
		$join=null;
		$contentTable = $this->getTable();
		foreach ($filters as $filter) {
			$sqlFilter= $filter->_createFilter($this);
			if ($sqlFilter!="") $where[]=$sqlFilter;
		}
		foreach( $contentTable->Fields as $tableField ) {
			// Based on the types we might want to have special names ;-)
			switch ($tableField->Type) {
				case "referenceid":
					$contentid_exist = (isset($contentid) && $contentid!=-1 );
					if( strtolower($tableField->Name) != "id" ) {
						$sqlFields[] = 'c.' .$tableField->Name. ' as id';
						if( $contentid_exist) $where[] = 'c.' .$tableField->Name. '=' .$db->Quote($contentid) ;
					}
					else {
						if( $contentid_exist ) $where[] = 'c.id=' .$contentid ;
					}
					$join[] = 'c.' .$tableField->Name. '=jfc.reference_id';
					break;
				case "titletext":
					if( strtolower($tableField->Name) != "title" ) {
						$sqlFields[] = 'c.' .$tableField->Name. ' as title';
					}
					//specific case if titletext is set to not to be translatable.
					if(!empty($tableField->Translate)){
						$join[] = "jfc.reference_field='" .$tableField->Name. "'";
					}
					$order[] = 'c.' .$tableField->Name;
					break;
				case "modified_date":
					if( strtolower($tableField->Name) != "modified_date" ) {
						$sqlFields[] = 'c.' .$tableField->Name. ' as modified_date';
					}
					break;
				case "checked_out_by":
					if( strtolower($tableField->Name) != "check_out" ) {
						$sqlFields[] = 'c.' .$tableField->Name. ' as check_out';
					}
					break;
			}

			// I want to have each field with his original name in the select
			// so the special fields will be only addon's!
			// Reason: To grap the data later it's more easy to refer to the original names of the XML file
			$sqlFields[] = 'c.' .$tableField->Name. '';
		}

		$sqlFields[] = "jfc.id as jfc_id";
		$sqlFields[] = "jfc.value as titleTranslation";
		$sqlFields[] = "jfc.modified as lastchanged";
		$sqlFields[] = 'jfc.published as published';
		$sqlFields[] = 'jfc.language_id';
		$sqlFields[] = 'jfl.title as language';
		$sqlFields[] = "jfc.reference_id as jfc_refid";
		$join[] = "jfc.reference_table='$contentTable->Name'";
		// Now redundant
		/*
		if( isset($contentid) && $contentid!=-1 ) {
		$where[] = 'c.id=' .$contentid;
		}
		*/
		if( isset($idLanguage) && $idLanguage!="" && $idLanguage!=-1 ) {
			if( $idLanguage=="NULL" ) {
				$where[] = "jfc.language_id IS NULL";
			} else {
				$join[] = "jfc.language_id=$idLanguage";
			}
		}

		if( $contentTable->Filter != '' ) {
			$where[] = $contentTable->Filter;
		}


		$sql = "SELECT " .implode( ', ', $sqlFields )
		. "\nFROM #__" .$contentTable->Name. ' as c'
		. "\nLEFT JOIN #__falang_content as jfc ON " .implode( ' AND ', $join )
		. "\nLEFT JOIN #__languages as jfl ON jfc.language_id=jfl.lang_id"
		. (count( $where ) ? "\nWHERE " . implode( ' AND ', $where ) : "")
		. (count( $order ) ? "\nORDER BY " . implode( ', ', $order ) : "");

		if( $limitStart!=-1 && $maxRows>0) {
			$sql .= "\nLIMIT $limitStart, $maxRows";
		}

        //echo "sql = <pre>$sql</pre><br />";

		return $sql;
	}

	/** Generating the sql statement to retrieve the orphans information from the database
	 */
	function createOrphanSQL( $idLanguage=-1, $contentid=null, $limitStart=-1, $maxRows=-1 , $filters=array()) {


		$sqlFields=null;
		$sqlFields[] = "jfc.id as jfc_id";
		$sqlFields[] = "jfc.reference_id as jfc_refid";
		$sqlFields[] = "jfc.value as titleTranslation";
		$sqlFields[] = "jfc.modified as lastchanged";
		$sqlFields[] = 'jfc.published as published';
		$sqlFields[] = 'jfc.language_id';
		$sqlFields[] = 'jfl.title as language';
		$sqlFields[] = 'jfc.original_text as original_text';

		$where=array();
		$order=array();
		$join=null;
		$contentTable = $this->getTable();
		foreach ($filters as $filter) {
			$sqlFilter= $filter->_createFilter($this);
			if ($sqlFilter!="") $where[]=$sqlFilter;
		}
		foreach( $contentTable->Fields as $tableField ) {
			// Based on the types we might want to have special names ;-)
			switch ($tableField->Type) {
				case "referenceid":
					$contentid_exist = (isset($contentid) && $contentid!=-1 );
					if( strtolower($tableField->Name) != "id" ) {
						$sqlFields[] = 'c.' .$tableField->Name. ' as id';
						if( $contentid_exist) $where[] = 'c.' .$tableField->Name. '=' .$contentid ;
					}
					else {
						if( $contentid_exist ) $where[] = 'c.id=' .$contentid ;
					}
					$join[] = 'c.' .$tableField->Name. '=jfc.reference_id ';
					$where[]  = 'c.' .$tableField->Name. ' IS NULL ';
					$sqlFields[] = 'c.' .$tableField->Name. '';
					break;
				case "titletext":
					if( strtolower($tableField->Name) != "title" ) {
						$sqlFields[] = 'c.' .$tableField->Name. ' as title';
					}
					//$join[] = "jfc.reference_field='" .$tableField->Name. "'";
					$where[] = "jfc.reference_field='" .$tableField->Name. "'";
					$sqlFields[] = 'c.' .$tableField->Name. '';
					//					$order[] = 'c.' .$tableField->Name;
					break;
			}
		}

		//$join[] = "jfc.reference_table='$contentTable->Name'";
		$where[] = "jfc.reference_table='$contentTable->Name'";
		if( !isset($idLanguage) || ($idLanguage!="" && $idLanguage!=-1 )) {
			$where[] = "jfc.language_id=$idLanguage";
		}

		$sql = "SELECT " .implode( ', ', $sqlFields )
		. "\nFROM #__falang_content as jfc"
		. "\nLEFT JOIN #__" .$contentTable->Name. ' as c ON '.implode( ' AND ', $join )
		. "\nLEFT JOIN #__languages as jfl ON jfc.language_id=jfl.lang_id"
		. (count( $where ) ? "\nWHERE " . implode( ' AND ', $where ) : "")
		. (count( $order ) ? "\nORDER BY " . implode( ', ', $order ) : "");

		if( $limitStart!=-1 ) {
			$sql .= "\nLIMIT $limitStart, $maxRows";
		}
		//echo "orphansql = $sql<br />";

		return $sql;
	}

	/** Generating the sql statement to count the information
	 */
	function countContentSQL($idLanguage=-1, $filters=array()) {
		$contentTable = $this->getTable();
/*
		$where=null;

		// Add standard filters
		foreach ($filters as $filter) {
			$sqlFilter= $filter->_createFilter($this);
			if ($sqlFilter!="") $where[]=$sqlFilter;
		}
		if( $contentTable->Filter != '' ) {
			$where[] = $contentTable->Filter;
		}

		$sql = "SELECT *"
		. "\nFROM #__" .$contentTable->Name. ' as c'
		. (count( $where ) ? "\nWHERE " . implode( ' AND ', $where ) : "");
*/
		/* Try to simplify the count queries.
		Check only on original table including the standard filters as we assume that*/

		$join=array();
		$where=array();
		$referencefield = "";
		
		foreach( $contentTable->Fields as $tableField ) {
			// Based on the types we might want to have special names ;-)
			if ( $tableField->Type == "referenceid" ) {
				$join[] = 'c.' .$tableField->Name. '=jfc.reference_id';
				$referencefield ='c.' .$tableField->Name;
			}
		}

		$sqlFields[] = "COUNT(distinct $referencefield)";
		$join[] = "jfc.reference_table='$contentTable->Name'";
		if( isset($idLanguage) && $idLanguage!=-1 ) {
			if( $idLanguage=='NULL' ) {
				$where[] = "jfc.language_id IS NULL";
			} else {
				$join[] = "jfc.language_id=$idLanguage";
			}
		}

		foreach ($filters as $filter) {
			$sqlFilter= $filter->_createFilter($this);
			if ($sqlFilter!="") $where[]= $sqlFilter;
		}
		if( $contentTable->Filter != '' ) {
			$where[] = $contentTable->Filter;
		}

		$sql = "SELECT " .implode( ', ', $sqlFields )
		. "\nFROM #__" .$contentTable->Name. ' as c'
		. "\nLEFT JOIN #__falang_content as jfc ON " .implode( ' AND ', $join )
		. (count( $where ) ? "\nWHERE " . implode( ' AND ', $where ) : "");


		//echo "<pre>count-sql = $sql</pre><br />";
		return $sql;

	}

	/**
	 * Returing the number of elements corresponding with the information of the class
	 * @return total number of elements
	 */
	function countReferences( $idLanguage=-1, $filters=array() ) {
		$db = JFactory::getDBO();
		$db->setQuery( $this->countContentSQL( $idLanguage, $filters ) );
		$count=$db->loadResult();
		return $count;
	}
}
