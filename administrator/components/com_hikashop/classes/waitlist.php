<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
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
		$new = empty($element->waitlist_id);
		if($new && empty($element->date)){
			$element->date = time();
		}

		$do = true;
		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		if($new) {
			$app->triggerEvent('onBeforeWaitlistCreate', array( &$element, &$do ));
		} else {
			$app->triggerEvent('onBeforeWaitlistUpdate', array( &$element, &$do ));
		}

		if(!$do)
			return false;

		$status = parent::save($element);
		if(!$status)
			return $status;

		if($new) {
			$app->triggerEvent('onAfterWaitlistCreate', array( &$element ));
		} else {
			$app->triggerEvent('onAfterWaitlistUpdate', array( &$element ));
		}
		return $status;
	}
	public function delete(&$elements) {
		$do = true;
		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$app->triggerEvent('onBeforeWaitlistDelete', array(&$elements, &$do));

		if(!$do)
			return false;

		$status = parent::delete($elements);
		if($status) {
			$app->triggerEvent('onAfterWaitlistDelete', array(&$elements));
		}
		return $status;
	}
}
