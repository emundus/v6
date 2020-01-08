<?php
/**
 * field tos
 *
 * @package	VirtueMart
 * @subpackage Cart
 * @author Max Milbers
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2014 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL2, see LICENSE.php
 * @version $Id: cart.php 7682 2014-02-26 17:07:20Z Milbo $
 */

defined('_JEXEC') or die('Restricted access');
$_prefix = $viewData['prefix'];
$field = $viewData['field'];
$tos = $field['value'];

$app = JFactory::getApplication();
if($app->isSite()){
	vmJsApi::popup('#full-tos','#terms-of-service');
	if (!class_exists('VirtueMartCart')) require(VMPATH_SITE . DS . 'helpers' . DS . 'cart.php');
	$cart = VirtuemartCart::getCart();
	$cart->prepareVendor();
	if(empty($tos) and !VmConfig::get ('agree_to_tos_onorder', true)){
		if(is_array($cart->BT) and !empty($cart->BT['tos'])){
			$tos = $cart->BT['tos'];
		}
	}
}

if(!class_exists('VmHtml')) require(VMPATH_ADMIN.DS.'helpers'.DS.'html.php');
$class = 'terms-of-service';
if(!empty($field['required'])){
	$class .= ' required';
}
echo VmHtml::checkbox ($_prefix.$field['name'], $tos, 1, 0, 'class="'.$class.'"', 'tos');
?>

<div class="forgotpassword" style="display: inline-block;">
<?php
if ( $app->isSite() ) {
?>
<div class="terms-of-service">
	<label for="tos">
		<a href="<?php echo JRoute::_ ('index.php?option=com_virtuemart&view=vendor&layout=tos&virtuemart_vendor_id=1', FALSE) ?>" class="terms-of-service" id="terms-of-service" rel="facebox"
		   target="_blank">
			<span class="vmicon vm2-termsofservice-icon"></span>
			<?php echo vmText::_ ('COM_VIRTUEMART_CART_TOS_READ_AND_ACCEPTED') ?>
		</a>
	</label>

	<div id="full-tos">
		<h2><?php echo vmText::_ ('COM_VIRTUEMART_CART_TOS') ?></h2>
		<?php echo $cart->vendor->vendor_terms_of_service ?>
		</div>
</div>
<?php
}
?>
</div>