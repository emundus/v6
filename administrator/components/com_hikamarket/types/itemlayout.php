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
class hikamarketItemlayoutType {

	protected $values = array();

	protected function loadFromCustom($hikamarketFiles, $template, $customDir, $files) {
		if(!is_dir($customDir))
			return;

		$customFiles = JFolder::files($customDir);
		if (empty($customFiles))
			return;

		$files = array();
		foreach ($customFiles as $file) {
			$notHikaMarket = true;
			foreach($hikamarketFiles as $hikamarketfile) {
				if ($hikamarketfile == $file) {
					$notHikaMarket = false;
					break;
				}
			}
			if($notHikaMarket)
				$files[] = $file;
		}
		if(!empty($files)) {
			$files = array_keys(array_flip($files));
			$this->loadValues('-- ' . JText::sprintf('FROM_TEMPLATE', basename($template)) . ' --', $files);
		}
	}

	protected function loadFromTemplates($hikamarketFiles) {
		$files = array();
		$templates = JFolder::folders(JPATH_SITE . DS . 'templates', '.', false, true);
		if(empty($templates))
			return;
		foreach($templates as $template) {
			$this->loadFromCustom($hikamarketFiles, $template, $template . DS . 'html' . DS . 'com_hikamarket' . DS . 'vendormarket', $files);
		}
	}

	protected function loadValues($optGroup, $files) {
		$config_data = array(
			$optGroup => array()
		);
		foreach($files as $file) {
			if(!preg_match('#^listingcontent_(.*)\.php$#', $file, $match))
				continue;
			$val = strtoupper($match[1]);
			$trans = JText::_($val);
			if($trans == $val)
				$trans = $match[1];
			$config_data[$optGroup][] = JHTML::_('select.option', $match[1], $trans);
		}
		if(!HIKASHOP_J40) {
			foreach($config_data as $optGroup => $values) {
				$this->values[] = JHTML::_('select.optgroup', $optGroup);
				$this->values = array_merge($this->values, $values);
				$this->values[] = JHTML::_('select.optgroup', '');
			}
		} else {
			foreach($config_data as $optGroup => $values) {
				$this->values[] = array(
					'text' => $optGroup,
					'items' => $values
				);
			}
		}
		if(hikaInput::get()->getBool('inherit',true) == true)
			$this->values[] = JHTML::_('select.option', 'inherit', JText::_('HIKA_INHERIT'));
	}

	protected function load() {
		$this->values = array();
		jimport('joomla.filesystem.folder');
		$vendor_folder = HIKAMARKET_FRONT.'views'.DS.'vendormarket'.DS.'tmpl'.DS;
		$files = JFolder::files($vendor_folder);
		$this->loadValues('-- '.JText::_('FROM_HIKAMARKET').' --', $files);
		$this->loadFromTemplates($files);
	}

	public function display($map, $value, &$js, $option = '') {
		if(empty($this->values))
			$this->load();
		$options = 'class="inputbox" size="1" '.$option;
		return JHTML::_('select.genericlist', $this->values, $map, $options, 'value', 'text', $value);
	}
}
