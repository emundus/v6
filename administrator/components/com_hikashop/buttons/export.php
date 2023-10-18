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
class JButtonExport extends JButton {
	protected $name = 'Export';

	public function fetchButton($type = 'Export', $task = '', $text = '', $icon = '', $check = false) {
		$doc = JFactory::getDocument();

		if(empty($task))
			$task = 'export';

		if(empty($text))
			$text = 'HIKA_EXPORT';

		if(empty($icon))
			$icon = 'icon-upload';

		$js = "
function hikaExport(task){
	submitbutton(task);
	var form = document.getElementById('adminForm');
	form.task.value = '';
	return false;
}";
		if(HIKASHOP_J40)
			$btnClass = 'btn btn-info';
		else
			$btnClass = 'btn btn-small';

		$onclick = 'return hikaExport(\''.$task.'\');';
		$attribs = '';
		if($check) {
			if(HIKASHOP_J40) {
				$attribs = ' list-selection';
			} else {
				$onclick = 'if (document.adminForm.boxchecked.value == 0) { alert(Joomla.JText._(\'JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST\')); } else {'.$onclick.'}';
			}
		}
		$doc->addScriptDeclaration($js);

		if(!HIKASHOP_J30)
			return '<a href="#" target="_blank" onclick="'.$onclick.'" class="toolbar"><span class="icon-32-archive" title="' . JText::_($text, true) . '"></span>' . JText::_($text) . '</a>';
		if(!HIKASHOP_J40)
			return '<button class="'.$btnClass.'" onclick="'.$onclick.'"><i class="'.$icon.'"></i> '.JText::_($text).'</button>';
		return '<joomla-toolbar-button'.$attribs.'><button class="'.$btnClass.'" onclick="'.$onclick.'"><i class="'.$icon.'"></i> '.JText::_($text).'</button></joomla-toolbar-button>';
	}

	public function fetchId($type = 'Export', $html = '', $id = 'export') {
		return $this->name . '-' . $id;
	}
}

class JToolbarButtonExport extends JButtonExport {}
