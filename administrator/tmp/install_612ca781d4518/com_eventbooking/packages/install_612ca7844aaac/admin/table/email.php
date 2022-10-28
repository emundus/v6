<?php

/**
 * Email Table Class
 */
class EventbookingTableEmail extends JTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  $db  Database connector object
	 */
	public function __construct(& $db)
	{
		parent::__construct('#__eb_emails', 'id', $db);
	}
}
