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
class hikamarketZoneClass extends hikamarketClass {

	protected $tables = array('shop.zone');
	protected $pkeys = array('zone_id');

	protected $toggle = array('zone_published' => 'zone_id');
	protected $toggleAcl = array('zone_published' => 'zone_edit_published');
	protected $deleteToggle = array('shop.zone' => 'zone_id');

	public function &getNameboxData($typeConfig, &$fullLoad, $mode, $value, $search, $options) {
		$ret = array(
			0 => array(),
			1 => array()
		);

		$zones = null;
		$fullLoad = false;

		if(!empty($search)) {
			$limit = 40;
			$searchStr = "'%" . ((HIKASHOP_J30) ? $this->db->escape($search, true) : $this->db->getEscaped($search, true) ) . "%'";

			$query = 'SELECT z.*, zp.zone_namekey as zone_parent_namekey, zp.zone_name_english as zone_parent_name_english, zp.zone_type as zone_parent_type '.
				' FROM ' . hikamarket::table('shop.zone') . ' AS z '.
				' LEFT JOIN ' . hikamarket::table('shop.zone_link') . ' AS zl ON z.zone_namekey = zl.zone_child_namekey '.
				' LEFT JOIN ' . hikamarket::table('shop.zone') . ' AS zp ON zp.zone_namekey = zl.zone_parent_namekey '.
				' WHERE z.zone_published = 1 AND (zp.zone_published IS NULL OR zp.zone_published = 1) AND z.zone_name_english LIKE '.$searchStr.
				' ORDER BY zp.zone_name_english, z.zone_name_english';

			$this->db->setQuery($query, 0, $limit);
			$zones = $this->db->loadObjectList('zone_namekey');

			$containers = array();
			foreach($zones as $element) {
				if(!empty($element->zone_parent_namekey) && !isset($containers[ $element->zone_parent_namekey ])) {
					$obj = new stdClass();
					$obj->status = 2;
					$obj->name = $element->zone_parent_name_english;
					$obj->value = $element->zone_parent_namekey;
					$obj->data = array();

					$ret[0][] =& $obj;
					$containers[ $element->zone_parent_namekey ] =& $obj;
					unset($obj);
				}

				$obj = new stdClass();
				if($element->zone_type == 'state')
					$obj->status = 0;
				else
					$obj->status = 4;
				$obj->name = $element->zone_name_english;
				$obj->value = $element->zone_namekey;
				if(!empty($element->zone_parent_namekey)) {
					$containers[ $element->zone_parent_namekey ]->data[] =& $obj;
				} else {
					$ret[0][] =& $obj;
				}
				unset($obj);
			}

			return $ret;
		}

		if(empty($options['zone_key'])) {

			$zone_types = array('country' => 'COUNTRY', 'ship' => 'SHIPPING');

			$zone_types_db = array();
			foreach($zone_types as $k => $v) {
				$zone_types_db[] = $this->db->Quote($k);
				$o = new stdClass();
				$o->status = 1;
				$o->name = JText::_($v);
				$o->value = 0;
				$o->data = array();
				$o->noselection = 1;
				$ret[0][] =& $o;
				$zone_types[$k] =& $o;
				unset($o);
			}
			$zone_types_db = implode(',', $zone_types_db);

			$query = 'SELECT zone_id, zone_namekey, zone_name, zone_name_english, zone_code_2, zone_code_3, zone_type '.
					' FROM ' . hikamarket::table('shop.zone').
					' WHERE zone_published = 1 AND zone_type IN ('.$zone_types_db.')'.
					' ORDER BY zone_name_english';
		} else {

			$zone_key = $options['zone_key'];

			$query = 'SELECT z.* '.
					' FROM '.hikamarket::table('shop.zone').' as z '.
					' INNER JOIN ' . hikamarket::table('shop.zone_link') . ' as zl ON z.zone_namekey = zl.zone_child_namekey '.
					' WHERE z.zone_published = 1 AND zl.zone_parent_namekey = ' . $this->db->Quote($zone_key).
					' ORDER BY z.zone_name_english';
		}

		$this->db->setQuery($query);
		$zones = $this->db->loadObjectList('zone_id');

		if(!empty($zones)) {
			foreach($zones as $zone) {
				$o = new stdClass();
				$o->name = $zone->zone_name_english;
				$o->value = $zone->zone_namekey;
				if(isset($zone_types) && isset($zone_types[ $zone->zone_type ])) {
					$o->status = 3;
					$zone_types[ $zone->zone_type ]->data[] =& $o;
				} else {
					$o->status = 0;
					$ret[0][] =& $o;
				}
				unset($o);
			}
		}
		unset($zones);

		if(!empty($value)) {
			if(!is_array($value))
				$value = array($value);

			$search = array();
			foreach($value as $v) {
				$search[] = $this->db->Quote($v);
			}
			$query = 'SELECT zone_id, zone_namekey, zone_name, zone_name_english, zone_code_2, zone_code_3, zone_type '.
					' FROM ' . hikamarket::table('shop.zone').
					' WHERE zone_published = 1 AND zone_namekey IN ('.implode(',', $search).')';
			$this->db->setQuery($query);
			$zones = $this->db->loadObjectList('zone_id');
			if(!empty($zones)) {
				foreach($zones as $zone) {
					$zone->name = $zone->zone_name_english;
					$ret[1][$zone->zone_namekey] = $zone;
				}
			}
			unset($zones);

			if($mode == hikamarketNameboxType::NAMEBOX_SINGLE)
				$ret[1] = reset($ret[1]);
		}

		return $ret;
	}
}
