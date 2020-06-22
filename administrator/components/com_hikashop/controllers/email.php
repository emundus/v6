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
class emailController extends hikashopController {
	var $type = 'mail';

	function __construct($config = array())
	{
		parent::__construct($config);
		$this->modify_views[]='emailtemplate';
		$this->display[]='preview';
		$this->modify[]='saveemailtemplate';
		$this->modify_views[]='orderstatus';
		$this->modify[]='saveorderstatus';
	}
	public function test() {
	}

	public function edit() {
		return false;
	}

	public function remove() {
		return $this->listing();
	}

	public function getUploadSetting($upload_key, $caller = '') {
	}

	public function preview() {
	}

	public function manageUpload($upload_key, &$ret, $uploadConfig, $caller = '') {
		if(empty($ret))
			return;
	}

	function emailtemplate() {
	}

	function orderstatus() {
	}

	public function saveemailtemplate() {
	}

	public function saveorderstatus() {
	}
}
