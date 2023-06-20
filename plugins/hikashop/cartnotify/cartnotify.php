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
		$cartNotifyParams = array(
			'reference' => $reference,
			'img_url' => HIKASHOP_IMAGES.'icons/icon-32-newproduct.png',
			'redirect_url' => $url,
			'redirect_delay' => $this->params->get('auto_redirect_delay', 4000),
			'hide_delay' => $delay,
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
		);

		if($this->params->get('guest_wishlist_redirect', '0') == '1') {
			$user = JFactory::getUser();
			if($user->guest) {
				global $Itemid;
				$url = '';
				if(!empty($Itemid))
					$url = '&Itemid='.$Itemid;
				$url = 'index.php?option=com_users&view=login'.$url;
				$cartNotifyParams['err_wishlist_guest'] = JRoute::_($url.'&return='.urlencode(base64_encode(hikashop_currentUrl('', false))));
			}
		}

		$js = '
jQuery.notify.defaults('.json_encode($params).');
window.cartNotifyParams = '.json_encode($cartNotifyParams).';
';
		if($this->params->get('notification_click_to_checkout', '0') == '1') {
			$menusClass = hikashop_get('class.menus');
			$url = $menusClass->getCheckoutURL();
			$js.= '
jQuery(document).on("click", ".notifyjs-hidable", function(e) {
	if(e.currentTarget.querySelector(\'.notifyjs-metro-info\'))
		window.location=\''.$url.'\';
});
';

		}
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
		$continue_js = 'if(window.top.vex.closeAll) window.top.vex.closeAll();';
		if(!empty($link_continue)){
			$continue_js = 'window.top.location="'.$link_continue.'";';
		}

		$extra_data = array();
		if($link_to_checkout) {
			$extra_data[] = '
window.cartNotifyParams.cart_params = {buttons:[
	{text:"'.JText::_('PROCEED_TO_CHECKOUT', true).'",type:"button",className:"vex-dialog-button-primary",click:function proceedClick(){window.top.location="'.$url_checkout.'";}},
	{text:"'.JText::_('CONTINUE_SHOPPING', true).'",type:"submit",className:"vex-dialog-button-primary",click:function continueClick(){'.$continue_js.'}}
]};';
		}
		$url = '';
		if($this->params->get('auto_redirect', 'no_redirect') == 'on_success') {
			$menusClass = hikashop_get('class.menus');
			$url = $menusClass->getCheckoutURL();
		}
		$delay = (int)$this->params->get('delay', 5000);
		$cartNotifyParams = array(
			'img_url' => HIKASHOP_IMAGES.'icons/icon-32-newproduct.png',
			'redirect_url' => $url,
			'redirect_delay' => $this->params->get('auto_redirect_delay', 4000),
			'hide_delay' => $delay,
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
		);

		if($this->params->get('guest_wishlist_redirect', '0') == '1') {
			$user = JFactory::getUser();
			if($user->guest) {
				global $Itemid;
				$url = '';
				if(!empty($Itemid))
					$url = '&Itemid='.$Itemid;
				$url = 'index.php?option=com_users&view=login'.$url;
				$cartNotifyParams['err_wishlist_guest'] = JRoute::_($url.'&return='.urlencode(base64_encode(hikashop_currentUrl('', false))));
			}
		}

		$js = '
if(window.Oby) {
vex.defaultOptions.className = "vex-theme-default";
vex.dialog.buttons.YES.text = "'.JText::_('HIKA_OK', true).'";
window.cartNotifyParams = '.json_encode($cartNotifyParams).';'.implode('',$extra_data).'
}
';
		$doc->addScriptDeclaration($js);
	}
}
