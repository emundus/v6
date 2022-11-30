<?php

require_once COM_FABRIK_FRONTEND . '/models/plugin-cron.php';
require_once (JPATH_SITE . DS. 'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');

class PlgFabrik_Cronemundusehespsiscole extends PlgFabrik_Cron
{
    /**
     * Check if the user can use the active element
     *
     * @param   string  $location  To trigger plugin on
     * @param   string  $event     To trigger plugin on
     *
     * @return  bool can use or not
     */

    public function canUse($location = null, $event = null)
    {
        return true;
    }

    public function process(&$data, &$listModel)
    {

        // LOGGER
        jimport('joomla.log.log');
        JLog::addLogger(['text_file' => 'com_emundus.emundusehespsiscole.info.php'], JLog::INFO, 'com_emundus.emundusehespsiscole');
        JLog::addLogger(['text_file' => 'com_emundus.emundusehespsiscole.error.php'], JLog::ERROR, 'com_emundus.emundusehespsiscole');

        $date = date('Y-m-d H:i:s');

        $params = $this->getParams();
        $eMConfig   = JComponentHelper::getParams('com_emundus');

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
        //Requête qui  récupère la date de dernière exécution du plugin
        $query = $db->getQuery(true);

        $query->select($db->quoteName('lastrun'));
        $query->from($db->quoteName('#__fabrik_cron'));
        $query->where($db->quoteName('plugin') . ' LIKE '.$db->quote('emundusehespsiscole'));

        $db->setQuery($query);
        $lastrun = $db->loadResult();
        // Requête qui recherche les dossiers créés ou modifié à la date du lancement du cron
        $query = $db->getQuery(true);
        /*
                $query->select('DISTINCT (fnum_to) as fnum');
                $query->from($db->quoteName('#__emundus_logs'));
                $query->where($db->quoteName('message') . ' IN ('.$status.') AND '.$db->quoteName('timestamp').' BETWEEN '.$db->quote($lastrun).' AND '.$db->quote($date).' ORDER BY timestamp DESC');
        */
        // Suite à la demande de pouvoir télécharger tous les dossiers complets, on ne cherche plus à identifier les dossiers dernièrement modifiés, seulement ceux non-archivés parmis une liste de statuts
        $query->select('fnum');
        $query->from($db->quoteName('#__emundus_campaign_candidature'));
        $query->where($db->quoteName('published') . '=1 AND '.$db->quoteName('status') . ' IN ('.$status.') ORDER BY date_submitted DESC');

        $db->setQuery($query);

        try {
            $resultlogs = $db->loadAssocList();
            JLog::add("Nb files to export: ".count($resultlogs), JLog::INFO, 'com_emundus.emundusehespsiscole');
        }
        catch (Exception $e){
            JLog::add("Cannot get modified file ", JLog::ERROR, 'com_emundus.emundusehespsiscole');
        }

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

                $results[] = $m_files->getFnumArray($fnums,$element,0,0,0,1,$query);

            }

            $entete = explode(',',trim($entete));
            $post[0] = $entete;

            foreach($results as $key => $result){
                foreach ($result as $res){
                    $post[] = array_values($res);
                }
            }

            $path = JPATH_SITE.DS.'images'.DS.'emundus'.DS.'files'.DS.'archives'.DS.$link.'.csv'; // chemin du lien
            $yesterday_date = date('Y-m-d',strtotime('- 1 day'));
            $csv_file = $link.$yesterday_date.'.csv';

            if(file_exists($path)){ // archive le fichier tout les jours
                rename($path, JPATH_SITE.DS.'images'.DS.'emundus'.DS.'files'.DS.'archives'.DS.$csv_file);

                $query = $db->getQuery(true);

                $columns = array('time_date','fnum','keyid', 'attachment_id', 'filename');

                $time_date = date('Y-m-d H:i:s');
                $fnum = $resultlogs[0]['fnum'];
                $bytes = random_bytes(32);
                $new_token = bin2hex($bytes);
                $attachment_id = $eMConfig->get('attachment_id');

                $values = array($db->quote($time_date), $db->quote($fnum), $db->quote($new_token), $attachment_id, $db->quote($csv_file));

                $query
                    ->insert($db->quoteName('#__emundus_files_request'))
                    ->columns($db->quoteName($columns))
                    ->values(implode(',', $values));

                $db->setQuery($query);
                try{
                    $db->execute();
                }
                catch (Exception $e){
                    JLog::add('An error occurring in sql request: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus.emundusehespsiscole');
                }
                JLog::add($path. " rename to ".JPATH_SITE.DS.'images'.DS.'emundus'.DS.'files'.DS.'archives'.DS.$link.$yesterday_date.'.csv', JLog::INFO, 'com_emundus.emundusehespsiscole');
            }
            try {
                $fp = fopen($path, 'a');
                fputs($fp, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) )); // permet l'encodage UTF8
                foreach ($post as $line) {
                    fwrite($fp, implode(';', $line) . "\r\n");
                }
                fclose($fp);
                JLog::add($path. " saved", JLog::INFO, 'com_emundus.emundusehespsiscole');
            }
            catch (Exception $e){
                JLog::add("The export doesn't work", JLog::ERROR, 'com_emundus.emundusehespsiscole');
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
                JLog::add('Could not get elements in query -> '.$query, JLog::ERROR, 'com_emundus.emundusehespsiscole');
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