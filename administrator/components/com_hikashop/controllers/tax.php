<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class TaxController extends hikashopController{
	var $type='tax';
	var $namekey = 'tax_namekey';
	var $table = 'tax';

	function __construct($config = array()) {
		parent::__construct($config);
		$this->display = array_merge($this->display, array('export'));
	}

	function cancel(){
		$return = hikaInput::get()->getString('return');
		if(!empty($return)){
			if(strpos($return,HIKASHOP_LIVE)===false && preg_match('#^https?://.*#',$return)) return false;
			$this->setRedirect(hikashop_completeLink(urldecode($return),false,true));
		}else{
			return $this->listing();
		}
		return true;
	}

	function export(){
		hikaInput::get()->set('layout', 'export');
		return parent::display();
	}

}
