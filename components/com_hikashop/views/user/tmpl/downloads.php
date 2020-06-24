<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2020 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
global $Itemid;
$url_itemid = (!empty($Itemid) ? '&Itemid='.$Itemid : '');
?>
<div id="hikashop_download_listing">
	<?php echo $this->toolbarHelper->process($this->toolbar, $this->title); ?>

<form action="<?php echo hikashop_completeLink('user&task=downloads'.$url_itemid); ?>" method="POST" name="adminForm" id="adminForm">
	<div class="hikashop_search_block">
		<input type="text" name="search" id="hikashop_search" value="<?php echo $this->escape($this->pageInfo->search);?>" placeholder="<?php echo JText::_('HIKA_SEARCH'); ?>" class="inputbox" onchange="document.adminForm.submit();" />
		<button class="hikabtn hikabtn-primary" onclick="this.form.submit();"><?php echo JText::_('GO'); ?></button>
		<button class="hikabtn hikabtn-primary" onclick="document.getElementById('hikashop_search').value='';this.form.submit();"><?php echo JText::_( 'RESET' ); ?></button>
	</div>
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="downloads" />
	<input type="hidden" name="ctrl" value="<?php echo hikaInput::get()->getCmd('ctrl'); ?>" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>

<table id="hikashop_downloads" class="hikashop_downloads_table adminlist table table-striped table-hover" cellpadding="1" width="100%">
	<thead>
		<tr>
			<th class="hikashop_product_name title"><?php
				echo JHTML::_('grid.sort', JText::_('PRODUCT'), 'op.order_product_name', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value );
			?></th>
			<th class="hikashop_file_name title"><?php
				echo JHTML::_('grid.sort', JText::_('HIKA_FILES'), 'f.file_name', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value );
			?></th>
			<th class="hikashop_nb_download title"><?php
				echo JText::_('NB_DOWNLOADED');
			?></th>
			<th class="hikashop_download_limit title"><?php
				echo JHTML::_('grid.sort', JText::_('DOWNLOAD_NUMBER_LIMIT'), 'f.file_limit', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value );
			?></th>
			<th class="hikashop_order_date_title title"><?php
				echo JHTML::_('grid.sort', JText::_('FIRST_PURCHASED_AT'), 'min_order_created', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value );
			?></th>
			<th class="hikashop_order_date_title title"><?php
				echo JHTML::_('grid.sort', JText::_('LAST_PURCHASED_AT'), 'max_order_created', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value );
			?></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="6">
				<div class="pagination">
					<form action="<?php echo hikashop_completeLink('user&task=downloads'.$url_itemid); ?>" method="post" name="adminForm_bottom">
						<?php $this->pagination->form = '_bottom'; echo $this->pagination->getListFooter(); ?>
						<?php echo $this->pagination->getResultsCounter(); ?>
						<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
						<input type="hidden" name="task" value="downloads" />
						<input type="hidden" name="ctrl" value="<?php echo hikaInput::get()->getCmd('ctrl'); ?>" />
						<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
						<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
						<?php echo JHTML::_( 'form.token' ); ?>
					</form>
				</div>
			</td>
		</tr>
	</tfoot>
	<tbody>
<?php
	$k = 1;
	foreach($this->downloadData as $downloadFile) {
		$k = 1 - $k;

		$limit = -1;
		if($downloadFile->file_limit == 0)
			$limit = (int)$this->config->get('download_number_limit', 50) * $downloadFile->file_quantity;
		if($downloadFile->file_limit > 0)
			$limit = $downloadFile->file_limit * $downloadFile->file_quantity;

		$limitNotReached = true;
		$periodNotReached = true;
		if(!empty($downloadFile->file_limit) && !empty($downloadFile->download_total) && $downloadFile->file_limit != -1 && $downloadFile->file_limit == 0 && $downloadFile->download_total >= $downloadFile->file_limit ) {
			$limitNotReached = false;
		}

		$order_id = 0;
		$order_date = 0;
		$single_limit = $downloadFile->file_limit;

		if($single_limit == 0)
			$single_limit = (int)$this->config->get('download_number_limit', 50);

		if(!empty($downloadFile->orders)) {
			if($single_limit > 0){
				foreach($downloadFile->orders as $o) {
					if(
					 (empty($order_date) || $o->order_created < $order_date || (($this->download_time_limit + $o->order_created) >= time() &&  ($this->download_time_limit + $order_date) < time())) &&
					 (empty($o->file_qty) || ($o->download_total < ($single_limit * (int)$o->order_product_quantity)))
					) {
						$order_id = (int)$o->order_id;
						$order_date = (int)$o->order_created;
					}
				}
			} else {
				foreach($downloadFile->orders as $o) {
					if(($this->download_time_limit + $o->order_created) >= time() &&  ($this->download_time_limit + $order_date) < time()){
						$order_id = (int)$o->order_id;
						$order_date = (int)$o->order_created;
					}
				}
			}
		}

		if(empty($order_id))
			$order_id = $downloadFile->order_id;
		if(empty($order_date))
			$order_date = $downloadFile->order_created;

		if(!empty($this->download_time_limit) && ($this->download_time_limit + $order_date) < time()) {
			$fileHtml = JText::_('TOO_LATE_NO_DOWNLOAD');
			$periodNotReached = false;
		}
?>
			<tr class="hikashop_downloads row<?php echo $k;?>">
				<td data-title="<?php echo JText::_('PRODUCT'); ?>" class="hikashop_order_item_name_value">
<?php if(!empty($downloadFile->product_id)){ ?>
					<a class="hikashop_order_product_link" href="<?php echo hikashop_completeLink('product&task=show&cid='.$downloadFile->product_id.$url_itemid); ?>">
<?php } ?>
					<p class="hikashop_order_product_name"><?php
						echo $downloadFile->order_product_name;
					?></p>
<?php if(!empty($downloadFile->product_id)){ ?>
					</a>
<?php } ?>
				</td>
				<td data-title="<?php echo JText::_('HIKA_FILES'); ?>" >
<?php
		if($limitNotReached && $periodNotReached) {
			if(empty($downloadFile->file_name)) {
				if(empty($downloadFile->file_path)) {
					$downloadFile->file_name = JText::_('DOWNLOAD_NOW');
				} else {
					$downloadFile->file_name = $downloadFile->file_path;
				}
			}
			$file_pos = '';
			if(!empty($downloadFile->file_pos)) {
				$file_pos = '&file_pos='.$downloadFile->file_pos;
			}

			if(in_array(substr($downloadFile->file_path, 0, 1), array('@', '#')) && (int)$downloadFile->file_quantity > 1) {
				for($i = 1; $i <= (int)$downloadFile->file_quantity; $i++) {
					echo '<a href="'.hikashop_completeLink('order&task=download&file_id='.$downloadFile->file_id.'&order_id='.$order_id.'&file_pos='.$i.$url_itemid).'">'.$downloadFile->file_name.'</a><br/>';
				}
				$fileHtml = '';
			} else {
				$fileHtml = '<a href="'.hikashop_completeLink('order&task=download&file_id='.$downloadFile->file_id.'&order_id='.$order_id.$file_pos.$url_itemid).'">'.$downloadFile->file_name.'</a>';
			}
		} else {
			$fileHtml = $downloadFile->file_name;
		}
		echo $fileHtml;
?>
				</td>
				<td data-title="<?php echo JText::_('NB_DOWNLOADED'); ?>" ><?php
		if(in_array(substr($downloadFile->file_path, 0, 1), array('@', '#')) && (int)$downloadFile->file_quantity > 1) {
			for($i = 1; $i <= (int)$downloadFile->file_quantity; $i++) {
				if(isset($downloadFile->downloads[$i])) {
					echo $downloadFile->downloads[$i]->download_number . '<br/>';
				} else {
					echo 0 . '<br/>';
				}
			}
		} else {
			if (!empty($downloadFile->download_total))
				echo $downloadFile->download_total;
			else
				echo 0;
		}
				?></td>
				<td data-title="<?php echo JText::_('DOWNLOAD_NUMBER_LIMIT'); ?>" >
<?php
		$downloadLimit = JText::_('UNLIMITED');
		if($limit == -1 && $limitNotReached && $periodNotReached) {
			$downloadLimit = JText::_('UNLIMITED');
		} elseif($limitNotReached && $periodNotReached) {
			if(in_array(substr($downloadFile->file_path, 0, 1), array('@', '#')) && (int)$downloadFile->file_quantity > 1) {
				$downloadLimit = '';
				for($i = 1; $i <= (int)$downloadFile->file_quantity; $i++) {
					if(isset($downloadFile->downloads[$i])) {
						echo JText::sprintf('X_DOWNLOADS_LEFT', $single_limit - $downloadFile->downloads[$i]->download_number) . '<br/>';
					} else {
						$downloadLimit .= JText::sprintf('X_DOWNLOADS_LEFT', $single_limit) . '<br/>';
					}
				}
			} else {
				$downloadLimit = JText::sprintf('X_DOWNLOADS_LEFT',$limit-$downloadFile->download_total);
			}
		} elseif(!$periodNotReached) {
			$downloadLimit = JText::_('TOO_LATE_NO_DOWNLOAD');
		} elseif(!$limitNotReached) {
			$downloadLimit = JText::_('MAX_REACHED_NO_DOWNLOAD');
		}
		echo $downloadLimit;
?>
				</td>
				<td data-title="<?php echo JText::_('FIRST_PURCHASED_AT'); ?>" ><?php
					echo hikashop_getDate($downloadFile->min_order_created,'%Y-%m-%d');
				?></td>
				<td data-title="<?php echo JText::_('LAST_PURCHASED_AT'); ?>" ><?php
					echo hikashop_getDate($downloadFile->max_order_created,'%Y-%m-%d');
				?></td>
			</tr>
<?php
	}
?>
	</tbody>
</table>

</div>
