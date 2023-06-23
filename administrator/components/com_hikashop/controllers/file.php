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
class FileController extends hikashopController{
	var $toggle = array();
	var $display = array();
	var $modify_views = array();
	var $add = array();
	var $modify = array('resetdownload');
	var $delete = array('delete');
	function resetdownload(){
		$fileClass = hikashop_get('class.file');
		$fileClass->resetdownload(hikaInput::get()->getInt('file_id'),hikaInput::get()->getInt('order_id'));
		$return = hikaInput::get()->getString('return');
		if(!empty($return)){
			$url = base64_decode(urldecode($return));
			if(hikashop_disallowUrlRedirect($url)) return false;
			$this->setRedirect($url);
		}
	}
}
