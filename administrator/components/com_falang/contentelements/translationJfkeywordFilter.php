<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

class translationJfkeywordFilter extends translationFilter
{
	public function __construct ($contentElement){
		$this->filterNullValue="";
		$this->filterType="jfkeyword";
		$params = $contentElement->getFilter("jfkeyword");		
		list($this->filterField,$this->label) = explode("|",$params);
		parent::__construct($contentElement);
	}


    public function createFilter(){
		if (!$this->filterField) return "";
		$filter="";
		if ($this->filter_value!=""){
			$db = JFactory::getDBO();
			$filter =  "LOWER(c.".$this->filterField." ) LIKE '%".$db->escape( $this->filter_value, true )."%'";
		}
		return $filter;
	}

	/**
 * Creates Keyword filter
 *
 * @param unknown_type $filtertype
 * @param unknown_type $contentElement
 * @return unknown
 */
	public function createFilterHTML(){

		if (!$this->filterField) return "";
		$Keywordlist=array();
		$Keywordlist["title"]= JText::_($this->label);

        if (FALANG_J30) {
            $Keywordlist['position'] = 'top';
            $Keywordlist['html'] = '<label class="element-invisible" for="jfkeyword_filter_value">'.$this->label.'</label>';
            $Keywordlist['html'] .= '<input type="text" name="jfkeyword_filter_value" title='.$this->label.' placeholder='.$this->label.' value="'.$this->filter_value.'" onChange="document.adminForm.submit();" />';
        } else {
            $Keywordlist["html"] = 	'<input type="text" name="jfkeyword_filter_value" value="'.$this->filter_value.'" class="text_area" onChange="document.adminForm.submit();" />';
        }

		return $Keywordlist;

	}
	

}