<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.2.2
 * @author	hikashop.com
 * @copyright	(C) 2010-2019 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
global $Itemid;
$url_itemid = '';
if(!empty($Itemid)) {
	$url_itemid = '&Itemid=' . $Itemid;
}

$texts = array(
	'MAIL_HEADER' => JText::_('HIKASHOP_MAIL_HEADER'),
	'CONTACT_TITLE' => JText::_('CONTACT_EMAIL_TITLE'),
	'CONTACT_BEGIN_MESSAGE' => JText::_('CONTACT_BEGIN_MESSAGE'),
	'USER_MESSAGE' => JText::_('CONTACT_USER_MESSAGE'),
	'USER' => JText::_('HIKA_USER'),
	'PRODUCT' => JText::_('PRODUCT'),
	'HI_USER' => JText::sprintf('HI_CUSTOMER', ''),
	'FOR_PRODUCT' => '',
);

if(!empty($data->product)) {
	$texts['FOR_PRODUCT'] = JText::sprintf('CONTACT_REQUEST_FOR_PRODUCT', $data->product->product_name);

	$admin_product_url = JRoute::_('administrator/index.php?option=com_hikashop&ctrl=product&task=edit&cid[]='.$data->product->product_id, false, true);
	$productClass = hikashop_get('class.product');
	$productClass->addAlias($data->product);
	$front_product_url = hikashop_frontendLink('index.php?option=com_hikashop&ctrl=product&task=show&cid='.$data->product->product_id.'&name='.$data->product->alias.$url_itemid);
}

$vars = array(
	'LIVE_SITE' => HIKASHOP_LIVE,
	'URL' => HIKASHOP_LIVE,
	'USER_DETAILS' => htmlentities($data->element->name.' ( '.$data->element->email . ' )', ENT_COMPAT, 'UTF-8'),
	'PRODUCT_DETAILS' => '',
	'FRONT_PRODUCT_DETAILS' => '',
	'PRODUCT' => !empty($data->product),
	'USER_MESSAGE' => str_replace(array("\r\n","\r","\n"), '<br/>', $data->element->altbody),
);

if(!empty($data->product)) {
	$vars['PRODUCT_DETAILS'] = '<p>'.strip_tags($data->product->product_name.' ('.$data->product->product_code.')').' <a href="'.$admin_product_url.'">'.JText::_('BACKEND_EDITON_PAGE').'</a> <a href="'.$front_product_url.'">'.JText::_('FRONTEND_DETAILS_PAGE').'</a></p>';
}

if(hikashop_level(1)) {
	$null = null;
	$fieldsClass = hikashop_get('class.field');
	$contactFields = $fieldsClass->getFields('frontcomp',$data->product,'contact');
	if(!empty($contactFields)){
		foreach($contactFields as $field){
			$namekey = $field->field_namekey;
			if(!isset($data->element->$namekey)) continue;
			if(empty($data->element->$namekey) && !strlen($data->element->$namekey)) continue;
			$vars['PRODUCT_DETAILS'] .= '<p>'.$fieldsClass->getFieldName($field).': '.$fieldsClass->show($field, $data->element->$namekey, 'admin_email').'</p>';
		}
	}
}
