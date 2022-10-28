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
class hikamarketPlugin extends JPlugin {
	protected $db;
	protected $multiple = false;
	protected $type = 'generic';
	protected $plugin_params = null;
	protected $doc_form = '';
	protected $doc_listing = '';

	public function __construct(&$subject, $config) {
		$this->db = JFactory::getDBO();
		parent::__construct($subject, $config);
	}

	public function pluginParams($id = 0, $name = '') {
		$this->plugin_params = null;
		if(in_array($this->type, array())) {
			if($id > 0) {
				$this->db->setQuery('SELECT '.$this->type.'_params FROM '.hikamarket::table($this->type).' WHERE '.$this->type.'_id = ' . (int)$id);
				$this->db->execute();
				$data = $this->db->loadResult();
				if(!empty($data)) {
					$this->plugin_params = hikamarket::unserialize($data);
					return true;
				}
			} else if(!empty($name)) {
				$this->db->setQuery('SELECT '.$this->type.'_params FROM '.hikamarket::table($this->type).' WHERE '.$this->type.'_type = ' . $this->db->Quote($name));
				$this->db->execute();
				$data = $this->db->loadResult();
				if(!empty($data)) {
					$this->plugin_params = hikamarket::unserialize($data);
					return true;
				}
			}
		} else if(!empty($name)) {
			$pluginsClass = hikamarket::get('shop.class.plugins');
			$this->plugin_params = $pluginsClass->getByName('hikamarket', $name);
		}
		return false;
	}

	public function type() {
		return $this->type;
	}

	public function isMultiple() {
		return $this->multiple;
	}

	public function configurationHead() {
		return array();
	}

	public function configurationLine($id = 0) {
		return null;
	}

	public function listPlugins($name, &$values, $full = true){
		if(in_array($this->type, array('plugin'))) {
			if($this->multiple) {
				$query = 'SELECT '.$this->type.'_id as id, '.$this->type.'_name as name FROM '.hikamarket::table($this->type).' WHERE '.$this->type.'_type = ' . $this->db->Quote($name) . ' AND '.$this->type.'_published = 1 ORDER BY '.$this->type.'_ordering';
				$this->db->setQuery($query);
				$plugins = $this->db->loadObjectList();
				if($full) {
					foreach($plugins as $plugin) {
						$values['plg.'.$name.'-'.$plugin->id] = $name.' - '.$plugin->name;
					}
				} else {
					foreach($plugins as $plugin) {
						$values[] = $plugin->id;
					}
				}
			} else {
				$values['plg.'.$name] = $name;
			};
		}
	}

	public function onPluginConfiguration(&$elements) {
		$this->plugins =& $elements;
		$this->pluginName = hikaInput::get()->getCmd('name', $this->type);
		$this->pluginView = '';

		$plugin_id = hikaInput::get()->getInt('plugin_id',0);
		if($plugin_id == 0) {
			$plugin_id = hikaInput::get()->getInt($this->type.'_id', 0);
		}

		$toolbar = JToolBar::getInstance('toolbar');
		JToolBarHelper::save();
		JToolBarHelper::apply();
		if($plugin_id == 0) {
			$toolbar->appendButton('Link', 'cancel', JText::_('HIKA_CANCEL'), hikamarket::completeLink('plugins') );
		} else {
			$toolbar->appendButton('Link', 'cancel', JText::_('HIKA_CANCEL'), hikamarket::completeLink('plugins&plugin_type='.$this->type.'&task=edit&name='.$this->pluginName) );
		}
		JToolBarHelper::divider();
		$toolbar->appendButton('Pophelp','plugins-'.$this->doc_form.'form');

		if(empty($this->title)) {
			$this->title = JText::_('HIKAMARKET_PLUGIN_METHOD');
		}
		if($plugin_id == 0) {
			hikamarket::setTitle($this->title, 'puzzle-piece', 'plugins&plugin_type='.$this->type.'&task=edit&name='.$this->pluginName.'&subtask=edit');
		} else {
			hikamarket::setTitle($this->title, 'puzzle-piece', 'plugins&plugin_type='.$this->type.'&task=edit&name='.$this->pluginName.'&subtask=edit&plugin_id='.$plugin_id);
		}
	}

	public function onPluginMultipleConfiguration(&$elements) {
		if(!$this->multiple)
			return;

		$app = JFactory::getApplication();
		$this->plugins =& $elements;
		$this->pluginName = hikaInput::get()->getCmd('name', $this->type);
		$this->pluginView = 'sublisting';
		$this->subtask = hikaInput::get()->getCmd('subtask','');
		$this->task = hikaInput::get()->getVar('task');

		if(empty($this->title)) {
			$this->title = JText::_('HIKAMARKET_PLUGIN_METHOD');
		}

		if($this->subtask == 'copy') {
			if(!in_array($this->task, array('orderup', 'orderdown', 'saveorder'))) {
				$pluginIds = hikaInput::get()->get('cid', array(), 'array');
				hikamarket::toInteger($pluginIds);
				$result = true;
				if(!empty($pluginIds) && in_array($this->type, array('plugin'))) {
					$this->db->setQuery('SELECT * FROM '.hikamarket::table($this->type).' WHERE '.$this->type.'_id IN ('.implode(',',$pluginIds).')');
					$plugins = $this->db->loadObjectList();
					$helper = hikamarket::get('class.'.$this->type);
					if($helper) {
						$plugin_id = $this->type . '_id';
						foreach($plugins as $plugin) {
							unset($plugin->$plugin_id);
							if(!$helper->save($plugin)) {
								$result = false;
							}
						}
					}
				}
				if($result) {
					$app->enqueueMessage(JText::_('HIKASHOP_SUCC_SAVED'), 'message');
					$app->redirect(hikamarket::completeLink('plugins&plugin_type='.$this->type.'&task=edit&name='.$this->pluginName, false, true));
				}
			}
		}

		$bar = JToolBar::getInstance('toolbar');
		JToolBarHelper::divider();
		JToolBarHelper::publishList();
		JToolBarHelper::unpublishList();
		JToolBarHelper::divider();
		$bar->appendButton('Standard', 'copy', JText::_('HIKA_COPY'), 'edit', true, false);
		$bar->appendButton('Link', 'new', JText::_('HIKA_NEW'), hikamarket::completeLink('plugins&plugin_type='.$this->type.'&task=edit&name='.$this->pluginName.'&subtask=edit'));
		JToolBarHelper::cancel();
		JToolBarHelper::divider();
		$bar->appendButton('Pophelp', 'plugins-'.$this->doc_listing.'sublisting');

		hikamarket::setTitle($this->title, 'plugin', 'plugins&plugin_type='.$this->type.'&task=edit&name='.$this->pluginName);
		$this->toggleClass = hikamarket::get('helper.toggle');
		jimport('joomla.html.pagination');
		$this->pagination = new JPagination(count($this->plugins), 0, false);
		$this->order = new stdClass();
		$this->order->ordering = true;
		$this->order->orderUp = 'orderup';
		$this->order->orderDown = 'orderdown';
		$this->order->reverse = false;
		$app->setUserState(HIKAMARKET_COMPONENT.$this->type.'._plugin_type', $this->pluginName);
	}
}
