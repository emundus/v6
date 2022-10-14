<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
if(empty($this->rows))
	return;

?><div class="hikamarket_vendors">
	<table class="hikamarket_vendors_table adminlist table table-striped table-hover">
		<thead>
			<tr>
<?php if($this->shopConfig->get('thumbnail')){ ?>
				<th class="hikamarket_vendor_image title" align="center"><?php echo JText::_('HIKA_IMAGE');?></th>
<?php } ?>
				<th class="hikamarket_vendor_name title" align="center"><?php echo JText::_('HIKA_VENDOR_NAME');?></th>
<?php if($this->params->get('show_vote')){ ?>
				<th class="hikamarket_vendor_vote title" align="center"><?php echo JText::_('VOTE'); ?></th>
<?php }
	if(!empty($this->displayFields['vendor'])) {
		foreach($this->displayFields['vendor'] as $fieldName => $oneExtraField) {
?>
				<th class="hikamarket_vendor_custom_<?php echo $oneExtraField->field_namekey;?> title" align="center"><?php
					echo $this->fieldsClass->getFieldName($oneExtraField);
				?></th>
<?php
		}
	}
?>
			</tr>
		</thead>
		<tbody>
<?php
	foreach($this->rows as $row) {
		$this->row =& $row;
		$link = hikamarket::completeLink('vendor&task=show&cid=' . $this->row->vendor_id . '&name=' . $this->row->alias . $this->menu_id);
?>
			<tr>
<?php
		if($this->shopConfig->get('thumbnail')) {
			$image = null;
			if(!empty($this->row->vendor_image))
				$image = $this->imageHelper->getThumbnail($this->row->vendor_image, $this->image_size, $this->image_options);
			if(empty($image) || !$image->success)
				$image = $this->default_vendor_image;
?>
				<td class="hikamarket_vendor_image_row">
					<div style="height:<?php echo $image->height; ?>px;text-align:center;clear:both;" class="hikamarket_vendor_image">
						<div style="position:relative;text-align:center;clear:both;width:<?php echo $image->width; ?>px;margin: auto;" class="hikamarket_vendor_image_subdiv">
<?php
			if($this->params->get('link_to_vendor_page', 1)) {
?>
							<a href="<?php echo $link; ?>" title="<?php echo $this->escape($this->row->vendor_name); ?>">
<?php
			}
?>
							<img src="<?php echo $image->url; ?>" alt="<?php echo $this->escape($this->row->vendor_name); ?>" />
<?php
			if($this->params->get('link_to_vendor_page', 1)) {
?>
							</a>
<?php
			}
?>
						</div>
					</div>
				</td>
<?php
		}
?>
				<td class="hikamerket_vendor_name_row">
					<span class="hikamerket_vendor_name">
<?php
		if($this->params->get('link_to_vendor_page', 1)) {
?>
						<a href="<?php echo $link;?>"><?php
		}

		echo $this->row->vendor_name;

		if($this->params->get('link_to_vendor_page', 1)) {
						?></a>
<?php
		}
?>
					</span>
				</td>
<?php
		if($this->params->get('show_vote')) {
?>
				<td class="hikamarket_vendor_vote_row"><?php
					$voteParams = new HikaParameter();
					$voteParams->set('vote_type','vendor');
					$voteParams->set('vote_ref_id',$this->row->vendor_id);
					$js = '';
					echo hikamarket::getLayout('shop.vote', 'mini', $voteParams, $js);
				?></td>
<?php
		}
		if(!empty($this->displayFields['vendor'])) {
			foreach($this->displayFields['vendor'] as $fieldName => $oneExtraField) {
?>
				<td class="hikamarket_vendor_custom_<?php echo $oneExtraField->field_namekey;?>_row"><?php
					echo $this->fieldsClass->show($oneExtraField, $this->row->$fieldName);
				?></td>
<?php
			}
		}
?>
			</tr>
<?php
		unset($this->row);
	}
?>
		</tbody>
	</table>
</div>
