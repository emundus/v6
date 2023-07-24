<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="iframedoc" id="iframedoc"></div>
<form action="<?php echo hikashop_completeLink('cart'); ?>" method="post" name="adminForm" id="adminForm">
<div class="hk-row-fluid">
	<div class="hkc-md-5 hika_j4_search"><?php
		echo $this->loadHkLayout('search', array());
	?></div>
	<div class="hkc-md-7 hikashop_listing_filters">
	</div>
</div>
<?php 
	echo $this->loadHkLayout('columns', array()); 
?>
<?php $colspan = 9; ?>
	<table id="hikashop_cart_listing" class="adminlist table table-striped table-hover" cellpadding="1">
		<thead>
			<tr>
				<th class="title titlenum">
					<?php echo JText::_( 'HIKA_NUM' );?>
				</th>
				<th class="title titlebox">
					<input type="checkbox" name="toggle" value="" onclick="hikashop.checkAll(this);" />
				</th>
				<th class="title title_product_id">
					<?php echo JHTML::_('grid.sort', JText::_('HIKA_NAME'), 'cart.cart_name', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title title_cart_user_id">
					<?php echo JHTML::_('grid.sort', JText::_('HIKA_USERNAME'), 'cart.user_id', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
<?php
if($this->config->get('cart_ip', 1)) {
	$colspan++;
?>
				<th class="title title_cart_ip">
					<?php echo JHTML::_('grid.sort', JText::_('HIKA_IP'), 'cart.cart_ip', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
<?php
}
?>
				<th class="titletoggle title_cart_current">
					<?php echo JHTML::_('grid.sort', JText::_('SHOW_DEFAULT'), 'cart.cart_current', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title title_cart_quantity">
					<?php  echo JText::_('PRODUCT_QUANTITY'); ?>
				</th>
				<th class="title title_cart_total">
					<?php echo JText::_('CART_PRODUCT_TOTAL_PRICE'); ?>
				</th>
				<th class="title title_cart_date">
					<?php echo JHTML::_('grid.sort', JText::_('DATE'), 'cart.cart_modified', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title title_cart_id">
					<?php echo JHTML::_('grid.sort', JText::_('ID'), 'cart.cart_id', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="<?php echo $colspan; ?>">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
<?php
	$i = 0;
	$k = 1;
	foreach($this->carts as $cart) {
		if(empty($cart->cart_id))
			continue;
?>
			<tr class="row<?php echo $k; ?>">
				<td><?php
					echo $this->pagination->getRowOffset($i);
				?></td>
				<td><?php
					echo JHTML::_('grid.id', $i, $cart->cart_id );
				?></td>
				<td>
<?php
		if($this->manage) {
?>
					<a href="<?php echo hikashop_completeLink('cart&task=edit&cart_type='.$this->cart_type.'&cart_id='.$cart->cart_id.'&cid[]='.$cart->cart_id); ?>" title="<?php echo JText::_('HIKA_EDIT'); ?>"><?php
						if(!empty($cart->cart_name))
							echo $cart->cart_name;
						else
							echo '<em>' . JText::_('HIKA_NONE') . '</em>';
					?>
						<i class="fas fa-pen"></i>
					</a>
<?php
					} else {
						echo $cart->cart_name;
					}
				?></td>
				<td><?php
		$user = null;
		if(empty($cart->user) || $cart->user_id == 0) {
			echo JText::_('NO_REGISTRATION');
		} else {
			if(!empty($cart->user->username)) {
				echo $cart->user->name.' ( '.$cart->user->username.' )</a><br/>';
			}
			$target = '';
			if($this->popup)
				$target = '" target="_top';
			$url = hikashop_completeLink('user&task=edit&cid[]='.$cart->user_id);

			if($this->manageUser)
				echo $cart->user->user_email.' <a href="'.$url.$target.'"><i class="fa fa-chevron-right"></i></a>';
		}
				?></td>
<?php
if($this->config->get('cart_ip', 1)) {
?>
				<td><?php
					echo $cart->cart_ip;
				?></td>
<?php
}
?>
				<td><?php
					if(!empty($cart->cart_current)) {
						echo '<i class="icon-publish"></i>';
					} else {
						echo '<i class="icon-unpublish"></i>';
					}
				?></td>
				<td><?php
					echo (int)@$cart->quantity;
				?></td>
				<td>
					<span class='hikashop_product_price_full hikashop_product_price'><?php
						echo $this->currencyClass->format($cart->price, $cart->currency);
					?></span>
				</td>
				<td><?php
					echo hikashop_getDate($cart->cart_modified);
				?></td>
				<td width="1%"><?php
					echo $cart->cart_id;
				?></td>
			</tr>
<?php
		$i++;
		$k = 1 - $k;
	}
?>
		</tbody>
	</table>
	<input type="hidden" name="cart_type" value="<?php echo $this->escape(hikaInput::get()->getString('cart_type', 'cart')); ?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="<?php echo hikaInput::get()->getCmd('ctrl'); ?>" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
