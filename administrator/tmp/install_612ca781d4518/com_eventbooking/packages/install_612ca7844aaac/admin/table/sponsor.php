<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

/**
 * Class EventbookingTableSponsor
 *
 * @property $id
 * @property $event_id
 * @property $name
 * @property $logo
 * @property $website
 * @property $ordering
 */
class EventbookingTableSponsor extends JTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  $db  Database connector object
	 */
	public function __construct(& $db)
	{
		parent::__construct('#__eb_sponsors', 'id', $db);
	}
}
