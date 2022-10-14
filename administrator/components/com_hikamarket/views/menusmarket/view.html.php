<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class menusmarketViewmenusmarket extends hikamarketView {

	const ctrl = 'menus';
	const name = 'MENU';
	const icon = 'fa-ellipsis-v';

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

	protected function getMenuData($cid) {
		$config = hikamarket::config();
		$shopConfig = hikamarket::config(false);
		if(empty($cid)) {
			$element = new stdClass();
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
			$menusClass = hikamarket::get('class.menus');
			$element = $menusClass->get($cid);

			if(!empty($element->content_type) && !in_array($element->content_type, array('vendor'))) {
				$app = JFactory::getApplication();
				$app->enqueueMessage(JText::_('HIKAM_MENU_TYPE_NOT_SUPPORTED'), 'error');
				$url = JRoute::_('index.php?option=com_menus&task=item.edit&id='.$cid, false);

				$app->redirect($url);
			}

			if(!isset($element->hikamarket_params['link_to_vendor_page']))
				$element->hikamarket_params['link_to_vendor_page'] = '1';
		}

		if(!isset($element->hikamarket_params['layout_type']))
			$element->hikamarket_params['layout_type'] = 'div';

		return $element;
	}

	public function form() {
		$cid = hikamarket::getCID('id');
		$element = $this->getMenuData($cid);

		if(!empty($cid)) {
			$task = 'edit';
			$control = 'config[menu_' . $cid . ']';

			$url = JRoute::_('index.php?option=com_menus&task=item.edit&id='.$element->id);
			$this->toolbarJoomlaMenu = array(
				'name' => 'link',
				'icon' => 'upload',
				'alt' => JText::_('JOOMLA_MENU_OPTIONS'),
				'url' => $url
			);
		} else {
			$task = 'add';
			$control = 'config[menu_0]';
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
			$menu = $this->getMenuData($cid);
			$this->element = $menu->hikamarket_params;
		}
	}

	public function listing() {
		$app = JFactory::getApplication();

		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->search = $app->getUserStateFromRequest($this->paramBase.'.search', 'search', '', 'string');
		$pageInfo->filter->order->value = $app->getUserStateFromRequest($this->paramBase.'.filter_order', 'filter_order', 'id', 'cmd');
		$pageInfo->filter->order->dir = $app->getUserStateFromRequest($this->paramBase.'.filter_order_Dir', 'filter_order_Dir', 'desc', 'word');

		$db = JFactory::getDBO();
		$query = 'SELECT extension_id FROM ' . hikamarket::table('extensions', false) . ' WHERE type=\'component\' AND element=\''.HIKAMARKET_COMPONENT.'\'';
		$db->setQuery($query, 0, 1);
		$filters = array(
			'(component_id = ' . $db->loadResult() . ' OR (component_id = 0 AND link LIKE \'%option='.HIKAMARKET_COMPONENT.'%\'))',
			'type = \'component\'',
			'client_id = 0',
			'published>-2'
		);

		$searchMap = array(
			'alias',
			'link',
			'name'
		);

		if(!empty($pageInfo->search)) {
			$searchVal = '\'%' . $db->escape(HikaStringHelper::strtolower($pageInfo->search ), true) . '%\'';
			$filters[] =  implode(' LIKE ' . $searchVal . ' OR ', $searchMap) . ' LIKE ' . $searchVal;
		}

		$order = '';
		if(!empty($pageInfo->filter->order->value))
			$order = ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;

		if(!empty($filters)) {
			$filters = ' WHERE (' . implode(') AND (', $filters) . ')';
		} else {
			$filters = '';
		}

		$query = ' FROM '.hikamarket::table('menu', false) . ' ' . $filters . $order;
		$db->setQuery('SELECT *' . $query);
		$rows = $db->loadObjectList();
		if(!empty($pageInfo->search))
			$rows = hikamarket::search($pageInfo->search, $rows, 'id');

		$db->setQuery('SELECT COUNT(*)' . $query);
		$pageInfo->elements = new stdClass();
		$pageInfo->elements->total = $db->loadResult();
		$pageInfo->elements->page = count($rows);

		$toggleClass = hikamarket::get('helper.toggle');
		$this->assignRef('toggleClass', $toggleClass);

		$config = hikamarket::config();
		$shopConfig = hikamarket::config(false);

		$unset = array();
		foreach($rows as $k => $row) {
			if(strpos($row->link, 'view=vendor') !== false && strpos($row->link, 'layout=listing') !== false) {
				$rows[$k]->hikamarket_params = $config->get('menu_' . $row->id);
				$rows[$k]->hikamarket_params['content_type'] = 'vendor';
			} else {
				$unset[] = $k;
				continue;
			}
			if(empty($rows[$k]->hikamarket_params)) {
				$rows[$k]->hikamarket_params = $shopConfig->get('default_params');
			}

			$rows[$k]->content_type = $rows[$k]->hikamarket_params['content_type'];
		}

		foreach($unset as $u) {
			unset($rows[$u]);
		}

		$this->assignRef('rows', $rows);
		$this->assignRef('pageInfo', $pageInfo);

		hikamarket::setTitle(JText::_(self::name), self::icon, self::ctrl);

		$manage = hikamarket::isAllowed($shopConfig->get('acl_menus_manage', 'all'));
		$this->assignRef('manage', $manage);

		$this->toolbar = array(
			array('name' => 'editList','display' => $manage),
			array('name' => 'deleteList', 'display' => hikamarket::isAllowed($shopConfig->get('acl_menus_delete', 'all'))),
			'|',
			array('name' => 'pophelp', 'target' => self::ctrl.'-listing'),
			'dashboard'
		);
	}
}
