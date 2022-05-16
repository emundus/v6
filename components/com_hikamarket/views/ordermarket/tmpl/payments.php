<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="hikam_product_listing">
<form action="<?php echo hikamarket::completeLink('order&task=payments'); ?>" method="post" id="adminForm" name="adminForm">

<div class="hk-row-fluid">
	<div class="hkc-md-12">
<?php
	echo $this->loadHkLayout('search', array(
		'id' => 'hikamarket_order_listing_search'
	));
?>
		<div class="hikam_sort_zone"><?php
			if(!empty($this->ordering_values))
				echo JHTML::_('select.genericlist', $this->ordering_values, 'filter_fullorder', 'onchange="this.form.submit();"', 'value', 'text', $this->full_ordering);
		?></div>
	</div>
	<div class="hkc-md-12">
		<div class="expand-filters">
<?php

echo $this->orderStatusType->display('filter_status', $this->pageInfo->filter->filter_status, ' onchange="document.adminForm.submit();"', true);

foreach($this->extrafilters as $name => $filterObj) {
	echo $filterObj->displayFilter($name, $this->pageInfo->filter);
}

?>
		</div>
		<div style="clear:both"></div>
	</div>
</div>

<div id="hikam_payments_main_listing">
<?php
$manage = false; // hikamarket::acl('order/show');
$extra_classes = '';

foreach($this->orders as $order) {
	$url = ($manage) ? hikamarket::completeLink('order&task=show&cid='.$order->order_id) : null;
?>
	<div class="hk-card hk-card-default hk-card-vendor-order<?php echo $extra_classes; ?>" data-hkm-order="<?php echo (int)$order->order_id; ?>">
		<div class="hk-card-header">
<?php if(!empty($url)) { ?>
			<a class="hk-row-fluid" href="<?php echo $url; ?>">
<?php } else { ?>
			<div class="hk-row-fluid">
<?php } ?>
				<div class="hkc-sm-6 hkm_order_date">
					<i class="fa fa-clock"></i>
					<?php echo hikashop_getDate((int)$order->order_created, '%Y-%m-%d %H:%M'); ?>
				</div>
				<div class="hkc-sm-6 hkm_order_price">
					<i class="fa fa-credit-card"></i>
					<?php echo $this->currencyHelper->format($order->order_full_price, $order->order_currency_id); ?>
				</div>
<?php if(!empty($url)) { ?>
			</a>
<?php } else { ?>
			</div>
<?php } ?>
		</div>
		<div class="hk-card-body">
			<div class="hk-row-fluid">
				<div class="hkc-sm-4 hkm_order_number">
<?php
	if(!empty($url)) {
					?><a href="<?php echo $url; ?>"><?php
	}
					?><i class="far fa-file-alt" style="margin-right:4px;"></i><span class="hika_order_number_value"><?php echo $order->order_number; ?></span><?php
	if(!empty($url)) {
					?></a><?php
	}
?>
<?php if(!empty($order->order_invoice_number)) { ?>
					<span class="hkm_order_number_invoice_separator"> - </span>
					<span class="hkm_invoice_number_value"><?php echo $order->order_invoice_number; ?></span>
<?php } ?>
				</div>
				<div class="hkc-sm-2 hkm_order_status">
					<span class="order-label order-label-<?php echo preg_replace('#[^a-z_0-9]#i', '_', str_replace(' ','_',$order->order_status)); ?>"><?php
						echo hikamarket::orderStatus($order->order_status);
					?></span>
				</div>
				<div class="hkc-sm-6 hkm_payment_details"><?php
					echo JText::sprintf('PAYMENT_X_TRANSACTIONS', (int)$order->counter);
				?></div>
			</div>
		</div>
	</div>
<?php
}
?>
	<div class="hikamarket_orders_footer">
		<div class="hikamarket_pagination">
			<?php $this->pagination->form = '_bottom'; echo $this->pagination->getListFooter(); ?>
			<?php echo $this->pagination->getResultsCounter(); ?>
		</div>
	</div>
</div>

	<input type="hidden" name="Itemid" value="<?php echo $this->Itemid; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKAMARKET_COMPONENT; ?>" />
	<input type="hidden" name="task" value="payments" />
	<input type="hidden" name="ctrl" value="<?php echo hikaInput::get()->getCmd('ctrl'); ?>" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
</div>
