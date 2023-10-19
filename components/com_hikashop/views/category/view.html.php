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
class CategoryViewCategory extends HikaShopView {
	var $type = 'product';
	var $ctrl = 'category';
	var $nameListing = 'HIKA_CATEGORIES';
	var $nameForm = 'HIKA_CATEGORIES';
	var $icon = 'category';
	var $module = false;
	var $triggerView = true;

	function display($tpl = null, $params = array()) {
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		$this->params = $params;
		if(method_exists($this,$function))
			$this->$function();
		parent::display($tpl);
	}

	function listing() {
		$config =& hikashop_config();
		$this->assignRef('config', $config);

		$app = JFactory::getApplication();
		$database = JFactory::getDBO();

		$module = hikashop_get('helper.module');
		$module->initialize($this);
		$categoryClass = hikashop_get('class.category');

		$this->paramBase .= '_'.$this->params->get('main_div_name');

		$filters = array();
		$catData = null;

		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();

		$defaultParams = $config->get('default_params');
		if(empty($defaultParams['links_on_main_categories']))
			$defaultParams['links_on_main_categories'] = 1;

		$params = array(
			'limit' => '',
			'order_dir' => 'inherit',
			'margin' => '',
			'border_visible' => '-1',
			'div_item_layout_type' => 'inherit',
			'text_center' => '-1',
			'columns' => '',
			'number_of_products' => '-1',
			'background_color' => '',
			'link_to_product_page' => '-1',
			'only_if_products' => '-1',
			'child_display_type' => 'inherit',
			'child_limit' => '',
			'links_on_main_categories' => '-1',
			'layout_type' => 'inherit'
		);

		$data = $this->params->get('data', false);

		if($data === false) {
			$data = new stdClass();
			$data->hk_category = $this->params->get('hk_category', false);
			if(!empty($data->hk_category))
				$data->hk_category = (object)$data->hk_category;
		}

		if(isset($data->hk_category) && is_object($data->hk_category)){
			$categoryId = (int)$this->params->get('category', 0);
			if($categoryId > 0) {
				$cat = $categoryClass->get($categoryId);
				if($cat->category_type == 'manufacturer')
					$this->params->set('content_type', 'manufacturer');
			}
			if(!empty($data->hk_category->category))
				$this->params->set('selectparentlisting', (int)$data->hk_category->category);
		}

		foreach($params as $k => $v) {
			if($this->params->get($k, $v) == $v)
				$this->params->set($k, @$defaultParams[$k]);
		}

		if( (int)$this->params->get('limit') == 0 ) {
			$this->params->set('limit', 1);
		}

		$content_type = $this->params->get('content_type');
		if($content_type=='manufacturer') {
			$content_type = 'manufacturer';
			if(!HIKASHOP_J30 || (HIKASHOP_J30 && !$this->params->get('selectparentlisting',false))) {
				$id = hikaInput::get()->getInt("cid");
				$new_id = 'manufacturer';
				$categoryClass->getMainElement($new_id);
				$this->params->set('selectparentlisting',$new_id);
			}
		} else{
			$content_type = 'product';
		}

		$categoryFromURL = false;
		if($this->params->get('content_synchronize')) {
			if(hikaInput::get()->getString('option','') == HIKASHOP_COMPONENT) {
				if(hikaInput::get()->getString('ctrl','category') == 'product') {
					$product_id = hikashop_getCID('product_id');
					if(!empty($product_id)) {
						$query = 'SELECT category_id FROM '.hikashop_table('product_category').' WHERE product_id='.$product_id;
						$database->setQuery($query);
						$pageInfo->filter->cid = $database->loadColumn();
					}else{
						$pageInfo->filter->cid = $this->params->get('selectparentlisting');
					}
				} elseif(hikaInput::get()->getString('ctrl','category') == 'category') {
					$pageInfo->filter->cid = hikaInput::get()->getInt("cid");
					if(empty($pageInfo->filter->cid))
						$pageInfo->filter->cid = $this->params->get('selectparentlisting');
					else
						$categoryFromURL = true;
				} else {
					$pageInfo->filter->cid = $this->params->get('selectparentlisting');
				}
			} else {
				$pageInfo->filter->cid = $this->params->get('selectparentlisting');
			}
		} else {
			if(empty($this->module)) {
				$pageInfo->filter->cid = hikaInput::get()->getInt("cid");
				if(empty($pageInfo->filter->cid))
					$pageInfo->filter->cid = $this->params->get('selectparentlisting');
				else
					$categoryFromURL = true;
			} else {
				$pageInfo->filter->cid = $this->params->get('selectparentlisting');
			}
		}

		if(HIKASHOP_J30 && $content_type == 'product' && is_numeric($pageInfo->filter->cid)){
			$catData = $categoryClass->get($pageInfo->filter->cid);
			if(!empty($catData) && $catData->category_type == 'manufacturer')
				$content_type = 'manufacturer';
		}

		if(empty($pageInfo->filter->cid)) {
			$pageInfo->filter->cid = 'product';
		}

		$category_selected = '';
		if(!is_array($pageInfo->filter->cid)) {
			$category_selected = '_'.$pageInfo->filter->cid;
			$this->paramBase .= $category_selected;
		}

		if(!empty($pageInfo->filter->cid)) {
			$acl_filters = array();
			hikashop_addACLFilters($acl_filters, 'category_access');
			if($categoryFromURL) {
				$acl_filters[] = 'category_published = 1';
			}
			if(!empty($acl_filters)) {
				if(!is_array($pageInfo->filter->cid)) {
					if(empty($catData))
						$catData = $categoryClass->get($pageInfo->filter->cid);
					if(!empty($catData->category_type))
						$content_type = $catData->category_type;
					$pageInfo->filter->cid = array($database->Quote($pageInfo->filter->cid));
				}

				$acl_filters[] = 'category_type=\''.$content_type.'\'';
				$acl_filters[] = 'category_id IN ('.implode(',',$pageInfo->filter->cid).')';
				$query = 'SELECT category_id FROM '.hikashop_table('category').' WHERE '.implode(' AND ',$acl_filters);
				$database->setQuery($query);
				$pageInfo->filter->cid = $database->loadColumn();
				if(!count($pageInfo->filter->cid) && empty($this->module)) {
					throw new Exception(JText::_('CATEGORY_NOT_FOUND'), 404);
				}
			}
		}

		$this->assignRef('category_selected',$category_selected);
		if($this->params->get('category_order', 'inherit') == 'inherit') {
			if(empty($defaultParams['category_order']) || $defaultParams['category_order'] == 'inherit' || is_numeric($defaultParams['category_order']))
				$defaultParams['category_order'] = 'category_ordering';
			$this->params->set('category_order', $defaultParams['category_order']);
		}

		if(in_array($this->params->get('order_dir','inherit'), array('inherit', ''))) {
			if(empty($defaultParams['order_dir']) || $defaultParams['order_dir'] == 'inherit')
				$defaultParams['order_dir'] = 'ASC';
			$this->params->set('order_dir', $defaultParams['order_dir']);
		}
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir_'.$this->params->get('main_div_name').$category_selected,	$this->params->get('order_dir','ASC'), 'word');
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order_'.$this->params->get('main_div_name').$category_selected,	'a.'.$this->params->get('category_order','category_ordering'), 'cmd');

		if(!in_array(strtoupper($pageInfo->filter->order->dir), array('ASC', 'DESC')))
			$pageInfo->filter->order->dir = 'ASC';

		if($this->params->get('limit', '') == '')
			$this->params->set('limit', $defaultParams['limit']);

		$oldValue = $app->getUserState($this->paramBase.'.list_limit_category');
		if(empty($oldValue))
			$oldValue = $this->params->get('limit');



		if($config->get('redirect_post',0)){
			if(isset($_REQUEST['limit_category'])){
				$pageInfo->limit->value = hikaInput::get()->getInt('limit_category');
			}else {
				$pageInfo->limit->value = $this->params->get('limit');
			}
		} else {
			$pageInfo->limit->value = $app->getUserStateFromRequest($this->paramBase.'.list_limit_category', 'limit_category', $this->params->get('limit'), 'int');
		}

		if($oldValue != $pageInfo->limit->value) {
			hikaInput::get()->set('limitstart_category',0);
		}


		if($config->get('redirect_post',0)){
			$pageInfo->limit->start = 0;
			if(isset($_REQUEST['limitstart_category'])){
				$pageInfo->limit->start = hikaInput::get()->getInt('limitstart_category');
			}
		} else {
			$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart_category', 'limitstart_category', 0, 'int' );
		}

		if(empty($this->module)){
			if($config->get('hikarss_format') != 'none'){
				$doc_title = $config->get('hikarss_name','');
				if(empty($doc_title)){
					if(!isset($catData)){
						if(is_array($pageInfo->filter->cid)){
							$cat = reset($pageInfo->filter->cid);
						}else{
							$cat = $pageInfo->filter->cid;
						}
						$catData = $categoryClass->get($cat);
					}
					if($catData) $doc_title = $catData->category_name;
				}
				$doc = JFactory::getDocument();
				if($config->get('hikarss_format') != 'both'){
					$link	= '&format=feed&limitstart=';
					$attribs = array('type' => 'application/rss+xml', 'title' => $doc_title.' RSS 2.0');
					$doc->addHeadLink(JRoute::_($link.'&type='.$config->get('hikarss_format')), 'alternate', 'rel', $attribs);
				}else{
					$link	= '&format=feed&limitstart=';
					$attribs = array('type' => 'application/rss+xml', 'title' => $doc_title.' RSS 2.0');
					$doc->addHeadLink(JRoute::_($link.'&type=rss'), 'alternate', 'rel', $attribs);
					$attribs = array('type' => 'application/atom+xml', 'title' => $doc_title.' Atom 1.0');
					$doc->addHeadLink(JRoute::_($link.'&type=atom'), 'alternate', 'rel', $attribs);
				}
			}

			$cid = hikaInput::get()->getInt("cid", 0);
			if(empty($cid)) {
				hikaInput::get()->set("no_cid",1);
			}
			if(is_array($pageInfo->filter->cid)) {
				hikaInput::get()->set("cid", reset($pageInfo->filter->cid));
			}else{
				hikaInput::get()->set("cid", $pageInfo->filter->cid);
			}
			hikaInput::get()->set('menu_main_category',$this->params->get('selectparentlisting'));
		}

		$searchMap = array('a.category_name','a.category_description','a.category_id');

		$order = '';
		if(!empty($pageInfo->filter->order->value)){
			$order = ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}
		if($this->params->get('random','-1') == '-1') {
			$this->params->set('random', $defaultParams['random']);
		}
		if($this->params->get('random')) {
			$order = ' ORDER BY RAND()';
		}

		$categoryClass->parentObject =& $this;
		if($this->params->get('filter_type', 2) == 2) {
			$this->params->set('filter_type',$defaultParams['filter_type']);
		}

		$rows = $categoryClass->getChildren($pageInfo->filter->cid,$this->params->get('filter_type'),$filters,$order,$pageInfo->limit->start,$pageInfo->limit->value,true);

		$pageInfo->elements = new stdClass();
		if(!empty($categoryClass->query)){
			$database->setQuery('SELECT COUNT(*) '.$categoryClass->query);
			$pageInfo->elements->total = $database->loadResult();
			$pageInfo->elements->page = count($rows);
		} else {
			$pageInfo->elements->total = 0;
			$pageInfo->elements->page = 0;
		}

		if($pageInfo->elements->page) {
			$ids = array();
			foreach($rows as $key => $row) {
				$ids[(int)$row->category_id] = (int)$row->category_id;
				$categoryClass->addAlias($rows[$key]);
			}

			if($this->params->get('number_of_products', '-1') == '-1') {
				$this->params->set('number_of_products', @$defaultParams['number_of_products']);
			}
			if($this->params->get('only_if_products', '-1') == '-1') {
				$this->params->set('only_if_products', @$defaultParams['only_if_products']);
			}
			if($this->params->get('child_display_type', 'inherit') == 'inherit') {
				$this->params->set('child_display_type', $defaultParams['child_display_type']);
			}

			$number_of_products = $this->params->get('number_of_products', 0) || $this->params->get('only_if_products', 0);

			if($this->params->get('child_display_type') != 'nochild' || $number_of_products) {
				$childs = $categoryClass->getChildren($ids,true,array(),$order,0,0,false);
				if(!empty($childs)) {
					$this->_associateChilds($rows,$childs);
					foreach($childs as $child) {
						if(is_numeric($child)) {
							$ids[$child] = $child;
						} else {
							$ids[(int)$child->category_id] = (int)$child->category_id;
						}
					}
				}
				if($number_of_products) {
					$filters = array();
					if(!$config->get('show_out_of_stock', 1)) {
						$filters[] = 'p.product_quantity != 0';
					}
					$additional_condition = '';
					hikashop_addACLFilters($filters, 'product_access', 'p');
					if(count($filters))
						$additional_condition = ' AND ' . implode(' AND ', $filters);
					if($content_type == 'manufacturer') {
						$query = 'SELECT count(p.product_id) AS number_of_products, p.product_manufacturer_id as category_id '.
							' FROM '.hikashop_table('product').' AS p '.
							' WHERE p.product_published > 0'.$additional_condition.' AND p.product_parent_id = 0 AND p.product_manufacturer_id IN ('.implode(',',$ids).') '.
							' GROUP BY p.product_manufacturer_id';
					} else {
						$query = 'SELECT count(pc.product_id) AS number_of_products, pc.category_id '.
							' FROM '.hikashop_table('product_category').' AS pc '.
							' INNER JOIN '.hikashop_table('product').' AS p ON pc.product_id = p.product_id AND p.product_published > 0'.$additional_condition.' AND p.product_parent_id = 0 '.
							' WHERE pc.category_id IN ('.implode(',',$ids).')'.
							' GROUP BY pc.category_id';
					}
					$database->setQuery($query);
					$counts = $database->loadObjectList('category_id');
					$this->_getCount($rows, $counts);
				}
			}
		}

		$this->assignRef('modules',$this->modules);

		$image = hikashop_get('helper.image');
		$this->assignRef('image', $image);

		$this->assignRef('category_image',$category_image);
		$menu_id = '';
		if(empty($this->module)) {
			if(is_array($pageInfo->filter->cid)) {
				$pageInfo->filter->cid = reset($pageInfo->filter->cid);
			}

			$element = $categoryClass->get($pageInfo->filter->cid,true);
			$this->assignRef('element',$element);

			$fieldsClass = hikashop_get('class.field');
			$fields = $fieldsClass->getFields('frontcomp',$element,'category','checkout&task=state');
			$this->assignRef('fieldsClass',$fieldsClass);
			$this->assignRef('fields',$fields);

			$use_module = $this->params->get('use_module_name');
			$title = $this->params->get('page_title');
			if(empty($title)) {
				$title = $this->params->get('title');
			}
			if(empty($use_module) && !empty($element->category_name)) {
				$title = hikashop_translate($element->category_name);
			}
			if(!empty($element->category_page_title)) {
				$page_title = hikashop_translate($element->category_page_title);
			} else {
				$page_title = $title;
			}
			hikashop_setPageTitle($page_title);
			$this->params->set('page_title', $title);

			$doc = JFactory::getDocument();
			if(!empty($element->category_keywords)) {
				$doc->setMetadata('keywords', hikashop_translate($element->category_keywords));
			}
			elseif ($this->params->get('menu-meta_keywords')) {
				$doc->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
			}
			if(!empty($element->category_meta_description)) {
				$doc->setMetadata('description', hikashop_translate($element->category_meta_description));
			}elseif ($this->params->get('menu-meta_description')) {
				$doc->setMetadata('description', $this->params->get('menu-meta_description'));
			}

			$robots = $this->params->get('robots');
			if (!$robots) {
				$jconfig = JFactory::getConfig();
				$robots = $jconfig->get('robots', '');
			}
			if($robots) {
				$doc->setMetadata('robots', $robots);
			}

			$pagination = hikashop_get('helper.pagination', $pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value);
			$pagination->hikaSuffix = '_category';
			$this->assignRef('pagination',$pagination);
			$pagination->addMetaLinks();
			$this->params->set('show_limit',1);

			$pathway = $app->getPathway();

			$categories = $categoryClass->getParents($cid,$this->params->get('selectparentlisting'));
			global $Itemid;
			if(!empty($Itemid)){
				$menu_id = '&Itemid='.$Itemid;
			}

			$one = true;
			if(is_array($categories)) {
				foreach($categories as $category) {
					if($one) {
						$one = false;
						continue;
					}
					$categoryClass->addAlias($category);
					$alias = $category->alias;
					$pathway->addItem(hikashop_translate($category->category_name),hikashop_completeLink('category&task=listing&cid='.(int)$category->category_id.'&name='.$alias.$menu_id));
				}
			}
		} else {
			$menu_id = $this->params->get('itemid',0);
			if(!empty($menu_id)) {
				$menu_id = '&Itemid='.$menu_id;
			} else {
				$menu_id = '';
			}
		}
		$this->assignRef('pageInfo',$pageInfo);
		$this->assignRef('menu_id',$menu_id);
		$this->assignRef('params',$this->params);

		foreach($rows as &$row) {
			$row->link = $this->getLink($row);
		}
		unset($row);

		$this->assignRef('rows', $rows);
	}

	function getLink($cid, $alias = '') {
		if(!is_object($cid)) {
			$obj = new stdClass();
			$obj->category_id = $cid;
			$obj->alias = $alias;
			$cid = $obj;
		}

		if(!empty($cid->override_url))
			return $cid->override_url;

		if(!empty($cid->link))
			return $cid->link;

		global $Itemid;
		$config =& hikashop_config();
		if(empty($this->module) && !empty($Itemid) && $config->get('forward_to_submenus', 1)) {
			$app = JFactory::getApplication();
			$menus	= $app->getMenu();
			$query = 'SELECT a.id as itemid FROM `#__menu` as a WHERE a.client_id=0 AND a.parent_id='.(int)$Itemid;
			$db = JFactory::getDBO();
			$db->setQuery($query);
			$submenus = $db->loadObjectList();
			foreach($submenus as $submenu){
				$menu = $menus->getItem($submenu->itemid);
				if(!empty($menu) && !empty($menu->link) && strpos($menu->link,'option='.HIKASHOP_COMPONENT)!==false && (strpos($menu->link,'view=category')!==false || strpos($menu->link,'view=')===false || strpos($menu->link,'view=product')!==false)) {
					$parent = 0;
					if(HIKASHOP_J30) {
						$category_params = $menu->getParams();
					} else {
						jimport('joomla.html.parameter');
						$category_params = new HikaParameter($menu->params);
					}

					if(HIKASHOP_J30) {
						$params = $category_params->get('hk_category',false);
						if($params && isset($params->category))
							$parent = $params->category;
						if(!$parent) {
							$params = $category_params->get('hk_product',false);
							if($params && isset($params->category))
								$parent = $params->category;
						}
					}
					if(!$parent) {
						$params = $config->get( 'menu_'.$submenu->itemid );
						if(isset($params['selectparentlisting']))
							$parent = $params['selectparentlisting'];
					}

					if(!empty($params) && $parent == $cid->category_id) {
						$url = JRoute::_('index.php?option=com_hikashop&Itemid='.$submenu->itemid);

						$config = hikashop_config();
						$force_canonical = $config->get('force_canonical_urls',1);
						if(isset($cid->category_canonical) && empty($cid->category_canonical) && $force_canonical == 2) {
							$newObj = new stdClass();
							$newObj->category_id = $cid->category_id;
							$newObj->category_canonical = $url;
							$categoryClass = hikashop_get('class.category');
							$categoryClass->save($newObj);
						}
						return $url;
					}
				}
			}
		}

		$type = 'category';
		if(!empty($this->menu_id)) {
			$parts = explode('=',$this->menu_id);
			$app = JFactory::getApplication();
			$menus	= $app->getMenu();
			$menu = $menus->getItem($parts[1]);
			if(!empty($menu) && !empty($menu->link) && strpos($menu->link,'option='.HIKASHOP_COMPONENT)!==false && (strpos($menu->link,'view=')===false || strpos($menu->link,'view=product')!==false)) {
				$type = 'product';
			}
		}
		return hikashop_contentLink($type.'&task=listing&cid='.$cid->category_id.'&name='.$cid->alias.$this->menu_id,$cid);
	}

	function _associateChilds(&$rows, &$childs, $level = 0) {
		if($level>10)
			return;
		$level++;

		$categoryClass = null;
		foreach($rows as $k => $row) {
			$rows[$k]->childs = array();
			foreach($childs as $child) {
				if($child->category_parent_id != $row->category_id) {
					continue;
				}
				if(empty($categoryClass))
					$categoryClass = hikashop_get('class.category');
				$categoryClass->addAlias($child);
				$rows[$k]->childs[$child->category_id] = $child;
			}
			$this->_associateChilds($rows[$k]->childs, $childs, $level);
		}
	}

	function _getCount(&$rows, &$counts, $level = 0) {
		if($level > 10)
			return;
		$level++;

		foreach($rows as $k => $row) {
			if(isset($counts[$row->category_id]->number_of_products)) {
				$rows[$k]->number_of_products = (int)$counts[$row->category_id]->number_of_products;
			} else {
				$rows[$k]->number_of_products = 0;
			}
			if(!empty($rows[$k]->childs)) {
				$this->_getCount($rows[$k]->childs, $counts, $level);
				foreach($rows[$k]->childs as $child) {
					$rows[$k]->number_of_products += (int)@$child->number_of_products;
				}
			}
		}
	}

	function pagination_display($type, $divName, $id, $currentId, $position, $products) {
		if($position == 'top' || $position == 'bottom') {
			if($type == 'numbers') {
				echo '<a id="slide_number_'.$divName.'_'.$id.'" class="hikashop_slide_numbers '.($currentId<$products ? ' hikashop_slide_pagination_selected' : '').'" style="cursor:pointer; text-decoration:none">'.($id+1).'</a>';
			}
			if($type == 'rounds') {
				echo '<span class="hikashop_slide_dot_basic'.($currentId<$products ? ' hikashop_slide_dot_selected' : '').'" id="slide_number_'.$divName.'_'.$id.'"></span>';
			}
			if($type == 'thumbnails') {
				echo '<span class="'.($currentId<$products ? ' hikashop_pagination_images_selected' : 'hikashop_pagination_images').'" id="slide_number_'.$divName.'_'.$id.'">';
			}
			if($type == 'names') {
				echo '<span id="slide_number_'.$divName.'_'.$id.'" class="hikashop_slide_numbers '.($currentId<$products ? ' hikashop_slide_pagination_selected' : '').'">';
			}
			return;
		}

		if($type == 'numbers') {
			echo '<a id="slide_number_'.$divName.'_'.$id.'" class="hikashop_slide_numbers '.($currentId<$products ? ' hikashop_slide_pagination_selected' : '').'" style="cursor:pointer; text-decoration:none">'.($id+1).'</a><br/>';
		}
		if($type == 'rounds') {
			echo '<span class="hikashop_slide_dot_basic'.($currentId<$products ? ' hikashop_slide_dot_selected' : '').'" id="slide_number_'.$divName.'_'.$id.'"></span><br/>';
		}
		if($type == 'thumbnails') {
			echo '<span class="'.($currentId<$products ? ' hikashop_pagination_images_selected' : 'hikashop_pagination_images').'" id="slide_number_'.$divName.'_'.$id.'">';
		}
		if($type == 'names') {
			echo '<span id="slide_number_'.$divName.'_'.$id.'" class="hikashop_slide_numbers '.($currentId<$products ? ' hikashop_slide_pagination_selected' : '').'">';
		}
	}
}
