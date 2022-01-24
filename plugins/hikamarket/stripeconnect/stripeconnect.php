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
class plgHikamarketStripeconnect extends JPlugin {
	public function __construct(&$subject, $config) {
		parent::__construct($subject, $config);
	}

	public function onHikamarketBeforeDisplayView($viewObj) {
		$viewName = $viewObj->getName();
		$layout = $viewObj->getLayout();

		if($viewName != 'vendormarket' || $layout != 'form')
			return;

		if(empty($viewObj->element->extraData))
			$viewObj->element->extraData = new stdClass();

		if(empty($viewObj->element->extraData->middle))
			$viewObj->element->extraData->middle = array();

		$this->initResources();
		include_once dirname(__FILE__).DS.'stripeconnect_class.php';

		$stripeClass = hikamarket::get('class.plg_stripeconnect');
		$viewObj->element->extraData->middle[] = $stripeClass->getVendorContent($viewObj);
	}

	private function initResources() {
		$this->loadLanguage('plg_hikamarket_stripeconnect', JPATH_ADMINISTRATOR);

		jimport('joomla.filesystem.file');
		$doc = JFactory::getDocument();
		if(JFile::exists(HIKASHOP_MEDIA . 'css' . DS . 'stripeconnect.css'))
			$doc->addStyleSheet(HIKASHOP_CSS . 'stripeconnect.css?v='.HIKAMARKET_RESSOURCE_VERSION);
		else
			$doc->addStyleSheet(JURI::base(true).'/plugins/hikamarket/stripeconnect/media/stripeconnect.css?v='.HIKAMARKET_RESSOURCE_VERSION);
	}

	public function onHikamarketPluginController($ctrl) {
		if($ctrl != 'stripeconnect')
			return;

		$app = JFactory::getApplication();
		$this->initResources();

		return array(
			'type' => 'hikamarket',
			'name' => 'stripeconnect',
			'prefix' => ($app->isAdmin() ? 'backend' : 'ctrl')
		);
	}
}
