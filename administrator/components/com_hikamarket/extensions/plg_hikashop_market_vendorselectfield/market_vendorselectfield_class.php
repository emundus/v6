<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class fieldOpt_market_vendorselect_type {
	public function show($value) {
		$v = array(
			JHTML::_('select.option', '0', JText::_('VENDOR_SELECTION_TYPE_DROPDOWN')),
			JHTML::_('select.option', '1', JText::_('VENDOR_SELECTION_TYPE_POPUP')),
			JHTML::_('select.option', '2', JText::_('VENDOR_SELECTION_TYPE_NAMEBOX')),
		);
		return JHTML::_('select.genericlist', $v, 'field_options[market_vendorselect_type]', '', 'value', 'text', $value);
	}
}

class hikashopMarket_vendorselectfield {

	public $prefix = null;
	public $suffix = null;
	public $excludeValue = null;
	public $report = null;
	public $parent = null;

	public function __construct(&$obj) {
		$this->prefix = $obj->prefix;
		$this->suffix = $obj->suffix;
		$this->excludeValue =& $obj->excludeValue;
		$this->report = @$obj->report;
		$this->parent =& $obj;
	}

	private function initMarket() {
		static $init = null;
		if($init !== null)
			return $init;

		$init = defined('HIKAMARKET_COMPONENT');
		if(!$init) {
			$filename = rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikamarket'.DS.'helpers'.DS.'helper.php';
			if(file_exists($filename)) {
				include_once($filename);
				$init = defined('HIKAMARKET_COMPONENT');
			}
		}
		return $init;
	}

	public function getFieldName($field) {
		return '<label for="' . $this->prefix . $field->field_namekey . $this->suffix.'">' . $this->trans($field->field_realname) . '</label>';
	}

	public function trans($name) {
		$val = preg_replace('#[^a-z0-9]#i', '_', strtoupper($name));
		$trans = JText::_($val);
		if($val == $trans)
			return $name;
		return $trans;
	}

	private function initField(&$field) {
		if(isset($field->show_myself))
			return;

		if($field->field_table == 'item' && isset($field->product_id)) {
			$productClass = hikamarket::get('class.product');
			$product = $productClass->get( (int)$field->product_id );
			if((int)$product->product_vendor_id > 0) {
				$field->show_myself = false;
				$field->original_vendor_id = (int)$product->product_vendor_id;
				return;
			}
		}
		$field->show_myself = true;
		return;
	}

	public function show(&$field, $value) {
		if(!$this->initMarket())
			return '';

		if(!empty($field->field_value) && !is_array($field->field_value)) {
			$field->field_value = $this->parent->explodeValues($field->field_value);
		}
		if(isset($field->field_value[$value])) {
			$value = $field->field_value[$value]->value;
		}

		$vendorClass = hikamarket::get('class.vendor');
		$vendor = $vendorClass->get( (int)$value );
		if(!empty($vendor)) {
			$value = $vendor->vendor_name;
		}

		return $this->trans($value);
	}

	public function display($field, $value, $map, $inside, $options = '', $test = false) {
		if(!$this->initMarket())
			return '';

		$this->initField($field);
		if(!$field->show_myself) {
			$vendorClass = hikamarket::get('class.vendor');
			$vendor = $vendorClass->get( (int)$field->original_vendor_id );
			if(!empty($vendor)) {
				$value = $vendor->vendor_name;
			}

			return $this->trans($value);
		}

		$show_type = 2;
		if(isset($field->field_options['market_vendorselect_type']))
			$show_type = (int)$field->field_options['market_vendorselect_type'];

		$please_select = !empty($field->field_options['pleaseselect']);

		$app = JFactory::getApplication();
		if(in_array($field->field_table, array('item', 'order')) && empty($value) && !hikamarket::isAdmin()) {
			$config = hikamarket::config();
			$preferred = $config->get('preferred_vendor_select_custom_field', '');
			if(!empty($preferred)) {
				$user = hikamarket::loadUser(true);
				if(isset($user->$preferred))
					$value = $user->$preferred;
			}
		}

		if($show_type == 0) {
			$vendorSelectionType = hikamarket::get('type.vendor_selection');
			$ret = $vendorSelectionType->displayDropdown($map, $value, $please_select, $options, $this->prefix . @$field->field_namekey . $this->suffix);

		} else if($show_type == 1) {
			$vendorSelectionType = hikamarket::get('type.vendor_selection');
			$ret = $vendorSelectionType->display($map, $value, false);

		} else {
			$nameboxType = hikamarket::get('type.namebox');
			$ret = $nameboxType->display(
				$map,
				$value,
				hikamarketNameboxType::NAMEBOX_SINGLE,
				'vendor',
				array(
					'id' => $this->prefix . @$field->field_namekey . $this->suffix,
					'delete' => (hikamarket::isAdmin() || empty($field->field_required)),
					'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>'
				)
			);
		}

		return $ret;
	}

	public function JSCheck(&$oneField,&$requiredFields,&$validMessages,&$values){
		if(empty($oneField->field_required))
			return;

		$requiredFields[] = $oneField->field_namekey;
		if(!empty($oneField->field_options['errormessage'])) {
			$validMessages[] = addslashes($this->trans($oneField->field_options['errormessage']));
		} else {
			$validMessages[] = addslashes(JText::sprintf('FIELD_VALID', $this->trans($oneField->field_realname)));
		}
	}

	public function check(&$field, &$value, $oldvalue) {
		if(is_string($value))
			$value = trim($value);

		if($value == '0')
			$value = '';

		if(!$field->field_required || is_array($value) || strlen($value) || ($value === null && strlen($oldvalue)))
			return true;

		$this->initField($field);
		if(!$field->show_myself)
			return true;

		if($this->report) {
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::sprintf('PLEASE_FILL_THE_FIELD', $this->trans($field->field_realname)));
		}
		return false;
	}
}
