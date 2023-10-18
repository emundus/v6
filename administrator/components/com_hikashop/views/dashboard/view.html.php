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
class dashboardViewDashboard extends hikashopView {

	public function display($tpl = null, $params = null) {
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		if(method_exists($this, $function))
			$this->$function($params);
		parent::display($tpl);
	}

	public function listing() {
		$this->toolbar = array();

		$config = hikashop_config();
		if($config->get('legacy_widgets', true)) {
			$this->widgets();
		} else {
			$this->statistics();
		}

		$this->links();
		hikashop_setTitle(JText::_('COM_HIKASHOP_DASHBOARD_VIEW_TITLE'), 'chart-line', 'dashboard');

		if(JFactory::getUser()->authorise('core.admin', 'com_hikashop')) {
			$this->toolbar[] = array('name' => 'preferences');
		}
		$this->toolbar[] = array('name' => 'pophelp', 'target' => 'dashboard');
		$toggle = hikashop_get('helper.toggle');
		$this->assignRef('toggleClass', $toggle);
	}

	public function cpanel() {
		$this->links();
	}

	public function widget($params) {
		$this->edit = true;
		$this->widgetClass = hikashop_get('class.widget');
		$widget = $params;
		if($widget->widget_params->display=='table'){
			foreach($widget->widget_params->table as $row){
				if(!empty($row))$this->widgetClass->data($row);
			}
		}else{
			$this->widgetClass->data($widget);
			if (isset($widget->widget_params->period_compare)  && $widget->widget_params->period_compare!='none' && $widget->widget_params->compare_with=='periods'){
				$this->widgetClass->data($widget);
			}
		}
		$this->assignRef('widget',$widget);

		$doc = JFactory::getDocument();
		$doc->addScript((hikashop_isSSL() ? 'https://' : 'http://').'www.google.com/jsapi');
		$currencyHelper = hikashop_get('class.currency');
		$this->assignRef('currencyHelper',$currencyHelper);
		$this->editView=true;
		if($widget->widget_params->display=='listing'){
			$this->setLayout($widget->widget_params->content_view);
		}else if($widget->widget_params->display=='column' || $widget->widget_params->display=='line' || $widget->widget_params->display=='area'){
			$this->setLayout('chart');
		}else{
			$this->setLayout($widget->widget_params->display);
		}
	}

	public function widgets() {
		$widgetClass = hikashop_get('class.widget');
		$widgets = $widgetClass->get();
		foreach($widgets as $k => $widget) {
			if(empty($widget->widget_params->content) && $widget->widget_params->display != 'table')
				continue;

			if($widget->widget_params->display == 'table') {
				foreach($widget->widget_params->table as $row){
					if(!empty($row))
						$widgetClass->data($row);
				}
			} else {
				$widgetClass->data($widget);
				if(isset($widget->widget_params->period_compare)  && $widget->widget_params->period_compare != 'none') {
					$widgetClass->data($widget);
				}
			}
		}
		$this->assignRef('widgets', $widgets);

		$doc = JFactory::getDocument();
		$doc->addScript((hikashop_isSSL() ? 'https://' : 'http://').'www.google.com/jsapi');

		$currencyHelper = hikashop_get('class.currency');
		$this->assignRef('currencyHelper',$currencyHelper);
		if(hikashop_level(1)) {
			$config =& hikashop_config();
			$manage = hikashop_isAllowed($config->get('acl_dashboard_manage','all'));
			$this->assignRef('manage',$manage);
			$delete = hikashop_isAllowed($config->get('acl_dashboard_delete','all'));
			$this->assignRef('delete',$delete);
			$this->toolbar[] = array('name' => 'link', 'icon' => 'new', 'alt' => 'NEW_WIDGET', 'url' => hikashop_completeLink('report&task=add&dashboard=true'), 'display' => $manage);
		}
	}

	public function statistics() {
		$statisticsClass = hikashop_get('class.statistics');
		$statistics = $statisticsClass->getDashboard('cpanel');

		$statistics_slots = array();
		foreach($statistics as $key => &$stat) {
			$slot = (int)@$stat['slot'];
			$stat['slot'] = $slot;
			$stat['key'] = $key;
			$statistics_slots[ $slot ] = $slot;
		}
		unset($stat);
		asort($statistics_slots);

		$this->assignRef('statisticsClass', $statisticsClass);
		$this->assignRef('statistics', $statistics);
		$this->assignRef('statistics_slots', $statistics_slots);
	}

	public function links() {
		$buttons = array();
		$desc = array(
			'product' => array(JText::_('PRODUCTS_DESC_CREATE'),JText::_('PRODUCTS_DESC_MANAGE'),JText::_('CHATACTERISTICS_DESC_MANAGE')),
			'category' => array(JText::_('CATEGORIES_DESC_CREATE')),
			'user' => array(JText::_('CUSTOMERS_DESC_CREATE'),JText::_('CUSTOMERS_DESC_MANAGE')),
			'order' => array(JText::_('ORDERS_DESC'),JText::_('ORDERS_DESC_STATUS')),
			'banner' => array(JText::_('AFFILIATES_DESC'),JText::_('AFFILIATES_DESC_BANNERS'),JText::_('AFFILIATES_DESC_SALES')),
			'zone' => array(JText::_('ZONE_DESC'),JText::_('ZONE_DESC_TAXES')),
			'discount' => array(JText::_('DISCOUNT_DESC'),JText::_('DISCOUNT_DESC_LIMITS')),
			'currency' => array(JText::_('CURRENCY_DESC'),JText::_('CURRENCY_DESC_RATES')),
			'plugins' => array(JText::_('PLUGINS_DESC_PAYMENT'),JText::_('PLUGINS_DESC_SHIPPING')),
			'view' => array(JText::_('DISPLAY_DESC_VIEW'),JText::_('DISPLAY_DESC_CONTENT'),JText::_('DISPLAY_DESC_FIELDS')),
			'config' => array(JText::_('CONFIG_DESC_HIKASHOP_CONFIG'),JText::_('CONFIG_DESC_HIKASHOP_MODIFY'),JText::_('CONFIG_DESC_EMAIL')),
			'documentation' => array(JText::_('HELP_DESC'),JText::_('UPDATE_DESC'),JText::_('FORUM_DESC')),
		);
		if(!hikashop_level(1)){
			$desc['discount'][] = '<small style="color:red">'.JText::_('ONLY_COMMERCIAL').'</small>';
			$desc['config'][] = '<small style="color:red">'.JText::_('ONLY_COMMERCIAL').'</small>';
		}
		if(!hikashop_level(2)){
			$desc['banner'][] = '<small style="color:red">'.JText::_('ONLY_FROM_HIKASHOP_BUSINESS').'</small>';
			$desc['currency'][] = '<small style="color:red">'.JText::_('ONLY_FROM_HIKASHOP_BUSINESS').'</small>';
		}

		$config =& hikashop_config();
		if(hikashop_isAllowed($config->get('acl_config_view','all'))) $desc['config'][] = JText::_('CONFIG_DESC_HIKASHOP_PLUGIN');

		if(hikashop_isAllowed($config->get('acl_product_view','all'))) $buttons[] = array('link'=>'product','level'=>0,'image'=>'fa fa-cubes','text'=>JText::_('PRODUCTS'));
		if(hikashop_isAllowed($config->get('acl_category_view','all'))) $buttons[] = array('link'=>'category','level'=>0,'image'=>'fa fa-folder','text'=>JText::_('HIKA_CATEGORIES'));
		if(hikashop_isAllowed($config->get('acl_user_view','all'))) $buttons[] = array('link'=>'user','level'=>0,'image'=>'fa fa-user','text'=>JText::_('CUSTOMERS'));
		if(hikashop_isAllowed($config->get('acl_order_view','all'))) $buttons[] = array('link'=>'order','level'=>0,'image'=>'fa fa-credit-card','text'=>JText::_('ORDERS'));
		if(hikashop_isAllowed($config->get('acl_banner_view','all'))) $buttons[] = array('link'=>'banner','level'=>2,'image'=>'fa fa-users-cog fa-user-plus','text'=>JText::_('AFFILIATES'));
		if(hikashop_isAllowed($config->get('acl_zone_view','all'))) $buttons[] = array('link'=>'zone','level'=>0,'image'=>'fa fa-map-marker-alt fa-map-marker','text'=>JText::_('ZONES'));
		if(hikashop_isAllowed($config->get('acl_discount_view','all'))) $buttons[] = array('link'=>'discount','level'=>0,'image'=>'fa fa-percent','text'=>JText::_('DISCOUNTS'));
		if(hikashop_isAllowed($config->get('acl_currency_view','all'))) $buttons[] = array('link'=>'currency','level'=>0,'image'=>'fa fa-euro-sign','text'=>JText::_('CURRENCIES'));
		if(hikashop_isAllowed($config->get('acl_plugins_view','all'))) $buttons[] = array('link'=>'plugins','level'=>0,'image'=>'fa fa-puzzle-piece','text'=>JText::_('PLUGINS'));
		if(hikashop_isAllowed($config->get('acl_view_view','all'))) $buttons[] = array('link'=>'view','level'=>0,'image'=>'fa fa-tv fa-television','text'=>JText::_('DISPLAY'));
		if(JFactory::getUser()->authorise('core.admin', 'com_hikashop') && hikashop_isAllowed($config->get('acl_config_view','all'))) $buttons[] = array('link'=>'config','level'=>0,'image'=>'fa fa-wrench','text'=>JText::_('HIKA_CONFIGURATION'));
		if(hikashop_isAllowed($config->get('acl_update_about_view','all'))) $buttons[] = array('link'=>'documentation','level'=>0,'image'=>'fa fa-sync','text'=>JText::_('UPDATE_ABOUT'));

		$htmlbuttons = array();
		foreach($buttons as $oneButton){
			$htmlbuttons[] = $this->_quickiconButton($oneButton['link'],$oneButton['image'],$oneButton['text'],$desc[$oneButton['link']],$oneButton['level']);
		}
		$this->assignRef('buttons', $htmlbuttons);
		$this->assignRef('buttonList', $buttons);
		$this->assignRef('descriptions', $desc);
	}

	public function _quickiconButton($link, $image, $text,$description,$level) {
		if(is_array($description))
			$description = '<ul><li>' . implode('</li><li>', $description) . '</li></ul>';

		$url = hikashop_level($level) ? 'onclick="document.location.href=\''.hikashop_completeLink($link).'\';"' : '';
		$html = '<div style="float:left;width: 100%;" '.$url.' class="icon"><a href="' .
			(hikashop_level($level) ? hikashop_completeLink($link) : '#') .
			'"><table width="100%"><tr><td style="text-align: center;" width="120px">' .
			'<span class="'.$image.'" style="background-repeat:no-repeat;background-position:center;height:48px" title="'.$text.'"> </span>' .
			'<span>'.$text.'</span></td><td>'.$description.'</td></tr></table></a>' .
			'</div>';
		return $html;
	}
}
