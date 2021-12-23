<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.4.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2020 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class hikashopWaitlistClass extends hikashopClass{
	var $tables = array('waitlist');
	var $pkeys = array('waitlist_id');

	function get($cid=0,$default=''){
		$element = parent::get($cid,$default);
		if($element){
			$productClass = hikashop_get('class.product');
			$product = $productClass->get($element->product_id);
			if($product){
				foreach(get_object_vars($product) as $k => $v){
					$element->$k = $v;
				}
			}
		}
		return $element;
	}

	function saveForm(){
		$element = new stdClass();
		$element->waitlist_id = hikashop_getCID('waitlist_id');
		$formData = hikaInput::get()->get('data', array(), 'array');
		jimport('joomla.filter.filterinput');
		$safeHtmlFilter = JFilterInput::getInstance(array(), array(), 1, 1);
		foreach($formData['waitlist'] as $column => $value){
			hikashop_secureField($column);
			$element->$column = $safeHtmlFilter->clean(strip_tags($value), 'string');
		}
		if(!empty($element->date)){
			$element->date=hikashop_getTime($element->date);
		}
		$result = $this->save($element);
		return $result;
	}

	function save(&$element){
		if(empty($element->waitlist_id) && empty($element->date)){
			$element->date = time();
		}
		$status = parent::save($element);
		return $status;
	}
}
