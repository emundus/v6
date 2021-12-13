<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><form action="<?php echo hikamarket::completeLink('user');?>" method="post" name="hikamarket_form" id="hikamarket_user_form">

<h2><?php echo JText::_('CUSTOMER'); ?></h2>
	<dl class="hikam_options">
		<dt class="hikamarket_user_name"><label><?php echo JText::_('HIKA_USER_NAME');?></label></dt>
		<dd class="hikamarket_user_name"><span id="hikamarket_user_name"><?php echo @$this->user->name; ?></span></dd>
	</dl>
	<dl class="hikam_options">
		<dt class="hikamarket_user_username"><label><?php echo JText::_('HIKA_USERNAME');?></label></dt>
		<dd class="hikamarket_user_username"><span id="hikamarket_user_username"><?php echo @$this->user->username; ?></span></dd>
	</dl>
<?php if(hikamarket::acl('user/edit/email')) { ?>
	<dl class="hikam_options">
		<dt class="hikamarket_user_email"><label><?php echo JText::_('HIKA_EMAIL');?></label></dt>
		<dd class="hikamarket_user_email"><span id="hikamarket_user_email">
<?php if($this->vendor->vendor_id > 1) {
			echo $this->escape(@$this->user->user_email);
		} else { ?>
			<input type="text" name="data[user][user_email]" value="<?php echo $this->escape(@$this->user->user_email); ?>" />
<?php } ?>
		</span></dd>
	</dl>
<?php } ?>
<?php
$edit_custom_fields = hikamarket::acl('user/edit/customfields');
foreach($this->fields['user'] as $fieldName => $oneExtraField) { ?>
	<dl class="hikam_options">
		<dt class="hikamarket_user_<?php echo $fieldName; ?>"><label><?php echo $this->fieldsClass->getFieldName($oneExtraField); ?></label></dt>
		<dd class="hikamarket_user_<?php echo $fieldName; ?>"><?php
			if($edit_custom_fields && !empty($oneExtraField->vendor_edit) && $this->vendor->vendor_id <= 1)
				echo $this->fieldsClass->display($oneExtraField, @$this->user->$fieldName, 'data[user]['.$fieldName.']');
			else
				echo $this->fieldsClass->show($oneExtraField, @$this->user->$fieldName);
		?></dd>
	</dl>
<?php } ?>

<?php if(hikamarket::acl('user/show/address')) { ?>
<h2><?php echo JText::_('ADDRESSES'); ?></h2>
<?php
	$this->setLayout('show');
	echo $this->loadTemplate('address');
?>
<?php } ?>

<?php if(hikamarket::acl('order/listing')) { ?>
<h2><?php echo JText::_('ORDERS'); ?></h2>
	<table class="hikam_listing hikam_table hikam_bordered" style="width:100%">
		<thead>
			<tr>
				<th class="hikamarket_order_num_title title titlenum"><?php
					echo JText::_('HIKA_NUM');
				?></th>
				<th class="hikamarket_order_id_title title"><?php
					echo JText::_('ORDER_NUMBER');
				?></th>
				<th class="hikamarket_order_status_title title"><?php
					echo JText::_('ORDER_STATUS');
				?></th>
				<th class="hikamarket_order_date_title title"><?php
					echo JText::_('DATE')
				?></th>
				<th class="hikamarket_order_total_title title"><?php
					echo JText::_('HIKASHOP_TOTAL');
				?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th colspan="5"><?php
					echo JText::sprintf('HIKAM_DISPLAY_X_ORDERS_OF_Y', count($this->orders), (int)$this->order_count);
				?> <a href="<?php echo hikamarket::completeLink('order&task=listing&filter_user='.$this->user->user_id); ?>"><?php echo JText::_('HIKAM_SEE_MORE'); ?></a></th>
			</tr>
		</tfoot>
<?php
$k = 0;
$i = 1;
$order_show = hikamarket::acl('order/show');
if(!empty($this->orders)) {
	foreach($this->orders as $order) {
?>
			<tr class="row<?php echo $k; ?>">
				<td class="hikamarket_order_num_value" style="text-align:center"><?php
					echo $i;
				?></td>
				<td class="hikamarket_order_id_value" align="center">
<?php if($order_show) { ?>
					<a href="<?php echo hikamarket::completeLink('order&task=show&cid='.$order->order_id); ?>"><?php
	}
	echo $order->order_number;
	if($order_show) {
					?></a>
<?php
	}
?>
				</td>
				<td class="hikamarket_order_status_value">
					<span class="order-label order-label-<?php echo preg_replace('#[^a-z_0-9]#i', '_', str_replace(' ','_',$order->order_status)); ?>"><?php
						echo hikamarket::orderStatus($order->order_status);
					?></span>
				</td>
				<td class="hikamarket_order_date_value"><?php echo hikamarket::getDate($order->order_created,'%Y-%m-%d %H:%M');?></td>
				<td class="hikamarket_order_total_value"><?php
					echo $this->currencyHelper->format($order->order_full_price, $order->order_currency_id);
				?></td>
			</tr>
<?php
		$i++;
		$k = 1 - $k;
	}
} else {
?>
			<tr class="row<?php echo $k; ?>">
				<td class="hikamarket_no_order" colspan="6"><?php
					echo JText::_('NO_ORDERS_FOUND');
				?></td>
			</tr>
<?php
}
?>
		</tbody>
	</table>
<?php } ?>
	<input type="hidden" name="cid" value="<?php echo @$this->user->user_id; ?>"/>
	<input type="hidden" name="option" value="<?php echo HIKAMARKET_COMPONENT; ?>"/>
	<input type="hidden" name="task" value="show"/>
	<input type="hidden" name="ctrl" value="user"/>
	<?php echo JHTML::_('form.token'); ?>
</form>
