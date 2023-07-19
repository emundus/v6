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
class hikashopRadioType {
	static $event = false;

	protected function init() {
		if(self::$event)
			return true;

		self::$event = true;


		if(HIKASHOP_J40) {
			$app = JFactory::getApplication();
			$doc = $app->getDocument();
			if($doc) {
				$doc->getWebAssetManager()->useStyle('switcher');
			}
		} else {
			hikashop_loadJslib('jquery');
			$doc = JFactory::getDocument();
			$doc->addScriptDeclaration('
setTimeout(function(){
(function($){
	if(!window.hikashopLocal) window.hikashopLocal = {};
	window.hikashopLocal.radioEvent = function(el) {
		var id = $(el).attr("id"), c = $(el).attr("class"), lbl = $("label[for=\"" + id + "\"]"), v = $(el).val(), target = $(el).parent().find("label[data-default=\"1\"]");
		if(v == "-1")
			target.addClass("btn-default");
		else
			target.removeClass("btn-default");
		if(c !== undefined && c.length > 0)
			lbl.addClass(c);
		lbl.addClass("active");
		$("input[name=\"" + $(el).attr("name") + "\"]").each(function() {
			if($(this).attr("id") != id) {
				c = $(this).attr("class");
				lbl = $("label[for=\"" + $(this).attr("id") + "\"]");
				if(c !== undefined && c.length > 0)
					lbl.removeClass(c);
				lbl.removeClass("active");
			}
		});
	}
	$(document).ready(function() {
		$(".hikaradios .btn-group label").off("click");
	});
})(jQuery);
}, 200);
');
		}
	}

	public function booleanlist($name, $attribs = null, $selected = null, $yes = 'JYES', $no = 'JNO', $id = false) {
		$arr = array(
			JHtml::_('select.option', '1', JText::_($yes)),
			JHtml::_('select.option', '0', JText::_($no))
		);
		if(HIKASHOP_J40) {
			$arr = array_reverse($arr);
		}
		$arr[0]->booleanlist = true;
		$arr[0]->class = 'hikabtn-success';

		$arr[1]->booleanlist = true;
		$arr[1]->class = 'hikabtn-danger';

		return $this->radiolist($arr, $name, $attribs, 'value', 'text', (int)$selected, $id);
	}

	public function radiolist($data, $name, $attribs = null, $optKey = 'value', $optText = 'text', $selected = null, $idtag = false, $translate = false, $vertical = false, $default = '') {
		reset($data);
		$app = JFactory::getApplication();

		$this->init();

		$yes_text = JText::_('JYES');
		$no_text = JText::_('JNO');
		foreach($data as &$obj) {
			if(HIKASHOP_J40) {
				$obj->class = '';
			} else {
				if(!empty($obj->class))
					continue;
				$obj->class = 'hikabtn-primary';
				if(($translate && $obj->$optText == 'JYES') || (!$translate && $obj->$optText == $yes_text))
					$obj->class = 'hikabtn-success';
				if(($translate && $obj->$optText == 'JNO') || (!$translate && $obj->$optText == $no_text))
					$obj->class = 'hikabtn-danger';
			}
		}
		unset($obj);

		if(is_array($attribs))	{
			$attribs = array_map('strval', $attribs);
		}

		$id_text = str_replace(array('[',']'),array('_',''),$idtag ? $idtag : $name);

		$backend = false && hikashop_isClient('administrator');
		$htmlLabels = '';
		$html = '<div class="hikaradios" id="'.$id_text.'">';
		$mainClass = 'hikabtn-group'. ($vertical?' hikabtn-group-vertical':'');
		$labelClass = 'hikabtn';

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
			if(!HIKASHOP_J40) {
				if(strpos($extra, ' style="') !== false) {
					$extra = str_replace(' style="', ' style="display:none;', $extra);
				} elseif(strpos($extra, 'style=\'') !== false) {
					$extra = str_replace(' style=\'', ' style=\'display:none;', $extra);
				} else {
					$extra .= ' style="display:none;"';
				}
				if(strpos($extra, ' onchange="') !== false) {
					$extra = str_replace(' onchange="', ' onchange="hikashopLocal.radioEvent(this);', $extra);
				} elseif(strpos($extra, 'onchange=\'') !== false) {
					$extra = str_replace(' onchange=\'', ' onchange=\'hikashopLocal.radioEvent(this);', $extra);
				} else {
					$extra .= ' onchange="hikashopLocal.radioEvent(this);"';
				}
			}
			if(!empty($obj->class)) {
				if(strpos($extra, 'class="') === false)
					$extra .= ' class="'.$obj->class.'"';
				else
					$extra = str_replace('class="', 'class="'.$obj->class.' ', $extra);
			}
			$input = "\n\t" . '<input type="radio" name="' . $name . '"' . ' class="'.($sel ? ' active '.$class : '').'" id="' . $currId . '" value="' . $k . '"' . ' ' . trim($extra) . '/>';

			$dataDefault = '0';
			$addClass = '';
			if(isset($obj->default) && $obj->default) {
				$dataDefault = '1';
				$addClass = 'hikabtn-default-lbl';
				if($selected == '-1')
					$addClass .= ' hikabtn-default';
			}
			if(HIKASHOP_J40) {
				$htmlLabels .= $input;
				$labelClass = '';
				$addClass = '';
			} else {
				$html .= $input;
			}
			$htmlLabels .= '<label for="' . $currId . '"' . ' data-default="'.$dataDefault.'" class="'.$labelClass.' '.$addClass.($sel ? ' active '.$class : '') .'">' . $t . '</label>';

		}
		if(HIKASHOP_J40) {
			$mainClass = 'switcher';
			$htmlLabels .= '<span class="toggle-outside"><span class="toggle-inside"></span></span>';
		}

		$html .= "\r\n" . '<div class="'.$mainClass.'" data-toggle="">' . $htmlLabels . "\r\n" . '</div>';
		$html .= "\r\n" . '</div>' . "\r\n";
		return $html;
	}
}
