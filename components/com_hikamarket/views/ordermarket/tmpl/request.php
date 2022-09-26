<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><form action="<?php echo hikamarket::completeLink('order'); ?>" method="post" id="hikamarket_order_request_form" name="hikamarket_order_request_form">
<?php
if(count($this->data) <= 1) {
?>

<dl class="hikam_options">
	<dt><?php echo JText::_('HIKAM_STATS_TOTAL_ORDERS'); ?></dt>
	<dd><?php echo $this->total->count; ?></dd>

	<dt><?php echo JText::_('HIKASHOP_TOTAL'); ?></dt>
	<dd><?php echo $this->currencyHelper->format($this->total->value, $this->total->currency); ?></dd>
</dl>

<?php
} else {
?>
	<table class="hikam_listing <?php echo (HIKASHOP_RESPONSIVE)?'table table-striped table-hover table-bordered':'hikam_table'; ?>" style="width:100%">
		<thead>
			<tr>
				<th><?php echo JText::_('HIKAM_STATS_TOTAL_ORDERS'); ?></th>
				<th><?php echo JText::_('HIKASHOP_TOTAL'); ?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td><?php echo $this->total->count; ?></td>
				<td><?php echo $this->currencyHelper->format($this->total->value, $this->total->currency); ?></td>
			</tr>
		</tfoot>
		<tbody>
<?php
	foreach($this->data as $data) {
?>
			<tr>
				<td><?php echo (int)$data->count; ?></td>
				<td><?php echo $this->currencyHelper->format($data->value, $data->currency); ?></td>
			</tr>
<?php
	}
?>
		</tbody>
	</table>
<?php
}
?>
	<input type="hidden" name="Itemid" value="<?php echo $this->Itemid; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKAMARKET_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="data[request]" value="1" />
	<input type="hidden" name="ctrl" value="<?php echo hikaInput::get()->getCmd('ctrl'); ?>" />
<?php
	if($this->total->value != 0)
		echo JHTML::_( 'form.token' );
?>
</form>
