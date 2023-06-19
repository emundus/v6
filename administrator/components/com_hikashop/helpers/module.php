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
class hikashopModuleHelper {

	public function initialize(&$obj) {
		$this->_getParams($obj);

		if(!empty($obj->ctrl)) {
			$data = $obj->params->get('data');
			$type = 'hk_'.$obj->ctrl;

			if(isset($data->$type) && is_object($data->$type)) {
				foreach($data->$type as $k => $v) {
					$obj->params->set($k,$v);
				}
			} else {
				$data = $obj->params->get('hk_'.$obj->ctrl);
				if(!empty($data) && is_array($data)) {
					foreach($data as $k => $v) {
						$obj->params->set($k,$v);
					}
				}
			}
		}

		$this->setCSS($obj->params, @$obj->module);
		$obj->modules = $this->setModuleData($obj->params->get('modules'));
	}

	public function setCSS(&$params, $name = '') {
		$config =& hikashop_config();
		$css = '';

		$main_div_name = $params->get('main_div_name');
		if(empty($main_div_name)) {
			$main_div_name = 'hikashop_category_information_' . ($name ? 'module_' : 'menu_') . $params->get('id');
			$params->set('main_div_name', $main_div_name);
		}

		if($config->get('no_css_header', 0)) {
			return true;
		}

		if($params->get('background_color', '') == '') {
			$defaultParams = $config->get('default_params');
			$params->set('background_color', $defaultParams['background_color']);
		}

		$background_color = $params->get('background_color');
		if(!empty($background_color)) {
			$css .= '
#'.$main_div_name.' div.hikashop_subcontainer,
#'.$main_div_name.' .hikashop_rtop *,#'.$main_div_name.' .hikashop_rbottom * { background:'.$background_color.'; }';
		}

		if((int)$params->get('text_center', -1) == -1) {
			$defaultParams = $config->get('default_params');
			$params->set('text_center', (int)$defaultParams['text_center']);
		}

		$center = $params->get('text_center');
		$align = (!empty($center) ? 'center': 'left');
		$css .= '
#'.$main_div_name.' div.hikashop_subcontainer,
#'.$main_div_name.' div.hikashop_subcontainer span,
#'.$main_div_name.' div.hikashop_container { text-align:'.$align.'; }';

		if($params->get('margin', '') == '') {
			$defaultParams = $config->get('default_params');
			$params->set('margin', $defaultParams['margin']);
		}

		$margin = $params->get('margin', '');
		if(strlen($margin)) {
			$css .= '
#'.$main_div_name.' div.hikashop_container { margin:'.(int)$margin.'px '.(int)$margin.'px; }';
		}

		if((int)$params->get('rounded_corners', -1) == -1) {
			$defaultParams = $config->get('default_params');
			$params->set('rounded_corners', (int)$defaultParams['rounded_corners']);
		}

		$rounded_corners = $params->get('rounded_corners', 0);
		if($rounded_corners) {
			$css .= '
#'.$main_div_name.' .hikashop_subcontainer { -moz-border-radius: 5px; -webkit-border-radius: 5px; -khtml-border-radius: 5px; border-radius: 5px; }';
		}

		$doc = JFactory::getDocument();
		$doc->addStyleDeclaration($css);
	}

	public function setModuleData($modules) {
		if(empty($modules)) {
			$modules = array();
			return $modules;
		}

		if(!is_array($modules)) {
			$modules = explode(',', $modules);
		}
		hikashop_toInteger($modules);

		$filters = array('id IN ('.implode(',', $modules).')');

		$database = JFactory::getDBO();
		if(HIKASHOP_J30) {
			$lang = JFactory::getLanguage();
			$tag = $lang->getTag();
			$filters[] = "language IN ('*', '', ".$database->Quote($tag).")";
		}
		$query = 'SELECT * FROM '.hikashop_table('modules', false).' WHERE '.implode(' AND ', $filters);
		$database->setQuery($query);
		$modulesData = $database->loadObjectList('id');

		$unset = array();
		foreach($modules as $k => $v) {
			if(!isset($modulesData[$v])) {
				$unset[] = $k;
				continue;
			}

			$file = $modulesData[$v]->module;
			$custom = substr($file, 0, 4) == 'mod_' ?  0 : 1;
			$modulesData[$v]->user = $custom;
			$modulesData[$v]->name = $custom ? $modulesData[$v]->title : substr($file, 4);
			$modulesData[$v]->style	= null;
			$modulesData[$v]->position = strtolower($modulesData[$v]->position);
			$modules[$k] = $modulesData[$v];
		}
		if(!empty($unset)) {
			foreach($unset as $u) {
				unset($modules[$u]);
			}
		}
		return $modules;
	}

	public function _getParams(&$obj) {
		global $Itemid;
		$app = JFactory::getApplication();
		$menus	= $app->getMenu();
		$menu	= $menus->getActive();
		$menuParams = null;

		if(!empty($menu)) {
			if(HIKASHOP_J30)
				$menuParams = $menu->getParams();
			else
				$menuParams = @$menu->params;
		}

		if(!empty($obj->params)) {
			$obj->module = true;

			if($obj->params->get('content_synchronize')){
				$id = null;
				if(HIKASHOP_J30 && isset($menu)) {
					$productParams = $menuParams->get('hk_product',false);
					if($productParams && isset($productParams->category))
						$id = $productParams->category;
					$categoryParams = $menuParams->get('hk_category', false);
					if($categoryParams && isset($categoryParams->category))
						$id = $categoryParams->category;
				}
				if($id)
					$obj->params->set('selectparentlisting', $id);
			}
			return true;
		}


		if(!empty($Itemid) && !empty($menu) && !empty($menuData->link) && strpos($menu->link,'option='.HIKASHOP_COMPONENT)!==false && (strpos($menu->link,'view=category')!==false || strpos($menu->link,'view=')===false)){
			$app->setUserState(HIKASHOP_COMPONENT.'.category_item_id',$Itemid);
		}
		if(empty($menu)) {
			if(!empty($Itemid)) {
				$menus->setActive($Itemid);
				$menu	= $menus->getItem($Itemid);
			} else {
				$item_id = $app->getUserState(HIKASHOP_COMPONENT.'.category_item_id');
				if(!empty($item_id)) {
					$menus->setActive($item_id);
					$menu	= $menus->getItem($item_id);
				}
			}
		}

		jimport('joomla.html.parameter');
		if (is_object( $menuParams )) {
			if(HIKASHOP_J30 && (($menu->query['view'] == 'category' && (!$menuParams->get('hk_category',false) || !$menuParams->get('hk_product',false))) || ($menu->query['view'] == 'product' && !$menuParams->get('hk_product',false)))){
				$db = JFactory::getDBO();
				$query = 'SELECT params FROM '.hikashop_table('menu', false).' WHERE id = '.(int)$menu->id;
				$db->setQuery($query);
				$itemData = json_decode($db->loadResult());
				if(isset($itemData->hk_category))
					$menuParams->set('hk_category', $itemData->hk_category);
				if(isset($itemData->hk_product))
					$menuParams->set('hk_product', $itemData->hk_product);
			}

			$obj->params = new HikaParameter( $menuParams );
			$obj->params->set('id',$menu->id);
			$obj->params->set('title',$menu->title);

			if(HIKASHOP_J30) {
				$productParams = $menuParams->get('hk_product',false);
				if($productParams && !isset($productParams->selectparentlisting) && isset($productParams->category)) {
					$productParams->selectparentlisting = $productParams->category;
				}

				$categoryParams = $menuParams->get('hk_category', false);
				if($categoryParams && !isset($categoryParams->selectparentlisting) && isset($categoryParams->category)) {
					$categoryParams->selectparentlisting = $categoryParams->category;
				}
			}
		} else {
			$params ='';
			$obj->params = new HikaParameter($params);
		}

		$config = hikashop_config();
		$menuClass = hikashop_get('class.menus');
		$menuData = $menuClass->get(@$menu->id);

		if($config->get('auto_init_options', 1) && !empty($menuData->link) && strpos($menuData->link, 'view=product') === false) {
			$options = $config->get('menu_'.@$menu->id, null);
			if(!HIKASHOP_J30 && (empty($options) || empty($options['modules']))) {
				$menuClass->createMenuOption($menuData,$options);
			}
		}

		if(!empty($menuData->hikashop_params)) {
			foreach($menuData->hikashop_params as $key => $item) {
				$obj->params->set($key, $item);
			}
		}

		if(!empty($menuData->params)) {
			foreach($menuData->params as $key => $item) {
				if(!is_object($item)) {
					$obj->params->set($key,$item);
				}
			}
		}
	}
}
