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
class plgHikashoppaymentPaybox extends hikashopPaymentPlugin
{
	var $accepted_currencies = array(
		978 => 'EUR'
	);
	var $country_codes = array(
		'AD' => '020',
		'AE' => '784',
		'AF' => '004',
		'AG' => '028',
		'AI' => '660',
		'AL' => '008',
		'AM' => '051',
		'AO' => '024',
		'AQ' => '010',
		'AR' => '032',
		'AS' => '016',
		'AT' => '040',
		'AU' => '036',
		'AW' => '533',
		'AX' => '248',
		'AZ' => '031',
		'BA' => '070',
		'BB' => '052',
		'BD' => '050',
		'BE' => '056',
		'BF' => '854',
		'BG' => '100',
		'BH' => '048',
		'BI' => '108',
		'BJ' => '204',
		'BL' => '652',
		'BM' => '060',
		'BN' => '096',
		'BO' => '068',
		'BQ' => '535',
		'BR' => '076',
		'BS' => '044',
		'BT' => '064',
		'BV' => '074',
		'BW' => '072',
		'BY' => '112',
		'BZ' => '084',
		'CA' => '124',
		'CC' => '166',
		'CD' => '180',
		'CF' => '140',
		'CG' => '178',
		'CH' => '756',
		'CI' => '384',
		'CK' => '184',
		'CL' => '152',
		'CM' => '120',
		'CN' => '156',
		'CO' => '170',
		'CR' => '188',
		'CU' => '192',
		'CV' => '132',
		'CW' => '531',
		'CX' => '162',
		'CY' => '196',
		'CZ' => '203',
		'DE' => '276',
		'DJ' => '262',
		'DK' => '208',
		'DM' => '212',
		'DO' => '214',
		'DZ' => '012',
		'EC' => '218',
		'EE' => '233',
		'EG' => '818',
		'EH' => '732',
		'ER' => '232',
		'ES' => '724',
		'ET' => '231',
		'FI' => '246',
		'FJ' => '242',
		'FK' => '238',
		'FM' => '583',
		'FO' => '234',
		'FR' => '250',
		'GA' => '266',
		'GB' => '826',
		'GD' => '308',
		'GE' => '268',
		'GF' => '254',
		'GG' => '831',
		'GH' => '288',
		'GI' => '292',
		'GL' => '304',
		'GM' => '270',
		'GN' => '324',
		'GP' => '312',
		'GQ' => '226',
		'GR' => '300',
		'GS' => '239',
		'GT' => '320',
		'GU' => '316',
		'GW' => '624',
		'GY' => '328',
		'HK' => '344',
		'HM' => '334',
		'HN' => '340',
		'HR' => '191',
		'HT' => '332',
		'HU' => '348',
		'ID' => '360',
		'IE' => '372',
		'IL' => '376',
		'IM' => '833',
		'IN' => '356',
		'IO' => '086',
		'IQ' => '368',
		'IR' => '364',
		'IS' => '352',
		'IT' => '380',
		'JE' => '832',
		'JM' => '388',
		'JO' => '400',
		'JP' => '392',
		'KE' => '404',
		'KG' => '417',
		'KH' => '116',
		'KI' => '296',
		'KM' => '174',
		'KN' => '659',
		'KP' => '408',
		'KR' => '410',
		'KW' => '414',
		'KY' => '136',
		'KZ' => '398',
		'LA' => '418',
		'LB' => '422',
		'LC' => '662',
		'LI' => '438',
		'LK' => '144',
		'LR' => '430',
		'LS' => '426',
		'LT' => '440',
		'LU' => '442',
		'LV' => '428',
		'LY' => '434',
		'MA' => '504',
		'MC' => '492',
		'MD' => '498',
		'ME' => '499',
		'MF' => '663',
		'MG' => '450',
		'MH' => '584',
		'MK' => '807',
		'ML' => '466',
		'MM' => '104',
		'MN' => '496',
		'MO' => '446',
		'MP' => '580',
		'MQ' => '474',
		'MR' => '478',
		'MS' => '500',
		'MT' => '470',
		'MU' => '480',
		'MV' => '462',
		'MW' => '454',
		'MX' => '484',
		'MY' => '458',
		'MZ' => '508',
		'NA' => '516',
		'NC' => '540',
		'NE' => '562',
		'NF' => '574',
		'NG' => '566',
		'NI' => '558',
		'NL' => '528',
		'NO' => '578',
		'NP' => '524',
		'NR' => '520',
		'NU' => '570',
		'NZ' => '554',
		'OM' => '512',
		'PA' => '591',
		'PE' => '604',
		'PF' => '258',
		'PG' => '598',
		'PH' => '608',
		'PK' => '586',
		'PL' => '616',
		'PM' => '666',
		'PN' => '612',
		'PR' => '630',
		'PS' => '275',
		'PT' => '620',
		'PW' => '585',
		'PY' => '600',
		'QA' => '634',
		'RE' => '638',
		'RO' => '642',
		'RS' => '688',
		'RU' => '643',
		'RW' => '646',
		'SA' => '682',
		'SB' => '090',
		'SC' => '690',
		'SD' => '729',
		'SE' => '752',
		'SG' => '702',
		'SH' => '654',
		'SI' => '705',
		'SJ' => '744',
		'SK' => '703',
		'SL' => '694',
		'SM' => '674',
		'SN' => '686',
		'SO' => '706',
		'SR' => '740',
		'SS' => '728',
		'ST' => '678',
		'SV' => '222',
		'SX' => '534',
		'SY' => '760',
		'SZ' => '748',
		'TC' => '796',
		'TD' => '148',
		'TF' => '260',
		'TG' => '768',
		'TH' => '764',
		'TJ' => '762',
		'TK' => '772',
		'TL' => '626',
		'TM' => '795',
		'TN' => '788',
		'TO' => '776',
		'TR' => '792',
		'TT' => '780',
		'TV' => '798',
		'TW' => '158',
		'TZ' => '834',
		'UA' => '804',
		'UG' => '800',
		'UM' => '581',
		'US' => '840',
		'UY' => '858',
		'UZ' => '860',
		'VA' => '336',
		'VC' => '670',
		'VE' => '862',
		'VG' => '092',
		'VI' => '850',
		'VN' => '704',
		'VU' => '548',
		'WF' => '876',
		'WS' => '882',
		'YE' => '887',
		'YT' => '175',
		'ZA' => '710',
		'ZM' => '894',
		'ZW' => '716',
	);

	var $multiple = true;
	var $name = 'paybox';
	var $doc_form = 'paybox';
	var $pluginConfig = array(
		'pbx_site' => array('Site', 'input'),
		'pbx_rang' => array('Rang', 'input'),
		'pbx_indentifiant' => array('Identifiant', 'input'),
		'hash' => array('HMAC', 'input'),
		'bank' => array('Banque', 'list', array(
			'' => 'par défaut',
			'sofinco' => 'Sofinco',
			'ca' => 'e-transactions (Crédit Agricole)',
		)),
		'debug' => array('DEBUG', 'boolean','0'),
		'payment_methods' => array('Payment methods', 'list',array(
			'_' => 'All',
			'CARTE_' => '- All cards -',
			'CARTE_CB' => 'CB, VISA, EUROCARD_MASTERCARD, E_CARD',
			'CARTE_MAESTRO' => 'MAESTRO',
			'CARTE_BCMC' => 'BCMC',
			'CARTE_AMEX' => 'AMEX',
			'CARTE_JCB' => 'JCB',
			'CARTE_COFINOGA' => 'COFINOGA',
			'CARTE_SOFINCO' => 'SOFINCO',
			'LIMONETIK_SOF3X' =>'Limonetik SOFINCO 3X',
			'LIMONETIK_SOF3XSF' =>'Limonetik SOFINCO 3XSF',
			'CARTE_AURORE' => 'AURORE',
			'CARTE_CDGP' => 'CDGP',
			'CARTE_24H00' => '24H00',
			'CARTE_RIVEGAUCHE' => 'RIVEGAUCHE',
			'PAYPAL_PAYPAL' => '- Paypal -',
			'CREDIT_' => ' - All credit cards -',
			'CREDIT_UNEURO' => 'UNEURO',
			'CREDIT_34ONEY' => '34ONEY',
			'NETRESERVE_NETCDGP' => '- CDGP -',
			'PREPAYEE_' => '- All prepayed cards -',
			'PREPAYEE_SVS' => 'SVS',
			'PREPAYEE_KADEOS' => 'KADEOS',
			'PREPAYEE_PSC' => 'PSC',
			'PREPAYEE_CSHTKT' => 'CSHTKT',
			'PREPAYEE_LASER' => 'LASER',
			'PREPAYEE_EMONEO' => 'EMONEO',
			'PREPAYEE_IDEAL' => 'IDEAL',
			'PREPAYEE_ONEYKDO' => 'ONEYKDO',
			'PREPAYEE_ILLICADO' => 'ILLICADO',
			'PREPAYEE_WEXPAY' => 'WEXPAY',
			'PREPAYEE_MAXICHEQUE' => 'MAXICHEQUE',
			'FINAREF_' => '- All gift cards -',
			'FINAREF_SURCOUF' => 'SURCOUF',
			'FINAREF_KANGOUROU' => 'KANGOUROU',
			'FINAREF_FNAC' => 'FNAC',
			'FINAREF_CYRILLUS' => 'CYRILLUS',
			'FINAREF_PRINTEMPS' => 'PRINTEMPS',
			'FINAREF_CONFORAMA' => 'CONFORAMA',
			'BUYSTER_BUYSTER' => '- Buyster -',
			'LEETCHI_LEETCHI' => '- Leetchi -',
			'PAYBUTTONS_PAYBUTTONS' => '- Paybuttons -'
		)),
		'sandbox' => array('SANDBOX', 'boolean','0'),
		'iframe' => array('iFrame mode', 'boolean', '0'),
		'ips' => array('IPS', 'input'),
		'signature' => array('SIGNATURE', 'boolean', '1'),
		'ticket' => array('Send the Paybox payment receipt to', 'input'),
		'cancel_url' => array('CANCEL_URL', 'input'),
		'return_url' => array('RETURN_URL', 'input'),
		'invalid_status' => array('INVALID_STATUS', 'orderstatus'),
		'pending_status' => array('PENDING_STATUS', 'orderstatus'),
		'verified_status' => array('VERIFIED_STATUS', 'orderstatus')
	);

	function onAfterOrderConfirm(&$order,&$methods,$method_id) {
		parent::onAfterOrderConfirm($order, $methods, $method_id);

		$srv = 'tpeweb.paybox.com';
		if($this->payment_params->sandbox) {
			$srv = 'preprod-tpeweb.paybox.com';
		}

		$amount = (int)(round($order->cart->full_total->prices[0]->price_value_with_tax, 2) * 100);

		$this->vars = array(
			'PBX_SITE' => trim($this->payment_params->pbx_site),
			'PBX_RANG' => trim($this->payment_params->pbx_rang),
			'PBX_IDENTIFIANT' => trim($this->payment_params->pbx_indentifiant),
			'PBX_TOTAL' => $amount,
			'PBX_DEVISE' => 978,
			'PBX_CMD' => (int)$order->order_id,
			'PBX_PORTEUR' => $this->user->user_email,
			'PBX_SHOPPINGCART' => $this->getCartInformation($order),
			'PBX_BILLING' => $this->getBillingInformation($order),
			'PBX_RETOUR' => 'mt:M;ref:R;auth:A;err:E;sign:K',
			'PBX_HASH' => 'SHA512',
			'PBX_TIME' => date('c'),
			'PBX_EFFECTUE' => (HIKASHOP_LIVE.'paybox_'.$method_id.'.php?pbx=user&t=confirm'),
			'PBX_ATTENTE' => (HIKASHOP_LIVE.'paybox_'.$method_id.'.php?pbx=user&t=wait'),
			'PBX_REFUSE' => (HIKASHOP_LIVE.'paybox_'.$method_id.'.php?pbx=user&t=refuse'),
			'PBX_ANNULE' => (HIKASHOP_LIVE.'paybox_'.$method_id.'.php?pbx=user&t=cancel'),
			'PBX_REPONDRE_A' => (HIKASHOP_LIVE.'paybox_'.$method_id.'.php')
		);


		switch(@$this->payment_params->bank) {
			case 'ca':
				$this->vars['PBX_SOURCE'] = 'RWD';
				$srv = 'tpeweb.e-transactions.fr';

				if($this->payment_params->sandbox) {
					$srv = 'recette-tpeweb.e-transactions.fr';
				}
				break;
			case 'sofinco':
				$simpleXMLElement = new SimpleXMLElement("<Customer/>");
				$simpleXMLElement->addChild('Id',$this->user->user_id);        
				$this->vars['PBX_CUSTOMER'] = $simpleXMLElement->asXML();
				break;
			case '':
			default:
				break;
		}


		$this->url = 'https://'.$srv.'/cgi/FramepagepaiementRWD.cgi';

		if(!empty($this->payment_params->iframe)) {
			$this->url = 'https://'.$srv.'/cgi/MYframepagepaiement_ip.cgi';
		}


		if(!empty($this->payment_params->ticket)){
			$this->vars['PBX_PORTEUR'] = $this->payment_params->ticket;
		}

		if(empty($this->payment_params->payment_methods) && !empty($this->payment_params->force_card)){
			$this->payment_params->payment_methods = 'CARTE_';
		}

		if(!empty($this->payment_params->payment_methods)){
			list($typepaiement,$typecarte) = explode('_',$this->payment_params->payment_methods);
			if(!empty($typepaiement)) $this->vars['PBX_TYPEPAIEMENT'] = $typepaiement;
			if(!empty($typecarte)) $this->vars['PBX_TYPECARTE'] = $typecarte;
		}


		$payboxLanguages = array('FRA','GBR','ESP','ITA','DEU','NLD','SWE','PRT');
		$lang = JFactory::getLanguage();
		$possibleLanguageCodes = explode(',',strtoupper(preg_replace('#[^a-z,]#i','',$lang->get('locale'))));
		$inter = array_intersect($payboxLanguages,$possibleLanguageCodes);
		if(!empty($inter)) $this->vars['PBX_LANGUE'] = reset($inter);

		$msg = array();
		foreach($this->vars as $k => $v) {
			$msg[] = $k . '=' . $v;
		}
		$msg = implode('&', $msg);

		$binKey = pack('H*', $this->payment_params->hash);
		$this->vars['PBX_HMAC'] = strtoupper(hash_hmac('sha512', $msg, $binKey));
		$this->vars['PBX_SHOPPINGCART'] = htmlspecialchars($this->vars['PBX_SHOPPINGCART'], ENT_QUOTES,'UTF-8');
		$this->vars['PBX_BILLING'] =  htmlspecialchars($this->vars['PBX_BILLING'], ENT_QUOTES,'UTF-8');
		unset($msg);

		if(!empty($this->payment_params->debug)) {
			hikashop_writeToLog($this->vars);
		}

		return $this->showPage('end');
	}

	function getBillingInformation(&$order) {
		$country = 'FR';
		if(empty($order->cart->billing_address->address_country->zone_code_2)) {
			$country = $order->cart->billing_address->address_country->zone_code_2;
		}
		if(isset($this->country_codes[$country]))
			$country = $this->country_codes[$country];
		else
			$country = '250';
		$xml = '<?xml version="1.0" encoding="utf-8"?><Billing><Address><FirstName>'.
			$this->formatTextValue($order->cart->billing_address->address_firstname, 'ANP', 30).
		'</FirstName><LastName>'.
			$this->formatTextValue($order->cart->billing_address->address_lastname, 'ANP', 30).
		'</LastName><Address1>'.
			$this->formatTextValue($order->cart->billing_address->address_street, 'ANS', 50).
		'</Address1><Address2>'.
			$this->formatTextValue($order->cart->billing_address->address_street2, 'ANS', 50).
		'</Address2><ZipCode>'.
			$this->formatTextValue($order->cart->billing_address->address_post_code, 'ANS', 16).
		'</ZipCode><City>'.
			$this->formatTextValue($order->cart->billing_address->address_city, 'ANS', 50).
		'</City><CountryCode>'.
			$country.
		'</CountryCode></Address></Billing>';

		return $this->exportToXml($xml);
	}

	function exportToXml($xml) {
		if (class_exists('DOMDocument')) {
			$doc = new DOMDocument();
			$doc->loadXML($xml);
			$xml = $doc->saveXML();
		} elseif (function_exists('simplexml_load_string')) {
			$xml = simplexml_load_string($xml)->asXml();
		}

		$xml = trim(preg_replace('/(\s*)(' . preg_quote('<?xml version="1.0" encoding="utf-8"?>') . ')(\s*)/', '$2', $xml));
		$xml = trim(preg_replace("/\r|\n/", '', $xml));

		return $xml;
	}


	function formatTextValue($value, $type, $maxLength = null) {

		switch ($type) {
			default:
			case 'AN':
				$value = $this->remove_accents($value);
				break;
			case 'ANP':
				$value = $this->remove_accents($value);
				$value = preg_replace('/[^-. a-zA-Z0-9]/', '', $value);
				break;
			case 'ANS':
				break;
			case 'N':
				$value = preg_replace('/[^0-9.]/', '', $value);
				break;
			case 'A':
				$value = $this->remove_accents($value);
				$value = preg_replace('/[^A-Za-z]/', '', $value);
				break;
		}
		$value = trim(preg_replace("/\r|\n/", '', $value));

		if (!empty($maxLength) && is_numeric($maxLength) && $maxLength > 0) {
			if (function_exists('mb_strlen')) {
				if (mb_strlen($value) > $maxLength) {
					$value = mb_substr($value, 0, $maxLength);
				}
			} elseif (strlen($value) > $maxLength) {
				$value = substr($value, 0, $maxLength);
			}
		}

		return $value;
	}

	function remove_accents( $string ) {
		if ( ! preg_match( '/[\x80-\xff]/', $string ) ) {
			return $string;
		}

		$chars = array(
			'ª' => 'a',
			'º' => 'o',
			'À' => 'A',
			'Á' => 'A',
			'Â' => 'A',
			'Ã' => 'A',
			'Ä' => 'A',
			'Å' => 'A',
			'Æ' => 'AE',
			'Ç' => 'C',
			'È' => 'E',
			'É' => 'E',
			'Ê' => 'E',
			'Ë' => 'E',
			'Ì' => 'I',
			'Í' => 'I',
			'Î' => 'I',
			'Ï' => 'I',
			'Ð' => 'D',
			'Ñ' => 'N',
			'Ò' => 'O',
			'Ó' => 'O',
			'Ô' => 'O',
			'Õ' => 'O',
			'Ö' => 'O',
			'Ù' => 'U',
			'Ú' => 'U',
			'Û' => 'U',
			'Ü' => 'U',
			'Ý' => 'Y',
			'Þ' => 'TH',
			'ß' => 's',
			'à' => 'a',
			'á' => 'a',
			'â' => 'a',
			'ã' => 'a',
			'ä' => 'a',
			'å' => 'a',
			'æ' => 'ae',
			'ç' => 'c',
			'è' => 'e',
			'é' => 'e',
			'ê' => 'e',
			'ë' => 'e',
			'ì' => 'i',
			'í' => 'i',
			'î' => 'i',
			'ï' => 'i',
			'ð' => 'd',
			'ñ' => 'n',
			'ò' => 'o',
			'ó' => 'o',
			'ô' => 'o',
			'õ' => 'o',
			'ö' => 'o',
			'ø' => 'o',
			'ù' => 'u',
			'ú' => 'u',
			'û' => 'u',
			'ü' => 'u',
			'ý' => 'y',
			'þ' => 'th',
			'ÿ' => 'y',
			'Ø' => 'O',
			'Ā' => 'A',
			'ā' => 'a',
			'Ă' => 'A',
			'ă' => 'a',
			'Ą' => 'A',
			'ą' => 'a',
			'Ć' => 'C',
			'ć' => 'c',
			'Ĉ' => 'C',
			'ĉ' => 'c',
			'Ċ' => 'C',
			'ċ' => 'c',
			'Č' => 'C',
			'č' => 'c',
			'Ď' => 'D',
			'ď' => 'd',
			'Đ' => 'D',
			'đ' => 'd',
			'Ē' => 'E',
			'ē' => 'e',
			'Ĕ' => 'E',
			'ĕ' => 'e',
			'Ė' => 'E',
			'ė' => 'e',
			'Ę' => 'E',
			'ę' => 'e',
			'Ě' => 'E',
			'ě' => 'e',
			'Ĝ' => 'G',
			'ĝ' => 'g',
			'Ğ' => 'G',
			'ğ' => 'g',
			'Ġ' => 'G',
			'ġ' => 'g',
			'Ģ' => 'G',
			'ģ' => 'g',
			'Ĥ' => 'H',
			'ĥ' => 'h',
			'Ħ' => 'H',
			'ħ' => 'h',
			'Ĩ' => 'I',
			'ĩ' => 'i',
			'Ī' => 'I',
			'ī' => 'i',
			'Ĭ' => 'I',
			'ĭ' => 'i',
			'Į' => 'I',
			'į' => 'i',
			'İ' => 'I',
			'ı' => 'i',
			'Ĳ' => 'IJ',
			'ĳ' => 'ij',
			'Ĵ' => 'J',
			'ĵ' => 'j',
			'Ķ' => 'K',
			'ķ' => 'k',
			'ĸ' => 'k',
			'Ĺ' => 'L',
			'ĺ' => 'l',
			'Ļ' => 'L',
			'ļ' => 'l',
			'Ľ' => 'L',
			'ľ' => 'l',
			'Ŀ' => 'L',
			'ŀ' => 'l',
			'Ł' => 'L',
			'ł' => 'l',
			'Ń' => 'N',
			'ń' => 'n',
			'Ņ' => 'N',
			'ņ' => 'n',
			'Ň' => 'N',
			'ň' => 'n',
			'ŉ' => 'n',
			'Ŋ' => 'N',
			'ŋ' => 'n',
			'Ō' => 'O',
			'ō' => 'o',
			'Ŏ' => 'O',
			'ŏ' => 'o',
			'Ő' => 'O',
			'ő' => 'o',
			'Œ' => 'OE',
			'œ' => 'oe',
			'Ŕ' => 'R',
			'ŕ' => 'r',
			'Ŗ' => 'R',
			'ŗ' => 'r',
			'Ř' => 'R',
			'ř' => 'r',
			'Ś' => 'S',
			'ś' => 's',
			'Ŝ' => 'S',
			'ŝ' => 's',
			'Ş' => 'S',
			'ş' => 's',
			'Š' => 'S',
			'š' => 's',
			'Ţ' => 'T',
			'ţ' => 't',
			'Ť' => 'T',
			'ť' => 't',
			'Ŧ' => 'T',
			'ŧ' => 't',
			'Ũ' => 'U',
			'ũ' => 'u',
			'Ū' => 'U',
			'ū' => 'u',
			'Ŭ' => 'U',
			'ŭ' => 'u',
			'Ů' => 'U',
			'ů' => 'u',
			'Ű' => 'U',
			'ű' => 'u',
			'Ų' => 'U',
			'ų' => 'u',
			'Ŵ' => 'W',
			'ŵ' => 'w',
			'Ŷ' => 'Y',
			'ŷ' => 'y',
			'Ÿ' => 'Y',
			'Ź' => 'Z',
			'ź' => 'z',
			'Ż' => 'Z',
			'ż' => 'z',
			'Ž' => 'Z',
			'ž' => 'z',
			'ſ' => 's',
			'Ș' => 'S',
			'ș' => 's',
			'Ț' => 'T',
			'ț' => 't',
			'€' => 'E',
			'£' => '',
			'Ơ' => 'O',
			'ơ' => 'o',
			'Ư' => 'U',
			'ư' => 'u',
			'Ầ' => 'A',
			'ầ' => 'a',
			'Ằ' => 'A',
			'ằ' => 'a',
			'Ề' => 'E',
			'ề' => 'e',
			'Ồ' => 'O',
			'ồ' => 'o',
			'Ờ' => 'O',
			'ờ' => 'o',
			'Ừ' => 'U',
			'ừ' => 'u',
			'Ỳ' => 'Y',
			'ỳ' => 'y',
			'Ả' => 'A',
			'ả' => 'a',
			'Ẩ' => 'A',
			'ẩ' => 'a',
			'Ẳ' => 'A',
			'ẳ' => 'a',
			'Ẻ' => 'E',
			'ẻ' => 'e',
			'Ể' => 'E',
			'ể' => 'e',
			'Ỉ' => 'I',
			'ỉ' => 'i',
			'Ỏ' => 'O',
			'ỏ' => 'o',
			'Ổ' => 'O',
			'ổ' => 'o',
			'Ở' => 'O',
			'ở' => 'o',
			'Ủ' => 'U',
			'ủ' => 'u',
			'Ử' => 'U',
			'ử' => 'u',
			'Ỷ' => 'Y',
			'ỷ' => 'y',
			'Ẫ' => 'A',
			'ẫ' => 'a',
			'Ẵ' => 'A',
			'ẵ' => 'a',
			'Ẽ' => 'E',
			'ẽ' => 'e',
			'Ễ' => 'E',
			'ễ' => 'e',
			'Ỗ' => 'O',
			'ỗ' => 'o',
			'Ỡ' => 'O',
			'ỡ' => 'o',
			'Ữ' => 'U',
			'ữ' => 'u',
			'Ỹ' => 'Y',
			'ỹ' => 'y',
			'Ấ' => 'A',
			'ấ' => 'a',
			'Ắ' => 'A',
			'ắ' => 'a',
			'Ế' => 'E',
			'ế' => 'e',
			'Ố' => 'O',
			'ố' => 'o',
			'Ớ' => 'O',
			'ớ' => 'o',
			'Ứ' => 'U',
			'ứ' => 'u',
			'Ạ' => 'A',
			'ạ' => 'a',
			'Ậ' => 'A',
			'ậ' => 'a',
			'Ặ' => 'A',
			'ặ' => 'a',
			'Ẹ' => 'E',
			'ẹ' => 'e',
			'Ệ' => 'E',
			'ệ' => 'e',
			'Ị' => 'I',
			'ị' => 'i',
			'Ọ' => 'O',
			'ọ' => 'o',
			'Ộ' => 'O',
			'ộ' => 'o',
			'Ợ' => 'O',
			'ợ' => 'o',
			'Ụ' => 'U',
			'ụ' => 'u',
			'Ự' => 'U',
			'ự' => 'u',
			'Ỵ' => 'Y',
			'ỵ' => 'y',
			'ɑ' => 'a',
			'Ǖ' => 'U',
			'ǖ' => 'u',
			'Ǘ' => 'U',
			'ǘ' => 'u',
			'Ǎ' => 'A',
			'ǎ' => 'a',
			'Ǐ' => 'I',
			'ǐ' => 'i',
			'Ǒ' => 'O',
			'ǒ' => 'o',
			'Ǔ' => 'U',
			'ǔ' => 'u',
			'Ǚ' => 'U',
			'ǚ' => 'u',
			'Ǜ' => 'U',
			'ǜ' => 'u',
		);

		$chars['Ä'] = 'Ae';
		$chars['ä'] = 'ae';
		$chars['Ö'] = 'Oe';
		$chars['ö'] = 'oe';
		$chars['Ü'] = 'Ue';
		$chars['ü'] = 'ue';
		$chars['ß'] = 'ss';
		$chars['Æ'] = 'Ae';
		$chars['æ'] = 'ae';
		$chars['Ø'] = 'Oe';
		$chars['ø'] = 'oe';
		$chars['Å'] = 'Aa';
		$chars['å'] = 'aa';
		$chars['l·l'] = 'll';
		$chars['Đ'] = 'DJ';
		$chars['đ'] = 'dj';

		$string = strtr( $string, $chars );

		return $string;
	}

	function getCartInformation(&$order) {
		$total = 0;
		if (!empty($order) && !empty($order->products)) {
			foreach ($order->products as $item) {
				$total+= $item->order_product_quantity;
			}
		} else {
			$total = 1;
		}
		$total = max(1, min($total, 99));

		return '<?xml version="1.0" encoding="utf-8"?><shoppingcart><total><totalQuantity>'.$total.'</totalQuantity></total></shoppingcart>';
	}

	function onPaymentNotification(&$statuses) {
		global $Itemid;
		$this->url_itemid = empty($Itemid) ? '' : '&Itemid=' . $Itemid;

		$method_id = hikaInput::get()->getInt('notif_id', 0);
		$this->pluginParams($method_id);
		$this->payment_params =& $this->plugin_params;

		if(hikaInput::get()->getVar('pbx', '') == 'user') {
			$app = JFactory::getApplication();
			$t = hikaInput::get()->getVar('t', '');
			switch($t) {
				case 'refuse':
					$url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=order&task=cancel_order'.$this->url_itemid;
					break;
				case 'cancel':
					$url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=order&task=cancel_order'.$this->url_itemid;
					break;
				case 'confirm':
				default:
					$url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=after_end'.$this->url_itemid;
					break;
			}

			if(!empty($this->payment_params->iframe)){
				echo '<script>window.parent.location.href = "'.$url.'";</script>';
				exit;
			}else{
				$app->redirect($url);
			}

			return;
		}

		if(empty($this->payment_params))
			exit;

		if(!empty($this->payment_params->debug)) {
			hikashop_writeToLog($_REQUEST);
		}

		if(!empty($this->payment_params->ips)){
			$ip = hikashop_getIP();
			$valid = false;
			$ips = explode(';', $this->payment_params->ips);
			foreach($ips as $i) {
				$i = trim($i);
				if($i == $ip) {
					$valid = true;
					break;
				}
			}
			if(!$valid) {
				$email = new stdClass();
				$email->subject = JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','Paybox') . ' ' . JText::sprintf('IP_NOT_VALID', '');
				$email->body = str_replace('<br/>',"\r\n",JText::sprintf('NOTIFICATION_REFUSED_FROM_IP','Paybox',$ip,implode("\r\n",$ips)));
				$action = false;
				$this->modifyOrder($action, null, null, $email);

				$this->app->enqueueMessage(JText::_('Access Forbidden'), 'error');
				exit;
			}
		}

		if(function_exists('openssl_pkey_get_public') && (!isset($this->payment_params->signature) || !empty($this->payment_params->signature))) {
			$signature = hikaInput::get()->getVar('sign', '');
			if(!empty($signature))
				$signature = base64_decode(urldecode($signature));

			$p_mt = hikaInput::get()->getVar('mt', '');
			$p_ref = hikaInput::get()->getVar('ref', '');
			$p_auth = hikaInput::get()->getVar('auth', '');
			$p_err = hikaInput::get()->getVar('err', '');
			$sign_data = 'mt=' . rawurlencode($p_mt) . '&ref=' . rawurlencode($p_ref) . '&auth=' . rawurlencode($p_auth) . '&err' . rawurlencode($p_err);

			$pubkeyid = openssl_pkey_get_public( dirname(__FILE__) . DS . 'paybox_pubkey.pem' );
			if($pubkeyid !== false) {
				$sign = openssl_verify($sign_data, $signature, $pubkeyid);
				openssl_free_key($pubkeyid);

				if($sign !== 1) {
					$ip = hikashop_getIP();
					$email = new stdClass();
					$email->subject = JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','Paybox') . ' ' . JText::_('SIGN_NOT_VALID');
					$email->body = str_replace('<br/>',"\r\n",JText::sprintf('NOTIFICATION_REFUSED_FROM_IP','Paybox',$ip,JText::_('SIGN_NOT_VALID')));
					$action = false;
					$this->modifyOrder($action, null, null, $email);

					$this->app->enqueueMessage(JText::_('Access Forbidden'), 'error');
					exit;
				}
			}
		}

		$order_id = (int)hikaInput::get()->getInt('ref', 0);
		$dbOrder = $this->getOrder($order_id);
		if(empty($dbOrder)){
			exit;
		}

		if($method_id != $dbOrder->order_payment_id)
			exit;
		$this->loadOrderData($dbOrder);
		if(empty($this->payment_params))
			return false;

		$pbx_auth = hikaInput::get()->getVar('auth', '');
		$pbx_err = hikaInput::get()->getVar('err', '99999');
		$pbx_mt = hikaInput::get()->getInt('mt', 0);

		$history = new stdClass();
		$email = new stdClass();

		$url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=edit&order_id=' . $order_id . $this->url_itemid;
		$order_text = "\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE', $dbOrder->order_number, HIKASHOP_LIVE);
		$order_text .= "\r\n".str_replace('<br/>',"\r\n",JText::sprintf('ACCESS_ORDER_WITH_LINK', $url));

		$history->notified = 0;
		$history->amount = ($pbx_mt/100);
		$history->data =  ob_get_clean();

		$price_check = (int)(round($dbOrder->order_full_price, 2) * 100);
		if($pbx_mt != $price_check) {
			$email->subject = JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER', 'Paybox') . JText::_('INVALID_AMOUNT');
			$email->body = str_replace('<br/>', "\r\n", JText::sprintf('AMOUNT_RECEIVED_DIFFERENT_FROM_ORDER', 'Paybox', $history->amount, ($price_check/100) . $this->currency->currency_code)) . "\r\n\r\n" . $order_text;
			$this->modifyOrder($order_id, $this->payment_params->invalid_status, $history, $email);
			exit;
		}

		$completed = ((int)$pbx_err == 0 && $pbx_err == '00000');

		if( !$completed ) {
			$order_status = $this->payment_params->invalid_status;
			$history->data .= "\n\n" . 'payment with code '.$pbx_auth;
			$payment_status = 'cancel';

			$email->body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS', 'Paybox', $payment_status)).' '.JText::_('STATUS_NOT_CHANGED')."\r\n\r\n".$order_text;
		 	$email->subject = JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER', 'Paybox', $payment_status, $dbOrder->order_number);

			$this->modifyOrder($order_id, $order_status, $history, $email);
			exit;
		}


		$history->notified = 1;
		$order_status = $this->payment_params->verified_status;
		$payment_status = 'Accepted';

		if($dbOrder->order_status == $order_status)
			return true;

		$email->body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','Paybox', $payment_status)).' '.JText::sprintf('ORDER_STATUS_CHANGED', $statuses[$order_status])."\r\n\r\n".$order_text;
		$email->subject = JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER', 'Paybox', $payment_status, $dbOrder->order_number);

		$this->modifyOrder($order_id, $order_status, $history, $email);
		exit;
	}

	function getPaymentDefaultValues(&$element) {
		$element->payment_name = 'PAYBOX';
		$element->payment_description = 'You can pay by credit card using this payment method';
		$element->payment_images = 'MasterCard,VISA,Credit_card,American_Express';

		$element->payment_params->ips = '';

		$element->payment_params->invalid_status = 'cancelled';
		$element->payment_params->pending_status = 'created';
		$element->payment_params->verified_status = 'confirmed';
	}

	function onPaymentConfiguration(&$element){
		parent::onPaymentConfiguration($element);

		if(!empty($element->payment_params->force_card)) $element->payment_params->payment_methods = 'CARTE_';
	}

	function onPaymentConfigurationSave(&$element) {
		parent::onPaymentConfigurationSave($element);

		if(empty($element->payment_id)) {
			$pluginClass = hikashop_get('class.payment');
			$status = $pluginClass->save($element);
			if(!$status)
				return true;
			$element->payment_id = $status;
		}

		$app = JFactory::getApplication();
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.path');
		$lang = JFactory::getLanguage();
		$locale = strtolower(substr($lang->get('tag'),0,2));

		$content = '<?php
$_GET[\'option\']=\'com_hikashop\';
$_GET[\'tmpl\']=\'component\';
$_GET[\'ctrl\']=\'checkout\';
$_GET[\'task\']=\'notify\';
$_GET[\'notif_payment\']=\'paybox\';
$_GET[\'format\']=\'html\';
$_GET[\'lang\']=\''.$locale.'\';
$_GET[\'notif_id\']=\''.$element->payment_id.'\';
$_REQUEST[\'option\']=\'com_hikashop\';
$_REQUEST[\'tmpl\']=\'component\';
$_REQUEST[\'ctrl\']=\'checkout\';
$_REQUEST[\'task\']=\'notify\';
$_REQUEST[\'notif_payment\']=\'paybox\';
$_REQUEST[\'format\']=\'html\';
$_REQUEST[\'lang\']=\''.$locale.'\';
$_REQUEST[\'notif_id\']=\''.$element->payment_id.'\';
include(\'index.php\');
';
		JFile::write(JPATH_ROOT.DS.'paybox_'.$element->payment_id.'.php', $content);

		return true;
	}
}
