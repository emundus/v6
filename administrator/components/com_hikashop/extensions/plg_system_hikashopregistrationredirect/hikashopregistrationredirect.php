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
class plgSystemHikashopregistrationredirect extends JPlugin
{
	function __construct(&$subject, $config){
		parent::__construct($subject, $config);
		if(!isset($this->params)){
			$plugin = JPluginHelper::getPlugin('system', 'hikashopregistrationredirect');
			$this->params = new JRegistry(@$plugin->params);
		}
	}


	function onAfterRoute(){
		$app = JFactory::getApplication();
		if(version_compare(JVERSION,'4.0','>=') && $app->isClient('administrator'))
			return true;
		if(version_compare(JVERSION,'4.0','<') && $app->isAdmin())
			return true;

		if((@$_REQUEST['option']=='com_user' && @$_REQUEST['view']=='register') || (@$_REQUEST['option']=='com_users' && @$_REQUEST['view']=='registration' && !in_array(@$_REQUEST['task'],array('remind.remind','reset.request','reset.confirm','reset.complete')))){

			if(!defined('DS'))
				define('DS', DIRECTORY_SEPARATOR);
			if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php')) return true;

			$Itemid = $this->params->get('item_id');
			if(empty($Itemid)){
				global $Itemid;
				if(empty($Itemid)){
					$urlItemid = hikaInput::get()->getInt('Itemid');
					if($urlItemid){
						$Itemid = $urlItemid;
					}
				}
			}

			$menuClass = hikashop_get('class.menus');
			if(!empty($Itemid)){
				$Itemid = $menuClass->loadAMenuItemId('','',$Itemid);
			}
			if(empty($Itemid)){
				$Itemid = $menuClass->loadAMenuItemId('','');
			}
			$url_itemid = '';
			if(!empty($Itemid)){
				$url_itemid.='&Itemid='.$Itemid;
			}

			$app->redirect(JRoute::_('index.php?option=com_hikashop&ctrl=user&task=form'.$url_itemid, false));
		}
		return true;
	}

}
