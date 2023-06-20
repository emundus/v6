<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
	$extra_css = "";
	if (HIKASHOP_J40)
		$extra_css = "display: block; min-height: 50px; max-width:unset !important;";
?>
<div style="min-height: 50px;">
	<h1 style="float:left;"><?php echo JText::_('SELECT_SUBZONES'); ?></h1>
	<div class="toolbar" id="toolbar" style="float: right;">
		<?php if(!in_array($this->type,array('discount','shipping','payment','config','tax'))){?>
			<button class="btn btn-primary" type="button" onclick="submitbutton('newchild');"><i class="fa fa-plus"></i> <?php echo JText::_('HIKA_NEW'); ?></button>
		<?php }?>
		<button class="btn btn-success" type="button" onclick="if(document.adminForm.boxchecked.value==0){alert('<?php echo JText::_( 'PLEASE_SELECT_SOMETHING',true ); ?>');}else{submitbutton('addchild');}"><i class="fa fa-save"></i> <?php echo JText::_('OK'); ?></button>
	</div>
</div>
<div class="iframedoc" id="iframedoc"></div>
<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&amp;ctrl=zone&amp;tmpl=component" method="post"  name="adminForm" id="adminForm">
	<table width="100%">
		<tr>
			<td style="<?php echo $extra_css; ?>">
				<?php echo $this->loadHkLayout('search'); ?>
			</td>
			<td nowrap="nowrap" style="text-align:right; <?php echo $extra_css; ?>">
				<?php echo $this->filters->country; ?>
				<?php echo $this->filters->type; ?>
			</td>
		</tr>
	</table>
	<table id="hikashop_zone_selection_listing" class="adminlist table table-striped table-hover" cellpadding="1">
		<thead>
			<tr>
				<th class="title titlenum">
					<?php echo JText::_( 'HIKA_NUM' );?>
				</th>
				<th class="title titlebox">
					<input type="checkbox" name="toggle" value="" onclick="hikashop.checkAll(this);" />
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('ZONE_NAME_ENGLISH'), 'a.zone_name_english', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value,'selectchildlisting' ) . ' / ' . JHTML::_('grid.sort', JText::_('HIKA_NAME'), 'a.zone_name', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value,'selectchildlisting' ); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('ZONE_CODE_2'), 'a.zone_code_2', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value,'selectchildlisting' ); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('ZONE_CODE_3'), 'a.zone_code_3', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value,'selectchildlisting' ); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('ZONE_TYPE'), 'a.zone_type', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ,'selectchildlisting'); ?>
				</th>
				<th class="title titletoggle">
					<?php echo JHTML::_('grid.sort',   JText::_('HIKA_PUBLISHED'), 'a.zone_published', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value,'selectchildlisting' ); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort',   JText::_( 'ID' ), 'a.zone_id', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value,'selectchildlisting' ); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="10">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
			<?php
				$k = 0;
				for($i = 0,$a = count($this->rows);$i<$a;$i++){
					$row =& $this->rows[$i];
					$publishedid = 'zone_published-'.$row->zone_id;
			?>
				<tr class="<?php echo "row$k"; ?>">
					<td class="hk_center">
					<?php echo $this->pagination->getRowOffset($i); ?>
					</td>
					<td class="hk_center">
						<?php echo JHTML::_('grid.id', $i, $row->zone_id ); ?>
					</td>
					<td>
						<?php if(in_array($this->type,array('discount','shipping','payment','config','tax'))){?>
							<a href="<?php echo hikashop_completeLink('zone&task=addchild&cid='.$row->zone_id.'&type='.$this->type.'&subtype='.$this->subtype.'&map='.$this->map,true); ?>">
						<?php }?>
							<?php echo $row->zone_name_english; ?>
						<?php if(in_array($this->type,array('discount','shipping','payment','config','tax'))){?>
							</a>
						<?php }?>
						<?php echo ' / ' . $row->zone_name; ?>
					</td>
					<td class="hk_center">
						<?php echo $row->zone_code_2; ?>
					</td>
					<td class="hk_center">
						<?php echo $row->zone_code_3; ?>
					</td>
					<td class="hk_center">
						<?php echo $row->zone_type; ?>
					</td>
					<td class="hk_center">
						<span id="<?php echo $publishedid ?>" class="spanloading"><?php echo $this->toggleClass->toggle($publishedid,(int) $row->zone_published,'zone') ?></span>
					</td>
					<td width="1%" class="hk_center">
						<?php echo $row->zone_id; ?>
					</td>
				</tr>
			<?php
					$k = 1-$k;
				}
			?>
		</tbody>
	</table>
	<?php if(in_array($this->type,array('discount','shipping','payment','config','tax'))){?>
		<input type="hidden" name="type" value="<?php echo $this->type;?>" />
		<input type="hidden" name="subtype" value="<?php echo $this->subtype;?>" />
		<input type="hidden" name="map" value="<?php echo $this->map;?>" />
		<input type="hidden" name="column" value="<?php echo $this->column;?>" />
	<?php }?>
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="<?php echo hikaInput::get()->getCmd('task'); ?>" />
	<input type="hidden" name="ctrl" value="<?php echo hikaInput::get()->getCmd('ctrl'); ?>" />
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" name="main_namekey" value="<?php echo hikaInput::get()->getCmd('main_namekey'); ?>" />
	<input type="hidden" name="main_id" value="<?php echo hikaInput::get()->getInt('main_id'); ?>" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
