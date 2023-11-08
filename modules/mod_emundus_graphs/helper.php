<?php
defined('_JEXEC') or die('Access Deny');

class modEmundusGraphsHelper
{

	public function getCountView($view)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('COUNT(*)')->from($view);

		try {
			return $db->loadResult();
		}
		catch (Exception $e) {
			JLog::add('Error getting account type stats from mod_graphs helper at query: ' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'com_emundus');

			return 0;
		}
	}

	// Get Every account From emundus_stats_nombre_comptes View To then Filter by user_ID
	public function getaccountType()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('*')->from($db->quoteName('#__emundus_stats_nombre_comptes'))->order('_date');
		$db->setQuery($query);

		try {
			return $db->loadAssocList();
		}
		catch (Exception $e) {
			JLog::add('Error getting account type stats from mod_graphs helper at query: ' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'com_emundus');

			return false;
		}
	}


	//// Get Every consultation From __emundus_stats_nombre_consult_offre View
	public function consultationOffres()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('*')->from($db->quoteName('#__emundus_stats_nombre_consult_offre'));
		$db->setQuery($query);

		try {
			return $db->loadAssocList();
		}
		catch (Exception $e) {
			JLog::add('Error getting offer consultation stats from mod_graphs helper at query: ' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'com_emundus');

			return false;
		}
	}

	//// Get Every candidate From __emundus_stats_nombre_candidature_offre View
	public function candidatureOffres()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('*')->from($db->quoteName('#__emundus_stats_nombre_candidature_offre'));
		$db->setQuery($query);

		try {
			return $db->loadAssocList();
		}
		catch (Exception $e) {
			JLog::add('Error getting offer candidacy stats from mod_graphs helper at query: ' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'com_emundus');

			return false;
		}
	}

	//// Get Every connexion From __emundus_stats_nombre_connexions View
	public function getConnections()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('*')->from($db->quoteName('#__emundus_stats_nombre_connexions'));
		$db->setQuery($query);

		try {
			return $db->loadAssocList();
		}
		catch (Exception $e) {
			JLog::add('Error getting connection stats from mod_graphs helper at query: ' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'com_emundus');

			return false;
		}
	}

	//// Get Every relation From __emundus_stats_nombre_relations_etablies View
	public function getRelations()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('*')->from($db->quoteName('#__emundus_stats_nombre_relations_etablies'));
		$db->setQuery($query);

		try {
			return $db->loadAssocList();
		}
		catch (Exception $e) {
			JLog::add('Error getting relationship stats from mod_graphs helper at query: ' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'com_emundus');

			return false;
		}
	}

	/// Get all projects and accepted projects for each profile ( Custom Hesam )  from jos_emundus_stats_relation_realise_accepte_par_profil
	public function getProjects()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('*')->from($db->quoteName('#__emundus_stats_relation_realise_accepte_par_profil'));
		$db->setQuery($query);

		try {
			return $db->loadAssoc();
		}
		catch (Exception $e) {
			JLog::add('Error getting relationship stats from mod_graphs helper at query: ' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'com_emundus');

			return false;
		}
	}
}
