<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

include_once(dirname(__FILE__).DS."ContentElementTableField.php");

/**
 * Description of a content element table.
 *
 * @package joomfish
 * @subpackage administrator
 * @copyright 2003 - 2011, Think Network GmbH, Munich
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Revision: 1551 $
 * @author Alex Kempkens <joomfish@thinknetwork.com>
 */
class ContentElementTable {
	var $Name;
	var $Fields;
	var $Filter;

	/** Standard constructor
	*/
	public function __construct($tableElement){
		$this->Name = trim( $tableElement->getAttribute( 'name' ) );

		$tableFields = $tableElement->getElementsByTagName( 'field' );
		$this->Fields =array();
		$this->IndexedFields =array();
		foreach( $tableFields as $tablefieldElement ) {
			$field = new ContentElementTablefield( $tablefieldElement );
			$this->Fields[] = $field;
			$this->IndexedFields[$field->Name] = $field;
		}

		$filterElement = $tableElement->getElementsByTagName('filter');
		if( $filterElement && $filterElement->length>0 ) {
			$this->Filter = $filterElement->item(0)->textContent;
		}
	}

	/** Retrieves one field based on the name
	 * @param	string	Fieldname
	 * @return	object	field
	 */
	function getField( $name ) {
		$ret_field = null;
		foreach( $this->Fields  as $field ) {
			if ($field->Name == $name ) {
				$ret_field = $field;
				break;
			}
		}

		return $ret_field;
	}
}

