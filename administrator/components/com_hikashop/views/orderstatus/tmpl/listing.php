<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2020 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="iframedoc" id="iframedoc"></div>
<form action="<?php echo hikashop_completeLink('orderstatus'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="hk-row-fluid">
		<div class="hkc-md-6">
<?php
	echo $this->loadHkLayout('search', array());
?>
		</div>
		<div class="hkc-md-6">
		</div>
	</div>

<?php
	$classes = 'adminlist table';
	if(empty($this->colors)) {
		$classes .= ' table-striped table-hover';
	}
?>
	<table id="hikashop_orderstatus_listing" class="<?php echo $classes; ?>" cellpadding="1">
		<thead>
			<tr>
				<th class="title titlenum"><?php
					echo JText::_('HIKA_NUM');
				?></th>
				<th class="title titlebox">
					<input type="checkbox" name="toggle" value="" onclick="hikashop.checkAll(this);" />
				</th>
				<th class="title titlebox"><?php
					echo JText::_('HIKA_EDIT');
				?></th>
				<th class="title"><?php
					echo JHTML::_('grid.sort', JText::_('HIKA_NAME'), 'o.orderstatus_name', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value);
				?></th>
<?php
	foreach($this->orderstatus_columns as $column) {
?>
				<th class="title titlebox"><?php
					echo hikashop_hktooltip($column['description'],$column['title'],$column['text']);
				?></th>
<?php
	}
?>
				<th class="title titleorder"><?php
					if($this->ordering->ordering)
						echo JHTML::_('grid.order', $this->rows);
					echo JHTML::_('grid.sort', JText::_( 'HIKA_ORDER' ), 'o.orderstatus_ordering', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value );
				?></th>
				<th class="title titletoggle"><?php
					echo JHTML::_('grid.sort', JText::_('HIKA_PUBLISHED'), 'o.orderstatus_published', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value );
				?></th>
				<th class="title"><?php
					echo JHTML::_('grid.sort', JText::_('ID'), 'o.orderstatus_id', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value );
				?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="<?php echo 7 + count($this->orderstatus_columns); ?>">
					<?php echo $this->pagination->getListFooter(); ?>
					<?php echo $this->pagination->getResultsCounter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
<?php
	$k = 0;
	$i = 0;
	$nbRows = count($this->rows);
	foreach($this->rows as $row) {
		$publishedid = 'orderstatus_published-'.$row->orderstatus_id;
		$attributes = '';
		if(!empty($this->orderStatuses[$row->orderstatus_namekey]->orderstatus_color))
			$attributes .= ' style="background-color:'.$this->orderStatuses[$row->orderstatus_namekey]->orderstatus_color.';"';
?>
			<tr class="row<?php echo $k; ?>"<?php echo $attributes; ?>>
				<td style="text-align:center"><?php
					echo $this->pagination->getRowOffset($i);
				?></td>
				<td style="text-align:center"><?php
					echo JHTML::_('grid.id', $i, $row->orderstatus_id);
				?></td>
				<td>
<?php if($this->manage) { ?>
					<a href="<?php echo hikashop_completeLink('orderstatus&task=edit&cid='.(int)$row->orderstatus_id); ?>" title="<?php echo JText::_('HIKA_EDIT'); ?>">
						<i class="fas fa-pen"></i>
					</a>
<?php } ?>
				</td>
				<td>
<?php if($this->manage) { ?>
					<a href="<?php echo hikashop_completeLink('orderstatus&task=edit&cid='.(int)$row->orderstatus_id); ?>">
<?php } ?>
					<?php echo $row->orderstatus_name; ?>
<?php if($this->manage) { ?>
					</a>
<?php } ?>
				</td>
<?php
	foreach($this->orderstatus_columns as $key => $column) {
		$publishedid = 'orderstatus_published-'.$row->orderstatus_id;
?>
				<td style="text-align:center" id="<?php echo 'status-'.$key.'-'.$row->orderstatus_namekey; ?>"><?php
					if($column['type'] == 'toggle')
						echo $this->toggleHelper->toggle('status-'.$key.'-'.$row->orderstatus_namekey, @$row->columns[$key], 'config', array('trigger'=>$column['trigger'], 'key'=>$column['key'], 'type'=>$column['type'], 'default_value'=>$column['default']));
					if($column['type'] == 'radio')
						echo $this->toggleHelper->radio('status-'.$key.'-'.$row->orderstatus_namekey, @$row->columns[$key], 'config', array('trigger'=>$column['trigger'], 'key'=>$column['key'], 'type'=>$column['type'], 'default_value'=>$column['default']));
				?></td>
<?php
	}
?>
				<td class="order">
					<span><?php echo $this->pagination->orderUpIcon($i, $this->ordering->reverse XOR ( $row->orderstatus_ordering >= @$this->rows[$i-1]->orderstatus_ordering ), $this->ordering->orderUp, 'Move Up', $this->ordering->ordering); ?></span>
					<span><?php echo $this->pagination->orderDownIcon($i, $nbRows, $this->ordering->reverse XOR ( $row->orderstatus_ordering <= @$this->rows[$i+1]->orderstatus_ordering ), $this->ordering->orderDown, 'Move Down', $this->ordering->ordering); ?></span>
					<input type="text" name="order[]" size="5" <?php if(!$this->ordering->ordering) echo 'disabled="disabled"'?> value="<?php echo $row->orderstatus_ordering; ?>" class="text_area" style="text-align: center" />
				</td>
				<td style="text-align:center">
<?php
		if($this->manage) {
?>
					<span id="<?php echo $publishedid ?>" class="spanloading"><?php echo $this->toggleHelper->toggle($publishedid,(int) $row->orderstatus_published, 'orderstatus') ?></span>
<?php
		} else {
			echo $this->toggleHelper->display('activate', $row->orderstatus_published);
		}
?>
				</td>
				<td style="text-align:center;width:1%;"><?php
					echo (int)$row->orderstatus_id;
				?></td>
			</tr>
<?php
		$k = 1-$k;
		$i++;
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
