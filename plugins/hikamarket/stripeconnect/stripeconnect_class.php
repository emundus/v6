<?php
/**
 * @package    StripeConnect for Joomla! HikaShop
 * @version    1.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2020 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class hikamarketPlg_stripeconnectClass extends JObject {

	protected static $currentStripeVendor = null;
	protected static $stripeconnectPlugin = null;
	protected static $stripeconnectAPI = null;

	public function getStripePlugin() {
		if(empty(self::$stripeconnectPlugin))
			self::$stripeconnectPlugin = hikamarket::import('hikashoppayment', 'stripeconnect');
		return self::$stripeconnectPlugin;
	}

	public function getStripeVendor() {
		if(!empty(self::$currentStripeVendor))
			return self::$currentStripeVendor;

		$stripeconnectPlugin = $this->getStripePlugin();
		$stripeApi = $stripeconnectPlugin->getAPI();

		$vendor = hikamarket::loadVendor(true, false);
		self::$currentStripeVendor = $stripeApi->getVendor($vendor);
		return self::$currentStripeVendor;
	}

	public function getStripeAPI() {
		if(!empty(self::$stripeconnectAPI))
			return self::$stripeconnectAPI;

		$stripeconnectPlugin = $this->getStripePlugin();
		self::$stripeconnectAPI = $stripeconnectPlugin->getAPI();
		return self::$stripeconnectAPI;
	}

	public function debug($e, $r = false) {
		$mangopayPlugin = $this->getStripePlugin();
		$mangopayPlugin->stripeDebug($e, $r);
	}
	public function stripeDebug($e, $r = false) { return $this->debug($e, $r); }

	public function getVendorContent($view) {
		ob_start();
		$this->displayView('cpanel', array('shopView' => &$view, 'vendor' => $view->element));
		return ob_get_clean();
	}

	private function displayView($layout, $params = array()) {
		$app = JFactory::getApplication();
		$doc = JFactory::getDocument();

		$base_path = rtrim(dirname(__FILE__),DS);

		$controller = new hikashopBridgeController(array(
			'name' => 'stripeconnectmarket',
			'base_path' => $base_path
		));

		$viewType = $doc->getType();
		if(empty($viewType))
			$viewType = 'html';

		$view = $controller->getView( '', $viewType, '', array('base_path' => $base_path));

		$folder	= $base_path.DS.'views'.DS.$view->getName().DS.'tmpl';
		$view->addTemplatePath($folder);

		$folder	= JPATH_BASE.DS.'templates'.DS.$app->getTemplate().DS.'html'.DS.HIKASHOP_COMPONENT.DS.$view->getName();
		$view->addTemplatePath($folder);

		$old = $view->setLayout($layout);

		$view->display(null,$params);

		$js = @$view->js;
		if(!empty($old))
			$view->setLayout($old);
		return;
	}
}
