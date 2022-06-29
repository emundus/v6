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
            };
        }

        return $this->xmlTree;
    }

    /* handle telephone number with max 15 digits from left to right (no country code) */
    public function setCustomTelephoneNumber() {
        $db = JFactory::getDbo();

        /* find all xml nodes of numTel, we have at least 3 : numTel (aa), numTel (af), numTelPorOpi */

        /* firstly, find "adresseAnnuelle" node */
        $_aaRoot = $this->xmlTree->getElementsByTagName('adresseAnnuelle')->item(0);
        $_aaTel = $_aaRoot->getElementsByTagName('numTel')->item(0);

        /* find "adresseFixe" node */
        $_afRoot = $this->xmlTree->getElementsByTagName('adresseFixe')->item(0);
        $_afTel = $_afRoot->getElementsByTagName('numTel')->item(0);

        /* find "donneesPersonnelles" node */
        $_dpRoot = $this->xmlTree->getElementsByTagName('donneesPersonnelles')->item(0);
        $_dpTelOpi = $_dpRoot->getElementsByTagName('numTelPorOpi')->item(0);

        /// get telephone number
        $getTelNumQuery = "select trim(replace(replace(#__emundus_personal_detail.etu_telephone,' ',''), '+', '')) from #__emundus_personal_detail where #__emundus_personal_detail.fnum =  " . $this->fnum;
        $db->setQuery($getTelNumQuery);
        $getTel = $db->loadResult();

        # find ")" in $rawAaTel
        if(strpos($getTel, ")")) {
            # split string
            $getTel = explode(')', $getTel)[1];

            if(strlen($getTel) > 15) {
                # truncate string if length is more than 15
                $getTel = substr($getTel,0,15);
            }
        }

        $_aaTel->nodeValue = $getTel;
        $_afTel->nodeValue = $getTel;
        $_dpTelOpi->nodeValue = $getTel;

        return $this->xmlTree;
    }

    /* set value to repeat group
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
    }*/

    /* set value to repeat group */
    /*public function setTitreAccessExterne() {
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
    }*/

    /*public function buildCustomSchema() {
        /// custom schema building
        $this->buildConvocation();
        $this->buildTitreAccessExterne();
        return $this->xmlTree;
    }*/
}
?>
