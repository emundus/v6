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

JLoader::register('MenusHelper', JPATH_ADMINISTRATOR . '/components/com_menus/helpers/menus.php');

class HikashopHelperAssociation {

	public static function getAssociations($id = 0, $view = null, $layout = null) {
		$languages	= JLanguageHelper::getLanguages();
		$jinput = JFactory::getApplication()->input;
		$ctrl = $jinput->get('ctrl');
		$ctrl_var = 'ctrl';
		if(empty($ctrl)) {
			$ctrl = $jinput->get('view');
			if(!empty($ctrl))
				$ctrl_var = 'view';
		}
		$task = $jinput->get('task');
		$task_var = 'task';
		if(empty($ctrl)) {
			$task = $jinput->get('layout');
			if(!empty($task))
				$task_var = 'layout';
		}
		$component = $jinput->getCmd('option');
		$Itemid = $jinput->get('Itemid');

		$result = array();

		if($component != 'com_hikashop')
			return $result;

		$url = 'index.php?option=com_hikashop';



		$step = $jinput->get('step');
		if(!empty($step))
			$url .= '&step='.$step;

		$name = $jinput->get('name');
		$cid = $jinput->get('cid');

		$associations = MenusHelper::getAssociations($Itemid);

		$app = JFactory::getApplication();
		$config = hikashop_config();
		$canonical = $config->get('force_canonical_urls');
		$menu = $app->getMenu();
		foreach($languages as $i => &$language) {

			$item_id = $Itemid;
			$lang_url = $url;
			$add_cid = false;
			$add = false;
			$cid_in_menu_item = 0;
			if(!empty($cid)) {
				$add_cid = true;

				if($ctrl == 'category' && $task == 'listing' && empty($associations[$language->lang_code])) {
					$menu_item = $menu->getItem($Itemid);
					if(!empty($menu_item)) {
						$params = $menu_item->getParams();
						$p = $params->get('hk_category');
						if(empty($p->selectparentlisting) && !empty($p->category))
							$p->selectparentlisting = $p->category;
						if(!empty($p->selectparentlisting))
							$cid_in_menu_item = $p->selectparentlisting;
						if(!empty($cid_in_menu_item) && $cid_in_menu_item == $cid) {
							$add_cid = false;
						}
					}
				}
			}

			if(!empty($associations) && !empty($associations[$language->lang_code])) {
				$menu_item = $menu->getItem($associations[$language->lang_code]);

				if(!empty($cid)) {
					if($ctrl == 'product' && $task == 'show') {
						$class = hikashop_get('class.product');
						$product = $class->get($cid);
						$class->addAlias($product, $language->lang_code);
						if(!empty($product->alias))
							$name = $product->alias;
						if($canonical && !empty($product->product_canonical)) {
							$result[$language->lang_code] = hikashop_cleanURL(hikashop_translate($product->product_canonical, $language->lang_code), false, true);
							continue;
						}
					}elseif($ctrl == 'category' && $task == 'listing') {
						$cid_in_menu_item = 0;
						if(!empty($menu_item)) {
							$params = $menu_item->getParams();
							$p = $params->get('hk_category');
							if(empty($p->selectparentlisting) && !empty($p->category))
								$p->selectparentlisting = $p->category;
							if(!empty($p->selectparentlisting))
								$cid_in_menu_item = $p->selectparentlisting;
						}
						if(!empty($cid_in_menu_item) && $cid_in_menu_item == $cid) {
							$add_cid = false;
						} else {
							$class = hikashop_get('class.category');
							$category = $class->get($cid);
							$class->addAlias($category, $language->lang_code);
							if(!empty($category->alias))
								$name = $category->alias;
							if($canonical && !empty($category->category_canonical)) {
								$result[$language->lang_code] = hikashop_cleanURL(hikashop_translate($category->category_canonical, $language->lang_code), false, true);
							}
						}
					}
				}
				if(!empty($menu_item->link)) {
					if( !empty($ctrl) && strpos($menu_item->link, '&view='.$ctrl) === false )
						$add = true;
					if( !empty($task) && strpos($menu_item->link, '&layout='.$task) === false )
						$add = true;
				}
				$item_id = $associations[$language->lang_code];
			} else {
				if(!empty($ctrl) && !empty($task))
					$add = true;
			}

			if($add)
				$lang_url .= '&'.$ctrl_var.'='.$ctrl.'&'.$task_var.'='.$task;

			if(!empty($add_cid)) {
				$lang_url .= '&cid='.$cid;

				if(!empty($name)) {
					$lang_url .= '&name='.$name;
				}
			}
			$sef = substr($language->lang_code, 0,2);
			if(!empty($language->sef))
				$sef = $language->sef;

			$result[$language->lang_code] = $lang_url . (!empty($item_id) ? '&Itemid='.$item_id : ''). '&lang='.$sef;
		}

		return $result;
	}

}
