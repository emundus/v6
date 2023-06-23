<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="hikashop_carts_listing">
<?php echo $this->toolbarHelper->process($this->toolbar, $this->title); ?>
<table id="hikashop_cart_listing" class="hikashop_carts adminlist table table-striped table-hover" style="width:100%">
	<thead>
		<tr>
<!-- NAME HEADER -->
			<th class="hikashop_cart_name_title title"><?php
				echo JText::_('CART_PRODUCT_NAME');
			?></th>
<!-- EO NAME HEADER -->
<!-- QUANTITY HEADER -->
			<th class="hikashop_cart_quantity_title title"><?php
				echo JText::_('PRODUCT_QUANTITY');
			?></th>
<!-- EO QUANTITY HEADER -->
<!-- PRICE HEADER -->
			<th class="hikashop_cart_price_title title"><?php
				echo JText::_('PRODUCT_PRICE');
			?></th>
<!-- EO PRICE HEADER -->
<!-- LAST MODIFIED HEADER -->
			<th class="hikashop_cart_modified_title title"><?php
				echo JText::_('HIKA_LAST_MODIFIED');
			?></th>
<!-- EO LAST MODIFIED HEADER -->
<!-- CURRENT HEADER -->
			<th class="hikashop_cart_current_title title"><?php
				echo JText::_('HIKA_CURRENT');
			?></th>
<!-- EO CURRENT HEADER -->
<!-- DELETE HEADER -->
			<th class="hikashop_cart_delete_title title"><?php
				echo JText::_('HIKA_DELETE');
			?></th>
<!-- EO DELETE HEADER -->
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="6">
				<form method="POST" action="<?php echo hikashop_completeLink('cart&cart_type='.$this->cart_type.'&Itemid='.$this->Itemid); ?>">
				<?php
					echo $this->pagination->getListFooter();
					echo '<span class="hikashop_results_counter">'.$this->pagination->getResultsCounter().'</span>';
				?>
					<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
					<input type="hidden" name="task" value="listing" />
					<input type="hidden" name="ctrl" value="<?php echo hikaInput::get()->getCmd('ctrl'); ?>" />
				<?php echo JHTML::_('form.token'); ?>
				</form>
			</td>
		</tr>
	</tfoot>
	<tbody>
<?php
	$i = 0;
	$k = 0;
	foreach($this->carts as $cart) {
?>
		<tr class="row<?php echo $k; ?>">
<!-- NAME -->
			<td data-title="<?php echo JText::_('CART_PRODUCT_NAME'); ?>" class="hikashop_cart_name_value">
				<a href="<?php echo hikashop_completeLink('cart&task=show&cid='.(int)$cart->cart_id.'&Itemid='.$this->Itemid);?>" title="<?php echo JText::_('HIKA_EDIT'); ?>">
					<i class="fas fa-pen"></i><?php
					if(!empty($cart->cart_name))
						echo $this->escape($cart->cart_name);
					else
						echo '<em>'.JText::_('HIKA_NO_NAME').'</em>';
				?></a>
			</td>
<!-- EO NAME -->
<!-- QUANTITY -->
			<td data-title="<?php echo JText::_('PRODUCT_QUANTITY'); ?>" class="hikashop_cart_quantity_value"><?php
				echo (int)@$cart->package['total_quantity'];
			?></td>
<!-- EO QUANTITY -->
<!-- PRICE -->
			<td data-title="<?php echo JText::_('PRODUCT_PRICE'); ?>" class="hikashop_cart_price_value"><?php
				echo $this->currencyClass->format(@$cart->total->prices[0]->price_value_with_tax, $cart->cart_currency_id);
			?></td>
<!-- EO PRICE -->
<!-- LAST MODIFIED -->
			<td data-title="<?php echo JText::_('HIKA_LAST_MODIFIED'); ?>" class="hikashop_cart_modified_value"><?php
				echo hikashop_getDate($cart->cart_modified);
			?></td>
<!-- EO LAST MODIFIED -->
<!-- CURRENT -->
			<td data-title="<?php echo JText::_('HIKA_CURRENT'); ?>" class="hikashop_cart_current_value"><?php
				if($cart->cart_current) {
					?><i class="fa fa-star"></i><?php
				} else {
?>
				<a href="<?php echo hikashop_completeLink('cart&task=setcurrent&cid='.(int)$cart->cart_id.'&'.hikashop_getFormToken().'=1&Itemid='.$this->Itemid);?>" title="<?php echo JText::_('HIKA_SET_AS_CURRENT'); ?>">
					<i class="far fa-star"></i>
				</a>
<?php
				}
			?></td>
<!-- EO CURRENT -->
<!-- DELETE -->
			<td data-title="<?php echo JText::_('HIKA_DELETE'); ?>" class="hikashop_cart_delete_value">
				<a title="<?php echo JText::_('HIKA_DELETE'); ?>" href="<?php echo hikashop_completeLink('cart&task=remove&cid='.(int)$cart->cart_id.'&'.hikashop_getFormToken().'=1&Itemid='.$this->Itemid); ?>" onclick="if(window.localPage && window.localPage.confirmDelete) return window.localPage.confirmDelete()">
					<i class="fas fa-trash"></i>
				</a>
			</td>
<!-- EO DELETE -->
		</tr>
<?php
		$i++;
		$k = 1 - $k;
	}
?>
	</tbody>
</table>
<script type="text/javascript">
if(!window.localPage)
	window.localPage = {};
window.localPage.confirmDelete = function() {
	return confirm('<?php
		if($this->cart_type == 'wishlist')
			echo JText::_('HIKA_CONFIRM_DELETE_WISHLIST', true);
		else
			echo JText::_('HIKA_CONFIRM_DELETE_CART', true);
	?>');
};
</script>
</div>
