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
class hikashopVatHelper{
	function isValid(&$vat){
		$zone_code = '';
		if(!empty($vat->address_country)){
			$zoneClass = hikashop_get('class.zone');
			$zone = $zoneClass->get(@$vat->address_country);
			$zone_code = $zone->zone_code_2;

			if(empty($zone_code) || !in_array($zone_code,array('AT','BE','BG','CY','CZ','DK','EE','EL','DE','PT','GR','ES','FI','HR','HU','LU','MT','SI',
		'FR','IE','IT','LV','LT','NL','PL','SK','RO','SE'))){
				return true;
			}
		}


		if($zone_code=='ES' && !empty($vat->address_state)){
			$statezone = $zoneClass->get(@$vat->address_state);
			if($statezone->zone_code_3=='GC' || $statezone->zone_code_3=='TF'){
				return true;
			}
		}


		static $vat_check = null;
		if(!isset($vat_check)){
			$config = hikashop_config();
			$vat_check = (int)$config->get('vat_check',2);
		}
		if($zone_code == 'GR') $zone_code = 'EL';

		switch($vat_check){
			case 1:
			case 2:

				if(is_object($vat)){
					$vat_number =& $vat->address_vat;
				}else{
					$vat_number =& $vat;
				}
				$regex = $this->getRegex($vat_number);

				if($regex===false){
					if(is_object($vat) && !empty($vat->address_country)){
						if(!empty($zone_code)){
							$vat_number = $zone_code.$vat_number;
							$regex = $this->getRegex($vat_number);
						}
					}
					if($regex===false){
						$app = JFactory::getApplication();
						$this->message = JText::_('VAT_NOT_FOR_YOUR_COUNTRY');
						if(@$_REQUEST['tmpl']=='component'){
							hikashop_display($this->message,'error');
						}else{
							$app->enqueueMessage($this->message);
						}
						return false;
					}
				}

				if(!$this->regexCheck($vat_number,$regex)){
					return false;
				}
				$vat_number = strtoupper(str_replace(array(' ','.','-'),array('','',''),$vat_number));
				$code = substr($vat_number,0, 2);
				if($code == 'GR'){
					$code = 'EL';
					$vat_number = $code.substr($vat_number,2);
				}
				if($zone_code != $code){
					$app = JFactory::getApplication();
					$this->message = JText::sprintf('WRONG_VAT_NUMBER_COUNTRY_CODE',$code,$zone_code);
					if(@$_REQUEST['tmpl']=='component'){
						hikashop_display($this->message,'error');
					}else{
						$app->enqueueMessage($this->message);
					}
					return false;
				}

				if($vat_check==2){
					return $this->onlineCheck($vat_number);
				}
			case 0:
			default:
		}
		return true;
	}

	function regexCheck(  $vat , $regex) {
		if(!preg_match($regex, str_replace(array(' ','.','-'),array('','',''),$vat))){
			$app = JFactory::getApplication();
			$this->message = JText::_('VAT_NUMBER_NOT_VALID');
			if(@$_REQUEST['tmpl']=='component'){
				hikashop_display($this->message,'error');
			}else{
				$app->enqueueMessage($this->message);
			}

			return false;
		}
		return true;
	}

	function getRegex($vat){
		$regex = false;
		switch(strtoupper(substr(str_replace(array(' ','.','-'),array('','',''),$vat),0, 2))) {
			case 'AT':
				$regex = '/^(AT){0,1}U[0-9]{8}$/i';
				break;
			case 'BE':
				$regex = '/^(BE){0,1}[0]{0,1}[0-9]{9}[0-9]{0,2}$/i';
				break;
			case 'BG':
				$regex = '/^(BG){0,1}[0-9]{9,10}$/i';
				break;
			case 'CY':
				$regex = '/^(CY){0,1}[0-9]{8}[A-Z]$/i';
				break;
			case 'CZ':
				$regex = '/^(CZ){0,1}[0-9]{8,10}$/i';
				break;
			case 'DK':
				$regex = '/^(DK){0,1}([0-9]{2}[\ ]{0,1}){3}[0-9]{2}$/i';
				break;
			case 'EE':
			case 'DE':
			case 'PT':
			case 'EL':
			case 'GR':
				$regex = '/^(EE|EL|DE|GR|PT){0,1}[0-9]{9}$/i';
				break;
			case 'ES':
				$regex = '/^(ES){0,1}([0-9A-Z][0-9]{7}[A-Z])|([A-Z][0-9]{7}[0-9A-Z])$/i';
				break;
			case 'FI':
			case 'HU':
			case 'LU':
			case 'MT':
			case 'SI':
				$regex = '/^(FI|HU|LU|MT|SI){0,1}[0-9]{8}$/i';
				break;
			case 'FR':
				$regex = '/^(FR){0,1}[0-9A-Z]{2}[\ ]{0,1}[0-9]{9}$/i';
				break;
			case 'GB':
				$regex = '/^(GB){0,1}([1-9][0-9]{2}[\ ]{0,1}[0-9]{4}[\ ]{0,1}[0-9]{2})|([1-9][0-9]{2}[\ ]{0,1}[0-9]{4}[\ ]{0,1}[0-9]{2}[\ ]{0,1}[0-9]{3})|((GD|HA)[0-9]{3})$/i';
				break;
			case 'HR':
				$regex = '/^(HR){0,1}[0-9]{11}$/i';
				break;
			case 'IE':
				$regex = '/^(IE){0,1}[0-9][0-9A-Z\+\*][0-9]{5}[A-Z][A-Z]?$/i';
				break;
			case 'IT':
			case 'LV':
				$regex = '/^(IT|LV){0,1}[0-9]{11}$/i';
				break;
			case 'LT':
				$regex = '/^(LT){0,1}([0-9]{9}|[0-9]{12})$/i';
				break;
			case 'NL':
				$regex = '/^(NL){0,1}[0-9]{9}B[0-9]{2}$/i';
				break;
			case 'PL':
			case 'SK':
				$regex = '/^(PL|SK){0,1}[0-9]{10}$/i';
				break;
			case 'RO':
				$regex = '/^(RO){0,1}[1-9][0-9]{1,9}$/i';
				break;
			case 'SE':
				$regex = '/^(SE){0,1}[0-9]{12}$/i';
				break;
			default:
				break;
		}
		return $regex;
	}

	function onlineCheck($vat){
		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$processed = false;
		$obj =& $this;
		$rc = $app->triggerEvent( 'onBeforeVATOnlineCheck', array( &$obj, &$processed, &$vat) );
		if( $processed) {
			return $rc;
		}

		if (extension_loaded('soap')) {
			try{
				$client = new SoapClient("http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl");
				$countryCode = substr($vat, 0, 2);
				$result = $client->checkVat(array('countryCode' => $countryCode, 'vatNumber' => substr($vat, 2)));
				if(empty($result) || !$result->valid) {
					$this->message = JText::_('VAT_NUMBER_NOT_VALID');
				}
			}catch(SoapFault $e) {
				$this->message = $e->__toString();
				return true;
			}catch (Exception $e) {
				$this->message = $e->__toString();
				return true;
			}
			if ($result === false || empty($result) || !$result->valid ) {
				$app = JFactory::getApplication();
				if(@$_REQUEST['tmpl']=='component'){
					hikashop_display($this->message,'error');
				}else{
					$app->enqueueMessage($this->message);
				}
				return false;
			}
		}else{
			$app = JFactory::getApplication();
			$this->message = JText::_('SOAP_EXTENSION_NOT_FOUND');
			if(@$_REQUEST['tmpl']=='component'){
				hikashop_display($this->message,'error');
			}else{
				$app->enqueueMessage($this->message);
			}
			return false;
		}
		return true;
	}
}
