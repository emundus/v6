<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div style="float:right">
	<?php
		echo $this->popup->display(
			'<i class="fa fa-plus"></i> '.JText::_('ADD'),
			'ADD',
			 hikashop_completeLink("characteristic&task=editpopup&characteristic_parent_id=".@$this->element->characteristic_id,true ),
			'value_add_button',
			860, 480, 'class="btn btn-success"', '', 'link'
		);
	?>
</div>
<br/>
<table id="hikashop_characteristic_values_listing" class="adminlist table table-striped table-hover" cellpadding="1" width="100%">
	<thead>
		<tr>
			<th class="title titletoggle"></th>
			<th class="title titletoggle"><?php
				echo JText::_('HIKA_EDIT');
			?></th>
			<th class="title"><?php echo JText::_('VALUE');
			?></th>
<?php
	if(!empty($this->extrafields)) {
		foreach($this->extrafields as $namekey => $extrafield) {
			echo '<th class="hikashop_characteristic_'.$namekey.'_title title">'.$extrafield->name.'</th>'."\r\n";
		}
	}
?>
			<th class="title titletoggle"><?php
				echo JText::_('ORDERING');
			?></th>
			<th class="title titletoggle"><?php
				echo JText::_('HIKA_DELETE');
			?></th>
			<th class="title"><?php
				echo JText::_('ID');
			?></th>
		</tr>
	</thead>
	<tbody id="characteristic_listing">
<?php
	hikashop_loadJslib('jquery');
	if(!empty($this->element->values)){
		$k = 0;
		for($i = 0,$a = count($this->element->values);$i<$a;$i++){
			$row =& $this->element->values[$i];
			$id=rand();
?>
		<tr id="characteristic_<?php echo $row->characteristic_id.'_'.$id;?>">
			<td class="column_move"><img src="../media/com_hikashop/images/move.png" alt=""></td>
			<td><?php
				echo $this->popup->display(
					'<i class="fas fa-pen"></i>',
					'ADD',
					hikashop_completeLink("characteristic&task=editpopup&cid=".$row->characteristic_id.'&characteristic_parent_id='.$this->element->characteristic_id.'&id='.$id,true ),
					'value_'.$row->characteristic_id.'_edit_button',
					860, 480, 'title="'.JText::_('HIKA_EDIT').'"', '', 'link'
				);
			?></td>
			<td><?php
				echo hikashop_translate($row->characteristic_value);
			?></td>
<?php
		if(!empty($this->extrafields)) {
			foreach($this->extrafields as $namekey => $extrafield) {
				$value = '';
				if(!empty($extrafield->value)) {
					$n = $extrafield->value;
					$value = $row->$n;
				} else if(!empty($extrafield->obj)) {
					$n = $extrafield->obj;
					$value = $n->showfield($this, $namekey, $row);
				}
				echo '<td class="hikashop_characteristic_'.$namekey.'_value">'.$value.'</td>';
			}
		}
?>
			<td class="order">
				<input type="text" size="3" name="characteristic_ordering[<?php echo $row->characteristic_id;?>]" id="characteristic_ordering[<?php echo $row->characteristic_id;?>][<?php echo $id;?>]" value="<?php echo $row->characteristic_ordering;?>"/>
			</td>
			<td class="hk_center">
				<a title="<?php echo Jtext::_('HIKA_DELETE'); ?>" href="#" onclick="return deleteRow('characteristic_div_<?php echo $row->characteristic_id.'_'.$id;?>','characteristic[<?php echo $row->characteristic_id;?>][<?php echo $id;?>]','characteristic_<?php echo $row->characteristic_id.'_'.$id;?>');">
					<i class="fas fa-trash"></i>
				</a>
			</td>
			<td width="1%" class="hk_center">
				<?php echo $row->characteristic_id; ?>
				<div id="characteristic_div_<?php echo $row->characteristic_id.'_'.$id;?>">
					<input type="hidden" name="characteristic[<?php echo $row->characteristic_id;?>]" id="characteristic[<?php echo $row->characteristic_id;?>][<?php echo $id;?>]" value="<?php echo $row->characteristic_id;?>"/>
				</div>
			</td>
		</tr>
<?php
			$k = 1-$k;
		}
	}
?>
	</tbody>
</table>
<script type="text/javascript">
	hkjQuery("tbody#characteristic_listing").sortable({
		axis: "y", cursor: "move", opacity: 0.8,
		helper: function(e, ui) {
			ui.children().each(function() {
				hkjQuery(this).width(hkjQuery(this).width());
			});
			return ui;
		},
		stop: function(event, ui) {
			recalculateOrdering("tbody#characteristic_listing");
			window.hikashop.cleanTableRows('hikashop_characteristic_values_listing');
		}
	});
	function recalculateOrdering(selector) {
		var table = document.querySelector(selector);
		var orderingInputs = table.querySelectorAll('input:not([type="hidden"])');
		for(var i = 0; i < orderingInputs.length; i++) {
			orderingInputs[i].value = i + 1;
		}
	}
</script>
