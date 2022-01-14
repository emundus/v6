<?php
/**
 * @package    StripeConnect for Joomla! HikaShop
 * @version    1.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2020 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php

include_once dirname(__FILE__) . DS . 'stripeconnect_class.php';

class stripeconnectMarketController extends hikamarketController {
	protected $rights = array(
		'display' => array('show', 'oauth'),
		'add' => array(),
		'edit' => array('save'),
		'modify' => array(),
		'delete' => array()
	);
	protected $pluginCtrl = array('hikamarket', 'stripeconnect');
	protected $type = 'plg_stripeconnect';

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
		if(!hikamarket::acl('plugins/stripeconnect'))
			return hikamarket::deny('vendor', JText::sprintf('HIKAM_ACTION_DENY', 'stripeConnect'));

		hikaInput::get()->set('layout', 'show');
		return parent::display();
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
		if(!hikamarket::acl('plugins/stripeconnect'))
			return hikamarket::deny('vendor', JText::sprintf('HIKAM_ACTION_DENY', 'stripeConnect'));

		return $this->adminStore(true);
	}

	public function oauth() {
		if(!hikamarket::loginVendor())
			return false;
		if( !$this->config->get('frontend_edition',0) )
			return false;

		$authorization_code = hikaInput::get()->getString('code', '');
		$state = hikaInput::get()->getString('state', '');

		if(empty($state))
			return false;

		$vendor = hikamarket::loadVendor(true);
		if($state != 'vendor.'.$vendor->vendor_id)
			return false;

		if(!empty($vendor->vendor_params->stripe_account_id))
			return false;

		$stripeClass = hikamarket::get('class.plg_stripeconnect');
		$stripeAPI = $stripeClass->getStripeAPI();

		$ret = $stripeAPI->oAuthToken(array(
    	    'grant_type' => 'authorization_code',
            'code' => $authorization_code,
		));

		$app = JFactory::getApplication();
		if(empty($ret)) {
			$app->enqueueMessage(JText::_('STRIPE_ASSOCIATION_ERROR'), 'error');
			$app->redirect( hikamarket::completeLink('vendor') );
		}

		if(!empty($ret)) {
			$updateVendor = new stdClass();
			$updateVendor->vendor_id = (int)$vendor->vendor_id;
			$updateVendor->vendor_params = $vendor->vendor_params;


			$updateVendor->vendor_params->stripe_account_id = $ret->stripe_user_id;

			$vendorClass = hikamarket::get('class.vendor');
			$vendorClass->save($updateVendor);

			$app->enqueueMessage(JText::_('STRIPE_ASSOCIATION_SUCCESS'), 'success');
		}

		$app->redirect( hikamarket::completeLink('vendor') );
	}
}
