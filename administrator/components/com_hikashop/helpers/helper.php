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
if(!defined('DS'))
	define('DS', DIRECTORY_SEPARATOR);

jimport('joomla.application.component.controller');
jimport('joomla.application.component.view');
jimport('joomla.filesystem.file');

$jversion = preg_replace('#[^0-9\.]#i','',JVERSION);
define('HIKASHOP_J16', true); define('HIKASHOP_J17', true); define('HIKASHOP_J25',true);
define('HIKASHOP_J30',version_compare($jversion,'3.0.0','>=') ? true : false);
define('HIKASHOP_J40',version_compare($jversion,'4.0.0','>=') ? true : false);
define('HIKASHOP_JVERSION', $jversion);

define('HIKASHOP_PHP5',version_compare(PHP_VERSION,'5.0.0', '>=') ? true : false);
define('HIKASHOP_PHP7',version_compare(PHP_VERSION,'7.0.0', '>=') ? true : false);
define('HIKASHOP_PHP8',version_compare(PHP_VERSION,'8.0.0', '>=') ? true : false);

define('HIKASHOP_VERSION', '4.7.3');

$app = JFactory::getApplication();
$app->triggerEvent('onBeforeHikashopLoad', array() );

class hikashop {
	public static function __callStatic($name, $arguments) {
		$fct = 'hikashop_'.$name;
		if(!function_exists($fct))
			return false;
		return call_user_func_array($fct, $arguments);
	}
}

if(!function_exists('hikashop_getDate')) {
	function hikashop_getDate($time = 0, $format = '%d %B %Y %H:%M') {
		if(empty($time))
			return '';

		$format = str_replace(array('%A','%d','%B','%m','%Y','%y','%H','%M','%S','%a'), array('l','d','F','m','Y','y','H','i','s','D'), hikashop_getDateFormat($format));
		$date = '';
		try {
			$date = JHTML::_('date', $time, $format, false);
		} catch (Exception $e) {
			echo $e->getMessage();
		}
		return $date;
	}
}

if(!function_exists('hikashop_getDateFormat')) {
	function hikashop_getDateFormat($format = '%d %B %Y %H:%M') {
		$lang  = JFactory::getLanguage();
		$debug = $lang->setDebug(false);

		if(is_numeric($format))
			$format = JText::_('DATE_FORMAT_LC'.$format);
		$format_key = '';
		$clean_format = trim($format);
		if($clean_format=='%d %B %Y'){
			$format_key = 'HIKASHOP_DATE_FORMAT';
		}elseif($clean_format=='%d %B %Y %H:%M'){
			$format_key = 'HIKASHOP_EXTENDED_DATE_FORMAT';
		}
		if(!empty($format_key)){
			$language_format = JText::_($format_key);
			if($language_format!=$format_key){
				$format = $language_format;
			}
		}

		$lang->setDebug($debug);
		return $format;
	}
}

if(!function_exists('hikashop_isAllowed')) {
	function hikashop_isAllowed($allowedGroups, $id = null, $type = 'user') {
		if($allowedGroups == 'all') return true;
		if($allowedGroups == 'none') return false;

		if(!is_array($allowedGroups)) $allowedGroups = explode(',',$allowedGroups);

		if($type=='user'){
			jimport('joomla.access.access');
			$my = JFactory::getUser($id);
			$config =& hikashop_config();
			$userGroups = JAccess::getGroupsByUser($my->id, (bool)$config->get('inherit_parent_group_access'));
		}else{
			$userGroups = array($id);
		}
		$inter = array_intersect($userGroups,$allowedGroups);
		if(empty($inter)) return false;
		return true;
	}
}

if(!function_exists('hikashop_addACLFilters')) {
	function hikashop_addACLFilters(&$filters, $field, $table = '', $level = 2, $allowNull = false, $user_id = 0) {
		if(!hikashop_level($level))
			return;

		if(empty($user_id) || (int)$user_id == 0) {
			$my = JFactory::getUser();
		} else {
			$userClass = hikashop_get('class.user');
			$hkUser = $userClass->get($user_id);
			$my = JFactory::getUser($hkUser->user_cms_id);
		}

		jimport('joomla.access.access');
		$config =& hikashop_config();
		$userGroups = JAccess::getGroupsByUser($my->id, (bool)$config->get('inherit_parent_group_access'));//$my->authorisedLevels();

		if(empty($userGroups))
			return;

		if(!empty($table))
				$table .= '.';
		$acl_filters = array($table.$field." = 'all'");
		foreach($userGroups as $userGroup) {
			$acl_filters[]=$table.$field." LIKE '%,".(int)$userGroup.",%'";
		}
		if($allowNull)
			$acl_filters[] = 'ISNULL(' . $table.$field . ')';
		$filters[] = '(' . implode(' OR ', $acl_filters) . ')';
	}
}

if(!function_exists('hikashop_currentURL')) {
	function hikashop_currentURL($checkInRequest = '', $safe = true) {
		if(!empty($checkInRequest)){
			$url = hikaInput::get()->getVar($checkInRequest,'');
			if(!empty($url)){
				if(strpos($url,'http')!==0&&strpos($url,'/')!==0){
					if($checkInRequest=='return_url'){
						$url = base64_decode(urldecode($url));
					}elseif($checkInRequest=='url'){
						$url = urldecode($url);
					}
				}
				if($safe){
					$url = str_replace(array('"',"'",'<','>',';'),array('%22','%27','%3C','%3E','%3B'),$url);
				}
				return $url;
			}
		}

		$config = hikashop_config();
		$mode = $config->get('server_current_url_mode','REQUEST_URI');

		switch($mode){
			case 'REQUEST_URI':
				$requestUri = $_SERVER["REQUEST_URI"];
				if (!empty($_SERVER["REDIRECT_URL"]) && !empty($_SERVER['REDIRECT_QUERY_STRING']) && strpos($requestUri,'?')===false) $requestUri = rtrim($requestUri,'/').'?'.$_SERVER['REDIRECT_QUERY_STRING'];
				break;
			case 'REDIRECT_URL':
				$requestUri = $_SERVER["REQUEST_URI"];
				if (!empty($_SERVER['REDIRECT_QUERY_STRING'])) $requestUri = rtrim($requestUri,'/').'?'.$_SERVER['REDIRECT_QUERY_STRING'];
				elseif (!empty($_SERVER['QUERY_STRING'])) $requestUri = rtrim($requestUri,'/').'?'.$_SERVER['QUERY_STRING'];
				break;
			case '0':
			case 0:
			case '':
			default:
				if(!empty($_SERVER["REDIRECT_URL"]) && preg_match('#.*index\.php$#',$_SERVER["REDIRECT_URL"]) && empty($_SERVER['QUERY_STRING'])&&(empty($_SERVER['REDIRECT_QUERY_STRING']) || strpos($_SERVER['REDIRECT_QUERY_STRING'],'&')===false) && !empty($_SERVER["REQUEST_URI"])){
					$requestUri = $_SERVER["REQUEST_URI"];
					if (!empty($_SERVER['REDIRECT_QUERY_STRING'])) $requestUri = rtrim($requestUri,'/').'?'.$_SERVER['REDIRECT_QUERY_STRING'];
				}elseif(!empty($_SERVER["REDIRECT_URL"]) && preg_match('#.*index\.php$#',$_SERVER["REDIRECT_URL"]) && !empty($_SERVER["REQUEST_URI"])){
					$requestUri = $_SERVER["REQUEST_URI"];
				}elseif(!empty($_SERVER["REDIRECT_URL"]) && (isset($_SERVER['QUERY_STRING'])||isset($_SERVER['REDIRECT_QUERY_STRING']))){
					$requestUri = $_SERVER["REDIRECT_URL"];
					if (!empty($_SERVER['REDIRECT_QUERY_STRING'])) $requestUri = rtrim($requestUri,'/').'?'.$_SERVER['REDIRECT_QUERY_STRING'];
					elseif (!empty($_SERVER['QUERY_STRING'])) $requestUri = rtrim($requestUri,'/').'?'.$_SERVER['QUERY_STRING'];
				}elseif(isset($_SERVER["REQUEST_URI"])){
					$requestUri = $_SERVER["REQUEST_URI"];
				}else{
					$requestUri = $_SERVER['PHP_SELF'];
					if (!empty($_SERVER['QUERY_STRING'])) $requestUri = rtrim($requestUri,'/').'?'.$_SERVER['QUERY_STRING'];
				}
				break;
		}
		if(strpos($requestUri, 'http://') === false && strpos($requestUri, 'https://') === false)
			$result = (hikashop_isSSL() ? 'https://' : 'http://').$_SERVER["HTTP_HOST"].$requestUri;
		else
			$result = $requestUri;
		if($safe){
			$result = str_replace(array('"',"'",'<','>',';'),array('%22','%27','%3C','%3E','%3B'),$result);
		}
		return $result;
	}
}

if(!function_exists('hikashop_getTime')) {
	function hikashop_getTime($date, $format = '%d %B %Y %H:%M') {
		$realDate = preg_replace('#[0 \-:\/]#', '', $date);
		if(empty($realDate))
			return '';
		static $timeoffset = null;
		static $timeZone = null;
		if($timeoffset === null){
			$config = JFactory::getConfig();
			if(!HIKASHOP_J30){
				$timeZone = $config->getValue('config.offset');
			} else {
				$timeZone = $config->get('offset');
			}
			$dateC = JFactory::getDate('now', $timeZone);
			$timeoffset = $dateC->getOffsetFromGMT(true);
		}
		if(!is_numeric($date)) {
			$format = str_replace(array('%A','%d','%B','%m','%Y','%y','%H','%M','%S','%a','%l','%p','%P','%'), array('l','d','F','m','Y','y','H','i','s','D','h','A','a',''), hikashop_getDateFormat($format));
			try {
				$dateTime = DateTime::createFromFormat($format, $date, new DateTimeZone($timeZone));
				if($dateTime) {
					$date = $dateTime->getTimestamp();
				}else {
					$date = strtotime(str_replace('-','/', $date));
					if($date === false)
						return false;
					return $date - $timeoffset *60*60 + date('Z', $date);
				}
			} catch( Exception $e) {
				$date = strtotime(str_replace('-','/', $date));
				if($date === false)
					return false;
				return $date - $timeoffset *60*60 + date('Z', $date);
			}

		}
		return $date;
	}
}

if(!function_exists('hikashop_getIP')) {
	function hikashop_getIP() {
		$ip = '';
		if( !empty($_SERVER['HTTP_X_FORWARDED_FOR']) && strlen($_SERVER['HTTP_X_FORWARDED_FOR']) > 6){
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}elseif( !empty($_SERVER['HTTP_CLIENT_IP']) && strlen($_SERVER['HTTP_CLIENT_IP']) > 6){
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		}elseif(!empty($_SERVER['REMOTE_ADDR']) && strlen($_SERVER['REMOTE_ADDR']) > 6){
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		if(strpos($ip,',') !== false) {
			$ips = explode(',', trim($ip,','));
			$ip = trim($ips[0]);
		}

		return filter_var($ip, FILTER_VALIDATE_IP);
	}
}

if(!function_exists('hikashop_isSSL')) {
	function hikashop_isSSL() {
		if((isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off')) || $_SERVER['SERVER_PORT'] == 443 ||
			(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https') ) {
			return true;
		}
		return false;
	}
}

if(!function_exists('hikashop_getUpgradeLink')) {
	function hikashop_getUpgradeLink($tolevel) {
		$config =& hikashop_config();
		$text = '';
		if($tolevel=='essential')
			$text = 'ONLY_COMMERCIAL';
		elseif($tolevel=='business')
			$text = 'ONLY_FROM_HIKASHOP_BUSINESS';
		return ' <a class="hikaupgradelink" href="'.HIKASHOP_REDIRECT.'upgrade-hikashop-'.strtolower($config->get('level')).'-to-'.$tolevel.'" target="_blank">'.JText::_($text).'</a>';
	}
}

if(!function_exists('hikashop_encode')) {
	function hikashop_encode(&$data, $type = 'order', $format = '') {
		$id = null;
		if(is_object($data)) {
			if($type=='order')
				$id = $data->order_id;
			if($type=='invoice')
				$id = $data->order_invoice_id;
		} else {
			$id = $data;
		}
		if(is_object($data) && ($type=='order' || $type=='invoice') && hikashop_level(1)) {
			JPluginHelper::importPlugin( 'hikashop' );
			$app = JFactory::getApplication();
			$result='';
			$trigger_name = 'onBefore'.ucfirst($type).'NumberGenerate';
			$app->triggerEvent($trigger_name, array( &$data, &$result) );
			if(!empty($result)) {
				return $result;
			}

			$config =& hikashop_config();
			if(empty($format)) {
				$format = $config->get($type.'_number_format','{automatic_code}');
			}
			if(preg_match('#\{id *(?:size ?= ?(?:"|\')(.*)(?:"|\'))? *\}#Ui',$format,$matches)) {
				$copy = $id;
				if(!empty($matches[1])){
					$copy = sprintf('%0'.$matches[1].'d', $copy);
				}
				$format = str_replace($matches[0],$copy,$format);
			}
			$matches=null;
			if(preg_match_all('#\{date *format ?= ?(?:"|\')(.*)(?:"|\') *\}#Ui',$format,$matches)) {
				foreach($matches[0] as $k => $match) {
					$format = str_replace($match,date($matches[1][$k],$data->order_modified),$format);
				}
			}
			if(strpos($format,'{automatic_code}')!==false) {
				$format = str_replace('{automatic_code}',hikashop_base($id),$format);
			}
			if(preg_match_all('#\{user ([a-z_0-9]+)\}#i',$format,$matches)) {
				if(empty($data->customer)) {
					$order_user_id = 0;
					if(isset($data->order_user_id)) {
						$order_user_id = $data->order_user_id;
					} elseif(isset($data->old->order_user_id)) {
						$order_user_id = $data->old->order_user_id;
					} elseif(isset($data->order_id)) {
						$orderClass = hikashop_get('class.order');
						$orderData = $orderClass->get($data->order_id);
						$order_user_id = $orderData->order_user_id;
					}
					if($order_user_id) {
						$userClass = hikashop_get('class.user');
						$data->customer = $userClass->get($order_user_id);
					}
				}
				foreach($matches[1] as $match) {
					if(isset($data->customer->$match)) {
						$format = str_replace('{user '.$match.'}',$data->customer->$match,$format);
					} else {
						$format = str_replace('{user '.$match.'}','',$format);
					}
				}
			}
			if(preg_match_all('#\{([a-z_0-9]+) *(?:size ?= ?(?:"|\')(.*)(?:"|\'))? *\}#i',$format,$matches)) {
				foreach($matches[1] as $k => $match) {
					$copy = @$data->$match;
					if(!empty($matches[2][$k])) {
						$copy = sprintf('%0'.$matches[2][$k].'d', $copy);
					}
					$format = str_replace($matches[0][$k],$copy,$format);
				}
			}
			return $format;
		}
		return hikashop_base($id);
	}
}

if(!function_exists('hikashop_base')) {
	function hikashop_base($id) {
		$base=23;
		$chars='ABCDEFGHJKLMNPQRSTUWXYZ';
		$str='';
		$val2=(string)$id;
		do {
			$i=$id % $base;
			$str=$chars[$i].$str;
			$id=($id-$i)/$base;
		} while($id>0);
		$str2='';
		$size=strlen($val2);
		for($i=0;$i<$size;$i++){
			if(isset($str[$i]))$str2.=$str[$i];
			$str2.=$val2[$i];
		}
		if($i<strlen($str))
			$str2.=substr($str,$i);
		return $str2;
	}
}

if(!function_exists('hikashop_decode')) {
	function hikashop_decode($str, $type = 'order') {
		if($type == 'order' && hikashop_level(1)) {
			$config =& hikashop_config();
			JPluginHelper::importPlugin('hikashop');
			$app = JFactory::getApplication();
			$result = '';
			$app->triggerEvent('onBeforeOrderNumberRevert', array( & $str, &$result ));
			if(!empty($result)) {
				return $result;
			}

			$format = $config->get('order_number_format','{automatic_code}');
			$format = str_replace(array('^','$','.','[',']','|','(',')','?','*','+'),array('\^','\$','\.','\[','\]','\|','\(','\)','\?','\*','\+'),$format);
			if(preg_match_all('#\{date *format ?= ?(?:"|\')(.*)(?:"|\') *\}#Ui',$format,$matches)){
				foreach($matches[0] as $k => $match) {
					$format = str_replace($match,'(?:'.preg_replace('#[a-z]+#i','[0-9a-z]+',$matches[1][$k]).')',$format);
				}
			}
			if(preg_match('#\{id *(?:size ?= ?(?:"|\')(.*)(?:"|\'))? *\}#Ui',$format,$matches)){
				$format = str_replace($matches[0],'([0-9]+)',$format);
			}
			if(strpos($format,'{automatic_code}')!==false){
					$format = str_replace('{automatic_code}','([0-9a-z]+)',$format);
			}
			if(preg_match_all('#\{([a-z_0-9]+)\}#i',$format,$matches)){
				foreach($matches[1] as $match){
					if(isset($data->$match)){
						$format = str_replace('{'.$match.'}','.*',$format);
					}else{
						$format = str_replace('{'.$match.'}','',$format);
					}
				}
			}

			$format = str_replace(array('{','}'),array('\{','\}'),$format);

			if(preg_match('#'.$format.'#i',$str,$matches)){
				foreach($matches as $i => $match){
					if($i){
						return ltrim(preg_replace('#[^0-9]#','',$match),'0');
					}
				}
			}
		}
		return preg_replace('#[^0-9]#','',$str);
	}
}

if(!function_exists('hikashop_array_path')) {
	function &hikashop_array_path(&$array, $path) {
		settype($path, 'array');
		$offset =& $array;
		foreach ($path as $index) {
			if (!isset($offset[$index])) {
				return false;
			}
			$offset =& $offset[$index];
		}
		return $offset;
	}
}

if(!function_exists('hikashop_toFloat')) {
	function hikashop_toFloat($val) {
		if(is_string($val) && preg_match_all('#-?[0-9]+#', $val, $parts) && count($parts[0]) > 1) {
			$dec = array_pop($parts[0]);
			return (float)(implode('', $parts[0]) . '.' . $dec);
		}
		return (float) $val;
	}
}

if(!function_exists('hikashop_toInteger')) {
	function hikashop_toInteger(&$array) {
		if(is_array($array))
			$array = array_map('intval', $array);
		else
			$array = array();
	}
}

if(!function_exists('hikashop_loadUser')) {
	function hikashop_loadUser($full = false, $reset = false) {
		static $user = null;
		if($reset){
			$user = null;
			return true;
		}
		if(!isset($user) || $user === null) {
			$app = JFactory::getApplication();
			$user_id = (int)$app->getUserState( HIKASHOP_COMPONENT.'.user_id' );
			$userClass = hikashop_get('class.user');
			if(empty($user_id)){
				$userCMS = JFactory::getUser();
				if(!$userCMS->guest){
					$joomla_user_id = $userCMS->get('id');
					$user_id = $userClass->getID($userCMS->get('id'));
					$app->setUserState( HIKASHOP_COMPONENT.'.user_id',$user_id);
				}else{
					$app->setUserState( HIKASHOP_COMPONENT.'.user_id',0);
					return $user;
				}
			}

			$user = $userClass->get($user_id);
		}
		if($full)
			return $user;
		return (int)@$user->user_id;
	}
}


if(!function_exists('hikashop_getZone')) {
	function hikashop_getZone($type = 'shipping', $args = null) {
		if(empty($type) || !in_array($type, array('billing', 'shipping'))) {
			$config =& hikashop_config();
			$type = $config->get('tax_zone_type', 'shipping');
		}
		$app = JFactory::getApplication();
		$isAdmin = hikashop_isClient('administrator');
		$zone_id = 0;

		if(!$isAdmin) {
			$cartClass = hikashop_get('class.cart');
			$cart = $cartClass->get(0);
			if(!empty($cart) && $type == 'shipping')
				$shipping_address = (int)$cart->cart_shipping_address_ids;
			if(!empty($cart) && ($type == 'billing' || empty($shipping_address)))
				$shipping_address = (int)$cart->cart_billing_address_id;

			if(empty($shipping_address) && empty($cart->user_id)) {
				$session_addresses = $app->getUserState(HIKASHOP_COMPONENT.'.addresses', array());
				$session_address = reset($session_addresses);
				$shipping_address = (int)@$session_addresses->address_id;
				unset($session_addresses);
				unset($session_address);
			}
		}

		if(!$isAdmin && empty($shipping_address))
			$shipping_address = $app->getUserState(HIKASHOP_COMPONENT.'.'.$type.'_address', 0);
		if(!$isAdmin && empty($shipping_address) && $type == 'shipping')
			$shipping_address = $app->getUserState(HIKASHOP_COMPONENT.'.'.'billing_address', 0);

		if(!empty($shipping_address)) {
			$useMainZone = false;
			$id = $app->getUserState(HIKASHOP_COMPONENT.'.shipping_id', '');
			if(!empty($id)) {
				if(is_array($id))
					$id = reset($id);

				$shippingClass = hikashop_get('class.shipping');
				$shipping = $shippingClass->get($id);
				if(!empty($shipping->shipping_params) && is_string($shipping->shipping_params))
					$shipping->shipping_params = hikashop_unserialize($shipping->shipping_params);

				if($type == 'shipping' && !empty($shipping->shipping_params->override_tax_zone) && is_numeric($shipping->shipping_params->override_tax_zone)){
					return (int)$shipping->shipping_params->override_tax_zone;
				}

				$override = 0;
				if(isset($shipping->shipping_params->shipping_override_address))
					$override = (int)$shipping->shipping_params->shipping_override_address;

				unset($shipping);

				if($override && $type == 'shipping') {
					$config =& hikashop_config();
					$zone_id = explode(',',$config->get('main_tax_zone', $zone_id));
					if(count($zone_id))
						$zone_id = array_shift($zone_id);
					else
						$zone_id = 0;
					return (int)$zone_id;
				}
			}

			$addressClass = hikashop_get('class.address');
			$address = $addressClass->get($shipping_address);
			if(!empty($address)) {
				$field = 'address_country';
				if(!empty($address->address_state))
					$field = 'address_state';

				static $zones = array();
				if(empty($zones[$address->$field])) {
					$zoneClass = hikashop_get('class.zone');
					$zones[$address->$field] = $zoneClass->get($address->$field);
				}
				if(!empty($zones[$address->$field]))
					$zone_id = $zones[$address->$field]->zone_id;
			}
		}
		if(empty($zone_id)) {
			$zone_id = (int)$app->getUserState(HIKASHOP_COMPONENT.'.zone_id', 0);
			if(empty($zone_id)) {
				$config =& hikashop_config();
				$zone_id = explode(',', $config->get('main_tax_zone', $zone_id));
				if(count($zone_id))
					$zone_id = array_shift($zone_id);
				else
					$zone_id = 0;
				$app->setUserState(HIKASHOP_COMPONENT.'.zone_id', $zone_id);
			}
		}
		return (int)$zone_id;
	}
}

if(!function_exists('hikashop_getCurrency')) {
	function hikashop_getCurrency() {
		$config =& hikashop_config();
		$main_currency = (int)$config->get('main_currency', 1);
		$app = JFactory::getApplication();
		$currency_id = (int)$app->getUserState(HIKASHOP_COMPONENT.'.currency_id', $main_currency);

		if($currency_id != $main_currency && !hikashop_isClient('administrator')) {
			static $checked = array();
			if(!isset($checked[$currency_id])) {
				$checked[$currency_id]=true;
				$db = JFactory::getDBO();
				$db->setQuery('SELECT currency_id FROM '.hikashop_table('currency').' WHERE currency_id = '.$currency_id. ' AND (currency_published = 1 OR currency_displayed = 1)');
				$currency_id = (int)$db->loadResult();
			}
		}

		if(empty($currency_id)) {
			$app->setUserState(HIKASHOP_COMPONENT.'.currency_id', $main_currency);
			$currency_id = $main_currency;
		}
		return $currency_id;
	}
}

if(!function_exists('hikashop_cleanCart')) {
	function hikashop_cleanCart() {
		$config =& hikashop_config();
		$period = $config->get('cart_retaining_period');
		$check = $config->get('cart_retaining_period_check_frequency',1200);
		$checked = $config->get('cart_retaining_period_checked',0);
		$max = time()-$check;
		if(!$checked || $checked<$max){
			$database = JFactory::getDBO();
			$query = 'SELECT cart_id FROM '.hikashop_table('cart').' WHERE cart_type = '.$database->Quote('cart').' AND cart_modified < '.(time()-$period).' ORDER BY cart_modified ASC LIMIT 50';
			$database->setQuery($query);
			$ids = $database->loadColumn();
			if(!empty($ids)){
				$query = 'DELETE FROM '.hikashop_table('cart_product').' WHERE cart_id IN ('.implode(',',$ids).')';
				$database->setQuery($query);
				$database->execute();
				$query = 'DELETE FROM '.hikashop_table('cart').' WHERE cart_id IN ('.implode(',',$ids).')';
				$database->setQuery($query);
				$database->execute();
			}
			$options = array('cart_retaining_period_checked'=>time());
			$config->save($options);
		}
	}
}


if(!function_exists('hikashop_isAmpPage')) {
	function hikashop_isAmpPage() {
		$isAmpPage = false;
		if(class_exists('plgSystemWbamp') && method_exists('plgSystemWbamp', 'isAmpPage') && plgSystemWbamp::isAmpPage())
			$isAmpPage = true;
		return $isAmpPage;
	}
}



if(!function_exists('hikashop_import')) {
	function hikashop_import($type, $name, $dispatcher = null) {
		$type = preg_replace('#[^A-Z0-9_\.-]#i', '', $type);
		$name = preg_replace('#[^A-Z0-9_\.-]#i', '', $name);
		$path = JPATH_PLUGINS.DS.$type.DS.$name.DS.$name.'.php';
		$instance=false;
		if (file_exists( $path )){
			require_once( $path );
			if($type=='editors-xtd') $typeName = 'Button';
			else $typeName = $type;
			$className = 'plg'.$typeName.$name;
			if(class_exists($className)){
				if($dispatcher == null) {
					if(HIKASHOP_J40)
						$dispatcher = JFactory::getContainer()->get('dispatcher');
					else
						$dispatcher = JDispatcher::getInstance();
				}
				$instance = new $className($dispatcher, array('name'=>$name,'type'=>$type));
			}
		}
		return $instance;
	}
}

if(!function_exists('hikashop_copy')) {
	function hikashop_copy($src) {
		if(is_array($src)) {
			$array = array();
			foreach($src as $k => $v) {
				$array[$k] = hikashop_copy($v);
			}
			return $array;
		}

		if(is_object($src)) {
			$obj = new stdClass();
			foreach(get_object_vars($src) as $k => $v) {
				$obj->$k = hikashop_copy($v);
			}
			return $obj;
		}
		return $src;
	}
}


if(!function_exists('hikashop_createDir')) {
	function hikashop_createDir($dir, $report = true) {
		if(is_dir($dir)) return true;

		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');

		$indexhtml = '<html><body bgcolor="#FFFFFF"></body></html>';

		if(!JFolder::create($dir)){
			if($report) hikashop_display('Could not create the directly '.$dir,'error');
			return false;
		}
		if(!JFile::write($dir.DS.'index.html',$indexhtml)){
			if($report) hikashop_display('Could not create the file '.$dir.DS.'index.html','error');
		}
		return true;
	}
}

if(!function_exists('hikashop_initModule')) {
	function hikashop_initModule() {
		static $done = false;
		if($done)
			return true;
		$fe = hikaInput::get()->getVar('hikashop_front_end_main',0);
		if(!empty($fe))
			return true;
		$done = true;
		$lang = JFactory::getLanguage();
		$override_path = hikashop_getLanguagePath(JPATH_ROOT).DS.'overrides'.DS.$lang->getTag().'.override.ini';
		$lang->load(HIKASHOP_COMPONENT,JPATH_SITE);
		if(file_exists($override_path))
			hikashop_loadTranslationFile($override_path);
		return true;
	}
}

if(!function_exists('hikashop_absoluteURL')) {
	function hikashop_absoluteURL($text) {
		static $mainurl = '';
		if(empty($mainurl)){
			$urls = parse_url(HIKASHOP_LIVE);
			if(!empty($urls['path'])){
				$mainurl = substr(HIKASHOP_LIVE,0,strrpos(HIKASHOP_LIVE,$urls['path'])).'/';
			}else{
				$mainurl = HIKASHOP_LIVE;
			}
		}
		$text = str_replace(array('href="../undefined/','href="../../undefined/','href="../../../undefined//','href="undefined/'),array('href="'.$mainurl,'href="'.$mainurl,'href="'.$mainurl,'href="'.HIKASHOP_LIVE),$text);
		$text = preg_replace('#href="(/?administrator)?/({|%7B)#Uis','href="$2',$text);
		$replace = array();
		$replaceBy = array();
		if($mainurl !== HIKASHOP_LIVE){
			$replace[] = '#(href|src|action|background)[ ]*=[ ]*\"(?!(\{|%7B|\#|[a-z]{3,7}:|/))(?:\.\./)#i';
			$replaceBy[] = '$1="'.substr(HIKASHOP_LIVE,0,strrpos(rtrim(HIKASHOP_LIVE,'/'),'/')+1);
		}
		$replace[] = '#(href|src|action|background)[ ]*=[ ]*\"(?!(\{|%7B|\#|[a-z]{3,7}:|/))(?:\.\./|\./)?#i';
		$replaceBy[] = '$1="'.HIKASHOP_LIVE;
		$replace[] = '#(href|src|action|background)[ ]*=[ ]*\"(?!(\{|%7B|\#|[a-z]{3,7}:))/#i';
		$replaceBy[] = '$1="'.$mainurl;
		$replace[] = '#((background-image|background)[ ]*:[ ]*url\(\'?"?(?!([a-z]{3,7}:|/|\'|"))(?:\.\./|\./)?)#i';
		$replaceBy[] = '$1'.HIKASHOP_LIVE;
		return preg_replace($replace,$replaceBy,$text);
	}
}

if(!function_exists('hikashop_disallowUrlRedirect')) {
	function hikashop_disallowUrlRedirect($url) {
		$url = str_replace(array('http://www.','https://www.','https://'), array('http://','http://','http://'),strtolower($url));
		$live = str_replace(array('http://www.','https://www.','https://'), array('http://','http://','http://'),strtolower(HIKASHOP_LIVE));
		if(strpos($url,$live)!==0 && strpos(urldecode($url), $live) !== 0 && preg_match('#^http://.*#',$url)) return true;
		jimport('joomla.filter.filterinput');
		$safeHtmlFilter = JFilterInput::getInstance(array(), array(), 1, 1);
		if($safeHtmlFilter->clean($url,'string') != $url) return true;
		return false;
	}
}

if(!function_exists('hikashop_setTitle')) {
	function hikashop_setTitle($name, $picture, $link) {
		$app = JFactory::getApplication();
		if(!hikashop_isClient('administrator'))
			return false;
		$config =& hikashop_config();
		$menu_style = $config->get('menu_style','title_bottom');
		$menu_style = 'content_top';
		$html = '<a class="hikashop_title_link hikashop_title_j'.(int)HIKASHOP_JVERSION.'" href="'. hikashop_completeLink($link).'">'.$name.'</a>';
		if($menu_style != 'content_top') {
			$html = hikashop_getMenu($html,$menu_style);
		}
		JToolBarHelper::title( '<i class="fa fa-'.$picture.' hika-title-icons"></i>'.$html ,' hika-hide' );

		$doc = JFactory::getDocument();
		$doc->setTitle($app->getCfg('sitename'). ' - ' .JText::_('JADMINISTRATION').' - '.$name);
	}
}

if(!function_exists('hikashop_setPageTitle')) {
	function hikashop_setPageTitle($title) {
		$doc = JFactory::getDocument();
		$app = JFactory::getApplication();
		if(!empty($title)){
			$key = str_replace(',','_',$title);
			$title_name = JText::_($key);
			if($title_name==$key){
				$title_name = $title;
			}
		}
		if (empty($title_name)) {
			$title_name = $app->getCfg('sitename');
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 1) {
			$title_name = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title_name);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
			$title_name = JText::sprintf('JPAGETITLE', $title_name, $app->getCfg('sitename'));
		}
		$doc->setTitle( strip_tags($title_name) );
	}
}

if(!function_exists('hikashop_getMenu')) {
	function hikashop_getMenu($title = '', $menu_style = 'content_top') {
		if(HIKASHOP_J30) $menu_type = 'content_top';
		$document = JFactory::getDocument();
		$controller = new hikashopBridgeController(array('name'=>'menu'));
		$viewType = $document->getType();
		if(empty($viewType) || !in_array($viewType, array('html', 'feed'))) $viewType = 'html';
		$view = $controller->getView('', $viewType, '');

		$view->setLayout('default');
		ob_start();
		$view->display(null, $title, $menu_style);
		return ob_get_clean();
	}
}

if(!function_exists('hikashop_getLayout')) {
	function hikashop_getLayout($controller, $layout, $params, &$js, $backend = false) {
		$base_path=HIKASHOP_FRONT;
		$app = JFactory::getApplication();
		if(hikashop_isClient('administrator') || $backend){
			$base_path=HIKASHOP_BACK;
		}
		$base_path = rtrim($base_path,DS);
		$document = JFactory::getDocument();

		$controllerObj = new hikashopBridgeController(array('name'=>$controller,'base_path'=>$base_path));
		$viewType = $document->getType();
		if(empty($viewType) || !in_array($viewType, array('html', 'feed'))) $viewType = 'html';
		$view = $controllerObj->getNewView( '', $viewType, '',array('base_path'=>$base_path));
		if(empty($view)) {
			$controllerObj = hikashop_get('controller.'.$controller);
			$view = $controllerObj->getNewView( '', $viewType, '',array('base_path'=>HIKASHOP_ROOT.'plugins'.DS.'hikashop'.DS.$controller.DS));
		}

		$folder	= $base_path.DS.'views'.DS.$view->getName().DS.'tmpl';
		$view->addTemplatePath($folder);
		$folder	= JPATH_BASE.DS.'templates'.DS.$app->getTemplate().DS.'html'.DS.HIKASHOP_COMPONENT.DS.$view->getName();
		$view->addTemplatePath($folder);
		$old = $view->setLayout($layout);
		ob_start();
		$view->display(null,$params);
		$js = @$view->js;
		if(!empty($old))
			$view->setLayout($old);
		return ob_get_clean();
	}
}

if(!function_exists('hikashop_setExplorer')) {
	function hikashop_setExplorer($task, $defaultId = 0, $popup = false, $type = '') {
		$document = JFactory::getDocument();
		$controller = new hikashopBridgeController(array('name' => 'explorer'));
		$viewType = $document->getType();
		$view = $controller->getView('', $viewType, '');
		$view->setLayout('default');
		ob_start();
		$view->display(null, $task, $defaultId, $popup, $type);
		return ob_get_clean();
	}
}

if(!function_exists('hikashop_bytes')) {
	function hikashop_bytes($val) {
		$val = trim($val);
		if(empty($val))
			return 0;
		$last = strtolower($val[strlen($val)-1]);
		if(!is_numeric($last))
			$val = (int)substr($val, 0, -1);
		switch($last) {
			case 'g':
				$val *= 1024;
			case 'm':
				$val *= 1024;
			case 'k':
				$val *= 1024;
		}
		return (int)$val;
	}
}


if(!function_exists('hikashop_human_readable_bytes')) {
	function hikashop_human_readable_bytes($bytes, $decimals = 2, $system = 'binary') {
		$mod = ($system === 'binary') ? 1024 : 1000;
		$units = array(
			'binary' => array(
				'B',
				'KiB',
				'MiB',
				'GiB',
				'TiB',
				'PiB',
				'EiB',
				'ZiB',
				'YiB',
			),
			'metric' => array(
				'B',
				'kB',
				'MB',
				'GB',
				'TB',
				'PB',
				'EB',
				'ZB',
				'YB',
			),
		);
		$factor = floor((strlen((string)$bytes) - 1) / 3);
		return sprintf("%.{$decimals}f%s", $bytes / pow($mod, $factor), $units[$system][$factor]);
	}
}

if(!function_exists('hikashop_display')) {
	function hikashop_display($messages, $type = 'success', $return = false, $close = true) {
		if(empty($messages))
			return;
		if(!is_array($messages))
			$messages = array($messages);
		$display_messages = array();
		foreach($messages as $msg) {
			if(is_object($msg) || is_array($msg))
				continue;
			$display_messages[] = $msg;
		}
		$app = JFactory::getApplication();
		if((hikashop_isClient('administrator') && (!HIKASHOP_BACK_RESPONSIVE || HIKASHOP_J40)) || (!hikashop_isClient('administrator') && !HIKASHOP_RESPONSIVE)) {
			$html = '<div id="hikashop_messages_'.$type.'" class="hikashop_messages hikashop_'.$type.'"><ul><li>'.implode('</li><li>',$display_messages).'</li></ul></div>';
		} else {
			$html = '<div class="alert alert-'.$type.' alert-block">'.($close?'<button type="button" class="close" data-dismiss="alert" data-bs-dismiss="alert">×</button>':'').'<p>'.implode('</p><p>',$display_messages).'</p></div>';
		}

		if($return)
			return $html;
		echo $html;
	}
}

if(!function_exists('hikashop_frontendLink')) {
	function hikashop_frontendLink($link, $popup = false) {
		if($popup) $link .= '&tmpl=component';

		$menusClass = hikashop_get('class.menus');
		$id = 0;
		$to_be_replaced = '';
		if(preg_match('#Itemid=([0-9]+)#',$link,$match)){
			$to_be_replaced = $match[0];
			$new_id = $menusClass->loadAMenuItemId('','',$id);
		}
		if(empty($id)){
			$new_id = $menusClass->loadAMenuItemId('','');
		}

		$by = (empty($new_id)?'':'Itemid='.$new_id);
		if(empty($to_be_replaced)){
			$link .= '&'.$by;
		}else{
			$link = str_replace($to_be_replaced,$by,$link);
		}

		$config = hikashop_config();
		$app = JFactory::getApplication();
		if(!hikashop_isClient('administrator') && $config->get('activate_sef',0)){
			$link = ltrim(JRoute::_($link,false),'/');
		}

		static $mainurl = '';
		static $otherarguments = false;
		if(empty($mainurl)){
			$urls = parse_url(HIKASHOP_LIVE);
			if(isset($urls['path']) AND strlen($urls['path'])>0){
				$mainurl = substr(HIKASHOP_LIVE,0,strrpos(HIKASHOP_LIVE,$urls['path'])).'/';
				$otherarguments = trim(str_replace($mainurl,'',HIKASHOP_LIVE),'/');
				if(strlen($otherarguments) > 0) $otherarguments .= '/';
			}else{
				$mainurl = HIKASHOP_LIVE;
			}
		}

		if($otherarguments && strpos($link,$otherarguments) === false){
			$link = $otherarguments.$link;
		}

		return $mainurl.$link;
	}
}

if(!function_exists('hikashop_backendLink')) {
	function hikashop_backendLink($link, $popup = false) {
		static $mainurl = '';
		static $otherarguments = false;
		if(empty($mainurl)){
			$urls = parse_url(HIKASHOP_LIVE);
			if(!empty($urls['path'])){
				$mainurl = substr(HIKASHOP_LIVE,0,strrpos(HIKASHOP_LIVE,$urls['path'])).'/';
				$otherarguments = trim(str_replace($mainurl,'',HIKASHOP_LIVE),'/');
				if(!empty($otherarguments)) $otherarguments .= '/';
			}else{
				$mainurl = HIKASHOP_LIVE;
			}
		}
		if($otherarguments && strpos($link,$otherarguments) === false){
			$link = $otherarguments.$link;
		}
		return $mainurl.$link;
	}
}

if(!function_exists('hikashop_completeLink')) {
	function hikashop_completeLink($link, $popup = false, $redirect = false, $js = false, $frontend = false) {
		if($popup === 'ajax' && HIKASHOP_J30) $link .= '&tmpl=raw';
		else if($popup) $link .= '&tmpl=component';
		$link = 'index.php?option='.HIKASHOP_COMPONENT.'&ctrl='.$link;

		$config = hikashop_config();
		$app = JFactory::getApplication();
		if($frontend && hikashop_isClient('administrator')){
			static $mainurl = '';
			static $otherarguments = false;
			if(empty($mainurl)){
				$urls = parse_url(HIKASHOP_LIVE);
				if(isset($urls['path']) AND strlen($urls['path'])>0){
					$mainurl = substr(HIKASHOP_LIVE,0,strrpos(HIKASHOP_LIVE,$urls['path'])).'/';
					$otherarguments = trim(str_replace($mainurl,'',HIKASHOP_LIVE),'/');
					if(strlen($otherarguments) > 0) $otherarguments .= '/';
				}else{
					$mainurl = HIKASHOP_LIVE;
				}
			}

			if($otherarguments && strpos($link,$otherarguments) === false){
				$link = $otherarguments.$link;
			}

			$ret = $mainurl.$link;
		}else{
			$ret = JRoute::_($link,!$redirect);
		}

		if($js) return str_replace('&amp;', '&', $ret);
		return $ret;
	}
}

if(!function_exists('hikashop_contentLink')) {
	function hikashop_contentLink($link, $object, $popup = false, $redirect = false, $js = false, $frontend = false) {
		$config = hikashop_config();
		$force_canonical = $config->get('force_canonical_urls',1);
		if($force_canonical){
			$url = null;
			if(!isset($object->product_canonical) && !empty($object->product_id)){
				$productClass = hikashop_get('class.product');
				$objectData = $productClass->get($object->product_id);
				if(!empty($objectData->product_canonical))
				$object->product_canonical = $objectData->product_canonical;
			}
			if(!empty($object->product_canonical) || !empty($object->product_parent_id)){
				if(!empty($object->product_canonical)) {
					$url = hikashop_translate($object->product_canonical);
				}
				if(!empty($object->product_parent_id)) {
					$productClass = hikashop_get('class.product');
					$parent = $productClass->get($object->product_parent_id);
					if(empty($object->product_canonical) && !empty($parent->product_canonical)) {
						$url = hikashop_translate($parent->product_canonical);
					}
					$change_id_in_canonical_for_variant = $config->get('change_id_in_canonical_for_variant',1);
					if($change_id_in_canonical_for_variant) {
						if(empty($object->alias)) {
							if(!empty($object->product_alias))
								$object->alias = $object->product_alias;
							else
								$object->alias = $parent->alias;
						}
						$url = str_replace('/'.$parent->product_id.'-'.$parent->alias,'/'.$object->product_id.'-'.$object->alias,$url);
					}
				}
			}elseif(!empty($object->category_canonical)){
				$url = hikashop_translate($object->category_canonical);
			}

			if(!empty($url)){
				$url = hikashop_cleanURL($url, false, $frontend);
				if($popup){
					if(strpos($url,'?')!==false){
						$url.='&';
					}else{
						$url.='?';
					}
					$url .= 'tmpl=component';
				}
				if($js) return str_replace('&amp;', '&', $url);
				return $url;
			}
		}

		$app = JFactory::getApplication();
		$menusClass = hikashop_get('class.menus');
		if(hikashop_isClient('administrator')){
			$id = 0;
			$to_be_replaced = '';
			if(preg_match('#Itemid=([0-9]+)#',$link,$match)){
				$to_be_replaced = $match[0];
				$new_id = $menusClass->loadAMenuItemId('','',$id);
			}
			if(empty($id)){
				$new_id = $menusClass->loadAMenuItemId('','');
			}

			$by = (empty($new_id)?'':'Itemid='.$new_id);
			if(empty($to_be_replaced)){
				$link .= '&'.$by;
			}else{
				$link = str_replace($to_be_replaced,$by,$link);
			}

		}

		if(preg_match('#Itemid=([0-9]+)#',$link,$match)){
			$type = '';

			if(!empty($object->product_id)){
				$type = 'category';

				if($config->get('auto_search_menu_item_based_on_main_category', 1)) {
					$database = JFactory::getDBO();
					static $menuItems = null;
					if(is_null($menuItems)) {
						$filters = array(
							'a.type=\'component\'',
							'a.published=1',
							'b.title IS NOT NULL'
						);

						$user = JFactory::getUser();
						$accesses = JAccess::getAuthorisedViewLevels(@$user->id);
						if(!empty($accesses)){
							$filters[]='a.access IN ('.implode(',',$accesses).')';
						}

						$filters[] = 'a.client_id=0';
						$filters[] = '(a.link='.$database->Quote('index.php?option=com_hikashop&view=category&layout=listing').' OR a.link='.$database->Quote('index.php?option=com_hikashop&view=product&layout=listing').')';

						$lang = JFactory::getLanguage();
						$tag = $lang->getTag();
						$filters[] = "a.language IN ('*', '', ".$database->Quote($tag).")";

						$query="SELECT a.id, a.link, a.params FROM ".hikashop_table('menu',false).' AS a ' .
							'INNER JOIN `#__menu_types` AS b ON a.menutype = b.menutype ' .
							'WHERE ' .  implode(' AND ',$filters);
						$database->setQuery($query);
						$menuItems = $database->loadObjectList();
					}

					if (!empty($menuItems)) {
						$database->setQuery('SELECT c.category_id ' .
							'FROM '.hikashop_table('product_category') . ' AS pc ' .
							'INNER JOIN '.hikashop_table('category') . ' AS c ' .
							'ON c.category_id = pc.category_id ' .
							'AND c.category_published = 1 ' .
							'WHERE pc.product_id = ' . ((@$object->product_type == 'variant') ? $object->product_parent_id : $object->product_id) . ' ' .
							"ORDER BY pc.product_category_id ASC");
						$category = $database->loadObject();

						if(!empty($category)) {
							foreach($menuItems as $menuItem) {
								$params = json_decode($menuItem->params);
								if ($menuItem->link == 'index.php?option=com_hikashop&view=category&layout=listing' && !empty($params->hk_category->category) && $category->category_id == $params->hk_category->category) {
									$link = str_replace('Itemid='.$match[1],'Itemid='.$menuItem->id,$link);
									$type = '';
									break;
								}
								if ($menuItem->link == 'index.php?option=com_hikashop&view=product&layout=listing' && !empty($params->hk_product->category) && $category->category_id == $params->hk_product->category) {
									$link = str_replace('Itemid='.$match[1],'Itemid='.$menuItem->id,$link);
									$type = '';
									break;
								}
							}
						}
					}
				}

			}elseif(!empty($object->category_id)){
				if(isset($object->category_type) && $object->category_type=='manufacturer'){
					$type = 'manufacturer';
				}else{
					$type = 'category';
				}
			}elseif(!empty($object->order_id)){
				$id = $menusClass->loadAMenuItemId('order', 'listing');
				if(empty($id)){
					$id = $menusClass->loadAMenuItemId('user', 'cpanel');
				}
				if(!empty($id))
					$link = str_replace('Itemid='.$match[1],'Itemid='.$id,$link);
			}
			if(!empty($type)){
				$id = $menusClass->loadAMenuItemId($type,'listing',$match[1]);
				if(empty($id)){
					$id = $menusClass->loadAMenuItemId('product','listing',$match[1]);
					if(empty($id)){
						$id = $menusClass->loadAMenuItemId($type,'listing');
						if(!empty($id))
							$link = str_replace('Itemid='.$match[1],'Itemid='.$id,$link);
					}
				}

			}
		}

		$url = hikashop_completeLink($link,$popup,$redirect, $js, $frontend);
		if($force_canonical==2){
			if(!empty($object->product_id)){
				$newObj = new stdClass();
				$newObj->product_id = $object->product_id;
				$newObj->product_canonical = $url;
				$productClass = hikashop_get('class.product');
				$productClass->save($newObj);
			}elseif(!empty($object->category_id)){
				$newObj = new stdClass();
				$newObj->category_id = $object->category_id;
				$newObj->category_canonical = $url;
				$categoryClass = hikashop_get('class.category');
				$categoryClass->save($newObj);
			}
		}
		return $url;
	}
}

if(!function_exists('hikashop_table')) {
	function hikashop_table($name, $component = true) {
		$prefix = $component ? HIKASHOP_DBPREFIX : '#__';
		return $prefix.$name;
	}
}

if(!function_exists('hikashop_secureField')) {
	function hikashop_secureField($fieldName) {
		if (!is_string($fieldName) || preg_match('|[^a-z0-9#_.-]|i',$fieldName) !== 0 ){
			jimport('joomla.filter.filterinput');
			$safeHtmlFilter = JFilterInput::getInstance(array(), array(), 1, 1);
			die('field "'.htmlentities($safeHtmlFilter->clean($fieldName,'string')) .'" not secured');
		}
		return $fieldName;
	}
}

if(!function_exists('hikashop_translate')) {
	function hikashop_translate($name, $language_code = null) {
		if(is_null($name))
			return '';

		if(substr($name,0,9) == '#notrans#') {
			return substr($name,9);
		}

		$val = preg_replace('#[^A-Z_0-9]#','',strtoupper($name));
		$config = hikashop_config();
		if((empty($val) || $config->get('non_latin_translation_keys', 0)) && !empty($name)) {
			$val = 'T'.strtoupper(sha1($name));
		} elseif(is_numeric($val)) {
			$val = 'T'.$val;
		}

		if(!empty($language_code)) {
			$lang = JFactory::getLanguage();
			$old_code = $lang->getTag();
			if($old_code != $language_code) {
				hikashop_clearTranslationKey($val);
				hikashop_loadHikashopTranslations($language_code);
			}
		}

		$trans = JText::_($val);
		if($val == $trans)
			$trans = $name;

		if(!empty($language_code) && $old_code != $language_code) {
			hikashop_loadHikashopTranslations($old_code);
		}

		return $trans;
	}
}

if(!function_exists('hikashop_increasePerf')) {
	function hikashop_increasePerf() {
		@ini_set('max_execution_time',0);
		if(hikashop_bytes(@ini_get('memory_limit')) < 60000000){
			$config = hikashop_config();
			if($config->get('hikaincreasemem','1')){
				if(!empty($_SESSION['hikaincreasemem'])){
					$newConfig = new stdClass();
					$newConfig->hikaincreasemem = 0;
					$config->save($newConfig);
					unset($_SESSION['hikaincreasemem']);
					return;
				}
				if(isset($_SESSION)) $_SESSION['hikaincreasemem'] = 1;
				@ini_set('memory_limit','256M');
				if(isset($_SESSION['hikaincreasemem'])) unset($_SESSION['hikaincreasemem']);
			}
		}
	}
}
if(!function_exists('hikashop_config')) {
	function &hikashop_config($reload = false) {
		static $configClass = null;
		if($configClass === null || $reload || !is_object($configClass) || $configClass->get('configClassInit',0) == 0){
			$configClass = hikashop_get('class.config');
			$configClass->load();
			$configClass->set('configClassInit',1);
		}
		return $configClass;
	}
}

function hikashop_level($level) {
	$config =& hikashop_config();
	if($config->get($config->get('level'),0) >= $level) return true;
	return false;
}

if(!function_exists('hikashop_footer')) {
	function hikashop_footer() {
		$config =& hikashop_config();
		if($config->get('show_footer',true)=='-1') return '';
		$description = $config->get('description_'.strtolower($config->get('level')),'Joomla!<sup>&reg;</sup> Ecommerce System');
		$link = 'http://www.hikashop.com';
		$aff = $config->get('partner_id');
		if(!empty($aff)){
			$link.='?partner_id='.$aff;
		}
		$text = '<!--  HikaShop Component powered by '.$link.' -->
		<!-- version '.$config->get('level').' : '.$config->get('version').' [2305311516] -->';
		if(!$config->get('show_footer',true)) return $text;
		$text .= '<div class="hikashop_footer" style="text-align:center"><a href="'.$link.'" target="_blank" title="'.HIKASHOP_NAME.' : '.strip_tags($description).'">'.HIKASHOP_NAME.' ';
		$app= JFactory::getApplication();
		if(hikashop_isClient('administrator')){
			$text .= $config->get('level').' '.$config->get('version');
		}
		$text .= ', '.$description.'</a></div>';
		return $text;
	}
}

if(!function_exists('hikashop_search')) {
	function hikashop_search($searchString, $object, $exclude = '') {
		if(empty($object) || is_numeric($object))
			return $object;
		if(is_string($object)){
			return preg_replace('#('.str_replace(array('#','(',')','.','[',']','?','*'),array('\#','\(','\)','\.','\[','\]','\?','\*'),$searchString).')#i','<span class="searchtext">$1</span>',$object);
		}
		if(is_array($object)){
			foreach($object as $key => $element){
				$object[$key] = hikashop_search($searchString,$element,$exclude);
			}
		}elseif(is_object($object)){
			foreach($object as $key => $element){
				if((is_string($exclude) && $key != $exclude) || (is_array($exclude) && !in_array($key, $exclude)))
					$object->$key = hikashop_search($searchString,$element,$exclude);
			}
		}
		return $object;
	}
}

if(!function_exists('hikashop_get')) {
	function hikashop_get($path) {

		if(strpos($path, '/') !== false || strpos($path, '\\') !== false)
			return null;
		list($group, $class) = explode('.', strtolower($path));
		if($group == 'controller') {
			$className = $class.ucfirst($group);
		} elseif(strpos($class, '-') === false) {
			$className = 'hikashop'.ucfirst($class).ucfirst($group);
		} else {
			$blocks = explode('-', $class);
			$blocks = array_map('ucfirst', $blocks);
			$className = 'hikashop'.implode('', $blocks).ucfirst($group);
		}

		if(class_exists($className.'Override'))
			$className .= 'Override';
		if(!class_exists($className)) {
			$class = str_replace('-', DS, $class);
			$app = JFactory::getApplication();
			$override = '';
			if(!empty($app) && method_exists($app, 'getTemplate') && (hikashop_isClient('administrator') || defined('HIKASHOP_JOOMLA_LOADED'))) {
				try{
					$path = JPATH_THEMES.DS.$app->getTemplate().DS.'html'.DS.'com_hikashop'.DS.'administrator'.DS;
					$override = str_replace(HIKASHOP_BACK, $path, constant(strtoupper('HIKASHOP_'.$group))).$class.'.override.php';
				}catch (Exception $e) {
				}
			}

			$include_file = constant('HIKASHOP_'.strtoupper($group)).$class.'.php';
			if(JFile::exists($include_file))
				include_once($include_file);
			elseif($group == 'controller') {
				hikashop_getPluginController($class);
			}
			if(!empty($override) && JFile::exists($override)) {
				include_once($override);
				$className .= 'Override';
			}
		}
		if(!class_exists($className)) return null;

		$args = func_get_args();
		array_shift($args);
		switch(count($args)){
			case 3:
				return new $className($args[0],$args[1],$args[2]);
			case 2:
				return new $className($args[0],$args[1]);
			case 1:
				return new $className($args[0]);
			case 0:
			default:
				return new $className();
		}
	}
}
if(!function_exists('hikashop_getPluginController')) {
	function hikashop_getPluginController($ctrl) {
		if(empty($ctrl))
			return false;

		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$controllers = $app->triggerEvent('onHikashopPluginController', array($ctrl));

		if(empty($controllers))
			return false;
		foreach($controllers as $k => &$c) {
			if(!is_array($c) && is_string($c))
				$c = array('name' => $c);
			if(empty($c['name'])) {
				unset($controllers[$k]);
				continue;
			}
			if(empty($c['type']))
				$c['type'] = 'hikashop';
		}
		unset($c);

		if(count($controllers) > 1)
			return false;

		$controller = reset($controllers);

		if(empty($controller['prefix']))
			$controller['prefix'] = 'ctrl';

		$type = preg_replace('#[^A-Z0-9_\.-]#i', '', $controller['type']);
		$name = preg_replace('#[^A-Z0-9_\.-]#i', '', $controller['name']);
		$prefix = preg_replace('#[^A-Z0-9_]#i', '', $controller['prefix']);
		$path = JPATH_PLUGINS.DS.$type.DS.$name.DS;

		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');

		if(!empty($controller['component']) && preg_match('#^com_[_a-zA-Z0-9]+$#', $controller['component'])) {
			$path = rtrim(JPATH_SITE,DS).DS.'components'.DS.$controller['component'].DS;
			$file = isset($controller['file']) ? preg_replace('#[^A-Z0-9_\.-]#i', '', $controller['file']) : $name;
			if(JFile::exists($path.'controllers'.DS.$file.'.php') && JFolder::exists($path.'views'.DS.$name.'shop'.DS)) {
				include_once($path.'controllers'.DS.$file.'.php');
				return true;
			}
		}

		if(!JFile::exists($path.$name.'_'.$prefix.'.php') || (empty($controller['noview']) && !JFolder::exists($path.'views'.DS)))
			return false;

		include_once($path.$name.'_'.$prefix.'.php');
		return true;
	}
}
if(!function_exists('hikashop_getCID')) {
	function hikashop_getCID($field = '', $int = true) {
		$oneResult = hikaInput::get()->get('cid', array(), 'array');
		if(is_array($oneResult)) $oneResult = reset($oneResult);
		if(empty($oneResult) && !empty($field)) $oneResult = hikaInput::get()->getCmd($field, 0);
		if($int) return intval($oneResult);
		return $oneResult;
	}
}

if(!function_exists('hikashop_tooltip')) {
	function hikashop_tooltip($desc, $title = '', $image = 'tooltip.png', $name = '', $href = '', $link = 1) {
		static $class = null;
		if($class === null) {
			$class = HIKASHOP_J30 ? 'hasTooltip' : 'hasTip';
			if(HIKASHOP_J30) {
				$app = JFactory::getApplication();
				$config = hikashop_config();
				if(hikashop_isClient('administrator') || $config->get('bootstrap_design', HIKASHOP_J30))
					JHtml::_('bootstrap.tooltip');
				else
					$class = 'hasTip';
			}
		}
		return JHTML::_('tooltip', str_replace(array("'", "::"), array("&#039;", ": : "), $desc . ' '), str_replace(array("'", '::'), array("&#039;", ': : '), $title), $image, str_replace(array("'", '"', '::'), array("&#039;", "&quot;", ': : '), $name . ' '), $href, $link, $class);
	}
}

if(!function_exists('hikashop_hktooltip')) {
	function hikashop_hktooltip($desc, $title = '', $name = '', $href = '') {
		hikashop_loadJslib('tooltip');
		$desc = htmlspecialchars($desc, ENT_COMPAT, 'UTF-8');
		$title = htmlspecialchars($title, ENT_COMPAT, 'UTF-8');
		if($href) $name = '<a href="' . $href . '">' . $name . '</a>';
		if($title) $desc = '&lt;strong&gt;'.$title.'&lt;/strong&gt;&lt;br/&gt;' . $desc;
		return '<span data-toggle="hk-tooltip" data-title="' . $desc . '">' . $name . '</span>';
	}
}
if(!function_exists('hikashop_checkRobots')) {
	function hikashop_checkRobots() {
		if(preg_match('#(libwww-perl|python)#i',@$_SERVER['HTTP_USER_AGENT']))
			die('Not allowed for robots. Please contact us if you are not a robot');
	}
}

if(!function_exists('hikashop_clearTranslationKey')) {
	function hikashop_clearTranslationKey($translationKey) {
		$loadOverride = function($key) {
			if(empty($this->lang)) return false;
			unset($this->override[$key]);
			unset($this->strings[$key]);
			$ret = true;
		};
		$lang = JFactory::getLanguage();
		$loadOverrideCB = $loadOverride->bindTo($lang, 'JLanguage');
		$loadOverrideCB($translationKey);
	}
}

if(!function_exists('hikashop_loadTranslationFile')) {
	function hikashop_loadTranslationFile($path) {
		$loadOverride = function($filename = null) {
			$ret = false;
			if(empty($this->lang) && empty($filename)) return $ret;
			if(empty($filename))
				$filename = JPATH_BASE.'/language/overrides/'.$this->lang.'.override.ini';
			if(file_exists($filename) && $contents = $this->parse($filename)) {
				if(is_array($contents)) {
					$this->override = array_merge($this->override, $contents);
					$this->strings = array_merge($this->strings, $this->override);
					$ret = true;
				}
				unset($contents);
			}
			return $ret;
		};
		$lang = JFactory::getLanguage();
		$loadOverrideCB = $loadOverride->bindTo($lang, 'JLanguage');
		$loadOverrideCB($path);
	}
}

if(!function_exists('hikashop_loadHikashopTranslations')) {
	function hikashop_loadHikashopTranslations($locale) {
		$path = hikashop_getLanguagePath(JPATH_ROOT).DS.$locale.DS.$locale.'.com_hikashop.ini';
		$override_path = hikashop_getLanguagePath(JPATH_ROOT).DS.'overrides'.DS.$locale.'.override.ini';
		if(file_exists($path))
			hikashop_loadTranslationFile($path);

		JPluginHelper::importPlugin('hikashop');
		JPluginHelper::importPlugin('hikashopshipping');
		JPluginHelper::importPlugin('hikashoppayment');
		$app = JFactory::getApplication();
		$app->triggerEvent('onHikashopLanguageChange', array($locale));

		if(file_exists($override_path))
			hikashop_loadTranslationFile($override_path);
	}
}

if(!function_exists('hikashop_getHTML')) {
	function hikashop_getHTML($lambdaFunction) {
		$doc = JFactory::getDocument();
		$scripts_already = count($doc->_scripts);
		$script_already = count($doc->_script);
		$css_already = count($doc->_styleSheets);
		ob_start();
		$lambdaFunction();
		$html = ob_get_clean();
		foreach($doc->_scripts as $script => $v) {
			if($scripts_already){
				$scripts_already--;
				continue;
			}
			$html.= '<script src="'.$script.'" type="text/javascript"></script>'."\r\n";
		}
		foreach($doc->_styleSheets as $css => $v) {
			if($css_already){
				$css_already--;
				continue;
			}
			$html.= '<style type="text/css">'."\r\n@import url(".$css.");\r\n".'</style>';
		}
		foreach($doc->_script as $script) {
			if($script_already){
				$script_already--;
				continue;
			}
			if(is_array($script))
				$script = implode("\r\n",$script);
			$html.= '<script type="text/javascript">'."\r\n".$script."\r\n".'</script>';
		}
		return $html;
	}
}

if(!function_exists('hikashop_loadJslib')) {
	function hikashop_loadJslib($name, $data = null) {
		static $loadLibs = array();
		static $toLoad = array();
		$doc = JFactory::getDocument();
		$name = strtolower($name);
		$ret = false;
		if(isset($loadLibs[$name]) && $loadLibs[$name] !== null)
			return $loadLibs[$name];

		if(HIKASHOP_J40) {
			$app = JFactory::getApplication();
			$document = $app->getDocument();
			if(empty($document)) {
				$toLoad[$name] = $name;
				return;
			} elseif(count($toLoad)) {
				$copy = hikashop_copy($toLoad);
				$toLoad = array();
				foreach($copy as $lib) {
					hikashop_loadJslib($lib);
				}
			}
		}

		$js = 0;
		$css = 0;
		if(!hikashop_isClient('cli')) {
			$config = hikashop_config();
			$js = $config->get('load_js', 1) || hikashop_isClient('administrator');
			$css = $config->get('load_css',1) || hikashop_isClient('administrator');
		}

		switch($name) {
			case 'mootools':
				if($js) {
					if(!HIKASHOP_J30)
						JHTML::_('behavior.mootools');
					else
						JHTML::_('behavior.framework');
				}
				$ret = true;
				break;
			case 'jquery':
				if($js) {
					if(HIKASHOP_J30) {
						try {
							JHtml::_('jquery.framework');
							if(!HIKASHOP_J40)
								JHtml::_('jquery.ui', array('core', 'sortable'));
						} catch(Exception $e) {
							$doc->addScript(HIKASHOP_JS.'jquery.min.js');
						}
					} else {
						$doc->addScript(HIKASHOP_JS.'jquery.min.js');
					}
					$doc->addScript(HIKASHOP_JS.'jquery-ui.min.js');
				}
				$ret = true;
				break;
			case 'tooltip':
				if($js) {
					hikashop_loadJslib('jquery');
					$doc->addScript(HIKASHOP_JS.'tooltip.js');
				}
				if($css)
					$doc->addStyleSheet(HIKASHOP_CSS.'tooltip.css');
				$ret = true;
				break;
			case 'owl-carousel':
				if($js) {
					hikashop_loadJslib('jquery');
					$doc->addScript(HIKASHOP_JS.'owl.carousel.min.js');
				}
				if($css) {
					$doc->addStyleSheet(HIKASHOP_CSS.'owl.carousel.min.css');
					$doc->addStyleSheet(HIKASHOP_CSS.'owl.theme.default.min.css');
				}
				$ret = true;
				break;
			case 'otree':
				if($js) {
					hikashop_loadJslib('jquery');
					$doc->addScript(HIKASHOP_JS.'otree.js?v='.HIKASHOP_RESSOURCE_VERSION);
				}
				if($css)
					$doc->addStyleSheet(HIKASHOP_CSS.'otree.css?v='.HIKASHOP_RESSOURCE_VERSION);
				$ret = true;
				break;
			case 'opload':
				if($js) {
					hikashop_loadJslib('jquery');
					$doc->addScript(HIKASHOP_JS.'opload.js?v='.HIKASHOP_RESSOURCE_VERSION);
				}
				if($css)
					$doc->addStyleSheet(HIKASHOP_CSS.'opload.css?v='.HIKASHOP_RESSOURCE_VERSION);
				$ret = true;
				break;
			case 'vex':
				if($js) {
					try {
						if(HIKASHOP_J30)
							JHtml::_('jquery.framework');
						else
							hikashop_loadJslib('jquery');
					} catch(Exception $e) {
						$doc->addScript(HIKASHOP_JS.'jquery.min.js');
					}
					$doc->addScript(HIKASHOP_JS.'vex.min.js?v='.HIKASHOP_RESSOURCE_VERSION);
				}
				if($css)
					$doc->addStyleSheet(HIKASHOP_CSS.'vex.css?v='.HIKASHOP_RESSOURCE_VERSION);
				$ret = true;
				break;
			case 'notify':
				if($js) {
					try {
						if(HIKASHOP_J30)
							JHtml::_('jquery.framework');
						else
							hikashop_loadJslib('jquery');
					} catch(Exception $e) {
						$doc->addScript(HIKASHOP_JS.'jquery.min.js');
					}
					$doc->addScript(HIKASHOP_JS.'notify.min.js?v='.HIKASHOP_RESSOURCE_VERSION);
				}
				if($css)
					$doc->addStyleSheet(HIKASHOP_CSS.'notify-metro.css?v='.HIKASHOP_RESSOURCE_VERSION);
				$ret = true;
				break;
			case 'creditcard':
				if($js)
					$doc->addScript(HIKASHOP_JS.'creditcard.js?v='.HIKASHOP_RESSOURCE_VERSION);
				$ret = true;
				break;
			case 'dropdown':
				if($js) {
					hikashop_loadJslib('jquery');
					$doc->addScript(HIKASHOP_JS.'dropdown.js?v='.HIKASHOP_RESSOURCE_VERSION);
				}
				if($css)
					$doc->addStyleSheet(HIKASHOP_CSS.'dropdown.css?v='.HIKASHOP_RESSOURCE_VERSION);
				$ret = true;
				break;
			case 'font-awesome':
				if($css) {
					$admin = hikashop_isClient('administrator');
					$fa_type = $config->get('font-awesome-type', '');
					if(empty($fa_type) || ($admin && $fa_type == 'admin') || (!$admin && $fa_type == 'front')) {
						$fa = $config->get('font-awesome', 'local');
						if(!in_array($fa, array('local','cdn','none')))
							$fa = 'local';
						if($fa == 'local')
							$doc->addStyleSheet(HIKASHOP_CSS.'font-awesome.css?v=5.2.0');
						if($fa == 'cdn')
							$doc->addStyleSheet('https://use.fontawesome.com/releases/v5.2.0/css/all.css');
					}
				}
				$ret = true;
				break;
			case 'translations':
				$js = '';
				if(!isset($loadLibs[$name]))
					$js = 'window.hikashop.translations_url = "' . hikashop_completeLink('translations&task=load', false, false, true).'";';
				if(!empty($data))
					$js .= ' window.hikashop.addTrans('.json_encode($data).');';
				if(!empty($js))
					$doc->addScriptDeclaration($js);
				$ret = null;
				break;
			case 'nouislider':
				if($js)
					$doc->addScript(HIKASHOP_JS.'nouislider.min.js?v='.HIKASHOP_RESSOURCE_VERSION);
				if($css)
					$doc->addStyleSheet(HIKASHOP_CSS.'nouislider.min.css?v='.HIKASHOP_RESSOURCE_VERSION);
				$ret = true;
				break;
			case 'wnumb':
				if($js)
					$doc->addScript(HIKASHOP_JS.'wNumb.js?v='.HIKASHOP_RESSOURCE_VERSION);
				$ret = true;
				break;
			case 'sortable':
				if($js)
					$doc->addScript(HIKASHOP_JS.'Sortable.min.js?v='.HIKASHOP_RESSOURCE_VERSION);
				$ret = true;
				break;
			case 'formcustom':
				if($js) {
					$doc->addScript(HIKASHOP_JS.'Sortable.min.js?v='.HIKASHOP_RESSOURCE_VERSION);
					$doc->addScript(HIKASHOP_JS.'formCustom.js?v='.HIKASHOP_RESSOURCE_VERSION);
				}
				$ret = true;
				break;
			case 'listingcustom':
				if($js) {
					$doc->addScript(HIKASHOP_JS.'Sortable.min.js?v='.HIKASHOP_RESSOURCE_VERSION);
					$doc->addScript(HIKASHOP_JS.'listingCustom.js?v='.HIKASHOP_RESSOURCE_VERSION);
				}
				$ret = true;
				break;
		}

		$loadLibs[$name] = $ret;
		return $ret;
	}
}

if(!function_exists('hikashop_writeToLog')) {
	function hikashop_writeToLog($data = null, $name = '') {
		$dbg = ($data === null) ? ob_get_clean() : $data;
		if(!empty($dbg)) {
			if(is_array($dbg) || is_object($dbg))
				$dbg = '<pre>'.str_replace(array("\r","\n","\r\n"),"\r\n",print_r($dbg, true)).'</pre>';

			$dbg = "\r\n".'<h3>' . date('m.d.y H:i:s') . (!empty($name) ? (' - '.$name) : '') . '</h3>'."\r\n" . $dbg;

			jimport('joomla.filesystem.file');
			$config = hikashop_config();
			$file = $config->get('payment_log_file', '');

			if(preg_match_all('#\{date *format ?= ?(?:"|\')(.*)(?:"|\') *\}#Ui',$file,$matches)) {
				foreach($matches[0] as $k => $match) {
					$file = str_replace($match,date($matches[1][$k],time()),$file);
				}
			}

			$file = rtrim(JPath::clean(html_entity_decode($file)), DS . ' ');
			if(!preg_match('#^([A-Z]:)?/.*#',$file) && (!$file[0] == '/' || !file_exists($file)))
				$file = JPath::clean(HIKASHOP_ROOT . DS . trim($file, DS . ' '));
			if(!empty($file) && defined('FILE_APPEND')) {
				if(!file_exists(dirname($file))) {
					jimport('joomla.filesystem.folder');
					JFolder::create(dirname($file));
				}
				file_put_contents($file, $dbg, FILE_APPEND);
			}
		}
		if($data === null)
			ob_start();
	}
}

if(!function_exists('hikashop_cleanURL')) {
	function hikashop_cleanURL($url, $forceInternURL = false, $frontend = false) {
		$parsedURL = parse_url($url);
		$parsedCurrent = parse_url(JURI::base());

		if($forceInternURL == false && isset($parsedURL['scheme']))
			return $url;

		if(preg_match('#https?://#',$url)){
			return $url;
		}

		if(preg_match('#www.#',$url)){
			return $parsedCurrent['scheme'].'://'.$url;
		}
		if(!isset($parsedURL['path']))
			$parsedURL['path'] = '';

		if(!empty($parsedURL['path']) && $parsedURL['path'][0]!='/'){
			$parsedURL['path']='/'.$parsedURL['path'];
		}

		if(!isset($parsedURL['query']))
			$endUrl = $parsedURL['path'];
		else
			$endUrl = $parsedURL['path'].'?'.$parsedURL['query'];

		if(!empty($parsedURL['fragment'])) {
			$endUrl .= '#'.$parsedURL['fragment'];
		}

		$port = '';
		if(!empty($parsedCurrent['port']) && $parsedCurrent['port']!= 80){
			$port = ':'.$parsedCurrent['port'];
		}

		if(isset($parsedCurrent['path']) && !preg_match('#^/?'.$parsedCurrent['path'].'#', $endUrl)) {
			$parsedCurrent['path'] = preg_replace('#/$#', '', $parsedCurrent['path']);
			$app = JFactory::getApplication();
			if($frontend && hikashop_isClient('administrator') && strpos($parsedCurrent['path'], '/administrator') === 0)
				$parsedCurrent['path'] = substr($parsedCurrent['path'], 14);
		} else
			$parsedCurrent['path'] = '';

		$cleanUrl = $parsedCurrent['scheme'].'://'.$parsedCurrent['host'].$port.$parsedCurrent['path'].$endUrl;
		return $cleanUrl;
	}
}

if(!function_exists('hikashop_orderStatus')) {
	function hikashop_orderStatus($order_status) {
		static $statuses = null;
		if(empty($statuses)){
			$db = JFactory::getDBO();
			$db->setQuery('SELECT orderstatus_name, orderstatus_namekey FROM #__hikashop_orderstatus WHERE orderstatus_published=1');
			$statuses = $db->loadObjectList('orderstatus_namekey');
		}
		if(isset($statuses[$order_status])){
			$order_status = $statuses[$order_status]->orderstatus_name;
		}

		$order_upper = HikaStringHelper::strtoupper($order_status);
		$tmp = 'ORDER_STATUS_' . $order_upper;
		$ret = JText::_($tmp);
		if($ret != $tmp)
			return $ret;
		$ret = JText::_($order_upper);
		if($ret != $order_upper)
			return $ret;
		return $order_status;
	}
}

if(!function_exists('hikashop_getEscaped')) {
	function hikashop_getEscaped($text, $extra = false) {
		return JFactory::getDBO()->escape($text, $extra);
	}
}

if(!function_exists('hikashop_logData')) {
	function hikashop_logData($data = null, $name = null) {
		$dbg = ($data === null) ? ob_get_clean() : $data;
		if(!empty($dbg)) {
			if(!is_string($dbg))
				$dbg = '<pre>'.str_replace(array("\r","\n","\r\n"),"\r\n",print_r($dbg, true)).'</pre>';

			$dbg = "\r\n".'<h3>' . date('m.d.y H:i:s') . (!empty($name) ? (' - '.$name) : '') . '</h3>'."\r\n" . $dbg;

			jimport('joomla.filesystem.file');
			$config = hikashop_config();
			$file = $config->get('payment_log_file', '');
			$file = rtrim(JPath::clean(html_entity_decode($file)), DS . ' ');
			if(!preg_match('#^([A-Z]:)?/.*#',$file) && (!$file[0] == '/' || !file_exists($file)))
				$file = JPath::clean(HIKASHOP_ROOT . DS . trim($file, DS . ' '));
			if(!empty($file) && defined('FILE_APPEND')) {
				if(!file_exists(dirname($file))) {
					jimport('joomla.filesystem.folder');
					JFolder::create(dirname($file));
				}
				file_put_contents($file, $dbg, FILE_APPEND);
			}
		}
		if($data === null)
			ob_start();
	}
}

if(!function_exists('hikashop_nocache')) {
	function hikashop_nocache() {
		if(headers_sent())
			return false;

		header('Cache-Control: no-store, no-cache, must-revalidate');
		header('Cache-Control: post-check=0, pre-check=0', false);
		header('Pragma: no-cache');
		header('Expires: Wed, 17 Sep 1975 21:32:10 GMT');
		return true;
	}
}

if(!function_exists('hikashop_cleanBuffers')) {
	function hikashop_cleanBuffers() {
		$previous = 0;
		while(ob_get_level() != $previous){
			$previous = ob_get_level();
			@ob_end_clean();
		}
	}
}

if(!function_exists('hikashop_limitString')) {
	function hikashop_limitString($string, $limit, $replacement = '...', $tooltip = false) {
		if(empty($string) || !is_string($string))
			return '';
		$l = strlen($string);
		if($l <= $limit)
			return $string;

		$nbExtra = $l - $limit + strlen($replacement);
		$new_string = substr($string, 0, $l - ceil(($l + $nbExtra) / 2)) . $replacement . substr($string, floor(($l + $nbExtra) / 2));
		if($tooltip)
			return hikashop_tooltip($string, '', '', $new_string, '', 0);
		return $new_string;
	}
}

if(!function_exists('hikashop_getLanguagePath')) {
	function hikashop_getLanguagePath($basePath = JPATH_BASE, $language = null) {
		if(HIKASHOP_J40)
			return JLanguageHelper::getLanguagePath($basePath, $language);
		return JLanguage::getLanguagePath($basePath, $language);
	}
}
function hikashop_acl($acl) {
	return true;
}


if(!function_exists('hikashop_unserialize')) {
	function hikashop_unserialize($data) {
		if(!is_string($data))
			return false;
		if(!preg_match_all('#[OC]:[0-9]+:"([-_a-zA-Z0-9]+)":[0-9]+:\{#iU', $data, $matches))
			return unserialize($data);
		if(!empty($matches[1])) {
			foreach($matches[1] as $m) {
				if($m != 'stdClass')
					return false;
			}
		}
		return unserialize($data);
	}
}
if(!function_exists('hikashop_db_quote')) {
	function hikashop_db_quote($data) {
		$db = JFactory::getDBO();
		if(!is_array($data))
			return $db->Quote($data);
		$ret = array();
		foreach($data as $k => $d) {
			$ret[$k] = $db->Quote($d);
		}
		return $ret;
	}
}

if(!HIKASHOP_J30){
	function hikashop_getFormToken() {
		return JUtility::getToken();
	}
} else {
	function hikashop_getFormToken() {
		return JSession::getFormToken();
	}
}
if(!HIKASHOP_J40){
	function hikashop_isClient($type) {
		static $test = array();
		if(!isset($test[$type])) {
			$app = JFactory::getApplication();
			if($type == 'administrator')
				$test[$type] = $app->isAdmin();
			elseif($type == 'cli')
				$test[$type] = false;
			else
				$test[$type] = $app->isSite();
		}
		return $test[$type];
	}
} else {
	function hikashop_isClient($type) {
		static $test = array();
		if(!isset($test[$type])) {
			$app = JFactory::getApplication();
			$test[$type] = $app->isClient($type);
		}
		return $test[$type];
	}
}

if(!class_exists('hikashopBridgeController')) {
	if(!HIKASHOP_J30){
		class hikashopBridgeController extends JController {
			public function getNewView($name = '', $type = '', $prefix = '', $config = array()){
				return $this->getView($name, $type, $prefix, $config);
			}
		}
	} else {
		class hikashopBridgeController extends JControllerLegacy {
			public function getNewView($name = '', $type = '', $prefix = '', $config = array()){
				if (empty($name))
					$name = $this->getName();

				if (empty($prefix))
					$prefix = $this->getName() . 'View';
				if(method_exists($this, 'createView'))
					return $this->createView($name, $prefix, $type, $config);
				return $this->getView($name, $prefix, $type, $config);
			}
			public function __toString() {
				return get_class($this);
			}
		}
	}
}

class hikashopController extends hikashopBridgeController {
	var $pkey = array();
	var $table = array();
	var $groupMap = '';
	var $groupVal = null;
	var $orderingMap ='';

	var $display = array('listing','show','cancel','');
	var $local_display = array();
	var $modify_views = array('edit','selectlisting','childlisting','newchild');
	var $add = array('add');
	var $modify = array('apply','save','save2new','store','orderdown','orderup','saveorder','savechild','addchild','toggle');
	var $delete = array('delete','remove');
	var $publish_return_view = 'listing';
	var $pluginCtrl = null;

	function __construct($config = array(), $skip = false) {
		if(!empty($this->pluginCtrl) && is_array($this->pluginCtrl)) {
			$config['base_path'] = JPATH_PLUGINS.DS.$this->pluginCtrl[0].DS.$this->pluginCtrl[1].DS;
		}
		if(!$skip) {
			parent::__construct($config);
			$this->registerDefaultTask('listing');
		}
		if(!empty($this->local_display))
			$this->display = array_merge($this->display, $this->local_display);
		if(!empty($this->type)) {
			$massactionClass = hikashop_get('class.massaction');
			$massactionClass->addActionTasks($this, $this->type);
		}
	}
	function listing(){
		hikaInput::get()->set('layout', 'listing');
		return $this->display();
	}
	function show(){
		hikaInput::get()->set('layout', 'show');
		return $this->display();
	}
	function edit(){
		hikaInput::get()->set('hidemainmenu',1);
		hikaInput::get()->set('layout', 'form');
		return $this->display();
	}
	function add(){
		hikaInput::get()->set('hidemainmenu',1);
		hikaInput::get()->set('layout', 'form');
		return $this->display();
	}
	function apply(){
		$status = $this->store();
		return $this->edit();
	}
	function save(){
		$this->store();
		return $this->listing();
	}
	function save2new(){
		$this->store(true);
		return $this->edit();
	}
	function orderdown(){
		if(!empty($this->table)&&!empty($this->pkey)&&(empty($this->groupMap)||isset($this->groupVal))&&!empty($this->orderingMap)){
			$orderHelper = hikashop_get('helper.order');
			$orderHelper->pkey = $this->pkey;
			$orderHelper->table = $this->table;
			$orderHelper->groupMap = $this->groupMap;
			$orderHelper->groupVal = $this->groupVal;
			$orderHelper->orderingMap = $this->orderingMap;
			if(!empty($this->main_pkey)){
				$orderHelper->main_pkey = $this->main_pkey;
			}
			$orderHelper->order(true);
		}
		return $this->listing();
	}
	function orderup(){
		if(!empty($this->table)&&!empty($this->pkey)&&(empty($this->groupMap)||isset($this->groupVal))&&!empty($this->orderingMap)){
			$orderHelper = hikashop_get('helper.order');
			$orderHelper->pkey = $this->pkey;
			$orderHelper->table = $this->table;
			$orderHelper->groupMap = $this->groupMap;
			$orderHelper->groupVal = $this->groupVal;
			$orderHelper->orderingMap = $this->orderingMap;
			if(!empty($this->main_pkey)){
				$orderHelper->main_pkey = $this->main_pkey;
			}
			$orderHelper->order(false);
		}
		return $this->listing();
	}
	function saveorder(){
		if(!empty($this->table)&&!empty($this->pkey)&&(empty($this->groupMap)||isset($this->groupVal))&&!empty($this->orderingMap)){
			$orderHelper = hikashop_get('helper.order');
			$orderHelper->pkey = $this->pkey;
			$orderHelper->table = $this->table;
			$orderHelper->groupMap = $this->groupMap;
			$orderHelper->groupVal = $this->groupVal;
			$orderHelper->orderingMap = $this->orderingMap;
			if(!empty($this->main_pkey)){
				$orderHelper->main_pkey = $this->main_pkey;
			}
			$orderHelper->save();
		}
		return $this->listing();
	}

	function store($new = false) {
		$app = JFactory::getApplication();
		$class = hikashop_get('class.'.$this->type);
		$status = $class->saveForm();
		if($status) {
			if(!HIKASHOP_J30)
				$app->enqueueMessage(JText::_('HIKASHOP_SUCC_SAVED'), 'success');
			else
				$app->enqueueMessage(JText::_('HIKASHOP_SUCC_SAVED'));
			if(!$new) hikaInput::get()->set('cid', $status);
			else hikaInput::get()->set('cid', 0);
			hikaInput::get()->set('fail', null);
		} else {
			$app->enqueueMessage(JText::_( 'ERROR_SAVING' ), 'error');
			if(!empty($class->errors)){
				foreach($class->errors as $oneError){
					$app->enqueueMessage($oneError, 'error');
				}
			}
		}
		return $status;
	}

	function remove() {
		$cids = hikaInput::get()->get('cid', array(), 'array');
		$class = hikashop_get('class.'.$this->type);
		$num = $class->delete($cids);
		if($num) {
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::sprintf('SUCC_DELETE_ELEMENTS',count($cids)), 'message');
		}
		return $this->listing();
	}

	function publish() {
		$cid = hikaInput::get()->post->get('cid', array(), 'array');
		hikashop_toInteger($cid);
		return $this->_toggle($cid,1);
	}

	function unpublish() {
		$cid = hikaInput::get()->post->get('cid', array(), 'array');
		hikashop_toInteger($cid);
		return $this->_toggle($cid,0);
	}

	function _toggle($cid, $publish) {
		if(empty( $cid )) {
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('No items selected'), 'warning');
		}
		if(in_array($this->type,array('product','category'))){
			JPluginHelper::importPlugin( 'hikashop' );
			$app = JFactory::getApplication();
			$unset = array();
			$objs = array();
			$class = hikashop_get('class.'.$this->type);
			foreach($cid as $k => $id){
				$element = new stdClass();
				$name = reset($this->toggle);
				$element->$name = $id;
				$publish_name = key($this->toggle);
				$element->$publish_name = (int)$publish;
				$element->old = $class->get($id);
				$do = true;
				$app->triggerEvent( 'onBefore'.ucfirst($this->type).'Update', array( & $element, & $do) );
				if(!$do){
					$unset[]=$k;
				}else{
					$objs[$k]=& $element;
				}
				unset($element);
			}
			if(!empty($unset)){
				foreach($unset as $u){
					unset($cid[$u]);
				}
			}
		}
		$cids = implode( ',', $cid );
		$db = JFactory::getDBO();
		$query = 'UPDATE '.hikashop_table($this->type) . ' SET '.key($this->toggle).' = ' . (int)$publish . ' WHERE '.reset($this->toggle).' IN ( '.$cids.' )';
		$db->setQuery( $query );
		if (!$db->execute()) {
			$app = JFactory::getApplication();
			$app->enqueueMessage($db->getErrorMsg(), 'warning');
		}elseif(in_array($this->type,array('product','category'))){
			if(!empty($objs)){
				foreach($objs as $element){
					$app->triggerEvent( 'onAfter'.ucfirst($this->type).'Update', array( & $element ) );
				}
			}
		}
		$task = $this->publish_return_view;
		return $this->$task();
	}

	function getModel($name = '', $prefix = '', $config = array(),$do=false) {
		if($do) return parent::getModel($name, $prefix , $config);
		return false;
	}

	function authorise($task){
		return $this->authorize($task);
	}

	function authorize($task){
		if(!$this->isIn($task,array('modify_views','add','modify','delete','display'))){
			return false;
		}
		if($this->isIn($task,array('modify','delete')) && (!JSession::checkToken('request'))) {
			return false;
		}
		$app = JFactory::getApplication();
		if(hikashop_isClient('administrator')) {
			if(method_exists($this,'getACLName')) {
				$name = $this->getACLName($task);
			} else {
				$name = $this->getName();
			}
			if(!empty($name) && hikashop_level(2)) {
				$config =& hikashop_config();
				if($this->isIn($task,array('display'))){
					$task = 'view';
				}elseif($this->isIn($task,array('modify_views','add','modify'))){
					$task = 'manage';
				}elseif($this->isIn($task,array('delete'))){
					$task = 'delete';
				}else{
					return true;
				}

				if(!empty($name))
					$name = 'acl_'.$name.'_'.$task;
				if(!hikashop_isAllowed($config->get($name,'all'))){
					hikashop_display(JText::_('RESSOURCE_NOT_ALLOWED'),'error');
					return false;
				}
			}
		}
		return true;
	}

	function isIn($task,$lists){
		foreach($lists as $list){
			if(in_array($task,$this->$list)){
				return true;
			}
		}
		return false;
	}

	function execute($task){
		$task = (string)$task;
		if(substr($task,0,12)=='triggerplug-'){
			JPluginHelper::importPlugin( 'hikashop' );
			JPluginHelper::importPlugin( 'hikashopshipping' );
			JPluginHelper::importPlugin( 'hikashoppayment' );
			$app = JFactory::getApplication();
			$parts = explode('-',$task,2);
			$event = 'onTriggerPlug'.ucfirst(array_pop($parts));
			$app->triggerEvent( $event, array( ) );
			return true;
		} elseif(substr($task,0,7)=='action_') {
			$action_id = substr($task,7);
			if(is_numeric($action_id) && $this->authorize($task)) {
				$massactionClass = hikashop_get('class.massaction');
				hikaInput::get()->set('ctrl','massaction');
				$result = $massactionClass->runActions($action_id, $this->type);
				if(is_bool($result)) {
					hikaInput::get()->set('ctrl',$this->type);
					$task = 'listing';
				} else {
					$js = null;
					$params = new HikaParameter();
					$params->set('output',$result);
					$params->set('type',$this->type);
					echo hikashop_getLayout('massaction', 'output', $params, $js);
					return;
				}
			}
		}
		if(HIKASHOP_J30) {
			if(empty($task))
				$task = @$this->taskMap['__default'];
			if(!empty($task) && !$this->authorize($task)){
				$app = JFactory::getApplication();
				$app->enqueueMessage(JText::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'), 'error');
				return;
			}
		}
		return parent::execute($task);
	}

	function display($cachable = false, $urlparams = false) {
		if(HIKASHOP_J30) {
			$document = JFactory::getDocument();
			$view = $this->getView('', $document->getType(), '', array('base_path' => $this->basePath));

			if($view->getLayout() == 'default' && hikaInput::get()->getString('layout', '') != '')
				$view->setLayout(hikaInput::get()->getString('layout'));
		}

		$app = JFactory::getApplication();
		$tmpl = hikaInput::get()->getString('tmpl');
		if(hikashop_isClient('administrator') && $tmpl !== 'component' && $tmpl !== 'ajax' && $tmpl !== 'raw') {
			$config =& hikashop_config();
			$menu_style = $config->get('menu_style','title_bottom');
			$menu_style = 'content_top';
			if($menu_style == 'content_top') {
				echo hikashop_getMenu('',$menu_style);
			}
		}
		return parent::display($cachable, $urlparams);
	}

	function getUploadSetting($upload_key, $caller = '') {
		return false;
	}

	function manageUpload($upload_key, &$ret, $uploadConfig, $caller = '') { }
}

class hikashopClass extends JObject {
	var $tables = array();
	var $pkeys = array();
	var $namekeys = array();

	protected $db = null;

	public function __construct($config = array()) {
		$this->database = JFactory::getDBO();
		if(!isset($this->db))
			$this->db = $this->database;
		return parent::__construct($config);
	}

	public function save(&$element){
		$pkey = end($this->pkeys);
		if(empty($pkey)) {
			$pkey = end($this->namekeys);
		} elseif(empty($element->$pkey)) {
			$tmp = end($this->namekeys);
			if(!empty($tmp) && !empty($element->$tmp)) {
				$pkey = $tmp;
			} elseif(!empty($tmp)) {
				$element->$tmp = $this->getNamekey($element);
				if($element->$tmp === false) {
					return false;
				}
			}
		}

		if(!empty($this->fields_whitelist) && is_array($this->fields_whitelist) && !empty($this->fields_whitelist)) {
			foreach(get_object_vars($element) as $key => $var) {
				if(!in_array($key, $this->fields_whitelist)) {
					unset($element->$key);
				}
			}
		}

		$obj =& $element;
		if(empty($element->$pkey)) {
			$query = $this->_getInsert($this->getTable(),$obj);
			$this->database->setQuery($query);
			$status = $this->database->execute();
		} else {
			if(count((array) $element) > 1) {
				$status = $this->database->updateObject($this->getTable(), $obj, $pkey);
			} else {
				$status = true;
			}
		}
		if($status)
			return empty($element->$pkey) ? $this->database->insertid() : $element->$pkey;
		return false;
	}

	public function getTable() {
		return hikashop_table(end($this->tables));
	}

	public function _getInsert($table, &$object, $keyName = null) {
		$fmtsql = 'INSERT IGNORE INTO '.$this->database->quoteName($table).' ( %s ) VALUES ( %s ) ';
		$fields = array();
		foreach (get_object_vars( $object ) as $k => $v) {
			if (is_array($v) or is_object($v) || $v === NULL || $k[0] == '_') {
				continue;
			}
			$fields[] = $this->database->quoteName( $k );
			$values[] = $this->database->Quote( $v );
		}
		return sprintf( $fmtsql, implode(',', $fields), implode(',', $values) );
	}

	public function delete(&$elementsToDelete) {
		if(!is_array($elementsToDelete)) {
			$elements = array($elementsToDelete);
		} else {
			$elements = $elementsToDelete;
		}

		$isNumeric = is_numeric(reset($elements));
		$strings = array();
		foreach($elements as $key => $val) {
			$strings[$key] = $this->database->Quote($val);
		}

		$columns = $isNumeric ? $this->pkeys : $this->namekeys;

		if(empty($columns) || empty($elements))
			return false;

		$otherElements = array();
		$otherColumn = '';
		foreach($columns as $i => $column) {
			if(!empty($column))
				continue;

			$query = 'SELECT '.($isNumeric?end($this->pkeys):end($this->namekeys)).' FROM '.$this->getTable().' WHERE '.($isNumeric?end($this->pkeys):end($this->namekeys)).' IN ( '.implode(',',$strings).');';
			$this->database->setQuery($query);
			$otherElements = $this->database->loadColumn();
			foreach($otherElements as $key => $val) {
				$otherElements[$key] = $this->database->Quote($val);
			}
			break;
		}

		$result = true;
		$tables = array();
		if(empty($this->tables)) {
			$tables[0] = $this->getTable();
		} else {
			foreach($this->tables as $i => $oneTable) {
				$tables[$i] = hikashop_table($oneTable);
			}
		}
		foreach($tables as $i => $oneTable) {
			$column = $columns[$i];
			if(empty($column)) {
				$whereIn = ' WHERE '.($isNumeric?$this->namekeys[$i]:$this->pkeys[$i]).' IN ('.implode(',',$otherElements).')';
			} else {
				$whereIn = ' WHERE '.$column.' IN ('.implode(',',$strings).')';
			}
			$query = 'DELETE FROM '.$oneTable.$whereIn;
			$this->database->setQuery($query);
			$result = $this->database->execute() && $result;
		}
		return $result;
	}

	public function get($element, $default = null) {
		if(empty($element))
			return null;
		$pkey = end($this->pkeys);
		$namekey = end($this->namekeys);
		if(is_array($element)) {
			$el = reset($element);
			if(!is_numeric($el) && !empty($namekey)) {
				$pkey = $namekey;
			}
			$elements = array();
			foreach($element as $el) {
				$elements[] = $this->database->Quote($el);
			}
			$query = 'SELECT * FROM '.$this->getTable().' WHERE '.$pkey.' IN ('.implode(',', $elements).')';
			$this->database->setQuery($query);
			return $this->database->loadObjectList($pkey);
		}

		if(!is_numeric($element) && !empty($namekey)) {
			$pkey = $namekey;
		}
		$query = 'SELECT * FROM '.$this->getTable().' WHERE '.$pkey.' = '.$this->database->Quote($element);
		$this->database->setQuery($query, 0, 1);
		if(!hikashop_isClient('administrator') && $default == '#notrans#') {
			if(class_exists('JFalangDatabase')) {
				return $this->database->loadObject('stdClass', false);
			} elseif((class_exists('JFDatabase') || class_exists('JDatabaseMySQLx'))) {
				return $this->database->loadObject( false);
			}
		}
		return $this->database->loadObject();
	}

	public function getRaw($element, $default = null) {
		static $multiTranslation = null;
		if(empty($element))
			return null;
		$pkey = end($this->pkeys);
		$namekey = end($this->namekeys);
		$table = $this->getTable(); // hikashop_table(end($this->tables));
		if(!is_numeric($element) && !empty($namekey)) {
			$pkey = $namekey;
		}
		if($multiTranslation === null) {
			$translationHelper = hikashop_get('helper.translation');
			$multiTranslation = $translationHelper->isMulti(true) && $translationHelper->falang;
		}
		$query = 'SELECT * FROM '.$table.' WHERE '.$pkey.' = '.$this->database->Quote($element);
		$this->database->setQuery($query, 0, 1);
		if($multiTranslation) {
			$app = JFactory::getApplication();
			if(!hikashop_isClient('administrator') && class_exists('JFalangDatabase')) {
				$ret = $this->database->loadObject('stdClass', false);
			} elseif(!hikashop_isClient('administrator') && (class_exists('JFDatabase') || class_exists('JDatabaseMySQLx'))) {
				$ret = $this->database->loadObject(false);
			} else {
				$ret = $this->database->loadObject();
			}
		} else {
			$ret = $this->database->loadObject();
		}
		return $ret;
	}
}

if(!class_exists('hikashopBridgeView')) {
	if(!HIKASHOP_J30){
		class hikashopBridgeView extends JView {}
	} else {
		class hikashopBridgeView extends JViewLegacy {}
	}
}

class hikashopView extends hikashopBridgeView {
	var $triggerView = false;
	var $displayView = true;
	var $toolbar = array();
	var $direction = 'ltr';
	var $chosen = true;
	var $extrafilters = array();
	var $title = '';
	var $allowInlineJavascript = false;
	var $extraData = null;
	var $toolbarHelper = null;
	var $paramBase = '';
	var $pageInfo = null;

	function display($tpl = null) {
		$lang = JFactory::getLanguage();
		if($lang->isRTL()) $this->direction = 'rtl';

		if($this->triggerView) {
			if(!is_array($this->triggerView))
				$this->triggerView = array('hikashop');
			foreach($this->triggerView as $group){
				JPluginHelper::importPlugin($group);
			}
			$app = JFactory::getApplication();
			$obj =& $this;
			$app->triggerEvent('onHikashopBeforeDisplayView', array(&$obj));
		}


		$this->toolbarHelper = hikashop_get('helper.toolbar');

		if(!empty($this->toolbar)) {
			$app = JFactory::getApplication();
			if(hikashop_isClient('administrator'))
				$this->toolbarHelper->process($this->toolbar, $this->title);
		}

		if(HIKASHOP_J30 && $this->chosen) {
			$jversion = preg_replace('#[^0-9\.]#i','',JVERSION);
			$include_mootools = version_compare($jversion,'3.3.0','<');

			$app = JFactory::getApplication();
			if(hikashop_isClient('administrator')) {
				if($_REQUEST['option'] == HIKASHOP_COMPONENT && !HIKASHOP_J40) {
					if($include_mootools)
						JHTML::_('behavior.framework');
					if(@$_REQUEST['ctrl'] != 'massaction')
						JHtml::_('formbehavior.chosen', 'select');
				}
			} else {
				$configClass =& hikashop_config();
				if($configClass->get('bootstrap_forcechosen', 0)) {
					if($include_mootools)
						JHTML::_('behavior.framework');
					try {
						JHtml::_('formbehavior.chosen', 'select');
					} catch(Exception $e) {
						$doc = JFactory::getDocument();
						$doc->addStyleSheet(JURI::base(true).'/media/vendor/chosen/chosen.css');
						$doc->addScript(JURI::base(true).'/media/vendor/chosen/chosen.jquery.js');
					}

				}
			}
		}

		if($this->displayView) {
			ob_start();
			parent::display($tpl);
			$html = ob_get_clean();


			if(HIKASHOP_J40) {
				if(!$this->allowInlineJavascript) {
					$doc = JFactory::getDocument();
					$toRemove = array();

					if(hikashop_isClient('administrator')) {
						$doc->addScriptDeclaration('
window.saveorder = function ( n, task ) {
	var checkbox = document.querySelector(\'form[name="adminForm"] [type="checkbox"]\');
	checkbox.click();
	hikashop.checkAll(checkbox);
	var form = document.getElementById(\'adminForm\');
	Joomla.submitform(task, form);
};
						');
					}
					if(count($toRemove)) {
						foreach($toRemove as $r) {
							$html = str_replace($r, '', $html);
						}
					}
				} else {
					$html = str_replace(array('window.hikashop.ready(', '(function($){'), array('if(window.hikashop) window.hikashop.ready(', 'if (window.jQuery) (function($){'), $html);
				}
			} else {
				$html = str_replace('class="custom-select', 'class="inputbox', $html);
			}

			echo $html;
		}

		if($this->triggerView) {
			$obj =& $this;
			$app->triggerEvent('onHikashopAfterDisplayView', array( &$obj));
		}
	}

	function loadTemplate($tpl = null){
		$config = hikashop_config();
		$active = $config->get('display_view_files', 0);
		if(!$active)
			return parent::loadTemplate($tpl);

		$app = JFactory::getApplication();
		if(hikashop_isClient('administrator') && $active != 2)
			return parent::loadTemplate($tpl);

		return '<div class="hikashop_view_files_border"><div class="hikashop_view_files_title"><span>'.
			$this->getName().' / '.$this->getLayout().(!empty($tpl)?'_':'').$tpl.'.php</span></div><div class="hikashop_view_files_wrapper">'.
			parent::loadTemplate($tpl).
			'</div></div>';
	}

	function &getPageInfo($default = '', $dir = 'asc', $filters = array()) {
		$app = JFactory::getApplication();

		$pageInfo = new stdClass();
		$pageInfo->search = $app->getUserStateFromRequest($this->paramBase.'.search', 'search', '', 'string');

		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$filter_fullorder = hikaInput::get()->getString('filter_fullorder', null);
		if($filter_fullorder != null && strpos($filter_fullorder, ' ') !== false) {
			$filter_fullorder = explode(' ', $filter_fullorder, 2);
			$filter_fullorder[1] = strtolower($filter_fullorder[1]);
			if($filter_fullorder[1] == 'asc' || $filter_fullorder[1] == 'desc') {
				$app->setUserState($this->paramBase.'.filter_order', $filter_fullorder[0]);
				$app->setUserState($this->paramBase.'.filter_order_Dir', $filter_fullorder[1]);
			}
		}
		$pageInfo->filter->order->value = $app->getUserStateFromRequest($this->paramBase.'.filter_order', 'filter_order', $default, 'cmd');
		$pageInfo->filter->order->dir = $app->getUserStateFromRequest($this->paramBase.'.filter_order_Dir', 'filter_order_Dir',	$dir, 'word');

		$pageInfo->limit = new stdClass();
		$pageInfo->limit->value = $app->getUserStateFromRequest($this->paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int');
		if(empty($pageInfo->limit->value))
			$pageInfo->limit->value = 500;
		if(hikaInput::get()->getVar('search') != $app->getUserState($this->paramBase.'.search')) {
			$app->setUserState($this->paramBase.'.limitstart',0);
			$pageInfo->limit->start = 0;
		} else {
			$pageInfo->limit->start = $app->getUserStateFromRequest($this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
		}

		$this->searchOptions = $filters;
		$this->openfeatures_class = "hidden-features";

		if(!empty($filters)) {
			$reset = false;
			foreach($filters as $k => $v) {
				$type = 'string';
				if(is_int($v)) $type = 'int';

				if(!$reset) $oldValue = $app->getUserState($this->paramBase.'.filter_'.$k, $v);
				$newValue = $app->getUserStateFromRequest($this->paramBase.'.filter_'.$k, 'filter_'.$k, $v, $type);
				$reset = $reset || ($oldValue != $newValue);
				$pageInfo->filter->$k = $newValue;
			}
			if($reset) {
				$app->setUserState($this->paramBase.'.limitstart',0);
				$pageInfo->limit->start = 0;
			}
		}

		$pageInfo->search = HikaStringHelper::strtolower($app->getUserStateFromRequest($this->paramBase.'.search', 'search', '', 'string'));
		$pageInfo->search = trim($pageInfo->search);

		$pageInfo->elements = new stdClass();

		$this->assignRef('pageInfo', $pageInfo);
		return $pageInfo;
	}

	function getPageInfoTotal($query, $countValue = '*') {
		if(empty($this->pageInfo))
			return false;

		$db = JFactory::getDBO();
		$app = JFactory::getApplication();

		$db->setQuery('SELECT COUNT('.$countValue.') '.$query);
		if(empty($this->pageInfo->elements))
			$this->pageInfo->elements = new stdClass();
		$this->pageInfo->elements->total = (int)$db->loadResult();
		if((int)$this->pageInfo->limit->start >= $this->pageInfo->elements->total) {
			$this->pageInfo->limit->start = 0;
			$app->setUserState($this->paramBase.'.limitstart', 0);
		}
	}

	function processFilters(&$filters, &$order, $searchMap = array(), $orderingAccept = array()) {
		if(!empty($this->pageInfo->search)) {
			$db = JFactory::getDBO();
			if(!HIKASHOP_J30) {
				$searchVal = '\'%' . $db->getEscaped(HikaStringHelper::strtolower($this->pageInfo->search), true) . '%\'';
			} else {
				$searchVal = '\'%' . $db->escape(HikaStringHelper::strtolower($this->pageInfo->search), true) . '%\'';
			}
			$filters[] = '('.implode(' LIKE '.$searchVal.' OR ',$searchMap).' LIKE '.$searchVal.')';
		}
		if(!empty($filters)) {
			$filters = ' WHERE '. implode(' AND ', $filters);
		} else {
			$filters = '';
		}

		if(!empty($this->pageInfo->filter->order->value)) {
			$t = '';
			if(strpos($this->pageInfo->filter->order->value, '.') !== false)
				list($t,$v) = explode('.', $this->pageInfo->filter->order->value, 2);

			if(empty($orderingAccept) || in_array($t.'.', $orderingAccept) || in_array($this->pageInfo->filter->order->value, $orderingAccept))
				$order = ' ORDER BY '.$this->pageInfo->filter->order->value.' '.$this->pageInfo->filter->order->dir;
		}
	}

	function getPagination($max = 500, $limit = 100) {
		if(empty($this->pageInfo))
			return false;

		if($this->pageInfo->limit->value == $max)
			$this->pageInfo->limit->value = $limit;

		if(HIKASHOP_J30) {
			$paginationHelper = hikashop_get('helper.pagination', $this->pageInfo->elements->total, $this->pageInfo->limit->start, $this->pageInfo->limit->value);
		} else {
			jimport('joomla.html.pagination');
			$paginationHelper = new JPagination($this->pageInfo->elements->total, $this->pageInfo->limit->start, $this->pageInfo->limit->value);
		}

		$this->assignRef('pagination', $paginationHelper);
		return $paginationHelper;
	}

	function getOrdering($value = '', $doOrdering = true) {
		$this->assignRef('doOrdering', $doOrdering);

		$ordering = new stdClass();
		$ordering->ordering = false;

		if($doOrdering) {
			$ordering->ordering = false;
			$ordering->orderUp = 'orderup';
			$ordering->orderDown = 'orderdown';
			$ordering->reverse = false;
			if(!empty($this->pageInfo) && $this->pageInfo->filter->order->value == $value) {
				$ordering->ordering = true;
				if($this->pageInfo->filter->order->dir == 'desc') {
					$ordering->orderUp = 'orderdown';
					$ordering->orderDown = 'orderup';
					$ordering->reverse = true;
				}
			}
		}
		$this->assignRef('ordering', $ordering);

		return $ordering;
	}

	protected function loadRef($refs) {
		foreach($refs as $key => $name) {
			$obj = hikashop_get($name);
			if(!empty($obj))
				$this->$key = $obj;
			unset($obj);
		}
	}

	function loadHkLayout($layout, $params = array()) {
		$backup_paths = $this->_path['template'];

		$app = JFactory::getApplication();
		$component = JApplicationHelper::getComponentName();
		$component = preg_replace('/[^A-Z0-9_\.-]/i', '', $component);
		$layout_path = ( hikashop_isClient('administrator') ? HIKASHOP_BACK : HIKASHOP_FRONT ) . 'views/layouts/tmpl';
		$fallback = JPATH_THEMES . '/' . $app->getTemplate() . '/html/' . $component . '/layouts';

		$this->_path['template'] = array();
		$this->_addPath('template', array($layout_path, $fallback));

		$backup_params = @$this->params;
		$this->params = new hikaParameter();
		foreach($params as $k => $v) {
			$this->params->set($k, $v);
		}

		$current_name = $this->getName();
		$this->_name = 'layouts';

		$current_layout = $this->getLayout();
		$this->setLayout($layout);

		$ret = $this->loadTemplate();

		$this->setLayout($current_layout);

		$this->_name = $current_name;

		$this->_path['template'] = $backup_paths;

		$this->params = $backup_params;

		return $ret;
	}

	public function assignRef($name, &$ref) {
		$this->$name =& $ref;
	}
}

class hikashopPlugin extends JPlugin {
	var $db;
	var $type = 'plugin';
	var $multiple = false;
	var $plugin_params = null;
	var $toolbar = array();
	var $name = '';

	function __construct(&$subject, $config) {
		$this->db = JFactory::getDBO();
		parent::__construct($subject, $config);
	}

	function pluginParams($id = 0) {
		if(!empty($this->name) && in_array($this->type, array('payment', 'shipping', 'plugin'))) {
			static $pluginsCache = array();
			$key = $this->type.'_'.$this->name.'_'.$id;
			if(!isset($pluginsCache[$key])){
				$query = 'SELECT * FROM '.hikashop_table($this->type).' WHERE '.$this->type.'_type = '.$this->db->Quote($this->name);
				if($id > 0) {
					$query .= ' AND '.$this->type.'_id = ' . (int)$id;
				}
				$this->db->setQuery($query);
				$pluginsCache[$key] = $this->db->loadObject();
			}
			if(!empty($pluginsCache[$key])) {
				$params = $this->type.'_params';
				$this->plugin_params = hikashop_unserialize($pluginsCache[$key]->$params);
				$this->plugin_data = $pluginsCache[$key];
				return true;
			}
		}
		$this->plugin_params = null;
		$this->plugin_data = null;
		return false;
	}

	function isMultiple() {
		return $this->multiple;
	}

	function configurationHead() {
		return array();
	}

	function configurationLine($id = 0) {
		return null;
	}

	function listPlugins($name, &$values, $full = true, $aclFilter = false) {
		if(!in_array($this->type, array('payment', 'shipping', 'plugin')))
			return;

		if(!$this->multiple) {
			$values['plg.'.$name] = $name;
			return;
		}

		$where = array(
			$this->type.'_type = ' . $this->db->Quote($name),
			$this->type.'_published = 1'
		);

		if(!empty($aclFilter)) {
			$app = JFactory::getApplication();
			if(is_int($aclFilter) && $aclFilter > 0)
				hikashop_addACLFilters($where, $this->type.'_access', '', 2, false, (int)$aclFilter);
			else if(!hikashop_isClient('administrator'))
				hikashop_addACLFilters($where, $this->type.'_access');
		}
		$where = '('.implode(') AND (', $where).')';

		$key = $this->type.$where;
		static $pluginsCache = array();
		if(!isset($pluginsCache[$key])){
			$query = 'SELECT '.$this->type.'_id as id, '.$this->type.'_name as name FROM '.hikashop_table($this->type).' WHERE '.$where.' ORDER BY '.$this->type.'_ordering';
			$this->db->setQuery($query);
			$pluginsCache[$key] = $this->db->loadObjectList();
		}
		if($full) {
			foreach($pluginsCache[$key] as $plugin) {
				$values['plg.'.$name.'-'.$plugin->id] = $name.' - '.$plugin->name;
			}
		} else {
			foreach($pluginsCache[$key] as $plugin) {
				$values[] = $plugin->id;
			}
		}
	}

	function showPage($name = 'thanks') {
		if(!HIKASHOP_J30)
			JHTML::_('behavior.mootools');
		elseif(!HIKASHOP_J40)
			JHTML::_('behavior.framework');

		$folder = 'hikashop';
		if(!empty($this->type) && $this->type != 'plugin')
			$folder .= $this->type;

		$app = JFactory::getApplication();
		$path = JPATH_THEMES.DS.$app->getTemplate().DS.$folder.DS.$this->name.'_'.$name.'.php';
		if(!file_exists($path)) {
			$path = JPATH_PLUGINS .DS.$folder.DS.$this->name.DS.$this->name.'_'.$name.'.php';
		}
		if(!file_exists($path)) {
		}

		if(!file_exists($path))
			return false;
		require($path);
		return true;
	}

	function pluginConfiguration(&$elements) {
		$app = JFactory::getApplication();

		$this->plugins =& $elements;
		$this->pluginName = hikaInput::get()->getCmd('name', $this->type);
		$this->pluginView = '';

		$plugin_id = hikaInput::get()->getInt('plugin_id',0);
		if($plugin_id == 0) {
			$plugin_id = hikaInput::get()->getInt($this->type.'_id', 0);
		}

		if(hikashop_isClient('administrator')) {
			$this->toolbar = array(
				'save',
				'apply',
				'cancel' => array('name' => 'link', 'icon' => 'cancel', 'alt' => JText::_('HIKA_CANCEL'), 'url' => hikashop_completeLink('plugins')),
			);
			if(!empty($this->doc_form)) {
				$this->toolbar[] = '|';
				$this->toolbar[] = array('name' => 'pophelp', 'target' => $this->type.'-'.$this->doc_form.'-form');
			}
		}


		if(empty($this->title)) {
			$this->title = JText::_('HIKASHOP_PLUGIN_METHOD');
		}
		if(hikashop_isClient('administrator')) {
			if($plugin_id == 0) {
				hikashop_setTitle($this->title, 'plugin', 'plugins&plugin_type='.$this->type.'&task=edit&name='.$this->pluginName.'&subtask=edit');
			} else {
				hikashop_setTitle($this->title, 'plugin', 'plugins&plugin_type='.$this->type.'&task=edit&name='.$this->pluginName.'&subtask='.$this->type.'_edit&'.$this->type.'_id='.$plugin_id);
			}
		}
	}

	function pluginMultipleConfiguration(&$elements) {
		if(!$this->multiple)
			return;

		$app = JFactory::getApplication();
		$this->plugins =& $elements;
		$this->pluginName = hikaInput::get()->getCmd('name', $this->type);
		$this->pluginView = 'sublisting';
		$this->subtask = hikaInput::get()->getCmd('subtask','');
		$this->task = hikaInput::get()->getVar('task');

		if(empty($this->title)) { $this->title = JText::_('HIKASHOP_PLUGIN_METHOD'); }

		if($this->subtask == 'copy') {
			if(!in_array($this->task, array('orderup', 'orderdown', 'saveorder'))) {
				$pluginIds = hikaInput::get()->get('cid', array(), 'array');
				hikashop_toInteger($pluginIds);
				$result = true;
				if(!empty($pluginIds) && in_array($this->type, array('payment','shipping'))) {
					$this->db->setQuery('SELECT * FROM '.hikashop_table($this->type).' WHERE '.$this->type.'_id IN ('.implode(',',$pluginIds).')');
					$plugins = $this->db->loadObjectList();
					$helper = hikashop_get('class.'.$this->type);
					$plugin_id = $this->type . '_id';
					foreach($plugins as $plugin) {
						unset($plugin->$plugin_id);
						if(!$helper->save($plugin)) {
							$result = false;
						}
					}
				}
				if($result) {
					$app->enqueueMessage(JText::_('HIKASHOP_SUCC_SAVED'), 'message');
					$app->redirect(hikashop_completeLink('plugins&plugin_type='.$this->type.'&task=edit&name='.$this->pluginName, false, true));
				}
			}
		}

		if(hikashop_isClient('administrator')) {
			$this->toolbar = array(
				array('name' => 'link', 'icon'=>'new','alt' => JText::_('HIKA_NEW'), 'url' => hikashop_completeLink('plugins&plugin_type='.$this->type.'&task=edit&name='.$this->pluginName.'&subtask=edit')),
				'cancel',
				'|',
				array('name' => 'pophelp', 'target' => 'plugins-'.$this->doc_listing.'sublisting')
			);
			hikashop_setTitle($this->title, 'plugin', 'plugins&plugin_type='.$this->type.'&task=edit&name='.$this->pluginName);
		}

		$this->toggleClass = hikashop_get('helper.toggle');
		jimport('joomla.html.pagination');
		$this->pagination = new JPagination(count($this->plugins), 0, false);
		$this->order = new stdClass();
		$this->order->ordering = true;
		$this->order->orderUp = 'orderup';
		$this->order->orderDown = 'orderdown';
		$this->order->reverse = false;
		$app->setUserState(HIKASHOP_COMPONENT.'.plugin_type.'.$this->type, $this->pluginName);
	}

	public function getProperties($public = true) {
		$vars = get_object_vars($this);
		if (!$public)
			return $vars;
		foreach ($vars as $key => $value) {
			if ('_' == substr($key, 0, 1))
				unset($vars[$key]);
		}
		return $vars;
	}
}

spl_autoload_register(function($classname) {
	switch($classname) {
		case 'hikashopPaymentPlugin':
			include_once __DIR__ . '/paymentplugin.php';
			break;
		case 'hikashopShippingPlugin':
			include_once __DIR__ . '/shippingplugin.php';
			break;
		case 'JToolbarButtonPophelp':
			include_once HIKASHOP_BACK . '/buttons/pophelp.php';
			break;
		case 'JToolbarButtonHikaPopup':
			include_once HIKASHOP_BACK . '/buttons/hikapopup.php';
			break;
		case 'JToolbarButtonExport':
			include_once HIKASHOP_BACK . '/buttons/export.php';
			break;
	}
});

if(HIKASHOP_J30) {
	class hikaInput {
		protected static $ref = null;

		public static function &get() {
			if(!empty($ref))
				return $ref;
			$ref =& JFactory::getApplication()->input;
			return $ref;
		}
	}
} else {
	class hikaInput {
		protected static $ref = null;
		protected $mode = null;

		public function __construct($mode = null) {
			$this->mode = $mode;
		}

		public static function &get() {
			if(!empty($ref))
				return $ref;
			$ref = new hikaInput();
			if(func_num_args()) {
				$ret = call_user_func_array(array($ref, 'getVar'), func_get_args());
				return $ret;
			}
			return $ref;
		}
		public function __call($method, $args) {
			if(in_array($method, array('set', 'get')))
				$method .= 'Var';
			if($method == 'getVar' && count($args) == 3)
				array_splice($args, 2, 0, "default");
			if($this->mode == null)
				return call_user_func_array(array('JRequest', $method), $args);
			if(!isset($args[1]))
				$args[1] = '';
			$args[2] = $this->mode;
			$ret = call_user_func_array(array('JRequest', $method), $args);

			if($this->mode != 'files' || !is_array($ret) || !count($ret))
				return $ret;

			$new_ret = array();
			foreach($ret as $k => $v) {
				if(is_array($v)){
					foreach($v as $k2 => $v2) {
						$new_ret[$k2][$k] = $v2;
					}
				}else{
					$new_ret[$k] = $v;
				}
			}
			return $new_ret;

		}
		public function __get($name) {
			if(in_array($name, array('get','post','files','server','env','cookie','request')))
				return new hikaInput($name);
		}
		public function getRaw($value, $default) {
			return JRequest::getVar($value, $default, ($this->mode == null) ? '' : $this->mode, 'string', JREQUEST_ALLOWRAW);
		}
		public function getUsername($value, $default) {
			return JRequest::getVar($value, $default, ($this->mode == null) ? '' : $this->mode, 'string');
		}
		public function getArray($value, $default) {
			return JRequest::getVar($value, $default, ($this->mode == null) ? '' : $this->mode, 'array');
		}
	}
}

class hikaRegistry {
	protected static $data = array();
	public static function get($name) { return isset(self::$data[$name]) ? self::$data[$name] : null; }
	public static function set($name, $value) { self::$data[$name] = $value; }
}

JHTML::_('select.booleanlist','hikashop');
class hikaParameter extends JRegistry {
	function get($path, $default = null) {
		$value = parent::get($path, 'noval');
		if($value==='noval') $value = parent::get('data.'.$path,$default);
		return $value;
	}
}

class hikaLanguage extends JLanguage {
	public function __construct($old) {
		if(is_object($old)) {
			parent::__construct($old->lang);
		} else
			parent::__construct($old);
	}
	public function publicLoadLanguage($filename, $extension = 'unknown') {
		return hikashop_loadTranslationFile($filename);
	}
}

if(HIKASHOP_J40) {
	class HikaStringHelper extends Joomla\String\StringHelper {}
} else {
	class HikaStringHelper extends JString {}
}

define('HIKASHOP_COMPONENT', 'com_hikashop');
define('HIKASHOP_LIVE', rtrim(JURI::root(),'/').'/');
define('HIKASHOP_ROOT', rtrim(JPATH_ROOT,DS).DS);
define('HIKASHOP_FRONT', rtrim(JPATH_SITE,DS).DS.'components'.DS.HIKASHOP_COMPONENT.DS);
define('HIKASHOP_BACK', rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.HIKASHOP_COMPONENT.DS);
define('HIKASHOP_HELPER', HIKASHOP_BACK.'helpers'.DS);
define('HIKASHOP_BUTTON', HIKASHOP_BACK.'buttons');
define('HIKASHOP_CLASS', HIKASHOP_BACK.'classes'.DS);
define('HIKASHOP_INC', HIKASHOP_BACK.'inc'.DS);
define('HIKASHOP_VIEW', HIKASHOP_BACK.'views'.DS);
define('HIKASHOP_TYPE', HIKASHOP_BACK.'types'.DS);
define('HIKASHOP_MEDIA', HIKASHOP_ROOT.'media'.DS.HIKASHOP_COMPONENT.DS);
define('HIKASHOP_DBPREFIX', '#__hikashop_');

$lang = JFactory::getLanguage();
$db = JFactory::getDBO();
$configClass = hikashop_config();

if(HIKASHOP_J40) {
	$db->setQuery("SET sql_mode=(SELECT REPLACE(REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''), 'STRICT_TRANS_TABLES', ''));");
	$db->execute();
}

$override_path = hikashop_getLanguagePath(JPATH_ROOT).DS.'overrides'.DS.$lang->getTag().'.override.ini';
if(file_exists($override_path) && $lang->getTag() != 'en-GB' && $configClass->get('multi_language_edit') && $configClass->get('language_files_forced_reload', 1)) {
	$resetOverride = function() {
		$this->override = array();
		$this->strings = array();
		if(!empty($this->paths)) {
			foreach($this->paths as $extension => $files) {
				if(!empty($files)) {
					foreach($files as $file => $result) {
						if(!$result)
							continue;
						if(strpos($file, '/overrides/en-GB'))
							continue;

						$strings = $this->parse($file);
						$this->strings = array_replace($this->strings, $strings);
					}
				}
			}
		}
	};
	$lang = JFactory::getLanguage();
	$resetOverrideCB = $resetOverride->bindTo($lang, 'JLanguage');
	$resetOverrideCB();
}
$lang->load(HIKASHOP_COMPONENT,JPATH_SITE);
if(file_exists($override_path)) {
	hikashop_loadTranslationFile($override_path);
}

if(defined('HIKASHOP_INSTALL_PRECHECK')) {
	$databaseHelper = hikashop_get('helper.database');
	$databaseHelper->checkdb();
}

$responsive = $configClass->get('bootstrap_design', HIKASHOP_J30);
if($responsive) {
	define('HIKASHOP_RESPONSIVE', true);
	define('HK_GRID_BTN', 'hikabtn');
} else {
	define('HIKASHOP_RESPONSIVE', false);
	define('HK_GRID_BTN', '');
}
define('HK_GRID_ROW', 'hk-row');
define('HK_GRID_THUMBNAILS', 'hk-thumbnails');
define('HK_GRID_COL_12', 'hkc-md-12');
define('HK_GRID_COL_10', 'hkc-md-10');
define('HK_GRID_COL_8', 'hkc-md-8');
define('HK_GRID_COL_6', 'hkc-md-6');
define('HK_GRID_COL_4', 'hkc-md-4');
define('HK_GRID_COL_3', 'hkc-md-3');
define('HK_GRID_COL_2', 'hkc-md-2');
define('HK_GRID_COL_1', 'hkc-md-1');

if($configClass->get('bootstrap_back_design', HIKASHOP_J30)) {
	define('HIKASHOP_BACK_RESPONSIVE', true);
} else {
	define('HIKASHOP_BACK_RESPONSIVE', false);
}

$elements = array(
	'form_select_class' => array(
		'form-select',
		'hkform-control',
	),
	'form_control_class' => array(
		'form-control',
		'hkform-control'
	),
	'group_class' => array(
		'input-group',
		'input-append'
	),
	'css_button' => array(
		'btn',
		'hikabtn'
	),
	'css_button_primary' => array(
		'btn-primary',
		'hikabtn-primary'
	),
	'css_button_success' => array(
		'btn-success',
		'hikabtn-success'
	),
	'css_button_danger' => array(
		'btn-danger',
		'hikabtn-danger'
	),
);
$app->triggerEvent('onHikashopDefineConstants', array(&$elements) );
foreach($elements as $k => $classes) {
	$override_class = $configClass->get($k, '');
	$key = 'HK_'.strtoupper($k);
	if(!defined($key)) {
		if(empty($override_class) || $override_class == $classes[1]) {
			if(HIKASHOP_J40) {
				define($key, $classes[0]);
			} else {
				define($key, $classes[1]);
			}
		} else {
			define($key, $override_class);
		}
	}
}

$admin = hikashop_isClient('administrator');
if(HIKASHOP_J30 && (($admin && HIKASHOP_BACK_RESPONSIVE) || (!$admin && HIKASHOP_RESPONSIVE && (int)$configClass->get('bootstrap_radios', 1) == 1))) {
	include_once(dirname(__FILE__).DS.'joomla30.php');
} else {
	include_once(dirname(__FILE__).DS.'joomla25.php');
}

if(!function_exists('bccomp'))
	include_once HIKASHOP_INC.'compat.php';

define('HIKASHOP_RESSOURCE_VERSION', str_replace('.', '', $configClass->get('version')));

define('HIKASHOP_NAME','HikaShop');
define('HIKASHOP_TEMPLATE',HIKASHOP_FRONT.'templates'.DS);
define('HIKASHOP_URL','https://www.hikashop.com/');
define('HIKASHOP_UPDATEURL',HIKASHOP_URL.'index.php?option=com_updateme&ctrl=update&task=');
define('HIKASHOP_HELPURL',HIKASHOP_URL.'index.php?option=com_updateme&ctrl=doc&component='.HIKASHOP_NAME.'&page=');
define('HIKASHOP_REDIRECT',HIKASHOP_URL.'index.php?option=com_updateme&ctrl=redirect&page=');
if(is_callable("date_default_timezone_set"))
	date_default_timezone_set(@date_default_timezone_get());

if($admin) {
	define('HIKASHOP_CONTROLLER', HIKASHOP_BACK.'controllers'.DS);
	define('HIKASHOP_IMAGES', '../media/'.HIKASHOP_COMPONENT.'/images/');
	define('HIKASHOP_CSS', '../media/'.HIKASHOP_COMPONENT.'/css/');
	define('HIKASHOP_JS', '../media/'.HIKASHOP_COMPONENT.'/js/');
	$css_type = 'backend';
} else {
	define('HIKASHOP_CONTROLLER',HIKASHOP_FRONT.'controllers'.DS);
	define('HIKASHOP_IMAGES',JURI::base(true).'/media/'.HIKASHOP_COMPONENT.'/images/');
	define('HIKASHOP_CSS',JURI::base(true).'/media/'.HIKASHOP_COMPONENT.'/css/');
	define('HIKASHOP_JS',JURI::base(true).'/media/'.HIKASHOP_COMPONENT.'/js/');
	$css_type = 'frontend';
}
$js = 0;
$css = 0;

if(!hikashop_isClient('cli')) {
	$js = $configClass->get('load_js', 1) || $admin;
	$css = $configClass->get('load_css',1) || $admin;
}
if($js) {
	$doc = JFactory::getDocument();
	$doc->addScript(HIKASHOP_JS.'hikashop.js?v='.HIKASHOP_RESSOURCE_VERSION);
}
if($css) {
	$doc = JFactory::getDocument();
	$doc->addStyleSheet(HIKASHOP_CSS.'hikashop.css?v='.HIKASHOP_RESSOURCE_VERSION);

	$css_file = $configClass->get('css_'.$css_type,'default');
	if(!empty($css_file)) {
		$doc->addStyleSheet(HIKASHOP_CSS.$css_type.'_'.$css_file.'.css?t='.@filemtime(HIKASHOP_MEDIA.'css'.DS.$css_type.'_'.$css_file.'.css'));
	}

	if(!$admin) {
		$style = $configClass->get('css_style', '');
		if(!empty($style)) {
			$doc->addStyleSheet(HIKASHOP_CSS.'style_'.$style.'.css?t='.@filemtime(HIKASHOP_MEDIA.'css'.DS.'style_'.$style.'.css'));
		}
	}

	if($lang->isRTL()) {
		$doc->addStyleSheet(HIKASHOP_CSS.'rtl.css?v='.HIKASHOP_RESSOURCE_VERSION);
	}

	$navigator_check = hikashop_getNavigator();
	if ($navigator_check["name"] == "Apple Safari") {
		$doc->addStyleSheet(HIKASHOP_CSS.'safari_hikashop.css');
	}
}

hikashop_loadJslib('font-awesome');

function hikashop_getNavigator($agent = null) {
	$u_agent = ($agent!=null)? $agent : @$_SERVER['HTTP_USER_AGENT'];
	$bname = 'Unknown';
	$platform = 'Unknown';
	$version= "";

	if (preg_match('/linux/i', $u_agent)) {
		$platform = 'linux';
	}
	elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
		$platform = 'mac';
	}
	elseif (preg_match('/windows|win32/i', $u_agent)) {
		$platform = 'windows';
	}

	if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent))
	{
		$bname = 'Internet Explorer';
		$ub = "MSIE";
	}
	elseif(preg_match('/Firefox/i',$u_agent))
	{
		$bname = 'Mozilla Firefox';
		$ub = "Firefox";
	}
	elseif(preg_match('/Chrome/i',$u_agent))
	{
		$bname = 'Google Chrome';
		$ub = "Chrome";
	}
	elseif(preg_match('/Safari/i',$u_agent))
	{
		$bname = 'Apple Safari';
		$ub = "Safari";
	}
	elseif(preg_match('/Opera/i',$u_agent))
	{
		$bname = 'Opera';
		$ub = "Opera";
	}
	elseif(preg_match('/Netscape/i',$u_agent))
	{
		$bname = 'Netscape';
		$ub = "Netscape";
	}

	$known = array('Version', 'other');
	if(!empty($ub))
		$known[] = $ub;
	$pattern = '#(?<browser>' . join('|', $known) .
	')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
	if (!preg_match_all($pattern, $u_agent, $matches)) {
	}

	$i = count($matches['browser']);
	if ($i != 1) {
		if (!empty($ub) && strripos($u_agent,"Version") < strripos($u_agent, $ub)){
			$version = @$matches['version'][0];
		}
		else {
			$version = @$matches['version'][1];
		}
	}
	else {
		$version = @$matches['version'][0];
	}

	if ($version==null || $version=="") {$version="?";}

	$result = array(
		'userAgent' => $u_agent,
		'name'      => $bname,
		'version'   => $version,
		'platform'  => $platform,
		'pattern'    => $pattern
	);

	return $result;
}

$app->triggerEvent('onAfterHikashopLoad', array() );
