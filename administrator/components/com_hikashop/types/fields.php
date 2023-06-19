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
class hikashopFieldsType {
	var $allValues;
	var $externalValues;
	var $externalOptions;
	var $options;

	function __construct() {
		$this->externalValues = null;
		$this->externalOptions = null;
		$this->options = array();
	}

	function load($type = '') {
		$this->allValues = array(
			'text' => array(
				'name' => JText::_('FIELD_TEXT'),
				'options' => array('size','required','default','columnname','filtering','maxlength','readonly','placeholder','translatable','attribute','regex', 'display_format')
			),
			'link' => array(
				'name' => JText::_('LINK'),
				'options' => array('size','required','default','columnname','filtering','maxlength','readonly','target_blank','attribute','regex')
			),
			'textarea' => array(
				'name' => JText::_('FIELD_TEXTAREA'),
				'options' => array('cols','rows','required','default','columnname','filtering','readonly','maxlength','placeholder','translatable','attribute','regex', 'display_format')
			),
			'wysiwyg' => array(
				'name' => JText::_('WYSIWYG'),
				'options' => array('cols','rows','required','default','columnname','filtering','translatable','regex', 'display_format')
			),
			'radio' => array(
				'name' => JText::_('FIELD_RADIO'),
				'options' => array('multivalues','required','default','columnname','attribute','inline', 'add')
			),
			'checkbox' => array(
				'name' => JText::_('FIELD_CHECKBOX'),
				'options' => array('multivalues','required','default','columnname','attribute','inline', 'add')
			),
			'boolean' => array(
				'name' => JText::_('FIELD_BOOLEAN'),
				'options' => array('required','default','columnname','attribute')
			),
			'singledropdown' => array(
				'name' => JText::_('FIELD_SINGLEDROPDOWN'),
				'options' => array('multivalues','required','default','columnname','attribute', 'add')
			),
			'multipledropdown' => array(
				'name' => JText::_('FIELD_MULTIPLEDROPDOWN'),
				'options' => array('multivalues','size','default','columnname','attribute', 'add')
			),
			'date' => array(
				'name' => JText::_('FIELD_DATE'),
				'options' => array('required','size','default','columnname','allow')
			),
			'zone' => array(
				'name' => JText::_('FIELD_ZONE'),
				'options' => array('required','zone','default','columnname','pleaseselect','attribute')
			),
			'hidden' => array(
				'name' => JText::_('FIELD_HIDDEN'),
				'options' => array('required','default','columnname','filtering','attribute','regex')
			),
		);

		if(hikashop_level(2)) {
			if($type == 'entry'|| empty($type)) {
				$this->allValues['coupon'] = array(
					'name' => JText::_('HIKASHOP_COUPON'),
					'options' => array('size','required','default','columnname')
				);
			}
			$this->allValues['file'] = array(
				'name' => JText::_('HIKA_FILE'),
				'options' => array('required','default','columnname','attribute')
			);
			$this->allValues['image'] = array(
				'name' => JText::_('HIKA_IMAGE'),
				'options' => array('required','default','columnname','attribute')
			);
			$this->allValues['ajaxfile'] = array(
				'name' => JText::_('FIELD_AJAX_FILE'),
				'options' => array('required','default','columnname','allowed_extensions', 'multiple', 'upload_dir', 'max_filesize', 'delete_files')
			);
			$this->allValues['ajaximage'] = array(
				'name' => JText::_('FIELD_AJAX_IMAGE'),
				'options' => array('required','default','columnname','imagesize','allowed_extensions', 'multiple', 'upload_dir', 'max_filesize', 'thumbnail', 'max_dimensions', 'delete_files')
			);
		}
		$this->allValues['customtext'] = array(
			'name' => JText::_('CUSTOM_TEXT'),
			'options' => array('customtext')
		);
		if($this->externalValues == null) {
			$this->externalValues = array();
			$this->externalOptions = array();
			JPluginHelper::importPlugin('hikashop');
			$app = JFactory::getApplication();
			$app->triggerEvent('onFieldsLoad', array( &$this->externalValues, &$this->externalOptions ) );
		}

		if(!empty($this->externalValues)) {
			foreach($this->externalValues as $value) {
				if(substr($value->name,0,4) != 'plg.')
					$value->name = 'plg.'.$value->name;
				$this->allValues[$value->name] = array(
					'name' => $value->text,
					'options' => @$value->options
				);
			}
		}

		foreach($this->allValues as $v) {
			if(!empty($v['options'])) {
				foreach($v['options'] as $o) {
					$this->options[$o] = $o;
				}
			}
		}
	}

	function addJS(){
		$this->load();
		$externalJS = '';
		if(!empty($this->externalValues)){
			foreach($this->externalValues as $value) {
				if(!empty($value->js))
					$externalJS .= "\r\n\t".$value->js;
			}
		}

		$types = array();
		foreach($this->allValues as $k => $v) {
			$t = '"' . $k . '": [';
			if(!empty($v['options'])) {
				$t .= '"' . implode('","', $v['options']) . '"';
			}
			$t.=']';
			$types[] = $t;
		}

		$options = '';
		if(!empty($this->options)) {
			$options = '"' . implode('","', $this->options) . '"';
		}

		$js = '
function updateFieldType() {
	var d = document,
		newType = "",
		key = "",
		el = d.getElementById("fieldtype"),
		hiddenAll = ['.$options.'],
		allTypes = {
			'.implode(",\r\n\t\t\t", $types).'
		};'.$externalJS.'

	if(el)
		newType = el.value;

	for(var i = 0; i < hiddenAll.length; i++) {
		fields_display_blocks(hiddenAll[i], false);
	}

	var hkDisplays = d.querySelectorAll(\'[data-hk-displays]\');
	if(hkDisplays && hkDisplays.length > 0) {
		for(var i = 0; i < hkDisplays.length; i++) {
			var values = hkDisplays[i].getAttribute("data-hk-displays");
			if(!values)
				continue;
			hkDisplays[i].style.display = "none";
		}
	}

	for(var i = 0; i < allTypes[newType].length; i++) {
		fields_display_blocks(allTypes[newType][i], true, hkDisplays);
	}
}
function fields_display_blocks(key, state, hkDisplays) {
	var d = document,
		el = d.getElementById("fieldopt_" + key);
	if(!el) {
		var j = 0;
		el = d.getElementById("fieldopt_" + key + "_" + j);
		while(el) {
			el.style.display = state ? "" : "none";
			j++;
			el = d.getElementById("fieldopt_" + key + "_" + j);
		}
	} else {
		el.style.display = state ? "" : "none";
	}

	var fields = d.querySelectorAll(\'[data-hk-display="\' + key + \'"]\');
	if(fields && fields.length > 0) {
		for(var i = 0; i < fields.length; i++) {
			fields[i].style.display = state ? "" : "none";
		}
	}

	if(!state || !hkDisplays || hkDisplays.length == 0)
		return;
	for(var i = 0; i < hkDisplays.length; i++) {
		var values = hkDisplays[i].getAttribute("data-hk-displays");
		if(!values)
			continue;
		values = "," + values + ",";
		if(values.indexOf("," + key + ",") >= 0)
			hkDisplays[i].style.display = "";
	}
}
window.hikashop.ready(function(){updateFieldType();});
';
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration( $js );
	}

	function display($map,$value,$type){
		$this->load($type);
		$this->addJS();

		$this->values = array();
		foreach($this->allValues as $oneType => $oneVal) {
			if($value != 'date' && $oneType == 'date')
				continue;
			$this->values[] = JHTML::_('select.option', $oneType, $oneVal['name']);
		}

		return JHTML::_('select.genericlist', $this->values, $map , 'class="custom-select" size="1" onchange="updateFieldType();"', 'value', 'text', (string)$value, 'fieldtype');
	}
}
