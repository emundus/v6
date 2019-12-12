<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.2.2
 * @author	hikashop.com
 * @copyright	(C) 2010-2019 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class hikashopTaxClass extends hikashopClass{
	var $tables = array('tax');
	var $namekeys = array('tax_namekey');

	function get($id,$default=null){
		$query='SELECT * FROM '.hikashop_table('tax').' WHERE tax_namekey='.$this->database->Quote($id).' LIMIT 1';
		$this->database->setQuery($query);
		return $this->database->loadObject();
	}
	function delete(&$ids){
		foreach($ids as $k => $id){
			$ids[$k] = $this->database->Quote($id);
		}
		$query='DELETE FROM '.hikashop_table('tax').' WHERE tax_namekey IN ('.implode(',',$ids).')';
		$this->database->setQuery($query);
		return $this->database->execute();
	}

	function saveForm(){
		$tax = new stdClass();
		$formData = hikaInput::get()->get('data', array(), 'array');
		jimport('joomla.filter.filterinput');
		$safeHtmlFilter = & JFilterInput::getInstance(null, null, 1, 1);
		foreach($formData['tax'] as $column => $value){
			hikashop_secureField($column);
			if($column=='tax_rate'){
				$tax->$column = ((float)(strip_tags(str_replace('"','',$value))))/100.0;
			}else{
				$tax->$column = $safeHtmlFilter->clean(strip_tags($value), 'string');
			}
		}
		if(hikaInput::get()->getVar('task')!='save2new') hikaInput::get()->set('tax_namekey',$tax->tax_namekey);
		return $this->save($tax);
	}

	function save(&$element){
		$old = $this->get($element->tax_namekey);
		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$do = true;
		if(!empty($old)){
			$element->old =& $old;
			$app->triggerEvent('onBeforeTaxUpdate', array( &$element, &$do) );
		}else{
			$app->triggerEvent('onBeforeTaxCreate', array( &$element, &$do) );
		}
		if(!$do){
			return false;
		}

		if(!empty($old)){
			$result = parent::save($element);
		}else{
			$this->database->setQuery($this->_getInsert($this->getTable(),$element));
			$result = $this->database->execute();
		}
		if(!empty($old)){
			$app->triggerEvent('onAfterTaxUpdate', array( &$element) );
		}else{
			$app->triggerEvent('onAfterTaxCreate', array( &$element) );
		}
		return $result;
	}
}
