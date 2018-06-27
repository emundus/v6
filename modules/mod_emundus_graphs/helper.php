<?php
    defined('_JEXEC') or die('Access Deny');

    class modEmundusGraphsHelper
    {

        public function getaccountType() {
            $db = JFactory::getDbo();
            $query  = $db->getQuery(true);
            $query = 'SELECT * FROM `jos_emundus_stats_nombre_comptes` ORDER BY _date';
            $db->setQuery($query);
            try {
                $list = $db->loadAssocList();
                return $list;
            }
            catch(Exception $e) {
                echo $e->getMessage() . '<br />'.$query->__toString();
            }
        }

        public function consultationOffres() {
            $db = JFactory::getDbo();
            $query  = $db->getQuery(true);
            $query->select('*');
            $query->from('jos_emundus_stats_nombre_consult_offre');
            $db->setQuery($query);
            $list = $db->loadAssocList();
            return $list;
        }

        public function candidatureOffres() {
            $db = JFactory::getDbo();
            $query  = $db->getQuery(true);
            $query->select('*');
            $query->from('jos_emundus_stats_nombre_candidature_offre');
            $db->setQuery($query);
            $list = $db->loadAssocList();
            return $list;
        }

        public function getConnections() {
            $db = JFactory::getDbo();
            $query  = $db->getQuery(true);
            $query->select('*');
            $query->from('jos_emundus_stats_nombre_connexions');
            $db->setQuery($query);
            $list = $db->loadAssocList();
            return $list;
        }

        public function getRelations() {
            $db = JFactory::getDbo();
            $query  = $db->getQuery(true);
            $query->select('*');
            $query->from('jos_emundus_stats_nombre_relations_etablies');
            $db->setQuery($query);
            $list = $db->loadAssocList();
            return $list;
        }

        
    }
?>