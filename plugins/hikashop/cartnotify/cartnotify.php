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
class plgHikashopCartnotify extends JPlugin
{
	public function __construct(&$subject, $config) {
		parent::__construct($subject, $config);

		if(isset($this->params))
			return;

		$plugin = JPluginHelper::getPlugin('hikashop', 'cartnotify');
		$this->params = new JRegistry(@$plugin->params);
	}

	public function onBeforeCompileHead() {
		$app = JFactory::getApplication();
		if(version_compare(JVERSION,'4.0','<')) {
			if($app->isAdmin())
				return;
		} else {
			if($app->isClient('administrator'))
				return;
		}

		$reference = $this->params->get('notification_reference', 'global');
		if($reference == 'popup')
			return $this->initVex();

		$this->initCartNotificationScript();
	}

	protected function initCartNotificationScript() {
		$app = JFactory::getApplication();
		$doc = JFactory::getDocument();
		$base = (hikashop_isClient('administrator')) ? '..' : JURI::base(true);

		hikashop_loadJslib('notify');
		$doc->addScript($base.'/plugins/hikashop/cartnotify/media/notify.js');

		$reference = $this->params->get('notification_reference', 'global');

		$default_position = $this->params->get('notification_position', 'top right');
		if(!in_array($default_position, array('top right', 'top left', 'top center', 'right', 'bottom right', 'bottom left', 'bottom center', 'left')))
			$default_position = 'top right';

		$params = array(
			'arrowShow' => false,
			'globalPosition' => $default_position,
			'elementPosition' => $default_position,
			'clickToHide' => true
		);

		$delay = (int)$this->params->get('delay', 5000);
		if($delay > 0) {
			$params['autoHideDelay'] = (int)$delay;
			$params['autoHide'] = true;
		} else {
			$params['autoHide'] = false;
		}

		$url = '';
		if($this->params->get('auto_redirect', 'no_redirect') == 'on_success') {
			$menusClass = hikashop_get('class.menus');
			$url = $menusClass->getCheckoutURL();
		}

		$js = '
jQuery.notify.defaults('.json_encode($params).');
window.cartNotifyParams = '.json_encode(array(
		'reference' => $reference,
		'img_url' => HIKASHOP_IMAGES.'icons/icon-32-newproduct.png',
		'redirect_url' => $url,
		'redirect_delay' => $this->params->get('auto_redirect_delay', 4000),
		'title' => JText::_('PRODUCT_ADDED_TO_CART'),
		'text' => JText::_('PRODUCT_SUCCESSFULLY_ADDED_TO_CART'),
		'wishlist_title' => JText::_('PRODUCT_ADDED_TO_WISHLIST'),
		'wishlist_text' => JText::_('PRODUCT_SUCCESSFULLY_ADDED_TO_WISHLIST'),
		'list_title' => JText::_('PRODUCTS_ADDED_TO_CART'),
		'list_text' => JText::_('PRODUCTS_SUCCESSFULLY_ADDED_TO_CART'),
		'list_wishlist_title' => JText::_('PRODUCTS_ADDED_TO_WISHLIST'),
		'list_wishlist_text' => JText::_('PRODUCTS_SUCCESSFULLY_ADDED_TO_WISHLIST'),
		'err_title' => JText::_('PRODUCT_NOT_ADDED_TO_CART'),
		'err_text' => JText::_('PRODUCT_UNSUCCESSFULLY_ADDED_TO_CART'),
		'err_wishlist_title' =>  JText::_('PRODUCT_NOT_ADDED_TO_WISHLIST'),
		'err_wishlist_text' => JText::_('PRODUCT_UNSUCCESSFULLY_ADDED_TO_WISHLIST')
	)).';
';
		$doc->addScriptDeclaration($js);
	}

	protected function initVex() {
		hikashop_loadJslib('vex');

		$app = JFactory::getApplication();
		$doc = JFactory::getDocument();

		$base = (hikashop_isClient('administrator')) ? '..' : JURI::base(true);
		$doc->addScript($base.'/plugins/hikashop/cartnotify/media/notify-vex.js');
		$doc->addStyleSheet($base.'/media/com_hikashop/css/notify-metro.css');

		$menusClass = hikashop_get('class.menus');
		$url_checkout = $menusClass->getCheckoutURL(true);
		$link_to_checkout = (int)$this->params->get('checkout_button', 1);

		$link_continue = $this->params->get('continue_url', '');
		$link_continue = hikashop_translate($link_continue);
		$continue_js = '';
		if(!empty($link_continue)){
			$continue_js = 'window.location="'.$link_continue.'";';
		}

		$extra_data = array();
		if($link_to_checkout) {
			$extra_data[] = '
window.cartNotifyParams.cart_params = {buttons:[
	{text:"'.JText::_('PROCEED_TO_CHECKOUT', true).'",type:"button",className:"vex-dialog-button-primary",click:function proceedClick(){window.location="'.$url_checkout.'";}},
	{text:"'.JText::_('CONTINUE_SHOPPING', true).'",type:"submit",className:"vex-dialog-button-primary",click:function continueClick(){'.$continue_js.'}}
]};';
		}

		$js = '
if(window.Oby) {
vex.defaultOptions.className = "vex-theme-default";
vex.dialog.buttons.YES.text = "'.JText::_('HIKA_OK', true).'";
window.cartNotifyParams = '.json_encode(array(
	'img_url' => HIKASHOP_IMAGES.'icons/icon-32-newproduct.png',
	'title' => JText::_('PRODUCT_ADDED_TO_CART'),
	'text' => JText::_('PRODUCT_SUCCESSFULLY_ADDED_TO_CART'),
	'wishlist_title' => JText::_('PRODUCT_ADDED_TO_WISHLIST'),
	'wishlist_text' => JText::_('PRODUCT_SUCCESSFULLY_ADDED_TO_WISHLIST'),
	'list_title' => JText::_('PRODUCTS_ADDED_TO_CART'),
	'list_text' => JText::_('PRODUCTS_SUCCESSFULLY_ADDED_TO_CART'),
	'list_wishlist_title' => JText::_('PRODUCTS_ADDED_TO_WISHLIST'),
	'list_wishlist_text' => JText::_('PRODUCTS_SUCCESSFULLY_ADDED_TO_WISHLIST'),
	'err_title' => JText::_('PRODUCT_NOT_ADDED_TO_CART'),
	'err_text' => JText::_('PRODUCT_UNSUCCESSFULLY_ADDED_TO_CART'),
	'err_wishlist_title' => JText::_('PRODUCT_NOT_ADDED_TO_WISHLIST'),
	'err_wishlist_text' => JText::_('PRODUCT_UNSUCCESSFULLY_ADDED_TO_WISHLIST')
)).';'.implode('',$extra_data).'
}
';
		$doc->addScriptDeclaration($js);
	}
}
