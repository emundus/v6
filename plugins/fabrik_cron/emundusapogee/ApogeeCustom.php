<?php

class ApogeeCustom {
    var $xmlTree, $fnum;

    public function __construct($_xmlTree, $_fnum = null) {
        $this->xmlTree = $_xmlTree;
        $this->fnum = $_fnum;
    }

    /* CUSTOM for SCHEMA BUILDING */
    public function addCustomElements($elements, $root, $elementAttr = null, $elementValue = null) {
        // TODO: Implement addElements() method.

        /// create children elements
        $root_local = null;
        foreach($elements as $element) {
            $root_local = $this->xmlTree->createElement($element, $elementValue);
            $root->appendChild($root_local);

            if($elementAttr !== null) {
                $root_local->setAttribute('duplicata' , $elementAttr);
                $root->appendChild($root_local);
            }

        }
        return $root_local;
    }

    public function buildConvocation() {
        /// get parent node of 'convocation' (e.g: item)
        $_convocation = $this->xmlTree->getElementsByTagName('convocation');
        $_convocationCount = $_convocation->count();

        $_childList = array("datCvc", "dhhCvc", "dmnCvc");

        for($_count = 0 ; $_count < $_convocationCount ; $_count++) {
            $_cNode = $_convocation->item($_count);
            $this->addCustomElements($_childList,$_cNode);
        }
        return $this->xmlTree;
    }

    /// output of buildConvocation (is) input of buildTitreAccessExterne
    public function buildTitreAccessExterne() {
        $_titreAccessExterne = $this->xmlTree->getElementsByTagName('titreAccesExterne');
        $_titreAccessExterneCount = $_titreAccessExterne->count();

        $_childList = array("codDacOpi", "codDepPayDacOpi", "codEtbDacOpi", "codTpeDacOpi", "codTypDepPayDacOpi", "daaDacOpi");

        for($_count = 0 ; $_count < $_titreAccessExterneCount ; $_count++) {
            $_tNode = $_titreAccessExterne->item($_count);
            $this->addCustomElements($_childList,$_tNode);
        }
        return $this->xmlTree;
    }

    /* CUSTOM for DATA FILLING */
    public function setCodPay_Address() {
        $nodes = $this->xmlTree->getElementsByTagName('codPay');            /// e.g: find(2)

        foreach($nodes as $node) {
            if($node->nodeValue == "" or $node->nodeValue === null) { $node->nodeValue = '100'; }
        }

        return $this->xmlTree;
    }

    public function setBdiComAde_Address() {
        $db = JFactory::getDbo();
        ///////////////////////////////// ADRESSE ANNUELLE /////////////////////////////////
        $_aaRoot = $this->xmlTree->getElementsByTagName('adresseAnnuelle')->item(0);

        $_aaCodPay = $_aaRoot->getElementsByTagName('codPay')->item(0);
        $_aaCodBdi = $_aaRoot->getElementsByTagName('codBdi')->item(0);
        $_aaCodCom = $_aaRoot->getElementsByTagName('codCom')->item(0);
        $_aaLibAde = $_aaRoot->getElementsByTagName('libAde')->item(0);

        if($_aaCodPay->nodeValue == '100') {        /// # france
            /* set Bdi */
            $_getAaBdiSql = "SELECT lpad(#__emundus_personal_detail.etu_code_postal,5,'0') FROM #__emundus_personal_detail WHERE #__emundus_personal_detail.fnum = " . $this->fnum;
            $db->setQuery($_getAaBdiSql);
            $_aaCodBdi->nodeValue = $db->loadResult();
        } else {                                    /// # not france
            /* set Bdi */
            $_getAaBdiSql = 'SELECT #__emundus_personal_detail.e_287_8117 FROM #__emundus_personal_detail WHERE #__emundus_personal_detail.fnum = ' . $this->fnum;
            $_aaCodBdi->nodeValue = "";
            /* set libAde --> concat(e_287_8117, '', e_287_8118) */
            $_getAaLibAdeSql = "select trim(concat(#__emundus_personal_detail.e_287_8117, ' ', #__emundus_personal_detail.e_287_8118)) from #__emundus_personal_detail where #__emundus_personal_detail.fnum = " . $this->fnum;
            $db->setQuery($_getAaLibAdeSql);
            $_aaLibAde->nodeValue =  $db->loadResult();
        }



        ///////////////////////////////// ADRESSE FIXE /////////////////////////////////
        $_afRoot = $this->xmlTree->getElementsByTagName('adresseFixe')->item(0);

        $_afCodPay = $_afRoot->getElementsByTagName('codPay')->item(0);
        $_afCodBdi = $_afRoot->getElementsByTagName('codBdi')->item(0);
        $_afCodCom = $_afRoot->getElementsByTagName('codCom')->item(0);
        $_afLibAde = $_afRoot->getElementsByTagName('libAde')->item(0);

        if($_afCodPay->nodeValue == '100') {        /// #france
            /* set Bdi */
            $_getAfBdiSql = "SELECT lpad(#__emundus_personal_detail.etu_code_postal,5,'0') FROM #__emundus_personal_detail WHERE #__emundus_personal_detail.fnum = " . $this->fnum;
            $db->setQuery($_getAfBdiSql);
            $_afCodBdi->nodeValue = $db->loadResult();
        } else {                                    /// # not france
            /* set Bdi */
            $_getAfBdiSql = 'SELECT #__emundus_personal_detail.e_287_8117 FROM #__emundus_personal_detail WHERE #__emundus_personal_detail.fnum = ' . $this->fnum;
            $_afCodBdi->nodeValue = "";
            /* set libAde --> concat(e_287_8117, '', e_287_8118) */
            $_getAfLibAdeSql = "select trim(concat(#__emundus_personal_detail.e_287_8117, ' ', #__emundus_personal_detail.e_287_8118)) from #__emundus_personal_detail where #__emundus_personal_detail.fnum = " . $this->fnum;
            $db->setQuery($_getAfLibAdeSql);
            $_afLibAde->nodeValue =  $db->loadResult();
        }


        ///////////////////////////////////////////// Done /////////////////////////////////////////////

        /* Quote "Si l’un des deux champs (code commune ou code bureau distributeur) est vide ou incohérent par rapport à la table COM_BDI d’Apogée,
            les deux données sont remises à blanc et les autres données de l’adresse sont chargées, à condition que le code pays soit renseigné
            et valide par rapport à la table PAYS d’Apogée.
        */
        if($_aaCodBdi->nodeValue == null or $_aaCodCom->nodeValue == null) {
            $_aaCodCom->nodeValue == '';
            $_aaCodBdi->nodeValue == '';
        }

        if($_afCodBdi->nodeValue == null or $_afCodCom->nodeValue == null) {
            $_afCodCom->nodeValue == '';
            $_afCodBdi->nodeValue == '';
        }

        return $this->xmlTree;
    }

    public function setDepPayDerDip_LastObtainDipl() {
        $db = JFactory::getDbo();
        /* -- Si FRANCE (code = 100) --> codDepPayDerDip = Departement // codTypDepPayDerDip = "D" */
        /* -- Si pas FRANCE (code != 100) --> codDepPayDerDip = Pays // codTypDepPayDerDip = "P" */

        /// find "codDepPayDerDip" node
        $_codDepPayDerDipNode = $this->xmlTree->getElementsByTagName('codDepPayDerDip')->item(0);
        $_codTypDepPayDerDipNode = $this->xmlTree->getElementsByTagName('codTypDepPayDerDip')->item(0);

        if($_codDepPayDerDipNode->nodeValue == '100') {
            $_codTypDepPayDerDipNode->nodeValue = 'D';

            /// set $_codDepPayDerDipNode->nodeValue by France Department
            $_getDepartmentSql = "select cod_dep from data_departements left join jos_emundus_1001_00 as je10 on je10.dep_etb_last_dip = data_departements.departement_code where je10.fnum = " . $this->fnum;
            $db->setQuery($_getDepartmentSql);
            $_codDepPayDerDipNode->nodeValue = $db->loadResult();
        } else {
            $_codTypDepPayDerDipNode->nodeValue = 'P';
        }

        return $this->xmlTree;
    }

    public function setDepPayAnt_LastFrequentEtb() {
        $db = JFactory::getDbo();
        /* -- Si FRANCE (code = 100) --> codDepPayAntIaaOpi = Departement // codTypDepPayAntIaaOpi = "D" */
        /* -- Si pas FRANCE (code != 100) --> codDepPayAntIaaOpi = Pays // codTypDepPayAntIaaOpi = "P" */
        $_codDepPayAntIaaOpiNode = $this->xmlTree->getElementsByTagName('codDepPayAntIaaOpi')->item(0);
        $_codTypDepPayAntIaaOpiNode = $this->xmlTree->getElementsByTagName('codTypDepPayAntIaaOpi')->item(0);

        if($_codDepPayAntIaaOpiNode->nodeValue == '100') {
            /// set $_codDepPayDerDipNode->nodeValue by France Department
            $_getDepartmentSql = "select cod_dep from data_departements left join jos_emundus_1001_00 as je10 on je10.dep_etb_dernier = data_departements.departement_code where je10.fnum = " . $this->fnum;
            $db->setQuery($_getDepartmentSql);
            $_codDepPayAntIaaOpiNode->nodeValue = $db->loadResult();
            $_codTypDepPayAntIaaOpiNode->nodeValue = 'D';
        }
        else {
            $_codTypDepPayAntIaaOpiNode->nodeValue = 'P';
        }

        return $this->xmlTree;
    }

    public function setDepPay_Civility() {
        $db = JFactory::getDbo();

        $_codDepPayNaiNode = $this->xmlTree->getElementsByTagName('codDepPayNai')->item(0);
        $_codTypDepPayNaiNode = $this->xmlTree->getElementsByTagName('codTypDepPayNai')->item(0);

        if($_codDepPayNaiNode->nodeValue == '100') {
            $_codTypDepPayNaiNode->nodeValue = 'D';

            /* get French department if $_codDepPayNaiNode is 100 */
            $_getDepartmentSql = "select cod_dep from data_departements left join jos_emundus_personal_detail as jepd on jepd.etu_dept_nais = data_departements.departement_code where jepd.fnum = " . $this->fnum;
            $db->setQuery($_getDepartmentSql);
            $_codDepPayNaiNode->nodeValue = $db->loadResult();
        } else {
            $_codTypDepPayNaiNode->nodeValue = 'P';
        }

        return $this->xmlTree;
    }

    public function setDepPay_LastYear() {
        $db = JFactory::getDbo();
        $_codDepPayAnnPreOpiNode = $this->xmlTree->getElementsByTagName('codDepPayAnnPreOpi')->item(0);
        $_codTypDepPayAnnPreOpiNode =$this->xmlTree->getElementsByTagName('codTypDepPayAnnPreOpi')->item(0);

        if($_codDepPayAnnPreOpiNode->nodeValue !== null and !empty($_codDepPayAnnPreOpiNode->nodeValue)) {
            if ($_codDepPayAnnPreOpiNode->nodeValue == '100') {         # france
                // get France Dep
                $_getDepartmentSql = "select cod_dep from data_departement left join jos_emundus_1001_00 as je10 on je10.e_358_7943 = data_departement.id where je10.fnum = " . $this->fnum;
                $db->setQuery($_getDepartmentSql);

                $_codDepPayAnnPreOpiNode->nodeValue = $db->loadResult();
                $_codTypDepPayAnnPreOpiNode->nodeValue = 'D';
            } else {
                $_codTypDepPayAnnPreOpiNode->nodeValue = 'P';
            }
        }

        return $this->xmlTree;
    }

    /* custom voeux */
    public function setCustomVoeux() {
        $db = JFactory::getDbo();
        /* step 1 : check if this fnum has voeux (table "jos_emundus_scholarship_domain") */
        $sql = " select count(*) from jos_emundus_scholarship_domain as jesm where jesm.fnum like ('%" . $this->fnum . "%')";
        $db->setQuery($sql);

        $count = $db->loadResult();

        if($count == "0") {
            //get all voeux
            $items = $this->xmlTree->getElementsByTagName('item');
            $itemCount = $items->count();

            //get first voeux
            $firstVoeux = $items->item(0);

            // get cge
            $_getCodCge = "select dra.code_centre from data_referentiel_apogee as dra left join jos_emundus_setup_campaigns as jesc on dra.id = jesc.libelle_apogee left join jos_emundus_campaign_candidature as jecc on jecc.campaign_id = jesc.id where jecc.fnum like ('%" . $this->fnum . "%')";
            $db->setQuery($_getCodCge);
            $firstVoeux->childNodes[1]->nodeValue = $db->loadResult();

            // get codDip
            $_getCodDip = "select dra.code_diplome from data_referentiel_apogee as dra left join jos_emundus_setup_campaigns as jesc on dra.id = jesc.libelle_apogee left join jos_emundus_campaign_candidature as jecc on jecc.campaign_id = jesc.id where jecc.fnum like ('%" . $this->fnum . "%')";
            $db->setQuery($_getCodDip);
            $firstVoeux->childNodes[7]->nodeValue = $db->loadResult();

            // get codEtp
            $_getCodEtp = "select dra.code_etape from data_referentiel_apogee as dra left join jos_emundus_setup_campaigns as jesc on dra.id = jesc.libelle_apogee left join jos_emundus_campaign_candidature as jecc on jecc.campaign_id = jesc.id where jecc.fnum like ('%" . $this->fnum . "%')";
            $db->setQuery($_getCodEtp);
            $firstVoeux->childNodes[8]->nodeValue = $db->loadResult();

            $_getCodVrsVdi = "select dra.code_version from data_referentiel_apogee as dra left join jos_emundus_setup_campaigns as jesc on dra.id = jesc.libelle_apogee left join jos_emundus_campaign_candidature as jecc on jecc.campaign_id = jesc.id where jecc.fnum like ('%" . $this->fnum . "%')";
            $db->setQuery($_getCodVrsVdi);
            $firstVoeux->childNodes[14]->nodeValue = $db->loadResult();

            $_getCodVrsVet = "select dra.code_version_etape from data_referentiel_apogee as dra left join jos_emundus_setup_campaigns as jesc on dra.id = jesc.libelle_apogee left join jos_emundus_campaign_candidature as jecc on jecc.campaign_id = jesc.id where jecc.fnum like ('%" . $this->fnum . "%')";
            $db->setQuery($_getCodVrsVet);
            $firstVoeux->childNodes[15]->nodeValue = $db->loadResult();

            $firstVoeux->childNodes[17]->nodeValue = 1;

            for($index = 1; $index <= $itemCount; $index++) {
                foreach($items[$index]->childNodes as $children) {
                    $children->nodeValue = '';
                }
            }
        }
        return $this->xmlTree;
    }

    /* validate voeux => remove all empty voeux */
    public function validateVoeux() {
        /* get all items */

        $items = $this->xmlTree->getElementsByTagName('item');
        $itemCount = $items->count();

        for($index = 0; $index <= $itemCount-1; $index++) {
            /* find codCge */
            if(empty($items[$index]->getElementsByTagName('codCge')->item(0)->nodeValue)) {
                /* remove all children of $item[$index] */
                $children = $items[$index]->childNodes;
                foreach($children as $child) {
                    $child->nodeValue = '';
                }
            };
        }

        return $this->xmlTree;
    }

    /* set value to repeat group */
    public function setConvocation() {
        $db = JFactory::getDbo();

        $convocation_elems = array(
                "datCvc" => ["default" => 'N'],
                "dhhCvc" => ['sql' => "select concat(date_time, '>>> SPLIT <<<', cancelled, '>>> SPLIT <<<', published) from #__emundus_campaign_candidature where #__emundus_campaign_candidature.fnum = " . $this->fnum, "default" => 'N'],
                "dmnCvc" => ['sql' => "select concat(submitted, '>>> SPLIT <<<', cancelled, '>>> SPLIT <<<', date_submitted) from #__emundus_campaign_candidature where #__emundus_campaign_candidature.fnum = " . $this->fnum, "default" => 'N']
        );


        $_convocationNodes = $this->xmlTree->getElementsByTagName('convocation');
        $_convocationNodesCount = $_convocationNodes->count();

        for($_index = 0; $_index <= $_convocationNodesCount - 1; $_index++) {
            $_childs = $_convocationNodes[$_index]->childNodes;

            for($_count = 0; $_count <= count($_childs); $_count++) {
                if(in_array($_childs[$_count]->tagName, array_keys($convocation_elems))) {
                    // if query exists
                    if($convocation_elems[$_childs[$_count]->tagName]['sql'] !== null) {
                        // get query
                        $query = $convocation_elems[$_childs[$_count]->tagName]['sql'];

                        // run it // loadResults
                        $db->setQuery($query);
                        $res[$_childs[$_count]->tagName] = explode('>>> SPLIT <<<', $db->loadResult());
                    }
                    // otherwise
                    else {
                        // get default value
                        $default = $convocation_elems[$_childs[$_count]->tagName]['default'];
                        $res[$_childs[$_count]->tagName]['default'] = $default;
                    }
                }
            }
        }

        foreach(array_keys($convocation_elems) as $attr) {
            $attr_node = $this->xmlTree->getElementsByTagName($attr);

            for($_index = 0; $_index <= count($attr_node); $_index++) {
                if($res[$attr]['default'] === null) {
                    $attr_node[$_index]->nodeValue = $res[$attr][$_index];
                } else {
                    $attr_node[$_index]->nodeValue = $res[$attr]['default'];
                }
            }
        }
        return $this->xmlTree;
    }

    /* set value to repeat group */
    public function setTitreAccessExterne() {
        $db = JFactory::getDbo();

        $titreAccessExterne_elems = array(
            "codDacOpi" => ["default" => 'N'],
            "codDepPayDacOpi" => ['sql' => "select concat(fnum, '>>> SPLIT <<<', applicant_id, '>>> SPLIT <<<', user_id) from #__emundus_campaign_candidature where #__emundus_campaign_candidature.fnum = " . $this->fnum, "default" => 'N'],
            "codEtbDacOpi" => ['sql' => "select concat(submitted, '>>> SPLIT <<<', cancelled, '>>> SPLIT <<<', date_submitted) from #__emundus_campaign_candidature where #__emundus_campaign_candidature.fnum = " . $this->fnum, "default" => 'N'],
            "codTpeDacOpi" => ['sql' => "select concat(submitted, '>>> SPLIT <<<', cancelled, '>>> SPLIT <<<', date_submitted) from #__emundus_campaign_candidature where #__emundus_campaign_candidature.fnum = " . $this->fnum, "default" => 'N'],
            "codTypDepPayDacOpi" => ['sql' => "select concat(submitted, '>>> SPLIT <<<', cancelled, '>>> SPLIT <<<', date_submitted) from #__emundus_campaign_candidature where #__emundus_campaign_candidature.fnum = " . $this->fnum, "default" => 'N'],
            "daaDacOpi" => ['sql' => "select concat(submitted, '>>> SPLIT <<<', cancelled, '>>> SPLIT <<<', date_submitted) from #__emundus_campaign_candidature where #__emundus_campaign_candidature.fnum = " . $this->fnum, "default" => 'N']
        );

        $_titreAccessExterneNodes = $this->xmlTree->getElementsByTagName('titreAccesExterne');
        $_titreAccessExterneCount = $_titreAccessExterneNodes->count();

        for($_index = 0; $_index <= $_titreAccessExterneCount - 1; $_index++) {
            $_childs = $_titreAccessExterneNodes[$_index]->childNodes;

            for($_count = 0; $_count <= count($_childs); $_count++) {
                if(in_array($_childs[$_count]->tagName, array_keys($titreAccessExterne_elems))) {
                    // if query exists
                    if($titreAccessExterne_elems[$_childs[$_count]->tagName]['sql'] !== null) {
                        // get query
                        $query = $titreAccessExterne_elems[$_childs[$_count]->tagName]['sql'];

                        // run it // loadResults
                        $db->setQuery($query);
                        $res[$_childs[$_count]->tagName] = explode('>>> SPLIT <<<', $db->loadResult());
                    }
                    // otherwise
                    else {
                        // get default value
                        $default = $titreAccessExterne_elems[$_childs[$_count]->tagName]['default'];
                        $res[$_childs[$_count]->tagName]['default'] = $default;
                    }
                }
            }
        }

        foreach(array_keys($titreAccessExterne_elems) as $attr) {
            $attr_node = $this->xmlTree->getElementsByTagName($attr);

            for($_index = 0; $_index <= count($attr_node); $_index++) {
                if($res[$attr]['default'] === null) {
                    $attr_node[$_index]->nodeValue = $res[$attr][$_index];
                } else {
                    $attr_node[$_index]->nodeValue = $res[$attr]['default'];
                }
            }
        }
        return $this->xmlTree;
    }

    public function buildCustomSchema() {
        /// custom schema building
        $this->buildConvocation();
        $this->buildTitreAccessExterne();
        return $this->xmlTree;
    }

    public function buildCustomData() {
        /// custom data mapping
        $this->setCodPay_Address();
        $this->setBdiComAde_Address();
        $this->setDepPayDerDip_LastObtainDipl();
        $this->setDepPayAnt_LastFrequentEtb();
        $this->setDepPay_Civility();
        $this->setDepPay_LastYear();

        /* $this->setConvocation();
        $this->setTitreAccessExterne(); /// expected results :: print */

        // $this->setCustomVoeux();
        return $this->xmlTree;
    }
}
?>
