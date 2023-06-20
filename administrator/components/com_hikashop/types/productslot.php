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
class hikashopProductslotType {
	private $values = null;

	public function load() {
		$this->values = array(
			'show' => array(
				'topBegin',
				'topEnd',
				'leftBegin',
				'leftEnd',
				'rightBegin',
				'rightMiddle',
				'rightEnd',
				'bottomBegin',
				'bottomMiddle',
				'bottomEnd'
			),
			'listing' => array(
				'top',
				'afterProductName',
				'bottom'
			)
		);
		return $this->values;
	}

	public function display($map, $value, $type = 'show') {
		if(empty($this->values))
			$this->load();

		if(!isset($this->values[$type])) {
			$type = 'show';
		}
		return JHTML::_('select.genericlist', $this->values[$type], $map, 'class="custom-select" size="1"', 'value', 'text', $value);
	}
}
