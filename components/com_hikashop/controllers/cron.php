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
class CronController extends hikashopController{
	function __construct($config = array(),$skip=false){
		parent::__construct($config,$skip);
		$this->display[]='cron';
		if(!$skip){
			$this->registerDefaultTask('cron');
		}
		hikaInput::get()->set('tmpl','component');
	}
	function cron(){
		$config =& hikashop_config();
		if($config->get('cron') == 'no'){
			hikashop_display(JText::_('CRON_DISABLED'),'info');
			return false;
		}
		$cronHelper = hikashop_get('helper.cron');
		$cronHelper->report = true;
		$launched = $cronHelper->cron();
		if($launched){
			$cronHelper->report();
		}
	}
}
