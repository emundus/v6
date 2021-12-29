<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class plgHikamarketMangopay extends JPlugin {
	public function __construct(&$subject, $config) {
		parent::__construct($subject, $config);
	}

	public function onHikamarketPluginController($ctrl) {
		if($ctrl != 'mangopay')
			return;

		$app = JFactory::getApplication();
		$this->loadLanguage('plg_hikamarket_mangopay', JPATH_ADMINISTRATOR);

		return array(
			'type' => 'hikamarket',
			'name' => 'mangopay',
			'prefix' => (hikamarket::isAdmin() ? 'backend' : 'ctrl')
		);
	}

	public function onMarketAclPluginListing(&$categories) {
		if(empty($categories['root']['plugins']))
			$categories['root']['plugins'] = array();
		$categories['root']['plugins'][] = 'mangopay';
	}

	public function onVendorPanelDisplay(&$buttons, &$statistics) {
		global $Itemid;
		$url_itemid = '';
		if(!empty($Itemid))
			$url_itemid = '&Itemid=' . $Itemid;

		$this->loadLanguage('plg_hikamarket_mangopay', JPATH_ADMINISTRATOR);
		$doc = JFactory::getDocument();

		jimport('joomla.filesystem.file');
		if(JFile::exists(HIKASHOP_MEDIA . 'css' . DS . 'mangopay.css'))
			$doc->addStyleSheet(HIKASHOP_CSS . 'mangopay.css?v='.HIKAMARKET_RESSOURCE_VERSION);
		else
			$doc->addStyleSheet(JURI::base(true).'/plugins/hikamarket/mangopay/media/mangopay.css?v='.HIKAMARKET_RESSOURCE_VERSION);

		$buttons['mangopay'] = array(
			'url' => hikamarket::completeLink('mangopay'.$url_itemid),
			'level' => 1,
			'icon' => 'iconM-48-mangopay',
			'name' => JText::_('HIKAM_MANGOPAY'),
			'description' => '',
			'display' => hikamarket::acl('plugins/mangopay')
		);
	}
}
