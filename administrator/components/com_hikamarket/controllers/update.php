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
class updateMarketController extends hikashopBridgeController {

	public function __construct($config = array()){
		parent::__construct($config);
		$this->registerDefaultTask('update');
	}

	public function install() {
		hikamarket::setTitle(HIKAMARKET_NAME, 'sync', 'update');

		$newConfig = new stdClass();
		$newConfig->installcomplete = 1;
		$config = hikamarket::config();
		$config->save($newConfig);

		$updateHelper = hikamarket::get('helper.update');
		$updateHelper->addJoomfishElements();
		$updateHelper->addDefaultData();
		$updateHelper->createUploadFolders();
		$updateHelper->installMenu();
		$updateHelper->installExtensions();
		$updateHelper->addUpdateSite();

		$fieldsClass = hikamarket::get('class.field');
		$fieldsClass->initFields();

		$bar = JToolBar::getInstance('toolbar');
		$bar->appendButton('Link', HIKAMARKET_LNAME, JText::_('HIKASHOP_CPANEL'), hikamarket::completeLink('dashboard'));

		$this->showIframe(HIKAMARKET_UPDATEURL.'install&fromversion='.hikaInput::get()->getCmd('fromversion'));
		return false;
	}

	public function update() {
		$config = hikamarket::config();
		if($config->get('website') != HIKASHOP_LIVE){
			$updateHelper = hikamarket::get('helper.update');
			$updateHelper->addUpdateSite();
		}
		hikamarket::setTitle(JText::_('UPDATE_ABOUT'), 'sync', 'update');
		$bar = JToolBar::getInstance('toolbar');
		$bar->appendButton('Link', HIKAMARKET_LNAME, JText::_('HIKASHOP_CPANEL'), hikamarket::completeLink('dashboard'));
		$this->showIframe(HIKAMARKET_UPDATEURL.'update');
		return false;
	}

	private function showIframe($url) {
		$config = hikamarket::config();
		if(hikashop_isSSL())
			$url = str_replace('http://', 'https://', $url);
		echo '<div id="hikashop_div"><iframe allowtransparency="true" scrolling="auto" height="450px" frameborder="0" width="100%" name="hikamarket_frame" id="hikamarket_frame" '.
			'src="'.$url.'&level='.$config->get('level').'&component='.HIKAMARKET_LNAME.'&version='.$config->get('version').'&li='.urlencode(base64_encode(HIKASHOP_LIVE)).'"></iframe></div>';
	}

}
