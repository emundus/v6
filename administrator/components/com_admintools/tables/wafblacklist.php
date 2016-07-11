<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

class AdmintoolsTableWafblacklist extends F0FTable
{
	public function __construct($table, $key, &$db)
	{
		parent::__construct('#__admintools_wafblacklists', 'id', $db);
	}
}
