<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php if( !$this->singleSelection ) { ?>
<fieldset>
	<div class="toolbar" id="toolbar" style="float: right;">
		<button class="hikabtn" type="button" onclick="if(document.adminForm.boxchecked.value==0){alert('<?php echo JText::_('PLEASE_SELECT_SOMETHING', true); ?>');}else{submitbutton('useselection');}"><i class="fas fa-check"></i> <?php echo JText::_('OK'); ?></button>
	</div>
</fieldset>
<?php } ?>
<form action="<?php echo hikamarket::completeLink('vendor'); ?>" method="post" name="adminForm" id="adminForm">
	<table class="hikam_filter" style="width:100%">
		<tr>
			<td width="100%">
				<?php echo JText::_('FILTER'); ?>:
				<input type="text" id="hikamarket_vendor_search" name="search" value="<?php echo $this->escape($this->pageInfo->search);?>" class="text_area" onchange="this.form.submit();" />
				<button class="hikabtn" onclick="this.form.submit();"><i class="fas fa-search"></i></button>
				<button class="hikabtn" onclick="document.getElementById('hikamarket_vendor_search').value='';this.form.submit();"><i class="fas fa-times"></i></button>
			</td>
		</tr>
	</table>
	<table class="hikam_listing <?php echo (HIKASHOP_RESPONSIVE)?'table table-striped table-hover':'hikam_table'; ?>" style="cell-spacing:1px">
		<thead>
			<tr>
				<th class="title titlenum"><?php
					echo JText::_('HIKA_NUM');
				?></th>
<?php if( !$this->singleSelection ) { ?>
				<th class="title titlebox"><input type="checkbox" name="toggle" value="" onclick="hikashop.checkAll(this);" /></th>
<?php } ?>
				<th class="title"><?php
					echo JHTML::_('grid.sort', JText::_('HIKA_NAME'), 'vendor.vendor_name', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value );
				?></th>
<?php if($this->mainVendor) { ?>
				<th class="title"><?php
					echo JHTML::_('grid.sort', JText::_('HIKA_EMAIL'), 'vendor.vendor_email', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value );
				?></th>
<?php }
	if(!empty($this->extraFields['vendor'])) {
		foreach($this->extraFields['vendor'] as $fieldName => $oneExtraField) {
?>
				<th class="hikamarket_vendor_custom_<?php echo $oneExtraField->field_namekey;?> title" align="center"><?php
					echo $this->fieldsClass->getFieldName($oneExtraField);
				?></th>
<?php
		}
	}
?>
				<th class="title"><?php
					echo JHTML::_('grid.sort', JText::_('ID'), 'vendor.vendor_id', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value);
				?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="10"><?php
					echo $this->pagination->getListFooter();
					echo $this->pagination->getResultsCounter();
				?></td>
			</tr>
		</tfoot>
		<tbody>
<?php
$k = 0;
$i = 0;
foreach($this->vendors as $vendor) {

	$lbl1 = ''; $lbl2 = '';
	$extraTr = '';
	if( $this->singleSelection ) {
		$data = '{id:'.$vendor->vendor_id;
		foreach($this->elemStruct as $s) {
			if($s == 'id')
				continue;
			$data .= ','.$s.':\''. str_replace(array('\'','"'),array('\\\'','\\"'),$vendor->$s).'\'';
		}
		$data .= '}';
		$extraTr = ' style="cursor:pointer" onclick="window.top.hikamarket.submitBox('.$data.');"';

		if(!empty($this->pageInfo->search)) {
			$row = hikamarket::search($this->pageInfo->search, $vendor, 'vendor_id');
		}
	} else {
		$lbl1 = '<label for="cb'.$i.'">';
		$lbl2 = '</label>';
		$extraTr = ' onclick="hikamarket.checkRow(\'cb'.$i.'\');"';
	}
?>
			<tr class="row<?php echo $k; ?>"<?php echo $extraTr; ?>>
				<td align="center"><?php
					echo $this->pagination->getRowOffset($i);
				?></td>
<?php if( !$this->singleSelection ) { ?>
				<td align="center">
					<input type="checkbox" onclick="this.clicked=true; this.checked=!this.checked" value="<?php echo $vendor->vendor_id;?>" name="cid[]" id="cb<?php echo $i;?>"/>
				</td>
<?php } ?>
				<td><?php
					echo $lbl1 . $vendor->vendor_name . $lbl2;
				?></td>
<?php if($this->mainVendor) { ?>
				<td><?php
					echo $lbl1 . $vendor->vendor_email . $lbl2;
				?></td>
<?php }
	if(!empty($this->extraFields['vendor'])) {
		foreach($this->extraFields['vendor'] as $fieldName => $oneExtraField) {
?>
				<td class="hikamarket_vendor_custom_<?php echo $oneExtraField->field_namekey;?>_row"><?php
					echo $this->fieldsClass->show($oneExtraField, $this->vendorFields->$fieldName);
				?></td>
<?php
		}
	}
?>
				<td width="1%" align="center"><?php
					echo $vendor->vendor_id;
				?></td>
			</tr>
<?php
		$k = 1-$k;
		$i++;
	}
?>
		</tbody>
	</table>
<?php if( $this->singleSelection ) { ?>
	<input type="hidden" name="pid" value="0" />
<?php } ?>
	<input type="hidden" name="option" value="<?php echo HIKAMARKET_COMPONENT; ?>" />
	<input type="hidden" name="task" value="selection" />
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" name="selection" value="vendor" />
	<input type="hidden" name="confirm" value="<?php echo $this->confirm ? '1' : '0'; ?>" />
	<input type="hidden" name="single" value="<?php echo $this->singleSelection ? '1' : '0'; ?>" />
	<input type="hidden" name="ctrl" value="vendor" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
<?php
	if(!empty($this->afterParams)) {
		foreach($this->afterParams as $p) {
			if(empty($p[0]) || !isset($p[1]))
				continue;
			echo '<input type="hidden" name="'.$this->escape($p[0]).'" value="'.$this->escape($p[1]).'"/>' . "\r\n";
		}
	}
?>
	<?php echo JHTML::_('form.token'); ?>
</form>
