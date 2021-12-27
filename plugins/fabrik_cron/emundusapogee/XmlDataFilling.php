<?php

/*
 * XmlDataFilling >> Filling up the predefined XML tree with your own data from Data Mapping file
 * */

ini_set('display_errors','1');                /// turn off Error Displaying
ini_set('soap.wsdl_cache_enabled', 1);
error_reporting(E_ALL);

set_time_limit(0);                                  /// turn off time limit (the code may run longer)

/// import XmlSchema
require_once("XmlSchema.php");

/// init Joomla App :: see here https://stackoverflow.com/questions/13589069/using-jimport-in-my-own-script
//define( '_JEXEC', 1 );
//define('JPATH_BASE', dirname(__DIR__) . '/../../');
//include_once ( JPATH_BASE . 'includes/defines.php' );
//include_once ( JPATH_BASE . 'includes/framework.php' );
//
//jimport('joomla.user.helper');
//jimport( 'joomla.application.application' );
//jimport('joomla.plugin.helper');
//
//$app = JFactory::getApplication('site');
//$app->initialise();

class XmlDataFilling {
    var $db = null;
    var $query = null;
    var $jsonDataFile;

    public function __construct($_data) {
        $this->db = JFactory::getDbo();
        $this->query = $this->db->getQuery(true);

        $this->jsonDataFile = $_data;
    }

    /// get data mapping description
    public function getDataMapping() {
        return json_decode(file_get_contents(dirname(EMUNDUS_PATH_ABS) . DS . 'letters' . DS . $this->jsonDataFile));
    }

    public function fillData($xmlDocument, $_description, $fnum) {
        /// get schema description
        $jsonDescriptionBody = $_description;

        /// get data mapping
        $jsonDataBody = $this->getDataMapping();

        /// get all keys of json data mapping
        $jsonKeys = array_keys((array)$jsonDataBody);

        /// iterate each json key
        foreach ($jsonKeys as $js_key) {
            /// get DOM node from each json key
            $rootDom = $xmlDocument->getElementsByTagName($js_key);

            /// get root node
            $rootNode = $rootDom->item(0);

            /// check if $js_key has property or not
            $hasProperty = count(get_object_vars($jsonDataBody->$js_key));            /// bool

            /// if tag have properties --> find sql query
            if ($hasProperty > 0) {
                // get all properties
                $propertyName = array_keys((array)$jsonDataBody->$js_key);
                $rootNodeCount = $rootDom->count();

                // iterate each property
                foreach ($propertyName as $pr_name) {
                    if (is_null($jsonDescriptionBody->subData) && is_null($jsonDescriptionBody->repeat)) {

                        $childNode = $xmlDocument->getElementsByTagName($pr_name);

                        foreach($childNode as $_ch) {
                            if ($_ch->parentNode->nodeName === $js_key) {
                                if(!is_null($jsonDataBody->$js_key->$pr_name->default)) {
                                    if (is_null($jsonDataBody->$js_key->$pr_name->sql) or ($jsonDataBody->$js_key->$pr_name->sql === "")) {
                                        $_ch->nodeValue = $jsonDataBody->$js_key->$pr_name->default;
                                    } else {
                                        $this->buildSql($xmlDocument, $_ch, null, $jsonDataBody->$js_key->$pr_name->sql . $fnum, false);
                                    }
                                } else {
                                    $this->buildSql($xmlDocument, $_ch, null, $jsonDataBody->$js_key->$pr_name->sql . $fnum, false);
                                }
                            }
                        }
                    }
                    else {
                        if (!is_null($jsonDescriptionBody->subData) and is_null($jsonDescriptionBody->repeat)) {
                            $inSubData = in_array($pr_name, array_keys((array)$jsonDescriptionBody->subData));

                            if ($inSubData) {
                                // get all subProps
                                $_subProps = $jsonDescriptionBody->subData->$pr_name;             /// array
                                foreach ($_subProps as $_sp) {
                                    if(!is_null($jsonDataBody->$js_key->$pr_name->$_sp->default)) {
                                        if (is_null($jsonDataBody->$js_key->$pr_name->$_sp->sql) or ($jsonDataBody->$js_key->$pr_name->$_sp->sql === "")) {
                                            $_sp->nodeValue = $jsonDataBody->$js_key->$pr_name->$_sp->default;
                                        } else {
                                            $this->buildSql($xmlDocument, null, $_sp, $jsonDataBody->$js_key->$pr_name->$_sp->sql . $fnum, false);
                                        }
                                    } else {
                                        $this->buildSql($xmlDocument, null, $_sp, $jsonDataBody->$js_key->$pr_name->$_sp->sql . $fnum, false);
                                    }
                                }
                            }
                            else {
                                $childNode = $xmlDocument->getElementsByTagName($pr_name);

                                foreach($childNode as $_ch) {
                                    if ($_ch->parentNode->nodeName === $js_key) {
                                        if(!is_null($jsonDataBody->$js_key->$pr_name->default)) {
                                            if (is_null($jsonDataBody->$js_key->$pr_name->sql) or ($jsonDataBody->$js_key->$pr_name->sql === "")) {
                                                $_ch->nodeValue = $jsonDataBody->$js_key->$pr_name->default;
                                            } else {
                                                $this->buildSql($xmlDocument, $_ch, null, $jsonDataBody->$js_key->$pr_name->sql . $fnum, false);
                                            }
                                        } else {
                                            $this->buildSql($xmlDocument, $_ch, null, $jsonDataBody->$js_key->$pr_name->sql . $fnum, false);
                                        }
                                    }
                                }
                            }
                        }
                        else if (!is_null($jsonDescriptionBody->repeat) and is_null($jsonDescriptionBody->subData)) {
                            $inRepeatData = in_array($pr_name, array_keys((array)$jsonDescriptionBody->repeat));            /// bool
                            $repeat_times = $jsonDescriptionBody->repeat->$pr_name->occurrence;                           /// int

                            if (!$inRepeatData || is_null($repeat_times) || $repeat_times === 1) {

                                /// all <repeat elements> always have sub properties
                                $subProps = array_values((array)$jsonDescriptionBody->repeat->$pr_name->elements);            /// always (0) or (1)

                                /// check if $root_node has child node
                                $hasChildren = $rootNode->hasChildNodes();

                                if (count($subProps) === 0) {  // no repeat
                                    if ($hasChildren) {
                                        $rootChildren = $rootNode->childNodes;

                                        foreach ($rootChildren as $child) {
                                            if (in_array($child->tagName, $propertyName)) {
                                                /// find child
                                                $childNode = $xmlDocument->getElementsByTagName($child->tagName);  /// return an array
                                                ///
                                                foreach ($childNode as $_ch) {
                                                    if ($_ch->parentNode->nodeName === $js_key) {
                                                        if ($pr_name == $child->tagName) {
                                                            if(!is_null($jsonDataBody->$js_key->$pr_name->default)) {
                                                                if (is_null($jsonDataBody->$js_key->$pr_name->sql) or ($jsonDataBody->$js_key->$pr_name->sql === "")) {
                                                                    $_ch->nodeValue = $jsonDataBody->$js_key->$pr_name->default;
                                                                } else {
                                                                    $this->buildSql($xmlDocument, $_ch, null, $jsonDataBody->$js_key->$pr_name->sql . $fnum, false);
                                                                }
                                                            } else {
                                                                $this->buildSql($xmlDocument, $_ch, null, $jsonDataBody->$js_key->$pr_name->sql . $fnum, false);
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                } else {
                                    if ($hasChildren) {
                                        /// iterate children
                                        $rootChildren = $rootNode->childNodes;

                                        foreach ($rootChildren as $child) {

                                            /// each $child has childNodes
                                            $subChildren = $xmlDocument->getElementsByTagName($child->tagName)->item(0)->childNodes;
                                            foreach ($subChildren as $_sc) {
                                                if (in_array($_sc->tagName, $subProps)) {
                                                    /// find child
                                                    $subChildNodes = $xmlDocument->getElementsByTagName($_sc->tagName);  /// return an array

                                                    foreach ($subChildNodes as $_scn) {
                                                        if ($_scn->parentNode->nodeName === $pr_name) {
                                                            $tagName = $_scn->tagName;
                                                            if(!is_null($jsonDataBody->$js_key->$pr_name->$tagName->default)) {
                                                                if (is_null($jsonDataBody->$js_key->$pr_name->$tagName->sql) or ($jsonDataBody->$js_key->$pr_name->$tagName->sql === "")) {
                                                                    $_scn->nodeValue = $jsonDataBody->$js_key->$pr_name->$tagName->default;
                                                                } else {
                                                                    $this->buildSql($xmlDocument, $_scn, null, $jsonDataBody->$js_key->$pr_name->$tagName->sql . $fnum, true);
                                                                }
                                                            } else {
                                                                $this->buildSql($xmlDocument, $_scn, null, $jsonDataBody->$js_key->$pr_name->$tagName->sql . $fnum, true);
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                            $xmlDocument->getElementsByTagName($child->tagName)->item(0)->removeAttribute('duplicata');
                                        }
                                    }
                                }
                            }
                            else {
                                /// if repetitive, get sql query for each sub property

                                /// get pr
                                $pr_names = $jsonDataBody->$js_key->$pr_name;

                                foreach ($pr_names as $p_key => $p_val) {
                                    $p_sql = $p_val->sql . $fnum;

                                    // run SQL query
                                    $this->db->setQuery($p_sql);
                                    $result = $this->db->loadResult();

                                    // stock value into $sql_array
                                    $sql_array[$p_key] = explode('>>> SPLIT <<<', $result);
                                }


                                for ($i = 0; $i <= $repeat_times - 1; $i++) {
                                    //if ($i == $xmlDocument->getElementsByTagName('item')->item($i)->getAttribute('duplicata')) {
                                    /// get children
                                    $_childNodes = $xmlDocument->getElementsByTagName('item')->item($i)->childNodes;

                                    /// iterate child
                                    foreach ($_childNodes as $_child) {
                                        /// next step : mapping data for each child --- data in this case is a sequential array that its length is exactly [$repeat_times] ::: sql query must to be loadAssocList // loadObjectList
                                        if (in_array($_child->tagName, array_keys($sql_array))) {
                                            $_child->nodeValue = $sql_array[$_child->tagName][$i];
                                        }
                                    }

                                    // remove attribut 'duplicata' -- optional
                                    $xmlDocument->getElementsByTagName('item')->item($i)->removeAttribute('duplicata');
                                    //}
                                }
                            }
                        }
                        else {
                            $inSubData = in_array($pr_name, array_keys((array)$jsonDescriptionBody->subData));
                            $inRepeatData = in_array($pr_name, array_keys((array)$jsonDescriptionBody->repeat));

                            if ($inRepeatData) {
                                $repeat_times = $jsonDescriptionBody->repeat->$pr_name->occurrence;                           /// int

                                if (is_null($repeat_times) || $repeat_times === 1) {
                                    $subProps = array_values((array)$jsonDescriptionBody->repeat->$pr_name->elements);            /// always (0) or (1)

                                    /// check if $root_node has child node
                                    $hasChildren = $rootNode->hasChildNodes();

                                    if (count($subProps) === 0) {  // no repeat
                                        if ($hasChildren) {
                                            /// iterate children
                                            $rootChildren = $rootNode->childNodes;
                                            foreach ($rootChildren as $child) {
                                                if (in_array($child->tagName, $propertyName)) {
                                                    /// find child
                                                    $childNode = $xmlDocument->getElementsByTagName($child->tagName);  /// return an array

                                                    foreach ($childNode as $_ch) {
                                                        if ($_ch->parentNode->nodeName === $js_key) {
                                                            if ($pr_name == $child->tagName) {
                                                                if(!is_null($jsonDataBody->$js_key->$pr_name->default)) {
                                                                    if (is_null($jsonDataBody->$js_key->$pr_name->sql) or ($jsonDataBody->$js_key->$pr_name->sql === "")) {
                                                                        $_ch->nodeValue = $jsonDataBody->$js_key->$pr_name->default;
                                                                    } else {
                                                                        $this->buildSql($xmlDocument, null, $pr_name, $jsonDataBody->$js_key->$pr_name->sql . $fnum, false);
                                                                    }
                                                                } else {
                                                                    $this->buildSql($xmlDocument, null, $pr_name, $jsonDataBody->$js_key->$pr_name->sql . $fnum, false);
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    else {
                                        if ($hasChildren) {
                                            /// iterate children
                                            $rootChildren = $rootNode->childNodes;

                                            foreach ($rootChildren as $child) {
                                                /// each $child has childNodes
                                                $subChildren = $xmlDocument->getElementsByTagName($child->tagName)->item(0)->childNodes;
                                                foreach ($subChildren as $_sc) {
                                                    if (in_array($_sc->tagName, $subProps)) {
                                                        /// find child
                                                        $subChildNodes = $xmlDocument->getElementsByTagName($_sc->tagName);  /// return an array

                                                        foreach ($subChildNodes as $_scn) {
                                                            if ($_scn->parentNode->nodeName === $pr_name) {
                                                                $tagName = $_scn->tagName;
                                                                if(!is_null($jsonDataBody->$js_key->$pr_name->$tagName->default)) {
                                                                    if (is_null($jsonDataBody->$js_key->$pr_name->$tagName->sql) or ($jsonDataBody->$js_key->$pr_name->$tagName->sql === "")) {
                                                                        $_scn->nodeValue = $jsonDataBody->$js_key->$pr_name->$tagName->default;
                                                                    } else {
                                                                        $this->buildSql($xmlDocument, $_scn, null, $jsonDataBody->$js_key->$pr_name->$tagName->sql . $fnum, false);
                                                                    }
                                                                } else {
                                                                    $this->buildSql($xmlDocument, $_scn, null, $jsonDataBody->$js_key->$pr_name->$tagName->sql . $fnum, false);
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                                $xmlDocument->getElementsByTagName($child->tagName)->item(0)->removeAttribute('duplicata');
                                            }
                                        }
                                    }
                                }
                                else {
                                    $sql_array = [];

                                    $pr_names = $jsonDataBody->$js_key->$pr_name;

                                    foreach ($pr_names as $p_key => $p_val) {
                                        /// check if SQL query exists
                                        if(!is_null($p_val->sql) && ($p_val->sql) !== "") {
                                            $p_sql = $p_val->sql . $fnum;

                                            // run SQL query
                                            $this->db->setQuery($p_sql);

                                            /// if CONCAT is in $p_sql, use loadResult(). Otherwise, use loadColumn()
                                            if(strpos($p_sql, 'CONCAT') or strpos($p_sql, 'concat')) {
                                                $result = $this->db->loadResult();      /// may be many columns
                                                // stock value into $sql_array
                                                $sql_array[$p_key] = explode('>>> SPLIT <<<', $result);
                                            } else {
                                                $result = $this->db->loadColumn();      /// just one column, but many rows
                                                // stock value into $sql_array
//                                                $sql_array[$p_key] = implode('>>> SPLIT <<<', $result);
                                                $sql_array[$p_key] = $result;
                                            }
                                        }
                                        else {
                                            /// clone
                                            for ($i = 0; $i <= $repeat_times - 1; $i++) {
                                                $sql_array[$p_key][$i] = $p_val->default;
                                            }
                                        }
                                    }

                                    for ($i = 0; $i <= $repeat_times - 1; $i++) {
                                        //if ($i == $xmlDocument->getElementsByTagName('item')->item($i)->getAttribute('duplicata')) {
                                        /// get children
                                        $_childNodes = $xmlDocument->getElementsByTagName('item')->item($i)->childNodes;

                                        /// iterate child
                                        foreach ($_childNodes as $_child) {
                                            /// next step : mapping data for each child --- data in this case is a sequential array that its length is exactly [$repeat_times] ::: sql query must to be loadAssocList // loadObjectList
                                            if (in_array($_child->tagName, array_keys($sql_array))) {
                                                if(empty($sql_array[$_child->tagName])) {
                                                    continue;
                                                }

                                                else {
                                                    $_child->nodeValue = $sql_array[$_child->tagName][$i];
                                                }
                                            }
                                        }

                                        // remove attribut 'duplicata' -- optional
                                        $xmlDocument->getElementsByTagName('item')->item($i)->removeAttribute('duplicata');
                                        //}
                                    }
                                }
                            }
                            else {
                                if($inSubData) {
                                    $_subProps = $jsonDescriptionBody->subData->$pr_name;             /// array

                                    foreach ($_subProps as $_sp) {
                                        $childNode = $xmlDocument->getElementsByTagName($_sp);

                                        foreach ($childNode as $_cn) {

                                            if ($_cn->parentNode->nodeName === $pr_name) {
                                                $tagName = $_cn->tagName;
                                                if(!is_null($jsonDataBody->$js_key->$pr_name->$tagName->default)) {
                                                    if (is_null($jsonDataBody->$js_key->$pr_name->$tagName->sql) or ($jsonDataBody->$js_key->$pr_name->$tagName->sql === "")) {
                                                        $_cn->nodeValue = $jsonDataBody->$js_key->$pr_name->$tagName->default;
                                                    } else {
                                                        $this->buildSql($xmlDocument, $_cn, null, $jsonDataBody->$js_key->$pr_name->$tagName->sql . $fnum, true);
                                                    }
                                                } else {
                                                    $this->buildSql($xmlDocument, $_cn, null,  $jsonDataBody->$js_key->$pr_name->$_sp->sql . $fnum, false);
                                                }
                                            }
                                        }
                                    }
                                } else {
                                    $childNode = $xmlDocument->getElementsByTagName($pr_name);

                                    foreach($childNode as $_ch) {
                                        if ($_ch->parentNode->nodeName === $js_key) {
                                            if(!is_null($jsonDataBody->$js_key->$pr_name->default)) {
                                                if (is_null($jsonDataBody->$js_key->$pr_name->sql) or ($jsonDataBody->$js_key->$pr_name->sql === "")) {
                                                    $_ch->nodeValue = $jsonDataBody->$js_key->$pr_name->default;
                                                } else {
                                                    $this->buildSql($xmlDocument, $_ch, null, $jsonDataBody->$js_key->$pr_name->sql . $fnum, true);
                                                }
                                            } else {
                                                $this->buildSql($xmlDocument, $_ch, null, $jsonDataBody->$js_key->$pr_name->sql . $fnum, false);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
//        die;
        return $xmlDocument;
    }

    //// run SQL query and assign to node
    public function buildSql($xmlTree, $property=null, $domNode=null, $sql, $isRepeat=false) : void {
        $this->db->setQuery($sql);
        $result = $this->db->loadResult();

        if(!is_null($result)) {
            if ($isRepeat) {
                $result = reset(explode('>>> SPLIT <<<', $result));
            }

            if ($domNode !== null) {
                /// get node from $domNode
                $_node = $xmlTree->getElementsByTagName($domNode)->item(0);
                // bind value to node
                $_node->nodeValue = $result;
            }
        } else {
            $property->nodeValue = '';
        }

        if($property !== null) { $property->nodeValue = $result; }
    }

    public function pruneXML($xml) {
        $doc = new DOMDocument;
        $doc->preserveWhiteSpace = false;

        $doc->loadXML($xml);

        $xpath = new DOMXPath($doc);

        /* prune data body */
        foreach( $xpath->query('//*[not(node())]') as $node ) {
            if($node->tagName == 'soapenv:Header' or $node->tagName == 'soapenv:Body') { continue; }
            else { $node->parentNode->removeChild($node); }
        }

        /* prune schema body */
        foreach($doc->getElementsByTagName('*') as $elt) {
            if(!$elt->hasChildNodes()) { $elt->parentNode->removeChild($elt); }
        }

        $doc->formatOutput = true;
        return $doc;
    }

}