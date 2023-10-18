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
<form action="<?php echo hikashop_completeLink('user'); ?>" method="post"  name="adminForm" id="adminForm">
<div class="hk-row-fluid">
	<div class="hkc-xs-6 hika_j4_search">
<?php
	echo $this->loadHkLayout('search', array());
?>
	</div>
	<div id="hikashop_listing_filters_id" class="hkc-xs-6 hikashop_listing_filters <?php echo $this->openfeatures_class; ?>">
	</div>
</div>
<?php 
	echo $this->loadHkLayout('columns', array()); 
?>
	<table id="hikashop_click_listing" class="adminlist table table-striped table-hover" cellpadding="1">
		<thead>
			<tr>
				<th class="title titlenum">
					<?php echo JText::_( 'HIKA_NUM' );?>
				</th>
				<th class="title titledate">
					<?php echo JHTML::_('grid.sort', JText::_('DATE'), 'a.click_created', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<?php if(empty($this->user_id)){?>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('PARTNER'), 'b.user_email', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<?php } ?>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('IP'), 'a.click_ip', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('REFERER'), 'a.click_referer', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>

				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('TOTAL_UNPAID_AMOUNT'), 'a.click_partner_price', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort',   JText::_( 'ID' ), 'a.click_id', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="<?php echo empty($this->user_id) ? 7 : 6; ?>">
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
						<?php echo  hikashop_getDate($row->click_created,'%Y-%m-%d %H:%M'); ?>
					</td>
					<?php if(empty($this->user_id)){?>
					<td class="hk_center">
						<a href="<?php echo hikashop_completeLink('user&task=edit&cid[]='.$row->click_partner_id);?>"><?php echo $row->user_email; ?></a>
					</td>
					<?php } ?>
					<td class="hk_center">
						<?php echo $row->click_ip; ?>
					</td>
					<td>
						<a target="_blank" href="<?php echo strip_tags($row->click_referer); ?>">
						<?php if(strlen(strip_tags($row->click_referer)) > 50){
							$row->click_referer = strip_tags($row->click_referer);
							$row->click_referer = substr($row->click_referer,0,20).'...'.substr($row->click_referer,-20);
						}
						echo $row->click_referer; ?>
						</a>
					</td>

					<td class="hk_center">
						<?php
						if(bccomp(sprintf('%F',$row->click_partner_price),0,5)){
							echo $this->currencyHelper->format($row->click_partner_price,$row->user_currency_id);
						}
						?>
					</td>
					<td width="1%" class="hk_center">
						<?php echo $row->click_id; ?>
					</td>
				</tr>
			<?php
					$k = 1-$k;
				}
			?>
		</tbody>
	</table>
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="clicks" />
	<?php if(hikaInput::get()->getCmd('tmpl') == 'component') { ?><input type="hidden" name="tmpl" value="component" /> <?php } ?>
	<input type="hidden" name="user_id" value="<?php echo hikashop_getCID('user_id');?>" />
	<input type="hidden" name="ctrl" value="<?php echo hikaInput::get()->getCmd('ctrl'); ?>" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
