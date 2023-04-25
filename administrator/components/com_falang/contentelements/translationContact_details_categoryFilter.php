<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

class translationContact_details_categoryFilter extends translationFilter
{
	public function __construct ($contentElement){
		$this->filterNullValue="-1";
		$this->filterType="contact_details_category";
        $this->filterField =  $contentElement->getFilter("contact_details_category");
		//$params = $contentElement->getFilter("contact_details_category");
        //list($this->filterField,$this->label) = explode("|",$params);
		parent::__construct($contentElement);
	}

	public function _createFilter(){
		if (!$this->filterField) return "";
		$filter="";

        //since joomla 3.0 filter_value can be '' too not only filterNullValue
        if (isset($this->filter_value) && strlen($this->filter_value) > 0  && $this->filter_value!=$this->filterNullValue){
			$db = JFactory::getDBO();
			$filter =  " c.".$this->filterField."=".$db->escape( $this->filter_value, true );
		}
		return $filter;
	}

    function _createfilterHTML(){
        if (!$this->filterField) return "";

        $allCategoryOptions = array();
        $extension = 'com_contact';
        $options = JHtml::_('category.options', $extension);

        if (!FALANG_J30) {
            $allCategoryOptions[-1] = JHTML::_('select.option', '-1',JText::_('COM_FALANG_ALL_CATEGORIES') );
        }
        $options = array_merge($allCategoryOptions, $options);

        $categoryList=array();

        if (FALANG_J30) {
            $categoryList["title"]= JText::_('COM_FALANG_SELECT_CATEGORY');
            $categoryList["position"] = 'sidebar';
            $categoryList["name"]= 'contact_details_categoryy_filter_value';
            $categoryList["type"]= 'contact_details_category';
            $categoryList["options"] = $options;
            $categoryList["html"] = JHTML::_('select.genericlist', $options, 'contact_details_category_filter_value', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $this->filter_value );
        } else {
            $categoryList["title"]= JText::_('COM_FALANG_CATEGORY_FILTER');
            $categoryList["html"] = JHTML::_('select.genericlist', $options, 'contact_details_category_filter_value', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $this->filter_value );
        }

        return $categoryList;

    }


}