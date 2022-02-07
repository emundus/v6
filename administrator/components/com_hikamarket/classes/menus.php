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
class hikamarketMenusClass extends hikamarketClass {

	protected $tables = array('joomla.menu');
	protected $pkeys = array('id');
	protected $toggle = array('published' => 'id');

	public function get($id, $default = '') {
		$obj = parent::get($id);
		$config = hikamarket::config();
		$shopConfig = hikamarket::config(false);

		if(is_null($obj))
			$obj = new stdClass();

		if(!empty($obj->id))
			$obj->hikamarket_params = $config->get('menu_' . $obj->id, null);

		if(empty($obj->hikamarket_params)) {
			$obj->hikamarket_params = $shopConfig->get('default_params', null);
			$obj->hikamarket_params['content_type'] = '';
			if(!empty($obj->link) && substr($obj->link, 0, 32) == 'index.php?option=com_hikamarket&') {
				$link_params = explode('&', substr($obj->link, 32));
				foreach($link_params as $link_param) {
					if(!empty($link_param) && strpos($link_param, '=') !== false) {
						list($p,$v) = explode('=', $link_param, 2);
						if($p == 'view' || $p == 'ctrl')
							$obj->hikamarket_params['content_type'] = str_replace('market', '', $v);
					}
				}
			}
		}
		$obj->content_type = $obj->hikamarket_params['content_type'];

		$this->loadParams($obj);
		return $obj;
	}

	protected function loadParams(&$result) {
		if(empty($result->params))
			return;

		$lines = explode("\n", $result->params);
		$result->params = array();
		foreach($lines as $line) {
			$param = explode('=', $line, 2);
			if(count($param) == 2)
				$result->params[$param[0]] = $param[1];
		}
	}

	public function saveForm() {
		$module = new stdClass();
		$formData = hikaInput::get()->get('menu', array(), 'array');
		if(!empty($formData)) {
			foreach($formData as $column => $value) {
				hikamarket::secureField($column);
				if(is_array($value)) {
					$module->$column = array();
					foreach($value as $k2 => $v2) {
						hikamarket::secureField($k2);
						$module->{$column}[$k2] = strip_tags($v2);
					}
				} else {
					$module->$column = strip_tags($value);
				}
			}
			$module->link = 'index.php?option='.HIKAMARKET_COMPONENT.'&view='.$module->content_type.'market&layout=listing';
			$content_type = $module->content_type;
			unset($module->content_type);
		}

		$new = false;
		if(empty($module->id)) {
			$new = true;
			if(empty($module->alias))
				$module->alias = preg_replace('#[^a-z_0-9-]#i','',$module->title);
		}

		$result = $this->save($module);

		if($result) {
			$element = array();
			$formData = hikaInput::get()->get('config', array(), 'array');
			$params_name = 'menu_'.(int)$module->id;

			if($new)
				$post_name = 'menu_0';
			else
				$post_name = $params_name;

			if(!empty($formData[$post_name])) {
				foreach($formData[$post_name] as $column => $value) {
					hikamarket::secureField($column);
					$element[$column] = strip_tags($value);
				}
			}

			$element['content_type'] = $content_type;
			$configClass = hikamarket::config();
			$config = new stdClass();
			$config->$params_name = $element;

			if($configClass->save($config))
				$configClass->set($params_name, $element);
		}
		return $result;
	}

	protected function displayErrors($id) {
		static $displayed = false;
		if($displayed)
			return;

		$displayed = true;
		$app = JFactory::getApplication();
		$app->enqueueMessage(JText::_('MENU_WITHOUT_ASSOCIATED_MODULE'));
		$app->enqueueMessage(JText::_('ASSOCIATED_MODULE_NEEDED'));
		$app->enqueueMessage(JText::sprintf('ADD_MODULE_AUTO', hikamarket::completeLink('menus&task=add_module&cid='.$id.'&'.hikamarket::getFormToken().'=1')));
	}

	public function save(&$element) {
		$query = 'SELECT a.extension_id FROM ' . hikamarket::table('extensions',false).' AS a WHERE a.type=\'component\' AND a.element=\''.HIKAMARKET_COMPONENT.'\'';
		$this->db->setQuery($query);
		$element->component_id = $this->db->loadResult();

		if(empty($element->id))
			$element->params['show_page_title'] = 1;

		if(!empty($element->params) && is_array($element->params)) {
			$params = '';
			foreach($element->params as $k => $v) {
				$params .= $k . '=' . $v . "\n";
			}
			$element->params = rtrim($params, "\n");
		}

		$element->id = parent::save($element);
		return $element->id;
	}

	public function delete(&$elements) {
		$result = parent::delete($elements);
		if($result) {
			if(!is_array($elements))
				$elements = array($elements);

			if(!empty($elements)) {
				$ids = array();
				foreach($elements as $id) {
					$ids[] = $this->db->Quote('menu_' . (int)$id);
				}
				$query = 'DELETE FROM ' . hikamarket::table('config') . ' WHERE config_namekey IN (' . implode(',', $ids) . ');';
				$this->db->setQuery($query);
				return $this->db->execute();
			}
		}
		return $result;
	}

	public function attachAssocModule($id) {
		$menu = $this->get($id);

		$menu->hikamarket_params['content_type'] = 'vendor';

		$params =& $menu->hikamarket_params;
		$module_id = $this->createAssocModule($params,$id);
		if(!empty($module_id)) {
			$configData = new stdClass();
			$params['modules'] = $module_id;
			$name = 'menu_' . $id;
			$configData->$name = $params;
			$config = hikamarket::config();
			if($config->save($configData)){
				$config->set($name, $params);
				$app = JFactory::getApplication();
				$app->enqueueMessage(JText::_('HIKASHOP_SUCC_SAVED'));
			}
		}
		return true;
	}

	public function createMenu(&$moduleOtpions, $id) {
		$alias = 'hikamarket-menu-for-module-' . $id;
		$this->db->setQuery('SELECT id FROM ' . hikamarket::table('menu',false).' WHERE alias=\''.$alias.'\'');
		$moduleOtpions['itemid'] = $this->db->loadResult();

		if(empty($moduleOtpions['itemid'])) {
			$this->db->setQuery('SELECT menutype FROM '.hikamarket::table('menu_types',false).' WHERE menutype=\'hikashop_hidden\'');
			$mainMenu = $this->db->loadResult();
			if(empty($mainMenu)) {
				$this->db->setQuery('INSERT INTO '.hikamarket::table('menu_types',false).' (`menutype`,`title`,`description`) VALUES ( \'hikashop_hidden\',\'HikaShop hidden menus\',\'This menu is used by HikaShop to store menus configurations\' )');
				$this->db->execute();
			}

			$this->db->setQuery('SELECT rgt FROM '.hikamarket::table('menu',false).' WHERE id=1');
			$root = $this->db->loadResult();
			$element = new stdClass();
			$element->menutype = 'hikashop_hidden';
			$element->alias = $alias;
			$element->link = 'index.php?option=com_hikamarket&view=vendor&layout=listing';
			$element->type = 'component';
			$element->published = 1;
			$element->client_id = 0;
			$element->language = '*';
			$element->access = 1;
			$element->lft = $root;
			$element->rgt = $root+1;
			$element->level = 1;
			$element->parent_id = 1;
			$element->title = 'Menu item for vendor listing module '.$id;
			$this->save($element);
			$this->db->setQuery('UPDATE '.hikamarket::table('menu',false).' SET rgt='.($root+2).' WHERE id=1');
			$this->db->execute();
			$this->db->setQuery('SELECT id FROM '.hikashop_table('menu',false).' WHERE alias=\''.$element->alias.'\'');
			$moduleOtpions['itemid'] = $this->db->loadResult();
		}

		if(!empty($moduleOtpions['itemid'])) {
			$menuData = new stdClass();
			$menuData->id = $moduleOtpions['itemid'];
			$this->createMenuOption($menuData, $moduleOtpions);
		}
	}

	public function createMenuOption(&$menuData, $default_params = null) {
		$configClass = hikamarket::config();
		$shopConfig = hikamarket::config(false);
		if(empty($default_params)) {
			if(!isset($default_params['columns']))
				$default_params['columns'] = 1;
			$default_params = $shopConfig->get('default_params');
			$default_params['content_type'] = 'vendor';
			$default_params['layout_type'] = 'div';
			$default_params['content_synchronize'] = '1';
			if($default_params['columns'] == 1)
				$default_params['columns'] = 3;
		}

		$id = (int)@$menuData->id;
		$default_params['modules'] = '';
		$default_params['modules'] = (int)$this->createAssocModule($default_params, $id);
		$name = 'menu_'.$id;
		$config = new stdClass();
		$config->$name = $default_params;
		if($configClass->save($config)) {
			$configClass->set($name, $default_params);
		}
		$menuData->hikamarket_params = $default_params;
		return true;
	}

	public function createAssocModule(&$params,$id){
		if(!empty($params['modules'])) {
			if(is_array($params['modules'])) {
				$ids = implode(',', $params['modules']);
			} else {
				$ids = (int)$params['modules'];
			}
			$this->db->setQuery('SELECT * FROM '.hikamarket::table('modules',false).' WHERE id IN ('.$ids.');');
			$modulesData = $this->db->loadObjectList('id');
			if(!is_array($modulesData) || !count($modulesData)) {
				$params['modules'] = '';
			}
		}
		return false;
	}
}
