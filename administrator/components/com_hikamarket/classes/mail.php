<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class hikamarketMailClass extends hikamarketClass {

	protected $tables = array();
	protected $pkeys = array();
	protected $toggle = array();

	private $orderEmails = array(
		'order_admin_notification' => 1,
		'order_cancel' => 0,
		'order_creation_notification' => 0,
		'order_notification' => 0,
		'order_status_notification' => 0
	);

	public function  __construct($config = array()){
		$marketConfig = hikamarket::config();
		return parent::__construct($config);
	}

	public function load($name, &$data) {
		$shopMailClass = hikamarket::get('shop.class.mail');
		$shopMailClass->mailer = JFactory::getMailer();
		$shopMailClass->mail_folder = HIKAMARKET_MEDIA . 'mail' . DS;

		if(substr($name, 0, 7) == 'market.')
			$name = substr($name, 7);

		$mail = new stdClass();
		$mail->mail_name = $name;
		$shopMailClass->loadInfos($mail, 'market.'.$name);

		$mail->body = $shopMailClass->loadEmail($mail, $data);
		$mail->altbody = $shopMailClass->loadEmail($mail, $data, 'text');
		$mail->preload = $shopMailClass->loadEmail($mail, $data, 'preload');
		$mail->data =& $data;
		$mail->mailer =& $shopMailClass->mailer;
		if($data !== true)
			$mail->body = hikamarket::absoluteURL($mail->body);
		if(empty($mail->altbody) && $data !== true)
			$mail->altbody = $shopMailClass->textVersion($mail->body);

		return $mail;
	}

	public function sendMail(&$mail) {
		$shopMailClass = hikamarket::get('shop.class.mail');
		return $shopMailClass->sendMail($mail);
	}

	public function cleanEmail($text) {
		return trim(preg_replace('/(%0A|%0D|\n+|\r+)/i', '', (string)$text));
	}

	public function beforeMailPrepare(&$mail, &$mailer, &$do) {
		$mail_name = $mail->mail_name;
		if(isset($mail->hikamarket) && !empty($mail->hikamarket)) {
			$mail_name = 'market.' . $mail_name;

			if(empty($mail->attachments)) {
				$shopMailClass = hikamarket::get('shop.class.mail');
				$mail->attachments = $shopMailClass->loadAttachments($mail_name);
			}
		}

		if(isset($this->orderEmails[$mail_name]))
			return $this->processOrderEmail($mail, $mailer, $do);

		if($mail_name == 'contact_request')
			return $this->processContactMail($mail, $mailer, $do);

		if($mail_name == 'new_comment')
			return $this->processCommentMail($mail, $mailer, $do);
	}

	public function processMailTemplate(&$mail, &$data, &$content, &$vars, &$texts, &$templates) {
		$mail_name = $mail->mail_name;
		if(isset($mail->hikamarket) && !empty($mail->hikamarket))
			$mail_name = 'market.' . $mail_name;

		if(isset($this->orderEmails[$mail_name]))
			return $this->processOrdernotificationTemplate($mail, $data, $content, $vars, $texts, $templates);
		if($mail_name == 'contact_request')
			return $this->processContactrequestTemplate($mail, $data, $content, $vars, $texts, $templates);
	}

	public function sendVendorOrderEmail(&$order) {
		if(empty($order->order_vendor_id) && empty($order->old->order_vendor_id))
			return false;
		if(!empty($order->hikamarket->vendor)) {
			$vendor =& $order->hikamarket->vendor;
		} else {
			$vendor_id = !empty($order->order_vendor_id) ? (int)$order->order_vendor_id : (int)$order->old->order_vendor_id;
			$vendorClass = hikamarket::get('class.vendor');
			$vendor = $vendorClass->get($vendor_id);
		}

		if(empty($vendor) || empty($vendor->vendor_email) || filter_var($vendor->vendor_email, FILTER_VALIDATE_EMAIL) === false)
			return false;

		$order->vendor =& $vendor;
		if(empty($order->customer)) {
			$order_user_id = !empty($order->order_user_id) ? (int)$order->order_user_id : (int)$order->old->order_user_id;
			$userClass = hikamarket::get('shop.class.user');
			$order->customer = $userClass->get($order_user_id);
		}

		if(empty($order->mail_status))
			$order->mail_status = hikamarket::orderStatus(@$order->order_status);
		else
			$order->mail_status = hikamarket::orderStatus($order->mail_status);

		$user_cms_id = (int)$order->customer->user_cms_id;

		$mail = $this->load('order_status_notification', $order);
		$mail->hikamarket = true;

		if(empty($mail->subject))
			$mail->subject = 'MARKET_ORDER_STATUS_NOTIFICATION_SUBJECT';

		$order_number = isset($order->order_number) ? $order->order_number : @$order->old->order_number;
		$mail_subject = JText::sprintf($mail->subject, $order_number, $order->mail_status, HIKASHOP_LIVE);


		if(empty($mail) || empty($mail->published))
			return false;

		$mail->dst_email = $vendor->vendor_email;
		$mail->dst_name = $vendor->vendor_name;

		$this->setVendorNotifyEmails($mail, $vendor);
		if(empty($mail->dst_email))
			return;

		$mail->subject = $mail_subject;
		$ret = $this->sendMail($mail);

		return $ret;
		return false;
	}

	public function sendVendorPaymentEmail(&$order, &$vendor) {
		if(empty($vendor) || empty($vendor->vendor_email) || filter_var($vendor->vendor_email, FILTER_VALIDATE_EMAIL) === false)
			return false;

		$data = new stdClass();
		$data->order_id = (int)$order->order_id;
		$data->mail_status = $order->order_status;
		$data->order = $order;
		$data->vendor = $vendor;

		if(empty($data->order->customer)) {
			$userClass = hikamarket::get('shop.class.user');
			$data->order->customer = $userClass->get($vendor->vendor_admin_id);
		}

		$mail = $this->load('vendor_payment_notification', $data);
		if(empty($mail) || !$mail->published)
			return false;

		$mail->hikamarket = true;

		$mail->dst_email = $vendor->vendor_email;
		$mail->dst_name = $vendor->vendor_name;
		if(empty($mail->dst_email))
			return;

		if(empty($mail->subject))
			$mail->subject = 'MARKET_VENDOR_PAYMENT_NOTIFICATION_SUBJECT';
		$mail->subject = JText::sprintf($mail->subject, HIKASHOP_LIVE);
		$ret = $this->sendMail($mail);

		return $ret;
		return false;
	}

	public function setVendorNotifyEmails(&$mail, $vendor) {
		$vendor_access = $vendor->vendor_access;
		if(empty($vendor_access)) {
			$config = hikamarket::config();
			$vendor_access = $config->get('store_default_access', 'all');
		}
		if($vendor_access == 'all')
			$vendor_access = '*';
		$vendor_access = explode(',', trim(strtolower($vendor_access), ','));
		sort($vendor_access, SORT_STRING);

		if(!hikamarket::aclTest('order/notify', $vendor_access))
			return;

		$query = 'SELECT hku.*, ju.* '.
				' FROM '.hikamarket::table('user','shop').' AS hku '.
				' LEFT JOIN '.hikamarket::table('users',false).' AS ju ON hku.user_cms_id = ju.id '.
				' WHERE hku.user_vendor_id = ' . (int)$vendor->vendor_id . ' ORDER BY hku.user_id';
		$this->db->setQuery($query);
		$users = $this->db->loadObjectList('user_id');

		$config = hikamarket::config();
		if((int)$config->get('user_multiple_vendor', 0)) {
			$query = 'SELECT hku.*, ju.*, vu.user_access as vendor_user_access ' .
				' FROM ' .hikamarket::table('vendor_user') . ' AS vu ' .
				' INNER JOIN '.hikamarket::table('shop.user').' AS hku ON (hku.user_id = vu.user_id)' .
				' LEFT JOIN '.hikamarket::table('users',false).' AS ju ON hku.user_cms_id = ju.id '.
				' WHERE vu.vendor_id = ' . (int)$vendor->vendor_id . ' ORDER BY hku.user_id';
			$this->db->setQuery($query);
			$extra_users = $this->db->loadObjectList('user_id');
		}

		if(empty($users) && empty($extra_users))
			return;

		if(!empty($extra_users))
			$users = empty($users) ? $extra_users : array_merge($users, $extra_users);

		foreach($users as $user) {
			if((is_string($mail->dst_email) && $user->user_email == $mail->dst_email) || (is_array($mail->dst_email) && in_array($user->user_email, $mail->dst_email)))
				continue;
			if(!empty($user->vendor_user_access))
				$user->user_vendor_access = $user->vendor_user_access;
			if(empty($user->user_vendor_access))
				continue;

			if($user->user_vendor_access == 'all')
				$user->user_vendor_access = '*';
			$user_access = explode(',', trim(strtolower($user->user_vendor_access), ','));
			sort($user_access, SORT_STRING);

			$ret = hikamarket::aclTest('order/notify', $user_access);
			if($ret) {
				if(!is_array($mail->dst_email)) $mail->dst_email = ( !empty($mail->dst_email) ? array($mail->dst_email) : array() );
				if(!is_array($mail->dst_name)) $mail->dst_name = ( !empty($mail->dst_name) ? array($mail->dst_name) : array() );

				$mail->dst_email[] = $user->user_email;
				$mail->dst_name[] = $user->username;
			}
		}
	}

	protected function loadLocale($user_cms_id) {
		return true;

		$locale = '';
		if(!empty($user_cms_id)) {
			$user = JFactory::getUser($user_cms_id);
			$locale = $user->getParam('language');
			if(empty($locale))
				$locale = $user->getParam('admin_language');
		} else if($user_cms_id === false && isset($this->oldLocale)) {
			if($this->oldLocale === false)
				return;
			$local = $this->oldLocale;
		}
		if(empty($locale)) {
			$params = JComponentHelper::getParams('com_languages');
			$locale = $params->get('site', 'en-GB');
		}

		$this->oldLocale = false;
		$lang = JFactory::getLanguage();
		if($lang->getTag() == $locale)
			return;

		$this->oldLocale = $lang->getTag();

		$joomlaConfig = JFactory::getConfig();
		$joomlaConfig->set('language', $locale);

		$override_path = hikashop_getLanguagePath(JPATH_ROOT) . '/overrides/' . $locale . '.override.ini';
		$lang->load(HIKASHOP_COMPONENT, JPATH_SITE, $locale, true);
		if(file_exists($override_path))
			hikashop_loadTranslationFile($override_path);
		return $locale;
	}


	private function processOrderEmail(&$mail, &$mailer, &$do) {
		$supportEmail = $this->orderEmails[$mail->mail_name];
		$config = hikamarket::config();
		$vendorOrderType = 'subsale';

		$subsaleEmail = false;
		if((!empty($mail->data->order_type) && $mail->data->order_type == $vendorOrderType)
		|| (!empty($mail->data->old->order_type) && $mail->data->old->order_type == $vendorOrderType)) {
			$subsaleEmail = true;
		}

		if($subsaleEmail) {
			$do = false;
			return;
		}

		if(!$subsaleEmail && $supportEmail) {
			$vendorClass = hikamarket::get('class.vendor');
			$vendor = $vendorClass->get(1);
			$this->setVendorNotifyEmails($mail, $vendor);
		}
	}

	private function processContactMail(&$mail, &$mailer, &$do) {
		$config = hikamarket::config();
		if($config->get('contact_mail_to_vendor', 1) == 0)
			return;

		if(!empty($mail->data->product) && isset($mail->data->product->product_vendor_id) && $mail->data->product->product_vendor_id == 0 && $mail->data->product->product_type == 'variant') {
			$productClass = hikashop_get('class.product');
			$parentProduct = $productClass->get((int)$mail->data->product->product_parent_id);
			if(!empty($parentProduct))
				$mail->data->product->product_vendor_id = $parentProduct->product_vendor_id;
		}

		if(!empty($mail->data->product) && isset($mail->data->product->product_vendor_id) && $mail->data->product->product_vendor_id > 1) {
			$vendorClass = hikamarket::get('class.vendor');
			$vendor = $vendorClass->get($mail->data->product->product_vendor_id);
			if(empty($vendor) || empty($vendor->vendor_published))
				return;
			$mail->dst_email = $vendor->vendor_email;
			$mail->dst_name = $vendor->vendor_name;
		}

		if(!empty($mail->data->element->target) && $mail->data->element->target == 'vendor') {
			$vendorClass = hikamarket::get('class.vendor');
			$vendor = $vendorClass->get($mail->data->element->vendor_id);
			if(empty($vendor) || empty($vendor->vendor_published))
				return;
			$mail->dst_email = $vendor->vendor_email;
			$mail->dst_name = $vendor->vendor_name;
		}
	}

	private function processCommentMail(&$mail, &$mailer, &$do) {
		if($mail->data->result->vote_type == 'vendor') {


			$do = false;
			return;
		}

		if($mail->data->result->vote_type == 'product') {
			if(empty($mail->data->type->product_vendor_id))
				return;

			$vendorClass = hikamarket::get('class.vendor');
			$vendor = $vendorClass->get($mail->data->type->product_vendor_id);
			if(empty($vendor) || empty($vendor->vendor_published))
				return;

			$mail->dst_email = $vendor->vendor_email;
			$mail->dst_name = $vendor->vendor_name;
		}
	}

	private function processOrdernotificationTemplate(&$mail, &$data, &$content, &$vars, &$texts, &$templates) {
		$config = hikamarket::config();

		if(empty($templates['PRODUCT_LINE']) || !$config->get('mail_display_vendor', 0))
			return;


		$vendor_ids = array();
		foreach($templates['PRODUCT_LINE'] as $p) {
			if(!empty($p['product']->product_vendor_id))
				$vendor_ids[ (int)$p['product']->product_vendor_id ] = (int)$p['product']->product_vendor_id;
		}
		if(empty($vendor_ids))
			return;

		$query = 'SELECT * FROM ' . hikamarket::table('vendor') . ' WHERE vendor_id IN (' . implode(',', $vendor_ids) . ') AND vendor_published = 1';
		$this->db->setQuery($query);
		$vendors = $this->db->loadObjectList('vendor_id');

		foreach($templates['PRODUCT_LINE'] as &$p) {
			if(empty($p['product']->product_vendor_id))
				continue;

			$v = (int)$p['product']->product_vendor_id;
			if(!isset($vendors[$v]))
				continue;

			$p['vendor'] = $vendors[$v];
			if(empty($p['PRODUCT_DETAILS'])) $p['PRODUCT_DETAILS'] = '';
			$p['PRODUCT_DETAILS'] .= '<br />' . JText::sprintf('SOLD_BY_VENDOR', $vendors[$v]->vendor_name);
		}
		unset($p);
	}

	private function processContactrequestTemplate(&$mail, &$data, &$content, &$vars, &$texts, &$templates) {
		$config = hikamarket::config();
		if($config->get('contact_mail_to_vendor', 1) == 0)
			return;
		if(!empty($data->product) && isset($data->product->product_vendor_id) && $data->product->product_vendor_id > 1) {
			global $Itemid;
			$url_itemid = (!empty($Itemid)) ? ('&Itemid=' . $Itemid) : '';
			$front_product_url = hikashop_frontendLink('index.php?option=com_hikashop&ctrl=product&task=show&cid='.$data->product->product_id.'&name='.$data->product->alias . $url_itemid);
			$vars['PRODUCT_DETAILS'] = '<p>' .
					strip_tags($data->product->product_name.' ('.$data->product->product_code.')') . ' ' .
					'<a href="'.$front_product_url.'">' . JText::_('FRONTEND_DETAILS_PAGE').'</a>' .
				'</p>';
		}
	}
}
