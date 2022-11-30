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
class modulesmarketViewmodulesmarket extends hikamarketView {

	const ctrl = 'modules';
	const name = 'MODULE';
	const icon = 'module';

	public function display($tpl = null, $params = null) {
		$this->paramBase = HIKAMARKET_COMPONENT . '.' . $this->getName();
		$fct = $this->getLayout();
		if(method_exists($this, $fct)) {
			if($this->$fct($params) === false)
				return false;
		}
		parent::display($tpl);
	}

	private function assignTypes() {
		JHTML::_('behavior.modal');

		$modules = array(
			'colorType' => 'type.color',
			'listType' => 'shop.type.list',
			'contentType' => 'type.menu_content',
			'layoutType' => 'type.menu_layout',
			'orderdirType' => 'shop.type.orderdir',
			'orderType' => 'shop.type.order',
			'itemlayoutType' => 'type.itemlayout',
			'popup' => 'shop.helper.popup',
		);
		foreach($modules as $k => $module) {
			$element = hikamarket::get($module);
			$this->assignRef($k, $element);
			unset($element);
		}

		$this->toolbar = array(
			'save',
			'apply',
			'cancel',
			'|',
			array('name' => 'pophelp', 'target' => self::ctrl.'-form')
		);
		if(!empty($this->toolbarJoomlaMenu)) {
			array_unshift($this->toolbar, '|');
			array_unshift($this->toolbar, $this->toolbarJoomlaMenu);
		}
	}

	protected function getModuleData($cid) {
		$config = hikamarket::config();
		$shopConfig = hikamarket::config(false);

		if(empty($cid)) {
			$element = new stdClass();
			$element->id = 0;
			$element->module = 'mod_hikamarket';
			$element->hikamarket_params = $shopConfig->get('default_params');
			$default = array(
				'content_type' => 'vendor',
				'link_to_vendor_page' => '1',
				'border_visible' => true,

				'layout_type' => 'inherit',
				'columns' => '',
				'limit' => '',
				'random' => '-1',
				'order_dir' => 'inherit',
				'filter_type' => 2,

				'vendor_order' => 'inherit',
				'show_vote' => '-1',

				'div_item_layout_type' => 'inherit',
				'background_color' => '',
				'margin' => '',
				'border_visible' => '-1',
				'rounded_corners' => '-1',
				'text_center' => '-1',

				'ul_class_name' => '',
			);
			foreach($default as $k => $v) {
				$element->hikamarket_params[$k] = $v;
			}
		} else {
			$modulesClass = hikamarket::get('class.modules');
			$element = $modulesClass->get($cid);

			if(!empty($element->content_type) && !in_array($element->content_type, array('vendor'))) {
				$app = JFactory::getApplication();
				$app->enqueueMessage(JText::_('HIKAM_MENU_TYPE_NOT_SUPPORTED'), 'error');
				$url = JRoute::_('index.php?option=com_modules&task=module.edit&id='.$cid, false);
				$app->redirect($url);
			}

			if(!isset($element->hikamarket_params['link_to_vendor_page']))
				$element->hikamarket_params['link_to_vendor_page'] = '1';
		}

		if(!isset($element->hikamarket_params['layout_type']))
			$element->hikamarket_params['layout_type'] = 'div';

		$element->hikamarket_params['content_type'] = 'vendor';

		return $element;
	}

	public function form() {
		$cid = hikamarket::getCID('id');
		$element = $this->getModuleData($cid);

		if(!empty($cid)) {
			$task = 'edit';
			$control = 'config[params_' . $cid . ']';

			$url = JRoute::_('index.php?option=com_modules&task=module.edit&id='.$element->id);
			$this->toolbarJoomlaMenu = array(
				'name' => 'link',
				'icon' => 'upload',
				'alt' => JText::_('JOOMLA_MODULE_OPTIONS'),
				'url' => $url
			);
		} else {
			$task = 'add';
			$control = 'config[params_0]';
		}

		$this->assignRef('element', $element);
		$this->assignRef('control', $control);
		$this->assignTypes();

		hikamarket::setTitle(JText::_(self::name), self::icon, self::ctrl . '&task=' . $task . '&cid[]=' . $cid);
	}

	public function options(&$params) {
		$modules = array(
			'colorType' => 'type.color',
			'listType' => 'shop.type.list',
			'contentType' => 'type.menu_content',
			'layoutType' => 'type.menu_layout',
			'orderdirType' => 'shop.type.orderdir',
			'orderType' => 'shop.type.order',
			'itemlayoutType' => 'type.itemlayout',
		);
		foreach($modules as $k => $module) {
			$element = hikamarket::get($module);
			$this->assignRef($k, $element);
			unset($element);
		}

		$this->name = str_replace('[]', '', $params->get('name'));
		$this->id = str_replace(array('][', '[', ']'), array('_', '_', ''), $this->name);

		$this->element = $params->get('value');
		$this->type = $params->get('type');
		$this->menu = $params->get('menu');

		$cid = $params->get('cid', 0);
		if(empty($cid) || empty($this->element)) {
			$menu = $this->getModuleData($cid);
			$this->element = $menu->hikamarket_params;
		}
	}

	public function listing() {
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		hikamarket::setTitle(JText::_(self::name), self::icon, self::ctrl);

		$cfg = array(
			'table' => 'joomla.modules',
			'main_key' => 'module_id',
			'order_sql_value' => 'a.id'
		);

		$pageInfo = $this->getPageInfo($cfg['order_sql_value']);

		$filters = array(
			'module = \'mod_hikamarket\'',
			'published >= 0'
		);
		$searchMap = array(
			'module',
			'title'
		);
		$order = '';

		$this->processFilters($filters, $order, $searchMap);

		$query = 'FROM '.hikamarket::table($cfg['table']).' AS a '.$filters.$order;
		$db->setQuery('SELECT * '.$query, (int)$pageInfo->limit->start, (int)$pageInfo->limit->value);

		$rows = $db->loadObjectList();
		$this->assignRef('rows', $rows);

		$db->setQuery('SELECT COUNT(*) '.$query);
		$pageInfo->elements = new stdClass();
		$pageInfo->elements->total = $db->loadResult();
		$pageInfo->elements->page = count($rows);

		$toggleClass = hikamarket::get('helper.toggle');
		$this->assignRef('toggleClass', $toggleClass);

		$config = hikamarket::config();
		$shopConfig = hikamarket::config(false);

		$manage = hikamarket::isAllowed($shopConfig->get('acl_modules_manage', 'all'));
		$this->assignRef('manage', $manage);

		$this->toolbar = array(
			array('name' => 'addNew', 'display' => !HIKASHOP_J30 && $manage),
			array('name' => 'editList','display' => $manage),
			array('name' => 'deleteList', 'display' => hikamarket::isAllowed($shopConfig->get('acl_modules_delete', 'all'))),
			'|',
			array('name' => 'pophelp', 'target' => self::ctrl.'-listing'),
			'dashboard'
		);

		$this->getPagination();
		$this->getOrdering('a.ordering');
	}
}
