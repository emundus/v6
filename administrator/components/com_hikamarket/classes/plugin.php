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
class hikamarketPluginClass extends hikamarketClass {
	protected $tables = array('shop.plugin');
	protected $pkeys = array('plugin_id');
	protected $toggle = array('plugin_published' => 'plugin_id');
	protected $toggleAcl = array('plugin' => 'plugin_published');
	protected $deleteToggle = array('shop.plugin' => array('plugin_type', 'plugin_id'));

	public function  __construct($config = array()) {
		return parent::__construct($config);
	}

	public function get($id, $default = '') {
		$ret = parent::get($id);
		if(!empty($ret->plugin_params))
			$ret->plugin_params = hikamarket::unserialize($ret->plugin_params);
		return $ret;
	}

	public function frontSaveForm($task = '', $acl = true) {
		$app = JFactory::getApplication();
		$config = hikamarket::config();
		$shopConfig = hikamarket::config(false);
		$plugin_id = hikamarket::getCID('plugin_id');
		$vendor_id = hikamarket::loadVendor(false, false);

		$plugin_name = hikaInput::get()->getCmd('name', '');
		if(empty($plugin_name))
			return false;

		$plugin_type = hikaInput::get()->getCmd('plugin_type', '');
		if(empty($plugin_type) || !in_array($plugin_type, array('payment', 'shipping', 'generic')))
			return false;

		if($plugin_type == 'plugin' || $plugin_type == 'generic')
			$pluginInstance = hikamarket::import('hikashop', $plugin_name);
		else
			$pluginInstance = hikamarket::import('hikashop'.$plugin_type, $plugin_name);

		if(empty($pluginInstance))
			return false;

		$pluginInterfaceClass = null;
		switch($plugin_type) {
			case 'payment':
				$pluginInterfaceClass = hikamarket::get('class.payment');
				break;
			case 'shipping':
				$pluginInterfaceClass = hikamarket::get('class.shipping');
				break;
			case 'generic':
			default:
				$pluginInterfaceClass = hikamarket::get('class.plugin');
				$plugin_type = 'plugin';
				break;
		}

		if(empty($pluginInterfaceClass))
			return false;

		$new = empty($plugin_id);
		$oldPlugin = null;

		if(!$new) {
			if(!hikamarket::acl($plugin_type.'plugin/edit'))
				return false;

			$oldPlugin = $pluginInterfaceClass->get($plugin_id);
			if(empty($oldPlugin))
				return false;

			if($vendor_id > 1 && (int)@$oldPlugin->{$plugin_type.'_vendor_id'} != $vendor_id)
				return false;

		} else if(!hikamarket::acl($plugin_type.'plugin/add'))
			return false;

		$plugin = new stdClass();
		$plugin->{$plugin_type . '_params'} = new stdClass();

		if($vendor_id > 1)
			$plugin->{$plugin_type.'_vendor_id'} = $vendor_id;

		$plugin->{$plugin_type . '_type'} = $plugin_name;
		if(!$new)
			$plugin->{$plugin_type . '_id'} = $plugin_id;

		$formData = hikaInput::get()->get('data', array(), 'array');
		if(empty($formData[$plugin_type]))
			return false;

		if($plugin_type == 'payment' && $vendor_id > 1)
			$plugin->{$plugin_type . '_params'}->payment_vendor_id = (int)$vendor_id;

		$fields = $pluginInterfaceClass->loadConfigurationFields();
		if(empty($fields['main']))
			return false;

		$fields['main'][$plugin_type.'_name'] = array(
			'name' => 'HIKA_NAME',
			'type' => 'input'
		);
		$fields['main'][$plugin_type.'_description'] = array(
			'name' => 'HIKA_DESCRIPTION',
			'type' => 'wysiwyg',
			'format' => 'text'
		);

		foreach($fields['main'] as $k => $v) {
			$key = str_replace(array('params.'.$plugin_type.'_', $plugin_type.'_', '_'), array('', '', '-'), $k);
			if(!hikamarket::acl($plugin_type . 'plugin/edit/' . $key))
				unset($fields['main'][$k]);
		}
		if(!empty($fields['restriction'])) {
			foreach($fields['restriction'] as $k => $v) {
				$key = str_replace(array('params.'.$plugin_type.'_', $plugin_type.'_', '_'), array('', '', '-'), $k);
				if(!hikamarket::acl($plugin_type . 'plugin/edit/restriction/' . $key))
					unset($fields['restriction'][$k]);
			}
		}

		$process_fields = array_merge($fields['main'], $fields['restriction']);

		if(hikamarket::acl($plugin_type . 'plugin/edit/specific') ) {
			if(!empty($pluginInstance->pluginConfig)) {
				foreach($pluginInstance->pluginConfig as $k => $v) {
					$val = array(
						'name' => $v[0],
						'type' => $v[1]
					);
					if($v[1] == 'wysiwyg') {
						$val['format'] = 'wysiwyg';
						$val['form_field'] = $plugin_type . '_params_' . $k;
					}
					$process_fields['params.'.$k] = $val;
				}
			} else {
				foreach($formData[$plugin_type][$plugin_type . '_params'] as $k => $v) {
					$plugin->{$plugin_type . '_params'}->$k = $v;
				}
			}
		}

		$processed = array();

		foreach($process_fields as $fieldName => $field) {
			$params = false;
			$key = $fieldName;
			if(substr($fieldName, 0, 7) == 'params.') {
				$params = true;
				$fieldName = substr($fieldName, 7);
			}

			$data = null;
			if((isset($field['type']) && $field['type'] == 'wysiwyg') || (!empty($field['format']) && $field['format'] == 'text'))
				$data = hikaInput::get()->getRaw( isset($field['form_field']) ? $field['form_field'] : $fieldName, '');
			else if($params)
				$data = @$formData[$plugin_type][$plugin_type . '_params'][$fieldName];
			else
				$data = @$formData[$plugin_type][$fieldName];

			if($data === null)
				$data = '';

			$format = !empty($field['format']) ? $field['format'] : 'auto';
			$data = $this->parseData($data, $format);

			if($params)
				$plugin->{$plugin_type . '_params'}->$fieldName = $data;
			else
				$plugin->$fieldName = $data;

			$processed[$key] = true;

			if(!empty($field['link'])) {
				$linkName = $field['link'];
				$key = $linkName;
				if($params) {
					$key = 'params.' . $key;
				} elseif(substr($linkName, 0, 7) == 'params.') {
					$params = true;
					$linkName = substr($linkName, 7);
				}

				if(empty($processed[$key])) {
					if($params)
						$data = @$formData[$plugin_type][$plugin_type . '_params'][$linkName];
					else
						$data = @$formData[$plugin_type][$linkName];

					$format = !empty($field['linkformat']) ? $field['linkformat'] : 'auto';
					$data = $this->parseData($data, $format);

					if($params)
						$plugin->{$plugin_type . '_params'}->$linkName = $data;
					else
						$plugin->$linkName = $data;

					$processed[$key] = true;
				}
			}
		}

		if(hikamarket::acl($plugin_type . 'plugin/edit/specific') && empty($pluginInstance->pluginConfig)) {
			foreach($fields['main'] as $k => $v) {
				if(substr($k, 0, 7) != 'params.')
					continue;
				$k = substr($k, 7);
				$key = str_replace(array($plugin_type.'_', '_'), array('', '-'), $k);
				if(!hikamarket::acl($plugin_type . 'plugin/edit/' . $key))
					unset($plugin->{$plugin_type . '_params'}->$k);
			}
			if(!empty($fields['restriction'])) {
				foreach($fields['restriction'] as $k => $v) {
					if(substr($k, 0, 7) != 'params.')
						continue;
					$k = substr($k, 7);
					$key = str_replace(array($plugin_type.'_', '_'), array('', '-'), $k);
					if(!hikamarket::acl($plugin_type . 'plugin/edit/restriction/' . $key))
						unset($plugin->{$plugin_type . '_params'}->$k);
				}
			}
		}

		if($plugin_type == 'payment') {
			if(!isset($plugin->payment_shipping_methods))
				$plugin->payment_shipping_methods = array();
			if(!isset($plugin->payment_currency))
				$plugin->payment_currency = array();
		} elseif($plugin_type == 'shipping') {
			if(!isset($plugin->shipping_currency))
				$plugin->shipping_currency = array();
		}

		$status = true;

		$function = 'on'.ucfirst($plugin_type).'ConfigurationSave';
		if(method_exists($pluginInstance, $function))
			$pluginInstance->$function($plugin);

		if(!empty($fields['main'][$plugin_type.'_name']) && empty($plugin->{$plugin_type.'_name'})) {
			$status = false;

		}

		if($status)
			$status = $pluginInterfaceClass->save($plugin);

		if(!$status) {
			hikaInput::get()->set('fail', $plugin);
		} else {
			if(!HIKASHOP_J30)
				$app->enqueueMessage(JText::_('HIKASHOP_SUCC_SAVED'), 'success');
			else
				$app->enqueueMessage(JText::_('HIKASHOP_SUCC_SAVED'));

			if($new)
				hikaInput::get()->set($plugin_type.'_id', $status);
		}

		return $status;
	}

	public function parseData($data, $format) {
		if(empty($this->safeHtmlFilter))
			$this->safeHtmlFilter = JFilterInput::getInstance(array(), array(), 1, 1);

		switch($format) {
			case 'text':
			case 'wysiwyg':
				if(is_array($data))
					$data = reset($data);
				$data = $this->safeHtmlFilter->clean(trim($data), 'string');
				break;

			case 'string':
				if(is_array($data))
					$data = reset($data);
				$data = $this->safeHtmlFilter->clean(strip_tags(trim($data)), 'string');
				break;

			case 'boolean':
			case 'int':
				if(is_array($data))
					$data = reset($data);
				$data = trim($data);
				if($format == 'boolean' || $data !== '')
					$data = (int)$data;
				break;

			case 'float':
				if(is_array($data))
					$data = reset($data);
				$data = trim($data);
				if($data !== '')
					$data = (float)hikamarket::toFloat($data);
				break;

			case 'arrayString':
				if(!is_array($data)) {
					if($data !== '')
						$data = array($data);
					else
						$data = array();
				}
				foreach($data as &$d) {
					$d = $this->safeHtmlFilter->clean(strip_tags(trim($d)), 'string');
				}
				unset($d);
				$data = implode(',', $data);
				break;

			case 'acl':
				if(empty($data) || $data == 'all') {
					$data = 'all';
					break;
				}
			case 'arrayInt':
				if(!is_array($data)) {
					if($data !== '')
						$data = array($data);
					else
						$data = array();
				}
				foreach($data as &$d) {
					$d = (int)trim($d);
				}
				unset($d);
				$data = implode(',', $data);
				break;

			case 'auto':
			default:
				if(is_array($data)) {
					foreach($data as &$d) {
						$d = $this->safeHtmlFilter->clean(strip_tags(trim($d)), 'string');
					}
					unset($d);
				} else
					$data = $this->safeHtmlFilter->clean(strip_tags(trim($data)), 'string');
				break;
		}

		return $data;
	}

	public function save(&$plugin) {
		JPluginHelper::importPlugin('hikamarket');
		$pluginClass = hikamarket::get('shop.class.plugin');
		$status = $pluginClass->save($plugin);
		return $status;
	}

	public function &getNameboxData($typeConfig, &$fullLoad, $mode, $value, $search, $options) {
		$ret = array(
			0 => array(),
			1 => array()
		);

		if(isset($typeConfig['params']['type']) && $typeConfig['params']['type'] == 'images') {
			$image_type = @$options['type'];
			if(!in_array($image_type, array('shipping', 'payment')))
				return $ret;

			$path = HIKASHOP_MEDIA.'images'.DS.$image_type.DS;
			jimport('joomla.filesystem.folder');
			$images = JFolder::files($path);
			$rows = array();
			foreach($images as $image){
				$parts = explode('.',$image);
				$row = new stdClass();
				$row->ext = array_pop($parts);
				if(!in_array(strtolower($row->ext), array('gif','png','jpg','jpeg','svg')))
					continue;
				$row->id = implode('.',$parts);
				$row->image_name = str_replace('_', ' ', $row->id);
				$row->image_file = $image;
				$row->image_url = '<img src="'.HIKASHOP_IMAGES .$image_type.'/'. $row->image_file.'" />';
				$rows[$row->id] = $row;
			}

			if(!empty($value)) {
				if(is_string($value))
					$value = explode(',', $value);

				foreach($value as $v) {
					if(isset($rows[$v]))
						$ret[1][$v] = $rows[$v];
				}
			}

			if(!empty($rows))
				$ret[0] = $rows;
		}

		return $ret;
	}

	public function toggleId($task, $value = null) {
		if($value !== null) {
			$app = JFactory::getApplication();
			if(!hikamarket::isAdmin() && ((int)$value == 0 || empty($this->toggle[$task]) || !hikamarket::acl('genericplugin/edit/'.$task) || !hikamarket::isVendorPlugin((int)$value, 'plugin') ))
				return false;
		}
		if(!empty($this->toggle[$task]))
			return $this->toggle[$task];
		return false;
	}

	public function toggleDelete($value1 = '', $value2 = '') {
		$app = JFactory::getApplication();
		if(!hikamarket::isAdmin() && ((int)$value1 == 0 || !hikamarket::acl('genericplugin/delete') || !hikamarket::isVendorPlugin((int)$value1, 'plugin')))
			return false;
		if(!empty($this->deleteToggle))
			return $this->deleteToggle;
		return false;
	}
}
