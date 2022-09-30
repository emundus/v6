<?php

/*
 * AbstractXmlInterface >> all abstract methods
 * */

interface AbstractXmlInterface {
    public function buildXMLSchema($namespaces=array());
    public function addElements($elements, $root, $elementAttr=null, $elementValue=null);
    public function exportXMLString($xmlTree);
    public function exportXMLFile($xmlTree, $xmlFileName=null);
}