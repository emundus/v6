<?php
/**
 * Plugin element to render fields
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.field
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

/**
 * Plugin element to render fields
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.field
 * @since       3.0
 */

class PlgFabrik_ElementAutocomplete extends PlgFabrik_Element
{

	var $_pluginName = 'autocomplete';

	/**
	 * Constructor
	 */
	/*
	function __construct()
	{
		$current_user =& JFactory::getUser();
		$allowed = array("Super Administrator", "Administrator", "Publisher", "Editor", "Author", "Registered");
		if (!in_array($current_user->usertype, $allowed)) {
			die(JText::_('You are not allowed to access to this page...').$current_user->usertype);
		}
		parent::__construct();
	}
	*/
	/**
	 * this really does get just the default value (as defined in the element's settings)
	 * @param array data
	 * @return string
	 */

	function getDefaultValue($data = array(), $repeatCounter = 0)
	{
		if (!isset($this->_default)) {
			$w = new FabrikWorker();
			$element =& $this->getElement();
			$default	 	=& $element->default;
			$default = $w->parseMessageForPlaceHolder($default, $data, true, true);
			if ($element->eval == "1") {
				$default = JDEBUG ? eval($default) : @eval($default);
			}
			$this->_default = $default;
		}
		return $this->_default;
	}


	/**
	 * shows the data formatted for the table view
	 * @param string data
	 * @param object  current row's data
	 * @return string formatted value
	 */

	function renderListData($data, &$thisRow)
	{
	
		/* Ancienne version
		$params =& $this->getParams();
		$format = $params->get('ac_format_string');
		if ($format  != '') {
			$data = sprintf($format, $data);
		}
		return parent::renderTableData($data, $thisRow);
		*/
		
		$params = $this->getParams();
		$data = FabrikWorker::JSONtoData($data, true);
		$format = $params->get('text_format_string');
		foreach ($data as &$d)
		{
			$d = $this->numberFormat($d);
			if ($format != '')
			{
				$d = sprintf($format, $d);
			}
			$this->_guessLinkType($d, $thisRow, 0);
		}
		return parent::renderTableData($data, $thisRow);
		
		
	}

	/**
	 * fudge the CSV export so that we get the calculated result regardless of whether
	 * the value has been stored in the database base (mimics what the user would see in the table view)
	 * @see components/com_fabrik/models/FabrikModelElement#renderTableData_csv($data, $oAllRowsData)
	 */

	function renderListData_csv($data, &$thisRow)
	{
		/* Ancienne version function renderTableData_csv($data, $col, &$thisRow)
		$val = $this->renderTableData($data, $thisRow);
		$raw = $col . '_raw';
		$thisRow->$raw = $val;
		return $val;
		*/
		
		$params = $this->getParams();
		$data = $this->numberFormat($data);
		$format = $params->get('text_format_string');
		if ($format != '')
		{
			$data = sprintf($format, $data);
		}
		return $data;
	}

	/**
	 * draws the form element
	 * @param array data
	 * @param int repeat group counter
	 * @return string returns element html
	 */

	function render($data, $repeatCounter = 0)
	{
		$params 		=& $this->getParams();
		$element 		=& $this->getElement();
		$data 			=& $this->_form->_data;

		FabrikHelperHTML::stylesheet(COM_FABRIK_LIVESITE . 'plugins/fabrik_element/autocomplete/autosuggest_inquisitor.css');
		FabrikHelperHTML::script(COM_FABRIK_LIVESITE . 'plugins/fabrik_element/autocomplete/bsn.AutoSuggest_2.1.3_comp.js');

		$value 	= $this->getValue($data, $repeatCounter);
		$callbackid = $params->get('ac_callback_id');
		$sql = $params->get('ac_sql');
		$search_field = $params->get('ac_search_field');
		$search_value_name = $params->get('ac_search_value_id');
		$info_field = $params->get('ac_info_field');
		$infoid = $params->get('ac_info_cible_id');

		$format = $params->get('ac_format_string');
		if ($format != '') {
			$value = sprintf($format, $value);
		}
		$name = $this->getHTMLName($repeatCounter);
		$id = $this->getHTMLId($repeatCounter);
		$str = '';
		//$str = '<div class="asholder">';
		if ($this->canView()) {
			if (!$this->_editable) {
				$str = $value;
			}
			else {
				$str .= "<input class=\"fabrikinput inputbox\" name=\"$name\" id=\"$id\" value=\"$value\" size=\"$element->width\" />\n";
			}
		} else {
			/* make a hidden field instead*/
			$str .= "<input type=\"hidden\" class=\"fabrikinput\" name=\"$name\" id=\"$id\" value=\"$value\" />";
		}
		//$str .= " <img src=\"" . COM_FABRIK_LIVESITE . "media/com_fabrik/images/ajax-loader.gif\" id=\"".$id."_loader\" class=\"loader\" alt=\"" . JText::_('Loading') . "\" style=\"display:none;padding-left:10px;\" />";
		$str .= " <img src=\"" . COM_FABRIK_LIVESITE . "media/com_fabrik/images/ajax-loader.gif\" class=\"loader\" alt=\"" . JText::_('Loading') . "\" style=\"display:none;padding-left:10px;\" />";

		return $str;
	}

	function getFieldDescription()
	{
		$p =& $this->getParams();
		if ($this->encryptMe()) {
			return 'BLOB';
		}
		return "TEXT";
	}

	/**
	 * return tehe javascript to create an instance of the class defined in formJavascriptClass
	 * @return string javascript to create instance. Instance name must be 'el'
	 */

	function elementJavascript($repeatCounter)
	{
		$id = $this->getHTMLId($repeatCounter);
		$opts = $this->getElementJSOptions($repeatCounter);
		$params = $this->getParams(); 
		$opts->sql = $params->get('ac_sql');
		$opts->search_field = $params->get('ac_search_field');
		
		if($params->get('ac_callback_id')!='') $opts->callbackid=$params->get('ac_callback_id'); else $opts->callbackid='null';
		if($params->get('ac_search_value_id')!='') $opts->search_value_name=$params->get('ac_search_value_id'); else $opts->search_value_name='null';
		if($params->get('ac_info_field')!='') $opts->info_field = $params->get('ac_info_field'); else $opts->info_field ='null';
		if($params->get('ac_info_cible_id')!='') $opts->infoid = $params->get('ac_info_cible_id'); else $opts->infoid ='null';
		
		$str = '';
		if($params->get('join_val_column_concat')!='') {
			$opts->r1 = $params->get('join_val_column_concat'); 
		} else {
			if($params->get('join_key_column')!='') $str = $params->get('join_key_column'); 
			if($params->get('join_val_column')!='') $str .= ',' . $params->get('join_val_column'); 
			$opts->r1 = $str;
		}	

		if($params->get('join_db_name')!='') $opts->r2 = $params->get('join_db_name'); else $opts->r2 ='null';
		if($params->get('database_join_where_sql')!='') $opts->r3 = $params->get('database_join_where_sql'); else $opts->r3 ='';

		
		$opts->format = $params->get('ac_format_string');
		$opts->liveSite = COM_FABRIK_LIVESITE;
		$opts->id = $id;
		
		/*
		$autoOpts = array();
		$autoOpts['storeMatchedResultsOnly'] = true;
		FabrikHelperHTML::autoComplete($id, $this->getElement()->id, $this->getFormModel()->getId(), 'databasejoin', $autoOpts);
		*/
		
		//$opts = $this->elementJavascriptOpts($repeatCounter);
		$opts = json_encode($opts);
		return array('FbAutocomplete', $id, $opts);
	}
	
	function onjson_get() 
	{
		$user = JFactory::getUser();
		if ($user->guest)
			exit();
		
		$params = $this->getParams();
		//$search_field = $params->get('ac_search_field');
		$s = JRequest::getVar('input');
		//$sql = JRequest::getVar('sql');
		//$sql = $params->get('ac_sql');
		$search_field = JRequest::getVar('search_field');
		$search_value_name = JRequest::getVar('search_value_name');
		$search_value = JRequest::getVar('search_value');
		$info_field = JRequest::getVar('info_field');

		$sql_select = JRequest::getVar('r1');
		$sql_from = JRequest::getVar('r2');
		$sql_where = JRequest::getVar('r3');
		$sql = 'SELECT '.$sql_select . ' FROM `' . $sql_from . '` WHERE ' . $sql_where;

		$sql = str_replace('insert', '', $sql);
		$sql = str_replace('update', '', $sql);
		$sql = str_replace('user', '', $sql);
		$sql = str_replace('password', '', $sql);
		$sql = str_replace('{input}', '"%'.$s.'%"', $sql);
		
		if (isset($search_value) && $search_value != '')
			$sql = str_replace('{ac_search_value}', $search_value, $sql);

		$db = JFactory::getDBO();
		$slist = explode(' ', $s);
		//$where = 'ja.name like "%'.$slist[0].'%" ';
		$where = '';
		if (count($slist)>1) {
			$where .= ' OR (';
			for($i=1 ; count($slist) > $i ; $i++) {
				if (strlen($slist[$i])>3)
					$where .= '`'.$search_field.'` like "%'.$slist[$i].'%" AND ';
			}
			$where .= ' 1=1)';
		}
		
		$query = $sql.' '.$where.' ORDER BY '.$search_field.' LIMIT 6';

		$db->setQuery( $query );
		$data = $db->loadAssocList();
	
		$json = "{\"results\": [";
		$first = true;
		if(count($data) > 0){
			foreach ($data as $d) {
				if ($first) 
					$first = false;
				else
					$json .= ",";
				
				$json .= "{\"id\": \"".$d['id']."\", \"value\": \"".str_replace('"', '\"', $d[$search_field])."\", \"info\": \"".$d[$info_field]."\"}";
			}
		$json .= "]}";
		} else
			$json .= $query."]}";

		echo $json;
		
	}
}
?>