<?php
/**
 * Fabrik Admin Group Model
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2013 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.5
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Fabrik Admin View Model.Handles storing a 'view' to a json file
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @since       3.5
 */

class FabrikView implements JTableInterface
{

	public $params = '';
	/**
	 * Object constructor to set table and key fields.  In most cases this will
	 * be overridden by child classes to explicitly set the table and key fields
	 * for a particular database table.
	 *
	 * @param   JDatabaseDriver  $db     JDatabaseDriver object.
	 *
	 * @since   11.1
	 */
	public function __construct($db, $config = array())
	{
		$this->viewName = JArrayHelper::getValue($config, 'view');
		$this->subView = JArrayHelper::getValue($config, 'subview');
		$this->db = $db;
	}

	/**
	 * Method to get the primary key field name for the table.
	 *
	 * @param   boolean  $multiple  True to return all primary keys (as an array) or false to return just the first one (as a string).
	 *
	 * @return  mixed  Array of primary key field names or string containing the first primary key field.
	 *
	 * @link    http://docs.joomla.org/JTable/getKeyName
	 * @since   11.1
	 */
	public function getKeyName($multiple = false)
	{
		return 'id';
	}

	/**
	 * Method to load a row from the database by primary key and bind the fields
	 * to the JTable instance properties.
	 *
	 * @param   mixed    $keys   An optional primary key value to load the row by, or an array of fields to match.  If not
	 *                           set the instance property value is used.
	 * @param   boolean  $reset  True to reset the default values before loading the new row.
	 *
	 * @return  boolean  True if successful. False if row not found.
	 *
	 * @link    http://docs.joomla.org/JTable/load
	 * @since   11.1
	 * @throws  InvalidArgumentException
	 * @throws  RuntimeException
	 * @throws  UnexpectedValueException
	 */
	public function load($keys = null, $reset = true)
	{
		return true;
	}

	/**
	 * Method to bind an associative array or object to the JTable instance.This
	 * method only binds properties that are publicly accessible and optionally
	 * takes an array of properties to ignore when binding.
	 *
	 * @param   mixed  $src     An associative array or object to bind to the JTable instance.
	 * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return  boolean  True on success.
	 *
	 * @link    http://docs.joomla.org/JTable/bind
	 * @since   11.1
	 * @throws  InvalidArgumentException
	 */
	public function bind($src, $ignore = array())
	{
		$subview = $this->subView;
		$this->$subview = $src;
		echo "<pre>";print_r($this);exit;
		return true;
	}

	/**
	 * Method to perform sanity checks on the JTable instance properties to ensure
	 * they are safe to store in the database.  Child classes should override this
	 * method to make sure the data they are storing in the database is safe and
	 * as expected before storage.
	 *
	 * @return  boolean  True if the instance is sane and able to be stored in the database.
	 *
	 * @link    http://docs.joomla.org/JTable/check
	 * @since   11.1
	 */
	public function check()
	{
		return true;
	}

	/**
	 * Method to store a row in the database from the JTable instance properties.
	 * If a primary key value is set the row with that primary key value will be
	 * updated with the instance property values.  If no primary key value is set
	 * a new row will be inserted into the database with the properties from the
	 * JTable instance.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 *
	 * @link    http://docs.joomla.org/JTable/store
	 * @since   11.1
	 */
	public function store($updateNulls = false)
	{
		return true;
	}

	/**
	 * Override parent delete method to delete tags information.
	 *
	 * @param   mixed  $pk  An optional primary key value to delete.  If not set the instance property value is used.
	 *
	 * @return  boolean  True on success.
	 *
	 * @link    http://docs.joomla.org/JTable/delete
	 * @since   3.2
	 * @throws  UnexpectedValueException
	 */
	public function delete($pk = null)
	{
		return true;
	}

	/**
	 * Method to reset class properties to the defaults set in the class
	 * definition. It will ignore the primary key as well as any private class
	 * properties.
	 *
	 * @return  void
	 *
	 * @link    http://docs.joomla.org/JTable/reset
	 * @since   3.2
	 */
	public function reset()
	{

	}

	/**
	 * Method to get the JDatabaseDriver object.
	 *
	 * @return  JDatabaseDriver  The internal database driver object.
	 *
	 * @link    http://docs.joomla.org/JTable/getDBO
	 * @since   3.2
	 */
	public function getDbo()
	{
		return $this->db;
	}

	public function getProperties($public = true)
	{
		return array();
	}
}
