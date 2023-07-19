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
class MenuViewMenu extends hikashopView{
	var $triggerView = true;

	function display($tpl = null,$title='',$menu_style=''){
		$this->assignRef('title',$title);
		$this->assignRef('menu_style',$menu_style);

		hikashop_loadJslib('dropdown');
		$config = hikashop_config();
		$function = $this->getLayout();
		if(method_exists($this,$function))
			$this->$function();

		$plugin = JPluginHelper::getPlugin('system', 'hikashopaffiliate');

		$menus = array(
			'system' => array(
				'name' => JText::_('SYSTEM'),
				'check' => 'ctrl=config',
				'acl' => 'config',
				'task' => 'manage',
				'icon' => 'fa fa-wrench',
				'url' => (JFactory::getUser()->authorise('core.admin', 'com_hikashop')) ? hikashop_completeLink('config') : '#',
				'children' => array(
					array(
						'name' => JText::_('HIKA_CONFIGURATION'),
						'check' => 'ctrl=config',
						'acl' => 'config',
						'task' => 'manage',
						'icon' => 'fa fa-wrench',
						'url' => hikashop_completeLink('config'),
						'display'=>(JFactory::getUser()->authorise('core.admin', 'com_hikashop'))
					),
					array('name' => ''),
					array(
						'name' => JText::_('PAYMENT_METHODS'),
						'check' => array('ctrl'=>'plugins', 'plugin_type'=>'payment'),
						'acl' => 'plugins',
						'icon' => 'fa fa-hand-holding-usd fa-money',
						'url' => hikashop_completeLink('plugins&plugin_type=payment')
					),
					array(
						'name' => JText::_('SHIPPING_METHODS'),
						'check' => array('ctrl'=>'plugins', 'plugin_type'=>'shipping'),
						'acl' => 'plugins',
						'icon' => 'fa fa-shipping-fast fa-truck',
						'url' => hikashop_completeLink('plugins&plugin_type=shipping')
					),
					array(
						'name' => JText::_('PLUGINS'),
						'check' => array('ctrl'=>'plugins', 'plugin_type'=>'plugin'),
						'acl' => 'plugins',
						'icon' => 'fa fa-puzzle-piece',
						'url' => hikashop_completeLink('plugins&plugin_type=plugin')
					),
					array('name' => ''),
					array(
						'name' => JText::_('WAREHOUSE'),
						'check' => array('ctrl'=>'warehouse'),
						'acl' => 'warehouse',
						'icon' => 'fa fa-industry',
						'url' => hikashop_completeLink('warehouse')
					),
					array('name' => ''),
					array(
						'name' => JText::_('TAXES'),
						'check' => array('ctrl'=>'taxation'),
						'acl' => 'taxation',
						'icon' => 'fa fa-university',
						'url' => hikashop_completeLink('taxation')
					),
					array(
						'name' => JText::_('ZONES'),
						'check' => 'ctrl=zone',
						'acl' => 'zone',
						'icon' => 'fa fa-map-marker-alt fa-map-marker',
						'url' => hikashop_completeLink('zone')
					),
					array(
						'name' => JText::_('CURRENCIES'),
						'check' => array('ctrl'=>'currency'),
						'acl' => 'currency',
						'icon' => 'fa fa-euro-sign',
						'url' => hikashop_completeLink('currency')
					),
					array('name' => ''),
					array(
						'name' => JText::_('ORDER_STATUSES'),
						'check' => array('ctrl'=>'orderstatus'),
						'acl' => 'orderstatus',
						'icon' => 'fa fa-tasks',
						'url' => hikashop_completeLink('orderstatus')
					),
					array(
						'name' => JText::_('EMAILS'),
						'check' => array('ctrl' => 'email'),
						'acl' => 'email',
						'url' => hikashop_completeLink('email'),
						'icon' => 'fa fa-envelope',
					),
					array(
						'name' => JText::_('HIKA_MASSACTION'),
						'check' => 'ctrl=massaction',
						'acl' => 'massaction',
						'url' => hikashop_completeLink('massaction'),
						'icon' => 'fa fa-cogs'
					)
				)
			),
			'products' => array(
				'name' => JText::_('PRODUCTS'),
				'check' => array('ctrl'=>'product', '!task'=>array('add')),
				'acl' => 'product',
				'icon' => 'fa fa-cubes',
				'url' => hikashop_completeLink('product'),
				'children' => array(
					array(
						'name' => JText::_('PRODUCTS'),
						'check' => array('ctrl'=>'product', '!task'=>array('add')),
						'acl' => 'product',
						'icon' => 'fa fa-cubes',
						'url' => hikashop_completeLink('product')
					),
					array(
						'name' => JText::_('HIKA_CATEGORIES'),
						'check' => array('ctrl'=>'category','filter_id'=>'product'),
						'acl' => 'category',
						'icon' => 'fa fa-folder',
						'url' => hikashop_completeLink('category&filter_id=product')
					),
					array(
						'name' => JText::_('CHARACTERISTICS'),
						'check' => 'ctrl=characteristic',
						'acl' => 'characteristic',
						'icon' => 'fa fa-adjust',
						'url' => hikashop_completeLink('characteristic')
					),
					array(
						'name' => JText::_('MANUFACTURERS'),
						'check' => array('ctrl'=>'category','filter_id'=>'manufacturer'),
						'acl' => 'category',
						'icon' => 'fa fa-tag',
						'url' => hikashop_completeLink('category&filter_id=manufacturer')
					),
					array(
						'name' => JText::_('HIKA_BADGES'),
						'check'=> 'ctrl=badge',
						'acl' => 'badge',
						'icon' => 'fa fa-image',
						'url' => hikashop_completeLink('badge')
					),
					array(
						'name' => JText::_('LIMIT'),
						'check'=> 'ctrl=limit',
						'icon' => 'fa fa-tachometer fa-tachometer-alt',
						'acl' => 'limit',
						'url'=> hikashop_completeLink('limit'),
						'display' => hikashop_level(1)
					),

					array(
						'name' => JText::_('IMPORT'),
						'check' => 'ctrl=import',
						'acl' => 'import',
						'icon' => 'fa fa-download',
						'url' => hikashop_completeLink('import&task=show')
					)
				)
			),
			'customers' => array(
				'name' => JText::_('CUSTOMERS'),
				'check' => array('ctrl'=>'user','filter_partner'=>0, '!task'=>array('clicks')),
				'acl' => 'user',
				'icon' => 'fa fa-user',
				'url' => hikashop_completeLink('user&filter_partner=0'),
				'children' => array(
					array(
						'name' => JText::_('CUSTOMERS'),
						'check' => array('ctrl'=>'user','filter_partner'=>0, '!task'=>array('clicks')),
						'acl' => 'user',
						'icon' => 'fa fa-user',
						'url' => hikashop_completeLink('user&filter_partner=0')
					),
					array(
						'name' => JText::_('VOTE'),
						'check' => 'ctrl=vote',
						'acl' => 'vote',
						'icon' => 'fa fa-star',
						'url' => hikashop_completeLink('vote')
					),
					array(
						'name' => JText::_('HIKASHOP_CHECKOUT_CART'),
						'check' => array('ctrl'=>'cart','cart_type'=>'cart'),
						'acl' => 'cart',
						'icon' => 'fa fa-shopping-cart',
						'url' => hikashop_completeLink('cart&cart_type=cart'),
						'display' => hikashop_level(1)
					),
					array(
						'name' => JText::_('WISHLISTS'),
						'check' => array('ctrl'=>'cart','cart_type'=>'wishlist'),
						'acl' => 'wishlist',
						'icon' => 'fa fa-heart',
						'url' => hikashop_completeLink('cart&cart_type=wishlist'),
						'display' => hikashop_level(1) && $config->get('enable_wishlist', 0)
					),
					array(
						'name' => JText::_('HIKA_WAITLIST'),
						'check' => array('ctrl'=>'waitlist'),
						'acl' => 'waitlist',
						'icon' => 'fa fa-clock',
						'url' => hikashop_completeLink('waitlist'),
						'display' => hikashop_level(1) && $config->get('product_waitlist', 1)
					),
				)
			),
			'orders' => array(
				'name' => JText::_('ORDERS'),
				'check' => array('ctrl'=>'order','filter_partner'=>0),
				'acl' => 'order',
				'icon' => 'fa fa-credit-card',
				'url' => hikashop_completeLink('order&order_type=sale&filter_partner=0'),
				'children' => array(
					array(
						'name' => JText::_('ORDERS'),
						'check' => array('ctrl'=>'order','filter_partner'=>0),
						'acl' => 'order',
						'icon' => 'fa fa-credit-card',
						'url' => hikashop_completeLink('order&order_type=sale&filter_partner=0')
					),
					array(
						'name' => JText::_('DISCOUNTS'),
						'check' => array('ctrl' => 'discount','filter_type'=>'discount'),
						'acl' => 'discount',
						'icon' => 'fa fa-percent',
						'url' => hikashop_completeLink('discount&filter_type=discount')
					),
					array(
						'name' => JText::_('COUPONS'),
						'check' => array('ctrl' => 'discount','filter_type'=>'coupon'),
						'acl' => 'discount',
						'icon' => 'fa fa-percent',
						'url' => hikashop_completeLink('discount&filter_type=coupon')
					),
					array(
						'name' => JText::_('HIKASHOP_ENTRIES'),
						'check' => 'ctrl=entry',
						'acl' => 'entry',
						'icon' => 'fa fa-users',
						'url' => hikashop_completeLink('entry'),
						'display' => hikashop_level(2)
					),
					array(
						'name' => JText::_('HIKASHOP_REPORTS'),
						'check' => 'ctrl=report',
						'acl' => 'report',
						'icon' => 'fa fa-chart-bar',
						'url' => hikashop_completeLink('report'),
						'display' => hikashop_level(1)
					)
				)
			),
			'affiliates' => array(
				'name' => JText::_('AFFILIATES'),
				'check' => array('ctrl'=>'user','filter_partner'=>'1'),
				'acl' => 'affiliates',
				'icon' => 'fa fa-users-cog fa-user-plus',
				'url' => hikashop_completeLink('user&filter_partner=1'),
				'display' => (!empty($plugin) && hikashop_level(2)),
				'children' => array(
					array(
						'name' => JText::_('PARTNERS'),
						'check' => array('ctrl'=>'user','filter_partner'=>'1'),
						'acl' => 'affiliates',
						'icon' => 'fa fa-users-cog fa-user-plus',
						'url' => hikashop_completeLink('user&filter_partner=1')
					),
					array(
						'name' => JText::_('HIKA_BANNERS'),
						'check' => 'ctrl=banner',
						'acl' => 'banner',
						'icon' => 'fa fa-window-maximize',
						'url' => hikashop_completeLink('banner')
					),
					array(
						'name' => JText::_('AFFILIATES_SALES'),
						'check' => array('ctrl'=>'order','filter_partner'=>'1'),
						'acl' => 'order',
						'icon' => 'fa fa-credit-card',
						'url' => hikashop_completeLink('order&order_type=sale&filter_partner=1')
					),
					array(
						'name' => JText::_('CLICKS'),
						'check' => array('ctrl'=>'user', 'task'=>'clicks'),
						'acl' => 'order',
						'icon' => 'fa fa-mouse-pointer',
						'url' => hikashop_completeLink('user&task=clicks')
					)
				)
			),
			'display' => array(
				'name' => JText::_('DISPLAY'),
				'check' => 'ctrl=view',
				'acl' => 'view',
				'icon' => 'fa fa-tv fa-television',
				'url' => hikashop_completeLink('view'),
				'children' => array(
					array(
						'name' => JText::_('VIEWS'),
						'check' => 'ctrl=view',
						'acl' => 'view',
						'icon' => 'fa fa-file-code',
						'url' => hikashop_completeLink('view')
					),
					array(
						'name' => JText::_('CONTENT_MENUS'),
						'check' => 'ctrl=menus',
						'acl' => 'menus',
						'icon' => 'fa fa-list',
						'url' => hikashop_completeLink('menus'),
						'display' => !HIKASHOP_J30
					),
					array(
						'name' => JText::_('CONTENT_MODULES'),
						'check' => 'ctrl=modules',
						'acl' => 'modules',
						'icon' => 'fa fa-cube',
						'url' => hikashop_completeLink('modules'),
						'display' => !HIKASHOP_J30
					),
					array(
						'name' => JText::_('FIELDS'),
						'check' => 'ctrl=field',
						'acl' => 'field',
						'icon' => 'fa fa-check-square',
						'url' => hikashop_completeLink('field')
					),
					array(
						'name' => JText::_('FILTERS'),
						'check' => 'ctrl=filter',
						'acl' => 'filter',
						'icon' => 'fa fa-filter',
						'url' => hikashop_completeLink('filter'),
						'display' => hikashop_level(2)
					)
				)
			),
			'help' => array(
				'name' => JText::_('DOCUMENTATION'),
				'check' => 'ctrl=documentation',
				'acl' => 'documentation',
				'icon' => 'fa fa-life-ring',
				'url' => hikashop_completeLink('documentation'),
				'children' => array(
					array(
						'name' => JText::_('DOCUMENTATION'),
						'check' => 'ctrl=documentation',
						'acl' => 'documentation',
						'icon' => 'fa fa-life-ring',
						'url' => hikashop_completeLink('documentation')
					),
					array(
						'name' => JText::_('UPDATE_ABOUT'),
						'check' => 'ctrl=update',
						'acl' => 'update_about',
						'icon' => 'fa fa-sync',
						'url' => hikashop_completeLink('update')
					),
					array(
						'name' => JText::_('FORUM'),
						'options' => 'target="_blank"',
						'acl' => 'forum',
						'icon' => 'fa fa-info',
						'url' => HIKASHOP_URL.'support/forum.html'
					)
				)
			)
		);

		$this->_checkActive($menus);
		$this->assignRef('menus',$menus);

		parent::display($tpl);
	}

	function _checkActive(&$menus, $level = 0){
		if($level >= 2)
			return;

		if(empty($this->request)) {
			$this->request = array();
			$this->request['option'] = hikaInput::get()->getCmd('option', HIKASHOP_COMPONENT);
			$this->request['ctrl'] = hikaInput::get()->getCmd('ctrl', null);
			$this->request['task'] = hikaInput::get()->getCmd('task', null);
		}

		foreach($menus as $k => $menu) {
			if(!empty($menu['check'])) {
				if(is_array($menu['check'])) {
					$active = true;
					if(!isset($menu['check']['option'])) {
						$menu['check']['option'] = HIKASHOP_COMPONENT;
					}
					foreach($menu['check'] as $key => $value) {
						$invert = false;
						if(substr($key, 0, 1) == '!') {
							$key = substr($key,1);
							$invert = true;
						}

						if(!isset($this->request[$key])) {
							$this->request[$key] = hikaInput::get()->getCmd($key, null);
						}

						if($value === 0 && empty($this->request[$key])) {
							continue;
						}
						if($invert) {
							if(is_array($value)) {
								$active = !in_array($this->request[$key], $value);
							} else {
								$active = ($this->request[$key] != $value);
							}
						} else {
							$active = ($this->request[$key] == $value);
						}
						if(!$active)
							break;
					}
					if($active) {
						$menus[$k]['active'] = true;
					}
				} else {
					if(strpos($menu['check'], 'option=') === false) {
						if($this->request['option'] == HIKASHOP_COMPONENT && strpos(@$_SERVER['QUERY_STRING'], $menu['check']) !== false) {
							$menus[$k]['active'] = true;
						}
					} elseif(strpos(@$_SERVER['QUERY_STRING'], $menu['check']) !== false) {
						$menus[$k]['active'] = true;
					}
				}
			}
			if(isset($menu['display']) && !$menu['display']) {
				unset($menus[$k]);
				continue;
			}
			if(!empty($menu['children'])) {
				$this->_checkActive($menus[$k]['children'], $level+1);
			}
		}
	}
}
