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
<form action="<?php echo hikashop_completeLink('entry'); ?>" method="post"  name="adminForm" id="adminForm">
<div class="hk-row-fluid">
	<div class="hkc-xs-6 hika_j4_search">
<?php
	echo $this->loadHkLayout('search', array());
?>
	</div>
	<div class="hkc-xs-6 hikashop_listing_filters">
<?php
	echo $this->category->display("filter_status",$this->pageInfo->filter->filter_status,false);
?>
	</div>
</div>
<?php 
	echo $this->loadHkLayout('columns', array()); 
?>
	<table id="hikashop_entry_listing" class="adminlist table table-striped table-hover" cellpadding="1">
		<thead>
			<tr>
				<th class="title titlenum">
					<?php echo JText::_( 'HIKA_NUM' );?>
				</th>
				<th class="title titlebox">
					<input type="checkbox" name="toggle" value="" onclick="hikashop.checkAll(this);" />
				</th>
				<th class="title">
					<?php echo JText::_('HIKASHOP_ORDER'); ?>
				</th>
				<?php
				$count_fields=0;
				if(!empty($this->fields)){
					foreach($this->fields as $field){
						$count_fields++;
						echo '<th class="title">'.JHTML::_('grid.sort', $this->fieldsClass->trans($field->field_realname), 'b.'.$field->field_namekey, $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ).'</th>';
					}
				}
				?>
				<th class="title">
					<?php echo JHTML::_('grid.sort',   JText::_( 'ID' ), 'b.entry_id', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="<?php echo 4+$count_fields;?>">
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
						<?php echo JHTML::_('grid.id', $i, $row->entry_id ); ?>
					</td>
					<td class="hk_center">
						<?php if(!empty($row->order_id)){
								if($this->manage){ ?>
									<a href="<?php echo hikashop_completeLink('order&task=edit&cid[]='.$row->order_id.'&cancel_redirect='.urlencode(base64_encode(hikashop_completeLink('entry')))); ?>">
										<?php echo $row->order_number; ?>
									</a>
						<?php 	}
							}
						 ?>
					</td>
					<?php
					if(!empty($this->fields)){
						foreach($this->fields as $field){
							$namekey = $field->field_namekey;
							echo '<td>'.$this->fieldsClass->show($field,$row->$namekey).'</td>';
						}
					}
					?>
					<td width="1%" class="hk_center">
						<?php echo $row->entry_id; ?>
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
<?php
if(hikashop_level(2) && !empty($this->fields) && !empty($this->rows) && empty($this->pageInfo->search) && empty($this->pageInfo->limit->start) ){
	foreach($this->fields as $field){
		if( in_array($field->field_type,array('radio','singledropdown','zone'))){
			$this->fieldsClass->chart('entry',$field,$this->pageInfo->filter->filter_status,450,240);
		}
	}
}
?><br style="clear:both" />
