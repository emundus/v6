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
<form action="<?php echo hikashop_completeLink('waitlist'); ?>" method="post"  name="adminForm" id="adminForm">
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
	<table id="hikashop_waitlist_listing" class="adminlist table table-striped table-hover" cellpadding="1">
		<thead>
			<tr>
				<th class="title titlenum">
					<?php echo JText::_( 'HIKA_NUM' );?>
				</th>
				<th class="title titlebox">
					<input type="checkbox" name="toggle" value="" onclick="hikashop.checkAll(this);" />
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('HIKA_NAME'), 'a.name', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('HIKA_EMAIL'), 'a.email', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('PRODUCT'), 'b.product_name', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort',   JText::_('DATE'), 'a.date', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort',   JText::_( 'ID' ), 'a.waitlist_id', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="8">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
			<?php
				$k = 0;
				$a = count($this->rows);
				if($a){
					for($i = 0;$i<$a;$i++){
						$row =& $this->rows[$i];
				?>
					<tr class="<?php echo "row$k"; ?>">
						<td class="hk_center">
						<?php echo $this->pagination->getRowOffset($i); ?>
						</td>
						<td class="hk_center">
							<?php echo JHTML::_('grid.id', $i, $row->waitlist_id ); ?>
						</td>
						<td>
							<?php if($this->manage){ ?>
								<a href="<?php echo hikashop_completeLink('waitlist&task=edit&cid[]='.$row->waitlist_id); ?>">
							<?php } ?>
									<?php echo $row->name; ?>
							<?php if($this->manage){ ?>
								</a>
							<?php } ?>
						</td>
						<td>
							<?php if($this->manage){ ?>
								<a href="<?php echo hikashop_completeLink('waitlist&task=edit&cid[]='.$row->waitlist_id); ?>">
							<?php } ?>
									<?php echo $row->email; ?>
							<?php if($this->manage){ ?>
								</a>
							<?php } ?>
						</td>
						<td>
							<?php echo $row->product_name; ?>
							<a href="<?php echo hikashop_completeLink('product&task=edit&cid[]='.$row->product_id); ?>">
								<i class="fa fa-chevron-right"></i>
							</a>
						</td>
						<td class="hk_center">
							<?php echo hikashop_getDate($row->date,'%Y-%m-%d %H:%M');?>
						</td>
						<td width="1%" class="hk_center">
							<?php echo $row->waitlist_id; ?>
						</td>
					</tr>
				<?php
						$k = 1-$k;
					}
				}
			?>
		</tbody>
	</table>
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="<?php echo hikaInput::get()->getCmd('ctrl'); ?>" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
