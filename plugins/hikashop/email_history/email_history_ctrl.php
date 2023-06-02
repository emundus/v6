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

include_once dirname(__FILE__) . DS . 'email_history_class.php';

class email_historyController extends hikashopController {
	public $display = array('listing','show','cancel','');
	public $modify_views = array('edit');
	public $add = array();
	public $modify = array('resend');
	public $delete = array('delete','remove');

	public $pluginCtrl = array('hikashop', 'email_history');
	public $type = 'plg_email_history';

	public function __construct($config = array(), $skip = false) {
		parent::__construct($config, $skip);
		if(!$skip)
			$this->registerDefaultTask('listing');
	}

	protected function getACLName($task) {
		return 'email_log';
	}

	public function listing() {
		hikaInput::get()->set('layout', 'listing');
		return $this->display();
	}
	public function resend() {
		$cid = hikashop_getCID('email_log_id');
		if(!$cid) {
			hikaInput::get()->set('layout', 'listing');
		} else {
			$emailHistoryClass = new hikashopPlg_email_historyClass();
			$app = JFactory::getApplication();
			if($emailHistoryClass->resend($cid))
				$app->enqueueMessage(JText::_('THE_EMAIL_HAS_BEEN_RESENT'), 'notice');
			else
				$app->enqueueMessage(JText::_('AN_ERROR_HAPPENED_DURING_THE_RESENDING_OF_THE_EMAIL'), 'error');
			hikaInput::get()->set('layout', 'form');
		}
		return $this->display();
	}
}
