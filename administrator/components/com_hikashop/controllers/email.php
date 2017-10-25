<?php
/**
 * @package	HikaShop for Joomla!
 * @version	3.2.1
 * @author	hikashop.com
 * @copyright	(C) 2010-2017 HIKARI SOFTWARE. All rights reserved.
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
		$this->modify[]='saveemailtemplate';
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

	public function manageUpload($upload_key, &$ret, $uploadConfig, $caller = '') {
		if(empty($ret))
			return;
	}

	function emailtemplate(){
		hikaInput::get()->set('layout', 'emailtemplate');
		return parent::display();
	}

	public function saveemailtemplate(){
		if(!HIKASHOP_J25) {
			JRequest::checkToken() || die('Invalid Token');
		} else {
			JSession::checkToken() || die('Invalid Token');
		}
		$file = hikaInput::get()->getCmd('file');
		$email_name = hikaInput::get()->getCmd('email_name');

		jimport('joomla.filesystem.file');
		$fileName = JFile::makeSafe($file);

		$path = HIKASHOP_MEDIA.'mail'.DS.'template'.DS.$fileName.'.html.modified.php';
		if(empty($fileName) || $fileName == 'none' || strpos($fileName, DS) !== false || strpos($fileName, '.') !== false || !JPath::check($path)) {
			hikashop_display(JText::sprintf('FAIL_SAVE','invalid filename'),'error');
			return $this->emailtemplate();
		}

		$templatecontent = hikaInput::get()->getRaw('templatecontent', '');
		$templatecontent = trim($templatecontent);

		if(empty($templatecontent)) {
			if(JFile::exists($path) && JFile::delete($path)) {
				hikashop_display(JText::sprintf('SUCC_DELETE_ELEMENTS', 1),'success');
			}
			return $this->emailtemplate();
		}

		$ret = JFile::write($path, $templatecontent);
		if($ret)
			hikashop_display(JText::_('HIKASHOP_SUCC_SAVED'),'success');
		else
			hikashop_display(JText::sprintf('FAIL_SAVE',$path),'error');

		return $this->emailtemplate();
	}
}
