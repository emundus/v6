<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="iframedoc" id="iframedoc"></div>
<form action="<?php echo hikashop_completeLink('order'); ?>" method="post"  name="adminForm" id="adminForm">
	<table>
		<tr>
			<td width="100%">
				<?php echo JText::_( 'FILTER' ); ?>:
				<input type="text" name="search" id="search" value="<?php echo $this->escape($this->pageInfo->search);?>" class="text_area" onchange="document.adminForm.submit();" />
				<button class="btn" onclick="this.form.submit();"><?php echo JText::_( 'GO' ); ?></button>
				<button class="btn" onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'RESET' ); ?></button>
			</td>
		</tr>
	</table>
	<table class="adminlist table table-striped table-hover" cellpadding="1">
		<thead>
			<tr>
				<th class="title titlenum">
					<?php echo JText::_( 'HIKA_NUM' );?>
				</th>
				<th class="title">
					<?php echo JText::_('ORDER_NUMBER'); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('CUSTOMER'), 'c.name', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('PAYMENT_METHOD'), 'b.order_payment_method', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('DATE'), 'b.order_created', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort',   JText::_('ORDER_STATUS'), 'b.order_status', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort',   JText::_('HIKASHOP_TOTAL'), 'b.order_full_price', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort',   JText::_('AFFILIATE'),'b.order_partner_price', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort',   JText::_( 'ID' ), 'b.order_id', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="9">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
			<?php
				$k = 0;
				for($i = 0,$a = count($this->rows);$i<$a;$i++){
					$row =& $this->rows[$i];
			?>
				<tr class="<?php echo "row$k"; ?>">
					<td class="hk_center">
					<?php echo $this->pagination->getRowOffset($i);
					?>
					</td>
					<td class="hk_center">
						<?php echo $row->order_number; ?>
					</td>
					<td>
						<?php
						 if(!empty($row->username)){
						 	echo $row->name.' ( '.$row->username.' )</a><br/>';
						 }
						 echo $row->user_email;
						 ?>
					</td>
					<td class="hk_center">
						<?php echo $row->order_payment_method; ?>
					</td>
					<td class="hk_center">
						<?php echo hikashop_getDate($row->order_created,'%Y-%m-%d %H:%M');?>
					</td>
					<td class="hk_center">
						<?php echo $row->order_status;?>
					</td>
					<td class="hk_center">
						<?php echo $this->currencyHelper->format($row->order_full_price,$row->order_currency_id);?>
					</td>
					<td class="hk_center">
						<?php
						if(bccomp(sprintf('%F',$row->order_partner_price),0,5)){
							echo $this->currencyHelper->format($row->order_partner_price,$row->order_partner_currency_id);
						}
						?>
					</td>
					<td width="1%" class="hk_center">
						<?php echo $row->order_id; ?>
					</td>
				</tr>
			<?php
					$k = 1-$k;
				}
			?>
		</tbody>
	</table>
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="sales" />
	<input type="hidden" name="ctrl" value="<?php echo hikaInput::get()->getCmd('ctrl'); ?>" />
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" name="user_id" value="<?php echo hikashop_getCID('user_id'); ?>" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
