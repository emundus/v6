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
	'HI_USER' => JText::sprintf('HI_CUSTOMER', ''),
	'FOR_PRODUCT' => '',
);

$vars = array(
	'LIVE_SITE' => HIKASHOP_LIVE,
	'URL' => HIKASHOP_LIVE,
	'PRODUCT_DETAILS' => '',
	'FRONT_PRODUCT_DETAILS' => '',
	'PRODUCT' => false,
);

$main_element = null;
if(!empty($data->order)) {
	$texts['FOR_PRODUCT'] = JText::sprintf('CONTACT_REQUEST_FOR_ORDER', $data->order->order_number);

	$admin_url = JRoute::_('administrator/index.php?option=com_hikashop&ctrl=order&task=show&cid[]='.$data->order->order_id, false, true);
	$front_url = hikashop_frontendLink('index.php?option=com_hikashop&ctrl=order&task=show&cid='.$data->order->order_id.'&order_token='.$data->order->order_token.$url_itemid);
	$element_name = $data->order->order_number;
	$texts['PRODUCT'] = JText::_('HIKASHOP_ORDER');
	$vars['PRODUCT'] = true;
	$main_element = $data->order;
} elseif(!empty($data->product)) {
	$texts['FOR_PRODUCT'] = JText::sprintf('CONTACT_REQUEST_FOR_PRODUCT', $data->product->product_name);

	$admin_url = JRoute::_('administrator/index.php?option=com_hikashop&ctrl=product&task=edit&cid[]='.$data->product->product_id, false, true);
	$productClass = hikashop_get('class.product');
	$productClass->addAlias($data->product);
	$front_url = hikashop_frontendLink('index.php?option=com_hikashop&ctrl=product&task=show&cid='.$data->product->product_id.'&name='.$data->product->alias.$url_itemid);
	$element_name = strip_tags($data->product->product_name.' ('.$data->product->product_code.')');
	$texts['PRODUCT'] = JText::_('PRODUCT');
	$vars['PRODUCT'] = true;
	$main_element = $data->product;
}


$name = '';
if(!empty($data->element->email)) {
	$name = $data->element->email;
}
if(!empty($data->element->name)) {
	if(empty($name))
		$name = $data->element->name;
	else
		$name = $data->element->name.' ( '. $name . ' )';
}
if(!empty($name)) {
	$vars['USER_DETAILS'] = htmlentities($name, ENT_COMPAT, 'UTF-8');

}
$vars['USER'] = !empty($name);

if(!empty($data->element->altbody)) {
	$vars['USER_MESSAGE'] = str_replace(array("\r\n","\r","\n"), '<br/>', $data->element->altbody);
}

$vars['MESSAGE'] = !empty($data->element->altbody);

if(!empty($element_name)) {
	$vars['PRODUCT_DETAILS'] = '<p>'.$element_name;
	if(!empty($admin_url))
		$vars['PRODUCT_DETAILS'] .= ' <a href="'.$admin_url.'">'.JText::_('BACKEND_EDITON_PAGE').'</a>';
	if(!empty($front_url))
		$vars['PRODUCT_DETAILS'] .= ' <a href="'.$front_url.'">'.JText::_('FRONTEND_DETAILS_PAGE').'</a>';
	$vars['PRODUCT_DETAILS'] .= '</p>';
}

if(hikashop_level(1)) {
	$fieldsClass = hikashop_get('class.field');
	$contactFields = $fieldsClass->getFields('frontcomp',$main_element,'contact');
	if(!empty($contactFields)){
		foreach($contactFields as $field){
			$namekey = $field->field_namekey;
			if(!isset($data->element->$namekey)) continue;
			if(empty($data->element->$namekey) && !strlen($data->element->$namekey)) continue;
			$vars['PRODUCT_DETAILS'] .= '<p>'.$fieldsClass->getFieldName($field).': '.$fieldsClass->show($field, $data->element->$namekey, 'admin_email').'</p>';
		}
		if(!empty($vars['PRODUCT_DETAILS']))
			$vars['PRODUCT'] = true;
	}
}
