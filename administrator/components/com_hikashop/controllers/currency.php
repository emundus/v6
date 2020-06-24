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
class CurrencyController extends hikashopController{
	var $type='currency';

	function __construct(){
		parent::__construct();
		$this->modify[]='update';
	}

	function update(){
		$currency=hikaInput::get()->getInt('hikashopcurrency',0);
		if(!empty($currency)){
			$app = JFactory::getApplication();
			$app->setUserState( HIKASHOP_COMPONENT.'.currency_id', $currency );
			$url = hikaInput::get()->getString('return_url','');
			if(!empty($url)){
				if(hikashop_disallowUrlRedirect($url)) return false;
				$app->redirect(urldecode($url));
			}
			return true;
		}

		$ratePlugin = hikashop_import('hikashop','rates');
		if($ratePlugin){
			$ratePlugin->updateRates();
		} else {
			$app= JFactory::getApplication();
			$app->enqueueMessage('Currencies rates auto update plugin not found !','error');
		}
		$this->listing();
	}
}
