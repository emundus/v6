<?php
/**
 * @package	HikaShop for Joomla!
 * @version	3.0.1
 * @author	hikashop.com
 * @copyright	(C) 2010-2017 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="iframedoc" id="iframedoc"></div>
<form action="<?php echo hikashop_completeLink('orderstatus'); ?>" method="post" name="adminForm" id="adminForm">
<?php if(HIKASHOP_BACK_RESPONSIVE) { ?>
	<div class="row-fluid">
		<div class="span6">
			<div class="input-prepend input-append">
				<span class="add-on"><i class="icon-filter"></i></span>
				<input type="text" name="search" id="search" value="<?php echo $this->escape($this->pageInfo->search);?>" class="text_area" />
				<button class="btn" onclick="this.form.limitstart.value=0;this.form.submit();"><i class="icon-search"></i></button>
				<button class="btn" onclick="this.form.limitstart.value=0;document.getElementById('search').value='';this.form.submit();"><i class="icon-remove"></i></button>
			</div>
		</div>
		<div class="span6">
<?php } else { ?>
	<table>
		<tr>
			<td width="100%">
				<?php echo JText::_('FILTER'); ?>:
				<input type="text" name="search" id="search" value="<?php echo $this->escape($this->pageInfo->search);?>" class="text_area" />
				<button class="btn" onclick="this.form.limitstart.value=0;this.form.submit();"><?php echo JText::_('GO'); ?></button>
				<button class="btn" onclick="this.form.limitstart.value=0;document.getElementById('search').value='';this.form.submit();"><?php echo JText::_('RESET'); ?></button>
			</td>
			<td nowrap="nowrap">
<?php }

if(HIKASHOP_BACK_RESPONSIVE) { ?>
		</div>
	</div>
<?php } else { ?>
			</td>
		</tr>
	</table>
<?php } ?>
	<table id="hikashop_orderstatus_listing" class="adminlist table table-striped table-hover" cellpadding="1">
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
?>
			<tr class="row<?php echo $k; ?>">
				<td style="text-align:center"><?php
					echo $this->pagination->getRowOffset($i);
				?></td>
				<td style="text-align:center"><?php
					echo JHTML::_('grid.id', $i, $row->orderstatus_id);
				?></td>
				<td>
<?php if($this->manage) { ?>
					<a href="<?php echo hikashop_completeLink('orderstatus&task=edit&cid='.(int)$row->orderstatus_id); ?>">
						<img src="<?php echo HIKASHOP_IMAGES; ?>edit.png" alt="edit"/>
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
	<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
