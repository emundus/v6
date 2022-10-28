<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php

echo JText::sprintf('HI_CUSTOMER', @$data->customer->name)."\n"."\n";

$url = $data->order_number;
$config =& hikashop_config();
if($config->get('simplified_registration', 0) != 2) {
	$url .= "\n".'( '.$data->order_url.' )'."\n";
}

if(!empty($data->usermsg->usermsg)) {
	echo $data->usermsg->usermsg;
} else {
	echo JText::sprintf('ORDER_STATUS_CHANGED_TO', $url, $data->mail_status);
}

echo "\n"."\n" .
	JText::sprintf('THANK_YOU_FOR_YOUR_ORDER', HIKASHOP_LIVE) . "\n"."\n" .
	str_replace('<br/>', "\n", JText::sprintf('BEST_REGARDS_CUSTOMER', $mail->from_name));
