<?php
defined('_JEXEC') or die('Access Deny');

class modEmundusUpdateHelper
{
	private $db;
	private $user;

	public function __construct()
	{
		$this->db   = JFactory::getDbo();
		$this->user = JFactory::getUser();

	}


	public function checkVersion()
	{
		$query = $this->db->getQuery(true);

		$query
			->select($this->db->quoteName('*'))
			->from($this->db->quoteName('#__emundus_version'));

		$this->db->setQuery($query);

		try {
			return $this->db->loadObject();
		}
		catch (Exception $e) {
			JLog::add('Error getting account type stats from mod_graphs helper at query: ' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'com_emundus');
		}

	}


}
