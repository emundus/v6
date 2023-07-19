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
<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&amp;ctrl=characteristic" method="post"  name="adminForm" id="adminForm">
	<div class="hk-row-fluid">
		<div class="hkc-md-4 hika_j4_search">
<?php echo $this->loadHkLayout('search', array()); ?>
		</div>
		<div class="hk-md-8">
<?php
	if(!empty($this->extrafilters)) {
		foreach($this->extrafilters as $name => $filterObj) {
			echo $filterObj->displayFilter($name, $this->pageInfo->filter);
		}
	} ?>
		</div>
	</div>
<?php 
	echo $this->loadHkLayout('columns', array()); 
?>
	<table id="hikashop_characteristic_listing" class="adminlist table table-striped table-hover" cellpadding="1">
		<thead>
			<tr>
				<th class="title titlenum">
					<?php echo JText::_( 'HIKA_NUM' );?>
				</th>
				<th class="title titlebox">
					<input type="checkbox" name="toggle" value="" onclick="hikashop.checkAll(this);" />
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('HIKA_NAME'), 'a.characteristic_value', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('HIKA_ALIAS'), 'a.characteristic_alias', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
<?php
	$count_extrafields = 0;
	if(!empty($this->extrafields)) {
		foreach($this->extrafields as $namekey => $extrafield) {
			echo '<th class="hikashop_characteristic_'.$namekey.'_title title">'.$extrafield->name.'</th>'."\r\n";
		}
		$count_extrafields = count($this->extrafields);
	}
?>
				<th class="title">
					<?php echo JHTML::_('grid.sort',   JText::_( 'ID' ), 'a.characteristic_id', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="<?php echo 5 + $count_extrafields; ?>">
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
				<?php echo $this->pagination->getRowOffset($i); ?>
				</td>
				<td class="hk_center">
					<?php echo JHTML::_('grid.id', $i, $row->characteristic_id ); ?>
				</td>
				<td>
					<a href="<?php echo hikashop_completeLink('characteristic&task=edit&cid[]='.$row->characteristic_id); ?>">
						<?php echo hikashop_translate($row->characteristic_value); ?>
					</a>
				</td>
				<td>
					<a href="<?php echo hikashop_completeLink('characteristic&task=edit&cid[]='.$row->characteristic_id); ?>">
						<?php echo $row->characteristic_alias; ?>
					</a>
				</td>
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
				<td width="1%" class="hk_center">
					<?php echo $row->characteristic_id; ?>
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
