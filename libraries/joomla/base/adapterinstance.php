<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Base
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Adapter Instance Class
 *
 * @since       1.6
 * @deprecated  5.0 Will be removed without replacement
 */
class JAdapterInstance extends JObject
{
	/**
	 * Parent
	 *
	 * @var    JAdapter
	 * @since  1.6
	 */
	protected $parent = null;

	/**
	 * Database
	 *
	 * @var    JDatabaseDriver
	 * @since  1.6
	 */
	protected $db = null;

	/**
	 * Constructor
	 *
	 * @param   JAdapter         $parent   Parent object
	 * @param   JDatabaseDriver  $db       Database object
	 * @param   array            $options  Configuration Options
	 *
	 * @since   1.6
	 */
	public function __construct(JAdapter $parent, JDatabaseDriver $db, array $options = array())
	{
		// Set the properties from the options array that is passed in
		$this->setProperties($options);

		// Set the parent and db in case $options for some reason overrides it.
		$this->parent = $parent;

		// Pull in the global dbo in case something happened to it.
		$this->db = $db ?: JFactory::getDbo();
	}

	/**
	 * Retrieves the parent object
	 *
	 * @return  JAdapter
	 *
	 * @since   1.6
	 */
	public function getParent()
	{
		return $this->parent;
	}
}
