<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><table class="adminlist table table-striped table-hover" cellpadding="1" width="100%">
	<tbody id="result">
	<?php
		$row=$this->rows[0];
		$id=rand();
		?>
		<tr id="characteristic_<?php echo $row->characteristic_id.'_'.$id;?>">
			<td class="column_move"><img src="../media/com_hikashop/images/move.png" alt=""></td>
			<td>
				<?php
					echo $this->popup->display(
						'<i class="fas fa-pen"></i>',
						'HIKA_EDIT',
						hikashop_completeLink("characteristic&task=editpopup&cid=".$row->characteristic_id.'&characteristic_parent_id='.$row->characteristic_parent_id.'&id='.$id,true ),
						'charac_edit_button'.$row->characteristic_id,
						860, 480, 'title="'.JText::_('HIKA_EDIT').'"', '', 'link'
					);
				?>
			</td>
			<td>
				<?php echo $row->characteristic_value; ?>
			</td>
			<td class="order">
			<input type="text" size="3" name="characteristic_ordering[<?php echo $row->characteristic_id;?>]" id="characteristic_ordering[<?php echo $row->characteristic_id;?>][<?php echo $id;?>]" value="<?php echo $row->characteristic_ordering;?>"/>
			</td>
			<td class="hk_center">
				<a href="#" title="<?php echo JText::_('HIKA_DELETE'); ?>" onclick="return deleteRow('characteristic_div_<?php echo $row->characteristic_id.'_'.$id;?>','characteristic[<?php echo $row->characteristic_id;?>][<?php echo $id;?>]','characteristic_<?php echo $row->characteristic_id.'_'.$id;?>');">
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
	</tbody>
</table>
