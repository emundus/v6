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
<form action="<?php echo hikashop_completeLink('badge'); ?>" method="post"  name="adminForm" id="adminForm">

<div class="hk-row">
	<div class="hkc-md-5 hika_j4_search"><?php echo $this->loadHkLayout('search', array()); ?></div>
	<div class="hkc-md-7"></div>
</div>
<?php 
	echo $this->loadHkLayout('columns', array()); 
?>
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
					if ($this->order->ordering) {
						$keys = array_keys($this->rows);  
						$rows_nb = end($keys);
						$href = "javascript:saveorder(".$rows_nb.", 'saveorder')";
						?><a href="<?php echo $href; ?>" rel="tooltip" class="saveorder btn btn-sm btn-secondary float-end" title="Save Order">
							<button class="button-apply btn btn-success" type="button">
<!--							<span class="icon-apply" aria-hidden="true"></span> -->
								<i class="fas fa-save"></i>
							</button>
						</a><?php
					}
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
					$image_options = array('default' => true,'forcesize'=>true,'scale'=>$this->config->get('image_scale_mode','inside'));
					$img = $this->image->getThumbnail(@$row->badge_image, array('width' => 100, 'height' => 100), $image_options);
					if($img->success) {
						echo '<img class="hikashop_category_listing_image" title="'.$this->escape(@$row->badge_name).'" alt="'.$this->escape(@$row->badge_name).'" src="'.$img->url.'"/>';
					}
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

			if(!empty($row->badge_start)) {
				$restrictions[] = '<strong>'.JText::_('START_DATE').'</strong>:'.hikashop_getDate($row->badge_start,'%Y-%m-%d %H:%M');
			}
			if(!empty($row->badge_end)) {
				$restrictions[] = '<strong>'.JText::_('END_DATE').'</strong>:'.hikashop_getDate($row->badge_end,'%Y-%m-%d %H:%M');
			}
			if(!empty($row->badge_quantity) || $row->badge_quantity === '0') {
				$restrictions[] = '<strong>'.JText::_('MAXIMUM_PRODUCT_QUANTITY').'</strong>:'.$row->badge_quantity;
			}
			if(!empty($row->badge_new_period)) {
				$delayType = hikashop_get('type.delay');
				$restrictions[] = '<strong>'.JText::_('NEW_PRODUCT_PERIOD').'</strong>:'.$delayType->displayDelay($row->badge_new_period);
			}
			if(!empty($row->badge_product_id)) {
				$restrictions[] = '<strong>'.JText::_('PRODUCT').'</strong>:'.$row->badge_product_id;
			}
			if(!empty($row->badge_category_id)) {
				$restriction = '<strong>'.JText::_('CATEGORY').'</strong>:'.$row->badge_category_id;
				if($row->badge_category_childs){
					$restriction .= '<br/>'.JText::_('INCLUDING_SUB_CATEGORIES');
				}
				$restrictions[] = $restriction;
			}
			if(!empty($row->badge_discount_id)) {
				$restrictions[] = '<strong>'.JText::_('DISCOUNT').'</strong>:'.$row->badge_discount_id;
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
	<input type="hidden" name="ctrl" value="<?php echo hikaInput::get()->getCmd('ctrl'); ?>" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
	<?php echo JHTML::_('form.token'); ?>
</form>
