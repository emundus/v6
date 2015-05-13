<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

class AdmintoolsTableIpautobanhistory extends F0FTable
{
	var $until = '0000-00-00 00:00:00';

	public function __construct($table, $key, &$db)
	{
		parent::__construct('#__admintools_ipautobanhistory', 'id', $db);
	}

	public function delete($oid = null)
	{
		$k = $this->_tbl_key;

		if ($oid)
		{
			$this->$k = $oid;
		}

		if (!defined('F0F_INCLUDED'))
		{
			require_once JPATH_LIBRARIES . '/f0f/include.php';
		}

		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->delete($db->quoteName($this->_tbl))
			->where($db->quoteName($this->_tbl_key) . ' = ' . $db->quote($this->$k));
		$this->_db->setQuery($query);

		if ($this->_db->execute())
		{
			return true;
		}
		else
		{
			$this->setError($this->_db->getErrorMsg());

			return false;
		}
	}
}
