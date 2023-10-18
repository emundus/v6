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
class hikashopMailClass {
	public $mail_success = true;
	public $_force_embed = false;
	public $mail_folder = '';
	public $mailer = null;

	public function __construct() {
		$this->mailer = JFactory::getMailer();
	}

	public function get($name, &$data) {
		$app = JFactory::getApplication();

		$this->mailer = JFactory::getMailer();

		$mail = new stdClass();
		$mail->mail_name = $name;

		$this->loadInfos($mail, $name);

		$mail->body = $this->loadEmail($mail, $data);
		$mail->altbody = $this->loadEmail($mail, $data, 'text');
		if(hikashop_isClient('administrator'))
			$mail->preload = $this->loadEmail($mail, $data, 'preload');
		$mail->data =& $data;

		if($data !== true)
			$mail->body = hikashop_absoluteURL($mail->body);

		if(empty($mail->altbody) && $data !== true)
			$mail->altbody = $this->textVersion($mail->body);

		return $mail;
	}

	public function getFiles() {
		$files = array(
			'cron_report',
			'order_admin_notification',
			'order_creation_notification',
			'order_status_notification',
			'order_notification',
			'payment_notification',
			'user_account',
			'user_account_admin_notification',
			'out_of_stock',
			'order_cancel',
			'waitlist_notification',
			'waitlist_admin_notification',
			'new_comment',
			'contact_request',
			'subscription_eot',
			'massaction_notification',
			'wishlist_share'
		);

		$plugin_files = array();
		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$app->triggerEvent('onMailListing', array(&$plugin_files));
		if(!empty($plugin_files)) {
			$files = array_merge($files, $plugin_files);
		}

		return $files;
	}

	public function loadInfos(&$mail, $name) {
		$config =& hikashop_config();

		$mail->name_info = $name;

		$settings = array(
			'from_name' => true,
			'from_email' => true,
			'reply_name' => true,
			'reply_email' => true,

			'template' => array('config' => 'mail_default_template'),

			'bcc_email' => false,
			'cc_email' => false,
			'subject' => false,

			'html' => array('default' => 1),
			'published' => array('default' => 1),
			'email_log_published' => array('default' => 1),
		);
		foreach($settings as $k => $v) {
			$default = (is_array($v) && isset($v['default'])) ? $v['default'] : null;
			$mail->$k = $config->get($name . '.' . $k, $default);
			if(empty($mail->$k) && $v === true)
				$mail->$k = $config->get($k);
			if(empty($mail->$k) && is_array($v) && isset($v['config']))
				$mail->$k = $config->get( $v['config'] );
		}

		$attach = $config->get($name.'.attach');
		$mail->attach = array();
		if(!empty($attach)) {
			$mail->attach = hikashop_unserialize($attach);
		}
	}

	 public function getPreviewMaker($mail_name) {
	 	$class_name = $mail_name.'PreviewMaker';

		if(strpos($class_name, '.'))
			$class_name = str_replace('.', '_', $class_name);

		if(!class_exists($class_name)) {
		 	$path = $this->getMailPath($mail_name, 'preview');
			if(empty($path))
				return false;

			include_once($path);
		}
		if(!class_exists($class_name))
			return false;

		return new $class_name;
	 }

	public function saveForm() {
		$app = JFactory::getApplication();
		$mail = new stdClass();
		$config =& hikashop_config();

		$mail->mail_name = hikaInput::get()->getString('mail_name');
		$formData = hikaInput::get()->get('data', array(), 'array');
		jimport('joomla.filter.filterinput');
		$safeHtmlFilter = JFilterInput::getInstance(array(), array(), 1, 1);

		foreach($formData['mail'] as $column => $value) {
			hikashop_secureField($column);
			if(in_array($column, array('from_name', 'from_email', 'reply_name', 'reply_email')) && $config->get($column) == $value){
				$value = '';
			}
			if($column == 'attachments')
				continue;

			if(in_array($column, array('params', 'body', 'altbody', 'preload'))) {
				$mail->$column = $value;
			} else {
				$mail->$column = $safeHtmlFilter->clean(strip_tags($value), 'string');
			}
		}

		$mail->attach = array();

		$old = $config->get($mail->mail_name.'.attach');
		if(!empty($old)) {
			$oldAttachments = hikashop_unserialize($old);
			foreach($oldAttachments as $oldAttachment) {
				$mail->attach[] = $oldAttachment;
			}

			if(!empty($formData['mail']['attachments']) && is_array($formData['mail']['attachments'])) {
				$mail->attach = array();
				$attachments = array_combine($formData['mail']['attachments'], $formData['mail']['attachments']);
				unset($attachments['']);
				foreach($oldAttachments as $oldAttachment) {
					if(!isset($attachments[$oldAttachment->filename]))
						continue;
					$mail->attach[] = $oldAttachment;
				}
			}
		}

		$this->saveAttachmentLegacy($mail);

		return $this->save($mail);
	}

	protected function saveAttachmentLegacy(&$mail) {
		$attachments = hikaInput::get()->files->getVar('attachments', array(), 'array');

		if(empty($attachments) || (empty($attachments[0]['name'])))
			return;

		jimport('joomla.filesystem.file');
		$allowedFiles = explode(',',strtolower($config->get('allowedfiles')));
		$uploadFolder = JPath::clean(html_entity_decode($config->get('uploadfolder')));
		if(!preg_match('#^([A-Z]:)?/.*#',$uploadFolder)) {
			$uploadFolder = trim($uploadFolder,DS.' ').DS;
			$uploadFolder = JPath::clean(HIKASHOP_ROOT.$uploadFolder);
		}
		if(!is_dir($uploadFolder)) {
			jimport('joomla.filesystem.folder');
			JFolder::create($uploadFolder);
		}
		if(!is_writable($uploadFolder)) {
			@chmod($uploadFolder, '0755');
			if(!is_writable($uploadFolder)) {
				$app->enqueueMessage(JText::sprintf('WRITABLE_FOLDER', $uploadFolder), 'notice');
			}
		}

		foreach($attachments as $id => $file) {
			if(empty($file['name']))
				continue;

			$attachment = new stdClass();
			$attachment->filename = strtolower(JFile::makeSafe($file['name']));
			$attachment->size = $file['size'];
			if(!preg_match('#\.('.str_replace(array(',','.'),array('|','\.'),$config->get('allowedfiles')).')$#Ui',$attachment->filename,$extension) || preg_match('#\.(php.?|.?htm.?|pl|py|jsp|asp|sh|cgi)$#Ui',$attachment->filename)) {
				$app->enqueueMessage(JText::sprintf( 'ACCEPTED_TYPE',substr($attachment->filename,strrpos($attachment->filename,'.')+1),str_replace(',',', ',$config->get('allowedfiles'))), 'notice');
				continue;
			}

			$attachment->filename = str_replace(array('.',' '),'_',substr($attachment->filename,0,strpos($attachment->filename,$extension[0]))).$extension[0];
			if(!move_uploaded_file($file['tmp_name'], $uploadFolder . $attachment->filename)) {
				if(!JFile::upload($file['tmp_name'], $uploadFolder . $attachment->filename)) {
					$app->enqueueMessage(JText::sprintf( 'FAIL_UPLOAD',$file['tmp_name'],$uploadFolder . $attachment->filename), 'error');
					continue;
				}
			}

			$mail->attach[] = $attachment;
		}
	}

	public function save(&$element) {
		if(empty($element->mail_name))
			return false;
		$configData = array();
		if(isset($element->body)) {
			$this->saveEmail($element->mail_name, $element->body, 'html');
		}
		if(isset($element->altbody)) {
			$this->saveEmail($element->mail_name, $element->altbody, 'text');
		}
		if(isset($element->preload)) {
			$this->saveEmail($element->mail_name, $element->preload, 'preload');
		}

		$fields = array(
			'from_name' => 'string',
			'from_email' => 'string',
			'reply_name' => 'string',
			'reply_email' => 'string',
			'bcc_email' => 'string',
			'cc_email' => 'string',
			'subject' => 'string',
			'template' => 'string',
			'html' => 'integer',
			'attach' => 'serialize',
			'published' => 'integer',
			'email_log_published' => 'integer'
		);
		foreach($fields as $field => $field_type) {
			if(!isset($element->$field))
				continue;
			$value = $element->$field;
			switch($field_type) {
				case 'serialize':
					$value = serialize($value);
					break;
				case 'integer':
					$value = (int)$value;
					break;
			}
			$configData[$element->mail_name . '.' . $field] = $value;
		}

		$config =& hikashop_config();
		$config->save($configData);
		return $element->mail_name;
	}

	public function saveEmail($name, $data, $type = 'html') {
		$path = $this->getMailPath($name, $type, true);
		if(empty($path))
			$path = $this->mail_folder . $name . '.' . $type . '.modified.php';

		$data = trim($data);

		$original_path = $this->getMailPath($name, $type, false);
		if(!empty($original_path) && strpos($original_path, '.modified.php') !== false)
			$original_path = str_replace('.modified.php', '.php', $original_path);
		if(empty($original_path))
			$original_path = $this->mail_folder . $name . '.' . $type . '.php';
		$original_data = '';
		if(file_exists($original_path))
			$original_data = trim(file_get_contents($original_path));

		$check = ($data == $original_data);
		unset($original_data);

		jimport('joomla.filesystem.file');
		if(file_exists($path)) {
			JFile::delete($path);
		}

		if($check)
			return true;

		return JFile::write($path, $data);
	}

	public function delete(&$mail_name, $type = '') {
		$path = $this->getMailPath($mail_name, $type, true);
		if(empty($path))
			return false;

		jimport('joomla.filesystem.file');
		if(file_exists($path)) {
			return JFile::delete($path);
		}
		return true;
	}

	public function loadEmail(&$mail, &$data, $type = 'html') {
		$path = $this->getMailPath($mail, $type);

		if(empty($path))
			return '';

		if($data === true) {
			jimport('joomla.filesystem.file');
			return file_get_contents($path);
		}

		$preload = $this->getMailPath($mail, 'preload');
		$postload = $this->getMailPath($mail, 'postload');

		if($mail->mail_name == 'order_status_notification' && !empty($data->order_status)) {
			$mail_status_name = $mail->mail_name.'.'.$data->order_status;
			jimport('joomla.filesystem.file');
			$mail_status_name = JFile::makeSafe($mail_status_name);
			$pathWithStatus = $this->getMailPath($mail_status_name, $type);
			if(!empty($pathWithStatus)) $path = $pathWithStatus;

			$preloadWithStatus = $this->getMailPath($mail_status_name, 'preload');
			if(!empty($preloadWithStatus)) $preload = $preloadWithStatus;

			$postloadWithStatus = $this->getMailPath($mail_status_name, 'postload');
			if(!empty($postloadWithStatus)) $postload = $postloadWithStatus;
		}

		$currencyClass = hikashop_get('class.currency');
		$currencyHelper = $currencyClass;

		ob_start();

		$useTemplating = !empty($mail->template);
		$vars = array();
		$texts = array();
		$templates = array();

		if(!empty($preload) && file_exists($preload))
			include $preload;

		if($useTemplating || !empty($vars) || !empty($texts) || !empty($templates))
			ob_start();

		require($path);

		if(!empty($postload) && file_exists($postload)) {
			include $postload;
		} else if($useTemplating || !empty($vars) || !empty($texts) || !empty($templates)) {

			if(!empty($mail->template) && $type == 'html') {
				$tpl_path = $this->getTemplatePath($mail->template, $mail->mail_name, $type);
				if(empty($tpl_path))
					$tpl_path = $this->getTemplatePath('default', $mail->mail_name, $type);

				if(!empty($tpl_path)) {
					$vars['TPL_CONTENT'] = $this->processMailTemplate($mail, $data, $texts, $vars, $templates);
					ob_start();
					require($tpl_path);
				}
			}

			echo $this->processMailTemplate($mail, $data, $texts, $vars, $templates);
		}

		unset($vars);
		unset($texts);
		unset($templates);

		$ret = ob_get_clean();
		return trim($ret);
	}

	public function processMailTemplate(&$mail, &$data, $texts, $vars, $templates = array()) {
		$content = ob_get_clean();

		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$app->triggerEvent('onHkProcessMailTemplate', array(&$mail, &$data, &$content, &$vars, &$texts, &$templates));

		if(empty($content))
			return '';

		if(!empty($templates)) {
			foreach($templates as $key => $templateVariables) {
				$cursorStartLength = strlen('<!--{START:'.$key.'}-->');
				$cusorEndLength = strlen('<!--{END:'.$key.'}-->');

				$cursorStart = strpos($content, '<!--{START:'.$key.'}-->');
				$cursorEnd = strpos($content, '<!--{END:'.$key.'}-->');

				while($cursorStart !== false && $cursorEnd !== false) {
					$templateData = '';
					$templateContent = '';
					if($cursorStart !== false && $cursorEnd !== false) {
						$templateContent = substr($content, $cursorStart + $cursorStartLength, $cursorEnd - $cursorStart - $cursorStartLength);
						if(strpos($content, '{VAR:'.$key.'}') === false)
							$content = substr($content, 0, $cursorStart) . '{VAR:'.$key.'}' . substr($content, $cursorEnd + $cusorEndLength);
						else
							$content = substr($content, 0, $cursorStart) . substr($content, $cursorEnd + $cusorEndLength);
					}
					if(!empty($templateContent) && !empty($templateVariables)) {
						foreach($templateVariables as $c) {
							$d = $templateContent;
							foreach($c as $k => $v) {
								if(is_string($v) || is_int($v) || is_float($v)) {
									$d = str_replace('{LINEVAR:'.$k.'}', $v, $d);
								} else if(is_object($v) || is_array($v)) {
									foreach($v as $objK => $objV) {
										if(is_string($objV) || is_int($objV) || is_float($objV))
											$d = str_replace('{LINEVAR:'.$k.'.'.$objK.'}', $objV, $d);
									}
								}
							}
							$templateData .= $d;
						}
						$content = str_replace('{VAR:'.$key.'}', $templateData, $content);
					}
					$cursorStart = strpos($content, '<!--{START:'.$key.'}-->');
					$cursorEnd = strpos($content, '<!--{END:'.$key.'}-->');
				}
			}
		}

		foreach($texts as $k => $v) {
			if(is_null($v))
				$v = '';
			if(is_string($v) || is_int($v) || is_float($v))
				$content = str_replace('{TXT:'.$k.'}', $v, $content);
		}

		foreach($vars as $k => $v) {
			if(is_string($v) || is_int($v) || is_float($v))
				$content = str_replace('{VAR:'.$k.'}', $v, $content);
			else if(is_object($v) || is_array($v)) {
				foreach($v as $objK => $objV) {
					if(is_string($objV) || is_int($objV) || is_float($objV))
						$content = str_replace('{VAR:'.$k.'.'.$objK.'}', $objV, $content);
				}
			}

			$startIf = '<!--{IF:'.$k.'}-->';
			$endIf = '<!--{ENDIF:'.$k.'}-->';
			if(empty($v)) {
				$cursorStart = strpos($content, $startIf);
				$cursorEnd = strpos($content, $endIf);
				if($cursorStart !== false && $cursorEnd !== false) {
					$content = substr($content, 0, $cursorStart) . substr($content, $cursorEnd + strlen($endIf));
				} else {
					$content = str_replace(array($startIf, $endIf), '', $content);
				}
			} else {
				$content = str_replace(array($startIf, $endIf), '', $content);
			}
		}
		if(strpos($content, '<!--{IF:') !== false) {
			$out = array();
			if(preg_match_all('#<!--{IF:([- _A-Z0-9a-z]+)}-->#U', $content, $out)) {
				foreach($out[1] as $key) {
					$startIf = '<!--{IF:'.$key.'}-->';
					$endIf = '<!--{ENDIF:'.$key.'}-->';
					$cursorStart = strpos($content, $startIf);
					$cursorEnd = strpos($content, $endIf);
					if($cursorStart !== false && $cursorEnd !== false) {
						$content = substr($content, 0, $cursorStart) . substr($content, $cursorEnd + strlen($endIf));
					} else {
						$content = str_replace(array($startIf, $endIf), '', $content);
					}
				}
			}
		}

		if(strpos($content, '{TXT:') !== false) {
			$out = array();
			if(preg_match_all('#{TXT:([- _A-Z0-9a-z]+)}#U', $content, $out)) {
				foreach($out[1] as $key) {
					if(isset($texts[$key]))
						$content = str_replace('{TXT:'.$key.'}', $texts[$key], $content);
					else
						$content = str_replace('{TXT:'.$key.'}', JText::_($key), $content);
				}
			}
		}

		if(strpos($content, '{VAR:') !== false) {
			$out = array();
			if(preg_match_all('#{VAR:([-. _A-Z0-9a-z]+)}#U', $content, $out)) {
				foreach($out[1] as $key) {
					$content = str_replace('{VAR:'.$key.'}', '', $content);
				}
			}
		}

		if(strpos($content, '{LINEVAR:') !== false) {
			$out = array();
			if(preg_match_all('#{LINEVAR:([-. _A-Z0-9a-z]+)}#U', $content, $out)) {
				foreach($out[1] as $key) {
					$content = str_replace('{LINEVAR:'.$key.'}', '', $content);
				}
			}
		}

		return $content;
	}

	public function getMailPath(&$mail_name, $type = 'html', $getModifiedFile = false) {
		if(empty($this->mail_folder)) {
			$config = hikashop_config();
			$this->mail_folder = rtrim( str_replace( '{root}', JPATH_ROOT, $config->get('mail_folder', HIKASHOP_MEDIA.'mail'.DS)), '/\\');
			if(!empty($this->mail_folder))
				$this->mail_folder .= DS;
		}
		if(empty($this->mail_folder)) {
			$this->mail_folder = HIKASHOP_MEDIA.'mail'.DS;
		}

		if(is_object($mail_name)) {
			$name = $mail_name->mail_name;
		} else {
			$name = $mail_name;
		}

		if(strpos($name, '..') !== false)
			return false;

		$name = str_replace(array('\\', '/'), DS, $name);

		$override_path_name = $type.'_override_path';
		$path_name = $type.'_path';
		$override_name = $type.'_override';

		$override_path = $this->mail_folder . $name . '.' . $type . '.modified.php';
		$path = $this->mail_folder . $name . '.' . $type . '.php';
		if(is_object($mail_name)) {
			$mail_name->$override_path_name = $override_path;
			$mail_name->$path_name = $path;
		}

		if(!file_exists($path)) {

			$plugin_files = array();
			JPluginHelper::importPlugin('hikashop');
			$app = JFactory::getApplication();
			$app->triggerEvent('onMailListing', array(&$plugin_files));
			if(!empty($plugin_files)) {
				$mail_folder = '';
				$mail_file = '';
				foreach($plugin_files as $plugin_file) {
					if($plugin_file['file'] == $name || $plugin_file['filename'] == $name) {
						$mail_folder = @$plugin_file['folder'];
						$mail_file = $plugin_file['filename'];
						break;
					}
				}
				if(!empty($mail_file)) {
					if(empty($mail_folder))
						$mail_folder = $this->mail_folder;

					$override_path = $mail_folder . $mail_file . '.' . $type . '.modified.php';
					$path = $mail_folder . $mail_file . '.' . $type . '.php';
					if(is_object($mail_name)) {
						$mail_name->$override_path_name = $override_path;
						$mail_name->$path_name = $path;
					}
					if(file_exists($override_path)) {
						if(is_object($mail_name)) {
							$mail_name->$override_name = true;
						}
						return $override_path;
					}
					if(file_exists($path)) {
						if($getModifiedFile)
							return $override_path;
						return $path;
					}
					if($type == 'text' && $getModifiedFile && file_exists($mail_folder . $mail_file . '.html.php'))
						return $override_path;
					return '';
				}
			}
		}

		if(file_exists($override_path)) {
			if(is_object($mail_name)) {
				$mail_name->$override_name = true;
			}
			return $override_path;
		}

		if(!file_exists($path))
			return '';

		if($getModifiedFile)
			return $override_path;
		return $path;

	}

	public function getTemplatePath($template_name, $mail_name, $type = 'html') {
		if(empty($this->mail_folder)) {
			$config = hikashop_config();
			$this->mail_folder = rtrim( str_replace('{root}', JPATH_ROOT, $config->get('mail_folder', HIKASHOP_MEDIA.'mail'.DS)), '/\\');
			if(!empty($this->mail_folder))
				$this->mail_folder .= DS;
		}

		if(empty($this->mail_folder))
			$this->mail_folder = HIKASHOP_MEDIA.'mail'.DS;

		if(strpos($template_name, '..') !== false || (!empty($mail_name) && strpos($mail_name, '..') !== false))
			return false;

		$template_name = str_replace(array('\\', '/'), DS, $template_name);
		$mail_name = str_replace(array('\\', '/'), DS, $mail_name);

		$path = $this->mail_folder . 'template' . DS . $template_name . '.' . $type . '.modified.php';
		if(file_exists($path))
			return $path;
		$path = $this->mail_folder . 'template' . DS . $template_name . '.' . $type . '.php';
		if(file_exists($path))
			return $path;
		$path = $this->mail_folder . 'template' . DS . $template_name . '.php';
		if(file_exists($path))
			return $path;

		$external_template_files = array();
		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$app->triggerEvent('onMailTemplateListing', array(&$external_template_files, $mail_name));
		if(empty($external_template_files))
			return false;

		$mail_folder = '';
		$mail_file = '';
		foreach($external_template_files as $k => $f) {
			if($f['file'] != $template_name)
				continue;

			$mail_folder = @$f['folder'];
			if(empty($mail_folder))
				$mail_folder = $this->mail_folder . 'template' . DS;

			$mail_file = $f['filename'];
			$path = $mail_folder . $mail_file . '.' . $type . '.modified.php';
			if(file_exists($path))
				return $path;
			$path = $mail_folder . $mail_file . '.' . $type . '.php';
			if(file_exists($path))
				return $path;
			$path = $mail_folder . $mail_file . '.php';
			if(file_exists($path))
				return $path;
		}
		return false;
	}

	public function preProcessMail(&$mail){

		$config =& hikashop_config();

		$this->mailer->CharSet = $config->get('charset');
		if(empty($this->mailer->CharSet)) $this->mailer->CharSet = 'utf-8';

		$this->mailer->Encoding = $config->get('encoding_format');
		if(empty($this->mailer->Encoding)) $this->mailer->Encoding = 'base64';

		$this->mailer->WordWrap = intval($config->get('word_wrapping',0));

		$this->mailer->Sender = $this->cleanText($config->get('bounce_email'));
		if(empty($this->mailer->Sender)) $this->mailer->Sender = '';

		if(!empty($mail->dst_email)){
			if(!is_array($mail->dst_email) && strpos($mail->dst_email,',')){
				$mail->dst_email = explode(',',$mail->dst_email);
			}
			if(is_array($mail->dst_email)){
				if(HIKASHOP_J30){
					foreach($mail->dst_email as $dst_mail){
						$this->mailer->addRecipient($dst_mail);
					}
				}else{
					$this->mailer->addRecipient($mail->dst_email);
				}
			}else{
				$addedName = $config->get('add_names',true) ? $this->cleanText(@$mail->dst_name) : '';
				$this->mailer->AddAddress($this->cleanText($mail->dst_email),(string)$addedName);
			}
		}
		if(!empty($mail->cc_email)) {
			if(!is_array($mail->cc_email))
				$mail->cc_email = explode(',', $mail->cc_email);
			if(HIKASHOP_J30){
				foreach($mail->cc_email as $cc_email){
					$this->mailer->addCC($cc_email);
				}
			}else{
				$this->mailer->addCC($mail->cc_email);
			}
		}
		if(!empty($mail->bcc_email)) {
			if(!is_array($mail->bcc_email))
				$mail->bcc_email = explode(',', $mail->bcc_email);
			if(HIKASHOP_J30){
				foreach($mail->bcc_email as $bcc_email){
					$this->mailer->addBCC($bcc_email);
				}
			}else{
				$this->mailer->addBCC($mail->bcc_email);
			}
		}
		$this->setFrom($mail->from_email,@$mail->from_name);
		if(!empty($mail->reply_email)){
			$replyToName = $config->get('add_names',true) ? $this->cleanText(@$mail->reply_name) : '';
			if(HIKASHOP_J30){
				$this->mailer->addReplyTo($this->cleanText($mail->reply_email),$replyToName);
			}else{
				$this->mailer->addReplyTo(array($this->cleanText($mail->reply_email),$replyToName));
			}

		}

		$this->processMailSubject($mail);

		$this->mailer->setSubject($mail->subject);
		$this->mailer->IsHTML(@$mail->html);
		if(!empty($mail->html)){
			if(empty($mail->full_body)) {
				$headers = array(
					'<meta http-equiv="Content-Type" content="text/html; charset='.$this->mailer->CharSet.'">',
					'<title>'.$mail->subject.'</title>',
				);
				if(isset($mail->htmlStyle)) {
					$headers[] = '<style type="text/css">'."\r\n".$mail->htmlStyle."\r\n".'</style>';
				}
				$htmlExtra = '';
				$lang = JFactory::getLanguage();
				if($lang->isRTL()) {
					$htmlExtra = ' dir="rtl"';
				}

				$mail->body = preg_replace_callback('#class="w([0-9]+)[a-z_ ]*"(.+?)(?=>)#i', function ($matches){
					if(strpos($matches[2], 'width') !== false)
						return $matches[0];
					return 'width="'.$matches[1].'" '.$matches[0];
				}, $mail->body);

				$favicon = $config->get('email_favicon');
				if(!empty($favicon)) {
					$headers[] = '<link rel="Shortcut Icon" href="'.$favicon.'" />';
				}

				$headers[] = '<meta name="viewport" content="width=device-width, initial-scale=1.0">';

				if(!empty($mail->extraHeaders) && count($mail->extraHeaders)) {
					$headers = array_merge($headers, $mail->extraHeaders);
				}

				$this->mailer->Body = '<html><head>'.
					implode('', $headers).
					'</head><body class="hikashop_mail"'.$htmlExtra.'>'.hikashop_absoluteURL($mail->body).'</body></html>';
			}else{
				$this->mailer->Body = $mail->body;
			}
			if($config->get('multiple_part',false)){
				if(empty($mail->altbody)){
					$this->mailer->AltBody = $this->textVersion($mail->body);
				}else{
					$this->mailer->AltBody = $mail->altbody;
				}
			}
		}else{
			if(empty($mail->altbody)&&!empty($mail->body))
				$mail->altbody = $this->textVersion($mail->body);
			$this->mailer->Body = $mail->altbody;
		}

		if(empty($mail->attachments) && !empty($mail->mail_name)) {
			$mail->attachments = $this->loadAttachments($mail->mail_name);
		}

		if(!empty($mail->attachments)){
			if($config->get('embed_files') || $this->_force_embed){
				foreach($mail->attachments as $attachment){
					$this->mailer->AddAttachment($attachment->filename);
				}
			}else{
				$attachStringHTML = '<br/><fieldset><legend>'.JText::_( 'ATTACHMENTS' ).'</legend><table>';
				$attachStringText = "\n"."\n".'------- '.JText::_( 'ATTACHMENTS' ).' -------';
				foreach($mail->attachments as $attachment){
					$attachStringHTML .= '<tr><td><a href="'.$attachment->url.'" target="_blank">'.$attachment->name.'</a></td></tr>';
					$attachStringText .= "\n".'-- '.$attachment->name.' ( '.$attachment->url.' )';
				}
				$attachStringHTML .= '</table></fieldset>';
				if(@$mail->html){
					$this->mailer->Body = str_replace('</body></html>',$attachStringHTML.'</body></html>',$this->mailer->Body);
					if(!empty($this->mailer->AltBody))
						$this->mailer->AltBody .= "\n".$attachStringText;
				}else{
					$this->mailer->Body .= $attachStringText;
				}
			}
		}

		JPluginHelper::importPlugin('hikashop');
		JPluginHelper::importPlugin('hikashopshipping');
		JPluginHelper::importPlugin('hikashoppayment');
		$app = JFactory::getApplication();
		$app->triggerEvent('onBeforeMailSend', array(&$mail, &$this->mailer) );
	}

	public function sendMail(&$mail) {
		if(empty($mail))
			return false;
		if(isset($mail->published) && !$mail->published)
			return true;

		$do = true;
		JPluginHelper::importPlugin('hikashop');
		JPluginHelper::importPlugin('hikashopshipping');
		JPluginHelper::importPlugin('hikashoppayment');
		$app = JFactory::getApplication();
		$mail->mail_success = false;
		$app->triggerEvent('onBeforeMailPrepare', array(&$mail, &$this->mailer, &$do) );
		if(!$do) {
			if($mail->mail_success == true)
				$this->mail_success = true;
			else
				$this->mail_success = false;
			return false;
		}

		$result = true;
		try {
			$this->preProcessMail($mail);

			$config = hikashop_config();
			if((bool)$config->get('embed_images',0)){
				$this->embedImages();
			}

			if(strtoupper($this->mailer->CharSet) != 'UTF-8'){
				$encodingHelper = hikashop_get('helper.encoding');
				$this->mailer->Body = $encodingHelper->change($this->mailer->Body,'UTF-8',$this->mailer->CharSet);
				$this->mailer->Subject = $encodingHelper->change($this->mailer->Subject,'UTF-8',$this->mailer->CharSet);
				if(!empty($this->mailer->AltBody))
					$this->mailer->AltBody = $encodingHelper->change($this->mailer->AltBody,'UTF-8',$this->mailer->CharSet);
			}
			$this->mailer->Body = str_replace(" ",' ',$this->mailer->Body);

			$result = $this->mailer->Send();
		} catch( Exception $e) {
			$result = false;
			if(hikashop_isClient('administrator')) {
				$app->enqueueMessage($e->getMessage(), 'error');
			}
		}
		if(!$result || !empty($result->message)) {
			$this->mail_success = false;
		}
		if(!empty($result->message)) {
		}

		$app->triggerEvent('onAfterMailSend', array(&$mail, &$this->mailer, $this->mail_success) );
		return $result;
	}

	public function processMailSubject(&$mail) {
		if(preg_match_all('#\{([-._A-Z0-9a-z]+)\}#', $mail->subject, $matches)) {
			foreach($matches[0] as $k => $match) {
				$var = $matches[1][$k];
				$val = '';
				$table = '';
				if(strpos($var, '.')) {
					list($table, $var) = explode('.', $var, 2);
					if(!empty($table) && !empty($var)){
						if(!empty($mail->data->cart->$table->$var) && is_string($mail->data->cart->$table->$var)){
							$val = $mail->data->cart->$table->$var;
						}elseif(!empty($mail->data->$table->$var) && is_string($mail->data->$table->$var)){
							$val = $mail->data->$table->$var;
						}
					}
				} elseif(!empty($var) && !empty($mail->data->cart->$var) && is_string($mail->data->cart->$var)) {
					$val = $mail->data->cart->$var;
				} elseif(!empty($var) && !empty($mail->data->$var) && is_string($mail->data->$var)) {
					$val = $mail->data->$var;
				}
				$mail->subject = str_replace($match, $val, $mail->subject);
			}
		}
	}

	public function loadAttachments($name) {
		$config =& hikashop_config();
		$attach = $config->get($name.'.attach');

		if(empty($attach)) {
			$attach = array();
			return $attach;
		}

		$attachData = hikashop_unserialize($attach);
		$uploadFolder = str_replace(array('/','\\'),DS,html_entity_decode($config->get('uploadfolder')));
		if(preg_match('#^([A-Z]:)?/.*#',$uploadFolder)) {
			if(!$config->get('embed_files')) {
				$this->_force_embed = true;
			}
			$uploadPath = str_replace(array('/','\\'),DS,$uploadFolder);
		} else {
			$uploadFolder = trim($uploadFolder,DS.' ').DS;
			$uploadPath = str_replace(array('/','\\'),DS,HIKASHOP_ROOT.$uploadFolder);
		}
		$uploadURL = HIKASHOP_LIVE.str_replace(DS,'/',$uploadFolder);
		$attach = array();
		foreach($attachData as $oneAttach) {
			$attachObj = new stdClass();
			$attachObj->name = $oneAttach->filename;
			$attachObj->filename = $uploadPath.$oneAttach->filename;
			$attachObj->url = $uploadURL.$oneAttach->filename;
			$attach[] = $attachObj;
		}

		return $attach;
	}

	public function cleanText($text) {
		return trim( preg_replace('/(%0A|%0D|\n+|\r+)/i', '', (string) $text) );
	}

	public function setFrom($email, $name = '') {
		if(!empty($email)) {
			$this->mailer->From = $this->cleanText($email);
		}

		$config =& hikashop_config();
		if(!empty($name) && $config->get('add_names', true)) {
			$this->mailer->FromName = $this->cleanText($name);
		}
	}

	public function textVersion($html) {
		$html = hikashop_absoluteURL($html);
		$html = preg_replace('# +#', ' ', $html);
		$html = str_replace(array("\n","\r","\t"), '', $html);

		$removeScript = "#< *script(?:(?!< */ *script *>).)*< */ *script *>#isU";
		$removeStyle = "#< *style(?:(?!< */ *style *>).)*< */ *style *>#isU";
		$removeStrikeTags =  '#< *strike(?:(?!< */ *strike *>).)*< */ *strike *>#iU';
		$replaceByTwoReturnChar = '#< *(h1|h2)[^>]*>#Ui';
		$replaceByStars = '#< *li[^>]*>#Ui';
		$replaceByReturnChar1 = '#< */ *(li|td|tr|div|p)[^>]*> *< *(li|td|tr|div|p)[^>]*>#Ui';
		$replaceByReturnChar = '#< */? *(br|p|h1|h2|h3|li|ul|h4|h5|h6|tr|td|div)[^>]*>#Ui';
		$replaceLinks = '/< *a[^>]*href *= *"([^"]*)"[^>]*>(.*)< *\/ *a *>/Uis';

		$text = preg_replace(array($removeScript,$removeStyle,$removeStrikeTags,$replaceByTwoReturnChar,$replaceByStars,$replaceByReturnChar1,$replaceByReturnChar,$replaceLinks),array('','','',"\n\n","\n* ","\n","\n",'${2} ( ${1} )'),$html);
		$text = str_replace(array(" ","&nbsp;"),' ',strip_tags($text));
		$text = trim(@html_entity_decode($text,ENT_QUOTES,'UTF-8'));
		$text = preg_replace('# +#',' ',$text);
		$text = preg_replace('#\n *\n\s+#',"\n\n",$text);

		return $text;
	}

	public function embedImages() {
		preg_match_all('/(src|background)="([^"]*)"/Ui', $this->mailer->Body, $images);
		$result = true;
		if(empty($images[2]))
			return $result;

		$mimetypes = array(
			'bmp'  => 'image/bmp',
			'gif'  => 'image/gif',
			'jpeg' => 'image/jpeg',
			'jpg'  => 'image/jpeg',
			'jpe'  => 'image/jpeg',
			'png'  => 'image/png',
			'tiff' => 'image/tiff',
			'tif'  => 'image/tiff'
		);
		$allimages = array();
		foreach($images[2] as $i => $url) {
			if(isset($allimages[$url]))
				continue;

			$allimages[$url] = 1;
			$path = str_replace(array(HIKASHOP_LIVE, '/'), array(HIKASHOP_ROOT, DS), $url);
			$filename = basename($url);
			$md5 = md5($filename);
			$cid = 'cid:' . $md5;
			$fileParts = explode('.', $filename);
			$ext = strtolower($fileParts[1]);

			if(!isset($mimetypes[$ext]))
				continue;

			$mimeType  = $mimetypes[$ext];
			if($this->mailer->AddEmbeddedImage($path, $md5, $filename, 'base64', $mimeType)){
				 $this->mailer->Body = preg_replace("/".$images[1][$i]."=\"".preg_quote($url, '/')."\"/Ui", $images[1][$i]."=\"".$cid."\"", $this->mailer->Body);
			} else {
				$result = false;
			}
		}
		return $result;
	}
}
