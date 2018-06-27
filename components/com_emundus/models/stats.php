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
        if($periode == 0)
            $query = ' 1 WEEK ';
        if($periode == 1)
            $query = ' 2 WEEK ';
        if($periode == 2)
            $query = ' 1 MONTH ';
        if($periode == 3)
            $query = ' 3 MONTH ';
        if($periode == 4)
            $query = ' 6 MONTH ';
        return $query;
    }

    public function getAccountType($value, $periode) {
        $db = JFactory::getDbo();
        $query  = $db->getQuery(true);
        $p = self::getPeriodeData($periode);
        $query = 'SELECT * FROM jos_emundus_stats_nombre_comptes WHERE _day >= DATE_SUB(CURDATE(), INTERVAL '.$p.') AND _day <= CURDATE() AND profile_id = '.$value;
        $db->setQuery($query);
        try {
            $list = $db->loadAssocList();
            
            return $list;
        }
        catch(Exception $e) {
            echo $e->getMessage() . '<br />'.$query->__toString();
        }
    }

    public function consultationOffres($value, $periode) {
        $db = JFactory::getDbo();
        $query  = $db->getQuery(true);
        $p = self::getPeriodeData($periode);
        $query = 'SELECT * FROM jos_emundus_stats_nombre_consult_offre WHERE _day >= DATE_SUB(CURDATE(), INTERVAL '.$p.') AND _day <= CURDATE() AND num_offre = '.$value;
        
        $db->setQuery($query);
        $list = $db->loadAssocList();
        return $list;
    }

    public function candidatureOffres($value, $periode) {
        $db = JFactory::getDbo();
        $query  = $db->getQuery(true);
        $p = self::getPeriodeData($periode);
        $query = 'SELECT * FROM jos_emundus_stats_nombre_candidature_offre WHERE _day >= DATE_SUB(CURDATE(), INTERVAL '.$p.') AND _day <= CURDATE() AND num_offre = '.$value;
        
        $db->setQuery($query);
        $list = $db->loadAssocList();
        return $list;
    }

    public function getConnections($periode) {
        $db = JFactory::getDbo();
        $query  = $db->getQuery(true);
        $p = self::getPeriodeData($periode);
        $query = 'SELECT * FROM jos_emundus_stats_nombre_connexions WHERE _day >= DATE_SUB(CURDATE(), INTERVAL '.$p.') AND _day <= CURDATE()';
        $db->setQuery($query);
        $list = $db->loadAssocList();
        return $list;
    }

    public function getNbRelations($periode) {
        $db = JFactory::getDbo();
        $query  = $db->getQuery(true);
        $p = self::getPeriodeData($periode);
        $query = 'SELECT * FROM jos_emundus_stats_nombre_relations_etablies WHERE _day >= DATE_SUB(CURDATE(), INTERVAL '.$p.') AND _day <= CURDATE()';
        $db->setQuery($query);
        $list = $db->loadAssocList();
        return $list;
    }
}

