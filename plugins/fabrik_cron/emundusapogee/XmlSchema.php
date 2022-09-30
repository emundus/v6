<?php
# activate/desactivate debug mode
ini_set('display_errors','On');

# activate/desactivate SOAP WSDL Cache
ini_set('soap.wsdl_cache_enabled', 0);

require('AbstractXmlInterface.php');

/*
 * XmlSchema : build your own XML request from schema description file
 * */

class XmlSchema implements AbstractXmlInterface {
    var $xmlDocument = null;
    var $soapENV;
    var $description;

    public function __construct($description) {
        # init DOM Document (with format)
        $this->xmlDocument = new DOMDocument('1.0', 'UTF-8');
        $this->xmlDocument->preserveWhiteSpace = false;
        $this->xmlDocument->formatOutput = true;
        $this->description = $description;
    }

    public function buildXMLSchema($namespaces = array()) {
        // TODO: Implement buildXMLSchema() method.

        # init SOAP Envelop - which contains all namespaces
        $this->soapENV = $this->xmlDocument->createElementNS('http://schemas.xmlsoap.org/soap/envelope/', 'soapenv:Envelope');

        # foreach your own namespace, we attach attribut "xmlns" (w3c standard)
        foreach($namespaces as $key => $value) { $this->soapENV->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:' . $key, $value); }

        # assign SOAP Envelop being the root of request
        $this->xmlDocument->appendChild($this->soapENV);

        return $this->xmlDocument;
    }

    public function addElements($elements, $root, $elementAttr = null, $elementValue = null) {
        // TODO: Implement addElements() method.

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

    # JSON decode request schema description
    public function getSchemaDescription() {
        return json_decode(file_get_contents(JPATH_SITE . $this->description));
    }

    # build SoapRequest
    public function buildSoapRequest($schema) {
        $res = new stdClass();

        $res->hasSubData = false;
        $res->hasRepeatData = false;

        $json_body = $this->getSchemaDescription();

        # get all namespaces
        $json_ns = (array)$json_body->namespaces;

        # build XML request (just skeleton)
        $apogeeXMLSoapRequest = $this->buildXMLSchema($json_ns);

        # get header and body
        $json_hd = $json_body->header;
        $json_bd = $json_body->body;

        # add header and body to XML request
        $ns_node = $apogeeXMLSoapRequest->getElementsByTagNameNS('http://schemas.xmlsoap.org/soap/envelope/', '*')->item(0);
        $this->addElements(array($json_hd, $json_bd), $ns_node);

        # get API method (service)
        $json_mth = $json_body->method;

        # add API method to XML request
        $body_node = $apogeeXMLSoapRequest->getElementsByTagName($json_bd)->item(0);
        $this->addElements(array($json_mth), $body_node);

        # get data structure of XML request
        $json_dt = $json_body->data;

        # (Step 1) : get all data field from data structure
        $json_dt_keys = array_keys((array)$json_dt);

        # (Iterate 1) : for each data field, we attach it sub elements
        $mt_node = $apogeeXMLSoapRequest->getElementsByTagName($json_mth)->item(0);
        $this->addElements($json_dt_keys, $mt_node);

        # bind all these data to last method
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

        # However, sometimes, some data fields can contains it-self sub elements (inception level 1) : so, we must dive into them and keep binding
        # Hint: looking for 'subData'
        if(!is_null($json_body->subData)) {
            $res->hasSubData = true;
            $json_sbdt = $json_body->subData;
            $json_sbdt_keys = array_keys((array)$json_sbdt);

            foreach ($json_sbdt_keys as $js_sbdt_key) { $parent_node = $apogeeXMLSoapRequest->getElementsByTagName($js_sbdt_key)->item(0); $this->addElements($json_sbdt->$js_sbdt_key, $parent_node); }
        }

        # Additionally, sometimes, some data fields can repeat many times. E.g, a student can have 3 or 4 options when candidating
        # Hint: looking for 'repeat'
        # A repetitive data field can contain it-self sub elements or not
        if(!is_null($json_body->repeat)) {
            $res->hasSubData = false;
            $json_array = $json_body->repeat;

            # every key of $json_array always exist in $DATA or $SUBDATA --> so, get all of keys
            $json_array_keys = array_keys((array)$json_array);              /// e.g: "item1", "item2"

            foreach ($json_array_keys as $js_array_key) {
                # get occurrence time
                $js_array_occurrence = $json_array->$js_array_key->occurrence;

                # find parent node
                $parent_node = $apogeeXMLSoapRequest->getElementsByTagName($js_array_key)->item(0);

                //if ($js_array_occurrence > 1) {
                $grandFather_node_name = $parent_node->parentNode->tagName;
                $grandFather_node = $apogeeXMLSoapRequest->getElementsByTagName($grandFather_node_name)->item(0);

                # remove all childs of $grandFather_node (** explaint: since at this time, all childs nodes has been generated, but they are the same **)
                $grandFather_node->removeChild($parent_node);

                # recursive create parent node, e.g: <item/><item/><item/>
                # for each node, we add attribut has the form 'duplicata' + int (e.g: 'duplicata 0', 'duplicata 1', etc)
                for ($index = 0; $index <= $js_array_occurrence-1; $index++) { $this->addElements([$js_array_key], $grandFather_node, $index); }

                # count how many parent node (they will be well-distinguished by 'duplicata' attribut)
                $parent_node_count = $apogeeXMLSoapRequest->getElementsByTagName($parent_node->tagName)->count();          //// e.g: int(3)

                # iterate each $parent_node
                for($counter = 0; $counter <= $parent_node_count-1; $counter++) {
                    $p_node = $apogeeXMLSoapRequest->getElementsByTagName($js_array_key)->item($counter);

                    # and then bind each repeat-sub-data to $parent_node
                    $js_array_fields = $json_array->$js_array_key->elements;
                    $this->addElements($js_array_fields, $p_node);
                }
            }
        }
        return $apogeeXMLSoapRequest;
    }
}