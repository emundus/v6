<?php

require_once COM_FABRIK_FRONTEND . '/models/plugin-cron.php';

class PlgFabrik_Cronemundusehespsiscole extends PlgFabrik_Cron
{

    public function process(&$data, &$listModel)
    {

        $date = date('Y-m-d');

        $params = $this->getParams();
        $link = $params->get('filename', '');
        $id_element = $params->get('element', '');
        $table = $params->get('table', '');
        $entete = $params->get('entete', '');
        $id_ehesp = $params->get('id_ehesp', 0);
        $id_emundus = $params->get('id_emundus', 0);
        $status = $params->get('status', 0);
        $tag = $params->get('tag', 0);
        $annee = $params->get('annee', 0);
        $modif = $params->get('modif', 0);
        
        $element = $this->getElementsName($id_element);

        $db = JFactory::getDbo();

        // Requête qui recherche les dossiers créés ou modifié à la date du lancement du cron
        $query = $db->getQuery(true);

        $query->select($db->quoteName('fnum_to'));
        $query->from($db->quoteName('#__emundus_logs'));
        $query->where($db->quoteName('message') . ' IN (1,10,14) AND '.$db->quoteName('timestamp').' BETWEEN '.$db->quote($date.' 00:00:00').' AND '.$db->quote($date.' 23:59:59') );

        $db->setQuery($query);

        $resultlogs = $db->loadObjectList();

        if(!empty($resultlogs)){
            $query = $db->getQuery(true);
            $tab = array();
            $elt = array();
            foreach ($resultlogs as $fnums) {

                foreach ($element as $key => $el) {

                    $elt[$fnums->fnum_to][] = $el->tab_name . '.' . $el->element_name;
                    $query = "SELECT ";

                    //id_ehesp
                    if ($id_ehesp) {
                        $query .= "eu.id_ehesp ";
                    }
                    if($id_emundus) {
                        $query .= ", eu.user_id ";
                    }
                    $query .= ", ecc.fnum";
                    if($modif) {
                        $query .= ", ed.time_date ";
                    }
                    if ($tag) {
                        $query .= ", esat.label ";
                    }
                    //status
                    if ($status) {
                        $query .= ", ess.value ";
                    }
                    //année campagne
                    if ($annee) {
                        $query .= ", esc.year ";
                    }
                    $query .= ", ".implode(', ',$elt[$fnums->fnum_to]);

                    $query .= " FROM #__emundus_campaign_candidature as ecc";

                    $tab[] = $el->tab_name;
                }
                $arrayTab = array_unique($tab);
                foreach($arrayTab as $arr){
                    $query .= " LEFT JOIN " . $db->quoteName($arr) . " ON " . $db->quoteName('ecc.fnum') . " = " . $db->quoteName($arr. '.fnum');
                }

                //tag
                if ($tag) {
                    $query .= " LEFT JOIN " . $db->quoteName('#__emundus_tag_assoc', 'eta') . " ON " . $db->quoteName('ecc.fnum') . " = " . $db->quoteName('eta.fnum');
                    $query .= " LEFT JOIN " . $db->quoteName('#__emundus_setup_action_tag', 'esat') . " ON " . $db->quoteName('esat.id') . " = " . $db->quoteName('eta.id_tag');
                }
                //id_ehesp
                if ($id_ehesp) {
                    $query .= " LEFT JOIN " . $db->quoteName('#__emundus_users', 'eu') . " ON " . $db->quoteName('ecc.user_id') . " = " . $db->quoteName('eu.user_id');
                }
                //status
                if ($status) {
                    $query .= " LEFT JOIN " . $db->quoteName('#__emundus_setup_status', 'ess') . " ON " . $db->quoteName('ess.step') . " = " . $db->quoteName('ecc.status');
                }
                //année campagne
                if ($annee) {
                    $query .= " LEFT JOIN " . $db->quoteName('#__emundus_setup_campaigns', 'esc') . " ON " . $db->quoteName('esc.id') . " = " . $db->quoteName('ecc.campaign_id');
                }
                //date envoie / modification dossier
                if ($modif) {
                    $query .= " LEFT JOIN " . $db->quoteName('#__emundus_declaration', 'ed') . " ON " . $db->quoteName('ed.fnum') . " = " . $db->quoteName('ecc.fnum');
                }
                $query .= " WHERE " . $db->quoteName('ecc.fnum') . " IN (" . $db->quote($fnums->fnum_to)." ) GROUP BY ecc.fnum";


                $db->setQuery($query);
                $results = $db->loadAssocList();

            }

            $entete = explode(',',trim($entete));
            $post[0] = $entete;

            foreach($results as $key => $result){
                $post[] = array_values($result);
            }
            $path = JPATH_BASE.DS.'images'.DS.'emundus'.DS.'files'.DS.'archives'.DS.$link.'.csv'; // chemin du lien

            if(file_exists($path)){ // archive le fichier tout les jours
                rename($path, JPATH_BASE.DS.'images'.DS.'emundus'.DS.'files'.DS.'archives'.DS.$link.$date.'.csv');
            }
            try {
                $fp = fopen($path, 'a');
                fputs($fp, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) )); // permet l'encodage UTF8
                foreach ($post as $line) {
                    fputcsv($fp, $line, ';');
                }
                fclose($fp);
            }
            catch (Exception $e){
                JLog::add("The export doesn't work", JLog::ERROR, 'com_emundus');
            }
        }
    }
    public function getElementsName($elements_id) {
        if (!empty($elements_id) && !empty(ltrim($elements_id))) {

            $db = JFactory::getDBO();
            $query = 'SELECT element.id, element.name AS element_name, element.label as element_label, element.params AS element_attribs, element.plugin as element_plugin, element.hidden as element_hidden, forme.id as form_id, forme.label as form_label, groupe.id as group_id, groupe.label as group_label, groupe.params as group_attribs,tab.db_table_name AS tab_name, tab.created_by_alias AS created_by_alias, joins.table_join
                    FROM #__fabrik_elements element
                    INNER JOIN #__fabrik_groups AS groupe ON element.group_id = groupe.id
                    INNER JOIN #__fabrik_formgroup AS formgroup ON groupe.id = formgroup.group_id
                    INNER JOIN #__fabrik_forms AS forme ON formgroup.form_id = forme.id
                    INNER JOIN #__fabrik_lists AS tab ON tab.form_id = formgroup.form_id
                    LEFT JOIN #__fabrik_joins AS joins ON (tab.id = joins.list_id AND (groupe.id=joins.group_id OR element.id=joins.element_id))
                    WHERE element.id IN ('.ltrim($elements_id, ',').')
                    ORDER BY FIELD(element.id, '.ltrim($elements_id, ',').')';
            try {
                $db->setQuery($query);
                $res = $db->loadObjectList('id');
            } catch (Exception $e) {
                JLog::add('Could not get Evaluation elements name in query -> '.$query, JLog::ERROR, 'com_emundus');
                return false;
            }

            $elementsIdTab = array();
            foreach ($res as $kId => $r) {
                $elementsIdTab[$kId] = $r;
            }
            return $elementsIdTab;
        } else {
            return array();
        }
    }
}