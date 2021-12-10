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
class pluginsmarketViewpluginsmarket extends hikamarketView {

	const ctrl = 'plugins';
	const name = 'PLUGINS';
	const icon = 'puzzle-piece';

	public function display($tpl = null) {
		$this->paramBase = HIKAMARKET_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		if(method_exists($this,$function)) {
			if($this->$function())
				return false;
		}
		parent::display($tpl);
	}

	public function listing() {
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		hikamarket::setTitle(JText::_(self::name), self::icon, self::ctrl);

		$config = hikamarket::config();
		$this->assignRef('config',$config);

		$toggleClass = hikamarket::get('helper.toggle');
		$this->assignRef('toggleClass', $toggleClass);

		$manage = hikamarket::isAllowed($config->get('acl_plugins_manage','all'));
		$this->assignRef('manage', $manage);

		$type = $app->getUserStateFromRequest(HIKAMARKET_COMPONENT.'.plugin_type', 'plugin_type', 'generator');
		$group = 'hikamarket'; //.$type;
		$query = 'SELECT extension_id as id, enabled as published, name, element FROM ' . hikamarket::table('extensions', false) . ' WHERE `folder` = ' . $db->Quote($group) . ' AND type=\'plugin\' ORDER BY enabled DESC, ordering ASC';
		$db->setQuery($query);
		$plugins = $db->loadObjectList();


		$this->assignRef('plugins', $plugins);
		$this->assignRef('plugin_type', $type);

		$toolbar = JToolBar::getInstance('toolbar');
		JToolBarHelper::divider();
		$toolbar->appendButton('Pophelp', self::ctrl . '-listing');
		$toolbar->appendButton('Link', HIKAMARKET_LNAME, JText::_('HIKAMARKET_CPANEL'), hikamarket::completeLink('dashboard'));
	}

	public function form() {
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$config = hikamarket::config();
		$task = hikaInput::get()->getString('task', '');

		$this->content = '';
		$this->plugin_name = hikaInput::get()->getCmd('name', '');
		if(empty($this->plugin_name)) {
			return false;
		}

		$db = JFactory::getDBO();
		$plugin = hikamarket::import('hikamarket', $this->plugin_name);
		if(!$plugin || !method_exists($plugin, 'type')) {
			$pluginClass = hikamarket::get('class.plugins');
			$plugin = $pluginClass->getByName('hikamarket', $this->plugin_name);
			if(!empty($plugin) && (!empty($plugin->id) || !empty($plugin->extension_id))) {
				$url = 'index.php?option=com_plugins&task=plugin.edit&extension_id=' . $plugin->extension_id;
				$app->redirect($url);
			}
			return false;
		}
		$this->plugin_type = $plugin->type();

		$query = '';
		if(in_array($this->plugin_type, array('generator','consumer'))){
			$query = 'SELECT * FROM ' . hikamarket::table($this->plugin_type).' WHERE ' . $this->plugin_type . '_type = '.$db->Quote($this->plugin_name);
			$query .= ' ORDER BY ' . $this->plugin_type . '_ordering ASC';
		}
		if(empty($query))
			return false;
		$db->setQuery($query);
		$elements = $db->loadObjectList($this->plugin_type.'_id');

		if(!empty($elements)){
			$params_name = $this->plugin_type.'_params';
			foreach($elements as $k => $el){
				if(!empty($el->$params_name)){
					$elements[$k]->$params_name = hikamarket::unserialize($el->$params_name);
				}
			}
		}

		$multiple_plugin = false;
		if(method_exists($plugin, 'isMultiple')) {
			$multiple_plugin = $plugin->isMultiple();
		}

		$function = 'onPluginConfiguration';
		$ctrl = '&plugin_type='.$this->plugin_type.'&task='.$task.'&name='.$this->plugin_name;
		if($multiple_plugin === true) {
			$subtask = hikaInput::get()->getCmd('subtask','');
			$ctrl .= '&subtask='.$subtask;
			if(empty($subtask)) {
				$function = 'onPluginMultipleConfiguration';
			}
			$cid = hikamarket::getCID($this->plugin_type.'_id');
			if(isset($elements[$cid])){
				$this->assignRef('element', $elements[$cid]);
				$ctrl .= '&'.$this->plugin_type.'_id='.$cid;
			}
		} else {
			if(!empty($elements)) {
				$this->assignRef('element', reset($elements));
			}
		}
		$this->assignRef('elements', $elements);

		if(method_exists($plugin, $function)) {
			ob_start();
			$plugin->$function($elements);
			$this->content = ob_get_clean();
			$this->data = $plugin->getProperties();
		}

		$this->assignRef('name', $this->plugin_name);
		$this->assignRef('plugin', $plugin);
		$this->assignRef('multiple_plugin', $multiple_plugin);
		$this->assignRef('content', $this->content);
		$this->assignRef('plugin_type', $this->plugin_type);

		if(empty($plugin->pluginView)) {
			$this->content .= $this->loadPluginTemplate(@$plugin->view);
		}

		hikamarket::setTitle(JText::_('HIKAM_PLUGIN').' '.$this->name, self::icon, self::ctrl.$ctrl);
		return true;
	}

	private function loadPluginTemplate($view = '') {
		static $previousType = '';

		$app = JFactory::getApplication();

		$this->subview = '';
		if(!empty($view)) {
			$this->subview = '_' . $view;
		}

		$name = $this->plugin_name . '_configuration' . $this->subview . '.php';
		$path = JPATH_THEMES . DS . $app->getTemplate() . DS . 'hikamarket' . DS . $name;
		if(!file_exists($path)) {
			$path = JPATH_PLUGINS . DS . 'hikamarket' . DS . $this->plugin_name . DS . $name;
			if(!file_exists($path))
				return '';
		}
		ob_start();
		require($path);
		return ob_get_clean();
	}

}
