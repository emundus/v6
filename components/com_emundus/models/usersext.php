<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Extension that we need in order to handle all the new information that we collect.
 */
class TableUsersext extends JTable
{

	var $id 		= null;
	var $user_id 		= null;
	var $_password	= null;
	var $firstname 	= null;
	var $lastname 	= null;
	var $profile 	= null;
	var $schoolyear	= null;
	var $registerDate = null;
	var $university_id = null;
	
	/**
	 * We extend/override the parent.
	 *
	 * @param unknown_type $db
	 */
	function __construct(&$db){
		parent::__construct( '#__emundus_users', 'id', $db );
	}
	
	/**
	 * Custom validations and empty checking if the default joomla javascript
	 * validation is to be overridden.
	 *
	 * @return boolean true if everything went well, false otherwise.
	 */
	function validateMe(){
		if(empty($this->firstname)){
			$this->_error = JText::_('firstname_err');
			return false;
		}
		
		if(empty($this->lastname)){
			$this->_error = JText::_('lastname_err');
			return false;
		}
		
		return true;
		
	}

	/**
	 * Extending the parent load function.
	 */
	function load($id_value = null, $id_field = false){
		$temp = $this->_tbl_key;
		$this->_tbl_key = $id_field == false ? $this->_tbl_key : $id_field;
		$result = parent::load($id_value);
		$this->_tbl_key = $temp;
		return $result;
	}
	
	/**
	 * Our very own password checker that will call our parent jos_users table.
	 */
	function checkPassword($password){
		jimport('joomla.user.helper');
		
		$result = $this->getParent();
		
		//print_r($result);
		
		if($result){
			$parts	= explode( ':', $result->password );
			$crypt	= $parts[0];
			$salt	= @$parts[1];
			$testcrypt = JUserHelper::getCryptedPassword($password, $salt);
			
			if ($crypt == $testcrypt) {
				$this->_password = $password;
				return true;
			}
		}

		return false;
		
	}
	
	/**
	 * Returns an array of all public variables in an instance of this class.
	 *
	 * @return array The resultant array.
	 */
	function toArray(){
		$rarr = array();
		foreach($this as $key => $value)
			$rarr[$key] = $value;
		return $rarr;
	}
	
	/**
	 * We save an array representation of ourself in the session.
	 */
	function sessSave(&$session){
		$session->set('usersext', $this->toArray());	
	}
	
	/**
	 * Extending the bind function so that we can create instances
	 * of ourself from array input.
	 */
	function bind($from){
		$this->_password = $from['password'];
		return parent::bind($from);
	}
	
	/**
	 * Returns an instance of our parent (jos_users), I.E. the default user representation that we
	 * belong to. 
	 */
	function getParent(){
		$db = JFactory::getDBO();
		$query = "SELECT * FROM `#__users` WHERE id = {$this->id}";
		$db->setQuery( $query );
		return $db->loadObject();
	}
	
	/**
	 * Returns a value from our parent table (jos_users)
	 */
	function getParentField($field){
		$parent = $this->getParent();
		return $parent->$field;
	}
	
	/**
	 * Create an instance of ourself from array data stored in the session.
	 */
	function sessLoad(&$session){
		$this->bind( $session->get('usersext') );
	}
}
?>
