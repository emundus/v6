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
class PluginsController extends hikashopController {
	var $type = 'plugins';
	var $listing = true;

	function __construct($config = array()){
		parent::__construct($config);
		$this->modify[]='trigger';
	}

	function trigger(){
		$cid= hikaInput::get()->getInt('cid', 0);
		$function = 'productDisplay';
		if(empty($cid) || empty($function)){
			return false;
		}
		$pluginsClass = hikashop_get('class.plugins');
		$plugin = $pluginsClass->get($cid);
		if(empty($plugin)){
			return false;
		}
		$plugin = hikashop_import($plugin->folder, $plugin->element);
		if(method_exists($plugin, $function))
			return $plugin->$function();
		return false;
	}
}
