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
<?php 
	$extra_class = "";
	if (HIKASHOP_J40) {
		$extra_class = "hika_j4_search no_search_features";
	}
?>
<form action="<?php echo hikashop_completeLink('currency'); ?>" method="post"  name="adminForm" id="adminForm">
	<div class="hk-row-fluid">
		<div class="hkc-md-8 <?php echo $extra_class; ?>">
<?php
	echo $this->loadHkLayout('search', array());
?>
		</div>
		<div class="hkc-md-4">
		</div>
	</div>
<?php 
	echo $this->loadHkLayout('columns', array()); 
?>
	<table id="hikashop_currency_listing" class="adminlist table table-striped table-hover" cellpadding="1">
		<thead>
			<tr>
				<th class="title titlenum">
					<?php echo JText::_( 'HIKA_NUM' );?>
				</th>
				<th class="title titlebox">
					<input type="checkbox" name="toggle" value="" onclick="hikashop.checkAll(this);" />
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('HIKA_NAME'), 'a.currency_name', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title titletoggle">
					<?php echo JHTML::_('grid.sort', JText::_('CURRENCY_CODE'), 'a.currency_code', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title titletoggle">
					<?php echo JHTML::_('grid.sort', JText::_('CURRENCY_SYMBOL'), 'a.currency_symbol', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title hk_center">
					<?php echo JHTML::_('grid.sort', JText::_('RATE'), 'a.currency_rate', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title hk_center">
					<?php echo JText::_('CURRENCY_DISPLAY_EXAMPLE'); ?>
				</th>
				<th class="title titletoggle">
					<?php echo JHTML::_('grid.sort', JText::_('CURRENCY_DISPLAYED'), 'a.currency_displayed', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title titletoggle">
					<?php echo JHTML::_('grid.sort',   JText::_('HIKA_PUBLISHED'), 'a.currency_published', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort',   JText::_( 'ID' ), 'a.currency_id', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="10">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
			<?php
				$k = 0;
				$i = 0;
				foreach($this->rows as $row){
					$publishedid = 'currency_published-'.$row->currency_id;
					$displayedid = 'currency_displayed-'.$row->currency_id;
			?>
				<tr class="<?php echo "row$k"; ?>">
					<td class="hk_center">
					<?php echo $this->pagination->getRowOffset($i); ?>
					</td>
					<td class="hk_center">
						<?php echo JHTML::_('grid.id', $i, $row->currency_id ); ?>
					</td>
					<td>
						<?php if($this->manage){ ?>
							<a href="<?php echo hikashop_completeLink('currency&task=edit&cid[]='.$row->currency_id); ?>">
						<?php } ?>
								<?php echo $row->currency_name; ?>
						<?php if($this->manage){ ?>
							</a>
						<?php } ?>
					</td>
					<td>
						<?php if($this->manage){ ?>
							<a href="<?php echo hikashop_completeLink('currency&task=edit&cid[]='.$row->currency_id); ?>">
						<?php } ?>
								<?php echo $row->currency_code; ?>
						<?php if($this->manage){ ?>
							</a>
						<?php } ?>
					</td>
					<td class="hk_center">
						<?php echo $row->currency_symbol; ?>
					</td>
					<td class="hk_center">
						<?php echo $row->currency_rate; ?>
					</td>
					<td class="hk_center">
						<?php echo $this->currency->format(123456.78,$row->currency_id).' / '.$this->currency->format(-123456.78,$row->currency_id); ?>
					</td>
					<td class="hk_center">
						<?php if($this->manage){ ?>
							<span id="<?php echo $displayedid ?>" class="spanloading"><?php echo $this->toggleClass->toggle($displayedid,(int) $row->currency_displayed,'currency') ?></span>
						<?php }else{ echo $this->toggleClass->display('activate',$row->currency_displayed); } ?>
					</td>
					<td class="hk_center">
						<?php if($this->manage){ ?>
							<span id="<?php echo $publishedid ?>" class="spanloading"><?php echo $this->toggleClass->toggle($publishedid,(int) $row->currency_published,'currency') ?></span>
						<?php }else{ echo $this->toggleClass->display('activate',$row->currency_published); } ?>
					</td>
					<td width="1%" class="hk_center">
						<?php echo $row->currency_id; ?>
					</td>
				</tr>
			<?php
					$i++;
					$k = 1-$k;
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
