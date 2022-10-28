<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class characteristicMarketController extends hikamarketController {
	protected $rights = array(
		'display' => array('show','listing','findlist'),
		'add' => array('add'),
		'edit' => array('edit',),
		'modify' => array('apply','save','addcharacteristic'),
		'delete' => array('delete')
	);

	protected $ordering = array(
		'type' => 'characteristic',
		'pkey' => 'characteristic_id',
		'table' => 'characteristic',
		'groupMap' => 'characteristic_parent_id',
		'orderingMap' => 'characteristic_ordering',
		'groupVal' => 0
	);

	protected $type = 'characteristic';
	protected $config = null;

	public function __construct($config = array(), $skip = false) {
		parent::__construct($config, $skip);
		if(!$skip)
			$this->registerDefaultTask('listing');
		$this->config = hikamarket::config();
	}

	public function listing() {
		if(!hikamarket::loginVendor())
			return false;
		if(!$this->config->get('frontend_edition',0))
			return false;
		if(!hikamarket::acl('characteristic/listing'))
			return hikamarket::deny('vendor', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_CHARACTERISTIC_LISTING')));
		hikaInput::get()->set('layout', 'listing');
		return parent::display();
	}

	public function show() {
		if(!hikamarket::loginVendor())
			return false;
		if(!$this->config->get('frontend_edition',0))
			return false;
		if(!hikamarket::acl('characteristic/show'))
			return hikamarket::deny('vendor', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_CHARACTERISTIC_SHOWING')));

		$vendor_id = hikamarket::loadVendor(false);
		$characteristic_id = hikamarket::getCID('characteristic_id');
		$characteristic = null;
		if($characteristic_id > 0) {
			$characteristicClass = hikamarket::get('class.characteristic');
			$characteristic = $characteristicClass->get($characteristic_id);
		}
		if(!empty($characteristic_id) && (empty($characteristic) || ($characteristic->characteristic_vendor_id > 0 && $characteristic->characteristic_vendor_id != $vendor_id)))
			return hikamarket::deny('characteristic', JText::sprintf('HIKAM_ACTION_ERROR', JText::_('HIKAM_WRONG_DATA')));

		return parent::show();
	}

	public function edit() {
		return $this->show();
	}

	public function add() {
		return $this->show();
	}

	public function store() {
		if(!hikamarket::loginVendor())
			return false;
		if( !$this->config->get('frontend_edition',0) )
			return false;

		$characteristic_id = hikamarket::getCID('characteristic_id');
		if(!empty($characteristic_id) && !hikamarket::acl('characteristic/edit') )
			return hikamarket::deny('characteristic', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_CHARACTERISTIC_EDIT')));
		if(empty($characteristic_id) && !hikamarket::acl('characteristic/add') )
			return hikamarket::deny('characteristic', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_CHARACTERISTIC_ADD')));

		JSession::checkToken() || die('Invalid Token');
		$tmpl = hikaInput::get()->getCmd('tmpl', '');

		$characteristicClass = hikamarket::get('class.characteristic');
		if($characteristicClass === null)
			return false;
		$status = $characteristicClass->frontSaveForm();

		if($tmpl == 'json') {
			if(!empty($status))
				echo '{id:' . $status . '}';
			else
				echo '{err:"failed"}';
			exit;
		}

		$app = JFactory::getApplication();
		if($status) {
			$app->enqueueMessage(JText::_('HIKASHOP_SUCC_SAVED'), 'success');
			hikaInput::get()->set('cid', $status);
			hikaInput::get()->set('fail', null);
		} else {
			$app->enqueueMessage(JText::_('ERROR_SAVING'), 'error');
			if(!empty($characteristicClass->errors)) {
				foreach($characteristicClass->errors as $err) {
					$app->enqueueMessage($err, 'error');
				}
			}
		}
		return $status;
	}

	public function addCharacteristic() {
		if(!hikamarket::loginVendor())
			return false;
		if(!$this->config->get('frontend_edition',0))
			return false;

		JSession::checkToken('request') || die('Invalid Token');
		$tmpl = hikaInput::get()->getCmd('tmpl', '');

		$characteristic_parent_id = hikaInput::get()->getInt('characteristic_parent_id', 0);
		$characteristic_type = hikaInput::get()->getCmd('characteristic_type', '');

		$value = hikaInput::get()->getString('value', '');
		if(empty($value))
			return hikamarket::deny('vendor', JText::sprintf('HIKAM_ACTION_ERROR', JText::_('HIKAM_WRONG_DATA')));

		$value = trim($value);
		$vendor_id = hikamarket::loadVendor(false);
		if($vendor_id == 1)
			$vendor_id = 0;

		$ret = false;

		if($characteristic_type == 'value') {
			if(!hikamarket::acl('characteristic/values/add'))
				return hikamarket::deny('vendor', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_CHARACTERISTIC_ADD')));

			if($characteristic_parent_id <= 0 || (!hikamarket::isVendorCharacteristic($characteristic_parent_id) && !hikamarket::isVendorCharacteristic($characteristic_parent_id, 0, 0)))
				return hikamarket::deny('vendor', JText::sprintf('HIKAM_ACTION_ERROR', JText::_('HIKAM_WRONG_DATA')));

			$characteristicClass = hikamarket::get('class.characteristic');

			$characteristic_vendor_id = $vendor_id;
			if($characteristic_vendor_id == 0 && hikamarket::acl('characteristic/values/edit/vendor'))
				$characteristic_vendor_id = (int)hikaInput::get()->getInt('characteristic_vendor_id', 0);

			if($characteristicClass->findValue($value, $characteristic_parent_id, $characteristic_vendor_id) > 0)
				return hikamarket::deny('vendor', JText::sprintf('HIKAM_ACTION_ERROR', JText::_('HIKAM_WRONG_DATA')));

			$element = new stdClass();
			$element->characteristic_parent_id = $characteristic_parent_id;
			$element->characteristic_value = $value;
			$element->characteristic_vendor_id = $characteristic_vendor_id;

			$ret = $characteristicClass->save($element);
		} else {
			if(!hikamarket::acl('characteristic/add'))
				return hikamarket::deny('vendor', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_CHARACTERISTIC_ADD')));

			$characteristicClass = hikamarket::get('class.characteristic');

			$characteristic_vendor_id = $vendor_id;
			if($characteristic_vendor_id == 0 && hikamarket::acl('characteristic/edit/vendor'))
				$characteristic_vendor_id = (int)hikaInput::get()->getInt('characteristic_vendor_id', 0);

			if($characteristicClass->findValue($value, 0, $characteristic_vendor_id) > 0)
				return hikamarket::deny('vendor', JText::sprintf('HIKAM_ACTION_ERROR', JText::_('HIKAM_WRONG_DATA')));

			$element = new stdClass();
			$element->characteristic_parent_id = 0;
			$element->characteristic_value = $value;
			$element->characteristic_alias = strtolower($value);
			$element->characteristic_vendor_id = $characteristic_vendor_id;

			$ret = $characteristicClass->save($element);
		}

		if($tmpl == 'json') {
			while(ob_get_level())
				@ob_end_clean();

			if(!empty($ret)) {
				$data = array(
					'value' => $ret,
					'name' => $value
				);
				if($vendor_id == 0) {
					$data['vendor_id'] = $element->characteristic_vendor_id;
					if($element->characteristic_vendor_id > 0) {
						$vendorClass = hikamarket::get('class.vendor');
						$vendor = $vendorClass->get($element->characteristic_vendor_id);
						$data['vendor'] = $vendor->vendor_name;
					}
				}
				echo json_encode($data);
			} else
				echo '{err:"failed"}';
			exit;
		}

		hikaInput::get()->set('layout', 'listing');
		return parent::display();
	}

	public function findList() {
		if(!hikamarket::loginVendor())
			return false;
		if(!$this->config->get('frontend_edition',0))
			return false;

		$search = hikaInput::get()->getString('search', '');
		$type = hikaInput::get()->getString('characteristic_type', '');
		$characteristic_parent_id = hikaInput::get()->getInt('characteristic_parent_id', 0);

		$options = array(
			'vendor' => hikamarket::loadVendor(false)
		);

		if($type == 'value') {
			if($characteristic_parent_id <= 0 || (!hikamarket::isVendorCharacteristic($characteristic_parent_id) && !hikamarket::isVendorCharacteristic($characteristic_parent_id,0,0)))
				return hikamarket::deny('vendor', JText::sprintf('HIKAM_ACTION_ERROR', JText::_('HIKAM_WRONG_DATA')), 'error', 'json');

			$type = 'characteristic_value';
			$options['url_params'] = array('ID' => $characteristic_parent_id);
		} else
			$type = 'characteristic';

		$nameboxType = hikamarket::get('type.namebox');
		$elements = $nameboxType->getValues($search, $type, $options);

		while(ob_get_level())
			@ob_end_clean();
		echo json_encode($elements);
		exit;
	}
}
