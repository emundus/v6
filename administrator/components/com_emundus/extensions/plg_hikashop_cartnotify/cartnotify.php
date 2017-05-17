<?php
/**
 * @package	HikaShop for Joomla!
 * @version	3.0.1
 * @author	hikashop.com
 * @copyright	(C) 2010-2017 HIKARI SOFTWARE. All rights reserved.
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

		$plugin = JPluginHelper::getPlugin('hikashop', 'cartnotifiy');
		if(!HIKASHOP_J25) {
			jimport('joomla.html.parameter');
			$this->params = new JParameter(@$plugin->params);
		} else {
			$this->params = new JRegistry(@$plugin->params);
		}
	}

	public function onBeforeCompileHead() {
		$app = JFactory::getApplication();
		if($app->isAdmin())
			return;

		$reference = $this->params->get('notification_reference', 'global');
		if($reference == 'popup')
			return $this->initVex();

		$this->initCartNotificationScript();
	}

	protected function initCartNotificationScript() {
		$app = JFactory::getApplication();
		$doc = JFactory::getDocument();
		$base = ($app->isAdmin()) ? '..' : JURI::base(true);

		if(HIKASHOP_J30)
			JHtml::_('jquery.framework');
		else
			hikashop_loadJslib('jquery');

		if(HIKASHOP_J25) {
			$doc->addScript($base.'/plugins/hikashop/cartnotify/media/notify.min.js');
			$doc->addStyleSheet($base.'/plugins/hikashop/cartnotify/media/notify-metro.css');
		} else {
			$doc->addScript($base.'/plugins/hikashop/media/notify.min.js');
			$doc->addStyleSheet($base.'/plugins/hikashop/media/notify-metro.css');
		}

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

		$js = '
jQuery.notify.defaults('.json_encode($params).');
window.cartNotifyParams = '.json_encode(array(
		'reference' => $reference,
		'img_url' => HIKASHOP_IMAGES.'icons/icon-32-newproduct.png',
		'title' => JText::_('PRODUCT_ADDED_TO_CART'),
		'text' => JText::_('PRODUCT_SUCCESSFULLY_ADDED_TO_CART'),
		'wishlist_title' => JText::_('PRODUCT_ADDED_TO_WISHLIST'),
		'wishlist_text' => JText::_('PRODUCT_SUCCESSFULLY_ADDED_TO_WISHLIST'),
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

		$base = ($app->isAdmin()) ? '..' : JURI::base(true);
		if(HIKASHOP_J25) {
			$doc->addScript($base.'/plugins/hikashop/cartnotify/media/notify-vex.js');
			$doc->addStyleSheet($base.'/plugins/hikashop/cartnotify/media/notify-metro.css');
		} else {
			$doc->addScript($base.'/plugins/hikashop/media/notify-vex.js');
			$doc->addStyleSheet($base.'/plugins/hikashop/media/notify-metro.css');
		}

		$config = hikashop_config();
		$checkout_itemid = (int)$config->get('checkout_itemid', 0);
		$url_checkout = hikashop_completeLink('checkout'. (!empty($checkout_itemid) ? '&Itemid='.$checkout_itemid : ''), false, true);
		$link_to_checkout = (int)$this->params->get('checkout_button', 1);

		$extra_data = array();
		if($link_to_checkout) {
			$extra_data[] = '
window.cartNotifyParams.cart_params = {buttons:[
	{text:"'.JText::_('PROCEED_TO_CHECKOUT', true).'",type:"button",className:"vex-dialog-button-primary",click:function proceedClick(){window.location="'.$url_checkout.'";}},
	{text:"'.JText::_('CONTINUE_SHOPPING', true).'",type:"submit",className:"vex-dialog-button-primary",click:function continueClick(){}}
]};';
		}

		$js = '
if(window.Oby) {
vex.defaultOptions.className = "vex-theme-default"
window.cartNotifyParams = '.json_encode(array(
	'img_url' => HIKASHOP_IMAGES.'icons/icon-32-newproduct.png',
	'title' => JText::_('PRODUCT_ADDED_TO_CART'),
	'text' => JText::_('PRODUCT_SUCCESSFULLY_ADDED_TO_CART'),
	'wishlist_title' => JText::_('PRODUCT_ADDED_TO_WISHLIST'),
	'wishlist_text' => JText::_('PRODUCT_SUCCESSFULLY_ADDED_TO_WISHLIST'),
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
