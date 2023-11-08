<?php
defined('_JEXEC') or die('Access Deny');

class modEmundusQcmHelper
{

	public function __construct()
	{
		$this->offset = JFactory::getApplication()->get('offset', 'UTC');
		try {
			$dateTime  = new DateTime(gmdate("Y-m-d H:i:s"), new DateTimeZone('UTC'));
			$dateTime  = $dateTime->setTimezone(new DateTimeZone($this->offset));
			$this->now = $dateTime->format('Y-m-d H:i:s');
		}
		catch (Exception $e) {
			echo $e->getMessage() . '<br />';
		}
	}
}


