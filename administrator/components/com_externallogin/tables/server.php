<?php

/**
 * @package     External_Login
 * @subpackage  Component
 * @author      Christophe Demko <chdemko@gmail.com>
 * @author      Ioannis Barounis <contact@johnbarounis.com>
 * @author      Alexandre Gandois <alexandre.gandois@etudiant.univ-lr.fr>
 * @copyright   Copyright (C) 2008-2018 Christophe Demko, Ioannis Barounis, Alexandre Gandois. All rights reserved.
 * @license     GNU General Public License, version 2. http://www.gnu.org/licenses/gpl-2.0.html
 * @link        http://www.chdemko.com
 */

// No direct access
defined('_JEXEC') or die;

// Import Joomla table library
jimport('joomla.database.table');

/**
 * Server Table class of External Login component
 *
 * @package     External_Login
 * @subpackage  Component
 *
 * @since       0.0.1
 */
class ExternalloginTableServer extends JTable
{
	/**
	 * Constructor
	 *
	 * @param   object  $db  Database connector object
	 *
	 * @see     JTable::__construct
	 *
	 * @since   2.0.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__externallogin_servers', 'id', $db);
	}

	/**
	 * Overloaded load function.
	 *
	 * @param   mixed    $keys   An optional primary key value to load the row by, or an array of fields to match.  If not
	 * set the instance property value is used.
	 * @param   boolean  $reset  True to reset the default values before loading the new row.
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     JTable::load
	 *
	 * @since   2.0.0
	 */
	public function load($keys = null, $reset = true)
	{
		if (parent::load($keys, $reset))
		{
			if (isset($this->params))
			{
				$this->params = new JRegistry($this->params);
			}
			else
			{
				$this->params = new JRegistry;
			}

			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Overloaded store function.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 *
	 * @link	http://docs.joomla.org/JTable/store
	 * @since   2.0.0
	 */
	public function store($updateNulls = false)
	{
		if ($this->ordering == 0)
		{
			$query = $this->_db->getQuery(true);
			$query->select('MAX(ordering)');
			$query->from('#__externallogin_servers');
			$this->_db->setQuery($query);
			$this->ordering = $this->_db->loadResult() + 1;
		}

		if (is_array($this->params))
		{
			$this->params = (string) new JRegistry($this->params);
		}

		if (parent::store($updateNulls))
		{
			return $this->reorder();
		}
		else
		{
			return false;
		}
	}
}
