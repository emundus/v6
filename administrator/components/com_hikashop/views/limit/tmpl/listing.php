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
<form action="<?php echo hikashop_completeLink('limit'); ?>" method="post"  name="adminForm" id="adminForm">
	<div class="hk-row-fluid">
		<div class="hkc-md-8 hika_j4_search">
			<?php echo $this->loadHkLayout('search', array()); ?>
		</div>
		<div class="hkc-md-4 hikashop_listing_filters">
			<?php echo $this->limit_type->display('limit_type',$this->pageInfo->filter->limit_type, true); ?>
		</div>
	</div>
<?php 
	echo $this->loadHkLayout('columns', array()); 
?>
	<table id="hikashop_limit_listing" class="adminlist table table-striped" cellpadding="1">
		<thead>
			<tr>
				<th class="title titlenum">
					<?php echo JText::_( 'HIKA_NUM' );?>
				</th>
				<th class="title titlebox">
					<input type="checkbox" name="toggle" value="" onclick="hikashop.checkAll(this);" />
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('HIKA_TYPE'), 'a.limit_type', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('PRODUCT'), 'a.limit_product_id', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('CATEGORY'), 'a.limit_category_id', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title">
					<?php echo JText::_('VALUE'); ?>
				</th>
				<th class="title">
					<?php echo JText::_('PERIOD'); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('START_DATE'), 'a.limit_start', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('END_DATE'), 'a.limit_end', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title titletoggle">
					<?php echo JHTML::_('grid.sort',   JText::_('HIKA_PUBLISHED'), 'a.limit_published', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort',   JText::_( 'ID' ), 'a.limit_id', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
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
					$publishedid = 'limit_published-'.$row->limit_id;
			?>
				<tr class="<?php echo "row".$k; ?>">
					<td>
					<?php echo $this->pagination->getRowOffset($i); ?>
					</td>
					<td>
						<?php echo JHTML::_('grid.id', $i, $row->limit_id ); ?>
					</td>
					<td>
						<?php if($this->manage){ ?>
							<a href="<?php echo hikashop_completeLink('limit&task=edit&cid[]='.$row->limit_id); ?>">
						<?php }
						echo $row->limit_type;
						if($this->manage){ ?>
							</a>
						<?php } ?>
					</td>
					<td>
						<?php
							if(empty($row->product_name)) {
								echo '';
							} else {
								echo $row->product_name;
							}
						?>
					</td>
					<td>
						<?php
							if(empty($row->categories) || count($row->categories)<1) {
								echo '';
							} else {
								$first = true;
								foreach($row->categories as $category) {
									if($first)
										$first = false;
									else
										echo ' / ';
									if($this->manage_category)
										echo '<a href="'.hikashop_completeLink('category&task=edit&cid[]='.$category->category_id).'">';
									echo $category->category_name;
									if($this->manage_category)
										echo '</a>';
								}
							}
						?>
					</td>
					<td>
						<?php
							if( $row->limit_type == 'price' ) {
								echo $this->currencyHelper->displayPrices(array($row),'limit_value','limit_currency_id');
							} else if( $row->limit_type == 'weight' ) {
								echo @$row->limit_value . ' ' . $row->limit_unit;
							} else {
								echo @$row->limit_value;
							}
						?>
					</td>
					<td>
						<?php echo $row->limit_periodicity; ?>
					</td>
					<td>
						<?php echo hikashop_getDate($row->limit_start); ?>
					</td>
					<td>
						<?php echo hikashop_getDate($row->limit_end); ?>
					</td>
					<td>
						<?php if($this->manage){ ?>
							<span id="<?php echo $publishedid ?>" class="spanloading"><?php echo $this->toggleClass->toggle($publishedid,(int) $row->limit_published,'limit') ?></span>
						<?php }else{ echo $this->toggleClass->display('activate',$row->limit_published); } ?>
					</td>
					<td width="1%">
						<?php echo $row->limit_id; ?>
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
