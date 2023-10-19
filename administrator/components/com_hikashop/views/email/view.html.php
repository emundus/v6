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
class EmailViewEmail extends hikashopView
{
	var $type = '';
	var $ctrl= 'email';
	var $nameListing = 'EMAILS';
	var $nameForm = 'EMAILS';
	var $icon = 'envelope';
	var $triggerView = true;

	public function display($tpl = null) {
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		if(method_exists($this,$function))
			$this->$function();
		return parent::display($tpl);
	}

	public function form() {

		$main_page_array = array('payment_notification','order_admin_notification','order_creation_notification','order_status_notification','order_notification');


		$var_array = array(
			'order' => array(
				'HIKA_DISPLAY_ORDER_ID'=>'order_id',
				'HIKA_DISPLAY_ORDER_STATUS'=>'order_status',
				'HIKA_DISPLAY_ORDER_NUMBER'=>'order_number',
				'HIKA_DISPLAY_ORDER_INVOICE_NUMBER'=>'order_invoice_number',
				'HIKA_DISPLAY_ORDER_FULL_PRICE'=>'order_full_price',
				'HIKA_DISPLAY_ORDER_DISC_CODE'=>'order_discount_code',
				'HIKA_DISPLAY_ORDER_PRICE'=>'order_discount_price',
				'HIKA_DISPLAY_ORDER_DISCOUNT_TAX'=>'order_discount_tax',
				'HIKA_DISPLAY_ORDER_PAYMENT_METH'=>'order_payment_method',
				'HIKA_DISPLAY_ORDER_PAYMENT_PRICE'=>'order_payment_price',
				'HIKA_DISPLAY_ORDER_PAYMENT_TAX'=>'order_payment_tax',
				'HIKA_DISPLAY_ORDER_SHIPPING_METH'=>'order_shipping_method',
				'HIKA_DISPLAY_ORDER_SHIPPING_PRICE'=>'order_shipping_price',
				'HIKA_DISPLAY_ORDER_SHIPPING_TAX'=>'order_shipping_tax',
				'HIKA_DISPLAY_ORDER_PARTNER_PRICE'=>'order_partner_price',
				'HIKA_DISPLAY_ORDER_PARTNER_PAID'=>'order_partner_paid',
				'HIKA_DISPLAY_ORDER_LANG'=>'order_lang'
			),
			'order_field' =>  array(
				'<p>'.JText::_('HIKA_EMAIL_TAG_ORDER_PRE_TEXT').'</p>
				<p>{VAR:<span class="hikashop_mail_tag_key_table">order.</span><span class="hikashop_mail_tag_key_column">XXX</span>}</p>
				<p>'.JText::_('HIKA_EMAIL_TAG_ORDER_POST_TEXT').'</p>'
			),
			'customer' => array(
				'HIKA_DISPLAY_USER_MAIL'=>'user_email',
				'HIKA_DISPLAY_USER_POINTS'=>'user_points',
				'HIKA_DISPLAY_USER_NAME'=>'name',
				'HIKA_DISPLAY_USER_USERNAME'=>'username',
				'HIKA_DISPLAY_USER_EMAIL'=>'email',
				'HIKA_DISPLAY_USER_PASSWORD'=>'password',
				'HIKA_DISPLAY_USER_REGISTERDATE'=>'registerDate',
				'HIKA_DISPLAY_USER_LASTVISIT'=>'lastvisitDate'
			),
			'customer_field' =>  array(
				'<p>'.JText::_('HIKA_EMAIL_TAG_ORDER_PRE_TEXT').'</p>
				<p>{VAR:<span class="hikashop_mail_tag_key_table">customer.</span><span class="hikashop_mail_tag_key_column">XXX</span>}</p>
				<p>'.JText::_('HIKA_EMAIL_TAG_ORDER_POST_TEXT').'</p>'
			),
			'billing_address' => array(
				'<p>'.JText::_('HIKA_EMAIL_TAG_BILLING_ADDRESS_PRE_TEXT').'</p>
				<p>{VAR:<span class="hikashop_mail_tag_key_table">billing_address.</span><span class="hikashop_mail_tag_key_column">address_street</span>}</p>
				<p>'.JText::sprintf('HIKA_EMAIL_TAG_BILLING_ADDRESS_POST_TEXT', 'address_street').'</p>'
			),
			'shipping_address' => array(
				'<p>'.JText::_('HIKA_EMAIL_TAG_SHIPPING_ADDRESS_PRE_TEXT').'</p>
				<p>{VAR:<span class="hikashop_mail_tag_key_table">shipping_address.</span><span class="hikashop_mail_tag_key_column">address_street</span>}</p>
				<p>'.JText::sprintf('HIKA_EMAIL_TAG_SHIPPING_ADDRESS_POST_TEXT', 'address_street').'</p>'
			),
		);
		$linevar_array = array(
			'item' => array(
				'HIKA_DISPLAY_ITEM_ID'=>'product_id',
				'HIKA_DISPLAY_ITEM_QUANTITY'=>'order_product_quantity',
				'HIKA_DISPLAY_ITEM_NAME'=>'order_product_name',
				'HIKA_DISPLAY_ITEM_CODE'=>'order_product_code',
				'HIKA_DISPLAY_ITEM_PRICE'=>'order_product_price',
				'HIKA_DISPLAY_ITEM_TAX'=>'order_product_tax',
				'HIKA_DISPLAY_ITEM_SHIPPING_METH'=>'order_product_shipping_method',
				'HIKA_DISPLAY_ITEM_SHIPPING_PRICE'=>'order_product_shipping_price',
				'HIKA_DISPLAY_ITEM_SHIPPING_TAX'=>'order_product_shipping_tax',
				'HIKA_DISPLAY_ITEM_STATUS'=>'order_product_status'
			),
			'item_field' =>  array(
				'<p>'.JText::_('HIKA_EMAIL_TAG_ORDER_PRE_TEXT').'</p>
				<p>{LINEVAR:<span class="hikashop_mail_tag_key_table">item.</span><span class="hikashop_mail_tag_key_column">XXX</span>}</p>
				<p>'.JText::_('HIKA_EMAIL_TAG_ORDER_POST_TEXT').'</p>'
			),
			'product' => array(
				'HIKA_DISPLAY_PRODUCT_NAME'=>'product_name',
				'HIKA_DISPLAY_PRODUCT_DESC'=>'product_description',
				'HIKA_DISPLAY_PRODUCT_STOCK'=>'product_quantity',
				'HIKA_DISPLAY_PRODUCT_CODE'=>'product_code',
				'HIKA_DISPLAY_PRODUCT_PUBLISHED'=>'product_published',
				'HIKA_DISPLAY_PRODUCT_HIT'=>'product_hit',
				'HIKA_DISPLAY_PRODUCT_URL'=>'product_url',
				'HIKA_DISPLAY_PRODUCT_WEIGHT'=>'product_weight',
				'HIKA_DISPLAY_PRODUCT_KEYWORD'=>'product_keywords',
				'HIKA_DISPLAY_PRODUCT_WEIGHT_UNIT'=>'product_weight_unit',
				'HIKA_DISPLAY_PRODUCT_DESC_META'=>'product_meta_description',
				'HIKA_DISPLAY_PRODUCT_DIM_UNIT'=>'product_dimension_unit',
				'HIKA_DISPLAY_PRODUCT_WIDTH'=>'product_width',
				'HIKA_DISPLAY_PRODUCT_LENGTH'=>'product_length',
				'HIKA_DISPLAY_PRODUCT_HEIGTH'=>'product_height',
				'HIKA_DISPLAY_PRODUCT_MAX_ORDER'=>'product_max_per_order',
				'HIKA_DISPLAY_PRODUCT_MIN_ORDER'=>'product_min_per_order',
				'HIKA_DISPLAY_PRODUCT_SALES'=>'product_sales',
				'HIKA_DISPLAY_PRODUCT_AVERAGE_SC'=>'product_average_score',
				'HIKA_DISPLAY_PRODUCT_TOTAL_VOTE'=>'product_total_vote',
				'HIKA_DISPLAY_PRODUCT_PAGE_TITLE'=>'product_page_title',
				'HIKA_DISPLAY_PRODUCT_ALIAS'=>'product_alias',
				'HIKA_DISPLAY_PRODUCT_MSRP'=>'product_msrp',
				'HIKA_DISPLAY_PRODUCT_CANON'=>'product_canonical',
				'HIKA_DISPLAY_PRODUCT_SORT_PRICE'=>'product_sort_price',
			),
			'product_field' =>  array(
				'<p>'.JText::_('HIKA_EMAIL_TAG_ORDER_PRE_TEXT').'</p>
				<p>{LINEVAR:<span class="hikashop_mail_tag_key_table">product.</span><span class="hikashop_mail_tag_key_column">XXX</span>}</p>
				<p>'.JText::_('HIKA_EMAIL_TAG_ORDER_POST_TEXT').'</p>'
			)
		);
		$tag_documentation = array(
			'main_page_array' => $main_page_array,
			'var_array' => $var_array,
			'linevar_array' => $linevar_array
		);

		$config = hikashop_config();

		$mail_name = hikaInput::get()->getString('mail_name');
		$this->assignRef('mail_name', $mail_name);
		$this->assignRef('tag_documentation', $tag_documentation);
		$this->popupHelper = hikashop_get('helper.popup');

		$emailtemplateType = hikashop_get('type.emailtemplate');
		$this->assignRef('emailtemplateType', $emailtemplateType);
		$data = true;
		$mailClass = hikashop_get('class.mail');
		$mail = $mailClass->get($mail_name, $data);

		if(empty($mail)) {
			$mail->from_name = ''; // $config->get('from_name');
			$mail->from_email = ''; // $config->get('from_email');
			$mail->reply_name = ''; // $config->get('reply_name');
			$mail->reply_email = ''; // $config->get('reply_email');
			$mail->subject = '';
			$mail->html = 1;
			$mail->published = 1;
			$mail->body = '';
			$mail->altbody = '';
			$mail->preload = '';
			$mail->mail = $mail_name;
			$mail->email_log_published = 1;
		}

		$mail->default_values = new stdClass();

		$config_values = array('from_name', 'from_email', 'reply_name', 'reply_email');
		foreach($config_values as $k) {
			$mail->default_values->$k = $config->get($k);
			if($mail->$k === $mail->default_values->$k)
				$mail->$k = '';
		}

		$this->assignRef('mail', $mail);

		$values = new stdClass();
		$values->maxupload = (hikashop_bytes(ini_get('upload_max_filesize')) > hikashop_bytes(ini_get('post_max_size'))) ? ini_get('post_max_size') : ini_get('upload_max_filesize');
		$this->assignRef('values',$values);

		$email_history_plugin = JPluginHelper::getPlugin('hikashop', 'email_history');
		$this->assignRef('email_history_plugin', $email_history_plugin);
		$this->config = hikashop_config();

		$this->loadRef(array(
			'toggleClass' => 'helper.toggle',
			'editor' => 'helper.editor',
			'uploaderType' => 'type.uploader',
			'popup' => 'helper.popup',
		));

		$js = '
function updateEditor(htmlvalue) {
	var el = document.querySelector(".hikashop_mail_edit_html");
	if(!el) return;
	el.style.display = (htmlvalue == "0") ? "none" : "block";
}
window.hikashop.ready(function(){ updateEditor('.$mail->html.'); });';

		$script = '
function addFileLoader() {
	var divfile = window.document.getElementById("loadfile");
	var input = document.createElement("input");
	input.type = "file";
	input.size = "30";
	input.name = "attachments[]";
	divfile.appendChild(document.createElement("br"));
	divfile.appendChild(input);
}
function submitbutton(pressbutton) {
	if(pressbutton == "cancel") {
		submitform( pressbutton );
		return;
	}
	if(window.document.getElementById("subject").value.length < 2) {
		alert("'.JText::_('ENTER_SUBJECT',true).'");
		return false;
	}
	submitform(pressbutton);
}
';
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration( $js . $script );

		if(!empty($mail->attach)) {
			$upload_dir = $config->get('uploadsecurefolder');
			$upload_dir = rtrim(JPath::clean(html_entity_decode($upload_dir)), DS.' ').DS;
			if(!preg_match('#^([A-Z]:)?/.*#',$upload_dir) && (substr($upload_dir, 0, 1) != '/' || !is_dir($upload_dir))) {
				$upload_dir = JPath::clean(HIKASHOP_ROOT.DS.trim($upload_dir, DS.' ').DS);
			}
			foreach($mail->attach as $k => &$v) {
				$v->file_name = $v->filename;
				$v->file_path = $v->filename;
				$v->file_size = @filesize($upload_dir . $v->filename);
				$v->delete = true;
			}
		}

		if(hikaInput::get()->getString('tmpl') != 'component') {

			$this->toolbar = array(
				array('name' => 'group', 'buttons' => array( 'apply', 'save')),
				'cancel',
				'|',
				array('name' => 'pophelp', 'target' => $this->ctrl.'-form')
			);

			$previewMaker = $mailClass->getPreviewMaker($mail_name);

			if($previewMaker) {
				$url = 'index.php?option=com_hikashop&ctrl=email&task=preview&tmpl=component&mail_name='.$mail_name;
				array_unshift( $this->toolbar, array('name' => 'popup','icon'=>'mail','alt'=>JText::_('PREVIEW'),'url'=>$url) );
			}

			hikashop_setTitle(JText::_($this->nameForm),$this->icon,$this->ctrl.'&task=edit&mail_name='.$mail_name);
		}
	}


	public function diff() {
		$mail_name = hikaInput::get()->getString('mail_name');
		$type = hikaInput::get()->getString('type');
		$data = true;
		$mailClass = hikashop_get('class.mail');
		$this->element = $mailClass->get($mail_name, $data);

		$this->override_path_name = $type.'_override_path';
		$this->path_name = $type.'_path';
		$this->override_name = $type.'_override';

		$this->diffInc = hikashop_get('inc.diff');

		$this->toolbar = array(
			'cancel',
		);

		hikashop_setTitle(JText::_('HIKASHOP_MODIFICATIONS'),$this->icon,$this->ctrl.'&task=diff&mail_name='.$mail_name.'&type='.$type);

	}

	function preview() {

		$this->mail_name = hikaInput::get()->getCmd('mail_name');
		$this->mailClass = hikashop_get('class.mail');
		$this->previewMaker = $this->mailClass->getPreviewMaker($this->mail_name);

		if(!$this->previewMaker)
			return;

		$type = $this->mail_name;
		if(!empty($this->previewMaker->type))
			$type = $this->previewMaker->type;
		$this->formData = hikaInput::get()->getVar('data', array(), 'array');
		$app = JFactory::getApplication();
		if(empty($this->formData))
			$this->formData = $app->getUserState('email_preview_'.$type);
		else
			$app->setUserState('email_preview_'.$type, $this->formData);

		if(method_exists($this->previewMaker, 'prepareMail')) {
			$this->mail = $this->previewMaker->prepareMail($this->formData);
		} else {
			$data = null;
			if($method_exists($this->previewMaker, 'prepareData'))
				$data = $this->previewMaker->prepareData($this->formData);

			$this->mail = $this->mailClass->get($this->mail_name, $data);
			if(method_exists($this->previewMaker, 'beforeDisplay'))
				$this->previewMaker->beforeDisplay($this->mail);
		}

		if($this->mail)
			$this->mailClass->preProcessMail($this->mail);
	}


	public function listing() {
		$app = JFactory::getApplication();
		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$config =& hikashop_config();

		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.".search", 'search', '', 'string' );

		$pageInfo->limit->value = $app->getUserStateFromRequest($this->paramBase.'.limit', 'limit', $app->getCfg('list_limit'), 'int');
		$pageInfo->limit->start = $app->getUserStateFromRequest($this->paramBase.'.limitstart', 'limitstart', 0, 'int');
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order',	'a.user_id','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir',	'desc',	'word' );

		jimport('joomla.filesystem.file');
		$mail_folder = rtrim( str_replace( '{root}', JPATH_ROOT, $config->get('mail_folder',HIKASHOP_MEDIA.'mail'.DS)), '/\\').DS;

		$mailClass = hikashop_get('class.mail');
		$files = $mailClass->getFiles();

		$emails = array();
		foreach($files as $file){
			$folder = $mail_folder;
			$filename = $file;

			$email = new stdClass();

			if(is_array($file)) {
				$folder = $file['folder'];
				if(!empty($file['name']))
					$email->name = $file['name'];
				$filename = $file['filename'];
				$file = $file['file'];
			}

			$email->file = $file;
			$email->overriden_text = JFile::exists($folder.$filename.'.text.modified.php');
			$email->overriden_html = JFile::exists($folder.$filename.'.html.modified.php');
			$email->overriden_preload = JFile::exists($folder.$filename.'.preload.modified.php');
			$email->published = $config->get($file.'.published');
			$emails[] = $email;
		}

		$pageInfo->elements = new stdClass();
		$pageInfo->elements->total = count($emails);

		if(!empty($pageInfo->limit->value))
			$emails = array_slice($emails, $pageInfo->limit->start, $pageInfo->limit->value);
		$pageInfo->elements->page = count($emails);

		$this->assignRef('rows',$emails);
		$this->assignRef('pageInfo',$pageInfo);
		hikashop_setTitle(JText::_($this->nameListing),$this->icon,$this->ctrl);
		$this->getPagination();

		$this->toolbar = array(
			array('name' => 'pophelp', 'target' => $this->ctrl.'-listing'),
			'dashboard'
		);

		$manage = true; $delete = false;
		$manage = hikashop_isAllowed($config->get('acl_email_manage','all'));
		$delete = hikashop_isAllowed($config->get('acl_email_delete','all'));

		jimport('joomla.client.helper');
		$ftp = JClientHelper::setCredentialsFromRequest('ftp');
		$this->assignRef('ftp', $ftp);
		$this->assignRef('manage',$manage);
		$this->assignRef('delete',$delete);

		$toggle = hikashop_get('helper.toggle');
		$this->assignRef('toggleClass', $toggle);
	}

	public function emailtemplate() {
		$mailClass = hikashop_get('class.mail');

		$email_name = hikaInput::get()->getCmd('email_name');
		$file = hikaInput::get()->getCmd('file', '');
		$content = hikaInput::get()->getRaw('templatecontent', '');

		$filename = '';
		if(!empty($file))
			$filename = $mailClass->getTemplatePath($file, $email_name);

		if(!empty($file) && empty($content) && !empty($filename) && file_exists($filename))
			$content = file_get_contents($filename);

		if(empty($file)) {
			$i = 1;
			$file = 'custom_';
			$filename = JPath::clean(HIKASHOP_MEDIA.'mail'.DS.'template'.DS.$file.'.html.modified.php');
			while(file_exists($filename)) {
				$file = 'custom_'.$i;
				$filename = JPath::clean(HIKASHOP_MEDIA.'mail'.DS.'template'.DS.$file.'.html.modified.php');
				$i++;
			}
		}

		$this->assignRef('content', $content);
		$this->assignRef('fileName', $file);
		$this->assignRef('email_name', $email_name);
		$editor = hikashop_get('helper.editor');
		$this->assignRef('editor', $editor);
	}

	public function orderstatus() {
		$mailClass = hikashop_get('class.mail');

		$this->email_name = hikaInput::get()->getCmd('email_name');
		$this->type = hikaInput::get()->getCmd('type', '');
		$this->order_status = hikaInput::get()->getString('order_status', '');
		$this->content = hikaInput::get()->getRaw('emailcontent', '');

		if(empty($this->email_name) || $this->email_name != 'order_status_notification')
			return;

		if(empty($this->content)) {
			$path = $mailClass->getMailPath($this->email_name, $this->type);
			if(!empty($this->order_status)) {
				$name = $this->email_name.'.'.$this->order_status;
				jimport('joomla.filesystem.file');
				$name = JFile::makeSafe($name);
				$pathWithOrderStatus = $mailClass->getMailPath($name, $this->type);
				if(!empty($pathWithOrderStatus))
					$path = $pathWithOrderStatus;
			}
			if(!empty($path))
				$this->content = file_get_contents($path);
		}

		$this->editor = hikashop_get('helper.editor');
		$this->order_statusType = hikashop_get('type.order_status');
	}
}
