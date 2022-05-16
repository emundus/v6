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
            $node->nodeValue = empty($node->nodeValue) ? '100' : $node->nodeValue;
        }

        return $this->xmlTree;
    }

    public function setBdiComAde_Address() {
        ///////////////////////////////// ADRESSE ANNUELLE /////////////////////////////////
        $_aaRoot = $this->xmlTree->getElementsByTagName('adresseAnnuelle')->item(0);

        $_aaCodPay = $_aaRoot->getElementsByTagName('codPay')->item(0);
        $_aaCodBdi = $_aaRoot->getElementsByTagName('codBdi')->item(0);
        $_aaCodCom = $_aaRoot->getElementsByTagName('codCom')->item(0);
        $_aaLibAde = $_aaRoot->getElementsByTagName('libAde')->item(0);

        $bdiFrance = "";
        $libAde = "";
        if ($_aaCodPay->nodeValue == '100') {        /// # france
            $bdiFrance = $this->getBdiFromFnum($this->fnum);
            $_aaCodBdi->nodeValue = $bdiFrance;
        } else {                                    /// # not france
            $_aaCodBdi->nodeValue = "";
            $libAde = $this->getLibAdeFromFnum($this->fnum);
            $_aaLibAde->nodeValue =  $libAde;
        }

        ///////////////////////////////// ADRESSE FIXE /////////////////////////////////
        $_afRoot = $this->xmlTree->getElementsByTagName('adresseFixe')->item(0);

        $_afCodPay = $_afRoot->getElementsByTagName('codPay')->item(0);
        $_afCodBdi = $_afRoot->getElementsByTagName('codBdi')->item(0);
        $_afCodCom = $_afRoot->getElementsByTagName('codCom')->item(0);
        $_afLibAde = $_afRoot->getElementsByTagName('libAde')->item(0);

        if ($_afCodPay->nodeValue == '100') {        /// #france
            $_afCodBdi->nodeValue = !empty($bdiFrance) ? $bdiFrance : $this->getBdiFromFnum($this->fnum);
        } else {                                    /// # not france
            $_afCodBdi->nodeValue = "";
            $_afLibAde->nodeValue = !empty($libAde) ? $libAde : $this->getLibAdeFromFnum($this->fnum);
        }

        ///////////////////////////////////////////// Done /////////////////////////////////////////////

        /* Quote "Si l’un des deux champs (code commune ou code bureau distributeur) est vide ou incohérent par rapport à la table COM_BDI d’Apogée,
            les deux données sont remises à blanc et les autres données de l’adresse sont chargées, à condition que le code pays soit renseigné
            et valide par rapport à la table PAYS d’Apogée.
        */
        if($_aaCodBdi->nodeValue == null || $_aaCodCom->nodeValue == null) {
            $_aaCodCom->nodeValue = '';
            $_aaCodBdi->nodeValue = '';
        }

        if($_afCodBdi->nodeValue == null || $_afCodCom->nodeValue == null) {
            $_afCodCom->nodeValue = '';
            $_afCodBdi->nodeValue = '';
        }

        return $this->xmlTree;
    }

    private function getBdiFromFnum($fnum) {
        $bdiFrance = '';
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->clear()->select("lpad('#__emundus_personal_detail.etu_code_postal',5,'0')")
            ->from('#__emundus_personal_detail')
            ->where('#__emundus_personal_detail.fnum = ' . $db->quote($fnum));

        $db->setQuery($query);

        try {
            $bdiFrance = $db->loadResult();
        } catch (Exception $e) {
            JLog::add('Error getting Bdi for fnum ' . $fnum . ' in plugin_emundus_export_xml at line ' . __LINE__, JLog::ERROR, 'com_emundus');
        }

        return $bdiFrance;
    }

    private function getLibAdeFromFnum($fnum) {
        $libAde = '';
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->clear()->select("trim(right(concat(#__emundus_personal_detail.e_287_8117, ' ', #__emundus_personal_detail.e_287_8118),32))")
            ->from('#__emundus_personal_detail')
            ->where('#__emundus_personal_detail.fnum = ' . $db->quote($fnum));
        $db->setQuery($query);

        try {
            $libAde = $db->loadResult();
        } catch (Exception $e) {
            JLog::add('Error getting Bdi for fnum ' . $fnum . ' in plugin_emundus_export_xml at line ' . __LINE__, JLog::ERROR, 'com_emundus');
        }

        return $libAde;
    }

    public function setDepPayDerDip_LastObtainDipl() {
        /* -- Si FRANCE (code = 100) --> codDepPayDerDip = Departement // codTypDepPayDerDip = "D" */
        /* -- Si pas FRANCE (code != 100) --> codDepPayDerDip = Pays // codTypDepPayDerDip = "P" */

        /// find "codDepPayDerDip" node
        $_codDepPayDerDipNode = $this->xmlTree->getElementsByTagName('codDepPayDerDip')->item(0);
        $_codTypDepPayDerDipNode = $this->xmlTree->getElementsByTagName('codTypDepPayDerDip')->item(0);

        if($_codDepPayDerDipNode->nodeValue == '100') {
            /// set $_codDepPayDerDipNode->nodeValue by France Department

            $db = JFactory::getDbo();
            $_getDepartmentSql = "select cod_dep from data_departements left join jos_emundus_1001_00 as je10 on je10.dep_etb_last_dip = data_departements.departement_code where je10.fnum = " . $this->fnum;
            $db->setQuery($_getDepartmentSql);
            $_codDepPayDerDipNode->nodeValue = $db->loadResult();
            $_codTypDepPayDerDipNode->nodeValue = 'D';
        } else {
            $_codTypDepPayDerDipNode->nodeValue = 'P';
        }

        return $this->xmlTree;
    }

    public function setDepPayAnt_LastFrequentEtb() {
        /* -- Si FRANCE (code = 100) --> codDepPayAntIaaOpi = Departement // codTypDepPayAntIaaOpi = "D" */
        /* -- Si pas FRANCE (code != 100) --> codDepPayAntIaaOpi = Pays // codTypDepPayAntIaaOpi = "P" */
        $_codDepPayAntIaaOpiNode = $this->xmlTree->getElementsByTagName('codDepPayAntIaaOpi')->item(0);
        $_codTypDepPayAntIaaOpiNode = $this->xmlTree->getElementsByTagName('codTypDepPayAntIaaOpi')->item(0);

        if ($_codDepPayAntIaaOpiNode->nodeValue == '100') {
            /// set $_codDepPayDerDipNode->nodeValue by France Department

            $db = JFactory::getDbo();
            $_getDepartmentSql = "select cod_dep from data_departements left join jos_emundus_1001_00 as je10 on je10.dep_etb_dernier = data_departements.departement_code where je10.fnum = " . $this->fnum;
            $db->setQuery($_getDepartmentSql);
            $_codDepPayAntIaaOpiNode->nodeValue = $db->loadResult();
            $_codTypDepPayAntIaaOpiNode->nodeValue = 'D';
        } else {
            $_codTypDepPayAntIaaOpiNode->nodeValue = 'P';
        }

        return $this->xmlTree;
    }

    public function setDepPay_Civility() {
        $_codDepPayNaiNode = $this->xmlTree->getElementsByTagName('codDepPayNai')->item(0);
        $_codTypDepPayNaiNode = $this->xmlTree->getElementsByTagName('codTypDepPayNai')->item(0);

        if ($_codDepPayNaiNode->nodeValue == '100') {
            /* get French department if $_codDepPayNaiNode is 100 */
            $db = JFactory::getDbo();
            $_getDepartmentSql = "select cod_dep from data_departements left join jos_emundus_personal_detail as jepd on jepd.etu_dept_nais = data_departements.departement_code where jepd.fnum = " . $this->fnum;
            $db->setQuery($_getDepartmentSql);
            $_codDepPayNaiNode->nodeValue = $db->loadResult();
            $_codTypDepPayNaiNode->nodeValue = 'D';
        } else {
            $_codTypDepPayNaiNode->nodeValue = 'P';
        }

        return $this->xmlTree;
    }

    public function setDepPay_LastYear() {
        $_codDepPayAnnPreOpiNode = $this->xmlTree->getElementsByTagName('codDepPayAnnPreOpi')->item(0);
        $_codTypDepPayAnnPreOpiNode =$this->xmlTree->getElementsByTagName('codTypDepPayAnnPreOpi')->item(0);

        if (!empty($_codDepPayAnnPreOpiNode->nodeValue)) {
            if ($_codDepPayAnnPreOpiNode->nodeValue == '100') {         # france
                // get France Dep
                $db = JFactory::getDbo();
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

            //get data_referentiel_apogee data
            $getApogeeRefQuery = "select dra.code_centre,dra.code_diplome,dra.code_etape,dra.code_version,dra.code_version_etape from data_referentiel_apogee as dra left join jos_emundus_setup_campaigns as jesc on dra.id = jesc.libelle_apogee left join jos_emundus_campaign_candidature as jecc on jecc.campaign_id = jesc.id where jecc.fnum like ('%" . $this->fnum . "%')";
            $db->setQuery($getApogeeRefQuery);
            $getApogeeRef = $db->loadObject();

            $firstVoeux->childNodes[1]->nodeValue = $getApogeeRef->code_centre;
            $firstVoeux->childNodes[7]->nodeValue = $getApogeeRef->code_diplome;
            $firstVoeux->childNodes[8]->nodeValue = $getApogeeRef->code_etape;
            $firstVoeux->childNodes[14]->nodeValue = $getApogeeRef->code_version;
            $firstVoeux->childNodes[15]->nodeValue = $getApogeeRef->code_version_etape;

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

                /* get the "parent" of "item" */
                $items[$index]->nodeValue = '';
            }
        }

        return $this->xmlTree;
    }

    /* set Baccalaureat code for foreign students */
    public function setCodBac() {
        $bac = $this->xmlTree->getElementsByTagName('bac')->item(0);
        $codBac = $bac->getElementsByTagName('codBac')->item(0);
        $codBac->nodeValue = empty($codBac->nodeValue) ? '0031' : $codBac->nodeValue;

        return $this->xmlTree;
    }

    /* handle "premiereInscription" : set empty string for DAA_ENS_SUP_OPI and DAA_ENT_ETB_OPI if the candidat come from foreign country */
    public function setCustomFirstYear() {
        $db = JFactory::getDbo();

        $premiereInscription = $this->xmlTree->getElementsByTagName('premiereInscription')->item(0);
        $daaEnsSupOpi = $premiereInscription->getElementsByTagName('daaEnsSupOpi')->item(0);

        /* first, set daaEnsSupOpi country */
        $countryEnsSupOpiQuery = "SELECT jos_emundus_1001_00.pays_enseignement_sup FROM jos_emundus_1001_00 WHERE fnum like ('%" . $this->fnum . "%')";
        $db->setQuery($countryEnsSupOpiQuery);
        $countryEnsSupOpi = $db->loadResult();

        $daaEnsSupOpi->nodeValue = $countryEnsSupOpi !== '100' ? "" : $daaEnsSupOpi->nodeValue;

        $daaEntEtbOpi = $premiereInscription->getElementsByTagName('daaEntEtbOpi')->item(0);

        /* get daaEntEtbOpi country */
        $countryEntEtbOpiQuery = "SELECT jos_emundus_1001_00.country_univ FROM jos_emundus_1001_00 WHERE fnum like ('%" . $this->fnum . "%')";
        $db->setQuery($countryEntEtbOpiQuery);
        $countryEntSupOpi = $db->loadResult();

        $daaEntEtbOpi->nodeValue =  $countryEntSupOpi !== '100' ? "" : $daaEntEtbOpi->nodeValue;

        return $this->xmlTree;
    }

    /* handle telephone number with max 15 digits from left to right (no country code) */
    public function setCustomTelephoneNumber() {
        $db = JFactory::getDbo();

        /* find all xml nodes of numTel, we have at least 3 : numTel (aa), numTel (af), numTelPorOpi */

        $_aaRoot = $this->xmlTree->getElementsByTagName('adresseAnnuelle')->item(0);
        $_aaTel = $_aaRoot->getElementsByTagName('numTel')->item(0);

        $_afRoot = $this->xmlTree->getElementsByTagName('adresseFixe')->item(0);
        $_afTel = $_afRoot->getElementsByTagName('numTel')->item(0);

        $_dpRoot = $this->xmlTree->getElementsByTagName('donneesPersonnelles')->item(0);
        $_dpTelOpi = $_dpRoot->getElementsByTagName('numTelPorOpi')->item(0);

        /// get telephone number
        $getTelNumQuery = "select trim(replace(replace(#__emundus_personal_detail.etu_telephone,' ',''), '+', '')) from #__emundus_personal_detail where #__emundus_personal_detail.fnum =  " . $this->fnum;
        $db->setQuery($getTelNumQuery);
        $tel = $db->loadResult();

        # find ")" in $rawAaTel
        if(strpos($tel, ")")) {
            # split string
            $tel = explode(')', $tel)[1];

            if(strlen($tel) > 15) {
                # truncate string if length is more than 15
                $tel = substr($tel,0,15);
            }
        }

        $_aaTel->nodeValue = $tel;
        $_afTel->nodeValue = $tel;
        $_dpTelOpi->nodeValue = $tel;

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
                        $query = $convocation_elems[$_childs[$_count]->tagName]['sql'];

                        $db->setQuery($query);
                        $res[$_childs[$_count]->tagName] = explode('>>> SPLIT <<<', $db->loadResult());
                    } else {
                        $default = $convocation_elems[$_childs[$_count]->tagName]['default'];
                        $res[$_childs[$_count]->tagName]['default'] = $default;
                    }
                }
            }
        }

        foreach(array_keys($convocation_elems) as $attr) {
            $attr_node = $this->xmlTree->getElementsByTagName($attr);

            for ($_index = 0; $_index <= count($attr_node); $_index++) {
                $attr_node[$_index]->nodeValue = $res[$attr]['default'] === null ? $res[$attr][$_index] : $res[$attr]['default'];
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
                        $query = $titreAccessExterne_elems[$_childs[$_count]->tagName]['sql'];

                        $db->setQuery($query);
                        $res[$_childs[$_count]->tagName] = explode('>>> SPLIT <<<', $db->loadResult());
                    } else {
                        $default = $titreAccessExterne_elems[$_childs[$_count]->tagName]['default'];
                        $res[$_childs[$_count]->tagName]['default'] = $default;
                    }
                }
            }
        }

        foreach(array_keys($titreAccessExterne_elems) as $attr) {
            $attr_node = $this->xmlTree->getElementsByTagName($attr);

            for ($_index = 0; $_index <= count($attr_node); $_index++) {
                $attr_node[$_index]->nodeValue = $res[$attr]['default'] === null ? $res[$attr][$_index] : $res[$attr]['default'];
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
        $this->setCodBac();
        $this->setCustomFirstYear();
        $this->setCustomTelephoneNumber();

        /* $this->setConvocation();
        $this->setTitreAccessExterne(); /// expected results :: print */

        // $this->setCustomVoeux();
        return $this->xmlTree;
    }
}
