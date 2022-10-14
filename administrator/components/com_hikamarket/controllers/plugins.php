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
class pluginsMarketController extends hikamarketController {

	protected $type = 'plugins';
	protected $rights = array(
		'display' => array('show','listing','trigger'),
		'add' => array('add'),
		'edit' => array('edit','toggle'),
		'modify' => array('save','apply'),
		'delete' => array('delete')
	);

	public function __construct($config = array()) {
		parent::__construct($config);
		$this->registerDefaultTask('listing');
	}

	public function trigger() {
		$cid = hikaInput::get()->getInt('cid', 0);
		$function = hikaInput::get()->getString('function', '');
		if(empty($cid) || empty($function)){
			return false;
		}
		$pluginsClass = hikamarket::get('class.plugins');
		$plugin = $pluginsClass->get($cid);
		if(empty($plugin)) {
			return false;
		}
		$plugin = hikamarket::import($plugin->folder, $plugin->element);
		if(method_exists($plugin, $function))
			return $plugin->$function();
		return false;
	}


	public function store() {
		$this->plugin = hikaInput::get()->getCmd('name', '');
		$this->plugin_type = hikaInput::get()->getCmd('plugin_type', 'plugin');
		if(empty($this->plugin) || !in_array($this->plugin_type, array('plugin'))) {
			return false;
		}
		$data = hikamarket::import('hikamarket'.$this->plugin_type, $this->plugin);

		$element = null;
		$id = hikamarket::getCID($this->plugin_type.'_id');
		$formData = hikaInput::get()->get('data', array(), 'array');

		$params_name = $this->plugin_type.'_params';
		if(!empty($formData[$this->plugin_type])) {
			$plugin_id = $this->plugin_type.'_id';
			$element->$plugin_id = $id;
			foreach($formData[$this->plugin_type] as $column => $value) {
				hikamarket::secureField($column);
				if(is_array($value)) {
					if($column == $params_name) {
						$element->$params_name = null;
						foreach($formData[$this->plugin_type][$column] as $key=>$val) {
							hikamarket::secureField($key);
							$element->$params_name->$key = strip_tags($val);
						}
					}
				}else{
					$element->$column = strip_tags($value);
				}
			}

			$plugin_description = $this->plugin_type.'_description';
			$plugin_description_data = hikaInput::get()->getRaw($plugin_description, '');
			$element->$plugin_description = $plugin_description_data;
		}
		$function = 'on'.ucfirst($this->plugin_type).'ConfigurationSave';
		if(method_exists($data, $function)) {
			$data->$function($element);
		}

		if(!empty($element)) {
			$pluginClass = hikamarket::get('class.'.$this->plugin_type);
			if(isset($element->$params_name)) {
				$element->$params_name = serialize($element->$params_name);
			}
			$status = $pluginClass->save($element);

			if(!$status) {
				hikaInput::get()->set('fail', $element);
			} else {
				$app = JFactory::getApplication();
				$app->enqueueMessage(JText::_('HIKASHOP_SUCC_SAVED'), 'message');
				if(empty($id)) {
					hikaInput::get()->set($this->plugin_type.'_id', $status);
				}
			}
		}
	}
}
