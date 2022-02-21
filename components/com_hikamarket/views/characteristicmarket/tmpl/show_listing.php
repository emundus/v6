<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><table class="hikam_listing <?php echo (HIKASHOP_RESPONSIVE)?'table table-striped table-hover':'hikam_table'; ?>" style="width:100%">
	<thead>
		<tr>
			<th class="hikamarket_characteristic_name_title title"><?php
				echo JHTML::_('grid.sort', JText::_('HIKA_NAME'), 'characteristic.characteristic_value', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value);
				if($this->show_vendor)
					echo ' / ' . JText::_('HIKA_VENDOR');
			?></th>
<?php if($this->characteristic_ordering) { ?>
			<th class="hikamarket_characteristic_ordering_title title titlenum"><?php
				echo JHTML::_('grid.sort', JText::_('ORDERING'), 'characteristic.characteristic_ordering', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value );
			?></th>
<?php } ?>
			<th class="hikamarket_characteristic_usedcounter_title title titlenum"><?php
				echo JText::_('HIKAM_NB_OF_USED');
			?></th>
<?php if($this->characteristic_actions) { ?>
			<th class="hikamarket_characteristic_actions_title title titlenum"><?php
				echo JText::_('HIKA_ACTIONS');
			?></th>
<?php } ?>
			<th class="hikamarket_characteristic_id_title title titlenum"><?php
				echo JHTML::_('grid.sort', JText::_('ID'), 'characteristic.characteristic_id', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value );
			?></th>
		</tr>
	</thead>
<?php ?>
	<tbody>
<?php
$k = 0;
$i = 0;
foreach($this->characteristic->values as $characteristic) {
	$rowId = 'market_characteristic_'.$characteristic->characteristic_id;
	$editable = ($this->vendor->vendor_id <= 1 || $characteristic->characteristic_vendor_id == $this->vendor->vendor_id);
?>
		<tr class="row<?php echo $k; ?>" id="<?php echo $rowId; ?>">
			<td class="hikamarket_characteristic_name_value" id="market_characteristic_value_<?php echo (int)$characteristic->characteristic_id; ?>"><div><?php
	if($this->acl_edit_value && $editable) echo '<a href="#edit" onclick="return window.characteristicMgr.edit(this, '.(int)$characteristic->characteristic_id.');"><i class="fas fa-pencil-alt" style="margin-right:4px;"></i>';
	echo '<span class="value">'.$characteristic->characteristic_value.'</span>';
	if($this->acl_edit_value && $editable) echo '</a>';
			?></div><div></div><?php
	if($this->show_vendor) {
		?><div data-vendor_id="<?php echo (int)$characteristic->characteristic_vendor_id;?>" id="market_characteristic_vendor_<?php echo (int)$characteristic->characteristic_id; ?>"><div><?php
		if(!empty($characteristic->characteristic_vendor_id))
			echo @$characteristic->vendor;
		?></div><div></div></div><?php
	}
			?></td>
<?php if($this->characteristic_ordering) { ?>
			<td class="hikamarket_characteristic_ordering_value order">
<?php if($editable) { ?>
				<input type="text" size="3" name="data[values][ordering][]" id="characteristic_ordering[<?php echo (int)$characteristic->characteristic_id;?>]" value="<?php echo (int)$characteristic->characteristic_ordering;?>"/>
<?php } else { ?>
				<span><?php echo (int)$characteristic->characteristic_ordering; ?></span>
<?php } ?>
			</td>
<?php } ?>
			<td class="hikamarket_characteristic_usedcounter_value"><?php
				echo (int)@$characteristic->used;
			?></td>
<?php if($this->characteristic_actions) { ?>
			<td class="hikamarket_characteristic_actions_value"><?php
				if($this->characteristic_action_delete && $editable && (int)@$characteristic->used == 0 && ($this->vendor->vendor_id <= 1 || $this->vendor->vendor_id == (int)$characteristic->characteristic_vendor_id))
					echo $this->toggleClass->delete($rowId, (int)$characteristic->characteristic_id . '-' . (int)$this->characteristic->characteristic_id, 'characteristic', true);
				else
					echo '-';
			?></td>
<?php } ?>
			<td class="hikamarket_characteristic_id_value">
				<?php echo (int)$characteristic->characteristic_id; ?>
<?php if($editable) { ?>
				<input type="hidden" name="data[values][id][]" value="<?php echo (int)$characteristic->characteristic_id;?>"/>
<?php } ?>
			</td>
		</tr>
<?php
	$i++;
	$k = 1 - $k;
}

if(!empty($this->characteristic->characteristic_id)) { // (int)$this->characteristic->characteristic_id > 0) {
?>
		<tr class="row<?php echo $k; ?>" id="market_characteristic_tpl" style="display:none;">
			<td class="hikamarket_characteristic_name_value" id="market_characteristic_value_{ID}"><div><?php
				if($this->acl_edit_value) echo '<a href="#edit" onclick="return window.characteristicMgr.edit(this, {ID});"><i class="fas fa-pencil-alt" style="margin-right:4px;"></i>';
				echo '<span class="value">{VALUE}</span>';
				if($this->acl_edit_value) echo '</a>';
			?></div><div></div>
<?php if($this->show_vendor) { ?>
			<div data-vendor_id="{VENDOR_ID}" id="market_characteristic_vendor_{ID}"><div>{VENDOR}</div><div></div></div>
<?php } ?>
			</td>
<?php if($this->characteristic_ordering) { ?>
			<td class="hikamarket_characteristic_ordering_value order">
				<input type="text" size="3" name="data[values][ordering][]" id="characteristic_ordering[{ID}]" value="0"/>
			</td>
<?php } ?>
			<td class="hikamarket_characteristic_usedcounter_value">0</td>
<?php if($this->characteristic_actions) { ?>
			<td class="hikamarket_characteristic_actions_value"><?php
				if($this->characteristic_action_delete)
					echo $this->toggleClass->delete('market_characteristic_{ID}', '{ID}', 'characteristic', true);
			?></td>
<?php } ?>
			<td class="hikamarket_characteristic_id_value">
				{ID}
				<input type="hidden" name="data[values][id][]" value="{ID}"/>
			</td>
		</tr>
<?php
} else {
?>
		<tr class="row<?php echo $k; ?>" id="market_characteristic_tpl" style="display:none;">
			<td class="hikamarket_characteristic_name_value">
				<div>
					<input type="text" size="30" style="min-width:60%" name="data[values][value][]" value=""/>
				</div>
<?php if($this->show_vendor) { ?>
				<div>{VENDOR}</div>
<?php } ?>
			</td>
<?php if($this->characteristic_ordering) { ?>
			<td class="hikamarket_characteristic_ordering_value order">
				<input type="text" size="3" name="data[values][ordering][]" value="0"/>
			</td>
<?php } ?>
			<td class="hikamarket_characteristic_usedcounter_value">0</td>
<?php if($this->characteristic_actions) { ?>
			<td class="hikamarket_characteristic_actions_value"><?php
				if($this->characteristic_action_delete)
					echo '<a href="javascript:void(0);" onclick="window.hikashop.deleteRow(\'market_characteristic_{UUID}\'); return false;"><i class="far fa-trash"></i></a>';
				else
					echo '-';
			?></td>
<?php } ?>
			<td class="hikamarket_characteristic_id_value">
				-
				<input type="hidden" name="data[values][id][]" value="0"/>
			</td>
		</tr>
<?php
}
?>
	</tbody>
</table>
