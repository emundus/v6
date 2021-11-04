<?php
/// activate debug mode
ini_set('display_errors','On');
ini_set('soap.wsdl_cache_enabled', 0);
require('AbstractXmlInterface.php');

/*
 * XmlSchema >>> build your own XML Tree from description file
 * */
class XmlSchema implements AbstractXmlInterface {

    /// class variables
    var $xmlDocument = null;
    var $soapENV;
    var $description;

    public function __construct($description) {
        /// init DOM Document
        $this->xmlDocument = new DOMDocument('1.0', 'UTF-8');
        $this->xmlDocument->preserveWhiteSpace = false;
        $this->xmlDocument->formatOutput = true;
        $this->description = $description;
    }

    public function buildXMLSchema($namespaces = array()) {
        // TODO: Implement buildXMLSchema() method.

        /// build ENV
        $this->soapENV = $this->xmlDocument->createElementNS('http://schemas.xmlsoap.org/soap/envelope/', 'soapenv:Envelope');

        /// build NAMESPACES
        foreach($namespaces as $key => $value) { $this->soapENV->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:' . $key, $value); }

        //// build ROOT
        $this->xmlDocument->appendChild($this->soapENV);

        /// return a XML document
        return $this->xmlDocument;
    }

    public function addElements($elements, $root, $elementAttr = null, $elementValue = null) {
        // TODO: Implement addElements() method.

        /// create children elements
        $root_local = null;
        foreach($elements as $element) {
            $root_local = $this->xmlDocument->createElement($element, $elementValue);
            $root->appendChild($root_local);

            if($elementAttr !== null) {
                $root_local->setAttribute('duplicata' , $elementAttr);
                $root->appendChild($root_local);
            }

        }
        return $root_local;
    }

    public function exportXMLString($xmlTree) {
        // TODO: Implement exportXMLString() method.
        return $xmlTree->saveXML();
    }

    public function exportXMLFile($xmlTree, $xmlFileName=null) {
        // TODO: Implement exportXMLFile() method.
        if(!empty($xmlFileName)) { return $xmlTree->save($xmlFileName . '.xml'); }
        else { return $xmlTree->save(get_class() . '.xml'); }
    }

    public function getSchemaDescription() {
        return json_decode(file_get_contents(dirname(EMUNDUS_PATH_ABS) . DS . 'letters' . DS . $this->description));
    }

    // build SOAP Request Schema
    public function buildSoapRequest($schema) {
        $res = new stdClass();

        $res->hasSubData = false;
        $res->hasRepeatData = false;

        $json_body = $this->getSchemaDescription();

        /// get namespaces
        $json_ns = (array)$json_body->namespaces;

        $apogeeXMLSoapRequest = $this->buildXMLSchema($json_ns);

        //get header and body
        $json_hd = $json_body->header;
        $json_bd = $json_body->body;

        $ns_node = $apogeeXMLSoapRequest->getElementsByTagNameNS('http://schemas.xmlsoap.org/soap/envelope/', '*')->item(0);
        $this->addElements(array($json_hd, $json_bd), $ns_node);

        /// get method
        $json_mth = $json_body->method;
        $body_node = $apogeeXMLSoapRequest->getElementsByTagName($json_bd)->item(0);

        $this->addElements(array($json_mth), $body_node);

        /// get data
        $json_dt = $json_body->data;

        /// get all keys of $json_dt
        $json_dt_keys = array_keys((array)$json_dt);

        /// create child from ## method ##
        $mt_node = $apogeeXMLSoapRequest->getElementsByTagName($json_mth)->item(0);
        $this->addElements($json_dt_keys, $mt_node);

        //// bind all DATA to METHOD
        foreach($json_dt_keys as $js_key) {
            $js_key_node = $apogeeXMLSoapRequest->getElementsByTagName($js_key)->item(0);

            try {
                $this->addElements(array_keys((array)$json_dt->$js_key), $js_key_node);
                $js_key_fields = array_keys((array)$json_dt->$js_key);
                foreach($js_key_fields as $js_field) { $js_field_node = $apogeeXMLSoapRequest->getElementsByTagName($js_field)->item(0); $this->addElements($json_dt->$js_key->$js_field, $js_field_node); }
            } catch(Exception $e) {
                $this->addElements(array_values($json_dt->$js_key),$js_key_node);
            }
        }

        /// sometimes, some fields of DATA can contains it-self sub-data
        if(!is_null($json_body->subData)) {
            $res->hasSubData = true;
            $json_sbdt = $json_body->subData;
            $json_sbdt_keys = array_keys((array)$json_sbdt);

            foreach ($json_sbdt_keys as $js_sbdt_key) { $parent_node = $apogeeXMLSoapRequest->getElementsByTagName($js_sbdt_key)->item(0); $this->addElements($json_sbdt->$js_sbdt_key, $parent_node); }
        }

        /// sometimes, some fields of DATA can repeat
        if(!is_null($json_body->repeat)) {
            $res->hasSubData = false;
            $json_array = $json_body->repeat;

            /// every key of $json_array always exist in $DATA or $SUBDATA --> so, get all of keys
            $json_array_keys = array_keys((array)$json_array);              /// e.g: "item1", "item2"

            foreach ($json_array_keys as $js_array_key) {
                /// get occurrence
                $js_array_occurrence = $json_array->$js_array_key->occurrence;

                /// find parent node
                $parent_node = $apogeeXMLSoapRequest->getElementsByTagName($js_array_key)->item(0);

                //if ($js_array_occurrence > 1) {
                $grandFather_node_name = $parent_node->parentNode->tagName;
                $grandFather_node = $apogeeXMLSoapRequest->getElementsByTagName($grandFather_node_name)->item(0);

                /// remove all childs of $grandFather_node
                $grandFather_node->removeChild($parent_node);

                /// recursive create parent node, e.g: <item/><item/><item/>
                for ($index = 0; $index <= $js_array_occurrence-1; $index++) { $this->addElements([$js_array_key], $grandFather_node, $index); }

                /// create child node for each parent node, e.g: <item/><item/><item/>
                /// count how many parent node
                $parent_node_count = $apogeeXMLSoapRequest->getElementsByTagName($parent_node->tagName)->count();          //// e.g: int(3)

                /// iterate each node of $parent_node
                for($counter = 0; $counter <= $parent_node_count-1; $counter++) {
                    ///
                    $p_node = $apogeeXMLSoapRequest->getElementsByTagName($js_array_key)->item($counter);

                    /// add elements of each $p_node
                    $js_array_fields = $json_array->$js_array_key->elements;
                    $this->addElements($js_array_fields, $p_node);
                }
            }
        }
        return $apogeeXMLSoapRequest;
    }
}

//$apogee_paris2 = new XmlSchema();
//$soap_request = $apogee_paris2->buildSoapRequest('apogee_description_test.json');     /// replace by your json file
//
//$apogee_paris2->exportXMLFile($soap_request, 'apogee');