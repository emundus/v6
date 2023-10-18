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
class CartmodulesViewCartmodules extends hikashopView{
	var $include_module = false;
	var $ctrl= 'modules';
	var $nameListing = 'MODULES';
	var $nameForm = 'MODULE';
	var $icon = 'module';

	function display($tpl = null,$params=null)
	{
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function($params);
		parent::display($tpl);
	}

	function options(&$params){
		$this->id = $params->get('id');
		$this->name = str_replace('[]', '', $params->get('name'));
		$this->element = $params->get('value');
		$this->pricetaxType = hikashop_get('type.pricetax');
		$this->discountDisplayType = hikashop_get('type.discount_display');
		$this->priceDisplayType = hikashop_get('type.priceDisplay');
		if(!isset($this->element['show_original_price']))
			$this->element['show_original_price'] = 0;
		if(!isset($this->element['show_discount']))
			$this->element['show_discount'] = 0;
		$this->arr = array(
			JHTML::_('select.option', '-1', JText::_('HIKA_INHERIT') ),
			JHTML::_('select.option', '1', JText::_('HIKASHOP_YES') ),
			JHTML::_('select.option', '0', JText::_('HIKASHOP_NO') ),
		);
		$this->arr[0]->class = 'btn-primary';
		$this->arr[1]->class = 'btn-success';
		$this->arr[2]->class = 'btn-danger';

		$this->arr1 = array(
			JHTML::_('select.option', '0', JText::_('HIKA_DEFAULT') ),
			JHTML::_('select.option', '1', JText::_('HIKA_CUSTOM') ),
			JHTML::_('select.option', '2', JText::_('HIKA_HIDE') ),
		);
		$this->arr1[0]->class = 'btn-primary';
		$this->arr1[1]->class = 'btn-success';
		$this->arr1[2]->class = 'btn-danger';

		$this->type = 'cart';
		if(preg_match('/wishlist/',$this->name))
			$this->type = 'wishlist';
		hikashop_loadJslib('tooltip');

		$cid = hikaInput::get()->getInt('id','');
		if(empty($cid))
			$cid = hikashop_getCID();
		$modulesClass = hikashop_get('class.modules');
		$module = $modulesClass->get($cid);
		if(empty($this->element)) {
			$this->element = $module->hikashop_params;
		}
		$config = hikashop_config();
		$this->default_params = $config->get('default_params');

		if(empty($this->element['small_cart']) || $this->element['small_cart'] == 1)
			return;

		$display_settings_array = array(
			'image_in_cart' => $this->element['image_in_cart'],
			'show_cart_quantity' => $this->element['show_cart_quantity'],
			'show_cart_delete' => $this->element['show_cart_delete'],
			'show_coupon' => $this->element['show_coupon'],
			'show_shipping' => $this->element['show_shipping'],
			'show_taxes' => @$this->element['show_taxes'],
			'print_cart' => @$this->element['print_cart'],
			'text_or_icon' => @$this->element['text_or_icon'],
		);
		$error_message = '';
		$find= 0;
		foreach($display_settings_array as $k => $v) {
			if ($v == 1) {
				$find = 1;
				break;
			}
		}
		if($find == 0) {
			$error_message = JText::_('HIKA_MOD_DISPLAY_ERROR');
			if(!empty($this->element['show_cart_proceed']))
				$error_message = JText::_('HIKA_MOD_DISPLAY_ERROR_PROCEED');
		}

		if ($error_message != '') {
			$app = JFactory::getApplication();
			$app->enqueueMessage($error_message, 'error');
		}
	}
}
