<?php


class emundusehespsiscole extends JApplicationCli
{
    public function doExecute()
    {
        $date = new Date('Y-m-d');
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select($db->quoteName(array('fnum')));
        $query->from($db->quoteName('#__emundus_logs'));
        $query->where($db->quoteName('message') . ' IN (1,10,14) AND CONVERT('.$db->quoteName('timestamp').',Y-m-d) = '.$db->quote($date));

        $db->setQuery($query);

        $resultlogs = $db->loadObjectList();

        if(!empty($resultlogs)){
            $query = $db->getQuery(true);
            foreach ($resultlogs as $fnums)
            $query->select($db->quoteName(
                array('ep.user','ep.fnum', 'ep.time_date', 'e_344_7643', 'e_344_7649', 'e_344_8078', 'e_344_8081', 'e_344_7712', 'e_344_7646',
                    'e_344_7652', 'e_344_7655', 'e_344_7658', 'e_344_7661', 'e_344_7688', 'code_insee_commune_naissance',
                    'e_344_8012', 'e_344_7664', 'e_344_7667', 'e_344_7673', 'e_344_7679', 'e_344_7682', 'e_344_7685',
                    'e_344_7697', 'e_344_7691', 'e_344_7688', 'code_insee_commune_rersidence', 'e_344_7694', 'e_344_7697',
                    'e_344_7700', 'e_344_7703', 'e_344_7706', 'e_344_7709', 'e_344_7715','domaines_interventions','e_350_7748',
                    'e_350_7751','e_353_7766','e_356_7811','e_356_7805','e_356_7808','e_359_7850','e_359_7829','e_359_7832',
                    'pays_emploi','e_359_7835','e_359_7838','e_359_7841','e_359_8144','code_insee_commune_employeur',
                    'e_359_8147','e_359_7865','e_359_7871','e_359_7853','e_359_7856','e_362_7895','e_362_7898','e_362_7901','e_362_7904')));
            $query->from($db->quoteName('#__emundus_1001_00','ep'));
            $query->join('LEFT ' . $db->quoteName('#__emundus_1001_02', 'ei') . ' ON ' . $db->quoteName('ep.fnum') . ' = ' . $db->quoteName('ei.fnum'));
            $query->join('LEFT ' . $db->quoteName('#__emundus_1001_03', 'ecomp') . ' ON ' . $db->quoteName('ep.fnum') . ' = ' . $db->quoteName('ecomp.fnum'));
            $query->join('LEFT ' . $db->quoteName('#__emundus_1001_04', 'ec') . ' ON ' . $db->quoteName('ep.fnum') . ' = ' . $db->quoteName('ec.fnum'));
            $query->join('LEFT ' . $db->quoteName('#__emundus_1001_05', 'ee') . ' ON ' . $db->quoteName('ep.fnum') . ' = ' . $db->quoteName('ee.fnum'));
            $query->join('LEFT ' . $db->quoteName('#__emundus_1001_06', 'eib') . ' ON ' . $db->quoteName('ep.fnum') . ' = ' . $db->quoteName('eib.fnum'));
            $query->where($db->quoteName('fnum') . ' LIKE ' . $db->quote($fnums->fnum));

            $db->setQuery($query);
            $results = $db->loadObjectList();

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

            for($i = 1; $i <= count($results); $i++) {
                $post[$i] = [
                    $results[$i]->,
                    $results[$i]->user,
                    $results[$i]->fnum,
                    $results[$i]->time_date,
                    $results[$i]->,
                    $results[$i]->,
                    $results[$i]->,
                    $results[$i]->e_344_7643,
                    $results[$i]->e_344_7649,
                    $results[$i]->e_344_8078,
                    $results[$i]->e_344_8081,
                    $results[$i]->e_344_7712,
                    $results[$i]->e_344_7646,
                    $results[$i]->e_344_7652,
                    $results[$i]->e_344_7655,
                    $results[$i]->e_344_7658,
                    $results[$i]->e_344_7661,
                    $results[$i]->e_344_7688,
                    $results[$i]->code_insee_commune_naissance,
                    $results[$i]->e_344_8012,
                    $results[$i]->e_344_7664,
                    $results[$i]->e_344_7667,
                    $results[$i]->e_344_7673,
                    $results[$i]->e_344_7679,
                    $results[$i]->e_344_7682,
                    $results[$i]->e_344_7685,
                    $results[$i]->e_344_7697,
                    $results[$i]->e_344_7691,
                    $results[$i]->e_344_7688,
                    $results[$i]->code_insee_commune_rersidence,
                    $results[$i]->e_344_7694,
                    $results[$i]->e_344_7697,
                    $results[$i]->e_344_7700,
                    $results[$i]->e_344_7703,
                    $results[$i]->e_344_7706,
                    $results[$i]->e_344_7709,
                    $results[$i]->e_344_7715,
                    $results[$i]->domaines_interventions,
                    $results[$i]->e_350_7748,
                    $results[$i]->e_350_7751,
                    $results[$i]->e_353_7766,
                    $results[$i]->e_356_7811,
                    $results[$i]->e_356_7805,
                    $results[$i]->e_356_7808,
                    $results[$i]->e_359_7850,
                    $results[$i]->e_359_7829,
                    $results[$i]->e_359_7832,
                    $results[$i]->pays_emploi,
                    $results[$i]->e_359_7835,
                    $results[$i]->e_359_7838,
                    $results[$i]->e_359_7841,
                    $results[$i]->e_359_8144,
                    $results[$i]->code_insee_commune_employeur,
                    $results[$i]->e_359_8147,
                    $results[$i]->e_359_7865,
                    $results[$i]->e_359_7871,
                    $results[$i]->e_359_7853,
                    $results[$i]->e_359_7856,
                    $results[$i]->e_362_7895,
                    $results[$i]->e_362_7898,
                    $results[$i]->e_362_7901,
                    $results[$i]->e_362_7904,
                ];
                $path = '/images/emundus/files/archives/file_archive.csv';
                if(file_exists($path)){
                    rename($path, '/images/emundus/files/archives/file_archive'.$date.'.csv');
                }
                $fp = fopen($path, 'w');
                foreach ($post as $line) {
                    fputcsv($fp, $line, ';');
                }
                fclose($fp);
            }
        }
    }
}