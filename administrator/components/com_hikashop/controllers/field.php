<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2016 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php

class FieldController extends hikashopController{
	var $pkey = 'field_id';
	var $table = 'field';
	var $groupMap = '';
	var $groupVal = '';
	var $orderingMap ='field_ordering';

	function __construct($config = array()){
		parent::__construct($config);
		$this->modify_views[]='state';
		$this->modify_views[]='parentfield';
	}

	function store($new=false){
		JRequest::checkToken() || die( 'Invalid Token' );

		$app = JFactory::getApplication();

		$fieldClass = hikashop_get('class.field');
		$status = $fieldClass->saveForm();
		if($status){
			if(!HIKASHOP_J30)
				$app->enqueueMessage(JText::_( 'HIKASHOP_SUCC_SAVED' ), 'success');
			else
				$app->enqueueMessage(JText::_( 'HIKASHOP_SUCC_SAVED' ));

			$translationHelper = hikashop_get('helper.translation');
			if($translationHelper->isMulti(true)){
				$updateHelper = hikashop_get('helper.update');
				$updateHelper->addJoomfishElements(false);
			}
		}else{
			$app->enqueueMessage(JText::_( 'ERROR_SAVING' ), 'error');
			if(!empty($fieldClass->errors)){
				foreach($fieldClass->errors as $oneError){
					$app->enqueueMessage($oneError, 'error');
				}
			}
		}
	}

	function remove(){
		JRequest::checkToken() || die( 'Invalid Token' );

		$cids = JRequest::getVar( 'cid', array(), '', 'array' );

		$fieldClass = hikashop_get('class.field');
		$num = $fieldClass->delete($cids);

		if($num){
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::sprintf('SUCC_DELETE_ELEMENTS',$num), 'message');
		}

		return $this->listing();
	}
	function state(){
		JRequest::setVar( 'layout', 'state' );
		return parent::display();
	}

	function parentfield(){
		$type = JRequest::getVar('type');
		$namekey = JRequest::getVar('namekey');
		$value = JRequest::getString('value');
		if(!empty($namekey) && !empty($type)){
			$fieldClass = hikashop_get('class.field');
			$field = $fieldClass->getField($namekey,$type);
			if($field->field_type == 'zone' && !empty($field->field_options['zone_type']) && $field->field_options['zone_type'] == 'state'){
				$null = null;
				$fields = $fieldClass->getFields('',$null,$type);
				$countryField = null;
				foreach($fields as $brotherField){
					if($brotherField->field_type == 'zone' && !empty($brotherField->field_options['zone_type']) && $brotherField->field_options['zone_type'] == 'country'){
						$countryField = $brotherField;
						break;
					}
				}
				if($countryField){
					$baseUrl = JURI::base().'index.php?option=com_hikashop&ctrl=field&task=state&tmpl=component';
					$currentUrl = strtolower(hikashop_currentUrl());
					if(substr($currentUrl, 0, 8) == 'https://') {
						$domain = substr($currentUrl, 0, strpos($currentUrl, '/', 9));
					} else {
						$domain = substr($currentUrl, 0, strpos($currentUrl, '/', 8));
					}
					if(substr($baseUrl, 0, 8) == 'https://') {
						$baseUrl = $domain . substr($baseUrl, strpos($baseUrl, '/', 9));
					} else {
						$baseUrl = $domain . substr($baseUrl, strpos($baseUrl, '/', 8));
					}
					$countryField->field_url = $baseUrl . '&';
					echo $fieldClass->display($countryField,$countryField->field_default,'field_options_parent_value',false,'',true);
				}
			}
			echo $fieldClass->display($field,$value,'field_options[parent_value]',false,'',true);
		}
		exit;
	}

}
