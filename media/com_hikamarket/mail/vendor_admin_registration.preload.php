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
	'user' => $data->user,
	'vendor_name' => $data->vendor_name,
);
$texts = array(
	'MAIL_TITLE' => JText::_('VENDOR_REGISTRATION_EMAIL_SUBJECT'),
	'MAIL_HEADER' => JText::_('HIKAMARKET_MAIL_HEADER'),
);
