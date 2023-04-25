<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

function getTranslationFilters($catid, $contentElement)
{
        if (!$contentElement) return array();
	$filterNames=$contentElement->getAllFilters();

    //reset keyword filter is add with keyword search since joomla 3.0
    if (FALANG_J30) {} else {
        if (count($filterNames)>0) {
            $filterNames["reset"]="reset";
        }
    }

	$filters=array();
	foreach ($filterNames as $key=>$value){
		$filterType = "translation".ucfirst(strtolower($key))."Filter" ;
		$classFile = JPATH_SITE."/administrator/components/com_falang/contentelements/$filterType.php" ;
		if (!class_exists($filterType)){
			if (file_exists($classFile )) include_once($classFile);
			if (!class_exists($filterType)) {
				continue;
			}
		}
		$filters[strtolower($key)] =  new $filterType($contentElement);
	}
	return $filters;
}


class translationFilter
{
	var $filterNullValue;
	var $filterType;
	var $filter_value;
	var $filterField = false;
	var $tableName = "";
	var $filterHTML="";

	// Should we use session data to remember previous selections?
	var $rememberValues = true;

	public function __construct($contentElement=null){
		$jinput = JFactory::getApplication()->input;
		if (intval($jinput->get('filter_reset',0,'INT'))){
			$this->filter_value =  $this->filterNullValue;
		}
		else if ($this->rememberValues){
			// TODO consider making the filter variable name content type specific
            $app	= JFactory::getApplication();
			$this->filter_value = $app->getUserStateFromRequest($this->filterType.'_filter_value', $this->filterType.'_filter_value', $this->filterNullValue);
		}
		else {
			$this->filter_value =  $jinput->get( $this->filterType.'_filter_value', $this->filterNullValue );
		}
		//echo $this->filterType.'_filter_value = '.$this->filter_value."<br/>";
		$this->tableName = isset($contentElement)?$contentElement->getTableName():"";
	}

	function _createFilter(){
		if (!$this->filterField ) return "";
		$filter="";

        //since joomla 3.0 filter_value can be '' too not only filterNullValue
        if (isset($this->filter_value) && strlen($this->filter_value) > 0  && $this->filter_value!=$this->filterNullValue){
                       if (is_int($this->filter_value)) {
        			$filter = "c.".$this->filterField."=$this->filter_value";
                       } else {
        			$filter = "c.".$this->filterField."='".$this->filter_value."'";
                       }
                }
		return $filter;
	}

	function _createfilterHTML(){ return "";}
}

class translationResetFilter extends translationFilter
{
	public function __construct($contentElement){
		$this->filterNullValue=-1;
		$this->filterType="reset";
		$this->filterField = "";
		parent::__construct($contentElement);
	}

	function _createFilter(){
		return "";
	}

	
	/**
 * Creates javascript session memory reset action
 *
 */
	function _createfilterHTML(){
		$reset["title"]= JText::_('COM_FALANG_FILTER_RESET');
        if (FALANG_J30) {
            $reset['position'] = 'sidebar';
            $reset["html"] = '<input type=\'hidden\' name=\'filter_reset\' id=\'filter_reset\' value=\'0\' /><button class="btn hasTooltip" onclick="document.getElementById(\'filter_reset\').value=1;document.adminForm.submit()" type="button" data-original-title="'.JText::_("COM_FALANG_FILTER_RESET").'"> <i class="icon-remove"></i></button>';
        } else {
            $reset["html"] = "<input type='hidden' name='filter_reset' id='filter_reset' value='0' /><input type='button' value='".JText::_("COM_FALANG_FILTER_RESET")."' onclick='document.getElementById(\"filter_reset\").value=1;document.adminForm.submit()' />";
        }
		return $reset;

	}

}

class translationFrontpageFilter extends translationFilter
{
	public function __construct($contentElement){
		$this->filterNullValue=-1;
		$this->filterType="frontpage";
		$this->filterField = $contentElement->getFilter("frontpage");
		parent::__construct($contentElement);
	}

	function _createFilter(){
		if (!$this->filterField) return "";
		$filter="";

        //since joomla 3.0 filter_value can be '' too not only filterNullValue
		if (isset($this->filter_value) && strlen($this->filter_value) > 0 && $this->filter_value!=$this->filterNullValue){
			$db = JFactory::getDBO();
			$sql = "SELECT content_id FROM #__content_frontpage order by ordering";
			$db->setQuery($sql);
			$ids = $db->loadColumn();

			if (is_null($ids)){
				$ids = array();
			}
			$ids[] = -1;
			$idstring = implode(",",$ids);
			$not = "";
			if ($this->filter_value==0){
				$not = " NOT ";
			}
			$filter =   " c.".$this->filterField.$not." IN (".$idstring.") ";
		}
		return $filter;
	}

	
	/**
 * Creates frontpage filter
 *
 * @param unknown_type $filtertype
 * @param unknown_type $contentElement
 * @return unknown
 */
	function _createfilterHTML(){
		$db = JFactory::getDBO();

		if (!$this->filterField) return "";

		$FrontpageOptions=array();

        if (!FALANG_J30) {
		    $FrontpageOptions[] = JHTML::_('select.option', -1, JText::_('COM_FALANG_FILTER_ANY'));
        }
		$FrontpageOptions[] = JHTML::_('select.option', 1, JText::_('JYES'));
		$FrontpageOptions[] = JHTML::_('select.option', 0, JText::_('JNO'));
		$frontpageList=array();

        if (FALANG_J30) {
            $frontpageList["title"]= JText::_('COM_FALANG_SELECT_FRONTPAGE');
            $frontpageList["position"] = 'sidebar';
            $frontpageList["name"]= 'frontpage_filter_value';
            $frontpageList["type"]= 'frontpage';
            $frontpageList["options"] = $FrontpageOptions;
            $frontpageList["html"] = JHTML::_('select.genericlist', $FrontpageOptions, 'frontpage_filter_value', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $this->filter_value );
        } else {
            $frontpageList["title"]= JText::_('COM_FALANG_FILTER_FRONTPAGE');
            $frontpageList["html"] = JHTML::_('select.genericlist', $FrontpageOptions, 'frontpage_filter_value', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $this->filter_value );
        }


		return $frontpageList;

	}

}

class translationArchiveFilter extends translationFilter
{
	public function __construct($contentElement){
		$this->filterNullValue=-1;
		$this->filterType="archive";
		$this->filterField = $contentElement->getFilter("archive");
		parent::__construct($contentElement);
	}

	function _createFilter(){
		if (!$this->filterField) return "";
		$filter="";
        //since joomla 3.0 filter_value can be '' too not only filterNullValue
        if (isset($this->filter_value) && strlen($this->filter_value) > 0 && $this->filter_value!=$this->filterNullValue){
			if ($this->filter_value==0){
				$filter =   " c.".$this->filterField." >=0 ";
			}
			else {
				$filter =   " c.".$this->filterField." =-1 ";
			}
		}
		return $filter;
	}

	
	/**
 * Creates archive filter
 *
 * @param unknown_type $filtertype
 * @param unknown_type $contentElement
 * @return unknown
 */
	function _createfilterHTML(){
		$db = JFactory::getDBO();

		if (!$this->filterField) return "";

		$FrontpageOptions=array();

        if (!FALANG_J30) {
		    $FrontpageOptions[] = JHTML::_('select.option', -1, JText::_('COM_FALANG_FILTER_ANY'));
        }
		$FrontpageOptions[] = JHTML::_('select.option', 1, JText::_('JYES'));
		$FrontpageOptions[] = JHTML::_('select.option', 0, JText::_('JNO'));
		$frontpageList=array();

        if (FALANG_J30) {
            $frontpageList["title"]= JText::_('COM_FALANG_SELECT_ARCHIVE');
            $frontpageList["position"] = 'sidebar';
            $frontpageList["name"]= 'archive_filter_value';
            $frontpageList["type"]= 'archive';
            $frontpageList["options"] = $FrontpageOptions;
            $frontpageList["html"] = JHTML::_('select.genericlist', $FrontpageOptions, 'archive_filter_value', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $this->filter_value );

        } else {
            $frontpageList["title"]= JText::_('COM_FALANG_FILTER_ARCHIVE');
            $frontpageList["html"] = JHTML::_('select.genericlist', $FrontpageOptions, 'archive_filter_value', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $this->filter_value );
        }

		return $frontpageList;

	}

}

class translationCategoryFilter extends translationFilter
{
	var $section_filter_value;

	public function __construct($contentElement){
		$this->filterNullValue=-1;
		$this->filterType="category";
		$this->filterField = $contentElement->getFilter("category");
		parent::__construct($contentElement);

	}


    function _createFilter(){
        if (!$this->filterField) return "";
        $filter="";

        //since joomla 3.0 filter_value can be '' too not only filterNullValue
        if (isset($this->filter_value) && strlen($this->filter_value) > 0  && $this->filter_value!=$this->filterNullValue){
                $filter =   " c.".$this->filterField." = ".$this->filter_value;
        }
        return $filter;
    }

	function _createfilterHTML(){
		$db = JFactory::getDBO();

		if (!$this->filterField) return "";

        $allCategoryOptions = array();
        $extension = 'com_'.$this->tableName;

        $options = JHtml::_('category.options', $extension);

        if (!FALANG_J30) {
            $allCategoryOptions[-1] = JHTML::_('select.option', '-1',JText::_('COM_FALANG_ALL_CATEGORIES') );
        }

        $options = array_merge($allCategoryOptions, $options);

        $categoryList=array();

        if (FALANG_J30) {
            $categoryList["title"]= JText::_('COM_FALANG_SELECT_CATEGORY');
            $categoryList["position"] = 'sidebar';
            $categoryList["name"]= 'category_filter_value';
            $categoryList["type"]= 'category';
            $categoryList["options"] = $options;
            $categoryList["html"] = JHTML::_('select.genericlist', $options, 'category_filter_value', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $this->filter_value );
        } else {
            $categoryList["title"]= JText::_('COM_FALANG_CATEGORY_FILTER');
            $categoryList["html"] = JHTML::_('select.genericlist', $options, 'category_filter_value', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $this->filter_value );
        }

        return $categoryList;

	}

}

class translationAuthorFilter extends translationFilter
{
	public function __construct($contentElement){
		$this->filterNullValue=-1;
		$this->filterType="author";
		$this->filterField = $contentElement->getFilter("author");
		parent::__construct($contentElement);
	}


	function _createfilterHTML(){
		$db = JFactory::getDBO();

		if (!$this->filterField) return "";
		$AuthorOptions=array();

        if (!FALANG_J30) {
    		$AuthorOptions[] = JHTML::_('select.option', '-1',JText::_('COM_FALANG_ALL_AUTHORS') );
        }

		//	$sql = "SELECT c.id, c.title FROM #__categories as c ORDER BY c.title";
		$sql = "SELECT DISTINCT auth.id, auth.username FROM #__users as auth, #__".$this->tableName." as c
			WHERE c.".$this->filterField."=auth.id ORDER BY auth.username";
		$db->setQuery($sql);
		$cats = $db->loadObjectList();
		$catcount=0;
		foreach($cats as $cat){
			$AuthorOptions[] = JHTML::_('select.option', $cat->id,$cat->username);
			$catcount++;
		}
		$Authorlist=array();

        if (FALANG_J30) {
            $Authorlist["title"]=JText::_('COM_FALANG_SELECT_AUTHOR');
            $Authorlist["position"] = 'sidebar';
            $Authorlist["name"]= 'author_filter_value';
            $Authorlist["type"]= 'author';
            $Authorlist["options"] = $AuthorOptions;
            $Authorlist["html"] = JHTML::_('select.genericlist', $AuthorOptions, 'author_filter_value', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $this->filter_value );

        } else {
            $Authorlist["title"]=JText::_('COM_FALANG_AUTHOR_FILTER');
            $Authorlist["html"] = JHTML::_('select.genericlist', $AuthorOptions, 'author_filter_value', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $this->filter_value );
        }
		return $Authorlist;

	}

}


class translationExtensionFilter extends translationFilter
{

	public function __construct($contentElement){
        $this->filterNullValue='';
        $this->filterType="extension";
        $this->filterField = $contentElement->getFilter("extension");
        parent::__construct($contentElement);
    }


    function _createfilterHTML(){
        $db = JFactory::getDBO();

        if (!$this->filterField) return "";
        $ExtensionOptions=array();

        //not necessary in joomla 3.0
        if (!FALANG_J30) {
            $ExtensionOptions[] = JHTML::_('select.option', '',JText::_('COM_FALANG_ALL_EXTENSION') );
        }

        $query = $db->getQuery(true);
        $query
            ->select('DISTINCT c.extension')
            ->from('#__'.$this->tableName.' as c')
            ->where('c.'.$this->filterField.' != '.$db->q('system'))
            ->order('c.extension');

        $db->setQuery($query);
        $extensions = $db->loadObjectList();
        $extcount=0;
        foreach($extensions as $extension){
            $ExtensionOptions[] = JHTML::_('select.option', $extension->extension,$extension->extension);
            $extcount++;
        }
        $Extensionlist=array();

        if (FALANG_J30) {
            $Extensionlist["title"]=JText::_('COM_FALANG_SELECT_EXTENSION');
            $Extensionlist["position"] = 'sidebar';
            $Extensionlist["name"]= 'extension_filter_value';
            $Extensionlist["type"]= 'extension';
            $Extensionlist["options"] = $ExtensionOptions;
            $Extensionlist["html"] = JHTML::_('select.genericlist', $ExtensionOptions, 'extension_filter_value', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $this->filter_value );

        } else {
            $Extensionlist["title"]=JText::_('COM_FALANG_EXTENSION_FILTER');
            $Extensionlist["html"] = JHTML::_('select.genericlist', $ExtensionOptions, 'extension_filter_value', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $this->filter_value );
        }
        return $Extensionlist;

    }

}


class translationKeywordFilter extends translationFilter
{
	public function __construct($contentElement){
		$this->filterNullValue="";
		$this->filterType="keyword";
		$this->filterField = $contentElement->getFilter("keyword");
		parent::__construct($contentElement);
	}


	function _createFilter(){
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
	function _createfilterHTML(){
		if (!$this->filterField) return "";
		$Keywordlist=array();
		$Keywordlist["title"]= JText::_('COM_FALANG_KEYWORD_FILTER');

        $Keywordlist["position"] = 'top';
		$Keywordlist['html'] = '<div class="btn-group mr-2">';
        $Keywordlist['html'] .= '<div class="input-group">';
        $Keywordlist['html'] .= '<label class="sr-only" for="keyword_filter_value">'.$Keywordlist["title"].'</label>';
        $Keywordlist['html'] .= '<input type="text" name="keyword_filter_value" id="keyword_filter_value" title="'.$Keywordlist["title"].'" class="form-control" placeholder="'.$Keywordlist["title"].'" value="'.$this->filter_value.'" onChange="document.adminForm.submit();" />';
        $Keywordlist['html'] .= '</div>';
		$Keywordlist['html'] .= '<span class="input-group-append">';
		$Keywordlist['html'] .= '<button class="btn btn-primary hasTooltip" type="submit" data-original-title="'.JText::_('SEARCH').'"><span class="fa fa-search" aria-hidden="true"></span></button>';
		$Keywordlist['html'] .= '</span>';
		$Keywordlist['html'] .= '</div>';
        $Keywordlist['html'] .= '<button type="button" class="btn btn-primary hasTooltip js-stools-btn-clear mr-2" onclick="document.id(\'keyword_filter_value\').value=\'\';this.form.submit();" title="'.Text::_('JSEARCH_FILTER_CLEAR').'">'.Text::_('JSEARCH_FILTER_CLEAR').'</button>';
		$Keywordlist['html'] .= '';

		return $Keywordlist;
	}

}

class translationModuleFilter  extends translationFilter
{
	public function __construct($contentElement){
		$this->filterNullValue=-1;
		$this->filterType="module";
		$this->filterField = $contentElement->getFilter("module");
		parent::__construct($contentElement);
	}

	function _createFilter(){
		$filter = "c.".$this->filterField."<99";
		return $filter;
	}

	function _createfilterHTML(){
		return "";
	}
}

//new 2.8.2 filter by module type
class translationModuletypeFilter  extends translationFilter
{
	public function __construct($contentElement){
		$this->filterNullValue="-+-+";
		$this->filterType="moduletype";
		$this->filterField = $contentElement->getFilter("moduletype");
		parent::__construct($contentElement);
	}

	function _createFilter(){
		if (!$this->filterField ) return "";
		$filter="";

		//since joomla 3.0 filter_value can be '' too not only filterNullValue
		if (isset($this->filter_value) && strlen($this->filter_value) > 0  && $this->filter_value!=$this->filterNullValue){
			$filter = "c.".$this->filterField."='".$this->filter_value."'";
		}
		return $filter;
	}

	function _createfilterHTML(){
		$db = JFactory::getDBO();
		$lang = JFactory::getLanguage();

		if (!$this->filterField) return "";
		$MmoduletypeOptions=array();

		$sql = "SELECT DISTINCT module FROM #__modules WHERE client_id = 0 ORDER BY module ASC";
		$db->setQuery($sql);
		$cats = $db->loadObjectList();
		$catcount=0;
		foreach($cats as $cat){
			//get translate name system by administrator/components/com_modules/models/modules.php translate method
			$extension = $cat->module;
			$clientPath = JPATH_SITE;
			$source = $clientPath . "/modules/$extension";
			$lang->load("$extension.sys", $clientPath, null, false, true)
			|| $lang->load("$extension.sys", $source, null, false, true);
			$name = JText::_($cat->module);
			$MmoduletypeOptions[] = JHTML::_('select.option', $cat->module, $name);
			$catcount++;
		}
		$Menutypelist=array();

			$Menutypelist["title"]= JText::_('COM_FALANG_SELECT_MODULE');
			$Menutypelist["position"] = 'sidebar';
			$Menutypelist["name"]= 'moduletype_filter_value';
			$Menutypelist["type"]= 'moduletype';
			$Menutypelist["options"] = $MmoduletypeOptions;
			$Menutypelist["html"] = JHTML::_('select.genericlist', $MmoduletypeOptions, 'moduletype_filter_value', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $this->filter_value );

		return $Menutypelist;

	}
}

class translationMenutypeFilter  extends translationFilter
{
	public function __construct($contentElement){
		$this->filterNullValue="-+-+";
		$this->filterType="menutype";
		$this->filterField = $contentElement->getFilter("menutype");
		parent::__construct($contentElement);
	}

	function _createFilter(){
		if (!$this->filterField ) return "";
		$filter="";

        //since joomla 3.0 filter_value can be '' too not only filterNullValue
        if (isset($this->filter_value) && strlen($this->filter_value) > 0  && $this->filter_value!=$this->filterNullValue){
			$filter = "c.".$this->filterField."='".$this->filter_value."'";
		}
		return $filter;
	}

	function _createfilterHTML(){
		$db = JFactory::getDBO();

		if (!$this->filterField) return "";
		$MenutypeOptions=array();


        if (!FALANG_J30) {
    		$MenutypeOptions[] = JHTML::_('select.option', $this->filterNullValue, JText::_('COM_FALANG_ALL_MENUS') );
        }

        //dont't add root menu to the list != 1
		$sql = "SELECT DISTINCT mt.menutype FROM #__menu as mt WHERE id != 1 ORDER BY menutype ASC";
		$db->setQuery($sql);
		$cats = $db->loadObjectList();
		$catcount=0;
		foreach($cats as $cat){
			$MenutypeOptions[] = JHTML::_('select.option', $cat->menutype,$cat->menutype);
			$catcount++;
		}
		$Menutypelist=array();

        if (FALANG_J30) {
            $Menutypelist["title"]= JText::_('COM_FALANG_SELECT_MENU');
            $Menutypelist["position"] = 'sidebar';
            $Menutypelist["name"]= 'menutype_filter_value';
            $Menutypelist["type"]= 'menutype';
            $Menutypelist["options"] = $MenutypeOptions;
            $Menutypelist["html"] = JHTML::_('select.genericlist', $MenutypeOptions, 'menutype_filter_value', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $this->filter_value );
        } else {
            $Menutypelist["title"]= JText::_('COM_FALANG_MENU_FILTER');
            $Menutypelist["html"] = JHTML::_('select.genericlist', $MenutypeOptions, 'menutype_filter_value', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $this->filter_value );
        }

        return $Menutypelist;

	}
}

/**
 * filters translations based on creation/modification date of original
 *
 */
	class translationChangedFilter extends translationFilter
{
	public function __construct($contentElement){
		$this->filterNullValue=-1;
		$this->filterType="lastchanged";
		$this->filterField = $contentElement->getFilter("changed");
		list($this->_createdField,$this->_modifiedField) = explode("|",$this->filterField);
		parent::__construct($contentElement);
	}

	function _createFilter(){
		if (!$this->filterField) return "";
		$filter="";

        //since joomla 3.0 filter_value can be '' too not only filterNullValue
		if (isset($this->filter_value) && strlen($this->filter_value) > 0 && $this->filter_value!=$this->filterNullValue && $this->filter_value==1){
			// translations must be created after creation date so no need to check this!
			$filter = "( c.$this->_modifiedField>0 AND jfc.modified < c.$this->_modifiedField)" ;
		}
		else if (isset($this->filter_value) && strlen($this->filter_value) > 0 && $this->filter_value!=$this->filterNullValue){
			$filter = "( ";
			$filter .= "( c.$this->_modifiedField>0 AND jfc.modified >= c.$this->_modifiedField)" ;
			$filter .= " OR ( c.$this->_modifiedField=0 AND jfc.modified >= c.$this->_createdField)" ;
			$filter .= " )";
		}

		return $filter;
	}


	function _createfilterHTML(){
		$db = JFactory::getDBO();

		if (!$this->filterField) return "";
		$ChangedOptions=array();

        if (!FALANG_J30) {
		    $ChangedOptions[] = JHTML::_('select.option', -1,JText::_('COM_FALANG_FILTER_BOTH'));
        }

		$ChangedOptions[] = JHTML::_('select.option', 1, JText::_('COM_FALANG_FILTER_ORIGINAL_NEWER'));
		$ChangedOptions[] = JHTML::_('select.option', 0, JText::_('COM_FALANG_FILTER_TRANSLATION_NEWER'));

		$ChangedList=array();
        if (FALANG_J30) {
            $ChangedList["title"]= JText::_('COM_FALANG_SELECT_TRANSLATION_AGE');
            $ChangedList["position"] = 'sidebar';
            $ChangedList["name"]= 'lastchanged_filter_value';
            $ChangedList["type"]= 'lastchanged';
            $ChangedList["options"] = $ChangedOptions;
            $ChangedList["html"] = JHTML::_('select.genericlist', $ChangedOptions, $this->filterType.'_filter_value', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $this->filter_value );

        } else {
            $ChangedList["title"]= JText::_('COM_FALANG_FILTER_TRANSLATION_AGE');
            $ChangedList["html"] = JHTML::_('select.genericlist', $ChangedOptions, $this->filterType.'_filter_value', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $this->filter_value );
        }

		return $ChangedList;
	}
}

/**
 * Look for unpublished translations - i.e. no translation or translation is unpublished
 * Really only makes sense with a specific language selected
 *
 */

class translationTrashFilter extends translationFilter
{
	public function __construct($contentElement){
		$this->filterNullValue=-1;
		$this->filterType="trash";
		$this->filterField = $contentElement->getFilter("trash");
		parent::__construct($contentElement);
	}

	function _createFilter(){
		// -1 = archive, -2 = trash
		$filter = "c.".$this->filterField.">=-1";
		return $filter;
	}

	function _createfilterHTML(){
		return "";
	}

}

/**
 * Look for unpublished translations - i.e. no translation or translation is unpublished
 * Really only makes sense with a specific language selected
 *
 */

class translationPublishedFilter extends translationFilter
{
	public function __construct($contentElement){
		$this->filterNullValue='';
		$this->filterType="published";
		$this->filterField = $contentElement->getFilter("published");
		parent::__construct($contentElement);
	}

	function _createFilter(){
		if (!$this->filterField) return "";
		$filter="";
		if ($this->filter_value!=$this->filterNullValue){
			if ($this->filter_value==1){
				$filter = "jfc.".$this->filterField."=$this->filter_value";
			}
			else if ($this->filter_value==0){
				$filter = " ( jfc.".$this->filterField."=$this->filter_value AND jfc.reference_field IS NOT NULL ) ";
			}
			else if ($this->filter_value==2){
				$filter = " jfc.reference_field IS NULL  ";
			}
			else if ($this->filter_value==3){
				$filter = " jfc.reference_field IS NOT NULL ";
			}
		}

		return $filter;
	}

	function _createfilterHTML(){
		$db = JFactory::getDBO();

		if (!$this->filterField) return "";

		$PublishedOptions=array();
        if (!FALANG_J30) {
    		$PublishedOptions[] = JHTML::_('select.option', -1, JText::_('COM_FALANG_FILTER_ANY'));
        }
		$PublishedOptions[] = JHTML::_('select.option', 3, JText::_('COM_FALANG_FILTER_AVAILABLE'));
		$PublishedOptions[] = JHTML::_('select.option', 1, JText::_('COM_FALANG_TITLE_PUBLISHED'));
		$PublishedOptions[] = JHTML::_('select.option', 0, JText::_('COM_FALANG_TITLE_UNPUBLISHED'));
		$PublishedOptions[] = JHTML::_('select.option', 2, JText::_('COM_FALANG_FILTER_MISSING'));

		$publishedList=array();


        if (FALANG_J30) {
            $publishedList["title"]= JText::_('COM_FALANG_SELECT_TRANSLATION_AVAILABILITY');
            $publishedList["position"] = 'sidebar';
            $publishedList["name"]= 'published_filter_value';
            $publishedList["type"]= 'published';
            $publishedList["options"] = $PublishedOptions;
            $publishedList["html"] = JHTML::_('select.genericlist', $PublishedOptions, 'published_filter_value', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $this->filter_value );

        } else {
            $publishedList["title"]= JText::_('COM_FALANG_FILTER_TRANSLATION_AVAILABILITY');
            $publishedList["html"] = JHTML::_('select.genericlist', $PublishedOptions, 'published_filter_value', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $this->filter_value );
        }

        return $publishedList;

	}

}

class TranslateParams
{
	var $origparams;
	var $defaultparams;
	var $transparams;
	var $fields;
	var $fieldname;

	public function __construct($original, $translation, $fieldname, $fields=null){

		$this->origparams =  $original;
		$this->transparams = $translation;
		$this->fieldname = $fieldname;
		$this->fields = $fields;
	}

	public function showOriginal()
	{
		echo $this->origparams;

	}

	public function showDefault()
	{
		echo "";

	}

	function editTranslation(){
		$returnval = array( "editor_".$this->fieldname, "refField_".$this->fieldname );
		JFactory::getEditor()->display("editor_" . $this->fieldname, $this->transparams, "refField_" . $this->fieldname, '100%;', '300', '70', '15');
		echo $this->transparams;
		return $returnval;
	}
}

class TranslateParams_xml extends TranslateParams
{

	function showOriginal()
	{
		$output = "";
		$fieldname = 'orig_' . $this->fieldname;
		$output .= $this->origparams->render($fieldname);
		$output .= <<<SCRIPT
		<script language='javascript'>
		function copyParams(srctype, srcfield){
			var orig = document.getElementsByTagName('select');
			for (var i=0;i<orig.length;i++){
				if (orig[i].name.indexOf(srctype)>=0 && orig[i].name.indexOf("[")>=0){
					// TODO double check the str replacement only replaces one instance!!!
					targetName = orig[i].name.replace(srctype,"refField");
					target = document.getElementsByName(targetName);
					if (target.length!=1){
						alert(targetName+" problem "+target.length);
					}
					else {
						target[0].selectedIndex = orig[i].selectedIndex;
					}
				}
			}
			var orig = document.getElementsByTagName('input');
			for (var i=0;i<orig.length;i++){
				if (orig[i].name.indexOf(srctype)>=0 && orig[i].name.indexOf("[")>=0){
					// treat radio buttons differently
					if (orig[i].type.toLowerCase()=="radio"){
						//alert( orig[i].id+" "+orig[i].checked);
						targetId = orig[i].id;
						if (targetId){
							targetId = targetId.replace(srctype,"refField");
							target = document.getElementById(targetId);
							if (!target){
								alert("missing target for radio button "+orig[i].name);
							}
							else {
								target.checked = orig[i].checked;
							}
						}
						else {
							alert("missing id for radio button "+orig[i].name);
						}
					}
					else {
						// TODO double check the str replacement only replaces one instance!!!
						targetName = orig[i].name.replace(srctype,"refField");
						target = document.getElementsByName(targetName);
						if (target.length!=1){
							alert(targetName+" problem "+target.length);
						}
						else {
							target[0].value = orig[i].value;
						}
					}
				}
			}
			var orig = document.getElementsByTagName('textarea');
			for (var i=0;i<orig.length;i++){
				if (orig[i].name.indexOf(srctype)>=0 && orig[i].name.indexOf("[")>=0){
					// TODO double check the str replacement only replaces one instance!!!
					targetName = orig[i].name.replace(srctype,"refField");
					target = document.getElementsByName(targetName);
					if (target.length!=1){
						alert(targetName+" problem "+target.length);
					}
					else {
						target[0].value = orig[i].value;
					}
				}
			}
		}

		var orig = document.getElementsByTagName('select');
		for (var i=0;i<orig.length;i++){
			if (orig[i].name.indexOf("$fieldname")>=0){
				orig[i].disabled = true;
			}
		}
		var orig = document.getElementsByTagName('input');
		for (var i=0;i<orig.length;i++){
			if (orig[i].name.indexOf("$fieldname")>=0){
				orig[i].disabled = true;
			}
		}
		</script>
SCRIPT;
		echo $output;

	}

	function showDefault()
	{
		$output = "<span style='display:none'>";
		$output .= $this->defaultparams->render("defaultvalue_" . $this->fieldname);
		$output .= "</span>\n";
		echo $output;

	}
    function editTranslation(){
	    echo '<div class="form-horizontal translation-field-'.$this->fieldname.'">';
        echo $this->transparams->render("refField_".$this->fieldname);
        echo '</div';
        return false;
    }
}

class JFMenuParams extends JObject
{

	var $form = null;

	function __construct($form=null, $item=null)
	{
		$this->form = $form;

	}

	function render($type)
	{
		$this->menuform = $this->form;
		echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'collapse0'));
		$i = 0;


		$fieldSets = $this->form->getFieldsets('request');
		if ($fieldSets)
		{
			foreach ($fieldSets as $name => $fieldSet)
			{
				$hidden_fields = '';
				$label = !empty($fieldSet->label) ? $fieldSet->label : 'COM_MENUS_' . $name . '_FIELDSET_LABEL';
  				  echo JHtml::_('bootstrap.addTab', 'myTab', 'collapse' . ($i++), addslashes(JText::_($label)), true);

				if (isset($fieldSet->description) && trim($fieldSet->description)) :
					echo '<p class="tip">' . htmlspecialchars(JText::_($fieldSet->description), ENT_QUOTES, 'UTF-8')  . '</p>';
				endif;
				?>
				<div class="clr"></div>
				<fieldset class="panelform">

						<?php foreach ($this->form->getFieldset($name) as $field){ ?>
							<?php if (!$field->hidden)
							{
								echo $field->renderField();
							}
							else
							{
								$hidden_fields.= $field->input;
								?>
							<?php } ?>

						<?php } ?>

					<?php echo $hidden_fields; ?>
				</fieldset>

				<?php
				echo JHtml::_('bootstrap.endTab');
			}
		}

		$paramsfieldSets = $this->form->getFieldsets('params');
		if ($paramsfieldSets)
		{
			foreach ($paramsfieldSets as $name => $fieldSet)
			{
				$label = !empty($fieldSet->label) ? $fieldSet->label : 'COM_MENUS_' . $name . '_FIELDSET_LABEL';
  				  echo JHtml::_('bootstrap.addTab', 'myTab', 'collapse' . ($i++),JText::_($label), true);


				if (isset($fieldSet->description) && trim($fieldSet->description)) :
					echo '<p class="tip">' . htmlspecialchars(JText::_($fieldSet->description), ENT_QUOTES, 'UTF-8') . '</p>';
				endif;
				?>
				<div class="clr"></div>
				<fieldset class="panelform">
					<ul class="adminformlist">
						<?php foreach ($this->form->getFieldset($name) as $field) : ?>
							<?php echo $field->renderField(); ?>
						<?php endforeach; ?>
					</ul>
				</fieldset>

				<?php
				echo JHtml::_('bootstrap.endTab');
            }
		}
		echo JHtml::_('bootstrap.endTabSet');
		return;

	}

}


class JFContentParams extends JObject
{

    var $form = null;

    function __construct($form=null, $item=null)
    {
        $this->form = $form;

    }

    function render($type)
    {

	    echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'basic'));
		$paramsfieldSets = $this->form->getFieldsets('attribs');
		if ($paramsfieldSets)
		{

			foreach ($paramsfieldSets as $name => $fieldSet)
			{
				$label = !empty($fieldSet->label) ? $fieldSet->label : 'COM_CONTENT_' . $name . '_FIELDSET_LABEL';
				if ($name == 'basic-limited') {
					continue;
				}
				if ($name == 'editorConfig' ) {
					$label = 'COM_CONTENT_SLIDER_EDITOR_CONFIG';
				}
				echo JHtml::_('bootstrap.addTab', 'myTab', $name, addslashes(JText::_($label)), true);

				if (isset($fieldSet->description) && trim($fieldSet->description)) :
					echo '<p class="tip">' . htmlspecialchars(JText::_($fieldSet->description), ENT_QUOTES, 'UTF-8') . '</p>';
				endif;
				?>
				<div class="clr"></div>
				<fieldset class="panelform">
					<?php foreach ($this->form->getFieldset($name) as $field) : ?>

						<?php echo $field->renderField(); ?>
					<?php endforeach; ?>
				</fieldset>

				<?php
				echo JHtml::_('bootstrap.endTab');

			}

		}

		//v2.1 add images in translation
		$params = JComponentHelper::getParams('com_content');
		if ($params->get('show_urls_images_backend') == 1) {
			$imagesfields = $this->form->getGroup('images');
			$urlsfields = $this->form->getGroup('urls');
			echo JHtml::_('bootstrap.addTab', 'myTab', 'images', JText::_('COM_CONTENT_FIELDSET_URLS_AND_IMAGES', true));
            ?> <div class="row-fluid"> <?php
			if ($imagesfields) {
				?>

				<div class="span6">
					<?php echo $this->form->renderField('images') ?>
					<?php foreach ($imagesfields as $field) : ?>
						<?php echo $field->renderField(); ?>
					<?php endforeach; ?>
				</div>
			<?php
			}
			if ($urlsfields) {
				?>
				<div class="span6">
					<?php echo $this->form->renderField('urls') ?>
					<?php foreach ($urlsfields as $field) : ?>
						<?php echo $field->renderField(); ?>
					<?php endforeach; ?>
				</div>
			<?php
			}
            ?> </div> <?php
			echo JHtml::_('bootstrap.endTab');
		}

		//2.8.3 support of custom fields
	    $customfieldSets = $this->form->getFieldsets('com_fields');
	    if (isset($customfieldSets))
	    {
		    foreach ($customfieldSets as $name => $fieldSet)
		    {
			    $label = !empty($fieldSet->label) ? $fieldSet->label : 'COM_CONTENT_' . $name . '_FIELDSET_LABEL';
			    echo JHtml::_('bootstrap.addTab', 'myTab', $name, addslashes(JText::_($label)), true);

			    if (isset($fieldSet->description) && trim($fieldSet->description)) :
				    echo '<p class="tip">' . htmlspecialchars(JText::_($fieldSet->description), ENT_QUOTES, 'UTF-8') . '</p>';
			    endif;
			    ?>
			    <div class="clr"></div>
			    <fieldset class="panelform">
				    <ul class="adminformlist">
					    <?php foreach ($this->form->getFieldset($name) as $field) : ?>
						    <?php echo $field->renderField(); ?>
					    <?php endforeach; ?>
				    </ul>
			    </fieldset>

			    <?php
			    echo JHtml::_('bootstrap.endTab');
		    }
	    }
		echo JHtml::_('bootstrap.endTabSet');

		return;
	}

}

class TranslateParams_menu extends TranslateParams_xml
{

	var $_menutype;
	var $_menuViewItem;
	var $orig_modelItem;
	var $trans_modelItem;

	function __construct($original, $translation, $fieldname, $fields=null)
	{
		parent::__construct($original, $translation, $fieldname, $fields);
		$lang = JFactory::getLanguage();
		$lang->load("com_menus", JPATH_ADMINISTRATOR);

		$jinput = JFactory::getApplication()->input;
		$cid = $jinput->get('cid', array(0),'STR');

		$oldcid = $cid;
		$translation_id = 0;
		if (strpos($cid[0], '|') !== false)
		{
			list($translation_id, $contentid, $language_id) = explode('|', $cid[0]);
		}

		$jinput->set('cid',array($contentid));
		$jinput->set('edit',true);

        JLoader::import('models.JFMenusModelItem', FALANG_ADMINPATH);
        $this->orig_modelItem = new JFMenusModelItem();


		// Get The Original State Data
		// model's populate state method assumes the id is in the request object!
        $oldid = $jinput->get('id',0,'INT');
        $jinput->set('id',$contentid);

		// NOW GET THE TRANSLATION - IF AVAILABLE
		$this->trans_modelItem = new JFMenusModelItem();
		$this->trans_modelItem->setState('item.id', $contentid);
		if ($translation != "")
		{
			//fix bug in hikashop force return as array
			$translation = json_decode($translation,true);
		}

		$translationMenuModelForm = $this->trans_modelItem->getForm();

		//2.8.4
        //due to hikashop bugfix we need to get jfrequest by $translation['jfrequest'] and no more by $translation->jfrequest
		if (isset($translation['jfrequest'])){
			$translationMenuModelForm->bind(array("params" => $translation, "request" =>$translation['jfrequest']));
		}
		else {
			$translationMenuModelForm->bind(array("params" => $translation));
		}
		$cid = $oldcid;
		$jinput->set('cid', $cid);
		$jinput->set('id', $oldid);

		$this->transparams = new JFMenuParams($translationMenuModelForm);

	}

	function editTranslation()
	{
		if ($this->_menutype == "wrapper")
		{
			?>
			<table width="100%" class="paramlist">
				<tr>
					<td width="40%" align="right" valign="top"><span class="editlinktip"><!-- Tooltip -->
							<span onmouseover="return overlib('Link for Wrapper', CAPTION, 'Wrapper Link', BELOW, RIGHT);" onmouseout="return nd();" >Wrapper Link</span></span></td>
					<td align="left" valign="top"><input type="text" name="refField_params[url]" value="<?php echo $this->transparams->get('url', '') ?>" class="text_area" size="30" /></td>
				</tr>
			</table>
			<?php
		}
		parent::editTranslation();

	}

}

class JFModuleParams extends JObject
{

	protected $form = null;
	protected $item = null;

	function __construct($form=null, $item=null)
	{
		$this->form = $form;
		$this->item = $item;

	}

	function render($type)
	{

		echo JHtml::_('bootstrap.startTabSet', 'module-sliders', array('active' => 'basic-options'));

        $paramsfieldSets = $this->form->getFieldsets('params');
		if ($paramsfieldSets)
		{
			foreach ($paramsfieldSets as $name => $fieldSet)
			{
				$label = !empty($fieldSet->label) ? $fieldSet->label : 'COM_MODULES_' . $name . '_FIELDSET_LABEL';
				echo JHtml::_('bootstrap.addTab', 'module-sliders', $name.'-options', addslashes(JText::_($label)));
				if (isset($fieldSet->description) && trim($fieldSet->description)) :
					echo '<p class="tip">' . htmlspecialchars(JText::_($fieldSet->description), ENT_QUOTES, 'UTF-8')  . '</p>';
				endif;
				?>
				<div class="clr"></div>
				<fieldset class="panelform">
						<?php foreach ($this->form->getFieldset($name) as $field) : ?>
							<?php echo $field->renderField(); ?>
						<?php endforeach; ?>
				</fieldset>
				<?php
				echo JHtml::_('bootstrap.endTab');
			}
		}
        //not render assignment menu
        //depends on the original menu
		echo JHtml::_('bootstrap.endTabSet');
		return;

	}

}

class JFFieldsParams extends JObject
{

	protected $form = null;
	protected $item = null;

	function __construct($form=null, $item=null)
	{
		$this->form = $form;
		$this->item = $item;

	}

	function render($type)
	{
	    $options = ['readonly' => true];
		$paramsfieldSets = $this->form->getFieldsets('fieldparams');
		if ($paramsfieldSets) {
			foreach ($paramsfieldSets as $name => $fieldSet) {
				foreach ($this->form->getFieldset($name) as $field) {
				    echo $field->renderField($options);
                }
			}
		}
		return;
	}

}



class TranslateParams_modules extends TranslateParams_xml
{

	function __construct($original, $translation, $fieldname, $fields=null)
	{
        if (FALANG_J30){
            require_once JPATH_ADMINISTRATOR.'/components/com_modules/helpers/modules.php';
        }
		parent::__construct($original, $translation, $fieldname, $fields);
		$lang = JFactory::getLanguage();
		$lang->load("com_modules", JPATH_ADMINISTRATOR);
		$jinput = JFactory::getApplication()->input;

		$cid = $jinput->get('cid', array(0),'STR');
		$oldcid = $cid;
		$translation_id = 0;
		if (strpos($cid[0], '|') !== false)
		{
			list($translation_id, $contentid, $language_id) = explode('|', $cid[0]);
		}

		// if we have an existing translation then load this directly!
		// This is important for modules to populate the assignement fields

		//$contentid = $translation_id?$translation_id : $contentid;

		//TODO sbou check this
		$jinput->set('cid',array($contentid));
		$jinput->set('edit',true);

		JLoader::import('models.JFModuleModelItem', FALANG_ADMINPATH);

		// Get The Original State Data
		// model's populate state method assumes the id is in the request object!
		$oldid = $jinput->get('id',0,'INT');
		$jinput->set('id',$contentid);

		// NOW GET THE TRANSLATION - IF AVAILABLE
		$this->trans_modelItem = new JFModuleModelItem();
		$this->trans_modelItem->setState('module.id', $contentid);
		if ($translation != "")
		{
		    //for return as associated array and not a stdclass
            //fix bug with easyblog
			$translation = json_decode($translation,true);
		}
		$translationModuleModelForm = $this->trans_modelItem->getForm();
		if (isset($translation->jfrequest)){
			$translationModuleModelForm->bind(array("params" => $translation, "request" =>$translation->jfrequest));
		}
		else {
			$translationModuleModelForm->bind(array("params" => $translation));
		}

		$cid = $oldcid;
		$jinput->set('cid', $cid);
		$jinput->set("id", $oldid);

		$this->transparams = new JFModuleParams($translationModuleModelForm, $this->trans_modelItem->getItem());

	}

	function showOriginal()
	{
		parent::showOriginal();

		$output = "";
		if ($this->origparams->getNumParams('advanced'))
		{
			$fieldname = 'orig_' . $this->fieldname;
			$output .= $this->origparams->render($fieldname, 'advanced');
		}
		if ($this->origparams->getNumParams('other'))
		{
			$fieldname = 'orig_' . $this->fieldname;
			$output .= $this->origparams->render($fieldname, 'other');
		}
		if ($this->origparams->getNumParams('legacy'))
		{
			$fieldname = 'orig_' . $this->fieldname;
			$output .= $this->origparams->render($fieldname, 'legacy');
		}
		echo $output;

	}


	function editTranslation()
	{
		parent::editTranslation();

	}

}

class TranslateParams_fields extends TranslateParams_xml
{

	function __construct($original, $translation, $fieldname, $fields=null)
	{
		require_once JPATH_ADMINISTRATOR.'/components/com_fields/helpers/fields.php';

		parent::__construct($original, $translation, $fieldname, $fields);
		$lang = JFactory::getLanguage();
		$lang->load("com_fields", JPATH_ADMINISTRATOR);
		$jinput = JFactory::getApplication()->input;

		$cid = $jinput->get('cid', array(0),'STR');
		$oldcid = $cid;
		$translation_id = 0;
		if (strpos($cid[0], '|') !== false)
		{
			list($translation_id, $contentid, $language_id) = explode('|', $cid[0]);
		}

		// if we have an existing translation then load this directly!
		// This is important for modules to populate the assignement fields

		//$contentid = $translation_id?$translation_id : $contentid;

		//TODO sbou check this
		$jinput->set('cid',array($contentid));
		$jinput->set('edit',true);

		JLoader::import('models.JFFieldModelItem', FALANG_ADMINPATH);

		// Get The Original State Data
		// model's populate state method assumes the id is in the request object!
		$oldid = $jinput->get('id',0,'INT');
		$jinput->set('id',$contentid);

		// NOW GET THE TRANSLATION - IF AVAILABLE
		$this->trans_modelItem = new JFFieldModelItem();
		$this->trans_modelItem->setState('field.id', $contentid);
		if ($translation != "")
		{
			//for return as associated array and not a stdclass
			//fix bug with easyblog
			$translation = json_decode($translation,true);
		}
		$translationFieldModelForm = $this->trans_modelItem->getForm();
		if (isset($translation->jfrequest)){
			$translationFieldModelForm->bind(array("fieldparams" => $translation, "request" =>$translation->jfrequest));
		}
		else {
			$translationFieldModelForm->bind(array("fieldparams" => $translation));
		}

		$cid = $oldcid;
		$jinput->set('cid', $cid);
		$jinput->set("id", $oldid);

		$this->transparams = new JFFieldsParams($translationFieldModelForm, $this->trans_modelItem->getItem());

	}

	function showOriginal()
	{
		parent::showOriginal();

		$output = "";
		if ($this->origparams->getNumParams('advanced'))
		{
			$fieldname = 'orig_' . $this->fieldname;
			$output .= $this->origparams->render($fieldname, 'advanced');
		}
		if ($this->origparams->getNumParams('other'))
		{
			$fieldname = 'orig_' . $this->fieldname;
			$output .= $this->origparams->render($fieldname, 'other');
		}
		if ($this->origparams->getNumParams('legacy'))
		{
			$fieldname = 'orig_' . $this->fieldname;
			$output .= $this->origparams->render($fieldname, 'legacy');
		}
		echo $output;

	}


	function editTranslation()
	{
		parent::editTranslation();

	}

}

class TranslateParams_content extends TranslateParams_xml
{

    var $orig_contentModelItem;
    var $trans_contentModelItem;

    function __construct($original, $translation, $fieldname, $fields=null)
    {
	    $jinput = JFactory::getApplication()->input;
        require_once JPATH_ADMINISTRATOR.'/components/com_content/helpers/content.php';

        parent::__construct($original, $translation, $fieldname, $fields);
        $lang = JFactory::getLanguage();
        $lang->load("com_content", JPATH_ADMINISTRATOR);

        $cid = $jinput->get('cid', array(0),'STR');
        $oldcid = $cid;
        $translation_id = 0;
        if (strpos($cid[0], '|') !== false)
        {
            list($translation_id, $contentid, $language_id) = explode('|', $cid[0]);
        }

        $jinput->set('cid',array($contentid));
        $jinput->set('edit',true);

        // model's populate state method assumes the id is in the request object!
        $oldid = $jinput->get("article_id", 0, 'INT');
        // Take care of the name of the id for the item
	    $jinput->set("article_id", $contentid);

        JLoader::import('models.JFContentModelItem', FALANG_ADMINPATH);
        $this->orig_contentModelItem = new JFContentModelItem();

        // Get The Original form
        // JRequest does NOT this for us in articles!!
        $this->orig_contentModelItem->setState('article.id',$contentid);
        $jfcontentModelForm = $this->orig_contentModelItem->getForm();

        // NOW GET THE TRANSLATION - IF AVAILABLE
        $this->trans_contentModelItem = new JFContentModelItem();
        $this->trans_contentModelItem->setState('article.id', $contentid);
        if ($translation != "")
        {
            $translation = json_decode($translation,true);
        }
        $translationcontentModelForm = $this->trans_contentModelItem->getForm();

		if (isset($translation->jfrequest)) {
			$translationcontentModelForm->bind(array("attribs" => $translation,"images" => $translation,"urls" => $translation, "request" => $translation->jfrequest));
		} else {
			$translationcontentModelForm->bind(array("attribs" => $translation,"images" => $translation,"urls" => $translation));
		}

        // reset old values in REQUEST array
        $cid = $oldcid;
	    $jinput->set('cid', $cid);
	    $jinput->set("article_id", $oldid);

        //	$this->origparams = new JFContentParams( $jfcontentModelForm);
        $this->transparams = new JFContentParams($translationcontentModelForm);


    }

    function showOriginal()
    {
        parent::showOriginal();

        $output = "";
        if ($this->origparams->getNumParams('advanced'))
        {
            $fieldname = 'orig_' . $this->fieldname;
            $output .= $this->origparams->render($fieldname, 'advanced');
        }
        if ($this->origparams->getNumParams('legacy'))
        {
            $fieldname = 'orig_' . $this->fieldname;
            $output .= $this->origparams->render($fieldname, 'legacy');
        }
        echo $output;

    }

    function editTranslation()
    {
        parent::editTranslation();
    }

}

class TranslateParams_components extends TranslateParams_xml
{
	var $_menutype;
	var $_menuViewItem;
	var $orig_menuModelItem;
	var $trans_menuModelItem;

	public function __construct($original, $translation, $fieldname, $fields=null){
		$lang = JFactory::getLanguage();
		$lang->load("com_config", JPATH_ADMINISTRATOR);

		$this->fieldname = $fieldname;
		global $mainframe;
		$content = null;
		foreach ($fields as $field) {
			if ($field->Name=="option"){
				$comp = $field->originalValue;
				break;
			}
		}
		$lang->load($comp, JPATH_ADMINISTRATOR);
		
		$path = DS."components".DS.$comp.DS."config.xml";
        //sbou
        $xmlfile = $path;
		//$xmlfile = JApplicationHelper::_checkPath($path);
        //fin sbou
		
		$this->origparams = new JParameter($original, $xmlfile,"component");
		$this->transparams = new JParameter($translation, $xmlfile,"component");
		$this->defaultparams = new JParameter("", $xmlfile,"component");
		$this->fields = $fields;

	}

	function showOriginal(){
		if ($this->_menutype=="wrapper"){
			?>
			<table width="100%" class="paramlist">
			<tr>
			<td width="40%" align="right" valign="top"><span class="editlinktip"><!-- Tooltip -->
			<span onmouseover="return overlib('Link for Wrapper', CAPTION, 'Wrapper Link', BELOW, RIGHT);" onmouseout="return nd();" >Wrapper Link</span></span></td>

			<td align="left" valign="top"><input type="text" name="orig_params[url]" value="<?php echo $this->origparams->get('url','')?>" class="text_area" size="30" /></td>
			</tr>
			</table>
			<?php
		}
		parent::showOriginal();
	}

	function editTranslation(){
		if ($this->_menutype=="wrapper"){
			?>
			<table width="100%" class="paramlist">
			<tr>
			<td width="40%" align="right" valign="top"><span class="editlinktip"><!-- Tooltip -->
			<span onmouseover="return overlib('Link for Wrapper', CAPTION, 'Wrapper Link', BELOW, RIGHT);" onmouseout="return nd();" >Wrapper Link</span></span></td>
			<td align="left" valign="top"><input type="text" name="refField_params[url]" value="<?php echo $this->transparams->get('url','')?>" class="text_area" size="30" /></td>
			</tr>
			</table>
			<?php
		}
		parent::editTranslation();
	}

}

//new Falang 2.0
class JFCategoryParams extends JObject
{

	protected $form = null;
	protected $item = null;

	function __construct($form=null, $item=null)
	{
		$this->form = $form;
		$this->item = $item;

	}

	function render($type)
	{

		echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'basic'));

		$paramsfieldSets = $this->form->getFieldsets('params');
		if ($paramsfieldSets)
		{
			foreach ($paramsfieldSets as $name => $fieldSet)
			{
				$label = !empty($fieldSet->label) ? $fieldSet->label : 'COM_CATEGORIES_' . $name . '_FIELDSET_LABEL';
				echo JHtml::_('bootstrap.addTab', 'myTab', $name, addslashes(JText::_($label)), true);
    				if (isset($fieldSet->description) && trim($fieldSet->description)) :
					echo '<p class="tip">' . htmlspecialchars(JText::_($fieldSet->description), ENT_QUOTES, 'UTF-8')  . '</p>';
				endif;
				?>
				<div class="clr"></div>
				<fieldset class="panelform">
					<ul class="adminformlist">
						<?php foreach ($this->form->getFieldset($name) as $field) : ?>
							<?php echo $field->renderField(); ?>
						<?php endforeach; ?>
					</ul>
				</fieldset>

			<?php
				echo JHtml::_('bootstrap.endTab');
			}
		}
		echo JHtml::_('bootstrap.endTabSet');
		return;

	}

}
class TranslateParams_categories extends TranslateParams_xml
{

	function __construct($original, $translation, $fieldname, $fields=null)
	{
		require_once JPATH_ADMINISTRATOR.'/components/com_categories/helpers/categories.php';
		parent::__construct($original, $translation, $fieldname, $fields);
		$jinput = JFactory::getApplication()->input;

		$lang = JFactory::getLanguage();
		$lang->load("com_categories", JPATH_ADMINISTRATOR);

		$cid = $jinput->get('cid', array(0),'STR');
		$oldcid = $cid;
		$translation_id = 0;
		if (strpos($cid[0], '|') !== false)
		{
			list($translation_id, $contentid, $language_id) = explode('|', $cid[0]);
		}

		// if we have an existing translation then load this directly!
		// This is important for modules to populate the assignement fields

		//$contentid = $translation_id?$translation_id : $contentid;

		//TODO sbou check this
		$jinput->set('cid',array($contentid));
		$jinput->set('edit',true);


		JLoader::import('models.JFCategoryModelItem', FALANG_ADMINPATH);

		// Get The Original State Data
		// model's populate state method assumes the id is in the request object!
		$oldid = $jinput->get('id',0,'INT');
		$jinput->set('id',$contentid);

		// NOW GET THE TRANSLATION - IF AVAILABLE
		$this->trans_modelItem = new JFCategoryModelItem();
		$this->trans_modelItem->setState('category.id', $contentid);
		if ($translation != "")
		{
			$translation = json_decode($translation,true);
		}
		$translationCategoryModelForm = $this->trans_modelItem->getForm();
		if (isset($translation->jfrequest)){
			$translationCategoryModelForm->bind(array("params" => $translation, "request" =>$translation->jfrequest));
		}
		else {
			$translationCategoryModelForm->bind(array("params" => $translation));
		}

		// reset old values in REQUEST array
		$cid = $oldcid;
		$jinput->set('cid', $cid);
		$jinput->set("id", $oldid);


		$this->transparams = new JFCategoryParams($translationCategoryModelForm, $this->trans_modelItem->getItem());

	}

	function showOriginal()
	{
		parent::showOriginal();

		$output = "";
		if ($this->origparams->getNumParams('advanced'))
		{
			$fieldname = 'orig_' . $this->fieldname;
			$output .= $this->origparams->render($fieldname, 'advanced');
		}
		if ($this->origparams->getNumParams('other'))
		{
			$fieldname = 'orig_' . $this->fieldname;
			$output .= $this->origparams->render($fieldname, 'other');
		}
		if ($this->origparams->getNumParams('legacy'))
		{
			$fieldname = 'orig_' . $this->fieldname;
			$output .= $this->origparams->render($fieldname, 'legacy');
		}
		echo $output;

	}


	function editTranslation()
	{
		parent::editTranslation();

	}

}
