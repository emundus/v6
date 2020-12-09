<?php

require_once COM_FABRIK_FRONTEND . '/models/plugin-cron.php';
class PlgFabrik_Cronemundusehespsiscole extends PlgFabrik_Cron
{
    public function process(&$data, &$listModel)
    {
        $date = date('Y-m-d');

        $params = $this->getParams();
        $link = $params->get('filename', '');

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
            foreach ($resultlogs as $fnums) {

                $query->select($db->quoteName(
                    array('ep.user','eu.id_ehesp', 'ep.fnum', 'ep.time_date','ess.value','esat.label','esc.year','e_344_7640', 'e_344_7643', 'e_344_7649', 'e_344_8078', 'e_344_8081', 'e_344_7712', 'e_344_7646',
                        'e_344_7652', 'e_344_7655', 'e_344_7658', 'e_344_7661', 'e_344_7688', 'code_insee_commune_naissance',
                        'e_344_8012', 'e_344_7664', 'e_344_7667', 'e_344_7676', 'e_344_7673', 'e_344_7679', 'e_344_7682', 'e_344_7685',
                        'e_344_7697', 'e_344_7691', 'e_344_7688', 'code_insee_commune_rersidence', 'e_344_7694', 'e_344_7697',
                        'e_344_7700', 'e_344_7703', 'e_344_7706', 'e_344_7709', 'e_344_7715', 'ei.domaines_interventions', 'e_350_7748',
                        'e_350_7751', 'e_353_7766', 'e_356_7811', 'e_356_7805', 'e_356_7808', 'e_359_7850', 'e_359_7829', 'e_359_7832',
                        'pays_emploi', 'e_359_7835', 'e_359_7838', 'e_359_7841', 'e_359_8144', 'code_insee_commune_employeur',
                        'e_359_8147', 'e_359_7865', 'e_359_7871', 'e_359_7853', 'e_359_7856', 'e_362_7895', 'e_362_7898', 'e_362_7901', 'e_362_7904')));
                $query->from($db->quoteName('#__emundus_1001_00', 'ep'));
                $query->join('LEFT' , $db->quoteName('#__emundus_1001_02', 'ei') . ' ON ' . $db->quoteName('ep.fnum') . ' = ' . $db->quoteName('ei.fnum'));
                $query->join('LEFT' , $db->quoteName('#__emundus_1001_03', 'ecomp') . ' ON ' . $db->quoteName('ep.fnum') . ' = ' . $db->quoteName('ecomp.fnum'));
                $query->join('LEFT' , $db->quoteName('#__emundus_1001_04', 'ec') . ' ON ' . $db->quoteName('ep.fnum') . ' = ' . $db->quoteName('ec.fnum'));
                $query->join('LEFT' , $db->quoteName('#__emundus_1001_05', 'ee') . ' ON ' . $db->quoteName('ep.fnum') . ' = ' . $db->quoteName('ee.fnum'));
                $query->join('LEFT' , $db->quoteName('#__emundus_1001_06', 'eib') . ' ON ' . $db->quoteName('ep.fnum') . ' = ' . $db->quoteName('eib.fnum'));
                $query->join('LEFT' , $db->quoteName('#__emundus_campaign_candidature', 'ecc') . ' ON ' . $db->quoteName('ep.fnum') . ' = ' . $db->quoteName('ecc.fnum'));
                $query->join('LEFT' , $db->quoteName('#__emundus_tag_assoc', 'eta') . ' ON ' . $db->quoteName('ep.fnum') . ' = ' . $db->quoteName('eta.fnum'));
                $query->join('LEFT' , $db->quoteName('#__emundus_setup_action_tag', 'esat') . ' ON ' . $db->quoteName('esat.id') . ' = ' . $db->quoteName('eta.id_tag'));
                $query->join('LEFT' , $db->quoteName('#__emundus_users', 'eu') . ' ON ' . $db->quoteName('ep.user') . ' = ' . $db->quoteName('eu.user_id'));
                $query->join('LEFT' , $db->quoteName('#__emundus_setup_status', 'ess') . ' ON ' . $db->quoteName('ess.step') . ' = ' . $db->quoteName('ecc.status'));
                $query->join('LEFT' , $db->quoteName('#__emundus_setup_campaigns', 'esc') . ' ON ' . $db->quoteName('esc.id') . ' = ' . $db->quoteName('ecc.campaign_id'));
                $query->where($db->quoteName('ep.fnum') . ' LIKE ' . $db->quote($fnums->fnum_to));

                $db->setQuery($query);
                $results = $db->loadObjectList();

            }
            // En-tête du fichier CSV
            $post[0] = [
                'ID_EHESP',
                'ID_EMUNDUS',
                'NUM_DOSSIER',
                'DERNIERE_MODIFICATION_DOSSIER',
                'STATUT_DOSSIER',
                'ETIQUETTES_CODES',
                'CAMPAGNE_RATTACHEMENT',
                'CIVILITE_CODE',
                'NOM_USAGE',
                'PRENOM',
                'PRENOM2',
                'PRENOM3',
                'EMAIL',
                'NAISSANCE_NOM',
                'NAISSANCE_DATE',
                'NAISSANCE_PAYS_CODE',
                'NAISSANCE_DEPARTEMENT_CODE',
                'NAISSANCE_COMMUNE_NOM',
                'NAISSANCE_COMMUNE_CP',
                'NAISSANCE_COMMUNE_INSEE',
                'NAISSANCE_COMMUNE_NOM_ETRANGER',
                'NATIONALITE_1_CODE',
                'NATIONALITE_2_CODE',
                'RESIDENCE_PAYS_CODE',
                'RESIDENCE_ADR_NUM',
                'RESIDENCE_ADR_BTQ_CODE',
                'RESIDENCE_ADR_TYPE_VOIE_CODE',
                'RESIDENCE_ADR_LIBELLE_VOIE',
                'RESIDENCE_ADR_LIGNE_2',
                'RESIDENCE_ADR_LIGNE_3',
                'RESIDENCE_ADR_COMMUNE_NOM',
                'RESIDENCE_ADR_COMMUNE_CP',
                'RESIDENCE_ADR_COMMUNE_INSEE',
                'RESIDENCE_ADR_ETRANGER_LIGNE_1',
                'RESIDENCE_ADR_ETRANGER_LIGNE_2',
                'RESIDENCE_ADR_ETRANGER_CP',
                'RESIDENCE_ADR_ETRANGER_COMMUNE',
                'TELEPHONE_FIXE',
                'TELEPHONE_MOBILE',
                'FAX',
                'DISCIPLINES_CODES',
                'ASSISTANTE_CONTACT_CODE',
                'NIVEAU_DIPLOME_CODE',
                'PAIEMENT_VACATIONS',
                'STATUT_SALARIE_CODE',
                'NUMERO_SS',
                'REGIME_RETRAITE',
                'EMPLOYEUR_SIRET',
                'EMPLOYEUR_DATE_DEBUT',
                'EMPLOYEUR_RAISON_SOCIALE',
                'EMPLOYEUR_ADR_PAYS_CODE',
                'EMPLOYEUR_ADR_LIGNE_1',
                'EMPLOYEUR_ADR_LIGNE_2',
                'EMPLOYEUR_ADR_COMMUNE_CP',
                'EMPLOYEUR_ADR_COMMUNE_INSEE',
                'EMPLOYEUR_ADR_COMMUNE_NOM',
                'EMPLOYEUR_ADR_ETRANGER_CP',
                'EMPLOYEUR_ADR_ETRANGER_COMMUNE',
                'GRADE_METIER_EMPLOI',
                'FONCTION',
                'RIB_NOM_BENEFICIAIRE',
                'RIB_DOMICILIATION',
                'IBAN',
                'BIC_SWIFT'
            ];

            for($i = 1; $i < count($results); $i++) {
                $post[$i] = [
                    $results[$i]->id_ehesp,
                    $results[$i]->user,
                    $results[$i]->fnum,
                    $results[$i]->time_date,
                    $results[$i]->value,
                    $results[$i]->label,
                    $results[$i]->year,
                    $results[$i]->e_344_7640, //Civilité
                    $results[$i]->e_344_7643, //Nom usage
                    $results[$i]->e_344_7649, //Prenom1
                    $results[$i]->e_344_8078, //Prenom2
                    $results[$i]->e_344_8081, //Prenom3
                    $results[$i]->e_344_7712, //Email
                    $results[$i]->e_344_7646, //Nom naissance
                    $results[$i]->e_344_7652, //Date naissance
                    $results[$i]->e_344_7655, //Pays naissance
                    $results[$i]->e_344_7658, //Département naissance
                    $results[$i]->e_344_7661, // Commune naissance FR
                    $results[$i]->e_344_7688, //Code postal
                    $results[$i]->code_insee_commune_naissance, //code commune insee
                    $results[$i]->e_344_8012, // Commune naissance etranger
                    $results[$i]->e_344_7664, // Nationalité 1
                    $results[$i]->e_344_7667, //Nationalité 2
                    $results[$i]->e_344_7676, // Pays résidence
                    $results[$i]->e_344_7673, // adr résidence
                    $results[$i]->e_344_7679, // complément rue Résidence
                    $results[$i]->e_344_7682, // Type de voie
                    $results[$i]->e_344_7685, // Libellé voie
                    $results[$i]->e_344_7697, // Résidence, voie ..
                    $results[$i]->e_344_7697, // Résidence, voie ..
                    $results[$i]->e_344_7691, // Ville résidence
                    $results[$i]->e_344_7688, //code postal
                    $results[$i]->code_insee_commune_rersidence, // code insee residence
                    $results[$i]->e_344_7694, //adr residence etranger
                    $results[$i]->e_344_7697, // Résidence, voie ..
                    $results[$i]->e_344_7700, //code postal etranger
                    $results[$i]->e_344_7703, // ville etranger
                    $results[$i]->e_344_7706, // telephone fixe
                    $results[$i]->e_344_7709, // telephone mobile
                    $results[$i]->e_344_7715, // fax
                    $results[$i]->domaines_interventions,
                    $results[$i]->e_350_7748, //contact ehesp
                    $results[$i]->e_353_7766, //diplome
                    $results[$i]->e_350_7751, //remuneration
                    $results[$i]->e_356_7811, //statut couverture
                    $results[$i]->e_356_7805, //numero secu
                    $results[$i]->e_356_7808, //regime retraite
                    $results[$i]->e_359_7850, //siret
                    $results[$i]->e_359_7829, //date debut entreprise
                    $results[$i]->e_359_7832, //Dénomation / raison sociale
                    $results[$i]->pays_emploi,
                    $results[$i]->e_359_7835, //adr emploi
                    $results[$i]->e_359_7838, //adr complementaire
                    $results[$i]->e_359_7841, //code postal
                    $results[$i]->code_insee_commune_employeur,
                    $results[$i]->e_359_8147, //ville employeur
                    $results[$i]->e_359_7865, //code postal
                    $results[$i]->e_359_7871, //ville
                    $results[$i]->e_359_7853, //grade métier
                    $results[$i]->e_359_7856, // fonction
                    $results[$i]->e_362_7895, // nom bénéficiaire
                    $results[$i]->e_362_7898, // domicialication
                    $results[$i]->e_362_7901, // IBAN
                    $results[$i]->e_362_7904, // BIC
                ];
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
}