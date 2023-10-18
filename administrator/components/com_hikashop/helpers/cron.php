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
class hikashopCronHelper {
	var $report = false;
	var $messages = array();
	var $detailMessages = array();

	public function cron($report = null) {
		$time = time();
		$config =& hikashop_config();

		$this->messages = array();
		$this->detailMessages = array();

		$firstMessage = JText::sprintf('CRON_TRIGGERED',hikashop_getDate(time()));
		$this->messages[] = $firstMessage;
		if($this->report) {
			hikashop_display($firstMessage, 'info');
		}

		if($config->get('cron_next') > $time) {
			if($config->get('cron_next') > ($time + $config->get('cron_frequency'))) {
				$newConfig = new stdClass();
				$newConfig->cron_next = $time + $config->get('cron_frequency');
				$config->save($newConfig);
			}

			$nottime = JText::sprintf('CRON_NEXT', hikashop_getDate($config->get('cron_next')));
			$this->messages[] = $nottime;
			if($this->report) {
				hikashop_display($nottime,'info');
			}

			$sendreport = (int)$config->get('cron_sendreport');
			if($sendreport == 1) {
				$this->sendEmailReport();
			}
			return false;
		}

		$newConfig = new stdClass();
		$newConfig->cron_next = $config->get('cron_next') + $config->get('cron_frequency');
		if($newConfig->cron_next <= $time || $newConfig->cron_next> $time + $config->get('cron_frequency'))
			$newConfig->cron_next = $time + $config->get('cron_frequency');
		$newConfig->cron_last = $time;
		$newConfig->cron_fromip = hikashop_getIP();
		$config->save($newConfig);

		JPluginHelper::importPlugin('hikashop');
		JPluginHelper::importPlugin('hikashopshipping');
		JPluginHelper::importPlugin('hikashoppayment');
		$resultsTrigger = array();

		$app = JFactory::getApplication();
		$app->triggerEvent('onHikashopCronTrigger',array(&$resultsTrigger));

		if($this->report) {
			foreach($resultsTrigger as $message) {
				hikashop_display($message,'info');
			}
		}

		$this->detailMessages = $resultsTrigger;
		return true;
	}

	public function report() {
		$config =& hikashop_config();

		$newConfig = new stdClass();
		$newConfig->cron_report = @implode('<br/>', $this->messages);
		if(strlen($newConfig->cron_report) > 800)
			$newConfig->cron_report = substr($newConfig->cron_report,0,795).'...';
		$config->save($newConfig);

		$saveReport = $config->get('cron_savereport');
		if(!empty($saveReport)) {
			$reportPath = JPath::clean(HIKASHOP_ROOT . trim(html_entity_decode($config->get('cron_savepath'))));
			jimport('joomla.filesystem.folder');
			$parentFolder = dirname($reportPath);
			if(JFolder::exists($parentFolder) || JFolder::create($parentFolder)) {
				file_put_contents($reportPath, "\r\n"."\r\n" . str_repeat('*', 150) . "\r\n" . str_repeat('*', 20) . str_repeat(' ', 5) . hikashop_getDate(time()) . str_repeat(' ', 5) . str_repeat('*', 20) . "\r\n", FILE_APPEND);
				@file_put_contents($reportPath, @implode("\r\n", $this->messages), FILE_APPEND);
				if($saveReport == 2 && !empty($this->detailMessages)) {
					@file_put_contents($reportPath, "\r\n" . "---- Details ----" . "\r\n", FILE_APPEND);
					@file_put_contents($reportPath, @implode("\r\n", $this->detailMessages), FILE_APPEND);
				}
			}
		}

		$sendreport = $config->get('cron_sendreport');
		if(!empty($sendreport)) {
			$this->sendEmailReport();
		}
	}

	protected function sendEmailReport() {
		$config =& hikashop_config();
		$sendreport = (int)$config->get('cron_sendreport');
		if(!$sendreport)
			return false;

		$data = new stdClass();
		$data->report = @implode('<br/>', $this->messages);
		$data->detailreport = '';
		if(!empty($this->detailMessages) && is_array($this->detailMessages))
			$data->detailreport = implode('<br/>', $this->detailMessages);

		$mailClass = hikashop_get('class.mail');
		$mail = $mailClass->get('cron_report', $data);
		$mail->subject = JText::_($mail->subject);

		$receiverString = $config->get('cron_sendto');
		$receivers = explode(',', $receiverString);
		if(empty($receivers))
			return false;

		if($sendreport == 1 || !empty($this->detailMessages)) {
			foreach($receivers as $oneReceiver) {
				$mail->dst_email = $oneReceiver;
				$mailClass->sendMail($mail);
			}
		}
		return true;
	}
}
