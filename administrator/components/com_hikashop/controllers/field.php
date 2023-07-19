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
class FieldController extends hikashopController {
	var $pkey = 'field_id';
	var $table = 'field';
	var $groupMap = '';
	var $groupVal = '';
	var $orderingMap = 'field_ordering';

	public function __construct($config = array()){
		parent::__construct($config);
		$this->modify_views[] = 'state';
		$this->modify_views[] = 'parentfield';
		$this->modify_views[] = 'add_value';
		$this->modify_views[] = 'edit_translation';
		$this->modify[] = 'save_value';
		$this->modify[] = 'save_translation';
		$this->modify[] = 'delete_columns';
	}

	function add_value(){
		hikaInput::get()->set( 'layout', 'add_value'  );
		return parent::display();
	}


	function save_value(){
		JSession::checkToken() || die('Invalid Token');

		$fieldClass = hikashop_get('class.field');
		$input = hikaInput::get();
		$fieldClass->addValue($input->getInt('field_id'), $input->getVar('value_title'), $input->getVar('value_value'), $input->getVar('value_disabled'));
		hikaInput::get()->set( 'layout', 'save_value'  );
		return parent::display();
	}

	public function edit_translation(){
		hikaInput::get()->set('layout', 'edit_translation');
		return parent::display();
	}

	public function save_translation(){
		$field_id = hikashop_getCID('field_id');
		$fieldClass = hikashop_get('class.field');
		$element = $fieldClass->get($field_id);
		if(!empty($element->field_id)){
			$translationHelper = hikashop_get('helper.translation');
			$translationHelper->getTranslations($element);
			$translationHelper->handleTranslations('field',$element->field_id,$element);
			$translationHelper->saveOverrides();
		}
		$document= JFactory::getDocument();
		$document->addScriptDeclaration('window.top.hikashop.closeBox();');
	}



	function store($new = false) {
		JSession::checkToken() || die('Invalid Token');

		$app = JFactory::getApplication();

		$fieldClass = hikashop_get('class.field');
		$status = $fieldClass->saveForm();
		if($status) {
			if(!HIKASHOP_J30)
				$app->enqueueMessage(JText::_('HIKASHOP_SUCC_SAVED'), 'success');
			else
				$app->enqueueMessage(JText::_('HIKASHOP_SUCC_SAVED'));

			$translationHelper = hikashop_get('helper.translation');
			if($translationHelper->isMulti(true) && $translationHelper->falang) {
				$updateHelper = hikashop_get('helper.update');
				$updateHelper->addJoomfishElements(false);
			}
		} else {
			$app->enqueueMessage(JText::_( 'ERROR_SAVING' ), 'error');
			if(!empty($fieldClass->errors)) {
				foreach($fieldClass->errors as $oneError) {
					$app->enqueueMessage($oneError, 'error');
				}
			}
		}
	}

	public function remove() {
		JSession::checkToken() || die('Invalid Token');

		$cids = hikaInput::get()->get('cid', array(), 'array');

		$app = JFactory::getApplication();

		$fieldClass = hikashop_get('class.field');
		$num = 0;
		$namekeys = [];
		$tables = [];
		foreach($cids as $cid) {
			$field = $fieldClass->get($cid);
			if($fieldClass->delete($cid)) {
				$num++;
				if($field->field_table == 'item') {
					$namekeys[] = $field->field_namekey;
					$tables[] = 'cart_product';
					$namekeys[] = $field->field_namekey;
					$tables[] = 'order_product';
				}elseif($field->field_table != 'contact' && $field->field_type != 'customtext') {
					$namekeys[] = $field->field_namekey;
					$tables[] = $field->field_table;
				}
			} else {
				$app->enqueueMessage(JText::sprintf('THE_FIELD_X_COULD_NOT_BE_DELETED', $field->field_realname), 'error');
			}
		}

		if($num) {
			$app->enqueueMessage(JText::sprintf('SUCC_DELETE_ELEMENTS', $num), 'success');
			if(count($namekeys)) {
				$url = hikashop_completeLink('field&task=delete_columns&columns='.implode('|', $namekeys).'&tables='.implode('|', $tables).'&'.hikashop_getFormToken().'=1');
				$app->enqueueMessage(JText::sprintf('CLICK_HERE_IF_YOU_ALSO_WANT_TO_DELETE_THE_DATA_OF_THESE_FIELDS_IN_THE_DATABASE', $url), 'info');
			}
		}

		return $this->listing();
	}

	public function delete_columns() {
		JSession::checkToken('request') || die('Invalid Token');

		$namekeys = explode('|', hikaInput::get()->getString('columns'));
		$tables = explode('|', hikaInput::get()->getString('tables'));

		$app = JFactory::getApplication();

		if(!count($namekeys)) {
			$app->enqueueMessage('No columns to delete !', 'error');
		}
		if(!count($tables)) {
			$app->enqueueMessage('No tables !', 'error');
		}
		if(count($namekeys) != count($tables)) {
			$app->enqueueMessage('tables and columns mismatch', 'error');
		}

		$databaseHelper = hikashop_get('helper.database');
		$databaseHelper->loadStructure();

		foreach($namekeys as $k => $namekey) {
			if(!isset($databaseHelper->structure['#__hikashop_'.$tables[$k]][$namekey])) {
				$result = $databaseHelper->removeColumn($tables[$k], $namekey);
				if($result) {
					$app->enqueueMessage(JText::sprintf('DATA_FOR_CUSTOM_FIELD_X_IN_TABLE_Y_SUCCESSFULLY', $namekey, $tables[$k]), 'success');
				}
			}
		}

		return $this->listing();
	}

	function state(){
		hikashop_nocache();

		$namekey = hikaInput::get()->getCmd('namekey', '');
		if(empty($namekey))
			exit;

		$field_namekey = hikaInput::get()->getCmd('field_namekey', '');
		if(empty($field_namekey))
			$field_namekey = 'address_state';

		$field_id = hikaInput::get()->getCmd('field_id', '');
		if(empty($field_id))
			$field_id = 'address_state';

		$field_type = hikaInput::get()->getCmd('field_type', '');
		if(empty($field_type))
			$field_type = 'address';

		$countryType = hikashop_get('type.country');
		echo $countryType->displayStateDropDown($namekey, $field_id, $field_namekey, $field_type);
		exit;
	}

	public function parentfield() {
		hikashop_nocache();

		$type = hikaInput::get()->getVar('type');
		$namekey = hikaInput::get()->getVar('namekey');
		$value = hikaInput::get()->getString('value');
		if(empty($namekey) || empty($type))
			exit;

		$fieldClass = hikashop_get('class.field');
		$field = $fieldClass->getField($namekey,$type);

		if($field->field_type != 'zone' || empty($field->field_options['zone_type']) || $field->field_options['zone_type'] != 'state') {
			echo $fieldClass->display($field,$value,'field_options[parent_value]', false, '', true);
			exit;
		}

		$null = null;
		$fields = $fieldClass->getFields('', $null, $type);
		$countryField = null;
		foreach($fields as $brotherField) {
			if($brotherField->field_type == 'zone' && !empty($brotherField->field_options['zone_type']) && $brotherField->field_options['zone_type'] == 'country') {
				$countryField = $brotherField;
				break;
			}
		}

		if($countryField) {
			$baseUrl = JURI::base().'index.php?option=com_hikashop&ctrl=field&task=state&tmpl=component';
			$currentUrl = strtolower(hikashop_currentUrl());

			$s = (substr($currentUrl, 0, 8) == 'https://') ? 9 : 8;
			$domain = substr($currentUrl, 0, strpos($currentUrl, '/', $s));

			$s = (substr($baseUrl, 0, 8) == 'https://') ? 9 : 8;
			$baseUrl = $domain . substr($baseUrl, strpos($baseUrl, '/', $s));

			$countryField->field_url = $baseUrl . '&';
			echo $fieldClass->display($countryField, $countryField->field_default, 'field_options_parent_value', false, '', true);
		}
		echo $fieldClass->display($field,$value, 'field_options[parent_value]', false, '', true);

		exit;
	}

	public function findList() {
		$search = hikaInput::get()->getVar('search', '');
		$start = hikaInput::get()->getInt('start', 0);
		$displayFormat = hikaInput::get()->getVar('displayFormat', '');

		$options = array();

		if(!empty($displayFormat))
			$options['displayFormat'] = $displayFormat;
		if($start > 0)
			$options['page'] = $start;

		$nameboxType = hikashop_get('type.namebox');
		$elements = $nameboxType->getValues($search, 'field', $options);
		echo json_encode($elements);
		exit;
	}

}
