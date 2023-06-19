<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><table class="adminlist table table-striped" cellpadding="1" width="100%">
	<tbody id="result">
<?php
	$k = 0;
	$type = $this->type;
	for($i = 0,$a = count($this->rows);$i<$a;$i++){
		$row =& $this->rows[$i];
		$id=rand();
?>
			<tr class="row<?php echo $k; ?>" id="<?php echo $type.'_'.$row->product_id.'_'.$id;?>">
				<td>
					<a href="<?php echo hikashop_completeLink('product&task=edit&cid='.$row->product_id); ?>"><?php echo $row->product_name; ?></a>
				</td>
<?php if($type!='widget'){ ?>
				<td><?php
					echo $row->product_code;
				?></td>
				<td><?php
					echo $this->currencyHelper->displayPrices(@$row->prices);
				?></td>
				<td class="order hk_center">
					<div id="<?php echo $type;?>_ordering_div_<?php echo $row->product_id.'_'.$id;?>">
						<input type="text" size="3" name="<?php echo $type;?>_ordering[<?php echo $row->product_id;?>]" id="<?php echo $type;?>_ordering[<?php echo $row->product_id;?>][<?php echo $id?>]" value="<?php echo intval(@$row->product_related_ordering);?>"/>
					</div>
				</td>
<?php } ?>
				<td class="hk_center">
					<a href="#" onclick="return deleteRow('<?php echo $type.'_div_'.$row->product_id.'_'.$id;?>','<?php echo $type;?>[<?php echo $row->product_id;?>][<?php echo $id;?>]','<?php echo $type.'_'.$row->product_id.'_'.$id;?>');"><img src="<?php echo HIKASHOP_IMAGES; ?>delete.png"/></a>
				</td>
				<td width="1%" class="hk_center">
					<?php echo $row->product_id; ?>
					<div id="<?php echo $type.'_div_'.$row->product_id.'_'.$id;?>">
						<input type="hidden" name="<?php echo $type;?>[<?php echo $row->product_id;?>]" id="<?php echo $type;?>[<?php echo $row->product_id;?>][<?php echo $id;?>]" value="<?php echo $row->product_id;?>"/>
					</div>
				</td>
			</tr>
<?php
		$k = 1-$k;
	}
?>
	</tbody>
</table>
