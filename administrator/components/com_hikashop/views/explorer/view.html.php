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
class ExplorerViewExplorer extends hikashopView {
	public function display($tpl = null, $task = '', $defaultId = '', $popup = '', $type = '') {
		$app = JFactory::getApplication();
		$doc = JFactory::getDocument();
		$jconfig = JFactory::getConfig();
		$user = JFactory::getUser();
		$database	= JFactory::getDBO();

		$translationHelper = hikashop_get('helper.translation');

		$select = 'SELECT a.*';
		$table = ' FROM '.hikashop_table('category').' AS a';
		if(hikashop_isClient('administrator') && $translationHelper->isMulti()) {
			$locale = $user->getParam('language');
			if(empty($locale)) {
				if(HIKASHOP_J30) {
					$locale = $jconfig->get('language');
				} else {
					$locale = $jconfig->getValue('config.language');
				}
			}
			$lgid = $translationHelper->getId($locale);

			if($translationHelper->falang){
				$select .= ',b.value';
				$trans_table = 'falang_content';
				$table .= ' LEFT JOIN '.hikashop_table($trans_table, false).' AS b ON a.category_id = b.reference_id AND b.reference_table = \'hikashop_category\' AND b.reference_field = \'category_name\' AND b.published = 1 AND language_id = '.$lgid;
			}
		}

		$where = '';
		if(!empty($type)) {
			$where = ' WHERE a.category_type IN ('.$database->Quote($type).',\'root\')';
		}

		$database->setQuery($select.$table.$where.' ORDER BY a.category_parent_id ASC, a.category_ordering ASC');
		$elements = $database->loadObjectList();
		$this->assignRef('elements', $elements);

		if(!is_numeric($defaultId)) {
			$categoryClass = hikashop_get('class.category');
			$categoryClass->getMainElement($defaultId);
		}

		foreach($elements as $k => $element) {
			if(empty($element->value)) {
				$val = str_replace(array(' ', ','), '_', strtoupper($element->category_name));
				$element->value = JText::_($val);
				if($val == $element->value) {
					$element->value = $element->category_name;
				}
			}

			$elements[$k]->category_name = $element->value;

			if($element->category_namekey == 'root') {
				if(empty($defaultId)) {
					$defaultId=$element->category_id;
				}
				$elements[$k]->category_parent_id = -1;
			}
		}

		$this->assignRef('defaultId', $defaultId);
		$this->assignRef('popup', $popup);
		$this->assignRef('task', $task);
		$this->assignRef('type', $type);

		$function = $this->getLayout();
		if(method_exists($this,$function))
			$this->$function();
		parent::display($tpl);
	}
}
