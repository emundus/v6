<?php

require_once COM_FABRIK_FRONTEND . '/models/plugin-cron.php';
require_once (JPATH_SITE . DS. 'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
class PlgFabrik_Cronemundusehespsiscole extends PlgFabrik_Cron
{

    public function process(&$data, &$listModel)
    {

        $date = date('Y-m-d');

        $params = $this->getParams();
        $eMConfig 	= JComponentHelper::getParams('com_emundus');

        $link = $eMConfig->get('filename');
        $id_element = $params->get('element', '');
        $entete = $params->get('entete', '');
        $id_ehesp = $params->get('id_ehesp', 0);
        $id_emundus = $params->get('id_emundus', 0);
        $status = $params->get('status', 0);
        $tag = $params->get('tag', 0);
        $annee = $params->get('annee', 0);
        $modif = $params->get('modif', 0);
        $status = $params->get('status_emundus', 1);
        $m_files = new EmundusModelFiles;

        $element = $this->getElementsName($id_element);

        $db = JFactory::getDbo();

        // Requête qui recherche les dossiers créés ou modifié à la date du lancement du cron
        $query = $db->getQuery(true);

        $query->select($db->quoteName('fnum_to','fnum'));
        $query->from($db->quoteName('#__emundus_logs'));
        $query->where($db->quoteName('message') . ' IN ('.$status.') AND '.$db->quoteName('timestamp').' BETWEEN '.$db->quote($date.' 00:00:00').' AND '.$db->quote($date.' 23:59:59') );

        $db->setQuery($query);

        $resultlogs = $db->loadAssocList();

        if(!empty($resultlogs)){
            $query = $db->getQuery(true);


            foreach ($resultlogs as $fnums) {

                    $query = "SELECT ";

                    //id_ehesp
                    if ($id_ehesp) {
                        $query .= "eu.id_ehesp ";
                    }
                    if($id_emundus) {
                        $query .= ", eu.user_id ";
                    }
                    $query .= ", jos_emundus_campaign_candidature.fnum";
                    if($modif) { //5275
                        $query .= ", CAST(ed.time_date AS DATE) ";
                    }
                    //status
                    if ($status) { //6337
                        $query .= ", ess.value ";
                    }
                    if ($tag) { //5846
                        $query .= ", GROUP_CONCAT(esat.label) ";
                    }
                    //année campagne
                    if ($annee) { //1891
                        $query .= ", esc.year ";
                    }

                    $results = $m_files->getFnumArray($fnums,$element,0,0,0,1,$query);

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
                    fputcsv($fp, $line, ';',chr(0));
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