<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="iframedoc" id="iframedoc"></div>
<form action="<?php echo hikashop_completeLink('product&task=trashlist'); ?>" method="POST" name="adminForm" id="adminForm">

<div class="hk-row-fluid">
	<div class="hkc-md-12 hikashop_search_zone">
		<div class="hikashop_search_block">
<?php echo $this->loadHkLayout('search', array()); ?>
		</div>
		<div class="hikashop_order_sort"><?php
			if(!empty($this->ordering_values))
				echo JHTML::_('select.genericlist', $this->ordering_values, 'filter_fullorder', 'onchange="this.form.submit();"', 'value', 'text', $this->full_ordering);
		?></div>
	</div>
</div>
<?php 
	echo $this->loadHkLayout('columns', array()); 
?>
<table id="hikashop_trashlist_listing" class="adminlist table table-striped table-hover" cellpadding="1">
	<thead>
		<tr>
			<th class="title titlenum"><?php
				echo JText::_('HIKA_NUM');
			?></th>
			<th class="title titlebox">
				<input type="checkbox" name="toggle" value="" onclick="hikashop.checkAll(this);" />
			</th>
			<th class="title"><?php
				echo JText::_('HIKA_IMAGE');
			?></th>
			<th class="title"><?php
				echo JHTML::_('grid.sort', JText::_('HIKA_NAME'), 'b.product_name', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?> / <?php echo JHTML::_('grid.sort', JText::_('PRODUCT_CODE'), 'b.product_code', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value );
			?></th>
			<th class="title"><?php
				echo JHTML::_('grid.sort', JText::_('PRODUCT_PRICE'), 'b.product_sort_price', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value );
			?></th>
			<th class="title"><?php
				echo JHTML::_('grid.sort', JText::_('PRODUCT_QUANTITY'), 'b.product_quantity', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value );
			?></th>
<?php
	if(!empty($this->fields)){
		foreach($this->fields as $field){
			if($field->field_type == 'customtext') continue;
			echo '<th class="title">'.JHTML::_('grid.sort', $this->fieldsClass->trans($field->field_realname), 'b.'.$field->field_namekey, $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ).'</th>';
		}
	}
	$count_extrafields = 0;
	if(!empty($this->extrafields)) {
		foreach($this->extrafields as $namekey => $extrafield) {
			echo '<th class="hikashop_product_'.$namekey.'_title title">'.$extrafield->name.'</th>'."\r\n";
		}
		$count_extrafields = count($this->extrafields);
	}
?>
			<th class="title titletoggle"><?php
				echo JHTML::_('grid.sort', JText::_('HIKA_PUBLISHED'), 'b.product_published', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value );
			?></th>
			<th class="title"><?php
				echo JHTML::_('grid.sort', JText::_('ID'), 'b.product_id', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value );
			?></th>
		</tr>
	</thead>
<?php $count = 8 + (!empty($this->fields) ? count($this->fields) : 0) + $count_extrafields; ;?>
	<tfoot>
		<tr>
			<td colspan="<?php echo $count; ?>">
				<?php echo $this->pagination->getListFooter(); ?>
			</td>
		</tr>
	</tfoot>
	<tbody>
<?php
	$i = 0;
	$k = 0;
	foreach($this->rows as &$row) {
		$publishedid = 'product_published-'.$row->product_id;
?>
		<tr class="row<?php echo $k; ?>">
			<td class="hk_center"><?php
				echo $this->pagination->getRowOffset($i);
			?></td>
			<td class="hk_center"><?php
				echo JHTML::_('grid.id', $i, $row->product_id );
			?></td>
			<td><?php
				$image_options = array('default' => true,'forcesize'=>$this->config->get('image_force_size',true),'scale'=>$this->config->get('image_scale_mode','inside'));
				$img = $this->imageHelper->getThumbnail(@$row->file_path, array('width' => 50, 'height' => 50), $image_options);
				if($img->success) {
					$attributes = '';
					if($img->external)
						$attributes = ' width="'.$img->req_width.'" height="'.$img->req_height.'"';
					echo '<img class="hikashop_product_image" title="'.$this->escape(@$row->file_description).'" alt="'.$this->escape(@$row->file_name).'" src="'.$img->url.'"'.$attributes.'/>';
				}
			?></td>
			<td>
				<?php if($this->manage){ ?>
					<a href="<?php echo hikashop_completeLink('product&task=edit&cid[]='.$row->product_id); ?>">
				<?php } ?>
						<?php echo $row->product_name; ?><br/><?php echo $row->product_code; ?>
				<?php if($this->manage){ ?>
					</a>
				<?php } ?>
			</td>
			<td><?php
				$field = 'price_value';
				if($this->config->get('floating_tax_prices')){
					$field = 'price_value_with_tax';
				}
				echo $this->currencyHelper->displayPrices(@$row->prices, $field);
			?></td>
			<td><?php
				echo ($row->product_quantity==-1?JText::_('UNLIMITED'):$row->product_quantity);
			?></td>
			<td class="hk_center"><?php
				if($this->manage) {
					?><span id="<?php echo $publishedid ?>" class="spanloading"><?php echo $this->toggleClass->toggle($publishedid,(int) $row->product_published,'product') ?></span><?php
				} else {
					echo $this->toggleClass->display('activate',$row->product_published);
				}
			?></td>
			<td width="1%" class="hk_center"><?php
				echo $row->product_id;
			?></td>
		</tr>
<?php
		$k = 1 - $k;
		$i++;
	}
	unset($row);
?>
	</tbody>
</table>

	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="trashlist" />
	<input type="hidden" name="ctrl" value="<?php echo hikaInput::get()->getCmd('ctrl'); ?>" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
