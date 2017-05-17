<?php
/**
 * @package	HikaShop for Joomla!
 * @version	3.0.1
 * @author	hikashop.com
 * @copyright	(C) 2010-2017 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class hikashopPluginClass extends hikashopClass {
	var $tables = array('plugin');
	var $pkeys = array('plugin_id');
	var $toggle = array('plugin_published' => 'plugin_id');
	var $deleteToggle = array('plugin' => array('plugin_type', 'plugin_id'));

	function get($id, $default = '') {
		$result = parent::get($id);
		if(!empty($result->plugin_params))
			$result->plugin_params = hikashop_unserialize($result->plugin_params);
		return $result;
	}

	function save(&$element, $reorder = true) {
		JPluginHelper::importPlugin('hikashop');
		$dispatcher = JDispatcher::getInstance();
		$do = true;
		if(empty($element->payment_id))
			$dispatcher->trigger('onBeforeHikaPluginCreate', array('plugin', &$element, &$do));
		else
			$dispatcher->trigger('onBeforeHikaPluginUpdate', array('plugin', &$element, &$do));

		if(!$do)
			return false;

		if(isset($element->plugin_params) && !is_string($element->plugin_params))
			$element->plugin_params = serialize($element->plugin_params);

		if(empty($element->plugin_id))
			unset($element->plugin_id);

		$status = parent::save($element);
		if($status && empty($element->plugin_id)) {
			$element->plugin_id = $status;
			if($reorder) {
				$orderHelper = hikashop_get('helper.order');
				$orderHelper->pkey = 'plugin_id';
				$orderHelper->table = 'plugin';
				$orderHelper->groupVal = $element->plugin_type;
				$orderHelper->orderingMap = 'plugin_ordering';
				$orderHelper->reOrder();
			}
		}

		if($status && !empty($element->plugin_published) && !empty($element->plugin_id)) {
			$db = JFactory::getDBO();
			$query = 'SELECT plugin_type FROM ' . hikashop_table('plugin') . ' WHERE plugin_id = ' . (int)$element->plugin_id;
			$db->setQuery($query);
			$name = $db->loadResult();
			if(!HIKASHOP_J16) {
				$query = 'UPDATE '.hikashop_table('plugins',false).' SET published = 1 WHERE published = 0 AND element = ' . $db->Quote($name) . ' AND folder = ' . $db->Quote('hikashop');
			} else {
				$query = 'UPDATE '.hikashop_table('extensions',false).' SET enabled = 1 WHERE enabled = 0 AND type = ' . $db->Quote('plugin') . ' AND element = ' . $db->Quote($name) . ' AND folder = ' . $db->Quote('hikashop');
			}
			$db->setQuery($query);
			$db->query();
		}
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
				$row->id = implode($parts);
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
}
