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
class vendormarketViewvendormarket extends HikamarketView {

	protected $ctrl = 'vendor';
	protected $icon = 'vendor';
	protected $triggerView = true;

	public $extraFields = array();
	public $requiredFields = array();
	public $validMessages = array();

	public function display($tpl = null, $params = array()) {
		$this->params =& $params;
		$fct = $this->getLayout();
		if(method_exists($this, $fct))
			$this->$fct();
		parent::display($tpl);
	}

	public function show() {
		$app = JFactory::getApplication();
		$doc = JFactory::getDocument();
		$pathway = $app->getPathway();

		$config = hikamarket::config();
		$this->assignRef('config', $config);
		$shopConfig = hikamarket::config(false);
		$this->assignRef('shopConfig', $shopConfig);

		global $Itemid;
		$url_itemid = '';
		if(!empty($Itemid))
			$url_itemid = '&Itemid=' . $Itemid;

		$imageHelper = hikamarket::get('shop.helper.image');
		$imageHelper->thumbnail = 1;
		$this->assignRef('imageHelper', $imageHelper);

		$this->loadRef(array(
			'fieldsClass' => 'shop.class.field',
			'popup' => 'shop.helper.popup',
		));

		$cid = hikamarket::getCID('vendor_id');
		if(empty($cid)) {
			$menus = $app->getMenu();
			$menu = $menus->getActive();
			if(empty($menu) && !empty($Itemid)) {
				$menus->setActive($Itemid);
				$menu = $menus->getItem($Itemid);
			}
			if(is_object($menu)) {
				jimport('joomla.html.parameter');
				if(HIKASHOP_J30)
					$menuParams = $menu->getParams();
				else
					$menuParams = @$menu->params;
				$market_params = new HikaParameter($menuParams);
				$cid = $market_params->get('vendor_id');
				if(is_array($cid)) {
					$cid = (int)$cid[0];
				} else {
					$cid = (int)$cid;
				}
				hikaInput::get()->set('vendor_id', $cid);
			}
		}

		$vendorClass = hikamarket::get('class.vendor');
		$vendor = $vendorClass->get($cid);
		if(empty($vendor) || !$vendor->vendor_published) {
			$vendor = null;
			$app->enqueueMessage(JText::_('VENDOR_DOES_NOT_EXIST'));
			$this->assignRef('vendor', $vendor);
			return;
		}
		$this->assignRef('vendor', $vendor);

		$vendor->alias = (empty($vendor->vendor_alias)) ? $vendor->vendor_name : $vendor->vendor_alias;

		$stringSafe = (method_exists($app, 'stringURLSafe'));
		if($stringSafe)
			$vendor->alias = $app->stringURLSafe(strip_tags($vendor->alias));
		else
			$vendor->alias = JFilterOutput::stringURLSafe(strip_tags($vendor->alias));

		$doc->setTitle( strip_tags($vendor->vendor_name) );

		$pathway->addItem($vendor->vendor_name, hikamarket::completeLink('vendor&task=show&cid='.(int)$vendor->vendor_id.'&name='.$vendor->alias.$url_itemid));

		if(!empty($vendor->vendor_meta_keywords))
			$doc->setMetadata('keywords', $vendor->vendor_meta_keywords);
		if(!empty($vendor->vendor_meta_description))
			$doc->setMetadata('description', $vendor->vendor_meta_description);

		$vendor_layout = $config->get('default_vendor_layout', 'showcontainer_default');
		$vendor_layout_params = null;

		if(!empty($vendor->vendor_layout))
			$vendor_layout = $vendor->vendor_layout;
		if((int)$Itemid > 0) {
			$menu_params = $config->get('menu_'.(int)$Itemid, null);
			if(!empty($menu_params) && !empty($menu_params->vendor_page_layout))
				$vendor_layout = $menu_params->vendor_page_layout;
		}
		if(empty($vendor_layout) || (substr($vendor_layout, 0, 14) != 'showcontainer_' && substr($vendor_layout, 0, 7) != 'layout:'))
			$vendor_layout = 'showcontainer_default';

		if(substr($vendor_layout, 0, 7) == 'layout:') {
			if(empty($vendor_layout_params))
				$vendor_layout = 'showcontainer_default';
		}
		$this->assignRef('vendor_layout', $vendor_layout);
		$this->assignRef('vendor_layout_params', $vendor_layout_params);

		$moduleHelper = hikamarket::get('shop.helper.module');
		$modules = $moduleHelper->setModuleData($config->get('vendor_show_modules', ''));
		if(!empty($modules) && is_array($modules))
			jimport('joomla.application.module.helper');
		else
			$modules = null;
		$this->assignRef('modules', $modules);

		$image_size = array(
			'x' => (int)$config->get('vendor_image_x', $shopConfig->get('product_image_x', 100)),
			'y' => (int)$config->get('vendor_image_y', $shopConfig->get('product_image_y', 100)),
		);
		if(empty($image_size['x'])) $image_size['x'] = (int)$shopConfig->get('product_image_x', 100);
		if(empty($image_size['x'])) $image_size['x'] = 100;
		if(empty($image_size['y'])) $image_size['y'] = (int)$shopConfig->get('product_image_y', 100);
		if(empty($image_size['y'])) $image_size['y'] = 100;

		$this->assignRef('image_size', $image_size);

		$image_options = array();
		if($config->get('image_forcesize', '-1') !== '-1')
			$image_options['forcesize'] = (int)$config->get('image_forcesize');
		if($config->get('image_grayscale', '-1') !== '-1')
			$image_options['grayscale'] = (int)$config->get('image_grayscale');
		if($config->get('image_scale', '-1') !== '-1') {
			switch((int)$config->get('image_scale')) {
				case 0:
					$image_options['scale'] = 'outside';
					break;
				case 1:
					$image_options['scale'] = 'inside';
					break;
			}
		}
		if($config->get('image_radius', '-1') !== '-1')
			$image_options['radius'] = (int)$config->get('image_radius');
		$this->assignRef('image_options', $image_options);

		$vendor_image = null;
		if(!empty($vendor->vendor_image)) {
			if(isset($image_options['default']))
				unset($image_options['default']);
			$vendor_image = $imageHelper->getThumbnail($vendor->vendor_image, $image_size, $image_options);
		}
		if(empty($vendor_image) || !$vendor_image->success) {
			$image_options['default'] = true;
			$vendor_image = $imageHelper->getThumbnail($config->get('default_vendor_image', ''), $image_size, $image_options, true);
		}
		$this->assignRef('vendor_image', $vendor_image);

		$voteParams = null;
		if($this->config->get('display_vendor_vote', 0)) {
			$voteParams = new HikaParameter();
			$voteParams->set('vote_type', 'vendor');
			$voteParams->set('vote_ref_id', $this->vendor->vendor_id);
		}
		$this->assignRef('voteParams', $voteParams);

		$extraFields = array(
			'vendor' => $this->fieldsClass->getFields('display:vendor_page=1', $vendor, 'plg.hikamarket.vendor')
		);

		foreach($extraFields['vendor'] as $fieldName => $extraField) {
			if(empty($extraField->field_display) || strpos($extraField->field_display, ';vendor_page=1;') === false) {
				unset($extraFields['vendor'][$fieldName]);
			}
		}

		$this->assignRef('extraFields', $extraFields);
		$this->assignRef('vendorFields', $vendorFields);
	}

	public function listingAdmin($tpl = null, $mainVendor = false) {
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$this->paramBase = HIKAMARKET_COMPONENT.'.'.$this->getName().'.vendors';

		$shopConfig = hikamarket::config(false);
		$invoice_statuses = explode(',', $shopConfig->get('invoice_order_statuses','confirmed,shipped'));
		if(empty($invoice_statuses))
			$invoice_statuses = array('confirmed','shipped');

		$config = hikamarket::config();
		$this->assignRef('config', $config);

		$toggleClass = hikamarket::get('helper.toggle');
		$this->assignRef('toggleClass', $toggleClass);
		$currencyClass = hikamarket::get('shop.class.currency');
		$this->assignRef('currencyClass', $currencyClass);

		$filterType = $app->getUserStateFromRequest($this->paramBase.'.filter_type', 'filter_type', 0, 'int');

		$cfg = array(
			'table' => 'vendor',
			'main_key' => 'vendor_id',
			'order_sql_value' => 'vendor.vendor_id'
		);

		$manage = true;
		$this->assignRef('manage', $manage);

		$pageInfo = $this->getPageInfo($cfg['order_sql_value']);

		$filters = array();
		$searchMap = array(
			'vendor.vendor_id', 'vendor.vendor_name', 'vendor.vendor_email'
		);
		$orderingAccept = array('vendor.vendor_id', 'vendor.vendor_name', 'vendor.vendor_email', 'vendor.');
		$order = '';

		if(!$mainVendor)
			$filters[] = 'vendor.vendor_id > 1';
		$this->processFilters($filters, $order, $searchMap, $orderingAccept);

		$query = 'FROM '.hikamarket::table($cfg['table']).' AS vendor '.$filters.$order;
		$db->setQuery('SELECT * '.$query, (int)$pageInfo->limit->start, (int)$pageInfo->limit->value);

		$vendors = $db->loadObjectList();
		$this->assignRef('vendors', $vendors);

		$db->setQuery('SELECT COUNT(*) '.$query);
		$pageInfo->elements = new stdClass();
		$pageInfo->elements->total = $db->loadResult();
		$pageInfo->elements->page = count($vendors);

		$this->toolbar = array(
			'back' => array('icon' => 'back', 'fa' => 'fa-arrow-circle-left', 'name' => JText::_('HIKA_BACK'), 'url' => hikamarket::completeLink('vendor')),
		);

		$this->getPagination();

		$this->getOrdering('a.ordering', !$filterType);
		if(!empty($this->ordering->ordering))
			$this->toolbar['ordering']['display'] = true;
	}

	public function selection($tpl = null){
		$singleSelection = hikaInput::get()->getInt('single', 0);
		$confirm = hikaInput::get()->getInt('confirm', 1);
		$this->assignRef('singleSelection', $singleSelection);
		$this->assignRef('confirm', $confirm);

		$vendor = hikamarket::loadVendor(false);
		$mainVendor = ($vendor == 1);
		$this->assignRef('mainVendor', $mainVendor);

		$elemStruct = array(
			'vendor_name'
		);

		if($mainVendor) {
			$elemStruct = array(
				'vendor_name',
				'vendor_email'
			);
		}
		$this->assignRef('elemStruct', $elemStruct);

		$ctrl = hikaInput::get()->getCmd('ctrl');
		$this->assignRef('ctrl', $ctrl);

		$task = 'useselection';
		$this->assignRef('task', $task);

		$afterParams = array();
		$after = hikaInput::get()->getString('after', '');
		if(!empty($after)) {
			list($ctrl, $task) = explode('|', $after, 2);

			$afterParams = hikaInput::get()->getString('afterParams', '');
			$afterParams = explode(',', $afterParams);
			foreach($afterParams as &$p) {
				$p = explode('|', $p, 2);
				unset($p);
			}
		}
		$this->assignRef('afterParams', $afterParams);

		$fieldsClass = hikamarket::get('shop.class.field');
		$this->assignRef('fieldsClass', $fieldsClass);

		$vendorFields = null;
		$extraFields = array(
			'vendor' => $fieldsClass->getFields('display:vendor_select=1', $vendorFields, 'plg.hikamarket.vendor')
		);
		foreach($extraFields['vendor'] as $fieldName => $extraField) {
			if(empty($extraField->field_display) || strpos($extraField->field_display, ';vendor_select=1;') === false) {
				unset($extraFields['vendor'][$fieldName]);
			}
		}

		$this->assignRef('extraFields', $extraFields);
		$this->assignRef('vendorFields', $vendorFields);

		$this->listingAdmin();
		$this->toolbar = array();
	}

	public function useselection() {
		$selection = hikaInput::get()->get('cid', array(), 'array');
		$rows = array();
		$data = '';

		$vendor = hikamarket::loadVendor(false);
		$mainVendor = ($vendor == 1);
		$this->assignRef('mainVendor', $mainVendor);

		$elemStruct = array(
			'vendor_name'
		);

		if($mainVendor) {
			$elemStruct = array(
				'vendor_name',
				'vendor_email'
			);
		}

		if(!empty($selection)) {
			hikamarket::toInteger($selection);
			$db = JFactory::getDBO();
			$query = 'SELECT a.* FROM '.hikamarket::table('vendor').' AS a  WHERE a.vendor_id IN ('.implode(',',$selection).')';
			$db->setQuery($query);
			$rows = $db->loadObjectList();

			if(!empty($rows)) {
				$data = array();
				foreach($rows as $v) {
					$d = array('id' => (int)$v->vendor_id);
					foreach($elemStruct as $s) {
						if($s == 'id')
							continue;
						$d[$s] = $v->$s;
					}
					$data[] = $d;
				}
				$data = json_encode($data);
			}
		}
		$this->assignRef('rows', $rows);
		$this->assignRef('data', $data);

		$confirm = hikaInput::get()->getBool('confirm', true);
		$this->assignRef('confirm', $confirm);
		if($confirm) {
			$js = 'hikashop.ready(function(){window.top.hikamarket.submitBox('.$data.');});';
			$doc = JFactory::getDocument();
			$doc->addScriptDeclaration($js);
		}
	}

	public function cpanel() {
		$app = JFactory::getApplication();
		$pathway = $app->getPathway();
		$config = hikamarket::config();
		$shopConfig = hikamarket::config(false);

		$this->assignRef('config', $config);
		$this->assignRef('shopConfig', $shopConfig);

		global $Itemid;
		$url_itemid = '';
		if(!empty($Itemid))
			$url_itemid='&Itemid='.$Itemid;
		$this->assignRef('url_itemid', $url_itemid);

		$vendor = hikamarket::loadVendor(true);
		$this->assignRef('vendor', $vendor);

		$this->multiple_vendor = array();
		if($config->get('user_multiple_vendor', 0)) {
			$vendorClass = hikamarket::get('class.vendor');
			$vendors = $vendorClass->getUserVendors();
			if(count($vendors) > 1) {
				foreach($vendors as $i => $v) {
					$this->multiple_vendor[$i] = $v->vendor_name;
				}
			}
			unset($vendors);
		}

		$plugin_edition = ($vendor->vendor_id == 0 || $vendor->vendor_id == 1) || ($vendor->vendor_id > 1 && (int)$config->get('plugin_vendor_config', 0) > 0);

		$buttons = array(
			'account' => array(
				'url' => hikamarket::completeLink('vendor&task=form'.$url_itemid),
				'level' => 0,
				'icon' => 'iconM-48-account',
				'fa' => 'fas fa-user-tie',
				'name' => JText::_('VENDOR_ACCOUNT'),
				'description' => '',
				'display' => hikamarket::acl('vendor/edit')
			),
			'user' => array(
				'url' => hikamarket::completeLink('user'.$url_itemid),
				'level' => 0,
				'icon' => 'iconM-48-user',
				'fa' => 'fas fa-user-friends',
				'name' => JText::_('CUSTOMERS'),
				'description' => '',
				'display' => hikamarket::acl('user/listing')
			),
			'order' => array(
				'url' => hikamarket::completeLink('order'.$url_itemid),
				'level' => 0,
				'icon' => 'iconM-48-order',
				'fa' => 'fas fa-file-invoice-dollar',
				'name' => JText::_('ORDERS'),
				'description' => '',
				'display' => hikamarket::acl('order/listing')
			),
			'product' => array(
				'url' => hikamarket::completeLink('product'.$url_itemid),
				'level' => 0,
				'icon' => 'iconM-48-products',
				'fa' => 'fas fa-cubes',
				'name' => JText::_('PRODUCTS'),
				'description' => '',
				'display' => hikamarket::acl('product/listing')
			),
			'category' => array(
				'url' => hikamarket::completeLink('category'.$url_itemid),
				'level' => 0,
				'icon' => 'iconM-48-category',
				'fa' => 'fas fa-folder',
				'name' => JText::_('HIKA_CATEGORIES'),
				'description' => '',
				'display' => hikamarket::acl('category/listing')
			),
			'characteristic' => array(
				'url' => hikamarket::completeLink('characteristic'.$url_itemid),
				'level' => 0,
				'icon' => 'iconM-48-characteristic',
				'fa' => 'fas fa-adjust',
				'name' => JText::_('CHARACTERISTICS'),
				'description' => '',
				'display' => hikamarket::acl('characteristic/listing')
			),
			'discount' => array(
				'url' => hikamarket::completeLink('discount'.$url_itemid),
				'level' => 0,
				'icon' => 'iconM-48-discount',
				'fa' => array('<i class="fas fa-certificate fa-stack-2x"></i>', '<i class="fas fa-percent fa-inverse fa-stack-1x"></i>'),
				'name' => JText::_('DISCOUNTS'),
				'description' => '',
				'display' => hikamarket::acl('discount/listing')
			),
			'shipping' => array(
				'url' => hikamarket::completeLink('plugin&plugin_type=shipping'.$url_itemid),
				'level' => 0,
				'icon' => 'iconM-48-shipping',
				'fa' => 'fas fa-shipping-fast',
				'name' => JText::_('HIKAM_SHIPPINGS'),
				'description' => '',
				'display' => ($plugin_edition && hikamarket::acl('shippingplugin/listing'))
			),
			'payment' => array(
				'url' => hikamarket::completeLink('plugin&plugin_type=payment'.$url_itemid),
				'level' => 0,
				'icon' => 'iconM-48-payment',
				'fa' => 'far fa-credit-card',
				'name' => JText::_('HIKAM_PAYMENTS'),
				'description' => '',
				'display' => ($plugin_edition && hikamarket::acl('paymentplugin/listing'))
			),
		);

		$statistics = array();
		$statisticsClass = null;

		if(hikamarket::acl('vendor/statistics')) {
			$statisticsClass = hikamarket::get('class.statistics');
			$statistics = $statisticsClass->getVendor($vendor);
		}

		JPluginHelper::importPlugin('hikamarket');
		JPluginHelper::importPlugin('hikashop');
		JPluginHelper::importPlugin('hikashoppayment');
		JFactory::getApplication()->triggerEvent('onVendorPanelDisplay', array(&$buttons, &$statistics));

		if(hikamarket::acl('vendor/statistics')) {
			$vendor_statistics = $config->get('vendor_statistics', null);
			if(!empty($vendor_statistics)) {
				foreach($statistics as $key => &$stat) {
					$stat['published'] = false;
				}
				unset($stat);

				$vendor_statistics = hikamarket::unserialize(base64_decode($vendor_statistics));
				foreach($vendor_statistics as $key => $stat_conf) {
					if(!isset($statistics[$key]))
						continue;

					if(isset($stat_conf['container']))
						$statistics[$key]['container'] = (int)$stat_conf['container'];
					if(isset($stat_conf['slot']))
						$statistics[$key]['slot'] = (int)$stat_conf['slot'];
					if(isset($stat_conf['order']))
						$statistics[$key]['order'] = (int)$stat_conf['order'];
					else
						$statistics[$key]['order'] = 0;
					if(isset($stat_conf['published']))
						$statistics[$key]['published'] = $stat_conf['published'];
					if(!empty($stat_conf['vars'])) {
						foreach($stat_conf['vars'] as $k => $v)
							$statistics[$key]['vars'][$k] = $v;
					}
				}
			}

			uasort($statistics, array($this, 'sortStats'));
		}

		$statistic_slots = array();
		if(!empty($statistics)) {
			foreach($statistics as $key => &$stat) {
				if(isset($stat['published']) && empty($stat['published']))
					continue;

				$stat['key'] = $key;
				if(empty($stat['slot']))
					$stat['slot'] = 0;
				if(!isset($statistic_slots[ (int)$stat['slot'] ]))
					$statistic_slots[ (int)$stat['slot'] ] = array();

				$order = @$stat['order'] * 100;
				if(isset($statistic_slots[ $stat['slot'] ][ $order ])) {
					for($i = 1; $i < 100; $i++) {
						if(!isset($statistic_slots[ (int)$stat['slot'] ][ $order + $i ])) {
							$order += $i;
							break;
						}
					}
				}

				$statistic_slots[ (int)$stat['slot'] ][$order] =& $stat;
			}
			unset($stat);

			foreach($statistic_slots as $slot => &$stats) {
				ksort($stats);
			}
			unset($stats);
		}

		foreach($buttons as &$btn) {
			if(!hikamarket::level($btn['level']) || !$btn['display']) {
				$btn = null;
				unset($btn);
				continue;
			}
			if(!isset($btn['name']))
				$btn['name'] = '';
			if(!isset($btn['description']))
				$btn['description'] = '';
			if(empty($btn['icon']))
				$btn['icon'] = 'icon-48-hikamerket';
			unset($btn);
		}

		$this->assignRef('buttons', $buttons);
		$this->assignRef('statistics', $statistics);
		$this->assignRef('statisticsClass', $statisticsClass);
		$this->assignRef('statistic_slots', $statistic_slots);

		$items = $pathway->getPathway();
		if(!count($items))
			$pathway->addItem(JText::_('VENDOR_ACCOUNT'), hikamarket::completeLink('vendor'));
	}

	protected function sortStats($a, $b) {
		if($a['order'] == $b['order'])
			return 0;
		return ($a['order'] < $b['order']) ? -1 : 1;
	}

	public function form() {
		$this->vendorEdition();

		$vendor = hikamarket::loadVendor(true);

		if(!empty($vendor)) {
			$this->toolbar = array(
				'back' => array('icon' => 'back', 'fa' => 'fa-arrow-circle-left', 'name' => JText::_('HIKA_BACK'), 'url' => hikamarket::completeLink('vendor'.$this->url_itemid)),
				'save' => array(
					'url' => '#save',
					'linkattribs' => 'onclick="return window.hikamarket.submitform(\'save\',\'hikamarket_vendor_form\');"',
					'icon' => 'save',
					'fa' => 'fa-save',
					'name' => JText::_('HIKA_SAVE'), 'pos' => 'right'
				)
			);

			$app = JFactory::getApplication();
			$menu = $app->getMenu();
			$item = $menu->getActive();
			$menu_view = (!empty($item->query['view']) ? $item->query['view'] : (!empty($item->query['ctrl']) ? $item->query['ctrl'] : null));
			$menu_layout = (!empty($item->query['layout']) ? $item->query['layout'] : (!empty($item->query['task']) ? $item->query['task'] : null));
			if(in_array($menu_view, array('vendor','vendormarket')) && $menu_layout == 'form')
				unset($this->toolbar['back']);
		}

		$market_acl = hikamarket::get('type.market_acl');
		$this->assignRef('marketaclType', $market_acl);

		$users = array();
		if(hikamarket::acl('vendor/edit/users')) {
			$db = JFactory::getDBO();
			$query = 'SELECT a.*,b.* FROM '.hikamarket::table('user','shop').' AS a LEFT JOIN '.hikamarket::table('users',false).' AS b ON a.user_cms_id = b.id '.
					'WHERE a.user_vendor_id = ' . (int)$this->vendor->vendor_id . ' ORDER BY a.user_id';
			$db->setQuery($query);
			$users = $db->loadObjectList();

			$query = 'SELECT hku.*, vu.user_access as `user_vendor_access`, ju.* '.
					' FROM '.hikamarket::table('user','shop').' AS hku '.
					' INNER JOIN '.hikamarket::table('vendor_user').' AS vu ON hku.user_id = vu.user_id ' .
					' LEFT JOIN '.hikamarket::table('users',false).' AS ju ON hku.user_cms_id = ju.id '.
					' WHERE vu.vendor_id = ' . (int)$this->vendor->vendor_id . ' ORDER BY hku.user_id';
			$db->setQuery($query);
			$o_users = $db->loadObjectList('user_id');

			$users = array_merge($users, $o_users);
			unset($o_users);
		}
		$this->assignRef('users', $users);

		$app = JFactory::getApplication();
		$pathway = $app->getPathway();
		$items = $pathway->getPathway();
		if(!count($items)) {
			$pathway->addItem(JText::_('VENDOR_ACCOUNT'), hikamarket::completeLink('vendor'.$this->url_itemid));
		}
		$itemName = JText::_('HIKAM_VENDOR_EDIT');
		if(empty($this->vendor))
			$itemName = JText::_('HIKA_VENDOR_REGISTRATION');
		$pathway->addItem($itemName, hikamarket::completeLink('vendor&task=form'.$this->url_itemid));
	}

	public function registration() {
		$this->vendorEdition('register');
	}

	public function edit() {
		$this->vendorEdition('vendor');
	}

	public function vendorEdition($type = 'auto') {
		$jversion = preg_replace('#[^0-9\.]#i','', JVERSION);
		if(version_compare($jversion, '3.4.0', '>='))
			JHTML::_('behavior.formvalidator');
		else
			JHTML::_('behavior.formvalidation');

		$config = hikamarket::config();
		$this->assignRef('config', $config);

		$shopConfig = hikamarket::config(false);
		$this->assignRef('shopConfig', $shopConfig);

		$mainUser = JFactory::getUser();
		$this->assignRef('mainUser', $mainUser);

		$user = hikamarket::loadUser(true);
		$this->assignRef('user', $user);

		if(empty($user)) {
			$user = @$_SESSION['hikashop_user_data'];
			$address = @$_SESSION['hikashop_address_data'];

			if(empty($user))
				$user = new stdClass();

			$register = @$_SESSION['hikashop_register_data'];
			if(!empty($register)) {
				if(is_object($register))
					$register = get_object_vars($register);
				foreach($register as $k => $v) {
					if(!isset($user->$k))
						$user->$k = $v;
				}
			}
		}

		$simplified_reg = $config->get('simplified_registration', 1);
		$this->assignRef('simplified_registration', $simplified_reg);

		$failVendor = hikaInput::get()->getVar('fail[vendor]', null);
		$vendor = hikamarket::loadVendor(true);
		if(empty($vendor) && !empty($failVendor))
			$vendor = $failVendor;

		$this->assignRef('vendor', $vendor);

		if($type == 'auto')
			$type = ($vendor != null && !empty($vendor->vendor_id)) ? 'vendor' : 'vendorregister';
		$this->assignRef('form_type', $type);

		$this->loadRef(array(
			'uploaderType' => 'shop.type.uploader',
			'cartHelper' => 'shop.helper.cart',
			'currencyType' => 'shop.type.currency',
			'fieldsClass' => 'shop.class.field',
			'radioType' => 'shop.type.radio',
		));

		$editor = hikamarket::get('shop.helper.editor');
		$editor->setEditor($config->get('editor', ''));
		$editor->name = 'vendor_description';
		$editor->content = '';
		$editor->height = 250;
		if($config->get('editor_disable_buttons', 0))
			$editor->options = false;
		$this->assignRef('editor', $editor);

		global $Itemid;
		$url_itemid = '';
		if(!empty($Itemid)){ $url_itemid = '&Itemid='.$Itemid; }
		$this->assignRef('url_itemid',$url_itemid);

		$extraFields = array();
		$vendorFields = null;
		$fieldMode = 'frontcomp';
		if($type == 'vendorregister')
			$fieldMode = 'display:vendor_registration=1';

		if($type == 'vendorregister' || hikamarket::acl('vendor/edit/fields')) {
			$extraFields = array(
				'vendor' => $this->fieldsClass->getFields($fieldMode, $vendorFields, 'plg.hikamarket.vendor'),
				'user' => $this->fieldsClass->getFields($fieldMode, $user, 'user')
			);
		}
		$this->assignRef('extraFields', $extraFields);
		$this->assignRef('vendorFields', $vendorFields);

		$null = array();
		$this->fieldsClass->addJS($null, $null, $null);
		$this->fieldsClass->jsToggle($this->extraFields['vendor'], $vendorFields, 0);
		$this->fieldsClass->jsToggle($this->extraFields['user'], $user, 0);

		$values = array(
			'vendor' => $vendorFields,
			'user' => $user
		);

		if($shopConfig->get('address_on_registration', 1)) {
			$extraFields['address'] = $this->fieldsClass->getFields('frontcomp', $address, 'address');
			$this->fieldsClass->jsToggle($this->extraFields['address'], $address, 0);

			$values['address'] = $address;
			$this->assignRef('address', $address);
		}

		$this->fieldsClass->checkFieldsForJS($this->extraFields, $this->requiredFields, $this->validMessages, $values);

		$options = array(
			'ask_description' => 1,
			'ask_currency' => 1,
			'ask_terms' => 1,
			'ask_paypal' => 1,
		);
		$element = new stdClass();
		if($type == 'vendor') {
			$element = $vendor;

			$options['ask_description'] = hikamarket::acl('vendor/edit/description');
			$options['ask_currency'] = hikamarket::acl('vendor/edit/currency');
			$options['ask_terms'] = hikamarket::acl('vendor/edit/terms');
			$options['ask_paypal'] = hikamarket::acl('vendor/edit/paypalemail');
		} else {
			$element->vendor_params = new stdClass();

			$element->vendor_name = '';
			$element->vendor_description = '';
			$element->vendor_email = @$user->user_email;
			$element->vendor_currency_id = hikamarket::getCurrency();
			$element->vendor_params->paypal_email = @$user->user_email;

			if(!empty($failVendor)) {
				foreach($failVendor as $k => $v) {
					if(is_string($v))
						$element->$k = $v;
				}
				$element->vendor_params->paypal_email = @$failVendor->vendor_params->paypal_email;
				if(empty($element->vendor_params->paypal_email))
					$element->vendor_params->paypal_email = $element->vendor_email;
			}

			$options['ask_description'] = $config->get('register_ask_description', 1);
			$options['ask_currency'] = $config->get('register_ask_currency', 1);
			$options['ask_terms'] = $config->get('register_ask_terms', 1);
			$options['ask_paypal'] = $config->get('register_ask_paypal', 1);

			$shopUserClass = hikamarket::get('shop.class.user');
			$privacy = $shopUserClass->getPrivacyConsentSettings();
			if(!empty($privacy)) {
				$options['privacy'] = true;
				$options['privacy_id'] = $privacy['id'];
				$options['privacy_text'] = $privacy['text'];
			}
		}
		$this->assignRef('element', $element);
		$this->assignRef('options', $options);

	}

	public function terms() {
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();

		$config = hikamarket::config();
		$shopConfig = hikamarket::config(false);

		$step = hikaInput::get()->getInt('step', -1);
		$pos = hikaInput::get()->getInt('pos', -1);

		$cid = hikamarket::getCID();
		if(empty($cid) && $step < 0)
			$cid = 1;

		if(!empty($cid)) {
			$query = 'SELECT * FROM '.hikamarket::table('vendor').' WHERE vendor_id = ' . $cid;
			$db->setQuery($query);
			$vendor = $db->loadObject();
			$this->assignRef('vendor', $vendor);

			if($cid > 1 || !empty($vendor->vendor_terms))
				return;

		}
		$this->vendor = null;

		hikashop_get('helper.checkout');
		$checkoutHelper = hikashopCheckoutHelper::get();
		$this->workflow = $checkoutHelper->checkout_workflow;
		$block = @$this->workflow['steps'][$step-1]['content'][$pos];
		if(!empty($block) && $block['task'] == 'plg.market.terms' && !empty($block['params']['article_id']))
			$terms_article = $block['params']['article_id'];

		if(empty($terms_article))
			$terms_article = $config->get('checkout_terms', 0);

		if(empty($terms_article))
			return;

		$sql = 'SELECT * FROM #__content WHERE id = ' . (int)$terms_article;
		$db->setQuery($sql);
		$data = $db->loadObject();

		$article = '';
		if (is_object($data))
			$article = $data->introtext . $data->fulltext;
		$this->assignRef('article', $article);
	}

	public function listing() {
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$doc = JFactory::getDocument();
		$config = hikamarket::config();
		$shopConfig = hikamarket::config(false);

		$this->assignRef('config', $config);
		$this->assignRef('shopConfig', $shopConfig);

		$this->module = false;
		$moduleHelper = hikamarket::get('helper.module');
		$moduleHelper->initialize($this);
		$this->paramBase = HIKAMARKET_COMPONENT.'.'.$this->getName().'.vendors' . '.' . $this->params->get('main_div_name');

		$stringSafe = (method_exists($app, 'stringURLSafe'));

		$filters = array();
		$sql_select = array();
		$sql_joins = array();

		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$this->assignRef('pageInfo', $pageInfo);

		$defaultParams = $shopConfig->get('default_params');
		$marketDefaultParams = $config->get('default_params');

		$marketDefaultParams['show_vote'] = $config->get('display_vendor_vote', 0);

		$inheritShop = array(
			'limit' => '',
			'order_dir' => 'inherit',
			'margin' => '',
			'border_visible' => '-1',
			'div_item_layout_type' => 'inherit',
			'text_center' => '-1',
			'columns' => '',
			'background_color' => '',
			'layout_type' => 'inherit',
			'random' => '-1',

			'link_to_vendor_page' => '-1',
		);
		foreach($inheritShop as $k => $v) {
			if($this->params->get($k, $v) == $v)
				$this->params->set($k, @$defaultParams[$k]);
		}

		$inheritMarket = array(
			'image_forcesize' => '-1',
			'image_scale' => '-1',
			'image_grayscale' => '-1',
			'image_radius' => '',
			'show_vote' => '-1',
		);
		foreach($inheritMarket as $k => $v) {
			if($this->params->get($k, $v) == $v)
				$this->params->set($k, @$marketDefaultParams[$k]);
		}

		if((int)$this->params->get('limit', 0) == 0 )
			$this->params->set('limit', 1);

		if($this->params->get('vendor_order', 'inherit') == 'inherit' || $this->params->get('vendor_order', 'inherit') == '')
			$this->params->set('vendor_order', 'vendor_id');
		if($this->params->get('order_dir', 'inherit') == 'inherit' || $this->params->get('order_dir','inherit') == '')
			$this->params->set('order_dir', 'ASC');

		$pageInfo->filter->order->value = $app->getUserStateFromRequest($this->paramBase . '.filter_order', 'filter_order_' . $this->params->get('main_div_name'), 'vendor.' . $this->params->get('vendor_order', 'vendor_id'), 'cmd');
		$pageInfo->filter->order->dir = $app->getUserStateFromRequest($this->paramBase . '.filter_order_Dir', 'filter_order_Dir_' . $this->params->get('main_div_name'), $this->params->get('vendor_order_dir','ASC'), 'word');

		$oldValue = $app->getUserState($this->paramBase . '.list_limit');
		if($this->params->get('limit','') == '') {
			$this->params->set('limit', @$defaultParams['limit']);
			if($this->params->get('limit', 0) <= 0)
				$this->params->set('limit', 20);
		}

		if(empty($oldValue))
			$oldValue = $this->params->get('limit');

		$pageInfo->limit->value = $app->getUserStateFromRequest($this->paramBase . '.list_limit', 'limit_' . $this->params->get('main_div_name'), $this->params->get('limit'), 'int');
		if($oldValue != $pageInfo->limit->value)
			hikaInput::get()->set('limitstart_' . $this->params->get('main_div_name'), 0);

		$pageInfo->limit->start = $app->getUserStateFromRequest($this->paramBase . '.limitstart', 'limitstart_' . $this->params->get('main_div_name'), 0, 'int');

		$pageInfo->search = HikaStringHelper::strtolower($app->getUserStateFromRequest($this->paramBase.'.search', 'search', '', 'string'));
		$pageInfo->search = trim($pageInfo->search);

		if(empty($this->module) && $shopConfig->get('hikarss_format') != 'none') {
			$doc_title = $shopConfig->get('hikarss_name', '');
			if($config->get('hikarss_format') != 'both') {
				$link = '&format=feed&limitstart=';
				$attribs = array('type' => 'application/rss+xml', 'title' => $doc_title.' RSS 2.0');
				$doc->addHeadLink(JRoute::_($link.'&type='.$shopConfig->get('hikarss_format')), 'alternate', 'rel', $attribs);
			} else {
				$link = '&format=feed&limitstart=';
				$attribs = array('type' => 'application/rss+xml', 'title' => $doc_title.' RSS 2.0');
				$doc->addHeadLink(JRoute::_($link.'&type=rss'), 'alternate', 'rel', $attribs);
				$attribs = array('type' => 'application/atom+xml', 'title' => $doc_title.' Atom 1.0');
				$doc->addHeadLink(JRoute::_($link.'&type=atom'), 'alternate', 'rel', $attribs);
			}
		}
		$mainVendor = $config->get('listing_show_main_vendor', 0);

		$filters = array(
			'published' => 'vendor_published = 1'
		);
		$searchMap = array(
			'vendor.vendor_name',
			'vendor.vendor_description',
			'vendor.vendor_id'
		);
		$orderingAccept = array(
			'vendor.vendor_id',
			'vendor.vendor_name',
			'vendor.vendor_email',
			'vendor.'
		);
		$order = '';

		if(!$mainVendor)
			$filters['no_main_vendor'] = 'vendor.vendor_id > 1';

		JPluginHelper::importPlugin('hikamarket');
		$trigger_params = array(
			'select' => &$sql_select,
			'join' => &$sql_joins,
			'filter' => &$filters,
			'order' => &$order,
			'search_map' => &$searchMap,
			'order_accept' => &$orderingAccept,
		);
		$view =& $this;
		JFactory::getApplication()->triggerEvent('onBeforeVendorListingDisplay', array(&$view, &$trigger_params));
		unset($view);

		$this->processFilters($filters, $order, $searchMap, $orderingAccept);
		if($this->params->get('random'))
			$order = ' ORDER BY RAND()';

		if(!empty($sql_select)) {
			$sql_select = ',' . implode(',', $sql_select) . ' ';
		} else {
			$sql_select = '';
		}

		if(!empty($sql_joins)) {
			$sql_joins = implode(' ', $sql_joins) . ' ';
		} else {
			$sql_joins = '';
		}

		$query = 'FROM '.hikamarket::table('vendor').' AS vendor ' . $sql_joins . $filters . $order;
		$db->setQuery('SELECT vendor.* ' . $sql_select . $query, (int)$pageInfo->limit->start, (int)$pageInfo->limit->value);

		$rows = $db->loadObjectList();
		foreach($rows as &$row) {
			$row->alias = (empty($row->vendor_alias)) ? $row->vendor_name : $row->vendor_alias;

			if($stringSafe)
				$row->alias = $app->stringURLSafe(strip_tags($row->alias));
			else
				$row->alias = JFilterOutput::stringURLSafe(strip_tags($row->alias));

			unset($row);
		}
		$this->assignRef('rows', $rows);

		$db->setQuery('SELECT COUNT(vendor.vendor_id) FROM '.hikamarket::table('vendor').' AS vendor ' . $sql_joins . $filters);
		$pageInfo->elements = new stdClass();
		$pageInfo->elements->total = $db->loadResult();
		$pageInfo->elements->page = count($rows);

		if($pageInfo->elements->page) {

		}

		$this->assignRef('modules', $this->modules);

		$imageHelper = hikamarket::get('shop.helper.image');
		$imageHelper->thumbnail = 1;
		$this->assignRef('imageHelper', $imageHelper);

		if($this->params->get('image_height') == null && $this->params->get('image_width') == null) {
		 	$this->params->set('image_width', $this->imageHelper->main_thumbnail_x);
			$this->params->set('image_height', $this->imageHelper->main_thumbnail_y);
		}

		$image_size = array('x' => (int)$this->params->get('image_width'), 'y' => (int)$this->params->get('image_height'));
		$this->assignRef('image_size', $image_size);

		$image_options = array();
		if($this->params->get('image_forcesize', '-1') !== '-1')
			$image_options['forcesize'] = (int)$this->params->get('image_forcesize');
		if($this->params->get('image_grayscale', '-1') !== '-1')
			$image_options['grayscale'] = (int)$this->params->get('image_grayscale');
		if($this->params->get('image_scale', '-1') !== '-1') {
			switch((int)$this->params->get('image_scale')) {
				case 0:
					$image_options['scale'] = 'outside';
					break;
				case 1:
					$image_options['scale'] = 'inside';
					break;
			}
		}
		if($this->params->get('image_radius', '-1') !== '-1')
			$image_options['radius'] = (int)$this->params->get('image_radius');

		$this->assignRef('image_options', $image_options);

		$opt = $image_options;
		$opt['default'] = true;
		$default_vendor_image = $this->imageHelper->getThumbnail($config->get('default_vendor_image', ''), $this->image_size, $opt, true);
		$this->assignRef('default_vendor_image', $default_vendor_image);

		$menu_id = '';
		if(empty($this->module)) {
			$title = $this->params->get('page_title');
			if(empty($title))
				$title = $this->params->get('title');

			$page_title = $title;
			if(empty($title)) {
				$page_title = $app->getCfg('sitename');
			} elseif ($app->getCfg('sitename_pagetitles', 0) == 1) {
				$page_title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $page_title);
			} elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
				$page_title = JText::sprintf('JPAGETITLE', $page_title, $app->getCfg('sitename'));
			}
			$this->params->set('page_title', $title);
			$doc->setTitle(strip_tags($page_title));

			$pagination = hikamarket::get('shop.helper.pagination', $pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value);
			$pagination->hikaSuffix = '_'.$this->params->get('main_div_name');
			$this->assignRef('pagination',$pagination);
			$this->params->set('show_limit', 1);

			global $Itemid;
			if(!empty($Itemid))
				$menu_id = '&Itemid=' . $Itemid;
		} else {
			$menu_id = (int)$this->params->get('itemid', 0);
			$menu_id = (!empty($menu_id)) ? '&Itemid=' . $menu_id : '';
		}

		if(empty($menu_id)) {
			$i = (int)$config->get('vendor_default_menu', 0);
			if(!empty($i))
				$menu_id = '&Itemid=' . $i;
		}

		$this->assignRef('menu_id', $menu_id);

		$fieldsClass = hikamarket::get('shop.class.field');
		$this->assignRef('fieldsClass', $fieldsClass);

		$vendorFields = null;
		$extraFields = array(
			'vendor' => $fieldsClass->getFields('display:vendor_listing=1', $vendorFields, 'plg.hikamarket.vendor')
		);
		$displayFields = array(
			'vendor' => array()
		);
		foreach($extraFields['vendor'] as $fieldName => &$field) {
			if(empty($field->field_display))
				continue;
			if(strpos($field->field_display, ';vendor_listing=1;') === false)
				continue;
			$displayFields['vendor'][$fieldName] =& $field;
		}
		unset($field);

		$fieldsClass->handleZoneListing($displayFields['vendor'], $rows);

		$this->assignRef('extraFields', $extraFields);
		$this->assignRef('vendorFields', $vendorFields);
		$this->assignRef('displayFields', $displayFields);
	}
}
