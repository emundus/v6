<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class plgHikashopRates extends JPlugin
{
	var $message = '';
	var $urlECB = "https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml";

	function onHikashopCronTrigger(&$messages){
		$pluginsClass = hikashop_get('class.plugins');
		$plugin = $pluginsClass->getByName('hikashop','rates');
		if(empty($plugin->params))
			$plugin->params = array();
		if(empty($plugin->params['frequency'])){
			$plugin->params['frequency'] = 86400;
		}
		if(!empty($plugin->params['last_cron_update']) && $plugin->params['last_cron_update']+$plugin->params['frequency']>time()){
			return true;
		}
		$plugin->params['last_cron_update']=time();
		$pluginsClass->save($plugin);
		$this->updateRates();
		if(!empty($this->message)){
			$messages[] = $this->message;
		}
		return true;
	}

	function updateRates($plugin=null){
		if(empty($plugin)){
			$pluginsClass = hikashop_get('class.plugins');
			$plugin = $pluginsClass->getByName('hikashop','rates');
		}
		$app = JFactory::getApplication();

		if(empty($plugin->params) || !is_array($plugin->params))
			$plugin->params = array();

		if(empty($plugin->params['source'])){
			$plugin->params['source'] = 'ecb';
		}
		switch($plugin->params['source']) {
			case 'yahoo':
				$this->message = 'Yahoo Finance has been discontinued. Please reconfigure the HikaShop Currency Rates update plugin via the Joomla plugins manager.';
				$app->enqueueMessage($this->message, 'error' );
				break;
			case 'openexchangerates':
				if(empty($plugin->params['app_id'])) {
					$this->message = 'If you want to use the Open Exchange Rates service to update your currency rates, you first need to sign up on https://openexchangerates.org/ and then enter your App id in the settings of the HikaShop currency Rates update plugin via the Joomla plugins manager.';
					$app->enqueueMessage($this->message, 'error' );
				}else{
					$this->updateRatesOpenexchangerates($plugin->params['app_id']);
				}
				break;
			case 'cbr':
				$this->updateRatesCBR();
				break;
			case 'ecb':
			default:
				$this->updateRatesECB();
				break;
		}
	}

	function updateRatesCBR() {

		if(!function_exists('curl_init')){
			$app = JFactory::getApplication();
			$this->message = JText::_( 'The Central Bank of Russia feed requires cURL.' );
			$app->enqueueMessage($this->message, 'error' );
			return false;
		}

		$ch = curl_init();
		$url = 'https://www.cbr.ru/scripts/XML_daily.asp';
		curl_setopt ($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_CAINFO, __DIR__ . "/cacert.pem");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

		ob_start();

		curl_exec ($ch);
		$msg = curl_error($ch);
		curl_close ($ch);
		$string = ob_get_clean();

		if(empty($string)){
			$app = JFactory::getApplication();
			$this->message = JText::_( 'No valid data in the currencies rate feed of the CBR feed: '.$msg );
			$app->enqueueMessage($this->message, 'error' );
			return false;
		}

		if(!preg_match_all('/<Valute (.*)<\/Valute>/mUs',iconv('Windows-1251','UTF-8//IGNORE',$string), $matches)){
			$app = JFactory::getApplication();
			$this->message = JText::_( 'No rates found in the currencies rate feed of the CBR feed: '.$msg );
			$app->enqueueMessage($this->message, 'error' );
			return false;
		}
		$data = array();
		foreach($matches[1] as $match) {
			preg_match('/<CharCode>(.*?)<\/CharCode>/m',$match, $currency_code);
			preg_match('/<Value>(.*?)<\/Value>/m',$match, $rate);
			preg_match('/<Nominal>(.*?)<\/Nominal>/m',$match, $nominal);
			$data[$currency_code[1]] = hikashop_toFloat($rate[1])/(int)$nominal[1];
		}

		$config = hikashop_config();
		$main_currency = (int)$config->get('main_currency',1);
		$currencyClass = hikashop_get('class.currency');
		$mainCurrencyData = $currencyClass->get($main_currency);
		if($mainCurrencyData->currency_code!='RUB'){
			if(in_array($mainCurrencyData->currency_code,array_keys($data))){
				$rubRate = $data[$mainCurrencyData->currency_code];
				$newCurrencies = array();
				foreach($data as $code => $rate){
					if($code!=$mainCurrencyData->currency_code) $newCurrencies[$code]=$rubRate/$rate;
				}
				$newCurrencies[$mainCurrencyData->currency_code]=1;
				$newCurrencies['RUB']=$rubRate;
				$data=$newCurrencies;
			}else{
				$app = JFactory::getApplication();
				$this->message = 'Main currency '.$mainCurrencyData->currency_code.' not supported by CBR feed:'.implode(',',array_keys($data));
				$app->enqueueMessage($this->message, 'error' );
				return false;
			}
		}else{
			$data['RUB'] = 1;
			foreach($data as $code => $rate){
				$data[$code] = 1 / $rate;
			}
		}

		$db = JFactory::getDBO();
		foreach($data as $code => $rate){
			$currency = null;
			$query='UPDATE '.hikashop_table('currency').' SET currency_modified='.time().',currency_rate='.$db->Quote($rate).' WHERE currency_code='.$db->Quote($code);
			$db->setQuery($query);
			$db->execute();
		}
		$app = JFactory::getApplication();
		$this->message = JText::_( 'RATES_SUCCESSFULLY_UPDATED' );
		$app->enqueueMessage($this->message );
		return true;
	}

	function updateRatesOpenexchangerates($app_id) {

		if(!function_exists('curl_init')){
			$app = JFactory::getApplication();
			$this->message = JText::_( 'The Open Exchange Rates feed requires cURL.' );
			$app->enqueueMessage($this->message, 'error' );
			return false;
		}

		$ch = curl_init();
		$url = 'https://openexchangerates.org/api/latest.json?app_id='.$app_id;
		curl_setopt ($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_CAINFO, __DIR__ . "/cacert.pem");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

		ob_start();

		curl_exec ($ch);
		$msg = curl_error($ch);
		curl_close ($ch);
		$json = ob_get_clean();


		if(empty($json)){
			$app = JFactory::getApplication();
			$this->message = JText::_( 'No valid data in the currencies rate feed of the Open Exchange Rates feed: '.$msg );
			$app->enqueueMessage($this->message, 'error' );
			return false;
		}

		$data = json_decode($json, true);

		if(empty($data['rates'])){
			$app = JFactory::getApplication();
			$this->message = JText::_( 'JSON could not be decoded for the currencies rate feed of the Open Exchange Rates feed.' );
			$app->enqueueMessage($this->message, 'error' );
			return false;
		}
		$config = hikashop_config();
		$main_currency = (int)$config->get('main_currency',1);
		$currencyClass = hikashop_get('class.currency');
		$mainCurrencyData = $currencyClass->get($main_currency);
		if($mainCurrencyData->currency_code!='USD'){
			if(in_array($mainCurrencyData->currency_code,array_keys($data['rates']))){
				$euroRate = 1.0/$data['rates'][$mainCurrencyData->currency_code];
				$newCurrencies = array();
				foreach($data['rates'] as $code => $rate){
					if($code!=$mainCurrencyData->currency_code) $newCurrencies[$code]=$euroRate*$rate;
				}
				$newCurrencies[$mainCurrencyData->currency_code]=1;
				$newCurrencies['USD']=$euroRate;
				$data['rates']=$newCurrencies;
			}else{
				$app = JFactory::getApplication();
				$this->message = 'Main currency not supported by Open Exchange Rates feed.';
				$app->enqueueMessage($this->message, 'error' );
				return false;
			}
		}else{
			$data['rates']['USD']=1;
		}

		$db = JFactory::getDBO();
		foreach($data['rates'] as $code => $rate){
			$currency = null;
			$query='UPDATE '.hikashop_table('currency').' SET currency_modified='.time().',currency_rate='.$rate.' WHERE currency_code='.$db->Quote($code);
			$db->setQuery($query);
			$db->execute();
		}
		$app = JFactory::getApplication();
		$this->message = JText::_( 'RATES_SUCCESSFULLY_UPDATED' );
		$app->enqueueMessage($this->message );
		return true;
	}

	function updateRatesYahoo(){
		$app = JFactory::getApplication();
		$type = hikashop_get('type.currency');
		$type->displayType = 'all';
		$type->load('');
		if(empty($type->currencies)){
			$this->message = 'Currencies not enabled/displayed. See menu System>Currencies';
			$app->enqueueMessage($this->message, 'error' );
			return false;
		}
		$config =& hikashop_config();
		$main = (int)$config->get('main_currency');
		if(empty($type->currencies[$main])){
			$class = hikashop_get('class.currency');
			$type->currencies[$main] = $class->get($main);
		}

		if(empty($type->currencies[$main])){
			$this->message = 'Main currency missing';
			$app->enqueueMessage($this->message, 'error' );
			return false;
		}

		$mainCurrency = $type->currencies[$main];
		$list = array();
		foreach($type->currencies as $currency){
			if($mainCurrency->currency_code!=$currency->currency_code) $list[]=$mainCurrency->currency_code.$currency->currency_code;
		}
		$vars = 'q='.urlencode('select * from yahoo.finance.xchange where pair in ("'.implode('","',$list).'")').'&env='.urlencode('store://datatables.org/alltableswithkeys');
		$url = 'https://query.yahooapis.com/v1/public/yql?'.$vars;

		if(function_exists('curl_init')){
			$ch = curl_init();

			curl_setopt ($ch, CURLOPT_URL, $url);
			curl_setopt ($ch, CURLOPT_HEADER, 0);

			ob_start();

			curl_exec ($ch);
			curl_close ($ch);
			$string = ob_get_clean();
		}
		if(empty($string)){
			$this->message = 'Could not retrieve the Yahoo Financial feed';
			$app->enqueueMessage($this->message, 'error' );
			return false;
		}

		$xml = simplexml_load_string($string);

		$db = JFactory::getDBO();
		foreach($xml->results->rate as $rate){
			$attributes = $rate->Attributes();
			$id = (string)$attributes['id'];
			if(strlen($id)!=6){
				continue;
			}
			$code = substr($id,3);
			$currency = null;
			$query='UPDATE '.hikashop_table('currency').' SET currency_modified='.time().',currency_rate='.(string)$rate->Rate.' WHERE currency_code='.$db->Quote($code);
			$db->setQuery($query);
			$db->execute();
		}
		$this->message = JText::_( 'RATES_SUCCESSFULLY_UPDATED' );
		$app->enqueueMessage($this->message );
		return true;
	}

	function updateRatesECB(){
		$XMLContent= file($this->urlECB);
		if(empty($XMLContent)){
			if(function_exists('curl_init')){
				$ch = curl_init();

				curl_setopt ($ch, CURLOPT_URL, $this->urlECB);
				curl_setopt ($ch, CURLOPT_HEADER, 0);

				ob_start();

				curl_exec ($ch);
				curl_close ($ch);
				$string = ob_get_clean();
				if(!empty($string)){
					$XMLContent = explode("\n",$string);
				}
			}
			if(empty($XMLContent)){
				$app = JFactory::getApplication();
				$this->message = JText::_( 'NO_DATA_IN_ECB_FEED' );
				$app->enqueueMessage($this->message, 'error' );
				return false;
			}
		}
		$currencies=array();
			foreach($XMLContent as $line){
				if(preg_match('#currency=[\'"]?([A-Z]+)[\'"]?#',$line,$currency_match)&&preg_match('#rate=[\'"]?([0-9\.]+)[\'"]?#',$line,$rate_match)){
					$currencies[$currency_match[1]]=floatval($rate_match[1]);
				}
		}
		if(empty($currencies)){
			$app = JFactory::getApplication();
			$this->message = JText::_( 'No valid data in the currencies rate feed of the European Central Bank' );
			$app->enqueueMessage($this->message, 'error' );
			return false;
		}
		$config = hikashop_config();
		$main_currency = (int)$config->get('main_currency',1);
		$currencyClass = hikashop_get('class.currency');
		$mainCurrencyData = $currencyClass->get($main_currency);
		if($mainCurrencyData->currency_code!='EUR'){
			if(in_array($mainCurrencyData->currency_code,array_keys($currencies))){
				$euroRate = 1.0/$currencies[$mainCurrencyData->currency_code];
				$newCurrencies = array();
				foreach($currencies as $code => $rate){
					if($code!=$mainCurrencyData->currency_code) $newCurrencies[$code]=$euroRate*$rate;
				}
				$newCurrencies[$mainCurrencyData->currency_code]=1;
				$newCurrencies['EUR']=$euroRate;
				$currencies=$newCurrencies;
			}else{
				$app = JFactory::getApplication();
				$this->message = JText::_( 'MAIN_CURRENCY_NOT_SUPPORTED_ECB' );
				$app->enqueueMessage($this->message, 'error' );
				return false;
			}
		}else{
			$currencies['EUR']=1;
		}

		$db = JFactory::getDBO();
		foreach($currencies as $code => $rate){
			$currency = null;
			$query='UPDATE '.hikashop_table('currency').' SET currency_modified='.time().',currency_rate='.$rate.' WHERE currency_code='.$db->Quote($code);
			$db->setQuery($query);
			$db->execute();
		}
		$app = JFactory::getApplication();
		$this->message = JText::_( 'RATES_SUCCESSFULLY_UPDATED' );
		$app->enqueueMessage($this->message );
		return true;
	}
}
