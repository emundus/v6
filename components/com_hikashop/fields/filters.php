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
class JFormFieldFilters extends JFormField
{
	var $type = 'help';
	function getInput() {
		if(!defined('DS'))
			define('DS', DIRECTORY_SEPARATOR);
		if(!function_exists('hikashop_getCID') && !include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php')){
			return 'This plugin can not work without the Hikashop Component';
		}
		$nameboxType = hikashop_get('type.namebox');
		if(!is_array($this->value))
			$this->value = explode(',',$this->value);
		$text = $nameboxType->display(
			$this->name,
			$this->value,
			hikashopNameboxType::NAMEBOX_MULTIPLE,
			'filter',
			array(
				'delete' => true,
				'returnOnEmpty' => false,
				'default_text' => '<em>'.JText::_('HIKA_ALL').'</em>',
				'url_params' => array(),
			)
		);
		if(empty($text))
			$text = hikashop_display(JText::_('PLEASE_CREATE_FILTERS_FIRST'), 'error', true);
		return $text;
	}
}
