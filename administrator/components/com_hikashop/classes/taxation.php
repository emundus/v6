<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class hikashopTaxationClass extends hikashopClass{
	var $tables = array('taxation');
	var $pkeys = array('taxation_id');
	var $toggle = array('taxation_published'=>'taxation_id');

	function get($id,$default=null){
		$query='SELECT b.*,c.*,d.*,a.* FROM '.hikashop_table('taxation').' AS a LEFT JOIN '.hikashop_table('tax').' AS b ON a.tax_namekey=b.tax_namekey LEFT JOIN '.hikashop_table('category').' AS c ON a.category_namekey=c.category_namekey LEFT JOIN '.hikashop_table('zone').' AS d ON a.zone_namekey=d.zone_namekey WHERE a.taxation_id='.(int)$id.' LIMIT 1';
		$this->database->setQuery($query);
		return $this->database->loadObject();
	}

	function saveForm(){
		$taxation = new stdClass();
		$taxation->taxation_id = hikashop_getCID('taxation_id');
		$formData = hikaInput::get()->get('data', array(), 'array');
		jimport('joomla.filter.filterinput');
		$safeHtmlFilter = JFilterInput::getInstance(array(), array(), 1, 1);
		foreach($formData['taxation'] as $column => $value){
			hikashop_secureField($column);
			if(in_array($column,array('zone_namekey','taxation_type'))){
				if(is_array($value)){
					$value=implode(',',$value);
					if($column=='taxation_type' && !empty($value))
						$value=','.$value.',';
				}
			}
			$taxation->$column = $safeHtmlFilter->clean(strip_tags($value), 'string');
		}
		if(!isset($taxation->taxation_type)) $taxation->taxation_type ='';
		if(!isset($taxation->zone_namekey)) $taxation->zone_namekey ='';

		if(!empty($taxation->taxation_date_start)){
			$taxation->taxation_date_start=hikashop_getTime($taxation->taxation_date_start);
		}
		if(!empty($taxation->taxation_date_end)){
			$taxation->taxation_date_end=hikashop_getTime($taxation->taxation_date_end);
		}
		if(!empty($taxation->taxation_site_id) && $taxation->taxation_site_id=='[unselected]'){
			$taxation->taxation_site_id = '';
		}

		return $this->save($taxation);
	}

	function save(&$element){
		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$do = true;
		$new = true;
		if(!empty($element->taxation_id)){
			$app->triggerEvent('onBeforeTaxationUpdate', array( &$element, &$do) );
			$new = false;
		}else{
			$app->triggerEvent('onBeforeTaxationCreate', array( &$element, &$do) );
		}
		if(!$do){
			return false;
		}

		$result = parent::save($element);

		if(!$result){
			return false;
		}

		if(!$new){
			$app->triggerEvent('onAfterTaxationUpdate', array( &$element) );
		}else{
			$element->taxation_id = $result;
			$app->triggerEvent('onAfterTaxationCreate', array( &$element) );
		}
		return $result;
	}

	function delete(&$elements) {

		JPluginHelper::importPlugin( 'hikashop' );
		$app = JFactory::getApplication();
		$do=true;
		$app->triggerEvent( 'onBeforeTaxationDelete', array( & $elements, & $do) );
		if(!$do){
			return false;
		}

		$status = parent::delete($elements);

		if($status){
			$app->triggerEvent( 'onAfterTaxationDelete', array( & $elements ) );
		}
		return $status;
	}
}
