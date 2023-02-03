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
var '.$id.' = null;
function otree_init_'.$id.'() {
	var options = {rootImg:"'.HIKAMARKET_IMAGES.'otree/", showLoading:false};
	var data = '.$this->getDataFull($type, $root, $displayRoot, $selectRoot).';
	'.$id.' = new window.oTree("'.$id.'",options,null,data,false);
	'.$id.'.addIcon("world","world.png");
	'.$id.'.render(true);
}
if(document.getElementById('.$id.')!==null)
	otree_init_'.$id.'();
else
	window.hikashop.ready(otree_init_'.$id.');
</script>';
		return $ret;
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
