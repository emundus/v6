<?php

class IPN {

	public $amount;
	public $customData;
	public $customer;
	public $email;
	public $firstName;
	public $idTransaction;
	public $lastName;
	public $order;
	public $origin;
	public $state;

	public function __construct($headers = null, $body = null) {
		$config = Payplug::getConfig();


		if (is_null($config)) {
			throw new ParametersNotSetException();
		}
		if (is_null($body)) {
			$body = file_get_contents("php://input");
		}
		if (is_null($headers)) {
			$headers = getallheaders();
		}


		$headers = array_change_key_case($headers, CASE_UPPER);
		$signature = base64_decode($headers['PAYPLUG-SIGNATURE']);
		$publicKey = openssl_pkey_get_public($config->payplugPublicKey);

		$isValid = openssl_verify($body, $signature, $publicKey, OPENSSL_ALGO_SHA1);

		if ( ! $isValid) {
			throw new InvalidSignatureException();
		}


		$data = json_decode($body, true);

		$this->amount = $data['amount'];
		$this->customData = $data['custom_data'];
		$this->customer = $data['customer'];
		$this->email = $data['email'];
		$this->firstName = $data['first_name'];
		$this->idTransaction = $data['id_transaction'];
		$this->lastName = $data['last_name'];
		$this->order = $data['order'];
		$this->origin = $data['origin'];
		$this->state = $data['state'];
	}
}
