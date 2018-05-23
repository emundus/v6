<?php
/**
* Logs View para el Componente Securitycheckpro
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

// Chequeamos si el archivo está incluido en Joomla!
defined('_JEXEC') or die();
jimport( 'joomla.application.component.view' );
jimport( 'joomla.plugin.helper' );

// Load plugin language
$lang = JFactory::getLanguage();
$lang->load('plg_system_securitycheckpro');

/**
* Logs View
*
*/
class SecuritycheckprosViewFirewallConfig extends SecuritycheckproView
{

protected $state;

function __construct() 	{
	parent::__construct();
	
	JToolBarHelper::title( JText::_( 'Securitycheck Pro' ).' | ' .JText::_('COM_SECURITYCHECKPRO_WAF_CONFIG'), 'securitycheckpro' );	
}

/**
* Securitycheckpros FirewallConfig método 'display'
**/
function display($tpl = null)
{

// Filtro
$this->state= $this->get('State');
$lists = $this->state->get('filter.lists_search');

// Obtenemos el modelo
$model = $this->getModel();

//  Parámetros del plugin
$items= $model->getConfig();

// Pestaña Lists
$blacklist_elements= array();
$pagination_blacklist = null;
if ( (!is_null($items['blacklist'])) && ($items['blacklist'] != '') ) {
	$items['blacklist'] = str_replace(' ','',$items['blacklist']);
	$blacklist_elements = explode(',',trim($items['blacklist']));
	$blacklist_elements = $model->filter_data($blacklist_elements,$pagination_blacklist);
}

$dynamic_blacklist_elements= $model->get_dynamic_blacklist_ips();

$whitelist_elements= array();
$pagination_whitelist = null;

if ( (!is_null($items['whitelist'])) && ($items['whitelist'] != '') ) {	
	$items['whitelist'] = str_replace(' ','',$items['whitelist']);
	$whitelist_elements = explode(',',trim($items['whitelist']));
	$whitelist_elements = $model->filter_data($whitelist_elements,$pagination_whitelist);
}

// Información para la barra de navegación
$logs_pending = $model->LogsPending();
$trackactions_plugin_exists = $model->PluginStatus(8);
$this->logs_pending = $logs_pending;
$this->trackactions_plugin_exists = $trackactions_plugin_exists;

$this->blacklist_elements = $blacklist_elements;
$this->dynamic_blacklist_elements = $dynamic_blacklist_elements;
$this->whitelist_elements = $whitelist_elements;
$this->dynamic_blacklist = $items['dynamic_blacklist'];
$this->dynamic_blacklist_time = $items['dynamic_blacklist_time'];
$this->dynamic_blacklist_counter = $items['dynamic_blacklist_counter'];
$this->blacklist_email = $items['blacklist_email'];
$this->priority1 = $items['priority1'];
$this->priority2 = $items['priority2'];
$this->priority3 = $items['priority3'];
$this->priority4 = $items['priority4'];

// Pestaña methods
$methods= null;
if ( !is_null($items['methods']) ) {
	$this->methods = $items['methods'];
}

// Pestaña Mode
$mode= null;
if ( !is_null($items['mode']) ) {
	$this->mode = $items['mode'];
}

// Pestaña Logs
$logs_attacks= 1;
$scp_delete_period= 60;
$log_limits_per_ip_and_day = null;
$add_geoblock_logs = null;
$add_access_attempts_logs = null;

if ( !is_null($items['scp_delete_period']) ) {
	$scp_delete_period = $items['scp_delete_period'];	
}

if ( !is_null($items['logs_attacks']) ) {
	$logs_attacks = $items['logs_attacks'];	
}

if ( !is_null($items['log_limits_per_ip_and_day']) ) {
	$log_limits_per_ip_and_day = $items['log_limits_per_ip_and_day'];	
}

if ( !is_null($items['add_geoblock_logs']) ) {
	$add_geoblock_logs = $items['add_geoblock_logs'];	
}

if ( !is_null($items['add_access_attempts_logs']) ) {
	$add_access_attempts_logs = $items['add_access_attempts_logs'];	
}

$this->logs_attacks = $logs_attacks;
$this->scp_delete_period = $scp_delete_period;
$this->log_limits_per_ip_and_day = $log_limits_per_ip_and_day;
$this->add_geoblock_logs = $add_geoblock_logs;
$this->add_access_attempts_logs = $add_access_attempts_logs;

// Pestaña Redirection
$redirect_after_attack= null;
$redirect_options = null;

if ( !is_null($items['redirect_after_attack']) ) {
	$redirect_after_attack = $items['redirect_after_attack'];	
}

if ( !is_null($items['redirect_options']) ) {
	$redirect_options = $items['redirect_options'];	
}

$redirect_url = $items['redirect_url'];	
$custom_code = $items['custom_code'];

if ( is_null($custom_code) ) {
	$custom_code = "<h1 style=\"text-align:center;\">" . JText::_('COM_SECURITYCHECKPRO_403_ERROR') . "</h1>";
}

$this->redirect_after_attack = $redirect_after_attack;
$this->redirect_options = $redirect_options;
$this->redirect_url = $redirect_url;
$this->custom_code = $custom_code;

// Pestaña Second level
$second_level= null;
$second_level_redirect = null;
$second_level_limit_words = null;
$second_level_words = null;

if ( !is_null($items['second_level']) ) {
	$second_level = $items['second_level'];	
}

if ( !is_null($items['second_level_redirect']) ) {
	$second_level_redirect = $items['second_level_redirect'];	
}

if ( !is_null($items['second_level_limit_words']) ) {
	$second_level_limit_words = $items['second_level_limit_words'];	
}

if ( !is_null($items['second_level_words']) ) {
	$second_level_words = $items['second_level_words'];	
}

$this->second_level = $second_level;
$this->second_level_redirect = $second_level_redirect;
$this->second_level_limit_words = $second_level_limit_words;
$this->second_level_words = $second_level_words;

// Pestaña Email notifications
$email_active= null;
$email_subject = null;
$email_body = null;
$email_add_applied_rule = null;
$email_to = null;
$email_from_domain = null;
$email_from_name = null;
$email_max_number = null;

if ( !is_null($items['email_active']) ) {
	$email_active = $items['email_active'];	
}

if ( !is_null($items['email_subject']) ) {
	$email_subject = $items['email_subject'];	
}

if ( !is_null($items['email_body']) ) {
	$email_body = $items['email_body'];	
}

if ( !is_null($items['email_add_applied_rule']) ) {
	$email_add_applied_rule = $items['email_add_applied_rule'];	
}

if ( !is_null($items['email_to']) ) {
	$email_to = $items['email_to'];	
}

if ( !is_null($items['email_from_domain']) ) {
	$email_from_domain = $items['email_from_domain'];	
}

if ( !is_null($items['email_from_name']) ) {
	$email_from_name = $items['email_from_name'];	
}

if ( !is_null($items['email_max_number']) ) {
	$email_max_number = $items['email_max_number'];	
}

$this->email_active = $email_active;
$this->email_subject = $email_subject;
$this->email_body = $email_body;
$this->email_add_applied_rule = $email_add_applied_rule;
$this->email_to = $email_to;
$this->email_from_domain = $email_from_domain;
$this->email_from_name = $email_from_name;
$this->email_max_number = $email_max_number;

// Pestaña filter exceptions
$check_header_referer= null;
$check_base_64 = null;
$base64_exceptions = null;
$strip_tags_exceptions = null;
$duplicate_backslashes_exceptions = null;
$line_comments_exceptions = null;
$sql_pattern_exceptions = null;
$if_statement_exceptions = null;
$using_integers_exceptions = null;
$escape_strings_exceptions = null;
$lfi_exceptions = null;
$second_level_exceptions = null;
$exclude_exceptions_if_vulnerable = 1;
$strip_all_tags = null;
$tags_to_filter = null;

if ( !is_null($items['check_header_referer']) ) {
	$check_header_referer = $items['check_header_referer'];	
}

if ( !is_null($items['check_base_64']) ) {
	$check_base_64 = $items['check_base_64'];	
}

if ( !is_null($items['base64_exceptions']) ) {
	$base64_exceptions = $items['base64_exceptions'];	
}

if ( !is_null($items['strip_tags_exceptions']) ) {
	$strip_tags_exceptions = $items['strip_tags_exceptions'];	
}

if ( !is_null($items['duplicate_backslashes_exceptions']) ) {
	$duplicate_backslashes_exceptions = $items['duplicate_backslashes_exceptions'];	
}

if ( !is_null($items['line_comments_exceptions']) ) {
	$line_comments_exceptions = $items['line_comments_exceptions'];	
}

if ( !is_null($items['sql_pattern_exceptions']) ) {
	$sql_pattern_exceptions = $items['sql_pattern_exceptions'];	
}

if ( !is_null($items['if_statement_exceptions']) ) {
	$if_statement_exceptions = $items['if_statement_exceptions'];	
}

if ( !is_null($items['using_integers_exceptions']) ) {
	$using_integers_exceptions = $items['using_integers_exceptions'];	
}

if ( !is_null($items['lfi_exceptions']) ) {
	$lfi_exceptions = $items['lfi_exceptions'];	
}

if ( !is_null($items['escape_strings_exceptions']) ) {
	$escape_strings_exceptions = $items['escape_strings_exceptions'];	
}

if ( !is_null($items['second_level_exceptions']) ) {
	$second_level_exceptions = $items['second_level_exceptions'];	
}

if ( !is_null($items['strip_all_tags']) ) {
	$strip_all_tags = $items['strip_all_tags'];	
}

if ( !is_null($items['tags_to_filter']) ) {
	$tags_to_filter = $items['tags_to_filter'];	
}
				
$this->check_header_referer = $check_header_referer;
$this->check_base_64 = $check_base_64;
$this->base64_exceptions = $base64_exceptions;
$this->strip_tags_exceptions = $strip_tags_exceptions;
$this->duplicate_backslashes_exceptions = $duplicate_backslashes_exceptions;
$this->line_comments_exceptions = $line_comments_exceptions;
$this->sql_pattern_exceptions = $sql_pattern_exceptions;
$this->if_statement_exceptions = $if_statement_exceptions;
$this->using_integers_exceptions = $using_integers_exceptions;
$this->lfi_exceptions = $lfi_exceptions;
$this->escape_strings_exceptions = $escape_strings_exceptions;
$this->second_level_exceptions = $second_level_exceptions;
$this->exclude_exceptions_if_vulnerable = $exclude_exceptions_if_vulnerable;
$this->strip_all_tags = $strip_all_tags;
$this->tags_to_filter = $tags_to_filter;

// Pestaña user session protection
$session_protection_active= null;
$session_hijack_protection = null;
$track_failed_logins = null;
$write_log = null;
$logins_to_monitorize = null;
$actions_failed_login = null;
$email_on_admin_login = null;
$forbid_admin_frontend_login = null;
$forbid_new_admins = null;

if ( !is_null($items['session_protection_active']) ) {
	$session_protection_active = $items['session_protection_active'];	
}

if ( !is_null($items['session_hijack_protection']) ) {
	$session_hijack_protection = $items['session_hijack_protection'];	
}

if ( !is_null($items['track_failed_logins']) ) {
	$track_failed_logins = $items['track_failed_logins'];	
}

if ( !is_null($items['write_log']) ) {
	$write_log = $items['write_log'];	
}

if ( !is_null($items['logins_to_monitorize']) ) {
	$logins_to_monitorize = $items['logins_to_monitorize'];	
}

if ( !is_null($items['actions_failed_login']) ) {
	$actions_failed_login = $items['actions_failed_login'];	
}

if ( !is_null($items['email_on_admin_login']) ) {
	$email_on_admin_login = $items['email_on_admin_login'];	
}

if ( !is_null($items['forbid_admin_frontend_login']) ) {
	$forbid_admin_frontend_login = $items['forbid_admin_frontend_login'];	
}

if ( !is_null($items['forbid_new_admins']) ) {
	$forbid_new_admins = $items['forbid_new_admins'];	
}

$this->session_protection_active = $session_protection_active;
$this->session_hijack_protection = $session_hijack_protection;
$this->track_failed_logins = $track_failed_logins;
$this->write_log = $write_log;
$this->logins_to_monitorize = $logins_to_monitorize;
$this->actions_failed_login = $actions_failed_login;
$this->session_protection_groups = $items['session_protection_groups'];
$this->email_on_admin_login = $email_on_admin_login;
$this->forbid_admin_frontend_login = $forbid_admin_frontend_login;
$this->forbid_new_admins = $forbid_new_admins;

// Pestaña Geoblock
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


// Última vez que se actualizó la bbdd geoipv2
$geoip_database_update = $model->get_latest_database_update();

$items_geoblock = $model->load_geoblock_data();

// Inicializamos las variables
$countries= array();
$continents= array();

if ( (!is_null($items_geoblock)) && ($items_geoblock['geoblockcountries'] != '') ) {
	if(strstr($items_geoblock['geoblockcountries'], ',')) {
		$countries = explode(',', $items_geoblock['geoblockcountries']);
	} else {
		$countries = array($items_geoblock['geoblockcountries']);
	}
}

if ( (!is_null($items_geoblock)) && ($items_geoblock['geoblockcontinents'] != '') ) {
	if(strstr($items_geoblock['geoblockcontinents'], ',')) {
		$continents = explode(',', $items_geoblock['geoblockcontinents']);
	} else {
		$continents = array($items_geoblock['geoblockcontinents']);
	}
}

$this->countries = 	$countries;
$this->continents = $continents;
$this->allContinents = $allContinents;
$this->allCountries = $allCountries;
$this->geoip_database_update = $geoip_database_update;

// Pestaña upload scanner
$upload_scanner_enabled = 0;
$check_multiple_extensions = 0;
$extensions_blacklist  = "php,js,exe,xml";
$delete_files = 0;
$actions_upload_scanner = 0;

$upload_scanner_enabled = $items['upload_scanner_enabled'];	
$check_multiple_extensions = $items['check_multiple_extensions'];	
$extensions_blacklist = $items['extensions_blacklist'];
$delete_files = $items['delete_files'];
$actions_upload_scanner = $items['actions_upload_scanner'];

$this->upload_scanner_enabled = $upload_scanner_enabled;
$this->check_multiple_extensions = $check_multiple_extensions;
$this->extensions_blacklist = $extensions_blacklist;
$this->delete_files = $delete_files;
$this->actions_upload_scanner = $actions_upload_scanner;

// Pestaña spam protection
$check_if_user_is_spammer= null;
$spammer_action=null;
$spammer_write_log=null;
$spammer_limit=3;
$plugin_installed=false;

// Chequeamos si el plugin 'Spam protection' está instalado
$plugin_installed = $model->is_plugin_installed('system', 'securitycheck_spam_protection');

if ( !is_null($items['check_if_user_is_spammer']) ) {
	$check_if_user_is_spammer = $items['check_if_user_is_spammer'];	
}
if ( !is_null($items['spammer_action']) ) {
	$spammer_action = $items['spammer_action'];	
}
if ( !is_null($items['spammer_write_log']) ) {
	$spammer_write_log = $items['spammer_write_log'];	
}
if ( !is_null($items['spammer_limit']) ) {
	$spammer_limit = $items['spammer_limit'];	
}
if ( !is_null($items['spammer_what_to_check']) ) {
	$spammer_what_to_check = $items['spammer_what_to_check'];	
}

$this->check_if_user_is_spammer = $check_if_user_is_spammer;
$this->spammer_action = $spammer_action;
$this->spammer_write_log = $spammer_write_log;
$this->spammer_limit = $spammer_limit;
$this->plugin_installed = $plugin_installed;
$this->spammer_what_to_check = $spammer_what_to_check;

// Pestaña url inspector
// Esta el plugin habilitado?
$url_inspector_enabled= $model->PluginStatus(7);

// Extraemos los elementos que nos interesan...
$inspector_forbidden_words= null;
$write_log_inspector= null;
$action_inspector= 2;
$send_email_inspector = 0;

if ( !is_null($items['inspector_forbidden_words']) ) {
	$inspector_forbidden_words = $items['inspector_forbidden_words'];	
}

if ( !is_null($items['write_log_inspector']) ) {
	$write_log_inspector = $items['write_log_inspector'];	
}

if ( !is_null($items['action_inspector']) ) {
	$action_inspector = $items['action_inspector'];	
}

if ( !is_null($items['send_email_inspector']) ) {
	$send_email_inspector = $items['send_email_inspector'];	
}

$this->inspector_forbidden_words = $inspector_forbidden_words;
$this->write_log_inspector = $write_log_inspector;
$this->action_inspector = $action_inspector;
$this->send_email_inspector = $send_email_inspector;
$this->url_inspector_enabled = $url_inspector_enabled;

// Pestaña track actions
$delete_period= 0;
$ip_logging=null;
$plugin_trackactions_installed=false;
$loggable_extensions=null;

// Chequeamos si el plugin 'Track actions' está instalado
$plugin_trackactions_installed = $model->is_plugin_installed('system', 'trackactions');

if ( !is_null($items['delete_period']) ) {
	$delete_period = $items['delete_period'];
}
if ( !is_null($items['ip_logging']) ) {
	$ip_logging = $items['ip_logging'];	
}
if ( !is_null($items['loggable_extensions']) ) {
	$loggable_extensions = $items['loggable_extensions'];	
}

$this->delete_period = $delete_period;
$this->ip_logging = $ip_logging;
$this->plugin_trackactions_installed = $plugin_trackactions_installed;
$this->loggable_extensions = $loggable_extensions;


// Cargamos las librerías para extraer información de las ips
require_once JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/helpers/ip.php';
		
$ipmodel = new SecuritycheckProsModelIP;
							
// Extraemos la geolocalización de las distintas listas...
$blacklist_elements_geolocation= array();
$whitelist_elements_geolocation= array();
$dynamic_elements_geolocation= array();

if ( !is_null($blacklist_elements) ) {
	foreach($blacklist_elements as $element) {
		// Extraemos la ip de cada elemento, eliminando los comodines si es necesario
		$ip = $model->change_wildcards($element);
		// Si tenemos formato CIDR extraemos, por ejemplo, la última ip del rango para la geolocalización
		if ( ( strstr($element,"/") != false ) ) {
			$ip_range_info = $ipmodel->get_ip_info($element);
			$ip = $ip_range_info["hostmax"];			
		}
		$is_valid = filter_var($ip,FILTER_VALIDATE_IP);		
		if ( $is_valid ) {
			// Extraemos la geolocalización
			$geo = $model->geolocation($ip);
			$blacklist_elements_geolocation[] = $geo;	
		}
		
	}
}

if ( !is_null($whitelist_elements) ) {
	foreach($whitelist_elements as $element) {
		// Extraemos la ip de cada elemento, eliminando los comodines si es necesario
		$ip = $model->change_wildcards($element);
		// Si tenemos formato CIDR extraemos, por ejemplo, la ltima ip del rango para la geolocalizacin
		if ( ( strstr($element,"/") != false ) ) {
			$ip_range_info = $ipmodel->get_ip_info($element);
			$ip = $ip_range_info["hostmax"];			
		}
		$is_valid = filter_var($ip,FILTER_VALIDATE_IP);
		if ( $is_valid ) {
			// Extraemos la geolocalización
			$geo = $model->geolocation($ip);
			$whitelist_elements_geolocation[] = $geo;	
		}
	}
}

if ( !is_null($dynamic_blacklist_elements) ) {
	foreach($dynamic_blacklist_elements as $element) {
		$is_valid = filter_var($element,FILTER_VALIDATE_IP);
		if ( $is_valid ) {
			// Extraemos la geolocalización
			$geo = $model->geolocation($element);
			$dynamic_elements_geolocation[] = $geo;	
		}
	}
}

// Añadimos la información
$this->blacklist_elements_geolocation = $blacklist_elements_geolocation;
$this->whitelist_elements_geolocation = $whitelist_elements_geolocation;
$this->dynamic_elements_geolocation = $dynamic_elements_geolocation;


// Añadimos también la paginación (comparamos las dos paginaciones y asignamos la mayor)
if ( (!is_null($pagination_blacklist)) && (!is_null($pagination_whitelist)) ) {
	if ( ($blacklist_elements) > ($whitelist_elements) ) {
		$this->pagination = $pagination_blacklist;
	} else {
		$this->pagination = $pagination_whitelist;				
	}
} else if ( !is_null($pagination_blacklist) ) {
	$this->pagination = $pagination_blacklist;	
} else if ( !is_null($pagination_whitelist) ) {
	$this->pagination = $pagination_whitelist;	
}

parent::display($tpl);
}
}