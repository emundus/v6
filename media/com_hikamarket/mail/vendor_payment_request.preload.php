<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php

$vars = array(
	'LIVE_SITE' => HIKASHOP_LIVE,
	'VENDOR_URL'=> HIKASHOP_LIVE.'administrator/index.php?option=com_hikamarket&ctrl=vendor&task=edit&cid='.$data->vendor->vendor_id,
	'vendor' => $data->vendor,
	'user' => $data->user
);
$texts = array(
	'MAIL_TITLE' => JText::_('VENDOR_PAYMENT_REQUEST_EMAIL_SUBJECT'),
	'MAIL_HEADER' => JText::_('HIKAMARKET_MAIL_HEADER'),
);

	$currencyClass = hikamarket::get('shop.class.currency');
	$vendorClass = hikamarket::get('class.vendor');

	$stats = $vendorClass->getUnpaidOrders($data->vendor);

	$total = new stdClass();
	$total->count = 0;
	$total->value = 0;
	$total->currency = (int)$data->vendor->vendor_currency_id;
	if(empty($total->currency))
		$total->currency = hikashop_getCurrency();

	$templates['REQUEST_LINE'] = array();

	foreach($stats as $d) {
		$total->count += (int)$d->count;

		if($data->vendor->vendor_currency_id == $d->currency)
			$total->value += hikamarket::toFloat( (int)$d->value );
		else
			$total->value += $currencyClass->convertUniquePrice((float)hikamarket::toFloat($d->value), (int)$d->currency, (int)$data->vendor->vendor_currency_id);

		$templates['REQUEST_LINE'][] = array(
			'NAME' => hikamarket::orderStatus($d->status),
			'COUNT' => (int)$d->count,
			'TOTAL' => $currencyClass->format($d->value, $d->currency)
		);
	}

$templates['REQUEST_FOOTER'] = array(
	array(
		'COUNT' => $total->count,
		'TOTAL' => $currencyClass->format($total->value, $total->currency)
	)
);
