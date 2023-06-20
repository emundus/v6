<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
$td_class = 'class="hk_center"';
if (HIKASHOP_J40)
	$td_class = '';
?>
<div class="iframedoc" id="iframedoc"></div>
<form action="<?php echo hikashop_completeLink('filter'); ?>" method="post"  name="adminForm" id="adminForm">
<div class="hk-row-fluid">
	<div class="hkc-md-4 hika_j4_search">
<?php
	echo $this->loadHkLayout('search', array());
?>
	</div>
	<div class="hkc-md-8 hikashop_listing_filters">
	</div>
</div>
<?php 
	echo $this->loadHkLayout('columns', array()); 
?>
	<table id="hikashop_filter_listing" class="adminlist table table-striped table-hover" cellpadding="1">
		<thead>
			<tr>
				<th class="title titlenum">
					<?php echo JText::_( 'HIKA_NUM' );?>
				</th>
				<th class="title titlebox">
					<input type="checkbox" name="toggle" value="" onclick="hikashop.checkAll(this);" />
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('HIKA_NAME'), 'a.filter_name', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title titledata">
					<?php echo JHTML::_('grid.sort', JText::_('APPLY_ON'), 'a.filter_data', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('HIKA_TYPE'), 'a.filter_type', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title titleorder">
				<?php echo JHTML::_('grid.sort',  JText::_( 'HIKA_ORDER' ), 'a.filter_ordering',$this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
					<?php if ($this->order->ordering) {
						$keys = array_keys($this->rows);  
						$rows_nb = end($keys);
						$href = "javascript:saveorder(".$rows_nb.", 'saveorder')";
						?><a href="<?php echo $href; ?>" rel="tooltip" class="saveorder btn btn-sm btn-secondary float-end" title="Save Order">
							<button class="button-apply btn btn-success" type="button">
<!--							<span class="icon-apply" aria-hidden="true"></span> -->
								<i class="fas fa-save"></i>
							</button>
						</a><?php
					} ?>
				</th>
				<th class="title titlepublished">
					<?php echo JHTML::_('grid.sort',   JText::_('HIKA_PUBLISHED'), 'a.filter_published', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title titleid">
					<?php echo JHTML::_('grid.sort',   JText::_( 'ID' ), 'a.filter_id', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="11">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
			<?php
				$k = 0;
				for($i = 0,$a = count($this->rows);$i<$a;$i++){
					$row =& $this->rows[$i];
					$publishedid = 'filter_published-'.$row->filter_id;

					$row->filter_data = hikashop_unserialize($row->filter_data);
			?>
				<tr class="<?php echo "row$k"; ?>">
					<td class="hk_center">
					<?php echo $this->pagination->getRowOffset($i); ?>
					</td>
					<td class="hk_center">
						<?php echo JHTML::_('grid.id', $i, $row->filter_id ); ?>
					</td>
					<td <?php echo $td_class; ?>>
						<?php if($this->manage){ ?>
							<a href="<?php echo hikashop_completeLink('filter&task=edit&cid[]='.$row->filter_id); ?>">
						<?php }
							echo $row->filter_name;
						if($this->manage){ ?>
							</a>
						<?php } ?>
					</td>
					<td>
						<?php
							if(is_array($row->filter_data)){
								echo implode(',', $row->filter_data);
							}else{
								echo $row->filter_data;
							}
						?>
					</td>
					<td <?php echo $td_class; ?>>
						<?php
							echo $row->filter_type;
						?>
					</td>
					<td class="order">
						<?php if($this->manage){  ?>
							<span><?php echo $this->pagination->orderUpIcon( $i, $this->order->reverse XOR ( $row->filter_ordering >= @$this->rows[$i-1]->filter_ordering ), $this->order->orderUp, 'Move Up',$this->order->ordering ); ?></span>
							<span><?php echo $this->pagination->orderDownIcon( $i, $a, $this->order->reverse XOR ( $row->filter_ordering <= @$this->rows[$i+1]->filter_ordering ), $this->order->orderDown, 'Move Down' ,$this->order->ordering); ?></span>
							<input type="text" name="order[]" size="5" <?php if(!$this->order->ordering) echo 'disabled="disabled"'?> value="<?php echo $row->filter_ordering; ?>" class="text_area" style="text-align: center" />
						<?php }else{ echo $row->filter_ordering; } ?>
					</td>
					<td <?php echo $td_class; ?>>
						<?php if($this->manage){ ?>
							<span id="<?php echo $publishedid ?>" class="spanloading"><?php echo $this->toggleClass->toggle($publishedid,(int) $row->filter_published,'filter') ?></span>
						<?php }else{ echo $this->toggleClass->display('activate',$row->filter_published); } ?>
					</td>
					<td width="1%" class="hk_center">
						<?php echo $row->filter_id; ?>
					</td>
				</tr>
			<?php
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
