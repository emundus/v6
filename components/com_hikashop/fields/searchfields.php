<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class JFormFieldSearchfields extends JFormField
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
			'column',
			array(
				'delete' => true,
				'returnOnEmpty' => false,
				'table' => 'product',
				'default_text' => '<em>'.JText::_('HIKA_ALL').'</em>',
				'url_params' => array(
						'TABLE' => 'product',
					),
			)
		);
		if(empty($text))
			$text = hikashop_display(JText::_('PLEASE_CREATE_FILTERS_FIRST'), 'error', true);

		return $text;
	}
}
