<?php
// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

class TranslatorDefault {
	protected $script = NULL;
	protected $defaultLanguage;
	
	public function __construct() {
		
	}
	
	public function installScripts ($from, $to) {
		$script = "var translator = {'from' : '".strtolower($from). "','to' : '".strtolower($to). "'};\n";
		
		$document = JFactory::getDocument();
		$document->addScriptDeclaration($script,'text/javascript');
		
		
		if ($this->script != NULL){
			$document = JFactory::getDocument();
			$document->addScript('components/com_falang/assets/js/'.$this->script);
		}
	}
	
	public function languageCodeToISO ($language){
		$l = strtolower($language);
		return TranslatorDefault::$languageCodeInISO[$l]; 
	}
	
	public function getDefaultLanguage(){
		return $this->defaultLanguage;
	}
	
	static private $languageCodeInISO = array (
			'af-za' => 'AF',	// Afrikaans
			'sq-al' => 'AL', 	// Albanian
			'ar-aa' => 'AR', 	// Arabic unitag
			'hy-am' => 'HY', 	// Armenian
			'az-az' => 'AZ', 	// Azeri
			'eu-es' => 'EU', 	// Basque
			'be-by' => 'BY',	// Belarusian
			'bn-bd' => 'BN',	// Bengali
			'bs-ba' => 'BS', 	// Bosnian
			'bg-bg' => 'BU', 	// Bulgarian
			'ca-es' => 'CA',	// Catalan
			'ckb-iq' => 'KU', 	// Central Kurdish
			'zh-cn' => 'CN', 	// Chine simplified
			'zh-tw' => 'TW',	// Chinese traditional
			'hr-hr' => 'CR', 	// Croation
			'cs-cz' => 'CS',	// Czech
			'da-dk' => 'DK', 	// Danish
			// 'prs-AF' => '',		// Dari Persian
			'nl-nl' => 'NL', 	// Dutch
			'en-au' => 'EN', 	// English Australia
			// 'en-CA' => '',		// English Canadian
			'en-gb' => 'EN',	// Queen's English
			'en-us' => 'EN', 	// English US
			'eo-xx' => 'EO', 	// Esperanto
			'et-ee' => 'ET', 	// Estonian
			'fi-fi' => 'FI', 	// Finnish
			'nl-be' => 'NL', 	// Flemish
			'fr-fr' => 'FR', 	// French
			// 'fr-CA' => '',		// French Canadian
			'gl-es' => 'GZ', 	// Galcian
			'ka-ge' => 'KA',	// Georgian
			'de-de' => 'DE', 	// German
			'de-at' => 'AT',	// German
			'el-gr' => 'GR', 	// Greek
			'he-il' => 'IL',	// Hebrew
			'hi-in' => 'HI',	// Hindi
			'hu-hu' => 'HU', 	// Hungarian
			'id-id' => 'ID', 	// Indonesian
			'ga-IE' => 'GA',		// Irish
			'it-it' => 'IT', 	// Italian
			'ja-jp' => 'JP',	// Japanese
			'km-kh' => 'KM', 	// Khmer
			'ko-kr' => 'KR', 	// Korean
			'lo-la' => 'LO', 	// Loation
			'lv-lv' => 'LV', 	// Latvian
			'lt-lt' => 'LT', 	// Lithuanian
			'mk-mk' => 'MK',	// Macedonian
			'ml-in' => 'ML', 	// Malayalam
			'mn-mn' => 'MN',	// Mongolian
			'ms-MY' => 'MS',		// Malay
			'srp-ME' => 'SRP',		// Montenegrin
			'nb-no' => 'NO',	// Norwegian
			'nn-no' => 'NO', 	// Norwegian
			'fa-ir' => 'FA',	// Persian
			'pl-pl' => 'PL',	// Polish
			'pt-br'	=> 'BR',	// Portuguese Brazil
			'pt-pt' => 'PT',	// Portuuese
			'ro-ro' => 'RO',	// Romanian
			'ru-ru' => 'RU', 	// Russian
			'gd-gb' => 'GD', 	// Scottish Gaelic
			'sr-rs'	=> 'SR',	// Serbian Cyrillic
			'sr-yu' => 'SR',	// Serbian Latin
			'sk-sk' => 'SK', 	// Slovak
			'es-es' => 'ES',	// Spanish
			'sw-ke' => 'SI',	// Swahili
			'sv-se' => 'SW', 	// Swedish
			'sy-iq' => 'SYR',	// Syriac
			'ta-in' => 'TA', 	// Tamil
			'th-th' => 'TH',	// Thai
			'tr-tr' => 'TR',	// Turkish
			'uk-ua' => 'UA', 	// Ukrainian
			'ur-pk' => 'UR', 	// Urdu
			'ug-cn'	=> 'UG',	// Uyghur
			'vi-vn' => 'VU', 	// Vietnamese
			'cy-gb' => 'CY', 	// Welsh
	);
	
}