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
class hikamarketMenu_contentType {
	protected $values;

	public function __construct() {
		$this->values = array();
	}

	private function load() {
		$this->values = array(
			'vendor' => JHTML::_('select.option', 'vendor', JText::_('HIKA_VENDOR')),
		);
	}

	public function display($map, $value, &$js, $id = '', $warning = false) {
		if(empty($this->values))
			$this->load();

		$options = '';
		if(!empty($id)) {
			if(empty($value))
				$value = 'vendor';

			$js .= "\r\n".
				'if(window.localPage.switchPanel) window.localPage.switchPanel(\''.$id.'\', "'.str_replace('"', '\\"', $value).'", "content");'."\r\n";
			$options = 'onchange="if(window.localPage.switchPanel) window.localPage.switchPanel(\''.$id.'\', this.value, \'content\');"';
		}

		if(!isset($this->values[$value])) {
			$values = $this->values;
			$values[$value] = JHTML::_('select.option', $value, $value);
			$ret = '';
			if($warning)
				$ret .= JText::_('HIKAM_MENU_TYPE_NOT_SUPPORTED') . '<br/>';
			return $ret . JHTML::_('select.genericlist', $values, $map, 'class="inputbox" size="1" ' . $options, 'value', 'text', $value, 'content_select');
		}

		return JHTML::_('select.genericlist', $this->values, $map, 'class="inputbox" size="1" ' . $options, 'value', 'text', $value, 'content_select');
	}
}
