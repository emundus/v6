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
class emailController extends hikashopController {
	var $type = 'mail';

	function __construct($config = array())
	{
		parent::__construct($config);
		$this->modify_views[]='emailtemplate';
		$this->display[]='preview';
		$this->display[]='diff';
		$this->modify[]='saveemailtemplate';
		$this->modify_views[]='orderstatus';
		$this->modify[]='saveorderstatus';
	}
	public function test() {
		$this->store();

		$config =& hikashop_config();
		$user = hikashop_loadUser(true);
		$mailClass = hikashop_get('class.mail');
		$addedName = $config->get('add_names',true) ? $mailClass->cleanText(@$user->name) : '';

		$mail = new stdClass();
		$mail->from_name = $config->get('from_name');
		$mail->from_email = $config->get('from_email');
		$mail->reply_name = $config->get('reply_name');
		$mail->reply_email = $config->get('reply_email');
		$mail->html = 0;

		$mail->debug = 1;

		$mailClass->AddAddress($user->user_email,$addedName);
		$mailClass->Subject = 'Test e-mail from '.HIKASHOP_LIVE;
		$mailClass->Body = 'This test email confirms that your configuration enables HikaShop to send emails normally.';

		$result = $mailClass->sendMail($mail);
		return $this->edit();
	}

	public function edit() {
		return parent::edit();
		return false;
	}

	public function remove() {
		$mail_name = hikaInput::get()->getCmd('mail_name');
		$type = hikaInput::get()->getCmd('type');

		$mailClass = hikashop_get('class.mail');
		$num = $mailClass->delete($mail_name, $type);

		$app = JFactory::getApplication();
		$app->enqueueMessage(JText::sprintf('SUCC_DELETE_ELEMENTS', $num), 'message');
		return $this->listing();
	}

	public function getUploadSetting($upload_key, $caller = '') {
		$mail_name = hikaInput::get()->getString('mail_name', '');

		if(empty($upload_key) || empty($mail_name))
			return false;

		$mailClass = hikashop_get('class.mail');
		$files = $mailClass->getFiles();
		if(!in_array($mail_name, $files))
			return false;


		$config = hikashop_config(false);

		$options = array(
			'upload_dir' => $config->get('uploadfolder'),
			'max_file_size' => null
		);

		return array(
			'limit' => 1,
			'type' => 'file',
			'options' => $options,
			'extra' => array(
				'mail_name' => $mail_name,
				'file_type' => 'file',
				'field_name' => 'data[mail][attachments][]',
			)
		);
	}

	public function preview() {
		$mail_name = hikaInput::get()->getString('mail_name', '');

		if(empty($mail_name))
			return false;
		hikaInput::get()->set('layout', 'preview');
		return parent::display();
	}

	public function manageUpload($upload_key, &$ret, $uploadConfig, $caller = '') {
		if(empty($ret))
			return;
		$config = hikashop_config();

		if(empty($uploadConfig['extra']['mail_name']))
			return false;
		$mail_name = $uploadConfig['extra']['mail_name'];

		$mailClass = hikashop_get('class.mail');
		$files = $mailClass->getFiles();
		if(!in_array($mail_name, $files))
			return false;

		$mail = new stdClass();
		$mail->mail_name = $mail_name;
		$mail->attach = array();

		$old = $config->get($mail->mail_name.'.attach');
		if(!empty($old)) {
			$oldAttachments = hikashop_unserialize($old);
			foreach($oldAttachments as $oldAttachment) {
				$mail->attach[] = $oldAttachment;
			}
		}

		$o = new stdClass();
		$o->filename = $ret->name;
		$o->size = 0;
		$mail->attach[] = $o;

		$mailClass->save($mail);

		$ret->params->delete = true;
	}

	function emailtemplate() {
		hikaInput::get()->set('layout', 'emailtemplate');
		return parent::display();
	}


	function diff() {
		hikaInput::get()->set('layout', 'diff');
		return parent::display();
	}

	function orderstatus() {
		hikaInput::get()->set('layout', 'orderstatus');
		return parent::display();
	}

	public function saveemailtemplate() {
		JSession::checkToken() || die('Invalid Token');

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

	public function saveorderstatus() {
		JSession::checkToken() || die('Invalid Token');

		$type = hikaInput::get()->getCmd('type');
		$email_name = hikaInput::get()->getCmd('email_name');
		$order_status =  hikaInput::get()->getString('order_status');

		jimport('joomla.filesystem.file');
		$fileName = JFile::makeSafe($email_name.'.'.$order_status.'.'.$type);

		$path = HIKASHOP_MEDIA.'mail'.DS.$fileName.'.php';
		if(empty($fileName) || $fileName == 'none' || strpos($fileName, DS) !== false || strpos($email_name, '.') !== false || strpos($order_status, '.') !== false || strpos($type, '.') !== false || !JPath::check($path)) {
			hikashop_display(JText::sprintf('FAIL_SAVE','invalid filename'),'error');
			return $this->orderstatus();
		}

		$content = hikaInput::get()->getRaw('emailcontent', '');
		$content = trim($content);

		if(empty($content)) {
			if(JFile::exists($path) && JFile::delete($path)) {
				hikashop_display(JText::sprintf('SUCC_DELETE_ELEMENTS', 1),'success');
			}
			return $this->orderstatus();
		}

		$ret = JFile::write($path, $content);
		if($ret)
			hikashop_display(JText::_('HIKASHOP_SUCC_SAVED'),'success');
		else
			hikashop_display(JText::sprintf('FAIL_SAVE',$path),'error');

		return $this->orderstatus();
	}
}
