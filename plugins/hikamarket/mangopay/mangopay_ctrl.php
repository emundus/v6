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

include_once dirname(__FILE__) . DS . 'mangopay_class.php';

class mangopayMarketController extends hikamarketController {
	protected $rights = array(
		'display' => array('show'),
		'add' => array('bank','document','payout'),
		'edit' => array('save','addbank','adddocument','dopayout'),
		'modify' => array(),
		'delete' => array()
	);
	protected $pluginCtrl = array('hikamarket', 'mangopay');
	protected $type = 'plg_mangopay';

	public function __construct($config = array(), $skip = false) {
		parent::__construct($config, $skip);
		if(!$skip)
			$this->registerDefaultTask('show');
		$this->config = hikamarket::config();
	}

	public function show() {
		if(!hikamarket::loginVendor())
			return false;
		if(!$this->config->get('frontend_edition',0))
			return false;
		if(!hikamarket::acl('plugins/mangopay'))
			return hikamarket::deny('vendor', JText::sprintf('HIKAM_ACTION_DENY', 'MangoPay'));

		hikaInput::get()->set('layout', 'show');
		return parent::display();
	}

	public function bank() {
		if(!hikamarket::loginVendor())
			return false;
		if(!$this->config->get('frontend_edition',0))
			return false;
		if(!hikamarket::acl('plugins/mangopay'))
			return hikamarket::deny('vendor', JText::sprintf('HIKAM_ACTION_DENY', 'MangoPay'));

		hikaInput::get()->set('layout', 'bank');
		return parent::display();
	}

	public function document() {
		if(!hikamarket::loginVendor())
			return false;
		if(!$this->config->get('frontend_edition',0))
			return false;
		if(!hikamarket::acl('plugins/mangopay'))
			return hikamarket::deny('vendor', JText::sprintf('HIKAM_ACTION_DENY', 'MangoPay'));

		hikaInput::get()->set('layout', 'document');
		return parent::display();
	}

	public function payout() {
		if(!hikamarket::loginVendor())
			return false;
		if(!$this->config->get('frontend_edition',0))
			return false;
		if(!hikamarket::acl('plugins/mangopay'))
			return hikamarket::deny('vendor', JText::sprintf('HIKAM_ACTION_DENY', 'MangoPay'));

		hikaInput::get()->set('layout', 'payout');
		return parent::display();
	}

	public function addbank() {
		if(!hikamarket::loginVendor())
			return false;
		if( !$this->config->get('frontend_edition', 0) )
			return false;
		if(!hikamarket::acl('plugins/mangopay'))
			return hikamarket::deny('vendor', JText::sprintf('HIKAM_ACTION_DENY', 'MangoPay'));

		$app = JFactory::getApplication();
		JSession::checkToken() || die('Invalid Token');

		$mangoClass = hikamarket::get('class.plg_mangopay');

		$status = $mangoClass->addBank();
		if($status) {
			$app->enqueueMessage(JText::_('HIKAM_SUCC_SAVED'), 'message');
			hikaInput::get()->set('cid', $status);
			hikaInput::get()->set('fail', null);
			return $this->show();
		}

		$app->enqueueMessage(JText::_('ERROR_SAVING'), 'error');
		if(!empty($mangoClass->errors)) {
			foreach($mangoClass->errors as $err) {
				$app->enqueueMessage($err, 'error');
			}
		}
		return $this->bank();
	}

	public function adddocument() {
		if(!hikamarket::loginVendor())
			return false;
		if( !$this->config->get('frontend_edition', 0) )
			return false;
		if(!hikamarket::acl('plugins/mangopay'))
			return hikamarket::deny('vendor', JText::sprintf('HIKAM_ACTION_DENY', 'MangoPay'));

		$app = JFactory::getApplication();
		JSession::checkToken() || die('Invalid Token');

		$mangoClass = hikamarket::get('class.plg_mangopay');

		$status = $mangoClass->adddocument();
		if($status) {
			$app->enqueueMessage(JText::_('HIKAM_SUCC_SAVED'), 'message');
			hikaInput::get()->set('cid', $status);
			hikaInput::get()->set('fail', null);
			return $this->show();
		}

		$app->enqueueMessage(JText::_('ERROR_SAVING'), 'error');
		if(!empty($mangoClass->errors)) {
			foreach($mangoClass->errors as $err) {
				$app->enqueueMessage($err, 'error');
			}
		}
		return $this->document();
	}

	public function dopayout() {
		if(!hikamarket::loginVendor())
			return false;
		if( !$this->config->get('frontend_edition', 0) )
			return false;
		if(!hikamarket::acl('plugins/mangopay'))
			return hikamarket::deny('vendor', JText::sprintf('HIKAM_ACTION_DENY', 'MangoPay'));

		$app = JFactory::getApplication();
		JSession::checkToken() || die('Invalid Token');

		$mangoClass = hikamarket::get('class.plg_mangopay');

		$status = $mangoClass->doPayout();
		if($status) {
			$app->enqueueMessage(JText::_('MANGO_PAYOUT_DONE'), 'message');
			hikaInput::get()->set('cid', $status);
			hikaInput::get()->set('fail', null);
			return $this->show();
		}

		$app->enqueueMessage(JText::_('MANGO_ERROR_PAYOU'), 'error');
		if(!empty($mangoClass->errors)) {
			foreach($mangoClass->errors as $err) {
				$app->enqueueMessage($err, 'error');
			}
		}
		return $this->payout();
	}

	public function save() {
		$this->store();
		return $this->show();
	}

	public function store() {
		if(!hikamarket::loginVendor())
			return false;
		if( !$this->config->get('frontend_edition',0) )
			return false;
		if(!hikamarket::acl('plugins/mangopay'))
			return hikamarket::deny('vendor', JText::sprintf('HIKAM_ACTION_DENY', 'MangoPay'));

		return $this->adminStore(true);
	}
}
