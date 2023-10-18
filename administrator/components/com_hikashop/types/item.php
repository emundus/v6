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
class hikashopItemType {
	function loadFromCustom($hikashopFiles, $template, $customDir, $files) {
		if (!is_dir($customDir))
			return;

		$customFiles = JFolder::files($customDir);
		if (empty($customFiles))
			return;

		$files = array();
		foreach ($customFiles as $file) {
			$notHikashop = true;
			foreach ($hikashopFiles as $hikashopfile) {
				if ($hikashopfile == $file) {
					$notHikashop = false;
					break;
				}
			}
			if ($notHikashop)
				$files[] = $file;
		}
		if (!empty($files)) {
			$files = array_keys(array_flip($files));
			$this->loadValues('-- ' . JText::sprintf('FROM_TEMPLATE',basename($template)) . ' --', $files);
		}
	}

	function loadFromTemplates($hikashopFiles) {
		$files = array();
		$templates = JFolder::folders(JPATH_SITE . DS . 'templates', '.', false, true);
		if (empty($templates))
			return;

		foreach ($templates as $template) {
			$this->loadFromCustom($hikashopFiles, $template, $template . DS . 'html' . DS . 'com_hikashop' . DS . 'product', $files);
			$this->loadFromCustom($hikashopFiles, $template, $template . DS . 'html' . DS . 'com_hikashop' . DS . 'category', $files);
		}
	}

	function loadValues($optGroup, $files) {
		if(!HIKASHOP_J40) {
			$this->values[] = JHTML::_('select.optgroup', $optGroup);
			foreach($files as $file){
				if(preg_match('#^listing_((?!div|list|price|table|vote).*)\.php$#',$file,$match)){
					$trans = hikashop_translate('LAYOUT_'.$match[1]);
					if($trans == 'LAYOUT_'.$match[1])
						$trans = hikashop_translate($match[1]);
					$this->values[$match[1]] = JHTML::_('select.option', $match[1], $trans);
				}
			}
			$this->values[] = JHTML::_('select.optgroup', $optGroup);
			return;
		}

		$values = array();
		foreach($files as $file){
			if(preg_match('#^listing_((?!div|list|price|table|vote).*)\.php$#',$file,$match)){
				$trans = hikashop_translate('LAYOUT_'.$match[1]);
				if($trans == 'LAYOUT_'.$match[1])
					$trans = hikashop_translate($match[1]);
				$values[$match[1]] = JHTML::_('select.option', $match[1], $trans);
			}
		}
		$this->values[] = array(
			'text' => $optGroup,
			'items' => $values
		);
	}

	function load() {
		$this->values = array();

		jimport('joomla.filesystem.folder');
		$product_folder = HIKASHOP_FRONT.'views'.DS.'product'.DS.'tmpl'.DS;
		$category_folder = HIKASHOP_FRONT.'views'.DS.'category'.DS.'tmpl'.DS;
		$files = JFolder::files($product_folder);
		$files = array_keys(array_merge(array_flip($files), array_flip(JFolder::files($category_folder))));
		$this->loadValues('-- '.JText::_('FROM_HIKASHOP').' --', $files);
		$this->loadFromTemplates($files);

		if(hikaInput::get()->getVar('inherit', true) == true) {
			$config = hikashop_config();
			$defaultParams = $config->get('default_params');
			$default = '';
			if(isset($defaultParams['div_item_layout_type']))
				$default = ' ('.@$this->values[$defaultParams['div_item_layout_type']]->text.')';
			if(!HIKASHOP_J40)
				$this->values[] = JHTML::_('select.option', 'inherit', JText::_('HIKA_INHERIT').$default);
			else
				$this->values[''] = array('items' => array( JHTML::_('select.option', 'inherit', JText::_('HIKA_INHERIT').$default)) );
		}
	}

	function display($map, $value, &$js, $option = '') {
		$this->load();
		$options = 'class="custom-select" size="1" '.$option;

		if(!HIKASHOP_J40)
			return JHTML::_('select.genericlist', $this->values, $map, $options, 'value', 'text', $value );
		return JHTML::_('select.groupedlist', $this->values, $map, array('list.attr'=>'class="custom-select"', 'group.id' => 'id', 'list.select' => array($value)) );
	}
}
