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
class FieldViewField extends hikashopView {

	public $displayView = true;

	function display($tpl = null) {
		$function = $this->getLayout();
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		if(method_exists($this,$function))
			$this->$function();

		if($this->displayView)
			parent::display($tpl);
	}

	public function add_value() {
		$field_id = hikaInput::get()->getInt('field_id');
		$fieldClass = hikashop_get('class.field');
		$this->field = $fieldClass->getField($field_id);
	}

	public function save_value() {
		$field_id = hikaInput::get()->getInt('field_id');
		$fieldClass = hikashop_get('class.field');
		$this->field = $fieldClass->getField($field_id);
		$safeHtmlFilter = JFilterInput::getInstance(array(), array(), 1, 1);
		$this->new = new stdClass();
		$this->new->value = $safeHtmlFilter->clean(hikaInput::get()->getVar('value_value'), 'raw');
		$this->new->title = $safeHtmlFilter->clean(hikaInput::get()->getVar('value_title'), 'raw');
		$this->new->disabled = $safeHtmlFilter->clean(hikaInput::get()->getVar('value_disabled'), 'string');
	}

}
