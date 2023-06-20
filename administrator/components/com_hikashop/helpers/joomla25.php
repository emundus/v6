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

class JHtmlHikaselect extends JHTMLSelect {
	static $event = false;
	public static function radiolist($data, $name, $attribs = null, $optKey = 'value', $optText = 'text', $selected = null, $idtag = false, $translate = false, $vertical = false, $default = ''){
		$config = hikashop_config();
		if($config->get('bootstrap_btn_group_on_frontend', 1) && HIKASHOP_J40 && hikashop_isClient('site')) {
			reset($data);
			$attribs = str_replace('custom-select', '', $attribs);

			if (is_array($attribs))	{
				$attribs = ArrayHelper::toString($attribs);
			}

			$id_text = str_replace(array('[',']'),array('_',''),$idtag ? $idtag : $name);

			$html = '<div class="btn-group" id="'.$id_text.'">';

			foreach ($data as $obj) {
				$k = $obj->$optKey;
				$t = $translate ? JText::_($obj->$optText) : $obj->$optText;
				$class = isset($obj->class) ? $obj->class : '';
				$sel = false;
				$extra = $attribs;
				$currId = $id_text . $k;
				if(isset($obj->id))
					$currId = $obj->id;

				if (is_array($selected)) {
					foreach ($selected as $val) {
						$k2 = is_object($val) ? $val->$optKey : $val;
						if ($k == $k2) {
							$extra .= ' selected="selected"';
							$sel = true;
							break;
						}
					}
				} elseif((string) $k == (string) $selected) {
					$extra .= ' checked="checked"';
					$sel = true;
				}

				$extra = ' '.$extra;
				if(!empty($obj->class)) {
					if(strpos($extra, 'class="') === false)
						$extra .= ' class="btn-check '.$obj->class.'"';
					else
						$extra = str_replace('class="', 'class="btn-check '.$obj->class.' ', $extra);
				} else {
					$extra .= ' class="btn-check"';
				}
				$html .= "\n\t" . '<input type="radio" name="' . $name . '"' . ' id="' . $currId . '" value="' . $k . '"' . ' ' . trim($extra) . '/>';

				$html .= "\n\t"."\n\t" . '<label for="' . $currId . '"' . ' class="btn btn-outline-primary">' . $t . '</label>';
			}

			$html .= "\r\n" . '</div>' . "\r\n";

			return $html;
		}
		return parent::radiolist($data, $name, $attribs, $optKey, $optText, $selected, $idtag, $translate, $vertical, $default);
	}


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
