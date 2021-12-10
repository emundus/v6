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
class hikamarketShop_categoryType {

	public function display($map, $value, $type = 'category', $field = 'category_id', $form = true, $none = true) {
		$categoryType = hikamarket::get('shop.type.categorysub');
		$categoryType->type = $type;
		$categoryType->field = $field;
		return $categoryType->display($map, $value, $form, $none);
	}

	public function displaySingle($map, $value, $type = '', $root = 0, $delete = false) {

		if(empty($this->nameboxType))
			$this->nameboxType = hikamarket::get('type.namebox');

		return $this->nameboxType->display(
			$map,
			$value,
			hikamarketNameboxType::NAMEBOX_SINGLE,
			'category',
			array(
				'delete' => $delete,
				'root' => $root,
				'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
			)
		);
	}

	public function displayMultiple($map, $values, $type = '', $root = 0) {

		if(empty($this->nameboxType))
			$this->nameboxType = hikamarket::get('type.namebox');

		$first_element = reset($values);
		if(is_object($first_element))
			$values = array_keys($values);

		return $this->nameboxType->display(
			$map,
			$values,
			hikamarketNameboxType::NAMEBOX_MULTIPLE,
			'category',
			array(
				'delete' => true,
				'root' => $root,
				'sort' => true,
				'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
			)
		);
	}

	public function displayTree($id, $root = 0, $type = null, $displayRoot = false, $selectRoot = false, $openTo = null, $link = '') {
		hikamarket::loadJslib('otree');
		if(empty($type))
			$type = array('product','vendor');
		$ret = '';

		$ret .= '<div id="'.$id.'_otree" class="oTree"></div>
<script type="text/javascript">
var options = {rootImg:"'.HIKAMARKET_IMAGES.'otree/", showLoading:false};
var data = '.$this->getDataFull($type, $root, $displayRoot, $selectRoot).';
var '.$id.' = new window.oTree("'.$id.'",options,null,data,false);
'.$id.'.addIcon("world","world.png");
'.$id.'.render(true);
</script>';
		return $ret;
	}

	private function getData($type = 'product', $root = 0, $displayRoot = false, $selectRoot = false, $value = null) {
		$categoryClass = hikamarket::get('class.category');
		$shopCategoryClass = hikamarket::get('shop.class.category');
		if($root == 1)
			$root = 0;
		if(empty($root)) {
			if(!is_array($type))
				$type = array($type);
			$type[] = 'root';
		}
		$typeConfig = array('params' => array('category_type' => $type), 'mode' => 'tree');
		$fullLoad = false;
		$config = hikamarket::config();
		$options = array('depth' => $config->get('explorer_default_depth', 3), 'start' => $root);

		list($elements,$values) = $categoryClass->getNameboxData($typeConfig, $fullLoad, null, null, null, $options);

		if($value !== null) {
			$parents = $shopCategoryClass->getParents($value);
			$parents = array_reverse($parents);
		}

		$data = array();
		if(!empty($parents)) {
			$first = true;
			foreach($parents as $p) {
				$o = new stdClass();
				$o->status = 2;
				if($first) {
					if($p->category_left + 1 == $p->category_right) {
						$o->status = 4;
					} else {
						$o->status = 3;
					}
					$first = false;
				}

				$o->name = JText::_($p->category_name);
				$o->value = (int)$p->category_id;
				$o->data = $data;

				$s = new stdClass();
				$s->status = 4;
				$s->name = '...';
				$s->value = -(int)$p->category_parent_id;
				$s->data = array();
				$data = array($o, $s);
			}
		}

	var_dump($data);
	var_dump($elements);
	return json_encode($elements);

		foreach($elements as $k => $el) {
			if($el->value != $data[0]->data[0]->value)
				continue;

			if(count($el->data)) {
				$found = false;
				foreach($el->data as $j => $e) {
					if($e->value == $data[0]->data[0]->data[0]->value) {
						$elements[$k]->data[$j]->data = $data[0]->data[0]->data[0]->data;
						$elements[$k]->data[$j]->status = 2;
						$found = true;
					}
				}
				if(!$found) {
					$elements[$k]->data[] = $data[0]->data[0]->data[0];
				}
			} else {
				$elements[$k]->data = $data[0]->data[0]->data;
				$elements[$k]->status = 2;
			}
			break;
		}

		return json_encode($elements);
	}

	private function getDataFull($type = 'product', $root = 0, $displayRoot = false, $selectRoot = false) {
		$marketCategory = hikamarket::get('class.category');
		if($root == 1)
			$root = 0;
		$elements = $marketCategory->getList($type, $root, $displayRoot);

		$d = null;
		foreach($elements as $k => $element) {
			if($d !== null && $element->category_depth > ($d + 1)) {
				unset($elements[$k]);
			} else {
				$d = (int)$element->category_depth;
			}
		}

		$ret = array();
		$tmp = array();
		foreach($elements as $k => $element) {
			$el = new stdClass();
			$el->status = 4;
			$el->name = $element->category_name;

			if($element->category_type == 'root') {
				$el->status = 5;
				$el->icon = 'world';
				if(!$selectRoot)
					$el->noselection = 1;
				else
					$el->value = (int)$element->category_id;
			} else {
				$el->value = (int)$element->category_id;
			}

			$tmp[ (int)$element->category_id ] =& $el;

			if(empty($element->category_parent_id) || empty($tmp[(int)$element->category_parent_id]) || $tmp[(int)$element->category_parent_id]->status == 5) {
				$ret[] =& $el;
				unset($el);
				continue;
			}

			unset($parent);
			$parent =& $tmp[(int)$element->category_parent_id];
			$parent->status = 2;
			if(empty($parent->data))
				$parent->data = array();
			$parent->data[] =& $el;
			unset($el);
		}
		unset($tmp);

		return json_encode($ret);
	}
}
