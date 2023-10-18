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
class plgHikashoppaymentEpay extends hikashopPaymentPlugin
{
	var $accepted_currencies = array(
		'AUD','CAD','EUR','GBP','JPY','USD','NZD','CHF','HKD','SGD',
		'SEK','DKK','PLN','NOK','HUF','CZK','MXN','BRL','MYR','PHP',
		'TWD','THB','ILS','TRY'
	);
	var $debugData = array();

	var $multiple = true;
	var $name = 'epay';
	var $pluginConfig = array(
		'merchantnumber' => array('MERCHANT_NUMBER', 'input'),
		'windowstate' => array('WINDOW_STATE', 'list', array('1' => 'Overlay', '3' => 'Fullscreen')),
		'windowid' => array('WINDOW_ID', 'input'),
		'md5key' => array('MD5KEY', 'input'),
		'group' => array('GROUP', 'input'),
		'authsms' => array('AUTHSMS', 'input'),
		'authmail' => array('AUTHEMAIL', 'input'),
		'instantcapture' => array('INSTANTCAPTURE', 'list', array('0' => 'HIKASHOP_NO', '1' => 'HIKASHOP_YES')),
		'ownreceipt' => array('OWN_RECEIPT', 'list', array('0' => 'HIKASHOP_NO', '1' => 'HIKASHOP_YES')),
		'debug' => array('DEBUG', 'boolean','0'),
		'return_url' => array('RETURN_URL', 'input'),
		'invalid_status' => array('INVALID_STATUS', 'orderstatus'),
		'pending_status' => array('PENDING_STATUS', 'orderstatus'),
		'verified_status' => array('VERIFIED_STATUS', 'orderstatus')
	);

	function getVars($order) {
		$callback_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment=epay&tmpl=component&lang='.$this->locale.$this->url_itemid;
		$accept_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id='.$order->order_id.$this->url_itemid;
		$decline_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=order&task=cancel_order&order_id='.$order->order_id.$this->url_itemid;

		$vars = array(
			"merchantnumber" => $this->payment_params->merchantnumber,
			"orderid" => $order->order_id,
			"amount" => intval($order->order_full_price*100), //minor units
			"currency" => $this->get_iso_code($this->currency->currency_code),
			"windowstate" => $this->payment_params->windowstate,
			"windowid" => $this->payment_params->windowid,
			"accepturl" => $accept_url,
			"cancelurl" => $decline_url,
			"callbackurl" => $callback_url,
			"smsreceipt" => $this->payment_params->authsms,
			"mailreceipt" => $this->payment_params->authmail,
			"instantcapture" => $this->payment_params->instantcapture,
			"group" => $this->payment_params->group,
			"ownreceipt" => $this->payment_params->ownreceipt,
			"instantcallback" => 1,
			"language" => 0,
			"cms" => "hikashop"
		);

		$vars["hash"] = md5(implode("", array_values($vars)) . $this->payment_params->md5key);

		return $vars;
	}

	function onAfterOrderConfirm(&$order,&$methods,$method_id){
		parent::onAfterOrderConfirm($order, $methods, $method_id);
		if(empty($this->payment_params))
			return false;

		$this->vars = $this->getVars($order);

		return $this->showPage('end');
	}

	function onPaymentNotification(&$statuses){
		$order_id = (int)@$_GET['orderid'];

		$dbOrder = $this->getOrder($order_id);
		if(empty($dbOrder)){
			echo "Could not load any order for your notification ".@$_GET['orderid'];
			return false;
		}

		$this->loadPaymentParams($dbOrder);
		if(empty($this->payment_params))
			return false;
		$this->loadOrderData($dbOrder);
		if($this->payment_params->debug){
			echo print_r($dbOrder,true)."\n\n\n";
		}

		$order_status = $dbOrder->order_status;

		$url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=edit&order_id='.$order_id;
		$order_text = "\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE',$dbOrder->order_number,HIKASHOP_LIVE);
		$order_text .= "\r\n".str_replace('<br/>',"\r\n",JText::sprintf('ACCESS_ORDER_WITH_LINK',$url));

		if($this->payment_params->debug){
			echo print_r($dbOrder,true)."\n\n\n";
		}

		if($this->payment_params->debug){
			echo print_r($_GET,true)."\n\n\n";
		}

		if(strlen($this->payment_params->md5key) > 0)
		{
			$var = "";
			$params = $_GET;

			foreach($params as $key => $value)
			{
				if($key != "hash")
				{
					$var .= $value;
				}
				else
					break;
			}

			$genstamp = md5($var . $this->payment_params->md5key);

			if($genstamp != $_GET["hash"])
			{
				$history = new stdClass();
				$email = new stdClass();
				$history->notified = 0;
				$history->reason = JText::_('PAYMENT_MD5_ERROR');
				$history->data = "Payment by ePay - Invalid MD5 - ePay transaction ID: " . $_GET["tid"];
				$email->subject = JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','ePay').'invalid response';
				$email->body = JText::sprintf("Hello,\r\n An ePay notification was refused because the notification from the ePay server was invalid")."\r\n\r\n".$order_text;

				$this->modifyOrder($order_id, $this->payment_params->invalid_status, $history, $email);

				return false;
			}
		}

		$order_status = $this->payment_params->verified_status;
		if($dbOrder->order_status == $order_status) return true;

		$history->reason = JText::_('PAYMENT_ORDER_CONFIRMED');
		$history->notified=1;
		$history->data = "Payment by ePay - ePay transaction ID: ".$_GET["tid"];

		$mail_status = $statuses[$order_status];
		$email->subject = JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER','ePay',$order_status,$dbOrder->order_number); //order_id ?
		$email->body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','ePay',$order_status)).' '.JText::sprintf('ORDER_STATUS_CHANGED',$mail_status)."\r\n\r\n".$order_text; //order->mail_status == order_status ?

		$this->modifyOrder($order_id,$order_status,$history,$email);
		return true;
	}

	function getPaymentDefaultValues(&$element) {
		$element->payment_name = 'ePay';
		$element->payment_description = 'You can pay by credit card or epay using this payment method';
		$element->payment_images = 'MasterCard,VISA,Credit_card,American_Express';
		$element->payment_params->notification = 1;
		$element->payment_params->windowstate = 1;
		$element->payment_params->windowid = 1;
		$element->payment_params->instantcapture = 0;
		$element->payment_params->ownreceipt = 0;
		$element->payment_params->invalid_status = 'cancelled';
		$element->payment_params->pending_status = 'created';
		$element->payment_params->verified_status = 'confirmed';
	}

	function get_iso_code($code) {
		$codes = array(
			'ADP' => '020', 'AED' => '784', 'AFA' => '004', 'ALL' => '008', 'AMD' => '051', 'ANG' => '532', 'AOA' => '973', 'ARS' => '032', 'AUD' => '036', 'AWG' => '533', 'AZM' => '031',
			'BAM' => '977', 'BBD' => '052', 'BDT' => '050', 'BGL' => '100', 'BGN' => '975', 'BHD' => '048', 'BIF' => '108', 'BMD' => '060', 'BND' => '096', 'BOB' => '068', 'BOV' => '984',
			'BRL' => '986', 'BSD' => '044', 'BTN' => '064', 'BWP' => '072', 'BYR' => '974', 'BZD' => '084', 'CAD' => '124', 'CDF' => '976', 'CHF' => '756', 'CLF' => '990', 'CLP' => '152',
			'CNY' => '156', 'COP' => '170', 'CRC' => '188', 'CUP' => '192', 'CVE' => '132', 'CYP' => '196', 'CZK' => '203', 'DJF' => '262', 'DKK' => '208', 'DOP' => '214', 'DZD' => '012',
			'ECS' => '218', 'ECV' => '983', 'EEK' => '233', 'EGP' => '818', 'ERN' => '232', 'ETB' => '230', 'EUR' => '978', 'FJD' => '242', 'FKP' => '238', 'GBP' => '826', 'GEL' => '981',
			'GHC' => '288', 'GIP' => '292', 'GMD' => '270', 'GNF' => '324', 'GTQ' => '320', 'GWP' => '624', 'GYD' => '328', 'HKD' => '344', 'HNL' => '340', 'HRK' => '191', 'HTG' => '332',
			'HUF' => '348', 'IDR' => '360', 'ILS' => '376', 'INR' => '356', 'IQD' => '368', 'IRR' => '364', 'ISK' => '352', 'JMD' => '388', 'JOD' => '400', 'JPY' => '392', 'KES' => '404',
			'KGS' => '417', 'KHR' => '116', 'KMF' => '174', 'KPW' => '408', 'KRW' => '410', 'KWD' => '414', 'KYD' => '136', 'KZT' => '398', 'LAK' => '418', 'LBP' => '422', 'LKR' => '144',
			'LRD' => '430', 'LSL' => '426', 'LTL' => '440', 'LVL' => '428', 'LYD' => '434', 'MAD' => '504', 'MDL' => '498', 'MGF' => '450', 'MKD' => '807', 'MMK' => '104', 'MNT' => '496',
			'MOP' => '446', 'MRO' => '478', 'MTL' => '470', 'MUR' => '480', 'MVR' => '462', 'MWK' => '454', 'MXN' => '484', 'MXV' => '979', 'MYR' => '458', 'MZM' => '508', 'NAD' => '516',
			'NGN' => '566', 'NIO' => '558', 'NOK' => '578', 'NPR' => '524', 'NZD' => '554', 'OMR' => '512', 'PAB' => '590', 'PEN' => '604', 'PGK' => '598', 'PHP' => '608', 'PKR' => '586',
			'PLN' => '985', 'PYG' => '600', 'QAR' => '634', 'ROL' => '642', 'RUB' => '643', 'RUR' => '810', 'RWF' => '646', 'SAR' => '682', 'SBD' => '090', 'SCR' => '690', 'SDD' => '736',
			'SEK' => '752', 'SGD' => '702', 'SHP' => '654', 'SIT' => '705', 'SKK' => '703', 'SLL' => '694', 'SOS' => '706', 'SRG' => '740', 'STD' => '678', 'SVC' => '222', 'SYP' => '760',
			'SZL' => '748', 'THB' => '764', 'TJS' => '972', 'TMM' => '795', 'TND' => '788', 'TOP' => '776', 'TPE' => '626', 'TRL' => '792', 'TRY' => '949', 'TTD' => '780', 'TWD' => '901',
			'TZS' => '834', 'UAH' => '980', 'UGX' => '800', 'USD' => '840', 'UYU' => '858', 'UZS' => '860', 'VEB' => '862', 'VND' => '704', 'VUV' => '548', 'XAF' => '950', 'XCD' => '951',
			'XOF' => '952', 'XPF' => '953', 'YER' => '886', 'YUM' => '891', 'ZAR' => '710', 'ZMK' => '894', 'ZWD' => '716', 'ZMW' => '967'
		);
		$code = strtoupper(trim($code));
		if(isset( $codes[$code] ))
			return $codes[$code];
		return '208';
	}
}
