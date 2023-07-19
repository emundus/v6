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
if(!HIKASHOP_J30) {
	$toolbarInstance = JToolbar::getInstance();
	$toolbarInstance->loadButtonType('Popup');
	class JButtonHikaPopup extends JButtonPopup {
		public function fetchButton($type = 'Popup', $name = '', $text = '', $url = '', $width = 640, $height = 480, $top = 0, $left = 0, $onClose = '', $title = '', $footer = '', $check = false) {
			if(empty($title) && empty($footer))
				return parent::fetchButton($type, $name, $text, $url, $width, $height, $top, $left, $onClose);

			JHtml::_('behavior.modal');

			$text = JText::_($text);
			$class = $this->fetchIconClass($name);
			$doTask = $url; //$this->_getCommand($name, $url, $width, $height, $top, $left);
			$id = 'modal-toolbar-' . $name;

			$popup = hikashop_get('helper.popup');
			$params = array(
				'width' => $width,
				'height' => $height,
				'type' => 'link',
				'footer' => $footer
			);

			$html = $popup->displayMootools('<span class="'.$class.'"></span>'.$text, $title, $doTask, $id, $params);

			return $html;
		}
	}
} elseif(!HIKASHOP_J40) {
	class JToolbarButtonHikaPopup extends JToolbarButton {
		protected $_name = 'HikaPopup';

		public function fetchButton($type = 'Modal', $name = '', $text = '', $url = '', $width = 640, $height = 480, $top = 0, $left = 0, $onClose = '', $title = '', $footer = '', $check = false) {
			hikashop_loadJSLib('vex');

			list($name, $icon) = explode('#', $name, 2);
			$name .= '-btnpopup';

			$url = $this->_getCommand($url);

			if(HIKASHOP_J40)
				$btnClass = 'btn btn-sm btn-outline-primary';
			else
				$btnClass = 'btn btn-small';

			$onclick = 'return window.hikashop.openBox(this);';
			if($check)
				$onclick = 'if (document.adminForm.boxchecked.value == 0) { alert(Joomla.JText._(\'JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST\')); } else {'.$onclick.'}';

			return '<button onclick="'.$onclick.'" href="'.$url.'" data-hk-popup="vex" data-vex="{x:'.(int)$width.', y:'.(int)$height.'}" class="'.$btnClass.'">'.
				'<span class="icon-'.trim($icon).'"></span> ' . JText::_($text) .
			'</button>';
		}

		public function fetchId($type, $name) {
			return $this->_parent->getName() . '-popup-' . $name;
		}

		private function _getCommand($url) {
			$base = JUri::base(true);
			if (strpos($url, 'http') !== 0 && strpos($url, $base) !== 0)
				$url = JUri::base() . $url;
			return $url;
		}
	}
} else {
	class JToolbarButtonHikaPopup extends JToolbarButton {
		protected $_name = 'HikaPopup';

		public function fetchButton($type = 'Modal', $name = '', $text = '', $url = '', $width = 640, $height = 480, $top = 0, $left = 0, $onClose = '', $title = '', $footer='', $check = false) {
			hikashop_loadJSLib('vex');

			list($name, $icon) = explode('#', $name, 2);
			$name .= '-btnpopup';

			$url = $this->_getCommand($url);

			if(HIKASHOP_J40)
				$btnClass = 'btn btn-info';
			else
				$btnClass = 'btn btn-small';

			$attribs = '';
			if($check)
				$attribs.=' list-selection';

			return '
			<joomla-toolbar-button'.$attribs.'>'.
				'<button onclick="return window.hikashop.openBox(this);" href="'.$url.'" data-hk-popup="vex" data-vex="{x:'.(int)$width.', y:'.(int)$height.'}" class="'.$btnClass.'">'.
					'<span class="icon-'.trim($icon).'"></span> ' . JText::_($text) .
				'</button>' .
			'</joomla-toolbar-button>';
		}

		public function fetchId() {
			return $this->_parent->getName() . '-popup-' . $this->getName();
		}

		private function _getCommand($url) {
			$base = JUri::base(true);
			if (strpos($url, 'http') !== 0 && strpos($url, $base) !== 0)
				$url = JUri::base() . $url;
			return $url;
		}
	}
}
