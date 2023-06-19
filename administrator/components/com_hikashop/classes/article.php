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
class hikashopArticleClass extends hikashopClass {

	public function getLanguageArticleId($id) {
		if ($id > 0 && JLanguageAssociations::isEnabled()) {
			$associated = JLanguageAssociations::getAssociations('com_content', '#__content', 'com_content.item', $id);
			$currentLang = JFactory::getLanguage()->getTag();

			if (isset($associated[$currentLang]))
				$id = $associated[$currentLang]->id;
		}
		return $id;
	}

	public function &getNameboxData($typeConfig, &$fullLoad, $mode, $value, $search, $options) {
		$ret = array(
			0 => array(),
			1 => array()
		);

		$fullLoad = false;
		$displayFormat = !empty($options['displayFormat']) ? $options['displayFormat'] : @$typeConfig['displayFormat'];

		$start = (int)@$options['start']; // TODO
		$limit = (int)@$options['limit'];
		$page = (int)@$options['page'];
		if($limit <= 0)
			$limit = 50;

		$select = array('c.*');
		$table = array('#__content AS c');
		$where = array();

		if(!empty($search)) {
			$searchMap = array('c.title', 'c.alias', 'c.id');
			if(!HIKASHOP_J30)
				$searchVal = '\'%' . $this->db->getEscaped(HikaStringHelper::strtolower($search), true) . '%\'';
			else
				$searchVal = '\'%' . $this->db->escape(HikaStringHelper::strtolower($search), true) . '%\'';
			$where['search'] = '('.implode(' LIKE '.$searchVal.' OR ', $searchMap).' LIKE '.$searchVal.')';
		}

		$order = ' ORDER BY c.id DESC';

		if(count($where))
			$where = ' WHERE ' . implode(' AND ', $where);
		else
			$where = '';

		$query = 'SELECT '.implode(', ', $select) . ' FROM ' . implode(' ', $table) . $where . $order;
		$this->db->setQuery($query, $page, $limit);

		$ret[0] = $this->db->loadObjectList('id');

		if(count($ret[0]) < $limit)
			$fullLoad = true;

		if(empty($value))
			return $ret;

		if($mode == hikashopNameboxType::NAMEBOX_SINGLE && isset($ret[0][$value])) {
			$ret[1][$value] = $ret[0][$value];
		} elseif($mode == hikashopNameboxType::NAMEBOX_SINGLE) {
			$query = 'SELECT '.implode(', ', $select) . ' FROM ' . implode(' ', $table) . ' WHERE c.id = '.(int)$value;
			$this->db->setQuery($query);
			$ret[1][$value] = $this->db->loadObject();
		} elseif($mode == hikashopNameboxType::NAMEBOX_MULTIPLE && is_array($value)) {
			foreach($value as $v) {
				if(isset($ret[0][$v])) {
					$ret[1][$v] = $ret[0][$v];
				}
			}
		}
		return $ret;
	}
}
