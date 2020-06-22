<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2020 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class JElementSelectproduct extends JElement {
	function fetchElement($name, $value, &$node, $control_name) {
		if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php')){
			echo 'HikaShop is required';
			return;
		}

		if(is_array($this->value))
			$this->value = reset($this->value);

		$nameboxType = hikashop_get('type.namebox');
		$select = '<div style="height:130px; margin-left:150px;">' .
				$nameboxType->display(
					'jform[params][product_id]',
					$this->value,
					hikashopNameboxType::NAMEBOX_SINGLE,
					'product',
					array(
						'delete' => false,
						'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
					)
				).
				'</div>';

		return $select;
	}
}
