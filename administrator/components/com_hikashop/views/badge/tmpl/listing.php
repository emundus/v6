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
<form action="<?php echo hikashop_completeLink('badge'); ?>" method="post"  name="adminForm" id="adminForm">

<div class="hk-row">
	<div class="hkc-md-5"><?php
		echo $this->searchType->display('search', $this->pageInfo->search);
	?></div>
	<div class="hkc-md-7"></div>
</div>

	<table id="hikashop_badge_listing" class="adminlist table table-striped table-hover" cellpadding="1">
		<thead>
			<tr>
				<th class="title titlenum"><?php
					echo JText::_('HIKA_NUM');
				?></th>
				<th class="title titlebox">
					<input type="checkbox" name="toggle" value="" onclick="hikashop.checkAll(this);" />
				</th>
				<th class="title titlebox"><?php
					echo JText::_('HIKA_IMAGE');
				?></th>
				<th class="title"><?php
					echo JHTML::_('grid.sort', JText::_('HIKA_NAME'), 'a.badge_name', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value );
				?></th>
				<th class="title"><?php
					echo JHTML::_('grid.sort', JText::_('POSITION'), 'a.badge_position', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value );
				?></th>
				<th class="title titleorder"><?php
					if($this->order->ordering)
						echo JHTML::_('grid.order', $this->rows);
					echo JHTML::_('grid.sort', JText::_('HIKA_ORDER'), 'a.badge_ordering',$this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value );
				?></th>
				<th class="title titletoggle"><?php
					echo JHTML::_('grid.sort', JText::_('HIKA_PUBLISHED'), 'a.badge_published', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value );
				?></th>
				<th class="title"><?php
					echo JText::_('RESTRICTIONS');
				?></th>
				<th class="title"><?php
					echo JHTML::_('grid.sort', JText::_('ID'), 'a.badge_id', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value );
				?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="9">
					<?php echo $this->pagination->getListFooter(); ?>
					<?php echo $this->pagination->getResultsCounter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
<?php
	$k = 0;
	$i = 0;
	$count = count($this->rows);
	foreach($this->rows as &$row) {
		$publishedid = 'badge_published-'.$row->badge_id;
?>
			<tr class="row<?php echo $k; ?>">
				<td class="hk_center"><?php
					echo $this->pagination->getRowOffset($i);
				?></td>
				<td class="hk_center"><?php
					echo JHTML::_('grid.id', $i, $row->badge_id);
				?></td>
				<td><?php
					echo $this->image->display(@$row->badge_image, true, '', '', '', 100, 100);
				?></td>
				<td>
<?php if($this->manage) { ?>
					<a href="<?php echo hikashop_completeLink('badge&task=edit&cid[]='.$row->badge_id); ?>">
<?php } ?>
					<?php echo $row->badge_name; ?>
<?php if($this->manage) { ?>
					</a>
<?php } ?>
				</td>
				<td><?php
					echo $row->badge_position;
				?></td>
				<td class="order">
<?php if($this->manage){ ?>
					<span><?php echo $this->pagination->orderUpIcon($i, $this->order->reverse XOR ( $row->badge_ordering >= @$this->rows[$i-1]->badge_ordering ), $this->order->orderUp, 'Move Up',$this->order->ordering ); ?></span>
					<span><?php echo $this->pagination->orderDownIcon($i, $count, $this->order->reverse XOR ( $row->badge_ordering <= @$this->rows[$i+1]->badge_ordering ), $this->order->orderDown, 'Move Down' ,$this->order->ordering); ?></span>
					<input type="text" name="order[]" size="5" <?php if(!$this->order->ordering) echo 'disabled="disabled"'?> value="<?php echo $row->badge_ordering; ?>" class="text_area" style="text-align: center" />
<?php }else{ echo $row->badge_ordering; } ?>
				</td>
				<td class="hk_center">
<?php if($this->manage){ ?>
					<span id="<?php echo $publishedid ?>" class="spanloading"><?php echo $this->toggleClass->toggle($publishedid,(int) $row->badge_published,'badge') ?></span>
<?php }else{ echo $this->toggleClass->display('activate',$row->badge_published); } ?>
				</td>
				<td><?php

			$restrictions = array();
			if(!empty($row->badge_discount_id)) {
				$restrictions[] = '<strong>'.JText::_('DISCOUNT').'</strong>:'.$row->badge_discount_id;
			}
			if(!empty($row->badge_product_id)) {
				$restrictions[] = '<strong>'.JText::_('PRODUCT').'</strong>:'.$row->badge_product_id;
			}
			if(!empty($row->badge_category_id)) {
				$restriction = '<strong>'.JText::_('CATEGORY').'</strong>:'.$row->badge_category_id;
				if($row->badge_category_childs){
					$restriction .= '</br>'.JText::_('INCLUDING_SUB_CATEGORIES');
				}
				$restrictions[] = $restriction;
			}
			echo implode('<br/>',$restrictions);

				?></td>
				<td width="1%" class="hk_center"><?php
					echo $row->badge_id;
				?></td>
			</tr>
<?php
		$i++;
		$k = 1-$k;
	}
	unset($row);
?>
		</tbody>
	</table>
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
	<?php echo JHTML::_('form.token'); ?>
</form>
