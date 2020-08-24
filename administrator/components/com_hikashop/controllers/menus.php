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
class MenusController extends hikashopController{
	var $toggle = array();
	var $type='menus';

	function __construct(){
		parent::__construct();
		$this->modify[]='add_module';
		$this->add = array();
	}

	function add_module(){
		$id = hikashop_getCID('id');
		$menuClass = hikashop_get('class.menus');
		$menuClass->attachAssocModule($id);
		$this->edit();
	}

	function edit(){
		if(hikaInput::get()->getInt('fromjoomla')){
			$app = JFactory::getApplication();
			$context = 'com_menus.edit.item';
			$id = hikashop_getCID('id');
			if($id){
				$values = (array) $app->getUserState($context . '.id');
				$index = array_search((int) $id, $values, true);
				if (is_int($index)){
					unset($values[$index]);
					$app->setUserState($context . '.id', $values);
				}
			}
		}
		return parent::edit();
	}
}
