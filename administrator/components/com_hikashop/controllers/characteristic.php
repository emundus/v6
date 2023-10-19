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
class CharacteristicController extends hikashopController{
	var $type='characteristic';
	function __construct(){
		parent::__construct();
		$this->display[] = 'selectcharacteristic';
		$this->display[] = 'usecharacteristic';
		$this->display[] = 'findList';
		$this->modify_views[] = 'editpopup';
		$this->modify[] = 'addcharacteristic';
		$this->modify[] = 'addcharacteristic_ajax';
	}

	function addcharacteristic(){
		$characteristicClass = hikashop_get('class.characteristic');
		$status = $characteristicClass->saveForm();
		hikaInput::get()->set('cid',$status);
		hikaInput::get()->set( 'layout', 'addcharacteristic'  );
		return parent::display();
	}

	function editpopup(){
		hikaInput::get()->set( 'layout', 'editpopup'  );
		return parent::display();
	}

	function selectcharacteristic(){
		hikaInput::get()->set( 'layout', 'selectcharacteristic'  );
		return parent::display();
	}
	function usecharacteristic(){
		hikaInput::get()->set( 'layout', 'usecharacteristic'  );
		return parent::display();
	}

	public function addcharacteristic_ajax() {
		JSession::checkToken('request') || jexit('Invalid Token');
		$tmpl = hikaInput::get()->getCmd('tmpl', '');

		$characteristic_parent_id = hikaInput::get()->getInt('characteristic_parent_id', 0);
		$characteristic_type = hikaInput::get()->getCmd('characteristic_type', '');

		$value = hikaInput::get()->getString('value', '');
		if(empty($value))
			return false;

		$value = trim($value);
		$vendor_id = 0;
		$ret = false;

		if($characteristic_type == 'value') {
			if(!hikashop_acl('characteristic/values/add'))
				return false;

			if($characteristic_parent_id <= 0)
				return false;

			$characteristicClass = hikashop_get('class.characteristic');

			$characteristic_vendor_id = $vendor_id;
			if($characteristic_vendor_id == 0 && hikashop_acl('characteristic/values/edit/vendor'))
				$characteristic_vendor_id = (int)hikaInput::get()->getInt('characteristic_vendor_id', 0);

			if($characteristicClass->findValue($value, $characteristic_parent_id, $characteristic_vendor_id) > 0)
				return false; // hikamarket::deny('vendor', JText::sprintf('HIKAM_ACTION_ERROR', JText::_('HIKAM_WRONG_DATA')));

			$element = new stdClass();
			$element->characteristic_parent_id = $characteristic_parent_id;
			$element->characteristic_value = $value;
			if(!empty($characteristic_vendor_id))
				$element->characteristic_vendor_id = $characteristic_vendor_id;

			$ret = $characteristicClass->save($element);
		} else {
			if(!hikashop_acl('characteristic/add'))
				return false;

			$characteristicClass = hikashop_get('class.characteristic');

			$characteristic_vendor_id = $vendor_id;
			if($characteristic_vendor_id == 0 && hikashop_acl('characteristic/edit/vendor'))
				$characteristic_vendor_id = (int)hikaInput::get()->getInt('characteristic_vendor_id', 0);

			if($characteristicClass->findValue($value, 0, $characteristic_vendor_id) > 0)
				return false;

			$element = new stdClass();
			$element->characteristic_parent_id = 0;
			$element->characteristic_value = $value;
			$element->characteristic_alias = strtolower($value);

			$ret = $characteristicClass->save($element);
		}

		if($tmpl == 'json') {
			if(!empty($ret)) {
				$data = array(
					'value' => $ret,
					'name' => $value
				);
				echo json_encode($data);
			} else
				echo '{err:"failed"}';
			exit;
		}

		hikaInput::get()->set('layout', 'listing');
		return parent::display();
	}

	public function findList() {
		$search = hikaInput::get()->getVar('search', '');
		$type = hikaInput::get()->getVar('characteristic_type', '');
		$characteristic_parent_id = hikaInput::get()->getInt('characteristic_parent_id', 0);

		$options = array();

		if($type == 'value') {
			if($characteristic_parent_id <= 0)
				return false;

			$type = 'characteristic_value';
			$options['url_params'] = array('ID' => $characteristic_parent_id);
		} else
			$type = 'characteristic';

		$nameboxType = hikashop_get('type.namebox');
		$elements = $nameboxType->getValues($search, $type, $options);
		echo json_encode($elements);
		exit;
	}
}
