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
class hikashopProductdisplayType {
	var $default = array(
		'show_default',
		'show_reversed',
		'show_tabular'
	);

	function load(){
		if(!empty($this->values))
			return;
		$this->values = array();
		if(!HIKASHOP_J40) {
			if(hikaInput::get()->getCmd('from_display',false) == false)
				$this->values[] = JHTML::_('select.option', '', JText::_('HIKA_INHERIT'));
			$this->values[] = JHTML::_('select.optgroup', '-- '.JText::_('FROM_HIKASHOP').' --');
			foreach($this->default as $d) {
				$this->values[] = JHTML::_('select.option', $d, JText::_(strtoupper($d)));
			}
			$this->values[] = JHTML::_('select.optgroup', '-- '.JText::_('FROM_HIKASHOP').' --');

			$closeOpt = '';
			$values = $this->getLayout();
			foreach($values as $value) {
				if(substr($value,0,1) == '#') {
					if(!empty($closeOpt)){
						$this->values[] = JHTML::_('select.optgroup', $closeOpt);
					}
					$value = substr($value,1);
					$closeOpt = '-- ' . JText::sprintf('FROM_TEMPLATE',basename($value)) . ' --';
					$this->values[] = JHTML::_('select.optgroup', $closeOpt);
				} else {
					$this->values[] = JHTML::_('select.option', $value, JText::_(strtoupper($value)));
				}
			}
			if(!empty($closeOpt)){
				$this->values[] = JHTML::_('select.optgroup', $closeOpt);
			}
		} else {
			if(hikaInput::get()->getCmd('from_display',false) == false)
				$this->values[''] = array('items' => array( JHTML::_('select.option', '', JText::_('HIKA_INHERIT'))) );
			$this->values['core'] = array(
				'text' => '-- '.JText::_('FROM_HIKASHOP').' --',
				'items' => array()
			);
			foreach($this->default as $d) {
				$this->values['core']['items'][] = JHTML::_('select.option', $d, JText::_(strtoupper($d)));
			}

			$tmpl_name = '';
			$values = $this->getLayout();
			foreach($values as $value) {
				if(substr($value,0,1) == '#') {
					$value = substr($value,1);
					$tmpl_name = basename($value);
					$this->values[$tmpl_name] = array(
						'text' => '-- ' . JText::sprintf('FROM_TEMPLATE',$tmpl_name) . ' --',
						'items' => array()
					);
				} else {
					$this->values[$tmpl_name]['items'][] = JHTML::_('select.option', $value, JText::_(strtoupper($value)));
				}
			}
		}

		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$app->triggerEvent('onProductLayoutSelect', array(&$this->values));
	}

	function display($map,$value) {
		$this->load();
		if(!HIKASHOP_J40)
			return JHTML::_('select.genericlist', $this->values, $map, 'class="custom-select" size="1"', 'value', 'text', $value );
		return JHTML::_('select.groupedlist', $this->values, $map, array('list.attr'=>'class="custom-select"', 'group.id' => 'id', 'list.select' => array($value)) );
	}

	function check($name,$template) {
		if($name == '' || in_array($name, $this->default) || strpos($name, 'plg.') !== false)
			return true;
		$values = $this->getLayout($template);
		return in_array($name,$values);
	}

	function getLayout($template = '') {
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		static $values = null;
		if($values !== null)
			return $values;
		$client	= JApplicationHelper::getClientInfo(0); // 0: Front client
		$tplDir = $client->path.DS.'templates'.DS;
		$values = array();
		if(empty($template)) {
			$templates = JFolder::folders($tplDir);
			if(empty($templates))
				return null;
		} else {
			$templates = array($template);
		}
		$groupAdded = false;
		foreach($templates as $tpl) {
			$t = $tplDir.$tpl.DS.'html'.DS.HIKASHOP_COMPONENT.DS;
			if(!JFolder::exists($t))
				continue;
			$folders = JFolder::folders($t);
			if(empty($folders))
				continue;
			foreach($folders as $folder) {
				$files = JFolder::files($t.$folder.DS);
				if(empty($files))
					continue;
				foreach($files as $file) {
					if(substr($file,-4) == '.php')
						$file = substr($file,0,-4);
					if(substr($file,0,5) == 'show_' && substr($file,0,14) != 'show_quantity_' && !in_array($file,$this->default)) {
						if(!$groupAdded) {
							$values[] = '#'.$tpl;
							$groupAdded = true;
						}
						$values[] = $file;
					}
				}
			}
		}
		return $values;
	}

	public function displaySingle($map, $value, $display = '', $root = 0, $delete = false) {
		if(empty($this->nameboxType))
			$this->nameboxType = hikashop_get('type.namebox');

		return $this->nameboxType->display(
			$map,
			$value,
			hikashopNameboxType::NAMEBOX_SINGLE,
			'product',
			array(
				'delete' => $delete,
				'root' => $root,
				'displayFormat' => $display,
				'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
			)
		);
	}

	public function displayMultiple($map, $values, $display = '', $root = 0) {
		if(empty($this->nameboxType))
			$this->nameboxType = hikashop_get('type.namebox');

		return $this->nameboxType->display(
			$map,
			$values,
			hikashopNameboxType::NAMEBOX_MULTIPLE,
			'product',
			array(
				'delete' => true,
				'root' => $root,
				'sort' => true,
				'displayFormat' => $display,
				'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
			)
		);
	}
}
