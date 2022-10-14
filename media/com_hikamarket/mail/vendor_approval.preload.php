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
	'vendor_name' => @$data->vendor_name,
	'URL' => HIKASHOP_LIVE,
	'vendor' => @$data->vendor,
	'user' => @$data->user
);
$texts = array(
	'MAIL_TITLE' => JText::_('HIKAM_EMAIL_VENDOR_REGISTRATION'),
	'HI_VENDOR' => JText::sprintf('HI_VENDOR', @$data->name),
	'MAIL_HEADER' => JText::_('HIKAMARKET_MAIL_HEADER')
);

$msg = JText::_('VENDOR_REGISTRATION_BEGIN_MESSAGE');
if(strpos($msg, '%s') !== false) {
	$texts['VENDOR_REGISTRATION_BEGIN_MESSAGE'] = JText::sprintf('VENDOR_REGISTRATION_BEGIN_MESSAGE', HIKASHOP_LIVE, $data->name, $data->vendor->vendor_id);
}
