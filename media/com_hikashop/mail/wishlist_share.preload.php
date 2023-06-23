<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
$imageHelper = hikashop_get('helper.image');
$cartclass = hikashop_get('class.cart');
$user = $user_full = $data->user->user_email;
if(!empty($data->user->name)) {
	$user = $data->user->name;
	$user_full = $data->user->name . ' ( ' . $user_full . ' ) ';

}
$texts = array(
	'MAIL_HEADER' => JText::_('HIKASHOP_MAIL_HEADER'),
	'WISHLIST_TITLE' => JText::sprintf('WISHLIST_EMAIL_TITLE', $data->cart->cart_name, HIKASHOP_LIVE),
	'WISHLIST_BEGIN_MESSAGE' => JText::sprintf('WISHLIST_BEGIN_MESSAGE', $user_full),
	'DISPLAY_THE_WISHLIST_OF_USER' => JText::sprintf('DISPLAY_THE_WISHLIST_OF_USER', $user),
);

$vars = array(
	'URL' =>  $cartclass->getShareUrl($data->cart),
	'cart' => $data->cart,
	'user' => $data->user,
);
$cartProducts = array();
foreach($data->cart->products as $product){
	$imageData = '';
	if(!empty($product->images[0]->file_path)) {
		$img = $imageHelper->getThumbnail($product->images[0]->file_path, array(50, 50), array('forcesize' => true, 'scale' => 'outside'));
		if($img->success) {
			if(substr($img->url, 0, 3) == '../')
				$image = str_replace('../', HIKASHOP_LIVE, $img->url);
			elseif(!$img->external)
				$image = substr(HIKASHOP_LIVE, 0, strpos(HIKASHOP_LIVE, '/', 9)) . $img->url;
			else
				$image = $img->url;
			$attributes = '';
			if($img->external)
				$attributes = ' width="'.$img->req_width.'" height="'.$img->req_height.'"';
			$imageData = '<img src="'.$image.'" alt="" style="float:left;margin-top:3px;margin-bottom:3px;margin-right:6px;"'.$attributes.'/>';
		}
	}
	$cartProducts[] = array(
		'product' => $product,
		'PRODUCT_IMG' => $imageData
	);
}
$templates = array('PRODUCT_LINE' => $cartProducts);
