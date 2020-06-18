<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2020 HIKARI SOFTWARE. All rights reserved.
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

	public function form() {
		$app = JFactory::getApplication();
		$doc = JFactory::getDocument();

		$fieldid = hikashop_getCID('field_id');
		$fieldsClass = hikashop_get('class.field');
		if(!empty($fieldid)) {
			$field = $fieldsClass->getField($fieldid);
			$data = null;
			$allFields = $fieldsClass->getFields('', $data, $field->field_table);
			$config = hikashop_config();
			if($field->field_table == "order" && $field->field_frontcomp && !strpos($config->get('checkout_workflow', ''), '"task":"fields"')) {
				$app->enqueueMessage(JText::_('BEWARE_FIELD_DISPLAY_ON_CHECKOUT'), 'warning');
			}
		} else {
			$field = new stdClass();
			if(hikashop_level(1)) {
				$field->field_table = $app->getUserStateFromRequest($this->paramBase.'.filter_table', 'filter_table', 'product', 'string');
			} else {
				$field->field_table = 'address';
			}
			$field->field_published = 1;
			$field->field_type = 'text';
			$field->field_backend = 1;
			$allFields = null;
		}
		$this->assignRef('allFields', $allFields);

		$fieldTitle = '';
		if(!empty($field->field_id))
			$fieldTitle = ' : '.$field->field_namekey;
		hikashop_setTitle(JText::_('FIELD').$fieldTitle, 'check-square', 'field&task=edit&field_id='.$fieldid);

		hikashop_loadJslib('jquery');
		hikashop_loadJsLib('tooltip');

		$script = '
function addLine() {
	window.hikashop.dupRow("hikashop_field_values_table_template", {
		"TITLE":"field_values[title][]",
		"VALUE":"field_values[value][]",
		"DISABLED":"field_values[disabled][]"
	})
}
function setVisible(value) {
	var el = document.getElementById(\'category_field\');
	if(!el) return;
	el.style.display = (value == "product" || value == "item" || value == "category") ? "" : "none";
}
';
		$doc->addScriptDeclaration($script);

		$this->toolbar = array(
			array('name' => 'group', 'buttons' => array( 'apply', 'save')),
			'cancel',
			'|',
			array('name' => 'pophelp', 'target' => 'field-form')
		);

		$this->assignRef('field', $field);
		$this->assignRef('fieldsClass', $fieldsClass);

		$fieldType = hikashop_get('type.fields');
		$this->assignRef('fieldtype', $fieldType);

		$zoneType = hikashop_get('type.zone');
		$this->assignRef('zoneType', $zoneType);

		$allowType = hikashop_get('type.allow');
		$this->assignRef('allowType', $allowType);

		$displayOptions = array();
		$this->assignRef('displayOptions', $displayOptions);

		$tabletype = hikashop_get('type.table');
		$tabletype->load();
		if(count($tabletype->values) > 2)
			$this->assignRef('tabletype', $tabletype);

		if(hikashop_level(2)) {
			$limitParent = hikashop_get('type.limitparent');
			$this->assignRef('limitParent',$limitParent);

			if(!empty($field->field_options['product_id'])) {
				$product = hikashop_get('class.product');
				$element = $product->get((int)$field->field_options['product_id']);
				$this->assignRef('element', $element);
			}
		}

		$categories = array();
		if(isset($this->field->field_categories)) {
			$this->field->field_categories = $this->field->field_categories;
			$this->categories = explode(',', trim($this->field->field_categories, ','));

			if(!empty($this->categories)) {
				foreach($this->categories as $k => $cat) {
					if(!isset($categories[$k]))
						$categories[$k] = new stdClass();
					$categories[$k]->category_id = $cat;
				}

				hikashop_toInteger($this->categories);

				$db = JFactory::getDBO();
				$db->setQuery('SELECT * FROM '.hikashop_table('category').' WHERE category_id IN ('.implode(',', $this->categories).')');
				$cats = $db->loadObjectList('category_id');

				foreach($this->categories as $k => $cat) {
					if(!empty($cats[$cat])) {
						$categories[$k]->category_name = $cats[$cat]->category_name;
					} else {
						$categories[$k]->category_name = JText::_('CATEGORY_NOT_FOUND');
					}
				}
			}
			$this->categories = $categories;
		}

		if(!empty($this->field->field_display) && is_string($this->field->field_display)) {
			$fields_display = explode(';', trim($this->field->field_display, ';'));
			$this->field->field_display = new stdClass();
			foreach($fields_display as $f) {
				if(empty($f) || strpos($f, '=') === false)
					continue;
				list($k,$v) = explode('=', $f, 2);
				$this->field->field_display->$k = $v;
			}
		}
		if(!isset($this->field->field_display))
			$this->field->field_display = new stdClass();

		JHTML::_('behavior.modal');
		$popup = hikashop_get('helper.popup');
		$this->assignRef('popup', $popup);

		$nameboxType = hikashop_get('type.namebox');
		$this->assignRef('nameboxType', $nameboxType);

		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$obj =& $this;
		$app->triggerEvent('onCustomfieldEdit', array(&$field, &$obj));
	}

	public function listing() {
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$config =& hikashop_config();
		$filter = '';

		$tableType = hikashop_get('type.table');
		$tableType->load();
		if(hikashop_level(1) || count($tableType->values) > 2)
			$this->assignRef('tabletype', $tableType);

		$selectedType = '';
		if(hikashop_level(1)) {
			$selectedType = $app->getUserStateFromRequest($this->paramBase . '.filter_table', 'filter_table', '', 'string');
			if(!empty($selectedType) && isset($tableType->values[$selectedType])) {
				$filter = ' WHERE f.field_table = '.$db->Quote($selectedType);
			} else {
				$selectedType = '';
			}
		} else {
			$filter = ' WHERE (f.field_table = \'address\' OR f.field_table LIKE \'plg.%\')';
		}
		$this->assignRef('selectedType', $selectedType);

		$query = 'SELECT f.* FROM '.hikashop_table('field').' AS f ' . $filter . ' ORDER BY f.field_table ASC, f.field_ordering ASC';
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		$this->assignRef('rows',$rows);

		$total = count($rows);
		$pagination = hikashop_get('helper.pagination', $total, 0, $total);
		$this->assignRef('pagination', $pagination);

		hikashop_setTitle(JText::_('FIELDS'),'check-square','field');

		$manage = hikashop_isAllowed($config->get('acl_field_manage', 'all'));
		$this->assignRef('manage', $manage);

		$this->toolbar = array(
			array('name'=>'addNew','display' => $manage),
			array('name'=>'editList','display' => $manage),
			array('name'=>'deleteList','display' => hikashop_isAllowed($config->get('acl_field_delete', 'all'))),
			'|',
			array('name' => 'pophelp', 'target' => 'field-listing'),
			'dashboard'
		);


		$toggle = hikashop_get('helper.toggle');
		$this->assignRef('toggleClass',$toggle);

		$fieldsType = hikashop_get('type.fields');
		$fieldsType->load();
		$this->assignRef('fieldtype', $fieldsType);

		$fieldClass = hikashop_get('class.field');
		$this->assignRef('fieldsClass', $fieldClass);
	}

	protected function loadCfgLng() {
		static $loaded = false;
		if($loaded)
			return;
		$lg = JFactory::getLanguage();
		$lg->load('com_hikashop_config', JPATH_SITE);
		$loaded = true;
	}

	public function getDoc($key) {
		$this->loadCfgLng();
		$namekey = 'HK_CONFIG_' . strtoupper(trim($key));
		$ret = JText::_($namekey);
		if($ret == $namekey) {
			return '';
		}
		return $ret;
	}

	public function docFieldTip($key) {
		if(!empty($this->field->field_table))
			$key = 'field_'.str_replace(array('.','-'),'_', $this->field->field_table).'_'. $key;
		else
			$key = 'field_'.$key;

		$ret = $this->getDoc($key);
		if(empty($ret))
			return '';
		return 	' data-toggle="hk-tooltip" data-title="'.htmlspecialchars($ret, ENT_COMPAT, 'UTF-8').'"';
	}
}
