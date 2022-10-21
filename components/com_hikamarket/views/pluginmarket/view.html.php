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
class pluginmarketViewpluginmarket extends hikamarketView {

	protected $ctrl = 'plugin';
	protected $icon = 'plugin';
	protected $triggerView = true;

	public function display($tpl = null, $params = array()) {
		$this->params =& $params;
		$fct = $this->getLayout();
		if(method_exists($this, $fct)) {
			if($this->$fct() === false)
				return;
		}
		parent::display($tpl);
	}

	public function listing($tpl = null) {
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$ctrl = '';

		$type = hikaInput::get()->getCmd('plugin_type', 'payment');
		if(!in_array($type, array('payment', 'shipping', 'generic')))
			return false;

		$this->assignRef('type', $type);

		$this->paramBase = HIKAMARKET_COMPONENT.'.'.$this->getName().'.'.$type.'.listing';

		$vendor = hikamarket::loadVendor(true, false);
		$this->assignRef('vendor', $vendor);

		$config = hikamarket::config();
		$this->assignRef('config', $config);

		$this->loadRef(array(
			'toggleClass' => 'helper.toggle',
			'currencyClass' => 'shop.class.currency',
			'zoneClass' => 'shop.class.zone',
			'dropdownHelper' => 'shop.helper.dropdown',
		));

		$manage = hikamarket::acl($type.'plugin/edit');
		$this->assignRef('manage', $manage);

		$plugin_action_publish = hikamarket::acl($type.'plugin/edit/published');
		$plugin_action_delete = hikamarket::acl($type.'plugin/delete');
		$plugin_actions = $plugin_action_publish || $plugin_action_delete;
		$this->assignRef('plugin_action_publish', $plugin_action_publish);
		$this->assignRef('plugin_action_delete', $plugin_action_delete);
		$this->assignRef('plugin_actions', $plugin_actions);

		global $Itemid;
		$url_itemid = '';
		if(!empty($Itemid))
			$url_itemid = '&Itemid='.$Itemid;
		$this->Itemid = $Itemid;
		$this->assignRef('url_itemid', $url_itemid);

		$plugin_configs = array(
			'payment' => array(
				'table' => 'shop.payment',
				'main_key' => 'payment_id',
				'order_sql_value' => 'plugin.payment_id'
			),
			'shipping' => array(
				'table' => 'shop.shipping',
				'main_key' => 'shipping_id',
				'order_sql_value' => 'plugin.shipping_id'
			),
			'generic' => array(
				'table' => 'shop.plugin',
				'main_key' => 'plugin_id',
				'order_sql_value' => 'plugin.plugin_id'
			),
		);
		$cfg = $plugin_configs[$type];

		$default_sort_dir = 'asc';

		$listing_filters = array(
			'vendors' => -1,
			'published' => -1,
		);

		$pageInfo = $this->getPageInfo($cfg['order_sql_value'], $default_sort_dir, $listing_filters);

		$filters = array();
		$plugin_searchMaps = array(
			'payment' => array(
				'plugin.payment_type',
				'plugin.payment_name',
				'plugin.payment_id'
			),
			'shipping' => array(
				'plugin.shipping_type',
				'plugin.shipping_name',
				'plugin.shipping_id'
			),
			'generic' => array(
				'plugin.plugin_type',
				'plugin.plugin_name',
				'plugin.plugin_id'
			),
		);
		$searchMap = $plugin_searchMaps[$type];
		$order = '';

		$filter_type = ($type == 'generic') ? 'plugin' : $type;
		if($vendor->vendor_id > 1) {
			$filters['vendor'] = 'plugin.'.$filter_type.'_vendor_id = ' . (int)$vendor->vendor_id;
		} else {
			$vendorType = hikamarket::get('type.filter_vendor');
			$this->assignRef('vendorType', $vendorType);
			if($pageInfo->filter->vendors >= 0) {
				if($pageInfo->filter->vendors > 1)
					$filters['vendor'] = 'plugin.'.$filter_type.'_vendor_id = '.(int)$pageInfo->filter->vendors;
				else
					$filters['vendor'] = 'plugin.'.$filter_type.'_vendor_id <= 1';
			}
		}
		if($pageInfo->filter->published >= 0) {
			$filters['published'] = 'plugin.'.$filter_type.'_published = ' . ($pageInfo->filter->published ? '1' : '0');
		}

		$this->processFilters($filters, $order, $searchMap);

		$extrafilters = null;

		JPluginHelper::importPlugin('hikashop');
		if(in_array($type, array('shipping', 'payment')))
			JPluginHelper::importPlugin('hikashop'.$type);
		$view =& $this;
		$app->triggerEvent('onBeforeHikaPluginConfigurationListing', array($type, &$filters, &$order, &$searchMap, &$extrafilters, &$view));

		$query = 'FROM '.hikamarket::table($cfg['table']).' AS plugin '.$filters.$order;
		$db->setQuery('SELECT * '.$query, (int)$pageInfo->limit->start, (int)$pageInfo->limit->value);

		$rows = $db->loadObjectList();
		$this->assignRef('plugins', $rows);

		$db->setQuery('SELECT COUNT(*) '.$query);
		$pageInfo->elements = new stdClass();
		$pageInfo->elements->total = $db->loadResult();
		$pageInfo->elements->page = count($rows);

		if($pageInfo->elements->page) {

		}

		$listing_columns = array();
		$pluginInterfaceClass = hikamarket::get('shop.class.plugin');
		$pluginInterfaceClass->fillListingColumns($rows, $listing_columns, $this, $type);

		$app->triggerEvent('onAfterHikaPluginConfigurationListing', array($type, &$rows, &$listing_columns, &$view));

		$this->assignRef('listing_columns', $listing_columns);

		$this->toolbar = array(
			array(
				'icon' => 'back',
				'fa' => 'fa-arrow-circle-left',
				'name' => JText::_('HIKA_BACK'), 'url' => hikamarket::completeLink('vendor')
			),
			array(
				'icon' => 'new',
				'fa' => 'fa-plus-circle',
				'name' => JText::_('HIKA_NEW'),
				'url' => hikamarket::completeLink('plugin&plugin_type='.$type.'&task=add'),
				'pos' => 'right',
				'display' => hikamarket::acl($type.'plugin/add')
			)
		);

		$this->getPagination();
	}


	public function form($tpl = null) {
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$ctrl = '';
		$task = hikaInput::get()->getCmd('task', 'edit');

		$type = hikaInput::get()->getCmd('plugin_type', 'payment');
		if(!in_array($type, array('payment', 'shipping', 'generic')))
			return false;

		$this->assignRef('type', $type);

		$this->content = '';
		$this->plugin_name = hikaInput::get()->getCmd('name', '');

		if($type == 'plugin') {
			$plugin = hikamarket::import('hikashop', $this->plugin_name);
			if(!is_subclass_of($plugin, 'hikashopPlugin'))
				return false;
		} else
			$plugin = hikamarket::import('hikashop' . $type, $this->plugin_name);

		if(!$plugin)
			return false;

		$multiple_plugin = false;
		$multiple_interface = false;
		if(method_exists($plugin, 'isMultiple')) {
			$multiple_interface = true;
			$multiple_plugin = $plugin->isMultiple();
		}

		$subtask = hikaInput::get()->getCmd('subtask', 'edit');
		if($multiple_plugin && empty($subtask)) {
			$querySelect = array();
			$queryFrom = array();
			$queryWhere = array();
			$filters = array();

			JPluginHelper::importPlugin('hikashop');
			$app->triggerEvent('onHikaPluginListing', array($type, &$querySelect, &$queryFrom, &$queryWhere, &$filters));

			if(!empty($querySelect)) $querySelect = ', ' . implode(',', $querySelect);
			else $querySelect = '';

			if(!empty($queryFrom)) $queryFrom = ', ' . implode(',', $queryFrom);
			else $queryFrom = '';

			if(!empty($queryWhere)) $queryWhere = ' AND (' . implode(') AND (', $queryWhere) . ') ';
			else $queryWhere = '';

			$this->assignRef('filters', $filters);
		} else {
			$querySelect = '';
			$queryFrom = '';
			$queryWhere = '';
		}

		$query = 'SELECT plugin.* ' . $querySelect .
			' FROM ' . hikashop_table($type) . ' as plugin ' . $queryFrom .
			' WHERE (plugin.' . $type . '_type = ' . $db->Quote($this->plugin_name) . ') ' . $queryWhere .
			' ORDER BY plugin.' . $type . '_ordering ASC';
		$db->setQuery($query);
		$elements = $db->loadObjectList($type.'_id');

		if(!empty($elements)) {
			$params_name = $type.'_params';
			foreach($elements as $k => $el) {
				if(!empty($el->$params_name)) {
					$elements[$k]->$params_name = hikamarket::unserialize($el->$params_name);
				}
			}
		}

		$function = 'pluginConfiguration';
		$ctrl = '&plugin_type='.$type.'&task='.$task.'&name='.$this->plugin_name;
		if($multiple_plugin === true) {
			$ctrl .= '&subtask='.$subtask;
			if(empty($subtask)) {
				$function = 'pluginMultipleConfiguration';
			} else {
				$typeFunction = 'on' . ucfirst($type) . 'Configuration';
				if(method_exists($plugin, $typeFunction)) {
					$function = $typeFunction;
				}
			}
			$cid = hikashop_getCID($type.'_id');
			if(isset($elements[$cid])) {
				$this->assignRef('element', $elements[$cid]);
				$configParam =& $elements[$cid];
				$ctrl .= '&' . $type . '_id=' . $cid;
			} else {
				$configParam = new stdClass;
				$this->assignRef('element', $configParam);
			}
		} else {
			$configParam =& $elements;

			$element = null;
			if(!empty($elements)) {
				$element = reset($elements);
			}
			$this->assignRef('element', $element);
			$typeFunction = 'on' . ucfirst($type) . 'Configuration';
			if(method_exists($plugin, $typeFunction)) {
				$function = $typeFunction;
			}
		}
		$this->assignRef('elements', $elements);

		if($multiple_interface && !isset($subtask) || !empty($subtask)) {
			$extra_config = array();
			$extra_blocks = array();

			JPluginHelper::importPlugin('hikashop');
			$app->triggerEvent('onHikaPluginConfiguration', array($type, &$plugin, &$this->element, &$extra_config, &$extra_blocks));

			$this->assignRef('extra_config', $extra_config);
			$this->assignRef('extra_blocks', $extra_blocks);
		}

		if(method_exists($plugin, $function)) {
			if(empty($plugin->title))
				$plugin->title = JText::_('HIKA_PLUGIN').' '.$this->plugin_name;
			ob_start();
			$plugin->$function($configParam);
			$this->content = ob_get_clean();
			$this->data = $plugin->getProperties();
			$setTitle = false;
		}

		$this->assignRef('name', $this->plugin_name);
		$this->assignRef('plugin', $plugin);
		$this->assignRef('multiple_plugin', $multiple_plugin);
		$this->assignRef('multiple_interface', $multiple_interface);

		$this->main_form = array(
			$type.'_name' => array(
				'name' => 'HIKA_NAME',
				'type' => 'input'
			),
			$type.'_description' => array(
				'name' => 'HIKA_DESCRIPTION',
				'type' => 'wysiwyg'
			)
		);
		$this->restriction_form = array();

		if($multiple_plugin)
			$this->main_form[$type.'_published'] = array('HIKA_PUBLISHED', 'boolean', '0');

		$pluginInterfaceClass = null;
		switch($type) {
			case 'payment':
				$pluginInterfaceClass = hikamarket::get('class.payment');
				break;
			case 'shipping':
				$pluginInterfaceClass = hikamarket::get('class.shipping');
				break;
			case 'generic':
			default:
				$pluginInterfaceClass = hikamarket::get('class.plugin');
				break;
		}

		$fields = array();
		if(!empty($pluginInterfaceClass) && method_exists($pluginInterfaceClass, 'loadConfigurationFields'))
			$fields = $pluginInterfaceClass->loadConfigurationFields();

		if(!empty($fields['main']))
			$this->main_form = array_merge($this->main_form, $fields['main']);

		foreach($this->main_form as $k => $v) {
			$key = str_replace(array('params.'.$type.'_', $type.'_', '_'), array('', '', '-'), $k);
			if(!hikamarket::acl($type . 'plugin/edit/' . $key))
				unset($this->main_form[$k]);
		}

		if(!empty($fields['restriction']))
			$this->restriction_form = array_merge($this->restriction_form, $fields['restriction']);

		foreach($this->restriction_form as $k => $v) {
			$key = str_replace(array('params.'.$type.'_', $type.'_', '_'), array('', '', '-'), $k);
			if(!hikamarket::acl($type . 'plugin/edit/restriction/' . $key))
				unset($this->restriction_form[$k]);
		}

		if(empty($plugin->pluginView))
			$this->content .= $this->loadPluginTemplate(@$plugin->view, $type);

		$this->toolbar = array(
			'back' => array(
				'icon' => 'back',
				'fa' => 'fa-arrow-circle-left',
				'name' => JText::_('HIKA_BACK'),
				'url' => hikamarket::completeLink('plugin&plugin_type='.$type)
			),
			'apply' => array(
				'url' => '#apply',
				'fa' => 'fa-check-circle',
				'linkattribs' => 'onclick="return window.hikamarket.submitform(\'apply\',\'hikamarket_plugin_form\');"',
				'icon' => 'apply',
				'name' => JText::_('HIKA_APPLY'), 'pos' => 'right',
				'display' => hikamarket::acl($type.'plugin/edit')
			),
			'save' => array(
				'url' => '#save',
				'fa' => 'fa-save',
				'linkattribs' => 'onclick="return window.hikamarket.submitform(\'save\',\'hikamarket_plugin_form\');"',
				'icon' => 'save',
				'name' => JText::_('HIKA_SAVE'), 'pos' => 'right',
				'display' => hikamarket::acl($type.'plugin/edit')
			)
		);
	}

	public function add($tpl = null) {
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$config = hikamarket::config();
		$shopConfig = hikamarket::config(false);
		$vendor = hikamarket::loadVendor(true);

		$this->assignRef('config', $config);
		$this->assignRef('vendor', $vendor);

		$this->loadRef(array(
			'toggleClass' => 'helper.toggle',
			'radioType' => 'shop.type.radio',
		));

		$type = hikaInput::get()->getCmd('plugin_type', 'payment');
		if(!in_array($type, array('payment', 'shipping', 'generic')))
			return false;

		if(!hikamarket::acl($type.'plugin/add') )
			return false;

		if($type == 'generic') {
			$plugin_group = 'hikashop';
			$plugin_table = 'plugin';
		} else {
			$plugin_group = 'hikashop' . $type;
			$plugin_table = $type;
		}

		$filters = array();
		if($vendor->vendor_id <= 1)
			$filters['publish'] = -1;
		if($type == 'payment')
			$filters['currency'] = 0;
		$this->paramBase = 'plugins_'.$type;
		$pageInfo = $this->getPageInfo('', 'asc', $filters);

		if($vendor->vendor_id > 1)
			$pageInfo->filter->publish = 1;

		$query_select = array('extension_id as id', 'enabled as published', 'name', 'element');
		$query_table = 'extensions';
		$query_filters = array(
			'`folder` = '.$db->Quote($plugin_group),
			'type = \'plugin\''
		);
		if((int)$pageInfo->filter->publish >= 0)
			$query_filters[] = 'enabled = ' . (int)$pageInfo->filter->publish;

		if(!empty($pageInfo->search)) {
			$query_filters[] = 'name LIKE \'%' . $db->escape(HikaStringHelper::strtolower($pageInfo->search)) . '%\'';
		}
		$query_order = 'enabled DESC, name ASC, ordering ASC';

		$query = 'SELECT ' . implode(', ', $query_select) .
				' FROM ' . hikamarket::table($query_table, false) .
				' WHERE (' . implode(') AND (', $query_filters) . ')'.
				' ORDER BY ' . $query_order;

		$db->setQuery($query);
		$plugins = $db->loadObjectList();

		JPluginHelper::importPlugin($plugin_group);
		$view =& $this;
		$app->triggerEvent('onAfterHikaPluginConfigurationSelectionListing', array($type, &$plugins, &$view));

		$query = 'SELECT * FROM '.hikamarket::table('shop.'.$plugin_table);
		$db->setQuery($query);
		$obj = $db->loadObject();
		if(empty($obj))
			$app->enqueueMessage(JText::_('EDIT_PLUGINS_BEFORE_DISPLAY'));

		$currencies = null;
		if($type == 'payment') {
			$currencyClass = hikamarket::get('shop.class.currency');
			$mainCurrency = $shopConfig->get('main_currency',1);
			$currencyIds = $currencyClass->publishedCurrencies();
			if(!in_array($mainCurrency, $currencyIds))
				$currencyIds = array_merge(array($mainCurrency), $currencyIds);
			$null = null;
			$currencies = $currencyClass->getCurrencies($currencyIds, $null);

			$filter_currency = null;
			if(!empty($pageInfo->filter->currency) && !in_array((int)$pageInfo->filter->currency, $currencyIds))
				$pageInfo->filter->currency = 0;
			if(!empty($pageInfo->filter->currency))
				$filter_currency = $currencies[ (int)$pageInfo->filter->currency ]->currency_code;

			foreach($plugins as $key => &$plugin) {
				try{
					$p = hikamarket::import($plugin_group, $plugin->element);
				} catch(Exception $e) { $p = null; }
				if($vendor->vendor_id > 1 && (!method_exists($p, 'isMultiple') || !$p->isMultiple() || !empty($p->market_support))) {
					unset($plugins[$key]);
					unset($p);
					continue;
				}
				$plugin->accepted_currencies = array();
				if(isset($p->accepted_currencies)) {
					$plugin->accepted_currencies = $p->accepted_currencies;
					if(!empty($filter_currency) && !empty($p->accepted_currencies) && !in_array($filter_currency, $p->accepted_currencies))
						unset($plugins[$key]);
				}
				unset($p);
			}
			unset($plugin);
		} else if($vendor->vendor_id > 1) {
			foreach($plugins as $key => &$plugin) {
				try{
					$p = hikamarket::import($plugin_group, $plugin->element);
				} catch(Exception $e) { $p = null; }
				if(!method_exists($p, 'isMultiple') || !$p->isMultiple() || !empty($p->market_support))
					unset($plugins[$key]);
				unset($p);
			}
			unset($plugin);
		}

		$this->assignRef('plugins', $plugins);
		$this->assignRef('plugin_type', $type);
		$this->assignRef('currencies', $currencies);

		$this->toolbar = array(
			'back' => array(
				'icon' => 'back',
				'fa' => 'fa-arrow-circle-left',
				'name' => JText::_('HIKA_BACK'),
				'url' => hikamarket::completeLink('plugin&plugin_type='.$type)
			)
		);
	}

	protected function loadPluginTemplate($view = '', $type = '') {
		static $previousType = '';
		if(empty($type))
			$type = $previousType;
		else
			$previousType = $type;

		$app = JFactory::getApplication();
		$this->subview = '';
		if(!empty($view))
			$this->subview = '_' . $view;

		if(!isset($this->data['pluginConfig'])) {
			if($type == 'plugin')
				$type = '';

			$name = $this->name.'_configuration'.$this->subview.'.php';
			$path = JPATH_THEMES.DS.$app->getTemplate().DS.'hikashop'.$type.DS.$name;

			if(!file_exists($path)) {
				$path = JPATH_PLUGINS.DS.'hikashop'.$type.DS.$this->name.DS.$name;
				if(!file_exists($path))
					return '';
			}

			$this->pluginTemplateMode = 'html';

			ob_start();
			require($path);
			return ob_get_clean();
		}

		$paramsType = $type.'_params';

		$html = $this->processConfig($this->data['pluginConfig'], $type, $paramsType, @$this->element->$paramsType);
		return $html;
	}

	protected function getParamsData($configData, &$key, $type, $paramsType, &$localType) {
		$data = '';
		if(is_array($configData))
			$data = @$configData[$key];
		else if(!empty($configData))
			$data = @$configData->$key;

		if(empty($paramsType) && substr($key, 0, 7) == 'params.') {
			$localType = $type . '_params';
			$key = substr($key, 7);

			if(is_array($configData))
				$params = @$configData[$localType];
			else if(!empty($configData))
				$params = @$configData->$localType;

			if(is_array($params))
				$data = @$params[$key];
			else if(!empty($params))
				$data = @$params->$key;
		}

		return $data;
	}

	protected function processConfig($configs, $type, $paramsType, $configData, $id = '', $checkDisplay = false) {
		$html = '';
		if(empty($configs))
			return $html;

		$cache = array();

		if(!empty($id))
			$id = 'id="'.$id.'"';

		$html .= '<dl '.$id.' class="hikam_options large">'."\r\n";
		foreach($configs as $key => $config) {

			if(!isset($config['name'])) {
				$tmp = array(
					'name' => $config[0],
					'type' => $config[1],
					'data' => @$config[2]
				);
				$config = $tmp;
			}

			if(is_array($config['name'])) {
				$a = array_shift($config['name']);
				$label = vsprintf(JText::_($a), $config['name']);
			} else
				$label = JText::_($config['name']);

			$paramsTypeKey = '';
			$paramsTypeForm = '';
			$params = null;
			$fullKey = '' . $key;

			$jsEvent = "window.Oby.fireAjax('field_changed',{'key':'".$key."','obj':this});";

			$localType = $paramsType;
			$data = $this->getParamsData($configData, $key, $type, $paramsType, $localType);

			if(!empty($localType)) {
				$paramsTypeKey = $localType.'_';
				$paramsTypeForm = '['.$localType.']';
			}

			$data_key = empty($config['category']) ? '' : 'hikamarket_'.$type.'_cat_'.$config['category'];
			$classname = $data_key.' hikamarket_field_'.$paramsTypeKey.$key;
			$style = empty($config['hidden']) ? '' : ' style="display:none;"';

			if(!empty($config['display']))
				$style .= ' id="hikamarket_field_{TYPE}_'.$paramsTypeKey.$key.'"';

			$html .= '<dt data-hkm-key="'.$data_key.'" class="'.$classname.'"'.str_replace('{TYPE}', 'title', $style).'><label for="data_'.$type.'_'.$paramsTypeKey.$key.'">'.$label.'</label></dt>'."\r\n".
					'<dd data-hkm-key="'.$data_key.'" class="'.$classname.'"'.str_replace('{TYPE}', 'value', $style).'>';

			switch($config['type']) {
				case 'input':
					$html .= '<input type="text" onchange="'.$jsEvent.'" id="data_'.$type.'_'.$paramsTypeKey.$key.'" name="data['.$type.']'.$paramsTypeForm.'['.$key.']" value="'.$this->escape($data).'"/>';
					break;

				case 'textarea':
					$html .= '<textarea onchange="'.$jsEvent.'" id="data_'.$type.'_'.$paramsTypeKey.$key.'" name="data['.$type.']'.$paramsTypeForm.'['.$key.']" rows="3">'.$this->escape($data).'</textarea>';
					break;
				case 'big-textarea':
					$html .= '<textarea onchange="'.$jsEvent.'" id="data_'.$type.'_'.$paramsTypeKey.$key.'" name="data['.$type.']'.$paramsTypeForm.'['.$key.']" rows="9" width="100%" style="width:100%;">'.$this->escape($data).'</textarea>';
					break;

				case 'wysiwyg':
					if(empty($this->editorHelper)) {
						$this->editorHelper = hikamarket::get('shop.helper.editor');
						$marketConfig = hikamarket::config();
						$this->editorHelper->setEditor($marketConfig->get('editor', ''));
						if($marketConfig->get('editor_disable_buttons', 0))
							$this->editorHelper->options = false;
					}
					$this->editorHelper->name = $paramsTypeKey.$key;
					$this->editorHelper->content = $data;
					$html .= $this->editorHelper->display() . '<div style="clear:both"></div>';
					break;

				case 'boolean':
					if(empty($this->radioType))
						$this->radioType = hikamarket::get('shop.type.radio');
					if($data === null) {
						$default = null;
						if(isset($config['data'])) // retro-compat
							$default = $config['data'];
						if(isset($config['default']))
							$default = $config['default'];
						if($default === null)
							$default = 1;

						if($params === null) {
							if(!isset($configData->$key))
								$configData->$key = $default;
							$data = $configData->$key;
						} else {
							if(is_array($params)) {
								if(!isset($params[$key]))
									$params[$key] = $default;
								$data = @$params[$key];
							} else if(!empty($params)) {
								if(!isset($params->$key))
									$params->$key = $default;
								$data = @$params->$key;
							}
						}
					}
					$html .= $this->radioType->booleanlist('data['.$type.']'.$paramsTypeForm.'['.$key.']' , 'onchange="'.$jsEvent.'"', $data);
					break;

				case 'checkbox':
					$i = 0;
					foreach($config['data'] as $listKey => $listData) {
						$checked = '';
						if(!empty($data)) {
							if(in_array($listKey, $data))
								$checked = 'checked="checked"';
						}
						$html .= '<input onchange="'.$jsEvent.'" id="data_'.$type.'_'.$paramsTypeKey.$key.'_'.$i.'" name="data['.$type.']'.$paramsTypeForm.'['.$key.'][]" type="checkbox" value="'.$listKey.'" '.$checked.' /><label for="data_'.$type.'_'.$paramsType.'_'.$key.'_'.$i.'">'.$listData.'</label><br/>';
						$i++;
					}
					break;

				case 'radio':
					$values = array();
					foreach($config['data'] as $listKey => $listData) {
						$values[] = JHTML::_('select.option', $listKey, JText::_($listData));
					}
					$html .= $this->radioType->radiolist($values, 'data['.$type.']'.$paramsTypeForm.'['.$key.']' , 'onchange="'.$jsEvent.'" class="inputbox" size="1"', 'value', 'text', $data);
					break;

				case 'list':
					$values = array();
					foreach($config['data'] as $listKey => $listData) {
						$values[] = JHTML::_('select.option', $listKey,JText::_($listData));
					}
					$html .= JHTML::_('select.genericlist', $values, 'data['.$type.']'.$paramsTypeForm.'['.$key.']' , 'onchange="'.$jsEvent.'" class="inputbox" size="1"', 'value', 'text', $data);
					break;

				case 'price':
					if(empty($this->currenciesType))
						$this->currenciesType = hikamarket::get('shop.type.currency');

					if(!empty($config['data']))
						$key2 = $config['data'];
					else
						$key2 = str_replace('price', 'currency', $key);

					$link_params = false;
					if(!empty($config['link']))
						$link_params = (substr($config['link'], 0, 7) == 'params.');

					$data2 = '';
					if(is_array($configData) && isset($configData[$key2]) && !$link_params)
						$data2 = $configData[$key2];
					else if(is_object($configData) && isset($configData->$key2) && !$link_params)
						$data2 = $configData->$key2;
					else if(is_object($configData) && is_object($configData->{$type.'_params'}) && isset($configData->{$type.'_params'}->$key2))
						$data2 = @$configData->{$type.'_params'}->$key2;

					$html .= '<input type="text" onchange="'.$jsEvent.'" id="data_'.$type.'_'.$paramsTypeKey.$key.'" name="data['.$type.']'.$paramsTypeForm.'['.$key.']" value="'.$this->escape($data).'"/>';

					if($link_params && empty($paramsTypeForm))
						$paramsTypeForm = '['.$type . '_params]';
					$html .= $this->currenciesType->display('data['.$type.']'.$paramsTypeForm.'['.$key2.']', $data2);
					break;

				case 'tax':
					if(empty($this->categoryType))
						$this->categoryType = hikamarket::get('type.shop_category');
					$html .= $this->categoryType->display('data['.$type.']'.$paramsTypeForm.'['.$key.']', $data, 'tax');
					break;

				case 'weight':
					if(empty($this->weightType))
						$this->weightType = hikamarket::get('shop.type.weight');
					if(!empty($config['link'])) {
						$html .= '<input type="text" id="data_'.$type.'_'.$paramsTypeKey.$key.'" name="data['.$type.']'.$paramsTypeForm.'['.$key.']" value="'.$this->escape($data).'"/>';

						$key = $config['link'];
						if(is_array($configData) && isset($configData[$key]))
							$data = $configData[$key];
						else if(is_object($configData) && isset($configData->$key))
							$data = $configData->$key;
						else if(is_object($configData) && is_object($configData->{$type.'_params'}) && isset($configData->{$type.'_params'}->$key))
							$data = $configData->{$type.'_params'}->$key;
					}
					if(empty($config['link']) || empty($cache[$type.'_'.$paramsTypeForm.'_'.$key]))
						$html .= $this->weightType->display('data['.$type.']'.$paramsTypeForm.'['.$key.']', $data);

					if(!empty($config['link']))
						$cache[$type.'_'.$paramsTypeForm.'_'.$key] = true;
					break;

				case 'volume':
					if(empty($this->volumeType))
						$this->volumeType = hikamarket::get('shop.type.volume');

					if(!empty($config['link'])) {
						$html .= '<input type="text" id="data_'.$type.'_'.$paramsTypeKey.$key.'" name="data['.$type.']'.$paramsTypeForm.'['.$key.']" value="'.$this->escape($data).'"/>';

						$key = $config['link'];
						if(is_array($configData) && isset($configData[$key]))
							$data = $configData[$key];
						else if(is_object($configData) && isset($configData->$key))
							$data = $configData->$key;
						else if(is_object($configData) && is_object($configData->{$type.'_params'}) && isset($configData->{$type.'_params'}->$key))
							$data = $configData->{$type.'_params'}->$key;
					}
					if(empty($config['link']) || empty($cache[$type.'_'.$paramsTypeForm.'_'.$key]))
						$html .= $this->volumeType->display('data['.$type.']'.$paramsTypeForm.'['.$key.']', $data);

					if(!empty($config['link']))
						$cache[$type.'_'.$paramsTypeForm.'_'.$key] = true;
					break;

				case 'orderstatus':
				case 'orderstatuses':
					if(empty($this->nameboxType))
						$this->nameboxType = hikamarket::get('type.namebox');

					$html .= $this->nameboxType->display(
						'data['.$type.']'.$paramsTypeForm.'['.$key.']',
						$data,
						($config['type'] == 'orderstatus') ? hikamarketNameboxType::NAMEBOX_SINGLE : hikamarketNameboxType::NAMEBOX_MULTIPLE,
						'order_status',
						array(
							'delete' => ($config['type'] == 'orderstatus') ? false : true,
							'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
							'type' => $type
						)
					);
					break;

				case 'address':
					if(empty($this->addressType))
						$this->addressType = hikamarket::get('shop.type.address');
					$html .= $this->addressType->display('data['.$type.']'.$paramsTypeForm.'['.$key.']', $data);
					break;

				case 'acl':
					if(empty($this->joomlaAclType))
						$this->joomlaAclType = hikamarket::get('type.joomla_acl');
					if($data === null)
						$data = 'all';
					$html .= $this->joomlaAclType->display('data['.$type.']'.$paramsTypeForm.'['.$key.']', $data, true, true);
					break;

				case 'currency':
					if(empty($this->currenciesType))
						$this->currenciesType = hikamarket::get('shop.type.currency');
					$html .= $this->currenciesType->display('data['.$type.']'.$paramsTypeForm.'['.$key.']', $data);
					break;

				case 'currencies':
					if(empty($this->currenciesType))
						$this->currenciesType = hikamarket::get('shop.type.currency');
					if(is_string($data))
						$data = explode(',', trim($data, ','));
					$html .= $this->currenciesType->display('data['.$type.']'.$paramsTypeForm.'['.$key.'][]', $data, 'multiple="multiple" class="no-chzn" size="3"');
					break;

				case 'zone':
					if(empty($this->nameboxType))
						$this->nameboxType = hikamarket::get('type.namebox');

					$html .= $this->nameboxType->display(
						'data['.$type.']'.$paramsTypeForm.'['.$key.']',
						$data,
						hikamarketNameboxType::NAMEBOX_SINGLE,
						'zone',
						array(
							'delete' => true,
							'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
							'type' => $type
						)
					);
					break;

				case 'warehouse':
					if(empty($this->nameboxType))
						$this->nameboxType = hikamarket::get('type.namebox');

					$html .= $this->nameboxType->display(
						'data['.$type.']'.$paramsTypeForm.'['.$key.']',
						$data,
						hikamarketNameboxType::NAMEBOX_SINGLE,
						'warehouse',
						array(
							'delete' => true,
							'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
							'type' => $type
						)
					);
					break;

				case 'plugin_images':
					if(empty($this->nameboxType))
						$this->nameboxType = hikamarket::get('type.namebox');

					$html .= $this->nameboxType->display(
						'data['.$type.']'.$paramsTypeForm.'['.$key.']',
						$data,
						hikamarketNameboxType::NAMEBOX_MULTIPLE,
						'plugin_images',
						array(
							'delete' => true,
							'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
							'type' => $type
						)
					);
					break;

				case 'shipping_method':
					if(empty($this->nameboxType))
						$this->nameboxType = hikamarket::get('type.namebox');

					if(is_string($data))
						$data = explode("\n", $data);

					$html .= $this->nameboxType->display(
						'data['.$type.']'.$paramsTypeForm.'['.$key.']',
						$data,
						hikamarketNameboxType::NAMEBOX_MULTIPLE,
						'shipping_methods',
						array(
							'delete' => true,
							'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
							'type' => $type
						)
					);
					break;

				case 'html':
					$html .= $config['data'];
					break;

				default:
					$ret = false;
					if(!empty($this->plugin) && method_exists($this->plugin, 'pluginConfigDisplay'))
						$ret = $this->plugin->pluginConfigDisplay($config['type'], $config['data'], $type, $paramsTypeKey, $key, $configData);

					if($ret === false || empty($ret) && !empty($this->plugin) && method_exists($this->plugin, 'displayConfigField'))
						$ret = $this->plugin->displayConfigField($config, $type, $paramsTypeKey, $key, $data, $configData);
					if($ret === false || empty($ret)) {
						if(empty($this->dispatcher)) {
							JPluginHelper::importPlugin('hikamarket');
							JPluginHelper::importPlugin('hikashop');
							if(defined('HIKASHOP_J40') && HIKASHOP_J40)
								$this->dispatcher = JFactory::getContainer()->get('dispatcher');
							else
								$this->dispatcher = JDispatcher::getInstance();
						}
						$ret = '';
						$this->dispatcher->trigger('onHikaDisplayConfigField', array(&$ret, $config, $type, $paramsTypeKey, $key, $data, $configData));
					}
					$html .= $ret;
					break;
			}

			if(!empty($config['append']))
				$html .= $config['append'];

			$html .= '</dd>'."\r\n";

			if($checkDisplay && !empty($config['display'])) {
				if(!isset($this->hiddenElements))
					$this->hiddenElements = array();

				foreach($config['display'] as $k => $values) {
					$displayKey = ''.$k;
					$localType = $paramsType;
					$otherData = $this->getParamsData($configData, $displayKey, $type, $paramsType, $localType);

					$html_keys = 'hikamarket_field_{TYPE}_'.$paramsTypeKey.$key.''; // title, value

					if(!is_array($values))
						$values = array($values);
					$hidden = true;
					foreach($values as &$v) {
						if($otherData === $v || ((''.$otherData) === (''.$v))) {
							$hidden = false;
							break;
						}
						$v = ''.$v;
					}
					unset($v);

					if($hidden) {
						$this->hiddenElements[] = 'hikamarket_field_title_'.$paramsTypeKey.$key;
						$this->hiddenElements[] = 'hikamarket_field_value_'.$paramsTypeKey.$key;
					}
					if(!isset($this->displayTriggers))
						$this->displayTriggers = array($k => array());
					if(!isset($this->displayTriggers[$k]))
						$this->displayTriggers[$k] = array();
					$this->displayTriggers[$k][$fullKey] = array($html_keys, $values);
				}
			}
		}
		$html .= '</dl>';
		return $html;
	}
}
