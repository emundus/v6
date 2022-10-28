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
class hikamarketModuleHelper
{
	public function initialize(&$obj) {
		$this->getParams($obj);
		$this->setCSS($obj->params,@$obj->module);
		$obj->modules = $this->setModuleData($obj->params->get('modules'));
	}

	public function setCSS(&$params, $name = '') {
		$css = '';
		$main_div_name = $params->get('main_div_name');
		if(empty($main_div_name)) {
			$main_div_name ='hikamarket_vendor_listing_'.($name?'module_':'menu_').$params->get('id');
			$params->set('main_div_name', $main_div_name);
		}

		$shopConfig = hikamarket::config(false);
		if($shopConfig->get('no_css_header',0))
			return true;

		$css = PHP_EOL;
		$defaultParams = $shopConfig->get('default_params');

		if($params->get('background_color', '') == '')
			$params->set('background_color', $defaultParams['background_color']);
		if($params->get('margin', '') == '')
			$params->set('margin', $defaultParams['margin']);
		if($params->get('text_center', '-1') == '-1')
			$params->set('text_center', $defaultParams['text_center']);
		if($params->get('rounded_corners', '-1') == '-1')
			$params->set('rounded_corners', $defaultParams['rounded_corners']);

		$background_color = $params->get('background_color');
		if(!empty($background_color)) {
			$css .= '#'.$main_div_name.' div.hikamarket_subcontainer { background:'.$background_color.'; }' . PHP_EOL .
					'#'.$main_div_name.' .hikamarket_rtop *, #'.$main_div_name.' .hikamarket_rbottom * { background:'.$background_color.'; }' . PHP_EOL;
		}

		$center = $params->get('text_center');
		if(!empty($center)) {
			$css .= '#'.$main_div_name.' div.hikamarket_subcontainer, #'.$main_div_name.' div.hikamarket_subcontainer span { text-align:center; }' . PHP_EOL .
					'#'.$main_div_name.' div.hikamarket_container { text-align:center; }' . PHP_EOL;
		} else {
			$css .= '#'.$main_div_name.' div.hikamarket_subcontainer, #'.$main_div_name.' div.hikamarket_subcontainer span { text-align:left; }' . PHP_EOL .
					'#'.$main_div_name.' div.hikamarket_container { text-align:left; }' . PHP_EOL;
		}

		$margin = $params->get('margin',0);
		$css .= '#'.$main_div_name.' div.hikamarket_container { margin:'.$margin.'px '.$margin.'px; }' . PHP_EOL;

		$rounded_corners = $params->get('rounded_corners', 0);
		if($rounded_corners) {
			$css.= '#'.$main_div_name.' .hikamarket_subcontainer { -moz-border-radius: 5px; -webkit-border-radius: 5px; -khtml-border-radius: 5px; border-radius: 5px; }' . PHP_EOL;
		}

		$doc = JFactory::getDocument();
		$doc->addStyleDeclaration($css);
	}

	function setModuleData($modules) {
		if(empty($modules)) {
			$modules = array();
			return $modules;
		}

		if(!is_array($modules))
			$modules = explode(',',$modules);

		hikamarket::toInteger($modules);
		$modules = implode(',',$modules);

		$database = JFactory::getDBO();
		$query = 'SELECT * FROM '.hikamarket::table('modules',false).' WHERE id IN ('.$modules.');';
		$database->setQuery($query);
		$modulesData = $database->loadObjectList('id');
		$unset = array();
		$modules = explode(',',$modules);
		foreach($modules as $k => $v) {
			if(isset($modulesData[$v])) {
				$file = $modulesData[$v]->module;
				$custom = substr( $file, 0, 4 ) == 'mod_' ?  0 : 1;
				$modulesData[$v]->user = $custom;
				$modulesData[$v]->name = $custom ? $modulesData[$v]->title : substr( $file, 4 );
				$modulesData[$v]->style	= null;
				$modulesData[$v]->position = strtolower($modulesData[$v]->position);
				$modules[$k] = $modulesData[$v];
			} else {
				$unset[]=$k;
			}
		}

		if(!empty($unset)) {
			foreach($unset as $u) {
				unset($modules[$u]);
			}
		}

		return $modules;
	}

	private function getParams(&$obj) {
		if(!empty($obj->params)) {
			$obj->module = true;
			return;
		}

		global $Itemid;
		$app = JFactory::getApplication();
		$menus = $app->getMenu();
		$menu = $menus->getActive();

		if(empty($menu) && !empty($Itemid)) {
			$menus->setActive($Itemid);
			$menu = $menus->getItem($Itemid);
		}

		jimport('joomla.html.parameter');
		if(is_object($menu)) {
			if(HIKASHOP_J30)
				$menuParams = $menu->getParams();
			else
				$menuParams = @$menu->params;
			$obj->params = new HikaParameter($menuParams);
			$obj->params->set('id', $menu->id);
			$obj->params->set('title', $menu->title);
		} else {
			$params ='';
			$obj->params = new HikaParameter($params);
		}

		$config = hikamarket::config();
		$menuClass = hikamarket::get('class.menus');
		$menuData = $menuClass->get(@$menu->id);

		$marketdata = $obj->params->get('market', null);
		if(!empty($marketdata)) {
			foreach($marketdata as $key => $item) {
				$obj->params->set($key, $item);
			}
		} else if(!empty($menuData->hikamarket_params)) {
			foreach($menuData->hikamarket_params as $key => $item) {
				$obj->params->set($key, $item);
			}
		}
		if(!empty($menuData->params)) {
			foreach($menuData->params as $key => $item) {
				if(!is_object($item))
					$obj->params->set($key, $item);
			}
		}
	}
}
