<?php
/**
 * @package	HikaShop for Joomla!
 * @version	3.0.1
 * @author	hikashop.com
 * @copyright	(C) 2010-2017 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php

class JHtmlHikaselect extends JHTMLSelect {
	static $event = false;

	public static function inheritRadiolist($name, $selected = null, $extra = array(), $attribs = null, $id = false){
		$arr = array(
			JHtml::_('select.option', '-1', JText::_('HIKA_INHERIT')),
			JHtml::_('select.option', '1', JText::_('JYES')),
			JHtml::_('select.option', '0', JText::_('JNO'))
		);

		if(!is_array($extra))
			$extra = array($extra);
		foreach($extra as $option){
			$arr[] = $option;
		}

		$shortName = str_replace(']','',preg_replace('#(.*)\[#','',$name));
		$config = hikashop_config();

		if($shortName == 'display_filters')
			$shortName = 'show_filters';

		$default = $config->get($shortName,'');
		$default_params = $config->get('default_params');
		if(isset($default_params[$shortName])){
			$default = $default_params[$shortName];
		}
		foreach($arr as $k => $v){
			$arr[$k]->booleanlist = true;
			if($v->value == $default)
				$v->default = true;
		}
		return self::radiolist($arr, $name, $attribs, 'value', 'text', (int) $selected, $id);
	}
}
