<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die;

/**
 * Class EventbookingTableSpeaker
 *
 * @property $id
 * @property $event_id
 * @property $name
 * @property $description
 * @property $facebook
 * @property $twitter
 * @property $linkedin
 * @property $url
 * @property $ordering
 */
class EventbookingTableSpeaker extends JTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  $db  Database connector object
	 */
	public function __construct(& $db)
	{
		parent::__construct('#__eb_speakers', 'id', $db);
	}
}
