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
class hikashopBannerClass extends hikashopClass{
	var $tables = array('banner');
	var $pkeys = array('banner_id');
	var $toggle = array('banner_published'=>'banner_id');

	function saveForm(){
		$element = new stdClass();
		$element->banner_id = hikashop_getCID('banner_id');
		$formData = hikaInput::get()->get('data', array(), 'array');
		jimport('joomla.filter.filterinput');
		$safeHtmlFilter = & JFilterInput::getInstance(array(), array(), 1, 1);
		foreach($formData['banner'] as $column => $value){
			hikashop_secureField($column);
			$element->$column = $safeHtmlFilter->clean($value);
			if($column!='banner_comment'){
				$element->$column = strip_tags($element->$column);
			}
		}
		$translationHelper = hikashop_get('helper.translation');
		$translationHelper->getTranslations($element);
		$result = $this->save($element);
		if($result){
			$translationHelper->handleTranslations('banner',$result,$element);
		}
		return $result;
	}

	function save(&$element){
		$do = true;
		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$new = empty($element->banner_id);
		if($new) {
			$app->triggerEvent('onBeforeBannerCreate', array( &$element, &$do ));
		} else {
			$app->triggerEvent('onBeforeBannerUpdate', array( &$element, &$do ));
		}

		if(!$do)
			return false;

		$status = parent::save($element);
		if(!$status)
			return $status;

		if($new) {
			$app->triggerEvent('onAfterBannerCreate', array( &$element ));
		} else {
			$app->triggerEvent('onAfterBannerUpdate', array( &$element ));
		}

		if(!$status){
			return false;
		}
		if($new){
			$element->banner_id = $status;
			$orderHelper = hikashop_get('helper.order');
			$orderHelper->pkey = 'banner_id';
			$orderHelper->table = 'banner';
			$orderHelper->orderingMap = 'banner_ordering';
			$orderHelper->reOrder();
		}
		return $status;
	}
	public function delete(&$elements) {
		$do = true;
		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$app->triggerEvent('onBeforeBannerDelete', array(&$elements, &$do));

		if(!$do)
			return false;

		$status = parent::delete($elements);
		if($status) {
			$app->triggerEvent('onAfterBannerDelete', array(&$elements));
		}
		return $status;
	}

}
