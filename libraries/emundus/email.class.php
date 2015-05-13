<?php
class Email{
	##########################################################################################
	######################################## PROPERTIES ######################################
	##########################################################################################
		private $email = '';
		private $account = '';
		private $domain = '';
		private $timeout = 30; // en secondes
		private $complete_check = FALSE;
		public $checkEmail_results = '';
	##########################################################################################
	##########################################################################################
	
	
	
	##########################################################################################
	############################# METHODS : CONSTRUCT/DESTRUCT ###############################
	##########################################################################################
		/**
		* Constructeur
		* Email requis
		* Possibilité de passer le timout et le type de vérification
		*/
		public function __construct( $email, $timeout = 30, $complete_check = FALSE ){
			// Ajout de l'email
			$this->setEmail( $email );
		
			// Ajout du domain de l'email
			$this->setDomain( $email );
			
			// Ajout du compte de l'email
			$this->setAccount( $email );
			
			// Appel setter TIMEOUT si et seulement si :
			//		- Valeur fournit
			//		- Valeur de type numerique
			// 		- Valeur differente du timeout actuel
			if( isset($timeout) && !empty($timeout) && is_numeric($timeout) && $timeout != $this->timeout){
				$this->setTimeout( $timeout );
			}
			
			// Appel du setter TYPE_DE_VERIFICATION si et seulement si :
			//		- Valeur fournit
			//		- Valeur de type booleen
			// 		- Valeur differente du type actuel
			if( isset($complete_check) && !empty($complete_check) && is_bool($complete_check) && $complete_check != $this->complete_check ){
				$this->setVerification( $complete_check );
			}
			// Verification de l'email
			$this->checkEmail();
		}
	##########################################################################################
	##########################################################################################
	
	
	
	##########################################################################################
	############################### METHODS : GETTER/SETTER ##################################
	##########################################################################################
		/**
		* Ajout de l'adresse email dans l'objet
		*/
		public function setEmail( $email ){
			$this->email = $email;
		}
		
		/**
		* Recuper et retourne l'adresse email
		*/
		public function getEmail(){
			return $this->email;
		}
		
		/**
		* Ajout du timeout pour les vérification via socket (en secondes)
		*/
		public function setTimeout( $timeout ){
			$this->timout = $timeout;
		}
		
		/**
		* Recupere et retourne le nombre de secondes définits pour le timeout
		*/
		public function getTimeout(){
			return $this->timeout;
		}
		
		/**
		* Ajout du type de vérification nécessaire
		*/
		public function setVerification( $complete ){
			if( $complete ){ 
				$this->complete_check = TRUE;
			}else{
				$this->complete_check = FALSE;
			}
		}
		
		/**
		* Recupere et retourne le type de vérification paramêtré
		*/
		public function getTypeVerification(){
			if(  $this->complete_check ){
				return "COMPLETE_CHECK";
			}else{
				return "SIMPLE_CHECK";
			}
		}
		
		/**
		* Ajout du domaine de l'email
		*/
		public function setDomain( $email ){
			$temp = explode("@", $email);
			$this->domain = $temp[1];
			unset($temp);
		}
		
		/**
		* Recupere et retourne le nom de domaine de l'Email
		*/
		public function getDomain(){
			return $this->domain;
		}
		
		/**
		* Ajout du compte de l'email
		*/
		public function setAccount( $email ){
			$temp = explode("@", $email);
			$this->account = $temp[0];
			unset($temp);
		}
		
		/**
		* Recupere et retourne le compte de l'email
		*/
		public function getAccount(){
			return $this->account;
		}
	##########################################################################################
	##########################################################################################
	
	/**
	* Verifie la validité de l'email
	*/
	public function checkEmail(){
		if( $this->checkEmailSyntax($this->email) ){
			$results['checkEmailSyntax'] = 1;
		}else{
			$results['checkEmailSyntax'] = 0;
		}

			// Effectué uniquement si le test de syntaxe est OK
		if( $results['checkEmailSyntax'] ){
			// HOST
			if( $this->checkEmailWithHost() ){
				$results['gethostbyname'] = 1;
			}else{
				$results['gethostbyname'] = 0;
			}

			if( $this->checkEmailWith_Dnsrr($this->domain) ){
				$results['checkEmailWith_Dnsrr'] = 1;
			}else{
				$results['checkEmailWith_Dnsrr'] = 0;
			}

			// Test DNS poussé (Windows inclu)
			if( $this->customCheckEmailWith_Dnsrr($this->domain) ){
				$results['customCheckEmailWith_Dnsrr'] = 1;
			}else{
				$results['customCheckEmailWith_Dnsrr'] = 0;
			}
			
			// Test MX poussé (Windows exclu)
			if( $this->customCheckEmailWith_Mxrr($this->domain) ){
				$results['customCheckEmailWith_Mxrr'] = 1;
			}else{
				$results['customCheckEmailWith_Mxrr'] = 0;
			}

			if( $this->complete_check ){ 
				if( ($results['gethostbyname'] && $results['customCheckEmailWith_Dnsrr'] )
				||
				($results['checkEmailWith_Dnsrr'] && $results['customCheckEmailWith_Mxrr'] )
				){
					$results['COMPLETE_CHECK'] = 1;
					$results['CheckEmailWith_Socket'] = $this->CheckEmailWith_Socket($this->email, $this->domain, $timeout);
				}
			}else{
				$results['COMPLETE_CHECK'] = 0;
				$results['CheckEmailWith_Socket'] = "";
			}
		}
		$this->checkEmail_results = $results;
	}
	
	/**
	² Verification via :
		- connexion au serveur par socket 
		- recuperation et traitement des codes retours
	*/
	private function CheckEmailWith_Socket(){
		$fsock_flow = @fsockopen($this->domain, 25, $errno, $errstr, $this->timeout);

		if( $fsock_flow ){
			$Out = fgets($fsock_flow, 1024);
			if( preg_match("/^220/", $Out) != 0 &&
				preg_match("/^220/", $Out) != FALSE
			){
				fwrite($fsock_flow, "HELO ".$this->domain."\r\n");
				$Out = fgets( $fsock_flow, 1024 );

				fwrite($fsock_flow, "MAIL FROM: <".$this->email.">\r\n");
				$From = fgets( $fsock_flow, 1024 );

				fwrite($fsock_flow, "RCPT TO: <".$this->email.">\r\n");
				$To = fgets( $fsock_flow, 1024 );

				fwrite($fsock_flow, "QUIT\r\n");

				fclose($fsock_flow); // Fermeture flux de socket

				// Si le code renvoyé par la commande RCPT TO est 250 ou 251 (cf: RFC)
				// Alors l'adresse existe
				if( (	preg_match("/^250/", $To) != false &&
						preg_match("/^250/", $To) > 0
					)
					||
					(	preg_match("/^251/", $To) != false &&
						preg_match("/^251/", $To) > 0
					)
				){
					// Adresse acceptée par le serveur
					return "ACCEPT";
								
				}else{
					// Adresse rejetée par le serveur
					return "REJECT";
				}
			}else{
				// Le serveur n'a pas répondu
				return "NO_RESPONSE";
			}
		}else{
			if( $errno == 0 ){
				// Erreur de connexion via les sockets
				return "SOCKET_ERROR";
			}else{
				return "NO_CONNECTION";
			}
		}
    }
	
	/**
	* Verification de l'existence du serveur via un test de récupération des enregistrements "MX"
	*/
	private function customCheckEmailWith_Mxrr(){
		if( !getmxrr($this->domain, $mxhosts) ){
			return FALSE;
		}
		return TRUE;
    }
	
	/**
	* Verification de l'existence du serveur via requête NSLOOKUP
	*/
	private function customCheckEmailWith_Dnsrr() {
		if( !empty($this->domain) ){
			$recType = "MX";

			exec("nslookup -type=".$recType." ".$this->domain, $output);

			foreach($output as $line){
				if( preg_match("/^".$this->domain."/", $line) ){
					return TRUE;
				}
			}
		}
		return FALSE;
    }
	
	/**
	* Verification de l'existence du serveur via le test de la résolution de nom
	*/
	private function checkEmailWith_Dnsrr(){
		if( checkdnsrr($this->domain,'MX') ){
			return TRUE;
		}
		return FALSE;
    }
	
	/**
	* 
	*/
	private function checkEmailWithHost(){
		if( gethostbyname($this->domain) != $this->domain ){
			return TRUE;
		}else{
			return FALSE;
		}
	}
	
	/**
	* Verifie la syntaxe de l'adresse email
	*/
	private function checkEmailSyntax(){
		/* Only for PHP 5 and > */
		if( function_exists( 'filter_var' ) ){
			if( filter_var($this->email, FILTER_VALIDATE_EMAIL) ){
				return TRUE;
			}
		}else{
			if(preg_match("/^( [a-zA-Z0-9] )+( [a-zA-Z0-9\._-] )*@( [a-zA-Z0-9_-] )+( [a-zA-Z0-9\._-] +)+$/" , $this->email)){
				return FALSE;
			}
		}
		return FALSE;
    }
}
?>