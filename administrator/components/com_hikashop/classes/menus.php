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
class hikashopMenusClass extends hikashopClass {
	var $pkeys = array('id');
	var $toggle = array('published' => 'id');

	function getTable() {
		return hikashop_table('menu', false);
	}

	function get($id, $default = ''){
		$obj = parent::get($id, $default);
		$config =& hikashop_config();
		if(is_null($obj)) $obj = new stdClass();

		$this->loadParams($obj);

		if(isset($obj->params->hk_product) && !isset($obj->params->hk_category)){
			$obj->hikashop_params = array();
			foreach($obj->params->hk_product as $k => $v){
				$obj->hikashop_params[$k] = $v;
				if($k == 'category' && !isset($obj->params->hk_product->selectparentlisting))
					$obj->hikashop_params['selectparentlisting'] = $v;
			}
			$obj->hikashop_params['content_type'] = 'product';
		} else if(isset($obj->params->hk_category)){
			$obj->hikashop_params = array();
			foreach($obj->params->hk_category as $k => $v){
				$obj->hikashop_params[$k] = $v;
				if($k == 'category' && !isset($obj->params->hk_category->selectparentlisting))
					$obj->hikashop_params['selectparentlisting'] = $v;
			}
			$obj->hikashop_params['content_type'] = 'category';
		} else {
			$type = '';
			if(@$obj->link == 'index.php?option=com_hikashop&view=category&layout=listing')
				$type = 'category';
			elseif(@$obj->link == 'index.php?option=com_hikashop&view=product&layout=listing')
				$type = 'product';
			elseif(hikaInput::get()->getString('ctrl', 'category') == 'category')
				$type = 'category';
			else
				$type = 'product';
			try {
				if(!empty($obj->id)) {
					$app = JFactory::getApplication();
					$menus	= $app->getMenu();
					$menu	= $menus->getItem($obj->id);
					if(!empty($menu)) {
						if(HIKASHOP_J30)
							$menuParams = $menu->getParams();
						else
							$menuParams = $menu->params;
						if(!empty($menuParams)) {
							$p = $menuParams->get('hk_'.$type,false);
							if(!empty($p->selectparentlisting))
								$obj->hikashop_params['selectparentlisting'] = $p->selectparentlisting;
						}
					}
				}
			} catch(Exception $e) {

			}
		}

		if(empty($obj->hikashop_params)){
			if(!empty($obj->id)){
				$obj->hikashop_params = $config->get('menu_'.$obj->id,null);
			}
			if(empty($obj->hikashop_params)){
				$obj->hikashop_params = $config->get('default_params',null);
			}
		}

		return $obj;
	}

	function loadParams(&$result) {
		if(empty($result->params))
			return;

		if(HIKASHOP_J30){
			$result->params = json_decode($result->params);
			return;
		}

		$lines = explode("\n", $result->params);
		$result->params = array();
		foreach($lines as $line) {
			$param = explode('=', $line, 2);
			if(count($param) == 2) {
				$result->params[$param[0]] = $param[1];
			}
		}
	}

	function saveForm(){
		$module = new stdClass();
		$formData = hikaInput::get()->get('menu', array(), 'array');
		jimport('joomla.filter.filterinput');
		$safeHtmlFilter = JFilterInput::getInstance(array(), array(), 1, 1);
		if(!empty($formData)){
			foreach($formData as $column => $value){
				hikashop_secureField($column);
				if(is_array($value)){
					$module->$column=array();
					foreach($value as $k2 => $v2){
						hikashop_secureField($k2);
						$module->{$column}[$k2] = $safeHtmlFilter->clean(strip_tags($v2), 'string');
					}
				}else{
					$module->$column = $safeHtmlFilter->clean(strip_tags($value), 'string');
				}
			}
			if(in_array($module->content_type,array('category','manufacturer'))){
				$module->link='index.php?option=com_hikashop&view=category&layout=listing';
			}else{
				$module->link='index.php?option=com_hikashop&view=product&layout=listing';
			}
			$content_type = $module->content_type;
			unset($module->content_type);
		}
		$new = false;
		if(empty($module->id)){
			$new = true;
			if(empty($module->alias)){
				$module->alias = $module->title;
				$module->alias = preg_replace('#[^a-z_0-9-]#i','',$module->alias);
			}
		}
		$result = $this->save($module);
		if($result)
		{
			$element = array();
			$formData = hikaInput::get()->get('config', array(), 'array');
			$params_name = 'menu_'.(int)$module->id;
			if($new){
				$post_name = 'menu_0';
			}else{
				$post_name = $params_name;
			}
			if(!empty($formData[$post_name])){
				foreach($formData[$post_name] as $column => $value){
					hikashop_secureField($column);
					$element[$column] = $safeHtmlFilter->clean(strip_tags($value), 'string');
				}
				if(empty($element['selectparentlisting'])){
					$categoryClass = hikashop_get('class.category');
					$mainProductCategory = 'product';
					$categoryClass->getMainElement($mainProductCategory);
					$element['selectparentlisting']=$mainProductCategory;
				}
			}

			$element['content_type']=$content_type;
			if(in_array($element['content_type'],array('category','manufacturer')) && empty($element['modules'])){
				$this->displayErrors((int)$module->id);
			}
			$configClass =& hikashop_config();
			$config=new stdClass();
			$config->$params_name = $element;

			if($configClass->save($config)){
				$configClass->set($params_name,$element);
			}
			if (!empty($element['modules']))
			{
				$modules = explode(',',$element['modules']);
				$modulesClass = hikashop_get('class.modules');
				foreach($modules as $moduleId){
					$_REQUEST['moduleconfig']['params_'.$moduleId]['id']=$moduleId;
				}
				foreach($modules as $moduleId){
					$status = $modulesClass->saveForm($moduleId);
				}
			}
		}
		return $result;
	}

	function displayErrors($id){
		static $displayed = false;
		if($displayed)
			return;

		$displayed = true;
		$app = JFactory::getApplication();
		$app->enqueueMessage(JText::_('MENU_WITHOUT_ASSOCIATED_MODULE'));
		$app->enqueueMessage(JText::_('ASSOCIATED_MODULE_NEEDED'));
		$app->enqueueMessage(JText::sprintf('ADD_MODULE_AUTO',hikashop_completeLink('menus&task=add_module&cid='.$id.'&'.hikashop_getFormToken().'=1')));
	}

	function getCheckoutMenuIdForURL(){
		global $Itemid;
		$menu_id = 0;
		if($Itemid){
			$menu_id = $this->loadAMenuItemId('','',$Itemid);
		}
		if(empty($menu_id)){
			$config = hikashop_config();
			$task = $config->get('checkout_legacy', 0) ? 'step' : 'show';
			$menu_id = $this->loadAMenuItemId('checkout', $task);
			if(empty($menu_id)){
				$menu_id = $this->loadAMenuItemId('','');
			}
		}
		$url_menu_id = '';
		if(!empty($menu_id)){
			$url_menu_id = '&Itemid='.$menu_id;
		}
		return $url_menu_id;
	}

	function getCheckoutURL($redirect = false, $extra = '') {
		$config = hikashop_config();
		$task = $config->get('checkout_legacy', 0) ? 'step' : 'show';
		$itemid_for_checkout = (int)$config->get('checkout_itemid', 0);
		if(!empty($itemid_for_checkout)) {
			$forced_menu_item_is_checkout_type = (int)$this->loadAMenuItemId('checkout', $task, $itemid_for_checkout);
			if(!empty($forced_menu_item_is_checkout_type))
				return JRoute::_('index.php?option=' . HIKASHOP_COMPONENT . $extra . '&Itemid=' . $itemid_for_checkout, !$redirect);

			$forced_menu_item_is_hikashop = (int)$this->loadAMenuItemId('', '', $itemid_for_checkout);
			if(!empty($forced_menu_item_is_hikashop))
				return hikashop_completeLink('checkout' . $extra . '&Itemid=' . $itemid_for_checkout, false, $redirect);
		}

		$menu_id = (int)$this->loadAMenuItemId('checkout', $task);
		if(!empty($menu_id))
			return JRoute::_('index.php?option=' . HIKASHOP_COMPONENT . $extra . '&Itemid=' . $menu_id, !$redirect);

		global $Itemid;
		$menu_id = (int)$this->loadAMenuItemId('','',$Itemid);
		if(!empty($menu_id))
			return hikashop_completeLink('checkout' . $extra . '&Itemid=' . $menu_id, false, $redirect);

		$menu_id = (int)$this->loadAMenuItemId('','');
		if(!empty($menu_id))
			return hikashop_completeLink('checkout' . $extra . '&Itemid=' . $menu_id, false, $redirect);

		if(!empty($Itemid))
			return hikashop_completeLink('checkout' . $extra . '&Itemid=' . $Itemid, false, $redirect);

		return hikashop_completeLink('checkout' . $extra, false, $redirect);
	}


	function getWishlistURL($redirect = false, $extra = '') {
		global $Itemid;
		$menu_id = (int)$this->loadAMenuItemId('','',$Itemid);
		if(!empty($menu_id))
			return hikashop_completeLink('cart&task=listing&cart_type=wishlist' . $extra . '&Itemid=' . $menu_id, false, $redirect);

		$menu_id = (int)$this->loadAMenuItemId('','');
		if(!empty($menu_id))
			return hikashop_completeLink('cart&task=listing&cart_type=wishlist' . $extra . '&Itemid=' . $menu_id, false, $redirect);

		if(!empty($Itemid))
			return hikashop_completeLink('cart&task=listing&cart_type=wishlist' . $extra . '&Itemid=' . $Itemid, false, $redirect);

		return hikashop_completeLink('cart&task=listing&cart_type=wishlist' . $extra, false, $redirect);
	}

	function getPublicMenuItemId($id = 0) {
		$filters = array(
			'a.type=\'component\'',
			'a.published=1',
			'b.title IS NOT NULL',
			'a.access IN (0, 1)',
			'a.link LIKE \'index.php?option=com_hikashop&view=%\'',
		);

		$lang = JFactory::getLanguage();
		$tag = $lang->getTag();
		$filters[] = "a.language IN ('*', '', ".$this->database->Quote($tag).")";

		if($id){
			$filters[] = 'a.id = '.(int)$id;
		}
		$query = "SELECT a.id FROM ".hikashop_table('menu',false).' AS a INNER JOIN `#__menu_types` as b on a.menutype = b.menutype WHERE '.implode(' AND ',$filters);
		$this->database->setQuery($query);
		return (int) $this->database->loadResult();
	}

	function loadAMenuItemId($view = 'category', $layout = 'listing', $id = 0) {
		static $cache = array();
		if(!isset($cache[$view.'.'.$layout])){
			$filters = array(
				'a.type=\'component\'',
				'a.published=1',
				'b.title IS NOT NULL'
			);

			$user = JFactory::getUser();
			$accesses = JAccess::getAuthorisedViewLevels(@$user->id);
			if(!empty($accesses)){
				$filters[]='a.access IN ('.implode(',',$accesses).')';
			}

			$filters[] = 'a.client_id=0';

			if(empty($view)){
				$filters[] = 'a.link LIKE \'index.php?option=com_hikashop&view=%\'';
			}else{
				$filters[] = 'a.link='.$this->database->Quote('index.php?option=com_hikashop&view='.($view=='manufacturer'?'category':$view).'&layout='.$layout);
			}

			$lang = JFactory::getLanguage();
			$tag = $lang->getTag();
			$filters[] = "a.language IN ('*', '', ".$this->database->Quote($tag).")";


			$query="SELECT a.id FROM ".hikashop_table('menu',false).' AS a INNER JOIN `#__menu_types` as b on a.menutype = b.menutype WHERE '.implode(' AND ',$filters);
			$this->database->setQuery($query);
			$cache[$view.'.'.$layout] = $this->database->loadColumn();
		}
		if($id){
			if(is_array($cache[$view.'.'.$layout]) && count($cache[$view.'.'.$layout])){
				foreach($cache[$view.'.'.$layout] as $current_id){
					if($current_id==$id){
						return (int)$id;
					}
				}
			}
			return 0;
		}


		if(in_array($view,array('product','manufacturer','category'))){
			if(is_array($cache[$view.'.'.$layout]) && count($cache[$view.'.'.$layout])){
				$app = JFactory::getApplication();
				$config = hikashop_config();

				$this->database->setQuery('SELECT category_id FROM '.hikashop_table('category').' WHERE category_type = '.$this->database->quote('manufacturer'));
				$brandId = $this->database->loadResult();
				if(empty($brandId))
					$brandId = 0;

				foreach($cache[$view.'.'.$layout] as $current_id){

					if(HIKASHOP_J30){
						$menuItem = $app->getMenu()->getItem($current_id);
						$categoryParams = new stdClass();
						if(isset($menuItem->params))
							$categoryParams = $menuItem->params->get('hk_category');
						if((isset($categoryParams->content_type) && $categoryParams->content_type == 'manufacturer') || (isset($categoryParams->category) && $categoryParams->category == $brandId))
							return (int)$current_id;
					}

					$options = $config->get('menu_'.$current_id,null);
					if (isset($options['content_type']) && $options['content_type'] == $view) {
						return (int)$current_id;
					}
				}
				return (int)reset($cache[$view.'.'.$layout]);
			}
			return 0;
		}

		if(is_array($cache[$view.'.'.$layout]) && count($cache[$view.'.'.$layout])){
			return (int)reset($cache[$view.'.'.$layout]);
		}
		return 0;
	}

	function save(&$element){
		$query="SELECT a.extension_id FROM ".hikashop_table('extensions',false).' AS a WHERE a.type=\'component\' AND a.element=\''.HIKASHOP_COMPONENT.'\'';
		$this->database->setQuery($query);
		$element->component_id = $this->database->loadResult();

		if(empty($element->id)){
			$element->params['show_page_title']=1;
		}
		if(!empty($element->params)&&is_array($element->params)){
			$params = '';
			foreach($element->params as $k => $v){
				$params.=$k.'='.$v."\n";
			}
			$element->params = rtrim($params,"\n");
		}
		$element->id = parent::save($element);

		if($element->id && HIKASHOP_J30){

			$plugin = JPluginHelper::getPlugin('system', 'cache');
			$params = new JRegistry(@$plugin->params);

			$options = array(
				'defaultgroup'	=> 'page',
				'browsercache'	=> $params->get('browsercache', false),
				'caching'		=> false,
			);

			$cache		= JCache::getInstance('page', $options);
			$cache->clean();
		}
		return $element->id;
	}

	function delete(&$elements){
		$result = parent::delete($elements);
		if($result){
			if(!is_array($elements)){
				$elements=array($elements);
			}
			if(!empty($elements)){
				$ids = array();
				foreach($elements as $id){
					$ids[]=$this->database->Quote('menu_'.(int)$id);
				}
				$query = 'DELETE FROM '.hikashop_table('config').' WHERE config_namekey IN ('.implode(',',$ids).');';
				$this->database->setQuery($query);
				return $this->database->execute();
			}
		}
		return $result;
	}

	function attachAssocModule($id, $displayMessage = true){
		$menu = $this->get($id);
		if(!empty($menu->link) && strpos($menu->link,'view=product')===false){
			if($menu->hikashop_params['content_type']!='manufacturer'){
				$menu->hikashop_params['content_type']='category';
			}
		}else{
			$menu->hikashop_params['content_type']='category';
		}
		$params =& $menu->hikashop_params;
		$module_id = $this->createAssocModule($params,$id);
		if(!empty($module_id)){
			$configData=new stdClass();
			$params['modules']=$module_id;
			$name = 'menu_'.$id;
			$configData->$name = $params;
			$config =& hikashop_config();
			if($config->save($configData)){
				$config->set($name,$params);
				if($displayMessage) {
					$app = JFactory::getApplication();
					$app->enqueueMessage(JText::_( 'HIKASHOP_SUCC_SAVED' ));
				}
			}
		}
		return true;
	}
	function createMenu(&$moduleOtpions,$id){

		$alias = 'hikashop-menu-for-module-'.$id;
		$this->database->setQuery('SELECT id FROM '.hikashop_table('menu',false).' WHERE alias=\''.$alias.'\'');
		$moduleOtpions['itemid'] = $this->database->loadResult();
		if(empty($moduleOtpions['itemid'])){
			$this->database->setQuery('SELECT menutype FROM '.hikashop_table('menu_types',false).' WHERE menutype=\'hikashop_hidden\'');
			$mainMenu = $this->database->loadResult();
			if(empty($mainMenu)){
				$this->database->setQuery('INSERT INTO '.hikashop_table('menu_types',false).' ( `menutype`,`title`,`description` ) VALUES ( \'hikashop_hidden\',\'HikaShop hidden menus\',\'This menu is used by HikaShop to store menus configurations\' )');
				$this->database->execute();
			}

			$this->database->setQuery('SELECT rgt FROM '.hikashop_table('menu',false).' WHERE id=1');
			$root = $this->database->loadResult();
			$element = new stdClass();
			$element->menutype = 'hikashop_hidden';
			$element->alias = $alias;
			$element->path = $alias;
			$element->link = 'index.php?option=com_hikashop&view=category&layout=listing';
			$element->type = 'component';
			$element->published = 1;
			$element->client_id = 0;
			$element->language = '*';
			$element->access = 1;
			$element->lft = $root;
			$element->rgt = $root+1;
			$element->level = 1;
			$element->parent_id = 1;
			$element->title = 'Menu item for category listing module '.$id;
			$this->save($element);
			$this->database->setQuery('UPDATE '.hikashop_table('menu',false).' SET rgt='.($root+2).' WHERE id=1');
			$this->database->execute();
			$this->database->setQuery('SELECT id FROM '.hikashop_table('menu',false).' WHERE alias=\''.$element->alias.'\'');
			$moduleOtpions['itemid'] = $this->database->loadResult();
		}
		if(!empty($moduleOtpions['itemid'])){
			$menuData = new stdClass();
			$menuData->id = $moduleOtpions['itemid'];
			$this->createMenuOption($menuData,$moduleOtpions);
		}
	}
	function createMenuOption(&$menuData,$default_params=null){

		$configClass =& hikashop_config();
		if(empty($default_params)){
			if(!isset($default_params['columns']))$default_params['columns'] = 1;
			$default_params = $configClass->get('default_params');
			$default_params['content_type'] = 'category';
			$default_params['layout_type']='div';
			$default_params['content_synchronize']='1';
			if($default_params['columns']==1){
				$default_params['columns']=3;
			}
		}

		$id = (int)@$menuData->id;
		if(!HIKASHOP_J30)
			$default_params['modules']=(int)$this->createAssocModule($default_params,$id);
		$name = 'menu_'.$id;
		$config=new stdClass();
		$config->$name = $default_params;
		if($configClass->save($config)){
			$configClass->set($name,$default_params);
		}
		$menuData->hikashop_params = $default_params;
		return true;
	}

	function createAssocModule(&$params,$id){
		if(HIKASHOP_J30)
			return false;
		if(!empty($params['modules'])){
			if(is_array($params['modules'])){
				$ids = implode(',',$params['modules']);
			}
			else{
				$ids = (int)$params['modules'];
			}
			$this->database->setQuery('SELECT * FROM '.hikashop_table('modules',false).' WHERE id IN ('.$ids.');');
			$modulesData = $this->database->loadObjectList('id');
			if(!is_array($modulesData) || !count($modulesData)){
				$params['modules']='';
			}
		}
		if(!empty($params['content_type']) && in_array($params['content_type'],array('category','manufacturer'))&&empty($params['modules'])){
			$config =& hikashop_config();
			$default_params = $config->get('default_params');
			$default_params['content_type'] = 'product';
			$default_params['layout_type']='div';
			$default_params['random']=0;
			$default_params['content_synchronize']='1';
			if(!isset($default_params['columns']))$default_params['columns'] = 1;
			if($default_params['columns']==1){
				$default_params['columns']=3;
			}
			$module = new stdClass();
			$module->hikashop_params = $default_params;
			$module->title = 'Associated products listing for '.$params['content_type'].' listing menu '.$id;
			$module->published=0;
			$module->position='left';
			$module->ordering=0;
			$module->module='mod_hikashop';
			$module->client_id=0;
			$module->showtitle=0;
			$modulesClass = hikashop_get('class.modules');
			return $modulesClass->save($module);
		}
		return false;
	}

	function getItemidFromCategory($category_id,$type='category') {
		$config = & hikashop_config();
		$values = $config->values;
		foreach ($values as $key => $value) {
			if (preg_match('#menu_([0-9]+)#', $key, $match)  && is_string($value->config_value)) {
				$options = hikashop_unserialize(base64_decode($value->config_value));
				if (isset($options['selectparentlisting']) && $options['selectparentlisting'] == $category_id) {
					$id = $this->loadAMenuItemId($type,'listing',$match[1]);
					if($id){
						return $id;
					}elseif($type!='product'){
						$id = $this->loadAMenuItemId('product','listing',$match[1]);
						if($id) return $id;
					}
				}
			}
		}
		return $this->loadAMenuItemId($type,'listing');
	}
}
