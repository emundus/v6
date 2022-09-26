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
class hikamarketProductsType {

	public function display($map, $value) {
		return '';
	}

	public function displaySingle($map, $value, $display = '', $root = 0, $delete = false) {

		if(empty($this->nameboxType))
			$this->nameboxType = hikamarket::get('type.namebox');

		return $this->nameboxType->display(
			$map,
			$value,
			hikamarketNameboxType::NAMEBOX_SINGLE,
			'product',
			array(
				'delete' => $delete,
				'root' => $root,
				'displayFormat' => $display,
				'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
			)
		);
	}

	public function displayMultiple($map, $values, $display = '', $root = 0) {

		if(empty($this->nameboxType))
			$this->nameboxType = hikamarket::get('type.namebox');

		return $this->nameboxType->display(
			$map,
			$values,
			hikamarketNameboxType::NAMEBOX_MULTIPLE,
			'product',
			array(
				'delete' => true,
				'root' => $root,
				'sort' => true,
				'displayFormat' => $display,
				'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
			)
		);
	}
}
