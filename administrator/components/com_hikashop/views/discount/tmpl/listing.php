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
<form action="<?php echo hikashop_completeLink('discount'); ?>" method="post" name="adminForm" id="adminForm">

<div class="hk-row-fluid">
	<div class="hkc-xs-6 hika_j4_search">
<?php
	echo $this->loadHkLayout('search', array());
?>
	</div>
	<div class="hkc-xs-6 hikashop_listing_filters"><?php
		echo $this->filter_type->display('filter_type', $this->pageInfo->filter->filter_type);
	?></div>
</div>
<?php 
	echo $this->loadHkLayout('columns', array()); 
?>
	<table id="hikashop_discount_listing" class="adminlist table table-striped table-hover" cellpadding="1">
		<thead>
			<tr>
				<th class="title titlenum"><?php
					echo JText::_('HIKA_NUM');
				?></th>
				<th class="title titlebox">
					<input type="checkbox" name="toggle" value="" onclick="hikashop.checkAll(this);" />
				</th>
				<th class="title"><?php
					echo JHTML::_('grid.sort', JText::_('DISCOUNT_CODE'), 'a.discount_code', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value );
				?></th>
				<th class="title"><?php
					echo JHTML::_('grid.sort', JText::_('DISCOUNT_TYPE'), 'a.discount_type', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value );
				?></th>
				<th class="title"><?php
					echo JHTML::_('grid.sort', JText::_('DISCOUNT_START_DATE'), 'a.discount_start', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value );
				?></th>
				<th class="title"><?php
					echo JHTML::_('grid.sort', JText::_('DISCOUNT_END_DATE'), 'a.discount_end', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value );
				?></th>
				<th class="title"><?php
					echo JText::_('DISCOUNT_VALUE');
				?></th>
<?php if(hikashop_level(1)){ ?>
				<th class="title"><?php
					echo JText::_('DISCOUNT_QUOTA');
				?></th>
				<th class="title"><?php
					echo JText::_('RESTRICTIONS');
				?></th>
<?php } ?>
				<th class="title titletoggle"><?php
					echo JHTML::_('grid.sort', JText::_('HIKA_PUBLISHED'), 'a.discount_published', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value );
				?></th>
				<th class="title"><?php
					echo JHTML::_('grid.sort', JText::_( 'ID' ), 'a.discount_id', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value );
				?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="11">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
<?php
	$k = 0;
	$i = 0;
	foreach($this->rows as &$row) {
		$publishedid = 'discount_published-'.$row->discount_id;
?>
			<tr class="row<?php echo $k; ?>">
				<td><?php
					echo $this->pagination->getRowOffset($i);
				?></td>
				<td><?php
					echo JHTML::_('grid.id', $i, $row->discount_id );
				?></td>
				<td>
<?php if($this->manage) { ?>
					<a href="<?php echo hikashop_completeLink('discount&task=edit&cid[]='.$row->discount_id); ?>">
<?php } ?>
<?php
	if(!empty($row->discount_code))
		echo $row->discount_code;
	else
		echo '<em>'.JText::_('HIKA_NONE').'</em>';
?>
<?php if($this->manage) { ?>
					</a>
<?php } ?>
					</td>
					<td><?php
						echo $this->escape($row->discount_type);
					?></td>
					<td><?php
						echo hikashop_getDate($row->discount_start);
					?></td>
					<td><?php
						echo hikashop_getDate($row->discount_end);
					?></td>
					<td><?php
	if(isset($row->discount_flat_amount) && $row->discount_flat_amount > 0) {
		echo $this->currencyHelper->displayPrices(array($row),'discount_flat_amount','discount_currency_id');
	} elseif(isset($row->discount_percent_amount) && $row->discount_percent_amount > 0) {
		echo $row->discount_percent_amount. '%';
	}
					?></td>
<?php if(hikashop_level(1)){ ?>
					<td><?php
		if(empty($row->discount_quota)) {
				echo JText::_('UNLIMITED');
		} else {
				echo $row->discount_quota. ' ('.JText::sprintf('X_LEFT',$row->discount_quota-$row->discount_used_times).')';
		}
					?></td>
					<td><?php

		$restrictions = array();
		if(!empty($row->discount_minimum_order) && (float)$row->discount_minimum_order != 0) {
			$restrictions[] = '<strong>'.JText::_('MINIMUM_ORDER_VALUE').'</strong>: '.$this->currencyHelper->displayPrices(array($row),'discount_minimum_order','discount_currency_id');
		}
		if(!empty($row->discount_maximum_order) && (float)$row->discount_maximum_order != 0) {
			$restrictions[] = '<strong>'.JText::_('MAXIMUM_ORDER_VALUE').'</strong>: '.$this->currencyHelper->displayPrices(array($row),'discount_maximum_order','discount_currency_id');
		}
		if(!empty($row->discount_minimum_products) && (float)$row->discount_minimum_products != 0) {
			$restrictions[] = '<strong>'.JText::_('PRODUCT_MIN_QUANTITY_PER_ORDER').'</strong>: '.$row->discount_minimum_products;
		}
		if(!empty($row->discount_maximum_products) && (float)$row->discount_maximum_products != 0) {
			$restrictions[] = '<strong>'.JText::_('PRODUCT_MAX_QUANTITY_PER_ORDER').'</strong>: '.$row->discount_maximum_products;
		}
		if(!empty($row->discount_product_id)) {
			$restrictions[] = '<strong>'.JText::_('PRODUCT').'</strong>:'.$row->discount_product_id;
		}
		if(!empty($row->discount_category_id)){
			$restriction = '<strong>'.JText::_('CATEGORY').'</strong>:'.$row->discount_category_id;
			if($row->discount_category_childs) {
				$restriction .= ' '.JText::_('INCLUDING_SUB_CATEGORIES');
			}
			$restrictions[] = $restriction;
		}
		if(!empty($row->discount_zone_id)) {
			$restrictions[] = '<strong>'.JText::_('ZONE').'</strong>:'.$row->discount_zone_id;
		}
		if(!empty($row->discount_user_id)) {
			$restrictions[] = '<strong>'.JText::_('HIKA_USER').'</strong>:'.$row->discount_user_id;
		}



		if ($row->discount_type == 'coupon') {
			if (!empty($row->discount_coupon_product_only)) {
				 $restrictions[] = 'Percentage for product only';
			}
			if(!empty($row->discount_coupon_nodoubling)) {
				switch($row->discount_coupon_nodoubling) {
					case 1:
						$restrictions[] = 'Ignore discounted products';
						break;
					case 2:
						$restrictions[] = 'Override discounted products';
						break;
					default:
						break;
				}
			}
		}




		if(!empty($row->discount_site_id)) {
			$restrictions[] = '<strong>'.JText::_('SITE_ID').'</strong>:'.$row->discount_site_id;
		}
		echo implode('<br/>', $restrictions);

				?></td>
<?php } ?>
				<td class="hk_center">
					<?php if($this->manage){ ?>
						<span id="<?php echo $publishedid ?>" class="spanloading"><?php echo $this->toggleClass->toggle($publishedid,(int) $row->discount_published,'discount') ?></span>
					<?php }else{ echo $this->toggleClass->display('activate',$row->discount_published); } ?>
				</td>
				<td width="1%" class="hk_center"><?php
					echo $row->discount_id;
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
