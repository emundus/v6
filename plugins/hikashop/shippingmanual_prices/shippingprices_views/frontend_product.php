<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="hikashop_shippingmanual_prices">
<?php
$title = (count($shippings) > 1) ? JText::_('SEVERAL_SHIPPING_METHOD_PER_PRODUCT') : JText::_('ONE_SHIPPING_METHOD_PER_PRODUCT');
?>
	<div class="hikashop_shipping_manual_price"><?php echo $title; ?></div><br/>
	<div class="shipping_price_per_prdct_method">
<?php
foreach($shipData as $key => $value) {

	if ( !empty($shipData[$key]['several']) && $display == 1 ) {
?>
		<div id="shipping_per_product_<?php echo $key; ?>">
			<span class="shipping_per_product_<?php echo $key; ?>">
				<?php echo JText::sprintf('HIKA_FOR_AN_ORDER_OF',$shipData[$key]['name'],$shipData[$key]['minQtity'],$view->element->product_name);?>
				<span class="price_per_product"><?php echo $shipData[$key]['price']; ?></span>
			</span>
		</div>
<?php
	} elseif ($shipData[$key]['minQtity'] <= 1) {
?>
		<div id="shipping_per_product_<?php echo $key; ?>">
			<span class="shipping_per_product_<?php echo $key; ?>"> <?php echo $shipData[$key]['name'] . ' : ';?>
				<span class="price_per_product"><?php echo $shipData[$key]['price']; ?></span>
			</span>
		</div>
<?php
	}
}
?>
	</div>
</div><br/>
