<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class hikashopWarehouseType{

	var $values = array();

	function __construct() {
		$this->app = JFactory::getApplication();
	}

	function load($value) {
		$this->values = array();
		$db = JFactory::getDBO();

		$query = 'SELECT COUNT(*) FROM '.hikashop_table('warehouse').' WHERE warehouse_published = 1';
		$db->setQuery($query);
		$ret = (int)$db->loadResult();
		if($ret > 10) {
			$this->values = $ret;
			return;
		}

		$query = 'SELECT * FROM '.hikashop_table('warehouse').' WHERE warehouse_published = 1';
		$db->setQuery($query);
		$warehouses = $db->loadObjectList();
		$this->values[] = JHTML::_('select.option', 0, JText::_('NO_WAREHOUSE'));
		if(!empty($warehouses)){
			foreach($warehouses as $warehouse){
				if($warehouse->warehouse_id == 0 || $warehouse->warehouse_id == 1)
					continue;
				$this->values[] = JHTML::_('select.option', $warehouse->warehouse_id, $warehouse->warehouse_id.' '.$warehouse->warehouse_name);
			}
		}
	}

	public function displayDropdown($map, $value, $delete = false, $options = '', $id = '') {
		if(empty($this->values))
			$this->load($value);
		return JHTML::_('select.genericlist', $this->values, $map, $options, 'value', 'text', $value, $id);
	}
	function display($map, $value, $delete = false) {
		$nameboxType = hikashop_get('type.namebox');
		return $nameboxType->display(
			$map,
			(int)$value,
			hikashopNameboxType::NAMEBOX_SINGLE,
			'warehouse',
			array(
				'delete' => $delete,
				'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
			)
		);
		return $ret;
	}
}
