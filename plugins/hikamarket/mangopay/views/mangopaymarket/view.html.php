<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class mangopaymarketViewmangopaymarket extends HikamarketView {
	public function display($tpl = null, $params = array()) {
		$this->params =& $params;
		$fct = $this->getLayout();
		if(method_exists($this, $fct)) {
			if($this->$fct() === false)
				return;
		}
		parent::display($tpl);
	}

	protected function loadCSS() {
		$doc = JFactory::getDocument();
		$doc->addStyleSheet(JURI::base(true).'/plugins/hikamarket/mangopay/media/mangopay.css?v='.HIKAMARKET_RESSOURCE_VERSION);
	}

	public function show() {
		$this->loadCSS();

		$config = hikamarket::config();
		$shopConfig = hikamarket::config(false);

		$this->vendor = hikamarket::loadVendor(true);
		if(is_string($this->vendor->vendor_params) && !empty($this->vendor->vendor_params))
			$this->vendor->vendor_params = hikamarket::unserialize($this->vendor->vendor_params);

		$this->currencyClass = hikamarket::get('shop.class.currency');
		$this->mangoClass = hikamarket::get('class.plg_mangopay');

		$this->mangopayPlugin = $this->mangoClass->getMangoPlugin();
		$this->api = $this->mangoClass->getMangoAPI();
		$this->mango_vendor = null;
		$this->mango_wallets = null;
		$this->mango_bank_accounts = null;

		$this->mango_vendor = $this->mangopayPlugin->getVendor($this->vendor);

		if(!empty($this->mango_vendor->Id)) {

			$this->mango_wallets = $this->mangopayPlugin->getVendorWallets($this->vendor);

			if(empty($this->mango_wallets)) {
				$wallet = $this->mangopayPlugin->getVendorWallet($this->vendor, null, true);
				$this->mango_wallets[ strtolower($wallet->Currency) ] = $wallet;
			}

			try {

				$this->mango_bank_accounts = $this->api->Users->GetBankAccounts($this->mango_vendor->Id);

			}
			catch (MangoPay\ResponseException $e) { $this->mangoClass->mangoDebug($e, true); }
			catch (MangoPay\Exception $e) { $this->mangoClass->mangoDebug($e, false); }
			catch (Exception $e) {}
		}

		$this->toolbar = array(
			array(
				'icon' => 'back',
				'name' => JText::_('HIKA_BACK'),
				'url' => hikamarket::completeLink('vendor')
			)
		);
	}

	public function payout() {
		$this->loadCSS();

		$config = hikamarket::config();
		$shopConfig = hikamarket::config(false);
		$app = JFactory::getApplication();

		$this->vendor = hikamarket::loadVendor(true);
		if(is_string($this->vendor->vendor_params) && !empty($this->vendor->vendor_params))
			$this->vendor->vendor_params = hikamarket::unserialize($this->vendor->vendor_params);

		$walletId = hikaInput::get()->getInt('wallet', 0);
		if(empty($walletId)) {
			$app->enqueueMessage(JText::_('MANGO_INVALID_REQUEST'));
			$app->redirect( hikamarket::completeLink('mangopay', false, true, false) );
		}

		$this->currencyClass = hikamarket::get('shop.class.currency');
		$this->mangoClass = hikamarket::get('class.plg_mangopay');

		$this->mangopayPlugin = $this->mangoClass->getMangoPlugin();

		$this->mango_vendor = $this->mangopayPlugin->getVendor($this->vendor);
		if(empty($this->mango_vendor->Id)) {
			$app->enqueueMessage(JText::_('MANGO_INVALID_REQUEST'));
			$app->redirect( hikamarket::completeLink('mangopay', false, true, false) );
		}

		$this->api = $this->mangoClass->getMangoAPI();
		$this->mango_wallets = null;
		$this->mango_bank_accounts = null;

		$mango_wallets = $this->mangopayPlugin->getVendorWallets($this->vendor);
		$this->mango_wallet = null;
		foreach($mango_wallets as $mango_wallet) {
			if((int)$mango_wallet->Id == $walletId) {
				$this->mango_wallet = $mango_wallet;
				break;
			}
		}
		if(empty($this->mango_wallet)) {
			$app->enqueueMessage(JText::_('MANGO_INVALID_WALLET'));
			$app->redirect( hikamarket::completeLink('mangopay', false, true, false) );
		}
		$this->assignRef('walletId', $walletId);

		$duration = 31;
		if(isset($this->mangopayPlugin->params))
			$duration = (int)$this->mangopayPlugin->params->get('payout_waiting_duration', 31);
		if($duration <= 0)
			$duration = 31;

		$this->currency_id = $this->convertCurrency($this->mango_wallet->Balance->Currency);
		$this->transactions = $this->mangoClass->getTransactions($this->mango_wallet->Id, $duration);

		$this->transactions_total = 0.0;
		foreach($this->transactions as $transaction) {
			if($transaction->Nature == 'REGULAR')
				$this->transactions_total += ($transaction->CreditedFunds->Amount / 100);

			if($transaction->Nature == 'REFUND')
				$this->transactions_total -= ($transaction->CreditedFunds->Amount / 100);
		}
		if($this->transactions_total < 0)
			$this->transactions_total = 0.0;

		$this->maximum_authorized = ($this->mango_wallet->Balance->Amount / 100) - $this->transactions_total;
		if($this->maximum_authorized < 0)
			$this->maximum_authorized = 0.0;

		try {

			$this->mango_bank_accounts = $this->api->Users->GetBankAccounts($this->mango_vendor->Id);

		}
		catch (MangoPay\ResponseException $e) { $this->mangoClass->mangoDebug($e, true); }
		catch (MangoPay\Exception $e) { $this->mangoClass->mangoDebug($e, false); }
		catch (Exception $e) {}
	}

	public function document() {
	}

	public function getCountryList() {
		if(empty($this->mangopayPlugin))
			$this->mangopayPlugin = $this->mangoClass->getMangoPlugin();
		$countries = $this->mangopayPlugin->getCountryList();
		$ret = array();
		foreach($countries as $country) {
			$ret[] = JHTML::_('select.option', $country, $country);
		}
		return $ret;
	}

	protected function convertCurrency($currency_code) {
		$db = JFactory::getDBO();
		$query = 'SELECT currency_id FROM ' . hikamarket::table('shop.currency') . ' WHERE currency_code = ' . $db->Quote(strtoupper(trim($currency_code)));
		$db->setQuery($query);
		$ret = (int)$db->loadResult();
		return $ret;
	}
}
