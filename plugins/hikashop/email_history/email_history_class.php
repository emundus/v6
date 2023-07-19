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
class hikashopPlg_email_historyClass extends hikashopClass {
	public $tables = array('email_log');
	public $pkeys = array('email_log_id');
	protected $db = null;

	private $dbStructure = array(
		'hikashop_email_log' => array(
			'fields' => array(
				'email_log_id' => 'int(10) unsigned NOT NULL AUTO_INCREMENT',
				'email_log_sender_email' => 'varchar(255) NOT NULL DEFAULT \'\'',
				'email_log_sender_name' => 'varchar(255) NOT NULL DEFAULT \'\'',
				'email_log_recipient_email' => 'varchar(255) NOT NULL DEFAULT \'\'',
				'email_log_recipient_name' => 'varchar(255) NOT NULL DEFAULT \'\'',
				'email_log_reply_email' => 'varchar(255) NOT NULL DEFAULT \'\'',
				'email_log_reply_name' => 'varchar(255) NOT NULL DEFAULT \'\'',
				'email_log_cc_email' => 'varchar(255) NOT NULL DEFAULT \'\'',
				'email_log_bcc_email' => 'varchar(255) NOT NULL DEFAULT \'\'',
				'email_log_subject' => 'text NOT NULL',
				'email_log_altbody' => 'text NOT NULL',
				'email_log_body' => 'text NOT NULL',
				'email_log_name' => 'varchar(255) NOT NULL DEFAULT \'\'',
				'email_log_ref_id' => 'varchar(255) NOT NULL DEFAULT \'\'',
				'email_log_params' => 'text NOT NULL',
				'email_log_date' => 'int(10) NOT NULL',
				'email_log_published' => 'tinyint(3) unsigned NOT NULL DEFAULT \'1\'',
			),
			'primary' => array('email_log_id')
		),
	);

	public function __construct( $config = array() ) {
		parent::__construct($config);
		$this->db = JFactory::getDBO();
	}

	public function initDB() {
		try {
			$current = $this->db->getTableColumns(hikashop_table('email_log'));
		} catch(Exception $e) {
			$current = null;
		}

		if(!empty($current))
			return true;

		$query = $this->getDBCreateQuery('hikashop_email_log');
		$this->db->setQuery($query);
		$this->db->execute();
		return true;
	}

	 public function get($element, $default = null) {
 		$ret = parent::get($element);
 		if(!empty($ret->email_log_params))
			$ret->email_log_params = json_decode($ret->email_log_params);
		return $ret;
	 }

	public function beforeCheckDb(&$createTable, &$custom_fields, &$structure, &$helper) {
		$createTable['#__hikashop_email_log'] = $this->getDBCreateQuery('hikashop_email_log');
		if(!isset($structure['#__hikashop_email_log']))
			$structure['#__hikashop_email_log'] = $this->dbStructure['hikashop_email_log']['fields'];

	}

	private function getDBCreateQuery($name) {
		if(!isset($this->dbStructure[$name]))
			return false;

		$data = array();
		foreach($this->dbStructure[$name]['fields'] as $k => $v) {
			$data[] = '`'.$k.'` ' . $v;
		}
		if(isset($this->dbStructure[$name]['primary'])) {
			if(!is_array($this->dbStructure[$name]['primary']))
				$this->dbStructure[$name]['primary'] = array($this->dbStructure[$name]['primary']);
			$data[] = 'PRIMARY KEY (`'. implode('`, `', $this->dbStructure[$name]['primary']) . '`)';
		} else {
			$k = reset(array_keys($this->dbStructure[$name]['fields']));
			$data[] = 'PRIMARY KEY (`'. $k . '`)';
		}
		return 'CREATE TABLE IF NOT EXISTS `#__'.$name.'` (' . "\r\n" . implode(",\r\n", $data) . ') ENGINE=MyISAM;';
	}

	public function resend($email_log_id) {
		$mailClass = hikashop_get('class.mail');

		$email_log = $this->get($email_log_id);

		if(empty($email_log))
			return false;

		$email = new stdClass();
		$email->published = 1;
		$email->html = 1;
		$email->mail_name = $email_log->email_log_name;
		$email->from_email = $email_log->email_log_sender_email;
		$email->from_name = $email_log->email_log_sender_name;
		$email->dst_email = $email_log->email_log_recipient_email;
		$email->dst_name = $email_log->email_log_recipient_name;
		$email->reply_email = $email_log->email_log_reply_email;
		$email->reply_name = $email_log->email_log_reply_name;
		$email->cc_email = $email_log->email_log_cc_email;
		$email->bcc_email = $email_log->email_log_bcc_email;
		$email->subject = $email_log->email_log_subject;
		$email->body = $email_log->email_log_body;
		$email->full_body = true;
		$email->altbody = $email_log->email_log_altbody;
		switch($email->mail_name) {
			case 'user_account_admin_notification':
			case 'user_account':
				$userClass = hikashop_get('class.user');
				$email->data = $userClass->get($email_log->email_log_ref_id);
				$email->data->user_data = $email->data;
				break;
			case 'order_cancel':
			case 'order_notification':
			case 'order_admin_notification':
			case 'order_creation_notification':
			case 'order_status_notification':
			case 'payment_notification':
				$orderClass = hikashop_get('class.order');
				$email->data = $orderClass->get($email_log->email_log_ref_id);
				break;
			case 'contact_request':
				$productClass = hikashop_get('class.product');
				$email->data = new stdClass();
				$email->data->product = $productClass->get($email_log->email_log_ref_id);
				break;
			case 'new_comment':
				$productClass = hikashop_get('class.product');
				$email->data = new stdClass();
				$email->data->type = $productClass->get($email_log->email_log_ref_id);
				break;
			default:
				break;
		}

		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$app->triggerEvent('onBeforeEmail_logResend', array( &$email, &$email_log ));

		$result = $mailClass->sendMail($email);

		if(!$result || !empty($result->message)){
			if(!empty($result->message) && is_string($mailClass->message)){
				$app = JFactory::getApplication();
				$app->enqueueMessage($mailClass->message, 'error');
			}
			return false;
		}
		return true;
	}

	public function save(&$email_log) {

		if(!empty($email_log->email_log_params) && !is_string($email_log->email_log_params))
			$email_log->email_log_params = json_encode($email_log->email_log_params);

		$new = empty($email_log->email_log_id);
		$do = true;
		$app = JFactory::getApplication();
		if(!empty($new)) {
			$app->triggerEvent('onBeforeEmail_logCreate', array( &$email_log, &$do ));
		} else {
			$app->triggerEvent('onBeforeEmail_logUpdate', array( &$email_log, &$do ));
		}

		if(!$do)
			return false;

		$status = parent::save($email_log);
		if(!$status)
			return $status;

		if(!empty($new)) {
			$email_log->email_log_id = $status;
			$app->triggerEvent('onAfterEmail_logCreate', array( &$email_log ));
		} else {
			$app->triggerEvent('onAfterEmail_logUpdate', array( &$email_log ));
		}
		return $status;
	}

	public function beforeMailSend(&$mail, &$mailer) {
		if(!$this->initDB())
			return false;

		$data = new stdClass();

		$data->email_log_sender_email = strip_tags($mail->from_email);

		if(!empty($mail->from_name))
			$data->email_log_sender_name = strip_tags($mail->from_name);

		if(empty($mail->dst_email))
			$data->email_log_recipient_email = '';
		elseif(is_array($mail->dst_email))
			$data->email_log_recipient_email = strip_tags(implode(',', $mail->dst_email));
		else
			$data->email_log_recipient_email = strip_tags($mail->dst_email);

		if(!empty($mail->dst_name)) {
			if(is_array($mail->dst_name))
				$data->email_log_recipient_name = strip_tags(implode(',', $mail->dst_name));
			else
				$data->email_log_recipient_name = strip_tags($mail->dst_name);
		}

		if(!empty($mail->reply_email))
			$data->email_log_reply_email = strip_tags($mail->reply_email);

		if(!empty($mail->reply_name))
			$data->email_log_reply_name = strip_tags($mail->reply_name);

		if(!empty($mail->cc_email)) {
			if(is_array($mail->cc_email))
				$data->email_log_cc_email = strip_tags(implode(',', $mail->cc_email));
			else
				$data->email_log_cc_email = strip_tags($mail->cc_email);
		}
		if(!empty($mail->bcc_email)) {
			if(is_array($mail->bcc_email))
				$data->email_log_bcc_email = strip_tags(implode(',', $mail->bcc_email));
			else
				$data->email_log_bcc_email = strip_tags($mail->bcc_email);
		}
		if(!empty($mail->subject))
			$data->email_log_subject = strip_tags($mail->subject);

		if(!empty($mail->altbody))
			$data->email_log_altbody = strip_tags($mail->altbody);

		if(!empty($mail->body))
			$data->email_log_body = $mail->body;

		if(!isset($mail->email_log_published)) {
			$config =& hikashop_config();
			$data->email_log_published = $config->get($mail->mail_name.'.email_log_published', 1);
		} else
			$data->email_log_published = $mail->email_log_published;

		$data->email_log_date = time();
		$data->email_log_name = $mail->mail_name;

		if(!empty($mail->name_info) && $mail->name_info != $mail->mail_name)
			$data->email_log_name = $mail->name_info;


		$data->email_log_params = array();
		if(!empty($mail->attachments)) {
			$data->email_log_params['attachments'] = $mail->attachments;
		}

		switch($mail->mail_name) {
			case 'user_account_admin_notification':
			case 'user_account':
				$data->email_log_ref_id = $mail->data->user_data->user_id;
				break;
			case 'order_cancel':
			case 'order_notification':
			case 'order_admin_notification':
			case 'order_creation_notification':
			case 'order_status_notification':
			case 'payment_notification':
				$data->email_log_ref_id = $mail->data->order_id;
				break;
			case 'contact_request':
				if(!empty($mail->data->order->order_id)) {
					$data->email_log_params['contact_type'] = 'order';
					$data->email_log_ref_id = $mail->data->order->order_id;
				} elseif(!empty($mail->data->product->product_id)) {
					$data->email_log_params['contact_type'] = 'product';
					$data->email_log_ref_id = $mail->data->product->product_id;
				}
				break;
			case 'new_comment':
				$data->email_log_ref_id = $mail->data->type->product_id;
				break;
			default:
				break;
		}
		$this->save($data);

		$pluginsClass = hikashop_get('class.plugins');
		$plugin = $pluginsClass->getByName('hikashop', 'email_history');
		if(!empty($plugin->params['number_of_days']))
			$this->clearEntries((int)$plugin->params['number_of_days']);
	}

	protected function clearEntries($days = 0) {
		if(empty($days) || (int)$days <= 0)
			return;

		$query = 'DELETE FROM '.hikashop_table('email_log').' '.
			   ' WHERE email_log_date < '.(time() - ((3600 * 24) * (int)$days));
		$this->db->setQuery($query);
		$this->db->execute();
	}
}
