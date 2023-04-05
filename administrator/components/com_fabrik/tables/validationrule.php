<?php
/**
 * Validation Rule Fabrik Table
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

require_once JPATH_ADMINISTRATOR . '/components/com_fabrik/tables/fabtable.php';

/**
 * Validation Rule Fabrik Table
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @since       3.0
 * @deprecated  not used?
 */
class FabrikTableValidationrule extends FabTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  &$db  database object
	 */

	public function __construct(&$db)
	{
		parent::__construct('#__fabrik_validation_rules', 'id', $db);
	}

    /**
     * Method to store a row in the database from the Table instance properties.
     * If a primary key value is set the row with that primary key value will be
     * updated with the instance property values.  If no primary key value is set
     * a new row will be inserted into the database with the properties from the
     * Table instance.
     *
     * @param   boolean  $updateNulls  True to update fields even if they are null.
     *
     * @return  boolean  True on success.
     *
     * @link    http://docs.joomla.org/Table/store
     * @since   11.1
     */
    public function store($updateNulls = true)
    {
        //return parent::store($updateNulls);
		if (!parent::store($updateNulls)) 
		{
			throw new RuntimeException('Fabrik error storing validationrule data: ' . $this->getError());
		}
		return true;
    }

}
