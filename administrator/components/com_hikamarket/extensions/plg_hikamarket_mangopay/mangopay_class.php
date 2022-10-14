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
class hikamarketPlg_mangopayClass extends JObject {

	protected static $currentMangoVendor = null;
	protected static $mangopayPlugin = null;
	protected static $mangopayAPI = null;

	public function getMangoPlugin() {
		if(empty(self::$mangopayPlugin))
			self::$mangopayPlugin = hikamarket::import('hikashoppayment', 'mangopay');
		return self::$mangopayPlugin;
	}

	public function getMangoVendor() {
		if(!empty(self::$currentMangoVendor))
			return self::$currentMangoVendor;

		$mangopayPlugin = $this->getMangoPlugin();
		$vendor = hikamarket::loadVendor(true, false);
		self::$currentMangoVendor = $mangopayPlugin->getVendor($vendor);
		return self::$currentMangoVendor;
	}

	public function getMangoAPI() {
		if(!empty(self::$mangopayAPI))
			return self::$mangopayAPI;

		$mangopayPlugin = $this->getMangoPlugin();
		self::$mangopayAPI = $mangopayPlugin->getAPI();
		return self::$mangopayAPI;
	}

	public function debug($e, $r = false) {
		$mangopayPlugin = $this->getMangoPlugin();
		$mangopayPlugin->mangoDebug($e, $r);
	}
	public function mangoDebug($e, $r = false) { return $this->debug($e, $r); }

	public function saveForm() {
		$formData = hikaInput::get()->get('mango', array(), 'array');
		if(empty($formData))
			return false;

		$api = $this->getMangoAPI();
		$mango_vendor = $this->getMangoVendor();

		if(empty($mango_vendor) || empty($mango_vendor->Id))
			return false;

		$user = new MangoPay\UserLegal();
		$user->Id = $mango_vendor->Id;

		if(!empty($formData['name']))
			$user->Name = substr($formData['name'], 0, 254);

		if(!empty($formData['legalpersontype']) && in_array($formData['legalpersontype'], array('BUSINESS', 'ORGANIZATION', 'SOLETRADER')))
			$user->LegalPersonType = $formData['legalpersontype'];

		if(!empty($formData['email']))
			$user->Email = substr($formData['email'], 0, 254);

		if(!empty($formData['headquartersaddress']))
			$user->HeadquartersAddress = substr($formData['headquartersaddress'], 0, 254);

		if(!empty($formData['legalrepresentativefirstname']))
			$user->LegalRepresentativeFirstName = substr($formData['legalrepresentativefirstname'], 0, 99);

		if(!empty($formData['legalrepresentativelastname']))
			$user->LegalRepresentativeLastName = substr($formData['legalrepresentativelastname'], 0, 99);

		if(!empty($formData['legalrepresentativeaddress']))
			$user->LegalRepresentativeAddress = substr($formData['legalrepresentativeaddress'], 0, 254);

		if(!empty($formData['legalrepresentativeemail']))
			$user->LegalRepresentativeEmail = substr($formData['legalrepresentativeemail'], 0, 254);

		if(!empty($formData['legalrepresentativebirthday']))
			$user->LegalRepresentativeBirthday = hikamarket::getTime($formData['legalrepresentativebirthday']);

		if(!empty($formData['legalrepresentativenationality']))
			$user->LegalRepresentativeNationality = strtoupper(substr(trim($formData['legalrepresentativenationality']), 0, 2));
		if(!empty($formData['legalrepresentativecountryofresidence']))
			$user->LegalRepresentativeCountryOfResidence = strtoupper(substr(trim($formData['legalrepresentativecountryofresidence']), 0, 2));

		$result = false;
		try {

			$result = $api->Users->Update($user);

		}
		catch (MangoPay\ResponseException $e) { $this->debug($e, true); }
		catch (MangoPay\Exception $e) { $this->debug($e, false); }
		catch (Exception $e) {}

		if(empty($result) || empty($result->Id))
			return false;
		return $result->Id;
	}

	public function addBank() {
		$formData = hikaInput::get()->get('mangobank', array(), 'array');
		if(empty($formData))
			return false;

		if(empty($formData['type']) || !in_array($formData['type'], array('IBAN','GB','US','CA','OTHER')))
			return false;

		$api = $this->getMangoAPI();
		$mango_vendor = $this->getMangoVendor();

		$bank = new MangoPay\BankAccount();
		$bank->Type = $formData['type'];
		$bank->OwnerName = $formData['ownername'];
		$bank->OwnerAddress =  $formData['owneraddress'];

		switch($bank->Type) {
			case 'IBAN':
				$bank->Details = new MangoPay\BankAccountDetailsIBAN();
				$bank->Details->IBAN = $formData['iban']['iban'];
				$bank->Details->BIC = $formData['iban']['bic'];
				break;
			case 'GB':
				$bank->Details = new MangoPay\BankAccountDetailsGB();
				$bank->Details->AccountNumber = $formData['gb']['accountnumber'];
				$bank->Details->SortCode = $formData['gb']['sortcode'];
				break;
			case 'US':
				$bank->Details = new MangoPay\BankAccountDetailsUS();
				$bank->Details->AccountNumber = $formData['us']['accountnumber'];
				$bank->Details->ABA = $formData['us']['aba'];
				break;
			case 'CA':
				$bank->Details = new MangoPay\BankAccountDetailsCA();
				$bank->Details->BankName = $formData['ca']['bankname'];
				$bank->Details->InstitutionNumber = $formData['ca']['institutionnumber'];
				$bank->Details->BranchCode = $formData['ca']['branchcode'];
				$bank->Details->AccountNumber = $formData['ca']['accountnumber'];
				break;
			case 'OTHER':
				$bank->Details = new MangoPay\BankAccountDetailsOTHER();
				$bank->Details->Country = $formData['other']['country'];
				$bank->Details->BIC = $formData['other']['bic'];
				$bank->Details->AccountNumber = $formData['other']['accountnumber'];
				break;
		}

		$result = false;
		try {

			$result = $api->Users->CreateBankAccount($mango_vendor->Id, $bank);

		}
		catch (MangoPay\ResponseException $e) { $this->debug($e, true); }
		catch (MangoPay\Exception $e) { $this->debug($e, false); }
		catch (Exception $e) {}

		if(empty($result) || empty($result->Id))
			return false;
		return $result->Id;
	}

	public function addDocument() {
		$formData = hikaInput::get()->get('mangodoc', array(), 'array');
		if(empty($formData))
			return false;

		$api = $this->getMangoAPI();
		$mango_vendor = $this->getMangoVendor();

		$document_type = strtoupper(trim($formData['type']));
		if(!in_array($document_type, array('IDENTITY_PROOF', 'REGISTRATION_PROOF', 'ARTICLES_OF_ASSOCIATION', 'SHAREHOLDER_DECLARATION', 'IDENTITY_PROOF', 'ADDRESS_PROOF')))
			return false;

		if(empty($_FILES) || empty($_FILES['mangodoc_page']))
			return false;

		$file = $_FILES['mangodoc_page'];

		$kyc_document = new MangoPay\KycDocument();
		$kyc_document->Type = $document_type;

		$createdDocument = false;
		try {
			$createdDocument = $api->Users->CreateKycDocument($mango_vendor->Id, $kyc_document);
		}
		catch (MangoPay\ResponseException $e) { $this->debug($e, true); }
		catch (MangoPay\Exception $e) { $this->debug($e, false); }
		catch (Exception $e) {}

		if(empty($createdDocument) || empty($createdDocument->Id))
			return false;

		$createdPage = false;
		try {
			$api->Users->CreateKycPageFromFile($mango_vendor->Id, $createdDocument->Id, $file);
			$createdPage = true;
		}
		catch (MangoPay\ResponseException $e) { $this->debug($e, true); }
		catch (MangoPay\Exception $e) { $this->debug($e, false); }
		catch (Exception $e) {}

		if(empty($createdPage))
			return false;

		$kyc_document = new MangoPay\KycDocument();
		$kyc_document->Id = $createdDocument->Id;
		$kyc_document->Status = 'VALIDATION_ASKED';

		$updatedDocument = false;
		try {
			$updatedDocument = $api->Users->UpdateKycDocument($mango_vendor->Id, $kyc_document);
		}
		catch (MangoPay\ResponseException $e) { $this->debug($e, true); }
		catch (MangoPay\Exception $e) { $this->debug($e, false); }
		catch (Exception $e) {}

		if(empty($updatedDocument))
			return false;
		return true;
	}

	public function doPayout() {
		$formData = hikaInput::get()->get('payout', array(), 'array');
		if(empty($formData))
			return false;

		$walletId = (int)trim(@$formaData['wallet']);
		if(empty($walletId))
			return false;

		$value = (float)hikashop_toFloat(@$formData['value']);
		if($value <= 0.0)
			return false;

		$api = $this->getMangoAPI();
		$vendor = hikamarket::loadVendor(true, false);
		$mango_vendor = $this->getMangoVendor();
		$mangopayPlugin = $this->getMangoPlugin();

		$mango_wallets = $mangopayPlugin->getVendorWallets($vendor);
		$mango_wallet = null;
		foreach($mango_wallets as $mango_wallet) {
			if((int)$mango_wallet->Id == $walletId) {
				$mango_wallet = $mango_wallet;
				break;
			}
		}


		$duration = 31;
		if(isset($mangopayPlugin->params))
			$duration = (int)$mangopayPlugin->params->get('payout_waiting_duration', 31);
		if($duration <= 0)
			$duration = 31;

		$transactions = $this->getTransactions($mango_wallet->Id, $duration);
		$transactions_total = 0.0;
		foreach($transactions as $transaction) {
			if($transaction->Nature == 'REGULAR')
				$transactions_total += ($transaction->CreditedFunds->Amount / 100);

			if($transaction->Nature == 'REFUND')
				$transactions_total -= ($transaction->CreditedFunds->Amount / 100);
		}
		if($transactions_total < 0)
			$transactions_total = 0.0;

		$maximum_authorized = ($mango_wallet->Balance->Amount / 100) - $transactions_total;
		if($maximum_authorized < 0)
			$maximum_authorized = 0.0;

		if($value > $maximum_authorized)
			return false;

		$bank_account = (int)@$formData['bank'];
		if(empty($bank_account))
			return false;
		try {

			$mango_bank_account = $api->Users->GetBankAccount($mango_vendor->Id, $bank_account);

		}
		catch (MangoPay\ResponseException $e) { $this->debug($e, true); }
		catch (MangoPay\Exception $e) { $this->debug($e, false); }
		catch (Exception $e) {}

		if(empty($mango_bank_account))
			return false;

		$payout = new MangoPay\PayOut();
		$payout->AuthorId = $mango_vendor->Id;
		$payout->DebitedWalletId = $mango_wallet->Id;
		$payout->PaymentType = 'BANK_WIRE';

		$payout->DebitedFunds = new MangoPay\Money();
		$payout->DebitedFunds->Currency = $mango_wallet->Currency;
		$payout->DebitedFunds->Amount = $value * 100;

		$payout->Fees = new MangoPay\Money();
		$payout->Fees->Currency = $mango_wallet->Currency;
		$payout->Fees->Amount = 0;

		$payout->MeanOfPaymentDetails = new MangoPay\PayOutPaymentDetailsBankWire();
		$payout->MeanOfPaymentDetails->BankAccountId = $mango_bank_account->Id;

		$payoutResult = null;
		try {

			$payoutResult = $api->PayOuts->Create($payout);

		}
		catch (MangoPay\ResponseException $e) { $this->debug($e, true); }
		catch (MangoPay\Exception $e) { $this->debug($e, false); }
		catch (Exception $e) {}

		if(empty($payoutResult) || empty($payoutResult->Id))
			return false;

		if(isset($payoutResult->Status) && $payoutResult->Status == 'FAILED') {
			switch(@$payoutResult->ResultCode) {
				case '002998':
					$this->errors[] = 'The bank account needs to be KYC verified. Please contact the side owner for more details.';
					break;
				case '002999':
					$this->errors[] = 'The account needs to be KYC verified. Please contact the side owner for more details.';
					break;
			}
			return false;
		}

		return $payoutResult;
	}

	public function getTransactions($wallet_id, $duration = 31) {
		$api = $this->getMangoAPI();

		$transactions = false;
		try {
			$pagination = null;
			$filter = new MangoPay\FilterTransactions();
			if($duration > 1)
				$filter->AfterDate = time() - ($duration * 86400);
			$filter->Status = 'SUCCEEDED';
			$filter->Type = 'TRANSFER';

			$transactions = $api->Wallets->GetTransactions($wallet_id, $pagination, $filter);
		}
		catch (MangoPay\ResponseException $e) { $this->debug($e, true); }
		catch (MangoPay\Exception $e) { $this->debug($e, false); }
		catch (Exception $e) {}

		return $transactions;
	}

}
