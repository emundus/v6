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

class plgSystemVm_redirect extends JPlugin {

	function __construct(&$subject, $config) {
		parent::__construct($subject, $config);
	}


	function onAfterRoute() {
		$app = JFactory::getApplication();

		if(version_compare(JVERSION,'3.0','>=')) {
			$option = $app->input->getVar('option');
			$vmProdId = $app->input->getInt('product_id');
			if (empty($vmProdId))
				$vmProdId = $app->input->getInt('virtuemart_product_id');
			$vmCatId = $app->input->getInt('category_id');
			if (empty($vmCatId))
				$vmCatId = $app->input->getInt('virtuemart_category_id');
			$vmOrderId = $app->input->getInt('order_id');
			if (empty($vmOrderId))
				$vmOrderId = $app->input->getInt('order_number');
		} else {
			$option = JRequest::getVar('option');
			$vmProdId = JRequest::getInt('product_id');
			if (empty($vmProdId))
				$vmProdId = JRequest::getInt('virtuemart_product_id');
			$vmCatId = JRequest::getInt('category_id');
			if (empty($vmCatId))
				$vmCatId = JRequest::getInt('virtuemart_category_id');
			$vmOrderId = JRequest::getInt('order_id');
			if (empty($vmOrderId))
				$vmOrderId = JRequest::getInt('order_number');
		}


		if(version_compare(JVERSION,'4.0','>=') && $app->isClient('administrator'))
			return true;
		if(version_compare(JVERSION,'4.0','<') && $app->isAdmin())
			return true;

		if( $option != 'com_virtuemart' )
			return true;


		$db = JFactory::getDBO();
		if(!defined('DS'))
			define('DS', DIRECTORY_SEPARATOR);
		if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php'))
			return true;

		if(empty($vmProdId) && empty($vmCatId) && empty($vmOrderId)){
			$currentURL = hikashop_currentURL();
			if(preg_match_all('#/(virtuemart_product_id|product_id|category_id|virtuemart_category_id|order_id|order_number)/([0-9]+)#',$currentURL,$matches)){
				foreach($matches[1] as $k => $key){
					switch($key){
						case 'product_id':
						case 'virtuemart_product_id':
							$vmProdId = $matches[2][$k];
							break;
						case 'category_id':
						case 'virtuemart_category_id':
							$vmCatId = $matches[2][$k];
							break;
						case 'order_id':
						case 'order_number':
							$vmOrderId = $matches[2][$k];
							break;
					}
				}
			}

			if(empty($vmProdId) && empty($vmCatId) && empty($vmOrderId)){
				return true;
			}
		}


		$query='SHOW TABLES LIKE '.$db->Quote($db->getPrefix().substr(hikashop_table('vm_prod'),3));
		$db->setQuery($query);
		$table = $db->loadResult();
		if(empty($table))
			return true;

		$url = null;
		if( !empty($vmProdId) && $vmProdId > 0 ) {
			$query = "SELECT a.hk_id, b.product_name as 'name' FROM `#__hikashop_vm_prod` a INNER JOIN `#__hikashop_product` b ON a.hk_id = b.product_id WHERE a.vm_id = " . $vmProdId . ";";
			$baseUrl = 'product&task=show';
		} else if( !empty($vmCatId)  && $vmCatId > 0 ) {
			$id = 'vm-fallback';
			$alias = 'hikashop-menu-for-module-'.$id;
			$db->setQuery('SELECT id FROM '.hikashop_table('menu',false).' WHERE alias=\''.$alias.'\'');
			$itemId = $db->loadResult();
			if(empty($itemId)) {
				$options = new stdClass();
				$config =& hikashop_config();
				$options->hikashop_params = $config->get('default_params',null);
				$classMenu = hikashop_get('class.menus');
				$classMenu->loadParams($options);
				$options->hikashop_params['content_type'] = 'category';
				$options->hikashop_params['layout_type']='div';
				$options->hikashop_params['content_synchronize']='1';
				if($options->hikashop_params['columns']==1){
					$options->hikashop_params['columns']=3;
				}
				$classMenu->createMenu($options->hikashop_params, $id);
				$itemId = $options->hikashop_params['itemid'];
			}

			$query = "SELECT a.hk_id, b.category_name as 'name' FROM `#__hikashop_vm_cat` a INNER JOIN `#__hikashop_category` b ON a.hk_id = b.category_id WHERE a.vm_id = " . $vmCatId . ";";
			$baseUrl = 'category&task=listing&Itemid='.$itemId;
		}elseif(!empty($vmOrderId)){
			$db->setQuery('SELECT order_id FROM '.hikashop_table('order').' WHERE order_vm_id='.$vmOrderId);
			$hikaOrderId = $db->loadResult();
			if(!empty($hikaOrderId)){
				$url = hikashop_completeLink('order&task=show&cid='.$hikaOrderId, false, true);
				$app->redirect($url);
				return true;
			}
			else
			{
				$db->setQuery('SELECT order_id FROM '.hikashop_table('order').' AS h INNER JOIN `#__virtuemart_orders` AS v ON h.order_vm_id = v.virtuemart_order_id WHERE v.order_number='.$vmOrderId);
				$hikaOrderId = $db->loadResult();
				if(!empty($hikaOrderId)){
					$url = hikashop_completeLink('order&task=show&cid='.$hikaOrderId, false, true);
					$app->redirect($url);
					return true;
				}
			}
		}

		if( !empty($query) && !empty($baseUrl) ) {
			$db->setQuery($query);
			$link = $db->loadObject();

			if( $link ) {
				if(method_exists($app,'stringURLSafe')) {
					$name = $app->stringURLSafe(strip_tags($link->name));
				} else {
					$name = JFilterOutput::stringURLSafe(strip_tags($link->name));
				}
				$url = hikashop_completeLink($baseUrl.'&cid='.$link->hk_id.'&name='.$name, false, true);
			}
		}

		if( $url )
			$app->redirect($url);
	}
}
