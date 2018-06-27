<?php
defined('_JEXEC') or die('Access Deny');

class modEmundusGraphsHelper {

    public function getaccountType() {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('*')->from($db->quoteName('#__emundus_stats_nombre_comptes'))->order('_date');
        $db->setQuery($query);

        try {
            return $db->loadAssocList();
        } catch(Exception $e) {
            JLog::add('Error getting account type stats from mod_graphs helper at query: '.$query->__toString(), JLog::ERROR, 'com_emundus');
        }
    }

    public function consultationOffres() {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('*')->from($db->quoteName('#__emundus_stats_nombre_consult_offre'));
        $db->setQuery($query);

        try {
	        return $db->loadAssocList();
        } catch(Exception $e) {
	        JLog::add('Error getting offer consultation stats from mod_graphs helper at query: '.$query->__toString(), JLog::ERROR, 'com_emundus');
        }
    }

    public function candidatureOffres() {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('*')->from($db->quoteName('#__emundus_stats_nombre_candidature_offre'));
        $db->setQuery($query);

        try {
	        return $db->loadAssocList();
        } catch(Exception $e) {
	        JLog::add('Error getting offer candidacy stats from mod_graphs helper at query: '.$query->__toString(), JLog::ERROR, 'com_emundus');
        }
    }

    public function getConnections() {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('*')->from($db->quoteName('#__emundus_stats_nombre_connexions'));
        $db->setQuery($query);

        try {
	        return $db->loadAssocList();
        } catch(Exception $e) {
	        JLog::add('Error getting connection stats from mod_graphs helper at query: '.$query->__toString(), JLog::ERROR, 'com_emundus');
        }
    }

    public function getRelations() {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('*')->from($db->quoteName('#__emundus_stats_nombre_relations_etablies'));
        $db->setQuery($query);

	    try {
		    return $db->loadAssocList();
	    } catch(Exception $e) {
		    JLog::add('Error getting relationship stats from mod_graphs helper at query: '.$query->__toString(), JLog::ERROR, 'com_emundus');
	    }
    }
}
