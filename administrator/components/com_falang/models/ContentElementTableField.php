<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

class ContentElementTablefield {
	var $Type='';
	var $Name='';
	var $Lable='';
	var $Translate=false;
	var $Option='';
	var $Length=30;
	var $MaxLength=80;
	var $Rows=15;
	var $Columns=30;
	var $posthandler="";
	var $prehandler="";
	var $prehandleroriginal="";
	var $prehandlertranslation="";
	
	// Can be boolean or array, if boolean defines if the buttons are displayed, if array defines a list of buttons not to show.
	var $ebuttons=true;

	// boolean to determine where to show this field if original is not blank e.g. content in modules
	var $ignoreifblank=0;
	
	/** originalValue value of the corresponding content table */
	var $originalValue;

	/** translationContent reference to the actual translation db object */
	var $translationContent;

	/** changed Flag that says if a field is changed or not */
	var $changed=false;

	/** this Flag explains if the original is empty or not */
	var $originalEmpty=false;

	/** Standard constructur
	*/
	public function __construct($tablefieldElement){
		$this->Type = trim( $tablefieldElement->getAttribute( 'type' ) );
		$this->Name = trim( $tablefieldElement->getAttribute( 'name' ) );
		$this->Lable = trim( $tablefieldElement->textContent );
		$this->Translate = trim( $tablefieldElement->getAttribute( 'translate' ) );
		$this->Option = trim( $tablefieldElement->getAttribute( 'option' ) );
		$this->Length = intval( $tablefieldElement->getAttribute( 'length' ) );
		$this->MaxLength = intval( $tablefieldElement->getAttribute( 'maxlength' ) );
		$this->Rows = intval( $tablefieldElement->getAttribute( 'rows' ) );
		$this->Columns = intval( $tablefieldElement->getAttribute( 'columns' ) );
		$this->posthandler = trim( $tablefieldElement->getAttribute( 'posthandler' ) );
		$this->prehandler = trim( $tablefieldElement->getAttribute( 'prehandler' ) );
		$this->prehandlertranslation = trim( $tablefieldElement->getAttribute( 'prehandlertranslation' ) );
		$this->prehandleroriginal = trim( $tablefieldElement->getAttribute( 'prehandleroriginal' ) );
		$this->ignoreifblank = intval( $tablefieldElement->getAttribute( 'ignoreifblank' ) );
		
		$this->ebuttons = trim( $tablefieldElement->getAttribute( 'ebuttons' ) );
		if (strpos($this->ebuttons,",")>0){
			$this->ebuttons = explode(",",$this->ebuttons);
		}
		else if ($this->ebuttons=="1"  || strtolower($this->ebuttons)=="true"){
			$this->ebuttons = true;
		}
		else if (strlen($this->ebuttons)==0) {
			$this->ebuttons = array("readmore");
		}
		else if ($this->ebuttons=="0"  || strtolower($this->ebuttons)=="false"){
			$this->ebuttons = false;
		}
		else if (strlen($this->ebuttons)>0){
			$this->ebuttons = array($this->ebuttons);
		}
	}
	
	function preHandle($element){
		if ($this->prehandler!="" && method_exists($this,$this->prehandler)){
			$prehandler=$this->prehandler;
			$this->$prehandler($element);
		}
	}
	function checkUrlType($element){
		if ($element->IndexedFields["type"]->originalValue=="url") $this->Type="text";
	}

	public function fetchUrlRequest(&$element)
	{
		// pre-populate special 'request' entry.
		if (isset($element->IndexedFields) && isset($element->IndexedFields["link"]) && isset($this->translationContent)) {
			$field = $element->IndexedFields["link"];
			$args = array();
			if ($field->Name=="link" && isset($field->translationContent)){
				$value =$field->translationContent->value;
				parse_str(parse_url($value, PHP_URL_QUERY), $args);
			}
			$translation = json_decode($this->translationContent->value);
			if(count($args)>0){
				//s:sbou v2.0
				if (!isset($translation)){$translation = new stdClass();}
				//e:sbou
				$translation->jfrequest=$args;
				$this->translationContent->value  = json_encode($translation);
			}
			else {
                //s:sbou v2.0
                if (!isset($translation)){$translation = new stdClass();}
                //e:sbou
				$translation->jfrequest =array();
				$this->translationContent->value  = json_encode($translation);
			}
		}
	}

	//new 2.8.2 preHandler use to copy images&urls params to empty translation.
	// 2.8.3 copy only is param's set in backend to display image&url links
	public function preHandlerArticleImagesAndUrls($element){
		$falangManager =  FalangManager::getInstance();
		$contentParms = JComponentHelper::getParams('com_content');
		if ($falangManager->getCfg('copy_images_and_urls',0) && $contentParms->get('show_urls_images_backend',0) ){
			if (!is_null($element))
			{
				$attibs = $element->IndexedFields["attribs"];
				$images = $element->IndexedFields["images"];
				$urls   = $element->IndexedFields["urls"];

				$registry = new JRegistry;
				$registry->loadString($attibs->originalValue);
				if (!empty($images))
				{
					$registry->loadString($images->originalValue);
				}
				if (!empty($urls))
				{
					$registry->loadString($urls->originalValue);
				}
				$this->originalValue = $registry->toString();
			}
	}
	}

}

