<?php
/**
 * @package         Joomla
 * @subpackage      eMundus
 * @link            http://www.emundus.fr
 * @copyright       Copyright (C) 2015 eMundus. All rights reserved.
 * @license         GNU/GPL
 * @author          James Dean
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

class EmundusModelStats extends JModelLegacy {

    public function getPeriodeData($periode) {
        if ($periode == 0)
            $query = ' 1 WEEK ';
        elseif ($periode == 1)
            $query = ' 2 WEEK ';
        elseif ($periode == 2)
            $query = ' 1 MONTH ';
        elseif ($periode == 3)
            $query = ' 3 MONTH ';
        elseif ($periode == 4)
            $query = ' 6 MONTH ';
        return $query;
    }

    public function getAccountType($value, $periode) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $p = self::getPeriodeData($periode);

        $query->select('*')->from($db->quoteName('#__emundus_stats_nombre_comptes'))->where($db->quoteName('_day').' >= DATE_SUB(CURDATE(), INTERVAL '.$p.') AND '.$db->quoteName('_day').' <= CURDATE() AND '.$db->quoteName('profile_id').' = '.$value);
        $db->setQuery($query);

        try {
            return $db->loadAssocList();
        } catch(Exception $e) {
            JLog::add('Error getting stats on account types at m/stats in query: '.$query->__toString(), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    public function consultationOffres($value, $periode) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $p = self::getPeriodeData($periode);

        $query->select('*')->from($db->quoteName('#__emundus_stats_nombre_consult_offre'))->where($db->quoteName('_day').' >= DATE_SUB(CURDATE(), INTERVAL '.$p.') AND '.$db->quoteName('_day').' <= CURDATE() AND '.$db->quoteName('num_offre').' = '.$value);
        $db->setQuery($query);

        try {
        	return $db->loadAssocList();
        } catch(Exception $e) {
	        JLog::add('Error getting stats on offer consultations at m/stats in query: '.$query->__toString(), JLog::ERROR, 'com_emundus');
	        return false;
        }
    }

    public function candidatureOffres($value, $periode) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $p = self::getPeriodeData($periode);

	    $query->select('*')->from($db->quoteName('#__emundus_stats_nombre_candidature_offre'))->where($db->quoteName('_day').' >= DATE_SUB(CURDATE(), INTERVAL '.$p.') AND '.$db->quoteName('_day').' <= CURDATE() AND '.$db->quoteName('num_offre').' = '.$value);
        $db->setQuery($query);

        try {
	        return $db->loadAssocList();
        } catch(Exception $e) {
	        JLog::add('Error getting stats on offer consultations at m/stats in query: '.$query->__toString(), JLog::ERROR, 'com_emundus');
	        return false;
        }
    }

    public function getConnections($periode) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $p = self::getPeriodeData($periode);

        $query->select('*')->from($db->quoteName('#__emundus_stats_nombre_connexions'))->where($db->quoteName('_day').' >= DATE_SUB(CURDATE(), INTERVAL '.$p.') AND '.$db->quoteName('_day').' <= CURDATE()');

	    try {
		    return $db->loadAssocList();
	    } catch(Exception $e) {
		    JLog::add('Error getting stats on number of connections at m/stats in query: '.$query->__toString(), JLog::ERROR, 'com_emundus');
		    return false;
	    }
    }

    public function getNbRelations($periode) {
        $db = JFactory::getDbo();
        $query  = $db->getQuery(true);
        $p = self::getPeriodeData($periode);

        $query->select('*')->from($db->quoteName('#__emundus_stats_nombre_relations_etablies'))->where($db->quoteName('_day').' >= DATE_SUB(CURDATE(), INTERVAL '.$p.') AND '.$db->quoteName('_day').' <= CURDATE()');

	    try {
		    return $db->loadAssocList();
	    } catch(Exception $e) {
		    JLog::add('Error getting stats on number of relations at m/stats in query: '.$query->__toString(), JLog::ERROR, 'com_emundus');
		    return false;
	    }
    }
}

