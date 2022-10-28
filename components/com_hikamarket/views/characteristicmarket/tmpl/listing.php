<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div>
<form action="<?php echo hikamarket::completeLink('characteristic&task=listing'); ?>" method="post" id="adminForm" name="adminForm">
	<div class="hk-row-fluid">
		<div class="hkc-md-12"><?php
	echo $this->loadHkLayout('search', array(
		'id' => 'hikamarket_characteristic_listing_search',
	));
		?></div>
	</div>
	<div class="hk-row-fluid">
		<div class="hkc-md-12">
			<div class="expand-filters" style="width:auto;">
<?php
	if(!empty($this->vendorType))
		echo $this->vendorType->display('filter_vendors', @$this->pageInfo->filter->vendors);
?>
			</div>
			<div style="clear:both"></div>
		</div>
	</div>
	<table class="hikam_listing hikam_table" style="width:100%">
		<thead>
			<tr>
				<th class="hikamarket_characteristic_name_title title"><?php
					echo JHTML::_('grid.sort', JText::_('HIKA_NAME'), 'characteristic.characteristic_value', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value);
				?></th>
				<th class="hikamarket_characteristic_alias_title title"><?php
					echo JHTML::_('grid.sort', JText::_('HIKA_ALIAS'), 'characteristic.characteristic_alias', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value);
				?></th>
<?php if($this->show_vendor) { ?>
				<th class="hikamarket_characteristic_brnfot_title title"><?php
					echo JText::_('HIKA_VENDOR');
				?></th>
<?php } ?>
				<th class="hikamarket_characteristic_valuecounter_title title titlenum"><?php
					echo JText::_('HIKAM_NB_OF_VALUES');
				?></th>
				<th class="hikamarket_characteristic_usedcounter_title title titlenum"><?php
					echo JText::_('HIKAM_NB_OF_USED');
				?></th>
<?php if($this->characteristic_actions) { ?>
				<th class="hikamarket_characteristic_actions_title title titlenum"><?php
					echo JText::_('HIKA_ACTIONS');
				?></th>
<?php } ?>
				<th class="hikamarket_characteristic_id_title title titlenum">
					<?php echo JHTML::_('grid.sort', JText::_( 'ID' ), 'characteristic.characteristic_id', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
			</tr>
		</thead>
<?php if(!isset($this->embbed)) {
	$columns = 5;
	if($this->characteristic_actions)
		$columns++;
	if($this->show_vendor)
		$columns++;
?>
		<tfoot>
			<tr>
				<td colspan="<?php echo $columns; ?>">
					<?php echo $this->pagination->getListFooter(); ?>
					<?php echo $this->pagination->getResultsCounter(); ?>
				</td>
			</tr>
		</tfoot>
<?php } ?>
		<tbody>
<?php
$k = 0;
$i = 0;
foreach($this->characteristics as $characteristic) {
	$rowId = 'market_characteristic_'.$characteristic->characteristic_id;
	if($this->manage)
		$url = hikamarket::completeLink('characteristic&task=show&cid='.$characteristic->characteristic_id);
?>
			<tr class="row<?php echo $k; ?>" id="<?php echo $rowId; ?>">
				<td class="hikamarket_characteristic_name_value"><?php
					if(!empty($url)) echo '<a href="'.$url.'"><i class="fas fa-pencil-alt" style="margin-right:4px;"></i>';
					echo $this->escape($characteristic->characteristic_value);
					if(!empty($url)) echo '</a>';
				?></td>
				<td class="hikamarket_characteristic_alias_value"><?php
					if(!empty($url)) echo '<a href="'.$url.'">';
					echo $this->escape($characteristic->characteristic_alias);
					if(!empty($url)) echo '</a>';
				?></td>
<?php if($this->show_vendor) { ?>
				<td class="hikamarket_characteristic_vendor_value"><?php
					if(empty($characteristic->characteristic_vendor_id))
						echo '<em>'.JText::_('HIKA_NONE').'</em>';
					else
						echo $characteristic->vendor;
				?></td>
<?php } ?>
				<td class="hikamarket_characteristic_valuecounter_value"><?php
					echo (int)$characteristic->counter;
				?></td>
				<td class="hikamarket_characteristic_usedcounter_value"><?php
					echo (int)$characteristic->used;
				?></td>
<?php if($this->characteristic_actions) { ?>
				<td class="hikamarket_characteristic_actions_value"><?php
					if($this->characteristic_action_delete && ($this->vendor->vendor_id <= 1 || $this->vendor->vendor_id == $characteristic->characteristic_vendor_id) && empty($characteristic->used))
						echo $this->toggleClass->delete($rowId, (int)$characteristic->characteristic_id, 'characteristic', true);
					else
						echo '-';
				?></td>
<?php } ?>
				<td class="hikamarket_characteristic_id_value"><?php echo $characteristic->characteristic_id; ?></td>
			</tr>
<?php
	$i++;
	$k = 1 - $k;
}
?>
		</tbody>
	</table>
	<input type="hidden" name="Itemid" value="<?php echo $this->Itemid; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKAMARKET_COMPONENT; ?>" />
	<input type="hidden" name="task" value="listing" />
	<input type="hidden" name="ctrl" value="<?php echo hikaInput::get()->getCmd('ctrl'); ?>" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
</div>
