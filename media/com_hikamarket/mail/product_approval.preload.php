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
$vendor_name = $data->vendor->vendor_name;

$vars = array(
	'LIVE_SITE' => HIKASHOP_LIVE,
	'PRODUCT_URL' => @$product_url,
	'vendor' => $data->vendor,
	'message' => !empty($data->message) ? nl2br($data->message) : '',
);

if(isset($data->product)) {
	$vars['product'] = $data->product;
	$vars['PRODUCT_URL'] = hikashop_frontendLink('index.php?option=com_hikashop&ctrl=product&task=show&cid='.(int)$data->product->product_id);
}
if(isset($data->products)) {
	foreach($data->products as &$p) {
		$p->url = hikashop_frontendLink('index.php?option=com_hikashop&ctrl=product&task=show&cid='.(int)$p->product_id);
	}
	$vars['products'] = true;
	$templates['products'] = $data->products;
}

$texts = array(
	'MAIL_TITLE' => JText::_('HIKAM_EMAIL_PRODUCT_APPROVAL'),
	'MAIL_HEADER' => JText::_('HIKAMARKET_MAIL_HEADER'),
	'HI_VENDOR' => JText::sprintf('HI_VENDOR', $vendor_name),
);
