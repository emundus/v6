<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

/**
 * Database class for handling the joomfish contents
 *
 * @package joomfish
 * @subpackage administrator
 * @copyright 2003 - 2011, Think Network GmbH, Munich
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Revision: 1551 $
 * @author Alex Kempkens <joomfish@thinknetwork.com>
 */
class FalangContent extends JTable  {
	/** @var int Primary ke */
	var $id=null;
	/** @var int Reference id for the language */
	var $language_id=null;
	/** @var int Reference id for the original content */
	var $reference_id=null;
	/** @var int Reference table of the original content */
	var $reference_table=null;
	/** @var int Reference field of the original content */
	var $reference_field=null;
	/** @var string translated value*/
	var $value=null;
	/** @var string original value for equals check*/
	var $original_value=null;
	/** @var string original value for equals check*/
	var $original_text=null;
	/** @var int user that checked out the falangContent*/
	//	var $checked_out=null;					// not yet supported
	/** @var datetime time when the checkout was done*/
	//	var $checked_out_time=null;			// not yet supported
	/** @var date Date of last modification*/
	var $modified=null;
	/** @var string Last translator*/
	var $modified_by=null;
	/** @var boolean Flag of the translation publishing status*/
	var $published=false;

	/** Standard constructur
	*/
	public function __construct( &$db) {
		parent::__construct( '#__falang_content', 'id', $db );
	}

	/**
	 * Bind the content of the newValues to the object. Overwrite to make it possible
	 * to use also objects here
	 */
	function bind( $newValues, $ignore = array() ) {
		if (is_array( $newValues )) {
			return parent::bind( $newValues,$ignore );
		} else {
			foreach (get_object_vars($this) as $k => $v) {
				if ( isset($newValues->$k) ) {
					$this->$k = $newValues->$k;
				}
			}
		}
		return true;
	}


	/**
	 * Validate language information
	 * Name and Code name are mandatory
	 * activated will automatically set to false if not set
	 */
	function check() {
		if (trim( $this->language_id ) == '') {
			$this->_error = JText::_('NO_LANGUAGE_DBERROR');
			return false;
		}

		return true;
	}

	function toString() {
		$retString = "<p>content field:<br />";
		$retString .= "id=$this->id; language_id=$this->language_id<br>";
		$retString .= "reference_id=$this->reference_id, reference_table=$this->reference_table, reference_field=$this->reference_field<br>";
		$retString .= "value=>" .htmlspecialchars($this->value). "<<br />";
		$retString .= "original_value=>" .htmlspecialchars($this->original_value). "<<br />";
		$retString .="modified=$this->modified, modified_by=$this->modified_by, published=$this->published</p>";

		return $retString;
	}
}
