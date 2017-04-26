<?php
/**
 * @package	HikaShop for Joomla!
 * @version	3.0.1
 * @author	hikashop.com
 * @copyright	(C) 2010-2017 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="hikashop_order_listing">
<?php global $Itemid; ?>
<div class="header hikashop_header_title"><h1><?php echo JText::_('ORDERS');?></h1></div>

<div class="toolbar hikashop_header_buttons" id="toolbar" style="float: right;">
	<table class="hikashop_no_border">
		<tr>
			<td>
				<a onclick="javascript:submitbutton('cancel'); return false;" href="#">
					<span class="icon-32-back" title="<?php echo JText::_('HIKA_BACK'); ?>"></span>
					<?php echo JText::_('HIKA_BACK'); ?>
				</a>
			</td>
		</tr>
	</table>
</div>
<div style="clear:both"></div>

<form action="<?php echo hikashop_completeLink('order'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="hikashop_search_block">
		<input type="text" name="search" id="hikashop_search" value="<?php echo $this->escape($this->pageInfo->search);?>" placeholder="<?php echo JText::_('HIKA_SEARCH'); ?>" class="inputbox" onchange="document.adminForm.submit();" />
		<button class="btn" onclick="this.form.submit();"><?php echo JText::_('GO'); ?></button>
		<button class="btn" onclick="document.getElementById('hikashop_search').value='';this.form.submit();"><?php echo JText::_( 'RESET' ); ?></button>
	</div>
	<input type="hidden" name="Itemid" value="<?php echo $Itemid; ?>"/>
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
	<?php echo JHTML::_('form.token'); ?>
</form>

<table id="hikashop_order_listing" class="hikashop_orders adminlist table table-striped table-hover" style="width:100%">
	<thead>
		<tr>
			<th class="hikashop_order_num_title title titlenum"><?php
				echo JText::_('HIKA_NUM');
			?></th>
			<th class="hikashop_order_number_title title"><?php
				echo JText::_('ORDER_NUMBER');
			?></th>
			<th class="hikashop_order_date_title title"><?php
				echo JHTML::_('grid.sort', JText::_('DATE'), 'hk_order.order_created', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value);
			?></th>
			<th class="hikashop_order_status_title title"><?php
				echo JHTML::_('grid.sort', JText::_('ORDER_STATUS'), 'hk_order.order_status', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value);
			?></th>
			<th class="hikashop_order_total_title title"><?php
				echo JHTML::_('grid.sort', JText::_('HIKASHOP_TOTAL'), 'hk_order.order_full_price', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value);
			?></th>
<?php
	$extra_cols = 0;
	if(!empty($this->action_column)) {
		$extra_cols++;
?>
			<th class="hikashop_order_action_title title"><?php
				echo JText::_('HIKASHOP_ACTION');
			?></th>
<?php
	}
?>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="<?php echo 5 + $extra_cols; ?>">
				<div class="pagination">
					<form action="<?php echo hikashop_completeLink('order'); ?>" method="post" name="adminForm_bottom">
						<?php $this->pagination->form = '_bottom'; echo $this->pagination->getListFooter(); ?>
						<?php echo $this->pagination->getResultsCounter(); ?>
						<input type="hidden" name="Itemid" value="<?php echo $Itemid; ?>"/>
						<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
						<input type="hidden" name="task" value="" />
						<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>" />
						<input type="hidden" name="boxchecked" value="0" />
						<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
						<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
						<?php echo JHTML::_('form.token'); ?>
					</form>
				</div>
			</td>
		</tr>
	</tfoot>
	<tbody>
<?php
	$url_itemid = (!empty($Itemid) ? '&Itemid=' . $Itemid : '');
	$orderUrl = hikashop_completeLink('order'.$url_itemid);

	$config =& hikashop_config();
	if($config->get('force_ssl',0) && strpos('https://',$orderUrl) === false) {
		$orderUrl = str_replace('http://','https://',HIKASHOP_LIVE) . 'index.php?option=com_hikashop&ctrl=order';
	}

	$i = 0;
	$k = 0;
	foreach($this->rows as &$row) {
?>
		<tr class="row<?php echo $k; ?>">
			<td class="hikashop_order_num_value"><?php
				echo $this->pagination->getRowOffset($i);
			?></td>
			<td class="hikashop_order_number_value">
				<a href="<?php echo hikashop_completeLink('order&task=show&cid='.$row->order_id.$url_itemid); ?>"><?php
					echo $row->order_number;
				?></a>
			</td>
			<td class="hikashop_order_date_value"><?php
				echo hikashop_getDate($row->order_created,'%Y-%m-%d %H:%M');
			?></td>
			<td class="hikashop_order_status_value">
				<span class="hikashop_order_listing_status order-label order-label-<?php echo preg_replace('#[^a-z_0-9]#i', '_', str_replace(' ','_', $row->order_status)); ?>"><?php
					echo hikashop_orderStatus($row->order_status);
				?></span>
			</td>
			<td class="hikashop_order_total_value"><?php
				echo $this->currencyClass->format($row->order_full_price, $row->order_currency_id);
			?></td>
<?php if(!empty($this->action_column)) { ?>
			<td class="hikashop_order_action_value"><?php

		$dropData = array();

		if(!empty($row->show_cancel_button)) {
			$dropData[] = array(
				'name' => JText::_('CANCEL_ORDER'),
				'link' => '#cancel_order',
				'click' => 'return window.localPage.cancelOrder('.(int)$row->order_id.',\''.$row->order_number.'\');',
			);
		}
		if(!empty($row->show_payment_button) && bccomp($row->order_full_price, 0, 5) > 0) {
			$url_param = ($this->payment_change) ? '&select_payment=1' : '';
			$url = hikashop_completeLink('order&task=pay&order_id='.$row->order_id.$url_param.$url_itemid);
			if($config->get('force_ssl',0) && strpos('https://',$url) === false)
				$url = str_replace('http://','https://', $url);
			$dropData[] = array(
				'name' => JText::_('PAY_NOW'),
				'link' => $url
			);
		}
		if($this->config->get('allow_reorder', 0)) {
			$url = hikashop_completeLink('order&task=reorder&order_id='.$row->order_id.$url_itemid);
			if($config->get('force_ssl',0) && strpos('https://',$url) === false)
				$url = str_replace('http://','https://', $url);
			$dropData[] = array(
				'name' => JText::_('REORDER'),
				'link' => $url
			);
		}

		if(!empty($dropData)) {
			echo $this->dropdownHelper->display(
				JText::_('HIKA_MORE'),
				$dropData,
				array('type' => 'btn', 'right' => true, 'up' => false)
			);
		}
			?></td>
<?php } ?>
		</tr>
<?php
		$i++;
		$k = 1 - $k;
	}
	unset($row);
?>
	</tbody>
</table>

<?php
	if(!empty($this->action_column)) {
?>
<script type="text/javascript">
if(!window.localPage) window.localPage = {};
window.localPage.cancelOrder = function(id, number) {
	var d = document, form = d.getElementById('hikashop_cancel_order_form');
	if(!form || !form.elements['order_id']) {
		console.log('Error: Form not found, cannot cancel the order');
		return false;
	}
	if(!confirm('<?php echo JText::_('HIKA_CONFIRM_CANCEL_ORDER', true); ?>'.replace(/ORDER_NUMBER/, number)))
		return false;
	form.elements['order_id'].value = id;
	form.submit();
	return false;
};
</script>
<form action="<?php echo hikashop_completeLink('order&task=cancel_order&email=1'); ?>" name="hikashop_cancel_order_form" id="hikashop_cancel_order_form" method="POST">
	<input type="hidden" name="Itemid" value="<?php echo $Itemid; ?>"/>
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="cancel_order" />
	<input type="hidden" name="email" value="1" />
	<input type="hidden" name="order_id" value="" />
	<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>" />
	<input type="hidden" name="redirect_url" value="<?php echo hikashop_currentURL(); ?>" />
	<?php echo JHTML::_('form.token'); ?>
</form>
<?php
	}
?>
</div>
