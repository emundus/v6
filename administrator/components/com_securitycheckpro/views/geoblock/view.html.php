<?php
/**
* Geoblock View para el Componente Securitycheckpro
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

// Chequeamos si el archivo está incluido en Joomla!
defined('_JEXEC') or die();
jimport( 'joomla.application.component.view' );
jimport( 'joomla.plugin.helper' );


/**
* Geoblocking View
*
*/
class SecuritycheckprosViewGeoblock extends SecuritycheckproView
{

protected $state;

function __construct() 	{
	parent::__construct();
	
	JToolBarHelper::title( JText::_( 'Securitycheck Pro' ).' | ' .JText::_('COM_SECURITYCHECKPRO_GEOBLOCK_LABEL'), 'securitycheckpro' );	
}

/**
* Securitycheckpros Geoblock método 'display'
**/
function display($tpl = null)
{

$allContinents = array(
	'AF' => 'Africa',
	'NA' => 'North America',
	'SA' => 'South America',
	'AN' => 'Antartica',
	'AS' => 'Asia',
	'EU' => 'Europe',
	'OC' => 'Oceania'
);

$allCountries = array(
"" => "", "AP" => "Asia/Pacific Region", "EU" => "Europe", "AD" => "Andorra", "AE" => "United Arab Emirates",
"AF" => "Afghanistan", "AG" => "Antigua and Barbuda", "AI" => "Anguilla", "AL" => "Albania",  "AM" => "Armenia",
"AN" => "Netherlands Antilles", "AO" => "Angola", "AQ" => "Antarctica", "AR" => "Argentina", "AS" => "American Samoa",
"AT" => "Austria", "AU" => "Australia", "AW" => "Aruba", "AZ" => "Azerbaijan", "BA" => "Bosnia and Herzegovina",
"BB" => "Barbados", "BD" => "Bangladesh", "BE" => "Belgium", "BF" => "Burkina Faso", "BG" => "Bulgaria", "BH" =>"Bahrain",
"BI" => "Burundi", "BJ" => "Benin", "BM" => "Bermuda", "BN" => "Brunei Darussalam", "BO" => "Bolivia", "BR" => "Brazil",
"BS" => "Bahamas", "BT" => "Bhutan", "BV" => "Bouvet Island", "BW" => "Botswana", "BY" => "Belarus", "BZ" => "Belize",
"CA" => "Canada", "CC" => "Cocos (Keeling) Islands", "CD" => "Congo, The Democratic Republic of the",
"CF" => "Central African Republic", "CG" => "Congo", "CH" => "Switzerland", "CI" => "Cote D'Ivoire", "CK" => "Cook Islands",
"CL" => "Chile", "CM" => "Cameroon", "CN" => "China", "CO" => "Colombia", "CR" => "Costa Rica", "CU" => "Cuba", "CV" => "Cape Verde",
"CX" => "Christmas Island", "CY" => "Cyprus", "CZ" => "Czech Republic", "DE" => "Germany", "DJ" => "Djibouti",
"DK" => "Denmark", "DM" => "Dominica", "DO" => "Dominican Republic", "DZ" => "Algeria", "EC" => "Ecuador", "EE" => "Estonia",
"EG" => "Egypt", "EH" => "Western Sahara", "ER"=> "Eritrea", "ES" => "Spain", "ET" => "Ethiopia", "FI" => "Finland", "FJ" => "Fiji",
"FK" => "Falkland Islands (Malvinas)", "FM" => "Micronesia, Federated States of", "FO" => "Faroe Islands",
"FR" => "France", "FX" => "France, Metropolitan", "GA" => "Gabon", "GB" => "United Kingdom",
"GD" => "Grenada", "GE" => "Georgia", "GF" => "French Guiana", "GH" => "Ghana", "GI" => "Gibraltar", "GL" => "Greenland",
"GM" => "Gambia", "GN" => "Guinea", "GP" => "Guadeloupe", "GQ" => "Equatorial Guinea", "GR" => "Greece", "GS" => "South Georgia and the South Sandwich Islands", "GT" => "Guatemala", "GU" => "Guam", "GW" => "Guinea-Bissau", "GY" => "Guyana", "HK" => "Hong Kong", "HM" => "Heard Island and McDonald Islands", 
"HN" => "Honduras", "HR" => "Croatia", "HT" => "Haiti", "HU" => "Hungary", "ID" => "Indonesia", "IE" => "Ireland", "IL" => "Israel", "IN" => "India",
"IO" => "British Indian Ocean Territory", "IQ" => "Iraq", "IR" => "Iran, Islamic Republic of",
"IS" => "Iceland", "IT" => "Italy", "JM" => "Jamaica", "JO" => "Jordan", "JP" => "Japan", "KE" => "Kenya", "KG" => "Kyrgyzstan",
"KH" => "Cambodia", "KI" => "Kiribati", "KM" => "Comoros", "KN" => "Saint Kitts and Nevis", "KP" => "Korea, Democratic People's Republic of",
"KR" => "Korea, Republic of", "KW" => "Kuwait", "KY" => "Cayman Islands",
"KZ" => "Kazakhstan", "LA" => "Lao People's Democratic Republic", "LB" => "Lebanon", "LC" => "Saint Lucia",
"LI" => "Liechtenstein", "LK" => "Sri Lanka", "LR" => "Liberia", "LS" => "Lesotho", "LT" => "Lithuania", "LU" => "Luxembourg",
"LV" => "Latvia", "LY" => "Libyan Arab Jamahiriya", "MA" => "Morocco", "MC" => "Monaco", "MD" => "Moldova, Republic of",
"MG" => "Madagascar", "MH" => "Marshall Islands", "MK" => "Macedonia",
"ML" => "Mali", "MM" => "Myanmar", "MN" => "Mongolia", "MO" => "Macau", "MP" => "Northern Mariana Islands",
"MQ" => "Martinique", "MR" => "Mauritania", "MS" => "Montserrat", "MT" => "Malta", "MU" => "Mauritius", "MV" => "Maldives",
"MW" => "Malawi", "MX" => "Mexico", "MY" => "Malaysia", "MZ" => "Mozambique", "NA" => "Namibia", "NC" => "New Caledonia",
"NE" => "Niger", "NF" => "Norfolk Island", "NG" => "Nigeria", "NI" => "Nicaragua", "NL" => "Netherlands", "NO" => "Norway",
"NP" => "Nepal", "NR" => "Nauru", "NU" => "Niue", "NZ" => "New Zealand", "OM" => "Oman", "PA" => "Panama", "PE" => "Peru", "PF" => "French Polynesia",
"PG" => "Papua New Guinea", "PH" => "Philippines", "PK" => "Pakistan", "PL" => "Poland", "PM" => "Saint Pierre and Miquelon",
"PN" => "Pitcairn Islands", "PR" => "Puerto Rico", "PS" => "Palestinian Territory", "PT" => "Portugal", "PW" => "Palau", "PY" => "Paraguay",
"QA" => "Qatar", "RE" => "Reunion", "RO" => "Romania",
"RU" => "Russian Federation", "RW" => "Rwanda", "SA" => "Saudi Arabia", "SB" => "Solomon Islands",
"SC" => "Seychelles", "SD" => "Sudan", "SE" => "Sweden", "SG" => "Singapore", "SH" => "Saint Helena", "SI" => "Slovenia",
"SJ" => "Svalbard and Jan Mayen", "SK" => "Slovakia", "SL" => "Sierra Leone", "SM" => "San Marino", "SN" => "Senegal",
"SO" => "Somalia", "SR" => "Suriname", "ST" => "Sao Tome and Principe", "SV" => "El Salvador", "SY" => "Syrian Arab Republic",
"SZ" => "Swaziland", "TC" => "Turks and Caicos Islands", "TD" => "Chad", "TF" => "French Southern Territories",
"TG" => "Togo", "TH" => "Thailand", "TJ" => "Tajikistan", "TK" => "Tokelau", "TM" => "Turkmenistan",
"TN" => "Tunisia", "TO" => "Tonga", "TL" => "Timor-Leste", "TR" => "Turkey", "TT" => "Trinidad and Tobago", "TV" => "Tuvalu",
"TW" => "Taiwan", "TZ" => "Tanzania, United Republic of", "UA" => "Ukraine",
"ug" => "Uganda", "UM" => "United States Minor Outlying Islands", "US" => "United States", "UY" => "Uruguay",
"UZ" => "Uzbekistan", "VA" => "Holy See (Vatican City State)", "VC" => "Saint Vincent and the Grenadines",
"VE" => "Venezuela", "VG" => "Virgin Islands, British", "VI" => "Virgin Islands, U.S.",
"VN" => "Vietnam", "VU" => "Vanuatu", "WF" => "Wallis and Futuna", "WS" => "Samoa", "YE" => "Yemen", "YT" => "Mayotte",
"RS" => "Serbia", "ZA" => "South Africa", "ZM" => "Zambia", "ME" => "Montenegro", "ZW" => "Zimbabwe",
"A1" => "Anonymous Proxy", "A2" => "Satellite Provider", "O1" => "Other",
"AX" => "Aland Islands", "GG" => "Guernsey", "IM" => "Isle of Man", "JE" => "Jersey", "BL" => "Saint Barthelemy", "MF" => "Saint Martin"
);

// Obtenemos el modelo...
$model = $this->getModel();

//  ... y la información almacenada
$items = $model->getConfig();
// Última vez que se actualizó la bbdd geoipv2
$geoip_database_update = $model->get_latest_database_update();

// Inicializamos las variables
$countries= array();
$continents= array();

if ( (!is_null($items)) && ($items['geoblockcountries'] != '') ) {
	if(strstr($items['geoblockcountries'], ',')) {
		$countries = explode(',', $items['geoblockcountries']);
	} else {
		$countries = array($items['geoblockcountries']);
	}
}

if ( (!is_null($items)) && ($items['geoblockcontinents'] != '') ) {
	if(strstr($items['geoblockcontinents'], ',')) {
		$continents = explode(',', $items['geoblockcontinents']);
	} else {
		$continents = array($items['geoblockcontinents']);
	}
}


// Extraemos los elementos de las distintas listas y los ponemos en el template
$this->assign('countries',		$countries);
$this->assign('continents',		$continents);
$this->assign('allContinents',		$allContinents);
$this->assign('allCountries',		$allCountries);
$this->assign('geoip_database_update',$geoip_database_update);

parent::display($tpl);
}
}