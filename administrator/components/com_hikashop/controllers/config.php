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
class ConfigController extends hikashopController {
	public function __construct($config = array()) {
		parent::__construct($config);
		$this->registerDefaultTask('config');

		$this->modify_views = array_merge($this->modify_views, array(
			'latest', 'share', 'send', 'css', 'language', 'cleanreport', 'config',
		));
		$this->display = array_merge($this->display, array(
			'seepaymentreport', 'seereport', 'checkout_newblock', 'checkout_newstep',
		));
		$this->modify = array_merge($this->modify, array(
			'savelanguage', 'savecss', 'wizard', 'checkdb', 'resetAddressFormat',
		));
	}

	public function save() {
		$this->store();
		return $this->cancel();
	}

	public function apply() {
		$this->store();
		return $this->display();
	}

	public function display($cachable = false, $urlparams = array()) {
		hikaInput::get()->set('layout', 'config');
		return parent::display();
	}

	public function cancel() {
		$this->setRedirect( hikashop_completeLink('dashboard',false,true) );
	}

	public function store($new = false) {
		$app = JFactory::getApplication();
		JSession::checkToken() || die('Invalid Token');
		$fileClass = hikashop_get('class.file');
		$source = is_array($_POST['config'])?'post':'request'; //to avoid strange bugs on some web servers where the config array might be only in one of the two global variable :/
		$formData = hikaInput::get()->{$source}->get('config', array(), 'array');
		$aclcats = hikaInput::get()->get('aclcat', array(), 'array' );
		if(!empty($aclcats)) {
			if(hikaInput::get()->getString('acl_config','all') != 'all' && !hikashop_isAllowed($formData['acl_config_manage'])) {
				$app->enqueueMessage(JText::_( 'ACL_WRONG_CONFIG' ), 'notice');
				unset($formData['acl_config_manage']);
			}
			$deleteAclCats = array();
			$unsetVars = array('manage','delete','view');
			foreach($aclcats as $oneCat) {
				if(hikaInput::get()->getString('acl_'.$oneCat) == 'all') {
					foreach($unsetVars as $oneVar) {
						unset($formData['acl_'.$oneCat.'_'.$oneVar]);
					}
					$deleteAclCats[] = $oneCat;
				}
			}
		}
		$config =& hikashop_config();

		$nameboxes = array('simplified_registration', 'product_show_modules');
		foreach($nameboxes as $namebox) {
			if(!isset($formData[$namebox])) {
				$formData[$namebox] = '';
			} elseif(is_array($formData[$namebox])) {
				$formData[$namebox] = implode(',', $formData[$namebox]);
			}
		}

		if(!isset($this->wizard)){
			if($formData['uploadsecurefolder'] == $formData['uploadfolder']){
				$app->enqueueMessage('The "Upload folder" and "Upload secure folder" settings cannot use the same path.', "error");
				return false;
			}
		}

		$status = $config->save($formData);
	 	if(!empty($deleteAclCats)) {
			$db = JFactory::getDBO();
	 		$db->setQuery("DELETE FROM `#__hikashop_config` WHERE `config_namekey` LIKE 'acl_".implode("%' OR `config_namekey` LIKE 'acl_",$deleteAclCats)."%'");
	 		$db->execute();
	 	}
		$ids = $fileClass->storeFiles('default_image',0);
		if(!empty($ids)) {
			$data = $fileClass->get($ids[0]);
			$formData['default_image']=$data->file_path;
		}
		if(hikashop_level(2)) {
			$ids = $fileClass->storeFiles('watermark',0,'watermark');
			if(!empty($ids)){
				$data = $fileClass->get($ids[0]);
				$formData['watermark']=$data->file_path;
			}
		}
		$formData['store_address']=hikaInput::get()->getRaw( 'config_store_address','');

		$this->_saveAddressFormat();

		if(!empty($formData['cart_item_limit']) && !is_numeric($formData['cart_item_limit'])){
			$formData['cart_item_limit']=0;
		}
		if(!isset($this->wizard) && !$this->_checkWorkflow($formData)){
			$app->enqueueMessage('Checkout workflow invalid. The modification is ignored. See <a style="font-size:1.2em;text-decoration:underline" href="http://www.hikashop.com/support/documentation/integrated-documentation/54-hikashop-config.html#main" target="_blank" >the documentation</a> for more information on how to configure that option.', 'error');
			unset($formData['checkout']);
			return false;
		}

		if(!isset($this->wizard)){
			if(empty($formData['category_sef_name']) && empty($formData['product_sef_name'])){
				$app->enqueueMessage('No SEF category and product names entered. Please complete at least one of these two fields. The system put back the default values');
				$formData['category_sef_name']='category';
				$formData['product_sef_name']='product';
			}
		}


		if(!empty($formData['weight_symbols'])){
			$symbols = explode(',',$formData['weight_symbols']);
			$weightHelper = hikashop_get('helper.weight');
			$possibleSymbols = array_keys($weightHelper->conversion);
			$possibleSymbols[]='l';
			$possibleSymbols[]='ml';
			$possibleSymbols[]='cl';
			$okSymbols = array();
			foreach($symbols as $k => $symbol){
				if(!in_array($symbol,$possibleSymbols)){
					$app->enqueueMessage('The weight unit "'.$symbol.'" is not in the list of possible units : '.implode(',',$possibleSymbols));
					return false;
				}else{
					$okSymbols[]=$symbol;
				}
			}
			$formData['weight_symbols'] = implode(',',$okSymbols);
		}
		if(!isset($this->wizard) && empty($formData['weight_symbols'])){
			$app->enqueueMessage('No valid weight unit entered. The system put back the default units.');
			$formData['weight_symbols']='kg,g,mg,lb,oz,ozt';
		}
		if(!empty($formData['volume_symbols'])){
			$symbols = explode(',',$formData['volume_symbols']);
			$weightHelper = hikashop_get('helper.volume');
			$possibleSymbols = array_keys($weightHelper->conversion);
			$okSymbols = array();
			foreach($symbols as $k => $symbol){
				if(!in_array($symbol,$possibleSymbols)){
					$app->enqueueMessage('The dimension unit "'.$symbol.'" is not in the list of possible units : '.implode(',',$possibleSymbols));
					return false;
				}else{
					$okSymbols[]=$symbol;
				}
			}
			$formData['volume_symbols'] = implode(',',$okSymbols);
		}
		if(!isset($this->wizard) && empty($formData['volume_symbols'])){
			$app->enqueueMessage('No valid dimension unit entered. The system put back the default units.');
			$formData['volume_symbols']='m,dm,cm,mm,in,ft,yd';
		}
		if(!isset($this->wizard) && $formData['force_ssl']=='url' && empty($formData['force_ssl_url'])){
			$formData['force_ssl']='no';
			$app->enqueueMessage('No ssl url specified, force ssl parameter setted to no');
		}

		if(!empty($formData['order_number_format']))
			$formData['order_number_format']=str_replace('&quot;}"','"}',$formData['order_number_format']);

		$config =& hikashop_config();
		$status = $config->save($formData);
		if($status){
			$app->enqueueMessage(JText::_( 'HIKASHOP_SUCC_SAVED' ));
		}else{
			$app->enqueueMessage(JText::_( 'ERROR_SAVING' ), 'error');
		}

		$pluginsClass = hikashop_get('class.plugins');

		$paramsPlugins = hikaInput::get()->get('params', array(), 'array');
		foreach($paramsPlugins as $group => $paramsPluginsOneGroup){
			foreach($paramsPluginsOneGroup as $name => $paramsPlugin){
				$plugin = $pluginsClass->getByName($group,$name);
				if(!empty($plugin)){
					$plugin->params = $paramsPlugin;
					$pluginsClass->save($plugin);
				}
			}
		}
		$js = '
function setVisible(value) {
	value = parseInt(value);
	var d = document, disp = (value == 1) ? "" : "none";
	d.getElementById("sef_cat_name").style.display = disp;
	d.getElementById("sef_prod_name").style.display = disp;
}';
		$doc = JFactory::getDocument();
	 	$doc->addScriptDeclaration($js);

		$config->load();
		return $status;

	}

	protected function _checkWorkflow(&$formData) {
		if(empty($formData['checkout']) && !empty($formData['checkout_workflow']))
			return true;

		if(empty($formData['checkout'])) {
			$app = JFactory::getApplication();
			$app->enqueueMessage('Your checkout workflow is empty.');
			return false;
		}
		$formData['checkout'] = trim($formData['checkout']);
		$steps = explode(',',$formData['checkout']);
		$login = false;
		$address = false;
		$ok = array('login','address','shipping','payment','confirm','coupon','cart','cartstatus','status','fields','terms','end');

		JPluginHelper::importPlugin('hikashop');
		JPluginHelper::importPlugin('hikashopshipping');
		JPluginHelper::importPlugin('hikashoppayment');
		$app = JFactory::getApplication();
		$list = array();
		$app->triggerEvent('onCheckoutStepList', array(&$list));
		if(!empty($list)) {
			foreach($list as $k => $v) {
				if(!in_array($k, $ok))
					$ok[] = $k;
			}
		}

		foreach($steps as $step){
			if(empty($step)){
				$app = JFactory::getApplication();
				$app->enqueueMessage('You have an empty step in your checkout workflow.');
				return false;
			}
			$views = explode('_',$step);
			foreach($views as $view){
				if(!in_array($view,$ok)){
					$app = JFactory::getApplication();
					$app->enqueueMessage('You have a view name which is not possible in your checkout workflow. You can only use the views: '.implode(',',$ok));
					return false;
				}
				if($view=='login') $login = true;
				if($view=='address') $address = true;
			}
		}
		if($address && !$login){
			$app = JFactory::getApplication();
			$app->enqueueMessage('You cannot have the Address view without the Login view on your checkout workflow.', 'error');
			return false;
		}
		return true;
	}

	protected function _saveAddressFormat() {
		$format = trim(hikaInput::get()->getRaw( 'config_address_format',''));
		if(empty($format))
			return;
		$config = hikashop_config();

		$originalFormat = $config->getAddressFormat(true);
		if($originalFormat == $format)
			return;

		$db = JFactory::getDBO();
		$query = "SELECT * FROM #__template_styles";
		$db->setQuery($query);
		$templates = $db->loadObjectList();
		if(empty($templates))
			return;
		jimport('joomla.filesystem.file');

		$header = "<?php
 defined('_JEXEC') or die('Restricted access');
?>
";

		$format = $header . $format;

		foreach($templates as $template){
			$path = HIKASHOP_ROOT;
			$view = 'address';
			if($template->client_id){
				$path .= 'administrator' . DS;
				$view = 'order';
			}
			$path .= 'templates' . DS . $template->template . DS . 'html' . DS . 'com_hikashop' . DS . $view . DS . 'address_template.php';
			JFile::write($path, $format);
		}
	}

	public function resetAddressFormat() {
		$db = JFactory::getDBO();
		$query = "SELECT * FROM #__template_styles";
		$db->setQuery($query);
		$templates = $db->loadObjectList();
		if(empty($templates))
			return;
		jimport('joomla.filesystem.file');
		$ret = 	array(
			'error' => '',
			'result' => true,
		);
		foreach($templates as $template){
			$path = HIKASHOP_ROOT;
			if($template->client_id){
				$path .= 'administrator' . DS;
			}
			$path .= 'templates' . DS . $template->template . DS . 'html' . DS . 'com_hikashop' . DS . 'address' . DS . 'address_template.php';
			if(!JFile::delete($path)){
				$ret['error'] .= JText::sprintf('COULD_NOT_DELETE_THE_FILE', $path);
				$ret['result'] = false;
			}
		}

		if($ret['result']){
			$ret['message'] = hikashop_display(JText::_('ADDRESS_FORMAT_RESET_SUCCESSFULLY'),'success',true);
			$config = hikashop_config();
			$ret['defaultFormat'] = trim($config->getAddressFormat(true));
		}

		ob_clean();
		echo json_encode($ret);
		exit;
	}

	public function test() {
		$app = JFactory::getApplication();
		$this->store();

		$config =& hikashop_config();
		$user = hikashop_loadUser(true);
		$mailClass = hikashop_get('class.mail');
		$addedName = $config->get('add_names',true) ? $mailClass->cleanText(@$user->name) : '';
		$true = true;
		$mail = $mailClass->get('test',$true);
		$mailClass->mailer->AddAddress($user->user_email,$addedName);
		$mail->subject = 'Test e-mail from '.HIKASHOP_LIVE;
		$mail->altbody = 'This test email confirms that your configuration enables HikaShop to send emails normally.';
		$mail->html=0;
		$mail->debug = 1;
		$result = $mailClass->sendMail($mail);
		if(!$result){
			$bounce = $config->get('bounce_email');
			if(!empty($bounce)){
				$app->enqueueMessage(JText::sprintf('ADVICE_BOUNCE',$bounce),'notice');
			}
		}
		return $this->display();
	}

	public function seepaymentreport() {
		$config =& hikashop_config();
		$path = trim(html_entity_decode($config->get('payment_log_file')));
		if(!preg_match('#^[a-z0-9/_\-]*\.log$#i', $path)) {
			hikashop_display('The log file must only contain alphanumeric characters and end with .log','error');
			return false;
		}
		$reportPath = JPath::clean(HIKASHOP_ROOT.$path);
		if(!JPath::check($reportPath)) {
			hikashop_display(JText::_('EXIST_LOG'),'info');
			return false;
		}

		$logFile = @file_get_contents($reportPath);
		if(empty($logFile)){
			hikashop_display(JText::_('EMPTY_LOG').' '.$reportPath,'info');
		} else {
			$logFile = trim($logFile);

			if(strpos($logFile, '<pre') !== false) {
				$tmp = str_replace(array("\r\n","\r","\n"), "\x02", $logFile);
				$pre = array();
				if(preg_match_all('#<pre\s?(.*)>(.*)</pre>#Ui', $tmp, $pre)) {
					foreach($pre[0] as $p) {
						$n = str_replace("\x02", "\r\n", $p);
						$tmp = str_replace($p, $n, $tmp);
						unset($n);
					}
					unset($p);
					unset($pre);
				}
				$ret = str_replace("\x02", "<br/>\r\n", $tmp);
			} else
				$ret = nl2br($logFile);

			echo $ret;
		}
	}

	public function seereport() {
		$config =& hikashop_config();
		$path = trim(html_entity_decode($config->get('cron_savepath')));
		if(!preg_match('#^[a-z0-9/_\-]*\.log$#i',$path)){
			hikashop_display('The log file must only contain alphanumeric characters and end with .log','error');
			return false;
		}
		$reportPath = JPath::clean(HIKASHOP_ROOT.$path);

		if(!JPath::check($reportPath)) {
			hikashop_display(JText::_('EXIST_LOG'),'info');
			return false;
		}
		$logFile = @file_get_contents($reportPath);
		if(empty($logFile)){
			hikashop_display(JText::_('EMPTY_LOG'),'info');
		}else{
			echo nl2br($logFile);
		}
	}

	public function cleanreport() {
		jimport('joomla.filesystem.file');
		$config =& hikashop_config();

		$path = trim(html_entity_decode($config->get('cron_savepath')));
		if(!preg_match('#^[a-z0-9/_\-]*\.log$#i',$path)){
			hikashop_display('The log file must only contain alphanumeric characters and end with .log','error');
			return false;
		}
		$reportPath = JPath::clean(HIKASHOP_ROOT.$path);

		if(!JPath::check($reportPath)) {
			hikashop_display(JText::_('EXIST_LOG'),'info');
			return false;
		}

		if(is_file($reportPath)){
			$result = JFile::delete($reportPath);
			if($result){
				hikashop_display(JText::_('SUCC_DELETE_LOG'),'success');
			}else{
				hikashop_display(JText::_('ERROR_DELETE_LOG'),'error');
			}
		}else{
			hikashop_display(JText::_('EXIST_LOG'),'info');
		}
	}

	public function language() {
		hikaInput::get()->set('layout', 'language');
		return parent::display();
	}

	public function savelanguage() {
		JSession::checkToken() || die( 'Invalid Token' );
		$this->_savelanguage();
		return $this->language();
	}

	public function latest() {
		return $this->language();
	}

	public function savecss() {
		JSession::checkToken() || die('Invalid Token');

		$file = hikaInput::get()->getCmd('file');
		if(!preg_match('#^([-_a-z0-9]*)_([-_a-z0-9]*)$#i',$file,$result)){
			hikashop_display('Could not load the file '.htmlentities($file).' properly');
			exit;
		}
		$type = $result[1];
		$fileName = JFile::makeSafe($result[2]);
		jimport('joomla.filesystem.file');
		$path = HIKASHOP_MEDIA.'css'.DS.$type.'_'.$fileName.'.css';
		if(!JPath::check($path)) {
			hikashop_display(JText::sprintf('FAIL_SAVE','invalid filename'),'error');
			return $this->css();
		}
		$csscontent = hikaInput::get()->getString('csscontent');
		$alreadyExists = file_exists($path);
		if(JFile::write($path, $csscontent)){
			$varName = hikaInput::get()->getCmd('var');
			$configName = 'css_'.$varName;
			$config =& hikashop_config();
			$newConfig = new stdClass();
			$newConfig->$configName = $fileName;
 			$config->save($newConfig);
			hikashop_display(JText::_('HIKASHOP_SUCC_SAVED'),'success');
			if(!$alreadyExists){
				$js = "var optn = document.createElement(\"OPTION\");
						optn.text = '$fileName'; optn.value = '$fileName';
						mydrop = window.top.document.getElementById('".$varName."_choice');
						mydrop.options.add(optn);
						lastid = 0; while(mydrop.options[lastid+1]){lastid = lastid+1;} mydrop.selectedIndex = lastid;";
				$doc = JFactory::getDocument();
				$doc->addScriptDeclaration( $js );
			}
		}else{
			hikashop_display(JText::sprintf('FAIL_SAVE',$path),'error');
		}
		return $this->css();
	}

	public function css() {
		hikaInput::get()->set('layout', 'css');
		return parent::display();
	}


	public function send() {
		JSession::checkToken() || die( 'Invalid Token' );
		$bodyEmail = hikaInput::get()->getString('mailbody');
		$code = hikaInput::get()->getString('code');
		hikaInput::get()->set('code',$code);
		if(empty($code)) return;
		$config =& hikashop_config();
		$user = hikashop_loadUser(true);
		$mailClass = hikashop_get('class.mail');
		$addedName = $config->get('add_names',true) ? $mailClass->cleanText(@$user->name) : '';
		$true = true;
		$mail = $mailClass->get('language',$true);
		$mailClass->mailer->AddAddress($user->user_email,$addedName);
		$mailClass->mailer->AddAddress('translate@hikashop.com','Hikashop Translation Team');
		$mail->subject = '[HIKASHOP LANGUAGE FILE] '.$code;
		$mail->altbody = 'The website '.HIKASHOP_LIVE.' using HikaShop '.$config->get('level').$config->get('version').' sent a language file : '.$code;
		$mail->altbody .= "\n"."\n"."\n".$bodyEmail;
		$mail->html=0;
		jimport('joomla.filesystem.file');
		$path = JPath::clean(hikashop_getLanguagePath(JPATH_ROOT).DS.$code.DS.$code.'.com_hikashop.ini');
		$result = parse_ini_file($path);
		if(!$result){
			hikashop_display('There is an parsing error in your language file. So the file could not be sent. Please activate the "debug language" setting of the Joomla configuration and look at the debug information at the bottom of the page. It will tell you on which line of the file you have a problem.', 'success');
			return;
		}
		$mailClass->mailer->AddAttachment($path);
		$result = $mailClass->sendMail($mail);

		if($result){
			hikashop_display(JText::_('THANK_YOU_SHARING'),'success');
		}
		$this->display();
	}

	public function share() {
		JSession::checkToken() || die('Invalid Token');

		if($this->_savelanguage()) {
			hikaInput::get()->set( 'layout', 'share' );
			return parent::display();
		}
		return $this->language();
	}

	protected function _savelanguage() {
		$code = hikaInput::get()->getString('code');
		hikaInput::get()->set('code', $code);
		if(empty($code))
			return;

		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		$content = hikaInput::get()->getRaw('content', '');
		$content_override = hikaInput::get()->getRaw('content_override', '');

		$folder = hikashop_getLanguagePath(JPATH_ROOT).DS.'overrides';
		jimport('joomla.filesystem.folder');
		if(!JFolder::exists($folder)) {
			JFolder::create($folder);
		}
		if(JFolder::exists($folder)) {
			$path = $folder.DS.$code.'.override.ini';
			if(!JPath::check($path)) {
				hikashop_display(JText::sprintf('FAIL_SAVE','invalid filename'),'error');
				return false;
			}
			$result = JFile::write($path, $content_override);
			if(!$result) {
				hikashop_display(JText::sprintf('FAIL_SAVE',$path),'error');
			}
		}

		if(empty($content))
			return;

		$path = hikashop_getLanguagePath(JPATH_ROOT).DS.$code.DS.$code.'.com_hikashop.ini';
		if(!JPath::check($path)) {
			hikashop_display(JText::sprintf('FAIL_SAVE','invalid filename'),'error');
			return false;
		}
		$result = JFile::write($path, $content);
		if($result) {
			hikashop_display(JText::_('HIKASHOP_SUCC_SAVED'),'success');
			$updateHelper = hikashop_get('helper.update');
			$updateHelper->installMenu($code);
		} else {
			hikashop_display(JText::sprintf('FAIL_SAVE',$path),'error');
		}
		return $result;
	}

	public function getUploadSetting($upload_key, $caller = '') {
		if(empty($upload_key))
			return false;

		$upload_value = null;
		$upload_keys = array(
			'default_image' => array(
				'type' => 'image',
				'field' => 'config[default_image]'
			),
			'watermark' => array(
				'type' => 'image',
				'field' => 'config[watermark]',
				'delete' => true,
				'uploader_id' => 'hikashop_config_watermark_image'
			)
		);

		if(empty($upload_keys[$upload_key]))
			return false;
		$upload_value = $upload_keys[$upload_key];

		return array(
			'limit' => 1,
			'type' => $upload_value['type'],
			'options' => array(),
			'extra' => array(
				'delete' => !empty($upload_value['delete']),
				'uploader_id' => (!empty($upload_value['uploader_id']) ? $upload_value['uploader_id'] : null),
				'field_name' => $upload_value['field']
			)
		);
	}

	public function manageUpload($upload_key, &$ret, $uploadConfig, $caller = '') {
		if(empty($ret) || empty($ret->name))
			return;

		$upload_keys = array(
			'default_image' => true,
			'watermark' => true
		);
		if(empty($upload_keys[$upload_key]))
			return;

		$data = array(
			$upload_key => $ret->name
		);
		$config = hikashop_config();
		$config->save($data);
	}

	public function checkdb() {
		$databaseHelper = hikashop_get('helper.database');
		$html = $databaseHelper->checkdb();

		hikaInput::get()->set('layout', 'checkdb');
		return parent::display();
	}

	public function checkout_newblock() {
		$name = hikaInput::get()->get('name', '');
		if(empty($name))
			exit;
		hikashop_cleanBuffers();
		$checkout_workflowType = hikashop_get('type.checkout_workflow');
		echo $checkout_workflowType->newBlock($name);
		exit;
	}

	public function checkout_newstep() {
		$num = hikaInput::get()->getInt('num', -1);
		if($num < 0)
			exit;
		hikashop_cleanBuffers();
		$checkout_workflowType = hikashop_get('type.checkout_workflow');
		echo $checkout_workflowType->newStep($num);
		exit;
	}
}
